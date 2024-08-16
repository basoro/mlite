jQuery().ready(function () {
    var var_tbl_databarang = $('#tbl_databarang').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'dom': 'Bfrtip',
        'searching': false,
        'select': true,
        'colReorder': true,
        "bInfo" : false,
        "ajax": {
            "url": "{?=url(['databarang','data'])?}",
            "dataType": "json",
            "type": "POST",
            "data": function (data) {

                // Read values
                var search_field_databarang = $('#search_field_databarang').val();
                var search_text_databarang = $('#search_text_databarang').val();
                
                data.search_field_databarang = search_field_databarang;
                data.search_text_databarang = search_text_databarang;
                
            }
        },
        "fnDrawCallback": function () {
            $('#more_data_databarang').on('click', function(e) {
                e.preventDefault();
                var clientX = e.originalEvent.clientX;
                var clientY = e.originalEvent.clientY;
                $('#tbl_databarang tr').contextMenu({x: clientX, y: clientY});
            });          
        },        
        "columns": [
            { 'data': 'kode_brng' },
            { 'data': 'nama_brng' },
            { 'data': 'kode_satbesar' },
            { 'data': 'kode_sat' },
            { 'data': 'letak_barang' },
            { 'data': 'dasar' },
            { 'data': 'h_beli' },
            { 'data': 'ralan' },
            { 'data': 'kelas1' },
            { 'data': 'kelas2' },
            { 'data': 'kelas3' },
            { 'data': 'utama' },
            { 'data': 'vip' },
            { 'data': 'vvip' },
            { 'data': 'beliluar' },
            { 'data': 'jualbebas' },
            { 'data': 'karyawan' },
            { 'data': 'stokminimal' },
            { 'data': 'kdjns' },
            { 'data': 'nama_jenis' },
            { 'data': 'isi' },
            { 'data': 'kapasitas' },
            { 'data': 'expire' },
            { 'data': 'status', 
                "render": function (data) {
                    if(data == '1') {
                        var status = 'Aktif';
                    } else {
                        var status = 'Tidak Aktif';
                    }
                    return status;
                } 
            },
            { 'data': 'kode_industri' },
            { 'data': 'nama_industri' },
            { 'data': 'kode_kategori' },
            { 'data': 'nama_kategori' },
            { 'data': 'kode_golongan' },
            { 'data': 'nama_golongan' }
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
            { 'targets': 29}
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
        selector: '#tbl_databarang tr', 
        trigger: 'right',
        callback: function(key, options) {
          var rowData = var_tbl_databarang.rows({ selected: true }).data()[0];
          if (rowData != null) {
            var kode_brng = rowData['kode_brng'];
            switch (key) {
                case 'detail' :
                    OpenModal(mlite.url + '/databarang/detail/' + kode_brng + '?t=' + mlite.token);
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

    $("form[name='form_databarang']").validate({
        rules: {
            kode_brng: 'required',
            nama_brng: 'required',
            kode_satbesar: 'required',
            kode_sat: 'required',
            letak_barang: 'required',
            dasar: 'required',
            h_beli: 'required',
            ralan: 'required',
            kelas1: 'required',
            kelas2: 'required',
            kelas3: 'required',
            utama: 'required',
            vip: 'required',
            vvip: 'required',
            beliluar: 'required',
            jualbebas: 'required',
            karyawan: 'required',
            stokminimal: 'required',
            kdjns: 'required',
            isi: 'required',
            kapasitas: 'required',
            expire: 'required',
            status: 'required',
            kode_industri: 'required',
            kode_kategori: 'required',
            kode_golongan: 'required'
        },
        messages: {
            kode_brng:'Kode Brng tidak boleh kosong!',
            nama_brng:'Nama Brng tidak boleh kosong!',
            kode_satbesar:'Kode Satbesar tidak boleh kosong!',
            kode_sat:'Kode Sat tidak boleh kosong!',
            letak_barang:'Letak Barang tidak boleh kosong!',
            dasar:'Dasar tidak boleh kosong!',
            h_beli:'H Beli tidak boleh kosong!',
            ralan:'Ralan tidak boleh kosong!',
            kelas1:'Kelas1 tidak boleh kosong!',
            kelas2:'Kelas2 tidak boleh kosong!',
            kelas3:'Kelas3 tidak boleh kosong!',
            utama:'Utama tidak boleh kosong!',
            vip:'Vip tidak boleh kosong!',
            vvip:'Vvip tidak boleh kosong!',
            beliluar:'Beliluar tidak boleh kosong!',
            jualbebas:'Jualbebas tidak boleh kosong!',
            karyawan:'Karyawan tidak boleh kosong!',
            stokminimal:'Stokminimal tidak boleh kosong!',
            kdjns:'Kdjns tidak boleh kosong!',
            isi:'Isi tidak boleh kosong!',
            kapasitas:'Kapasitas tidak boleh kosong!',
            expire:'Expire tidak boleh kosong!',
            status:'Status tidak boleh kosong!',
            kode_industri:'Kode Industri tidak boleh kosong!',
            kode_kategori:'Kode Kategori tidak boleh kosong!',
            kode_golongan:'Kode Golongan tidak boleh kosong!'
        },
        submitHandler: function (form) {
            var kode_brng= $('#kode_brng').val();
            var nama_brng= $('#nama_brng').val();
            var kode_satbesar= $('#kode_satbesar').val();
            var kode_sat= $('#kode_sat').val();
            var letak_barang= $('#letak_barang').val();
            var dasar= $('#dasar').val();
            var h_beli= $('#h_beli').val();
            var ralan= $('#ralan').val();
            var kelas1= $('#kelas1').val();
            var kelas2= $('#kelas2').val();
            var kelas3= $('#kelas3').val();
            var utama= $('#utama').val();
            var vip= $('#vip').val();
            var vvip= $('#vvip').val();
            var beliluar= $('#beliluar').val();
            var jualbebas= $('#jualbebas').val();
            var karyawan= $('#karyawan').val();
            var stokminimal= $('#stokminimal').val();
            var kdjns= $('#kdjns').val();
            var isi= $('#isi').val();
            var kapasitas= $('#kapasitas').val();
            var expire= $('#expire').val();
            var status= $('#status').val();
            var kode_industri= $('#kode_industri').val();
            var kode_kategori= $('#kode_kategori').val();
            var kode_golongan= $('#kode_golongan').val();

            var typeact = $('#typeact').val();

            var formData = new FormData(form); // tambahan
            formData.append('typeact', typeact); // tambahan

            $.ajax({
                url: "{?=url(['databarang','aksi'])?}",
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
                            $("#modal_databarang").modal('hide');
                        } else {
                            bootbox.alert('<span class="text-danger">' + data.msg + '</span>');
                        }    
                    }
                    else if (typeact == "edit") {
                        if(data.status === 'success') {
                            bootbox.alert('<span class="text-success">' + data.msg + '</span>');
                            $("#modal_databarang").modal('hide');
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
                    var_tbl_databarang.draw();
                }
            })
        }
    });


    if(typeof ws != 'undefined' && typeof ws.readyState != 'undefined' && ws.readyState == 1){
        ws.onmessage = function(response){
            try{
                output = JSON.parse(response.data);
                if(output['action'] == 'add'){
                    var_tbl_databarang.draw();
                }
                if(output['action'] == 'edit'){
                    var_tbl_databarang.draw();
                }
                if(output['action'] == 'del'){
                    var_tbl_databarang.draw();
                }
            }catch(e){
                console.log(e);
            }
        }
    }
    
    // ==============================================================
    // KETIKA TOMBOL SEARCH DITEKAN
    // ==============================================================
    $('#filter_search_databarang').click(function () {
        var_tbl_databarang.draw();
    });

    // ===========================================
    // KETIKA TOMBOL EDIT DITEKAN
    // ===========================================

    $("#edit_data_databarang").click(function () {
        var rowData = var_tbl_databarang.rows({ selected: true }).data()[0];
        if (rowData != null) {

            var kode_brng = rowData['kode_brng'];
            var nama_brng = rowData['nama_brng'];
            var kode_satbesar = rowData['kode_satbesar'];
            var kode_sat = rowData['kode_sat'];
            var letak_barang = rowData['letak_barang'];
            var dasar = rowData['dasar'];
            var h_beli = rowData['h_beli'];
            var ralan = rowData['ralan'];
            var kelas1 = rowData['kelas1'];
            var kelas2 = rowData['kelas2'];
            var kelas3 = rowData['kelas3'];
            var utama = rowData['utama'];
            var vip = rowData['vip'];
            var vvip = rowData['vvip'];
            var beliluar = rowData['beliluar'];
            var jualbebas = rowData['jualbebas'];
            var karyawan = rowData['karyawan'];
            var stokminimal = rowData['stokminimal'];
            var kdjns = rowData['kdjns'];
            var isi = rowData['isi'];
            var kapasitas = rowData['kapasitas'];
            var expire = rowData['expire'];
            var status = rowData['status'];
            var kode_industri = rowData['kode_industri'];
            var kode_kategori = rowData['kode_kategori'];
            var kode_golongan = rowData['kode_golongan'];

            $("#typeact").val("edit");
  
            $('#kode_brng').val(kode_brng);
            $('#nama_brng').val(nama_brng);
            $('#kode_satbesar').val(kode_satbesar);
            $('#kode_sat').val(kode_sat);
            $('#letak_barang').val(letak_barang);
            $('#dasar').val(dasar);
            $('#h_beli').val(h_beli);
            $('#ralan').val(ralan);
            $('#kelas1').val(kelas1);
            $('#kelas2').val(kelas2);
            $('#kelas3').val(kelas3);
            $('#utama').val(utama);
            $('#vip').val(vip);
            $('#vvip').val(vvip);
            $('#beliluar').val(beliluar);
            $('#jualbebas').val(jualbebas);
            $('#karyawan').val(karyawan);
            $('#stokminimal').val(stokminimal);
            $('#kdjns').val(kdjns);
            $('#isi').val(isi);
            $('#kapasitas').val(kapasitas);
            $('#expire').val(expire);
            $('#status').val(status);
            $('#kode_industri').val(kode_industri);
            $('#kode_kategori').val(kode_kategori);
            $('#kode_golongan').val(kode_golongan);

            $("#kode_brng").prop('readonly', true); // GA BISA DIEDIT KALI READONLY
            $('#modal-title').text("Edit Data Databarang");
            $("#modal_databarang").modal('show');
        }
        else {
            bootbox.alert("Silakan pilih data yang akan di edit.");
        }

    });

    // ==============================================================
    // TOMBOL  DELETE DI CLICK
    // ==============================================================
    jQuery("#hapus_data_databarang").click(function () {
        var rowData = var_tbl_databarang.rows({ selected: true }).data()[0];


        if (rowData) {
            var kode_brng = rowData['kode_brng'];
            bootbox.confirm('Anda yakin akan menghapus data dengan kode_brng="' + kode_brng, function(result) {
                if(result) {
                    $.ajax({
                        url: "{?=url(['databarang','aksi'])?}",
                        method: "POST",
                        data: {
                            kode_brng: kode_brng,
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
                            var_tbl_databarang.draw();
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
    jQuery("#tambah_data_databarang").click(function () {

        $('#kode_brng').val('{$setKodeDatabarang}');
        $('#nama_brng').val('');
        $('#kode_satbesar').val('');
        $('#kode_sat').val('');
        $('#letak_barang').val('');
        $('#dasar').val('');
        $('#h_beli').val('');
        $('#ralan').val('');
        $('#kelas1').val('');
        $('#kelas2').val('');
        $('#kelas3').val('');
        $('#utama').val('');
        $('#vip').val('');
        $('#vvip').val('');
        $('#beliluar').val('');
        $('#jualbebas').val('');
        $('#karyawan').val('');
        $('#stokminimal').val('');
        $('#kdjns').val('');
        $('#isi').val('');
        $('#kapasitas').val('');
        $('#expire').val('');
        $('#status').val('');
        $('#kode_industri').val('');
        $('#kode_kategori').val('');
        $('#kode_golongan').val('');

        $("#typeact").val("add");
        $("#kode_brng").prop('readonly', false);
        
        $('#modal-title').text("Tambah Data Databarang");
        $("#modal_databarang").modal('show');
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_databarang").click(function () {

        var search_field_databarang = $('#search_field_databarang').val();
        var search_text_databarang = $('#search_text_databarang').val();

        $.ajax({
            url: "{?=url(['databarang','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat', 
                search_field_databarang: search_field_databarang, 
                search_text_databarang: search_text_databarang
            },
            dataType: 'json',
            success: function (res) {
                var eTable = "<div class='table-responsive'><table id='tbl_lihat_databarang' class='table display dataTable' style='width:100%'><thead><th>Kode Brng</th><th>Nama Brng</th><th>Kode Satbesar</th><th>Kode Sat</th><th>Letak Barang</th><th>Dasar</th><th>H Beli</th><th>Ralan</th><th>Kelas1</th><th>Kelas2</th><th>Kelas3</th><th>Utama</th><th>Vip</th><th>Vvip</th><th>Beliluar</th><th>Jualbebas</th><th>Karyawan</th><th>Stokminimal</th><th>Kdjns</th><th>Isi</th><th>Kapasitas</th><th>Expire</th><th>Status</th><th>Kode Industri</th><th>Kode Kategori</th><th>Kode Golongan</th></thead>";
                for (var i = 0; i < res.length; i++) {
                    eTable += "<tr>";
                    eTable += '<td>' + res[i]['kode_brng'] + '</td>';
                    eTable += '<td>' + res[i]['nama_brng'] + '</td>';
                    eTable += '<td>' + res[i]['kode_satbesar'] + '</td>';
                    eTable += '<td>' + res[i]['kode_sat'] + '</td>';
                    eTable += '<td>' + res[i]['letak_barang'] + '</td>';
                    eTable += '<td>' + res[i]['dasar'] + '</td>';
                    eTable += '<td>' + res[i]['h_beli'] + '</td>';
                    eTable += '<td>' + res[i]['ralan'] + '</td>';
                    eTable += '<td>' + res[i]['kelas1'] + '</td>';
                    eTable += '<td>' + res[i]['kelas2'] + '</td>';
                    eTable += '<td>' + res[i]['kelas3'] + '</td>';
                    eTable += '<td>' + res[i]['utama'] + '</td>';
                    eTable += '<td>' + res[i]['vip'] + '</td>';
                    eTable += '<td>' + res[i]['vvip'] + '</td>';
                    eTable += '<td>' + res[i]['beliluar'] + '</td>';
                    eTable += '<td>' + res[i]['jualbebas'] + '</td>';
                    eTable += '<td>' + res[i]['karyawan'] + '</td>';
                    eTable += '<td>' + res[i]['stokminimal'] + '</td>';
                    eTable += '<td>' + res[i]['kdjns'] + '</td>';
                    eTable += '<td>' + res[i]['isi'] + '</td>';
                    eTable += '<td>' + res[i]['kapasitas'] + '</td>';
                    eTable += '<td>' + res[i]['expire'] + '</td>';
                    eTable += '<td>' + res[i]['status'] + '</td>';
                    eTable += '<td>' + res[i]['kode_industri'] + '</td>';
                    eTable += '<td>' + res[i]['kode_kategori'] + '</td>';
                    eTable += '<td>' + res[i]['kode_golongan'] + '</td>';
                    eTable += "</tr>";
                }
                eTable += "</tbody></table></div>";
                $('#forTable_databarang').html(eTable);
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_databarang").modal('show');
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
        doc.text("Tabel Data Databarang", 20, 95, null, null, null);
        const totalPagesExp = "{total_pages_count_string}";        
        doc.autoTable({
            html: '#tbl_lihat_databarang',
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
        // doc.save('table_data_databarang.pdf');
        window.open(doc.output('bloburl'), '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
              
    })

    // ===========================================
    // Ketika tombol export xlsx di tekan
    // ===========================================
    $("#export_xlsx").click(function () {
        let tbl1 = document.getElementById("tbl_lihat_databarang");
        let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
        let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
        let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
        const new_workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data databarang");
        XLSX.writeFile(new_workbook, 'tmp_file.xls');
    })

    $("#view_chart").click(function () {
        window.open(mlite.url + '/databarang/chart?t=' + mlite.token, '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
    })   

    $(".datepicker").daterangepicker({
        singleDatePicker: true,
        showDropdowns: true,
        locale: {
            format: "YYYY-MM-DD",
        },
    });

    $('#dasar').on('keyup', function() {
        var dasar = $('#dasar').val();
        $('#h_beli').val(dasar);
        $('#ralan').val(dasar);
        $('#kelas1').val(dasar);
        $('#kelas2').val(dasar);
        $('#kelas3').val(dasar);
        $('#utama').val(dasar);
        $('#vip').val(dasar);
        $('#vvip').val(dasar);
        $('#beliluar').val(dasar);
        $('#jualbebas').val(dasar);
        $('#karyawan').val(dasar);
    })

});