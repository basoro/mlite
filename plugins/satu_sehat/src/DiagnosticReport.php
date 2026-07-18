<?php

namespace SatuSehat\Src;

class DiagnosticReport
{
    private $uuid_diagnostic_report;
    private $uuid_specimen;
    private $uuid_encounter;
    private $uuid_service_request;
    private $uuid_observation_lab;
    private $no_ktp_dokter;
    private $ihs_patient;
    private $code_loinc;
    private $display_loinc;
    private $waktu_hasil;

    public function __construct($uuid_diagnostic_report, $uuid_specimen, $uuid_encounter, $uuid_service_request, $uuid_observation_lab, $no_ktp_dokter, $ihs_patient, $code_loinc = null, $display_loinc = null,$waktu_hasil)
    {
        $this->uuid_diagnostic_report = $uuid_diagnostic_report;
        $this->uuid_specimen = $uuid_specimen;
        $this->uuid_encounter = $uuid_encounter;
        $this->uuid_service_request = $uuid_service_request;
        $this->no_ktp_dokter = $no_ktp_dokter;
        $this->ihs_patient = $ihs_patient;
        $this->code_loinc = $code_loinc;
        $this->display_loinc = $display_loinc;
        $this->waktu_hasil = $waktu_hasil;
        $this->uuid_observation_lab = $uuid_observation_lab;
    }

    public function toJson()
    {

        $data = [
            "resourceType" => "DiagnosticReport",
            "status" => "final",
            "code" => [
                "coding" => [
                    [
                        "system" => "http://loinc.org",
                        "code" => $this->code_loinc,
                        "display" => $this->display_loinc
                    ]
                ]
            ],
            "subject" => [
                "reference" => "Patient/". $this->ihs_patient,
            ],
            "encounter" => [
                "reference" => "urn:uuid:".$this->uuid_encounter
            ],
            "effectiveDateTime" => $this->waktu_hasil,
            "issued" => $this->waktu_hasil,
            "performer" => [
                [
                    "reference" => "Practitioner/" . $this->no_ktp_dokter,
                ]
            ],
            "specimen" => [
                [
                    "reference" => "urn:uuid:" . $this->uuid_specimen
                ]
            ],
            "basedOn" => [
                [
                    "reference" => "urn:uuid:". $this->uuid_service_request
                ]
            ],
            "result" => [
                [
                    "reference" => "urn:uuid:" . $this->uuid_observation_lab
                ]
            ],
        ];
        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    public function toJsonBundle()
    {
        $data = [
            "fullUrl" => "urn:uuid:" . $this->uuid_diagnostic_report,
            "resource" => json_decode($this->toJson(), true),
            "request" => [
                "method" => "POST",
                "url" => "DiagnosticReport"
            ]
        ];
        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}