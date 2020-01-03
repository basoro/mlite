<?php

?>

        <div class="block-header">
            <h2>REKAM OBAT<h2>
        </div>


        <!-- Basic Examples -->
        <div class="row clearfix">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="card">

                <?php if (isset($_POST['proses'])) { ?>
                    <div class="header">
                        <h2>
                            Periode : <?php echo $_POST['tanggal_awal']; ?> s/d <?php echo $_POST['tanggal_akhir']; ?>
                        </h2>
                    </div>
                <?php } ?>
                    <div class="body">

                    <?php
                    if (isset($_POST['proses'])) {
                        if (($_POST['tanggal_awal'] == "")||($_POST['tanggal_akhir'] == "")) {
                          redirect ('rekam-obat.php');
                        } else {
                    ?>


                    <?php
                    $q_pasien = query ("select * from databarang where kode_brng = '$_POST[no_pasien]'");
                    $data_pasien = fetch_array($q_pasien);
                    ?>
                        <dl class="dl-horizontal">
                            <dt>Nama Barang</dt>
                            <dd><?php echo $data_pasien['nama_brng']; ?></dd>
                            <dt>Kode Barang</dt>
                            <dd><?php echo $data_pasien['kode_brng']; ?></dd>
                            <dt>Tanggal Expire</dt>
                            <dd><?php $tglasal = $data_pasien['expire'];$tglbaru = date("d-m-Y",strtotime($tglasal));echo $tglbaru; ?></dd>
                            <dt>Harga</dt>
                            <dd>Rp. <?php $angka = number_format($data_pasien['h_beli']); echo $angka; ?>,-</dd>
                        </dl>
                        <div id="buttons" class="align-center m-l-10 m-b-15 export-hidden"></div>
                        <table id="datatable" class="table table-bordered table-striped table-hover display nowrap js-exportable" width="100%">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>No Rekam Medis</th>
                                    <th>Nama</th>
                                    <th>Jumlah</th>
                                    <th>Tanggal Pemberian</th>
                                  	<th>Cara Bayar</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            $q_kunj = query ("SELECT databarang.nama_brng, databarang.kode_brng, detail_pemberian_obat.jml, pasien.no_rkm_medis, pasien.nm_pasien, reg_periksa.no_rawat , detail_pemberian_obat.tgl_perawatan , penjab.png_jawab
                            				FROM databarang, detail_pemberian_obat, pasien, reg_periksa , penjab
                                            WHERE databarang.kode_brng = '$_POST[no_pasien]' and detail_pemberian_obat.tgl_perawatan BETWEEN '$_POST[tanggal_awal]' AND '$_POST[tanggal_akhir]'
                                        	and detail_pemberian_obat.kode_brng = databarang.kode_brng AND reg_periksa.kd_pj = penjab.kd_pj
                                        	and detail_pemberian_obat.no_rawat = reg_periksa.no_rawat and reg_periksa.no_rkm_medis = pasien.no_rkm_medis and detail_pemberian_obat.jml != '0'");
                                $i = 0;
                          while ($data_kunj = fetch_array($q_kunj)) {
                                    $rm   = $data_kunj[3];
                                    $nama = $data_kunj[4];
                                    $jml = $data_kunj[2];
                                    $tgl = $data_kunj[6];
                                    $byr = $data_kunj[7];
                            		$i++;
                            ?>
                                <tr>
                                    <td><?php echo $i; ?></td>
                                    <td><?php echo $rm; ?></td>
                                    <td><?php echo $nama; ?></td>
                                    <td><?php echo $jml; ?></td>
                                    <td><?php echo $tgl; ?></td>
                                  	<td><?php echo $byr; ?></td>
                                </tr>
                            <?php
                            }
                            ?>
                            </tbody>
                        </table>
                      </div>
                    <?php
                        }
                    }
                    ?>

                    <div class="body">
                    <form method="post" action="">
                        <dl class="dl-horizontal">
                            <dt>Pasien</dt>
                            <dd><select name="no_pasien" class="pasien" style="width:100%"></select></dd><br/>
                            <dt>Periode</dt>
                            <dd><input type="text" class="datepicker form-control" name="tanggal_awal" placeholder="Pilih tanggal awal...">
                            <dt></dt><dd>s/d</dd>
                            <dt></dt><dd><input type="text" class="datepicker form-control" name="tanggal_akhir" placeholder="Pilih tanggal akhir..."></dd><br/>
                            <dt></dt><dd><input type="submit" class="btn btn-primary waves-effect" name="proses" value="Proses"> <button type="reset" class="btn btn-red waves-effect" name="batal" style="background-color: #f7f7f7 !important; color: #555; border-color: #ccc; text-shadow: none; -webkit-appearance: none;">Batal</button></dd>
                        </dl>
                    </form>
                    </div>
                </div>
            </div>
        </div>
