<?php

namespace Plugins\Satu_Sehat\Resources;

use Plugins\Satu_Sehat\Services\SatuSehatBundleBuilder;

/**
 * Builder resource FHIR MedicationStatement (riwayat pengobatan pasien, BAB 3).
 *
 * Sumber: emr['riwayat_pengobatan_items'] (default diisi dari pemberian obat
 * selama rawat inap) = [{kode_brng, nama_brng, aturan, tgl, jam, map}].
 * Referensi Medication diambil dari ctx['built']['medications'] bila cocok.
 */
class MedicationStatementBuilder
{
  public static function build(array &$ctx): array
  {
    $emr = (array) ($ctx['emr'] ?? []);
    $entries = [];
    $extra = [];
    $auth = $ctx['auth'] ?? null;
    $seen = [];

    foreach ((array) ($emr['riwayat_pengobatan_items'] ?? []) as $item) {
      $item = (array) $item;
      $kodeBrng = (string) ($item['kode_brng'] ?? '');
      $map = (array) ($item['map'] ?? []);
      $nama = trim((string) ($map['nama_kfa'] ?? ($item['nama_brng'] ?? $kodeBrng)));
      if ($nama === '' || isset($seen[$kodeBrng . '|' . $nama])) {
        continue;
      }
      $seen[$kodeBrng . '|' . $nama] = true;

      // medicationReference wajib berupa reference (rule 10138) — sintesis bila perlu.
      $medicationRef = MedicationBuilder::ensureReference($ctx, $item, $extra);
      $medication = ['medicationReference' => ['reference' => $medicationRef, 'display' => $nama]];

      $waktu = trim((string) (($item['tgl'] ?? '') . ' ' . ($item['jam'] ?? '')));
      $effective = $auth !== null ? $auth->fhirTime($waktu, true) : (string) (($ctx['time'] ?? [])['now'] ?? '');

      $dosage = ['text' => trim((string) ($item['aturan'] ?? ''))];
      if (trim((string) ($map['kode_route'] ?? '')) !== '') {
        // Kode rute KFA = kode rute ATC (template Kemenkes memakai whocc.no/atc).
        $dosage['route'] = [
          'coding' => [
            [
              'system' => 'http://www.whocc.no/atc',
              'code' => (string) $map['kode_route'],
              'display' => (string) ($map['nama_route'] ?? ''),
            ],
          ],
        ];
      }

      $statement = array_merge([
        'resourceType' => 'MedicationStatement',
        'status' => 'completed',
        'category' => [
          'coding' => [
            [
              'system' => 'http://terminology.hl7.org/CodeSystem/medication-statement-category',
              'code' => 'inpatient',
              'display' => 'Inpatient',
            ],
          ],
        ],
      ], $medication, [
        'subject' => [
          'reference' => (string) (($ctx['ref'] ?? [])['patient'] ?? ''),
          'display' => (string) ($ctx['patient_name'] ?? ''),
        ],
        'context' => ['reference' => (string) (($ctx['ref'] ?? [])['encounter'] ?? '')],
        'effectiveDateTime' => $effective,
        'dateAsserted' => (string) (($ctx['time'] ?? [])['now'] ?? ''),
        'informationSource' => [
          'reference' => (string) (($ctx['ref'] ?? [])['patient'] ?? ''),
          'display' => (string) ($ctx['patient_name'] ?? ''),
        ],
        'dosage' => [$dosage],
      ]);

      $entries[] = SatuSehatBundleBuilder::entry($statement);
    }

    return array_merge($extra, $entries);
  }
}
