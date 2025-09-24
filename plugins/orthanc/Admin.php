<?php
namespace Plugins\Orthanc;

use Systems\AdminModule;

class Admin extends AdminModule
{

    public function navigation()
    {
        return [
            'Manage' => 'manage',
            'Settings' => 'settings',
            'Studies' => 'studies',
        ];
    }

    public function getManage()
    {
      $sub_modules = [
        ['name' => 'Settings', 'url' => url([ADMIN, 'orthanc', 'settings']), 'icon' => 'cubes', 'desc' => 'Pengaturan Orthanc'],
        ['name' => 'Studies', 'url' => url([ADMIN, 'orthanc', 'studies']), 'icon' => 'cubes', 'desc' => 'Data studies'],
      ];
      return $this->draw('manage.html', ['sub_modules' => $sub_modules]);
    }

    public function getSettings()
    {
      $orthanc['server'] = $this->settings->get('orthanc.server');
      $orthanc['username'] = $this->settings->get('orthanc.username');
      $orthanc['password'] = $this->settings->get('orthanc.password');
      return $this->draw('settings.html', ['orthanc' => $orthanc]);
    }

    public function postSaveSettings()
    {
        foreach ($_POST['orthanc'] as $key => $val) {
            $this->settings('orthanc', $key, $val);
        }

        $orthanc['server'] = $this->settings->get('orthanc.server');
        $orthanc['username'] = $this->settings->get('orthanc.username');
        $orthanc['password'] = $this->settings->get('orthanc.password');
        $this->notify('success', 'Pengaturan telah disimpan');
        redirect(url([ADMIN, 'orthanc', 'manage']));
    }

