<?php
if(!empty($_GET['lastPost'])) {
  $last_post_id = $_GET['lastPost'];
  //remove pageination- from last_post_id
  $remove_string = explode("-",$last_post_id);

  $last_post_id = $remove_string[1];
  include_once '../../../config.php';
  include_once '../../../init.php';
  include_once 'libs/smileys.php';
  include_once 'libs/security.php';
  $a = query("SELECT * FROM pegawai WHERE nik = '{$_SESSION['username']}'");
  $b = fetch_assoc($a);
  $action = isset($_GET['user'])?$_GET['user']:null;
  //query to fetch posts < last_post_id
  if(!$action) {
    $result = query("SELECT * FROM `posts` WHERE `pid` < $last_post_id ORDER BY `pid` DESC LIMIT 4");
  } else {
    $result = query("SELECT * FROM `posts` WHERE `username` = '".$_GET['user']."' AND `pid` < $last_post_id ORDER BY `pid` DESC LIMIT 4");
    //$result = query("SELECT * FROM `posts` WHERE `username` = '".$_GET['user']."' ORDER BY `pid` DESC LIMIT 4");
  }
  //$result = query("SELECT * FROM `posts` WHERE `pid` < $last_post_id ORDER BY `pid` DESC LIMIT 4");
  $total_result = num_rows($result);
  if($total_result > 0) {
    $al = 0;
    while($row = fetch_array($result)) {
      $post_id = $row['pid'];
    ?>
      <li class="<?php echo alignment($al); ?>" id="post-<?php echo $post_id; ?>"> <i class="pointer" id="pagination-<?php echo $post_id;?>"></i>
        <div class="unit">
          <!-- Story -->
          <div class="storyUnit">
            <div class="imageUnit">
              <a href="<?php echo URL; ?>/index.php?module=Userwall&page=index&user=<?php echo $row['username']; ?>">
                <?php
                  $a_wall = query("SELECT * FROM pegawai WHERE nik = '{$row['username']}'");
                  $b_wall = fetch_assoc($a_wall);
                  if($b_wall['photo'] == '') {
                    echo '<img src="'.URL.'/assets/images/no-photo.jpg" width="32px" alt="User" />';
                  } else {
                    echo '<img src="'.URLSIMRS.'/penggajian/'.$b_wall['photo'].'" width="32px" alt="User" />';
                  }
                ?>
              </a>
              <?php if($row['username'] == $_SESSION['username']) { ?>
              <p style="float:right; text-align:right;"><a href="javascript:;" class="post-delete" id="post_delete_<?php echo $post_id; ?>">X</a></p>
              <?php } ?>
              <div class="imageUnit-content">
                <h4><a href="<?php echo URL; ?>/index.php?module=Userwall&page=index&user=<?php echo $row['username']; ?>"><?php echo $b_wall['nama']; ?></a></h4>
                <p><?php echo timeAgo($row['date']);?></p>
              </div>
            </div>
            <p class="msg_wrap"><?php echo parse_smileys(make_clickable(nl2br(stripslashes($row['desc']))), URL.'/modules/Userwall/inc/assets/smileys/'); ?></p>
            <?php if(!empty($row['vid_url'])) { ?>
              <div class="embed-responsive embed-responsive-16by9"><iframe src="https://www.youtube.com/embed/<?php echo get_youtubeid($row['vid_url']);?>" frameborder="0" allowfullscreen></iframe></div>
            <?php } elseif(!empty($row['image_url'])) { ?>
              <img src="<?php echo URL;?>/modules/Userwall/inc/image.php/<?php echo $row['image_url'];?>?width=400&nocache&quality=100&image=/<?php echo DIR;?>modules/Userwall/inc/uploads/<?php echo $row['image_url'];?>">
            <?php } ?>
          </div>
          <div class="activity-comments">
            <ul id="CommentPosted<?php echo $post_id; ?>">
            <?php
            //fetch comments from comments table using post id
            $comments = query("SELECT * FROM `comments` WHERE `cpid`=$post_id ORDER BY `cid` ASC ");
            $total_comments = num_rows($comments);
            ?>
            <li class="show-all" id="show-all-<?php echo $post_id; ?>" <?php if($total_comments == 0) { ?> style="display:none" <?php } ?>><a href="javascript:;"><span id="comment_count_<?php echo $post_id;?>"><?php echo $total_comments;?></span> comments</a></li>

             <?php while($comt = fetch_array($comments)) { $comment_id = $comt['cid']; ?>
             <li id="li-comment-<?php echo $comment_id; ?>">
            <div class="acomment-avatar">
                    <a href="<?php echo URL; ?>/index.php?module=Userwall&user=<?php echo $comt['username']; ?>">
            					<?php
            					$a_wall = query("SELECT * FROM pegawai WHERE nik = '{$comt['username']}'");
            					$b_wall = fetch_assoc($a_wall);
            					if($b_wall['photo'] == '') {
            						echo '<img src="'.URL.'/assets/images/no-photo.jpg" width="32px" alt="User" />';
            					} else {
            						echo '<img src="'.URLSIMRS.'/penggajian/'.$b_wall['photo'].'" width="32px" alt="User" />';
            					}
            					?>
            				</a>
                          <?php if($comt['username'] == $_SESSION['username']) { ?>
             <p style="float:right; text-align:right; font-size:10px;"><a href="javascript:;" rel="<?php echo $post_id; ?>" class="comment-delete" id="comment_delete_<?php echo $comment_id; ?>">X</a></p>
            <?php } ?>
            </div>
            <div class="acomment-meta">
            <a href="<?php echo URL; ?>/index.php?module=Userwall&user=<?php echo $comt['username']; ?>"><?php echo $b_wall['nama']; ?></a>  <?php echo timeAgo($comt['commented_date']);?>
            </div>
            <div class="acomment-content"><p class="msg_wrap"><?php echo parse_smileys(make_clickable(nl2br(stripslashes($comt['comment']))), URL.'/modules/Userwall/inc/assets/smileys/'); ?></p></div></li>
            <?php } ?>
            </ul>
            <a href="javascript:;" class="acomment-reply" title="" id="acomment-comment-<?php echo $post_id; ?>">Write a comment..</a>
            <form  method="post" id="fb-<?php echo $post_id; ?>" class="ac-form">
            <div class="ac-reply-avatar">
            					<?php
            					$___a_wall = query("SELECT * FROM pegawai WHERE nik = '{$_SESSION['username']}'");
            					$___b_wall = fetch_assoc($___a_wall);
            					if($___b_wall['photo'] == '') {
            						echo '<img src="'.URL.'/assets/images/no-photo.jpg" width="30" height="30" alt="User" />';
            					} else {
            						echo '<img src="'.URLSIMRS.'/penggajian/'.$___b_wall['photo'].'" width="30" height="30" alt="User" />';
            					}
            					?>
            </div>
            <div class="ac-reply-content">
            <div class="ac-textarea">
            <textarea id="ac-input-<?php echo $post_id; ?>" class="ac-input" name="comment" style="height:40px;"></textarea>
            <input type="hidden" id="act-id-<?php echo $post_id; ?>" name="act_id" value="<?php echo $post_id; ?>" />
            </div>
            <input name="ac_form_submit" class="uibutton confirm live_comment_submit" title="fb-<?php echo $post_id; ?>" id="comment_id_<?php echo $post_id; ?>" type="button" value="Submit"> &nbsp; or <a href="javascript:;" class="comment_cancel" id="<?php echo $post_id; ?>">Cancel</a>
            </div>
            </form>
          </div>
        </div>
      </li>
      <?php
  		if($al == 2) {
        $al=0;
      } else {
        $al++;
      }
    }
  }
}
?>
