<?php

namespace Plugins\Satu_Sehat\Resources;

use Plugins\Satu_Sehat\Services\SatuSehatBundleBuilder;

/**
 * Builder resource FHIR Observation untuk ERM Rawat Inap melengkapi ObservationBuilder:
 *
 * - Tanda vital dengan nilai interpretasi (H/L/N) + bodySite tekanan darah
 * - Luas permukaan tubuh (BSA, LOINC 8277-6)
 * - Pemeriksaan fisik head to toe (Observation exam dengan LOINC narrative)
 * - Status psikologis (LOINC 8693-4) & skor ADL (SNOMED 715823002)
 * - Hasil pemeriksaan radiologi (Observation kategori imaging)
 * - Kriteria pasien yang dilakukan rencana pemulangan (clinical-term OC000055)
 */
class ObservationRanapBuilder
{
  /** Peta label pemeriksaan head to toe -> [LOINC, display]. */
  const HEAD_TO_TOE = [
    'kepala' => ['10199-8', 'Physical findings of Head Narrative'],
    'mata' => ['10197-2', 'Physical findings of Eye Narrative'],
    'telinga' => ['10195-6', 'Physical findings of Ear Narrative'],
    'hidung' => ['10203-8', 'Physical findings of Nose Narrative'],
    'rambut' => ['32436-8', 'Physical findings of Hair'],
    'bibir' => ['32446-7', 'Physical findings of Lip'],
    'gigi' => ['85910-8', 'Physical findings of Teeth and gum Narrative'],
    'lidah' => ['32483-0', 'Physical findings of Tongue'],
    'mulut' => ['10201-2', 'Physical findings of Mouth and Throat and Teeth Narrative'],
    'tenggorokan' => ['56867-5', 'Physical findings of Throat Narrative'],
    'leher' => ['11411-6', 'Physical findings of Neck Narrative'],
    'dada' => ['11391-0', 'Physical findings of Chest Narrative'],
    'payudara' => ['10193-1', 'Physical findings of Breasts Narrative'],
    'punggung' => ['10192-3', 'Physical findings of Back Narrative'],
    'abdomen' => ['10191-5', 'Physical findings of Abdomen Narrative'],
    'perut' => ['10191-5', 'Physical findings of Abdomen Narrative'],
    'genitalia' => ['11400-9', 'Physical findings of Genitalia Narrative'],
    'bokong' => ['11388-6', 'Physical findings of Buttocks Narrative'],
    'lengan atas' => ['11386-0', 'Physical findings of Upper Arm Narrative'],
    'lengan bawah' => ['11398-5', 'Physical findings of Forearm Narrative'],
    'tangan' => ['11404-1', 'Physical findings of Hand Narrative'],
    'kuku' => ['32456-6', 'Physical findings of Nail'],
    'pergelangan tangan' => ['11415-7', 'Physical findings of Wrist Narrative'],
    'paha' => ['11414-0', 'Physical findings of Thigh Narrative'],
    'betis' => ['11389-4', 'Physical findings of Calf Narrative'],
    'tungkai bawah' => ['11389-4', 'Physical findings of Calf Narrative'],
    'kaki' => ['11397-7', 'Physical findings of Foot Narrative'],
    'pergelangan kaki' => ['11385-2', 'Physical findings of Ankle Narrative'],
    'jari kaki' => ['11397-7', 'Physical findings of Foot Narrative'],
    'jari tangan' => ['11404-1', 'Physical findings of Hand Narrative'],
  ];

  /** Ambang interpretasi tanda vital -> [kode, display, teks]. */
  const INTERPRETATION = [
    'sistole' => [140, 90],
    'diastole' => [90, 60],
    'suhu' => [37, 36],
    'nadi' => [100, 60],
    'respirasi' => [20, 12],
    'spo2' => [100, 95],
  ];

  /**
   * Gabungan Observation khas rawat inap (tanpa hasil lab; lab memakai
   * ObservationBuilder::buildLabObservations()).
   */
  public static function build(array $ctx): array
  {
    return array_merge(
      self::buildVitalSigns($ctx),
      self::buildPhysicalExam($ctx),
      self::buildFunctional($ctx),
      self::buildRadiologyResults($ctx),
      self::buildKriteriaPulang($ctx)
    );
  }

