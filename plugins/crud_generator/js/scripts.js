function titleCase(str) {
    var splitStr = str.toLowerCase().split(' ');
    for (var i = 0; i < splitStr.length; i++) {
        // You do not need to check if i is larger than splitStr length, as your for does that for you
        // Assign it back to the array
        splitStr[i] = splitStr[i].charAt(0).toUpperCase() + splitStr[i].substring(1);     
    }
    // Directly return the joined string
    return splitStr.join(' '); 
}

$(document).ready(function () {
    $("#open").click(function() {
        $.ajax({
            url : "{?=url()?}/plugins/crud_generator/src/javascript.tpl",
            dataType: "text",
            success : function (data) { $("#t4_javascript").text(data); }
        });
        $.ajax({
            url : "{?=url()?}/plugins/crud_generator/src/Info.tpl",
            dataType: "text",
            success : function (data) { $("#t4_info").text(data); }
        });
        $.ajax({
            url : "{?=url()?}/plugins/crud_generator/src/Admin.tpl",
            dataType: "text",
            success : function (data) { $("#t4_index").text(data); }
        });
        $.ajax({
            url : "{?=url()?}/plugins/crud_generator/src/manage.tpl",
            dataType: "text",
            success : function (data) { $("#t4_view_data").text(data); }
        });
        $.ajax({
            url : "{?=url()?}/plugins/crud_generator/src/detail.tpl",
            dataType: "text",
            success : function (data) { $("#t4_view_detail").text(data); }
        });
        $.ajax({
            url : "{?=url()?}/plugins/crud_generator/src/chart.tpl",
            dataType: "text",
            success : function (data) { $("#t4_view_chart").text(data); }
        });
        $.ajax({
            url : "{?=url()?}/plugins/crud_generator/src/style.tpl",
            dataType: "text",
            success : function (data) { $("#t4_styles").text(data); }
        });
        $.ajax({
            url : "{?=url()?}/plugins/crud_generator/src/snippets.tpl",
            dataType: "text",
            success : function (data) { $("#t4_snippets").text(data); }
        });
    });

    $("#database").click(function() {
        $('#table_txt').selectator('destroy');
        $.ajax({
        url: "{?=url(['crud_generator','database'])?}",
        type: "POST",
        data: {
        },
        dataType: 'json',
            success: function (res) {
                console.log(res);
                var $select_table = $('#table_txt');                        
                $select_table.find('option').remove();  
                for (var i = 0; i < res.length; i++) {
                    $select_table.append('<option value="' + res[i]['TABLE_NAME'] + '">' + res[i]['TABLE_NAME'] + '</option>');
                }
                $('#table_txt').selectator();
            }
        });
    });

    $("#database_exclude").click(function() {
        $('#table_txt').selectator('destroy');
        $.ajax({
        url: "{?=url(['crud_generator','databaseexclude'])?}",
        type: "POST",
        data: {
        },
        dataType: 'json',
            success: function (res) {
                console.log(res);
                var $select_table = $('#table_txt');                        
                $select_table.find('option').remove();  
                for (var i = 0; i < res.length; i++) {
                    $select_table.append('<option value="' + res[i]['TABLE_NAME'] + '">' + res[i]['TABLE_NAME'] + '</option>');
                }
                $('#table_txt').selectator();
            }
        });
    });

    $('#table_txt').on('change', function() {
        $('#fields_txt').selectator('destroy');
        $('#fields_chart').selectator('destroy');
        var nama_table = $("#table_txt").val();
        $('#module_txt').val(titleCase(nama_table.replace(/_/g, ' ')));
        $.ajax({
            url: "{?=url(['crud_generator','table'])?}",
            type: "POST",
            data: {
                nama_table: nama_table 
            },
            dataType: 'json',
            success: function (res) {
                console.log(res);
                var $select_field = $('#fields_txt');
                var $select_chart = $('#fields_chart'); 
                $select_field.find('option').remove();  
                $select_chart.find('option').remove();  
                for (var i = 0; i < res.length; i++) {
                    $select_field.append('<option value="' + res[i]['COLUMN_NAME'] + '">' + res[i]['COLUMN_NAME'] + '</option>');
                    $select_chart.append('<option value="' + res[i]['COLUMN_NAME'] + '">' + res[i]['COLUMN_NAME'] + '</option>');
                }
                $('#fields_txt').selectator();
                $('#fields_chart').selectator();
            }
        });
    });

    $("#generate").click(function() {

        var nama_module = $("#module_txt").val(); 
        var icon_module = $("#icon_txt").val(); 
        var nama_table = $("#table_txt").val();
        var nama_field = document.getElementById('fields_txt');

        // ========= BAGIAN INFO =========//
        var text = $("#t4_info").text();

        var table_txt = $("#table_txt").val().replace(/_/g, ' ');

        text = text.replace('MODULE_NAME', nama_module);

        text = text.replace('MODULE_ICON', icon_module);
        
        text = text.replace('MODULE_DESCRIPTION', table_txt);
        
        $("#t4_info").text(text); 

        // ========== BAGIAN INDEX =========//
        var text_index = $("#t4_index").text();

        text_index = text_index.replace(/NAMA_TABLE/g, nama_table);

        text_index = text_index.replace(/MODULE_NAME_CLASS/g, nama_module.replace(/ /g, '_'));
        text_index = text_index.replace(/MODULE_NAME/g, nama_module.toLowerCase().replace(/ /g, '_'));
        
        // loadData //
        var isi = "";

        text_index = text_index.replaceAll("$$$$", nama_table);

        for (i = 0; i < nama_field.options.length; i++) {
        if(i == nama_field.options.length-1){
            isi = isi + "'" + nama_field.options[i].value + "'=>$row['" + nama_field.options[i].value + "']\n" ;
        }
        else
        {
            isi = isi + "'" + nama_field.options[i].value + "'=>$row['" + nama_field.options[i].value + "'],\n" ;
        }
        }

        text_index = text_index.replace('ISI_LOAD_DATA', isi);

        // postAksi //
        text_index = text_index.replace(/NAMA_TABLE/g, nama_table);

        var ISI_TEMPAT_VALUES = "";
        var ISI_ISI_VALUES = "";
        var ISI_TIPE_VARIABLE = "";
        var ISI_VARIABLE_TIPE_ADD = "";
        var ISI_VALUES_EDIT = "";
        var ISI_LIHAT_ISI = "";
        var ISI_POST_VARIABLE = "";

        var ISI_WHERE_EDIT = "";
        var ISI_POST_DELETE = "";
        var ISI_WHERE_DELETE = "";
        var ISI_VALUES_ISI_EDIT = "";

        for (i = 0; i < nama_field.options.length; i++) {

        ISI_POST_VARIABLE += "$" + nama_field.options[i].value + " = $_POST['" + nama_field.options[i].value + "'];\n";

        if(i == nama_field.options.length-1){
            ISI_TEMPAT_VALUES += "?";
            ISI_ISI_VALUES += "'" + nama_field.options[i].value + "'=>$" + nama_field.options[i].value;
            ISI_TIPE_VARIABLE += "ss";
            ISI_VARIABLE_TIPE_ADD += "s";
            ISI_VALUES_EDIT += "'" + nama_field.options[i].value + "'=>$" + nama_field.options[i].value;
            ISI_LIHAT_ISI += "'" + nama_field.options[i].value + "'=>$row['" + nama_field.options[i].value + "']";
        }
        else
        {
            ISI_TEMPAT_VALUES += "?, ";
            ISI_ISI_VALUES += "'" + nama_field.options[i].value + "'=>$" + nama_field.options[i].value + ", ";
            ISI_TIPE_VARIABLE += "s";
            ISI_VARIABLE_TIPE_ADD += "s";
            ISI_VALUES_EDIT += "'" + nama_field.options[i].value + "'=>$" + nama_field.options[i].value + ", ";
            ISI_LIHAT_ISI += "'" + nama_field.options[i].value + "'=>$row['" + nama_field.options[i].value + "'],\n";
        }
        }


        ISI_WHERE_EDIT = "'" + nama_field.options[0].value + "'=>$" + nama_field.options[0].value;
        ISI_POST_DELETE = "$" + nama_field.options[0].value + "= $_POST['" + nama_field.options[0].value + "'];";
        ISI_WHERE_DELETE = "'" + nama_field.options[0].value + "'=>$" + nama_field.options[0].value;
        ISI_VALUES_ISI_EDIT += ISI_ISI_VALUES + ",$" + nama_field.options[0].value;

        text_index = text_index.replace(/POST_VARIABLE/g, ISI_POST_VARIABLE);
        text_index = text_index.replace("TEMPAT_VALUES", ISI_TEMPAT_VALUES);
        text_index = text_index.replace("TIPE_VARIABLE", ISI_TIPE_VARIABLE);
        text_index = text_index.replace("VALUES_EDIT", ISI_VALUES_EDIT);
        text_index = text_index.replace("WHERE_EDIT", ISI_WHERE_EDIT);
        text_index = text_index.replace("POST_DELETE", ISI_POST_DELETE);
        text_index = text_index.replace("WHERE_DELETE", ISI_WHERE_DELETE);
        text_index = text_index.replace("LIHAT_ISI", ISI_LIHAT_ISI);
        text_index = text_index.replace("VALUES_ISI_EDIT", ISI_VALUES_ISI_EDIT);
        text_index = text_index.replace("ISI_VALUES", ISI_ISI_VALUES);
        text_index = text_index.replace("VARIABLE_TIPE_ADD", ISI_VARIABLE_TIPE_ADD);        


        // getDetail //
        text_index = text_index.replace(/GET_DETAIL/g, nama_field.options[0].value);

        // getChart //
        var chart = $('#fields_chart').find(":selected").val();

        text_index = text_index.replace(/CHART/g, chart);

        $("#t4_index").text(text_index);

        // ========== BAGIAN VIEW DATA =========//

        var view_data = $("#t4_view_data").text();

        var ISI_SEARCH_ISI = "";
        var ISI_HEAD_TABLE = "";
        var ISI_FORM_EDIT = "<div class='row gx-3'>\n";

        for (i = 0; i < nama_field.options.length; i++) {
            if(i == nama_field.options.length-1){
                ISI_SEARCH_ISI = ISI_SEARCH_ISI + "<option value='" + nama_field.options[i].value + "'>" + titleCase(nama_field.options[i].value.replace(/_/g, ' ')) + "</option>\n";
                ISI_HEAD_TABLE = ISI_HEAD_TABLE + "<th>" + titleCase(nama_field.options[i].value.replace(/_/g, ' ')) + "</th>\n";
                ISI_FORM_EDIT += "  <div class='col-xxl-3 col-lg-4 col-sm-6'>\n";
                ISI_FORM_EDIT += "    <div class='mb-3'>\n";
                ISI_FORM_EDIT += "      <label class='form-label' for='" + nama_field.options[i].value + "'>" + titleCase(nama_field.options[i].value.replace(/_/g, ' ')) + "</label>\n";
                ISI_FORM_EDIT += "      <input type='text' class='form-control' id='" + nama_field.options[i].value + "' name='" + nama_field.options[i].value + "' />\n";
                ISI_FORM_EDIT += "    <div class='invalid-feedback error'></div>\n";
                ISI_FORM_EDIT += "    </div>\n";
                ISI_FORM_EDIT += "  </div>\n";
            }
            else
            {
                ISI_SEARCH_ISI = ISI_SEARCH_ISI + "<option value='" + nama_field.options[i].value + "'>" + titleCase(nama_field.options[i].value.replace(/_/g, ' ')) + "</option>\n";
                ISI_HEAD_TABLE = ISI_HEAD_TABLE + "<th>" + titleCase(nama_field.options[i].value.replace(/_/g, ' ')) + "</th>\n";
                ISI_FORM_EDIT += "  <div class='col-xxl-3 col-lg-4 col-sm-6'>\n";
                ISI_FORM_EDIT += "    <div class='mb-3'>\n";
                ISI_FORM_EDIT += "      <label class='form-label' for='" + nama_field.options[i].value + "'>" + titleCase(nama_field.options[i].value.replace(/_/g, ' ')) + "</label>\n";
                ISI_FORM_EDIT += "      <input type='text' class='form-control' id='" + nama_field.options[i].value + "' name='" + nama_field.options[i].value + "' />\n";
                ISI_FORM_EDIT += "    <div class='invalid-feedback error'></div>\n";
                ISI_FORM_EDIT += "    </div>\n";
                ISI_FORM_EDIT += "  </div>\n";
            }
        }
        ISI_FORM_EDIT += "</div>\n";

        view_data = view_data.replace(/NAMA_TABLE/g, nama_table);        
        view_data = view_data.replace(/MODULE_NAME/g, nama_module);
        view_data = view_data.replace('SEARCH_ISI', ISI_SEARCH_ISI);
        view_data = view_data.replace('HEAD_TABLE', ISI_HEAD_TABLE);
        view_data = view_data.replace('FORM_EDIT', ISI_FORM_EDIT);


        $("#t4_view_data").text(view_data);

        // ========== BAGIAN VIEW DETAIL =========//

        var view_detail = $("#t4_view_detail").text();

        view_detail = view_detail.replace(/GET_DETAIL/g, nama_field.options[0].value);

        var ISI_VIEW_DETAIL = "";
        for (i = 0; i < nama_field.options.length; i++) {
            ISI_VIEW_DETAIL += "eTable += '<tr><td>" + titleCase(nama_field.options[i].value.replace(/_/g, ' ')) + "</td><td>' + res.msg['" + nama_field.options[i].value + "'] + '</td><tr>';\n";
        }
        view_detail = view_detail.replace('NAMA_TABLE', titleCase(nama_table.replace(/_/g, ' ')));
        view_detail = view_detail.replace(/NAMA_TABLE/g, nama_table);
        view_detail = view_detail.replace(/MODULE_NAME/g, nama_module.toLowerCase().replace(/ /g, '_'));
        view_detail = view_detail.replace('TABLE_DETAIL', ISI_VIEW_DETAIL);

        $("#t4_view_detail").text(view_detail);

        // ========== BAGIAN VIEW CHART =========//

        var view_chart = $("#t4_view_chart").text();
        var chart = $('#fields_chart').find(":selected").val();

        view_chart = view_chart.replace(/NAMA_TABLE/g, titleCase(nama_table.replace(/_/g, ' ')));
        view_chart = view_chart.replace('CHART', chart);
        view_chart = view_chart.replace(/MODULE_NAME/g, nama_module.toLowerCase().replace(/ /g, '_'));

        $("#t4_view_chart").text(view_chart);


        // ========== BAGIAN JAVASCRIPT =========//
        var text_javascript = $("#t4_javascript").text();

        var ISI_COLUMNS_ISI= ""; 
        var ISI_COLUMNDEFS_ISI= ""; 
        var ISI_RULES_ISI= ""; 
        var ISI_MESSAGES_ISI= ""; 
        var ISI_SUBMITHANDLER_ISI= "";
        var ISI_DATA_ISI= ""; 
        var ISI_EDIT_ISI= ""; 
        var ISI_DELETE_ISI= ""; 
        var ISI_FORM_ISI= ""; 
        var ISI_HEADER_ISI= ""; 
        var ISI_ETABLE_ISI= ""; 
        var ISI_TAMBAH_ISI= "";

        text_javascript = text_javascript.replace('NAMA_TABLE_UPPER', titleCase(nama_table.replace(/_/g, ' ')));
        text_javascript = text_javascript.replace(/NAMA_TABLE/g, nama_table);


        for (i = 0; i < nama_field.options.length; i++) {

        ISI_POST_VARIABLE += "$" + nama_field.options[i].value + " = $_POST['" + nama_field.options[i].value + "'];\n";

        if(i == nama_field.options.length-1){

            ISI_COLUMNS_ISI += "{ 'data': '" + nama_field.options[i].value + "' }\n";
            ISI_COLUMNDEFS_ISI += "{ 'targets': " + i + "}\n";
            ISI_RULES_ISI += nama_field.options[i].value + ": 'required'\n";
            ISI_MESSAGES_ISI += nama_field.options[i].value + ":'" + titleCase(nama_field.options[i].value.replace(/_/g, ' ')) + " tidak boleh kosong!'\n";
            ISI_SUBMITHANDLER_ISI += "var " + nama_field.options[i].value + "= $('#" + nama_field.options[i].value + "').val();\n";
            ISI_EDIT_ISI += "var " + nama_field.options[i].value + " = rowData['" + nama_field.options[i].value + "'];\n";
            ISI_FORM_ISI += "$('#" + nama_field.options[i].value + "').val(" + nama_field.options[i].value + ");\n";
            ISI_HEADER_ISI += "<th>" + titleCase(nama_field.options[i].value.replace(/_/g, ' ')) + "</th>"
            ISI_ETABLE_ISI += "eTable += '<td>' + res[i]['" + nama_field.options[i].value + "'] + '</td>';"
            ISI_TAMBAH_ISI += "$('#" + nama_field.options[i].value + "').val('');\n";
            
        }
        else
        {
            ISI_COLUMNS_ISI += "{ 'data': '" + nama_field.options[i].value + "' },\n";
            ISI_COLUMNDEFS_ISI += "{ 'targets': " + i + "},\n";
            ISI_RULES_ISI += nama_field.options[i].value + ": 'required',\n";
            ISI_MESSAGES_ISI += nama_field.options[i].value + ":'" + titleCase(nama_field.options[i].value.replace(/_/g, ' ')) + " tidak boleh kosong!',\n";
            ISI_SUBMITHANDLER_ISI += "var " + nama_field.options[i].value + "= $('#" + nama_field.options[i].value + "').val();\n";
            ISI_EDIT_ISI += "var " + nama_field.options[i].value + " = rowData['" + nama_field.options[i].value + "'];\n";
            ISI_FORM_ISI += "$('#" + nama_field.options[i].value + "').val(" + nama_field.options[i].value + ");\n";
            ISI_HEADER_ISI += "<th>" + titleCase(nama_field.options[i].value.replace(/_/g, ' ')) + "</th>"
            ISI_ETABLE_ISI += "eTable += '<td>' + res[i]['" + nama_field.options[i].value + "'] + '</td>';\n";
            ISI_TAMBAH_ISI += "$('#" + nama_field.options[i].value + "').val('');\n";
        }
        }

        ISI_DATA_ISI = "var " + nama_field.options[0].value + " = rowData['" + nama_field.options[0].value + "'];";
        ISI_DELETE_ISI = "var " + nama_field.options[0].value + " = rowData['" + nama_field.options[0].value + "'];";

        text_javascript = text_javascript.replace(/MODULE_NAME/g, nama_module.toLowerCase().replace(/ /g, '_'));
        text_javascript = text_javascript.replace(/NAMA_MODULE/g, nama_module);
        text_javascript = text_javascript.replace("COLUMNS_ISI", ISI_COLUMNS_ISI);
        text_javascript = text_javascript.replace("COLUMNDEFS_ISI", ISI_COLUMNDEFS_ISI);
        text_javascript = text_javascript.replace("RULES_ISI", ISI_RULES_ISI);
        text_javascript = text_javascript.replace("MESSAGES_ISI", ISI_MESSAGES_ISI);
        text_javascript = text_javascript.replace("SUBMITHANDLER_ISI", ISI_SUBMITHANDLER_ISI);
        text_javascript = text_javascript.replace("EDIT_ISI", ISI_EDIT_ISI);
        text_javascript = text_javascript.replace("DATA_ISI", ISI_DATA_ISI);
        text_javascript = text_javascript.replace("DELETE_ISI", ISI_DELETE_ISI);
        text_javascript = text_javascript.replace("FORM_ISI", ISI_FORM_ISI);
        text_javascript = text_javascript.replace("FORM_ISI", ISI_FORM_ISI);
        text_javascript = text_javascript.replace(/DATA_FIELD/g, nama_field.options[0].value);
        text_javascript = text_javascript.replace(/DEL_FIELD/g, nama_field.options[0].value);
        text_javascript = text_javascript.replace(/ADD_FIELD/g, nama_field.options[0].value);
        text_javascript = text_javascript.replace(/EDIT_FIELD/g, nama_field.options[0].value);
        text_javascript = text_javascript.replace("HEADER_ISI", ISI_HEADER_ISI);
        text_javascript = text_javascript.replace("ETABLE_ISI", ISI_ETABLE_ISI);
        text_javascript = text_javascript.replace("TAMBAH_ISI", ISI_TAMBAH_ISI);

        $("#t4_javascript").text(text_javascript); 

        // ========= BAGIAN STYLE =========//
        var text_style = $("#t4_styles").text();

        text_style = text_style.replace(/NAMA_TABLE/g, nama_table);
        
        $("#t4_styles").text(text_style); 


        // Simpan file sebagai modul //

        const modulename = document.getElementById('module_txt').value.toLowerCase().replace(/ /g, '_');
        const content_info = document.getElementById('t4_info').value;

        $.ajax({
            type: 'POST',
            url: "{?=url(['crud_generator','tulisinfo'])?}",
            data: {modulename: modulename, filename: 'Info.php', content: content_info},     
            success: function(result) {
                console.log('the data was successfully sent to the server');
            }
        });

        const content_index = document.getElementById('t4_index').value;

        $.ajax({
            type: 'POST',
            url: "{?=url(['crud_generator','tulisadmin'])?}",
            data: {modulename: modulename, filename: 'Admin.php', content: content_index},     
            success: function(result) {
                console.log('the data was successfully sent to the server');
            }
        });

        const content_view = document.getElementById('t4_view_data').value;

        $.ajax({
            type: 'POST',
            url: "{?=url(['crud_generator','tulisview'])?}",
            data: {modulename: modulename, content: content_view},     
            success: function(result) {
                console.log('the data was successfully sent to the server');
            }
        });

        const content_detail = document.getElementById('t4_view_detail').value;

        $.ajax({
            type: 'POST',
            url: "{?=url(['crud_generator','tulisdetail'])?}",
            data: {modulename: modulename, content: content_detail},     
            success: function(result) {
                console.log('the data was successfully sent to the server');
            }
        });

        const content_chart = document.getElementById('t4_view_chart').value;

        $.ajax({
            type: 'POST',
            url: "{?=url(['crud_generator','tulischart'])?}",
            data: {modulename: modulename, content: content_chart},     
            success: function(result) {
                console.log('the data was successfully sent to the server');
            }
        });        
        
        const content_javascript = document.getElementById('t4_javascript').value;

        $.ajax({
            type: 'POST',
            url: "{?=url(['crud_generator','tulisjavascript'])?}",
            data: {modulename: modulename, content: content_javascript},     
            success: function(result) {
                console.log('the data was successfully sent to the server');
            }
        });        

        const content_style = document.getElementById('t4_styles').value;

        $.ajax({
            type: 'POST',
            url: "{?=url(['crud_generator','tulisstyle'])?}",
            data: {modulename: modulename, content: content_style},     
            success: function(result) {
                console.log('the data was successfully sent to the server');
            }
        });        

    });

    // REMOVE FIELD ON FIELD SELECT

    $('#field_remove').click(function() {
        $('#fields_txt').selectator('destroy');
        $('#fields_txt').find('option:selected').remove();
        $('#fields_txt').selectator();
    });

    $("#add_table").click(function (event) {
        event.preventDefault();
        var loadURL =  mlite.url + '/crud_generator/addtable?t=' + mlite.token;
    
        var modal = $('#modal_add_table');
        var modalContent = $('#modal_add_table .modal-content');
    
        modal.off('show.bs.modal');
        modal.on('show.bs.modal', function () {
            modalContent.load(loadURL);
        }).modal('show');
        
        return false;

    })    

    $("form[name='form_crud_generator']").validate({
        rules: {
        },
        messages: {
        },
        submitHandler: function (form) {
            console.log(form);
            var table_name= $('#table_name').val();
            var column_name= $('#column_name').val();
            var column_type= $('#column_type').val();
            var column_length= $('#column_length').val();
            var column_default= $('#column_default').val();

            var formData = new FormData(form); // tambahan

            $.ajax({
                url: "{?=url(['crud_generator','saveaddtable'])?}",
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
                        bootbox.alert('<span class="text-success">' + data.msg + '</span>');
                    } else {
                        bootbox.alert('<span class="text-danger">' + data.msg + '</span>');
                    }    
                    $("#modal_crud_generator").modal('hide');
                }
            })
        }
    });

    $("#tambah_data_tabel_database").click(function () {

        $('#kd_bangsal').val('');
        $('#nm_bangsal').val('');
        $('#status').val('');

        $("#typeact").val("add");
        $("#kd_bangsal").prop('disabled', false);
        
        $('#modal-title').text("Tambah Table Database");
        $("#modal_crud_generator").modal('show');
    });

    
    $(document).on('click', '.btn-add', function(e) {
        e.preventDefault();
        $("#repeat-div .outer:first").find('#column_type').selectator('destroy');
        var controlForm = $('#repeat-div .outer:first').clone(true);
        $(controlForm).find('button.btn')
            .removeClass('btn-add btn-success').addClass('btn-remove btn-danger')
            .html('<i class="ri-close-line"></i>');//add remove class
        $("#repeat-div").append(controlForm)    
        $("#repeat-div .outer:first").find('#column_type').selectator();
        $("#repeat-div .outer:last").find('#column_type').selectator();
    }).on('click', '.btn-remove', function(e) {
        e.preventDefault();
        $(this).parents('.outer').remove();//remove closest class .outer
        return false;
    });

    $("#save_add_table").click(function (event) {
        event.preventDefault();
        
        var add_table_name = $_POST['table_name'];
        var add_column_name = $_POST['column_name'];
        var add_column_type = $_POST['column_type'];
        var add_column_length = $_POST['column_length'];
        var add_column_default = $_POST['column_default'];

        $.ajax({
            type: 'POST',
            url: "{?=url(['crud_generator','saveaddtable'])?}",
            data: {
                add_table_name: add_table_name, 
                add_column_name: add_column_name, 
                add_column_type: add_column_type, 
                add_column_length: add_column_length, 
                add_column_default: add_column_default
            },
            success: function(result) {
                console.log(result);
            },
            error: function(err) {
                console.log(err);
            }
        });

    })   

    $("#icon_txt").click(function (event) {
        event.preventDefault();
        var loadURL =  mlite.url + '/crud_generator/icons?t=' + mlite.token;
    
        var modal = $('#modal_search_icons');
        var modalContent = $('#modal_search_icons .modal-content');
    
        modal.off('show.bs.modal');
        modal.on('show.bs.modal', function () {
            modalContent.load(loadURL);
        }).modal('show');
        
        return false;

    })    

    $("#modal_search_icons").on('shown.bs.modal', function () {
        $("#search-icons").on("keyup", function() {
            var valThis = $(this).val().toLowerCase();
            var n = 'arrow';
            if(valThis == ""){
                $('.icons-container').show();
                $('.icons').css('display', 'block');
            } else {
                $('.icons').css('display', 'none');
                $('.icons-container').each(function(){
                    var text = $(this).find("[class*='" + valThis + "']").parents('.icons').css('display','block');
                });
            };        
        });        
        $(".icons").click(function(){
            $('#icon_txt').val($(this).find("i").attr("class").substr(3));
            $('#modal_search_icons').modal('hide');
        });      
    });    
    
    $("#module_txt").prop('disabled', true); // GA BISA DIEDIT KALI DISABLE
	$("#module_txt_Change").on("click", function() {
		$("input[name=module_txt]").attr('disabled', false);
		return false;
	})

}); 