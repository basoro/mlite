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
        header('Content-Type: application/json');
        
        $json = file_get_contents(BASE_DIR . '/MLITE.postman_collection.json');
        $postmanCollection = json_decode($json, true);

        // Convert Postman Collection to OpenAPI (Simplified)
        $openApi = [
            'openapi' => '3.0.0',
            'info' => [
                'title' => $postmanCollection['info']['name'],
                'description' => $postmanCollection['info']['description'],
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
        exit;
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
