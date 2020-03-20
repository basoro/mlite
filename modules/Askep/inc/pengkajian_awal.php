<?php
/***
* SIMRS Khanza Lite from version 0.1 Beta
* About : Porting of SIMRS Khanza by Windiarto a.k.a Mas Elkhanza as web and mobile app.
* Last modified: 02 Pebruari 2018
* Author : drg. Faisol Basoro
* Email : drg.faisol@basoro.org
* Licence under GPL
***/

if(isset($_GET['no_rawat'])) {
    //$_sql = "SELECT a.no_rkm_medis, a.no_rawat, b.nm_pasien, b.umur FROM reg_periksa a, pasien b WHERE a.no_rkm_medis = b.no_rkm_medis AND a.no_rawat = '$_GET[no_rawat]'";
    $_sql = "SELECT a.*, b.no_rawat FROM pasien a, reg_periksa b WHERE a.no_rkm_medis = b.no_rkm_medis AND b.no_rawat = '$_GET[no_rawat]'";
    $found_pasien = query($_sql);
    if(num_rows($found_pasien) == 1) {
	     while($row = fetch_array($found_pasien)) {
	        $no_rkm_medis  = $row['no_rkm_medis'];
          $no_rawat	     = $row['no_rawat'];
	        $nm_pasien     = $row['nm_pasien'];
	        $umur          = $row['umur'];
          $alamat        = $row['alamat'];
	     }
    } else {
	     redirect ('pasien-ranap.php');
    }
}
?>
                    <div class="card">
                        <div class="header">
                            <h2>
                                Pengkajian Awal Keperawatan
                            </h2>
                        </div>
                        <?php
                        $action = isset($_GET['action'])?$_GET['action']:null;
                        if(!$action){
                        ?>
                        <div class="body">
                            <table class="table responsive table-bordered table-striped table-hover display nowrap" width="100%">
                                <thead>
                                    <tr>
                                        <th>Nama</th>
                                        <th>Nomer MR</th>
                                        <th>Kamar</th>
                                        <th>Bed</th>
                                        <th>Tanggal Masuk</th>
                                        <th>Cara Bayar</th>
                                        <th>Aksi</th>
                                     </tr>
                                </thead>
                                <tbody>
                                <!-- This query based on Adly's (Adly Hidayat S.KOM) query. Thanks bro -->
                                <?php
                                $sql = "
                                	SELECT
                                		pasien.nm_pasien,
                                    	reg_periksa.no_rkm_medis,
                                    	bangsal.nm_bangsal,
                                    	kamar_inap.kd_kamar,
                                    	kamar_inap.tgl_masuk,
                                    	penjab.png_jawab,
                                    	reg_periksa.no_rawat
                                    FROM
                                    	kamar_inap,
                                        reg_periksa,
                                        pasien,
                                        bangsal,
                                        kamar,
                                        penjab
                                    WHERE
                                    	kamar_inap.no_rawat = reg_periksa.no_rawat
                                    AND
                                    	reg_periksa.no_rkm_medis = pasien.no_rkm_medis
                                    AND
                                    	kamar_inap.kd_kamar = kamar.kd_kamar
                                    AND
                                    	kamar.kd_bangsal = bangsal.kd_bangsal
                                    AND
                                    	kamar_inap.stts_pulang = '-'
                                    AND
                                    	reg_periksa.kd_pj = penjab.kd_pj
                                ";
                                if($role == 'Paramedis_Ranap') {
                                	$sql .= " AND bangsal.kd_bangsal = '$jenis_poli'";
                                }
                                $sql .= " ORDER BY kamar_inap.kd_kamar ASC";
                                $result = query($sql);
                                while($row = fetch_array($result)) {
                                  $get_no_rawat = $row['6'];
                                ?>
                                    <tr>
                                        <td><?php echo SUBSTR($row['0'], 0, 15).' ...'; ?></td>
                                        <td><a href="./index.php?module=Askep&page=pengkajian_awal&action=view&no_rawat=<?php echo $row['6'];?>"><b><?php echo $row['1'];?></b></a></td>
                                        <td><?php echo $row['2']; ?></td>
                                        <td><?php echo $row['3']; ?></td>
                                        <td><?php echo $row['4']; ?></td>
                                        <td><?php echo $row['5']; ?></td>
                                        <td><button class="btn btn-primary">Simpan</button></td>
                                    </tr>
                                <?php
                                }
                                ?>
                                </tbody>
                            </table>
                        </div>
                        <?php } ?>
                        <?php if($action == "view"){ ?>
                        <!-- View Pasien -->
                        <div class="body">
                            <dl class="dl-horizontal">
                                <dt>Nama Lengkap</dt>
                                <dd><?php echo $nm_pasien; ?></dd>
                                <dt>No. RM</dt>
                                <dd><?php echo $no_rkm_medis; ?></dd>
                                <dt>No. Rawat</dt>
                                <dd><?php echo $no_rawat; ?></dd>
                                <dt>Umur</dt>
                                <dd><?php echo $umur; ?></dd>
                                <dt>Alamat</dt>
                                <dd><?php echo $alamat; ?></dd>
                            </dl>
                        </div>
                        <div class="body" style="border-top:1px solid #ddd;">
                            <!-- Nav tabs -->
                            <ul class="nav nav-tabs" role="tablist">
                                <li role="presentation" class="active">
                                    <a href="#home_with_icon_title" data-toggle="tab">
                                        <i class="material-icons">home</i> RIWAYAT PENYAKIT
                                    </a>
                                </li>
                                <li role="presentation">
                                    <a href="#profile_with_icon_title" data-toggle="tab">
                                        <i class="material-icons">face</i> PENGKAJIAN HEAD TO TOE
                                    </a>
                                </li>
                                <li role="presentation">
                                    <a href="#messages_with_icon_title" data-toggle="tab">
                                        <i class="material-icons">email</i> PENGKAJIAN KEPERAWATAN
                                    </a>
                                </li>
                            </ul>
                            <!-- Tab panes -->
                            <div class="tab-content">
                                <div role="tabpanel" class="tab-pane fade in active" id="home_with_icon_title">
                                    <div class="font-20 p-b-15">A. Riwayat Penyakit Sekarang</div>
                                    <form class="form-horizontal">
                                        <div class="row clearfix">
                                            <div class="col-lg-2 col-md-2 col-sm-4 col-xs-5 form-control-label">
                                                <label for="email_address_2">1. Keluhan Utama</label>
                                            </div>
                                            <div class="col-lg-10 col-md-10 col-sm-8 col-xs-7">
                                                <div class="form-group">
                                                    <div class="form-line">
                                                        <input type="text" class="form-control">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row clearfix">
                                            <div class="col-lg-2 col-md-2 col-sm-4 col-xs-5 form-control-label">
                                                <label for="password_2">2. Alasan Masuk RS</label>
                                            </div>
                                            <div class="col-lg-10 col-md-10 col-sm-8 col-xs-7">
                                                <div class="form-group">
                                                    <div class="form-line">
                                                        <input type="text" class="form-control">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row clearfix">
                                            <div class="col-lg-2 col-md-2 col-sm-4 col-xs-5 form-control-label">
                                                <label for="password_2">3. Faktor Pencetus</label>
                                            </div>
                                            <div class="col-lg-10 col-md-10 col-sm-8 col-xs-7">
                                                <div class="form-group">
                                                    <div class="form-line">
                                                        <input type="text" class="form-control">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row clearfix">
                                            <div class="col-lg-2 col-md-2 col-sm-4 col-xs-5 form-control-label">
                                                <label for="password_2">4. Lamanya Sakit</label>
                                            </div>
                                            <div class="col-lg-10 col-md-10 col-sm-8 col-xs-7">
                                                <div class="form-group">
                                                    <div class="form-line">
                                                        <input type="text" class="form-control">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                    <div class="font-20 p-b-15">B. Riwayat Penyakit Dahulu</div>
                                    <form class="form-horizontal">
                                        <div class="row clearfix">
                                            <div class="col-lg-2 col-md-2 col-sm-4 col-xs-5 form-control-label">
                                                <label for="password_2">1. Kondisi Kesehatan Sebelumnya</label>
                                            </div>
                                            <div class="col-lg-10 col-md-10 col-sm-8 col-xs-7">
                                                <div class="form-group">
                                                    <div class="form-line">
                                                        <input type="text" class="form-control">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row clearfix">
                                            <div class="col-lg-2 col-md-2 col-sm-4 col-xs-5 form-control-label">
                                                <label for="password_2">2. Pernah Dirawat Dengan Kasus Yang Sama</label>
                                            </div>
                                            <div class="col-lg-10 col-md-10 col-sm-8 col-xs-7">
                                                <div class="form-group">
                                                    <div class="form-line">
                                                        <input type="text" class="form-control">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row clearfix">
                                            <div class="col-lg-2 col-md-2 col-sm-4 col-xs-5 form-control-label">
                                                <label for="password_2">3. Riwayat Alergi</label>
                                            </div>
                                            <div class="col-lg-10 col-md-10 col-sm-8 col-xs-7">
                                                <div class="form-group">
                                                    <div class="form-line">
                                                        <input type="text" class="form-control">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row clearfix">
                                            <div class="col-lg-2 col-md-2 col-sm-4 col-xs-5 form-control-label">
                                                <label for="password_2">4. Riwayat Masalah Emosi/Psikiatri</label>
                                            </div>
                                            <div class="col-lg-10 col-md-10 col-sm-8 col-xs-7">
                                                <div class="form-group">
                                                    <div class="form-line">
                                                        <input type="text" class="form-control">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row clearfix">
                                            <div class="col-lg-offset-2 col-md-offset-2 col-sm-offset-4 col-xs-offset-5">
                                                <button type="button" class="btn btn-primary m-t-15 waves-effect">SIMPAN</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div role="tabpanel" class="tab-pane fade" id="profile_with_icon_title">
                                    <ul class="nav nav-tabs tab-nav-right" role="tablist">
                                      <li role="presentation" class="active"><a href="#riwayat" data-toggle="tab">Pernafasan</a></li>
                                      <li role="presentation"><a href="#pemeriksaan" data-toggle="tab">Kardiofaskuler</a></li>
                                      <li role="presentation"><a href="#odontogram" data-toggle="tab">Syaraf Pusat</a></li>
                                      <li role="presentation"><a href="#tindakan" data-toggle="tab">Gastrointestinal</a></li>
                                      <li role="presentation"><a href="#tindakan" data-toggle="tab">Perkemihan</a></li>
                                      <li role="presentation"><a href="#tindakan" data-toggle="tab">Obstetri</a></li>
                                      <li role="presentation"><a href="#tindakan" data-toggle="tab">Muskoskeletal</a></li>
                                      <li role="presentation"><a href="#tindakan" data-toggle="tab">Hematologi</a></li>
                                      <li role="presentation"><a href="#tindakan" data-toggle="tab">Psikososial</a></li>
                                      <li role="presentation"><a href="#tindakan" data-toggle="tab">Spiritual</a></li>
                                      <li role="presentation"><a href="#tindakan" data-toggle="tab">Alat Invasif</a></li>
                                      <li role="presentation"><a href="#tindakan" data-toggle="tab">Terapi</a></li>
                                      <li role="presentation"><a href="#tindakan" data-toggle="tab">Nutrisi</a></li>
                                      <li role="presentation"><a href="#tindakan" data-toggle="tab">Kognitif</a></li>
                                      <li role="presentation"><a href="#tindakan" data-toggle="tab">Pola Fungsional</a></li>
                                      <li role="presentation"><a href="#tindakan" data-toggle="tab">Sensori</a></li>
                                      <li role="presentation"><a href="#tindakan" data-toggle="tab">Aktifitas & Latihan</a></li>
                                    </ul>
                                    <div class="font-20 m-t-15 p-b-15">A. Sistem Pernafasan</div>
                                    <form class="form-horizontal">
                                        <div class="row clearfix">
                                            <div class="col-lg-2 col-md-2 col-sm-4 col-xs-5 form-control-label">
                                                <label for="email_address_2">- Jalan Nafas</label>
                                            </div>
                                            <div class="col-lg-10 col-md-10 col-sm-8 col-xs-7">
                                                <div class="input-group">
                                                    <div class="form-line">
                                                      <input type="checkbox" id="md_checkbox_21" class="filled-in chk-col-red" />
                                                      <label for="md_checkbox_21">Bersih</label><br>
                                                      <input type="checkbox" id="md_checkbox_22" class="filled-in chk-col-red" />
                                                      <label for="md_checkbox_22">Sumbatan, Berupa </label>
                                                      <input type="checkbox" id="md_checkbox_23" class="filled-in chk-col-red" />
                                                      <label for="md_checkbox_23">Sputum</label>
                                                      <input type="checkbox" id="md_checkbox_24" class="filled-in chk-col-red" />
                                                      <label for="md_checkbox_24">Darah</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row clearfix">
                                            <div class="col-lg-2 col-md-2 col-sm-4 col-xs-5 form-control-label">
                                                <label for="password_2">- Respiratory Rate</label>
                                            </div>
                                            <div class="col-lg-10 col-md-10 col-sm-8 col-xs-7">
                                                <div class="input-group">
                                                    <div class="form-line">
                                                        <input type="text" class="form-control">
                                                    </div>
                                                    <span class="input-group-addon">X/Menit</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row clearfix">
                                            <div class="col-lg-2 col-md-2 col-sm-4 col-xs-5 form-control-label">
                                                <label for="email_address_2">- Penggunaan Alat Bantu Nafas</label>
                                            </div>
                                            <div class="col-lg-10 col-md-10 col-sm-8 col-xs-7">
                                                <div class="input-group">
                                                    <div class="form-line">
                                                      <input name="group5" type="radio" id="radio_30" class="with-gap radio-col-red" />
                                                       <label for="radio_30">Ya</label>
                                                       <input name="group5" type="radio" id="radio_31" class="with-gap radio-col-pink" />
                                                       <label for="radio_31">Tidak</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row clearfix">
                                            <div class="col-lg-2 col-md-2 col-sm-4 col-xs-5 form-control-label">
                                                <label for="email_address_2">- Terpasang ETT</label>
                                            </div>
                                            <div class="col-lg-10 col-md-10 col-sm-8 col-xs-7">
                                                <div class="input-group">
                                                    <div class="form-line">
                                                      <input name="group6" type="radio" id="radio_32" class="with-gap radio-col-red" />
                                                       <label for="radio_32">Ya</label>
                                                       <input name="group6" type="radio" id="radio_33" class="with-gap radio-col-pink" />
                                                       <label for="radio_33">Tidak</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row clearfix">
                                            <div class="col-lg-2 col-md-2 col-sm-4 col-xs-5 form-control-label">
                                                <label for="email_address_2">- Terpasang Ventilator</label>
                                            </div>
                                            <div class="col-lg-10 col-md-10 col-sm-8 col-xs-7">
                                                <div class="input-group">
                                                    <div class="form-line">
                                                       <input name="group7" type="radio" id="radio_34" class="with-gap radio-col-red" />
                                                       <label for="radio_34">Ya</label>
                                                       <input name="group7" type="radio" id="radio_35" class="with-gap radio-col-pink" />
                                                       <label for="radio_35">Tidak</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row clearfix">
                                            <div class="col-lg-2 col-md-2 col-sm-4 col-xs-5 form-control-label">
                                            </div>
                                            <div class="col-lg-10 col-md-10 col-sm-8 col-xs-7">
                                                <div class="input-group">
                                                    <span class="input-group-addon">Mode</span>
                                                    <div class="form-line">
                                                       <input name="group5" type="text"/>
                                                    </div>
                                                    <span class="input-group-addon">RR</span>
                                                    <div class="form-line">
                                                       <input name="group5" type="text"/>
                                                    </div>
                                                    <span class="input-group-addon">IE</span>
                                                    <div class="form-line">
                                                       <input name="group5" type="text"/>
                                                    </div>
                                                    <span class="input-group-addon">TV</span>
                                                    <div class="form-line">
                                                       <input name="group5" type="text"/>
                                                    </div>
                                                    <span class="input-group-addon">Fi02</span>
                                                    <div class="form-line">
                                                       <input name="group5" type="text"/>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row clearfix">
                                            <div class="col-lg-offset-2 col-md-offset-2 col-sm-offset-4 col-xs-offset-5">
                                                <button type="button" class="btn btn-primary m-t-15 waves-effect">SIMPAN</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div role="tabpanel" class="tab-pane fade" id="messages_with_icon_title">
                                    <b>Message Content</b>
                                    <p>
                                        Lorem ipsum dolor sit amet, ut duo atqui exerci dicunt, ius impedit mediocritatem an. Pri ut tation electram moderatius.
                                        Per te suavitate democritum. Duis nemore probatus ne quo, ad liber essent aliquid
                                        pro. Et eos nusquam accumsan, vide mentitum fabellas ne est, eu munere gubergren
                                        sadipscing mel.
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                    </div>
