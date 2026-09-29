<?php

namespace Plugins\Satu_Sehat\Services;

/**
 * Autentikasi OAuth2 (client credentials) dan transport HTTP untuk API FHIR R4 SATUSEHAT.
 *
 * Dipakai oleh seluruh service & resource builder sub modul ERM Rawat Jalan.
 */
class SatuSehatAuthService
{
  /** @var \Systems\Main */
  private $core;

  /** @var array Cache access token untuk 1 proses request. */
  private static $tokenCache = ['token' => '', 'expires' => 0];

  public function __construct($core)
  {
    $this->core = $core;
  }

  public function authUrl()
  {
    return rtrim((string) $this->core->settings->get('satu_sehat.authurl'), '/');
  }

  public function fhirUrl()
  {
    return rtrim((string) $this->core->settings->get('satu_sehat.fhirurl'), '/');
  }

  public function organizationId()
  {
    return trim((string) $this->core->settings->get('satu_sehat.organizationid'));
  }

  /**
   * Offset zona waktu fasyankes sesuai pengaturan (WIB/WITA/WIT), mis. "+07:00".
   */
  public function timeZoneOffset()
  {
    $zone = strtoupper((string) $this->core->settings->get('satu_sehat.zonawaktu'));
    if ($zone === 'WITA') {
      return '+08:00';
    }
    if ($zone === 'WIT') {
      return '+09:00';
    }
    return '+07:00';
  }

  /**
   * Konversi waktu SIMRS ("Y-m-d H:i:s") ke instant FHIR (ISO-8601 + offset).
   */
  public function fhirTime($waktu, $fallbackNow = true)
  {
    $waktu = trim((string) $waktu);
    if ($waktu === '' || strtotime($waktu) === false) {
      if (!$fallbackNow) {
        return '';
      }
      $waktu = date('Y-m-d H:i:s');
    }
    return date('Y-m-d\TH:i:s', strtotime($waktu)) . $this->timeZoneOffset();
  }

  /**
   * UUID v4 untuk fullUrl/id resource lokal (urn:uuid:...).
   */
  public static function uuid()
  {
    return sprintf(
      '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
      mt_rand(0, 0xffff), mt_rand(0, 0xffff),
      mt_rand(0, 0xffff),
      mt_rand(0, 0x0fff) | 0x4000,
      mt_rand(0, 0x3fff) | 0x8000,
      mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
  }

  /**
   * Ambil access token OAuth2. Hasil di-cache sampai hampir kedaluwarsa.
   *
   * @return string Token, atau string kosong bila gagal.
   */
  public function getAccessToken(): string
  {
    if (self::$tokenCache['token'] !== '' && self::$tokenCache['expires'] > time()) {
      return self::$tokenCache['token'];
    }

    $clientId = trim((string) $this->core->settings->get('satu_sehat.clientid'));
    $secretKey = trim((string) $this->core->settings->get('satu_sehat.secretkey'));
    if ($this->authUrl() === '' || $clientId === '') {
      return '';
    }

    $ch = curl_init();
    curl_setopt_array($ch, [
      CURLOPT_URL => $this->authUrl() . '/accesstoken?grant_type=client_credentials',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
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

    self::$tokenCache['token'] = $token;
    self::$tokenCache['expires'] = time() + max(60, (int) ($json['expires_in'] ?? 3600) - 60);
    return $token;
  }

  /**
   * Header standar untuk request FHIR.
   */
  public function authHeaders(array $extra = [])
  {
    $headers = [
      'Accept: application/fhir+json',
      'Content-Type: application/fhir+json',
    ];
    $token = $this->getAccessToken();
    if ($token !== '') {
      $headers[] = 'Authorization: Bearer ' . $token;
    }
    return array_merge($headers, $extra);
  }

  /**
   * Lakukan request HTTP ke server FHIR.
   *
   * @param string     $method  GET/POST/PUT/DELETE.
   * @param string     $path    Path relatif (mis. "Encounter") atau URL absolut.
   * @param array|null $payload Body JSON (diabaikan untuk GET).
   * @return array{body:string,json:?array,http_code:int,error:string,duration_ms:int}
   */
  public function fhirRequest($method, $path, $payload = null)
  {
    $url = preg_match('#^https?://#i', (string) $path)
      ? (string) $path
      : $this->fhirUrl() . '/' . ltrim((string) $path, '/');

    $ch = curl_init($url);
    $options = [
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_CONNECTTIMEOUT => 10,
      CURLOPT_TIMEOUT => 60,
      CURLOPT_HTTPHEADER => $this->authHeaders(),
      CURLOPT_CUSTOMREQUEST => strtoupper((string) $method),
      CURLOPT_SSL_VERIFYPEER => true,
    ];
    if ($payload !== null && strtoupper((string) $method) !== 'GET') {
      $options[CURLOPT_POSTFIELDS] = is_string($payload)
        ? $payload
        : json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
    curl_setopt_array($ch, $options);

    $started = microtime(true);
    $body = (string) curl_exec($ch);
    $error = (string) curl_error($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [
      'body' => $body,
      'json' => json_decode($body, true),
      'http_code' => $httpCode,
      'error' => $error,
      'duration_ms' => (int) round((microtime(true) - $started) * 1000),
    ];
  }

  /**
   * Cari ID IHS pasien dari SATUSEHAT berdasarkan NIK.
   *
   * @return string ID IHS pasien atau string kosong.
   */
  public function lookupPatientId($nik)
  {
    $nik = trim((string) $nik);
    if ($nik === '') {
      return '';
    }
    $resp = $this->fhirRequest('GET', 'Patient?identifier=' . urlencode('https://fhir.kemkes.go.id/id/nik|' . $nik));
    $entry = $resp['json']['entry'][0]['resource']['id'] ?? '';
    return (string) $entry;
  }
}
