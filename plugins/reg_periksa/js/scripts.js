jQuery().ready(function () {
    var var_tbl_reg_periksa = $('#tbl_reg_periksa').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'dom': 'Bfrtip',
        'searching': false,
        'select': true,
        'colReorder': true,
        "bInfo" : false,
        "language": {
            "lengthMenu": '_MENU_', 
            "info": 'Page _PAGE_ of _PAGES_', 
            "infoFiltered": 'from _MAX_ records'
        },         
        "ajax": {
            "url": "{?=url(['reg_periksa','data'])?}",
            "dataType": "json",
            "type": "POST",
            "data": function (data) {

                // Read values
                var search_field_reg_periksa = $('#search_field_reg_periksa').val();
                var search_text_reg_periksa = $('#search_text_reg_periksa').val();
                
                data.search_field_reg_periksa = search_field_reg_periksa;
                data.search_text_reg_periksa = search_text_reg_periksa;

                var from_date = $('#tanggal_awal').val();
                var to_date = $('#tanggal_akhir').val();
                
                data.searchByFromdate = from_date;
                data.searchByTodate = to_date;
                
            }
        },
        "fnDrawCallback": function () {
            $('#more_data_reg_periksa').on('click', function(e) {
                e.preventDefault();
                var clientX = e.originalEvent.clientX;
                var clientY = e.originalEvent.clientY;
                $('#tbl_reg_periksa tr').contextMenu({x: clientX, y: clientY});
            });          
        },
        "columns": [
            { 'data': 'no_reg' },
            { 'data': 'no_rawat' },
            { 'data': 'tgl_registrasi' },
            { 'data': 'jam_reg' },
            { 'data': 'kd_dokter' },
            { 'data': 'nm_dokter' },
            { 'data': 'no_rkm_medis' },
            { 'data': 'nm_pasien' },
            { 'data': 'no_ktp' },
            { 'data': 'no_peserta' },
            { 'data': 'kd_poli' },
            { 'data': 'nm_poli' },
            { 'data': 'p_jawab' },
            { 'data': 'almt_pj' },
            { 'data': 'hubunganpj' },
            { 'data': 'biaya_reg' },
            { 'data': 'stts' },
            { 'data': 'stts_daftar' },
            { 'data': 'status_lanjut' },
            { 'data': 'kd_pj' },
            { 'data': 'png_jawab' },
            { 'data': 'status_bayar' },
            { 'data': 'status_poli' }
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
            { 'targets': 22}
        ],
        order: [[1, 'DESC']], 
        buttons: [],
        "scrollCollapse": true,
        // "scrollY": '48vh', 
        // "pageLength":'25', 
        "lengthChange": true,
        // "info": true, 
        "scrollX": true,
        dom: "<'row'<'col-sm-12'tr>><<'pmd-datatable-pagination' l i p>>"
    });

    $.contextMenu({
        selector: '#tbl_reg_periksa tr', 
        trigger: 'right',
        callback: function(key, options) {
          var rowData = var_tbl_reg_periksa.rows({ selected: true }).data()[0];
          if (rowData != null) {
            var no_rkm_medis = rowData['no_rkm_medis'];
            var nama_pasien = rowData['nm_pasien'];
            var no_rawat = rowData['no_rawat'];
            var no_reg = rowData['no_reg'];
            var no_ktp = rowData['no_ktp'];
            var no_peserta = rowData['no_peserta'];
            var kd_pj = rowData['kd_pj'];
            switch (key) {
                case 'detail' :
                    OpenModal(mlite.url + '/reg_periksa/detail/' + no_rawat.replace(/\//g,'') + '?t=' + mlite.token);
                break;
                case 'antrian' :
                    OpenModal(mlite.url + '/reg_periksa/antrian/' + no_rawat.replace(/\//g,'') + '?t=' + mlite.token);
                break;
                case 'antrian-2' :
                    OpenPDF(mlite.url + '/reg_periksa/buktiregister/' + no_rawat.replace(/\//g,'') + '?t=' + mlite.token);
                break;
                case 'pemeriksaan_layanan' :
                    window.location.href = mlite.url + '/reg_periksa/view/' + no_rawat.replace(/\//g,'') + '?t=' + mlite.token;
                break;
                case 'periksa_lab' :
                    OpenModal(mlite.url + '/reg_periksa/periksalab/' + no_rawat.replace(/\//g,'') + '?t=' + mlite.token);
                break;
                case 'pasien_riwayat' :
                    OpenModal(mlite.url + '/pasien/riwayat/' + no_rkm_medis + '?t=' + mlite.token);
                break;
                case 'bridging-bpjs-cek-noka' :
                    OpenModal(mlite.url + '/pasien/cekpeserta/' + no_rkm_medis + '/noka?t=' + mlite.token);
                    break;
                case 'bridging-bpjs-cek-nik' :
                    OpenModal(mlite.url + '/pasien/cekpeserta/' + no_rkm_medis + '/nik?t=' + mlite.token);
                    break;
                case 'bridging-bpjs-cetak-sep' :
                    OpenPDF(mlite.url + '/reg_periksa/cetaksep/' + no_rawat.replace(/\//g,'') + '?t=' + mlite.token);
                break;
                case 'bridging-bpjs-sep' :
                    $('#no_rawat_sep').val(no_rawat); 
                    $('#no_reg_sep').val(no_reg); 
                    $('#nomr').val(no_rkm_medis); 
                    $('#nama_pasien').val(nama_pasien); 
                    $('#no_kartu').val(no_peserta); 
                    $('#no_ktp').val(no_ktp); 
                    $('#asal_rujukan').val('1').change(); 
                    $('#lakalantas').val('0').change(); 
                    $('#cob').val('0').change(); 
                    $('#eksekutif').val('0').change(); 
                    $('#katarak').val('0').change(); 
                    $("#modal_form_sep").modal('show');
                break;
                case 'bridging-bpjs-surat-kontrol' :
                    $('#no_rawat_booking').val(no_rawat); 
                    $('#no_rkm_medis_booking').val(no_rkm_medis); 
                    $('#kd_pj_booking').val(kd_pj).change(); 
                    $("#modal_form_surat_kontrol_bpjs").modal('show');
                break;
                case 'surat-kontrol' :
                    $('#no_rawat_booking').val(no_rawat); 
                    $('#no_rkm_medis_booking').val(no_rkm_medis); 
                    $('#kd_pj_booking').val(kd_pj).change(); 
                    $("#modal_form_surat_kontrol_bpjs").modal('show');
                break;
                default :
                break
            } 
          } else {
            bootbox.alert("Silakan pilih data atau klik baris data.");            
          }           
        },
        items: {
            "detail": {name: "View Detail", "icon": "edit"},
            "antrian": {name: "Cetak Nomor Antrian 1", "icon": "edit"},
            "antrian-2": {name: "Cetak Nomor Antrian 2", "icon": "edit"},
            "pemeriksaan_layanan": {name: "Pemeriksaan dan Layanan", "icon": "edit"},
            "periksa_lab": {name: "Periksa Laboratorium", "icon": "edit"},
            "surat-kontrol": {"name": "Surat Kontrol dan Booking", disabled:  {$disabled_menu.create}},
            "sep1": "---------",
            "bridging-bpjs": {
                "name": "Bridging BPJS", 
                "items": {
                    "bridging-bpjs-cek-noka": {name: "[BPJS] Cek Peserta ByNoKartu",disabled:  {$disabled_menu.create}},
                    "bridging-bpjs-cek-nik": {name: "[BPJS] Cek Peserta ByNik", disabled:  {$disabled_menu.create}},
                    "bridging-bpjs-surat-kontrol": {"name": "Surat Kontrol BPJS", disabled:  {$disabled_menu.create}},
                    "bridging-bpjs-referensi": {
                        "name": "Cek Referensi", 
                        "items": {
                            "bridging-bpjs-referensi-diagnosa": {"name": "Diagnosa"},
                            "bridging-bpjs-referensi-dokter": {"name": "Dokter"},
                            "bridging-bpjs-referensi-faskes": {"name": "Fasiltas Kesehatan"}
                        }
                    },
                    "bridging-bpjs-sep": {"name": "Pembuatan SEP", disabled:  {$disabled_menu.create}},
                    "bridging-bpjs-cetak-sep": {"name": "Cetak SEP", disabled:  {$disabled_menu.create}}
                }
            }, 
            "pasien_riwayat": {name: "Riwayat Perawatan", "icon": "edit"},
        }
    });

    // ==============================================================
    // FORM VALIDASI
    // ==============================================================

    $("form[name='form_reg_periksa']").validate({
        rules: {
            no_reg: 'required',
            no_rawat: 'required',
            tgl_registrasi: 'required',
            jam_reg: 'required',
            kd_dokter: 'required',
            no_rkm_medis: 'required',
            kd_poli: 'required',
            p_jawab: 'required',
            almt_pj: 'required',
            hubunganpj: 'required',
            biaya_reg: 'required',
            stts: 'required',
            stts_daftar: 'required',
            status_lanjut: 'required',
            kd_pj: 'required',
            status_bayar: 'required',
            status_poli: 'required'
        },
        messages: {
            no_reg:'no_reg tidak boleh kosong!',
            no_rawat:'no_rawat tidak boleh kosong!',
            tgl_registrasi:'tgl_registrasi tidak boleh kosong!',
            jam_reg:'jam_reg tidak boleh kosong!',
            kd_dokter:'kd_dokter tidak boleh kosong!',
            no_rkm_medis:'no_rkm_medis tidak boleh kosong!',
            kd_poli:'kd_poli tidak boleh kosong!',
            p_jawab:'p_jawab tidak boleh kosong!',
            almt_pj:'almt_pj tidak boleh kosong!',
            hubunganpj:'hubunganpj tidak boleh kosong!',
            biaya_reg:'biaya_reg tidak boleh kosong!',
            stts:'stts tidak boleh kosong!',
            stts_daftar:'stts_daftar tidak boleh kosong!',
            status_lanjut:'status_lanjut tidak boleh kosong!',
            kd_pj:'kd_pj tidak boleh kosong!',
            status_bayar:'status_bayar tidak boleh kosong!',
            status_poli:'status_poli tidak boleh kosong!'
        },
        submitHandler: function (form) {
            var no_reg= $('#no_reg').val();
            var no_rawat= $('#no_rawat').val();
            var tgl_registrasi= $('#tgl_registrasi').val();
            var jam_reg= $('#jam_reg').val();
            var kd_dokter= $('#kd_dokter').val();
            var no_rkm_medis= $('#no_rkm_medis').val();
            var kd_poli= $('#kd_poli').val();
            var p_jawab= $('#p_jawab').val();
            var almt_pj= $('#almt_pj').val();
            var hubunganpj= $('#hubunganpj').val();
            var biaya_reg= $('#biaya_reg').val();
            var stts= $('#stts').val();
            var stts_daftar= $('#stts_daftar').val();
            var status_lanjut= $('#status_lanjut').val();
            var kd_pj= $('#kd_pj').val();
            var status_bayar= $('#status_bayar').val();
            var status_poli= $('#status_poli').val();

            var typeact = $('#typeact').val();

            var formData = new FormData(form); // tambahan
            formData.append('typeact', typeact); // tambahan

            $.ajax({
                url: "{?=url(['reg_periksa','aksi'])?}",
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
                            $("#modal_reg_periksa").modal('hide');
                        } else {
                            bootbox.alert('<span class="text-danger">' + data.msg + '</span>');
                        }    
                    }
                    else if (typeact == "edit") {
                        if(data.status === 'success') {
                            bootbox.alert('<span class="text-success">' + data.msg + '</span>');
                            $("#modal_reg_periksa").modal('hide');
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
                    var_tbl_reg_periksa.draw();
                }
            })
        }
    });

    if(typeof ws != 'undefined' && typeof ws.readyState != 'undefined' && ws.readyState == 1){
        ws.onmessage = function(response){
            try{
                output = JSON.parse(response.data);
                if(output['action'] == 'add'){
                    var_tbl_reg_periksa.draw();
                }
                if(output['action'] == 'edit'){
                    var_tbl_reg_periksa.draw();
                }
                if(output['action'] == 'del'){
                    var_tbl_reg_periksa.draw();
                }
            }catch(e){
                console.log(e);
            }
        }
    }

    // ==============================================================
    // KETIKA MENGETIK DI INPUT SEARCH
    // ==============================================================
    $('#search_text_reg_periksa').keyup(function () {
        var_tbl_reg_periksa.draw();
    });

    $('#filter_search').click(function () {
        var_tbl_reg_periksa.draw();
    });

    $("#search_field_reg_periksa").on('change', function() {
        if ($(this).val() == 'kd_dokter'){
          $('#tempat_pilih').empty();
          $('#search_text_reg_periksa').remove();
          $('#tempat_pilih').append('<select class="form-select" name="search_text_reg_periksa" id="search_text_reg_periksa" style="text-align:left !important;width:260px !important;">' +
            '<option value="">Semua</option>' +
            {loop: $reg_periksa.dokter}
            '<option value="{$value.kd_dokter}">{$value.nm_dokter}</option>' +
            {/loop}
            '</select>');
          $('#search_text_reg_periksa').selectator();
          $("#search_text_reg_periksa").on('change', function() {
            // var_tbl_reg_periksa.draw();
          });  
        } else if ($(this).val() == 'kd_poli'){
            $('#tempat_pilih').empty();
            $('#search_text_reg_periksa').remove();
            $('#tempat_pilih').append('<select class="form-control" name="search_text_reg_periksa" id="search_text_reg_periksa" style="text-align:left !important;width:260px !important;">' +
              '<option value="">Semua</option>' +
              {loop: $reg_periksa.poliklinik}
              '<option value="{$value.kd_poli}">{$value.nm_poli}</option>' +
              {/loop}
              '</select>');
            $('#search_text_reg_periksa').selectator();
            $("#search_text_reg_periksa").on('change', function() {
            //   var_tbl_reg_periksa.draw();
            });    
        } else if ($(this).val() == 'kd_pj'){
            $('#tempat_pilih').empty();
            $('#search_text_reg_periksa').remove();
            $('#tempat_pilih').append('<select class="form-control" name="search_text_reg_periksa" id="search_text_reg_periksa" style="text-align:left !important;width:260px !important;">' +
              '<option value="">Semua</option>' +
              {loop: $reg_periksa.penjab}
              '<option value="{$value.kd_pj}">{$value.png_jawab}</option>' +
              {/loop}
              '</select>');
            $('#search_text_reg_periksa').selectator();
            $("#search_text_reg_periksa").on('change', function() {
            //   var_tbl_reg_periksa.draw();
            });    
        } else if ($(this).val() == 'stts'){
            $('#tempat_pilih').empty();
            $('#search_text_reg_periksa').remove();
            $('#tempat_pilih').append('<select class="form-control" name="search_text_reg_periksa" id="search_text_reg_periksa" style="text-align:left !important;width:260px !important;">' +
              '<option value="">Semua</option>' +
              {loop: $reg_periksa.stts}
              '<option value="{$value}">{$value}</option>' +
              {/loop}
              '</select>');
            $('#search_text_reg_periksa').selectator();
            $("#search_text_reg_periksa").on('change', function() {
            //   var_tbl_reg_periksa.draw();
            });    
        } else if ($(this).val() == 'stts_daftar'){
            $('#tempat_pilih').empty();
            $('#search_text_reg_periksa').remove();
            $('#tempat_pilih').append('<select class="form-control" name="search_text_reg_periksa" id="search_text_reg_periksa" style="text-align:left !important;width:260px !important;">' +
              '<option value="">Semua</option>' +
              {loop: $reg_periksa.stts_daftar}
              '<option value="{$value}">{$value}</option>' +
              {/loop}
              '</select>');
            $('#search_text_reg_periksa').selectator();
            $("#search_text_reg_periksa").on('change', function() {
            //   var_tbl_reg_periksa.draw();
            });    
        } else if ($(this).val() == 'status_lanjut'){
            $('#tempat_pilih').empty();
            $('#search_text_reg_periksa').remove();
            $('#tempat_pilih').append('<select class="form-control" name="search_text_reg_periksa" id="search_text_reg_periksa" style="text-align:left !important;width:260px !important;">' +
              '<option value="">Semua</option>' +
              {loop: $reg_periksa.status_lanjut}
              '<option value="{$value}">{$value}</option>' +
              {/loop}
              '</select>');
            $('#search_text_reg_periksa').selectator();
            $("#search_text_reg_periksa").on('change', function() {
            //   var_tbl_reg_periksa.draw();
            });    
        } else if ($(this).val() == 'status_bayar'){
            $('#tempat_pilih').empty();
            $('#search_text_reg_periksa').remove();
            $('#tempat_pilih').append('<select class="form-control" name="search_text_reg_periksa" id="search_text_reg_periksa" style="text-align:left !important;width:260px !important;">' +
              '<option value="">Semua</option>' +
              {loop: $reg_periksa.status_bayar}
              '<option value="{$value}">{$value}</option>' +
              {/loop}
              '</select>');
            $('#search_text_reg_periksa').selectator();
            $("#search_text_reg_periksa").on('change', function() {
            //   var_tbl_reg_periksa.draw();
            });   
        } else if ($(this).val() == 'status_poli'){
            $('#tempat_pilih').empty();
            $('#search_text_reg_periksa').remove();
            $('#tempat_pilih').append('<select class="form-control" name="search_text_reg_periksa" id="search_text_reg_periksa" style="text-align:left !important;width:260px !important;">' +
              '<option value="">Semua</option>' +
              {loop: $reg_periksa.status_poli}
              '<option value="{$value}">{$value}</option>' +
              {/loop}
              '</select>');
            $('#search_text_reg_periksa').selectator();
            $("#search_text_reg_periksa").on('change', function() {
            //   var_tbl_reg_periksa.draw();
            });   
        } else {
          $('#tempat_pilih').empty();
          $('#search_text_reg_periksa').remove();
          $('#tempat_pilih').append('<input class="form-control" name="search_text_reg_periksa" id="search_text_reg_periksa" type="search" placeholder="Masukkan Kata Kunci Pencarian" style="border-radius: 0 !important;padding: 8px;width:260px !important;" />');
          $('#search_text_reg_periksa').keyup(function () {
            // var_tbl_reg_periksa.draw();
          });
        }
    });     


    // ==============================================================
    // CLICK TANDA X DI INPUT SEARCH
    // ==============================================================
    $("#searchclear_reg_periksa").click(function () {
        $("#search_text_reg_periksa").val("");
        // var_tbl_reg_periksa.draw();
    });

    // ===========================================
    // Ketika tombol Edit di tekan
    // ===========================================

    $("#edit_data_reg_periksa").click(function () {
        var rowData = var_tbl_reg_periksa.rows({ selected: true }).data()[0];
        if (rowData != null) {

            var no_reg = rowData['no_reg'];
            var no_rawat = rowData['no_rawat'];
            var tgl_registrasi = rowData['tgl_registrasi'];
            var jam_reg = rowData['jam_reg'];
            var kd_dokter = rowData['kd_dokter'];
            var no_rkm_medis = rowData['no_rkm_medis'];
            var nm_pasien = rowData['nm_pasien'];
            var kd_poli = rowData['kd_poli'];
            var p_jawab = rowData['p_jawab'];
            var almt_pj = rowData['almt_pj'];
            var hubunganpj = rowData['hubunganpj'];
            var biaya_reg = rowData['biaya_reg'];
            var stts = rowData['stts'];
            var stts_daftar = rowData['stts_daftar'];
            var status_lanjut = rowData['status_lanjut'];
            var kd_pj = rowData['kd_pj'];
            var status_bayar = rowData['status_bayar'];
            var status_poli = rowData['status_poli'];

            $("#typeact").val("edit");
  
            $('#no_reg').val(no_reg);                
            $('#no_rawat').val(no_rawat);
            $('#tgl_registrasi').val(tgl_registrasi);
            $('#jam_reg').val(jam_reg);
            $('#kd_dokter').val(kd_dokter).change();
            $('#no_rkm_medis').val(no_rkm_medis).trigger('change');
            $('#nm_pasien').val(nm_pasien);
            $('#kd_poli').val(kd_poli).change();
            $('#p_jawab').val(p_jawab);
            $('#almt_pj').val(almt_pj);
            $('#hubunganpj').val(hubunganpj);
            $('#biaya_reg').val(biaya_reg);
            $('#stts').val(stts).change();
            $('#stts_daftar').val(stts_daftar).change();
            $('#status_lanjut').val(status_lanjut).change();
            $('#kd_pj').val(kd_pj).change();
            $('#status_bayar').val(status_bayar).change();
            $('#status_poli').val(status_poli).change();

            $("#nm_pasien").prop('readonly', true); // GA BISA DIEDIT KALI DISABLE
            $("#no_rawat").prop('readonly', true); // GA BISA DIEDIT KALI DISABLE
            $('#modal-title').text("Edit Data Pendaftaran");
            $("#modal_reg_periksa").modal('show');
        }
        else {
            bootbox.alert("Silakan pilih data yang akan di edit.");
        }

    });

    // ==============================================================
    // TOMBOL  DELETE DI CLICK
    // ==============================================================
    jQuery("#hapus_data_reg_periksa").click(function () {
        var rowData = var_tbl_reg_periksa.rows({ selected: true }).data()[0];


        if (rowData) {
            var no_rawat = rowData['no_rawat'];
            bootbox.confirm('Anda yakin akan menghapus data dengan no_reg="' + no_rawat, function(result) {
                if(result) {
                    $.ajax({
                        url: "{?=url(['reg_periksa','aksi'])?}",
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
                            var_tbl_reg_periksa.draw();
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
    jQuery("#tambah_data_reg_periksa").click(function () {

        $('#nm_pasien').val('').prop('disabled', false);
        $('#no_rawat').val('').prop('disabled', false);
        $('#kd_dokter').val('').change();
        $('#kd_poli').val('').change();
        $('#no_reg').val('');


        $('#no_reg').val('{$reg_periksa.no_reg}');
        $('#no_rawat').val('{$reg_periksa.no_rawat}');
        $('#kd_dokter').val('');
        $('#no_rkm_medis').val('');
        $('#kd_poli').val('');
        $('#p_jawab').val('');
        $('#almt_pj').val('');
        $('#hubunganpj').val('');
        $('#biaya_reg').val('');
        $('#stts').val('Belum').change();
        $('#stts_daftar').val('').change();
        $('#status_lanjut').val('Ralan').change();
        $('#kd_pj').val('').change();
        $('#status_bayar').val('Belum Bayar').change();
        $('#status_poli').val('').change();

        $("#refresh_no_rawat").on("click", function() {
            $.ajax({
                url: "{?=url(['reg_periksa','getnorawat'])?}",
                method: "POST",
                data: {
                },
                success: function (data) {
                    $('#no_rawat').val(data);
                }
            })
        });

        $("#refresh_no_reg").on("click", function() {
            var kd_poli = $('#kd_poli').val();
            var kd_dokter = $('#kd_dokter').val();
            $.ajax({
                url: "{?=url(['reg_periksa','getnoreg'])?}",
                method: "POST",
                data: {
                    kd_poli: kd_poli, 
                    kd_dokter: kd_dokter
                },
                success: function (data) {
                    $('#no_reg').val(data);
                }
            })
        });

        $("#typeact").val("add");
        $("#no_reg").prop('disabled', false);
        
        $('#modal-title').text("Tambah Data Pendaftaran");
        $("#modal_reg_periksa").modal('show');
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_reg_periksa").click(function () {

        var search_field_reg_periksa = $('#search_field_reg_periksa').val();
        var search_text_reg_periksa = $('#search_text_reg_periksa').val();

        var from_date = $('#tanggal_awal').val();
        var to_date = $('#tanggal_akhir').val();

        $.ajax({
            url: "{?=url(['reg_periksa','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat', 
                search_field_reg_periksa: search_field_reg_periksa, 
                search_text_reg_periksa: search_text_reg_periksa, 
                searchByFromdate: from_date, 
                searchByTodate: to_date
            },
            dataType: 'json',
            success: function (res) {
                var eTable = "<div class='table-responsive'><table id='tbl_lihat_reg_periksa' class='table display dataTable' style='width:100%'><thead><th>No Reg</th><th>No Rawat</th><th>Tgl Registrasi</th><th>Jam Reg</th><th>Kd Dokter</th><th>No Rkm Medis</th><th>Kd Poli</th><th>P Jawab</th><th>Almt Pj</th><th>Hubunganpj</th><th>Biaya Reg</th><th>Stts</th><th>Stts Daftar</th><th>Status Lanjut</th><th>Kd Pj</th><th>Status Bayar</th><th>Status Poli</th></thead>";
                for (var i = 0; i < res.length; i++) {
                    eTable += "<tr>";
                    eTable += '<td>' + res[i]['no_reg'] + '</td>';
                    eTable += '<td>' + res[i]['no_rawat'] + '</td>';
                    eTable += '<td>' + res[i]['tgl_registrasi'] + '</td>';
                    eTable += '<td>' + res[i]['jam_reg'] + '</td>';
                    eTable += '<td>' + res[i]['kd_dokter'] + '</td>';
                    eTable += '<td>' + res[i]['no_rkm_medis'] + '</td>';
                    eTable += '<td>' + res[i]['kd_poli'] + '</td>';
                    eTable += '<td>' + res[i]['p_jawab'] + '</td>';
                    eTable += '<td>' + res[i]['almt_pj'] + '</td>';
                    eTable += '<td>' + res[i]['hubunganpj'] + '</td>';
                    eTable += '<td>' + res[i]['biaya_reg'] + '</td>';
                    eTable += '<td>' + res[i]['stts'] + '</td>';
                    eTable += '<td>' + res[i]['stts_daftar'] + '</td>';
                    eTable += '<td>' + res[i]['status_lanjut'] + '</td>';
                    eTable += '<td>' + res[i]['kd_pj'] + '</td>';
                    eTable += '<td>' + res[i]['status_bayar'] + '</td>';
                    eTable += '<td>' + res[i]['status_poli'] + '</td>';
                    eTable += "</tr>";
                }
                eTable += "</tbody></table></div>";
                $('#forTable_reg_periksa').html(eTable);
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_reg_periksa").modal('show');
    });

    // ==============================================================
    // TOMBOL DETAIL reg_periksa DI CLICK
    // ==============================================================
    jQuery("#lihat_detail_reg_periksa").click(function (event) {

        var rowData = var_tbl_reg_periksa.rows({ selected: true }).data()[0];

        if (rowData) {
            var no_rawat = rowData['no_rawat'];
            event.preventDefault();
            var loadURL =  mlite.url + '/reg_periksa/detail/' + no_rawat.replace(/\//g,'') + '?t=' + mlite.token;
        
            var modal = $('#modal_detail_reg_periksa');
            var modalContent = $('#modal_detail_reg_periksa .modal-content');
        
            modal.off('show.bs.modal');
            modal.on('show.bs.modal', function () {
                modalContent.load(loadURL);
            }).modal('show');
            return false;
        
        }
        else {
            bootbox.alert("Pilih satu baris untuk detail");
        }
    });

    // ==============================================================
    // TOMBOL DETAIL reg_periksa DI CLICK
    // ==============================================================
    jQuery("#lihat_detail_reg_periksa_antrian").click(function (event) {

        var rowData = var_tbl_reg_periksa.rows({ selected: true }).data()[0];

        if (rowData) {
            var no_rawat = rowData['no_rawat'];
            event.preventDefault();
            var loadURL =  mlite.url + '/reg_periksa/antrian/' + no_rawat.replace(/\//g,'') + '?t=' + mlite.token;
        
            var modal = $('#modal_detail_reg_periksa');
            var modalContent = $('#modal_detail_reg_periksa .modal-content');
        
            modal.off('show.bs.modal');
            modal.on('show.bs.modal', function () {
                modalContent.load(loadURL);
            }).modal('show');
            return false;
        
        }
        else {
            bootbox.alert("Pilih satu baris untuk detail");
        }
    });
        
    // ==============================================================
    // TOMBOL DETAIL reg_periksa DI CLICK
    // ==============================================================
    jQuery("#lihat_detail_reg_periksa_antrol").click(function (event) {

        var rowData = var_tbl_reg_periksa.rows({ selected: true }).data()[0];

        if (rowData) {
            var no_rawat = rowData['no_rawat'];
            event.preventDefault();
            var loadURL =  mlite.url + '/reg_periksa/antrol/' + no_rawat.replace(/\//g,'') + '?t=' + mlite.token;
        
            var modal = $('#modal_detail_reg_periksa');
            var modalContent = $('#modal_detail_reg_periksa .modal-content');
        
            modal.off('show.bs.modal');
            modal.on('show.bs.modal', function () {
                modalContent.load(loadURL);
            }).modal('show');
            return false;
        
        }
        else {
            bootbox.alert("Pilih satu baris untuk detail");
        }
    });

    // ==============================================================
    // TOMBOL DETAIL reg_periksa DI CLICK
    // ==============================================================
    jQuery("#lihat_detail_reg_periksa_sep").click(function (event) {

        var rowData = var_tbl_reg_periksa.rows({ selected: true }).data()[0];

        if (rowData) {
            var no_rawat = rowData['no_rawat'];
            event.preventDefault();
            var loadURL =  mlite.url + '/reg_periksa/sep/' + no_rawat.replace(/\//g,'') + '?t=' + mlite.token;
        
            var modal = $('#modal_detail_reg_periksa');
            var modalContent = $('#modal_detail_reg_periksa .modal-content');
        
            modal.off('show.bs.modal');
            modal.on('show.bs.modal', function () {
                modalContent.load(loadURL);
            }).modal('show');
            return false;
        
        }
        else {
            bootbox.alert("Pilih satu baris untuk detail");
        }
    });

    // ==============================================================
    // TOMBOL DETAIL reg_periksa DI CLICK
    // ==============================================================
    jQuery("#lihat_detail_reg_periksa_pemeriksaan").click(function (event) {

        var rowData = var_tbl_reg_periksa.rows({ selected: true }).data()[0];

        if (rowData) {
            var no_rawat = rowData['no_rawat'];
            event.preventDefault();
            window.location.href = mlite.url + '/reg_periksa/view/' + no_rawat.replace(/\//g,'') + '?t=' + mlite.token;;
            return false;
        
        }
        else {
            bootbox.alert("Pilih satu baris untuk detail");
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
        doc.text("Tabel Data Reg Periksa", 20, 95, null, null, null);
        const totalPagesExp = "{total_pages_count_string}";        
        doc.autoTable({
            html: '#tbl_lihat_reg_periksa',
            startY: 105,
            margin: {
                left: 20, 
                right: 20
            }, 
            styles: {
                fontSize: 8,
                cellPadding: 2
            }, 
            didDrawPage: data => {
                let footerStr = "Page " + doc.internal.getNumberOfPages();
                if (typeof doc.putTotalPages === 'function') {
                footerStr = footerStr + " of " + totalPagesExp;
                }
                doc.setFontSize(10);
                doc.text(`© ${new Date().getFullYear()} {$settings.nama_instansi}.`, data.settings.margin.left, doc.internal.pageSize.height - 10);                
                doc.text(footerStr, data.settings.margin.left + 728, doc.internal.pageSize.height - 10); /* doc.text(footerStr, data.settings.margin.left + 728, doc.internal.pageSize.height - 10); --> Jika landscape */
           }
        });
        if (typeof doc.putTotalPages === 'function') {
            doc.putTotalPages(totalPagesExp);
        }
        // doc.save('table_data_reg_periksa.pdf')
        window.open(doc.output('bloburl'), '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
              
    })

    // ===========================================
    // Ketika tombol export xlsx di tekan
    // ===========================================
    $("#export_xlsx").click(function () {
        let tbl1 = document.getElementById("tbl_lihat_reg_periksa");
        let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
        let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
        let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
        const new_workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data reg_periksa");
        XLSX.writeFile(new_workbook, 'tmp_file.xls');
    })

    $("#view_chart").click(function () {
        window.open(mlite.url + '/reg_periksa/chart?t=' + mlite.token, '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
    })   

    $(".datepicker").daterangepicker({
        singleDatePicker: true,
        showDropdowns: true,
        locale: {
            format: "YYYY-MM-DD",
        },
    });

    $('.daterange').daterangepicker({
        opens: 'left'
    }, function(start, end, label) {
        $('#tanggal_awal').val(start.format('YYYY-MM-DD'));
        $('#tanggal_akhir').val(end.format('YYYY-MM-DD'));
    });

    $(".timepicker").daterangepicker({
            timePicker : true,
            singleDatePicker:true,
            timePicker24Hour : true,
            timePickerIncrement : 1,
            timePickerSeconds : true,
            startDate: moment().format('HH:mm:ss'),
            // endDate: moment().startOf('hour').add(32, 'hour'),            
            locale : {
                format : 'HH:mm:ss'
            }
        }).on('show.daterangepicker', function(ev, picker){
            picker.container.find(".calendar-table").hide()
    });

    $("#nm_pasien").click(function (event) {
        // $(".modal-content").modal("show");
        event.preventDefault();
        var loadURL =  mlite.url + '/reg_periksa/managepasien?t=' + mlite.token;;
    
        var modal = $('#modal_detail_reg_periksa_pasien');
        var modalContent = $('#modal_detail_reg_periksa_pasien .modal-content');
        // alert(modal);
    
        modal.off('show.bs.modal');
        modal.on('show.bs.modal', function () {
            modalContent.load(loadURL);
        }).modal('show');
        
        return false;

    })

    $("#modal_detail_reg_periksa_pasien").on('shown.bs.modal', function () {
        var var_tbl_pasien =  $('#tbl_pasien').DataTable({
          'processing': true,
          'serverSide': true,
          'serverMethod': 'post',
          'dom': 'Bfrtip',
          'searching': false,
          'select': true,
          'colReorder': true,
          "bInfo" : false,
          "ajax": {
              "url": "{?=url(['reg_periksa','datapasien'])?}",
              "dataType": "json",
              "type": "POST",
              "data": function (data) {
  
                  // Read values
                  var search_field_pasien = $('#search_field_pasien').val();
                  var search_text_pasien = $('#search_text_pasien').val();
                  
                  data.search_field_pasien = search_field_pasien;
                  data.search_text_pasien = search_text_pasien;
                  
              }
          },
          "fnDrawCallback": function () {
            $('.selectator').selectator();
          },                  
          "columns": [
              { 'data': 'no_rkm_medis' },
              { 'data': 'nm_pasien' },
              { 'data': 'no_ktp' },
              { 'data': 'jk' },
              { 'data': 'tmp_lahir' },
              { 'data': 'tgl_lahir' },
              { 'data': 'nm_ibu' },
              { 'data': 'alamat' },
              { 'data': 'gol_darah' },
              { 'data': 'pekerjaan' },
              { 'data': 'stts_nikah' },
              { 'data': 'agama' },
              { 'data': 'no_tlp' },
              { 'data': 'pnd' },
              { 'data': 'keluarga' },
              { 'data': 'namakeluarga' },
              { 'data': 'kd_pj' },
              { 'data': 'no_peserta' },
              { 'data': 'kd_kel' },
              { 'data': 'kd_kec' },
              { 'data': 'kd_kab' },
  
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
              { 'targets': 20}
  
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

        $('#search_text_pasien').keyup(function () {
            var_tbl_pasien.draw();
        });

        $('#tbl_pasien').on( 'select.dt', function ( e, dt, type, indexes ) {
            var rowData = var_tbl_pasien.rows({ selected: true }).data()[0];
            $('#no_rkm_medis').val(rowData['no_rkm_medis']);
            $('#nm_pasien').val(rowData['nm_pasien']);
            $('#p_jawab').val(rowData['namakeluarga']);
            $('#almt_pj').val(rowData['alamatpj']);
            $('#hubunganpj').val(rowData['keluarga']);
            $('#kd_pj').val(rowData['kd_pj']).change();

            var kd_poli = $('#kd_poli').val();
            var kd_dokter = $('#kd_dokter').val();

            $.ajax({
                url: "{?=url(['reg_periksa','getnorawat'])?}",
                method: "POST",
                data: {
                },
                success: function (data) {
                    $('#no_rawat').val(data);
                }
            })
            
            $.ajax({
                url: "{?=url(['reg_periksa','getnoreg'])?}",
                method: "POST",
                data: {
                    kd_poli: kd_poli, 
                    kd_dokter: kd_dokter
                },
                success: function (data) {
                    $('#no_reg').val(data);
                }
            })    

            $.ajax({
                url: "{?=url(['reg_periksa','getsttsdaftar'])?}",
                method: "POST",
                data: {
                    no_rkm_medis: rowData['no_rkm_medis']
                },
                success: function (data) {
                    $('#stts_daftar').val(data).change();
                }
            })    

            $.ajax({
                url: "{?=url(['reg_periksa','getstatuspoli'])?}",
                method: "POST",
                data: {
                    no_rkm_medis: rowData['no_rkm_medis'], 
                    kd_poli: $('#kd_poli').val()
                },
                success: function (data) {
                    $('#status_poli').val(data).change();
                }
            })    
            
            $("#modal_detail_reg_periksa_pasien").modal('hide');
        } );  
    
    });   

    var var_tbl_reg_periksa_riwayat = $('#tbl_pemeriksaan_riwayat').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'dom': 'Bfrtip',
        'searching': false,
        'select': true,
        'colReorder': true,
        "bInfo" : false,
        "ajax": { 
            "url": "{?=url(['reg_periksa','datariwayat'])?}",
            "dataType": "json",
            "type": "POST",
            "data": function (data) {

                data.no_rawat = $("a.active").attr('data-no_rawat')
            }
        },
        "fnDrawCallback": function () {
        }, 
        "columns": [
            {
                'className': 'dt-control',
                'orderable': false,
                'data': null,
                'defaultContent': ''
            },            
            { 'data': 'no_rawat' },
            { 'data': 'tgl_registrasi' },
            { 'data': 'jam_reg' },
            { 'data': 'kd_dokter' },
            { 'data': 'no_rkm_medis' },
            { 'data': 'kd_poli' },
            { 'data': 'p_jawab' },
            { 'data': 'almt_pj' },
            { 'data': 'hubunganpj' },
            { 'data': 'biaya_reg' },
            { 'data': 'stts' },
            { 'data': 'stts_daftar' },
            { 'data': 'status_lanjut' },
            { 'data': 'kd_pj' },
            { 'data': 'status_bayar' },
            { 'data': 'status_poli' }
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

    function format(d) {
        // console.log(d);
        $('.pan').pan();
        var riwayat=''; //just a variable to construct

        if(d.pemeriksaan_ralan !='') {
            riwayat += 'Pemeriksaan Rawat Jalan';
            riwayat += '<table class="table table-bordered table-striped table-hover">'+
                   '<thead>'+
                      '<th>Keluhan</th>'+
                      '<th>Pemeriksaan</th>'+  
                      '<th>Rtl</th>'+  
                   '</thead><tbody>';
            $.each($(d.pemeriksaan_ralan),function(key,value){
                riwayat+='<tr><td>'+value.keluhan+'</td><td>'+value.pemeriksaan+'</td><td>'+value.rtl+'</td></tr>';
            })
            riwayat += '</tbody></table>';    
        }

        if(d.rawat_jl_dr !='') {
            riwayat += 'Tindakan Dokter Rawat Jalan';
            riwayat += '<table class="table table-bordered table-striped table-hover">'+
                   '<thead>'+
                      '<th>Tanggal Perawatan</th>'+  
                      '<th>Kode Perawatan</th>'+
                      '<th>Biaya Rawat</th>'+  
                   '</thead><tbody>';
            $.each($(d.rawat_jl_dr),function(key,value){
                riwayat+='<tr><td>'+value.tgl_perawatan+'</td><td>'+value.kd_jenis_prw+'</td><td>'+value.biaya_rawat+'</td></tr>';
            })
            riwayat += '</tbody></table>';    
        }

        if(d.rawat_jl_pr !='') {
            riwayat += 'Tindakan Perawat Rawat Jalan';
            riwayat += '<table class="table table-bordered table-striped table-hover">'+
                   '<thead>'+
                      '<th>Tanggal Perawatan</th>'+  
                      '<th>Kode Perawatan</th>'+
                      '<th>Biaya Rawat</th>'+  
                   '</thead><tbody>';
            $.each($(d.rawat_jl_pr),function(key,value){
                riwayat+='<tr><td>'+value.tgl_perawatan+'</td><td>'+value.kd_jenis_prw+'</td><td>'+value.biaya_rawat+'</td></tr>';
            })
            riwayat += '</tbody></table>';    
        }

        if(d.rawat_jl_drpr !='') {
            riwayat += 'Tindakan Dokter & Perawat Rawat Jalan';
            riwayat += '<table class="table table-bordered table-striped table-hover">'+
                   '<thead>'+
                      '<th>Tanggal Perawatan</th>'+  
                      '<th>Kode Perawatan</th>'+
                      '<th>Biaya Rawat</th>'+  
                   '</thead><tbody>';
            $.each($(d.rawat_jl_drpr),function(key,value){
                riwayat+='<tr><td>'+value.tgl_perawatan+'</td><td>'+value.kd_jenis_prw+'</td><td>'+value.biaya_rawat+'</td></tr>';
            })
            riwayat += '</tbody></table>';    
        }

        if(d.resep_dokter !='') {
            riwayat += 'Resep Dokter';
            riwayat += '<table class="table table-bordered table-striped table-hover">'+
                   '<thead>'+
                      '<th>Tanggal Peresepan</th>'+  
                      '<th>Jam Peresepan</th>'+
                      '<th>Kode Barang</th>'+  
                   '</thead><tbody>';
            $.each($(d.resep_dokter),function(key,value){
                riwayat+='<tr><td>'+value.tgl_peresepan+'</td><td>'+value.jam_peresepan+'</td><td>'+value.kode_brng+'</td></tr>';
            })
            riwayat += '</tbody></table>';    
        }

        if(d.resep_dokter_racikan !='') {
            riwayat += 'Resep Dokter Racikan';
            riwayat += '<table class="table table-bordered table-striped table-hover">'+
                   '<thead><tr>'+
                      '<th>Tanggal Peresepan</th>'+  
                      '<th>Jam Peresepan</th>'+
                      '<th>Nama Racik</th>'+  
                      '<th>Jumlah</th>'+  
                      '<th>Aturan Pakai</th>'+  
                      '<th>Keterangan</th>'+  
                   '</tr></thead><tbody>';
            $.each($(d.resep_dokter_racikan),function(key,value){
                riwayat+='<tr><td>'+value.tgl_peresepan+'</td><td>'+value.jam_peresepan+'</td><td>'+value.nama_racik+'</td><td>'+value.jml_dr+'</td><td>'+value.aturan_pakai+'</td><td>'+value.keterangan+'</td></tr>';
                riwayat+='<tr><th>Nama Barang</th><th>Kode Barang</th><th>P1</th><th>P2</th><th>Kandungan</th><th>Jumlah</th></tr>';
                $.each($(value.resep_dokter_racikan_detail),function(key,value){
                    riwayat+='<tr><td>'+value.kode_brng+'</td><td>'+value.kode_brng+'</td><td>'+value.p1+'</td><td>'+value.p2+'</td><td>'+value.kandungan+'</td><td>'+value.jml+'</td></tr>';
                })
            })
            riwayat += '</tbody></table>';    
        }

        if(d.berkas_digital_perawatan !='') {
            riwayat += 'Berkas Digital Perawatan';
            riwayat += '<table class="table table-border table-hover">'+
                   '<thead>'+
                      '<th>Nomor Rawat</th>'+
                      '<th>Nama Berkas</th>'+  
                      '<th>Gambar</th>'+  
                   '</thead><tbody>';
            $.each($(d.berkas_digital_perawatan),function(key,value){
                riwayat+='<tr><td>'+value.no_rawat+'</td><td>'+value.kode+'</td><td><a href="#" data-big="'+mlite.url+'/'+value.lokasi_file+'" class="pan"><img src="'+mlite.url+'/'+value.lokasi_file+'" height="100"></a></td></tr>';
            })
            riwayat += '</tbody></table>';    

        }
        return riwayat;
    }

    // State handling for restoring child rows
    var_tbl_reg_periksa_riwayat.on('requestChild', function (e, row) {
        row.child(format(row.data())).show();
    });
    
    // Add event listener for opening and closing details
    var_tbl_reg_periksa_riwayat.on('click', 'tbody td.dt-control', function (e) {
        let tr = e.target.closest('tr');
        let row = var_tbl_reg_periksa_riwayat.row(tr);
        
        if (row.child.isShown()) {
            // This row is already open - close it
            row.child.hide();
        }
        else {
            $.ajax({
                // url: "{?=url(['reg_periksa','getriwayat'])?}",
                url: mlite.url + '/reg_periksa/getriwayat/' + row.data().no_rawat.replace(/\//g,'') + '?t=' + mlite.token, 
                method: "GET",
                data: {
                    // no_rawat: row.data().no_rawat
                },
                success: function (data) {
                    // console.log(data);
                    // riwayat = JSON.parse(data);
                    row.child(format(data)).show();
                    $('.pan').pan();
                }
            })
        }
    });    

    $('#riwayatTabs').click('show', function(e) {  
        paneID = $(e.target).attr('href');
        src = $(paneID).attr('data-src');
        // if the iframe hasn't already been loaded once
        if($(paneID+" iframe").attr("src")=="")
        {
            $(paneID+" iframe").attr("src",src);
        }
    });

    $('#filter_search_riwayat').click(function () {
        var_tbl_reg_periksa_riwayat.draw();
    });

    if (typeof $.fn.slimScroll != 'undefined') {
        var height = ($(window).height() - 330);
        var $el = $('.list-group');
    
        $el.slimscroll({
            height: height + "px",
            color: 'rgba(0,0,0,0.5)',
            size: '4px',
            alwaysVisible: false,
            borderRadius: '0',
            railBorderRadius: '0'
        });
    
        //Scroll active menu item when page load, if option set = true
        var item = $('.list-group-scroll .list-group a.active')[0];
        if (item) {
            var activeItemOffsetTop = item.offsetTop;
            if (activeItemOffsetTop > 50) $el.slimscroll({ scrollTo: activeItemOffsetTop + 'px' });
        }
    }

    // if (typeof $.fn.slimScroll != 'undefined') {
    //     var height = ($(window).height() - 280);
    //     var $el = $('.list-pemeriksaan');
    
    //     $el.slimscroll({
    //         height: height + "px",
    //         color: 'rgba(0,0,0,0.5)',
    //         size: '4px',
    //         alwaysVisible: false,
    //         borderRadius: '0',
    //         railBorderRadius: '0'
    //     });
    
    //     //Scroll active menu item when page load, if option set = true
    //     var item = $('.pemeriksaan-group-scroll .list-group a.active')[0];
    //     if (item) {
    //         var activeItemOffsetTop = item.offsetTop;
    //         if (activeItemOffsetTop > 50) $el.slimscroll({ scrollTo: activeItemOffsetTop + 'px' });
    //     }
    // }    

    $("#filter-pasien").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $("#list-pasien a").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });    

    $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function (event) {
        $('#tbl_pemeriksaan_ralan').DataTable()
          .columns.adjust()
          .responsive.recalc();
        $('#tbl_rawat_jl_dr').DataTable()
          .columns.adjust()
          .responsive.recalc();
        $('#tbl_rawat_jl_pr').DataTable()
          .columns.adjust()
          .responsive.recalc();
        $('#tbl_rawat_jl_drpr').DataTable()
          .columns.adjust()
          .responsive.recalc();
        $('#tbl_resep_dokter').DataTable()
          .columns.adjust()
          .responsive.recalc();
        $('#tbl_resep_dokter_racikan').DataTable()
          .columns.adjust()
          .responsive.recalc();
        $('#tbl_permintaan_lab').DataTable()
          .columns.adjust()
          .responsive.recalc();
        $('#tbl_permintaan_radiologi').DataTable()
          .columns.adjust()
          .responsive.recalc();

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

        $("#no_rawat_pemeriksaan_ralan").val($("a.active").attr('data-no_rawat'));
        $("#no_rawat_rawat_jl_dr").val($("a.active").attr('data-no_rawat'));
        $("#no_rawat_rawat_jl_pr").val($("a.active").attr('data-no_rawat'));
        $("#no_rawat_rawat_jl_drpr").val($("a.active").attr('data-no_rawat'));
        $("#no_rawat_resep_dokter").val($("a.active").attr('data-no_rawat'));
        $("#no_rawat_resep_dokter_racikan").val($("a.active").attr('data-no_rawat'));
        $("#no_rawat_permintaan_lab").val($("a.active").attr('data-no_rawat'));
        $("#no_rawat_permintaan_radiologi").val($("a.active").attr('data-no_rawat'));

    });

    $('#cari_rujukan').on('click', function() {
        var searchBy = $('#asal_rujukan').find(':selected').val();
        console.log(searchBy);
        var keyword = $('#no_kartu').val();
        console.log(keyword);
        $.ajax({
            url: "{?=url(['bridging_sep','cekrujukan'])?}",
            method: "POST",
            data: {
                searchBy: searchBy, 
                keyword: keyword, 
                multi: 'true'
            },
            success: function (data) {
                var data = JSON.parse(data);
                console.log(data);
                if(data.metaData.code == '200') {
                    var data = data.response.rujukan;                    
                } else {
                    var data = [];
                }
                let table = '<table id="tbl_cari_rujukan" class="table table-stripped" width="100%"><thead>';
                    table += '<tr>';
                    table += '<th>Tanggal</th>';
                    table += '<th>Nomor rujukan</th>';
                    table += '<th>Nama Pasien</th>';
                    table += '<th>Tangal Lahir</th>';
                    table += '<th>Jenis Kelamin</th>';
                    table += '<th>Nomor Telepon</th>';
                    table += '<th>Kode PPK Rujukan</th>';
                    table += '<th>Nama PPK Rujukan</th>';
                    table += '<th>Kode Pelayanan</th>';
                    table += '<th>Jenis Pelayanan</th>';
                    table += '<th>Kode Diagnosa</th>';
                    table += '<th>Nama Diagnosa</th>';
                    table += '<th>Kode Poli Tujuan</th>';
                    table += '<th>Nama Poli Tujuan</th>';
                    table += '<th>Kode Kelas</th>';
                    table += '<th>Nama Kelas</th>';
                    table += '<th>Peserta</th>';
                    table += '</tr>';
                    table += '</thead><tbody>';
                data.forEach(function(d){
                    table += '<tr>';
                    table += '<td>'+d.tglKunjungan+'</td>';
                    table += '<td>'+d.noKunjungan+'</td>';
                    table += '<td>'+d.peserta.nama+'</td>';
                    table += '<td>'+d.peserta.tglLahir+'</td>';
                    table += '<td>'+d.peserta.sex+'</td>';
                    table += '<td>'+d.peserta.mr.noTelepon+'</td>';
                    table += '<td>'+d.provPerujuk.kode+'</td>';
                    table += '<td>'+d.provPerujuk.nama+'</td>';
                    table += '<td>'+d.pelayanan.kode+'</td>';
                    table += '<td>'+d.pelayanan.nama+'</td>';
                    table += '<td>'+d.diagnosa.kode+'</td>';
                    table += '<td>'+d.diagnosa.nama+'</td>';
                    table += '<td>'+d.poliRujukan.kode+'</td>';
                    table += '<td>'+d.poliRujukan.nama+'</td>';
                    table += '<td>'+d.peserta.hakKelas.kode+'</td>';
                    table += '<td>'+d.peserta.hakKelas.keterangan+'</td>';
                    table += '<td>'+d.peserta.jenisPeserta.keterangan+'</td>';
                    table += '</tr>';
                })
                table += '</tbody></table>';
                // setTimeout(function(){
                    $('#forTable_rujukan').empty().html(table);
                // },1000); 

                var var_tbl_cari_rujukan = $('#tbl_cari_rujukan').DataTable({
                    'select': true
                });
                $('#tbl_cari_rujukan').on('select.dt', function ( e, dt, type, indexes ) {
                    var rowData = var_tbl_cari_rujukan.rows({ selected: true }).data()[0];
                    console.log(rowData);
                    $('#tglrujukan').val(rowData['0']);
                    $('#no_rujukan').val(rowData['1']);
                    $('#noskdp').val(rowData['1']);
                    $('#nama_pasien').val(rowData['2']);
                    $('#tanggal_lahir').val(rowData['3']);
                    $('#jkel').val(rowData['4']).change();
                    $('#notelep').val(rowData['5']);
                    $('#kdppkrujukan').val(rowData['6']);
                    $('#nmppkrujukan').val(rowData['7']);
                    $('#jnspelayanan').val(rowData['8']).change();
                    // $('#nmjnspelayanan').val(rowData['9']);
                    $('#diagawal').val(rowData['10']);
                    $('#nmdiagnosaawal').val(rowData['11']);
                    $('#kdpolitujuan').val(rowData['12']).change();
                    $('#nmpolitujuan').val(rowData['13']);
                    $('#klsrawat').val(rowData['14']).change();
                    // $('#namaklsrawat').val(rowData['15']);
                    $('#peserta').val(rowData['16']);   

                    $("#modal_cari_rujukan").modal('hide');
                });
        
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_cari_rujukan").modal('show');
    });

    $('#cari_surat_kontrol').on('click', function() {
        // var searchBy = $('#asal_rujukan').find(':selected').val();
        // console.log(searchBy);
        var nokartu = $('#no_kartu').val();
        $.ajax({
            url: "{?=url(['bridging_sep','rencanakontrol'])?}",
            method: "POST",
            data: {
                bulan: '{?=date("m")?}', 
                tahun: '{?=date("Y")?}',
                nokartu: nokartu, 
                filter: '2'
            },
            success: function (data) {
                var data = JSON.parse(data);
                console.log(data);
                if(data.metaData.code == '200') {
                    var data = data.response.list;                    
                } else {
                    var data = [];
                }
                let table = '<table id="tbl_cari_surat_kontrol" class="table table-stripped" width="100%"><thead>';
                    table += '<tr>';
                    table += '<th>Tanggal Kontrol</th>';
                    table += '<th>Tanggal Surat Kontol</th>';
                    table += '<th>Nomor Surat Kontrol</th>';
                    table += '<th>Nama Pasien</th>';
                    table += '<th>Nama Dokter</th>';
                    table += '<th>Jenis Kontrol</th>';
                    table += '<th>Jenis Pelayanan</th>';
                    table += '<th>Nama Jenis Kontrol</th>';
                    table += '<th>Nama Poli Asal</th>';
                    table += '<th>Nama Poli Tujuan</th>';
                    table += '<th>Nomor Kartu</th>';
                    table += '<th>Nomor SEP Asal Kontrol</th>';
                    table += '<th>Tanggal SEP</th>';
                    table += '<th>Poli Asal</th>';
                    table += '<th>Poli Tujuan</th>';
                    table += '<th>Terbit SEP</th>';
                    table += '</tr>';
                    table += '</thead><tbody>';
                data.forEach(function(d){
                    table += '<tr>';
                    table += '<td>'+d.tglRencanaKontrol+'</td>';
                    table += '<td>'+d.tglTerbitKontrol+'</td>';
                    table += '<td>'+d.noSuratKontrol+'</td>';
                    table += '<td>'+d.nama+'</td>';
                    table += '<td>'+d.namaDokter+'</td>';
                    table += '<td>'+d.jnsKontrol+'</td>';
                    table += '<td>'+d.jnsPelayanan+'</td>';
                    table += '<td>'+d.namaJnsKontrol+'</td>';
                    table += '<td>'+d.namaPoliAsal+'</td>';
                    table += '<td>'+d.namaPoliTujuan+'</td>';
                    table += '<td>'+d.noKartu+'</td>';
                    table += '<td>'+d.noSepAsalKontrol+'</td>';
                    table += '<td>'+d.tglSEP+'</td>';
                    table += '<td>'+d.poliAsal+'</td>';
                    table += '<td>'+d.poliTujuan+'</td>';
                    table += '<td>'+d.terbitSEP+'</td>';
                    table += '</tr>';
                })
                table += '</tbody></table>';
                // setTimeout(function(){
                    $('#forTable_surat_kontrol').empty().html(table);
                // },1000); 

                var var_tbl_cari_surat_kontrol = $('#tbl_cari_surat_kontrol').DataTable({
                    'select': true
                });
                $('#tbl_cari_surat_kontrol').on('select.dt', function ( e, dt, type, indexes ) {
                    var rowData = var_tbl_cari_surat_kontrol.rows({ selected: true }).data()[0];
                    console.log(rowData);
                    $('#noskdp').val(rowData['2']);
                    $("#modal_cari_surat_kontrol").modal('hide');
                });
        
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_cari_surat_kontrol").modal('show');
    });

    $(".datepicker").daterangepicker({
        singleDatePicker: true,
        showDropdowns: true,
        locale: {
            format: "YYYY-MM-DD",
        },
    });

    $('.kecelakaan').hide(); 
    $('#lakalantas').change(function(){
        if($('#lakalantas').val() == '1') {
            $('.kecelakaan').show(); 
        } else {
            $('.kecelakaan').hide(); 
        } 
    });

    $('.naikkelas').hide(); 
    $('#klsnaik').change(function(){
        $('.naikkelas').show(); 
    });

    $('.tujuankunjungan').hide(); 
    $('#tujuankunjungan').change(function(){
        if($('#tujuankunjungan').val() == '1') {
            $('.tujuankunjungan').show(); 
        } else {
            $('.tujuankunjungan').hide(); 
        } 
    });

    $('.tujuankunjungan2').hide(); 
    $('#tujuankunjungan').change(function(){
        if($('#tujuankunjungan').val() !== '1') {
            $('.tujuankunjungan2').show(); 
        } else {
            $('.tujuankunjungan2').hide(); 
        } 
    });

    $('#antrol_data_bridging_sep').on('click', function() {
        if($('#kddpjp').val() == '') {
            alert('Dokter masih kosong');
        }
        if($('#asal_rujukan').find(':selected').val() == '1'  && $('#noskdp').val() == $('#no_rujukan').val() && $('#kddpjp').find(':selected').val() == $('#kddpjplayanan').find(':selected').val()) {
            alert('Rujukan FKTP');
            var jenis_kunjungan = '1';
        } else if ($('#asal_rujukan').find(':selected').val() == '2') {
            alert('Rujukan RS');
            var jenis_kunjungan = '4';
        } else if ($('#asal_rujukan').find(':selected').val() == '1' && $('#noskdp').val() != $('#no_rujukan').val()) {
            alert('Kontrol');
            var jenis_kunjungan = '3';
        } else if ($('#kddpjp').find(':selected').val() != $('#kddpjplayanan').find(':selected').val()) {
            alert('Rujukan internal');
            var jenis_kunjungan = '2';
        } else {
            bootbox.alert('Syarat antrian tidak terpenuhi. Cek lagi asal rujukan, nomor rujukan dan nomor surat kontrol!');
        }

        var tanggal_periksa= '{?=date("Y-m-d")?}';
        var no_rkm_medis= $('#nomr').val();
        var nomor_kartu= $('#no_kartu').val();
        var nomor_referensi= $('#no_rujukan').val();
        if(jenis_kunjungan == '3') {
            var nomor_referensi= $('#noskdp').val();
        }
        var ppk_bpjs = $('#kdppkpelayanan').val();
        var no_rawat = $('#no_rawat_sep').val();
        var no_rawat_convert = no_rawat.replace(/\//g,'');
        var kd_poli_bpjs = $('#kdpolitujuan').find(':selected').val();
        var no_reg = $('#no_reg_sep').val();
        var kodebooking= ppk_bpjs+no_rawat_convert+kd_poli_bpjs+no_reg;

        var jenispasien = 'JKN';
        var nik = $('#no_ktp').val();
        var nohp = $('#notelep').val();
        var kodepoli = $('#kdpolitujuan').find(':selected').val();
        var namapoli = $('#kdpolitujuan').find(':selected').text();
        var pasienbaru = '0';
        var kodedokter = $('#kddpjplayanan').find(':selected').val();
        var namadokter = $('#kddpjplayanan').find(':selected').text();
        var jampraktek = '';
        var nomorantrean = kd_poli_bpjs+'-'+no_reg;
        var estimasidilayani = '';
        var sisakuotajkn = '';
        var kuotajkn = '';
        var sisakuotanonjkn = '';
        var kuotanonjkn = '';
        
        $.ajax({
            url: "{?=url(['bridging_sep','addantrian'])?}",
            method: "POST",
            data: {
                kodebooking: kodebooking, 
                jenispasien: jenispasien, 
                nomorkartu: nomor_kartu, 
                nik: nik, 
                nohp: nohp, 
                kodepoli: kodepoli, 
                namapoli: namapoli, 
                pasienbaru: pasienbaru, 
                norm: no_rkm_medis, 
                tanggalperiksa: tanggal_periksa, 
                kodedokter: kodedokter, 
                namadokter: namadokter, 
                jampraktek: jampraktek, 
                jeniskunjungan: jenis_kunjungan, 
                nomorreferensi: nomor_referensi, 
                nomorantrean: nomorantrean, 
                angkaantrean: no_reg, 
                estimasidilayani: estimasidilayani, 
                sisakuotajkn: sisakuotajkn, 
                kuotajkn: kuotajkn, 
                sisakuotanonjkn: sisakuotanonjkn, 
                kuotanonjkn: kuotanonjkn
            },
            success: function (data) {
                console.log(data);
                data = JSON.parse(data);
                if(data.metadata.code == '200') {
                    var status_kirim = 'Sudah';
                    var keterangan = data.metadata.message;
                    
                    var typeact = 'add';
                    
                    $.ajax({
                        url: "{?=url(['mlite_antrian_referensi','aksi'])?}",
                        method: "POST",
                        data: {
                            tanggal_periksa: tanggal_periksa, 
                            no_rkm_medis: no_rkm_medis, 
                            nomor_kartu: nomor_kartu, 
                            nomor_referensi: nomor_referensi, 
                            kodebooking: kodebooking, 
                            jenis_kunjungan: jenis_kunjungan, 
                            status_kirim: status_kirim, 
                            keterangan: keterangan, 
                            typeact: typeact
                        },
                        success: function (data) {
                            console.log(data);
                            data = JSON.parse(data);
                            var audio = new Audio('{?=url()?}/assets/sound/' + data.status + '.mp3');
                            audio.play();
                            if (typeact == "add") {
                                if(data.status === 'success') {
                                    bootbox.alert('<span class="text-success">' + data.msg + '</span>');
                                    $("#modal_bridging_sep").modal('hide');
                                } else {
                                    bootbox.alert('<span class="text-danger">' + data.msg + '</span>');
                                }    
                            }
                        }
                    })
                } else {
                    var message = data.metadata.message;
                    if(data.metadata.message == 'Waktu estimasi dilayani tidak valid'){
                       var message = data.metadata.message + '. Sesuaikan jadwal SIMRS dengan HFIS, atau cek maping dokter.';
                    }
                    bootbox.alert('<span class="text-danger">' + message + '</span>');
                    var audio = new Audio('{?=url()?}/assets/sound/error.mp3');
                    audio.play();
                }
            }
        }) 

    });
    
    $('#aproval_data_bridging_sep').on('click', function() {
        $('#no_kartu_aproval').val($('#no_kartu').val());
        $('#modal_form_aproval_sep').modal('show');
        $('#simpan_data_aproval_sep').on('click', function() {
            var no_kartu_aproval = $('#no_kartu_aproval').val();
            var tgl_sep_aproval = $('#tgl_sep_aproval').val();
            var jns_pelayanan_aproval = $('#jns_pelayanan_aproval').find(':selected').val();
            var jns_pengajuan_aproval = $('#jns_pengajuan_aproval').find(':selected').val();
            var keterangan_aproval = $('#keterangan_aproval').val();
            $.ajax({
                url: "{?=url(['bridging_sep','pengajuanaprovalsep'])?}",
                method: "POST",
                data: {
                    no_kartu_aproval: no_kartu_aproval, 
                    tgl_sep_aproval: tgl_sep_aproval, 
                    jns_pelayanan_aproval: jns_pelayanan_aproval, 
                    jns_pengajuan_aproval: jns_pengajuan_aproval, 
                    keterangan_aproval: keterangan_aproval
                },
                success: function (data) {
                    data = JSON.parse(data);
                    if(data.metaData.code == '200') {
                        $.ajax({
                            url: "{?=url(['bridging_sep','aprovalsep'])?}",
                            method: "POST",
                            data: {
                                no_kartu_aproval: no_kartu_aproval, 
                                tgl_sep_aproval: tgl_sep_aproval, 
                                jns_pelayanan_aproval: jns_pelayanan_aproval, 
                                jns_pengajuan_aproval: jns_pengajuan_aproval, 
                                keterangan_aproval: keterangan_aproval
                            },
                            success: function (data) {
                                data = JSON.parse(data);
                                var audio = new Audio('{?=url()?}/assets/sound/success.mp3');
                                audio.play();
                                bootbox.alert('<span class="text-success">' + data.metaData.message + '</span>');
                                $('#modal_form_aproval_sep').modal('hide');
                            }
                        })
                    } else {
                        var audio = new Audio('{?=url()?}/assets/sound/error.mp3');
                        audio.play();
                        bootbox.alert('<span class="text-danger">' + data.metaData.message + '</span>');
                    }
                }
            });
        });        
    });

    $("form[name='form_bridging_sep']").validate({
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
            // catatan:'Catatan tidak boleh kosong!',
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
            var nmpolitujuan= $('#kdpolitujuan').find(':selected').text();
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
            var nolp= $('#nolp').val();
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
            var nmdpdjp= $('#kddpjp').find(':selected').text();
            var tujuankunjungan= $('#tujuankunjungan').val();
            var flagprosedur= $('#flagprosedur').val();
            var penunjang= $('#penunjang').val();
            var asesmenpelayanan= $('#asesmenpelayanan').val();
            var kddpjplayanan= $('#kddpjplayanan').val();
            var nmdpjplayanan= $('#kddpjplayanan').find(':selected').text();

            var typeact = 'add';

            var formData = new FormData(form); // tambahan
            formData.append('typeact', typeact); // tambahan
            formData.append('nmpolitujuan', nmpolitujuan); // tambahan
            formData.append('nmdpdjp', nmdpdjp); // tambahan
            formData.append('nmdpjplayanan', nmdpjplayanan); // tambahan
            console.log(JSON.stringify(Object.fromEntries(formData)));

            $.ajax({
                url: "{?=url(['bridging_sep','aksi'])?}",
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
                            $("#modal_bridging_sep").modal('hide');
                        } else {
                            bootbox.alert('<span class="text-danger">' + JSON.stringify(data.msg) + '</span>');
                        }    
                    }
                }
            })
        }
    });

    $('#kddpjp').on('change', function(){
        $('#kddpjplayanan').val($('#kddpjp').val()).change();
    })

    if(typeof ws != 'undefined' && typeof ws.readyState != 'undefined' && ws.readyState == 1){
        ws.onmessage = function(response){
            try{
                output = JSON.parse(response.data);
                if(output['action'] == 'add'){
                    var_tbl_bridging_sep.draw();
                }
                if(output['action'] == 'edit'){
                    var_tbl_bridging_sep.draw();
                }
                if(output['action'] == 'del'){
                    var_tbl_bridging_sep.draw();
                }
            }catch(e){
                console.log(e);
            }
        }
    }

    $("form[name='form_booking_registrasi']").validate({
        rules: {
            tanggal_booking: 'required',
            jam_booking: 'required',
            no_rkm_medis: 'required',
            tanggal_periksa: 'required',
            kd_dokter: 'required',
            kd_poli: 'required',
            no_reg: 'required',
            kd_pj: 'required',
            limit_reg: 'required',
            waktu_kunjungan: 'required',
            status: 'required'

        },
        messages: {
            tanggal_booking:'Tanggal Booking tidak boleh kosong!',
            jam_booking:'Jam Booking tidak boleh kosong!',
            no_rkm_medis:'No Rkm Medis tidak boleh kosong!',
            tanggal_periksa:'Tanggal Periksa tidak boleh kosong!',
            kd_dokter:'Kd Dokter tidak boleh kosong!',
            kd_poli:'Kd Poli tidak boleh kosong!',
            no_reg:'No Reg tidak boleh kosong!',
            kd_pj:'Kd Pj tidak boleh kosong!',
            limit_reg:'Limit Reg tidak boleh kosong!',
            waktu_kunjungan:'Waktu Kunjungan tidak boleh kosong!',
            status:'Status tidak boleh kosong!'

        },
        submitHandler: function (form) {
            var tanggal_booking= $('#tanggal_booking').val();
            var jam_booking= $('#jam_booking').val();
            var no_rkm_medis= $('#no_rkm_medis').val();
            var tanggal_periksa= $('#tanggal_periksa').val();
            var kd_dokter= $('#kd_dokter').val();
            var kd_poli= $('#kd_poli').val();
            var no_reg= $('#no_reg').val();
            var kd_pj= $('#kd_pj').val();
            var limit_reg= $('#limit_reg').val('0');
            var waktu_kunjungan= tanggal_periksa + ' ' + jam_booking;
            var status= $('#status').val('Belum');

            var typeact = 'add';

            var formData = new FormData(form); // tambahan
            formData.append('typeact', typeact); // tambahan
            formData.append('limit_reg', limit_reg); // tambahan
            formData.append('waktu_kunjungan', waktu_kunjungan); // tambahan
            formData.append('status', status); // tambahan

            $.ajax({
                url: "{?=url(['booking_registrasi','aksi'])?}",
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
                            $("#modal_booking_registrasi").modal('hide');
                        } else {
                            bootbox.alert('<span class="text-danger">' + data.msg + '</span>');
                        }    
                    }
                }
            })
        }
    });

});