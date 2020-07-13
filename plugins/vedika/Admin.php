<?php

namespace Plugins\Vedika;

use Systems\AdminModule;
use Systems\Lib\BpjsRequest;

class Admin extends AdminModule
{
    public function navigation()
    {
        return [
            'Manage' => 'manage',
            'Pengaturan' => 'settings',
        ];
    }

    public function getManage($page = 1)
    {
      $this->_addHeaderFiles();
      $start_date = date('Y-m-d');
      if(isset($_GET['start_date']) && $_GET['start_date'] !='')
        $start_date = $_GET['start_date'];
      $end_date = date('Y-m-d');
      if(isset($_GET['end_date']) && $_GET['end_date'] !='')
        $end_date = $_GET['end_date'];
      $perpage = '10';
      $phrase = '';
      if(isset($_GET['s']))
        $phrase = $_GET['s'];

      // pagination
      $totalRecords = $this->db()->pdo()->prepare("SELECT reg_periksa.no_rawat FROM reg_periksa, pasien WHERE reg_periksa.no_rkm_medis = pasien.no_rkm_medis AND (reg_periksa.no_rkm_medis LIKE ? OR reg_periksa.no_rawat LIKE ? OR pasien.nm_pasien LIKE ?) AND reg_periksa.tgl_registrasi BETWEEN '$start_date' AND '$end_date'");
      $totalRecords->execute(['%'.$phrase.'%', '%'.$phrase.'%', '%'.$phrase.'%']);
      $totalRecords = $totalRecords->fetchAll();

      $pagination = new \Systems\Lib\Pagination($page, count($totalRecords), $perpage, url([ADMIN, 'vedika', 'manage', '%d?s='.$phrase.'&start_date='.$start_date.'&end_date='.$end_date]));
      $this->assign['pagination'] = $pagination->nav('pagination','5');
      $this->assign['totalRecords'] = $totalRecords;

      $offset = $pagination->offset();
      $query = $this->db()->pdo()->prepare("SELECT reg_periksa.*, pasien.*, dokter.nm_dokter, poliklinik.nm_poli, penjab.png_jawab FROM reg_periksa, pasien, dokter, poliklinik, penjab WHERE reg_periksa.no_rkm_medis = pasien.no_rkm_medis AND reg_periksa.kd_dokter = dokter.kd_dokter AND reg_periksa.kd_poli = poliklinik.kd_poli AND reg_periksa.kd_pj = penjab.kd_pj AND (reg_periksa.no_rkm_medis LIKE ? OR reg_periksa.no_rawat LIKE ? OR pasien.nm_pasien LIKE ?) AND reg_periksa.tgl_registrasi BETWEEN '$start_date' AND '$end_date' LIMIT $perpage OFFSET $offset");
      $query->execute(['%'.$phrase.'%', '%'.$phrase.'%', '%'.$phrase.'%']);
      $rows = $query->fetchAll();

      $this->assign['list'] = [];
      if (count($rows)) {
          foreach ($rows as $row) {
              $berkas_digital = $this->db('berkas_digital_perawatan')
                ->join('master_berkas_digital', 'master_berkas_digital.kode=berkas_digital_perawatan.kode')
                ->where('berkas_digital_perawatan.no_rawat', $row['no_rawat'])
                ->asc('master_berkas_digital.nama')
                ->toArray();
              $galleri_pasien = $this->db('lite_pasien_galleries_items')
                ->join('lite_pasien_galleries', 'lite_pasien_galleries.id = lite_pasien_galleries_items.gallery')
                ->where('lite_pasien_galleries.slug', $row['no_rkm_medis'])
                ->toArray();

              $berkas_digital_pasien = array();
              if (count($galleri_pasien)) {
                  foreach ($galleri_pasien as $galleri) {
                      $galleri['src'] = unserialize($galleri['src']);

                      if (!isset($galleri['src']['sm'])) {
                          $galleri['src']['sm'] = isset($galleri['src']['xs']) ? $galleri['src']['xs'] : $galleri['src']['lg'];
                      }

                      $berkas_digital_pasien[] = $galleri;
                  }
              }

              $row = htmlspecialchars_array($row);
              $row['no_sep'] = $this->_getSEPInfo('no_sep', $row['no_rawat']);
              $row['no_peserta'] = $this->_getSEPInfo('no_kartu', $row['no_rawat']);
              $row['no_rujukan'] = $this->_getSEPInfo('no_rujukan', $row['no_rawat']);
              $row['kd_penyakit'] = $this->_getDiagnosa('kd_penyakit', $row['no_rawat'], $row['status_lanjut']);
              $row['nm_penyakit'] = $this->_getDiagnosa('nm_penyakit', $row['no_rawat'], $row['status_lanjut']);
              $row['berkas_digital'] = $berkas_digital;
              $row['berkas_digital_pasien'] = $berkas_digital_pasien;
              $row['sepURL'] = url([ADMIN, 'vedika', 'sep', $row['no_sep']]);
              $row['formSepURL'] = url([ADMIN, 'vedika', 'formsepvclaim', '?no_rawat='.$row['no_rawat']]);
              $row['pdfURL'] = url([ADMIN, 'vedika', 'pdf', convertNorawat($row['no_rawat'])]);
              $row['resumeURL']  = url([ADMIN, 'vedika', 'resume', convertNorawat($row['no_rawat'])]);
              $row['billingURL'] = url([ADMIN, 'vedika', 'billing', convertNorawat($row['no_rawat'])]);
              $row['berkasPasien'] = url([ADMIN, 'vedika', 'berkaspasien', $this->core->getRegPeriksaInfo('no_rkm_medis', $row['no_rawat'])]);
              $row['berkasPerawatan'] = url([ADMIN, 'vedika', 'berkasperawatan', convertNorawat($row['no_rawat'])]);
              $this->assign['list'][] = $row;
          }
      }

      $this->core->addCSS(url('assets/jscripts/lightbox/lightbox.min.css'));
      $this->core->addJS(url('assets/jscripts/lightbox/lightbox.min.js'));

      $this->assign['searchUrl'] =  url([ADMIN, 'vedika', 'manage', $page.'?s='.$phrase.'&start_date='.$start_date.'&end_date='.$end_date]);
      return $this->draw('manage.html', ['vedika' => $this->assign]);

    }

