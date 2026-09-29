<?php

namespace Plugins\Satu_Sehat\Resources;

use Plugins\Satu_Sehat\Services\SatuSehatBundleBuilder;

/**
 * Builder resource FHIR Observation untuk ERM Rawat Jalan:
 * tanda vital (BAB 4a), antropometri (BAB 4d), tingkat kesadaran, dan hasil laboratorium (BAB 10a).
 */
class ObservationBuilder
{
  /** Peta kolom pemeriksaan_ralan -> [kode LOINC, display, unit, kode UCUM]. */
  const VITAL_SIGNS = [
    'nadi' => ['8867-4', 'Heart rate', 'beats/minute', '/min'],
    'respirasi' => ['9279-1', 'Respiratory rate', 'respirations/minute', '/min'],
    'suhu' => ['8310-5', 'Body temperature', 'Cel', 'Cel'],
    'spo2' => ['59408-5', 'Oxygen saturation in Arterial blood by Pulse oximetry', '%', '%'],
  ];

  const ANTHROPOMETRY = [
    'tinggi' => ['8302-2', 'Body height', 'cm', 'cm'],
    'berat' => ['29463-7', 'Body weight', 'kg', 'kg'],
  ];

  /** Gabungan Observation tanda vital + hasil laboratorium. */
  public static function build(array $ctx): array
  {
    return array_merge(
      self::buildVitalSigns($ctx),
      self::buildLabObservations($ctx)
    );
  }

  /**
   * Tanda vital, antropometri, IMT, dan tingkat kesadaran (BAB 4).
   */
  public static function buildVitalSigns(array $ctx): array
  {
    $emr = (array) ($ctx['emr'] ?? []);
    $pemeriksaan = (array) ($emr['pemeriksaan'] ?? []);
    $entries = [];
    $effective = (string) (($ctx['time'] ?? [])['exam'] ?? (($ctx['time'] ?? [])['now'] ?? ''));

    foreach (self::VITAL_SIGNS as $key => $code) {
      $value = trim((string) ($pemeriksaan[$key] ?? ''));
      if ($value === '' || !is_numeric($value)) {
        continue;
      }
      $entries[] = self::quantityObservation($ctx, [
        'code' => $code,
        'value' => (float) $value,
        'effective' => $effective,
        'display' => $code[1],
      ]);
    }

    // Tekanan darah (sistolik/diastolik) dari kolom tensi "120/80".
    $tensi = trim((string) ($pemeriksaan['tensi'] ?? ''));
    if ($tensi !== '' && strpos($tensi, '/') !== false) {
      [$sistole, $diastole] = array_pad(explode('/', $tensi, 2), 2, '');
      if (is_numeric(trim($sistole))) {
        $entries[] = self::quantityObservation($ctx, [
          'code' => ['8480-6', 'Systolic blood pressure', 'mm[Hg]', 'mm[Hg]'],
          'value' => (float) trim($sistole),
          'effective' => $effective,
          'display' => 'Systolic blood pressure ' . $tensi,
        ]);
      }
      if (is_numeric(trim($diastole))) {
        $entries[] = self::quantityObservation($ctx, [
          'code' => ['8462-4', 'Diastolic blood pressure', 'mm[Hg]', 'mm[Hg]'],
          'value' => (float) trim($diastole),
          'effective' => $effective,
          'display' => 'Diastolic blood pressure ' . $tensi,
        ]);
      }
    }

    // Antropometri + IMT (BAB 4d).
    $tinggi = (float) ($pemeriksaan['tinggi'] ?? 0);
    $berat = (float) ($pemeriksaan['berat'] ?? 0);
    foreach (self::ANTHROPOMETRY as $key => $code) {
      $value = (float) ($pemeriksaan[$key] ?? 0);
      if ($value > 0) {
        $entries[] = self::quantityObservation($ctx, [
          'code' => $code,
          'value' => $value,
          'effective' => $effective,
          'display' => $code[1],
        ]);
      }
    }
    if ($tinggi > 0 && $berat > 0) {
      $imt = round($berat / pow($tinggi / 100, 2), 1);
      $entries[] = self::quantityObservation($ctx, [
        'code' => ['39156-5', 'Body mass index (BMI) [Ratio]', 'kg/m2', 'kg/m2'],
        'value' => $imt,
        'effective' => $effective,
        'display' => 'Body mass index',
      ]);
    }

    // Tingkat kesadaran (GCS / Compos Mentis dst).
    $kesadaran = trim((string) ($pemeriksaan['kesadaran'] ?? ''));
    if ($kesadaran !== '') {
      $gcs = trim((string) ($pemeriksaan['gcs'] ?? ''));
      $value = $kesadaran . ($gcs !== '' ? ' (GCS ' . $gcs . ')' : '');
      $entries[] = SatuSehatBundleBuilder::entry([
        'resourceType' => 'Observation',
        'status' => 'final',
        'category' => [self::category('vital-signs', 'Vital Signs')],
        'code' => [
          'coding' => [
            [
              'system' => 'http://snomed.info/sct',
              'code' => '248240008',
              'display' => 'Glasgow coma scale',
            ],
          ],
          'text' => 'Tingkat Kesadaran',
        ],
        'subject' => ['reference' => (string) (($ctx['ref'] ?? [])['patient'] ?? '')],
        'performer' => [['reference' => (string) (($ctx['ref'] ?? [])['practitioner'] ?? '')]],
        'encounter' => ['reference' => (string) (($ctx['ref'] ?? [])['encounter'] ?? '')],
        'effectiveDateTime' => $effective,
        'issued' => $effective,
        'valueString' => $value,
      ]);
    }

    return $entries;
  }

