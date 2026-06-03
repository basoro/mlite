<?php

namespace Plugins\Clinical_Pathway;

use PDO;
use Systems\AdminModule;

class Admin extends AdminModule
{
    protected $activityCategories = [
        'Assessment',
        'Laboratorium',
        'Radiologi',
        'Obat',
        'Tindakan',
        'Nutrisi',
        'Edukasi',
        'Monitoring',
        'Outcome'
    ];

    public function navigation()
    {
        return [
            'Dashboard' => 'manage',
            'Master CP' => 'master',
            'Template Harian' => 'template',
            'Template CPPT' => 'cppttemplate',
            'Mapping ICD' => 'mapping',
            'Evidence Engine' => 'evidence',
            'Generator CP' => 'generator',
            'Monitoring' => 'monitoring',
            'Variance' => 'variance'
        ];
    }

    public function getManage()
    {
        $this->_addHeaderFiles();

        return $this->draw('manage.html', [
            'stats' => htmlspecialchars_array($this->getDashboardStats()),
            'top_icd' => htmlspecialchars_array($this->getTopDiagnosisOptions(20)),
            'recent_generators' => htmlspecialchars_array($this->getRecentGeneratedPatients())
        ]);
    }

    public function anyMaster()
    {
        $this->_addHeaderFiles();

        $selectedId = (int) ($_GET['id'] ?? 0);
        $selected = $selectedId ? $this->getMasterRow($selectedId) : [];

        return $this->draw('master.html', [
            'selected' => htmlspecialchars_array($selected),
            'rows' => htmlspecialchars_array($this->getMasterList())
        ]);
    }

    public function postSaveMaster()
    {
        $result = $this->saveMaster($_POST);

        if ($result['status']) {
            $this->notify('success', $result['message']);
        } else {
            $this->notify('failure', $result['message']);
        }

        redirect(url([ADMIN, 'clinical_pathway', 'master']));
    }

    public function anyTemplate()
    {
        $this->_addHeaderFiles();

        $selectedCpId = $this->getSelectedClinicalPathwayId();

        return $this->draw('template.html', [
            'cp_id' => $selectedCpId,
            'cp_options' => htmlspecialchars_array($this->getCpOptions()),
            'day_options' => htmlspecialchars_array($this->getTemplateDayOptions($selectedCpId)),
            'selected_cp' => htmlspecialchars_array($selectedCpId ? $this->getMasterRow($selectedCpId) : []),
            'days' => htmlspecialchars_array($this->getTemplateDays($selectedCpId)),
            'categories' => $this->activityCategories
        ]);
    }

    public function postSaveDay()
    {
        $cpId = (int) ($_POST['clinical_pathway_id'] ?? 0);
        $result = $this->saveDay($_POST);

        $this->notify($result['status'] ? 'success' : 'failure', $result['message']);
        redirect(url([ADMIN, 'clinical_pathway', 'template']) . '&cp_id=' . $cpId);
    }

    public function postSaveActivity()
    {
        $dayId = (int) ($_POST['clinical_pathway_day_id'] ?? 0);
        $cpId = (int) ($_POST['clinical_pathway_id'] ?? 0);
        if (!$cpId && $dayId) {
            $cpId = (int) $this->getClinicalPathwayIdByDay($dayId);
        }

        $result = $this->saveActivity($_POST);

        $this->notify($result['status'] ? 'success' : 'failure', $result['message']);
        redirect(url([ADMIN, 'clinical_pathway', 'template']) . '&cp_id=' . $cpId);
    }

    public function getDeleteday($id)
    {
        $day = $this->db('mlite_clinical_pathway_day')->oneArray('id', (int) $id);
        $cpId = (int) ($day['clinical_pathway_id'] ?? ($_GET['cp_id'] ?? 0));

        if ($day) {
            $this->db('mlite_clinical_pathway_day')->delete((int) $id);
            $this->notify('success', 'Hari template berhasil dihapus.');
        } else {
            $this->notify('failure', 'Hari template tidak ditemukan.');
        }

        redirect(url([ADMIN, 'clinical_pathway', 'template']) . '&cp_id=' . $cpId);
    }

    public function getDeleteactivity($id)
    {
        $activity = $this->db('mlite_clinical_pathway_activity')->oneArray('id', (int) $id);
        $cpId = (int) ($_GET['cp_id'] ?? 0);

        if ($activity) {
            if (!$cpId) {
                $cpId = (int) $this->getClinicalPathwayIdByActivity((int) $id);
            }
            $this->db('mlite_clinical_pathway_activity')->delete((int) $id);
            $this->notify('success', 'Aktivitas template berhasil dihapus.');
        } else {
            $this->notify('failure', 'Aktivitas template tidak ditemukan.');
        }

        redirect(url([ADMIN, 'clinical_pathway', 'template']) . '&cp_id=' . $cpId);
    }

    public function anyCppttemplate()
    {
        $this->_addHeaderFiles();

        $selectedId = (int) ($_GET['id'] ?? 0);
        $selected = $selectedId ? $this->getCpptTemplateRow($selectedId) : [];

        return $this->draw('cppt.template.html', [
            'selected' => htmlspecialchars_array($selected),
            'rows' => htmlspecialchars_array($this->getCpptTemplateList()),
            'ppra_options' => $this->getCpptPpraOptions($selected['ppra'] ?? '')
        ]);
    }

    public function postSavecppttemplate()
    {
        $result = $this->saveCpptTemplate($_POST);

        $this->notify($result['status'] ? 'success' : 'failure', $result['message']);
        redirect(url([ADMIN, 'clinical_pathway', 'cppttemplate']));
    }

    public function getDeletecppttemplate($id)
    {
        $row = $this->db('mlite_clinical_pathway_cppt_template')->oneArray('id', (int) $id);

        if ($row) {
            $this->db('mlite_clinical_pathway_cppt_template')->delete((int) $id);
            $this->notify('success', 'Template CPPT berhasil dihapus.');
        } else {
            $this->notify('failure', 'Template CPPT tidak ditemukan.');
        }

        redirect(url([ADMIN, 'clinical_pathway', 'cppttemplate']));
    }

    public function anyMapping()
    {
        $this->_addHeaderFiles();

        $selectedCpId = $this->getSelectedClinicalPathwayId();
        $keyword = trim($_GET['keyword'] ?? '');

        return $this->draw('mapping.html', [
            'cp_id' => $selectedCpId,
            'keyword' => $keyword,
            'cp_options' => htmlspecialchars_array($this->getCpOptions()),
            'selected_cp' => htmlspecialchars_array($selectedCpId ? $this->getMasterRow($selectedCpId) : []),
            'rows' => htmlspecialchars_array($this->getMappingList($selectedCpId)),
            'diagnosis_options' => htmlspecialchars_array($this->searchDiagnoses($keyword, 75))
        ]);
    }

    public function postSavemapping()
    {
        $cpId = (int) ($_POST['clinical_pathway_id'] ?? 0);
        $result = $this->saveMapping($_POST);

        $this->notify($result['status'] ? 'success' : 'failure', $result['message']);
        redirect(url([ADMIN, 'clinical_pathway', 'mapping']) . '&cp_id=' . $cpId);
    }

    public function getDeletemapping($id)
    {
        $cpId = (int) ($_GET['cp_id'] ?? 0);
        $row = $this->db('mlite_clinical_pathway_diagnosis')->oneArray('id', (int) $id);

        if ($row) {
            if (!$cpId) {
                $cpId = (int) $row['clinical_pathway_id'];
            }
            $this->db('mlite_clinical_pathway_diagnosis')->delete((int) $id);
            $this->notify('success', 'Mapping ICD berhasil dihapus.');
        } else {
            $this->notify('failure', 'Mapping ICD tidak ditemukan.');
        }

        redirect(url([ADMIN, 'clinical_pathway', 'mapping']) . '&cp_id=' . $cpId);
    }

    public function anyEvidence()
    {
        $this->_addHeaderFiles();

        $icd = trim($_GET['icd'] ?? ($_POST['icd'] ?? ''));
        $summary = [];

        if ($icd !== '') {
            $summary = $this->getEvidenceSummary($icd);
        }

        return $this->draw('evidence.html', [
            'icd' => $icd,
            'top_icd' => htmlspecialchars_array($this->getTopDiagnosisOptions()),
            'summary' => htmlspecialchars_array($summary)
        ]);
    }

    public function anyGenerator()
    {
        $this->_addHeaderFiles();

        $cpId = (int) ($_GET['cp_id'] ?? 0);
        $icd = trim($_GET['icd'] ?? '');
        $noRawat = trim($_GET['no_rawat'] ?? '');

        return $this->draw('generator.html', [
            'cp_id' => $cpId,
            'icd' => $icd,
            'no_rawat' => $noRawat,
            'cp_options' => htmlspecialchars_array($this->getCpOptions()),
            'top_icd' => htmlspecialchars_array($this->getTopDiagnosisOptions(100)),
            'preview' => htmlspecialchars_array($this->buildTemplatePreview($cpId, $icd)),
            'patient_preview' => htmlspecialchars_array($this->buildPatientPreview($noRawat)),
            'seeder_options' => htmlspecialchars_array($this->getSeederOptions())
        ]);
    }

    public function postGeneratetemplate()
    {
        $cpId = (int) ($_POST['clinical_pathway_id'] ?? 0);
        $icd = trim($_POST['icd'] ?? '');
        $result = $this->generateTemplateFromEvidence($cpId, $icd);

        $this->notify($result['status'] ? 'success' : 'failure', $result['message']);
        redirect(url([ADMIN, 'clinical_pathway', 'generator']) . '&cp_id=' . $cpId . '&icd=' . urlencode($icd));
    }

    public function postGeneratepatient()
    {
        $noRawat = trim($_POST['no_rawat'] ?? '');
        $result = $this->generateClinicalPathwayForPatient($noRawat);

        $this->notify($result['status'] ? 'success' : 'failure', $result['message']);
        redirect(url([ADMIN, 'clinical_pathway', 'generator']) . '&no_rawat=' . urlencode($noRawat));
    }

    public function postImportseeder()
    {
        $filename = trim($_POST['seeder_file'] ?? '');
        $result = $this->importSeederFile($filename);

        $this->notify($result['status'] ? 'success' : 'failure', $result['message']);
        redirect(url([ADMIN, 'clinical_pathway', 'generator']));
    }

    public function postRollbackseeder()
    {
        $result = $this->importSeederFile($this->getRollbackSeederFilename());

        $this->notify($result['status'] ? 'success' : 'failure', $result['message']);
        redirect(url([ADMIN, 'clinical_pathway', 'generator']));
    }

    public function getMonitoring()
    {
        $this->_addHeaderFiles();

        return $this->draw('monitoring.html', [
            'rows' => htmlspecialchars_array($this->getMonitoringList())
        ]);
    }

    public function getRefreshrealisasi($id)
    {
        $result = $this->refreshPatientActualization((int) $id);

        $this->notify($result['status'] ? 'success' : 'failure', $result['message']);
        redirect(url([ADMIN, 'clinical_pathway', 'monitoring']));
    }

    public function getSetstatus($id)
    {
        $status = trim($_GET['status'] ?? '');
        $result = $this->updatePatientClinicalPathwayStatus((int) $id, $status);

        $this->notify($result['status'] ? 'success' : 'failure', $result['message']);
        redirect(url([ADMIN, 'clinical_pathway', 'monitoring']));
    }

    public function getPrintcp()
    {
        $noRawat = trim($_GET['no_rawat'] ?? '');
        $print = $this->getPrintableCpByNoRawat($noRawat);

        if (!$print) {
            $this->notify('failure', 'Data CP pasien tidak ditemukan untuk dicetak.');
            redirect(url([ADMIN, 'clinical_pathway', 'monitoring']));
        }

        header('Content-type: text/html; charset=UTF-8');
        echo $this->draw('print.cp.html', [
            'print' => htmlspecialchars_array($print),
            'mode' => 'html'
        ]);
        exit();
    }

