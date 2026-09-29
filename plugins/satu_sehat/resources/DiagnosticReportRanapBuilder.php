<?php

namespace Plugins\Satu_Sehat\Resources;

use Plugins\Satu_Sehat\Services\SatuSehatBundleBuilder;

/**
 * Builder resource FHIR DiagnosticReport untuk ERM Rawat Inap mengikuti template
 * Bundle Transaction Rawat Inap Kemenkes:
 *
 * - Laboratorium (v2-0074 "CH" Chemistry) dengan specimen, result, conclusionCode
 * - Radiologi (v2-0074 "RAD" Radiology) dengan result, conclusion
 */
class DiagnosticReportRanapBuilder
{
  /** @param string $scope 'lab', 'rad', atau 'all'. */
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
    $auth = $ctx['auth'] ?? null;
    $entries = [];

    foreach ((array) ($emr['lab_items'] ?? []) as $group) {
      $group = (array) $group;
      $map = (array) ($group['map'] ?? []);
      $nama = (string) ($group['nm_perawatan'] ?? ($map['display'] ?? ($group['kd_jenis_prw'] ?? '')));
      $groupKey = ObservationBuilder::labGroupKey($group);
      $effective = $auth !== null
        ? $auth->fhirTime(trim((string) (($group['tgl_periksa'] ?? '') . ' ' . ($group['jam'] ?? ''))), true)
        : (string) (($ctx['time'] ?? [])['now'] ?? '');

      $coding = [];
      if (trim((string) ($map['code'] ?? '')) !== '') {
        $coding[] = [
          'system' => (string) ($map['system'] ?? 'http://loinc.org'),
          'code' => (string) $map['code'],
          'display' => $nama,
        ];
      }

      $report = [
        'resourceType' => 'DiagnosticReport',
        'identifier' => [
          [
            'use' => 'official',
            'system' => 'http://sys-ids.kemkes.go.id/diagnostic/' . (string) ($ctx['organization_id'] ?? '') . '/lab',
            'value' => str_replace(['@', ' ', ':'], '-', $groupKey),
          ],
        ],
        'status' => 'final',
        'category' => [
          [
            'coding' => [
              [
                'system' => 'http://terminology.hl7.org/CodeSystem/v2-0074',
                'code' => 'CH',
                'display' => 'Chemistry',
              ],
            ],
          ],
        ],
        'code' => ['coding' => $coding, 'text' => $nama],
        'subject' => ['reference' => (string) (($ctx['ref'] ?? [])['patient'] ?? '')],
        'encounter' => ['reference' => (string) (($ctx['ref'] ?? [])['encounter'] ?? '')],
        'effectiveDateTime' => $effective,
        'issued' => (string) (($ctx['time'] ?? [])['now'] ?? $effective),
        'performer' => [
          ['reference' => (string) (($ctx['ref'] ?? [])['practitioner'] ?? '')],
          ['reference' => (string) (($ctx['ref'] ?? [])['organization'] ?? '')],
        ],
      ];

      // Result: Observation laboratorium milik grup ini.
      $result = [];
      foreach ((array) (($ctx['built'] ?? [])['observations_lab'] ?? []) as $entry) {
        $value = (string) ($entry['resource']['identifier'][0]['value'] ?? '');
        if ($groupKey !== '' && strpos($value, $groupKey . '#') === 0) {
          $result[] = ['reference' => (string) ($entry['fullUrl'] ?? '')];
        }
      }
      if (!empty($result)) {
        $report['result'] = $result;
      }

      // Specimen terkait (bila dibangun).
      $specimen = (array) (($ctx['built'] ?? [])['specimens'] ?? []);
      if ($groupKey !== '' && isset($specimen[$groupKey]['fullUrl'])) {
        $report['specimen'] = [['reference' => (string) $specimen[$groupKey]['fullUrl']]];
      }

      // basedOn: ServiceRequest permintaan lab dengan kd_jenis_prw sama.
      foreach ((array) (($ctx['built'] ?? [])['servicerequests_lab'] ?? []) as $entry) {
        $value = (string) ($entry['resource']['identifier'][0]['value'] ?? '');
        $kd = (string) ($group['kd_jenis_prw'] ?? '');
        if ($kd !== '' && (strpos($value, '-' . $kd) !== false || $value === $kd)) {
          $report['basedOn'] = [['reference' => (string) ($entry['fullUrl'] ?? '')]];
          break;
        }
      }

      // conclusionCode: interpretasi pertama hasil lab (H/L/N).
      $conclusion = null;
      foreach ($result as $ref) {
        foreach ((array) (($ctx['built'] ?? [])['observations_lab'] ?? []) as $entry) {
          if ((string) ($entry['fullUrl'] ?? '') === (string) ($ref['reference'] ?? '')) {
            $first = (array) ($entry['resource']['interpretation'][0] ?? []);
            if (!empty($first['coding'])) {
              $conclusion = ['coding' => $first['coding']];
              break 2;
            }
          }
        }
      }
      if ($conclusion !== null) {
        $report['conclusionCode'] = [$conclusion];
      }

      $entries[] = SatuSehatBundleBuilder::entry($report);
    }

