<?php

namespace Plugins\Satu_Sehat\Services;

/**
 * RME Nasional SATUSEHAT (ChaRME).
 *
 * Alur: OAuth2 client credentials -> Consent Health Link (POST /ssrme/v2/ntl/chl)
 * -> Open RME Nasional (POST /ssrme/v2/ntl/shl).
 *
 * Catatan respons: API dapat mengembalikan HTTP 200 tetapi body-nya
 * {"success": false, ...} (mis. "failed to create consent health link",
 * "wrong organization"). Karena itu keputusan sukses/gagal TIDAK boleh
 * hanya dari HTTP status — flag `success` pada body wajib diperiksa.
 *
 * Consent bersifat consent-based: bila pasien belum memberi persetujuan, API SHL
 * mengembalikan CONSENT_REQUIRED beserta petunjuk untuk membuat Consent Health
 * Link yang dibuka pasien melalui SATUSEHAT Mobile.
 */
class SatuSehatRmeNasionalService
{
  /** Status log khusus RME Nasional (tabel log sama dengan ERM). */
  const LOG_CHL = 'chl';
  const LOG_SHL = 'shl';
  const LOG_CONSENT = 'consent';

  /** @var \Systems\Main */
  private $core;

  /** @var SatuSehatAuthService */
  private $auth;

  /** @var string no_rawat kunjungan aktif (untuk logging). */
  private $noRawat = '';

  public function __construct($core, $noRawat = '')
  {
    $this->core = $core;
    $this->auth = new SatuSehatAuthService($core);
    $this->noRawat = (string) $noRawat;
  }

  /**
   * Base URL API RME/ChaRME.
   *
   * Diambil dari host setting 'satu_sehat.rme_authurl' bila diisi (mis.
   * https://api-satusehat.kemkes.go.id/oauth2/v1). Bila kosong, diturunkan dari
   * host authurl FHIR (sandbox <-> sandbox, production <-> production).
   */
  public function rmeBaseUrl()
  {
    $rmeAuthUrl = rtrim(trim((string) $this->core->settings->get('satu_sehat.rme_authurl')), '/');
    if ($rmeAuthUrl !== '') {
      $host = (string) parse_url($rmeAuthUrl, PHP_URL_HOST);
      $scheme = (string) (parse_url($rmeAuthUrl, PHP_URL_SCHEME) ?: 'https');
      return $host !== '' ? $scheme . '://' . $host : '';
    }

    // Fallback: turunkan dari authurl FHIR.
    $authUrl = $this->auth->authUrl();
    if ($authUrl === '') {
      return '';
    }
    $host = (string) parse_url($authUrl, PHP_URL_HOST);
    $scheme = (string) (parse_url($authUrl, PHP_URL_SCHEME) ?: 'https');
    return $host !== '' ? $scheme . '://' . $host : '';
  }

  /**
   * URL lengkap endpoint CHLink (Consent Health Link).
   *
   * Prioritas: setting 'satu_sehat.chlurl' (URL penuh) -> turunan dari
   * rmeBaseUrl()/authurl (host + path /ssrme/v2/ntl/chl).
   */
  public function chlUrl()
  {
    $setting = rtrim(trim((string) $this->core->settings->get('satu_sehat.chlurl')), '/');
    if ($setting !== '') {
      return $setting;
    }
    $base = $this->rmeBaseUrl();
    return $base !== '' ? $base . '/ssrme/v2/ntl/chl' : '';
  }

  /**
   * URL lengkap endpoint SHLink (Smart Health Link / buka RME Nasional).
   *
   * Prioritas: setting 'satu_sehat.shlurl' (URL penuh) -> turunan dari
   * rmeBaseUrl()/authurl (host + path /ssrme/v2/ntl/shl).
   */
  public function shlUrl()
  {
    $setting = rtrim(trim((string) $this->core->settings->get('satu_sehat.shlurl')), '/');
    if ($setting !== '') {
      return $setting;
    }
    $base = $this->rmeBaseUrl();
    return $base !== '' ? $base . '/ssrme/v2/ntl/shl' : '';
  }

