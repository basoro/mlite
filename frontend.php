<?php
// Skrip ini jalan di belakang aplikasi Mobile

$base_url = "http://localhost:8000/admin";
$api_key = "YOUR_API_KEY_HERE"; // Identitas bahwa App ini berizin hit server kita

// ---- TAHAP AWAL: Meloginkan Pengguna Akhir (Misal: Dokter Budi) ----
$login_url = $base_url . "/api/login";
$login_data = json_encode([
    "username" => "DR001", // Datanya ada di mlite_users
    "password" => "12345678"
]);

$ch1 = curl_init($login_url);
curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch1, CURLOPT_POST, true);
curl_setopt($ch1, CURLOPT_POSTFIELDS, $login_data);
curl_setopt($ch1, CURLOPT_HTTPHEADER, [
    "X-API-KEY: " . $api_key,  // Aplikasi menunjukan tiketnya terlebih dulu
    "Content-Type: application/json"
]);

$login_response = json_decode(curl_exec($ch1), true);
curl_close($ch1);

if (!isset($login_response['token'])) {
    die("Login Dokter Gagal!");
}

$jwt_token = $login_response['token'];


// ---- KEDUA: Mengambil Data Klinis Mengatasnamakan Dokter Tsb ----
$rawat_jalan_url = $base_url . "/api/rawat_jalan/list";

$ch2 = curl_init($rawat_jalan_url);
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
// Sekarang request mengirim BAE: Header App & Header Pengguna Akhir
curl_setopt($ch2, CURLOPT_HTTPHEADER, [
    "X-API-KEY: " . $api_key,           // Pintu lapis 1 (Apakah App Diblokir Cek IP?)
    "Authorization: Bearer " . $jwt_token, // Pintu Lapis 2 & 3 (Siapa End-Usernya & Hak Aksesnya)
    "Content-Type: application/json"
]);

$data_response = curl_exec($ch2);
curl_close($ch2);

echo "Data Rawat Jalan milik Dokter Budi:\n";
echo $data_response;
?>