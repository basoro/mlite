<?php

function checkEmptyFields(array $keys, array $array)
{
    foreach ($keys as $field) {
        if (empty($array[$field])) {
            return true;
        }
    }

    return false;
}

function deleteDir($path)
{
    return !empty($path) && is_file($path)
        ? @unlink($path)
        : (array_reduce(glob($path.'/*'),
            function ($r, $i) {
                return $r && deleteDir($i);
            }, true))
        && @rmdir($path);
}

function createSlug($text)
{
    setlocale(LC_ALL, 'en_EN');
    $text = str_replace(' ', '-', trim($text));
    $text = str_replace('.', '-', trim($text));
    $text = iconv('utf-8', 'ascii//translit', $text);
    $text = preg_replace('#[^a-z0-9\-]#si', '', $text);

    return strtolower(str_replace('\'', '', $text));
}

function convertNorawat($text)
{
    setlocale(LC_ALL, 'en_EN');
    $text = str_replace('/', '', trim($text));
    return $text;
}

function revertNorawat($text)
{
    setlocale(LC_ALL, 'en_EN');
    $tahun = substr($text, 0, 4);
    $bulan = substr($text, 4, 2);
    $tanggal = substr($text, 6, 2);
    $nomor = substr($text, 8, 6);
    $result = $tahun.'/'.$bulan.'/'.$tanggal.'/'.$nomor;
    return $result;
}

function htmlspecialchars_array(array $array)
{
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            $array[$key] = htmlspecialchars_array($value);
        } else {
            $array[$key] = htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
    }

    return $array;
}

function htmlentities_array(array $array)
{
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            $array[$key] = htmlentities_array($value);
        } else {
            $array[$key] = htmlentities($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
    }

    return $array;
}

function redirect($url, array $data = [])
{
    if ($data) {
        $_SESSION['REDIRECT_DATA'] = $data;
    }

    header("Location: $url");
    exit();
}

function getRedirectData()
{
    if (isset($_SESSION['REDIRECT_DATA'])) {
        $tmp = $_SESSION['REDIRECT_DATA'];
        unset($_SESSION['REDIRECT_DATA']);

        return $tmp;
    }

    return null;
}

function currentURL($query = false)
{
    if (isset_or($GLOBALS['core'], null) instanceof \Systems\Admin) {
        $url = url(ADMIN.'/'.implode('/', parseURL()));
    } else {
        $url = url(implode('/', parseURL()));
    }

    if ($query) {
        return $url.'?'.$_SERVER['QUERY_STRING'];
    } else {
        return $url;
    }
}

function parseURL($key = null)
{
    $url = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
    $url = trim(str_replace($url, '', $_SERVER['REQUEST_URI']), '/');
    $url = explode('?', $url);
    $array = explode('/', $url[0]);

    if ($key) {
        return isset_or($array[$key - 1], false);
    } else {
        return $array;
    }
}

function addToken($url)
{
    if (isset($_SESSION['token'])) {
        if (parse_url($url, PHP_URL_QUERY)) {
            return $url.'&t='.$_SESSION['token'];
        } else {
            return $url.'?t='.$_SESSION['token'];
        }
    }

    return $url;
}

function url($data = null)
{
    if (filter_var($data, FILTER_VALIDATE_URL) !== false) {
        return $data;
    }

    if (!is_array($data) && strpos($data, '#') === 0) {
        return $data;
    }

    if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off')
        || isset_or($_SERVER['SERVER_PORT'], null) == 443
        || isset_or($_SERVER['HTTP_X_FORWARDED_PORT'], null) == 443
    ) {
        $protocol = 'https://';
    } else {
        $protocol = 'http://';
    }

    $url = trim($protocol.$_SERVER['HTTP_HOST'].dirname($_SERVER['SCRIPT_NAME']), '/\\');
    $url = str_replace('/'.ADMIN, '', $url);

    if (is_array($data)) {
        $url = $url.'/'.implode('/', $data);
    } elseif ($data) {
        $data = str_replace(BASE_DIR.'/', null, $data);
        $url = $url.'/'.trim($data, '/');
    }

    if (strpos($url, '/'.ADMIN.'/') !== false) {
        $url = addToken($url);
    }

    return $url;
}

