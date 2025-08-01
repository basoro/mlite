<?php
namespace Plugins\Master;

use Systems\AdminModule;

use Plugins\Master\Src\Dokter;
use Plugins\Master\Src\Petugas;
use Plugins\Master\Src\Poliklinik;
use Plugins\Master\Src\Bangsal;
use Plugins\Master\Src\Kamar;
use Plugins\Master\Src\DataBarang;
use Plugins\Master\Src\JnsPerawatan;
use Plugins\Master\Src\JnsPerawatanInap;
use Plugins\Master\Src\JnsPerawatanLab;
use Plugins\Master\Src\JnsPerawatanRadiologi;
use Plugins\Master\Src\Bahasa;
use Plugins\Master\Src\Propinsi;
use Plugins\Master\Src\Kabupaten;
use Plugins\Master\Src\Kecamatan;
use Plugins\Master\Src\Kelurahan;
use Plugins\Master\Src\Cacat;
use Plugins\Master\Src\Suku;
use Plugins\Master\Src\Perusahaan;
use Plugins\Master\Src\Penjab;
use Plugins\Master\Src\GolonganBarang;
use Plugins\Master\Src\IndustriFarmasi;
use Plugins\Master\Src\Jenis;
use Plugins\Master\Src\KategoriBarang;
use Plugins\Master\Src\KategoriPenyakit;
use Plugins\Master\Src\KategoriPerawatan;
use Plugins\Master\Src\KodeSatuan;
use Plugins\Master\Src\MasterAturanPakai;
use Plugins\Master\Src\MasterBerkasDigital;
use Plugins\Master\Src\Spesialis;
use Plugins\Master\Src\Bank;
use Plugins\Master\Src\Bidang;
use Plugins\Master\Src\Departemen;
use Plugins\Master\Src\EmergencyIndex;
use Plugins\Master\Src\Jabatan;
use Plugins\Master\Src\JenjangJabatan;
use Plugins\Master\Src\KelompokJabatan;
use Plugins\Master\Src\Pendidikan;
use Plugins\Master\Src\ResikoKerja;
use Plugins\Master\Src\StatusKerja;
use Plugins\Master\Src\StatusWP;
use Plugins\Master\Src\MetodeRacik;
use Plugins\Master\Src\RuangOk;

class Admin extends AdminModule
{

  protected $dokter;
  protected $petugas;
  protected $poliklinik;
  protected $bangsal;
  protected $kamar;
  protected $databarang;
  protected $jnsperawatan;
  protected $jnsperawataninap;
  protected $jnsperawatanlab;
  protected $jnsperawatanradiologi;
  protected $bahasa;
  protected $propinsi;
  protected $kabupaten;
  protected $kecamatan;
  protected $kelurahan;
  protected $cacat;
  protected $suku;
  protected $perusahaan;
  protected $penjab;
  protected $golonganbarang;
  protected $industrifarmasi;
  protected $jenis;
  protected $kategoribarang;
  protected $kategoripenyakit;
  protected $kategoriperawatan;
  protected $masteraturanpakai;
  protected $masterberkasdigital;
  protected $kodesatuan;
  protected $spesialis;
  protected $bank;
  protected $bidang;
  protected $departemen;
  protected $emergencyindex;
  protected $jabatan;
  protected $jenjangjabatan;
  protected $kelompokjabatan;
  protected $pendidikan;
  protected $resikokerja;
  protected $statuskerja;
  protected $statuswp;
  protected $metoderacik;
  protected $ruangok;

    public function init()
    {
        $this->dokter = new Dokter();
        $this->petugas = new Petugas();
        $this->poliklinik = new Poliklinik($this->core);
        $this->bangsal = new Bangsal();
        $this->kamar = new Kamar();
        $this->databarang = new DataBarang();
        $this->jnsperawatan = new JnsPerawatan();
        $this->jnsperawataninap = new JnsPerawatanInap();
        $this->jnsperawatanlab = new JnsPerawatanLab();
        $this->jnsperawatanradiologi = new JnsPerawatanRadiologi();
        $this->bahasa = new Bahasa();
        $this->propinsi = new Propinsi();
        $this->kabupaten = new Kabupaten();
        $this->kecamatan = new Kecamatan();
        $this->kelurahan = new Kelurahan();
        $this->cacat = new Cacat();
        $this->suku = new Suku();
        $this->perusahaan = new Perusahaan();
        $this->penjab = new Penjab();
        $this->golonganbarang = new GolonganBarang();
        $this->industrifarmasi = new IndustriFarmasi();
        $this->jenis = new Jenis();
        $this->kategoribarang = new KategoriBarang();
  	    $this->kategoripenyakit = new KategoriPenyakit();
  	    $this->kategoriperawatan = new KategoriPerawatan();
  	    $this->masteraturanpakai = new MasterAturanPakai();
  	    $this->masterberkasdigital = new MasterBerkasDigital();
  	    $this->kodesatuan = new KodeSatuan();
        $this->spesialis = new Spesialis();
        $this->bank = new Bank();
        $this->bidang = new Bidang();
        $this->departemen = new Departemen();
        $this->emergencyindex = new EmergencyIndex();
        $this->jabatan = new Jabatan();
        $this->jenjangjabatan = new JenjangJabatan();
        $this->kelompokjabatan = new KelompokJabatan();
        $this->pendidikan = new Pendidikan();
        $this->resikokerja = new ResikoKerja();
        $this->statuskerja = new StatusKerja();
        $this->statuswp = new StatusWP();
        $this->metoderacik = new MetodeRacik();
        $this->ruangok = new RuangOk();
    }

    public function navigation()
    {
        return [
            'Manage' => 'manage',
            'Dokter' => 'dokter',
            'Petugas' => 'petugas',
            'Poliklinik' => 'poliklinik',
            'Bangsal' => 'bangsal',
            'Kamar' => 'kamar',
            'Data Barang' => 'databarang',
            'Perawatan Ralan' => 'jnsperawatan',
            'Perawatan Ranap' => 'jnsperawataninap',
            'Perawatan Laboratorium' => 'jnsperawatanlab',
            'Perawatan Radiologi' => 'jnsperawatanradiologi',
            'Bahasa' => 'bahasa',
            'Propinsi' => 'propinsi',
            'Kabupaten' => 'kabupaten',
            'Kecamatan' => 'kecamatan',
            'Kelurahan' => 'kelurahan',
            'Cacat Fisik' => 'cacat',
            'Suku Bangsa' => 'suku',
            'Perusahaan Pasien' => 'perusahaan',
            'Penanggung Jawab' => 'penjab',
            'Golongan Barang' => 'golonganbarang',
            'Industri Farmasi' => 'industrifarmasi',
            'Jenis Barang' => 'jenis',
            'Kategori Barang' => 'kategoribarang',
            'Kategori Penyakit' => 'kategoripenyakit',
            'Kategori Perawatan' => 'kategoriperawatan',
            'Kode Satuan' => 'kodesatuan',
            'Master Aturan Pakai' => 'masteraturanpakai',
            'Master Berkas Digital' => 'masterberkasdigital',
            'Spesialis' => 'spesialis',
            'Bank' => 'bank',
            'Bidang' => 'bidang',
            'Departemen' => 'departemen',
            'Emergency Index' => 'emergencyindex',
            'Jabatan' => 'jabatan',
            'Jenjang Jabatan' => 'jenjangjabatan',
            'Kelompok Jabatan' => 'kelompokjabatan',
            'Pendidikan' => 'pendidikan',
            'Resiko Kerja' => 'resikokerja',
            'Status Kerja' => 'statuskerja',
            'Status WP' => 'statuswp',
            'Metode Racik' => 'metoderacik',
            'Ruang OK' => 'ruangok',
        ];
    }

