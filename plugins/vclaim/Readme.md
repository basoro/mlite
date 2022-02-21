### BPJS Kesehatan Indonesia
KhanzaLITE Library package to access BPJS Kesehatan API.
This package is a wrapper of BPJS VClaim Web Service
https://dvlp.bpjs-kesehatan.go.id/VClaim-Katalog

#### Example Usage
```php
// use Referensi service
// https://dvlp.bpjs-kesehatan.go.id/VClaim-Katalog/Referensi

public function getDiagnosa($keyword)
{
    $url = $this->api_url.'referensi/diagnosa/'.$keyword;
    $output = BpjsService::get($url, NULL, $this->consid, $this->secretkey, $this->user_key);
    $json = json_decode($output, true);
    var_dump($json);
    exit();
}

//use Peserta service
//https://dvlp.bpjs-kesehatan.go.id/VClaim-Katalog/Peserta

public function getByNoKartu($noKartu, $tglPelayananSEP)
{
  $url = $this->api_url.'Peserta/nokartu/'.$noKartu.'/tglSEP/'.$tglPelayananSEP;
  $output = BpjsService::get($url, NULL, $this->consid, $this->secretkey, $this->user_key);
  $json = json_decode($output, true);
  var_dump($json);
  exit();
}
```


#### Supported Services (WIP)

- [√] Referensi
- [√] Peserta
- [√] SEP
- [√] Rujukan
- [x] Rencana Kontrol
- [x] PRB
- [√] Lembar Pengajuan Klaim
- [√] Monitoring
