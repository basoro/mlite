<?php

namespace SatuSehat\Src;

class QuestionareMedication
{

    private $uuid_questionare;
    private $patient_ihs;
    private $patient_name;
    private $practitioner_code;
    private $practitioner_name;
    private $uuid_encounter;

    public function __construct($uuid_questionare,$uuid_encounter,$patient_ihs,$patient_name,$practitioner_code,$practitioner_name)
    {
        $this->uuid_questionare = $uuid_questionare;
        $this->uuid_encounter = $uuid_encounter;
        $this->patient_ihs = $patient_ihs;
        $this->patient_name = $patient_name;
        $this->practitioner_code = $practitioner_code;
        $this->practitioner_name = $practitioner_name;
    }

    public function toJson() 
    {
        return '{
            "fullUrl": "urn:uuid:'.$this->uuid_questionare.'",
            "resource": {
                "resourceType": "QuestionnaireResponse",
                "questionnaire": "https://fhir.kemkes.go.id/Questionnaire/Q0007",
                "status": "completed",
                "subject": {
                    "reference": "Patient/'.$this->patient_ihs.'",
                    "display": "'.$this->patient_name.'"
                },
                "encounter": {
                    "reference": "urn:uuid:'.$this->uuid_encounter.'"
                },
                "authored": "2023-08-31T03:00:00+00:00",
                "author": {
                    "reference": "Practitioner/'.$this->practitioner_code.'",
                    "display": "Apoteker '.$this->practitioner_name.'"
                },
                "source": {
                    "reference": "Patient/'.$this->patient_ihs.'"
                },
                "item": [
                    {
                        "linkId": "1",
                        "text": "Persyaratan Administrasi",
                        "item": [
                            {
                                "linkId": "1.1",
                                "text": "Apakah nama, umur, jenis kelamin, berat badan dan tinggi badan pasien sudah sesuai?",
                                "answer": [
                                    {
                                        "valueCoding": {
                                            "system": "http://terminology.kemkes.go.id/CodeSystem/clinical-term",
                                            "code": "OV000052",
                                            "display": "Sesuai"
                                        }
                                    }
                                ]
                            },
                            {
                                "linkId": "1.2",
                                "text": "Apakah nama, nomor ijin, alamat dan paraf dokter sudah sesuai?",
                                "answer": [
                                    {
                                        "valueCoding": {
                                            "system": "http://terminology.kemkes.go.id/CodeSystem/clinical-term",
                                            "code": "OV000052",
                                            "display": "Sesuai"
                                        }
                                    }
                                ]
                            },
                            {
                                "linkId": "1.3",
                                "text": "Apakah tanggal resep sudah sesuai?",
                                "answer": [
                                    {
                                        "valueCoding": {
                                            "system": "http://terminology.kemkes.go.id/CodeSystem/clinical-term",
                                            "code": "OV000052",
                                            "display": "Sesuai"
                                        }
                                    }
                                ]
                            },
                            {
                                "linkId": "1.4",
                                "text": "Apakah ruangan/unit asal resep sudah sesuai?",
                                "answer": [
                                    {
                                        "valueCoding": {
                                            "system": "http://terminology.kemkes.go.id/CodeSystem/clinical-term",
                                            "code": "OV000052",
                                            "display": "Sesuai"
                                        }
                                    }
                                ]
                            },
                            {
                                "linkId": "2",
                                "text": "Persyaratan Farmasetik",
                                "item": [
                                    {
                                        "linkId": "2.1",
                                        "text": "Apakah nama obat, bentuk dan kekuatan sediaan sudah sesuai?",
                                        "answer": [
                                            {
                                                "valueCoding": {
                                                    "system": "http://terminology.kemkes.go.id/CodeSystem/clinical-term",
                                                    "code": "OV000052",
                                                    "display": "Sesuai"
                                                }
                                            }
                                        ]
                                    },
                                    {
                                        "linkId": "2.2",
                                        "text": "Apakah dosis dan jumlah obat sudah sesuai?",
                                        "answer": [
                                            {
                                                "valueCoding": {
                                                    "system": "http://terminology.kemkes.go.id/CodeSystem/clinical-term",
                                                    "code": "OV000052",
                                                    "display": "Sesuai"
                                                }
                                            }
                                        ]
                                    },
                                    {
                                        "linkId": "2.3",
                                        "text": "Apakah stabilitas obat sudah sesuai?",
                                        "answer": [
                                            {
                                                "valueCoding": {
                                                    "system": "http://terminology.kemkes.go.id/CodeSystem/clinical-term",
                                                    "code": "OV000052",
                                                    "display": "Sesuai"
                                                }
                                            }
                                        ]
                                    },
                                    {
                                        "linkId": "2.4",
                                        "text": "Apakah aturan dan cara penggunaan obat sudah sesuai?",
                                        "answer": [
                                            {
                                                "valueCoding": {
                                                    "system": "http://terminology.kemkes.go.id/CodeSystem/clinical-term",
                                                    "code": "OV000052",
                                                    "display": "Sesuai"
                                                }
                                            }
                                        ]
                                    }
                                ]
                            },
                            {
                                "linkId": "3",
                                "text": "Persyaratan Klinis",
                                "item": [
                                    {
                                        "linkId": "3.1",
                                        "text": "Apakah ketepatan indikasi, dosis, dan waktu penggunaan obat sudah sesuai?",
                                        "answer": [
                                            {
                                                "valueCoding": {
                                                    "system": "http://terminology.kemkes.go.id/CodeSystem/clinical-term",
                                                    "code": "OV000052",
                                                    "display": "Sesuai"
                                                }
                                            }
                                        ]
                                    },
                                    {
                                        "linkId": "3.2",
                                        "text": "Apakah terdapat duplikasi pengobatan?",
                                        "answer": [
                                            {
                                                "valueBoolean": false
                                            }
                                        ]
                                    },
                                    {
                                        "linkId": "3.3",
                                        "text": "Apakah terdapat alergi dan reaksi obat yang tidak dikehendaki (ROTD)?",
                                        "answer": [
                                            {
                                                "valueBoolean": false
                                            }
                                        ]
                                    },
                                    {
                                        "linkId": "3.4",
                                        "text": "Apakah terdapat kontraindikasi pengobatan?",
                                        "answer": [
                                            {
                                                "valueBoolean": false
                                            }
                                        ]
                                    },
                                    {
                                        "linkId": "3.5",
                                        "text": "Apakah terdapat dampak interaksi obat?",
                                        "answer": [
                                            {
                                                "valueBoolean": false
                                            }
                                        ]
                                    }
                                ]
                            }
                        ]
                    }
                ]
            },
            "request": {
                "method": "POST",
                "url": "QuestionnaireResponse"
            }
        },';
    }
}