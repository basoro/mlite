jQuery().ready(function () {
    var var_tbl_mlite_api_key = $('#tbl_mlite_api_key').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'dom': 'Bfrtip',
        'searching': false,
        'select': true,
        'colReorder': true,
        "bInfo" : false,
        "ajax": {
            "url": "{?=url([ADMIN,'mlite_api_key','data'])?}",
            "dataType": "json",
            "type": "POST",
            "data": function (data) {

                // Read values
                var search_field_mlite_api_key = $('#search_field_mlite_api_key').val();
                var search_text_mlite_api_key = $('#search_text_mlite_api_key').val();
                
                data.search_field_mlite_api_key = search_field_mlite_api_key;
                data.search_text_mlite_api_key = search_text_mlite_api_key;
                
            }
        },
        "columns": [
{ 'data': 'id' },
{ 'data': 'api_key' },
{ 'data': 'username' },
{ 'data': 'method' },
{ 'data': 'ip_range' },
{ 'data': 'exp_time' }

        ],
        "columnDefs": [
{ 'targets': 0},
{ 'targets': 1},
{ 'targets': 2},
{ 'targets': 3},
{ 'targets': 4},
{ 'targets': 5}

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

    // Context menu configuration
    $.contextMenu({
        selector: '#tbl_mlite_api_key tbody tr', 
        trigger: 'right', // for right-click
        events: {
            show: function(options) {
                // Highlight the selected row
                $(this).addClass('selected');
            },
            hide: function(options) {
                // Remove highlight
                $(this).removeClass('selected');
            }
        },
        callback: function(key, options) {
            // Get the data from the selected row
            let table = $('#tbl_mlite_api_key').DataTable();
            let data = table.row(this).data();
            
            // Handle menu actions
            switch(key) {
                case "edit":
                    $('#edit_data_mlite_api_key').trigger('click');
                    break;
                case "delete":
                    $('#hapus_data_mlite_api_key').trigger('click');
                    break;
                case "detail":
                    $('#lihat_detail_mlite_api_key').trigger('click');
                    break;
            }
        },
        items: {
            "edit": {name: "Edit", icon: "fa-edit"},
            "delete": {name: "Hapus", icon: "fa-trash"},
            "detail": {name: "Detail", icon: "fa-eye"},
            "sep1": "---------",
            "quit": {name: "Tutup", icon: "fa-close"}
        }
    });

    // Add touch support for mobile devices
    $('#tbl_mlite_api_key tbody').on('touchstart', 'tr', function(e) {
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

    $("form[name='form_mlite_api_key']").validate({
        rules: {
id: 'required',
api_key: 'required',
username: 'required',
method: 'required',
ip_range: 'required',
exp_time: 'required'

        },
        messages: {
id:'id tidak boleh kosong!',
api_key:'api_key tidak boleh kosong!',
username:'username tidak boleh kosong!',
method:'method tidak boleh kosong!',
ip_range:'ip_range tidak boleh kosong!',
exp_time:'exp_time tidak boleh kosong!'

        },
        submitHandler: function (form) {
 var id= $('#id').val();
var api_key= $('#api_key').val();
var username= $('#username').val();
var method= $('#method').val();
var ip_range= $('#ip_range').val();
var exp_time= $('#exp_time').val();

 var typeact = $('#typeact').val();

 var formData = new FormData(form); // tambahan
 formData.append('typeact', typeact); // tambahan

            $.ajax({
                url: "{?=url([ADMIN,'mlite_api_key','aksi'])?}",
                method: "POST",
                contentType: false, // tambahan
                processData: false, // tambahan
                data: formData,
                success: function (data) {
                    try {
                        data = JSON.parse(data);
                        var audio = new Audio('{?=url()?}/assets/sound/' + data.status + '.mp3');
                        audio.play();
                        if (data.status === "success") {
                            bootbox.alert(data.message);
                            $("#modal_mlite_api_key").modal('hide');
                            var_tbl_mlite_api_key.draw();
                        } else {
                            bootbox.alert("Gagal: " + data.message);
                        }
                    } catch (e) {
                        bootbox.alert("Terjadi kesalahan saat memproses respons server.");
                    }
                }
            })
        }
    });

    // ==============================================================
    // CLICK ICON SEARCH DI INPUT SEARCH
    // ==============================================================
    $("#search_mlite_api_key").click(function () {
        var_tbl_mlite_api_key.draw();
    });

    // ===========================================
    // Ketika tombol Edit di tekan
    // ===========================================

    $("#edit_data_mlite_api_key").click(function () {
        var rowData = var_tbl_mlite_api_key.rows({ selected: true }).data()[0];
        if (rowData != null) {

            var id = rowData['id'];
var api_key = rowData['api_key'];
var username = rowData['username'];
var method = rowData['method'];
var ip_range = rowData['ip_range'];
var exp_time = rowData['exp_time'];



            $("#typeact").val("edit");
  
            $('#id').val(id);
$('#api_key').val(api_key);
$('#username').val(username).change();

$.each(method.split(","), function(i,e){
    $("#method option[value='" + e + "']").prop("selected", true).change();
});

$('#ip_range').val(ip_range);
$('#exp_time').val(exp_time);


            $("#id").prop('readonly', true); // GA BISA DIEDIT KALAU READONLY
            $('#modal-title').text("Edit Data mLITE API Key");
            $("#modal_mlite_api_key").modal();
        }
        else {
            alert("Silakan pilih data yang akan di edit.");
        }

    });

    // ==============================================================
    // TOMBOL  DELETE DI CLICK
    // ==============================================================
    jQuery("#hapus_data_mlite_api_key").click(function () {
        var rowData = var_tbl_mlite_api_key.rows({ selected: true }).data()[0];


        if (rowData) {
var id = rowData['id'];
            bootbox.confirm("Anda yakin akan menghapus data dengan id = " + id + "?", function(result) {
                if (result) {
                    $.ajax({
                        url: "{?=url([ADMIN,'mlite_api_key','aksi'])?}",
                        method: "POST",
                        data: {
                            id: id,
                            typeact: 'del'
                        },
                        success: function (data) {
                            try {
                                data = JSON.parse(data);
                                var audio = new Audio('{?=url()?}/assets/sound/' + data.status + '.mp3');
                                audio.play();
                                bootbox.alert(data.message);
                                if(data.status === 'success') {
                                    var_tbl_mlite_api_key.draw();
                                }
                            } catch (e) {
                                bootbox.alert("Terjadi kesalahan saat menghapus.");
                            }
                        },
                        error: function () {
                            bootbox.alert("Gagal terhubung ke server.");
                        }
                    });
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

    if(window.location.search.indexOf('no_rawat') !== -1) { 
        let searchParams = new URLSearchParams(window.location.search)
        $('#search_text_mlite_api_key').val(searchParams.get('no_rawat'));
        var_tbl_mlite_api_key.draw();
        if(searchParams.get('modal') == 'true') {
            $("#modal_mlite_api_key").modal();
            $('#no_rawat').val(searchParams.get('no_rawat'));    
        }
    }

    jQuery("#tambah_data_mlite_api_key").click(function () {

        $('#id').val('');
$('#api_key').val('{$apikey}');
$('#username').val('');
$('#method').val('');
$('#ip_range').val('*');
$('#exp_time').val('');


        if(window.location.search.indexOf('no_rawat') !== -1) { 
            $('#no_rawat').val(searchParams.get('no_rawat'));
        }

        $("#typeact").val("add");
        $("#id").prop('disabled', false);
        
        $('#modal-title').text("Tambah Data mLITE API Key");
        $("#modal_mlite_api_key").modal();
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_mlite_api_key").click(function () {

        var search_field_mlite_api_key = $('#search_field_mlite_api_key').val();
        var search_text_mlite_api_key = $('#search_text_mlite_api_key').val();

        $.ajax({
            url: "{?=url([ADMIN,'mlite_api_key','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat', 
                search_field_mlite_api_key: search_field_mlite_api_key, 
                search_text_mlite_api_key: search_text_mlite_api_key
            },
            dataType: 'json',
            success: function (res) {
                var eTable = "<div class='table-responsive'><table id='tbl_lihat_mlite_api_key' class='table display dataTable' style='width:100%'><thead><th>Id</th><th>Api Key</th><th>Username</th><th>Method</th><th>Ip Range</th><th>Exp Time</th></thead>";
                for (var i = 0; i < res.length; i++) {
                    eTable += "<tr>";
                    eTable += '<td>' + res[i]['id'] + '</td>';
eTable += '<td>' + res[i]['api_key'] + '</td>';
eTable += '<td>' + res[i]['username'] + '</td>';
eTable += '<td>' + res[i]['method'] + '</td>';
eTable += '<td>' + res[i]['ip_range'] + '</td>';
eTable += '<td>' + res[i]['exp_time'] + '</td>';
                    eTable += "</tr>";
                }
                eTable += "</tbody></table></div>";
                $('#forTable_mlite_api_key').html(eTable);
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_mlite_api_key").modal();
    });

    // ==============================================================
    // TOMBOL DETAIL mlite_api_key DI CLICK
    // ==============================================================
    jQuery("#lihat_detail_mlite_api_key").click(function (event) {

        var rowData = var_tbl_mlite_api_key.rows({ selected: true }).data()[0];

        if (rowData) {
var id = rowData['id'];
            var baseURL = mlite.url + '/' + mlite.admin;
            event.preventDefault();
            var loadURL =  baseURL + '/mlite_api_key/detail/' + id + '?t=' + mlite.token;
        
            var modal = $('#modal_detail_mlite_api_key');
            var modalContent = $('#modal_detail_mlite_api_key .modal-content');
        
            modal.off('show.bs.modal');
            modal.on('show.bs.modal', function () {
                modalContent.load(loadURL);
            }).modal();
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
        doc.text("Tabel Data Mlite Api Key", 20, 95, null, null, null);
        const totalPagesExp = "{total_pages_count_string}";        
        doc.autoTable({
            html: '#tbl_lihat_mlite_api_key',
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
        // doc.save('table_data_mlite_api_key.pdf')
        window.open(doc.output('bloburl'), '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
              
    })

    // ===========================================
    // Ketika tombol export xlsx di tekan
    // ===========================================
    $("#export_xlsx").click(function () {
        let tbl1 = document.getElementById("tbl_lihat_mlite_api_key");
        let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
        let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
        let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
        const new_workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data mlite_api_key");
        XLSX.writeFile(new_workbook, 'tmp_file.xls');
    })

    // ===========================================
    // Ketika tombol chart di tekan
    // ===========================================

    $("#view_chart").click(function () {
        var baseURL = mlite.url + '/' + mlite.admin;
        window.open(baseURL + '/mlite_api_key/chart?t=' + mlite.token, '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
    })   


    $('.tanggaljam').datetimepicker({
        format: 'YYYY-MM-DD HH:mm:ss',
        locale: 'id'
    });
    
});

$(document).ready(function() {
    $('.--add-parameter').on('click', function(e) {

        e.preventDefault();

        var initial = $(this).attr('data-parameter');

        $(
            '<div class="row">' +
                '<div class="col-md-4" style="margin-bottom: 10px;">' +
                    '<input type="text" name="' + initial + '_key[]" class="form-control param-' + initial + '-key" placeholder="Key" />' +
                '</div>' +
                '<div class="col-md-8" style="margin-bottom: 10px;">' +
                    '<div class="input-group">' +
                        '<input type="text" name="' + initial + '_value[]" class="form-control param-' + initial + '-value" placeholder="Value" />' +
                        '<span class="input-group-btn">' +
                            '<button type="button" class="btn btn-default btn-sm" onclick="$(this).closest(\'.row\').remove()">' +
                                '<i class="fa fa-close"></i>' +
                            '</button>' +
                        '</span>' +
                    '</div>' +
                '</div>' +
            '</div>'
        )
        .insertBefore($(this))
    }),
    
    $('.--api-debug').on('submit', function(e) {
        e.preventDefault();

        $('.response-result').trigger('click');
        
        if (! $(this).find('input[name=url]').val()) {
            $('pre code').text(JSON.stringify({error: "No service URL are given"}, null, 4));
            Prism.highlightAll();
            
            return;
        }
        
        let header = {},
            body = {},
            method = $(this).find('select[name=method]').val(),
            parameter = new FormData(this);

        $('[name^=header_key]').each(function(index) {
            let key = $(this).val().trim();
            let val = $('[name^=header_value]').eq(index).val().trim();
            if (key !== '') {
                header[key] = val;
                console.log('Header param:', key, '=', val);
            }
        });
        
        $('[name^=body_key]').each(function(index) {
            let key = $(this).val().trim();
            let val = $('[name^=body_value]').eq(index).val().trim();
            if (key !== '') {
                body[key] = val;
                console.log('Body param:', key, '=', val);
            }
        });

        $.ajax({
            url: $(this).find('input[name=url]').val(),
            method: method,
            data: body,
            headers: header,
            beforeSend: function() {
                $('pre code').text('Requesting...'),
                $('.result-html').html('')
            }
        })
        .always(function(response, status, error) {
            if (typeof response !== 'object') {
                response = {
                    error: 'The response is not a valid object'
                };
            }
            
            $('pre code')
                .removeClass()
                .addClass('language-javascript')
                .text(JSON.stringify((response.responseJSON || response), null, 4));

            Prism.highlightAll(); // atau Prism.highlightElement($('pre code')[0]);

        })
    })
})

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.toggle-submenu').forEach(function (toggleLink) {
    toggleLink.addEventListener('click', function (e) {
        e.preventDefault();
        const li = this.closest('li');
        li.classList.toggle('open');
    });
    });
});
