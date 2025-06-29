<?php
namespace Plugins\mLITE_API_Key;

use Systems\AdminModule;

use PDOException;

class Admin extends AdminModule
{
    public function navigation()
    {
        return [
            'Manage API'   => 'manage',
            'Alat Pengujian'   => 'tools',
            'Dokumentasi'   => 'documentations',
        ];
    }

    public function getManage()
    {
        $this->_addHeaderFiles();
        $mlite_users = $this->db('mlite_users')->toArray();
        return $this->draw('manage.html', ['mlite_users' => $mlite_users]);
    }

    public function getTools(){
        $this->_addHeaderFiles();
        return $this->draw('tools.html');
    }


    public function getTest($class)
    {
        echo $this->getMainTableFromPostData($class);
        exit();
    }

    public function getMainTableFromPostData($class)
    {
        if (!class_exists($class)) {
            echo "Class tidak ditemukan: $class<br>";
            return '';
        }
    
        $ref = new \ReflectionClass($class);
        if (!$ref->hasMethod('postData')) {
            echo "Method postData tidak ditemukan dalam class $class<br>";
            return '';
        }
    
        $method = $ref->getMethod('postData');
        $file = $method->getFileName();
        $startLine = $method->getStartLine();
        $endLine = $method->getEndLine();
    
        $lines = file($file);
        $methodCode = implode("", array_slice($lines, $startLine - 1, $endLine - $startLine + 1));
    
        // Cari FROM nama_tabel pertama
        if (preg_match('/\bFROM\s+([a-zA-Z0-9_]+)/i', $methodCode, $match)) {
            return $match[1];
        }
    
        return '';
    }    

    public function getMainTableAndQueryFromPostData($class)
    {
        if (!class_exists($class)) return '';

        $ref = new \ReflectionClass($class);
        if (!$ref->hasMethod('postData')) return '';
    
        $method = $ref->getMethod('postData');
        $file = $method->getFileName();
        $startLine = $method->getStartLine();
        $endLine = $method->getEndLine();
    
        $lines = file($file);
        $methodCode = implode("", array_slice($lines, $startLine - 1, $endLine - $startLine + 1));
    
        // Ambil semua query SELECT ... FROM ...
        preg_match_all('/SELECT\s+(.*?)\s+FROM\s+([a-zA-Z0-9_]+(?:\s+AS\s+\w+)?)(.*?)(?:;|$)/is', $methodCode, $matches, PREG_SET_ORDER);
    
        if (isset($matches[2])) {
            $third = $matches[2]; // SELECT ke-3
    
            // Gabungkan
            $selectQuery = "SELECT " . $third[1] . " FROM " . $third[2] . $third[3];
    
            // Hapus bagian WHERE, ORDER, LIMIT yang masih berisi variabel PHP
            $selectQuery = preg_replace('/\bWHERE\b\s+.*?(?=\b(ORDER|GROUP|LIMIT)\b|$)/is', '', $selectQuery);
            $selectQuery = preg_replace('/\bORDER\s+BY\b\s+.*?(?=\b(LIMIT|GROUP|WHERE)\b|$)/is', '', $selectQuery);
            $selectQuery = preg_replace('/\bLIMIT\b\s+[^;]+/is', '', $selectQuery);
    
            // Hapus sisa variabel PHP yang tidak valid untuk PDO
            $selectQuery = preg_replace('/\$\w+/', '0', $selectQuery);
    
            // Normalize spasi
            $selectQuery = trim(preg_replace('/\s+/', ' ', $selectQuery));
    
            return $selectQuery;
        }
    
        return '';
    }
        

