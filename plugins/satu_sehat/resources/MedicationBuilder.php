<?php

namespace Plugins\Satu_Sehat\Resources;

use Plugins\Satu_Sehat\Services\SatuSehatBundleBuilder;

/**
 * Builder resource FHIR Medication (BAB 15 peresepan obat / BAB 18 pengeluaran obat).
 *
 * Struktur mengikuti StructureDefinition/Medication SATUSEHAT (lihat src/Medication.php):
 * meta.profile + ekstensi MedicationType (NC/CO), identifier sys-ids medication/{org},
 * code KFA, dan form CodeSystem medication-form (kode bentuk sediaan KFA, mis. BS034).
 *
 * Urutan entri selalu sama dengan urutan emr['resep_items'] sehingga
 * MedicationRequestBuilder dapat mencocokkan lewat index + kode_brng.
 */
class MedicationBuilder
{
  public static function build(array $ctx): array
  {
    $emr = (array) ($ctx['emr'] ?? []);
    $entries = [];

    foreach ((array) ($emr['resep_items'] ?? []) as $item) {
      $item = (array) $item;
      $entries[] = SatuSehatBundleBuilder::entry(self::medication($ctx, $item));
    }

    return $entries;
  }

  /**
   * Susun satu resource Medication dari item resep/pemberian.
   */
  public static function medication(array $ctx, array $item, array $mapOverride = null)
  {
    $map = $mapOverride !== null ? $mapOverride : (array) ($item['map'] ?? []);
    $kodeKfa = trim((string) ($map['kode_kfa'] ?? ''));
    $namaKfa = trim((string) ($map['nama_kfa'] ?? ($item['nama_brng'] ?? '')));
    $kodeBrng = (string) ($item['kode_brng'] ?? '');

    if ($kodeKfa !== '') {
      $code = [
        'coding' => [
          [
            'system' => 'http://sys-ids.kemkes.go.id/kfa',
            'code' => $kodeKfa,
            'display' => $namaKfa,
          ],
        ],
        'text' => $namaKfa,
      ];
    } else {
      // Tanpa kode KFA: hanya teks — jangan kirim coding kosong (rule 10050).
      $code = ['text' => $namaKfa !== '' ? $namaKfa : $kodeBrng];
    }

    // MedicationType: CO (Compound/racikan) atau NC (Non-compound) — WAJIB (rule 10031).
    $tipe = strtoupper(trim((string) ($map['type'] ?? '')));
    $compound = in_array($tipe, ['CO', 'COMPOUND', 'RACIKAN'], true) || strpos($tipe, 'RACIK') !== false;

    $medication = [
      'resourceType' => 'Medication',
      'meta' => [
        'profile' => ['https://fhir.kemkes.go.id/r4/StructureDefinition/Medication'],
      ],
      'extension' => [
        [
          'url' => 'https://fhir.kemkes.go.id/r4/StructureDefinition/MedicationType',
          'valueCodeableConcept' => [
            'coding' => [
              [
                'system' => 'http://terminology.kemkes.go.id/CodeSystem/medication-type',
                'code' => $compound ? 'CO' : 'NC',
                'display' => $compound ? 'Compound' : 'Non-compound',
              ],
            ],
          ],
        ],
      ],
      'identifier' => [
        [
          'use' => 'official',
          'system' => 'http://sys-ids.kemkes.go.id/medication/' . (string) ($ctx['organization_id'] ?? ''),
          'value' => $kodeBrng,
        ],
      ],
      'status' => 'active',
      'code' => $code,
    ];

    // Bentuk sediaan KFA (kode BS034 dst) -> CodeSystem medication-form Kemenkes.
    $kodeSediaan = trim((string) ($map['kode_sediaan'] ?? ''));
    $namaSediaan = trim((string) ($map['nama_sediaan'] ?? ''));
    if ($kodeSediaan !== '') {
      $medication['form'] = [
        'coding' => [
          [
            'system' => 'http://terminology.kemkes.go.id/CodeSystem/medication-form',
            'code' => $kodeSediaan,
            'display' => $namaSediaan,
          ],
        ],
      ];
    } elseif ($namaSediaan !== '') {
      $medication['form'] = ['text' => $namaSediaan];
    }

    return $medication;
  }

  /**
   * Cari fullUrl Medication untuk sebuah item; bila belum ada, bangun entri baru
   * (rule 10138: MedicationDispense/Statement.medicationReference wajib).
   *
   * @param array $ctx Konteks lokal (dimutasi agar pencocokan berikutnya menemukan entri baru).
   * @param array $extra Entri baru ikut dikumpulkan untuk dimasukkan ke bundle.
   * @return string FullUrl Medication yang dipakai.
   */
  public static function ensureReference(array &$ctx, array $item, array &$extra, array $mapOverride = null)
  {
    $kodeBrng = (string) ($item['kode_brng'] ?? '');
    $ref = MedicationRequestBuilder::medicationReference($ctx, -1, $kodeBrng);
    if ($ref !== '') {
      return $ref;
    }
    $entry = SatuSehatBundleBuilder::entry(self::medication($ctx, $item, $mapOverride));
    $ctx['built']['medications'][] = $entry;
    $extra[] = $entry;
    return (string) ($entry['fullUrl'] ?? '');
  }
}
