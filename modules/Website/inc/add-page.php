            <?php
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
                    $sql = "INSERT INTO website_posts (post_id, post_title, post_content, post_date, post_cat_id, post_author, post_type, post_status) VALUES (NULL, '" . $_POST['post_title'] . "', '" . $_POST['post_content'] . "', NOW(), '', '" . $_SESSION['username'] . "', 'page', '" . $_POST['post_status'] . "')";
                    $update = query($sql);
                    if($update) {
                        set_message('Page added!');
                        redirect(URL.'/index.php?module=Website&page=pages');
                    }

                }
            }
            ?>
            <div class="row clearfix">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="card">
                        <div class="header">
                            <h2>
                                New Page
                            </h2>
                        </div>
                        <div class="body">
                            <form action="" method="POST">
                                <label class="form-label">Page Title</label>
                                <div class="form-group">
                                    <div class="form-line">
                                        <input type="text" name="post_title" id="post_title" class="form-control">
                                    </div>
                                </div>
                                <label class="form-label">Page Content</label>
                                <div class="form-group">
                                    <div class="form-line">
                                        <textarea rows="6" id="tinymce" name="post_content" class="form-control no-resize"></textarea>
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
