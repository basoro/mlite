import 'dotenv/config'
import express from 'express'
import cors from 'cors'
import pino from 'pino'
import { WebSocketServer } from 'ws'
import makeWASocket, { useMultiFileAuthState, DisconnectReason, fetchLatestBaileysVersion } from '@whiskeysockets/baileys'
import Database from 'better-sqlite3'
import fs from 'fs'
import path from 'path'
import dns from 'dns'

// Force IPv4 for better stability in some container environments
dns.setDefaultResultOrder('ipv4first')

const logger = pino({ level: 'info' })
const app = express()
app.use(cors())
app.use(express.urlencoded({ extended: true }))
app.use(express.json())

const PORT = process.env.PORT || 4000

// --- SQLite Setup ---
const dbPath = process.env.DB_PATH || 'whatsapp.db'
const db = new Database(dbPath)
db.pragma('journal_mode = WAL')

// Initialize Tables
db.exec(`
  CREATE TABLE IF NOT EXISTS whatsapp_raw_messages (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    wa_message_id TEXT,
    chat_jid TEXT,
    sender_jid TEXT,
    from_me INTEGER,
    event_type TEXT,
    message_json TEXT,
    message_timestamp TEXT
  );
  CREATE TABLE IF NOT EXISTS whatsapp_contacts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    phone TEXT UNIQUE,
    name TEXT,
    unread_count INTEGER DEFAULT 0,
    last_message_at TEXT
  );
  CREATE TABLE IF NOT EXISTS whatsapp_lid_mappings (
    lid TEXT PRIMARY KEY,
    phone TEXT,
    contact_id INTEGER,
    updated_at TEXT
  );
  CREATE TABLE IF NOT EXISTS whatsapp_messages (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    contact_id INTEGER,
    direction TEXT,
    text TEXT,
    status TEXT,
    wa_message_id TEXT UNIQUE,
    sender_jid TEXT,
    created_at TEXT,
    FOREIGN KEY(contact_id) REFERENCES whatsapp_contacts(id)
  );
`)

// Prepared Statements
const stmtInsertRaw = db.prepare(`
  INSERT INTO whatsapp_raw_messages (wa_message_id, chat_jid, sender_jid, from_me, event_type, message_json, message_timestamp)
  VALUES (@waId, @chatJid, @senderJid, @fromMe, @eventType, @msg, @ts)
`)

const stmtGetContactByPhone = db.prepare('SELECT * FROM whatsapp_contacts WHERE phone = ?')
const stmtInsertContact = db.prepare('INSERT INTO whatsapp_contacts (name, phone, unread_count, last_message_at) VALUES (@name, @phone, @unread, @lastAt)')
const stmtUpdateContact = db.prepare('UPDATE whatsapp_contacts SET unread_count = @unread, last_message_at = @lastAt, name = coalesce(@name, name) WHERE id = @id')
const stmtUpsertLid = db.prepare(`
  INSERT INTO whatsapp_lid_mappings (lid, phone, contact_id, updated_at)
  VALUES (@lid, @phone, @contactId, @updatedAt)
  ON CONFLICT(lid) DO UPDATE SET updated_at = @updatedAt, contact_id = @contactId, phone = @phone
`)
const stmtUpsertMessage = db.prepare(`
  INSERT INTO whatsapp_messages (contact_id, direction, text, status, wa_message_id, sender_jid, created_at)
  VALUES (@contactId, @direction, @text, @status, @waId, @senderJid, @createdAt)
  ON CONFLICT(wa_message_id) DO UPDATE SET status = @status, text = @text
`)
const stmtGetContacts = db.prepare('SELECT * FROM whatsapp_contacts ORDER BY last_message_at DESC')
const stmtGetMessages = db.prepare('SELECT * FROM whatsapp_messages WHERE contact_id = ? ORDER BY created_at ASC')

// -------------------

let sock = null
let connStatus = { connected: false, qr: null, connecting: true, lastError: null }
let manualReset = false
let isStarting = false
let autoReconnectEnabled = true
let reconnectDelayMs = 5000

