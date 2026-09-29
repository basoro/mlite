<?php

namespace Plugins\Satu_Sehat\Resources;

use Plugins\Satu_Sehat\Services\SatuSehatBundleBuilder;

/**
 * Builder resource FHIR Specimen (spesimen pemeriksaan laboratorium, BAB 10a).
 *
 * Sumber: emr['lab_items'] (grup periksa_lab) + permintaan lab terkait.
 * Referensi ServiceRequest diambil dari ctx['built']['servicerequests_lab']
 * (dicocokkan lewat kd_jenis_prw) untuk field Specimen.request.
 */
class SpecimenBuilder
{
  /**
   * @return array<string,array> Entri per kunci grup lab (lihat ObservationBuilder::labGroupKey()).
   */
  public static function build(array $ctx): array
  {
    $emr = (array) ($ctx['emr'] ?? []);
    $auth = $ctx['auth'] ?? null;
    $entries = [];

    foreach ((array) ($emr['lab_items'] ?? []) as $group) {
      $group = (array) $group;
      $groupKey = ObservationBuilder::labGroupKey($group);
      $waktu = trim((string) (($group['tgl_periksa'] ?? '') . ' ' . ($group['jam'] ?? '')));
      $collected = $auth !== null ? $auth->fhirTime($waktu, true) : (string) (($ctx['time'] ?? [])['now'] ?? '');
      $nama = trim((string) ($group['nm_perawatan'] ?? ($group['kd_jenis_prw'] ?? '')));

      $specimen = [
        'resourceType' => 'Specimen',
        'identifier' => [
          [
            'system' => 'http://sys-ids.kemkes.go.id/specimen/' . (string) ($ctx['organization_id'] ?? ''),
            'value' => 'SP-' . str_replace(['@', ' ', ':'], '-', $groupKey),
            'assigner' => ['reference' => (string) (($ctx['ref'] ?? [])['organization'] ?? '')],
          ],
        ],
        'status' => 'available',
        'type' => [
          'coding' => [
            [
              'system' => 'http://snomed.info/sct',
              'code' => '119297000',
              'display' => 'Blood specimen',
            ],
          ],
          'text' => 'Spesimen ' . $nama,
        ],
        'subject' => [
          'reference' => (string) (($ctx['ref'] ?? [])['patient'] ?? ''),
          'display' => (string) ($ctx['patient_name'] ?? ''),
        ],
        'receivedTime' => $collected,
        'collection' => [
          'collector' => [
            'reference' => (string) (($ctx['ref'] ?? [])['practitioner'] ?? ''),
            'display' => (string) ($ctx['practitioner_name'] ?? ''),
          ],
          'collectedDateTime' => $collected,
          'method' => [
            'coding' => [
              [
                'system' => 'http://snomed.info/sct',
                'code' => '82078001',
                'display' => 'Collection of blood specimen for laboratory',
              ],
            ],
          ],
        ],
        'condition' => [
          ['text' => 'Kondisi spesimen ' . $nama . ' baik'],
        ],
      ];

      // Request: ServiceRequest permintaan lab dengan kd_jenis_prw sama.
      $requestRef = self::serviceRequestReference($ctx, (string) ($group['kd_jenis_prw'] ?? ''));
      if ($requestRef !== '') {
        $specimen['request'] = [['reference' => $requestRef]];
      }

      $entries[$groupKey] = SatuSehatBundleBuilder::entry($specimen);
    }

    return $entries;
  }

  private static function serviceRequestReference(array $ctx, $kdJenisPrw)
  {
    if ($kdJenisPrw === '') {
      return '';
    }
    foreach ((array) (($ctx['built'] ?? [])['servicerequests_lab'] ?? []) as $entry) {
      $value = (string) ($entry['resource']['identifier'][0]['value'] ?? '');
      if (strpos($value, '-' . $kdJenisPrw) !== false || $value === $kdJenisPrw) {
        return (string) ($entry['fullUrl'] ?? '');
      }
    }
    return '';
  }
}
