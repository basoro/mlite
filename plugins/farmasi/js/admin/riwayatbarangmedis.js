jQuery(function($) {
    var riwayatbarangmedisTable;

    // Initialize DataTable
    riwayatbarangmedisTable = $('#riwayatbarangmedis_table').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "{?=url([ADMIN, 'farmasi', 'riwayatbarangmedisdata'])?}",
            "type": "POST",
            "data": function(d) {
                d.search_field_riwayat_barang_medis = $('#search_field_riwayat_barang_medis').val();
                d.search_text_riwayat_barang_medis = $('#search_text_riwayat_barang_medis').val();
                d.tgl_awal = $('#tgl_awal').val();
                d.tgl_akhir = $('#tgl_akhir').val();
            }
        },
        "columns": [
            { "data": "kode_brng" },
            { "data": "nama_brng" },
            { 
                "data": "stok_awal",
                "render": function(data, type, row) {
                    return parseInt(data).toLocaleString('id-ID');
                }
            },
            { 
                "data": "masuk",
                "render": function(data, type, row) {
                    return parseInt(data).toLocaleString('id-ID');
                }
            },
            { 
                "data": "keluar",
                "render": function(data, type, row) {
                    return parseInt(data).toLocaleString('id-ID');
                }
            },
            { 
                "data": "stok_akhir",
                "render": function(data, type, row) {
                    return parseInt(data).toLocaleString('id-ID');
                }
            },
            { "data": "posisi" },
            { "data": "tanggal" },
            { "data": "jam" },
            { "data": "petugas" },
            { "data": "nm_bangsal" },
            { 
                "data": "status",
                "render": function(data, type, row) {
                    if (data == 'Simpan') {
                        return '<span class="label label-success">' + data + '</span>';
                    } else {
                        return '<span class="label label-default">' + data + '</span>';
                    }
                }
            },
            { "data": "no_batch" },
            { "data": "no_faktur" },
            { "data": "keterangan" }
        ],
        "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]],
        "language": {
            "url": "{?=url('assets/jscripts/dataTables-id.json')?}"
        },
        "order": [[7, "desc"]],
        "scrollX": true,
        "pageLength": 10
    });

    // Search button click event
    $('#btn_cari').on('click', function() {
        riwayatbarangmedisTable.ajax.reload();
    });

    // Reset button click event
    $('#btn_reset').on('click', function() {
        $('#tgl_awal').val(new Date().toISOString().split('T')[0]);
        $('#tgl_akhir').val(new Date().toISOString().split('T')[0]);
        $('#search_field_riwayat_barang_medis').val('kode_brng');
        $('#search_text_riwayat_barang_medis').val('');
        riwayatbarangmedisTable.ajax.reload();
    });

    // Enter key event for search text
    $('#search_text_riwayat_barang_medis').on('keypress', function(e) {
        if (e.which == 13) {
            riwayatbarangmedisTable.ajax.reload();
        }
    });

    // Date change events
    $('#tgl_awal, #tgl_akhir').on('change', function() {
        riwayatbarangmedisTable.ajax.reload();
    });

    // Search field change event
    $('#search_field_riwayat_barang_medis').on('change', function() {
        $('#search_text_riwayat_barang_medis').focus();
    });

    // Auto reload every 30 seconds
    setInterval(function() {
        riwayatbarangmedisTable.ajax.reload(null, false);
    }, 30000);
});