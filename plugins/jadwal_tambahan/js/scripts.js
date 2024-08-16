jQuery().ready(function () {
    var var_tbl_jadwal_tambahan = $('#tbl_jadwal_tambahan').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'dom': 'Bfrtip',
        'searching': false,
        'select': true,
        'colReorder': true,
        "bInfo" : false,
        "ajax": {
            "url": "{?=url(['jadwal_tambahan','data'])?}",
            "dataType": "json",
            "type": "POST",
            "data": function (data) {

                // Read values
                var search_field_jadwal_tambahan = $('#search_field_jadwal_tambahan').val();
                var search_text_jadwal_tambahan = $('#search_text_jadwal_tambahan').val();
                
                data.search_field_jadwal_tambahan = search_field_jadwal_tambahan;
                data.search_text_jadwal_tambahan = search_text_jadwal_tambahan;
                
            }
        },
        "fnDrawCallback": function () {
            $('#more_data_jadwal_tambahan').on('click', function(e) {
                e.preventDefault();
                var clientX = e.originalEvent.clientX;
                var clientY = e.originalEvent.clientY;
                $('#tbl_jadwal_tambahan tr').contextMenu({x: clientX, y: clientY});
            });          
        }, 
        "columns": [
{ 'data': 'id' },
{ 'data': 'tahun' },
{ 'data': 'bulan' },
{ 'data': 'h1' },
{ 'data': 'h2' },
{ 'data': 'h3' },
{ 'data': 'h4' },
{ 'data': 'h5' },
{ 'data': 'h6' },
{ 'data': 'h7' },
{ 'data': 'h8' },
{ 'data': 'h9' },
{ 'data': 'h10' },
{ 'data': 'h11' },
{ 'data': 'h12' },
{ 'data': 'h13' },
{ 'data': 'h14' },
{ 'data': 'h15' },
{ 'data': 'h16' },
{ 'data': 'h17' },
{ 'data': 'h18' },
{ 'data': 'h19' },
{ 'data': 'h20' },
{ 'data': 'h21' },
{ 'data': 'h22' },
{ 'data': 'h23' },
{ 'data': 'h24' },
{ 'data': 'h25' },
{ 'data': 'h26' },
{ 'data': 'h27' },
{ 'data': 'h28' },
{ 'data': 'h29' },
{ 'data': 'h30' },
{ 'data': 'h31' }

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
{ 'targets': 33}

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
        selector: '#tbl_jadwal_tambahan tr', 
        trigger: 'right',
        callback: function(key, options) {
          var rowData = var_tbl_jadwal_tambahan.rows({ selected: true }).data()[0];
          if (rowData != null) {
var id = rowData['id'];
            switch (key) {
                case 'detail' :
                    OpenModal(mlite.url + '/jadwal_tambahan/detail/' + id + '?t=' + mlite.token);
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

    $("form[name='form_jadwal_tambahan']").validate({
        rules: {
id: 'required',
tahun: 'required',
bulan: 'required',
h1: 'required',
h2: 'required',
h3: 'required',
h4: 'required',
h5: 'required',
h6: 'required',
h7: 'required',
h8: 'required',
h9: 'required',
h10: 'required',
h11: 'required',
h12: 'required',
h13: 'required',
h14: 'required',
h15: 'required',
h16: 'required',
h17: 'required',
h18: 'required',
h19: 'required',
h20: 'required',
h21: 'required',
h22: 'required',
h23: 'required',
h24: 'required',
h25: 'required',
h26: 'required',
h27: 'required',
h28: 'required',
h29: 'required',
h30: 'required',
h31: 'required'

        },
        messages: {
id:'Id tidak boleh kosong!',
tahun:'Tahun tidak boleh kosong!',
bulan:'Bulan tidak boleh kosong!',
h1:'H1 tidak boleh kosong!',
h2:'H2 tidak boleh kosong!',
h3:'H3 tidak boleh kosong!',
h4:'H4 tidak boleh kosong!',
h5:'H5 tidak boleh kosong!',
h6:'H6 tidak boleh kosong!',
h7:'H7 tidak boleh kosong!',
h8:'H8 tidak boleh kosong!',
h9:'H9 tidak boleh kosong!',
h10:'H10 tidak boleh kosong!',
h11:'H11 tidak boleh kosong!',
h12:'H12 tidak boleh kosong!',
h13:'H13 tidak boleh kosong!',
h14:'H14 tidak boleh kosong!',
h15:'H15 tidak boleh kosong!',
h16:'H16 tidak boleh kosong!',
h17:'H17 tidak boleh kosong!',
h18:'H18 tidak boleh kosong!',
h19:'H19 tidak boleh kosong!',
h20:'H20 tidak boleh kosong!',
h21:'H21 tidak boleh kosong!',
h22:'H22 tidak boleh kosong!',
h23:'H23 tidak boleh kosong!',
h24:'H24 tidak boleh kosong!',
h25:'H25 tidak boleh kosong!',
h26:'H26 tidak boleh kosong!',
h27:'H27 tidak boleh kosong!',
h28:'H28 tidak boleh kosong!',
h29:'H29 tidak boleh kosong!',
h30:'H30 tidak boleh kosong!',
h31:'H31 tidak boleh kosong!'

        },
        submitHandler: function (form) {
var id= $('#id').val();
var tahun= $('#tahun').val();
var bulan= $('#bulan').val();
var h1= $('#h1').val();
var h2= $('#h2').val();
var h3= $('#h3').val();
var h4= $('#h4').val();
var h5= $('#h5').val();
var h6= $('#h6').val();
var h7= $('#h7').val();
var h8= $('#h8').val();
var h9= $('#h9').val();
var h10= $('#h10').val();
var h11= $('#h11').val();
var h12= $('#h12').val();
var h13= $('#h13').val();
var h14= $('#h14').val();
var h15= $('#h15').val();
var h16= $('#h16').val();
var h17= $('#h17').val();
var h18= $('#h18').val();
var h19= $('#h19').val();
var h20= $('#h20').val();
var h21= $('#h21').val();
var h22= $('#h22').val();
var h23= $('#h23').val();
var h24= $('#h24').val();
var h25= $('#h25').val();
var h26= $('#h26').val();
var h27= $('#h27').val();
var h28= $('#h28').val();
var h29= $('#h29').val();
var h30= $('#h30').val();
var h31= $('#h31').val();

var typeact = $('#typeact').val();

var formData = new FormData(form); // tambahan
formData.append('typeact', typeact); // tambahan

            $.ajax({
                url: "{?=url(['jadwal_tambahan','aksi'])?}",
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
                            $("#modal_jadwal_tambahan").modal('hide');
                        } else {
                            bootbox.alert('<span class="text-danger">' + data.msg + '</span>');
                        }    
                    }
                    else if (typeact == "edit") {
                        if(data.status === 'success') {
                            bootbox.alert('<span class="text-success">' + data.msg + '</span>');
                            $("#modal_jadwal_tambahan").modal('hide');
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
                    var_tbl_jadwal_tambahan.draw();
                }
            })
        }
    });

    if(typeof ws != 'undefined' && typeof ws.readyState != 'undefined' && ws.readyState == 1){
        ws.onmessage = function(response){
            try{
                output = JSON.parse(response.data);
                if(output['action'] == 'add'){
                    var_tbl_jadwal_tambahan.draw();
                }
                if(output['action'] == 'edit'){
                    var_tbl_jadwal_tambahan.draw();
                }
                if(output['action'] == 'del'){
                    var_tbl_jadwal_tambahan.draw();
                }
            }catch(e){
                console.log(e);
            }
        }
    }

    // ==============================================================
    // KETIKA TOMBOL SEARCH DITEKAN
    // ==============================================================
    $('#filter_search_jadwal_tambahan').click(function () {
        var_tbl_jadwal_tambahan.draw();
    });

    // ===========================================
    // KETIKA TOMBOL EDIT DITEKAN
    // ===========================================

    $("#edit_data_jadwal_tambahan").click(function () {
        var rowData = var_tbl_jadwal_tambahan.rows({ selected: true }).data()[0];
        if (rowData != null) {

            var id = rowData['id'];
var tahun = rowData['tahun'];
var bulan = rowData['bulan'];
var h1 = rowData['h1'];
var h2 = rowData['h2'];
var h3 = rowData['h3'];
var h4 = rowData['h4'];
var h5 = rowData['h5'];
var h6 = rowData['h6'];
var h7 = rowData['h7'];
var h8 = rowData['h8'];
var h9 = rowData['h9'];
var h10 = rowData['h10'];
var h11 = rowData['h11'];
var h12 = rowData['h12'];
var h13 = rowData['h13'];
var h14 = rowData['h14'];
var h15 = rowData['h15'];
var h16 = rowData['h16'];
var h17 = rowData['h17'];
var h18 = rowData['h18'];
var h19 = rowData['h19'];
var h20 = rowData['h20'];
var h21 = rowData['h21'];
var h22 = rowData['h22'];
var h23 = rowData['h23'];
var h24 = rowData['h24'];
var h25 = rowData['h25'];
var h26 = rowData['h26'];
var h27 = rowData['h27'];
var h28 = rowData['h28'];
var h29 = rowData['h29'];
var h30 = rowData['h30'];
var h31 = rowData['h31'];

            $("#typeact").val("edit");
  
            $('#id').val(id);
$('#tahun').val(tahun);
$('#bulan').val(bulan);
$('#h1').val(h1);
$('#h2').val(h2);
$('#h3').val(h3);
$('#h4').val(h4);
$('#h5').val(h5);
$('#h6').val(h6);
$('#h7').val(h7);
$('#h8').val(h8);
$('#h9').val(h9);
$('#h10').val(h10);
$('#h11').val(h11);
$('#h12').val(h12);
$('#h13').val(h13);
$('#h14').val(h14);
$('#h15').val(h15);
$('#h16').val(h16);
$('#h17').val(h17);
$('#h18').val(h18);
$('#h19').val(h19);
$('#h20').val(h20);
$('#h21').val(h21);
$('#h22').val(h22);
$('#h23').val(h23);
$('#h24').val(h24);
$('#h25').val(h25);
$('#h26').val(h26);
$('#h27').val(h27);
$('#h28').val(h28);
$('#h29').val(h29);
$('#h30').val(h30);
$('#h31').val(h31);

            $("#id").prop('readonly', true); // GA BISA DIEDIT KALI READONLY
            $('#modal-title').text("Edit Data Jadwal Tambahan");
            $("#modal_jadwal_tambahan").modal('show');
        }
        else {
            bootbox.alert("Silakan pilih data yang akan di edit.");
        }

    });

    // ==============================================================
    // TOMBOL  DELETE DI CLICK
    // ==============================================================
    jQuery("#hapus_data_jadwal_tambahan").click(function () {
        var rowData = var_tbl_jadwal_tambahan.rows({ selected: true }).data()[0];


        if (rowData) {
var id = rowData['id'];
            bootbox.confirm('Anda yakin akan menghapus data dengan id="' + id, function(result) {
                if(result) {
                    $.ajax({
                        url: "{?=url(['jadwal_tambahan','aksi'])?}",
                        method: "POST",
                        data: {
                            id: id,
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
                            var_tbl_jadwal_tambahan.draw();
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
    jQuery("#tambah_data_jadwal_tambahan").click(function () {

        $('#id').val('');
$('#tahun').val('');
$('#bulan').val('');
$('#h1').val('');
$('#h2').val('');
$('#h3').val('');
$('#h4').val('');
$('#h5').val('');
$('#h6').val('');
$('#h7').val('');
$('#h8').val('');
$('#h9').val('');
$('#h10').val('');
$('#h11').val('');
$('#h12').val('');
$('#h13').val('');
$('#h14').val('');
$('#h15').val('');
$('#h16').val('');
$('#h17').val('');
$('#h18').val('');
$('#h19').val('');
$('#h20').val('');
$('#h21').val('');
$('#h22').val('');
$('#h23').val('');
$('#h24').val('');
$('#h25').val('');
$('#h26').val('');
$('#h27').val('');
$('#h28').val('');
$('#h29').val('');
$('#h30').val('');
$('#h31').val('');

        $("#typeact").val("add");
        $("#id").prop('readonly', false);
        
        $('#modal-title').text("Tambah Data Jadwal Tambahan");
        $("#modal_jadwal_tambahan").modal('show');
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_jadwal_tambahan").click(function () {

        var search_field_jadwal_tambahan = $('#search_field_jadwal_tambahan').val();
        var search_text_jadwal_tambahan = $('#search_text_jadwal_tambahan').val();

        $.ajax({
            url: "{?=url(['jadwal_tambahan','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat', 
                search_field_jadwal_tambahan: search_field_jadwal_tambahan, 
                search_text_jadwal_tambahan: search_text_jadwal_tambahan
            },
            dataType: 'json',
            success: function (res) {
                var eTable = "<div class='table-responsive'><table id='tbl_lihat_jadwal_tambahan' class='table display dataTable' style='width:100%'><thead><th>Id</th><th>Tahun</th><th>Bulan</th><th>H1</th><th>H2</th><th>H3</th><th>H4</th><th>H5</th><th>H6</th><th>H7</th><th>H8</th><th>H9</th><th>H10</th><th>H11</th><th>H12</th><th>H13</th><th>H14</th><th>H15</th><th>H16</th><th>H17</th><th>H18</th><th>H19</th><th>H20</th><th>H21</th><th>H22</th><th>H23</th><th>H24</th><th>H25</th><th>H26</th><th>H27</th><th>H28</th><th>H29</th><th>H30</th><th>H31</th></thead>";
                for (var i = 0; i < res.length; i++) {
                    eTable += "<tr>";
                    eTable += '<td>' + res[i]['id'] + '</td>';
eTable += '<td>' + res[i]['tahun'] + '</td>';
eTable += '<td>' + res[i]['bulan'] + '</td>';
eTable += '<td>' + res[i]['h1'] + '</td>';
eTable += '<td>' + res[i]['h2'] + '</td>';
eTable += '<td>' + res[i]['h3'] + '</td>';
eTable += '<td>' + res[i]['h4'] + '</td>';
eTable += '<td>' + res[i]['h5'] + '</td>';
eTable += '<td>' + res[i]['h6'] + '</td>';
eTable += '<td>' + res[i]['h7'] + '</td>';
eTable += '<td>' + res[i]['h8'] + '</td>';
eTable += '<td>' + res[i]['h9'] + '</td>';
eTable += '<td>' + res[i]['h10'] + '</td>';
eTable += '<td>' + res[i]['h11'] + '</td>';
eTable += '<td>' + res[i]['h12'] + '</td>';
eTable += '<td>' + res[i]['h13'] + '</td>';
eTable += '<td>' + res[i]['h14'] + '</td>';
eTable += '<td>' + res[i]['h15'] + '</td>';
eTable += '<td>' + res[i]['h16'] + '</td>';
eTable += '<td>' + res[i]['h17'] + '</td>';
eTable += '<td>' + res[i]['h18'] + '</td>';
eTable += '<td>' + res[i]['h19'] + '</td>';
eTable += '<td>' + res[i]['h20'] + '</td>';
eTable += '<td>' + res[i]['h21'] + '</td>';
eTable += '<td>' + res[i]['h22'] + '</td>';
eTable += '<td>' + res[i]['h23'] + '</td>';
eTable += '<td>' + res[i]['h24'] + '</td>';
eTable += '<td>' + res[i]['h25'] + '</td>';
eTable += '<td>' + res[i]['h26'] + '</td>';
eTable += '<td>' + res[i]['h27'] + '</td>';
eTable += '<td>' + res[i]['h28'] + '</td>';
eTable += '<td>' + res[i]['h29'] + '</td>';
eTable += '<td>' + res[i]['h30'] + '</td>';
eTable += '<td>' + res[i]['h31'] + '</td>';
                    eTable += "</tr>";
                }
                eTable += "</tbody></table></div>";
                $('#forTable_jadwal_tambahan').html(eTable);
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_jadwal_tambahan").modal('show');
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
        doc.text("Tabel Data Jadwal Tambahan", 20, 95, null, null, null);
        const totalPagesExp = "{total_pages_count_string}";        
        doc.autoTable({
            html: '#tbl_lihat_jadwal_tambahan',
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
        // doc.save('table_data_jadwal_tambahan.pdf');
        window.open(doc.output('bloburl'), '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
              
    })

    // ===========================================
    // Ketika tombol export xlsx di tekan
    // ===========================================
    $("#export_xlsx").click(function () {
        let tbl1 = document.getElementById("tbl_lihat_jadwal_tambahan");
        let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
        let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
        let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
        const new_workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data jadwal_tambahan");
        XLSX.writeFile(new_workbook, 'tmp_file.xls');
    })

    $("#view_chart").click(function () {
        window.open(mlite.url + '/jadwal_tambahan/chart?t=' + mlite.token, '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
    })   

});