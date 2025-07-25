<?php

namespace Plugins\Master\Src;

use Systems\Lib\QueryWrapper;

class Poliklinik
{

    protected $core;

    public function __construct($core)
    {
        $this->core = $core;
                
    }

    protected function db($table)
    {
        return new QueryWrapper($table);
    }

    public function getIndex()
    {

      $totalRecords = $this->db('poliklinik')
        ->select('kd_poli')
        ->toArray();
      $offset         = 10;
      $return['halaman']    = 1;
      $return['jml_halaman']    = ceil(count($totalRecords) / $offset);
      $return['jumlah_data']    = count($totalRecords);

      $return['list'] = $this->db('poliklinik')
        ->desc('kd_poli')
        ->limit(10)
        ->toArray();

      return $return;

    }

    public function anyForm()
    {
        if (isset($_POST['kd_poli'])){
          $return['form'] = $this->db('poliklinik')->where('kd_poli', $_POST['kd_poli'])->oneArray();
        } else {
          $return['form'] = [
            'kd_poli' => '',
            'nm_poli' => '',
            'registrasi' => '',
            'registrasilama' => '',
            'status' => ''
          ];
        }

        return $return;
    }

    public function anyDisplay()
    {

        $perpage = '10';
        $totalRecords = $this->db('poliklinik')
          ->select('kd_poli')
          ->toArray();
        $offset         = 10;
        $return['halaman']    = 1;
        $return['jml_halaman']    = ceil(count($totalRecords) / $offset);
        $return['jumlah_data']    = count($totalRecords);

        $return['list'] = $this->db('poliklinik')
          ->desc('kd_poli')
          ->offset(0)
          ->limit($perpage)
          ->toArray();

        if(isset($_POST['cari'])) {
          $return['list'] = $this->db('poliklinik')
            ->like('kd_poli', '%'.$_POST['cari'].'%')
            ->orLike('nm_poli', '%'.$_POST['cari'].'%')
            ->desc('kd_poli')
            ->offset(0)
            ->limit($perpage)
            ->toArray();
          $jumlah_data = count($return['list']);
          $jml_halaman = ceil($jumlah_data / $offset);
        }
        if(isset($_POST['halaman'])){
          $offset     = (($_POST['halaman'] - 1) * $perpage);
          $return['list'] = $this->db('poliklinik')
            ->desc('kd_poli')
            ->offset($offset)
            ->limit($perpage)
            ->toArray();
          $return['halaman'] = $_POST['halaman'];
        }

        return $return;
    }

    public function postSave()
    {
      if (!$this->db('poliklinik')->where('kd_poli', $_POST['kd_poli'])->oneArray()) {
        $query = $this->db('poliklinik')->save($_POST);
      } else {
        $query = $this->db('poliklinik')->where('kd_poli', $_POST['kd_poli'])->save($_POST);
      }
      return $query;
    }

    public function postHapus()
    {
      return $this->db('poliklinik')->where('kd_poli', $_POST['kd_poli'])->delete();
    }

