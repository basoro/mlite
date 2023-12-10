<?php
namespace Plugins\Apotek_Ranap;

use Systems\AdminModule;
use Systems\Lib\Fpdf\PDF_MC_Table;

class Admin extends AdminModule
{

    public function navigation()
    {
        return [
            'Kelola'   => 'manage',
        ];
    }

    public function anyManage()
    {
        $tgl_kunjungan = date('Y-m-d');
        $tgl_kunjungan_akhir = date('Y-m-d');
        $status_periksa = '';

        if(isset($_POST['periode_rawat_inap'])) {
          $tgl_kunjungan = $_POST['periode_rawat_inap'];
        }
        if(isset($_POST['periode_rawat_inap_akhir'])) {
          $tgl_kunjungan_akhir = $_POST['periode_rawat_inap_akhir'];
        }
        if(isset($_POST['status_periksa'])) {
          $status_periksa = $_POST['status_periksa'];
        }
        $cek_vclaim = $this->db('mlite_modules')->where('dir', 'vclaim')->oneArray();
        $this->_Display($tgl_kunjungan, $tgl_kunjungan_akhir, $status_periksa);
        return $this->draw('manage.html', ['rawat_inap' => $this->assign, 'cek_vclaim' => $cek_vclaim]);
    }

    public function anyDisplay()
    {
        $tgl_kunjungan = date('Y-m-d');
        $tgl_kunjungan_akhir = date('Y-m-d');
        $status_periksa = '';

        if(isset($_POST['periode_rawat_inap'])) {
          $tgl_kunjungan = $_POST['periode_rawat_inap'];
        }
        if(isset($_POST['periode_rawat_inap_akhir'])) {
          $tgl_kunjungan_akhir = $_POST['periode_rawat_inap_akhir'];
        }
        if(isset($_POST['status_periksa'])) {
          $status_periksa = $_POST['status_periksa'];
        }
        $cek_vclaim = $this->db('mlite_modules')->where('dir', 'vclaim')->oneArray();
        $this->_Display($tgl_kunjungan, $tgl_kunjungan_akhir, $status_periksa);
        echo $this->draw('display.html', ['rawat_inap' => $this->assign, 'cek_vclaim' => $cek_vclaim]);
        exit();
    }

    public function _Display($tgl_masuk='', $tgl_masuk_akhir='', $status_pulang='')
    {
        $this->_addHeaderFiles();

        $this->assign['kamar'] = $this->db('kamar')->join('bangsal', 'bangsal.kd_bangsal=kamar.kd_bangsal')->where('statusdata', '1')->toArray();
        $this->assign['dokter']         = $this->db('dokter')->where('status', '1')->toArray();
        $this->assign['penjab']       = $this->db('penjab')->where('status', '1')->toArray();
        $this->assign['no_rawat'] = '';

        $bangsal = str_replace(",","','", $this->core->getUserInfo('cap', null, true));

        $sql = "SELECT
            kamar_inap.*,
            reg_periksa.*,
            pasien.*,
            kamar.*,
            bangsal.*,
            penjab.*
          FROM
            kamar_inap,
            reg_periksa,
            pasien,
            kamar,
            bangsal,
            penjab
          WHERE
            kamar_inap.no_rawat=reg_periksa.no_rawat
          AND
            reg_periksa.no_rkm_medis=pasien.no_rkm_medis
          AND
            kamar_inap.kd_kamar=kamar.kd_kamar
          AND
            bangsal.kd_bangsal=kamar.kd_bangsal
          AND
            reg_periksa.kd_pj=penjab.kd_pj";

        if ($this->core->getUserInfo('role') != 'admin') {
          $sql .= " AND bangsal.kd_bangsal IN ('$bangsal')";
        }
        if($status_pulang == '') {
          $sql .= " AND kamar_inap.stts_pulang = '-'";
        }
        if($status_pulang == 'all' && $tgl_masuk !== '' && $tgl_masuk_akhir !== '') {
          $sql .= " AND kamar_inap.stts_pulang = '-' AND kamar_inap.tgl_masuk BETWEEN '$tgl_masuk' AND '$tgl_masuk_akhir'";
        }
        if($status_pulang == 'masuk' && $tgl_masuk !== '' && $tgl_masuk_akhir !== '') {
          $sql .= " AND kamar_inap.tgl_masuk BETWEEN '$tgl_masuk' AND '$tgl_masuk_akhir'";
        }
        if($status_pulang == 'pulang' && $tgl_masuk !== '' && $tgl_masuk_akhir !== '') {
          $sql .= " AND kamar_inap.tgl_keluar BETWEEN '$tgl_masuk' AND '$tgl_masuk_akhir'";
        }

        $stmt = $this->db()->pdo()->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        $this->assign['list'] = [];
        foreach ($rows as $row) {
          $dpjp_ranap = $this->db('dpjp_ranap')
            ->join('dokter', 'dokter.kd_dokter=dpjp_ranap.kd_dokter')
            ->where('no_rawat', $row['no_rawat'])
            ->toArray();
          $row['dokter'] = $dpjp_ranap;
          $this->assign['list'][] = $row;
        }

    }

