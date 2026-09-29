<?php

namespace Plugins\Satu_Sehat\Resources;

use Plugins\Satu_Sehat\Services\SatuSehatBundleBuilder;

/**
 * Builder resource FHIR Goal (tujuan perawatan, BAB 7) untuk ERM Rawat Inap.
 *
 * Sumber: pemeriksaan_ranap.rtl (rencana tindak lanjut/tujuan) dan
 * emr['tujuan_perawatan_items'] = [{description, measure_code, measure_display,
 * outcome_code, outcome_display, due_date}] untuk target terukur.
 */
class GoalBuilder
{
  public static function build(array $ctx): array
  {
    $emr = (array) ($ctx['emr'] ?? []);
    $pemeriksaan = (array) ($emr['pemeriksaan'] ?? []);
    $deskripsi = trim((string) ($pemeriksaan['rtl'] ?? ''));
    $items = (array) ($emr['tujuan_perawatan_items'] ?? []);

    if ($deskripsi === '' && empty($items)) {
      return [];
    }
    if ($deskripsi === '') {
      $deskripsi = 'Perawatan dilakukan untuk mengatasi keluhan pasien';
    }

    $time = (array) ($ctx['time'] ?? []);
    $goal = [
      'resourceType' => 'Goal',
      'lifecycleStatus' => 'planned',
      'category' => [
        [
          'coding' => [
            [
              'system' => 'http://terminology.hl7.org/CodeSystem/goal-category',
              'code' => 'nursing',
              'display' => 'Nursing',
            ],
          ],
        ],
      ],
      'description' => ['text' => $deskripsi],
      'subject' => ['reference' => (string) (($ctx['ref'] ?? [])['patient'] ?? '')],
      'expressedBy' => ['reference' => (string) (($ctx['ref'] ?? [])['practitioner'] ?? '')],
    ];

    if (!empty($time['discharge'])) {
      $goal['statusDate'] = substr((string) $time['discharge'], 0, 10);
    }

    $target = [];
    foreach ($items as $item) {
      $item = (array) $item;
      $entry = [];
      if (trim((string) ($item['measure_code'] ?? '')) !== '') {
        $entry['measure'] = [
          'coding' => [
            [
              'system' => (string) ($item['measure_system'] ?? 'http://loinc.org'),
              'code' => (string) $item['measure_code'],
              'display' => (string) ($item['measure_display'] ?? ''),
            ],
          ],
        ];
      }
      if (trim((string) ($item['outcome_code'] ?? '')) !== '') {
        $entry['detailCodeableConcept'] = [
          'coding' => [
            [
              'system' => (string) ($item['outcome_system'] ?? 'http://snomed.info/sct'),
              'code' => (string) $item['outcome_code'],
              'display' => (string) ($item['outcome_display'] ?? ''),
            ],
          ],
        ];
      }
      if (trim((string) ($item['due_date'] ?? '')) !== '') {
        $entry['dueDate'] = (string) $item['due_date'];
      }
      if (!empty($entry)) {
        $target[] = $entry;
      }
    }
    if (!empty($target)) {
      $goal['target'] = $target;
    }

    // Tujuan perawatan dikaitkan ke kondisi keluhan utama / diagnosis primer.
    $built = (array) ($ctx['built'] ?? []);
    $cc = (array) ($built['conditions_cc'] ?? []);
    $dx = (array) ($built['conditions_dx'] ?? []);
    $addressed = (string) (($cc[0]['fullUrl'] ?? '') ?: ($dx[0]['fullUrl'] ?? ''));
    if ($addressed !== '') {
      $goal['addresses'] = [['reference' => $addressed]];
    }

    return [SatuSehatBundleBuilder::entry($goal)];
  }
}
