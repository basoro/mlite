$(document).ready(function () {
    if ($('#pacsTable').length > 0) {
        var base_url = window.location.origin + window.location.pathname.replace('/admin/mini_pacs/manage', '');
        var pacsTable = $('#pacsTable').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": base_url + '/admin/api/mini_pacs/list',
                "type": "POST"
            },
            "columns": [
                { "data": "id", "searchable": false, "orderable": false },
                { "data": "no_rawat" },
                { "data": "nm_pasien" },
                { "data": "modality" },
                { "data": "study_date" },
                {
                    "data": null,
                    "searchable": false,
                    "orderable": false,
                    "render": function (data, type, row) {
                        var buttons = '';
                        buttons += '<a href="' + row.view_url + '" target="_blank" class="btn btn-primary btn-xs"><i class="fa fa-eye"></i> View</a> ';
                        buttons += '<a href="' + base_url + '/admin/mini_pacs/detail/' + row.id + '?t=' + mlite.token + '" class="btn btn-info btn-xs"><i class="fa fa-list"></i> Detail</a> ';
                        buttons += '<button class="btn btn-success btn-xs btn-send-satusehat" data-id="' + row.id + '"><i class="fa fa-cloud-upload"></i> Kirim ke Satu Sehat</button> ';
                        buttons += '<a href="' + base_url + '/admin/mini_pacs/delete/' + row.id + '?t=' + mlite.token + '" class="btn btn-danger btn-xs" onclick="return confirm(\'Anda yakin ingin menghapus study beserta rincian series dan instance di dalamnya?\')"><i class="fa fa-trash"></i> Hapus</a>';
                        return buttons;
                    }
                }
            ],
            "order": [[0, "desc"]]
        });

        // Fix numbering
        pacsTable.on('draw.dt', function () {
            var info = pacsTable.page.info();
            pacsTable.column(0, { search: 'applied', order: 'applied' }).nodes().each(function (cell, i) {
                cell.innerHTML = i + 1 + info.start;
            });
        });
    }

    $(document).on('click', '.btn-send-satusehat', function (e) {
        e.preventDefault();
        var id = $(this).data('id');
        if (confirm('Kirim DICOM ke Satu Sehat?')) {
            var btn = $(this);
            var oText = btn.html();
            btn.html('<i class="fa fa-spinner fa-spin"></i> Loading...').prop('disabled', true);

            var base_url = window.location.origin + window.location.pathname.replace('/admin/mini_pacs/manage', '');
            $.ajax({
                url: base_url + '/admin/mini_pacs/satusehat/' + id + '?t=' + mlite.token,
                type: 'GET',
                dataType: 'json',
                success: function (res) {
                    btn.html(oText).prop('disabled', false);
                    if (res.status === 'success' || res.status === 'duplicate') {
                        $('#satusehatDicomResponse').text(res.dicom_raw || '');
                        $('#satusehatFhirResponse').text(res.fhir_raw || '');
                        
                        if ($('#satusehatFhirPayload').length === 0) {
                            $('#satusehatFhirResponse').before('<h5><strong>Payload JSON (ImagingStudy)</strong></h5><pre><code id="satusehatFhirPayload"></code></pre><hr>');
                        }
                        $('#satusehatFhirPayload').text(res.fhir_payload || '');

                        if (res.status === 'duplicate') {
                            $('#satusehatModalLabel').html('Respon Satu Sehat <span class="label label-warning">Duplicate DICOM</span>');
                        } else {
                            $('#satusehatModalLabel').html('Respon Satu Sehat <span class="label label-success">Success</span>');
                        }
                        $('#satusehatModal').modal('show');
                    } else {
                        alert('Gagal: ' + res.message);
                    }
                },
                error: function (xhr) {
                    btn.html(oText).prop('disabled', false);
                    alert('Terjadi kesalahan saat mengirim ke Satu Sehat. Silakan cek network console.');
                }
            });
        }
    });

});
