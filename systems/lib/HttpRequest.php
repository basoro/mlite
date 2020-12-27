<?php

namespace Systems\Lib;


class HttpRequest
{

    protected static $lastStatus = null;


    public static function get($url, $datafields = [], $headers = [])
    {
        return self::request('GET', $url, $datafields, $headers);
    }


    public static function post($url, $datafields = [], $headers = [])
    {
        return self::request('POST', $url, $datafields, $headers);
    }


    public static function getStatus()
    {
        return self::$lastStatus;
    }

    protected static function request($type, $url, $datafields, $headers)
    {
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
}
