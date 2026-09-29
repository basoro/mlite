<?php

namespace Plugins\Satu_Sehat\Resources;

use Plugins\Satu_Sehat\Services\SatuSehatBundleBuilder;

/**
 * Builder resource FHIR MedicationAdministration (pemberian obat, BAB 17)
 * untuk ERM Rawat Inap.
 *
 * Sumber: emr['pemberian_items'] (detail_pemberian_obat + aturan_pakai).
 * Referensi Medication diambil dari ctx['built']['medications'] bila cocok,
 * selain itu dipakai contained Medication (lengkap dengan batch/no_batch).
 */
class MedicationAdministrationBuilder
{
  public static function build(array &$ctx): array
  {
    $emr = (array) ($ctx['emr'] ?? []);
    $entries = [];
    $extra = [];
    $auth = $ctx['auth'] ?? null;

    foreach ((array) ($emr['pemberian_items'] ?? []) as $item) {
      $item = (array) $item;
      $kodeBrng = (string) ($item['kode_brng'] ?? '');
      $map = (array) ($item['map'] ?? []);
      $nama = trim((string) ($map['nama_kfa'] ?? ($item['nama_brng'] ?? $kodeBrng)));

      $admin = [
        'resourceType' => 'MedicationAdministration',
        'status' => 'completed',
        'category' => [
          'coding' => [
            [
              'system' => 'http://terminology.hl7.org/CodeSystem/medication-admin-category',
              'code' => 'inpatient',
              'display' => 'Inpatient',
            ],
          ],
        ],
        'subject' => ['reference' => (string) (($ctx['ref'] ?? [])['patient'] ?? '')],
        'context' => ['reference' => (string) (($ctx['ref'] ?? [])['encounter'] ?? '')],
        'performer' => [
          [
            'actor' => [
              'reference' => (string) (($ctx['ref'] ?? [])['practitioner'] ?? (($ctx['ref'] ?? [])['pharmacist'] ?? '')),
              'display' => (string) ($ctx['practitioner_name'] ?? ''),
            ],
          ],
        ],
        'reasonCode' => [
          [
            'coding' => [
              [
                'system' => 'http://terminology.hl7.org/CodeSystem/reason-medication-given',
                'code' => 'b',
                'display' => 'Given as Ordered',
              ],
            ],
          ],
        ],
        'dosage' => [
          'route' => self::route($map, $item),
        ],
      ];

      // Dosis hanya bila satuannya dapat dikodekan (UCUM/SNOMED) dengan valid.
      $dose = MedicationRequestBuilder::unitQuantity((float) ($item['jml'] ?? 0), (string) ($map['satuan_den'] ?? ''));
      if ($dose !== null) {
        $admin['dosage']['dose'] = $dose;
      }

      // Efektif waktu pemberian (jika tersedia bila tidak ada -> waktu sekarang).
      $waktu = trim((string) (($item['tgl_perawatan'] ?? '') . ' ' . ($item['jam'] ?? '')));
      $effective = $auth !== null ? $auth->fhirTime($waktu, true) : (string) (($ctx['time'] ?? [])['now'] ?? '');
      $admin['effectivePeriod'] = ['start' => $effective, 'end' => $effective];

      // Referensi Medication WAJIB berupa reference — entri bundle, sintesis bila perlu.
      $medicationRef = MedicationBuilder::ensureReference($ctx, $item, $extra);
      $admin['medicationReference'] = ['reference' => $medicationRef, 'display' => $nama];

      // Referensi MedicationRequest asal — sintesis bila perlu.
      $admin['request'] = [
        'reference' => MedicationRequestBuilder::ensureRequest($ctx, $item, $medicationRef, $effective, $extra, $map),
      ];

      $entries[] = SatuSehatBundleBuilder::entry($admin);
    }

    return array_merge($extra, $entries);
  }

  private static function route(array $map, array $item)
  {
    if (trim((string) ($map['kode_route'] ?? '')) !== '') {
      // Kode rute KFA = kode rute ATC (template Kemenkes memakai whocc.no/atc).
      return [
        'coding' => [
          [
            'system' => 'http://www.whocc.no/atc',
            'code' => (string) $map['kode_route'],
            'display' => (string) ($map['nama_route'] ?? ''),
          ],
        ],
      ];
    }
    return ['text' => trim((string) ($item['aturan'] ?? ''))];
  }
}
