<?php
namespace Plugins\Keuangan;

use Systems\AdminModule;
use Plugins\Keuangan\Src\Akunrekening;

class Admin extends AdminModule
{

    public function init()
    {
      $this->akunrekening = new Akunrekening();
    }

    public function navigation()
    {
        return [
            'Kelola'   => 'manage',
            'Akun Rekening' => 'akunrekening',
            'Rekening Tahun' => 'rekeningtahun',
            'Pengaturan Rekening' => 'pengaturanrekening',
            'Posting Jurnal' => 'postingjurnal',
            'Jurnal Harian' => 'jurnalharian',
            'Buku Besar' => 'bukubesar',
            'Cash Flow' => 'cashflow',
            'Neraca Keuangan' => 'neraca',
            'Pengaturan' => 'settings'
        ];
    }

    public function getManage()
    {
      $sub_modules = [
        ['name' => 'Akun Rekening', 'url' => url([ADMIN, 'keuangan', 'akunrekening']), 'icon' => 'money', 'desc' => 'Akun Rekening'],
        ['name' => 'Rekening Tahun', 'url' => url([ADMIN, 'keuangan', 'rekeningtahun']), 'icon' => 'money', 'desc' => 'Rekening Tahun'],
        ['name' => 'Pengaturan Rekening', 'url' => url([ADMIN, 'keuangan', 'pengaturanrekening']), 'icon' => 'money', 'desc' => 'Pengaturan Rekening'],
        ['name' => 'Posting Jurnal', 'url' => url([ADMIN, 'keuangan', 'postingjurnal']), 'icon' => 'money', 'desc' => 'Posting Jurnal'],
        ['name' => 'Jurnal Harian', 'url' => url([ADMIN, 'keuangan', 'jurnalharian']), 'icon' => 'money', 'desc' => 'Jurnal Harian'],
        ['name' => 'Buku Besar', 'url' => url([ADMIN, 'keuangan', 'bukubesar']), 'icon' => 'money', 'desc' => 'Buku Besar'],
        ['name' => 'Cash Flow', 'url' => url([ADMIN, 'keuangan', 'cashflow']), 'icon' => 'money', 'desc' => 'Cash Flow'],
        ['name' => 'Neraca Keuangan', 'url' => url([ADMIN, 'keuangan', 'neraca']), 'icon' => 'money', 'desc' => 'Neraca Keuangan'],
        ['name' => 'Pengaturan Keuangan', 'url' => url([ADMIN, 'keuangan', 'settings']), 'icon' => 'money', 'desc' => 'Pengaduan Modul Keuangan'],
      ];
      return $this->draw('manage.html', ['sub_modules' => $sub_modules]);
    }

    /* Start Bahasa Section */
    public function getAkunRekening()
    {
      $this->core->addJS(url([ADMIN, 'keuangan', 'akunrekeningjs']), 'footer');
      $return = $this->akunrekening->getIndex();
      return $this->draw('akunrekening.html', [
        'akunrekening' => $return
      ]);

    }

    public function anyAkunRekeningForm()
    {
        $return = $this->akunrekening->anyForm();
        echo $this->draw('akunrekening.form.html', ['akunrekening' => $return]);
        exit();
    }

    public function anyAkunRekeningDisplay()
    {
        $return = $this->akunrekening->anyDisplay();
        echo $this->draw('akunrekening.display.html', ['akunrekening' => $return]);
        exit();
    }

    public function postAkunRekeningSave()
    {
      $this->akunrekening->postSave();
      exit();
    }

    public function postAkunRekeningHapus()
    {
      $this->akunrekening->postHapus();
      exit();
    }

