<?php

namespace Plugins\Satu_Sehat\Resources;

use Plugins\Satu_Sehat\Services\SatuSehatBundleBuilder;

/**
 * Builder resource FHIR Condition untuk ERM IGD mengikuti template Bundle
 * Transaction IGD Kemenkes (kategori encounter-diagnosis):
 *
 * - Diagnosis Awal   : dari mlite_triase_igd.diagnosa_awal (koding ICD-10
 *                      dari diagnosa_pasien prioritas 1 bila tersedia).
 * - Diagnosis Kerja  : diagnosa_pasien prioritas 1 (verificationStatus provisional).
 * - Diagnosis Banding: diagnosa_pasien prioritas 2..n (verificationStatus differential).
 */
class ConditionIgdBuilder
{
  /** Kategori Condition sesuai template IGD. */
  private static function category()
  {
    return [
      [
        'coding' => [
          [
            'system' => 'http://terminology.hl7.org/CodeSystem/condition-category',
            'code' => 'encounter-diagnosis',
            'display' => 'Encounter Diagnosis',
          ],
        ],
      ],
    ];
  }

  /** Diagnosis awal (Admission diagnosis) dari triase IGD. */
  public static function buildDiagnosisAwal(array $ctx): array
  {
    $emr = (array) ($ctx['emr'] ?? []);
    $triase = (array) ($emr['triase'] ?? []);
    $diagnosaAwal = trim((string) ($triase['diagnosa_awal'] ?? ''));
    $keluhanUtama = trim((string) ($triase['keluhan_utama'] ?? ($emr['pemeriksaan']['keluhan'] ?? '')));

    // Koding ICD-10 dari diagnosa_pasien prioritas 1 bila ada.
    $coding = self::icd10Coding($ctx, 1);
    $text = $diagnosaAwal !== '' ? $diagnosaAwal : ($coding !== [] ? (string) ($coding[0]['display'] ?? '') : $keluhanUtama);
    if ($text === '') {
      return [];
    }

    $condition = self::condition($ctx, $coding, $text);
    $note = $diagnosaAwal !== '' && $keluhanUtama !== '' && $diagnosaAwal !== $keluhanUtama
      ? 'Pasien ' . (string) ($ctx['patient_name'] ?? '') . ' dengan keluhan utama: ' . $keluhanUtama
      : '';
    if ($note !== '') {
      $condition['note'] = [['text' => $note]];
    }

    return [SatuSehatBundleBuilder::entry($condition)];
  }

  /** Diagnosis kerja (provisional) dari diagnosa_pasien prioritas 1. */
  public static function buildDiagnosisKerja(array $ctx): array
  {
    $emr = (array) ($ctx['emr'] ?? []);
    $items = (array) ($emr['diagnosa_items'] ?? []);
    $entries = [];

    foreach ($items as $item) {
      $item = (array) $item;
      if ((int) ($item['prioritas'] ?? 0) !== 1) {
        continue;
      }
      $coding = self::icd10CodingFromItem($item);
      $condition = self::condition($ctx, $coding, (string) ($item['nama'] ?? ''));
      $condition['verificationStatus'] = [
        'coding' => [
          [
            'system' => 'http://terminology.hl7.org/CodeSystem/condition-ver-status',
            'code' => 'provisional',
            'display' => 'Provisional',
          ],
        ],
      ];
      $condition['note'] = [
        ['text' => 'Diagnosis kerja dari Pasien ' . (string) ($ctx['patient_name'] ?? '') . ': ' . (string) ($item['nama'] ?? '')],
      ];
      $entries[] = SatuSehatBundleBuilder::entry($condition);
      break;
    }

    return $entries;
  }

  /** Diagnosis banding (differential) dari diagnosa_pasien prioritas 2..n. */
  public static function buildDiagnosisBanding(array $ctx): array
  {
    $emr = (array) ($ctx['emr'] ?? []);
    $items = (array) ($emr['diagnosa_items'] ?? []);
    $entries = [];

    foreach ($items as $item) {
      $item = (array) $item;
      if ((int) ($item['prioritas'] ?? 0) < 2) {
        continue;
      }
      $coding = self::icd10CodingFromItem($item);
      $condition = self::condition($ctx, $coding, (string) ($item['nama'] ?? ''));
      $condition['verificationStatus'] = [
        'coding' => [
          [
            'system' => 'http://terminology.hl7.org/CodeSystem/condition-ver-status',
            'code' => 'differential',
            'display' => 'Differential',
          ],
        ],
      ];
      $condition['note'] = [
        ['text' => 'Diagnosis banding dari Pasien ' . (string) ($ctx['patient_name'] ?? '') . ': ' . (string) ($item['nama'] ?? '')],
      ];
      $entries[] = SatuSehatBundleBuilder::entry($condition);
    }

    return $entries;
  }

  /** Kerangka Condition IGD (clinicalStatus active, kategori encounter-diagnosis). */
  private static function condition(array $ctx, array $coding, $text)
  {
    $time = (array) ($ctx['time'] ?? []);
    $onset = (string) ($time['triage'] ?? ($time['exam'] ?? ($time['reg'] ?? '')));

    $condition = [
      'resourceType' => 'Condition',
      'clinicalStatus' => [
        'coding' => [
          [
            'system' => 'http://terminology.hl7.org/CodeSystem/condition-clinical',
            'code' => 'active',
            'display' => 'Active',
          ],
        ],
      ],
      'category' => self::category(),
      'code' => ['coding' => $coding, 'text' => $text],
      'subject' => [
        'reference' => (string) (($ctx['ref'] ?? [])['patient'] ?? ''),
        'display' => (string) ($ctx['patient_name'] ?? ''),
      ],
      'encounter' => ['reference' => (string) (($ctx['ref'] ?? [])['encounter'] ?? '')],
    ];
    if ($onset !== '') {
      $condition['onsetDateTime'] = $onset;
      $condition['recordedDate'] = (string) ($time['exam'] ?? $onset);
    }
    return $condition;
  }

  /** Koding ICD-10 dari diagnosa_items berdasar prioritas. */
  private static function icd10Coding(array $ctx, $prioritas)
  {
    foreach ((array) (($ctx['emr'] ?? [])['diagnosa_items'] ?? []) as $item) {
      $item = (array) $item;
      if ((int) ($item['prioritas'] ?? 0) === (int) $prioritas && trim((string) ($item['kode'] ?? '')) !== '') {
        return self::icd10CodingFromItem($item);
      }
    }
    return [];
  }

  private static function icd10CodingFromItem(array $item)
  {
    if (trim((string) ($item['kode'] ?? '')) === '') {
      return [];
    }
    return [
      [
        'system' => 'http://hl7.org/fhir/sid/icd-10',
        'code' => (string) $item['kode'],
        'display' => (string) ($item['nama'] ?? ''),
      ],
    ];
  }
}
