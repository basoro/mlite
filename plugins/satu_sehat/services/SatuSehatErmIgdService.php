<?php

namespace Plugins\Satu_Sehat\Services;

use Plugins\Satu_Sehat\Resources\AllergyIntoleranceBuilder;
use Plugins\Satu_Sehat\Resources\CarePlanIgdBuilder;
use Plugins\Satu_Sehat\Resources\ConditionIgdBuilder;
use Plugins\Satu_Sehat\Resources\DiagnosticReportRanapBuilder;
use Plugins\Satu_Sehat\Resources\EncounterIgdBuilder;
use Plugins\Satu_Sehat\Resources\MedicationBuilder;
use Plugins\Satu_Sehat\Resources\MedicationDispenseBuilder;
use Plugins\Satu_Sehat\Resources\MedicationRequestBuilder;
use Plugins\Satu_Sehat\Resources\ObservationBuilder;
use Plugins\Satu_Sehat\Resources\ObservationIgdBuilder;
use Plugins\Satu_Sehat\Resources\ObservationRanapBuilder;
use Plugins\Satu_Sehat\Resources\ProcedureIgdBuilder;
use Plugins\Satu_Sehat\Resources\QuestionnaireResponseBuilder;
use Plugins\Satu_Sehat\Resources\ServiceRequestIgdBuilder;
use Plugins\Satu_Sehat\Resources\SpecimenBuilder;

/**
 * Orkestrator sub modul ERM IGD (Instalasi Gawat Darurat).
 *
 * Mengembangkan SatuSehatErmRalanService (kunjungan IGD tercatat sebagai
 * registrasi poli IGD) dengan sumber data khas IGD (mlite_triase_igd,
 * penilaian_awal_keperawatan_igd) dan pipeline resource FHIR mengikuti
 * template Bundle Transaction IGD Kemenkes:
 *
 * Encounter EMER (statusHistory arrived/triaged/in-progress/finished,
 * lokasi berjenjang + ServiceClass, diagnosis role AD, dischargeDisposition),
 * Condition (diagnosis awal / kerja provisional / banding differential),
 * Procedure (emergensi + persiapan puasa not-done), Observation (tingkat
 * kesadaran 67775-7, risiko jatuh 59461-4, status kehamilan 82810-3,
 * tanda vital triase, hasil lab/rad, kriteria pemulangan), CarePlan
 * (rencana rawat & instruksi 702779007, perencanaan pemulangan 736372004),
 * ServiceRequest (lab/rad prioritas stat + orderDetail + supportingInfo),
 * Specimen, DiagnosticReport, Medication/MedicationRequest/MedicationDispense,
 * dan QuestionnaireResponse Q0007.
 */
class SatuSehatErmIgdService extends SatuSehatErmRalanService
{
  // ==================================================================
  // 1. Data klinis IGD
  // ==================================================================

  /** {@inheritdoc} Selalu 'igd'. */
  protected function detectErmType($reg = null, $override = null)
  {
    return 'igd';
  }

  /** {@inheritdoc} 'ERM IGD'. */
  public function ermLabel()
  {
    return 'ERM IGD';
  }

  /** Kode poli IGD dari pengaturan (settings.igd), default 'IGD'. */
  public function igdPoliCode()
  {
    $kode = '';
    if (isset($this->core->settings)) {
      $kode = trim((string) $this->core->settings->get('settings.igd'));
    }
    return $kode !== '' ? $kode : 'IGD';
  }

