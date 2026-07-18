<?php

namespace SatuSehat\Src;

class ServiceRequest
{
    private $uuid_service_request;
    private $org_id;
    private $lab_srid_ordinal;
    private $patient_id;
    private $encounter_id;
    private $practitioner_id;
    private $practitioner_name;
    private $code_loinc;
    private $display_loinc;
    private $code_kptl;
    private $display_kptl;
    private $pemeriksaan;
    private $practitioner_lab;

    public function __construct($uuid_service_request, $org_id, $lab_srid_ordinal, $patient_id, $encounter_id, $practitioner_id, $practitioner_name ,$code_loinc, $display_loinc, $code_kptl, $display_kptl,$pemeriksaan,$practitioner_lab = null)
    {
        $this->uuid_service_request = $uuid_service_request;
        $this->org_id = $org_id;
        $this->lab_srid_ordinal = $lab_srid_ordinal;
        $this->patient_id = $patient_id;
        $this->encounter_id = $encounter_id;
        $this->practitioner_id = $practitioner_id;
        $this->practitioner_name = $practitioner_name;
        $this->code_loinc = $code_loinc;
        $this->display_loinc = $display_loinc;
        $this->code_kptl = $code_kptl;
        $this->display_kptl = $display_kptl;
        $this->pemeriksaan = $pemeriksaan;
        $this->practitioner_lab = $practitioner_lab;
    }

    public function toJson()
    {
        $data = [
            "resourceType" => "ServiceRequest",
            "identifier" => [
                [
                    "system" => "http://sys-ids.kemkes.go.id/servicerequest/" . $this->org_id,
                    "value" => $this->lab_srid_ordinal
                ]
            ],
            "status" => "active",
            "intent" => "original-order",
            "category" => [
                [
                    "coding" => [
                        [
                            "system" => "http://snomed.info/sct",
                            "code" => "108252007",
                            "display" => "Laboratory procedure"
                        ]
                    ]
                ]
            ],
            "code" => [
                "coding" => [
                    [
                        "system" => "http://loinc.org",
                        "code" => $this->code_loinc,
                        "display" => $this->display_loinc
                    ]
                ],
                "text" => "Pemeriksaan ".$this->pemeriksaan
            ],
            "subject" => [
                "reference" => "Patient/".$this->patient_id,
            ],
            "encounter" => [
                "reference" => "urn:uuid:".$this->encounter_id,
            ],
            "requester" => [
                "reference" => "Practitioner/".$this->practitioner_id,
                "display" => $this->practitioner_name
            ],
            "performer" => [
                [
                    "reference" => "Practitioner/10038040334"
                ]
            ],
        ];
        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
    public function toJsonBundle()
    {
        $data = [
            "fullUrl" => "urn:uuid:" . $this->uuid_service_request,
            "resource" => json_decode($this->toJson(), true),
            "request" => [
                "method" => "POST",
                "url" => "ServiceRequest"
            ]
        ];
        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}