    public function getBridgingOrthanc($no_rawat, $status='', $tgl_periksa='', $jam='')
    {
      $this->_addHeaderFiles();

      $orthanc = $this->settings->get('orthanc.server');

      $pacs['data'] = $this->core->getRegPeriksaInfo('no_rkm_medis', revertNoRawat($no_rawat));

      $curl = curl_init();
      curl_setopt ($curl, CURLOPT_URL, $orthanc . '/tools/lookup');
      curl_setopt ($curl, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt ($curl, CURLOPT_USERPWD, $this->settings->get('orthanc.username').":".$this->settings->get('orthanc.password'));
      curl_setopt ($curl, CURLOPT_TIMEOUT, 30);
      curl_setopt ($curl, CURLOPT_POST, 1);
      curl_setopt ($curl, CURLOPT_POSTFIELDS, $pacs['data']);
      $resp = curl_exec($curl);
      curl_close($curl);

      $patient = json_decode($resp, TRUE);

      // Add null check before accessing array elements
      if (!empty($patient) && isset($patient[0]) && isset($patient[0]["ID"])) {
          $pacs['patientUUID'] = $patient[0]["ID"];
      } else {
          $pacs['patientUUID'] = "";
      }

      if ($pacs['patientUUID'] != "") {

        $curl = curl_init();
        curl_setopt ($curl, CURLOPT_URL, $orthanc . '/patients/' . $pacs['patientUUID']);
        curl_setopt ($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($curl, CURLOPT_USERPWD, $this->settings->get('orthanc.username').":".$this->settings->get('orthanc.password'));
        curl_setopt ($curl, CURLOPT_TIMEOUT, 30);
        $resp = curl_exec($curl);
        curl_close($curl);

        $study = json_decode($resp, TRUE);
        //echo json_encode($study);

        // Add null check before accessing array elements
        if (!empty($study) && isset($study["Studies"]) && isset($study["Studies"][0])) {
            $pacs['Studies'] = $study["Studies"][0];
        } else {
            $pacs['Studies'] = "";
        }

        if($pacs['Studies'] != "") {
          $curl = curl_init();
          curl_setopt ($curl, CURLOPT_URL, $orthanc . '/studies/' . $pacs['Studies']);
          curl_setopt ($curl, CURLOPT_RETURNTRANSFER, 1);
          curl_setopt ($curl, CURLOPT_USERPWD, $this->settings->get('orthanc.username').":".$this->settings->get('orthanc.password'));
          curl_setopt ($curl, CURLOPT_TIMEOUT, 30);
          $resp = curl_exec($curl);
          curl_close($curl);

          $series = json_decode($resp, TRUE);
          //echo json_encode($series);

          // Add null check before accessing array elements
          if (!empty($series) && isset($series["Series"])) {
              $pacs['Series'] = json_encode($series["Series"]);
          } else {
              $pacs['Series'] = "";
          }

          //echo $pacs['Series'];

          if($pacs['Series'] != "") {
            foreach (json_decode($pacs['Series'], true) as $series) {
              $curl = curl_init();
              curl_setopt ($curl, CURLOPT_URL, $orthanc . '/series/' . $series);
              curl_setopt ($curl, CURLOPT_RETURNTRANSFER, 1);
              curl_setopt ($curl, CURLOPT_USERPWD, $this->settings->get('orthanc.username').":".$this->settings->get('orthanc.password'));
              curl_setopt ($curl, CURLOPT_TIMEOUT, 30);
              $resp = curl_exec($curl);
              curl_close($curl);

              $Instances = json_decode($resp, TRUE);
              //echo json_encode($Instances);

              $pacs['Instances'][] = $Instances;
              //echo $pacs['Instances'];
              /*
              if($pacs['Instances'] != "") {
                foreach ($pacs['Instances'] as $instances) {
                  $curl = curl_init();
                  curl_setopt ($curl, CURLOPT_URL, $orthanc . '/instances/' . $instances);
                  curl_setopt ($curl, CURLOPT_RETURNTRANSFER, 1);
                  curl_setopt ($curl, CURLOPT_USERPWD, $this->settings->get('orthanc.username').":".$this->settings->get('orthanc.password'));
                  curl_setopt ($curl, CURLOPT_TIMEOUT, 30);
                  $resp = curl_exec($curl);
                  curl_close($curl);

                  $pacs['instances'] = json_decode($resp, TRUE);
                  //echo json_encode($pacs['instances']);

                  //$pacs['url'] = '';
                  //$pacs['url'] = $orthanc . '/instances/' . $pacs['instances']['ID'] . '/rendered/?width=200';

                }

              }
              */

            }

          }

        }

      }

      $this->tpl->set('pacs', $pacs);
      $this->tpl->set('orthanc', $orthanc);
      $this->tpl->set('status', $status);
      $this->tpl->set('tgl_periksa', $tgl_periksa);
      $this->tpl->set('jam', $jam);

      echo $this->tpl->draw(MODULES.'/orthanc/view/admin/orthanc.html', true);
      exit();
    }

    public function postSavePACS()
    {
      if(isset($_POST["image_url"]))
      {
       $message = '';
       $image = '';
       if(filter_var($_POST["image_url"], FILTER_VALIDATE_URL))
       {
         $auth = base64_encode($this->settings->get('orthanc.username').":".$this->settings->get('orthanc.password'));
         $context = stream_context_create([
             "http" => [
                 "header" => "Authorization: Basic $auth"
             ]
         ]);
         $image_data = file_get_contents($_POST["image_url"], false, $context);
         $filename = time().'.png';
         $new_image_path = WEBAPPS_PATH.'/radiologi/pages/upload/'.$filename;
         file_put_contents($new_image_path, $image_data);
         $message = 'Hasil PACS telah disimpan ke server SIMRS';
         $result = $this->db('gambar_radiologi')
           ->save([
             'no_rawat' => $_POST['no_rawat'],
             'tgl_periksa' => $_POST['tgl_periksa'],
             'jam' => $_POST['jam_periksa'],
             'lokasi_gambar' => 'pages/upload/'.$filename
           ]);

       }
       else
       {
        $message = 'Invalid Url';
       }
       $output = array(
        'message' => $message,
        'image'  => $image
       );
       echo json_encode($output);
      }
      exit();
    }

    public function postSaveHasilBaca()
    {
      if(isset($_POST['hasil']) && $_POST['hasil'] != '') {
        $result = $this->db('hasil_radiologi')
          ->save([
            'no_rawat' => $_POST['no_rawat'],
            'tgl_periksa' => $_POST['tgl_periksa'],
            'jam' => $_POST['jam_periksa'],
            'hasil' => $_POST['hasil']
          ]);
        if($result) {
          $message = 'sukses';
          $code = '200';
        } else {
          $message = 'error';
          $code = '201';
        }
      } else {
        $message = 'error';
        $code = '201';
      }
      $output = array(
       'message' => $message,
       'code'  => $code
      );
      echo json_encode($output);
      exit();
    }

    public function getStudies()
    {
        // Ambil konfigurasi Orthanc
        $orthanc = [
            'server' => $this->settings->get('orthanc.server'),
            'username' => $this->settings->get('orthanc.username'),
            'password' => $this->settings->get('orthanc.password')
        ];
        
        // Validasi konfigurasi
        $configError = $this->validateOrthancConfig($orthanc);
        if ($configError) {
            $error = $configError;
            $studies = [];
            return $this->draw('studies.html', ['orthanc' => $orthanc]);
        }
        
        // Ambil parameter dari GET request
        $studyDate = $_GET['StudyDate'] ?? date('Ymd'); // Default hari ini
        $limit = intval($_GET['limit'] ?? 50); // Default 50 studies per page
        $offset = intval($_GET['since'] ?? 0); // Default offset 0
        $fromDate = $_GET['from_date'] ?? null;
        $toDate = $_GET['to_date'] ?? null;
        
        // Validasi dan format parameter
        if ($limit <= 0) $limit = 50;
        if ($limit > 200) $limit = 200; // Maksimal 200 untuk performa
        if ($offset < 0) $offset = 0;
        
        // Format date range jika ada
        if ($fromDate && $toDate) {
            // Convert dari format YYYY-MM-DD ke YYYYMMDD
            $fromDateFormatted = str_replace('-', '', $fromDate);
            $toDateFormatted = str_replace('-', '', $toDate);
            $studyDate = $fromDateFormatted . '-' . $toDateFormatted;
        } elseif ($fromDate) {
            $studyDate = str_replace('-', '', $fromDate);
        } elseif ($toDate) {
            $studyDate = str_replace('-', '', $toDate);
        }
        
        // Ambil daftar studies dari Orthanc
        $studies = [];
        $error = null;
        $totalStudies = 0;
        
        try {
            // Buat URL dengan query parameters
            $queryParams = [
                'StudyDate' => $studyDate,
                'limit' => $limit,
                'since' => $offset,
                'expand' => 'true'
            ];
            
            $url = $orthanc['server'] . '/studies?' . http_build_query($queryParams);
            
            $curl = $this->setupCurl($url, $orthanc);
            $resp = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $curlError = curl_error($curl);
            $curlErrno = curl_errno($curl);
            curl_close($curl);
            
            if ($curlErrno !== 0) {
                $errorMessage = $this->getCurlErrorMessage($curlErrno, $curlError);
                $error = 'Koneksi gagal: ' . $errorMessage['message'];
            } elseif ($httpCode === 200) {
                $studiesData = json_decode($resp, true);
                
                if (is_array($studiesData)) {
                    foreach ($studiesData as $study) {
                        // Jika expand=true, data sudah lengkap
                        if (isset($study['MainDicomTags'])) {
                            $studies[] = [
                                'ID' => $study['ID'],
                                'MainDicomTags' => $study['MainDicomTags'] ?? [],
                                'PatientMainDicomTags' => $study['PatientMainDicomTags'] ?? [],
                                'Series' => $study['Series'] ?? [],
                                'Instances' => $study['Instances'] ?? [],
                                'SeriesCount' => count($study['Series'] ?? []),
                                'InstancesCount' => count($study['Instances'] ?? []),
                                'StudyDate' => $this->formatDicomDate($study['MainDicomTags']['StudyDate'] ?? ''),
                                'StudyTime' => $this->formatDicomTime($study['MainDicomTags']['StudyTime'] ?? ''),
                                'PatientName' => $study['PatientMainDicomTags']['PatientName'] ?? 'Unknown',
                                'PatientID' => $study['PatientMainDicomTags']['PatientID'] ?? 'Unknown',
                                'StudyDescription' => $study['MainDicomTags']['StudyDescription'] ?? 'No Description',
                                'Modality' => $study['MainDicomTags']['ModalitiesInStudy'] ?? 'Unknown'
                            ];
                        } else {
                            // Fallback jika expand tidak bekerja
                            $studyDetail = $this->getStudyDetail($study, $orthanc);
                            if ($studyDetail) {
                                $studies[] = $studyDetail;
                            }
                        }
                    }
                    
                    $totalStudies = count($studies);
                    
                    // Urutkan berdasarkan tanggal study (terbaru dulu)
                    usort($studies, function($a, $b) {
                        $dateA = $a['MainDicomTags']['StudyDate'] ?? '19700101';
                        $dateB = $b['MainDicomTags']['StudyDate'] ?? '19700101';
                        return strcmp($dateB, $dateA);
                    });
                }
            } else {
                $errorMessage = $this->getHttpErrorMessage($httpCode, $resp);
                $error = 'Server error: ' . $errorMessage['message'];
            }
        } catch (\Exception $e) {
            $error = 'Error: ' . $e->getMessage();
        }
        
        // Hitung pagination info
        $currentPage = floor($offset / $limit) + 1;
        $hasNextPage = count($studies) >= $limit;
        $hasPrevPage = $offset > 0;
        
        $pagination = [
            'current_page' => $currentPage,
            'limit' => $limit,
            'offset' => $offset,
            'has_next' => $hasNextPage,
            'has_prev' => $hasPrevPage,
            'next_offset' => $hasNextPage ? $offset + $limit : null,
            'prev_offset' => $hasPrevPage ? max(0, $offset - $limit) : null,
            'study_date' => $studyDate,
            'from_date' => $fromDate,
            'to_date' => $toDate
        ];
        
        $this->tpl->set('studies', $studies);
        $this->tpl->set('error', $error);
        $this->tpl->set('total_studies', $totalStudies);
        $this->tpl->set('pagination', $pagination);
        
        return $this->draw('studies.html', [
            'orthanc' => $orthanc, 
            'baseUrl' => url().'/'.ADMIN.'/orthanc',
            'pagination' => $pagination
        ]);
    }
    
    private function getStudyDetail($studyId, $orthanc)
    {
        try {
            $curl = $this->setupCurl($orthanc['server'] . '/studies/' . $studyId, $orthanc);
            $resp = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);
            
            if ($httpCode === 200) {
                $study = json_decode($resp, true);
                
                // Format data untuk template
                return [
                    'ID' => $studyId,
                    'MainDicomTags' => $study['MainDicomTags'] ?? [],
                    'PatientMainDicomTags' => $study['PatientMainDicomTags'] ?? [],
                    'Series' => $study['Series'] ?? [],
                    'Instances' => $study['Instances'] ?? [],
                    'SeriesCount' => count($study['Series'] ?? []),
                    'InstancesCount' => count($study['Instances'] ?? []),
                    'StudyDate' => $this->formatDicomDate($study['MainDicomTags']['StudyDate'] ?? ''),
                    'StudyTime' => $this->formatDicomTime($study['MainDicomTags']['StudyTime'] ?? ''),
                    'PatientName' => $study['PatientMainDicomTags']['PatientName'] ?? 'Unknown',
                    'PatientID' => $study['PatientMainDicomTags']['PatientID'] ?? 'Unknown',
                    'StudyDescription' => $study['MainDicomTags']['StudyDescription'] ?? 'No Description',
                    'Modality' => $study['MainDicomTags']['ModalitiesInStudy'] ?? 'Unknown'
                ];
            }
        } catch (\Exception $e) {
            // Log error tapi jangan stop proses
            error_log('Error getting study detail for ' . $studyId . ': ' . $e->getMessage());
        }
        
        return null;
    }

    public function getSeries($studyId = null)
    {
        // Ambil konfigurasi Orthanc
        $orthanc = [
            'server' => $this->settings->get('orthanc.server'),
            'username' => $this->settings->get('orthanc.username'),
            'password' => $this->settings->get('orthanc.password')
        ];
        
        // Validasi parameter studyId
        if (!$studyId) {
            $studyId = $_GET['study'] ?? null;
        }
        
        if (!$studyId) {
            $this->tpl->set('error', 'Study ID tidak ditemukan');
            $this->tpl->set('series', []);
            return $this->draw('series.html', ['orthanc' => $orthanc]);
        }
        
        // Validasi konfigurasi Orthanc
        $configError = $this->validateOrthancConfig($orthanc);
        if ($configError) {
            $this->tpl->set('error', $configError);
            $this->tpl->set('series', []);
            return $this->draw('series.html', ['orthanc' => $orthanc]);
        }
        
        // Ambil daftar series dari study
        $series = [];
        $error = null;
        $studyInfo = null;
        
        try {
            // Ambil informasi study terlebih dahulu
            $studyInfo = $this->getStudyDetail($studyId, $orthanc);
            
            if ($studyInfo && !empty($studyInfo['Series'])) {
                foreach ($studyInfo['Series'] as $seriesId) {
                    $seriesDetail = $this->getSeriesDetail($seriesId, $orthanc);
                    if ($seriesDetail) {
                        $series[] = $seriesDetail;
                    }
                }
                
                // Urutkan berdasarkan series number
                usort($series, function($a, $b) {
                    $numA = intval($a['SeriesNumber'] ?? 0);
                    $numB = intval($b['SeriesNumber'] ?? 0);
                    return $numA - $numB;
                });
            } else {
                $error = 'Study tidak ditemukan atau tidak memiliki series';
            }
            
        } catch (\Exception $e) {
            $error = 'Error: ' . $e->getMessage();
        }
        
        $this->tpl->set('series', $series);
        $this->tpl->set('error', $error);
        $this->tpl->set('studyInfo', $studyInfo);
        $this->tpl->set('studyId', $studyId);
        $this->tpl->set('total_series', count($series));
        
        return $this->draw('series.html', ['orthanc' => $orthanc, 'baseUrl' => url().'/'.ADMIN.'/orthanc']);
    }
    
    private function getSeriesDetail($seriesId, $orthanc)
    {
        try {
            $curl = $this->setupCurl($orthanc['server'] . '/series/' . $seriesId, $orthanc);
            $resp = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);
            
            if ($httpCode === 200) {
                $series = json_decode($resp, true);
                
                return [
                    'ID' => $seriesId,
                    'MainDicomTags' => $series['MainDicomTags'] ?? [],
                    'Instances' => $series['Instances'] ?? [],
                    'InstancesCount' => count($series['Instances'] ?? []),
                    'SeriesNumber' => $series['MainDicomTags']['SeriesNumber'] ?? 'N/A',
                    'SeriesDescription' => $series['MainDicomTags']['SeriesDescription'] ?? 'No Description',
                    'Modality' => $series['MainDicomTags']['Modality'] ?? 'Unknown',
                    'SeriesDate' => $this->formatDicomDate($series['MainDicomTags']['SeriesDate'] ?? ''),
                    'SeriesTime' => $this->formatDicomTime($series['MainDicomTags']['SeriesTime'] ?? ''),
                    'BodyPartExamined' => $series['MainDicomTags']['BodyPartExamined'] ?? 'N/A',
                    'SliceThickness' => $series['MainDicomTags']['SliceThickness'] ?? 'N/A'
                ];
            }
        } catch (\Exception $e) {
            error_log('Error getting series detail for ' . $seriesId . ': ' . $e->getMessage());
        }
        
        return null;
    }

