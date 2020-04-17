            <?php display_message(); ?>
            <div class="row clearfix">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="card">
                        <div class="header">
                            <h2>
                                Categories List
                            </h2>
                        </div>
                        <div class="body table-responsive">
                            <table id="datatable" class="table table-hover">
                                <thead>
                                    <tr>
                                        <th width="40">#</th>
                                        <th>Category Name</th>
                                        <th>Category Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                $sql = "SELECT * FROM website_categories ORDER BY cat_id ASC";
                                $query = query($sql);
                                $no = '1';
                                while ($row = fetch_array($query)) {
                                  echo '<tr>';
                                  echo '<td>'.$no.'</th>';
                                  echo '<td><a href="'.URL.'/index.php?module=Website&page=categories&action=edit&cat_id='.$row['0'].'">'.$row['1'].'</a></td>';
                                  echo '<td>'.$row['2'].'</th>';
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
            <?php

                $sql = "SELECT * FROM website_categories WHERE cat_id = '".$_GET['cat_id']."'";
                $query = query($sql);
                $result = fetch_assoc($query);

                if($_SERVER['REQUEST_METHOD'] == "POST") {

                    if($_POST['cat_name'] == "") {
                        $errors[] = 'Your category is empty';
                    }

                    if (!empty($errors)) {
                        foreach($errors as $error) {
                            echo validation_errors($error);
                        }
                    } else {
                        $get_cat = fetch_assoc(query("SELECT * FROM website_categories WHERE cat_id = '".$_POST['cat_id']."'"));

                        if($get_cat > 0) {
                          $sql = "UPDATE website_categories SET cat_name = '" . $_POST['cat_name'] . "', cat_description = '" . $_POST['cat_description'] . "' WHERE cat_id = '" . $_POST['cat_id'] . "'";
                        } else {
                          $sql = "INSERT INTO website_categories (cat_id, cat_name, cat_description) VALUES (NULL, '" . $_POST['cat_name'] . "', '" . $_POST['cat_description'] . "')";
                        }
                        $update = query($sql);

                        if($update) {
                            set_message('Category Updated!');
                            redirect(URL.'/index.php?module=Website&page=categories');
                        }

                    }
                }

            ?>

            <div class="row clearfix">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="card">
                        <div class="header">
                            <h2>
                                <?php if($_GET['action']) { ?>Edit<?php } else { ?>Add<?php } ?> Category
                            </h2>
                        </div>
                        <div class="body">
                            <form action="" method="POST">
                                <input type="hidden" id="cat_id" name="cat_id" class="form-control" value="<?php echo $result['cat_id']; ?>">
                                <label for="category">Category</label>
                                <div class="form-group">
                                    <div class="form-line">
                                        <input type="text" id="cat_name" name="cat_name" class="form-control" value="<?php echo $result['cat_name']; ?>">
                                    </div>
                                </div>
                                <label class="form-label">Category Description</label>
                                <div class="form-group">
                                    <div class="form-line">
                                        <textarea rows="6" name="cat_description" class="form-control no-resize"><?php echo $result['cat_description']; ?></textarea>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary m-t-15 waves-effect">SUBMIT</button> <?php if($_GET['action']) { ?> <a href="<?php echo URL; ?>/index.php?module=Website&page=categories&action=delete&cat_id=<?php echo $_GET['cat_id']; ?>" class="trash" onclick="return confirm(\'Anda yakin ingin menghapus?\');"><button type="button" class="btn btn-danger m-t-15 waves-effect">HAPUS</button><?php } ?>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php
            if($_GET['action'] == "delete") {

        	      $sql = "SELECT * FROM website_categories WHERE cat_id = '".$_GET['cat_id']."'";
        	      $result = query($sql);

                if(num_rows($result) == 1 && $_GET['cat_id'] == '1') {
                     set_message('Kategori tidak dapat dihapus!');
                     redirect(URL.'/index.php?module=Website&page=categories');
        	      } else if(num_rows($result) == 1 && $_GET['cat_id'] !== '1') {
        	      //if(num_rows($result) == 1) {
        	           $sql = "DELETE FROM website_categories WHERE cat_id = '".$_GET['cat_id']."'";
        	           $del = query($sql);

        	           if($del){
                         $sql = "UPDATE website_posts SET post_cat_id = '1' WHERE post_cat_id = '".$_GET['cat_id']."'";
          	             $update = query($sql);
        		             set_message('Kategori telah dihapus!<br>Semua kategori postingan yang dihapus adalah <b>Tak Berkategori</b>.');
                         redirect(URL.'/index.php?module=Website&page=categories');
        	           }else{
        		             set_message('Tidak ada kategori yang dihapus!');
                         redirect(URL.'/index.php?module=Website&page=categories');
        	           }
        	      }else{
        	           set_message('Kategori tidak ditemukan!');
                     redirect(URL.'/index.php?module=Website&page=categories');
        	      }
            }
            ?>
