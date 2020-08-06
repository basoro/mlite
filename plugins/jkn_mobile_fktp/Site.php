<?php

namespace Plugins\JKN_Mobile_FKTP;

use Systems\SiteModule;
use Systems\Lib\BpjsRequest;

class Site extends SiteModule
{

    public function routes()
    {
        $this->route('jknmobilefktp', 'getIndex');
        $this->route('jknmobilefktp/auth', 'getAuth');
        $this->route('jknmobilefktp/antrean', 'getAntrean');
        $this->route('jknmobilefktp/antrean/status/(:str)/(:str)', 'getStatusAntrean');
        $this->route('jknmobilefktp/peserta', 'getPeserta');
    }

    public function getIndex()
    {
        $page = [
            'content' => $this->draw('index.html', ['referensi_poli' => $this->db('maping_poli_bpjs')->toArray()])
        ];

        $this->setTemplate('canvas.html');
        $this->tpl->set('page', $page);
    }

    public function getAuth()
    {
        $page = [
            'content' => $this->_resultAuth()
        ];

        $this->setTemplate('canvas.html');
        $this->tpl->set('page', $page);
    }

    private function _resultAuth()
    {
        header("Content-Type: application/json");
        $header = apache_request_headers();
        $response = array();
        if ($header[$this->options->get('jkn_mobile_fktp.header_username')] == $this->options->get('jkn_mobile_fktp.username') && $header[$this->options->get('jkn_mobile_fktp.header_password')] == $this->options->get('jkn_mobile_fktp.password')) {
            $response = array(
                'response' => array(
                    'token' => $this->_getToken()
                ),
                'metadata' => array(
                    'message' => 'Ok',
                    'code' => 200
                )
            );
        } else {
            $response = array(
                'metadata' => array(
                    'message' => 'Access denied',
                    'code' => 401
                )
            );
        }
        echo json_encode(array("response" => $response));
    }

    public function getAntrean()
    {
        $page = [
            'content' => $this->_resultAntrean()
        ];

        $this->setTemplate('canvas.html');
        $this->tpl->set('page', $page);
    }

    private function _resultAntrean()
    {
        header("Content-Type: application/json");
        $header = apache_request_headers();
        $decode = json_decode($konten, true);
        $response = array();
        if ($header[$this->options->get('jkn_mobile_fktp.header')] == $this->_getToken()) {
        } else {
            $response = array(
                'metadata' => array(
                    'message' => 'Access denied',
                    'code' => 401
                )
            );
        }
        echo json_encode($response);
    }

    public function getStatusAntrean()
    {
        $page = [
            'content' => $this->_resultStatusAntrean()
        ];

        $this->setTemplate('canvas.html');
        $this->tpl->set('page', $page);
    }

    private function _resultStatusAntrean()
    {
        header("Content-Type: application/json");
        $slug = parseURL();
        if(count($slug) == 4) {$n = 0;}
        if(count($slug) == 5) {$n = 1;}
        if(count($slug) == 6) {$n = 2;}
        if(count($slug) == 7) {$n = 3;}
        $header = apache_request_headers();
        $response = array();
        if ($slug[(1+$n)] == 'status' && $header[$this->options->get('jkn_mobile_fktp.header')] == $this->_getToken() && $header[$this->options->get('jkn_mobile_fktp.header_username')] == $this->options->get('jkn_mobile_fktp.username')) {
            $data = $this->db('reg_periksa')
              ->select('poliklinik.nm_poli')
              ->select(['total_antrean' => 'COUNT(DISTINCT reg_periksa.no_rawat)'])
              ->select(['sisa_antrean' => 'SUM(CASE WHEN reg_periksa.stts=\'Belum\' THEN 1 ELSE 0 END)'])
              ->join('poliklinik', 'poliklinik.kd_poli = reg_periksa.kd_poli')
              ->join('maping_poliklinik_pcare', 'maping_poliklinik_pcare.kd_poli_rs = reg_periksa.kd_poli')
              ->where('reg_periksa.tgl_registrasi', $slug[(3+$n)])
              ->where('maping_poliklinik_pcare.kd_poli_pcare', $slug[(2+$n)])
              ->oneArray();
            $get_no_reg = $this->db('reg_periksa')
              ->select('reg_periksa.no_reg')
              ->join('maping_poliklinik_pcare', 'maping_poliklinik_pcare.kd_poli_pcare = reg_periksa.kd_poli')
              ->where('reg_periksa.tgl_registrasi', $slug[(3+$n)])
              ->where('maping_poliklinik_pcare.kd_poli_pcare', $slug[(2+$n)])
              ->where('reg_periksa.stts', 'Berkas Diterima')
              ->limit(1)
              ->oneArray();
            $data['antrean_panggil'] = '000';
            if(!empty($get_no_reg['no_reg'])) {
               $data['antrean_panggil'] = $get_no_reg['no_reg'];
            }

            if ($data['nm_poli'] != '') {
                $response = array(
                    'response' => array(
                        'namapoli' => $data['nm_poli'],
                        'totalantrean' => $data['total_antrean'],
                        'sisaantrean' => $data['sisa_antrean'],
                        'antreanpanggil' => $data['antrean_panggil'],
                        'keterangan' => 'Datanglah Minimal 30 Menit, jika no antrian anda terlewat, silakan konfirmasi ke bagian Pendaftaran atau Perawat Poli, Terima Kasih.'
                    ),
                    'metadata' => array(
                        'message' => 'Ok',
                        'code' => 200
                    )
                );
            } else {
                $response = array(
                    'metadata' => array(
                        'message' => 'Maaf belum Ada Antrian ditanggal ' . $slug[(3+$n)],
                        'code' => 201
                    )
                );
            }
        } else {
            $response = array(
                'metadata' => array(
                    'message' => 'Access denied',
                    //'message' => count($slug).' - '.print_r($slug),
                    'code' => 401
                )
            );
        }
        echo json_encode($response);
    }

    private function _setUmur($tanggal)
    {
        list($cY, $cm, $cd) = explode('-', date('Y-m-d'));
        list($Y, $m, $d) = explode('-', date('Y-m-d', strtotime($tanggal)));
        $umur = $cY - $Y;
        return $umur;
    }

    private function _getToken()
    {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode(['username' => $this->options->get('jkn_mobile_fktp.username'), 'password' => $this->options->get('jkn_mobile_fktp.password'), 'date' => strtotime(date('Y-m-d')) * 1000]);
        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, 'abC123!', true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
        return $jwt;
    }
    private function _getErrors($error)
    {
        $errors = $error;
        return $errors;
    }
}
