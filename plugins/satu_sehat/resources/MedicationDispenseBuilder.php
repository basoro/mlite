<?php

namespace Plugins\Satu_Sehat\Resources;

use Plugins\Satu_Sehat\Services\SatuSehatBundleBuilder;

/**
 * Builder resource FHIR MedicationDispense untuk pengeluaran obat (BAB 16/18).
 *
 * Sumber data: detail_pemberian_obat + aturan_pakai.
 *
 * Catatan validasi SATUSEHAT (hasil uji data nyata):
 * - medicationReference WAJIB berupa reference (rule 10138) — bukan codeableConcept.
 * - authorizingPrescription WAJIB (rule 10393).
 * - identifier hanya namespace resmi prescription/{org} + prescription-item/{org}
 *   (rule 10389).
 * - Quantity wajib punya system+code unit yang valid (rule 10050/10349).
 */
class MedicationDispenseBuilder
{
  public static function build(array &$ctx): array
  {
    $emr = (array) ($ctx['emr'] ?? []);
    $entries = [];
    $extra = [];
    $auth = $ctx['auth'] ?? null;
    $org = (string) ($ctx['organization_id'] ?? '');

    foreach ((array) ($emr['pemberian_items'] ?? []) as $item) {
      $item = (array) $item;
      $kodeBrng = (string) ($item['kode_brng'] ?? '');
      $map = (array) ($item['map'] ?? []);
      $nama = trim((string) ($map['nama_kfa'] ?? ($item['nama_brng'] ?? $kodeBrng)));

      // Referensi Medication WAJIB — bangun entri cadangan bila belum ada.
      $medicationRef = MedicationBuilder::ensureReference($ctx, $item, $extra);

      $handedOver = $auth !== null
        ? $auth->fhirTime((($item['tgl_perawatan'] ?? '') . ' ' . ($item['jam'] ?? '')), true)
        : (string) (($ctx['time'] ?? [])['now'] ?? '');

      $dispense = [
        'resourceType' => 'MedicationDispense',
        'identifier' => [
          [
            'system' => 'http://sys-ids.kemkes.go.id/prescription/' . $org,
            'use' => 'official',
            'value' => $kodeBrng . '@' . ($item['tgl_perawatan'] ?? '') . '@' . ($item['jam'] ?? ''),
          ],
          [
            'system' => 'http://sys-ids.kemkes.go.id/prescription-item/' . $org,
            'use' => 'official',
            'value' => $kodeBrng,
          ],
        ],
        'status' => 'completed',
        'medicationReference' => ['reference' => (string) $medicationRef, 'display' => $nama],
        'subject' => ['reference' => (string) (($ctx['ref'] ?? [])['patient'] ?? '')],
        'context' => ['reference' => (string) (($ctx['ref'] ?? [])['encounter'] ?? '')],
        'performer' => [
          [
            'actor' => ['reference' => (string) (($ctx['ref'] ?? [])['pharmacist'] ?? (($ctx['ref'] ?? [])['practitioner'] ?? ''))],
          ],
        ],
        'whenHandedOver' => $handedOver,
        'dosageInstruction' => [
          [
            'text' => trim((string) ($item['aturan'] ?? ($item['aturan_pakai'] ?? ''))),
          ],
        ],
      ];

      // Quantity hanya bila satuannya dapat dikodekan (UCUM/SNOMED) dengan valid.
      $qty = MedicationRequestBuilder::unitQuantity((float) ($item['jml'] ?? 0), (string) ($map['satuan_den'] ?? ''));
      if ($qty !== null) {
        $dispense['quantity'] = $qty;
      }

      // IGD: kategori outpatient + lokasi penyerahan (template IGD).
      if ((string) ($ctx['erm_type'] ?? 'ralan') === 'igd') {
        $dispense['category'] = [
          'coding' => [
            [
              'system' => 'http://terminology.hl7.org/fhir/CodeSystem/medicationdispense-category',
              'code' => 'outpatient',
              'display' => 'Outpatient',
            ],
          ],
        ];
        if (trim((string) (($ctx['ref'] ?? [])['location'] ?? '')) !== '') {
          $dispense['location'] = [
            'reference' => (string) $ctx['ref']['location'],
            'display' => (string) ($ctx['location_display'] ?? ''),
          ];
        }
      }

      // Resep asal WAJIB (BAB 16/18: authorizingPrescription) — sintesis bila perlu.
      $requestRef = MedicationRequestBuilder::ensureRequest($ctx, $item, $medicationRef, $handedOver, $extra, $map);
      $dispense['authorizingPrescription'] = [['reference' => $requestRef]];

      // Nomor batch memakai elemen batch.lotNumber bawaan FHIR.
      if (trim((string) ($item['no_batch'] ?? '')) !== '') {
        $dispense['batch'] = ['lotNumber' => (string) $item['no_batch']];
      }

      $entries[] = SatuSehatBundleBuilder::entry($dispense);
    }

    return array_merge($extra, $entries);
  }

  /**
   * Cari fullUrl MedicationRequest berdasarkan kode_brng (identifier prescription-item).
   */
  public static function prescriptionReference(array $ctx, $kodeBrng)
  {
    foreach ((array) (($ctx['built'] ?? [])['medrequests'] ?? []) as $entry) {
      foreach ((array) ($entry['resource']['identifier'] ?? []) as $identifier) {
        if (strpos((string) ($identifier['system'] ?? ''), '/prescription-item/') !== false
          && (string) ($identifier['value'] ?? '') === $kodeBrng) {
          return (string) ($entry['fullUrl'] ?? '');
        }
      }
    }
    return '';
  }
}
