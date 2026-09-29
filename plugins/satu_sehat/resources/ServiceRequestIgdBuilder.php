<?php

namespace Plugins\Satu_Sehat\Resources;

/**
 * Builder resource FHIR ServiceRequest untuk ERM IGD mengikuti template Bundle
 * Transaction IGD Kemenkes.
 *
 * Merupakan pembungkus ServiceRequestBuilder::build() (identik identifier
 * "{noorder}-{kd_jenis_prw}" agar cocok dengan SpecimenBuilder /
 * DiagnosticReportRanapBuilder) yang melengkapi:
 *
 * - priority "stat" (permintaan penunjang IGD bersifat segera)
 * - orderDetail modality + AE Title untuk radiologi
 * - reasonReference -> Condition diagnosis awal
 * - supportingInfo -> prosedur persiapan (lab), status kehamilan/prosedur
 *   persiapan/alergi (radiologi), sesuai template.
 */
class ServiceRequestIgdBuilder
{
  /** @param string $scope 'lab', 'rad', atau 'all'. */
  public static function build(array $ctx, $scope = 'all'): array
  {
    $entries = [];
    if ($scope === 'lab' || $scope === 'all') {
      $entries = array_merge($entries, self::decorate(ServiceRequestBuilder::build($ctx, 'lab'), $ctx, 'lab'));
    }
    if ($scope === 'rad' || $scope === 'all') {
      $entries = array_merge($entries, self::decorate(ServiceRequestBuilder::build($ctx, 'rad'), $ctx, 'rad'));
    }
    return $entries;
  }

  /** Lengkapi entri ServiceRequest dengan atribut khas template IGD. */
  private static function decorate(array $entries, array $ctx, $kind)
  {
    $reasonReference = self::reasonReference($ctx);
    $supportingInfo = self::supportingInfo($ctx, $kind);

    foreach ($entries as $i => $entry) {
      $resource = (array) ($entry['resource'] ?? []);

      // Permintaan penunjang IGD bersifat segera.
      $resource['priority'] = 'stat';

      // orderDetail: modality DICOM + AE Title untuk radiologi.
      if ($kind === 'rad') {
        $orderDetail = [];
        $coding = (array) ($resource['code']['coding'][0] ?? []);
        $loinc = (string) ($coding['code'] ?? '');
        $modality = self::modality($loinc);
        if ($modality !== '') {
          $orderDetail[] = [
            'coding' => [
              ['system' => 'http://dicom.nema.org/resources/ontology/DCM', 'code' => $modality],
            ],
            'text' => 'Modality code: ' . $modality,
          ];
        }
        $aeTitle = trim((string) (($ctx['emr']['rad_items'][0]['ae_title'] ?? '')));
        if ($aeTitle !== '') {
          $orderDetail[] = [
            'coding' => [
              ['system' => 'http://sys-ids.kemkes.go.id/ae-title', 'display' => $aeTitle],
            ],
          ];
        }
        if (!empty($orderDetail)) {
          $resource['orderDetail'] = $orderDetail;
        }
      }

      if (!empty($reasonReference)) {
        $resource['reasonReference'] = [$reasonReference];
      }
      if (!empty($supportingInfo)) {
        $resource['supportingInfo'] = $supportingInfo;
      }

      $entry['resource'] = $resource;
      $entries[$i] = $entry;
    }

    return $entries;
  }

  /** Referensi Condition diagnosis awal (template reasonReference). */
  private static function reasonReference(array $ctx)
  {
    foreach ((array) (($ctx['built'] ?? [])['conditions_awal'] ?? []) as $entry) {
      if (!empty($entry['fullUrl'])) {
        return ['reference' => (string) $entry['fullUrl']];
      }
    }
    return null;
  }

  /** supportingInfo sesuai template: lab -> prosedur puasa; rad -> obs kehamilan, prosedur puasa, alergi. */
  private static function supportingInfo(array $ctx, $kind)
  {
    $refs = [];
    $built = (array) ($ctx['built'] ?? []);

    if ($kind === 'lab') {
      if (!empty($built['procedures_pralab']['fullUrl'])) {
        $refs[] = ['reference' => (string) $built['procedures_pralab']['fullUrl']];
      }
      return $refs;
    }

    if (!empty($built['obs_pra_rad']['fullUrl'])) {
      $refs[] = ['reference' => (string) $built['obs_pra_rad']['fullUrl']];
    }
    if (!empty($built['procedures_prarad']['fullUrl'])) {
      $refs[] = ['reference' => (string) $built['procedures_prarad']['fullUrl']];
    }
    foreach ((array) ($built['allergy'] ?? []) as $entry) {
      if (!empty($entry['fullUrl'])) {
        $refs[] = ['reference' => (string) $entry['fullUrl']];
      }
    }
    return $refs;
  }

  /** Modality DICOM dari kode LOINC pemeriksaan radiologi. */
  private static function modality($loinc)
  {
    $loinc = (string) $loinc;
    if ($loinc === '') {
      return '';
    }
    if (preg_match('/^(11525-3|11535-2|11537-8|59777-5)/', $loinc)) {
      return 'US';
    }
    if (preg_match('/^(36642-7|24627-2|24628-0|30799-1|36643-5)/', $loinc)) {
      return 'CT';
    }
    if (preg_match('/^(36644-3|24624-9|24625-6|36645-0)/', $loinc)) {
      return 'MR';
    }
    if (preg_match('/^(30793-2|30794-0|30795-7|30796-5|30797-3|30798-1|39004-7|30792-4)/', $loinc)) {
      return 'DX';
    }
    return 'DX';
  }
}