    public function getManage()
    {
      $sub_modules = [
        ['name' => 'Dokter', 'url' => url([ADMIN, 'master', 'dokter']), 'icon' => 'cubes', 'desc' => 'Master dokter'],
        ['name' => 'Petugas', 'url' => url([ADMIN, 'master', 'petugas']), 'icon' => 'cubes', 'desc' => 'Master petugas'],
        ['name' => 'Poliklinik', 'url' => url([ADMIN, 'master', 'poliklinik']), 'icon' => 'cubes', 'desc' => 'Master poliklinik'],
        ['name' => 'Bangsal', 'url' => url([ADMIN, 'master', 'bangsal']), 'icon' => 'cubes', 'desc' => 'Master bangsal'],
        ['name' => 'Kamar', 'url' => url([ADMIN, 'master', 'kamar']), 'icon' => 'cubes', 'desc' => 'Master kamar'],
        ['name' => 'Data Barang', 'url' => url([ADMIN, 'master', 'databarang']), 'icon' => 'cubes', 'desc' => 'Master data barang'],
        ['name' => 'Perawatan Rawat Jalan', 'url' => url([ADMIN, 'master', 'jnsperawatan']), 'icon' => 'cubes', 'desc' => 'Master jenis perawatan rawat jalan'],
        ['name' => 'Perawatan Rawat Inap', 'url' => url([ADMIN, 'master', 'jnsperawataninap']), 'icon' => 'cubes', 'desc' => 'Master jenis perawatan rawat inap'],
        ['name' => 'Perawatan Laboratorium', 'url' => url([ADMIN, 'master', 'jnsperawatanlab']), 'icon' => 'cubes', 'desc' => 'Master jenis perawatan laboratorium'],
        ['name' => 'Perawatan Radiologi', 'url' => url([ADMIN, 'master', 'jnsperawatanradiologi']), 'icon' => 'cubes', 'desc' => 'Master jenis perawatan radiologi'],
        ['name' => 'Bahasa', 'url' => url([ADMIN, 'master', 'bahasa']), 'icon' => 'cubes', 'desc' => 'Master bahasa'],
        ['name' => 'Propinsi', 'url' => url([ADMIN, 'master', 'propinsi']), 'icon' => 'cubes', 'desc' => 'Master propinsi'],
        ['name' => 'Kabupaten', 'url' => url([ADMIN, 'master', 'kabupaten']), 'icon' => 'cubes', 'desc' => 'Master kabupaten'],
        ['name' => 'Kecamatan', 'url' => url([ADMIN, 'master', 'kecamatan']), 'icon' => 'cubes', 'desc' => 'Master kecamatan'],
        ['name' => 'Kelurahan', 'url' => url([ADMIN, 'master', 'kelurahan']), 'icon' => 'cubes', 'desc' => 'Master kelurahan'],
        ['name' => 'Cacat Fisik', 'url' => url([ADMIN, 'master', 'cacat']), 'icon' => 'cubes', 'desc' => 'Master cacat fisik'],
        ['name' => 'Suku Bangsa', 'url' => url([ADMIN, 'master', 'suku']), 'icon' => 'cubes', 'desc' => 'Master suku bangsa'],
        ['name' => 'Perusahaan Pasien', 'url' => url([ADMIN, 'master', 'perusahaan']), 'icon' => 'cubes', 'desc' => 'Master perusahaan pasien'],
        ['name' => 'Penanggung Jawab', 'url' => url([ADMIN, 'master', 'penjab']), 'icon' => 'cubes', 'desc' => 'Master penanggung jawab'],
        ['name' => 'Golongan Barang', 'url' => url([ADMIN, 'master', 'golonganbarang']), 'icon' => 'cubes', 'desc' => 'Master golongan barang'],
        ['name' => 'Industri Farmasi', 'url' => url([ADMIN, 'master', 'industrifarmasi']), 'icon' => 'cubes', 'desc' => 'Master industri farmasi'],
        ['name' => 'Jenis Barang', 'url' => url([ADMIN, 'master', 'jenis']), 'icon' => 'cubes', 'desc' => 'Master jenis barang'],
        ['name' => 'Kategori Barang', 'url' => url([ADMIN, 'master', 'kategoribarang']), 'icon' => 'cubes', 'desc' => 'Master kategori barang'],
        ['name' => 'Kategori Penyakit', 'url' => url([ADMIN, 'master', 'kategoripenyakit']), 'icon' => 'cubes', 'desc' => 'Master kategori penyakit'],
        ['name' => 'Kategori Perawatan', 'url' => url([ADMIN, 'master', 'kategoriperawatan']), 'icon' => 'cubes', 'desc' => 'Master kategori perawatan'],
        ['name' => 'Kode Satuan', 'url' => url([ADMIN, 'master', 'kodesatuan']), 'icon' => 'cubes', 'desc' => 'Master kode satuan'],
        ['name' => 'Master Aturan Pakai', 'url' => url([ADMIN, 'master', 'masteraturanpakai']), 'icon' => 'cubes', 'desc' => 'Master aturan pakai'],
        ['name' => 'Master Berkas Digital', 'url' => url([ADMIN, 'master', 'masterberkasdigital']), 'icon' => 'cubes', 'desc' => 'Master berkas digital'],
        ['name' => 'Spesialis', 'url' => url([ADMIN, 'master', 'spesialis']), 'icon' => 'cubes', 'desc' => 'Master spesialis'],
        ['name' => 'Bank', 'url' => url([ADMIN, 'master', 'bank']), 'icon' => 'cubes', 'desc' => 'Master bank'],
        ['name' => 'Bidang', 'url' => url([ADMIN, 'master', 'bidang']), 'icon' => 'cubes', 'desc' => 'Master bidang'],
        ['name' => 'Departemen', 'url' => url([ADMIN, 'master', 'departemen']), 'icon' => 'cubes', 'desc' => 'Master departemen'],
        ['name' => 'Emergency Index', 'url' => url([ADMIN, 'master', 'emergencyindex']), 'icon' => 'cubes', 'desc' => 'Master emergency index'],
        ['name' => 'Jabatan', 'url' => url([ADMIN, 'master', 'jabatan']), 'icon' => 'cubes', 'desc' => 'Master jabatan'],
        ['name' => 'Jenjang Jabatan', 'url' => url([ADMIN, 'master', 'jenjangjabatan']), 'icon' => 'cubes', 'desc' => 'Master jenjang jabatan'],
        ['name' => 'Kelompok Jabatan', 'url' => url([ADMIN, 'master', 'kelompokjabatan']), 'icon' => 'cubes', 'desc' => 'Master kelompok jabatan'],
        ['name' => 'Pendidikan', 'url' => url([ADMIN, 'master', 'pendidikan']), 'icon' => 'cubes', 'desc' => 'Master pendidikan'],
        ['name' => 'Resiko Kerja', 'url' => url([ADMIN, 'master', 'resikokerja']), 'icon' => 'cubes', 'desc' => 'Master resiko kerja'],
        ['name' => 'Status Kerja', 'url' => url([ADMIN, 'master', 'statuskerja']), 'icon' => 'cubes', 'desc' => 'Master status kerja'],
        ['name' => 'Status Wajib Pajak', 'url' => url([ADMIN, 'master', 'statuswp']), 'icon' => 'cubes', 'desc' => 'Master status wajib pajak'],
        ['name' => 'Metode Racik', 'url' => url([ADMIN, 'master', 'metoderacik']), 'icon' => 'cubes', 'desc' => 'Master metode racik'],
        ['name' => 'Ruang OK', 'url' => url([ADMIN, 'master', 'ruangok']), 'icon' => 'cubes', 'desc' => 'Master ruang OK'],
      ];
      return $this->draw('manage.html', ['sub_modules' => $sub_modules]);
    }

    /* Start Dokter Section */
    public function getDokter()
    {
      $this->_addHeaderFiles();
      $this->core->addJS(url([ADMIN, 'master', 'dokterjs']), 'footer');
      $return = $this->dokter->getIndex();
      return $this->draw('dokter.html', [
        'dokter' => $return
      ]);

    }

    public function anyDokterForm()
    {
        $return = $this->dokter->anyForm();
        echo $this->draw('dokter.form.html', ['dokter' => $return]);
        exit();
    }

    public function anyDokterDisplay()
    {
        $return = $this->dokter->anyDisplay();
        echo $this->draw('dokter.display.html', ['dokter' => $return]);
        exit();
    }

    public function postDokterSave()
    {
      $this->dokter->postSave();
      exit();
    }

    public function postDokterHapus()
    {
      $this->dokter->postHapus();
      exit();
    }

