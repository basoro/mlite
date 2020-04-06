<?php
if (!defined('IS_IN_MODULE')) {
    die("NO DIRECT FILE ACCESS!");
}
class Lab
{
    public function index()
    {
        global $dataSettings, $date;
        include 'modules/Lab/pasien_lab.php';
    }
}
