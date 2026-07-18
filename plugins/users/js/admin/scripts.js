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
            "url": "{?=url([ADMIN,'users','data'])?}",
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
        "columns": [
{ 'data': 'id' },
{ 'data': 'username' },
{ 'data': 'fullname' },
{ 'data': 'description' },
// { 'data': 'password' },
{ 'data': 'avatar' },
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
{ 'targets': 8},
// { 'targets': 9}

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
        selector: '#tbl_mlite_users tbody tr', 
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
            let table = $('#tbl_mlite_users').DataTable();
            let data = table.row(this).data();
            
            // Handle menu actions
            switch(key) {
                case "individual_menu":
                    individual_menu(data);
                    break;
            }
        },
        items: {
            "individual_menu": {name: "Individual Menu", icon: "fa-edit"}
        }
    });

    // Add touch support for mobile devices
    $('#tbl_mlite_users tbody').on('touchstart', 'tr', function(e) {
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
id:'id tidak boleh kosong!',
username:'username tidak boleh kosong!',
fullname:'fullname tidak boleh kosong!',
description:'description tidak boleh kosong!',
password:'password tidak boleh kosong!',
avatar:'avatar tidak boleh kosong!',
email:'email tidak boleh kosong!',
role:'role tidak boleh kosong!',
cap:'cap tidak boleh kosong!',
access:'access tidak boleh kosong!'

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

 var formData = new FormData(form); // tambahan
 formData.append('typeact', typeact); // tambahan

            $.ajax({
                url: "{?=url([ADMIN,'users','aksi'])?}",
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
                            $("#modal_mlite_users").modal('hide');
                            var_tbl_mlite_users.draw();
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
    $("#search_mlite_users").click(function () {
        var_tbl_mlite_users.draw();
    });

    // ===========================================
    // Ketika tombol Edit di tekan
    // ===========================================

    $("#edit_data_mlite_users").click(function () {
        var rowData = var_tbl_mlite_users.rows({ selected: true }).data()[0];
        if (rowData != null) {

            var id = rowData['id'];
var username = rowData['username'];
var fullname = rowData['fullname'];
var description = rowData['description'];
var password = rowData['password'];
var avatar = rowData['avatar'];
var email = rowData['email'];
var role = rowData['role'];
var cap = rowData['cap'];
var access = rowData['access'];



            $("#typeact").val("edit");
  
            $('#id').val(id);
$('#username').val(username);
$('#fullname').val(fullname);
$('#description').val(description);
$('#password').val(password);
$('#avatar').val(avatar);
$('#email').val(email);
$('#role').val(role);
$('#cap').val(cap);
$('#access').val(access);

            $("#id").prop('readonly', true); // GA BISA DIEDIT KALAU READONLY
            $('#modal-title').text("Edit Data Users");
            $("#modal_mlite_users").modal();
        }
        else {
            alert("Silakan pilih data yang akan di edit.");
        }

    });

    // ==============================================================
    // TOMBOL  DELETE DI CLICK
    // ==============================================================
    jQuery("#hapus_data_mlite_users").click(function () {
        var rowData = var_tbl_mlite_users.rows({ selected: true }).data()[0];


        if (rowData) {
var id = rowData['id'];
            bootbox.confirm("Anda yakin akan menghapus data dengan id = " + id + "?", function(result) {
                if (result) {
                    $.ajax({
                        url: "{?=url([ADMIN,'users','aksi'])?}",
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
                                    var_tbl_mlite_users.draw();
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
        $('#search_text_mlite_users').val(searchParams.get('no_rawat'));
        var_tbl_mlite_users.draw();
        if(searchParams.get('modal') == 'true') {
            $("#modal_mlite_users").modal();
            $('#no_rawat').val(searchParams.get('no_rawat'));    
        }
    }

    jQuery("#tambah_data_mlite_users").click(function () {

        $('#id').val('');
$('#username').val('');
$('#fullname').val('');
$('#description').val('');
$('#password').val('');
$('#avatar').val('');
$('#email').val('');
$('#role').val('');
$('#cap').val('');
$('#access').val('');


        if(window.location.search.indexOf('no_rawat') !== -1) { 
            $('#no_rawat').val(searchParams.get('no_rawat'));
        }

        $("#typeact").val("add");
        $("#id").prop('disabled', false);
        
        $('#modal-title').text("Tambah Data Users");
        $("#modal_mlite_users").modal();
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_mlite_users").click(function () {

        var search_field_mlite_users = $('#search_field_mlite_users').val();
        var search_text_mlite_users = $('#search_text_mlite_users').val();

        $.ajax({
            url: "{?=url([ADMIN,'users','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat', 
                search_field_mlite_users: search_field_mlite_users, 
                search_text_mlite_users: search_text_mlite_users
            },
            dataType: 'json',
            success: function (res) {
                var eTable = "<div class='table-responsive'><table id='tbl_lihat_mlite_users' class='table display dataTable' style='width:100%'><thead><th>Id</th><th>Username</th><th>Fullname</th><th>Description</th><!--<th>Password</th>--><th>Avatar</th><th>Email</th><th>Role</th><th>Cap</th><th>Access</th></thead>";
                for (var i = 0; i < res.length; i++) {
                    eTable += "<tr>";
                    eTable += '<td>' + res[i]['id'] + '</td>';
eTable += '<td>' + res[i]['username'] + '</td>';
eTable += '<td>' + res[i]['fullname'] + '</td>';
eTable += '<td>' + res[i]['description'] + '</td>';
// eTable += '<td>' + res[i]['password'] + '</td>';
eTable += '<td>' + res[i]['avatar'] + '</td>';
eTable += '<td>' + res[i]['email'] + '</td>';
eTable += '<td>' + res[i]['role'] + '</td>';
eTable += '<td>' + res[i]['cap'] + '</td>';
eTable += '<td>' + res[i]['access'] + '</td>';
                    eTable += "</tr>";
                }
                eTable += "</tbody></table></div>";
                $('#forTable_mlite_users').html(eTable);
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_mlite_users").modal();
    });

    // ==============================================================
    // TOMBOL DETAIL mlite_users DI CLICK
    // ==============================================================
    jQuery("#lihat_detail_mlite_users").click(function (event) {

        var rowData = var_tbl_mlite_users.rows({ selected: true }).data()[0];

        if (rowData) {
var id = rowData['id'];
            var baseURL = mlite.url + '/' + mlite.admin;
            event.preventDefault();
            var loadURL =  baseURL + '/users/detail/' + id + '?t=' + mlite.token;
        
            var modal = $('#modal_detail_mlite_users');
            var modalContent = $('#modal_detail_mlite_users .modal-content');
        
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
        var img = "{?=base64_encode(file_get_contents(url(ADMIN . '/' . $settings['logo'])))?}";
        doc.addImage(img, 'JPEG', 20, 10, 50, 50);
        doc.setFontSize(20);
        doc.text("{$settings.nama_instansi}", 80, 35, null, null, null);
        doc.setFontSize(10);
        doc.text("{$settings.alamat} - {$settings.kota} - {$settings.propinsi}", 80, 46, null, null, null);
        doc.text("Telepon: {$settings.nomor_telepon} - Email: {$settings.email}", 80, 56, null, null, null);
        doc.line(20,70,572,70,null); /* doc.line(20,70,820,70,null); --> Jika landscape */
        doc.line(20,72,572,72,null); /* doc.line(20,72,820,72,null); --> Jika landscape */
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

    // ===========================================
    // Ketika tombol chart di tekan
    // ===========================================

    $("#view_chart").click(function () {
        var baseURL = mlite.url + '/' + mlite.admin;
        window.open(baseURL + '/users/chart?t=' + mlite.token, '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
    })   

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
                url: "{?=url([ADMIN,'users','aksimenu'])?}",
                method: "POST",
                contentType: false, // tambahan
                processData: false, // tambahan
                data: formData,
                success: function (data) {
                    console.log(data);
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

});

function individual_menu(rowData) {
    if (rowData != null) {
        var id = rowData['id'];
        var baseURL = mlite.url + '/' + mlite.admin;
        event.preventDefault();
        var loadURL =  baseURL + '/users/menu/' + id + '?t=' + mlite.token;
        window.location.href = loadURL; 
        return false;

    }
    else {
        alert("Silakan pilih data yang akan di edit.");
    }
}

// Avatar
var reader  = new FileReader();
reader.addEventListener("load", function() {
    $("#avatar").attr('src', reader.result);
}, false);
$("input[name=fileToUpload]").change(function() {
    reader.readAsDataURL(this.files[0]);
});