  /**
   * Ambil seluruh data ERM IGD sebuah kunjungan (data rawat jalan + triase IGD
   * + penilaian awal keperawatan IGD).
   */
  public function getEmr($no_rawat, $ermType = null)
  {
    $no_rawat = $this->normalizeNoRawat($no_rawat);
    if (isset($this->pipelineCache['emr_igd'][$no_rawat])) {
      return $this->pipelineCache['emr_igd'][$no_rawat];
    }

    $result = parent::getEmr($no_rawat, 'igd');
    if (empty($result['reg_periksa'])) {
      return $result;
    }
    $result['erm_type'] = 'igd';
    $result['triase'] = [];
    $result['penilaian_igd'] = [];
    $result['lokasi_items'] = [];

    $reg = (array) $result['reg_periksa'];

    // ---- Triase IGD (mlite_triase_igd) ----
    $triase = (array) ($this->fetchOne(
      'SELECT * FROM mlite_triase_igd WHERE no_rawat = ? ORDER BY tgl_triase DESC, id_triase DESC LIMIT 1',
      [$no_rawat]
    ) ?: []);
    $result['triase'] = $triase;

    // ---- Penilaian awal keperawatan IGD ----
    $penilaian = (array) ($this->fetchOne(
      'SELECT * FROM penilaian_awal_keperawatan_igd WHERE no_rawat = ? ORDER BY tanggal DESC LIMIT 1',
      [$no_rawat]
    ) ?: []);
    $result['penilaian_igd'] = [
      'tanggal' => (string) ($penilaian['tanggal'] ?? ''),
      'informasi' => (string) ($penilaian['informasi'] ?? ''),
      'keluhan_utama' => trim((string) ($penilaian['keluhan_utama'] ?? '')),
      'rpd' => trim((string) ($penilaian['rpd'] ?? '')),
      'rpo' => trim((string) ($penilaian['rpo'] ?? '')),
      'status_kehamilan' => trim((string) ($penilaian['status_kehamilan'] ?? '')),
      'gravida' => trim((string) ($penilaian['gravida'] ?? '')),
      'para' => trim((string) ($penilaian['para'] ?? '')),
      'abortus' => trim((string) ($penilaian['abortus'] ?? '')),
      'hpht' => trim((string) ($penilaian['hpht'] ?? '')),
      'hasil' => trim((string) ($penilaian['hasil'] ?? '')),
      'morse_total' => trim((string) ($penilaian['penilaian_jatuhmorse_totalnilai'] ?? '')),
      'nyeri' => trim((string) ($penilaian['nyeri'] ?? '')),
      'skala_nyeri' => trim((string) ($penilaian['skala_nyeri'] ?? '')),
      'rencana' => trim((string) ($penilaian['rencana'] ?? '')),
      'nip' => trim((string) ($penilaian['nip'] ?? '')),
    ];

    // ---- Overlay tanda vital & anamnesis dari triase bila pemeriksaan kosong ----
    $p = (array) $result['pemeriksaan'];
    if (trim((string) ($p['tensi'] ?? '')) === '' && !empty($triase)) {
      $p['tensi'] = trim((string) ($triase['tekanan_darah'] ?? ''));
      $p['nadi'] = trim((string) ($triase['nadi'] ?? ''));
      $p['respirasi'] = trim((string) ($triase['respirasi'] ?? ''));
      $p['suhu'] = trim((string) ($triase['suhu'] ?? ''));
      $p['spo2'] = trim((string) ($triase['spo2'] ?? ''));
      $p['kesadaran'] = trim((string) ($triase['kesadaran'] ?? ''));
      $gcs = trim(implode('', [
        ($triase['gcs_e'] ?? '') !== '' ? 'E' . $triase['gcs_e'] : '',
        ($triase['gcs_v'] ?? '') !== '' ? 'V' . $triase['gcs_v'] : '',
        ($triase['gcs_m'] ?? '') !== '' ? 'M' . $triase['gcs_m'] : '',
      ]));
      if ($gcs !== '') {
        $p['gcs'] = $gcs;
      }
      if (trim((string) ($p['tgl_perawatan'] ?? '')) === '') {
        $p['tgl_perawatan'] = substr((string) ($triase['tgl_triase'] ?? ''), 0, 10);
        $p['jam_rawat'] = substr((string) ($triase['tgl_triase'] ?? ''), 11, 8);
      }
    }
    if (trim((string) ($p['keluhan'] ?? '')) === '') {
      $p['keluhan'] = trim((string) ($triase['keluhan_utama'] ?? ''));
      if ($p['keluhan'] === '') {
        $p['keluhan'] = (string) ($result['penilaian_igd']['keluhan_utama'] ?? '');
      }
    }
    if (trim((string) ($p['penilaian'] ?? '')) === '' && trim((string) ($triase['diagnosa_awal'] ?? '')) !== '') {
      $p['penilaian'] = trim((string) $triase['diagnosa_awal']);
    }
    $result['pemeriksaan'] = $p;

    // ---- Lokasi berjenjang IGD: ruang triase -> ruang tindakan ----
    $nmPoli = trim((string) (($result['poliklinik']['nm_poli'] ?? '')));
    $start = $this->auth()->fhirTime(trim((string) (($reg['tgl_registrasi'] ?? '') . ' ' . ($reg['jam_reg'] ?? ''))), true);
    $triageAt = trim((string) ($triase['tgl_triase'] ?? ''));
    $triageTime = $triageAt !== '' ? $this->auth()->fhirTime($triageAt, false) : '';
    if ($triageTime !== '') {
      $result['lokasi_items'] = [
        ['display' => 'Ruangan Triase, ' . $nmPoli, 'start' => $start, 'end' => $triageTime],
        ['display' => 'Ruangan Tindakan, ' . $nmPoli, 'start' => $triageTime, 'end' => $this->dischargeTime($result)],
      ];
    } else {
      $result['lokasi_items'] = [
        ['display' => $nmPoli, 'start' => $start, 'end' => $this->dischargeTime($result)],
      ];
    }

    $this->pipelineCache['emr_igd'][$no_rawat] = $result;
    return $result;
  }

