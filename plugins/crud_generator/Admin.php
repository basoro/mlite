<?php
namespace Plugins\Crud_Generator;

use Systems\AdminModule;

class Admin extends AdminModule
{

    public function navigation()
    {
        return [
            'Generator'   => 'manage',
        ];
    }

    public function getManage(){
        $this->_addHeaderFiles();
        return $this->draw('manage.html');
    }

    public function getAddTable(){
      $this->_addHeaderFiles();
      echo $this->draw('add.table.html');
      exit();
    }

    public function getIcons(){
      $this->_addHeaderFiles();
      echo $this->draw('icons.html');
      exit();
    }

    public function postSaveAddTable()
    {
      $table_name = $_POST['table_name'];
      $column_name = $_POST['column_name'];
      $column_type = $_POST['column_type'];
      $column_length = $_POST['column_length'];
      $column_default = $_POST['column_default'];
      
      $table_data = array();
      for($i=0; $i < count($column_name); $i++){
        $table_data[] = array(
          'column_name' => $column_name[$i], 
          'column_type' => $column_type[$i], 
          'column_length' => $column_length[$i], 
          'column_default' => isset_or($column_default[$i], '') 
        );
      }

      $c_query = "CREATE TABLE IF NOT EXISTS ". $table_name ." (";
      $c_query .= "id INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY, ";
      foreach($table_data as $key => $value){
        if($column_type =='INT' || $column_type =='VARCHAR') {
          $c_query .= $value['column_name'] . " " . $value['column_type'] . "(" . $value['column_length'] . ") " . $value['column_default'] . ",";
        } else {
          $c_query .= $value['column_name'] . " " . $value['column_type'] . " " . $value['column_default'] . ",";
        }
      }
      $c_query = substr_replace($c_query,"",-1);
      $c_query .= ") ENGINE=InnoDB DEFAULT CHARSET=latin1;";

      $query = $this->core->db->query($c_query);

      if (!empty($query)){
        $data = array(
          'status' => 'success', 
          'msg' => 'Data telah ditambah'
        );
      } else {
        $data = array(
          'status' => 'error', 
          'msg' => $this->core->db->errorInfo[2]
        );
      }

      echo json_encode($data); 

      exit();
    }

    public function postDatabase()
    {
      $database = DBNAME;
      $get_table = $this->core->db->pdo->prepare("SELECT DISTINCT TABLE_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='$database'");
	    $get_table->execute();
	    $result = $get_table->fetchAll();

	    $data = array();

      foreach($result as $row){
        $data[] = array( 'TABLE_NAME'=>$row['TABLE_NAME'] );
      }
	    echo json_encode($data);
      exit();
    }

    public function postDatabaseExclude()
    {
      $modules = implode("','",array_column($this->core->db->select('mlite_modules', '*', ['ORDER' => 'sequence']), 'dir'));
      $database = DBNAME;
      $get_table = $this->core->db->pdo->prepare("SELECT DISTINCT TABLE_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='$database' AND TABLE_NAME NOT IN ('.$modules.')");
	    $get_table->execute();
	    $result = $get_table->fetchAll();

	    $data = array();

      foreach($result as $row){
        $data[] = array( 'TABLE_NAME'=>$row['TABLE_NAME'] );
      }
	    echo json_encode($data);
      exit();
    }

    public function postTable()
    {
      $database = DBNAME;
      $nama_table = $_POST['nama_table'];

      $get_table = $this->core->db->pdo->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$database' AND TABLE_NAME='$nama_table'");
	    $get_table->execute();
	    $result = $get_table->fetchAll();

	    $data = array();

      foreach($result as $row){
        $data[] = array( 'COLUMN_NAME'=>$row['COLUMN_NAME'] );
      }
	    echo json_encode($data);
      exit();
    }    

    public function postTulisAdmin()
    {
      if (!is_dir(MODULES."/".$_POST['modulename'])) {
          mkdir(MODULES."/".$_POST['modulename'], 0777);
      }
      $data = $_POST['content']; 
      $f = fopen(MODULES."/".$_POST['modulename']."/".$_POST['filename'], 'w+');
      fwrite($f, $data);
      fclose($f);
      exit();
    }

