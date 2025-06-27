<?php

namespace Plugins\Satu_Sehat;

use SatuSehat\Src\CarePlan;
use Systems\AdminModule;
use SatuSehat\Src\ClinicalImpression;
use SatuSehat\Src\Composition;
use SatuSehat\Src\Condition;
use SatuSehat\Src\DiagnosticReport;
use SatuSehat\Src\Respiratory;
use SatuSehat\Src\Observation;
use SatuSehat\Src\Medication;
use SatuSehat\Src\MedicationDispense;
use SatuSehat\Src\MedicationRequest;
use SatuSehat\Src\Procedure;
use SatuSehat\Src\QuestionareMedication;
use SatuSehat\Src\ServiceRequest;
use SatuSehat\Src\Temperature;
use SatuSehat\Src\Specimen;

class Admin extends AdminModule
{

  private $authurl;
  private $fhirurl;
  private $clientid;
  private $secretkey;
  private $organizationid;  

  public function init()
  {
    $this->authurl = $this->settings->get('satu_sehat.authurl');
    $this->fhirurl = $this->settings->get('satu_sehat.fhirurl');
    $this->clientid = $this->settings->get('satu_sehat.clientid');
    $this->secretkey = $this->settings->get('satu_sehat.secretkey');
    $this->organizationid = $this->settings->get('satu_sehat.organizationid');
  }

  public function getTest()
  {
    // $searchResult = $this->searchObat('paracetamol');
    // echo json_encode($searchResult);
    // echo json_encode($this->getObatByCode('93021992'));

    $row['medications'] = $this->db('resep_obat')
      ->join('resep_dokter', 'resep_dokter.no_resep=resep_obat.no_resep')
      ->join('mlite_satu_sehat_mapping_obat', 'mlite_satu_sehat_mapping_obat.kode_brng=resep_dokter.kode_brng')
      ->where('mlite_satu_sehat_mapping_obat.tipe', 'obat')
      ->where('no_rawat', '2025/06/08/000001')->toArray();

    // echo json_encode($row['medications']);
    echo date('c', strtotime('2025-06-08'));

    exit();
  }

  public function getObatByCode($code)
  {
    if (!json_decode($this->getToken())->access_token) {
      return ['error' => 'Gagal mendapatkan access token'];
    }

    $url = $this->settings->get('satu_sehat.authurl');
    $parsed = parse_url($url);
    $baseUrl = $parsed['scheme'] . '://' . $parsed['host'];

    $ch = curl_init();
    curl_setopt_array($ch, [
      CURLOPT_URL => $baseUrl . '/kfa-v2/products?identifier=kfa&code=' . urlencode($code),
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . json_decode($this->getToken())->access_token,
        'Accept: application/json'
      ]
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
  }

  public function navigation()
  {
    return [
      'Kelola'   => 'manage',
      'Referensi Praktisi'   => 'praktisi',
      'Referensi Pasien'   => 'pasien',
      'Mapping Departemen'   => 'departemen',
      'Mapping Lokasi'   => 'lokasi',
      'Mapping Praktisi'   => 'mappingpraktisi',
      'Mapping Obat'   => 'mappingobat',
      'Mapping Laboratorium'   => 'mappinglab',
      'Mapping Radiologi'   => 'mappingrad',
      'Data Response'   => 'response',
      'Verifikasi KYC' => 'kyc',
      'Pengaturan'   => 'settings',
    ];
  }

  public function getManage()
  {
    $sub_modules = [
      ['name' => 'Referensi Praktisi', 'url' => url([ADMIN, 'satu_sehat', 'praktisi']), 'icon' => 'heart', 'desc' => 'Referensi praktisi satu sehat'],
      ['name' => 'Referensi Pasien', 'url' => url([ADMIN, 'satu_sehat', 'pasien']), 'icon' => 'heart', 'desc' => 'Referensi pasien satu sehat'],
      ['name' => 'Mapping Departemen', 'url' => url([ADMIN, 'satu_sehat', 'departemen']), 'icon' => 'heart', 'desc' => 'Mapping departemen satu sehat'],
      ['name' => 'Mapping Lokasi', 'url' => url([ADMIN, 'satu_sehat', 'lokasi']), 'icon' => 'heart', 'desc' => 'Mapping lokasi satu sehat'],
      ['name' => 'Mapping Praktisi', 'url' => url([ADMIN, 'satu_sehat', 'mappingpraktisi']), 'icon' => 'heart', 'desc' => 'Mapping praktisi satu sehat'],
      ['name' => 'Mapping Obat', 'url' => url([ADMIN, 'satu_sehat', 'mappingobat']), 'icon' => 'heart', 'desc' => 'Mapping obat satu sehat'],
      ['name' => 'Mapping Laboratorium', 'url' => url([ADMIN, 'satu_sehat', 'mappinglab']), 'icon' => 'heart', 'desc' => 'Mapping laboratorium satu sehat'],
      ['name' => 'Mapping Radiologi', 'url' => url([ADMIN, 'satu_sehat', 'mappingrad']), 'icon' => 'heart', 'desc' => 'Mapping radiologi satu sehat'],
      ['name' => 'Data Response', 'url' => url([ADMIN, 'satu_sehat', 'response']), 'icon' => 'heart', 'desc' => 'Data encounter satu sehat'],
      ['name' => 'Verifikasi KYC', 'url' => url([ADMIN, 'satu_sehat', 'kyc']), 'icon' => 'heart', 'desc' => 'Verifikasi KYC satu sehat'],
      ['name' => 'Pengaturan', 'url' => url([ADMIN, 'satu_sehat', 'settings']), 'icon' => 'heart', 'desc' => 'Pengaturan satu sehat'],
    ];
    return $this->draw('manage.html', ['sub_modules' => $sub_modules]);
  }

  public function getToken()
  {

    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => $this->settings->get('satu_sehat.authurl') . '/accesstoken?grant_type=client_credentials',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS => 'client_id=' . $this->clientid . '&client_secret=' . $this->secretkey,
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    return $response;
    // echo $response;    
    // exit();
  }

  public function getPractitioner($nik_dokter)
  {

    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => $this->fhirurl . '/Practitioner?identifier=https://fhir.kemkes.go.id/id/nik|' . $nik_dokter,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_HTTPHEADER => array('Content-Type: application/json', 'Authorization: Bearer ' . json_decode($this->getToken())->access_token),
      CURLOPT_CUSTOMREQUEST => 'GET'
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    return $response;
    // echo $response;
    // exit();

  }

  public function getPractitionerID($nik_dokter)
  {
    echo json_decode($this->getPractitioner($nik_dokter))->entry[0]->resource->id;
    exit();
  }

  public function getPractitionerByID($id_dokter)
  {

    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => $this->fhirurl . '/Practitioner/' . $id_dokter,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'GET',
      CURLOPT_HTTPHEADER => array('Content-Type: application/json', 'Authorization: Bearer ' . json_decode($this->getToken())->access_token),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    echo $response;
    exit();
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
      CURLOPT_HTTPHEADER => array('Content-Type: application/json', 'Authorization: Bearer ' . json_decode($this->getToken())->access_token),
      CURLOPT_CUSTOMREQUEST => 'GET'
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    return $response;
    // echo $response;
    // exit();

  }

  public function getPatientID($nik_pasien)
  {
    echo json_decode($this->getPatient($nik_pasien))->entry[0]->resource->id;
    exit();
  }

  public function getPatientByID($id_pasien)
  {

    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => $this->fhirurl . '/Patient/' . $id_pasien,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'GET',
      CURLOPT_HTTPHEADER => array('Content-Type: application/json', 'Authorization: Bearer ' . json_decode($this->getToken())->access_token),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    echo $response;
    exit();
  }

  public function getOrganization($kode_departemen, $kode_organization = '')
  {
    $partOf = $this->organizationid;
    $nameResource = $this->core->getDepartemenInfo($kode_departemen);
    if ($kode_organization != '') {
      $partOf = $kode_organization;
      $nameResource = $this->core->getPoliklinikInfo('nm_poli', $kode_departemen);
    }
    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => $this->fhirurl . '/Organization',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_HTTPHEADER => array('Content-Type: application/json', 'Authorization: Bearer ' . json_decode($this->getToken())->access_token),
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS => '{
        "resourceType": "Organization",
        "active": true,
        "identifier": [
            {
                "use": "official",
                "system": "http://sys-ids.kemkes.go.id/organization/' . $this->organizationid . '",
                "value": "' . $kode_departemen . '"
            }
        ],
        "type": [
            {
                "coding": [
                    {
                        "system": "http://terminology.hl7.org/CodeSystem/organization-type",
                        "code": "dept",
                        "display": "Hospital Department"
                    }
                ]
            }
        ],
        "name": "' . $nameResource . '",
        "telecom": [
            {
                "system": "phone",
                "value": "' . $this->settings->get('settings.nomor_telepon') . '",
                "use": "work"
            },
            {
                "system": "email",
                "value": "' . $this->settings->get('settings.email') . '",
                "use": "work"
            },
            {
                "system": "url",
                "value": "www.' . $this->settings->get('settings.email') . '",
                "use": "work"
            }
        ],
        "address": [
            {
                "use": "work",
                "type": "both",
                "line": [
                    "' . $this->settings->get('settings.alamat') . '"
                ],
                "city": "' . $this->settings->get('settings.kota') . '",
                "postalCode": "' . $this->settings->get('satu_sehat.kodepos') . '",
                "country": "ID",
                "extension": [
                    {
                        "url": "https://fhir.kemkes.go.id/r4/StructureDefinition/administrativeCode",
                        "extension": [
                            {
                                "url": "province",
                                "valueCode": "' . $this->settings->get('satu_sehat.propinsi') . '"
                            },
                            {
                                "url": "city",
                                "valueCode": "' . $this->settings->get('satu_sehat.kabupaten') . '"
                            },
                            {
                                "url": "district",
                                "valueCode": "' . $this->settings->get('satu_sehat.kecamatan') . '"
                            },
                            {
                                "url": "village",
                                "valueCode": "' . $this->settings->get('satu_sehat.kelurahan') . '"
                            }
                        ]
                    }
                ]
            }
        ],
        "partOf": {
            "reference": "Organization/' . $partOf . '"
        }
    }',
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    return $response;
    // echo $response;
    // exit();    
  }

  public function getOrganizationById($kode_departemen)
  {

    $mlite_satu_sehat_departemen = $this->db('mlite_satu_sehat_departemen')->where('dep_id', $kode_departemen)->oneArray();

    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => $this->fhirurl . '/Organization/' . $mlite_satu_sehat_departemen['id_organisasi_satusehat'],
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_HTTPHEADER => array('Content-Type: application/json', 'Authorization: Bearer ' . json_decode($this->getToken())->access_token),
      CURLOPT_CUSTOMREQUEST => 'GET',
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    echo $response;
    exit();
  }

  public function getOrganizationByPart($kode_departemen)
  {

    $mlite_satu_sehat_departemen = $this->db('mlite_satu_sehat_departemen')->where('dep_id', $kode_departemen)->oneArray();

    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => $this->fhirurl . '/Organization?partof=' . $this->organizationid,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_HTTPHEADER => array('Content-Type: application/json', 'Authorization: Bearer ' . json_decode($this->getToken())->access_token),
      CURLOPT_CUSTOMREQUEST => 'GET',
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    echo $response;
    exit();
  }