  /** Waktu discharge administrasi IGD ('' bila kunjungan masih berjalan). */
  private function dischargeTime(array $emr)
  {
    if (!$this->isClosed($emr)) {
      return '';
    }
    $p = (array) ($emr['pemeriksaan'] ?? []);
    return $this->auth()->fhirTime(trim((string) (($p['tgl_perawatan'] ?? '') . ' ' . ($p['jam_rawat'] ?? ''))), false);
  }

  /** Kunjungan IGD dianggap selesai bila ada stts_pulang / pindah ke rawat inap. */
  private function isClosed(array $emr)
  {
    $reg = (array) ($emr['reg_periksa'] ?? []);
    if (stripos((string) ($reg['status_lanjut'] ?? ''), 'ranap') !== false) {
      return true;
    }
    $sttsPulang = trim((string) ($reg['stts_pulang'] ?? ''));
    return $sttsPulang !== '' && strpos($sttsPulang, '0000-00-00') !== 0;
  }

  /** {@inheritdoc} Konteks IGD + waktu triase & discharge. */
  public function buildContext(array $emr)
  {
    $ctx = parent::buildContext($emr);
    $triase = (array) ($emr['triase'] ?? []);
    $triageTime = trim((string) ($triase['tgl_triase'] ?? '')) !== ''
      ? $this->auth()->fhirTime((string) $triase['tgl_triase'], false)
      : '';
    $ctx['time']['triage'] = $triageTime !== '' ? $triageTime : (string) ($ctx['time']['reg'] ?? '');
    $ctx['time']['discharge'] = $this->dischargeTime($emr);
    return $ctx;
  }

  // ==================================================================
  // 2. Pipeline perakitan resource FHIR IGD
  // ==================================================================

