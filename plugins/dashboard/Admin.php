<?php

namespace Plugins\Dashboard;

use Systems\AdminModule;

class Admin extends AdminModule
{

    public function navigation()
    {
        return [
            'Manajemen'   => 'main'
        ];
    }

    public function getMain(){ 

        $waktu = gmdate("H:i",time()+8*3600);
        $t = explode(":",$waktu);
        $jam = $t[0];
        $menit = $t[1];

        $this->assign['salam']="Assalamualaikum....";
        
        if ($jam >= 00 and $jam < 10 ){
            if ($menit >00 and $menit<60){
                $this->assign['salam']="Selamat Pagi";
            }
        }else if ($jam >= 10 and $jam < 15 ){
            if ($menit >00 and $menit<60){
                $this->assign['salam']="Selamat Siang";
            }
        }else if ($jam >= 15 and $jam < 18 ){
            if ($menit >00 and $menit<60){
                $this->assign['salam']="Selamat Sore";
            }
        }else if ($jam >= 18 and $jam <= 24 ){
            if ($menit >00 and $menit<60){
                $this->assign['salam']="Selamat Malam";
            }
        }else {
            $this->assign['salam']="Assalamualaikum....";
        }

        $this->assign['user'] = $this->core->db->get('mlite_users', '*', ['id' => $_SESSION['mlite_user']]);

        $this->core->addJS(url(['assets/vendor/apex/apexcharts.min.js']), 'footer');
        if($this->core->db->get('mlite_users', 'role', ['id' => $_SESSION['mlite_user']]) == 'medis') {

            $this->assign['count_reg_periksa'] = $this->core->db->count('reg_periksa', 'no_rawat', ['tgl_registrasi' => date('Y-m-d'), 'kd_dokter' => $this->core->db->get('mlite_users', 'username', ['id' => $_SESSION['mlite_user']])]);
            $this->assign['count_booking_operasi'] = $this->core->db->count('booking_operasi', 'no_rawat', ['tanggal' => date('Y-m-d'), 'kd_dokter' => $this->core->db->get('mlite_users', 'username', ['id' => $_SESSION['mlite_user']])]);
            
            $this->assign['dokter'] = array_chunk($this->core->db->rand('dokter', [
                '[>]pegawai' => ['kd_dokter' => 'nik']
            ], [
                'kd_dokter', 
                'nm_dokter', 
                'bidang', 
                'photo'
            ], [
                'pegawai.stts_aktif' => 'Aktif', 
                'LIMIT' => 10
            ]), 2);

            $this->assign['booking_operasi'] = array_chunk($this->core->db->rand('booking_operasi', [
                '[>]reg_periksa' => ['no_rawat' => 'no_rawat'], 
                '[>]pasien' => ['reg_periksa.no_rkm_medis' => 'no_rkm_medis'], 
                '[>]paket_operasi' => ['kode_paket' => 'kode_paket']
            ], [
                'pasien.nm_pasien', 
                'paket_operasi.nm_perawatan', 
                'tanggal', 
                'jam_mulai', 
                'jam_selesai'
            ], [
                'booking_operasi.status[!]' => 'Selesai', 
                'LIMIT' => 10
            ]), 2);

            $cap = $this->core->db->get('mlite_users', 'cap', ['id' => $_SESSION['mlite_user']]);

            $this->assign['pendaftaran_pasien'] = $this->core->db->select('reg_periksa', [
                '[>]pasien' => ['no_rkm_medis' => 'no_rkm_medis'], 
                '[>]poliklinik' => ['kd_poli' => 'kd_poli'], 
                '[>]penjab' => ['kd_pj' => 'kd_pj'],
                '[>]pegawai' => ['kd_dokter' => 'nik']
            ],[
                'no_rawat',
                'no_reg',  
                'nm_pasien', 
                'umurdaftar', 
                'sttsumur', 
                'nm_poli', 
                'png_jawab', 
                'nama', 
                'photo'
            ],[
                'tgl_registrasi' => date('Y-m-d'), 
                'reg_periksa.kd_poli' => explode(',', $cap), 
                'LIMIT' => 10
            ]);

            $this->core->addJS(url(['assets/vendor/rating/raty.js']), 'footer');
            $this->core->addJS(url(['dashboard', 'dokterjavascript']), 'footer');

            return $this->draw('dokter.html', ['dashboard' => $this->assign]);

        } else {

            $this->assign['count_reg_periksa'] = $this->core->db->count('reg_periksa', 'no_rawat', ['tgl_registrasi' => date('Y-m-d')]);
            $this->assign['count_kamar_inap'] = $this->core->db->count('kamar_inap', 'no_rawat', ['tgl_masuk' => date('Y-m-d')]);
            $this->assign['count_booking_operasi'] = $this->core->db->count('booking_operasi', 'no_rawat', ['tanggal' => date('Y-m-d')]);
            $this->assign['count_pasien_pulang'] = $this->core->db->count('kamar_inap', 'no_rawat', ['tgl_keluar' => date('Y-m-d')]);

            $kamar = $this->core->db->select('kamar', '*', [
                'GROUP' => 'kd_bangsal'
            ]);
            $this->assign['kamar'] = [];
            foreach($kamar as $row) {
                $row['nm_bangsal'] = $this->core->db->get('bangsal', 'nm_bangsal', ['kd_bangsal' => $row['kd_bangsal']]);
                $row['isi'] = $this->core->db->count('kamar', '*', ['kd_bangsal' => $row['kd_bangsal'], 'status' => 'ISI']);
                $row['kosong'] = $this->core->db->count('kamar', '*', ['kd_bangsal' => $row['kd_bangsal'], 'status' => 'KOSONG']);
                $this->assign['kamar'][] = $row;
            }

            $this->core->addJS(url(['dashboard', 'javascript']), 'footer');

            return $this->draw('manage.html', ['dashboard' => $this->assign]);
        }

    }

