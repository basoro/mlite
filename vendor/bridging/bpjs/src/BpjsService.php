<?php

namespace Bridging\Bpjs;

use GuzzleHttp\Client;
use LZCompressor\LZString;

class BpjsService
{

    /**
     * Guzzle HTTP Client object
     * @var \GuzzleHttp\Client
     */
    private $clients;

    /**
     * Request headers
     * @var array
     */
    private $headers;

    /**
     * X-cons-id header value
     * @var int
     */
    private $cons_id;

    /**
     * X-Timestamp header value
     * @var string
     */
    private $timestamp;

    /**
     * X-Signature header value
     * @var string
     */
    private $signature;

    /**
     * @var string
     */
    private $secret_key;

    /**
     * @var string
     */
    private $user_key;

    /**
     * @var string
     */
    private $base_url;

    /**
     * @var string
     */
    private $service_name;
    private $timeout = 0;

    public function __construct($configurations)
    {
        $this->clients = new Client([
            'verify' => false
        ]);

        foreach ($configurations as $key => $val) {
            if (property_exists($this, $key)) {
                $this->$key = $val;
            }
        }

        //set X-Timestamp, X-Signature, and finally the headers
        $this->setTimestamp()->setSignature()->setHeaders();
    }

    protected function setHeaders()
    {
        $this->headers = [
            'X-cons-id' => $this->cons_id,
            'X-Timestamp' => $this->timestamp,
            'X-Signature' => $this->signature,
            'user_key' => $this->user_key
        ];
        return $this;
    }

    protected function setTimestamp()
    {
        $dateTime = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->timestamp = (string)$dateTime->getTimestamp();
        return $this;
    }

    protected function setSignature()
    {
        $data = $this->cons_id . '&' . $this->timestamp;
        $signature = hash_hmac('sha256', $data, $this->secret_key, true);
        $encodedSignature = base64_encode($signature);
        $this->signature = $encodedSignature;
        return $this;
    }

    private function _getDecryptionKey()
    {
        return $this->cons_id . $this->secret_key . $this->timestamp;
    }

    // returns response object or false
    private function _request($method, $feature, $data = [], $headers = [])
    {
        if (!in_array($method, ['GET', 'POST', 'PUT', 'DELETE']))
            return false;

        if (in_array($method, ['POST', 'PUT', 'DELETE']))
            $this->headers['Content-Type'] = 'application/x-www-form-urlencoded';
        if (!empty($headers))
            $this->headers = array_merge($this->headers, $headers);

        $opts = ['headers' => $this->headers, 'timeout' => $this->timeout];
        if (!empty($data))
            $opts['json'] = $data;

        try {
            $response = json_decode(
                $this->clients->request(
                    $method,
                    $this->base_url . '/' . $this->service_name . '/' . $feature,
                    $opts
                )->getBody()->getContents(),
                true
            );
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            if ($e->getCode() == 0) {
                $handlerContext = $e->getHandlerContext();
                $response = [
                    'metaData' => [
                        'code' => $handlerContext['errno'],
                        'message' => $handlerContext['error']
                    ]
                ];
            } else
                $response = [
                    'metaData' => [
                        'code' => $e->getCode(),
                        'message' => $e->getMessage()
                    ]
                ];
        }

        if ($response['metaData']['code'] ?? '' == '200' and !empty($response['response']) and is_string($response['response']))
            $response['response'] = json_decode($this->_decompress($response['response']), true);
        return $response;
    }

    private function _decompress($txt)
    {
        $key  = $this->_getDecryptionKey();
        $hash = hex2bin(hash('sha256', $key));
        $iv   = substr($hash, 0, 16);


        $tmp  = openssl_decrypt(base64_decode($txt), 'AES-256-CBC', $hash, OPENSSL_RAW_DATA, $iv);
        if ($tmp === false) return $txt;

        return LZString::decompressFromEncodedURIComponent($tmp);
    }

    protected function get($feature)
    {
        return $this->_request('GET', $feature);
    }

    protected function post($feature, $data = [], $headers = [])
    {
        return $this->_request('POST', $feature, $data, $headers);
    }

    protected function put($feature, $data = [])
    {
        return $this->_request('PUT', $feature, $data);
    }

    protected function delete($feature, $data = [])
    {
        return $this->_request('DELETE', $feature, $data);
    }
}