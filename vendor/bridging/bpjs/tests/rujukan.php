<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once 'config.php';

$vruj = new Bridging\Bpjs\VClaim\Rujukan($vclaim_conf);
$rujukan = $vruj->cariByNoKartu('Peserta', '0002033613472', true);
var_dump($rujukan);

$rujukan = $vruj->cariByNoRujukan('RS', '0117R0721121B000004');
var_dump($rujukan);