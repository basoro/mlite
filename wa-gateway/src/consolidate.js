import 'dotenv/config'
import { createClient } from '@supabase/supabase-js'

const url = process.env.SUPABASE_URL
const key = process.env.SUPABASE_SERVICE_ROLE_KEY
if (!url || !key) {
  console.error('Missing SUPABASE_URL or SUPABASE_SERVICE_ROLE_KEY')
  process.exit(1)
}
const supabase = createClient(url, key)

const isValidPhone = (p) => /^62\d{6,}$/.test(String(p || ''))

async function consolidate() {
  const { data: contacts } = await supabase.from('whatsapp_contacts').select('id,name,phone')
  const invalid = (contacts || []).filter((c) => !isValidPhone(c.phone))
  for (const c of invalid) {
    // Move messages based on sender_jid if present
    const { data: msgs } = await supabase
      .from('whatsapp_messages')
      .select('id,contact_id,direction,text,wa_message_id,sender_jid,created_at')
      .eq('contact_id', c.id)
    const toMove = (msgs || []).filter((m) => m.sender_jid && isValidPhone(String(m.sender_jid).split('@')[0].replace(/[^0-9]/g, '')))
    for (const m of toMove) {
      const phone = String(m.sender_jid).split('@')[0].replace(/[^0-9]/g, '')
      const { data: target } = await supabase.from('whatsapp_contacts').select('id').eq('phone', phone).maybeSingle()
      let targetId = target?.id
      if (!targetId) {
        const { data: upserted } = await supabase
          .from('whatsapp_contacts')
          .upsert({ name: phone, phone, last_message_at: new Date().toISOString() }, { onConflict: 'phone' })
          .select('id')
          .limit(1)
        targetId = Array.isArray(upserted) ? upserted[0]?.id : upserted?.id
      }
      if (!targetId) continue
      await supabase.from('whatsapp_messages').update({ contact_id: targetId }).eq('id', m.id)
      await supabase.from('whatsapp_contacts').update({ last_message_at: new Date().toISOString() }).eq('id', targetId)
    }
    // Delete contact if no messages remain
    const { count } = await supabase.from('whatsapp_messages').select('*', { count: 'exact', head: true }).eq('contact_id', c.id)
    if ((count || 0) === 0) {
      await supabase.from('whatsapp_contacts').delete().eq('id', c.id)
    }
  }
  console.log('Consolidation completed')
}

consolidate().catch((e) => { console.error(e); process.exit(1) })

