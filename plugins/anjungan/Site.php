<?php

namespace Plugins\Anjungan;

use Systems\SiteModule;

class Site extends SiteModule
{
    public function routes()
    {
        $this->route('anjungan', 'getIndex');
        $this->route('anjungan/pasien', 'getDisplayAPM');
        $this->route('anjungan/loket', 'getDisplayAntrianLoket');
        $this->route('anjungan/poli', 'getDisplayAntrianPoli');
        $this->route('anjungan/laboratorium', 'getDisplayAntrianLaboratorium');
        $this->route('anjungan/ajax', 'getAjax');
    }

    public function getIndex()
    {
        echo $this->draw('index.html');
        exit();
    }
    public function getDisplayAPM()
    {
        $title = 'Display Antrian Poliklinik';
        $logo  = $this->settings->get('settings.logo');
        $poliklinik = $this->db('poliklinik')->toArray();
        echo $this->draw('display.antrian.html', [
          'title' => $title,
          'logo' => $logo,
          'running_text' => $this->settings->get('anjungan.text_anjungan'),
          'poliklinik' => $poliklinik
        ]);
        exit();
    }

    public function getDisplayAntrianPoli()
    {
        $title = 'Display Antrian Poliklinik';
        $logo  = $this->settings->get('settings.logo');
        $display = $this->_resultDisplayAntrianPoli();
        echo $this->draw('display.antrian.poli.html', [
          'title' => $title,
          'logo' => $logo,
          'running_text' => $this->settings->get('anjungan.text_poli'),
          'display' => $display
        ]);
        exit();
    }