  public function getOrganizationUpdate($kode_departemen)
  {

    $mlite_satu_sehat_departemen = $this->db('mlite_satu_sehat_departemen')->where('dep_id', $kode_departemen)->oneArray();

    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => $this->fhirurl . '/Organization/' . $mlite_satu_sehat_departemen['id_organisasi_satusehat'],
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_HTTPHEADER => array('Content-Type: application/json', 'Authorization: Bearer ' . json_decode($this->getToken())->access_token),
      CURLOPT_CUSTOMREQUEST => 'PUT',
      CURLOPT_POSTFIELDS => '{
        "resourceType": "Organization",
        "id":"' . $mlite_satu_sehat_departemen['id_organisasi_satusehat'] . '",
        "active": true,
        "identifier": [
            {
                "use": "official",
                "system": "http://sys-ids.kemkes.go.id/organization/' . $this->organizationid . '",
                "value": "' . $kode_departemen . '"
            }
        ],
        "type": [
            {
                "coding": [
                    {
                        "system": "http://terminology.hl7.org/CodeSystem/organization-type",
                        "code": "dept",
                        "display": "Hospital Department"
                    }
                ]
            }
        ],
        "name": "' . $this->core->getDepartemenInfo($kode_departemen) . '",
        "telecom": [
            {
                "system": "phone",
                "value": "' . $this->settings->get('settings.nomor_telepon') . '",
                "use": "work"
            },
            {
                "system": "email",
                "value": "' . $this->settings->get('settings.email') . '",
                "use": "work"
            },
            {
                "system": "url",
                "value": "www.' . $this->settings->get('settings.email') . '",
                "use": "work"
            }
        ],
        "address": [
            {
                "use": "work",
                "type": "both",
                "line": [
                    "' . $this->settings->get('settings.alamat') . '"
                ],
                "city": "' . $this->settings->get('settings.kota') . '",
                "postalCode": "' . $this->settings->get('satu_sehat.kodepos') . '",
                "country": "ID",
                "extension": [
                    {
                        "url": "https://fhir.kemkes.go.id/r4/StructureDefinition/administrativeCode",
                        "extension": [
                            {
                                "url": "province",
                                "valueCode": "' . $this->settings->get('satu_sehat.propinsi') . '"
                            },
                            {
                                "url": "city",
                                "valueCode": "' . $this->settings->get('satu_sehat.kabupaten') . '"
                            },
                            {
                                "url": "district",
                                "valueCode": "' . $this->settings->get('satu_sehat.kecamatan') . '"
                            },
                            {
                                "url": "village",
                                "valueCode": "' . $this->settings->get('satu_sehat.kelurahan') . '"
                            }
                        ]
                    }
                ]
            }
        ],
        "partOf": {
            "reference": "Organization/' . $this->settings->get('satu_sehat.organizationid') . '"
        }
    }',
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    echo $response;
    exit();
  }

  public function getLocation($kode, $kode_organization = '')
  {
    $lokasi = '';
    if (!empty($this->core->getPoliklinikInfo('nm_poli', $kode))) {
      $lokasi = $this->core->getPoliklinikInfo('nm_poli', $kode);
    } else {
      $lokasi = $this->core->getBangsalInfo('nm_bangsal', $kode);
    }

    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => $this->fhirurl . '/Location',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_HTTPHEADER => array('Content-Type: application/json', 'Authorization: Bearer ' . json_decode($this->getToken())->access_token),
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS => '{
        "resourceType": "Location",
        "identifier": [
            {
                "system": "http://sys-ids.kemkes.go.id/location/' . $this->organizationid . '",
                "value": "' . $kode . '"
            }
        ],
        "status": "active",
        "name": "' . $lokasi . '",
        "description": "' . $kode . ' - ' . $lokasi . '",
        "mode": "instance",
        "telecom": [
          {
              "system": "phone",
              "value": "' . $this->settings->get('settings.nomor_telepon') . '",
              "use": "work"
          },
          {
              "system": "email",
              "value": "' . $this->settings->get('settings.email') . '",
              "use": "work"
          },
          {
              "system": "url",
              "value": "www.' . $this->settings->get('settings.email') . '",
              "use": "work"
          }
        ],
        "address": {
            "use": "work",
            "line": [
                "' . $this->settings->get('settings.alamat') . '"
            ],
            "city": "' . $this->settings->get('settings.kota') . '",
            "postalCode": "' . $this->settings->get('satu_sehat.kodepos') . '",
            "country": "ID",
            "extension": [
                {
                    "url": "https://fhir.kemkes.go.id/r4/StructureDefinition/administrativeCode",
                    "extension": [
                        {
                            "url": "province",
                            "valueCode": "' . $this->settings->get('satu_sehat.propinsi') . '"
                        },
                        {
                            "url": "city",
                            "valueCode": "' . $this->settings->get('satu_sehat.kabupaten') . '"
                        },
                        {
                            "url": "district",
                            "valueCode": "' . $this->settings->get('satu_sehat.kecamatan') . '"
                        },
                        {
                            "url": "village",
                            "valueCode": "' . $this->settings->get('satu_sehat.kelurahan') . '"
                        },
                        {
                            "url": "rt",
                            "valueCode": "1"
                        },
                        {
                            "url": "rw",
                            "valueCode": "2"
                        }
                    ]
                }
            ]
        },
        "physicalType": {
            "coding": [
                {
                    "system": "http://terminology.hl7.org/CodeSystem/location-physical-type",
                    "code": "ro",
                    "display": "Room"
                }
            ]
        },
        "position": {
            "longitude": ' . $this->settings->get('satu_sehat.longitude') . ',
            "latitude": ' . $this->settings->get('satu_sehat.latitude') . ',
            "altitude": 0
      },
        "managingOrganization": {
            "reference": "Organization/' . $kode_organization . '"
        }
    }',
    ));

    $response = curl_exec($curl);

    if (json_decode($response)->issue[0]->code == 'duplicate') {
      $curl = curl_init();
      curl_setopt_array($curl, array(
        CURLOPT_URL => $this->settings->get('satu_sehat.fhirurl') . '/Location?organization=' . $kode_organization,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_HTTPHEADER => array('Content-Type: application/json', 'Authorization: Bearer ' . json_decode($this->getToken())->access_token),
        CURLOPT_CUSTOMREQUEST => 'GET',
      ));

      $response = curl_exec($curl);
    }

    curl_close($curl);

    return $response;
    // echo $response;
  }

  public function getLocationByOrgId($kode_departemen)
  {

    $mlite_satu_sehat_lokasi = $this->db('mlite_satu_sehat_lokasi')->where('kode', $kode_departemen)->oneArray();

    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => $this->fhirurl . '/Location?organization=' . $mlite_satu_sehat_lokasi['id_lokasi_satusehat'],
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_HTTPHEADER => array('Content-Type: application/json', 'Authorization: Bearer ' . json_decode($this->getToken())->access_token),
      CURLOPT_CUSTOMREQUEST => 'GET',
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    echo $response;
    exit();
  }

  public function getLocationUpdate($kode_departemen)
  {

    $mlite_satu_sehat_lokasi = $this->db('mlite_satu_sehat_lokasi')->where('kode', $kode_departemen)->oneArray();

    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => $this->fhirurl . '/Location/' . $mlite_satu_sehat_lokasi['id_lokasi_satusehat'],
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_HTTPHEADER => array('Content-Type: application/json', 'Authorization: Bearer ' . json_decode($this->getToken())->access_token),
      CURLOPT_CUSTOMREQUEST => 'PUT',
      CURLOPT_POSTFIELDS => '{
        "resourceType": "Location",
        "id": "' . $mlite_satu_sehat_lokasi['id_lokasi_satusehat'] . '",
        "identifier": [
            {
                "system": "http://sys-ids.kemkes.go.id/location/' . $this->organizationid . '",
                "value": "' . $mlite_satu_sehat_lokasi['kode'] . '"
            }
        ],
        "status": "inactive",
        "name": "' . $mlite_satu_sehat_lokasi['lokasi'] . '",
        "description": "' . $mlite_satu_sehat_lokasi['kode'] . ' - ' . $mlite_satu_sehat_lokasi['lokasi'] . '",
        "mode": "instance",
        "telecom": [
            {
                "system": "phone",
                "value": "' . $this->settings->get('settings.nomor_telepon') . '",
                "use": "work"
            },
            {
                "system": "fax",
                "value": "' . $this->settings->get('settings.nomor_telepon') . '",
                "use": "work"
            },
            {
                "system": "email",
                "value": "' . $this->settings->get('settings.email') . '"
            },
            {
                "system": "url",
                "value": "' . $this->settings->get('settings.website') . '",
                "use": "work"
            }
        ],
        "address": {
            "use": "work",
            "line": [
                "' . $this->settings->get('settings.alamat') . '"
            ],
            "city": "' . $this->settings->get('settings.kota') . '",
            "postalCode": "' . $this->settings->get('satu_sehat.kodepos') . '",
            "country": "ID",
            "extension": [
                {
                    "url": "https://fhir.kemkes.go.id/r4/StructureDefinition/administrativeCode",
                    "extension": [
                        {
                            "url": "province",
                            "valueCode": "' . $this->settings->get('satu_sehat.propinsi') . '"
                        },
                        {
                            "url": "city",
                            "valueCode": "' . $this->settings->get('satu_sehat.kabupaten') . '"
                        },
                        {
                            "url": "district",
                            "valueCode": "' . $this->settings->get('satu_sehat.kecamatan') . '"
                        },
                        {
                            "url": "village",
                            "valueCode": "' . $this->settings->get('satu_sehat.kelurahan') . '"
                        },
                        {
                            "url": "rt",
                            "valueCode": "1"
                        },
                        {
                            "url": "rw",
                            "valueCode": "2"
                        }
                    ]
                }
            ]
        },
        "physicalType": {
            "coding": [
                {
                    "system": "http://terminology.hl7.org/CodeSystem/location-physical-type",
                    "code": "ro",
                    "display": "Room"
                }
            ]
        },
        "position": {
            "longitude": -6.23115426275766,
            "latitude": 106.83239885393944,
            "altitude": 0
        },
        "managingOrganization": {
            "reference": "Organization/' . $this->settings->get('satu_sehat.organizationid') . '"
        }
    }',
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    echo $response;
    exit();
  }

  public function getEncounter($no_rawat)
  {

    $zonawaktu = '+07:00';
    if ($this->settings->get('satu_sehat.zonawaktu') == 'WITA') {
      $zonawaktu = '+08:00';
    }
    if ($this->settings->get('satu_sehat.zonawaktu') == 'WIT') {
      $zonawaktu = '+09:00';
    }


    $no_rawat = revertNoRawat($no_rawat);
    $kd_poli = $this->core->getRegPeriksaInfo('kd_poli', $no_rawat);
    $nm_poli = $this->core->getPoliklinikInfo('nm_poli', $kd_poli);
    $kd_dokter = $this->core->getRegPeriksaInfo('kd_dokter', $no_rawat);
    $no_ktp_dokter = $this->db('mlite_satu_sehat_mapping_praktisi')->select('practitioner_id')->where('kd_dokter', $kd_dokter)->oneArray();
    $nama_dokter = $this->core->getPegawaiInfo('nama', $kd_dokter);
    $no_rkm_medis = $this->core->getRegPeriksaInfo('no_rkm_medis', $no_rawat);
    $no_ktp_pasien = $this->core->getPasienInfo('no_ktp', $no_rkm_medis);
    $nama_pasien = $this->core->getPasienInfo('nm_pasien', $no_rkm_medis);
    $status_lanjut = $this->core->getRegPeriksaInfo('status_lanjut', $no_rawat);
    $tgl_registrasi = $this->core->getRegPeriksaInfo('tgl_registrasi', $no_rawat);
    $jam_reg = $this->core->getRegPeriksaInfo('jam_reg', $no_rawat);
    $endTime = date("H:i:s", strtotime('+10 minutes', strtotime($jam_reg)));

    $code = 'AMB';
    $display = 'ambulatory';
    if ($status_lanjut == 'Ranap') {
      $kd_poli = $this->core->getKamarInapInfo('kd_kamar', $no_rawat);
      $kd_bangsal = $this->core->getKamarInfo('kd_bangsal', $kd_poli);
      $nm_poli = $this->core->getBangsalInfo('nm_bangsal', $kd_bangsal);
      $code = 'IMP';
      $display = 'inpatient encounter';
    }

    $mlite_satu_sehat_lokasi = $this->db('mlite_satu_sehat_lokasi')->where('kode', $kd_poli)->oneArray();

    $curl = curl_init();
    $json = '{
      "resourceType": "Encounter",
      "status": "arrived",
      "class": {
          "system": "http://terminology.hl7.org/CodeSystem/v3-ActCode",
          "code": "' . $code . '",
          "display": "' . $display . '"
      },
      "subject": {
          "reference": "Patient/' . json_decode($this->getPatient($no_ktp_pasien))->entry[0]->resource->id . '",
          "display": "' . $nama_pasien . '"
      },
      "participant": [
          {
              "type": [
                  {
                      "coding": [
                          {
                              "system": "http://terminology.hl7.org/CodeSystem/v3-ParticipationType",
                              "code": "ATND",
                              "display": "attender"
                          }
                      ]
                  }
              ],
              "individual": {
                  "reference": "Practitioner/' . $no_ktp_dokter['practitioner_id'] . '",
                  "display": "' . $nama_dokter . '"
              }
          }
      ],
      "period": {
          "start": "' . $tgl_registrasi . 'T' . $jam_reg . '' . $zonawaktu . '"
      },
      "location": [
          {
              "location": {
                  "reference": "Location/' . $mlite_satu_sehat_lokasi['id_lokasi_satusehat'] . '",
                  "display": "' . $kd_poli . ' ' . $nm_poli . '"
              }
          }
      ],
      "statusHistory": [
          {
              "status": "arrived",
              "period": {
                  "start": "' . $tgl_registrasi . 'T' . $jam_reg . '' . $zonawaktu . '",
                  "end": "' . $tgl_registrasi . 'T' . $endTime . '' . $zonawaktu . '"
              }
          }
      ],
      "serviceProvider": {
          "reference": "Organization/' . $this->organizationid . '"
      },
      "identifier": [
          {
              "system": "http://sys-ids.kemkes.go.id/encounter/' . $this->organizationid . '",
              "value": "' . $no_rawat . '"
          }
      ]
    }';
    curl_setopt_array($curl, array(
      CURLOPT_URL => $this->fhirurl . '/Encounter',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_HTTPHEADER => array('Content-Type: application/json', 'Authorization: Bearer ' . json_decode($this->getToken())->access_token),
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS => $json,
    ));

    $response = curl_exec($curl);

    $id_encounter = json_decode($response)->id;
    $pesan = 'Gagal mengirim encounter platform Satu Sehat!!';
    if ($id_encounter) {
      $this->db('mlite_satu_sehat_response')->save([
        'no_rawat' => $no_rawat,
        'id_encounter' => $id_encounter
      ]);
      $pesan = 'Sukses mengirim encounter platform Satu Sehat!!';
    }

    curl_close($curl);
    // echo $response;
    echo $this->draw('encounter.html', ['pesan' => $pesan, 'response' => $response, 'json' => $json]);
    exit();
  }

  public function getEncounterBundle($no_rawat, $param = '')
  {

    $zonawaktu = '+00:00';

    $no_rawat = revertNoRawat($no_rawat);
    $kd_poli = $this->core->getRegPeriksaInfo('kd_poli', $no_rawat);
    $nm_poli = $this->core->getPoliklinikInfo('nm_poli', $kd_poli);
    $kd_dokter = $this->core->getRegPeriksaInfo('kd_dokter', $no_rawat);
    $no_ktp_dokter = $this->db('mlite_satu_sehat_mapping_praktisi')->select('practitioner_id')->where('kd_dokter', $kd_dokter)->oneArray();
    $nama_dokter = $this->core->getPegawaiInfo('nama', $kd_dokter);
    $no_rkm_medis = $this->core->getRegPeriksaInfo('no_rkm_medis', $no_rawat);
    $no_ktp_pasien = $this->core->getPasienInfo('no_ktp', $no_rkm_medis);
    $nama_pasien = $this->core->getPasienInfo('nm_pasien', $no_rkm_medis);
    $status_lanjut = $this->core->getRegPeriksaInfo('status_lanjut', $no_rawat);
    $tgl_registrasi = $this->core->getRegPeriksaInfo('tgl_registrasi', $no_rawat);
    $jam_reg = $this->core->getRegPeriksaInfo('jam_reg', $no_rawat);
    $inProg = $this->db('pemeriksaan_ralan')->select(['tgl' => 'tgl_perawatan', 'jam' => 'jam_rawat', 'respirasi' => 'respirasi', 'suhu' => 'suhu_tubuh', 'tensi' => 'tensi', 'nadi' => 'nadi', 'penilaian' => 'penilaian','keluhan' => 'keluhan'])->where('no_rawat', $no_rawat)->oneArray();
    $diagnosa_pasien = $this->db('diagnosa_pasien')
      ->join('penyakit', 'penyakit.kd_penyakit=diagnosa_pasien.kd_penyakit')
      ->where('no_rawat', $no_rawat)
      ->where('diagnosa_pasien.status', $status_lanjut)
      ->where('prioritas', '1')
      ->oneArray();
    $_prosedure_pasien = $this->db('prosedur_pasien')->select(['deskripsi_pendek' => 'icd9.deskripsi_pendek', 'kode' => 'icd9.kode'])->join('icd9', 'icd9.kode = prosedur_pasien.kode')->where('prosedur_pasien.no_rawat', $no_rawat)->where('prosedur_pasien.status', 'Ralan')->where('prosedur_pasien.prioritas', '1')->oneArray();
    $prosedure_pasien = $_prosedure_pasien['deskripsi_pendek'];
    $kode_prosedure_pasien = $_prosedure_pasien['kode'];
    if (strpos($kode_prosedure_pasien, '.') !== false) {
      $kode_prosedure_pasien = $kode_prosedure_pasien;
    } else {
      $kode_prosedure_pasien = substr_replace($kode_prosedure_pasien, '.', 2, 0);
    }

    $mlite_billing = $this->db('mlite_billing')->where('no_rawat', $no_rawat)->oneArray();

    $kunjungan = 'Kunjungan';
    if ($status_lanjut == 'Ranap') {
      $kunjungan = 'Perawatan';
    }

    $code = 'AMB';
    $display = 'ambulatory';
    if ($status_lanjut == 'Ranap') {
      $kd_poli = $this->core->getKamarInapInfo('kd_kamar', $no_rawat);
      $kd_bangsal = $this->core->getKamarInfo('kd_bangsal', $kd_poli);
      $nm_poli = $this->core->getBangsalInfo('nm_bangsal', $kd_bangsal);
      $code = 'IMP';
      $display = 'inpatient encounter';
    }

    $mlite_satu_sehat_lokasi = $this->db('mlite_satu_sehat_lokasi')->where('kode', $kd_poli)->oneArray();

    $respiratory = '';
    $suhu = '';
    $diastole = '';
    $sistole = '';
    $nadi = '';
    $procedure = '';
    $uuid_encounter = $this->gen_uuid();
    $uuid_condition = $this->gen_uuid();
    $uuid_respiration = $this->gen_uuid();
    $uuid_suhu = $this->gen_uuid();
    $uuid_sistolik = $this->gen_uuid();
    $uuid_diastolik = $this->gen_uuid();
    $uuid_nadi = $this->gen_uuid();
    $uuid_procedure = $this->gen_uuid();
    $uuid_composition = $this->gen_uuid();
    $uuid_clinical_impression_history = $this->gen_uuid();
    $uuid_clinical_impression_prognosis = $this->gen_uuid();
    // $cek_ihs = $this->core->getPasienInfo('nip',$no_rkm_medis);
    // $ihs_patient = $cek_ihs;
    // if ($ihs_patient == '' || $ihs_patient == '-') {
    $ihs_patient = json_decode($this->getPatient($no_ktp_pasien))->entry[0]->resource->id;
    // $this->db('pasien')->where('no_rkm_medis',$no_rkm_medis)->update('nip',$ihs_patient);
    // }

    $sistole = strtok($inProg['tensi'], '/');
    $diastole = substr($inProg['tensi'], strpos($inProg['tensi'], '/') + 1);

    if ($inProg['tensi'] != '') {
      $diastole = '{
        "fullUrl": "urn:uuid:' . $uuid_diastolik . '",
        "resource": {
            "resourceType": "Observation",
            "status": "final",
            "category": [
                {
                    "coding": [
                        {
                            "system": "http://terminology.hl7.org/CodeSystem/observation-category",
                            "code": "vital-signs",
                            "display": "Vital Signs"
                        }
                    ]
                }
            ],
            "code": {
                "coding": [
                    {
                      "system": "http://loinc.org",
                      "code": "8462-4",
                      "display": "Diastolic blood pressure"
                    }
                ]
            },
            "subject": {
                "reference": "Patient/' . $ihs_patient . '"
            },
            "performer": [
                {
                    "reference": "Practitioner/' . $no_ktp_dokter['practitioner_id'] . '"
                }
            ],
            "encounter": {
                "reference": "urn:uuid:' . $uuid_encounter . '",
                "display": "Pemeriksaan Fisik Diastolik ' . $nama_pasien . ' di ' . $tgl_registrasi . '"
            },
            "effectiveDateTime": "' . $this->convertTimeSatset($inProg['tgl'] . ' ' . $inProg['jam']) . '' . $zonawaktu . '",
            "issued": "' . $this->convertTimeSatset($inProg['tgl'] . ' ' . $inProg['jam']) . '' . $zonawaktu . '",
            "bodySite": {
              "coding": [
                  {
                      "system": "http://snomed.info/sct",
                      "code": "368209003",
                      "display": "Right arm"
                  }
              ]
            },
            "valueQuantity": {
                "value": ' . $diastole . ',
                "unit": "mm[Hg]",
                "system": "http://unitsofmeasure.org",
                "code": "mm[Hg]"
            },
            "interpretation": [
              {
                  "coding": [
                      {
                          "system": "http://terminology.hl7.org/CodeSystem/v3-ObservationInterpretation",
                          "code": "L",
                          "display": "low"
                      }
                  ],
                  "text": "Di bawah nilai referensi"
              }
            ]
        },
        "request": {
            "method": "POST",
            "url": "Observation"
        }
      },';
      $sistole = '{
        "fullUrl": "urn:uuid:' . $sistole . '",
        "resource": {
            "resourceType": "Observation",
            "status": "final",
            "category": [
                {
                    "coding": [
                        {
                            "system": "http://terminology.hl7.org/CodeSystem/observation-category",
                            "code": "vital-signs",
                            "display": "Vital Signs"
                        }
                    ]
                }
            ],
            "code": {
                "coding": [
                    {
                      "system": "http://loinc.org",
                      "code": "8462-4",
                      "display": "Diastolic blood pressure"
                    }
                ]
            },
            "subject": {
                "reference": "Patient/' . $ihs_patient . '"
            },
            "performer": [
                {
                    "reference": "Practitioner/' . $no_ktp_dokter['practitioner_id'] . '"
                }
            ],
            "encounter": {
                "reference": "urn:uuid:' . $uuid_encounter . '",
                "display": "Pemeriksaan Fisik Sistole ' . $nama_pasien . ' di ' . $tgl_registrasi . '"
            },
            "effectiveDateTime": "' . $this->convertTimeSatset($inProg['tgl'] . ' ' . $inProg['jam']) . '' . $zonawaktu . '",
            "issued": "' . $this->convertTimeSatset($inProg['tgl'] . ' ' . $inProg['jam']) . '' . $zonawaktu . '",
            "valueQuantity": {
                "value": ' . $inProg['respirasi'] . ',
                "unit": "breaths/minute",
                "system": "http://unitsofmeasure.org",
                "code": "/min"
            }
        },
        "request": {
            "method": "POST",
            "url": "Observation"
        }
      },';
    }

    $composition_json = '';
    $display_composition = "Kunjungan ' . $nama_pasien . ' di tanggal ' . $tgl_registrasi . '";
    $zonaWaktu_composition = $this->convertTimeSatset($mlite_billing['tgl_billing'] . ' ' . $mlite_billing['jam_billing']) . '' . $zonawaktu;
    $composition = new Composition(
      $uuid_encounter,
      $uuid_composition,
      $ihs_patient,
      $no_ktp_dokter['practitioner_id'],
      $nama_pasien,
      $nama_dokter,
      $no_rawat,
      $this->organizationid,
      $display_composition,
      $zonaWaktu_composition
    );
    $composition_json = $composition->toJson();

    $respiratory_json = '';
    if (!in_array($inProg['respirasi'], ['', '-'])) {
      $zonaWaktu = $this->convertTimeSatset($inProg['tgl'] . ' ' . $inProg['jam']) . '' . $zonawaktu;
      $display_respiratory = "Pemeriksaan Fisik Pernafasan ' . $nama_pasien . ' di ' . $tgl_registrasi . '";
      $respiratory = new Respiratory(
        $uuid_encounter,
        $uuid_respiration,
        $ihs_patient,
        $no_ktp_dokter['practitioner_id'],
        $inProg['respirasi'],
        $zonaWaktu,
        $display_respiratory
      );
      $respiratory_json = $respiratory->toJson();
    }

    $temperatur_json = '';
    if ($inProg['suhu'] != '') {
      $value_temp = 'N';
      $display_temp = 'Normal';
      $text_temp = 'antara';
      if ($inProg['suhu'] > 37) {
        $value_temp = 'H';
        $display_temp = 'High';
        $text_temp = 'atas';
      }
      if ($inProg['suhu'] < 36) {
        $value_temp = 'L';
        $display_temp = 'Low';
        $text_temp = 'bawah';
      }
      $display_encounter_temp = "Pemeriksaan Fisik Suhu ' . $nama_pasien . ' di ' . $tgl_registrasi . '";
      $zonaWaktu = $this->convertTimeSatset($inProg['tgl'] . ' ' . $inProg['jam']) . '' . $zonawaktu;
      $suhu = new Temperature(
        $uuid_encounter,
        $uuid_suhu,
        $ihs_patient,
        $no_ktp_dokter['practitioner_id'],
        str_replace(',', '.', $inProg['suhu']),
        $display_encounter_temp,
        $value_temp,
        $display_temp,
        $text_temp,
        $zonaWaktu
      );
      $temperatur_json = $suhu->toJson();
    }

    $heart_rate_json = '';
    if (!in_array($inProg['nadi'], ['', '-'])) {
      $zonaWaktu = $this->convertTimeSatset($inProg['tgl'] . ' ' . $inProg['jam']) . '' . $zonawaktu;
      $display_nadi = "Pemeriksaan Fisik Nadi ' . $nama_pasien . ' di ' . $tgl_registrasi . '";
      $nadi = new Observation($uuid_encounter, $uuid_nadi, $ihs_patient, $no_ktp_dokter['practitioner_id'], $inProg['nadi'], $zonaWaktu, $display_nadi, 'nadi');
      $heart_rate_json = $nadi->toJsonBundle().',';
    }

    $condition_json = '';
    if ($diagnosa_pasien['kd_penyakit'] != '') {
      $display_condition = $kunjungan . ' ' . $nama_pasien . ' dari tanggal ' . $tgl_registrasi;
      $condition = new Condition(
        $uuid_encounter,
        $uuid_condition,
        $diagnosa_pasien['kd_penyakit'],
        $diagnosa_pasien['nm_penyakit'],
        $ihs_patient,
        $nama_pasien,
        $display_condition
      );
      $condition_json = $condition->toJson();
    }

    $procedure_json = '';
    if ($prosedure_pasien) {
      $display_procedure = "Tindakan pada ' . $nama_pasien . ' di tanggal ' . $tgl_registrasi . '";
      $zonaWaktu = $this->convertTimeSatset($inProg['tgl'] . ' ' . $inProg['jam']) . '' . $zonawaktu;
      $procedure = new Procedure(
        $uuid_encounter,
        $uuid_procedure,
        $kode_prosedure_pasien,
        $prosedure_pasien,
        $ihs_patient,
        $nama_pasien,
        $display_procedure,
        $zonaWaktu,
        $no_ktp_dokter['practitioner_id'],
        $nama_dokter,
        $diagnosa_pasien['kd_penyakit'],
        $diagnosa_pasien['nm_penyakit']
      );
      $procedure_json = $procedure->toJson();
    }

    $careplan_json = '';
    $cek_ranap = $this->db('kamar_inap')->where('no_rawat',$no_rawat)->oneArray();
    if ($cek_ranap) {
      $uuid_careplan = $this->gen_uuid();
      $careplan = new CarePlan($ihs_patient,$uuid_encounter,$uuid_careplan,"Pasien Dirawat Inapkan","Rawat Inap",$no_ktp_dokter['practitioner_id']);
      $careplan_json = $careplan->toJsonBundle().',';
    }

    $medicationforrequest_json = '';
    $medicationrequest_json = '';
    $no = 1;
    $cek_resep = $this->db('resep_dokter')->join('resep_obat', 'resep_obat.no_resep = resep_dokter.no_resep')->where('no_rawat', $no_rawat)->where('status', 'ralan')->toArray();
    foreach ($cek_resep as $value) {
      $cek_obat = $this->db('mlite_satu_sehat_mapping_obat')->where('kode_brng', $value['kode_brng'])->oneArray();
      if ($cek_obat) {
        $uuid_medication = $this->gen_uuid();
        $uuid_medicationrequest = $this->gen_uuid();
        $system_cek = 'http://terminology.hl7.org/CodeSystem/v3-orderableDrugForm';
        if ($cek_obat['satuan_den'] == '385057009' || $cek_obat['satuan_den'] == '421366001') {
          $system_cek = 'http://snomed.info/sct';
        }

        $medication = new Medication(
          $uuid_medication,
          $this->organizationid,
          $no_rawat,
          $cek_obat['kode_kfa'],
          $cek_obat['nama_kfa'],
          $cek_obat['kode_sediaan'],
          $cek_obat['nama_sediaan'],
          $cek_obat['kode_bahan'],
          $cek_obat['nama_bahan'],
          $cek_obat['satuan_num'],
          $cek_obat['numerator'],
          $cek_obat['satuan_den'],
          $system_cek,
          $value['jml'],
          $no . $this->ran_char()
        );
        $medicationforrequest_json .= $medication->toJson();

        $time_authored = $this->convertTimeSatset($value['tgl_peresepan'] . ' ' . $value['jam_peresepan']) . $zonawaktu;
        $medicationrequest = new MedicationRequest(
          $uuid_medication,
          $uuid_medicationrequest,
          $this->organizationid,
          $value['no_resep'],
          $cek_obat['nama_kfa'],
          $ihs_patient,
          $nama_pasien,
          $time_authored,
          $no_ktp_dokter['practitioner_id'],
          $nama_dokter,
          $uuid_condition,
          $uuid_encounter,
          $diagnosa_pasien['nm_penyakit'],
          $value['aturan_pakai'],
          $cek_obat['kode_route'],
          $cek_obat['nama_route'],
          $value['jml'],
          $cek_obat['satuan_den'],
          $system_cek,
          $cek_obat['satuan_den'],
          $no . $this->ran_char()
        );
        $medicationrequest_json .= $medicationrequest->toJson();

        $no++;
      }
    }

    $medicationfordispense_json = '';
    $medicationdispense_json = '';
    $no = 1;
    $cek_detail = $this->db('detail_pemberian_obat')->where('no_rawat', $no_rawat)->toArray();
    $praktisi_apoteker = $this->db('mlite_satu_sehat_mapping_praktisi')->select('practitioner_id', 'kd_dokter')->where('jenis_praktisi', 'Apoteker')->toArray();
    $id_praktisi_apoteker = $praktisi_apoteker[array_rand($praktisi_apoteker)];
    $nama_praktisi_apoteker = $this->core->getPegawaiInfo('nama', $id_praktisi_apoteker['kd_dokter']);
    foreach ($cek_detail as $value) {
      $cek_obat = $this->db('mlite_satu_sehat_mapping_obat')->where('kode_brng', $value['kode_brng'])->oneArray();
      $cek_aturan_pakai = $this->db('aturan_pakai')->where('no_rawat', $no_rawat)->where('kode_brng', $value['kode_brng'])->where('jam', $value['jam'])->oneArray();
      if ($cek_obat && $cek_aturan_pakai) {
        $uuid_medication_for_dispense = $this->gen_uuid();
        $uuid_medication_dispense = $this->gen_uuid();
        $system_cek = 'http://terminology.hl7.org/CodeSystem/v3-orderableDrugForm';
        if ($cek_obat['satuan_den'] == '385057009' || $cek_obat['satuan_den'] == '421366001') {
          $system_cek = 'http://snomed.info/sct';
        }

        $medication_for_dispense = new Medication(
          $uuid_medication_for_dispense,
          $this->organizationid,
          $no_rawat,
          $cek_obat['kode_kfa'],
          $cek_obat['nama_kfa'],
          $cek_obat['kode_sediaan'],
          $cek_obat['nama_sediaan'],
          $cek_obat['kode_bahan'],
          $cek_obat['nama_bahan'],
          $cek_obat['satuan_num'],
          $cek_obat['numerator'],
          $cek_obat['satuan_den'],
          $system_cek,
          $value['jml'],
          $no . $this->ran_char()
        );
        $medicationfordispense_json .= $medication_for_dispense->toJson();

        $time_prepared_handed = $this->convertTimeSatset($value['tgl_perawatan'] . ' ' . $value['jam']) . $zonawaktu;
        $medication_dispense = new MedicationDispense(
          $uuid_medication_dispense,
          $this->organizationid,
          $no_rawat,
          $uuid_medication_for_dispense,
          $cek_obat['nama_kfa'],
          $ihs_patient,
          $nama_pasien,
          $uuid_encounter,
          $id_praktisi_apoteker['practitioner_id'],
          $nama_praktisi_apoteker,
          $mlite_satu_sehat_lokasi['id_lokasi_satusehat'],
          $uuid_medicationrequest,
          $time_prepared_handed,
          $time_prepared_handed,
          $cek_aturan_pakai['aturan'],
          $value['jml'],
          $cek_obat['satuan_den'],
          $system_cek,
          $cek_obat['satuan_den'],
          $no . $this->ran_char()
        );
        $medicationdispense_json .= $medication_dispense->toJson();

        $no++;
      }
    }

    $questionare_json = '';
    $careplan_medication_json = '';
    if ($medicationdispense_json) {
      $uuid_questionare = $this->gen_uuid();
      $questionare = new QuestionareMedication($uuid_questionare, $uuid_encounter, $ihs_patient, $nama_pasien, $id_praktisi_apoteker['practitioner_id'], $nama_praktisi_apoteker);
      $questionare_json = $questionare->toJson();

      $uuid_careplan = $this->gen_uuid();
      $careplan = new CarePlan($ihs_patient,$uuid_encounter,$uuid_careplan,"Pasien Mendapatkan Resep Obat","Resep Obat",$no_ktp_dokter['practitioner_id']);
      $careplan_medication_json = $careplan->toJsonBundle().',';
    }

    $clinical_impression_json_history = '';
    if ($inProg['keluhan'] != '') {
      $clinicalimpression_history = new ClinicalImpression(
        $this->organizationid,
        $uuid_clinical_impression_history,
        $no_rawat,
        $ihs_patient,
        $nama_pasien,
        $uuid_encounter,
        $inProg['keluhan'],"in-progress"
      );
      $clinical_impression_json_history = $clinicalimpression_history->toJsonBundle().',';
    }

    $clinical_impression_json_prognosis = '';
    if ($inProg['penilaian'] != '') {
      $clinicalimpression = new ClinicalImpression(
        $this->organizationid,
        $uuid_clinical_impression_prognosis,
        $no_rawat,
        $ihs_patient,
        $nama_pasien,
        $uuid_encounter,
        $inProg['penilaian'],"completed"
      );
      $clinical_impression_json_prognosis = $clinicalimpression->toJsonBundle().',';
    }

    $service_request_lab_json = [];
    $specimen_json = [];
    $observation_lab_json = [];
    $diagnostic_report_json = [];
    $permintaan_lab = $this->db('permintaan_lab')->join('permintaan_pemeriksaan_lab','permintaan_lab.noorder = permintaan_pemeriksaan_lab.noorder')->where('no_rawat', $no_rawat)->toArray();
    foreach ($permintaan_lab as $value) {
      $check_mapping_lab = $this->db('mlite_satu_sehat_mapping_lab')->where('kd_jenis_prw',$value['kd_jenis_prw'])->oneArray();
      if ($check_mapping_lab && $check_mapping_lab['id_template'] != '' && $check_mapping_lab['jenis_pemeriksaan'] == 'tunggal') {
        $praktisi_lab = $this->db('mlite_satu_sehat_mapping_praktisi')->select('practitioner_id', 'kd_dokter')->where('jenis_praktisi', 'Laboratorium')->toArray();
        $id_praktisi_lab = $praktisi_lab[array_rand($praktisi_lab)];
        $nama_praktisi_lab = $this->core->getPegawaiInfo('nama', $id_praktisi_lab['kd_dokter']);
        $no_ktp_dokter_perujuk = $this->db('mlite_satu_sehat_mapping_praktisi')->select('practitioner_id')->where('kd_dokter', $value['dokter_perujuk'])->oneArray();
        $nama_dokter_perujuk = $this->core->getPegawaiInfo('nama', $value['dokter_perujuk']);
        $nama_tindakan = $this->db('jns_perawatan_lab')->select('nm_perawatan')->where('kd_jenis_prw', $value['kd_jenis_prw'])->oneArray();
        $time_sampled = $this->convertTimeSatset($value['tgl_sample'] . ' ' . $value['jam_sample']) . $zonawaktu;
        $time_result = $this->convertTimeSatset($value['tgl_hasil'] . ' ' . $value['jam_hasil']) . $zonawaktu;

        $uuid_service_request_lab = $this->gen_uuid();
        $uuid_specimen_lab = $this->gen_uuid();
        $uuid_observation_lab = $this->gen_uuid();
        $uuid_diagnostic_report = $this->gen_uuid();
        

        $service_request_lab = new ServiceRequest(
          $uuid_service_request_lab,
          $this->organizationid,
          $no_rawat,
          $ihs_patient,
          $uuid_encounter,
          $no_ktp_dokter_perujuk['practitioner_id'],
          $nama_dokter_perujuk,
          $check_mapping_lab['code_loinc'],
          $check_mapping_lab['display_loinc'],
          $check_mapping_lab['code_kptl'],
          $check_mapping_lab['display_kptl'],
          $nama_tindakan['nm_perawatan']
        );
        $service_request_lab_json[] = $service_request_lab->toJsonBundle().',';

        $specimen_lab = new Specimen($uuid_specimen_lab,$this->organizationid,$ihs_patient,$nama_pasien,$uuid_service_request_lab,$no_rawat,$time_sampled);
        $specimen_json[] = $specimen_lab->toJsonBundle().',';

        $cek_hasil_lab = $this->db('detail_periksa_lab')->where('no_rawat', $no_rawat)->where('kd_jenis_prw',$value['kd_jenis_prw'])->where('tgl_periksa',$value['tgl_hasil'])->where('jam',$value['jam_hasil'])->oneArray();
        $observation_lab = new Observation(
          $uuid_encounter,$uuid_observation_lab,$ihs_patient,$id_praktisi_lab,$cek_hasil_lab['nilai'],$time_result,"","lab",$uuid_specimen_lab,$uuid_service_request_lab,$check_mapping_lab['code_loinc'],
          $check_mapping_lab['display_loinc']
        );
        $observation_lab_json[] = $observation_lab->toJsonBundle().',';

        $diagnostic_report_lab = new DiagnosticReport($uuid_diagnostic_report,$uuid_specimen_lab,$uuid_encounter,$uuid_service_request_lab,$uuid_observation_lab,$id_praktisi_lab,$ihs_patient,$check_mapping_lab['code_loinc'],
        $check_mapping_lab['display_loinc'],$time_result);
        $diagnostic_report_json[] = $diagnostic_report_lab->toJsonBundle().',';
      }
    }
    $service_request_lab_json_decode = implode("\n", $service_request_lab_json);
    $specimen_lab_json_decode = implode("\n", $specimen_json);
    $observation_lab_json_decode = implode("\n", $observation_lab_json);
    $diagnostic_report_json_decode = implode("\n", $diagnostic_report_json);

    $careplan_service_request_lab_json = '';
    if ($service_request_lab_json_decode) {
      $uuid_careplan = $this->gen_uuid();
      $careplan = new CarePlan($ihs_patient,$uuid_encounter,$uuid_careplan,"Pasien Mendapatkan Pemeriksaan Laboratorium","Pemeriksaan Laboratorium" ,$no_ktp_dokter['practitioner_id']);
      $careplan_service_request_lab_json = $careplan->toJsonBundle().',';
    }

    // Bundle dari sini ----------------------------------------------------------------------
    $json_bundle = '{
    "resourceType": "Bundle",
    "type": "transaction",
    "entry": [
        {
            "fullUrl": "urn:uuid:' . $uuid_encounter . '",
            "resource": {
                "resourceType": "Encounter",
                "status": "finished",
                "class": {
                    "system": "http://terminology.hl7.org/CodeSystem/v3-ActCode",
                    "code": "' . $code . '",
                    "display": "' . $display . '"
                },
                "subject": {
                    "reference": "Patient/' . $ihs_patient . '",
                    "display": "' . $nama_pasien . '"
                },
                "participant": [
                    {
                        "type": [
                            {
                                "coding": [
                                    {
                                        "system": "http://terminology.hl7.org/CodeSystem/v3-ParticipationType",
                                        "code": "ATND",
                                        "display": "attender"
                                    }
                                ]
                            }
                        ],
                        "individual": {
                            "reference": "Practitioner/' . $no_ktp_dokter['practitioner_id'] . '",
                            "display": "' . $nama_dokter . '"
                        }
                    }
                ],
                "period": {
                    "start": "' . $this->convertTimeSatset($tgl_registrasi . ' ' . $jam_reg) . '' . $zonawaktu . '",
                    "end": "' . $this->convertTimeSatset($mlite_billing['tgl_billing'] . ' ' . $mlite_billing['jam_billing']) . '' . $zonawaktu . '"
                },
                "location": [
                    {
                        "location": {
                            "reference": "Location/' . $mlite_satu_sehat_lokasi['id_lokasi_satusehat'] . '",
                            "display": "' . $kd_poli . ' ' . $nm_poli . '"
                        }
                    }
                ],
                "diagnosis": [
                    {
                        "condition": {
                            "reference": "urn:uuid:' . $uuid_condition . '",
                            "display": "' . $diagnosa_pasien['nm_penyakit'] . '"
                        },
                        "use": {
                            "coding": [
                                {
                                    "system": "http://terminology.hl7.org/CodeSystem/diagnosis-role",
                                    "code": "DD",
                                    "display": "Discharge diagnosis"
                                }
                            ]
                        },
                        "rank": 1
                    }
                ],
                "statusHistory": [
                    {
                        "status": "arrived",
                        "period": {
                            "start": "' . $this->convertTimeSatset($tgl_registrasi . ' ' . $jam_reg) . '' . $zonawaktu . '",
                            "end": "' . $this->convertTimeSatset($inProg['tgl'] . ' ' . $inProg['jam']) . '' . $zonawaktu . '"
                        }
                    },
                    {
                        "status": "in-progress",
                        "period": {
                            "start": "' . $this->convertTimeSatset($inProg['tgl'] . ' ' . $inProg['jam']) . '' . $zonawaktu . '",
                            "end": "' . $this->convertTimeSatset($mlite_billing['tgl_billing'] . ' ' . $mlite_billing['jam_billing']) . '' . $zonawaktu . '"
                        }
                    },
                    {
                        "status": "finished",
                        "period": {
                            "start": "' . $this->convertTimeSatset($mlite_billing['tgl_billing'] . ' ' . $mlite_billing['jam_billing']) . '' . $zonawaktu . '",
                            "end": "' . $this->convertTimeSatset($mlite_billing['tgl_billing'] . ' ' . $mlite_billing['jam_billing']) . '' . $zonawaktu . '"
                        }
                    }
                ],
                "serviceProvider": {
                    "reference": "Organization/' . $this->organizationid . '"
                },
                "identifier": [
                    {
                        "system": "http://sys-ids.kemkes.go.id/encounter/' . $this->organizationid . '",
                        "value": "' . $no_rawat . '"
                    }
                ]
            },
            "request": {
                "method": "POST",
                "url": "Encounter"
            }
        },' .
      $respiratory_json .
      $temperatur_json .
      $heart_rate_json .
      $clinical_impression_json_history .
      $careplan_json .
      $careplan_service_request_lab_json .
      $service_request_lab_json_decode .
      $specimen_lab_json_decode .
      $observation_lab_json_decode .
      $diagnostic_report_json_decode .
      $condition_json .
      $procedure_json .
      $careplan_medication_json .
      $medicationforrequest_json .
      $medicationrequest_json .
      $questionare_json .
      $medicationfordispense_json .
      $medicationdispense_json .
      $clinical_impression_json_prognosis .
      $composition_json .
      ']
    }';
    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => $this->fhirurl,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_HTTPHEADER => array('Content-Type: application/json', 'Authorization: Bearer ' . json_decode($this->getToken())->access_token),
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS => $json_bundle,
    ));

    $response = curl_exec($curl);
    $id_encounter = '';
    $id_condition = '';
    $id_observation_respiratory = NULL;
    $id_observation_temp = NULL;
    $id_observation_nadi = NULL;
    $id_procedure = NULL;
    $id_composition = NULL;
    $id_medication_for_request = NULL;
    $id_medication_request = NULL;
    $id_medication_dispense = NULL;
    $id_clinical_impression = NULL;
    $id_service_request_lab = NULL;
    $id_specimen_lab = NULL;
    $id_observation_lab = NULL;
    $id_diagnostic_report_lab = NULL;
    $id_careplan = NULL;

    $entry = json_decode($response)->entry;
    $index = '';
    foreach ($entry as $key => $value) {
      $resourceType = $value->response->resourceType;
      $index = $index . ' { ' . $key . '. ' . $resourceType . ' } ';
      if ($resourceType == 'Encounter') {
        $id_encounter = $value->response->resourceID;
      }
      if ($resourceType == 'Condition') {
        $id_condition = $value->response->resourceID;
      }
      if ($resourceType == 'Observation') {
        if (!empty($respiratory_json && $temperatur_json && $heart_rate_json)) {
          if ($key == '1') {
            $id_observation_respiratory = $value->response->resourceID;
          }
          if ($key == '2') {
            $id_observation_temp = $value->response->resourceID;
          }
          if ($key == '3') {
            $id_observation_nadi = $value->response->resourceID;
          }
        }
        if (!empty($respiratory_json && $temperatur_json) && empty($heart_rate_json)) {
          if ($key == '1') {
            $id_observation_respiratory = $value->response->resourceID;
          }
          if ($key == '2') {
            $id_observation_temp = $value->response->resourceID;
          }
        }
        if (!empty($respiratory_json && $heart_rate_json) && empty($temperatur_json)) {
          if ($key == '1') {
            $id_observation_respiratory = $value->response->resourceID;
          }
          if ($key == '2') {
            $id_observation_nadi = $value->response->resourceID;
          }
        }
        if (!empty($temperatur_json && $heart_rate_json) && empty($respiratory_json)) {
          if ($key == '1') {
            $id_observation_temp = $value->response->resourceID;
          }
          if ($key == '2') {
            $id_observation_nadi = $value->response->resourceID;
          }
        }
        if (!empty($respiratory_json) && empty($temperatur_json && $heart_rate_json)) {
          if ($key == '1') {
            $id_observation_respiratory = $value->response->resourceID;
          }
        }
        if (!empty($temperatur_json) && empty($respiratory_json && $heart_rate_json)) {
          if ($key == '1') {
            $id_observation_temp = $value->response->resourceID;
          }
        }
        if (!empty($heart_rate_json) && empty($respiratory_json && $temperatur_json)) {
          if ($key == '1') {
            $id_observation_nadi = $value->response->resourceID;
          }
        }
      }
      if ($resourceType == 'Procedure') {
        $id_procedure = $value->response->resourceID;
      }
      if ($resourceType == 'Composition') {
        $id_composition = $value->response->resourceID;
      }
      if ($resourceType == 'Medication') {
        $id_medication_for_request = $value->response->resourceID;
      }
      if ($resourceType == 'MedicationRequest') {
        $id_medication_request = $value->response->resourceID;
      }
      if ($resourceType == 'MedicationDispense') {
        $id_medication_dispense = $value->response->resourceID;
      }
      if ($resourceType == 'ClinicalImpression') {
        $id_clinical_impression = $value->response->resourceID;
      }
      if ($resourceType == 'ServiceRequest') {
        $id_service_request_lab = $value->response->resourceID;
      }
      if ($resourceType == 'Specimen') {
        $id_specimen_lab = $value->response->resourceID;
      }
      if ($resourceType == 'Observation' && $observation_lab_json_decode) {
        $id_observation_lab = $value->response->resourceID;
      }
      if ($resourceType == 'DiagnosticReport') {
        $id_diagnostic_report_lab = $value->response->resourceID;
      }
      if ($resourceType == 'CarePlan') {
        $id_careplan = $value->response->resourceID;
      }
    }

    $pesan = 'Gagal mengirim pasien dengan No Rawat : ' . $no_rawat . ' ke platform Satu Sehat!!';
    if ($id_encounter) {
      $this->db('mlite_satu_sehat_response')->save([
        'no_rawat' => $no_rawat,
        'id_encounter' => $id_encounter,
        'id_condition' => $id_condition,
        'id_observation_ttvrespirasi' => $id_observation_respiratory,
        'id_observation_ttvsuhu' => $id_observation_temp,
        'id_observation_ttvnadi' => $id_observation_nadi,
        'id_procedure' => $id_procedure,
        'id_composition' => $id_composition,
        'id_medication_request' => $id_medication_request,
        'id_medication_dispense' => $id_medication_dispense,
        'id_clinical_impression' => $id_clinical_impression,
        'id_lab_pk_request' => $id_service_request_lab,
        'id_lab_pk_specimen' => $id_specimen_lab,
        'id_lab_pk_observation' => $id_observation_lab,
        'id_lab_pk_diagnostic' => $id_diagnostic_report_lab,
        'id_careplan' => $id_careplan,
      ]);
      $pesan = 'Sukses mengirim pasien dengan No Rawat : ' . $no_rawat . ' ' . $index . ' ke platform Satu Sehat!! ';
    }

    curl_close($curl);
    
    if ($param == "all") {
      return json_decode($response);
    } else {
      echo $this->draw('encounter.html', ['pesan' => $pesan, 'response' => $response]);
      print_r($json_bundle);
      exit();
    }
    // echo $response;

    // DEBUG ******
    // print_r($response);
    // $response = json_decode($json_bundle);
  }

  public function getCondition($no_rawat)
  {

    $zonawaktu = '+07:00';
    if ($this->settings->get('satu_sehat.zonawaktu') == 'WITA') {
      $zonawaktu = '+08:00';
    }
    if ($this->settings->get('satu_sehat.zonawaktu') == 'WIT') {
      $zonawaktu = '+09:00';
    }


    $no_rawat = revertNoRawat($no_rawat);
    $kd_poli = $this->core->getRegPeriksaInfo('kd_poli', $no_rawat);
    $nm_poli = $this->core->getPoliklinikInfo('nm_poli', $kd_poli);
    $kd_dokter = $this->core->getRegPeriksaInfo('kd_dokter', $no_rawat);
    $no_ktp_dokter = $this->core->getPegawaiInfo('no_ktp', $kd_dokter);
    $nama_dokter = $this->core->getPegawaiInfo('nama', $kd_dokter);
    $no_rkm_medis = $this->core->getRegPeriksaInfo('no_rkm_medis', $no_rawat);
    $no_ktp_pasien = $this->core->getPasienInfo('no_ktp', $no_rkm_medis);
    $nama_pasien = $this->core->getPasienInfo('nm_pasien', $no_rkm_medis);
    $status_lanjut = $this->core->getRegPeriksaInfo('status_lanjut', $no_rawat);
    $tgl_registrasi = $this->core->getRegPeriksaInfo('tgl_registrasi', $no_rawat);
    $jam_reg = $this->core->getRegPeriksaInfo('jam_reg', $no_rawat);
    $mlite_billing = $this->db('mlite_billing')->where('no_rawat', $no_rawat)->oneArray();
    $diagnosa_pasien = $this->db('diagnosa_pasien')
      ->join('penyakit', 'penyakit.kd_penyakit=diagnosa_pasien.kd_penyakit')
      ->where('no_rawat', $no_rawat)
      ->where('diagnosa_pasien.status', $status_lanjut)
      ->where('prioritas', '1')
      ->oneArray();

    $mlite_satu_sehat_response = $this->db('mlite_satu_sehat_response')->where('no_rawat', $no_rawat)->oneArray();

    $kunjungan = 'Kunjungan';
    if ($status_lanjut == 'Ranap') {
      $kunjungan = 'Perawatan';
    }

    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => $this->fhirurl . '/Condition',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_HTTPHEADER => array('Content-Type: application/json', 'Authorization: Bearer ' . json_decode($this->getToken())->access_token),
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS => '{
       "resourceType": "Condition",
       "clinicalStatus": {
          "coding": [
             {
                "system": "http://terminology.hl7.org/CodeSystem/condition-clinical",
                "code": "active",
                "display": "Active"
             }
          ]
       },
       "category": [
          {
             "coding": [
                {
                   "system": "http://terminology.hl7.org/CodeSystem/condition-category",
                   "code": "encounter-diagnosis",
                   "display": "Encounter Diagnosis"
                }
             ]
          }
       ],
       "code": {
          "coding": [
             {
                "system": "http://hl7.org/fhir/sid/icd-10",
                "code": "' . $diagnosa_pasien['kd_penyakit'] . '",
                "display": "' . $diagnosa_pasien['nm_penyakit'] . '"
             }
          ]
       },
       "subject": {
          "reference": "Patient/' . json_decode($this->getPatient($no_ktp_pasien))->entry[0]->resource->id . '",
          "display": "' . $nama_pasien . '"
       },
       "encounter": {
          "reference": "Encounter/' . $mlite_satu_sehat_response['id_encounter'] . '",
          "display": "' . $kunjungan . ' ' . $nama_pasien . ' dari tanggal ' . $tgl_registrasi . '"
       }
    }',
    ));

    $response = curl_exec($curl);


    $id_condition = json_decode($response)->id;
    $pesan = 'Gagal mengirim condition platform Satu Sehat!!';
    if ($id_condition) {
      $mlite_satu_sehat_response = $this->db('mlite_satu_sehat_response')->where('no_rawat', $no_rawat)->oneArray();
      if ($mlite_satu_sehat_response) {
        $this->db('mlite_satu_sehat_response')
          ->where('no_rawat', $no_rawat)
          ->save([
            'no_rawat' => $no_rawat,
            'id_condition' => $id_condition
          ]);
      } else {
        $this->db('mlite_satu_sehat_response')
          ->save([
            'no_rawat' => $no_rawat,
            'id_condition' => $id_condition
          ]);
      }
      $pesan = 'Sukses mengirim condition platform Satu Sehat!!';
    }

    curl_close($curl);
    // echo $response;
    echo $this->draw('condition.html', ['pesan' => $pesan, 'response' => $response]);
    exit();
  }

  public function getObservation($no_rawat, $ttv)
  {

    $zonawaktu = '+07:00';
    if ($this->settings->get('satu_sehat.zonawaktu') == 'WITA') {
      $zonawaktu = '+08:00';
    }
    if ($this->settings->get('satu_sehat.zonawaktu') == 'WIT') {
      $zonawaktu = '+09:00';
    }


    $no_rawat = revertNoRawat($no_rawat);
    $kd_poli = $this->core->getRegPeriksaInfo('kd_poli', $no_rawat);
    $nm_poli = $this->core->getPoliklinikInfo('nm_poli', $kd_poli);
    $kd_dokter = $this->core->getRegPeriksaInfo('kd_dokter', $no_rawat);
    $no_ktp_dokter = $this->core->getPegawaiInfo('no_ktp', $kd_dokter);
    $nama_dokter = $this->core->getPegawaiInfo('nama', $kd_dokter);
    $no_rkm_medis = $this->core->getRegPeriksaInfo('no_rkm_medis', $no_rawat);
    $no_ktp_pasien = $this->core->getPasienInfo('no_ktp', $no_rkm_medis);
    $nama_pasien = $this->core->getPasienInfo('nm_pasien', $no_rkm_medis);
    $status_lanjut = $this->core->getRegPeriksaInfo('status_lanjut', $no_rawat);
    $tgl_registrasi = $this->core->getRegPeriksaInfo('tgl_registrasi', $no_rawat);
    $jam_reg = $this->core->getRegPeriksaInfo('jam_reg', $no_rawat);
    $mlite_billing = $this->db('mlite_billing')->where('no_rawat', $no_rawat)->oneArray();

    $mlite_satu_sehat_response = $this->db('mlite_satu_sehat_response')->where('no_rawat', $no_rawat)->oneArray();

    $pemeriksaan = $this->db('pemeriksaan_ralan')
      ->where('no_rawat', $no_rawat)
      ->limit(1)
      ->desc('tgl_perawatan')
      ->oneArray();

    if ($status_lanjut == 'Ranap') {
      $pemeriksaan = $this->db('pemeriksaan_ranap')
        ->where('no_rawat', $no_rawat)
        ->limit(1)
        ->desc('tgl_perawatan')
        ->oneArray();
    }

    $ttv_hl7_code = '';
    $ttv_hl7_display = '';
    $ttv_loinc_code = '';
    $ttv_loinc_display = '';
    $ttv_unitsofmeasure_value = '';
    $ttv_unitsofmeasure_unit = '';
    $ttv_unitsofmeasure_code = '';

    if ($ttv == 'nadi') {
      $ttv_hl7_code = 'vital-signs';
      $ttv_hl7_display = 'Vital Signs';
      $ttv_loinc_code = '8867-4';
      $ttv_loinc_display = 'Heart rate';
      $ttv_unitsofmeasure_value = $pemeriksaan['nadi'];
      $ttv_unitsofmeasure_unit = 'beats/minute';
      $ttv_unitsofmeasure_code = '/min';
    }

    if ($ttv == 'respirasi') {
      $ttv_hl7_code = 'vital-signs';
      $ttv_hl7_display = 'Vital Signs';
      $ttv_loinc_code = '9279-1';
      $ttv_loinc_display = 'Respiratory rate';
      $ttv_unitsofmeasure_value = $pemeriksaan['respirasi'];
      $ttv_unitsofmeasure_unit = 'breaths/minute';
      $ttv_unitsofmeasure_code = '/min';
    }

    if ($ttv == 'suhu') {
      $ttv_hl7_code = 'vital-signs';
      $ttv_hl7_display = 'Vital Signs';
      $ttv_loinc_code = '8310-5';
      $ttv_loinc_display = 'Body temperature';
      $ttv_unitsofmeasure_value = $pemeriksaan['suhu_tubuh'];
      $ttv_unitsofmeasure_unit = 'degree Celsius';
      $ttv_unitsofmeasure_code = 'Cel';
    }

    if ($ttv == 'spo2') {
      $ttv_hl7_code = 'vital-signs';
      $ttv_hl7_display = 'Vital Signs';
      $ttv_loinc_code = '59408-5';
      $ttv_loinc_display = 'Oxygen saturation';
      $ttv_unitsofmeasure_value = $pemeriksaan['spo2'];
      $ttv_unitsofmeasure_unit = 'percent saturation';
      $ttv_unitsofmeasure_code = '%';
    }

    if ($ttv == 'gcs') {
      $ttv_hl7_code = 'vital-signs';
      $ttv_hl7_display = 'Vital Signs';
      $ttv_loinc_code = '9269-2';
      $ttv_loinc_display = 'Glasgow coma score total';
      $ttv_unitsofmeasure_value = $pemeriksaan['gcs'];
      $ttv_unitsofmeasure_code = '{score}';
    }

    if ($ttv == 'tinggi') {
      $ttv_hl7_code = 'vital-signs';
      $ttv_hl7_display = 'Vital Signs';
      $ttv_loinc_code = '8302-2';
      $ttv_loinc_display = 'Body height';
      $ttv_unitsofmeasure_value = $pemeriksaan['tinggi'];
      $ttv_unitsofmeasure_unit = 'centimeter';
      $ttv_unitsofmeasure_code = 'cm';
    }

    if ($ttv == 'berat') {
      $ttv_hl7_code = 'vital-signs';
      $ttv_hl7_display = 'Vital Signs';
      $ttv_loinc_code = '29463-7';
      $ttv_loinc_display = 'Body weight';
      $ttv_unitsofmeasure_value = $pemeriksaan['berat'];
      $ttv_unitsofmeasure_unit = 'kilogram';
      $ttv_unitsofmeasure_code = 'kg';
    }

    if ($ttv == 'perut') {
      $ttv_hl7_code = 'vital-signs';
      $ttv_hl7_display = 'Vital Signs';
      $ttv_loinc_code = '8280-0';
      $ttv_loinc_display = 'Waist Circumference at umbilicus by Tape measure';
      $ttv_unitsofmeasure_value = $pemeriksaan['berat'];
      $ttv_unitsofmeasure_unit = 'centimeter';
      $ttv_unitsofmeasure_code = 'cm';
    }

    if ($ttv == 'tensi') {
      $ttv_hl7_code = 'vital-signs';
      $ttv_hl7_display = 'Vital Signs';
      $ttv_loinc_code = '35094-2';
      $ttv_loinc_display = 'Blood pressure panel';
      $sistole = strtok($pemeriksaan['tensi'], '/');
      $diastole = substr($pemeriksaan['tensi'], strpos($pemeriksaan['tensi'], '/') + 1);
      $ttv_unitsofmeasure_unit = 'mmHg';
      $ttv_unitsofmeasure_code = 'mm[Hg]';
    }

    if ($ttv == 'kesadaran') {
      $ttv_hl7_code = 'exam';
      $ttv_hl7_display = 'Exam';
      $ttv_unitsofmeasure_value = 'Alert';
      if ($pemeriksaan['kesadaran'] == 'Somnolence') {
        $ttv_unitsofmeasure_value = 'Voice';
      }
      if ($pemeriksaan['kesadaran'] == 'Sopor') {
        $ttv_unitsofmeasure_value = 'Pain';
      }
    }

    $curl = curl_init();

    if ($ttv == 'kesadaran') {
      curl_setopt_array($curl, array(
        CURLOPT_URL => $this->fhirurl . '/Observation',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_HTTPHEADER => array('Content-Type: application/json', 'Authorization: Bearer ' . json_decode($this->getToken())->access_token),
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => '{
          "resourceType": "Observation",
          "status": "final",
          "category": [
              {
                  "coding": [
                      {
                          "system": "http://terminology.hl7.org/CodeSystem/observation-category",
                          "code": "' . $ttv_hl7_code . '",
                          "display": "' . $ttv_hl7_display . '"
                      }
                  ]
              }
          ],
          "code": {
              "coding": [
                  {
                      "system": "http://snomed.info/sct",
                      "code": "1104441000000107",
                      "display": "ACVPU (Alert Confusion Voice Pain Unresponsive) scale score"
                  }
              ]
          },
          "subject": {
              "reference": "Patient/' . json_decode($this->getPatient($no_ktp_pasien))->entry[0]->resource->id . '"
          },
          "performer": [
              {
                  "reference": "Practitioner/' . json_decode($this->getPractitioner($no_ktp_dokter))->entry[0]->resource->id . '"
              }
          ],
          "encounter": {
              "reference": "Encounter/' . $mlite_satu_sehat_response['id_encounter'] . '",
              "display": "Pemeriksaan fisik ' . $ttv . ' ' . $nama_pasien . ' tanggal ' . $tgl_registrasi . '"
          },
          "effectiveDateTime": "' . $pemeriksaan['tgl_perawatan'] . 'T' . $pemeriksaan['jam_rawat'] . '' . $zonawaktu . '",
          "issued": "' . $pemeriksaan['tgl_perawatan'] . 'T' . $pemeriksaan['jam_rawat'] . '' . $zonawaktu . '",
          "valueCodeableConcept": {
              "text": "' . $ttv_unitsofmeasure_value . '"
          }
        }',
      ));
    } elseif ($ttv == 'tensi') {
      curl_setopt_array($curl, array(
        CURLOPT_URL => $this->fhirurl . '/Observation',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_HTTPHEADER => array('Content-Type: application/json', 'Authorization: Bearer ' . json_decode($this->getToken())->access_token),
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => '{
          "resourceType": "Observation",
          "status": "final",
          "category": [
              {
                  "coding": [
                      {
                          "system": "http://terminology.hl7.org/CodeSystem/observation-category",
                          "code": "' . $ttv_hl7_code . '",
                          "display": "' . $ttv_hl7_display . '"
                      }
                  ]
              }
          ],
          "code": {
            "coding": [
                {
                    "system": "http://loinc.org",
                    "code": "' . $ttv_loinc_code . '",
                    "display": "' . $ttv_loinc_display . '"
                }
            ],
            "text": "Blood pressure systolic & diastolic"
          },
          "subject": {
              "reference": "Patient/' . json_decode($this->getPatient($no_ktp_pasien))->entry[0]->resource->id . '"
          },
          "performer": [
              {
                  "reference": "Practitioner/' . json_decode($this->getPractitioner($no_ktp_dokter))->entry[0]->resource->id . '"
              }
          ],
          "encounter": {
              "reference": "Encounter/' . $mlite_satu_sehat_response['id_encounter'] . '",
              "display": "Pemeriksaan fisik ' . $ttv . ' ' . $nama_pasien . ' tanggal ' . $tgl_registrasi . '"
          },
          "effectiveDateTime": "' . $pemeriksaan['tgl_perawatan'] . 'T' . $pemeriksaan['jam_rawat'] . '' . $zonawaktu . '",
          "issued": "' . $pemeriksaan['tgl_perawatan'] . 'T' . $pemeriksaan['jam_rawat'] . '' . $zonawaktu . '",
          "component": [
            {
              "code": {
                "coding": [
                    {
                        "system": "http://loinc.org",
                        "code": "8480-6",
                        "display": "Systolic blood pressure"
                    }
                ]
              },
              "valueQuantity": {
                "value": ' . intval($sistole) . ',
                "unit": "' . $ttv_unitsofmeasure_unit . '",
                "system": "http://unitsofmeasure.org",
                "code": "' . $ttv_unitsofmeasure_code . '"
              }  
            }, 
            {
              "code": {
                "coding": [
                    {
                        "system": "http://loinc.org",
                        "code": "8462-4",
                        "display": "Diastolic blood pressure"
                    }
                ]
              },
              "valueQuantity": {
                "value": ' . intval($diastole) . ',
                "unit": "' . $ttv_unitsofmeasure_unit . '",
                "system": "http://unitsofmeasure.org",
                "code": "' . $ttv_unitsofmeasure_code . '"
              } 
            }
          ]
        }',
      ));
    } else {
      curl_setopt_array($curl, array(
        CURLOPT_URL => $this->fhirurl . '/Observation',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_HTTPHEADER => array('Content-Type: application/json', 'Authorization: Bearer ' . json_decode($this->getToken())->access_token),
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => '{
          "resourceType": "Observation",
          "status": "final",
          "category": [
              {
                  "coding": [
                      {
                          "system": "http://terminology.hl7.org/CodeSystem/observation-category",
                          "code": "' . $ttv_hl7_code . '",
                          "display": "' . $ttv_hl7_display . '"
                      }
                  ]
              }
          ],
          "code": {
              "coding": [
                  {
                      "system": "http://loinc.org",
                      "code": "' . $ttv_loinc_code . '",
                      "display": "' . $ttv_loinc_display . '"
                  }
              ]
          },
          "subject": {
              "reference": "Patient/' . json_decode($this->getPatient($no_ktp_pasien))->entry[0]->resource->id . '"
          },
          "performer": [
              {
                  "reference": "Practitioner/' . json_decode($this->getPractitioner($no_ktp_dokter))->entry[0]->resource->id . '"
              }
          ],
          "encounter": {
              "reference": "Encounter/' . $mlite_satu_sehat_response['id_encounter'] . '",
              "display": "Pemeriksaan fisik ' . $ttv . ' ' . $nama_pasien . ' tanggal ' . $tgl_registrasi . '"
          },
          "effectiveDateTime": "' . $pemeriksaan['tgl_perawatan'] . 'T' . $pemeriksaan['jam_rawat'] . '' . $zonawaktu . '",
          "issued": "' . $pemeriksaan['tgl_perawatan'] . 'T' . $pemeriksaan['jam_rawat'] . '' . $zonawaktu . '",
          "valueQuantity": {
              "value": ' . intval($ttv_unitsofmeasure_value) . ',
              "unit": "' . $ttv_unitsofmeasure_unit . '",
              "system": "http://unitsofmeasure.org",
              "code": "' . $ttv_unitsofmeasure_code . '"
          }
        }',
      ));
    }

    $response = curl_exec($curl);


    $id_observation = json_decode($response)->id;
    $pesan = 'Gagal mengirim observation ttv ' . $ttv . ' ke platform Satu Sehat!!';
    if ($id_observation) {
      $mlite_satu_sehat_response = $this->db('mlite_satu_sehat_response')->where('no_rawat', $no_rawat)->oneArray();
      if ($mlite_satu_sehat_response) {
        $this->db('mlite_satu_sehat_response')
          ->where('no_rawat', $no_rawat)
          ->save([
            'no_rawat' => $no_rawat,
            'id_observation_ttv' . $ttv . '' => $id_observation
          ]);
        $pesan = 'Sukses mengirim observation ttv ' . $ttv . ' ke platform Satu Sehat!!';
      }
    }

    curl_close($curl);
    // echo $response;
    echo $this->draw('observation.html', ['pesan' => $pesan, 'response' => $response]);
    exit();
  }

  public function getMappingLab()
  {
    $this->_addHeaderFiles();
    $json = file_get_contents(__DIR__ . '/data_tab3.json');
    $data = json_decode($json, true); 
    $search = "Permintaan";
    $data_json = [];
    $keyMap = [
        "Nama Pemeriksaan" => "Nama_Pemeriksaan",
        "Kategori/Kelompok Pemeriksaan" => "Kategori_Kelompok_Pemeriksaan",
        "Tipe hasil pemeriksaan" => "Tipe_hasil_pemeriksaan",
        "Metode Analisis" => "Metode_Analisis",
        "Permintaan/Hasil" => "Permintaan_Hasil",
        "Unit of Measure" => "Unit_of_Measure",
        "Code System" => "Code_System",
    ];
    // $filtered = array_filter($data, function($item) use ($search) {
    foreach ($data as $value) {
      # code...
      $renamedItem = [];
      // if(stripos($value['Permintaan/Hasil'], $search) !== false) {
        foreach ($value as $key => $item) {
          $newKey = $keyMap[$key] ?? $key;
          $renamedItem[$newKey] = $item;
          # code...
        }
        $data_json[] = $renamedItem;
      // }
    }
    // });
    $mapping_lab = $this->db('mlite_satu_sehat_mapping_lab')
      ->select([
        'kd_jenis_prw' => 'mlite_satu_sehat_mapping_lab.kd_jenis_prw',
        'id_template'=>'mlite_satu_sehat_mapping_lab.id_template',
        'Pemeriksaan'=>'template_laboratorium.Pemeriksaan',
        'code_loinc'=>'mlite_satu_sehat_mapping_lab.code_loinc',
        'display_loinc'=>'mlite_satu_sehat_mapping_lab.display_loinc',
        'unit_of_measure'=>'mlite_satu_sehat_mapping_lab.unit_of_measure',
      ])
      ->leftJoin('template_laboratorium', 'template_laboratorium.id_template = mlite_satu_sehat_mapping_lab.id_template')
      ->toArray();
    // $filtered = json_decode($filtered,true);
    $template_laboratorium = $this->db('template_laboratorium')->select(['id_template'=>'template_laboratorium.id_template','kd_jenis_prw' => 'template_laboratorium.kd_jenis_prw','Pemeriksaan'=>'template_laboratorium.Pemeriksaan','nm_perawatan'=>'jns_perawatan_lab.nm_perawatan'])->leftJoin('jns_perawatan_lab','jns_perawatan_lab.kd_jenis_prw = template_laboratorium.kd_jenis_prw')->where('jns_perawatan_lab.status','1')->toArray();
    return $this->draw('mapping.lab.html', ['mapping_lab_satu_sehat' => $mapping_lab, 'template_laboratorium' => $template_laboratorium , 'filtered' => $data_json]);
    // echo json_encode($data_json);
    // exit();
  }

  public function getMappingRad()
  {
    $this->_addHeaderFiles();
    $mapping_rad = $this->db('mlite_satu_sehat_mapping_rad')
      ->join('jns_perawatan_radiologi', 'jns_perawatan_radiologi.kd_jenis_prw = mlite_satu_sehat_mapping_rad.kd_jenis_prw')
      ->toArray();
    $jns_perawatan_radiologi = $this->db('jns_perawatan_radiologi')->toArray();
    return $this->draw('mapping.rad.html', ['mapping_rad_satu_sehat' => $mapping_rad, 'jns_perawatan_radiologi' => $jns_perawatan_radiologi]);
  }

  public function postSaveRad()
  {
    if (isset($_POST['simpan'])) {
      $query = $this->db('mlite_satu_sehat_mapping_rad')->save(
        [
          'kd_jenis_prw' => $_POST['kd_jenis_prw'],
          'code' => $_POST['code'],
          'code_system' => $_POST['code_system'],
          'display' => $_POST['display'],
          'sample_code' => $_POST['sample_code'],
          'sample_system' => $_POST['sample_system'],
          'sample_display' => $_POST['sample_display']
        ]
      );

      if ($query) {
        $this->notify('success', 'Mapping radiologi telah disimpan');
      } else {
        $this->notify('danger', 'Mapping radiologi gagal disimpan');
      }
    }

    if (isset($_POST['hapus'])) {
      $query = $this->db('mlite_satu_sehat_mapping_rad')
        ->where('kd_jenis_prw', $_POST['kd_jenis_prw'])
        ->delete();
      if ($query) {
        $this->notify('success', 'Mapping radiologi telah dihapus');
      } else {
        $this->notify('danger', 'Mapping radiologi gagal dihapus');
      }
    }

    redirect(url([ADMIN, 'satu_sehat', 'mappingrad']));
  }

  public function postSaveLab()
  {
    if (isset($_POST['simpan'])) {
      $json = file_get_contents(__DIR__ . '/data_tab3.json');
      $data = json_decode($json, true); 
      $search = $_POST['id_template_json'];
      $data_json = [];
      $keyMap = [
          "Nama Pemeriksaan" => "Nama_Pemeriksaan",
          "Kategori/Kelompok Pemeriksaan" => "Kategori_Kelompok_Pemeriksaan",
          "Tipe hasil pemeriksaan" => "Tipe_hasil_pemeriksaan",
          "Metode Analisis" => "Metode_Analisis",
          "Permintaan/Hasil" => "Permintaan_Hasil",
          "Unit of Measure" => "Unit_of_Measure",
          "Code System" => "Code_System",
      ];
      foreach ($data as $value) {
        $renamedItem = [];
        if($value['No'] == $search) {
          foreach ($value as $key => $item) {
            $newKey = $keyMap[$key] ?? $key;
            $renamedItem[$newKey] = $item;
          }
          $data_json[] = $renamedItem;
        }
      }
      $data_json = json_encode($data_json);
      $return_json = json_decode($data_json, true);
      $parts = explode(":", $_POST['id_template']);
      $id_template = trim($parts[0]);
      $kd_jenis_prw = trim($parts[1]);
      
      if ($_POST['is_template'] == '') {
        $id_template = "";
      }
      
      // echo $_POST['id_template'];
      // echo '<br>';
      // echo $_POST['is_template'];
      // exit();
      $query = $this->db('mlite_satu_sehat_mapping_lab')->save(
        [
          'id_template' => $id_template,
          'kd_jenis_prw' => $kd_jenis_prw,
          'jenis_pemeriksaan' => $_POST['type'],
          'code_loinc' => $return_json[0]['Code'],
          'display_loinc' => $return_json[0]['Display'],
          'code_kptl' => '',
          'display_kptl' => '',
          'unit_of_measure' => $return_json[0]['Unit_of_Measure']
        ]
      );

      if ($query) {
        $this->notify('success', 'Mapping laboratorium telah disimpan');
      } else {
        $this->notify('danger', 'Mapping laboratorium gagal disimpan');
      }
    }

    if (isset($_POST['hapus'])) {
      $query = $this->db('mlite_satu_sehat_mapping_lab')
        ->where('id_template', $_POST['id_template'])
        ->delete();
      if ($query) {
        $this->notify('success', 'Mapping laboratorium telah dihapus');
      } else {
        $this->notify('danger', 'Mapping laboratorium gagal dihapus');
      }
    }

    redirect(url([ADMIN, 'satu_sehat', 'mappinglab']));
  }

  public function postSaveDepartemen()
  {
    if (isset($_POST['simpan'])) {

      $get_id_organisasi_satusehat = json_decode($this->getOrganization($_POST['dep_id']));
      // $id_organisasi_satusehat = $get_id_organisasi_satusehat->id;

      // echo json_encode($get_id_organisasi_satusehat, true);

      if ($get_id_organisasi_satusehat->issue[0]->code == 'duplicate') {

        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => $this->fhirurl . '/Organization?partof=' . $this->organizationid,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_HTTPHEADER => array('Content-Type: application/json', 'Authorization: Bearer ' . json_decode($this->getToken())->access_token),
          CURLOPT_CUSTOMREQUEST => 'GET',
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $get_id_organisasi_satusehat = json_decode($response);
        $get_id_organisasi_satusehat = json_encode($get_id_organisasi_satusehat, true);
        // echo $get_id_organisasi_satusehat;

        foreach (json_decode($get_id_organisasi_satusehat)->entry as $item) {
          if ($item->resource->identifier[0]->value == $_POST['dep_id']) {
            $id_organisasi_satusehat = $item->resource->id;
            echo $id_organisasi_satusehat;
          }
        }
      }

      if ($id_organisasi_satusehat != '') {
        $query = $this->db('mlite_satu_sehat_departemen')->save(
          [
            'dep_id' => $_POST['dep_id'],
            'id_organisasi_satusehat' => $id_organisasi_satusehat
          ]
        );
        if ($query) {
          $this->notify('success', 'Mapping departemen telah disimpan');
        } else {
          $this->notify('danger', 'Mapping departemen gagal disimpan');
        }
      }
    }

    if (isset($_POST['update'])) {
      $mlite_satu_sehat_departemen = $this->db('mlite_satu_sehat_departemen')->where('id_organisasi_satusehat', $_POST['id_organisasi_satusehat'])->oneArray();
      $query = $this->db('mlite_satu_sehat_departemen')
        ->where('id_organisasi_satusehat', $mlite_satu_sehat_departemen['id_organisasi_satusehat'])
        ->save(
          [
            'dep_id' => $_POST['dep_id']
          ]
        );
      if ($query) {
        $this->notify('success', 'Mapping departemen telah disimpan');
      }
    }

    if (isset($_POST['hapus'])) {
      $query = $this->db('mlite_satu_sehat_departemen')
        ->where('id_organisasi_satusehat', $_POST['id_organisasi_satusehat'])
        ->delete();
      if ($query) {
        $this->notify('success', 'Mapping departemen telah dihapus');
      }
    }

    redirect(url([ADMIN, 'satu_sehat', 'departemen']));
  }

  public function getProcedure($no_rawat)
  {

    $zonawaktu = '+07:00';
    if ($this->settings->get('satu_sehat.zonawaktu') == 'WITA') {
      $zonawaktu = '+08:00';
    }
    if ($this->settings->get('satu_sehat.zonawaktu') == 'WIT') {
      $zonawaktu = '+09:00';
    }


    $no_rawat = revertNoRawat($no_rawat);
    $kd_poli = $this->core->getRegPeriksaInfo('kd_poli', $no_rawat);
    $nm_poli = $this->core->getPoliklinikInfo('nm_poli', $kd_poli);
    $kd_dokter = $this->core->getRegPeriksaInfo('kd_dokter', $no_rawat);
    $no_ktp_dokter = $this->core->getPegawaiInfo('no_ktp', $kd_dokter);
    $nama_dokter = $this->core->getPegawaiInfo('nama', $kd_dokter);
    $no_rkm_medis = $this->core->getRegPeriksaInfo('no_rkm_medis', $no_rawat);
    $no_ktp_pasien = $this->core->getPasienInfo('no_ktp', $no_rkm_medis);
    $nama_pasien = $this->core->getPasienInfo('nm_pasien', $no_rkm_medis);
    $status_lanjut = $this->core->getRegPeriksaInfo('status_lanjut', $no_rawat);
    $tgl_registrasi = $this->core->getRegPeriksaInfo('tgl_registrasi', $no_rawat);
    $jam_reg = $this->core->getRegPeriksaInfo('jam_reg', $no_rawat);
    $mlite_billing = $this->db('mlite_billing')->where('no_rawat', $no_rawat)->oneArray();
    $prosedur_pasien = $this->db('prosedur_pasien')
      ->join('icd9', 'icd9.kode=prosedur_pasien.kode')
      ->where('no_rawat', $no_rawat)
      ->where('prosedur_pasien.status', $status_lanjut)
      ->where('prioritas', '1')
      ->oneArray();

    $kode_icd9 = $prosedur_pasien['kode'];
    $deskripsi_icd9 = $prosedur_pasien['deskripsi_panjang'];

    $mlite_satu_sehat_response = $this->db('mlite_satu_sehat_response')->where('no_rawat', $no_rawat)->oneArray();

    $id_pasien = json_decode($this->getPatient($no_ktp_pasien))->entry[0]->resource->id;
    $id_encounter = $mlite_satu_sehat_response['id_encounter'];
    $tgl_pulang = $mlite_billing['tgl_billing'];
    $jam_pulang = $mlite_billing['jam_billing'];

    $kunjungan = 'Kunjungan';
    if ($status_lanjut == 'Ranap') {
      $kunjungan = 'Perawatan';
    }

    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => $this->fhirurl . '/Procedure',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_HTTPHEADER => array('Content-Type: application/json', 'Authorization: Bearer ' . json_decode($this->getToken())->access_token),
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS => '{
        "resourceType": "Procedure", 
        "status": "completed",
        "category": {
            "coding": [
                {
                    "system": "http://snomed.info/sct", 
                    "code": "103693007", 
                    "display": "Diagnostic procedure"
                }
            ], 
            "text":"Diagnostic procedure"
        }, 
        "code": {
            "coding": [
                {
                    "system": "http://hl7.org/fhir/sid/icd-9-cm", 
                    "code": "' . $kode_icd9 . '", 
                    "display": "' . $deskripsi_icd9 . '"
                
                }
            ]
        }, 
        "subject": {
            "reference": "Patient/' . $id_pasien . '", 
            "display": "' . $nama_pasien . '"
        }, 
        "encounter": {
            "reference": "Encounter/' . $id_encounter . '", 
            "display": "Prosedur kepada ' . $nama_pasien . ' selama ' . $kunjungan . ' dari tanggal ' . $tgl_registrasi . 'T' . $jam_reg . '' . $zonawaktu . ' sampai ' . $tgl_pulang . 'T' . $jam_pulang . '' . $zonawaktu . '"
        }, 
        "performedPeriod": {
            "start": "' . $tgl_registrasi . 'T' . $jam_reg . '' . $zonawaktu . '",
            "end": "' . $tgl_pulang . 'T' . $jam_pulang . '' . $zonawaktu . '"
        }
      }',
    ));

    $response = curl_exec($curl);

    $id_procedure = json_decode($response)->id;
    $pesan = 'Gagal mengirim procedure platform Satu Sehat!!';
    if ($id_procedure) {
      $mlite_satu_sehat_response = $this->db('mlite_satu_sehat_response')->where('no_rawat', $no_rawat)->oneArray();
      if ($mlite_satu_sehat_response) {
        $this->db('mlite_satu_sehat_response')
          ->where('no_rawat', $no_rawat)
          ->save([
            'no_rawat' => $no_rawat,
            'id_procedure' => $id_procedure
          ]);
      } else {
        $this->db('mlite_satu_sehat_response')
          ->save([
            'no_rawat' => $no_rawat,
            'id_procedure' => $id_procedure
          ]);
      }
      $pesan = 'Sukses mengirim procedure platform Satu Sehat!!';
    }

    curl_close($curl);
    // echo $response;

    echo $this->draw('procedure.html', ['pesan' => $pesan, 'response' => $response]);
    exit();
  }

  public function getDietGizi($no_rawat)
  {

    $zonawaktu = '+07:00';
    if ($this->settings->get('satu_sehat.zonawaktu') == 'WITA') {
      $zonawaktu = '+08:00';
    }
    if ($this->settings->get('satu_sehat.zonawaktu') == 'WIT') {
      $zonawaktu = '+09:00';
    }


    $no_rawat = revertNoRawat($no_rawat);
    $kd_poli = $this->core->getRegPeriksaInfo('kd_poli', $no_rawat);
    $nm_poli = $this->core->getPoliklinikInfo('nm_poli', $kd_poli);
    $kd_dokter = $this->core->getRegPeriksaInfo('kd_dokter', $no_rawat);
    $no_ktp_dokter = $this->core->getPegawaiInfo('no_ktp', $kd_dokter);
    $nama_dokter = $this->core->getPegawaiInfo('nama', $kd_dokter);
    $no_rkm_medis = $this->core->getRegPeriksaInfo('no_rkm_medis', $no_rawat);
    $no_ktp_pasien = $this->core->getPasienInfo('no_ktp', $no_rkm_medis);
    $nama_pasien = $this->core->getPasienInfo('nm_pasien', $no_rkm_medis);
    $status_lanjut = $this->core->getRegPeriksaInfo('status_lanjut', $no_rawat);
    $tgl_registrasi = $this->core->getRegPeriksaInfo('tgl_registrasi', $no_rawat);
    $jam_reg = $this->core->getRegPeriksaInfo('jam_reg', $no_rawat);
    $mlite_billing = $this->db('mlite_billing')->where('no_rawat', $no_rawat)->oneArray();
    $kd_dokter = $this->core->getRegPeriksaInfo('kd_dokter', $no_rawat);

    $id_dokter = $this->db('mlite_satu_sehat_mapping_praktisi')->select('practitioner_id')->where('kd_dokter', $kd_dokter)->oneArray();

    $mlite_satu_sehat_response = $this->db('mlite_satu_sehat_response')->where('no_rawat', $no_rawat)->oneArray();

    $id_pasien = json_decode($this->getPatient($no_ktp_pasien))->entry[0]->resource->id;
    $id_encounter = $mlite_satu_sehat_response['id_encounter'];

    $date = date('Y-m-d');
    $time = date('H:i:s');

    $nama_dokter = $this->core->getPegawaiInfo('nama', $kd_dokter);
    $tgl_registrasi = $this->core->getRegPeriksaInfo('tgl_registrasi', $no_rawat);
    $adime_gizi = $this->db('catatan_adime_gizi')->where('no_rawat', $no_rawat)->oneArray();
    $instruksi = $adime_gizi['instruksi'];

    $curl = curl_init();

    $data = '{
      "resourceType" : "Composition",
      "identifier" : {
          "system" : "http://sys-ids.kemkes.go.id/composition/' . $this->organizationid . '",
          "value" : "' . $no_rawat . '"
      },
      "status" : "final",
      "type" : {
          "coding" : [
              {
                  "system" : "http://loinc.org",
                  "code" : "18842-5",
                  "display" : "Discharge summary"
              }
          ]
      },
      "category" : [
          {
              "coding" : [
                  {
                      "system" : "http://loinc.org",
                      "code" : "LP173421-1",
                      "display" : "Report"
                  }
              ]
          }
      ],
      "subject" : {
          "reference" : "Patient/' . $id_pasien . '",
          "display" : "' . $nama_pasien . '"
      },
      "encounter" : {
          "reference" : "Encounter/' . $id_encounter . '", 
          "display" : "Kunjungan ' . $nama_pasien . ' pada tanggal ' . $tgl_registrasi . ' dengan nomor kunjungan ' . $no_rawat . '"
      },
      "date" : "' . $date . 'T' . $time . '' . $zonawaktu . '", 
      "author" : [
          {
              "reference" : "Practitioner/' . $id_dokter['practitioner_id'] . '",
              "display" : "' . $nama_dokter . '"
          }
      ],
      "title" : "Modul Gizi",
      "custodian" : {
          "reference" : "Organization/' . $this->organizationid . '" 
      },
      "section" : [
          {
              "code" : {
                  "coding" : [
                      {
                          "system" : "http://loinc.org",
                          "code" : "42344-2",
                          "display" : "Discharge diet (narrative)"
                      }
                  ]
              },
              "text" : {
                  "status" : "additional",
                  "div" : "' . $instruksi . '"
              }
          }
      ]
    }';

    curl_setopt_array($curl, array(
      CURLOPT_URL => $this->fhirurl . '/Composition',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_HTTPHEADER => array('Content-Type: application/json', 'Authorization: Bearer ' . json_decode($this->getToken())->access_token),
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS => $data
    ));

    $response = curl_exec($curl);

    $id_composition = json_decode($response)->id;
    $pesan = 'Gagal mengirim composition platform Satu Sehat!!';
    if ($id_composition) {
      $mlite_satu_sehat_response = $this->db('mlite_satu_sehat_response')->where('no_rawat', $no_rawat)->oneArray();
      if ($mlite_satu_sehat_response) {
        $this->db('mlite_satu_sehat_response')
          ->where('no_rawat', $no_rawat)
          ->save([
            'no_rawat' => $no_rawat,
            'id_composition' => $id_composition
          ]);
      } else {
        $this->db('mlite_satu_sehat_response')
          ->save([
            'no_rawat' => $no_rawat,
            'id_composition' => $id_composition
          ]);
      }
      $pesan = 'Sukses mengirim id_composition platform Satu Sehat!!';
    }

    curl_close($curl);
    // echo $response;
    echo '<pre>' . $data . '</pre>';

    echo $this->draw('dietgizi.html', ['pesan' => $pesan, 'response' => $response]);
    exit();
  }

  public function getVaksin($no_rawat)
  {

    $zonawaktu = '+07:00';
    if ($this->settings->get('satu_sehat.zonawaktu') == 'WITA') {
      $zonawaktu = '+08:00';
    }
    if ($this->settings->get('satu_sehat.zonawaktu') == 'WIT') {
      $zonawaktu = '+09:00';
    }


    $no_rawat = revertNoRawat($no_rawat);

    // Data resep dan mapping obat
    $row['medications'] = $this->db('resep_obat')
      ->join('resep_dokter', 'resep_dokter.no_resep = resep_obat.no_resep')
      ->join('mlite_satu_sehat_mapping_obat', 'mlite_satu_sehat_mapping_obat.kode_brng = resep_dokter.kode_brng')
      ->where('mlite_satu_sehat_mapping_obat.tipe', 'vaksin')
      ->where('no_rawat', $no_rawat)
      ->toArray();

    // Data pasien dan dokter
    $kd_poli       = $this->core->getRegPeriksaInfo('kd_poli', $no_rawat);
    $no_rkm_medis  = $this->core->getRegPeriksaInfo('no_rkm_medis', $no_rawat);
    $nm_pasien = $this->core->getPasienInfo('nm_pasien', $no_rkm_medis);
    $no_ktp_pasien = $this->core->getPasienInfo('no_ktp', $no_rkm_medis);
    $kd_dokter     = $this->core->getRegPeriksaInfo('kd_dokter', $no_rawat);
    $nm_dokter     = $this->core->getPegawaiInfo('nama', $kd_dokter);
    $id_dokter     = $this->db('mlite_satu_sehat_mapping_praktisi')
      ->select('practitioner_id')
      ->where('kd_dokter', $kd_dokter)
      ->oneArray();
    $id_pasien     = json_decode($this->getPatient($no_ktp_pasien))
      ->entry[0]->resource->id;

    $mlite_satu_sehat_response = $this->db('mlite_satu_sehat_response')->where('no_rawat', $no_rawat)->oneArray();

    foreach ($row['medications'] as $i => $obat) {
      $medReqId = $this->gen_uuid();
      $medId = $obat['no_resep'] . '' . $obat['kode_brng'];
      $medUuid = "urn:uuid:" . $this->gen_uuid();
      $system_cek = 'http://terminology.hl7.org/CodeSystem/v3-orderableDrugForm';
      if (is_string($obat['satuan_den']) && ctype_digit($obat['satuan_den'])) {
        $system_cek = 'http://snomed.info/sct';
      }

      // Parsing aturan pakai
      if (preg_match_all('/\d+/', $obat['aturan_pakai'], $m) && count($m[0]) >= 2) {
        $frequency = (int)$m[0][0];
        $doseValue = (int)$m[0][1];
      } else {
        $frequency = 1;
        $doseValue = 1;
      }

      // $duration = max(1, (int)round($obat['jml'] / $frequency / $doseValue));
      // $startDate = $obat['tgl_peresepan'];
      // $endDate = date('Y-m-d', strtotime("$startDate +{$duration} days"));

      // $satu_sehat_mapping_obat = $this->db('mlite_satu_sehat_mapping_obat')->where('kode_brng', $obat['kode_brng'])->oneArray();
      $mlite_satu_sehat_lokasi = $this->db('mlite_satu_sehat_lokasi')->where('kode', $kd_poli)->oneArray();
      $databarang = $this->db('databarang')->where('kode_brng', $obat['kode_brng'])->oneArray();
      $gudangbarang = $this->db('gudangbarang')->where('kode_brng', $obat['kode_brng'])->where('kd_bangsal', $this->core->getSettings('satu_sehat', 'farmasi'))->oneArray();
      // $batch['no_batch'] = '121212';

      $data = '{
        "resourceType": "Immunization",
        "status": "completed",
        "vaccineCode": {
            "coding": [
                {
                    "system": "http://sys-ids.kemkes.go.id/kfa",
                    "code": "' . $obat['kode_kfa'] . '",
                    "display": "' . $obat['nama_kfa'] . '"
                }
            ]
        },
        "patient": {
            "reference": "Patient/' . $id_pasien . '"
        },
        "encounter": {
            "reference": "Encounter/' . $mlite_satu_sehat_response['id_encounter'] . '"
        },
        "occurrenceDateTime": "' . $obat['tgl_perawatan'] . 'T' . $obat['jam'] . '' . $zonawaktu . '",
        "expirationDate": "' . $databarang['expire'] . '",
        "recorded": "' . $obat['tgl_perawatan'] . 'T' . $obat['jam'] . '' . $zonawaktu . '",
        "primarySource": true,
        "location": {
            "reference": "Location/' . $mlite_satu_sehat_lokasi['id_lokasi_satusehat'] . '",
            "display": "' . $mlite_satu_sehat_lokasi['lokasi'] . '"
        },
        "lotNumber": "' . $gudangbarang['no_batch'] . '",
        "route": {
            "coding": [
                {
                      "system": "http://www.whocc.no/atc",  
                      "code": "' . $obat['kode_route'] . '",
                      "display": "' . $obat['nama_route'] . '"
                }
            ]
        },
        "doseQuantity": {
            "value": ' . (int)$obat['jml'] . ',
            "unit": "' . $obat['satuan_num'] . '",
            "system": "' . $obat['system_num'] . '",
            "code": "' . $obat['satuan_num'] . '"
        },
        "performer": [
            {
                "function": {
                    "coding": [
                        {
                            "system": "http://terminology.hl7.org/CodeSystem/v2-0443",
                            "code": "AP",
                            "display": "Administering Provider"
                        }
                    ]
                },
                "actor": {
                    "reference": "Practitioner/' . $id_dokter['practitioner_id'] . '"
                }
            }
        ],
        "reasonCode": [
            {
                "coding": [
                    {
                        "system": "http://terminology.kemkes.go.id/CodeSystem/immunization-reason",
                        "code": "IM-Program",
                        "display" : "Imunisasi Program"
                    }
                ]
            }
        ],
        "protocolApplied" : [
            {
                "doseNumberPositiveInt" : ' . (int)$doseValue . '
            }
        ]
      }';

      $url = $this->fhirurl . '/Immunization';
      $curl = curl_init();

      curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_HTTPHEADER => array('Content-Type: application/json', 'Authorization: Bearer ' . json_decode($this->getToken())->access_token),
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $data,
      ));

      $response = curl_exec($curl);

      $id_immunization = json_decode($response)->id;
      $pesan = 'Gagal mengirim vaksin/imunisasi platform Satu Sehat!!';
      if ($id_immunization) {
        $this->db('mlite_satu_sehat_response')
          ->where('no_rawat', $no_rawat)
          ->save([
            'id_immunization' => $id_immunization
          ]);
        $pesan = 'Sukses mengirim vaksin/imunisasi platform Satu Sehat!!';
      }

      curl_close($curl);

      // echo '<pre>'. $data. '</pre>';

    }


    echo $this->draw('vaksin.html', ['pesan' => $pesan, 'response' => $response]);
    exit();
  }

  public function getClinicalImpression($no_rawat)
  {

    $zonawaktu = '+07:00';
    if ($this->settings->get('satu_sehat.zonawaktu') == 'WITA') {
      $zonawaktu = '+08:00';
    }
    if ($this->settings->get('satu_sehat.zonawaktu') == 'WIT') {
      $zonawaktu = '+09:00';
    }

    $no_rawat = revertNoRawat($no_rawat);

    $mlite_satu_sehat_response = $this->db('mlite_satu_sehat_response')->where('no_rawat', $no_rawat)->oneArray();
    $pemeriksaan_ralan = $this->db('pemeriksaan_ralan')->where('no_rawat', $no_rawat)->oneArray();

    $keluhan = $pemeriksaan_ralan['keluhan'];
    $pemeriksaan = $pemeriksaan_ralan['pemeriksaan'];
    $penilaian = $pemeriksaan_ralan['penilaian'];
    $no_rkm_medis = $this->core->getRegPeriksaInfo('no_rkm_medis', $no_rawat);
    $no_ktp_pasien = $this->core->getPasienInfo('no_ktp', $no_rkm_medis);
    $nama_pasien = $this->core->getPasienInfo('nm_pasien', $no_rkm_medis);
    $kd_dokter = $this->core->getRegPeriksaInfo('kd_dokter', $no_rawat);
    $tgl_perawatan = $pemeriksaan_ralan['tgl_perawatan'];
    $jam_rawat = $pemeriksaan_ralan['jam_rawat'];
    $id_dokter = $this->db('mlite_satu_sehat_mapping_praktisi')->select('practitioner_id')->where('kd_dokter', $kd_dokter)->oneArray();
    $nama_dokter = $this->db('dokter')->where('kd_dokter', $kd_dokter)->oneArray();
    $id_condition = $mlite_satu_sehat_response['id_condition'];
    $diagnosa_pasien = $this->db('diagnosa_pasien')
      ->join('penyakit', 'penyakit.kd_penyakit=diagnosa_pasien.kd_penyakit')
      ->where('no_rawat', $no_rawat)
      ->where('prioritas', '1')
      ->oneArray();

    $kd_penyakit = $diagnosa_pasien['kd_penyakit'];
    $nm_penyakit = $diagnosa_pasien['nm_penyakit'];

    $id_pasien = json_decode($this->getPatient($no_ktp_pasien))->entry[0]->resource->id;
    $id_encounter = $mlite_satu_sehat_response['id_encounter'];

    $data = '{
      "resourceType": "ClinicalImpression",
      "status": "completed",
      "description": "Evaluasi klinis untuk pasien dengan ' . $keluhan . ', ' . $pemeriksaan . '.",
      "subject": {
        "reference": "Patient/' . $id_pasien . '"
      },
      "encounter": {
        "reference": "Encounter/' . $id_encounter . '"
      },
      "effectiveDateTime": "' . $tgl_perawatan . 'T' . $jam_rawat . '' . $zonawaktu . '",
      "date": "' . $tgl_perawatan . 'T' . $jam_rawat . '' . $zonawaktu . '",
      "assessor": {
        "reference": "Practitioner/' . $id_dokter['practitioner_id'] . '"
      },
      "summary": "' . $penilaian . '", 
      "finding": [
        {
          "itemCodeableConcept": {
            "coding": [
              {
                "system": "http://hl7.org/fhir/sid/icd-10",
                "code": "' . $kd_penyakit . '",
                "display": "' . $nm_penyakit . '"
              }
            ]
          },
          "itemReference": {
              "reference": "Condition/' . $id_condition . '" 
          }
        }
      ],
      "prognosisCodeableConcept": [
        {
          "coding": [
            {
              "system": "http://terminology.kemkes.go.id/CodeSystem/clinical-term",
              "code": "PR000001",
              "display": "Prognosis"
            }
          ]
        }
      ]
    }';

    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => $this->fhirurl . '/ClinicalImpression',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_HTTPHEADER => array('Content-Type: application/json', 'Authorization: Bearer ' . json_decode($this->getToken())->access_token),
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS => $data,
    ));

    $response = curl_exec($curl);

    $id_clinical_impression = json_decode($response)->id;
    $pesan = 'Gagal mengirim clinical impression platform Satu Sehat!!';
    if ($id_clinical_impression) {
      $mlite_satu_sehat_response = $this->db('mlite_satu_sehat_response')->where('no_rawat', $no_rawat)->oneArray();
      if ($mlite_satu_sehat_response) {
        $this->db('mlite_satu_sehat_response')
          ->where('no_rawat', $no_rawat)
          ->save([
            'no_rawat' => $no_rawat,
            'id_clinical_impression' => $id_clinical_impression
          ]);
      } else {
        $this->db('mlite_satu_sehat_response')
          ->save([
            'no_rawat' => $no_rawat,
            'id_clinical_impression' => $id_clinical_impression
          ]);
      }
      $pesan = 'Sukses mengirim clinical impression platform Satu Sehat!!';
    }

    curl_close($curl);
    // echo '<pre>' . $data . '</pre>';

    echo $this->draw('clinical.impression.html', ['pesan' => $pesan, 'response' => $response]);
    exit();
  }

  public function getMedication(string $tipe = 'request', string $no_rawat = '')
  {
    // Zona waktu
    $zonawaktu = match ($this->settings->get('satu_sehat.zonawaktu')) {
      'WITA' => '+08:00',
      'WIT'  => '+09:00',
      default => '+07:00',
    };

    $kode_brng = $no_rawat;
    $no_rawat = revertNoRawat($no_rawat);

    if ($tipe === 'request') {

      // Data resep dan mapping obat
      $row['medications'] = $this->db('resep_obat')
        ->join('resep_dokter', 'resep_dokter.no_resep = resep_obat.no_resep')
        ->join('mlite_satu_sehat_mapping_obat', 'mlite_satu_sehat_mapping_obat.kode_brng = resep_dokter.kode_brng')
        ->where('mlite_satu_sehat_mapping_obat.tipe', 'obat')
        ->where('no_rawat', $no_rawat)
        ->toArray();

      // Data pasien dan dokter
      $no_rkm_medis  = $this->core->getRegPeriksaInfo('no_rkm_medis', $no_rawat);
      $nm_pasien = $this->core->getPasienInfo('nm_pasien', $no_rkm_medis);
      $no_ktp_pasien = $this->core->getPasienInfo('no_ktp', $no_rkm_medis);
      $kd_dokter     = $this->core->getRegPeriksaInfo('kd_dokter', $no_rawat);
      $nm_dokter     = $this->core->getPegawaiInfo('nama', $kd_dokter);
      $id_dokter     = $this->db('mlite_satu_sehat_mapping_praktisi')
        ->select('practitioner_id')
        ->where('kd_dokter', $kd_dokter)
        ->oneArray();
      $id_pasien     = json_decode($this->getPatient($no_ktp_pasien))
        ->entry[0]->resource->id;

      $mlite_satu_sehat_response = $this->db('mlite_satu_sehat_response')->where('no_rawat', $no_rawat)->oneArray();

      foreach ($row['medications'] as $i => $obat) {
        $medReqId = $this->gen_uuid();
        $medId = $obat['no_resep'] . '' . $obat['kode_brng'];
        $medUuid = "urn:uuid:" . $this->gen_uuid();
        $system_cek = 'http://terminology.hl7.org/CodeSystem/v3-orderableDrugForm';
        if (ctype_digit($obat['satuan_den'])) {
          $system_cek = 'http://snomed.info/sct';
        }

        // Parsing aturan pakai
        if (preg_match_all('/\d+/', $obat['aturan_pakai'], $m) && count($m[0]) >= 2) {
          $frequency = (int)$m[0][0];
          $doseValue = (int)$m[0][1];
        } else {
          $frequency = 1;
          $doseValue = 1;
        }

        // $duration = max(1, (int)round($obat['jml'] / $frequency / $doseValue));
        // $startDate = $obat['tgl_peresepan'];
        // $endDate = date('Y-m-d', strtotime("$startDate +{$duration} days"));

        $satu_sehat_mapping_obat = $this->db('mlite_satu_sehat_mapping_obat')->where('kode_brng', $obat['kode_brng'])->oneArray();

        $data = '{
              "resourceType": "MedicationRequest",
              "identifier": [
                  {
                      "system": "http://sys-ids.kemkes.go.id/prescription/' . $this->organizationid . '",
                      "use": "official",
                      "value": "' . $obat['no_resep'] . '"
                  },
                  {
                      "system": "http://sys-ids.kemkes.go.id/prescription-item/' . $this->organizationid . '",
                      "use": "official",
                      "value": "' . $obat['kode_brng'] . '"
                  }
              ],
              "status": "completed",
              "intent": "order",
              "category": [
                  {
                      "coding": [
                          {
                              "system": "http://terminology.hl7.org/CodeSystem/medicationrequest-category",
                              "code": "outpatient",
                              "display": "Outpatient"
                          }
                      ]
                  }
              ],
              "medicationReference": {
                  "reference": "Medication/' . $satu_sehat_mapping_obat['id_medication'] . '",
                  "display": "' . $obat['nama_kfa'] . '"
              },
              "subject": {
                  "reference": "Patient/' . $id_pasien . '",
                  "display": "' . $nm_pasien . '"
              },
              "encounter": {
                  "reference": "Encounter/' . $mlite_satu_sehat_response['id_encounter'] . '"
              },
              "authoredOn": "' . $obat['tgl_peresepan'] . 'T' . $obat['jam_peresepan'] . '' . $zonawaktu . '",
              "requester": {
                  "reference": "Practitioner/' . $id_dokter['practitioner_id'] . '",
                  "display": "' . $nm_dokter . '"
              },
              "dosageInstruction": [
                  {
                      "sequence": 1,
                      "patientInstruction": "' . $obat['aturan_pakai'] . '",
                      "timing": {
                          "repeat": {
                              "frequency": ' . $doseValue . ',
                              "period": 1,
                              "periodUnit": "d"
                          }
                      },
                      "route": {
                          "coding": [
                              {
                                  "system": "http://www.whocc.no/atc",
                                  "code": "' . $obat['kode_route'] . '",
                                  "display": "' . $obat['nama_route'] . '"
                              }
                          ]
                      },
                      "doseAndRate": [
                          {
                              "doseQuantity": {
                                  "value": ' . $frequency . ',
                                  "unit": "' . $obat['satuan_den'] . '",
                                  "system": "' . $system_cek . '",
                                  "code": "' . $obat['satuan_den'] . '"
                              }
                          }
                      ]
                  }
              ],
              "dispenseRequest": {
                  "quantity": {
                      "value": ' . $obat['jml'] . ',
                      "unit": "' . $obat['satuan_den'] . '",
                      "system": "' . $system_cek . '",
                      "code": "' . $obat['satuan_den'] . '"
                  },
                  "performer": {
                      "reference": "Organization/' . $this->organizationid . '"
                  }
              }
            }';

        $url = $this->fhirurl . '/MedicationRequest';
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_HTTPHEADER => array('Content-Type: application/json', 'Authorization: Bearer ' . json_decode($this->getToken())->access_token),
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS => $data,
        ));

        $response = curl_exec($curl);

        $id_medication_request = json_decode($response)->id;
        $pesan = 'Gagal mengirim medication request platform Satu Sehat!!';
        if ($id_medication_request) {
          $this->db('mlite_satu_sehat_response')
            ->where('no_rawat', $no_rawat)
            ->save([
              'id_medication_request' => $id_medication_request
            ]);
          $pesan = 'Sukses mengirim medication request platform Satu Sehat!!';
        }

        curl_close($curl);

        // echo '<pre>'. $data. '</pre>';

      }
    } else if ($tipe === 'dispense') {
      // Data resep dan mapping obat
      $row['medications'] = $this->db('resep_obat')
        ->join('resep_dokter', 'resep_dokter.no_resep = resep_obat.no_resep')
        ->join('mlite_satu_sehat_mapping_obat', 'mlite_satu_sehat_mapping_obat.kode_brng = resep_dokter.kode_brng')
        ->where('mlite_satu_sehat_mapping_obat.tipe', 'obat')
        ->where('no_rawat', $no_rawat)
        ->toArray();

      // Data pasien dan dokter
      $no_rkm_medis  = $this->core->getRegPeriksaInfo('no_rkm_medis', $no_rawat);
      $nm_pasien = $this->core->getPasienInfo('nm_pasien', $no_rkm_medis);
      $no_ktp_pasien = $this->core->getPasienInfo('no_ktp', $no_rkm_medis);
      $kd_dokter     = $this->core->getRegPeriksaInfo('kd_dokter', $no_rawat);
      $nm_dokter     = $this->core->getPegawaiInfo('nama', $kd_dokter);
      $id_dokter     = $this->db('mlite_satu_sehat_mapping_praktisi')
        ->select('practitioner_id')
        ->where('kd_dokter', $kd_dokter)
        ->oneArray();
      $id_pasien     = json_decode($this->getPatient($no_ktp_pasien))
        ->entry[0]->resource->id;

      $mlite_satu_sehat_response = $this->db('mlite_satu_sehat_response')->where('no_rawat', $no_rawat)->oneArray();
      $mlite_satu_sehat_lokasi = $this->db('mlite_satu_sehat_lokasi')->where('kode', $this->core->getSettings('satu_sehat', 'farmasi'))->oneArray();

      foreach ($row['medications'] as $i => $obat) {
        $medReqId = $this->gen_uuid();
        $medId = $obat['no_resep'] . '' . $obat['kode_brng'];
        $medUuid = "urn:uuid:" . $this->gen_uuid();
        $system_cek = 'http://terminology.hl7.org/CodeSystem/v3-orderableDrugForm';
        if (ctype_digit($obat['satuan_den'])) {
          $system_cek = 'http://snomed.info/sct';
        }

        // Parsing aturan pakai
        if (preg_match_all('/\d+/', $obat['aturan_pakai'], $m) && count($m[0]) >= 2) {
          $frequency = (int)$m[0][0];
          $doseValue = (int)$m[0][1];
        } else {
          $frequency = 1;
          $doseValue = 1;
        }

        // $duration = max(1, (int)round($obat['jml'] / $frequency / $doseValue));
        // $startDate = $obat['tgl_peresepan'];
        // $endDate = date('Y-m-d', strtotime("$startDate +{$duration} days"));

        $satu_sehat_mapping_obat = $this->db('mlite_satu_sehat_mapping_obat')->where('kode_brng', $obat['kode_brng'])->oneArray();

        $data = '{
              "resourceType": "MedicationDispense",
              "identifier": [
                  {
                      "system": "http://sys-ids.kemkes.go.id/medicationdispense/' . $this->organizationid . '",
                      "use": "official",
                      "value": "' . $obat['no_resep'] . '"
                  },
                  {
                      "system": "http://sys-ids.kemkes.go.id/medicationdispense-item/' . $this->organizationid . '",
                      "use": "official",
                      "value": "' . $obat['kode_brng'] . '"
                  }
              ],
              "status": "completed",
              "category": {
                  "coding": [
                      {
                          "system": "http://terminology.hl7.org/fhir/CodeSystem/medicationdispense-category",
                          "code": "outpatient",
                          "display": "Outpatient"
                      }
                  ]
              },
              "medicationReference": {
                  "reference": "Medication/' . $satu_sehat_mapping_obat['id_medication'] . '",
                  "display": "' . $obat['nama_kfa'] . '"
              },
              "subject": {
                  "reference": "Patient/' . $id_pasien . '",
                  "display": "' . $nm_pasien . '"
              },
              "context": {
                  "reference": "Encounter/' . $mlite_satu_sehat_response['id_encounter'] . '"
              },
              "performer": [
                  {
                      "actor": {
                          "reference": "Practitioner/' . $id_dokter['practitioner_id'] . '",
                          "display": "' . $nm_dokter . '"
                      }
                  }
              ],
              "location": {
                  "reference": "Location/' . $mlite_satu_sehat_lokasi['id_lokasi_satusehat'] . '",
                  "display": "' . $mlite_satu_sehat_lokasi['lokasi'] . '"
              },
              "authorizingPrescription": [{
                  "reference": "MedicationRequest/' . $mlite_satu_sehat_response['id_medication_request'] . '"
              }],
              "quantity": {
                  "system": "' . $system_cek . '",
                  "code": "' . $obat['satuan_den'] . '",
                  "value": ' . $obat['jml'] . '
              },
              "whenPrepared": "' . $obat['tgl_peresepan'] . 'T' . $obat['jam_peresepan'] . '' . $zonawaktu . '",
              "whenHandedOver": "' . $obat['tgl_perawatan'] . 'T' . $obat['jam'] . '' . $zonawaktu . '",
              "dosageInstruction": [
                  {
                      "sequence": 1,
                      "text": "' . $obat['aturan_pakai'] . '",
                      "timing": {
                          "repeat": {
                              "frequency": ' . $doseValue . ',
                              "period": 1,
                              "periodUnit": "d"
                          }
                      },
                      "route": {
                          "coding": [
                              {
                                  "system": "http://www.whocc.no/atc",
                                  "code": "' . $obat['kode_route'] . '",
                                  "display": "' . $obat['nama_route'] . '"
                              }
                          ]
                      },
                      "doseAndRate": [
                          {
                              "doseQuantity": {
                                  "value": ' . $frequency . ',
                                  "unit": "' . $obat['satuan_den'] . '",
                                  "system": "' . $system_cek . '",
                                  "code": "' . $obat['satuan_den'] . '"
                              }
                          }
                      ]
                  }
              ]
            }';

        $url = $this->fhirurl . '/MedicationDispense';
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_HTTPHEADER => array('Content-Type: application/json', 'Authorization: Bearer ' . json_decode($this->getToken())->access_token),
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS => $data,
        ));

        $response = curl_exec($curl);

        $id_medication_dispense = json_decode($response)->id;
        $pesan = 'Gagal mengirim medication dispense platform Satu Sehat!!';
        if ($id_medication_dispense) {
          $this->db('mlite_satu_sehat_response')
            ->where('no_rawat', $no_rawat)
            ->save([
              'id_medication_dispense' => $id_medication_dispense
            ]);
          $pesan = 'Sukses mengirim medication dispense platform Satu Sehat!!';
        }

        curl_close($curl);

        // echo '<pre>'. $data. '</pre>';

      }
    } else if ($tipe === 'statement') {
    } else if ($tipe == 'mapping') {
      $satu_sehat_mapping_obat = $this->db('mlite_satu_sehat_mapping_obat')->where('kode_brng', $kode_brng)->oneArray();
      $data = '{
              "resourceType": "Medication",
              "meta": {
                  "profile": [
                      "https://fhir.kemkes.go.id/r4/StructureDefinition/Medication"
                  ]
              },
              "identifier": [
                  {
                      "system" : "http://sys-ids.kemkes.go.id/medication/' . $this->organizationid . '",
                      "use": "official",
                      "value" : "' . $satu_sehat_mapping_obat['kode_brng'] . '"
                  }
              ],
              "code": {
                  "coding": [
                      {
                          "system": "http://sys-ids.kemkes.go.id/kfa",
                          "code": "' . $satu_sehat_mapping_obat['kode_kfa'] . '",
                          "display": "' . $satu_sehat_mapping_obat['nama_kfa'] . '"
                      }
                  ]
              },
              "status": "active",
              "form": {
                  "coding": [
                      {
                          "system": "http://terminology.kemkes.go.id/CodeSystem/medication-form",
                          "code": "' . $satu_sehat_mapping_obat['kode_sediaan'] . '",
                          "display": "' . $satu_sehat_mapping_obat['nama_sediaan'] . '"
                      }
                  ]
              },
              "extension": [
                  {
                      "url": "https://fhir.kemkes.go.id/r4/StructureDefinition/MedicationType",
                          "valueCodeableConcept": {
                          "coding": [
                              {
                                  "system": "http://terminology.kemkes.go.id/CodeSystem/medication-type",
                                  "code": "NC",
                                  "display": "Non-compound"
                              }
                          ]
                      }
                  }
              ]
        }';

      $url = $this->fhirurl . '/Medication';
      $curl = curl_init();

      curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_HTTPHEADER => array('Content-Type: application/json', 'Authorization: Bearer ' . json_decode($this->getToken())->access_token),
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $data,
      ));

      $response = curl_exec($curl);

      $id_medication = json_decode($response)->id;
      $pesan = 'Gagal mengirim mapping medication platform Satu Sehat!!';
      if ($id_medication) {
        $this->db('mlite_satu_sehat_mapping_obat')
          ->where('kode_brng', $kode_brng)
          ->save([
            'id_medication' => $id_medication
          ]);
        $pesan = 'Sukses mengirim mapping medication platform Satu Sehat!!';
      }

      curl_close($curl);
    }

    echo $this->draw('medication.html', [
      'pesan' => $pesan,
      'response' => $response
    ]);
    exit();
  }

  public function getMedication_($tipe = '', $no_rawat = '')
  {

    $zonawaktu = '+07:00';
    if ($this->settings->get('satu_sehat.zonawaktu') == 'WITA') {
      $zonawaktu = '+08:00';
    }
    if ($this->settings->get('satu_sehat.zonawaktu') == 'WIT') {
      $zonawaktu = '+09:00';
    }

    $no_rawat = revertNoRawat($no_rawat);
    $row['medications'] = $this->db('resep_obat')
      ->join('resep_dokter', 'resep_dokter.no_resep=resep_obat.no_resep')
      ->join('mlite_satu_sehat_mapping_obat', 'mlite_satu_sehat_mapping_obat.kode_brng=resep_dokter.kode_brng')
      ->where('mlite_satu_sehat_mapping_obat.tipe', 'obat')
      ->where('no_rawat', $no_rawat)->toArray();

    $pesan = '';
    $response = '';

    $no_rkm_medis = $this->core->getRegPeriksaInfo('no_rkm_medis', $no_rawat);
    $no_ktp_pasien = $this->core->getPasienInfo('no_ktp', $no_rkm_medis);
    $kd_dokter = $this->core->getRegPeriksaInfo('kd_dokter', $no_rawat);
    $id_dokter = $this->db('mlite_satu_sehat_mapping_praktisi')->select('practitioner_id')->where('kd_dokter', $kd_dokter)->oneArray();
    $id_pasien = json_decode($this->getPatient($no_ktp_pasien))->entry[0]->resource->id;

    // Variabel statis (atau ambil dari fungsi lain)
    $patientId      = "Patient/$id_pasien";
    $practitionerId = "Practitioner/$id_dokter[practitioner_id]";

    $data = [];

    if ($tipe == 'request') {

      foreach ($row['medications'] as $idx => $med) {

        // --- 1. Medication  -----------------------------------------------------
        // $medId = 'med' . ($idx + 1);  // med1, med2, dst.
        $medId = $this->gen_uuid();

        $medicationResource = [
          'resourceType' => 'Medication',
          'id'           => $medId,
          'code'         => [
            'coding' => [[
              'system'  => 'http://www.whocc.no/atc',
              'code'    => $med['kode_kfa'],          // kolom mapping
              'display' => $med['nama_kfa']  // kolom mapping
            ]],
            'text' => $med['nama_kfa']             // mis. "Amoxicillin 500 mg Tablet"
          ],
          'form' => [
            'coding' => [[
              'system'  => 'http://terminology.hl7.org/CodeSystem/medication-form-codes',
              'code'    => $med['satuan_den'],                      // contoh: Tablet
              'display' => $med['nama_satuan_den']
            ]]
          ]
        ];

        // --- 2. MedicationRequest  ---------------------------------------------
        // $mrId = 'medrequest-' . str_pad($idx + 1, 3, '0', STR_PAD_LEFT);   // medrequest-001 ...
        $mrId = $this->gen_uuid();

        if (preg_match_all('/\d+/', $med['aturan_pakai'], $matches)) {
          $frequency = (int)$matches[0][0];
          $dose_value = (int)$matches[0][1];
        } else {
          $frequency = (int)0;
          $dose_value = (int)1;
        }

        $duration = $med['jml'] / $frequency;
        $start_date = $med['tgl_peresepan'];
        $end_date = date('Y-m-d', strtotime($start_date . ' + ' . (int)round($duration) . ' days'));

        $medicationRequestResource = [
          'resourceType' => 'MedicationRequest',
          'id'           => $mrId,
          'status'       => 'active',
          'intent'       => 'order',
          'authoredOn'   => date('c'),            // waktu sekarang ISO-8601
          'subject'      => ['reference' => $patientId],
          'requester'    => ['reference' => $practitionerId],
          'medicationReference' => ['reference' => "#$medId"],
          'dosageInstruction' => [[
            'text'   => $med['aturan_pakai'],    // mis. "1 tablet 3× sehari selama 5 hari"
            'timing' => [
              'repeat' => [
                'frequency'  => (int)$frequency, // 3
                'period'     => 1,
                'periodUnit' => 'd'
              ]
            ],
            'route' => [
              'coding' => [[
                'system'  => 'http://terminology.hl7.org/CodeSystem/route-codes',
                'code'    => $med['kode_route'],
                'display' => $med['nama_route']
              ]]
            ],
            'doseAndRate' => [[
              'doseQuantity' => [
                'value' => (float)$dose_value,   // 1
                'unit'  => strtolower($med['nama_satuan_den'])
              ]
            ]]
          ]],
          'dispenseRequest' => [
            'validityPeriod' => [
              'start' => $start_date,             // "2025-06-05"
              'end'   => $end_date                // "2025-06-10"
            ],
            'numberOfRepeatsAllowed' => 0,
            'quantity' => [
              'value' => (int)$med['jml'],               // 15
              'unit'  => strtolower($med['nama_satuan_den'])
            ],
            'expectedSupplyDuration' => [
              'value' => (int)$duration,          // 5
              'unit'  => 'days'
            ]
          ]
        ];

        // --- 3. Bundling keduanya ----------------------------------------------
        $data[] = [
          'resourceType' => 'Bundle',
          'type'         => 'collection',
          'entry'        => [
            ['resource' => $medicationResource],
            ['resource' => $medicationRequestResource]
          ]
        ];
      }
    } else if ($tipe == 'dispense') {
      $medication = '{ 
        "resourceType": "MedicationDispense",
        "identifier": [
          {
            "system": "http://sys-ids.kemkes.go.id/medication-dispense/",
            "value": "123456789"
          }
        ],
        "status": "completed",
        "category": {
          "coding": [
            {
              "system": "http://terminology.hl7.org/CodeSystem/medicationdispense-category",
              "code": "outpatient",
              "display": "Outpatient"
            }
          ]
        },
        "medicationReference": {
          "reference": "Medication/93021992",
          "display": "Ekstrak Phyllanthus niruri Herba 50 mg Kapsul (STIMUNO FORTE)"
        },
        "subject": {
          "reference": "Patient/P02028901234",
          "display": "BUDI SANTOSO"
        },
        "context": {
          "reference": "Encounter/123456"
        },
        "performer": [
          {
            "actor": {
              "reference": "Practitioner/N10000001",
              "display": "Apt. SUSI SUSANTI, S.Farm"
            }
          }
        ],
        "location": {
          "reference": "Location/123456",
          "display": "Apotek RS Sehat"
        },
        "authorizingPrescription": [
          {
            "reference": "MedicationRequest/789012"
          }
        ],
        "quantity": {
          "value": 21,
          "unit": "Kapsul",
          "system": "http://terminology.kemkes.go.id/CodeSystem/medication-form",
          "code": "BS019"
        },
        "daysSupply": {
          "value": 7,
          "unit": "days",
          "system": "http://unitsofmeasure.org",
          "code": "d"
        },
        "whenPrepared": "2024-01-20T09:45:00+07:00",
        "whenHandedOver": "2024-01-20T10:00:00+07:00",
        "dosageInstruction": [
          {
            "sequence": 1,
            "text": "3 x 1 kapsul sehari setelah makan",
            "timing": {
              "repeat": {
                "frequency": 3,
                "period": 1,
                "periodUnit": "d"
              }
            },
            "route": {
              "coding": [
                {
                  "system": "http://terminology.hl7.org/CodeSystem/v3-RouteOfAdministration",
                  "code": "PO",
                  "display": "Oral"
                }
              ]
            },
            "doseAndRate": [
              {
                "type": {
                  "coding": [
                    {
                      "system": "http://terminology.hl7.org/CodeSystem/dose-rate-type",
                      "code": "ordered",
                      "display": "Ordered"
                    }
                  ]
                },
                "doseQuantity": {
                  "value": 1,
                  "unit": "Kapsul",
                  "system": "http://terminology.kemkes.go.id/CodeSystem/medication-form",
                  "code": "BS019"
                }
              }
            ]
          }
        ]        
      }';
    }

    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => $this->fhirurl . '/MedicationRequest',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_HTTPHEADER => array('Content-Type: application/json', 'Authorization: Bearer ' . json_decode($this->getToken())->access_token),
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS => json_encode($data)
    ));

    $response = curl_exec($curl);

    $id_medication_request = json_decode($response)->id;
    $pesan = 'Gagal mengirim medication request platform Satu Sehat!!';
    if ($id_medication_request) {
      $mlite_satu_sehat_response = $this->db('mlite_satu_sehat_response')->where('no_rawat', $no_rawat)->oneArray();
      if ($mlite_satu_sehat_response) {
        $this->db('mlite_satu_sehat_response')
          ->where('no_rawat', $no_rawat)
          ->save([
            'no_rawat' => $no_rawat,
            'id_medication_request' => $id_medication_request
          ]);
      } else {
        $this->db('mlite_satu_sehat_response')
          ->save([
            'no_rawat' => $no_rawat,
            'id_medication_request' => $id_medication_request
          ]);
      }
      $pesan = 'Sukses mengirim medication request platform Satu Sehat!!';
    }

    curl_close($curl);

    // header('Content-Type: application/json');
    // echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);    

    echo $this->draw('medication.html', ['pesan' => $pesan, 'response' => $response]);
    exit();
  }

  public function getLaboratory($no_rawat = '', $no_order = '', $cat = '', $tipe = '')
  {

    $zonawaktu = '+07:00';
    if ($this->settings->get('satu_sehat.zonawaktu') == 'WITA') {
      $zonawaktu = '+08:00';
    }
    if ($this->settings->get('satu_sehat.zonawaktu') == 'WIT') {
      $zonawaktu = '+09:00';
    }


    $no_rawat = revertNoRawat($no_rawat);
    $pesan = '';
    $response = '';
    $laboratory = '';

    if ($cat == 'pk') {
      if ($tipe == 'request') {
        $laboratory = '
          {
            "resourceType": "ServiceRequest",
            "identifier": [
              {
                "system": "http://sys-ids.kemkes.go.id/servicerequest/8529d474-30e0-4fee-81a4-7a5234003a1b",
                "value": "LAB-PKL-20231214-001"
              }
            ],
            "status": "active",
            "intent": "original-order",
            "priority": "routine",
            "category": [
              {
                "coding": [
                  {
                    "system": "http://snomed.info/sct",
                    "code": "108252007",
                    "display": "Laboratory procedure"
                  }
                ]
              }
            ],
            "code": {
              "coding": [
                {
                  "system": "http://loinc.org",
                  "code": "24323-8",
                  "display": "Comprehensive metabolic 2000 panel - Serum or Plasma"
                }
              ],
              "text": "Panel Metabolik Komprehensif"
            },
            "subject": {
              "reference": "Patient/P02029555482",
              "display": "Budi Santoso"
            },
            "encounter": {
              "reference": "Encounter/E20231214001"
            },
            "occurrenceDateTime": "2023-12-14T08:30:00+07:00",
            "requester": {
              "reference": "Practitioner/D001",
              "display": "dr. Ahmad Rahman, Sp.PD"
            },
            "performer": [
              {
                "reference": "Organization/LAB-001",
                "display": "Laboratorium Patologi Klinik RSUD H. Damanhuri"
              }
            ],
            "reasonCode": [
              {
                "coding": [
                  {
                    "system": "http://snomed.info/sct",
                    "code": "73211009",
                    "display": "Diabetes mellitus"
                  }
                ],
                "text": "Kontrol Diabetes Mellitus"
              }
            ],
            "specimen": [
              {
                "reference": "Specimen/SPEC-20231214-001",
                "display": "Serum darah vena"
              }
            ]
          }
        ';
      }
      if ($tipe == 'specimen') {
        $laboratory = '
          {
            "resourceType": "Specimen",
            "identifier": [
              {
                "system": "http://sys-ids.kemkes.go.id/specimen/8529d474-30e0-4fee-81a4-7a5234003a1b",
                "value": "SPEC-20231214-001"
              }
            ],
            "status": "available",
            "type": {
              "coding": [
                {
                  "system": "http://snomed.info/sct",
                  "code": "119364003",
                  "display": "Serum specimen"
                }
              ]
            },
            "subject": {
              "reference": "Patient/P02029555482"
            },
            "receivedTime": "2023-12-14T08:45:00+07:00",
            "collection": {
              "collectedDateTime": "2023-12-14T08:30:00+07:00",
              "method": {
                "coding": [
                  {
                    "system": "http://snomed.info/sct",
                    "code": "28520004",
                    "display": "Venipuncture"
                  }
                ]
              },
              "bodySite": {
                "coding": [
                  {
                    "system": "http://snomed.info/sct",
                    "code": "368208006",
                    "display": "Left upper arm"
                  }
                ]
              }
            }
          }
        ';
      }
      if ($tipe == 'observation') {
        $laboratory = '
          {
            "resourceType": "Observation",
            "identifier": [
              {
                "system": "http://sys-ids.kemkes.go.id/observation/8529d474-30e0-4fee-81a4-7a5234003a1b",
                "value": "OBS-LAB-20231214-001"
              }
            ],
            "status": "final",
            "category": [
              {
                "coding": [
                  {
                    "system": "http://terminology.hl7.org/CodeSystem/observation-category",
                    "code": "laboratory",
                    "display": "Laboratory"
                  }
                ]
              }
            ],
            "code": {
              "coding": [
                {
                  "system": "http://loinc.org",
                  "code": "2345-7",
                  "display": "Glucose [Mass/volume] in Serum or Plasma"
                }
              ],
              "text": "Glukosa Darah"
            },
            "subject": {
              "reference": "Patient/P02029555482"
            },
            "encounter": {
              "reference": "Encounter/E20231214001"
            },
            "effectiveDateTime": "2023-12-14T08:30:00+07:00",
            "issued": "2023-12-14T10:30:00+07:00",
            "performer": [
              {
                "reference": "Practitioner/LAB001",
                "display": "dr. Siti Nurhaliza, Sp.PK"
              }
            ],
            "valueQuantity": {
              "value": 180,
              "unit": "mg/dL",
              "system": "http://unitsofmeasure.org",
              "code": "mg/dL"
            },
            "interpretation": [
              {
                "coding": [
                  {
                    "system": "http://terminology.hl7.org/CodeSystem/v3-ObservationInterpretation",
                    "code": "H",
                    "display": "High"
                  }
                ]
              }
            ],
            "referenceRange": [
              {
                "low": {
                  "value": 70,
                  "unit": "mg/dL"
                },
                "high": {
                  "value": 140,
                  "unit": "mg/dL"
                },
                "text": "70-140 mg/dL"
              }
            ],
            "specimen": {
              "reference": "Specimen/SPEC-20231214-001"
            }
          }
        ';
      }
      if ($tipe == 'diagnostic') {
        $laboratory = '
          {
            "resourceType": "DiagnosticReport",
            "identifier": [
              {
                "system": "http://sys-ids.kemkes.go.id/diagnosticreport/8529d474-30e0-4fee-81a4-7a5234003a1b",
                "value": "DR-LAB-20231214-001"
              }
            ],
            "status": "final",
            "category": [
              {
                "coding": [
                  {
                    "system": "http://terminology.hl7.org/CodeSystem/v2-0074",
                    "code": "LAB",
                    "display": "Laboratory"
                  }
                ]
              }
            ],
            "code": {
              "coding": [
                {
                  "system": "http://loinc.org",
                  "code": "24323-8",
                  "display": "Comprehensive metabolic 2000 panel - Serum or Plasma"
                }
              ]
            },
            "subject": {
              "reference": "Patient/P02029555482"
            },
            "encounter": {
              "reference": "Encounter/E20231214001"
            },
            "effectiveDateTime": "2023-12-14T08:30:00+07:00",
            "issued": "2023-12-14T10:30:00+07:00",
            "performer": [
              {
                "reference": "Organization/LAB-001"
              }
            ],
            "result": [
              {
                "reference": "Observation/OBS-LAB-20231214-001"
              },
              {
                "reference": "Observation/OBS-LAB-20231214-002"
              }
            ],
            "specimen": [
              {
                "reference": "Specimen/SPEC-20231214-001"
              }
            ],
            "conclusion": "Hasil pemeriksaan menunjukkan kadar glukosa darah tinggi, konsisten dengan diagnosis diabetes mellitus."
          }
        ';
      };
    }

    if ($cat == 'pa') {
      if ($tipe == 'request') {
        $laboratory = '
          {
            "resourceType": "ServiceRequest",
            "identifier": [
              {
                "system": "http://sys-ids.kemkes.go.id/servicerequest/8529d474-30e0-4fee-81a4-7a5234003a1b",
                "value": "PA-20231214-001"
              }
            ],
            "status": "active",
            "intent": "original-order",
            "priority": "routine",
            "category": [
              {
                "coding": [
                  {
                    "system": "http://snomed.info/sct",
                    "code": "394595002",
                    "display": "Pathology"
                  }
                ]
              }
            ],
            "code": {
              "coding": [
                {
                  "system": "http://loinc.org",
                  "code": "33717-0",
                  "display": "Cytology report of Cervical or vaginal smear or scraping Cyto stain"
                },
                {
                  "system": "http://snomed.info/sct",
                  "code": "168731009",
                  "display": "Cervical cytology"
                }
              ],
              "text": "Sitologi Serviks (Pap Smear)"
            },
            "subject": {
              "reference": "Patient/P02029555482",
              "display": "Siti Aisyah"
            },
            "encounter": {
              "reference": "Encounter/E20231214001"
            },
            "occurrenceDateTime": "2023-12-14T09:00:00+07:00",
            "requester": {
              "reference": "Practitioner/D002",
              "display": "dr. Maya Sari, Sp.OG"
            },
            "performer": [
              {
                "reference": "Organization/PA-001",
                "display": "Laboratorium Patologi Anatomi RSUD H. Damanhuri"
              }
            ],
            "reasonCode": [
              {
                "coding": [
                  {
                    "system": "http://snomed.info/sct",
                    "code": "268547008",
                    "display": "Screening for malignant neoplasm of cervix"
                  }
                ],
                "text": "Skrining Kanker Serviks"
              }
            ],
            "specimen": [
              {
                "reference": "Specimen/SPEC-PA-20231214-001",
                "display": "Apusan serviks"
              }
            ],
            "bodySite": [
              {
                "coding": [
                  {
                    "system": "http://snomed.info/sct",
                    "code": "71252005",
                    "display": "Cervix uteri"
                  }
                ],
                "text": "Serviks uteri"
              }
            ]
          }
        ';
      }
      if ($tipe == 'specimen') {
        $laboratory = '
          {
            "resourceType": "Specimen",
            "identifier": [
              {
                "system": "http://sys-ids.kemkes.go.id/specimen/8529d474-30e0-4fee-81a4-7a5234003a1b",
                "value": "SPEC-PA-20231214-001"
              }
            ],
            "status": "available",
            "type": {
              "coding": [
                {
                  "system": "http://snomed.info/sct",
                  "code": "309502007",
                  "display": "Cervical smear sample"
                }
              ],
              "text": "Apusan serviks"
            },
            "subject": {
              "reference": "Patient/P02029555482"
            },
            "receivedTime": "2023-12-14T09:30:00+07:00",
            "collection": {
              "collectedDateTime": "2023-12-14T09:00:00+07:00",
              "collector": {
                "reference": "Practitioner/D002"
              },
              "method": {
                "coding": [
                  {
                    "system": "http://snomed.info/sct",
                    "code": "129314006",
                    "display": "Biopsy - action"
                  }
                ]
              },
              "bodySite": {
                "coding": [
                  {
                    "system": "http://snomed.info/sct",
                    "code": "71252005",
                    "display": "Cervix uteri"
                  }
                ]
              }
            },
            "processing": [
              {
                "description": "Fiksasi dalam formalin 10%",
                "procedure": {
                  "coding": [
                    {
                      "system": "http://snomed.info/sct",
                      "code": "434684005",
                      "display": "Tissue fixation"
                    }
                  ]
                },
                "additive": [
                  {
                    "display": "Formalin 10%"
                  }
                ],
                "timeDateTime": "2023-12-14T09:30:00+07:00"
              }
            ]
          }
        ';
      }
      if ($tipe == 'observation') {
        $laboratory = '
          {
            "resourceType": "Observation",
            "identifier": [
              {
                "system": "http://sys-ids.kemkes.go.id/observation/8529d474-30e0-4fee-81a4-7a5234003a1b",
                "value": "OBS-PA-20231214-001"
              }
            ],
            "status": "final",
            "category": [
              {
                "coding": [
                  {
                    "system": "http://terminology.hl7.org/CodeSystem/observation-category",
                    "code": "laboratory",
                    "display": "Laboratory"
                  }
                ]
              }
            ],
            "code": {
              "coding": [
                {
                  "system": "http://loinc.org",
                  "code": "33717-0",
                  "display": "Cytology report of Cervical or vaginal smear or scraping Cyto stain"
                }
              ],
              "text": "Hasil Sitologi Serviks"
            },
            "subject": {
              "reference": "Patient/P02029555482"
            },
            "encounter": {
              "reference": "Encounter/E20231214001"
            },
            "effectiveDateTime": "2023-12-14T09:00:00+07:00",
            "issued": "2023-12-16T14:00:00+07:00",
            "performer": [
              {
                "reference": "Practitioner/PA001",
                "display": "dr. Ahmad Fauzi, Sp.PA"
              }
            ],
            "valueCodeableConcept": {
              "coding": [
                {
                  "system": "http://snomed.info/sct",
                  "code": "17621005",
                  "display": "Normal"
                }
              ],
              "text": "Dalam batas normal"
            },
            "interpretation": [
              {
                "coding": [
                  {
                    "system": "http://terminology.hl7.org/CodeSystem/v3-ObservationInterpretation",
                    "code": "N",
                    "display": "Normal"
                  }
                ]
              }
            ],
            "specimen": {
              "reference": "Specimen/SPEC-PA-20231214-001"
            },
            "component": [
              {
                "code": {
                  "coding": [
                    {
                      "system": "http://loinc.org",
                      "code": "33743-4",
                      "display": "Cytology report of Cervical or vaginal smear or scraping Cyto stain.microscopy"
                    }
                  ],
                  "text": "Gambaran Mikroskopis"
                },
                "valueString": "Sel epitel skuamous superfisial dan intermediet dalam jumlah adekuat. Tidak tampak sel abnormal. Tidak tampak mikroorganisme patogen."
              },
              {
                "code": {
                  "coding": [
                    {
                      "system": "http://loinc.org",
                      "code": "33747-5",
                      "display": "Cytology report of Cervical or vaginal smear or scraping Cyto stain.impression"
                    }
                  ],
                  "text": "Kesimpulan"
                },
                "valueString": "Sitologi serviks dalam batas normal. Tidak ada tanda-tanda keganasan."
              }
            ]
          }
        ';
      }
      if ($tipe == 'diagnostic') {
        $laboratory = '
          {
            "resourceType": "DiagnosticReport",
            "identifier": [
              {
                "system": "http://sys-ids.kemkes.go.id/diagnosticreport/8529d474-30e0-4fee-81a4-7a5234003a1b",
                "value": "DR-PA-20231214-001"
              }
            ],
            "status": "final",
            "category": [
              {
                "coding": [
                  {
                    "system": "http://terminology.hl7.org/CodeSystem/v2-0074",
                    "code": "PAT",
                    "display": "Pathology"
                  }
                ]
              }
            ],
            "code": {
              "coding": [
                {
                  "system": "http://loinc.org",
                  "code": "33717-0",
                  "display": "Cytology report of Cervical or vaginal smear or scraping Cyto stain"
                }
              ]
            },
            "subject": {
              "reference": "Patient/P02029555482"
            },
            "encounter": {
              "reference": "Encounter/E20231214001"
            },
            "effectiveDateTime": "2023-12-14T09:00:00+07:00",
            "issued": "2023-12-16T14:00:00+07:00",
            "performer": [
              {
                "reference": "Organization/PA-001"
              },
              {
                "reference": "Practitioner/PA001"
              }
            ],
            "result": [
              {
                "reference": "Observation/OBS-PA-20231214-001"
              }
            ],
            "specimen": [
              {
                "reference": "Specimen/SPEC-PA-20231214-001"
              }
            ],
            "conclusion": "Sitologi serviks dalam batas normal. Tidak ditemukan sel-sel abnormal atau tanda-tanda keganasan. Direkomendasikan pemeriksaan rutin sesuai protokol skrining.",
            "conclusionCode": [
              {
                "coding": [
                  {
                    "system": "http://snomed.info/sct",
                    "code": "17621005",
                    "display": "Normal"
                  }
                ]
              }
            ]
          }
        ';
      };
    }

    if ($cat == 'mb') {
      if ($tipe == 'request') {
        $laboratory = '
          {
            "resourceType": "ServiceRequest",
            "identifier": [
              {
                "system": "http://sys-ids.kemkes.go.id/servicerequest/8529d474-30e0-4fee-81a4-7a5234003a1b",
                "value": "MIKRO-20231214-001"
              }
            ],
            "status": "active",
            "intent": "original-order",
            "priority": "routine",
            "category": [
              {
                "coding": [
                  {
                    "system": "http://snomed.info/sct",
                    "code": "15220000",
                    "display": "Laboratory test"
                  }
                ]
              }
            ],
            "code": {
              "coding": [
                {
                  "system": "http://loinc.org",
                  "code": "600-7",
                  "display": "Bacteria identified in Urine by Culture"
                },
                {
                  "system": "http://snomed.info/sct",
                  "code": "252398009",
                  "display": "Urine culture"
                }
              ],
              "text": "Kultur Urin dan Uji Kepekaan"
            },
            "subject": {
              "reference": "Patient/P02029555482",
              "display": "Ahmad Rizki"
            },
            "encounter": {
              "reference": "Encounter/E20231214001"
            },
            "occurrenceDateTime": "2023-12-14T08:00:00+07:00",
            "requester": {
              "reference": "Practitioner/D003",
              "display": "dr. Fatimah Zahra, Sp.PD"
            },
            "performer": [
              {
                "reference": "Organization/MIKRO-001",
                "display": "Laboratorium Mikrobiologi RSUD H. Damanhuri"
              }
            ],
            "reasonCode": [
              {
                "coding": [
                  {
                    "system": "http://snomed.info/sct",
                    "code": "68566005",
                    "display": "Urinary tract infectious disease"
                  }
                ],
                "text": "Suspek Infeksi Saluran Kemih"
              }
            ],
            "specimen": [
              {
                "reference": "Specimen/SPEC-MIKRO-20231214-001",
                "display": "Urin midstream"
              }
            ],
            "note": [
              {
                "text": "Pasien mengeluh nyeri saat berkemih dan demam. Riwayat ISK berulang."
              }
            ]
          }
        ';
      }
      if ($tipe == 'specimen') {
        $laboratory = '
          {
            "resourceType": "Specimen",
            "identifier": [
              {
                "system": "http://sys-ids.kemkes.go.id/specimen/8529d474-30e0-4fee-81a4-7a5234003a1b",
                "value": "SPEC-MIKRO-20231214-001"
              }
            ],
            "status": "available",
            "type": {
              "coding": [
                {
                  "system": "http://snomed.info/sct",
                  "code": "122575003",
                  "display": "Urine specimen"
                }
              ],
              "text": "Urin midstream"
            },
            "subject": {
              "reference": "Patient/P02029555482"
            },
            "receivedTime": "2023-12-14T08:30:00+07:00",
            "collection": {
              "collectedDateTime": "2023-12-14T08:00:00+07:00",
              "method": {
                "coding": [
                  {
                    "system": "http://snomed.info/sct",
                    "code": "258574006",
                    "display": "Mid-stream urine sample"
                  }
                ]
              },
              "quantity": {
                "value": 10,
                "unit": "mL"
              }
            },
            "processing": [
              {
                "description": "Disimpan dalam suhu ruang, segera dikirim ke laboratorium",
                "timeDateTime": "2023-12-14T08:30:00+07:00"
              }
            ],
            "container": [
              {
                "type": {
                  "coding": [
                    {
                      "system": "http://snomed.info/sct",
                      "code": "706041008",
                      "display": "Urine collection container"
                    }
                  ]
                },
                "description": "Kontainer steril untuk urin"
              }
            ]
          }
        ';
      }
      if ($tipe == 'observation') {
        $laboratory = '
          {
            "resourceType": "Observation",
            "identifier": [
              {
                "system": "http://sys-ids.kemkes.go.id/observation/8529d474-30e0-4fee-81a4-7a5234003a1b",
                "value": "OBS-MIKRO-20231214-001"
              }
            ],
            "status": "final",
            "category": [
              {
                "coding": [
                  {
                    "system": "http://terminology.hl7.org/CodeSystem/observation-category",
                    "code": "laboratory",
                    "display": "Laboratory"
                  }
                ]
              }
            ],
            "code": {
              "coding": [
                {
                  "system": "http://loinc.org",
                  "code": "600-7",
                  "display": "Bacteria identified in Urine by Culture"
                }
              ],
              "text": "Identifikasi Bakteri dari Kultur Urin"
            },
            "subject": {
              "reference": "Patient/P02029555482"
            },
            "encounter": {
              "reference": "Encounter/E20231214001"
            },
            "effectiveDateTime": "2023-12-14T08:00:00+07:00",
            "issued": "2023-12-16T10:00:00+07:00",
            "performer": [
              {
                "reference": "Practitioner/MIKRO001",
                "display": "dr. Indira Sari, Sp.MK"
              }
            ],
            "valueCodeableConcept": {
              "coding": [
                {
                  "system": "http://snomed.info/sct",
                  "code": "112283007",
                  "display": "Escherichia coli"
                }
              ],
              "text": "Escherichia coli"
            },
            "specimen": {
              "reference": "Specimen/SPEC-MIKRO-20231214-001"
            },
            "component": [
              {
                "code": {
                  "coding": [
                    {
                      "system": "http://loinc.org",
                      "code": "87829-8",
                      "display": "Bacteria [#/volume] in Urine by Automated count"
                    }
                  ],
                  "text": "Jumlah Koloni"
                },
                "valueQuantity": {
                  "value": 100000,
                  "unit": "CFU/mL",
                  "system": "http://unitsofmeasure.org",
                  "code": "CFU/mL"
                }
              },
              {
                "code": {
                  "coding": [
                    {
                      "system": "http://loinc.org",
                      "code": "33747-0",
                      "display": "Microscopy report"
                    }
                  ],
                  "text": "Morfologi"
                },
                "valueString": "Basil gram negatif, motil"
              }
            ]
          }
        ';
      }
      if ($tipe == 'diagnostic') {
        $laboratory = '
          {
            "resourceType": "DiagnosticReport",
            "identifier": [
              {
                "system": "http://sys-ids.kemkes.go.id/diagnosticreport/8529d474-30e0-4fee-81a4-7a5234003a1b",
                "value": "DR-MIKRO-20231214-001"
              }
            ],
            "status": "final",
            "category": [
              {
                "coding": [
                  {
                    "system": "http://terminology.hl7.org/CodeSystem/v2-0074",
                    "code": "MB",
                    "display": "Microbiology"
                  }
                ]
              }
            ],
            "code": {
              "coding": [
                {
                  "system": "http://loinc.org",
                  "code": "600-7",
                  "display": "Bacteria identified in Urine by Culture"
                }
              ]
            },
            "subject": {
              "reference": "Patient/P02029555482"
            },
            "encounter": {
              "reference": "Encounter/E20231214001"
            },
            "effectiveDateTime": "2023-12-14T08:00:00+07:00",
            "issued": "2023-12-16T10:00:00+07:00",
            "performer": [
              {
                "reference": "Organization/MIKRO-001"
              },
              {
                "reference": "Practitioner/MIKRO001"
              }
            ],
            "result": [
              {
                "reference": "Observation/OBS-MIKRO-20231214-001"
              },
              {
                "reference": "Observation/OBS-MIKRO-20231214-002"
              }
            ],
            "specimen": [
              {
                "reference": "Specimen/SPEC-MIKRO-20231214-001"
              }
            ],
            "conclusion": "Ditemukan pertumbuhan Escherichia coli dengan jumlah koloni >10^5 CFU/mL yang signifikan untuk infeksi saluran kemih. Uji kepekaan menunjukkan bakteri sensitif terhadap ciprofloxacin, cotrimoxazole, dan nitrofurantoin. Resisten terhadap ampicillin.",
            "conclusionCode": [
              {
                "coding": [
                  {
                    "system": "http://snomed.info/sct",
                    "code": "112283007",
                    "display": "Escherichia coli"
                  }
                ]
              }
            ]
          }
        ';
      };
    }

    echo $this->draw('laboratory.html', ['pesan' => $pesan, 'response' => $response]);
    exit();
  }

  public function getRadiology($no_rawat = '', $no_order = '', $tipe = '')
  {

    $zonawaktu = '+07:00';
    if ($this->settings->get('satu_sehat.zonawaktu') == 'WITA') {
      $zonawaktu = '+08:00';
    }
    if ($this->settings->get('satu_sehat.zonawaktu') == 'WIT') {
      $zonawaktu = '+09:00';
    }


    $no_rawat = revertNoRawat($no_rawat);
    $pesan = '';
    $response = '';

    $radiologi = '';

    if ($tipe == 'request') {
      $radiologi = '{
        "resourceType": "ServiceRequest",
        "identifier": [
          {
            "system": "http://sys-ids.kemkes.go.id/servicerequest/8529d474-30e0-4fee-81a4-7a5234003a1b",
            "value": "RAD-20231214-001"
          }
        ],
        "status": "active",
        "intent": "original-order",
        "priority": "routine",
        "category": [
          {
            "coding": [
              {
                "system": "http://snomed.info/sct",
                "code": "363679005",
                "display": "Imaging"
              }
            ]
          }
        ],
        "code": {
          "coding": [
            {
              "system": "http://loinc.org",
              "code": "30746-2",
              "display": "Chest X-ray"
            }
          ]
        },
        "subject": {
          "reference": "Patient/P02029555482",
          "display": "Budi Santoso"
        },
        "encounter": {
          "reference": "Encounter/ENC-20231214-RAD-001"
        },
        "occurrenceDateTime": "2023-12-14T08:30:00+07:00",
        "requester": {
          "reference": "Practitioner/N10000001",
          "display": "dr. Ahmad Fauzi, Sp.PD"
        },
        "performer": [
          {
            "reference": "Practitioner/N10000002",
            "display": "dr. Sarah Dewi, Sp.Rad"
          }
        ],
        "locationReference": [
          {
            "reference": "Location/RAD-ROOM-01",
            "display": "Ruang Radiologi 1"
          }
        ],
        "reasonCode": [
          {
            "coding": [
              {
                "system": "http://hl7.org/fhir/sid/icd-10",
                "code": "R06.02",
                "display": "Shortness of breath"
              }
            ]
          }
        ],
        "bodySite": [
          {
            "coding": [
              {
                "system": "http://snomed.info/sct",
                "code": "51185008",
                "display": "Thoracic structure"
              }
            ]
          }
        ]
      }';
    }
    if ($tipe == 'specimen') {
      $radiologi = '{ 
        "resourceType": "Specimen",
        "identifier": [
          {
            "system": "http://sys-ids.kemkes.go.id/specimen/8529d474-30e0-4fee-81a4-7a5234003a1b",
            "value": "SPEC-RAD-20231214-001"
          }
        ],
        "status": "available",
        "type": {
          "coding": [
            {
              "system": "http://snomed.info/sct",
              "code": "122555007",
              "display": "Venous blood specimen"
            }
          ]
        },
        "subject": {
          "reference": "Patient/P02029555482",
          "display": "Budi Santoso"
        },
        "receivedTime": "2023-12-14T07:30:00+07:00",
        "request": [
          {
            "reference": "ServiceRequest/RAD-20231214-001"
          }
        ],
        "collection": {
          "collector": {
            "reference": "Practitioner/N10000003",
            "display": "Ns. Maria Sari"
          },
          "collectedDateTime": "2023-12-14T07:30:00+07:00",
          "method": {
            "coding": [
              {
                "system": "http://snomed.info/sct",
                "code": "28520004",
                "display": "Venipuncture"
              }
            ]
          },
          "bodySite": {
            "coding": [
              {
                "system": "http://snomed.info/sct",
                "code": "368208006",
                "display": "Left upper arm structure"
              }
            ]
          }
        },
        "processing": [
          {
            "description": "Persiapan kontras untuk CT Scan Abdomen",
            "procedure": {
              "coding": [
                {
                  "system": "http://snomed.info/sct",
                  "code": "432102000",
                  "display": "Administration of substance"
                }
              ]
            },
            "additive": [
              {
                "display": "Kontras Iodine"
              }
            ],
            "timeDateTime": "2023-12-14T08:00:00+07:00"
          }
        ],
        "container": [
          {
            "identifier": [
              {
                "value": "CONT-001"
              }
            ],
            "description": "Syringe untuk kontras",
            "type": {
              "coding": [
                {
                  "system": "http://snomed.info/sct",
                  "code": "706041008",
                  "display": "Syringe"
                }
              ]
            },
            "capacity": {
              "value": 50,
              "unit": "mL",
              "system": "http://unitsofmeasure.org",
              "code": "mL"
            },
            "specimenQuantity": {
              "value": 30,
              "unit": "mL", 
              "system": "http://unitsofmeasure.org",
              "code": "mL"
            }
          }
        ],
        "note": [
          {
            "text": "Pasien telah menjalani persiapan puasa 6 jam sebelum pemeriksaan CT Scan dengan kontras"
          }
        ]
      }';
    }
    if ($tipe == 'observation') {
      $radiologi = '{ 
        "resourceType": "Observation",
        "identifier": [
          {
            "system": "http://sys-ids.kemkes.go.id/observation/8529d474-30e0-4fee-81a4-7a5234003a1b",
            "value": "RAD-OBS-20231214-001"
          }
        ],
        "status": "final",
        "category": [
          {
            "coding": [
              {
                "system": "http://terminology.hl7.org/CodeSystem/observation-category",
                "code": "imaging",
                "display": "Imaging"
              }
            ]
          }
        ],
        "code": {
          "coding": [
            {
              "system": "http://loinc.org",
              "code": "30746-2",
              "display": "Chest X-ray"
            }
          ]
        },
        "subject": {
          "reference": "Patient/P02029555482",
          "display": "Budi Santoso"
        },
        "encounter": {
          "reference": "Encounter/ENC-20231214-RAD-001"
        },
        "effectiveDateTime": "2023-12-14T08:30:00+07:00",
        "performer": [
          {
            "reference": "Practitioner/N10000002",
            "display": "dr. Sarah Dewi, Sp.Rad"
          }
        ],
        "valueString": "Thoraks dalam batas normal",
        "bodySite": {
          "coding": [
            {
              "system": "http://snomed.info/sct",
              "code": "51185008",
              "display": "Thoracic structure"
            }
          ]
        },
        "method": {
          "coding": [
            {
              "system": "http://snomed.info/sct",
              "code": "168537006",
              "display": "Plain radiography"
            }
          ]
        }
      }';
    }
    if ($tipe == 'diagnostic') {
      $radiologi = '{        
        "resourceType": "DiagnosticReport",
        "identifier": [
          {
            "system": "http://sys-ids.kemkes.go.id/diagnosticreport/8529d474-30e0-4fee-81a4-7a5234003a1b",
            "value": "RAD-REPORT-20231214-001"
          }
        ],
        "status": "final",
        "category": [
          {
            "coding": [
              {
                "system": "http://terminology.hl7.org/CodeSystem/v2-0074",
                "code": "RAD",
                "display": "Radiology"
              }
            ]
          }
        ],
        "code": {
          "coding": [
            {
              "system": "http://loinc.org",
              "code": "30746-2",
              "display": "Chest X-ray"
            }
          ]
        },
        "subject": {
          "reference": "Patient/P02029555482",
          "display": "Budi Santoso"
        },
        "encounter": {
          "reference": "Encounter/ENC-20231214-RAD-001"
        },
        "effectiveDateTime": "2023-12-14T08:30:00+07:00",
        "issued": "2023-12-14T10:15:00+07:00",
        "performer": [
          {
            "reference": "Practitioner/N10000002",
            "display": "dr. Sarah Dewi, Sp.Rad"
          },
          {
            "reference": "Organization/8529d474-30e0-4fee-81a4-7a5234003a1b",
            "display": "RSUD H. Damanhuri"
          }
        ],
        "basedOn": [
          {
            "reference": "ServiceRequest/RAD-20231214-001"
          }
        ],
        "conclusion": "Gambaran thoraks dalam batas normal. Tidak tampak infiltrat, konsolidasi, atau efusi pleura. Corakan bronkovaskular normal.",
        "conclusionCode": [
          {
            "coding": [
              {
                "system": "http://hl7.org/fhir/sid/icd-10",
                "code": "Z01.6",
                "display": "Radiological examination, not elsewhere classified"
              }
            ]
          }
        ]
      }';
    };

    echo $this->draw('radiology.html', ['pesan' => $pesan, 'response' => $response]);
    exit();
  }

  public function getCarePlan($no_rawat)
  {

    $zonawaktu = '+07:00';
    if ($this->settings->get('satu_sehat.zonawaktu') == 'WITA') {
      $zonawaktu = '+08:00';
    }
    if ($this->settings->get('satu_sehat.zonawaktu') == 'WIT') {
      $zonawaktu = '+09:00';
    }


    $no_rawat = revertNoRawat($no_rawat);
    $pesan = '';
    $response = '';

    echo $this->draw('careplan.html', ['pesan' => $pesan, 'response' => $response]);
    exit();
  }

  public function getSettings()
  {
    return $this->draw('settings.html', ['satu_sehat' => $this->settings->get('satu_sehat'), 'mapping_lokasi' => $this->db('mlite_satu_sehat_lokasi')->toArray(),'bidang' => $this->db('bidang')->toArray()]);
  }

  public function postSaveSettings()
  {
    foreach ($_POST['satu_sehat'] as $key => $val) {
      $this->settings('satu_sehat', $key, $val);
    }

    $this->notify('success', 'Pengaturan telah disimpan');
    redirect(url([ADMIN, 'satu_sehat', 'settings']));
  }

  public function anyPraktisi()
  {
    $response = [];
    if (isset($_POST['nik_dokter']) && $_POST['nik_dokter'] != '') {
      $response = json_decode($this->getPractitioner($_POST['nik_dokter']));
    }
    return $this->draw('praktisi.html', ['response' => json_encode($response, JSON_PRETTY_PRINT)]);
  }

  public function anyPasien()
  {
    $response = [];
    if (isset($_POST['nik_pasien']) && $_POST['nik_pasien'] != '') {
      $response = json_decode($this->getPatient($_POST['nik_pasien']));
    }
    return $this->draw('pasien.html', ['response' => json_encode($response, JSON_PRETTY_PRINT)]);
  }

  public function getDepartemen()
  {
    $poli = $this->db('poliklinik')->where('status', '1')->toArray();
    $mlite_satset = $this->db('mlite_satu_sehat_departemen')->toArray();
    $satu_sehat = [];
    foreach ($mlite_satset as $value) {
      $nama = $this->core->getDepartemenInfo($value['dep_id']);
      if ($nama == '') {
        $nama = $this->core->getPoliklinikInfo('nm_poli', $value['dep_id']);
      }
      $value['nama'] = $nama;
      $satu_sehat[] = $value;
    }
    return $this->draw('departemen.html', ['departemen' => $this->db('departemen')->toArray(), 'poli' => $poli, 'satu_sehat_departemen' => $satu_sehat]);
  }

  public function getLokasi()
  {
    $poliklinik = $this->db('poliklinik')->select([
      'kode' => 'kd_poli',
      'nama' => 'nm_poli'
    ])->toArray();
    $bangsal = $this->db('bangsal')->select([
      'kode' => 'kd_bangsal',
      'nama' => 'nm_bangsal'
    ])->toArray();
    $lokasi = array_merge($poliklinik, $bangsal);
    return $this->draw('lokasi.html', [
      'lokasi' => $lokasi,
      'satu_sehat_departemen' => $this->db('mlite_satu_sehat_departemen')->join('departemen', 'departemen.dep_id=mlite_satu_sehat_departemen.dep_id')->toArray(),
      'satu_sehat_lokasi' => $this->db('mlite_satu_sehat_lokasi')
        ->join('mlite_satu_sehat_departemen', 'mlite_satu_sehat_departemen.id_organisasi_satusehat=mlite_satu_sehat_lokasi.id_organisasi_satusehat')
        ->join('departemen', 'departemen.dep_id=mlite_satu_sehat_departemen.dep_id')
        ->toArray()
    ]);
  }

  public function postSaveLokasi()
  {
    if (isset($_POST['simpan'])) {

      $mlite_satu_sehat_departemen = $this->db('mlite_satu_sehat_departemen')->where('dep_id', $_POST['dep_id'])->oneArray();
      $id_organisasi_satusehat = $mlite_satu_sehat_departemen['id_organisasi_satusehat'];
      $id_lokasi_satusehat = json_decode($this->getLocation($_POST['kode'], $id_organisasi_satusehat))->id;

      if ($id_lokasi_satusehat != '') {
        $query = $this->db('mlite_satu_sehat_lokasi')->save(
          [
            'kode' => $_POST['kode'],
            'lokasi' => $_POST['lokasi'],
            'id_organisasi_satusehat' => $id_organisasi_satusehat,
            'id_lokasi_satusehat' => $id_lokasi_satusehat,
            'longitude' => $this->settings->get('satu_sehat.longitude'),
            'latitude' => $this->settings->get('satu_sehat.latitude'),
            'altitude' => '0'
          ]
        );
        if ($query) {
          $this->notify('success', 'Mapping lokasi telah disimpan');
        } else {
          $this->notify('danger', 'Mapping lokasi gagal disimpan');
        }
      }
    }

    if (isset($_POST['update'])) {
      $mlite_satu_sehat_departemen = $this->db('mlite_satu_sehat_departemen')->where('dep_id', $_POST['dep_id'])->oneArray();
      $id_organisasi_satusehat = $mlite_satu_sehat_departemen['id_organisasi_satusehat'];

      $query = $this->db('mlite_satu_sehat_lokasi')
        ->where('id_lokasi_satusehat', $_POST['id_lokasi_satusehat'])
        ->save(
          [
            'kode' => $_POST['kode'],
            'lokasi' => $_POST['lokasi'],
            'id_organisasi_satusehat' => $id_organisasi_satusehat,
            'longitude' => $this->settings->get('satu_sehat.longitude'),
            'latitude' => $this->settings->get('satu_sehat.latitude'),
            'altitude' => '0'
          ]
        );
      if ($query) {
        $this->notify('success', 'Mapping lokasi telah disimpan');
      }
    }

    if (isset($_POST['hapus'])) {
      $query = $this->db('mlite_satu_sehat_lokasi')
        ->where('id_lokasi_satusehat', $_POST['id_lokasi_satusehat'])
        ->delete();
      if ($query) {
        $this->notify('success', 'Mapping lokasi telah dihapus');
      }
    }

    redirect(url([ADMIN, 'satu_sehat', 'lokasi']));
  }

  public function getMappingPraktisi()
  {
    $this->_addHeaderFiles();
    $apotek_setting = $this->settings->get('satu_sehat.praktisiapotek');
    $lab_setting = $this->settings->get('satu_sehat.praktisilab');
    $mapping_praktisi = $this->db('mlite_satu_sehat_mapping_praktisi')
      ->join('dokter', 'dokter.kd_dokter=mlite_satu_sehat_mapping_praktisi.kd_dokter')
      ->toArray();
    $mapping_praktisi_apoteker = $this->db('mlite_satu_sehat_mapping_praktisi')
      ->select('pegawai.nama as nm_dokter, mlite_satu_sehat_mapping_praktisi.*')
      ->join('pegawai', 'pegawai.nik=mlite_satu_sehat_mapping_praktisi.kd_dokter')->toArray();

    $combined = array_merge($mapping_praktisi, $mapping_praktisi_apoteker);
    $unique = [];
    $seen = [];

    foreach ($combined as $item) {
      $key = $item['kd_dokter'];
      if (!isset($seen[$key])) {
        $seen[$key] = true;
        $unique[] = $item;
      }
    }

    $dokter = $this->db('dokter')->where('status', '1')->toArray();
    $apoteker = $this->db('pegawai')->where('stts_aktif', 'AKTIF')->where('bidang', $apotek_setting)->toArray();
    $lab = $this->db('pegawai')->where('stts_aktif', 'AKTIF')->where('bidang', $lab_setting)->toArray();
    $lab_apoteker = array_merge($lab,$apoteker);
    return $this->draw('mapping.praktisi.html', ['mapping_praktisi' => $unique, 'dokter' => $dokter, 'apoteker' => $lab_apoteker]);
  }

  public function postSaveMappingPraktisi()
  {
    if (isset($_POST['simpan'])) {
      $kd_dokter = (is_null($_POST['dokter'])) ? $_POST['medis'] : $_POST['dokter'];
      $nik = $this->core->getPegawaiInfo('no_ktp', $kd_dokter);
      $bidang = $this->core->getPegawaiInfo('bidang', $kd_dokter);
      $send_json = json_decode($this->getPractitioner($nik))->entry[0]->resource->id;
      $apotek_setting = $this->settings->get('satu_sehat.praktisiapotek');
      $lab_setting = $this->settings->get('satu_sehat.praktisilab');
      $jenis_praktisi = 'Dokter';
      if ($bidang == $apotek_setting) {
        $jenis_praktisi = 'Apoteker';
      }
      if ($bidang == $lab_setting) {
        $jenis_praktisi = 'Laboratorium';
      }
      if ($send_json != '') {
        $query = $this->db('mlite_satu_sehat_mapping_praktisi')->save(
          [
            'practitioner_id' => $send_json,
            'kd_dokter' => $_POST['dokter'],
            'jenis_praktisi' => $jenis_praktisi,
          ]
        );
        if ($query) {
          $this->notify('success', 'Mapping praktisi telah disimpan ');
        } else {
          $this->notify('danger', 'Mapping depapraktisirtemen gagal disimpan');
        }
      }
    }

    if (isset($_POST['hapus'])) {
      $query = $this->db('mlite_satu_sehat_mapping_praktisi')
        ->where('kd_dokter', $_POST['dokter'])
        ->delete();
      if ($query) {
        $this->notify('success', 'Mapping praktisi telah dihapus ');
      }
    }

    redirect(url([ADMIN, 'satu_sehat', 'mappingpraktisi']));
  }

  public function getCodeDrugForm($keyword)
  {
    $url = 'https://terminology.hl7.org/6.4.0/CodeSystem-v3-orderableDrugForm.json';

    // Get JSON data from the URL
    $json = file_get_contents($url);

    // Decode JSON to PHP array
    $data = json_decode($json, true);

    // Access values
    foreach ($data['concept'] as $value) {
      if ($value['display'] === $keyword) {
        return $value['code'];
      }
    }
  }

  public function searchObat($keyword)
  {

    $url = $this->settings->get('satu_sehat.authurl');
    $parsed = parse_url($url);
    $baseUrl = $parsed['scheme'] . '://' . $parsed['host'];

    if (!json_decode($this->getToken())->access_token) {
      return ['error' => 'Gagal mendapatkan access token'];
    }

    $ch = curl_init();
    curl_setopt_array($ch, [
      CURLOPT_URL => $baseUrl . '/kfa-v2/products/all?page=1&size=5&product_type=farmasi&keyword=' . urlencode($keyword),
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . json_decode($this->getToken())->access_token,
        'Accept: application/json'
      ]
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
  }

  public function searchIdentifierObat($keyword)
  {

    $url = $this->settings->get('satu_sehat.authurl');
    $parsed = parse_url($url);
    $baseUrl = $parsed['scheme'] . '://' . $parsed['host'];

    if (!json_decode($this->getToken())->access_token) {
      return ['error' => 'Gagal mendapatkan access token'];
    }

    $ch = curl_init();
    curl_setopt_array($ch, [
      CURLOPT_URL => $baseUrl . '/kfa-v2/products?identifier=kfa&code=' . urlencode($keyword),
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . json_decode($this->getToken())->access_token,
        'Accept: application/json'
      ]
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
  }

  public function getMappingObat()
  {
    $this->_addHeaderFiles();
    $databarang = $this->db('databarang')->where('status', '1')->toArray();
    $mapping_obat = $this->db('mlite_satu_sehat_mapping_obat')->toArray();
    return $this->draw('mapping.obat.html', ['databarang' => $databarang, 'mapping_obat_satu_sehat' => $mapping_obat]);
  }

  public function getMappingObatSearch()
  {
    echo json_encode($this->searchObat($_GET['keyword']));
    exit();
  }

  public function regexZatAktif($string)
  {
    if (preg_match('/([\d\.]+)\s*([^\d\s]+)/', $string, $matches)) {
      return [
        'value' => $matches[1],
        'unit' => $matches[2],
      ];
    }

    return null;
  }

  public function postSaveObat()
  {
    if (isset($_POST['simpan'])) {

      $cari_obat = $this->searchIdentifierObat($_POST['select_kfa']);
      $get_drug_form = $this->getCodeDrugForm($cari_obat['result']['uom']['name']);
      $nama_satuan_den = $cari_obat['result']['uom']['name'];
      if ($get_drug_form == '') {
        $get_drug_form = $this->getCodeDrugForm(ucfirst($cari_obat['result']['rute_pemberian']['code']));
        $nama_satuan_den = ucfirst($cari_obat['result']['rute_pemberian']['code']);
      }
      $numerator_value = $this->regexZatAktif($cari_obat['result']['active_ingredients'][0]['kekuatan_zat_aktif']);

      $query = $this->db('mlite_satu_sehat_mapping_obat')->save(
        [
          'kode_brng' => $_POST['kode_brng'],
          'kode_kfa' => $_POST['select_kfa'],
          'nama_kfa' => $cari_obat['result']['name'],
          'kode_bahan' => $cari_obat['result']['active_ingredients'][0]['kfa_code'],
          'nama_bahan' => $cari_obat['result']['active_ingredients'][0]['zat_aktif'],
          'numerator' => $numerator_value['value'],
          'satuan_num' => $numerator_value['unit'],
          'denominator' => 1,
          'satuan_den' => $get_drug_form,
          'nama_satuan_den' => $nama_satuan_den,
          'kode_sediaan' => $cari_obat['result']['dosage_form']['code'],
          'nama_sediaan' => $cari_obat['result']['dosage_form']['name'],
          'kode_route' => $cari_obat['result']['rute_pemberian']['code'],
          'nama_route' => $cari_obat['result']['rute_pemberian']['name'],
          'type' => $_POST['type'],
        ]
      );
      if ($query) {
        $this->notify('success', 'Mapping obat telah disimpan');
      } else {
        $this->notify('danger', 'Mapping obat gagal disimpan');
      }
    }

    // if (isset($_POST['update'])) {
    //   $query = $this->db('mlite_satu_sehat_mapping_obat')
    //     ->where('kode_brng', $_POST['kode_brng'])
    //     ->save(
    //       [
    //         'obat_code' => $_POST['select_kfa'],
    //         'obat_system' => '',
    //         'obat_display' => $cari_obat['result']['name'],
    //         'type' => $_POST['type']  
    //       ]
    //     );
    //   if ($query) {
    //     $this->notify('success', 'Mapping obat telah disimpan');
    //   }
    // }

    if (isset($_POST['hapus'])) {
      $query = $this->db('mlite_satu_sehat_mapping_obat')
        ->where('kode_brng', $_POST['kode_brng'])
        ->delete();
      if ($query) {
        $this->notify('success', 'Mapping obat telah dihapus');
      }
    }

    redirect(url([ADMIN, 'satu_sehat', 'mappingobat']));
  }

  public function getBunban($periode = '')
  {
    $this->_addHeaderFiles();
    if ($periode == '') {
      $periode = date('Y-m-d'); 
    }
    if (isset($periode) && $periode != '') {
      $periode = $periode;
    }
    $data_response = [];
    $url = url([ADMIN,'satu_sehat','bunban',$periode]);
    $mainMenu = url([ADMIN,'satu_sehat','manage']);
    echo 'Tanggal Yang Dipilih : '.$periode;
    echo '<br>
            <label for="tanggal">Pilih Tanggal:</label>
            <input type="date" id="tanggal" name="tanggal" required>
            <button type="button" onclick="redirectToDate()">Tampilkan</button>
      

          <script>
            function redirectToDate() {
            let currentLocation = window.location;
            console.log(currentLocation.origin + currentLocation.pathname);
            const params = new URLSearchParams(window.location.search);
             const t = params.get("t");
             console.log(t);
            var baseURL = currentLocation.origin + currentLocation.pathname;
              const dateValue = document.getElementById("tanggal").value;
              if (dateValue) {
                console.log("Redirecting to: " + baseURL + "/satu_sehat/bunban/" + dateValue + "?t=" + t);
                window.location.href = baseURL + "/" + dateValue + "?t=" + t;
              }
              return false;
            }
          </script>';
          echo '<br>';
    $cekbundle = $this->db('mlite_satu_sehat_log')->where('tgl_registrasi',$periode)->where('response','Belum')->limit(5)->toArray();
    if (!$cekbundle) {
      $limit =5;
      $offset = 0;
      if (isset($_GET['offset']) && $_GET['offset'] != '') {
        $offset = $_GET['offset'];
      }
      $result = $this->db('reg_periksa')
          ->select('no_rawat')
          ->where('reg_periksa.tgl_registrasi', $periode)
          ->where('stts', '!=', 'Batal')
          ->where('kd_poli', '!=', 'IGD01')
          ->where('status_lanjut', 'Ralan')
          ->limit($limit)
          ->offset($offset)
          ->toArray();
      $no = 1;
      foreach ($result as $row) {
          $no_rawat = $row['no_rawat'];
          $cek_data = $this->db('mlite_satu_sehat_log')->where('no_rawat' ,$no_rawat)->oneArray();
          if (!$cek_data) {
            $this->db('mlite_satu_sehat_log')->save([
              'no_rawat' => $no_rawat,'tgl_registrasi' => $periode,'response' => 'Belum'
            ]);
            echo $no_rawat."<br>";
            echo "Menyimpan data <br>";
            echo '==========================================<br>';
          } else {
            echo $no."Sudah Ada<br>";
            echo '==========================================<br>';
          }
        $no++;
      }
      $offset += $limit;
      echo '<script type="text/javascript">
          document.addEventListener("DOMContentLoaded", function(event) { 
          let currentLocation = window.location;
          const params = new URLSearchParams(window.location.search);
          const t = params.get("t");
          let offset = null;
          let nextoffset = 5;
          if (!params.has("offset")) {
            nextoffset = offset + 5;
          } else {
            nextoffset = nextoffset + Number(params.get("offset"));
          }
          var baseURL = currentLocation.origin + currentLocation.pathname;
          const dateValue = document.getElementById("tanggal").value;
            var auto_refresh = setInterval(
              function ()
              {
                console.log(baseURL + "?offset="+ nextoffset +"&t=" + t);
                window.location.href = baseURL + "?offset="+ nextoffset +"&t=" + t;
              }, 30000); // refresh every 10000 milliseconds
          });
          </script>';
    } else {
      foreach ($cekbundle as $row) {
        $no_rawat = $row['no_rawat'];
        $mlite_satu_sehat_response = $this->db('mlite_satu_sehat_response')->where('no_rawat', $no_rawat)->oneArray();
        $row['response'] = null;
        if ($mlite_satu_sehat_response['id_encounter'] != '') {
          $row['response'] = $mlite_satu_sehat_response['id_encounter'];
          $this->db('mlite_satu_sehat_log')->where('no_rawat',$no_rawat)->update([
            'response' => $mlite_satu_sehat_response['id_encounter']
          ]);
        } else {
          $row['response'] = $this->getEncounterBundle(convertNorawat($no_rawat),'all');
          $response = json_decode(json_encode($row['response']),true);
          if($response['issue'][0]['severity'] == 'error'){
            $this->db('mlite_satu_sehat_log')->where('no_rawat',$no_rawat)->update([
              'response' => $response['issue'][0]['details']['text']
            ]);
          };
          if($response['entry'][0]['response']['status'] == '201 Created'){
            $this->db('mlite_satu_sehat_log')->where('no_rawat',$no_rawat)->update([
              'response' => $response['entry'][0]['response']['resourceID']
            ]);
          }
        }
        $row['id_encounter'] = isset_or($mlite_satu_sehat_response['id_encounter'], '');
        $row['id_condition'] = isset_or($mlite_satu_sehat_response['id_condition'], '');
        $row['id_observation_ttvtensi'] = isset_or($mlite_satu_sehat_response['id_observation_ttvtensi'], '');
        $row['id_observation_ttvnadi'] = isset_or($mlite_satu_sehat_response['id_observation_ttvnadi'], '');
        $row['id_observation_ttvrespirasi'] = isset_or($mlite_satu_sehat_response['id_observation_ttvrespirasi'], '');
        $row['id_observation_ttvsuhu'] = isset_or($mlite_satu_sehat_response['id_observation_ttvsuhu'], '');
        $row['id_observation_ttvspo2'] = isset_or($mlite_satu_sehat_response['id_observation_ttvspo2'], '');
        $row['id_observation_ttvgcs'] = isset_or($mlite_satu_sehat_response['id_observation_ttvgcs'], '');
        $row['id_observation_ttvtinggi'] = isset_or($mlite_satu_sehat_response['id_observation_ttvtinggi'], '');
        $row['id_observation_ttvberat'] = isset_or($mlite_satu_sehat_response['id_observation_ttvberat'], '');
        $row['id_observation_ttvperut'] = isset_or($mlite_satu_sehat_response['id_observation_ttvperut'], '');
        $row['id_observation_ttvkesadaran'] = isset_or($mlite_satu_sehat_response['id_observation_ttvkesadaran'], '');
        $row['id_procedure'] = isset_or($mlite_satu_sehat_response['id_procedure'], '');
        $row['id_composition'] = isset_or($mlite_satu_sehat_response['id_composition'], '');
        $data_response[] = $row;
      }
      $json = json_encode($data_response);
      $return = json_decode($json,true);
      foreach ($return as $value) {
        echo '<br>';
        echo 'Encounter : '.$value['id_encounter'].'<br>';
        echo 'Condition : '.$value['id_condition'].'<br>';
        echo 'Procedure : '.$value['id_procedure'].'<br>';
        echo 'Nadi : '.$value['id_observation_ttvnadi'].'<br>';
        echo 'Respirasi : '.$value['id_observation_ttvrespirasi'].'<br>';
        echo 'Suhu : '.$value['id_observation_ttvsuhu'].'<br>';
        // echo 'Poli : '.$this->core->getPoliklinikInfo('nm_poli',$value['kd_poli']);
        echo '<br>';
        echo 'Nama Pasien : '.$this->core->getPasienInfo('nm_pasien',$this->core->getRegPeriksaInfo('no_rkm_medis',$value['no_rawat']));
        echo '<br>';
        echo 'No Rawat : '.$value['no_rawat'];
        echo '<br>';
        echo (json_encode($value['response']) == null) ? '' : json_encode($value['response']);
        echo '<br>';
        echo '==========================================';
      }
      echo '<br>';
      echo '<br>';
      echo '<script type="text/javascript">
      document.addEventListener("DOMContentLoaded", function(event) { 
        let currentLocation = window.location;
          const params = new URLSearchParams(window.location.search);
          var baseURL = currentLocation.origin + currentLocation.pathname;
          const dateValue = document.getElementById("tanggal").value;
          const t = params.get("t");
          let offset = null;
          let nextoffset = 5;
          let url = null
          if (!params.has("offset")) {
            nextoffset = offset + 5;
            url = baseURL + "?offset="+ nextoffset +"&t=" + t;
          } else {
            nextoffset = params.get("offset");
            url = baseURL + "?offset="+ nextoffset +"&t=" + t;
          }
          
        var auto_refresh = setInterval(
          function ()
          {
            console.log(url);
            window.location.href = url;
          }, 25000); // refresh every 10000 milliseconds
      });
      </script>';
    }
    echo '<a href="'.$mainMenu.'" class="btn btn-primary"> Menu Utama</a>';
    exit();
  }

  public function getResponse()
  {
    $this->_addHeaderFiles();
    return $this->draw('response.html');
  }

  public function postResponseApi()
  {
    $this->_addHeaderFiles();
    $periode = date('Y-m-d');
    if (isset($_GET['periode']) && $_GET['periode'] != '') {
        $periode = $_GET['periode'];
    }

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $periode)) {
        $periode = date('Y-m-d'); // fallback
    }

    $start  = intval($_POST['start'] ?? 0);
    $length = intval($_POST['length'] ?? 10);
    $draw   = intval($_POST['draw'] ?? 1); // untuk datatables tracking

    // Hitung total records tanpa filter (total di DB pada tanggal itu)
    // $totalRecords = $this->db('reg_periksa')
    //     ->where('tgl_registrasi', $periode)
    //     ->where('stts', '!=', 'Batal')
    //     ->where('status_lanjut', 'Ralan')
    //     ->count();    

    $data_response = [];
    $query = $this->db('reg_periksa')
      ->where('reg_periksa.tgl_registrasi', $periode)
      ->where('stts', '!=', 'Batal')
      ->where('status_lanjut', 'Ralan')
      ->limit($length)
      ->offset($start)
      ->toArray();

    $total = $this->db('reg_periksa')->select('no_rawat')
      ->where('reg_periksa.tgl_registrasi', $periode)
      ->where('stts', '!=', 'Batal')
      ->where('status_lanjut', 'Ralan')
      ->count();

    foreach ($query as $row) {

      $mlite_satu_sehat_response = $this->db('mlite_satu_sehat_response')->where('no_rawat', $row['no_rawat'])->oneArray();
      $row['no_rawat_converted'] = convertNoRawat($row['no_rawat']);
      $row['no_ktp_pasien'] = $this->core->getPasienInfo('no_ktp', $row['no_rkm_medis']);
      $row['nm_pasien'] = $this->core->getPasienInfo('nm_pasien', $row['no_rkm_medis']);
      $row['nm_dokter'] = $this->core->getDokterInfo('nm_dokter', $row['kd_dokter']);
      $row['no_ktp_dokter'] = $this->core->getPegawaiInfo('no_ktp', $row['kd_dokter']);
      $row['nm_poli'] = $this->core->getPoliklinikInfo('nm_poli', $row['kd_poli']);

      $praktisi_id = $this->db('mlite_satu_sehat_mapping_praktisi')->where('kd_dokter', $row['kd_dokter'])->oneArray();
      $row['praktisi_id'] = $praktisi_id['practitioner_id'];

      $mlite_billing = $this->db('mlite_billing')->where('no_rawat', $row['no_rawat'])->oneArray();
      if ($this->settings->get('satu_sehat.billing') == 'khanza') {
        $mlite_billing = $this->db('nota_jalan')->select([
          'tgl_billing' => 'tanggal'
        ])
          ->where('no_rawat', $row['no_rawat'])
          ->oneArray();
        if ($status_lanjut == 'Ranap') {
          $mlite_billing = $this->db('nota_inap')->select([
            'tgl_billing' => 'tanggal'
          ])
            ->where('no_rawat', $row['no_rawat'])
            ->oneArray();
        }
      }
      $row['tgl_pulang'] = isset_or($mlite_billing['tgl_billing'], '');

      if ($row['status_lanjut'] == 'Ranap') {
        $row['kd_kamar'] = $this->core->getKamarInapInfo('kd_kamar', $row['no_rawat']);
        $row['kd_poli'] = $this->core->getKamarInfo('kd_bangsal', $row['kd_kamar']);
        $row['nm_poli'] = $this->core->getBangsalInfo('nm_bangsal', $row['kd_poli']);
      }

      $mlite_satu_sehat_lokasi = $this->db('mlite_satu_sehat_lokasi')->where('kode', $row['kd_poli'])->oneArray();
      $row['id_organisasi'] = isset_or($mlite_satu_sehat_lokasi['id_organisasi_satusehat'], '');
      $row['id_lokasi'] = isset_or($mlite_satu_sehat_lokasi['id_lokasi_satusehat'], '');

      $row['pemeriksaan'] = $this->db('pemeriksaan_ralan')
        ->where('no_rawat', $row['no_rawat'])
        ->limit(1)
        ->desc('tgl_perawatan')
        ->oneArray();

      if ($row['status_lanjut'] == 'Ranap') {
        $row['pemeriksaan'] = $this->db('pemeriksaan_ranap')
          ->where('no_rawat', $row['no_rawat'])
          ->limit(1)
          ->desc('tgl_perawatan')
          ->oneArray();
      }

      $row['diagnosa_pasien'] = $this->db('diagnosa_pasien')
        ->join('penyakit', 'penyakit.kd_penyakit=diagnosa_pasien.kd_penyakit')
        ->where('no_rawat', $row['no_rawat'])
        ->where('diagnosa_pasien.status', $row['status_lanjut'])
        ->where('prioritas', '1')
        ->oneArray();

      $row['prosedur_pasien'] = $this->db('prosedur_pasien')
        ->join('icd9', 'icd9.kode=prosedur_pasien.kode')
        ->where('no_rawat', $row['no_rawat'])
        ->where('prosedur_pasien.status', $row['status_lanjut'])
        ->where('prioritas', '1')
        ->oneArray();

      // $row['adime_gizi'] = $this->db('catatan_adime_gizi')
      //   ->where('no_rawat', $row['no_rawat'])->oneArray();

      $row['immunization'] = $this->db('resep_obat')
        ->join('resep_dokter','resep_dokter.no_resep=resep_obat.no_resep')
        ->join('mlite_satu_sehat_mapping_obat','mlite_satu_sehat_mapping_obat.kode_brng=resep_dokter.kode_brng')
        ->where('mlite_satu_sehat_mapping_obat.type', 'vaksin')
        ->where('no_rawat', $row['no_rawat'])->oneArray();

      $row['clinical_impression'] = isset_or($row['pemeriksaan']['penilaian'], '');

      $row['medications'] = $this->db('resep_obat')
        ->join('resep_dokter', 'resep_dokter.no_resep=resep_obat.no_resep')
        ->where('no_rawat', $row['no_rawat'])->oneArray();

      $row['medication_request'] = isset_or($row['medications']['tgl_peresepan'], '');

      $row['medication_dispense'] = isset_or($row['medications']['tgl_perawatan'], '');

      $row['permintaan_radiologi'] = $this->db('permintaan_radiologi')
        ->where('no_rawat', $row['no_rawat'])
        ->oneArray();

      $row['service_request_radiologi'] = isset_or($row['permintaan_radiologi']['tgl_permintaan'], '');

      $row['specimen_radiologi'] = isset_or($row['permintaan_radiologi']['tgl_sampel'], '');

      $row['observation_radiologi'] = isset_or($row['permintaan_radiologi']['tgl_hasil'], '');

      $row['diagnostic_report_radiologi'] = $row['permintaan_radiologi'];

      $row['permintaan_lab'] = $this->db('permintaan_lab')
        ->where('no_rawat', $row['no_rawat'])
        ->oneArray();

      $row['service_request_lab_pk'] = isset_or($row['permintaan_lab']['tgl_permintaan'], '');

      $row['service_request_lab_pa'] = isset_or($row['permintaan_lab']['tgl_permintaan'], '');

      $row['service_request_lab_mb'] = isset_or($row['permintaan_lab']['tgl_permintaan'], '');

      $row['specimen_lab_pk'] = $row['permintaan_lab'];

      $row['specimen_lab_pa'] = $row['permintaan_lab'];

      $row['specimen_lab_mb'] = $row['permintaan_lab'];

      $row['observation_lab_pk'] = $row['permintaan_lab'];

      $row['observation_lab_pa'] = $row['permintaan_lab'];

      $row['observation_lab_mb'] = $row['permintaan_lab'];

      $row['diagnostic_report_lab_pk'] = $row['permintaan_lab'];

      $row['diagnostic_report_lab_pa'] = $row['permintaan_lab'];

      $row['diagnostic_report_lab_mb'] = $row['permintaan_lab'];

      $row['care_plan'] = $row['pemeriksaan'];

      $row['id_encounter'] = isset_or($mlite_satu_sehat_response['id_encounter'], '');
      $row['id_condition'] = isset_or($mlite_satu_sehat_response['id_condition'], '');
      $row['id_clinical_impression'] = isset_or($mlite_satu_sehat_response['id_clinical_impression'], '');
      $row['id_observation_ttvtensi'] = isset_or($mlite_satu_sehat_response['id_observation_ttvtensi'], '');
      $row['id_observation_ttvnadi'] = isset_or($mlite_satu_sehat_response['id_observation_ttvnadi'], '');
      $row['id_observation_ttvrespirasi'] = isset_or($mlite_satu_sehat_response['id_observation_ttvrespirasi'], '');
      $row['id_observation_ttvsuhu'] = isset_or($mlite_satu_sehat_response['id_observation_ttvsuhu'], '');
      $row['id_observation_ttvspo2'] = isset_or($mlite_satu_sehat_response['id_observation_ttvspo2'], '');
      $row['id_observation_ttvgcs'] = isset_or($mlite_satu_sehat_response['id_observation_ttvgcs'], '');
      $row['id_observation_ttvtinggi'] = isset_or($mlite_satu_sehat_response['id_observation_ttvtinggi'], '');
      $row['id_observation_ttvberat'] = isset_or($mlite_satu_sehat_response['id_observation_ttvberat'], '');
      $row['id_observation_ttvperut'] = isset_or($mlite_satu_sehat_response['id_observation_ttvperut'], '');
      $row['id_observation_ttvkesadaran'] = isset_or($mlite_satu_sehat_response['id_observation_ttvkesadaran'], '');
      $row['id_procedure'] = isset_or($mlite_satu_sehat_response['id_procedure'], '');
      $row['id_composition'] = isset_or($mlite_satu_sehat_response['id_composition'], '');
      $row['id_medication_for_request'] = isset_or($mlite_satu_sehat_response['id_medication_for_request'], '');
      $row['id_medication_request'] = isset_or($mlite_satu_sehat_response['id_medication_request'], '');
      $row['id_medication_for_dispense'] = isset_or($mlite_satu_sehat_response['id_medication_for_dispense'], '');
      $row['id_medication_dispense'] = isset_or($mlite_satu_sehat_response['id_medication_dispense'], '');
      $row['id_immunization'] = isset_or($mlite_satu_sehat_response['id_immunization'], '');
      $row['id_procedure'] = isset_or($mlite_satu_sehat_response['id_procedure'], '');
      $row['id_clinical_impression'] = isset_or($mlite_satu_sehat_response['id_clinical_impression'], '');
      $row['id_medication_request'] = isset_or($mlite_satu_sehat_response['id_medication_request'], '');
      $row['id_medication_dispense'] = isset_or($mlite_satu_sehat_response['id_medication_dispense'], '');
      $row['id_medication_statement'] = isset_or($mlite_satu_sehat_response['id_medication_statement'], '');
      $row['id_service_request_radiologi'] = isset_or($mlite_satu_sehat_response['id_service_request_radiologi'], '');
      $row['id_specimen_radiologi'] = isset_or($mlite_satu_sehat_response['id_specimen_radiologi'], '');
      $row['id_observation_radiologi'] = isset_or($mlite_satu_sehat_response['id_observation_radiologi'], '');
      $row['id_diagnostic_report_radiologi'] = isset_or($mlite_satu_sehat_response['id_diagnostic_report_radiologi'], '');
      $row['id_lab_pk_request'] = isset_or($mlite_satu_sehat_response['id_lab_pk_request'], '');
      $row['id_service_request_lab_pa'] = isset_or($mlite_satu_sehat_response['id_service_request_lab_pa'], '');
      $row['id_service_request_lab_mb'] = isset_or($mlite_satu_sehat_response['id_service_request_lab_mb'], '');
      $row['id_lab_pk_specimen'] = isset_or($mlite_satu_sehat_response['id_lab_pk_specimen'], '');
      $row['id_specimen_lab_pa'] = isset_or($mlite_satu_sehat_response['id_specimen_lab_pa'], '');
      $row['id_specimen_lab_mb'] = isset_or($mlite_satu_sehat_response['id_specimen_lab_mb'], '');
      $row['id_lab_pk_observation'] = isset_or($mlite_satu_sehat_response['id_lab_pk_observation'], '');
      $row['id_observation_lab_pa'] = isset_or($mlite_satu_sehat_response['id_observation_lab_pa'], '');
      $row['id_observation_lab_mb'] = isset_or($mlite_satu_sehat_response['id_observation_lab_mb'], '');
      $row['id_lab_pk_diagnostic'] = isset_or($mlite_satu_sehat_response['id_lab_pk_diagnostic'], '');
      $row['id_diagnostic_report_lab_pa'] = isset_or($mlite_satu_sehat_response['id_diagnostic_report_lab_pa'], '');
      $row['id_diagnostic_report_lab_mb'] = isset_or($mlite_satu_sehat_response['id_diagnostic_report_lab_mb'], '');
      $row['id_careplan'] = isset_or($mlite_satu_sehat_response['id_careplan'], '');
      $data_response[] = $row;
    }
    // Format hasil
    echo json_encode([
      "draw" => $draw,
      "recordsFiltered" => $total, // Jika tidak pakai pencarian server-side, ini bisa sama
      "recordsTotal" => $total,
      "data" => $data_response
    ]);
    exit();
  }

  public function gen_uuid()
  {
    return sprintf(
      '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
      // 32 bits for "time_low"
      mt_rand(0, 0xffff),
      mt_rand(0, 0xffff),

      // 16 bits for "time_mid"
      mt_rand(0, 0xffff),

      // 16 bits for "time_hi_and_version",
      // four most significant bits holds version number 4
      mt_rand(0, 0x0fff) | 0x4000,

      // 16 bits, 8 bits for "clk_seq_hi_res",
      // 8 bits for "clk_seq_low",
      // two most significant bits holds zero and one for variant DCE1.1
      mt_rand(0, 0x3fff) | 0x8000,

      // 48 bits for "node"
      mt_rand(0, 0xffff),
      mt_rand(0, 0xffff),
      mt_rand(0, 0xffff)
    );
  }

  public function ran_char()
  {
    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $randomChars = '';

    for ($i = 0; $i < 3; $i++) {
      $randomChars .= $characters[random_int(0, strlen($characters) - 1)];
    }
    return $randomChars;
  }

  public function convertTimeSatset($waktu)
  {
    $zonawaktu = '-7 hours';
    if ($this->settings->get('satu_sehat.zonawaktu') == 'WITA') {
      $zonawaktu = '-8 hours';
    }
    if ($this->settings->get('satu_sehat.zonawaktu') == 'WIT') {
      $zonawaktu = '-9 hours';
    }
    $DateTime = new \DateTime($waktu);
    $DateTime->modify($zonawaktu);
    return $DateTime->format("Y-m-d\TH:i:s");
  }

  public function getKyc()
  {

    $this->authurl = $this->settings->get('satu_sehat.authurl');
    $this->fhirurl = $this->settings->get('satu_sehat.fhirurl');
    $this->clientid = $this->settings->get('satu_sehat.clientid');
    $this->secretkey = $this->settings->get('satu_sehat.secretkey');
    $this->organizationid = $this->settings->get('satu_sehat.organizationid');

    $client_id = $this->clientid;
    $client_secret = $this->secretkey;
    $auth_url = $this->authurl;
    $api_url = 'https://api-satusehat.kemkes.go.id/kyc/v1/generate-url';
    $environment = 'production';

    // nama petugas/operator Fasilitas Pelayanan Kesehatan (Fasyankes) yang akan melakukan validasi
    $agent_name = $this->core->getUserInfo('fullname', null, true);

    // NIK petugas/operator Fasilitas Pelayanan Kesehatan (Fasyankes) yang akan melakukan validasi
    $agent_nik = $this->core->getPegawaiInfo('no_ktp',  $this->core->getUserInfo('username', null, true));

    // auth to satusehat
    $auth_result = $this->authenticateWithOAuth2($client_id, $client_secret, $auth_url);

    // Example usage
    $kyc = new Kyc;
    $json = $kyc->generateUrl($agent_name, $agent_nik, $auth_result, $api_url, $environment);

    $validation_web = json_decode($json, TRUE);

    $url = $validation_web["data"]["url"];

    return $this->draw('kyc.html', ['url' => $url]);
  }

  public function authenticateWithOAuth2($clientId, $clientSecret, $tokenUrl)
  {

    $curl = curl_init();
    $params = [
      'grant_type' => 'client_credentials',
      'client_id' => $clientId,
      'client_secret' => $clientSecret
    ];

    curl_setopt_array($curl, array(
      CURLOPT_URL => "$tokenUrl/accesstoken?grant_type=client_credentials",
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS => http_build_query($params),
      CURLOPT_HTTPHEADER => array(
        'Content-Type: application/x-www-form-urlencoded'
      ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    // echo $response;
    // Parse the response body
    $data = json_decode($response, true);

    // Return the access token
    return $data['access_token'];
  }

  private function _addHeaderFiles()
  {
    $this->core->addCSS(url('assets/css/dataTables.bootstrap.min.css'));
    $this->core->addCSS(url('assets/css/fixedColumns.dataTables.min.css'));
    $this->core->addJS(url('assets/jscripts/jquery.dataTables.min.js'));
    $this->core->addJS(url('assets/jscripts/dataTables.bootstrap.min.js'));
    $this->core->addJS(url('assets/jscripts/dataTables.fixedColumns.min.js'));
    $this->core->addCSS(url('assets/css/bootstrap-datetimepicker.css'));
    $this->core->addJS(url('assets/jscripts/moment-with-locales.js'));
    $this->core->addJS(url('assets/jscripts/bootstrap-datetimepicker.js'));
  }
}
