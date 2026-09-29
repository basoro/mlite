<?php

namespace Plugins\Satu_Sehat\Resources;

use Plugins\Satu_Sehat\Services\SatuSehatBundleBuilder;

/**
 * Builder resource FHIR NutritionOrder (diet pasien, BAB 18) untuk ERM Rawat Inap.
 *
 * Sumber: emr['diet_items'] = [{type_code, type_display, type_text,
 * exclude_code, exclude_display, nutrient_code, nutrient_display,
 * nutrient_amount, nutrient_unit}].
 */
class NutritionOrderBuilder
{
  public static function build(array $ctx): array
  {
    $emr = (array) ($ctx['emr'] ?? []);
    $entries = [];

    foreach ((array) ($emr['diet_items'] ?? []) as $item) {
      $item = (array) $item;
      $typeCoding = [];
      if (trim((string) ($item['type_code'] ?? '')) !== '') {
        $typeCoding[] = [
          'system' => (string) ($item['type_system'] ?? 'http://snomed.info/sct'),
          'code' => (string) $item['type_code'],
          'display' => (string) ($item['type_display'] ?? ''),
        ];
      }
      $typeText = trim((string) ($item['type_text'] ?? ''));
      if (empty($typeCoding) && $typeText === '') {
        continue;
      }

      $order = [
        'resourceType' => 'NutritionOrder',
        'status' => 'active',
        'intent' => 'proposal',
        'patient' => ['reference' => (string) (($ctx['ref'] ?? [])['patient'] ?? '')],
        'encounter' => ['reference' => (string) (($ctx['ref'] ?? [])['encounter'] ?? '')],
        'dateTime' => (string) (($ctx['time'] ?? [])['now'] ?? ''),
        'orderer' => ['reference' => (string) (($ctx['ref'] ?? [])['practitioner'] ?? '')],
        'oralDiet' => [
          'type' => [
            [
              'coding' => $typeCoding,
              'text' => $typeText !== '' ? $typeText : (string) ($item['type_display'] ?? ''),
            ],
          ],
        ],
      ];

      if (trim((string) ($item['exclude_code'] ?? '')) !== '') {
        $order['excludeFoodModifier'] = [
          [
            'coding' => [
              [
                'system' => (string) ($item['exclude_system'] ?? 'http://snomed.info/sct'),
                'code' => (string) $item['exclude_code'],
                'display' => (string) ($item['exclude_display'] ?? ''),
              ],
            ],
          ],
        ];
      }

      if (trim((string) ($item['nutrient_code'] ?? '')) !== '' && is_numeric($item['nutrient_amount'] ?? null)) {
        $unit = (string) ($item['nutrient_unit'] ?? 'L');
        $order['oralDiet']['nutrient'] = [
          [
            'modifier' => [
              'coding' => [
                [
                  'system' => (string) ($item['nutrient_system'] ?? 'http://snomed.info/sct'),
                  'code' => (string) $item['nutrient_code'],
                  'display' => (string) ($item['nutrient_display'] ?? ''),
                ],
              ],
            ],
            'amount' => [
              'value' => (float) $item['nutrient_amount'],
              'unit' => $unit,
              'system' => 'http://unitsofmeasure.org',
              'code' => $unit,
            ],
          ],
        ];
      }

      $entries[] = SatuSehatBundleBuilder::entry($order);
    }

    return $entries;
  }
}