    public function getAkunRekeningJS()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/keuangan/js/admin/akunrekening.js');
        exit();
    }
    /* End Bahasa Section */

    public function getRekeningTahun()
    {
      $this->core->addJS(url([ADMIN, 'keuangan', 'akunrekeningjs']), 'footer');
      $this->_addHeaderFiles();
      $curr_year = date('Y');
      $akunrekening = $this->core->mysql('mlite_rekening')->toArray();
      $rekeningtahun = $this->core->mysql('mlite_rekeningtahun')
      ->join('mlite_rekening', 'mlite_rekening.kd_rek=mlite_rekeningtahun.kd_rek')
      ->where('thn', $curr_year)
      ->toArray();
      return $this->draw('rekening.tahun.html', ['akunrekening' => $akunrekening, 'rekeningtahun' => $rekeningtahun]);
    }

    public function postSaveRekeningTahun()
    {
      if($_POST['simpan']) {
        $this->core->mysql('mlite_rekeningtahun')
        ->save([
          'thn' => $_POST['tahun'],
          'kd_rek' => $_POST['kd_rek'],
          'saldo_awal' => $_POST['saldo_awal']
        ]);
        $this->notify('success', 'Rekening tahun telah disimpan');
      } else if ($_POST['update']) {
        $this->core->mysql('mlite_rekeningtahun')
        ->where('thn', $_POST['tahun'])
        ->where('kd_rek', $_POST['kd_rek'])
        ->save([
          'saldo_awal' => $_POST['saldo_awal']
        ]);
        $this->notify('failure', 'Rekening tahun telah diubah');
      } else if ($_POST['hapus']) {
        $this->core->mysql('mlite_rekeningtahun')
        ->where('thn', $_POST['tahun'])
        ->where('kd_rek', $_POST['kd_rek'])
        ->delete();
        $this->notify('failure', 'Rekening tahun  telah dihapus');
      }
      redirect(url([ADMIN, 'keuangan', 'rekeningtahun']));
    }

    public function getPengaturanRekening()
    {
      $this->core->addJS(url([ADMIN, 'keuangan', 'akunrekeningjs']), 'footer');
      $akunkegiatan = $this->core->mysql('mlite_akun_kegiatan')->toArray();
      $akunrekening = $this->core->mysql('mlite_rekening')->toArray();
      return $this->draw('pengaturan.rekening.html', ['akunkegiatan' => $akunkegiatan, 'akunrekening' => $akunrekening]);
    }

    public function getPostingJurnal()
    {
      $this->_addHeaderFiles();
      $kegiatan = $this->core->mysql('mlite_akun_kegiatan')->toArray();
      $akunrekening = $this->core->mysql('mlite_rekening')->toArray();
      return $this->draw('posting.jurnal.html', ['kegiatan' => $kegiatan, 'akunrekening' => $akunrekening, 'no_jurnal' => $this->core->setNoJurnal()]);
    }

    public function getJurnalHarian()
    {
      $this->_addHeaderFiles();
      $settings = $this->settings('settings');
      $this->tpl->set('settings', $this->tpl->noParse_array(htmlspecialchars_array($settings)));

      $tgl_awal = date('Y-m-d');
      $tgl_akhir = date('Y-m-d');

      if(isset($_GET['tgl_awal'])) {
        $tgl_awal = $_GET['tgl_awal'];
      }
      if(isset($_GET['tgl_akhir'])) {
        $tgl_akhir = $_GET['tgl_akhir'];
      }

      $jurnalharian = [];
      $rows = $this->core->mysql('mlite_jurnal')
        ->join('mlite_detailjurnal', 'mlite_detailjurnal.no_jurnal=mlite_jurnal.no_jurnal')
        ->join('mlite_rekening', 'mlite_rekening.kd_rek=mlite_detailjurnal.kd_rek')
        ->where('tgl_jurnal', '>=', $tgl_awal)
        ->where('tgl_jurnal', '<=', $tgl_akhir)
        ->toArray();
      foreach ($rows as $row) {
        if($row['jenis'] == 'U') {
          $row['jenis'] = 'Umum';
        } else {
          $row['jenis'] = 'Penyesuaian';
        }
        $rekening = $this->core->mysql('mlite_rekening')
          ->where('kd_rek', $row['kd_rek'])->oneArray();
        $row['nm_rek'] = $rekening['nm_rek'];
        $jurnalharian[] = $row;
      }
      if(isset($_GET['action']) && $_GET['action'] == 'print') {
        echo $this->draw('jurnal.harian.print.html', [
          'jurnalharian' => $jurnalharian,
          'action' => url([ADMIN,'keuangan','jurnalharian'])
        ]);
        exit();
      } else {
        return $this->draw('jurnal.harian.html', ['jurnalharian' => $jurnalharian]);
      }
    }

    public function getBukuBesar()
    {
      $this->_addHeaderFiles();
      $this->core->addJS(url([ADMIN, 'keuangan', 'akunrekeningjs']), 'footer');
      $settings = $this->settings('settings');
      $this->tpl->set('settings', $this->tpl->noParse_array(htmlspecialchars_array($settings)));

      $tgl_awal = date('Y-m-d');
      $tgl_akhir = date('Y-m-d');

      if(isset($_GET['tgl_awal'])) {
        $tgl_awal = $_GET['tgl_awal'];
      }
      if(isset($_GET['tgl_akhir'])) {
        $tgl_akhir = $_GET['tgl_akhir'];
      }

      $query = $this->core->mysql()->pdo()->query("SELECT mlite_detailjurnal.no_jurnal, tgl_jurnal, keterangan, debet, kredit, cast((@saldo:= @saldo+kredit-debet) AS DECIMAL(12,0)) AS saldo FROM mlite_detailjurnal JOIN (SELECT @saldo := 0) as saldo_sementara JOIN mlite_jurnal ON mlite_detailjurnal.no_jurnal = mlite_jurnal.no_jurnal WHERE (mlite_jurnal.tgl_jurnal BETWEEN '$tgl_awal' AND '$tgl_akhir') ORDER BY mlite_detailjurnal.no_jurnal ASC");
      $query->execute();
      $bukubesar = $query->fetchAll(\PDO::FETCH_ASSOC);;

      if(isset($_GET['action']) && $_GET['action'] == 'print') {
        echo $this->draw('buku.besar.print.html', [
          'bukubesar' => $bukubesar,
          'action' => url([ADMIN,'keuangan','bukubesar'])
        ]);
        exit();
      } else {
        return $this->draw('buku.besar.html', ['bukubesar' => $bukubesar]);
      }
    }

    public function getCashFlow()
    {
      $settings = $this->settings('settings');
      $this->tpl->set('settings', $this->tpl->noParse_array(htmlspecialchars_array($settings)));
      $curr_year = date('Y');
      $aruskas = [];

      $rows_aruskas = array(
          array(
              "tipe" => "N",
              "arus_kas" => "Kegiatan Operasional",
          ),
          array(
              "tipe" => "R",
              "arus_kas" => "Kegiatan Pendanaan",
          ),
          array(
              "tipe" => "M",
              "arus_kas" => "Kegiatan Investasi",
          )
      );
      $total_kredit = 0;
      $total_debet = 0;
      $total_saldo_kredit = 0;
      $total_saldo_debet = 0;
      $n = 1;
      foreach ($rows_aruskas as $row) {
        $row['nomor'] = $n++;
        $row['total_masuk'] = 0;
        $row['total_keluar'] = 0;
        $row['total_saldo_awal_masuk'] = 0;
        $row['total_saldo_awal_keluar'] = 0;
        $jumlah_total_saldo = 0;
        $rows_kredit = $this->core->mysql('mlite_detailjurnal')
        ->join('mlite_rekening', 'mlite_rekening.kd_rek=mlite_detailjurnal.kd_rek')
        ->where('tipe', $row['tipe'])
        ->where('balance', 'K')
        ->group('mlite_detailjurnal.kd_rek')
        ->toArray();
        $row['jurnal_masuk'] = [];
        foreach ($rows_kredit as $row_kredit) {
          $kredits = $this->core->mysql('mlite_detailjurnal')->where('kd_rek', $row_kredit['kd_rek'])->toArray();
          $row_kredit['kredit_all'] = 0;
          foreach ($kredits as $kredit) {
            $row['total_masuk'] += $kredit['kredit'];
            $row_kredit['kredit_all'] += $kredit['kredit'];
          }
          $saldo_awal = $this->core->mysql('mlite_rekeningtahun')->where('kd_rek', $row_kredit['kd_rek'])->oneArray();
          $row_kredit['saldo_awal'] = $saldo_awal['saldo_awal'];
          $row['total_saldo_awal_masuk'] += $saldo_awal['saldo_awal'];
          $row['jurnal_masuk'][] = $row_kredit;
          $total_saldo_kredit += $row['total_saldo_awal_masuk'];
          $total_kredit += $row_kredit['kredit_all'];
        }

        $rows_debet = $this->core->mysql('mlite_detailjurnal')
        ->join('mlite_rekening', 'mlite_rekening.kd_rek=mlite_detailjurnal.kd_rek')
        ->where('tipe', $row['tipe'])
        ->where('balance', 'D')
        ->group('mlite_detailjurnal.kd_rek')
        ->toArray();
        $row['jurnal_keluar'] = [];
        foreach ($rows_debet as $row_debet) {
          $debets = $this->core->mysql('mlite_detailjurnal')->where('kd_rek', $row_debet['kd_rek'])->toArray();
          $row_debet['debet_all'] = 0;
          foreach ($debets as $debet) {
            $row['total_keluar'] += $debet['debet'];
            $row_debet['debet_all'] += $debet['debet'];
          }
          $saldo_awal = $this->core->mysql('mlite_rekeningtahun')->where('kd_rek', $row_debet['kd_rek'])->oneArray();
          $row_debet['saldo_awal'] = $saldo_awal['saldo_awal'];
          $row['total_saldo_awal_keluar'] += $saldo_awal['saldo_awal'];
          $row['jurnal_keluar'][] = $row_debet;
          $total_saldo_debet += $row['total_saldo_awal_keluar'];
          $total_debet += $row_debet['debet_all'];
        }
        $aruskas[] = $row;
        $total_saldo_awal = $this->core->mysql('mlite_rekeningtahun')->toArray();
        foreach ($total_saldo_awal as $saldo) {
          $jumlah_total_saldo += $saldo['saldo_awal'];
        }
      }
      $akunrekening = $this->core->mysql('mlite_rekening')->toArray();
      if(isset($_GET['action']) && $_GET['action'] == 'print') {
        echo $this->draw('cash.flow.print.html', ['aruskas' => $aruskas, 'akunrekening' => $akunrekening, 'masuk_all' => $total_kredit, 'keluar_all' => $total_debet, 'saldo_masuk' => $total_saldo_kredit, 'saldo_keluar' => $total_saldo_debet, 'jumlah_total_saldo' => $jumlah_total_saldo]);
        exit();
      } else {
        return $this->draw('cash.flow.html', ['aruskas' => $aruskas, 'akunrekening' => $akunrekening, 'masuk_all' => $total_kredit, 'keluar_all' => $total_debet, 'saldo_masuk' => $total_saldo_kredit, 'saldo_keluar' => $total_saldo_debet, 'jumlah_total_saldo' => $jumlah_total_saldo]);
      }
    }

    public function getNeraca()
    {
      return $this->draw('blank.html');
    }

    public function getSettings()
    {
        $this->assign['title'] = 'Pengaturan Modul Keuangan';
        $this->assign['keuangan'] = htmlspecialchars_array($this->settings('keuangan'));
        $akunkegiatan = $this->core->mysql('mlite_settings')->where('module', 'keuangan')->where('field', '<>', 'jurnal_kasir')->toArray();
        $akunrekening = $this->core->mysql('mlite_rekening')->toArray();
        return $this->draw('settings.html', ['settings' => $this->assign, 'akunkegiatan' => $akunkegiatan, 'akunrekening' => $akunrekening]);
    }

    public function postSaveSettings()
    {
        foreach ($_POST['keuangan'] as $key => $val) {
            $this->settings('keuangan', $key, $val);
        }
        $this->notify('success', 'Pengaturan keuangan telah disimpan');
        redirect(url([ADMIN, 'keuangan', 'settings']));
    }


    public function postSaveAkunKegiatan()
    {
        if($_POST['simpan']) {
          $this->core->mysql('mlite_akun_kegiatan')
          ->save([
            'id' => NULL,
            'kegiatan' => $_POST['nama_kegiatan'],
            'kd_rek' => $_POST['kd_rek']
          ]);
          $this->notify('success', 'Nama kegiatan keuangan telah disimpan');
        } else if ($_POST['update']) {
          $this->core->mysql('mlite_akun_kegiatan')
          ->where('id', $_POST['id'])
          ->save([
            'kegiatan' => $_POST['nama_kegiatan'],
            'kd_rek' => $_POST['kd_rek']
          ]);
          $this->notify('failure', 'Nama kegiatan keuangan telah diubah');
        } else if ($_POST['hapus']) {
          $this->core->mysql('mlite_akun_kegiatan')
          ->where('id', $_POST['id'])
          ->delete();
          $this->notify('failure', 'Nama kegiatan keuangan telah dihapus');
        }
        redirect(url([ADMIN, 'keuangan', 'pengaturanrekening']));
    }

    public function postSaveSettingsRekening()
    {
        foreach ($_POST['kegiatan'] as $key => $val) {
            $this->core->mysql('mlite_akun_kegiatan')
            ->where('id', $key)
            ->save([
              'kd_rek' => $val
            ]);
        }
        $this->notify('success', 'Pengaturan rekeing keuangan telah disimpan');
        //exit();
        redirect(url([ADMIN, 'keuangan', 'pengaturanrekening']));
    }

    public function postSaveJurnal()
    {
        $query = $this->core->mysql('mlite_jurnal')
        ->save([
          'no_jurnal' => $_POST['no_jurnal'],
          'no_bukti' => $_POST['no_bukti'],
          'tgl_jurnal' => $_POST['tgl_jurnal'],
          'jenis' => $_POST['jenis'],
          'keterangan' => $_POST['kegiatan'].'. Diposting oleh '.$this->core->getUserInfo('fullname', null, true).'. ('.$_POST['keterangan'].').'
          ]);
        $this->core->mysql('mlite_detailjurnal')
        ->save([
          'no_jurnal' => $_POST['no_jurnal'],
          'kd_rek' => $_POST['kd_rek'],
          'debet' => $_POST['debet'],
          'kredit' => $_POST['kredit']
        ]);
        $this->notify('success', 'Posting jurnal telah disimpan');
        redirect(url([ADMIN, 'keuangan', 'postingjurnal']));
    }

    private function _addHeaderFiles()
    {
        $this->core->addCSS(url('assets/css/dataTables.bootstrap.min.css'));
        $this->core->addJS(url('assets/jscripts/jquery.dataTables.min.js'));
        $this->core->addJS(url('assets/jscripts/dataTables.bootstrap.min.js'));
        $this->core->addCSS(url('assets/css/bootstrap-datetimepicker.css'));
        $this->core->addJS(url('assets/jscripts/moment-with-locales.js'));
        $this->core->addJS(url('assets/jscripts/bootstrap-datetimepicker.js'));
    }

}