    public function _resultDisplayAntrianPoli()
    {
        $date = date('Y-m-d');
        $tentukan_hari=date('D',strtotime(date('Y-m-d')));
        $day = array(
          'Sun' => 'AKHAD',
          'Mon' => 'SENIN',
          'Tue' => 'SELASA',
          'Wed' => 'RABU',
          'Thu' => 'KAMIS',
          'Fri' => 'JUMAT',
          'Sat' => 'SABTU'
        );
        $hari=$day[$tentukan_hari];

        $poliklinik = str_replace(",","','", $this->settings->get('anjungan.display_poli'));
        $query = $this->db()->pdo()->prepare("SELECT a.kd_dokter, a.kd_poli, b.nm_poli, c.nm_dokter, a.jam_mulai, a.jam_selesai FROM jadwal a, poliklinik b, dokter c WHERE a.kd_poli = b.kd_poli AND a.kd_dokter = c.kd_dokter AND a.hari_kerja = '$hari'  AND a.kd_poli IN ('$poliklinik')");
        $query->execute();
        $rows = $query->fetchAll(\PDO::FETCH_ASSOC);;

        $result = [];
        if (count($rows)) {
            foreach ($rows as $row) {
                $row['dalam_pemeriksaan'] = $this->db('reg_periksa')
                  ->select('no_reg')
                  ->select('nm_pasien')
                  ->join('pasien', 'pasien.no_rkm_medis = reg_periksa.no_rkm_medis')
                  ->where('tgl_registrasi', $date)
                  ->where('stts', 'Berkas Diterima')
                  ->where('kd_poli', $row['kd_poli'])
                  ->where('kd_dokter', $row['kd_dokter'])
                  ->limit(1)
                  ->oneArray();
                $row['dalam_antrian'] = $this->db('reg_periksa')
                  ->select(['jumlah' => 'COUNT(DISTINCT reg_periksa.no_rawat)'])
                  ->join('poliklinik', 'poliklinik.kd_poli = reg_periksa.kd_poli')
                  ->where('reg_periksa.tgl_registrasi', date('Y-m-d'))
                  ->where('reg_periksa.kd_poli', $row['kd_poli'])
                  ->where('reg_periksa.kd_dokter', $row['kd_dokter'])
                  ->oneArray();
                $row['sudah_dilayani'] = $this->db('reg_periksa')
                  ->select(['count' => 'COUNT(DISTINCT reg_periksa.no_rawat)'])
                  ->join('poliklinik', 'poliklinik.kd_poli = reg_periksa.kd_poli')
                  ->where('reg_periksa.tgl_registrasi', date('Y-m-d'))
                  ->where('reg_periksa.kd_poli', $row['kd_poli'])
                  ->where('reg_periksa.kd_dokter', $row['kd_dokter'])
                  ->where('reg_periksa.stts', 'Sudah')
                  ->oneArray();
                $row['sudah_dilayani']['jumlah'] = 0;
                if(!empty($row['sudah_dilayani'])) {
                  $row['sudah_dilayani']['jumlah'] = $row['sudah_dilayani']['count'];
                }
                $row['selanjutnya'] = $this->db('reg_periksa')
                  ->select('reg_periksa.no_reg')
                  ->select(['no_urut_reg' => 'ifnull(MAX(CONVERT(RIGHT(reg_periksa.no_reg,3),signed)),0)'])
                  ->select('pasien.nm_pasien')
                  ->join('pasien', 'pasien.no_rkm_medis = reg_periksa.no_rkm_medis')
                  ->where('reg_periksa.tgl_registrasi', $date)
                  ->where('reg_periksa.stts', 'Belum')
                  ->where('reg_periksa.kd_poli', $row['kd_poli'])
                  ->where('reg_periksa.kd_dokter', $row['kd_dokter'])
                  ->asc('reg_periksa.no_reg')
                  ->toArray();
                $row['get_no_reg'] = $this->db('reg_periksa')
                  ->select(['max' => 'ifnull(MAX(CONVERT(RIGHT(no_reg,3),signed)),0)'])
                  ->where('tgl_registrasi', $date)
                  ->where('kd_poli', $row['kd_poli'])
                  ->where('kd_dokter', $row['kd_dokter'])
                  ->oneArray();
                $row['diff'] = (strtotime($row['jam_selesai'])-strtotime($row['jam_mulai']))/60;
                $row['interval'] = 0;
                if($row['diff'] == 0) {
                  $row['interval'] = round($row['diff']/$row['get_no_reg']['max']);
                }
                if($row['interval'] > 10){
                  $interval = 10;
                } else {
                  $interval = $row['interval'];
                }
                foreach ($row['selanjutnya'] as $value) {
                  $minutes = $value['no_urut_reg'] * $interval;
                  $row['jam_mulai'] = date('H:i',strtotime('+10 minutes',strtotime($row['jam_mulai'])));
                }

                $result[] = $row;
            }
        }

        return $result;
    }

