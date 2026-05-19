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


function renderIcons() {
    const container = document.getElementById('iconsContainer');
    const iconCount = document.getElementById('iconCount');
    
    iconCount.textContent = `Showing ${filteredIcons.length} of ${allIcons.length} icons`;
    
    if (filteredIcons.length === 0) {
        container.innerHTML = '<div class="no-results">No icons found matching your search.</div>';
        return;
    }
    
    // Group filtered icons by category
    const categorizedIcons = {};
    Object.keys(fontAwesome4Icons).forEach(category => {
        const categoryIcons = fontAwesome4Icons[category].filter(icon => 
            filteredIcons.includes(icon)
        );
        if (categoryIcons.length > 0) {
            categorizedIcons[category] = categoryIcons;
        }
    });
    
    // If search is active, show all in one grid
    if (document.getElementById('searchBox').value.trim()) {
        container.innerHTML = `
            <div class="category-section">
                <div class="category-title">Search Results</div>
                <div class="icons-grid">
                    ${filteredIcons.map(icon => `
                        <div class="icon-item" onclick="showIconAlert('${icon}')">
                            <div class="icon-display">
                                <i class="fa ${icon}"></i>
                            </div>
                            <div class="icon-name">${icon}</div>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
    } else {
        // Show categorized
        container.innerHTML = Object.keys(categorizedIcons).map(category => `
            <div class="category-section">
                <div class="category-title">${category} (${categorizedIcons[category].length})</div>
                <div class="icons-grid">
                    ${categorizedIcons[category].map(icon => `
                        <div class="icon-item" onclick="showIconAlert('${icon}')">
                            <div class="icon-display">
                                <i class="fa ${icon}"></i>
                            </div>
                            <div class="icon-name">${icon}</div>
                        </div>
                    `).join('')}
                </div>
            </div>
        `).join('');
    }
}

