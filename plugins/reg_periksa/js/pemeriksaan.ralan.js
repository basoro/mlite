jQuery().ready(function () {
    var var_tbl_pemeriksaan_ralan = $('#tbl_pemeriksaan_ralan').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'dom': 'Bfrtip',
        'responsive': true, 
        'searching': false,
        'select': true,
        'colReorder': true,
        "bInfo" : false,
        "ajax": {
            "url": "{?=url(['reg_periksa','datapemeriksaanralan'])?}",
            "dataType": "json",
            "type": "POST",
            "data": function (data) {

                data.no_rawat = $("a.active").attr('data-no_rawat');
                
            }
        },
        "columns": [
            { 'data': 'no_rawat' },
            { 'data': 'tgl_perawatan' },
            { 'data': 'jam_rawat' },
            { 'data': 'suhu_tubuh' },
            { 'data': 'tensi' },
            { 'data': 'nadi' },
            { 'data': 'respirasi' },
            { 'data': 'tinggi' },
            { 'data': 'berat' },
            { 'data': 'spo2' },
            { 'data': 'gcs' },
            { 'data': 'kesadaran' },
            { 'data': 'keluhan' },
            { 'data': 'pemeriksaan' },
            { 'data': 'alergi' },
            { 'data': 'lingkar_perut' },
            { 'data': 'rtl' },
            { 'data': 'penilaian' },
            { 'data': 'instruksi' },
            { 'data': 'evaluasi' },
            { 'data': 'nip' },
            { 'data': 'nama' }
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
            { 'targets': 21}
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
        selector: '#tbl_pemeriksaan_ralan tr', 
        trigger: 'right',
        callback: function(key, options) {
          var rowData = var_tbl_pemeriksaan_ralan.rows({ selected: true }).data()[0];
          if (rowData != null) {
            var no_rawat = rowData['no_rawat'];
            switch (key) {
                case 'detail' :
                OpenModal(mlite.url + '/pemeriksaan_ralan/detail/' + no_rawat + '?t=' + mlite.token);
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
        }
    });

    // ==============================================================
    // FORM VALIDASI
    // ==============================================================

    $("form[name='form_pemeriksaan_ralan']").validate({
        rules: {
no_rawat: 'required',
tgl_perawatan: 'required',
jam_rawat: 'required',
suhu_tubuh: 'required',
tensi: 'required',
nadi: 'required',
respirasi: 'required',
tinggi: 'required',
berat: 'required',
spo2: 'required',
gcs: 'required',
kesadaran: 'required',
keluhan: 'required',
pemeriksaan: 'required',
alergi: 'required',
lingkar_perut: 'required',
rtl: 'required',
penilaian: 'required',
instruksi: 'required',
evaluasi: 'required',
nip: 'required'

        },
        messages: {
no_rawat:'No Rawat tidak boleh kosong!',
tgl_perawatan:'Tgl Perawatan tidak boleh kosong!',
jam_rawat:'Jam Rawat tidak boleh kosong!',
suhu_tubuh:'Suhu Tubuh tidak boleh kosong!',
tensi:'Tensi tidak boleh kosong!',
nadi:'Nadi tidak boleh kosong!',
respirasi:'Respirasi tidak boleh kosong!',
tinggi:'Tinggi tidak boleh kosong!',
berat:'Berat tidak boleh kosong!',
spo2:'Spo2 tidak boleh kosong!',
gcs:'Gcs tidak boleh kosong!',
kesadaran:'Kesadaran tidak boleh kosong!',
keluhan:'Keluhan tidak boleh kosong!',
pemeriksaan:'Pemeriksaan tidak boleh kosong!',
alergi:'Alergi tidak boleh kosong!',
lingkar_perut:'Lingkar Perut tidak boleh kosong!',
rtl:'Rtl tidak boleh kosong!',
penilaian:'Penilaian tidak boleh kosong!',
instruksi:'Instruksi tidak boleh kosong!',
evaluasi:'Evaluasi tidak boleh kosong!',
nip:'Nip tidak boleh kosong!'

        },
        submitHandler: function (form) {
var no_rawat= $('#no_rawat').val();
var tgl_perawatan= $('#tgl_perawatan').val();
var jam_rawat= $('#jam_rawat').val();
var suhu_tubuh= $('#suhu_tubuh').val();
var tensi= $('#tensi').val();
var nadi= $('#nadi').val();
var respirasi= $('#respirasi').val();
var tinggi= $('#tinggi').val();
var berat= $('#berat').val();
var spo2= $('#spo2').val();
var gcs= $('#gcs').val();
var kesadaran= $('#kesadaran').val();
var keluhan= $('#keluhan').val();
var pemeriksaan= $('#pemeriksaan').val();
var alergi= $('#alergi').val();
var lingkar_perut= $('#lingkar_perut').val();
var rtl= $('#rtl').val();
var penilaian= $('#penilaian').val();
var instruksi= $('#instruksi').val();
var evaluasi= $('#evaluasi').val();
var nip= $('#nip').val();

 var typeact = $('#typeact').val();

 var formData = new FormData(form); // tambahan
 formData.append('typeact', typeact); // tambahan

            $.ajax({
                url: "{?=url(['pemeriksaan_ralan','aksi'])?}",
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
                    $("#modal_pemeriksaan_ralan").modal('hide');
                    var_tbl_pemeriksaan_ralan.draw();
                }
            })
        }
    });

    // ==============================================================
    // KETIKA TOMBOL SEARCH DITEKAN
    // ==============================================================
    $('#filter_search_pemeriksaan_ralan').click(function () {
        var_tbl_pemeriksaan_ralan.draw();
    });

    // ===========================================
    // KETIKA TOMBOL EDIT DITEKAN
    // ===========================================

    $("#edit_data_pemeriksaan_ralan").click(function () {
        var rowData = var_tbl_pemeriksaan_ralan.rows({ selected: true }).data()[0];
        if (rowData != null) {

            var no_rawat = rowData['no_rawat'];
var tgl_perawatan = rowData['tgl_perawatan'];
var jam_rawat = rowData['jam_rawat'];
var suhu_tubuh = rowData['suhu_tubuh'];
var tensi = rowData['tensi'];
var nadi = rowData['nadi'];
var respirasi = rowData['respirasi'];
var tinggi = rowData['tinggi'];
var berat = rowData['berat'];
var spo2 = rowData['spo2'];
var gcs = rowData['gcs'];
var kesadaran = rowData['kesadaran'];
var keluhan = rowData['keluhan'];
var pemeriksaan = rowData['pemeriksaan'];
var alergi = rowData['alergi'];
var lingkar_perut = rowData['lingkar_perut'];
var rtl = rowData['rtl'];
var penilaian = rowData['penilaian'];
var instruksi = rowData['instruksi'];
var evaluasi = rowData['evaluasi'];
var nip = rowData['nip'];



            $("#typeact").val("edit");
  
            $('#no_rawat').val(no_rawat);
$('#tgl_perawatan').val(tgl_perawatan);
$('#jam_rawat').val(jam_rawat);
$('#suhu_tubuh').val(suhu_tubuh);
$('#tensi').val(tensi);
$('#nadi').val(nadi);
$('#respirasi').val(respirasi);
$('#tinggi').val(tinggi);
$('#berat').val(berat);
$('#spo2').val(spo2);
$('#gcs').val(gcs);
$('#kesadaran').val(kesadaran).change();
$('#keluhan').val(keluhan);
$('#pemeriksaan').val(pemeriksaan);
$('#alergi').val(alergi);
$('#lingkar_perut').val(lingkar_perut);
$('#rtl').val(rtl);
$('#penilaian').val(penilaian);
$('#instruksi').val(instruksi);
$('#evaluasi').val(evaluasi);
$('#nip').val(nip).change();

            //$("#no_rawat").prop('disabled', true); // GA BISA DIEDIT KALI DISABLE
            $('#modal-title').text("Edit Data Pemeriksaan Ralan");
            $("#modal_pemeriksaan_ralan").modal('show');
        }
        else {
            bootbox.alert("Silakan pilih data yang akan di edit.");
        }

    });

    // ==============================================================
    // TOMBOL  DELETE DI CLICK
    // ==============================================================
    jQuery("#hapus_data_pemeriksaan_ralan").click(function () {
        var rowData = var_tbl_pemeriksaan_ralan.rows({ selected: true }).data()[0];


        if (rowData) {
var no_rawat = rowData['no_rawat'];
            bootbox.confirm('Anda yakin akan menghapus data dengan no_rawat="' + no_rawat, function(result) {
                if(result) {
                    $.ajax({
                        url: "{?=url(['pemeriksaan_ralan','aksi'])?}",
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
                            var_tbl_pemeriksaan_ralan.draw();
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
    jQuery("#tambah_data_pemeriksaan_ralan").click(function () {

        $('#no_rawat').val('');
// $('#tgl_perawatan').val('');
// $('#jam_rawat').val('');
$('#suhu_tubuh').val('');
$('#tensi').val('');
$('#nadi').val('');
$('#respirasi').val('');
$('#tinggi').val('');
$('#berat').val('');
$('#spo2').val('');
$('#gcs').val('');
$('#kesadaran').val('');
$('#keluhan').val('');
$('#pemeriksaan').val('');
$('#alergi').val('');
$('#lingkar_perut').val('');
$('#rtl').val('');
$('#penilaian').val('');
$('#instruksi').val('');
$('#evaluasi').val('');
$('#nip').find(':selected');


        $("#typeact").val("add");
        $("#no_rawat").prop('disabled', false);
        
        $('#modal-title').text("Tambah Data Pemeriksaan Ralan");
        $("#modal_pemeriksaan_ralan").modal('show');
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_pemeriksaan_ralan").click(function () {

        var search_field_pemeriksaan_ralan = $('#search_field_pemeriksaan_ralan').val();
        var search_text_pemeriksaan_ralan = $('#search_text_pemeriksaan_ralan').val();

        $.ajax({
            url: "{?=url(['pemeriksaan_ralan','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat', 
                search_field_pemeriksaan_ralan: search_field_pemeriksaan_ralan, 
                search_text_pemeriksaan_ralan: search_text_pemeriksaan_ralan
            },
            dataType: 'json',
            success: function (res) {
                var eTable = "<div class='table-responsive'><table id='tbl_lihat_pemeriksaan_ralan' class='table display dataTable' style='width:100%'><thead><th>No Rawat</th><th>Tgl Perawatan</th><th>Jam Rawat</th><th>Suhu Tubuh</th><th>Tensi</th><th>Nadi</th><th>Respirasi</th><th>Tinggi</th><th>Berat</th><th>Spo2</th><th>Gcs</th><th>Kesadaran</th><th>Keluhan</th><th>Pemeriksaan</th><th>Alergi</th><th>Lingkar Perut</th><th>Rtl</th><th>Penilaian</th><th>Instruksi</th><th>Evaluasi</th><th>Nip</th></thead>";
                for (var i = 0; i < res.length; i++) {
                    eTable += "<tr>";
                    eTable += '<td>' + res[i]['no_rawat'] + '</td>';
eTable += '<td>' + res[i]['tgl_perawatan'] + '</td>';
eTable += '<td>' + res[i]['jam_rawat'] + '</td>';
eTable += '<td>' + res[i]['suhu_tubuh'] + '</td>';
eTable += '<td>' + res[i]['tensi'] + '</td>';
eTable += '<td>' + res[i]['nadi'] + '</td>';
eTable += '<td>' + res[i]['respirasi'] + '</td>';
eTable += '<td>' + res[i]['tinggi'] + '</td>';
eTable += '<td>' + res[i]['berat'] + '</td>';
eTable += '<td>' + res[i]['spo2'] + '</td>';
eTable += '<td>' + res[i]['gcs'] + '</td>';
eTable += '<td>' + res[i]['kesadaran'] + '</td>';
eTable += '<td>' + res[i]['keluhan'] + '</td>';
eTable += '<td>' + res[i]['pemeriksaan'] + '</td>';
eTable += '<td>' + res[i]['alergi'] + '</td>';
eTable += '<td>' + res[i]['lingkar_perut'] + '</td>';
eTable += '<td>' + res[i]['rtl'] + '</td>';
eTable += '<td>' + res[i]['penilaian'] + '</td>';
eTable += '<td>' + res[i]['instruksi'] + '</td>';
eTable += '<td>' + res[i]['evaluasi'] + '</td>';
eTable += '<td>' + res[i]['nip'] + '</td>';
                    eTable += "</tr>";
                }
                eTable += "</tbody></table></div>";
                $('#forTable_pemeriksaan_ralan').html(eTable);
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_pemeriksaan_ralan").modal('show');
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
        doc.text("Tabel Data Pemeriksaan Ralan", 20, 95, null, null, null);
        const totalPagesExp = "{total_pages_count_string}";        
        doc.autoTable({
            html: '#tbl_lihat_pemeriksaan_ralan',
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
        // doc.save('table_data_pemeriksaan_ralan.pdf')
        window.open(doc.output('bloburl'), '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
              
    });

    // ===========================================
    // Ketika tombol export xlsx di tekan
    // ===========================================
    $("#export_xlsx").click(function () {
        let tbl1 = document.getElementById("tbl_lihat_pemeriksaan_ralan");
        let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
        let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
        let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
        const new_workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data pemeriksaan_ralan");
        XLSX.writeFile(new_workbook, 'tmp_file.xls');
    });

    $("#copy_soap").click(function (event) {
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

    });

    $("#copy_soap").click(function (event) {
        // alert('Test');
        // $(".modal-content").modal("show");
        var no_rawat = $('#no_rawat_pemeriksaan_ralan').val();
        event.preventDefault();
        var loadURL =  mlite.url + '/reg_periksa/copysoap/' + no_rawat.replace(/\//g,'') + '?t=' + mlite.token;;
    
        var modal = $('#modal_detail_pemeriksaan_ralan');
        var modalContent = $('#modal_detail_pemeriksaan_ralan .modal-content');
        // alert(modal);
    
        modal.off('show.bs.modal');
        modal.on('show.bs.modal', function () {
            modalContent.load(loadURL);
        }).modal('show');
        
        return false;
    
    });

    // $('.table_copy_soap').on('click', 'tr', function () {
    //     alert('Coba');
    //     $("#keluhan").val($(this).attr("data-keluhan"));
    // })


    // $("#table_copy_soap").delegate("tr", "click", function(){
    //     alert("Click!");
    // });    


});