  /**
   * Tanda vital rawat inap dengan interpretasi H/L/N + BSA.
   */
  public static function buildVitalSigns(array $ctx): array
  {
    $emr = (array) ($ctx['emr'] ?? []);
    $p = (array) ($emr['pemeriksaan'] ?? []);
    $time = (array) ($ctx['time'] ?? []);
    $effective = (string) ($time['exam'] ?? ($time['now'] ?? ''));
    $entries = [];

    $tensi = trim((string) ($p['tensi'] ?? ''));
    if ($tensi !== '' && strpos($tensi, '/') !== false) {
      [$sistole, $diastole] = array_pad(explode('/', $tensi, 2), 2, '');
      if (is_numeric(trim($sistole))) {
        $entries[] = self::quantity($ctx, [
          'code' => ['8480-6', 'Systolic blood pressure', 'mm[Hg]', 'mm[Hg]'],
          'value' => (float) trim($sistole),
          'key' => 'sistole',
          'display' => 'Pemeriksaan Fisik Sistolik ' . ($ctx['patient_name'] ?? ''),
          'bodySite' => true,
        ], $effective);
      }
      if (is_numeric(trim($diastole))) {
        $entries[] = self::quantity($ctx, [
          'code' => ['8462-4', 'Diastolic blood pressure', 'mm[Hg]', 'mm[Hg]'],
          'value' => (float) trim($diastole),
          'key' => 'diastole',
          'display' => 'Pemeriksaan Fisik Diastolik ' . ($ctx['patient_name'] ?? ''),
          'bodySite' => true,
        ], $effective);
      }
    }

    foreach ([
      'suhu' => ['8310-5', 'Body temperature', 'C', 'Cel'],
      'nadi' => ['8867-4', 'Heart rate', 'beats/minute', '/min'],
      'respirasi' => ['9279-1', 'Respiratory rate', 'breaths/minute', '/min'],
      'spo2' => ['59408-5', 'Oxygen saturation in Arterial blood by Pulse oximetry', '%', '%'],
    ] as $key => $code) {
      $value = trim((string) ($p[$key] ?? ''));
      if ($value === '' || !is_numeric(str_replace(',', '.', $value))) {
        continue;
      }
      $entries[] = self::quantity($ctx, [
        'code' => $code,
        'value' => (float) str_replace(',', '.', $value),
        'key' => $key,
        'display' => $code[1],
      ], $effective);
    }

    // Antropometri + BSA (Mosteller).
    $tinggi = (float) ($p['tinggi'] ?? 0);
    $berat = (float) ($p['berat'] ?? 0);
    foreach ([
      'tinggi' => ['8302-2', 'Body height', 'cm', 'cm'],
      'berat' => ['29463-7', 'Body weight', 'kg', 'kg'],
    ] as $key => $code) {
      $value = (float) ($p[$key] ?? 0);
      if ($value > 0) {
        $entries[] = self::quantity($ctx, [
          'code' => $code,
          'value' => $value,
          'key' => '',
          'display' => $code[1],
        ], $effective);
      }
    }
    if ($tinggi > 0 && $berat > 0) {
      $bsa = round(sqrt($tinggi * $berat / 3600), 2);
      $entries[] = self::quantity($ctx, [
        'code' => ['8277-6', 'Body surface area', 'm2', 'm2'],
        'value' => $bsa,
        'key' => '',
        'display' => 'Body surface area',
      ], $effective);
    }

    return $entries;
  }

