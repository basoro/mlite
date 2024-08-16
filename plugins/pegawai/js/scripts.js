jQuery().ready(function () {
    var var_tbl_pegawai = $('#tbl_pegawai').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'dom': 'Bfrtip',
        'searching': false,
        'select': true,
        'colReorder': true,
        "bInfo" : false,
        "ajax": {
            "url": "{?=url(['pegawai','data'])?}",
            "dataType": "json",
            "type": "POST",
            "data": function (data) {

                // Read values
                var search_field_pegawai = $('#search_field_pegawai').val();
                var search_text_pegawai = $('#search_text_pegawai').val();
                
                data.search_field_pegawai = search_field_pegawai;
                data.search_text_pegawai = search_text_pegawai;
                
            }
        },
        "fnDrawCallback": function () {
            $('#more_data_pegawai').on('click', function(e) {
                e.preventDefault();
                var clientX = e.originalEvent.clientX;
                var clientY = e.originalEvent.clientY;
                $('#tbl_pegawai tr').contextMenu({x: clientX, y: clientY});
            });          
        },        
        "columns": [
            { 'data': 'id' },
            { 'data': 'nik' },
            { 'data': 'nama' },
            { 'data': 'jk' },
            { 'data': 'jbtn' },
            { 'data': 'jnj_jabatan' },
            { 'data': 'kode_kelompok' },
            { 'data': 'kode_resiko' },
            { 'data': 'kode_emergency' },
            { 'data': 'departemen' },
            { 'data': 'bidang' },
            { 'data': 'stts_wp' },
            { 'data': 'stts_kerja' },
            { 'data': 'npwp' },
            { 'data': 'pendidikan' },
            { 'data': 'gapok' },
            { 'data': 'tmp_lahir' },
            { 'data': 'tgl_lahir' },
            { 'data': 'alamat' },
            { 'data': 'kota' },
            { 'data': 'mulai_kerja' },
            { 'data': 'ms_kerja' },
            { 'data': 'indexins' },
            { 'data': 'bpd' },
            { 'data': 'rekening' },
            { 'data': 'stts_aktif' },
            { 'data': 'wajibmasuk' },
            { 'data': 'pengurang' },
            { 'data': 'indek' },
            { 'data': 'mulai_kontrak' },
            { 'data': 'cuti_diambil' },
            { 'data': 'dankes' },
            { 'data': 'photo' },
            { 'data': 'no_ktp' }
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
        selector: '#tbl_pegawai tr', 
        trigger: 'right',
        callback: function(key, options) {
          var rowData = var_tbl_pegawai.rows({ selected: true }).data()[0];
          if (rowData != null) {
            var nik = rowData['nik'];
            switch (key) {
                case 'detail' :
                OpenModal(mlite.url + '/pegawai/detail/' + nik + '?t=' + mlite.token);
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

    $("form[name='form_pegawai']").validate({
        rules: {
            id: 'required',
            nik: 'required',
            nama: 'required',
            jk: 'required',
            jbtn: 'required',
            jnj_jabatan: 'required',
            kode_kelompok: 'required',
            kode_resiko: 'required',
            kode_emergency: 'required',
            departemen: 'required',
            bidang: 'required',
            stts_wp: 'required',
            stts_kerja: 'required',
            npwp: 'required',
            pendidikan: 'required',
            gapok: 'required',
            tmp_lahir: 'required',
            tgl_lahir: 'required',
            alamat: 'required',
            kota: 'required',
            mulai_kerja: 'required',
            ms_kerja: 'required',
            indexins: 'required',
            bpd: 'required',
            rekening: 'required',
            stts_aktif: 'required',
            wajibmasuk: 'required',
            pengurang: 'required',
            indek: 'required',
            mulai_kontrak: 'required',
            cuti_diambil: 'required',
            dankes: 'required',
            photo: 'required',
            no_ktp: 'required'
        },
        messages: {
            id:'Id tidak boleh kosong!',
            nik:'Nik tidak boleh kosong!',
            nama:'Nama tidak boleh kosong!',
            jk:'Jk tidak boleh kosong!',
            jbtn:'Jbtn tidak boleh kosong!',
            jnj_jabatan:'Jnj Jabatan tidak boleh kosong!',
            kode_kelompok:'Kode Kelompok tidak boleh kosong!',
            kode_resiko:'Kode Resiko tidak boleh kosong!',
            kode_emergency:'Kode Emergency tidak boleh kosong!',
            departemen:'Departemen tidak boleh kosong!',
            bidang:'Bidang tidak boleh kosong!',
            stts_wp:'Stts Wp tidak boleh kosong!',
            stts_kerja:'Stts Kerja tidak boleh kosong!',
            npwp:'Npwp tidak boleh kosong!',
            pendidikan:'Pendidikan tidak boleh kosong!',
            gapok:'Gapok tidak boleh kosong!',
            tmp_lahir:'Tmp Lahir tidak boleh kosong!',
            tgl_lahir:'Tgl Lahir tidak boleh kosong!',
            alamat:'Alamat tidak boleh kosong!',
            kota:'Kota tidak boleh kosong!',
            mulai_kerja:'Mulai Kerja tidak boleh kosong!',
            ms_kerja:'Ms Kerja tidak boleh kosong!',
            indexins:'Indexins tidak boleh kosong!',
            bpd:'Bpd tidak boleh kosong!',
            rekening:'Rekening tidak boleh kosong!',
            stts_aktif:'Stts Aktif tidak boleh kosong!',
            wajibmasuk:'Wajibmasuk tidak boleh kosong!',
            pengurang:'Pengurang tidak boleh kosong!',
            indek:'Indek tidak boleh kosong!',
            mulai_kontrak:'Mulai Kontrak tidak boleh kosong!',
            cuti_diambil:'Cuti Diambil tidak boleh kosong!',
            dankes:'Dankes tidak boleh kosong!',
            photo:'Photo tidak boleh kosong!',
            no_ktp:'No Ktp tidak boleh kosong!'
        },
        submitHandler: function (form) {
            var id= $('#id').val();
            var nik= $('#nik').val();
            var nama= $('#nama').val();
            var jk= $('#jk').val();
            var jbtn= $('#jbtn').val();
            var jnj_jabatan= $('#jnj_jabatan').val();
            var kode_kelompok= $('#kode_kelompok').val();
            var kode_resiko= $('#kode_resiko').val();
            var kode_emergency= $('#kode_emergency').val();
            var departemen= $('#departemen').val();
            var bidang= $('#bidang').val();
            var stts_wp= $('#stts_wp').val();
            var stts_kerja= $('#stts_kerja').val();
            var npwp= $('#npwp').val();
            var pendidikan= $('#pendidikan').val();
            var gapok= $('#gapok').val();
            var tmp_lahir= $('#tmp_lahir').val();
            var tgl_lahir= $('#tgl_lahir').val();
            var alamat= $('#alamat').val();
            var kota= $('#kota').val();
            var mulai_kerja= $('#mulai_kerja').val();
            var ms_kerja= $('#ms_kerja').val();
            var indexins= $('#indexins').val();
            var bpd= $('#bpd').val();
            var rekening= $('#rekening').val();
            var stts_aktif= $('#stts_aktif').val();
            var wajibmasuk= $('#wajibmasuk').val();
            var pengurang= $('#pengurang').val();
            var indek= $('#indek').val();
            var mulai_kontrak= $('#mulai_kontrak').val();
            var cuti_diambil= $('#cuti_diambil').val();
            var dankes= $('#dankes').val();
            var photo= $('#photo').val();
            var no_ktp= $('#no_ktp').val();

            var typeact = $('#typeact').val();

            const fileupload = $('#fileToUpload').prop('files')[0];
            var formData = new FormData(form); // tambahan
            formData.append('fileToUpload', fileToUpload);

            formData.append('typeact', typeact); // tambahan

            $.ajax({
                url: "{?=url(['pegawai','aksi'])?}",
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
                            $("#modal_pegawai").modal('hide');
                        } else {
                            bootbox.alert('<span class="text-danger">' + data.msg + '</span>');
                        }    
                    }
                    else if (typeact == "edit") {
                        if(data.status === 'success') {
                            bootbox.alert('<span class="text-success">' + data.msg + '</span>');
                            $("#modal_pegawai").modal('hide');
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
                    var_tbl_pegawai.draw();
                }
            })
        }
    });


    if(typeof ws != 'undefined' && typeof ws.readyState != 'undefined' && ws.readyState == 1){
        ws.onmessage = function(response){
            try{
                output = JSON.parse(response.data);
                if(output['action'] == 'add'){
                    var_tbl_pegawai.draw();
                }
                if(output['action'] == 'edit'){
                    var_tbl_pegawai.draw();
                }
                if(output['action'] == 'del'){
                    var_tbl_pegawai.draw();
                }
            }catch(e){
                console.log(e);
            }
        }
    }

    // ==============================================================
    // KETIKA TOMBOL SEARCH DITEKAN
    // ==============================================================
    $('#filter_search_pegawai').click(function () {
        var_tbl_pegawai.draw();
    });

    // ===========================================
    // KETIKA TOMBOL EDIT DITEKAN
    // ===========================================

    $("#edit_data_pegawai").click(function () {
        var rowData = var_tbl_pegawai.rows({ selected: true }).data()[0];
        if (rowData != null) {

            var id = rowData['id'];
            var nik = rowData['nik'];
            var nama = rowData['nama'];
            var jk = rowData['jk'];
            var jbtn = rowData['jbtn'];
            var jnj_jabatan = rowData['jnj_jabatan'];
            var kode_kelompok = rowData['kode_kelompok'];
            var kode_resiko = rowData['kode_resiko'];
            var kode_emergency = rowData['kode_emergency'];
            var departemen = rowData['departemen'];
            var bidang = rowData['bidang'];
            var stts_wp = rowData['stts_wp'];
            var stts_kerja = rowData['stts_kerja'];
            var npwp = rowData['npwp'];
            var pendidikan = rowData['pendidikan'];
            var gapok = rowData['gapok'];
            var tmp_lahir = rowData['tmp_lahir'];
            var tgl_lahir = rowData['tgl_lahir'];
            var alamat = rowData['alamat'];
            var kota = rowData['kota'];
            var mulai_kerja = rowData['mulai_kerja'];
            var ms_kerja = rowData['ms_kerja'];
            var indexins = rowData['indexins'];
            var bpd = rowData['bpd'];
            var rekening = rowData['rekening'];
            var stts_aktif = rowData['stts_aktif'];
            var wajibmasuk = rowData['wajibmasuk'];
            var pengurang = rowData['pengurang'];
            var indek = rowData['indek'];
            var mulai_kontrak = rowData['mulai_kontrak'];
            var cuti_diambil = rowData['cuti_diambil'];
            var dankes = rowData['dankes'];
            var photo = rowData['photo'];
            var no_ktp = rowData['no_ktp'];

            $("#typeact").val("edit");
  
            $('#id').val(id);
            $('#nik').val(nik);
            $('#nama').val(nama);
            $('#jk').val(jk).change();
            $('#jbtn').val(jbtn);
            $('#jnj_jabatan').val(jnj_jabatan).change();
            $('#kode_kelompok').val(kode_kelompok).change();
            $('#kode_resiko').val(kode_resiko).change();
            $('#kode_emergency').val(kode_emergency).change();
            $('#departemen').val(departemen).change();
            $('#bidang').val(bidang).change();
            $('#stts_wp').val(stts_wp).change();
            $('#stts_kerja').val(stts_kerja).change();
            $('#npwp').val(npwp);
            $('#pendidikan').val(pendidikan).change();
            $('#gapok').val(gapok);
            $('#tmp_lahir').val(tmp_lahir);
            $('#tgl_lahir').val(tgl_lahir);
            $('#alamat').val(alamat);
            $('#kota').val(kota);
            $('#mulai_kerja').val(mulai_kerja);
            $('#ms_kerja').val(ms_kerja).change();
            $('#indexins').val(indexins).change();
            $('#bpd').val(bpd).change();
            $('#rekening').val(rekening);
            $('#stts_aktif').val(stts_aktif).change();
            $('#wajibmasuk').val(wajibmasuk);
            $('#pengurang').val(pengurang);
            $('#indek').val(indek);
            $('#mulai_kontrak').val(mulai_kontrak);
            $('#cuti_diambil').val(cuti_diambil);
            $('#dankes').val(dankes);
            if(photo) {
                $("#photo").attr('src', '{?=url()?}/uploads/pegawai/' + photo);
            } else {
                $("#photo").attr('src', '{?=url()?}/plugins/pegawai/img/default.png');
            }
            $('#no_ktp').val(no_ktp);

            //$("#id").prop('disabled', true); // GA BISA DIEDIT KALI DISABLE
            $('#modal-title').text("Edit Data Pegawai");
            $("#modal_pegawai").modal('show');
        }
        else {
            bootbox.alert("Silakan pilih data yang akan di edit.");
        }

    });

    // ==============================================================
    // TOMBOL  DELETE DI CLICK
    // ==============================================================
    jQuery("#hapus_data_pegawai").click(function () {
        var rowData = var_tbl_pegawai.rows({ selected: true }).data()[0];


        if (rowData) {
var id = rowData['id'];
            bootbox.confirm('Anda yakin akan menghapus data dengan id="' + id, function(result) {
                if(result) {
                    $.ajax({
                        url: "{?=url(['pegawai','aksi'])?}",
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
                            var_tbl_pegawai.draw();
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
    jQuery("#tambah_data_pegawai").click(function () {

        $('#id').val('');
        $('#nik').val('');
        $('#nama').val('');
        $('#jk').val('').change();
        $('#jbtn').val('');
        $('#jnj_jabatan').val('').change();
        $('#kode_kelompok').val('').change();
        $('#kode_resiko').val('').change();
        $('#kode_emergency').val('').change();
        $('#departemen').val('').change();
        $('#bidang').val('').change();
        $('#stts_wp').val('').change();
        $('#stts_kerja').val('').change();
        $('#npwp').val('');
        $('#pendidikan').val('').change();
        $('#gapok').val('');
        $('#tmp_lahir').val('');
        // $('#tgl_lahir').val('');
        $('#alamat').val('');
        $('#kota').val('');
        // $('#mulai_kerja').val('');
        $('#ms_kerja').val('').change();
        $('#indexins').val('').change();
        $('#bpd').val('').change();
        $('#rekening').val('');
        $('#stts_aktif').val('').change();
        $('#wajibmasuk').val('');
        $('#pengurang').val('');
        $('#indek').val('');
        // $('#mulai_kontrak').val('');
        $('#cuti_diambil').val('');
        $('#dankes').val('');
        $("#photo").attr('src', '{?=url()?}/plugins/pegawai/img/default.png');
        $('#no_ktp').val('');

        $("#typeact").val("add");
        $("#id").prop('disabled', false);
        
        $('#modal-title').text("Tambah Data Pegawai");
        $("#modal_pegawai").modal('show');
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_pegawai").click(function () {

        var search_field_pegawai = $('#search_field_pegawai').val();
        var search_text_pegawai = $('#search_text_pegawai').val();

        $.ajax({
            url: "{?=url(['pegawai','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat', 
                search_field_pegawai: search_field_pegawai, 
                search_text_pegawai: search_text_pegawai
            },
            dataType: 'json',
            success: function (res) {
                var eTable = "<div class='table-responsive'><table id='tbl_lihat_pegawai' class='table display dataTable' style='width:100%'><thead><th>Id</th><th>Nik</th><th>Nama</th><th>Jk</th><th>Jbtn</th><th>Jnj Jabatan</th><th>Kode Kelompok</th><th>Kode Resiko</th><th>Kode Emergency</th><th>Departemen</th><th>Bidang</th><th>Stts Wp</th><th>Stts Kerja</th><th>Npwp</th><th>Pendidikan</th><th>Gapok</th><th>Tmp Lahir</th><th>Tgl Lahir</th><th>Alamat</th><th>Kota</th><th>Mulai Kerja</th><th>Ms Kerja</th><th>Indexins</th><th>Bpd</th><th>Rekening</th><th>Stts Aktif</th><th>Wajibmasuk</th><th>Pengurang</th><th>Indek</th><th>Mulai Kontrak</th><th>Cuti Diambil</th><th>Dankes</th><th>Photo</th><th>No Ktp</th></thead>";
                for (var i = 0; i < res.length; i++) {
                    eTable += "<tr>";
                    eTable += '<td>' + res[i]['id'] + '</td>';
                    eTable += '<td>' + res[i]['nik'] + '</td>';
                    eTable += '<td>' + res[i]['nama'] + '</td>';
                    eTable += '<td>' + res[i]['jk'] + '</td>';
                    eTable += '<td>' + res[i]['jbtn'] + '</td>';
                    eTable += '<td>' + res[i]['jnj_jabatan'] + '</td>';
                    eTable += '<td>' + res[i]['kode_kelompok'] + '</td>';
                    eTable += '<td>' + res[i]['kode_resiko'] + '</td>';
                    eTable += '<td>' + res[i]['kode_emergency'] + '</td>';
                    eTable += '<td>' + res[i]['departemen'] + '</td>';
                    eTable += '<td>' + res[i]['bidang'] + '</td>';
                    eTable += '<td>' + res[i]['stts_wp'] + '</td>';
                    eTable += '<td>' + res[i]['stts_kerja'] + '</td>';
                    eTable += '<td>' + res[i]['npwp'] + '</td>';
                    eTable += '<td>' + res[i]['pendidikan'] + '</td>';
                    eTable += '<td>' + res[i]['gapok'] + '</td>';
                    eTable += '<td>' + res[i]['tmp_lahir'] + '</td>';
                    eTable += '<td>' + res[i]['tgl_lahir'] + '</td>';
                    eTable += '<td>' + res[i]['alamat'] + '</td>';
                    eTable += '<td>' + res[i]['kota'] + '</td>';
                    eTable += '<td>' + res[i]['mulai_kerja'] + '</td>';
                    eTable += '<td>' + res[i]['ms_kerja'] + '</td>';
                    eTable += '<td>' + res[i]['indexins'] + '</td>';
                    eTable += '<td>' + res[i]['bpd'] + '</td>';
                    eTable += '<td>' + res[i]['rekening'] + '</td>';
                    eTable += '<td>' + res[i]['stts_aktif'] + '</td>';
                    eTable += '<td>' + res[i]['wajibmasuk'] + '</td>';
                    eTable += '<td>' + res[i]['pengurang'] + '</td>';
                    eTable += '<td>' + res[i]['indek'] + '</td>';
                    eTable += '<td>' + res[i]['mulai_kontrak'] + '</td>';
                    eTable += '<td>' + res[i]['cuti_diambil'] + '</td>';
                    eTable += '<td>' + res[i]['dankes'] + '</td>';
                    eTable += '<td>' + res[i]['photo'] + '</td>';
                    eTable += '<td>' + res[i]['no_ktp'] + '</td>';
                    eTable += "</tr>";
                }
                eTable += "</tbody></table></div>";
                $('#forTable_pegawai').html(eTable);
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_pegawai").modal('show');
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
        doc.text("Tabel Data Pegawai", 20, 95, null, null, null);
        const totalPagesExp = "{total_pages_count_string}";        
        doc.autoTable({
            html: '#tbl_lihat_pegawai',
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
        // doc.save('table_data_pegawai.pdf')
        window.open(doc.output('bloburl'), '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
              
    })

    // ===========================================
    // Ketika tombol export xlsx di tekan
    // ===========================================
    $("#export_xlsx").click(function () {
        let tbl1 = document.getElementById("tbl_lihat_pegawai");
        let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
        let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
        let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
        const new_workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data pegawai");
        XLSX.writeFile(new_workbook, 'tmp_file.xls');
    })

    $("#view_chart").click(function () {
        window.open(mlite.url + '/pegawai/chart?t=' + mlite.token, '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
    })   

	// Avatar
	var reader  = new FileReader();
	reader.addEventListener("load", function() {
		$("#photo").attr('src', reader.result);
	}, false);
	$("input[name=fileToUpload]").change(function() {
		reader.readAsDataURL(this.files[0]);
	});

    $(".datepicker").daterangepicker({
        singleDatePicker: true,
        showDropdowns: true,
        locale: {
            format: "YYYY-MM-DD",
        },
    });

});