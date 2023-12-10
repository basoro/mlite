<?php

namespace Plugins\Veronisa;

use Systems\SiteModule;
use Systems\Lib\QRCode;

class Site extends SiteModule
{

    protected $mlite;
    protected $assign;

    public function init()
    {
        $this->mlite['notify']         = $this->core->getNotify();
        $this->mlite['logo']           = $this->settings->get('settings.logo');
        $this->mlite['nama_instansi']  = $this->settings->get('settings.nama_instansi');
        $this->mlite['path']           = url();
        $this->mlite['version']        = $this->core->settings->get('settings.version');
        $this->mlite['token']          = '  ';
        if ($this->_loginCheck()) {
            $this->mlite['veronisa_user']    = $_SESSION['veronisa_user'];
            $this->mlite['veronisa_token']   = $_SESSION['veronisa_token'];
        }
        $this->mlite['slug']           = parseURL();
    }

    public function routes()
    {
        $this->route('vero', 'getIndex');
        $this->route('vero/index/(:int)', 'getIndex');
        $this->route('vero/css', 'getCss');
        $this->route('vero/javascript', 'getJavascript');
        $this->route('vero/catatan/(:str)', 'getCatatan');
        $this->route('vero/pdf/(:str)', 'getPDF');
        $this->route('vero/createpdf/(:str)', 'getCreatePDF');
        $this->route('vero/downloadpdf/(:str)', 'getDownloadPDF');
        $this->route('vero/logout', function () {
            $this->logout();
        });
    }

    public function getIndex()
    {
        if ($this->_loginCheck()) {
            $page = [
                'title' => 'Veronisa',
                'desc' => 'Dashboard Verifikasi Obat Kronis Aura Syifa',
                'content' => $this->_getManage()
            ];
            if(isset($_POST['perbaiki'])) {
              $simpan_status = $this->db('mlite_veronisa')
              ->where('nosep', $_POST['nosep'])
              ->save([
                'status' => 'Perbaiki'
              ]);
              if($simpan_status) {
                $this->db('mlite_veronisa_feedback')->save([
                  'id' => NULL,
                  'nosep' => $_POST['nosep'],
                  'tanggal' => date('Y-m-d'),
                  'catatan' => $_POST['catatan'],
                  'username' => $_SESSION['veronisa_user']
                ]);
              }
            }
        } else {
            if (isset($_POST['login'])) {
                if ($this->_login($_POST['username'], $_POST['password'])) {
                    if (count($arrayURL = parseURL()) > 1) {
                        $url = array_merge(['veronisa'], $arrayURL);
                        redirect(url($url));
                    }
                }
                redirect(url(['vero', '']));
            }
            $page = [
                'title' => 'Veronisa',
                'desc' => 'Dashboard Verifikasi Obat Kronis Aura Syifa',
                'content' => $this->draw('login.html', ['mlite' => $this->mlite])
            ];
        }

        $this->setTemplate('fullpage.html');
        $this->tpl->set('page', $page);

    }

    public function _getManage($page = 1)
    {
      $this->_addHeaderFiles();
      $perpage = '10';
      $phrase = '';
      if(isset($_GET['s']))
        $phrase = $_GET['s'];
      $start_date = date('Y-m-d');
      if (isset($_GET['start_date']) && $_GET['start_date'] != '')
        $start_date = $_GET['start_date'];
      $end_date = date('Y-m-d');
      if (isset($_GET['end_date']) && $_GET['end_date'] != '')
        $end_date = $_GET['end_date'];

      $slug = parseURL();
      if (count($slug) == 4 && $slug[0] == 'vero' && $slug[1] == 'index') {
        $page = $slug[2];
      }

      // pagination
      $totalRecords = $this->db()->pdo()->prepare("SELECT reg_periksa.no_rawat FROM reg_periksa, pasien, mlite_veronisa WHERE reg_periksa.no_rkm_medis = pasien.no_rkm_medis AND reg_periksa.no_rawat = mlite_veronisa.no_rawat AND (reg_periksa.no_rkm_medis LIKE ? OR reg_periksa.no_rawat LIKE ? OR pasien.nm_pasien LIKE ? OR mlite_veronisa.nosep LIKE ?) AND reg_periksa.tgl_registrasi BETWEEN '$start_date' AND '$end_date' AND reg_periksa.status_lanjut = 'Ralan'");
      $totalRecords->execute(['%' . $phrase . '%', '%' . $phrase . '%', '%' . $phrase . '%', '%' . $phrase . '%']);
      $totalRecords = $totalRecords->fetchAll();

      $pagination = new \Systems\Lib\Pagination($page, count($totalRecords), $perpage, url(['vero', 'index', '%d/?s=' . $phrase . '&start_date=' . $start_date . '&end_date=' . $end_date]));
      $this->assign['pagination'] = $pagination->nav('pagination', '5');
      $this->assign['totalRecords'] = $totalRecords;

      $offset = $pagination->offset();
      $query = $this->db()->pdo()->prepare("SELECT reg_periksa.*, pasien.*, dokter.nm_dokter, poliklinik.nm_poli, mlite_veronisa.no_rawat, mlite_veronisa.nosep FROM reg_periksa, pasien, dokter, poliklinik, mlite_veronisa WHERE reg_periksa.no_rkm_medis = pasien.no_rkm_medis AND reg_periksa.kd_dokter = dokter.kd_dokter AND reg_periksa.kd_poli = poliklinik.kd_poli AND reg_periksa.no_rawat = mlite_veronisa.no_rawat AND (reg_periksa.no_rkm_medis LIKE ? OR reg_periksa.no_rawat LIKE ? OR pasien.nm_pasien LIKE ? OR mlite_veronisa.nosep LIKE ?) AND reg_periksa.tgl_registrasi BETWEEN '$start_date' AND '$end_date' AND reg_periksa.status_lanjut = 'Ralan' LIMIT $perpage OFFSET $offset");
      $query->execute(['%' . $phrase . '%', '%' . $phrase . '%', '%' . $phrase . '%', '%' . $phrase . '%']);
      $rows = $query->fetchAll();

      $this->assign['list'] = [];
      if (count($rows)) {
          foreach ($rows as $row) {
              $berkas_digital = $this->db('berkas_digital_perawatan')
                ->join('master_berkas_digital', 'master_berkas_digital.kode=berkas_digital_perawatan.kode')
                ->where('berkas_digital_perawatan.no_rawat', $row['no_rawat'])
                ->asc('master_berkas_digital.nama')
                ->toArray();

              $row = htmlspecialchars_array($row);
              $row['berkas_digital'] = $berkas_digital;
              $row['catatanURL'] = url(['vero', 'catatan', $this->_getSEPInfo('no_sep', $row['no_rawat'])]);
              $row['status_pengajuan'] = $this->db('mlite_veronisa')->where('nosep', $this->_getSEPInfo('no_sep', $row['no_rawat']))->desc('id')->limit(1)->toArray();
              $row['pdfURL'] = url(['vero', 'pdf', $this->convertNorawat($row['no_rawat'])]);
              $row['downloadURL'] = url(['vero', 'downloadpdf', $this->convertNorawat($row['no_rawat'])]);
              $this->assign['list'][] = $row;
          }
      }

      $this->assign['veronisa_username'] = $this->settings->get('veronisa.username');
      $this->assign['veronisa_password'] = $this->settings->get('veronisa.password');

      $this->assign['searchUrl'] =  url(['vero', 'index', $page.'?start_date='.$start_date.'&end_date='.$end_date]);
      return $this->draw('index.html', ['veronisa' => $this->assign]);

    }