    public function getSEP($id)
    {
      $print_sep['bridging_sep'] = $this->db('bridging_sep')->where('no_sep', $id)->oneArray();
      $batas_rujukan = $this->db('bridging_sep')->select('DATE_ADD(tglrujukan , INTERVAL 85 DAY) AS batas_rujukan')->where('no_sep', $id)->oneArray();
      $print_sep['batas_rujukan'] = $batas_rujukan['batas_rujukan'];
      $print_sep['nama_instansi'] = $this->core->getSettings('nama_instansi');
      $print_sep['logoURL'] = url(MODULES.'/pendaftaran/img/bpjslogo.png');
      $this->tpl->set('print_sep', $print_sep);
      echo $this->tpl->draw(MODULES.'/vedika/view/admin/sep.html', true);
      exit();
    }

    public function getFormSEPVClaim()
    {
      $cek_sep = array();
      $this->tpl->set('cek_sep', $cek_sep);
      echo $this->tpl->draw(MODULES.'/vedika/view/admin/form.sepvclaim.html', true);
      exit();
    }

    public function postSaveSEP()
    {
      header('Content-type: text/html');
      $date = date('Y-m-d');
      $url = $this->options->get('settings.BpjsApiUrl').'SEP/'.$_POST['no_sep'];
      $consid = $this->options->get('settings.BpjsConsID');
      $secretkey = $this->options->get('settings.BpjsSecretKey');
      $output = BpjsRequest::get($url, NULL, NULL, $consid, $secretkey);
      $data = json_decode($output, true);

      $url_rujukan = $this->options->get('settings.BpjsApiUrl').'Rujukan/'.$data['response']['noRujukan'];
      $rujukan = BpjsRequest::get($url_rujukan, NULL, NULL, $consid, $secretkey);
      $data_rujukan = json_decode($rujukan, true);

      if($data['metaData']['code'] == 200)
      {
        $insert = $this->db('bridging_sep')
          ->save([
            'no_sep' => $data['response']['noSep'],
            'no_rawat' => $_POST['no_rawat'],
            'tglsep' => $data['response']['tglSep'],
            'tglrujukan' => $data_rujukan['response']['rujukan']['tglKunjungan'],
            'no_rujukan' => $data['response']['noRujukan'],
            'kdppkrujukan' => $data_rujukan['response']['rujukan']['provPerujuk']['kode'],
            'nmppkrujukan' => $data_rujukan['response']['rujukan']['provPerujuk']['nama'],
            'kdppkpelayanan' => $this->core->getSettings('kode_ppk'),
            'nmppkpelayanan' => $this->core->getSettings('nama_instansi'),
            'jnspelayanan' => $data_rujukan['response']['rujukan']['pelayanan']['kode'],
            'catatan' => $data['response']['catatan'],
            'diagawal' => $data_rujukan['response']['rujukan']['diagnosa']['kode'],
            'nmdiagnosaawal' => $data_rujukan['response']['rujukan']['diagnosa']['nama'],
            'kdpolitujuan' => $this->db('maping_poli_bpjs')->where('kd_poli_rs', $this->core->getRegPeriksaInfo('kd_poli', $_POST['no_rawat']))->oneArray()['kd_poli_bpjs'],
            'nmpolitujuan' => $this->db('maping_poli_bpjs')->where('kd_poli_rs', $this->core->getRegPeriksaInfo('kd_poli', $_POST['no_rawat']))->oneArray()['nm_poli_bpjs'],
            'klsrawat' =>  $data_rujukan['response']['rujukan']['peserta']['hakKelas']['kode'],
            'lakalantas' => '0',
            'user' => $this->core->getUserInfo('username', null, true),
            'nomr' => $this->core->getRegPeriksaInfo('no_rkm_medis', $_POST['no_rawat']),
            'nama_pasien' => $data['response']['peserta']['nama'],
            'tanggal_lahir' => $data['response']['peserta']['tglLahir'],
            'peserta' => $data['response']['peserta']['jnsPeserta'],
            'jkel' => $data['response']['peserta']['kelamin'],
            'no_kartu' => $data['response']['peserta']['noKartu'],
            'tglpulang' => '1900-01-01 00:00:00',
            'asal_rujukan' => $_POST['asal_rujukan'],
            'eksekutif' => $data['response']['poliEksekutif'],
            'cob' => '0',
            'penjamin' => '-',
            'notelep' => $data_rujukan['response']['rujukan']['peserta']['mr']['noTelepon'],
            'katarak' => '0',
            'tglkkl' => '1900-01-01',
            'keterangankkl' => '-',
            'suplesi' => '0',
            'no_sep_suplesi' => '-',
            'kdprop' => '-',
            'nmprop' => '-',
            'kdkab' => '-',
            'nmkab' => '-',
            'kdkec' => '-',
            'nmkec' => '-',
            'noskdp' => '0',
            'kddpjp' => $this->db('maping_dokter_dpjpvclaim')->where('kd_dokter', $this->core->getRegPeriksaInfo('kd_dokter', $_POST['no_rawat']))->oneArray()['kd_dokter_bpjs'],
            'nmdpdjp' => $this->db('maping_dokter_dpjpvclaim')->where('kd_dokter', $this->core->getRegPeriksaInfo('kd_dokter', $_POST['no_rawat']))->oneArray()['nm_dokter_bpjs']
          ]);
      }

      if ($insert) {
          $this->notify('success', 'Simpan sukes');
      } else {
          $this->notify('failure', 'Simpan gagal');
      }
      redirect(url([ADMIN, 'vedika', 'manage']));
    }
    public function getPDF($id)
    {
      $berkas_digital = $this->db('berkas_digital_perawatan')
        ->join('master_berkas_digital', 'master_berkas_digital.kode=berkas_digital_perawatan.kode')
        ->where('berkas_digital_perawatan.no_rawat', revertNorawat($id))
        ->asc('master_berkas_digital.nama')
        ->toArray();

      $galleri_pasien = $this->db('lite_pasien_galleries_items')
        ->join('lite_pasien_galleries', 'lite_pasien_galleries.id = lite_pasien_galleries_items.gallery')
        ->where('lite_pasien_galleries.slug', $this->core->getRegPeriksaInfo('no_rkm_medis', revertNorawat($id)))
        ->toArray();

      $berkas_digital_pasien = array();
      if (count($galleri_pasien)) {
          foreach ($galleri_pasien as $galleri) {
              $galleri['src'] = unserialize($galleri['src']);

              if (!isset($galleri['src']['sm'])) {
                  $galleri['src']['sm'] = isset($galleri['src']['xs']) ? $galleri['src']['xs'] : $galleri['src']['lg'];
              }

              $berkas_digital_pasien[] = $galleri;
          }
      }

      $no_rawat = revertNorawat($id);
      $query = $this->db()->pdo()->prepare("select no,nm_perawatan,pemisah,if(biaya=0,'',biaya),if(jumlah=0,'',jumlah),if(tambahan=0,'',tambahan),if(totalbiaya=0,'',totalbiaya),totalbiaya from billing where no_rawat='$no_rawat'");
      $query->execute();
      $rows = $query->fetchAll();
      $total=0;
      foreach ($rows as $key => $value) {
        $total = $total+$value['7'];
      }
      $total = $total;
      $this->tpl->set('total', $total);

      $instansi['logo'] = $this->core->getSettings('logo');
      $instansi['nama_instansi'] = $this->core->getSettings('nama_instansi');
      $instansi['alamat_instansi'] = $this->core->getSettings('alamat_instansi');
      $instansi['kabupaten'] = $this->core->getSettings('kabupaten');
      $instansi['propinsi'] = $this->core->getSettings('propinsi');
      $instansi['kontak'] = $this->core->getSettings('kontak');
      $instansi['email'] = $this->core->getSettings('email');

      $this->tpl->set('billing', $rows);
      $this->tpl->set('instansi', $instansi);

      $print_sep = array();
      if(!empty($this->_getSEPInfo('no_sep', $no_rawat))) {
        $print_sep['bridging_sep'] = $this->db('bridging_sep')->where('no_sep', $this->_getSEPInfo('no_sep', $no_rawat))->oneArray();
        $batas_rujukan = $this->db('bridging_sep')->select('DATE_ADD(tglrujukan , INTERVAL 85 DAY) AS batas_rujukan')->where('no_sep', $id)->oneArray();
        $print_sep['batas_rujukan'] = $batas_rujukan['batas_rujukan'];
      }
      $print_sep['nama_instansi'] = $this->core->getSettings('nama_instansi');
      $print_sep['logoURL'] = url(MODULES.'/pendaftaran/img/bpjslogo.png');
      $this->tpl->set('print_sep', $print_sep);

      $resume_pasien = $this->db('resume_pasien')
        ->join('dokter', 'dokter.kd_dokter = resume_pasien.kd_dokter')
        ->where('no_rawat', revertNorawat($id))
        ->oneArray();
      $this->tpl->set('resume_pasien', $resume_pasien);


      $this->tpl->set('berkas_digital', $berkas_digital);
      $this->tpl->set('berkas_digital_pasien', $berkas_digital_pasien);
      echo $this->tpl->draw(MODULES.'/vedika/view/admin/pdf.html', true);
      exit();
    }

