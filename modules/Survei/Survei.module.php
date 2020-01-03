<?php
if(!defined('IS_IN_MODULE')) { die("NO DIRECT FILE ACCESS!"); }
?>

<?php
class Survei {
    function index() {
      if(num_rows(query("SHOW TABLES LIKE 'questions'")) !== 1) {
        echo '<div class="alert bg-pink alert-dismissible text-center">';
        echo '<p class="lead">Belum terinstall Database Survei</p>';
        echo '<a href="'.URL.'/?module=Survei&page=install" class="btn btn-lg btn-primary m-t-20" style="color:#fff;">Install Sekarang</a>';
        echo '</div>';
      } else {
        include('modules/Survei/dashboard.php');
      }
    }
    function poll() {
      if(num_rows(query("SHOW TABLES LIKE 'questions'")) !== 1) {
        echo '<div class="alert bg-pink alert-dismissible text-center">';
        echo '<p class="lead">Belum terinstall Database Survei</p>';
        echo '<a href="'.URL.'/?module=Survei&page=install" class="btn btn-lg btn-primary m-t-20" style="color:#fff;">Install Sekarang</a>';
        echo '</div>';
      } else {
        include('modules/Survei/poll/index.php');
      }
    }
    function install() {
      global $connection;
      $sql_userwall = "CREATE TABLE IF NOT EXISTS `answers` (
        `id` int(11) NOT NULL,
        `question_id` int(11) NOT NULL,
        `answer` varchar(255) NOT NULL
      ) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=latin1;
      CREATE TABLE IF NOT EXISTS `poll_answers` (
        `id` int(11) NOT NULL,
        `question_id` int(11) NOT NULL,
        `answer_id` int(11) NOT NULL,
        `user_ip` varchar(100) NOT NULL
      ) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=latin1;
      CREATE TABLE IF NOT EXISTS `questions` (
        `id` int(11) NOT NULL,
        `question` varchar(255) NOT NULL
      ) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
      ALTER TABLE `answers`
        ADD PRIMARY KEY (`id`);
      ALTER TABLE `poll_answers`
        ADD PRIMARY KEY (`id`);
      ALTER TABLE `questions`
        ADD PRIMARY KEY (`id`);
      ALTER TABLE `answers`
        MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
      ALTER TABLE `poll_answers`
        MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
      ALTER TABLE `questions`
        MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;";

      if(mysqli_multi_query($connection,$sql_userwall)){
          echo "Table created successfully.";
      } else{
          echo "ERROR: Could not able to execute $sql. " . mysqli_error($con);
      }
    }
}
?>
