<?php

namespace Plugins\Satu_Sehat\Resources;

use Plugins\Satu_Sehat\Services\SatuSehatBundleBuilder;

/**
 * Builder resource FHIR Condition untuk ERM Rawat Jalan:
 * keluhan utama (chief complaint) dan diagnosis ICD-10 (BAB 12).
 */
class ConditionBuilder
{
  /** SNOMED-CT |Chief complaint|. */
  const SNOMED_CHIEF_COMPLAINT = '386661006';

  /** Gabungan semua Condition (keluhan utama + diagnosis). */
  public static function build(array $ctx): array
  {
    return array_merge(
      self::buildChiefComplaints($ctx),
      self::buildDiagnoses($ctx)
    );
  }

  /**
   * Keluhan utama dari pemeriksaan_ralan.keluhan (BAB 3).
   */
  public static function buildChiefComplaints(array $ctx): array
  {
    $emr = (array) ($ctx['emr'] ?? []);
    $pemeriksaan = (array) ($emr['pemeriksaan'] ?? []);
    // Teks keluhan utama; fallback ke catatan pemeriksaan/penilaian agar selalu
    // ada Condition acuan untuk Encounter.diagnosis (rule 10457).
    $keluhan = trim((string) ($pemeriksaan['keluhan'] ?? ''));
    if ($keluhan === '') {
      $keluhan = trim((string) ($pemeriksaan['pemeriksaan'] ?? ''));
    }
    if ($keluhan === '') {
      $keluhan = trim((string) ($pemeriksaan['penilaian'] ?? ''));
    }
    if ($keluhan === '') {
      return [];
    }

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
      'verificationStatus' => [
        'coding' => [
          [
            'system' => 'http://terminology.hl7.org/CodeSystem/condition-ver-status',
            'code' => 'unconfirmed',
            'display' => 'Unconfirmed',
          ],
        ],
      ],
      'category' => [self::categoryEncounterDiagnosis()],
      'code' => [
        'coding' => [
          [
            'system' => 'http://snomed.info/sct',
            'code' => self::SNOMED_CHIEF_COMPLAINT,
            'display' => 'Chief complaint',
          ],
        ],
        'text' => $keluhan,
      ],
      'subject' => ['reference' => (string) (($ctx['ref'] ?? [])['patient'] ?? '')],
      'encounter' => ['reference' => (string) (($ctx['ref'] ?? [])['encounter'] ?? '')],
      'onsetDateTime' => (string) (($ctx['time'] ?? [])['reg'] ?? ''),
      'recordedDate' => (string) (($ctx['time'] ?? [])['now'] ?? ''),
    ];

    return [SatuSehatBundleBuilder::entry($condition)];
  }

  /**
   * Diagnosis dari diagnosa_pasien (ICD-10, diurut prioritas).
   */
  public static function buildDiagnoses(array $ctx): array
  {
    $emr = (array) ($ctx['emr'] ?? []);
    $items = (array) ($emr['diagnosa_items'] ?? []);
    $entries = [];

    foreach ($items as $item) {
      $item = (array) $item;
      $kode = (string) ($item['kode'] ?? '');
      $nama = (string) ($item['nama'] ?? '');
      if ($kode === '' && $nama === '') {
        continue;
      }

      $coding = [];
      if ($kode !== '') {
        $coding[] = [
          'system' => 'http://hl7.org/fhir/sid/icd-10',
          'code' => $kode,
          'display' => $nama,
        ];
      }

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
        'verificationStatus' => [
          'coding' => [
            [
              'system' => 'http://terminology.hl7.org/CodeSystem/condition-ver-status',
              'code' => 'confirmed',
              'display' => 'Confirmed',
            ],
          ],
        ],
        'category' => [self::categoryEncounterDiagnosis()],
        'code' => [
          'coding' => $coding,
          'text' => $nama !== '' ? $nama : $kode,
        ],
        'subject' => ['reference' => (string) (($ctx['ref'] ?? [])['patient'] ?? '')],
        'encounter' => ['reference' => (string) (($ctx['ref'] ?? [])['encounter'] ?? '')],
        'recordedDate' => (string) (($ctx['time'] ?? [])['now'] ?? ''),
      ];

      $entries[] = SatuSehatBundleBuilder::entry($condition);
    }

    return $entries;
  }

  private static function categoryEncounterDiagnosis()
  {
    return [
      'coding' => [
        [
          'system' => 'http://terminology.hl7.org/CodeSystem/condition-category',
          'code' => 'encounter-diagnosis',
          'display' => 'Encounter Diagnosis',
        ],
      ],
    ];
  }
}
