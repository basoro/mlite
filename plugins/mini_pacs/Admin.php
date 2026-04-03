<?php

namespace Plugins\Mini_pacs;

use Systems\AdminModule;

class Admin extends AdminModule
{
    public function navigation()
    {
        return [
            'Manage PACS' => 'manage',
            'Pengaturan' => 'settings'
        ];
    }

    public function getManage()
    {
        $this->_addHeaderFiles();
        $this->assign['title'] = 'Manage PACS';
        return $this->draw('manage.html', ['pacs' => htmlspecialchars_array($this->assign)]);
    }

    public function getDetail($id)
    {
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
        $draw = $_POST['draw'] ?? 0;
        $start = $_POST['start'] ?? 0;
        $length = $_POST['length'] ?? 10;
        $search = $_POST['search']['value'] ?? '';

        $totalRecords = $this->db('mlite_mini_pacs_study')->count();

        $query = $this->db('mlite_mini_pacs_study')
            ->select(['mlite_mini_pacs_study.*', 'pasien.nm_pasien'])
            ->join('reg_periksa', 'reg_periksa.no_rawat = mlite_mini_pacs_study.no_rawat')
            ->join('pasien', 'pasien.no_rkm_medis = reg_periksa.no_rkm_medis');

        if ($search) {
            $query->like('pasien.nm_pasien', '%' . $search . '%')
                ->orLike('mlite_mini_pacs_study.no_rawat', '%' . $search . '%')
                ->orLike('mlite_mini_pacs_study.modality', '%' . $search . '%');
        }

        $filteredRows = $query->toArray();
        $filteredRecords = count($filteredRows);

        $data = $query->desc('mlite_mini_pacs_study.id')
            ->offset($start)
            ->limit($length)
            ->toArray();

        $result = [];
        foreach ($data as $row) {
            $row['view_url'] = url([ADMIN, 'mini_pacs', 'viewer', $row['id']]);
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

    public function anyDelete($id = null)
    {
        if ($this->db('mlite_mini_pacs_study')->where('id', $id)->delete()) {
            $this->notify('success', 'Hapus sukses');
        } else {
            $this->notify('failure', 'Hapus gagal');
        }
        redirect(url([ADMIN, 'mini_pacs', 'manage']));
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

            $mlite_satu_sehat_response = $this->db('mlite_satu_sehat_response')->where('no_rawat', $study['no_rawat'])->oneArray();

            $permintaan_radiologi = $this->db('permintaan_radiologi')->where('no_rawat', $study['no_rawat'])->oneArray();

            $pasien = $this->db('reg_periksa')
                ->join('pasien', 'pasien.no_rkm_medis = reg_periksa.no_rkm_medis')
                ->where('reg_periksa.no_rawat', $study['no_rawat'])
                ->oneArray();
            $no_ktp_pasien = isset($pasien['no_ktp']) ? $pasien['no_ktp'] : '';

            // 2. Kirim ImagingStudy
            $fhirResult = $client->sendImagingStudy([
                // 'patientId' => $this->getPatient($no_ktp_pasien) ?: 'P01341663942', // Mohon sesuaikan
                'patientId' => 'P01341663942', // Mohon sesuaikan
                // 'encounterId' => $mlite_satu_sehat_response['id_encounter'] ?: '0771b77b-52b0-45f4-8201-95ec91b15df7', // Mohon sesuaikan
                'encounterId' => '0771b77b-52b0-45f4-8201-95ec91b15df7', // Mohon sesuaikan
                // 'serviceRequestId' => $mlite_satu_sehat_response['id_service_request'] ?: '31dab331-5350-4c8e-9ce9-1a9f4138dd8f', // Mohon sesuaikan
                'serviceRequestId' => '31dab331-5350-4c8e-9ce9-1a9f4138dd8f', // Mohon sesuaikan
                'noRawat' => $study['no_rawat'],
                // 'noOrder' => $permintaan_radiologi['no_order'] ?: 'PR202604030012',
                'noOrder' => 'PR202604030012',
                'studyUID' => $upload['study_uid'] ?: $study['study_instance_uid'],
                'seriesUID' => $series['series_instance_uid'],
                'instanceUID' => $instance['sop_instance_uid']
            ]);

            $fhirString = $fhirResult['response'];
            $fhirPayload = $fhirResult['payload'];

            // Percantik format string JSON jika memungkinkan
            $fhirArr = json_decode($fhirString, true);
            $fhirPretty = $fhirArr ? json_encode($fhirArr, JSON_PRETTY_PRINT) : $fhirString;

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

    public function getPatient($nik_pasien)
    {

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->fhirurl . '/Patient?identifier=https://fhir.kemkes.go.id/id/nik|' . $nik_pasien,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_HTTPHEADER => array('Content-Type: application/json', 'Authorization: Bearer ' . $this->getAccessToken()),
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
        return $this->draw('settings.html', ['settings' => htmlspecialchars_array($this->assign)]);
    }

    public function postSaveSettings()
    {
        $this->db('settings')->where('module', 'mini_pacs')->where('field', 'ae_title')->save(['value' => $_POST['ae_title']]);
        $this->db('settings')->where('module', 'mini_pacs')->where('field', 'target_aet')->save(['value' => $_POST['target_aet']]);
        $this->db('settings')->where('module', 'mini_pacs')->where('field', 'target_ip')->save(['value' => $_POST['target_ip']]);
        $this->db('settings')->where('module', 'mini_pacs')->where('field', 'target_port')->save(['value' => $_POST['target_port']]);
        $this->notify('success', 'Pengaturan DCMTK & PACS berhasil disimpan');
        redirect(url([ADMIN, 'mini_pacs', 'settings']));
    }

    public function apiStore()
    {
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
        $no_rawat = $_POST['no_rawat'];
        $modality = $_POST['modality'] ?: 'CR';
        $file = $_FILES['file_image'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->notify('failure', 'Upload error');
            redirect(url([ADMIN, 'mini_pacs', 'manage']));
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

            $studyId = $this->db('mlite_mini_pacs_study')->save([
                'no_rawat' => $no_rawat,
                'study_instance_uid' => $studyInstanceUid,
                'study_date' => date('Y-m-d H:i:s'),
                'modality' => $modality,
                'description' => 'Converted Image'
            ]);

            $seriesId = $this->db('mlite_mini_pacs_series')->save([
                'study_id' => $studyId,
                'series_instance_uid' => $seriesInstanceUid,
                'series_description' => 'Image to DICOM'
            ]);

            $this->db('mlite_mini_pacs_instance')->save([
                'series_id' => $seriesId,
                'sop_instance_uid' => $sopInstanceUid,
                'file_path' => $outFile
            ]);

            $this->notify('success', 'Konversi berhasil');
        } else {
            $this->notify('failure', 'Gagal convert image ke DICOM. LOG: ' . $output);
        }

        redirect(url([ADMIN, 'mini_pacs', 'manage']));
    }

    public function postApiUpload()
    {
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

            $studyId = $this->db('mlite_mini_pacs_study')->save([
                'no_rawat' => $no_rawat,
                'study_instance_uid' => $studyInstanceUid,
                'study_date' => date('Y-m-d H:i:s'),
                'modality' => $modality,
                'description' => 'Converted Image'
            ]);

            $seriesId = $this->db('mlite_mini_pacs_series')->save([
                'study_id' => $studyId,
                'series_instance_uid' => $seriesInstanceUid,
                'series_description' => 'Image to DICOM'
            ]);

            $this->db('mlite_mini_pacs_instance')->save([
                'series_id' => $seriesId,
                'sop_instance_uid' => $sopInstanceUid,
                'file_path' => $outFile
            ]);

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

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES . '/mini_pacs/js/admin/mini_pacs.js');
        exit();
    }

    private function _addHeaderFiles()
    {
        $this->core->addCSS(url('assets/css/dataTables.bootstrap.min.css'));
        $this->core->addJS(url('assets/jscripts/jquery.dataTables.min.js'));
        $this->core->addJS(url('assets/jscripts/dataTables.bootstrap.min.js'));
        $this->core->addJS(url([ADMIN, 'mini_pacs', 'javascript']), 'footer');
    }
    public function getViewer($studyId = null)
    {
        if (!$studyId) {
            redirect(url([ADMIN, 'mini_pacs', 'manage']));
        }
        $study = $this->db('mlite_mini_pacs_study')->where('id', $studyId)->oneArray();
        if (!$study) {
            redirect(url([ADMIN, 'mini_pacs', 'manage']));
        }

        $this->assign['studyId'] = $studyId;
        $this->assign['baseUrl'] = url() . '/plugins/mini_pacs';
        echo $this->draw('viewer.html', $this->assign);
        exit();
    }

    public function getJavascriptviewer($studyId)
    {
        header('Content-type: text/javascript');

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
            cornerstone.enable(dicomImageElement);

            if(studyData.length > 0 && studyData[0].instances.length > 0) {
                const imageId = 'wadouri:' + studyData[0].instances[0];

                cornerstone.loadAndCacheImage(imageId).then(function(image) {
                    cornerstone.displayImage(dicomImageElement, image);
                    
                    if(image.data) {
                        try {
                            var metadataHtml = '<table class=\\'table table-bordered table-condensed\\' style=\\'color:#ddd;font-size:11px;margin:0;background:transparent;\\'>';
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
                                    metadataHtml += '<tr><td width=\\'40%\\'><b>'+tags[tag]+'</b></td><td>'+val+'</td></tr>';
                                }
                            }
                            
                            metadataHtml += '<tr><td><b>Resolution</b></td><td>'+image.width+' x '+image.height+'</td></tr>';
                            metadataHtml += '<tr><td><b>Window (C/W)</b></td><td>'+(image.windowCenter||'-')+' / '+(image.windowWidth||'-')+'</td></tr>';
                            metadataHtml += '</table>';
                            
                            document.getElementById('dicomMetadata').innerHTML = metadataHtml;
                            
                            var ptName = image.data.string('x00100010') || '';
                            var modality = image.data.string('x00080060') || '';
                            var bottomleft = document.getElementById('bottomleft');
                            if(bottomleft) bottomleft.innerHTML = ptName;
                            
                            var topleft = document.getElementById('topleft');
                            if(topleft) topleft.innerHTML = '<div style=\\'color: #00ff00; font-weight: bold;\\'>Modality: '+modality+'</div>';
                            
                        } catch(e) {
                            console.log('Error parsing metadata', e);
                        }
                    }
                    
                    cornerstoneTools.addStackStateManager(dicomImageElement, ['stack']);
                    cornerstoneTools.addToolState(dicomImageElement, 'stack', {
                        currentImageIdIndex: 0,
                        imageIds: studyData[0].instances.map(url => 'wadouri:' + url)
                    });

                    const WwwcTool = cornerstoneTools.WwwcTool;
                    cornerstoneTools.addTool(WwwcTool);
                    cornerstoneTools.setToolActive('Wwwc', { mouseButtonMask: 1 });

                    const ZoomTool = cornerstoneTools.ZoomTool;
                    cornerstoneTools.addTool(ZoomTool);
                    cornerstoneTools.setToolActive('Zoom', { mouseButtonMask: 2 });
                    
                    const PanTool = cornerstoneTools.PanTool;
                    cornerstoneTools.addTool(PanTool);
                    cornerstoneTools.setToolActive('Pan', { mouseButtonMask: 4 });

                }, function(err) {
                    alert('Error loading DICOM image: ' + err);
                });
            } else {
                alert('No Instances found in this study');
            }
        ";
        exit();
    }

    public function apiDicomFile($instanceId)
    {
        $instance = $this->db('mlite_mini_pacs_instance')->where('id', $instanceId)->oneArray();
        if (!$instance || !file_exists($instance['file_path'])) {
            header("HTTP/1.0 404 Not Found");
            exit();
        }

        $file = $instance['file_path'];
        header('Content-Type: application/dicom');
        header('Content-Length: ' . filesize($file));
        header('Cache-Control: max-age=31536000');
        readfile($file);
        exit();
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
}
