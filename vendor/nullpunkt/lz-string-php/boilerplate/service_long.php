<?php
require_once '../vendor/autoload.php';

use \LZCompressor\LZString as LZ;
use \LZCompressor\LZUtil as Util;

$log = new \Monolog\Logger('name');
\Monolog\ErrorHandler::register($log);

$log->pushHandler(new \Monolog\Handler\StreamHandler(getcwd().'/log/service.log'));
$request = json_decode(file_get_contents('php://input'), true);

$input = $request['str'];
$decompressed = LZ::decompressFromBase64($input);

$result = [
    'comporessedTextLength' => Util::utf8_strlen($input),
    'decompressedLength' => Util::utf8_strlen($decompressed),
    'decompressedMd5' => md5($decompressed)
];

header('Content-type: text/application');
echo json_encode($result);