    public function getCatatan($id)
    {
      $set_status = $this->db('bridging_sep')->where('no_sep', $id)->oneArray();
      $veronisa = $this->db('mlite_veronisa')->where('nosep', $id)->asc('id')->toArray();
      $veronisa_feedback = $this->db('mlite_veronisa_feedback')->where('nosep', $id)->asc('id')->toArray();
      $this->tpl->set('logo', $this->settings->get('settings.logo'));
      $this->tpl->set('nama_instansi', $this->settings->get('settings.nama_instansi'));
      $this->tpl->set('set_status', $set_status);
      $this->tpl->set('veronisa', $veronisa);
      $this->tpl->set('veronisa_feedback', $veronisa_feedback);
      $this->tpl->set('username', $_SESSION['veronisa_user']);
      echo $this->tpl->draw(MODULES.'/veronisa/view/catatan.html', true);
      exit();
    }

    public function getPDF($id)
    {
      if ($this->_loginCheck()) {

        $berkas_digital = $this->db('berkas_digital_perawatan')
          ->join('master_berkas_digital', 'master_berkas_digital.kode=berkas_digital_perawatan.kode')
          ->where('berkas_digital_perawatan.no_rawat', $this->revertNorawat($id))
          ->asc('master_berkas_digital.nama')
          ->toArray();

        $no_rawat = $this->revertNorawat($id);

        /** Billing versi mlite */

        $billing_mlite_settings = $this->settings('settings');
        $this->tpl->set('billing_mlite_settings', $this->tpl->noParse_array(htmlspecialchars_array($billing_mlite_settings)));
        $billing_mlite_reg_periksa = $this->db('reg_periksa')->where('no_rawat', $no_rawat)->oneArray();
        $billing_mlite_pasien = $this->db('pasien')->where('no_rkm_medis', $billing_mlite_reg_periksa['no_rkm_medis'])->oneArray();

        $billing_result = $this->db('mlite_billing')->where('no_rawat', $no_rawat)->like('kd_billing', 'RJ%')->desc('id_billing')->oneArray();

        $result_detail['poliklinik'] = $this->db('poliklinik')
          ->join('reg_periksa', 'reg_periksa.kd_poli = poliklinik.kd_poli')
          ->where('reg_periksa.no_rawat', $no_rawat)
          ->oneArray();

        $result_detail['rawat_jl_dr'] = $this->db('rawat_jl_dr')
          ->select('jns_perawatan.nm_perawatan')
          ->select(['biaya_rawat' => 'rawat_jl_dr.biaya_rawat'])
          ->select(['jml' => 'COUNT(rawat_jl_dr.kd_jenis_prw)'])
          ->select(['total_biaya_rawat_dr' => 'SUM(rawat_jl_dr.biaya_rawat)'])
          ->join('jns_perawatan', 'jns_perawatan.kd_jenis_prw = rawat_jl_dr.kd_jenis_prw')
          ->where('rawat_jl_dr.no_rawat', $no_rawat)
          ->group('jns_perawatan.nm_perawatan')
          ->toArray();

        $total_rawat_jl_dr = 0;
        foreach ($result_detail['rawat_jl_dr'] as $row) {
          $total_rawat_jl_dr += $row['biaya_rawat'];
        }

        $result_detail['rawat_jl_pr'] = $this->db('rawat_jl_pr')
          ->select('jns_perawatan.nm_perawatan')
          ->select(['biaya_rawat' => 'rawat_jl_pr.biaya_rawat'])
          ->select(['jml' => 'COUNT(rawat_jl_pr.kd_jenis_prw)'])
          ->select(['total_biaya_rawat_pr' => 'SUM(rawat_jl_pr.biaya_rawat)'])
          ->join('jns_perawatan', 'jns_perawatan.kd_jenis_prw = rawat_jl_pr.kd_jenis_prw')
          ->where('rawat_jl_pr.no_rawat', $no_rawat)
          ->group('jns_perawatan.nm_perawatan')
          ->toArray();

        $total_rawat_jl_pr = 0;
        foreach ($result_detail['rawat_jl_pr'] as $row) {
          $total_rawat_jl_pr += $row['biaya_rawat'];
        }

        $result_detail['rawat_jl_drpr'] = $this->db('rawat_jl_drpr')
          ->select('jns_perawatan.nm_perawatan')
          ->select(['biaya_rawat' => 'rawat_jl_drpr.biaya_rawat'])
          ->select(['jml' => 'COUNT(rawat_jl_drpr.kd_jenis_prw)'])
          ->select(['total_biaya_rawat_drpr' => 'SUM(rawat_jl_drpr.biaya_rawat)'])
          ->join('jns_perawatan', 'jns_perawatan.kd_jenis_prw = rawat_jl_drpr.kd_jenis_prw')
          ->where('rawat_jl_drpr.no_rawat', $no_rawat)
          ->group('jns_perawatan.nm_perawatan')
          ->toArray();

        $total_rawat_jl_drpr = 0;
        foreach ($result_detail['rawat_jl_drpr'] as $row) {
          $total_rawat_jl_drpr += $row['biaya_rawat'];
        }

        $result_detail['detail_pemberian_obat'] = $this->db('detail_pemberian_obat')
          ->join('databarang', 'databarang.kode_brng=detail_pemberian_obat.kode_brng')
          ->where('no_rawat', $no_rawat)
          ->where('detail_pemberian_obat.status', 'Ralan')
          ->toArray();

        $total_detail_pemberian_obat = 0;
        foreach ($result_detail['detail_pemberian_obat'] as $row) {
          $total_detail_pemberian_obat += $row['total'];
        }

        $result_detail['periksa_lab'] = $this->db('periksa_lab')
          ->join('jns_perawatan_lab', 'jns_perawatan_lab.kd_jenis_prw=periksa_lab.kd_jenis_prw')
          ->where('no_rawat', $no_rawat)
          ->where('periksa_lab.status', 'Ralan')
          ->toArray();

        $total_periksa_lab = 0;
        foreach ($result_detail['periksa_lab'] as $row) {
          $total_periksa_lab += $row['biaya'];
        }

        $result_detail['periksa_radiologi'] = $this->db('periksa_radiologi')
          ->join('jns_perawatan_radiologi', 'jns_perawatan_radiologi.kd_jenis_prw=periksa_radiologi.kd_jenis_prw')
          ->where('no_rawat', $no_rawat)
          ->where('periksa_radiologi.status', 'Ralan')
          ->toArray();

        $total_periksa_radiologi = 0;
        foreach ($result_detail['periksa_radiologi'] as $row) {
          $total_periksa_radiologi += $row['biaya'];
        }

        $jumlah_total_operasi = 0;
        $operasis = $this->db('operasi')->join('paket_operasi', 'paket_operasi.kode_paket=operasi.kode_paket')->where('no_rawat', $no_rawat)->where('operasi.status', 'Ralan')->toArray();
        $result_detail['operasi'] = [];
        foreach ($operasis as $operasi) {
          $operasi['jumlah'] = $operasi['biayaoperator1']+$operasi['biayaoperator2']+$operasi['biayaoperator3']+$operasi['biayaasisten_operator1']+$operasi['biayaasisten_operator2']+$operasi['biayadokter_anak']+$operasi['biayaperawaat_resusitas']+$operasi['biayadokter_anestesi']+$operasi['biayaasisten_anestesi']+$operasi['biayabidan']+$operasi['biayaperawat_luar'];
          $jumlah_total_operasi += $operasi['jumlah'];
          $result_detail['operasi'][] = $operasi;
        }
        $jumlah_total_obat_operasi = 0;
        $obat_operasis = $this->db('beri_obat_operasi')->join('obatbhp_ok', 'obatbhp_ok.kd_obat=beri_obat_operasi.kd_obat')->where('no_rawat', $no_rawat)->toArray();
        $result_detail['obat_operasi'] = [];
        foreach ($obat_operasis as $obat_operasi) {
          $obat_operasi['harga'] = $obat_operasi['hargasatuan'] * $obat_operasi['jumlah'];
          $jumlah_total_obat_operasi += $obat_operasi['harga'];
          $result_detail['obat_operasi'][] = $obat_operasi;
        }

        $qr=QRCode::getMinimumQRCode($this->core->getUserInfo('fullname', null, true),QR_ERROR_CORRECT_LEVEL_L);
        //$qr=QRCode::getMinimumQRCode('Petugas: '.$this->core->getUserInfo('fullname', null, true).'; Lokasi: '.UPLOADS.'/invoices/'.$result['kd_billing'].'.pdf',QR_ERROR_CORRECT_LEVEL_L);
        $im=$qr->createImage(4,4);
        imagepng($im,BASE_DIR.'/tmp/qrcode.png');
        imagedestroy($im);

        $image = BASE_DIR."/tmp/qrcode.png";
        $qrCode = "../../tmp/qrcode.png";

        $this->tpl->set('billing_mlite_detail', $result_detail);
        $this->tpl->set('billing_mlite', $billing_result);
        $this->tpl->set('billing_mlite_qrcode', $qrCode);
        $this->tpl->set('billing_mlite_kasir', $this->core->getUserInfo('fullname', null, true));
        $this->tpl->set('billing_mlite_pasien', $billing_mlite_pasien);
        $this->tpl->set('billing_mlite_veronisa', htmlspecialchars_array($this->settings('veronisa')));

        /** End billing versi mlite **/


        $print_sep = array();
        if(!empty($this->_getSEPInfo('no_sep', $no_rawat))) {
          $print_sep['bridging_sep'] = $this->db('bridging_sep')->where('no_sep', $this->_getSEPInfo('no_sep', $no_rawat))->oneArray();
          $print_sep['bpjs_prb'] = $this->db('bpjs_prb')->where('no_sep', $this->_getSEPInfo('no_sep', $no_rawat))->oneArray();
          $batas_rujukan = $this->db('bridging_sep')->select('DATE_ADD(tglrujukan , INTERVAL 85 DAY) AS batas_rujukan')->where('no_sep', $id)->oneArray();
          $print_sep['batas_rujukan'] = $batas_rujukan['batas_rujukan'];
        }

        $print_sep['logoURL'] = url(MODULES.'/pendaftaran/img/bpjslogo.png');
        $this->tpl->set('print_sep', $print_sep);

        /*
        $resume_pasien = $this->db('resume_pasien')
          ->join('dokter', 'dokter.kd_dokter = resume_pasien.kd_dokter')
          ->where('no_rawat', $this->revertNorawat($id))
          ->oneArray();
        $this->tpl->set('resume_pasien', $resume_pasien);
        */

        $pasien = $this->db('pasien')
          ->join('kecamatan', 'kecamatan.kd_kec = pasien.kd_kec')
          ->join('kabupaten', 'kabupaten.kd_kab = pasien.kd_kab')
          ->where('no_rkm_medis', $this->core->getRegPeriksaInfo('no_rkm_medis', $this->revertNorawat($id)))
          ->oneArray();
        $reg_periksa = $this->db('reg_periksa')
          ->join('dokter', 'dokter.kd_dokter = reg_periksa.kd_dokter')
          ->join('poliklinik', 'poliklinik.kd_poli = reg_periksa.kd_poli')
          ->join('penjab', 'penjab.kd_pj = reg_periksa.kd_pj')
          ->where('stts', '<>', 'Batal')
          ->where('no_rawat', $this->revertNorawat($id))
          ->oneArray();
        /*
        $rujukan_internal = $this->db('rujukan_internal_poli')
          ->join('poliklinik', 'poliklinik.kd_poli = rujukan_internal_poli.kd_poli')
          ->join('dokter', 'dokter.kd_dokter = rujukan_internal_poli.kd_dokter')
          ->where('no_rawat', $this->revertNorawat($id))
          ->oneArray();
        */
        $rows_dpjp_ranap = $this->db('dpjp_ranap')
          ->join('dokter', 'dokter.kd_dokter = dpjp_ranap.kd_dokter')
          ->where('no_rawat', $this->revertNorawat($id))
          ->toArray();
        $dpjp_i = 1;
        $dpjp_ranap = [];
        foreach ($rows_dpjp_ranap as $row) {
          $row['nomor'] = $dpjp_i++;
          $dpjp_ranap[] = $row;
        }
        $diagnosa_pasien = $this->db('diagnosa_pasien')
          ->join('penyakit', 'penyakit.kd_penyakit = diagnosa_pasien.kd_penyakit')
          ->where('no_rawat', $this->revertNorawat($id))
          ->toArray();
        $prosedur_pasien = $this->db('prosedur_pasien')
          ->join('icd9', 'icd9.kode = prosedur_pasien.kode')
          ->where('no_rawat', $this->revertNorawat($id))
          ->toArray();
        $pemeriksaan_ralan = $this->db('pemeriksaan_ralan')
          ->where('no_rawat', $this->revertNorawat($id))
          ->asc('tgl_perawatan')
          ->asc('jam_rawat')
          ->toArray();
        $pemeriksaan_ranap = $this->db('pemeriksaan_ranap')
          ->where('no_rawat', $this->revertNorawat($id))
          ->asc('tgl_perawatan')
          ->asc('jam_rawat')
          ->toArray();
        $rawat_jl_dr = $this->db('rawat_jl_dr')
          ->join('jns_perawatan', 'rawat_jl_dr.kd_jenis_prw=jns_perawatan.kd_jenis_prw')
          ->join('dokter', 'rawat_jl_dr.kd_dokter=dokter.kd_dokter')
          ->where('no_rawat', $this->revertNorawat($id))
          ->toArray();
        $rawat_jl_pr = $this->db('rawat_jl_pr')
          ->join('jns_perawatan', 'rawat_jl_pr.kd_jenis_prw=jns_perawatan.kd_jenis_prw')
          ->join('petugas', 'rawat_jl_pr.nip=petugas.nip')
          ->where('no_rawat', $this->revertNorawat($id))
          ->toArray();
        $rawat_jl_drpr = $this->db('rawat_jl_drpr')
          ->join('jns_perawatan', 'rawat_jl_drpr.kd_jenis_prw=jns_perawatan.kd_jenis_prw')
          ->join('dokter', 'rawat_jl_drpr.kd_dokter=dokter.kd_dokter')
          ->join('petugas', 'rawat_jl_drpr.nip=petugas.nip')
          ->where('no_rawat', $this->revertNorawat($id))
          ->toArray();
        $rawat_inap_dr = $this->db('rawat_inap_dr')
          ->join('jns_perawatan_inap', 'rawat_inap_dr.kd_jenis_prw=jns_perawatan_inap.kd_jenis_prw')
          ->join('dokter', 'rawat_inap_dr.kd_dokter=dokter.kd_dokter')
          ->where('no_rawat', $this->revertNorawat($id))
          ->toArray();
        $rawat_inap_pr = $this->db('rawat_inap_pr')
          ->join('jns_perawatan_inap', 'rawat_inap_pr.kd_jenis_prw=jns_perawatan_inap.kd_jenis_prw')
          ->join('petugas', 'rawat_inap_pr.nip=petugas.nip')
          ->where('no_rawat', $this->revertNorawat($id))
          ->toArray();
        $rawat_inap_drpr = $this->db('rawat_inap_drpr')
          ->join('jns_perawatan_inap', 'rawat_inap_drpr.kd_jenis_prw=jns_perawatan_inap.kd_jenis_prw')
          ->join('dokter', 'rawat_inap_drpr.kd_dokter=dokter.kd_dokter')
          ->join('petugas', 'rawat_inap_drpr.nip=petugas.nip')
          ->where('no_rawat', $this->revertNorawat($id))
          ->toArray();
        $kamar_inap = $this->db('kamar_inap')
          ->join('kamar', 'kamar_inap.kd_kamar=kamar.kd_kamar')
          ->join('bangsal', 'kamar.kd_bangsal=bangsal.kd_bangsal')
          ->where('no_rawat', $this->revertNorawat($id))
          ->toArray();
        $operasi = $this->db('operasi')
          ->join('paket_operasi', 'operasi.kode_paket=paket_operasi.kode_paket')
          ->where('no_rawat', $this->revertNorawat($id))
          ->toArray();
        $tindakan_radiologi = $this->db('periksa_radiologi')
          ->join('jns_perawatan_radiologi', 'periksa_radiologi.kd_jenis_prw=jns_perawatan_radiologi.kd_jenis_prw')
          ->join('dokter', 'periksa_radiologi.kd_dokter=dokter.kd_dokter')
          ->join('petugas', 'periksa_radiologi.nip=petugas.nip')
          ->where('no_rawat', $this->revertNorawat($id))
          ->toArray();
        $hasil_radiologi = $this->db('hasil_radiologi')
          ->where('no_rawat', $this->revertNorawat($id))
          ->toArray();
        $pemeriksaan_laboratorium = [];
        $rows_pemeriksaan_laboratorium = $this->db('periksa_lab')
          ->join('jns_perawatan_lab', 'jns_perawatan_lab.kd_jenis_prw=periksa_lab.kd_jenis_prw')
          ->where('no_rawat', $this->revertNorawat($id))
          ->toArray();
        foreach ($rows_pemeriksaan_laboratorium as $value) {
          $value['detail_periksa_lab'] = $this->db('detail_periksa_lab')
            ->join('template_laboratorium', 'template_laboratorium.id_template=detail_periksa_lab.id_template')
            ->where('detail_periksa_lab.no_rawat', $value['no_rawat'])
            ->where('detail_periksa_lab.kd_jenis_prw', $value['kd_jenis_prw'])
            ->toArray();
          $pemeriksaan_laboratorium[] = $value;
        }
        $pemberian_obat = $this->db('detail_pemberian_obat')
          ->join('databarang', 'detail_pemberian_obat.kode_brng=databarang.kode_brng')
          ->where('no_rawat', $this->revertNorawat($id))
          ->toArray();
        $riwayat_obat = [];
        $list_riwayat = $this->db('reg_periksa')
        ->where('no_rkm_medis',$this->core->getRegPeriksaInfo('no_rkm_medis', $this->revertNorawat($id)))
        ->toArray();
        foreach($list_riwayat as $list_riw){
          $beri_obat = $this->db('detail_pemberian_obat')
            ->join('databarang', 'detail_pemberian_obat.kode_brng=databarang.kode_brng')
            ->where('no_rawat', $list_riw['no_rawat'])
            ->toArray();
            foreach($beri_obat as $row){
              $row['resep_obat_ku'] = $this->db('aturan_pakai')
              ->where('aturan_pakai.no_rawat',$row['no_rawat'])
              ->where('aturan_pakai.kode_brng',$row['kode_brng'])
              ->oneArray();
              $riwayat_obat[] = $row;
            }
        }
        $obat_operasi = $this->db('beri_obat_operasi')
          ->join('obatbhp_ok', 'beri_obat_operasi.kd_obat=obatbhp_ok.kd_obat')
          ->where('no_rawat', $this->revertNorawat($id))
          ->toArray();
        /*
        $resep_pulang = $this->db('resep_pulang')
          ->join('databarang', 'resep_pulang.kode_brng=databarang.kode_brng')
          ->where('no_rawat', $this->revertNorawat($id))
          ->toArray();
        */
        $laporan_operasi = $this->db('laporan_operasi')
          ->where('no_rawat', $this->revertNorawat($id))
          ->oneArray();

        $this->tpl->set('pasien', $pasien);
        $this->tpl->set('reg_periksa', $reg_periksa);
        $this->tpl->set('dpjp_ranap', $dpjp_ranap);
        $this->tpl->set('diagnosa_pasien', $diagnosa_pasien);
        $this->tpl->set('prosedur_pasien', $prosedur_pasien);
        $this->tpl->set('pemeriksaan_ralan', $pemeriksaan_ralan);
        $this->tpl->set('pemeriksaan_ranap', $pemeriksaan_ranap);
        $this->tpl->set('rawat_jl_dr', $rawat_jl_dr);
        $this->tpl->set('rawat_jl_pr', $rawat_jl_pr);
        $this->tpl->set('rawat_jl_drpr', $rawat_jl_drpr);
        $this->tpl->set('rawat_inap_dr', $rawat_inap_dr);
        $this->tpl->set('rawat_inap_pr', $rawat_inap_pr);
        $this->tpl->set('rawat_inap_drpr', $rawat_inap_drpr);
        $this->tpl->set('kamar_inap', $kamar_inap);
        $this->tpl->set('operasi', $operasi);
        $this->tpl->set('tindakan_radiologi', $tindakan_radiologi);
        $this->tpl->set('hasil_radiologi', $hasil_radiologi);
        $this->tpl->set('pemeriksaan_laboratorium', $pemeriksaan_laboratorium);
        $this->tpl->set('pemberian_obat', $pemberian_obat);
        $this->tpl->set('obat_operasi', $obat_operasi);
        $this->tpl->set('riwayat_obat', $riwayat_obat);

        $this->tpl->set('berkas_digital', $berkas_digital);
        $this->tpl->set('hasil_radiologi', $this->db('hasil_radiologi')->where('no_rawat', $this->revertNorawat($id))->oneArray());
        $this->tpl->set('gambar_radiologi', $this->db('gambar_radiologi')->where('no_rawat', $this->revertNorawat($id))->toArray());
        $this->tpl->set('veronisa', htmlspecialchars_array($this->settings('veronisa')));
        echo $this->tpl->draw(MODULES.'/veronisa/view/pdf.html', true);
        exit();
      } else {
        redirect(url(['vero', '']));
      }
    }