    public function postSaveDetail()
    {

      $get_gudangbarang = $this->db('gudangbarang')->where('kode_brng', $_POST['kd_jenis_prw'])->where('kd_bangsal', $this->settings->get('farmasi.deporanap'))->oneArray();

      $this->db('gudangbarang')
        ->where('kode_brng', $_POST['kd_jenis_prw'])
        ->where('kd_bangsal', $this->settings->get('farmasi.deporanap'))
        ->update([
          'stok' => $get_gudangbarang['stok'] - $_POST['jml']
        ]);

      $this->db('riwayat_barang_medis')
        ->save([
          'kode_brng' => $_POST['kd_jenis_prw'],
          'stok_awal' => $get_gudangbarang['stok'],
          'masuk' => '0',
          'keluar' => $_POST['jml'],
          'stok_akhir' => $get_gudangbarang['stok'] - $_POST['jml'],
          'posisi' => 'Pemberian Obat',
          'tanggal' => $_POST['tgl_perawatan'],
          'jam' => $_POST['jam_rawat'],
          'petugas' => $this->core->getUserInfo('fullname', null, true),
          'kd_bangsal' => $this->settings->get('farmasi.deporanap'),
          'status' => 'Simpan',
          'no_batch' => $get_gudangbarang['no_batch'],
          'no_faktur' => $get_gudangbarang['no_faktur'],
          'keterangan' => $_POST['no_rawat'] . ' ' . $this->core->getRegPeriksaInfo('no_rkm_medis', $_POST['no_rawat']) . ' ' . $this->core->getPasienInfo('nm_pasien', $this->core->getRegPeriksaInfo('no_rkm_medis', $_POST['no_rawat']))
        ]);

      $this->db('detail_pemberian_obat')
        ->save([
          'tgl_perawatan' => $_POST['tgl_perawatan'],
          'jam' => $_POST['jam_rawat'],
          'no_rawat' => $_POST['no_rawat'],
          'kode_brng' => $_POST['kd_jenis_prw'],
          'h_beli' => $_POST['biaya'],
          'biaya_obat' => $_POST['biaya'],
          'jml' => $_POST['jml'],
          'embalase' => '0',
          'tuslah' => '0',
          'total' => $_POST['biaya'] * $_POST['jml'],
          'status' => 'Ranap',
          'kd_bangsal' => $this->settings->get('farmasi.deporanap'),
          'no_batch' => $get_gudangbarang['no_batch'],
          'no_faktur' => $get_gudangbarang['no_faktur']
        ]);

      $this->db('aturan_pakai')
        ->save([
          'tgl_perawatan' => $_POST['tgl_perawatan'],
          'jam' => $_POST['jam_rawat'],
          'no_rawat' => $_POST['no_rawat'],
          'kode_brng' => $_POST['kd_jenis_prw'],
          'aturan' => $_POST['aturan_pakai']
        ]);

      exit();
    }

