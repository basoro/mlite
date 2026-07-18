<?php

namespace SatuSehat\Src;

class ClinicalImpression
{
    private $org_id;
    private $uuid_clinical_impression;
    private $no_rawat;
    private $ihs_patient;
    private $nama_pasien;
    private $uuid_encounter;
    private $keluhan_utama;
    private $status_clinical_impression;

    public function __construct($org_id, $uuid_clinical_impression, $no_rawat, $ihs_patient, $nama_pasien, $uuid_encounter, $keluhan_utama, $status_clinical_impression)
    {
        $this->org_id = $org_id;
        $this->uuid_clinical_impression = $uuid_clinical_impression;
        $this->no_rawat = $no_rawat;
        $this->ihs_patient = $ihs_patient;
        $this->nama_pasien = $nama_pasien;
        $this->uuid_encounter = $uuid_encounter;
        $this->keluhan_utama = $keluhan_utama;
        $this->status_clinical_impression = $status_clinical_impression;
    }

    public function toJson()
    {
        $prognosis = $this->status_clinical_impression === 'completed' ? $this->completed() : [];
        $coding = $this->status_clinical_impression === 'in-progress' ? $this->inProgress() : [];
        $data = array_merge(
            [
                "resourceType" => "ClinicalImpression",
                "status" => $this->status_clinical_impression,
            ],
            $coding,
            [
                "identifier" => [
                    [
                        "use" => "official",
                        "system" => "http://sys-ids.kemkes.go.id/clinicalimpression/" . $this->org_id,
                        "value" => $this->no_rawat
                    ]
                ],
                "subject" => [
                    "reference" => "Patient/" . $this->ihs_patient,
                    "display" => $this->nama_pasien
                ],
                "encounter" => [
                    "reference" => "Encounter/" . $this->uuid_encounter
                ],
            ],
            $prognosis,
            [
                "summary" => $this->keluhan_utama,
            ]
        );
        return $data;
    }

    public function inProgress()
    {
        $data = [
            "code" => [
                "coding" => [
                    [
                        "system" => "http://snomed.info/sct",
                        "code" => "312850006",
                        "display" => "History of disorder"
                    ]
                ]
            ],
            "prognosisCodeableConcept" => [
                [
                    "coding" => [
                        [
                            "system" => "http://snomed.info/sct",
                            "code" => "65872000",
                            "display" => "Fair prognosis"
                        ]
                    ]
                ]
            ]
        ];
        return $data;
    }

    public function completed()
    {
        $data = [
            "code" => [
                "coding" => [
                    [
                        "system" => "http://snomed.info/sct",
                        "code" => "20481000",
                        "display" => "Determination of prognosis"
                    ]
                ]
            ],
            "prognosisCodeableConcept" => [
                [
                    "coding" => [
                        [
                            "system" => "http://snomed.info/sct",
                            "code" => "65872000",
                            "display" => "Fair prognosis"
                        ]
                    ]
                ]
            ]
        ];
        return $data;
    }

    public function toJsonBundle()
    {
        $data = [
            "fullUrl" => "urn:uuid:" . $this->uuid_clinical_impression,
            "resource" => $this->toJson(),
            "request" => [
                "method" => "POST",
                "url" => "ClinicalImpression"
            ]
        ];
        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}