<?php

namespace SatuSehat\Src;

class Temperature
{
    private $uuid_encounter;
    private $uuid_suhu;
    private $ihs_patient;
    private $no_ktp_dokter;
    private $suhu;
    private $display_encounter_temp;
    private $code_interpretation;
    private $display_interpretation;
    private $text_interpretation;
    private $zonawaktu;

    public function __construct($uuid_encounter,$uuid_suhu,$ihs_patient,$no_ktp_dokter,$suhu,$display_encounter_temp,$code_interpretation,$display_interpretation,$text_interpretation,$zonawaktu)
    {
        $this->uuid_encounter = $uuid_encounter;
        $this->uuid_suhu = $uuid_suhu;
        $this->ihs_patient = $ihs_patient;
        $this->no_ktp_dokter = $no_ktp_dokter;
        $this->suhu = $suhu;
        $this->display_encounter_temp = $display_encounter_temp;
        $this->code_interpretation = $code_interpretation;
        $this->display_interpretation = $display_interpretation;
        $this->text_interpretation = $text_interpretation;
        $this->zonawaktu = $zonawaktu;
    }

    public function toJson()
    {
        return '{
        "fullUrl": "urn:uuid:' . $this->uuid_suhu . '",
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
                      "code": "8310-5",
                      "display": "Body temperature"
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
                "display": "'.$this->display_encounter_temp.'"
            },
            "effectiveDateTime": "' . $this->zonawaktu . '",
            "issued": "' . $this->zonawaktu . '",
            "valueQuantity": {
                "value": '.$this->suhu.',
                "unit": "C",
                "system": "http://unitsofmeasure.org",
                "code": "Cel"
            },
            "interpretation": [
              {
                  "coding": [
                      {
                          "system": "http://terminology.hl7.org/CodeSystem/v3-ObservationInterpretation",
                          "code": "'.$this->code_interpretation.'",
                          "display": "'.$this->display_interpretation.'"
                      }
                  ],
                  "text": "Di '.$this->text_interpretation.' nilai referensi"
              }
          ]
        },
        "request": {
            "method": "POST",
            "url": "Observation"
        }
      },';
    }
}