const CAPTURE_GROUP_PARTICIPANTS = (process.env.CAPTURE_GROUP_PARTICIPANTS ?? 'true') === 'true'
const PERSIST_ENCRYPTED = (process.env.PERSIST_ENCRYPTED ?? 'true') === 'true'
const ALLOW_ANY_PHONE_DIGITS = (process.env.ALLOW_ANY_PHONE_DIGITS ?? 'true') === 'true'
const PERSIST_LID_FALLBACK = (process.env.PERSIST_LID_FALLBACK ?? 'false') === 'true'

function normalizeDigits(d) {
  const digits = String(d || '').replace(/[^0-9]/g, '')
  if (!digits) return null
  if (digits.startsWith('62')) return digits
  if (digits.startsWith('0')) return '62' + digits.slice(1)
  if (digits.startsWith('8')) return '62' + digits
  return digits
}

async function startBaileys() {
  if (isStarting) { logger.info({ msg: 'startBaileys skipped: already starting' }); return }
  isStarting = true
  const authBase = process.env.AUTH_DIR || './wa-gateway/auth'
  const authDir = path.resolve(process.cwd(), authBase)
  if (!fs.existsSync(authDir)) fs.mkdirSync(authDir, { recursive: true })
  const { state, saveCreds } = await useMultiFileAuthState(authDir)
  const { version } = await fetchLatestBaileysVersion()
  sock = makeWASocket({
      auth: state,
      version,
      browser: ['Baileys', 'Chrome', '10.0.0'],
      logger,
      connectTimeoutMs: 60000,
      defaultQueryTimeoutMs: 0,
      keepAliveIntervalMs: 10000,
      emitOwnEvents: true,
      retryRequestDelayMs: 250,
      markOnlineOnConnect: false,
      generateHighQualityLinkPreview: true,
  })
  
  const unwrap = (message) => {
    let m = message || {}
    for (let i = 0; i < 3; i++) {
      if (m?.ephemeralMessage?.message) m = m.ephemeralMessage.message
      else if (m?.viewOnceMessage?.message) m = m.viewOnceMessage.message
      else if (m?.deviceSentMessage?.message) m = m.deviceSentMessage.message
      else break
    }
    return m || {}
  }
  
  const normalizePhone = (digits) => {
    const d = String(digits || '').replace(/[^0-9]/g, '')
    if (!d) return null
    if (d.startsWith('62')) return d
    if (d.startsWith('0')) return '62' + d.slice(1)
    if (d.startsWith('8')) return '62' + d
    if (ALLOW_ANY_PHONE_DIGITS && d.length >= 8) return '62' + d
    return null
  }

  const getSenderJid = (msg) => {
    const k = msg?.key || {}
    const m = unwrap(msg?.message)
    const altPN = k.remoteJidAlt || k.participantAlt
    const p1 = k.participant
    const p2 = msg?.participant
    const p3 = m?.extendedTextMessage?.contextInfo?.participant
    const p4 = m?.imageMessage?.contextInfo?.participant
    const p5 = m?.videoMessage?.contextInfo?.participant
    const p6 = m?.documentMessage?.contextInfo?.participant
    const direct = k.remoteJid && String(k.remoteJid).endsWith('@s.whatsapp.net') ? k.remoteJid : null
    const cand = altPN || p1 || p2 || p3 || p4 || p5 || p6 || direct || null
    if (!cand) return null
    const s = String(cand)
    if (s.endsWith('@s.whatsapp.net')) return s
    if (s.endsWith('@c.us')) return s.replace('@c.us', '@s.whatsapp.net')
    const digits = s.split('@')[0].replace(/[^0-9]/g, '')
    const n = normalizePhone(digits)
    if (n) return `${n}@s.whatsapp.net`
    return null
  }

  const getSenderLid = (msg) => {
    const k = msg?.key || {}
    const m = unwrap(msg?.message)
    const l1 = k.participant && String(k.participant).endsWith('@lid') ? k.participant : null
    const l2 = k.remoteJid && String(k.remoteJid).endsWith('@lid') ? k.remoteJid : null
    const l3 = m?.extendedTextMessage?.contextInfo?.participant && String(m?.extendedTextMessage?.contextInfo?.participant).endsWith('@lid') ? m?.extendedTextMessage?.contextInfo?.participant : null
    return l1 || l2 || l3 || null
  }

  function resolveSenderJid(msg, sock) {
    const k = msg.key || {}
    let jid = k.participant || k.remoteJid || msg.participant || null
    if (!jid) return null
    jid = String(jid)
    if (jid.endsWith('@c.us')) {
      return jid.replace('@c.us', '@s.whatsapp.net')
    }
    if (jid.endsWith('@s.whatsapp.net')) {
      return jid
    }
    if (jid.endsWith('@lid') && sock?.signalRepository?.lidMapping) {
      try {
        const pn = sock.signalRepository.lidMapping.getPNForLID(jid)
        if (pn) return `${pn}@s.whatsapp.net`
      } catch {}
    }
    return null
  }

  const extractText = (msg) => {
    const m = unwrap(msg?.message)
    return (
      m?.conversation ||
      m?.extendedTextMessage?.text ||
      m?.imageMessage?.caption ||
      m?.videoMessage?.caption ||
      m?.documentMessage?.caption ||
      m?.buttonsMessage?.text ||
      m?.contactMessage?.displayName ||
      ''
    )
  }

  const extractName = (msg) => {
    const m = unwrap(msg?.message)
    return (
      msg?.pushName ||
      m?.contactMessage?.displayName ||
      null
    )
  }

  const extractTimestamp = (msg) => {
    const m = unwrap(msg?.message)
    const t = (msg && msg.messageTimestamp) ?? (m && m.messageTimestamp)
    let ms
    if (typeof t === 'number') {
      ms = t > 1e12 ? t : t * 1000
    } else if (typeof t === 'string') {
      const s = t.trim()
      if (/^\d+$/.test(s)) {
        const n = parseInt(s, 10)
        ms = n > 1e12 ? n : n * 1000
      } else {
        ms = Date.now()
      }
    } else if (t && typeof t.toNumber === 'function') {
      const n = t.toNumber()
      ms = n > 1e12 ? n : n * 1000
    } else {
      ms = Date.now()
    }
    return new Date(ms)
  }

  const persistRawMessage = async ({ waId, chatJid, senderJid, fromMe, eventType, msg }) => {
    try {
        const message_timestamp = extractTimestamp(msg).toISOString()
        const message_json = JSON.stringify(msg)
        stmtInsertRaw.run({
            waId: waId || null,
            chatJid: chatJid || null,
            senderJid: senderJid || null,
            fromMe: fromMe ? 1 : 0,
            eventType,
            msg: message_json,
            ts: message_timestamp
        })
        logger.info({ msg: 'raw persist ok', wa_message_id: waId })
        return true
    } catch (err) {
        logger.error({ msg: 'raw persist failed', wa_message_id: waId, err })
        return false
    }
  }

  sock.ev.on('connection.update', async (update) => {
    const { connection, lastDisconnect, qr } = update
    if (qr) {
      connStatus.qr = qr
      broadcast({ type: 'qr', qr })
      logger.info({ msg: 'QR updated' })
    }
    if (connection === 'open') {
      connStatus.connected = true
      connStatus.connecting = false
      connStatus.qr = null
      manualReset = false
      autoReconnectEnabled = true
      reconnectDelayMs = 5000
      broadcast({ type: 'status', connected: true })
    } else if (connection === 'close') {
      connStatus.connected = false
      connStatus.connecting = false
      const shouldReconnect = (lastDisconnect?.error)?.output?.statusCode !== DisconnectReason.loggedOut
      connStatus.lastError = (lastDisconnect?.error?.message) || null
      broadcast({ type: 'status', connected: false })
      if (shouldReconnect && !manualReset && autoReconnectEnabled) {
        const delay = reconnectDelayMs
        reconnectDelayMs = Math.min(reconnectDelayMs * 2, 60000)
        logger.warn({ msg: 'reconnect scheduled', delay_ms: delay, lastError: connStatus.lastError })
        setTimeout(() => { if (!isStarting) startBaileys() }, delay)
      }
    } else if (connection === 'connecting') {
      connStatus.connecting = true
    }
  })

  sock.ev.on('creds.update', saveCreds)
  sock.ev.on('creds.update', () => {
    try {
      if (sock?.user?.id) {
        connStatus.connected = true
        connStatus.connecting = false
        connStatus.qr = null
        broadcast({ type: 'status', connected: true })
      }
    } catch {}
  })

  sock.ev.on('messages.upsert', async (m) => {
    const msgs = m.messages || []
    for (const msg of msgs) {
      const from = msg.key.remoteJid
      const id = msg.key.id
      const text = extractText(msg)
      const fromMe = !!msg.key.fromMe
      let senderJid = resolveSenderJid(msg, sock) || getSenderJid(msg)
      const senderLid = getSenderLid(msg)
      if (!senderJid && senderLid && sock?.signalRepository?.lidMapping) {
        try {
          const pn = sock.signalRepository.lidMapping.getPNForLID(senderLid)
          if (pn) senderJid = `${normalizeDigits(pn)}@s.whatsapp.net`
        } catch {}
      }
      const displayName = extractName(msg)
      await persistRawMessage({ waId: id, chatJid: from, senderJid, fromMe, eventType: 'upsert', msg })
      logger.info({ event: 'messages.upsert', from, sender_pn: senderJid, fromMe, hasText: !!text, groupCapture: CAPTURE_GROUP_PARTICIPANTS })
      
      if (senderJid) {
        try { await sock.presenceSubscribe(senderJid) } catch {}
      }

      let effSender = senderJid
      if (!effSender && PERSIST_LID_FALLBACK && String(from).endsWith('@lid')) {
        const lidDigits = String(from).split('@')[0].replace(/[^0-9]/g, '')
        const norm = normalizePhone(lidDigits)
        if (norm) effSender = `${norm}@s.whatsapp.net`
      }
      
      const phoneRaw = String(effSender || '').split('@')[0].replace(/[^0-9]/g, '')
      const normPhone = normalizePhone(phoneRaw)
      const isValidPhone = !!normPhone
      const isGroup = String(from).endsWith('@g.us')
      const canPersistText = !!text || PERSIST_ENCRYPTED

      if (!fromMe && (effSender && String(effSender).endsWith('@s.whatsapp.net')) && isValidPhone && canPersistText && (CAPTURE_GROUP_PARTICIPANTS || !isGroup)) {
        let contactId = null
        try {
          const contactRow = stmtGetContactByPhone.get(normPhone)
          logger.info({ msg: 'contact resolve', phone: normPhone, resolved_contact_id: contactRow?.id })
          
          if (!contactRow) {
             const result = stmtInsertContact.run({
                 name: displayName || normPhone,
                 phone: normPhone,
                 unread: 1,
                 lastAt: new Date().toISOString()
             })
             contactId = result.lastInsertRowid
             logger.info({ msg: 'contact insert ok', phone: normPhone, contact_id: contactId })
          } else {
             contactId = contactRow.id
             const updateFields = { 
                 unread: Number(contactRow.unread_count || 0) + 1, 
                 lastAt: new Date().toISOString(),
                 id: contactId,
                 name: displayName || null
             }
             stmtUpdateContact.run(updateFields)
          }

          const senderLidMap = getSenderLid(msg)
          if (senderLidMap) {
             stmtUpsertLid.run({
                 lid: senderLidMap,
                 phone: normPhone,
                 contactId: contactId,
                 updatedAt: new Date().toISOString()
             })
          }

          if (contactId) {
             stmtUpsertMessage.run({
                 contactId: contactId,
                 direction: 'in',
                 text: text || '[ter-enkripsi, menunggu dekripsi ulang]',
                 status: text ? 'received' : 'encrypted',
                 waId: id,
                 senderJid: effSender,
                 createdAt: new Date().toISOString()
             })
             logger.info({ msg: 'message upsert ok', wa_message_id: id, contact_id: contactId })
          }

        } catch (e) {
          logger.error({ msg: 'persist inbound failed', err: e })
        }
        
        broadcast({ type: 'message', from: effSender || from, sender_pn: effSender || null, display_name: displayName || null, chat_jid: from, contact_id: contactId, text, encrypted: !text, id, ts: Date.now(), fromMe: false })
      } else {
        logger.info({ msg: 'persist skipped', id, from, sender_pn: senderJid, flags: { fromMe, missingSender: !effSender, notPersonal: !!effSender && !String(effSender).endsWith('@s.whatsapp.net'), noText: !text, isValidPhone, isGroup, groupCapture: CAPTURE_GROUP_PARTICIPANTS, canPersistText } })
      }
    }
  })

  sock.ev.on('messages.update', async (updates) => {
    for (const { key, update } of updates) {
      const from = key.remoteJid
      const id = key.id
      const text = extractText({ message: update?.message })
      const fromMe = !!key.fromMe
      let senderJid = resolveSenderJid({ key, message: update?.message }, sock) || getSenderJid({ key, message: update?.message })
      const senderLid = getSenderLid({ key, message: update?.message })
      if (!senderJid && senderLid && sock?.signalRepository?.lidMapping) {
        try {
          const pn = sock.signalRepository.lidMapping.getPNForLID(senderLid)
          if (pn) senderJid = `${normalizeDigits(pn)}@s.whatsapp.net`
        } catch {}
      }
      const displayName = extractName({ message: update?.message, pushName: update?.pushName })
      await persistRawMessage({ waId: id, chatJid: from, senderJid, fromMe, eventType: 'update', msg: { key, message: update?.message } })
      logger.info({ event: 'messages.update', from, sender_pn: senderJid, fromMe, hasText: !!text, groupCapture: CAPTURE_GROUP_PARTICIPANTS })
      
      let effSender = senderJid
      if (!effSender && PERSIST_LID_FALLBACK && String(from).endsWith('@lid')) {
        const lidDigits = String(from).split('@')[0].replace(/[^0-9]/g, '')
        const norm = normalizePhone(lidDigits)
        if (norm) effSender = `${norm}@s.whatsapp.net`
      }
      
      const phoneRaw = String(effSender || '').split('@')[0].replace(/[^0-9]/g, '')
      const normPhone = normalizePhone(phoneRaw)
      const isValidPhone = !!normPhone
      let contactId = null
      const isGroup = String(from).endsWith('@g.us')
      const canPersistText = !!text || PERSIST_ENCRYPTED

      if (!fromMe && (effSender && String(effSender).endsWith('@s.whatsapp.net')) && isValidPhone && canPersistText && (CAPTURE_GROUP_PARTICIPANTS || !isGroup)) {
        try {
          const contactRow = stmtGetContactByPhone.get(normPhone)
          contactId = contactRow?.id
          
          if (contactId) {
            if (senderLid) {
               stmtUpsertLid.run({
                 lid: senderLid,
                 phone: normPhone,
                 contactId: contactId,
                 updatedAt: new Date().toISOString()
               })
            }
            
            stmtUpsertMessage.run({
                 contactId: contactId,
                 direction: 'in',
                 text: text || '[ter-enkripsi, menunggu dekripsi ulang]',
                 status: text ? 'received' : 'encrypted',
                 waId: id,
                 senderJid: effSender,
                 createdAt: new Date().toISOString()
            })
            
            if (displayName) {
               stmtUpdateContact.run({
                   id: contactId,
                   unread: contactRow.unread_count, // keep existing unread
                   lastAt: new Date().toISOString(),
                   name: displayName
               })
            }
            logger.info({ msg: 'message update upsert ok', wa_message_id: id, contact_id: contactId })
          }
        } catch (e) {
          logger.error({ msg: 'update inbound failed', err: e })
        }
      }
      
      if (!fromMe && (text || PERSIST_ENCRYPTED)) broadcast({ type: 'message', from: effSender || from, sender_pn: effSender || null, display_name: displayName || null, chat_jid: from, contact_id: contactId, text, encrypted: !text, id, ts: Date.now(), fromMe: false })
    }
  })

  sock.ev.on('presence.update', (update) => {
    try {
      if (!update) return
      const id = update.id || null
      const presences = update.presences || null
      if (id && update.presence) {
        const senderJid = String(id).endsWith('@s.whatsapp.net') ? id : null
        broadcast({ type: 'typing', from: senderJid, isTyping: update.presence === 'composing', ts: Date.now() })
      } else if (id && presences) {
        for (const [jid, p] of Object.entries(presences)) {
          const senderJid = String(jid).endsWith('@s.whatsapp.net') ? jid : null
          broadcast({ type: 'typing', from: senderJid, isTyping: p?.lastKnownPresence === 'composing', ts: Date.now() })
        }
      }
    } catch {}
  })
  isStarting = false
}