  /**
   * Rakit seluruh resource ERM IGD mengikuti template Bundle Transaction IGD
   * Kemenkes. Hasil di-cache per kunjungan agar UUID konsisten antar hook.
   *
   * @return array{ctx:array,entries:array,groups:array<string,array>}
   */
  public function buildResourceEntries($no_rawat, $ermType = null, $emr = null)
  {
    $no_rawat = $this->normalizeNoRawat($no_rawat);
    if (isset($this->pipelineCache['pipeline_igd'][$no_rawat])) {
      return $this->pipelineCache['pipeline_igd'][$no_rawat];
    }

    if (!is_array($emr) || empty($emr['reg_periksa'])) {
      $emr = $this->getEmr($no_rawat);
    }
    $ctx = $this->buildContext($emr);
    $ctx['built'] = [];

    // 1. Condition: diagnosis awal / kerja (provisional) / banding (differential).
    $conditionsAwal = ConditionIgdBuilder::buildDiagnosisAwal($ctx);
    $conditionsKerja = ConditionIgdBuilder::buildDiagnosisKerja($ctx);
    $conditionsBanding = ConditionIgdBuilder::buildDiagnosisBanding($ctx);
    $ctx['built']['conditions_awal'] = $conditionsAwal;
    $ctx['built']['conditions_kerja'] = $conditionsKerja;
    $ctx['built']['conditions_banding'] = $conditionsBanding;
    $ctx['built']['conditions'] = array_merge($conditionsAwal, $conditionsKerja, $conditionsBanding);
    $ctx['built']['conditions_dx'] = array_merge($conditionsKerja, $conditionsBanding);

    // 2. Encounter EMER (rujuk diagnosis awal sebagai role AD).
    $encounter = EncounterIgdBuilder::build($ctx);

    // 3. Observation khas IGD + tanda vital triase.
    $obsKesadaran = ObservationIgdBuilder::buildKesadaran($ctx);
    $obsRisikoJatuh = ObservationIgdBuilder::buildRisikoJatuh($ctx);
    $obsKehamilan = ObservationIgdBuilder::buildStatusKehamilan($ctx);
    $obsKriteriaPulang = ObservationIgdBuilder::buildKriteriaPulang($ctx);
    $ctx['built']['obs_pra_rad'] = $obsKehamilan;
    $obsIgd = array_merge(
      $obsKesadaran,
      $obsRisikoJatuh,
      array_values(array_filter([$obsKehamilan])),
      $obsKriteriaPulang
    );
    $obsVital = ObservationRanapBuilder::buildVitalSigns($ctx);

    // 4. Prosedur: emergensi + persiapan penunjang (puasa, not-done).
    $proceduresEmergency = ProcedureIgdBuilder::buildEmergency($ctx);
    $praLab = ProcedureIgdBuilder::buildPreparation($ctx, 'lab');
    $praRad = ProcedureIgdBuilder::buildPreparation($ctx, 'rad');
    $ctx['built']['procedures_pralab'] = $praLab;
    $ctx['built']['procedures_prarad'] = $praRad;

    // 5. Alergi (dipakai pula sebagai supportingInfo ServiceRequest radiologi).
    $allergy = AllergyIntoleranceBuilder::build($ctx);
    $ctx['built']['allergy'] = $allergy;

    // 6. ServiceRequest penunjang (prioritas stat, reasonReference, supportingInfo).
    $srLab = ServiceRequestIgdBuilder::build($ctx, 'lab');
    $srRad = ServiceRequestIgdBuilder::build($ctx, 'rad');
    $ctx['built']['servicerequests_lab'] = $srLab;
    $ctx['built']['servicerequests_rad'] = $srRad;
    $ctx['built']['servicerequests'] = array_merge($srLab, $srRad);

    // 7. Obat: Medication + MedicationRequest + MedicationDispense + Q0007.
    $medications = MedicationBuilder::build($ctx);
    $ctx['built']['medications'] = $medications;
    $medRequests = MedicationRequestBuilder::build($ctx);
    $ctx['built']['medrequests'] = $medRequests;
    $medDispenses = MedicationDispenseBuilder::build($ctx);
    $ctx['built']['meddispenses'] = $medDispenses;
    $questionnaires = QuestionnaireResponseBuilder::buildQ0007($ctx);

    // 8. Spesimen lab (rujuk ServiceRequest) lalu hasil lab/rad.
    $specimens = SpecimenBuilder::build($ctx);
    $ctx['built']['specimens'] = $specimens;
    $obsLab = ObservationBuilder::buildLabObservations($ctx);
    $ctx['built']['observations_lab'] = $obsLab;
    $obsRad = ObservationRanapBuilder::buildRadiologyResults($ctx);
    $ctx['built']['observations_rad'] = $obsRad;

    // 9. DiagnosticReport lab & radiologi.
    $drLab = DiagnosticReportRanapBuilder::build($ctx, 'lab');
    $drRad = DiagnosticReportRanapBuilder::build($ctx, 'rad');

    // 10. CarePlan: rencana rawat, instruksi, perencanaan pemulangan.
    $carePlans = CarePlanIgdBuilder::build($ctx);

    $groups = [
      'encounter' => $encounter,
      'condition' => $ctx['built']['conditions'],
      'observation' => array_merge($obsIgd, $obsVital),
      'allergy' => $allergy,
      'procedure' => array_merge($proceduresEmergency, array_values(array_filter([$praLab, $praRad]))),
      'care_plan' => $carePlans,
      'laboratory' => array_merge($srLab, array_values($specimens), $obsLab, $drLab),
      'radiology' => array_merge($srRad, $obsRad, $drRad),
      'medication' => array_merge($medications, $medRequests, array_values($medDispenses), $questionnaires),
    ];

    $result = [
      'ctx' => $ctx,
      'entries' => $this->uniqueEntries($groups),
      'groups' => $groups,
    ];

    $this->pipelineCache['pipeline_igd'][$no_rawat] = $result;
    return $result;
  }

