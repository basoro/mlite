<?php

namespace Plugins\Satu_Sehat\Resources;

use Plugins\Satu_Sehat\Services\SatuSehatBundleBuilder;

/**
 * Builder resource FHIR Procedure untuk ERM IGD mengikuti template Bundle
 * Transaction IGD Kemenkes:
 *
 * - Prosedur emergensi (SNOMED 373110003 Emergency procedure) dari
 *   tindakan/prosedur kunjungan IGD, dengan reasonCode diagnosis awal.
 * - Prosedur persiapan penunjang "Puasa / Fasting" (SNOMED 792805006,
 *   category 103693007 Diagnostic procedure, status not-done) yang dipakai
 *   sebagai supportingInfo ServiceRequest lab/radiologi.
 */
class ProcedureIgdBuilder
{
  /** Prosedur emergensi IGD (category 373110003 Emergency procedure). */
  public static function buildEmergency(array $ctx): array
  {
    $emr = (array) ($ctx['emr'] ?? []);
    $entries = [];

    foreach ((array) ($emr['tindakan_items'] ?? []) as $item) {
      $item = (array) $item;
      $kode = trim((string) ($item['kode'] ?? ''));
      $nama = trim((string) ($item['nama'] ?? ''));
      if ($kode === '' && $nama === '') {
        continue;
      }

      $coding = [];
      if ($kode !== '') {
        if (($item['sumber'] ?? '') === 'icd9') {
          $coding[] = [
            'system' => 'http://hl7.org/fhir/sid/icd-9-cm',
            'code' => ProcedureBuilder::normalizeIcd9($kode),
            'display' => $nama,
          ];
        } else {
          // Kode lokal jns_perawatan bukan kode KPTL (rule 10015). Coding kptl
          // hanya dikirim bila terverifikasi di codebook mlite_ktpl.
          $kodeKptl = trim((string) ($item['map']['kode_ktpl'] ?? ''));
          if ($kodeKptl !== '') {
            $coding[] = [
              'system' => 'http://terminology.kemkes.go.id/CodeSystem/kptl',
              'code' => $kodeKptl,
              'display' => trim((string) ($item['map']['nama_ktpl'] ?? '')) !== '' ? (string) $item['map']['nama_ktpl'] : $nama,
            ];
          }
        }
      }

      $auth = $ctx['auth'] ?? null;
      $waktu = trim((string) (($item['tgl'] ?? '') . ' ' . ($item['jam'] ?? '')));
      $start = $auth !== null ? $auth->fhirTime($waktu, true) : (string) (($ctx['time'] ?? [])['now'] ?? '');

      $procedure = [
        'resourceType' => 'Procedure',
        'status' => 'completed',
        'category' => [
          [
            'coding' => [
              ['system' => 'http://snomed.info/sct', 'code' => '373110003', 'display' => 'Emergency procedure'],
            ],
            'text' => 'Prosedur emergensi',
          ],
        ],
        'code' => ['coding' => $coding, 'text' => $nama !== '' ? $nama : $kode],
        'subject' => [
          'reference' => (string) (($ctx['ref'] ?? [])['patient'] ?? ''),
          'display' => (string) ($ctx['patient_name'] ?? ''),
        ],
        'encounter' => ['reference' => (string) (($ctx['ref'] ?? [])['encounter'] ?? '')],
        'performedPeriod' => ['start' => $start, 'end' => $start],
        'performer' => [
          [
            'actor' => [
              'reference' => (string) (($ctx['ref'] ?? [])['practitioner'] ?? ''),
              'display' => (string) ($ctx['practitioner_name'] ?? ''),
            ],
          ],
        ],
      ];

      // reasonCode: ICD-10 diagnosis awal (template Procedure_Emergency).
      $reasonCode = self::reasonCode($ctx);
      if (!empty($reasonCode)) {
        $procedure['reasonCode'] = [$reasonCode];
      }

      $entries[] = SatuSehatBundleBuilder::entry($procedure);
    }

    return $entries;
  }

  /**
   * Prosedur persiapan penunjang "Puasa / Fasting" (status not-done) sebagai
   * supportingInfo ServiceRequest lab ('lab') atau radiologi ('rad').
   */
  public static function buildPreparation(array $ctx, $kind = 'lab')
  {
    // Persiapan hanya relevan bila ada permintaan penunjang terkait.
    $permintaan = (array) (($ctx['emr'] ?? [])[$kind === 'rad' ? 'permintaan_rad_items' : 'permintaan_lab_items'] ?? []);
    if (empty($permintaan)) {
      return null;
    }

    $auth = $ctx['auth'] ?? null;
    $time = (array) ($ctx['time'] ?? []);
    $waktu = (string) ($time['exam'] ?? ($time['now'] ?? ''));

    $procedure = [
      'resourceType' => 'Procedure',
      'status' => 'not-done',
      'category' => [
        [
          'coding' => [
            ['system' => 'http://snomed.info/sct', 'code' => '103693007', 'display' => 'Diagnostic procedure'],
          ],
        ],
      ],
      'code' => [
        'coding' => [
          ['system' => 'http://snomed.info/sct', 'code' => '792805006', 'display' => 'Fasting'],
        ],
        'text' => $kind === 'rad' ? 'Persiapan pemeriksaan radiologi (puasa)' : 'Persiapan pemeriksaan laboratorium (puasa)',
      ],
      'subject' => [
        'reference' => (string) (($ctx['ref'] ?? [])['patient'] ?? ''),
        'display' => (string) ($ctx['patient_name'] ?? ''),
      ],
      'encounter' => ['reference' => (string) (($ctx['ref'] ?? [])['encounter'] ?? '')],
      'performedPeriod' => ['start' => $waktu, 'end' => $waktu],
      'performer' => [
        [
          'actor' => [
            'reference' => (string) (($ctx['ref'] ?? [])['practitioner'] ?? ''),
            'display' => (string) ($ctx['practitioner_name'] ?? ''),
          ],
        ],
      ],
    ];

    $reasonCode = self::reasonCode($ctx);
    if (!empty($reasonCode)) {
      $procedure['reasonCode'] = [$reasonCode];
    }

    return SatuSehatBundleBuilder::entry($procedure);
  }

  /** reasonCode ICD-10 dari Condition diagnosis awal / diagnosa prioritas 1. */
  private static function reasonCode(array $ctx)
  {
    foreach ((array) (($ctx['emr'] ?? [])['diagnosa_items'] ?? []) as $item) {
      $item = (array) $item;
      if ((int) ($item['prioritas'] ?? 0) === 1 && trim((string) ($item['kode'] ?? '')) !== '') {
        return [
          'coding' => [
            [
              'system' => 'http://hl7.org/fhir/sid/icd-10',
              'code' => (string) $item['kode'],
              'display' => (string) ($item['nama'] ?? ''),
            ],
          ],
        ];
      }
    }
    $triase = (array) (($ctx['emr'] ?? [])['triase'] ?? []);
    $diagnosaAwal = trim((string) ($triase['diagnosa_awal'] ?? ''));
    if ($diagnosaAwal !== '') {
      return ['text' => $diagnosaAwal];
    }
    return null;
  }
}
