<?php

namespace Plugins\Bpjs_emr;

use Systems\AdminModule;

class Admin extends AdminModule
{

    public $assign = [];

    public function navigation()
    {
        return [
            'MR Bundle' => 'bundle',
            'Pengaturan' => 'settings'
        ];
    }

    public function getManage()
    {
        $sub_modules = [
            ['name' => 'MR Bundle', 'url' => url([ADMIN, 'bpjs_emr', 'bundle']), 'icon' => 'tasks', 'desc' => 'Medical Record Bundle'],
            ['name' => 'Pengaturan', 'url' => url([ADMIN, 'bpjs_emr', 'settings']), 'icon' => 'tasks', 'desc' => 'Pengaturan BPJS EMR'],
        ];
        return $this->draw('manage.html', ['sub_modules' => $sub_modules]);
    }

    public function getBundle()
    {
        $this->_addHeaderFiles();

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perpage = 20;
        $search = isset($_GET['s']) ? $_GET['s'] : '';
        $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d');
        $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

        $query = "SELECT b.*, p.nm_pasien, r.kd_dokter, d.nm_dokter, r.tgl_registrasi, r.jam_reg,
                         (SELECT status FROM mlite_bpjs_emr_logs WHERE mlite_bpjs_emr_logs.no_sep = b.no_sep ORDER BY id DESC LIMIT 1) as status_kirim
                  FROM bridging_sep b
                  JOIN reg_periksa r ON b.no_rawat = r.no_rawat
                  JOIN pasien p ON b.nomr = p.no_rkm_medis
                  JOIN dokter d ON r.kd_dokter = d.kd_dokter
                  WHERE r.tgl_registrasi BETWEEN :start_date AND :end_date";

        $params = [
            ':start_date' => $start_date,
            ':end_date' => $end_date
        ];

        if (!empty($search)) {
            $query .= " AND (b.no_sep LIKE :search OR b.no_rawat LIKE :search OR b.nomr LIKE :search OR p.nm_pasien LIKE :search)";
            $params[':search'] = "%$search%";
        }

        $stmtCount = $this->db()->pdo()->prepare($query);
        $stmtCount->execute($params);
        $totalRecords = $stmtCount->rowCount();

        $pagination = new \Systems\Lib\Pagination($page, $totalRecords, $perpage, url([ADMIN, 'bpjs_emr', 'bundle', '%d?s=' . $search . '&start_date=' . $start_date . '&end_date=' . $end_date]));
        
        $offset = $pagination->offset();
        $query .= " ORDER BY b.tglsep DESC LIMIT " . (int)$perpage . " OFFSET " . (int)$offset;
        
        $stmt = $this->db()->pdo()->prepare($query);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return $this->draw('bundle.html', [
            'title' => 'Medical Record Bundle',
            'bundle_list' => $rows,
            'pagination' => $pagination->nav('pagination', '5'),
            's' => htmlspecialchars($search),
            'start_date' => htmlspecialchars($start_date),
            'end_date' => htmlspecialchars($end_date)
        ]);
    }

    private function timestamp()
    {
        return (string) time();
    }

    private function signature($timestamp, $consid, $secretkey)
    {
        $data = $consid . "&" . $timestamp;
        return base64_encode(hash_hmac('sha256', $data, $secretkey, true));
    }

    private function encryptMR($plain, $consid, $secretkey, $koders)
    {
        $gzip = gzencode($plain);
        $base = base64_encode($gzip);

        // KEY AES 256
        $key = hex2bin(hash('sha256', $consid . $secretkey . $koders));
        $iv  = substr($key, 0, 16);

        $encrypted = openssl_encrypt(
            $base,
            'AES-256-CBC',
            $key,
            OPENSSL_RAW_DATA,
            $iv
        );

        return base64_encode($encrypted);
    }

    public function decryptMR($payload, $consid, $secretkey, $koders)
    {
        $key = hash('sha256', $consid . $secretkey . $koders, true);
        $iv  = substr($key, 0, 16);

        $decrypted = openssl_decrypt(base64_decode($payload), 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);

        return gzuncompress($decrypted);
    }

    public function sendMR($noSep, $jnsPelayanan, $bulan, $tahun, $fhirJson, $consid, $secretkey, $userkey, $koders, $baseUrl)
    {
        $baseUrl = rtrim($baseUrl, '/') . '/';
        $timestamp   = $this->timestamp();
        $signature   = $this->signature($timestamp, $consid, $secretkey);
        $encryptedMR = $this->encryptMR($fhirJson, $consid, $secretkey, $koders);

        $body = [
            "request" => [
                "noSep"        => $noSep,
                "jnsPelayanan" => $jnsPelayanan,
                "bulan"        => $bulan,
                "tahun"        => $tahun,
                "dataMR"       => $encryptedMR
            ]
        ];

        $headers = [
            "Content-Type: application/json",
            "Accept: application/json",
            "X-cons-id: {$consid}",
            "X-timestamp: {$timestamp}",
            "X-signature: {$signature}",
            "user_key: {$userkey}"
        ];
        
        $ch = curl_init($baseUrl . "eclaim/rekammedis/insert");
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($body),
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 120,
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);

        $response = curl_exec($ch);
        $error    = curl_error($ch);
        $info     = curl_getinfo($ch);
        curl_close($ch);

        return [
            "timestamp" => $timestamp,
            "signature" => $signature,
            "request"   => $body,
            "response"  => $response,
            "error"     => $error,
            "info"      => $info,
            "encryptedBody" => json_encode($body, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        ];
    }

    private function entry($resource){
        return [
            "resource" => $resource
        ];
    }

    private function bundle($id, $sep, $entries){
        return [
            "resourceType" => "Bundle",
            "id" => $id,
            "meta" => [
                "lastUpdated" => date("Y-m-d H:i:s")
            ],
            "identifier" => [
                "system" => "sep",
                "value"  => $sep
            ],
            "type" => "document",
            "entry" => $entries
        ];
    }

