<?php

namespace SatuSehat\Src;

class Respiratory
{
    private $uuid_encounter;
    private $uuid_respiration;
    private $ihs_patient;
    private $no_ktp_dokter;
    private $inProg;
    private $zonawaktu;
    private $display_respiration;

    public function __construct($uuid_encounter, $uuid_respiration, $ihs_patient, $no_ktp_dokter, $inProg, $zonawaktu, $display_respiration)
    {
        $this->uuid_encounter = $uuid_encounter;
        $this->uuid_respiration = $uuid_respiration;
        $this->ihs_patient = $ihs_patient;
        $this->no_ktp_dokter = $no_ktp_dokter;
        $this->inProg = $inProg;
        $this->zonawaktu = $zonawaktu;
        $this->display_respiration = $display_respiration;
    }

    public function toJson()
    {
        return '{
            "fullUrl": "urn:uuid:' . $this->uuid_respiration . '",
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
                            "code": "9279-1",
                            "display": "Respiratory rate"
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
                    "display": "'.$this->display_respiration.'"
                },
                "effectiveDateTime": "' . $this->zonawaktu . '",
                "issued": "' . $this->zonawaktu . '",
                "valueQuantity": {
                    "value": '.$this->inProg.',
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
}