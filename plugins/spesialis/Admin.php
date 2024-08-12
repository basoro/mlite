<?php

namespace Plugins\Spesialis;

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
    $disabled_menu = $this->core->loadDisabledMenu('spesialis');
    foreach ($disabled_menu as &$row) {
      if ($row == "true") $row = "disabled";
    }
    unset($row);
    return $this->draw('manage.html', ['disabled_menu' => $disabled_menu]);
  }

  public function postData()
  {
    $column_name = isset_or($_POST['column_name'], 'kd_sps');
    $column_order = isset_or($_POST['column_order'], 'asc');
    $draw = isset_or($_POST['draw'], '0');
    $row1 = isset_or($_POST['start'], '0');
    $rowperpage = isset_or($_POST['length'], '10'); // Rows display per page
    $columnIndex = isset_or($_POST['order'][0]['column']); // Column index
    $columnName = isset_or($_POST['columns'][$columnIndex]['data'], $column_name); // Column name
    $columnSortOrder = isset_or($_POST['order'][0]['dir'], $column_order); // asc or desc
    $searchValue = isset_or($_POST['search']['value']); // Search value

    ## Custom Field value
    $search_field_spesialis = isset_or($_POST['search_field_spesialis']);
    $search_text_spesialis = isset_or($_POST['search_text_spesialis']);

    if ($search_text_spesialis != '') {
      $where[$search_field_spesialis . '[~]'] = $search_text_spesialis;
      $where = ["AND" => $where];
    } else {
      $where = [];
    }

    ## Total number of records without filtering
    $totalRecords = $this->core->db->count('spesialis', '*');

    ## Total number of records with filtering
    $totalRecordwithFilter = $this->core->db->count('spesialis', '*', $where);

    ## Fetch records
    $where['ORDER'] = [$columnName => strtoupper($columnSortOrder)];
    $where['LIMIT'] = [$row1, $rowperpage];
    $result = $this->core->db->select('spesialis', '*', $where);

    $data = array();
    foreach ($result as $row) {
      $data[] = array(
        'kd_sps' => $row['kd_sps'],
        'nm_sps' => $row['nm_sps']

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

    if ($this->settings('settings', 'logquery') == true) {
      $this->core->LogQuery('spesialis => postData');
    }

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

      if ($this->core->loadDisabledMenu('spesialis')['create'] == 'true') {
        http_response_code(403);
        $data = array(
          'code' => '403',
          'status' => 'error',
          'msg' => 'Maaf, akses dibatasi!'
        );
        echo json_encode($data);
        exit();
      }

      $kd_sps = $_POST['kd_sps'];
      $nm_sps = $_POST['nm_sps'];


      $result = $this->core->db->insert('spesialis', [
        'kd_sps' => $kd_sps,
        'nm_sps' => $nm_sps
      ]);


      if (!empty($result)) {
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

      if ($this->settings('settings', 'logquery') == true) {
        $this->core->LogQuery('spesialis => postAksi => add');
      }

      echo json_encode($data);
    }
    if ($act == "edit") {

      if ($this->core->loadDisabledMenu('spesialis')['update'] == 'true') {
        http_response_code(403);
        $data = array(
          'code' => '403',
          'status' => 'error',
          'msg' => 'Maaf, akses dibatasi!'
        );
        echo json_encode($data);
        exit();
      }

      $kd_sps = $_POST['kd_sps'];
      $nm_sps = $_POST['nm_sps'];


      // BUANG FIELD PERTAMA

      $result = $this->core->db->update('spesialis', [
        'nm_sps' => $nm_sps
      ], [
        'kd_sps' => $kd_sps
      ]);


      if (!empty($result)) {
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

      if ($this->settings('settings', 'logquery') == true) {
        $this->core->LogQuery('spesialis => postAksi => edit');
      }

      echo json_encode($data);
    }

    if ($act == "del") {

      if ($this->core->loadDisabledMenu('spesialis')['delete'] == 'true') {
        http_response_code(403);
        $data = array(
          'code' => '403',
          'status' => 'error',
          'msg' => 'Maaf, akses dibatasi!'
        );
        echo json_encode($data);
        exit();
      }

      $kd_sps = $_POST['kd_sps'];
      $result = $this->core->db->delete('spesialis', [
        'AND' => [
          'kd_sps' => $kd_sps
        ]
      ]);

      if (!empty($result)) {
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

      if ($this->settings('settings', 'logquery') == true) {
        $this->core->LogQuery('spesialis => postAksi => del');
      }

      echo json_encode($data);
    }

    if ($act == "lihat") {

      if ($this->core->loadDisabledMenu('spesialis')['read'] == 'true') {
        http_response_code(403);
        $data = array(
          'code' => '403',
          'status' => 'error',
          'msg' => 'Maaf, akses dibatasi!'
        );
        echo json_encode($data);
        exit();
      }

      $search_field_spesialis = $_POST['search_field_spesialis'];
      $search_text_spesialis = $_POST['search_text_spesialis'];

      if ($search_text_spesialis != '') {
        $where[$search_field_spesialis . '[~]'] = $search_text_spesialis;
        $where = ["AND" => $where];
      } else {
        $where = [];
      }

      ## Fetch records
      $result = $this->core->db->select('spesialis', '*', $where);

      $data = array();
      foreach ($result as $row) {
        $data[] = array(
          'kd_sps' => $row['kd_sps'],
          'nm_sps' => $row['nm_sps']
        );
      }

      if ($this->settings('settings', 'logquery') == true) {
        $this->core->LogQuery('spesialis => postAksi => lihat');
      }

      echo json_encode($data);
    }
    exit();
  }

  public function getRead($kd_sps)
  {

    if ($this->core->loadDisabledMenu('spesialis')['read'] == 'true') {
      http_response_code(403);
      $data = array(
        'code' => '403',
        'status' => 'error',
        'msg' => 'Maaf, akses dibatasi!'
      );
      echo json_encode($data);
      exit();
    }

    $result =  $this->core->db->get('spesialis', '*', ['kd_sps' => $kd_sps]);

    if (!empty($result)) {
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

    if ($this->settings('settings', 'logquery') == true) {
      $this->core->LogQuery('spesialis => getRead');
    }

    echo json_encode($data);
    exit();
  }

  public function getDetail($kd_sps)
  {

    if ($this->core->loadDisabledMenu('spesialis')['read'] == 'true') {
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

    if ($this->settings('settings', 'logquery') == true) {
      $this->core->LogQuery('spesialis => getDetail');
    }

    echo $this->draw('detail.html', ['settings' => $settings, 'kd_sps' => $kd_sps]);
    exit();
  }

  public function getChart($type = '', $column = '')
  {
    if ($type == '') {
      $type = 'pie';
    }

    $labels = $this->core->db->select('spesialis', 'nm_sps', ['GROUP' => 'nm_sps']);
    $datasets = $this->core->db->select('spesialis', ['count' => \Medoo\Medoo::raw('COUNT(<nm_sps>)')], ['GROUP' => 'nm_sps']);

    if (isset_or($column)) {
      $labels = $this->core->db->select('spesialis', '' . $column . '', ['GROUP' => '' . $column . '']);
      $datasets = $this->core->db->select('spesialis', ['count' => \Medoo\Medoo::raw('COUNT(<' . $column . '>)')], ['GROUP' => '' . $column . '']);
    }

    $database = DBNAME;
    $nama_table = 'spesialis';

    $get_table = $this->core->db->pdo->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$database' AND TABLE_NAME='$nama_table'");
    $get_table->execute();
    $result = $get_table->fetchAll();

    if ($this->settings('settings', 'logquery') == true) {
      $this->core->LogQuery('spesialis => getChart');
    }

    echo $this->draw('chart.html', ['type' => $type, 'column' => $result, 'labels' => json_encode($labels), 'datasets' => json_encode(array_column($datasets, 'count'))]);
    exit();
  }

  public function getCss()
  {
    header('Content-type: text/css');
    echo $this->draw(MODULES . '/spesialis/css/styles.css');
    exit();
  }

  public function getJavascript()
  {
    header('Content-type: text/javascript');
    $settings = $this->settings('settings');
    echo $this->draw(MODULES . '/spesialis/js/scripts.js', ['settings' => $settings, 'disabled_menu' => $this->core->loadDisabledMenu('spesialis')]);
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

    $this->core->addCSS(url(['spesialis', 'css']));
    $this->core->addJS(url(['spesialis', 'javascript']), 'footer');
  }
}
