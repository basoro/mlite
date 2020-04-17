<?php require_once(ROOT_PATH . '/modules/Website/themes/'.$theme.'/header.php') ?>
<?php $post_id = isset($_GET['post_id'])?$_GET['post_id']:null; ?>
<?php if ($post_id) { $post = getPost($_GET['post_id']); } ?>
<?php $posts = getPublishedPosts(); ?>

<!-- Retrieve single post from database by id  -->
<?php
	if (isset($_GET['post_id'])) {
		$post = getPost($_GET['post_id']);
	}
?>

    <!-- Page Header -->
    <header class="masthead" style="background-image: url('<?php echo URL.'/modules/Website/themes/'.$theme; ?>/assets/img/home-bg.jpg')">
      <div class="overlay"></div>
      <div class="container">
        <div class="row">
          <div class="col-lg-8 col-md-10 mx-auto">
            <div class="site-heading">
              <h1><?php if ($post_id) { echo $post['post_title']; } else { echo 'Blog'; } ?></h1>
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
				if (isset($_GET['post_id'])) {
					?>
					<h2 class="post-title"><?php echo $post['post_title'] ?></h2>
					<span class="post-meta"><?php echo date("F j, Y ", strtotime($post["post_date"])); ?></span>
					<p><?php echo $post['post_content']; ?></p>
					<?php
				} else {
			 ?>
					<!-- Add this ... -->
					<?php foreach ($posts as $post): ?>
					 <div class="post-preview">
							 <a href="./index.php?mode=post&post_id=<?php echo $post['post_id']; ?>"><h2 class="post-title"><?php echo $post['post_title'] ?></h2></a>
									 <span class="post-meta"><?php echo date("F j, Y ", strtotime($post["post_date"])); ?></span>
										<p><?php echo $post['post_content']; ?></p>
					 </div>
					 <hr>
					<?php endforeach ?>

					<?php
					$totalPages = num_rows(query("SELECT * FROM website_posts WHERE post_type = 'post'"));
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
