<?php

namespace Plugins\Satu_Sehat\Resources;

use Plugins\Satu_Sehat\Services\SatuSehatBundleBuilder;

/**
 * Builder resource FHIR Encounter (Kunjungan Rawat Jalan, Encounter.class = AMB).
 *
 * Sumber data: reg_periksa + pemeriksaan_ralan + mapping lokasi/praktisi.
 * Referensi resource lain dikirim melalui $ctx (lihat SatuSehatErmRalanService::buildContext()).
 */
class EncounterBuilder
{
  public static function build(array $ctx): array
  {
    $emr = (array) ($ctx['emr'] ?? []);
    $reg = (array) ($emr['reg_periksa'] ?? []);
    $pasien = (array) ($emr['pasien'] ?? []);
    $poli = (array) ($emr['poliklinik'] ?? []);
    $ref = (array) ($ctx['ref'] ?? []);
    $time = (array) ($ctx['time'] ?? []);

    $class = ($ctx['erm_type'] ?? 'ralan') === 'ralan'
      ? ['system' => 'http://terminology.hl7.org/CodeSystem/v3-ActCode', 'code' => 'AMB', 'display' => 'ambulatory']
      : ['system' => 'http://terminology.hl7.org/CodeSystem/v3-ActCode', 'code' => 'IMP', 'display' => 'inpatient encounter'];

    $period = ['start' => (string) ($time['reg'] ?? '')];
    if (!empty($time['end'])) {
      $period['end'] = (string) $time['end'];
    }

    $encounter = [
      'resourceType' => 'Encounter',
      'identifier' => [
        [
          'system' => 'http://sys-ids.kemkes.go.id/encounter/' . (string) ($ctx['organization_id'] ?? ''),
          'value' => (string) ($ctx['no_rawat'] ?? ''),
        ],
      ],
      'status' => 'finished',
      'class' => $class,
      'subject' => [
        'reference' => (string) ($ref['patient'] ?? ''),
        'display' => (string) ($ctx['patient_name'] ?? ($pasien['nm_pasien'] ?? '')),
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
      'location' => [
        [
          'location' => [
            'reference' => (string) ($ref['location'] ?? ''),
            'display' => (string) ($ctx['location_display'] ?? trim(($reg['kd_poli'] ?? '') . ' ' . ($poli['nm_poli'] ?? ''))),
          ],
        ],
      ],
      'statusHistory' => [
        [
          'status' => 'finished',
          'period' => $period,
        ],
      ],
      'serviceProvider' => [
        'reference' => (string) ($ref['organization'] ?? ''),
      ],
    ];

    // Diagnosis (BAB 12) direferensikan dari Condition yang sudah dibangun
    // sebelumnya. Encounter.diagnosis wajib minimal 1 (rule 10457) — saat tidak
    // ada diagnosis ICD-10, rujuk Condition keluhan utama dengan peran CC.
    $diagnosis = [];
    $conditions = (array) ($ctx['built']['conditions_dx'] ?? []);
    $useCode = 'DD';
    $useDisplay = 'Discharge diagnosis';
    if (empty($conditions)) {
      $conditions = (array) ($ctx['built']['conditions'] ?? []);
      $useCode = 'CC';
      $useDisplay = 'Chief Complaint';
    }
    foreach ($conditions as $index => $conditionEntry) {
      $resource = (array) ($conditionEntry['resource'] ?? []);
      $diagnosis[] = [
        'use' => [
          'coding' => [
            [
              'system' => 'http://terminology.hl7.org/CodeSystem/diagnosis-role',
              'code' => $useCode,
              'display' => $useDisplay,
            ],
          ],
        ],
        'condition' => [
          'reference' => (string) ($conditionEntry['fullUrl'] ?? ''),
          'display' => (string) ($resource['code']['text'] ?? ''),
        ],
        'rank' => (int) ($index + 1),
      ];
    }
    if (!empty($diagnosis)) {
      $encounter['diagnosis'] = $diagnosis;
    }

    return [SatuSehatBundleBuilder::entry($encounter, (string) ($ctx['uuid_encounter'] ?? ''))];
  }
}