const clients = new Set()
function broadcast(data) {
  const payload = JSON.stringify(data)
  for (const ws of clients) {
    try { ws.send(payload) } catch { /* ignore */ }
  }
}

const wss = new WebSocketServer({ noServer: true })
wss.on('connection', (ws) => {
  clients.add(ws)
  ws.on('close', () => clients.delete(ws))
  ws.send(JSON.stringify({ type: 'status', connected: connStatus.connected }))
  if (!connStatus.connected && connStatus.qr) ws.send(JSON.stringify({ type: 'qr', qr: connStatus.qr }))
})

const server = app.listen(PORT, () => {
  logger.info({ msg: 'WA Gateway started', port: PORT })
})

server.on('upgrade', (request, socket, head) => {
  if (request.url === '/ws') {
    wss.handleUpgrade(request, socket, head, (ws) => {
      wss.emit('connection', ws, request)
    })
  } else {
    socket.destroy()
  }
})

// --- API Endpoints for Frontend ---

app.get('/api/contacts', (req, res) => {
    try {
        const contacts = stmtGetContacts.all()
        res.json({ ok: true, data: contacts })
    } catch (e) {
        res.status(500).json({ ok: false, error: e.message })
    }
})

app.get('/api/messages/:contactId', (req, res) => {
    try {
        const messages = stmtGetMessages.all(req.params.contactId)
        res.json({ ok: true, data: messages })
    } catch (e) {
        res.status(500).json({ ok: false, error: e.message })
    }
})

