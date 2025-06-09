<?php

namespace Plugins\Satu_Sehat;

use SatuSehat\Model\Composition;
use Systems\AdminModule;
use SatuSehat\Model\Condition;
use SatuSehat\Model\Respiratory;
use SatuSehat\Model\HeartRate;
use SatuSehat\Model\Medication;
use SatuSehat\Model\MedicationDispense;
use SatuSehat\Model\MedicationRequest;
use SatuSehat\Model\Procedure;
use SatuSehat\Model\QuestionareMedication;
use SatuSehat\Model\Temperature;

class Admin extends AdminModule
{

  public function init()
  {
    $this->authurl = $this->settings->get('satu_sehat.authurl');
    $this->fhirurl = $this->settings->get('satu_sehat.fhirurl');
    $this->clientid = $this->settings->get('satu_sehat.clientid');
    $this->secretkey = $this->settings->get('satu_sehat.secretkey');
    $this->organizationid = $this->settings->get('satu_sehat.organizationid');
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
    $mlite_billing = $this->db('mlite_billing')->where('no_rawat', $no_rawat)->oneArray();
    if ($this->settings->get('satu_sehat.billing') == 'khanza') {
      $mlite_billing = $this->db('nota_jalan')->select([
        'tgl_billing' => 'tanggal',
        'jam_billing' => 'jam'
      ])
        ->where('no_rawat', $no_rawat)
        ->oneArray();
      if ($status_lanjut == 'Ranap') {
        $mlite_billing = $this->db('nota_inap')->select([
          'tgl_billing' => 'tanggal',
          'jam_billing' => 'jam'
        ])
          ->where('no_rawat', $no_rawat)
          ->oneArray();
      }
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

  public function getEncounterBundle($no_rawat)
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
    $inProg = $this->db('pemeriksaan_ralan')->select(['tgl' => 'tgl_perawatan', 'jam' => 'jam_rawat', 'respirasi' => 'respirasi', 'suhu' => 'suhu_tubuh', 'tensi' => 'tensi', 'nadi' => 'nadi'])->where('no_rawat', $no_rawat)->oneArray();
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
    if ($this->settings->get('satu_sehat.billing') == 'khanza') {
      $mlite_billing = $this->db('nota_jalan')->select([
        'tgl_billing' => 'tanggal',
        'jam_billing' => 'jam'
      ])
        ->where('no_rawat', $no_rawat)
        ->oneArray();
      if ($status_lanjut == 'Ranap') {
        $mlite_billing = $this->db('nota_inap')->select([
          'tgl_billing' => 'tanggal',
          'jam_billing' => 'jam'
        ])
          ->where('no_rawat', $no_rawat)
          ->oneArray();
      }
    }

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
      $uuid_encounter, $uuid_composition, $ihs_patient,$no_ktp_dokter['practitioner_id'],$nama_pasien,$nama_dokter,$no_rawat,
      $this->organizationid,$display_composition,$zonaWaktu_composition
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
      $nadi = new HeartRate($uuid_encounter, $uuid_nadi, $ihs_patient, $no_ktp_dokter['practitioner_id'], $inProg['nadi'], $zonaWaktu, $display_nadi);
      $heart_rate_json = $nadi->toJson();
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
    if ($medicationdispense_json) {
      $uuid_questionare = $this->gen_uuid();
      $questionare = new QuestionareMedication($uuid_questionare, $uuid_encounter, $ihs_patient, $nama_pasien, "10024043022", "Muhammad Saputra, S.Farm");
      $questionare_json = $questionare->toJson();
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
      $condition_json .
      $procedure_json .
      $medicationforrequest_json .
      $medicationrequest_json .
      $questionare_json .
      $medicationfordispense_json .
      $medicationdispense_json .
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
        'id_medication_dispense' => $id_medication_dispense
      ]);
      $pesan = 'Sukses mengirim pasien dengan No Rawat : ' . $no_rawat . ' ' . $index . ' ke platform Satu Sehat!! ';
    }

    curl_close($curl);
    echo $this->draw('encounter.html', ['pesan' => $pesan, 'response' => $response]);
    // echo $response;

