<?php
include('../../../config.php');

if(!empty($_POST['no_rkm_medis'])){
    $data = array();

    //get data from the database
    $query = $db->query("SELECT pasien.nm_pasien , reg_periksa.no_rawat , reg_periksa.no_rkm_medis , pasien.tgl_lahir , pasien.no_tlp , pasien.no_peserta , pasien.jk , poliklinik.nm_poli , poliklinik.kd_poli
   	FROM reg_periksa , pasien , poliklinik WHERE reg_periksa.no_rkm_medis = pasien.no_rkm_medis AND reg_periksa.kd_poli = poliklinik.kd_poli AND reg_periksa.no_rkm_medis = '{$_POST['no_rkm_medis']}' AND reg_periksa.tgl_registrasi='{$date}'");

    if($query->num_rows > 0){
      $userData = $query->fetch_assoc();
        $data['status'] = 'ok';
        $data['result'] = $userData;
    }else{
        $data['status'] = 'err';
        $data['result'] = '';
    }

    //returns data as JSON format
    echo json_encode($data);
}
?>