  /**
   * Pemeriksaan fisik head to toe: teks pemeriksaan dipecah per label
   * (mis. "Kepala: ...; Mata: ...") menjadi Observation exam per bagian tubuh;
   * bila tidak ada label dikenali dipakai satu Observation narrative (10187-3).
   */
  public static function buildPhysicalExam(array $ctx): array
  {
    $emr = (array) ($ctx['emr'] ?? []);
    $p = (array) ($emr['pemeriksaan'] ?? []);
    $text = trim((string) ($p['pemeriksaan'] ?? ''));
    if ($text === '') {
      return [];
    }

    $time = (array) ($ctx['time'] ?? []);
    $effective = (string) ($time['exam'] ?? ($time['now'] ?? ''));
    $segments = preg_split('/[\r\n;]+/', $text);
    $entries = [];
    $sisa = [];

    foreach ((array) $segments as $segment) {
      $segment = trim((string) $segment);
      if ($segment === '') {
        continue;
      }
      $matched = false;
      foreach (self::HEAD_TO_TOE as $label => $code) {
        if (preg_match('/^' . preg_quote($label, '/') . '\s*[:\-]\s*(.+)$/iu', $segment, $m)) {
          $entries[] = self::examObservation($ctx, $code, trim($m[1]), $effective);
          $matched = true;
          break;
        }
      }
      if (!$matched) {
        $sisa[] = $segment;
      }
    }

    if (!empty($sisa)) {
      $entries[] = self::examObservation(
        $ctx,
        ['10187-3', 'Review of systems Narrative - Reported'],
        implode('; ', $sisa),
        $effective
      );
    }

    return $entries;
  }

  /**
   * Pemeriksaan fungsional: status psikologis (8693-4) & skor ADL.
   */
  public static function buildFunctional(array $ctx): array
  {
    $emr = (array) ($ctx['emr'] ?? []);
    $p = (array) ($emr['pemeriksaan'] ?? []);
    $time = (array) ($ctx['time'] ?? []);
    $effective = (string) ($time['exam'] ?? ($time['now'] ?? ''));
    $entries = [];

    $mental = trim((string) ($p['kesadaran'] ?? ''));
    if ($mental !== '') {
      $mentalObs = [
        'resourceType' => 'Observation',
        'status' => 'final',
        'category' => [self::category('survey', 'Survey')],
        'code' => [
          'coding' => [
            ['system' => 'http://loinc.org', 'code' => '8693-4', 'display' => 'Mental Status'],
          ],
        ],
        'subject' => ['reference' => (string) (($ctx['ref'] ?? [])['patient'] ?? '')],
        'encounter' => [
          'reference' => (string) (($ctx['ref'] ?? [])['encounter'] ?? ''),
          'display' => 'Pemeriksaan Status Psikologis ' . (string) ($ctx['patient_name'] ?? ''),
        ],
        'effectiveDateTime' => $effective,
        'issued' => $effective,
        'performer' => [['reference' => (string) (($ctx['ref'] ?? [])['practitioner'] ?? '')]],
      ];
      if (preg_match('/compos\s*mentis|alert|sadar penuh/i', $mental)) {
        $mentalObs['valueCodeableConcept'] = [
          'coding' => [
            ['system' => 'http://snomed.info/sct', 'code' => '248234008', 'display' => 'Mentally alert'],
          ],
          'text' => $mental,
        ];
      } else {
        $mentalObs['valueString'] = $mental;
      }
      $entries[] = SatuSehatBundleBuilder::entry($mentalObs);
    }

    $skorAdl = trim((string) ($emr['skor_adl'] ?? ''));
    if ($skorAdl !== '' && is_numeric($skorAdl)) {
      $entries[] = SatuSehatBundleBuilder::entry([
        'resourceType' => 'Observation',
        'status' => 'final',
        'category' => [self::category('survey', 'Survey')],
        'code' => [
          'coding' => [
            [
              'system' => 'http://snomed.info/sct',
              'code' => '715823002',
              'display' => 'World Health Organization Disability Assessment Schedule 2.0 score',
            ],
          ],
          'text' => 'Skor ADL',
        ],
        'subject' => ['reference' => (string) (($ctx['ref'] ?? [])['patient'] ?? '')],
        'encounter' => [
          'reference' => (string) (($ctx['ref'] ?? [])['encounter'] ?? ''),
          'display' => 'Pemeriksaan Skor ADL ' . (string) ($ctx['patient_name'] ?? ''),
        ],
        'effectiveDateTime' => $effective,
        'issued' => $effective,
        'performer' => [['reference' => (string) (($ctx['ref'] ?? [])['practitioner'] ?? '')]],
        'valueQuantity' => [
          'value' => (float) $skorAdl,
          'unit' => '{score}',
          'system' => 'http://unitsofmeasure.org',
          'code' => '{score}',
        ],
      ]);
    }

    return $entries;
  }