    public function getDisplayAntrianLoket()
    {
        $title = 'Display Antrian Loket';
        $logo  = $this->settings->get('settings.logo');
        $display = '';
        $show = isset($_GET['show']) ? $_GET['show'] : "";
        switch($show){
          default:
            $display = 'Depan';
            echo $this->draw('display.antrian.loket.html', [
              'title' => $title,
              'logo' => $logo,
              'show' => $show,
              'vidio' => $this->settings->get('anjungan.vidio'),
              'running_text' => $this->settings->get('anjungan.text_loket'),
              'display' => $display
            ]);
          break;
          case "panggil_loket":
            $display = 'Panggil Loket';
            $setting_antrian_loket = str_replace(",","','", $this->settings->get('anjungan.antrian_loket'));
            $loket = explode(",", $this->settings->get('anjungan.antrian_loket'));
            $get_antrian = $this->db('mlite_antrian_loket')->select('noantrian')->where('type', 'Loket')->where('postdate', date('Y-m-d'))->desc('start_time')->oneArray();
            $noantrian = 0;
            if(!empty($get_antrian['noantrian'])) {
              $noantrian = $get_antrian['noantrian'];
            }

            //$antriloket = $this->db('antriloket')->oneArray();
            //$tcounter = $antriloket['antrian'];
            $antriloket = $this->settings->get('anjungan.panggil_loket_nomor');
            $tcounter = $antriloket;
            $_tcounter = 1;
            if(!empty($tcounter)) {
              $_tcounter = $tcounter + 1;
            }
            if(isset($_GET['loket'])) {
              $this->db('mlite_antrian_loket')
                ->where('type', 'Loket')
                ->where('noantrian', $tcounter)
                ->where('postdate', date('Y-m-d'))
                ->save(['end_time' => date('H:i:s')]);
              /*$this->db()->pdo()->exec("DELETE FROM `antriloket`");
              $this->db('antriloket')->save([
                'loket' => $_GET['loket'],
                'antrian' => $_tcounter
              ]);*/
              $this->db('mlite_settings')->where('module', 'anjungan')->where('field', 'panggil_loket')->save(['value' => $_GET['loket']]);
              $this->db('mlite_settings')->where('module', 'anjungan')->where('field', 'panggil_loket_nomor')->save(['value' => $_tcounter]);
            }
            if(isset($_GET['antrian'])) {
              /*$this->db()->pdo()->exec("DELETE FROM `antriloket`");
              $this->db('antriloket')->save([
                'loket' => $_GET['reset'],
                'antrian' => $_GET['antrian']
              ]);*/
              $this->db('mlite_settings')->where('module', 'anjungan')->where('field', 'panggil_loket')->save(['value' => $_GET['reset']]);
              $this->db('mlite_settings')->where('module', 'anjungan')->where('field', 'panggil_loket_nomor')->save(['value' => $_GET['antrian']]);
            }
            $hitung_antrian = $this->db('mlite_antrian_loket')
              ->where('type', 'Loket')
              ->like('postdate', date('Y-m-d'))
              ->toArray();
            $counter = strlen($tcounter);
            $xcounter = [];
            for($i=0;$i<$counter;$i++){
            	$xcounter[] = '<audio id="suarabel'.$i.'" src="{?=url()?}/plugins/anjungan/suara/'.substr($tcounter,$i,1).'.wav" ></audio>';
            };

            echo $this->draw('display.antrian.loket.html', [
              'title' => $title,
              'logo' => $logo,
              'show' => $show,
              'loket' => $loket,
              'namaloket' => 'a',
              'panggil_loket' => 'panggil_loket',
              'antrian' => $tcounter,
              'hitung_antrian' => $hitung_antrian,
              'xcounter' => $xcounter,
              'noantrian' =>$noantrian,
              'display' => $display
            ]);
          break;
          case "panggil_cs":
            $display = 'Panggil CS';
            $loket = explode(",", $this->settings->get('anjungan.antrian_cs'));
            $get_antrian = $this->db('mlite_antrian_loket')->select('noantrian')->where('type', 'CS')->where('postdate', date('Y-m-d'))->desc('start_time')->oneArray();
            $noantrian = 0;
            if(!empty($get_antrian['noantrian'])) {
              $noantrian = $get_antrian['noantrian'];
            }

            //$antriloket = $this->db('antrics')->oneArray();
            //$tcounter = $antriloket['antrian'];
            $antriloket = $this->settings->get('anjungan.panggil_cs_nomor');
            $tcounter = $antriloket;
            $_tcounter = 1;
            if(!empty($tcounter)) {
              $_tcounter = $tcounter + 1;
            }
            if(isset($_GET['loket'])) {
              $this->db('mlite_antrian_loket')
                ->where('type', 'CS')
                ->where('noantrian', $tcounter)
                ->where('postdate', date('Y-m-d'))
                ->save(['end_time' => date('H:i:s')]);
              /*$this->db()->pdo()->exec("DELETE FROM `antrics`");
              $this->db('antrics')->save([
                'loket' => $_GET['loket'],
                'antrian' => $_tcounter
              ]);*/
              $this->db('mlite_settings')->where('module', 'anjungan')->where('field', 'panggil_cs')->save(['value' => $_GET['loket']]);
              $this->db('mlite_settings')->where('module', 'anjungan')->where('field', 'panggil_cs_nomor')->save(['value' => $_tcounter]);
            }
            if(isset($_GET['antrian'])) {
              /*$this->db()->pdo()->exec("DELETE FROM `antrics`");
              $this->db('antrics')->save([
                'loket' => $_GET['reset'],
                'antrian' => $_GET['antrian']
              ]);*/
              $this->db('mlite_settings')->where('module', 'anjungan')->where('field', 'panggil_cs')->save(['value' => $_GET['reset']]);
              $this->db('mlite_settings')->where('module', 'anjungan')->where('field', 'panggil_cs_nomor')->save(['value' => $_GET['antrian']]);
            }
            $hitung_antrian = $this->db('mlite_antrian_loket')
              ->where('type', 'CS')
              ->like('postdate', date('Y-m-d'))
              ->toArray();
            $counter = strlen($tcounter);
            $xcounter = [];
            for($i=0;$i<$counter;$i++){
              $xcounter[] = '<audio id="suarabel'.$i.'" src="{?=url()?}/plugins/anjungan/suara/'.substr($tcounter,$i,1).'.wav" ></audio>';
            };

            echo $this->draw('display.antrian.loket.html', [
              'title' => $title,
              'logo' => $logo, 
              'show' => $show,
              'loket' => $loket,
              'namaloket' => 'b',
              'panggil_loket' => 'panggil_cs',
              'antrian' => $tcounter,
              'hitung_antrian' => $hitung_antrian,
              'xcounter' => $xcounter,
              'noantrian' =>$noantrian,
              'display' => $display
            ]);
          break;
        }
        exit();
    }

