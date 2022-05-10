<?php

namespace Plugins\Cuti;

use Systems\AdminModule;
use Systems\Lib\Fpdf\PDF_MC_Table;
use Systems\MySQL;

class Admin extends AdminModule
{
    public function navigation()
    {
        return [
            'Kelola' => 'index',
            'Pengajuan Cuti' => 'manage',
            'Rekap Cuti' => 'rekap_cuti'
        ];
    }

    public function getIndex()
    {
      $sub_modules = [
        ['name' => 'Pengajuan Cuti', 'url' => url([ADMIN, 'cuti', 'manage']), 'icon' => 'calendar-check-o', 'desc' => 'Pengajuan Cuti Pegawai'],
        ['name' => 'Rekap Cuti', 'url' => url([ADMIN, 'cuti', 'rekap_cuti']), 'icon' => 'calendar', 'desc' => 'Rekap Cuti Pegawai'],
      ];
      return $this->draw('index.html', ['sub_modules' => $sub_modules]);
    }

    public function anyManage()
    {
        $tgl_pengajuan = date('Y-m-d');
        $tgl_pengajuan_akhir = date('Y-m-d');
        $status_cuti = '';

        if(isset($_POST['periode_cuti'])) {
          $tgl_pengajuan = $_POST['periode_cuti'];
        }
        if(isset($_POST['periode_cuti_akhir'])) {
          $tgl_pengajuan_akhir = $_POST['periode_cuti_akhir'];
        }
        if(isset($_POST['status_cuti'])) {
          $status_cuti = $_POST['status_cuti'];
        }

        $this->_Display($tgl_pengajuan, $tgl_pengajuan_akhir, $status_cuti);
        return $this->draw('manage.html',['cuti' => $this->assign]);
    }

    public function anyDisplay()
    {
        $tgl_pengajuan = date('Y-m-d');
        $tgl_pengajuan_akhir = date('Y-m-d');
        $status_cuti = '';

        if(isset($_POST['periode_cuti'])) {
          $tgl_pengajuan = $_POST['periode_cuti'];
        }
        if(isset($_POST['periode_cuti_akhir'])) {
          $tgl_pengajuan_akhir = $_POST['periode_cuti_akhir'];
        }
        if(isset($_POST['status_cuti'])) {
          $status_cuti = $_POST['status_cuti'];
        }
        $this->_Display($tgl_pengajuan, $tgl_pengajuan_akhir, $status_cuti);
        echo $this->draw('display.html', ['cuti' => $this->assign]);
        exit();
    }

    public function _Display($tgl_pengajuan, $tgl_pengajuan_akhir, $status_cuti='')
    {
        $username = $this->core->getUserInfo('username', null, true);
        $this->_addHeaderFiles();
        $sql = "SELECT pengajuan_cuti.no_pengajuan,
        pengajuan_cuti.tanggal,
        pengajuan_cuti.tanggal_awal,
        pengajuan_cuti.tanggal_akhir,
        pengajuan_cuti.nik,
        peg1.nama AS namapengaju,
        peg1.jbtn,
        peg1.departemen,
        pengajuan_cuti.urgensi,
        pengajuan_cuti.alamat,
        pengajuan_cuti.jumlah,
        pengajuan_cuti.kepentingan,
        pengajuan_cuti.nik_pj,
        peg2.nama AS namapj,
        pengajuan_cuti.status 
        FROM pengajuan_cuti 
        INNER JOIN pegawai AS peg1 ON pengajuan_cuti.nik=peg1.nik 
        INNER JOIN pegawai AS peg2 ON pengajuan_cuti.nik_pj=peg2.nik 
        WHERE pengajuan_cuti.tanggal BETWEEN '$tgl_pengajuan' AND '$tgl_pengajuan_akhir'";

        if ($this->core->getUserInfo('role') != 'admin') {
          $sql .= " AND pengajuan_cuti.nik='$username'";
        }
        if($status_cuti == 'Proses Pengajuan') {
          $sql .= " AND pengajuan_cuti.status='Proses Pengajuan'";
        }
        if($status_cuti == 'Disetujui') {
          $sql .= " AND pengajuan_cuti.status='Disetujui'";
        }
        if($status_cuti == 'Ditolak') {
          $sql .= " AND pengajuan_cuti.status='Ditolak'";
        }
        $sql .= " ORDER BY pengajuan_cuti.tanggal";
        $stmt = $this->mysql()->pdo()->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        $this->assign['list'] = [];
        foreach ($rows as $row) {
          $this->assign['list'][] = $row;
        }
    }

