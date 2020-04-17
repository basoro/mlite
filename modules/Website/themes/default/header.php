<?php require_once(ROOT_PATH . '/modules/Website/inc/template_functions.php') ?>

<!DOCTYPE html>
<html lang="en">

  <head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title><?php echo $dataSettings['nama_instansi']; ?></title>

    <!-- Bootstrap core CSS -->
    <link href="<?php echo URL.'/modules/Website/themes/'.$theme; ?>/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom fonts for this template -->
    <link href="<?php echo URL.'/modules/Website/themes/'.$theme; ?>/assets/vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
    <link href='https://fonts.googleapis.com/css?family=Lora:400,700,400italic,700italic' rel='stylesheet' type='text/css'>
    <link href='https://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,700italic,800italic,400,300,600,700,800' rel='stylesheet' type='text/css'>

    <!-- Custom styles for this template -->
    <link href="<?php echo URL.'/modules/Website/themes/'.$theme; ?>/assets/css/clean-blog.min.css" rel="stylesheet">

  </head>

  <body>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top" id="mainNav">
      <div class="container">
        <a class="navbar-brand" href="./index.php"><?php echo $dataSettings['nama_instansi']; ?></a>
        <button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
          Menu
          <i class="fa fa-bars"></i>
        </button>
        <div class="collapse navbar-collapse" id="navbarResponsive">
          <ul class="navbar-nav ml-auto">
						<?php
						$sql = "SELECT * FROM website_posts WHERE post_type = 'page'";
						$result = query($sql);
							echo '<li class="nav-item"><a class="nav-link" href="./index.php">Home</a></li>';
              echo '<li class="nav-item"><a class="nav-link" href="./index.php?mode=post">Blog</a></li>';
						while ($row = fetch_array($result)) {
							echo '<li class="nav-item"><a class="nav-link" href="./index.php?mode=page&page_id='.$row['post_id'].'">'.$row['post_title'].'</a></li>';
						}
						?>
          </ul>
        </div>
      </div>
    </nav>
