jQuery().ready(function () {
    var var_tbl_resep_dokter = $('#tbl_resep_dokter').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'dom': 'Bfrtip',
        'searching': false,
        'select': true,
        'colReorder': true,
        "bInfo" : false,
        "ajax": {
            "url": "{?=url(['reg_periksa','dataresepdokter'])?}",
            "dataType": "json",
            "type": "POST",
            "data": function (data) {

                data.no_rawat = $("a.active").attr('data-no_rawat');
                
            }
        },
        "columns": [
{
    'className': 'dt-control',
    'orderable': false,
    'data': null,
    'defaultContent': ''
},            
{ 'data': 'no_resep' },
{ 'data': 'tgl_perawatan' },
{ 'data': 'jam' },
{ 'data': 'no_rawat' },
{ 'data': 'kd_dokter' },
{ 'data': 'tgl_peresepan' },
{ 'data': 'jam_peresepan' },
{ 'data': 'status' },
{ 'data': 'tgl_penyerahan' },
{ 'data': 'jam_penyerahan' }

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
{ 'targets': 10}

        ],
        order: [[1, 'DESC']], 
        rowId: 'no_resep', 
        buttons: [],
        "scrollCollapse": true,
        // "scrollY": '48vh', 
        // "pageLength":'25', 
        "lengthChange": true,
        "scrollX": true,
        dom: "<'row'<'col-sm-12'tr>><<'pmd-datatable-pagination' l i p>>"
    });


    $.contextMenu({
        selector: '#tbl_resep_dokter tr', 
        trigger: 'right',
        callback: function(key, options) {
          var rowData = var_tbl_resep_dokter.rows({ selected: true }).data()[0];
          if (rowData != null) {
            var no_resep = rowData['no_resep'];
            switch (key) {
                case 'detail' :
                    OpenModal(mlite.url + '/resep_dokter/detail/' + no_resep + '?t=' + mlite.token);
                break;
                case 'edit' :
                    OpenModal(mlite.url + '/resep_dokter/edit/' + no_resep + '?t=' + mlite.token);
                break;
                default :
                break
            } 
          } else {
            bootbox.alert("Silakan pilih data atau klik baris data.");            
          }          
        },
        items: {
            "detail": {name: "View Detail Resep", "icon": "edit", disabled:  {$disabled_menu.read}},
            "edit": {name: "Edit Detail Resep", "icon": "edit", disabled:  {$disabled_menu.read}}
        }
    });

    function format(d) {
        // console.log(d);
        var resep=''; //just a variable to construct
        if(d!='') {
            resep +='<table class="table table-striped table-hover">'+
            '<tbody>';
            resep +='<tr><th>Kode Obat</th><th>Nama Obat</th><th>Jumlah</th><th>Aturan Pakai</th></tr>';
            $.each($(d),function(key,value){
                resep+='<tr><td>'+value.kode_brng+'</td><td>'+value.nama_brng+'</td><td>'+value.jml+'</td><td>'+value.aturan_pakai+'</td></tr>';
            })    
            resep +='</tbody></table>';
        }
        // `d` is the original data object for the row
        return resep;
    }

    // State handling for restoring child rows
    var_tbl_resep_dokter.on('requestChild', function (e, row) {
        row.child(format(row.data())).show();
    });
    
    // Add event listener for opening and closing details
    var_tbl_resep_dokter.on('click', 'tbody td.dt-control', function (e) {
        let tr = e.target.closest('tr');
        let row = var_tbl_resep_dokter.row(tr);
    
        if (row.child.isShown()) {
            // This row is already open - close it
            row.child.hide();
        }
        else {
            $.ajax({
                url: "{?=url(['reg_periksa','getresepdokter'])?}",
                method: "POST",
                data: {
                    no_resep: row.data().no_resep
                },
                success: function (data) {
                    resep = JSON.parse(data);
                    row.child(format(resep)).show();
                }
            })
        }
    });    


    var tbl_reg_periksa_databarang = $("#tbl_reg_periksa_databarang").DataTable({
        "responsive": true, 
        "order": [1, "asc"]
    });
    
      // Listen to change event from checkbox to trigger re-sorting
    $('#tbl_reg_periksa_databarang input[type="checkbox"]').on('change', function() {
        // Update data-sort on closest <td>
        $(this).closest('td').attr('data-order', this.checked ? 1 : 0);
        
        // Store row reference so we can reset its data
        var $tr = $(this).closest('tr');
        
        // Force resorting
        tbl_reg_periksa_databarang
          .row($tr)
          .invalidate()
          .responsive()
          .order([ 0, 'desc' ])
          .draw();
    });

    // ==============================================================
    // FORM VALIDASI
    // ==============================================================

    $("form[name='form_resep_dokter']").validate({
        rules: {
no_resep: 'required',
tgl_perawatan: 'required',
jam: 'required',
no_rawat: 'required',
kd_dokter: 'required',
tgl_peresepan: 'required',
jam_peresepan: 'required',
status: 'required',
tgl_penyerahan: 'required',
jam_penyerahan: 'required',
kode_brng: 'required',
jml: 'required',
aturan_pakai: 'required'

        },
        messages: {
no_resep:'No Resep tidak boleh kosong!',
tgl_perawatan:'Tgl Perawatan tidak boleh kosong!',
jam:'Jam tidak boleh kosong!',
no_rawat:'No Rawat tidak boleh kosong!',
kd_dokter:'Kd Dokter tidak boleh kosong!',
tgl_peresepan:'Tgl Peresepan tidak boleh kosong!',
jam_peresepan:'Jam Peresepan tidak boleh kosong!',
status:'Status tidak boleh kosong!',
tgl_penyerahan:'Tgl Penyerahan tidak boleh kosong!',
jam_penyerahan:'Jam Penyerahan tidak boleh kosong!',
kode_brng:'Kode Brng tidak boleh kosong!',
jml:'Jml tidak boleh kosong!',
aturan_pakai:'Aturan Pakai tidak boleh kosong!'

        },
        submitHandler: function (form) {
var no_resep= $('#no_resep').val();
var tgl_perawatan= $('#tgl_perawatan').val();
var jam= $('#jam').val();
var no_rawat= $('#no_rawat').val();
var kd_dokter= $('#kd_dokter').val();
var tgl_peresepan= $('#tgl_peresepan').val();
var jam_peresepan= $('#jam_peresepan').val();
var status= $('#status').val();
var tgl_penyerahan= $('#tgl_penyerahan').val();
var jam_penyerahan= $('#jam_penyerahan').val();

var kode_brng= $('#kode_brng').val();
var jml= $('#jml').val();
var aturan_pakai= $('#aturan_pakai').val();

 var typeact = $('#typeact').val();
//  var dataRepeater = $('.repeater-default').repeaterVal();

//  console.log(dataRepeater);

 var formData = new FormData(form); // tambahan
 formData.append('typeact', typeact); // tambahan
//  formData.append('data', dataRepeater); // tambahan
console.log(JSON.stringify(Object.fromEntries(formData)));

            $.ajax({
                url: "{?=url(['resep_dokter','aksi'])?}",
                method: "POST",
                contentType: false, // tambahan
                processData: false, // tambahan
                data: formData,
                success: function (data) {
                    console.log(data);
                    data = JSON.parse(data);
                    var audio = new Audio('{?=url()?}/assets/sound/' + data.status + '.mp3');
                    audio.play();
                    if (typeact == "add") {
                        if(data.status === 'success') {
                            bootbox.alert('<span class="text-success">' + data.msg + '</span>');
                        } else {
                            bootbox.alert('<span class="text-danger">' + data.msg + '</span>');
                        }    
                    }
                    else if (typeact == "edit") {
                        if(data.status === 'success') {
                            bootbox.alert('<span class="text-success">' + data.msg + '</span>');
                        } else {
                            bootbox.alert('<span class="text-danger">' + data.msg + '</span>');
                        }    
                    }
                    $("#modal_resep_dokter").modal('hide');
                    var_tbl_resep_dokter.draw();
                }
            })
        }
    });

    // ==============================================================
    // KETIKA TOMBOL SEARCH DITEKAN
    // ==============================================================
    $('#filter_search_resep_dokter').click(function () {
        var_tbl_resep_dokter.draw();
    });

    // ===========================================
    // KETIKA TOMBOL EDIT DITEKAN
    // ===========================================

    $("#edit_data_resep_dokter").click(function () {
        var rowData = var_tbl_resep_dokter.rows({ selected: true }).data()[0];
        if (rowData != null) {
            // OpenModal(mlite.url + '/resep_dokter/edit/' + rowData['no_resep'] + '?t=' + mlite.token);

            var no_resep = rowData['no_resep'];
var tgl_perawatan = rowData['tgl_perawatan'];
var jam = rowData['jam'];
var no_rawat = rowData['no_rawat'];
var kd_dokter = rowData['kd_dokter'];
var tgl_peresepan = rowData['tgl_peresepan'];
var jam_peresepan = rowData['jam_peresepan'];
var status = rowData['status'];
var tgl_penyerahan = rowData['tgl_penyerahan'];
var jam_penyerahan = rowData['jam_penyerahan'];



            $("#typeact").val("edit");
  
            $('#no_resep').val(no_resep);
$('#tgl_perawatan').val(tgl_perawatan);
$('#jam').val(jam);
$('#no_rawat').val(no_rawat);
$('#kd_dokter').val(kd_dokter);
$('#tgl_peresepan').val(tgl_peresepan);
$('#jam_peresepan').val(jam_peresepan);
$('#status').val(status);
$('#tgl_penyerahan').val(tgl_penyerahan);
$('#jam_penyerahan').val(jam_penyerahan);

            //$("#no_resep").prop('disabled', true); // GA BISA DIEDIT KALI DISABLE
            $('#modal-title').text("Edit Data Resep Obat");
            $("#modal_resep_dokter_edit").modal('show');
        }
        else {
            bootbox.alert("Silakan pilih data yang akan di edit.");
        }

    });

    // ==============================================================
    // TOMBOL  DELETE DI CLICK
    // ==============================================================
    jQuery("#hapus_data_resep_dokter").click(function () {
        var rowData = var_tbl_resep_dokter.rows({ selected: true }).data()[0];


        if (rowData) {
var no_resep = rowData['no_resep'];
            bootbox.confirm('Anda yakin akan menghapus data dengan no_resep="' + no_resep, function(result) {
                if(result) {
                    $.ajax({
                        url: "{?=url(['resep_dokter','aksi'])?}",
                        method: "POST",
                        data: {
                            no_resep: no_resep,
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
                            var_tbl_resep_dokter.draw();
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
    jQuery("#tambah_data_resep_dokter").click(function () {

        $('#no_resep').val('{$setnoresep}');
$('#tgl_perawatan').val('');
$('#jam').val('');
$('#no_rawat').val('');
// $('#kd_dokter').val('').change();
// $('#tgl_peresepan').val('');
// $('#jam_peresepan').val(''); 
$('#status').val('ralan').change();
$('#tgl_penyerahan').val('');
$('#jam_penyerahan').val('');

$("#refresh_no_resep").on("click", function() {
    var tgl_peresepan = $('#tgl_peresepan').val();
    var no_rawat = $('#no_rawat_resep_dokter').val();
    var tgl_perawatan = no_rawat.replace(/\//g,'-').slice(0,-7);
    $('#tgl_peresepan').val(tgl_perawatan);
    $.ajax({
        url: "{?=url(['reg_periksa','getnoresep'])?}",
        method: "POST",
        data: {
            tgl_peresepan: tgl_peresepan, 
            tgl_perawatan: tgl_perawatan
        },
        success: function (data) {
            $('#no_resep').val(data);
        }
    })
});

$("#status").on("change", function() {
    var tgl_peresepan = $('#tgl_peresepan').val();
    var no_rawat = $('#no_rawat_resep_dokter').val();
    var tgl_perawatan = no_rawat.replace(/\//g,'-').slice(0,-7);
    $('#tgl_peresepan').val(tgl_perawatan);
    $.ajax({
        url: "{?=url(['reg_periksa','getnoresep'])?}",
        method: "POST",
        data: {
            tgl_peresepan: tgl_peresepan, 
            tgl_perawatan: tgl_perawatan
        },
        success: function (data) {
            $('#no_resep').val(data);
        }
    })
});


// $('#kode_brng').val('');
// $('#jml').val('');
// $('#aturan_pakai').val('');

        $("#typeact").val("add");
        $("#no_resep").prop('disabled', false);
        
        $('#modal-title').text("Tambah Data Resep Obat");
        $("#modal_resep_dokter").modal('show');
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_resep_dokter").click(function () {

        var search_field_resep_dokter = $('#search_field_resep_dokter').val();
        var search_text_resep_dokter = $('#search_text_resep_dokter').val();

        $.ajax({
            url: "{?=url(['resep_dokter','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat', 
                search_field_resep_dokter: search_field_resep_dokter, 
                search_text_resep_dokter: search_text_resep_dokter
            },
            dataType: 'json',
            success: function (res) {
                var eTable = "<div class='table-responsive'><table id='tbl_lihat_resep_dokter' class='table display dataTable' style='width:100%'><thead><th>No Resep</th><th>Tgl Perawatan</th><th>Jam</th><th>No Rawat</th><th>Kd Dokter</th><th>Tgl Peresepan</th><th>Jam Peresepan</th><th>Status</th><th>Tgl Penyerahan</th><th>Jam Penyerahan</th></thead>";
                for (var i = 0; i < res.length; i++) {
                    eTable += "<tr>";
                    eTable += '<td>' + res[i]['no_resep'] + '</td>';
eTable += '<td>' + res[i]['tgl_perawatan'] + '</td>';
eTable += '<td>' + res[i]['jam'] + '</td>';
eTable += '<td>' + res[i]['no_rawat'] + '</td>';
eTable += '<td>' + res[i]['kd_dokter'] + '</td>';
eTable += '<td>' + res[i]['tgl_peresepan'] + '</td>';
eTable += '<td>' + res[i]['jam_peresepan'] + '</td>';
eTable += '<td>' + res[i]['status'] + '</td>';
eTable += '<td>' + res[i]['tgl_penyerahan'] + '</td>';
eTable += '<td>' + res[i]['jam_penyerahan'] + '</td>';
                    eTable += "</tr>";
                }
                eTable += "</tbody></table></div>";
                $('#forTable_resep_dokter').html(eTable);
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_resep_dokter").modal('show');
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
        doc.text("Tabel Data Resep Obat", 20, 95, null, null, null);
        const totalPagesExp = "{total_pages_count_string}";        
        doc.autoTable({
            html: '#tbl_lihat_resep_dokter',
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
        // doc.save('table_data_resep_dokter.pdf')
        window.open(doc.output('bloburl'), '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
              
    })

    // ===========================================
    // Ketika tombol export xlsx di tekan
    // ===========================================
    $("#export_xlsx").click(function () {
        let tbl1 = document.getElementById("tbl_lihat_resep_dokter");
        let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
        let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
        let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
        const new_workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data resep_dokter");
        XLSX.writeFile(new_workbook, 'tmp_file.xls');
    })
    
    $(document).on('click', '.btn-add', function(e) {
        e.preventDefault();
        //get first div insde repeatingtildes
        // $('#kode_brng').selectator('destroy');
        $("#repeat-div .outer:first").find('#kode_brng').selectator('destroy');
        var controlForm = $('#repeat-div .outer:first').clone(true);
        $(controlForm).find('button.btn')
            .removeClass('btn-add btn-success').addClass('btn-remove btn-danger')
            .html('<i class="ri-close-line"></i>');//add remove class
        $("#repeat-div").append(controlForm)
        $("#repeat-div .outer:first").find('#kode_brng').selectator();
        $("#repeat-div .outer:last").find('#kode_brng').selectator();
    
    }).on('click', '.btn-remove', function(e) {
        e.preventDefault();
        $(this).parents('.outer').remove();//remove closest class .outer
        return false;
    });

});