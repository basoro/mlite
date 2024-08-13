<?php
namespace Plugins\Mlite_Settings;

use Systems\AdminModule;

class Admin extends AdminModule
{

    public function navigation()
    {
        return [
            'Kelola'   => 'manage',
            'General'  => 'general',
            'Updates'  => 'updates'
        ];
    }

    public function getManage(){
        $this->_addHeaderFiles();
        $disabled_menu = $this->core->loadDisabledMenu('mlite_settings'); 
        foreach ($disabled_menu as &$row) { 
          if ($row == "true" ) $row = "disabled"; 
        } 
        unset($row);
        return $this->draw('manage.html', ['disabled_menu' => $disabled_menu]);
    }

    public function postData(){
        $column_name = isset_or($_POST['column_name'], 'id');
        $column_order = isset_or($_POST['column_order'], 'asc');
        $draw = isset_or($_POST['draw'], '0');
        $row1 = isset_or($_POST['start'], '0');
        $rowperpage = isset_or($_POST['length'], '10'); // Rows display per page
        $columnIndex = isset_or($_POST['order'][0]['column']); // Column index
        $columnName = isset_or($_POST['columns'][$columnIndex]['data'], $column_name); // Column name
        $columnSortOrder = isset_or($_POST['order'][0]['dir'], $column_order); // asc or desc
        $searchValue = isset_or($_POST['search']['value']); // Search value

        ## Custom Field value
        $search_field_mlite_settings= isset_or($_POST['search_field_mlite_settings']);
        $search_text_mlite_settings = isset_or($_POST['search_text_mlite_settings']);

        if ($search_text_mlite_settings != '') {
          $where[$search_field_mlite_settings.'[~]'] = $search_text_mlite_settings;
          $where = ["AND" => $where];
        } else {
          $where = [];
        }

        ## Total number of records without filtering
        $totalRecords = $this->core->db->count('mlite_settings', '*');

        ## Total number of records with filtering
        $totalRecordwithFilter = $this->core->db->count('mlite_settings', '*', $where);

        ## Fetch records
        $where['ORDER'] = [$columnName => strtoupper($columnSortOrder)];
        $where['LIMIT'] = [$row1, $rowperpage];
        $result = $this->core->db->select('mlite_settings', '*', $where);

        $data = array();
        foreach($result as $row) {
            $data[] = array(
                'id'=>$row['id'],
                'module'=>$row['module'],
                'field'=>$row['field'],
                'value'=>$row['value']
            );
        }

        ## Response
        http_response_code(200);
        $response = array(
            "draw" => intval($draw), 
            "iTotalRecords" => $totalRecords,
            "iTotalDisplayRecords" => $totalRecordwithFilter,
            "aaData" => $data
        );

        if($this->settings('settings', 'logquery') == true) {
          $this->core->LogQuery('mlite_settings => postData');
        }

        echo json_encode($response);
        exit();
    }

