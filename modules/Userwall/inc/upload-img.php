<?php
  include('../../../config.php');
  include('../../../init.php');
  include('libs/SimpleImage.php');
  $uploaddir = $_SERVER['DOCUMENT_ROOT'].'/'.DIR.'modules/Userwall/inc/uploads/';
  $bname = basename($_FILES['uploadfile']['name']);
  $file = $uploaddir . $bname;
  if (move_uploaded_file($_FILES['uploadfile']['tmp_name'], $file)) {
    list($width, $height, $type, $attr) = getimagesize($file);
    if($width > 1024) {
    $image = new SimpleImage();
    $image->load($file);
    $image->resizeToWidth(1024);
    $image->save($file);
  }
    echo "success";
  } else {
    echo "error";
  }
?>
