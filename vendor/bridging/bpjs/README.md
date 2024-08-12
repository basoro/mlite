# BPJS Kesehatan Indonesia
PHP package to access BPJS Kesehatan API 
This package is a wrapper of BPJS VClaim Web Service V2

#### Installation :fire:

`composer require bridging/bpjs`

#### Example Usage :
```php
//use your own bpjs config
/**
 * VCLAIM
 */
$vclaim_config = [
    'cons_id'       => '123456',
    'secret_key'    => 'abcdef',
    'user_key'      => 'xxxyyyzzz',
    'base_url'      => 'https://apijkn-dev.bpjs-kesehatan.go.id',
    'service_name'  => 'vclaim-rest-dev'
];

/**
 * APLI CARE
 */
$aplicare_config = [
    'cons_id'      => '',
    'secret_key'   => '',
    'base_url'     => 'https://new-api.bpjs-kesehatan.go.id:8080',
    'service_name' => 'aplicaresws/rest'
];

/**
 * P-CARE
 */
$pcare_config = [
    'cons_id'      => '',
    'secret_key'   => '',
    'base_url'     => 'https://dvlp.bpjs-kesehatan.go.id:9081',
    'service_name' => 'pcare-rest-v3.0',
    'pcare_user'   => '',
    'pcare_pass'   => '',
    'kd_aplikasi'  => ''
];

/**
 * SITB_CONF
*/
$sitb_conf = [
        'cons_id'       => '',
        'user_pass'      => '',
        'base_url'      => 'http://sirs.yankes.kemkes.go.id',
        'service_name'  => 'sirsservice/sitbtraining/sitb'
    ];

// use Referensi service
$referensi = new Bridging\Bpjs\VClaim\Referensi($vclaim_conf);
var_dump($referensi->diagnosa('A00'));

$peserta = new Bridging\Bpjs\VClaim\Peserta($vclaim_conf);
var_dump($peserta->getByNoKartu('123456789','2018-09-16'));
```


#### Supported Services (WIP) :rocket:

- [x] Referensi
- [x] Peserta
- [x] SEP
- [x] Rujukan
- [x] Lembar Pengajuan Klaim
- [x] Monitoring
- [x] PRB
- [x] Rencana Kontrol
- [x] Aplicare
- [x] SITB ( Sietem Informasi Tuberkolosis )


####  REFERENSI REST 📘

- Vclaim V2.0 Trust Mark: https://dvlp.bpjs-kesehatan.go.id:8888/trust-mark/portal.html
- Pcare v3.0: https://new-api.bpjs-kesehatan.go.id/pcare-rest-v3.0/

#### BASED ON & CREDIT THANKS 👍