    public function getCreatePDF($id)
    {
        $berkas_digital = $this->db('berkas_digital_perawatan')
          ->join('master_berkas_digital', 'master_berkas_digital.kode=berkas_digital_perawatan.kode')
          ->where('berkas_digital_perawatan.no_rawat', $this->revertNorawat($id))
          ->asc('master_berkas_digital.nama')
          ->toArray();

        $no_rawat = $this->revertNorawat($id);

        /** Billing versi mlite */

        $billing_mlite_settings = $this->settings('settings');
        $this->tpl->set('billing_mlite_settings', $this->tpl->noParse_array(htmlspecialchars_array($billing_mlite_settings)));
        $billing_mlite_reg_periksa = $this->db('reg_periksa')->where('no_rawat', $no_rawat)->oneArray();
        $billing_mlite_pasien = $this->db('pasien')->where('no_rkm_medis', $billing_mlite_reg_periksa['no_rkm_medis'])->oneArray();

        $billing_result = $this->db('mlite_billing')->where('no_rawat', $no_rawat)->like('kd_billing', 'RJ%')->desc('id_billing')->oneArray();

        $result_detail['poliklinik'] = $this->db('poliklinik')
          ->join('reg_periksa', 'reg_periksa.kd_poli = poliklinik.kd_poli')
          ->where('reg_periksa.no_rawat', $no_rawat)
          ->oneArray();

        $result_detail['rawat_jl_dr'] = $this->db('rawat_jl_dr')
          ->select('jns_perawatan.nm_perawatan')
          ->select(['biaya_rawat' => 'rawat_jl_dr.biaya_rawat'])
          ->select(['jml' => 'COUNT(rawat_jl_dr.kd_jenis_prw)'])
          ->select(['total_biaya_rawat_dr' => 'SUM(rawat_jl_dr.biaya_rawat)'])
          ->join('jns_perawatan', 'jns_perawatan.kd_jenis_prw = rawat_jl_dr.kd_jenis_prw')
          ->where('rawat_jl_dr.no_rawat', $no_rawat)
          ->group('jns_perawatan.nm_perawatan')
          ->toArray();

        $total_rawat_jl_dr = 0;
        foreach ($result_detail['rawat_jl_dr'] as $row) {
          $total_rawat_jl_dr += $row['biaya_rawat'];
        }

        $result_detail['rawat_jl_pr'] = $this->db('rawat_jl_pr')
          ->select('jns_perawatan.nm_perawatan')
          ->select(['biaya_rawat' => 'rawat_jl_pr.biaya_rawat'])
          ->select(['jml' => 'COUNT(rawat_jl_pr.kd_jenis_prw)'])
          ->select(['total_biaya_rawat_pr' => 'SUM(rawat_jl_pr.biaya_rawat)'])
          ->join('jns_perawatan', 'jns_perawatan.kd_jenis_prw = rawat_jl_pr.kd_jenis_prw')
          ->where('rawat_jl_pr.no_rawat', $no_rawat)
          ->group('jns_perawatan.nm_perawatan')
          ->toArray();

        $total_rawat_jl_pr = 0;
        foreach ($result_detail['rawat_jl_pr'] as $row) {
          $total_rawat_jl_pr += $row['biaya_rawat'];
        }

        $result_detail['rawat_jl_drpr'] = $this->db('rawat_jl_drpr')
          ->select('jns_perawatan.nm_perawatan')
          ->select(['biaya_rawat' => 'rawat_jl_drpr.biaya_rawat'])
          ->select(['jml' => 'COUNT(rawat_jl_drpr.kd_jenis_prw)'])
          ->select(['total_biaya_rawat_drpr' => 'SUM(rawat_jl_drpr.biaya_rawat)'])
          ->join('jns_perawatan', 'jns_perawatan.kd_jenis_prw = rawat_jl_drpr.kd_jenis_prw')
          ->where('rawat_jl_drpr.no_rawat', $no_rawat)
          ->group('jns_perawatan.nm_perawatan')
          ->toArray();

        $total_rawat_jl_drpr = 0;
        foreach ($result_detail['rawat_jl_drpr'] as $row) {
          $total_rawat_jl_drpr += $row['biaya_rawat'];
        }

        $result_detail['detail_pemberian_obat'] = $this->db('detail_pemberian_obat')
          ->join('databarang', 'databarang.kode_brng=detail_pemberian_obat.kode_brng')
          ->where('no_rawat', $no_rawat)
          ->where('detail_pemberian_obat.status', 'Ralan')
          ->toArray();

        $total_detail_pemberian_obat = 0;
        foreach ($result_detail['detail_pemberian_obat'] as $row) {
          $total_detail_pemberian_obat += $row['total'];
        }

        $result_detail['periksa_lab'] = $this->db('periksa_lab')
          ->join('jns_perawatan_lab', 'jns_perawatan_lab.kd_jenis_prw=periksa_lab.kd_jenis_prw')
          ->where('no_rawat', $no_rawat)
          ->where('periksa_lab.status', 'Ralan')
          ->toArray();

        $total_periksa_lab = 0;
        foreach ($result_detail['periksa_lab'] as $row) {
          $total_periksa_lab += $row['biaya'];
        }

        $result_detail['periksa_radiologi'] = $this->db('periksa_radiologi')
          ->join('jns_perawatan_radiologi', 'jns_perawatan_radiologi.kd_jenis_prw=periksa_radiologi.kd_jenis_prw')
          ->where('no_rawat', $no_rawat)
          ->where('periksa_radiologi.status', 'Ralan')
          ->toArray();

        $total_periksa_radiologi = 0;
        foreach ($result_detail['periksa_radiologi'] as $row) {
          $total_periksa_radiologi += $row['biaya'];
        }

        $jumlah_total_operasi = 0;
        $operasis = $this->db('operasi')->join('paket_operasi', 'paket_operasi.kode_paket=operasi.kode_paket')->where('no_rawat', $no_rawat)->where('operasi.status', 'Ralan')->toArray();
        $result_detail['operasi'] = [];
        foreach ($operasis as $operasi) {
          $operasi['jumlah'] = $operasi['biayaoperator1']+$operasi['biayaoperator2']+$operasi['biayaoperator3']+$operasi['biayaasisten_operator1']+$operasi['biayaasisten_operator2']+$operasi['biayadokter_anak']+$operasi['biayaperawaat_resusitas']+$operasi['biayadokter_anestesi']+$operasi['biayaasisten_anestesi']+$operasi['biayabidan']+$operasi['biayaperawat_luar'];
          $jumlah_total_operasi += $operasi['jumlah'];
          $result_detail['operasi'][] = $operasi;
        }
        $jumlah_total_obat_operasi = 0;
        $obat_operasis = $this->db('beri_obat_operasi')->join('obatbhp_ok', 'obatbhp_ok.kd_obat=beri_obat_operasi.kd_obat')->where('no_rawat', $no_rawat)->toArray();
        $result_detail['obat_operasi'] = [];
        foreach ($obat_operasis as $obat_operasi) {
          $obat_operasi['harga'] = $obat_operasi['hargasatuan'] * $obat_operasi['jumlah'];
          $jumlah_total_obat_operasi += $obat_operasi['harga'];
          $result_detail['obat_operasi'][] = $obat_operasi;
        }

        $qr=QRCode::getMinimumQRCode($this->core->getUserInfo('fullname', null, true),QR_ERROR_CORRECT_LEVEL_L);
        //$qr=QRCode::getMinimumQRCode('Petugas: '.$this->core->getUserInfo('fullname', null, true).'; Lokasi: '.UPLOADS.'/invoices/'.$result['kd_billing'].'.pdf',QR_ERROR_CORRECT_LEVEL_L);
        $im=$qr->createImage(4,4);
        imagepng($im,BASE_DIR.'/tmp/qrcode.png');
        imagedestroy($im);

        $image = BASE_DIR."/tmp/qrcode.png";
        $qrCode = "../../tmp/qrcode.png";

        $this->tpl->set('billing_mlite_detail', $result_detail);
        $this->tpl->set('billing_mlite', $billing_result);
        $this->tpl->set('billing_mlite_qrcode', $qrCode);
        $this->tpl->set('billing_mlite_kasir', $this->core->getUserInfo('fullname', null, true));
        $this->tpl->set('billing_mlite_pasien', $billing_mlite_pasien);
        $this->tpl->set('billing_mlite_veronisa', htmlspecialchars_array($this->settings('veronisa')));

        /** End billing versi mlite **/


        $print_sep = array();
        if(!empty($this->_getSEPInfo('no_sep', $no_rawat))) {
          $print_sep['bridging_sep'] = $this->db('bridging_sep')->where('no_sep', $this->_getSEPInfo('no_sep', $no_rawat))->oneArray();
          $print_sep['bpjs_prb'] = $this->db('bpjs_prb')->where('no_sep', $this->_getSEPInfo('no_sep', $no_rawat))->oneArray();
          $batas_rujukan = $this->db('bridging_sep')->select('DATE_ADD(tglrujukan , INTERVAL 85 DAY) AS batas_rujukan')->where('no_sep', $id)->oneArray();
          $print_sep['batas_rujukan'] = $batas_rujukan['batas_rujukan'];
        }

        $print_sep['logoURL'] = url(MODULES.'/pendaftaran/img/bpjslogo.png');
        $this->tpl->set('print_sep', $print_sep);

        /*
        $resume_pasien = $this->db('resume_pasien')
          ->join('dokter', 'dokter.kd_dokter = resume_pasien.kd_dokter')
          ->where('no_rawat', $this->revertNorawat($id))
          ->oneArray();
        $this->tpl->set('resume_pasien', $resume_pasien);
        */

        $pasien = $this->db('pasien')
          ->join('kecamatan', 'kecamatan.kd_kec = pasien.kd_kec')
          ->join('kabupaten', 'kabupaten.kd_kab = pasien.kd_kab')
          ->where('no_rkm_medis', $this->core->getRegPeriksaInfo('no_rkm_medis', $this->revertNorawat($id)))
          ->oneArray();
        $reg_periksa = $this->db('reg_periksa')
          ->join('dokter', 'dokter.kd_dokter = reg_periksa.kd_dokter')
          ->join('poliklinik', 'poliklinik.kd_poli = reg_periksa.kd_poli')
          ->join('penjab', 'penjab.kd_pj = reg_periksa.kd_pj')
          ->where('stts', '<>', 'Batal')
          ->where('no_rawat', $this->revertNorawat($id))
          ->oneArray();
        /*
        $rujukan_internal = $this->db('rujukan_internal_poli')
          ->join('poliklinik', 'poliklinik.kd_poli = rujukan_internal_poli.kd_poli')
          ->join('dokter', 'dokter.kd_dokter = rujukan_internal_poli.kd_dokter')
          ->where('no_rawat', $this->revertNorawat($id))
          ->oneArray();
        */
        $rows_dpjp_ranap = $this->db('dpjp_ranap')
          ->join('dokter', 'dokter.kd_dokter = dpjp_ranap.kd_dokter')
          ->where('no_rawat', $this->revertNorawat($id))
          ->toArray();
        $dpjp_i = 1;
        $dpjp_ranap = [];
        foreach ($rows_dpjp_ranap as $row) {
          $row['nomor'] = $dpjp_i++;
          $dpjp_ranap[] = $row;
        }
        $diagnosa_pasien = $this->db('diagnosa_pasien')
          ->join('penyakit', 'penyakit.kd_penyakit = diagnosa_pasien.kd_penyakit')
          ->where('no_rawat', $this->revertNorawat($id))
          ->toArray();
        $prosedur_pasien = $this->db('prosedur_pasien')
          ->join('icd9', 'icd9.kode = prosedur_pasien.kode')
          ->where('no_rawat', $this->revertNorawat($id))
          ->toArray();
        $pemeriksaan_ralan = $this->db('pemeriksaan_ralan')
          ->where('no_rawat', $this->revertNorawat($id))
          ->asc('tgl_perawatan')
          ->asc('jam_rawat')
          ->toArray();
        $pemeriksaan_ranap = $this->db('pemeriksaan_ranap')
          ->where('no_rawat', $this->revertNorawat($id))
          ->asc('tgl_perawatan')
          ->asc('jam_rawat')
          ->toArray();
        $rawat_jl_dr = $this->db('rawat_jl_dr')
          ->join('jns_perawatan', 'rawat_jl_dr.kd_jenis_prw=jns_perawatan.kd_jenis_prw')
          ->join('dokter', 'rawat_jl_dr.kd_dokter=dokter.kd_dokter')
          ->where('no_rawat', $this->revertNorawat($id))
          ->toArray();
        $rawat_jl_pr = $this->db('rawat_jl_pr')
          ->join('jns_perawatan', 'rawat_jl_pr.kd_jenis_prw=jns_perawatan.kd_jenis_prw')
          ->join('petugas', 'rawat_jl_pr.nip=petugas.nip')
          ->where('no_rawat', $this->revertNorawat($id))
          ->toArray();
        $rawat_jl_drpr = $this->db('rawat_jl_drpr')
          ->join('jns_perawatan', 'rawat_jl_drpr.kd_jenis_prw=jns_perawatan.kd_jenis_prw')
          ->join('dokter', 'rawat_jl_drpr.kd_dokter=dokter.kd_dokter')
          ->join('petugas', 'rawat_jl_drpr.nip=petugas.nip')
          ->where('no_rawat', $this->revertNorawat($id))
          ->toArray();
        $rawat_inap_dr = $this->db('rawat_inap_dr')
          ->join('jns_perawatan_inap', 'rawat_inap_dr.kd_jenis_prw=jns_perawatan_inap.kd_jenis_prw')
          ->join('dokter', 'rawat_inap_dr.kd_dokter=dokter.kd_dokter')
          ->where('no_rawat', $this->revertNorawat($id))
          ->toArray();
        $rawat_inap_pr = $this->db('rawat_inap_pr')
          ->join('jns_perawatan_inap', 'rawat_inap_pr.kd_jenis_prw=jns_perawatan_inap.kd_jenis_prw')
          ->join('petugas', 'rawat_inap_pr.nip=petugas.nip')
          ->where('no_rawat', $this->revertNorawat($id))
          ->toArray();
        $rawat_inap_drpr = $this->db('rawat_inap_drpr')
          ->join('jns_perawatan_inap', 'rawat_inap_drpr.kd_jenis_prw=jns_perawatan_inap.kd_jenis_prw')
          ->join('dokter', 'rawat_inap_drpr.kd_dokter=dokter.kd_dokter')
          ->join('petugas', 'rawat_inap_drpr.nip=petugas.nip')
          ->where('no_rawat', $this->revertNorawat($id))
          ->toArray();
        $kamar_inap = $this->db('kamar_inap')
          ->join('kamar', 'kamar_inap.kd_kamar=kamar.kd_kamar')
          ->join('bangsal', 'kamar.kd_bangsal=bangsal.kd_bangsal')
          ->where('no_rawat', $this->revertNorawat($id))
          ->toArray();
        $operasi = $this->db('operasi')
          ->join('paket_operasi', 'operasi.kode_paket=paket_operasi.kode_paket')
          ->where('no_rawat', $this->revertNorawat($id))
          ->toArray();
        $tindakan_radiologi = $this->db('periksa_radiologi')
          ->join('jns_perawatan_radiologi', 'periksa_radiologi.kd_jenis_prw=jns_perawatan_radiologi.kd_jenis_prw')
          ->join('dokter', 'periksa_radiologi.kd_dokter=dokter.kd_dokter')
          ->join('petugas', 'periksa_radiologi.nip=petugas.nip')
          ->where('no_rawat', $this->revertNorawat($id))
          ->toArray();
        $hasil_radiologi = $this->db('hasil_radiologi')
          ->where('no_rawat', $this->revertNorawat($id))
          ->toArray();
        $pemeriksaan_laboratorium = [];
        $rows_pemeriksaan_laboratorium = $this->db('periksa_lab')
          ->join('jns_perawatan_lab', 'jns_perawatan_lab.kd_jenis_prw=periksa_lab.kd_jenis_prw')
          ->where('no_rawat', $this->revertNorawat($id))
          ->toArray();
        foreach ($rows_pemeriksaan_laboratorium as $value) {
          $value['detail_periksa_lab'] = $this->db('detail_periksa_lab')
            ->join('template_laboratorium', 'template_laboratorium.id_template=detail_periksa_lab.id_template')
            ->where('detail_periksa_lab.no_rawat', $value['no_rawat'])
            ->where('detail_periksa_lab.kd_jenis_prw', $value['kd_jenis_prw'])
            ->toArray();
          $pemeriksaan_laboratorium[] = $value;
        }
        $pemberian_obat = $this->db('detail_pemberian_obat')
          ->join('databarang', 'detail_pemberian_obat.kode_brng=databarang.kode_brng')
          ->where('no_rawat', $this->revertNorawat($id))
          ->toArray();
        $riwayat_obat = [];
        $list_riwayat = $this->db('reg_periksa')
        ->where('no_rkm_medis',$this->core->getRegPeriksaInfo('no_rkm_medis', $this->revertNorawat($id)))
        ->toArray();
        foreach($list_riwayat as $list_riw){
          $beri_obat = $this->db('detail_pemberian_obat')
            ->join('databarang', 'detail_pemberian_obat.kode_brng=databarang.kode_brng')
            ->where('no_rawat', $list_riw['no_rawat'])
            ->toArray();
            foreach($beri_obat as $row){
              $row['resep_obat_ku'] = $this->db('aturan_pakai')
              ->where('aturan_pakai.no_rawat',$row['no_rawat'])
              ->where('aturan_pakai.kode_brng',$row['kode_brng'])
              ->oneArray();
              $riwayat_obat[] = $row;
            }
        }
        $obat_operasi = $this->db('beri_obat_operasi')
          ->join('obatbhp_ok', 'beri_obat_operasi.kd_obat=obatbhp_ok.kd_obat')
          ->where('no_rawat', $this->revertNorawat($id))
          ->toArray();
        /*
        $resep_pulang = $this->db('resep_pulang')
          ->join('databarang', 'resep_pulang.kode_brng=databarang.kode_brng')
          ->where('no_rawat', $this->revertNorawat($id))
          ->toArray();
        */
        $laporan_operasi = $this->db('laporan_operasi')
          ->where('no_rawat', $this->revertNorawat($id))
          ->oneArray();

        $this->tpl->set('pasien', $pasien);
        $this->tpl->set('reg_periksa', $reg_periksa);
        $this->tpl->set('dpjp_ranap', $dpjp_ranap);
        $this->tpl->set('diagnosa_pasien', $diagnosa_pasien);
        $this->tpl->set('prosedur_pasien', $prosedur_pasien);
        $this->tpl->set('pemeriksaan_ralan', $pemeriksaan_ralan);
        $this->tpl->set('pemeriksaan_ranap', $pemeriksaan_ranap);
        $this->tpl->set('rawat_jl_dr', $rawat_jl_dr);
        $this->tpl->set('rawat_jl_pr', $rawat_jl_pr);
        $this->tpl->set('rawat_jl_drpr', $rawat_jl_drpr);
        $this->tpl->set('rawat_inap_dr', $rawat_inap_dr);
        $this->tpl->set('rawat_inap_pr', $rawat_inap_pr);
        $this->tpl->set('rawat_inap_drpr', $rawat_inap_drpr);
        $this->tpl->set('kamar_inap', $kamar_inap);
        $this->tpl->set('operasi', $operasi);
        $this->tpl->set('tindakan_radiologi', $tindakan_radiologi);
        $this->tpl->set('hasil_radiologi', $hasil_radiologi);
        $this->tpl->set('pemeriksaan_laboratorium', $pemeriksaan_laboratorium);
        $this->tpl->set('pemberian_obat', $pemberian_obat);
        $this->tpl->set('obat_operasi', $obat_operasi);
        $this->tpl->set('riwayat_obat', $riwayat_obat);

        $this->tpl->set('berkas_digital', $berkas_digital);
        $this->tpl->set('hasil_radiologi', $this->db('hasil_radiologi')->where('no_rawat', $this->revertNorawat($id))->oneArray());
        $this->tpl->set('gambar_radiologi', $this->db('gambar_radiologi')->where('no_rawat', $this->revertNorawat($id))->toArray());
        $this->tpl->set('veronisa', htmlspecialchars_array($this->settings('veronisa')));
        echo $this->tpl->draw(MODULES.'/veronisa/view/pdf.html', true);
        exit();
    }