function filterIcons() {
    const searchTerm = document.getElementById('searchBox').value.toLowerCase();
    filteredIcons = allIcons.filter(icon => 
        icon.toLowerCase().includes(searchTerm)
    );
    renderIcons();
}
function showIconAlert(iconClass) {
    // alert(`Icon: ${iconClass}\n\nHTML: <i class="fa ${iconClass}"></i>`);  
    $('#icon_module').val(iconClass.substr(3));
    $('#modal_search_icons').modal('hide');
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

    $("#tambah_data_tabel_database").click(function () {
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
    
    $("form[name='form_crud_generator']").validate({
        rules: {
        },
        messages: {
        },
        submitHandler: function (form) {

            var formData = new FormData(form); // tambahan

            $.ajax({
                url: "{?=url([ADMIN,'crud_generator','saveaddtable'])?}",
                method: "POST",
                contentType: false, // tambahan
                processData: false, // tambahan
                data: formData,
                success: function (data) {
                    data = JSON.parse(data);
                    // console.log(data);
                    var audio = new Audio('{?=url()?}/assets/sound/' + data.status + '.mp3');
                    audio.play();
                    if(data.status === 'success') {
                        bootbox.alert('<span class="text-success">' + data.msg + '</span>');
                    }    
                    $("#modal_crud_generator").modal('hide');
                }
            })
        }
    });

    $("#database").click(function() {
        $('#table_txt').selectator('destroy');
        $.ajax({
        url: "{?=url([ADMIN,'crud_generator','database'])?}",
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
        $.ajax({
            url: "{?=url([ADMIN,'crud_generator','table'])?}",
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
        var kategori_module = $("#module_category").val(); 
        var icon_module = $("#icon_module").val(); 
        var nama_table = $("#table_txt").val();
        var nama_field = document.getElementById('fields_txt');

        // ========= BAGIAN INFO =========//
        var text = $("#t4_info").text();

        var table_txt = $("#table_txt").val().replace(/_/g, ' ');

        text = text.replace('MODULE_NAME', nama_module);
        
        text = text.replace('MODULE_DESCRIPTION', table_txt);

        text = text.replace('MODULE_CATEGORY', kategori_module);

        text = text.replace('MODULE_ICON', icon_module);

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
            ISI_ISI_VALUES += "$" + nama_field.options[i].value;
            ISI_TIPE_VARIABLE += "ss";
            ISI_VARIABLE_TIPE_ADD += "s";
            ISI_VALUES_EDIT += nama_field.options[i].value + "=?";
            ISI_LIHAT_ISI += "'" + nama_field.options[i].value + "'=>$row['" + nama_field.options[i].value + "']";
        }
        else
        {
            ISI_TEMPAT_VALUES += "?, ";
            ISI_ISI_VALUES += "$" + nama_field.options[i].value + ", ";
            ISI_TIPE_VARIABLE += "s";
            ISI_VARIABLE_TIPE_ADD += "s";
            ISI_VALUES_EDIT += nama_field.options[i].value + "=?, ";
            ISI_LIHAT_ISI += "'" + nama_field.options[i].value + "'=>$row['" + nama_field.options[i].value + "'],\n";
        }
        }


        ISI_WHERE_EDIT = nama_field.options[0].value + "=?";
        ISI_POST_DELETE = "$" + nama_field.options[0].value + "= $_POST['" + nama_field.options[0].value + "'];";
        ISI_WHERE_DELETE = nama_field.options[0].value + "='$" + nama_field.options[0].value + "'";
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
        var ISI_FORM_EDIT = "";

        for (i = 0; i < nama_field.options.length; i++) {
            if(i == nama_field.options.length-1){
                ISI_SEARCH_ISI = ISI_SEARCH_ISI + "<option value='" + nama_field.options[i].value + "'>" + titleCase(nama_field.options[i].value.replace(/_/g, ' ')) + "</option>\n";
                ISI_HEAD_TABLE = ISI_HEAD_TABLE + "<th>" + titleCase(nama_field.options[i].value.replace(/_/g, ' ')) + "</th>\n";
                ISI_FORM_EDIT += "  <div class='col-xxl-3 col-lg-4 col-sm-6'>\n";
                ISI_FORM_EDIT += "  <div class='form-group'>\n";
                ISI_FORM_EDIT += "      <label class='form-label' for='" + nama_field.options[i].value + "'>" + titleCase(nama_field.options[i].value.replace(/_/g, ' ')) + "</label>\n";
                ISI_FORM_EDIT += "      <input type='text' class='form-control' id='" + nama_field.options[i].value + "' name='" + nama_field.options[i].value + "' />\n";
                ISI_FORM_EDIT += "    <div class='error'></div>\n";
                ISI_FORM_EDIT += "  </div>\n";
                ISI_FORM_EDIT += "  </div>\n";
            }
            else
            {
                ISI_SEARCH_ISI = ISI_SEARCH_ISI + "<option value='" + nama_field.options[i].value + "'>" + titleCase(nama_field.options[i].value.replace(/_/g, ' ')) + "</option>\n";
                ISI_HEAD_TABLE = ISI_HEAD_TABLE + "<th>" + titleCase(nama_field.options[i].value.replace(/_/g, ' ')) + "</th>\n";
                ISI_FORM_EDIT += "  <div class='col-xxl-3 col-lg-4 col-sm-6'>\n";
                ISI_FORM_EDIT += "  <div class='form-group'>\n";
                ISI_FORM_EDIT += "      <label class='form-label' for='" + nama_field.options[i].value + "'>" + titleCase(nama_field.options[i].value.replace(/_/g, ' ')) + "</label>\n";
                ISI_FORM_EDIT += "      <input type='text' class='form-control' id='" + nama_field.options[i].value + "' name='" + nama_field.options[i].value + "' />\n";
                ISI_FORM_EDIT += "    <div class='error'></div>\n";
                ISI_FORM_EDIT += "  </div>\n";
                ISI_FORM_EDIT += "  </div>\n";
            }
        }

        view_data = view_data.replace(/NAMA_TABLE/g, nama_table);        
        view_data = view_data.replace(/MODULE_NAME/g, nama_module);
        view_data = view_data.replace('SEARCH_ISI', ISI_SEARCH_ISI);
        view_data = view_data.replace('HEAD_TABLE', ISI_HEAD_TABLE);
        view_data = view_data.replace('FORM_EDIT', ISI_FORM_EDIT);


        $("#t4_view_data").text(view_data);

        // ========== BAGIAN VIEW DETAIL =========//

        var view_detail = $("#t4_view_detail").text();

        var ISI_VIEW_DETAIL = "";
        for (i = 0; i < nama_field.options.length; i++) {
            ISI_VIEW_DETAIL = ISI_VIEW_DETAIL + "<tr><td>" + titleCase(nama_field.options[i].value.replace(/_/g, ' ')) + "</td><td>{$value." + nama_field.options[i].value + "}</td><tr>\n";
        }
        view_detail = view_detail.replace(/NAMA_TABLE/g, nama_table);
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
            ISI_MESSAGES_ISI += nama_field.options[i].value + ":'" + nama_field.options[i].value + " tidak boleh kosong!'\n";
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
            ISI_MESSAGES_ISI += nama_field.options[i].value + ":'" + nama_field.options[i].value + " tidak boleh kosong!',\n";
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
            url: "{?=url([ADMIN,'crud_generator','tulisinfo'])?}",
            data: {modulename: modulename, filename: 'Info.php', content: content_info},     
            success: function(result) {
                console.log('the data was successfully sent to the server');
            }
        });

        const content_index = document.getElementById('t4_index').value;

        $.ajax({
            type: 'POST',
            url: "{?=url([ADMIN,'crud_generator','tulisadmin'])?}",
            data: {modulename: modulename, filename: 'Admin.php', content: content_index},     
            success: function(result) {
                console.log('the data was successfully sent to the server');
            }
        });

        const content_view = document.getElementById('t4_view_data').value;

        $.ajax({
            type: 'POST',
            url: "{?=url([ADMIN,'crud_generator','tulisview'])?}",
            data: {modulename: modulename, content: content_view},     
            success: function(result) {
                console.log('the data was successfully sent to the server');
            }
        });

        const content_detail = document.getElementById('t4_view_detail').value;

        $.ajax({
            type: 'POST',
            url: "{?=url([ADMIN,'crud_generator','tulisdetail'])?}",
            data: {modulename: modulename, content: content_detail},     
            success: function(result) {
                console.log('the data was successfully sent to the server');
            }
        });
        
        const content_chart = document.getElementById('t4_view_chart').value;

        $.ajax({
            type: 'POST',
            url: "{?=url([ADMIN,'crud_generator','tulischart'])?}",
            data: {modulename: modulename, content: content_chart},     
            success: function(result) {
                console.log('the data was successfully sent to the server');
            }
        });        
                
        const content_javascript = document.getElementById('t4_javascript').value;

        $.ajax({
            type: 'POST',
            url: "{?=url([ADMIN,'crud_generator','tulisjavascript'])?}",
            data: {modulename: modulename, content: content_javascript},     
            success: function(result) {
                console.log('the data was successfully sent to the server');
            }
        });        

        const content_style = document.getElementById('t4_styles').value;

        $.ajax({
            type: 'POST',
            url: "{?=url([ADMIN,'crud_generator','tulisstyle'])?}",
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

    $("#icon_module").click(function (event) {
        event.preventDefault();
        var loadURL =  mlite.url + '/' + mlite.admin + '/crud_generator/icons?t=' + mlite.token;
    
        var modal = $('#modal_search_icons');
        var modalContent = $('#modal_search_icons .modal-content');
    
        modal.off('show.bs.modal');
        modal.on('show.bs.modal', function () {
            modalContent.load(loadURL);
        }).modal('show');
        
        return false;

    })    

    // At the top of your script section, before any other code
    if (typeof window.fontAwesome4Icons === 'undefined') {
        window.fontAwesome4Icons = {
            "Web Application Icons": [
                "fa-adjust", "fa-anchor", "fa-archive", "fa-area-chart", "fa-arrows", "fa-arrows-h", "fa-arrows-v", "fa-asterisk",
                "fa-at", "fa-automobile", "fa-balance-scale", "fa-ban", "fa-bank", "fa-bar-chart", "fa-bar-chart-o", "fa-barcode",
                "fa-bars", "fa-battery-0", "fa-battery-1", "fa-battery-2", "fa-battery-3", "fa-battery-4", "fa-battery-empty",
                "fa-battery-full", "fa-battery-half", "fa-battery-quarter", "fa-battery-three-quarters", "fa-bed", "fa-beer",
                "fa-bell", "fa-bell-o", "fa-bell-slash", "fa-bell-slash-o", "fa-bicycle", "fa-binoculars", "fa-birthday-cake",
                "fa-bluetooth", "fa-bluetooth-b", "fa-bolt", "fa-bomb", "fa-book", "fa-bookmark", "fa-bookmark-o", "fa-briefcase",
                "fa-bug", "fa-building", "fa-building-o", "fa-bullhorn", "fa-bullseye", "fa-bus", "fa-cab", "fa-calculator",
                "fa-calendar", "fa-calendar-check-o", "fa-calendar-minus-o", "fa-calendar-o", "fa-calendar-plus-o",
                "fa-calendar-times-o", "fa-camera", "fa-camera-retro", "fa-car", "fa-caret-square-o-down", "fa-caret-square-o-left",
                "fa-caret-square-o-right", "fa-caret-square-o-up", "fa-cart-arrow-down", "fa-cart-plus", "fa-cc", "fa-certificate",
                "fa-check", "fa-check-circle", "fa-check-circle-o", "fa-check-square", "fa-check-square-o", "fa-child", "fa-circle",
                "fa-circle-o", "fa-circle-o-notch", "fa-circle-thin", "fa-clock-o", "fa-clone", "fa-close", "fa-cloud",
                "fa-cloud-download", "fa-cloud-upload", "fa-code", "fa-code-fork", "fa-coffee", "fa-cog", "fa-cogs", "fa-comment",
                "fa-comment-o", "fa-commenting", "fa-commenting-o", "fa-comments", "fa-comments-o", "fa-compass", "fa-copyright",
                "fa-creative-commons", "fa-credit-card", "fa-credit-card-alt", "fa-crop", "fa-crosshairs", "fa-cube", "fa-cubes",
                "fa-cutlery", "fa-dashboard", "fa-database", "fa-desktop", "fa-diamond", "fa-dot-circle-o", "fa-download",
                "fa-edit", "fa-ellipsis-h", "fa-ellipsis-v", "fa-envelope", "fa-envelope-o", "fa-envelope-square", "fa-eraser",
                "fa-exchange", "fa-exclamation", "fa-exclamation-circle", "fa-exclamation-triangle", "fa-external-link",
                "fa-external-link-square", "fa-eye", "fa-eye-slash", "fa-eyedropper", "fa-fax", "fa-feed", "fa-female", "fa-fighter-jet",
                "fa-file-archive-o", "fa-file-audio-o", "fa-file-code-o", "fa-file-excel-o", "fa-file-image-o", "fa-file-movie-o",
                "fa-file-o", "fa-file-pdf-o", "fa-file-photo-o", "fa-file-picture-o", "fa-file-powerpoint-o", "fa-file-sound-o",
                "fa-file-text", "fa-file-text-o", "fa-file-video-o", "fa-file-word-o", "fa-file-zip-o", "fa-film", "fa-filter",
                "fa-fire", "fa-fire-extinguisher", "fa-flag", "fa-flag-checkered", "fa-flag-o", "fa-flash", "fa-flask", "fa-folder",
                "fa-folder-o", "fa-folder-open", "fa-folder-open-o", "fa-frown-o", "fa-futbol-o", "fa-gamepad", "fa-gavel",
                "fa-gear", "fa-gears", "fa-gift", "fa-glass", "fa-globe", "fa-graduation-cap", "fa-group", "fa-hand-grab-o",
                "fa-hand-lizard-o", "fa-hand-o-down", "fa-hand-o-left", "fa-hand-o-right", "fa-hand-o-up", "fa-hand-paper-o",
                "fa-hand-peace-o", "fa-hand-pointer-o", "fa-hand-rock-o", "fa-hand-scissors-o", "fa-hand-spock-o", "fa-hand-stop-o",
                "fa-hashtag", "fa-hdd-o", "fa-headphones", "fa-heart", "fa-heart-o", "fa-heartbeat", "fa-history", "fa-home",
                "fa-hotel", "fa-hourglass", "fa-hourglass-1", "fa-hourglass-2", "fa-hourglass-3", "fa-hourglass-end",
                "fa-hourglass-half", "fa-hourglass-o", "fa-hourglass-start", "fa-i-cursor", "fa-image", "fa-inbox", "fa-industry",
                "fa-info", "fa-info-circle", "fa-institution", "fa-key", "fa-keyboard-o", "fa-language", "fa-laptop", "fa-leaf",
                "fa-legal", "fa-lemon-o", "fa-level-down", "fa-level-up", "fa-life-bouy", "fa-life-buoy", "fa-life-ring",
                "fa-life-saver", "fa-lightbulb-o", "fa-line-chart", "fa-location-arrow", "fa-lock", "fa-magic", "fa-magnet",
                "fa-mail-forward", "fa-mail-reply", "fa-mail-reply-all", "fa-male", "fa-map", "fa-map-marker", "fa-map-o",
                "fa-map-pin", "fa-map-signs", "fa-meh-o", "fa-microphone", "fa-microphone-slash", "fa-minus", "fa-minus-circle",
                "fa-minus-square", "fa-minus-square-o", "fa-mobile", "fa-mobile-phone", "fa-money", "fa-moon-o", "fa-mortar-board",
                "fa-motorcycle", "fa-mouse-pointer", "fa-music", "fa-navicon", "fa-newspaper-o", "fa-object-group", "fa-object-ungroup",
                "fa-paint-brush", "fa-paper-plane", "fa-paper-plane-o", "fa-paw", "fa-pencil", "fa-pencil-square", "fa-pencil-square-o",
                "fa-percent", "fa-phone", "fa-phone-square", "fa-photo", "fa-picture-o", "fa-pie-chart", "fa-plane", "fa-plug",
                "fa-plus", "fa-plus-circle", "fa-plus-square", "fa-plus-square-o", "fa-power-off", "fa-print", "fa-puzzle-piece",
                "fa-qrcode", "fa-question", "fa-question-circle", "fa-question-circle-o", "fa-quote-left", "fa-quote-right",
                "fa-random", "fa-recycle", "fa-refresh", "fa-registered", "fa-remove", "fa-reorder", "fa-reply", "fa-reply-all",
                "fa-retweet", "fa-road", "fa-rocket", "fa-rss", "fa-rss-square", "fa-search", "fa-search-minus", "fa-search-plus",
                "fa-send", "fa-send-o", "fa-server", "fa-share", "fa-share-alt", "fa-share-alt-square", "fa-share-square",
                "fa-share-square-o", "fa-shield", "fa-ship", "fa-shopping-bag", "fa-shopping-basket", "fa-shopping-cart",
                "fa-sign-in", "fa-sign-out", "fa-signal", "fa-sitemap", "fa-sliders", "fa-smile-o", "fa-soccer-ball-o", "fa-sort",
                "fa-sort-alpha-asc", "fa-sort-alpha-desc", "fa-sort-amount-asc", "fa-sort-amount-desc", "fa-sort-asc", "fa-sort-desc",
                "fa-sort-down", "fa-sort-numeric-asc", "fa-sort-numeric-desc", "fa-sort-up", "fa-space-shuttle", "fa-spinner",
                "fa-spoon", "fa-square", "fa-square-o", "fa-star", "fa-star-half", "fa-star-half-empty", "fa-star-half-full",
                "fa-star-half-o", "fa-star-o", "fa-sticky-note", "fa-sticky-note-o", "fa-street-view", "fa-suitcase", "fa-sun-o",
                "fa-support", "fa-tablet", "fa-tachometer", "fa-tag", "fa-tags", "fa-tasks", "fa-taxi", "fa-television",
                "fa-terminal", "fa-thumb-tack", "fa-thumbs-down", "fa-thumbs-o-down", "fa-thumbs-o-up", "fa-thumbs-up", "fa-ticket",
                "fa-times", "fa-times-circle", "fa-times-circle-o", "fa-times-rectangle", "fa-times-rectangle-o", "fa-tint",
                "fa-toggle-down", "fa-toggle-left", "fa-toggle-off", "fa-toggle-on", "fa-toggle-right", "fa-toggle-up", "fa-trademark",
                "fa-trash", "fa-trash-o", "fa-tree", "fa-trophy", "fa-truck", "fa-tty", "fa-tv", "fa-umbrella", "fa-university",
                "fa-unlock", "fa-unlock-alt", "fa-unsorted", "fa-upload", "fa-user", "fa-user-plus", "fa-user-secret", "fa-user-times",
                "fa-users", "fa-video-camera", "fa-volume-control-phone", "fa-volume-down", "fa-volume-off", "fa-volume-up",
                "fa-warning", "fa-wheelchair", "fa-wheelchair-alt", "fa-wifi", "fa-wrench"
            ],
            "Accessibility Icons": [
                "fa-american-sign-language-interpreting", "fa-asl-interpreting", "fa-assistive-listening-systems", "fa-audio-description",
                "fa-blind", "fa-braille", "fa-cc", "fa-deaf", "fa-deafness", "fa-hard-of-hearing", "fa-low-vision", "fa-question-circle-o",
                "fa-sign-language", "fa-signing", "fa-tty", "fa-universal-access", "fa-volume-control-phone", "fa-wheelchair",
                "fa-wheelchair-alt"
            ],
            "File Type Icons": [
                "fa-file", "fa-file-archive-o", "fa-file-audio-o", "fa-file-code-o", "fa-file-excel-o", "fa-file-image-o",
                "fa-file-movie-o", "fa-file-o", "fa-file-pdf-o", "fa-file-photo-o", "fa-file-picture-o", "fa-file-powerpoint-o",
                "fa-file-sound-o", "fa-file-text", "fa-file-text-o", "fa-file-video-o", "fa-file-word-o", "fa-file-zip-o"
            ],
            "Spinner Icons": [
                "fa-circle-o-notch", "fa-cog", "fa-gear", "fa-refresh", "fa-spinner"
            ],
            "Form Control Icons": [
                "fa-check-square", "fa-check-square-o", "fa-circle", "fa-circle-o", "fa-dot-circle-o", "fa-minus-square",
                "fa-minus-square-o", "fa-plus-square", "fa-plus-square-o", "fa-square", "fa-square-o"
            ],
            "Payment Icons": [
                "fa-cc-amex", "fa-cc-diners-club", "fa-cc-discover", "fa-cc-jcb", "fa-cc-mastercard", "fa-cc-paypal",
                "fa-cc-stripe", "fa-cc-visa", "fa-credit-card", "fa-credit-card-alt", "fa-google-wallet", "fa-paypal"
            ],
            "Chart Icons": [
                "fa-area-chart", "fa-bar-chart", "fa-bar-chart-o", "fa-line-chart", "fa-pie-chart"
            ],
            "Currency Icons": [
                "fa-bitcoin", "fa-btc", "fa-cny", "fa-dollar", "fa-eur", "fa-euro", "fa-gbp", "fa-gg", "fa-gg-circle",
                "fa-ils", "fa-inr", "fa-jpy", "fa-krw", "fa-money", "fa-rmb", "fa-rouble", "fa-rub", "fa-ruble", "fa-rupee",
                "fa-shekel", "fa-sheqel", "fa-try", "fa-turkish-lira", "fa-usd", "fa-won", "fa-yen"
            ],
            "Text Editor Icons": [
                "fa-align-center", "fa-align-justify", "fa-align-left", "fa-align-right", "fa-bold", "fa-chain", "fa-chain-broken",
                "fa-clipboard", "fa-columns", "fa-copy", "fa-cut", "fa-dedent", "fa-eraser", "fa-file", "fa-file-o", "fa-file-text",
                "fa-file-text-o", "fa-files-o", "fa-floppy-o", "fa-font", "fa-header", "fa-indent", "fa-italic", "fa-link",
                "fa-list", "fa-list-alt", "fa-list-ol", "fa-list-ul", "fa-outdent", "fa-paperclip", "fa-paragraph", "fa-paste",
                "fa-repeat", "fa-rotate-left", "fa-rotate-right", "fa-save", "fa-scissors", "fa-strikethrough", "fa-subscript",
                "fa-superscript", "fa-table", "fa-text-height", "fa-text-width", "fa-th", "fa-th-large", "fa-th-list", "fa-underline",
                "fa-undo", "fa-unlink"
            ],
            "Directional Icons": [
                "fa-angle-double-down", "fa-angle-double-left", "fa-angle-double-right", "fa-angle-double-up", "fa-angle-down",
                "fa-angle-left", "fa-angle-right", "fa-angle-up", "fa-arrow-circle-down", "fa-arrow-circle-left", "fa-arrow-circle-o-down",
                "fa-arrow-circle-o-left", "fa-arrow-circle-o-right", "fa-arrow-circle-o-up", "fa-arrow-circle-right", "fa-arrow-circle-up",
                "fa-arrow-down", "fa-arrow-left", "fa-arrow-right", "fa-arrow-up", "fa-arrows", "fa-arrows-alt", "fa-arrows-h",
                "fa-arrows-v", "fa-caret-down", "fa-caret-left", "fa-caret-right", "fa-caret-square-o-down", "fa-caret-square-o-left",
                "fa-caret-square-o-right", "fa-caret-square-o-up", "fa-caret-up", "fa-chevron-circle-down", "fa-chevron-circle-left",
                "fa-chevron-circle-right", "fa-chevron-circle-up", "fa-chevron-down", "fa-chevron-left", "fa-chevron-right",
                "fa-chevron-up", "fa-exchange", "fa-hand-o-down", "fa-hand-o-left", "fa-hand-o-right", "fa-hand-o-up", "fa-long-arrow-down",
                "fa-long-arrow-left", "fa-long-arrow-right", "fa-long-arrow-up"
            ],
            "Video Player Icons": [
                "fa-arrows-alt", "fa-backward", "fa-compress", "fa-eject", "fa-expand", "fa-fast-backward", "fa-fast-forward",
                "fa-forward", "fa-pause", "fa-pause-circle", "fa-pause-circle-o", "fa-play", "fa-play-circle", "fa-play-circle-o",
                "fa-random", "fa-step-backward", "fa-step-forward", "fa-stop", "fa-stop-circle", "fa-stop-circle-o", "fa-youtube-play"
            ],
            "Brand Icons": [
                "fa-500px", "fa-adn", "fa-amazon", "fa-android", "fa-angellist", "fa-apple", "fa-behance", "fa-behance-square",
                "fa-bitbucket", "fa-bitbucket-square", "fa-bitcoin", "fa-black-tie", "fa-bluetooth", "fa-bluetooth-b", "fa-btc",
                "fa-buysellads", "fa-cc-amex", "fa-cc-diners-club", "fa-cc-discover", "fa-cc-jcb", "fa-cc-mastercard", "fa-cc-paypal",
                "fa-cc-stripe", "fa-cc-visa", "fa-chrome", "fa-codepen", "fa-codiepie", "fa-connectdevelop", "fa-contao",
                "fa-css3", "fa-dashcube", "fa-delicious", "fa-deviantart", "fa-digg", "fa-dribbble", "fa-dropbox", "fa-drupal",
                "fa-edge", "fa-empire", "fa-envira", "fa-expeditedssl", "fa-fa", "fa-facebook", "fa-facebook-f", "fa-facebook-official",
                "fa-facebook-square", "fa-firefox", "fa-first-order", "fa-flickr", "fa-fonticons", "fa-fort-awesome", "fa-forumbee",
                "fa-foursquare", "fa-free-code-camp", "fa-ge", "fa-get-pocket", "fa-gg", "fa-gg-circle", "fa-git", "fa-git-square",
                "fa-github", "fa-github-alt", "fa-github-square", "fa-gitlab", "fa-gittip", "fa-glide", "fa-glide-g", "fa-google",
                "fa-google-plus", "fa-google-plus-circle", "fa-google-plus-official", "fa-google-plus-square", "fa-google-wallet",
                "fa-gratipay", "fa-hacker-news", "fa-houzz", "fa-html5", "fa-instagram", "fa-internet-explorer", "fa-ioxhost",
                "fa-joomla", "fa-jsfiddle", "fa-lastfm", "fa-lastfm-square", "fa-leanpub", "fa-linkedin", "fa-linkedin-square",
                "fa-linux", "fa-maxcdn", "fa-meanpath", "fa-medium", "fa-mixcloud", "fa-modx", "fa-odnoklassniki",
                "fa-odnoklassniki-square", "fa-opencart", "fa-openid", "fa-opera", "fa-optin-monster", "fa-pagelines", "fa-paypal",
                "fa-pied-piper", "fa-pied-piper-alt", "fa-pied-piper-pp", "fa-pinterest", "fa-pinterest-p", "fa-pinterest-square",
                "fa-product-hunt", "fa-qq", "fa-ra", "fa-rebel", "fa-reddit", "fa-reddit-alien", "fa-reddit-square", "fa-renren",
                "fa-resistance", "fa-safari", "fa-scribd", "fa-sellsy", "fa-share-alt", "fa-share-alt-square", "fa-shirtsinbulk",
                "fa-simplybuilt", "fa-skyatlas", "fa-skype", "fa-slack", "fa-slideshare", "fa-snapchat", "fa-snapchat-ghost",
                "fa-snapchat-square", "fa-soundcloud", "fa-spotify", "fa-stack-exchange", "fa-stack-overflow", "fa-steam",
                "fa-steam-square", "fa-stumbleupon", "fa-stumbleupon-circle", "fa-tencent-weibo", "fa-themeisle", "fa-trello",
                "fa-tripadvisor", "fa-tumblr", "fa-tumblr-square", "fa-twitch", "fa-twitter", "fa-twitter-square", "fa-usb",
                "fa-viacoin", "fa-viadeo", "fa-viadeo-square", "fa-vimeo", "fa-vimeo-square", "fa-vine", "fa-vk", "fa-wechat",
                "fa-weibo", "fa-weixin", "fa-whatsapp", "fa-wikipedia-w", "fa-windows", "fa-wordpress", "fa-wpbeginner",
                "fa-wpforms", "fa-xing", "fa-xing-square", "fa-y-combinator", "fa-y-combinator-square", "fa-yahoo", "fa-yc",
                "fa-yc-square", "fa-yelp", "fa-yoast", "fa-youtube", "fa-youtube-play", "fa-youtube-square"
            ],
            "Medical Icons": [
                "fa-ambulance", "fa-h-square", "fa-heart", "fa-heart-o", "fa-heartbeat", "fa-hospital-o", "fa-medkit",
                "fa-plus-square", "fa-stethoscope", "fa-user-md", "fa-wheelchair", "fa-wheelchair-alt"
            ]
        };
    }

    let allIcons = [];
    let filteredIcons = [];

    // Flatten all icons
    Object.values(fontAwesome4Icons).forEach(categoryIcons => {
        allIcons = allIcons.concat(categoryIcons);
    });

    // Remove duplicates
    allIcons = [...new Set(allIcons)];
    filteredIcons = [...allIcons];

    // Event listeners
    $(document).on('input', '#searchBox', filterIcons);

    // document.getElementById('searchBox').addEventListener('input', filterIcons);
    // document.getElementById('searchBox').addEventListener('keyup', filterIcons);

    // Initialize these variables only once at the global scope
    if (typeof window.allIcons === 'undefined') {
        window.allIcons = [];
        window.filteredIcons = [];
        
        // Flatten all icons
        Object.values(window.fontAwesome4Icons).forEach(categoryIcons => {
            window.allIcons = window.allIcons.concat(categoryIcons);
        });

        // Remove duplicates
        window.allIcons = [...new Set(window.allIcons)];
        window.filteredIcons = [...window.allIcons];
    }

    $('#modal_search_icons').on('hidden.bs.modal', function () {
        // Clear the search box
        $('#searchBox').val('');
        
        // Reset filtered icons to show all icons
        window.filteredIcons = [...window.allIcons];
        
        // Clear the icons container
        $('#iconsContainer').empty();
    });
    
    $('#modal_search_icons').on('shown.bs.modal', function () {
        // Re-render icons when modal is shown
        renderIcons();
    });

}); 