function domain($with_protocol = true, $cut_www = false)
{
    $url = parse_url(url());

    if ($cut_www && strpos($url['host'], 'www.') === 0) {
        $host = str_replace('www.', null, $url['host']);
    } else {
        $host = $url['host'];
    }

    if ($with_protocol) {
        return $url['scheme'].'://'.$host;
    }

    return $host;
}

function opensimrs_dir() {
    return dirname(str_replace(ADMIN, null, $_SERVER['SCRIPT_NAME']));
}

function isset_or(&$var, $alternate = null)
{
    return (isset($var)) ? $var : $alternate;
}

function cmpver($a, $b)
{
    $a = explode(".", $a);
    $b = explode(".", $b);
    foreach ($a as $depth => $aVal) {
        if (isset($b[$depth])) {
            $bVal = $b[$depth];
        } else {
            $bVal = "0";
        }

        list($aLen, $bLen) = [strlen($aVal), strlen($bVal)];

        if ($aLen > $bLen) {
            $bVal = str_pad($bVal, $aLen, "0");
        } elseif ($bLen > $aLen) {
            $aVal = str_pad($aVal, $bLen, "0");
        }

        if ($aVal == $bVal) {
            continue;
        }

        if ($aVal > $bVal) {
            return 1;
        }

        if ($aVal < $bVal) {
            return -1;
        }
    }

    return 0;
}

function str_limit($text, $limit = 100, $end = '...')
{
    if (mb_strlen($text, 'UTF-8') > $limit) {
        return mb_substr($text, 0, $limit, 'UTF-8').$end;
    }

    return $text;
}

function get_headers_list($key = null)
{
    $headers_list = headers_list();
    $headers = [];
    foreach ($headers_list as $header) {
        $e = explode(":", $header);
        $headers[strtolower(array_shift($e))] = trim(implode(":", $e));
    }

    if ($key) {
        return isset_or($headers[strtolower($key)], false);
    }

    return $headers;
}

function str_gen($length, $characters = "1234567890qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM")
{
    $return = null;

    if (is_string($characters)) {
        $characters = str_split($characters);
    }

    for ($i = 0; $i < $length; $i++) {
        $return .= $characters[rand(0, count($characters) - 1)];
    }

    return $return;
}

function gz64_encode($string)
{
    return str_replace(['+', '/'], ['_', '-'], trim(base64_encode(gzcompress($string, 9)), "="));
}

function gz64_decode($string)
{
    return gzuncompress(base64_decode(str_replace(['_', '-'], ['+', '/'], $string)));
}

function cv($variable)
{
    if (!is_string($variable) && is_callable($variable)) {
        return $variable();
    }

    return $variable;
}

if (!function_exists('apache_request_headers')) {
    function apache_request_headers() {
        $return = array();
        foreach($_SERVER as $key=>$value) {
            if (substr($key,0,5)=="HTTP_") {
                $key=str_replace(" ","-",ucwords(strtolower(str_replace("_"," ",substr($key,5)))));
                $return[$key]=$value;
            }else{
                $return[$key]=$value;
	        }
        }
        return $return;
    }
}

function formatDuit($duit){
    return "Rp. ".number_format($duit,0,",",".").",-";
}

function hitungUmur($tanggal_lahir){
  	$birthDate = new DateTime($tanggal_lahir);
  	$today = new DateTime("today");
  	if ($birthDate > $today) {
  	    exit("0 Th 0 Bl 0 Hr");
  	}
  	$y = $today->diff($birthDate)->y;
  	$m = $today->diff($birthDate)->m;
  	$d = $today->diff($birthDate)->d;
  	return $y." Th ".$m." Bl ".$d." Hr";
}
