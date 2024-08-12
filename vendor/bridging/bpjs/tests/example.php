<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once 'config.php';

//use referensi serivce
$referensi = new Bridging\Bpjs\VClaim\Referensi($vclaim_conf);
var_dump($referensi->diagnosa('A00'));

//use peserta service
$peserta = new Bridging\Bpjs\VClaim\Peserta($vclaim_conf);
var_dump($peserta->getByNoKartu('123456789', '2018-09-16'));