    public function getPdfcp()
    {
        $noRawat = trim($_GET['no_rawat'] ?? '');
        $print = $this->getPrintableCpByNoRawat($noRawat);

        if (!$print) {
            $this->notify('failure', 'Data CP pasien tidak ditemukan untuk diexport PDF.');
            redirect(url([ADMIN, 'clinical_pathway', 'monitoring']));
        }

        $html = $this->draw('print.cp.html', [
            'print' => htmlspecialchars_array($print),
            'mode' => 'pdf'
        ]);

        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'P',
            'margin_top' => 10,
            'margin_bottom' => 10,
            'margin_left' => 10,
            'margin_right' => 10
        ]);

        $mpdf->WriteHTML($html);
        $mpdf->Output('clinical-pathway-' . str_replace('/', '-', $noRawat) . '.pdf', 'I');
        exit();
    }

    public function getVariance()
    {
        $this->_addHeaderFiles();

        return $this->draw('variance.html', [
            'rows' => htmlspecialchars_array($this->getVarianceList())
        ]);
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES . '/clinical_pathway/css/admin/clinical_pathway.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES . '/clinical_pathway/js/admin/clinical_pathway.js');
        exit();
    }

    protected function db($table = null)
    {
        return $this->core->db($table);
    }

    protected function pdo()
    {
        return $this->db()->pdo();
    }

    protected function getPrintableSectionConfig()
    {
        return [
            'Assessment' => ['order' => 10, 'label' => 'Asesmen'],
            'Laboratorium' => ['order' => 20, 'label' => 'Laboratorium'],
            'Radiologi' => ['order' => 30, 'label' => 'Radiologi'],
            'Tindakan' => ['order' => 40, 'label' => 'Tindakan / Intervensi'],
            'Obat' => ['order' => 50, 'label' => 'Terapi Medikamentosa'],
            'Nutrisi' => ['order' => 60, 'label' => 'Nutrisi'],
            'Edukasi' => ['order' => 70, 'label' => 'Edukasi Terintegrasi'],
            'Monitoring' => ['order' => 80, 'label' => 'Monitoring dan Evaluasi'],
            'Outcome' => ['order' => 90, 'label' => 'Outcome / Hasil']
        ];
    }

    protected function normalizePrintableLabel($label)
    {
        $label = trim((string) $label);
        if ($label === '') {
            return '';
        }

        $label = preg_replace('/^\s*(?:\d+|[a-zA-Z]+)[\.\)]\s*/', '', $label);
        $label = preg_replace('/^\s*-\s*/', '', $label);

        return trim((string) $label);
    }

    protected function getPrintableGroupLabel($section, $group)
    {
        $group = $this->normalizePrintableLabel($group);
        if ($group !== '') {
            return $group;
        }

        $fallback = [
            'Assessment' => 'Asesmen Klinis',
            'Laboratorium' => 'Pemeriksaan Penunjang',
            'Radiologi' => 'Pemeriksaan Radiologi',
            'Tindakan' => 'Intervensi / Tindakan',
            'Obat' => 'Terapi Medikamentosa',
            'Nutrisi' => 'Asuhan Gizi',
            'Edukasi' => 'Edukasi Pasien / Keluarga',
            'Monitoring' => 'Monitoring Harian',
            'Outcome' => 'Evaluasi Outcome'
        ];

        return $fallback[$section] ?? '-';
    }

    protected function getSelectedClinicalPathwayId()
    {
        return (int) ($_GET['cp_id'] ?? ($_POST['clinical_pathway_id'] ?? 0));
    }

    protected function getSeederDirectory()
    {
        return BASE_DIR . '/plugins/clinical_pathway/seeders';
    }

    protected function getRollbackSeederFilename()
    {
        return 'rollback_dummy_seeders.sql';
    }

    protected function getSeederOptions()
    {
        return [
            [
                'file' => 'seeder_typhoid_ranap_dummy.sql',
                'label' => 'Demam Tifoid Ranap',
                'description' => 'Dewasa + anak, lengkap dengan tindakan perawat, lab, radiologi, resep, compliance, dan variance.'
            ],
            [
                'file' => 'seeder_pneumonia_ranap_dummy.sql',
                'label' => 'Pneumonia Ranap',
                'description' => 'Dummy pneumonia dewasa dengan foto thorax, CRP, antibiotik, dan cetak CP realistis.'
            ],
            [
                'file' => 'seeder_dengue_ranap_dummy.sql',
                'label' => 'Dengue Ranap',
                'description' => 'Dummy dengue dewasa dengan monitoring serial lab, cairan, dan variasi pemeriksaan penunjang.'
            ],
            [
                'file' => 'seeder_stroke_ranap_dummy.sql',
                'label' => 'Stroke Ranap',
                'description' => 'Dummy stroke dewasa dengan CT scan kepala, lab awal, resep, dan edukasi rehabilitasi.'
            ],
            [
                'file' => 'seeder_low_back_pain_ranap_dummy.sql',
                'label' => 'Low Back Pain Ranap',
                'description' => 'Dummy low back pain 4 hari dengan format matriks Clinical Pathway multi-halaman seperti lembar pathway RS.'
            ]
        ];
    }

    protected function importSeederFile($filename)
    {
        $allowed = [];
        foreach ($this->getSeederOptions() as $option) {
            $allowed[] = $option['file'];
        }
        $allowed[] = $this->getRollbackSeederFilename();

        $filename = basename($filename);
        if ($filename === '' || !in_array($filename, $allowed, true)) {
            return [
                'status' => false,
                'message' => 'File seeder tidak valid atau tidak diizinkan.'
            ];
        }

        $file = $this->getSeederDirectory() . '/' . $filename;
        if (!is_readable($file)) {
            return [
                'status' => false,
                'message' => 'File seeder tidak ditemukan: ' . $filename
            ];
        }

        $sql = file_get_contents($file);
        if ($sql === false || trim($sql) === '') {
            return [
                'status' => false,
                'message' => 'Isi file seeder kosong: ' . $filename
            ];
        }

        try {
            $this->pdo()->exec($sql);

            return [
                'status' => true,
                'message' => 'Seeder berhasil diimport: ' . $filename
            ];
        } catch (\Throwable $e) {
            return [
                'status' => false,
                'message' => 'Import seeder gagal: ' . $e->getMessage()
            ];
        }
    }

    protected function getDashboardStats()
    {
        $avgCompliance = $this->db('mlite_clinical_pathway_compliance')
            ->select('AVG(compliance_percentage) AS avg_compliance')
            ->oneArray();

        return [
            'total_cp' => (int) $this->db('mlite_clinical_pathway')->count(),
            'active_patient' => (int) $this->db('mlite_clinical_pathway_patient')->where('status', 'Aktif')->count(),
            'variance_open' => (int) $this->db('mlite_clinical_pathway_variance')->where('status_tindak_lanjut', 'Open')->count(),
            'avg_compliance' => round((float) ($avgCompliance['avg_compliance'] ?? 0), 2)
        ];
    }

    protected function getRecentGeneratedPatients()
    {
        $sql = "SELECT cpp.no_rawat, cpp.tanggal_mulai, cpp.status, cp.nama_cp, p.nm_pasien
                FROM mlite_clinical_pathway_patient cpp
                INNER JOIN mlite_clinical_pathway cp ON cp.id = cpp.clinical_pathway_id
                INNER JOIN reg_periksa rp ON rp.no_rawat = cpp.no_rawat
                INNER JOIN pasien p ON p.no_rkm_medis = rp.no_rkm_medis
                ORDER BY cpp.id DESC
                LIMIT 10";

        return $this->pdo()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    protected function getMasterList()
    {
        $sql = "SELECT cp.*,
                       COUNT(DISTINCT cpd.kd_penyakit) AS total_diagnosis,
                       COUNT(DISTINCT cpa.id) AS total_activity
                FROM mlite_clinical_pathway cp
                LEFT JOIN mlite_clinical_pathway_diagnosis cpd ON cpd.clinical_pathway_id = cp.id
                LEFT JOIN mlite_clinical_pathway_day cpd2 ON cpd2.clinical_pathway_id = cp.id
                LEFT JOIN mlite_clinical_pathway_activity cpa ON cpa.clinical_pathway_day_id = cpd2.id
                GROUP BY cp.id
                ORDER BY cp.aktif DESC, cp.nama_cp ASC";

        return $this->pdo()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    protected function getMasterRow($id)
    {
        return $this->db('mlite_clinical_pathway')->oneArray('id', (int) $id) ?: [];
    }

    protected function getCpOptions()
    {
        return $this->db('mlite_clinical_pathway')->asc('nama_cp')->toArray();
    }

    protected function getCpptTemplateList()
    {
        $sql = "SELECT t.*, py.nm_penyakit
                FROM mlite_clinical_pathway_cppt_template t
                LEFT JOIN penyakit py ON py.kd_penyakit = t.kd_penyakit
                ORDER BY t.aktif DESC, t.ppra ASC, t.kd_penyakit ASC";

        return $this->pdo()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    protected function getCpptPpraOptions($selected = '')
    {
        $options = [
            'PPRA',
            'Medis',
            'Keperawatan',
            'Farmasi Klinis',
            'Gizi',
            'Rehab Medik',
            'Edukasi'
        ];

        $selected = trim((string) $selected);
        if ($selected !== '' && !in_array($selected, $options, true)) {
            $options[] = $selected;
        }

        sort($options);

        return $options;
    }

    protected function getCpptTemplateRow($id)
    {
        $sql = "SELECT t.*, py.nm_penyakit
                FROM mlite_clinical_pathway_cppt_template t
                LEFT JOIN penyakit py ON py.kd_penyakit = t.kd_penyakit
                WHERE t.id = ?
                LIMIT 1";

        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute([(int) $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    protected function saveCpptTemplate($data)
    {
        $now = date('Y-m-d H:i:s');
        $kdPenyakit = strtoupper(trim((string) ($data['kd_penyakit'] ?? '')));
        $subjective = trim((string) ($data['subjective'] ?? ''));
        $objective = trim((string) ($data['objective'] ?? ''));
        $assessment = trim((string) ($data['assessment'] ?? ''));
        $plan = trim((string) ($data['plan'] ?? ''));
        $ppra = trim((string) ($data['ppra'] ?? ''));
        $aktif = ($data['aktif'] ?? 'Ya') === 'Tidak' ? 'Tidak' : 'Ya';

        if ($kdPenyakit === '' || $ppra === '' || $subjective === '' || $objective === '' || $assessment === '' || $plan === '') {
            return ['status' => false, 'message' => 'Kode ICD, kategori PPRA, S, O, A, dan P wajib diisi.'];
        }

        if (!in_array($ppra, $this->getCpptPpraOptions($ppra), true)) {
            return ['status' => false, 'message' => 'Kategori PPRA tidak valid.'];
        }

        $diagnosis = $this->db('penyakit')->oneArray('kd_penyakit', $kdPenyakit);
        if (!$diagnosis) {
            return ['status' => false, 'message' => 'Kode ICD tidak ditemukan pada master penyakit.'];
        }

        $payload = [
            'kd_penyakit' => $kdPenyakit,
            'ppra' => $ppra,
            'subjective' => $subjective,
            'objective' => $objective,
            'assessment' => $assessment,
            'plan' => $plan,
            'aktif' => $aktif,
            'updated_at' => $now
        ];

        $duplicate = $this->db('mlite_clinical_pathway_cppt_template')
            ->where('kd_penyakit', $kdPenyakit)
            ->oneArray();

        if (!empty($data['id'])) {
            $id = (int) $data['id'];
            if ($duplicate && (int) $duplicate['id'] !== $id) {
                return ['status' => false, 'message' => 'Template CPPT untuk ICD ini sudah ada.'];
            }

            $saved = $this->db('mlite_clinical_pathway_cppt_template')->where('id', $id)->save($payload);
            return [
                'status' => (bool) $saved,
                'message' => $saved ? 'Template CPPT berhasil diperbarui.' : 'Template CPPT gagal diperbarui.'
            ];
        }

        if ($duplicate) {
            return ['status' => false, 'message' => 'Template CPPT untuk ICD ini sudah ada.'];
        }

        $payload['created_at'] = $now;
        $saved = $this->db('mlite_clinical_pathway_cppt_template')->save($payload);

        return [
            'status' => (bool) $saved,
            'message' => $saved ? 'Template CPPT berhasil disimpan.' : 'Template CPPT gagal disimpan.'
        ];
    }

    protected function saveMaster($data)
    {
        $now = date('Y-m-d H:i:s');
        $payload = [
            'kode_cp' => trim($data['kode_cp'] ?? ''),
            'nama_cp' => trim($data['nama_cp'] ?? ''),
            'jenis_layanan' => ($data['jenis_layanan'] ?? 'Ranap') === 'Ralan' ? 'Ralan' : 'Ranap',
            'target_los' => max(0, (int) ($data['target_los'] ?? 0)),
            'target_tarif' => (float) ($data['target_tarif'] ?? 0),
            'confidence_score' => (float) ($data['confidence_score'] ?? 0),
            'evidence_note' => trim($data['evidence_note'] ?? ''),
            'guideline_note' => trim($data['guideline_note'] ?? ''),
            'aktif' => ($data['aktif'] ?? 'Ya') === 'Tidak' ? 'Tidak' : 'Ya',
            'updated_at' => $now
        ];

        if ($payload['kode_cp'] === '' || $payload['nama_cp'] === '') {
            return ['status' => false, 'message' => 'Kode CP dan nama CP wajib diisi.'];
        }

        $duplicate = $this->db('mlite_clinical_pathway')
            ->where('kode_cp', $payload['kode_cp'])
            ->oneArray();

        if (!empty($data['id'])) {
            if ($duplicate && (int) $duplicate['id'] !== (int) $data['id']) {
                return ['status' => false, 'message' => 'Kode CP sudah digunakan.'];
            }

            $saved = $this->db('mlite_clinical_pathway')->where('id', (int) $data['id'])->save($payload);
            return ['status' => (bool) $saved, 'message' => $saved ? 'Master Clinical Pathway berhasil diperbarui.' : 'Master Clinical Pathway gagal diperbarui.'];
        }

        if ($duplicate) {
            return ['status' => false, 'message' => 'Kode CP sudah digunakan.'];
        }

        $payload['created_at'] = $now;
        $saved = $this->db('mlite_clinical_pathway')->save($payload);
        return ['status' => (bool) $saved, 'message' => $saved ? 'Master Clinical Pathway berhasil disimpan.' : 'Master Clinical Pathway gagal disimpan.'];
    }

    protected function getTemplateDays($cpId)
    {
        if (!$cpId) {
            return [];
        }

        $days = $this->db('mlite_clinical_pathway_day')
            ->where('clinical_pathway_id', (int) $cpId)
            ->asc('hari_ke')
            ->toArray();

        foreach ($days as &$day) {
            $day['activities'] = $this->db('mlite_clinical_pathway_activity')
                ->where('clinical_pathway_day_id', $day['id'])
                ->asc('urutan')
                ->asc('id')
                ->toArray();
        }
        unset($day);

        return $days;
    }

    protected function getTemplateDayOptions($cpId)
    {
        if (!$cpId) {
            return [];
        }

        return $this->db('mlite_clinical_pathway_day')
            ->where('clinical_pathway_id', (int) $cpId)
            ->asc('hari_ke')
            ->toArray();
    }

    protected function saveDay($data)
    {
        $payload = [
            'clinical_pathway_id' => (int) ($data['clinical_pathway_id'] ?? 0),
            'hari_ke' => max(1, (int) ($data['hari_ke'] ?? 1)),
            'label_hari' => trim($data['label_hari'] ?? ''),
            'tujuan_harian' => trim($data['tujuan_harian'] ?? '')
        ];

        if (!$payload['clinical_pathway_id']) {
            return ['status' => false, 'message' => 'Pilih master CP terlebih dahulu.'];
        }

        $duplicate = $this->db('mlite_clinical_pathway_day')
            ->where('clinical_pathway_id', $payload['clinical_pathway_id'])
            ->where('hari_ke', $payload['hari_ke'])
            ->oneArray();

        if (!empty($data['id'])) {
            if ($duplicate && (int) $duplicate['id'] !== (int) $data['id']) {
                return ['status' => false, 'message' => 'Hari template sudah ada pada CP ini.'];
            }

            $saved = $this->db('mlite_clinical_pathway_day')->where('id', (int) $data['id'])->save($payload);
            return ['status' => (bool) $saved, 'message' => $saved ? 'Template hari berhasil diperbarui.' : 'Template hari gagal diperbarui.'];
        }

        if ($duplicate) {
            return ['status' => false, 'message' => 'Hari template sudah ada pada CP ini.'];
        }

        if ($payload['label_hari'] === '') {
            $payload['label_hari'] = 'Hari ke-' . $payload['hari_ke'];
        }

        $saved = $this->db('mlite_clinical_pathway_day')->save($payload);
        return ['status' => (bool) $saved, 'message' => $saved ? 'Template hari berhasil disimpan.' : 'Template hari gagal disimpan.'];
    }

    protected function saveActivity($data)
    {
        $payload = [
            'clinical_pathway_day_id' => (int) ($data['clinical_pathway_day_id'] ?? 0),
            'kategori' => in_array($data['kategori'] ?? '', $this->activityCategories, true) ? $data['kategori'] : 'Monitoring',
            'uraian_kegiatan' => trim($data['uraian_kegiatan'] ?? ''),
            'sumber_tabel' => trim($data['sumber_tabel'] ?? ''),
            'item_kode' => trim($data['item_kode'] ?? ''),
            'item_nama' => trim($data['item_nama'] ?? ''),
            'keterangan' => trim($data['keterangan'] ?? ''),
            'evidence_frequency' => max(0, (int) ($data['evidence_frequency'] ?? 0)),
            'evidence_percentage' => round((float) ($data['evidence_percentage'] ?? 0), 2),
            'evidence_status' => in_array($data['evidence_status'] ?? '', ['Wajib', 'Direkomendasikan', 'Opsional'], true) ? $data['evidence_status'] : 'Opsional',
            'wajib' => ($data['wajib'] ?? 'Ya') === 'Tidak' ? 'Tidak' : 'Ya',
            'urutan' => max(0, (int) ($data['urutan'] ?? 0))
        ];

        if (!$payload['clinical_pathway_day_id'] || $payload['item_nama'] === '') {
            return ['status' => false, 'message' => 'Hari template dan nama aktivitas wajib diisi.'];
        }

        if (!empty($data['id'])) {
            $saved = $this->db('mlite_clinical_pathway_activity')->where('id', (int) $data['id'])->save($payload);
            return ['status' => (bool) $saved, 'message' => $saved ? 'Aktivitas template berhasil diperbarui.' : 'Aktivitas template gagal diperbarui.'];
        }

        $saved = $this->db('mlite_clinical_pathway_activity')->save($payload);
        return ['status' => (bool) $saved, 'message' => $saved ? 'Aktivitas template berhasil disimpan.' : 'Aktivitas template gagal disimpan.'];
    }

    protected function getClinicalPathwayIdByDay($dayId)
    {
        $row = $this->db('mlite_clinical_pathway_day')->oneArray('id', (int) $dayId);
        return $row ? (int) $row['clinical_pathway_id'] : 0;
    }

    protected function getClinicalPathwayIdByActivity($activityId)
    {
        $sql = "SELECT d.clinical_pathway_id
                FROM mlite_clinical_pathway_activity a
                INNER JOIN mlite_clinical_pathway_day d ON d.id = a.clinical_pathway_day_id
                WHERE a.id = ?
                LIMIT 1";

        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute([(int) $activityId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? (int) $row['clinical_pathway_id'] : 0;
    }

    protected function extractInsertId($result)
    {
        if (is_numeric($result) && (int) $result > 0) {
            return (int) $result;
        }

        $lastId = (int) $this->pdo()->lastInsertId();
        if ($lastId > 0) {
            return $lastId;
        }

        return 0;
    }

    protected function getDayIdByUniqueKey($cpId, $hariKe)
    {
        $row = $this->db('mlite_clinical_pathway_day')
            ->where('clinical_pathway_id', (int) $cpId)
            ->where('hari_ke', (int) $hariKe)
            ->oneArray();

        return $row ? (int) ($row['id'] ?? 0) : 0;
    }

    protected function getPatientIdByNoRawat($noRawat)
    {
        $row = $this->db('mlite_clinical_pathway_patient')
            ->where('no_rawat', $noRawat)
            ->oneArray();

        return $row ? (int) ($row['id'] ?? 0) : 0;
    }

    protected function getMappingList($cpId)
    {
        if (!$cpId) {
            return [];
        }

        $sql = "SELECT cpd.*, py.nm_penyakit
                FROM mlite_clinical_pathway_diagnosis cpd
                INNER JOIN penyakit py ON py.kd_penyakit = cpd.kd_penyakit
                WHERE cpd.clinical_pathway_id = ?
                ORDER BY cpd.prioritas ASC, cpd.tipe ASC, cpd.kd_penyakit ASC";

        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute([(int) $cpId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    protected function searchDiagnoses($keyword = '', $limit = 50)
    {
        if ($keyword !== '') {
            $stmt = $this->pdo()->prepare("SELECT kd_penyakit, nm_penyakit FROM penyakit WHERE kd_penyakit LIKE ? OR nm_penyakit LIKE ? ORDER BY kd_penyakit ASC LIMIT " . (int) $limit);
            $stmt->execute(['%' . $keyword . '%', '%' . $keyword . '%']);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        return $this->getTopDiagnosisOptions((int) $limit);
    }

    protected function saveMapping($data)
    {
        $payload = [
            'clinical_pathway_id' => (int) ($data['clinical_pathway_id'] ?? 0),
            'kd_penyakit' => trim($data['kd_penyakit'] ?? ''),
            'prioritas' => max(1, (int) ($data['prioritas'] ?? 1)),
            'tipe' => ($data['tipe'] ?? 'Utama') === 'Sekunder' ? 'Sekunder' : 'Utama'
        ];

        if (!$payload['clinical_pathway_id'] || $payload['kd_penyakit'] === '') {
            return ['status' => false, 'message' => 'Pilih master CP dan diagnosis ICD terlebih dahulu.'];
        }

        $duplicate = $this->db('mlite_clinical_pathway_diagnosis')
            ->where('clinical_pathway_id', $payload['clinical_pathway_id'])
            ->where('kd_penyakit', $payload['kd_penyakit'])
            ->where('tipe', $payload['tipe'])
            ->oneArray();

        if ($duplicate) {
            return ['status' => false, 'message' => 'Mapping ICD tersebut sudah terdaftar pada CP ini.'];
        }

        $saved = $this->db('mlite_clinical_pathway_diagnosis')->save($payload);
        return ['status' => (bool) $saved, 'message' => $saved ? 'Mapping ICD berhasil disimpan.' : 'Mapping ICD gagal disimpan.'];
    }

    protected function getTopDiagnosisOptions($limit = 100)
    {
        $sql = "SELECT dp.kd_penyakit, py.nm_penyakit, COUNT(*) AS jumlah
                FROM diagnosa_pasien dp
                INNER JOIN penyakit py ON py.kd_penyakit = dp.kd_penyakit
                INNER JOIN reg_periksa rp ON rp.no_rawat = dp.no_rawat
                WHERE rp.tgl_registrasi >= DATE_SUB(CURDATE(), INTERVAL 3 YEAR)
                GROUP BY dp.kd_penyakit, py.nm_penyakit
                ORDER BY jumlah DESC, dp.kd_penyakit ASC
                LIMIT " . (int) $limit;

        return $this->pdo()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    protected function getEvidenceSummary($icd)
    {
        if ($icd === '') {
            return [];
        }

        return [
            'case_profile' => $this->getCaseProfile($icd),
            'monthly_trend' => $this->getMonthlyTrend($icd),
            'top_labs' => $this->getTopLabs($icd),
            'top_radiology' => $this->getTopRadiology($icd),
            'top_drugs' => $this->getTopDrugs($icd),
            'top_procedures' => $this->getTopProcedures($icd),
            'outcomes' => $this->getOutcomeSummary($icd),
            'evidence_score' => $this->getEvidenceScore($icd)
        ];
    }

    protected function getCaseProfile($icd)
    {
        $sql = "SELECT COUNT(DISTINCT dp.no_rawat) AS total_kasus,
                       ROUND(AVG(IFNULL(DATEDIFF(COALESCE(ki.tgl_keluar, CURDATE()), rp.tgl_registrasi), 0)), 2) AS avg_los,
                       MIN(IFNULL(DATEDIFF(COALESCE(ki.tgl_keluar, CURDATE()), rp.tgl_registrasi), 0)) AS min_los,
                       MAX(IFNULL(DATEDIFF(COALESCE(ki.tgl_keluar, CURDATE()), rp.tgl_registrasi), 0)) AS max_los,
                       SUM(CASE WHEN rp.stts = 'Meninggal' THEN 1 ELSE 0 END) AS meninggal,
                       SUM(CASE WHEN rp.stts = 'Pulang Paksa' THEN 1 ELSE 0 END) AS pulang_paksa
                FROM diagnosa_pasien dp
                INNER JOIN reg_periksa rp ON rp.no_rawat = dp.no_rawat
                LEFT JOIN kamar_inap ki ON ki.no_rawat = rp.no_rawat
                WHERE dp.kd_penyakit = :icd
                  AND rp.tgl_registrasi >= DATE_SUB(CURDATE(), INTERVAL 3 YEAR)";

        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute([':icd' => $icd]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    protected function getMonthlyTrend($icd)
    {
        $sql = "SELECT DATE_FORMAT(rp.tgl_registrasi, '%Y-%m') AS periode,
                       COUNT(DISTINCT dp.no_rawat) AS jumlah
                FROM diagnosa_pasien dp
                INNER JOIN reg_periksa rp ON rp.no_rawat = dp.no_rawat
                WHERE dp.kd_penyakit = :icd
                  AND rp.tgl_registrasi >= DATE_SUB(CURDATE(), INTERVAL 3 YEAR)
                GROUP BY DATE_FORMAT(rp.tgl_registrasi, '%Y-%m')
                ORDER BY periode ASC";

        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute([':icd' => $icd]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    protected function getTopLabs($icd, $limit = 10)
    {
        return $this->getEvidenceCategoryRows(
            $icd,
            "periksa_lab p
             INNER JOIN jns_perawatan_lab j ON j.kd_jenis_prw = p.kd_jenis_prw",
            "p.kd_jenis_prw",
            "j.nm_perawatan",
            (int) $limit
        );
    }

    protected function getTopRadiology($icd, $limit = 10)
    {
        return $this->getEvidenceCategoryRows(
            $icd,
            "periksa_radiologi p
             INNER JOIN jns_perawatan_radiologi j ON j.kd_jenis_prw = p.kd_jenis_prw",
            "p.kd_jenis_prw",
            "j.nm_perawatan",
            (int) $limit
        );
    }

    protected function getTopDrugs($icd, $limit = 10)
    {
        return $this->getEvidenceCategoryRows(
            $icd,
            "resep_obat ro
             INNER JOIN resep_dokter rd ON rd.no_resep = ro.no_resep
             INNER JOIN databarang db ON db.kode_brng = rd.kode_brng",
            "rd.kode_brng",
            "db.nama_brng",
            (int) $limit,
            "ro.no_rawat = dp.no_rawat"
        );
    }

    protected function getTopProcedures($icd, $limit = 10)
    {
        return $this->getEvidenceCategoryRows(
            $icd,
            "prosedur_pasien pp
             INNER JOIN icd9 i9 ON i9.kode = pp.kode",
            "pp.kode",
            "i9.deskripsi_panjang",
            (int) $limit,
            "pp.no_rawat = dp.no_rawat"
        );
    }

    protected function getEvidenceCategoryRows($icd, $fromClause, $codeField, $nameField, $limit = 10, $relation = 'p.no_rawat = dp.no_rawat')
    {
        $totalPatients = max(1, $this->getTotalCasesByDiagnosis($icd));
        $sql = "SELECT {$codeField} AS kode,
                       {$nameField} AS aktivitas,
                       COUNT(*) AS frekuensi,
                       ROUND((COUNT(*) / :total_patients) * 100, 2) AS persentase
                FROM diagnosa_pasien dp
                INNER JOIN {$fromClause} ON {$relation}
                INNER JOIN reg_periksa rp ON rp.no_rawat = dp.no_rawat
                WHERE dp.kd_penyakit = :icd
                  AND rp.tgl_registrasi >= DATE_SUB(CURDATE(), INTERVAL 3 YEAR)
                GROUP BY {$codeField}, {$nameField}
                ORDER BY frekuensi DESC, aktivitas ASC
                LIMIT " . (int) $limit;

        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute([
            ':icd' => $icd,
            ':total_patients' => $totalPatients
        ]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as &$row) {
            $row['status'] = $this->getEvidenceStatus((float) $row['persentase']);
        }
        unset($row);

        return $rows;
    }

    protected function getOutcomeSummary($icd)
    {
        $sql = "SELECT rp.stts AS outcome, COUNT(*) AS jumlah
                FROM diagnosa_pasien dp
                INNER JOIN reg_periksa rp ON rp.no_rawat = dp.no_rawat
                WHERE dp.kd_penyakit = :icd
                  AND rp.tgl_registrasi >= DATE_SUB(CURDATE(), INTERVAL 3 YEAR)
                GROUP BY rp.stts
                ORDER BY jumlah DESC";

        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute([':icd' => $icd]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    protected function getEvidenceScore($icd)
    {
        $rows = [];
        $rows = array_merge($rows, $this->tagEvidenceRows($this->getTopLabs($icd, 5), 'Laboratorium'));
        $rows = array_merge($rows, $this->tagEvidenceRows($this->getTopRadiology($icd, 5), 'Radiologi'));
        $rows = array_merge($rows, $this->tagEvidenceRows($this->getTopDrugs($icd, 5), 'Obat'));
        $rows = array_merge($rows, $this->tagEvidenceRows($this->getTopProcedures($icd, 5), 'Tindakan'));

        usort($rows, function ($a, $b) {
            if ((float) $a['persentase'] === (float) $b['persentase']) {
                return strcmp($a['aktivitas'], $b['aktivitas']);
            }
            return ((float) $a['persentase'] < (float) $b['persentase']) ? 1 : -1;
        });

        return array_slice($rows, 0, 12);
    }

    protected function tagEvidenceRows(array $rows, $kategori)
    {
        foreach ($rows as &$row) {
            $row['aktivitas'] = $kategori . ' - ' . $row['aktivitas'];
            $row['status'] = $this->getEvidenceStatus((float) $row['persentase']);
        }
        unset($row);

        return $rows;
    }

    protected function getTotalCasesByDiagnosis($icd)
    {
        $stmt = $this->pdo()->prepare(
            "SELECT COUNT(DISTINCT dp.no_rawat) AS total_pasien
             FROM diagnosa_pasien dp
             INNER JOIN reg_periksa rp ON rp.no_rawat = dp.no_rawat
             WHERE dp.kd_penyakit = ?
               AND rp.tgl_registrasi >= DATE_SUB(CURDATE(), INTERVAL 3 YEAR)"
        );
        $stmt->execute([$icd]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return (int) ($row['total_pasien'] ?? 0);
    }

    protected function getEvidenceStatus($percentage)
    {
        if ($percentage >= 80) {
            return 'Wajib';
        }

        if ($percentage >= 50) {
            return 'Direkomendasikan';
        }

        return 'Opsional';
    }

    protected function buildTemplatePreview($cpId, $icd)
    {
        if (!$cpId || $icd === '') {
            return [];
        }

        $cp = $this->getMasterRow($cpId);
        if (!$cp) {
            return [];
        }

        $suggested = $this->suggestActivitiesFromEvidence($icd, max(1, (int) ($cp['target_los'] ?? 1)));

        return [
            'cp' => $cp,
            'icd' => $icd,
            'case_profile' => $this->getCaseProfile($icd),
            'activities' => $suggested
        ];
    }

    protected function buildPatientPreview($noRawat)
    {
        if ($noRawat === '') {
            return [];
        }

        $registration = $this->getPatientRegistration($noRawat);
        if (!$registration) {
            return [];
        }

        $diagnoses = $this->getPatientDiagnoses($noRawat, $registration['status_lanjut']);
        $best = $this->findBestClinicalPathwayByDiagnosis($diagnoses, $registration['status_lanjut']);
        $existingCp = $this->getPatientClinicalPathwaySummary($noRawat);
        $careTeam = $this->getCareTeamByNoRawat(
            $noRawat,
            $registration['status_lanjut'],
            $registration['tgl_registrasi'] ?? null,
            $registration['kd_dokter'] ?? null
        );
        $tariffs = $this->getHospitalTariffSummary($noRawat, $registration['status_lanjut']);
        $serviceLocation = $this->getPatientServiceLocation($noRawat, $registration['status_lanjut']);

        return [
            'registration' => $registration,
            'diagnoses' => $diagnoses,
            'matched_cp' => $best,
            'existing_cp' => $existingCp,
            'care_team' => $careTeam,
            'tariffs' => $tariffs,
            'service_location' => $serviceLocation
        ];
    }

    protected function suggestActivitiesFromEvidence($icd, $targetLos)
    {
        $activities = [];
        $dayOne = 1;
        $lastDay = max(1, (int) $targetLos);

        $activities[] = [
            'hari_ke' => 1,
            'label_hari' => 'Hari ke-1',
            'kategori' => 'Assessment',
            'sumber_tabel' => 'reg_periksa',
            'item_kode' => '',
            'item_nama' => 'Assessment awal dan penetapan diagnosis kerja',
            'evidence_frequency' => 0,
            'evidence_percentage' => 100,
            'evidence_status' => 'Wajib',
            'wajib' => 'Ya',
            'urutan' => 1
        ];

        foreach ($this->getTopLabs($icd, 5) as $row) {
            $activities[] = $this->formatSuggestedActivity($row, $dayOne, 'Laboratorium', 'periksa_lab');
        }

        foreach ($this->getTopRadiology($icd, 5) as $row) {
            $activities[] = $this->formatSuggestedActivity($row, $dayOne, 'Radiologi', 'periksa_radiologi');
        }

        foreach ($this->getTopDrugs($icd, 5) as $row) {
            $activities[] = $this->formatSuggestedActivity($row, $dayOne, 'Obat', 'resep_dokter');
        }

        foreach ($this->getTopProcedures($icd, 5) as $row) {
            $activities[] = $this->formatSuggestedActivity($row, min(2, $lastDay), 'Tindakan', 'prosedur_pasien');
        }

        for ($day = 1; $day <= $lastDay; $day++) {
            $activities[] = [
                'hari_ke' => $day,
                'label_hari' => 'Hari ke-' . $day,
                'kategori' => 'Monitoring',
                'sumber_tabel' => 'manual',
                'item_kode' => '',
                'item_nama' => 'Monitoring klinis harian dan evaluasi respon terapi',
                'evidence_frequency' => 0,
                'evidence_percentage' => 100,
                'evidence_status' => 'Wajib',
                'wajib' => 'Ya',
                'urutan' => 90
            ];
        }

        $activities[] = [
            'hari_ke' => $lastDay,
            'label_hari' => 'Hari ke-' . $lastDay,
            'kategori' => 'Edukasi',
            'sumber_tabel' => 'manual',
            'item_kode' => '',
            'item_nama' => 'Edukasi pulang dan rencana kontrol',
            'evidence_frequency' => 0,
            'evidence_percentage' => 100,
            'evidence_status' => 'Wajib',
            'wajib' => 'Ya',
            'urutan' => 95
        ];

        $activities[] = [
            'hari_ke' => $lastDay,
            'label_hari' => 'Hari ke-' . $lastDay,
            'kategori' => 'Outcome',
            'sumber_tabel' => 'reg_periksa',
            'item_kode' => '',
            'item_nama' => 'Evaluasi outcome dan persiapan pulang',
            'evidence_frequency' => 0,
            'evidence_percentage' => 100,
            'evidence_status' => 'Wajib',
            'wajib' => 'Ya',
            'urutan' => 100
        ];

        return $this->deduplicateSuggestedActivities($activities);
    }

    protected function formatSuggestedActivity(array $row, $day, $kategori, $sumberTabel)
    {
        return [
            'hari_ke' => (int) $day,
            'label_hari' => 'Hari ke-' . (int) $day,
            'kategori' => $kategori,
            'sumber_tabel' => $sumberTabel,
            'item_kode' => $row['kode'] ?? '',
            'item_nama' => $row['aktivitas'] ?? '',
            'evidence_frequency' => (int) ($row['frekuensi'] ?? 0),
            'evidence_percentage' => (float) ($row['persentase'] ?? 0),
            'evidence_status' => $row['status'] ?? 'Opsional',
            'wajib' => (($row['status'] ?? 'Opsional') === 'Wajib') ? 'Ya' : 'Tidak',
            'urutan' => 10
        ];
    }

    protected function deduplicateSuggestedActivities(array $activities)
    {
        $unique = [];
        foreach ($activities as $activity) {
            $key = $activity['hari_ke'] . '|' . $activity['kategori'] . '|' . $activity['item_kode'] . '|' . $activity['item_nama'];
            if (!isset($unique[$key])) {
                $unique[$key] = $activity;
            }
        }

        usort($unique, function ($a, $b) {
            if ((int) $a['hari_ke'] === (int) $b['hari_ke']) {
                return ((int) $a['urutan'] <=> (int) $b['urutan']);
            }
            return ((int) $a['hari_ke'] <=> (int) $b['hari_ke']);
        });

        return array_values($unique);
    }

    protected function generateTemplateFromEvidence($cpId, $icd)
    {
        if (!$cpId || $icd === '') {
            return ['status' => false, 'message' => 'Pilih master CP dan diagnosis ICD terlebih dahulu.'];
        }

        $cp = $this->getMasterRow($cpId);
        if (!$cp) {
            return ['status' => false, 'message' => 'Master CP tidak ditemukan.'];
        }

        $activities = $this->suggestActivitiesFromEvidence($icd, max(1, (int) ($cp['target_los'] ?? 1)));
        if (!$activities) {
            return ['status' => false, 'message' => 'Belum ada evidence historis yang cukup untuk membuat template otomatis.'];
        }

        $confidence = $this->calculateConfidenceScore($icd);

        $this->pdo()->beginTransaction();
        try {
            $this->db('mlite_clinical_pathway_day')->where('clinical_pathway_id', $cpId)->delete();

            $days = [];
            foreach ($activities as $activity) {
                if (!isset($days[$activity['hari_ke']])) {
                    $days[$activity['hari_ke']] = $this->extractInsertId($this->db('mlite_clinical_pathway_day')->save([
                        'clinical_pathway_id' => $cpId,
                        'hari_ke' => $activity['hari_ke'],
                        'label_hari' => $activity['label_hari'],
                        'tujuan_harian' => $activity['hari_ke'] === 1 ? 'Stabilisasi awal dan konfirmasi diagnosis' : 'Monitoring dan pencapaian target harian'
                    ]));

                    if (!$days[$activity['hari_ke']]) {
                        $days[$activity['hari_ke']] = $this->getDayIdByUniqueKey($cpId, $activity['hari_ke']);
                    }
                }

                if (!$days[$activity['hari_ke']]) {
                    throw new \RuntimeException('Gagal mendapatkan ID template hari.');
                }

                $this->db('mlite_clinical_pathway_activity')->save([
                    'clinical_pathway_day_id' => $days[$activity['hari_ke']],
                    'kategori' => $activity['kategori'],
                    'uraian_kegiatan' => '',
                    'sumber_tabel' => $activity['sumber_tabel'],
                    'item_kode' => $activity['item_kode'],
                    'item_nama' => $activity['item_nama'],
                    'keterangan' => '',
                    'evidence_frequency' => $activity['evidence_frequency'],
                    'evidence_percentage' => $activity['evidence_percentage'],
                    'evidence_status' => $activity['evidence_status'],
                    'wajib' => $activity['wajib'],
                    'urutan' => $activity['urutan']
                ]);
            }

            $mappingExists = $this->db('mlite_clinical_pathway_diagnosis')
                ->where('clinical_pathway_id', $cpId)
                ->where('kd_penyakit', $icd)
                ->where('tipe', 'Utama')
                ->oneArray();

            if (!$mappingExists) {
                $this->db('mlite_clinical_pathway_diagnosis')->save([
                    'clinical_pathway_id' => $cpId,
                    'kd_penyakit' => $icd,
                    'prioritas' => 1,
                    'tipe' => 'Utama'
                ]);
            }

            $this->db('mlite_clinical_pathway')->where('id', $cpId)->update([
                'confidence_score' => $confidence,
                'evidence_note' => 'Template otomatis dibentuk dari analisis historis 3 tahun terakhir untuk ICD ' . $icd . '.',
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            $this->writeAudit(null, $cpId, 'generate_template', $icd, 'Template harian otomatis dibentuk dari evidence historis.');

            $this->pdo()->commit();
        } catch (\Exception $e) {
            $this->pdo()->rollBack();
            return ['status' => false, 'message' => 'Gagal membuat template otomatis: ' . $e->getMessage()];
        }

        return ['status' => true, 'message' => 'Template harian berhasil digenerate otomatis dari evidence ICD ' . $icd . '.'];
    }

    protected function calculateConfidenceScore($icd)
    {
        $profile = $this->getCaseProfile($icd);
        $totalCases = (int) ($profile['total_kasus'] ?? 0);
        $scores = $this->getEvidenceScore($icd);
        $mandatory = 0;

        foreach ($scores as $row) {
            if (($row['status'] ?? '') === 'Wajib') {
                $mandatory++;
            }
        }

        $caseScore = min(40, $totalCases * 0.2);
        $consistencyScore = min(40, $mandatory * 5);
        $outcomeScore = 20;

        return round(min(99, $caseScore + $consistencyScore + $outcomeScore), 2);
    }

    protected function getPatientRegistration($noRawat)
    {
        $sql = "SELECT rp.no_rawat, rp.no_rkm_medis, rp.tgl_registrasi, rp.jam_reg, rp.status_lanjut, rp.stts,
                       rp.kd_dokter, rp.kd_poli,
                       p.nm_pasien, p.jk, p.tgl_lahir,
                       dokter.nm_dokter,
                       poliklinik.nm_poli,
                       penjab.png_jawab
                FROM reg_periksa rp
                INNER JOIN pasien p ON p.no_rkm_medis = rp.no_rkm_medis
                LEFT JOIN dokter ON dokter.kd_dokter = rp.kd_dokter
                LEFT JOIN poliklinik ON poliklinik.kd_poli = rp.kd_poli
                LEFT JOIN penjab ON penjab.kd_pj = rp.kd_pj
                WHERE rp.no_rawat = ?
                LIMIT 1";

        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute([$noRawat]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        if (!$row) {
            return [];
        }

        $row['umur_display'] = $this->getPatientAgeDisplay($row['tgl_lahir'] ?? '');
        $row['tanggal_registrasi_display'] = $this->formatDateTimeDisplay(
            $row['tgl_registrasi'] ?? '',
            $row['jam_reg'] ?? ''
        );

        return $row;
    }

    protected function getPatientClinicalPathwaySummary($noRawat)
    {
        if ($noRawat === '') {
            return [];
        }

        $sql = "SELECT cpp.id AS clinical_pathway_patient_id,
                       cpp.no_rawat,
                       cpp.tanggal_mulai,
                       cpp.tanggal_selesai,
                       cpp.status,
                       cpp.kd_penyakit,
                       cpp.auto_generated,
                       cp.kode_cp,
                       cp.nama_cp,
                       cp.target_los,
                       cp.target_tarif,
                       cp.confidence_score,
                       IFNULL(cc.compliance_percentage, 0) AS compliance_percentage,
                       IFNULL(cc.kategori_kepatuhan, 'Tidak Patuh') AS kategori_kepatuhan,
                       IFNULL(cc.planned_activity, 0) AS planned_activity,
                       IFNULL(cc.completed_activity, 0) AS completed_activity,
                       IFNULL(cc.missed_activity, 0) AS missed_activity
                FROM mlite_clinical_pathway_patient cpp
                INNER JOIN mlite_clinical_pathway cp ON cp.id = cpp.clinical_pathway_id
                LEFT JOIN mlite_clinical_pathway_compliance cc ON cc.clinical_pathway_patient_id = cpp.id
                WHERE cpp.no_rawat = ?
                LIMIT 1";

        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute([$noRawat]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    protected function getPatientServiceLocation($noRawat, $statusLanjut)
    {
        if ($noRawat === '') {
            return [];
        }

        if ($statusLanjut !== 'Ranap') {
            return [];
        }

        $sql = "SELECT kamar_inap.kd_kamar,
                       kamar_inap.tgl_masuk,
                       kamar_inap.jam_masuk,
                       kamar_inap.stts_pulang,
                       bangsal.nm_bangsal
                FROM kamar_inap
                INNER JOIN kamar ON kamar.kd_kamar = kamar_inap.kd_kamar
                INNER JOIN bangsal ON bangsal.kd_bangsal = kamar.kd_bangsal
                WHERE kamar_inap.no_rawat = ?
                ORDER BY kamar_inap.tgl_masuk DESC, kamar_inap.jam_masuk DESC
                LIMIT 1";

        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute([$noRawat]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    protected function getPatientAgeDisplay($birthDate)
    {
        $birthDate = trim((string) $birthDate);
        if ($birthDate === '' || $birthDate === '0000-00-00') {
            return '-';
        }

        try {
            $birth = new \DateTime($birthDate);
            $today = new \DateTime(date('Y-m-d'));
            $diff = $birth->diff($today);

            if ((int) $diff->y > 0) {
                return $diff->y . ' th';
            }

            if ((int) $diff->m > 0) {
                return $diff->m . ' bln';
            }

            return max(0, (int) $diff->d) . ' hr';
        } catch (\Exception $e) {
            return '-';
        }
    }

    protected function formatDateTimeDisplay($date, $time = '')
    {
        $date = trim((string) $date);
        $time = trim((string) $time);

        if ($date === '' || $date === '0000-00-00') {
            return '-';
        }

        $display = date('d-m-Y', strtotime($date));
        if ($time !== '' && $time !== '00:00:00') {
            $display .= ' ' . substr($time, 0, 5);
        }

        return $display;
    }

    protected function getPatientDiagnoses($noRawat, $statusLanjut)
    {
        $stmt = $this->pdo()->prepare(
            "SELECT dp.kd_penyakit, dp.prioritas, py.nm_penyakit
             FROM diagnosa_pasien dp
             INNER JOIN penyakit py ON py.kd_penyakit = dp.kd_penyakit
             WHERE dp.no_rawat = ?
               AND dp.status = ?
             ORDER BY dp.prioritas ASC, dp.kd_penyakit ASC"
        );
        $stmt->execute([$noRawat, $statusLanjut]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    protected function findBestClinicalPathwayByDiagnosis(array $diagnoses, $statusLanjut)
    {
        if (!$diagnoses) {
            return [];
        }

        $codes = array_values(array_filter(array_column($diagnoses, 'kd_penyakit')));
        if (!$codes) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($codes), '?'));
        $params = $codes;
        $params[] = $statusLanjut;

        $sql = "SELECT cp.*, cpd.kd_penyakit, cpd.prioritas, cpd.tipe
                FROM mlite_clinical_pathway cp
                INNER JOIN mlite_clinical_pathway_diagnosis cpd ON cpd.clinical_pathway_id = cp.id
                WHERE cp.aktif = 'Ya'
                  AND cpd.kd_penyakit IN ($placeholders)
                  AND cp.jenis_layanan = ?
                ORDER BY cpd.prioritas ASC,
                         CASE WHEN cpd.tipe = 'Utama' THEN 0 ELSE 1 END ASC,
                         cp.confidence_score DESC,
                         cp.id ASC
                LIMIT 1";

        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($params);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return $row;
        }

        array_pop($params);
        $sql = "SELECT cp.*, cpd.kd_penyakit, cpd.prioritas, cpd.tipe
                FROM mlite_clinical_pathway cp
                INNER JOIN mlite_clinical_pathway_diagnosis cpd ON cpd.clinical_pathway_id = cp.id
                WHERE cp.aktif = 'Ya'
                  AND cpd.kd_penyakit IN ($placeholders)
                ORDER BY cpd.prioritas ASC,
                         CASE WHEN cpd.tipe = 'Utama' THEN 0 ELSE 1 END ASC,
                         cp.confidence_score DESC,
                         cp.id ASC
                LIMIT 1";
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    protected function generateClinicalPathwayForPatient($noRawat)
    {
        if ($noRawat === '') {
            return ['status' => false, 'message' => 'Nomor rawat wajib diisi.'];
        }

        $registration = $this->getPatientRegistration($noRawat);
        if (!$registration) {
            return ['status' => false, 'message' => 'Nomor rawat tidak ditemukan.'];
        }

        $diagnoses = $this->getPatientDiagnoses($noRawat, $registration['status_lanjut']);
        if (!$diagnoses) {
            return ['status' => false, 'message' => 'Diagnosa pasien belum tersedia, CP otomatis belum dapat dibuat.'];
        }

        $cp = $this->findBestClinicalPathwayByDiagnosis($diagnoses, $registration['status_lanjut']);
        if (!$cp) {
            return ['status' => false, 'message' => 'Belum ada mapping ICD ke master CP yang sesuai untuk pasien ini.'];
        }

        $templateDays = $this->getTemplateDays((int) $cp['id']);
        if (!$templateDays) {
            return ['status' => false, 'message' => 'Template harian untuk CP terpilih masih kosong.'];
        }

        $diagnosisCode = $cp['kd_penyakit'] ?? $diagnoses[0]['kd_penyakit'];
        $startDate = $registration['tgl_registrasi'] ?: date('Y-m-d');
        $startDateTime = $startDate . ' ' . (!empty($registration['jam_reg']) ? $registration['jam_reg'] : '00:00:00');

        $this->pdo()->beginTransaction();
        try {
            $existing = $this->db('mlite_clinical_pathway_patient')->oneArray('no_rawat', $noRawat);

            $payload = [
                'no_rawat' => $noRawat,
                'clinical_pathway_id' => (int) $cp['id'],
                'kd_penyakit' => $diagnosisCode,
                'tanggal_mulai' => $startDateTime,
                'tanggal_selesai' => null,
                'status' => 'Aktif',
                'auto_generated' => 'Ya'
            ];

            if ($existing) {
                $patientId = (int) $existing['id'];
                $this->db('mlite_clinical_pathway_patient')->where('id', $patientId)->save($payload);
                $this->db('mlite_clinical_pathway_execution')->where('clinical_pathway_patient_id', $patientId)->delete();
                $this->db('mlite_clinical_pathway_variance')->where('clinical_pathway_patient_id', $patientId)->delete();
                $this->db('mlite_clinical_pathway_compliance')->where('clinical_pathway_patient_id', $patientId)->delete();
            } else {
                $patientId = $this->extractInsertId($this->db('mlite_clinical_pathway_patient')->save($payload));
                if (!$patientId) {
                    $patientId = $this->getPatientIdByNoRawat($noRawat);
                }
            }

            if (!$patientId) {
                throw new \RuntimeException('Gagal mendapatkan ID pasien Clinical Pathway.');
            }

            foreach ($templateDays as $day) {
                foreach ($day['activities'] as $activity) {
                    $plannedDate = date('Y-m-d', strtotime($startDate . ' +' . max(0, ((int) $day['hari_ke']) - 1) . ' day'));
                    $this->db('mlite_clinical_pathway_execution')->save([
                        'clinical_pathway_patient_id' => $patientId,
                        'clinical_pathway_activity_id' => $activity['id'],
                        'hari_ke' => (int) $day['hari_ke'],
                        'tanggal_rencana' => $plannedDate,
                        'status' => 'Planned',
                        'catatan' => 'Dibuat otomatis dari template CP'
                    ]);
                }
            }

            $this->syncExecutionActualization($patientId, $noRawat, $registration['status_lanjut']);
            $this->calculateCompliance($patientId);
            $this->detectVariance($patientId, $noRawat, (int) $cp['target_los'], $registration['status_lanjut']);
            $this->writeAudit($patientId, (int) $cp['id'], 'generate_patient_cp', $noRawat, 'CP pasien digenerate otomatis berdasarkan mapping ICD.');

            $this->pdo()->commit();
        } catch (\Exception $e) {
            $this->pdo()->rollBack();
            return ['status' => false, 'message' => 'Gagal generate CP pasien: ' . $e->getMessage()];
        }

        return ['status' => true, 'message' => 'CP otomatis untuk pasien ' . $registration['nm_pasien'] . ' berhasil dibuat menggunakan ' . $cp['nama_cp'] . '.'];
    }

    protected function syncExecutionActualization($patientId, $noRawat, $statusLanjut)
    {
        $sql = "SELECT e.id AS execution_id, e.tanggal_rencana, a.*
                FROM mlite_clinical_pathway_execution e
                INNER JOIN mlite_clinical_pathway_activity a ON a.id = e.clinical_pathway_activity_id
                WHERE e.clinical_pathway_patient_id = ?";

        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute([(int) $patientId]);
        $executions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($executions as $execution) {
            $actual = $this->findActualActivityRecord($noRawat, $statusLanjut, $execution);
            $update = [
                'status' => $actual ? 'Completed' : ($execution['tanggal_rencana'] < date('Y-m-d') ? 'Missed' : 'Planned'),
                'tanggal_realisasi' => $actual['tanggal_realisasi'] ?? null,
                'sumber_data' => $actual['sumber_data'] ?? null,
                'sumber_referensi' => $actual['sumber_referensi'] ?? null,
                'catatan' => $actual['catatan'] ?? ($execution['tanggal_rencana'] < date('Y-m-d') ? 'Aktivitas belum ditemukan pada data aktual.' : 'Menunggu realisasi.')
            ];

            $this->db('mlite_clinical_pathway_execution')->where('id', $execution['execution_id'])->save($update);
        }
    }

    protected function findActualActivityRecord($noRawat, $statusLanjut, array $activity)
    {
        $kode = trim($activity['item_kode'] ?? '');
        $kategori = $activity['kategori'] ?? '';

        if ($kategori === 'Assessment') {
            return $this->findAssessmentActual($noRawat, $statusLanjut);
        }

        if ($kategori === 'Monitoring') {
            return $this->findMonitoringActual($noRawat, $statusLanjut);
        }

        if ($kategori === 'Edukasi') {
            return $this->findEducationActual($noRawat, $statusLanjut);
        }

        if ($kategori === 'Nutrisi') {
            return $this->findNutritionActual($noRawat, $statusLanjut);
        }

        if ($kategori === 'Laboratorium' && $kode !== '') {
            return $this->fetchSingleActual(
                "SELECT CONCAT(tgl_periksa, ' ', jam) AS tanggal_realisasi, kd_jenis_prw AS sumber_referensi
                 FROM periksa_lab
                 WHERE no_rawat = ? AND kd_jenis_prw = ?
                 ORDER BY tgl_periksa ASC, jam ASC
                 LIMIT 1",
                [$noRawat, $kode],
                'periksa_lab'
            );
        }

        if ($kategori === 'Radiologi' && $kode !== '') {
            return $this->fetchSingleActual(
                "SELECT CONCAT(tgl_periksa, ' ', jam) AS tanggal_realisasi, kd_jenis_prw AS sumber_referensi
                 FROM periksa_radiologi
                 WHERE no_rawat = ? AND kd_jenis_prw = ?
                 ORDER BY tgl_periksa ASC, jam ASC
                 LIMIT 1",
                [$noRawat, $kode],
                'periksa_radiologi'
            );
        }

        if ($kategori === 'Obat' && $kode !== '') {
            return $this->fetchSingleActual(
                "SELECT CONCAT(ro.tgl_peresepan, ' ', IFNULL(ro.jam_peresepan, ro.jam)) AS tanggal_realisasi, rd.kode_brng AS sumber_referensi
                 FROM resep_obat ro
                 INNER JOIN resep_dokter rd ON rd.no_resep = ro.no_resep
                 WHERE ro.no_rawat = ? AND rd.kode_brng = ?
                 ORDER BY ro.tgl_peresepan ASC, ro.jam_peresepan ASC
                 LIMIT 1",
                [$noRawat, $kode],
                'resep_dokter'
            );
        }

        if ($kategori === 'Tindakan') {
            return $this->findProcedureActual($noRawat, $statusLanjut, $activity);
        }

        if ($kategori === 'Outcome') {
            $row = $this->getPatientRegistration($noRawat);
            if (!empty($row['stts']) && $row['stts'] !== '-') {
                return [
                    'tanggal_realisasi' => date('Y-m-d H:i:s'),
                    'sumber_data' => 'reg_periksa',
                    'sumber_referensi' => $row['stts'],
                    'catatan' => 'Outcome pasien saat ini: ' . $row['stts']
                ];
            }
        }

        return [];
    }

    protected function fetchSingleActual($sql, array $params, $source)
    {
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return [];
        }

        return [
            'tanggal_realisasi' => $row['tanggal_realisasi'] ?? date('Y-m-d H:i:s'),
            'sumber_data' => $source,
            'sumber_referensi' => $row['sumber_referensi'] ?? '',
            'catatan' => 'Aktivitas ditemukan pada data aktual.'
        ];
    }

    protected function getSoapTableByStatus($statusLanjut)
    {
        return $statusLanjut === 'Ranap' ? 'pemeriksaan_ranap' : 'pemeriksaan_ralan';
    }

    protected function findAssessmentActual($noRawat, $statusLanjut)
    {
        $table = $this->getSoapTableByStatus($statusLanjut);

        return $this->fetchSingleActual(
            "SELECT CONCAT(tgl_perawatan, ' ', jam_rawat) AS tanggal_realisasi,
                    CONCAT_WS(' | ',
                        NULLIF(TRIM(keluhan), ''),
                        NULLIF(TRIM(pemeriksaan), ''),
                        NULLIF(TRIM(penilaian), '')
                    ) AS sumber_referensi
             FROM {$table}
             WHERE no_rawat = ?
               AND (
                    NULLIF(TRIM(keluhan), '') IS NOT NULL
                    OR NULLIF(TRIM(pemeriksaan), '') IS NOT NULL
                    OR NULLIF(TRIM(penilaian), '') IS NOT NULL
               )
             ORDER BY tgl_perawatan ASC, jam_rawat ASC
             LIMIT 1",
            [$noRawat],
            $table
        );
    }

    protected function findMonitoringActual($noRawat, $statusLanjut)
    {
        if ($statusLanjut === 'Ranap') {
            $actual = $this->fetchSingleActual(
                "SELECT CONCAT(x.tanggal, ' ', x.jam) AS tanggal_realisasi,
                        x.sumber_referensi
                 FROM (
                    SELECT tgl_perawatan AS tanggal,
                           jam_rawat AS jam,
                           CONCAT('rawat_inap_pr:', kd_jenis_prw) AS sumber_referensi
                    FROM rawat_inap_pr
                    WHERE no_rawat = ?
                    UNION ALL
                    SELECT tgl_perawatan AS tanggal,
                           jam_rawat AS jam,
                           CONCAT('rawat_inap_drpr:', kd_jenis_prw) AS sumber_referensi
                    FROM rawat_inap_drpr
                    WHERE no_rawat = ?
                 ) x
                 ORDER BY x.tanggal ASC, x.jam ASC
                 LIMIT 1",
                [$noRawat, $noRawat],
                'rawat_inap'
            );

            if (!empty($actual)) {
                $actual['catatan'] = 'Monitoring ditemukan dari tindakan rawat inap.';
                return $actual;
            }
        }

        $table = $this->getSoapTableByStatus($statusLanjut);
        $actual = $this->fetchSingleActual(
            "SELECT CONCAT(tgl_perawatan, ' ', jam_rawat) AS tanggal_realisasi,
                    CONCAT_WS(' | ',
                        NULLIF(TRIM(tensi), ''),
                        NULLIF(TRIM(nadi), ''),
                        NULLIF(TRIM(respirasi), ''),
                        NULLIF(TRIM(suhu_tubuh), ''),
                        NULLIF(TRIM(spo2), ''),
                        NULLIF(TRIM(gcs), ''),
                        NULLIF(TRIM(evaluasi), '')
                    ) AS sumber_referensi
             FROM {$table}
             WHERE no_rawat = ?
               AND (
                    NULLIF(TRIM(tensi), '') IS NOT NULL
                    OR NULLIF(TRIM(nadi), '') IS NOT NULL
                    OR NULLIF(TRIM(respirasi), '') IS NOT NULL
                    OR NULLIF(TRIM(suhu_tubuh), '') IS NOT NULL
                    OR NULLIF(TRIM(spo2), '') IS NOT NULL
                    OR NULLIF(TRIM(gcs), '') IS NOT NULL
                    OR NULLIF(TRIM(evaluasi), '') IS NOT NULL
               )
             ORDER BY tgl_perawatan ASC, jam_rawat ASC
             LIMIT 1",
            [$noRawat],
            $table
        );

        if (!empty($actual)) {
            $actual['catatan'] = 'Monitoring ditemukan dari SOAP/CPPT.';
        }

        return $actual;
    }

    protected function findEducationActual($noRawat, $statusLanjut)
    {
        $table = $this->getSoapTableByStatus($statusLanjut);

        $actual = $this->fetchSingleActual(
            "SELECT CONCAT(tgl_perawatan, ' ', jam_rawat) AS tanggal_realisasi,
                    CONCAT_WS(' | ',
                        NULLIF(TRIM(instruksi), ''),
                        NULLIF(TRIM(rtl), ''),
                        NULLIF(TRIM(evaluasi), '')
                    ) AS sumber_referensi
             FROM {$table}
             WHERE no_rawat = ?
               AND (
                    NULLIF(TRIM(instruksi), '') IS NOT NULL
                    OR NULLIF(TRIM(rtl), '') IS NOT NULL
                    OR NULLIF(TRIM(evaluasi), '') IS NOT NULL
               )
             ORDER BY tgl_perawatan ASC, jam_rawat ASC
             LIMIT 1",
            [$noRawat],
            $table
        );

        if (!empty($actual)) {
            $actual['catatan'] = 'Edukasi ditemukan dari SOAP/CPPT.';
        }

        return $actual;
    }

    protected function findNutritionActual($noRawat, $statusLanjut)
    {
        $actual = $this->fetchSingleActual(
            "SELECT tanggal AS tanggal_realisasi,
                    CONCAT_WS(' | ',
                        NULLIF(TRIM(asesmen), ''),
                        NULLIF(TRIM(diagnosis), ''),
                        NULLIF(TRIM(intervensi), ''),
                        NULLIF(TRIM(monitoring), ''),
                        NULLIF(TRIM(evaluasi), ''),
                        NULLIF(TRIM(instruksi), '')
                    ) AS sumber_referensi
             FROM catatan_adime_gizi
             WHERE no_rawat = ?
               AND (
                    NULLIF(TRIM(asesmen), '') IS NOT NULL
                    OR NULLIF(TRIM(diagnosis), '') IS NOT NULL
                    OR NULLIF(TRIM(intervensi), '') IS NOT NULL
                    OR NULLIF(TRIM(monitoring), '') IS NOT NULL
                    OR NULLIF(TRIM(evaluasi), '') IS NOT NULL
                    OR NULLIF(TRIM(instruksi), '') IS NOT NULL
               )
             ORDER BY tanggal ASC
             LIMIT 1",
            [$noRawat],
            'catatan_adime_gizi'
        );

        if (!empty($actual)) {
            $actual['catatan'] = 'Nutrisi ditemukan dari catatan ADIME gizi.';
            return $actual;
        }

        $table = $this->getSoapTableByStatus($statusLanjut);
        $actual = $this->fetchSingleActual(
            "SELECT CONCAT(tgl_perawatan, ' ', jam_rawat) AS tanggal_realisasi,
                    CONCAT_WS(' | ',
                        NULLIF(TRIM(instruksi), ''),
                        NULLIF(TRIM(rtl), ''),
                        NULLIF(TRIM(evaluasi), '')
                    ) AS sumber_referensi
             FROM {$table}
             WHERE no_rawat = ?
               AND (
                    LOWER(COALESCE(instruksi, '')) LIKE '%diet%'
                    OR LOWER(COALESCE(instruksi, '')) LIKE '%gizi%'
                    OR LOWER(COALESCE(instruksi, '')) LIKE '%nutrisi%'
                    OR LOWER(COALESCE(rtl, '')) LIKE '%diet%'
                    OR LOWER(COALESCE(rtl, '')) LIKE '%gizi%'
                    OR LOWER(COALESCE(rtl, '')) LIKE '%nutrisi%'
                    OR LOWER(COALESCE(evaluasi, '')) LIKE '%diet%'
                    OR LOWER(COALESCE(evaluasi, '')) LIKE '%gizi%'
                    OR LOWER(COALESCE(evaluasi, '')) LIKE '%nutrisi%'
               )
             ORDER BY tgl_perawatan ASC, jam_rawat ASC
             LIMIT 1",
            [$noRawat],
            $table
        );

        if (!empty($actual)) {
            $actual['catatan'] = 'Nutrisi ditemukan dari SOAP/CPPT.';
        }

        return $actual;
    }

    protected function findProcedureActual($noRawat, $statusLanjut, array $activity)
    {
        $kode = trim($activity['item_kode'] ?? '');
        $nama = trim($activity['item_nama'] ?? '');

        if ($kode !== '') {
            $actual = $this->fetchSingleActual(
                "SELECT NOW() AS tanggal_realisasi, kode AS sumber_referensi
                 FROM prosedur_pasien
                 WHERE no_rawat = ? AND kode = ? AND status = ?
                 LIMIT 1",
                [$noRawat, $kode, $statusLanjut],
                'prosedur_pasien'
            );

            if (!empty($actual)) {
                $actual['catatan'] = 'Tindakan ditemukan dari prosedur pasien.';
                return $actual;
            }
        }

        if ($statusLanjut === 'Ranap') {
            $actual = $this->findInpatientTreatmentActual($noRawat, $kode, $nama);
            if (!empty($actual)) {
                return $actual;
            }
        }

        return [];
    }

    protected function findInpatientTreatmentActual($noRawat, $kode, $nama)
    {
        if ($kode !== '') {
            $actual = $this->fetchSingleActual(
                "SELECT CONCAT(x.tanggal, ' ', x.jam) AS tanggal_realisasi,
                        x.sumber_referensi
                 FROM (
                    SELECT rip.tgl_perawatan AS tanggal,
                           rip.jam_rawat AS jam,
                           rip.kd_jenis_prw,
                           jpi.nm_perawatan,
                           CONCAT('rawat_inap_pr:', rip.kd_jenis_prw, ' - ', jpi.nm_perawatan) AS sumber_referensi
                    FROM rawat_inap_pr rip
                    INNER JOIN jns_perawatan_inap jpi ON jpi.kd_jenis_prw = rip.kd_jenis_prw
                    WHERE rip.no_rawat = ?
                    UNION ALL
                    SELECT ridp.tgl_perawatan AS tanggal,
                           ridp.jam_rawat AS jam,
                           ridp.kd_jenis_prw,
                           jpi.nm_perawatan,
                           CONCAT('rawat_inap_drpr:', ridp.kd_jenis_prw, ' - ', jpi.nm_perawatan) AS sumber_referensi
                    FROM rawat_inap_drpr ridp
                    INNER JOIN jns_perawatan_inap jpi ON jpi.kd_jenis_prw = ridp.kd_jenis_prw
                    WHERE ridp.no_rawat = ?
                 ) x
                 WHERE x.kd_jenis_prw = ?
                 ORDER BY x.tanggal ASC, x.jam ASC
                 LIMIT 1",
                [$noRawat, $noRawat, $kode],
                'rawat_inap'
            );

            if (!empty($actual)) {
                $actual['catatan'] = 'Tindakan ditemukan dari rawat inap perawat/dokter-perawat berdasarkan kode tindakan.';
                return $actual;
            }
        }

        if ($nama !== '') {
            $namaLike = '%' . strtolower($nama) . '%';
            $actual = $this->fetchSingleActual(
                "SELECT CONCAT(x.tanggal, ' ', x.jam) AS tanggal_realisasi,
                        x.sumber_referensi
                 FROM (
                    SELECT rip.tgl_perawatan AS tanggal,
                           rip.jam_rawat AS jam,
                           rip.kd_jenis_prw,
                           jpi.nm_perawatan,
                           CONCAT('rawat_inap_pr:', rip.kd_jenis_prw, ' - ', jpi.nm_perawatan) AS sumber_referensi
                    FROM rawat_inap_pr rip
                    INNER JOIN jns_perawatan_inap jpi ON jpi.kd_jenis_prw = rip.kd_jenis_prw
                    WHERE rip.no_rawat = ?
                    UNION ALL
                    SELECT ridp.tgl_perawatan AS tanggal,
                           ridp.jam_rawat AS jam,
                           ridp.kd_jenis_prw,
                           jpi.nm_perawatan,
                           CONCAT('rawat_inap_drpr:', ridp.kd_jenis_prw, ' - ', jpi.nm_perawatan) AS sumber_referensi
                    FROM rawat_inap_drpr ridp
                    INNER JOIN jns_perawatan_inap jpi ON jpi.kd_jenis_prw = ridp.kd_jenis_prw
                    WHERE ridp.no_rawat = ?
                 ) x
                 WHERE LOWER(x.nm_perawatan) = LOWER(?)
                    OR LOWER(x.nm_perawatan) LIKE ?
                    OR LOWER(?) LIKE CONCAT('%', LOWER(x.nm_perawatan), '%')
                 ORDER BY x.tanggal ASC, x.jam ASC
                 LIMIT 1",
                [$noRawat, $noRawat, $nama, $namaLike, $nama],
                'rawat_inap'
            );

            if (!empty($actual)) {
                $actual['catatan'] = 'Tindakan ditemukan dari rawat inap perawat/dokter-perawat berdasarkan nama tindakan.';
                return $actual;
            }
        }

        return [];
    }

    protected function calculateCompliance($patientId)
    {
        $summary = $this->db('mlite_clinical_pathway_execution')
            ->select('COUNT(*) AS planned_activity')
            ->select("SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) AS completed_activity")
            ->select("SUM(CASE WHEN status = 'Missed' THEN 1 ELSE 0 END) AS missed_activity")
            ->where('clinical_pathway_patient_id', (int) $patientId)
            ->oneArray();

        $planned = (int) ($summary['planned_activity'] ?? 0);
        $completed = (int) ($summary['completed_activity'] ?? 0);
        $missed = (int) ($summary['missed_activity'] ?? 0);
        $percentage = $planned > 0 ? round(($completed / $planned) * 100, 2) : 0;

        $kategori = 'Tidak Patuh';
        if ($percentage >= 90) {
            $kategori = 'Sangat Patuh';
        } elseif ($percentage >= 75) {
            $kategori = 'Patuh';
        } elseif ($percentage >= 50) {
            $kategori = 'Kurang Patuh';
        }

        $this->db('mlite_clinical_pathway_compliance')
            ->where('clinical_pathway_patient_id', (int) $patientId)
            ->delete();

        $this->db('mlite_clinical_pathway_compliance')->save([
            'clinical_pathway_patient_id' => (int) $patientId,
            'planned_activity' => $planned,
            'completed_activity' => $completed,
            'missed_activity' => $missed,
            'compliance_percentage' => $percentage,
            'kategori_kepatuhan' => $kategori,
            'last_calculated_at' => date('Y-m-d H:i:s')
        ]);
    }

    protected function detectVariance($patientId, $noRawat, $targetLos, $statusLanjut)
    {
        $executions = $this->pdo()->prepare(
            "SELECT e.id, e.status, e.tanggal_rencana, a.kategori, a.item_nama, a.wajib
             FROM mlite_clinical_pathway_execution e
             INNER JOIN mlite_clinical_pathway_activity a ON a.id = e.clinical_pathway_activity_id
             WHERE e.clinical_pathway_patient_id = ?"
        );
        $executions->execute([(int) $patientId]);

        foreach ($executions->fetchAll(PDO::FETCH_ASSOC) as $row) {
            if ($row['wajib'] === 'Ya' && $row['status'] === 'Missed') {
                $this->saveVarianceIfNotExists([
                    'clinical_pathway_patient_id' => (int) $patientId,
                    'clinical_pathway_execution_id' => (int) $row['id'],
                    'kategori_variance' => $this->mapActivityCategoryToVariance($row['kategori']),
                    'penyebab' => 'Aktivitas wajib belum terealisasi',
                    'deskripsi' => $row['item_nama'] . ' belum ditemukan sampai melewati tanggal rencana.',
                    'severity' => 'Sedang',
                    'tanggal_variance' => date('Y-m-d H:i:s'),
                    'status_tindak_lanjut' => 'Open'
                ]);
            }
        }

        if ($targetLos > 0) {
            $registration = $this->getPatientRegistration($noRawat);
            if ($registration) {
                $currentLos = max(0, (int) ((strtotime(date('Y-m-d')) - strtotime($registration['tgl_registrasi'])) / 86400));
                if ($currentLos > $targetLos && (($registration['stts'] ?? '-') === '-' || empty($registration['stts']))) {
                    $this->saveVarianceIfNotExists([
                        'clinical_pathway_patient_id' => (int) $patientId,
                        'clinical_pathway_execution_id' => null,
                        'kategori_variance' => 'LOS',
                        'penyebab' => 'Length of Stay melebihi target CP',
                        'deskripsi' => 'LOS aktual ' . $currentLos . ' hari melebihi target ' . $targetLos . ' hari.',
                        'severity' => 'Tinggi',
                        'tanggal_variance' => date('Y-m-d H:i:s'),
                        'status_tindak_lanjut' => 'Open'
                    ]);
                }
            }
        }

        $primaryDiagnosis = $this->db('diagnosa_pasien')
            ->where('no_rawat', $noRawat)
            ->where('status', $statusLanjut)
            ->where('prioritas', 1)
            ->oneArray();

        $patient = $this->db('mlite_clinical_pathway_patient')->oneArray('id', (int) $patientId);
        if ($patient && $primaryDiagnosis && $primaryDiagnosis['kd_penyakit'] !== $patient['kd_penyakit']) {
            $this->saveVarianceIfNotExists([
                'clinical_pathway_patient_id' => (int) $patientId,
                'clinical_pathway_execution_id' => null,
                'kategori_variance' => 'Diagnosis',
                'penyebab' => 'Diagnosis utama berubah',
                'deskripsi' => 'Diagnosis utama saat ini ' . $primaryDiagnosis['kd_penyakit'] . ' berbeda dengan mapping CP ' . $patient['kd_penyakit'] . '.',
                'severity' => 'Sedang',
                'tanggal_variance' => date('Y-m-d H:i:s'),
                'status_tindak_lanjut' => 'Open'
            ]);
        }
    }

    protected function saveVarianceIfNotExists(array $payload)
    {
        $query = $this->db('mlite_clinical_pathway_variance')
            ->where('clinical_pathway_patient_id', (int) $payload['clinical_pathway_patient_id'])
            ->where('kategori_variance', $payload['kategori_variance'])
            ->where('penyebab', $payload['penyebab'])
            ->where('deskripsi', $payload['deskripsi']);

        if (isset($payload['clinical_pathway_execution_id']) && $payload['clinical_pathway_execution_id'] !== null) {
            $query = $query->where('clinical_pathway_execution_id', (int) $payload['clinical_pathway_execution_id']);
        } else {
            $query = $query->where('clinical_pathway_execution_id', null);
        }

        $existing = $query->oneArray();
        if (!$existing) {
            $this->db('mlite_clinical_pathway_variance')->save($payload);
        }
    }

    protected function refreshPatientActualization($patientId)
    {
        if ($patientId <= 0) {
            return ['status' => false, 'message' => 'ID pasien Clinical Pathway tidak valid.'];
        }

        $patient = $this->db('mlite_clinical_pathway_patient')->oneArray('id', $patientId);
        if (!$patient) {
            return ['status' => false, 'message' => 'Data pasien Clinical Pathway tidak ditemukan.'];
        }

        $registration = $this->getPatientRegistration($patient['no_rawat']);
        if (!$registration) {
            return ['status' => false, 'message' => 'Registrasi pasien tidak ditemukan untuk refresh realisasi.'];
        }

        $cp = $this->db('mlite_clinical_pathway')->oneArray('id', (int) $patient['clinical_pathway_id']);
        $targetLos = (int) ($cp['target_los'] ?? 0);

        $this->pdo()->beginTransaction();
        try {
            $this->syncExecutionActualization($patientId, $patient['no_rawat'], $registration['status_lanjut']);
            $this->calculateCompliance($patientId);
            $this->db('mlite_clinical_pathway_variance')
                ->where('clinical_pathway_patient_id', $patientId)
                ->delete();
            $this->detectVariance($patientId, $patient['no_rawat'], $targetLos, $registration['status_lanjut']);
            $this->writeAudit(
                $patientId,
                (int) ($patient['clinical_pathway_id'] ?? 0),
                'refresh_actualization',
                $patient['no_rawat'],
                'Realisasi aktivitas, compliance, dan variance disegarkan ulang dari data operasional.'
            );
            $this->pdo()->commit();
        } catch (\Exception $e) {
            $this->pdo()->rollBack();
            return ['status' => false, 'message' => 'Gagal refresh realisasi: ' . $e->getMessage()];
        }

        return ['status' => true, 'message' => 'Refresh realisasi berhasil untuk no. rawat ' . $patient['no_rawat'] . '.'];
    }

    protected function updatePatientClinicalPathwayStatus($patientId, $status)
    {
        $allowedStatuses = ['Selesai', 'Drop'];
        if ($patientId <= 0) {
            return ['status' => false, 'message' => 'ID pasien Clinical Pathway tidak valid.'];
        }

        if (!in_array($status, $allowedStatuses, true)) {
            return ['status' => false, 'message' => 'Status yang dipilih tidak valid.'];
        }

        $patient = $this->db('mlite_clinical_pathway_patient')->oneArray('id', $patientId);
        if (!$patient) {
            return ['status' => false, 'message' => 'Data pasien Clinical Pathway tidak ditemukan.'];
        }

        if (($patient['status'] ?? '') === $status) {
            return ['status' => true, 'message' => 'Status pasien sudah ' . $status . '.'];
        }

        $saved = $this->db('mlite_clinical_pathway_patient')
            ->where('id', $patientId)
            ->save([
                'status' => $status,
                'tanggal_selesai' => date('Y-m-d H:i:s')
            ]);

        if (!$saved) {
            return ['status' => false, 'message' => 'Gagal mengubah status pasien Clinical Pathway.'];
        }

        $this->writeAudit(
            $patientId,
            (int) ($patient['clinical_pathway_id'] ?? 0),
            'set_status_' . strtolower($status),
            $patient['no_rawat'] ?? '',
            'Status Clinical Pathway pasien diubah manual menjadi ' . $status . '.'
        );

        return ['status' => true, 'message' => 'Status Clinical Pathway berhasil diubah menjadi ' . $status . '.'];
    }

    protected function mapActivityCategoryToVariance($category)
    {
        $map = [
            'Laboratorium' => 'Lab',
            'Radiologi' => 'Radiologi',
            'Obat' => 'Obat',
            'Tindakan' => 'Tindakan',
            'Nutrisi' => 'Nutrisi',
            'Edukasi' => 'Edukasi',
            'Outcome' => 'Outcome'
        ];

        return $map[$category] ?? 'Administrasi';
    }

    protected function writeAudit($patientId, $cpId, $action, $reference, $description)
    {
        $this->db('mlite_clinical_pathway_audit')->save([
            'clinical_pathway_patient_id' => $patientId ?: null,
            'clinical_pathway_id' => $cpId ?: null,
            'aksi' => $action,
            'referensi' => $reference,
            'deskripsi' => $description,
            'user_aksi' => $_SESSION['username'] ?? 'system',
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    protected function getMonitoringList()
    {
        $sql = "SELECT cpp.id,
                       cpp.no_rawat,
                       cpp.status,
                       cpp.tanggal_mulai,
                       cpp.tanggal_selesai,
                       cp.nama_cp,
                       cp.target_los,
                       cpp.kd_penyakit,
                       p.nm_pasien,
                       rp.tgl_registrasi,
                       rp.status_lanjut,
                       IFNULL(cc.compliance_percentage, 0) AS compliance_percentage,
                       IFNULL(cc.kategori_kepatuhan, 'Tidak Patuh') AS kategori_kepatuhan
                FROM mlite_clinical_pathway_patient cpp
                INNER JOIN mlite_clinical_pathway cp ON cp.id = cpp.clinical_pathway_id
                INNER JOIN reg_periksa rp ON rp.no_rawat = cpp.no_rawat
                INNER JOIN pasien p ON p.no_rkm_medis = rp.no_rkm_medis
                LEFT JOIN mlite_clinical_pathway_compliance cc ON cc.clinical_pathway_patient_id = cpp.id
                ORDER BY cpp.tanggal_mulai DESC, cpp.no_rawat DESC";

        return $this->pdo()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    protected function getPrintableCpByNoRawat($noRawat)
    {
        if ($noRawat === '') {
            return [];
        }

        $sql = "SELECT cpp.id AS clinical_pathway_patient_id,
                       cpp.no_rawat,
                       cpp.tanggal_mulai,
                       cpp.tanggal_selesai,
                       cpp.status,
                       cpp.kd_penyakit,
                       cp.kode_cp,
                       cp.nama_cp,
                       cp.jenis_layanan,
                       cp.target_los,
                       cp.target_tarif,
                       cp.confidence_score,
                       p.no_rkm_medis,
                       p.nm_pasien,
                       p.jk,
                       p.tgl_lahir,
                       rp.tgl_registrasi,
                       rp.kd_dokter,
                       rp.status_lanjut,
                       IFNULL(cc.compliance_percentage, 0) AS compliance_percentage,
                       IFNULL(cc.kategori_kepatuhan, 'Tidak Patuh') AS kategori_kepatuhan,
                       IFNULL(cc.planned_activity, 0) AS planned_activity,
                       IFNULL(cc.completed_activity, 0) AS completed_activity,
                       IFNULL(cc.missed_activity, 0) AS missed_activity
                FROM mlite_clinical_pathway_patient cpp
                INNER JOIN mlite_clinical_pathway cp ON cp.id = cpp.clinical_pathway_id
                INNER JOIN reg_periksa rp ON rp.no_rawat = cpp.no_rawat
                INNER JOIN pasien p ON p.no_rkm_medis = rp.no_rkm_medis
                LEFT JOIN mlite_clinical_pathway_compliance cc ON cc.clinical_pathway_patient_id = cpp.id
                WHERE cpp.no_rawat = ?
                LIMIT 1";

        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute([$noRawat]);
        $header = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$header) {
            return [];
        }

        $diagnoses = $this->getPatientDiagnoses($noRawat, $header['status_lanjut']);

        return [
            'header' => $header,
            'hospital' => $this->getHospitalProfile(),
            'tariffs' => $this->getHospitalTariffSummary($noRawat, $header['status_lanjut']),
            'care_team' => $this->getCareTeamByNoRawat($noRawat, $header['status_lanjut'], $header['tgl_registrasi'], $header['kd_dokter'] ?? null),
            'diagnoses' => $diagnoses,
            'cppt_template' => $this->getPrintableCpptTemplate($diagnoses, $header['kd_penyakit'] ?? ''),
            'days' => $this->getPrintableExecutionDays((int) $header['clinical_pathway_patient_id'], (int) $header['target_los']),
            'matrix' => $this->getPrintableMatrixRows((int) $header['clinical_pathway_patient_id'], (int) $header['target_los']),
            'variances' => $this->getPrintableVariances((int) $header['clinical_pathway_patient_id']),
            'verification_user' => $_SESSION['fullname'] ?? ($_SESSION['username'] ?? ''),
            'pdf_url' => url([ADMIN, 'clinical_pathway', 'pdfcp']) . '&no_rawat=' . urlencode($noRawat)
        ];
    }

    protected function getPrintableCpptTemplate(array $diagnoses, $fallbackCode = '')
    {
        $codes = [];
        foreach ($diagnoses as $diagnosis) {
            $code = strtoupper(trim((string) ($diagnosis['kd_penyakit'] ?? '')));
            if ($code !== '' && !in_array($code, $codes, true)) {
                $codes[] = $code;
            }
        }

        $fallbackCode = strtoupper(trim((string) $fallbackCode));
        if ($fallbackCode !== '' && !in_array($fallbackCode, $codes, true)) {
            $codes[] = $fallbackCode;
        }

        if (!$codes) {
            return [];
        }

        foreach ($codes as $code) {
            $row = $this->getCpptTemplateByDiagnosisCode($code);
            if ($row) {
                return $row;
            }
        }

        return [];
    }

    protected function getCpptTemplateByDiagnosisCode($kdPenyakit)
    {
        $sql = "SELECT t.*, py.nm_penyakit
                FROM mlite_clinical_pathway_cppt_template t
                LEFT JOIN penyakit py ON py.kd_penyakit = t.kd_penyakit
                WHERE t.kd_penyakit = ?
                  AND t.aktif = 'Ya'
                LIMIT 1";

        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute([strtoupper(trim((string) $kdPenyakit))]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    protected function getHospitalProfile()
    {
        $logo = trim((string) $this->settings('settings.logo'));
        $logoUrl = '';

        if ($logo !== '') {
            $logoUrl = url() . '/' . ltrim($logo, '/');
        }

        return [
            'logo' => $logo,
            'logo_url' => $logoUrl,
            'nama_instansi' => $this->settings('settings.nama_instansi'),
            'alamat' => $this->settings('settings.alamat'),
            'kota' => $this->settings('settings.kota'),
            'propinsi' => $this->settings('settings.propinsi'),
            'nomor_telepon' => $this->settings('settings.nomor_telepon'),
            'email' => $this->settings('settings.email')
        ];
    }

    protected function sumValueByQuery($sql, array $params = [])
    {
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($params);
        return (float) $stmt->fetchColumn();
    }

    protected function getHospitalTariffSummary($noRawat, $statusLanjut)
    {
        $components = [];

        $components['registrasi'] = $this->sumValueByQuery(
            "SELECT COALESCE(SUM(biaya_reg), 0) FROM reg_periksa WHERE no_rawat = ?",
            [$noRawat]
        );

        $components['rawat_jalan'] =
            $this->sumValueByQuery("SELECT COALESCE(SUM(biaya_rawat), 0) FROM rawat_jl_dr WHERE no_rawat = ?", [$noRawat]) +
            $this->sumValueByQuery("SELECT COALESCE(SUM(biaya_rawat), 0) FROM rawat_jl_pr WHERE no_rawat = ?", [$noRawat]) +
            $this->sumValueByQuery("SELECT COALESCE(SUM(biaya_rawat), 0) FROM rawat_jl_drpr WHERE no_rawat = ?", [$noRawat]);

        $components['rawat_inap'] =
            $this->sumValueByQuery("SELECT COALESCE(SUM(biaya_rawat), 0) FROM rawat_inap_dr WHERE no_rawat = ?", [$noRawat]) +
            $this->sumValueByQuery("SELECT COALESCE(SUM(biaya_rawat), 0) FROM rawat_inap_pr WHERE no_rawat = ?", [$noRawat]) +
            $this->sumValueByQuery("SELECT COALESCE(SUM(biaya_rawat), 0) FROM rawat_inap_drpr WHERE no_rawat = ?", [$noRawat]);

        $components['laboratorium'] = $this->sumValueByQuery(
            "SELECT COALESCE(SUM(biaya), 0) FROM periksa_lab WHERE no_rawat = ?",
            [$noRawat]
        );

        $components['radiologi'] = $this->sumValueByQuery(
            "SELECT COALESCE(SUM(biaya), 0) FROM periksa_radiologi WHERE no_rawat = ?",
            [$noRawat]
        );

        $components['obat'] = $this->sumValueByQuery(
            "SELECT COALESCE(SUM(total), 0) FROM detail_pemberian_obat WHERE no_rawat = ?",
            [$noRawat]
        );

        $components['obat_operasi'] = $this->sumValueByQuery(
            "SELECT COALESCE(SUM(hargasatuan * jumlah), 0) FROM beri_obat_operasi WHERE no_rawat = ?",
            [$noRawat]
        );

        $components['kamar'] = $statusLanjut === 'Ranap'
            ? $this->sumValueByQuery(
                "SELECT COALESCE(SUM(ttl_biaya), 0) FROM kamar_inap WHERE no_rawat = ?",
                [$noRawat]
            )
            : 0.0;

        $components['operasi'] = $this->sumValueByQuery(
            "SELECT COALESCE(SUM(
                COALESCE(biayaoperator1, 0) +
                COALESCE(biayaoperator2, 0) +
                COALESCE(biayaoperator3, 0) +
                COALESCE(biayaasisten_operator1, 0) +
                COALESCE(biayaasisten_operator2, 0) +
                COALESCE(biayaasisten_operator3, 0) +
                COALESCE(biayainstrumen, 0) +
                COALESCE(biayadokter_anak, 0) +
                COALESCE(biayaperawaat_resusitas, 0) +
                COALESCE(biayadokter_anestesi, 0) +
                COALESCE(biayaasisten_anestesi, 0) +
                COALESCE(biayaasisten_anestesi2, 0) +
                COALESCE(biayabidan, 0) +
                COALESCE(biayabidan2, 0) +
                COALESCE(biayabidan3, 0) +
                COALESCE(biayaperawat_luar, 0) +
                COALESCE(biayaalat, 0) +
                COALESCE(biayasewaok, 0) +
                COALESCE(akomodasi, 0) +
                COALESCE(bagian_rs, 0) +
                COALESCE(biaya_omloop, 0) +
                COALESCE(biaya_omloop2, 0) +
                COALESCE(biaya_omloop3, 0) +
                COALESCE(biaya_omloop4, 0) +
                COALESCE(biaya_omloop5, 0) +
                COALESCE(biayasarpras, 0) +
                COALESCE(biaya_dokter_pjanak, 0) +
                COALESCE(biaya_dokter_umum, 0)
            ), 0)
            FROM operasi
            WHERE no_rawat = ?",
            [$noRawat]
        );

        $rsTotal = array_sum($components);

        return [
            'components' => $components,
            'rs_total' => $rsTotal,
            'inacbg_total' => null
        ];
    }

    protected function getCareTeamByNoRawat($noRawat, $statusLanjut, $tglRegistrasi = null, $kdDokter = null)
    {
        $team = [
            'dokter' => '',
            'perawat' => '',
            'tanggal_ttd' => $tglRegistrasi ?: date('Y-m-d')
        ];

        if ($kdDokter) {
            $dokter = $this->db('dokter')->oneArray('kd_dokter', $kdDokter);
            if ($dokter) {
                $team['dokter'] = $dokter['nm_dokter'] ?? '';
            }
        }

        if ($statusLanjut === 'Ranap') {
            $sql = "SELECT petugas.nama
                    FROM (
                        SELECT nip, tgl_perawatan AS tanggal, jam_rawat AS jam
                        FROM rawat_inap_pr
                        WHERE no_rawat = ?
                        UNION ALL
                        SELECT nip, tgl_perawatan AS tanggal, jam_rawat AS jam
                        FROM rawat_inap_drpr
                        WHERE no_rawat = ?
                    ) x
                    INNER JOIN petugas ON petugas.nip = x.nip
                    ORDER BY x.tanggal DESC, x.jam DESC
                    LIMIT 1";
            $params = [$noRawat, $noRawat];
        } else {
            $sql = "SELECT petugas.nama
                    FROM (
                        SELECT nip, tgl_perawatan AS tanggal, jam_rawat AS jam
                        FROM rawat_jl_pr
                        WHERE no_rawat = ?
                        UNION ALL
                        SELECT nip, tgl_perawatan AS tanggal, jam_rawat AS jam
                        FROM rawat_jl_drpr
                        WHERE no_rawat = ?
                    ) x
                    INNER JOIN petugas ON petugas.nip = x.nip
                    ORDER BY x.tanggal DESC, x.jam DESC
                    LIMIT 1";
            $params = [$noRawat, $noRawat];
        }

        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($params);
        $perawat = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($perawat) {
            $team['perawat'] = $perawat['nama'] ?? '';
        }

        return $team;
    }

    protected function getPrintableExecutionDays($patientId, $targetLos = 0)
    {
        $sql = "SELECT d.hari_ke,
                       d.label_hari,
                       d.tujuan_harian,
                       a.kategori,
                       a.uraian_kegiatan,
                       a.item_nama,
                       a.keterangan,
                       a.evidence_percentage,
                       a.evidence_status,
                       a.wajib,
                       e.tanggal_rencana,
                       e.tanggal_realisasi,
                       e.status AS status_eksekusi,
                       e.catatan
                FROM mlite_clinical_pathway_execution e
                INNER JOIN mlite_clinical_pathway_activity a ON a.id = e.clinical_pathway_activity_id
                INNER JOIN mlite_clinical_pathway_day d ON d.id = a.clinical_pathway_day_id
                WHERE e.clinical_pathway_patient_id = ?
                ORDER BY d.hari_ke ASC, a.urutan ASC, a.id ASC";

        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute([(int) $patientId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $days = [];
        foreach ($rows as $row) {
            $hariKe = (int) $row['hari_ke'];
            if (!isset($days[$hariKe])) {
                $days[$hariKe] = [
                    'hari_ke' => $hariKe,
                    'label_hari' => $row['label_hari'],
                    'tujuan_harian' => $row['tujuan_harian'],
                    'activities' => []
                ];
            }

            $days[$hariKe]['activities'][] = $row;
        }

        if (!$days && $targetLos > 0) {
            for ($i = 1; $i <= $targetLos; $i++) {
                $days[$i] = [
                    'hari_ke' => $i,
                    'label_hari' => 'Hari ke-' . $i,
                    'tujuan_harian' => '',
                    'activities' => []
                ];
            }
        }

        return array_values($days);
    }

    protected function getPrintableMatrixRows($patientId, $targetLos = 0)
    {
        $sql = "SELECT d.hari_ke,
                       a.kategori,
                       a.uraian_kegiatan,
                       a.sumber_tabel,
                       a.item_kode,
                       a.item_nama,
                       a.keterangan,
                       a.urutan,
                       a.wajib,
                       e.status AS status_eksekusi,
                       e.tanggal_rencana,
                       e.tanggal_realisasi,
                       e.catatan
                FROM mlite_clinical_pathway_execution e
                INNER JOIN mlite_clinical_pathway_activity a ON a.id = e.clinical_pathway_activity_id
                INNER JOIN mlite_clinical_pathway_day d ON d.id = a.clinical_pathway_day_id
                WHERE e.clinical_pathway_patient_id = ?
                ORDER BY a.urutan ASC, a.kategori ASC, a.sumber_tabel ASC, a.item_nama ASC, d.hari_ke ASC, a.id ASC";

        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute([(int) $patientId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $maxDay = max(1, (int) $targetLos);
        foreach ($rows as $row) {
            $maxDay = max($maxDay, (int) ($row['hari_ke'] ?? 0));
        }

        $grouped = [];
        $defaultSources = [
            'reg_periksa',
            'periksa_lab',
            'periksa_radiologi',
            'resep_dokter',
            'resep_obat',
            'prosedur_pasien',
            'rawat_inap_pr',
            'rawat_inap_drpr',
            'rawat_inap',
            'manual',
            'catatan_adime_gizi',
            'pemeriksaan_ranap',
            'pemeriksaan_ralan'
        ];
        foreach ($rows as $row) {
            $rawSource = trim((string) ($row['sumber_tabel'] ?? ''));
            $section = trim((string) ($row['kategori'] ?? ''));
            $group = trim((string) ($row['uraian_kegiatan'] ?? ''));
            if ($group === '' && $rawSource !== '' && !in_array($rawSource, $defaultSources, true)) {
                $section = $rawSource;
                $group = trim((string) ($row['item_kode'] ?? ''));
            }
            $activity = trim((string) ($row['item_nama'] ?? ''));
            $note = trim((string) ($row['keterangan'] ?? ''));
            $key = implode('|', [$section, $group, $activity]);

            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'section' => $section,
                    'group' => $group,
                    'activity' => $activity,
                    'note' => $note,
                    'urutan' => (int) ($row['urutan'] ?? 0),
                    'days' => []
                ];

                for ($i = 1; $i <= $maxDay; $i++) {
                    $grouped[$key]['days'][$i] = [
                        'active' => false,
                        'status' => '',
                        'tanggal_realisasi' => ''
                    ];
                }
            }

            $hariKe = (int) ($row['hari_ke'] ?? 0);
            if ($hariKe > 0 && $hariKe <= $maxDay) {
                $grouped[$key]['days'][$hariKe] = [
                    'active' => true,
                    'status' => $row['status_eksekusi'] ?? '',
                    'tanggal_realisasi' => $row['tanggal_realisasi'] ?? ''
                ];
            }

            if ($grouped[$key]['note'] === '') {
                $catatan = trim((string) ($row['catatan'] ?? ''));
                if ($catatan !== '' && $catatan !== 'Dibuat otomatis dari template CP') {
                    $grouped[$key]['note'] = $catatan;
                }
            }
        }

        $sectionConfig = $this->getPrintableSectionConfig();
        $matrixRows = array_values($grouped);
        usort($matrixRows, function ($a, $b) use ($sectionConfig) {
            $aSectionOrder = $sectionConfig[$a['section']]['order'] ?? 999;
            $bSectionOrder = $sectionConfig[$b['section']]['order'] ?? 999;

            if ($aSectionOrder !== $bSectionOrder) {
                return $aSectionOrder <=> $bSectionOrder;
            }

            $aGroup = $this->getPrintableGroupLabel($a['section'], $a['group']);
            $bGroup = $this->getPrintableGroupLabel($b['section'], $b['group']);

            if ($aGroup !== $bGroup) {
                return strcmp($aGroup, $bGroup);
            }

            if ((int) $a['urutan'] === (int) $b['urutan']) {
                $aKey = $a['section'] . '|' . $aGroup . '|' . $a['activity'];
                $bKey = $b['section'] . '|' . $bGroup . '|' . $b['activity'];
                return strcmp($aKey, $bKey);
            }
            return ((int) $a['urutan'] <=> (int) $b['urutan']);
        });

        $prepared = [];
        $count = count($matrixRows);
        for ($i = 0; $i < $count; $i++) {
            $current = $matrixRows[$i];
            $prev = $i > 0 ? $matrixRows[$i - 1] : null;

            $current['section_display'] = $this->normalizePrintableLabel($sectionConfig[$current['section']]['label'] ?? strtoupper($current['section']));
            $current['group_display'] = $this->getPrintableGroupLabel($current['section'], $current['group']);
            $current['show_section'] = !$prev || $prev['section'] !== $current['section'];
            $current['section_rowspan'] = 1;

            if ($current['show_section']) {
                $sectionRowspan = 0;
                for ($j = $i; $j < $count; $j++) {
                    if ($matrixRows[$j]['section'] !== $current['section']) {
                        break;
                    }
                    $sectionRowspan++;
                }
                $current['section_rowspan'] = max(1, $sectionRowspan);
            }

            $prepared[] = $current;
        }

        return [
            'days' => range(1, $maxDay),
            'rows' => $prepared
        ];
    }

    protected function getPrintableVariances($patientId)
    {
        return $this->db('mlite_clinical_pathway_variance')
            ->where('clinical_pathway_patient_id', (int) $patientId)
            ->desc('tanggal_variance')
            ->toArray();
    }

    protected function getVarianceList()
    {
        $sql = "SELECT cv.id,
                       cv.kategori_variance,
                       cv.penyebab,
                       cv.deskripsi,
                       cv.severity,
                       cv.status_tindak_lanjut,
                       cv.tanggal_variance,
                       cpp.no_rawat,
                       cp.nama_cp,
                       p.nm_pasien
                FROM mlite_clinical_pathway_variance cv
                INNER JOIN mlite_clinical_pathway_patient cpp ON cpp.id = cv.clinical_pathway_patient_id
                INNER JOIN mlite_clinical_pathway cp ON cp.id = cpp.clinical_pathway_id
                INNER JOIN reg_periksa rp ON rp.no_rawat = cpp.no_rawat
                INNER JOIN pasien p ON p.no_rkm_medis = rp.no_rkm_medis
                ORDER BY cv.tanggal_variance DESC, cv.id DESC";

        return $this->pdo()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    private function _addHeaderFiles()
    {
        $this->core->addCSS(url('assets/css/dataTables.bootstrap.min.css'));
        $this->core->addCSS(url([ADMIN, 'clinical_pathway', 'css']));
        $this->core->addJS(url('assets/jscripts/jquery.dataTables.min.js'));
        $this->core->addJS(url('assets/jscripts/dataTables.bootstrap.min.js'));
        $this->core->addJS(url([ADMIN, 'clinical_pathway', 'javascript']), 'footer');
    }
}
