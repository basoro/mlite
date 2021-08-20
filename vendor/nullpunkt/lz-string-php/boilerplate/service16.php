<?php
require_once '../vendor/autoload.php';

use \LZCompressor\LZString as LZ;
use \LZCompressor\LZUtil16 as Util;

$log = new \Monolog\Logger('name');
\Monolog\ErrorHandler::register($log);

$log->pushHandler(new \Monolog\Handler\StreamHandler(getcwd().'/log/service.log'));
$request = json_decode(file_get_contents('php://input'), true);



$log->debug('Request: '.json_encode($request));

$compressed = LZ::compressToUTF16($request['str']);

$length = Util::utf16_strlen($compressed);
$compressedBytes = [];
for($i=0; $i<$length; $i++) {
    $val = Util::charCodeAt($compressed, $i);
    $log->debug('val: |'.$val.'|');
    $compressedBytes[] = $val & 255;
    $compressedBytes[] = ($val>>8) & 255;
}


$result = [
    'compressedBytes' => $compressedBytes,
    'decompressed' => LZ::decompressFromUTF16($compressed)
];

$log->debug('Result: '.json_encode($result));

header('Content-type: text/application');
echo json_encode($result);
//echo json_encode(['hi']);