    public function getDokterJS()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/master/js/admin/dokter.js');
        exit();
    }
    /* End Dokter Section */

    /* Start Petugas Section */
    public function getPetugas()
    {
      $this->_addHeaderFiles();
      $this->core->addJS(url([ADMIN, 'master', 'petugasjs']), 'footer');
      $return = $this->petugas->getIndex();
      return $this->draw('petugas.html', [
        'petugas' => $return
      ]);

    }

    public function anyPetugasForm()
    {
        $return = $this->petugas->anyForm();
        echo $this->draw('petugas.form.html', ['petugas' => $return]);
        exit();
    }

    public function anyPetugasDisplay()
    {
        $return = $this->petugas->anyDisplay();
        echo $this->draw('petugas.display.html', ['petugas' => $return]);
        exit();
    }

    public function postPetugasSave()
    {
      $this->petugas->postSave();
      exit();
    }

    public function postPetugasHapus()
    {
      $this->petugas->postHapus();
      exit();
    }

    public function getPetugasJS()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/master/js/admin/petugas.js');
        exit();
    }
    /* End Petugas Section */

    /* Start Poliklinik Section */
    public function getPoliklinik()
    {
      if($this->settings->get('settings.versi_beta') == 'ya') {
        $this->poliklinik->_addHeaderFiles();
        $this->core->addCSS(url([ADMIN, 'master', 'poliklinikcss']));
        $this->core->addJS(url([ADMIN, 'master', 'poliklinikjs']), 'footer');
        return $this->draw('poliklinik.beta.html');  
      } else {
        $this->_addHeaderFiles();
        $this->core->addJS(url([ADMIN, 'master', 'poliklinikjs']), 'footer');
        $return = $this->poliklinik->getIndex();
        return $this->draw('poliklinik.html', [
          'poliklinik' => $return
        ]);
      }

    }

    public function anyPoliklinikForm()
    {
        $return = $this->poliklinik->anyForm();
        echo $this->draw('poliklinik.form.html', ['poliklinik' => $return]);
        exit();
    }

    public function anyPoliklinikDisplay()
    {
        $return = $this->poliklinik->anyDisplay();
        echo $this->draw('poliklinik.display.html', ['poliklinik' => $return]);
        exit();
    }

    public function postPoliklinikSave()
    {
      $this->poliklinik->postSave();
      exit();
    }

    public function postPoliklinikHapus()
    {
      $this->poliklinik->postHapus();
      exit();
    }

    public function getPoliklinikJS()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/master/js/admin/poliklinik.js', ['settings' => $settings]);
        exit();
    }

    public function getPoliklinikBeta()
    {
      $this->poliklinik->_addHeaderFiles();
      $this->core->addCSS(url([ADMIN, 'master', 'poliklinikcss']));
      $this->core->addJS(url([ADMIN, 'master', 'poliklinikjs']), 'footer');
      return $this->draw('poliklinik.beta.html');
    }

    public function postPoliklinikData()
    {
      echo $this->poliklinik->postData();
    }

    public function postPoliklinikAksi()
    {
      echo $this->poliklinik->postAksi();
    }

    public function getPoliklinikDetail($kd_poli)
    {
      $detail = $this->db('poliklinik')->where('kd_poli', $kd_poli)->toArray();
      $settings =  $this->settings('settings');
      echo $this->draw('poliklinik.detail.html', ['detail' => $detail, 'settings' => $settings]);
      exit();
    }
    
    public function getPoliklinikChart($type = '', $column = '')
    {
      $data = $this->poliklinik->getChart($type, $column);
      echo $this->draw('poliklinik.chart.html', $data);
      exit();

    }
    
    public function getPoliklinikCSS()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/master/css/admin/poliklinik.css');
        exit();
    }

    /* End Poliklinik Section */

    /* Start Bangsal Section */
    public function getBangsal()
    {
      $this->_addHeaderFiles();
      $this->core->addJS(url([ADMIN, 'master', 'bangsaljs']), 'footer');
      $return = $this->bangsal->getIndex();
      return $this->draw('bangsal.html', [
        'bangsal' => $return
      ]);

    }

    public function anyBangsalForm()
    {
        $return = $this->bangsal->anyForm();
        echo $this->draw('bangsal.form.html', ['bangsal' => $return]);
        exit();
    }

    public function anyBangsalDisplay()
    {
        $return = $this->bangsal->anyDisplay();
        echo $this->draw('bangsal.display.html', ['bangsal' => $return]);
        exit();
    }

    public function postBangsalSave()
    {
      $this->bangsal->postSave();
      exit();
    }

    public function postBangsalHapus()
    {
      $this->bangsal->postHapus();
      exit();
    }

    public function getBangsalJS()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/master/js/admin/bangsal.js');
        exit();
    }
    /* End Bangsal Section */

    /* Start Kamar Section */
    public function getKamar()
    {
      $this->core->addJS(url([ADMIN, 'master', 'kamarjs']), 'footer');
      $return = $this->kamar->getIndex();
      return $this->draw('kamar.html', [
        'kamar' => $return
      ]);

    }

    public function anyKamarForm()
    {
        $return = $this->kamar->anyForm();
        echo $this->draw('kamar.form.html', ['kamar' => $return]);
        exit();
    }

    public function anyKamarDisplay()
    {
        $return = $this->kamar->anyDisplay();
        echo $this->draw('kamar.display.html', ['kamar' => $return]);
        exit();
    }

    public function postKamarSave()
    {
      $this->kamar->postSave();
      exit();
    }

    public function postKamarHapus()
    {
      $this->kamar->postHapus();
      exit();
    }

    public function getKamarJS()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/master/js/admin/kamar.js');
        exit();
    }
    /* End Kamar Section */

    /* Start DataBarang Section */
    public function getDataBarang()
    {
      $this->_addHeaderFiles();
      $this->core->addJS(url([ADMIN, 'master', 'databarangjs']), 'footer');
      $return = $this->databarang->getIndex();
      return $this->draw('databarang.html', [
        'databarang' => $return
      ]);

    }

    public function anyDataBarangForm()
    {
        $return = $this->databarang->anyForm();
        echo $this->draw('databarang.form.html', ['databarang' => $return]);
        exit();
    }

    public function anyDataBarangDisplay()
    {
        $return = $this->databarang->anyDisplay();
        echo $this->draw('databarang.display.html', ['databarang' => $return]);
        exit();
    }

    public function postDataBarangSave()
    {
      $query = $this->databarang->postSave();
      
      if($query) {
        $data['status'] = 'success';
        echo json_encode($data);
      } else {
        $data['status'] = 'error';
        $data['msg'] = $query->errorInfo()['2'];
        echo json_encode($data);
      }

      exit();
    }

    public function postDataBarangHapus()
    {
      $this->databarang->postHapus();
      exit();
    }

    public function postDataBarangMaxId()
    {
      $this->databarang->postMaxId();
      exit();
    }

    public function getDataBarangJS()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/master/js/admin/databarang.js');
        exit();
    }
    /* End DataBarang Section */

    /* Start JnsPerawatan Section */
    public function getJnsPerawatan()
    {
      $this->core->addJS(url([ADMIN, 'master', 'jnsperawatanjs']), 'footer');
      $return = $this->jnsperawatan->getIndex();
      return $this->draw('jnsperawatan.html', [
        'jnsperawatan' => $return
      ]);

    }

    public function anyJnsPerawatanForm()
    {
        $return = $this->jnsperawatan->anyForm();
        echo $this->draw('jnsperawatan.form.html', ['jnsperawatan' => $return]);
        exit();
    }

    public function anyJnsPerawatanDisplay()
    {
        $return = $this->jnsperawatan->anyDisplay();
        echo $this->draw('jnsperawatan.display.html', ['jnsperawatan' => $return]);
        exit();
    }

    public function postJnsPerawatanSave()
    {
      $this->jnsperawatan->postSave();
      exit();
    }

    public function postJnsPerawatanHapus()
    {
      $this->jnsperawatan->postHapus();
      exit();
    }

    public function postJnsPerawatanMaxId()
    {
      $this->jnsperawatan->postMaxId();
      exit();
    }

    public function getJnsPerawatanJS()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/master/js/admin/jnsperawatan.js');
        exit();
    }
    /* End JnsPerawatan Section */

    /* Start JnsPerawatanInap Section */
    public function getJnsPerawatanInap()
    {
      $this->core->addJS(url([ADMIN, 'master', 'jnsperawataninapjs']), 'footer');
      $return = $this->jnsperawataninap->getIndex();
      return $this->draw('jnsperawataninap.html', [
        'jnsperawatan' => $return
      ]);

    }

    public function anyJnsPerawatanInapForm()
    {
        $return = $this->jnsperawataninap->anyForm();
        echo $this->draw('jnsperawataninap.form.html', ['jnsperawatan' => $return]);
        exit();
    }

    public function anyJnsPerawatanInapDisplay()
    {
        $return = $this->jnsperawataninap->anyDisplay();
        echo $this->draw('jnsperawataninap.display.html', ['jnsperawatan' => $return]);
        exit();
    }

    public function postJnsPerawatanInapSave()
    {
      $this->jnsperawataninap->postSave();
      exit();
    }

    public function postJnsPerawatanInapHapus()
    {
      $this->jnsperawataninap->postHapus();
      exit();
    }

    public function postJnsPerawatanInapMaxId()
    {
      $this->jnsperawataninap->postMaxId();
      exit();
    }

    public function getJnsPerawatanInapJS()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/master/js/admin/jnsperawataninap.js');
        exit();
    }
    /* End JnsPerawatanInap Section */

    /* Start JnsPerawatanLab Section */
    public function getJnsPerawatanLab()
    {
      $this->core->addJS(url([ADMIN, 'master', 'jnsperawatanlabjs']), 'footer');
      $return = $this->jnsperawatanlab->getIndex();
      return $this->draw('jnsperawatanlab.html', [
        'jnsperawatan' => $return
      ]);

    }

    public function anyJnsPerawatanLabForm()
    {
        $return = $this->jnsperawatanlab->anyForm();
        echo $this->draw('jnsperawatanlab.form.html', ['jnsperawatan' => $return]);
        exit();
    }

    public function anyTemplateLaboratorium()
    {
        $return = $this->jnsperawatanlab->anyTemplateLaboratorium();
        echo $this->draw('jnsperawatanlab.template.html', ['jnsperawatan' => $return]);
        exit();
    }

    public function anyJnsPerawatanLabDisplay()
    {
        $return = $this->jnsperawatanlab->anyDisplay();
        echo $this->draw('jnsperawatanlab.display.html', ['jnsperawatan' => $return]);
        exit();
    }

    public function postJnsPerawatanLabSave()
    {
      $this->jnsperawatanlab->postSave();
      exit();
    }

    public function postJnsPerawatanLabHapus()
    {
      $this->jnsperawatanlab->postHapus();
      exit();
    }

    public function anyTemplateLaboratoriumForm($kd_jenis_prw, $id_template='')
    {
      $template = [];
      if($id_template !='') {
        $template = $this->db('template_laboratorium')->where('id_template', $id_template)->oneArray();
      }
      echo $this->draw('jnsperawatanlab.template.form.html', ['kd_jenis_prw' => $kd_jenis_prw, 'id_template' => $id_template, 'template' => $template]);
      exit();
    }

    public function postJnsPerawatanLabTemplateSave()
    {
      if($_POST['id_template'] !='') {
        $this->db('template_laboratorium')->where('id_template', $_POST['id_template'])->save($_POST);
      } else {
        $this->db('template_laboratorium')->save($_POST);
      }
      exit();
    }

    public function postJnsPerawatanLabTemplateHapus()
    {
      $query = $this->db('template_laboratorium')->where('id_template', $_POST['id_template'])->delete();
      if($query == 1) {
        $data['status'] = 'success';
        echo json_encode($data);
      } else {
        $data['status'] = 'error';
        $data['msg'] = $query->errorInfo()['2'];
        echo json_encode($data);
      }
      exit();
    }

    public function postJnsPerawatanLabMaxId()
    {
      $this->jnsperawatanlab->postMaxId();
      exit();
    }

    public function getJnsPerawatanLabJS()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/master/js/admin/jnsperawatanlab.js');
        exit();
    }
    /* End JnsPerawatanLab Section */

    /* Start JnsPerawatanRadiologi Section */
    public function getJnsPerawatanRadiologi()
    {
      $this->core->addJS(url([ADMIN, 'master', 'jnsperawatanradiologijs']), 'footer');
      $return = $this->jnsperawatanradiologi->getIndex();
      return $this->draw('jnsperawatanradiologi.html', [
        'jnsperawatan' => $return
      ]);

    }

    public function anyJnsPerawatanRadiologiForm()
    {
        $return = $this->jnsperawatanradiologi->anyForm();
        echo $this->draw('jnsperawatanradiologi.form.html', ['jnsperawatan' => $return]);
        exit();
    }

    public function anyJnsPerawatanRadiologiDisplay()
    {
        $return = $this->jnsperawatanradiologi->anyDisplay();
        echo $this->draw('jnsperawatanradiologi.display.html', ['jnsperawatan' => $return]);
        exit();
    }

    public function postJnsPerawatanRadiologiSave()
    {
      $this->jnsperawatanradiologi->postSave();
      exit();
    }

    public function postJnsPerawatanRadiologiHapus()
    {
      $this->jnsperawatanradiologi->postHapus();
      exit();
    }

    public function postJnsPerawatanRadiologiMaxId()
    {
      $this->jnsperawatanradiologi->postMaxId();
      exit();
    }

    public function getJnsPerawatanRadiologiJS()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/master/js/admin/jnsperawatanradiologi.js');
        exit();
    }
    /* End JnsPerawatanRadiologi Section */

    /* Start Bahasa Section */
    public function getBahasa()
    {
      $this->core->addJS(url([ADMIN, 'master', 'bahasajs']), 'footer');
      $return = $this->bahasa->getIndex();
      return $this->draw('bahasa.html', [
        'bahasa' => $return
      ]);

    }

    public function anyBahasaForm()
    {
        $return = $this->bahasa->anyForm();
        echo $this->draw('bahasa.form.html', ['bahasa' => $return]);
        exit();
    }

    public function anyBahasaDisplay()
    {
        $return = $this->bahasa->anyDisplay();
        echo $this->draw('bahasa.display.html', ['bahasa' => $return]);
        exit();
    }

    public function postBahasaSave()
    {
      $this->bahasa->postSave();
      exit();
    }

    public function postBahasaHapus()
    {
      $this->bahasa->postHapus();
      exit();
    }

    public function getBahasaJS()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/master/js/admin/bahasa.js');
        exit();
    }
    /* End Bahasa Section */

    /* Start Propinsi Section */
    public function getPropinsi()
    {
      $this->core->addJS(url([ADMIN, 'master', 'propinsijs']), 'footer');
      $return = $this->propinsi->getIndex();
      return $this->draw('propinsi.html', [
        'propinsi' => $return
      ]);

    }

    public function anyPropinsiForm()
    {
        $return = $this->propinsi->anyForm();
        echo $this->draw('propinsi.form.html', ['propinsi' => $return]);
        exit();
    }

    public function anyPropinsiDisplay()
    {
        $return = $this->propinsi->anyDisplay();
        echo $this->draw('propinsi.display.html', ['propinsi' => $return]);
        exit();
    }

    public function postPropinsiSave()
    {
      $this->propinsi->postSave();
      exit();
    }

    public function postPropinsiHapus()
    {
      $this->propinsi->postHapus();
      exit();
    }

    public function getPropinsiJS()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/master/js/admin/propinsi.js');
        exit();
    }

    public function getImportPropinsi()
    {
      $filename = 'https://basoro.id/downloads/provinces.csv';
      echo '['.date('d-m-Y H:i:s').'][info] --- Mengimpor file csv'."<br>";

      $csvData = file_get_contents($filename);
      if($csvData) {
        echo '['.date('d-m-Y H:i:s').'][info] Berkas ditemukan'."<br>";
      } else {
        echo '['.date('d-m-Y H:i:s').'][error] File '.$filename.' tidak ditemukan'."<br>";
        exit();
      }

      $lines = explode(PHP_EOL, $csvData);
      $array = array();
      foreach ($lines as $line) {
          $array[] = str_getcsv($line);
      }

      foreach ($array as $data){   
        $kode = $data[0];
        $nama = isset_or($data[1], '');
        $value_query[] = "('".$kode."','".str_replace("'","\'",$nama)."')";
      }
      $str = implode(",", $value_query);
      echo '['.date('d-m-Y H:i:s').'][info] Memasukkan data'."<br>";
      $result = $this->core->db()->pdo()->exec("INSERT INTO propinsi (kd_prop, nm_prop) VALUES $str ON DUPLICATE KEY UPDATE kd_prop=VALUES(kd_prop)");
      if($result) {
        echo '['.date('d-m-Y H:i:s').'][info] Impor selesai'."<br>";
      } else {
        echo '['.date('d-m-Y H:i:s').'][error] kesalahan selama import : <pre>'.json_encode($str, JSON_PRETTY_PRINT).''."</pre><br>";
        exit();
      }
      
      exit();
    }

    /* End Propinsi Section */    

    /* Start Kabupaten Section */
    public function getKabupaten()
    {
      $this->core->addJS(url([ADMIN, 'master', 'kabupatenjs']), 'footer');
      $return = $this->kabupaten->getIndex();
      return $this->draw('kabupaten.html', [
        'kabupaten' => $return
      ]);

    }

    public function anyKabupatenForm()
    {
        $return = $this->kabupaten->anyForm();
        echo $this->draw('kabupaten.form.html', ['kabupaten' => $return]);
        exit();
    }

    public function anyKabupatenDisplay()
    {
        $return = $this->kabupaten->anyDisplay();
        echo $this->draw('kabupaten.display.html', ['kabupaten' => $return]);
        exit();
    }

    public function postKabupatenSave()
    {
      $this->kabupaten->postSave();
      exit();
    }

    public function postKabupatenHapus()
    {
      $this->kabupaten->postHapus();
      exit();
    }

    public function getKabupatenJS()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/master/js/admin/kabupaten.js');
        exit();
    }

    public function getImportKabupaten()
    {
      $filename = 'https://basoro.id/downloads/regencies.csv';
      echo '['.date('d-m-Y H:i:s').'][info] --- Mengimpor file csv'."<br>";

      $csvData = file_get_contents($filename);
      if($csvData) {
        echo '['.date('d-m-Y H:i:s').'][info] Berkas ditemukan'."<br>";
      } else {
        echo '['.date('d-m-Y H:i:s').'][error] File '.$filename.' tidak ditemukan'."<br>";
        exit();
      }

      $lines = explode(PHP_EOL, $csvData);
      $array = array();
      foreach ($lines as $line) {
          $array[] = str_getcsv($line);
      }

      foreach ($array as $data){   
        $kode = $data[0];
        $nama = $data[2];
        $value_query[] = "('".$kode."','".str_replace("'","\'",$nama)."')";
      }
      $str = implode(",", $value_query);
      echo '['.date('d-m-Y H:i:s').'][info] Memasukkan data'."<br>";
      $result = $this->core->db()->pdo()->exec("INSERT INTO kabupaten (kd_kab, nm_kab) VALUES $str ON DUPLICATE KEY UPDATE kd_kab=VALUES(kd_kab)");
      if($result) {
        echo '['.date('d-m-Y H:i:s').'][info] Impor selesai'."<br>";
      } else {
        echo '['.date('d-m-Y H:i:s').'][error] kesalahan selama import : <pre>'.json_encode($str, JSON_PRETTY_PRINT).''."</pre><br>";
        exit();
      }

      exit();
    }

    /* End Kabupaten Section */    

    /* Start Kecamatan Section */
    public function getKecamatan()
    {
      $this->core->addJS(url([ADMIN, 'master', 'kecamatanjs']), 'footer');
      $return = $this->kecamatan->getIndex();
      return $this->draw('kecamatan.html', [
        'kecamatan' => $return
      ]);

    }

    public function anyKecamatanForm()
    {
        $return = $this->kecamatan->anyForm();
        echo $this->draw('kecamatan.form.html', ['kecamatan' => $return]);
        exit();
    }

    public function anyKecamatanDisplay()
    {
        $return = $this->kecamatan->anyDisplay();
        echo $this->draw('kecamatan.display.html', ['kecamatan' => $return]);
        exit();
    }

    public function postKecamatanSave()
    {
      $this->kecamatan->postSave();
      exit();
    }

    public function postKecamatanHapus()
    {
      $this->kecamatan->postHapus();
      exit();
    }

    public function getKecamatanJS()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/master/js/admin/kecamatan.js');
        exit();
    }

    public function getImportKecamatan()
    {
      $filename = 'https://basoro.id/downloads/districts.csv';
      echo '['.date('d-m-Y H:i:s').'][info] --- Mengimpor file csv'."<br>";

      $csvData = file_get_contents($filename);
      if($csvData) {
        echo '['.date('d-m-Y H:i:s').'][info] Berkas ditemukan'."<br>";
      } else {
        echo '['.date('d-m-Y H:i:s').'][error] File '.$filename.' tidak ditemukan'."<br>";
        exit();
      }

      $lines = explode(PHP_EOL, $csvData);
      $array = array();
      foreach ($lines as $line) {
          $array[] = str_getcsv($line);
      }

      foreach ($array as $data){   
        $kode = $data[0];
        $nama = $data[2];
        $value_query[] = "('".$kode."','".str_replace("'","\'",$nama)."')";
      }
      $str = implode(",", $value_query);
      echo '['.date('d-m-Y H:i:s').'][info] Memasukkan data'."<br>";
      $result = $this->core->db()->pdo()->exec("INSERT INTO kecamatan (kd_kec, nm_kec) VALUES $str ON DUPLICATE KEY UPDATE kd_kec=VALUES(kd_kec)");
      if($result) {
        echo '['.date('d-m-Y H:i:s').'][info] Impor selesai'."<br>";
      } else {
        echo '['.date('d-m-Y H:i:s').'][error] kesalahan selama import : <pre>'.json_encode($str, JSON_PRETTY_PRINT).''."</pre><br>";
        exit();
      }

      exit();
    }

    /* End Kecamatan Section */    

    /* Start Kelurahan Section */
    public function getKelurahan()
    {
      $this->core->addJS(url([ADMIN, 'master', 'kelurahanjs']), 'footer');
      $return = $this->kelurahan->getIndex();
      return $this->draw('kelurahan.html', [
        'kelurahan' => $return
      ]);

    }

    public function anyKelurahanForm()
    {
        $return = $this->kelurahan->anyForm();
        echo $this->draw('kelurahan.form.html', ['kelurahan' => $return]);
        exit();
    }

    public function anyKelurahanDisplay()
    {
        $return = $this->kelurahan->anyDisplay();
        echo $this->draw('kelurahan.display.html', ['kelurahan' => $return]);
        exit();
    }

    public function postKelurahanSave()
    {
      $this->kelurahan->postSave();
      exit();
    }

    public function postKelurahanHapus()
    {
      $this->kelurahan->postHapus();
      exit();
    }

    public function getKelurahanJS()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/master/js/admin/kelurahan.js');
        exit();
    }

    public function getImportKelurahan()
    {
      $filename = 'https://basoro.id/downloads/villages.csv';
      echo '['.date('d-m-Y H:i:s').'][info] --- Mengimpor file csv'."<br>";

      $csvData = file_get_contents($filename);
      if($csvData) {
        echo '['.date('d-m-Y H:i:s').'][info] Berkas ditemukan'."<br>";
      } else {
        echo '['.date('d-m-Y H:i:s').'][error] File '.$filename.' tidak ditemukan'."<br>";
        exit();
      }

      $lines = explode(PHP_EOL, $csvData);
      $array = array();
      foreach ($lines as $line) {
          $array[] = str_getcsv($line);
      }

      foreach ($array as $data){   
        $kode = $data[0];
        $nama = $data[2];
        $value_query[] = "('".$kode."','".str_replace("'","\'",$nama)."')";
      }
      $str = implode(",", $value_query);
      echo '['.date('d-m-Y H:i:s').'][info] Memasukkan data'."<br>";
      $result = $this->core->db()->pdo()->exec("INSERT INTO kelurahan (kd_kel, nm_kel) VALUES $str ON DUPLICATE KEY UPDATE kd_kel=VALUES(kd_kel)");
      if($result) {
        echo '['.date('d-m-Y H:i:s').'][info] Impor selesai'."<br>";
      } else {
        echo '['.date('d-m-Y H:i:s').'][error] kesalahan selama import : <pre>'.json_encode($str, JSON_PRETTY_PRINT).''."</pre><br>";
        exit();
      }

      exit();
    }

    /* End Kelurahan Section */   

    /* Start Cacat Fisik Section */
    public function getCacat()
    {
      $this->core->addJS(url([ADMIN, 'master', 'cacatjs']), 'footer');
      $return = $this->cacat->getIndex();
      return $this->draw('cacat.html', [
        'cacat' => $return
      ]);

    }

    public function anyCacatForm()
    {
        $return = $this->cacat->anyForm();
        echo $this->draw('cacat.form.html', ['cacat' => $return]);
        exit();
    }

    public function anyCacatDisplay()
    {
        $return = $this->cacat->anyDisplay();
        echo $this->draw('cacat.display.html', ['cacat' => $return]);
        exit();
    }

    public function postCacatSave()
    {
      $this->cacat->postSave();
      exit();
    }

    public function postCacatHapus()
    {
      $this->cacat->postHapus();
      exit();
    }

    public function getCacatJS()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/master/js/admin/cacat.js');
        exit();
    }
    /* End Cacat Section */

    /* Start Suku Section */
    public function getSuku()
    {
      $this->core->addJS(url([ADMIN, 'master', 'sukujs']), 'footer');
      $return = $this->suku->getIndex();
      return $this->draw('suku.html', [
        'suku' => $return
      ]);

    }

    public function anySukuForm()
    {
        $return = $this->suku->anyForm();
        echo $this->draw('suku.form.html', ['suku' => $return]);
        exit();
    }

    public function anySukuDisplay()
    {
        $return = $this->suku->anyDisplay();
        echo $this->draw('suku.display.html', ['suku' => $return]);
        exit();
    }

    public function postSukuSave()
    {
      $this->suku->postSave();
      exit();
    }

    public function postSukuHapus()
    {
      $this->suku->postHapus();
      exit();
    }

    public function getSukuJS()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/master/js/admin/suku.js');
        exit();
    }
    /* End Suku Section */

    /* Start Perusahaan Section */
    public function getPerusahaan()
    {
      $this->core->addJS(url([ADMIN, 'master', 'perusahaanjs']), 'footer');
      $return = $this->perusahaan->getIndex();
      return $this->draw('perusahaan.html', [
        'perusahaan' => $return
      ]);

    }

    public function anyPerusahaanForm()
    {
        $return = $this->perusahaan->anyForm();
        echo $this->draw('perusahaan.form.html', ['perusahaan' => $return]);
        exit();
    }

    public function anyPerusahaanDisplay()
    {
        $return = $this->perusahaan->anyDisplay();
        echo $this->draw('perusahaan.display.html', ['perusahaan' => $return]);
        exit();
    }

    public function postPerusahaanSave()
    {
      $this->perusahaan->postSave();
      exit();
    }

    public function postPerusahaanHapus()
    {
      $this->perusahaan->postHapus();
      exit();
    }

    public function getPerusahaanJS()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/master/js/admin/perusahaan.js');
        exit();
    }
    /* End Perusahaan Section */

    /* Start Penjab Section */
    public function getPenjab()
    {
      $this->core->addJS(url([ADMIN, 'master', 'penjabjs']), 'footer');
      $return = $this->penjab->getIndex();
      return $this->draw('penjab.html', [
        'penjab' => $return
      ]);

    }

    public function anyPenjabForm()
    {
        $return = $this->penjab->anyForm();
        echo $this->draw('penjab.form.html', ['penjab' => $return]);
        exit();
    }

    public function anyPenjabDisplay()
    {
        $return = $this->penjab->anyDisplay();
        echo $this->draw('penjab.display.html', ['penjab' => $return]);
        exit();
    }

    public function postPenjabSave()
    {
      $this->penjab->postSave();
      exit();
    }

    public function postPenjabHapus()
    {
      $this->penjab->postHapus();
      exit();
    }

    public function getPenjabJS()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/master/js/admin/penjab.js');
        exit();
    }
    /* End Penjab Section */

    /* Start GolonganBarang Section */
    public function getGolonganBarang()
    {
      $this->core->addJS(url([ADMIN, 'master', 'golonganbarangjs']), 'footer');
      $return = $this->golonganbarang->getIndex();
      return $this->draw('golonganbarang.html', [
        'golonganbarang' => $return
      ]);

    }

    public function anyGolonganBarangForm()
    {
        $return = $this->golonganbarang->anyForm();
        echo $this->draw('golonganbarang.form.html', ['golonganbarang' => $return]);
        exit();
    }

    public function anyGolonganBarangDisplay()
    {
        $return = $this->golonganbarang->anyDisplay();
        echo $this->draw('golonganbarang.display.html', ['golonganbarang' => $return]);
        exit();
    }

    public function postGolonganBarangSave()
    {
      $this->golonganbarang->postSave();
      exit();
    }

    public function postGolonganBarangHapus()
    {
      $this->golonganbarang->postHapus();
      exit();
    }

    public function getGolonganBarangJS()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/master/js/admin/golonganbarang.js');
        exit();
    }
    /* End GolonganBarang Section */

    /* Start IndustriFarmasi Section */
    public function getIndustriFarmasi()
    {
      $this->core->addJS(url([ADMIN, 'master', 'industrifarmasijs']), 'footer');
      $return = $this->industrifarmasi->getIndex();
      return $this->draw('industrifarmasi.html', [
        'industrifarmasi' => $return
      ]);

    }

    public function anyIndustriFarmasiForm()
    {
        $return = $this->industrifarmasi->anyForm();
        echo $this->draw('industrifarmasi.form.html', ['industrifarmasi' => $return]);
        exit();
    }

    public function anyIndustriFarmasiDisplay()
    {
        $return = $this->industrifarmasi->anyDisplay();
        echo $this->draw('industrifarmasi.display.html', ['industrifarmasi' => $return]);
        exit();
    }

    public function postIndustriFarmasiSave()
    {
      $this->industrifarmasi->postSave();
      exit();
    }

    public function postIndustriFarmasiHapus()
    {
      $this->industrifarmasi->postHapus();
      exit();
    }

    public function getIndustriFarmasiJS()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/master/js/admin/industrifarmasi.js');
        exit();
    }
    /* End IndustriFarmasi Section */

    /* Start Jenis Section */
    public function getJenis()
    {
      $this->core->addJS(url([ADMIN, 'master', 'jenisjs']), 'footer');
      $return = $this->jenis->getIndex();
      return $this->draw('jenis.html', [
        'jenis' => $return
      ]);

    }

    public function anyJenisForm()
    {
        $return = $this->jenis->anyForm();
        echo $this->draw('jenis.form.html', ['jenis' => $return]);
        exit();
    }

    public function anyJenisDisplay()
    {
        $return = $this->jenis->anyDisplay();
        echo $this->draw('jenis.display.html', ['jenis' => $return]);
        exit();
    }

    public function postJenisSave()
    {
      $this->jenis->postSave();
      exit();
    }

    public function postJenisHapus()
    {
      $this->jenis->postHapus();
      exit();
    }

    public function getJenisJS()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/master/js/admin/jenis.js');
        exit();
    }
    /* End Jenis Section */

    /* Start KategoriBarang Section */
    public function getKategoriBarang()
    {
      $this->core->addJS(url([ADMIN, 'master', 'kategoribarangjs']), 'footer');
      $return = $this->kategoribarang->getIndex();
      return $this->draw('kategoribarang.html', [
        'kategoribarang' => $return
      ]);

    }

    public function anyKategoriBarangForm()
    {
        $return = $this->kategoribarang->anyForm();
        echo $this->draw('kategoribarang.form.html', ['kategoribarang' => $return]);
        exit();
    }

    public function anyKategoriBarangDisplay()
    {
        $return = $this->kategoribarang->anyDisplay();
        echo $this->draw('kategoribarang.display.html', ['kategoribarang' => $return]);
        exit();
    }

    public function postKategoriBarangSave()
    {
      $this->kategoribarang->postSave();
      exit();
    }

    public function postKategoriBarangHapus()
    {
      $this->kategoribarang->postHapus();
      exit();
    }

    public function getKategoriBarangJS()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/master/js/admin/kategoribarang.js');
        exit();
    }
    /* End KategoriBarang Section */

