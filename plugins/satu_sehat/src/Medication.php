<?php

namespace SatuSehat\Src;

class Medication
{
    private $uuid_medication;
    private $identifier_org;
    private $identifier_value;
    private $kfa_coding;
    private $kfa_display;
    private $form_coding;
    private $form_display;
    private $ingredient_coding;
    private $ingredient_display;
    private $numerator_value;
    private $numerator_code;
    private $denominator_value;
    private $denominator_system;
    private $denominator_code;
    private $no;

    public function __construct(
        $uuid_medication,$identifier_org,$identifier_value,$kfa_coding,$kfa_display,$form_coding,$form_display,$ingredient_coding,$ingredient_display,
        $numerator_code,$numerator_value,$denominator_code,$denominator_system,$denominator_value,$no
        )
    {
        $this->uuid_medication = $uuid_medication;
        $this->identifier_org = $identifier_org;
        $this->identifier_value = $identifier_value;
        $this->kfa_coding = $kfa_coding;
        $this->kfa_display = $kfa_display;
        $this->form_coding = $form_coding;
        $this->form_display = $form_display;
        $this->ingredient_coding = $ingredient_coding;
        $this->ingredient_display = $ingredient_display;
        $this->numerator_code = $numerator_code;
        $this->numerator_value = $numerator_value;
        $this->denominator_code = $denominator_code;
        $this->denominator_system = $denominator_system;
        $this->denominator_value = $denominator_value;
        $this->no = $no;
    }

    public function toJson()
    {
        return '{
            "fullUrl": "urn:uuid:'.$this->uuid_medication.'",
            "resource": {
                "resourceType": "Medication",
                "meta": {
                    "profile": [
                        "https://fhir.kemkes.go.id/r4/StructureDefinition/Medication"
                    ]
                },
                "extension": [
                    {
                        "url": "https://fhir.kemkes.go.id/r4/StructureDefinition/MedicationType",
                        "valueCodeableConcept": {
                            "coding": [
                                {
                                    "system": "http://terminology.kemkes.go.id/CodeSystem/medication-type",
                                    "code": "NC",
                                    "display": "Non-compound"
                                }
                            ]
                        }
                    }
                ],
                "identifier": [
                    {
                        "use": "official",
                        "system": "http://sys-ids.kemkes.go.id/medication/'. $this->identifier_org . '",
                        "value": "'.$this->identifier_value.'-'.$this->no.'"
                    }
                ],
                "code": {
                    "coding": [
                        {
                            "system": "http://sys-ids.kemkes.go.id/kfa",
                            "code": "'.$this->kfa_coding.'",
                            "display": "'.$this->kfa_display.'"
                        }
                    ]
                },
                "status": "active",
                "form": {
                    "coding": [
                        {
                            "system": "http://terminology.kemkes.go.id/CodeSystem/medication-form",
                            "code": "'.$this->form_coding.'",
                            "display": "'.$this->form_display.'"
                        }
                    ]
                },
                "ingredient": [
                    {
                        "itemCodeableConcept": {
                            "coding": [
                                {
                                    "system": "http://sys-ids.kemkes.go.id/kfa",
                                    "code": "'.$this->ingredient_coding.'",
                                    "display": "'.$this->ingredient_display.'"
                                }
                            ]
                        },
                        "isActive": true,
                        "strength": {
                            "numerator": {
                                "value": '.$this->numerator_value.',
                                "system": "http://unitsofmeasure.org",
                                "code": "'.$this->numerator_code.'"
                            },
                            "denominator": {
                                "value": '.$this->denominator_value.',
                                "system": "'.$this->denominator_system.'",
                                "code": "'.$this->denominator_code.'"
                            }
                        }
                    }
                ]
            },
            "request": {
                "method": "POST",
                "url": "Medication"
            }
        },';
    }
}