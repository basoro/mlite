<?php

namespace Plugins\Satu_Sehat\Services;

/**
 * Sinkronisasi ERM Rawat Jalan ke SATUSEHAT memakai Bundle type=transaction,
 * lengkap dengan pencatatan log request/response dan pemetaan resource hasil.
 */
class SatuSehatSyncService
{
  /** @var \Systems\Main */
  private $core;

  /** @var SatuSehatErmRalanService */
  private $erm;

  /** @var SatuSehatAuthService */
  private $auth;

  /** @var SatuSehatResourceMappingService */
  private $mapping;

  /** @var SatuSehatValidator */
  private $validator;

  public function __construct($core, SatuSehatErmRalanService $erm)
  {
    $this->core = $core;
    $this->erm = $erm;
    $this->auth = $erm->auth();
    $this->mapping = $erm->mapping();
    $this->validator = new SatuSehatValidator();
    $this->mapping->ensureTables();
  }

  /**
   * Sinkronisasi satu kunjungan rawat jalan.
   *
   * @return array{status:string,message:string,errors:array,warnings:array,http_code:int,parsed:?array,log_id:string}
   */
  public function sync($no_rawat)
  {
    $no_rawat = $this->erm->normalizeNoRawat($no_rawat);
    $result = [
      'status' => 'error',
      'message' => '',
      'errors' => [],
      'warnings' => [],
      'http_code' => 0,
      'parsed' => null,
      'log_id' => '',
    ];

    $emr = $this->erm->getEmr($no_rawat);
    if (empty($emr['reg_periksa'])) {
      $result['message'] = 'Kunjungan ' . $this->erm->ermLabel() . ' tidak ditemukan.';
      $result['errors'][] = $result['message'];
      return $result;
    }

    $pipeline = $this->erm->buildResourceEntries($no_rawat);
    $entries = $pipeline['entries'];
    $validation = $this->validator->validate($entries);
    $result['warnings'] = $validation['warnings'];

    $bundle = $this->erm->buildTransactionBundle($no_rawat);
    $requestJson = json_encode($bundle, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    if (!$validation['valid']) {
      $result['errors'] = $validation['errors'];
      $result['message'] = 'Data ERM belum lengkap untuk dikirim.';
      $result['log_id'] = $this->log($no_rawat, 'invalid', 0, 0, count($entries), $result['message'], $requestJson, json_encode(['errors' => $validation['errors']], JSON_UNESCAPED_UNICODE));
      $this->mapping->markSent($no_rawat, 'invalid', $result['message']);
      return $result;
    }

    $response = $this->auth->fhirRequest('POST', $this->auth->fhirUrl(), $bundle);
    $parsed = SatuSehatResponseParser::parse($response['body'], $entries);
    $ok = $response['error'] === '' && $response['http_code'] >= 200 && $response['http_code'] < 300 && $parsed['ok'];

    $message = $ok
      ? 'Bundle ' . $this->erm->ermLabel() . ' berhasil dikirim ke SATUSEHAT.'
      : ($response['error'] !== '' ? $response['error'] : $parsed['message']);
    if (!$ok && $parsed['errors']) {
      $result['errors'] = $parsed['errors'];
      $message .= ' ' . implode(' ', array_slice($parsed['errors'], 0, 3));
    }

    $result['status'] = $ok ? 'success' : 'error';
    $result['message'] = $message;
    $result['http_code'] = $response['http_code'];
    $result['parsed'] = $parsed;
    $result['log_id'] = $this->log(
      $no_rawat,
      $ok ? 'terkirim' : 'gagal',
      $response['http_code'],
      $response['duration_ms'],
      count($entries),
      $message,
      $requestJson,
      $response['body']
    );

    // Simpan ID resource hasil + status pengiriman.
    if ($ok) {
      $this->mapping->registerCreatedResources($no_rawat, $parsed['created']);
      $ctx = $pipeline['ctx'];
      $save = ['status_kirim' => 'terkirim'];
      if (!empty($parsed['created']['Encounter'][0])) {
        $save['encounter_id'] = (string) $parsed['created']['Encounter'][0];
      }
      $refPatient = (string) (($ctx['ref']['patient']) ?? '');
      if ($refPatient !== '') {
        $save['patient_id'] = substr($refPatient, strlen('Patient/'));
      }
      $refPractitioner = (string) (($ctx['ref']['practitioner']) ?? '');
      if ($refPractitioner !== '') {
        $save['practitioner_id'] = substr($refPractitioner, strlen('Practitioner/'));
      }
      $refLocation = (string) (($ctx['ref']['location']) ?? '');
      if ($refLocation !== '') {
        $save['location_id'] = substr($refLocation, strlen('Location/'));
      }
      $save['organization_id'] = (string) $ctx['organization_id'];
      $this->mapping->saveVisitMapping($no_rawat, $save);
    } else {
      $this->mapping->markSent($no_rawat, 'gagal', $message);
    }

    return $result;
  }

  /**
   * Kirim Bundle mentah (dipakai bila ingin pratinjau lalu kirim).
   */
  public function sendBundle(array $bundle, $no_rawat = '')
  {
    $entries = is_array($bundle['entry'] ?? null) ? $bundle['entry'] : [];
    $response = $this->auth->fhirRequest('POST', $this->auth->fhirUrl(), $bundle);
    $parsed = SatuSehatResponseParser::parse($response['body'], $entries);
    $ok = $response['error'] === '' && $response['http_code'] >= 200 && $response['http_code'] < 300 && $parsed['ok'];

    if ($no_rawat !== '') {
      $this->log(
        $no_rawat,
        $ok ? 'terkirim' : 'gagal',
        $response['http_code'],
        $response['duration_ms'],
        count($entries),
        $parsed['message'],
        json_encode($bundle, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        $response['body']
      );
    }

    return [
      'status' => $ok ? 'success' : 'error',
      'message' => $parsed['message'],
      'http_code' => $response['http_code'],
      'parsed' => $parsed,
    ];
  }

  /**
   * Catat satu baris log sinkronisasi.
   */
  public function log($no_rawat, $status, $httpCode = 0, $durationMs = 0, $jumlahResource = 0, $message = '', $request = null, $response = null)
  {
    $id = date('YmdHis') . '-' . substr(md5((string) $no_rawat . microtime(true)), 0, 8);
    try {
      $stmt = $this->core->db()->pdo()->prepare(
        'INSERT INTO ' . SatuSehatResourceMappingService::TABLE_LOG . '
         (id, no_rawat, status, http_code, duration_ms, jumlah_resource, message, request, response, created_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
      );
      $stmt->execute([
        $id,
        (string) $no_rawat,
        (string) $status,
        (int) $httpCode,
        (int) $durationMs,
        (int) $jumlahResource,
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

  /** Ambil log sinkronisasi (opsional per no_rawat). */
  public function getLogs($no_rawat = null, $limit = 100, $offset = 0)
  {
    try {
      $sql = 'SELECT * FROM ' . SatuSehatResourceMappingService::TABLE_LOG;
      $binds = [];
      if ($no_rawat !== null && $no_rawat !== '') {
        $sql .= ' WHERE no_rawat = ?';
        $binds[] = $this->erm->normalizeNoRawat($no_rawat);
      }
      $sql .= ' ORDER BY created_at DESC, id DESC LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset;
      $stmt = $this->core->db()->pdo()->prepare($sql);
      $stmt->execute($binds);
      $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
      return is_array($rows) ? $rows : [];
    } catch (\Throwable $e) {
      return [];
    }
  }

  public function countLogs($no_rawat = null)
  {
    try {
      $sql = 'SELECT COUNT(*) FROM ' . SatuSehatResourceMappingService::TABLE_LOG;
      $binds = [];
      if ($no_rawat !== null && $no_rawat !== '') {
        $sql .= ' WHERE no_rawat = ?';
        $binds[] = $this->erm->normalizeNoRawat($no_rawat);
      }
      $stmt = $this->core->db()->pdo()->prepare($sql);
      $stmt->execute($binds);
      return (int) $stmt->fetchColumn();
    } catch (\Throwable $e) {
      return 0;
    }
  }
}