    return $entries;
  }

  private static function buildRadiology(array $ctx)
  {
    $emr = (array) ($ctx['emr'] ?? []);
    $auth = $ctx['auth'] ?? null;
    $entries = [];

    foreach ((array) ($emr['rad_items'] ?? []) as $item) {
      $item = (array) $item;
      $map = (array) ($item['map'] ?? []);
      $nama = (string) ($item['nm_perawatan'] ?? ($map['display'] ?? ($item['kd_jenis_prw'] ?? '')));
      $hasil = trim((string) ($item['hasil'] ?? ''));
      $effective = $auth !== null
        ? $auth->fhirTime(trim((string) (($item['tgl_periksa'] ?? '') . ' ' . ($item['jam'] ?? ''))), true)
        : (string) (($ctx['time'] ?? [])['now'] ?? '');

      $coding = [];
      if (trim((string) ($map['code'] ?? '')) !== '') {
        $coding[] = [
          'system' => (string) ($map['system'] ?? 'http://loinc.org'),
          'code' => (string) $map['code'],
          'display' => $nama,
        ];
      }

      $report = [
        'resourceType' => 'DiagnosticReport',
        'identifier' => [
          [
            'use' => 'official',
            'system' => 'http://sys-ids.kemkes.go.id/diagnostic/' . (string) ($ctx['organization_id'] ?? '') . '/rad',
            'value' => str_replace(['@', ' ', ':'], '-', (string) ($item['kd_jenis_prw'] ?? '') . '@' . ($item['tgl_periksa'] ?? '') . '@' . ($item['jam'] ?? '')),
          ],
        ],
        'status' => 'final',
        'category' => [
          [
            'coding' => [
              [
                'system' => 'http://terminology.hl7.org/CodeSystem/v2-0074',
                'code' => 'RAD',
                'display' => 'Radiology',
              ],
            ],
          ],
        ],
        'code' => ['coding' => $coding, 'text' => $nama],
        'subject' => ['reference' => (string) (($ctx['ref'] ?? [])['patient'] ?? '')],
        'encounter' => ['reference' => (string) (($ctx['ref'] ?? [])['encounter'] ?? '')],
        'effectiveDateTime' => $effective,
        'issued' => (string) (($ctx['time'] ?? [])['now'] ?? $effective),
        'performer' => [
          ['reference' => (string) (($ctx['ref'] ?? [])['practitioner'] ?? '')],
          ['reference' => (string) (($ctx['ref'] ?? [])['organization'] ?? '')],
        ],
      ];

      if ($hasil !== '') {
        $report['conclusion'] = $hasil;
      }

      // result -> Observation hasil radiologi yang dibangun ObservationRanapBuilder.
      $key = (string) ($item['kd_jenis_prw'] ?? '') . '@' . ($item['tgl_periksa'] ?? '') . '@' . ($item['jam'] ?? '');
      foreach ((array) (($ctx['built'] ?? [])['observations_rad'] ?? []) as $entry) {
        $value = (string) ($entry['resource']['identifier'][0]['value'] ?? '');
        if ($key !== '' && strpos($value, $key) !== false) {
          $report['result'] = [['reference' => (string) ($entry['fullUrl'] ?? '')]];
          break;
        }
      }

      // basedOn: ServiceRequest permintaan radiologi dengan kd_jenis_prw sama.
      foreach ((array) (($ctx['built'] ?? [])['servicerequests_rad'] ?? []) as $entry) {
        $value = (string) ($entry['resource']['identifier'][0]['value'] ?? '');
        $kd = (string) ($item['kd_jenis_prw'] ?? '');
        if ($kd !== '' && (strpos($value, '-' . $kd) !== false || $value === $kd)) {
          $report['basedOn'] = [['reference' => (string) ($entry['fullUrl'] ?? '')]];
          break;
        }
      }

      $entries[] = SatuSehatBundleBuilder::entry($report);
    }

    return $entries;
  }
}