/* Start KategoriPenyakit Section */
    public function getKategoriPenyakit()
    {
      $this->core->addJS(url([ADMIN, 'master', 'kategoripenyakitjs']), 'footer');
      $return = $this->kategoripenyakit->getIndex();
      return $this->draw('kategoripenyakit.html', [
        'kategoripenyakit' => $return
      ]);

    }

    public function anyKategoriPenyakitForm()
    {
        $return = $this->kategoripenyakit->anyForm();
        echo $this->draw('kategoripenyakit.form.html', ['kategoripenyakit' => $return]);
        exit();
    }

    public function anyKategoriPenyakitDisplay()
    {
        $return = $this->kategoripenyakit->anyDisplay();
        echo $this->draw('kategoripenyakit.display.html', ['kategoripenyakit' => $return]);
        exit();
    }

    public function postKategoriPenyakitSave()
    {
      $this->kategoripenyakit->postSave();
      exit();
    }

    public function postKategoriPenyakitHapus()
    {
      $this->kategoripenyakit->postHapus();
      exit();
    }

    public function getKategoriPenyakitJS()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/master/js/admin/kategoripenyakit.js');
        exit();
    }
    /* End KategoriPenyakit Section */

/* Start KategoriPerawatan Section */
    public function getKategoriPerawatan()
    {
      $this->core->addJS(url([ADMIN, 'master', 'kategoriperawatanjs']), 'footer');
      $return = $this->kategoriperawatan->getIndex();
      return $this->draw('kategoriperawatan.html', [
        'kategoriperawatan' => $return
      ]);

    }

    public function anyKategoriPerawatanForm()
    {
        $return = $this->kategoriperawatan->anyForm();
        echo $this->draw('kategoriperawatan.form.html', ['kategoriperawatan' => $return]);
        exit();
    }

    public function anyKategoriPerawatanDisplay()
    {
        $return = $this->kategoriperawatan->anyDisplay();
        echo $this->draw('kategoriperawatan.display.html', ['kategoriperawatan' => $return]);
        exit();
    }

    public function postKategoriPerawatanSave()
    {
      $this->kategoriperawatan->postSave();
      exit();
    }

    public function postKategoriPerawatanHapus()
    {
      $this->kategoriperawatan->postHapus();
      exit();
    }

    public function getKategoriPerawatanJS()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/master/js/admin/kategoriperawatan.js');
        exit();
    }
    /* End KategoriPerawatan Section */

