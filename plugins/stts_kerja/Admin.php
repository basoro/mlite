<?php

namespace Plugins\Stts_Kerja;

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
    $disabled_menu = $this->core->loadDisabledMenu('stts_kerja');
    foreach ($disabled_menu as &$row) {
      if ($row == "true") $row = "disabled";
    }
    unset($row);
    return $this->draw('manage.html', ['disabled_menu' => $disabled_menu]);
  }

  public function postData()
  {
    $column_name = isset_or($_POST['column_name'], 'stts');
    $column_order = isset_or($_POST['column_order'], 'asc');
    $draw = isset_or($_POST['draw'], '0');
    $row1 = isset_or($_POST['start'], '0');
    $rowperpage = isset_or($_POST['length'], '10'); // Rows display per page
    $columnIndex = isset_or($_POST['order'][0]['column']); // Column index
    $columnName = isset_or($_POST['columns'][$columnIndex]['data'], $column_name); // Column name
    $columnSortOrder = isset_or($_POST['order'][0]['dir'], $column_order); // asc or desc
    $searchValue = isset_or($_POST['search']['value']); // Search value

    ## Custom Field value
    $search_field_stts_kerja = isset_or($_POST['search_field_stts_kerja']);
    $search_text_stts_kerja = isset_or($_POST['search_text_stts_kerja']);

    if ($search_text_stts_kerja != '') {
      $where[$search_field_stts_kerja . '[~]'] = $search_text_stts_kerja;
      $where = ["AND" => $where];
    } else {
      $where = [];
    }

    ## Total number of records without filtering
    $totalRecords = $this->core->db->count('stts_kerja', '*');

    ## Total number of records with filtering
    $totalRecordwithFilter = $this->core->db->count('stts_kerja', '*', $where);

    ## Fetch records
    $where['ORDER'] = [$columnName => strtoupper($columnSortOrder)];
    $where['LIMIT'] = [$row1, $rowperpage];
    $result = $this->core->db->select('stts_kerja', '*', $where);

    $data = array();
    foreach ($result as $row) {
      $data[] = array(
        'stts' => $row['stts'],
        'ktg' => $row['ktg'],
        'indek' => $row['indek']

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
      $this->core->LogQuery('stts_kerja => postData');
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

      if ($this->core->loadDisabledMenu('stts_kerja')['create'] == 'true') {
        http_response_code(403);
        $data = array(
          'code' => '403',
          'status' => 'error',
          'msg' => 'Maaf, akses dibatasi!'
        );
        echo json_encode($data);
        exit();
      }

      $stts = $_POST['stts'];
      $ktg = $_POST['ktg'];
      $indek = $_POST['indek'];


      $result = $this->core->db->insert('stts_kerja', [
        'stts' => $stts,
        'ktg' => $ktg,
        'indek' => $indek
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
        $this->core->LogQuery('stts_kerja => postAksi => add');
      }

      echo json_encode($data);
    }
    if ($act == "edit") {

      if ($this->core->loadDisabledMenu('stts_kerja')['update'] == 'true') {
        http_response_code(403);
        $data = array(
          'code' => '403',
          'status' => 'error',
          'msg' => 'Maaf, akses dibatasi!'
        );
        echo json_encode($data);
        exit();
      }

      $stts = $_POST['stts'];
      $ktg = $_POST['ktg'];
      $indek = $_POST['indek'];


      // BUANG FIELD PERTAMA

      $result = $this->core->db->update('stts_kerja', [
        'ktg' => $ktg,
        'indek' => $indek
      ], [
        'stts' => $stts
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
        $this->core->LogQuery('stts_kerja => postAksi => edit');
      }

      echo json_encode($data);
    }

    if ($act == "del") {

      if ($this->core->loadDisabledMenu('stts_kerja')['delete'] == 'true') {
        http_response_code(403);
        $data = array(
          'code' => '403',
          'status' => 'error',
          'msg' => 'Maaf, akses dibatasi!'
        );
        echo json_encode($data);
        exit();
      }

      $stts = $_POST['stts'];
      $result = $this->core->db->delete('stts_kerja', [
        'AND' => [
          'stts' => $stts
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
        $this->core->LogQuery('stts_kerja => postAksi => del');
      }

      echo json_encode($data);
    }

    if ($act == "lihat") {

      if ($this->core->loadDisabledMenu('stts_kerja')['read'] == 'true') {
        http_response_code(403);
        $data = array(
          'code' => '403',
          'status' => 'error',
          'msg' => 'Maaf, akses dibatasi!'
        );
        echo json_encode($data);
        exit();
      }

      $search_field_stts_kerja = $_POST['search_field_stts_kerja'];
      $search_text_stts_kerja = $_POST['search_text_stts_kerja'];

      if ($search_text_stts_kerja != '') {
        $where[$search_field_stts_kerja . '[~]'] = $search_text_stts_kerja;
        $where = ["AND" => $where];
      } else {
        $where = [];
      }

      ## Fetch records
      $result = $this->core->db->select('stts_kerja', '*', $where);

      $data = array();
      foreach ($result as $row) {
        $data[] = array(
          'stts' => $row['stts'],
          'ktg' => $row['ktg'],
          'indek' => $row['indek']
        );
      }

      if ($this->settings('settings', 'logquery') == true) {
        $this->core->LogQuery('stts_kerja => postAksi => lihat');
      }

      echo json_encode($data);
    }
    exit();
  }

  public function getRead($stts)
  {

    if ($this->core->loadDisabledMenu('stts_kerja')['read'] == 'true') {
      http_response_code(403);
      $data = array(
        'code' => '403',
        'status' => 'error',
        'msg' => 'Maaf, akses dibatasi!'
      );
      echo json_encode($data);
      exit();
    }

    $result =  $this->core->db->get('stts_kerja', '*', ['stts' => $stts]);

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
      $this->core->LogQuery('stts_kerja => getRead');
    }

    echo json_encode($data);
    exit();
  }

  public function getDetail($stts)
  {

    if ($this->core->loadDisabledMenu('stts_kerja')['read'] == 'true') {
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
      $this->core->LogQuery('stts_kerja => getDetail');
    }

    echo $this->draw('detail.html', ['settings' => $settings, 'stts' => $stts]);
    exit();
  }

  public function getChart($type = '', $column = '')
  {
    if ($type == '') {
      $type = 'pie';
    }

    $labels = $this->core->db->select('stts_kerja', 'ktg', ['GROUP' => 'ktg']);
    $datasets = $this->core->db->select('stts_kerja', ['count' => \Medoo\Medoo::raw('COUNT(<ktg>)')], ['GROUP' => 'ktg']);

    if (isset_or($column)) {
      $labels = $this->core->db->select('stts_kerja', '' . $column . '', ['GROUP' => '' . $column . '']);
      $datasets = $this->core->db->select('stts_kerja', ['count' => \Medoo\Medoo::raw('COUNT(<' . $column . '>)')], ['GROUP' => '' . $column . '']);
    }

    $database = DBNAME;
    $nama_table = 'stts_kerja';

    $get_table = $this->core->db->pdo->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$database' AND TABLE_NAME='$nama_table'");
    $get_table->execute();
    $result = $get_table->fetchAll();

    if ($this->settings('settings', 'logquery') == true) {
      $this->core->LogQuery('stts_kerja => getChart');
    }

    echo $this->draw('chart.html', ['type' => $type, 'column' => $result, 'labels' => json_encode($labels), 'datasets' => json_encode(array_column($datasets, 'count'))]);
    exit();
  }

  public function getCss()
  {
    header('Content-type: text/css');
    echo $this->draw(MODULES . '/stts_kerja/css/styles.css');
    exit();
  }

  public function getJavascript()
  {
    header('Content-type: text/javascript');
    $settings = $this->settings('settings');
    echo $this->draw(MODULES . '/stts_kerja/js/scripts.js', ['settings' => $settings, 'disabled_menu' => $this->core->loadDisabledMenu('stts_kerja')]);
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

    $this->core->addCSS(url(['stts_kerja', 'css']));
    $this->core->addJS(url(['stts_kerja', 'javascript']), 'footer');
  }
}
