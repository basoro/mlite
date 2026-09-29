<?php

namespace Plugins\Satu_Sehat\Resources;

use Plugins\Satu_Sehat\Services\SatuSehatBundleBuilder;

/**
 * Builder resource FHIR RiskAssessment (penilaian risiko, BAB 13) untuk ERM Rawat Inap.
 *
 * Sumber: pemeriksaan_ranap.penilaian (mitigasi), pemeriksaan_ranap.rtl,
 * dan emr['penilaian_risiko_items'] = [{outcome_code, outcome_display,
 * probability, mitigation}] untuk prediksi terukur.
 */
class RiskAssessmentBuilder
{
  public static function build(array $ctx): array
  {
    $emr = (array) ($ctx['emr'] ?? []);
    $pemeriksaan = (array) ($emr['pemeriksaan'] ?? []);
    $penilaian = trim((string) ($pemeriksaan['penilaian'] ?? ''));
    $rtl = trim((string) ($pemeriksaan['rtl'] ?? ''));
    $items = (array) ($emr['penilaian_risiko_items'] ?? []);

    if ($penilaian === '' && $rtl === '' && empty($items)) {
      return [];
    }

    $built = (array) ($ctx['built'] ?? []);
    $dx = (array) ($built['conditions_dx'] ?? []);
    $cc = (array) ($built['conditions_cc'] ?? []);
    $primerRef = (string) ($dx[0]['fullUrl'] ?? '');

    $risk = [
      'resourceType' => 'RiskAssessment',
      'status' => 'final',
      'code' => [
        'coding' => [
          [
            'system' => 'http://snomed.info/sct',
            'code' => '709510001',
            'display' => 'Assessment of risk for disease',
          ],
        ],
      ],
      'subject' => ['reference' => (string) (($ctx['ref'] ?? [])['patient'] ?? '')],
      'encounter' => ['reference' => (string) (($ctx['ref'] ?? [])['encounter'] ?? '')],
      'performer' => ['reference' => (string) (($ctx['ref'] ?? [])['practitioner'] ?? '')],
    ];

    if ($primerRef !== '') {
      $risk['condition'] = ['reference' => $primerRef];
    }
    if (!empty($cc)) {
      $risk['reasonReference'] = [['reference' => (string) ($cc[0]['fullUrl'] ?? '')]];
    }

    $prediction = [];
    foreach ($items as $item) {
      $item = (array) $item;
      $entry = [];
      if (trim((string) ($item['outcome_code'] ?? '')) !== '') {
        $entry['outcome'] = [
          'coding' => [
            [
              'system' => (string) ($item['outcome_system'] ?? 'http://snomed.info/sct'),
              'code' => (string) $item['outcome_code'],
              'display' => (string) ($item['outcome_display'] ?? ''),
            ],
          ],
        ];
      }
      if (is_numeric($item['probability'] ?? null)) {
        $entry['probabilityDecimal'] = (float) $item['probability'];
      }
      if (!empty($entry)) {
        $prediction[] = $entry;
      }
    }
    if (!empty($prediction)) {
      $risk['prediction'] = $prediction;
    }

    $mitigation = trim((string) (($items[0]['mitigation'] ?? '') ?: ($rtl !== '' ? $rtl : $penilaian)));
    if ($mitigation !== '') {
      $risk['mitigation'] = $mitigation;
    }

    return [SatuSehatBundleBuilder::entry($risk)];
  }
}