    private function composition($compositionId, $noMr, $nama, $encounterId, $id_pr, $nama_pr, $start, $sectionData=[])
    {
        $sections = new \stdClass();
        $no = 0;

        foreach ($sectionData as $s) {
            $section = [
                "title" => $s['title'],
                "code" => [
                    "coding" => [[
                        "system" => $s['system'],
                        "code" => $s['code'],
                        "display" => $s['display']
                    ]]
                ],
                "text" => [
                    "status" => "additional",
                    "div" => "\n" . $s['text'] . "\n"
                ]
            ];

            if(isset($s['entry'])){
                $section['entry'] = $s['entry'];
            }

            if(isset($s['mode'])){
                $section['mode'] = $s['mode'];
            }

            $sections->{(string)$no} = $section;
            $no++;
        }

        return [
            "resourceType" => "Composition",
            "id" => $compositionId,
            "status" => "final",
            "type" => [
                "coding" => [[
                    "system" => "http://loinc.org",
                    "code" => "81218-0"
                ]],
                "text" => "Discharge Summary"
            ],
            "subject" => [
                "reference" => "Patient/".$noMr,
                "display" => $nama
            ],
            "encounter" => [
                "reference" => "Encounter/".$encounterId
            ],
            "date" => $start,
            "author" => [[
                "reference" => "Practitioner/".$id_pr,
                "display" => $nama_pr
            ]],
            "title" => "Discharge Summary",
            "confidentiality" => "N",
            "section" => $sections
        ];
    }

    private function conditions($orgId, $noMr, $diagnosa, $start)
    {
        $entries = [];
        $conditionRefs = [];

        foreach ($diagnosa as $i => $diag) {

            $conditionId = $orgId . "-" . uniqid();

            $entries[] = [
                "resource" => [
                    "resourceType" => "Condition",
                    "id" => $conditionId,
                    "clinicalStatus" => "active",
                    "verificationStatus" => "confirmed",
                    "category" => [
                        [
                            "coding" => [
                                [
                                    "system" => "http://hl7.org/fhir/condition-category",
                                    "code" => "encounter-diagnosis",
                                    "display" => "Encounter Diagnosis"
                                ]
                            ]
                        ]
                    ],
                    "code" => [
                        "coding" => [
                            [
                                "system" => "http://hl7.org/fhir/sid/icd-10",
                                "code" => $diag["code"],
                                "display" => $diag["display"]
                            ]
                        ],
                        "text" => $diag["display"]
                    ],
                    "subject" => [
                        "reference" => "Patient/".$noMr
                    ],
                    "onsetDateTime" => $start
                ]
            ];

            $conditionRefs[] = [
                "condition" => [
                    "reference" => "Condition/".$conditionId
                ],
                "role" => [
                    "coding" => [
                        [
                            "system" => "http://hl7.org/fhir/diagnosis-role",
                            "code" => "DD",
                            "display" => "Discharge Diagnosis"
                        ]
                    ]
                ],
                "rank" => $i + 1
            ];
        }

        return [
            "conditions" => $entries,
            "references" => $conditionRefs
        ];
    }

    private function encounter($encounterId, $id2, $nama, $noSep, $start, $end, $conditionRefs, $diagnosaAwal, $jnsPelayanan, $asalRujukan, $noRujukan)
    {
        // $conditionRefs is an array of [ "condition" => ["reference" => "..."], "rank" => 1 ]
        // $diagnosaAwal is text for reason.
                
        $classCode = ($jnsPelayanan == '1') ? 'IMP' : 'AMB';
        $classDisplay = ($jnsPelayanan == '1') ? 'inpatient encounter' : 'ambulatory';
        
        $rujukanSystem = ($asalRujukan == '1') ? 'nomor_rujukan_bpjs' : 'nomor_rujukan_internal_rs';

        return [
            "resourceType" => "Encounter",
            "id" => $encounterId,
            "identifier" => [
                [
                    "system" => "http://api.bpjs-kesehatan.go.id:8080/Vclaim-rest/SEP/",
                    "value" => $noSep
                ]
            ],
            "subject" => [
                "reference" => "Patient/" . $id2,
                "display" => $nama,
                "noSep" => $noSep
            ],
            "class" => [
                "system" => "http://hl7.org/fhir/v3/ActCode",
                "code" => $classCode,
                "display" => $classDisplay
            ],
            "incomingReferral" => [
                [
                    "identifier" => [
                        [
                            "system" => $rujukanSystem,
                            "value" => $noRujukan
                        ]
                    ]
                ]
            ],
            "reason" => [
                [
                    "coding" => [
                        [
                            "code" => "",
                            "display" => null,
                            "system" => "http://hl7.org/fhir/sid/icd-10"
                        ]
                    ],
                    "text" => $diagnosaAwal
                ]
            ],
            "diagnosis" => $conditionRefs,
            "hospitalization" => [
                "dischargeDisposition" => [
                    [
                        "coding" => [
                            [
                                "code" => "home",
                                "display" => "Home",
                                "system" => "http://hl7.org/fhir/discharge-disposition"
                            ]
                        ]
                    ]
                ]
            ],
            "period" => [
                "start" => $start,
                "end" => $end
            ],
            "status" => "finished",
            "text" => [
                "status" => "generated",
                "div" => "\nAdmitted to Instalasi Gawat Darurat," . $this->settings->get('settings.nama_instansi') . " between " . $start . " and " . $end . "\n"
            ]
        ];
    }

