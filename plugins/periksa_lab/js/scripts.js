jQuery().ready(function () {
    var var_tbl_periksa_lab = $('#tbl_periksa_lab').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'dom': 'Bfrtip',
        'searching': false,
        'select': true,
        'colReorder': true,
        "bInfo" : false,
        "ajax": {
            "url": "{?=url(['periksa_lab','data'])?}",
            "dataType": "json",
            "type": "POST",
            "data": function (data) {

                // Read values
                var search_field_periksa_lab = $('#search_field_periksa_lab').val();
                var search_text_periksa_lab = $('#search_text_periksa_lab').val();
                
                data.search_field_periksa_lab = search_field_periksa_lab;
                data.search_text_periksa_lab = search_text_periksa_lab;
                
            }
        },
        "fnDrawCallback": function () {
            $('#more_data_periksa_lab').on('click', function(e) {
                e.preventDefault();
                var clientX = e.originalEvent.clientX;
                var clientY = e.originalEvent.clientY;
                $('#tbl_periksa_lab tr').contextMenu({x: clientX, y: clientY});
            });          
        }, 
        "columns": [
{ 'data': 'no_rawat' },
{ 'data': 'nip' },
{ 'data': 'kd_jenis_prw' },
{ 'data': 'tgl_periksa' },
{ 'data': 'jam' },
{ 'data': 'dokter_perujuk' },
{ 'data': 'bagian_rs' },
{ 'data': 'bhp' },
{ 'data': 'tarif_perujuk' },
{ 'data': 'tarif_tindakan_dokter' },
{ 'data': 'tarif_tindakan_petugas' },
{ 'data': 'kso' },
{ 'data': 'menejemen' },
{ 'data': 'biaya' },
{ 'data': 'kd_dokter' },
{ 'data': 'status' },
{ 'data': 'kategori' }

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
        selector: '#tbl_periksa_lab tr', 
        trigger: 'right',
        callback: function(key, options) {
          var rowData = var_tbl_periksa_lab.rows({ selected: true }).data()[0];
          if (rowData != null) {
var no_rawat = rowData['no_rawat'];
            switch (key) {
                case 'detail' :
                    OpenModal(mlite.url + '/periksa_lab/detail/' + no_rawat + '?t=' + mlite.token);
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
            $('#bagian_rs').val(result.bagian_rs);
            $('#bhp').val(result.bhp);
            $('#tarif_perujuk').val(result.tarif_perujuk);
            $('#tarif_tindakan_dokter').val(result.tarif_tindakan_dokter);
            $('#tarif_tindakan_petugas').val(result.tarif_tindakan_petugas);
            $('#menejemen').val(result.menejemen);
            $('#kso').val(result.kso);
            $('#biaya').val(result.total_byr);
            var result = result.template_laboratorium;
            // console.log(result.bagian_rs);
            var html = '';
            html += '<div class="tables-template-lab-content">';
            if(result.length > 0) {  
                html +='<table class="table table-striped table-hover">'
                        +'<thead>'
                        +'<tr>'
                        +'<th><input class="form-check-input" type="checkbox" id="id_template_all"></th>'
                        // +'<th>Kd Jenis Prw</th>'
                        // +'<th>ID Template</th>'
                        +'<th>Pemeriksaan</th>'
                        +'<th>Satuan</th>'
                        +'<th>Nilai</th>'
                        +'<th>Keterangan</th>'
                        +'</tr>'
                        +'</thead>'
                        +'<tbody>';
                    for(var i=0;i < result.length; i++) {
                        html +='<tr>'
                            +'<td><input class="form-check-input" type="checkbox" name="id_template[]" id="id_template" value="'+result[i].id_template+'"></td>'
                            // +'<td>'+result[i].kd_jenis_prw+'</td>'
                            // +'<td>'+result[i].id_template+'</td>'
                            +'<td>'+result[i].Pemeriksaan+'</td>'
                            +'<td>'+result[i].satuan+'</td>'
                            +'<td><input class="form-control" name="nilai[]"></td>'
                            +'<td><input class="form-control" name="keterangan[]"></td>'
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

    // ==============================================================
    // FORM VALIDASI
    // ==============================================================

    $("form[name='form_periksa_lab']").validate({
        rules: {
no_rawat: 'required',
nip: 'required',
kd_jenis_prw: 'required',
tgl_periksa: 'required',
jam: 'required',
dokter_perujuk: 'required',
bagian_rs: 'required',
bhp: 'required',
tarif_perujuk: 'required',
tarif_tindakan_dokter: 'required',
tarif_tindakan_petugas: 'required',
kso: 'required',
menejemen: 'required',
biaya: 'required',
kd_dokter: 'required',
status: 'required',
kategori: 'required'

        },
        messages: {
no_rawat:'No Rawat tidak boleh kosong!',
nip:'Nip tidak boleh kosong!',
kd_jenis_prw:'Kd Jenis Prw tidak boleh kosong!',
tgl_periksa:'Tgl Periksa tidak boleh kosong!',
jam:'Jam tidak boleh kosong!',
dokter_perujuk:'Dokter Perujuk tidak boleh kosong!',
bagian_rs:'Bagian Rs tidak boleh kosong!',
bhp:'Bhp tidak boleh kosong!',
tarif_perujuk:'Tarif Perujuk tidak boleh kosong!',
tarif_tindakan_dokter:'Tarif Tindakan Dokter tidak boleh kosong!',
tarif_tindakan_petugas:'Tarif Tindakan Petugas tidak boleh kosong!',
kso:'Kso tidak boleh kosong!',
menejemen:'Menejemen tidak boleh kosong!',
biaya:'Biaya tidak boleh kosong!',
kd_dokter:'Kd Dokter tidak boleh kosong!',
status:'Status tidak boleh kosong!',
kategori:'Kategori tidak boleh kosong!'

        },
        submitHandler: function (form) {
var no_rawat= $('#no_rawat').val();
var nip= $('#nip').val();
var kd_jenis_prw= $('#kd_jenis_prw').val();
var tgl_periksa= $('#tgl_periksa').val();
var jam= $('#jam').val();
var dokter_perujuk= $('#dokter_perujuk').val();
var bagian_rs= $('#bagian_rs').val();
var bhp= $('#bhp').val();
var tarif_perujuk= $('#tarif_perujuk').val();
var tarif_tindakan_dokter= $('#tarif_tindakan_dokter').val();
var tarif_tindakan_petugas= $('#tarif_tindakan_petugas').val();
var kso= $('#kso').val();
var menejemen= $('#menejemen').val();
var biaya= $('#biaya').val();
var kd_dokter= $('#kd_dokter').val();
var status= $('#status').val();
var kategori= $('#kategori').val();

var typeact = $('#typeact').val();

var formData = new FormData(form); // tambahan
formData.append('typeact', typeact); // tambahan

            $.ajax({
                url: "{?=url(['periksa_lab','aksi'])?}",
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
                            $("#modal_periksa_lab").modal('hide');
                        } else {
                            bootbox.alert('<span class="text-danger">' + data.msg + '</span>');
                        }    
                    }
                    else if (typeact == "edit") {
                        if(data.status === 'success') {
                            bootbox.alert('<span class="text-success">' + data.msg + '</span>');
                            $("#modal_periksa_lab").modal('hide');
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
                    var_tbl_periksa_lab.draw();
                }
            })
        }
    });

    if(typeof ws != 'undefined' && typeof ws.readyState != 'undefined' && ws.readyState == 1){
        ws.onmessage = function(response){
            try{
                output = JSON.parse(response.data);
                if(output['action'] == 'add'){
                    var_tbl_periksa_lab.draw();
                }
                if(output['action'] == 'edit'){
                    var_tbl_periksa_lab.draw();
                }
                if(output['action'] == 'del'){
                    var_tbl_periksa_lab.draw();
                }
            }catch(e){
                console.log(e);
            }
        }
    }

    // ==============================================================
    // KETIKA TOMBOL SEARCH DITEKAN
    // ==============================================================
    $('#filter_search_periksa_lab').click(function () {
        var_tbl_periksa_lab.draw();
    });

    // ===========================================
    // KETIKA TOMBOL EDIT DITEKAN
    // ===========================================

    $("#edit_data_periksa_lab").click(function () {
        var rowData = var_tbl_periksa_lab.rows({ selected: true }).data()[0];
        if (rowData != null) {

            var no_rawat = rowData['no_rawat'];
var nip = rowData['nip'];
var kd_jenis_prw = rowData['kd_jenis_prw'];
var tgl_periksa = rowData['tgl_periksa'];
var jam = rowData['jam'];
var dokter_perujuk = rowData['dokter_perujuk'];
var bagian_rs = rowData['bagian_rs'];
var bhp = rowData['bhp'];
var tarif_perujuk = rowData['tarif_perujuk'];
var tarif_tindakan_dokter = rowData['tarif_tindakan_dokter'];
var tarif_tindakan_petugas = rowData['tarif_tindakan_petugas'];
var kso = rowData['kso'];
var menejemen = rowData['menejemen'];
var biaya = rowData['biaya'];
var kd_dokter = rowData['kd_dokter'];
var status = rowData['status'];
var kategori = rowData['kategori'];

            $("#typeact").val("edit");
  
            $('#no_rawat').val(no_rawat);
$('#nip').val(nip);
$('#kd_jenis_prw').val(kd_jenis_prw);
$('#tgl_periksa').val(tgl_periksa);
$('#jam').val(jam);
$('#dokter_perujuk').val(dokter_perujuk);
$('#bagian_rs').val(bagian_rs);
$('#bhp').val(bhp);
$('#tarif_perujuk').val(tarif_perujuk);
$('#tarif_tindakan_dokter').val(tarif_tindakan_dokter);
$('#tarif_tindakan_petugas').val(tarif_tindakan_petugas);
$('#kso').val(kso);
$('#menejemen').val(menejemen);
$('#biaya').val(biaya);
$('#kd_dokter').val(kd_dokter);
$('#status').val(status);
$('#kategori').val(kategori);

            $("#no_rawat").prop('readonly', true); // GA BISA DIEDIT KALI READONLY
            $('#modal-title').text("Edit Data Periksa Lab");
            $("#modal_periksa_lab").modal('show');
        }
        else {
            bootbox.alert("Silakan pilih data yang akan di edit.");
        }

    });

    // ==============================================================
    // TOMBOL  DELETE DI CLICK
    // ==============================================================
    jQuery("#hapus_data_periksa_lab").click(function () {
        var rowData = var_tbl_periksa_lab.rows({ selected: true }).data()[0];


        if (rowData) {
var no_rawat = rowData['no_rawat'];
            bootbox.confirm('Anda yakin akan menghapus data dengan no_rawat="' + no_rawat, function(result) {
                if(result) {
                    $.ajax({
                        url: "{?=url(['periksa_lab','aksi'])?}",
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
                            var_tbl_periksa_lab.draw();
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
    jQuery("#tambah_data_periksa_lab").click(function () {

        $('#no_rawat').val('');
$('#nip').val('');
$('#kd_jenis_prw').val('');
// $('#tgl_periksa').val('');
// $('#jam').val('');
$('#dokter_perujuk').val('');
$('#bagian_rs').val('');
$('#bhp').val('');
$('#tarif_perujuk').val('');
$('#tarif_tindakan_dokter').val('');
$('#tarif_tindakan_petugas').val('');
$('#kso').val('');
$('#menejemen').val('');
$('#biaya').val('');
$('#kd_dokter').val('');
$('#status').val('');
$('#kategori').val('');

        $("#typeact").val("add");
        $("#no_rawat").prop('readonly', false);
        
        $('#modal-title').text("Tambah Data Periksa Lab");
        $("#modal_periksa_lab").modal('show');
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_periksa_lab").click(function () {

        var search_field_periksa_lab = $('#search_field_periksa_lab').val();
        var search_text_periksa_lab = $('#search_text_periksa_lab').val();

        $.ajax({
            url: "{?=url(['periksa_lab','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat', 
                search_field_periksa_lab: search_field_periksa_lab, 
                search_text_periksa_lab: search_text_periksa_lab
            },
            dataType: 'json',
            success: function (res) {
                var eTable = "<div class='table-responsive'><table id='tbl_lihat_periksa_lab' class='table display dataTable' style='width:100%'><thead><th>No Rawat</th><th>Nip</th><th>Kd Jenis Prw</th><th>Tgl Periksa</th><th>Jam</th><th>Dokter Perujuk</th><th>Bagian Rs</th><th>Bhp</th><th>Tarif Perujuk</th><th>Tarif Tindakan Dokter</th><th>Tarif Tindakan Petugas</th><th>Kso</th><th>Menejemen</th><th>Biaya</th><th>Kd Dokter</th><th>Status</th><th>Kategori</th></thead>";
                for (var i = 0; i < res.length; i++) {
                    eTable += "<tr>";
                    eTable += '<td>' + res[i]['no_rawat'] + '</td>';
                    eTable += '<td>' + res[i]['nip'] + '</td>';
                    eTable += '<td>' + res[i]['kd_jenis_prw'] + '</td>';
                    eTable += '<td>' + res[i]['tgl_periksa'] + '</td>';
                    eTable += '<td>' + res[i]['jam'] + '</td>';
                    eTable += '<td>' + res[i]['dokter_perujuk'] + '</td>';
                    eTable += '<td>' + res[i]['bagian_rs'] + '</td>';
                    eTable += '<td>' + res[i]['bhp'] + '</td>';
                    eTable += '<td>' + res[i]['tarif_perujuk'] + '</td>';
                    eTable += '<td>' + res[i]['tarif_tindakan_dokter'] + '</td>';
                    eTable += '<td>' + res[i]['tarif_tindakan_petugas'] + '</td>';
                    eTable += '<td>' + res[i]['kso'] + '</td>';
                    eTable += '<td>' + res[i]['menejemen'] + '</td>';
                    eTable += '<td>' + res[i]['biaya'] + '</td>';
                    eTable += '<td>' + res[i]['kd_dokter'] + '</td>';
                    eTable += '<td>' + res[i]['status'] + '</td>';
                    eTable += '<td>' + res[i]['kategori'] + '</td>';
                    eTable += "</tr>";
                }
                eTable += "</tbody></table></div>";
                $('#forTable_periksa_lab').html(eTable);
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_periksa_lab").modal('show');
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
        doc.text("Tabel Data Periksa Lab", 20, 95, null, null, null);
        const totalPagesExp = "{total_pages_count_string}";        
        doc.autoTable({
            html: '#tbl_lihat_periksa_lab',
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
        // doc.save('table_data_periksa_lab.pdf');
        window.open(doc.output('bloburl'), '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
              
    })

    // ===========================================
    // Ketika tombol export xlsx di tekan
    // ===========================================
    $("#export_xlsx").click(function () {
        let tbl1 = document.getElementById("tbl_lihat_periksa_lab");
        let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
        let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
        let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
        const new_workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data periksa_lab");
        XLSX.writeFile(new_workbook, 'tmp_file.xls');
    })

    $("#view_chart").click(function () {
        window.open(mlite.url + '/periksa_lab/chart?t=' + mlite.token, '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
    })   

    $("#cari_no_rawat").click(function (event) {
        // $(".modal-content").modal("show");
        event.preventDefault();
        var loadURL =  mlite.url + '/periksa_lab/manageregperiksa?t=' + mlite.token;;
    
        var modal = $('#myModalFull');
        var modalContent = $('#myModalFull .modal-content');
        // alert(modal);
    
        modal.off('show.bs.modal');
        modal.on('show.bs.modal', function () {
            modalContent.load(loadURL);
        }).modal('show');
        
        return false;

    })

    $(".datepicker").daterangepicker({
        singleDatePicker: true,
        showDropdowns: true,
        locale: {
            format: "YYYY-MM-DD",
        },
    });
    
    $(".timepicker").daterangepicker({
            timePicker : true,
            singleDatePicker:true,
            timePicker24Hour : true,
            timePickerIncrement : 1,
            timePickerSeconds : true,
            startDate: moment().format('HH:mm:ss'),
            locale : {
                format : 'HH:mm:ss'
            }
        }).on('show.daterangepicker', function(ev, picker){
            picker.container.find(".calendar-table").hide()
    });

});