  /**
   * Base URL endpoint token OAuth untuk API RME.
   *
   * Dipakai getRmeAccessToken(): prioritas host 'satu_sehat.rme_authurl',
   * lalu host endpoint CHL/SHL, lalu host authurl FHIR.
   */
  private function rmeTokenBaseUrl()
  {
    foreach (['satu_sehat.rme_authurl', 'satu_sehat.chlurl', 'satu_sehat.shlurl'] as $key) {
      $url = rtrim(trim((string) $this->core->settings->get($key)), '/');
      if ($url !== '') {
        $host = (string) parse_url($url, PHP_URL_HOST);
        $scheme = (string) (parse_url($url, PHP_URL_SCHEME) ?: 'https');
        if ($host !== '') {
          return $scheme . '://' . $host;
        }
      }
    }
    return $this->rmeBaseUrl();
  }

  /**
   * Catat satu baris log ke tabel log yang sama dengan ERM
   * (mlite_satu_sehat_erm_log).
   */
  public function log($status, $httpCode = 0, $durationMs = 0, $message = '', $request = null, $response = null)
  {
    if ($this->noRawat === '') {
      return '';
    }
    $id = date('YmdHis') . '-' . substr(md5('rme' . $this->noRawat . microtime(true)), 0, 8);
    try {
      $stmt = $this->core->db()->pdo()->prepare(
        'INSERT INTO ' . SatuSehatResourceMappingService::TABLE_LOG .
        ' (id, no_rawat, status, http_code, duration_ms, jumlah_resource, message, request, response, created_at)' .
        ' VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
      );
      $stmt->execute([
        $id,
        $this->noRawat,
        (string) $status,
        (int) $httpCode,
        (int) $durationMs,
        0,
        (string) $message,
        $request === null ? null : (string) $request,
        $response === null ? null : (string) $response,
        date('Y-m-d H:i:s'),
      ]);
    } catch (\Throwable $e) {
      return '';
    }
    return $id;
  }

  /**
   * Baris log RME Nasional terakhir untuk sebuah kunjungan (status chl/shl/consent).
   */
  public static function lastRmeLog($core, $noRawat)
  {
    try {
      $stmt = $core->db()->pdo()->prepare(
        'SELECT * FROM ' . SatuSehatResourceMappingService::TABLE_LOG .
        " WHERE no_rawat = ? AND status IN ('chl', 'shl', 'consent')" .
        ' ORDER BY created_at DESC, id DESC LIMIT 1'
      );
      $stmt->execute([(string) $noRawat]);
      $row = $stmt->fetch(\PDO::FETCH_ASSOC);
      return is_array($row) ? $row : null;
    } catch (\Throwable $e) {
      return null;
    }
  }

  /**
   * URL persetujuan (verification_url) dari log consent/chl terakhir bila masih relevan.
   */
  public static function lastConsentUrl($core, $noRawat)
  {
    $row = self::lastRmeLog($core, $noRawat);
    if (!$row || !in_array((string) $row['status'], ['consent', 'chl'], true)) {
      return '';
    }
    $json = json_decode((string) ($row['response'] ?? ''), true);
    if (is_array($json)) {
      $data = $json['data'] ?? null;
      if (is_string($data) && preg_match('#^https?://#i', trim($data))) {
        return trim($data);
      }
      if (is_array($data)) {
        return (string) ($data['url'] ?? ($data['verification_url'] ?? ''));
      }
      return (string) ($json['verification_url'] ?? ($json['url'] ?? ''));
    }
    return '';
  }

