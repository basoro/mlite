<?php
require_once '../vendor/autoload.php';

use \LZCompressor\LZString as LZ;
use \LZCompressor\LZUtil as Util;

$log = new \Monolog\Logger('name');
\Monolog\ErrorHandler::register($log);

$log->pushHandler(new \Monolog\Handler\StreamHandler(getcwd().'/log/service.log'));
$request = json_decode(file_get_contents('php://input'), true);



$log->debug('Request: '.json_encode($request));


$compressed = LZ::compress($request['str']);

$length = Util::utf8_strlen($compressed);
$compressedBytes = [];
for($i=0; $i<$length; $i++) {
    $val = Util::charCodeAt($compressed, $i);
    $compressedBytes[] = $val & 255;
    $compressedBytes[] = ($val>>8) & 255;
}


$compressed64 = LZ::compressToBase64($request['str']);

$result = [
    'compressedBytes' => $compressedBytes,
    'compressed64' => $compressed64,
    'decompressed' => LZ::decompress($compressed),
    'decompressed64' => LZ::decompressFromBase64($request['com64'])
];

$log->debug('Result: '.json_encode($result));

header('Content-type: text/application');
echo json_encode($result);