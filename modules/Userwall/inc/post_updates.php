<style>
p.msg_wrap { word-wrap:break-word; }
</style>
<?php
if(!empty($_POST['message'])) {
	include_once '../../../config.php';
	include_once '../../../init.php';
	include_once 'libs/security.php';
	include_once 'libs/smileys.php';

	$username = $_SESSION['username'];
	$message = clean($_POST['message']);
	$message = special_chars($message);
	$time = time();
	//getting image link
	if(!empty($_POST['pic_url'])) {
		$image = strip_tags($_POST['pic_url']);
	} else {
		$image = '';
	}

	//getting video link
	if(!empty($_POST['y_link'])) {
		$video = fix_url(strip_tags($_POST['y_link']));
	} else {
		$video = '';
	}

	//insert into wall table
	 $query = query("INSERT INTO `posts` (`username`, `desc`, `image_url`, `vid_url`,`date`) VALUES ('$username','$message', '$image', '$video','$time')");
	 $ins_id = mysqli_insert_id($connection);


$lft = array('left' => '0', 'right' => '1', 'highlight' => '2'); ?>
<li class="<?php echo array_rand($lft,1);?>" id="post-<?php echo $ins_id; ?>">
  <i class="pointer"></i>
  <div class="unit">

    <!-- Story -->
    <div class="storyUnit">
      <div class="imageUnit">
        <a href="#">
					<?php
					$a_wall = query("SELECT * FROM pegawai WHERE nik = '{$_SESSION['username']}'");
					$b_wall = fetch_assoc($a_wall);
					if($b_wall['photo'] == '') {
						echo '<img src="'.URL.'/assets/images/no-photo.jpg" width="32px" alt="User" />';
					} else {
						echo '<img src="'.URLSIMRS.'/penggajian/'.$b_wall['photo'].'" width="32px" alt="User" />';
					}
					?>
				</a>
         <p style="float:right; text-align:right;"><a href="javascript:;" class="post-delete" id="post_delete_<?php echo $ins_id;?>">X</a></p>
        <div class="imageUnit-content">

          <h4><a href="<?php echo URL; ?>/index.php?module=Userwall&page=index&user=<?php echo $_SESSION['username']; ?>"><?php echo $b_wall['nama']; ?></a></h4>
          <p>0 sec ago</p>

        </div>
    </div>

      <p class="msg_wrap"><?php echo parse_smileys(make_clickable(nl2br(stripslashes($message))), URL.'/modules/Userwall/assets/smileys/'); ?></p>
       <?php if(!empty($video)) { ?>
          <div class="embed-responsive embed-responsive-16by9"><iframe src="https://www.youtube.com/embed/<?php echo get_youtubeid($video);?>" frameborder="0" allowfullscreen></iframe></div>
          <?php } elseif(!empty($image)) { ?>
          <img class="img-responsive" src="<?php echo URL;?>/modules/Userwall/inc/image.php/<?php echo $image;?>?nocache&quality=100&image=/<?php echo DIR;?>modules/Userwall/inc/uploads/<?php echo $image;?>">
          <?php } ?>

    </div>

<!-- comment starts -->
<div class="activity-comments">
<ul id="CommentPosted<?php echo $ins_id; ?>">
<li class="show-all" id="show-all-<?php echo $ins_id; ?>" style="display:none"><a href="javascript:;"><span id="comment_count_<?php echo $ins_id; ?>">0</span> comments</a></li>
  <a href="javascript:;" class="acomment-reply" title="" id="acomment-comment-<?php echo $ins_id; ?>">
Write a comment..</a>
</ul>
<form  method="post" id="fb-<?php echo $ins_id; ?>" class="ac-form">
<div class="ac-reply-avatar">
  <?php
  if($b_wall['photo'] == '') {
    echo '<img src="'.URL.'/assets/images/no-photo.jpg" width="30" height="30" alt="User" />';
  } else {
    echo '<img src="'.URLSIMRS.'/penggajian/'.$b_wall['photo'].'" width="30" height="30" alt="User" />';
  }
  ?>
  </div>
<div class="ac-reply-content">
<div class="ac-textarea">
<textarea id="ac-input-<?php echo $ins_id; ?>" class="ac-input" name="comment" style="height:40px;"></textarea>
<input type="hidden" id="act-id-<?php echo $ins_id; ?>" name="act_id" value="<?php echo $ins_id; ?>" />
</div>
<input name="ac_form_submit" class="uibutton confirm live_comment_submit" title="fb-<?php echo $ins_id; ?>" id="comment_id_<?php echo $ins_id; ?>" type="button" value="Submit"> &nbsp; or <a href="javascript:;" class="comment_cancel" id="<?php echo $ins_id; ?>">Cancel</a>
</div>
</form>
</div>
<!-- comment ends -->
  </div>
</li>
<?php } ?>
