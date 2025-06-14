<?php

namespace SatuSehat\Src;

class Condition
{
    private $uuid_encounter;
    private $uuid_condition;
    private $code;
    private $display;
    private $patient_ihs;
    private $patient_display;
    private $display_encounter;

    public function __construct($uuid_encounter, $uuid_condition, $code, $display, $patient_ihs, $patient_display, $display_encounter)
    {
        $this->uuid_encounter = $uuid_encounter;
        $this->uuid_condition = $uuid_condition;
        $this->code = $code;
        $this->display = $display;
        $this->patient_ihs = $patient_ihs;
        $this->patient_display = $patient_display;
        $this->display_encounter = $display_encounter;
    }

    public function toJson()
    {
        return '{
            "fullUrl": "urn:uuid:' . $this->uuid_condition . '",
            "resource": {
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
                            "code": "' . $this->code . '",
                            "display": "' . $this->display . '"
                        }
                    ]
                },
                "subject": {
                    "reference": "Patient/' . $this->patient_ihs . '",
                    "display": "' . $this->patient_display . '"
                },
                "encounter": {
                    "reference": "urn:uuid:' . $this->uuid_encounter . '",
                    "display": "' . $this->display_encounter . '"
                }
            },
            "request": {
                "method": "POST",
                "url": "Condition"
            }
        },';
    }
}