// ----------------------------------

app.get('/status', (req, res) => {
  res.json({ connected: connStatus.connected, qr: connStatus.qr, connecting: connStatus.connecting, lastError: connStatus.lastError })
})

app.get('/health', (req, res) => {
  res.status(200).json({ ok: true, connected: connStatus.connected })
})

app.get('/qr', (req, res) => {
  res.json({ qr: connStatus.qr || null })
})

app.get('/active-user', (req, res) => {
  try {
    let jid = sock && sock.user && sock.user.id ? sock.user.id : null
    if (!jid) {
      const credsPath = path.resolve(process.cwd(), './wa-gateway/auth/creds.json')
      if (fs.existsSync(credsPath)) {
        try {
          const raw = fs.readFileSync(credsPath, 'utf8')
          const j = JSON.parse(raw)
          jid = (j && j.me && (j.me.id || (j.me.wid && j.me.wid.id))) || null
        } catch {}
      }
    }
    if (!jid) return res.status(404).json({ ok: false, error: 'not_found' })
    const digits = String(jid).split('@')[0].replace(/[^0-9]/g, '')
    const num = normalizeDigits(digits)
    if (!num) return res.status(404).json({ ok: false, error: 'not_found' })
    return res.json({ ok: true, number: num })
  } catch (e) {
    return res.status(500).json({ ok: false, error: 'active_user_failed' })
  }
})

