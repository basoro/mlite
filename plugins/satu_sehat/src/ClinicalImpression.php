<?php

namespace SatuSehat\Src;

class ClinicalImpression
{
    private $org_id;
    private $uuid_clinical_impression;
    private $no_rawat;
    private $ihs_patient;
    private $nama_pasien;
    private $uuid_encounter;
    private $keluhan_utama;

    public function __construct($org_id, $uuid_clinical_impression, $no_rawat, $ihs_patient, $nama_pasien, $uuid_encounter, $keluhan_utama)
    {
        $this->org_id = $org_id;
        $this->uuid_clinical_impression = $uuid_clinical_impression;
        $this->no_rawat = $no_rawat;
        $this->ihs_patient = $ihs_patient;
        $this->nama_pasien = $nama_pasien;
        $this->uuid_encounter = $uuid_encounter;
        $this->keluhan_utama = $keluhan_utama;
    }

    public function toJson()
    {
        return '{
            "resourceType": "ClinicalImpression",
            "status": "completed",
            "code": {
                "coding": [
                    {
                        "system": "http://snomed.info/sct",
                        "code": "312850006",
                        "display": "History of disorder"
                    }
                ]
            },
            "identifier": [
                {
                    "use": "official",
                    "system": "http://sys-ids.kemkes.go.id/clinicalimpression/' . $this->org_id . '",
                    "value": "' . $this->no_rawat . '"
                }
            ],
            "subject": {
                "reference": "Patient/'. $this->ihs_patient . '",
                "display": "' . $this->nama_pasien . '"
            },
            "encounter": {
                "reference": "Encounter/' . $this->uuid_encounter . '"
            },
            "prognosisCodeableConcept": [
                {
                    "coding": [
                        {
                            "system": "http://snomed.info/sct",
                            "code": "65872000",
                            "display": "Dubia et bonam"
                        }
                    ]
                }
            ],
            "summary": "' . $this->keluhan_utama . '"
        }';
    }

    public function toJsonBundle()
    {   
        $bundle = '{
                    "fullUrl": "urn:uuid:' . $this->uuid_clinical_impression . '",
                    "resource": '. $this->toJson() . ',
                    "request": {
                        "method": "POST",
                        "url": "ClinicalImpression"
                    }
                },';
        return $bundle;
    }
}