<?php

namespace Plugins\Master\Src;

use Systems\Lib\QueryWrapper;


class JnsPerawatanLab
{

    protected function db($table)
    {
        return new QueryWrapper($table);
    }

    public function getIndex()
    {

      $totalRecords = $this->db('jns_perawatan_lab')
        ->where('status', '1')
        ->select('kd_jenis_prw')
        ->toArray();
      $offset         = 20;
      $return['halaman']    = 1;
      $return['jml_halaman']    = ceil(count($totalRecords) / $offset);
      $return['jumlah_data']    = count($totalRecords);

      $return['list'] = $this->db('jns_perawatan_lab')
        ->join('penjab', 'penjab.kd_pj=jns_perawatan_lab.kd_pj')
        ->where('jns_perawatan_lab.status', '1')
        ->desc('kd_jenis_prw')
        ->limit(20)
        ->toArray();

      return $return;

    }

    public function anyForm()
    {
        $return['penjab'] = $this->db('penjab')->where('status', '1')->toArray();
        $return['kelas'] = ['Kelas 1','Kelas 2','Kelas 3','Kelas Utama','Kelas VIP','Kelas VVIP'];
        $return['kategori'] = ['PA','PK'];
        if (isset($_POST['kd_jenis_prw'])){
          $return['form'] = $this->db('jns_perawatan_lab')->where('kd_jenis_prw', $_POST['kd_jenis_prw'])->oneArray();
        } else {
          $return['form'] = [
            'kd_jenis_prw' => '',
            'nm_perawatan' => '',
            'bagian_rs' => 0,
            'bhp' => 0,
            'tarif_perujuk' =>0,
            'tarif_tindakan_dokter' => 0,
            'tarif_tindakan_petugas' => 0,
            'kso' => 0,
            'menejemen' => 0,
            'total_byr' => 0,
            'kd_pj' => '',
            'status' => '',
            'kelas' => '',
            'kategori' => ''
          ];
        }

        return $return;
    }

    public function anyTemplateLaboratorium()
    {

      $return['kd_jenis_prw'] = $_POST['kd_jenis_prw'];
      $return['list'] = $this->db('template_laboratorium')
        ->where('kd_jenis_prw', $_POST['kd_jenis_prw'])
        ->toArray();

      return $return;

    }

    public function anyDisplay()
    {

        $perpage = '20';
        $totalRecords = $this->db('jns_perawatan_lab')
          ->where('status', '1')
          ->select('kd_jenis_prw')
          ->toArray();
        $offset         = 20;
        $return['halaman']    = 1;
        $return['jml_halaman']    = ceil(count($totalRecords) / $offset);
        $return['jumlah_data']    = count($totalRecords);

        $return['list'] = $this->db('jns_perawatan_lab')
          ->join('penjab', 'penjab.kd_pj=jns_perawatan_lab.kd_pj')
          ->where('jns_perawatan_lab.status', '1')
          ->desc('kd_jenis_prw')
          ->offset(0)
          ->limit($perpage)
          ->toArray();

        if(isset($_POST['cari'])) {
          $return['list'] = $this->db('jns_perawatan_lab')
            ->join('penjab', 'penjab.kd_pj=jns_perawatan_lab.kd_pj')
            ->where('jns_perawatan_lab.status', '1')
            ->like('nm_perawatan', '%'.$_POST['cari'].'%')
            ->desc('kd_jenis_prw')
            ->offset(0)
            ->limit($perpage)
            ->toArray();
          $jumlah_data = count($return['list']);
          $jml_halaman = ceil($jumlah_data / $offset);
        }
        if(isset($_POST['halaman'])){
          $offset     = (($_POST['halaman'] - 1) * $perpage);
          $return['list'] = $this->db('jns_perawatan_lab')
            ->join('penjab', 'penjab.kd_pj=jns_perawatan_lab.kd_pj')
            ->where('jns_perawatan_lab.status', '1')
            ->desc('kd_jenis_prw')
            ->offset($offset)
            ->limit($perpage)
            ->toArray();
          $return['halaman'] = $_POST['halaman'];
        }

        return $return;
    }