    public function getDownloadPDF($id)
    {
      $apikey = 'c811af07-d551-40ec-8e87-9abbf03abe16';
      $value = url().'/vero/createpdf/'.$id; // can aso be a url, starting with http..

      $bridging_sep = $this->db('bridging_sep')->where('no_rawat', $this->revertNorawat($id))->oneArray();

      // Convert the HTML string to a PDF using those parameters.  Note if you have a very long HTML string use POST rather than get.  See example #5
      $result = file_get_contents("http://url2pdf.basoro.id/?apikey=" . urlencode($apikey) . "&url=" . urlencode($value));

      // Save to root folder in website
      //file_put_contents('mypdf-1.pdf', $result);

      // Output headers so that the file is downloaded rather than displayed
      // Remember that header() must be called before any actual output is sent
      header('Content-Description: File Transfer');
      header('Content-Type: application/pdf');
      header('Expires: 0');
      header('Cache-Control: must-revalidate');
      header('Pragma: public');
      header('Content-Length: ' . strlen($result));

      // Make the file a downloadable attachment - comment this out to show it directly inside the
      // web browser.  Note that you can give the file any name you want, e.g. alias-name.pdf below:
      header('Content-Disposition: attachment; filename=' . 'e-veronisa-'.$bridging_sep['tglsep'].'-'.$bridging_sep['no_sep'].'.pdf' );

      // Stream PDF to user
      echo $result;
      exit();
    }