    private function organization($data){
        return [
            "resourceType" => "Organization",
            "id" => $data['id'],
            "identifier" => [
                [
                    "use" => "official",
                    "system" => "urn:oid:bpjs",
                    "value" => $data['kode_bpjs']
                ],
                [
                    "use" => "official",
                    "system" => "urn:oid:kemkes",
                    "value" => $data['kode_kemkes']
                ]
            ],
            "type" => [
                [
                    "coding" => [
                        [
                            "system" => "http://hl7.org/fhir/organization-type",
                            "code" => "prov",
                            "display" => "Healthcare Provider"
                        ]
                    ]
                ]
            ],
            "name" => $data['nama'],
            "alias" => [
                $data['alias'] ?? "RSCM" // TODO: Sesuaikan dengan setting alias instansi RS jika ada, atau biarkan default RSCM seperti contoh
            ],
            "telecom" => [
                [
                    "system" => "phone",
                    "value" => $data['telp'],
                    "use" => "work"
                ]
            ],
            "address" => [
                [
                    "use" => "work",
                    "text" => $data['alamat_lengkap'] ?? $data['alamat'],
                    "line" => [$data['alamat']],
                    "city" => $data['kota'],
                    "state" => $data['provinsi'],
                    "postalCode" => $data['kodepos'],
                    "country" => "IDN"
                ]
            ],
            "contact" => [
                [
                    "purpose" => [
                        "coding" => [
                            [
                                "system" => "http://hl7.org/fhir/contactentity-type",
                                "code" => "PATINF"
                            ]
                        ]
                    ],
                    "telecom" => [
                        [
                            "system" => "phone",
                            "value" => $data['telp']
                        ]
                    ]
                ]
            ]
        ];
    }

    private function patient($id2, $noMr, $noKartu, $nik, $nama, $kelamin, $tglLahir, $hp, $alamat){
        $pasien = $this->db('pasien')->where('no_rkm_medis', $id2)->oneArray();
        
        // Asumsi data kota, kecamatan, dll bisa ditarik dari tabel pasien/kelurahan jika ada
        // Default values
        $city = $pasien['kabupaten'] ?? '';
        $district = $pasien['kecamatan'] ?? '';
        $state = $pasien['propinsi'] ?? '';
        $postalCode = '';
        
        // Status nikah M=Married, U=Unmarried, dll
        $maritalStatus = 'U';
        if (isset($pasien['stts_nikah']) && (strtolower($pasien['stts_nikah']) == 'menikah' || strtolower($pasien['stts_nikah']) == 'kawin')) {
            $maritalStatus = 'M';
        }

        return [
            "resourceType" => "Patient",
            "id" => $id2,
            "identifier" => [
                [
                    "use" => "usual",
                    "type" => [
                        "coding" => [
                            [
                                "system" => "http://hl7.org/fhir/v2/0203",
                                "code" => "MR"
                            ]
                        ]
                    ],
                    "value" => $noMr,
                    "assigner" => [
                        "display" => $this->settings->get('settings.nama_instansi')
                    ]
                ],
                [
                    "use" => "official",
                    "type" => [
                        "coding" => [
                            [
                                "system" => "http://hl7.org/fhir/v2/0203",
                                "code" => "MB"
                            ]
                        ]
                    ],
                    "value" => $noKartu,
                    "assigner" => [
                        "display" => "BPJS KESEHATAN"
                    ]
                ],
                [
                    "use" => "official",
                    "type" => [
                        "coding" => [
                            [
                                "system" => "http://hl7.org/fhir/v2/0203",
                                "code" => "NNIDN"
                            ]
                        ]
                    ],
                    "value" => $nik,
                    "assigner" => [
                        "display" => "KEMENDAGRI"
                    ]
                ]
            ],
            "active" => true,
            "name" => [
                [
                    "use" => "official",
                    "text" => $nama
                ]
            ],
            "maritalStatus" => [
                "coding" => [
                    [
                        "system" => "http://hl7.org/fhir/v3/MaritalStatus",
                        "code" => $maritalStatus
                    ]
                ]
            ],
            "telecom" => [
                [
                    "system" => "phone",
                    "value" => "",
                    "use" => "work"
                ],
                [
                    "system" => "phone",
                    "value" => $hp,
                    "use" => "mobile"
                ],
                [
                    "system" => "phone",
                    "value" => "TDK ADA",
                    "use" => "home"
                ]
            ],
            "gender" => $kelamin,
            "birthDate" => $tglLahir,
            "deceasedBoolean" => false,
            "address" => [
                [
                    "line" => [$alamat],
                    "city" => $city,
                    "district" => $district,
                    "state" => $state,
                    "postalCode" => $postalCode,
                    "text" => $alamat . " " . $district . " " . $city,
                    "use" => "home",
                    "type" => "both"
                ]
            ],
            "managingOrganization" => [
                "reference" => "Organization/" . $this->settings->get('bpjs_emr.koders'),
                "display" => $this->settings->get('settings.nama_instansi')
            ]
        ];
    }

    private function practitioner(
        $id_pr,
        $sip,
        $nik_pr,
        $nama_pr,
        $phone,
        $email,
        $address,
        $city,
        $district,
        $state,
        $postalCode,
        $gender,
        $birthDate
    ){
        return [
            "resourceType" => "Practitioner",
            "id" => $id_pr,
            "identifier" => [
                [
                    "use" => "official",
                    "system" => "urn:oid:nomor_sip",
                    "value" => $sip
                ],
                [
                    "use" => "official",
                    "type" => [
                        "coding" => [
                            [
                                "system" => "http://hl7.org/fhir/v2/0203",
                                "code" => "NNIDN"
                            ]
                        ]
                    ],
                    "value" => $nik_pr,
                    "assigner" => [
                        "display" => "KEMDAGRI"
                    ]
                ]
            ],
            "name" => [
                [
                    "use" => "official",
                    "text" => $nama_pr
                ]
            ],
            "telecom" => [
                [
                    "system" => "phone",
                    "value" => $phone,
                    "use" => "work"
                ],
                [
                    "system" => "email",
                    "value" => $email,
                    "use" => "work"
                ],
                [
                    "system" => "fax",
                    "value" => "",
                    "use" => "work"
                ],
                [
                    "system" => "home", // Sesuaikan dengan JSON user, walaupun standar FHIR system telecom adalah phone/fax/email/dll
                    "value" => "",
                    "use" => "home"
                ]
            ],
            "address" => [
                [
                    "use" => "home",
                    "line" => [$address],
                    "city" => $city === '-' || empty($city) ? null : $city,
                    "postalCode" => $postalCode === '-' || empty($postalCode) ? "" : $postalCode,
                    "country" => null
                ]
            ],
            "gender" => $gender,
            "birthDate" => $birthDate === '-' ? null : $birthDate
        ];
    }

