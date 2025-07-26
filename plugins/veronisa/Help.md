### BPJS Kesehatan Indonesia
mLITE Library package to access BPJS Apotek Online API.
This package is a wrapper of BPJS Apotek Online API
https://apijkn.bpjs-kesehatan.go.id/apotek-rest

#### Example Usage
```php
// use Apotek Online service
// https://apijkn.bpjs-kesehatan.go.id/apotek-rest

// Insert Obat Non Racikan
public function insertObatNonRacikan($data)
{
    $url = $this->apotek_api_url.'obatnonracikan/v3/insert';
    $output = BpjsService::post($url, $data, $this->consid, $this->secretkey, $this->user_key);
    $json = json_decode($output, true);
    return $json;
}

// Hapus Pelayanan Obat (menggunakan method DELETE)
public function hapusPelayananObat($data)
{
    $url = $this->apotek_api_url.'pelayanan/obat/hapus';
    $output = BpjsService::delete($url, $data, $this->consid, $this->secretkey, $this->user_key);
    $json = json_decode($output, true);
    return $json;
}

// Hapus Resep (menggunakan method DELETE)
public function hapusResep($data)
{
    $url = $this->apotek_api_url.'hapusresep';
    $output = BpjsService::delete($url, $data, $this->consid, $this->secretkey, $this->user_key);
    $json = json_decode($output, true);
    return $json;
}

// Cari SEP
public function cariSEP($nomorSEP)
{
    $url = $this->apotek_api_url.'sep/'.$nomorSEP;
    $output = BpjsService::get($url, NULL, $this->consid, $this->secretkey, $this->user_key);
    $json = json_decode($output, true);
    return $json;
}
```


#### Supported Services
- [√] Referensi (DPHO, Poli, PPK, Setting PPK, Spesialistik, Obat)
- [√] Obat Non Racikan (Insert)
- [√] Obat Racikan (Insert)
- [√] Pelayanan Obat (Hapus dengan method DELETE)
- [√] Resep (Insert, Hapus dengan method DELETE, Daftar)
- [√] SEP (Cari)
- [√] Monitoring Klaim
- [√] Obat Daftar
- [√] Riwayat Obat

#### Features
- [√] Form testing interface dengan parameter dinamis
- [√] Support untuk method GET, POST, dan DELETE
- [√] Automatic method selection untuk endpoint hapus
- [√] Real-time URL building dengan parameter validation
- [√] JSON request/response handling
