<?php

namespace Plugins\laporan_bpjs;

use Systems\AdminModule;

class Admin extends AdminModule
{

    public function navigation()
    {
        return [
            'Kelola'   => 'manage',
        ];
    }

    public function getManage()
    {
        $this->_addHeaderFiles();
        return $this->draw('manage.html');
    }

    public function postData()
    {
        $draw = $_POST['draw'];
        $row1 = $_POST['start'];
        $rowperpage = $_POST['length']; // Rows display per page
        $columnIndex = $_POST['order'][0]['column']; // Column index
        $columnName = $_POST['columns'][$columnIndex]['data']; // Column name
        $columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
        $searchValue = $_POST['search']['value']; // Search value

        ## Custom Field value
        $search_field_mlite_antrian_referensi = $_POST['search_field_mlite_antrian_referensi'];
        $search_text_mlite_antrian_referensi = $_POST['search_text_mlite_antrian_referensi'];

        $searchQuery = " ";
        if ($search_text_mlite_antrian_referensi != '') {
            $searchQuery .= " and (" . $search_field_mlite_antrian_referensi . " like '%" . $search_text_mlite_antrian_referensi . "%' ) ";
        }

        ## Total number of records without filtering
        $sel = $this->db()->pdo()->prepare("select count(*) as allcount from mlite_antrian_referensi");
        $sel->execute();
        $records = $sel->fetch();
        $totalRecords = $records['allcount'];

        ## Total number of records with filtering
        $sel = $this->db()->pdo()->prepare("select count(*) as allcount from mlite_antrian_referensi WHERE 1 " . $searchQuery);
        $sel->execute();
        $records = $sel->fetch();
        $totalRecordwithFilter = $records['allcount'];

        ## Fetch records
        $sel = $this->db()->pdo()->prepare("select * from mlite_antrian_referensi WHERE 1 " . $searchQuery . " order by " . $columnName . " " . $columnSortOrder . " limit " . $row1 . "," . $rowperpage);
        $sel->execute();
        $result = $sel->fetchAll(\PDO::FETCH_ASSOC);

        $data = array();
        foreach ($result as $row) {
            $no_rawat = $this->db('reg_periksa')->select('no_rawat')->where('no_rkm_medis',$row['no_rkm_medis'])->where('tgl_registrasi',$row['tanggal_periksa'])->oneArray();
            $data[] = array(
                'tanggal_periksa' => $row['tanggal_periksa'],
                'no_rkm_medis' => $row['no_rkm_medis'],
                'nm_pasien' => $this->core->getPasienInfo('nm_pasien',$row['no_rkm_medis']),
                'nm_poli' => $this->core->getPoliklinikInfo('nm_poli',$this->core->getRegPeriksaInfo('kd_poli',$no_rawat['no_rawat'])),
                'nomor_kartu' => $row['nomor_kartu'],
                'nomor_referensi' => $row['nomor_referensi'],
                'kodebooking' => $row['kodebooking'],
                'jenis_kunjungan' => $row['jenis_kunjungan'],
                'status_kirim' => $row['status_kirim'],
                'keterangan' => $row['keterangan']

            );
        }

        ## Response
        $response = array(
            "draw" => intval($draw),
            "iTotalRecords" => $totalRecords,
            "iTotalDisplayRecords" => $totalRecordwithFilter,
            "aaData" => $data
        );

        echo json_encode($response);
        exit();
    }

