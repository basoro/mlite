<?php

namespace Plugins\Satu_Sehat\Resources;

use Plugins\Satu_Sehat\Services\SatuSehatBundleBuilder;

/**
 * Builder resource FHIR FamilyMemberHistory (riwayat penyakit keluarga, BAB 3).
 *
 * Sumber: emr['riwayat_keluarga_items'] = [{relationship_code, relationship_display,
 * deceased, condition_code, condition_display, outcome_code, outcome_display,
 * contributed_to_death, onset_string}].
 */
class FamilyMemberHistoryBuilder
{
  public static function build(array $ctx): array
  {
    $emr = (array) ($ctx['emr'] ?? []);
    $entries = [];
    $auth = $ctx['auth'] ?? null;

    foreach ((array) ($emr['riwayat_keluarga_items'] ?? []) as $item) {
      $item = (array) $item;
      $conditionDisplay = trim((string) ($item['condition_display'] ?? ($item['condition_text'] ?? '')));
      $conditionCode = trim((string) ($item['condition_code'] ?? ''));
      if ($conditionDisplay === '' && $conditionCode === '') {
        continue;
      }

      $conditionCoding = [];
      if ($conditionCode !== '') {
        $conditionCoding[] = [
          'system' => 'http://snomed.info/sct',
          'code' => $conditionCode,
          'display' => $conditionDisplay,
        ];
      }

      $conditionEntry = [
        'code' => [
          'coding' => $conditionCoding,
          'text' => $conditionDisplay !== '' ? $conditionDisplay : $conditionCode,
        ],
        'contributedToDeath' => (bool) ($item['contributed_to_death'] ?? false),
      ];
      if (trim((string) ($item['outcome_code'] ?? '')) !== '') {
        $conditionEntry['outcome'] = [
          'coding' => [
            [
              'system' => 'http://snomed.info/sct',
              'code' => (string) $item['outcome_code'],
              'display' => (string) ($item['outcome_display'] ?? ''),
            ],
          ],
        ];
      }
      if (trim((string) ($item['onset_string'] ?? '')) !== '') {
        $conditionEntry['onsetString'] = (string) $item['onset_string'];
      }

      $history = [
        'resourceType' => 'FamilyMemberHistory',
        'status' => 'completed',
        'patient' => [
          'reference' => (string) (($ctx['ref'] ?? [])['patient'] ?? ''),
          'display' => (string) ($ctx['patient_name'] ?? ''),
        ],
        'date' => $auth !== null
          ? $auth->fhirTime((string) (($ctx['time'] ?? [])['exam'] ?? ''), true)
          : (string) (($ctx['time'] ?? [])['now'] ?? ''),
        'relationship' => [
          'coding' => [
            [
              'system' => 'http://terminology.hl7.org/CodeSystem/v3-RoleCode',
              'code' => (string) ($item['relationship_code'] ?? 'FAMMEMB'),
              'display' => (string) ($item['relationship_display'] ?? 'family member'),
            ],
          ],
        ],
        'deceasedBoolean' => (bool) ($item['deceased'] ?? false),
        'condition' => [$conditionEntry],
      ];

      $entries[] = SatuSehatBundleBuilder::entry($history);
    }

    return $entries;
  }
}
