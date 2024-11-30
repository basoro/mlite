jQuery().ready(function () {
    var var_tbl_mlite_fenton = $('#tbl_mlite_fenton').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'dom': 'Bfrtip',
        'searching': false,
        'select': true,
        'colReorder': true,
        "bInfo" : false,
        "ajax": {
            "url": "{?=url([ADMIN,'grafik_fenton','data'])?}",
            "dataType": "json",
            "type": "POST",
            "data": function (data) {

                // Read values
                var search_field_mlite_fenton = $('#search_field_mlite_fenton').val();
                var search_text_mlite_fenton = $('#search_text_mlite_fenton').val();
                
                data.search_field_mlite_fenton = search_field_mlite_fenton;
                data.search_text_mlite_fenton = search_text_mlite_fenton;
                
            }
        },
        "columns": [
{ 'data': 'id' },
{ 'data': 'no_rawat' },
{ 'data': 'nm_pasien' },
{ 'data': 'jk' },
{ 'data': 'tanggal' },
{ 'data': 'usia_kehamilan' },
{ 'data': 'tgl_lahir' },
{ 'data': 'berat_badan' },
{ 'data': 'lingkar_kepala' },
{ 'data': 'panjang_badan' },
{ 'data': 'petugas' },
{ 'data': 'created_at' },
{ 'data': 'deleted_at' }

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
{ 'targets': 12}

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

    $("form[name='form_mlite_fenton']").validate({
        rules: {
id: 'required',
no_rawat: 'required',
tanggal: 'required',
usia_kehamilan: 'required',
tgl_lahir: 'required',
berat_badan: 'required',
lingkar_kepala: 'required',
panjang_badan: 'required',
petugas: 'required',
created_at: 'required',
deleted_at: 'required'

        },
        messages: {
id:'id tidak boleh kosong!',
no_rawat:'no_rawat tidak boleh kosong!',
tanggal:'tanggal tidak boleh kosong!',
usia_kehamilan:'usia_kehamilan tidak boleh kosong!',
tgl_lahir:'tgl_lahir tidak boleh kosong!',
berat_badan:'berat_badan tidak boleh kosong!',
lingkar_kepala:'lingkar_kepala tidak boleh kosong!',
panjang_badan:'panjang_badan tidak boleh kosong!',
petugas:'petugas tidak boleh kosong!',
created_at:'created_at tidak boleh kosong!',
deleted_at:'deleted_at tidak boleh kosong!'

        },
        submitHandler: function (form) {
 var id= $('#id').val();
var no_rawat= $('#no_rawat').val();
var tanggal= $('#tanggal').val();
var usia_kehamilan= $('#usia_kehamilan').val();
var tgl_lahir= $('#tgl_lahir').val();
var berat_badan= $('#berat_badan').val();
var lingkar_kepala= $('#lingkar_kepala').val();
var panjang_badan= $('#panjang_badan').val();
var petugas= $('#petugas').val();
var created_at= $('#created_at').val();
var deleted_at= $('#deleted_at').val();

 var typeact = $('#typeact').val();

 var formData = new FormData(form); // tambahan
 formData.append('typeact', typeact); // tambahan

            $.ajax({
                url: "{?=url([ADMIN,'grafik_fenton','aksi'])?}",
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
    $('#search_text_mlite_fenton').keyup(function () {
        var_tbl_mlite_fenton.draw();
    });
    // ==============================================================
    // CLICK TANDA X DI INPUT SEARCH
    // ==============================================================
    $("#searchclear_mlite_fenton").click(function () {
        $("#search_text_mlite_fenton").val("");
        var_tbl_mlite_fenton.draw();
    });

    // ===========================================
    // Ketika tombol Edit di tekan
    // ===========================================

    $("#edit_data_mlite_fenton").click(function () {
        var rowData = var_tbl_mlite_fenton.rows({ selected: true }).data()[0];
        if (rowData != null) {

            var id = rowData['id'];
var no_rawat = rowData['no_rawat'];
var tanggal = rowData['tanggal'];
var usia_kehamilan = rowData['usia_kehamilan'];
var tgl_lahir = rowData['tgl_lahir'];
var berat_badan = rowData['berat_badan'];
var lingkar_kepala = rowData['lingkar_kepala'];
var panjang_badan = rowData['panjang_badan'];
var petugas = rowData['petugas'];
var created_at = rowData['created_at'];
var deleted_at = rowData['deleted_at'];



            $("#typeact").val("edit");
  
            $('#id').val(id);
$('#no_rawat').val(no_rawat);
$('#tanggal').val(tanggal);
$('#usia_kehamilan').val(usia_kehamilan);
$('#tgl_lahir').val(tgl_lahir);
$('#berat_badan').val(berat_badan);
$('#lingkar_kepala').val(lingkar_kepala);
$('#panjang_badan').val(panjang_badan);
$('#petugas').val(petugas);
$('#created_at').val(created_at);
$('#deleted_at').val(deleted_at);

            //$("#id").prop('disabled', true); // GA BISA DIEDIT KALI DISABLE
            $('#modal-title').text("Edit Data Grafik Fenton");
            $("#modal_mlite_fenton").modal();
        }
        else {
            alert("Silakan pilih data yang akan di edit.");
        }

    });

    // ==============================================================
    // TOMBOL  DELETE DI CLICK
    // ==============================================================
    jQuery("#hapus_data_mlite_fenton").click(function () {
        var rowData = var_tbl_mlite_fenton.rows({ selected: true }).data()[0];


        if (rowData) {
var id = rowData['id'];
            var a = confirm("Anda yakin akan menghapus data dengan id=" + id);
            if (a) {

                $.ajax({
                    url: "{?=url([ADMIN,'grafik_fenton','aksi'])?}",
                    method: "POST",
                    data: {
                        id: id,
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
    jQuery("#tambah_data_mlite_fenton").click(function () {

        $('#id').val('');
$('#no_rawat').val('');
$('#tanggal').val('');
$('#usia_kehamilan').val('');
$('#tgl_lahir').val('');
$('#berat_badan').val('');
$('#lingkar_kepala').val('');
$('#panjang_badan').val('');
$('#petugas').val('');
$('#created_at').val('');
$('#deleted_at').val('');


        $("#typeact").val("add");
        $("#id").prop('disabled', false);
        
        $('#modal-title').text("Tambah Data Grafik Fenton");
        $("#modal_mlite_fenton").modal();
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_mlite_fenton").click(function () {

        var search_field_mlite_fenton = $('#search_field_mlite_fenton').val();
        var search_text_mlite_fenton = $('#search_text_mlite_fenton').val();

        $.ajax({
            url: "{?=url([ADMIN,'grafik_fenton','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat', 
                search_field_mlite_fenton: search_field_mlite_fenton, 
                search_text_mlite_fenton: search_text_mlite_fenton
            },
            dataType: 'json',
            success: function (res) {
                var eTable = "<div class='table-responsive'><table id='tbl_lihat_mlite_fenton' class='table display dataTable' style='width:100%'><thead><th>Id</th><th>No Rawat</th><th>Tanggal</th><th>Usia Kehamilan</th><th>Tgl Lahir</th><th>Berat Badan</th><th>Lingkar Kepala</th><th>Panjang Badan</th><th>Petugas</th><th>Created At</th><th>Deleted At</th></thead>";
                for (var i = 0; i < res.length; i++) {
                    eTable += "<tr>";
                    eTable += '<td>' + res[i]['id'] + '</td>';
eTable += '<td>' + res[i]['no_rawat'] + '</td>';
eTable += '<td>' + res[i]['tanggal'] + '</td>';
eTable += '<td>' + res[i]['usia_kehamilan'] + '</td>';
eTable += '<td>' + res[i]['tgl_lahir'] + '</td>';
eTable += '<td>' + res[i]['berat_badan'] + '</td>';
eTable += '<td>' + res[i]['lingkar_kepala'] + '</td>';
eTable += '<td>' + res[i]['panjang_badan'] + '</td>';
eTable += '<td>' + res[i]['petugas'] + '</td>';
eTable += '<td>' + res[i]['created_at'] + '</td>';
eTable += '<td>' + res[i]['deleted_at'] + '</td>';
                    eTable += "</tr>";
                }
                eTable += "</tbody></table></div>";
                $('#forTable_mlite_fenton').html(eTable);
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_mlite_fenton").modal();
    });

    // ==============================================================
    // TOMBOL DETAIL mlite_fenton DI CLICK
    // ==============================================================
    jQuery("#lihat_detail_mlite_fenton").click(function (event) {

        var rowData = var_tbl_mlite_fenton.rows({ selected: true }).data()[0];

        if (rowData) {
            var id = rowData['id'];
            var baseURL = mlite.url + '/' + mlite.admin;
            event.preventDefault();
            var loadURL =  baseURL + '/grafik_fenton/detail/' + id + '?t=' + mlite.token;
        
            var modal = $('#modal_detail_mlite_fenton');
            var modalContent = $('#modal_detail_mlite_fenton .modal-content');
        
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

    jQuery("#lihat_detail_mlite_fenton_grafik").click(function (event) {

        var rowData = var_tbl_mlite_fenton.rows({ selected: true }).data()[0];

        if (rowData) {
            var no_rawat = rowData['no_rawat'];
            var baseURL = mlite.url + '/' + mlite.admin;
            event.preventDefault();
            var loadURL =  baseURL + '/grafik_fenton/grafik/' + no_rawat.replace(/\//g,'') + '?t=' + mlite.token;
            window.open(loadURL, '_self');
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
        doc.text("Tabel Data Mlite Fenton", 20, 95, null, null, null);
        const totalPagesExp = "{total_pages_count_string}";        
        doc.autoTable({
            html: '#tbl_lihat_mlite_fenton',
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
        // doc.save('table_data_mlite_fenton.pdf')
        window.open(doc.output('bloburl'), '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
              
    })

    // ===========================================
    // Ketika tombol export xlsx di tekan
    // ===========================================
    $("#export_xlsx").click(function () {
        let tbl1 = document.getElementById("tbl_lihat_mlite_fenton");
        let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
        let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
        let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
        const new_workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data mlite_fenton");
        XLSX.writeFile(new_workbook, 'tmp_file.xls');
    })
});