/* Start KodeSatuan Section */
    public function getKodeSatuan()
    {
      $this->core->addJS(url([ADMIN, 'master', 'kodesatuanjs']), 'footer');
      $return = $this->kodesatuan->getIndex();
      return $this->draw('kodesatuan.html', [
        'kodesatuan' => $return
      ]);

    }

    public function anyKodeSatuanForm()
    {
        $return = $this->kodesatuan->anyForm();
        echo $this->draw('kodesatuan.form.html', ['kodesatuan' => $return]);
        exit();
    }

    public function anyKodeSatuanDisplay()
    {
        $return = $this->kodesatuan->anyDisplay();
        echo $this->draw('kodesatuan.display.html', ['kodesatuan' => $return]);
        exit();
    }

    public function postKodeSatuanSave()
    {
      $this->kodesatuan->postSave();
      exit();
    }

    public function postKodeSatuanHapus()
    {
      $this->kodesatuan->postHapus();
      exit();
    }

    public function getKodeSatuanJS()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/master/js/admin/kodesatuan.js');
        exit();
    }
    /* End KodeSatuan Section */

/* Start MasterAturanPakai Section */
    public function getMasterAturanPakai()
    {
      $this->core->addJS(url([ADMIN, 'master', 'masteraturanpakaijs']), 'footer');
      $return = $this->masteraturanpakai->getIndex();
      return $this->draw('masteraturanpakai.html', [
        'masteraturanpakai' => $return
      ]);

    }

    public function anyMasterAturanPakaiForm()
    {
        $return = $this->masteraturanpakai->anyForm();
        echo $this->draw('masteraturanpakai.form.html', ['masteraturanpakai' => $return]);
        exit();
    }

    public function anyMasterAturanPakaiDisplay()
    {
        $return = $this->masteraturanpakai->anyDisplay();
        echo $this->draw('masteraturanpakai.display.html', ['masteraturanpakai' => $return]);
        exit();
    }

    public function postMasterAturanPakaiSave()
    {
      $this->masteraturanpakai->postSave();
      exit();
    }

    public function postMasterAturanPakaiHapus()
    {
      $this->masteraturanpakai->postHapus();
      exit();
    }

    public function getMasterAturanPakaiJS()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/master/js/admin/masteraturanpakai.js');
        exit();
    }
    /* End MasterAturanPakai Section */