    public function anyForm()
    {

      $this->assign['urgensi'] = array("Tahunan","Besar","Sakit","Bersalin","Alasan Penting","Keterangan Lainnya");
      $this->assign['pj'] = $this->mysql('pegawai')->where('stts_aktif', '!=', 'KELUAR')->toArray();
      $this->assign['no_pengajuan'] = '';
      $this->assign['tgl_pengajuan']= date('Y-m-d');
      $this->assign['tgl_akhir']= date('Y-m-d');
      if (isset($_POST['no_pengajuan'])){
        $this->assign['pengajuan_cuti'] = $this->mysql('pengajuan_cuti')
        ->select([
          'no_pengajuan' => 'pengajuan_cuti.no_pengajuan',
          'tanggal' => 'pengajuan_cuti.tanggal',
          'tanggal_awal' => 'pengajuan_cuti.tanggal_awal',
          'tanggal_akhir' => 'pengajuan_cuti.tanggal_akhir',
          'nik' => 'pengajuan_cuti.nik',
          'urgensi' => 'pengajuan_cuti.urgensi',
          'alamat' => 'pengajuan_cuti.alamat',
          'jumlah' => 'pengajuan_cuti.jumlah',
          'kepentingan' => 'pengajuan_cuti.kepentingan',
          'nik_pj' => 'pengajuan_cuti.nik_pj',
          'status' => 'pengajuan_cuti.status',
          'nama' => 'pegawai.nama',
      ])
          ->join('pegawai', 'pengajuan_cuti.nik_pj=pegawai.nik')
          ->where('no_pengajuan', $_POST['no_pengajuan'])
          ->oneArray();
        echo $this->draw('form.html', [
          'cuti' => $this->assign
        ]);
       } else {
        $this->assign['pengajuan_cuti'] = [
          'no_pengajuan' => '',
          'tanggal' => '',
          'tanggal_awal' => '',
          'tanggal_akhir' => '',
          'nik' => '',
          'urgensi' => '',
          'alamat' => '',
          'jumlah' => '',
          'kepentingan' => '',
          'nik_pj' => '',
          'status' => ''
         ];
        echo $this->draw('form.html', [
          'cuti' => $this->assign
        ]);
      }
      exit();
    }    
    public function postMaxid()
    {
      $max_id = $this->mysql('pengajuan_cuti')->select(['no_pengajuan' => 'ifnull(MAX(CONVERT(RIGHT(no_pengajuan,3),signed)),0)'])->where('tanggal', date('Y-m-d'))->oneArray();
      if(empty($max_id['no_pengajuan'])) {
        $max_id['no_pengajuan'] = '000';
      }
      $_next_no_rawat = sprintf('%03s', ($max_id['no_pengajuan'] + 1));
      $next_no_rawat = 'PC'.date('Ymd').''.$_next_no_rawat;
      echo $next_no_rawat;
      exit();
    }

    public function postSave()
    {
      $username = $this->core->getUserInfo('username', null, true);
      
      $awal  = new \DateTime($_POST['tgl_pengajuan']);
      $akhir = new \DateTime($_POST['tgl_akhir']);
      $diff = $akhir->diff($awal, true); // to make the difference to be always positive.
      $sisa = $diff->format('%d');
      $jmla = $sisa + 1 ;

    if (!$this->mysql('pengajuan_cuti')->where('no_pengajuan', $_POST['no_pengajuan'])->oneArray()) {
      $query = $this->mysql('pengajuan_cuti')->save([
          'no_pengajuan' => $_POST['no_pengajuan'],
          'tanggal' => date('Y-m-d'),
          'tanggal_awal' => $_POST['tgl_pengajuan'],
          'tanggal_akhir' => $_POST['tgl_akhir'],
          'nik' => $username,
          'urgensi' => $_POST['urgensi'],
          'alamat' => $_POST['description'],
          'jumlah' => $jmla,
          'kepentingan' => $_POST['kepentingan'],
          'nik_pj' => $_POST['kd_pj'],
          'status' => 'Proses Pengajuan'
        ]);
        $dada = 'sukses';
        echo $dada;
      } else if ($this->mysql('pengajuan_cuti')->where('no_pengajuan', $_POST['no_pengajuan'])->where('status', 'Disetujui')->oneArray()) { 
        $dada = 'gagal';
        echo $dada;
      }
      else {
        $query = $this->mysql('pengajuan_cuti')->where('no_pengajuan', $_POST['no_pengajuan'])->save([
          'tanggal_awal' => $_POST['tgl_pengajuan'],
          'tanggal_akhir' => $_POST['tgl_akhir'],
          'urgensi' => $_POST['urgensi'],
          'alamat' => $_POST['description'],
          'jumlah' => $jmla,
          'kepentingan' => $_POST['kepentingan'],
          'nik_pj' => $_POST['kd_pj']
        ]);
        $dada = 'sukses';
        echo $dada;
      }    

      exit();
    }    
    public function postStatusCuti()
    {
      if($_POST['statusct'] == 'Ditolak') {
            $this->mysql('pengajuan_cuti')->where('no_pengajuan', $_POST['no_pengajuan'])->save([
              'status' => 'Ditolak'
            ]);
      } else if ($_POST['statusct'] == 'Disetujui') {
        $this->mysql('pengajuan_cuti')->where('no_pengajuan', $_POST['no_pengajuan'])->save([
          'status' => 'Disetujui'
        ]);
      } else {
        $this->mysql('pengajuan_cuti')->where('no_pengajuan', $_POST['no_pengajuan'])->save([
          'status' => 'Proses Pengajuan'
        ]);
          }
      exit();
    }

