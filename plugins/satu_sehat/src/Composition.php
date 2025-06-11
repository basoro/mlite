<?php

namespace SatuSehat\Src;

class Composition
{
    private $uuid_encounter;
    private $uuid_composition;
    private $ihs_patient;
    private $no_ktp_dokter;
    private $nama_pasien;
    private $nama_dokter;
    private $no_rawat;
    private $org_id;
    private $display_encounter;
    private $zonawaktu;

    public function __construct($uuid_encounter, $uuid_composition, $ihs_patient, $no_ktp_dokter, $nama_pasien, $nama_dokter, $no_rawat, $org_id, $display_encounter, $zonawaktu)
    {
        $this->uuid_encounter = $uuid_encounter;
        $this->uuid_composition = $uuid_composition;
        $this->ihs_patient = $ihs_patient;
        $this->no_ktp_dokter = $no_ktp_dokter;
        $this->nama_pasien = $nama_pasien;
        $this->nama_dokter = $nama_dokter;
        $this->no_rawat = $no_rawat;
        $this->org_id = $org_id;
        $this->display_encounter = $display_encounter;
        $this->zonawaktu = $zonawaktu;
    }

    public function toJson()
    {
        return '{
            "fullUrl": "urn:uuid:' . $this->uuid_composition . '",
            "resource": {
                "resourceType": "Composition",
                "identifier": {
                    "system": "http://sys-ids.kemkes.go.id/composition/' . $this->org_id . '",
                    "value": "' . $this->no_rawat . '"
                },
                "status": "final",
                "type": {
                    "coding": [
                        {
                            "system": "http://loinc.org",
                            "code": "18842-5",
                            "display": "Discharge summary"
                        }
                    ]
                },
                "category": [
                    {
                        "coding": [
                            {
                                "system": "http://loinc.org",
                                "code": "LP173421-1",
                                "display": "Report"
                            }
                        ]
                    }
                ],
                "subject": {
                    "reference": "Patient/' . $this->ihs_patient . '",
                    "display": "' . $this->nama_pasien . '"
                },
                "encounter": {
                    "reference": "urn:uuid:' . $this->uuid_encounter . '",
                    "display": "'.$this->display_encounter.'"
                },
                "date": "' . $this->zonawaktu . '",
                "author": [
                    {
                        "reference": "Practitioner/' . $this->no_ktp_dokter . '",
                        "display": "' . $this->nama_dokter . '"
                    }
                ],
                "title": "Resume Medis Rawat Jalan",
                "custodian": {
                    "reference": "Organization/' . $this->org_id . '"
                }
            },
            "request": {
                "method": "POST",
                "url": "Composition"
            }
        }';
    }
}