    public function getViewer()
    {
      $this->core->addJS(url([ADMIN, 'orthanc', 'javascript']), 'footer');
      echo $this->draw('viewer.html', ['baseUrl' => url().'/plugins/orthanc']);
      exit();
    }

    private function formatDicomDate($dicomDate)
    {
        if (empty($dicomDate) || strlen($dicomDate) < 8) {
            return '-';
        }
        
        $year = substr($dicomDate, 0, 4);
        $month = substr($dicomDate, 4, 2);
        $day = substr($dicomDate, 6, 2);
        
        return $day . '/' . $month . '/' . $year;
    }
    
    private function formatDicomTime($dicomTime)
    {
        if (empty($dicomTime) || strlen($dicomTime) < 6) {
            return '-';
        }
        
        $hour = substr($dicomTime, 0, 2);
        $minute = substr($dicomTime, 2, 2);
        $second = substr($dicomTime, 4, 2);
        
        return $hour . ':' . $minute . ':' . $second;
    }

    public function postTestConnection()
    {
        // Gunakan parameter dari form jika ada, jika tidak gunakan dari settings
        $orthanc = [
            'server' => isset($_POST['server']) ? $_POST['server'] : $this->settings->get('orthanc.server'),
            'username' => isset($_POST['username']) ? $_POST['username'] : $this->settings->get('orthanc.username'),
            'password' => isset($_POST['password']) ? $_POST['password'] : $this->settings->get('orthanc.password')
        ];
        
        // Validasi konfigurasi
        $configError = $this->validateOrthancConfig($orthanc);
        if ($configError) {
            echo json_encode([
                'success' => false,
                'message' => $configError
            ]);
            exit();
        }
        
        // Test koneksi dengan endpoint system
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $orthanc['server'] . '/system');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_USERPWD, $orthanc['username'].":".$orthanc['password']);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        
        $resp = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlError = curl_error($curl);
        $curlErrno = curl_errno($curl);
        curl_close($curl);
        
        if ($curlErrno !== 0) {
            $errorMessage = $this->getCurlErrorMessage($curlErrno, $curlError);
            echo json_encode([
                'success' => false,
                'message' => 'Koneksi gagal: ' . $errorMessage['message'],
                'troubleshooting' => $errorMessage['troubleshooting']
            ]);
        } elseif ($httpCode === 200) {
            $system = json_decode($resp, TRUE);
            echo json_encode([
                'success' => true,
                'message' => 'Koneksi berhasil',
                'data' => [
                    'version' => $system['Version'] ?? 'Unknown',
                    'name' => $system['Name'] ?? 'Orthanc'
                ]
            ]);
        } else {
            $errorMessage = $this->getHttpErrorMessage($httpCode, $resp);
            echo json_encode([
                'success' => false,
                'message' => 'Server error: ' . $errorMessage['message'],
                'troubleshooting' => $errorMessage['troubleshooting']
            ]);
        }
        exit();
    }
    
    private function validateOrthancConfig($orthanc)
    {
        if (empty($orthanc['server'])) {
            return 'Server Orthanc belum dikonfigurasi';
        }
        
        if (empty($orthanc['username']) || empty($orthanc['password'])) {
            return 'Username atau password Orthanc belum dikonfigurasi';
        }
        
        // Validasi format URL
        if (!filter_var($orthanc['server'], FILTER_VALIDATE_URL)) {
            return 'Format URL server tidak valid. Contoh: http://localhost:8042';
        }
        
        return null;
    }
    
    private function getCurlErrorMessage($errno, $error)
    {
        $messages = [
            CURLE_COULDNT_CONNECT => [
                'message' => 'Tidak dapat terhubung ke server Orthanc',
                'troubleshooting' => 'Periksa apakah server Orthanc berjalan dan URL sudah benar'
            ],
            CURLE_OPERATION_TIMEDOUT => [
                'message' => 'Koneksi timeout',
                'troubleshooting' => 'Server terlalu lama merespons, periksa koneksi jaringan'
            ],
            CURLE_COULDNT_RESOLVE_HOST => [
                'message' => 'Tidak dapat menemukan host server',
                'troubleshooting' => 'Periksa URL server dan koneksi DNS'
            ],
            CURLE_SSL_CONNECT_ERROR => [
                'message' => 'Error koneksi SSL',
                'troubleshooting' => 'Periksa konfigurasi SSL server Orthanc'
            ]
        ];
        
        return $messages[$errno] ?? [
            'message' => 'Error koneksi: ' . $error,
            'troubleshooting' => 'Periksa konfigurasi server dan jaringan'
        ];
    }
    
    private function getHttpErrorMessage($httpCode, $response)
    {
        $messages = [
            401 => [
                'message' => 'Autentikasi gagal',
                'troubleshooting' => 'Periksa username dan password Orthanc'
            ],
            403 => [
                'message' => 'Akses ditolak',
                'troubleshooting' => 'User tidak memiliki permission untuk mengakses resource'
            ],
            404 => [
                'message' => 'Endpoint tidak ditemukan',
                'troubleshooting' => 'Periksa versi Orthanc, mungkin endpoint tidak tersedia'
            ],
            500 => [
                'message' => 'Internal server error',
                'troubleshooting' => 'Periksa log server Orthanc untuk detail error'
            ],
            503 => [
                'message' => 'Service unavailable',
                'troubleshooting' => 'Server Orthanc sedang overload atau maintenance'
            ]
        ];
        
        return $messages[$httpCode] ?? [
            'message' => 'HTTP Error ' . $httpCode,
            'troubleshooting' => 'Periksa status server Orthanc dan konfigurasi'
        ];
    }

    private function setupCurl($url, $orthanc, $options = [])
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_USERPWD, $orthanc['username'].":".$orthanc['password']);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        
        // Apply additional options
        foreach ($options as $option => $value) {
            curl_setopt($curl, $option, $value);
        }
        
        return $curl;
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/orthanc/js/admin/scripts.js');
        exit();
    }

    public function getApi()
    {
        // Get the API path from URL
        $path = $_GET['path'] ?? '';
        
        if (empty($path)) {
            http_response_code(400);
            echo json_encode(['error' => 'API path is required']);
            exit();
        }
        
        // Get Orthanc configuration
        $orthanc = [
            'server' => $this->settings->get('orthanc.server'),
            'username' => $this->settings->get('orthanc.username'),
            'password' => $this->settings->get('orthanc.password')
        ];
        
        // Validate configuration
        $configError = $this->validateOrthancConfig($orthanc);
        if ($configError) {
            http_response_code(500);
            echo json_encode(['error' => $configError]);
            exit();
        }
        
        // Make request to Orthanc
        $url = rtrim($orthanc['server'], '/') . '/' . ltrim($path, '/');
        $curl = $this->setupCurl($url, $orthanc);
        
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($curl, CURLINFO_CONTENT_TYPE);
        $curlError = curl_error($curl);
        $curlErrno = curl_errno($curl);
        curl_close($curl);
        
        if ($curlErrno !== 0) {
            http_response_code(500);
            echo json_encode(['error' => 'Connection failed: ' . $curlError]);
            exit();
        }
        
        // Set appropriate content type
        if (strpos($contentType, 'application/json') !== false) {
            header('Content-Type: application/json');
        } elseif (strpos($contentType, 'application/dicom') !== false) {
            header('Content-Type: application/dicom');
            header('Content-Disposition: attachment; filename="dicom.dcm"');
        } else {
            header('Content-Type: ' . $contentType);
        }
        
        http_response_code($httpCode);
        echo $response;
        exit();
    }

    public function getJavascriptTools()
    {
        header('Content-type: text/javascript');
        $orthanc = [
            'server' => $this->settings->get('orthanc.server'),
            'username' => $this->settings->get('orthanc.username'),
            'password' => $this->settings->get('orthanc.password')
        ];
        echo $this->draw(MODULES.'/orthanc/js/admin/tools.js', ['orthanc' => $orthanc]);
        exit();
    }

    public function postGetstudydetails()
    {
        // Validate token
        if (!isset($_POST['token']) || $_POST['token'] !== $_SESSION['token']) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid token'
            ]);
            exit();
        }
        
        // Validate studyId
        if (!isset($_POST['studyId']) || empty($_POST['studyId'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Study ID is required'
            ]);
            exit();
        }
        
        $studyId = $_POST['studyId'];
        
        // Get Orthanc configuration
        $orthanc = [
            'server' => $this->settings->get('orthanc.server'),
            'username' => $this->settings->get('orthanc.username'),
            'password' => $this->settings->get('orthanc.password')
        ];
        
        // Validate configuration
        $configError = $this->validateOrthancConfig($orthanc);
        if ($configError) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => $configError
            ]);
            exit();
        }
        
        try {
            // Get study details from Orthanc
            $curl = $this->setupCurl($orthanc['server'] . '/studies/' . $studyId, $orthanc);
            $resp = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $curlError = curl_error($curl);
            $curlErrno = curl_errno($curl);
            curl_close($curl);
            
            if ($curlErrno !== 0) {
                $errorMessage = $this->getCurlErrorMessage($curlErrno, $curlError);
                echo json_encode([
                    'success' => false,
                    'message' => 'Connection failed: ' . $errorMessage['message']
                ]);
                exit();
            }
            
            if ($httpCode === 200) {
                $studyData = json_decode($resp, true);
                
                if ($studyData) {
                    echo json_encode([
                        'success' => true,
                        'data' => $studyData
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Invalid response from Orthanc server'
                    ]);
                }
            } elseif ($httpCode === 404) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Study not found'
                ]);
            } else {
                $errorMessage = $this->getHttpErrorMessage($httpCode, $resp);
                echo json_encode([
                    'success' => false,
                    'message' => 'Server error: ' . $errorMessage['message']
                ]);
            }
            
        } catch (\Exception $e) {
            // Clean any output buffers before JSON response
            if (ob_get_level()) {
                ob_end_clean();
            }
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
        
        exit();
    }
    
    public function postModifystudytags()
    {
        // Validate token
        if (!isset($_POST['token']) || $_POST['token'] !== $_SESSION['token']) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid token'
            ]);
            exit();
        }
        
        // Validate studyId
        if (!isset($_POST['studyId']) || empty($_POST['studyId'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Study ID is required'
            ]);
            exit();
        }
        
        $studyId = $_POST['studyId'];
        $modifyOption = $_POST['modifyOption'] ?? 'generate_new_uids';
        $tags = $_POST['tags'] ?? [];
        
        // Validate modifyOption
        $validOptions = ['generate_new_uids', 'keep_original_uids', 'create_copy'];
        if (!in_array($modifyOption, $validOptions)) {
            error_log('Invalid modifyOption received: ' . $modifyOption);
            echo json_encode([
                'success' => false,
                'message' => 'Invalid modify option: ' . $modifyOption
            ]);
            exit();
        }
        
        // Debug logging
        error_log('Received modifyOption: ' . $modifyOption);
        error_log('Received tags: ' . json_encode($tags));
        
        // Get Orthanc configuration
        $orthanc = [
            'server' => $this->settings->get('orthanc.server'),
            'username' => $this->settings->get('orthanc.username'),
            'password' => $this->settings->get('orthanc.password')
        ];
        
        // Validate configuration
        $configError = $this->validateOrthancConfig($orthanc);
        if ($configError) {
            echo json_encode([
                'success' => false,
                'message' => $configError
            ]);
            exit();
        }
        
        try {
            // Prepare modification request
            $modifyRequest = [
                'Replace' => [],
                'Remove' => [],
                'RemovePrivateTags' => false,
                'Force' => true
            ];
            
            // Map form field names to DICOM tags
            $tagMapping = [
                'PatientID' => '0010,0020',
                'PatientName' => '0010,0010',
                'PatientBirthDate' => '0010,0030',
                'PatientSex' => '0010,0040',
                'AccessionNumber' => '0008,0050',
                'InstitutionName' => '0008,0080',
                'ReferringPhysicianName' => '0008,0090',
                'RequestingPhysician' => '0032,1032',
                'RequestedProcedureDescription' => '0032,1060',
                'StudyDate' => '0008,0020',
                'StudyTime' => '0008,0030',
                'StudyID' => '0020,0010',
                'StudyDescription' => '0008,1030',
                'StudyInstanceUID' => '0020,000D'
            ];
            
            // Process tags
            foreach ($tags as $fieldName => $value) {
                if (!empty($value)) {
                    // Check if it's a direct DICOM tag (like "0008,0020") or a field name
                    if (preg_match('/^[0-9A-Fa-f]{4},[0-9A-Fa-f]{4}$/', $fieldName)) {
                        // Direct DICOM tag
                        $modifyRequest['Replace'][$fieldName] = $value;
                    } elseif (isset($tagMapping[$fieldName])) {
                        // Mapped field name
                        $modifyRequest['Replace'][$tagMapping[$fieldName]] = $value;
                    }
                }
            }
            
            // Set modification options based on modifyOption
            if ($modifyOption === 'keep_original_uids') {
                // Keep original UIDs - this modifies in place without generating new UIDs
                $modifyRequest['Keep'] = ['StudyInstanceUID', 'SeriesInstanceUID'];
                $modifyRequest['Force'] = true;
            } elseif ($modifyOption === 'create_copy') {
                // Create a copy with new UIDs - use different endpoint
                $modifyRequest['Force'] = true;
                // Don't set Keep parameter - let Orthanc generate new UIDs
            } else {
                // Default: generate_new_uids - modify original with new UIDs
                $modifyRequest['Force'] = true;
                // Don't set Keep parameter - let Orthanc generate new UIDs
            }
            
            // Debug logging
            error_log('ModifyOption: ' . $modifyOption);
            error_log('ModifyRequest: ' . json_encode($modifyRequest));
            
            // Determine the correct endpoint based on modify option
            if ($modifyOption === 'create_copy') {
                // For creating a copy, we still use modify but the response will be different
                $endpoint = '/studies/' . $studyId . '/modify';
            } else {
                // For in-place modification (both keep_original_uids and generate_new_uids)
                $endpoint = '/studies/' . $studyId . '/modify';
            }
            
            $curl = $this->setupCurl($orthanc['server'] . $endpoint, $orthanc, [
                CURLOPT_POST => 1,
                CURLOPT_POSTFIELDS => json_encode($modifyRequest),
                CURLOPT_HTTPHEADER => ['Content-Type: application/json']
            ]);
            
            $resp = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $curlError = curl_error($curl);
            $curlErrno = curl_errno($curl);
            curl_close($curl);
            
            if ($curlErrno !== 0) {
                $errorMessage = $this->getCurlErrorMessage($curlErrno, $curlError);
                echo json_encode([
                    'success' => false,
                    'message' => 'Connection failed: ' . $errorMessage['message']
                ]);
                exit();
            }
            
            if ($httpCode === 200) {
                $result = json_decode($resp, true);
                
                // Create appropriate success message based on modify option
                $successMessage = '';
                switch ($modifyOption) {
                    case 'keep_original_uids':
                        $successMessage = 'Study tags modified successfully (keeping original DICOM UIDs)';
                        break;
                    case 'create_copy':
                        $successMessage = 'Study copy created successfully with modified tags';
                        break;
                    default:
                        $successMessage = 'Study tags modified successfully (with new DICOM UIDs)';
                        break;
                }
                
                echo json_encode([
                    'success' => true,
                    'message' => $successMessage,
                    'data' => $result,
                    'modifyOption' => $modifyOption
                ]);
            } else {
                $errorMessage = $this->getHttpErrorMessage($httpCode, $resp);
                echo json_encode([
                    'success' => false,
                    'message' => 'Modification failed: ' . $errorMessage['message']
                ]);
            }
            
        } catch (\Exception $e) {
            error_log('Orthanc Download Exception: ' . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'debug' => [
                    'exception_trace' => $e->getTraceAsString()
                ]
            ]);
        }
        
        exit();
    }
    
    // Alternative download method using streaming
    private function downloadSeriesStream($seriesId, $orthanc)
    {
        $downloadEndpoint = '/series/' . $seriesId . '/archive';
        $fullUrl = rtrim($orthanc['server'], '/') . $downloadEndpoint;
        
        // Create a temporary file to store the download
        $tempFile = tempnam(sys_get_temp_dir(), 'orthanc_series_');
        $fp = fopen($tempFile, 'w+');
        
        if (!$fp) {
            return ['success' => false, 'message' => 'Cannot create temporary file'];
        }
        
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $fullUrl);
        curl_setopt($curl, CURLOPT_USERPWD, $orthanc['username'].":".$orthanc['password']);
        curl_setopt($curl, CURLOPT_FILE, $fp);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 300);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        
        $result = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlError = curl_error($curl);
        $curlErrno = curl_errno($curl);
        curl_close($curl);
        fclose($fp);
        
        if ($curlErrno !== 0 || $httpCode !== 200) {
            unlink($tempFile);
            return [
                'success' => false, 
                'message' => 'Stream download failed',
                'debug' => [
                    'curl_errno' => $curlErrno,
                    'curl_error' => $curlError,
                    'http_code' => $httpCode
                ]
            ];
        }
        
        $fileSize = filesize($tempFile);
        if ($fileSize < 100) {
            unlink($tempFile);
            return ['success' => false, 'message' => 'Downloaded file too small'];
        }
        
        // Validate ZIP file
        $handle = fopen($tempFile, 'r');
        $header = fread($handle, 4);
        fclose($handle);
        
        if (substr($header, 0, 2) !== 'PK') {
            unlink($tempFile);
            return ['success' => false, 'message' => 'Invalid ZIP file format'];
        }
        
        // Stream the file to browser
        $filename = 'series_' . $seriesId . '_' . date('Y-m-d_H-i-s') . '.zip';
        
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . $fileSize);
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
        header('Pragma: public');
        
        readfile($tempFile);
        unlink($tempFile);
        
        return ['success' => true];
    }

    public function postDownloadstudy()
    {
        // Validate token
        if (!isset($_POST['token']) || $_POST['token'] !== $_SESSION['token']) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid token'
            ]);
            exit();
        }
        
        // Validate studyId
        if (!isset($_POST['studyId']) || empty($_POST['studyId'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Study ID is required'
            ]);
            exit();
        }
        
        $studyId = $_POST['studyId'];
        
        // Get Orthanc configuration
        $orthanc = [
            'server' => $this->settings->get('orthanc.server'),
            'username' => $this->settings->get('orthanc.username'),
            'password' => $this->settings->get('orthanc.password')
        ];
        
        // Validate configuration
        $configError = $this->validateOrthancConfig($orthanc);
        if ($configError) {
            echo json_encode([
                'success' => false,
                'message' => $configError
            ]);
            exit();
        }
        
        try {
            // First, verify the study exists
            $verifyEndpoint = '/studies/' . $studyId;
            $curl = $this->setupCurl($orthanc['server'] . $verifyEndpoint, $orthanc);
            
            $resp = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $curlError = curl_error($curl);
            $curlErrno = curl_errno($curl);
            curl_close($curl);
            
            if ($curlErrno !== 0) {
                $errorMessage = $this->getCurlErrorMessage($curlErrno, $curlError);
                echo json_encode([
                    'success' => false,
                    'message' => 'Connection failed: ' . $errorMessage['message']
                ]);
                exit();
            }
            
            if ($httpCode !== 200) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Study not found or access denied'
                ]);
                exit();
            }
            
            // Get study archive (ZIP file)
            $downloadEndpoint = '/studies/' . $studyId . '/archive';
            $curl = $this->setupCurl($orthanc['server'] . $downloadEndpoint, $orthanc, [
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 5,
                CURLOPT_TIMEOUT => 300 // 5 minutes timeout for large files
            ]);
            
            $archiveData = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $contentType = curl_getinfo($curl, CURLINFO_CONTENT_TYPE);
            $curlError = curl_error($curl);
            $curlErrno = curl_errno($curl);
            curl_close($curl);
            
            if ($curlErrno !== 0) {
                $errorMessage = $this->getCurlErrorMessage($curlErrno, $curlError);
                // Clean any output buffers before JSON response
                if (ob_get_level()) {
                    ob_end_clean();
                }
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'Download failed: ' . $errorMessage['message']
                ]);
                exit();
            }
            
            if ($httpCode === 200 && !empty($archiveData)) {
                // Generate filename
                $filename = 'study_' . $studyId . '_' . date('Y-m-d_H-i-s') . '.zip';
                
                // Set headers for file download
                header('Content-Type: application/zip');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                header('Content-Length: ' . strlen($archiveData));
                header('Cache-Control: no-cache, must-revalidate');
                header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
                
                // Output the file data
                echo $archiveData;
                exit();
                
            } else {
                $errorMessage = $this->getHttpErrorMessage($httpCode, $archiveData);
                // Clean any output buffers before JSON response
                if (ob_get_level()) {
                    ob_end_clean();
                }
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'Download failed: ' . $errorMessage['message']
                ]);
            }
            
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
        
        exit();
    }

    public function postDownloadseries()
    {
        // Validate token
        if (!isset($_POST['token']) || $_POST['token'] !== $_SESSION['token']) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Invalid token'
            ]);
            exit();
        }
        
        // Validate seriesId
        if (!isset($_POST['seriesId']) || empty($_POST['seriesId'])) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Series ID is required'
            ]);
            exit();
        }
        
        $seriesId = $_POST['seriesId'];
        $verifyOnly = isset($_POST['verify_only']) && $_POST['verify_only'] === '1';
        
        // Get Orthanc configuration
        $orthanc = [
            'server' => $this->settings->get('orthanc.server'),
            'username' => $this->settings->get('orthanc.username'),
            'password' => $this->settings->get('orthanc.password')
        ];
        
        // Validate configuration
        $configError = $this->validateOrthancConfig($orthanc);
        if ($configError) {
            echo json_encode([
                'success' => false,
                'message' => $configError
            ]);
            exit();
        }
        
        try {
            // First, verify the series exists
            $verifyEndpoint = '/series/' . $seriesId;
            $curl = $this->setupCurl($orthanc['server'] . $verifyEndpoint, $orthanc);
            
            $resp = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $curlError = curl_error($curl);
            $curlErrno = curl_errno($curl);
            curl_close($curl);
            
            if ($curlErrno !== 0) {
                $errorMessage = $this->getCurlErrorMessage($curlErrno, $curlError);
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'Connection failed: ' . $errorMessage['message']
                ]);
                exit();
            }
            
            if ($httpCode !== 200) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'Series not found or access denied'
                ]);
                exit();
            }
            
            // If this is just a verification request, return success
            if ($verifyOnly) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'message' => 'Series exists and is accessible',
                    'seriesId' => $seriesId
                ]);
                exit();
            }
            
            // Get series archive (ZIP file)
            $downloadEndpoint = '/series/' . $seriesId . '/archive';
            $fullUrl = rtrim($orthanc['server'], '/') . $downloadEndpoint;
            
            // Log the full URL for debugging
            error_log('Orthanc Download Debug - Full URL: ' . $fullUrl);
            
            $curl = $this->setupCurl($fullUrl, $orthanc, [
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 5,
                CURLOPT_TIMEOUT => 300, // 5 minutes timeout for large files
                CURLOPT_HTTPHEADER => [
                    'Accept: application/zip, application/octet-stream, */*',
                    'User-Agent: MLite-Orthanc-Client/1.0'
                ]
            ]);
            
            $archiveData = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $contentType = curl_getinfo($curl, CURLINFO_CONTENT_TYPE);
            $contentLength = curl_getinfo($curl, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
            $curlError = curl_error($curl);
            $curlErrno = curl_errno($curl);
            curl_close($curl);
            
            // Debug logging
            error_log('Orthanc Download Debug - Series ID: ' . $seriesId);
            error_log('Orthanc Download Debug - HTTP Code: ' . $httpCode);
            error_log('Orthanc Download Debug - Content Type: ' . $contentType);
            error_log('Orthanc Download Debug - Content Length: ' . $contentLength);
            error_log('Orthanc Download Debug - Data Size: ' . strlen($archiveData));
            error_log('Orthanc Download Debug - First 100 chars: ' . substr($archiveData, 0, 100));
            
            if ($curlErrno !== 0) {
                $errorMessage = $this->getCurlErrorMessage($curlErrno, $curlError);
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'Download failed: ' . $errorMessage['message'],
                    'debug' => [
                        'curl_errno' => $curlErrno,
                        'curl_error' => $curlError,
                        'endpoint' => $orthanc['server'] . $downloadEndpoint
                    ]
                ]);
                exit();
            }
            
            // Check if response is actually JSON error instead of binary ZIP
            if (strpos($contentType, 'application/json') !== false || 
                (strlen($archiveData) > 0 && $archiveData[0] === '{')) {
                $jsonResponse = json_decode($archiveData, true);
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'Orthanc returned error instead of ZIP file',
                    'orthanc_error' => $jsonResponse,
                    'debug' => [
                        'http_code' => $httpCode,
                        'content_type' => $contentType,
                        'data_size' => strlen($archiveData)
                    ]
                ]);
                exit();
            }
            
            if ($httpCode === 200 && !empty($archiveData) && strlen($archiveData) > 100) {
                // Validate ZIP file by checking magic bytes
                $isValidZip = (strlen($archiveData) >= 4 && 
                              (substr($archiveData, 0, 2) === 'PK' || 
                               substr($archiveData, 0, 4) === "\x50\x4b\x03\x04" ||
                               substr($archiveData, 0, 4) === "\x50\x4b\x05\x06"));
                
                if (!$isValidZip) {
                    error_log('Orthanc Download Debug - Invalid ZIP magic bytes: ' . bin2hex(substr($archiveData, 0, 10)));
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => false,
                        'message' => 'Received data is not a valid ZIP file',
                        'debug' => [
                            'data_size' => strlen($archiveData),
                            'first_bytes' => bin2hex(substr($archiveData, 0, 10)),
                            'content_type' => $contentType
                        ]
                    ]);
                    exit();
                }
                
                // Generate filename
                $filename = 'series_' . $seriesId . '_' . date('Y-m-d_H-i-s') . '.zip';
                
                // Calculate exact content length for binary safety
                $contentLength = mb_strlen($archiveData, '8bit');
                
                // Log exact sizes for debugging
                error_log('Orthanc Download Debug - Archive data length: ' . strlen($archiveData));
                error_log('Orthanc Download Debug - Binary safe length: ' . $contentLength);
                
                // Clean all output buffers completely
                while (ob_get_level()) {
                    ob_end_clean();
                }
                
                // Set minimal required headers for clean download
                header('Content-Type: application/zip');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                header('Content-Length: ' . $contentLength);
                header('Cache-Control: no-cache');
                
                // Flush headers before binary output
                flush();
                
                // Output the file data directly without any interference
                echo $archiveData;
                exit();
                
            } else {
                // Try alternative streaming method as fallback
                error_log('Orthanc Download Debug - Primary method failed, trying streaming method');
                
                $streamResult = $this->downloadSeriesStream($seriesId, $orthanc);
                
                if ($streamResult['success']) {
                    // Streaming method succeeded, file already sent to browser
                    exit();
                } else {
                    // Both methods failed, return detailed error
                    $dataPreview = strlen($archiveData) > 0 ? substr($archiveData, 0, 200) : 'No data received';
                    $errorMessage = $this->getHttpErrorMessage($httpCode, $archiveData);
                    
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => false,
                        'message' => 'Both download methods failed: ' . $errorMessage['message'],
                        'debug' => [
                            'primary_method' => [
                                'http_code' => $httpCode,
                                'content_type' => $contentType,
                                'content_length' => $contentLength,
                                'data_size' => strlen($archiveData),
                                'data_preview' => $dataPreview,
                                'endpoint' => $fullUrl,
                                'series_id' => $seriesId
                            ],
                            'streaming_method' => $streamResult
                        ]
                    ]);
                }
            }
            
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
        
        exit();
    }

    public function postDeletestudy()
    {
        // Validate token
        if (!isset($_POST['token']) || $_POST['token'] !== $_SESSION['token']) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid token'
            ]);
            exit();
        }
        
        // Validate studyId
        if (!isset($_POST['studyId']) || empty($_POST['studyId'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Study ID is required'
            ]);
            exit();
        }
        
        $studyId = $_POST['studyId'];
        
        // Get Orthanc configuration
        $orthanc = [
            'server' => $this->settings->get('orthanc.server'),
            'username' => $this->settings->get('orthanc.username'),
            'password' => $this->settings->get('orthanc.password')
        ];
        
        // Validate configuration
        $configError = $this->validateOrthancConfig($orthanc);
        if ($configError) {
            echo json_encode([
                'success' => false,
                'message' => $configError
            ]);
            exit();
        }
        
        try {
            // First, verify the study exists
            $verifyEndpoint = '/studies/' . $studyId;
            $curl = $this->setupCurl($orthanc['server'] . $verifyEndpoint, $orthanc);
            
            $resp = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $curlError = curl_error($curl);
            $curlErrno = curl_errno($curl);
            curl_close($curl);
            
            if ($curlErrno !== 0) {
                $errorMessage = $this->getCurlErrorMessage($curlErrno, $curlError);
                echo json_encode([
                    'success' => false,
                    'message' => 'Connection failed: ' . $errorMessage['message']
                ]);
                exit();
            }
            
            if ($httpCode !== 200) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Study not found or access denied'
                ]);
                exit();
            }
            
            // Get study info before deletion for logging
            $studyInfo = json_decode($resp, true);
            
            // Delete the study using DELETE method
            $deleteEndpoint = '/studies/' . $studyId;
            $curl = $this->setupCurl($orthanc['server'] . $deleteEndpoint, $orthanc, [
                CURLOPT_CUSTOMREQUEST => 'DELETE',
                CURLOPT_TIMEOUT => 60 // 1 minute timeout for deletion
            ]);
            
            $deleteResp = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $curlError = curl_error($curl);
            $curlErrno = curl_errno($curl);
            curl_close($curl);
            
            if ($curlErrno !== 0) {
                $errorMessage = $this->getCurlErrorMessage($curlErrno, $curlError);
                echo json_encode([
                    'success' => false,
                    'message' => 'Delete operation failed: ' . $errorMessage['message']
                ]);
                exit();
            }
            
            if ($httpCode === 200) {
                // Log the deletion for audit purposes
                error_log('Orthanc Study Deleted: ' . $studyId . ' by user: ' . ($_SESSION['username'] ?? 'unknown'));
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Study deleted successfully from Orthanc server',
                    'studyId' => $studyId
                ]);
            } else {
                $errorMessage = $this->getHttpErrorMessage($httpCode, $deleteResp);
                echo json_encode([
                    'success' => false,
                    'message' => 'Delete failed: ' . $errorMessage['message'],
                    'httpCode' => $httpCode
                ]);
            }
            
        } catch (\Exception $e) {
            error_log('Orthanc Delete Study Error: ' . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
        
        exit();
    }

    private function _addHeaderFiles()
    {
        // CSS
        $this->core->addCSS(url('assets/css/dataTables.bootstrap.min.css'));
        $this->core->addJS(url('assets/jscripts/jquery.dataTables.min.js'));
        $this->core->addJS(url('assets/jscripts/dataTables.bootstrap.min.js'));
        $this->core->addCSS(url('assets/css/bootstrap-datetimepicker.css'));
        $this->core->addJS(url('assets/jscripts/moment-with-locales.js'));
        $this->core->addJS(url('assets/jscripts/bootstrap-datetimepicker.js'));
        $this->core->addJS(url([ADMIN, 'orthanc', 'javascript']), 'footer');
    }

}
