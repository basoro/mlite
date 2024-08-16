jQuery().ready(function () {
    var var_tbl_resume_pasien_ranap = $('#tbl_resume_pasien_ranap').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'dom': 'Bfrtip',
        'searching': false,
        'select': true,
        'colReorder': true,
        "bInfo" : false,
        "ajax": {
            "url": "{?=url(['resume_pasien_ranap','data'])?}",
            "dataType": "json",
            "type": "POST",
            "data": function (data) {

                // Read values
                var search_field_resume_pasien_ranap = $('#search_field_resume_pasien_ranap').val();
                var search_text_resume_pasien_ranap = $('#search_text_resume_pasien_ranap').val();
                
                data.search_field_resume_pasien_ranap = search_field_resume_pasien_ranap;
                data.search_text_resume_pasien_ranap = search_text_resume_pasien_ranap;
                
            }
        },
        "fnDrawCallback": function () {
            $('#more_data_resume_pasien_ranap').on('click', function(e) {
                e.preventDefault();
                var clientX = e.originalEvent.clientX;
                var clientY = e.originalEvent.clientY;
                $('#tbl_resume_pasien_ranap tr').contextMenu({x: clientX, y: clientY});
            });          
        }, 
        "columns": [
{ 'data': 'no_rawat' },
{ 'data': 'kd_dokter' },
{ 'data': 'diagnosa_awal' },
{ 'data': 'alasan' },
{ 'data': 'keluhan_utama' },
{ 'data': 'pemeriksaan_fisik' },
{ 'data': 'jalannya_penyakit' },
{ 'data': 'pemeriksaan_penunjang' },
{ 'data': 'hasil_laborat' },
{ 'data': 'tindakan_dan_operasi' },
{ 'data': 'obat_di_rs' },
{ 'data': 'diagnosa_utama' },
{ 'data': 'kd_diagnosa_utama' },
{ 'data': 'diagnosa_sekunder' },
{ 'data': 'kd_diagnosa_sekunder' },
{ 'data': 'diagnosa_sekunder2' },
{ 'data': 'kd_diagnosa_sekunder2' },
{ 'data': 'diagnosa_sekunder3' },
{ 'data': 'kd_diagnosa_sekunder3' },
{ 'data': 'diagnosa_sekunder4' },
{ 'data': 'kd_diagnosa_sekunder4' },
{ 'data': 'prosedur_utama' },
{ 'data': 'kd_prosedur_utama' },
{ 'data': 'prosedur_sekunder' },
{ 'data': 'kd_prosedur_sekunder' },
{ 'data': 'prosedur_sekunder2' },
{ 'data': 'kd_prosedur_sekunder2' },
{ 'data': 'prosedur_sekunder3' },
{ 'data': 'kd_prosedur_sekunder3' },
{ 'data': 'alergi' },
{ 'data': 'diet' },
{ 'data': 'lab_belum' },
{ 'data': 'edukasi' },
{ 'data': 'cara_keluar' },
{ 'data': 'ket_keluar' },
{ 'data': 'keadaan' },
{ 'data': 'ket_keadaan' },
{ 'data': 'dilanjutkan' },
{ 'data': 'ket_dilanjutkan' },
{ 'data': 'kontrol' },
{ 'data': 'obat_pulang' }

        ],
        "columnDefs": [
{ 'targets': 0},
{ 'targets': 1},
{ 'targets': 2},
{ 'targets': 3},
{ 'targets': 4},
{ 'targets': 5},
{ 'targets': 6},
{ 'targets': 7},
{ 'targets': 8},
{ 'targets': 9},
{ 'targets': 10},
{ 'targets': 11},
{ 'targets': 12},
{ 'targets': 13},
{ 'targets': 14},
{ 'targets': 15},
{ 'targets': 16},
{ 'targets': 17},
{ 'targets': 18},
{ 'targets': 19},
{ 'targets': 20},
{ 'targets': 21},
{ 'targets': 22},
{ 'targets': 23},
{ 'targets': 24},
{ 'targets': 25},
{ 'targets': 26},
{ 'targets': 27},
{ 'targets': 28},
{ 'targets': 29},
{ 'targets': 30},
{ 'targets': 31},
{ 'targets': 32},
{ 'targets': 33},
{ 'targets': 34},
{ 'targets': 35},
{ 'targets': 36},
{ 'targets': 37},
{ 'targets': 38},
{ 'targets': 39},
{ 'targets': 40}

        ],
        order: [[1, 'DESC']], 
        buttons: [],
        "scrollCollapse": true,
        // "scrollY": '48vh', 
        // "pageLength":'25', 
        "lengthChange": true,
        "scrollX": true,
        dom: "<'row'<'col-sm-12'tr>><<'pmd-datatable-pagination' l i p>>"
    });


    $.contextMenu({
        selector: '#tbl_resume_pasien_ranap tr', 
        trigger: 'right',
        callback: function(key, options) {
          var rowData = var_tbl_resume_pasien_ranap.rows({ selected: true }).data()[0];
          if (rowData != null) {
var no_rawat = rowData['no_rawat'];
            switch (key) {
                case 'detail' :
                    OpenModal(mlite.url + '/resume_pasien_ranap/detail/' + no_rawat + '?t=' + mlite.token);
                break;
                default :
                break
            } 
          } else {
            bootbox.alert("Silakan pilih data atau klik baris data.");            
          }          
        },
        items: {
            "detail": {name: "View Detail", "icon": "edit", disabled:  {$disabled_menu.read}}
        }
    });

    // ==============================================================
    // FORM VALIDASI
    // ==============================================================

    $("form[name='form_resume_pasien_ranap']").validate({
        rules: {
no_rawat: 'required',
kd_dokter: 'required',
diagnosa_awal: 'required',
alasan: 'required',
keluhan_utama: 'required',
pemeriksaan_fisik: 'required',
jalannya_penyakit: 'required',
pemeriksaan_penunjang: 'required',
hasil_laborat: 'required',
tindakan_dan_operasi: 'required',
obat_di_rs: 'required',
diagnosa_utama: 'required',
kd_diagnosa_utama: 'required',
diagnosa_sekunder: 'required',
kd_diagnosa_sekunder: 'required',
diagnosa_sekunder2: 'required',
kd_diagnosa_sekunder2: 'required',
diagnosa_sekunder3: 'required',
kd_diagnosa_sekunder3: 'required',
diagnosa_sekunder4: 'required',
kd_diagnosa_sekunder4: 'required',
prosedur_utama: 'required',
kd_prosedur_utama: 'required',
prosedur_sekunder: 'required',
kd_prosedur_sekunder: 'required',
prosedur_sekunder2: 'required',
kd_prosedur_sekunder2: 'required',
prosedur_sekunder3: 'required',
kd_prosedur_sekunder3: 'required',
alergi: 'required',
diet: 'required',
lab_belum: 'required',
edukasi: 'required',
cara_keluar: 'required',
ket_keluar: 'required',
keadaan: 'required',
ket_keadaan: 'required',
dilanjutkan: 'required',
ket_dilanjutkan: 'required',
kontrol: 'required',
obat_pulang: 'required'

        },
        messages: {
no_rawat:'No Rawat tidak boleh kosong!',
kd_dokter:'Kd Dokter tidak boleh kosong!',
diagnosa_awal:'Diagnosa Awal tidak boleh kosong!',
alasan:'Alasan tidak boleh kosong!',
keluhan_utama:'Keluhan Utama tidak boleh kosong!',
pemeriksaan_fisik:'Pemeriksaan Fisik tidak boleh kosong!',
jalannya_penyakit:'Jalannya Penyakit tidak boleh kosong!',
pemeriksaan_penunjang:'Pemeriksaan Penunjang tidak boleh kosong!',
hasil_laborat:'Hasil Laborat tidak boleh kosong!',
tindakan_dan_operasi:'Tindakan Dan Operasi tidak boleh kosong!',
obat_di_rs:'Obat Di Rs tidak boleh kosong!',
diagnosa_utama:'Diagnosa Utama tidak boleh kosong!',
kd_diagnosa_utama:'Kd Diagnosa Utama tidak boleh kosong!',
diagnosa_sekunder:'Diagnosa Sekunder tidak boleh kosong!',
kd_diagnosa_sekunder:'Kd Diagnosa Sekunder tidak boleh kosong!',
diagnosa_sekunder2:'Diagnosa Sekunder2 tidak boleh kosong!',
kd_diagnosa_sekunder2:'Kd Diagnosa Sekunder2 tidak boleh kosong!',
diagnosa_sekunder3:'Diagnosa Sekunder3 tidak boleh kosong!',
kd_diagnosa_sekunder3:'Kd Diagnosa Sekunder3 tidak boleh kosong!',
diagnosa_sekunder4:'Diagnosa Sekunder4 tidak boleh kosong!',
kd_diagnosa_sekunder4:'Kd Diagnosa Sekunder4 tidak boleh kosong!',
prosedur_utama:'Prosedur Utama tidak boleh kosong!',
kd_prosedur_utama:'Kd Prosedur Utama tidak boleh kosong!',
prosedur_sekunder:'Prosedur Sekunder tidak boleh kosong!',
kd_prosedur_sekunder:'Kd Prosedur Sekunder tidak boleh kosong!',
prosedur_sekunder2:'Prosedur Sekunder2 tidak boleh kosong!',
kd_prosedur_sekunder2:'Kd Prosedur Sekunder2 tidak boleh kosong!',
prosedur_sekunder3:'Prosedur Sekunder3 tidak boleh kosong!',
kd_prosedur_sekunder3:'Kd Prosedur Sekunder3 tidak boleh kosong!',
alergi:'Alergi tidak boleh kosong!',
diet:'Diet tidak boleh kosong!',
lab_belum:'Lab Belum tidak boleh kosong!',
edukasi:'Edukasi tidak boleh kosong!',
cara_keluar:'Cara Keluar tidak boleh kosong!',
ket_keluar:'Ket Keluar tidak boleh kosong!',
keadaan:'Keadaan tidak boleh kosong!',
ket_keadaan:'Ket Keadaan tidak boleh kosong!',
dilanjutkan:'Dilanjutkan tidak boleh kosong!',
ket_dilanjutkan:'Ket Dilanjutkan tidak boleh kosong!',
kontrol:'Kontrol tidak boleh kosong!',
obat_pulang:'Obat Pulang tidak boleh kosong!'

        },
        submitHandler: function (form) {
var no_rawat= $('#no_rawat').val();
var kd_dokter= $('#kd_dokter').val();
var diagnosa_awal= $('#diagnosa_awal').val();
var alasan= $('#alasan').val();
var keluhan_utama= $('#keluhan_utama').val();
var pemeriksaan_fisik= $('#pemeriksaan_fisik').val();
var jalannya_penyakit= $('#jalannya_penyakit').val();
var pemeriksaan_penunjang= $('#pemeriksaan_penunjang').val();
var hasil_laborat= $('#hasil_laborat').val();
var tindakan_dan_operasi= $('#tindakan_dan_operasi').val();
var obat_di_rs= $('#obat_di_rs').val();
var diagnosa_utama= $('#diagnosa_utama').val();
var kd_diagnosa_utama= $('#kd_diagnosa_utama').val();
var diagnosa_sekunder= $('#diagnosa_sekunder').val();
var kd_diagnosa_sekunder= $('#kd_diagnosa_sekunder').val();
var diagnosa_sekunder2= $('#diagnosa_sekunder2').val();
var kd_diagnosa_sekunder2= $('#kd_diagnosa_sekunder2').val();
var diagnosa_sekunder3= $('#diagnosa_sekunder3').val();
var kd_diagnosa_sekunder3= $('#kd_diagnosa_sekunder3').val();
var diagnosa_sekunder4= $('#diagnosa_sekunder4').val();
var kd_diagnosa_sekunder4= $('#kd_diagnosa_sekunder4').val();
var prosedur_utama= $('#prosedur_utama').val();
var kd_prosedur_utama= $('#kd_prosedur_utama').val();
var prosedur_sekunder= $('#prosedur_sekunder').val();
var kd_prosedur_sekunder= $('#kd_prosedur_sekunder').val();
var prosedur_sekunder2= $('#prosedur_sekunder2').val();
var kd_prosedur_sekunder2= $('#kd_prosedur_sekunder2').val();
var prosedur_sekunder3= $('#prosedur_sekunder3').val();
var kd_prosedur_sekunder3= $('#kd_prosedur_sekunder3').val();
var alergi= $('#alergi').val();
var diet= $('#diet').val();
var lab_belum= $('#lab_belum').val();
var edukasi= $('#edukasi').val();
var cara_keluar= $('#cara_keluar').val();
var ket_keluar= $('#ket_keluar').val();
var keadaan= $('#keadaan').val();
var ket_keadaan= $('#ket_keadaan').val();
var dilanjutkan= $('#dilanjutkan').val();
var ket_dilanjutkan= $('#ket_dilanjutkan').val();
var kontrol= $('#kontrol').val();
var obat_pulang= $('#obat_pulang').val();

var typeact = $('#typeact').val();

var formData = new FormData(form); // tambahan
formData.append('typeact', typeact); // tambahan

            $.ajax({
                url: "{?=url(['resume_pasien_ranap','aksi'])?}",
                method: "POST",
                contentType: false, // tambahan
                processData: false, // tambahan
                data: formData,
                success: function (data) {
                    data = JSON.parse(data);
                    var audio = new Audio('{?=url()?}/assets/sound/' + data.status + '.mp3');
                    audio.play();
                    if (typeact == "add") {
                        if(data.status === 'success') {
                            bootbox.alert('<span class="text-success">' + data.msg + '</span>');
                            $("#modal_resume_pasien_ranap").modal('hide');
                        } else {
                            bootbox.alert('<span class="text-danger">' + data.msg + '</span>');
                        }    
                    }
                    else if (typeact == "edit") {
                        if(data.status === 'success') {
                            bootbox.alert('<span class="text-success">' + data.msg + '</span>');
                            $("#modal_resume_pasien_ranap").modal('hide');
                        } else {
                            bootbox.alert('<span class="text-danger">' + data.msg + '</span>');
                        }    
                    }
                    if(typeof ws != 'undefined' && typeof ws.readyState != 'undefined' && ws.readyState == 1){
                        let payload = {
                            'action' : typeact
                        }
                        ws.send(JSON.stringify(payload));
                    } 
                    var_tbl_resume_pasien_ranap.draw();
                }
            })
        }
    });

    if(typeof ws != 'undefined' && typeof ws.readyState != 'undefined' && ws.readyState == 1){
        ws.onmessage = function(response){
            try{
                output = JSON.parse(response.data);
                if(output['action'] == 'add'){
                    var_tbl_resume_pasien_ranap.draw();
                }
                if(output['action'] == 'edit'){
                    var_tbl_resume_pasien_ranap.draw();
                }
                if(output['action'] == 'del'){
                    var_tbl_resume_pasien_ranap.draw();
                }
            }catch(e){
                console.log(e);
            }
        }
    }

    // ==============================================================
    // KETIKA TOMBOL SEARCH DITEKAN
    // ==============================================================
    $('#filter_search_resume_pasien_ranap').click(function () {
        var_tbl_resume_pasien_ranap.draw();
    });

    // ===========================================
    // KETIKA TOMBOL EDIT DITEKAN
    // ===========================================

    $("#edit_data_resume_pasien_ranap").click(function () {
        var rowData = var_tbl_resume_pasien_ranap.rows({ selected: true }).data()[0];
        if (rowData != null) {

            var no_rawat = rowData['no_rawat'];
var kd_dokter = rowData['kd_dokter'];
var diagnosa_awal = rowData['diagnosa_awal'];
var alasan = rowData['alasan'];
var keluhan_utama = rowData['keluhan_utama'];
var pemeriksaan_fisik = rowData['pemeriksaan_fisik'];
var jalannya_penyakit = rowData['jalannya_penyakit'];
var pemeriksaan_penunjang = rowData['pemeriksaan_penunjang'];
var hasil_laborat = rowData['hasil_laborat'];
var tindakan_dan_operasi = rowData['tindakan_dan_operasi'];
var obat_di_rs = rowData['obat_di_rs'];
var diagnosa_utama = rowData['diagnosa_utama'];
var kd_diagnosa_utama = rowData['kd_diagnosa_utama'];
var diagnosa_sekunder = rowData['diagnosa_sekunder'];
var kd_diagnosa_sekunder = rowData['kd_diagnosa_sekunder'];
var diagnosa_sekunder2 = rowData['diagnosa_sekunder2'];
var kd_diagnosa_sekunder2 = rowData['kd_diagnosa_sekunder2'];
var diagnosa_sekunder3 = rowData['diagnosa_sekunder3'];
var kd_diagnosa_sekunder3 = rowData['kd_diagnosa_sekunder3'];
var diagnosa_sekunder4 = rowData['diagnosa_sekunder4'];
var kd_diagnosa_sekunder4 = rowData['kd_diagnosa_sekunder4'];
var prosedur_utama = rowData['prosedur_utama'];
var kd_prosedur_utama = rowData['kd_prosedur_utama'];
var prosedur_sekunder = rowData['prosedur_sekunder'];
var kd_prosedur_sekunder = rowData['kd_prosedur_sekunder'];
var prosedur_sekunder2 = rowData['prosedur_sekunder2'];
var kd_prosedur_sekunder2 = rowData['kd_prosedur_sekunder2'];
var prosedur_sekunder3 = rowData['prosedur_sekunder3'];
var kd_prosedur_sekunder3 = rowData['kd_prosedur_sekunder3'];
var alergi = rowData['alergi'];
var diet = rowData['diet'];
var lab_belum = rowData['lab_belum'];
var edukasi = rowData['edukasi'];
var cara_keluar = rowData['cara_keluar'];
var ket_keluar = rowData['ket_keluar'];
var keadaan = rowData['keadaan'];
var ket_keadaan = rowData['ket_keadaan'];
var dilanjutkan = rowData['dilanjutkan'];
var ket_dilanjutkan = rowData['ket_dilanjutkan'];
var kontrol = rowData['kontrol'];
var obat_pulang = rowData['obat_pulang'];

            $("#typeact").val("edit");
  
            $('#no_rawat').val(no_rawat);
$('#kd_dokter').val(kd_dokter);
$('#diagnosa_awal').val(diagnosa_awal);
$('#alasan').val(alasan);
$('#keluhan_utama').val(keluhan_utama);
$('#pemeriksaan_fisik').val(pemeriksaan_fisik);
$('#jalannya_penyakit').val(jalannya_penyakit);
$('#pemeriksaan_penunjang').val(pemeriksaan_penunjang);
$('#hasil_laborat').val(hasil_laborat);
$('#tindakan_dan_operasi').val(tindakan_dan_operasi);
$('#obat_di_rs').val(obat_di_rs);
$('#diagnosa_utama').val(diagnosa_utama);
$('#kd_diagnosa_utama').val(kd_diagnosa_utama);
$('#diagnosa_sekunder').val(diagnosa_sekunder);
$('#kd_diagnosa_sekunder').val(kd_diagnosa_sekunder);
$('#diagnosa_sekunder2').val(diagnosa_sekunder2);
$('#kd_diagnosa_sekunder2').val(kd_diagnosa_sekunder2);
$('#diagnosa_sekunder3').val(diagnosa_sekunder3);
$('#kd_diagnosa_sekunder3').val(kd_diagnosa_sekunder3);
$('#diagnosa_sekunder4').val(diagnosa_sekunder4);
$('#kd_diagnosa_sekunder4').val(kd_diagnosa_sekunder4);
$('#prosedur_utama').val(prosedur_utama);
$('#kd_prosedur_utama').val(kd_prosedur_utama);
$('#prosedur_sekunder').val(prosedur_sekunder);
$('#kd_prosedur_sekunder').val(kd_prosedur_sekunder);
$('#prosedur_sekunder2').val(prosedur_sekunder2);
$('#kd_prosedur_sekunder2').val(kd_prosedur_sekunder2);
$('#prosedur_sekunder3').val(prosedur_sekunder3);
$('#kd_prosedur_sekunder3').val(kd_prosedur_sekunder3);
$('#alergi').val(alergi);
$('#diet').val(diet);
$('#lab_belum').val(lab_belum);
$('#edukasi').val(edukasi);
$('#cara_keluar').val(cara_keluar);
$('#ket_keluar').val(ket_keluar);
$('#keadaan').val(keadaan);
$('#ket_keadaan').val(ket_keadaan);
$('#dilanjutkan').val(dilanjutkan);
$('#ket_dilanjutkan').val(ket_dilanjutkan);
$('#kontrol').val(kontrol);
$('#obat_pulang').val(obat_pulang);

            $("#no_rawat").prop('readonly', true); // GA BISA DIEDIT KALI READONLY
            $('#modal-title').text("Edit Data Resume Pasien Ranap");
            $("#modal_resume_pasien_ranap").modal('show');
        }
        else {
            bootbox.alert("Silakan pilih data yang akan di edit.");
        }

    });

    // ==============================================================
    // TOMBOL  DELETE DI CLICK
    // ==============================================================
    jQuery("#hapus_data_resume_pasien_ranap").click(function () {
        var rowData = var_tbl_resume_pasien_ranap.rows({ selected: true }).data()[0];


        if (rowData) {
var no_rawat = rowData['no_rawat'];
            bootbox.confirm('Anda yakin akan menghapus data dengan no_rawat="' + no_rawat, function(result) {
                if(result) {
                    $.ajax({
                        url: "{?=url(['resume_pasien_ranap','aksi'])?}",
                        method: "POST",
                        data: {
                            no_rawat: no_rawat,
                            typeact: 'del'
                        },
                        success: function (data) {
                            data = JSON.parse(data);
                            var audio = new Audio('{?=url()?}/assets/sound/' + data.status + '.mp3');
                            audio.play();
                            if(data.status === 'success') {
                                bootbox.alert('<span class="text-success">' + data.msg + '</span>');
                            } else {
                                bootbox.alert('<span class="text-danger">' + data.msg + '</span>');
                            } 
                            if(typeof ws != 'undefined' && typeof ws.readyState != 'undefined' && ws.readyState == 1){
                                let payload = {
                                    'action' : 'del'
                                }
                                ws.send(JSON.stringify(payload));
                            }
                            var_tbl_resume_pasien_ranap.draw();
                        }
                    })    
                }
            });

        }
        else {
            bootbox.alert("Pilih satu baris untuk dihapus");
        }
    });

    // ==============================================================
    // TOMBOL TAMBAH DATA DI CLICK
    // ==============================================================
    jQuery("#tambah_data_resume_pasien_ranap").click(function () {

        $('#no_rawat').val('');
$('#kd_dokter').val('');
$('#diagnosa_awal').val('');
$('#alasan').val('');
$('#keluhan_utama').val('');
$('#pemeriksaan_fisik').val('');
$('#jalannya_penyakit').val('');
$('#pemeriksaan_penunjang').val('');
$('#hasil_laborat').val('');
$('#tindakan_dan_operasi').val('');
$('#obat_di_rs').val('');
$('#diagnosa_utama').val('');
$('#kd_diagnosa_utama').val('');
$('#diagnosa_sekunder').val('');
$('#kd_diagnosa_sekunder').val('');
$('#diagnosa_sekunder2').val('');
$('#kd_diagnosa_sekunder2').val('');
$('#diagnosa_sekunder3').val('');
$('#kd_diagnosa_sekunder3').val('');
$('#diagnosa_sekunder4').val('');
$('#kd_diagnosa_sekunder4').val('');
$('#prosedur_utama').val('');
$('#kd_prosedur_utama').val('');
$('#prosedur_sekunder').val('');
$('#kd_prosedur_sekunder').val('');
$('#prosedur_sekunder2').val('');
$('#kd_prosedur_sekunder2').val('');
$('#prosedur_sekunder3').val('');
$('#kd_prosedur_sekunder3').val('');
$('#alergi').val('');
$('#diet').val('');
$('#lab_belum').val('');
$('#edukasi').val('');
$('#cara_keluar').val('');
$('#ket_keluar').val('');
$('#keadaan').val('');
$('#ket_keadaan').val('');
$('#dilanjutkan').val('');
$('#ket_dilanjutkan').val('');
$('#kontrol').val('');
$('#obat_pulang').val('');

        $("#typeact").val("add");
        $("#no_rawat").prop('readonly', false);
        
        $('#modal-title').text("Tambah Data Resume Pasien Ranap");
        $("#modal_resume_pasien_ranap").modal('show');
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_resume_pasien_ranap").click(function () {

        var search_field_resume_pasien_ranap = $('#search_field_resume_pasien_ranap').val();
        var search_text_resume_pasien_ranap = $('#search_text_resume_pasien_ranap').val();

        $.ajax({
            url: "{?=url(['resume_pasien_ranap','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat', 
                search_field_resume_pasien_ranap: search_field_resume_pasien_ranap, 
                search_text_resume_pasien_ranap: search_text_resume_pasien_ranap
            },
            dataType: 'json',
            success: function (res) {
                var eTable = "<div class='table-responsive'><table id='tbl_lihat_resume_pasien_ranap' class='table display dataTable' style='width:100%'><thead><th>No Rawat</th><th>Kd Dokter</th><th>Diagnosa Awal</th><th>Alasan</th><th>Keluhan Utama</th><th>Pemeriksaan Fisik</th><th>Jalannya Penyakit</th><th>Pemeriksaan Penunjang</th><th>Hasil Laborat</th><th>Tindakan Dan Operasi</th><th>Obat Di Rs</th><th>Diagnosa Utama</th><th>Kd Diagnosa Utama</th><th>Diagnosa Sekunder</th><th>Kd Diagnosa Sekunder</th><th>Diagnosa Sekunder2</th><th>Kd Diagnosa Sekunder2</th><th>Diagnosa Sekunder3</th><th>Kd Diagnosa Sekunder3</th><th>Diagnosa Sekunder4</th><th>Kd Diagnosa Sekunder4</th><th>Prosedur Utama</th><th>Kd Prosedur Utama</th><th>Prosedur Sekunder</th><th>Kd Prosedur Sekunder</th><th>Prosedur Sekunder2</th><th>Kd Prosedur Sekunder2</th><th>Prosedur Sekunder3</th><th>Kd Prosedur Sekunder3</th><th>Alergi</th><th>Diet</th><th>Lab Belum</th><th>Edukasi</th><th>Cara Keluar</th><th>Ket Keluar</th><th>Keadaan</th><th>Ket Keadaan</th><th>Dilanjutkan</th><th>Ket Dilanjutkan</th><th>Kontrol</th><th>Obat Pulang</th></thead>";
                for (var i = 0; i < res.length; i++) {
                    eTable += "<tr>";
                    eTable += '<td>' + res[i]['no_rawat'] + '</td>';
eTable += '<td>' + res[i]['kd_dokter'] + '</td>';
eTable += '<td>' + res[i]['diagnosa_awal'] + '</td>';
eTable += '<td>' + res[i]['alasan'] + '</td>';
eTable += '<td>' + res[i]['keluhan_utama'] + '</td>';
eTable += '<td>' + res[i]['pemeriksaan_fisik'] + '</td>';
eTable += '<td>' + res[i]['jalannya_penyakit'] + '</td>';
eTable += '<td>' + res[i]['pemeriksaan_penunjang'] + '</td>';
eTable += '<td>' + res[i]['hasil_laborat'] + '</td>';
eTable += '<td>' + res[i]['tindakan_dan_operasi'] + '</td>';
eTable += '<td>' + res[i]['obat_di_rs'] + '</td>';
eTable += '<td>' + res[i]['diagnosa_utama'] + '</td>';
eTable += '<td>' + res[i]['kd_diagnosa_utama'] + '</td>';
eTable += '<td>' + res[i]['diagnosa_sekunder'] + '</td>';
eTable += '<td>' + res[i]['kd_diagnosa_sekunder'] + '</td>';
eTable += '<td>' + res[i]['diagnosa_sekunder2'] + '</td>';
eTable += '<td>' + res[i]['kd_diagnosa_sekunder2'] + '</td>';
eTable += '<td>' + res[i]['diagnosa_sekunder3'] + '</td>';
eTable += '<td>' + res[i]['kd_diagnosa_sekunder3'] + '</td>';
eTable += '<td>' + res[i]['diagnosa_sekunder4'] + '</td>';
eTable += '<td>' + res[i]['kd_diagnosa_sekunder4'] + '</td>';
eTable += '<td>' + res[i]['prosedur_utama'] + '</td>';
eTable += '<td>' + res[i]['kd_prosedur_utama'] + '</td>';
eTable += '<td>' + res[i]['prosedur_sekunder'] + '</td>';
eTable += '<td>' + res[i]['kd_prosedur_sekunder'] + '</td>';
eTable += '<td>' + res[i]['prosedur_sekunder2'] + '</td>';
eTable += '<td>' + res[i]['kd_prosedur_sekunder2'] + '</td>';
eTable += '<td>' + res[i]['prosedur_sekunder3'] + '</td>';
eTable += '<td>' + res[i]['kd_prosedur_sekunder3'] + '</td>';
eTable += '<td>' + res[i]['alergi'] + '</td>';
eTable += '<td>' + res[i]['diet'] + '</td>';
eTable += '<td>' + res[i]['lab_belum'] + '</td>';
eTable += '<td>' + res[i]['edukasi'] + '</td>';
eTable += '<td>' + res[i]['cara_keluar'] + '</td>';
eTable += '<td>' + res[i]['ket_keluar'] + '</td>';
eTable += '<td>' + res[i]['keadaan'] + '</td>';
eTable += '<td>' + res[i]['ket_keadaan'] + '</td>';
eTable += '<td>' + res[i]['dilanjutkan'] + '</td>';
eTable += '<td>' + res[i]['ket_dilanjutkan'] + '</td>';
eTable += '<td>' + res[i]['kontrol'] + '</td>';
eTable += '<td>' + res[i]['obat_pulang'] + '</td>';
                    eTable += "</tr>";
                }
                eTable += "</tbody></table></div>";
                $('#forTable_resume_pasien_ranap').html(eTable);
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_resume_pasien_ranap").modal('show');
    });
        
    // ===========================================
    // Ketika tombol export pdf di tekan
    // ===========================================
    $("#export_pdf").click(function () {

        var doc = new jsPDF('p', 'pt', 'A4'); /* pilih 'l' atau 'p' */
        var img = "{?=base64_encode(file_get_contents(url($settings['logo'])))?}";
        doc.addImage(img, 'JPEG', 20, 10, 50, 50);
        doc.setFontSize(20);
        doc.text("{$settings.nama_instansi}", 80, 35, null, null, null);
        doc.setFontSize(10);
        doc.text("{$settings.alamat} - {$settings.kota} - {$settings.propinsi}", 80, 46, null, null, null);
        doc.text("Telepon: {$settings.nomor_telepon} - Email: {$settings.email}", 80, 56, null, null, null);
        doc.line(20,70,572,70,null); /* doc.line(20,70,820,70,null); --> Jika landscape */
        doc.line(20,72,572,72,null); /* doc.line(20,72,820,72,null); --> Jika landscape */
        doc.setFontSize(14);
        doc.text("Tabel Data Resume Pasien Ranap", 20, 95, null, null, null);
        const totalPagesExp = "{total_pages_count_string}";        
        doc.autoTable({
            html: '#tbl_lihat_resume_pasien_ranap',
            startY: 105,
            margin: {
                left: 20, 
                right: 20
            }, 
            styles: {
                fontSize: 10,
                cellPadding: 5
            }, 
            didDrawPage: data => {
                let footerStr = "Page " + doc.internal.getNumberOfPages();
                if (typeof doc.putTotalPages === 'function') {
                footerStr = footerStr + " of " + totalPagesExp;
                }
                doc.setFontSize(10);
                doc.text(`© ${new Date().getFullYear()} {$settings.nama_instansi}.`, data.settings.margin.left, doc.internal.pageSize.height - 10);                
                doc.text(footerStr, data.settings.margin.left + 480, doc.internal.pageSize.height - 10);
           }
        });
        if (typeof doc.putTotalPages === 'function') {
            doc.putTotalPages(totalPagesExp);
        }
        // doc.save('table_data_resume_pasien_ranap.pdf');
        window.open(doc.output('bloburl'), '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
              
    })

    // ===========================================
    // Ketika tombol export xlsx di tekan
    // ===========================================
    $("#export_xlsx").click(function () {
        let tbl1 = document.getElementById("tbl_lihat_resume_pasien_ranap");
        let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
        let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
        let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
        const new_workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data resume_pasien_ranap");
        XLSX.writeFile(new_workbook, 'tmp_file.xls');
    })

    $("#view_chart").click(function () {
        window.open(mlite.url + '/resume_pasien_ranap/chart?t=' + mlite.token, '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
    })   

});