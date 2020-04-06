<?php
if (!defined('IS_IN_MODULE')) {
    die("NO DIRECT FILE ACCESS!");
}
?>

<?php
class Umpeg
{
    public function index()
    {
        global $dataSettings , $date;
        include 'modules/Umpeg/umpeg.php';
    }
}
?>
