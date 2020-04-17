<?php
if(!defined('IS_IN_MODULE')) { die("NO DIRECT FILE ACCESS!"); }
?>

<?php
class Website {
    function index() {
      global $dataSettings;
      include('modules/Website/inc/index.php');
    }
    function posts() {
      include('modules/Website/inc/posts.php');
    }
    function add_post() {
      include('modules/Website/inc/add-post.php');
    }
    function categories() {
      include('modules/Website/inc/categories.php');
    }
    function pages() {
      include('modules/Website/inc/pages.php');
    }
    function add_page() {
      include('modules/Website/inc/add-page.php');
    }
    function media() {
      include('modules/Website/inc/media.php');
    }
}
?>
