<?php
namespace Systems\Lib;

class PcareService
{
    protected static $lastStatus = null;

    public static function get($url, $datafields = [], $consid = '', $secretkey = '', $user_key = '', $pcareUname = '', $pcarePWD = '', $kdAplikasi = '')
    {
        return self::request('GET', $url, $datafields, $consid, $secretkey, $user_key, $pcareUname, $pcarePWD, $kdAplikasi);
    }

    public static function post($url, $datafields = [], $consid = '', $secretkey = '', $user_key = '', $pcareUname = '', $pcarePWD = '', $kdAplikasi = '')
    {
        return self::request2('POST', $url, $datafields, $consid, $secretkey, $user_key, $pcareUname, $pcarePWD, $kdAplikasi);
    }

    public static function put($url, $datafields = [], $consid = '', $secretkey = '', $user_key = '', $pcareUname = '', $pcarePWD = '', $kdAplikasi = '')
    {
        return self::request2('PUT', $url, $datafields, $consid, $secretkey, $user_key, $pcareUname, $pcarePWD, $kdAplikasi);
    }

    public static function delete($url, $datafields = [], $consid = '', $secretkey = '', $user_key = '', $pcareUname = '', $pcarePWD = '', $kdAplikasi = '')
    {
        return self::request2('DELETE', $url, $datafields, $consid, $secretkey, $user_key, $pcareUname, $pcarePWD, $kdAplikasi);
    }

    public static function getStatus()
    {
        return self::$lastStatus;
    }

    protected static function request($type, $url, $datafields, $consid, $secretkey, $user_key, $pcareUname, $pcarePWD, $kdAplikasi)
    {
        date_default_timezone_set('UTC');
        $stamp          = strval(time() - strtotime("1970-01-01 00:00:00"));
        $data           = $consid.'&'.$stamp;

        $signature = hash_hmac('sha256', $data, $secretkey, true);
        $encodedSignature = base64_encode($signature);
        $encodedAuthorization = base64_encode($pcareUname.':'.$pcarePWD.':'.$kdAplikasi);

        $headers = array(
           "Accept: application/json",
           "X-cons-id:".$consid,
           "X-timestamp: ".$stamp,
           "X-signature: ".$encodedSignature,
           "X-authorization: Basic " .$encodedAuthorization,
           "user_key: ".$user_key
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $type);

        if (!empty($datafields)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($datafields));
        }

        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        self::$lastStatus = curl_error($ch);
        curl_close($ch);

        return $output;
    }
    protected static function request2($type, $url, $datafields, $consid, $secretkey, $user_key, $pcareUname, $pcarePWD, $kdAplikasi)
    {
        date_default_timezone_set('UTC');
        $stamp          = time();
        $data           = $consid.'&'.$stamp;

        $signature = hash_hmac('sha256', $data, $secretkey, true);
        $encodedSignature = base64_encode($signature);
        $encodedAuthorization = base64_encode($pcareUname.':'.$pcarePWD.':'.$kdAplikasi);

        $headers = array(
           "Accept: application/json",
           "X-cons-id:".$consid,
           "X-timestamp: ".$stamp,
           "X-signature: ".$encodedSignature,
           "X-authorization: Basic " .$encodedAuthorization,
           "user_key: ".$user_key
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $type);

        if (!empty($datafields)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $datafields);
        }

        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        self::$lastStatus = curl_error($ch);
        curl_close($ch);

        return $output;
    }
}
