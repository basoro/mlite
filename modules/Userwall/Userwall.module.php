<?php
if(!defined('IS_IN_MODULE')) { die("NO DIRECT FILE ACCESS!"); }

class Userwall {
  function index() {
    include('modules/Userwall/inc/index.php');
  }
  function install() {
    global $connection;
    $sql_userwall = "CREATE TABLE IF NOT EXISTS `posts` (
      `pid` int(9) NOT NULL,
      `username` varchar(50) NOT NULL,
      `desc` mediumtext NOT NULL,
      `image_url` varchar(100) NOT NULL,
      `vid_url` varchar(100) NOT NULL,
      `date` varchar(20) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    CREATE TABLE IF NOT EXISTS `comments` (
      `cid` int(9) NOT NULL,
      `username` varchar(50) NOT NULL,
      `comment` mediumtext NOT NULL,
      `cpid` int(9) NOT NULL,
      `commented_date` varchar(20) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ALTER TABLE `posts`
      ADD PRIMARY KEY (`pid`),
      ADD KEY `pid` (`pid`);
    ALTER TABLE `comments`
      ADD PRIMARY KEY (`cid`),
      ADD KEY `cpid` (`cpid`),
      ADD KEY `cid` (`cid`);
    ALTER TABLE `posts`
      MODIFY `pid` int(9) NOT NULL AUTO_INCREMENT;
    ALTER TABLE `comments`
      MODIFY `cid` int(9) NOT NULL AUTO_INCREMENT;
    ALTER TABLE `comments`
      ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`cpid`) REFERENCES `posts` (`pid`) ON DELETE CASCADE ON UPDATE NO ACTION;";

    if(mysqli_multi_query($connection,$sql_userwall)){
        echo "Table created successfully.";
    } else{
        echo "ERROR: Could not able to execute $sql. " . mysqli_error($con);
    }
  }
}
?>
