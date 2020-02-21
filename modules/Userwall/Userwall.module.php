<?php
if(!defined('IS_IN_MODULE')) { die("NO DIRECT FILE ACCESS!"); }

class Userwall {
  function index() {
    $get_user = isset($_GET['user'])?$_GET['user']:null;
    if(num_rows(query("SHOW TABLES LIKE 'posts'")) !== 1) {
      echo '<div class="alert bg-pink alert-dismissible text-center">';
      echo '<p class="lead">Belum terinstall Database Userwall (Facebook Like)</p>';
      echo '<a href="'.URL.'/index.php?module=Userwall&page=install" class="btn btn-lg btn-primary m-t-20" style="color:#fff;">Install Sekarang</a>';
      echo '</div>';
    } else {
      $a = query("SELECT * FROM pegawai WHERE nik = '{$_SESSION['username']}'");
      $b = fetch_assoc($a);
      $a_wall = query("SELECT * FROM pegawai WHERE nik = '{$get_user}'");
      $b_wall = fetch_assoc($a_wall);
      $action = isset($_GET['user'])?$_GET['user']:null;
      include 'inc/libs/smileys.php';
?>
      <?php display_message(); ?>
      <div class="row container-userwall">
        <ol class="timeline clearfix">
          <li class="right"> <i class="pointer"></i>
            <div class="unit">
              <!-- Story -->
              <div class="storyUnit">
                <div class="imageUnit">
                  <a href="<?php echo URL; ?>/index.php?module=Userwall&user=<?php echo $_SESSION['username']; ?>">
                    <?php
                      $_a_wall = query("SELECT * FROM pegawai WHERE nik = '{$_SESSION['username']}'");
                      $_b_wall = fetch_assoc($_a_wall);
                      if($_b_wall['photo'] == '') {
                        echo '<img src="'.URL.'/assets/images/no-photo.jpg" width="32px" alt="User" />';
                      } else {
                        echo '<img src="'.URLSIMRS.'/penggajian/'.$_b_wall['photo'].'" width="32px" alt="User" />';
                      }
                    ?>
                  </a>
                  <div class="imageUnit-content">
                    <h4><a href="<?php echo URL; ?>/index.php?module=Userwall&user=<?php echo $_SESSION['username']; ?>"><?php echo $b['nama']; ?></a></h4>
                    <p>Follow me</p>
                  </div>
                </div>
                <p> Features:</p>
                <ul>
                  <li><span style="line-height: 15px;">Share Updates | Upload pictures | Share Youtube Videos | Post Hightlights</span></li>
      	          <li>Support Smileys</li>
                  <li>FB like Commenting System</li>
                </ul>
              </div>
              <!-- / Story -->
            </div>
          </li>
          <li class="spine"> <a href="#" title=""></a></li><br>
          <li class="left"> <i class="pointer"></i>
            <div class="unit" id="tabs">
              <ul class="actions">
                <li><a href="#tabs-1"><i class="icon icon-status"></i>Status</a></li>
                <li><a href="#tabs-2"><i class="icon icon-photo"></i>Add Picture</a></li>
                <li><a href="#tabs-3"><i class="icon icon-video"></i>Add Video</a></li>
              </ul>
              <span class="ajax_indi"><img src="<?php echo URL;?>/modules/Userwall/inc/assets/images/loader.gif"></span>
              <!-- Units -->
              <div class="actionUnits" id="tabs-1">
               <form id="npost" name="npost">
                <p class="formUnit" id="Status"> <i class="active"></i>
                  <textarea name="message" placeholder="What's on your mind?" id="message" cols="30" rows="3"></textarea>
                <ol class="controls clearfix">
                  <li class="post">
                    <input class="uibutton confirm fb_submit" type="button" title="npost" value="Post">
                  </li>
                </ol>
                </p>
                </form>
              </div>
              <div class="actionUnits" id="tabs-2">
               <form id="picpost" name="picpost">
                <p class="formUnit"> <i class="active_pic"></i>
                  <textarea name="message" placeholder="What's on your mind?" id="pmessage" cols="30" rows="3"></textarea>
                  <input type="hidden" name="pic_url" id="pic_url">
                  <button class="uibutton" type="button" id="upload_pic">Upload Picture</button><span id="statuss"></span>
                  <ol class="controls clearfix">
                    <li class="post">
                      <input class="uibutton confirm fb_submit"  type="button" value="Post" title="picpost">
                    </li>
                  </ol>
                </p>
                <p id="files"></p>
               </form>
              </div>
              <div class="actionUnits" id="tabs-3">
               <form id="vidpost" name="vidpost">
                <p class="formUnit" id="Status"> <i class="active_vid"></i>
                  <textarea name="message" placeholder="Video Description" id="vmessage" cols="30" rows="3"></textarea>
                  <input type="text" name="y_link" style="width:100%" id="y_link" placeholder="Enter Youtube Url - www.youtube.com/watch?v=rdmycu13Png">
                  <ol class="controls clearfix">
                    <li class="post">
                      <input class="uibutton confirm fb_submit" type="button" value="Post" title="vidpost">
                    </li>
                  </ol>
                </p>
               </form>
              </div>
              <!-- / Units -->
            </div>
          </li>
        </ol>
        <style>
        p.msg_wrap { word-wrap:break-word; }
        </style>
        <ol class="timeline clearfix" id="tupdate">
          <?php
            include 'inc/libs/security.php';
            if(!$action) {
              $result = query("SELECT * FROM `posts` ORDER BY `pid` DESC LIMIT 4");
            } else {
              $result = query("SELECT * FROM `posts` WHERE `username` = '".$_GET['user']."' ORDER BY `pid` DESC LIMIT 4");
            }
          ?>
          <?php
          $al = 0;
          while($row = fetch_array($result)) {
            $post_id = $row['pid'];
          ?>
            <li class="<?php echo alignment($al); ?>" id="post-<?php echo $post_id; ?>"> <i class="pointer" id="pagination-<?php echo $post_id;?>"></i>
              <div class="unit">
                <!-- Story -->
                <div class="storyUnit">
                  <div class="imageUnit">
                    <a href="<?php echo URL; ?>/index.php?module=Userwall&user=<?php echo $row['username']; ?>">
                      <?php
                        $_a_wall = query("SELECT * FROM pegawai WHERE nik = '{$row['username']}'");
                        $_b_wall = fetch_assoc($_a_wall);
                        if($_b_wall['photo'] == '') {
                          echo '<img src="'.URL.'/assets/images/no-photo.jpg" width="32px" alt="User" />';
                        } else {
                          echo '<img src="'.URLSIMRS.'/penggajian/'.$_b_wall['photo'].'" width="32px" alt="User" />';
                        }
                      ?>
                    </a>
                    <?php if($row['username'] == $_SESSION['username']) { ?>
                      <p style="float:right; text-align:right;"><a href="javascript:;" class="post-delete" id="post_delete_<?php echo $post_id; ?>">X</a></p>
                    <?php } ?>
                    <div class="imageUnit-content">
                      <h4><a href="<?php echo URL; ?>/index.php?module=Userwall&user=<?php echo $row['username']; ?>"><?php echo $_b_wall['nama']; ?></a></h4>
                      <p><?php echo timeAgo($row['date']);?></p>
                    </div>
                  </div>
                  <p class="msg_wrap"><?php echo parse_smileys(make_clickable(nl2br(stripslashes($row['desc']))), URL.'/modules/Userwall/inc/assets/smileys/'); ?></p>
                  <?php if(!empty($row['vid_url'])) { ?>
                    <div class="embed-responsive embed-responsive-16by9"><iframe src="https://www.youtube.com/embed/<?php echo get_youtubeid($row['vid_url']);?>" frameborder="0" allowfullscreen></iframe></div>
                  <?php } elseif(!empty($row['image_url'])) { ?>
                    <img class="img-responsive" src="<?php echo URL;?>/modules/Userwall/inc/image.php/<?php echo $row['image_url'];?>?nocache&quality=100&image=/<?php echo DIR;?>modules/Userwall/inc/uploads/<?php echo $row['image_url'];?>">
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
                  <?php $c = fetch_array(query("SELECT `photo` FROM `pegawai` WHERE `nik` = '{$comt['username']}'")); ?>
                  <li id="li-comment-<?php echo $comment_id; ?>">
                  <div class="acomment-avatar">
                    <a href="<?php echo URL; ?>/index.php?module=Userwall&user=<?php echo $comt['username']; ?>" rel="nofollow">
                    <?php
                      $__a_wall = query("SELECT * FROM pegawai WHERE nik = '{$comt['username']}'");
                      $__b_wall = fetch_assoc($__a_wall);
                      if($__b_wall['photo'] == '') {
                      echo '<img src="'.URL.'/assets/images/no-photo.jpg" width="32px" alt="User" />';
                      } else {
                      echo '<img src="'.URLSIMRS.'/penggajian/'.$__b_wall['photo'].'" width="32px" alt="User" />';
                      }
                      ?>
                    </a>
                    <?php if($comt['username'] == $_SESSION['username']) { ?>
                    <p style="float:right; text-align:right; font-size:10px;"><a href="javascript:;" rel="<?php echo $post_id; ?>" class="comment-delete" id="comment_delete_<?php echo $comment_id; ?>">X</a></p>
                      <?php } ?>
                  </div>
                  <div class="acomment-meta">
                    <a href="<?php echo URL; ?>/index.php?module=Userwall&user=<?php echo $comt['username']; ?>"><?php echo $__b_wall['nama']; ?></a>  <?php echo timeAgo($comt['commented_date']);?>
                  </div>
                  <div class="acomment-content"><p class="msg_wrap"><?php echo parse_smileys(make_clickable(nl2br(stripslashes($comt['comment']))), URL.'/modules/Userwall/inc/assets/smileys/'); ?></p></div></li>
                  <?php } ?>
                  </ul>
                  <a href="javascript:;" class="acomment-reply" title="" id="acomment-comment-<?php echo $post_id; ?>">
                  Write a comment..</a>
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
                <!-- / Units -->
              </div>
            </li>
            <?php
        		if($al == 2) {
              $al=0;
            } else {
              $al++;
            }
          } ?>
        </ol>
      </div>
<?php
    }
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
        set_message ('Table created successfully.');
        redirect ('./index.php?module=Userwall&page=index');
    } else{
        echo "ERROR: Could not able to execute $sql. " . mysqli_error($con);
    }
  }
}
?>