    public function getResume($id)
    {
      $resume_pasien = $this->db('resume_pasien')
        ->join('dokter', 'dokter.kd_dokter = resume_pasien.kd_dokter')
        ->where('no_rawat', revertNorawat($id))
        ->oneArray();
      $this->tpl->set('resume_pasien', $resume_pasien);
      echo $this->tpl->draw(MODULES.'/vedika/view/admin/resume.html', true);
      exit();
    }

    public function getBilling($no_rawat)
    {
      $no_rawat = revertNorawat($no_rawat);
      $query = $this->db()->pdo()->prepare("select no,nm_perawatan,pemisah,if(biaya=0,'',biaya),if(jumlah=0,'',jumlah),if(tambahan=0,'',tambahan),if(totalbiaya=0,'',totalbiaya),totalbiaya from billing where no_rawat='$no_rawat'");
      $query->execute();
      $rows = $query->fetchAll();
      $total=0;
      foreach ($rows as $key => $value) {
        $total = $total+$value['7'];
      }
      $total = $total;
      $this->tpl->set('total', $total);

      $instansi['logo'] = $this->core->getSettings('logo');
      $instansi['nama_instansi'] = $this->core->getSettings('nama_instansi');
      $instansi['alamat_instansi'] = $this->core->getSettings('alamat_instansi');
      $instansi['kabupaten'] = $this->core->getSettings('kabupaten');
      $instansi['propinsi'] = $this->core->getSettings('propinsi');
      $instansi['kontak'] = $this->core->getSettings('kontak');
      $instansi['email'] = $this->core->getSettings('email');

      $this->tpl->set('billing', $rows);
      $this->tpl->set('instansi', $instansi);

      echo $this->tpl->draw(MODULES.'/vedika/view/admin/billing.html', true);
      exit();
    }

