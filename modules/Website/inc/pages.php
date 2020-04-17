<?php display_message(); ?>
<?php if(!isset($_GET['action'])) { ?>
<div class="row clearfix">
    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
        <div class="card">
            <div class="header">
                <a href="<?php echo URL; ?>/index.php?module=Website&page=add_page" class="btn btn-primary" style="float:right;">Tambah</a>
                <h2>
                    Page List
                </h2>
            </div>
            <div class="body">
                <div class="table-responsive">
                    <table class="table table-hover display">
                        <thead>
                            <tr>
                                <th width="40">#</th>
                                <th>Title</th>
                                <th>Date</th>
                                <th>Author</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                          <?php
                          $sql = "SELECT * FROM website_posts WHERE post_type = 'page' ORDER BY post_id ASC";
                          $query = query($sql);
                          $no = '1';
                          while ($row = fetch_array($query)) {
                            echo '<tr>';
                            echo '<th scope="row">'.$no.'</th>';
                            echo '<td><a href="'.URL.'/index.php?module=Website&page=pages&action=edit&post_id='.$row['post_id'].'">'.$row['post_title'].'</a></td>';
                            echo '<td>'.date("Y-m-d ", strtotime($row['post_date'])).'</td>';
                            echo '<td>'.$row['post_author'].'</td>';
                            echo '<td>'.$row['post_status'].'</td>';
                            echo '</tr>';
                          $no++;
                          }
                          ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php } ?>
<?php
if(isset($_GET['action']) == "edit" ) {

    $sql = "SELECT * FROM website_posts WHERE post_id = '".$_GET['post_id']."'";
    $query = query($sql);
    $result = fetch_assoc($query);

    if($_SERVER['REQUEST_METHOD'] == "POST") {

      if($_POST['post_title'] == "") {
          $errors[] = 'Your post title is empty';
      }

      if($_POST['post_content'] == "") {
          $errors[] = 'Your post content is empty';
      }

      if (!empty($errors)) {
          foreach($errors as $error) {
              echo validation_errors($error);
          }
      } else {
          $sql = "UPDATE website_posts SET post_title = '" . $_POST['post_title'] . "', post_content = '" . $_POST['post_content'] . "', post_status = '" . $_POST['post_status'] . "' WHERE post_id = '" . $_GET['post_id'] . "'";
          $update = query($sql);
          if($update) {
              set_message('Post updated!');
              redirect(URL.'/index.php?module=Website&page=pages&action=edit&post_id='.$_GET['post_id']);
          }
      }
    }

?>

<div class="row clearfix">
    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
        <div class="card">
            <div class="header">
                <h2>
                    Edit Page
                </h2>
            </div>
            <div class="body">
                <form action="" method="POST">
                    <label class="form-label">Post Title</label>
                    <div class="form-group">
                        <div class="form-line">
                            <input type="text" name="post_title" id="post_title" value="<?php echo $result['post_title']; ?>" class="form-control">
                        </div>
                    </div>
                    <label class="form-label">Post Content</label>
                    <div class="form-group">
                        <div class="form-line">
                            <textarea rows="6" id="tinymce" name="post_content" class="form-control no-resize"><?php echo $result['post_content']; ?></textarea>
                        </div>
                    </div>
                    <label class="form-label">Status</label>
                    <div class="form-group">
                        <div class="form-line">
                          <select name="post_status" id="post_status" data-width="100%">
                            <option value="draft" <?php if($result['post_status'] == 'draft') echo 'selected'; ?>>draft</option>
                            <option value="publish" <?php if($result['post_status'] == 'publish') echo 'selected'; ?>>publish</option>
                            <option value="trash" <?php if($result['post_status'] == 'trash') echo 'selected'; ?>>trash</option>
                            <option value="home" <?php if($result['post_status'] == 'home') echo 'selected'; ?>>home</option>
                          </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary m-t-15 waves-effect">SUBMIT</button> <a href="<?php echo URL; ?>/index.php?module=Website&page=pages&action=delete&post_id=<?php echo $_GET['post_id']; ?>" class="trash" onclick="return confirm(\'Anda yakin ingin menghapus?\');"><button type="button" class="btn btn-danger m-t-15 waves-effect">HAPUS</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php } ?>
<?php
if($_GET['action'] == "delete") {

    $sql = "SELECT * FROM website_posts WHERE post_id = '".$_GET['post_id']."'";
    $result = query($sql);

    if(num_rows($result) == 1) {
         $sql = "DELETE FROM website_posts WHERE post_id='".$_GET['post_id']."'";
         $del = query($sql);

         if($del){
             set_message('Postingan telah dihapus!');
             redirect(URL.'/index.php?module=Website&page=pages');
         }else{
             set_message('Tidak ada postingan yang dihapus!');
             redirect(URL.'/index.php?module=Website&page=pages');
         }
    }else{
         set_message('Postingan tidak ditemukan!');
         redirect(URL.'/index.php?module=Website&page=pages');
    }
}
?>
