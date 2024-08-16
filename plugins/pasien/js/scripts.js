jQuery().ready(function () {
    var var_tbl_pasien = $('#tbl_pasien').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'dom': 'Bfrtip',
        'searching': false,
        'select': true,
        'colReorder': true,
        "bInfo" : false,
        "ajax": {
            "url": "{?=url(['pasien','data'])?}",
            "dataType": "json",
            "type": "POST",
            "data": function (data) {

                // Read values
                var search_field_pasien = $('#search_field_pasien').val();
                var search_text_pasien = $('#search_text_pasien').val();
                
                data.search_field_pasien = search_field_pasien;
                data.search_text_pasien = search_text_pasien;

                var from_date = $('#tanggal_awal').val();
                var to_date = $('#tanggal_akhir').val();
                
                data.searchByFromdate = from_date;
                data.searchByTodate = to_date;

                
            }
        },
        "fnDrawCallback": function () {
            $('a[data-toggle="modal"]').on('click', function(e) {
                var target_modal = $(e.currentTarget).data('bs-target');
                var remote_content = $(e.currentTarget).attr('href');
              
                if(remote_content.indexOf('#') === 0) return;
              
                var modal = $(target_modal);
                var modalContent = $(target_modal + ' .modal-content');
                
                modal.off('show.bs.modal');
                modal.on('show.bs.modal', function () {
                    modalContent.load(remote_content);
                }).modal('show');
                    
                return false;
              });

              var loadURL =  mlite.url + '/pasien/cekpeserta/' + no_rkm_medis + '/noka?t=' + mlite.token;

            $('#more_data_pasien').on('click', function(e) {
                e.preventDefault();
                var clientX = e.originalEvent.clientX;
                var clientY = e.originalEvent.clientY;
                $('#tbl_pasien tr').contextMenu({x: clientX, y: clientY});
            });          
        },        
        "columns": [
            { 'data': 'no_rkm_medis' },            
            { 'data': 'nm_pasien' },
            { 'data': 'no_ktp' },
            { 'data': 'jk', 
                "render": function (data) {
                    if(data == 'L') {
                        var jk = 'Laki-Laki';
                    } else {
                        var jk = 'Perempuan';
                    }
                    return jk;
                }      
            },
            { 'data': 'tmp_lahir' },
            { 'data': 'tgl_lahir' },
            { 'data': 'nm_ibu' },
            { 'data': 'alamat' },
            { 'data': 'gol_darah' },
            { 'data': 'pekerjaan' },
            { 'data': 'stts_nikah' },
            { 'data': 'agama' },
            { 'data': 'tgl_daftar' },
            { 'data': 'no_tlp' },
            { 'data': 'umur' },
            { 'data': 'pnd' },
            { 'data': 'keluarga' },
            { 'data': 'namakeluarga' },
            { 'data': 'kd_pj' },
            { 'data': 'png_jawab' },
            { 'data': 'no_peserta' },
            { 'data': 'kd_kel' },
            { 'data': 'nm_kel' },
            { 'data': 'kd_kec' },
            { 'data': 'nm_kec' },
            { 'data': 'kd_kab' },
            { 'data': 'nm_kab' },
            { 'data': 'kd_prop' },
            { 'data': 'nm_prop' },
            { 'data': 'pekerjaanpj' },
            { 'data': 'alamatpj' },
            { 'data': 'kelurahanpj' },
            { 'data': 'kecamatanpj' },
            { 'data': 'kabupatenpj' },
            { 'data': 'propinsipj'},
            { 'data': 'perusahaan_pasien' },
            { 'data': 'nama_perusahaan' },
            { 'data': 'suku_bangsa' },
            { 'data': 'nama_suku_bangsa' },
            { 'data': 'bahasa_pasien' },
            { 'data': 'nama_bahasa' },
            { 'data': 'cacat_fisik' },
            { 'data': 'nama_cacat' },
            { 'data': 'email' },
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
            { 'targets': 44}
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
        selector: '#tbl_pasien tr', 
        trigger: 'right',
        callback: function(key, options) {
          var row = var_tbl_pasien.rows({ selected: true }).data()[0];
          var no_rkm_medis = row['no_rkm_medis'];
          switch (key) {
            case 'detail' :
              OpenModal(mlite.url + '/pasien/detail/' + no_rkm_medis + '?t=' + mlite.token);
              break;
            case 'cek_noka' :
                OpenModal(mlite.url + '/pasien/cekpeserta/' + no_rkm_medis + '/noka?t=' + mlite.token);
                break;
            case 'cek_nik' :
                OpenModal(mlite.url + '/pasien/cekpeserta/' + no_rkm_medis + '/nik?t=' + mlite.token);
                break;
            default :
              break
          } 
        },
        items: {
            "detail": {name: "View Detail", "icon": "edit"},
            "cek_noka": {name: "[BPJS] Cek Peserta ByNoKartu", icon: "delete", disabled:  {$disabled_menu.create}},
            "cek_nik": {name: "[BPJS] Cek Peserta ByNik", icon: "delete", disabled:  {$disabled_menu.create}},
        }
    });

    // ==============================================================
    // FORM VALIDASI
    // ==============================================================

    $("form[name='form_pasien']").validate({
        // ignore: [], 
        rules: {
            no_rkm_medis: 'required',
            nm_pasien: 'required',
            no_ktp: 'required',
            jk: 'required',
            tmp_lahir: 'required',
            tgl_lahir: 'required',
            nm_ibu: 'required',
            alamat: 'required',
            gol_darah: 'required',
            pekerjaan: 'required',
            stts_nikah: 'required',
            agama: 'required',
            tgl_daftar: 'required',
            no_tlp: 'required',
            umur: 'required',
            pnd: 'required',
            keluarga: 'required',
            namakeluarga: 'required',
            kd_pj: 'required',
            no_peserta: 'required',
            kd_kel: 'required',
            kd_kec: 'required',
            kd_kab: 'required',
            pekerjaanpj: 'required',
            alamatpj: 'required',
            kelurahanpj: 'required',
            kecamatanpj: 'required',
            kabupatenpj: 'required',
            perusahaan_pasien: 'required',
            suku_bangsa: 'required',
            bahasa_pasien: 'required',
            cacat_fisik: 'required',
            email: 'required',
            nip: 'required',
            kd_prop: 'required',
            propinsipj: 'required'
        },
        messages: {
            no_rkm_medis:'No Rkm Medis tidak boleh kosong!',
            nm_pasien:'Nm Pasien tidak boleh kosong!',
            no_ktp:'No Ktp tidak boleh kosong!',
            jk:'Jk tidak boleh kosong!',
            tmp_lahir:'Tmp Lahir tidak boleh kosong!',
            tgl_lahir:'Tgl Lahir tidak boleh kosong!',
            nm_ibu:'Nm Ibu tidak boleh kosong!',
            alamat:'Alamat tidak boleh kosong!',
            gol_darah:'Gol Darah tidak boleh kosong!',
            pekerjaan:'Pekerjaan tidak boleh kosong!',
            stts_nikah:'Stts Nikah tidak boleh kosong!',
            agama:'Agama tidak boleh kosong!',
            tgl_daftar:'Tgl Daftar tidak boleh kosong!',
            no_tlp:'No Tlp tidak boleh kosong!',
            umur:'Umur tidak boleh kosong!',
            pnd:'Pnd tidak boleh kosong!',
            keluarga:'Keluarga tidak boleh kosong!',
            namakeluarga:'Namakeluarga tidak boleh kosong!',
            kd_pj:'Kd Pj tidak boleh kosong!',
            no_peserta:'No Peserta tidak boleh kosong!',
            kd_kel:'Kd Kel tidak boleh kosong!',
            kd_kec:'Kd Kec tidak boleh kosong!',
            kd_kab:'Kd Kab tidak boleh kosong!',
            pekerjaanpj:'Pekerjaanpj tidak boleh kosong!',
            alamatpj:'Alamatpj tidak boleh kosong!',
            kelurahanpj:'Kelurahanpj tidak boleh kosong!',
            kecamatanpj:'Kecamatanpj tidak boleh kosong!',
            kabupatenpj:'Kabupatenpj tidak boleh kosong!',
            perusahaan_pasien:'Perusahaan Pasien tidak boleh kosong!',
            suku_bangsa:'Suku Bangsa tidak boleh kosong!',
            bahasa_pasien:'Bahasa Pasien tidak boleh kosong!',
            cacat_fisik:'Cacat Fisik tidak boleh kosong!',
            email:'Email tidak boleh kosong!',
            nip:'Nip tidak boleh kosong!',
            kd_prop:'Kd Prop tidak boleh kosong!',
            propinsipj:'Propinsipj tidak boleh kosong!'
        },
        submitHandler: function (form) {
            var no_rkm_medis= $('#no_rkm_medis').val();
            var nm_pasien= $('#nm_pasien').val();
            var no_ktp= $('#no_ktp').val();
            var jk= $('#jk').val();
            var tmp_lahir= $('#tmp_lahir').val();
            var tgl_lahir= $('#tgl_lahir').val();
            var nm_ibu= $('#nm_ibu').val();
            var alamat= $('#alamat').val();
            var gol_darah= $('#gol_darah').val();
            var pekerjaan= $('#pekerjaan').val();
            var stts_nikah= $('#stts_nikah').val();
            var agama= $('#agama').val();
            var tgl_daftar= $('#tgl_daftar').val();
            var no_tlp= $('#no_tlp').val();
            var umur= $('#umur').val();
            var pnd= $('#pnd').val();
            var keluarga= $('#keluarga').val();
            var namakeluarga= $('#namakeluarga').val();
            var kd_pj= $('#kd_pj').val();
            var no_peserta= $('#no_peserta').val();
            var kd_kel= $('#kd_kel').val();
            var kd_kec= $('#kd_kec').val();
            var kd_kab= $('#kd_kab').val();
            var pekerjaanpj= $('#pekerjaanpj').val();
            var alamatpj= $('#alamatpj').val();
            var kelurahanpj= $('#kelurahanpj').val();
            var kecamatanpj= $('#kecamatanpj').val();
            var kabupatenpj= $('#kabupatenpj').val();
            var perusahaan_pasien= $('#perusahaan_pasien').val();
            var suku_bangsa= $('#suku_bangsa').val();
            var bahasa_pasien= $('#bahasa_pasien').val();
            var cacat_fisik= $('#cacat_fisik').val();
            var email= $('#email').val();
            var nip= $('#nip').val();
            var kd_prop= $('#kd_prop').val();
            var nm_prop= $('#kd_prop').find(':selected').text();
            var nm_kab= $('#kd_kab').find(':selected').text();
            var nm_kec= $('#kd_kec').find(':selected').text();
            var nm_kel= $('#kd_kel').find(':selected').text();

            var propinsipj= $('#propinsipj').val();

            var typeact = $('#typeact').val();

            var formData = new FormData(form); // tambahan
            formData.append('typeact', typeact); // tambahan
            formData.append('nm_prop', nm_prop); // tambahan
            formData.append('nm_kab', nm_kab); // tambahan
            formData.append('nm_kec', nm_kec); // tambahan
            formData.append('nm_kel', nm_kel); // tambahan

            $.ajax({
                url: "{?=url(['pasien','aksi'])?}",
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
                            $("#modal_pasien").modal('hide');
                        } else {
                            bootbox.alert('<span class="text-danger">' + data.msg + '</span>');
                        }    
                    }
                    else if (typeact == "edit") {
                        if(data.status === 'success') {
                            bootbox.alert('<span class="text-success">' + data.msg + '</span>');
                            $("#modal_pasien").modal('hide');
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
                    var_tbl_pasien.draw();
                }
            })
        }
    });


    if(typeof ws != 'undefined' && typeof ws.readyState != 'undefined' && ws.readyState == 1){
        ws.onmessage = function(response){
            try{
                output = JSON.parse(response.data);
                if(output['action'] == 'add'){
                    var_tbl_pasien.draw();
                }
                if(output['action'] == 'edit'){
                    var_tbl_pasien.draw();
                }
                if(output['action'] == 'del'){
                    var_tbl_pasien.draw();
                }
            }catch(e){
                console.log(e);
            }
        }
    }

    // ==============================================================
    // KETIKA TOMBOL SEARCH DITEKAN
    // ==============================================================
    $('#filter_search_pasien').click(function () {
        var_tbl_pasien.draw();
    });

    // ===========================================
    // KETIKA TOMBOL EDIT DITEKAN
    // ===========================================

    $("#edit_data_pasien").click(function () {
        var rowData = var_tbl_pasien.rows({ selected: true }).data()[0];
        if (rowData != null) {

            var no_rkm_medis = rowData['no_rkm_medis'];
            var nm_pasien = rowData['nm_pasien'];
            var no_ktp = rowData['no_ktp'];
            var jk = rowData['jk'];
            var tmp_lahir = rowData['tmp_lahir'];
            var tgl_lahir = rowData['tgl_lahir'];
            var nm_ibu = rowData['nm_ibu'];
            var alamat = rowData['alamat'];
            var gol_darah = rowData['gol_darah'];
            var pekerjaan = rowData['pekerjaan'];
            var stts_nikah = rowData['stts_nikah'];
            var agama = rowData['agama'];
            var tgl_daftar = rowData['tgl_daftar'];
            var no_tlp = rowData['no_tlp'];
            var umur = rowData['umur'];
            var pnd = rowData['pnd'];
            var keluarga = rowData['keluarga'];
            var namakeluarga = rowData['namakeluarga'];
            var kd_pj = rowData['kd_pj'];
            var no_peserta = rowData['no_peserta'];
            var kd_kel = rowData['kd_kel'];
            var kd_kec = rowData['kd_kec'];
            var kd_kab = rowData['kd_kab'];
            var pekerjaanpj = rowData['pekerjaanpj'];
            var alamatpj = rowData['alamatpj'];
            var kelurahanpj = rowData['kelurahanpj'];
            var kecamatanpj = rowData['kecamatanpj'];
            var kabupatenpj = rowData['kabupatenpj'];
            var perusahaan_pasien = rowData['perusahaan_pasien'];
            var suku_bangsa = rowData['suku_bangsa'];
            var bahasa_pasien = rowData['bahasa_pasien'];
            var cacat_fisik = rowData['cacat_fisik'];
            var email = rowData['email'];
            var nip = rowData['nip'];
            var kd_prop = rowData['kd_prop'];
            var propinsipj = rowData['propinsipj'];

            $("#typeact").val("edit");
  
            $('#no_rkm_medis').val(no_rkm_medis);
            $('#nm_pasien').val(nm_pasien);
            $('#no_ktp').val(no_ktp);
            $('#jk').val(jk).change();
            $('#tmp_lahir').val(tmp_lahir);
            $('#tgl_lahir').val(tgl_lahir);
            $('#nm_ibu').val(nm_ibu);
            $('#alamat').val(alamat);
            $('#gol_darah').val(gol_darah).change();
            $('#pekerjaan').val(pekerjaan);
            $('#stts_nikah').val(stts_nikah).change();
            $('#agama').val(agama).change();
            $('#tgl_daftar').val(tgl_daftar);
            $('#no_tlp').val(no_tlp);
            $('#umur').val(umur);
            $('#pnd').val(pnd).change();
            $('#keluarga').val(keluarga).change();
            $('#namakeluarga').val(namakeluarga);
            $('#kd_pj').val(kd_pj).change();
            $('#no_peserta').val(no_peserta);
            $('#kd_kel').val(kd_kel).change();
            $('#kd_kec').val(kd_kec).change();
            $('#kd_kab').val(kd_kab).change();
            $('#pekerjaanpj').val(pekerjaanpj);
            $('#alamatpj').val(alamatpj);
            $('#kelurahanpj').val(kelurahanpj).change();
            $('#kecamatanpj').val(kecamatanpj).change();
            $('#kabupatenpj').val(kabupatenpj).change();
            $('#perusahaan_pasien').val(perusahaan_pasien).change();
            $('#suku_bangsa').val(suku_bangsa).change();
            $('#bahasa_pasien').val(bahasa_pasien).change();
            $('#cacat_fisik').val(cacat_fisik).change();
            $('#email').val(email);
            $('#nip').val(nip);
            $('#kd_prop').val(kd_prop).change();
            $('#propinsipj').val(propinsipj).change();
    
            if($('#kd_prop').selectator() !== undefined) {
                $('#kd_prop').selectator('destroy');
            }

            $.ajax({
                url: "https://basoro.id/api-wilayah-indonesia/api/provinces.json",
                method: "GET",
                data: {
                },
                success: function (data) {
                    var options = data.map(function(val, ind){
                        return $("<option></option>").val(val.id).html(val.name);
                    });
                    $('#kd_prop').append(options);
                    $('#kd_prop').selectator();
                }
            })
    
            $("#kd_prop").on("change", function() {
                if($('#kd_kab').selectator() !== undefined) {
                    $('#kd_kab').selectator('destroy');
                }
                var kd_prop = $('#kd_prop').find(':selected').val();
                $.ajax({
                    url: "https://basoro.id/api-wilayah-indonesia/api/regencies/" + kd_prop +".json",
                    method: "GET",
                    data: {
                    },
                    success: function (data) {
                        var options = data.map(function(val, ind){
                            return $("<option></option>").val(val.id).html(val.name);
                        });
                        $('#kd_kab').append(options);
                        $('#kd_kab').selectator();
                    }
                })
            });
    
            $("#kd_kab").on("change", function() {
                if($('#kd_kec').selectator() !== undefined) {
                    $('#kd_kec').selectator('destroy');
                }
                var kd_kab = $('#kd_kab').find(':selected').val();
                $.ajax({
                    url: "https://basoro.id/api-wilayah-indonesia/api/districts/" + kd_kab +".json",
                    method: "GET",
                    data: {
                    },
                    success: function (data) {
                        var options = data.map(function(val, ind){
                            return $("<option></option>").val(val.id).html(val.name);
                        });
                        $('#kd_kec').append(options);
                        $('#kd_kec').selectator();
                    }
                })
            });  
            
            $("#kd_kec").on("change", function() {
                if($('#kd_kel').selectator() !== undefined) {
                    $('#kd_kel').selectator('destroy');
                }
                var kd_kec = $('#kd_kec').find(':selected').val();
                $.ajax({
                    url: "https://basoro.id/api-wilayah-indonesia/api/villages/" + kd_kec +".json",
                    method: "GET",
                    data: {
                    },
                    success: function (data) {
                        var options = data.map(function(val, ind){
                            return $("<option></option>").val(val.id).html(val.name);
                        });
                        $('#kd_kel').append(options);
                        $('#kd_kel').selectator();
                    }
                })
            });            
    
            $("#no_rkm_medis").prop('readonly', true); // GA BISA DIEDIT KALI DISABLE
            $('#modal-title').text("Edit Data Pasien");
            $("#modal_pasien").modal('show');
                        
        }
        else {
            bootbox.alert("Silakan pilih data yang akan di edit.");
        }

    });

    // ==============================================================
    // TOMBOL  DELETE DI CLICK
    // ==============================================================
    jQuery("#hapus_data_pasien").click(function () {
        var rowData = var_tbl_pasien.rows({ selected: true }).data()[0];


        if (rowData) {
            var no_rkm_medis = rowData['no_rkm_medis'];
            bootbox.confirm('Anda yakin akan menghapus data dengan no_rkm_medis="' + no_rkm_medis, function(result) {
                if(result) {
                    $.ajax({
                        url: "{?=url(['pasien','aksi'])?}",
                        method: "POST",
                        data: {
                            no_rkm_medis: no_rkm_medis,
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
                            var_tbl_pasien.draw();
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
    jQuery("#tambah_data_pasien").click(function () {

        $.ajax({
            url: "{?=url(['pasien','getnorm'])?}",
            method: "POST",
            data: {
            },
            success: function (data) {
                $('#no_rkm_medis').val(data);
            }
        })    

        $('#nm_pasien').val('');
        $('#no_ktp').val('');
        $('#jk').val('').change();
        $('#tmp_lahir').val('');
        $('#tgl_lahir').val('');
        $('#nm_ibu').val('');
        $('#alamat').val('');
        $('#gol_darah').val('').change();
        $('#pekerjaan').val('');
        $('#stts_nikah').val('').change();
        $('#agama').val('').change();
        $('#no_tlp').val('');
        $('#umur').val('');
        $('#pnd').val('').change();
        $('#keluarga').val('').change();
        $('#namakeluarga').val('');
        $('#kd_pj').val('').change();
        $('#no_peserta').val('');
        $('#kd_kel').val('').change();
        $('#kd_kec').val('').change();
        $('#kd_kab').val('').change();

        if($('#kd_prop').selectator() !== undefined) {
            $('#kd_prop').selectator('destroy');
        }

        $.ajax({
            url: "https://basoro.id/api-wilayah-indonesia/api/provinces.json",
            method: "GET",
            data: {
            },
            success: function (data) {
                var options = data.map(function(val, ind){
                    return $("<option></option>").val(val.id).html(val.name);
                });
                $('#kd_prop').append(options);
                $('#kd_prop').selectator();
            }
        })

        $("#kd_prop").on("change", function() {
            if($('#kd_kab').selectator() !== undefined) {
                $('#kd_kab').selectator('destroy');
            }
            var kd_prop = $('#kd_prop').find(':selected').val();
            $.ajax({
                url: "https://basoro.id/api-wilayah-indonesia/api/regencies/" + kd_prop +".json",
                method: "GET",
                data: {
                },
                success: function (data) {
                    var options = data.map(function(val, ind){
                        return $("<option></option>").val(val.id).html(val.name);
                    });
                    $('#kd_kab').append(options);
                    $('#kd_kab').selectator();
                }
            })
        });

        $("#kd_kab").on("change", function() {
            if($('#kd_kec').selectator() !== undefined) {
                $('#kd_kec').selectator('destroy');
            }
            var kd_kab = $('#kd_kab').find(':selected').val();
            $.ajax({
                url: "https://basoro.id/api-wilayah-indonesia/api/districts/" + kd_kab +".json",
                method: "GET",
                data: {
                },
                success: function (data) {
                    var options = data.map(function(val, ind){
                        return $("<option></option>").val(val.id).html(val.name);
                    });
                    $('#kd_kec').append(options);
                    $('#kd_kec').selectator();
                }
            })
        });  
        
        $("#kd_kec").on("change", function() {
            if($('#kd_kel').selectator() !== undefined) {
                $('#kd_kel').selectator('destroy');
            }
            var kd_kec = $('#kd_kec').find(':selected').val();
            $.ajax({
                url: "https://basoro.id/api-wilayah-indonesia/api/villages/" + kd_kec +".json",
                method: "GET",
                data: {
                },
                success: function (data) {
                    var options = data.map(function(val, ind){
                        return $("<option></option>").val(val.id).html(val.name);
                    });
                    $('#kd_kel').append(options);
                    $('#kd_kel').selectator();
                }
            })
        });  

        $("#refresh_no_rkm_medis").on("click", function() {
            $.ajax({
                url: "{?=url(['pasien','getnorm'])?}",
                method: "POST",
                data: {
                },
                success: function (data) {
                    $('#no_rkm_medis').val(data);
                }
            })
        });

        $('#alamat').on('keyup', function() {
            $('#alamatpj').val($("#alamat").val());
        });        
        $('#pekerjaan').on('keyup', function() {
            $('#pekerjaanpj').val($("#pekerjaan").val());
        });        
        $('#kd_kel').on('change', function() {
            $('#kelurahanpj').val($("#kd_kel option:selected").text());
        });        
        $('#kd_kec').on('change', function() {
            $('#kecamatanpj').val($("#kd_kec option:selected").text());
        });        
        $('#kd_kab').on('change', function() {
            $('#kabupatenpj').val($("#kd_kab option:selected").text());
        }); 
        $('#kd_prop').on('change', function() {
            $('#propinsipj').val($("#kd_prop option:selected").text());
        });        
        $('#cari-nik-bpjs').on('click', function() {
            var no_ktp = $('#no_ktp').val();
            if(no_ktp == '') {
                bootbox.alert('Nomor KTP masih kosong!');  
            } else {
                $.ajax({
                    url: "{?=url()?}/pasien/cekpeserta/" + no_ktp + "/2?t=" + mlite.token,
                    method: "GET",
                    data: {},
                    success: function (data) {
                        console.log(data);
                        data = JSON.parse(data);
                        if(data.metaData.code !='200') {
                            bootbox.alert(data.metaData.message);                        
                        } else {
                            $('#nm_pasien').val(data.response.peserta.nama);
                            $('#no_peserta').val(data.response.peserta.noKartu);
                            var dateAr = data.response.peserta.tglLahir.split('-');
                            var newDate = dateAr[1] + '-' + dateAr[2] + '-' + dateAr[0];
                            $('#tgl_lahir').val(data.response.peserta.tglLahir);
                            $('#umur').val(umurDaftar(newDate));
                            $('#jk').val(data.response.peserta.sex).change();
                        }
                    }
                })    
            }
        });        

        $('#cari-noka-bpjs').on('click', function() {
            var noka = $('#no_peserta').val();
            if(noka == '') {
                bootbox.alert('Nomor Peserta masih kosong!');  
            } else {
                $.ajax({
                    url: "{?=url()?}/pasien/cekpeserta/" + noka + "/1?t=" + mlite.token,
                    method: "GET",
                    data: {},
                    success: function (data) {
                        // console.log(data);
                        data = JSON.parse(data);
                        if(data.metaData.code !='200') {
                            bootbox.alert(data.metaData.message);                        
                        } else {
                            $('#nm_pasien').val(data.response.peserta.nama);
                        }
                    }
                })
            }
        });        

        $('#perusahaan_pasien').val('').change();
        $('#suku_bangsa').val('').change();
        $('#bahasa_pasien').val('').change();
        $('#cacat_fisik').val('').change();
        $('#email').val('');
        $('#nip').val('');
        // $('#kd_prop').val('').change();

        $("#typeact").val("add");
        $("#no_rkm_medis").prop('disabled', false);
        
        $('#modal-title').text("Tambah Data Pasien");
        $("#modal_pasien").modal('show');
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_pasien").click(function () {

        var search_field_pasien = $('#search_field_pasien').val();
        var search_text_pasien = $('#search_text_pasien').val();

        var from_date = $('#tanggal_awal').val();
        var to_date = $('#tanggal_akhir').val();

        $.ajax({
            url: "{?=url(['pasien','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat', 
                search_field_pasien: search_field_pasien, 
                search_text_pasien: search_text_pasien, 
                searchByFromdate: from_date, 
                searchByTodate: to_date
            },
            dataType: 'json',
            success: function (res) {
                var eTable = "<div class='table-responsive'>";
                    eTable += "<table id='tbl_lihat_pasien' class='table display dataTable' style='width:100%'>";
                    eTable += "<thead>";
                    eTable += "<th>Nomor RM</th>";
                    eTable += "<th>Nama Pasien</th>";
                    eTable += "<th>Nomor KTP</th>";
                    eTable += "<th>Jenis Kelamin</th>";
                    eTable += "<th>Tanggal Lahir</th>";
                    eTable += "<th>Nama Ibu</th>";
                    eTable += "<th>Alamat</th>";
                    eTable += "<th>Nomor Telepon</th>";
                    eTable += "<th>Cara Bayar</th>";
                    eTable += "<th>No Peserta</th>";
                    eTable += "<th>Kelurahana</th>";
                    eTable += "<th>Kecamatan</th>";
                    eTable += "<th>Kabupaten</th>";
                    eTable += "<th>Propinsi</th>";
                    eTable += "</thead>";
                    for (var i = 0; i < res.length; i++) {
                        if(res[i]['jk'] == 'L') {
                            var jk = 'Laki-Laki';
                        } else {
                            var jk = 'Perempuan';
                        }
                        eTable += "<tr>";
                        eTable += '<td>' + res[i]['no_rkm_medis'] + '</td>';
                        eTable += '<td>' + res[i]['nm_pasien'] + '</td>';
                        eTable += '<td>' + res[i]['no_ktp'] + '</td>';
                        eTable += '<td>' + jk + '</td>';
                        eTable += '<td>' + res[i]['tgl_lahir'] + '</td>';
                        eTable += '<td>' + res[i]['nm_ibu'] + '</td>';
                        eTable += '<td>' + res[i]['alamat'] + '</td>';
                        eTable += '<td>' + res[i]['no_tlp'] + '</td>';
                        eTable += '<td>' + res[i]['png_jawab'] + '</td>';
                        eTable += '<td>' + res[i]['no_peserta'] + '</td>';
                        eTable += '<td>' + res[i]['nm_kel'] + '</td>';
                        eTable += '<td>' + res[i]['nm_kec'] + '</td>';
                        eTable += '<td>' + res[i]['nm_kab'] + '</td>';
                        eTable += '<td>' + res[i]['nm_prop'] + '</td>';
                        eTable += "</tr>";
                    }
                eTable += "</tbody></table></div>";
                $('#forTable_pasien').html(eTable);
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_pasien").modal('show');
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
        doc.line(20,72,572,72,null); /* doc.line(20,72,820,72,null); --> Jika landscape */
        doc.setFontSize(14);
        doc.text("Tabel Data Pasien", 20, 95, null, null, null);
        const totalPagesExp = "{total_pages_count_string}";        
        doc.autoTable({
            html: '#tbl_lihat_pasien',
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
                doc.text(`© ${new Date().getDate()}/${new Date().getMonth()}/${new Date().getFullYear()} by {$settings.nama_instansi}.`, data.settings.margin.left, doc.internal.pageSize.height - 10);                
                doc.text(footerStr, data.settings.margin.left + 728, doc.internal.pageSize.height - 10); /* doc.text(footerStr, data.settings.margin.left + 728, doc.internal.pageSize.height - 10); --> Jika landscape */
           }
        });
        if (typeof doc.putTotalPages === 'function') {
            doc.putTotalPages(totalPagesExp);
        }
        // doc.save('table_data_pasien.pdf')
        window.open(doc.output('bloburl'), '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
              
    })

    // ===========================================
    // Ketika tombol export xlsx di tekan
    // ===========================================
    $("#export_xlsx").click(function () {
        let tbl1 = document.getElementById("tbl_lihat_pasien");
        let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
        let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
        let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
        const new_workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data pasien");
        XLSX.writeFile(new_workbook, 'tmp_file.xls');
    })

    $("#view_chart").click(function () {
        window.open(mlite.url + '/pasien/chart?t=' + mlite.token, '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
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
        
    $('#tgl_lahir').on('apply.daterangepicker', function(ev){
        var tgl_lahir = $(ev.target).data('daterangepicker').startDate.format('DD/MM/YYYY');
        $('#umur').val(umurDaftar(tgl_lahir));
    });

});