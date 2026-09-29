<?php

namespace Plugins\Satu_Sehat\Resources;

use Plugins\Satu_Sehat\Services\SatuSehatBundleBuilder;

/**
 * Builder resource FHIR Observation khas ERM IGD mengikuti template Bundle
 * Transaction IGD Kemenkes:
 *
 * - Tingkat kesadaran / respons (LOINC 67775-7) dari mlite_triase_igd.kesadaran
 * - Risiko jatuh (LOINC 59461-4 Fall risk level [Morse Fall Scale]) dari
 *   penilaian_awal_keperawatan_igd.hasil
 * - Status kehamilan (LOINC 82810-3) dari penilaian_awal_keperawatan_igd
 *   (juga dipakai sebagai supportingInfo pra radiologi pada template)
 * - Kriteria rencana pemulangan (clinical-term OC000055)
 */
class ObservationIgdBuilder
{
  /** Map kesadaran triase -> SNOMED (hanya yang baku; sisanya teks). */
  const KESADARAN = [
    'compos mentis' => ['248234008', 'Mentally alert'],
  ];

  /** Gabungan Observation khas IGD. */
  public static function build(array $ctx): array
  {
    return array_merge(
      self::buildKesadaran($ctx),
      self::buildRisikoJatuh($ctx),
      array_values(array_filter([self::buildStatusKehamilan($ctx)])),
      self::buildKriteriaPulang($ctx)
    );
  }

  /** Tingkat kesadaran (Level of responsiveness, 67775-7). */
  public static function buildKesadaran(array $ctx): array
  {
    $emr = (array) ($ctx['emr'] ?? []);
    $triase = (array) ($emr['triase'] ?? []);
    $kesadaran = trim((string) ($triase['kesadaran'] ?? ''));
    if ($kesadaran === '') {
      return [];
    }

    $value = ['text' => $kesadaran];
    $map = self::KESADARAN[trim(strtolower($kesadaran))] ?? null;
    if ($map !== null) {
      $value['coding'] = [
        ['system' => 'http://snomed.info/sct', 'code' => $map[0], 'display' => $map[1]],
      ];
    }

    return [SatuSehatBundleBuilder::entry(self::observation($ctx, [
      'category' => ['exam', 'Exam'],
      'code' => ['67775-7', 'Level of responsiveness'],
      'display' => 'Pemeriksaan Tingkat Kesadaran ' . (string) ($ctx['patient_name'] ?? ''),
    ]) + ['valueCodeableConcept' => $value])];
  }

  /**
   * Risiko jatuh (59461-4). Bila tersedia skor Morse numerik dipakai
   * valueQuantity; selain itu valueCodeableConcept dari hasil penilaian.
   */
  public static function buildRisikoJatuh(array $ctx): array
  {
    $emr = (array) ($ctx['emr'] ?? []);
    $penilaian = (array) ($emr['penilaian_igd'] ?? []);
    $hasil = trim((string) ($penilaian['hasil'] ?? ''));
    $skor = trim((string) ($penilaian['morse_total'] ?? ''));
    if ($hasil === '' && $skor === '') {
      return [];
    }

    $observation = self::observation($ctx, [
      'category' => ['exam', 'Exam'],
      'code' => ['59461-4', 'Fall risk level [Morse Fall Scale]'],
      'display' => 'Pemeriksaan Risiko Jatuh ' . (string) ($ctx['patient_name'] ?? ''),
    ]);

    if ($skor !== '' && is_numeric($skor)) {
      $observation['valueQuantity'] = [
        'value' => (float) $skor,
        'unit' => '{score}',
        'system' => 'http://unitsofmeasure.org',
        'code' => '{score}',
      ];
    } elseif ($hasil !== '') {
      $observation['valueCodeableConcept'] = ['text' => self::risikoLabel($hasil)];
    }

    if ($hasil !== '') {
      $observation['interpretation'] = [
        ['text' => self::risikoLabel($hasil)],
      ];
    }

    return [SatuSehatBundleBuilder::entry($observation)];
  }

