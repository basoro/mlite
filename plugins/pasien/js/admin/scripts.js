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
            "url": "{?=url([ADMIN,'pasien','data'])?}",
            "dataType": "json",
            "type": "POST",
            "data": function (data) {

                // Read values
                var search_field_pasien = $('#search_field_pasien').val();
                var search_text_pasien = $('#search_text_pasien').val();
                
                data.search_field_pasien = search_field_pasien;
                data.search_text_pasien = search_text_pasien;

                // Kirim filter tanggal daftar
                data.tgl_dari = $('#tgl_dari').val();
                data.tgl_sampai = $('#tgl_sampai').val();                
                
            }
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
            { 'data': 'tgl_daftar' },
            { 'data': 'no_tlp' },
            { 'data': 'umur' },
            { 'data': 'pnd' },
            { 'data': 'keluarga' },
            { 'data': 'namakeluarga' },
            { 'data': 'kd_pj' },
            { "data": 'nama_penjab' },
            { 'data': 'no_peserta' },
            { 'data': 'kd_kel' },
            { "data": 'nama_kelurahan' },
            { 'data': 'kd_kec' },
            { "data": 'nama_kecamatan' },
            { 'data': 'kd_kab' },
            { "data": 'nama_kabupaten' },
            { 'data': 'pekerjaanpj' },
            { 'data': 'alamatpj' },
            { 'data': 'kelurahanpj' },
            { 'data': 'kecamatanpj' },
            { 'data': 'kabupatenpj' },
            { 'data': 'perusahaan_pasien' },
            { 'data': 'suku_bangsa' },
            { 'data': 'bahasa_pasien' },
            { 'data': 'cacat_fisik' },
            { 'data': 'email' },
            { 'data': 'nip' },
            { 'data': 'kd_prop' },
            { "data": 'nama_propinsi' },
            { 'data': 'propinsipj' }
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
            { 'targets': 40}
        ],
        buttons: [],
        "scrollCollapse": true,
        // "scrollY": '48vh', 
        "pageLength":'25', 
        "lengthChange": true,
        "scrollX": true,
        dom: "<'row'<'col-sm-12'tr>><<'pmd-datatable-pagination' l i p>>"
    });

    // Configure long press
    let longPressTimer;
    let longPressDelay = 500; // milliseconds

    $.contextMenu({
        selector: '#tbl_pasien tbody tr', 
        trigger: 'right',
        events: {
            show: function(options) {
                $(this).addClass('selected');
            },
            hide: function(options) {
                $(this).removeClass('selected');
            }
        },
        callback: function(key, options) {
            let table = $('#tbl_pasien').DataTable();
            let data = table.row(this).data();
    
            switch(key) {
                case "cek_no_kartu":
                    vclaim_cek_kartu_pasien(data);
                    break;
                case "vclaim_cek_nik":
                    vclaim_cek_nik_pasien(data);
                    break;
                case "tampil_erm":
                    tampil_erm(data);
                    break;
                case "pendaftaran_igd":
                    pendaftaran_igd(data);
                    break;
                case "pendaftaran_poliklinik":
                    pendaftaran_poliklinik(data);
                    break;
                case "folder_rm":
                    folder_rm(data);
                    break;
            }
        },
        items: {
            "vclaim": {
                name: "Bridging VClaim BPJS",
                icon: "fa-hospital-o",
                items: {
                    "cek_no_kartu": { name: "Cek Nomor Kartu", icon: "fa-credit-card" },
                    "vclaim_cek_nik": { name: "Cek NIK", icon: "fa-id-card" }
                }
            },
            "pendaftaran": {
                name: "Pendaftaran",
                icon: "fa-file-text-o",
                items: {
                    "pendaftaran_igd": { name: "Pendaftaran IGD/UGD", icon: "fa-medkit" },
                    "pendaftaran_poliklinik": { name: "Pendaftaran Poliklinik", icon: "fa-user-md" }
                }
            },
            "tampil_erm": { name: "Elektronik Rekam Medis", icon: "fa-file-text-o" },
            "folder_rm": { name: "Folder Rekam Medis", icon: "fa-folder-o" }
        }
    });
    
    // Add touch support for mobile devices
    $('#tbl_pasien tbody').on('touchstart', 'tr', function(e) {
        let row = $(this);
        longPressTimer = setTimeout(function() {
            // Trigger context menu
            row.contextMenu({x: e.originalEvent.touches[0].pageX, y: e.originalEvent.touches[0].pageY});
        }, longPressDelay);
    }).on('touchend touchcancel', 'tr', function() {
        // Clear timer if touch ends before longpress delay
        clearTimeout(longPressTimer);
    });

    // ==============================================================
    // FORM VALIDASI
    // ==============================================================

    $("form[name='form_pasien']").validate({
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
            no_rkm_medis:'no_rkm_medis tidak boleh kosong!',
            nm_pasien:'nm_pasien tidak boleh kosong!',
            no_ktp:'no_ktp tidak boleh kosong!',
            jk:'jk tidak boleh kosong!',
            tmp_lahir:'tmp_lahir tidak boleh kosong!',
            tgl_lahir:'tgl_lahir tidak boleh kosong!',
            nm_ibu:'nm_ibu tidak boleh kosong!',
            alamat:'alamat tidak boleh kosong!',
            gol_darah:'gol_darah tidak boleh kosong!',
            pekerjaan:'pekerjaan tidak boleh kosong!',
            stts_nikah:'stts_nikah tidak boleh kosong!',
            agama:'agama tidak boleh kosong!',
            tgl_daftar:'tgl_daftar tidak boleh kosong!',
            no_tlp:'no_tlp tidak boleh kosong!',
            umur:'umur tidak boleh kosong!',
            pnd:'pnd tidak boleh kosong!',
            keluarga:'keluarga tidak boleh kosong!',
            namakeluarga:'namakeluarga tidak boleh kosong!',
            kd_pj:'kd_pj tidak boleh kosong!',
            no_peserta:'no_peserta tidak boleh kosong!',
            kd_kel:'kd_kel tidak boleh kosong!',
            kd_kec:'kd_kec tidak boleh kosong!',
            kd_kab:'kd_kab tidak boleh kosong!',
            pekerjaanpj:'pekerjaanpj tidak boleh kosong!',
            alamatpj:'alamatpj tidak boleh kosong!',
            kelurahanpj:'kelurahanpj tidak boleh kosong!',
            kecamatanpj:'kecamatanpj tidak boleh kosong!',
            kabupatenpj:'kabupatenpj tidak boleh kosong!',
            perusahaan_pasien:'perusahaan_pasien tidak boleh kosong!',
            suku_bangsa:'suku_bangsa tidak boleh kosong!',
            bahasa_pasien:'bahasa_pasien tidak boleh kosong!',
            cacat_fisik:'cacat_fisik tidak boleh kosong!',
            email:'email tidak boleh kosong!',
            nip:'nip tidak boleh kosong!',
            kd_prop:'kd_prop tidak boleh kosong!',
            propinsipj:'propinsipj tidak boleh kosong!'
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
            var propinsipj= $('#propinsipj').val();

            var typeact = $('#typeact').val();

            var formData = new FormData(form); // tambahan
            formData.append('typeact', typeact); // tambahan

            $.ajax({
                url: "{?=url([ADMIN,'pasien','aksi'])?}",
                method: "POST",
                contentType: false,
                processData: false,
                data: formData,
                success: function (data) {
                    try {
                        let response = typeof data === 'string' ? JSON.parse(data) : data;
            
                        if (response.status === 'success') {
                            bootbox.alert(response.message);
                            $("#modal_pasien").modal('hide');
                            var_tbl_pasien.draw();
                        } else {
                            bootbox.alert("Gagal: " + response.message);
                        }
                    } catch (e) {
                        bootbox.alert("Terjadi kesalahan pada response server.");
                        console.error("Invalid JSON response:", data);
                    }
                },
                error: function (xhr, status, error) {
                    bootbox.alert("Terjadi kesalahan AJAX: " + error);
                }
            });            
        }
    });

    // ==============================================================
    // CLICK TANDA X DI INPUT SEARCH
    // ==============================================================
    $("#search_pasien").click(function () {
        var_tbl_pasien.draw();
    });

    // ===========================================
    // Ketika tombol Edit di tekan
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
            $('#pnd').val(pnd);
            $('#keluarga').val(keluarga).change();
            $('#namakeluarga').val(namakeluarga);
            $('#kd_pj').val(kd_pj);
            $('#no_peserta').val(no_peserta);
            $('#kd_kel').val(kd_kel);
            $('#kd_kec').val(kd_kec);
            $('#kd_kab').val(kd_kab);
            $('#pekerjaanpj').val(pekerjaanpj);
            $('#alamatpj').val(alamatpj);
            $('#kelurahanpj').val(kelurahanpj);
            $('#kecamatanpj').val(kecamatanpj);
            $('#kabupatenpj').val(kabupatenpj);
            $('#perusahaan_pasien').val(perusahaan_pasien);
            $('#suku_bangsa').val(suku_bangsa);
            $('#bahasa_pasien').val(bahasa_pasien);
            $('#cacat_fisik').val(cacat_fisik);
            $('#email').val(email);
            $('#nip').val(nip);
            $('#kd_prop').val(kd_prop);
            $('#propinsipj').val(propinsipj);

            $("#no_rkm_medis").prop('readonly', true); // GA BISA DIEDIT KALI DISABLE
            $('#modal-title').text("Edit Data Pasien");
            $("#modal_pasien").modal();
        }
        else {
            bootbox.alert("Silakan pilih data yang akan di edit.");
        }

    });

    $("#cek_no_kartu").click(function () {
        var rowData = var_tbl_pasien.rows({ selected: true }).data()[0];
        if (rowData != null) {
            bootbox.alert('Ini');
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
        
            bootbox.confirm({
                title: "Konfirmasi Hapus",
                message: "Anda yakin ingin menghapus data pasien dengan No. RM: <strong>" + no_rkm_medis + "</strong>?",
                buttons: {
                    confirm: {
                        label: 'Ya, Hapus',
                        className: 'btn-danger'
                    },
                    cancel: {
                        label: 'Batal',
                        className: 'btn-secondary'
                    }
                },
                callback: function(result) {
                    if (result) {
                        $.ajax({
                            url: "{?=url([ADMIN,'pasien','aksi'])?}",
                            method: "POST",
                            data: {
                                no_rkm_medis: no_rkm_medis,
                                typeact: 'del'
                            },
                            success: function(response) {
                                var data = JSON.parse(response);
                                bootbox.alert({
                                    message: data.message,
                                    callback: function() {
                                        if (data.status === 'success') {
                                            var_tbl_pasien.draw();
                                        }
                                    }
                                });
                            },
                            error: function() {
                                bootbox.alert("Terjadi kesalahan saat menghapus data.");
                            }
                        });
                    }
                }
            });
        } else {
            bootbox.alert("Silakan pilih satu baris data untuk dihapus.");
        }        
    });

    // ==============================================================
    // TOMBOL TAMBAH DATA DI CLICK
    // ==============================================================

    let searchParams = new URLSearchParams(window.location.search)

    if(window.location.search.indexOf('no_rawat') !== -1) { 
        $('#search_text_pasien').val(searchParams.get('no_rawat'));
        var_tbl_pasien.draw();
        if(searchParams.get('modal') == 'true') {
            $("#modal_pasien").modal();
            $('#no_rawat').val(searchParams.get('no_rawat'));    
        }
    }

    jQuery("#tambah_data_pasien").click(function () {

        $('#no_rkm_medis').val('{?=$this->core->setNoRM()?}');
        $('#nm_pasien').val('');
        $('#no_ktp').val('');
        $('#jk').val('');
        $('#tmp_lahir').val('');
        $('#tgl_lahir').val('');
        $('#nm_ibu').val('');
        $('#alamat').val('');
        $('#gol_darah').val('');
        $('#pekerjaan').val('');
        $('#stts_nikah').val('');
        $('#agama').val('');
        $('#tgl_daftar').val('');
        $('#no_tlp').val('');
        $('#umur').val('');
        $('#pnd').val('');
        $('#keluarga').val('');
        $('#namakeluarga').val('');
        $('#kd_pj').val('');
        $('#no_peserta').val('');
        $('#kd_kel').val('');
        $('#kd_kec').val('');
        $('#kd_kab').val('');
        $('#pekerjaanpj').val('');
        $('#alamatpj').val('');
        $('#kelurahanpj').val('');
        $('#kecamatanpj').val('');
        $('#kabupatenpj').val('');
        $('#perusahaan_pasien').val('');
        $('#suku_bangsa').val('');
        $('#bahasa_pasien').val('');
        $('#cacat_fisik').val('');
        $('#email').val('');
        $('#nip').val('');
        $('#kd_prop').val('');
        $('#propinsipj').val('');

        if(window.location.search.indexOf('no_rawat') !== -1) { 
            $('#no_rawat').val(searchParams.get('no_rawat'));
        }

        $("#typeact").val("add");
        $("#no_rkm_medis").prop('disabled', false);
        
        $('#modal-title').text("Tambah Data Pasien");
        $("#modal_pasien").modal();
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    
    $("#lihat_data_pasien").click(function () {
        var search_field_pasien = $('#search_field_pasien').val();
        var search_text_pasien  = $('#search_text_pasien').val();
        var tgl_dari = $('#tgl_dari').val();
        var tgl_sampai = $('#tgl_sampai').val();

        $.ajax({
            url: "<?= url([ADMIN, 'pasien', 'aksi']) ?>",
            method: "POST",
            data: {
                typeact: 'lihat',
                search_field_pasien: search_field_pasien,
                search_text_pasien: search_text_pasien,
                tgl_dari: tgl_dari,
                tgl_sampai: tgl_sampai
            },
            dataType: 'json',
            success: function (res) {
                let eTable = `
                    <div class='table-responsive'>
                        <table id='tbl_lihat_pasien' class='table display dataTable' style='width:100%'>
                            <thead>
                                <tr>
                                    <th>No Rkm Medis</th>
                                    <th>Nama Pasien</th>
                                    <th>No KTP</th>
                                    <th>JK</th>
                                    <th>Tempat Lahir</th>
                                    <th>Tanggal Lahir</th>
                                    <th>Nama Ibu</th>
                                    <th>Alamat</th>
                                    <th>Gol Darah</th>
                                    <th>Pekerjaan</th>
                                    <th>Status Nikah</th>
                                    <th>Agama</th>
                                    <th>Tanggal Daftar</th>
                                    <th>No Telepon</th>
                                    <th>Umur</th>
                                    <th>Pendidikan</th>
                                    <th>Keluarga</th>
                                    <th>Nama Keluarga</th>
                                    <th>Kode Penjab</th>
                                    <th>Nama Penjab</th>
                                    <th>No Peserta</th>
                                    <th>Kode Kelurahan</th>
                                    <th>Nama Kelurahan</th>
                                    <th>Nama Kecamatan</th>
                                    <th>Nama Kabupaten</th>
                                    <th>Nama Provinsi</th>
                                    <th>Pekerjaan PJ</th>
                                    <th>Alamat PJ</th>
                                    <th>Kelurahan PJ</th>
                                    <th>Kecamatan PJ</th>
                                    <th>Kabupaten PJ</th>
                                    <th>Perusahaan Pasien</th>
                                    <th>Suku Bangsa</th>
                                    <th>Bahasa Pasien</th>
                                    <th>Cacat Fisik</th>
                                    <th>Email</th>
                                    <th>NIP</th>
                                    <th>Kode Provinsi</th>
                                    <th>Provinsi PJ</th>
                                </tr>
                            </thead>
                            <tbody>
                `;
    
                res.forEach(row => {
                    eTable += `
                        <tr>
                            <td>${row.no_rkm_medis}</td>
                            <td>${row.nm_pasien}</td>
                            <td>${row.no_ktp}</td>
                            <td>${row.jk}</td>
                            <td>${row.tmp_lahir}</td>
                            <td>${row.tgl_lahir}</td>
                            <td>${row.nm_ibu}</td>
                            <td>${row.alamat}</td>
                            <td>${row.gol_darah}</td>
                            <td>${row.pekerjaan}</td>
                            <td>${row.stts_nikah}</td>
                            <td>${row.agama}</td>
                            <td>${row.tgl_daftar}</td>
                            <td>${row.no_tlp}</td>
                            <td>${row.umur}</td>
                            <td>${row.pnd}</td>
                            <td>${row.keluarga}</td>
                            <td>${row.namakeluarga}</td>
                            <td>${row.kd_pj}</td>
                            <td>${row.nama_penjab}</td>
                            <td>${row.no_peserta}</td>
                            <td>${row.kd_kel}</td>
                            <td>${row.nama_kelurahan}</td>
                            <td>${row.nama_kecamatan}</td>
                            <td>${row.nama_kabupaten}</td>
                            <td>${row.nama_propinsi}</td>
                            <td>${row.pekerjaanpj}</td>
                            <td>${row.alamatpj}</td>
                            <td>${row.kelurahanpj}</td>
                            <td>${row.kecamatanpj}</td>
                            <td>${row.kabupatenpj}</td>
                            <td>${row.perusahaan_pasien}</td>
                            <td>${row.suku_bangsa}</td>
                            <td>${row.bahasa_pasien}</td>
                            <td>${row.cacat_fisik}</td>
                            <td>${row.email}</td>
                            <td>${row.nip}</td>
                            <td>${row.kd_prop}</td>
                            <td>${row.propinsipj}</td>
                        </tr>
                    `;
                });
    
                eTable += `
                            </tbody>
                        </table>
                    </div>
                `;
    
                $('#forTable_pasien').html(eTable);
            }
        });
    
        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_pasien").modal();
    });
    
    

    // ==============================================================
    // TOMBOL DETAIL pasien DI CLICK
    // ==============================================================
    jQuery("#lihat_detail_pasien").click(function (event) {

        var rowData = var_tbl_pasien.rows({ selected: true }).data()[0];

        if (rowData) {
            var no_rkm_medis = rowData['no_rkm_medis'];
            var baseURL = mlite.url + '/' + mlite.admin;
            event.preventDefault();
            var loadURL =  baseURL + '/pasien/detail/' + no_rkm_medis + '?t=' + mlite.token;
        
            var modal = $('#modal_detail_pasien');
            var modalContent = $('#modal_detail_pasien .modal-content');
        
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
        var baseURL = mlite.url + '/' + mlite.admin;
        window.open(baseURL + '/pasien/chart?t=' + mlite.token, '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
    }) 

});

function vclaim_cek_kartu_pasien(rowData) {
    if (rowData != null) {
        var no_peserta = rowData['no_peserta'];
        var baseURL = mlite.url + '/' + mlite.admin;
        event.preventDefault();
        var loadURL =  baseURL + '/pasien/vclaim_bynokartu/' + no_peserta + '/{?=date("Y-m-d")?}?t=' + mlite.token;
    
        var modal = $('#modal_detail_pasien');
        var modalContent = $('#modal_detail_pasien .modal-content');
    
        modal.off('show.bs.modal');
        modal.on('show.bs.modal', function () {
            modalContent.load(loadURL);
        }).modal();
        return false;

    }
    else {
        alert("Silakan pilih data yang akan di edit.");
    }
}

function vclaim_cek_nik_pasien(rowData) {
    if (rowData != null) {
        var no_ktp = rowData['no_ktp'];
        var baseURL = mlite.url + '/' + mlite.admin;
        event.preventDefault();
        var loadURL =  baseURL + '/pasien/vclaim_bynik/' + no_ktp + '/{?=date("Y-m-d")?}?t=' + mlite.token;
    
        var modal = $('#modal_detail_pasien');
        var modalContent = $('#modal_detail_pasien .modal-content');
    
        modal.off('show.bs.modal');
        modal.on('show.bs.modal', function () {
            modalContent.load(loadURL);
        }).modal();
        return false;

    }
    else {
        alert("Silakan pilih data yang akan di edit.");
    }
}

function tampil_erm(rowData) {
    if (rowData != null) {
        var no_rkm_medis = rowData['no_rkm_medis'];
        var baseURL = mlite.url + '/' + mlite.admin;
        event.preventDefault();
        var loadURL =  baseURL + '/pasien/riwayatperawatan/' + no_rkm_medis + '?t=' + mlite.token;
    
        var modal = $('#modal_detail_pasien');
        var modalContent = $('#modal_detail_pasien .modal-content');
    
        modal.off('show.bs.modal');
        modal.on('show.bs.modal', function () {
            modalContent.load(loadURL);
        }).modal();
        return false;

    }
    else {
        alert("Silakan pilih data yang akan di edit.");
    }
}

function pendaftaran_igd(rowData) {
    if (rowData != null) {
        var no_rkm_medis = rowData['no_rkm_medis'];
        var baseURL = mlite.url + '/' + mlite.admin;
        event.preventDefault();
        var loadURL =  baseURL + '/igd/manage?t=' + mlite.token + '&no_rkm_medis=' + no_rkm_medis;
        window.location.href = loadURL; 
        return false;

    }
    else {
        alert("Silakan pilih data yang akan di edit.");
    }
}

function pendaftaran_poliklinik(rowData) {
    if (rowData != null) {
        var no_rkm_medis = rowData['no_rkm_medis'];
        var baseURL = mlite.url + '/' + mlite.admin;
        event.preventDefault();
        var loadURL =  baseURL + '/rawat_jalan/manage?t=' + mlite.token + '&no_rkm_medis=' + no_rkm_medis;
        window.location.href = loadURL; 
        return false;

    }
    else {
        alert("Silakan pilih data yang akan di edit.");
    }
}

function folder_rm(rowData) {
    if (rowData != null) {
        var no_rkm_medis = rowData['no_rkm_medis'];
        var baseURL = mlite.url + '/' + mlite.admin;
        event.preventDefault();
        var loadURL =  baseURL + '/pasien/folder/' + no_rkm_medis + '?t=' + mlite.token;
        window.location.href = loadURL; 
        return false;

    }
    else {
        alert("Silakan pilih data yang akan di edit.");
    }
}

let currentStep = 0;
const steps = document.querySelectorAll(".step");
const nextBtn = document.getElementById("nextBtn");
const prevBtn = document.getElementById("prevBtn");
const submitBtn = document.getElementById("simpan_data_pasien");

function showStep(index) {
    // console.log("Menampilkan step:", index);
    steps.forEach((step, i) => {
        step.style.display = i === index ? "block" : "none";
    });

    // Tampilkan/sembunyikan tombol
    prevBtn.style.display = index === 0 ? "none" : "inline-block";
    nextBtn.style.display = index === steps.length - 1 ? "none" : "inline-block";
    submitBtn.style.display = index === steps.length - 1 ? "inline-block" : "none";
}

nextBtn.addEventListener("click", () => {
    // console.log("Next clicked, currentStep sebelum:", currentStep);
    if (currentStep < steps.length - 1) {
        currentStep++;
        showStep(currentStep);
    }
});

prevBtn.addEventListener("click", () => {
    if (currentStep > 0) {
        currentStep--;
        showStep(currentStep);
    }
});

// Inisialisasi tampilan
showStep(currentStep);

$('.tanggal').datetimepicker({
    format: 'YYYY-MM-DD',
    locale: 'id'
});