/* Start MasterBerkasDigital Section */
    public function getMasterBerkasDigital()
    {
      $this->core->addJS(url([ADMIN, 'master', 'masterberkasdigitaljs']), 'footer');
      $return = $this->masterberkasdigital->getIndex();
      return $this->draw('masterberkasdigital.html', [
        'masterberkasdigital' => $return
      ]);

    }

    public function anyMasterBerkasDigitalForm()
    {
        $return = $this->masterberkasdigital->anyForm();
        echo $this->draw('masterberkasdigital.form.html', ['masterberkasdigital' => $return]);
        exit();
    }

    public function anyMasterBerkasDigitalDisplay()
    {
        $return = $this->masterberkasdigital->anyDisplay();
        echo $this->draw('masterberkasdigital.display.html', ['masterberkasdigital' => $return]);
        exit();
    }

    public function postMasterBerkasDigitalSave()
    {
      $this->masterberkasdigital->postSave();
      exit();
    }

    public function postMasterBerkasDigitalHapus()
    {
      $this->masterberkasdigital->postHapus();
      exit();
    }

    public function getMasterBerkasDigitalJS()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/master/js/admin/masterberkasdigital.js');
        exit();
    }
    /* End MasterBerkasDigital Section */

    /* Start Spesialis Section */
    public function getSpesialis()
    {
      $this->core->addJS(url([ADMIN, 'master', 'spesialisjs']), 'footer');
      $return = $this->spesialis->getIndex();
      return $this->draw('spesialis.html', [
        'spesialis' => $return
      ]);

    }

    public function anySpesialisForm()
    {
        $return = $this->spesialis->anyForm();
        echo $this->draw('spesialis.form.html', ['spesialis' => $return]);
        exit();
    }

    public function anySpesialisDisplay()
    {
        $return = $this->spesialis->anyDisplay();
        echo $this->draw('spesialis.display.html', ['spesialis' => $return]);
        exit();
    }

    public function postSpesialisSave()
    {
      $this->spesialis->postSave();
      exit();
    }

    public function postSpesialisHapus()
    {
      $this->spesialis->postHapus();
      exit();
    }

    public function getSpesialisJS()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/master/js/admin/spesialis.js');
        exit();
    }
    /* End Spesialis Section */

