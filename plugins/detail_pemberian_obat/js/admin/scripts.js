jQuery().ready(function () {
    var var_tbl_detail_pemberian_obat = $('#tbl_detail_pemberian_obat').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'dom': 'Bfrtip',
        'searching': false,
        'select': true,
        'colReorder': true,
        "bInfo" : false,
        "ajax": {
            "url": "{?=url([ADMIN,'detail_pemberian_obat','data'])?}",
            "dataType": "json",
            "type": "POST",
            "data": function (data) {

                // Read values
                var search_field_detail_pemberian_obat = $('#search_field_detail_pemberian_obat').val();
                var search_text_detail_pemberian_obat = $('#search_text_detail_pemberian_obat').val();
                
                var from_date = $('#tanggal_awal').val();
                var to_date = $('#tanggal_akhir').val();

                data.search_field_detail_pemberian_obat = search_field_detail_pemberian_obat;
                data.search_text_detail_pemberian_obat = search_text_detail_pemberian_obat;

                data.searchByFromdate = from_date;
                data.searchByTodate = to_date;
                
            }
        },
        "fnDrawCallback": function () {
            $('.selectator').selectator();
            $(".datepicker").datetimepicker({
              format: 'YYYY-MM-DD',
              locale: 'id'
            });
          },          
        "columns": [
{ 'data': 'tgl_perawatan' },
{ 'data': 'jam' },
{ 'data': 'no_rawat' },
{ 'data': 'nm_pasien' },
{ 'data': 'kode_brng' },
{ 'data': 'h_beli' },
{ 'data': 'biaya_obat' },
{ 'data': 'jml' },
{ 'data': 'embalase' },
{ 'data': 'tuslah' },
{ 'data': 'total' },
{ 'data': 'status' },
{ 'data': 'kd_bangsal' },
{ 'data': 'no_batch' },
{ 'data': 'no_faktur' }

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

    $("form[name='form_detail_pemberian_obat']").validate({
        rules: {
tgl_perawatan: 'required',
jam: 'required',
no_rawat: 'required',
kode_brng: 'required',
h_beli: 'required',
biaya_obat: 'required',
jml: 'required',
embalase: 'required',
tuslah: 'required',
total: 'required',
status: 'required',
kd_bangsal: 'required',
no_batch: 'required',
no_faktur: 'required'

        },
        messages: {
tgl_perawatan:'tgl_perawatan tidak boleh kosong!',
jam:'jam tidak boleh kosong!',
no_rawat:'no_rawat tidak boleh kosong!',
kode_brng:'kode_brng tidak boleh kosong!',
h_beli:'h_beli tidak boleh kosong!',
biaya_obat:'biaya_obat tidak boleh kosong!',
jml:'jml tidak boleh kosong!',
embalase:'embalase tidak boleh kosong!',
tuslah:'tuslah tidak boleh kosong!',
total:'total tidak boleh kosong!',
status:'status tidak boleh kosong!',
kd_bangsal:'kd_bangsal tidak boleh kosong!',
no_batch:'no_batch tidak boleh kosong!',
no_faktur:'no_faktur tidak boleh kosong!'

        },
        submitHandler: function (form) {
 var tgl_perawatan= $('#tgl_perawatan').val();
var jam= $('#jam').val();
var no_rawat= $('#no_rawat').val();
var kode_brng= $('#kode_brng').val();
var h_beli= $('#h_beli').val();
var biaya_obat= $('#biaya_obat').val();
var jml= $('#jml').val();
var embalase= $('#embalase').val();
var tuslah= $('#tuslah').val();
var total= $('#total').val();
var status= $('#status').val();
var kd_bangsal= $('#kd_bangsal').val();
var no_batch= $('#no_batch').val();
var no_faktur= $('#no_faktur').val();

 var typeact = $('#typeact').val();

 var formData = new FormData(form); // tambahan
 formData.append('typeact', typeact); // tambahan

            $.ajax({
                url: "{?=url([ADMIN,'detail_pemberian_obat','aksi'])?}",
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
    $('#search_text_detail_pemberian_obat').keyup(function () {
        var_tbl_detail_pemberian_obat.draw();
    });

    $('#tanggal_filter').click(function () {
        var_tbl_detail_pemberian_obat.draw();
    });

    $("#search_field_detail_pemberian_obat").on('change', function() {
        if ($(this).val() == 'kode_brng'){
          $('#tempat_pilih').empty();
          $('#search_text_detail_pemberian_obat').remove();
          $('#tempat_pilih').append('<select class="form-control" name="search_text_detail_pemberian_obat" id="search_text_detail_pemberian_obat" style="text-align:left !important;">' +
            '<option value=""></option>' +
            {loop: $databarang}
            '<option value="{$value.kode_brng}">{$value.nama_brng}</option>' +
            {/loop}
            '</select>');
          $('#search_text_detail_pemberian_obat').selectator();
          $("#search_text_detail_pemberian_obat").on('change', function() {
            var_tbl_detail_pemberian_obat.draw();
          });  
        } else if ($(this).val() == 'kd_bangsal'){
            $('#tempat_pilih').empty();
            $('#search_text_detail_pemberian_obat').remove();
            $('#tempat_pilih').append('<select class="form-control" name="search_text_detail_pemberian_obat" id="search_text_detail_pemberian_obat" style="text-align:left !important;">' +
              '<option value=""></option>' +
              {loop: $bangsal}
              '<option value="{$value.kd_bangsal}">{$value.nm_bangsal}</option>' +
              {/loop}
              '</select>');
            $('#search_text_detail_pemberian_obat').selectator();
            $("#search_text_detail_pemberian_obat").on('change', function() {
              var_tbl_detail_pemberian_obat.draw();
            });    
        } else {
          $('#tempat_pilih').empty();
          $('#search_text_detail_pemberian_obat').remove();
          $('#tempat_pilih').append('<input class="form-control" name="search_text_detail_pemberian_obat" id="search_text_detail_pemberian_obat" type="search" placeholder="Masukkan Kata Kunci Pencarian" />');
          $('#search_text_detail_pemberian_obat').keyup(function () {
            var_tbl_detail_pemberian_obat.draw();
          });
        }
    });  

    // ==============================================================
    // CLICK TANDA X DI INPUT SEARCH
    // ==============================================================
    $("#searchclear_detail_pemberian_obat").click(function () {
        $("#search_text_detail_pemberian_obat").val("");
        $("#tanggal_awal").val("");
        $("#tanggal_akhir").val("");
        var_tbl_detail_pemberian_obat.draw();
    });

    // ===========================================
    // Ketika tombol Edit di tekan
    // ===========================================

    $("#edit_data_detail_pemberian_obat").click(function () {
        var rowData = var_tbl_detail_pemberian_obat.rows({ selected: true }).data()[0];
        if (rowData != null) {

            var tgl_perawatan = rowData['tgl_perawatan'];
var jam = rowData['jam'];
var no_rawat = rowData['no_rawat'];
var kode_brng = rowData['kode_brng'];
var h_beli = rowData['h_beli'];
var biaya_obat = rowData['biaya_obat'];
var jml = rowData['jml'];
var embalase = rowData['embalase'];
var tuslah = rowData['tuslah'];
var total = rowData['total'];
var status = rowData['status'];
var kd_bangsal = rowData['kd_bangsal'];
var no_batch = rowData['no_batch'];
var no_faktur = rowData['no_faktur'];



            $("#typeact").val("edit");
  
            $('#tgl_perawatan').val(tgl_perawatan);
$('#jam').val(jam);
$('#no_rawat').val(no_rawat);
$('#kode_brng').val(kode_brng);
$('#h_beli').val(h_beli);
$('#biaya_obat').val(biaya_obat);
$('#jml').val(jml);
$('#embalase').val(embalase);
$('#tuslah').val(tuslah);
$('#total').val(total);
$('#status').val(status);
$('#kd_bangsal').val(kd_bangsal);
$('#no_batch').val(no_batch);
$('#no_faktur').val(no_faktur);

            //$("#tgl_perawatan").prop('disabled', true); // GA BISA DIEDIT KALI DISABLE
            $('#modal-title').text("Edit Data Detail Pemberian Obat");
            $("#modal_detail_pemberian_obat").modal();
        }
        else {
            alert("Silakan pilih data yang akan di edit.");
        }
        //var no_pengajuan = rowData["no_pengajuan"];

    });

    // ==============================================================
    // TOMBOL  DELETE DI CLICK
    // ==============================================================
    jQuery("#hapus_data_detail_pemberian_obat").click(function () {
        var rowData = var_tbl_detail_pemberian_obat.rows({ selected: true }).data()[0];


        if (rowData) {
var tgl_perawatan = rowData['tgl_perawatan'];
            var a = confirm("Anda yakin akan menghapus data dengan tgl_perawatan=" + tgl_perawatan);
            if (a) {

                $.ajax({
                    url: "{?=url([ADMIN,'detail_pemberian_obat','aksi'])?}",
                    method: "POST",
                    data: {
                        tgl_perawatan: tgl_perawatan,
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
    jQuery("#tambah_data_detail_pemberian_obat").click(function () {

        $('#tgl_perawatan').val('');
$('#jam').val('');
$('#no_rawat').val('');
$('#kode_brng').val('');
$('#h_beli').val('');
$('#biaya_obat').val('');
$('#jml').val('');
$('#embalase').val('');
$('#tuslah').val('');
$('#total').val('');
$('#status').val('');
$('#kd_bangsal').val('');
$('#no_batch').val('');
$('#no_faktur').val('');


        $("#typeact").val("add");
        $("#tgl_perawatan").prop('disabled', false);
        
        $('#modal-title').text("Tambah Data Detail Pemberian Obat");
        $("#modal_detail_pemberian_obat").modal();
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_detail_pemberian_obat").click(function () {

        var search_field_detail_pemberian_obat = $('#search_field_detail_pemberian_obat').val();
        var search_text_detail_pemberian_obat = $('#search_text_detail_pemberian_obat').val();
        var from_date = $('#tanggal_awal').val();
        var to_date = $('#tanggal_akhir').val();

        $.ajax({
            url: "{?=url([ADMIN,'detail_pemberian_obat','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat', 
                search_field: search_field_detail_pemberian_obat, 
                search_value: search_text_detail_pemberian_obat, 
                searchByFromdate: from_date, 
                searchByTodate: to_date
            },
            dataType: 'json',
            success: function (res) {
                console.log(res);
                var eTable = "<div class='table-responsive'><table id='tbl_detail_pemberian_obat' class='table display dataTable' style='width:100%'><thead><th>Tgl Perawatan</th><th>Jam</th><th>No Rawat</th><th>Nama Pasien</th><th>Kode Brng</th><th>H Beli</th><th>Biaya Obat</th><th>Jml</th><th>Embalase</th><th>Tuslah</th><th>Total</th><th>Status</th><th>Kd Bangsal</th><th>No Batch</th><th>No Faktur</th></thead>";
                for (var i = 0; i < res.length; i++) {
                    eTable += "<tr>";
                    eTable += '<td>' + res[i]['tgl_perawatan'] + '</td>';
eTable += '<td>' + res[i]['jam'] + '</td>';
eTable += '<td>' + res[i]['no_rawat'] + '</td>';
eTable += '<td>' + res[i]['nm_pasien'] + '</td>';
eTable += '<td>' + res[i]['kode_brng'] + '</td>';
eTable += '<td>' + res[i]['h_beli'] + '</td>';
eTable += '<td>' + res[i]['biaya_obat'] + '</td>';
eTable += '<td>' + res[i]['jml'] + '</td>';
eTable += '<td>' + res[i]['embalase'] + '</td>';
eTable += '<td>' + res[i]['tuslah'] + '</td>';
eTable += '<td>' + res[i]['total'] + '</td>';
eTable += '<td>' + res[i]['status'] + '</td>';
eTable += '<td>' + res[i]['kd_bangsal'] + '</td>';
eTable += '<td>' + res[i]['no_batch'] + '</td>';
eTable += '<td>' + res[i]['no_faktur'] + '</td>';
                    eTable += "</tr>";
                }
                eTable += "</tbody></table></div>";
                $('#forTable_detail_pemberian_obat').html(eTable);
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_detail_pemberian_obat").modal();
    });

    // ==============================================================
    // TOMBOL DETAIL detail_pemberian_obat DI CLICK
    // ==============================================================
    $("#lihat_detail_detail_pemberian_obat").click(function (event) {

        var rowData = var_tbl_detail_pemberian_obat.rows({ selected: true }).data()[0];

        if (rowData) {
            var tgl_perawatan = rowData['tgl_perawatan'];
            var baseURL = mlite.url + '/' + mlite.admin;
            event.preventDefault();
            var loadURL =  baseURL + '/detail_pemberian_obat/detail/' + tgl_perawatan + '?t=' + mlite.token;
        
            var modal = $('#modal_detail_detail_pemberian_obat');
            var modalContent = $('#modal_detail_detail_pemberian_obat .modal-content');
        
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
        var doc = new jsPDF('l', 'pt', 'A4'); /* pilih 'l' atau 'p' */
        var img = "{?=base64_encode(file_get_contents(url($settings['logo'])))?}";
        doc.addImage(img, 'JPEG', 20, 10, 50, 50);
        doc.setFontSize(20);
        doc.text("{$settings.nama_instansi}", 80, 35, null, null, null);
        doc.setFontSize(10);
        doc.text("{$settings.alamat} - {$settings.kota} - {$settings.propinsi}", 80, 46, null, null, null);
        doc.text("Telepon: {$settings.nomor_telepon} - Email: {$settings.email}", 80, 56, null, null, null);
        doc.line(20,70,820,70,null); /* doc.line(20,70,820,70,null); */
        doc.line(20,72,820,72,null); /* doc.line(20,72,820,72,null); */
        doc.setFontSize(14);
        doc.text("Tabel Data Detail Pemberian Obat", 20, 95, null, null, null);
        const totalPagesExp = "{total_pages_count_string}";
        doc.autoTable({
            html: '#tbl_detail_pemberian_obat',
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
        // doc.save('table_data_detail_pemberian_obat.pdf')
        window.open(doc.output('bloburl'), '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");
    })

    // ===========================================
    // Ketika tombol export xlsx di tekan
    // ===========================================
    $("#export_xlsx").click(function () {
        let tbl1 = document.getElementById("tbl_detail_pemberian_obat");
        let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
        let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
        let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
        const new_workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data detail_pemberian_obat");
        XLSX.writeFile(new_workbook, 'tmp_file.xls');
    })

});