    private function diagnostic($diagId, $pasien, $nama_pasien, $noSep, $orgId, $nama_org, $observations, $categoryCode = 'LAB') {
        // Tentukan display berdasarkan kode
        $categoryDisplay = ($categoryCode === 'RAD') ? 'Radiology' : 'Laboratory';
        
        // Menggabungkan Observation di dalam DiagnosticReport
        return [
            "resourceType" => "DiagnosticReport",
            "id" => $diagId,
            "subject" => [
                "reference" => "Patient/" . $pasien,
                "display" => $nama_pasien,
                "noSep" => $noSep
            ],
            "category" => [
                "coding" => [
                    [
                        "system" => "http://hl7.org/fhir/v2/0074",
                        "code" => $categoryCode,
                        "display" => $categoryDisplay
                    ]
                ]
            ],
            "status" => "final",
            "performer" => [
                [
                    "reference" => "Organization/" . $orgId,
                    "display" => $nama_org
                ]
            ],
            "result" => $observations
        ];
    }

    private function buildMedicationResource($listObat,$pasien,$dokter,$diagnosa)
    {
        $result = [];

        foreach($listObat as $o){

            $result[] = [
                "resourceType" => "MedicationRequest",

                "text" => [
                    "div" => "\n" . $o['nama_obat'] . "\n"
                ],

                "identifier" => [
                    "system" => "id_resep_pulang",
                    "value" => (string)$o['id_resep']
                ],

                "subject" => [
                    "display" => $pasien['nama'],
                    "reference" => "Patient/".$pasien['no_rm']
                ],

                "intent" => "final",

                "medicationCodeableConcept" => [
                    "coding" => [
                        [
                            "code" => $o['kode_obat'],
                            "system" => "http://sys-ids.kemkes.go.id/kfa" // TODO: Sesuaikan dengan system yang digunakan (misal kfa atau custom)
                        ]
                    ],
                    "text" => $o['nama_obat']
                ],

                "dosageInstruction" => [[
                    "doseQuantity" => [
                        "code" => $o['kode_satuan'],
                        "system" => "http://unitsofmeasure.org",
                        "unit" => $o['satuan'],
                        "value" => (string)$o['jumlah']
                    ],

                    "route" => [
                        "coding" => [[
                            "code" => "001", // TODO: Ambil dari mapping rute obat (contoh 001 ORAL)
                            "display" => "ORAL",
                            "system" => "http://snomed.info/sct"
                        ]]
                    ],

                    "timing" => [
                        "repeat" => [
                            "frequency" => (string)$o['frequency'],
                            "period" => 1,
                            "periodUnit" => "d" // na, d, dll
                        ]
                    ],

                    "additionalInstruction" => [[
                        "text" => $o['aturan']
                    ]]
                ]],

                "reasonCode" => [[
                    "coding" => [
                        [
                            "code" => isset($diagnosa[0]['code']) ? $diagnosa[0]['code'] : "",
                            "display" => isset($diagnosa[0]['display']) ? $diagnosa[0]['display'] : "",
                            "system" => "http://hl7.org/fhir/sid/icd-10"
                        ]
                    ],
                    "text" => isset($diagnosa[0]['display']) ? $diagnosa[0]['display'] : ""
                ]],

                "requester" => [
                    "agent" => [
                        "display" => $dokter['nama'],
                        "reference" => "Practitioner/".$dokter['kd']
                    ],
                    "onBehalfOf" => [
                        "reference" => "Organization/".$dokter['org']
                    ]
                ],

                "meta" => [
                    "lastUpdated" => date('Y-m-d H:i:s')
                ]
            ];

        }

        return $result;
    }

    private function observation($obsId, $lab, $dokterId, $nama_dokter, $categoryCode = 'LAB') {
        $system = ($categoryCode === 'RAD') ? "http://snomed.info/sct" : "http://loinc.org";
        $divText = ($categoryCode === 'RAD') ? "\n" . $lab['hasil'] . "\n" : "\n" . $lab['pemeriksaan'] . ": " . $lab['hasil'] . " " . $lab['satuan'] . "\n";
        
        $obs = [
            "resourceType" => "Observation",
            "id" => $obsId,
            "status" => "final",
            "text" => [
                "status" => "generated",
                "div" => $divText
            ],
            "issued" => $lab['tgl_periksa'] ?? date('Y-m-d H:i:s'),
            "effectiveDateTime" => $lab['tgl_periksa'] ?? date('Y-m-d H:i:s'),
            "code" => [
                "coding" => [
                    [
                        "system" => $system,
                        "code" => $lab['loinc'],
                        "display" => $lab['pemeriksaan']
                    ]
                ],
                "text" => $lab['pemeriksaan']
            ],
            "performer" => [
                "reference" => "Practitioner/" . $dokterId,
                "display" => $nama_dokter
            ]
        ];

        if ($categoryCode === 'LAB') {
            $obs["valueQuantity"] = [
                "value" => $lab['hasil'],
                "unit" => $lab['satuan']
            ];
            $obs["conclusion"] = "Hasil Lab";
        } else {
            $obs["image"] = [
                [
                    "comment" => "",
                    "link" => [
                        "reference" => "",
                        "display" => ""
                    ]
                ]
            ];
            $obs["conclusion"] = $lab['conclusion'] ?? "Hasil Radiologi";
        }

        return $obs;
    }