  /**
   * Hasil laboratorium (detail_periksa_lab) sebagai Observation kategori laboratory (BAB 10a).
   *
   * Identifier value = "{kd_jenis_prw}@{tgl}@{jam}#{id_template}" agar bisa direferensikan
   * DiagnosticReportBuilder per grup pemeriksaan.
   */
  public static function buildLabObservations(array $ctx): array
  {
    $emr = (array) ($ctx['emr'] ?? []);
    $groups = (array) ($emr['lab_items'] ?? []);
    $entries = [];

    foreach ($groups as $group) {
      $group = (array) $group;
      $groupKey = self::labGroupKey($group);
      $effective = (string) (($ctx['auth'] ?? null) !== null
        ? $ctx['auth']->fhirTime(($group['tgl_periksa'] ?? '') . ' ' . ($group['jam'] ?? ''), true)
        : (($ctx['time'] ?? [])['now'] ?? ''));

      foreach ((array) ($group['detail'] ?? []) as $detail) {
        $detail = (array) $detail;
        $nilai = trim((string) ($detail['nilai'] ?? ''));
        if ($nilai === '') {
          continue;
        }
        $map = (array) ($detail['map'] ?? ($group['map'] ?? []));
        $loinc = (string) ($map['code'] ?? '');
        $display = (string) ($detail['nama'] ?? ($map['display'] ?? ''));
        $coding = [];
        if ($loinc !== '') {
          $coding[] = ['system' => 'http://loinc.org', 'code' => $loinc, 'display' => $display];
        }

        $observation = [
          'resourceType' => 'Observation',
          'identifier' => [
            [
              'system' => 'http://sys-ids.kemkes.go.id/erm-ralan/lab-item',
              'value' => $groupKey . '#' . (string) ($detail['id_template'] ?? ''),
            ],
          ],
          'status' => 'final',
          'category' => [self::category('laboratory', 'Laboratory')],
          'code' => [
            'coding' => $coding,
            'text' => $display,
          ],
          'subject' => ['reference' => (string) (($ctx['ref'] ?? [])['patient'] ?? '')],
          'performer' => [['reference' => (string) (($ctx['ref'] ?? [])['practitioner'] ?? '')]],
          'encounter' => ['reference' => (string) (($ctx['ref'] ?? [])['encounter'] ?? '')],
          'effectiveDateTime' => $effective,
          'issued' => $effective,
        ];

        // Specimen & ServiceRequest terkait (template Bundle Kemenkes).
        $specimens = (array) (($ctx['built'] ?? [])['specimens'] ?? []);
        if ($groupKey !== '' && isset($specimens[$groupKey]['fullUrl'])) {
          $observation['specimen'] = ['reference' => (string) $specimens[$groupKey]['fullUrl']];
        }
        $basedOn = self::serviceRequestReference($ctx, (string) ($group['kd_jenis_prw'] ?? ''));
        if ($basedOn !== '') {
          $observation['basedOn'] = [['reference' => $basedOn]];
        }

        if (is_numeric($nilai)) {
          $observation['valueQuantity'] = [
            'value' => (float) $nilai,
            'unit' => (string) ($detail['satuan'] ?? ''),
            'system' => 'http://unitsofmeasure.org',
            'code' => (string) ($detail['satuan'] ?? ''),
          ];
        } else {
          $observation['valueString'] = $nilai;
        }

        if (trim((string) ($detail['nilai_rujukan'] ?? '')) !== '') {
          $observation['referenceRange'] = [
            [
              'text' => (string) $detail['nilai_rujukan'],
            ],
          ];
        }

        $entries[] = SatuSehatBundleBuilder::entry($observation);
      }
    }

    return $entries;
  }

  /** Cari fullUrl ServiceRequest permintaan lab berdasarkan kd_jenis_prw. */
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

  /** Kunci unik grup pemeriksaan lab untuk pencocokan DiagnosticReport.result. */
  public static function labGroupKey(array $group)
  {
    return trim(($group['kd_jenis_prw'] ?? '') . '@' . ($group['tgl_periksa'] ?? '') . '@' . ($group['jam'] ?? ''), '@');
  }

  private static function quantityObservation(array $ctx, array $spec)
  {
    [$code, $display, $unit, $ucum] = $spec['code'];
    return SatuSehatBundleBuilder::entry([
      'resourceType' => 'Observation',
      'status' => 'final',
      'category' => [self::category('vital-signs', 'Vital Signs')],
      'code' => [
        'coding' => [
          [
            'system' => 'http://loinc.org',
            'code' => $code,
            'display' => $display,
          ],
        ],
      ],
      'subject' => ['reference' => (string) (($ctx['ref'] ?? [])['patient'] ?? '')],
      'performer' => [['reference' => (string) (($ctx['ref'] ?? [])['practitioner'] ?? '')]],
      'encounter' => [
        'reference' => (string) (($ctx['ref'] ?? [])['encounter'] ?? ''),
        'display' => (string) ($spec['display'] ?? $display),
      ],
      'effectiveDateTime' => (string) $spec['effective'],
      'issued' => (string) $spec['effective'],
      'valueQuantity' => [
        'value' => $spec['value'],
        'unit' => $unit,
        'system' => 'http://unitsofmeasure.org',
        'code' => $ucum,
      ],
    ]);
  }

  private static function category($code, $display)
  {
    return [
      'coding' => [
        [
          'system' => 'http://terminology.hl7.org/CodeSystem/observation-category',
          'code' => $code,
          'display' => $display,
        ],
      ],
    ];
  }
}