    public function getDisplayAntrianLaboratorium()
    {
        $logo  = $this->settings->get('settings.logo');
        $title = 'Display Antrian Laboratorium';
        $display = $this->_resultDisplayAntrianLaboratorium();
        echo $this->draw('display.antrian.laboratorium.html', [
          'logo' => $logo,
          'title' => $title,
          'running_text' => $this->settings->get('anjungan.text_laboratorium'),
          'display' => $display
        ]);
        exit();
    }

    public function _resultDisplayAntrianLaboratorium()
    {
        $date = date('Y-m-d');
        $tentukan_hari=date('D',strtotime(date('Y-m-d')));
        $day = array(
          'Sun' => 'AKHAD',
          'Mon' => 'SENIN',
          'Tue' => 'SELASA',
          'Wed' => 'RABU',
          'Thu' => 'KAMIS',
          'Fri' => 'JUMAT',
          'Sat' => 'SABTU'
        );
        $hari=$day[$tentukan_hari];

        $poliklinik = $this->settings('settings', 'laboratorium');
        $rows = $this->db('reg_periksa')
          ->join('pasien', 'pasien.no_rkm_medis=reg_periksa.no_rkm_medis')
          ->where('tgl_registrasi', date('Y-m-d'))
          ->where('kd_poli', $poliklinik)
          ->asc('no_reg')
          ->toArray();

        return $rows;
    }

