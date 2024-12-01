jQuery().ready(function () {
    var var_tbl_mlite_penilaian_ulang_nyeri = $('#tbl_mlite_penilaian_ulang_nyeri').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'dom': 'Bfrtip',
        'searching': false,
        'select': true,
        'colReorder': true,
        "bInfo" : false,
        "ajax": {
            "url": "{?=url([ADMIN,'penilaian_ulang_nyeri','data'])?}",
            "dataType": "json",
            "type": "POST",
            "data": function (data) {

                // Read values
                var search_field_mlite_penilaian_ulang_nyeri = $('#search_field_mlite_penilaian_ulang_nyeri').val();
                var search_text_mlite_penilaian_ulang_nyeri = $('#search_text_mlite_penilaian_ulang_nyeri').val();
                
                data.search_field_mlite_penilaian_ulang_nyeri = search_field_mlite_penilaian_ulang_nyeri;
                data.search_text_mlite_penilaian_ulang_nyeri = search_text_mlite_penilaian_ulang_nyeri;
                
            }
        },
        "columns": [
{ 'data': 'no_rawat' },
{ 'data': 'tanggal' },
{ 'data': 'nyeri' },
{ 'data': 'provokes' },
{ 'data': 'ket_provokes' },
{ 'data': 'quality' },
{ 'data': 'ket_quality' },
{ 'data': 'lokasi' },
{ 'data': 'menyebar' },
{ 'data': 'skala_nyeri' },
{ 'data': 'durasi' },
{ 'data': 'nyeri_hilang' },
{ 'data': 'ket_nyeri' },
{ 'data': 'manajemen_nyeri' },
{ 'data': 'nip' }

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
{ 'targets': 14}

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

    $("form[name='form_mlite_penilaian_ulang_nyeri']").validate({
        rules: {
no_rawat: 'required',
tanggal: 'required',
nyeri: 'required',
provokes: 'required',
ket_provokes: 'required',
quality: 'required',
ket_quality: 'required',
lokasi: 'required',
menyebar: 'required',
skala_nyeri: 'required',
durasi: 'required',
nyeri_hilang: 'required',
ket_nyeri: 'required',
manajemen_nyeri: 'required',
nip: 'required'

        },
        messages: {
no_rawat:'no_rawat tidak boleh kosong!',
tanggal:'tanggal tidak boleh kosong!',
nyeri:'nyeri tidak boleh kosong!',
provokes:'provokes tidak boleh kosong!',
ket_provokes:'ket_provokes tidak boleh kosong!',
quality:'quality tidak boleh kosong!',
ket_quality:'ket_quality tidak boleh kosong!',
lokasi:'lokasi tidak boleh kosong!',
menyebar:'menyebar tidak boleh kosong!',
skala_nyeri:'skala_nyeri tidak boleh kosong!',
durasi:'durasi tidak boleh kosong!',
nyeri_hilang:'nyeri_hilang tidak boleh kosong!',
ket_nyeri:'ket_nyeri tidak boleh kosong!',
manajemen_nyeri:'manajemen_nyeri tidak boleh kosong!',
nip:'nip tidak boleh kosong!'

        },
        submitHandler: function (form) {
 var no_rawat= $('#no_rawat').val();
var tanggal= $('#tanggal').val();
var nyeri= $('#nyeri').val();
var provokes= $('#provokes').val();
var ket_provokes= $('#ket_provokes').val();
var quality= $('#quality').val();
var ket_quality= $('#ket_quality').val();
var lokasi= $('#lokasi').val();
var menyebar= $('#menyebar').val();
var skala_nyeri= $('#skala_nyeri').val();
var durasi= $('#durasi').val();
var nyeri_hilang= $('#nyeri_hilang').val();
var ket_nyeri= $('#ket_nyeri').val();
var manajemen_nyeri= $('#manajemen_nyeri').val();
var nip= $('#nip').val();

 var typeact = $('#typeact').val();

 var formData = new FormData(form); // tambahan
 formData.append('typeact', typeact); // tambahan

            $.ajax({
                url: "{?=url([ADMIN,'penilaian_ulang_nyeri','aksi'])?}",
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
    $('#search_text_mlite_penilaian_ulang_nyeri').keyup(function () {
        var_tbl_mlite_penilaian_ulang_nyeri.draw();
    });
    // ==============================================================
    // CLICK TANDA X DI INPUT SEARCH
    // ==============================================================
    $("#searchclear_mlite_penilaian_ulang_nyeri").click(function () {
        $("#search_text_mlite_penilaian_ulang_nyeri").val("");
        var_tbl_mlite_penilaian_ulang_nyeri.draw();
    });

    // ===========================================
    // Ketika tombol Edit di tekan
    // ===========================================

    $("#edit_data_mlite_penilaian_ulang_nyeri").click(function () {
        var rowData = var_tbl_mlite_penilaian_ulang_nyeri.rows({ selected: true }).data()[0];
        if (rowData != null) {

            var no_rawat = rowData['no_rawat'];
var tanggal = rowData['tanggal'];
var nyeri = rowData['nyeri'];
var provokes = rowData['provokes'];
var ket_provokes = rowData['ket_provokes'];
var quality = rowData['quality'];
var ket_quality = rowData['ket_quality'];
var lokasi = rowData['lokasi'];
var menyebar = rowData['menyebar'];
var skala_nyeri = rowData['skala_nyeri'];
var durasi = rowData['durasi'];
var nyeri_hilang = rowData['nyeri_hilang'];
var ket_nyeri = rowData['ket_nyeri'];
var manajemen_nyeri = rowData['manajemen_nyeri'];
var nip = rowData['nip'];



            $("#typeact").val("edit");
  
            $('#no_rawat').val(no_rawat);
$('#tanggal').val(tanggal);
$('#nyeri').val(nyeri);
$('#provokes').val(provokes);
$('#ket_provokes').val(ket_provokes);
$('#quality').val(quality);
$('#ket_quality').val(ket_quality);
$('#lokasi').val(lokasi);
$('#menyebar').val(menyebar);
$('#skala_nyeri').val(skala_nyeri);
$('#durasi').val(durasi);
$('#nyeri_hilang').val(nyeri_hilang);
$('#ket_nyeri').val(ket_nyeri);
$('#manajemen_nyeri').val(manajemen_nyeri);
$('#nip').val(nip);

            //$("#no_rawat").prop('disabled', true); // GA BISA DIEDIT KALI DISABLE
            $('#modal-title').text("Edit Data Penilaian Ulang Nyeri");
            $("#modal_mlite_penilaian_ulang_nyeri").modal();
        }
        else {
            alert("Silakan pilih data yang akan di edit.");
        }

    });

    // ==============================================================
    // TOMBOL  DELETE DI CLICK
    // ==============================================================
    jQuery("#hapus_data_mlite_penilaian_ulang_nyeri").click(function () {
        var rowData = var_tbl_mlite_penilaian_ulang_nyeri.rows({ selected: true }).data()[0];


        if (rowData) {
var no_rawat = rowData['no_rawat'];
            var a = confirm("Anda yakin akan menghapus data dengan no_rawat=" + no_rawat);
            if (a) {

                $.ajax({
                    url: "{?=url([ADMIN,'penilaian_ulang_nyeri','aksi'])?}",
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
        $('#search_text_mlite_penilaian_ulang_nyeri').val(searchParams.get('no_rawat'));
        var_tbl_mlite_penilaian_ulang_nyeri.draw();
        if(searchParams.get('modal') == 'true') {
            $("#modal_mlite_penilaian_ulang_nyeri").modal();
            $('#no_rawat').val(searchParams.get('no_rawat'));    
        }
    }

    jQuery("#tambah_data_mlite_penilaian_ulang_nyeri").click(function () {

        $('#no_rawat').val('');

        if(window.location.search.indexOf('no_rawat') !== -1) { 
            $('#no_rawat').val(searchParams.get('no_rawat'));
        }
        
$('#tanggal').val('');
$('#nyeri').val('');
$('#provokes').val('');
$('#ket_provokes').val('');
$('#quality').val('');
$('#ket_quality').val('');
$('#lokasi').val('');
$('#menyebar').val('');
$('#skala_nyeri').val('');
$('#durasi').val('');
$('#nyeri_hilang').val('');
$('#ket_nyeri').val('');
$('#manajemen_nyeri').val('');
$('#nip').val('{?=$this->core->getUserInfo('username', null, true)?}');


        $("#typeact").val("add");
        $("#no_rawat").prop('disabled', false);
        
        $('#modal-title').text("Tambah Data Penilaian Ulang Nyeri");
        $("#modal_mlite_penilaian_ulang_nyeri").modal();
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_mlite_penilaian_ulang_nyeri").click(function () {

        var search_field_mlite_penilaian_ulang_nyeri = $('#search_field_mlite_penilaian_ulang_nyeri').val();
        var search_text_mlite_penilaian_ulang_nyeri = $('#search_text_mlite_penilaian_ulang_nyeri').val();

        $.ajax({
            url: "{?=url([ADMIN,'penilaian_ulang_nyeri','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat', 
                search_field_mlite_penilaian_ulang_nyeri: search_field_mlite_penilaian_ulang_nyeri, 
                search_text_mlite_penilaian_ulang_nyeri: search_text_mlite_penilaian_ulang_nyeri
            },
            dataType: 'json',
            success: function (res) {
                var eTable = "<div class='table-responsive'><table id='tbl_lihat_mlite_penilaian_ulang_nyeri' class='table display dataTable' style='width:100%'><thead><th>No Rawat</th><th>Tanggal</th><th>Nyeri</th><th>Provokes</th><th>Ket Provokes</th><th>Quality</th><th>Ket Quality</th><th>Lokasi</th><th>Menyebar</th><th>Skala Nyeri</th><th>Durasi</th><th>Nyeri Hilang</th><th>Ket Nyeri</th><th>Manajemen Nyeri</th><th>Nip</th></thead>";
                for (var i = 0; i < res.length; i++) {
                    eTable += "<tr>";
                    eTable += '<td>' + res[i]['no_rawat'] + '</td>';
eTable += '<td>' + res[i]['tanggal'] + '</td>';
eTable += '<td>' + res[i]['nyeri'] + '</td>';
eTable += '<td>' + res[i]['provokes'] + '</td>';
eTable += '<td>' + res[i]['ket_provokes'] + '</td>';
eTable += '<td>' + res[i]['quality'] + '</td>';
eTable += '<td>' + res[i]['ket_quality'] + '</td>';
eTable += '<td>' + res[i]['lokasi'] + '</td>';
eTable += '<td>' + res[i]['menyebar'] + '</td>';
eTable += '<td>' + res[i]['skala_nyeri'] + '</td>';
eTable += '<td>' + res[i]['durasi'] + '</td>';
eTable += '<td>' + res[i]['nyeri_hilang'] + '</td>';
eTable += '<td>' + res[i]['ket_nyeri'] + '</td>';
eTable += '<td>' + res[i]['manajemen_nyeri'] + '</td>';
eTable += '<td>' + res[i]['nip'] + '</td>';
                    eTable += "</tr>";
                }
                eTable += "</tbody></table></div>";
                $('#forTable_mlite_penilaian_ulang_nyeri').html(eTable);
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_mlite_penilaian_ulang_nyeri").modal();
    });

    // ==============================================================
    // TOMBOL DETAIL mlite_penilaian_ulang_nyeri DI CLICK
    // ==============================================================
    jQuery("#lihat_detail_mlite_penilaian_ulang_nyeri").click(function (event) {

        var rowData = var_tbl_mlite_penilaian_ulang_nyeri.rows({ selected: true }).data()[0];

        if (rowData) {
var no_rawat = rowData['no_rawat'];
            var baseURL = mlite.url + '/' + mlite.admin;
            event.preventDefault();
            var loadURL =  baseURL + '/penilaian_ulang_nyeri/detail/' + no_rawat + '?t=' + mlite.token;
        
            var modal = $('#modal_detail_mlite_penilaian_ulang_nyeri');
            var modalContent = $('#modal_detail_mlite_penilaian_ulang_nyeri .modal-content');
        
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

    jQuery("#lihat_detail_mlite_penilaian_ulang_nyeri2").click(function (event) {

        var rowData = var_tbl_mlite_penilaian_ulang_nyeri.rows({ selected: true }).data()[0];

        if (rowData) {
            var no_rawat = rowData['no_rawat'];
            var baseURL = mlite.url + '/' + mlite.admin;
            event.preventDefault();
            {if: $this->core->ActiveModule('jasper')}
                var loadURL =  baseURL + '/jasper/penilaianulangnyeri/' + no_rawat.replace(/\//g,'') + '?t=' + mlite.token;
                $("#modal_detail_mlite_penilaian_ulang_nyeri").modal('show').html('<div style="text-align:center;margin:20px auto;width:90%;height:95%;"><iframe src="' + loadURL + '" frameborder="no" width="100%" height="100%"></iframe></div>');
            {else}
                bootbox.alert('Cetak PDF tidak bisa dilakukan. Silahkan aktifkan Modul Premium PDF Jasper!');
            {/if}
            
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
        doc.text("Tabel Data Mlite Penilaian Ulang Nyeri", 20, 95, null, null, null);
        const totalPagesExp = "{total_pages_count_string}";        
        doc.autoTable({
            html: '#tbl_lihat_mlite_penilaian_ulang_nyeri',
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
        // doc.save('table_data_mlite_penilaian_ulang_nyeri.pdf')
        window.open(doc.output('bloburl'), '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
              
    })

    // ===========================================
    // Ketika tombol export xlsx di tekan
    // ===========================================
    $("#export_xlsx").click(function () {
        let tbl1 = document.getElementById("tbl_lihat_mlite_penilaian_ulang_nyeri");
        let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
        let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
        let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
        const new_workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data mlite_penilaian_ulang_nyeri");
        XLSX.writeFile(new_workbook, 'tmp_file.xls');
    })
});