app.post('/reset', async (req, res) => {
  try {
    const authBase = process.env.AUTH_DIR || './wa-gateway/auth'
    const authDir1 = path.resolve(process.cwd(), authBase)
    const rawNumber = (req.body && req.body.number) || (req.query && req.query.number) || ''
    const digits = String(rawNumber || '').replace(/[^0-9]/g, '')
    if (!digits) { return res.status(400).json({ ok: false, error: 'missing_number' }) }
    const deleteTargeted = (dir) => {
      try {
        if (!fs.existsSync(dir)) return
        const files = fs.readdirSync(dir)
        for (const f of files) {
          const full = path.join(dir, f)
          const isSession = f.startsWith('session-') && f.includes(digits)
          const isBroadcast = f.startsWith('sender-key-status@broadcast--') && f.includes(`--${digits}--`)
          const isSenderKey = f.startsWith('sender-key-') && f.includes(`--${digits}--`)
          if (isSession || isBroadcast || isSenderKey) {
            try { fs.rmSync(full, { recursive: true, force: true }); logger.info({ msg: 'deleted cred file', file: full }) } catch {}
          }
        }
        const credsPath = path.join(dir, 'creds.json')
        if (fs.existsSync(credsPath)) {
          try {
            const raw = fs.readFileSync(credsPath, 'utf8')
            const j = JSON.parse(raw)
            const meJid = (j && j.me && (j.me.id || (j.me.wid && j.me.wid.id))) || ''
            const meDigits = String(meJid).split('@')[0].replace(/[^0-9]/g, '')
            if (!meDigits || meDigits === digits) {
              fs.rmSync(credsPath, { recursive: true, force: true })
              logger.info({ msg: 'deleted creds.json', path: credsPath })
            }
          } catch {
            try { fs.rmSync(credsPath, { recursive: true, force: true }); logger.info({ msg: 'deleted creds.json (fallback)', path: credsPath }) } catch {}
          }
        }
      } catch {}
    }
    manualReset = true
    autoReconnectEnabled = false
    deleteTargeted(authDir1)
    connStatus.connected = false
    connStatus.qr = null
    broadcast({ type: 'status', connected: false })
    await startBaileys()
    res.json({ ok: true })
  } catch (e) {
    logger.error({ err: e })
    res.status(500).json({ ok: false })
  }
})

