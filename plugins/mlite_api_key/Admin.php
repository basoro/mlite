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
        ];
    }

    public function getManage()
    {
        $this->_addHeaderFiles();
        $mlite_users = $this->db('mlite_users')->toArray();
        return $this->draw('manage.html', ['mlite_users' => $mlite_users]);
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
        $slug = parseUrl();
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

        echo $this->draw('chart.html', ['type' => $type, 'column' => $result, 'labels' => $labels, 'datasets' => $datasets, 'slug' => $slug]);
        exit();
    }

    public function getTools(){
        $this->_addHeaderFiles();
        return $this->draw('tools.html');
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

    public function getSwaggerJson()
    {
        // Clear output buffer to bypass license verification callback
        if (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: application/json');
        
        $postmanCollection = [];
        $file_path = BASE_DIR . '/MLITE.postman_collection.json';
        
        if (file_exists($file_path)) {
            $json = file_get_contents($file_path);
            // Check for BOM
            if (substr($json, 0, 3) == pack("CCC", 0xef, 0xbb, 0xbf)) {
                $json = substr($json, 3);
            }
            $postmanCollection = json_decode($json, true);
        }

        // Convert Postman Collection to OpenAPI (Simplified)
        $openApi = [
            'openapi' => '3.0.0',
            'info' => [
                'title' => $postmanCollection['info']['name'] ?? 'mLITE API',
                'description' => $postmanCollection['info']['description'] ?? 'API Documentation',
                'version' => '1.0.0'
            ],
            'servers' => [
                ['url' => url()]
            ],
            'components' => [
                'securitySchemes' => [
                    'ApiKeyAuth' => [
                        'type' => 'apiKey',
                        'in' => 'header',
                        'name' => 'X-Api-Key'
                    ],
                    'BearerAuth' => [
                        'type' => 'http',
                        'scheme' => 'bearer'
                    ]
                ]
            ],
            'security' => [
                ['ApiKeyAuth' => []],
                ['BearerAuth' => []]
            ],
            'paths' => []
        ];

        if (isset($postmanCollection['item'])) {
            foreach ($postmanCollection['item'] as $group) {
                $groupName = $group['name'] ?? 'General';
                if (isset($group['item'])) {
                    foreach ($group['item'] as $item) {
                        $this->_processPostmanItem($item, $openApi, $groupName);
                    }
                } else {
                     $this->_processPostmanItem($group, $openApi, 'General');
                }
            }
        }

        echo json_encode($openApi, JSON_PRETTY_PRINT);
        exit();
    }

    private function _processPostmanItem($item, &$openApi, $tag = null) {
        if (!isset($item['request'])) return;

        $method = strtolower($item['request']['method']);
        // Extract Path
        $pathUrl = $item['request']['url']['raw'];
        // Remove Base URL variable if present
        $pathUrl = str_replace('{{baseUrl}}', '', $pathUrl);
        // Remove query parameters from path
        $pathUrl = explode('?', $pathUrl)[0];
        
        // Convert Postman variables {{var}} to OpenAPI {var}
        $pathUrl = preg_replace('/\{\{(.*?)\}\}/', '{$1}', $pathUrl);

        if (!isset($openApi['paths'][$pathUrl])) {
            $openApi['paths'][$pathUrl] = [];
        }

        $parameters = [];
        
        // Path Parameters
        if (preg_match_all('/\{(.*?)\}/', $pathUrl, $matches)) {
            foreach ($matches[1] as $param) {
                $parameters[] = [
                    'name' => $param,
                    'in' => 'path',
                    'required' => true,
                    'schema' => ['type' => 'string']
                ];
            }
        }

        // Query Parameters
        if (isset($item['request']['url']['query'])) {
            foreach ($item['request']['url']['query'] as $query) {
                 $parameters[] = [
                    'name' => $query['key'],
                    'in' => 'query',
                    'schema' => ['type' => 'string'],
                    'description' => isset($query['description']) ? $query['description'] : ''
                ];
            }
        }

        $requestBody = [];
        if (isset($item['request']['body']) && $item['request']['body']['mode'] == 'raw') {
            $requestBody = [
                'content' => [
                    'application/json' => [
                        'schema' => [
                            'type' => 'object',
                            'example' => json_decode($item['request']['body']['raw'])
                        ]
                    ]
                ]
            ];
        }

        $operation = [
            'summary' => $item['name'],
            'parameters' => $parameters,
            'requestBody' => $requestBody,
            'responses' => [
                '200' => [
                    'description' => 'Successful response'
                ]
            ]
        ];

        if ($tag) {
            $operation['tags'] = [$tag];
        }

        $openApi['paths'][$pathUrl][$method] = $operation;
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
