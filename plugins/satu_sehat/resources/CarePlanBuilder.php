<?php

namespace Plugins\Satu_Sehat\Resources;

use Plugins\Satu_Sehat\Services\SatuSehatBundleBuilder;

/**
 * Builder resource FHIR CarePlan untuk ERM Rawat Jalan (BAB 8 rencana rawat + BAB 9 instruksi).
 *
 * Sumber data: pemeriksaan_ralan.rtl dan pemeriksaan_ralan.instruksi.
 */
class CarePlanBuilder
{
  public static function build(array $ctx): array
  {
    $emr = (array) ($ctx['emr'] ?? []);
    $pemeriksaan = (array) ($emr['pemeriksaan'] ?? []);
    $rtl = trim((string) ($pemeriksaan['rtl'] ?? ''));
    $instruksi = trim((string) ($pemeriksaan['instruksi'] ?? ''));
    if ($rtl === '' && $instruksi === '') {
      return [];
    }

    $time = (array) ($ctx['time'] ?? []);
    $carePlan = [
      'resourceType' => 'CarePlan',
      'status' => 'active',
      'intent' => 'plan',
      'category' => [
        [
          'coding' => [
            [
              'system' => 'http://snomed.info/sct',
              'code' => '386053000',
              'display' => 'Evaluation procedure',
            ],
          ],
          'text' => 'Rencana Rawat',
        ],
      ],
      'title' => 'Rencana Rawat dan Instruksi Medik/Keperawatan',
      'description' => $rtl,
      'subject' => ['reference' => (string) (($ctx['ref'] ?? [])['patient'] ?? '')],
      'encounter' => ['reference' => (string) (($ctx['ref'] ?? [])['encounter'] ?? '')],
      'created' => (string) ($time['now'] ?? ''),
      'period' => [
        'start' => (string) ($time['reg'] ?? ''),
      ],
      'author' => ['reference' => (string) (($ctx['ref'] ?? [])['practitioner'] ?? '')],
    ];

    if ($instruksi !== '') {
      $carePlan['activity'] = [
        [
          'detail' => [
            'kind' => 'ServiceRequest',
            'description' => $instruksi,
            'status' => 'completed',
          ],
        ],
      ];
    }

    return [SatuSehatBundleBuilder::entry($carePlan)];
  }
}
