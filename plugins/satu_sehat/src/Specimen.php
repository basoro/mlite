<?php

namespace SatuSehat\Src;

class Specimen
{
    private $uuid_specimen;
    private $org_id;
    private $patient_id;
    private $patient_name;
    private $uuid_service_request;
    private $no_rawat;
    private $waktu_pengambilan;

    public function __construct($uuid_specimen, $org_id, $patient_id, $patient_name, $uuid_service_request, $no_rawat, $waktu_pengambilan)
    {
        $this->uuid_specimen = $uuid_specimen;
        $this->org_id = $org_id;
        $this->patient_id = $patient_id;
        $this->patient_name = $patient_name;
        $this->uuid_service_request = $uuid_service_request;
        $this->no_rawat = $no_rawat;
        $this->waktu_pengambilan = $waktu_pengambilan;
    }

    public function toJson()
    {
        $data = [
            "resourceType" => "Specimen",
            "identifier" => [
                [
                    "system" => "http://sys-ids.kemkes.go.id/specimen/" . $this->org_id,
                    "value" => $this->no_rawat,
                    "assigner" => [
                        "reference" => "Organization/" . $this->org_id,
                    ]
                ]
            ],
            "status" => "available",
            "type" => [
                "coding" => [
                    [
                        "system" => "http://snomed.info/sct",
                        "code" => "119297000",
                        "display" => "Blood specimen (specimen)"
                    ]
                ]
            ],
            "processing" => [
                [
                    "procedure" => [
                        "coding" => [
                            [
                                "system" => "http://snomed.info/sct",
                                "code" => "9265001",
                                "display" => "Specimen processing"
                            ]
                        ]
                    ],
                    "timeDateTime" => $this->waktu_pengambilan,
                ]
            ],
            "subject" => [
                "reference" => "Patient/" . $this->patient_id,
                "display" => $this->patient_name
            ],
            "request" => [
                [
                    "reference" => "urn:uuid:" . $this->uuid_service_request,
                ]
            ],
            "receivedTime" => $this->waktu_pengambilan,
        ];
        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    public function toJsonBundle()
    {
        $data = [
            "fullUrl" => "urn:uuid:" . $this->uuid_specimen,
            "resource" => json_decode($this->toJson(), true),
            "request" => [
                "method" => "POST",
                "url" => "Specimen"
            ]
        ];
        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}
