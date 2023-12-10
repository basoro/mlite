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
            $array[$key] = htmlspecialchars($value ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
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

function addTokenVedika($url)
{
    if (isset($_SESSION['vedika_token'])) {
        if (parse_url($url, PHP_URL_QUERY)) {
            return $url.'&t='.$_SESSION['vedika_token'];
        } else {
            return $url.'?t='.$_SESSION['vedika_token'];
        }
    }

    return $url;
}

function addTokenVeronisa($url)
{
    if (isset($_SESSION['veronisa_token'])) {
        if (parse_url($url, PHP_URL_QUERY)) {
            return $url.'&t='.$_SESSION['veronisa_token'];
        } else {
            return $url.'?t='.$_SESSION['veronisa_token'];
        }
    }

    return $url;
}

function url($data = '')
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
        $data = str_replace(BASE_DIR.'/', '', $data);
        $url = $url.'/'.trim($data, '/');
    }

    if (strpos($url, '/'.ADMIN.'/') !== false) {
        $url = addToken($url);
    }

    if (strpos($url, '/veda/') !== false) {
        $url = addTokenVedika($url);
    }

    if (strpos($url, '/vero/') !== false) {
        $url = addTokenVeronisa($url);
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


function mlite_dir() {
    return dirname(str_replace(ADMIN, '', $_SERVER['SCRIPT_NAME']));
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

function month()
{
  $month = [
    'jan' => 'Januari',
    'feb' => 'Februari',
    'mar' => 'Maret',
    'apr' => 'April',
    'may' => 'Mey',
    'jun' => 'Juni',
    'jul' => 'Juli',
    'aug' => 'Agustus',
    'sep' => 'September',
    'oct' => 'Oktober',
    'nov' => 'Nopember',
    'dec' => 'Desember'
  ];
  return $month;
}

function getDayIndonesia($date)
{
    if($date != '0000-00-00'){
        $data = hari(date('D', strtotime($date)));
    }else{
        $data = '-';
    }

    return $data;
}


function hari($day) {
    $hari = $day;

    switch ($hari) {
        case "Sun":
            $hari = "Minggu";
            break;
        case "Mon":
            $hari = "Senin";
            break;
        case "Tue":
            $hari = "Selasa";
            break;
        case "Wed":
            $hari = "Rabu";
            break;
        case "Thu":
            $hari = "Kamis";
            break;
        case "Fri":
            $hari = "Jum'at";
            break;
        case "Sat":
            $hari = "Sabtu";
            break;
    }
    return $hari;
}

function dateIndonesia($date){
    if($date != '0000-00-00'){
        $date = explode('-', $date);

        $data = $date[2] . ' ' . bulan($date[1]) . ' '. $date[0];
    }else{
        $data = 'Format tanggal salah';
    }

    return $data;
}

function bulan($bln) {
    $bulan = $bln;

    switch ($bulan) {
        case 1:
            $bulan = "Januari";
            break;
        case 2:
            $bulan = "Februari";
            break;
        case 3:
            $bulan = "Maret";
            break;
        case 4:
            $bulan = "April";
            break;
        case 5:
            $bulan = "Mei";
            break;
        case 6:
            $bulan = "Juni";
            break;
        case 7:
            $bulan = "Juli";
            break;
        case 8:
            $bulan = "Agustus";
            break;
        case 9:
            $bulan = "September";
            break;
        case 10:
            $bulan = "Oktober";
            break;
        case 11:
            $bulan = "November";
            break;
        case 12:
            $bulan = "Desember";
            break;
    }
    return $bulan;
}

function penyebut($nilai) {
	$nilai = intval($nilai);
	$huruf = array('','Satu','Dua','Tiga','Empat','Lima','Enam','Tujuh','Delapan','Sembilan','Sepuluh','Sebelas');
	$temp = "";
	if ($nilai < 12) {
		$temp = " ". $huruf[$nilai];
	} else if ($nilai < 20) {
		$temp = penyebut($nilai - 10). " Belas";
	} else if ($nilai < 100) {
		$temp = penyebut($nilai/10)." Puluh". penyebut($nilai % 10);
	} else if ($nilai < 200) {
		$temp = " Seratus" . penyebut($nilai - 100);
	} else if ($nilai < 1000) {
		$temp = penyebut($nilai/100) . " Ratus" . penyebut($nilai % 100);
	} else if ($nilai < 2000) {
		$temp = " Seribu" . penyebut($nilai - 1000);
	} else if ($nilai < 1000000) {
		$temp = penyebut($nilai/1000) . " Ribu" . penyebut($nilai % 1000);
	} else if ($nilai < 1000000000) {
		$temp = penyebut($nilai/1000000) . " Juta" . penyebut($nilai % 1000000);
	} else if ($nilai < 1000000000000) {
		$temp = penyebut($nilai/1000000000) . " Milyar" . penyebut(fmod($nilai,1000000000));
	} else if ($nilai < 1000000000000000) {
		$temp = penyebut($nilai/1000000000000) . " Trilyun" . penyebut(fmod($nilai,1000000000000));
	}
	return $temp;
}

function terbilang($nilai) {
	if($nilai<0) {
		$hasil = "Minus ". trim(penyebut($nilai));
	} else {
		$hasil = trim(penyebut($nilai));
	}
	return $hasil;
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

function hitungUmur($tanggal_lahir)
{
  $birthDate = new \DateTime($tanggal_lahir);
  $today = new \DateTime("today");
  $umur = "0 Th 0 Bl 0 Hr";
  if ($birthDate < $today) {
    $y = $today->diff($birthDate)->y;
    $m = $today->diff($birthDate)->m;
    $d = $today->diff($birthDate)->d;
    $umur =  $y." Th ".$m." Bl ".$d." Hr";
  }
  return $umur;
}

function stringDecrypt($key, $string){

    $encrypt_method = 'AES-256-CBC';
    $key_hash = hex2bin(hash('sha256', $key));
    $iv = substr(hex2bin(hash('sha256', $key)), 0, 16);

    $output = openssl_decrypt(base64_decode(isset_or($string,'')), $encrypt_method, $key_hash, OPENSSL_RAW_DATA, $iv);

    return $output;
}

function decompress($string){
    return \LZCompressor\LZString::decompressFromEncodedURIComponent($string);
}
