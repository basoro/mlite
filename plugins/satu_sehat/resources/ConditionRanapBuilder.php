<?php

namespace Plugins\Satu_Sehat\Resources;

use Plugins\Satu_Sehat\Services\SatuSehatBundleBuilder;

/**
 * Builder resource FHIR Condition untuk ERM Rawat Inap mengikuti template Bundle
 * Transaction Rawat Inap Kemenkes:
 *
 * - chief-complaint    : keluhan utama (pemeriksaan_ranap.keluhan)
 * - problem-list-item  : keluhan penyerta / problem list (pemeriksaan_ranap.pemeriksaan)
 * - previous-condition : riwayat penyakit pribadi terdahulu/sekarang (hook riwayat_penyakit_items)
 * - encounter-diagnosis: diagnosis primer (prioritas 1) & sekunder dari diagnosa_pasien,
 *                        dilengkapi stage.assessment -> ClinicalImpression rasional klinis.
 */
class ConditionRanapBuilder
{
  /** SNOMED-CT |Chief complaint|. */
  const SNOMED_CHIEF_COMPLAINT = '386661006';

  /** Gabungan seluruh Condition rawat inap. */
  public static function build(array $ctx): array
  {
    return array_merge(
      self::buildChiefComplaints($ctx),
      self::buildProblemList($ctx),
      self::buildPreviousConditions($ctx),
      self::buildDiagnoses($ctx)
    );
  }

  /**
   * Keluhan utama -> Condition kategori chief-complaint (sistem terminology.kemkes.go.id).
   */
  public static function buildChiefComplaints(array $ctx): array
  {
    $emr = (array) ($ctx['emr'] ?? []);
    $pemeriksaan = (array) ($emr['pemeriksaan'] ?? []);
    $keluhan = trim((string) ($pemeriksaan['keluhan'] ?? ''));
    if ($keluhan === '') {
      return [];
    }

    $condition = [
      'resourceType' => 'Condition',
      'clinicalStatus' => self::clinicalStatus('active', 'Active'),
      'category' => [
        [
          'coding' => [
            [
              'system' => 'http://terminology.kemkes.go.id',
              'code' => 'chief-complaint',
              'display' => 'Chief Complaint',
            ],
          ],
        ],
      ],
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
      'subject' => self::subject($ctx),
      'encounter' => self::encounter($ctx),
      'onsetDateTime' => (string) (($ctx['time'] ?? [])['reg'] ?? ''),
      'recordedDate' => (string) (($ctx['time'] ?? [])['exam'] ?? (($ctx['time'] ?? [])['now'] ?? '')),
      'recorder' => self::recorder($ctx),
      'note' => [['text' => $keluhan]],
    ];

    return [SatuSehatBundleBuilder::entry($condition)];
  }

  /**
   * Keluhan penyerta -> Condition kategori problem-list-item.
   */
  public static function buildProblemList(array $ctx): array
  {
    $emr = (array) ($ctx['emr'] ?? []);
    $pemeriksaan = (array) ($emr['pemeriksaan'] ?? []);
    $penyerta = trim((string) ($pemeriksaan['pemeriksaan'] ?? ''));
    if ($penyerta === '') {
      return [];
    }

    $condition = [
      'resourceType' => 'Condition',
      'clinicalStatus' => self::clinicalStatus('active', 'Active'),
      'category' => [
        [
          'coding' => [
            [
              'system' => 'http://terminology.hl7.org/CodeSystem/condition-category',
              'code' => 'problem-list-item',
              'display' => 'Problem List Item',
            ],
          ],
        ],
      ],
      'code' => ['text' => $penyerta],
      'subject' => self::subject($ctx),
      'encounter' => self::encounter($ctx),
      'onsetDateTime' => (string) (($ctx['time'] ?? [])['reg'] ?? ''),
      'recordedDate' => (string) (($ctx['time'] ?? [])['exam'] ?? (($ctx['time'] ?? [])['now'] ?? '')),
      'recorder' => self::recorder($ctx),
    ];

    return [SatuSehatBundleBuilder::entry($condition)];
  }

