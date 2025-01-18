jQuery().ready(function () {
    var var_tbl_mlite_surat_sakit = $('#tbl_mlite_surat_sakit').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'dom': 'Bfrtip',
        'searching': false,
        'select': true,
        'colReorder': true,
        "bInfo" : false,
        "ajax": {
            "url": "{?=url([ADMIN,'surat_sakit','data'])?}",
            "dataType": "json",
            "type": "POST",
            "data": function (data) {

                // Read values
                var search_field_mlite_surat_sakit = $('#search_field_mlite_surat_sakit').val();
                var search_text_mlite_surat_sakit = $('#search_text_mlite_surat_sakit').val();
                
                data.search_field_mlite_surat_sakit = search_field_mlite_surat_sakit;
                data.search_text_mlite_surat_sakit = search_text_mlite_surat_sakit;
                
            }
        },
        "columns": [
{ 'data': 'id' },
{ 'data': 'nomor_surat' },
{ 'data': 'no_rawat' },
{ 'data': 'no_rkm_medis' },
{ 'data': 'nm_pasien' },
{ 'data': 'tgl_lahir' },
{ 'data': 'umur' },
{ 'data': 'jk' },
{ 'data': 'alamat' },
{ 'data': 'keadaan' },
{ 'data': 'diagnosa' },
{ 'data': 'lama_angka' },
{ 'data': 'lama_huruf' },
{ 'data': 'tanggal_mulai' },
{ 'data': 'tanggal_selesai' },
{ 'data': 'dokter' },
{ 'data': 'petugas' }

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
{ 'targets': 16}

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

    $("form[name='form_mlite_surat_sakit']").validate({
        rules: {
id: 'required',
nomor_surat: 'required',
no_rawat: 'required',
no_rkm_medis: 'required',
nm_pasien: 'required',
tgl_lahir: 'required',
umur: 'required',
jk: 'required',
alamat: 'required',
keadaan: 'required',
diagnosa: 'required',
lama_angka: 'required',
lama_huruf: 'required',
tanggal_mulai: 'required',
tanggal_selesai: 'required',
dokter: 'required',
petugas: 'required'

        },
        messages: {
id:'id tidak boleh kosong!',
nomor_surat:'nomor_surat tidak boleh kosong!',
no_rawat:'no_rawat tidak boleh kosong!',
no_rkm_medis:'no_rkm_medis tidak boleh kosong!',
nm_pasien:'nm_pasien tidak boleh kosong!',
tgl_lahir:'tgl_lahir tidak boleh kosong!',
umur:'umur tidak boleh kosong!',
jk:'jk tidak boleh kosong!',
alamat:'alamat tidak boleh kosong!',
keadaan:'keadaan tidak boleh kosong!',
diagnosa:'diagnosa tidak boleh kosong!',
lama_angka:'lama_angka tidak boleh kosong!',
lama_huruf:'lama_huruf tidak boleh kosong!',
tanggal_mulai:'tanggal_mulai tidak boleh kosong!',
tanggal_selesai:'tanggal_selesai tidak boleh kosong!',
dokter:'dokter tidak boleh kosong!',
petugas:'petugas tidak boleh kosong!'

        },
        submitHandler: function (form) {
 var id= $('#id').val();
var nomor_surat= $('#nomor_surat').val();
var no_rawat= $('#no_rawat').val();
var no_rkm_medis= $('#no_rkm_medis').val();
var nm_pasien= $('#nm_pasien').val();
var tgl_lahir= $('#tgl_lahir').val();
var umur= $('#umur').val();
var jk= $('#jk').val();
var alamat= $('#alamat').val();
var keadaan= $('#keadaan').val();
var diagnosa= $('#diagnosa').val();
var lama_angka= $('#lama_angka').val();
var lama_huruf= $('#lama_huruf').val();
var tanggal_mulai= $('#tanggal_mulai').val();
var tanggal_selesai= $('#tanggal_selesai').val();
var dokter= $('#dokter').val();
var petugas= $('#petugas').val();

 var typeact = $('#typeact').val();

 var formData = new FormData(form); // tambahan
 formData.append('typeact', typeact); // tambahan

            $.ajax({
                url: "{?=url([ADMIN,'surat_sakit','aksi'])?}",
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
    $('#search_text_mlite_surat_sakit').keyup(function () {
        var_tbl_mlite_surat_sakit.draw();
    });
    // ==============================================================
    // CLICK TANDA X DI INPUT SEARCH
    // ==============================================================
    $("#searchclear_mlite_surat_sakit").click(function () {
        $("#search_text_mlite_surat_sakit").val("");
        var_tbl_mlite_surat_sakit.draw();
    });

    // ===========================================
    // Ketika tombol Edit di tekan
    // ===========================================

    $("#edit_data_mlite_surat_sakit").click(function () {
        var rowData = var_tbl_mlite_surat_sakit.rows({ selected: true }).data()[0];
        if (rowData != null) {

            var id = rowData['id'];
var nomor_surat = rowData['nomor_surat'];
var no_rawat = rowData['no_rawat'];
var no_rkm_medis = rowData['no_rkm_medis'];
var nm_pasien = rowData['nm_pasien'];
var tgl_lahir = rowData['tgl_lahir'];
var umur = rowData['umur'];
var jk = rowData['jk'];
var alamat = rowData['alamat'];
var keadaan = rowData['keadaan'];
var diagnosa = rowData['diagnosa'];
var lama_angka = rowData['lama_angka'];
var lama_huruf = rowData['lama_huruf'];
var tanggal_mulai = rowData['tanggal_mulai'];
var tanggal_selesai = rowData['tanggal_selesai'];
var dokter = rowData['dokter'];
var petugas = rowData['petugas'];



            $("#typeact").val("edit");
  
            $('#id').val(id);
$('#nomor_surat').val(nomor_surat);
$('#no_rawat').val(no_rawat);
$('#no_rkm_medis').val(no_rkm_medis);
$('#nm_pasien').val(nm_pasien);
$('#tgl_lahir').val(tgl_lahir);
$('#umur').val(umur);
$('#jk').val(jk);
$('#alamat').val(alamat);
$('#keadaan').val(keadaan);
$('#diagnosa').val(diagnosa);
$('#lama_angka').val(lama_angka);
$('#lama_huruf').val(lama_huruf);
$('#tanggal_mulai').val(tanggal_mulai);
$('#tanggal_selesai').val(tanggal_selesai);
$('#dokter').val(dokter);
$('#petugas').val(petugas);

            //$("#id").prop('disabled', true); // GA BISA DIEDIT KALI DISABLE
            $('#modal-title').text("Edit Data Surat Sakit");
            $("#modal_mlite_surat_sakit").modal();
        }
        else {
            alert("Silakan pilih data yang akan di edit.");
        }

    });

    // ==============================================================
    // TOMBOL  DELETE DI CLICK
    // ==============================================================
    jQuery("#hapus_data_mlite_surat_sakit").click(function () {
        var rowData = var_tbl_mlite_surat_sakit.rows({ selected: true }).data()[0];


        if (rowData) {
var id = rowData['id'];
            var a = confirm("Anda yakin akan menghapus data dengan id=" + id);
            if (a) {

                $.ajax({
                    url: "{?=url([ADMIN,'surat_sakit','aksi'])?}",
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
    jQuery("#tambah_data_mlite_surat_sakit").click(function () {

        $('#id').val('');
$('#nomor_surat').val('');
$('#no_rawat').val('');
$('#no_rkm_medis').val('');
$('#nm_pasien').val('');
$('#tgl_lahir').val('');
$('#umur').val('');
$('#jk').val('');
$('#alamat').val('');
$('#keadaan').val('');
$('#diagnosa').val('');
$('#lama_angka').val('');
$('#lama_huruf').val('');
$('#tanggal_mulai').val('');
$('#tanggal_selesai').val('');
$('#dokter').val('');
$('#petugas').val('');


        $("#typeact").val("add");
        $("#id").prop('disabled', false);
        
        $('#modal-title').text("Tambah Data Surat Sakit");
        $("#modal_mlite_surat_sakit").modal();
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_mlite_surat_sakit").click(function () {

        var search_field_mlite_surat_sakit = $('#search_field_mlite_surat_sakit').val();
        var search_text_mlite_surat_sakit = $('#search_text_mlite_surat_sakit').val();

        $.ajax({
            url: "{?=url([ADMIN,'surat_sakit','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat', 
                search_field_mlite_surat_sakit: search_field_mlite_surat_sakit, 
                search_text_mlite_surat_sakit: search_text_mlite_surat_sakit
            },
            dataType: 'json',
            success: function (res) {
                var eTable = "<div class='table-responsive'><table id='tbl_lihat_mlite_surat_sakit' class='table display dataTable' style='width:100%'><thead><th>Nomor Surat</th><th>No Rawat</th><th>Nm Pasien</th><th>Tgl Lahir</th><th>Umur</th><th>Jk</th><th>Lama</th><th>Tanggal Mulai</th><th>Tanggal Selesai</th><th>Dokter</th></thead>";
                for (var i = 0; i < res.length; i++) {
                    eTable += "<tr>";
eTable += '<td>' + res[i]['nomor_surat'] + '</td>';
eTable += '<td>' + res[i]['no_rawat'] + '</td>';
eTable += '<td>' + res[i]['nm_pasien'] + '</td>';
eTable += '<td>' + res[i]['tgl_lahir'] + '</td>';
eTable += '<td>' + res[i]['umur'] + '</td>';
eTable += '<td>' + res[i]['jk'] + '</td>';
eTable += '<td>' + res[i]['lama_angka'] + '</td>';
eTable += '<td>' + res[i]['tanggal_mulai'] + '</td>';
eTable += '<td>' + res[i]['tanggal_selesai'] + '</td>';
eTable += '<td>' + res[i]['dokter'] + '</td>';
                    eTable += "</tr>";
                }
                eTable += "</tbody></table></div>";
                $('#forTable_mlite_surat_sakit').html(eTable);
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_mlite_surat_sakit").modal();
    });

    // ==============================================================
    // TOMBOL DETAIL mlite_surat_sakit DI CLICK
    // ==============================================================
    jQuery("#lihat_detail_mlite_surat_sakit").click(function (event) {

        var rowData = var_tbl_mlite_surat_sakit.rows({ selected: true }).data()[0];

        if (rowData) {
            var id = rowData['id'];
            var no_rawat = rowData['no_rawat'];
            var no_rawat = no_rawat.replace(/\//g,'');
            var baseURL = mlite.url + '/' + mlite.admin;
            event.preventDefault();
            var loadURL =  baseURL + '/surat_sakit/suratsakit/' + no_rawat + '?t=' + mlite.token;
            window.open(loadURL);        
        
            // var modal = $('#modal_detail_mlite_surat_sakit');
            // var modalContent = $('#modal_detail_mlite_surat_sakit .modal-content');
        
            // modal.off('show.bs.modal');
            // modal.on('show.bs.modal', function () {
            //     modalContent.load(loadURL);
            // }).modal();
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

        var doc = new jsPDF('l', 'pt', 'A4'); /* pilih 'l' atau 'p' */
        var img = "{?=base64_encode(file_get_contents(url($settings['logo'])))?}";
        doc.addImage(img, 'JPEG', 20, 10, 50, 50);
        doc.setFontSize(20);
        doc.text("{$settings.nama_instansi}", 80, 35, null, null, null);
        doc.setFontSize(10);
        doc.text("{$settings.alamat} - {$settings.kota} - {$settings.propinsi}", 80, 46, null, null, null);
        doc.text("Telepon: {$settings.nomor_telepon} - Email: {$settings.email}", 80, 56, null, null, null);
        doc.line(20,70,820,70,null); /* doc.line(20,70,820,70,null); --> Jika landscape */
        doc.line(20,72,820,72,null); /* doc.line(20,72,820,72,null); --> Jika landscape */
        doc.setFontSize(14);
        doc.text("Tabel Data Mlite Surat Sakit", 20, 95, null, null, null);
        const totalPagesExp = "{total_pages_count_string}";        
        doc.autoTable({
            html: '#tbl_lihat_mlite_surat_sakit',
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
        // doc.save('table_data_mlite_surat_sakit.pdf')
        window.open(doc.output('bloburl'), '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
              
    })

    // ===========================================
    // Ketika tombol export xlsx di tekan
    // ===========================================
    $("#export_xlsx").click(function () {
        let tbl1 = document.getElementById("tbl_lihat_mlite_surat_sakit");
        let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
        let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
        let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
        const new_workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data mlite_surat_sakit");
        XLSX.writeFile(new_workbook, 'tmp_file.xls');
    })
});