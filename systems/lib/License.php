<?php

namespace Systems\Lib;


class License
{
    const FREE = 1;
    const COMMERCIAL = 2;
    const ERROR = 3;
    const INVALID_DOMAIN = 4;
    const TIME_OUT = 5;

    private static $feedURL = 'https://feed.basoro.org';

    public static function verify($license)
    {
        $license = self::unpack($license);

        if ($license[0] === false && $license[1] === false && $license[2] === false && $license[3] === false && $license[4] === false) {
            return License::FREE;
        }

        if ($license[0] == md5($license[1].$license[2].$license[3].domain(false, true))) {
            if (time() < $license[4] || strtotime("-48 hours") > $license[4]) {
                if (self::remoteCheck($license)) {
                    self::update($license);
                    return License::COMMERCIAL;
                } elseif (strpos(HttpRequest::getStatus(), 'timed out') !== false) {
                    return License::TIME_OUT;
                } else {
                    return License::ERROR;
                }
            } else {
                return License::COMMERCIAL;
            }
        }

        return License::ERROR;
    }

    public static function getLicenseData($domainCode)
    {
        $response = json_decode(HttpRequest::post(self::$feedURL.'/mlite/commercial/license/data', ['code' => $domainCode, 'domain' => domain(false)]), true);

        if (isset_or($response['status'], false) == 'verified') {
            return $response['data'];
        }

        return false;
    }

    private static function unpack($code)
    {
        $code = base64_decode($code);
        $code = empty($code) ? [] : json_decode($code, true);
        $code = array_replace(array_fill(0, 5, false), $code);
        array_walk($code, function (&$value) {
            $value = is_numeric($value) ? intval($value) : $value;
        });
        return $code;
    }

    private static function update($license)
    {
        $license[4] = time();
        $core = $GLOBALS['core'];
        $core->db('mlite_settings')->where('module', 'settings')->where('field', 'license')->save(['value' => base64_encode(json_encode($license))]);
    }

    private static function remoteCheck($license)
    {
        $output = HttpRequest::post(self::$feedURL.'/mlite/commercial/license/verify', ['pid' => $license[1], 'code' => $license[2], 'domain' => domain(false), 'domainCode' => $license[3]]);
        $output = json_decode($output, true);

        if (isset_or($output['status'], false) == 'verified') {
            return true;
        }

        return false;
    }
}
