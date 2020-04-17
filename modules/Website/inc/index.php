            <div class="block-header">
              <h2>DASHBOARD WEBSITE</h2>
            </div>
            <!-- Dashboard -->
            <div class="clearfix alert alert-dismissible" role="alert" style="background: #fff; color: #000 !important; margin-bottom: 40px;"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><i class="material-icons" style="color: #000 !important;">close</i></button>
                <h3>
                    Selamat Datang di <?php echo $dataSettings['nama_instansi']; ?>
                </h3>
                <br>
                <p>
                  Ini adalah halaman <i>dashboard</i> untuk administrasi setelah anda login.
                  Di halaman ini ada <i>widget</i> statistik, daftar postingan terakhir dan postingan cepat.
                  Disebelah kiri ada <i>sidebar menu</i>, digunakan untuk melihat, membuat, edit dan menghapus konten.
                </p>
                <br>
                <p>Jika ada error silahkan hubungi pembuatnya.</p>
            </div>
            <!-- #END# Dashboard -->
            <!-- Stat Widgets -->
            <div class="row clearfix">
                <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                    <div class="info-box bg-pink hover-expand-effect">
                        <div class="icon">
                            <i class="material-icons">playlist_add_check</i>
                        </div>
                        <div class="content">
                            <div class="text">Berita</div>
                            <div class="number count-to" data-from="0" data-to="<?php echo num_rows(query("SELECT post_id FROM website_posts WHERE post_type = 'post'"));?>" data-speed="15" data-fresh-interval="20"></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                    <div class="info-box bg-cyan hover-expand-effect">
                        <div class="icon">
                            <i class="material-icons">help</i>
                        </div>
                        <div class="content">
                            <div class="text">Informasi</div>
                            <div class="number count-to" data-from="0" data-to="<?php echo num_rows(query("SELECT post_id FROM website_posts WHERE post_type = 'page'"));?>" data-speed="1000" data-fresh-interval="20"></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                    <div class="info-box bg-light-green hover-expand-effect">
                        <div class="icon">
                            <i class="material-icons">forum</i>
                        </div>
                        <div class="content">
                            <div class="text">Kategori</div>
                            <div class="number count-to" data-from="0" data-to="<?php echo num_rows(query("SELECT cat_id FROM website_categories"));?>" data-speed="1000" data-fresh-interval="20"></div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- #END# Stat Widgets -->
            <div class="row clearfix">
                <!-- Recent Post -->
                <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                    <div class="card">
                        <div class="header">
                            <h2>RECENT POSTS</h2>
                        </div>
                        <div class="body">
                            <div class="table-responsive">
                                <table class="table table-hover dashboard-task-infos">
                                    <thead>
                                        <tr>
                                            <th width="40">#</th>
                                            <th>Title</th>
                                            <th>Category</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                      <?php
                                      $sql = "SELECT a.post_id, a.post_title, b.cat_name FROM website_posts a, website_categories b WHERE a.post_cat_id = b.cat_id AND a.post_type = 'post' ORDER BY a.post_id ASC LIMIT 5";
                                      $query = query($sql);
                                      $no = '1';
                                      while ($row = fetch_array($query)) {
                                        echo '<tr>';
                                        echo '<td>'.$no.'</td>';
                                        echo '<td><a href="posts.php?action=edit&post_id='.$row['post_id'].'">'.$row['post_title'].'</a></td>';
                                        echo '<td>'.$row['cat_name'].'</td>';
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
                <!-- #END# Recent Post -->
                <!-- Quick Post -->
                <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                    <div class="card">
                        <div class="header">
                            <h2>QUICK POST</h2>
                        </div>
                        <div class="body">
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
                                  $sql = "INSERT INTO website_posts (post_id, post_title, post_content, post_date, post_cat_id, post_author, post_type) VALUES (NULL, '" . $_POST['post_title'] . "', '" . $_POST['post_content'] . "', NOW(), '1', '" . $_SESSION['user_id'] . "', 'post')";
                                  $update = query($sql);
                                  if($update) {
                                      set_message('Post added!');
                                      redirect('posts.php');
                                  }

                              }
                          }
                          ?>
                            <form action="" method="POST">
                                <input type="hidden" name="post_category" id="post_category" value="1" class="form-control">
                                <div class="form-group form-float">
                                    <div class="form-line">
                                        <input type="text" name="post_title" id="post_title" class="form-control">
                                        <label class="form-label">Post Title</label>
                                    </div>
                                </div>
                                <div class="form-group form-float">
                                    <div class="form-line">
                                        <textarea rows="6" name="post_content" class="form-control no-resize"></textarea>
                                        <label class="form-label">Post Content</label>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary m-t-15 waves-effect">SUBMIT</button>
                            </form>
                        </div>
                    </div>
                </div>
                <!-- #END# Quick Post -->
            </div>
