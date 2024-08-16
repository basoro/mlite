jQuery().ready(function () {
    var var_tbl_operasi = $('#tbl_operasi').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'dom': 'Bfrtip',
        'searching': false,
        'select': true,
        'colReorder': true,
        "bInfo" : false,
        "ajax": {
            "url": "{?=url(['operasi','data'])?}",
            "dataType": "json",
            "type": "POST",
            "data": function (data) {

                // Read values
                var search_field_operasi = $('#search_field_operasi').val();
                var search_text_operasi = $('#search_text_operasi').val();
                
                data.search_field_operasi = search_field_operasi;
                data.search_text_operasi = search_text_operasi;
                
            }
        },
        "fnDrawCallback": function () {
            $('#more_data_operasi').on('click', function(e) {
                e.preventDefault();
                var clientX = e.originalEvent.clientX;
                var clientY = e.originalEvent.clientY;
                $('#tbl_operasi tr').contextMenu({x: clientX, y: clientY});
            });          
        }, 
        "columns": [
{ 'data': 'no_rawat' },
{ 'data': 'tgl_operasi' },
{ 'data': 'jenis_anasthesi' },
{ 'data': 'kategori' },
{ 'data': 'operator1' },
{ 'data': 'operator2' },
{ 'data': 'operator3' },
{ 'data': 'asisten_operator1' },
{ 'data': 'asisten_operator2' },
{ 'data': 'asisten_operator3' },
{ 'data': 'instrumen' },
{ 'data': 'dokter_anak' },
{ 'data': 'perawaat_resusitas' },
{ 'data': 'dokter_anestesi' },
{ 'data': 'asisten_anestesi' },
{ 'data': 'asisten_anestesi2' },
{ 'data': 'bidan' },
{ 'data': 'bidan2' },
{ 'data': 'bidan3' },
{ 'data': 'perawat_luar' },
{ 'data': 'omloop' },
{ 'data': 'omloop2' },
{ 'data': 'omloop3' },
{ 'data': 'omloop4' },
{ 'data': 'omloop5' },
{ 'data': 'dokter_pjanak' },
{ 'data': 'dokter_umum' },
{ 'data': 'kode_paket' },
{ 'data': 'biayaoperator1' },
{ 'data': 'biayaoperator2' },
{ 'data': 'biayaoperator3' },
{ 'data': 'biayaasisten_operator1' },
{ 'data': 'biayaasisten_operator2' },
{ 'data': 'biayaasisten_operator3' },
{ 'data': 'biayainstrumen' },
{ 'data': 'biayadokter_anak' },
{ 'data': 'biayaperawaat_resusitas' },
{ 'data': 'biayadokter_anestesi' },
{ 'data': 'biayaasisten_anestesi' },
{ 'data': 'biayaasisten_anestesi2' },
{ 'data': 'biayabidan' },
{ 'data': 'biayabidan2' },
{ 'data': 'biayabidan3' },
{ 'data': 'biayaperawat_luar' },
{ 'data': 'biayaalat' },
{ 'data': 'biayasewaok' },
{ 'data': 'akomodasi' },
{ 'data': 'bagian_rs' },
{ 'data': 'biaya_omloop' },
{ 'data': 'biaya_omloop2' },
{ 'data': 'biaya_omloop3' },
{ 'data': 'biaya_omloop4' },
{ 'data': 'biaya_omloop5' },
{ 'data': 'biayasarpras' },
{ 'data': 'biaya_dokter_pjanak' },
{ 'data': 'biaya_dokter_umum' },
{ 'data': 'status' }

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
{ 'targets': 33},
{ 'targets': 34},
{ 'targets': 35},
{ 'targets': 36},
{ 'targets': 37},
{ 'targets': 38},
{ 'targets': 39},
{ 'targets': 40},
{ 'targets': 41},
{ 'targets': 42},
{ 'targets': 43},
{ 'targets': 44},
{ 'targets': 45},
{ 'targets': 46},
{ 'targets': 47},
{ 'targets': 48},
{ 'targets': 49},
{ 'targets': 50},
{ 'targets': 51},
{ 'targets': 52},
{ 'targets': 53},
{ 'targets': 54},
{ 'targets': 55},
{ 'targets': 56}

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
        selector: '#tbl_operasi tr', 
        trigger: 'right',
        callback: function(key, options) {
          var rowData = var_tbl_operasi.rows({ selected: true }).data()[0];
          if (rowData != null) {
var no_rawat = rowData['no_rawat'];
            switch (key) {
                case 'detail' :
                    OpenModal(mlite.url + '/operasi/detail/' + no_rawat + '?t=' + mlite.token);
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

    $("form[name='form_operasi']").validate({
        rules: {
no_rawat: 'required',
tgl_operasi: 'required',
jenis_anasthesi: 'required',
kategori: 'required',
operator1: 'required',
operator2: 'required',
operator3: 'required',
asisten_operator1: 'required',
asisten_operator2: 'required',
asisten_operator3: 'required',
instrumen: 'required',
dokter_anak: 'required',
perawaat_resusitas: 'required',
dokter_anestesi: 'required',
asisten_anestesi: 'required',
asisten_anestesi2: 'required',
bidan: 'required',
bidan2: 'required',
bidan3: 'required',
perawat_luar: 'required',
omloop: 'required',
omloop2: 'required',
omloop3: 'required',
omloop4: 'required',
omloop5: 'required',
dokter_pjanak: 'required',
dokter_umum: 'required',
kode_paket: 'required',
biayaoperator1: 'required',
biayaoperator2: 'required',
biayaoperator3: 'required',
biayaasisten_operator1: 'required',
biayaasisten_operator2: 'required',
biayaasisten_operator3: 'required',
biayainstrumen: 'required',
biayadokter_anak: 'required',
biayaperawaat_resusitas: 'required',
biayadokter_anestesi: 'required',
biayaasisten_anestesi: 'required',
biayaasisten_anestesi2: 'required',
biayabidan: 'required',
biayabidan2: 'required',
biayabidan3: 'required',
biayaperawat_luar: 'required',
biayaalat: 'required',
biayasewaok: 'required',
akomodasi: 'required',
bagian_rs: 'required',
biaya_omloop: 'required',
biaya_omloop2: 'required',
biaya_omloop3: 'required',
biaya_omloop4: 'required',
biaya_omloop5: 'required',
biayasarpras: 'required',
biaya_dokter_pjanak: 'required',
biaya_dokter_umum: 'required',
status: 'required'

        },
        messages: {
no_rawat:'No Rawat tidak boleh kosong!',
tgl_operasi:'Tgl Operasi tidak boleh kosong!',
jenis_anasthesi:'Jenis Anasthesi tidak boleh kosong!',
kategori:'Kategori tidak boleh kosong!',
operator1:'Operator1 tidak boleh kosong!',
operator2:'Operator2 tidak boleh kosong!',
operator3:'Operator3 tidak boleh kosong!',
asisten_operator1:'Asisten Operator1 tidak boleh kosong!',
asisten_operator2:'Asisten Operator2 tidak boleh kosong!',
asisten_operator3:'Asisten Operator3 tidak boleh kosong!',
instrumen:'Instrumen tidak boleh kosong!',
dokter_anak:'Dokter Anak tidak boleh kosong!',
perawaat_resusitas:'Perawaat Resusitas tidak boleh kosong!',
dokter_anestesi:'Dokter Anestesi tidak boleh kosong!',
asisten_anestesi:'Asisten Anestesi tidak boleh kosong!',
asisten_anestesi2:'Asisten Anestesi2 tidak boleh kosong!',
bidan:'Bidan tidak boleh kosong!',
bidan2:'Bidan2 tidak boleh kosong!',
bidan3:'Bidan3 tidak boleh kosong!',
perawat_luar:'Perawat Luar tidak boleh kosong!',
omloop:'Omloop tidak boleh kosong!',
omloop2:'Omloop2 tidak boleh kosong!',
omloop3:'Omloop3 tidak boleh kosong!',
omloop4:'Omloop4 tidak boleh kosong!',
omloop5:'Omloop5 tidak boleh kosong!',
dokter_pjanak:'Dokter Pjanak tidak boleh kosong!',
dokter_umum:'Dokter Umum tidak boleh kosong!',
kode_paket:'Kode Paket tidak boleh kosong!',
biayaoperator1:'Biayaoperator1 tidak boleh kosong!',
biayaoperator2:'Biayaoperator2 tidak boleh kosong!',
biayaoperator3:'Biayaoperator3 tidak boleh kosong!',
biayaasisten_operator1:'Biayaasisten Operator1 tidak boleh kosong!',
biayaasisten_operator2:'Biayaasisten Operator2 tidak boleh kosong!',
biayaasisten_operator3:'Biayaasisten Operator3 tidak boleh kosong!',
biayainstrumen:'Biayainstrumen tidak boleh kosong!',
biayadokter_anak:'Biayadokter Anak tidak boleh kosong!',
biayaperawaat_resusitas:'Biayaperawaat Resusitas tidak boleh kosong!',
biayadokter_anestesi:'Biayadokter Anestesi tidak boleh kosong!',
biayaasisten_anestesi:'Biayaasisten Anestesi tidak boleh kosong!',
biayaasisten_anestesi2:'Biayaasisten Anestesi2 tidak boleh kosong!',
biayabidan:'Biayabidan tidak boleh kosong!',
biayabidan2:'Biayabidan2 tidak boleh kosong!',
biayabidan3:'Biayabidan3 tidak boleh kosong!',
biayaperawat_luar:'Biayaperawat Luar tidak boleh kosong!',
biayaalat:'Biayaalat tidak boleh kosong!',
biayasewaok:'Biayasewaok tidak boleh kosong!',
akomodasi:'Akomodasi tidak boleh kosong!',
bagian_rs:'Bagian Rs tidak boleh kosong!',
biaya_omloop:'Biaya Omloop tidak boleh kosong!',
biaya_omloop2:'Biaya Omloop2 tidak boleh kosong!',
biaya_omloop3:'Biaya Omloop3 tidak boleh kosong!',
biaya_omloop4:'Biaya Omloop4 tidak boleh kosong!',
biaya_omloop5:'Biaya Omloop5 tidak boleh kosong!',
biayasarpras:'Biayasarpras tidak boleh kosong!',
biaya_dokter_pjanak:'Biaya Dokter Pjanak tidak boleh kosong!',
biaya_dokter_umum:'Biaya Dokter Umum tidak boleh kosong!',
status:'Status tidak boleh kosong!'

        },
        submitHandler: function (form) {
var no_rawat= $('#no_rawat').val();
var tgl_operasi= $('#tgl_operasi').val();
var jenis_anasthesi= $('#jenis_anasthesi').val();
var kategori= $('#kategori').val();
var operator1= $('#operator1').val();
var operator2= $('#operator2').val();
var operator3= $('#operator3').val();
var asisten_operator1= $('#asisten_operator1').val();
var asisten_operator2= $('#asisten_operator2').val();
var asisten_operator3= $('#asisten_operator3').val();
var instrumen= $('#instrumen').val();
var dokter_anak= $('#dokter_anak').val();
var perawaat_resusitas= $('#perawaat_resusitas').val();
var dokter_anestesi= $('#dokter_anestesi').val();
var asisten_anestesi= $('#asisten_anestesi').val();
var asisten_anestesi2= $('#asisten_anestesi2').val();
var bidan= $('#bidan').val();
var bidan2= $('#bidan2').val();
var bidan3= $('#bidan3').val();
var perawat_luar= $('#perawat_luar').val();
var omloop= $('#omloop').val();
var omloop2= $('#omloop2').val();
var omloop3= $('#omloop3').val();
var omloop4= $('#omloop4').val();
var omloop5= $('#omloop5').val();
var dokter_pjanak= $('#dokter_pjanak').val();
var dokter_umum= $('#dokter_umum').val();
var kode_paket= $('#kode_paket').val();
var biayaoperator1= $('#biayaoperator1').val();
var biayaoperator2= $('#biayaoperator2').val();
var biayaoperator3= $('#biayaoperator3').val();
var biayaasisten_operator1= $('#biayaasisten_operator1').val();
var biayaasisten_operator2= $('#biayaasisten_operator2').val();
var biayaasisten_operator3= $('#biayaasisten_operator3').val();
var biayainstrumen= $('#biayainstrumen').val();
var biayadokter_anak= $('#biayadokter_anak').val();
var biayaperawaat_resusitas= $('#biayaperawaat_resusitas').val();
var biayadokter_anestesi= $('#biayadokter_anestesi').val();
var biayaasisten_anestesi= $('#biayaasisten_anestesi').val();
var biayaasisten_anestesi2= $('#biayaasisten_anestesi2').val();
var biayabidan= $('#biayabidan').val();
var biayabidan2= $('#biayabidan2').val();
var biayabidan3= $('#biayabidan3').val();
var biayaperawat_luar= $('#biayaperawat_luar').val();
var biayaalat= $('#biayaalat').val();
var biayasewaok= $('#biayasewaok').val();
var akomodasi= $('#akomodasi').val();
var bagian_rs= $('#bagian_rs').val();
var biaya_omloop= $('#biaya_omloop').val();
var biaya_omloop2= $('#biaya_omloop2').val();
var biaya_omloop3= $('#biaya_omloop3').val();
var biaya_omloop4= $('#biaya_omloop4').val();
var biaya_omloop5= $('#biaya_omloop5').val();
var biayasarpras= $('#biayasarpras').val();
var biaya_dokter_pjanak= $('#biaya_dokter_pjanak').val();
var biaya_dokter_umum= $('#biaya_dokter_umum').val();
var status= $('#status').val();

var typeact = $('#typeact').val();

var formData = new FormData(form); // tambahan
formData.append('typeact', typeact); // tambahan

            $.ajax({
                url: "{?=url(['operasi','aksi'])?}",
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
                            $("#modal_operasi").modal('hide');
                        } else {
                            bootbox.alert('<span class="text-danger">' + data.msg + '</span>');
                        }    
                    }
                    else if (typeact == "edit") {
                        if(data.status === 'success') {
                            bootbox.alert('<span class="text-success">' + data.msg + '</span>');
                            $("#modal_operasi").modal('hide');
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
                    var_tbl_operasi.draw();
                }
            })
        }
    });

    if(typeof ws != 'undefined' && typeof ws.readyState != 'undefined' && ws.readyState == 1){
        ws.onmessage = function(response){
            try{
                output = JSON.parse(response.data);
                if(output['action'] == 'add'){
                    var_tbl_operasi.draw();
                }
                if(output['action'] == 'edit'){
                    var_tbl_operasi.draw();
                }
                if(output['action'] == 'del'){
                    var_tbl_operasi.draw();
                }
            }catch(e){
                console.log(e);
            }
        }
    }

    // ==============================================================
    // KETIKA TOMBOL SEARCH DITEKAN
    // ==============================================================
    $('#filter_search_operasi').click(function () {
        var_tbl_operasi.draw();
    });

    // ===========================================
    // KETIKA TOMBOL EDIT DITEKAN
    // ===========================================

    $("#edit_data_operasi").click(function () {
        var rowData = var_tbl_operasi.rows({ selected: true }).data()[0];
        if (rowData != null) {

            var no_rawat = rowData['no_rawat'];
var tgl_operasi = rowData['tgl_operasi'];
var jenis_anasthesi = rowData['jenis_anasthesi'];
var kategori = rowData['kategori'];
var operator1 = rowData['operator1'];
var operator2 = rowData['operator2'];
var operator3 = rowData['operator3'];
var asisten_operator1 = rowData['asisten_operator1'];
var asisten_operator2 = rowData['asisten_operator2'];
var asisten_operator3 = rowData['asisten_operator3'];
var instrumen = rowData['instrumen'];
var dokter_anak = rowData['dokter_anak'];
var perawaat_resusitas = rowData['perawaat_resusitas'];
var dokter_anestesi = rowData['dokter_anestesi'];
var asisten_anestesi = rowData['asisten_anestesi'];
var asisten_anestesi2 = rowData['asisten_anestesi2'];
var bidan = rowData['bidan'];
var bidan2 = rowData['bidan2'];
var bidan3 = rowData['bidan3'];
var perawat_luar = rowData['perawat_luar'];
var omloop = rowData['omloop'];
var omloop2 = rowData['omloop2'];
var omloop3 = rowData['omloop3'];
var omloop4 = rowData['omloop4'];
var omloop5 = rowData['omloop5'];
var dokter_pjanak = rowData['dokter_pjanak'];
var dokter_umum = rowData['dokter_umum'];
var kode_paket = rowData['kode_paket'];
var biayaoperator1 = rowData['biayaoperator1'];
var biayaoperator2 = rowData['biayaoperator2'];
var biayaoperator3 = rowData['biayaoperator3'];
var biayaasisten_operator1 = rowData['biayaasisten_operator1'];
var biayaasisten_operator2 = rowData['biayaasisten_operator2'];
var biayaasisten_operator3 = rowData['biayaasisten_operator3'];
var biayainstrumen = rowData['biayainstrumen'];
var biayadokter_anak = rowData['biayadokter_anak'];
var biayaperawaat_resusitas = rowData['biayaperawaat_resusitas'];
var biayadokter_anestesi = rowData['biayadokter_anestesi'];
var biayaasisten_anestesi = rowData['biayaasisten_anestesi'];
var biayaasisten_anestesi2 = rowData['biayaasisten_anestesi2'];
var biayabidan = rowData['biayabidan'];
var biayabidan2 = rowData['biayabidan2'];
var biayabidan3 = rowData['biayabidan3'];
var biayaperawat_luar = rowData['biayaperawat_luar'];
var biayaalat = rowData['biayaalat'];
var biayasewaok = rowData['biayasewaok'];
var akomodasi = rowData['akomodasi'];
var bagian_rs = rowData['bagian_rs'];
var biaya_omloop = rowData['biaya_omloop'];
var biaya_omloop2 = rowData['biaya_omloop2'];
var biaya_omloop3 = rowData['biaya_omloop3'];
var biaya_omloop4 = rowData['biaya_omloop4'];
var biaya_omloop5 = rowData['biaya_omloop5'];
var biayasarpras = rowData['biayasarpras'];
var biaya_dokter_pjanak = rowData['biaya_dokter_pjanak'];
var biaya_dokter_umum = rowData['biaya_dokter_umum'];
var status = rowData['status'];

            $("#typeact").val("edit");
  
            $('#no_rawat').val(no_rawat);
$('#tgl_operasi').val(tgl_operasi);
$('#jenis_anasthesi').val(jenis_anasthesi);
$('#kategori').val(kategori);
$('#operator1').val(operator1);
$('#operator2').val(operator2);
$('#operator3').val(operator3);
$('#asisten_operator1').val(asisten_operator1);
$('#asisten_operator2').val(asisten_operator2);
$('#asisten_operator3').val(asisten_operator3);
$('#instrumen').val(instrumen);
$('#dokter_anak').val(dokter_anak);
$('#perawaat_resusitas').val(perawaat_resusitas);
$('#dokter_anestesi').val(dokter_anestesi);
$('#asisten_anestesi').val(asisten_anestesi);
$('#asisten_anestesi2').val(asisten_anestesi2);
$('#bidan').val(bidan);
$('#bidan2').val(bidan2);
$('#bidan3').val(bidan3);
$('#perawat_luar').val(perawat_luar);
$('#omloop').val(omloop);
$('#omloop2').val(omloop2);
$('#omloop3').val(omloop3);
$('#omloop4').val(omloop4);
$('#omloop5').val(omloop5);
$('#dokter_pjanak').val(dokter_pjanak);
$('#dokter_umum').val(dokter_umum);
$('#kode_paket').val(kode_paket);
$('#biayaoperator1').val(biayaoperator1);
$('#biayaoperator2').val(biayaoperator2);
$('#biayaoperator3').val(biayaoperator3);
$('#biayaasisten_operator1').val(biayaasisten_operator1);
$('#biayaasisten_operator2').val(biayaasisten_operator2);
$('#biayaasisten_operator3').val(biayaasisten_operator3);
$('#biayainstrumen').val(biayainstrumen);
$('#biayadokter_anak').val(biayadokter_anak);
$('#biayaperawaat_resusitas').val(biayaperawaat_resusitas);
$('#biayadokter_anestesi').val(biayadokter_anestesi);
$('#biayaasisten_anestesi').val(biayaasisten_anestesi);
$('#biayaasisten_anestesi2').val(biayaasisten_anestesi2);
$('#biayabidan').val(biayabidan);
$('#biayabidan2').val(biayabidan2);
$('#biayabidan3').val(biayabidan3);
$('#biayaperawat_luar').val(biayaperawat_luar);
$('#biayaalat').val(biayaalat);
$('#biayasewaok').val(biayasewaok);
$('#akomodasi').val(akomodasi);
$('#bagian_rs').val(bagian_rs);
$('#biaya_omloop').val(biaya_omloop);
$('#biaya_omloop2').val(biaya_omloop2);
$('#biaya_omloop3').val(biaya_omloop3);
$('#biaya_omloop4').val(biaya_omloop4);
$('#biaya_omloop5').val(biaya_omloop5);
$('#biayasarpras').val(biayasarpras);
$('#biaya_dokter_pjanak').val(biaya_dokter_pjanak);
$('#biaya_dokter_umum').val(biaya_dokter_umum);
$('#status').val(status);

            $("#no_rawat").prop('readonly', true); // GA BISA DIEDIT KALI READONLY
            $('#modal-title').text("Edit Data Operasi");
            $("#modal_operasi").modal('show');
        }
        else {
            bootbox.alert("Silakan pilih data yang akan di edit.");
        }

    });

    // ==============================================================
    // TOMBOL  DELETE DI CLICK
    // ==============================================================
    jQuery("#hapus_data_operasi").click(function () {
        var rowData = var_tbl_operasi.rows({ selected: true }).data()[0];


        if (rowData) {
var no_rawat = rowData['no_rawat'];
            bootbox.confirm('Anda yakin akan menghapus data dengan no_rawat="' + no_rawat, function(result) {
                if(result) {
                    $.ajax({
                        url: "{?=url(['operasi','aksi'])?}",
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
                            var_tbl_operasi.draw();
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
    jQuery("#tambah_data_operasi").click(function () {

        $('#no_rawat').val('');
$('#tgl_operasi').val('');
$('#jenis_anasthesi').val('');
$('#kategori').val('');
$('#operator1').val('');
$('#operator2').val('');
$('#operator3').val('');
$('#asisten_operator1').val('');
$('#asisten_operator2').val('');
$('#asisten_operator3').val('');
$('#instrumen').val('');
$('#dokter_anak').val('');
$('#perawaat_resusitas').val('');
$('#dokter_anestesi').val('');
$('#asisten_anestesi').val('');
$('#asisten_anestesi2').val('');
$('#bidan').val('');
$('#bidan2').val('');
$('#bidan3').val('');
$('#perawat_luar').val('');
$('#omloop').val('');
$('#omloop2').val('');
$('#omloop3').val('');
$('#omloop4').val('');
$('#omloop5').val('');
$('#dokter_pjanak').val('');
$('#dokter_umum').val('');
$('#kode_paket').val('');
$('#biayaoperator1').val('');
$('#biayaoperator2').val('');
$('#biayaoperator3').val('');
$('#biayaasisten_operator1').val('');
$('#biayaasisten_operator2').val('');
$('#biayaasisten_operator3').val('');
$('#biayainstrumen').val('');
$('#biayadokter_anak').val('');
$('#biayaperawaat_resusitas').val('');
$('#biayadokter_anestesi').val('');
$('#biayaasisten_anestesi').val('');
$('#biayaasisten_anestesi2').val('');
$('#biayabidan').val('');
$('#biayabidan2').val('');
$('#biayabidan3').val('');
$('#biayaperawat_luar').val('');
$('#biayaalat').val('');
$('#biayasewaok').val('');
$('#akomodasi').val('');
$('#bagian_rs').val('');
$('#biaya_omloop').val('');
$('#biaya_omloop2').val('');
$('#biaya_omloop3').val('');
$('#biaya_omloop4').val('');
$('#biaya_omloop5').val('');
$('#biayasarpras').val('');
$('#biaya_dokter_pjanak').val('');
$('#biaya_dokter_umum').val('');
$('#status').val('');

        $("#typeact").val("add");
        $("#no_rawat").prop('readonly', false);
        
        $('#modal-title').text("Tambah Data Operasi");
        $("#modal_operasi").modal('show');
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_operasi").click(function () {

        var search_field_operasi = $('#search_field_operasi').val();
        var search_text_operasi = $('#search_text_operasi').val();

        $.ajax({
            url: "{?=url(['operasi','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat', 
                search_field_operasi: search_field_operasi, 
                search_text_operasi: search_text_operasi
            },
            dataType: 'json',
            success: function (res) {
                var eTable = "<div class='table-responsive'><table id='tbl_lihat_operasi' class='table display dataTable' style='width:100%'><thead><th>No Rawat</th><th>Tgl Operasi</th><th>Jenis Anasthesi</th><th>Kategori</th><th>Operator1</th><th>Operator2</th><th>Operator3</th><th>Asisten Operator1</th><th>Asisten Operator2</th><th>Asisten Operator3</th><th>Instrumen</th><th>Dokter Anak</th><th>Perawaat Resusitas</th><th>Dokter Anestesi</th><th>Asisten Anestesi</th><th>Asisten Anestesi2</th><th>Bidan</th><th>Bidan2</th><th>Bidan3</th><th>Perawat Luar</th><th>Omloop</th><th>Omloop2</th><th>Omloop3</th><th>Omloop4</th><th>Omloop5</th><th>Dokter Pjanak</th><th>Dokter Umum</th><th>Kode Paket</th><th>Biayaoperator1</th><th>Biayaoperator2</th><th>Biayaoperator3</th><th>Biayaasisten Operator1</th><th>Biayaasisten Operator2</th><th>Biayaasisten Operator3</th><th>Biayainstrumen</th><th>Biayadokter Anak</th><th>Biayaperawaat Resusitas</th><th>Biayadokter Anestesi</th><th>Biayaasisten Anestesi</th><th>Biayaasisten Anestesi2</th><th>Biayabidan</th><th>Biayabidan2</th><th>Biayabidan3</th><th>Biayaperawat Luar</th><th>Biayaalat</th><th>Biayasewaok</th><th>Akomodasi</th><th>Bagian Rs</th><th>Biaya Omloop</th><th>Biaya Omloop2</th><th>Biaya Omloop3</th><th>Biaya Omloop4</th><th>Biaya Omloop5</th><th>Biayasarpras</th><th>Biaya Dokter Pjanak</th><th>Biaya Dokter Umum</th><th>Status</th></thead>";
                for (var i = 0; i < res.length; i++) {
                    eTable += "<tr>";
                    eTable += '<td>' + res[i]['no_rawat'] + '</td>';
eTable += '<td>' + res[i]['tgl_operasi'] + '</td>';
eTable += '<td>' + res[i]['jenis_anasthesi'] + '</td>';
eTable += '<td>' + res[i]['kategori'] + '</td>';
eTable += '<td>' + res[i]['operator1'] + '</td>';
eTable += '<td>' + res[i]['operator2'] + '</td>';
eTable += '<td>' + res[i]['operator3'] + '</td>';
eTable += '<td>' + res[i]['asisten_operator1'] + '</td>';
eTable += '<td>' + res[i]['asisten_operator2'] + '</td>';
eTable += '<td>' + res[i]['asisten_operator3'] + '</td>';
eTable += '<td>' + res[i]['instrumen'] + '</td>';
eTable += '<td>' + res[i]['dokter_anak'] + '</td>';
eTable += '<td>' + res[i]['perawaat_resusitas'] + '</td>';
eTable += '<td>' + res[i]['dokter_anestesi'] + '</td>';
eTable += '<td>' + res[i]['asisten_anestesi'] + '</td>';
eTable += '<td>' + res[i]['asisten_anestesi2'] + '</td>';
eTable += '<td>' + res[i]['bidan'] + '</td>';
eTable += '<td>' + res[i]['bidan2'] + '</td>';
eTable += '<td>' + res[i]['bidan3'] + '</td>';
eTable += '<td>' + res[i]['perawat_luar'] + '</td>';
eTable += '<td>' + res[i]['omloop'] + '</td>';
eTable += '<td>' + res[i]['omloop2'] + '</td>';
eTable += '<td>' + res[i]['omloop3'] + '</td>';
eTable += '<td>' + res[i]['omloop4'] + '</td>';
eTable += '<td>' + res[i]['omloop5'] + '</td>';
eTable += '<td>' + res[i]['dokter_pjanak'] + '</td>';
eTable += '<td>' + res[i]['dokter_umum'] + '</td>';
eTable += '<td>' + res[i]['kode_paket'] + '</td>';
eTable += '<td>' + res[i]['biayaoperator1'] + '</td>';
eTable += '<td>' + res[i]['biayaoperator2'] + '</td>';
eTable += '<td>' + res[i]['biayaoperator3'] + '</td>';
eTable += '<td>' + res[i]['biayaasisten_operator1'] + '</td>';
eTable += '<td>' + res[i]['biayaasisten_operator2'] + '</td>';
eTable += '<td>' + res[i]['biayaasisten_operator3'] + '</td>';
eTable += '<td>' + res[i]['biayainstrumen'] + '</td>';
eTable += '<td>' + res[i]['biayadokter_anak'] + '</td>';
eTable += '<td>' + res[i]['biayaperawaat_resusitas'] + '</td>';
eTable += '<td>' + res[i]['biayadokter_anestesi'] + '</td>';
eTable += '<td>' + res[i]['biayaasisten_anestesi'] + '</td>';
eTable += '<td>' + res[i]['biayaasisten_anestesi2'] + '</td>';
eTable += '<td>' + res[i]['biayabidan'] + '</td>';
eTable += '<td>' + res[i]['biayabidan2'] + '</td>';
eTable += '<td>' + res[i]['biayabidan3'] + '</td>';
eTable += '<td>' + res[i]['biayaperawat_luar'] + '</td>';
eTable += '<td>' + res[i]['biayaalat'] + '</td>';
eTable += '<td>' + res[i]['biayasewaok'] + '</td>';
eTable += '<td>' + res[i]['akomodasi'] + '</td>';
eTable += '<td>' + res[i]['bagian_rs'] + '</td>';
eTable += '<td>' + res[i]['biaya_omloop'] + '</td>';
eTable += '<td>' + res[i]['biaya_omloop2'] + '</td>';
eTable += '<td>' + res[i]['biaya_omloop3'] + '</td>';
eTable += '<td>' + res[i]['biaya_omloop4'] + '</td>';
eTable += '<td>' + res[i]['biaya_omloop5'] + '</td>';
eTable += '<td>' + res[i]['biayasarpras'] + '</td>';
eTable += '<td>' + res[i]['biaya_dokter_pjanak'] + '</td>';
eTable += '<td>' + res[i]['biaya_dokter_umum'] + '</td>';
eTable += '<td>' + res[i]['status'] + '</td>';
                    eTable += "</tr>";
                }
                eTable += "</tbody></table></div>";
                $('#forTable_operasi').html(eTable);
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_operasi").modal('show');
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
        doc.text("Tabel Data Operasi", 20, 95, null, null, null);
        const totalPagesExp = "{total_pages_count_string}";        
        doc.autoTable({
            html: '#tbl_lihat_operasi',
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
        // doc.save('table_data_operasi.pdf');
        window.open(doc.output('bloburl'), '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
              
    })

    // ===========================================
    // Ketika tombol export xlsx di tekan
    // ===========================================
    $("#export_xlsx").click(function () {
        let tbl1 = document.getElementById("tbl_lihat_operasi");
        let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
        let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
        let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
        const new_workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data operasi");
        XLSX.writeFile(new_workbook, 'tmp_file.xls');
    })

    $("#view_chart").click(function () {
        window.open(mlite.url + '/operasi/chart?t=' + mlite.token, '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
    })   

});