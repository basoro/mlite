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
            "url": "{?=url(['mlite_api_tools','data'])?}",
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
        selector: '#tbl_mlite_api_key tr', 
        trigger: 'right',
        callback: function(key, options) {
          var rowData = var_tbl_mlite_api_key.rows({ selected: true }).data()[0];
          if (rowData != null) {
            var id = rowData['id'];
            switch (key) {
                case 'detail' :
                OpenModal(mlite.url + '/mlite_api_tools/detail/' + id + '?t=' + mlite.token);
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

    $("form[name='form_mlite_api_key']").validate({
        rules: {
            api_key: 'required',
            username: 'required',
            method: 'required',
            ip_range: 'required',
            exp_time: 'required'
        },
        messages: {
            api_key:'Api Key tidak boleh kosong!',
            username:'Username tidak boleh kosong!',
            method:'Method tidak boleh kosong!',
            ip_range:'Ip Range tidak boleh kosong!',
            exp_time:'Exp Time tidak boleh kosong!'
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
                url: "{?=url(['mlite_api_tools','aksi'])?}",
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
                    $("#modal_mlite_api_key").modal('hide');
                    var_tbl_mlite_api_key.draw();
                }
            })
        }
    });

    // ==============================================================
    // KETIKA TOMBOL SEARCH DITEKAN
    // ==============================================================
    $('#filter_search_mlite_api_key').click(function () {
        var_tbl_mlite_api_key.draw();
    });

    // ===========================================
    // KETIKA TOMBOL EDIT DITEKAN
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

            $("#id").prop('readonly', true); // GA BISA DIEDIT KALI READONLY
            $('#modal-title').text("Edit Data Mlite Api Tools");
            $("#modal_mlite_api_key").modal('show');
        }
        else {
            bootbox.alert("Silakan pilih data yang akan di edit.");
        }

    });

    // ==============================================================
    // TOMBOL  DELETE DI CLICK
    // ==============================================================
    jQuery("#hapus_data_mlite_api_key").click(function () {
        var rowData = var_tbl_mlite_api_key.rows({ selected: true }).data()[0];


        if (rowData) {
            var id = rowData['id'];
            bootbox.confirm('Anda yakin akan menghapus data dengan id="' + id, function(result) {
                if(result) {
                    $.ajax({
                        url: "{?=url(['mlite_api_tools','aksi'])?}",
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
                            var_tbl_mlite_api_key.draw();
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
    jQuery("#tambah_data_mlite_api_key").click(function () {

        $('#id').val('');
        $('#api_key').val('{$apikey}');
        $('#username').val('').change();
        $('#method').val('').change();
        $('#ip_range').val('*');
        $('#exp_time').val('');

        $("#typeact").val("add");
        $("#id").prop('disabled', false);
        
        $('#modal-title').text("Tambah Data Mlite Api Tools");
        $("#modal_mlite_api_key").modal('show');
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_mlite_api_key").click(function () {

        var search_field_mlite_api_key = $('#search_field_mlite_api_key').val();
        var search_text_mlite_api_key = $('#search_text_mlite_api_key').val();

        $.ajax({
            url: "{?=url(['mlite_api_tools','aksi'])?}",
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
        $("#modal_lihat_mlite_api_key").modal('show');
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
                doc.text(`© ${new Date().getFullYear()} {$settings.nama_instansi}.`, data.settings.margin.left, doc.internal.pageSize.height - 10);                
                doc.text(footerStr, data.settings.margin.left + 480, doc.internal.pageSize.height - 10);
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

    $(document).ready(function() {
        $('.--add-parameter').on('click', function(e) {
            e.preventDefault();

            var initial = $(this).attr('data-parameter');

            $(
                '<div class="row">' +
                    '<div class="text-muted col-6 col-md-4">' +
                        '<div class="mb-3">' +
                            '<input type="text" name="' + initial + '_key[]" class="form-control form-control-sm param-' + initial + '-key" placeholder="Key" />' +
                        '</div>' +
                    '</div>' +
                    '<div class="text-muted col-6 col-md-6 ps-0">' +
                        '<div class="mb-3">' +
                            '<div class="input-group">' +
                                '<input type="text" name="' + initial + '_value[]" class="form-control form-control-sm param-' + initial + '-value" placeholder="Value" />' +
                                '<button type="button" class="btn btn-secondary btn-sm" onclick="$(this).closest(\'.row\').remove()">' +
                                    '<i class="ri-close-line"></i>' +
                                '</button>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                '</div>'
            )
            .insertBefore($(this))
        }),
        
        $('.--api-debug').on('submit', function(e) {
            e.preventDefault();

            $('.mdi.mdi-send').removeClass('mdi-send').addClass('mdi-loading mdi-spin');
            $('.response-result').trigger('click');
            
            if (! $(this).find('input[name=url]').val()) {
                $('.mdi.mdi-loading.mdi-spin').removeClass('mdi-loading mdi-spin').addClass('mdi-send');
                $('pre code').text(JSON.stringify({error: "No service URL are given"}, null, 4));
                Prism.highlightAll();
                
                return;
            }
            
            let header = {},
                body = {},
                method = $(this).find('select[name=method]').val(),
                parameter = new FormData(this);
            
            $('.param-header-key').each(function(num, value) {
                let key = $(this).val(),
                    val = $('.param-header-value:eq(' + num + ')').val();
                if (val) {
                    header[key] = val;
                }
            });
            
            $('.param-body-key').each(function(num, value) {
                let key = $(this).val(),
                    val = $('.param-body-value:eq(' + num + ')').val();
                if (val) {
                    body[key] = val;
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
                
                $('.mdi.mdi-loading.mdi-spin').removeClass('mdi-loading mdi-spin').addClass('mdi-send');
                $('pre code').text(JSON.stringify((typeof response.responseJSON !== 'undefined' ? response.responseJSON : response), null, 4));
                Prism.highlightAll();

            })
        })
    })

    $(".datetimepicker").daterangepicker({
        timePicker: true,
        use24hours: true,
        showMeridian: false, 
        singleDatePicker: true,
        showDropdowns: true,
        locale: {
            format: "YYYY-MM-DD hh:mm:ss",
        }
    });      

    if (typeof $.fn.slimScroll != 'undefined') {
        var height = ($(window).height() - 140);
        var $el = $('.sidebar-api');
    
        $el.slimscroll({
            height: height + "px",
            color: 'rgba(0,0,0,0.5)',
            size: '4px',
            alwaysVisible: false,
            borderRadius: '0',
            railBorderRadius: '0'
        });
    
        //Scroll active menu item when page load, if option set = true
        var item = $('.sidebarApiMenuScroll .sidebar-api li.active-api')[0];
        // console.log(item);
        if (item) {
            var activeItemOffsetTop = item.offsetTop;
            if (activeItemOffsetTop > 50) $el.slimscroll({ scrollTo: activeItemOffsetTop + 'px' });
        }
    }

});