<?php

namespace Plugins\Satu_Sehat;

use Systems\AdminModule;
use Systems\Lib\BpjsService;

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
        'Index'   => 'index',
        'Kelola'   => 'manage',
        'Pengaturan'   => 'settings',
      ];
  }

  public function getIndex()
  {
    $sub_modules = [
      ['name' => 'Referensi Praktisi', 'url' => url([ADMIN, 'satu_sehat', 'praktisi']), 'icon' => 'heart', 'desc' => 'Referensi praktisi satu sehat'],
      ['name' => 'Referensi Pasien', 'url' => url([ADMIN, 'satu_sehat', 'pasien']), 'icon' => 'heart', 'desc' => 'Referensi pasien satu sehat'],
      ['name' => 'Mapping Departemen', 'url' => url([ADMIN, 'satu_sehat', 'departemen']), 'icon' => 'heart', 'desc' => 'Mapping departemen satu sehat'],
      ['name' => 'Mapping Lokasi', 'url' => url([ADMIN, 'satu_sehat', 'lokasi']), 'icon' => 'heart', 'desc' => 'Mapping lokasi satu sehat'],
      ['name' => 'Kirim Encounter', 'url' => url([ADMIN, 'satu_sehat', 'dataencounter']), 'icon' => 'heart', 'desc' => 'Kirim encounter satu sehat'],
      ['name' => 'Pengaturan', 'url' => url([ADMIN, 'satu_sehat', 'settings']), 'icon' => 'heart', 'desc' => 'Pengaturan satu sehat'],
    ];
    return $this->draw('index.html', ['sub_modules' => $sub_modules]);
  }

  public function getManage()
  {
    $sub_modules = [
      ['name' => 'Referensi Praktisi', 'url' => url([ADMIN, 'satu_sehat', 'praktisi']), 'icon' => 'heart', 'desc' => 'Referensi praktisi satu sehat'],
      ['name' => 'Referensi Pasien', 'url' => url([ADMIN, 'satu_sehat', 'pasien']), 'icon' => 'heart', 'desc' => 'Referensi pasien satu sehat'],
      ['name' => 'Mapping Departemen', 'url' => url([ADMIN, 'satu_sehat', 'departemen']), 'icon' => 'heart', 'desc' => 'Mapping departemen satu sehat'],
      ['name' => 'Mapping Lokasi', 'url' => url([ADMIN, 'satu_sehat', 'lokasi']), 'icon' => 'heart', 'desc' => 'Mapping lokasi satu sehat'],
      ['name' => 'Kirim Encounter', 'url' => url([ADMIN, 'satu_sehat', 'dataencounter']), 'icon' => 'heart', 'desc' => 'Kirim encounter satu sehat'],
      ['name' => 'Pengaturan', 'url' => url([ADMIN, 'satu_sehat', 'settings']), 'icon' => 'heart', 'desc' => 'Pengaturan satu sehat'],
    ];
    return $this->draw('index.html', ['sub_modules' => $sub_modules]);
  }

  public function getToken()
  {

    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => $this->settings->get('satu_sehat.authurl').'/accesstoken?grant_type=client_credentials',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS => 'client_id='.$this->clientid.'&client_secret='.$this->secretkey,
    ));
    
    $response = curl_exec($curl);
    
    curl_close($curl);
    return $response;
    // echo $response;    
    // exit();
  }

  public function getTest()
  {
    echo json_decode($this->getToken())->access_token;
    if(!empty($this->core->getPoliklinikInfo('nm_poli', '121212'))) {
      echo $this->core->getPoliklinikInfo('nm_poli', 'IGDK');
    } else {
      echo $this->core->getBangsalInfo('nm_bangsal', 'ANG');
    }
    exit();
  }

  public function getPractitioner($nik_dokter)
  {

    $curl = curl_init();
    
    curl_setopt_array($curl, array(
      CURLOPT_URL => $this->fhirurl.'/Practitioner?identifier=https://fhir.kemkes.go.id/id/nik|'.$nik_dokter,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_HTTPHEADER => array('Content-Type: application/json', 'Authorization: Bearer '.json_decode($this->getToken())->access_token),
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
      CURLOPT_URL => $this->fhirurl.'/Practitioner/'.$id_dokter,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'GET',
      CURLOPT_HTTPHEADER => array('Content-Type: application/json', 'Authorization: Bearer '.json_decode($this->getToken())->access_token),
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
      CURLOPT_URL => $this->fhirurl.'/Patient?identifier=https://fhir.kemkes.go.id/id/nik|'.$nik_pasien,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_HTTPHEADER => array('Content-Type: application/json', 'Authorization: Bearer '.json_decode($this->getToken())->access_token),
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
      CURLOPT_URL => $this->fhirurl.'/Patient/'.$id_pasien,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'GET',
      CURLOPT_HTTPHEADER => array('Content-Type: application/json', 'Authorization: Bearer '.json_decode($this->getToken())->access_token),
    ));
    
    $response = curl_exec($curl);
    
    curl_close($curl);
    echo $response;
    exit();

  }
  
  public function getOrganization($kode_departemen)
  {

    $curl = curl_init();
    
    curl_setopt_array($curl, array(
      CURLOPT_URL => $this->fhirurl.'/Organization',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_HTTPHEADER => array('Content-Type: application/json', 'Authorization: Bearer '.json_decode($this->getToken())->access_token),
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS =>'{
        "resourceType": "Organization",
        "active": true,
        "identifier": [
            {
                "use": "official",
                "system": "http://sys-ids.kemkes.go.id/organization/'.$this->organizationid.'",
                "value": "'.$kode_departemen.'"
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
        "name": "'.$this->core->getDepartemenInfo($kode_departemen).'",
        "telecom": [
            {
                "system": "phone",
                "value": "'.$this->settings->get('settings.nomor_telepon').'",
                "use": "work"
            },
            {
                "system": "email",
                "value": "'.$this->settings->get('settings.email').'",
                "use": "work"
            },
            {
                "system": "url",
                "value": "www.'.$this->settings->get('settings.email').'",
                "use": "work"
            }
        ],
        "address": [
            {
                "use": "work",
                "type": "both",
                "line": [
                    "'.$this->settings->get('settings.alamat').'"
                ],
                "city": "'.$this->settings->get('settings.kota').'",
                "postalCode": "'.$this->settings->get('satu_sehat.kodepos').'",
                "country": "ID",
                "extension": [
                    {
                        "url": "https://fhir.kemkes.go.id/r4/StructureDefinition/administrativeCode",
                        "extension": [
                            {
                                "url": "province",
                                "valueCode": "3'.$this->settings->get('satu_sehat.propinsi').'1"
                            },
                            {
                                "url": "city",
                                "valueCode": "'.$this->settings->get('satu_sehat.kabupaten').'"
                            },
                            {
                                "url": "district",
                                "valueCode": "'.$this->settings->get('satu_sehat.kecamatan').'"
                            },
                            {
                                "url": "village",
                                "valueCode": "'.$this->settings->get('satu_sehat.kelurahan').'"
                            }
                        ]
                    }
                ]
            }
        ],
        "partOf": {
            "reference": "Organization/'.$this->settings->get('satu_sehat.organizationid').'"
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

    $mlite_satu_sehat_departemen = $this->core->mysql('mlite_satu_sehat_departemen')->where('dep_id', $kode_departemen)->oneArray();

    $curl = curl_init();
    
    curl_setopt_array($curl, array(
      CURLOPT_URL => $this->fhirurl.'/Organization/'.$mlite_satu_sehat_departemen['id_organisasi_satusehat'],
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_HTTPHEADER => array('Content-Type: application/json', 'Authorization: Bearer '.json_decode($this->getToken())->access_token),
      CURLOPT_CUSTOMREQUEST => 'GET',
    ));
    
    $response = curl_exec($curl);
    
    curl_close($curl);
    echo $response;
    exit();
  }

  public function getOrganizationUpdate($kode_departemen)
  {

    $mlite_satu_sehat_departemen = $this->core->mysql('mlite_satu_sehat_departemen')->where('dep_id', $kode_departemen)->oneArray();

    $curl = curl_init();
    
    curl_setopt_array($curl, array(
      CURLOPT_URL => $this->fhirurl.'/Organization/'.$mlite_satu_sehat_departemen['id_organisasi_satusehat'],
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_HTTPHEADER => array('Content-Type: application/json', 'Authorization: Bearer '.json_decode($this->getToken())->access_token),
      CURLOPT_CUSTOMREQUEST => 'PUT',
      CURLOPT_POSTFIELDS =>'{
        "resourceType": "Organization",
        "id":"'.$mlite_satu_sehat_departemen['id_organisasi_satusehat'].'",
        "active": true,
        "identifier": [
            {
                "use": "official",
                "system": "http://sys-ids.kemkes.go.id/organization/'.$this->organizationid.'",
                "value": "'.$kode_departemen.'"
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
        "name": "'.$this->core->getDepartemenInfo($kode_departemen).'",
        "telecom": [
            {
                "system": "phone",
                "value": "'.$this->settings->get('settings.nomor_telepon').'",
                "use": "work"
            },
            {
                "system": "email",
                "value": "'.$this->settings->get('settings.email').'",
                "use": "work"
            },
            {
                "system": "url",
                "value": "www.'.$this->settings->get('settings.email').'",
                "use": "work"
            }
        ],
        "address": [
            {
                "use": "work",
                "type": "both",
                "line": [
                    "'.$this->settings->get('settings.alamat').'"
                ],
                "city": "'.$this->settings->get('settings.kota').'",
                "postalCode": "'.$this->settings->get('satu_sehat.kodepos').'",
                "country": "ID",
                "extension": [
                    {
                        "url": "https://fhir.kemkes.go.id/r4/StructureDefinition/administrativeCode",
                        "extension": [
                            {
                                "url": "province",
                                "valueCode": "3'.$this->settings->get('satu_sehat.propinsi').'1"
                            },
                            {
                                "url": "city",
                                "valueCode": "'.$this->settings->get('satu_sehat.kabupaten').'"
                            },
                            {
                                "url": "district",
                                "valueCode": "'.$this->settings->get('satu_sehat.kecamatan').'"
                            },
                            {
                                "url": "village",
                                "valueCode": "'.$this->settings->get('satu_sehat.kelurahan').'"
                            }
                        ]
                    }
                ]
            }
        ],
        "partOf": {
            "reference": "Organization/'.$this->settings->get('satu_sehat.organizationid').'"
        }
    }',
    ));
    
    $response = curl_exec($curl);
    
    curl_close($curl);
    echo $response;
    exit();      
  }

  public function getLocation($kode)
  {
    $lokasi = '';
    if(!empty($this->core->getPoliklinikInfo('nm_poli', $kode))) {
      $lokasi = $this->core->getPoliklinikInfo('nm_poli', $kode);
    } else {
      $lokasi = $this->core->getBangsalInfo('nm_bangsal', $kode);
    }

    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => $this->fhirurl.'/Location',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_HTTPHEADER => array('Content-Type: application/json', 'Authorization: Bearer '.json_decode($this->getToken())->access_token),
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS =>'{
        "resourceType": "Location",
        "identifier": [
            {
                "system": "http://sys-ids.kemkes.go.id/location/'.$this->organizationid.'",
                "value": "'.$kode.'"
            }
        ],
        "status": "active",
        "name": "'.$lokasi.'",
        "description": "'.$kode.' - '.$lokasi.'",
        "mode": "instance",
        "telecom": [
          {
              "system": "phone",
              "value": "'.$this->settings->get('settings.nomor_telepon').'",
              "use": "work"
          },
          {
              "system": "email",
              "value": "'.$this->settings->get('settings.email').'",
              "use": "work"
          },
          {
              "system": "url",
              "value": "www.'.$this->settings->get('settings.email').'",
              "use": "work"
          }
        ],
        "address": {
            "use": "work",
            "line": [
                "'.$this->settings->get('settings.alamat').'"
            ],
            "city": "'.$this->settings->get('settings.kota').'",
            "postalCode": "'.$this->settings->get('satu_sehat.kodepos').'",
            "country": "ID",
            "extension": [
                {
                    "url": "https://fhir.kemkes.go.id/r4/StructureDefinition/administrativeCode",
                    "extension": [
                        {
                            "url": "province",
                            "valueCode": "'.$this->settings->get('satu_sehat.propinsi').'"
                        },
                        {
                            "url": "city",
                            "valueCode": "'.$this->settings->get('satu_sehat.kabupaten').'"
                        },
                        {
                            "url": "district",
                            "valueCode": "'.$this->settings->get('satu_sehat.kecamatan').'"
                        },
                        {
                            "url": "village",
                            "valueCode": "'.$this->settings->get('satu_sehat.kelurahan').'"
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
            "longitude": '.$this->settings->get('satu_sehat.longitude').',
            "latitude": '.$this->settings->get('satu_sehat.latitude').',
            "altitude": 0
      },
        "managingOrganization": {
            "reference": "Organization/'.$this->settings->get('satu_sehat.organizationid').'"
        }
    }',
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    return $response;
    // echo $response;
    // exit();
  }

  public function getLocationByOrgId($kode_departemen)
  {

    $mlite_satu_sehat_lokasi = $this->core->mysql('mlite_satu_sehat_lokasi')->where('kode', $kode_departemen)->oneArray();

    $curl = curl_init();
    
    curl_setopt_array($curl, array(
      CURLOPT_URL => $this->fhirurl.'/Location?organization='.$mlite_satu_sehat_lokasi['id_lokasi_satusehat'],
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_HTTPHEADER => array('Content-Type: application/json', 'Authorization: Bearer '.json_decode($this->getToken())->access_token),
      CURLOPT_CUSTOMREQUEST => 'GET',
    ));
    
    $response = curl_exec($curl);
    
    curl_close($curl);
    echo $response;
    exit();    

  }

  public function getLocationUpdate($kode_departemen)
  {
 
    $mlite_satu_sehat_lokasi = $this->core->mysql('mlite_satu_sehat_lokasi')->where('kode', $kode_departemen)->oneArray();

    $curl = curl_init();
    
    curl_setopt_array($curl, array(
      CURLOPT_URL => $this->fhirurl.'/Location/'.$mlite_satu_sehat_lokasi['id_lokasi_satusehat'],
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_HTTPHEADER => array('Content-Type: application/json', 'Authorization: Bearer '.json_decode($this->getToken())->access_token),
      CURLOPT_CUSTOMREQUEST => 'PUT',
      CURLOPT_POSTFIELDS =>'{
        "resourceType": "Location",
        "id": "'.$mlite_satu_sehat_lokasi['id_lokasi_satusehat'].'",
        "identifier": [
            {
                "system": "http://sys-ids.kemkes.go.id/location/'.$this->organizationid.'",
                "value": "'.$mlite_satu_sehat_lokasi['kode'].'"
            }
        ],
        "status": "inactive",
        "name": "'.$mlite_satu_sehat_lokasi['lokasi'].'",
        "description": "'.$mlite_satu_sehat_lokasi['kode'].' - '.$mlite_satu_sehat_lokasi['lokasi'].'",
        "mode": "instance",
        "telecom": [
            {
                "system": "phone",
                "value": "'.$this->settings->get('settings.nomor_telepon').'",
                "use": "work"
            },
            {
                "system": "fax",
                "value": "'.$this->settings->get('settings.nomor_telepon').'",
                "use": "work"
            },
            {
                "system": "email",
                "value": "'.$this->settings->get('settings.email').'"
            },
            {
                "system": "url",
                "value": "'.$this->settings->get('settings.website').'",
                "use": "work"
            }
        ],
        "address": {
            "use": "work",
            "line": [
                "'.$this->settings->get('settings.alamat').'"
            ],
            "city": "'.$this->settings->get('settings.kota').'",
            "postalCode": "'.$this->settings->get('satu_sehat.kodepos').'",
            "country": "ID",
            "extension": [
                {
                    "url": "https://fhir.kemkes.go.id/r4/StructureDefinition/administrativeCode",
                    "extension": [
                        {
                            "url": "province",
                            "valueCode": "'.$this->settings->get('satu_sehat.propinsi').'"
                        },
                        {
                            "url": "city",
                            "valueCode": "'.$this->settings->get('satu_sehat.kabupaten').'"
                        },
                        {
                            "url": "district",
                            "valueCode": "'.$this->settings->get('satu_sehat.kecamatan').'"
                        },
                        {
                            "url": "village",
                            "valueCode": "'.$this->settings->get('satu_sehat.kelurahan').'"
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
            "reference": "Organization/'.$this->settings->get('satu_sehat.organizationid').'"
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
    $mlite_billing = $this->core->mysql('mlite_billing')->where('no_rawat', $no_rawat)->oneArray();

    $code = 'AMB';
    $display = 'ambulatory';
    if($status_lanjut == 'Ranap') {
      $kd_poli = $this->core->getKamarInapInfo('kd_poli', $no_rawat);
      $kd_bangsal = $this->core->getKamarInfo('kd_bangsal', $kd_poli);  
      $nm_poli = $this->core->getBangsalInfo('nm_bangsal', $kd_bangsal);  
      $code = 'IMP';
      $display = 'inpatient encounter';  
    }

    $mlite_satu_sehat_lokasi = $this->core->mysql('mlite_satu_sehat_lokasi')->where('kode', $kd_poli)->oneArray();
    // $mlite_satu_sehat_lokasi['id_lokasi_satusehat'] = '66c0e46d-6d35-423c-b3a6-4b8bd96eb728';

    $curl = curl_init();
    
    curl_setopt_array($curl, array(
      CURLOPT_URL => $this->fhirurl.'/Encounter',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_HTTPHEADER => array('Content-Type: application/json', 'Authorization: Bearer '.json_decode($this->getToken())->access_token),
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS =>'{
        "resourceType": "Encounter",
        "status": "arrived",
        "class": {
            "system": "http://terminology.hl7.org/CodeSystem/v3-ActCode",
            "code": "AMB",
            "display": "ambulatory"
        },
        "subject": {
            "reference": "Patient/'.json_decode($this->getPatient($no_ktp_pasien))->entry[0]->resource->id.'",
            "display": "'.$nama_pasien.'"
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
                    "reference": "Practitioner/'.json_decode($this->getPractitioner($no_ktp_dokter))->entry[0]->resource->id.'",
                    "display": "'.$nama_dokter.'"
                }
            }
        ],
        "period": {
            "start": "'.$tgl_registrasi.'T'.$jam_reg.'+08:00"
        },
        "location": [
            {
                "location": {
                    "reference": "Location/'.$mlite_satu_sehat_lokasi['id_lokasi_satusehat'].'",
                    "display": "'.$kd_poli.' '.$nm_poli.'"
                }
            }
        ],
        "statusHistory": [
            {
                "status": "arrived",
                "period": {
                    "start": "'.$mlite_billing['tgl_billing'].'T'.$mlite_billing['jam_billing'].'+08:00"
                }
            }
        ],
        "serviceProvider": {
            "reference": "Organization/'.$this->organizationid.'"
        },
        "identifier": [
            {
                "system": "http://sys-ids.kemkes.go.id/encounter/'.$this->organizationid.'",
                "value": "'.$no_rawat.'"
            }
        ]
    }',
    ));
    
    $response = curl_exec($curl);
    
    $id_encounter = json_decode($response)->id;
    $pesan = 'Gagal mengirim encounter platform Satu Sehat!!';
    if($id_encounter) {
      $this->core->mysql('mlite_satu_sehat_encounter')->save([
        'no_rawat' => $no_rawat,
        'id_encounter' => $id_encounter
      ]);
      $pesan = 'Sukses mengirim encounter platform Satu Sehat!!';
    }
    
    curl_close($curl);
    // echo $response;
    echo $this->draw('encounter.html', ['pesan' => $pesan, 'response' => $response]);
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
    if(isset($_POST['nik_dokter']) && $_POST['nik_dokter'] !='') {
      $response = json_decode($this->getPractitioner($_POST['nik_dokter']));
    }
    return $this->draw('praktisi.html', ['response' => json_encode($response, JSON_PRETTY_PRINT)]);
  }

  public function anyPasien()
  {
    $response = [];
    if(isset($_POST['nik_pasien']) && $_POST['nik_pasien'] !='') {
      $response = json_decode($this->getPatient($_POST['nik_pasien']));
    }
    return $this->draw('pasien.html', ['response' => json_encode($response, JSON_PRETTY_PRINT)]);
  }

  public function getDepartemen()
  {
    return $this->draw('departemen.html', ['departemen' => $this->core->mysql('departemen')->toArray(), 'satu_sehat_departemen' => $this->core->mysql('mlite_satu_sehat_departemen')->join('departemen', 'departemen.dep_id=mlite_satu_sehat_departemen.dep_id')->toArray()]);
  }

  public function postSaveDepartemen()
  {
    if(isset($_POST['simpan'])) {

      $id_organisasi_satusehat = json_decode($this->getOrganization($_POST['dep_id']))->id;
      
      if($id_organisasi_satusehat !='') {
        $query = $this->core->mysql('mlite_satu_sehat_departemen')->save(
          [
            'dep_id' => $_POST['dep_id'], 
            'id_organisasi_satusehat' => $id_organisasi_satusehat
          ]
        );  
        if($query){
          $this->notify('success', 'Mapping departemen telah disimpan');
        } else {
          $this->notify('danger', 'Mapping departemen gagal disimpan');
        }
      }
    }
    if(isset($_POST['update'])) {
      $query = $this->core->mysql('mlite_satu_sehat_departemen')
       ->where('id_organisasi_satusehat', $_POST['id_organisasi_satusehat'])
       ->save(
        [
          'dep_id' => $_POST['dep_id'], 
          'nama' => $_POST['nama']
        ]
      );
      if($query) {
        $this->notify('success', 'Mapping departemen telah disimpan');
      }
    }
    redirect(url([ADMIN, 'satu_sehat', 'departemen']));
  }

  public function getLokasi()
  {
    $poliklinik = $this->core->mysql('poliklinik')->select([
      'kode' => 'kd_poli',
      'nama' => 'nm_poli'
    ])->toArray();
    $bangsal = $this->core->mysql('bangsal')->select([
      'kode' => 'kd_bangsal',
      'nama' => 'nm_bangsal'
    ])->toArray();
    $lokasi = array_merge($poliklinik, $bangsal);
    return $this->draw('lokasi.html', [
      'lokasi' => $lokasi, 
      'satu_sehat_departemen' => $this->core->mysql('mlite_satu_sehat_departemen')->toArray(), 
      'satu_sehat_lokasi' => $this->core->mysql('mlite_satu_sehat_lokasi')->toArray()
    ]);
  }

  public function postSaveLokasi()
  {
    if(isset($_POST['simpan'])) {

      $id_lokasi_satusehat = json_decode($this->getLocation($_POST['kode']))->id;
      $mlite_satu_sehat_departemen = $this->core->mysql('mlite_satu_sehat_departemen')->where('dep_id', $_POST['dep_id'])->oneArray();
      $id_organisasi_satusehat = $mlite_satu_sehat_departemen['id_organisasi_satusehat'];
      
      if($id_lokasi_satusehat !='') {
        $query = $this->core->mysql('mlite_satu_sehat_lokasi')->save(
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
        if($query){
          $this->notify('success', 'Mapping lokasi telah disimpan');
        } else {
          $this->notify('danger', 'Mapping lokasi gagal disimpan');
        }
      }
    }
    if(isset($_POST['update'])) {
      $query = $this->core->mysql('mlite_satu_sehat_lokasi')
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
      if($query) {
        $this->notify('success', 'Mapping lokasi telah disimpan');
      }
    }
    redirect(url([ADMIN, 'satu_sehat', 'lokasi']));
  }  
  
  public function getKirimEncounter($no_rawat)
  {
  
    echo $this->draw('kirim.encounter.html');
    exit();
  }

  public function getDataEncounter()
  {
    $periode = str_replace('-', '/', trim($tgl_registrasi));
    $date = isset_or($periode, date('Y/m/d'));
    $data_encounter = $this->core->mysql('mlite_satu_sehat_encounter')
      ->join('reg_periksa', 'reg_periksa.no_rawat=mlite_satu_sehat_encounter.no_rawat')
      ->join('pasien', 'pasien.no_rkm_medis=reg_periksa.no_rkm_medis')
      ->like('mlite_satu_sehat_encounter.no_rawat', '%'.$periode)
      ->toArray();
    return $this->draw('data.encounter.html', ['data_encounter' => $data_encounter]);
  }
}
