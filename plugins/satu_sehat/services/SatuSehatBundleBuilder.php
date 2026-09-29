<?php

namespace Plugins\Satu_Sehat\Services;

/**
 * Perakit Bundle FHIR untuk sub modul ERM Rawat Jalan.
 *
 * Mendukung:
 * - Bundle type=transaction  -> sinkronisasi ke SATUSEHAT (ada request.method/url).
 * - Bundle type=document     -> dokumen ERM (Composition + section, tanpa request).
 */
class SatuSehatBundleBuilder
{
  /**
   * Bentuk satu entri Bundle dari sebuah resource.
   *
   * @param array       $resource Resource FHIR (wajib punya resourceType, idealnya id).
   * @param string|null $uuid     UUID lokal; dihasilkan otomatis bila kosong.
   */
  public static function entry(array $resource, $uuid = null)
  {
    $type = (string) ($resource['resourceType'] ?? '');
    $uuid = $uuid !== null && $uuid !== '' ? (string) $uuid : (string) ($resource['id'] ?? '');
    if ($uuid === '') {
      $uuid = SatuSehatAuthService::uuid();
    }
    $resource['id'] = $uuid;

    return [
      'fullUrl' => 'urn:uuid:' . $uuid,
      'resource' => $resource,
      'request' => [
        'method' => 'POST',
        'url' => $type,
      ],
    ];
  }

  /**
   * Susun Bundle transaction dari daftar entri.
   */
  public function transaction(array $entries)
  {
    $entries = $this->normalizeEntries($entries);
    return [
      'resourceType' => 'Bundle',
      'type' => 'transaction',
      'timestamp' => date('c'),
      'entry' => $entries,
    ];
  }

  /**
   * Susun Bundle dokumen ERM (Composition harus entri pertama).
   *
   * @param array $entries           Entri resource (tanpa request, dilepas otomatis).
   * @param array $compositionResource Resource Composition.
   */
  public function document(array $entries, array $compositionResource)
  {
    $entries = $this->normalizeEntries($entries, true);
    $compositionEntry = [
      'fullUrl' => 'urn:uuid:' . (string) ($compositionResource['id'] ?? SatuSehatAuthService::uuid()),
      'resource' => $compositionResource,
    ];
    array_unshift($entries, $compositionEntry);

    return [
      'resourceType' => 'Bundle',
      'id' => SatuSehatAuthService::uuid(),
      'identifier' => [
        'system' => 'http://sys-ids.kemkes.go.id/erm-ralan',
        'value' => 'ERM-' . date('YmdHis'),
      ],
      'type' => 'document',
      'timestamp' => date('c'),
      'entry' => $entries,
    ];
  }

  /**
   * Gabungkan beberapa kelompok entri (assoc per jenis) menjadi satu daftar.
   */
  public static function flatten(array $grouped)
  {
    $flat = [];
    foreach ($grouped as $entries) {
      if (!is_array($entries)) {
        continue;
      }
      // Bisa berupa satu entri tunggal atau daftar entri.
      if (isset($entries['resource'])) {
        $flat[] = $entries;
        continue;
      }
      foreach ($entries as $entry) {
        if (is_array($entry) && isset($entry['resource'])) {
          $flat[] = $entry;
        }
      }
    }
    return $flat;
  }

  /**
   * Kelompokkan entri berdasarkan resourceType -> [type => [id, ...]].
   */
  public static function groupIdsByType(array $entries)
  {
    $grouped = [];
    foreach ($entries as $entry) {
      $type = (string) ($entry['resource']['resourceType'] ?? '');
      $id = (string) ($entry['resource']['id'] ?? '');
      if ($type !== '' && $id !== '') {
        $grouped[$type][] = $id;
      }
    }
    return $grouped;
  }

  private function normalizeEntries(array $entries, $stripRequest = false)
  {
    $normalized = [];
    foreach ($entries as $entry) {
      if (!is_array($entry) || !isset($entry['resource'])) {
        continue;
      }
      if ($stripRequest) {
        unset($entry['request']);
      }
      $normalized[] = $entry;
    }
    return $normalized;
  }
}
