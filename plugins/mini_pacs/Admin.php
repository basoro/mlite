<?php

namespace Plugins\Mini_pacs;

use Systems\AdminModule;

class Admin extends AdminModule
{

    protected array $assign = [];
    private $fhirurl;
    private $authurl;
    private $clientid;
    private $secretkey;
    private $organizationid;

    public function init()
    {
        $this->fhirurl = $this->core->getSettings('satu_sehat', 'fhirurl');
        $this->authurl = $this->core->getSettings('satu_sehat', 'authurl');
        $this->clientid = $this->core->getSettings('satu_sehat', 'clientid');
        $this->secretkey = $this->core->getSettings('satu_sehat', 'secretkey');
        $this->organizationid = $this->core->getSettings('satu_sehat', 'organizationid');
    }

    public function navigation()
    {
        return [
            'Manage PACS' => 'manage',
            'Radiology Worklist' => 'worklist',
            'Pengaturan' => 'settings'
        ];
    }

    public function getMain()
    {
        if ($res = $this->_checkAccess()) {
            return $res;
        }
        $this->_addHeaderFiles();
        $this->assign['title'] = 'Manage PACS';
        $this->assign['path'] = url();
        $this->assign['token'] = $_SESSION['token'];
        $this->assign['version'] = $this->settings('settings.version');
        $this->assign['theme_admin'] = $this->settings('settings.theme_admin');
        $this->assign['logo'] = $this->settings('settings.logo');
        $this->assign['header'] = $this->core->appends['header'] ?? [];
        $this->assign['footer'] = $this->core->appends['footer'] ?? [];
        $this->assign['notify'] = $this->core->getNotify();

        echo $this->draw('main.html', ['pacs' => htmlspecialchars_array($this->assign)]);
        exit();
    }

    public function getManage()
    {
        if ($res = $this->_checkAccess()) {
            return $res;
        }
        $this->_addHeaderFiles();
        $this->assign['title'] = 'Manage PACS';
        return $this->draw('manage.html', ['pacs' => htmlspecialchars_array($this->assign)]);
    }

    public function getDetail($id)
    {
        if ($res = $this->_checkAccess()) {
            return $res;
        }
        if (!$this->_isMono()) {
            $this->_addHeaderFiles();
            $this->assign['title'] = 'Detail PACS Study';
            $response = $this->_remoteCall('GET', '/admin/api/mini_pacs/detail/' . $id);
            if (is_array($response) && isset($response['status']) && $response['status'] === 'success') {
                $this->assign['study'] = $response['study'];
                $this->assign['series'] = $response['series'];
                return $this->draw('detail.html', ['pacs' => htmlspecialchars_array($this->assign)]);
            } else {
                $this->notify('failure', 'Data study tidak ditemukan di remote');
                redirect(url([ADMIN, 'mini_pacs', 'manage']));
            }
        }
        $this->_addHeaderFiles();
        $this->assign['title'] = 'Detail PACS Study';
        $study = $this->db('mlite_mini_pacs_study')->join('reg_periksa', 'reg_periksa.no_rawat = mlite_mini_pacs_study.no_rawat')->join('pasien', 'pasien.no_rkm_medis = reg_periksa.no_rkm_medis')->where('id', $id)->oneArray();
        if (!$study) {
            $this->notify('failure', 'Data study tidak ditemukan');
            redirect(url([ADMIN, 'mini_pacs', 'manage']));
        }
        $this->assign['study'] = $study;

        $series = $this->db('mlite_mini_pacs_series')->where('study_id', $id)->toArray();
        foreach ($series as &$s) {
            $s['instances'] = $this->db('mlite_mini_pacs_instance')->where('series_id', $s['id'])->toArray();
        }
        $this->assign['series'] = $series;

        return $this->draw('detail.html', ['pacs' => htmlspecialchars_array($this->assign)]);
    }

