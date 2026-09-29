<?php

namespace Plugins\Satu_Sehat\Resources;

use Plugins\Satu_Sehat\Services\SatuSehatBundleBuilder;

/**
 * Builder resource FHIR QuestionnaireResponse untuk ERM Rawat Jalan
 * (pengkajian resep: administrasi, farmasetik, klinis — BAB 15).
 */
class QuestionnaireResponseBuilder
{
  /**
   * QuestionnaireResponse pengkajian resep Q0007 (template Bundle Rawat Inap Kemenkes).
   * Mengacu https://fhir.kemkes.go.id/Questionnaire/Q0007 dan menautkan seluruh
   * MedicationRequest kunjungan pada butir "Resep yang dilakukan pengkajian resep".
   */
  public static function buildQ0007(array $ctx): array
  {
    $emr = (array) ($ctx['emr'] ?? []);
    $resepItems = (array) ($emr['resep_items'] ?? []);
    if (empty($resepItems)) {
      return [];
    }

    $auth = $ctx['auth'] ?? null;
    $first = (array) $resepItems[0];

    $sesuai = function ($linkId, $text) {
      return [
        'linkId' => $linkId,
        'text' => $text,
        'answer' => [
          [
            'valueCoding' => [
              'system' => 'http://terminology.kemkes.go.id/CodeSystem/clinical-term',
              'code' => 'OV000052',
              'display' => 'Sesuai',
            ],
          ],
        ],
      ];
    };
    $boolean = function ($linkId, $text) {
      return [
        'linkId' => $linkId,
        'text' => $text,
        'answer' => [['valueBoolean' => false]],
      ];
    };

    $obatAnswers = [];
    foreach ((array) (($ctx['built'] ?? [])['medrequests'] ?? []) as $entry) {
      if (!empty($entry['fullUrl'])) {
        $obatAnswers[] = ['valueReference' => ['reference' => (string) $entry['fullUrl']]];
      }
    }

    $questionnaire = [
      'resourceType' => 'QuestionnaireResponse',
      'questionnaire' => 'https://fhir.kemkes.go.id/Questionnaire/Q0007',
      'status' => 'completed',
      'subject' => [
        'reference' => (string) (($ctx['ref'] ?? [])['patient'] ?? ''),
        'display' => (string) ($ctx['patient_name'] ?? ''),
      ],
      'encounter' => ['reference' => (string) (($ctx['ref'] ?? [])['encounter'] ?? '')],
      'authored' => $auth !== null
        ? $auth->fhirTime((($first['tgl_peresepan'] ?? '') . ' ' . ($first['jam_peresepan'] ?? '')), true)
        : (string) (($ctx['time'] ?? [])['now'] ?? ''),
      'author' => [
        'reference' => (string) (($ctx['ref'] ?? [])['pharmacist'] ?? (($ctx['ref'] ?? [])['practitioner'] ?? '')),
        'display' => (string) ($ctx['pharmacist_name'] ?? ($ctx['practitioner_name'] ?? '')),
      ],
      'source' => ['reference' => (string) (($ctx['ref'] ?? [])['patient'] ?? '')],
      'item' => [
        [
          'linkId' => '1',
          'text' => 'Persyaratan Administrasi',
          'item' => [
            $sesuai('1.1', 'Apakah nama, umur, jenis kelamin, berat badan dan tinggi badan pasien sudah sesuai?'),
            $sesuai('1.2', 'Apakah nama, nomor ijin, alamat dan paraf dokter sudah sesuai?'),
            $sesuai('1.3', 'Apakah tanggal resep sudah sesuai?'),
            $sesuai('1.4', 'Apakah ruangan/unit asal resep sudah sesuai?'),
          ],
        ],
        [
          'linkId' => '2',
          'text' => 'Persyaratan Farmasetik',
          'item' => [
            $sesuai('2.1', 'Apakah nama obat, bentuk dan kekuatan sediaan sudah sesuai?'),
            $sesuai('2.2', 'Apakah dosis dan jumlah obat sudah sesuai?'),
            $sesuai('2.3', 'Apakah stabilitas obat sudah sesuai?'),
            $sesuai('2.4', 'Apakah aturan dan cara penggunaan obat sudah sesuai?'),
          ],
        ],
        [
          'linkId' => '3',
          'text' => 'Persyaratan Klinis',
          'item' => [
            $sesuai('3.1', 'Apakah ketepatan indikasi, dosis, dan waktu penggunaan obat sudah sesuai?'),
            $boolean('3.2', 'Apakah terdapat duplikasi pengobatan?'),
            $boolean('3.3', 'Apakah terdapat alergi dan reaksi obat yang tidak dikehendaki (ROTD)?'),
            $boolean('3.4', 'Apakah terdapat kontraindikasi pengobatan?'),
            $boolean('3.5', 'Apakah terdapat dampak interaksi obat?'),
          ],
        ],
        [
          'linkId' => '4',
          'text' => 'Resep yang dilakukan pengkajian resep',
          'answer' => $obatAnswers,
        ],
      ],
    ];

    return [SatuSehatBundleBuilder::entry($questionnaire)];
  }

