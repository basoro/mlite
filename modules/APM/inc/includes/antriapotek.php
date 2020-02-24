<?php
include('../../../../config.php');

$antriapotek = query("SELECT pasien.nm_pasien, pasien.no_rkm_medis FROM pasien, resep_obat, reg_periksa WHERE reg_periksa.no_rawat = resep_obat.no_rawat AND reg_periksa.no_rkm_medis = pasien.no_rkm_medis AND resep_obat.tgl_peresepan = '$date' AND resep_obat.jam = resep_obat.jam_peresepan AND resep_obat.status = 'ralan' ORDER BY resep_obat.jam ASC");
if(num_rows($antriapotek) > 0) {
  while ($row = fetch_array($antriapotek)) {
    echo '<li class="pt-3 pr-3 pb-2 pl-3"><h4>'.ucwords(strtolower(SUBSTR($row['nm_pasien'], 0, 15))).' ['.$row['no_rkm_medis'].']</h4></li>';
  }
} else {
  echo 'Tidak ada antrian';
}
?>
