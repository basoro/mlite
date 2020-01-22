<?php
include('../../../config.php');

if(!empty($_POST['kd_poli'])){
    $data = array();

      $tanggal = $_POST['tgl_registrasi'];
      $tentukan_hari = date('D',strtotime($tanggal));
      $day = array(
        'Sun' => 'AKHAD',
        'Mon' => 'SENIN',
        'Tue' => 'SELASA',
        'Wed' => 'RABU',
        'Thu' => 'KAMIS',
        'Fri' => 'JUMAT',
        'Sat' => 'SABTU'
      );
      $hari=$day[$tentukan_hari];

      //get data from the database
      $query = $db->query("SELECT a.kd_dokter, c.nm_dokter FROM jadwal a, poliklinik b, dokter c WHERE a.kd_poli = b.kd_poli AND a.kd_dokter = c.kd_dokter AND a.kd_poli = '{$_POST['kd_poli']}' AND a.hari_kerja LIKE '%$hari%'");

      $sql = "SELECT a.kd_dokter, c.nm_dokter, a.kuota FROM jadwal a, poliklinik b, dokter c WHERE a.kd_poli = b.kd_poli AND a.kd_dokter = c.kd_dokter AND a.kd_poli = '{$_POST['kd_poli']}' AND a.hari_kerja LIKE '%$hari%'";

      $result = fetch_assoc(query($sql));

      $check_kuota = fetch_assoc(query("SELECT COUNT(*) AS count FROM reg_periksa WHERE kd_poli = '{$_POST['kd_poli']}' AND tgl_registrasi = '{$_POST['tgl_registrasi']}'"));
      $curr_count = $check_kuota['count'];
      $curr_kuota = $result['kuota'];
      $online = $curr_kuota / LIMIT;

      if($curr_count > $online) {
        $data['status'] = 'limit';
      } else if($query->num_rows > 0){
          while ($userData = $query->fetch_assoc()) {
            $data['status'] = 'ok';
            $data['result'][] = $userData;
          }
      } else {
          $data['status'] = 'err';
          $data['result'] = '';
      }

    //returns data as JSON format
    echo json_encode($data);

}
?>