    private function _login($username, $password)
    {
        // Check attempt
        $attempt = $this->db('mlite_login_attempts')->where('ip', $_SERVER['REMOTE_ADDR'])->oneArray();

        // Create attempt if does not exist
        if (!$attempt) {
            $this->db('mlite_login_attempts')->save(['ip' => $_SERVER['REMOTE_ADDR'], 'attempts' => 0]);
            $attempt = ['ip' => $_SERVER['REMOTE_ADDR'], 'attempts' => 0, 'expires' => 0];
        } else {
            $attempt['attempts'] = intval($attempt['attempts']);
            $attempt['expires'] = intval($attempt['expires']);
        }

        $row_username = $this->settings->get('veronisa.username');
        $row_password = $this->settings->get('veronisa.password');

        if ($row_username == $username && $row_password == $password) {
            // Reset fail attempts for this IP
            $this->db('mlite_login_attempts')->where('ip', $_SERVER['REMOTE_ADDR'])->save(['attempts' => 0]);

            $_SESSION['veronisa_user']       = $row_username;
            $_SESSION['veronisa_token']      = bin2hex(openssl_random_pseudo_bytes(6));
            $_SESSION['veronisa_userAgent']  = $_SERVER['HTTP_USER_AGENT'];
            $_SESSION['veronisa_IPaddress']  = $_SERVER['REMOTE_ADDR'];

            return true;
        } else {
            // Increase attempt
            $this->db('mlite_login_attempts')->where('ip', $_SERVER['REMOTE_ADDR'])->save(['attempts' => $attempt['attempts']+1]);
            $attempt['attempts'] += 1;

            // ... and block if reached maximum attempts
            if ($attempt['attempts'] % 3 == 0) {
                $this->db('mlite_login_attempts')->where('ip', $_SERVER['REMOTE_ADDR'])->save(['expires' => strtotime("+10 minutes")]);
                $attempt['expires'] = strtotime("+10 minutes");

                $this->core->setNotify('failure', sprintf('Batas maksimum login tercapai. Tunggu %s menit untuk coba lagi.', ceil(($attempt['expires']-time())/60)));
            } else {
                $this->core->setNotify('failure', 'Username atau password salah!');
            }

            return false;
        }
    }

