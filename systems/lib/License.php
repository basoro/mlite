<?php

namespace Systems\Lib;


class License
{
    const UNREGISTERED = 1;
    const REGISTERED = 2;
    const ERROR = 3;
    const TIME_OUT = 5;

    private static $feedURL = 'https://mlite.id/datars/cek';

    public static function verify($license)
    {

        if (self::remoteCheck($license)) {
            self::update($license);
            return License::REGISTERED;
        } elseif (strpos(HttpRequest::getStatus(), 'timed out') !== false) {
            return License::TIME_OUT;
        } else {
            return License::UNREGISTERED;
        }

        return License::ERROR;
    }

    private static function update($license)
    {
        $core = $GLOBALS['core'];
        $core->db('mlite_settings')->where('module', 'settings')->where('field', 'license')->save(['value' => $license]);
    }

    private static function remoteCheck($license)
    {
        $output = json_decode(HttpRequest::get(self::$feedURL.'/'.$license), true);

        if (isset_or($output['status'], false) == 'verified') {
            return true;
        }

        return false;
    }
}