/* Start Bank Section */
    public function getBank()
    {
      $this->core->addJS(url([ADMIN, 'master', 'bankjs']), 'footer');
      $return = $this->bank->getIndex();
      return $this->draw('bank.html', [
        'bank' => $return
      ]);

    }

    public function anyBankForm()
    {
        $return = $this->bank->anyForm();
        echo $this->draw('bank.form.html', ['bank' => $return]);
        exit();
    }

    public function anyBankDisplay()
    {
        $return = $this->bank->anyDisplay();
        echo $this->draw('bank.display.html', ['bank' => $return]);
        exit();
    }

    public function postBankSave()
    {
      $this->bank->postSave();
      exit();
    }

    public function postBankHapus()
    {
      $this->bank->postHapus();
      exit();
    }

    public function getBankJS()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/master/js/admin/bank.js');
        exit();
    }
    /* End Bank Section */

/* Start Bidang Section */
    public function getBidang()
    {
      $this->core->addJS(url([ADMIN, 'master', 'bidangjs']), 'footer');
      $return = $this->bidang->getIndex();
      return $this->draw('bidang.html', [
        'bidang' => $return
      ]);

    }

    public function anyBidangForm()
    {
        $return = $this->bidang->anyForm();
        echo $this->draw('bidang.form.html', ['bidang' => $return]);
        exit();
    }

    public function anyBidangDisplay()
    {
        $return = $this->bidang->anyDisplay();
        echo $this->draw('bidang.display.html', ['bidang' => $return]);
        exit();
    }

    public function postBidangSave()
    {
      $this->bidang->postSave();
      exit();
    }

    public function postBidangHapus()
    {
      $this->bidang->postHapus();
      exit();
    }

    public function getBidangJS()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/master/js/admin/bidang.js');
        exit();
    }
    /* End Bidang Section */

/* Start Departemen Section */
    public function getDepartemen()
    {
      $this->core->addJS(url([ADMIN, 'master', 'departemenjs']), 'footer');
      $return = $this->departemen->getIndex();
      return $this->draw('departemen.html', [
        'departemen' => $return
      ]);

    }

    public function anyDepartemenForm()
    {
        $return = $this->departemen->anyForm();
        echo $this->draw('departemen.form.html', ['departemen' => $return]);
        exit();
    }

    public function anyDepartemenDisplay()
    {
        $return = $this->departemen->anyDisplay();
        echo $this->draw('departemen.display.html', ['departemen' => $return]);
        exit();
    }

    public function postDepartemenSave()
    {
      $this->departemen->postSave();
      exit();
    }

    public function postDepartemenHapus()
    {
      $this->departemen->postHapus();
      exit();
    }

    public function getDepartemenJS()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/master/js/admin/departemen.js');
        exit();
    }
    /* End Departemen Section */