    private function device($orgId, $noMr, $devices)
    {
        $items = [];

        foreach ($devices as $i => $dev) {
            $deviceId = $orgId . "-" . uniqid() . "-" . ($i + 1);

            $items[] = [
                "resourceType" => "Device",
                "id" => $deviceId,
                "text" => [
                    "status" => "generated",
                    "div" => "\nGenerated Narrative with Details\nid: " . $deviceId . "\nidentifier: " . $dev['identifier_value'] . "\ntype: " . $dev['type_display'] . " (Details : {http://acme.com/devices code = " . $dev['type_code'] . ", given as " . $dev['type_display'] . "})\nlotNumber: " . ($dev['lotNumber'] ?? "") . "\nmanufacturer: " . ($dev['manufacturer'] ?? "") . "\nmodel: " . ($dev['model'] ?? "") . "\npatient: Patient/" . $noMr . "\ncontact: " . ($dev['contact_value'] ?? "") . "\n"
                ],
                "identifier" => [
                    [
                        "system" => "http://acme.com/devices/pacemakers/octane/serial",
                        "value" => $dev['identifier_value']
                    ]
                ],
                "type" => [
                    "coding" => [
                        [
                            "system" => "http://acme.com/devices",
                            "code" => $dev['type_code'],
                            "display" => $dev['type_display']
                        ]
                    ]
                ],
                "lotNumber" => $dev['lotNumber'] ?? "",
                "manufacturer" => $dev['manufacturer'] ?? "",
                "manufactureDate" => $dev['manufactureDate'] ?? "",
                "expirationDate" => $dev['expirationDate'] ?? "",
                "model" => $dev['model'] ?? "",
                "patient" => [
                    "reference" => "Patient/" . $noMr
                ],
                "contact" => [
                    [
                        "system" => "phone",
                        "value" => $dev['contact_value'] ?? "",
                        "use" => "work"
                    ]
                ]
            ];
        }

        return [
            "resource" => $items
        ];
    }

    private function procedures($orgId,$noMr,$nama,$nama_pr,$id_pr,$start,$end,$procedures)
    {
        $items = [];

        foreach ($procedures as $i => $proc) {

            $procedureId = $orgId."-".uniqid()."-".($i+1);

            $items[] = [
                "resourceType" => "Procedure",
                "id" => $procedureId,
                "text" => [
                    "status" => "generated",
                    "div" => "Generated Narrative with Details"
                ],
                "status" => "completed",
                "code" => [
                    "coding" => $proc['coding']
                ],
                "subject" => [
                    "reference" => "Patient/".$noMr,
                    "display" => $nama
                ],
                "context" => [
                    "reference" => "Encounter/".$proc['encounter'],
                    "display" => $nama." encounter on ".$start
                ],
                "performedPeriod" => [
                    "start" => $start,
                    "end" => $end
                ],
                "performer" => [
                    [
                        "role" => [
                            "coding" => [
                                [
                                    "system" => "http://snomed.info/sct",
                                    "code" => $proc['snom_pr'],
                                    "display" => $proc['snom_dsp']
                                ]
                            ]
                        ],
                        "actor" => [
                            "reference" => "Practitioner/".$id_pr,
                            "display" => $nama_pr
                        ]
                    ]
                ],
                "reasonCode" => [
                    [
                        "text" => "DiagnosticReport/f201" // Sesuai contoh atau bisa disesuaikan
                    ]
                ],
                "bodySite" => [
                    [
                        "coding" => [
                            [
                                "system" => "http://snomed.info/sct",
                                "code" => "272676008", // Sesuai contoh atau bisa disesuaikan
                                "display" => "Sphenoid bone"
                            ]
                        ]
                    ]
                ],
                "focalDevice" => [
                    [
                        "action" => [
                            "coding" => [
                                [
                                    "system" => "http://hl7.org/fhir/device-action",
                                    "code" => "implanted"
                                ]
                            ]
                        ],
                        "manipulated" => [
                            "reference" => "Device/example-pacemaker"
                        ]
                    ]
                ],
                "note" => [
                    [
                        "text" => $proc['note']
                    ]
                ]
            ];
        }

        return [
            "resource" => $items
        ];
    }

