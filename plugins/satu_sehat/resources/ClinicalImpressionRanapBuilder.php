<?php

namespace Plugins\Satu_Sehat\Resources;

use Plugins\Satu_Sehat\Services\SatuSehatBundleBuilder;

/**
 * Builder resource FHIR ClinicalImpression untuk ERM Rawat Inap:
 *
 * - Riwayat Perjalanan Penyakit (SNOMED 312850006 History of disorder)
 * - Rasional Klinis (TK000056) dengan investigation -> hasil lab & radiologi
 * - Prognosis (SNOMED 20481000 Determination of prognosis) saat pulang.
 */
class ClinicalImpressionRanapBuilder
{
  /**
   * @return array<string,array> Asosiasi jenis -> entri bundle, plus '_entries' gabungan.
   */
  public static function build(array $ctx): array
  {
    $emr = (array) ($ctx['emr'] ?? []);
    $pemeriksaan = (array) ($emr['pemeriksaan'] ?? []);
    $ref = (array) ($ctx['ref'] ?? []);
    $time = (array) ($ctx['time'] ?? []);
    $penilaian = trim((string) ($pemeriksaan['penilaian'] ?? ''));
    $evaluasi = trim((string) ($pemeriksaan['evaluasi'] ?? ''));
    $inap = (array) ($emr['kamar_inap'] ?? []);

    $base = [
      'status' => 'completed',
      'subject' => [
        'reference' => (string) ($ref['patient'] ?? ''),
        'display' => (string) ($ctx['patient_name'] ?? ''),
      ],
      'encounter' => ['reference' => (string) ($ref['encounter'] ?? '')],
      'assessor' => ['reference' => (string) ($ref['practitioner'] ?? '')],
      'date' => (string) ($time['exam'] ?? ($time['now'] ?? '')),
    ];

    $result = [];

    // ---- Riwayat Perjalanan Penyakit ----
    if ($evaluasi !== '' || $penilaian !== '') {
      $history = $base + [
        'resourceType' => 'ClinicalImpression',
        'code' => [
          'coding' => [
            [
              'system' => 'http://snomed.info/sct',
              'code' => '312850006',
              'display' => 'History of disorder',
            ],
          ],
        ],
        'description' => 'Riwayat Perjalanan Penyakit Kunjungan Rawat Inap',
        'effectiveDateTime' => (string) ($time['exam'] ?? ($time['now'] ?? '')),
        'summary' => $evaluasi !== '' ? $evaluasi : $penilaian,
      ];
      $result['history'] = SatuSehatBundleBuilder::entry($history);
    }

    // ---- Rasional Klinis ----
    if ($penilaian !== '') {
      $rational = $base + [
        'resourceType' => 'ClinicalImpression',
        'code' => [
          'coding' => [
            [
              'system' => 'http://terminology.kemkes.go.id',
              'code' => 'TK000056',
              'display' => 'Rasional Klinis',
            ],
          ],
        ],
        'description' => 'Rasional Klinis Kunjungan Rawat Inap',
        'effectiveDateTime' => (string) ($time['exam'] ?? ($time['now'] ?? '')),
        'summary' => $penilaian,
      ];

      $items = [];
      foreach ((array) (($ctx['built'] ?? [])['observations_lab'] ?? []) as $entry) {
        $items[] = [
          'reference' => (string) ($entry['fullUrl'] ?? ''),
          'display' => 'Hasil Pemeriksaan Penunjang Laboratorium',
        ];
      }
      foreach ((array) (($ctx['built'] ?? [])['observations_rad'] ?? []) as $entry) {
        $items[] = [
          'reference' => (string) ($entry['fullUrl'] ?? ''),
          'display' => 'Hasil Pemeriksaan Penunjang Radiologi',
        ];
      }
      if (!empty($items)) {
        $rational['investigation'] = [
          [
            'code' => [
              'coding' => [
                [
                  'system' => 'http://snomed.info/sct',
                  'code' => '271336007',
                  'display' => 'Examination / signs',
                ],
              ],
            ],
            'item' => $items,
          ],
        ];
      }

      // UUID deterministik (ctx['uuid_ci_rational']) karena dirujuk Condition.stage.assessment.
      $rationalEntry = SatuSehatBundleBuilder::entry(
        $rational,
        trim((string) (($ctx['uuid_ci_rational'] ?? ''))) !== '' ? (string) $ctx['uuid_ci_rational'] : null
      );
      $result['rational'] = $rationalEntry;
      $result['ci_rational_ref'] = (string) ($rationalEntry['fullUrl'] ?? '');
    }

    // ---- Prognosis (saat pasien pulang) ----
    $sttsPulang = trim((string) ($inap['stts_pulang'] ?? ''));
    if ($sttsPulang !== '') {
      $prognosisCode = self::prognosisCode($sttsPulang);
      $prognosis = $base + [
        'resourceType' => 'ClinicalImpression',
        'identifier' => [
          [
            'use' => 'official',
            'system' => 'http://sys-ids.kemkes.go.id/clinicalimpression/' . (string) ($ctx['organization_id'] ?? ''),
            'value' => 'PROG-' . (string) ($ctx['no_rawat'] ?? ''),
          ],
        ],
        'code' => [
          'coding' => [
            [
              'system' => 'http://snomed.info/sct',
              'code' => '20481000',
              'display' => 'Determination of prognosis',
            ],
          ],
        ],
        'description' => 'Prognosis Kunjungan Rawat Inap ' . (string) ($ctx['patient_name'] ?? ''),
        'effectiveDateTime' => (string) ($time['discharge'] ?? ($time['now'] ?? '')),
        'summary' => 'Prognosis pasien saat meninggalkan rumah sakit: ' . $sttsPulang,
        'prognosisCodeableConcept' => [$prognosisCode],
      ];

      $finding = [];
      foreach ((array) (($ctx['built'] ?? [])['conditions_dx'] ?? []) as $entry) {
        $resource = (array) ($entry['resource'] ?? []);
        $finding[] = [
          'itemReference' => ['reference' => (string) ($entry['fullUrl'] ?? '')],
          'itemCodeableConcept' => ['text' => (string) ($resource['code']['text'] ?? '')],
        ];
      }
      if (!empty($finding)) {
        $prognosis['problem'] = [
          ['reference' => (string) ($finding[0]['itemReference']['reference'] ?? '')],
        ];
        $prognosis['finding'] = $finding;
      }

      $result['prognosis'] = SatuSehatBundleBuilder::entry($prognosis);
    }

    return $result;
  }

  private static function prognosisCode($sttsPulang)
  {
    $map = [
      'sehat' => ['255507004', 'Good prognosis'],
      'sembuh' => ['255507004', 'Good prognosis'],
      'membaik' => ['65872000', 'Fair prognosis'],
      'rujuk' => ['65872000', 'Fair prognosis'],
      'meninggal' => ['385054007', 'Terminal prognosis'],
    ];
    $code = $map[trim(strtolower($sttsPulang))] ?? ['65872000', 'Fair prognosis'];
    return [
      'coding' => [
        [
          'system' => 'http://snomed.info/sct',
          'code' => $code[0],
          'display' => $code[1],
        ],
      ],
    ];
  }
}