    public function getAjax()
    {
      $show = isset($_GET['show']) ? $_GET['show'] : "";
      switch($show){
       default:
        break;
        case "tampilloket":
          $result = $this->db('mlite_antrian_loket')->select('noantrian')->where('type', 'Loket')->where('postdate', date('Y-m-d'))->desc('start_time')->oneArray();
        	$noantrian = $result['noantrian'];
        	if($noantrian > 0) {
        		$next_antrian = $noantrian + 1;
        	} else {
        		$next_antrian = 1;
        	}
        	echo '<div id="nomernya" align="center">';
          echo '<h1 class="display-1">';
          echo 'A'.$next_antrian;
          echo '</h1>';
          echo '['.date('Y-m-d').']';
          echo '</div>';
          echo '<br>';
        break;
        case "printloket":
          $result = $this->db('mlite_antrian_loket')->select('noantrian')->where('type', 'Loket')->where('postdate', date('Y-m-d'))->desc('start_time')->oneArray();
        	$noantrian = $result['noantrian'];
        	if($noantrian > 0) {
        		$next_antrian = $noantrian + 1;
        	} else {
        		$next_antrian = 1;
        	}
        	echo '<div id="nomernya" align="center">';
          echo '<h1 class="display-1">';
          echo 'A'.$next_antrian;
          echo '</h1>';
          echo '['.date('Y-m-d').']';
          echo '</div>';
          echo '<br>';
          ?>
          <script>
        	$(document).ready(function(){
        		$("#btnKRM").on('click', function(){
        			$("#formloket").submit(function(){
        				$.ajax({
        					url: "<?php echo url().'/anjungan/ajax?show=simpanloket&noantrian='.$next_antrian; ?>",
        					type:"POST",
        					data:$(this).serialize(),
        					success:function(data){
        						setTimeout('$("#loading").hide()',1000);
        						//window.location.href = "{?=url('anjungan/pasien')?}";
        						}
        					});
        				return false;
        			});
        		});
        	})
        	</script>
          <?php
        break;
        case "simpanloket":
          $this->db('mlite_antrian_loket')
            ->save([
              'kd' => NULL,
              'type' => 'Loket',
              'noantrian' => $_GET['noantrian'],
              'postdate' => date('Y-m-d'),
              'start_time' => date('H:i:s'),
              'end_time' => '00:00:00'
            ]);
          //redirect(url('anjungan/pasien'));
        break;
        case "tampilcs":
          $result = $this->db('mlite_antrian_loket')->select('noantrian')->where('type', 'CS')->where('postdate', date('Y-m-d'))->desc('start_time')->oneArray();
        	$noantrian = $result['noantrian'];
        	if($noantrian > 0) {
        		$next_antrian = $noantrian + 1;
        	} else {
        		$next_antrian = 1;
        	}
        	echo '<div id="nomernya" align="center">';
          echo '<h1 class="display-1">';
          echo 'B'.$next_antrian;
          echo '</h1>';
          echo '['.date('Y-m-d').']';
          echo '</div>';
          echo '<br>';
        break;
        case "printcs":
          $result = $this->db('mlite_antrian_loket')->select('noantrian')->where('type', 'CS')->where('postdate', date('Y-m-d'))->desc('start_time')->oneArray();
        	$noantrian = $result['noantrian'];
        	if($noantrian > 0) {
        		$next_antrian = $noantrian + 1;
        	} else {
        		$next_antrian = 1;
        	}
        	echo '<div id="nomernya" align="center">';
          echo '<h1 class="display-1">';
          echo 'B'.$next_antrian;
          echo '</h1>';
          echo '['.date('Y-m-d').']';
          echo '</div>';
          echo '<br>';
          ?>
          <script>
        	$(document).ready(function(){
        		$("#btnKRMCS").on('click', function(){
        			$("#formcs").submit(function(){
        				$.ajax({
        					url: "<?php echo url().'/anjungan/ajax?show=simpancs&noantrian='.$next_antrian; ?>",
        					type:"POST",
        					data:$(this).serialize(),
        					success:function(data){
        						setTimeout('$("#loading").hide()',1000);
        						window.location.href = "{?=url('anjungan/pasien')?}";
        						}
        					});
        				return false;
        			});
        		});
        	})
        	</script>
          <?php
        break;
        case "simpancs":
          $this->db('mlite_antrian_loket')
            ->save([
              'kd' => NULL,
              'type' => 'CS',
              'noantrian' => $_GET['noantrian'],
              'postdate' => date('Y-m-d'),
              'start_time' => date('H:i:s'),
              'end_time' => '00:00:00'
            ]);
          redirect(url('anjungan/pasien'));
        break;
        case "loket":
          //$antrian = $this->db('antriloket')->oneArray();
          //echo $antrian['loket'];
          echo $this->settings->get('anjungan.panggil_loket');
        break;
        case "antriloket":
          //$antrian = $this->db('antriloket')->oneArray();
          //$antrian = $antrian['antrian'] - 1;
          $antrian = $this->settings->get('anjungan.panggil_loket_nomor') - 1;
          if($antrian == '-1') {
            echo '0';
          } else {
            echo $antrian;
          }
        break;
        case "cs":
          //$antrian = $this->db('antrics')->oneArray();
          //echo $antrian['loket'];
          echo $this->settings->get('anjungan.panggil_cs');
        break;
        case "antrics":
          //$antrian = $this->db('antrics')->oneArray();
          //$antrian = $antrian['antrian'] - 1;
          $antrian = $this->settings->get('anjungan.panggil_cs_nomor') - 1;
          if($antrian == '-1') {
            echo '0';
          } else {
            echo $antrian;
          }
        break;
        case "get-skdp":
          if(!empty($_POST['no_rkm_medis'])){
              $data = array();
              $query = $this->db('skdp_bpjs')
                ->join('dokter', 'dokter.kd_dokter = skdp_bpjs.kd_dokter')
                ->join('booking_registrasi', 'booking_registrasi.tanggal_periksa = skdp_bpjs.tanggal_datang')
                ->join('poliklinik', 'poliklinik.kd_poli = booking_registrasi.kd_poli')
                ->join('pasien', 'pasien.no_rkm_medis = skdp_bpjs.no_rkm_medis')
                ->where('skdp_bpjs.no_rkm_medis', $_POST['no_rkm_medis'])
                ->where('booking_registrasi.kd_poli', $_POST['kd_poli'])
                ->desc('skdp_bpjs.tanggal_datang')
                ->oneArray();
              if(!empty($query)){
                  $data['status'] = 'ok';
                  $data['result'] = $query;
              }else{
                  $data['status'] = 'err';
                  $data['result'] = '';
              }
              echo json_encode($data);
          }
        break;

        case "get-daftar":
          if(!empty($_POST['no_rkm_medis_daftar'])){
              $data = array();
              $query = $this->db('pasien')
                ->where('no_rkm_medis', $_POST['no_rkm_medis_daftar'])
                ->oneArray();
              if(!empty($query)){
                  $data['status'] = 'ok';
                  $data['result'] = $query;
              }else{
                  $data['status'] = 'err';
                  $data['result'] = '';
              }
              echo json_encode($data);
          }
        break;

        case "get-poli":
          if(!empty($_POST['no_rkm_medis'])){
              $data = array();
              if($this->db('reg_periksa')->where('no_rkm_medis', $_POST['no_rkm_medis'])->where('tgl_registrasi', $_POST['tgl_registrasi'])->oneArray()) {
                $data['status'] = 'exist';
                $data['result'] = '';
                echo json_encode($data);
              } else {
                $tanggal = $_POST['tgl_registrasi'];
                $tentukan_hari = date('D',strtotime($tanggal));
                $day = array('Sun' => 'AKHAD', 'Mon' => 'SENIN', 'Tue' => 'SELASA', 'Wed' => 'RABU', 'Thu' => 'KAMIS', 'Fri' => 'JUMAT', 'Sat' => 'SABTU');
                $hari=$day[$tentukan_hari];
                $query = $this->db('jadwal')
                  ->select(['kd_poli' => 'jadwal.kd_poli'])
                  ->select(['nm_poli' => 'poliklinik.nm_poli'])
                  ->select(['jam_mulai' => 'jadwal.jam_mulai'])
                  ->select(['jam_selesai' => 'jadwal.jam_selesai'])
                  ->join('poliklinik', 'poliklinik.kd_poli = jadwal.kd_poli')
                  ->join('dokter', 'dokter.kd_dokter = jadwal.kd_dokter')
                  ->like('jadwal.hari_kerja', $hari)
                  ->toArray();
                if(!empty($query)){
                    $data['status'] = 'ok';
                    $data['result'] = $query;
                }else{
                    $data['status'] = 'err';
                    $data['result'] = '';
                }
                echo json_encode($data);
              }
          }
        break;
        case "get-dokter":
          if(!empty($_POST['kd_poli'])){
              $tanggal = $_POST['tgl_registrasi'];
              $tentukan_hari = date('D',strtotime($tanggal));
              $day = array('Sun' => 'AKHAD', 'Mon' => 'SENIN', 'Tue' => 'SELASA', 'Wed' => 'RABU', 'Thu' => 'KAMIS', 'Fri' => 'JUMAT', 'Sat' => 'SABTU');
              $hari=$day[$tentukan_hari];
              $data = array();
              $result = $this->db('jadwal')
                ->select(['kd_dokter' => 'jadwal.kd_dokter'])
                ->select(['nm_dokter' => 'dokter.nm_dokter'])
                ->select(['kuota' => 'jadwal.kuota'])
                ->join('poliklinik', 'poliklinik.kd_poli = jadwal.kd_poli')
                ->join('dokter', 'dokter.kd_dokter = jadwal.kd_dokter')
                ->where('jadwal.kd_poli', $_POST['kd_poli'])
                ->like('jadwal.hari_kerja', $hari)
                ->oneArray();
              $check_kuota = $this->db('reg_periksa')
                ->select(['count' => 'COUNT(DISTINCT no_rawat)'])
                ->where('kd_poli', $_POST['kd_poli'])
                ->where('tgl_registrasi', $_POST['tgl_registrasi'])
                ->oneArray();
              $curr_count = $check_kuota['count'];
              $curr_kuota = $result['kuota'];
              $online = $curr_kuota/2;
              if($curr_count > $online) {
                $data['status'] = 'limit';
              } else {
                $query = $this->db('jadwal')
                  ->select(['kd_dokter' => 'jadwal.kd_dokter'])
                  ->select(['nm_dokter' => 'dokter.nm_dokter'])
                  ->join('poliklinik', 'poliklinik.kd_poli = jadwal.kd_poli')
                  ->join('dokter', 'dokter.kd_dokter = jadwal.kd_dokter')
                  ->where('jadwal.kd_poli', $_POST['kd_poli'])
                  ->like('jadwal.hari_kerja', $hari)
                  ->toArray();
                if(!empty($query)){
                    $data['status'] = 'ok';
                    $data['result'] = $query;
                }else{
                    $data['status'] = 'err';
                    $data['result'] = '';
                }
                echo json_encode($data);
              }
          }
        break;
        case "get-namapoli":
          //$_POST['kd_poli'] = 'INT';
          if(!empty($_POST['kd_poli'])){
              $data = array();
              $result = $this->db('poliklinik')->where('kd_poli', $_POST['kd_poli'])->oneArray();
              if(!empty($result)){
                  $data['status'] = 'ok';
                  $data['result'] = $result;
              }else{
                  $data['status'] = 'err';
                  $data['result'] = '';
              }
              echo json_encode($data);
          }
        break;
        case "get-namadokter":
          //$_POST['kd_dokter'] = 'DR001';
          if(!empty($_POST['kd_dokter'])){
              $data = array();
              $result = $this->db('dokter')->where('kd_dokter', $_POST['kd_dokter'])->oneArray();
              if(!empty($result)){
                  $data['status'] = 'ok';
                  $data['result'] = $result;
              }else{
                  $data['status'] = 'err';
                  $data['result'] = '';
              }
              echo json_encode($data);
          }
        break;
        case "post-registrasi":
          if(!empty($_POST['no_rkm_medis'])){
              $data = array();
              $date = date('Y-m-d');

              $_POST['no_reg']     = $this->core->setNoReg($_POST['kd_dokter']);
              $_POST['hubunganpj'] = $this->core->getPasienInfo('keluarga', $_POST['no_rkm_medis']);
              $_POST['almt_pj']    = $this->core->getPasienInfo('alamat', $_POST['no_rkm_medis']);
              $_POST['p_jawab']    = $this->core->getPasienInfo('namakeluarga', $_POST['no_rkm_medis']);
              $_POST['stts']       = 'Belum';

              $cek_stts_daftar = $this->db('reg_periksa')->where('no_rkm_medis', $_POST['no_rkm_medis'])->count();
              $_POST['stts_daftar'] = 'Baru';
              if($cek_stts_daftar > 0) {
                $_POST['stts_daftar'] = 'Lama';
              }

              $biaya_reg = $this->db('poliklinik')->where('kd_poli', $_POST['kd_poli'])->oneArray();
              $_POST['biaya_reg'] = $biaya_reg['registrasi'];
              if($_POST['stts_daftar'] == 'Lama') {
                $_POST['biaya_reg'] = $biaya_reg['registrasilama'];
              }

              $cek_status_poli = $this->db('reg_periksa')->where('no_rkm_medis', $_POST['no_rkm_medis'])->where('kd_poli', $_POST['kd_poli'])->count();
              $_POST['status_poli'] = 'Baru';
              if($cek_status_poli > 0) {
                $_POST['status_poli'] = 'Lama';
              }

              $tanggal = new \DateTime($this->core->getPasienInfo('tgl_lahir', $_POST['no_rkm_medis']));
              $today = new \DateTime($date);
              $y = $today->diff($tanggal)->y;
              $m = $today->diff($tanggal)->m;
              $d = $today->diff($tanggal)->d;

              $umur="0";
              $sttsumur="Th";
              if($y>0){
                  $umur=$y;
                  $sttsumur="Th";
              }else if($y==0){
                  if($m>0){
                      $umur=$m;
                      $sttsumur="Bl";
                  }else if($m==0){
                      $umur=$d;
                      $sttsumur="Hr";
                  }
              }
              $_POST['umurdaftar'] = $umur;
              $_POST['sttsumur'] = $sttsumur;
              $_POST['status_lanjut']   = 'Ralan';
              $_POST['kd_pj']           = $this->settings->get('anjungan.carabayar_umum');
              $_POST['status_bayar']    = 'Belum Bayar';
              $_POST['no_rawat'] = $this->core->setNoRawat();

              $query = $this->db('reg_periksa')->save($_POST);

              $result = $this->db('reg_periksa')
                ->select('reg_periksa.no_rkm_medis')
                ->select('pasien.nm_pasien')
                ->select('pasien.alamat')
                ->select('reg_periksa.tgl_registrasi')
                ->select('reg_periksa.jam_reg')
                ->select('reg_periksa.no_rawat')
                ->select('reg_periksa.no_reg')
                ->select('poliklinik.nm_poli')
                ->select('dokter.nm_dokter')
                ->select('reg_periksa.status_lanjut')
                ->select('penjab.png_jawab')
                ->join('poliklinik', 'poliklinik.kd_poli = reg_periksa.kd_poli')
                ->join('dokter', 'dokter.kd_dokter = reg_periksa.kd_dokter')
                ->join('penjab', 'penjab.kd_pj = reg_periksa.kd_pj')
                ->join('pasien', 'pasien.no_rkm_medis = reg_periksa.no_rkm_medis')
                ->where('reg_periksa.tgl_registrasi', $_POST['tgl_registrasi'])
                ->where('reg_periksa.no_rkm_medis', $_POST['no_rkm_medis'])
                ->oneArray();

              if(!empty($result)){
                  $data['status'] = 'ok';
                  $data['result'] = $result;
              }else{
                  $data['status'] = 'err';
                  $data['result'] = '';
              }
              echo json_encode($data);
          }
        break;
      }
      exit();
    }
}