    public function postAksi()
    {
        if (isset($_POST['typeact'])) {
            $act = $_POST['typeact'];
        } else {
            $act = '';
        }

        if ($act == 'add') {

            $tanggal_periksa = $_POST['tanggal_periksa'];
            $no_rkm_medis = $_POST['no_rkm_medis'];
            $nomor_kartu = $_POST['nomor_kartu'];
            $nomor_referensi = $_POST['nomor_referensi'];
            $kodebooking = $_POST['kodebooking'];
            $jenis_kunjungan = $_POST['jenis_kunjungan'];
            $status_kirim = $_POST['status_kirim'];
            $keterangan = $_POST['keterangan'];


            $mlite_antrian_referensi_add = $this->db()->pdo()->prepare('INSERT INTO mlite_antrian_referensi VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
            $mlite_antrian_referensi_add->execute([$tanggal_periksa, $no_rkm_medis, $nomor_kartu, $nomor_referensi, $kodebooking, $jenis_kunjungan, $status_kirim, $keterangan]);
        }
        if ($act == "edit") {

            $tanggal_periksa = $_POST['tanggal_periksa'];
            $no_rkm_medis = $_POST['no_rkm_medis'];
            $nomor_kartu = $_POST['nomor_kartu'];
            $nomor_referensi = $_POST['nomor_referensi'];
            $kodebooking = $_POST['kodebooking'];
            $jenis_kunjungan = $_POST['jenis_kunjungan'];
            $status_kirim = $_POST['status_kirim'];
            $keterangan = $_POST['keterangan'];


            // BUANG FIELD PERTAMA

            $mlite_antrian_referensi_edit = $this->db()->pdo()->prepare("UPDATE mlite_antrian_referensi SET tanggal_periksa=?, no_rkm_medis=?, nomor_kartu=?, nomor_referensi=?, kodebooking=?, jenis_kunjungan=?, status_kirim=?, keterangan=? WHERE tanggal_periksa=?");
            $mlite_antrian_referensi_edit->execute([$tanggal_periksa, $no_rkm_medis, $nomor_kartu, $nomor_referensi, $kodebooking, $jenis_kunjungan, $status_kirim, $keterangan, $tanggal_periksa]);
        }

        if ($act == "del") {
            $tanggal_periksa = $_POST['tanggal_periksa'];
            $check_db = $this->db()->pdo()->prepare("DELETE FROM mlite_antrian_referensi WHERE tanggal_periksa='$tanggal_periksa'");
            $result = $check_db->execute();
            $error = $check_db->errorInfo();
            if (!empty($result)) {
                $data = array(
                    'status' => 'success',
                    'msg' => $no_rkm_medis
                );
            } else {
                $data = array(
                    'status' => 'error',
                    'msg' => $error['2']
                );
            }
            echo json_encode($data);
        }

        if ($act == "lihat") {

            $search_field_mlite_antrian_referensi = $_POST['search_field_mlite_antrian_referensi'];
            $search_text_mlite_antrian_referensi = $_POST['search_text_mlite_antrian_referensi'];

            $searchQuery = " ";
            if ($search_text_mlite_antrian_referensi != '') {
                $searchQuery .= " and (" . $search_field_mlite_antrian_referensi . " like '%" . $search_text_mlite_antrian_referensi . "%' ) ";
            }

            $user_lihat = $this->db()->pdo()->prepare("SELECT * from mlite_antrian_referensi WHERE 1 " . $searchQuery);
            $user_lihat->execute();
            $result = $user_lihat->fetchAll(\PDO::FETCH_ASSOC);

            $data = array();

            foreach ($result as $row) {
                $data[] = array(
                    'tanggal_periksa' => $row['tanggal_periksa'],
                    'no_rkm_medis' => $row['no_rkm_medis'],
                    'nomor_kartu' => $row['nomor_kartu'],
                    'nomor_referensi' => $row['nomor_referensi'],
                    'kodebooking' => $row['kodebooking'],
                    'jenis_kunjungan' => $row['jenis_kunjungan'],
                    'status_kirim' => $row['status_kirim'],
                    'keterangan' => $row['keterangan']
                );
            }

            echo json_encode($data);
        }
        exit();
    }

    public function getDetail($tanggal_periksa)
    {
        $detail = $this->db('mlite_antrian_referensi')->where('tanggal_periksa', $tanggal_periksa)->toArray();
        echo $this->draw('detail.html', ['detail' => $detail]);
        exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES . '/laporan_bpjs/css/admin/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES . '/laporan_bpjs/js/admin/scripts.js', ['settings' => $settings]);
        exit();
    }

    private function _addHeaderFiles()
    {
        $this->core->addCSS(url('assets/css/datatables.min.css'));
        $this->core->addJS(url('assets/jscripts/jqueryvalidation.js'));
        $this->core->addJS(url('assets/jscripts/xlsx.js'));
        $this->core->addJS(url('assets/jscripts/jspdf.min.js'));
        $this->core->addJS(url('assets/jscripts/jspdf.plugin.autotable.min.js'));
        $this->core->addJS(url('assets/jscripts/datatables.min.js'));

        $this->core->addCSS(url([ADMIN, 'laporan_bpjs', 'css']));
        $this->core->addJS(url([ADMIN, 'laporan_bpjs', 'javascript']), 'footer');
    }
}