    public function apiList()
    {
        if (!$this->_isMono()) {
            $response = $this->_remoteCall('POST', '/admin/api/mini_pacs/list', $_POST);
            if (!is_array($response) || !isset($response['data'])) {
                echo json_encode([
                    "draw" => intval($_POST['draw'] ?? 0),
                    "recordsTotal" => 0,
                    "recordsFiltered" => 0,
                    "data" => [],
                    "error" => is_array($response) ? ($response['message'] ?? 'Unknown remote error') : 'Invalid response from remote server'
                ]);
            } else {
                echo json_encode($response);
            }
            exit();
        }

        $draw = $_POST['draw'] ?? 0;
        $start = $_POST['start'] ?? 0;
        $length = $_POST['length'] ?? 10;
        $search = $_POST['search']['value'] ?? '';
        $colFilters = $_POST['columns'] ?? [];

        $totalRecords = $this->db('mlite_mini_pacs_study')->count();

        $query = $this->db('mlite_mini_pacs_study')
            ->join('reg_periksa', 'reg_periksa.no_rawat = mlite_mini_pacs_study.no_rawat')
            ->join('pasien', 'pasien.no_rkm_medis = reg_periksa.no_rkm_medis')
            ->leftJoin('permintaan_radiologi', 'permintaan_radiologi.no_rawat = mlite_mini_pacs_study.no_rawat AND permintaan_radiologi.tgl_permintaan = DATE(mlite_mini_pacs_study.study_date)');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->like('pasien.nm_pasien', '%' . $search . '%')
                    ->orLike('pasien.no_rkm_medis', '%' . $search . '%')
                    ->orLike('mlite_mini_pacs_study.no_rawat', '%' . $search . '%')
                    ->orLike('mlite_mini_pacs_study.modality', '%' . $search . '%')
                    ->orLike('mlite_mini_pacs_study.description', '%' . $search . '%');
            });
        }

        // Individual column filters
        if (!empty($colFilters)) {
            if (!empty($colFilters[1]['search']['value'])) {
                $query->like('pasien.tgl_lahir', '%' . $colFilters[1]['search']['value'] . '%');
            }
            if (!empty($colFilters[2]['search']['value'])) {
                $query->like('pasien.nm_pasien', '%' . $colFilters[2]['search']['value'] . '%');
            }
            if (!empty($colFilters[3]['search']['value'])) {
                $query->like('pasien.no_rkm_medis', '%' . $colFilters[3]['search']['value'] . '%');
            }
            if (!empty($colFilters[4]['search']['value'])) {
                $query->like('mlite_mini_pacs_study.description', '%' . $colFilters[4]['search']['value'] . '%');
            }
            if (!empty($colFilters[5]['search']['value'])) {
                $query->like('mlite_mini_pacs_study.study_date', '%' . $colFilters[5]['search']['value'] . '%');
            }
            if (!empty($colFilters[6]['search']['value'])) {
                $query->like('mlite_mini_pacs_study.modality', '%' . $colFilters[6]['search']['value'] . '%');
            }
            if (!empty($colFilters[7]['search']['value'])) {
                $query->like('permintaan_radiologi.noorder', '%' . $colFilters[7]['search']['value'] . '%');
            }
        }

        $filteredRecords = $query->count();

        $data = $query->select([
            'mlite_mini_pacs_study.*',
            'pasien.nm_pasien',
            'pasien.no_rkm_medis',
            'pasien.tgl_lahir',
            '(SELECT COUNT(*) FROM mlite_mini_pacs_series WHERE study_id = mlite_mini_pacs_study.id) as series_count',
            '(SELECT COUNT(*) FROM mlite_mini_pacs_instance ins JOIN mlite_mini_pacs_series ser ON ser.id = ins.series_id WHERE ser.study_id = mlite_mini_pacs_study.id) as instance_count',
            'permintaan_radiologi.noorder as accession_number'
        ])
            ->desc('mlite_mini_pacs_study.id')
            ->offset($start)
            ->limit($length)
            ->toArray();

        $result = [];
        foreach ($data as $row) {
            $row['view_url'] = url([ADMIN, 'mini_pacs', 'viewer', $row['id']]);
            $row['ohif_url'] = url([ADMIN, 'mini_pacs', 'ohif', $row['id']]);
            $row['ser_inst'] = $row['series_count'] . '/' . $row['instance_count'];
            $result[] = $row;
        }

        echo json_encode([
            "draw" => intval($draw),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $filteredRecords,
            "data" => $result
        ]);
        exit();
    }

    public function apiStudyList()
    {
        if (!$this->_isMono()) {
            header('Content-Type: application/json');
            $response = $this->_remoteCall('GET', '/admin/api/mini_pacs/studylist', $_GET);
            echo json_encode($response);
            exit();
        }
        header('Content-Type: application/json');
        $this->core->checkAuth('GET');

        $page = $_GET['page'] ?? 1;
        $per_page = $_GET['per_page'] ?? 10;
        $offset = ($page - 1) * $per_page;
        $search = $_GET['s'] ?? '';

        $query = $this->db('mlite_mini_pacs_study')
            ->join('reg_periksa', 'reg_periksa.no_rawat = mlite_mini_pacs_study.no_rawat')
            ->join('pasien', 'pasien.no_rkm_medis = reg_periksa.no_rkm_medis');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->like('pasien.nm_pasien', '%' . $search . '%')
                    ->orLike('pasien.no_rkm_medis', '%' . $search . '%')
                    ->orLike('mlite_mini_pacs_study.no_rawat', '%' . $search . '%')
                    ->orLike('mlite_mini_pacs_study.modality', '%' . $search . '%');
            });
        }

        $total = $query->count();
        $data = $query->select(['mlite_mini_pacs_study.*', 'pasien.nm_pasien', 'pasien.no_rkm_medis'])
            ->desc('mlite_mini_pacs_study.study_date')
            ->limit($per_page)
            ->offset($offset)
            ->toArray();

        foreach ($data as &$row) {
            $row['series_count'] = $this->db('mlite_mini_pacs_series')->where('study_id', $row['id'])->count();
        }

        echo json_encode([
            'status' => 'success',
            'total' => $total,
            'page' => intval($page),
            'per_page' => intval($per_page),
            'data' => $data
        ]);
        exit();
    }

    public function apiStudyDetail($id)
    {
        if (!$this->_isMono()) {
            header('Content-Type: application/json');
            $response = $this->_remoteCall('GET', '/admin/api/mini_pacs/studydetail/' . $id);
            echo json_encode($response);
            exit();
        }
        header('Content-Type: application/json');
        $this->core->checkAuth('GET');

        $study = $this->db('mlite_mini_pacs_study')
            ->select(['mlite_mini_pacs_study.*', 'pasien.nm_pasien', 'pasien.no_rkm_medis', 'pasien.tgl_lahir', 'pasien.jk'])
            ->join('reg_periksa', 'reg_periksa.no_rawat = mlite_mini_pacs_study.no_rawat')
            ->join('pasien', 'pasien.no_rkm_medis = reg_periksa.no_rkm_medis')
            ->where('mlite_mini_pacs_study.id', $id)
            ->oneArray();

        if (!$study) {
            echo json_encode(['status' => 'error', 'message' => 'Study not found']);
            exit;
        }

        $series = $this->db('mlite_mini_pacs_series')->where('study_id', $id)->toArray();
        foreach ($series as &$s) {
            $s['instance_count'] = $this->db('mlite_mini_pacs_instance')->where('series_id', $s['id'])->count();
        }

        echo json_encode([
            'status' => 'success',
            'study' => $study,
            'series' => $series
        ]);
        exit;
    }

    public function apiSeriesDetail($id)
    {
        if (!$this->_isMono()) {
            header('Content-Type: application/json');
            $response = $this->_remoteCall('GET', '/admin/api/mini_pacs/seriesdetail/' . $id);
            echo json_encode($response);
            exit();
        }
        header('Content-Type: application/json');
        $this->core->checkAuth('GET');

        $series = $this->db('mlite_mini_pacs_series')->where('id', $id)->oneArray();
        if (!$series) {
            echo json_encode(['status' => 'error', 'message' => 'Series not found']);
            exit;
        }

        $instances = $this->db('mlite_mini_pacs_instance')->where('series_id', $id)->toArray();

        echo json_encode([
            'status' => 'success',
            'series' => $series,
            'instances' => $instances
        ]);
        exit;
    }

    public function apiInstanceJpg($id)
    {
        if (!$this->_isMono()) {
            $response = $this->_remoteCall('GET', '/admin/api/mini_pacs/instancejpg/' . $id);
            header('Content-Type: image/jpeg');
            echo $response;
            exit();
        }
        $this->core->checkAuth('GET');
        $instance = $this->db('mlite_mini_pacs_instance')->where('id', $id)->oneArray();

        if (!$instance) {
            header("HTTP/1.0 404 Not Found");
            exit;
        }

        $dicomPath = $instance['file_path'];
        $jpgPath = str_replace('.dcm', '_thumb.jpg', $dicomPath);

        if (!file_exists($jpgPath)) {
            // Try to generate on the fly if missing
            $binPath = 'export PATH=$PATH:/usr/local/bin:/usr/bin:/opt/homebrew/bin && ';
            $cmdThumb = sprintf(
                $binPath . 'dcmj2pnm +oj +Wn %s %s 2>&1',
                escapeshellarg($dicomPath),
                escapeshellarg($jpgPath)
            );
            shell_exec($cmdThumb);
        }

        if (file_exists($jpgPath)) {
            header('Content-Type: image/jpeg');
            header('Content-Length: ' . filesize($jpgPath));
            while (ob_get_level())
                ob_end_clean();
            readfile($jpgPath);
            exit;
        }

        header("HTTP/1.0 404 Not Found");
        exit;
    }

    public function apiInstanceDicom($id)
    {
        $this->core->checkAuth('GET');
        return $this->apiDicomFile($id);
    }

    public function apiDetail($id)
    {
        if (!$this->_isMono()) {
            header('Content-Type: application/json');
            $response = $this->_remoteCall('GET', '/admin/api/mini_pacs/detail/' . $id);
            echo json_encode($response);
            exit();
        }
        header('Content-Type: application/json');

        $study = $this->db('mlite_mini_pacs_study')
            ->select(['mlite_mini_pacs_study.*', 'pasien.nm_pasien', 'pasien.no_rkm_medis', 'pasien.tgl_lahir', 'pasien.jk'])
            ->join('reg_periksa', 'reg_periksa.no_rawat = mlite_mini_pacs_study.no_rawat')
            ->join('pasien', 'pasien.no_rkm_medis = reg_periksa.no_rkm_medis')
            ->where('mlite_mini_pacs_study.id', $id)
            ->oneArray();

        if (!$study) {
            echo json_encode(['status' => 'error', 'message' => 'Study not found']);
            exit;
        }

        // Get Accession Number
        $permintaan = $this->db('permintaan_radiologi')
            ->where('no_rawat', $study['no_rawat'])
            ->where('tgl_permintaan', date('Y-m-d', strtotime($study['study_date'])))
            ->oneArray();
        $study['accession_number'] = $permintaan['noorder'] ?? '';

        // Get metadata from first instance
        $series = $this->db('mlite_mini_pacs_series')->where('study_id', $id)->toArray();
        $studyTime = '';
        $institutionName = '';
        $referringPhysician = '';
        $requestingPhysician = '';
        $studyID = '';

        if (!empty($series)) {
            $instance = $this->db('mlite_mini_pacs_instance')->where('series_id', $series[0]['id'])->oneArray();
            if ($instance) {
                $meta = $this->getDicomMeta($instance['id']);
                $studyTime = $meta['0008,0030'] ?? '';
                $institutionName = $meta['0008,0080'] ?? '';
                $referringPhysician = $meta['0008,0090'] ?? '';
                $requestingPhysician = $meta['0032,1032'] ?? '';
                $studyID = $meta['0020,0010'] ?? '';
            }
        }

        $study['study_time'] = $studyTime;
        $study['institution_name'] = $institutionName;
        $study['referring_physician'] = $referringPhysician;
        $study['requesting_physician'] = $requestingPhysician;
        $study['study_id'] = $studyID;

        // Get series list
        foreach ($series as &$s) {
            $instances = $this->db('mlite_mini_pacs_instance')->where('series_id', $s['id'])->toArray();
            $s['instance_count'] = count($instances);
            $s['modality'] = $study['modality'];
            if (!empty($instances)) {
                $metaIns = $this->getDicomMeta($instances[0]['id']);
                $s['modality'] = $metaIns['0008,0060'] ?? $study['modality'];
            }
        }

        echo json_encode([
            'status' => 'success',
            'study' => $study,
            'series' => $series
        ]);
        exit;
    }

    public function anyDelete($id = null)
    {
        if (!$this->_isMono()) {
            $response = $this->_remoteCall('GET', '/admin/api/mini_pacs/delete/' . $id);
            if (is_array($response) && (($response['status'] ?? '') === 'success' || (isset($response['raw']) && strpos($response['raw'], 'sukses') !== false))) {
                $this->notify('success', 'Hapus sukses (remote)');
            } else {
                $this->notify('failure', 'Hapus gagal (remote)');
            }
            if (isset($_POST['main']) && $_POST['main'] == "main") {
                redirect(url([ADMIN, 'mini_pacs', 'main']));
            } else {
                redirect(url([ADMIN, 'mini_pacs', 'manage']));
            }
            exit();
        }
        $series = $this->db('mlite_mini_pacs_series')->where('study_id', $id)->toArray();
        foreach ($series as $s) {
            $instances = $this->db('mlite_mini_pacs_instance')->where('series_id', $s['id'])->toArray();
            foreach ($instances as $ins) {
                if (file_exists($ins['file_path'])) {
                    @unlink($ins['file_path']);
                }
                $this->db()->pdo()->exec("DELETE FROM mlite_mini_pacs_instance_metadata WHERE instance_id = " . intval($ins['id']));
                $this->db('mlite_mini_pacs_instance')->where('id', $ins['id'])->delete();
            }
            $this->db('mlite_mini_pacs_series')->where('id', $s['id'])->delete();
        }

        if ($this->db('mlite_mini_pacs_study')->where('id', $id)->delete()) {
            $this->notify('success', 'Hapus sukses');
        } else {
            $this->notify('failure', 'Hapus gagal');
        }
        if (isset($_POST['main']) && $_POST['main'] == "main") {
            redirect(url([ADMIN, 'mini_pacs', 'main']));
        } else {
            redirect(url([ADMIN, 'mini_pacs', 'manage']));
        }
        exit();
    }

    public function apiDelete($id = null)
    {
        $series = $this->db('mlite_mini_pacs_series')->where('study_id', $id)->toArray();
        foreach ($series as $s) {
            $instances = $this->db('mlite_mini_pacs_instance')->where('series_id', $s['id'])->toArray();
            foreach ($instances as $ins) {
                if (file_exists($ins['file_path'])) {
                    @unlink($ins['file_path']);
                }
                $this->db()->pdo()->exec("DELETE FROM mlite_mini_pacs_instance_metadata WHERE instance_id = " . intval($ins['id']));
                $this->db('mlite_mini_pacs_instance')->where('id', $ins['id'])->delete();
            }
            $this->db('mlite_mini_pacs_series')->where('id', $s['id'])->delete();
        }

        if ($this->db('mlite_mini_pacs_study')->where('id', $id)->delete()) {
            return ['status' => 'success', 'message' => 'Hapus sukses'];
        } else {
            return ['status' => 'error', 'message' => 'Hapus gagal'];
        }
    }

    public function getSatusehat($id = null)
    {
        if (!$id) {
            redirect(url([ADMIN, 'mini_pacs', 'manage']));
        }

        $study = $this->db('mlite_mini_pacs_study')->where('id', $id)->oneArray();
        if (!$study) {
            $this->notify('failure', 'Data study tidak ditemukan');
            redirect(url([ADMIN, 'mini_pacs', 'manage']));
        }

        $series = $this->db('mlite_mini_pacs_series')->where('study_id', $id)->oneArray();
        if (!$series) {
            $this->notify('failure', 'Series tidak ditemukan');
            redirect(url([ADMIN, 'mini_pacs', 'manage']));
        }

        $instance = $this->db('mlite_mini_pacs_instance')->where('series_id', $series['id'])->oneArray();
        if (!$instance || !file_exists($instance['file_path'])) {
            $this->notify('failure', 'File DICOM tidak ditemukan atau belum dikonversi');
            redirect(url([ADMIN, 'mini_pacs', 'manage']));
        }

        try {
            require_once __DIR__ . '/SatusehatDicomClient.php';
            $config = [
                'base_url' => 'https://api-satusehat.kemkes.go.id',
                'client_key' => $this->core->getSettings('satu_sehat', 'clientid'),
                'secret_key' => $this->core->getSettings('satu_sehat', 'secretkey'),
                'organization_id' => $this->core->getSettings('satu_sehat', 'organizationid')
            ];
            $client = new SatusehatDicomClient($config);

            // 1. Upload DICOM
            $upload = $client->uploadDicom($instance['file_path']);

            $isDuplicate = (isset($upload['status']) && $upload['status'] === 'duplicate');

            $mlite_satu_sehat_response = $this->db('mlite_satu_sehat_response')->where('no_rawat', $study['no_rawat'])->oneArray() ?: [];
            $permintaan_radiologi = $this->db('permintaan_radiologi')->where('no_rawat', $study['no_rawat'])->oneArray() ?: [];

            $pasien = $this->db('reg_periksa')
                ->join('pasien', 'pasien.no_rkm_medis = reg_periksa.no_rkm_medis')
                ->where('reg_periksa.no_rawat', $study['no_rawat'])
                ->oneArray() ?: [];
            $no_ktp_pasien = isset($pasien['no_ktp']) ? $pasien['no_ktp'] : '';

            // 2. Kirim ImagingStudy
            $fhirResult = $client->sendImagingStudy([
                'patientId' => $this->getPatientID($no_ktp_pasien), // Mohon sesuaikan
                'encounterId' => $mlite_satu_sehat_response['id_encounter'] ?? '', // Mohon sesuaikan
                'serviceRequestId' => $mlite_satu_sehat_response['id_rad_request'] ?? '', // Mohon sesuaikan
                'noRawat' => $study['no_rawat'],
                'noOrder' => $permintaan_radiologi['noorder'] ?? '',
                'studyUID' => $study['study_instance_uid'],
                'seriesUID' => $series['series_instance_uid'],
                'instanceUID' => $instance['sop_instance_uid']
            ]);

            $fhirString = $fhirResult['response'];
            $fhirPayload = $fhirResult['payload'];

            // Percantik format string JSON jika memungkinkan
            $fhirArr = json_decode($fhirString, true);
            $fhirPretty = $fhirArr ? json_encode($fhirArr, JSON_PRETTY_PRINT) : $fhirString;

            $id_imaging_study = isset($fhirArr['id']) ? $fhirArr['id'] : '';
            if (!empty($id_imaging_study)) {
                $this->db('mlite_satu_sehat_response')
                    ->where('no_rawat', $study['no_rawat'])
                    ->save([
                        'id_imaging_study' => $id_imaging_study
                    ]);
            }

            header('Content-Type: application/json');
            echo json_encode([
                'status' => $isDuplicate ? 'duplicate' : 'success',
                'message' => $isDuplicate ? 'DICOM sudah ada di Satu Sehat (Duplicate).' : 'Berhasil kirim ke Satu Sehat',
                'dicom_raw' => $upload['raw'],
                'fhir_payload' => $fhirPayload,
                'fhir_raw' => $fhirPretty
            ]);

            exit();
        } catch (\Exception $e) {
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
            exit();
        }
    }

    public function getAccessToken()
    {
        $cacheFile = __DIR__ . '/satusehat_token_cache.json';
        if (file_exists($cacheFile)) {
            $data = json_decode(file_get_contents($cacheFile), true);
            if ($data && isset($data['expired_at']) && $data['expired_at'] > time()) {
                return $data['access_token'];
            }
        }

        $url = $this->authurl . '/oauth2/v1/accesstoken?grant_type=client_credentials';
        $ch = curl_init();
        $postData = http_build_query([
            'client_id' => trim($this->clientid),
            'client_secret' => trim($this->secretkey)
        ]);

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ["Content-Type: application/x-www-form-urlencoded"],
            CURLOPT_POSTFIELDS => $postData
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($response, true);
        if (isset($json['access_token'])) {
            $token = $json['access_token'];
            file_put_contents($cacheFile, json_encode([
                'access_token' => $token,
                'expired_at' => time() + 3500
            ]));
            return $token;
        }

        return '';
    }

    public function getPatient($nik_pasien)
    {
        $token = $this->getAccessToken();
        if (!$token)
            return '';

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->fhirurl . '/Patient?identifier=https://fhir.kemkes.go.id/id/nik|' . $nik_pasien,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_HTTPHEADER => array('Content-Type: application/json', 'Authorization: Bearer ' . $token),
            CURLOPT_CUSTOMREQUEST => 'GET'
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    public function getPatientID($nik_pasien)
    {
        $response = $this->getPatient($nik_pasien);
        $data = json_decode($response, true);
        if (isset($data['entry'][0]['resource']['id'])) {
            return $data['entry'][0]['resource']['id'];
        }
        return '';
    }

    public function getSettings()
    {
        $this->_addHeaderFiles();
        $this->assign['title'] = 'Pengaturan PACS';
        $this->assign['ae_title'] = $this->core->getSettings('mini_pacs', 'ae_title');
        $this->assign['target_aet'] = $this->core->getSettings('mini_pacs', 'target_aet');
        $this->assign['target_ip'] = $this->core->getSettings('mini_pacs', 'target_ip');
        $this->assign['target_port'] = $this->core->getSettings('mini_pacs', 'target_port');
        $this->assign['worklist_aet'] = $this->core->getSettings('mini_pacs', 'worklist_aet');
        $this->assign['worklist_port'] = $this->core->getSettings('mini_pacs', 'worklist_port');
        $this->assign['is_mono'] = $this->core->getSettings('mini_pacs', 'is_mono');
        $this->assign['remote_ip'] = $this->core->getSettings('mini_pacs', 'remote_ip');
        $this->assign['remote_api_key'] = $this->core->getSettings('mini_pacs', 'remote_api_key');
        $this->assign['remote_username'] = $this->core->getSettings('mini_pacs', 'remote_username');
        $this->assign['remote_password'] = $this->core->getSettings('mini_pacs', 'remote_password');
        return $this->draw('settings.html', ['settings' => htmlspecialchars_array($this->assign)]);
    }

    public function postSaveSettings()
    {
        $this->db('mlite_settings')->where('module', 'mini_pacs')->where('field', 'ae_title')->save(['value' => $_POST['ae_title']]);
        $this->db('mlite_settings')->where('module', 'mini_pacs')->where('field', 'target_aet')->save(['value' => $_POST['target_aet']]);
        $this->db('mlite_settings')->where('module', 'mini_pacs')->where('field', 'target_ip')->save(['value' => $_POST['target_ip']]);
        $this->db('mlite_settings')->where('module', 'mini_pacs')->where('field', 'target_port')->save(['value' => $_POST['target_port']]);
        $this->db('mlite_settings')->where('module', 'mini_pacs')->where('field', 'worklist_aet')->save(['value' => $_POST['worklist_aet']]);
        $this->db('mlite_settings')->where('module', 'mini_pacs')->where('field', 'worklist_port')->save(['value' => $_POST['worklist_port']]);
        $this->db('mlite_settings')->where('module', 'mini_pacs')->where('field', 'is_mono')->save(['value' => $_POST['is_mono']]);
        $this->db('mlite_settings')->where('module', 'mini_pacs')->where('field', 'remote_ip')->save(['value' => $_POST['remote_ip']]);
        $this->db('mlite_settings')->where('module', 'mini_pacs')->where('field', 'remote_api_key')->save(['value' => $_POST['remote_api_key']]);
        $this->db('mlite_settings')->where('module', 'mini_pacs')->where('field', 'remote_username')->save(['value' => $_POST['remote_username']]);
        $this->db('mlite_settings')->where('module', 'mini_pacs')->where('field', 'remote_password')->save(['value' => $_POST['remote_password']]);
        $_SESSION['remote_pacs_token'] = ''; // Reset token on setting change
        $this->notify('success', 'Pengaturan mini PACS berhasil disimpan');
        redirect(url([ADMIN, 'mini_pacs', 'settings']));
    }

    private function _isMono()
    {
        if (defined('MULTI_APP') && MULTI_APP && defined('MULTI_APP_REDIRECT') && MULTI_APP_REDIRECT == 'mini_pacs') {
            return true;
        }
        return $this->core->getSettings('mini_pacs', 'is_mono') != '0';
    }

    private function _remoteCall($method, $path, $data = [])
    {
        $remote_ip = rtrim($this->core->getSettings('mini_pacs', 'remote_ip'), '/');
        if (empty($remote_ip)) {
            return ['status' => 'error', 'message' => 'Remote IP not configured'];
        }

        $url = $remote_ip . $path;
        if (strpos($url, 'http') !== 0) {
            $url = 'http://' . $url;
        }

        $ch = curl_init();

        $token = $this->_getRemoteToken();
        $api_key = $this->core->getSettings('mini_pacs', 'remote_api_key');

        $headers = [
            'Authorization: Bearer ' . ($token ?: ''),
            'X-Api-Key: ' . ($api_key ?: ''),
            'X-Requested-With: XMLHttpRequest'
        ];

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if (isset($data['file_image'])) {
                $headers[] = 'Content-Type: multipart/form-data';
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            } else {
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            }
        } else {
            if (!empty($data)) {
                $url .= (strpos($url, '?') !== false ? '&' : '?') . http_build_query($data);
            }
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Set timeout

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            return ['status' => 'error', 'message' => 'CURL Error: ' . $curlError];
        }

        if ($httpCode >= 400) {
            return ['status' => 'error', 'message' => 'Remote server returned error: ' . $httpCode, 'raw' => $response];
        }

        if (strpos($contentType, 'application/json') !== false) {
            $decoded = json_decode($response, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        // Check if response might be JSON even if content type is wrong
        $decoded = json_decode($response, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }

        return $response;
    }

    private function _getRemoteToken()
    {
        if (isset($_SESSION['remote_pacs_token']) && !empty($_SESSION['remote_pacs_token'])) {
            return $_SESSION['remote_pacs_token'];
        }

        $remote_ip = rtrim($this->core->getSettings('mini_pacs', 'remote_ip'), '/');
        $api_key = $this->core->getSettings('mini_pacs', 'remote_api_key');
        $username = $this->core->getSettings('mini_pacs', 'remote_username');
        $password = $this->core->getSettings('mini_pacs', 'remote_password');

        if (empty($remote_ip) || empty($username) || empty($password)) {
            return null;
        }

        $url = $remote_ip . '/admin/api/login';
        if (strpos($url, 'http') !== 0) {
            $url = 'http://' . $url;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'username' => $username,
            'password' => $password
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'X-Api-Key: ' . $api_key
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $data = json_decode($response, true);
            if (isset($data['token'])) {
                $_SESSION['remote_pacs_token'] = $data['token'];
                return $data['token'];
            }
        }

        return null;
    }

    public function getWorklist()
    {
        if ($res = $this->_checkAccess()) {
            return $res;
        }
        $this->_addHeaderFiles();
        $this->assign['title'] = 'Radiology Worklist';
        $worklist_dir = UPLOADS . '/pacs/worklist/';
        $files = glob($worklist_dir . '*.wl');
        $wl_files = [];
        foreach ($files as $file) {
            $wl_files[] = [
                'name' => basename($file),
                'size' => filesize($file),
                'date' => date('Y-m-d H:i:s', filemtime($file))
            ];
        }
        $this->assign['wl_files'] = $wl_files;

        // Get pending requests from permintaan_radiologi
        $this->assign['requests'] = $this->db('permintaan_radiologi')
            ->join('reg_periksa', 'reg_periksa.no_rawat = permintaan_radiologi.no_rawat')
            ->join('pasien', 'pasien.no_rkm_medis = reg_periksa.no_rkm_medis')
            ->leftJoin('mlite_mini_pacs_worklist_status', 'mlite_mini_pacs_worklist_status.noorder = permintaan_radiologi.noorder')
            ->select(['permintaan_radiologi.*', 'pasien.nm_pasien', 'pasien.tgl_lahir', 'pasien.jk', 'mlite_mini_pacs_worklist_status.pulled_at'])
            ->where('permintaan_radiologi.tgl_hasil', '0000-00-00')
            ->desc('permintaan_radiologi.tgl_permintaan')
            ->desc('permintaan_radiologi.jam_permintaan')
            ->limit(50)
            ->toArray();

        return $this->draw('worklist.html', ['pacs' => htmlspecialchars_array($this->assign)]);
    }

    private function _checkAccess()
    {
        if (!$this->_isMono()) {
            $this->assign['remote_ip'] = $this->core->getSettings('mini_pacs', 'remote_ip');
            return $this->draw('remote_access.html', ['is_mono' => htmlspecialchars_array($this->assign)]);
        }
        return null;
    }


    public function getMWL()
    {
        // $this->_addHeaderFiles();
        $this->assign['title'] = 'Radiology Worklist';
        $worklist_dir = UPLOADS . '/pacs/worklist/';
        $files = glob($worklist_dir . '*.wl');
        $wl_files = [];
        foreach ($files as $file) {
            $wl_files[] = [
                'name' => basename($file),
                'size' => filesize($file),
                'date' => date('Y-m-d H:i:s', filemtime($file))
            ];
        }
        $this->assign['wl_files'] = $wl_files;

        // Get pending requests from permintaan_radiologi
        $this->assign['requests'] = $this->db('permintaan_radiologi')
            ->join('reg_periksa', 'reg_periksa.no_rawat = permintaan_radiologi.no_rawat')
            ->join('pasien', 'pasien.no_rkm_medis = reg_periksa.no_rkm_medis')
            ->leftJoin('mlite_mini_pacs_worklist_status', 'mlite_mini_pacs_worklist_status.noorder = permintaan_radiologi.noorder')
            ->select(['permintaan_radiologi.*', 'pasien.nm_pasien', 'pasien.tgl_lahir', 'pasien.jk', 'mlite_mini_pacs_worklist_status.pulled_at'])
            ->where('permintaan_radiologi.tgl_hasil', '0000-00-00')
            ->desc('permintaan_radiologi.tgl_permintaan')
            ->desc('permintaan_radiologi.jam_permintaan')
            ->limit(50)
            ->toArray();

        $this->assign['path'] = url();
        $this->assign['token'] = $_SESSION['token'];
        $this->assign['version'] = $this->settings('settings.version');
        $this->assign['theme_admin'] = $this->settings('settings.theme_admin');
        $this->assign['logo'] = $this->settings('settings.logo');
        $this->assign['header'] = $this->core->appends['header'] ?? [];
        $this->assign['footer'] = $this->core->appends['footer'] ?? [];
        $this->assign['notify'] = $this->core->getNotify();

        echo $this->draw('mwl.html', ['pacs' => htmlspecialchars_array($this->assign)]);
        exit();
    }

    public function getLatestPulledEvents()
    {
        header('Content-Type: application/json');
        $events = $this->db('mlite_mini_pacs_worklist_status')
            ->join('permintaan_radiologi', 'permintaan_radiologi.noorder = mlite_mini_pacs_worklist_status.noorder')
            ->join('reg_periksa', 'reg_periksa.no_rawat = permintaan_radiologi.no_rawat')
            ->join('pasien', 'pasien.no_rkm_medis = reg_periksa.no_rkm_medis')
            ->select(['mlite_mini_pacs_worklist_status.noorder', 'pasien.nm_pasien', 'mlite_mini_pacs_worklist_status.pulled_at'])
            ->where('mlite_mini_pacs_worklist_status.notified', 0)
            ->where('mlite_mini_pacs_worklist_status.pulled_at', 'IS NOT', null)
            ->toArray();

        echo json_encode(['status' => 'success', 'events' => $events]);
        exit();
    }

    public function postMarkAsNotified()
    {
        header('Content-Type: application/json');
        $noorder = $_POST['noorder'] ?? '';
        if ($noorder) {
            $this->db('mlite_mini_pacs_worklist_status')->where('noorder', $noorder)->save(['notified' => 1]);
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Missing noorder']);
        }
        exit();
    }

    public function postUpdateWorklist()
    {
        $worklist_dir = UPLOADS . '/pacs/worklist/';
        if (!is_dir($worklist_dir)) {
            mkdir($worklist_dir, 0755, true);
        }

        // Clean up old .wl and .dump files
        array_map('unlink', glob($worklist_dir . "*.wl"));
        array_map('unlink', glob($worklist_dir . "*.dump"));

        $requests = $this->db('permintaan_radiologi')
            ->where('tgl_hasil', '0000-00-00')
            ->toArray();

        $count = 0;
        foreach ($requests as $req) {
            if ($this->_generateWorklistFile($req['noorder'])) {
                $count++;
            }
        }

        $this->notify('success', 'Worklist berhasil diperbarui (' . $count . ' entries)');
        if ($_POST['mwl'] == "mwl") {
            redirect(url([ADMIN, 'mini_pacs', 'mwl']));
        } else {
            redirect(url([ADMIN, 'mini_pacs', 'worklist']));
        }
    }

    public function postUpdateSelectedWorklist()
    {
        $selected = $_POST['selected_noorder'] ?? [];
        if (empty($selected)) {
            $this->notify('failure', 'Tidak ada item yang dipilih');
            if ($_POST['mwl'] == "mwl") {
                redirect(url([ADMIN, 'mini_pacs', 'mwl']));
            } else {
                redirect(url([ADMIN, 'mini_pacs', 'worklist']));
            }
        }

        $count = 0;
        foreach ($selected as $noorder) {
            if ($this->_generateWorklistFile($noorder)) {
                $count++;
            }
        }

        $this->notify('success', 'Worklist terpilih berhasil diperbarui (' . $count . ' entries)');
        if ($_POST['mwl'] == "mwl") {
            redirect(url([ADMIN, 'mini_pacs', 'mwl']));
        } else {
            redirect(url([ADMIN, 'mini_pacs', 'worklist']));
        }

    }

    private function _generateWorklistFile($noorder)
    {
        $worklist_dir = UPLOADS . '/pacs/worklist/';
        if (!is_dir($worklist_dir)) {
            mkdir($worklist_dir, 0755, true);
        }

        $req = $this->db('permintaan_radiologi')
            ->join('reg_periksa', 'reg_periksa.no_rawat = permintaan_radiologi.no_rawat')
            ->join('pasien', 'pasien.no_rkm_medis = reg_periksa.no_rkm_medis')
            ->select(['permintaan_radiologi.*', 'pasien.nm_pasien', 'pasien.tgl_lahir', 'pasien.jk'])
            ->where('permintaan_radiologi.noorder', $noorder)
            ->oneArray();

        if (!$req)
            return false;

        $worklist_aet = $this->core->getSettings('mini_pacs', 'worklist_aet') ?: 'MINIPACS';

        $patient_name = strtoupper($req['nm_pasien']);
        $patient_id = $req['no_rkm_medis'];
        $birth_date = str_replace('-', '', $req['tgl_lahir']);
        $sex = $req['jk'];
        $start_date = str_replace('-', '', $req['tgl_permintaan']);
        $start_time = str_replace(':', '', $req['jam_permintaan']);

        // Get modality from procedure name or default to CR
        $procedure = $this->db('permintaan_pemeriksaan_radiologi')
            ->join('jns_perawatan_radiologi', 'jns_perawatan_radiologi.kd_jenis_prw = permintaan_pemeriksaan_radiologi.kd_jenis_prw')
            ->where('permintaan_pemeriksaan_radiologi.noorder', $noorder)
            ->oneArray();

        $modality = 'CR';
        if ($procedure) {
            $p_name = strtoupper($procedure['nm_perawatan']);
            if (strpos($p_name, 'CT') !== false)
                $modality = 'CT';
            elseif (strpos($p_name, 'USG') !== false)
                $modality = 'USG';
            elseif (strpos($p_name, 'MRI') !== false)
                $modality = 'MR';
            elseif (strpos($p_name, 'CR') !== false)
                $modality = 'CR';
            elseif (strpos($p_name, 'DX') !== false)
                $modality = 'DX';
        }

        $dump_content = "# DICOM Worklist Dump\n";
        $dump_content .= "(0008,0005) CS [ISO_IR 100]\n";
        $dump_content .= "(0008,0050) SH [$noorder]\n";
        $dump_content .= "(0008,0060) CS [$modality]\n";
        $dump_content .= "(0010,0010) PN [$patient_name]\n";
        $dump_content .= "(0010,0020) LO [$patient_id]\n";
        $dump_content .= "(0010,0030) DA [$birth_date]\n";
        $dump_content .= "(0010,0040) CS [$sex]\n";
        $dump_content .= "(0040,0100) SQ\n";
        $dump_content .= "  (fffe,e000) na\n";
        $dump_content .= "    (0008,0060) CS [$modality]\n";
        $dump_content .= "    (0040,0001) AE [$worklist_aet]\n";
        $dump_content .= "    (0040,0002) DA [$start_date]\n";
        $dump_content .= "    (0040,0003) TM [$start_time]\n";
        $dump_content .= "    (0040,0009) SH [$noorder]\n";
        $dump_content .= "  (fffe,e00d) na\n";
        $dump_content .= "(fffe,e0dd) na\n";
        $dump_content .= "(0040,1001) SH [$noorder]\n";

        $dump_file = $worklist_dir . $noorder . ".dump";
        $wl_file = $worklist_dir . $noorder . ".wl";
        file_put_contents($dump_file, $dump_content);

        $cmd = sprintf(
            'export PATH=$PATH:/usr/local/bin:/usr/bin:/opt/homebrew/bin && dump2dcm %s %s 2>&1',
            escapeshellarg($dump_file),
            escapeshellarg($wl_file)
        );
        shell_exec($cmd);
        return true;
    }



    public function apiStore()
    {
        if (!$this->_isMono()) {
            header('Content-Type: application/json');
            $response = $this->_remoteCall('POST', '/admin/api/mini_pacs/store', json_decode(file_get_contents('php://input'), true));
            echo json_encode($response);
            exit();
        }
        header('Content-Type: application/json');

        $username = $this->core->checkAuth('POST');

        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid JSON payload']);
            exit();
        }

        $no_rawat = $input['no_rawat'] ?? '';
        $study_instance_uid = $input['study_instance_uid'] ?? '';
        $series_instance_uid = $input['series_instance_uid'] ?? '';
        $sop_instance_uid = $input['sop_instance_uid'] ?? '';
        $file_path = $input['file_path'] ?? '';
        $modality = $input['modality'] ?? 'UNKNOWN';

        if (empty($no_rawat) || empty($study_instance_uid) || empty($series_instance_uid) || empty($sop_instance_uid)) {
            echo json_encode(['status' => 'error', 'message' => 'Missing required UIDs or no_rawat']);
            exit();
        }

        $study = $this->db('mlite_mini_pacs_study')->where('study_instance_uid', $study_instance_uid)->oneArray();
        if (!$study) {
            $this->db('mlite_mini_pacs_study')->save([
                'no_rawat' => $no_rawat,
                'study_instance_uid' => $study_instance_uid,
                'study_date' => date('Y-m-d H:i:s'),
                'modality' => $modality,
                'description' => ''
            ]);
            $study = $this->db('mlite_mini_pacs_study')->where('study_instance_uid', $study_instance_uid)->oneArray();
        }

        if (!$study) {
            echo json_encode(['status' => 'error', 'message' => 'Failed to retrieve or create study']);
            exit();
        }

        $series = $this->db('mlite_mini_pacs_series')->where('series_instance_uid', $series_instance_uid)->oneArray();
        if (!$series) {
            $this->db('mlite_mini_pacs_series')->save([
                'study_id' => $study['id'],
                'series_instance_uid' => $series_instance_uid,
                'series_description' => ''
            ]);
            $series = $this->db('mlite_mini_pacs_series')->where('series_instance_uid', $series_instance_uid)->oneArray();
        }

        $instance = $this->db('mlite_mini_pacs_instance')->where('sop_instance_uid', $sop_instance_uid)->oneArray();
        if (!$instance) {
            $this->db('mlite_mini_pacs_instance')->save([
                'series_id' => $series['id'],
                'sop_instance_uid' => $sop_instance_uid,
                'file_path' => $file_path
            ]);
            echo json_encode(['status' => 'success', 'message' => 'Stored successfully']);
        } else {
            echo json_encode(['status' => 'success', 'message' => 'Instance already exists. Ignored.']);
        }

        exit();
    }

    public function postUpload()
    {
        if (!$this->_isMono()) {
            $data = $_POST;
            if (isset($_FILES['file_image'])) {
                $data['file_image'] = new \CURLFile($_FILES['file_image']['tmp_name'], $_FILES['file_image']['type'], $_FILES['file_image']['name']);
            }
            $response = $this->_remoteCall('POST', '/admin/api/mini_pacs/apiupload', $data);
            if (is_array($response) && (($response['status'] ?? '') === 'success' || (isset($response['raw']) && strpos($response['raw'], 'sukses') !== false))) {
                $this->notify('success', 'Upload sukses (remote)');
            } else {
                $errorMessage = is_array($response) ? ($response['message'] ?? 'Unknown error') : 'Response non-array';
                $this->notify('failure', 'Upload gagal (remote): ' . $errorMessage);
            }
            if ($_POST['main'] == "main") {
                redirect(url([ADMIN, 'mini_pacs', 'main']));
            } else {
                redirect(url([ADMIN, 'mini_pacs', 'manage']));
            }
        }
        $no_rawat = $_POST['no_rawat'];
        $modality = $_POST['modality'] ?: 'CR';
        $file = $_FILES['file_image'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->notify('failure', 'Upload error');
            if ($_POST['main'] == "main") {
                redirect(url([ADMIN, 'mini_pacs', 'main']));
            } else {
                redirect(url([ADMIN, 'mini_pacs', 'manage']));
            }
        }

        $reg = $this->db('reg_periksa')->join('pasien', 'pasien.no_rkm_medis = reg_periksa.no_rkm_medis')->where('no_rawat', $no_rawat)->oneArray();
        if (!$reg) {
            $this->notify('failure', 'No Rawat tidak valid');
            redirect(url([ADMIN, 'mini_pacs', 'manage']));
        }

        $uploadDir = UPLOADS . '/pacs/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $tmpFile = $file['tmp_name'];

        // Check if file is already DICOM
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $tmpFile);
        finfo_close($finfo);

        $isDicom = ($extension === 'dcm' || $mime === 'application/dicom');

        if (!$isDicom && $mime === 'image/png') {
            $imageObj = imagecreatefrompng($tmpFile);
            $bg = imagecreatetruecolor(imagesx($imageObj), imagesy($imageObj));
            $white = imagecolorallocate($bg, 255, 255, 255);
            imagefill($bg, 0, 0, $white);
            imagecopy($bg, $imageObj, 0, 0, 0, 0, imagesx($imageObj), imagesy($imageObj));

            $newTmpFile = $uploadDir . 'temp_' . time() . '.jpg';
            imagejpeg($bg, $newTmpFile, 100);
            imagedestroy($imageObj);
            imagedestroy($bg);
            $tmpFile = $newTmpFile;
        }

        $studyInstanceUid = $this->_generateOID();
        $seriesInstanceUid = $this->_generateOID();
        $sopInstanceUid = $this->_generateOID();
        $dcmodifyArgs = '';

        if ($isDicom) {
            $dumpStudy = shell_exec(sprintf('export PATH=$PATH:/usr/local/bin:/usr/bin:/opt/homebrew/bin && dcmdump +P "0020,000d" %s 2>/dev/null', escapeshellarg($tmpFile)));
            if ($dumpStudy && preg_match('/\[(.*?)\]/', $dumpStudy, $m)) {
                $studyInstanceUid = trim($m[1], " \t\n\r\0\x0B");
            } else {
                $dcmodifyArgs .= sprintf(' -i "(0020,000D)=%s"', escapeshellarg($studyInstanceUid));
            }

            $dumpSeries = shell_exec(sprintf('export PATH=$PATH:/usr/local/bin:/usr/bin:/opt/homebrew/bin && dcmdump +P "0020,000e" %s 2>/dev/null', escapeshellarg($tmpFile)));
            if ($dumpSeries && preg_match('/\[(.*?)\]/', $dumpSeries, $m)) {
                $seriesInstanceUid = trim($m[1], " \t\n\r\0\x0B");
            } else {
                $dcmodifyArgs .= sprintf(' -i "(0020,000E)=%s"', escapeshellarg($seriesInstanceUid));
            }

            $dumpSop = shell_exec(sprintf('export PATH=$PATH:/usr/local/bin:/usr/bin:/opt/homebrew/bin && dcmdump +P "0008,0018" %s 2>/dev/null', escapeshellarg($tmpFile)));
            if ($dumpSop && preg_match('/\[(.*?)\]/', $dumpSop, $m)) {
                $sopInstanceUid = trim($m[1], " \t\n\r\0\x0B");
            } else {
                $dcmodifyArgs .= sprintf(' -i "(0008,0018)=%s"', escapeshellarg($sopInstanceUid));
            }

            $dumpName = shell_exec(sprintf('export PATH=$PATH:/usr/local/bin:/usr/bin:/opt/homebrew/bin && dcmdump +P "0010,0010" %s 2>/dev/null', escapeshellarg($tmpFile)));
            if ($dumpName && preg_match('/\[(.*?)\]/', $dumpName, $m)) {
                $dcmodifyArgs .= sprintf(' -m "(0010,0010)=%s"', escapeshellarg($reg['nm_pasien']));
            } else {
                $dcmodifyArgs .= sprintf(' -i "(0010,0010)=%s"', escapeshellarg($reg['nm_pasien']));
            }

            $dumpId = shell_exec(sprintf('export PATH=$PATH:/usr/local/bin:/usr/bin:/opt/homebrew/bin && dcmdump +P "0010,0020" %s 2>/dev/null', escapeshellarg($tmpFile)));
            if ($dumpId && preg_match('/\[(.*?)\]/', $dumpId, $m)) {
                $dcmodifyArgs .= sprintf(' -m "(0010,0020)=%s"', escapeshellarg($reg['no_rkm_medis']));
            } else {
                $dcmodifyArgs .= sprintf(' -i "(0010,0020)=%s"', escapeshellarg($reg['no_rkm_medis']));
            }

            $dumpMod = shell_exec(sprintf('export PATH=$PATH:/usr/local/bin:/usr/bin:/opt/homebrew/bin && dcmdump +P "0008,0060" %s 2>/dev/null', escapeshellarg($tmpFile)));
            if ($dumpMod && preg_match('/\[(.*?)\]/', $dumpMod, $m)) {
                $dcmodifyArgs .= sprintf(' -m "(0008,0060)=%s"', escapeshellarg($modality));
            } else {
                $dcmodifyArgs .= sprintf(' -i "(0008,0060)=%s"', escapeshellarg($modality));
            }

            $outFile = $uploadDir . $sopInstanceUid . '.dcm';

            if (empty($dcmodifyArgs)) {
                $cmd = sprintf(
                    'export PATH=$PATH:/usr/local/bin:/usr/bin:/opt/homebrew/bin && cp %s %s 2>&1',
                    escapeshellarg($tmpFile),
                    escapeshellarg($outFile)
                );
            } else {
                $cmd = sprintf(
                    'export PATH=$PATH:/usr/local/bin:/usr/bin:/opt/homebrew/bin && cp %s %s && dcmodify -nb%s %s 2>&1',
                    escapeshellarg($tmpFile),
                    escapeshellarg($outFile),
                    $dcmodifyArgs,
                    escapeshellarg($outFile)
                );
            }
        } else {
            $outFile = $uploadDir . $sopInstanceUid . '.dcm';
            $cmd = sprintf(
                'export PATH=$PATH:/usr/local/bin:/usr/bin:/opt/homebrew/bin && img2dcm -k "PatientName=%s" -k "PatientID=%s" -k "StudyInstanceUID=%s" -k "SeriesInstanceUID=%s" -k "SOPInstanceUID=%s" -k "Modality=%s" %s %s 2>&1',
                escapeshellarg($reg['nm_pasien']),
                escapeshellarg($reg['no_rkm_medis']),
                escapeshellarg($studyInstanceUid),
                escapeshellarg($seriesInstanceUid),
                escapeshellarg($sopInstanceUid),
                escapeshellarg($modality),
                escapeshellarg($tmpFile),
                escapeshellarg($outFile)
            );
        }

        $output = shell_exec($cmd);

        if (file_exists($outFile)) {
            // Create thumbnail from the generated DICOM using dcmj2pnm
            $thumbnailFile = $uploadDir . $sopInstanceUid . '_thumb.jpg';
            $cmdThumb = sprintf(
                'export PATH=$PATH:/usr/local/bin:/usr/bin:/opt/homebrew/bin && dcmj2pnm +oj +Wn %s %s 2>&1',
                escapeshellarg($outFile),
                escapeshellarg($thumbnailFile)
            );
            shell_exec($cmdThumb);

            $studyInfo = $this->db('mlite_mini_pacs_study')->where('study_instance_uid', $studyInstanceUid)->oneArray();
            if (!$studyInfo) {
                $this->db('mlite_mini_pacs_study')->save([
                    'no_rawat' => $no_rawat,
                    'study_instance_uid' => $studyInstanceUid,
                    'study_date' => date('Y-m-d H:i:s'),
                    'modality' => $modality,
                    'description' => 'Manual Upload (' . $modality . ')'
                ]);
                $studyInfo = $this->db('mlite_mini_pacs_study')->where('study_instance_uid', $studyInstanceUid)->oneArray();
            }
            $studyId = $studyInfo['id'];

            $seriesInfo = $this->db('mlite_mini_pacs_series')->where('series_instance_uid', $seriesInstanceUid)->oneArray();
            if (!$seriesInfo) {
                $this->db('mlite_mini_pacs_series')->save([
                    'study_id' => $studyId,
                    'series_instance_uid' => $seriesInstanceUid,
                    'series_description' => 'Converted from ' . $file['name']
                ]);
                $seriesInfo = $this->db('mlite_mini_pacs_series')->where('series_instance_uid', $seriesInstanceUid)->oneArray();
            } else {
                $this->db('mlite_mini_pacs_series')->where('id', $seriesInfo['id'])->save([
                    'study_id' => $studyId
                ]);
            }
            $seriesId = $seriesInfo['id'];

            $instanceInfo = $this->db('mlite_mini_pacs_instance')->where('sop_instance_uid', $sopInstanceUid)->oneArray();
            if (!$instanceInfo) {
                $this->db('mlite_mini_pacs_instance')->save([
                    'series_id' => $seriesId,
                    'sop_instance_uid' => $sopInstanceUid,
                    'file_path' => $outFile
                ]);
                $instanceInfo = $this->db('mlite_mini_pacs_instance')->where('sop_instance_uid', $sopInstanceUid)->oneArray();
            } else {
                $this->db('mlite_mini_pacs_instance')->where('id', $instanceInfo['id'])->save([
                    'series_id' => $seriesId,
                    'file_path' => $outFile
                ]);
            }
            $instanceId = $instanceInfo['id'];

            // Ekstrak metadata
            $this->_extractAndSaveMetadata($instanceId, $outFile);

            $this->notify('success', 'Konversi berhasil');
        } else {
            $this->notify('failure', 'Gagal convert image ke DICOM. LOG: ' . $output);
        }

        if ($_POST['main'] == "main") {
            redirect(url([ADMIN, 'mini_pacs', 'main']));
        } else {
            redirect(url([ADMIN, 'mini_pacs', 'manage']));
        }

    }

    public function postApiUpload()
    {
        if (!$this->_isMono()) {
            header('Content-Type: application/json');
            $data = $_POST;
            if (isset($_FILES['file_image'])) {
                $data['file_image'] = new \CURLFile($_FILES['file_image']['tmp_name'], $_FILES['file_image']['type'], $_FILES['file_image']['name']);
            } elseif (isset($_FILES['file'])) {
                $data['file_image'] = new \CURLFile($_FILES['file']['tmp_name'], $_FILES['file']['type'], $_FILES['file']['name']);
            }
            $response = $this->_remoteCall('POST', '/admin/api/mini_pacs/apiupload', $data);
            echo json_encode($response);
            exit();
        }
        header('Content-Type: application/json');

        $no_rawat = $_POST['no_rawat'];
        $modality = $_POST['modality'] ?: 'CR';
        $file = $_FILES['file_image'] ?? $_FILES['file'];

        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['status' => 'error', 'message' => 'Upload error']);
            exit;
        }

        $reg = $this->db('reg_periksa')->join('pasien', 'pasien.no_rkm_medis = reg_periksa.no_rkm_medis')->where('no_rawat', $no_rawat)->oneArray();
        if (!$reg) {
            echo json_encode(['status' => 'error', 'message' => 'No Rawat tidak valid']);
            exit;
        }

        $uploadDir = UPLOADS . '/pacs/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $tmpFile = $file['tmp_name'];

        // Check if file is already DICOM
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $tmpFile);
        finfo_close($finfo);

        $isDicom = ($extension === 'dcm' || $mime === 'application/dicom');

        if (!$isDicom && $mime === 'image/png') {
            $imageObj = imagecreatefrompng($tmpFile);
            $bg = imagecreatetruecolor(imagesx($imageObj), imagesy($imageObj));
            $white = imagecolorallocate($bg, 255, 255, 255);
            imagefill($bg, 0, 0, $white);
            imagecopy($bg, $imageObj, 0, 0, 0, 0, imagesx($imageObj), imagesy($imageObj));

            $newTmpFile = $uploadDir . 'temp_' . time() . '.jpg';
            imagejpeg($bg, $newTmpFile, 100);
            imagedestroy($imageObj);
            imagedestroy($bg);
            $tmpFile = $newTmpFile;
        }

        $studyInstanceUid = $this->_generateOID();
        $seriesInstanceUid = $this->_generateOID();
        $sopInstanceUid = $this->_generateOID();
        $dcmodifyArgs = '';

        if ($isDicom) {
            $dumpStudy = shell_exec(sprintf('export PATH=$PATH:/usr/local/bin:/usr/bin:/opt/homebrew/bin && dcmdump +P "0020,000d" %s 2>/dev/null', escapeshellarg($tmpFile)));
            if ($dumpStudy && preg_match('/\[(.*?)\]/', $dumpStudy, $m)) {
                $studyInstanceUid = $m[1];
            } else {
                $dcmodifyArgs .= sprintf(' -i "(0020,000D)=%s"', escapeshellarg($studyInstanceUid));
            }

            $dumpSeries = shell_exec(sprintf('export PATH=$PATH:/usr/local/bin:/usr/bin:/opt/homebrew/bin && dcmdump +P "0020,000e" %s 2>/dev/null', escapeshellarg($tmpFile)));
            if ($dumpSeries && preg_match('/\[(.*?)\]/', $dumpSeries, $m)) {
                $seriesInstanceUid = $m[1];
            } else {
                $dcmodifyArgs .= sprintf(' -i "(0020,000E)=%s"', escapeshellarg($seriesInstanceUid));
            }

            $dumpSop = shell_exec(sprintf('export PATH=$PATH:/usr/local/bin:/usr/bin:/opt/homebrew/bin && dcmdump +P "0008,0018" %s 2>/dev/null', escapeshellarg($tmpFile)));
            if ($dumpSop && preg_match('/\[(.*?)\]/', $dumpSop, $m)) {
                $sopInstanceUid = $m[1];
            } else {
                $dcmodifyArgs .= sprintf(' -i "(0008,0018)=%s"', escapeshellarg($sopInstanceUid));
            }

            $dumpName = shell_exec(sprintf('export PATH=$PATH:/usr/local/bin:/usr/bin:/opt/homebrew/bin && dcmdump +P "0010,0010" %s 2>/dev/null', escapeshellarg($tmpFile)));
            if ($dumpName && preg_match('/\[(.*?)\]/', $dumpName, $m)) {
                $dcmodifyArgs .= sprintf(' -m "(0010,0010)=%s"', escapeshellarg($reg['nm_pasien']));
            } else {
                $dcmodifyArgs .= sprintf(' -i "(0010,0010)=%s"', escapeshellarg($reg['nm_pasien']));
            }

            $dumpId = shell_exec(sprintf('export PATH=$PATH:/usr/local/bin:/usr/bin:/opt/homebrew/bin && dcmdump +P "0010,0020" %s 2>/dev/null', escapeshellarg($tmpFile)));
            if ($dumpId && preg_match('/\[(.*?)\]/', $dumpId, $m)) {
                $dcmodifyArgs .= sprintf(' -m "(0010,0020)=%s"', escapeshellarg($reg['no_rkm_medis']));
            } else {
                $dcmodifyArgs .= sprintf(' -i "(0010,0020)=%s"', escapeshellarg($reg['no_rkm_medis']));
            }

            $dumpMod = shell_exec(sprintf('export PATH=$PATH:/usr/local/bin:/usr/bin:/opt/homebrew/bin && dcmdump +P "0008,0060" %s 2>/dev/null', escapeshellarg($tmpFile)));
            if ($dumpMod && preg_match('/\[(.*?)\]/', $dumpMod, $m)) {
                $dcmodifyArgs .= sprintf(' -m "(0008,0060)=%s"', escapeshellarg($modality));
            } else {
                $dcmodifyArgs .= sprintf(' -i "(0008,0060)=%s"', escapeshellarg($modality));
            }

            $studyDescription = trim((string) shell_exec(sprintf('export PATH=$PATH:/usr/local/bin:/usr/bin:/opt/homebrew/bin && dcmdump +P "0008,1030" -q %s | grep -o "\\[.*\\]" | tr -d "[]" | head -n 1', escapeshellarg($tmpFile))));
            $seriesDescription = trim((string) shell_exec(sprintf('export PATH=$PATH:/usr/local/bin:/usr/bin:/opt/homebrew/bin && dcmdump +P "0008,103E" -q %s | grep -o "\\[.*\\]" | tr -d "[]" | head -n 1', escapeshellarg($tmpFile))));

            $outFile = $uploadDir . $sopInstanceUid . '.dcm';

            $cmd = sprintf(
                'export PATH=$PATH:/usr/local/bin:/usr/bin:/opt/homebrew/bin && cp %s %s && dcmodify -nb%s %s 2>&1',
                escapeshellarg($tmpFile),
                escapeshellarg($outFile),
                $dcmodifyArgs,
                escapeshellarg($outFile)
            );
        } else {
            $outFile = $uploadDir . $sopInstanceUid . '.dcm';
            $cmd = sprintf(
                'export PATH=$PATH:/usr/local/bin:/usr/bin:/opt/homebrew/bin && img2dcm -k "PatientName=%s" -k "PatientID=%s" -k "StudyInstanceUID=%s" -k "SeriesInstanceUID=%s" -k "SOPInstanceUID=%s" -k "Modality=%s" %s %s 2>&1',
                escapeshellarg($reg['nm_pasien']),
                escapeshellarg($reg['no_rkm_medis']),
                escapeshellarg($studyInstanceUid),
                escapeshellarg($seriesInstanceUid),
                escapeshellarg($sopInstanceUid),
                escapeshellarg($modality),
                escapeshellarg($tmpFile),
                escapeshellarg($outFile)
            );
        }

        $output = shell_exec($cmd);

        if (file_exists($outFile)) {
            // Create thumbnail from the generated DICOM using dcmj2pnm
            $thumbnailFile = $uploadDir . $sopInstanceUid . '_thumb.jpg';
            $cmdThumb = sprintf(
                'export PATH=$PATH:/usr/local/bin:/usr/bin:/opt/homebrew/bin && dcmj2pnm +oj +Wn %s %s 2>&1',
                escapeshellarg($outFile),
                escapeshellarg($thumbnailFile)
            );
            shell_exec($cmdThumb);

            $studyInfo = $this->db('mlite_mini_pacs_study')->where('study_instance_uid', $studyInstanceUid)->oneArray();
            if (!$studyInfo) {
                $this->db('mlite_mini_pacs_study')->save([
                    'no_rawat' => $no_rawat,
                    'study_instance_uid' => $studyInstanceUid,
                    'study_date' => date('Y-m-d H:i:s'),
                    'modality' => $modality,
                    'description' => ($isDicom && !empty($studyDescription)) ? $studyDescription : 'Manual Upload (' . $modality . ')'
                ]);
                $studyInfo = $this->db('mlite_mini_pacs_study')->where('study_instance_uid', $studyInstanceUid)->oneArray();
            }
            $studyId = $studyInfo['id'];

            $seriesInfo = $this->db('mlite_mini_pacs_series')->where('series_instance_uid', $seriesInstanceUid)->oneArray();
            if (!$seriesInfo) {
                $this->db('mlite_mini_pacs_series')->save([
                    'study_id' => $studyId,
                    'series_instance_uid' => $seriesInstanceUid,
                    'series_description' => ($isDicom && !empty($seriesDescription)) ? $seriesDescription : 'Uploaded Series'
                ]);
                $seriesInfo = $this->db('mlite_mini_pacs_series')->where('series_instance_uid', $seriesInstanceUid)->oneArray();
            } else {
                $this->db('mlite_mini_pacs_series')->where('id', $seriesInfo['id'])->save([
                    'study_id' => $studyId
                ]);
            }
            $seriesId = $seriesInfo['id'];

            $instanceInfo = $this->db('mlite_mini_pacs_instance')->where('sop_instance_uid', $sopInstanceUid)->oneArray();
            if (!$instanceInfo) {
                $this->db('mlite_mini_pacs_instance')->save([
                    'series_id' => $seriesId,
                    'sop_instance_uid' => $sopInstanceUid,
                    'file_path' => $outFile
                ]);
                $instanceInfo = $this->db('mlite_mini_pacs_instance')->where('sop_instance_uid', $sopInstanceUid)->oneArray();
            } else {
                $this->db('mlite_mini_pacs_instance')->where('id', $instanceInfo['id'])->save([
                    'series_id' => $seriesId,
                    'file_path' => $outFile
                ]);
            }
            $instanceId = $instanceInfo['id'];

            // Ekstrak metadata
            $this->_extractAndSaveMetadata($instanceId, $outFile);

            echo json_encode(['status' => 'success', 'message' => 'Konversi berhasil', 'result' => url('uploads/pacs/' . $sopInstanceUid . '_thumb.jpg')]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal convert image ke DICOM. LOG: ' . $output]);
        }
        exit;
    }

    public function getSend($instance_id)
    {
        $instance = $this->db('mlite_mini_pacs_instance')->where('id', $instance_id)->oneArray();
        if (!$instance || !file_exists($instance['file_path'])) {
            $this->notify('failure', 'File DICOM tidak ditemukan');
            redirect(url([ADMIN, 'mini_pacs', 'manage']));
        }

        $ae_title = $this->core->getSettings('mini_pacs', 'ae_title');
        $target_aet = $this->core->getSettings('mini_pacs', 'target_aet');
        $target_ip = $this->core->getSettings('mini_pacs', 'target_ip');
        $target_port = $this->core->getSettings('mini_pacs', 'target_port');

        $cmd = sprintf(
            'export PATH=$PATH:/usr/local/bin:/usr/bin:/opt/homebrew/bin && storescu -aet %s -aec %s %s %s %s 2>&1',
            escapeshellarg($ae_title),
            escapeshellarg($target_aet),
            escapeshellarg($target_ip),
            escapeshellarg($target_port),
            escapeshellarg($instance['file_path'])
        );

        $output = shell_exec($cmd);

        $this->notify('success', 'Perintah C-STORE berhasil dieksekusi. LOG: ' . $output);

        $series = $this->db('mlite_mini_pacs_series')->where('id', $instance['series_id'])->oneArray();
        redirect(url([ADMIN, 'mini_pacs', 'detail', $series['study_id']]));
    }

    public function postApiSend($instance_id)
    {
        header('Content-Type: application/json');
        $instance = $this->db('mlite_mini_pacs_instance')->where('id', $instance_id)->oneArray();
        if (!$instance || !file_exists($instance['file_path'])) {
            echo json_encode(['status' => 'error', 'message' => 'File DICOM tidak ditemukan']);
            exit();
        }

        $ae_title = $this->core->getSettings('mini_pacs', 'ae_title');
        $target_aet = $this->core->getSettings('mini_pacs', 'target_aet');
        $target_ip = $this->core->getSettings('mini_pacs', 'target_ip');
        $target_port = $this->core->getSettings('mini_pacs', 'target_port');

        $cmd = sprintf(
            'export PATH=$PATH:/usr/local/bin:/usr/bin:/opt/homebrew/bin && storescu -aet %s -aec %s %s %s %s 2>&1',
            escapeshellarg($ae_title),
            escapeshellarg($target_aet),
            escapeshellarg($target_ip),
            escapeshellarg($target_port),
            escapeshellarg($instance['file_path'])
        );

        $output = shell_exec($cmd);

        echo json_encode(['status' => 'success', 'message' => 'C-STORE Executed', 'log' => $output]);
        exit();
    }

    public function anyDownload($id)
    {
        $study = $this->db('mlite_mini_pacs_study')
            ->join('reg_periksa', 'reg_periksa.no_rawat = mlite_mini_pacs_study.no_rawat')
            ->join('pasien', 'pasien.no_rkm_medis = reg_periksa.no_rkm_medis')
            ->where('mlite_mini_pacs_study.id', $id)
            ->oneArray();

        if (!$study) {
            $this->notify('failure', 'Study tidak ditemukan');
            redirect(url([ADMIN, 'mini_pacs', 'manage']));
        }

        $series = $this->db('mlite_mini_pacs_series')->where('study_id', $id)->toArray();
        $files = [];
        foreach ($series as $s) {
            $instances = $this->db('mlite_mini_pacs_instance')->where('series_id', $s['id'])->toArray();
            foreach ($instances as $ins) {
                if (file_exists($ins['file_path'])) {
                    $files[] = $ins['file_path'];
                }
            }
        }

        if (empty($files)) {
            $this->notify('failure', 'Tidak ada file untuk diunduh');
            redirect(url([ADMIN, 'mini_pacs', 'manage']));
        }

        $pasien_name = str_replace([' ', '/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $study['nm_pasien']);
        $zipName = 'PACS_' . $pasien_name . '_' . date('Ymd_His', strtotime($study['study_date'])) . '.zip';
        $zipPath = UPLOADS . '/pacs/' . $zipName;

        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
            foreach ($files as $file) {
                $zip->addFile($file, basename($file));
            }
            $zip->close();

            if (file_exists($zipPath)) {
                header('Content-Type: application/zip');
                header('Content-Disposition: attachment; filename="' . $zipName . '"');
                header('Content-Length: ' . filesize($zipPath));
                header('Pragma: no-cache');
                header('Expires: 0');
                readfile($zipPath);
                unlink($zipPath);
                exit;
            }
        }

        $this->notify('failure', 'Gagal membuat file ZIP');
        redirect(url([ADMIN, 'mini_pacs', 'manage']));
    }

    public function postApiForward($id)
    {
        header('Content-Type: application/json');

        $study = $this->db('mlite_mini_pacs_study')->where('id', $id)->oneArray();
        if (!$study) {
            echo json_encode(['status' => 'error', 'message' => 'Study not found']);
            exit;
        }

        $ae_title = $this->core->getSettings('mini_pacs', 'ae_title') ?: 'MINIPACS';
        $target_aet = $this->core->getSettings('mini_pacs', 'target_aet');
        $target_ip = $this->core->getSettings('mini_pacs', 'target_ip');
        $target_port = $this->core->getSettings('mini_pacs', 'target_port');

        if (empty($target_aet) || empty($target_ip) || empty($target_port)) {
            echo json_encode(['status' => 'error', 'message' => 'Target PACS settings are incomplete. Please check Pengaturan.']);
            exit;
        }

        $series = $this->db('mlite_mini_pacs_series')->where('study_id', $id)->toArray();
        $instances = [];
        foreach ($series as $s) {
            $ins_list = $this->db('mlite_mini_pacs_instance')->where('series_id', $s['id'])->toArray();
            $instances = array_merge($instances, $ins_list);
        }

        if (empty($instances)) {
            echo json_encode(['status' => 'error', 'message' => 'No instances found for this study.']);
            exit;
        }

        $sent = 0;
        $failed = 0;
        $logs = [];

        foreach ($instances as $instance) {
            if (!file_exists($instance['file_path'])) {
                $failed++;
                continue;
            }

            $cmd = sprintf(
                'export PATH=$PATH:/usr/local/bin:/usr/bin:/opt/homebrew/bin && storescu -aet %s -aec %s %s %s %s 2>&1',
                escapeshellarg($ae_title),
                escapeshellarg($target_aet),
                escapeshellarg($target_ip),
                escapeshellarg($target_port),
                escapeshellarg($instance['file_path'])
            );

            $output = shell_exec($cmd);
            $logs[] = $output;

            // Simple check for storescu success (usually no output or specific success message)
            if (empty($output) || strpos($output, 'done') !== false || strpos($output, 'Association Accepted') !== false) {
                $sent++;
            } else {
                $failed++;
            }
        }

        echo json_encode([
            'status' => ($sent > 0) ? 'success' : 'error',
            'message' => "Forwarding complete: $sent sent, $failed failed.",
            'total' => count($instances),
            'sent' => $sent,
            'failed' => $failed,
            'log' => implode("\n", $logs)
        ]);
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES . '/mini_pacs/js/admin/mini_pacs.js');
        exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES . '/mini_pacs/assets/css/manage.css');
        exit();
    }

    private function _addHeaderFiles()
    {
        $this->core->addCSS(url('assets/css/dataTables.bootstrap.min.css'));
        $this->core->addCSS(url([ADMIN, 'mini_pacs', 'css']));
        $this->core->addJS(url('assets/jscripts/jquery.dataTables.min.js'));
        $this->core->addJS(url('assets/jscripts/dataTables.bootstrap.min.js'));
        $this->core->addJS(url([ADMIN, 'mini_pacs', 'javascript']), 'footer');
    }
    public function getViewer($studyId = null)
    {
        if ($res = $this->_checkAccess()) {
            return $res;
        }
        if (!$studyId) {
            redirect(url([ADMIN, 'mini_pacs', 'manage']));
        }
        if ($this->_isMono()) {
            $study = $this->db('mlite_mini_pacs_study')->where('id', $studyId)->oneArray();
            if (!$study) {
                redirect(url([ADMIN, 'mini_pacs', 'manage']));
            }
        }

        $this->assign['studyId'] = $studyId;
        $this->assign['baseUrl'] = url() . '/plugins/mini_pacs';
        echo $this->draw('viewer.html', $this->assign);
        exit();
    }

    public function getOhif($studyId = null)
    {
        if ($studyId === 'viewer') {
            $ohifHtml = file_get_contents(MODULES . '/mini_pacs/view/ohif/index.html');
            // Ganti path JS ke URL absolut agar OHIF script selalu termuat
            $ohifHtml = str_replace('./app-config.js', url('plugins/mini_pacs/view/ohif/app-config.js'), $ohifHtml);
            $ohifHtml = str_replace('./index.umd.js', url('plugins/mini_pacs/view/ohif/index.umd.js'), $ohifHtml);
            echo $ohifHtml;
            exit();
        }

        if ($res = $this->_checkAccess()) {
            return $res;
        }

        if (!$studyId) {
            redirect(url([ADMIN, 'mini_pacs', 'manage']));
        }
        if ($this->_isMono()) {
            $study = $this->db('mlite_mini_pacs_study')->where('id', $studyId)->oneArray();
            if (!$study) {
                redirect(url([ADMIN, 'mini_pacs', 'manage']));
            }
        }

        $jsonUrl = url([ADMIN, 'api', 'mini_pacs', 'ohifjson', $studyId]);
        $ohifUrl = url([ADMIN, 'mini_pacs', 'ohif', 'viewer']) . '&url=' . urlencode($jsonUrl);
        redirect($ohifUrl);
        exit();
    }

    public function getJavascriptviewer($studyId)
    {
        header('Content-type: text/javascript');

        if (!$this->_isMono()) {
            $response = $this->_remoteCall('GET', '/admin/api/mini_pacs/detail/' . $studyId);
            $studyData = [];
            if ($response['status'] === 'success') {
                foreach ($response['series'] as $s) {
                    $instanceIds = [];
                    // Note: Here I assume Server B's API Detail returns instances in series
                    // But our apiDetail doesn't include instance list, just counts.
                    // Wait, our apiDetail calls _remoteCall if not mono.
                    // If Server B is mono, it returns 'series' which has 'instances'?
                    // Let's check apiDetail implementation in Admin.php.
                    // Oh, apiDetail (line 300) returns $study and $series.
                    // And $series has instances? No, it has instance_count.
                    // Let's check apiStudyDetail (line 206). It returns $study and $series.
                    // Wait, getJavascriptviewer needs actual instance URLs.
                }
            }
            // Actually, I should use apiOhifJson or similar if I want full data.
            // Or I can just proxy getJavascriptviewer call itself.
            $response = $this->_remoteCall('GET', '/admin/api/mini_pacs/javascriptviewer/' . $studyId);
            echo $response;
            exit();
        }

        // Fetch hierarchical data to feed to Cornerstone viewer script
        $study = $this->db('mlite_mini_pacs_study')->where('id', $studyId)->oneArray();
        $series = $this->db('mlite_mini_pacs_series')->where('study_id', $studyId)->toArray();
        $studyData = [];
        foreach ($series as $s) {
            $instances = $this->db('mlite_mini_pacs_instance')->where('series_id', $s['id'])->toArray();
            $instanceIds = [];
            foreach ($instances as $ins) {
                // Return URL points to our apiDicomFile method
                $instanceIds[] = url([ADMIN, 'api', 'mini_pacs', 'dicomfile', $ins['id']]);
            }
            $studyData[] = [
                'series_id' => $s['id'],
                'description' => $s['series_description'] ?: 'Series ' . $s['id'],
                'instances' => $instanceIds
            ];
        }

        $jsonStudyData = json_encode($studyData);

        // Serve inline javascript that replaces orthanc's script
        echo "
            // Initialize basic Cornerstone plugins
            cornerstoneWADOImageLoader.external.cornerstone = cornerstone;
            cornerstoneWADOImageLoader.external.dicomParser = dicomParser;
            cornerstoneTools.external.cornerstone = cornerstone;
            cornerstoneTools.external.Hammer = Hammer;
            cornerstoneTools.external.cornerstoneMath = cornerstoneMath;

            cornerstoneTools.init();

            var studyData = {$jsonStudyData};
            var dicomImageElement = document.getElementById('dicomImage');
            var currentSeriesIndex = 0;

            cornerstone.enable(dicomImageElement);

            // Function to update overlays
            function updateOverlays(image, index) {
                if(!image.data) return;
                try {
                    var metadataHtml = '<table class=\"table table-bordered table-condensed\" style=\"color:#ddd;font-size:11px;margin:0;background:transparent;\">';
                    var tags = {
                        'x00100010': 'Patient Name',
                        'x00100020': 'Patient ID',
                        'x00100030': 'Birth Date',
                        'x00100040': 'Sex',
                        'x00080020': 'Study Date',
                        'x00080030': 'Study Time',
                        'x00080060': 'Modality',
                        'x00081030': 'Study Description'
                    };
                    
                    for(var tag in tags) {
                        var val = image.data.string(tag);
                        if(val) {
                            metadataHtml += '<tr><td width=\"40%\"><b>'+tags[tag]+'</b></td><td>'+val+'</td></tr>';
                        }
                    }
                    
                    metadataHtml += '<tr><td><b>Resolution</b></td><td>'+image.width+' x '+image.height+'</td></tr>';
                    metadataHtml += '<tr><td><b>Window (C/W)</b></td><td>'+(image.windowCenter||'-')+' / '+(image.windowWidth||'-')+'</td></tr>';
                    metadataHtml += '</table>';
                    
                    document.getElementById('dicomMetadata').innerHTML = metadataHtml;
                    
                    var ptName = image.data.string('x00100010') || '';
                    var modality = image.data.string('x00080060') || '';
                    document.getElementById('bottomleft').innerHTML = ptName;
                    document.getElementById('topleft').innerHTML = '<div style=\"color: #00ff00; font-weight: bold;\">Modality: '+modality+'</div>';
                    
                    var seriesDesc = studyData[currentSeriesIndex].description;
                    document.getElementById('statusMessage').innerHTML = 'Series: ' + seriesDesc + ' | Image: ' + (index + 1) + '/' + studyData[currentSeriesIndex].instances.length;
                    
                } catch(e) { console.log('Error parsing metadata', e); }
            }

            // Function to load a specific series
            function loadSeries(index) {
                currentSeriesIndex = index;
                var series = studyData[index];
                if(!series || series.instances.length === 0) return;

                // Mark active thumbnail
                document.querySelectorAll('.thumbnail').forEach((t, i) => {
                    if(i === index) t.classList.add('active'); else t.classList.remove('active');
                });

                const firstImageId = 'wadouri:' + series.instances[0];

                cornerstone.loadAndCacheImage(firstImageId).then(function(image) {
                    cornerstone.displayImage(dicomImageElement, image);
                    updateOverlays(image, 0);

                    // Re-initialize stack
                    cornerstoneTools.clearToolState(dicomImageElement, 'stack');
                    cornerstoneTools.addStackStateManager(dicomImageElement, ['stack']);
                    cornerstoneTools.addToolState(dicomImageElement, 'stack', {
                        currentImageIdIndex: 0,
                        imageIds: series.instances.map(url => 'wadouri:' + url)
                    });

                    // Update Cine controls
                    var frameSlider = document.getElementById('frameSlider');
                    frameSlider.max = series.instances.length - 1;
                    frameSlider.value = 0;
                    document.getElementById('frameCounter').innerHTML = 'Image: 1/' + series.instances.length;

                }, function(err) { alert('Error loading DICOM image: ' + err); });
            }

            // Initialize Thumbnails Sidebar
            function initSeriesThumbnails() {
                var stackWrapper = document.getElementById('stackWrapper');
                stackWrapper.innerHTML = '';

                studyData.forEach((series, index) => {
                    var thumbDiv = document.createElement('div');
                    thumbDiv.className = 'thumbnail' + (index === 0 ? ' active' : '');
                    thumbDiv.innerHTML = '<div class=\"thumbnail-info\">' + series.description + '<br/>(' + series.instances.length + ' images)</div>';
                    
                    var canvasContainer = document.createElement('div');
                    canvasContainer.style.width = '100%';
                    canvasContainer.style.height = '120px';
                    thumbDiv.prepend(canvasContainer);
                    
                    stackWrapper.appendChild(thumbDiv);
                    
                    // Enable Cornerstone on thumbnail
                    cornerstone.enable(canvasContainer);
                    const thumbImageId = 'wadouri:' + series.instances[0];
                    cornerstone.loadAndCacheImage(thumbImageId).then(image => {
                        cornerstone.displayImage(canvasContainer, image);
                    });

                    thumbDiv.onclick = function() { loadSeries(index); };
                });
            }

            // Tool Initializations
            function initTools() {
                const WwwcTool = cornerstoneTools.WwwcTool;
                cornerstoneTools.addTool(WwwcTool);
                cornerstoneTools.setToolActive('Wwwc', { mouseButtonMask: 1 });

                const ZoomTool = cornerstoneTools.ZoomTool;
                cornerstoneTools.addTool(ZoomTool);
                cornerstoneTools.setToolActive('Zoom', { mouseButtonMask: 2 });
                
                const PanTool = cornerstoneTools.PanTool;
                cornerstoneTools.addTool(PanTool);
                cornerstoneTools.setToolActive('Pan', { mouseButtonMask: 4 });

                const MagnifyTool = cornerstoneTools.MagnifyTool;
                cornerstoneTools.addTool(MagnifyTool);
                const LengthTool = cornerstoneTools.LengthTool;
                cornerstoneTools.addTool(LengthTool);
                const AngleTool = cornerstoneTools.AngleTool;
                cornerstoneTools.addTool(AngleTool);
                const ProbeTool = cornerstoneTools.ProbeTool;
                cornerstoneTools.addTool(ProbeTool);
                const EllipticalRoiTool = cornerstoneTools.EllipticalRoiTool;
                cornerstoneTools.addTool(EllipticalRoiTool);
                const RectangleRoiTool = cornerstoneTools.RectangleRoiTool;
                cornerstoneTools.addTool(RectangleRoiTool);
                const FreehandRoiTool = cornerstoneTools.FreehandRoiTool;
                cornerstoneTools.addTool(FreehandRoiTool);
                const EraserTool = cornerstoneTools.EraserTool;
                cornerstoneTools.addTool(EraserTool);

                // Tool mapping for buttons
                const toolMappings = {
                    'wwwcTool': 'Wwwc', 'zoomTool': 'Zoom', 'panTool': 'Pan', 'magnifyTool': 'Magnify',
                    'lengthTool': 'Length', 'angleTool': 'Angle', 'probeTool': 'Probe',
                    'ellipticalRoiTool': 'EllipticalRoi', 'rectangleRoiTool': 'RectangleRoi',
                    'freehandRoiTool': 'FreehandRoi', 'eraserTool': 'Eraser'
                };

                const toolButtons = document.querySelectorAll('.toolButton:not(#invertTool):not(#resetTool):not(#playClip)');
                for (let id in toolMappings) {
                    let elem = document.getElementById(id);
                    if (elem) {
                        elem.addEventListener('click', function() {
                            cornerstoneTools.setToolActive(toolMappings[id], { mouseButtonMask: 1 });
                            toolButtons.forEach(btn => btn.classList.remove('active'));
                            this.classList.add('active');
                        });
                    }
                }

                document.getElementById('invertTool').onclick = function() {
                    let viewport = cornerstone.getViewport(dicomImageElement);
                    viewport.invert = !viewport.invert;
                    cornerstone.setViewport(dicomImageElement, viewport);
                };
                document.getElementById('resetTool').onclick = function() { cornerstone.reset(dicomImageElement); };
                
                // Stack Scroll & Selection change
                dicomImageElement.addEventListener('cornerstonestackscroll', function(e) {
                    var stackData = cornerstoneTools.getToolState(dicomImageElement, 'stack');
                    if(stackData && stackData.data && stackData.data.length > 0) {
                        var index = stackData.data[0].currentImageIdIndex;
                        document.getElementById('frameSlider').value = index;
                        document.getElementById('frameCounter').innerHTML = 'Image: ' + (index + 1) + '/' + stackData.data[0].imageIds.length;
                    }
                });

                // Frame slider interaction
                document.getElementById('frameSlider').oninput = function() {
                    var index = parseInt(this.value);
                    const stackData = cornerstoneTools.getToolState(dicomImageElement, 'stack');
                    if(stackData && stackData.data.length > 0) {
                        stackData.data[0].currentImageIdIndex = index;
                        const imageId = stackData.data[0].imageIds[index];
                        cornerstone.loadAndCacheImage(imageId).then(image => {
                            cornerstone.displayImage(dicomImageElement, image);
                            updateOverlays(image, index);
                        });
                    }
                };

                // Cine Play/Pause
                var isPlaying = false;
                document.getElementById('playClip').onclick = function() {
                    if(isPlaying) {
                        cornerstoneTools.stopClip(dicomImageElement);
                        this.innerHTML = '<i class=\"fa fa-play\"></i>';
                    } else {
                        cornerstoneTools.playClip(dicomImageElement, 31);
                        this.innerHTML = '<i class=\"fa fa-pause\"></i>';
                    }
                    isPlaying = !isPlaying;
                };
            }

            if(studyData.length > 0) {
                initSeriesThumbnails();
                initTools();
                loadSeries(0);
            } else {
                alert('No Instances found in this study');
            }
        ";
        exit();
    }


    public function apiOhifJson($studyId)
    {
        if (!$this->_isMono()) {
            header('Content-Type: application/json');
            $response = $this->_remoteCall('GET', '/admin/api/mini_pacs/ohifjson/' . $studyId);
            echo json_encode($response);
            exit();
        }
        header('Content-Type: application/json');

        $study = $this->db('mlite_mini_pacs_study')
            ->select(['mlite_mini_pacs_study.*', 'pasien.nm_pasien', 'pasien.no_rkm_medis'])
            ->join('reg_periksa', 'reg_periksa.no_rawat = mlite_mini_pacs_study.no_rawat')
            ->join('pasien', 'pasien.no_rkm_medis = reg_periksa.no_rkm_medis')
            ->where('mlite_mini_pacs_study.id', $studyId)
            ->oneArray();

        if (!$study) {
            $study = $this->db('mlite_mini_pacs_study')->where('id', $studyId)->oneArray();
            if (!isset($study['nm_pasien']))
                $study['nm_pasien'] = 'Unknown Patient';
            if (!isset($study['no_rkm_medis']))
                $study['no_rkm_medis'] = 'Unknown ID';
        }

        if (!$study) {
            echo json_encode(['studies' => []]);
            exit;
        }

        $seriesList = $this->db('mlite_mini_pacs_series')
            ->where('study_id', $studyId)
            ->toArray();

        $permintaan_radiologi = $this->db('permintaan_radiologi')->where('no_rawat', $study['no_rawat'])->where('tgl_permintaan', date('Y-m-d', strtotime($study['study_date'])))->oneArray();

        $formattedSeries = [];

        foreach ($seriesList as $series) {

            $instances = $this->db('mlite_mini_pacs_instance')
                ->where('series_id', $series['id'])
                ->toArray();

            $formattedInstances = [];
            $seriesModality = 'CR'; // default aman

            $formattedDate = '19700101';
            $formattedTime = '000000';

            if (!empty($study['study_date'])) {
                $ts = strtotime($study['study_date']);
                $formattedDate = date('Y-m-d', $ts);
                $formattedTime = date('H:i:s', $ts);
            }


            foreach ($instances as $ins) {

                $fileUrl = url([ADMIN, 'api', 'mini_pacs', 'dicomfile', $ins['id']]);
                // $meta = $this->getDicomMeta($ins['id']);
                $meta = $this->getDicomMetaFromDicom($ins['file_path']);

                // 🔥 CLEAN VALUE
                foreach ($meta as $k => $v) {
                    $meta[$k] = trim($v, "'\" ");
                }

                // ✅ SOPClassUID
                // $sopClass = $meta['0008,0016'] ?? '';
                if (empty($sopClass)) {
                    $sopClass = '1.2.840.10008.5.1.4.1.1.7'; // fallback Secondary Capture
                }

                // ✅ MODALITY (WAJIB dari DICOM, bukan DB)
                $modality = strtoupper(trim($meta['0008,0060'] ?? 'CR'));
                if (!empty($modality)) {
                    $seriesModality = $modality;
                }

                // ✅ Rows & Columns (WAJIB > 0)
                $rows = intval($meta['0028,0010'] ?? 0);
                $cols = intval($meta['0028,0011'] ?? 0);

                if ($rows <= 0)
                    $rows = 512;
                if ($cols <= 0)
                    $cols = 512;

                // ✅ PixelSpacing
                $pixelSpacing = [1, 1];
                if (!empty($meta['0018,1164'])) {
                    $ps = preg_split('/[,\s]+/', $meta['0018,1164']);
                    if (count($ps) >= 2) {
                        $pixelSpacing = [
                            floatval($ps[0]),
                            floatval($ps[1])
                        ];
                    }
                }

                // ✅ Numeric
                $samplesPerPixel = intval($meta['0028,0002'] ?? 1);
                $bitsAllocated = intval($meta['0028,0100'] ?? 16);
                $bitsStored = intval($meta['0028,0101'] ?? 15);
                $highBit = intval($meta['0028,0102'] ?? 14);
                $pixelRepresentation = intval($meta['0028,0103'] ?? 0);

                $windowCenter = floatval($meta['0028,1050'] ?? 2000);
                $windowWidth = floatval($meta['0028,1051'] ?? 4000);
                $rescaleSlope = floatval($meta['0028,1053'] ?? 1);
                $rescaleIntercept = floatval($meta['0028,1052'] ?? 0);

                $photo = strtoupper($meta['0028,0004'] ?? 'MONOCHROME2');

                // jika YBR / RGB → jangan pakai grayscale pipeline
                // FIX COLOR
                if (strpos($photo, 'YBR') !== false || $samplesPerPixel > 1) {
                    $photo = 'RGB';
                }

                // FIX WINDOW
                if ($photo !== 'MONOCHROME1' && $photo !== 'MONOCHROME2') {
                    $windowCenter = null;
                    $windowWidth = null;
                }

                // 🔥 OPTIONAL: skip format yang belum didukung
                // if (in_array($photo, ['YBR_FULL_422', 'RGB'])) continue;

                $nama = trim($study['nm_pasien']);
                $parts = explode(' ', $nama);

                // minimal: Given^Family
                if (count($parts) >= 2) {
                    $dicomName = $parts[1] . '^' . $parts[0];
                } else {
                    $dicomName = $parts[0];
                }


                $formattedInstances[] = [
                    'metadata' => [
                        'SOPInstanceUID' => $ins['sop_instance_uid'],
                        'SeriesInstanceUID' => $series['series_instance_uid'],
                        'StudyInstanceUID' => $study['study_instance_uid'],
                        'studyInstanceUIDs' => [$study['study_instance_uid']],

                        'StudyDate' => $formattedDate,
                        'StudyTime' => $formattedTime,

                        'PatientName' => $dicomName,
                        'PatientID' => $study['no_rkm_medis'],
                        // 'PatientID' => $dicomName,
                        'AccessionNumber' => $permintaan_radiologi['noorder'] ?? '',

                        'Rows' => $rows,
                        'Columns' => $cols,
                        'PixelSpacing' => $pixelSpacing,

                        'SamplesPerPixel' => $samplesPerPixel,
                        'PhotometricInterpretation' => $photo,

                        'BitsAllocated' => $bitsAllocated,
                        'BitsStored' => $bitsStored,
                        'HighBit' => $highBit,
                        'PixelRepresentation' => $pixelRepresentation,

                        'WindowCenter' => $windowCenter,
                        'WindowWidth' => $windowWidth,
                        'RescaleSlope' => $rescaleSlope,
                        'RescaleIntercept' => $rescaleIntercept,

                        'VOILUTFunction' => $meta['0028,1056'] ?? 'LINEAR',

                        'SOPClassUID' => $sopClass,
                    ],
                    'url' => 'wadouri:' . $fileUrl,
                    // 'metaku' => $meta
                ];
            }

            // ✅ hanya masukkan series kalau ada instance
            if (!empty($formattedInstances)) {

                // 🔥 VALIDASI MODALITY (biar tidak error OHIF)
                $allowedModalities = ['CR', 'DX', 'CT', 'MR', 'PT'];
                if (!in_array($seriesModality, $allowedModalities)) {
                    $seriesModality = 'CR';
                }

                $formattedSeries[] = [
                    'SeriesInstanceUID' => $series['series_instance_uid'],
                    'SeriesDescription' => $series['series_description'] ?: '',
                    'SeriesNumber' => 1,
                    'Modality' => $seriesModality,
                    'instances' => $formattedInstances
                ];
            }
        }

        $ohifJson = [
            'studies' => [
                [
                    'StudyInstanceUID' => $study['study_instance_uid'],
                    'studyInstanceUIDs' => [$study['study_instance_uid']],
                    'StudyDate' => $formattedDate,
                    'StudyTime' => $formattedTime,
                    'PatientName' => $dicomName,
                    'PatientID' => $study['no_rkm_medis'],
                    'AccessionNumber' => $permintaan_radiologi['noorder'],
                    'StudyDescription' => $study['description'] ?: '',
                    'series' => $formattedSeries
                ]
            ]
        ];

        echo json_encode($ohifJson);
        exit;
    }


    public function getDicomMetaFromDicom($filePath)
    {
        $meta = [];

        if (!file_exists($filePath)) {
            return $meta;
        }

        // 🔥 tag penting saja (biar cepat)
        $tags = [
            '0008,0016', // SOPClassUID
            '0008,0060', // Modality
            '0028,0010', // Rows
            '0028,0011', // Columns
            '0028,0002', // SamplesPerPixel
            '0028,0004', // PhotometricInterpretation
            '0028,0100', // BitsAllocated
            '0028,0101', // BitsStored
            '0028,0102', // HighBit
            '0028,0103', // PixelRepresentation
            '0028,1050', // WindowCenter
            '0028,1051', // WindowWidth
            '0028,1052', // RescaleIntercept
            '0028,1053', // RescaleSlope
            '0028,1056', // VOILUTFunction
            '0018,1164', // PixelSpacing
        ];

        // format: +P 0028,0010 +P 0028,0011 ...
        $tagString = implode(' ', array_map(fn($t) => "+P $t", $tags));

        // 🔥 gunakan shell_exec + export PATH
        $cmd = "export PATH=\$PATH:/usr/local/bin:/usr/bin:/opt/homebrew/bin && dcmdump $tagString "
            . escapeshellarg($filePath) . " 2>&1";

        $output = shell_exec($cmd);

        if (!$output) {
            return $meta;
        }

        foreach (explode("\n", $output) as $line) {

            // format: (0028,0010) US [1547]
            if (preg_match('/\(([\dA-Fa-f]{4},[\dA-Fa-f]{4})\)\s+\w+\s+\[(.*?)\]/', $line, $m)) {
                $tag = strtoupper($m[1]);
                $val = trim($m[2]);
                $meta[$tag] = $val;
            }
            // fallback: tanpa []
            elseif (preg_match('/\(([\dA-Fa-f]{4},[\dA-Fa-f]{4})\)\s+\w+\s+(.+)/', $line, $m)) {
                $tag = strtoupper($m[1]);
                $val = trim($m[2]);
                $meta[$tag] = trim($val, '[] ');
            }
        }

        return $meta;
    }

    private function getMetaValue($meta, $tag, $default = null)
    {
        return isset($meta[$tag]) && $meta[$tag] !== '' ? $meta[$tag] : $default;
    }

    private function parsePixelSpacing($val)
    {
        if (!$val)
            return [1, 1];

        // support "0.148, 0.148" atau "0.148\0.148"
        $val = str_replace(['\\'], ',', $val);
        $arr = array_map('trim', explode(',', $val));

        return [
            isset($arr[0]) ? (float) $arr[0] : 1,
            isset($arr[1]) ? (float) $arr[1] : (float) $arr[0]
        ];
    }

    private function getDicomMeta($instanceId)
    {
        $rows = $this->db('mlite_mini_pacs_instance_metadata')
            ->where('instance_id', $instanceId)
            ->toArray();

        $meta = [];

        foreach ($rows as $row) {
            $tag = trim($row['tag']);
            $val = trim($row['value']);

            // 🔥 penting: bersihkan tanda kutip
            $val = trim($val, "'\" ");

            $meta[$tag] = $val;
        }

        return $meta;
    }

    public function apiDicomFile($id)
    {
        if (!$this->_isMono()) {
            $response = $this->_remoteCall('GET', '/admin/api/mini_pacs/dicomfile/' . $id);
            header('Content-Type: application/dicom');
            echo $response;
            exit();
        }
        $instance = $this->db('mlite_mini_pacs_instance')->where('id', $id)->oneArray();

        if (!$instance || !file_exists($instance['file_path'])) {
            header("HTTP/1.0 404 Not Found");
            exit;
        }

        $file = $instance['file_path'];

        header('Content-Type: application/dicom');
        header('Content-Length: ' . filesize($file));
        header('Content-Disposition: inline; filename="' . basename($file) . '"');

        // 🔥 penting
        while (ob_get_level())
            ob_end_clean();

        readfile($file);
        exit;
    }

    private function _generateOID()
    {
        // Generate a strict 38-digit standard numeric sequence directly for DICOM RFC 2.25
        $digits = '';
        for ($i = 0; $i < 38; $i++) {
            $digits .= ($i === 0) ? random_int(1, 9) : random_int(0, 9);
        }
        return '2.25.' . $digits;
    }

    private function _extractAndSaveMetadata($instanceId, $filepath)
    {
        $binPath = $this->core->getSettings('mini_pacs', 'dcm_path') ?: '/usr/bin/';
        // Ensure trailing slash
        $binPath = rtrim($binPath, '/') . '/';
        $jsonOutput = shell_exec("export PATH=\$PATH:/usr/local/bin:/usr/bin:/opt/homebrew/bin && " . escapeshellarg($binPath . "dcm2json") . " -fc " . escapeshellarg($filepath) . " 2>&1");

        $metadataDecoded = json_decode($jsonOutput, true);
        if ($metadataDecoded) {
            try {
                $this->core->db()->pdo()->exec("DELETE FROM mlite_mini_pacs_instance_metadata WHERE instance_id = " . intval($instanceId));
                $metaInsertStmt = $this->core->db()->pdo()->prepare("INSERT INTO mlite_mini_pacs_instance_metadata (instance_id, tag, name, value) VALUES (?, ?, ?, ?)");

                foreach ($metadataDecoded as $tag => $data) {
                    $name = isset($data['vr']) ? $data['vr'] : '';
                    $valueStr = '';

                    if (isset($data['Value']) && is_array($data['Value'])) {
                        if (isset($data['Value'][0]) && is_array($data['Value'][0])) {
                            if (isset($data['Value'][0]['Alphabetic'])) {
                                $valueStr = $data['Value'][0]['Alphabetic'];
                            } else {
                                $valueStr = json_encode($data['Value']);
                            }
                        } else {
                            $valueStr = implode(', ', $data['Value']);
                        }
                    } elseif (isset($data['InlineBinary'])) {
                        $valueStr = '[InlineBinary Data]';
                    }

                    if (strlen($valueStr) > 60000) {
                        $valueStr = substr($valueStr, 0, 60000) . '...';
                    }

                    if (strlen($tag) == 8) {
                        $formattedTag = substr($tag, 0, 4) . ',' . substr($tag, 4, 4);
                    } else {
                        $formattedTag = $tag;
                    }

                    if (!empty($formattedTag) && $formattedTag !== '7FE0,0010') { // Skip pixel data itself
                        $metaInsertStmt->execute([$instanceId, strtoupper($formattedTag), $name, $valueStr]);
                    }
                }
            } catch (\Exception $e) {
                // Return gracefully silently if DB fails parsing.
            }
        }
    }
}
