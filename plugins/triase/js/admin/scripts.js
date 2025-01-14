jQuery().ready(function () {
    var var_tbl_mlite_triase = $('#tbl_mlite_triase').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'dom': 'Bfrtip',
        'searching': false,
        'select': true,
        'colReorder': true,
        "bInfo" : false,
        "ajax": {
            "url": "{?=url([ADMIN,'triase','data'])?}",
            "dataType": "json",
            "type": "POST",
            "data": function (data) {

                // Read values
                var search_field_mlite_triase = $('#search_field_mlite_triase').val();
                var search_text_mlite_triase = $('#search_text_mlite_triase').val();
                
                data.search_field_mlite_triase = search_field_mlite_triase;
                data.search_text_mlite_triase = search_text_mlite_triase;
                
            }
        },
        "columns": [
{ 'data': 'no_rawat' },
{ 'data': 'tgl_kunjungan' },
{ 'data': 'cara_masuk' },
{ 'data': 'alat_transportasi' },
{ 'data': 'alasan_kedatangan' },
{ 'data': 'keterangan_kedatangan' },
{ 'data': 'macam_kasus' },
{ 'data': 'tekanan_darah' },
{ 'data': 'nadi' },
{ 'data': 'pernapasan' },
{ 'data': 'suhu' },
{ 'data': 'saturasi_o2' },
{ 'data': 'nyeri' },
{ 'data': 'jenis_triase' },
{ 'data': 'keluhan_utama' },
{ 'data': 'kebutuhan_khusus' },
{ 'data': 'catatan' },
{ 'data': 'plan' },
{ 'data': 'nik' }

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
{ 'targets': 18}

        ],
        buttons: [],
        "scrollCollapse": true,
        // "scrollY": '48vh', 
        "pageLength":'25', 
        "lengthChange": true,
        "scrollX": true,
        dom: "<'row'<'col-sm-12'tr>><<'pmd-datatable-pagination' l i p>>"
    });

    // ==============================================================
    // FORM VALIDASI
    // ==============================================================

    $("form[name='form_mlite_triase']").validate({
        rules: {
no_rawat: 'required',
tgl_kunjungan: 'required',
cara_masuk: 'required',
alat_transportasi: 'required',
alasan_kedatangan: 'required',
keterangan_kedatangan: 'required',
macam_kasus: 'required',
tekanan_darah: 'required',
nadi: 'required',
pernapasan: 'required',
suhu: 'required',
saturasi_o2: 'required',
nyeri: 'required',
jenis_triase: 'required',
keluhan_utama: 'required',
kebutuhan_khusus: 'required',
catatan: 'required',
plan: 'required',
nik: 'required'

        },
        messages: {
no_rawat:'no_rawat tidak boleh kosong!',
tgl_kunjungan:'tgl_kunjungan tidak boleh kosong!',
cara_masuk:'cara_masuk tidak boleh kosong!',
alat_transportasi:'alat_transportasi tidak boleh kosong!',
alasan_kedatangan:'alasan_kedatangan tidak boleh kosong!',
keterangan_kedatangan:'keterangan_kedatangan tidak boleh kosong!',
macam_kasus:'macam_kasus tidak boleh kosong!',
tekanan_darah:'tekanan_darah tidak boleh kosong!',
nadi:'nadi tidak boleh kosong!',
pernapasan:'pernapasan tidak boleh kosong!',
suhu:'suhu tidak boleh kosong!',
saturasi_o2:'saturasi_o2 tidak boleh kosong!',
nyeri:'nyeri tidak boleh kosong!',
jenis_triase:'jenis_triase tidak boleh kosong!',
keluhan_utama:'keluhan_utama tidak boleh kosong!',
kebutuhan_khusus:'kebutuhan_khusus tidak boleh kosong!',
catatan:'catatan tidak boleh kosong!',
plan:'plan tidak boleh kosong!',
nik:'nik tidak boleh kosong!'

        },
        submitHandler: function (form) {
 var no_rawat= $('#no_rawat').val();
var tgl_kunjungan= $('#tgl_kunjungan').val();
var cara_masuk= $('#cara_masuk').val();
var alat_transportasi= $('#alat_transportasi').val();
var alasan_kedatangan= $('#alasan_kedatangan').val();
var keterangan_kedatangan= $('#keterangan_kedatangan').val();
var macam_kasus= $('#macam_kasus').val();
var tekanan_darah= $('#tekanan_darah').val();
var nadi= $('#nadi').val();
var pernapasan= $('#pernapasan').val();
var suhu= $('#suhu').val();
var saturasi_o2= $('#saturasi_o2').val();
var nyeri= $('#nyeri').val();
var jenis_triase= $('#jenis_triase').val();
var keluhan_utama= $('#keluhan_utama').val();
var kebutuhan_khusus= $('#kebutuhan_khusus').val();
var catatan= $('#catatan').val();
var plan= $('#plan').val();
var nik= $('#nik').val();

 var typeact = $('#typeact').val();

 var formData = new FormData(form); // tambahan
 formData.append('typeact', typeact); // tambahan

            $.ajax({
                url: "{?=url([ADMIN,'triase','aksi'])?}",
                method: "POST",
                contentType: false, // tambahan
                processData: false, // tambahan
                data: formData,
                success: function (data) {
                    if (typeact == "add") {
                        alert("Data Berhasil Ditambah");
                    }
                    else if (typeact == "edit") {
                        alert("Data Berhasil Diubah");
                    }
                    $("#modal_cs").hide();
                    location.reload(true);
                }
            })
        }
    });

    // ==============================================================
    // KETIKA MENGETIK DI INPUT SEARCH
    // ==============================================================
    $('#search_text_mlite_triase').keyup(function () {
        var_tbl_mlite_triase.draw();
    });
    // ==============================================================
    // CLICK TANDA X DI INPUT SEARCH
    // ==============================================================
    $("#searchclear_mlite_triase").click(function () {
        $("#search_text_mlite_triase").val("");
        var_tbl_mlite_triase.draw();
    });

    // ===========================================
    // Ketika tombol Edit di tekan
    // ===========================================

    $("#edit_data_mlite_triase").click(function () {
        var rowData = var_tbl_mlite_triase.rows({ selected: true }).data()[0];
        if (rowData != null) {

            var no_rawat = rowData['no_rawat'];
var tgl_kunjungan = rowData['tgl_kunjungan'];
var cara_masuk = rowData['cara_masuk'];
var alat_transportasi = rowData['alat_transportasi'];
var alasan_kedatangan = rowData['alasan_kedatangan'];
var keterangan_kedatangan = rowData['keterangan_kedatangan'];
var macam_kasus = rowData['macam_kasus'];
var tekanan_darah = rowData['tekanan_darah'];
var nadi = rowData['nadi'];
var pernapasan = rowData['pernapasan'];
var suhu = rowData['suhu'];
var saturasi_o2 = rowData['saturasi_o2'];
var nyeri = rowData['nyeri'];
var jenis_triase = rowData['jenis_triase'];
var keluhan_utama = rowData['keluhan_utama'];
var kebutuhan_khusus = rowData['kebutuhan_khusus'];
var catatan = rowData['catatan'];
var plan = rowData['plan'];
var nik = rowData['nik'];



            $("#typeact").val("edit");
  
            $('#no_rawat').val(no_rawat);
$('#tgl_kunjungan').val(tgl_kunjungan);
$('#cara_masuk').val(cara_masuk);
$('#alat_transportasi').val(alat_transportasi);
$('#alasan_kedatangan').val(alasan_kedatangan);
$('#keterangan_kedatangan').val(keterangan_kedatangan);
$('#macam_kasus').val(macam_kasus);
$('#tekanan_darah').val(tekanan_darah);
$('#nadi').val(nadi);
$('#pernapasan').val(pernapasan);
$('#suhu').val(suhu);
$('#saturasi_o2').val(saturasi_o2);
$('#nyeri').val(nyeri);
$('#jenis_triase').val(jenis_triase);
$('#keluhan_utama').val(keluhan_utama);
$('#kebutuhan_khusus').val(kebutuhan_khusus);
$('#catatan').val(catatan);
$('#plan').val(plan);
$('#nik').val(nik);

            //$("#no_rawat").prop('disabled', true); // GA BISA DIEDIT KALI DISABLE
            $('#modal-title').text("Edit Data Triase");
            $("#modal_mlite_triase").modal();
        }
        else {
            alert("Silakan pilih data yang akan di edit.");
        }

    });

    // ==============================================================
    // TOMBOL  DELETE DI CLICK
    // ==============================================================
    jQuery("#hapus_data_mlite_triase").click(function () {
        var rowData = var_tbl_mlite_triase.rows({ selected: true }).data()[0];


        if (rowData) {
var no_rawat = rowData['no_rawat'];
            var a = confirm("Anda yakin akan menghapus data dengan no_rawat=" + no_rawat);
            if (a) {

                $.ajax({
                    url: "{?=url([ADMIN,'triase','aksi'])?}",
                    method: "POST",
                    data: {
                        no_rawat: no_rawat,
                        typeact: 'del'
                    },
                    success: function (data) {
                        data = JSON.parse(data);
                        if(data.status === 'success') {
                            alert(data.msg);
                        } else {
                            alert(data.msg);
                        }
                        location.reload(true);
                    }
                })
            }
        }
        else {
            alert("Pilih satu baris untuk dihapus");
        }
    });

    // ==============================================================
    // TOMBOL TAMBAH DATA DI CLICK
    // ==============================================================

    let searchParams = new URLSearchParams(window.location.search)

    if(window.location.search.indexOf('no_rawat') !== -1) { 
        $('#search_text_mlite_triase').val(searchParams.get('no_rawat'));
        var_tbl_mlite_triase.draw();
        if(searchParams.get('modal') == 'true') {
            $("#modal_mlite_triase").modal();
            $('#no_rawat').val(searchParams.get('no_rawat'));    
        }
    }

    jQuery("#tambah_data_mlite_triase").click(function () {

        $('#no_rawat').val('');

        if(window.location.search.indexOf('no_rawat') !== -1) { 
            $('#no_rawat').val(searchParams.get('no_rawat'));
        }

$('#tgl_kunjungan').val('');
$('#cara_masuk').val('');
$('#alat_transportasi').val('');
$('#alasan_kedatangan').val('');
$('#keterangan_kedatangan').val('');
$('#macam_kasus').val('');
$('#tekanan_darah').val('');
$('#nadi').val('');
$('#pernapasan').val('');
$('#suhu').val('');
$('#saturasi_o2').val('');
$('#nyeri').val('');
$('#jenis_triase').val('');
$('#keluhan_utama').val('');
$('#kebutuhan_khusus').val('');
$('#catatan').val('');
$('#plan').val('');
$('#nik').val('{?=$this->core->getUserInfo('username', null, true)?}');


        $("#typeact").val("add");
        $("#no_rawat").prop('disabled', false);
        
        $('#modal-title').text("Tambah Data Triase");
        $("#modal_mlite_triase").modal();
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_mlite_triase").click(function () {

        var search_field_mlite_triase = $('#search_field_mlite_triase').val();
        var search_text_mlite_triase = $('#search_text_mlite_triase').val();

        $.ajax({
            url: "{?=url([ADMIN,'triase','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat', 
                search_field_mlite_triase: search_field_mlite_triase, 
                search_text_mlite_triase: search_text_mlite_triase
            },
            dataType: 'json',
            success: function (res) {
                var eTable = "<div class='table-responsive'><table id='tbl_lihat_mlite_triase' class='table display dataTable' style='width:100%'><thead><th>No Rawat</th><th>Tgl Kunjungan</th><th>Cara Masuk</th><th>Alat Transportasi</th><th>Alasan Kedatangan</th><th>Keterangan Kedatangan</th><th>Macam Kasus</th><th>Tekanan Darah</th><th>Nadi</th><th>Pernapasan</th><th>Suhu</th><th>Saturasi O2</th><th>Nyeri</th><th>Jenis Triase</th><th>Keluhan Utama</th><th>Kebutuhan Khusus</th><th>Catatan</th><th>Plan</th><th>Nik</th></thead>";
                for (var i = 0; i < res.length; i++) {
                    eTable += "<tr>";
                    eTable += '<td>' + res[i]['no_rawat'] + '</td>';
eTable += '<td>' + res[i]['tgl_kunjungan'] + '</td>';
eTable += '<td>' + res[i]['cara_masuk'] + '</td>';
eTable += '<td>' + res[i]['alat_transportasi'] + '</td>';
eTable += '<td>' + res[i]['alasan_kedatangan'] + '</td>';
eTable += '<td>' + res[i]['keterangan_kedatangan'] + '</td>';
eTable += '<td>' + res[i]['macam_kasus'] + '</td>';
eTable += '<td>' + res[i]['tekanan_darah'] + '</td>';
eTable += '<td>' + res[i]['nadi'] + '</td>';
eTable += '<td>' + res[i]['pernapasan'] + '</td>';
eTable += '<td>' + res[i]['suhu'] + '</td>';
eTable += '<td>' + res[i]['saturasi_o2'] + '</td>';
eTable += '<td>' + res[i]['nyeri'] + '</td>';
eTable += '<td>' + res[i]['jenis_triase'] + '</td>';
eTable += '<td>' + res[i]['keluhan_utama'] + '</td>';
eTable += '<td>' + res[i]['kebutuhan_khusus'] + '</td>';
eTable += '<td>' + res[i]['catatan'] + '</td>';
eTable += '<td>' + res[i]['plan'] + '</td>';
eTable += '<td>' + res[i]['nik'] + '</td>';
                    eTable += "</tr>";
                }
                eTable += "</tbody></table></div>";
                $('#forTable_mlite_triase').html(eTable);
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_mlite_triase").modal();
    });

    // ==============================================================
    // TOMBOL DETAIL mlite_triase DI CLICK
    // ==============================================================
    jQuery("#lihat_detail_mlite_triase").click(function (event) {

        var rowData = var_tbl_mlite_triase.rows({ selected: true }).data()[0];

        if (rowData) {
            var no_rawat = rowData['no_rawat'];
            var baseURL = mlite.url + '/' + mlite.admin;
            event.preventDefault();
            var loadURL =  baseURL + '/triase/detail/' + no_rawat.replace(/\//g,'') + '?t=' + mlite.token;
        
            var modal = $('#modal_detail_mlite_triase');
            var modalContent = $('#modal_detail_mlite_triase .modal-content');
        
            modal.off('show.bs.modal');
            modal.on('show.bs.modal', function () {
                modalContent.load(loadURL);
            }).modal();
            return false;
        
        }
        else {
            alert("Pilih satu baris untuk detail");
        }
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
        doc.text("Tabel Data Mlite Triase", 20, 95, null, null, null);
        const totalPagesExp = "{total_pages_count_string}";        
        doc.autoTable({
            html: '#tbl_lihat_mlite_triase',
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
                doc.text(footerStr, data.settings.margin.left, doc.internal.pageSize.height - 10);
           }
        });
        if (typeof doc.putTotalPages === 'function') {
            doc.putTotalPages(totalPagesExp);
        }
        // doc.save('table_data_mlite_triase.pdf')
        window.open(doc.output('bloburl'), '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
              
    })

    // ===========================================
    // Ketika tombol export xlsx di tekan
    // ===========================================
    $("#export_xlsx").click(function () {
        let tbl1 = document.getElementById("tbl_lihat_mlite_triase");
        let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
        let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
        let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
        const new_workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data mlite_triase");
        XLSX.writeFile(new_workbook, 'tmp_file.xls');
    })
});