<?php

namespace SatuSehat\Src;

class MedicationDispense
{
    private $uuid_medication_dispense;
    private $organization_id;
    private $identifier_value;
    private $uuid_medication_for_dispense;
    private $medication_name;
    private $ihs_patient;
    private $ihs_patient_name;
    private $uuid_encounter;
    private $practitioner_id;
    private $practitioner_name;
    private $location_id;
    private $uuid_medication_request;
    private $whenPrepared;
    private $whenHanded;
    private $patient_instruction;
    private $dose_value;
    private $dose_unit;
    private $dose_system;
    private $dose_code;
    private $no;


    public function __construct(
        $uuid_medication_dispense,$organization_id,$identifier_value,$uuid_medication_for_dispense,$medication_name,$ihs_patient,$ihs_patient_name,
        $uuid_encounter,$practitioner_id,$practitioner_name,$location_id,$uuid_medication_request,
        $whenPrepared,$whenHanded,$patient_instruction,$dose_value,$dose_unit,$dose_system,$dose_code,$no
        )
    {
        $this->uuid_medication_dispense = $uuid_medication_dispense;
        $this->organization_id = $organization_id;
        $this->identifier_value = $identifier_value;
        $this->uuid_medication_for_dispense = $uuid_medication_for_dispense;
        $this->medication_name = $medication_name;
        $this->ihs_patient = $ihs_patient;
        $this->ihs_patient_name = $ihs_patient_name;
        $this->uuid_encounter = $uuid_encounter;
        $this->practitioner_id = $practitioner_id;
        $this->practitioner_name = $practitioner_name;
        $this->location_id = $location_id;
        $this->uuid_medication_request = $uuid_medication_request;
        $this->whenPrepared = $whenPrepared;
        $this->whenHanded = $whenHanded;
        $this->patient_instruction = $patient_instruction;
        $this->dose_value = $dose_value;
        $this->dose_unit = $dose_unit;
        $this->dose_system = $dose_system;
        $this->dose_code = $dose_code;
        $this->no = $no;
    }

    public function toJson()
    {
        return '{
            "fullUrl": "urn:uuid:'.$this->uuid_medication_dispense.'",
            "resource": {
                "resourceType": "MedicationDispense",
                "identifier": [
                    {
                        "use": "official",
                        "system": "http://sys-ids.kemkes.go.id/prescription/'.$this->organization_id.'",
                        "value": "'.$this->identifier_value.'"
                    },
                    {
                        "use": "official",
                        "system": "http://sys-ids.kemkes.go.id/prescription-item/'.$this->organization_id.'",
                        "value": "'.$this->identifier_value.'-'.$this->no.'"
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
                    "reference": "urn:uuid:'.$this->uuid_medication_for_dispense.'",
                    "display": "'.$this->medication_name.'"
                },
                "subject": {
                    "reference": "Patient/'.$this->ihs_patient.'",
                    "display": "'.$this->ihs_patient_name.'"
                },
                "context": {
                    "reference": "urn:uuid:'.$this->uuid_encounter.'"
                },
                "performer": [
                    {
                        "actor": {
                            "reference": "Practitioner/'.$this->practitioner_id.'",
                            "display": "Apoteker '.$this->practitioner_name.'"
                        }
                    }
                ],
                "location": {
                    "reference": "Location/'.$this->location_id.'",
                    "display": "Farmasi"
                },
                "authorizingPrescription": [
                    {
                        "reference": "urn:uuid:'.$this->uuid_medication_request.'"
                    }
                ],
                "quantity": {
                    "value": '.$this->dose_value.',
                    "system": "'.$this->dose_system.'",
                    "code": "'.$this->dose_code.'"
                },
                "whenPrepared": "'.$this->whenPrepared.'",
                "whenHandedOver": "'.$this->whenHanded.'",
                "dosageInstruction": [
                    {
                        "sequence": 1,
                        "additionalInstruction": [
                            {
                                "coding": [
                                    {
                                        "system": "http://snomed.info/sct",
                                        "code": "418577003",
                                        "display": "Take at regular intervals. Complete the prescribed course unless otherwise directed"
                                    }
                                ]
                            }
                        ],
                        "patientInstruction": "'.$this->patient_instruction.'",
                        "timing": {
                            "repeat": {
                                "frequency": 1,
                                "period": 1,
                                "periodUnit": "d"
                            }
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
                                    "value": '.$this->dose_value.',
                                    "unit": "'.$this->dose_unit.'",
                                    "system": "'.$this->dose_system.'",
                                    "code": "'.$this->dose_code.'"
                                }
                            }
                        ]
                    }
                ]
            },
            "request": {
                "method": "POST",
                "url": "MedicationDispense"
            }
        },';
    }
}