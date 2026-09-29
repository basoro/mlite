<?php

namespace Plugins\Satu_Sehat\Resources;

use Plugins\Satu_Sehat\Services\SatuSehatBundleBuilder;

/**
 * Builder resource FHIR ServiceRequest untuk ERM Rawat Jalan:
 * permintaan pemeriksaan laboratorium (BAB 10a) dan radiologi (BAB 10b).
 */
class ServiceRequestBuilder
{
  /**
   * @param string $scope 'lab', 'rad', atau 'all'.
   */
  public static function build(array $ctx, $scope = 'all'): array
  {
    $entries = [];
    if ($scope === 'lab' || $scope === 'all') {
      $entries = array_merge($entries, self::buildFromPermintaan($ctx, 'permintaan_lab_items', '108252007', 'Laboratory procedure'));
    }
    if ($scope === 'rad' || $scope === 'all') {
      $entries = array_merge($entries, self::buildFromPermintaan($ctx, 'permintaan_rad_items', '363679005', 'Imaging'));
    }
    return $entries;
  }

  /**
   * ServiceRequest untuk tindakan/prosedur rawat inap (BAB 14) — mis. EKG,
   * ekokardiografi, hemodialisis — dari emr['tindakan_items'].
   *
   * Setiap entri diberi identifier "{kode}@{tgl}@{jam}" sehingga
   * ProcedureBuilder dapat menautkan Procedure.basedOn ke request ini.
   */
  public static function buildTindakan(array $ctx): array
  {
    $emr = (array) ($ctx['emr'] ?? []);
    $auth = $ctx['auth'] ?? null;
    $entries = [];

    foreach ((array) ($emr['tindakan_items'] ?? []) as $item) {
      $item = (array) $item;
      $kode = trim((string) ($item['kode'] ?? ''));
      $nama = trim((string) ($item['nama'] ?? ''));
      if ($kode === '' && $nama === '') {
        continue;
      }

      $kategori = self::tindakanCategory($nama);
      $coding = [];
      if ($kode !== '') {
        if (($item['sumber'] ?? '') === 'icd9') {
          $coding[] = [
            'system' => 'http://hl7.org/fhir/sid/icd-9-cm',
            'code' => ProcedureBuilder::normalizeIcd9($kode),
            'display' => $nama,
          ];
        } else {
          // Kode lokal jns_perawatan bukan kode KPTL (rule 10015). Coding kptl
          // hanya dikirim bila terverifikasi di codebook mlite_ktpl.
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

      $waktu = trim((string) (($item['tgl'] ?? '') . ' ' . ($item['jam'] ?? '')));
      $occurrence = $auth !== null ? $auth->fhirTime($waktu, true) : (string) (($ctx['time'] ?? [])['now'] ?? '');
      $key = self::tindakanKey($item);

      $serviceRequest = [
        'resourceType' => 'ServiceRequest',
        'identifier' => [
          [
            'system' => 'http://sys-ids.kemkes.go.id/servicerequest/' . (string) ($ctx['organization_id'] ?? ''),
            'value' => $key,
          ],
        ],
        'status' => 'active',
        'intent' => 'original-order',
        'category' => [
          [
            'coding' => [
              [
                'system' => 'http://terminology.kemkes.go.id',
                'code' => $kategori[0],
                'display' => $kategori[1],
              ],
            ],
          ],
        ],
        'priority' => 'routine',
        'code' => ['coding' => $coding, 'text' => $nama],
        'subject' => ['reference' => (string) (($ctx['ref'] ?? [])['patient'] ?? '')],
        'encounter' => [
          'reference' => (string) (($ctx['ref'] ?? [])['encounter'] ?? ''),
          'display' => 'Permintaan Tindakan ' . $nama . ' ' . (string) ($ctx['patient_name'] ?? ''),
        ],
        'occurrenceDateTime' => $occurrence,
        'authoredOn' => (string) (($ctx['time'] ?? [])['reg'] ?? $occurrence),
        'requester' => [
          'reference' => (string) (($ctx['ref'] ?? [])['practitioner'] ?? ''),
          'display' => (string) ($ctx['practitioner_name'] ?? ''),
        ],
        'performer' => [
          [
            'reference' => (string) (($ctx['ref'] ?? [])['practitioner'] ?? ''),
            'display' => (string) ($ctx['practitioner_name'] ?? ''),
          ],
        ],
      ];

      $entries[$key] = SatuSehatBundleBuilder::entry($serviceRequest);
    }

    return $entries;
  }

  /** Kunci pencocokan ServiceRequest <-> Procedure untuk satu tindakan. */
  public static function tindakanKey(array $item)
  {
    return trim((string) (($item['kode'] ?? '') . '@' . ($item['tgl'] ?? '') . '@' . ($item['jam'] ?? '')), '@');
  }

  /** Kategori tindakan: TK000028 (diagnostic) / TK000029 (therapeutic). */
  private static function tindakanCategory($nama)
  {
    if (preg_match('/hemodial|cuci darah|transfusi|operasi|tindakan|terapi/i', $nama)) {
      return ['TK000029', 'Therapeutic procedure'];
    }
    return ['TK000028', 'Diagnostic procedure'];
  }

  private static function buildFromPermintaan(array $ctx, $emrKey, $snomedCategory, $categoryDisplay)
  {
    $emr = (array) ($ctx['emr'] ?? []);
    $permintaanList = (array) ($emr[$emrKey] ?? []);
    $entries = [];
    $auth = $ctx['auth'] ?? null;

    foreach ($permintaanList as $permintaan) {
      $permintaan = (array) $permintaan;
      $occurrence = $auth !== null
        ? $auth->fhirTime((($permintaan['tgl_sampel'] ?? '') . ' ' . ($permintaan['jam_sampel'] ?? '')) ?: (($permintaan['tgl_permintaan'] ?? '') . ' ' . ($permintaan['jam_permintaan'] ?? '')), true)
        : (string) (($ctx['time'] ?? [])['now'] ?? '');

      foreach ((array) ($permintaan['items'] ?? []) as $item) {
        $item = (array) $item;
        $map = (array) ($item['map'] ?? []);
        $kode = (string) ($map['code'] ?? '');
        $nama = (string) ($item['nama'] ?? ($map['display'] ?? ($item['kd_jenis_prw'] ?? '')));

        $coding = [];
        if ($kode !== '') {
          $system = (string) ($map['system'] ?? 'http://loinc.org');
          $coding[] = ['system' => $system, 'code' => $kode, 'display' => $nama];
        }

        $serviceRequest = [
          'resourceType' => 'ServiceRequest',
          'identifier' => [
            [
              'system' => 'http://sys-ids.kemkes.go.id/erm-ralan/order/' . (string) ($ctx['organization_id'] ?? ''),
              'value' => (string) ($permintaan['noorder'] ?? '') . '-' . (string) ($item['kd_jenis_prw'] ?? ''),
            ],
          ],
          'status' => 'completed',
          'intent' => 'order',
          'category' => [
            [
              'coding' => [
                [
                  'system' => 'http://snomed.info/sct',
                  'code' => $snomedCategory,
                  'display' => $categoryDisplay,
                ],
              ],
            ],
          ],
          'code' => [
            'coding' => $coding,
            'text' => $nama,
          ],
          'priority' => 'routine',
          'subject' => ['reference' => (string) (($ctx['ref'] ?? [])['patient'] ?? '')],
          'encounter' => ['reference' => (string) (($ctx['ref'] ?? [])['encounter'] ?? '')],
          'occurrenceDateTime' => $occurrence,
          'authoredOn' => $auth !== null
            ? $auth->fhirTime((($permintaan['tgl_permintaan'] ?? '') . ' ' . ($permintaan['jam_permintaan'] ?? '')), true)
            : $occurrence,
          'requester' => ['reference' => (string) (($ctx['ref'] ?? [])['practitioner'] ?? '')],
        ];

        $diagnosaKlinis = trim((string) ($permintaan['diagnosa_klinis'] ?? ''));
        if ($diagnosaKlinis !== '') {
          $serviceRequest['reasonCode'] = [['text' => $diagnosaKlinis]];
        }
        $info = trim((string) ($permintaan['informasi_tambahan'] ?? ''));
        if ($info !== '') {
          $serviceRequest['note'] = [['text' => $info]];
        }

        $entries[] = SatuSehatBundleBuilder::entry($serviceRequest);
      }
    }

    return $entries;
  }
}
