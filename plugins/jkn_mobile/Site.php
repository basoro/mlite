<?php

namespace Plugins\JKN_Mobile;

use Systems\SiteModule;

class Site extends SiteModule
{
    protected $foo;

    public function init()
    {
    }

    public function routes()
    {
        $this->route('jknmobile', 'getIndex');
        $this->route('jknmobile/V1', '_getV1');
        $this->route('jknmobile/V1/token', '_getV1Token');
        $this->route('jknmobile/V1/antrian', '_getV1Antrian');
        $this->route('jknmobile/V1/rekapantrian', '_getV1RekapAntrian');
        $this->route('jknmobile/V1/operasi', '_getV1Operasi');
        $this->route('jknmobile/V1/daftaroperasi', '_getV1DaftarOperasi');
    }

    public function getIndex()
    {
        $page = [
            'title' => 'JKN Mobile API',
            'desc' => 'API untuk Bridging SIMRS Khanza dengan JKN Mobile',
            'content' => '<div style="padding-top:20px;"><center>Selamat Datang di API '.$this->core->getSettings('nama_instansi').' Antrean BPJS Mobile JKN<br><br> <a href="'.url('jknmobile/V1').'" class="btn btn-primary">Katalog V1</a><br/><br/></center></div>'
        ];

        $this->setTemplate('index.html');
        $this->tpl->set('page', $page);
    }

    public function _getV1()
    {
        $page = [
            'content' => $this->draw('v1.html', ['referensi_poli' => $this->db('maping_poli_bpjs')->toArray()])
        ];

        $this->setTemplate('canvas.html');
        $this->tpl->set('page', $page);
    }

    public function _getV1Token()
    {
        $page = [
            'content' => $this->_resultV1Token()
        ];

        $this->setTemplate('canvas.html');
        $this->tpl->set('page', $page);
    }

    private function _resultV1Token()
    {
        header("Content-Type: application/json");
        $konten = trim(file_get_contents("php://input"));
        $decode = json_decode($konten, true);
        $response = array();
        if ($decode['username'] == $this->options->get('jkn_mobile.username') && $decode['password'] == $this->options->get('jkn_mobile.password')) {
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

    public function _getV1Antrian()
    {
        $page = [
            'content' => 'Antrian'
        ];

        $this->setTemplate('canvas.html');
        $this->tpl->set('page', $page);
    }

    public function _getV1RekapAntrian()
    {
        $page = [
            'content' => 'Rekan Antrian'
        ];

        $this->setTemplate('canvas.html');
        $this->tpl->set('page', $page);
    }

    public function _getV1Operasi()
    {
        $page = [
            'content' => 'Operasi'
        ];

        $this->setTemplate('canvas.html');
        $this->tpl->set('page', $page);
    }

    public function _getV1DaftarOperasi()
    {
        $page = [
            'content' => 'Daftar Operasi'
        ];

        $this->setTemplate('canvas.html');
        $this->tpl->set('page', $page);
    }

    private function _getToken()
    {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode(['username' => $this->options->get('jkn_mobile.username'), 'password' => $this->options->get('jkn_mobile.password'), 'date' => strtotime(date('Y-m-d')) * 1000]);
        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, 'abC123!', true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
        return $jwt;
    }
}
