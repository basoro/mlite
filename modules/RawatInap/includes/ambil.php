<?php
include_once('../../../config.php');
$hasil1 = query("SELECT ifnull(MAX(CONVERT(no_antrian,signed)),0) from skdp_bpjs");
while ($sql = fetch_array($hasil1)) {
    $antri = $sql['0']+1; ?>
    <dt>No SKDP</dt>
    <dd><input type='text' id="antri" class='form-control antri' name='noan' value="<?php echo $antri; ?>" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" maxlength="6" required>
    </dd>
<?php
} ?>
