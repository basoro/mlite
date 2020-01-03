<?php
$host   = "localhost";
$dbname = "sik";
$dbuser = "root";
$dbpass = "";
global $conn;

$conn = new PDO("mysql:host=$host;dbname=$dbname","$dbuser","$dbpass");
?>