    public function postValidasiResep()
    {
      $get_resep_dokter_nonracikan = $this->db('resep_dokter')->select('kode_brng')->select('jml')->where('no_resep', $_POST['no_resep'])->toArray();
      $get_resep_dokter_racikan = $this->db('resep_dokter_racikan_detail')->select('kode_brng')->select('jml')->where('no_resep', $_POST['no_resep'])->toArray();
      $get_resep_dokter = array_merge($get_resep_dokter_nonracikan, $get_resep_dokter_racikan);

      foreach ($get_resep_dokter as $item) {

        $get_gudangbarang = $this->db('gudangbarang')->where('kode_brng', $item['kode_brng'])->where('kd_bangsal', $this->settings->get('farmasi.deporanap'))->oneArray();
        $get_databarang = $this->db('databarang')->where('kode_brng', $item['kode_brng'])->oneArray();

        $this->db('gudangbarang')
          ->where('kode_brng', $item['kode_brng'])
          ->where('kd_bangsal', $this->settings->get('farmasi.deporanap'))
          ->update([
            'stok' => $get_gudangbarang['stok'] - $item['jml']
          ]);

        $this->db('riwayat_barang_medis')
          ->save([
            'kode_brng' => $item['kode_brng'],
            'stok_awal' => $get_gudangbarang['stok'],
            'masuk' => '0',
            'keluar' => $item['jml'],
            'stok_akhir' => $get_gudangbarang['stok'] - $item['jml'],
            'posisi' => 'Pemberian Obat',
            'tanggal' => $_POST['tgl_peresepan'],
            'jam' => $_POST['jam_peresepan'],
            'petugas' => $this->core->getUserInfo('fullname', null, true),
            'kd_bangsal' => $this->settings->get('farmasi.deporanap'),
            'status' => 'Simpan',
            'no_batch' => $get_gudangbarang['no_batch'],
            'no_faktur' => $get_gudangbarang['no_faktur'],
            'keterangan' => $_POST['no_rawat'] . ' ' . $this->core->getRegPeriksaInfo('no_rkm_medis', $_POST['no_rawat']) . ' ' . $this->core->getPasienInfo('nm_pasien', $this->core->getRegPeriksaInfo('no_rkm_medis', $_POST['no_rawat']))
          ]);

        $this->db('detail_pemberian_obat')
          ->save([
            'tgl_perawatan' => $_POST['tgl_peresepan'],
            'jam' => $_POST['jam_peresepan'],
            'no_rawat' => $_POST['no_rawat'],
            'kode_brng' => $item['kode_brng'],
            'h_beli' => $get_databarang['h_beli'],
            'biaya_obat' => $get_databarang['h_beli'],
            'jml' => $item['jml'],
            'embalase' => '0',
            'tuslah' => '0',
            'total' => $get_databarang['h_beli'] * $item['jml'],
            'status' => 'Ranap',
            'kd_bangsal' => $this->settings->get('farmasi.deporanap'),
            'no_batch' => $get_gudangbarang['no_batch'],
            'no_faktur' => $get_gudangbarang['no_faktur']
          ]);

        $this->db('aturan_pakai')
          ->save([
            'tgl_perawatan' => $_POST['tgl_peresepan'],
            'jam' => $_POST['jam_peresepan'],
            'no_rawat' => $_POST['no_rawat'],
            'kode_brng' => $item['kode_brng'],
            'aturan' => $item['aturan_pakai']
          ]);
      }

      $resep_dokter_racikan = $this->db('resep_dokter_racikan')->where('no_resep', $_POST['no_resep'])->oneArray();

      if(!empty($resep_dokter_racikan)) {
        $this->db('obat_racikan')->save(
          [
              'tgl_perawatan' => date('Y-m-d'),
              'jam' => date('H:i:s'),
              'no_rawat' => $_POST['no_rawat'],
              'no_racik' => $resep_dokter_racikan['no_racik'],
              'nama_racik' => $resep_dokter_racikan['nama_racik'],
              'kd_racik' => $resep_dokter_racikan['kd_racik'],
              'jml_dr' => $resep_dokter_racikan['jml_dr'],
              'aturan_pakai' => $resep_dokter_racikan['aturan_pakai'],
              'keterangan' => $resep_dokter_racikan['keterangan']
          ]
        );
      }

      $this->db('resep_obat')->where('no_resep', $_POST['no_resep'])->save(['tgl_perawatan' => date('Y-m-d'), 'jam' => date('H:i:s')]);

      exit();
    }