  /**
   * Riwayat penyakit pribadi terdahulu/sekarang -> Condition kategori previous-condition.
   *
   * Sumber: emr['riwayat_penyakit_items'] = [{kode, system, nama, status: active|inactive,
   * onset_start, onset_end, onset_age, onset_age_unit, note}].
   */
  public static function buildPreviousConditions(array $ctx): array
  {
    $emr = (array) ($ctx['emr'] ?? []);
    $entries = [];

    foreach ((array) ($emr['riwayat_penyakit_items'] ?? []) as $item) {
      $item = (array) $item;
      $nama = trim((string) ($item['nama'] ?? ''));
      $kode = trim((string) ($item['kode'] ?? ''));
      if ($nama === '' && $kode === '') {
        continue;
      }

      $coding = [];
      if ($kode !== '') {
        $coding[] = [
          'system' => (string) ($item['system'] ?? 'http://snomed.info/sct'),
          'code' => $kode,
          'display' => $nama,
        ];
      }

      $status = trim((string) ($item['status'] ?? 'active')) === 'inactive' ? 'inactive' : 'active';
      $condition = [
        'resourceType' => 'Condition',
        'clinicalStatus' => self::clinicalStatus($status, ucfirst($status)),
        'category' => [
          [
            'coding' => [
              [
                'system' => 'http://terminology.kemkes.go.id',
                'code' => 'previous-condition',
                'display' => 'Previous Condition',
              ],
            ],
          ],
        ],
        'code' => [
          'coding' => $coding,
          'text' => $nama !== '' ? $nama : $kode,
        ],
        'subject' => self::subject($ctx),
        'encounter' => self::encounter($ctx),
        'recordedDate' => (string) (($ctx['time'] ?? [])['exam'] ?? (($ctx['time'] ?? [])['now'] ?? '')),
        'recorder' => self::recorder($ctx),
      ];

      if (trim((string) ($item['onset_start'] ?? '')) !== '' || trim((string) ($item['onset_end'] ?? '')) !== '') {
        $onsetPeriod = [];
        if (trim((string) ($item['onset_start'] ?? '')) !== '') {
          $onsetPeriod['start'] = (string) $item['onset_start'];
        }
        if (trim((string) ($item['onset_end'] ?? '')) !== '') {
          $onsetPeriod['end'] = (string) $item['onset_end'];
        }
        $condition['onsetPeriod'] = $onsetPeriod;
      } elseif ((float) ($item['onset_age'] ?? 0) > 0) {
        $unit = (string) ($item['onset_age_unit'] ?? 'years');
        $ucum = ['years' => 'a', 'months' => 'mo', 'days' => 'd'][$unit] ?? 'a';
        $condition['onsetAge'] = [
          'value' => (float) $item['onset_age'],
          'unit' => $unit,
          'system' => 'http://unitsofmeasure.org',
          'code' => $ucum,
        ];
      }

      if (trim((string) ($item['note'] ?? '')) !== '') {
        $condition['note'] = [['text' => (string) $item['note']]];
      }

      $entries[] = SatuSehatBundleBuilder::entry($condition);
    }

    return $entries;
  }

  /**
   * Diagnosis primer & sekunder (diagnosa_pasien, status Ranap) ->
   * Condition kategori encounter-diagnosis + stage.assessment rasional klinis.
   */
  public static function buildDiagnoses(array $ctx): array
  {
    $emr = (array) ($ctx['emr'] ?? []);
    $items = (array) ($emr['diagnosa_items'] ?? []);
    $entries = [];
    $rationalRef = (string) (($ctx['built'] ?? [])['ci_rational_ref'] ?? '');

    foreach ($items as $item) {
      $item = (array) $item;
      $kode = (string) ($item['kode'] ?? '');
      $nama = (string) ($item['nama'] ?? '');
      if ($kode === '' && $nama === '') {
        continue;
      }
      $prioritas = (int) ($item['prioritas'] ?? (count($entries) + 1));

      $coding = [];
      if ($kode !== '') {
        $coding[] = [
          'system' => 'http://hl7.org/fhir/sid/icd-10',
          'code' => $kode,
          'display' => $nama,
        ];
      }

      $label = $prioritas === 1 ? 'Diagnosis primer ' : 'Diagnosis sekunder ';
      $condition = [
        'resourceType' => 'Condition',
        'clinicalStatus' => self::clinicalStatus('active', 'Active'),
        'category' => [
          [
            'coding' => [
              [
                'system' => 'http://terminology.hl7.org/CodeSystem/condition-category',
                'code' => 'encounter-diagnosis',
                'display' => 'Encounter Diagnosis',
              ],
            ],
          ],
        ],
        'code' => [
          'coding' => $coding,
          'text' => $label . ($nama !== '' ? $nama : $kode),
        ],
        'subject' => self::subject($ctx),
        'encounter' => self::encounter($ctx),
        'onsetDateTime' => (string) (($ctx['time'] ?? [])['reg'] ?? ''),
        'recordedDate' => (string) (($ctx['time'] ?? [])['exam'] ?? (($ctx['time'] ?? [])['now'] ?? '')),
      ];

      if ($rationalRef !== '') {
        $condition['stage'] = [
          [
            'assessment' => [['reference' => $rationalRef]],
          ],
        ];
      }

      $note = trim((string) ($item['status_penyakit'] ?? ''));
      $condition['note'] = [['text' => 'Pasien mengalami ' . ($nama !== '' ? $nama : $kode) . ($note !== '' ? ' (' . $note . ')' : '')]];

      $entries[] = SatuSehatBundleBuilder::entry($condition);
    }

    return $entries;
  }

  private static function clinicalStatus($code, $display)
  {
    return [
      'coding' => [
        [
          'system' => 'http://terminology.hl7.org/CodeSystem/condition-clinical',
          'code' => $code,
          'display' => $display,
        ],
      ],
    ];
  }

  private static function subject(array $ctx)
  {
    return [
      'reference' => (string) (($ctx['ref'] ?? [])['patient'] ?? ''),
      'display' => (string) ($ctx['patient_name'] ?? ''),
    ];
  }

  private static function encounter(array $ctx)
  {
    return [
      'reference' => (string) (($ctx['ref'] ?? [])['encounter'] ?? ''),
      'display' => 'Kunjungan Rawat Inap ' . (string) ($ctx['patient_name'] ?? ''),
    ];
  }

  private static function recorder(array $ctx)
  {
    return [
      'reference' => (string) (($ctx['ref'] ?? [])['practitioner'] ?? ''),
      'display' => (string) ($ctx['practitioner_name'] ?? ''),
    ];
  }
}
