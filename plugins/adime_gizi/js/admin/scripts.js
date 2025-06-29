jQuery().ready(function () {
    var var_tbl_catatan_adime_gizi = $('#tbl_catatan_adime_gizi').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'dom': 'Bfrtip',
        'searching': false,
        'select': true,
        'colReorder': true,
        "bInfo" : false,
        "ajax": {
            "url": "{?=url([ADMIN,'adime_gizi','data'])?}",
            "dataType": "json",
            "type": "POST",
            "data": function (data) {

                // Read values
                var search_field_catatan_adime_gizi = $('#search_field_catatan_adime_gizi').val();
                var search_text_catatan_adime_gizi = $('#search_text_catatan_adime_gizi').val();
                
                data.search_field_catatan_adime_gizi = search_field_catatan_adime_gizi;
                data.search_text_catatan_adime_gizi = search_text_catatan_adime_gizi;
                
            }
        },
        "columns": [
{ 'data': 'no_rawat' },
{ 'data': 'tanggal' },
{ 'data': 'asesmen' },
{ 'data': 'diagnosis' },
{ 'data': 'intervensi' },
{ 'data': 'monitoring' },
{ 'data': 'evaluasi' },
{ 'data': 'instruksi' },
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
{ 'targets': 8}

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

    $("form[name='form_catatan_adime_gizi']").validate({
        rules: {
no_rawat: 'required',
tanggal: 'required',
asesmen: 'required',
diagnosis: 'required',
intervensi: 'required',
monitoring: 'required',
evaluasi: 'required',
instruksi: 'required',
nip: 'required'

        },
        messages: {
no_rawat:'no_rawat tidak boleh kosong!',
tanggal:'tanggal tidak boleh kosong!',
asesmen:'asesmen tidak boleh kosong!',
diagnosis:'diagnosis tidak boleh kosong!',
intervensi:'intervensi tidak boleh kosong!',
monitoring:'monitoring tidak boleh kosong!',
evaluasi:'evaluasi tidak boleh kosong!',
instruksi:'instruksi tidak boleh kosong!',
nip:'nip tidak boleh kosong!'

        },
        submitHandler: function (form) {
 var no_rawat= $('#no_rawat').val();
var tanggal= $('#tanggal').val();
var asesmen= $('#asesmen').val();
var diagnosis= $('#diagnosis').val();
var intervensi= $('#intervensi').val();
var monitoring= $('#monitoring').val();
var evaluasi= $('#evaluasi').val();
var instruksi= $('#instruksi').val();
var nip= $('#nip').val();

 var typeact = $('#typeact').val();

 var formData = new FormData(form); // tambahan
 formData.append('typeact', typeact); // tambahan

            $.ajax({
                url: "{?=url([ADMIN,'adime_gizi','aksi'])?}",
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
    $('#search_text_catatan_adime_gizi').keyup(function () {
        var_tbl_catatan_adime_gizi.draw();
    });
    // ==============================================================
    // CLICK TANDA X DI INPUT SEARCH
    // ==============================================================
    $("#searchclear_catatan_adime_gizi").click(function () {
        $("#search_text_catatan_adime_gizi").val("");
        var_tbl_catatan_adime_gizi.draw();
    });

    // ===========================================
    // Ketika tombol Edit di tekan
    // ===========================================

    $("#edit_data_catatan_adime_gizi").click(function () {
        var rowData = var_tbl_catatan_adime_gizi.rows({ selected: true }).data()[0];
        if (rowData != null) {

            var no_rawat = rowData['no_rawat'];
var tanggal = rowData['tanggal'];
var asesmen = rowData['asesmen'];
var diagnosis = rowData['diagnosis'];
var intervensi = rowData['intervensi'];
var monitoring = rowData['monitoring'];
var evaluasi = rowData['evaluasi'];
var instruksi = rowData['instruksi'];
var nip = rowData['nip'];



            $("#typeact").val("edit");
  
            $('#no_rawat').val(no_rawat);
$('#tanggal').val(tanggal);
$('#asesmen').val(asesmen);
$('#diagnosis').val(diagnosis);
$('#intervensi').val(intervensi);
$('#monitoring').val(monitoring);
$('#evaluasi').val(evaluasi);
$('#instruksi').val(instruksi);
$('#nip').val(nip);

            //$("#no_rawat").prop('disabled', true); // GA BISA DIEDIT KALI DISABLE
            $('#modal-title').text("Edit Data Adime Gizi");
            $("#modal_catatan_adime_gizi").modal();
        }
        else {
            alert("Silakan pilih data yang akan di edit.");
        }

    });

    // ==============================================================
    // TOMBOL  DELETE DI CLICK
    // ==============================================================
    jQuery("#hapus_data_catatan_adime_gizi").click(function () {
        var rowData = var_tbl_catatan_adime_gizi.rows({ selected: true }).data()[0];


        if (rowData) {
var no_rawat = rowData['no_rawat'];
            var a = confirm("Anda yakin akan menghapus data dengan no_rawat=" + no_rawat);
            if (a) {

                $.ajax({
                    url: "{?=url([ADMIN,'adime_gizi','aksi'])?}",
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

    
    if(window.location.search.indexOf('no_rawat') !== -1) { 
        let searchParams = new URLSearchParams(window.location.search)
        $('#search_text_catatan_adime_gizi').val(searchParams.get('no_rawat'));
        var_tbl_catatan_adime_gizi.draw();
        if(searchParams.get('modal') == 'true') {
            $("#modal_catatan_adime_gizi").modal();
            $('#no_rawat').val(searchParams.get('no_rawat'));    
        }
    }

    jQuery("#tambah_data_catatan_adime_gizi").click(function () {

        $('#no_rawat').val('');
$('#tanggal').val('');
$('#asesmen').val('');
$('#diagnosis').val('');
$('#intervensi').val('');
$('#monitoring').val('');
$('#evaluasi').val('');
$('#instruksi').val('');
$('#nip').val('');


        if(window.location.search.indexOf('no_rawat') !== -1) { 
            $('#no_rawat').val(searchParams.get('no_rawat'));
        }

        $("#typeact").val("add");
        $("#no_rawat").prop('disabled', false);
        
        $('#modal-title').text("Tambah Data Adime Gizi");
        $("#modal_catatan_adime_gizi").modal();
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_catatan_adime_gizi").click(function () {

        var search_field_catatan_adime_gizi = $('#search_field_catatan_adime_gizi').val();
        var search_text_catatan_adime_gizi = $('#search_text_catatan_adime_gizi').val();

        $.ajax({
            url: "{?=url([ADMIN,'adime_gizi','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat', 
                search_field_catatan_adime_gizi: search_field_catatan_adime_gizi, 
                search_text_catatan_adime_gizi: search_text_catatan_adime_gizi
            },
            dataType: 'json',
            success: function (res) {
                var eTable = "<div class='table-responsive'><table id='tbl_lihat_catatan_adime_gizi' class='table display dataTable' style='width:100%'><thead><th>No Rawat</th><th>Tanggal</th><th>Asesmen</th><th>Diagnosis</th><th>Intervensi</th><th>Monitoring</th><th>Evaluasi</th><th>Instruksi</th><th>Nip</th></thead>";
                for (var i = 0; i < res.length; i++) {
                    eTable += "<tr>";
                    eTable += '<td>' + res[i]['no_rawat'] + '</td>';
eTable += '<td>' + res[i]['tanggal'] + '</td>';
eTable += '<td>' + res[i]['asesmen'] + '</td>';
eTable += '<td>' + res[i]['diagnosis'] + '</td>';
eTable += '<td>' + res[i]['intervensi'] + '</td>';
eTable += '<td>' + res[i]['monitoring'] + '</td>';
eTable += '<td>' + res[i]['evaluasi'] + '</td>';
eTable += '<td>' + res[i]['instruksi'] + '</td>';
eTable += '<td>' + res[i]['nip'] + '</td>';
                    eTable += "</tr>";
                }
                eTable += "</tbody></table></div>";
                $('#forTable_catatan_adime_gizi').html(eTable);
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_catatan_adime_gizi").modal();
    });

    // ==============================================================
    // TOMBOL DETAIL catatan_adime_gizi DI CLICK
    // ==============================================================
    jQuery("#lihat_detail_catatan_adime_gizi").click(function (event) {

        var rowData = var_tbl_catatan_adime_gizi.rows({ selected: true }).data()[0];

        if (rowData) {
var no_rawat = rowData['no_rawat'];
            var baseURL = mlite.url + '/' + mlite.admin;
            event.preventDefault();
            var loadURL =  baseURL + '/adime_gizi/detail/' + no_rawat.replace(/\//g,'') + '?t=' + mlite.token;
        
            var modal = $('#modal_detail_catatan_adime_gizi');
            var modalContent = $('#modal_detail_catatan_adime_gizi .modal-content');
        
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
        doc.text("Tabel Data Catatan Adime Gizi", 20, 95, null, null, null);
        const totalPagesExp = "{total_pages_count_string}";        
        doc.autoTable({
            html: '#tbl_lihat_catatan_adime_gizi',
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
        // doc.save('table_data_catatan_adime_gizi.pdf')
        window.open(doc.output('bloburl'), '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
              
    })

    // ===========================================
    // Ketika tombol export xlsx di tekan
    // ===========================================
    $("#export_xlsx").click(function () {
        let tbl1 = document.getElementById("tbl_lihat_catatan_adime_gizi");
        let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
        let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
        let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
        const new_workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data catatan_adime_gizi");
        XLSX.writeFile(new_workbook, 'tmp_file.xls');
    })

    $('.tanggal').datetimepicker({
        defaultDate: '{?=date('Y-m-d')?}',
        format: 'YYYY-MM-DD',
        locale: 'id'
      });

});