    public function postHapusResep()
    {
      if(isset($_POST['kd_jenis_prw'])) {
        $this->db('resep_dokter')
        ->where('no_resep', $_POST['no_resep'])
        ->where('kode_brng', $_POST['kd_jenis_prw'])
        ->delete();
      } else {
        $this->db('resep_obat')
        ->where('no_resep', $_POST['no_resep'])
        ->where('no_rawat', $_POST['no_rawat'])
        ->where('tgl_peresepan', $_POST['tgl_peresepan'])
        ->where('jam_peresepan', $_POST['jam_peresepan'])
        ->delete();
      }

      exit();
    }

    public function anyRincian()
    {
      $rows = $this->db('resep_obat')
        ->join('dokter', 'dokter.kd_dokter=resep_obat.kd_dokter')
        ->where('no_rawat', $_POST['no_rawat'])
        ->where('resep_obat.status', 'ranap')
        ->toArray();
      $resep = [];
      $jumlah_total_resep = 0;
      foreach ($rows as $row) {
        $row['resep_dokter'] = $this->db('resep_dokter')
          ->join('resep_obat', 'resep_obat.no_resep=resep_dokter.no_resep')
          ->join('databarang', 'databarang.kode_brng=resep_dokter.kode_brng')
          ->where('resep_dokter.no_resep', $row['no_resep'])
          ->toArray();
        foreach ($row['resep_dokter'] as $value) {
          $value['h_beli'] = $value['jml'] * $value['h_beli'];
          $jumlah_total_resep += floatval($value['h_beli']);
        }

        $row['validasi'] = $this->db('resep_obat')
        ->where('no_rawat', $_POST['no_rawat'])
        ->where('tgl_perawatan','!=', $row['tgl_peresepan'])
        ->where('jam', '!=', $row['jam_peresepan'])
        ->where('status', 'ranap')
        ->oneArray();

        $resep[] = $row;
      }

      // $rows_pemberian_obat = $this->db('detail_pemberian_obat')
      // ->join('databarang', 'databarang.kode_brng=detail_pemberian_obat.kode_brng')
      // ->where('detail_pemberian_obat.no_rawat', $_POST['no_rawat'])
      // ->where('detail_pemberian_obat.status', 'Ranap')
      // ->toArray();

      $query = $this->db()->pdo()->prepare("SELECT * FROM detail_pemberian_obat WHERE no_rawat = '{$_POST['no_rawat']}' AND status = 'Ranap' AND jam NOT IN (SELECT resep_obat.jam_peresepan FROM resep_obat WHERE resep_obat.no_rawat = '{$_POST['no_rawat']}' AND resep_obat.tgl_peresepan = tgl_peresepan)");
      $query->execute();
      $rows_pemberian_obat = $query->fetchAll();

      $detail_pemberian_obat = [];
      $jumlah_total_obat = 0;
      foreach ($rows_pemberian_obat as $row) {
        $aturan_pakai = $this->db('aturan_pakai')
        ->where('no_rawat', $row['no_rawat'])
        ->where('kode_brng', $row['kode_brng'])
        ->where('tgl_perawatan', $row['tgl_perawatan'])
        ->where('jam', $row['jam'])
        ->oneArray();
        $databarang = $this->db('databarang')->where('kode_brng', $row['kode_brng'])->oneArray();
        $row['aturan_pakai'] = $aturan_pakai['aturan'];
        $row['nama_brng'] = $databarang['nama_brng'];
        $jumlah_total_obat += floatval($row['total']);
        $detail_pemberian_obat[] = $row;
      }

      echo $this->draw('rincian.html', ['jumlah_total_resep' => $jumlah_total_resep, 'jumlah_total_obat' => $jumlah_total_obat, 'resep' =>$resep, 'detail_pemberian_obat' => $detail_pemberian_obat, 'no_rawat' => $_POST['no_rawat']]);
      exit();
    }

