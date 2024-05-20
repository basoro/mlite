<?php
namespace Plugins\Crud_Generator;

use Systems\AdminModule;

class Admin extends AdminModule
{

    public function navigation()
    {
        return [
            'Kelola'   => 'manage',
        ];
    }

    public function getManage(){
        $this->_addHeaderFiles();
        return $this->draw('manage.html');
    }

    public function postDatabase()
    {
      $database = DBNAME;
      $get_table = $this->db()->pdo()->prepare("SELECT DISTINCT TABLE_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='$database'");
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

      $get_table = $this->db()->pdo()->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$database' AND TABLE_NAME='$nama_table'");
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
      if (!is_dir(MODULES."/".$_POST['modulename']."/view/admin")) {
        mkdir(MODULES."/".$_POST['modulename']."/view/admin", 0777);
      }
      $data = $_POST['content']; 
      $f = fopen(MODULES."/".$_POST['modulename']."/view/admin/manage.html", 'w+');
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
      if (!is_dir(MODULES."/".$_POST['modulename']."/view/admin")) {
        mkdir(MODULES."/".$_POST['modulename']."/view/admin", 0777);
      }
      $data = $_POST['content']; 
      $f = fopen(MODULES."/".$_POST['modulename']."/view/admin/detail.html", 'w+');
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
      if (!is_dir(MODULES."/".$_POST['modulename']."/js/admin")) {
        mkdir(MODULES."/".$_POST['modulename']."/js/admin", 0777);
      }
      $data = $_POST['content']; 
      $f = fopen(MODULES."/".$_POST['modulename']."/js/admin/scripts.js", 'w+');
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
      if (!is_dir(MODULES."/".$_POST['modulename']."/css/admin")) {
        mkdir(MODULES."/".$_POST['modulename']."/css/admin", 0777);
      }
      $data = $_POST['content']; 
      $f = fopen(MODULES."/".$_POST['modulename']."/css/admin/styles.css", 'w+');
      fwrite($f, $data);
      fclose($f);
      exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/crud_generator/js/admin/scripts.js');
        exit();
    }

    private function _addHeaderFiles()
    {
        $this->core->addJS(url([ADMIN, 'crud_generator', 'javascript']), 'footer');
    }

}