    public function postSave()
    {
      if (!$this->db('jns_perawatan_lab')->where('kd_jenis_prw', $_POST['kd_jenis_prw'])->oneArray()) {
        $query = $this->db('jns_perawatan_lab')->save($_POST);
      } else {
        $query = $this->db('jns_perawatan_lab')->where('kd_jenis_prw', $_POST['kd_jenis_prw'])->save($_POST);
      }
      return $query;
    }

    public function postHapus()
    {
      return $this->db('jns_perawatan_lab')->where('kd_jenis_prw', $_POST['kd_jenis_prw'])->delete();
    }

    public function postMaxId()
    {
      $max_id = $this->db('jns_perawatan_lab')->select(['kd_jenis_prw' => 'ifnull(MAX(CONVERT(RIGHT(kd_jenis_prw,3),signed)),0)'])->oneArray();
      if(empty($max_id['kd_jenis_prw'])) {
        $max_id['kd_jenis_prw'] = '000';
      }
      $_next_max_id = sprintf('%03s', ($max_id['kd_jenis_prw'] + 1));
      $next_max_id = 'LAB'.$_next_max_id;
      echo $next_max_id;
      exit();
    }

    public function postData()
    {
        $draw = $_POST['draw'] ?? 0;
        $row1 = $_POST['start'] ?? 0;
        $rowperpage = $_POST['length'] ?? 10;
        $columnIndex = $_POST['order'][0]['column'] ?? 0;
        $columnName = $_POST['columns'][$columnIndex]['data'] ?? 'kd_jenis_prw';
        $columnSortOrder = $_POST['order'][0]['dir'] ?? 'asc';
        $searchValue = $_POST['search']['value'] ?? '';

        $search_field = $_POST['search_field_jns_perawatan_lab'] ?? '';
        $search_text = $_POST['search_text_jns_perawatan_lab'] ?? '';

        $searchQuery = "";
        if (!empty($search_text)) {
            $searchQuery .= " AND (" . $search_field . " LIKE :search_text) ";
        }

        $stmt = $this->db()->pdo()->prepare("SELECT COUNT(*) AS allcount FROM jns_perawatan_lab");
        $stmt->execute();
        $records = $stmt->fetch();
        $totalRecords = $records['allcount'];

        $stmt = $this->db()->pdo()->prepare("SELECT COUNT(*) AS allcount FROM jns_perawatan_lab WHERE 1=1 $searchQuery");
        if (!empty($search_text)) {
            $stmt->bindValue(':search_text', "%$search_text%", \PDO::PARAM_STR);
        }
        $stmt->execute();
        $records = $stmt->fetch();
        $totalRecordwithFilter = $records['allcount'];

        $sql = "SELECT * FROM jns_perawatan_lab WHERE 1=1 $searchQuery ORDER BY $columnName $columnSortOrder LIMIT $row1, $rowperpage";
        $stmt = $this->db()->pdo()->prepare($sql);
        if (!empty($search_text)) {
            $stmt->bindValue(':search_text', "%$search_text%", \PDO::PARAM_STR);
        }
        $stmt->execute();
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $data = [];
        foreach ($result as $row) {
            $data[] = [
                'kd_jenis_prw'=>$row['kd_jenis_prw'],
                'nm_perawatan'=>$row['nm_perawatan'],
                'bagian_rs'=>$row['bagian_rs'],
                'bhp'=>$row['bhp'],
                'tarif_perujuk'=>$row['tarif_perujuk'],
                'tarif_tindakan_dokter'=>$row['tarif_tindakan_dokter'],
                'tarif_tindakan_petugas'=>$row['tarif_tindakan_petugas'],
                'kso'=>$row['kso'],
                'menejemen'=>$row['menejemen'],
                'total_byr'=>$row['total_byr'],
                'kd_pj'=>$row['kd_pj'],
                'status'=>$row['status'],
                'kelas'=>$row['kelas'],
                'kategori'=>$row['kategori']
            ];
        }

        echo json_encode([
            "draw" => intval($draw),
            "iTotalRecords" => $totalRecords,
            "iTotalDisplayRecords" => $totalRecordwithFilter,
            "aaData" => $data
        ]);
        exit();
    }

}
