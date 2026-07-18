<?php

namespace SatuSehat\Src;

class Observation
{
    private $uuid_encounter;
    private $uuid_observation;
    private $ihs_patient;
    private $no_ktp_dokter;
    private $inProg;
    private $zonawaktu;
    private $display_nadi;
    private $observation_type;
    private $uuid_specimen;
    private $uuid_service_request;
    private $code_loinc;
    private $display_loinc;

    public function __construct($uuid_encounter, $uuid_observation, $ihs_patient, $no_ktp_dokter, $inProg, $zonawaktu, $display_nadi ,$observation_type, $uuid_specimen = null, $uuid_service_request = null, $code_loinc = null, $display_loinc = null)
    {
        $this->uuid_encounter = $uuid_encounter;
        $this->uuid_observation = $uuid_observation;
        $this->ihs_patient = $ihs_patient;
        $this->no_ktp_dokter = $no_ktp_dokter;
        $this->inProg = $inProg;
        $this->zonawaktu = $zonawaktu;
        $this->display_nadi = $display_nadi;
        $this->observation_type = $observation_type;
        $this->uuid_specimen = $uuid_specimen;
        $this->uuid_service_request = $uuid_service_request;
        $this->code_loinc = $code_loinc;
        $this->display_loinc = $display_loinc;
    }

    public function toJson()
    {
        return '{
        "fullUrl": "urn:uuid:' . $this->uuid_observation . '",
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

    public function toJsonObservation()
    {
        if ($this->observation_type == 'nadi') {
            $category_coding = [
                [
                    "system" => "http://terminology.hl7.org/CodeSystem/observation-category",
                    "code" => "vital-signs",
                    "display" => "Vital Signs"
                ]
            ];
            $code_coding = [
                [
                    "system" => "http://loinc.org",
                    "code" => "8867-4",
                    "display" => "Heart rate"
                ]
            ];
            $value_additional = [
                "valueQuantity" => [
                    "value" => (float) $this->inProg,
                    "unit" => "beats/minute",
                    "system" => "http://unitsofmeasure.org",
                    "code" => "/min"
                ]
            ];
            
        }

        if ($this->observation_type == 'lab') {
            $category_coding = [
                [
                    "system" => "http://terminology.hl7.org/CodeSystem/observation-category",
                    "code" => "laboratory",
                    "display" => "Laboratory"
                ]
            ];
            $code_coding = [
                [
                    "system" => "http://loinc.org",
                    "code" => $this->code_loinc,
                    "display" => $this->display_loinc
                ]
            ];
            $value_additional = [
                "specimen" => [
                    "reference" => "urn:uuid:".$this->uuid_specimen
                ],
                "basedOn" => [
                    [
                        "reference" => "urn:uuid:".$this->uuid_service_request
                    ]
                ]
            ];
        }

        $data = array_merge(
            [
                "resourceType" => "Observation",
                "status" => "final",
                "category" => [
                    [
                        "coding" => $category_coding
                    ]
                ],
                "code" => [
                    "coding" => $code_coding
                ],
                "subject" => [
                    "reference" => "Patient/".$this->ihs_patient
                ],
                "performer" => [
                    [
                        "reference" => "Practitioner/".$this->no_ktp_dokter
                    ]
                ],
                "encounter" => [
                    "reference" => "urn:uuid:" . $this->uuid_encounter,
                ],
                "effectiveDateTime" => $this->zonawaktu,
                "issued" => $this->zonawaktu,
            ],
            $value_additional
        );

        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    public function toJsonBundle()
    {
        $data = [
            "fullUrl" => "urn:uuid:" . $this->uuid_observation,
            "resource" => json_decode($this->toJsonObservation(), true),
            "request" => [
                "method" => "POST",
                "url" => "Observation"
            ]
        ];
        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}