  /** Gabung entri seluruh kelompok tanpa duplikat fullUrl. */
  private function uniqueEntries(array $groups)
  {
    $entries = [];
    $seen = [];
    foreach (array_merge(...array_values($groups)) as $entry) {
      if (!is_array($entry) || !isset($entry['resource'])) {
        continue;
      }
      $fullUrl = (string) ($entry['fullUrl'] ?? '');
      if ($fullUrl !== '' && isset($seen[$fullUrl])) {
        continue;
      }
      if ($fullUrl !== '') {
        $seen[$fullUrl] = true;
      }
      $entries[] = $entry;
    }
    return $entries;
  }

  // ==================================================================
  // 3. Bundle & Composition
  // ==================================================================

  /** {@inheritdoc} Composition dokumen ERM IGD. */
  public function buildCompositionResource(array $ctx, array $groups)
  {
    $composition = parent::buildCompositionResource($ctx, $groups);
    $composition['title'] = 'ERM IGD ' . (string) ($ctx['no_rawat'] ?? '');
    return $composition;
  }

  // ==================================================================
  // 4. Struktur BAB ERM IGD
  // ==================================================================

  /**
   * {@inheritdoc} Susun section ERM IGD (BAB 1 s/d 29) dengan tambahan data
   * triase, risiko jatuh, status kehamilan, dan disposisi IGD.
   */
  public function getErmSections($no_rawat, $ermType = null)
  {
    $sections = parent::getErmSections($no_rawat, 'igd');
    if (empty($sections)) {
      return $sections;
    }

    $emr = $this->getEmr($no_rawat);
    $reg = (array) ($emr['reg_periksa'] ?? []);
    $poli = (array) ($emr['poliklinik'] ?? []);
    $p = (array) ($emr['pemeriksaan'] ?? []);
    $triase = (array) ($emr['triase'] ?? []);
    $penilaian = (array) ($emr['penilaian_igd'] ?? []);

    $masuk = trim((string) (($reg['tgl_registrasi'] ?? '') . ' ' . ($reg['jam_reg'] ?? '')));
    $keluar = trim((string) (($p['tgl_perawatan'] ?? '') . ' ' . ($p['jam_rawat'] ?? '')));
    $closed = $this->isClosed($emr);
    $statusLanjut = trim((string) ($reg['status_lanjut'] ?? ''));
    $keRanap = stripos($statusLanjut, 'ranap') !== false;
    $sttsPulang = trim((string) ($reg['stts_pulang'] ?? ''));
    $disposisi = '-';
    if ($keRanap) {
      $disposisi = 'Pasien dipindahkan dari IGD ke rawat inap.';
    } elseif ($sttsPulang !== '' && strpos($sttsPulang, '0000-00-00') !== 0) {
      $map = EncounterIgdBuilder::DISCHARGE_DISPOSITION[trim(strtolower($sttsPulang))] ?? null;
      $disposisi = $map !== null ? $map[2] : $sttsPulang;
    }

    // BAB 2 — Data Kunjungan (Encounter EMER, statusHistory, lokasi berjenjang).
    $sections['data_kunjungan']['label'] = '2. Data Kunjungan · Encounter (IGD)';
    $sections['data_kunjungan']['rows'] = [
      ['No. Kunjungan (Encounter ID)', (string) ($reg['no_rawat'] ?? ''), 'Status', $closed ? 'finished' : 'in-progress'],
      ['Jenis Kunjungan (Encounter.class)', 'EMER=Emergency', 'Kelas Pelayanan (ServiceClass)', 'Kelas Reguler'],
      ['Poli / Ruangan (location)', (string) ($poli['nm_poli'] ?? ''), 'Lokasi Berjenjang', trim('Ruangan Triase -> Ruangan Tindakan, ' . ($poli['nm_poli'] ?? ''), ', ')],
      ['Tgl / Jam Masuk (period.start)', $masuk, 'Tgl / Jam Selesai (period.end)', $closed ? $keluar : '-'],
      ['Status History', 'arrived -> triaged -> in-progress -> finished', 'Tgl / Jam Triase', (string) ($triase['tgl_triase'] ?? '-')],
    ];

    // BAB 3 — Anamnesis & triase (Condition / AllergyIntolerance).
    $sections['anamnesis']['label'] = '3. Anamnesis & Triase · Condition / AllergyIntolerance';
    $sections['anamnesis']['rows'] = [
      ['Keluhan Utama (Chief Complaint) · Condition', (string) ($p['keluhan'] ?? '')],
      ['Diagnosa Awal (mlite_triase_igd.diagnosa_awal) · Condition', (string) ($triase['diagnosa_awal'] ?? '')],
      ['Airway / Breathing / Circulation (Triase)', trim(($triase['airway'] ?? '-') . ' / ' . ($triase['breathing'] ?? '-') . ' / ' . ($triase['circulation'] ?? '-'), ' / ')],
      ['Skala Triase / Kategori (Merah-Kuning-Hijau-Hitam)', trim(($triase['skala_triase'] ?? '-') . ' / ' . ($triase['kategori'] ?? '-'), ' / ')],
      ['Riwayat Penyakit Dahulu (rpd)', (string) ($penilaian['rpd'] ?? '')],
      ['Riwayat Pengobatan (rpo)', (string) ($penilaian['rpo'] ?? '')],
      ['Riwayat Alergi · AllergyIntolerance', (string) ($p['alergi'] ?? '')],
    ];

    // BAB 4 — Pemeriksaan fisik (tanda vital triase + kesadaran + GCS).
    $sections['pemeriksaan_fisik']['label'] = '4. Pemeriksaan Fisik · Observation (Vital Sign, Kesadaran 67775-7, GCS)';
    $sections['pemeriksaan_fisik']['rows'] = [
      ['Tanda Vital · Denyut Jantung (8867-4)', (string) ($p['nadi'] ?? ''), 'bpm', (string) ($triase['tgl_triase'] ?? '')],
      ['Tanda Vital · Frekuensi Pernapasan (9279-1)', (string) ($p['respirasi'] ?? ''), 'x/menit', ''],
      ['Tanda Vital · Tekanan Darah', (string) ($p['tensi'] ?? ''), 'mmHg', 'Sistolik/Diastolik'],
      ['Tanda Vital · Suhu Tubuh (8310-5)', (string) ($p['suhu'] ?? ''), '°C', ''],
      ['Tanda Vital · Saturasi Oksigen (59408-5)', (string) ($p['spo2'] ?? ''), '%', ''],
      ['Tingkat Kesadaran (67775-7 Level of responsiveness)', (string) ($triase['kesadaran'] ?? ($p['kesadaran'] ?? '')), '', ''],
      ['GCS (E/V/M)', trim(($triase['gcs_e'] ?? '') . '/' . ($triase['gcs_v'] ?? '') . '/' . ($triase['gcs_m'] ?? ''), '/'), '', ''],
    ];

    // BAB 5 — Pemeriksaan fungsional (risiko jatuh, status kehamilan, nyeri).
    $sections['pemeriksaan_fungsional']['label'] = '5. Pemeriksaan Fungsional · Observation (Risiko Jatuh 59461-4, Status Kehamilan 82810-3)';
    $sections['pemeriksaan_fungsional']['rows'] = [
      ['Risiko Jatuh (59461-4 Morse Fall Scale)', (string) ($penilaian['hasil'] ?? ''), 'Skor', (string) ($penilaian['morse_total'] ?? '-')],
      ['Status Kehamilan (82810-3 Pregnancy status)', (string) ($penilaian['status_kehamilan'] ?? ''), 'G / P / A', trim(($penilaian['gravida'] ?? '-') . ' / ' . ($penilaian['para'] ?? '-') . ' / ' . ($penilaian['abortus'] ?? '-'), ' / ')],
      ['HPHT', (string) ($penilaian['hpht'] ?? ''), 'Nyeri / Skala', trim(($penilaian['nyeri'] ?? '-') . ' / ' . ($penilaian['skala_nyeri'] ?? '-'), ' / ')],
    ];

    // BAB 8-9 — Rencana rawat & instruksi (CarePlan 702779007).
    $sections['rencana_rawat']['rows'] = [
      ['Kategori Rencana (CarePlan.category)', '702779007 Emergency health care plan agreed', '', ''],
      ['Deskripsi Rencana (CarePlan.description)', trim((string) (($p['rtl'] ?? '') ?: ($penilaian['rencana'] ?? ''))), '', ''],
    ];
    $sections['instruksi_medik_keperawatan']['rows'] = [
      ['Instruksi Medik (Dokter) · CarePlan', (string) ($p['instruksi'] ?? ''), '', ''],
      ['Instruksi Keperawatan · CarePlan', (string) ($penilaian['rencana'] ?? ''), '', ''],
    ];

    // BAB 12 — Diagnosis awal / kerja / banding (Condition encounter-diagnosis).
    $diagnosaRows = [['Jenis Diagnosis', 'Kode ICD-10', 'Nama Diagnosis', 'Verifikasi']];
    $diagnosaRows[] = ['Diagnosis Awal (Admission)', '', (string) ($triase['diagnosa_awal'] ?? ($p['keluhan'] ?? '-')), 'encounter-diagnosis'];
    foreach ((array) ($emr['diagnosa_items'] ?? []) as $item) {
      $item = (array) $item;
      $diagnosaRows[] = [
        ((int) ($item['prioritas'] ?? 0) === 1 ? 'Diagnosis Kerja (Provisional)' : 'Diagnosis Banding (Differential)'),
        (string) ($item['kode'] ?? ''),
        (string) ($item['nama'] ?? ''),
        ((int) ($item['prioritas'] ?? 0) === 1 ? 'provisional' : 'differential'),
      ];
    }
    $sections['diagnosis']['rows'] = $diagnosaRows;

    // BAB 24-25 — Kondisi & cara keluar (disposisi IGD).
    $sections['kondisi_saat_keluar']['rows'] = [
      ['Kondisi Klinis Pasien Saat Meninggalkan RS', $keRanap ? 'Dirawat inap' : (string) ($sttsPulang ?: '-'), '', ''],
    ];
    $sections['cara_keluar_rs']['label'] = '25. Cara Keluar dari Rumah Sakit · Encounter.dischargeDisposition';
    $sections['cara_keluar_rs']['rows'] = [
      ['Disposisi IGD (Encounter.dischargeDisposition)', (string) $disposisi, '', ''],
    ];
    $sections['discharge_administrasi']['rows'] = [
      ['Tanggal Discharge Administrasi', $closed ? substr($keluar, 0, 10) : '-', 'Jam Discharge Administrasi', $closed ? substr($keluar, 11) : '-'],
    ];

    return $sections;
  }

  // ==================================================================
  // 5. Daftar kunjungan IGD
  // ==================================================================

  /** {@inheritdoc} Batasi pada kunjungan poli IGD (settings.igd). */
  protected function visitWhere(array $filters)
  {
    [$where, $binds] = parent::visitWhere($filters);
    $where .= ' AND r.kd_poli = ?';
    $binds[] = $this->igdPoliCode();
    return [$where, $binds];
  }
}
