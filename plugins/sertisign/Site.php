<?php
namespace Plugins\Sertisign;

use Systems\SiteModule;

class Site extends SiteModule
{
    public function routes()
    {
        $this->route('sertisign/webhook', 'getWebhook');
    }

    public function getWebhook()
    {
        if($_SERVER['REQUEST_METHOD'] == 'GET') {
            header("HTTP/1.0 404 Not Found");
            die();
        }
        $param = [];
        $pPost = $_POST;

        function isJson($string) {
            json_decode($string);
            return json_last_error() === JSON_ERROR_NONE;
        }

        if(!empty($pPost)){
            $param = json_encode($pPost);
        }
        $pRaw = file_get_contents('php://input');
            if(isJson($pRaw)){
            $param = array_merge($param, json_decode($pRaw, true));
        }
        if(!isset($param[0]['transaction_id']) || empty($param[0]['transaction_id'])){
            echo json_encode(['error' => 'transaction_id tidak ditemukan']);
            die();
        }

        file_put_contents($param[0]['transaction_id'].'.txt', json_encode($param, true).PHP_EOL, FILE_APPEND);
        file_put_contents($param[0]['transaction_id'].'.html', '<pre>'.json_encode($param, JSON_PRETTY_PRINT).'</pre>'.PHP_EOL, FILE_APPEND);

        $remote = fopen($param[0]['document_url'], 'r');
        $local  = fopen($param[0]['transaction_id'].'.pdf', 'w');

        stream_copy_to_stream($remote, $local);

        $logFile = (empty($_SERVER['HTTPS']) ? 'http' : 'https')."://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"].'/'.$param[0]['transaction_id'].'.txt';
        $pdfFile = (empty($_SERVER['HTTPS']) ? 'http' : 'https')."://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"].'/'.$param[0]['transaction_id'].'.pdf';
        echo json_encode(['error' => 'transaction_id tidak ditemukan']);

        $data['Transaction ID'] = $param[0]['transaction_id'];
        $data['Log File'] = $logFile;
        $data['Pdf File'] = $pdfFile;
        print_r(json_encode($data));   

    }
}
