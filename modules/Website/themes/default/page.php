<?php require_once(ROOT_PATH . '/modules/Website/themes/'.$theme.'/header.php') ?>
<?php $page_id = isset($_GET['page_id'])?$_GET['page_id']:null; ?>
<?php if($page_id) { $page = getPage($_GET['page_id']); } ?>
<?php $pages = getPublishedPages(); ?>

    <!-- Retrieve single post from database by id  -->
    <?php
    	if (isset($_GET['page_id'])) {
    		$page = getPage($_GET['page_id']);
    	}
    ?>

    <!-- Page Header -->
    <header class="masthead" style="background-image: url('<?php echo URL.'/modules/Website/themes/'.$theme; ?>/assets/img/about-bg.jpg')">
      <div class="overlay"></div>
      <div class="container">
        <div class="row">
          <div class="col-lg-8 col-md-10 mx-auto">
            <div class="page-heading">
              <h1><?php if($page_id) { echo $page['post_title']; } else { echo 'Halaman'; } ?></h1>
            </div>
          </div>
        </div>
      </div>
    </header>


<!-- Post Content -->
<article>
	<div class="container">
		<div class="row">
			<div class="col-lg-8 col-md-10 mx-auto">
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
</article>


	<hr>

<?php require_once(ROOT_PATH . '/modules/Website/themes/'.$theme.'/footer.php') ?>
