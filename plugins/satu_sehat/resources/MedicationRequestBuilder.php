<?php

namespace Plugins\Satu_Sehat\Resources;

use Plugins\Satu_Sehat\Services\SatuSehatBundleBuilder;

/**
 * Builder resource FHIR MedicationRequest untuk ERM Rawat Jalan (BAB 15 peresepan obat).
 *
 * Referensi Medication diambil dari ctx['built']['medications'] (index sama dengan
 * emr['resep_items'], dicocokkan ulang lewat kode_brng).
 */
class MedicationRequestBuilder
{
  /**
   * Kandidat kode unit UCUM yang dikenal untuk Quantity (kunci huruf kecil).
   * Nilai = ejaan UCUM resmi. Di luar daftar ini (mis. 'SYRUP', 'KAPSUL')
   * TIDAK boleh dikirim sebagai code — validator hanya menerima kode UCUM
   * valid (rule 10349) dan Quantity tanpa system/code ditolak (rule 10050).
   */
  private static $ucum = [
    'g' => 'g', 'mg' => 'mg', 'ug' => 'ug', 'mcg' => 'ug', 'kg' => 'kg',
    'l' => 'L', 'ml' => 'mL', 'dl' => 'dL', 'cl' => 'cL', 'ul' => 'uL',
    'mol' => 'mol', 'mmol' => 'mmol', 'umol' => 'umol', 'meq' => 'meq',
    '[iu]' => '[IU]', 'u' => 'U', '%' => '%',
    'cm' => 'cm', 'mm' => 'mm', 'm' => 'm',
    'h' => 'h', 'hr' => 'h', 'd' => 'd', 'day' => 'd', 'wk' => 'wk', 'mo' => 'mo',
  ];

  /**
   * Kode kanonis UCUM untuk sebuah kode satuan (ejaan umum seperti 'ml'
   * dikanonkan menjadi 'mL'), atau null bila bukan kode UCUM yang dikenal.
   */
  public static function ucumCode($code)
  {
    $code = trim((string) $code);
    if ($code === '') {
      return null;
    }
    return self::$ucum[strtolower($code)] ?? null;
  }

  /**
   * Susun Quantity dengan coding unit yang pasti valid, atau null bila satuan
   * tidak dapat dikodekan (Quantity wajib punya system+code yang valid).
   *
   * - kode digit  -> SNOMED (mis. 385057009 dari mapping KFA)
   * - kode UCUM   -> http://unitsofmeasure.org
   * - selain itu  -> null, pemanggil menghilangkan elemen Quantity
   */
  public static function unitQuantity($value, $unitText, $rawCode = null)
  {
    $code = trim((string) ($rawCode !== null && (string) $rawCode !== '' ? (string) $rawCode : (string) $unitText));
    $unit = trim((string) $unitText);
    if ($code === '') {
      return null;
    }
    if (preg_match('/^\d+$/', $code)) {
      return [
        'value' => (float) $value,
        'unit' => $unit !== '' ? $unit : $code,
        'system' => 'http://snomed.info/sct',
        'code' => $code,
      ];
    }
    $canon = self::ucumCode($code);
    if ($canon !== null) {
      return [
        'value' => (float) $value,
        'unit' => $unit !== '' ? $unit : $canon,
        'system' => 'http://unitsofmeasure.org',
        'code' => $canon,
      ];
    }
    return null;
  }

  /**
   * Pastikan ada MedicationRequest acuan untuk sebuah obat (rule 10393:
   * authorizingPrescription/request wajib). Bila belum ada, bangun entri
   * MedicationRequest minimal yang ikut dikumpulkan ke bundle.
   *
   * @return string FullUrl MedicationRequest yang dipakai.
   */
  public static function ensureRequest(array &$ctx, array $item, $medicationRef, $authoredOn, array &$extra, array $map = null)
  {
    $map = $map !== null ? $map : (array) ($item['map'] ?? []);
    $kodeBrng = (string) ($item['kode_brng'] ?? '');
    $nama = trim((string) ($map['nama_kfa'] ?? ($item['nama_brng'] ?? $kodeBrng)));
    $ref = MedicationDispenseBuilder::prescriptionReference($ctx, $kodeBrng);
    if ($ref !== '') {
      return $ref;
    }
    $org = (string) ($ctx['organization_id'] ?? '');
    $inpatient = (string) ($ctx['erm_type'] ?? 'ralan') === 'ranap';
    $request = [
      'resourceType' => 'MedicationRequest',
      'identifier' => [
        [
          'system' => 'http://sys-ids.kemkes.go.id/prescription/' . $org,
          'use' => 'official',
          'value' => $kodeBrng . '@' . ($item['tgl_perawatan'] ?? ($item['tgl'] ?? '')) . '@' . ($item['jam'] ?? ''),
        ],
        [
          'system' => 'http://sys-ids.kemkes.go.id/prescription-item/' . $org,
          'use' => 'official',
          'value' => $kodeBrng,
        ],
      ],
      'status' => 'completed',
      'intent' => 'order',
      'category' => [self::category((string) ($ctx['erm_type'] ?? 'ralan'))],
      'medicationReference' => ['reference' => (string) $medicationRef, 'display' => $nama],
      'subject' => ['reference' => (string) (($ctx['ref'] ?? [])['patient'] ?? '')],
      'encounter' => ['reference' => (string) (($ctx['ref'] ?? [])['encounter'] ?? '')],
      'authoredOn' => $authoredOn,
      'requester' => ['reference' => (string) (($ctx['ref'] ?? [])['practitioner'] ?? '')],
    ];
    $entry = SatuSehatBundleBuilder::entry($request);
    $ctx['built']['medrequests'][] = $entry;
    $extra[] = $entry;
    return (string) ($entry['fullUrl'] ?? '');
  }

