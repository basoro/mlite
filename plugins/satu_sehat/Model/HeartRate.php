<?php

namespace SatuSehat\Model;

class HeartRate
{
    private $uuid_encounter;
    private $uuid_nadi;
    private $ihs_patient;
    private $no_ktp_dokter;
    private $inProg;
    private $zonawaktu;
    private $display_nadi;

    public function __construct($uuid_encounter, $uuid_nadi, $ihs_patient, $no_ktp_dokter, $inProg, $zonawaktu, $display_nadi)
    {
        $this->uuid_encounter = $uuid_encounter;
        $this->uuid_nadi = $uuid_nadi;
        $this->ihs_patient = $ihs_patient;
        $this->no_ktp_dokter = $no_ktp_dokter;
        $this->inProg = $inProg;
        $this->zonawaktu = $zonawaktu;
        $this->display_nadi = $display_nadi;
    }

    public function toJson()
    {
        return '{
        "fullUrl": "urn:uuid:' . $this->uuid_nadi . '",
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
                        "code": "8867-4",
                        "display": "Heart rate"
                    }
                ]
            },
            "subject": {
                "reference": "Patient/' . $this->ihs_patient . '"
            },
            "performer": [
                {
                    "reference": "Practitioner/' . $this->no_ktp_dokter . '"
                }
            ],
            "encounter": {
                "reference": "urn:uuid:' . $this->uuid_encounter . '",
                "display": "' . $this->display_nadi . '"
            },
            "effectiveDateTime": "' . $this->zonawaktu . '",
            "issued": "' . $this->zonawaktu . '",
            "valueQuantity": {
                "value": '.$this->inProg.',
                "unit": "beats/minute",
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
}