    public function postAksi()
    {
        if(isset($_POST['typeact'])){ 
            $act = $_POST['typeact']; 
        }else{ 
            $act = ''; 
        }

        if ($act=='add') {

            if($this->core->loadDisabledMenu('mlite_settings')['create'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $id = $_POST['id'];
            $module = $_POST['module'];
            $field = $_POST['field'];
            $value = $_POST['value'];

            
            $result = $this->core->db->insert('mlite_settings', [
              'id'=>$id, 'module'=>$module, 'field'=>$field, 'value'=>$value
            ]);


            if (!empty($result)){
              http_response_code(200);
              $data = array(
                'code' => '200', 
                'status' => 'success', 
                'msg' => 'Data telah ditambah'
              );
            } else {
              http_response_code(201);
              $data = array(
                'code' => '201', 
                'status' => 'error', 
                'msg' => $this->core->db->errorInfo[2]
              );
            }

            if($this->settings('settings', 'logquery') == true) {
              $this->core->LogQuery('mlite_settings => postAksi => add');
            }
           
            echo json_encode($data);    
        }
        if ($act=="edit") {

            if($this->core->loadDisabledMenu('mlite_settings')['update'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $id = $_POST['id'];
            $module = $_POST['module'];
            $field = $_POST['field'];
            $value = $_POST['value'];

            // BUANG FIELD PERTAMA
            $result = $this->core->db->update('mlite_settings', [
              'module'=>$module, 'field'=>$field, 'value'=>$value
            ], [
              'id'=>$id
            ]);


            if (!empty($result)){
              http_response_code(200);
              $data = array(
                'code' => '200', 
                'status' => 'success', 
                'msg' => 'Data telah diubah'
              );
            } else {
              http_response_code(201);
              $data = array(
                'code' => '201', 
                'status' => 'error', 
                'msg' => $this->core->db->errorInfo[2]
              );
            }

            if($this->settings('settings', 'logquery') == true) {
              $this->core->LogQuery('mlite_settings => postAksi => edit');
            }
    
            echo json_encode($data);             
        }

        if ($act=="del") {

            if($this->core->loadDisabledMenu('mlite_settings')['delete'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $id= $_POST['id'];
            $result = $this->core->db->delete('mlite_settings', [
              'AND' => [
                'id'=>$id
              ]
            ]);

            if (!empty($result)){
              http_response_code(200);
              $data = array(
                'code' => '200', 
                'status' => 'success', 
                'msg' => 'Data telah dihapus'
              );
            } else {
              http_response_code(201);
              $data = array(
                'code' => '201', 
                'status' => 'error', 
                'msg' => $this->core->db->errorInfo[2]
              );
            }

            if($this->settings('settings', 'logquery') == true) {
              $this->core->LogQuery('mlite_settings => postaksi => del');
            }
    
            echo json_encode($data);                    
        }

        if ($act=="lihat") {

            if($this->core->loadDisabledMenu('mlite_settings')['read'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $search_field_mlite_settings= $_POST['search_field_mlite_settings'];
            $search_text_mlite_settings = $_POST['search_text_mlite_settings'];

            if ($search_text_mlite_settings != '') {
              $where[$search_field_mlite_settings.'[~]'] = $search_text_mlite_settings;
              $where = ["AND" => $where];
            } else {
              $where = [];
            }

            ## Fetch records
            $result = $this->core->db->select('mlite_settings', '*', $where);

            $data = array();
            foreach($result as $row) {
                $data[] = array(
                    'id'=>$row['id'],
                    'module'=>$row['module'],
                    'field'=>$row['field'],
                    'value'=>$row['value']
                );
            }

            if($this->settings('settings', 'logquery') == true) {
              $this->core->LogQuery('mlite_settings => postaksi => lihat');
            }
    
            echo json_encode($data);
        }
        exit();
    }

    public function getRead($id)
    {

        if($this->core->loadDisabledMenu('mlite_settings')['read'] == 'true') {
          http_response_code(403);
          $data = array(
            'code' => '403', 
            'status' => 'error', 
            'msg' => 'Maaf, akses dibatasi!'
          );
          echo json_encode($data);    
          exit();
        }

        $result =  $this->core->db->get('mlite_settings', '*', ['id' => $id]);

        if (!empty($result)){
          http_response_code(200);
          $data = array(
            'code' => '200', 
            'status' => 'success', 
            'msg' => $result
          );
        } else {
          http_response_code(201);
          $data = array(
            'code' => '201', 
            'status' => 'error', 
            'msg' => 'Data tidak ditemukan'
          );
        }

        if($this->settings('settings', 'logquery') == true) {
          $this->core->LogQuery('mlite_settings => getRead');
        }

        echo json_encode($data);        
        exit();
    }

    public function getDetail($id)
    {

        if($this->core->loadDisabledMenu('mlite_settings')['read'] == 'true') {
          http_response_code(403);
          $data = array(
            'code' => '403', 
            'status' => 'error', 
            'msg' => 'Maaf, akses dibatasi!'
          );
          echo json_encode($data);    
          exit();
        }

        $settings =  $this->settings('settings');

        if($this->settings('settings', 'logquery') == true) {
          $this->core->LogQuery('mlite_settings => getDetail');
        }

        echo $this->draw('detail.html', ['settings' => $settings, 'id' => $id]);
        exit();
    }

    public function getGeneral()
    {
        $this->_addHeaderFiles();
        $settings = $this->settings('settings');
        $settings['timezones'] = $this->_getTimezones();

        if (!empty($redirectData = getRedirectData())) {
            $settings = array_merge($settings, $redirectData);
        }

        $this->tpl->set('settings', $this->tpl->noParse_array(htmlspecialchars_array($settings)));
        $this->tpl->set('url', url([ 'settings', 's']));

        return $this->draw('general.html');
    }

    public function postSaveGeneral()
    {
        unset($_POST['save']);
        if (($_logo = isset_or($_FILES['logo']['tmp_name'], false))) {
            $img = new \Systems\Lib\Image;

            if ($img->load($_logo)) {
                if ($img->getInfos('width') < $img->getInfos('height')) {
                    $img->crop(0, 0, $img->getInfos('width'), $img->getInfos('width'));
                } else {
                    $img->crop(0, 0, $img->getInfos('height'), $img->getInfos('height'));
                }

                if ($img->getInfos('width') > 512) {
                    $img->resize(512, 512);
                }

                $logo_ = uniqid('logo_');
                $img->save(UPLOADS."/settings/".$logo_.".".$img->getInfos('type'));
                $logo = "uploads/settings/".$logo_.".".$img->getInfos('type');
            }
        } else {
          $logo = $this->settings->get('settings.logo');
        }

        $errors = 0;

        $_POST['logo'] = $logo;

        foreach ($_POST as $field => $value) {
            if (!$this->core->db->update('mlite_settings', ['value' => $value],['module' => 'settings', 'field' => $field])) {
                $errors++;
            }
        }

        if (!$errors) {

            $this->notify('success', 'Pengaturan berhasil disimpan.');

        } else {
            $this->notify('failure', 'Gagal menyimpan pengaturan.');
        }

        if($this->settings('settings', 'logquery') == true) {
          $this->core->LogQuery('mlite_settings => postSaveGeneral');
        }

        redirect(url([ 'mlite_settings', 'general']));
    }

    public function anyUpdates()
    {
        $this->tpl->set('allow_curl', intval(function_exists('curl_init')));
        $settings = $this->settings('settings');

        if (isset($_POST['check'])) {
            $url = "https://api.github.com/repos/basoro/mlite/releases/latest";
            $opts = [
                'http' => [
                    'method' => 'GET',
                    'header' => [
                            'User-Agent: PHP'
                    ]
                ]
            ];
            $json = file_get_contents($url, false, stream_context_create($opts));
            $obj = json_decode($json, true);
    
            $this->settings('settings', 'update_check', time());

            if (!is_array($obj)) {
                $this->tpl->set('error', $obj);
            } else {
                $this->settings('settings', 'update_version', $obj['tag_name']);
                $this->settings('settings', 'update_changelog', $obj['body']);
                $this->tpl->set('update_version', $obj['tag_name']);
            }
        } elseif (isset($_POST['update'])) {
            if (!class_exists("ZipArchive")) {
                $this->tpl->set('error', "ZipArchive is required to update mLITE.");
            }

            if (!isset($_GET['manual'])) {
                $this->download('https://github.com/basoro/mlite/archive/refs/tags/'.$this->settings->get('settings.update_version').'.zip', BASE_DIR.'/tmp/latest.zip');
            } else {
                $package = glob(BASE_DIR.'/mlite-*.zip');
                if (!empty($package)) {
                    $package = array_shift($package);
                    $this->rcopy($package, BASE_DIR.'/tmp/latest.zip');
                }
            }

            define("UPGRADABLE", true);

            // Making backup
            $backup_date = date('YmdHis');
            //$this->rcopy(BASE_DIR, BASE_DIR.'/backup/'.$backup_date.'/', 0755, [BASE_DIR.'/backup', BASE_DIR.'/tmp/latest.zip', (isset($package) ? BASE_DIR.'/'.basename($package) : '')]);
            $this->rcopy(BASE_DIR.'/systems', BASE_DIR.'/backup/'.$backup_date.'/systems');
            $this->rcopy(BASE_DIR.'/plugins', BASE_DIR.'/backup/'.$backup_date.'/plugins');
            $this->rcopy(BASE_DIR.'/assets', BASE_DIR.'/backup/'.$backup_date.'/assets');
            $this->rcopy(BASE_DIR.'/themes', BASE_DIR.'/backup/'.$backup_date.'/themes');
            $this->rcopy(BASE_DIR.'/config.php', BASE_DIR.'/backup/'.$backup_date.'/config.php');
            $this->rcopy(BASE_DIR.'/manifest.json', BASE_DIR.'/backup/'.$backup_date.'/manifest.json');

            // Unzip latest update
            $zip = new \ZipArchive;
            $zip->open(BASE_DIR.'/tmp/latest.zip');
            $zip->extractTo(BASE_DIR.'/tmp/update');

            // Copy files
            $this->rcopy(BASE_DIR.'/tmp/update/mlite-'.$this->settings->get('settings.update_version').'/systems', BASE_DIR.'/systems');
            $this->rcopy(BASE_DIR.'/tmp/update/mlite-'.$this->settings->get('settings.update_version').'/plugins', BASE_DIR.'/plugins');
            $this->rcopy(BASE_DIR.'/tmp/update/mlite-'.$this->settings->get('settings.update_version').'/assets', BASE_DIR.'/assets');
            $this->rcopy(BASE_DIR.'/tmp/update/mlite-'.$this->settings->get('settings.update_version').'/themes', BASE_DIR.'/themes');

            // Restore defines
            $this->rcopy(BASE_DIR.'/backup/'.$backup_date.'/config.php', BASE_DIR.'/config.php');
            $this->rcopy(BASE_DIR.'/backup/'.$backup_date.'/manifest.json', BASE_DIR.'/manifest.json');

            // Run upgrade script
            $version = $settings['version'];
            $new_version = include(BASE_DIR.'/tmp/update/mlite-'.$this->settings->get('settings.update_version').'/systems/upgrade.php');

            // Close archive and delete all unnecessary files
            $zip->close();
            unlink(BASE_DIR.'/tmp/latest.zip');
            rrmdir(BASE_DIR.'/tmp/update');

            $this->settings('settings', 'version', $new_version);
            $this->settings('settings', 'update_version', 0);
            $this->settings('settings', 'update_changelog', '');
            $this->settings('settings', 'update_check', time());

            sleep(2);
            redirect(url([ 'settings', 'updates']));
        } elseif (isset($_GET['reset'])) {
            $this->settings('settings', 'update_version', 0);
            $this->settings('settings', 'update_changelog', '');
            $this->settings('settings', 'update_check', 0);
        } elseif (isset($_GET['manual'])) {
            $package = glob(BASE_DIR.'/mlite-*.zip');
            $version = false;
            if (!empty($package)) {
                $package_path = array_shift($package);
                preg_match('/mlite\-([0-9\.a-z]+)\.zip$/', $package_path, $matches);
                $version = $matches[1];
            }
            $manual_mode = ['version' => $version];
        }

        $this->settings->reload();
        $settings = $this->settings('settings');
        $this->tpl->set('settings', $settings);
        $this->tpl->set('manual_mode', isset_or($manual_mode, false));
        return $this->draw('update.html');
    }

    private function download($source, $dest)
    {
        set_time_limit(0);
        $fp = fopen($dest, 'w+');
        $ch = curl_init($source);
        curl_setopt($ch, CURLOPT_TIMEOUT, 50);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_exec($ch);
        curl_close($ch);
        fclose($fp);
    }

    private function _updateSettings($field, $value)
    {
        return $this->settings('settings', $field, $value);
    }

    private function rcopy($source, $dest, $permissions = 0755, $expect = [])
    {
        foreach ($expect as $e) {
            if ($e == $source) {
                return;
            }
        }

        if (is_link($source)) {
            return symlink(readlink($source), $dest);
        }

        if (is_file($source)) {
            if (!is_dir(dirname($dest))) {
                mkdir(dirname($dest), 0777, true);
            }

            return copy($source, $dest);
        }

        if (!is_dir($dest)) {
            mkdir($dest, $permissions, true);
        }

        $dir = dir($source);
        while (false !== $entry = $dir->read()) {
            if ($entry == '.' || $entry == '..') {
                continue;
            }

            $this->rcopy("$source/$entry", "$dest/$entry", $permissions, $expect);
        }

        $dir->close();
        return true;
    }

    private function _getTimezones()
    {
        $regions = array(
            \DateTimeZone::AFRICA,
            \DateTimeZone::AMERICA,
            \DateTimeZone::ANTARCTICA,
            \DateTimeZone::ASIA,
            \DateTimeZone::ATLANTIC,
            \DateTimeZone::AUSTRALIA,
            \DateTimeZone::EUROPE,
            \DateTimeZone::INDIAN,
            \DateTimeZone::PACIFIC,
            \DateTimeZone::UTC,
        );

        $timezones = array();
        foreach ($regions as $region) {
            $timezones = array_merge($timezones, \DateTimeZone::listIdentifiers($region));
        }

        $timezone_offsets = array();
        foreach ($timezones as $timezone) {
            $tz = new \DateTimeZone($timezone);
            $timezone_offsets[$timezone] = $tz->getOffset(new \DateTime);
        }

        // sort timezone by offset
        asort($timezone_offsets);

        $timezone_list = array();
        foreach ($timezone_offsets as $timezone => $offset) {
            $offset_prefix = $offset < 0 ? '-' : '+';
            $offset_formatted = gmdate('H:i', abs($offset));

            $pretty_offset = "UTC{$offset_prefix}{$offset_formatted}";

            $timezone_list[$timezone] = "({$pretty_offset}) $timezone";
        }

        return $timezone_list;
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/mlite_settings/css/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/mlite_settings/js/scripts.js', ['settings' => $settings, 'disabled_menu' => $this->core->loadDisabledMenu('mlite_settings')]);
        exit();
    }

    private function _addHeaderFiles()
    {
        $this->core->addCSS(url('assets/vendor/datatables/datatables.min.css'));
        $this->core->addCSS(url('assets/css/jquery.contextMenu.css'));
        $this->core->addJS(url('assets/js/jqueryvalidation.js'), 'footer');
        $this->core->addJS(url('assets/vendor/jspdf/xlsx.js'), 'footer');
        $this->core->addJS(url('assets/vendor/jspdf/jspdf.min.js'), 'footer');
        $this->core->addJS(url('assets/vendor/jspdf/jspdf.plugin.autotable.min.js'), 'footer');
        $this->core->addJS(url('assets/vendor/datatables/datatables.min.js'), 'footer');
        $this->core->addJS(url('assets/js/jquery.contextMenu.js'), 'footer');

        $this->core->addCSS(url([ 'mlite_settings', 'css']));
        $this->core->addJS(url([ 'mlite_settings', 'javascript']), 'footer');
    }

}