    private function _loginCheck()
    {
        if (isset($_SESSION['veronisa_user']) && isset($_SESSION['veronisa_token']) && isset($_SESSION['veronisa_userAgent']) && isset($_SESSION['veronisa_IPaddress'])) {
            if ($_SESSION['veronisa_IPaddress'] != $_SERVER['REMOTE_ADDR']) {
                return false;
            }
            if ($_SESSION['veronisa_userAgent'] != $_SERVER['HTTP_USER_AGENT']) {
                return false;
            }

            if (empty(parseURL(1))) {
                redirect(url('vero'));
            } elseif (!isset($_GET['t']) || ($_SESSION['veronisa_token'] != @$_GET['t'])) {
                return false;
            }

            return true;
        }

        return false;
    }

    private function logout()
    {
        unset($_SESSION['veronisa_user']);
        unset($_SESSION['veronisa_token']);
        unset($_SESSION['veronisa_userAgent']);
        unset($_SESSION['veronisa_IPaddress']);

        redirect(url('vero'));
    }

    private function _getSEPInfo($field, $no_rawat)
    {
        $row = $this->db('bridging_sep')->where('no_rawat', $no_rawat)->oneArray();
        return $row[$field];
    }

    public function convertNorawat($text)
    {
        setlocale(LC_ALL, 'en_EN');
        $text = str_replace('/', '', trim($text));
        return $text;
    }

    public function revertNorawat($text)
    {
        setlocale(LC_ALL, 'en_EN');
        $tahun = substr($text, 0, 4);
        $bulan = substr($text, 4, 2);
        $tanggal = substr($text, 6, 2);
        $nomor = substr($text, 8, 6);
        $result = $tahun.'/'.$bulan.'/'.$tanggal.'/'.$nomor;
        return $result;
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/vero/js/scripts.js');
        exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/vero/css/styles.css');
        exit();
    }

    private function _addHeaderFiles()
    {
        // CSS
        $this->core->addCSS(url('assets/css/jquery-ui.css'));
        $this->core->addCSS(url('assets/css/jquery.timepicker.css'));

        // JS
        $this->core->addJS(url('assets/jscripts/jquery-ui.js'), 'footer');
        $this->core->addJS(url('assets/jscripts/jquery.timepicker.js'), 'footer');

        // MODULE SCRIPTS
        $this->core->addCSS(url(['veronisa', 'css']));
        $this->core->addJS(url(['veronisa', 'javascript']), 'footer');
    }

}
