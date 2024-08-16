jQuery().ready(function () {
    var var_tbl_bridging_sep_internal = $('#tbl_bridging_sep_internal').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'dom': 'Bfrtip',
        'searching': false,
        'select': true,
        'colReorder': true,
        "bInfo" : false,
        "ajax": {
            "url": "{?=url(['bridging_sep_internal','data'])?}",
            "dataType": "json",
            "type": "POST",
            "data": function (data) {

                // Read values
                var search_field_bridging_sep_internal = $('#search_field_bridging_sep_internal').val();
                var search_text_bridging_sep_internal = $('#search_text_bridging_sep_internal').val();
                
                data.search_field_bridging_sep_internal = search_field_bridging_sep_internal;
                data.search_text_bridging_sep_internal = search_text_bridging_sep_internal;
                
            }
        },
        "fnDrawCallback": function () {
            $('#more_data_bridging_sep_internal').on('click', function(e) {
                e.preventDefault();
                var clientX = e.originalEvent.clientX;
                var clientY = e.originalEvent.clientY;
                $('#tbl_bridging_sep_internal tr').contextMenu({x: clientX, y: clientY});
            });          
        }, 
        "columns": [
{ 'data': 'no_sep' },
{ 'data': 'no_rawat' },
{ 'data': 'tglsep' },
{ 'data': 'tglrujukan' },
{ 'data': 'no_rujukan' },
{ 'data': 'kdppkrujukan' },
{ 'data': 'nmppkrujukan' },
{ 'data': 'kdppkpelayanan' },
{ 'data': 'nmppkpelayanan' },
{ 'data': 'jnspelayanan' },
{ 'data': 'catatan' },
{ 'data': 'diagawal' },
{ 'data': 'nmdiagnosaawal' },
{ 'data': 'kdpolitujuan' },
{ 'data': 'nmpolitujuan' },
{ 'data': 'klsrawat' },
{ 'data': 'klsnaik' },
{ 'data': 'pembiayaan' },
{ 'data': 'pjnaikkelas' },
{ 'data': 'lakalantas' },
{ 'data': 'user' },
{ 'data': 'nomr' },
{ 'data': 'nama_pasien' },
{ 'data': 'tanggal_lahir' },
{ 'data': 'peserta' },
{ 'data': 'jkel' },
{ 'data': 'no_kartu' },
{ 'data': 'tglpulang' },
{ 'data': 'asal_rujukan' },
{ 'data': 'eksekutif' },
{ 'data': 'cob' },
{ 'data': 'notelep' },
{ 'data': 'katarak' },
{ 'data': 'tglkkl' },
{ 'data': 'keterangankkl' },
{ 'data': 'suplesi' },
{ 'data': 'no_sep_suplesi' },
{ 'data': 'kdprop' },
{ 'data': 'nmprop' },
{ 'data': 'kdkab' },
{ 'data': 'nmkab' },
{ 'data': 'kdkec' },
{ 'data': 'nmkec' },
{ 'data': 'noskdp' },
{ 'data': 'kddpjp' },
{ 'data': 'nmdpdjp' },
{ 'data': 'tujuankunjungan' },
{ 'data': 'flagprosedur' },
{ 'data': 'penunjang' },
{ 'data': 'asesmenpelayanan' },
{ 'data': 'kddpjplayanan' },
{ 'data': 'nmdpjplayanan' }

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
{ 'targets': 51}

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
        selector: '#tbl_bridging_sep_internal tr', 
        trigger: 'right',
        callback: function(key, options) {
          var rowData = var_tbl_bridging_sep_internal.rows({ selected: true }).data()[0];
          if (rowData != null) {
var no_sep = rowData['no_sep'];
            switch (key) {
                case 'detail' :
                    OpenModal(mlite.url + '/bridging_sep_internal/detail/' + no_sep + '?t=' + mlite.token);
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

    $("form[name='form_bridging_sep_internal']").validate({
        rules: {
no_sep: 'required',
no_rawat: 'required',
tglsep: 'required',
tglrujukan: 'required',
no_rujukan: 'required',
kdppkrujukan: 'required',
nmppkrujukan: 'required',
kdppkpelayanan: 'required',
nmppkpelayanan: 'required',
jnspelayanan: 'required',
catatan: 'required',
diagawal: 'required',
nmdiagnosaawal: 'required',
kdpolitujuan: 'required',
nmpolitujuan: 'required',
klsrawat: 'required',
klsnaik: 'required',
pembiayaan: 'required',
pjnaikkelas: 'required',
lakalantas: 'required',
user: 'required',
nomr: 'required',
nama_pasien: 'required',
tanggal_lahir: 'required',
peserta: 'required',
jkel: 'required',
no_kartu: 'required',
tglpulang: 'required',
asal_rujukan: 'required',
eksekutif: 'required',
cob: 'required',
notelep: 'required',
katarak: 'required',
tglkkl: 'required',
keterangankkl: 'required',
suplesi: 'required',
no_sep_suplesi: 'required',
kdprop: 'required',
nmprop: 'required',
kdkab: 'required',
nmkab: 'required',
kdkec: 'required',
nmkec: 'required',
noskdp: 'required',
kddpjp: 'required',
nmdpdjp: 'required',
tujuankunjungan: 'required',
flagprosedur: 'required',
penunjang: 'required',
asesmenpelayanan: 'required',
kddpjplayanan: 'required',
nmdpjplayanan: 'required'

        },
        messages: {
no_sep:'No Sep tidak boleh kosong!',
no_rawat:'No Rawat tidak boleh kosong!',
tglsep:'Tglsep tidak boleh kosong!',
tglrujukan:'Tglrujukan tidak boleh kosong!',
no_rujukan:'No Rujukan tidak boleh kosong!',
kdppkrujukan:'Kdppkrujukan tidak boleh kosong!',
nmppkrujukan:'Nmppkrujukan tidak boleh kosong!',
kdppkpelayanan:'Kdppkpelayanan tidak boleh kosong!',
nmppkpelayanan:'Nmppkpelayanan tidak boleh kosong!',
jnspelayanan:'Jnspelayanan tidak boleh kosong!',
catatan:'Catatan tidak boleh kosong!',
diagawal:'Diagawal tidak boleh kosong!',
nmdiagnosaawal:'Nmdiagnosaawal tidak boleh kosong!',
kdpolitujuan:'Kdpolitujuan tidak boleh kosong!',
nmpolitujuan:'Nmpolitujuan tidak boleh kosong!',
klsrawat:'Klsrawat tidak boleh kosong!',
klsnaik:'Klsnaik tidak boleh kosong!',
pembiayaan:'Pembiayaan tidak boleh kosong!',
pjnaikkelas:'Pjnaikkelas tidak boleh kosong!',
lakalantas:'Lakalantas tidak boleh kosong!',
user:'User tidak boleh kosong!',
nomr:'Nomr tidak boleh kosong!',
nama_pasien:'Nama Pasien tidak boleh kosong!',
tanggal_lahir:'Tanggal Lahir tidak boleh kosong!',
peserta:'Peserta tidak boleh kosong!',
jkel:'Jkel tidak boleh kosong!',
no_kartu:'No Kartu tidak boleh kosong!',
tglpulang:'Tglpulang tidak boleh kosong!',
asal_rujukan:'Asal Rujukan tidak boleh kosong!',
eksekutif:'Eksekutif tidak boleh kosong!',
cob:'Cob tidak boleh kosong!',
notelep:'Notelep tidak boleh kosong!',
katarak:'Katarak tidak boleh kosong!',
tglkkl:'Tglkkl tidak boleh kosong!',
keterangankkl:'Keterangankkl tidak boleh kosong!',
suplesi:'Suplesi tidak boleh kosong!',
no_sep_suplesi:'No Sep Suplesi tidak boleh kosong!',
kdprop:'Kdprop tidak boleh kosong!',
nmprop:'Nmprop tidak boleh kosong!',
kdkab:'Kdkab tidak boleh kosong!',
nmkab:'Nmkab tidak boleh kosong!',
kdkec:'Kdkec tidak boleh kosong!',
nmkec:'Nmkec tidak boleh kosong!',
noskdp:'Noskdp tidak boleh kosong!',
kddpjp:'Kddpjp tidak boleh kosong!',
nmdpdjp:'Nmdpdjp tidak boleh kosong!',
tujuankunjungan:'Tujuankunjungan tidak boleh kosong!',
flagprosedur:'Flagprosedur tidak boleh kosong!',
penunjang:'Penunjang tidak boleh kosong!',
asesmenpelayanan:'Asesmenpelayanan tidak boleh kosong!',
kddpjplayanan:'Kddpjplayanan tidak boleh kosong!',
nmdpjplayanan:'Nmdpjplayanan tidak boleh kosong!'

        },
        submitHandler: function (form) {
var no_sep= $('#no_sep').val();
var no_rawat= $('#no_rawat').val();
var tglsep= $('#tglsep').val();
var tglrujukan= $('#tglrujukan').val();
var no_rujukan= $('#no_rujukan').val();
var kdppkrujukan= $('#kdppkrujukan').val();
var nmppkrujukan= $('#nmppkrujukan').val();
var kdppkpelayanan= $('#kdppkpelayanan').val();
var nmppkpelayanan= $('#nmppkpelayanan').val();
var jnspelayanan= $('#jnspelayanan').val();
var catatan= $('#catatan').val();
var diagawal= $('#diagawal').val();
var nmdiagnosaawal= $('#nmdiagnosaawal').val();
var kdpolitujuan= $('#kdpolitujuan').val();
var nmpolitujuan= $('#nmpolitujuan').val();
var klsrawat= $('#klsrawat').val();
var klsnaik= $('#klsnaik').val();
var pembiayaan= $('#pembiayaan').val();
var pjnaikkelas= $('#pjnaikkelas').val();
var lakalantas= $('#lakalantas').val();
var user= $('#user').val();
var nomr= $('#nomr').val();
var nama_pasien= $('#nama_pasien').val();
var tanggal_lahir= $('#tanggal_lahir').val();
var peserta= $('#peserta').val();
var jkel= $('#jkel').val();
var no_kartu= $('#no_kartu').val();
var tglpulang= $('#tglpulang').val();
var asal_rujukan= $('#asal_rujukan').val();
var eksekutif= $('#eksekutif').val();
var cob= $('#cob').val();
var notelep= $('#notelep').val();
var katarak= $('#katarak').val();
var tglkkl= $('#tglkkl').val();
var keterangankkl= $('#keterangankkl').val();
var suplesi= $('#suplesi').val();
var no_sep_suplesi= $('#no_sep_suplesi').val();
var kdprop= $('#kdprop').val();
var nmprop= $('#nmprop').val();
var kdkab= $('#kdkab').val();
var nmkab= $('#nmkab').val();
var kdkec= $('#kdkec').val();
var nmkec= $('#nmkec').val();
var noskdp= $('#noskdp').val();
var kddpjp= $('#kddpjp').val();
var nmdpdjp= $('#nmdpdjp').val();
var tujuankunjungan= $('#tujuankunjungan').val();
var flagprosedur= $('#flagprosedur').val();
var penunjang= $('#penunjang').val();
var asesmenpelayanan= $('#asesmenpelayanan').val();
var kddpjplayanan= $('#kddpjplayanan').val();
var nmdpjplayanan= $('#nmdpjplayanan').val();

var typeact = $('#typeact').val();

var formData = new FormData(form); // tambahan
formData.append('typeact', typeact); // tambahan

            $.ajax({
                url: "{?=url(['bridging_sep_internal','aksi'])?}",
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
                            $("#modal_bridging_sep_internal").modal('hide');
                        } else {
                            bootbox.alert('<span class="text-danger">' + data.msg + '</span>');
                        }    
                    }
                    else if (typeact == "edit") {
                        if(data.status === 'success') {
                            bootbox.alert('<span class="text-success">' + data.msg + '</span>');
                            $("#modal_bridging_sep_internal").modal('hide');
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
                    var_tbl_bridging_sep_internal.draw();
                }
            })
        }
    });

    if(typeof ws != 'undefined' && typeof ws.readyState != 'undefined' && ws.readyState == 1){
        ws.onmessage = function(response){
            try{
                output = JSON.parse(response.data);
                if(output['action'] == 'add'){
                    var_tbl_bridging_sep_internal.draw();
                }
                if(output['action'] == 'edit'){
                    var_tbl_bridging_sep_internal.draw();
                }
                if(output['action'] == 'del'){
                    var_tbl_bridging_sep_internal.draw();
                }
            }catch(e){
                console.log(e);
            }
        }
    }

    // ==============================================================
    // KETIKA TOMBOL SEARCH DITEKAN
    // ==============================================================
    $('#filter_search_bridging_sep_internal').click(function () {
        var_tbl_bridging_sep_internal.draw();
    });

    // ===========================================
    // KETIKA TOMBOL EDIT DITEKAN
    // ===========================================

    $("#edit_data_bridging_sep_internal").click(function () {
        var rowData = var_tbl_bridging_sep_internal.rows({ selected: true }).data()[0];
        if (rowData != null) {

            var no_sep = rowData['no_sep'];
var no_rawat = rowData['no_rawat'];
var tglsep = rowData['tglsep'];
var tglrujukan = rowData['tglrujukan'];
var no_rujukan = rowData['no_rujukan'];
var kdppkrujukan = rowData['kdppkrujukan'];
var nmppkrujukan = rowData['nmppkrujukan'];
var kdppkpelayanan = rowData['kdppkpelayanan'];
var nmppkpelayanan = rowData['nmppkpelayanan'];
var jnspelayanan = rowData['jnspelayanan'];
var catatan = rowData['catatan'];
var diagawal = rowData['diagawal'];
var nmdiagnosaawal = rowData['nmdiagnosaawal'];
var kdpolitujuan = rowData['kdpolitujuan'];
var nmpolitujuan = rowData['nmpolitujuan'];
var klsrawat = rowData['klsrawat'];
var klsnaik = rowData['klsnaik'];
var pembiayaan = rowData['pembiayaan'];
var pjnaikkelas = rowData['pjnaikkelas'];
var lakalantas = rowData['lakalantas'];
var user = rowData['user'];
var nomr = rowData['nomr'];
var nama_pasien = rowData['nama_pasien'];
var tanggal_lahir = rowData['tanggal_lahir'];
var peserta = rowData['peserta'];
var jkel = rowData['jkel'];
var no_kartu = rowData['no_kartu'];
var tglpulang = rowData['tglpulang'];
var asal_rujukan = rowData['asal_rujukan'];
var eksekutif = rowData['eksekutif'];
var cob = rowData['cob'];
var notelep = rowData['notelep'];
var katarak = rowData['katarak'];
var tglkkl = rowData['tglkkl'];
var keterangankkl = rowData['keterangankkl'];
var suplesi = rowData['suplesi'];
var no_sep_suplesi = rowData['no_sep_suplesi'];
var kdprop = rowData['kdprop'];
var nmprop = rowData['nmprop'];
var kdkab = rowData['kdkab'];
var nmkab = rowData['nmkab'];
var kdkec = rowData['kdkec'];
var nmkec = rowData['nmkec'];
var noskdp = rowData['noskdp'];
var kddpjp = rowData['kddpjp'];
var nmdpdjp = rowData['nmdpdjp'];
var tujuankunjungan = rowData['tujuankunjungan'];
var flagprosedur = rowData['flagprosedur'];
var penunjang = rowData['penunjang'];
var asesmenpelayanan = rowData['asesmenpelayanan'];
var kddpjplayanan = rowData['kddpjplayanan'];
var nmdpjplayanan = rowData['nmdpjplayanan'];

            $("#typeact").val("edit");
  
            $('#no_sep').val(no_sep);
$('#no_rawat').val(no_rawat);
$('#tglsep').val(tglsep);
$('#tglrujukan').val(tglrujukan);
$('#no_rujukan').val(no_rujukan);
$('#kdppkrujukan').val(kdppkrujukan);
$('#nmppkrujukan').val(nmppkrujukan);
$('#kdppkpelayanan').val(kdppkpelayanan);
$('#nmppkpelayanan').val(nmppkpelayanan);
$('#jnspelayanan').val(jnspelayanan);
$('#catatan').val(catatan);
$('#diagawal').val(diagawal);
$('#nmdiagnosaawal').val(nmdiagnosaawal);
$('#kdpolitujuan').val(kdpolitujuan);
$('#nmpolitujuan').val(nmpolitujuan);
$('#klsrawat').val(klsrawat);
$('#klsnaik').val(klsnaik);
$('#pembiayaan').val(pembiayaan);
$('#pjnaikkelas').val(pjnaikkelas);
$('#lakalantas').val(lakalantas);
$('#user').val(user);
$('#nomr').val(nomr);
$('#nama_pasien').val(nama_pasien);
$('#tanggal_lahir').val(tanggal_lahir);
$('#peserta').val(peserta);
$('#jkel').val(jkel);
$('#no_kartu').val(no_kartu);
$('#tglpulang').val(tglpulang);
$('#asal_rujukan').val(asal_rujukan);
$('#eksekutif').val(eksekutif);
$('#cob').val(cob);
$('#notelep').val(notelep);
$('#katarak').val(katarak);
$('#tglkkl').val(tglkkl);
$('#keterangankkl').val(keterangankkl);
$('#suplesi').val(suplesi);
$('#no_sep_suplesi').val(no_sep_suplesi);
$('#kdprop').val(kdprop);
$('#nmprop').val(nmprop);
$('#kdkab').val(kdkab);
$('#nmkab').val(nmkab);
$('#kdkec').val(kdkec);
$('#nmkec').val(nmkec);
$('#noskdp').val(noskdp);
$('#kddpjp').val(kddpjp);
$('#nmdpdjp').val(nmdpdjp);
$('#tujuankunjungan').val(tujuankunjungan);
$('#flagprosedur').val(flagprosedur);
$('#penunjang').val(penunjang);
$('#asesmenpelayanan').val(asesmenpelayanan);
$('#kddpjplayanan').val(kddpjplayanan);
$('#nmdpjplayanan').val(nmdpjplayanan);

            $("#no_sep").prop('readonly', true); // GA BISA DIEDIT KALI READONLY
            $('#modal-title').text("Edit Data Bridging Sep Internal");
            $("#modal_bridging_sep_internal").modal('show');
        }
        else {
            bootbox.alert("Silakan pilih data yang akan di edit.");
        }

    });

    // ==============================================================
    // TOMBOL  DELETE DI CLICK
    // ==============================================================
    jQuery("#hapus_data_bridging_sep_internal").click(function () {
        var rowData = var_tbl_bridging_sep_internal.rows({ selected: true }).data()[0];


        if (rowData) {
var no_sep = rowData['no_sep'];
            bootbox.confirm('Anda yakin akan menghapus data dengan no_sep="' + no_sep, function(result) {
                if(result) {
                    $.ajax({
                        url: "{?=url(['bridging_sep_internal','aksi'])?}",
                        method: "POST",
                        data: {
                            no_sep: no_sep,
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
                            var_tbl_bridging_sep_internal.draw();
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
    jQuery("#tambah_data_bridging_sep_internal").click(function () {

        $('#no_sep').val('');
$('#no_rawat').val('');
$('#tglsep').val('');
$('#tglrujukan').val('');
$('#no_rujukan').val('');
$('#kdppkrujukan').val('');
$('#nmppkrujukan').val('');
$('#kdppkpelayanan').val('');
$('#nmppkpelayanan').val('');
$('#jnspelayanan').val('');
$('#catatan').val('');
$('#diagawal').val('');
$('#nmdiagnosaawal').val('');
$('#kdpolitujuan').val('');
$('#nmpolitujuan').val('');
$('#klsrawat').val('');
$('#klsnaik').val('');
$('#pembiayaan').val('');
$('#pjnaikkelas').val('');
$('#lakalantas').val('');
$('#user').val('');
$('#nomr').val('');
$('#nama_pasien').val('');
$('#tanggal_lahir').val('');
$('#peserta').val('');
$('#jkel').val('');
$('#no_kartu').val('');
$('#tglpulang').val('');
$('#asal_rujukan').val('');
$('#eksekutif').val('');
$('#cob').val('');
$('#notelep').val('');
$('#katarak').val('');
$('#tglkkl').val('');
$('#keterangankkl').val('');
$('#suplesi').val('');
$('#no_sep_suplesi').val('');
$('#kdprop').val('');
$('#nmprop').val('');
$('#kdkab').val('');
$('#nmkab').val('');
$('#kdkec').val('');
$('#nmkec').val('');
$('#noskdp').val('');
$('#kddpjp').val('');
$('#nmdpdjp').val('');
$('#tujuankunjungan').val('');
$('#flagprosedur').val('');
$('#penunjang').val('');
$('#asesmenpelayanan').val('');
$('#kddpjplayanan').val('');
$('#nmdpjplayanan').val('');

        $("#typeact").val("add");
        $("#no_sep").prop('readonly', false);
        
        $('#modal-title').text("Tambah Data Bridging Sep Internal");
        $("#modal_bridging_sep_internal").modal('show');
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_bridging_sep_internal").click(function () {

        var search_field_bridging_sep_internal = $('#search_field_bridging_sep_internal').val();
        var search_text_bridging_sep_internal = $('#search_text_bridging_sep_internal').val();

        $.ajax({
            url: "{?=url(['bridging_sep_internal','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat', 
                search_field_bridging_sep_internal: search_field_bridging_sep_internal, 
                search_text_bridging_sep_internal: search_text_bridging_sep_internal
            },
            dataType: 'json',
            success: function (res) {
                var eTable = "<div class='table-responsive'><table id='tbl_lihat_bridging_sep_internal' class='table display dataTable' style='width:100%'><thead><th>No Sep</th><th>No Rawat</th><th>Tglsep</th><th>Tglrujukan</th><th>No Rujukan</th><th>Kdppkrujukan</th><th>Nmppkrujukan</th><th>Kdppkpelayanan</th><th>Nmppkpelayanan</th><th>Jnspelayanan</th><th>Catatan</th><th>Diagawal</th><th>Nmdiagnosaawal</th><th>Kdpolitujuan</th><th>Nmpolitujuan</th><th>Klsrawat</th><th>Klsnaik</th><th>Pembiayaan</th><th>Pjnaikkelas</th><th>Lakalantas</th><th>User</th><th>Nomr</th><th>Nama Pasien</th><th>Tanggal Lahir</th><th>Peserta</th><th>Jkel</th><th>No Kartu</th><th>Tglpulang</th><th>Asal Rujukan</th><th>Eksekutif</th><th>Cob</th><th>Notelep</th><th>Katarak</th><th>Tglkkl</th><th>Keterangankkl</th><th>Suplesi</th><th>No Sep Suplesi</th><th>Kdprop</th><th>Nmprop</th><th>Kdkab</th><th>Nmkab</th><th>Kdkec</th><th>Nmkec</th><th>Noskdp</th><th>Kddpjp</th><th>Nmdpdjp</th><th>Tujuankunjungan</th><th>Flagprosedur</th><th>Penunjang</th><th>Asesmenpelayanan</th><th>Kddpjplayanan</th><th>Nmdpjplayanan</th></thead>";
                for (var i = 0; i < res.length; i++) {
                    eTable += "<tr>";
                    eTable += '<td>' + res[i]['no_sep'] + '</td>';
eTable += '<td>' + res[i]['no_rawat'] + '</td>';
eTable += '<td>' + res[i]['tglsep'] + '</td>';
eTable += '<td>' + res[i]['tglrujukan'] + '</td>';
eTable += '<td>' + res[i]['no_rujukan'] + '</td>';
eTable += '<td>' + res[i]['kdppkrujukan'] + '</td>';
eTable += '<td>' + res[i]['nmppkrujukan'] + '</td>';
eTable += '<td>' + res[i]['kdppkpelayanan'] + '</td>';
eTable += '<td>' + res[i]['nmppkpelayanan'] + '</td>';
eTable += '<td>' + res[i]['jnspelayanan'] + '</td>';
eTable += '<td>' + res[i]['catatan'] + '</td>';
eTable += '<td>' + res[i]['diagawal'] + '</td>';
eTable += '<td>' + res[i]['nmdiagnosaawal'] + '</td>';
eTable += '<td>' + res[i]['kdpolitujuan'] + '</td>';
eTable += '<td>' + res[i]['nmpolitujuan'] + '</td>';
eTable += '<td>' + res[i]['klsrawat'] + '</td>';
eTable += '<td>' + res[i]['klsnaik'] + '</td>';
eTable += '<td>' + res[i]['pembiayaan'] + '</td>';
eTable += '<td>' + res[i]['pjnaikkelas'] + '</td>';
eTable += '<td>' + res[i]['lakalantas'] + '</td>';
eTable += '<td>' + res[i]['user'] + '</td>';
eTable += '<td>' + res[i]['nomr'] + '</td>';
eTable += '<td>' + res[i]['nama_pasien'] + '</td>';
eTable += '<td>' + res[i]['tanggal_lahir'] + '</td>';
eTable += '<td>' + res[i]['peserta'] + '</td>';
eTable += '<td>' + res[i]['jkel'] + '</td>';
eTable += '<td>' + res[i]['no_kartu'] + '</td>';
eTable += '<td>' + res[i]['tglpulang'] + '</td>';
eTable += '<td>' + res[i]['asal_rujukan'] + '</td>';
eTable += '<td>' + res[i]['eksekutif'] + '</td>';
eTable += '<td>' + res[i]['cob'] + '</td>';
eTable += '<td>' + res[i]['notelep'] + '</td>';
eTable += '<td>' + res[i]['katarak'] + '</td>';
eTable += '<td>' + res[i]['tglkkl'] + '</td>';
eTable += '<td>' + res[i]['keterangankkl'] + '</td>';
eTable += '<td>' + res[i]['suplesi'] + '</td>';
eTable += '<td>' + res[i]['no_sep_suplesi'] + '</td>';
eTable += '<td>' + res[i]['kdprop'] + '</td>';
eTable += '<td>' + res[i]['nmprop'] + '</td>';
eTable += '<td>' + res[i]['kdkab'] + '</td>';
eTable += '<td>' + res[i]['nmkab'] + '</td>';
eTable += '<td>' + res[i]['kdkec'] + '</td>';
eTable += '<td>' + res[i]['nmkec'] + '</td>';
eTable += '<td>' + res[i]['noskdp'] + '</td>';
eTable += '<td>' + res[i]['kddpjp'] + '</td>';
eTable += '<td>' + res[i]['nmdpdjp'] + '</td>';
eTable += '<td>' + res[i]['tujuankunjungan'] + '</td>';
eTable += '<td>' + res[i]['flagprosedur'] + '</td>';
eTable += '<td>' + res[i]['penunjang'] + '</td>';
eTable += '<td>' + res[i]['asesmenpelayanan'] + '</td>';
eTable += '<td>' + res[i]['kddpjplayanan'] + '</td>';
eTable += '<td>' + res[i]['nmdpjplayanan'] + '</td>';
                    eTable += "</tr>";
                }
                eTable += "</tbody></table></div>";
                $('#forTable_bridging_sep_internal').html(eTable);
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_bridging_sep_internal").modal('show');
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
        doc.text("Tabel Data Bridging Sep Internal", 20, 95, null, null, null);
        const totalPagesExp = "{total_pages_count_string}";        
        doc.autoTable({
            html: '#tbl_lihat_bridging_sep_internal',
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
        // doc.save('table_data_bridging_sep_internal.pdf');
        window.open(doc.output('bloburl'), '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
              
    })

    // ===========================================
    // Ketika tombol export xlsx di tekan
    // ===========================================
    $("#export_xlsx").click(function () {
        let tbl1 = document.getElementById("tbl_lihat_bridging_sep_internal");
        let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
        let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
        let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
        const new_workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data bridging_sep_internal");
        XLSX.writeFile(new_workbook, 'tmp_file.xls');
    })

    $("#view_chart").click(function () {
        window.open(mlite.url + '/bridging_sep_internal/chart?t=' + mlite.token, '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
    })   

});