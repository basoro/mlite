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


/**
 * Aturan kekuatan kata sandi yang berlaku di seluruh aplikasi.
 *
 * Minimal 8 karakter dan memuat kombinasi huruf kecil, huruf besar, serta angka.
 * dipakai oleh modul Pengguna (admin) dan Ganti Password (profil) agar aturannya sama.
 *
 * @param  string $password Kata sandi yang akan diperiksa
 * @return string|null Pesan kesalahan bila tidak lolos, atau null bila lolos
 */
function passwordStrengthError($password)
{
    $password = (string) $password;

    if (strlen($password) < 8) {
        return 'Password terlalu pendek. Minimal 8 karakter.';
    }
    if (!preg_match('/[a-z]/', $password)) {
        return 'Password harus memuat minimal satu huruf kecil.';
    }
    if (!preg_match('/[A-Z]/', $password)) {
        return 'Password harus memuat minimal satu huruf besar.';
    }
    if (!preg_match('/[0-9]/', $password)) {
        return 'Password harus memuat minimal satu angka.';
    }

    return null;
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

/**
 * Escape a value for HTML output (text node or attribute).
 *
 * Dipakai template untuk mengeluarkan data request tanpa membuka celah XSS.
 *
 * @param  mixed  $value
 * @return string
 */
function e($value)
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Header keamanan standar yang dikirim pada setiap response aplikasi.
 */
function sendSecurityHeaders()
{
    if (headers_sent()) {
        return;
    }

    // Cegah sniffing tipe konten (mis. HTML yang dibaca sebagai script).
    header('X-Content-Type-Options: nosniff');

    // Cegah clickjacking: halaman hanya boleh diframe oleh origin yang sama.
    header('X-Frame-Options: SAMEORIGIN');

    // CSP: kunci object/base/frame-ancestors tanpa memutus gaya & script inline
    // yang dipakai luas oleh tema dan modul yang ada.
    header("Content-Security-Policy: default-src * data: blob: 'unsafe-inline' 'unsafe-eval'; object-src 'self'; base-uri 'self'; frame-ancestors 'self'");
}

/**
 * Token CSRF yang tersimpan di sesi (untuk form & AJAX admin).
 *
 * @return string
 */
function csrfToken()
{
    return isset($_SESSION['token']) ? (string) $_SESSION['token'] : '';
}

/**
 * Ambil token CSRF yang dikirim klien: field _token, header X-CSRF-Token, atau parameter t.
 *
 * @return string|null
 */
function csrfTokenFromRequest()
{
    if (isset($_POST['_token']) && is_string($_POST['_token'])) {
        return $_POST['_token'];
    }
    if (isset($_SERVER['HTTP_X_CSRF_TOKEN']) && is_string($_SERVER['HTTP_X_CSRF_TOKEN'])) {
        return $_SERVER['HTTP_X_CSRF_TOKEN'];
    }
    if (isset($_GET['t']) && is_string($_GET['t'])) {
        return $_GET['t'];
    }

    return null;
}

/**
 * Validasi token CSRF untuk permintaan state-changing (POST/PUT/PATCH/DELETE).
 *
 * Dilindungi hanya permintaan yang memakai cookie sesi browser; klien API
 * (API key / Bearer) dan permintaan sebelum login (form login & OTP) dikecualikan.
 *
 * @return bool
 */
function verifyCsrfToken()
{
    $method = isset($_SERVER['REQUEST_METHOD']) ? strtoupper($_SERVER['REQUEST_METHOD']) : 'GET';
    if (in_array($method, ['GET', 'HEAD', 'OPTIONS'], true)) {
        return true;
    }

    // Klien API tidak memakai cookie sesi sehingga tidak rentan CSRF.
    $apiKey = !empty($_SERVER['HTTP_X_API_KEY']) || !empty($_SERVER['X_API_KEY'])
        || !empty($_SERVER['HTTP_API_KEY']) || !empty($_SERVER['API_KEY'])
        || !empty($_SERVER['HTTP_AUTHORIZATION']);
    if ($apiKey) {
        return true;
    }

    // Form login dan verifikasi OTP berjalan sebelum sesi login terbentuk.
    if (empty($_SESSION['mlite_user'])) {
        return true;
    }

    $expected = csrfToken();
    $provided = csrfTokenFromRequest();

    return $expected !== '' && is_string($provided) && hash_equals($expected, $provided);
}

/**
 * Escape rekursif seluruh nilai array untuk output HTML.
 *
 * Toleran terhadap null (hasil query tanpa baris) dan skalar, agar pemakaian
 * sebagai pembungkus data template tidak memicu TypeError di PHP 8.
 *
 * @param  mixed  $array
 * @return mixed
 */
function htmlspecialchars_array($array)
{
    if ($array === null) {
        return [];
    }
    if (!is_array($array)) {
        if ($array instanceof Closure) {
            return $array;
        }
        return htmlspecialchars((string) $array, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    foreach ($array as $key => $value) {
        if (is_array($value)) {
            $array[$key] = htmlspecialchars_array($value);
        } elseif ($value instanceof Closure) {
            // Abaikan Closure, jangan panggil htmlspecialchars
            continue;
        } else {
            $array[$key] = htmlspecialchars((string)($value ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
    }

    return $array;
}


/**
 * Daftar ekstensi yang DITOLAK di semua unggahan, apa pun allowlist pemanggil.
 * SVG bisa berisi <script> (XSS tersimpan bila dibuka langsung), HTML/XML bisa
 * mengeksekusi skrip, dan sisanya berkas eksekusi/format berbahaya.
 */
function uploadBlockedExtensions()
{
    return [
        'svg', 'svgz', 'xml', 'xsl', 'xslt', 'xsd',
        'html', 'htm', 'xhtml', 'shtml', 'hta', 'mhtml', 'mht',
        'js', 'jse', 'vbs', 'vbe', 'wsf', 'wsh', 'ps1', 'reg',
        'svg', 'swf', 'jar', 'apk', 'ipa', 'deb', 'rpm', 'msi', 'app',
        'php', 'php3', 'php4', 'php5', 'php7', 'php8', 'phtml', 'pht', 'phar', 'phps', 'cgi', 'pl', 'py', 'rb', 'sh', 'bash', 'zsh',
        'exe', 'bat', 'cmd', 'com', 'dll', 'so', 'dylib', 'bin', 'elf', 'msi',
        'jsp', 'asp', 'aspx', 'cer', 'cdx', 'aspq', 'inc',
        'htaccess', 'htpasswd', 'ini', 'conf', 'cfg', 'env', 'lock',
        'doc', 'docm', 'dotm', 'xls', 'xlsm', 'xltm', 'xlsb', 'pptm', 'potm', 'ppam', 'sldm', // makro Office
        'lnk', 'scp', 'desktop',
    ];
}

/**
 * Batas ukuran upload per jenis ekstensi (byte).
 * Dipakai validateUploadedFile() sebagai batas bawaan bila pemanggil
 * tidak menentukan $maxBytes sendiri (atau batas pemanggil lebih longgar).
 */
function uploadSizeLimits()
{
    $MB = 1024 * 1024;
    return [
        // gambar
        'jpg' => 5 * $MB, 'jpeg' => 5 * $MB, 'png' => 5 * $MB, 'gif' => 5 * $MB,
        'webp' => 5 * $MB, 'bmp' => 5 * $MB,
        // dokumen
        'pdf' => 20 * $MB,
        'doc' => 10 * $MB, 'docx' => 10 * $MB, 'xls' => 10 * $MB, 'xlsx' => 10 * $MB,
        'ppt' => 20 * $MB, 'pptx' => 20 * $MB, 'txt' => 2 * $MB, 'csv' => 10 * $MB,
        // arsip & data
        'zip' => 50 * $MB, 'rar' => 50 * $MB, '7z' => 50 * $MB,
        'dcm' => 100 * $MB,
        // media
        'mp3' => 20 * $MB, 'wav' => 50 * $MB,
        'mp4' => 100 * $MB, 'avi' => 100 * $MB, 'mkv' => 100 * $MB,
    ];
}

/**
 * Format ukuran byte untuk pesan error (mis. "5 MB", "100 KB").
 */
function sizeForDisplay($bytes)
{
    $bytes = (int) $bytes;
    if ($bytes >= 1024 * 1024) {
        $val = $bytes / (1024 * 1024);
        return (floor($val) == $val ? number_format($val) : number_format($val, 1)) . ' MB';
    }
    if ($bytes >= 1024) {
        return number_format($bytes / 1024) . ' KB';
    }
    return number_format($bytes) . ' byte';
}

/**
 * Peta kecocokan ekstensi berkas dengan MIME yang sah untuknya.
 * Dipakai validateUploadedFile() untuk menolak berkas yang ekstensinya
 * sengaja dibedakan dengan isinya (mis. gambar.php.jpg).
 */
function uploadExtensionMimeMap()
{
    return [
        'jpg'  => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png'  => ['image/png'],
        'gif'  => ['image/gif'],
        'webp' => ['image/webp'],
        'bmp'  => ['image/bmp', 'image/x-ms-bmp'],
        'pdf'  => ['application/pdf'],
        'zip'  => ['application/zip', 'application/x-zip-compressed', 'multipart/x-zip'],
        'dcm'  => ['application/dicom', 'application/octet-stream', 'image/jpeg', 'image/png'],
    ];
}

/**
 * Validasi unggahan: ekstensi nama berkas + kecocokan dengan isi berkas.
 *
 * Pemeriksaan yang dilakukan, berurutan:
 *  1. kode error PHP dan (opsional) batas ukuran;
 *  2. ekstensi terakhir nama berkas harus termasuk daftar izin;
 *  3. nama berkas tidak boleh mengandung ekstensi berbahaya lain
 *     (mis. "dokumen.php.jpg" ditolak);
 *  4. MIME hasil pembacaan isi berkas (finfo - bukan tipe yang dikirim
 *     klien) harus termasuk daftar izin;
 *  5. MIME isi harus cocok dengan ekstensi yang diklaim;
 *  6. berkas gambar harus benar-benar terbaca oleh getimagesize().
 *
 * @param  array $file             entri dari $_FILES
 * @param  array $allowedExtensions daftar ekstensi yang diizinkan (huruf kecil)
 * @param  array $allowedMimes     daftar MIME isi yang diizinkan
 * @param  int   $maxBytes         batas ukuran opsional (0 = tanpa batas)
 * @return array ['mime' => ..., 'extension' => ...] bila valid,
 *                atau ['error' => ...] bila ditolak.
 */
function validateUploadedFile(array $file, array $allowedExtensions, array $allowedMimes, int $maxBytes = 0)
{
    if (!isset($file['error']) || is_array($file['error'])) {
        return ['error' => 'Upload file gagal.'];
    }
    if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
        return ['error' => 'Ukuran file terlalu besar.'];
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['error' => 'Upload file gagal (kode error ' . $file['error'] . ').'];
    }

    $tmp = (string) ($file['tmp_name'] ?? '');
    if ($tmp === '' || !(is_uploaded_file($tmp) || is_file($tmp))) {
        return ['error' => 'File tidak ditemukan.'];
    }
    if ($maxBytes > 0 && (int) ($file['size'] ?? 0) > $maxBytes) {
        return ['error' => 'Ukuran file terlalu besar. Maksimal ' . sizeForDisplay($maxBytes) . '.'];
    }

    // Batas ukuran per jenis ekstensi (berlaku meski pemanggil tidak
    // menentukan $maxBytes sendiri) - kecuali pemanggil sudah lebih ketat.
    $extLimits = uploadSizeLimits();
    $extGuess  = strtolower(pathinfo(basename(str_replace('\\', '/', (string) ($file['name'] ?? ''))), PATHINFO_EXTENSION));
    if (($maxBytes <= 0 || (int) ($file['size'] ?? 0) > $maxBytes)
        && isset($extLimits[$extGuess])
        && (int) ($file['size'] ?? 0) > $extLimits[$extGuess]) {
        return ['error' => 'Ukuran file .' . $extGuess . ' terlalu besar. Maksimal ' . sizeForDisplay($extLimits[$extGuess]) . '.'];
    }

    $original = str_replace('\\', '/', (string) ($file['name'] ?? ''));
    $base = basename($original);
    if ($base === '' || $base === '.' || $base === '/') {
        return ['error' => 'Nama file tidak valid.'];
    }

    // 1. Ekstensi nama berkas
    $allowedExtensions = array_map('strtolower', $allowedExtensions);
    $extension = strtolower(pathinfo($base, PATHINFO_EXTENSION));
    if ($extension === '' || !in_array($extension, $allowedExtensions, true)) {
        return ['error' => 'Tipe file tidak diizinkan. Ekstensi yang diperbolehkan: ' . implode(', ', $allowedExtensions) . '.'];
    }

    // 1b. Tolak ekstensi yang berbahaya apa pun konteksnya (termasuk SVG/HTML/XML)
    if (in_array($extension, uploadBlockedExtensions(), true)) {
        return ['error' => 'File dengan ekstensi .' . $extension . ' tidak diizinkan (berpotensi berbahaya).'];
    }

    // 2. Tolak ekstensi ganda berbahaya pada nama berkas
    $dangerous = uploadBlockedExtensions();
    $segments = explode('.', strtolower($base));
    array_pop($segments); // ekstensi terakhir sudah sah
    foreach ($segments as $segment) {
        if (in_array($segment, $dangerous, true)) {
            return ['error' => 'Nama file mengandung ekstensi yang tidak diizinkan.'];
        }
    }

    // 3. MIME isi berkas - dibaca dari file, bukan dari klaim klien
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = $finfo ? (string) finfo_file($finfo, $tmp) : '';
    if ($finfo) {
        finfo_close($finfo);
    }
    if ($mime === '') {
        $mime = strtolower((string) ($file['type'] ?? ''));
    }
    $mime = strtolower($mime);
    $allowedMimes = array_map('strtolower', $allowedMimes);
    if (!in_array($mime, $allowedMimes, true)) {
        return ['error' => 'Isi file tidak sesuai dengan ekstensi .' . $extension . '.'];
    }

    // 4. Ekstensi harus cocok dengan isi berkas
    $compat = uploadExtensionMimeMap();
    if (isset($compat[$extension]) && !in_array($mime, $compat[$extension], true)) {
        return ['error' => 'Ekstensi .' . $extension . ' tidak cocok dengan isi file.'];
    }

    // 5. Gambar wajib benar-benar terbaca sebagai gambar
    if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'], true) && @getimagesize($tmp) === false) {
        return ['error' => 'File gambar rusak atau bukan gambar yang valid.'];
    }

    return ['mime' => $mime, 'extension' => $extension];
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

/**
 * Check if variable is set and return it or alternate value
 * Compatible with PHP 8+ strict types
 * 
 * @param mixed $var Variable to check
 * @param mixed $alternate Alternate value if variable is not set
 * @return mixed
 */
function isset_or(&$var, $alternate = null)
{
    return isset($var) ? $var : $alternate;
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
    $return = '';
    $length = (int) $length;
    if ($length <= 0) {
        return $return;
    }

    if (is_string($characters)) {
        $characters = str_split($characters);
    }

    if (!is_array($characters) || empty($characters)) {
        return $return;
    }

    $maxIndex = count($characters) - 1;
    for ($i = 0; $i < $length; $i++) {
        $return .= $characters[random_int(0, $maxIndex)];
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
		// nilai 0 menghasilkan string kosong -> tampilkan "Nol"
		// agar baris "Terbilang : # ... Rupiah #" tetap terisi
		if ($hasil === '') {
			$hasil = 'Nol';
		}
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
  $umur = "0 Th 0 Bl 0 Hr";
  if (empty($tanggal_lahir)) {
    return $umur;
  }
  try {
    $birthDate = new \DateTime($tanggal_lahir);
  } catch (\Exception $e) {
    return $umur;
  }
  $today = new \DateTime("today");
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

function getRomawi($bln){
    switch ($bln){
        case 1:
            return "I";
            break;
        case 2:
            return "II";
            break;
        case 3:
            return "III";
            break;
        case 4:
            return "IV";
            break;
        case 5:
            return "V";
            break;
        case 6:
            return "VI";
            break;
        case 7:
            return "VII";
            break;
        case 8:
            return "VIII";
            break;
        case 9:
            return "IX";
            break;
        case 10:
            return "X";
            break;
        case 11:
            return "XI";
            break;
        case 12:
            return "XII";
            break;
    }
}
