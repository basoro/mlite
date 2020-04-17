<?php
function buka_form($link, $id, $aksi){
	echo'<div class="body"><form method="post" action="'.$link.'&show=action" class="form-horizontal" enctype="multipart/form-data">
			<input type="hidden" name="id" value="'.$id.'">
			<input type="hidden" name="aksi" value="'.$aksi.'">';
}

function buka_form_alt($link, $id, $aksi){
	echo'<div class="body"><form method="post" action="'.$link.'&show=action" enctype="multipart/form-data">
			<input type="hidden" name="id" value="'.$id.'">
			<input type="hidden" name="aksi" value="'.$aksi.'">';
}

function buat_textbox($label, $nama, $nilai, $lebar='4', $tipe="text"){
	echo '<div class="row clearfix">
			<div class="col-lg-2 col-md-2 col-sm-4 col-xs-5 form-control-label">
					<label for="'.$nama.'">'.$label.'</label>
			</div>
			<div class="col-lg-10 col-md-10 col-sm-8 col-xs-7">
					<div class="form-group">
							<div class="form-line">
									<input type="'.$tipe.'" name="'.$nama.'" value="'.$nilai.'" id="'.$nama.'" class="form-control" placeholder="'.$label.'">
							</div>
					</div>
			</div>
	</div>';
}

function buat_textbox_alt($label, $nama, $nilai, $lebar='4', $tipe="text"){
echo '<div class="form-group">
		<label for="'.$nama.'">'.$label.'</label>
		<div class="form-line">
				<input type="'.$tipe.'" name="'.$nama.'" value="'.$nilai.'" id="'.$nama.'" class="form-control" placeholder="'.$label.'">
		</div>
</div>';
}

function buat_textbox_alt2($label, $nama, $nilai, $lebar='4', $tipe="text"){
	echo '<div class="form-group">
			<label for="'.$nama.'" class="col-sm-4 control-label">'.$label.'</label>
			<div class="col-sm-8">
					<div class="form-line">
							<input type="'.$tipe.'" name="'.$nama.'" value="'.$nilai.'" id="'.$nama.'" class="form-control" placeholder="'.$label.'">
					</div>
			</div>
	</div>';
}

function buat_datepicker($label, $nama, $nilai, $lebar='4', $tipe="text"){
	echo '<div class="row clearfix">
			<div class="col-lg-2 col-md-2 col-sm-4 col-xs-5 form-control-label">
					<label for="'.$nama.'">'.$label.'</label>
			</div>
			<div class="col-lg-10 col-md-10 col-sm-8 col-xs-7">
					<div class="form-group">
							<div class="form-line">
									<input type="'.$tipe.'" name="'.$nama.'" value="'.$nilai.'" id="'.$nama.'" class="form-control datepicker" placeholder="'.$label.'">
							</div>
					</div>
			</div>
	</div>';
}

function buat_datepicker_alt($label, $nama, $nilai, $lebar='4', $tipe="text"){
echo '<div class="form-group">
		<label for="'.$nama.'">'.$label.'</label>
		<div class="form-line">
				<input type="'.$tipe.'" name="'.$nama.'" value="'.$nilai.'" id="'.$nama.'" class="form-control datepicker" placeholder="'.$label.'">
		</div>
</div>';
}

function buat_textarea($label, $nama, $nilai, $class=''){
	echo '<div class="row clearfix">
			<div class="col-lg-2 col-md-2 col-sm-4 col-xs-5 form-control-label">
					<label for="'.$nama.'">'.$label.'</label>
			</div>
			<div class="col-lg-10 col-md-10 col-sm-8 col-xs-7">
					<div class="form-group">
							<div class="form-line">
									<textarea class="form-control '.$class.'" rows="4" name="'.$nama.'">'.$nilai.'</textarea>
							</div>
					</div>
			</div>
	</div>';
}

function buat_textarea_alt($label, $nama, $nilai, $class=''){
	echo '<div class="form-group">
			<label for="'.$nama.'">'.$label.'</label>
			<div class="form-line">
					<textarea class="form-control '.$class.'" id="'.$nama.'" rows="4" name="'.$nama.'">'.$nilai.'</textarea>
			</div>
	</div>';
}

function buat_textarea_alt2($label, $nama, $nilai, $class=''){
	echo '<div class="form-group">
			<label for="'.$nama.'" class="col-sm-4 control-label">'.$label.'</label>
			<div class="col-sm-8">
					<div class="form-line">
							<textarea class="form-control '.$class.'" rows="4" name="'.$nama.'">'.$nilai.'</textarea>
					</div>
			</div>
	</div>';
}

