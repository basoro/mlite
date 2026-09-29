<?php

namespace Plugins\Satu_Sehat\Resources;

use Plugins\Satu_Sehat\Services\SatuSehatBundleBuilder;

/**
 * Builder resource FHIR Procedure untuk ERM Rawat Jalan (BAB 14 tindakan/prosedur medis).
 *
 * Sumber data: prosedur_pasien (ICD-9-CM) dan rawat_jl_dr/rawat_jl_pr (kode KPTL jns_perawatan).
 */
class ProcedureBuilder
{
  public static function build(array $ctx): array
  {
    $emr = (array) ($ctx['emr'] ?? []);
    $items = (array) ($emr['tindakan_items'] ?? []);
    $entries = [];

    foreach ($items as $item) {
      $item = (array) $item;
      $kode = trim((string) ($item['kode'] ?? ''));
      $nama = trim((string) ($item['nama'] ?? ''));
      if ($kode === '' && $nama === '') {
        continue;
      }

      $coding = [];
      if ($kode !== '') {
        if (($item['sumber'] ?? '') === 'icd9') {
          $coding[] = [
            'system' => 'http://hl7.org/fhir/sid/icd-9-cm',
            'code' => self::normalizeIcd9($kode),
            'display' => $nama,
          ];
        } else {
          // Kode lokal jns_perawatan (RJ048 dst) bukan kode KPTL dan ditolak
          // validator (rule 10015). Coding kptl hanya dikirim bila kode sudah
          // terverifikasi di codebook mlite_ktpl; selain itu cukup code.text.
          $kodeKptl = trim((string) ($item['map']['kode_ktpl'] ?? ''));
          if ($kodeKptl !== '') {
            $coding[] = [
              'system' => 'http://terminology.kemkes.go.id/CodeSystem/kptl',
              'code' => $kodeKptl,
              'display' => trim((string) ($item['map']['nama_ktpl'] ?? '')) !== '' ? (string) $item['map']['nama_ktpl'] : $nama,
            ];
          }
        }
      }

      $performed = [
        'start' => trim((string) (($item['tgl'] ?? '') . ' ' . ($item['jam'] ?? ''))),
      ];

      $procedure = [
        'resourceType' => 'Procedure',
        'status' => 'completed',
        'category' => [
          [
            'coding' => [
              [
                'system' => 'http://snomed.info/sct',
                'code' => '387713003',
                'display' => 'Surgical procedure',
              ],
            ],
          ],
        ],
        'code' => [
          'coding' => $coding,
          'text' => $nama !== '' ? $nama : $kode,
        ],
        'subject' => ['reference' => (string) (($ctx['ref'] ?? [])['patient'] ?? '')],
        'encounter' => ['reference' => (string) (($ctx['ref'] ?? [])['encounter'] ?? '')],
        'performer' => [
          [
            'function' => [
              'coding' => [
                [
                  'system' => 'http://terminology.hl7.org/CodeSystem/performer-role',
                  'code' => 'PRCP',
                  'display' => 'primary performer',
                ],
              ],
            ],
            'actor' => ['reference' => (string) (($ctx['ref'] ?? [])['practitioner'] ?? '')],
          ],
        ],
      ];

      // performedPeriod hanya bila waktu tindakan tersedia.
      $auth = $ctx['auth'] ?? null;
      $start = $auth !== null ? $auth->fhirTime($performed['start'], false) : '';
      if ($start !== '') {
        $procedure['performedPeriod'] = ['start' => $start, 'end' => $start];
      } elseif (!empty($ctx['time']['reg'])) {
        $procedure['performedDateTime'] = (string) $ctx['time']['reg'];
      }

      // basedOn: ServiceRequest tindakan rawat inap bila sudah dibangun.
      $tindakanKey = ServiceRequestBuilder::tindakanKey($item);
      $requests = (array) (($ctx['built'] ?? [])['tindakan_requests'] ?? []);
      if (isset($requests[$tindakanKey]['fullUrl'])) {
        $procedure['basedOn'] = [['reference' => (string) $requests[$tindakanKey]['fullUrl']]];
      }

      $entries[] = SatuSehatBundleBuilder::entry($procedure);
    }

    return $entries;
  }

  /**
   * Prosedur edukasi (BAB 19) untuk ERM Rawat Inap — kategori Education
   * (SNOMED 409073007) dengan kode KPTL 10913 Edukasi Kesehatan Individu.
   */
  public static function buildEducation(array $ctx): array
  {
    $emr = (array) ($ctx['emr'] ?? []);
    $pemeriksaan = (array) ($emr['pemeriksaan'] ?? []);
    $instruksi = trim((string) ($pemeriksaan['instruksi'] ?? ''));
    $evaluasi = trim((string) ($pemeriksaan['evaluasi'] ?? ''));
    $topik = trim((string) (($emr['edukasi_items'][0]['topik'] ?? '')));
    if ($topik === '') {
      $topik = 'Edukasi Proses Penyakit, Diagnosis, dan Rencana Asuhan';
    }
    if ($instruksi === '' && $evaluasi === '') {
      return [];
    }

    $auth = $ctx['auth'] ?? null;
    $start = $auth !== null
      ? $auth->fhirTime((string) (($ctx['time'] ?? [])['exam'] ?? ''), true)
      : (string) (($ctx['time'] ?? [])['now'] ?? '');

    $procedure = [
      'resourceType' => 'Procedure',
      'status' => 'completed',
      'category' => [
        [
          'coding' => [
            ['system' => 'http://snomed.info/sct', 'code' => '409073007', 'display' => 'Education'],
          ],
          'text' => 'Education',
        ],
      ],
      'code' => [
        'coding' => [
          ['system' => 'http://snomed.info/sct', 'code' => '84635008', 'display' => 'Disease process or condition education'],
          ['system' => 'http://terminology.kemkes.go.id/CodeSystem/kptl', 'code' => '10913', 'display' => 'Edukasi Kesehatan Individu'],
        ],
        'text' => $topik,
      ],
      'subject' => [
        'reference' => (string) (($ctx['ref'] ?? [])['patient'] ?? ''),
        'display' => (string) ($ctx['patient_name'] ?? ''),
      ],
      'encounter' => [
        'reference' => (string) (($ctx['ref'] ?? [])['encounter'] ?? ''),
        'display' => 'Edukasi ' . $topik . ' ' . (string) ($ctx['patient_name'] ?? ''),
      ],
      'performedPeriod' => ['start' => $start, 'end' => $start],
      'performer' => [
        [
          'actor' => [
            'reference' => (string) (($ctx['ref'] ?? [])['practitioner'] ?? ''),
            'display' => (string) ($ctx['practitioner_name'] ?? ''),
          ],
        ],
      ],
      'note' => [['text' => $instruksi !== '' ? $instruksi : $evaluasi]],
    ];

    return [SatuSehatBundleBuilder::entry($procedure)];
  }

  /**
   * Sisipkan titik format ICD-9-CM (mis. "8901" -> "89.01") seperti konvensi plugin.
   */
  public static function normalizeIcd9($kode)
  {
    $kode = trim((string) $kode);
    if ($kode !== '' && strpos($kode, '.') === false && strlen($kode) >= 2) {
      return substr_replace($kode, '.', 2, 0);
    }
    return $kode;
  }
}