app.get('/pair-code', async (req, res) => {
  try {
    if (!sock) return res.status(500).json({ ok: false, error: 'not_ready' })
    const number = String(req.query.number || '').replace(/[^0-9]/g, '')
    if (!number) return res.status(400).json({ ok: false, error: 'missing_number' })
    const normalize = (n) => (n.startsWith('62') ? n : n.startsWith('0') ? '62' + n.slice(1) : n.startsWith('8') ? '62' + n : n)
    if (connStatus.connected) return res.status(400).json({ ok: false, error: 'already_connected' })
    const code = await sock.requestPairingCode(normalize(number))
    res.json({ ok: true, code })
  } catch (e) {
    logger.error({ err: e })
    res.status(500).json({ ok: false, error: e?.message || 'pair_code_failed' })
  }
})

app.post('/send', async (req, res) => {
  try {
    const to = String(req.body.to || '').replace(/[^0-9]/g, '')
    const message = String(req.body.message || '')
    const normalize = (n) => (n.startsWith('62') ? n : n.startsWith('0') ? '62' + n.slice(1) : n.startsWith('8') ? '62' + n : n)
    const jid = normalize(to) + '@s.whatsapp.net'
    
    // Persist outgoing message
    const contactRow = stmtGetContactByPhone.get(normalize(to))
    let contactId = contactRow?.id
    if (!contactId) {
        const info = stmtInsertContact.run({ name: normalize(to), phone: normalize(to), unread: 0, lastAt: new Date().toISOString() })
        contactId = info.lastInsertRowid
    }
    
    // Store in DB
    const waId = 'OUT-' + Date.now()
    stmtUpsertMessage.run({
        contactId,
        direction: 'out',
        text: message,
        status: 'sent',
        waId,
        senderJid: sock?.user?.id || 'me',
        createdAt: new Date().toISOString()
    })

    if (!sock || !sock.user) return res.status(503).json({ ok: false, error: 'not_connected' })
    await sock.sendMessage(jid, { text: message })
    
    // Update contact last message
    stmtUpdateContact.run({ id: contactId, unread: 0, lastAt: new Date().toISOString(), name: null })
    
    broadcast({ type: 'sent', to, text: message, ts: Date.now() })
    res.json({ ok: true })
  } catch (e) {
    logger.error({ err: e })
    res.status(500).json({ ok: false, error: e?.message || 'send_failed' })
  }
})

startBaileys().catch((e) => logger.error({ err: e }))