    public function postSendBundle()
    {
        header('Content-Type: application/json');
        
        $no_sep = $_POST['no_sep'] ?? '';
        $no_rawat = $_POST['no_rawat'] ?? '';
        
        if (empty($no_sep) || empty($no_rawat)) {
            echo json_encode(['status' => 'error', 'message' => 'No SEP atau No Rawat tidak valid.']);
            exit;
        }

        // Ambil data pasien dari bridging_sep dan reg_periksa
        $data_sep = $this->db('bridging_sep')
            ->join('reg_periksa', 'bridging_sep.no_rawat = reg_periksa.no_rawat')
            ->join('pasien', 'bridging_sep.nomr = pasien.no_rkm_medis')
            ->join('dokter', 'reg_periksa.kd_dokter = dokter.kd_dokter')
            ->where('bridging_sep.no_sep', $no_sep)
            ->oneArray();

        if (!$data_sep) {
            echo json_encode(['status' => 'error', 'message' => 'Data SEP tidak ditemukan.']);
            exit;
        }

        // Include resource builders
        // Removed require_once since they are now internal methods

        $entries = [];

        // Data Organisasi (contoh RS)
        // TODO: Sesuaikan dengan setting instansi RS di database
        $orgId = $this->settings->get('bpjs_emr.koders') ?? '-';
        $org_list = [
            [
                "id" => $orgId,
                "kode_bpjs" => $orgId,
                "kode_kemkes" => $this->settings->get('bpjs_emr.kode_kemkes') ?? '-',
                "nama" => $this->settings->get('settings.nama_instansi') ?? '-',
                "telp" => $this->settings->get('settings.nomor_telepon') ?? '-',
                "use" => "work",
                "alamat" => $this->settings->get('settings.alamat') ?? '-',
                "kota" => $this->settings->get('settings.kota') ?? '-',
                "provinsi" => $this->settings->get('settings.propinsi') ?? '-',
                "kodepos" => $this->settings->get('bpjs_emr.kodepos') ?? '-',
                "negara" => "Indonesia"
            ]
        ];

        foreach($org_list as $org){
            $entries[] = $this->entry($this->organization($org));
        }

        // Data Pasien
        $id2 = $data_sep['nomr'];
        $entries[] = $this->entry($this->patient(
            $id2,
            $data_sep['nomr'],
            $data_sep['no_kartu'] ?? '0000000000000',
            $data_sep['no_ktp'] ?? '0000000000000000',
            $data_sep['nm_pasien'],
            ($data_sep['jk'] == 'P' ? 'female' : 'male'),
            $data_sep['tanggal_lahir'],
            $data_sep['notelep'] ?? '-',
            $data_sep['alamat'] ?? '-'
        ));

        // Data Practitioner / Dokter
        $id_pr = $data_sep['kddpjp'];
        
        // Ambil data detail dokter dari tabel dokter
        $dokter_detail = $this->db('dokter')->where('kd_dokter', $data_sep['kd_dokter'])->oneArray();

        $entries[] = $this->entry($this->practitioner(
            $id_pr,
            $dokter_detail['no_ijn_praktek'] ?? '-',
            $dokter_detail['no_ktp'] ?? '-',
            $dokter_detail['nm_dokter'] ?? $data_sep['nmdpjp'],
            $dokter_detail['no_telp'] ?? '-',
            '-', // email
            $dokter_detail['almt_tgl'] ?? '-',
            '-', // city
            '-', // district
            '-', // state
            '-', // postalCode
            (isset($dokter_detail['jk']) && $dokter_detail['jk'] == 'P' ? 'female' : 'male'),
            $dokter_detail['tgl_lahir'] ?? '-'  // birthDate
        ));

        // Data Kondisi / Diagnosa
        // Mengambil dari diagnosa_pasien mlite
        $diagnosa_db = $this->db('diagnosa_pasien')
            ->join('penyakit', 'diagnosa_pasien.kd_penyakit = penyakit.kd_penyakit')
            ->where('no_rawat', $no_rawat)
            ->toArray();
        
        $diagnosa = [];
        if (!empty($diagnosa_db)) {
            foreach ($diagnosa_db as $diag) {
                $diagnosa[] = [
                    "code" => $diag['kd_penyakit'],
                    "display" => $diag['nm_penyakit']
                ];
            }
        } else {
            // Fallback diagnosa dari bridging_sep
            if (!empty($data_sep['diagawal'])) {
                $diagnosa[] = [
                    "code" => $data_sep['diagawal'],
                    "display" => $data_sep['nmdiagnosaawal']
                ];
            }
        }

        $conditionRefs = [];
        $start = $data_sep['tgl_registrasi'] . ' ' . $data_sep['jam_reg'];
        if (!empty($diagnosa)) {
            $result = $this->conditions($orgId, $data_sep['nomr'], $diagnosa, $start);
            if (isset($result["conditions"])) {
                foreach($result["conditions"] as $c){
                    $entries[] = $c;
                }
            }
            $conditionRefs = $result["references"];
        }

        // Data Encounter
        $encounterId = $no_sep . '-' . date('Y-m-d', strtotime($data_sep['tgl_registrasi']));
        $end = date('Y-m-d H:i:s'); // TODO: Ambil dari tgl pulang
        $diagnosaAwal = $data_sep['nmdiagnosaawal'] ?? (count($diagnosa) > 0 ? $diagnosa[0]['display'] : 'Diagnosa Awal');
        $jnsPelayanan = $data_sep['jnspelayanan'] ?? '2'; // 1 = Ranap, 2 = Ralan
        
        $asalRujukan = $data_sep['asalrujukan'] ?? '1'; // 1 = Faskes 1 / BPJS, 2 = Faskes 2 / Internal
        $noRujukan = $data_sep['no_rujukan'] ?? '0'; // Fallback value
        
        $entries[] = $this->entry($this->encounter($encounterId, $id2, $data_sep['nm_pasien'], $no_sep, $start, $end, $conditionRefs, $diagnosaAwal, $jnsPelayanan, $asalRujukan, $noRujukan));

        // Data MedicationRequest / Resep Obat (Opsional)
        // Ambil data obat_racikan dan detail_pemberian_obat berdasarkan no_rawat
        $listObat = [];
        $obat_db = $this->db('detail_pemberian_obat')
            ->join('databarang', 'detail_pemberian_obat.kode_brng = databarang.kode_brng')
            ->where('no_rawat', $no_rawat)
            ->toArray();
            
        if (!empty($obat_db)) {
            foreach ($obat_db as $obat) {
                $listObat[] = [
                    'id_resep' => uniqid(),
                    'kode_obat' => $obat['kode_brng'], // Ganti dengan kode KFA jika ada
                    'nama_obat' => $obat['nama_brng'],
                    'aturan' => '3 x sehari', // TODO: Ambil dari aturan pakai resep
                    'kode_satuan' => 'TAB', // TODO: Sesuaikan satuan KFA
                    'satuan' => 'Tablet',
                    'jumlah' => $obat['jml'],
                    'frequency' => 3
                ];
            }
        }

        if (!empty($listObat)) {
            $pasien_med = [
                'nama' => $data_sep['nm_pasien'],
                'no_rm' => $data_sep['nomr']
            ];
            $dokter_med = [
                'kd' => $data_sep['kd_dokter'],
                'nama' => $data_sep['nm_dokter'],
                'org' => $orgId
            ];
            
            $medications = $this->buildMedicationResource($listObat, $pasien_med, $dokter_med, $diagnosa);
            foreach ($medications as $med) {
                $entries[] = $this->entry($med);
            }
        }

        // Data Observation / Hasil Lab (Opsional)
        // Ambil data periksa_lab berdasarkan no_rawat
        $lab_db = $this->db('detail_periksa_lab')
            ->join('template_laboratorium', 'detail_periksa_lab.id_template = template_laboratorium.id_template')
            ->where('no_rawat', $no_rawat)
            ->toArray();
            
        if (!empty($lab_db)) {
            $observations = [];
            foreach ($lab_db as $lab) {
                $obsId = 'DX' . uniqid();
                $obs_data = [
                    'loinc' => '12345-6', // TODO: Mapping kode LOINC dengan id_template
                    'pemeriksaan' => $lab['Pemeriksaan'],
                    'hasil' => (float)$lab['nilai'],
                    'satuan' => $lab['satuan']
                ];
                $observations[] = $this->observation($obsId, $obs_data, $data_sep['kd_dokter'], $data_sep['nm_dokter'], 'LAB');
            }
            
            // Tambahkan DiagnosticReport jika ada lab, dengan embed observation di result dan category LAB
            $diagId = $data_sep['nomr'] . '-' . uniqid();
            $entries[] = $this->entry($this->diagnostic($diagId, $data_sep['nomr'], $data_sep['nm_pasien'], $no_sep, $orgId, $this->settings->get('settings.nama_instansi'), $observations, 'LAB'));
        }

        // Data Observation / Hasil Radiologi (Opsional)
        // Ambil data hasil_radiologi berdasarkan no_rawat
        $rad_db = $this->db('hasil_radiologi')
            ->join('periksa_radiologi', 'hasil_radiologi.no_rawat = periksa_radiologi.no_rawat AND hasil_radiologi.tgl_periksa = periksa_radiologi.tgl_periksa AND hasil_radiologi.jam = periksa_radiologi.jam')
            ->join('jns_perawatan_radiologi', 'periksa_radiologi.kd_jenis_prw = jns_perawatan_radiologi.kd_jenis_prw')
            ->where('hasil_radiologi.no_rawat', $no_rawat)
            ->toArray();
            
        if (!empty($rad_db)) {
            $observations_rad = [];
            foreach ($rad_db as $rad) {
                $obsId = 'DX' . uniqid();
                $tgl_periksa = $rad['tgl_periksa'] . ' ' . $rad['jam'];
                $obs_data = [
                    'loinc' => 'PROCx000025499', // TODO: Mapping kode SNOMED CT dengan kd_jenis_prw
                    'pemeriksaan' => $rad['nm_perawatan'],
                    'hasil' => $rad['hasil'], // Narasi hasil radiologi
                    'tgl_periksa' => $tgl_periksa,
                    'conclusion' => "Hasil Radiologi " . $rad['nm_perawatan']
                ];
                // Kita bisa menggunakan dokter perujuk atau dokter spesialis radiologi jika tersedia, di sini fallback ke dpjp
                $dokter_rad = $rad['kd_dokter'] ?? $data_sep['kd_dokter'];
                // Jika ingin nama dokter rad, bisa dijoin ke tabel dokter, tapi sementara pakai fallback
                $nama_dokter_rad = $data_sep['nm_dokter']; 
                $observations_rad[] = $this->observation($obsId, $obs_data, $dokter_rad, $nama_dokter_rad, 'RAD');
            }
            
            // Tambahkan DiagnosticReport jika ada rad, dengan embed observation di result dan category RAD
            $diagIdRad = $data_sep['nomr'] . '-' . uniqid();
            $entries[] = $this->entry($this->diagnostic($diagIdRad, $data_sep['nomr'], $data_sep['nm_pasien'], $no_sep, $orgId, $this->settings->get('settings.nama_instansi'), $observations_rad, 'RAD'));
        }

        // Data Procedure / Tindakan (Opsional)
        // Ambil data rawat_jl_dr atau rawat_inap_dr
        $tindakan_db = $this->db('rawat_jl_dr')
            ->join('jns_perawatan', 'rawat_jl_dr.kd_jenis_prw = jns_perawatan.kd_jenis_prw')
            ->where('no_rawat', $no_rawat)
            ->toArray();
            
        $procedures_list = [];
        if (!empty($tindakan_db)) {
            foreach ($tindakan_db as $tnd) {
                $procedures_list[] = [
                    'encounter' => $encounterId,
                    'snom_pr' => '123456789', // TODO: Mapping SNOMED CT dokter
                    'snom_dsp' => 'General Practitioner',
                    'note' => $tnd['nm_perawatan'],
                    'coding' => [
                        [
                            'system' => 'http://snomed.info/sct',
                            'code' => '123456', // TODO: Mapping SNOMED CT tindakan
                            'display' => $tnd['nm_perawatan']
                        ]
                    ]
                ];
            }
        }
        
        if (!empty($procedures_list)) {
            $procs = $this->procedures($orgId, $data_sep['nomr'], $data_sep['nm_pasien'], $data_sep['nm_dokter'], $data_sep['kd_dokter'], $start, $end, $procedures_list);
            if (isset($procs['resource'])) {
                foreach ($procs['resource'] as $p) {
                    $entries[] = $this->entry($p);
                }
            }
        }

        // Data Device / Alkes (Opsional)
        // Contoh penggunaan fungsi device yang baru ditambahkan
        $devices_list = [];
        // TODO: Ambil data alkes/device dari database (misal tabel detail_pemberian_obat jika alkes dimasukkan ke sana, atau tabel khusus alkes)
        // Contoh mock data jika ada alkes:
        $devices_list[] = [
            'identifier_value' => 'MDVx024590',
            'type_code' => 'MDVx024590',
            'type_display' => 'SKINTACT EASYTAB',
            'lotNumber' => '',
            'manufacturer' => '',
            'model' => '',
            'contact_value' => 'ext 4352'
        ];
        if (!empty($devices_list)) {
            $devs = $this->device($orgId, $data_sep['nomr'], $devices_list);
            if (isset($devs['resource'])) {
                foreach ($devs['resource'] as $d) {
                    $entries[] = $this->entry($d);
                }
            }
        }

        // Data Composition
        $compositionId = $no_sep . '-' . $start;
        $sectionData = [
            [
                "title" => "Reason for admission",
                "system" => "http://loinc.org",
                "code" => "29299-5",
                "display" => "Reason for visit Narrative",
                "text" => ""
            ],
            [
                "title" => "Chief complaint",
                "system" => "http://loinc.org",
                "code" => "10154-3",
                "display" => "Chief complaint Narrative",
                "text" => ""
            ]
        ];

        // Tambahkan Admission diagnosis (opsional / jika ada)
        if (!empty($diagnosa)) {
            $sectionData[] = [
                "title" => "Admission diagnosis",
                "system" => "http://loinc.org",
                "code" => "42347-5",
                "display" => "Admission diagnosis Narrative",
                "text" => implode(", ", array_column($diagnosa, 'display')) . ",",
                "entry" => [
                    ["reference" => "urn:uuid:541a72a8-df75-4484-ac89-ac4923f03b81"] // TODO: Ganti urn dengan referensi Condition yg sesuai
                ]
            ];
        }

        // Tambahkan Medications on Discharge jika ada
        if (!empty($listObat)) {
            $med_entries = [];
            foreach ($medications as $med) {
                // asumsi id medication ditarik/dibuat untuk reference
                $med_entries[] = ["reference" => "MedicationRequest/".$med['id'] ?? 'dummy-id'];
            }
            $sectionData[] = [
                "title" => "Medications on Discharge",
                "system" => "http://loinc.org",
                "code" => "75311-1",
                "display" => "Hospital discharge medications Narrative",
                "text" => implode(", ", array_column($listObat, 'nama_obat')) . ",",
                "mode" => "working",
                "entry" => $med_entries
            ];
        }

        // Tambahkan Plan of care
        $sectionData[] = [
            "title" => "Plan of care",
            "system" => "http://loinc.org",
            "code" => "18776-5",
            "display" => "Plan of care",
            "text" => "",
            "mode" => "working",
            "entry" => [
                ["reference" => "MedicationRequest/124a6916-5d84-4b8c-b250-10cefb8e6e86"]
            ]
        ];

        // Tambahkan Known allergies
        $sectionData[] = [
            "title" => "Known allergies",
            "system" => "http://loinc.org",
            "code" => "48765-2",
            "display" => "Allergies and adverse reactions",
            "text" => "",
            "entry" => [
                ["reference" => "AllergyIntolerance/47600e0f-b6b5-4308-84b5-5dec157f7637"]
            ]
        ];

        $entries[] = $this->entry($this->composition(
            $compositionId,
            $data_sep['nomr'],
            $data_sep['nm_pasien'],
            $encounterId,
            $id_pr,
            $data_sep['nm_dokter'],
            $start,
            $sectionData
        ));

        // Build Final Bundle

        $consid = $this->settings->get('bpjs_emr.consid');
        $secretkey = $this->settings->get('bpjs_emr.secretkey');
        $userkey = $this->settings->get('bpjs_emr.userkey');
        $koders = $this->settings->get('bpjs_emr.koders');
        $baseurl = $this->settings->get('bpjs_emr.baseurl');

        $bundleId = $koders . '-' . $no_rawat . '-' . uniqid();
        $bundle = $this->bundle($bundleId, $no_sep, $entries);

        $json_payload = json_encode($bundle, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        
        $tahun = date('Y', strtotime($data_sep['tglsep']));
        $bulan = date('m', strtotime($data_sep['tglsep']));
        $jnsPelayanan = $data_sep['jnspelayanan']; // 1 = Ranap, 2 = Ralan

        // Kirim Payload ke BPJS menggunakan method internal
        $result = $this->sendMR($no_sep, $jnsPelayanan, $bulan, $tahun, $json_payload, $consid, $secretkey, $userkey, $koders, $baseurl);
        
        $status_kirim = 'Gagal';
        $response_bpjs = json_decode($result['response'], true);
        if ($response_bpjs && isset($response_bpjs['metaData']['code']) && $response_bpjs['metaData']['code'] == '200') {
            $status_kirim = 'Sukses';
        }
        
        // Simpan log ke tabel mlite_bpjs_emr_logs
        $this->db('mlite_bpjs_emr_logs')->save([
            'no_sep' => $no_sep,
            'no_rawat' => $no_rawat,
            'payload_json' => $json_payload,
            'payload_encrypted' => $result['encryptedBody'],
            'response' => $result['response'],
            'status' => $status_kirim,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        echo json_encode([
            'status' => 'success',
            'payload' => $json_payload,
            'payloadEncrypted' => $result['encryptedBody'],
            'response' => $result['response']
        ]);
        exit;
    }

    public function getLog()
    {
        header('Content-Type: application/json');
        $no_sep = $_GET['no_sep'] ?? '';
        
        if (empty($no_sep)) {
            echo json_encode(['status' => 'error', 'message' => 'No SEP tidak valid.']);
            exit;
        }

        $logs = $this->db('mlite_bpjs_emr_logs')
            ->where('no_sep', $no_sep)
            ->desc('id')
            ->toArray();

        if (!empty($logs)) {
            echo json_encode([
                'status' => 'success',
                'data' => $logs
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Data log tidak ditemukan.']);
        }
        exit;
    }

    public function getSettings()
    {
        $settings_db = $this->db('mlite_settings')->where('module', 'bpjs_emr')->toArray();
        $settings = array_column($settings_db, 'value', 'field');
        
        return $this->draw('settings.html', ['settings' => $settings]);
    }

    public function postSaveSettings()
    {
        // Logic to save settings
        foreach ($_POST['settings'] as $key => $value) {
            $this->db('mlite_settings')
                ->where('module', 'bpjs_emr')
                ->where('field', $key)
                ->update(['value' => $value]);
        }
        $this->notify('success', 'Pengaturan berhasil disimpan');
        header('Location: ' . url([ADMIN, 'bpjs_emr', 'settings']));
        exit;
    }

    private function _addHeaderFiles()
    {
        $this->core->addCSS(url('assets/css/dataTables.bootstrap.min.css'));
        $this->core->addJS(url('assets/jscripts/jquery.dataTables.min.js'));
        $this->core->addJS(url('assets/jscripts/dataTables.bootstrap.min.js'));
        $this->core->addCSS(url('assets/css/bootstrap-datetimepicker.css'));
        $this->core->addJS(url('assets/jscripts/moment-with-locales.js'));
        $this->core->addJS(url('assets/jscripts/bootstrap-datetimepicker.js'));
        $this->core->addJS(url([ADMIN, 'apotek_ranap', 'javascript']), 'footer');
    }


}
