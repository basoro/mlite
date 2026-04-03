<?php

namespace Plugins\Mini_pacs;

use Exception;

class SatusehatDicomClient
{
    private $baseUrl;
    private $clientKey;
    private $secretKey;
    private $organizationId;
    private $cacheFile;

    public function __construct($config)
    {
        $this->baseUrl = rtrim($config['base_url'], '/');
        $this->clientKey = $config['client_key'];
        $this->secretKey = $config['secret_key'];
        $this->organizationId = $config['organization_id'];
        $this->cacheFile = __DIR__ . '/satusehat_token_cache.json';
    }

    public function getToken()
    {
        if (file_exists($this->cacheFile)) {
            $data = json_decode(file_get_contents($this->cacheFile), true);
            if ($data && $data['expired_at'] > time()) {
                return $data['access_token'];
            }
        }

        $token = $this->requestToken();

        file_put_contents($this->cacheFile, json_encode([
            'access_token' => $token,
            'expired_at' => time() + 3500
        ]));

        return $token;
    }

    private function requestToken()
    {
        $url = $this->baseUrl . '/oauth2/v1/accesstoken?grant_type=client_credentials';

        $ch = curl_init();

        $postData = http_build_query([
            'client_id' => trim($this->clientKey),
            'client_secret' => trim($this->secretKey)
        ]);

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/x-www-form-urlencoded"
            ],
            CURLOPT_POSTFIELDS => $postData
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new Exception('Curl Error (Token): ' . curl_error($ch));
        }

        curl_close($ch);

        $json = json_decode($response, true);

        if (!isset($json['access_token'])) {
            throw new Exception('Gagal mendapatkan token: ' . $response);
        }

        return $json['access_token'];
    }

    /**
     * Upload DICOM + return StudyInstanceUID
     */
    public function uploadDicom($filePath)
    {
        $token = $this->getToken();

        $url = $this->baseUrl . '/dicom/v1/dicomWeb/studies';

        $boundary = "----mliteBoundary" . md5(time());

        $fileContent = file_get_contents($filePath);

        $body = "--$boundary\r\n";
        $body .= "Content-Type: application/dicom\r\n";
        $body .= "Content-Transfer-Encoding: binary\r\n\r\n";
        $body .= $fileContent . "\r\n";
        $body .= "--$boundary--\r\n";

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer $token",
                "Content-Type: multipart/related; type=application/dicom; boundary=$boundary",
                "Content-Length: " . strlen($body)
            ],
            CURLOPT_POSTFIELDS => $body
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new Exception('Upload Error: ' . curl_error($ch));
        }

        curl_close($ch);

        // ✅ HANDLE DUPLICATE DI SINI
        if ($this->isDuplicateDicom($response)) {
            return [
                'status' => 'duplicate',
                'message' => 'DICOM already exists',
                'raw' => $response
            ];
        }


        // parsing StudyInstanceUID
        $studyUID = $this->extractStudyUID($response);

        return [
            'raw' => $response,
            'study_uid' => $studyUID
        ];
    }

    /**
     * Extract StudyInstanceUID dari response DICOMWeb
     */
    private function extractStudyUID($response)
    {
        if (strpos($response, '<NativeDicomModel') === false) {
            return null;
        }

        $xml = simplexml_load_string($response);

        foreach ($xml->DicomAttribute as $attr) {
            if ((string) $attr['tag'] === '00081190') {
                $url = (string) $attr->Value;

                // ambil UID dari URL
                if (preg_match('/studies\/\'?([0-9\.]+)\'?/', $url, $match)) {
                    return $match[1];
                }
            }
        }

        return null;
    }

    /**
     * Kirim ImagingStudy ke SATUSEHAT
     */
    public function sendImagingStudy($data)
    {
        $token = $this->getToken();

        $url = $this->baseUrl . '/fhir-r4/v1/ImagingStudy';

        $patientId = $data['patientId'];
        $encounterId = $data['encounterId'];
        $serviceRequestId = $data['serviceRequestId'];
        $noRawat = $data['noRawat'];
        $noOrder = $data['noOrder'];
        $studyUID = $data['studyUID'];
        $seriesUID = $data['seriesUID'];
        $instanceUID = $data['instanceUID'];

        $payload = [
            "resourceType" => "ImagingStudy",
            "identifier" => [
                [
                    "use" => "usual",
                    "type" => [
                        "coding" => [
                            [
                                "system" => "http://terminology.hl7.org/CodeSystem/v2-0203",
                                "code" => "ACSN"
                            ]
                        ]
                    ],
                    "system" => "http://sys-ids.kemkes.go.id/acsn/" . $this->organizationId,
                    "value" => strval($noOrder)
                ],
                [
                    "system" => "urn:dicom:uid",
                    "value" => "urn:oid:" . trim($studyUID, "'")
                ]
            ],
            "status" => "available",
            "modality" => [
                [
                    "system" => "http://dicom.nema.org/resources/ontology/DCM",
                    "code" => "OP"
                ]
            ],
            "subject" => [
                "reference" => "Patient/" . $patientId
            ],
            "started" => date('c'),
            "basedOn" => [
                [
                    "reference" => "ServiceRequest/" . $serviceRequestId
                ]
            ],
            "numberOfSeries" => 1,
            "numberOfInstances" => 1,
            "series" => [
                [
                    "uid" => trim($seriesUID, "'"),
                    "number" => 1,
                    "modality" => [
                        "system" => "http://dicom.nema.org/resources/ontology/DCM",
                        "code" => "OP"
                    ],
                    "numberOfInstances" => 1,
                    "started" => date('c'),
                    "instance" => [
                        [
                            "uid" => trim($instanceUID, "'"),
                            "sopClass" => [
                                "system" => "urn:ietf:rfc:3986",
                                "code" => "urn:oid:1.2.840.10008.5.1.4.1.1.77.1.5.1"
                            ],
                            "number" => 1,
                            "title" => "ORIGINAL\\\\PRIMARY"
                        ]
                    ]
                ]
            ]
        ];


        // $payload = [
        //     "resourceType" => "ImagingStudy",
        //     "status" => "available",

        //     "subject" => [
        //         "reference" => "Patient/" . $patientId
        //     ],

        //     "encounter" => [
        //         "reference" => "Encounter/" . $encounterId
        //     ],

        //     "basedOn" => [
        //         [
        //             "reference" => "ServiceRequest/" . $serviceRequestId
        //         ]
        //     ],

        //     "identifier" => [
        //         [
        //             "system" => "http://sys-ids.kemkes.go.id/acsn/" . $this->organizationId,
        //             "value" => $noOrder // noorder radiologi
        //         ],
        //         [
        //             "system" => "urn:dicom:uid",
        //             "value" => "urn:oid:" . trim($data['studyUID'], "'")
        //         ]
        //     ],

        //     "started" => date('c'),

        //     "modality" => [
        //         [
        //             "system" => "http://dicom.nema.org/resources/ontology/DCM",
        //             "code" => "DX"
        //         ]
        //     ],

        //     "numberOfSeries" => 1,
        //     "numberOfInstances" => 1,

        //     "series" => [
        //         [
        //             "uid" => $seriesUID,
        //             "number" => 1,

        //             "modality" => [
        //                 "system" => "http://dicom.nema.org/resources/ontology/DCM",
        //                 "code" => "DX"
        //             ],

        //             "numberOfInstances" => 1,

        //             "instance" => [
        //                 [
        //                     "uid" => $instanceUID,
        //                     "number" => 1,
        //                     "sopClass" => [
        //                         "system" => "urn:ietf:rfc:3986",
        //                         "code" => "urn:oid:1.2.840.10008.5.1.4.1.1.1"
        //                     ]
        //                 ]
        //             ]
        //         ]
        //     ]
        // ];        

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer $token",
                "Content-Type: application/json"
            ],
            CURLOPT_POSTFIELDS => json_encode($payload)
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new Exception('FHIR Error: ' . curl_error($ch));
        }

        curl_close($ch);

        return [
            'payload' => json_encode($payload, JSON_PRETTY_PRINT),
            'response' => $response
        ];
    }

    private function parseDicomResponse($response)
    {
        $result = [
            'raw' => $response,
            'study_uid' => null,
            'series_uid' => null,
            'instance_uid' => null
        ];

        $start = strpos($response, '<NativeDicomModel');
        $end = strpos($response, '</NativeDicomModel>');

        if ($start !== false && $end !== false) {
            $cleanXml = substr($response, $start, $end - $start + 19);
            libxml_use_internal_errors(true);
            $xml = simplexml_load_string($cleanXml);

            if ($xml) {
                foreach ($xml->DicomAttribute as $attr) {
                    if ((string) $attr['tag'] === '00081199') {
                        foreach ($attr->Item as $item) {
                            foreach ($item->DicomAttribute as $sub) {
                                if ((string) $sub['tag'] === '00081155') {
                                    $uid = (string) $sub->Value;
                                    $result['instance_uid'] = trim($uid, "'");
                                }
                            }
                        }
                    }
                }
            }
        }

        return $result;
    }

    public function generateUID()
    {
        $base = "1.2.826.0.1.3680043.2.1125"; // OID RS (bisa disesuaikan)

        return [
            'study' => $base . '.' . uniqid(),
            'series' => $base . '.' . uniqid(),
            'instance' => $base . '.' . uniqid(),
        ];
    }

    /**
     * ============================
     * FULL PIPELINE
     * ============================
     */
    public function sendFull($filePath, $data)
    {
        // 1. generate UID
        $uids = $this->generateUID();

        $data['studyUID'] = $uids['study'];
        $data['seriesUID'] = $uids['series'];
        $data['instanceUID'] = $uids['instance'];

        // 2. kirim ImagingStudy
        $fhir = $this->sendImagingStudy($data);

        // 3. upload DICOM
        $dicom = $this->uploadDicom($filePath);

        return [
            'uids' => $uids,
            'fhir' => $fhir,
            'dicom' => $dicom
        ];
    }

    private function isDuplicateDicom($response)
    {
        $start = strpos($response, '<NativeDicomModel');
        $end = strpos($response, '</NativeDicomModel>');
        if ($start === false || $end === false) {
            return false;
        }

        $cleanXml = substr($response, $start, $end - $start + 19);
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($cleanXml);

        if (!$xml) {
            return false;
        }

        foreach ($xml->DicomAttribute as $attr) {

            if ((string) $attr['tag'] === '00081198') { // FailedSOPSequence

                foreach ($attr->Item as $item) {

                    foreach ($item->DicomAttribute as $sub) {

                        if ((string) $sub['tag'] === '00090097') { // FailureDetail

                            $value = '';

                            if (isset($sub->Value)) {
                                $value = strtolower(trim((string) $sub->Value[0]));
                            }

                            // DEBUG (optional)
                            // file_put_contents('debug.txt', $value . PHP_EOL, FILE_APPEND);

                            if (strpos($value, 'already exists') !== false) {
                                return true;
                            }
                        }
                    }
                }
            }
        }

        return false;
    }


}