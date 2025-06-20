<?php

namespace SatuSehat\Src;

class CarePlan
{
    private $patient_id;
    private $uuid_encounter;
    private $uuid_careplan;

    public function __construct($patient_id,$uuid_encounter,$uuid_careplan)
    {
        $this->patient_id = $patient_id;
        $this->uuid_encounter = $uuid_encounter;
        $this->uuid_careplan = $uuid_careplan;
    }

    public function toJson()
    {
        $data = [
            "resourceType" => "CarePlan",
            "status" => "active",
            "intent" => "plan",
            "category" => [
                [
                    "coding" => [
                        [
                            "system" => "http://snomed.info/sct",
                            "code" => "736271009",
                            "display" => "Outpatient care plan"
                        ]
                    ]
                ]
            ],
            "description" => "Pasien Rawat Inap",
            "subject" => [
                "reference" => "Patient/".$this->patient_id,
            ],
            "encounter" => [
                "reference" => "urn:uuid:".$this->uuid_encounter,
            ]
        ];
        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
    public function toJsonBundle()
    {
        $data = [
            "fullUrl" => "urn:uuid:" . $this->uuid_careplan,
            "resource" => json_decode($this->toJson(), true),
            "request" => [
                "method" => "POST",
                "url" => "CarePlan"
            ]
        ];
        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}