    public function getDocumentations()
    {

        $slug = parseUrl();
        $this->_addHeaderFiles();
        $access = $this->core->getUserInfo('access');
        $access = explode(',', isset_or($access, ''));
        if ($this->core->getUserInfo('role') == 'admin') {
            $access = array_column($this->db('mlite_modules')->asc('sequence')->toArray(), 'dir');
        }
    
        $data_json = [];
        $mlite_disabled_menu = [];
        $hasPostData = '';
        $masterNav = [
                'Manage' => 'manage',
                'Dokter' => 'dokter',
                'Petugas' => 'petugas',
                'Poliklinik' => 'poliklinik',
                'Bangsal' => 'bangsal',
                'Kamar' => 'kamar',
                'Data Barang' => 'databarang',
                'Perawatan Ralan' => 'jnsperawatan',
                'Perawatan Ranap' => 'jnsperawataninap',
                'Perawatan Laboratorium' => 'jnsperawatanlab',
                'Perawatan Radiologi' => 'jnsperawatanradiologi',
                'Bahasa' => 'bahasa',
                'Propinsi' => 'propinsi',
                'Kabupaten' => 'kabupaten',
                'Kecamatan' => 'kecamatan',
                'Kelurahan' => 'kelurahan',
                'Cacat Fisik' => 'cacat',
                'Suku Bangsa' => 'suku',
                'Perusahaan Pasien' => 'perusahaan',
                'Penanggung Jawab' => 'penjab',
                'Golongan Barang' => 'golonganbarang',
                'Industri Farmasi' => 'industrifarmasi',
                'Jenis Barang' => 'jenis',
                'Kategori Barang' => 'kategoribarang',
                'Kategori Penyakit' => 'kategoripenyakit',
                'Kategori Perawatan' => 'kategoriperawatan',
                'Kode Satuan' => 'kodesatuan',
                'Master Aturan Pakai' => 'masteraturanpakai',
                'Master Berkas Digital' => 'masterberkasdigital',
                'Spesialis' => 'spesialis',
                'Bank' => 'bank',
                'Bidang' => 'bidang',
                'Departemen' => 'departemen',
                'Emergency Index' => 'emergencyindex',
                'Jabatan' => 'jabatan',
                'Jenjang Jabatan' => 'jenjangjabatan',
                'Kelompok Jabatan' => 'kelompokjabatan',
                'Pendidikan' => 'pendidikan',
                'Resiko Kerja' => 'resikokerja',
                'Status Kerja' => 'statuskerja',
                'Status WP' => 'statuswp',
                'Metode Racik' => 'metoderacik',
                'Ruang OK' => 'ruangok',
            ];

        if (!empty($slug['2']) && $slug['2'] !='master') {
            $moduleName = $slug['2'];
            $moduleClass = '\\Plugins\\' . ucfirst($moduleName) . '\\Admin';
    
            // Ubah huruf kecil menjadi huruf besar sesuai folder
            if (!class_exists($moduleClass)) {
                // Coba dengan nama dimodifikasi (e.g. rawat_jalan -> Rawat_Jalan)
                $moduleNameFixed = str_replace(' ', '_', ucwords(str_replace('_', ' ', $moduleName)));
                $moduleClass = '\\Plugins\\' . $moduleNameFixed . '\\Admin';
            }

            if(str_contains($slug[2], 'master_')) {
                list($string1, $string2) = explode('_', $slug['2']);
                $moduleClass = '\\Plugins\\' . ucfirst($string1) . '\\Src\\' . ucfirst($string2);
        
                // Ubah huruf kecil menjadi huruf besar sesuai folder
                if (!class_exists($moduleClass)) {
                    // Coba dengan nama dimodifikasi (e.g. rawat_jalan -> Rawat_Jalan)
                    $moduleNameFixed = str_replace(' ', '_', ucwords(str_replace('_', ' ', $string2)));
                    $moduleClass = '\\Plugins\\' . ucfirst($string1) . '\\Src\\' . $moduleNameFixed;
                }    
            }

            $ref = new \ReflectionClass($moduleClass);
            $hasPostData = $ref->hasMethod('postData');

            $query_sql = $this->getMainTableAndQueryFromPostData($moduleClass);                        

            $data_json = [];
            if ($hasPostData && !empty($query_sql)) {
                try {
                    $pdo = $this->db()->pdo();
                    $stmt = $pdo->prepare($query_sql . " LIMIT 2");
                    $stmt->execute();
                    $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
                    $response = [
                        "draw" => 0,
                        "iTotalRecords" => count($rows),
                        "iTotalDisplayRecords" => count($rows),
                        "aaData" => $rows
                    ];
            
                    $data_json = json_encode($response, JSON_PRETTY_PRINT);
                } catch (\Exception $e) {
                    $data_json = json_encode([
                        "draw" => 0,
                        "iTotalRecords" => 0,
                        "iTotalDisplayRecords" => 0,
                        "aaData" => [],
                        "error" => $e->getMessage()
                    ], JSON_PRETTY_PRINT);
                }
            } else {
                $data_json = json_encode([
                    "status" => 'Tidak ada method postData()'
                ], JSON_PRETTY_PRINT);
            }
    
            $mlite_disabled_menu = $this->db('mlite_disabled_menu')
                ->where('module', $slug['2'])
                ->where('user', $this->core->getUserInfo('username'))
                ->oneArray();
    
            if ($this->core->getUserInfo('role') == 'admin') {
                $mlite_disabled_menu = [
                    'can_create' => 'false',
                    'can_read' => 'false',
                    'can_update' => 'false',
                    'can_delete' => 'false'
                ];
            }
        }
    
        return $this->draw('documentations.html', [
            'modules' => $access,
            'mlite_disabled_menu' => $mlite_disabled_menu,
            'slug' => $slug,
            'data_json' => $data_json, 
            'hasPostData' => $hasPostData, 
            'masterNav' => $masterNav
        ]);
    }
        

