jQuery().ready(function () {
    var var_tbl_mlite_users = $('#tbl_mlite_users').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'dom': 'Bfrtip',
        'searching': false,
        'select': true,
        'colReorder': true,
        "bInfo" : false,
        "ajax": {
            "url": "{?=url(['mlite_users','data'])?}",
            "dataType": "json",
            "type": "POST",
            "data": function (data) {

                // Read values
                var search_field_mlite_users = $('#search_field_mlite_users').val();
                var search_text_mlite_users = $('#search_text_mlite_users').val();
                
                data.search_field_mlite_users = search_field_mlite_users;
                data.search_text_mlite_users = search_text_mlite_users;
                
            }
        },
        "fnDrawCallback": function () {
            $('#more_data_mlite_users').on('click', function(e) {
                e.preventDefault();
                var clientX = e.originalEvent.clientX;
                var clientY = e.originalEvent.clientY;
                $('#tbl_mlite_users tr').contextMenu({x: clientX, y: clientY});
            });          
        },        
        "columns": [
            { 'data': 'id' },
            { 'data': 'username' },
            { 'data': 'fullname' },
            { 'data': 'description' },
            { 'data': 'avatar', 
                "render": function ( data) {
                    return data ? '<img src="' + mlite.url + '/uploads/users/' + data +'" width="25px">' : '<img src="' + mlite.url + '/plugins/mlite_users/img/default.png" width="25px">';
                }      
            },
            { 'data': 'email' },
            { 'data': 'role' },
            { 'data': 'cap' },
            { 'data': 'access' }
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
            { 'targets': 8}
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
        selector: '#tbl_mlite_users tr', 
        trigger: 'right',
        callback: function(key, options) {
          var rowData = var_tbl_mlite_users.rows({ selected: true }).data()[0];
          if (rowData != null) {
            var id = rowData['id'];
            switch (key) {
                case 'detail' :
                    OpenModal(mlite.url + '/mlite_users/detail/' + id + '?t=' + mlite.token);
                    break;
                case 'menu' :
                    window.location.href = mlite.url + '/mlite_users/menu/' + id + '?t=' + mlite.token;
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
            "menu": {name: "Menu Individual", "icon": "edit", disabled:  {$disabled_menu.read}}
        }
    });

    // ==============================================================
    // FORM VALIDASI
    // ==============================================================

    $("form[name='form_mlite_users']").validate({
        rules: {
id: 'required',
username: 'required',
fullname: 'required',
description: 'required',
password: 'required',
avatar: 'required',
email: 'required',
role: 'required',
cap: 'required',
access: 'required'

        },
        messages: {
id:'Id tidak boleh kosong!',
username:'Username tidak boleh kosong!',
fullname:'Fullname tidak boleh kosong!',
description:'Description tidak boleh kosong!',
password:'Password tidak boleh kosong!',
avatar:'Avatar tidak boleh kosong!',
email:'Email tidak boleh kosong!',
role:'Role tidak boleh kosong!',
cap:'Cap tidak boleh kosong!',
access:'Access tidak boleh kosong!'

        },
        submitHandler: function (form) {
 var id= $('#id').val();
var username= $('#username').val();
var fullname= $('#fullname').val();
var description= $('#description').val();
var password= $('#password').val();
var avatar= $('#avatar').val();
var email= $('#email').val();
var role= $('#role').val();
var cap= $('#cap').val();
var access= $('#access').val();

 var typeact = $('#typeact').val();

 const fileupload = $('#fileToUpload').prop('files')[0];
 var formData = new FormData(form); // tambahan
 formData.append('fileToUpload', fileToUpload);
 formData.append('typeact', typeact); // tambahan

            $.ajax({
                url: "{?=url(['mlite_users','aksi'])?}",
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
                            $("#modal_mlite_users").modal('hide');
                        } else {
                            bootbox.alert('<span class="text-danger">' + data.msg + '</span>');
                        }    
                    }
                    else if (typeact == "edit") {
                        if(data.status === 'success') {
                            bootbox.alert('<span class="text-success">' + data.msg + '</span>');
                            $("#modal_mlite_users").modal('hide');
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
                    var_tbl_mlite_users.draw();
                }
            })
        }
    });


    if(typeof ws != 'undefined' && typeof ws.readyState != 'undefined' && ws.readyState == 1){
        ws.onmessage = function(response){
            try{
                output = JSON.parse(response.data);
                if(output['action'] == 'add'){
                    var_tbl_permintaan_lab.draw();
                }
                if(output['action'] == 'edit'){
                    var_tbl_permintaan_lab.draw();
                }
                if(output['action'] == 'del'){
                    var_tbl_permintaan_lab.draw();
                }
            }catch(e){
                console.log(e);
            }
        }
    }

    $("form[name='form_mlite_users_menu']").validate({
        rules: {
            id: 'required'        },
        messages: {
            id:'id tidak boleh kosong!'
        },
        submitHandler: function (form) {
            var id= $('#id').val();
            var typeact = $('#typeact').val();
 
            var formData = new FormData(form); // tambahan
            formData.append('typeact', typeact); // tambahan

            $.ajax({
                url: "{?=url(['mlite_users','aksimenu'])?}",
                method: "POST",
                contentType: false, // tambahan
                processData: false, // tambahan
                data: formData,
                success: function (data) {
                    data = JSON.parse(data);
                    var audio = new Audio('{?=url()?}/assets/sound/' + data.status + '.mp3');
                    audio.play();
                    if(data.status === 'success') {
                        bootbox.alert('<span class="text-success">Data menu individula dengan nama pengguna ' + data.msg + ' telah ditambahkan.</span>');
                    } else {
                        bootbox.alert('<span class="text-danger">' + data.msg + '</span>');
                    }
                }
            });
        }
    });

    // ==============================================================
    // KETIKA TOMBOL SEARCH DITEKAN
    // ==============================================================
    $('#filter_search_mlite_users').click(function () {
        var_tbl_mlite_users.draw();
    });

    // ===========================================
    // KETIKA TOMBOL EDIT DITEKAN
    // ===========================================

    $("#edit_data_mlite_users").click(function () {
        var rowData = var_tbl_mlite_users.rows({ selected: true }).data()[0];
        if (rowData != null) {

            var id = rowData['id'];
            var username = rowData['username'];
            var fullname = rowData['fullname'];
            var description = rowData['description'];
            var password = '********';
            var avatar = rowData['avatar'];
            var email = rowData['email'];
            var role = rowData['role'];
            var cap = rowData['cap'];
            var access = rowData['access'];

            $("#typeact").val("edit");
  
            $('#id').val(id);
            $('#username').val(username).change();
            $('#fullname').val(fullname);
            $('#description').val(description);
            $('#password').val(password);

            if(avatar) {
                $("#avatar").attr('src', '{?=url()?}/uploads/users/' + avatar);
            } else {
                $("#avatar").attr('src', '{?=url()?}/plugins/mlite_users/img/default.png');
            }
            $('#email').val(email);
            $('#role').val(role).change();
            $.each(cap.split(","), function(i,e){
                $("#cap option[value='" + e + "']").prop("selected", true).change();
            });
            $.each(access.split(","), function(i,e){
                $("#access option[value='" + e + "']").prop("selected", true).change();
            });

            //$("#id").prop('disabled', true); // GA BISA DIEDIT KALI DISABLE
            $("#password").prop('disabled', true); // GA BISA DIEDIT KALI DISABLE
            $('#modal-title').text("Edit Data Mlite Users");
            $("#modal_mlite_users").modal('show');
        }
        else {
            bootbox.alert("Silakan pilih data yang akan di edit.");
        }

    });

    // ==============================================================
    // TOMBOL  DELETE DI CLICK
    // ==============================================================
    jQuery("#hapus_data_mlite_users").click(function () {
        var rowData = var_tbl_mlite_users.rows({ selected: true }).data()[0];


        if (rowData) {
var id = rowData['id'];
            bootbox.confirm('Anda yakin akan menghapus data dengan id="' + id, function(result) {
                if(result) {
                    $.ajax({
                        url: "{?=url(['mlite_users','aksi'])?}",
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
                            var_tbl_mlite_users.draw();
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
    jQuery("#tambah_data_mlite_users").click(function () {

        $('#id').val('');
        $('#username').val('');
        $('#description').val('');
        $('#password').val('');
        $("#avatar").attr('src', '{?=url()?}/plugins/mlite_users/img/default.png');
        $('#email').val('');
        $('#role').val('');
        $('#cap').val('');
        $('#access').val('');

        $('#username').change(function(){
            $('#fullname').val($(this).find(':selected').text());
        });

        $("#typeact").val("add");
        $("#id").prop('disabled', false);
        $("#password").prop('disabled', false);
        $('#passwordChange').hide();
        
        $('#modal-title').text("Tambah Data Mlite Users");
        $("#modal_mlite_users").modal('show');
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_mlite_users").click(function () {

        var search_field_mlite_users = $('#search_field_mlite_users').val();
        var search_text_mlite_users = $('#search_text_mlite_users').val();

        $.ajax({
            url: "{?=url(['mlite_users','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat', 
                search_field_mlite_users: search_field_mlite_users, 
                search_text_mlite_users: search_text_mlite_users
            },
            dataType: 'json',
            success: function (res) {
                console.log('opo');
                var eTable = "<div class='table-responsive'><table id='tbl_lihat_mlite_users' class='table display dataTable' style='width:100%'><thead><th>Id</th><th>Username</th><th>Fullname</th><th>Description</th><th>Avatar</th><th>Email</th><th>Role</th><th>Cap</th></thead>";
                for (var i = 0; i < res.length; i++) {
                    eTable += "<tr>";
                    eTable += '<td>' + res[i]['id'] + '</td>';
                    eTable += '<td>' + res[i]['username'] + '</td>';
                    eTable += '<td>' + res[i]['fullname'] + '</td>';
                    eTable += '<td>' + res[i]['description'] + '</td>';
                    eTable += '<td><img src="data:image/png;base64,' + res[i]['avatar'] + '" width="30"></td>';
                    eTable += '<td>' + res[i]['email'] + '</td>';
                    eTable += '<td>' + res[i]['role'] + '</td>';
                    eTable += '<td>' + res[i]['cap'] + '</td>';
                    eTable += "</tr>";
                }
                eTable += "</tbody></table></div>";
                $('#forTable_mlite_users').html(eTable);
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_mlite_users").modal('show');
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
        doc.text("Tabel Data Mlite Users", 20, 95, null, null, null);
        const totalPagesExp = "{total_pages_count_string}";        
        doc.autoTable({
            html: '#tbl_lihat_mlite_users',
            startY: 105,
            margin: {
                left: 20, 
                right: 20
            }, 
            styles: {
                fontSize: 10,
                cellPadding: 5
            }, 
            didDrawCell: function(data) {
                if (data.column.index === 4 && data.cell.section === 'body') {
                   var td = data.cell.raw;
                   var img = td.getElementsByTagName('img')[0];
                   var dim = data.cell.height - data.cell.padding('vertical');
                   var textPos = data.cell;
                   doc.addImage(img.src, textPos.x,  textPos.y, dim, dim);
                }
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
        // doc.save('table_data_mlite_users.pdf')
        window.open(doc.output('bloburl'), '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
              
    })

    // ===========================================
    // Ketika tombol export xlsx di tekan
    // ===========================================
    $("#export_xlsx").click(function () {
        let tbl1 = document.getElementById("tbl_lihat_mlite_users");
        let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
        let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
        let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
        const new_workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data mlite_users");
        XLSX.writeFile(new_workbook, 'tmp_file.xls');
    })

	// Avatar
	var reader  = new FileReader();
	reader.addEventListener("load", function() {
		$("#avatar").attr('src', reader.result);
	}, false);
	$("input[name=fileToUpload]").change(function() {
		reader.readAsDataURL(this.files[0]);
	});

	// Password
	$("#passwordChange").on("click", function() {
		$("input[name=password]").val("").attr('disabled', false);
		$(this).remove();
		return false;
	})

    $('#tbl_mlite_users_menu').DataTable({
        "pageLength":'1000',
        "lengthChange": false,
    });


});