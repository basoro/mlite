<?php
// Skrip ini jalan di Server Partner/S2S

$url = "http://localhost:8000/admin/api/pasien/list";

// Kredensial "System" (Aplikasi) miliknya
$api_key = "YOUR_API_KEY_HERE";

$curl = curl_init($url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_HTTPHEADER, [
    "X-API-KEY: " . $api_key,
    "Content-Type: application/json"
]);

// Hak Akses (CRUD & Module) yang membaca data ini akan dibatasi 
// berdasarkan 'User' yang dikaitkan dengan $api_key tersebut di tabel mlite_api_key.
$response = curl_exec($curl);
$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

echo "HTTP Code: $http_code\n";
echo "Data: $response";
?>