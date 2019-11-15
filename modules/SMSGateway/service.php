<?php
//passthru("net start > service.log");

$handle = @fopen("service.log", "r");
$baris = array();
$getstatus = 0;
if ($handle) {
    while (!feof($handle)) {
        $buffer = fgets($handle);
        if (substr_count($buffer, 'Gammu SMSD Service (GammuSMSD)') > 0)
		{
		   $status = 1;
		}
		else $status = 0;

		$baris[] = $buffer;
        $getstatus = $getstatus || $status;
    }
    fclose($handle);
}

if ($getstatus != 0) echo "Gammu service running..";
else echo "Gammu service stopped";

?>