  /**
   * Access token khusus RME/ChaRME.
   *
   * Token OAuth berlaku per-environment: bila host RME berbeda dari host
   * authurl FHIR (mis. FHIR sandbox, RME production), token harus diminta
   * dari host RME dengan kredensial yang sama. Hasil di-cache terpisah.
   */
  public function getRmeAccessToken()
  {
    static $cache = ['token' => '', 'expires' => 0];
    if ($cache['token'] !== '' && $cache['expires'] > time()) {
      return $cache['token'];
    }

    $base = $this->rmeTokenBaseUrl();
    if ($base === '') {
      return '';
    }

    // Host RME sama dengan host authurl FHIR -> pakai cache token FHIR.
    $authHost = (string) parse_url($this->auth->authUrl(), PHP_URL_HOST);
    $rmeHost = (string) parse_url($base, PHP_URL_HOST);
    if ($authHost !== '' && strcasecmp($authHost, $rmeHost) === 0) {
      return $this->auth->getAccessToken();
    }

    $clientId = trim((string) $this->core->settings->get('satu_sehat.clientid'));
    $secretKey = trim((string) $this->core->settings->get('satu_sehat.secretkey'));
    if ($clientId === '') {
      return '';
    }

    $ch = curl_init($base . '/oauth2/v1/accesstoken?grant_type=client_credentials');
    curl_setopt_array($ch, [
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_CONNECTTIMEOUT => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS => 'client_id=' . urlencode($clientId) . '&client_secret=' . urlencode($secretKey),
      CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded', 'Accept: application/json'],
      CURLOPT_SSL_VERIFYPEER => true,
    ]);
    $body = (string) curl_exec($ch);
    curl_close($ch);

    $json = json_decode($body, true);
    $token = (string) (is_array($json) ? ($json['access_token'] ?? '') : '');
    if ($token === '') {
      return '';
    }
    $cache['token'] = $token;
    $cache['expires'] = time() + max(60, (int) ($json['expires_in'] ?? 3600) - 60);
    return $token;
  }

  /**
   * POST JSON ke endpoint RME Nasional dengan Bearer token.
   *
   * @return array{json:array|null,http_code:int,error:string}
   */
  private function postJson($url, array $payload)
  {
    $token = $this->getRmeAccessToken();
    if ($token === '' || $url === '') {
      return ['json' => null, 'http_code' => 0, 'error' => 'Token/konfigurasi SATUSEHAT belum tersedia.'];
    }

    $ch = curl_init($url);
    curl_setopt_array($ch, [
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_CONNECTTIMEOUT => 10,
      CURLOPT_TIMEOUT => 60,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
      CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: Bearer ' . $token,
      ],
      CURLOPT_SSL_VERIFYPEER => true,
    ]);
    $body = (string) curl_exec($ch);
    $error = (string) curl_error($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $json = json_decode($body, true);
    if (!is_array($json) && $body !== '') {
      $json = ['raw' => $body];
    }

    return ['json' => is_array($json) ? $json : null, 'http_code' => $httpCode, 'error' => $error];
  }

  /**
   * Payload standar CHL/SHL untuk sebuah kunjungan.
   *
   * Semua nilai di-trim: ChaRME sensitif terhadap ketidakcocokan string
   * (mis. trailing space pada nama praktisi dari database dapat membuat
   * pembuatan consent link gagal).
   *
   * @param array $ctx Array berisi patient_id, patient_name, practitioner_id,
   *                   practitioner_name, organization_id, organization_name,
   *                   opsional type_medical_summary (mis. 'EMERGENCY' untuk
   *                   bypass consent di CHL).
   */
  public function buildPayload(array $ctx)
  {
    $payload = [
      'patient_id' => trim((string) ($ctx['patient_id'] ?? '')),
      'patient_name' => trim(preg_replace('/\s+/', ' ', (string) ($ctx['patient_name'] ?? ''))),
      'practitioner_id' => trim((string) ($ctx['practitioner_id'] ?? '')),
      'practitioner_name' => trim(preg_replace('/\s+/', ' ', (string) ($ctx['practitioner_name'] ?? ''))),
      'organization_id' => trim((string) ($ctx['organization_id'] ?? '')),
      'organization_name' => trim(preg_replace('/\s+/', ' ', (string) ($ctx['organization_name'] ?? ''))),
    ];
    // Bypass consent darurat (hanya relevan untuk CHL).
    if (!empty($ctx['type_medical_summary'])) {
      $payload['type_medical_summary'] = strtoupper(trim((string) $ctx['type_medical_summary']));
    }
    return $payload;
  }

