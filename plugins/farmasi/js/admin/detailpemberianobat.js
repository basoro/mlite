jQuery(function($) {

    var dataTable = $('#tbl_detailpemberianobat').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'ajax': {
            'url': '{?=url([ADMIN, "farmasi", "detailpemberianobatdata"])?}',
            'type': 'POST',
            'data': function(d) {
                d.search_field_detail_pemberian_obat = $('#search_field').val();
                d.search_text_detail_pemberian_obat = $('#search_text').val();
                d.tgl_awal = $('#tgl_awal').val();
                d.tgl_akhir = $('#tgl_akhir').val();
            }
        },
        'columns': [
            { data: 'tgl_perawatan' },
            { data: 'jam' },
            { data: 'no_rkm_medis' },
            { data: 'nm_pasien' },
            { data: 'no_rawat' },
            { data: 'kode_brng' },
            { data: 'nama_brng' },
            { 
                data: 'h_beli',
                render: function(data, type, row) {
                    return 'Rp ' + parseFloat(data).toLocaleString('id-ID');
                }
            },
            { 
                data: 'biaya_obat',
                render: function(data, type, row) {
                    return 'Rp ' + parseFloat(data).toLocaleString('id-ID');
                }
            },
            { data: 'jml' },
            { 
                data: 'embalase',
                render: function(data, type, row) {
                    return 'Rp ' + parseFloat(data).toLocaleString('id-ID');
                }
            },
            { 
                data: 'tuslah',
                render: function(data, type, row) {
                    return 'Rp ' + parseFloat(data).toLocaleString('id-ID');
                }
            },
            { 
                data: 'total',
                render: function(data, type, row) {
                    return 'Rp ' + parseFloat(data).toLocaleString('id-ID');
                }
            },
            { 
                data: 'status',
                render: function(data, type, row) {
                    if (data == 'Ralan') {
                        return '<span class="label label-success">Rawat Jalan</span>';
                    } else if (data == 'Ranap') {
                        return '<span class="label label-info">Rawat Inap</span>';
                    } else {
                        return '<span class="label label-default">' + data + '</span>';
                    }
                }
            },
            { data: 'nm_bangsal' },
            { data: 'no_batch' },
            { data: 'no_faktur' }
        ],
        'paging': true,
        'lengthChange': false,
        'searching': false,
        'ordering': true,
        'info': true,
        'autoWidth': false,
        'responsive': true,
        'pageLength': 25,
        'lengthMenu': [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]],
        'language': {
            'processing': 'Sedang memproses...',
            'lengthMenu': 'Tampilkan _MENU_ entri',
            'zeroRecords': 'Tidak ada data yang ditemukan',
            'info': 'Menampilkan _START_ sampai _END_ dari _TOTAL_ entri',
            'infoEmpty': 'Menampilkan 0 sampai 0 dari 0 entri',
            'infoFiltered': '(disaring dari _MAX_ entri keseluruhan)',
            'search': 'Cari:',
            'paginate': {
                'first': 'Pertama',
                'last': 'Terakhir',
                'next': 'Selanjutnya',
                'previous': 'Sebelumnya'
            }
        },
        'dom': '<"top"fl>rt<"bottom"ip><"clear">',
        'scrollX': true
    });

    // Event handler untuk tombol cari
    $('#btn_cari').on('click', function() {
        dataTable.draw();
    });

    // Event handler untuk tombol reset
    $('#btn_reset').on('click', function() {
        $('#tgl_awal').val('{?=date("Y-m-d")?}');
        $('#tgl_akhir').val('{?=date("Y-m-d")?}');
        $('#search_field').val('tgl_perawatan');
        $('#search_text').val('');
        dataTable.draw();
    });

    // Event handler untuk enter key pada search text
    $('#search_text').on('keypress', function(e) {
        if (e.which == 13) {
            dataTable.draw();
        }
    });

    // Event handler untuk perubahan tanggal
    $('#tgl_awal, #tgl_akhir').on('change', function() {
        dataTable.draw();
    });

    // Event handler untuk perubahan search field
    $('#search_field').on('change', function() {
        $('#search_text').focus();
    });

})