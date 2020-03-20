<?php
/***
* SIMRS Khanza Lite from version 0.1 Beta
* About : Porting of SIMRS Khanza by Windiarto a.k.a Mas Elkhanza as web and mobile app.
* Last modified: 02 Pebruari 2018
* Author : drg. Faisol Basoro
* Email : drg.faisol@basoro.org
* Licence under GPL
***/
?>
<div class="card">
    <div class="header">
        <h2>
            ASUHAN KEPERAWATAN <?php if($role == 'Paramedis_Ranap') { echo $dataGetBangsal['nm_bangsal']; } ?>
        </h2>
    </div>
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
                    <th>P. Awal</th>
                    <th>R. Asuhan</th>
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
                    <td><?php echo $row['1'];?></td>
                    <td><?php echo $row['2']; ?></td>
                    <td><?php echo $row['3']; ?></td>
                    <td><?php echo $row['4']; ?></td>
                    <td><?php echo $row['5']; ?></td>
                    <td><i class="material-icons font-bold bg-green">check</i> <a href="./index.php?module=Askep&page=pengkajian_awal&action=view&no_rawat=<?php echo $row['6']; ?>"><i class="material-icons bg-primary">fullscreen</i></a></td>
                    <td><i class="material-icons font-bold bg-red">close</i> <a href="./index.php?module=Askep&page=rencana_asuhan&action=view&no_rawat=<?php echo $row['6']; ?>"><i class="material-icons bg-primary">fullscreen</i></a></td>
                </tr>
            <?php
            }
            ?>
            </tbody>
        </table>
    </div>
</div>
