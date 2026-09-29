<?php

namespace Plugins\Satu_Sehat\Resources;

use Plugins\Satu_Sehat\Services\SatuSehatBundleBuilder;

/**
 * Builder resource FHIR CarePlan untuk ERM IGD mengikuti template Bundle
 * Transaction IGD Kemenkes:
 *
 * - "Rencana Rawat" & "Instruksi Medik dan Keperawatan"
 *   (SNOMED 702779007 Emergency health care plan agreed, intent plan)
 * - "Perencanaan Pemulangan Pasien"
 *   (SNOMED 736372004 Discharge care plan)
 */
class CarePlanIgdBuilder
{
  /** Gabungan CarePlan IGD. */
  public static function build(array $ctx): array
  {
    return array_merge(
      self::buildRencanaRawat($ctx),
      self::buildInstruksi($ctx),
      self::buildRencanaPulang($ctx)
    );
  }

  /** Rencana rawat IGD (702779007). */
  public static function buildRencanaRawat(array $ctx): array
  {
    $emr = (array) ($ctx['emr'] ?? []);
    $pemeriksaan = (array) ($emr['pemeriksaan'] ?? []);
    $penilaian = (array) ($emr['penilaian_igd'] ?? []);
    $deskripsi = self::joinText([
      trim((string) ($pemeriksaan['rtl'] ?? '')),
      trim((string) ($penilaian['rencana'] ?? '')),
    ]);
    if ($deskripsi === '') {
      return [];
    }

    return [SatuSehatBundleBuilder::entry(self::carePlan($ctx, [
      'title' => 'Rencana Rawat',
      'category' => ['702779007', 'Emergency health care plan agreed'],
      'description' => $deskripsi,
    ]))];
  }

  /** Instruksi medik dan keperawatan IGD (702779007). */
  public static function buildInstruksi(array $ctx): array
  {
    $emr = (array) ($ctx['emr'] ?? []);
    $pemeriksaan = (array) ($emr['pemeriksaan'] ?? []);
    $deskripsi = self::joinText([
      trim((string) ($pemeriksaan['instruksi'] ?? '')),
    ]);
    if ($deskripsi === '') {
      return [];
    }

    return [SatuSehatBundleBuilder::entry(self::carePlan($ctx, [
      'title' => 'Instruksi Medik dan Keperawatan',
      'category' => ['702779007', 'Emergency health care plan agreed'],
      'description' => $deskripsi,
    ]))];
  }

  /** Perencanaan pemulangan pasien (736372004). */
  public static function buildRencanaPulang(array $ctx): array
  {
    $emr = (array) ($ctx['emr'] ?? []);
    $pemeriksaan = (array) ($emr['pemeriksaan'] ?? []);
    $deskripsi = self::joinText([
      trim((string) ($emr['rencana_pulang'] ?? '')),
      trim((string) ($pemeriksaan['rtl'] ?? '')),
    ]);
    if ($deskripsi === '') {
      return [];
    }

    return [SatuSehatBundleBuilder::entry(self::carePlan($ctx, [
      'title' => 'Perencanaan Pemulangan Pasien',
      'category' => ['736372004', 'Discharge care plan'],
      'description' => $deskripsi,
    ]))];
  }

  /** Kerangka CarePlan IGD (status active, intent plan, author praktisi). */
  private static function carePlan(array $ctx, array $spec)
  {
    $time = (array) ($ctx['time'] ?? []);
    return [
      'resourceType' => 'CarePlan',
      'title' => (string) ($spec['title'] ?? ''),
      'status' => 'active',
      'category' => [
        [
          'coding' => [
            [
              'system' => 'http://snomed.info/sct',
              'code' => (string) ($spec['category'][0] ?? ''),
              'display' => (string) ($spec['category'][1] ?? ''),
            ],
          ],
        ],
      ],
      'intent' => 'plan',
      'description' => (string) ($spec['description'] ?? ''),
      'subject' => [
        'reference' => (string) (($ctx['ref'] ?? [])['patient'] ?? ''),
        'display' => (string) ($ctx['patient_name'] ?? ''),
      ],
      'encounter' => ['reference' => (string) (($ctx['ref'] ?? [])['encounter'] ?? '')],
      'created' => (string) ($time['exam'] ?? ($time['reg'] ?? ($time['now'] ?? ''))),
      'author' => ['reference' => (string) (($ctx['ref'] ?? [])['practitioner'] ?? '')],
    ];
  }

  private static function joinText(array $parts)
  {
    $parts = array_values(array_filter(array_map('trim', $parts), function ($part) {
      return $part !== '';
    }));
    return implode("\n", $parts);
  }
}
