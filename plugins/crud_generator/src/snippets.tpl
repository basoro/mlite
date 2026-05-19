============== VALIDASI ==============
Untuk di validasi (di js File) :
- rules
  nomor_telp:  { required: true, number: true },
  email: { required: true, email: true },
- messages
  nomor_telp: "Nomor Telp Rumah tidak boleh kosong dan harus angka!",
  email: "Email tidak boleh kosong atau format salah!",

============== UPLOAD IMAGE ==============

<div class="text-center" style="padding: 10px;">
	<img id="photo_cs" name="photo_cs" style=" border: 1px solid #555;" src="../adminweb/assets/img/female.jpg" width="200" height="250"> <br>
	<label>File Photo<br /></label>
	<input type="file" name="fileToUpload" id="fileToUpload"/>
</div>

<button type="submit" id="simpan_data_cs" class="btn btn-primary">Simpan Data</button>

DI AJAX
=======

contentType: false, // tambahan
processData: false, // tambahan

DI SUBMITHANDELER VALIDATION (JS)
=================================

const fileupload = $('#fileToUpload').prop('files')[0];
var formData = new FormData(form);
formData.append('fileToUpload', fileToUpload);

DI ADD AKSI
===========

$temp = "img/";
$fileupload      = $_FILES['fileToUpload']['tmp_name'];
$ImageName       = $_FILES['fileToUpload']['name'];
$ImageType       = $_FILES['fileToUpload']['type'];
move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $temp.$ImageName); // Menyimpan file

DI EDIT AKSI
============

$nama_file          = basename($_FILES["fileToUpload"]["name"]);
$target_file = $target_dir . $nama_file;
move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file);

DI EDIT JS
==========

var foldera = "modul/barang/img/";
$('#photobarang').attr('src', foldera + filename);

