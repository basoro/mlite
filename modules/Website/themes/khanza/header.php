<?php require_once(ROOT_PATH . '/modules/Website/inc/template_functions.php') ?>
<!DOCTYPE html>
<html lang="en">
<head>
<title><?php echo $dataSettings['nama_instansi']; ?></title>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="description" content="CareMed demo project">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" type="text/css" href="<?php echo URL.'/modules/Website/themes/'.$theme; ?>/styles/bootstrap4/bootstrap.min.css">
<link href="<?php echo URL.'/modules/Website/themes/'.$theme; ?>/plugins/font-awesome-4.7.0/css/font-awesome.min.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css" href="<?php echo URL.'/modules/Website/themes/'.$theme; ?>/plugins/OwlCarousel2-2.2.1/owl.carousel.css">
<link rel="stylesheet" type="text/css" href="<?php echo URL.'/modules/Website/themes/'.$theme; ?>/plugins/OwlCarousel2-2.2.1/owl.theme.default.css">
<link rel="stylesheet" type="text/css" href="<?php echo URL.'/modules/Website/themes/'.$theme; ?>/plugins/OwlCarousel2-2.2.1/animate.css">
<link rel="stylesheet" type="text/css" href="<?php echo URL.'/modules/Website/themes/'.$theme; ?>/styles/main_styles.css">
<link rel="stylesheet" type="text/css" href="<?php echo URL.'/modules/Website/themes/'.$theme; ?>/styles/responsive.css">
</head>
<body>

<div class="super_container">

	<!-- Header -->

	<header class="header trans_200">

		<!-- Top Bar -->
		<div class="top_bar">
			<div class="container">
				<div class="row">
					<div class="col">
						<div class="top_bar_content d-flex flex-row align-items-center justify-content-start">
							<div class="top_bar_item"><a href="#">Daftar Online</a></div>
							<div class="top_bar_item"><a href="#">Antrian Poli</a></div>
              <div class="top_bar_item"><a href="#">Jadwal Dokter</a></div>
							<div class="top_bar_item"><a href="#">Info Kamar</a></div>
							<div class="emergencies  d-flex flex-row align-items-center justify-content-start ml-auto">Pengaduan: 0813-5965-8918</div>
						</div>

					</div>
				</div>
			</div>
		</div>

		<!-- Header Content -->
		<div class="header_container">
			<div class="container">
				<div class="row">
					<div class="col">
						<div class="header_content d-flex flex-row align-items-center justify-content-start">
							<nav class="main_nav ml-auto">
								<ul>
									<?php
									$sql = "SELECT * FROM website_posts WHERE post_type = 'page'";
									$result = query($sql);
										echo '<li><a href="./index.php">Home</a></li>';
										echo '<li><a href="./index.php?mode=post">Berita</a></li>';
										?>
										<li class="dropdown">
											<a href="./index.php?mode=page">Informasi</a>
											<div class="dropdown-content">
												<?php
												while ($row = fetch_array($result)) {
													echo '<a class="nav-link" href="./index.php?mode=page&page_id='.$row['post_id'].'">'.$row['post_title'].'</a>';
												}
												?>
							        	</div>
										</li>
								</ul>
							</nav>
							<div class="hamburger ml-auto"><i class="fa fa-bars" aria-hidden="true"></i></div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Logo -->
		<div class="logo_container_outer">
			<div class="container">
				<div class="row">
					<div class="col">
						<div class="logo_container">
							<a href="/">
								<div class="logo_content d-flex flex-column align-items-start justify-content-center">

									<div class="logo_line"></div>
									<div class="logo d-flex flex-row align-items-center justify-content-center">
                                                                                <div class="logo_box"><img src="<?php echo URL.'/modules/Website/themes/'.$theme; ?>/images/yaski.png"></div>
										<div class="logo_text">RS <span>Khanza</span></div>
									</div>
									<div class="logo_sub">Barabai - Kalimantan Selatan</div>
								</div>
							</a>
						</div>
					</div>
				</div>
			</div>
		</div>

	</header>

	<!-- Menu -->

	<div class="menu_container menu_mm">

		<!-- Menu Close Button -->
		<div class="menu_close_container">
			<div class="menu_close"></div>
		</div>

		<!-- Menu Items -->
		<div class="menu_inner menu_mm">
			<div class="menu menu_mm">
				<ul class="menu_list menu_mm">
					<?php
					$sql = "SELECT * FROM website_posts WHERE post_type = 'page'";
					$result = query($sql);
						echo '<li class="menu_item menu_mm"><a href="./index.php">Home</a></li>';
					while ($row = fetch_array($result)) {
						echo '<li class="menu_item menu_mm"><a href="page.php?page_id='.$row['post_id'].'">'.$row['post_title'].'</a></li>';
					}
					?>
				</ul>
			</div>
			<div class="menu_extra">
				<div class="menu_faq"><a href="#">Bantuan</a></div>
				<div class="menu_appointment"><a href="#">Daftar Online</a></div>
				<div class="menu_emergencies">Pengaduan: 0852-4980-8800</div>
			</div>

		</div>

	</div>
