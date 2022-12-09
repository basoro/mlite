### BPJS Kesehatan Indonesia
DRG.my.id Library package to access BPJS Kesehatan API.
This package is a wrapper of BPJS PCare Web Service

#### Example Usage
```php
// use Referensi service
// https://apijkn-dev.bpjs-kesehatan.go.id/pcare-rest-dev/diagnosa/{Parameter 1}/{Parameter 2}/{Parameter 3}

public function getDiagnosa($keyword)
{
    $url = $this->api_url.'diagnosa/'.$keyword.'/0/500';
    $output = PcareService::get($url, NULL, $this->consumerID, $this->consumerSecret, $this->consumerUserKey, $this->usernamePcare, $this->passwordPcare, $this->kdAplikasi);
    $json = json_decode($output, true);
    var_dump($json);
    exit();
}

//use Peserta service
//https://apijkn-dev.bpjs-kesehatan.go.id/pcare-rest-dev/peserta/{Parameter 1}

public function getNoKartu($noKartu)
{
  $url = $this->api_url.'peserta/'.$noKartu;
  $output = PcareService::get($url, NULL, $this->consumerID, $this->consumerSecret, $this->consumerUserKey, $this->usernamePcare, $this->passwordPcare, $this->kdAplikasi);
  $json = json_decode($output, true);
  var_dump($json);
  exit();
}
```


#### Supported Services (WIP)

- [√] Diagnosa
- [√] Peserta
- [√] SEP
- [√] Poli
- [x] Pendaftaran
- [x] Kunjungan
