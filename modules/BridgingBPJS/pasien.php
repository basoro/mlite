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
    $_sql = "SELECT a.no_rkm_medis, a.no_rawat, b.nm_pasien, b.umur FROM reg_periksa a, pasien b WHERE a.no_rkm_medis = b.no_rkm_medis AND a.no_rawat = '$_GET[no_rawat]'";
    $found_pasien = query($_sql);
    if(num_rows($found_pasien) == 1) {
	     while($row = fetch_array($found_pasien)) {
	        $no_rkm_medis  = $row['0'];
	        $get_no_rawat	     = $row['1'];
          $no_rawat	     = $row['1'];
	        $nm_pasien     = $row['2'];
	        $umur          = $row['3'];
	     }
    } else {
	     redirect ('./?module=BridgingBPJS');
    }
}

?>

                        <?php display_message(); ?>
                        <?php
                        $action = isset($_GET['action'])?$_GET['action']:null;
                        $jenis_poli = isset($_SESSION['jenis_poli'])?$_SESSION['jenis_poli']:null;
                        $role = isset($_SESSION['role'])?$_SESSION['role']:null;
                        if(!$action){
                        ?>
                            <div class="body table-responsive">
                                <table id="datatable" class="table responsive table-bordered table-striped table-hover display nowrap js-exportable" width="100%">
                                    <thead>
                                        <tr>
                                            <th>Nama Pasien</th>
                                            <th>No. RM</th>
                                            <th width="10%">No.<br>Reg</th>
                                            <th>Tgl. Reg</th>
                                            <th>Jam Reg</th>
                                            <th>Alamat</th>
                                            <th>Jenis<br>Bayar</th>
                                            <th>Poliklinik</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    $sql = "SELECT a.nm_pasien, b.no_rkm_medis, a.alamat, c.png_jawab, d.nm_poli, b.no_rawat, b.no_reg, b.tgl_registrasi, b.jam_reg FROM pasien a, reg_periksa b, penjab c, poliklinik d WHERE a.no_rkm_medis = b.no_rkm_medis AND b.kd_pj = c.kd_pj AND b.kd_poli = d.kd_poli AND d.kd_poli NOT IN('IGDK')";
                                    if(isset($_POST['tgl_awal']) && isset($_POST['tgl_akhir'])) {
                                        $sql .= " AND b.tgl_registrasi BETWEEN '$_POST[tgl_awal]' AND '$_POST[tgl_akhir]'";
                                    } else {
                                        $sql .= " AND b.tgl_registrasi = CURDATE()";
                                    }
                                    $query = query($sql);
                                    while($row = fetch_array($query)) {
                                    ?>
                                        <tr>
                                            <td><?php echo SUBSTR($row['0'], 0, 15).' ...'; ?></td>
                                            <td>
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-secondary waves-effect dropdown-toggle" data-toggle="dropdown" data-disabled="true" aria-expanded="true"><?php echo $row['1']; ?> <span class="caret"></span></button>
                                                    <ul class="dropdown-menu dropdown-menu-right" role="menu">
                                                        <li><a href="./?module=BridgingBPJS&action=tindakan&no_rawat=<?php echo $row['5']; ?>">Bridging</a></li>
                                                        <li><a href="./?module=BridgingBPJS&page=data_sep&no_rm=<?php echo $row['1']; ?>">Data SEP</a></li>
                                                    </ul>
                                                </div>
                                            </td>
                                            <td><?php echo $row['6']; ?></td>
                                            <td><?php echo $row['7']; ?></td>
                                            <td><?php echo $row['8']; ?></td>
                                            <td><?php echo SUBSTR($row['2'],0,20); ?></td>
                                            <td><?php echo $row['3']; ?></td>
                                            <td><?php echo SUBSTR($row['4'],5,15); ?></td>
                                        </tr>
                                    <?php
                                    }
                                    ?>
                                    </tbody>
                                </table>
                                <div class="row clearfix">
                                    <form method="post" action="">
                                    <div class="col-sm-5">
                                        <div class="form-group">
                                            <div class="form-line">
                                                <input type="text" name="tgl_awal" class="datepicker form-control" placeholder="Pilih tanggal awal...">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-5">
                                        <div class="form-group">
                                            <div class="form-line">
                                                <input type="text" name="tgl_akhir" class="datepicker form-control" placeholder="Pilih tanggal akhir...">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-2">
                                        <div class="form-group">
                                            <div class="form-line">
                                                <input type="submit" class="btn bg-blue btn-block btn-lg waves-effect">
                                            </div>
                                        </div>
                                    </div>
                                    </form>
                                </div>
                      </div>

                        <?php } ?>