    public function getBerkasPasien()
    {
      echo $this->tpl->draw(MODULES.'/vedika/view/admin/berkaspasien.html', true);
      exit();
    }

    public function getBerkasPerawatan()
    {
      echo $this->tpl->draw(MODULES.'/vedika/view/admin/berkasperawatan.html', true);
      exit();
    }
    private function _getSEPInfo($field, $no_rawat)
    {
        $row = $this->db('bridging_sep')->where('no_rawat', $no_rawat)->oneArray();
        return $row[$field];
    }

    private function _getDiagnosa($field, $no_rawat, $status_lanjut)
    {
        $row = $this->db('diagnosa_pasien')->join('penyakit', 'penyakit.kd_penyakit = diagnosa_pasien.kd_penyakit')->where('diagnosa_pasien.no_rawat', $no_rawat)->where('diagnosa_pasien.prioritas', 1)->where('diagnosa_pasien.status', $status_lanjut)->oneArray();
        return $row[$field];
    }

    public function getSettings()
    {
        $this->_addHeaderFiles();
        $this->assign['title'] = 'Pengaturan Modul Vedika';
        $this->assign['vedika'] = htmlspecialchars_array($this->options('vedika'));
        return $this->draw('settings.html', ['settings' => $this->assign]);
    }

    public function postSaveSettings()
    {
        foreach ($_POST['vedika'] as $key => $val) {
            $this->options('vedika', $key, $val);
        }
        $this->notify('success', 'Pengaturan telah disimpan');
        redirect(url([ADMIN, 'vedika', 'settings']));
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/vedika/js/admin/scripts.js');
        exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/vedika/css/admin/styles.css');
        exit();
    }

    private function _addHeaderFiles()
    {
        // CSS
        $this->core->addCSS(url('assets/css/jquery-ui.css'));
        $this->core->addCSS(url('assets/css/jquery.timepicker.css'));
        $this->core->addCSS(url('assets/css/dataTables.bootstrap.min.css'));

        // JS
        $this->core->addJS(url('assets/jscripts/jquery-ui.js'), 'footer');
        $this->core->addJS(url('assets/jscripts/jquery.timepicker.js'), 'footer');
        $this->core->addJS(url('assets/jscripts/jquery.dataTables.min.js'), 'footer');
        $this->core->addJS(url('assets/jscripts/dataTables.bootstrap.min.js'), 'footer');

        // MODULE SCRIPTS
        $this->core->addCSS(url([ADMIN, 'vedika', 'css']));
        $this->core->addJS(url([ADMIN, 'vedika', 'javascript']), 'footer');
    }

}