    public function getDokterJavascript()
    {
        header('Content-type: text/javascript');
        $result = [];
        for ($i=0 ; $i < 7 ;$i++)
        {
            $row['date']=date('Y-m-d',strtotime("+{$i} day",strtotime('-1 week', strtotime(date('Y-m-d')))));
            $row['day'] = \Carbon\Carbon::parse($row['date'])->locale('id')->shortDayName;
            $row['reg_periksa'] = $this->core->db->count('reg_periksa', 'no_rawat', ['tgl_registrasi' => $row['date']]);
            $result[] = $row;
        }
        $activity['date'] = implode(',', array_column($result, 'date'));
        $activity['day'] = implode('","', array_column($result, 'day')); 
        $activity['reg_periksa'] = implode(',', array_column($result, 'reg_periksa'));
        echo $this->draw(MODULES.'/dashboard/js/dokter.js', ['activity' =>  $activity]);
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/dashboard/js/scripts.js');
        exit();
    }
    
    public function getSearch()
    {
        $term = '%' . $_GET['term'] . '%';

        $result = $this->core->db->select('pasien', [
            'no_rkm_medis', 
            'nm_pasien'
        ], [
            'AND' => [
                'OR' => [
                    'no_rkm_medis[~]' => $term, 
                    'nm_pasien[~]' => $term
                ]
            ], 
            'LIMIT' => 2
        ]);

        $data = array();
        foreach($result as $row) {
            $data[] = $row;
        }
    
        echo json_encode($data);
        exit();   
    }

    public function getHelp($dir)
    {

      $module = $this->core->getModuleInfo($dir);
      $module['description'] = $this->tpl->noParse($module['description']);
      $module['help'] = $this->tpl->noParse($module['help']);
    
      $this->tpl->set('module', $module);
      echo $this->tpl->draw(MODULES . '/dashboard/view/help.html', true);
      exit();
    }

    public function getTest()
    {

        // $cap = $this->core->db->get('mlite_users', 'cap', ['id' => $_SESSION['mlite_user']]);

        // $this->assign['pendaftaran_pasien'] = $this->core->db->select('reg_periksa', [
        //     '[>]pasien' => ['no_rkm_medis' => 'no_rkm_medis'], 
        //     '[>]poliklinik' => ['kd_poli' => 'kd_poli'], 
        //     '[>]penjab' => ['kd_pj' => 'kd_pj'],
        //     '[>]pegawai' => ['kd_dokter' => 'nik']
        // ],[
        //     'no_rawat',
        //     'no_reg',  
        //     'nm_pasien', 
        //     'umurdaftar', 
        //     'sttsumur', 
        //     'nm_poli', 
        //     'png_jawab', 
        //     'nama', 
        //     'photo'
        // ],[
        //     'tgl_registrasi' => date('2024-08-07'), 
        //     'reg_periksa.kd_poli' => explode(',', $cap), 
        //     'LIMIT' => 10
        // ]);


        // $this->assign['dokter'] = $this->core->db->rand('dokter', [
        //     '[>]pegawai' => ['kd_dokter' => 'nik']
        // ], [
        //     'kd_dokter', 
        //     'nm_dokter', 
        //     'bidang', 
        //     'photo'
        // ], [
        //     'pegawai.stts_aktif' => 'Aktif', 
        //     'LIMIT' => 4
        // ]);

        // $array_chunk = array_chunk($this->assign['dokter'], 2);

        // echo json_encode($array_chunk, JSON_PRETTY_PRINT);        
        
        $result = [];
        foreach (glob(MODULES.'/*', GLOB_ONLYDIR) as $dir) {
            $dir = basename($dir);
            $result[] = $dir;
        }

        $dbModules = array_column($this->core->db->select('mlite_modules', '*', [
            "AND" => [
                "dir[!]" => $result,
            ]            
        ]), 'dir');

        $dir = implode("','",$result);
        $get_table = $this->core->db->pdo->prepare("SELECT DISTINCT TABLE_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='mlite.io' AND TABLE_NAME NOT IN ('$dir')");
	    $get_table->execute();
	    $result = $get_table->fetchAll();

        echo count($result);
        // echo json_encode($result, JSON_PRETTY_PRINT);        
        exit();
    }

}