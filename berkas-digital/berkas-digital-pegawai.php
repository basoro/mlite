<div class="body">
    <?php
        if (isset($_POST['ok_berdig'])) {
            if($_FILES['file']['name']!='') {
                $tmp_name = $_FILES["file"]["tmp_name"];
                $namefile = $_FILES["file"]["name"];
                $explode = explode(".", $namefile);
                $ext = end($explode);
                move_uploaded_file($tmp_name,"../penggajian/pages/berkaspegawai/berkas/".$image_name);
                $lokasi_berkas = 'pages/berkaspegawai/berkas/'.$image_name;
                $insert_berkas = query("INSERT INTO berkas_pegawai VALUES('{$_SESSION['username']}','$date','{$_POST['masdig']}','$lokasi_berkas')");
                if($insert_berkas) {
                    set_message('Berkas digital pegawai telah ditersimpan.');
                    redirect("profil.php");
                }
            }
        }
    ?>
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="body">
                <div id="aniimated-thumbnials" class="list-unstyled row clearfix">
                    <?php
                        $sql_rad = query("select * from berkas_pegawai where nik= '{$_SESSION['username']}'");
                        $no=1;
                        while ($row_rad = fetch_array($sql_rad)) {
                            echo '<div class="col-lg-3 col-md-6 col-sm-12 col-xs-12">';
                            echo '<a href="'.URLSIMRS.'/berkasrawat/'.$row_rad[2].'" data-sub-html=""><img class="img-responsive thumbnail"  src="'.URLSIMRS.'/berkasrawat/'.$row_rad[2].'"></a>';
                            echo '</div>';
                            $no++;
                        }
                    ?>
                </div>
                <hr>
            </div>
            <div class="body">
                <form id="form_validation" name="berdigi" action="" method="POST"  enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="masdig">Kategori Berkas</label>
                        <select class="form-control" name="masdig">
                        <?php $query = query("SELECT * FROM master_berkas_pegawai");while ($a = fetch_array($query)) {?>
                            <option value="<?php echo $a['kode'];?>"><?php echo $a['kategori'];?></option>
                        <?php } ?>
                        </select>
                        <label for="namber">Berkas Pegawai</label>
                        <select name="namber" id="" class="form-control">
                            <option value=""></option>
                        </select>
                        <img id="image_upload_preview" width="200px" src="<?php echo URL; ?>/assets/images/upload_berkas.png" onclick="upload_berkas()" style="cursor:pointer;" />
                        <br/>
                        <input name="file" id="inputFile" type="file" style="display:none;"/>
                    </div>
                    <button type="submit" name="ok_berdig" value="ok_berdig" class="btn bg-indigo waves-effect" onclick="this.value=\'ok_berdig\'">UPLOAD BERKAS</button>
                </form>
            </div>
        </div>
    </div>
</div>
