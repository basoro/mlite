<?php

namespace Plugins\Satu_Sehat\Resources;

use Plugins\Satu_Sehat\Services\SatuSehatBundleBuilder;

/**
 * Builder resource FHIR AllergyIntolerance (riwayat alergi, BAB 3).
 */
class AllergyIntoleranceBuilder
{
  /** Nilai kolom alergi yang dianggap "tidak ada alergi". */
  const EMPTY_VALUES = ['', '-', 'tidak', 'tidak ada', 'tidak ada alergi', 'no', 'none', 'n/a'];

  public static function build(array $ctx): array
  {
    $emr = (array) ($ctx['emr'] ?? []);
    $pemeriksaan = (array) ($emr['pemeriksaan'] ?? []);
    $alergi = trim((string) ($pemeriksaan['alergi'] ?? ''));
    if ($alergi === '' || in_array(strtolower($alergi), self::EMPTY_VALUES, true)) {
      return [];
    }

    $allergy = [
      'resourceType' => 'AllergyIntolerance',
      'clinicalStatus' => [
        'coding' => [
          [
            'system' => 'http://terminology.hl7.org/CodeSystem/allergyintolerance-clinical',
            'code' => 'active',
            'display' => 'Active',
          ],
        ],
      ],
      'verificationStatus' => [
        'coding' => [
          [
            'system' => 'http://terminology.hl7.org/CodeSystem/allergyintolerance-verification',
            'code' => 'unconfirmed',
            'display' => 'Unconfirmed',
          ],
        ],
      ],
      'type' => 'allergy',
      'code' => [
        'text' => $alergi,
      ],
      'patient' => ['reference' => (string) (($ctx['ref'] ?? [])['patient'] ?? '')],
      'recordedDate' => (string) (($ctx['time'] ?? [])['now'] ?? ''),
      'recorder' => ['reference' => (string) (($ctx['ref'] ?? [])['practitioner'] ?? '')],
      'encounter' => ['reference' => (string) (($ctx['ref'] ?? [])['encounter'] ?? '')],
    ];

    return [SatuSehatBundleBuilder::entry($allergy)];
  }
}
