<?php

namespace Plugins\Satu_Sehat\Resources;

use Plugins\Satu_Sehat\Services\SatuSehatBundleBuilder;

/**
 * Builder resource FHIR DiagnosticReport untuk ERM Rawat Jalan (BAB 10a lab & 10b radiologi).
 *
 * Hasil lab direferensikan dari Observation laboratorium yang sudah dibangun
 * (dicocokkan lewat identifier Observation, lihat ObservationBuilder::labGroupKey()).
 */
class DiagnosticReportBuilder
{
  /**
   * @param string $scope 'lab', 'rad', atau 'all'.
   */
  public static function build(array $ctx, $scope = 'all'): array
  {
    $entries = [];
    if ($scope === 'lab' || $scope === 'all') {
      $entries = array_merge($entries, self::buildLab($ctx));
    }
    if ($scope === 'rad' || $scope === 'all') {
      $entries = array_merge($entries, self::buildRadiology($ctx));
    }
    return $entries;
  }

  private static function buildLab(array $ctx)
  {
    $emr = (array) ($ctx['emr'] ?? []);
    $entries = [];

    foreach ((array) ($emr['lab_items'] ?? []) as $group) {
      $group = (array) $group;
      $map = (array) ($group['map'] ?? []);
      $nama = (string) ($group['nm_perawatan'] ?? ($map['display'] ?? ($group['kd_jenis_prw'] ?? '')));
      $groupKey = ObservationBuilder::labGroupKey($group);

      $report = self::baseReport($ctx, 'laboratory', 'Laboratory', $map, $nama, $group);
      $report['result'] = self::resultReferences($ctx, 'observations_lab', $groupKey);

      $entries[] = SatuSehatBundleBuilder::entry($report);
    }

    return $entries;
  }

  private static function buildRadiology(array $ctx)
  {
    $emr = (array) ($ctx['emr'] ?? []);
    $entries = [];

    foreach ((array) ($emr['rad_items'] ?? []) as $item) {
      $item = (array) $item;
      $map = (array) ($item['map'] ?? []);
      $nama = (string) ($item['nm_perawatan'] ?? ($map['display'] ?? ($item['kd_jenis_prw'] ?? '')));

      $report = self::baseReport($ctx, 'radiology', 'Radiology', $map, $nama, $item);
      $hasil = trim((string) ($item['hasil'] ?? ''));
      if ($hasil !== '') {
        $report['conclusion'] = $hasil;
      }

      $entries[] = SatuSehatBundleBuilder::entry($report);
    }

    return $entries;
  }

  private static function baseReport(array $ctx, $categoryCode, $categoryDisplay, array $map, $nama, array $source)
  {
    $auth = $ctx['auth'] ?? null;
    $effective = $auth !== null
      ? $auth->fhirTime((($source['tgl_periksa'] ?? '') . ' ' . ($source['jam'] ?? '')), true)
      : (string) (($ctx['time'] ?? [])['now'] ?? '');

    $coding = [];
    $loinc = (string) ($map['code'] ?? '');
    if ($loinc !== '') {
      $coding[] = [
        'system' => (string) ($map['system'] ?? 'http://loinc.org'),
        'code' => $loinc,
        'display' => $nama,
      ];
    }

    return [
      'resourceType' => 'DiagnosticReport',
      'identifier' => [
        [
          'system' => 'http://sys-ids.kemkes.go.id/erm-ralan/report/' . (string) ($ctx['organization_id'] ?? ''),
          'value' => (string) ($source['kd_jenis_prw'] ?? '') . '@' . ($source['tgl_periksa'] ?? '') . '@' . ($source['jam'] ?? ''),
        ],
      ],
      'status' => 'final',
      'category' => [
        [
          'coding' => [
            [
              'system' => 'http://terminology.hl7.org/CodeSystem/v2-0074',
              'code' => strtoupper(substr($categoryCode, 0, 3)),
              'display' => $categoryDisplay,
            ],
          ],
        ],
      ],
      'code' => [
        'coding' => $coding,
        'text' => $nama,
      ],
      'subject' => ['reference' => (string) (($ctx['ref'] ?? [])['patient'] ?? '')],
      'encounter' => ['reference' => (string) (($ctx['ref'] ?? [])['encounter'] ?? '')],
      'effectiveDateTime' => $effective,
      'issued' => $effective,
      'performer' => [['reference' => (string) (($ctx['ref'] ?? [])['practitioner'] ?? '')]],
    ];
  }

  /**
   * Referensi Observation hasil lab milik satu grup pemeriksaan.
   */
  private static function resultReferences(array $ctx, $builtKey, $groupKey)
  {
    $refs = [];
    foreach ((array) ($ctx['built'][$builtKey] ?? []) as $entry) {
      $value = (string) ($entry['resource']['identifier'][0]['value'] ?? '');
      if ($groupKey !== '' && strpos($value, $groupKey . '#') === 0) {
        $refs[] = ['reference' => (string) ($entry['fullUrl'] ?? '')];
      }
    }
    return $refs;
  }
}
