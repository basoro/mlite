<?php

namespace Plugins\Satu_Sehat\Resources;

use Plugins\Satu_Sehat\Services\SatuSehatBundleBuilder;

/**
 * Builder resource FHIR CarePlan untuk ERM Rawat Inap:
 *
 * - "Rencana Rawat Pasien" (Inpatient care plan, SNOMED 736353004) dari pemeriksaan_ranap.rtl
 * - "Instruksi Medik dan Keperawatan Pasien" (Inpatient care plan) dari pemeriksaan_ranap.instruksi
 * - "Perencanaan Pemulangan Pasien" (Discharge care plan, SNOMED 736372004) saat pasien pulang.
 */
class CarePlanRanapBuilder
{
  public static function build(array $ctx): array
  {
    $emr = (array) ($ctx['emr'] ?? []);
    $pemeriksaan = (array) ($emr['pemeriksaan'] ?? []);
    $inap = (array) ($emr['kamar_inap'] ?? []);
    $rtl = trim((string) ($pemeriksaan['rtl'] ?? ''));
    $instruksi = trim((string) ($pemeriksaan['instruksi'] ?? ''));
    $time = (array) ($ctx['time'] ?? []);
    $ref = (array) ($ctx['ref'] ?? []);

    $entries = [];

    // Ref tujuan perawatan (BAB 7) bila sudah dibangun.
    $goalRef = '';
    foreach ((array) (($ctx['built'] ?? [])['goals'] ?? []) as $goalEntry) {
      $goalRef = (string) ($goalEntry['fullUrl'] ?? '');
      break;
    }

    $base = [
      'resourceType' => 'CarePlan',
      'status' => 'active',
      'intent' => 'plan',
      'subject' => [
        'reference' => (string) ($ref['patient'] ?? ''),
        'display' => (string) ($ctx['patient_name'] ?? ''),
      ],
      'encounter' => ['reference' => (string) ($ref['encounter'] ?? '')],
      'created' => (string) ($time['reg'] ?? ($time['now'] ?? '')),
      'author' => ['reference' => (string) ($ref['practitioner'] ?? '')],
    ];

    // ---- Rencana Rawat Pasien ----
    if ($rtl !== '') {
      $rencanaRawat = $base + [
        'category' => [self::categoryInpatient()],
        'title' => 'Rencana Rawat Pasien',
        'description' => $rtl,
      ];
      if ($goalRef !== '') {
        $rencanaRawat['goal'] = [['reference' => $goalRef]];
      }
      $entries[] = SatuSehatBundleBuilder::entry($rencanaRawat);
    }

    // ---- Instruksi Medik dan Keperawatan ----
    if ($instruksi !== '') {
      $instruksiMedik = $base + [
        'category' => [self::categoryInpatient()],
        'title' => 'Instruksi Medik dan Keperawatan Pasien',
        'description' => $instruksi,
      ];
      if ($goalRef !== '') {
        $instruksiMedik['goal'] = [['reference' => $goalRef]];
      }
      $entries[] = SatuSehatBundleBuilder::entry($instruksiMedik);
    }

    // ---- Perencanaan Pemulangan Pasien ----
    $sttsPulang = trim((string) ($inap['stts_pulang'] ?? ''));
    if ($sttsPulang !== '') {
      $deskripsi = trim((string) ($emr['rencana_pulang'] ?? ''));
      if ($deskripsi === '') {
        $deskripsi = 'Pasien pulang dengan status ' . $sttsPulang
          . ($instruksi !== '' ? '. ' . $instruksi : ($rtl !== '' ? '. ' . $rtl : ''));
      }
      $dischargePlan = $base + [
        'category' => [
          [
            'coding' => [
              [
                'system' => 'http://snomed.info/sct',
                'code' => '736372004',
                'display' => 'Discharge care plan',
              ],
            ],
          ],
        ],
        'title' => 'Perencanaan Pemulangan Pasien',
        'description' => $deskripsi,
        'created' => (string) ($time['discharge'] ?? ($time['now'] ?? '')),
      ];
      $entries[] = SatuSehatBundleBuilder::entry($dischargePlan);
    }

    return $entries;
  }

  private static function categoryInpatient()
  {
    return [
      'coding' => [
        [
          'system' => 'http://snomed.info/sct',
          'code' => '736353004',
          'display' => 'Inpatient care plan',
        ],
      ],
    ];
  }
}