function buat_combobox($label, $nama, $list, $nilai, $lebar='4'){

	echo '<div class="row clearfix">
			<div class="col-lg-2 col-md-2 col-sm-4 col-xs-5 form-control-label">
					<label for="'.$nama.'">'.$label.'</label>
			</div>
			<div class="col-lg-10 col-md-10 col-sm-8 col-xs-7">
					<div class="form-group">
							<div class="form-line">
									<select class="form-control show-tick" name="'.$nama.'">';
									foreach($list as $ls){
									$select = $ls['val']==$nilai ? 'selected' : '';
									echo'<option value='.$ls['val'].' '.$select.'>'.$ls['cap'].'</option>';
									}
									echo'	  </select>
							</div>
					</div>
			</div>
	</div>';
}

function buat_combobox_alt($label, $nama, $list, $nilai, $lebar='4'){

	echo '<div class="row clearfix">
			<div class="col-lg-2 col-md-2 col-sm-4 col-xs-5 form-control-label">
					<label for="'.$nama.'">'.$label.'</label>
			</div>
			<div class="col-lg-10 col-md-10 col-sm-8 col-xs-7">
					<div class="form-group">
							<div class="form-line">
									<select class="form-control show-tick" name="'.$nama.'">';
									foreach($list as $ls){
									$select = $ls['val']==$nilai ? 'selected' : '';
									echo'<option value='.$ls['val'].' '.$select.'>'.$ls['cap'].'</option>';
									}
									echo'	  </select>
							</div>
					</div>
			</div>
	</div>';
}
function buat_checkbox($label, $nama, $list){
	echo'<div class="form-group" id="'.$nama.'">
			<label class="col-sm-2 control-label">'.$label.'</label>
			<div class="col-sm-10">';
		foreach($list as $ls){
			echo' <input type="checkbox" name="'.$nama.'[]" value="'.$ls['val'].'" '.$ls['check'].'> '.$ls['cap'];
		}
	echo'	</div>
		</div>';
}

function buat_radio($label, $nama, $list){
	echo'<div class="form-group" id="'.$nama.'">
			<label class="col-sm-2 control-label">'.$label.'</label>
			<div class="col-sm-10">';
		foreach($list as $ls){
			echo'<label  for="'.$nama.$ls['val'].'" id="label_'.$nama.$ls['val'].'">
					<input type="radio" name="'.$nama.'" id="'.$nama.$ls['val'].'" value="'.$ls['val'].'" '.$ls['check'].'> '.$ls['cap'].'
				</label>';
		}
	echo'	</div>
		</div>';
}

function tutup_form($link){
	echo'<div class="form-group">
			<div class="col-sm-offset-2 col-sm-8">
				<button type="submit" class="btn btn-lg btn-primary">
					Simpan
				</button>
				<a class="btn btn-lg btn-warning" href="'.$link.'">
					Batal
				</a>
			</div>
		</div>
	</form></div>';
}

function tutup_form_alt($link){
	echo'</div></form></div>';
}
// Enum dropdown value
function getEnum($table_name, $column_name) {
		global $mysqli;
    $result = $mysqli->query("SHOW COLUMNS FROM $table_name LIKE '$column_name'");
    $row = $result->fetch_array();
		$regex = "/'(.*?)'/";
		preg_match_all( $regex , $row[1], $enum_array );
		$enumList = $enum_array[1];
		return $enumList;
}
function setUmur($tgl_lahir){
		list($cY, $cm, $cd) = explode('-', date('Y-m-d'));
		list($Y, $m, $d) = explode('-', date('Y-m-d', strtotime($tgl_lahir)));
		$umur = $cY - $Y;
		return $umur;
}

function setNoRM() {
	global $mysqli;
	// Get last no_rm
	$query = $mysqli->query("SELECT MAX(no_rkm_medis) FROM pasien");
	$last_no_rm = $query->fetch_array();
	// Next no_rm
	if(empty($last_no_rm[0])) {
		$last_no_rm[0] = '000000';
	}
	$last_no_rm = substr($last_no_rm[0], 0, 6);
	$next_no_rm = sprintf('%06s', ($last_no_rm + 1));
	return $next_no_rm;
}

function setNoRawat() {
	global $mysqli, $date;
	$tgl_reg = date('Y/m/d', strtotime($date));
	$query = $mysqli->query("SELECT max(no_rawat) FROM reg_periksa WHERE tgl_registrasi='$date'");
	$no_rawat_akhir = $query->fetch_array();
	if(empty($no_rawat_akhir[0])) {
		$no_rawat_akhir[0] = '000000';
	}
	$no_urut_rawat = substr($no_rawat_akhir[0], 11, 6);
	$next_no_rawat = $tgl_reg.'/'.sprintf('%06s', ($no_urut_rawat + 1));

	return $next_no_rawat;
}
?>