  /**
   * Hasil pemeriksaan radiologi -> Observation kategori imaging (valueString hasil).
   */
  public static function buildRadiologyResults(array $ctx): array
  {
    $emr = (array) ($ctx['emr'] ?? []);
    $entries = [];
    $auth = $ctx['auth'] ?? null;

    foreach ((array) ($emr['rad_items'] ?? []) as $item) {
      $item = (array) $item;
      $hasil = trim((string) ($item['hasil'] ?? ''));
      if ($hasil === '') {
        continue;
      }
      $map = (array) ($item['map'] ?? []);
      $nama = trim((string) ($item['nm_perawatan'] ?? ($map['display'] ?? ($item['kd_jenis_prw'] ?? ''))));
      $coding = [];
      if (trim((string) ($map['code'] ?? '')) !== '') {
        $coding[] = [
          'system' => (string) ($map['system'] ?? 'http://loinc.org'),
          'code' => (string) $map['code'],
          'display' => $nama,
        ];
      }
      $effective = $auth !== null
        ? $auth->fhirTime(trim((string) (($item['tgl_periksa'] ?? '') . ' ' . ($item['jam'] ?? ''))), true)
        : (string) (($ctx['time'] ?? [])['now'] ?? '');

      $entries[] = SatuSehatBundleBuilder::entry([
        'resourceType' => 'Observation',
        'identifier' => [
          [
            'system' => 'http://sys-ids.kemkes.go.id/erm-ranap/rad-item/' . (string) ($ctx['organization_id'] ?? ''),
            'value' => (string) ($item['kd_jenis_prw'] ?? '') . '@' . ($item['tgl_periksa'] ?? '') . '@' . ($item['jam'] ?? ''),
          ],
        ],
        'status' => 'final',
        'category' => [self::category('imaging', 'Imaging')],
        'code' => ['coding' => $coding, 'text' => $nama],
        'subject' => ['reference' => (string) (($ctx['ref'] ?? [])['patient'] ?? '')],
        'encounter' => ['reference' => (string) (($ctx['ref'] ?? [])['encounter'] ?? '')],
        'effectiveDateTime' => $effective,
        'issued' => $effective,
        'performer' => [['reference' => (string) (($ctx['ref'] ?? [])['practitioner'] ?? '')]],
        'valueString' => $hasil,
      ]);

      // basedOn: ServiceRequest permintaan radiologi dengan kd_jenis_prw sama.
      $kd = (string) ($item['kd_jenis_prw'] ?? '');
      foreach ((array) (($ctx['built'] ?? [])['servicerequests_rad'] ?? []) as $requestEntry) {
        $value = (string) ($requestEntry['resource']['identifier'][0]['value'] ?? '');
        if ($kd !== '' && (strpos($value, '-' . $kd) !== false || $value === $kd)) {
          $entries[count($entries) - 1]['resource']['basedOn'] = [['reference' => (string) ($requestEntry['fullUrl'] ?? '')]];
          break;
        }
      }
    }

    return $entries;
  }

  /**
   * Kriteria pasien yang dilakukan rencana pemulangan (clinical-term OC000055).
   */
  public static function buildKriteriaPulang(array $ctx): array
  {
    $emr = (array) ($ctx['emr'] ?? []);
    $inap = (array) ($emr['kamar_inap'] ?? []);
    $sttsPulang = trim((string) ($inap['stts_pulang'] ?? ''));
    $kriteria = trim((string) ($emr['kriteria_pemulangan'] ?? ''));
    if ($sttsPulang === '' && $kriteria === '') {
      return [];
    }

    $time = (array) ($ctx['time'] ?? []);
    $effective = (string) ($time['discharge'] ?? ($time['exam'] ?? ($time['now'] ?? '')));

    return [SatuSehatBundleBuilder::entry([
      'resourceType' => 'Observation',
      'status' => 'final',
      'category' => [self::category('survey', 'Survey')],
      'code' => [
        'coding' => [
          [
            'system' => 'http://terminology.kemkes.go.id/CodeSystem/clinical-term',
            'code' => 'OC000055',
            'display' => 'Kriteria Pasien yang dilakukan Rencana Pemulangan',
          ],
        ],
      ],
      'subject' => ['reference' => (string) (($ctx['ref'] ?? [])['patient'] ?? '')],
      'encounter' => [
        'reference' => (string) (($ctx['ref'] ?? [])['encounter'] ?? ''),
        'display' => 'Pemeriksaan Kriteria untuk Rencana Pemulangan ' . (string) ($ctx['patient_name'] ?? ''),
      ],
      'effectiveDateTime' => $effective,
      'issued' => $effective,
      'performer' => [['reference' => (string) (($ctx['ref'] ?? [])['practitioner'] ?? '')]],
      'valueCodeableConcept' => ['text' => $kriteria !== '' ? $kriteria : $sttsPulang],
    ])];
  }