    public function anyObat()
    {
      $obat = $this->db('databarang')
        ->join('gudangbarang', 'gudangbarang.kode_brng=databarang.kode_brng')
        ->where('status', '1')
        ->where('gudangbarang.kd_bangsal', $this->settings->get('farmasi.deporanap'))
        ->like('databarang.nama_brng', '%'.$_POST['obat'].'%')
        ->limit(10)
        ->toArray();
      echo $this->draw('obat.html', ['obat' => $obat]);
      exit();
    }

    public function postAturanPakai()
    {

      if(isset($_POST["query"])){
        $output = '';
        $key = "%".$_POST["query"]."%";
        $rows = $this->db('master_aturan_pakai')->like('aturan', $key)->limit(10)->toArray();
        $output = '';
        if(count($rows)){
          foreach ($rows as $row) {
            $output .= '<li class="list-group-item link-class">'.$row["aturan"].'</li>';
          }
        }
        echo $output;
      }

      exit();

    }

    public function postProviderList()
    {

      if(isset($_POST["query"])){
        $output = '';
        $key = "%".$_POST["query"]."%";
        $rows = $this->db('dokter')->like('nm_dokter', $key)->where('status', '1')->limit(10)->toArray();
        $output = '';
        if(count($rows)){
          foreach ($rows as $row) {
            $output .= '<li class="list-group-item link-class">'.$row["kd_dokter"].': '.$row["nm_dokter"].'</li>';
          }
        }
        echo $output;
      }

      exit();

    }

    public function postProviderList2()
    {

      if(isset($_POST["query"])){
        $output = '';
        $key = "%".$_POST["query"]."%";
        $rows = $this->db('petugas')->like('nama', $key)->limit(10)->toArray();
        $output = '';
        if(count($rows)){
          foreach ($rows as $row) {
            $output .= '<li class="list-group-item link-class">'.$row["nip"].': '.$row["nama"].'</li>';
          }
        }
        echo $output;
      }

      exit();

    }

