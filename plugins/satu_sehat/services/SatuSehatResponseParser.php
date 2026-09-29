<?php

namespace Plugins\Satu_Sehat\Services;

/**
 * Parser response FHIR (khususnya Bundle transaction-response) dari SATUSEHAT.
 */
class SatuSehatResponseParser
{
  /**
   * Parse body response pengiriman Bundle transaction.
   *
   * @param string $body          Body response mentah.
   * @param array  $requestEntries Entri bundle terkirim (untuk mencocokkan fullUrl).
   * @return array{ok:bool,message:string,entries:array,created:array,errors:array}
   */
  public static function parse($body, array $requestEntries = [])
  {
    $result = [
      'ok' => false,
      'message' => '',
      'entries' => [],
      'created' => [],
      'errors' => [],
    ];

    $json = json_decode((string) $body, true);
    if (!is_array($json)) {
      $result['errors'][] = 'Response bukan JSON valid.';
      $result['message'] = 'Response bukan JSON valid.';
      return $result;
    }

    // OperationOutcome di level root (mis. token expired / payload ditolak).
    if (($json['resourceType'] ?? '') === 'OperationOutcome') {
      foreach (($json['issue'] ?? []) as $issue) {
        $result['errors'][] = trim(($issue['severity'] ?? 'error') . ': ' . ($issue['diagnostics'] ?? ($issue['details']['text'] ?? '')));
      }
      $result['message'] = implode(' ', $result['errors']) ?: 'SATUSEHAT mengembalikan OperationOutcome.';
      return $result;
    }

    foreach (($json['entry'] ?? []) as $index => $entry) {
      $response = is_array($entry) ? ($entry['response'] ?? []) : [];
      $statusLine = (string) ($response['status'] ?? '');
      $statusCode = (int) $statusLine;
      $location = (string) ($response['location'] ?? '');
      [$resType, $resId] = self::parseLocation($location);

      $requestEntry = $requestEntries[$index] ?? [];
      if ($resType === '' && isset($requestEntry['resource']['resourceType'])) {
        $resType = (string) $requestEntry['resource']['resourceType'];
      }
      if ($resId === '' && isset($requestEntry['resource']['id'])) {
        $resId = (string) $requestEntry['resource']['id'];
      }

      $issues = [];
      $outcome = is_array($entry) ? ($entry['response']['outcome'] ?? null) : null;
      if (is_array($outcome) && ($outcome['resourceType'] ?? '') === 'OperationOutcome') {
        foreach (($outcome['issue'] ?? []) as $issue) {
          $issues[] = trim(($issue['diagnostics'] ?? ($issue['details']['text'] ?? '')));
        }
      }

      $row = [
        'fullUrl' => (string) ($requestEntry['fullUrl'] ?? ''),
        'resourceType' => $resType,
        'id' => $resId,
        'status' => $statusLine,
        'location' => $location,
        'issues' => $issues,
      ];
      $result['entries'][] = $row;

      if ($statusCode >= 200 && $statusCode < 300) {
        if ($resType !== '' && $resId !== '') {
          $result['created'][$resType][] = $resId;
        }
      } else {
        $result['errors'][] = trim(($resType ?: 'Entry #' . ($index + 1)) . ' -> ' . ($statusLine ?: 'gagal') . ($issues ? ' (' . implode('; ', $issues) . ')' : ''));
      }
    }

    $total = count($result['entries']);
    $failed = count($result['errors']);
    $result['ok'] = $total > 0 && $failed === 0;
    if ($total === 0) {
      $result['message'] = 'Response tidak berisi entri Bundle.';
    } elseif ($result['ok']) {
      $result['message'] = $total . ' resource berhasil dibuat di SATUSEHAT.';
    } else {
      $result['message'] = $failed . ' dari ' . $total . ' resource gagal diproses SATUSEHAT.';
    }

    return $result;
  }

  /**
   * Ekstrak (resourceType, id) dari lokasi response FHIR.
   *
   * @return array{0:string,1:string}
   */
  public static function parseLocation($location)
  {
    $location = trim((string) $location);
    if ($location === '') {
      return ['', ''];
    }
    $path = parse_url($location, PHP_URL_PATH);
    $path = trim((string) ($path !== null && $path !== false ? $path : $location), '/');
    $parts = explode('/', $path);
    $id = (string) array_pop($parts);
    $type = (string) array_pop($parts);
    return [$type, $id];
  }

  /**
   * Ambil UUID lokal dari fullUrl "urn:uuid:...".
   */
  public static function uuidFromFullUrl($fullUrl)
  {
    $fullUrl = trim((string) $fullUrl);
    if (strpos($fullUrl, 'urn:uuid:') === 0) {
      return substr($fullUrl, strlen('urn:uuid:'));
    }
    [, $id] = self::parseLocation($fullUrl);
    return $id;
  }

  /**
   * Ringkasan satu baris untuk ditampilkan di log.
   */
  public static function summary(array $parsed)
  {
    $parts = [];
    foreach (($parsed['created'] ?? []) as $type => $ids) {
      $parts[] = $type . ' x' . count($ids);
    }
    return $parts ? implode(', ', $parts) : (string) ($parsed['message'] ?? '');
  }
}
