<?php require_once(ROOT_PATH . '/modules/Website/themes/'.$theme.'/header.php') ?>
<?php $page = getPage($_GET['page_id']); ?>
<?php $pages = getPublishedPages(); ?>

<div class="page">
	<div class="home">
		<div class="home_background parallax-window" data-parallax="scroll" data-image-src="<?php echo URL.'/modules/Website/themes/'.$theme; ?>/images/about.jpg" data-speed="0.8"></div>
		<div class="home_container">
			<div class="container">
				<div class="row">
					<div class="col">
						<div class="home_content">
							<div class="home_title"><span>Halaman</span></div>
							<div class="breadcrumbs">
								<ul>
									<li><a href="#">Home</a></li>
									<li><?php echo $page['post_title'] ?></li>
								</ul>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
  <div class="services">
    <div class="container">
         <?php
      	 if (isset($_GET['page_id'])) {
           echo $page['post_content'];
      	 } else {
        ?>
           <!-- Add this ... -->
           <?php foreach ($pages as $page): ?>
           	<div class="post-preview">
           			<a href="./index.php?mode=page&page_id=<?php echo $page['post_id']; ?>"><h2 class="post-title"><?php echo $page['post_title'] ?></h2></a>
           					<span class="post-meta"><?php echo date("F j, Y ", strtotime($page["post_date"])); ?></span>
                     <p><?php echo $page['post_content']; ?></p>
           	</div>
           	<hr>
           <?php endforeach ?>

           <?php
           $totalPages = num_rows(query("SELECT * FROM website_posts WHERE post_type = 'page'"));
           $page = (isset($_GET['page'])) ? $_GET['page'] : 1;
           $lastPage = ceil($totalPages/10);
           ?>
           <!-- Pager -->
           <div class="clearfix">
             <?php if($page > 1) { ?>
               <a class="btn btn-primary float-left" href="index.php?page=<?php echo $page - 1; ?>">&larr; Newer</a>
             <?php } ?>
             <?php if($page < $lastPage) { ?>
               <a class="btn btn-primary float-right" href="index.php?page=<?php echo $page + 1; ?>">Older &rarr;</a>
             <?php } ?>
           </div>
         <?php
         }
         ?>
    </div>
  </div>
</div>

	<?php require_once(ROOT_PATH . '/modules/Website/themes/'.$theme.'/footer.php') ?>