  private static function quantity(array $ctx, array $spec, $effective)
  {
    [$code, $display, $unit, $ucum] = $spec['code'];
    $observation = [
      'resourceType' => 'Observation',
      'status' => 'final',
      'category' => [self::category('vital-signs', 'Vital Signs')],
      'code' => [
        'coding' => [
          ['system' => 'http://loinc.org', 'code' => $code, 'display' => $display],
        ],
      ],
      'subject' => ['reference' => (string) (($ctx['ref'] ?? [])['patient'] ?? '')],
      'encounter' => [
        'reference' => (string) (($ctx['ref'] ?? [])['encounter'] ?? ''),
        'display' => (string) ($spec['display'] ?? $display),
      ],
      'effectiveDateTime' => $effective,
      'issued' => $effective,
      'performer' => [['reference' => (string) (($ctx['ref'] ?? [])['practitioner'] ?? '')]],
      'valueQuantity' => [
        'value' => $spec['value'],
        'unit' => $unit,
        'system' => 'http://unitsofmeasure.org',
        'code' => $ucum,
      ],
    ];

    $interpretation = self::interpretation((string) ($spec['key'] ?? ''), (float) $spec['value']);
    if ($interpretation !== null) {
      $observation['interpretation'] = [$interpretation];
    }
    if (!empty($spec['bodySite'])) {
      $observation['bodySite'] = [
        'coding' => [
          ['system' => 'http://snomed.info/sct', 'code' => '368209003', 'display' => 'Right arm'],
        ],
      ];
    }

    return SatuSehatBundleBuilder::entry($observation);
  }

  private static function interpretation($key, $value)
  {
    if ($key === '' || !isset(self::INTERPRETATION[$key])) {
      return null;
    }
    [$high, $low] = self::INTERPRETATION[$key];
    if ($key === 'spo2') {
      if ($value < $low) {
        return self::codingInterpretation('L', 'Low', 'Di bawah nilai referensi');
      }
      return null;
    }
    if ($value > $high) {
      return self::codingInterpretation('H', 'High', 'Di atas nilai referensi');
    }
    if ($value < $low) {
      return self::codingInterpretation('L', 'Low', 'Di bawah nilai referensi');
    }
    return self::codingInterpretation('N', 'Normal', 'Dalam nilai referensi');
  }

  private static function codingInterpretation($code, $display, $text)
  {
    return [
      'coding' => [
        [
          'system' => 'http://terminology.hl7.org/CodeSystem/v3-ObservationInterpretation',
          'code' => $code,
          'display' => $display,
        ],
      ],
      'text' => $text,
    ];
  }

  private static function examObservation(array $ctx, array $code, $text, $effective)
  {
    return SatuSehatBundleBuilder::entry([
      'resourceType' => 'Observation',
      'status' => 'final',
      'category' => [self::category('exam', 'Exam')],
      'code' => [
        'coding' => [
          ['system' => 'http://loinc.org', 'code' => $code[0], 'display' => $code[1]],
        ],
      ],
      'subject' => [
        'reference' => (string) (($ctx['ref'] ?? [])['patient'] ?? ''),
        'display' => (string) ($ctx['patient_name'] ?? ''),
      ],
      'encounter' => ['reference' => (string) (($ctx['ref'] ?? [])['encounter'] ?? '')],
      'effectiveDateTime' => $effective,
      'issued' => $effective,
      'performer' => [['reference' => (string) (($ctx['ref'] ?? [])['practitioner'] ?? '')]],
      'valueString' => $text,
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
