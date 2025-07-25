<?php
namespace Plugins\mLITE_Logs;

use Systems\AdminModule;
use PDOException;

class Admin extends AdminModule
{
    public function navigation()
    {
        return [
            'Kelola' => 'manage',
        ];
    }

    public function getManage()
    {
        $this->_addHeaderFiles();
        return $this->draw('manage.html');
    }

    public function postData()
    {
        $draw = $_POST['draw'] ?? 0;
        $row1 = $_POST['start'] ?? 0;
        $rowperpage = $_POST['length'] ?? 10;
        $columnIndex = $_POST['order'][0]['column'] ?? 0;
        $columnName = $_POST['columns'][$columnIndex]['data'] ?? 'username';
        $columnSortOrder = $_POST['order'][0]['dir'] ?? 'asc';
        $searchValue = $_POST['search']['value'] ?? '';

        $search_field = $_POST['search_field_mlite_query_logs'] ?? '';
        $search_text = $_POST['search_text_mlite_query_logs'] ?? '';

        $searchQuery = "";
        if (!empty($search_text)) {
            $searchQuery .= " AND (" . $search_field . " LIKE :search_text) ";
        }

        $stmt = $this->db()->pdo()->prepare("SELECT COUNT(*) AS allcount FROM mlite_query_logs");
        $stmt->execute();
        $records = $stmt->fetch();
        $totalRecords = $records['allcount'];

        $stmt = $this->db()->pdo()->prepare("SELECT COUNT(*) AS allcount FROM mlite_query_logs WHERE 1=1 $searchQuery");
        if (!empty($search_text)) {
            $stmt->bindValue(':search_text', "%$search_text%", \PDO::PARAM_STR);
        }
        $stmt->execute();
        $records = $stmt->fetch();
        $totalRecordwithFilter = $records['allcount'];

        $sql = "SELECT * FROM mlite_query_logs WHERE 1=1 $searchQuery ORDER BY $columnName $columnSortOrder LIMIT $row1, $rowperpage";
        $stmt = $this->db()->pdo()->prepare($sql);
        if (!empty($search_text)) {
            $stmt->bindValue(':search_text', "%$search_text%", \PDO::PARAM_STR);
        }
        $stmt->execute();
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $data = [];
        foreach ($result as $row) {
            $data[] = [
                'id'=>$row['id'],
'sql_text'=>$row['sql_text'],
'bindings'=>$row['bindings'],
'created_at'=>$row['created_at'],
'error_message'=>$row['error_message'],
'username'=>$row['username']

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
                $id = $_POST['id'];
$sql_text = $_POST['sql_text'];
$bindings = $_POST['bindings'];
$created_at = $_POST['created_at'];
$error_message = $_POST['error_message'];
$username = $_POST['username'];


                $sql = "INSERT INTO mlite_query_logs VALUES (?, ?, ?, ?, ?, ?)";
                $binds = [$id, $sql_text, $bindings, $created_at, $error_message, $username];
                $stmt = $this->db()->pdo()->prepare($sql);
                $stmt->execute($binds);

                if($this->settings->get('settings.log_query') == 'ya') {
                    \Systems\Lib\QueryWrapper::logPdoQuery($sql, $binds);
                }

                echo json_encode(["status" => "success", "message" => "Data berhasil ditambahkan."]);

            } elseif ($act == 'edit') {
                $id = $_POST['id'];
$sql_text = $_POST['sql_text'];
$bindings = $_POST['bindings'];
$created_at = $_POST['created_at'];
$error_message = $_POST['error_message'];
$username = $_POST['username'];


                $sql = "UPDATE mlite_query_logs SET id=?, sql_text=?, bindings=?, created_at=?, error_message=?, username=? WHERE id=?";
                $binds = [$id, $sql_text, $bindings, $created_at, $error_message, $username,$id];
                $stmt = $this->db()->pdo()->prepare($sql);
                $stmt->execute($binds);

                if($this->settings->get('settings.log_query') == 'ya') {
                    \Systems\Lib\QueryWrapper::logPdoQuery($sql, $binds);
                }
                echo json_encode(["status" => "success", "message" => "Data berhasil diperbarui."]);

            } elseif ($act == 'del') {
                $id= $_POST['id'];

                $sql = "DELETE FROM mlite_query_logs WHERE id='$id'";
                $binds = [];

                $stmt = $this->db()->pdo()->prepare($sql);
                $stmt->execute();

                if($this->settings->get('settings.log_query') == 'ya') {
                    \Systems\Lib\QueryWrapper::logPdoQuery($sql, $binds);
                }

                if ($stmt->rowCount() > 0) {
                    echo json_encode(["status" => "success", "message" => "Data berhasil dihapus."]);
                } else {
                    echo json_encode(["status" => "error", "message" => "Data tidak ditemukan atau gagal dihapus."]);
                }

            } elseif ($act == 'lihat') {
                $search_field = $_POST['search_field_mlite_query_logs'] ?? '';
                $search_text = $_POST['search_text_mlite_query_logs'] ?? '';

                $searchQuery = "";
                if (!empty($search_text)) {
                    $searchQuery .= " AND (" . $search_field . " LIKE :search_text) ";
                }

                $stmt = $this->db()->pdo()->prepare("SELECT * FROM mlite_query_logs WHERE 1=1 $searchQuery");

                if (!empty($search_text)) {
                    $stmt->bindValue(':search_text', "%$search_text%", \PDO::PARAM_STR);
                }

                $stmt->execute();
                $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

                $data = [];
                foreach ($result as $row) {
                    $data[] = [
                        'id'=>$row['id'],
'sql_text'=>$row['sql_text'],
'bindings'=>$row['bindings'],
'created_at'=>$row['created_at'],
'error_message'=>$row['error_message'],
'username'=>$row['username']
                    ];
                }

                echo json_encode($data);
            }
        } catch (\PDOException $e) {
            if($this->settings->get('settings.log_query') == 'ya') {            
                if (in_array($act, ['add', 'edit', 'del'])) {
                \Systems\Lib\QueryWrapper::logPdoQuery($sql, $binds, $e->getMessage());   
                } 
            }
            echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        }

        exit();
    }

