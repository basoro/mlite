<style>
p.msg_wrap { word-wrap:break-word; }
</style>
<?php
if(!empty($_POST['comment']) && !empty($_POST['act_id'])) {
	include_once '../../../config.php';
	include_once '../../../init.php';
	include_once 'libs/security.php';
	include_once 'libs/smileys.php';

	//clean the comment message
	$username = $_SESSION['username'];
	$comment = clean($_POST['comment']);
	$comment = special_chars($comment);
	$time = time();
	$post_id = $_POST['act_id'];

	//insert into wall table
	 $query = query("INSERT INTO `comments` (`username`, `comment`, `cpid`, `commented_date`) VALUES ('$username', '$comment', '$post_id','$time')");
	 $ins_id = mysqli_insert_id($connection);
	?>
	<li id="li-comment-<?php echo $ins_id; ?>">
	  <?php $d = fetch_array(query("SELECT `photo` FROM `pegawai` WHERE `nik` = '{$_SESSION['username']}'")); ?>
	  <div class="acomment-avatar">
	    <a href="<?php echo URL; ?>/index.php?module=Userwall&user=<?php echo $_SESSION['username']; ?>" rel="nofollow">
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
			<p style="float:right; text-align:right; font-size:10px;"><a href="javascript:;" rel="<?php echo $post_id; ?>" class="comment-delete" id="comment_delete_<?php echo $ins_id; ?>">X</a></p>
		</div>
		<div class="acomment-meta">
			<a href="<?php echo URL; ?>/index.php?module=Userwall&user=<?php echo $_SESSION['username']; ?>"><?php echo $b_wall['nama']; ?></a>  0 sec ago
		</div>
		<div class="acomment-content">
			<p class="msg_wrap">
				<?php echo parse_smileys(make_clickable(nl2br(stripslashes($comment))), URL.'/modules/Userwall/inc/assets/smileys/'); ?>
			</p>
		</div>
	</li>
<?php } ?>