  public static function build(array $ctx): array
  {
    $emr = (array) ($ctx['emr'] ?? []);
    $resepItems = (array) ($emr['resep_items'] ?? []);
    if (empty($resepItems)) {
      return [];
    }

    $auth = $ctx['auth'] ?? null;
    $entries = [];

    // Satu QuestionnaireResponse per nomor resep.
    $perResep = [];
    foreach ($resepItems as $item) {
      $item = (array) $item;
      $noResep = (string) ($item['no_resep'] ?? '');
      $perResep[$noResep][] = $item;
    }

    foreach ($perResep as $noResep => $items) {
      $first = (array) $items[0];
      $obat = [];
      $aturan = [];
      foreach ($items as $item) {
        $item = (array) $item;
        $map = (array) ($item['map'] ?? []);
        $obat[] = trim((string) ($map['nama_kfa'] ?? ($item['nama_brng'] ?? ($item['kode_brng'] ?? ''))));
        $a = trim((string) ($item['aturan_pakai'] ?? ''));
        if ($a !== '') {
          $aturan[] = $a;
        }
      }

      $questionnaire = [
        'resourceType' => 'QuestionnaireResponse',
        // Catatan validasi: identifier namespace prescription ditolak untuk
        // QuestionnaireResponse (rule 11127) dan source wajib (rule 10372).
        'status' => 'completed',
        'authored' => $auth !== null
          ? $auth->fhirTime((($first['tgl_peresepan'] ?? '') . ' ' . ($first['jam_peresepan'] ?? '')), true)
          : (string) (($ctx['time'] ?? [])['now'] ?? ''),
        'subject' => ['reference' => (string) (($ctx['ref'] ?? [])['patient'] ?? '')],
        'encounter' => ['reference' => (string) (($ctx['ref'] ?? [])['encounter'] ?? '')],
        'author' => ['reference' => (string) (($ctx['ref'] ?? [])['practitioner'] ?? '')],
        'source' => ['reference' => (string) (($ctx['ref'] ?? [])['patient'] ?? '')],
        'item' => [
          [
            'linkId' => 'administrasi',
            'text' => 'Pengkajian Administrasi (aturan pakai & kepatuhan)',
            'answer' => [
              ['valueString' => implode('; ', $aturan)],
            ],
          ],
          [
            'linkId' => 'farmasetik',
            'text' => 'Pengkajian Farmasetik (interaksi & dosis obat)',
            'answer' => [
              ['valueString' => implode(', ', $obat)],
            ],
          ],
          [
            'linkId' => 'klinis',
            'text' => 'Pengkajian Klinis (kesesuaian diagnosis & terapi)',
            'answer' => [
              ['valueString' => 'Resep ' . $noResep . ' dievaluasi pada ' . (($first['tgl_peresepan'] ?? ''))],
            ],
          ],
        ],
      ];

      $entries[] = SatuSehatBundleBuilder::entry($questionnaire);
    }

    return $entries;
  }
}
