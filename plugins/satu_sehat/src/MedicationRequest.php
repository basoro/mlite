<?php

namespace SatuSehat\Src;

class MedicationRequest
{
    private $uuid_medication;
    private $uuid_medicationrequest;
    private $organization_id;
    private $identifier_value;
    private $medication_reference;
    private $ihs_patient;
    private $nama_pasien;
    private $time_authored;
    private $no_ktp_dokter;
    private $nama_dokter;
    private $uuid_condition;
    private $uuid_encounter;
    private $reason_reference;
    private $patient_instruction;
    private $route_code;
    private $route_display;
    private $dose_value;
    private $dose_unit;
    private $dose_system;
    private $dose_code;
    private $no;
    
    public function __construct(
        $uuid_medication,$uuid_medicationrequest,$organization_id,$identifier_value,$medication_reference,$ihs_patient,$nama_pasien,
        $time_authored,$no_ktp_dokter,$nama_dokter,$uuid_condition,$uuid_encounter,$reason_reference,$patient_instruction,$route_code,$route_display,
        $dose_value,$dose_unit,$dose_system,$dose_code,$no
        )
    {
        $this->uuid_medication = $uuid_medication;
        $this->uuid_medicationrequest = $uuid_medicationrequest;
        $this->organization_id = $organization_id;
        $this->identifier_value = $identifier_value;
        $this->medication_reference = $medication_reference;
        $this->ihs_patient = $ihs_patient;
        $this->nama_pasien = $nama_pasien;
        $this->time_authored = $time_authored;
        $this->no_ktp_dokter = $no_ktp_dokter;
        $this->nama_dokter = $nama_dokter;
        $this->uuid_condition = $uuid_condition;
        $this->uuid_encounter = $uuid_encounter;
        $this->reason_reference = $reason_reference;
        $this->patient_instruction = $patient_instruction;
        $this->route_code = $route_code;
        $this->route_display = $route_display;
        $this->dose_value = $dose_value;
        $this->dose_unit = $dose_unit;
        $this->dose_system = $dose_system;
        $this->dose_code = $dose_code;
        $this->no = $no;
    }

    public function toJson()
    {
        return '{
            "fullUrl": "urn:uuid:'.$this->uuid_medicationrequest.'",
            "resource": {
                "resourceType": "MedicationRequest",
                "identifier": [
                    {
                        "use": "official",
                        "system": "http://sys-ids.kemkes.go.id/prescription/'. $this->organization_id . '",
                        "value": "'.$this->identifier_value.'"
                    },
                    {
                        "use": "official",
                        "system": "http://sys-ids.kemkes.go.id/prescription-item/'. $this->organization_id . '",
                        "value": "'.$this->identifier_value.'-'.$this->no.'"
                    }
                ],
                "status": "completed",
                "intent": "order",
                "category": [
                    {
                        "coding": [
                            {
                                "system": "http://terminology.hl7.org/CodeSystem/medicationrequest-category",
                                "code": "outpatient",
                                "display": "Outpatient"
                            }
                        ]
                    }
                ],
                "priority": "routine",
                "medicationReference": {
                    "reference": "Medication/'.$this->uuid_medication.'",
                    "display": "'.$this->medication_reference.'"
                },
                "subject": {
                    "reference": "Patient/' . $this->ihs_patient . '",
                    "display": "' . $this->nama_pasien . '"
                },
                "encounter": {
                    "reference": "Encounter/' . $this->uuid_encounter . '"
                },
                "authoredOn": "'.$this->time_authored.'",
                "requester": {
                    "reference": "Practitioner/' . $this->no_ktp_dokter . '",
                    "display": "' . $this->nama_dokter . '"
                },
                "reasonReference": [
                    {
                        "reference": "Condition/' . $this->uuid_condition . '",
                        "display": "' . $this->reason_reference . '"
                    }
                ],
                "dosageInstruction": [
                    {
                        "sequence": 1,
                        "patientInstruction": "'.$this->patient_instruction.'",
                        "route": {
                            "coding": [
                                {
                                    "system": "http://www.whocc.no/atc",
                                    "code": "'.$this->route_code.'",
                                    "display": "'.$this->route_display.'"
                                }
                            ]
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
                ],
                "dispenseRequest": {
                    "dispenseInterval": {
                        "value": 1,
                        "unit": "days",
                        "system": "http://unitsofmeasure.org",
                        "code": "d"
                    },
                    "quantity": {
                        "value": '.$this->dose_value.',
                        "unit": "'.$this->dose_unit.'",
                        "system": "'.$this->dose_system.'",
                        "code": "'.$this->dose_code.'"
                    },
                    "performer": {
                        "reference": "Organization/'. $this->organization_id . '"
                    }
                }
            },
            "request": {
                "method": "POST",
                "url": "MedicationRequest"
            }
        },';
    }
}