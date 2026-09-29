<?php

namespace Plugins\Satu_Sehat\Resources;

use Plugins\Satu_Sehat\Services\SatuSehatBundleBuilder;

/**
 * Builder resource FHIR Encounter untuk ERM IGD (Encounter.class = EMER).
 *
 * Mengikuti template Bundle Transaction IGD Kemenkes: statusHistory
 * (arrived -> triaged -> in-progress -> finished), lokasi berjenjang
 * (ruang triase -> ruang tindakan) dengan ekstensi ServiceClass rawat jalan,
 * diagnosis Encounter (role AD diagnosis awal), dan hospitalization
 * (dischargeDisposition termasuk "dipindahkan ke rawat inap").
 *
 * Sumber data: reg_periksa + mlite_triase_igd + mapping lokasi.
 */
class EncounterIgdBuilder
{
  /** Map disposisi IGD SIMRS -> [code, display, teks] discharge-disposition HL7. */
  const DISCHARGE_DISPOSITION = [
    'sehat' => ['home', 'Home', 'Sembuh/Sehat'],
    'sembuh' => ['home', 'Home', 'Sembuh/Sehat'],
    'membaik' => ['home', 'Home', 'Membaik'],
    'memburuk' => ['oth', 'Other', 'Kondisi memburuk, dirawat lanjutan'],
    'rujuk' => ['other-hcf', 'Other healthcare facility', 'Dirujuk ke fasyankes lain'],
    'pulang paksa' => ['aadvice', 'Left against advice', 'Pulang atas permintaan sendiri'],
    'meninggal' => ['exp', 'Expired', 'Meninggal'],
  ];

  public static function build(array $ctx): array
  {
    $emr = (array) ($ctx['emr'] ?? []);
    $reg = (array) ($emr['reg_periksa'] ?? []);
    $ref = (array) ($ctx['ref'] ?? []);
    $time = (array) ($ctx['time'] ?? []);

    $masuk = (string) ($time['reg'] ?? '');
    $triage = (string) ($time['triage'] ?? '');
    $keluar = (string) ($time['discharge'] ?? ($time['end'] ?? ''));
    $isOpen = $keluar === '';

    $period = ['start' => $masuk];
    if (!$isOpen) {
      $period['end'] = $keluar;
    }

    $encounter = [
      'resourceType' => 'Encounter',
      'identifier' => [
        [
          'system' => 'http://sys-ids.kemkes.go.id/encounter/' . (string) ($ctx['organization_id'] ?? ''),
          'value' => (string) ($ctx['no_rawat'] ?? ''),
        ],
      ],
      'status' => $isOpen ? 'in-progress' : 'finished',
      'class' => [
        'system' => 'http://terminology.hl7.org/CodeSystem/v3-ActCode',
        'code' => 'EMER',
        'display' => 'emergency',
      ],
      'subject' => [
        'reference' => (string) ($ref['patient'] ?? ''),
        'display' => (string) ($ctx['patient_name'] ?? ''),
      ],
      'participant' => [
        [
          'type' => [
            [
              'coding' => [
                [
                  'system' => 'http://terminology.hl7.org/CodeSystem/v3-ParticipationType',
                  'code' => 'ATND',
                  'display' => 'attender',
                ],
              ],
            ],
          ],
          'individual' => [
            'reference' => (string) ($ref['practitioner'] ?? ''),
            'display' => (string) ($ctx['practitioner_name'] ?? ''),
          ],
        ],
      ],
      'period' => $period,
      'serviceProvider' => [
        'reference' => (string) ($ref['organization'] ?? ''),
      ],
    ];

    // serviceType: layanan IGD (template memakai coding service-type; SIMRS
    // tidak menyimpan kode layanan, sehingga dipakai teks nama poli/layanan).
    $serviceType = trim((string) ($ctx['location_display'] ?? ''));
    if ($serviceType !== '') {
      $encounter['serviceType'] = ['text' => $serviceType];
    }

    // statusHistory: arrived -> triaged -> in-progress -> finished.
    $encounter['statusHistory'] = self::statusHistory($masuk, $triage, $keluar, $isOpen);

    // Lokasi berjenjang (ruang triase -> ruang tindakan) + ekstensi ServiceClass.
    $locations = [];
    foreach ((array) ($emr['lokasi_items'] ?? []) as $lokasi) {
      $lokasi = (array) $lokasi;
      $locationPeriod = ['start' => (string) ($lokasi['start'] ?? $masuk)];
      $locationEnd = trim((string) ($lokasi['end'] ?? ''));
      if ($locationEnd !== '') {
        $locationPeriod['end'] = $locationEnd;
      }
      $locations[] = [
        'location' => [
          'reference' => (string) ($ref['location'] ?? ''),
          'display' => (string) ($lokasi['display'] ?? ($ctx['location_display'] ?? '')),
        ],
        'period' => $locationPeriod,
        'extension' => [self::serviceClassExtension()],
      ];
    }
    if (empty($locations)) {
      $locations[] = [
        'location' => [
          'reference' => (string) ($ref['location'] ?? ''),
          'display' => (string) ($ctx['location_display'] ?? ''),
        ],
        'period' => $period,
        'extension' => [self::serviceClassExtension()],
      ];
    }
    $encounter['location'] = $locations;

    // diagnosis Encounter: role AD (Admission diagnosis) -> Condition diagnosis awal.
    // Encounter.diagnosis wajib minimal 1 (rule 10457) — fallback ke diagnosis
    // kerja/banding bila diagnosis awal triase kosong.
    $diagnosis = [];
    $awalEntries = (array) (($ctx['built'] ?? [])['conditions_awal'] ?? []);
    if (empty($awalEntries)) {
      $awalEntries = array_slice((array) (($ctx['built'] ?? [])['conditions_dx'] ?? []), 0, 1);
    }
    foreach ($awalEntries as $awalEntry) {
      $diagnosis[] = [
        'condition' => [
          'reference' => (string) ($awalEntry['fullUrl'] ?? ''),
          'display' => (string) ($awalEntry['resource']['code']['text'] ?? ''),
        ],
        'use' => [
          'coding' => [
            [
              'system' => 'http://terminology.hl7.org/CodeSystem/diagnosis-role',
              'code' => 'AD',
              'display' => 'Admission diagnosis',
            ],
          ],
        ],
      ];
    }
    if (!empty($diagnosis)) {
      $encounter['diagnosis'] = $diagnosis;
    }

    // hospitalization.dischargeDisposition.
    $disposition = self::dischargeDisposition($reg);
    if ($disposition !== null) {
      $encounter['hospitalization'] = ['dischargeDisposition' => $disposition];
    }

    return [SatuSehatBundleBuilder::entry($encounter, (string) ($ctx['uuid_encounter'] ?? ''))];
  }