    public function getCetakLabel($no_rawat, $tipe, $tgl_peresepan){
      if($tipe == 'nonracikan') {
        $rows_pemberian_obat = $this->db('detail_pemberian_obat')
        ->join('databarang', 'databarang.kode_brng=detail_pemberian_obat.kode_brng')
        ->join('kamar_inap', 'kamar_inap.no_rawat=detail_pemberian_obat.no_rawat')
        ->join('kamar', 'kamar.kd_kamar=kamar_inap.kd_kamar')
        ->join('bangsal', 'bangsal.kd_bangsal=kamar.kd_bangsal')
        ->where('detail_pemberian_obat.no_rawat', revertNoRawat($no_rawat))
        ->where('detail_pemberian_obat.status', 'Ranap')
        ->where('detail_pemberian_obat.tgl_perawatan', $tgl_peresepan)
        ->toArray();
        $detail_pemberian_obat = [];
        $jumlah_total_obat = 0;
        foreach ($rows_pemberian_obat as $row) {
          $aturan_pakai = $this->db('aturan_pakai')
          ->where('no_rawat', $row['no_rawat'])
          ->where('kode_brng', $row['kode_brng'])
          ->where('tgl_perawatan', $row['tgl_perawatan'])
          ->where('jam', $row['jam'])
          ->oneArray();
          $row['aturan_pakai'] = $aturan_pakai['aturan'];
          $row['keterangan'] = '';
          $detail_pemberian_obat[] = $row;
        }
      }
      if($tipe == 'racikan') {
        $rows_pemberian_obat = $this->db('obat_racikan')
          ->join('reg_periksa', 'reg_periksa.no_rawat=obat_racikan.no_rawat')
          ->join('kamar_inap', 'kamar_inap.no_rawat=detail_pemberian_obat.no_rawat')
          ->join('kamar', 'kamar.kd_kamar=kamar_inap.kd_kamar')
          ->join('bangsal', 'bangsal.kd_bangsal=kamar.kd_bangsal')  
          ->where('obat_racikan.no_rawat', revertNoRawat($no_rawat))
          ->where('obat_racikan.tgl_perawatan', $tgl_peresepan)
          ->toArray();
        $detail_pemberian_obat = [];
        $jumlah_total_obat = 0;
        foreach ($rows_pemberian_obat as $row) {
          $row['nama_brng'] = $row['nama_racik'];
          $row['jml'] = $row['jml_dr'];
          $detail_pemberian_obat[] = $row;
        }

      }

      $logo = $this->settings->get('settings.logo');
      $pdf = new PDF_MC_Table('L','mm', array(100,50));

      foreach($detail_pemberian_obat as $dpo){
        $pdf->AddPage();
        $pdf->SetAutoPageBreak(true, 10);
        $pdf->SetTopMargin(10);
        $pdf->SetLeftMargin(10);
        $pdf->SetRightMargin(10);

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Text(10,7,'Instalasi Farmasi',0,1, 'C');
        $pdf->Text(10, 10, $this->settings->get('settings.nama_instansi'));
        $pdf->SetFont('Arial', '', 9);
        $pdf->Text(10,14,'Email: '.$this->settings->get('settings.email').' - Telp: '.$this->settings->get('settings.nomor_telepon'),0,1);
        $pdf->Line(10, 16, 90, 16);
        $pdf->Line(10, 17, 90, 17);
        $pdf->SetFont('Arial', '', 8);
        $pdf->Text(64, 20, ''.dateIndonesia(date('Y-m-d')),0,1, 'L');
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Text(10, 23, ''.$this->core->getPasienInfo('nm_pasien', $this->core->getRegPeriksaInfo('no_rkm_medis', revertNoRawat($no_rawat))),0,1, 'C');
        $pdf->SetFont('Arial', '', 8);
        $pdf->Text(10, 26, 'No. RM: '.$this->core->getRegPeriksaInfo('no_rkm_medis', revertNoRawat($no_rawat)),0,1, 'L');
        $pdf->Text(40, 26, 'Kamar: '.$dpo['nm_bangsal'].' ('.$dpo['kd_kamar'].')',0,1);
        $pdf->Text(15, 31, ''.$dpo['nama_brng'],0,1, 'L');
        $pdf->Text(80, 31, '('.$dpo['jml'].')',0,1);
        $pdf->Text(20, 34, ''.$dpo['aturan_pakai'],0,1, 'L');
        $pdf->Text(20, 38, ''.$dpo['keterangan'],0,1, 'L');
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Text(33, 47, 'SEMOGA LEKAS SEMBUH', 0, 1, 'C');
      }

      $pdf->Output('etiket-obat-'.date('Y-m-d').'.pdf','I');
      exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/apotek_ranap/js/admin/apotek_ranap.js');
        exit();
    }

    private function _addHeaderFiles()
    {
        $this->core->addCSS(url('assets/css/dataTables.bootstrap.min.css'));
        $this->core->addJS(url('assets/jscripts/jquery.dataTables.min.js'));
        $this->core->addJS(url('assets/jscripts/dataTables.bootstrap.min.js'));
        $this->core->addCSS(url('assets/css/bootstrap-datetimepicker.css'));
        $this->core->addJS(url('assets/jscripts/moment-with-locales.js'));
        $this->core->addJS(url('assets/jscripts/bootstrap-datetimepicker.js'));
        $this->core->addJS(url([ADMIN, 'apotek_ranap', 'javascript']), 'footer');
    }

    protected function data_icd($table)
    {
        return new DB_ICD($table);
    }

}
