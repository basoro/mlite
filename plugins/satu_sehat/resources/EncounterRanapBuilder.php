<?php

namespace Plugins\Satu_Sehat\Resources;

use Plugins\Satu_Sehat\Services\SatuSehatBundleBuilder;

/**
 * Builder resource FHIR Encounter untuk ERM Rawat Inap (Encounter.class = IMP).
 *
 * Mengikuti template Bundle Transaction Rawat Inap Kemenkes: statusHistory
 * (in-progress -> finished), serviceType, length of stay, hospitalization
 * (dischargeDisposition dari stts_pulang), lokasi rawat dengan ekstensi
 * ServiceClass (kelas perawatan + upgradeClassIndicator), dan diagnosis
 * Encounter (DD berperingkat + CC keluhan utama).
 *
 * Sumber data: reg_periksa + kamar_inap + kamar/bangsal + mapping lokasi/praktisi.
 */
class EncounterRanapBuilder
{
  /** Map stts_pulang SIMRS -> code discharge-disposition HL7. */
  const DISCHARGE_DISPOSITION = [
    'sehat' => ['home', 'Home', 'Sembuh/Sehat'],
    'sembuh' => ['home', 'Home', 'Sembuh/Sehat'],
    'membaik' => ['home', 'Home', 'Membaik'],
    'rujuk' => ['other-hcf', 'Other healthcare facility', 'Dirujuk ke fasyankes lain'],
    'pulang paksa' => ['aadvice', 'Left against advice', 'Pulang atas permintaan sendiri'],
    'meninggal' => ['exp', 'Expired', 'Meninggal'],
  ];

  /** Map kelas kamar SIMRS -> code locationServiceClass-Inpatient. */
  const SERVICE_CLASS = [
    '1' => ['1', 'Kelas 1'],
    '2' => ['2', 'Kelas 2'],
    '3' => ['3', 'Kelas 3'],
    'utama' => ['utama', 'Kelas Utama'],
    'vip' => ['vip', 'Kelas VIP'],
    'vvip' => ['vvip', 'Kelas VVIP'],
  ];

  public static function build(array $ctx): array
  {
    $emr = (array) ($ctx['emr'] ?? []);
    $reg = (array) ($emr['reg_periksa'] ?? []);
    $inap = (array) ($emr['kamar_inap'] ?? []);
    $ref = (array) ($ctx['ref'] ?? []);
    $time = (array) ($ctx['time'] ?? []);

    $masuk = (string) ($time['reg'] ?? '');
    $keluar = (string) ($time['discharge'] ?? '');
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
      'statusHistory' => [
        [
          'status' => 'in-progress',
          'period' => $isOpen ? ['start' => $masuk] : ['start' => $masuk, 'end' => $keluar],
        ],
      ],
      'class' => [
        'system' => 'http://terminology.hl7.org/CodeSystem/v3-ActCode',
        'code' => 'IMP',
        'display' => 'inpatient encounter',
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

    // serviceType: nama bangsal/layanan rawat inap.
    $serviceType = trim((string) ($inap['nm_bangsal'] ?? ($ctx['location_display'] ?? '')));
    if ($serviceType !== '') {
      $encounter['serviceType'] = ['text' => $serviceType];
    }

    // statusHistory "finished" (masa discharge administrasi).
    if (!$isOpen) {
      $encounter['statusHistory'][] = [
        'status' => 'finished',
        'period' => ['start' => $keluar, 'end' => $keluar],
      ];
    }

    // basedOn: ServiceRequest perujuk rawat inap (pra ranap) bila tersedia.
    if (trim((string) ($ctx['ref']['praranap_request'] ?? '')) !== '') {
      $encounter['basedOn'] = [['reference' => (string) $ctx['ref']['praranap_request']]];
    }

    // length of stay (dari kamar_inap.lama, satuan hari).
    $lama = (float) ($inap['lama'] ?? 0);
    if ($lama > 0) {
      $encounter['length'] = [
        'value' => $lama,
        'unit' => 'd',
        'system' => 'http://unitsofmeasure.org',
        'code' => 'd',
      ];
    }

    // diagnosis Encounter: DD berperingkat + CC keluhan utama (tanpa rank).
    $diagnosis = [];
    $conditionsCc = (array) (($ctx['built'] ?? [])['conditions_cc'] ?? []);
    foreach ($conditionsCc as $ccEntry) {
      $diagnosis[] = [
        'condition' => [
          'reference' => (string) ($ccEntry['fullUrl'] ?? ''),
          'display' => (string) ($ccEntry['resource']['code']['text'] ?? ''),
        ],
        'use' => [
          'coding' => [
            [
              'system' => 'http://terminology.hl7.org/CodeSystem/diagnosis-role',
              'code' => 'CC',
              'display' => 'Chief Complaint',
            ],
          ],
        ],
      ];
    }
    $rank = 1;
    foreach ((array) (($ctx['built'] ?? [])['conditions_dx'] ?? []) as $dxEntry) {
      $resource = (array) ($dxEntry['resource'] ?? []);
      $diagnosis[] = [
        'condition' => [
          'reference' => (string) ($dxEntry['fullUrl'] ?? ''),
          'display' => (string) ($resource['code']['text'] ?? ''),
        ],
        'use' => [
          'coding' => [
            [
              'system' => 'http://terminology.hl7.org/CodeSystem/diagnosis-role',
              'code' => 'DD',
              'display' => 'Discharge diagnosis',
            ],
          ],
        ],
        'rank' => (int) ($resource['extension'][0]['valueInteger'] ?? $rank),
      ];
      $rank++;
    }
    if (!empty($diagnosis)) {
      $encounter['diagnosis'] = $diagnosis;
    }

    // hospitalization.dischargeDisposition dari stts_pulang kamar_inap.
    $sttsPulang = trim((string) ($inap['stts_pulang'] ?? ''));
    if ($sttsPulang !== '') {
      $disposition = self::DISCHARGE_DISPOSITION[trim(strtolower($sttsPulang))] ?? ['oth', 'Other', $sttsPulang];
      $encounter['hospitalization'] = [
        'dischargeDisposition' => [
          'coding' => [
            [
              'system' => 'http://terminology.hl7.org/CodeSystem/discharge-disposition',
              'code' => $disposition[0],
              'display' => $disposition[1],
            ],
          ],
          'text' => $sttsPulang,
        ],
      ];
    }

    // Lokasi kamar rawat + ekstensi ServiceClass (kelas perawatan).
    $location = [
      'location' => [
        'reference' => (string) ($ref['location'] ?? ''),
        'display' => (string) ($ctx['location_display'] ?? ''),
      ],
      'period' => $period,
    ];
    $kelas = trim((string) ($inap['kelas'] ?? ''));
    if ($kelas !== '') {
      $serviceClass = self::SERVICE_CLASS[trim(strtolower($kelas))] ?? ['', 'Kelas ' . $kelas];
      $location['extension'] = [
        [
          'url' => 'https://fhir.kemkes.go.id/r4/StructureDefinition/ServiceClass',
          'extension' => [
            [
              'url' => 'value',
              'valueCodeableConcept' => [
                'coding' => [
                  [
                    'system' => 'http://terminology.kemkes.go.id/CodeSystem/locationServiceClass-Inpatient',
                    'code' => $serviceClass[0] !== '' ? $serviceClass[0] : $kelas,
                    'display' => $serviceClass[1],
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
        ],
      ];
    }
    $encounter['location'] = [$location];

    return [SatuSehatBundleBuilder::entry($encounter, (string) ($ctx['uuid_encounter'] ?? ''))];
  }
}
