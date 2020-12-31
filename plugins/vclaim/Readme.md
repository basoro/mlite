### BPJS Kesehatan Indonesia
mLITE Library package to access BPJS Kesehatan API.
This package is a wrapper of BPJS VClaim Web Service
https://dvlp.bpjs-kesehatan.go.id/VClaim-Katalog

Created because i don't really wanna get my hands dirty coz of using the old repeated scripts.

#### Example Usage
```php
// use Referensi service
// https://dvlp.bpjs-kesehatan.go.id/VClaim-Katalog/Referensi

public function getDiagnosa($keyword)
{
    $url = $this->api_url.'referensi/diagnosa/'.$keyword;
    $output = BpjsService::get($url, NULL, $this->consid, $this->secretkey);
    $json = json_decode($output, true);
    var_dump($json);
    exit();
}

//use Peserta service
//https://dvlp.bpjs-kesehatan.go.id/VClaim-Katalog/Peserta

public function getByNoKartu($noKartu, $tglPelayananSEP)
{
  $url = $this->api_url.'Peserta/nokartu/'.$noKartu.'/tglSEP/'.$tglPelayananSEP;
  $output = BpjsService::get($url, NULL, $this->consid, $this->secretkey);
  $json = json_decode($output, true);
  var_dump($json);
  exit();
}
```


#### Supported Services (WIP)

- [x] Referensi
- [x] Peserta
- [x] SEP
- [x] Rujukan
- [x] Lembar Pengajuan Klaim
- [x] Monitoring
