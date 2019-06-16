<?php

/***
* SIMRS Khanza Lite from version 0.1 Beta
* About : Porting of SIMRS Khanza by Windiarto a.k.a Mas Elkhanza as web and mobile app.
* Last modified: 02 Pebruari 2018
* Author : drg. Faisol Basoro
* Email : drg.faisol@basoro.org
* Licence under GPL
***/

$title = 'Rekam Medik Pasien';
include_once('config.php');
include_once('layout/header.php');
include_once('layout/sidebar.php');

?>

<section class="content">
    <div class="container-fluid">
        <div class="block-header">
            <h2>REKAM MEDIK PASIEN<h2>
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
                          redirect ('rekam-medik.php');
                        } else {
                    ?>


                    <?php
                    $q_pasien = query ("select * from pasien where no_rkm_medis = '$_POST[no_pasien]'");
                    $data_pasien = fetch_array($q_pasien);
                    ?>
                        <dl class="dl-horizontal">
                            <dt>Nama Lengkap</dt>
                            <dd><?php echo $data_pasien['nm_pasien']; ?></dd>
                            <dt>No. RM</dt>
                            <dd><?php echo $data_pasien['no_rkm_medis']; ?></dd>
                            <dt>Jenis Kelamin</dt>
                            <dd><?php echo $data_pasien['jk']; ?></dd>
                            <dt>Umur</dt>
                            <dd><?php echo $data_pasien['umur']; ?></dd>
                        </dl>
                        <div id="buttons" class="align-center m-l-10 m-b-15 export-hidden"></div>
                        <table id="datatable" class="table table-bordered table-striped table-hover display nowrap js-exportable" width="100%">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Poliklinik</th>
                                    <th>Keluhan</th>
                                    <th>Pemeriksaan</th>
                                    <th>Diagnosa</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            $q_kunj = query ("SELECT a.tgl_registrasi, b.nm_poli, c.keluhan, c.pemeriksaan, a.no_rawat FROM reg_periksa a, poliklinik b, pemeriksaan_ralan c WHERE a.no_rkm_medis = '$_POST[no_pasien]' AND a.tgl_registrasi BETWEEN '$_POST[tanggal_awal]' AND '$_POST[tanggal_akhir]' AND a.kd_poli = b.kd_poli AND a.no_rawat = c.no_rawat");
                                while ($data_kunj = fetch_array($q_kunj)) {
                                    $tanggal   = $data_kunj[0];
                                    $nama_poli = $data_kunj[1];
                                    $keluhan = $data_kunj[2];
                                    $pemeriksaan = $data_kunj[3];
                                    $no_rawat = $data_kunj[4];
                            ?>
                                <tr>
                                    <td><?php echo $tanggal; ?></td>
                                    <td><?php echo $nama_poli; ?></td>
                                    <td><?php echo $keluhan; ?></td>
                                    <td><?php echo $pemeriksaan; ?></td>
                                    <td>
                                        <ul style="list-style:none;">
                                        <?php
                                        $sql_dx = query("SELECT a.kd_penyakit, a.nm_penyakit FROM penyakit a, diagnosa_pasien b WHERE a.kd_penyakit = b.kd_penyakit AND b.no_rawat = '$no_rawat'");
                                      $no=1;
                                        while ($row_dx = fetch_array($sql_dx)) {
                                            echo '<li>'.$no.'. '.$row_dx[1].' ('.$row_dx[0].')</li>';
                                        $no++;
                                        }
                                        ?>
                                        </ul>
                                    </td>
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
    </div>
</section>

<?php
include_once('layout/footer.php');
?>

<script type="text/javascript">

    function formatData (data) {
        var $data = $(
            '<b>'+ data.id +'</b> - <i>'+ data.text +'</i>'
        );
        return $data;
    };

    function formatDataTEXT (data) {
        var $data = $(
            '<b>'+ data.text +'</b>'
        );
        return $data;
    };

      $('.pasien').select2({
        placeholder: 'Pilih nama/no.RM pasien',
        ajax: {
          url: 'includes/select-pasien.php',
          dataType: 'json',
          delay: 250,
          processResults: function (data) {
            return {
              results: data
            };
          },
          cache: true
        },
        templateResult: formatData,
        minimumInputLength: 3
      });

</script>