  /** StatusHistory IGD dari timestamp registrasi, triase, dan pulang. */
  private static function statusHistory($masuk, $triage, $keluar, $isOpen)
  {
    $history = [];
    $triage = $triage !== '' ? $triage : $masuk;

    $history[] = [
      'status' => 'arrived',
      'period' => ['start' => $masuk, 'end' => $triage],
    ];
    if ($triage !== '' && $triage !== $masuk) {
      $history[] = [
        'status' => 'triaged',
        'period' => ['start' => $triage, 'end' => $triage],
      ];
    }
    if ($isOpen) {
      $history[] = [
        'status' => 'in-progress',
        'period' => ['start' => $triage],
      ];
    } else {
      $history[] = [
        'status' => 'in-progress',
        'period' => ['start' => $triage, 'end' => $keluar],
      ];
      $history[] = [
        'status' => 'finished',
        'period' => ['start' => $keluar, 'end' => $keluar],
      ];
    }
    return $history;
  }

  /** Ekstensi ServiceClass kelas rawat jalan (reguler) sesuai template IGD. */
  private static function serviceClassExtension()
  {
    return [
      'url' => 'https://fhir.kemkes.go.id/r4/StructureDefinition/ServiceClass',
      'extension' => [
        [
          'url' => 'value',
          'valueCodeableConcept' => [
            'coding' => [
              [
                'system' => 'http://terminology.kemkes.go.id/CodeSystem/locationServiceClass-Outpatient',
                'code' => 'reguler',
                'display' => 'Kelas Reguler',
              ],
            ],
          ],
        ],
        [
          'url' => 'upgradeClassIndicator',
          'valueCodeableConcept' => [
            'coding' => [
              [
                'system' => 'http://terminology.kemkes.go.id/CodeSystem/locationUpgradeClass',
                'code' => 'kelas-tetap',
                'display' => 'Kelas Tetap Perawatan',
              ],
            ],
          ],
        ],
      ],
    ];
  }

  /**
   * Disposisi akhir kunjungan IGD: pindah ke rawat inap (template memakai
   * code "oth" dengan teks pemindahan) atau dari stts_pulang reg_periksa.
   */
  private static function dischargeDisposition(array $reg)
  {
    $statusLanjut = trim((string) ($reg['status_lanjut'] ?? ''));
    if (stripos($statusLanjut, 'ranap') !== false) {
      return [
        'coding' => [
          [
            'system' => 'http://terminology.hl7.org/CodeSystem/discharge-disposition',
            'code' => 'oth',
            'display' => 'Other',
          ],
        ],
        'text' => 'Pasien dipindahkan dari IGD ke rawat inap.',
      ];
    }

    $sttsPulang = trim((string) ($reg['stts_pulang'] ?? ''));
    if ($sttsPulang === '' || strpos($sttsPulang, '0000-00-00') === 0) {
      return null;
    }
    $map = self::DISCHARGE_DISPOSITION[trim(strtolower($sttsPulang))] ?? ['oth', 'Other', $sttsPulang];
    return [
      'coding' => [
        [
          'system' => 'http://terminology.hl7.org/CodeSystem/discharge-disposition',
          'code' => $map[0],
          'display' => $map[1],
        ],
      ],
      'text' => $map[2],
    ];
  }
}