    public function postData()
    {
        $draw = $_POST['draw'] ?? 0;
        $row1 = $_POST['start'] ?? 0;
        $rowperpage = $_POST['length'] ?? 10;
        $columnIndex = $_POST['order'][0]['column'] ?? 0;
        $columnName = $_POST['columns'][$columnIndex]['data'] ?? 'method';
        $columnSortOrder = $_POST['order'][0]['dir'] ?? 'asc';
        $searchValue = $_POST['search']['value'] ?? '';

        $search_field = $_POST['search_field_mlite_api_key'] ?? '';
        $search_text = $_POST['search_text_mlite_api_key'] ?? '';

        $searchQuery = "";
        if (!empty($search_text)) {
            $searchQuery .= " AND (" . $search_field . " LIKE :search_text) ";
        }

        $stmt = $this->db()->pdo()->prepare("SELECT COUNT(*) AS allcount FROM mlite_api_key");
        $stmt->execute();
        $records = $stmt->fetch();
        $totalRecords = $records['allcount'];

        $stmt = $this->db()->pdo()->prepare("SELECT COUNT(*) AS allcount FROM mlite_api_key WHERE 1=1 $searchQuery");
        if (!empty($search_text)) {
            $stmt->bindValue(':search_text', "%$search_text%", \PDO::PARAM_STR);
        }
        $stmt->execute();
        $records = $stmt->fetch();
        $totalRecordwithFilter = $records['allcount'];

        $sql = "SELECT * FROM mlite_api_key WHERE 1=1 $searchQuery ORDER BY $columnName $columnSortOrder LIMIT $row1, $rowperpage";
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
'api_key'=>$row['api_key'],
'username'=>$row['username'],
'method'=>$row['method'],
'ip_range'=>$row['ip_range'],
'exp_time'=>$row['exp_time']

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
                $id = NULL;
$api_key = $_POST['api_key'];
$username = $_POST['username'];
$method = implode(',', $_POST['method']);
$ip_range = $_POST['ip_range'];
$exp_time = $_POST['exp_time'];


                $sql = "INSERT INTO mlite_api_key VALUES (?, ?, ?, ?, ?, ?)";
                $binds = [$id, $api_key, $username, $method, $ip_range, $exp_time];
                $stmt = $this->db()->pdo()->prepare($sql);
                $stmt->execute($binds);

                if($this->settings->get('settings.log_query') == 'ya') {
                    \Systems\Lib\QueryWrapper::logPdoQuery($sql, $binds);
                }

                echo json_encode(["status" => "success", "message" => "Data berhasil ditambahkan."]);

            } elseif ($act == 'edit') {
                $id = $_POST['id'];
$api_key = $_POST['api_key'];
$username = $_POST['username'];
$method = implode(',', $_POST['method']);
$ip_range = $_POST['ip_range'];
$exp_time = $_POST['exp_time'];


                $sql = "UPDATE mlite_api_key SET id=?, api_key=?, username=?, method=?, ip_range=?, exp_time=? WHERE id=?";
                $binds = [$id, $api_key, $username, $method, $ip_range, $exp_time,$id];
                $stmt = $this->db()->pdo()->prepare($sql);
                $stmt->execute($binds);

                if($this->settings->get('settings.log_query') == 'ya') {
                    \Systems\Lib\QueryWrapper::logPdoQuery($sql, $binds);
                }
                echo json_encode(["status" => "success", "message" => "Data berhasil diperbarui."]);

            } elseif ($act == 'del') {
                $id= $_POST['id'];

                $sql = "DELETE FROM mlite_api_key WHERE id='$id'";
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
                $search_field = $_POST['search_field_mlite_api_key'] ?? '';
                $search_text = $_POST['search_text_mlite_api_key'] ?? '';

                $searchQuery = "";
                if (!empty($search_text)) {
                    $searchQuery .= " AND (" . $search_field . " LIKE :search_text) ";
                }

                $stmt = $this->db()->pdo()->prepare("SELECT * FROM mlite_api_key WHERE 1=1 $searchQuery");

                if (!empty($search_text)) {
                    $stmt->bindValue(':search_text', "%$search_text%", \PDO::PARAM_STR);
                }

                $stmt->execute();
                $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

                $data = [];
                foreach ($result as $row) {
                    $data[] = [
                        'id'=>$row['id'],
'api_key'=>$row['api_key'],
'username'=>$row['username'],
'method'=>$row['method'],
'ip_range'=>$row['ip_range'],
'exp_time'=>$row['exp_time']
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
        $detail = $this->db('mlite_api_key')->where('id', $id)->toArray();
        $settings =  $this->settings('settings');
        echo $this->draw('detail.html', ['detail' => $detail, 'settings' => $settings]);
        exit();
    }

    public function getChart($type = '', $column = '')
    {
        if ($type == '') {
            $type = 'pie';
        }

        $labels = $this->db('mlite_api_key')->select('method')->group('method')->toArray();
        $labels = json_encode(array_column($labels, 'method'));
        $datasets = $this->db('mlite_api_key')->select('COUNT(method)')->group('method')->toArray();
        $datasets = json_encode(array_column($datasets, 'COUNT(method)'));

        if (!empty($column)) {
            $labels = $this->db('mlite_api_key')->select($column)->group($column)->toArray();
            $labels = json_encode(array_column($labels, $column));
            $datasets = $this->db('mlite_api_key')->select("COUNT($column)")->group($column)->toArray();
            $datasets = json_encode(array_column($datasets, "COUNT($column)"));
        }

        $database = DBNAME;
        $nama_table = 'mlite_api_key';

        $stmt = $this->db()->pdo()->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=? AND TABLE_NAME=?");
        $stmt->execute([$database, $nama_table]);
        $result = $stmt->fetchAll();

        echo $this->draw('chart.html', ['type' => $type, 'column' => $result, 'labels' => $labels, 'datasets' => $datasets]);
        exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/mlite_api_key/css/admin/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        $apikey = substr(strtoupper(md5(microtime().rand(1000, 9999))), 0, 32);
        echo $this->draw(MODULES.'/mlite_api_key/js/admin/scripts.js', ['settings' => $settings, 'apikey' => $apikey]);
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
        $this->core->addJS(url('plugins/mlite_api_key/js/admin/prism.min.js'), 'footer');

        $this->core->addCSS(url('assets/css/bootstrap-datetimepicker.css'));
        $this->core->addJS(url('assets/jscripts/moment-with-locales.js'));
        $this->core->addJS(url('assets/jscripts/bootstrap-datetimepicker.js'));

        $this->core->addCSS(url([ADMIN, 'mlite_api_key', 'css']));
        $this->core->addJS(url([ADMIN, 'mlite_api_key', 'javascript']), 'footer');
    }
}