    public function getDetail($id)
    {
        $detail = $this->db('mlite_query_logs')->where('id', $id)->toArray();
        $settings =  $this->settings('settings');
        echo $this->draw('detail.html', ['detail' => $detail, 'settings' => $settings]);
        exit();
    }

    public function getChart($type = '', $column = '')
    {
        if ($type == '') {
            $type = 'pie';
        }

        $labels = $this->db('mlite_query_logs')->select('username')->group('username')->toArray();
        $labels = json_encode(array_column($labels, 'username'));
        $datasets = $this->db('mlite_query_logs')->select('COUNT(username)')->group('username')->toArray();
        $datasets = json_encode(array_column($datasets, 'COUNT(username)'));

        if (!empty($column)) {
            $labels = $this->db('mlite_query_logs')->select($column)->group($column)->toArray();
            $labels = json_encode(array_column($labels, $column));
            $datasets = $this->db('mlite_query_logs')->select("COUNT($column)")->group($column)->toArray();
            $datasets = json_encode(array_column($datasets, "COUNT($column)"));
        }

        $database = DBNAME;
        $nama_table = 'mlite_query_logs';

        $stmt = $this->db()->pdo()->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=? AND TABLE_NAME=?");
        $stmt->execute([$database, $nama_table]);
        $result = $stmt->fetchAll();

        echo $this->draw('chart.html', ['type' => $type, 'column' => $result, 'labels' => $labels, 'datasets' => $datasets]);
        exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/mlite_logs/css/admin/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/mlite_logs/js/admin/scripts.js', ['settings' => $settings]);
        exit();
    }

    private function _addHeaderFiles()
    {
        $this->core->addCSS(url('assets/css/datatables.min.css'));
        $this->core->addCSS(url('assets/css/jquery.contextMenu.min.css'));
        $this->core->addJS(url('assets/jscripts/jqueryvalidation.js'));
        $this->core->addJS(url('assets/jscripts/xlsx.js'));
        $this->core->addJS(url('assets/jscripts/jspdf.min.js'));
        $this->core->addJS(url('assets/jscripts/jspdf.plugin.autotable.min.js'));
        $this->core->addJS(url('assets/jscripts/datatables.min.js'));
        $this->core->addJS(url('assets/jscripts/jquery.contextMenu.min.js'));

        $this->core->addCSS(url([ADMIN, 'mlite_logs', 'css']));
        $this->core->addJS(url([ADMIN, 'mlite_logs', 'javascript']), 'footer');
    }
}
