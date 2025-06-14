<?php

namespace SatuSehat\Src;

class Procedure
{
    private $uuid_encounter;
    private $uuid_procedure;
    private $code_procedure;
    private $display_procedure;
    private $ihs_patient;
    private $patient_name;
    private $display_encounter;
    private $zonawaktu;
    private $no_ktp_dokter;
    private $nama_dokter;
    private $coding_code;
    private $coding_display;

    public function __construct(
        $uuid_encounter,$uuid_procedure,$code_procedure,$display_procedure,$ihs_patient,$patient_name,
        $display_encounter,$zonawaktu,$no_ktp_dokter,$nama_dokter,$coding_code,$coding_display
        )
    {
        $this->uuid_encounter = $uuid_encounter;
        $this->uuid_procedure = $uuid_procedure;
        $this->code_procedure = $code_procedure;
        $this->display_procedure = $display_procedure;
        $this->ihs_patient = $ihs_patient;
        $this->patient_name = $patient_name;
        $this->display_encounter = $display_encounter;
        $this->zonawaktu = $zonawaktu;
        $this->no_ktp_dokter = $no_ktp_dokter;
        $this->nama_dokter = $nama_dokter;
        $this->coding_code = $coding_code;
        $this->coding_display = $coding_display;
    }

    public function toJson(){
        return '{
            "fullUrl": "urn:uuid:' . $this->uuid_procedure . '",
            "resource": {
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
                    "text": "Diagnostic procedure"
                },
                "code": {
                    "coding": [
                        {
                            "system": "http://hl7.org/fhir/sid/icd-9-cm",
                            "code": "'.$this->code_procedure.'",
                            "display": "'.$this->display_procedure.'"
                        }
                    ]
                },
                "subject": {
                    "reference": "Patient/' . $this->ihs_patient . '",
                    "display": "' . $this->patient_name . '"
                },
                "encounter": {
                    "reference": "urn:uuid:' . $this->uuid_encounter . '",
                    "display": "'. $this->display_encounter . '"
                },
                "performedPeriod": {
                    "start": "' . $this->zonawaktu . '",
                    "end": "' . $this->zonawaktu . '"
                },
                "performer": [
                    {
                        "actor": {
                            "reference": "Practitioner/' . $this->no_ktp_dokter . '",
                            "display": "' . $this->nama_dokter . '"
                        }
                    }
                ],
                "reasonCode": [
                    {
                        "coding": [
                            {
                                "system": "http://hl7.org/fhir/sid/icd-10",
                                "code": "' . $this->coding_code . '",
                                "display": "' . $this->coding_display . '"
                            }
                        ]
                    }
                ]
            },
            "request": {
                "method": "POST",
                "url": "Procedure"
            }
        },';
    }
}