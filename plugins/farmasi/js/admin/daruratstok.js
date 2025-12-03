jQuery(function($) {
    var daruratstokTable;

    // Initialize DataTable
    daruratstokTable = $('#daruratstok_table').DataTable({
        "processing": true,
        "serverSide": true,
        "ordering": false,
        "ajax": {
            "url": "{?=url([ADMIN, 'farmasi', 'daruratstokdata'])?}",
            "type": "POST",
            "dataSrc": function(json){
                try {
                    if (json && Array.isArray(json.data)) return json.data;
                    if (json && Array.isArray(json.rows)) return json.rows;
                    if (json && Array.isArray(json.aaData)) return json.aaData;
                    if (Array.isArray(json)) return json;
                    console.error('Unexpected DataTables response shape:', json);
                    return [];
                } catch(e) {
                    console.error('dataSrc parse error:', e);
                    return [];
                }
            },
            "data": function(d) {
                d.search_field_databarang = $('#search_field_databarang').val();
                d.search_text_databarang = $('#search_text_databarang').val();
            }
        },
        "deferRender": true,
        "columns": [
            { 
                "data": null,
                "render": function(data, type, row) {
                    var stok = parseInt(row.stok) || 0;
                    var stokMinimal = parseInt(row.stokminimal) || 0;
                    
                    if (stok == 0) {
                        return '<span class="label label-danger">HABIS</span>';
                    } else if (stok < stokMinimal) {
                        return '<span class="label label-warning">KRITIS</span>';
                    } else {
                        return '<span class="label label-success">AMAN</span>';
                    }
                },
                "orderable": false
            },
            { "data": "kode_brng" },
            { "data": "nama_brng" },
            { 
                "data": "stok",
                "render": function(data, type, row) {
                    var stok = parseInt(data) || 0;
                    var stokMinimal = parseInt(row.stokminimal) || 0;
                    var className = '';
                    
                    if (stok == 0) {
                        className = 'text-danger font-weight-bold';
                    } else if (stok < stokMinimal) {
                        className = 'text-warning font-weight-bold';
                    } else {
                        className = 'text-success';
                    }
                    
                    return '<span class="' + className + '">' + stok.toLocaleString('id-ID') + '</span>';
                }
            },
            { 
                "data": "stokminimal",
                "render": function(data, type, row) {
                    return parseInt(data || 0).toLocaleString('id-ID');
                }
            },
            { 
                "data": null,
                "render": function(data, type, row) {
                    var stok = parseInt(row.stok) || 0;
                    var stokMinimal = parseInt(row.stokminimal) || 0;
                    var selisih = stok - stokMinimal;
                    var className = '';
                    
                    if (selisih < 0) {
                        className = 'text-danger font-weight-bold';
                    } else if (selisih == 0) {
                        className = 'text-warning';
                    } else {
                        className = 'text-success';
                    }
                    
                    return '<span class="' + className + '">' + selisih.toLocaleString('id-ID') + '</span>';
                },
                "orderable": false
            },
            { "data": "kode_satbesar" },
            { "data": "kode_sat" },
            { 
                "data": "h_beli",
                "render": function(data, type, row) {
                    return 'Rp ' + parseFloat(data || 0).toLocaleString('id-ID', {minimumFractionDigits: 0, maximumFractionDigits: 2});
                }
            },
            { 
                "data": "isi",
                "render": function(data, type, row) {
                    return parseInt(data || 0).toLocaleString('id-ID');
                }
            },
            { 
                "data": "kapasitas",
                "render": function(data, type, row) {
                    return parseFloat(data || 0).toLocaleString('id-ID', {minimumFractionDigits: 0, maximumFractionDigits: 2});
                }
            },
            { "data": "expire" }
        ],
        'lengthChange': false,
        'searching': false,
        "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]],
        "language": {
            "url": "{?=url('assets/jscripts/dataTables-id.json')?}"
        },
        // Ordering handled on server without DataTables default ORDER param
        "scrollX": true,
        "pageLength": 25,
        "rowCallback": function(row, data) {
            var stok = parseInt(data.stok) || 0;
            var stokMinimal = parseInt(data.stokminimal) || 0;
            
            // Highlight rows based on stock status
            if (stok == 0) {
                $(row).addClass('bg-danger text-primary font-weight-bold'); // Red background for empty stock
            } else if (stok < stokMinimal) {
                $(row).addClass('bg-warning text-primary font-weight-bold'); // Yellow background for critical stock
            }
        }
    });

    // Search button click event
    $('#btn_cari').on('click', function() {
        daruratstokTable.ajax.reload();
    });

    // Reset button click event
    $('#btn_reset').on('click', function() {
        $('#search_field_databarang').val('kode_brng');
        $('#search_text_databarang').val('');
        daruratstokTable.ajax.reload();
    });

    // Enter key event for search text
    $('#search_text_databarang').on('keypress', function(e) {
        if (e.which == 13) {
            daruratstokTable.ajax.reload();
        }
    });

    // Search field change event
    $('#search_field_databarang').on('change', function() {
        $('#search_text_databarang').focus();
    });

    // Auto reload every 60 seconds (longer interval for stock data)
    setInterval(function() {
        daruratstokTable.ajax.reload(null, false);
    }, 60000);

    // Add custom CSS for better visual indication
    $('<style>')
        .prop('type', 'text/css')
        .html(`
            .table tbody tr.danger {
                background-color: #f2dede !important;
            }
            .table tbody tr.warning {
                background-color: #fcf8e3 !important;
            }
            .font-weight-bold {
                font-weight: bold;
            }
        `)
        .appendTo('head');
});