  public static function build(array $ctx): array
  {
    $emr = (array) ($ctx['emr'] ?? []);
    $entries = [];
    $auth = $ctx['auth'] ?? null;

    foreach ((array) ($emr['resep_items'] ?? []) as $index => $item) {
      $item = (array) $item;
      $map = (array) ($item['map'] ?? []);
      $nama = trim((string) ($map['nama_kfa'] ?? ($item['nama_brng'] ?? '')));
      $noResep = (string) ($item['no_resep'] ?? '');
      $kodeBrng = (string) ($item['kode_brng'] ?? '');

      $medicationRef = self::medicationReference($ctx, $index, $kodeBrng);
      if ($medicationRef === '') {
        continue;
      }

      $authoredOn = $auth !== null
        ? $auth->fhirTime((($item['tgl_peresepan'] ?? '') . ' ' . ($item['jam_peresepan'] ?? '')), true)
        : (string) (($ctx['time'] ?? [])['now'] ?? '');
      $diserahkan = trim((string) (($item['tgl_penyerahan'] ?? '') . ' ' . ($item['jam_penyerahan'] ?? '')));

      $dosage = [
        'text' => trim((string) ($item['aturan_pakai'] ?? '')),
      ];
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
      $dose = self::unitQuantity((float) ($item['jml'] ?? 0), (string) ($map['satuan_den'] ?? ''));
      if ($dose !== null) {
        $dosage['doseAndRate'] = [['doseQuantity' => $dose]];
      }

      $request = [
        'resourceType' => 'MedicationRequest',
        'identifier' => [
          [
            'system' => 'http://sys-ids.kemkes.go.id/prescription/' . (string) ($ctx['organization_id'] ?? ''),
            'use' => 'official',
            'value' => $noResep,
          ],
          [
            'system' => 'http://sys-ids.kemkes.go.id/prescription-item/' . (string) ($ctx['organization_id'] ?? ''),
            'use' => 'official',
            'value' => $kodeBrng,
          ],
        ],
        'status' => ($diserahkan !== '' && strpos($diserahkan, '0000-00-00') !== 0) ? 'completed' : 'active',
        'intent' => 'order',
        'category' => [self::category((string) ($ctx['erm_type'] ?? 'ralan'))],
        'medicationReference' => [
          'reference' => $medicationRef,
          'display' => $nama,
        ],
        'subject' => ['reference' => (string) (($ctx['ref'] ?? [])['patient'] ?? '')],
        'encounter' => ['reference' => (string) (($ctx['ref'] ?? [])['encounter'] ?? '')],
        'authoredOn' => $authoredOn,
        'requester' => ['reference' => (string) (($ctx['ref'] ?? [])['practitioner'] ?? '')],
        'dosageInstruction' => [$dosage],
      ];

      // Rawat inap: dispenseRequest (template Bundle Rawat Inap Kemenkes).
      if ((string) ($ctx['erm_type'] ?? 'ralan') !== 'ralan') {
        $request['dispenseRequest'] = [
          'numberOfRepeatsAllowed' => 0,
          'performer' => ['reference' => (string) (($ctx['ref'] ?? [])['organization'] ?? '')],
        ];
        $dispenseQty = self::unitQuantity((float) ($item['jml'] ?? 0), (string) ($map['satuan_den'] ?? ''));
        if ($dispenseQty !== null) {
          $request['dispenseRequest']['quantity'] = $dispenseQty;
        }
        if ($diserahkan !== '' && strpos($diserahkan, '0000-00-00') !== 0 && $auth !== null) {
          $request['dispenseRequest']['validityPeriod'] = [
            'start' => $authoredOn,
            'end' => $auth->fhirTime(date('Y-m-d H:i:s', strtotime($diserahkan) + 30 * 86400), true),
          ];
        }
      }

      // IGD: prioritas stat + reasonReference diagnosis awal (template IGD).
      if ((string) ($ctx['erm_type'] ?? 'ralan') === 'igd') {
        $request['priority'] = 'stat';
        foreach ((array) (($ctx['built'] ?? [])['conditions_awal'] ?? []) as $awalEntry) {
          if (!empty($awalEntry['fullUrl'])) {
            $request['reasonReference'] = [['reference' => (string) $awalEntry['fullUrl']]];
            break;
          }
        }
      }

      $entries[] = SatuSehatBundleBuilder::entry($request);
    }

    return $entries;
  }

  /** Kategori MedicationRequest: outpatient (ralan/IGD) / inpatient (ranap). */
  private static function category($ermType)
  {
    $inpatient = $ermType === 'ranap';
    return [
      'coding' => [
        [
          'system' => 'http://terminology.hl7.org/CodeSystem/medicationrequest-category',
          'code' => $inpatient ? 'inpatient' : 'outpatient',
          'display' => $inpatient ? 'Inpatient' : 'Outpatient',
        ],
      ],
    ];
  }

  /**
   * Cari fullUrl Medication untuk sebuah item resep.
   */
  public static function medicationReference(array $ctx, $index, $kodeBrng)
  {
    $medications = (array) (($ctx['built'] ?? [])['medications'] ?? []);

    if (isset($medications[$index]) && (string) ($medications[$index]['resource']['identifier'][0]['value'] ?? '') === $kodeBrng) {
      return (string) $medications[$index]['fullUrl'];
    }
    foreach ($medications as $entry) {
      if ((string) ($entry['resource']['identifier'][0]['value'] ?? '') === $kodeBrng) {
        return (string) ($entry['fullUrl'] ?? '');
      }
    }
    return '';
  }
}