  /**
   * Interpretasi envelope respons RME Nasional.
   *
   * Envelope yang dikenal:
   *   {"success": true,  "code": 200, "message": "...",
   *    "data": {"shlinkId": "...", "verificationUrl": "https://...",
   *             "expiredAt": "..."}}                                  -> sukses
   *   {"status": 200, "error": false, "message": "store success",
   *    "data": {"verificationUrl": "..."}}                           -> sukses (SHL)
   *   {"success": false, "code": 400, "data": "wrong organization"}          -> gagal
   *   {"success": false, "code": 403, "message": "consent required",
   *    "data": {"code": "CONSENT_REQUIRED"}, "request_id": "..."}            -> consent_required
   *
   * HTTP 200 dengan success=false atau error=true dianggap GAGAL.
   *
   * @return array{outcome:string, message:string, url:string, request_id:string}
   */
  private function interpret($resp, $successMessage)
  {
    if ($resp['error'] !== '') {
      return [
        'outcome' => 'error',
        'message' => 'Gagal menghubungi server SATUSEHAT: ' . $resp['error'],
        'url' => '',
        'request_id' => '',
      ];
    }

    $json = is_array($resp['json']) ? $resp['json'] : null;
    if ($json === null) {
      return [
        'outcome' => 'error',
        'message' => 'Response tidak valid dari server SATUSEHAT (HTTP ' . $resp['http_code'] . ').',
        'url' => '',
        'request_id' => '',
      ];
    }

    $successFlag = $json['success'] ?? null;
    $errorFlag = $json['error'] ?? null;
    $innerCode = isset($json['code']) ? (string) $json['code'] : '';
    $data = $json['data'] ?? null;
    $dataCode = is_array($data) ? strtoupper((string) ($data['code'] ?? '')) : '';
    $requestId = (string) ($json['request_id'] ?? '');

    $message = (string) ($json['message'] ?? '');
    if ($message === '' && is_string($data)) {
      $message = $data;
    }
    if ($message === '' && is_array($data)) {
      $message = (string) ($data['message'] ?? '');
    }

    // Ekstraksi URL dari data (string URL) atau key verificationUrl/shlinkUrl/url/verification_url.
    $url = '';
    if (is_string($data) && preg_match('#^https?://#i', trim($data))) {
      $url = trim($data);
    } elseif (is_array($data)) {
      $url = (string) ($data['verificationUrl'] ?? ($data['shlinkUrl'] ?? ($data['url'] ?? ($data['verification_url'] ?? ''))));
    }
    if ($url === '') {
      $url = (string) ($json['verificationUrl'] ?? ($json['shlinkUrl'] ?? ($json['url'] ?? ($json['verification_url'] ?? ''))));
    }

    // Consent belum diberikan pasien.
    if ($dataCode === 'CONSENT_REQUIRED') {
      return [
        'outcome' => 'consent_required',
        'message' => $message !== '' ? $message : 'Consent pasien diperlukan.',
        'url' => '',
        'request_id' => $requestId,
      ];
    }

    // Flag eksplisit gagal — meski HTTP 200.
    if ($successFlag === false || $errorFlag === true) {
      $suffix = $innerCode !== '' && $innerCode !== (string) $resp['http_code'] ? ' (kode ' . $innerCode . ')' : '';
      return [
        'outcome' => 'error',
        'message' => ($message !== '' ? $message : 'Permintaan ditolak SATUSEHAT.') . ' (HTTP ' . $resp['http_code'] . ')' . $suffix,
        'url' => '',
        'request_id' => $requestId,
      ];
    }

    $httpOk = $resp['http_code'] >= 200 && $resp['http_code'] < 300;
    $envelopeOk = ($successFlag === true || $successFlag === null) && $errorFlag !== true;
    if ($envelopeOk && $httpOk && $url !== '') {
      return [
        'outcome' => 'success',
        'message' => $message !== '' ? $message : $successMessage,
        'url' => $url,
        'request_id' => $requestId,
      ];
    }

    return [
      'outcome' => 'error',
      'message' => ($message !== '' ? $message : 'Permintaan gagal.') . ' (HTTP ' . $resp['http_code'] . ')',
      'url' => '',
      'request_id' => $requestId,
    ];
  }

