            <div class="block-header">
              <h2>ADD POSTS</h2>
            </div>
            <?php
            if($_SERVER['REQUEST_METHOD'] == "POST") {

                if($_POST['post_title'] == "") {
                    $errors[] = 'Your post title is empty';
                }

                if($_POST['post_cat_id'] == "") {
                    $errors[] = 'Your post category is empty';
                }

                if($_POST['post_content'] == "") {
                    $errors[] = 'Your post content is empty';
                }

                if (!empty($errors)) {
                    foreach($errors as $error) {
                        echo validation_errors($error);
                    }
                } else {
                    $sql = "INSERT INTO website_posts (post_id, post_title, post_content, post_date, post_cat_id, post_author, post_type, post_image, post_status) VALUES (NULL, '" . $_POST['post_title'] . "', '" . $_POST['post_content'] . "', NOW(), '" . $_POST['post_cat_id'] . "', '" . $_SESSION['username'] . "', 'post', '" . $_POST['post_image'] . "', '" . $_POST['post_status'] . "')";
                    $update = query($sql);
                    if($update) {
                        set_message('Post added!');
                        redirect(URL.'/index.php?module=Website&page=posts');
                    }

                }
            }
            ?>
            <div class="row clearfix">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="card">
                        <div class="header">
                            <h2>
                                New Post
                            </h2>
                        </div>
                        <div class="body">
                            <form action="" method="POST">
                                <label class="form-label">Post Title</label>
                                <div class="form-group">
                                    <div class="form-line">
                                        <input type="text" name="post_title" id="post_title" class="form-control">
                                    </div>
                                </div>
                                <label class="form-label">Post Category</label>
                                <div class="form-group">
                                    <div class="form-line">
                                        <select name="post_cat_id" class="form-control show-tick">
                                        <?php
                                        $sql = "SELECT * FROM website_categories";
                                        $query = query($sql);
                                        while ($row = fetch_array($query)) {
                                            echo '<option value="'.$row['cat_id'].'">'.$row['cat_name'].'</option>';
                                        }
                                        ?>
                                        </select>
                                    </div>
                                </div>
                                <label class="form-label">Post Content</label>
                                <div class="form-group">
                                    <div class="form-line">
                                        <textarea rows="6" id="tinymce" name="post_content" class="form-control no-resize"></textarea>
                                    </div>
                                </div>
                                <label class="form-label">Post Image</label>
                                <div class="form-group">
                                    <div class="form-line">
                                        <input type="hidden" name="post_image" id="post_image" class="form-control">
                                        <a class="btn iframe-btn" type="button" href="./modules/Website/tinymce/plugins/filemanager/dialog.php?type=1&field_id=post_image">Pilih Gambar</a>
                                        <br><br>
                                        <div id="cont-img"><img id="image_preview" src="" style="display:none;" width="200" /></div>
                                    </div>
                                </div>
                                <label class="form-label">Status</label>
                                <div class="form-group">
                                    <div class="form-line">
                                        <?php echo enumDropdown('website_posts', 'post_status', '&nbsp;', ''); ?>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary m-t-15 waves-effect">SUBMIT</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