/* Start EmergencyIndex Section */
    public function getEmergencyIndex()
    {
      $this->core->addJS(url([ADMIN, 'master', 'emergencyindexjs']), 'footer');
      $return = $this->emergencyindex->getIndex();
      return $this->draw('emergencyindex.html', [
        'emergencyindex' => $return
      ]);

    }

    public function anyEmergencyIndexForm()
    {
        $return = $this->emergencyindex->anyForm();
        echo $this->draw('emergencyindex.form.html', ['emergencyindex' => $return]);
        exit();
    }

    public function anyEmergencyIndexDisplay()
    {
        $return = $this->emergencyindex->anyDisplay();
        echo $this->draw('emergencyindex.display.html', ['emergencyindex' => $return]);
        exit();
    }

    public function postEmergencyIndexSave()
    {
      $this->emergencyindex->postSave();
      exit();
    }

    public function postEmergencyIndexHapus()
    {
      $this->emergencyindex->postHapus();
      exit();
    }

    public function getEmergencyIndexJS()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/master/js/admin/emergencyindex.js');
        exit();
    }
    /* End EmergencyIndex Section */

    /* Start Jabatan Section */
    public function getJabatan()
    {
      $this->core->addJS(url([ADMIN, 'master', 'jabatanjs']), 'footer');
      $return = $this->jabatan->getIndex();
      return $this->draw('jabatan.html', [
        'jabatan' => $return
      ]);

    }

    public function anyJabatanForm()
    {
        $return = $this->jabatan->anyForm();
        echo $this->draw('jabatan.form.html', ['jabatan' => $return]);
        exit();
    }

    public function anyJabatanDisplay()
    {
        $return = $this->jabatan->anyDisplay();
        echo $this->draw('jabatan.display.html', ['jabatan' => $return]);
        exit();
    }

    public function postJabatanSave()
    {
      $this->jabatan->postSave();
      exit();
    }

    public function postJabatanHapus()
    {
      $this->jabatan->postHapus();
      exit();
    }

    public function getJabatanJS()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/master/js/admin/jabatan.js');
        exit();
    }
    /* End Jabatan Section */

    /* Start JenjangJabatan Section */
    public function getJenjangJabatan()
    {
      $this->core->addJS(url([ADMIN, 'master', 'jenjangjabatanjs']), 'footer');
      $return = $this->jenjangjabatan->getIndex();
      return $this->draw('jenjangjabatan.html', [
        'jenjangjabatan' => $return
      ]);

    }

    public function anyJenjangJabatanForm()
    {
        $return = $this->jenjangjabatan->anyForm();
        echo $this->draw('jenjangjabatan.form.html', ['jenjangjabatan' => $return]);
        exit();
    }

    public function anyJenjangJabatanDisplay()
    {
        $return = $this->jenjangjabatan->anyDisplay();
        echo $this->draw('jenjangjabatan.display.html', ['jenjangjabatan' => $return]);
        exit();
    }

    public function postJenjangJabatanSave()
    {
      $this->jenjangjabatan->postSave();
      exit();
    }

    public function postJenjangJabatanHapus()
    {
      $this->jenjangjabatan->postHapus();
      exit();
    }

    public function getJenjangJabatanJS()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/master/js/admin/jenjangjabatan.js');
        exit();
    }
    /* End JenjangJabatan Section */

    /* Start KelompokJabatan Section */
    public function getKelompokJabatan()
    {
      $this->core->addJS(url([ADMIN, 'master', 'kelompokjabatanjs']), 'footer');
      $return = $this->kelompokjabatan->getIndex();
      return $this->draw('kelompokjabatan.html', [
        'kelompokjabatan' => $return
      ]);

    }

    public function anyKelompokJabatanForm()
    {
        $return = $this->kelompokjabatan->anyForm();
        echo $this->draw('kelompokjabatan.form.html', ['kelompokjabatan' => $return]);
        exit();
    }

    public function anyKelompokJabatanDisplay()
    {
        $return = $this->kelompokjabatan->anyDisplay();
        echo $this->draw('kelompokjabatan.display.html', ['kelompokjabatan' => $return]);
        exit();
    }

    public function postKelompokJabatanSave()
    {
      $this->kelompokjabatan->postSave();
      exit();
    }

    public function postKelompokJabatanHapus()
    {
      $this->kelompokjabatan->postHapus();
      exit();
    }

    public function getKelompokJabatanJS()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/master/js/admin/kelompokjabatan.js');
        exit();
    }
    /* End KelompokJabatan Section */

    /* Start Pendidikan Section */
    public function getPendidikan()
    {
      $this->core->addJS(url([ADMIN, 'master', 'pendidikanjs']), 'footer');
      $return = $this->pendidikan->getIndex();
      return $this->draw('pendidikan.html', [
        'pendidikan' => $return
      ]);

    }

    public function anyPendidikanForm()
    {
        $return = $this->pendidikan->anyForm();
        echo $this->draw('pendidikan.form.html', ['pendidikan' => $return]);
        exit();
    }

    public function anyPendidikanDisplay()
    {
        $return = $this->pendidikan->anyDisplay();
        echo $this->draw('pendidikan.display.html', ['pendidikan' => $return]);
        exit();
    }

    public function postPendidikanSave()
    {
      $this->pendidikan->postSave();
      exit();
    }

    public function postPendidikanHapus()
    {
      $this->pendidikan->postHapus();
      exit();
    }

    public function getPendidikanJS()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/master/js/admin/pendidikan.js');
        exit();
    }
    /* End Pendidikan Section */

    /* Start Resiko Kerja Section */
    public function getResikoKerja()
    {
      $this->core->addJS(url([ADMIN, 'master', 'resikokerjajs']), 'footer');
      $return = $this->resikokerja->getIndex();
      return $this->draw('resiko.kerja.html', [
        'resikokerja' => $return
      ]);

    }

    public function anyResikoKerjaForm()
    {
        $return = $this->resikokerja->anyForm();
        echo $this->draw('resiko.kerja.form.html', ['resikokerja' => $return]);
        exit();
    }

    public function anyResikoKerjaDisplay()
    {
        $return = $this->resikokerja->anyDisplay();
        echo $this->draw('resiko.kerja.display.html', ['resikokerja' => $return]);
        exit();
    }

    public function postResikoKerjaSave()
    {
      $this->resikokerja->postSave();
      exit();
    }

    public function postResikoKerjaHapus()
    {
      $this->resikokerja->postHapus();
      exit();
    }

    public function getResikoKerjaJS()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/master/js/admin/resikokerja.js');
        exit();
    }
    /* End Pendidikan Section */

    /* Start Status Kerja Section */
    public function getStatusKerja()
    {
      $this->core->addJS(url([ADMIN, 'master', 'statuskerjajs']), 'footer');
      $return = $this->statuskerja->getIndex();
      return $this->draw('status.kerja.html', [
        'statuskerja' => $return
      ]);

    }

    public function anyStatusKerjaForm()
    {
        $return = $this->statuskerja->anyForm();
        echo $this->draw('status.kerja.form.html', ['statuskerja' => $return]);
        exit();
    }

    public function anyStatusKerjaDisplay()
    {
        $return = $this->statuskerja->anyDisplay();
        echo $this->draw('status.kerja.display.html', ['statuskerja' => $return]);
        exit();
    }

    public function postStatusKerjaSave()
    {
      $this->statuskerja->postSave();
      exit();
    }

    public function postStatusKerjaHapus()
    {
      $this->statuskerja->postHapus();
      exit();
    }

    public function getStatusKerjaJS()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/master/js/admin/statuskerja.js');
        exit();
    }
    /* End Pendidikan Section */

    /* Start Status WP Section */
    public function getStatusWP()
    {
      $this->core->addJS(url([ADMIN, 'master', 'statuswpjs']), 'footer');
      $return = $this->statuswp->getIndex();
      return $this->draw('status.wp.html', [
        'statuswp' => $return
      ]);

    }

    public function anyStatusWPForm()
    {
        $return = $this->statuswp->anyForm();
        echo $this->draw('status.wp.form.html', ['statuswp' => $return]);
        exit();
    }

    public function anyStatusWPDisplay()
    {
        $return = $this->statuswp->anyDisplay();
        echo $this->draw('status.wp.display.html', ['statuswp' => $return]);
        exit();
    }

    public function postStatusWPSave()
    {
      $this->statuswp->postSave();
      exit();
    }

    public function postStatusWPHapus()
    {
      $this->statuswp->postHapus();
      exit();
    }

    public function getStatusWPJS()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/master/js/admin/statuswp.js');
        exit();
    }
    /* End Pendidikan Section */

    /* Start MetodeRacik Section */
    public function getMetodeRacik()
    {
      $this->core->addJS(url([ADMIN, 'master', 'metoderacikjs']), 'footer');
      $return = $this->metoderacik->getIndex();
      return $this->draw('metoderacik.html', [
        'metoderacik' => $return
      ]);

    }

    public function anyMetodeRacikForm()
    {
        $return = $this->metoderacik->anyForm();
        echo $this->draw('metoderacik.form.html', ['metoderacik' => $return]);
        exit();
    }

    public function anyMetodeRacikDisplay()
    {
        $return = $this->metoderacik->anyDisplay();
        echo $this->draw('metoderacik.display.html', ['metoderacik' => $return]);
        exit();
    }

    public function postMetodeRacikSave()
    {
      $this->metoderacik->postSave();
      exit();
    }

    public function postMetodeRacikHapus()
    {
      $this->metoderacik->postHapus();
      exit();
    }

    public function getMetodeRacikJS()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/master/js/admin/metoderacik.js');
        exit();
    }
    /* End MetodeRacik Section */

    /* Start Ruang OK Section */
    public function getRuangOk()
    {
      $this->core->addJS(url([ADMIN, 'master', 'ruangokjs']), 'footer');
      $return = $this->ruangok->getIndex();
      return $this->draw('ruangok.html', [
        'ruangok' => $return
      ]);

    }

    public function anyRuangOkForm()
    {
        $return = $this->ruangok->anyForm();
        echo $this->draw('ruangok.form.html', ['ruangok' => $return]);
        exit();
    }

    public function anyRuangOkDisplay()
    {
        $return = $this->ruangok->anyDisplay();
        echo $this->draw('ruangok.display.html', ['ruangok' => $return]);
        exit();
    }

    public function postRuangOkSave()
    {
      $this->ruangok->postSave();
      exit();
    }

    public function postRuangOkHapus()
    {
      $this->ruangok->postHapus();
      exit();
    }

    public function getRuangOkJS()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/master/js/admin/ruangok.js');
        exit();
    }
    /* End Ruang OK Section */

    public function getCSS()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/master/css/admin/master.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/master/js/admin/master.js');
        exit();
    }

    private function _addHeaderFiles()
    {
        // CSS
        $this->core->addCSS(url('assets/css/bootstrap-datetimepicker.css'));
        $this->core->addJS(url('assets/jscripts/moment-with-locales.js'));
        $this->core->addJS(url('assets/jscripts/bootstrap-datetimepicker.js'));

        // MODULE SCRIPTS
        $this->core->addCSS(url([ADMIN, 'master', 'css']));
        $this->core->addJS(url([ADMIN, 'master', 'javascript']), 'footer');
    }

}
