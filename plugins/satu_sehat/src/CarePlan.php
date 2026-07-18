<?php

namespace SatuSehat\Src;

class CarePlan
{
    private $patient_id;
    private $uuid_encounter;
    private $uuid_careplan;
    private $instruction;
    private $title;
    private $no_ktp_dokter;

    public function __construct($patient_id,$uuid_encounter,$uuid_careplan,$instruction,$title,$no_ktp_dokter)
    {
        $this->patient_id = $patient_id;
        $this->uuid_encounter = $uuid_encounter;
        $this->uuid_careplan = $uuid_careplan;
        $this->instruction = $instruction;
        $this->title = $title;
        $this->no_ktp_dokter = $no_ktp_dokter;
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
            "title" => $this->title,
            "description" => $this->instruction,
            "subject" => [
                "reference" => "Patient/".$this->patient_id,
            ],
            "encounter" => [
                "reference" => "urn:uuid:".$this->uuid_encounter,
            ],
            "author" => [
                "reference" => "Practitioner/".$this->no_ktp_dokter,
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