  /**
   * Status kehamilan (82810-3). Dipakai pula sebagai supportingInfo pra
   * radiologi pada ServiceRequest (template Observation_PraRad).
   */
  public static function buildStatusKehamilan(array $ctx)
  {
    $emr = (array) ($ctx['emr'] ?? []);
    $penilaian = (array) ($emr['penilaian_igd'] ?? []);
    $status = trim((string) ($penilaian['status_kehamilan'] ?? ''));
    if ($status === '') {
      return null;
    }

    $hamil = stripos($status, 'hamil') !== false && stripos($status, 'tidak') === false;
    $value = ['text' => $status];
    if ($hamil) {
      $value['coding'] = [
        ['system' => 'http://snomed.info/sct', 'code' => '77386006', 'display' => 'Pregnancy'],
      ];
    }

    return SatuSehatBundleBuilder::entry(self::observation($ctx, [
      'category' => ['survey', 'Survey'],
      'code' => ['82810-3', 'Pregnancy status'],
      'display' => 'Pemeriksaan Status Kehamilan ' . (string) ($ctx['patient_name'] ?? ''),
    ]) + ['valueCodeableConcept' => $value]);
  }

  /** Kriteria pasien yang dilakukan rencana pemulangan (clinical-term OC000055). */
  public static function buildKriteriaPulang(array $ctx): array
  {
    $emr = (array) ($ctx['emr'] ?? []);
    $reg = (array) ($emr['reg_periksa'] ?? []);
    $kriteria = trim((string) ($emr['kriteria_pemulangan'] ?? ''));
    $disposisi = trim((string) ($reg['stts_pulang'] ?? ''));
    if ($kriteria === '' && $disposisi === '') {
      return [];
    }

    $observation = self::observation($ctx, [
      'category' => ['survey', 'Survey'],
      'code' => ['OC000055', 'Kriteria Pasien yang dilakukan Rencana Pemulangan', 'http://terminology.kemkes.go.id/CodeSystem/clinical-term'],
      'display' => 'Pemeriksaan Kriteria untuk Rencana Pemulangan ' . (string) ($ctx['patient_name'] ?? ''),
    ]);
    $observation['valueCodeableConcept'] = ['text' => $kriteria !== '' ? $kriteria : $disposisi];

    return [SatuSehatBundleBuilder::entry($observation)];
  }

  /** Label risiko jatuh dari nilai enum penilaian_awal_keperawatan_igd.hasil. */
  private static function risikoLabel($hasil)
  {
    $hasil = trim((string) $hasil);
    if (stripos($hasil, 'tinggi') !== false) {
      return 'Risiko tinggi';
    }
    if (stripos($hasil, 'rendah') !== false) {
      return 'Risiko rendah';
    }
    return 'Tidak beresiko';
  }

  /** Kerangka Observation IGD (status final, performer praktisi). */
  private static function observation(array $ctx, array $spec)
  {
    $system = (string) ($spec['code'][2] ?? 'http://loinc.org');
    $time = (array) ($ctx['time'] ?? []);
    $effective = (string) ($time['triage'] ?? ($time['exam'] ?? ($time['now'] ?? '')));

    return [
      'resourceType' => 'Observation',
      'status' => 'final',
      'category' => [
        [
          'coding' => [
            [
              'system' => 'http://terminology.hl7.org/CodeSystem/observation-category',
              'code' => (string) ($spec['category'][0] ?? 'exam'),
              'display' => (string) ($spec['category'][1] ?? 'Exam'),
            ],
          ],
        ],
      ],
      'code' => [
        'coding' => [
          ['system' => $system, 'code' => (string) ($spec['code'][0] ?? ''), 'display' => (string) ($spec['code'][1] ?? '')],
        ],
      ],
      'subject' => [
        'reference' => (string) (($ctx['ref'] ?? [])['patient'] ?? ''),
        'display' => (string) ($ctx['patient_name'] ?? ''),
      ],
      'encounter' => [
        'reference' => (string) (($ctx['ref'] ?? [])['encounter'] ?? ''),
        'display' => (string) ($spec['display'] ?? ''),
      ],
      'effectiveDateTime' => $effective,
      'issued' => $effective,
      'performer' => [['reference' => (string) (($ctx['ref'] ?? [])['practitioner'] ?? '')]],
    ];
  }
}