    public function postHapus()
    {
      $cek_cuti = $this->mysql('pengajuan_cuti')->where('no_pengajuan', $_POST['no_pengajuan'])->where('status', 'Disetujui')->oneArray();
      if ($cek_cuti) {
        $dapa = 'error';
        echo $dapa;
      } else{
        $this->mysql('pengajuan_cuti')->where('no_pengajuan', $_POST['no_pengajuan'])->delete();
      }
      exit();
    }

    public function getRekap_Cuti($page = 1)
    {
        $this->_addHeaderFiles();
        $perpage = '10';
        $phrase = '';
        if (isset($_GET['s']))
            $phrase = $_GET['s'];

        $tgl_pengajuan = date('Y');

        if (isset($_GET['awal'])) {
            $tgl_pengajuan = $_GET['awal'];
        }       

        $username = $this->core->getUserInfo('username', null, true);
        $totalRecords = $this->mysql('pengajuan_cuti')
        ->select([
          'SUM(pengajuan_cuti.jumlah) AS total'
        ])
        ->where('pengajuan_cuti.nik', $username)
        ->where('YEAR(tanggal)', $tgl_pengajuan)
        ->oneArray();
        
        $totalan = $this->mysql('pengajuan_cuti')
        ->like('kepentingan', '%' . $phrase . '%')
        ->where('nik', $username)
        ->where('YEAR(tanggal)', $tgl_pengajuan)
        ->desc('tanggal')
        ->toArray();


        $pagination = new \Systems\Lib\Pagination($page, count($totalan), 10, url([ADMIN, 'cuti', 'rekap_cuti', '%d?awal=' . $tgl_pengajuan . '&s=' . $phrase]));
        $this->assign['pagination'] = $pagination->nav('pagination', '5');
        $this->assign['totalRecords'] = $totalRecords;

        // list
        $offset = $pagination->offset();

        $sql = "SELECT pengajuan_cuti.no_pengajuan,
        pengajuan_cuti.tanggal,
        pengajuan_cuti.tanggal_awal,
        pengajuan_cuti.tanggal_akhir,
        pengajuan_cuti.nik,
        peg1.nama AS namapengaju,
        peg1.jbtn,
        peg1.departemen,
        pengajuan_cuti.urgensi,
        pengajuan_cuti.alamat,
        pengajuan_cuti.jumlah,
        pengajuan_cuti.kepentingan,
        pengajuan_cuti.nik_pj,
        peg2.nama AS namapj,
        pengajuan_cuti.status 
        FROM pengajuan_cuti 
        INNER JOIN pegawai AS peg1 ON pengajuan_cuti.nik=peg1.nik 
        INNER JOIN pegawai AS peg2 ON pengajuan_cuti.nik_pj=peg2.nik 
        WHERE pengajuan_cuti.nik='$username' AND YEAR(tanggal) = '$tgl_pengajuan' AND pengajuan_cuti.kepentingan like '%$phrase%' ORDER BY pengajuan_cuti.tanggal DESC LIMIT $offset , $perpage";

        $stmt = $this->mysql()->pdo()->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        $this->assign['list'] = [];
        foreach ($rows as $row) {
          $this->assign['list'][] = $row;
        }


        $secondminus = 12;
        foreach ($totalRecords as $time) {
          $this->assign['totalRecords']['total'] = $time;
        }
         $secondminus -= $time;

         $sisaRecords =  $secondminus;

        $this->assign['totalsisa'] = $sisaRecords;
        return $this->draw('rekap_cuti.html', ['rekap' => $this->assign]);
    }    

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/cuti/js/admin/cuti.js');
        exit();
    }

    private function _addHeaderFiles()
    {
        $this->core->addCSS(url('assets/css/dataTables.bootstrap.min.css'));
        $this->core->addJS(url('assets/jscripts/jquery.dataTables.min.js'));
        $this->core->addJS(url('assets/jscripts/dataTables.bootstrap.min.js'));
        $this->core->addJS(url('assets/jscripts/lightbox/lightbox.min.js'));
        $this->core->addCSS(url('assets/jscripts/lightbox/lightbox.min.css'));
        $this->core->addCSS(url('assets/css/bootstrap-datetimepicker.css'));
        $this->core->addJS(url('assets/jscripts/moment-with-locales.js'));
        $this->core->addJS(url('assets/jscripts/bootstrap-datetimepicker.js'));
        $this->core->addJS(url([ADMIN, 'cuti', 'javascript']), 'footer');
    }

    protected function data_icd($table)
    {
        return new DB_ICD($table);
    }
  
	protected function mysql($table = NULL)
    {
        return new MySQL($table);
    }  

}