    public function postTulisInfo()
    {
      if (!is_dir(MODULES."/".$_POST['modulename'])) {
          mkdir(MODULES."/".$_POST['modulename'], 0777);
      }
      $data = $_POST['content']; 
      $f = fopen(MODULES."/".$_POST['modulename']."/".$_POST['filename'], 'w+');
      fwrite($f, $data);
      fclose($f);
      exit();
    }

    public function postTulisView()
    {
      if (!is_dir(MODULES."/".$_POST['modulename'])) {
          mkdir(MODULES."/".$_POST['modulename'], 0777);
      }
      if (!is_dir(MODULES."/".$_POST['modulename']."/view/")) {
          mkdir(MODULES."/".$_POST['modulename']."/view", 0777);
      }
      $data = $_POST['content']; 
      $f = fopen(MODULES."/".$_POST['modulename']."/view/manage.html", 'w+');
      fwrite($f, $data);
      fclose($f);
      exit();
    }

    public function postTulisDetail()
    {
      if (!is_dir(MODULES."/".$_POST['modulename'])) {
          mkdir(MODULES."/".$_POST['modulename'], 0777);
      }
      if (!is_dir(MODULES."/".$_POST['modulename']."/view/")) {
          mkdir(MODULES."/".$_POST['modulename']."/view", 0777);
      }
      $data = $_POST['content']; 
      $f = fopen(MODULES."/".$_POST['modulename']."/view/detail.html", 'w+');
      fwrite($f, $data);
      fclose($f);
      exit();
    }    

    public function postTulisChart()
    {
      if (!is_dir(MODULES."/".$_POST['modulename'])) {
          mkdir(MODULES."/".$_POST['modulename'], 0777);
      }
      if (!is_dir(MODULES."/".$_POST['modulename']."/view/")) {
          mkdir(MODULES."/".$_POST['modulename']."/view", 0777);
      }
      $data = $_POST['content']; 
      $f = fopen(MODULES."/".$_POST['modulename']."/view/chart.html", 'w+');
      fwrite($f, $data);
      fclose($f);
      exit();
    } 

    public function postTulisJavascript()
    {
      if (!is_dir(MODULES."/".$_POST['modulename'])) {
          mkdir(MODULES."/".$_POST['modulename'], 0777);
      }
      if (!is_dir(MODULES."/".$_POST['modulename']."/js/")) {
          mkdir(MODULES."/".$_POST['modulename']."/js", 0777);
      }
      $data = $_POST['content']; 
      $f = fopen(MODULES."/".$_POST['modulename']."/js/scripts.js", 'w+');
      fwrite($f, $data);
      fclose($f);
      exit();
    }

    public function postTulisStyle()
    {
      if (!is_dir(MODULES."/".$_POST['modulename'])) {
          mkdir(MODULES."/".$_POST['modulename'], 0777);
      }
      if (!is_dir(MODULES."/".$_POST['modulename']."/css/")) {
          mkdir(MODULES."/".$_POST['modulename']."/css", 0777);
      }
      $data = $_POST['content']; 
      $f = fopen(MODULES."/".$_POST['modulename']."/css/styles.css", 'w+');
      fwrite($f, $data);
      fclose($f);
      exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/crud_generator/js/scripts.js');
        exit();
    }

    private function _addHeaderFiles()
    {
      $this->core->addJS(url('assets/js/jqueryvalidation.js'), 'footer');
      $this->core->addJS(url(['crud_generator', 'javascript']), 'footer');
    }

    public function getTest()
    {
      $modules = implode("','",array_column($this->core->db->select('mlite_modules', '*', ['ORDER' => 'sequence']), 'dir'));
      $database = DBNAME;
      $get_table = $this->core->db->pdo->prepare("SELECT DISTINCT TABLE_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='$database' AND TABLE_NAME NOT IN ('.$modules.')");
	    $get_table->execute();
	    $result = $get_table->fetchAll();

	    $data = array();

      foreach($result as $row){
        $data[] = array( 'TABLE_NAME'=>$row['TABLE_NAME'] );
      }
      echo $this->core->LogQuery('reg_periksa');
	    echo json_encode($data);
      exit();
    }

}