    public function postData()
    {
        $draw = $_POST['draw'] ?? 0;
        $row1 = $_POST['start'] ?? 0;
        $rowperpage = $_POST['length'] ?? 10;
        $columnIndex = $_POST['order'][0]['column'] ?? 0;
        $columnName = $_POST['columns'][$columnIndex]['data'] ?? 'kd_poli';
        $columnSortOrder = $_POST['order'][0]['dir'] ?? 'asc';
        $searchValue = $_POST['search']['value'] ?? '';
    
        $search_field = $_POST['search_field_poliklinik'] ?? '';
        $search_text = $_POST['search_text_poliklinik'] ?? '';
    
        $searchQuery = "";
        $params = [];
    
        // ✅ Periksa dengan isset + !== '' agar '0' tidak dianggap kosong
        if (isset($search_text) && $search_text !== '') {
            if ($search_field === 'status') {
                // untuk field status yang tipenya integer atau tinyint
                $searchQuery .= " AND $search_field = :search_text ";
                $params[':search_text'] = (int)$search_text; // convert ke integer
            } else {
                $searchQuery .= " AND $search_field LIKE :search_text ";
                $params[':search_text'] = "%$search_text%";
            }
        }
    
        // Total record tanpa filter
        $stmt = $this->core->db()->pdo()->prepare("SELECT COUNT(*) AS allcount FROM poliklinik");
        $stmt->execute();
        $records = $stmt->fetch();
        $totalRecords = $records['allcount'];
    
        // Total record dengan filter
        $stmt = $this->core->db()->pdo()->prepare("SELECT COUNT(*) AS allcount FROM poliklinik WHERE 1=1 $searchQuery");
        $stmt->execute($params);
        $records = $stmt->fetch();
        $totalRecordwithFilter = $records['allcount'];
    
        // Data paginated
        $sql = "SELECT * FROM poliklinik WHERE 1=1 $searchQuery ORDER BY $columnName $columnSortOrder LIMIT $row1, $rowperpage";
        $stmt = $this->core->db()->pdo()->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    
        $data = [];
        foreach ($result as $row) {
            $data[] = [
                'kd_poli'        => $row['kd_poli'],
                'nm_poli'        => $row['nm_poli'],
                'registrasi'     => $row['registrasi'],
                'registrasilama' => $row['registrasilama'],
                'status'         => $row['status']
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

    public function postAksi()
    {
        $act = $_POST['typeact'] ?? '';

        if (!in_array($act, ['add', 'edit', 'del', 'lihat'])) {
            echo json_encode(["status" => "error", "message" => "Aksi tidak dikenali."]);
            exit();
        }

        try {
            if ($act == 'add') {
                $kd_poli = $_POST['kd_poli'];
                $nm_poli = $_POST['nm_poli'];
                $registrasi = $_POST['registrasi'];
                $registrasilama = $_POST['registrasilama'];
                $status = $_POST['status'];

                $sql = "INSERT INTO poliklinik VALUES (?, ?, ?, ?, ?)";
                $binds = [$kd_poli, $nm_poli, $registrasi, $registrasilama, $status];
                $stmt = $this->core->db()->pdo()->prepare($sql);
                $stmt->execute($binds);

                if($this->core->settings->get('settings.log_query') == 'ya') {
                  \Systems\Lib\QueryWrapper::logPdoQuery($sql, $binds);
                }

                echo json_encode(["status" => "success", "message" => "Data berhasil ditambahkan."]);

            } elseif ($act == 'edit') {
                $kd_poli = $_POST['kd_poli'];
                $nm_poli = $_POST['nm_poli'];
                $registrasi = $_POST['registrasi'];
                $registrasilama = $_POST['registrasilama'];
                $status = $_POST['status'];

                $sql = "UPDATE poliklinik SET kd_poli=?, nm_poli=?, registrasi=?, registrasilama=?, status=? WHERE kd_poli=?";
                $binds = [$kd_poli, $nm_poli, $registrasi, $registrasilama, $status, $kd_poli];
                $stmt = $this->core->db()->pdo()->prepare($sql);
                $stmt->execute($binds);

                if($this->core->settings->get('settings.log_query') == 'ya') {
                  \Systems\Lib\QueryWrapper::logPdoQuery($sql, $binds);
                }

                echo json_encode(["status" => "success", "message" => "Data berhasil diperbarui."]);

            } elseif ($act == 'del') {
                $kd_poli= $_POST['kd_poli'];
                $binds = [];
                $sql = "DELETE FROM poliklinik WHERE kd_poli='$kd_poli'";
                $stmt = $this->core->db()->pdo()->prepare($sql);
                $stmt->execute();

                if($this->core->settings->get('settings.log_query') == 'ya') {
                  \Systems\Lib\QueryWrapper::logPdoQuery($sql, $binds);
                }

                if ($stmt->rowCount() > 0) {
                    echo json_encode(["status" => "success", "message" => "Data berhasil dihapus."]);
                } else {
                    echo json_encode(["status" => "error", "message" => "Data tidak ditemukan atau gagal dihapus."]);
                }

            } elseif ($act == 'lihat') {
              $search_field = $_POST['search_field_poliklinik'] ?? '';
              $search_text  = $_POST['search_text_poliklinik'] ?? '';
              
              $searchQuery = "";
              $params = [];
              
              // Periksa apakah search_text diset dan tidak kosong secara literal
              if (isset($search_text) && $search_text !== '') {
                  if ($search_field === 'status') {
                      $searchQuery .= " AND $search_field = :search_text ";
                      $params[':search_text'] = (int)$search_text; // casting ke int untuk status (TINYINT)
                  } else {
                      $searchQuery .= " AND $search_field LIKE :search_text ";
                      $params[':search_text'] = "%$search_text%";
                  }
              }
              
              // Siapkan dan jalankan query
              $stmt = $this->core->db()->pdo()->prepare("SELECT * FROM poliklinik WHERE 1=1 $searchQuery");
              $stmt->execute($params);
              $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
              
              // Format data untuk dikembalikan ke frontend
              $data = [];
              foreach ($result as $row) {
                  $data[] = [
                      'kd_poli'        => $row['kd_poli'],
                      'nm_poli'        => $row['nm_poli'],
                      'registrasi'     => $row['registrasi'],
                      'registrasilama' => $row['registrasilama'],
                      'status'         => $row['status']
                  ];
              }
              
              echo json_encode($data);
              
            }
        } catch (\PDOException $e) {
          if($this->core->settings->get('settings.log_query') == 'ya') {
            if (in_array($act, ['add', 'edit', 'del'])) {
              \Systems\Lib\QueryWrapper::logPdoQuery($sql, $binds, $e->getMessage());   
            }
          }

            echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        }

        exit();
    }

    public function getChart($type = '', $column = '')
    {
        if ($type == '') {
            $type = 'pie';
        }

        $labels = $this->db('poliklinik')->select('status')->group('status')->toArray();
        $labels = json_encode(array_column($labels, 'status'));
        $datasets = $this->db('poliklinik')->select('COUNT(status)')->group('status')->toArray();
        $datasets = json_encode(array_column($datasets, 'COUNT(status)'));

        if (!empty($column)) {
            $labels = $this->db('poliklinik')->select($column)->group($column)->toArray();
            $labels = json_encode(array_column($labels, $column));
            $datasets = $this->db('poliklinik')->select("COUNT($column)")->group($column)->toArray();
            $datasets = json_encode(array_column($datasets, "COUNT($column)"));
        }

        $database = DBNAME;
        $nama_table = 'poliklinik';

        $stmt = $this->core->db()->pdo()->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=? AND TABLE_NAME=?");
        $stmt->execute([$database, $nama_table]);
        $result = $stmt->fetchAll();

        return [
            'type' => $type,
            'column' => $result,
            'labels' => $labels,
            'datasets' => $datasets
        ];

    }

    public function _addHeaderFiles()
    {
        $this->core->addCSS(url('assets/css/datatables.min.css'));
        $this->core->addCSS(url('assets/css/jquery.contextMenu.min.css'));
        $this->core->addJS(url('assets/jscripts/jqueryvalidation.js'));
        $this->core->addJS(url('assets/jscripts/xlsx.js'));
        $this->core->addJS(url('assets/jscripts/jspdf.min.js'));
        $this->core->addJS(url('assets/jscripts/jspdf.plugin.autotable.min.js'));
        $this->core->addJS(url('assets/jscripts/datatables.min.js'));
        $this->core->addJS(url('assets/jscripts/jquery.contextMenu.min.js'));
    }

}