    // DEBUG ******
    // print_r($response);
    // $response = json_decode($json_bundle);
    // print_r($json_bundle);
    exit();
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
      } else {
        $this->db('mlite_satu_sehat_response')
          ->save([
            'no_rawat' => $no_rawat,
            'id_observation_ttv' . $ttv . '' => $id_condition
          ]);
      }
      $pesan = 'Sukses mengirim observation ttv ' . $ttv . ' ke platform Satu Sehat!!';
    }

    curl_close($curl);
    // echo $response;
    echo $this->draw('observation.html', ['pesan' => $pesan, 'response' => $response]);
    exit();
  }

  public function getSettings()
  {
    return $this->draw('settings.html', ['satu_sehat' => $this->settings->get('satu_sehat')]);
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
    $apoteker = $this->db('pegawai')->where('stts_aktif', 'AKTIF')->where('bidang', 'Instalasi Farmasi (Ikhtiar)')->toArray();
    return $this->draw('mapping.praktisi.html', ['mapping_praktisi' => $unique, 'dokter' => $dokter, 'apoteker' => $apoteker]);
  }

  public function postSaveMappingPraktisi()
  {
    if (isset($_POST['simpan'])) {
      $kd_dokter = $_POST['dokter'];
      $nik = $this->core->getPegawaiInfo('no_ktp', $kd_dokter);
      $bidang = $this->core->getPegawaiInfo('bidang', $kd_dokter);
      $send_json = json_decode($this->getPractitioner($nik))->entry[0]->resource->id;
      $jenis_praktisi = 'Dokter';
      if ($bidang == 'Instalasi Farmasi (Ikhtiar)') {
        $jenis_praktisi = 'Apoteker';
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

  public function getResponse()
  {
    $this->_addHeaderFiles();
    // $periode = date('Y-m-d');
    // if (isset($_GET['periode']) && $_GET['periode'] != '') {
    //   $periode = $_GET['periode'];
    // }
    // $data_response = [];
    // $query = $this->db('reg_periksa')
    //   ->where('reg_periksa.tgl_registrasi', $periode)
    //   ->where('stts', '!=', 'Batal')
    //   ->where('status_lanjut', 'Ralan')
    //   ->limit(10)
    //   ->offset(220)
    //   ->toArray();
    // foreach ($query as $row) {

    //   $mlite_satu_sehat_response = $this->db('mlite_satu_sehat_response')->where('no_rawat', $row['no_rawat'])->oneArray();

    //   $row['no_ktp_pasien'] = $this->core->getPasienInfo('no_ktp', $row['no_rkm_medis']);
    //   $row['nm_pasien'] = $this->core->getPasienInfo('nm_pasien', $row['no_rkm_medis']);
    //   $row['nm_dokter'] = $this->core->getDokterInfo('nm_dokter', $row['kd_dokter']);
    //   $row['no_ktp_dokter'] = $this->core->getPegawaiInfo('no_ktp', $row['kd_dokter']);
    //   $row['nm_poli'] = $this->core->getPoliklinikInfo('nm_poli', $row['kd_poli']);

    //   $praktisi_id = $this->db('mlite_satu_sehat_mapping_praktisi')->where('kd_dokter',$row['kd_dokter'])->oneArray();
    //   $row['praktisi_id'] = $praktisi_id['practitioner_id'];

    //   $mlite_billing = $this->db('mlite_billing')->where('no_rawat', $row['no_rawat'])->oneArray();
    //   if($this->settings->get('satu_sehat.billing') == 'khanza') {
    //     $mlite_billing = $this->db('nota_jalan')->select([
    //       'tgl_billing' => 'tanggal'
    //     ])
    //     ->where('no_rawat', $row['no_rawat'])
    //     ->oneArray();
    //     if($status_lanjut == 'Ranap') {
    //       $mlite_billing = $this->db('nota_inap')->select([
    //         'tgl_billing' => 'tanggal'
    //       ])
    //       ->where('no_rawat', $row['no_rawat'])
    //       ->oneArray();
    //     }
    //   }      
    //   $row['tgl_pulang'] = isset_or($mlite_billing['tgl_billing'], '');      

    //   if ($row['status_lanjut'] == 'Ranap') {
    //     $row['kd_kamar'] = $this->core->getKamarInapInfo('kd_kamar', $row['no_rawat']);
    //     $row['kd_poli'] = $this->core->getKamarInfo('kd_bangsal', $row['kd_kamar']);
    //     $row['nm_poli'] = $this->core->getBangsalInfo('nm_bangsal', $row['kd_poli']);
    //   }

    //   $mlite_satu_sehat_lokasi = $this->db('mlite_satu_sehat_lokasi')->where('kode', $row['kd_poli'])->oneArray();
    //   $row['id_organisasi'] = $mlite_satu_sehat_lokasi['id_organisasi_satusehat'];
    //   $row['id_lokasi'] = $mlite_satu_sehat_lokasi['id_lokasi_satusehat'];

    //   $row['pemeriksaan'] = $this->db('pemeriksaan_ralan')
    //     ->where('no_rawat', $row['no_rawat'])
    //     ->limit(1)
    //     ->desc('tgl_perawatan')
    //     ->oneArray();

    //   if ($row['status_lanjut'] == 'Ranap') {
    //     $row['pemeriksaan'] = $this->db('pemeriksaan_ranap')
    //       ->where('no_rawat', $row['no_rawat'])
    //       ->limit(1)
    //       ->desc('tgl_perawatan')
    //       ->oneArray();
    //   }

    //   $row['diagnosa_pasien'] = $this->db('diagnosa_pasien')
    //     ->join('penyakit', 'penyakit.kd_penyakit=diagnosa_pasien.kd_penyakit')
    //     ->where('no_rawat', $row['no_rawat'])
    //     ->where('diagnosa_pasien.status', $row['status_lanjut'])
    //     ->where('prioritas', '1')
    //     ->oneArray();

    //   $row['id_encounter'] = isset_or($mlite_satu_sehat_response['id_encounter'], '');
    //   $row['id_condition'] = isset_or($mlite_satu_sehat_response['id_condition'], '');
    //   $row['id_observation_ttvtensi'] = isset_or($mlite_satu_sehat_response['id_observation_ttvtensi'], '');
    //   $row['id_observation_ttvnadi'] = isset_or($mlite_satu_sehat_response['id_observation_ttvnadi'], '');
    //   $row['id_observation_ttvrespirasi'] = isset_or($mlite_satu_sehat_response['id_observation_ttvrespirasi'], '');
    //   $row['id_observation_ttvsuhu'] = isset_or($mlite_satu_sehat_response['id_observation_ttvsuhu'], '');
    //   $row['id_observation_ttvspo2'] = isset_or($mlite_satu_sehat_response['id_observation_ttvspo2'], '');
    //   $row['id_observation_ttvgcs'] = isset_or($mlite_satu_sehat_response['id_observation_ttvgcs'], '');
    //   $row['id_observation_ttvtinggi'] = isset_or($mlite_satu_sehat_response['id_observation_ttvtinggi'], '');
    //   $row['id_observation_ttvberat'] = isset_or($mlite_satu_sehat_response['id_observation_ttvberat'], '');
    //   $row['id_observation_ttvperut'] = isset_or($mlite_satu_sehat_response['id_observation_ttvperut'], '');
    //   $row['id_observation_ttvkesadaran'] = isset_or($mlite_satu_sehat_response['id_observation_ttvkesadaran'], '');
    //   $row['id_procedure'] = isset_or($mlite_satu_sehat_response['id_procedure'], '');
    //   $row['id_composition'] = isset_or($mlite_satu_sehat_response['id_composition'], '');
    //   $row['id_medication_for_request'] = isset_or($mlite_satu_sehat_response['id_medication_for_request'], '');
    //   $row['id_medication_request'] = isset_or($mlite_satu_sehat_response['id_medication_request'], '');
    //   $row['id_medication_for_dispense'] = isset_or($mlite_satu_sehat_response['id_medication_for_dispense'], '');
    //   $row['id_medication_dispense'] = isset_or($mlite_satu_sehat_response['id_medication_dispense'], '');
    //   $data_response[] = $row;
    // }
    return $this->draw('response.html');
    // return $this->draw('response.html', ['data_response' => $data_response]);
  }

  public function postResponseApi()
  {
    $this->_addHeaderFiles();
    $periode = date('Y-m-d');
    if (isset($_GET['periode']) && $_GET['periode'] != '') {
      $periode = $_GET['periode'];
    }
    $start  = intval($_POST['start'] ?? 0);
    $length = intval($_POST['length'] ?? 10);
    $data_response = [];
    $query = $this->db('reg_periksa')
      ->where('reg_periksa.tgl_registrasi', $periode)
      ->where('stts', '!=', 'Batal')
      ->where('status_lanjut', 'Ralan')
      ->limit($length)
      ->offset($start)
      ->toArray();
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
      $row['id_organisasi'] = $mlite_satu_sehat_lokasi['id_organisasi_satusehat'];
      $row['id_lokasi'] = $mlite_satu_sehat_lokasi['id_lokasi_satusehat'];

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
      $row['id_medication_for_request'] = isset_or($mlite_satu_sehat_response['id_medication_for_request'], '');
      $row['id_medication_request'] = isset_or($mlite_satu_sehat_response['id_medication_request'], '');
      $row['id_medication_for_dispense'] = isset_or($mlite_satu_sehat_response['id_medication_for_dispense'], '');
      $row['id_medication_dispense'] = isset_or($mlite_satu_sehat_response['id_medication_dispense'], '');
      $data_response[] = $row;
    }
    // echo json_encode($data_response);
    echo json_encode([
      "data" => $data_response
    ]);
    exit();
    // return $this->draw('response.html', ['data_response' => $data_response]);
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
      CURLOPT_URL => "${tokenUrl}/accesstoken?grant_type=client_credentials",
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
    $this->core->addCSS(url('https://cdn.datatables.net/fixedcolumns/4.3.0/css/fixedColumns.dataTables.min.css'));
    $this->core->addJS(url('assets/jscripts/jquery.dataTables.min.js'));
    $this->core->addJS(url('assets/jscripts/dataTables.bootstrap.min.js'));
    $this->core->addJS(url('https://cdn.datatables.net/fixedcolumns/4.3.0/js/dataTables.fixedColumns.min.js'));
    $this->core->addCSS(url('assets/css/bootstrap-datetimepicker.css'));
    $this->core->addJS(url('assets/jscripts/moment-with-locales.js'));
    $this->core->addJS(url('assets/jscripts/bootstrap-datetimepicker.js'));
  }
}