  /** Encode payload/response untuk kolom log. */
  private function jsonForLog($value)
  {
    if (is_array($value)) {
      return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
    return (string) $value;
  }

  /**
   * Buat Consent Health Link (POST /ssrme/v2/ntl/chl).
   *
   * @return array ['status'=>success|error, 'message'=>..., 'data'=>[url, http_code, raw]]
   */
  public function createConsentHealthLink(array $ctx)
  {
    $endpoint = $this->chlUrl();
    if ($endpoint === '') {
      $message = 'URL endpoint Consent Health Link (chlurl/authurl) belum dikonfigurasi.';
      $this->log(self::LOG_CHL, 0, 0, 'Gagal: ' . $message);
      return ['status' => 'error', 'message' => $message, 'data' => ['url' => '', 'http_code' => 0, 'raw' => null]];
    }

    $payload = $this->buildPayload($ctx);
    $started = microtime(true);
    $resp = $this->postJson($endpoint, $payload);
    $durationMs = (int) round((microtime(true) - $started) * 1000);
    $parsed = $this->interpret($resp, 'Consent Health Link berhasil dibuat.');

    $this->log(
      self::LOG_CHL,
      $resp['http_code'],
      $durationMs,
      ($parsed['outcome'] === 'success' ? '' : 'Gagal: ') . $parsed['message'],
      $this->jsonForLog($payload),
      $this->jsonForLog($resp['json'] ?? $resp['error'])
    );

    if ($parsed['outcome'] === 'success') {
      return [
        'status' => 'success',
        'message' => $parsed['message'],
        'data' => ['url' => $parsed['url'], 'http_code' => $resp['http_code'], 'raw' => $resp['json']],
      ];
    }

    return [
      'status' => 'error',
      'message' => $parsed['message'],
      'data' => ['url' => '', 'http_code' => $resp['http_code'], 'raw' => $resp['json']],
    ];
  }

  /**
   * Buka RME Nasional (POST /ssrme/v2/ntl/shl).
   *
   * @return array ['status'=>success|consent_required|error, ...]
   */
  public function openRmeNasional(array $ctx)
  {
    $endpoint = $this->shlUrl();
    if ($endpoint === '') {
      $message = 'URL endpoint SHLink (shlurl/authurl) belum dikonfigurasi.';
      $this->log(self::LOG_SHL, 0, 0, 'Gagal: ' . $message);
      return ['status' => 'error', 'message' => $message];
    }

    $payload = $this->buildPayload($ctx);
    $started = microtime(true);
    $resp = $this->postJson($endpoint, $payload);
    $durationMs = (int) round((microtime(true) - $started) * 1000);
    $parsed = $this->interpret($resp, 'RME Nasional siap dibuka.');

    if ($parsed['outcome'] === 'consent_required') {
      $this->log(
        self::LOG_SHL,
        $resp['http_code'],
        $durationMs,
        $parsed['message'] . ' (CONSENT_REQUIRED)',
        $this->jsonForLog($payload),
        $this->jsonForLog($resp['json'])
      );
      return [
        'status' => 'consent_required',
        'message' => $parsed['message'],
        'data' => ['http_code' => $resp['http_code'], 'request_id' => $parsed['request_id'], 'raw' => $resp['json']],
      ];
    }

    $this->log(
      self::LOG_SHL,
      $resp['http_code'],
      $durationMs,
      ($parsed['outcome'] === 'success' ? '' : 'Gagal: ') . $parsed['message'],
      $this->jsonForLog($payload),
      $this->jsonForLog($resp['json'] ?? $resp['error'])
    );

    if ($parsed['outcome'] === 'success') {
      return [
        'status' => 'success',
        'message' => $parsed['message'],
        'data' => ['url' => $parsed['url'], 'http_code' => $resp['http_code'], 'raw' => $resp['json']],
      ];
    }

    return ['status' => 'error', 'message' => $parsed['message']];
  }
}
