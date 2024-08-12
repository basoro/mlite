jQuery().ready(function () {
    var var_tbl_permintaan_lab = $('#tbl_permintaan_lab').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'dom': 'Bfrtip',
        'searching': false,
        'select': true,
        'colReorder': true,
        "bInfo" : false,
        "ajax": {
            "url": "{?=url(['reg_periksa','datapermintaanlab'])?}",
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
{ 'data': 'noorder' },
{ 'data': 'no_rawat' },
{ 'data': 'tgl_permintaan' },
{ 'data': 'jam_permintaan' },
{ 'data': 'tgl_sampel' },
{ 'data': 'jam_sampel' },
{ 'data': 'tgl_hasil' },
{ 'data': 'jam_hasil' },
{ 'data': 'dokter_perujuk' },
{ 'data': 'status' },
{ 'data': 'informasi_tambahan' },
{ 'data': 'diagnosa_klinis' }

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
        selector: '#tbl_permintaan_lab tr', 
        trigger: 'right',
        callback: function(key, options) {
          var rowData = var_tbl_permintaan_lab.rows({ selected: true }).data()[0];
          if (rowData != null) {
var noorder = rowData['noorder'];
            switch (key) {
                case 'detail' :
                OpenModal(mlite.url + '/permintaan_lab/detail/' + noorder + '?t=' + mlite.token);
                break;
                default :
                break
            } 
          } else {
            bootbox.alert("Silakan pilih data atau klik baris data.");            
          }          
        },
        items: {
            "detail": {name: "View Detail", "icon": "edit", disabled:  {$disabled_menu.read}},
            // "sep1": "---------",
            // "fold1": {
            //     "name": "Sub group", 
            //     "items": {
            //         "fold1-key1": {"name": "Foo bar"},
            //         "fold2": {
            //             "name": "Sub group 2", 
            //             "items": {
            //                 "fold2-key1": {"name": "alpha"},
            //                 "fold2-key2": {"name": "bravo"}
            //             }
            //         }
            //     }
            // }
        }
    });

    function format(d) {
        console.log(d);
        var resep=''; //just a variable to construct
        $.each($(d),function(key,value){
            resep +='<tr><td>'+value.nomor+'</td><td>'+value.kd_jenis_prw+'</td><td>'+value.stts_bayar+'</td></tr>'
            resep +='<tr><td>#</td><td>Nama Pemeriksaan Detail</td><td>Status Bayar</td></tr>'
            $.each($(value.detail_permintaan),function(key2,value2){
                resep+='<tr><td>'+value2.nomor2+'</td><td>'+value2.id_template+'</td><td>'+value2.stts_bayar+'</td></tr>';
                //loop through each product and append it to trs and am hoping that number of price 
                //values in array will be equal to number of products
            })
        })
        // `d` is the original data object for the row
        return '<table class="table table-bordered table-striped table-hover">'+
               '<thead>'+
                  '<th>Nomor</th>'+  
                  '<th>Nama Pemeriksaan</th>'+  
                  '<th>Satus Bayar</th>'+  
               '</thead><tbody>' 
                   + resep +
               '</tbody></table>';
    }

    // State handling for restoring child rows
    var_tbl_permintaan_lab.on('requestChild', function (e, row) {
        row.child(format(row.data())).show();
    });
    
    // Add event listener for opening and closing details
    var_tbl_permintaan_lab.on('click', 'tbody td.dt-control', function (e) {
        let tr = e.target.closest('tr');
        let row = var_tbl_permintaan_lab.row(tr);
    
        if (row.child.isShown()) {
            // This row is already open - close it
            row.child.hide();
        }
        else {
            $.ajax({
                url: "{?=url(['reg_periksa','getpermintaanlab'])?}",
                method: "POST",
                data: {
                    noorder: row.data().noorder
                },
                success: function (data) {
                    resep = JSON.parse(data);
                    row.child(format(resep)).show();
                }
            })
        }
    });   
    
    // ==============================================================
    // FORM VALIDASI
    // ==============================================================

    $("form[name='form_permintaan_lab']").validate({
        rules: {
noorder: 'required',
no_rawat: 'required',
tgl_permintaan: 'required',
jam_permintaan: 'required',
tgl_sampel: 'required',
jam_sampel: 'required',
tgl_hasil: 'required',
jam_hasil: 'required',
dokter_perujuk: 'required',
status: 'required',
informasi_tambahan: 'required',
diagnosa_klinis: 'required'

        },
        messages: {
noorder:'Noorder tidak boleh kosong!',
no_rawat:'No Rawat tidak boleh kosong!',
tgl_permintaan:'Tgl Permintaan tidak boleh kosong!',
jam_permintaan:'Jam Permintaan tidak boleh kosong!',
tgl_sampel:'Tgl Sampel tidak boleh kosong!',
jam_sampel:'Jam Sampel tidak boleh kosong!',
tgl_hasil:'Tgl Hasil tidak boleh kosong!',
jam_hasil:'Jam Hasil tidak boleh kosong!',
dokter_perujuk:'Dokter Perujuk tidak boleh kosong!',
status:'Status tidak boleh kosong!',
informasi_tambahan:'Informasi Tambahan tidak boleh kosong!',
diagnosa_klinis:'Diagnosa Klinis tidak boleh kosong!'

        },
        submitHandler: function (form) {
 var noorder= $('#noorder').val();
var no_rawat= $('#no_rawat').val();
var tgl_permintaan= $('#tgl_permintaan').val();
var jam_permintaan= $('#jam_permintaan').val();
var tgl_sampel= $('#tgl_sampel').val();
var jam_sampel= $('#jam_sampel').val();
var tgl_hasil= $('#tgl_hasil').val();
var jam_hasil= $('#jam_hasil').val();
var dokter_perujuk= $('#dokter_perujuk').val();
var status= $('#status').val();
var informasi_tambahan= $('#informasi_tambahan').val();
var diagnosa_klinis= $('#diagnosa_klinis').val();

 var typeact = $('#typeact').val();

 var formData = new FormData(form); // tambahan
 formData.append('typeact', typeact); // tambahan

            $.ajax({
                url: "{?=url(['permintaan_lab','aksi'])?}",
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
                    $("#modal_permintaan_lab").modal('hide');
                    var_tbl_permintaan_lab.draw();
                }
            })
        }
    });

    // ==============================================================
    // KETIKA TOMBOL SEARCH DITEKAN
    // ==============================================================
    $('#filter_search_permintaan_lab').click(function () {
        var_tbl_permintaan_lab.draw();
    });

    // ===========================================
    // KETIKA TOMBOL EDIT DITEKAN
    // ===========================================

    $("#edit_data_permintaan_lab").click(function () {
        var rowData = var_tbl_permintaan_lab.rows({ selected: true }).data()[0];
        if (rowData != null) {

            var noorder = rowData['noorder'];
var no_rawat = rowData['no_rawat'];
var tgl_permintaan = rowData['tgl_permintaan'];
var jam_permintaan = rowData['jam_permintaan'];
var tgl_sampel = rowData['tgl_sampel'];
var jam_sampel = rowData['jam_sampel'];
var tgl_hasil = rowData['tgl_hasil'];
var jam_hasil = rowData['jam_hasil'];
var dokter_perujuk = rowData['dokter_perujuk'];
var status = rowData['status'];
var informasi_tambahan = rowData['informasi_tambahan'];
var diagnosa_klinis = rowData['diagnosa_klinis'];



            $("#typeact").val("edit");
  
            $('#noorder').val(noorder);
$('#no_rawat').val(no_rawat);
$('#tgl_permintaan').val(tgl_permintaan);
$('#jam_permintaan').val(jam_permintaan);
$('#tgl_sampel').val(tgl_sampel);
$('#jam_sampel').val(jam_sampel);
$('#tgl_hasil').val(tgl_hasil);
$('#jam_hasil').val(jam_hasil);
$('#dokter_perujuk').val(dokter_perujuk);
$('#status').val(status);
$('#informasi_tambahan').val(informasi_tambahan);
$('#diagnosa_klinis').val(diagnosa_klinis);

            //$("#noorder").prop('disabled', true); // GA BISA DIEDIT KALI DISABLE
            $('#modal-title').text("Edit Data Permintaan Lab");
            $("#modal_permintaan_lab").modal('show');
        }
        else {
            bootbox.alert("Silakan pilih data yang akan di edit.");
        }

    });

    // ==============================================================
    // TOMBOL  DELETE DI CLICK
    // ==============================================================
    jQuery("#hapus_data_permintaan_lab").click(function () {
        var rowData = var_tbl_permintaan_lab.rows({ selected: true }).data()[0];


        if (rowData) {
var noorder = rowData['noorder'];
            bootbox.confirm('Anda yakin akan menghapus data dengan noorder="' + noorder, function(result) {
                if(result) {
                    $.ajax({
                        url: "{?=url(['permintaan_lab','aksi'])?}",
                        method: "POST",
                        data: {
                            noorder: noorder,
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
                            var_tbl_permintaan_lab.draw();
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
    jQuery("#tambah_data_permintaan_lab").click(function () {

        $('#modal_permintaan_lab #noorder').val('{$setnoorder}');
$('#no_rawat').val('');
// $('#tgl_permintaan').val('');
// $('#jam_permintaan').val('');
$('#tgl_sampel').val('');
$('#jam_sampel').val('');
$('#tgl_hasil').val('');
$('#jam_hasil').val('');
// $('#dokter_perujuk').val('');
$('#status').val('');
$('#informasi_tambahan').val('');
$('#diagnosa_klinis').val('');


$('#kd_jenis_prw_lab').on('change', function() {
    var kd_jnis_prw = $('#kd_jenis_prw_lab option:selected').val();
    var api = mlite.url + '/reg_periksa/jnsperawatanlab/' + kd_jnis_prw + '?t=' + mlite.token;
    $.ajax({
      url:api,
      method:'GET',
      cache:false,
      type:"text/json"
   })
   .always(function(){
      $('#loading').html('Load Data Template Perawtan Lab...');
   })
   .done(function(evt) {
      // Set timeout for lazy loading
      setTimeout(function(){
         var result = JSON.parse(evt);
         var html = '';
         html += '<div class="tables-template-lab-content">';
         if(result.length > 0) {  
            html +='<table class="table table-striped table-hover">'
                    +'<thead>'
                    +'<tr>'
                    +'<th><input class="form-check-input" type="checkbox" id="id_template_all"></th>'
                    +'<th>Kd Jenis Prw</th>'
                    +'<th>ID Template</th>'
                    +'<th>Pemeriksaan</th>'
                    +'<th>Satuan</th>'
                    +'<th>Nilai Rujukan LD</th>'
                    +'<th>Nilai Rujukan LA</th>'
                    +'<th>Nilai Rujukan PD</th>'
                    +'<th>Nilai Rujukan PA</th>'
                    +'</tr>'
                    +'</thead>'
                    +'<tbody>';
                for(var i=0;i < result.length; i++) {
                    html +='<tr>'
                        +'<td><input class="form-check-input" type="checkbox" name="id_template[]" id="id_template" value="'+result[i].id_template+'"></td>'
                        +'<td>'+result[i].kd_jenis_prw+'</td>'
                        +'<td>'+result[i].id_template+'</td>'
                        +'<td>'+result[i].Pemeriksaan+'</td>'
                        +'<td>'+result[i].satuan+'</td>'
                        +'<td>'+result[i].nilai_rujukan_ld+'</td>'
                        +'<td>'+result[i].nilai_rujukan_la+'</td>'
                        +'<td>'+result[i].nilai_rujukan_pd+'</td>'
                        +'<td>'+result[i].nilai_rujukan_pa+'</td>'
                        +'</tr>';
                }
            html +='</tbody></table>';
         } else {
            html += '<div>Tidak ada data template perawatan lab...</div>';
         }

         html +='</div>';

         // Set all content
         $('.tables-template-lab').html(html);

        $('#id_template_all').click(function() {
            if(this.checked) {
                $('table').find('input:checkbox').prop('checked',true); 
            }
            else {
                $('table').find('input:checkbox').prop('checked',false); 
            }
        });
        
        $('input:checkbox:not(#id_template_all)').click(function() {
            if(!this.checked) {
                $('#id_template_all').prop('checked',false); 
            }
            else {
                var numChecked = $('input:checkbox:checked:not(#id_template_all)').length;
                var numTotal = $('input:checkbox:not(#id_template_all)').length;
                if(numTotal == numChecked) {
                    $('input[type=checkbox]').prop('checked',true); 
                }
            }
        });        

      },1000); 
   })
   .fail(function() {
      alert('Error : Failed to reach API Url or check your connection');
   })
   .then(function(evt){
      setTimeout(function(){        
         $('#loading').hide();          
      },1000);
   });

});


        $("#typeact").val("add");
        $("#noorder").prop('disabled', false);
        
        $('#modal-title').text("Tambah Data Permintaan Lab");
        $("#modal_permintaan_lab").modal('show');
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_permintaan_lab").click(function () {

        var search_field_permintaan_lab = $('#search_field_permintaan_lab').val();
        var search_text_permintaan_lab = $('#search_text_permintaan_lab').val();

        $.ajax({
            url: "{?=url(['permintaan_lab','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat', 
                search_field_permintaan_lab: search_field_permintaan_lab, 
                search_text_permintaan_lab: search_text_permintaan_lab
            },
            dataType: 'json',
            success: function (res) {
                var eTable = "<div class='table-responsive'><table id='tbl_lihat_permintaan_lab' class='table display dataTable' style='width:100%'><thead><th>Noorder</th><th>No Rawat</th><th>Tgl Permintaan</th><th>Jam Permintaan</th><th>Tgl Sampel</th><th>Jam Sampel</th><th>Tgl Hasil</th><th>Jam Hasil</th><th>Dokter Perujuk</th><th>Status</th><th>Informasi Tambahan</th><th>Diagnosa Klinis</th></thead>";
                for (var i = 0; i < res.length; i++) {
                    eTable += "<tr>";
                    eTable += '<td>' + res[i]['noorder'] + '</td>';
eTable += '<td>' + res[i]['no_rawat'] + '</td>';
eTable += '<td>' + res[i]['tgl_permintaan'] + '</td>';
eTable += '<td>' + res[i]['jam_permintaan'] + '</td>';
eTable += '<td>' + res[i]['tgl_sampel'] + '</td>';
eTable += '<td>' + res[i]['jam_sampel'] + '</td>';
eTable += '<td>' + res[i]['tgl_hasil'] + '</td>';
eTable += '<td>' + res[i]['jam_hasil'] + '</td>';
eTable += '<td>' + res[i]['dokter_perujuk'] + '</td>';
eTable += '<td>' + res[i]['status'] + '</td>';
eTable += '<td>' + res[i]['informasi_tambahan'] + '</td>';
eTable += '<td>' + res[i]['diagnosa_klinis'] + '</td>';
                    eTable += "</tr>";
                }
                eTable += "</tbody></table></div>";
                $('#forTable_permintaan_lab').html(eTable);
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_permintaan_lab").modal('show');
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
        doc.text("Tabel Data Permintaan Lab", 20, 95, null, null, null);
        const totalPagesExp = "{total_pages_count_string}";        
        doc.autoTable({
            html: '#tbl_lihat_permintaan_lab',
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
        // doc.save('table_data_permintaan_lab.pdf')
        window.open(doc.output('bloburl'), '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
              
    })

    // ===========================================
    // Ketika tombol export xlsx di tekan
    // ===========================================
    $("#export_xlsx").click(function () {
        let tbl1 = document.getElementById("tbl_lihat_permintaan_lab");
        let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
        let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
        let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
        const new_workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data permintaan_lab");
        XLSX.writeFile(new_workbook, 'tmp_file.xls');
    })
});