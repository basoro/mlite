$(document).ready(function () {
    if ($('#pacsTable').length > 0) {
        var base_url = mlite.url + '/' + mlite.admin;

        var pacsTable = $('#pacsTable').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": base_url + '/api/mini_pacs/list',
                "type": "POST"
            },
            "lengthChange": false,
            "columns": [
                {
                    "className": 'select-checkbox',
                    "orderable": false,
                    "data": null,
                    "defaultContent": '<input type="checkbox" class="study-check">'
                },
                {
                    "className": 'details-control',
                    "orderable": false,
                    "data": null,
                    "render": function (data, type, row) {
                        return '<i class="fa fa-eye" style="cursor:pointer" title="Klik untuk detail"></i>';
                    }
                },
                { "data": "tgl_lahir" },
                { "data": "nm_pasien" },
                { "data": "no_rkm_medis" },
                { "data": "description" },
                { "data": "study_date" },
                { "data": "modality" },
                { "data": "accession_number" },
                { "data": "ser_inst" }
            ],
            "order": [[6, "desc"]],
            "dom": "r<'row'<'col-sm-12'tr>><'row'<'col-sm-5'i><'col-sm-7'p>>"
        });

        // Column filtering
        $('.column-filter').on('keyup change', function () {
            var colIdx = $(this).data('column');
            pacsTable.column(colIdx).search(this.value).draw();
        });

        // Check All
        $('#checkAll').on('change', function () {
            $('.study-check').prop('checked', $(this).is(':checked'));
        });

        // Detail expansion
        $('#pacsTable tbody').on('click', 'tr', function (e) {
            // console.log('Row clicked:', e.target);
            if ($(e.target).is('input[type="checkbox"]') || $(e.target).is('a') || $(e.target).is('button') || $(this).hasClass('pacs-details-row')) return;

            var tr = $(this);
            var row = pacsTable.row(tr);

            // Periksa apakah data row ada
            if (!row.data()) return;

            if (row.child.isShown()) {
                row.child.hide();
                tr.removeClass('shown');
            } else {
                formatDetails(row.data(), function (html) {
                    row.child(html, 'pacs-details-row').show();
                    tr.addClass('shown');
                });
            }
        });

        function formatDetails(d, callback) {
            // console.log('Fetching details for ID:', d.id);
            $.ajax({
                url: base_url + '/api/mini_pacs/detail/' + d.id + '?t=' + mlite.token,
                type: 'GET',
                dataType: 'json',
                success: function (res) {
                    if (res.status === 'success') {
                        var s = res.study || {};
                        var series = res.series || [];
                        var html = '<div class="pacs-details-container">';
                        html += '<div class="pacs-details-top">';
                        html += '  <div class="pacs-metadata-card">';
                        html += '    <div class="pacs-meta-item"><span class="pacs-meta-label">Study Date:</span> <span class="pacs-meta-val">' + (s.study_date || '-') + ' <i class="fa fa-copy"></i></span></div>';
                        html += '    <div class="pacs-meta-item"><span class="pacs-meta-label">Patient ID:</span> <span class="pacs-meta-val">' + (s.no_rkm_medis || '-') + ' <i class="fa fa-copy"></i></span></div>';
                        html += '    <div class="pacs-meta-item"><span class="pacs-meta-label">Study Time:</span> <span class="pacs-meta-val">' + (s.study_time || '-') + ' <i class="fa fa-copy"></i></span></div>';
                        html += '    <div class="pacs-meta-item"><span class="pacs-meta-label">Patient Name:</span> <span class="pacs-meta-val">' + (s.nm_pasien || '-') + ' <i class="fa fa-copy"></i></span></div>';
                        html += '    <div class="pacs-meta-item"><span class="pacs-meta-label">Study Description:</span> <span class="pacs-meta-val">' + (s.description || '-') + ' <i class="fa fa-copy"></i></span></div>';
                        html += '    <div class="pacs-meta-item"><span class="pacs-meta-label">Patient Birth Date:</span> <span class="pacs-meta-val">' + (s.tgl_lahir || '-') + ' <i class="fa fa-copy"></i></span></div>';
                        html += '    <div class="pacs-meta-item"><span class="pacs-meta-label">Accession number:</span> <span class="pacs-meta-val">' + (s.accession_number || '-') + ' <i class="fa fa-copy"></i></span></div>';
                        html += '    <div class="pacs-meta-item"><span class="pacs-meta-label">Patient sex:</span> <span class="pacs-meta-val">' + (s.jk || '-') + ' <i class="fa fa-copy"></i></span></div>';
                        html += '    <div class="pacs-meta-item"><span class="pacs-meta-label">Study ID:</span> <span class="pacs-meta-val">' + (s.study_id || '-') + ' <i class="fa fa-copy"></i></span></div>';
                        html += '    <div class="pacs-meta-item"><span class="pacs-meta-label">Patient Other IDs:</span> <span class="pacs-meta-val">-</span></div>';

                        var studyUID = s.study_instance_uid || '';
                        var displayUID = studyUID.length > 25 ? studyUID.substring(0, 25) + '...' : studyUID;
                        html += '    <div class="pacs-meta-item"><span class="pacs-meta-label">Study Instance UID:</span> <span class="pacs-meta-val" title="' + studyUID + '">' + displayUID + ' <i class="fa fa-copy"></i></span></div>';

                        html += '    <div class="pacs-meta-item"><span class="pacs-meta-label">Institution Name:</span> <span class="pacs-meta-val">' + (s.institution_name || '-') + ' <i class="fa fa-copy"></i></span></div>';
                        html += '  </div>';
                        html += '  <div class="pacs-details-actions">';
                        html += '    <button class="btn btn-view-pacs" data-url="' + d.view_url + '" title="View PACS"><i class="fa fa-eye"></i></button>';
                        html += '    <button class="btn btn-view-ohif" data-url="' + d.ohif_url + '" title="View Standalone OHIF"><i class="fa fa-tv"></i></button>';
                        html += '    <button class="btn btn-download-pacs" data-id="' + d.id + '" title="Download"><i class="fa fa-download"></i></button>';
                        html += '    <button class="btn btn-danger-delete" data-id="' + d.id + '" title="Delete"><i class="fa fa-trash"></i></button>';
                        html += '    <button class="btn btn-send-satusehat" data-id="' + d.id + '" title="Send to Satu Sehat"><i class="fa fa-cloud-upload"></i></button>';
                        html += '    <button class="btn btn-forward-pacs" data-id="' + d.id + '" title="Forward DICOM"><i class="fa fa-paper-plane"></i></button>';
                        html += '  </div>';
                        html += '</div>';
                        html += '<div class="pacs-series-section">';
                        html += '  <table class="pacs-series-table">';
                        html += '    <thead><tr><th>Series number</th><th>Series Description</th><th width="100">Modality</th><th width="100"># Instances</th></tr></thead>';
                        html += '    <tbody>';
                        series.forEach(function (ser, i) {
                            html += '<tr><td>' + (i + 1) + '</td><td>' + (ser.series_description || '-') + '</td><td>' + (ser.modality || '-') + '</td><td>' + (ser.instance_count || 0) + '</td></tr>';
                        });
                        html += '    </tbody>';
                        html += '  </table>';
                        html += '</div>';
                        html += '</div>';
                        callback(html);
                    } else {
                        console.error('API Error:', res.message);
                        alert('Gagal mengambil detail: ' + (res.message || 'Unknown error'));
                    }
                },
                error: function (xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                    console.error('Response Text:', xhr.responseText);
                    alert('Terjadi kesalahan koneksi saat mengambil detail.\nStatus: ' + status + '\nError: ' + error + '\nSilakan hubungi administrator atau cek konsol browser.');
                }
            });
        }
    }

    $(document).on('click', '.btn-send-satusehat', function (e) {
        e.preventDefault();
        var id = $(this).data('id');
        if (confirm('Kirim DICOM ke Satu Sehat?')) {
            var btn = $(this);
            var oText = btn.html();
            btn.html('<i class="fa fa-spinner fa-spin"></i>').prop('disabled', true);

            $.ajax({
                url: base_url + '/mini_pacs/satusehat/' + id + '?t=' + mlite.token,
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
                        $('#satusehatModal').modal('show');
                    } else { alert('Gagal: ' + res.message); }
                },
                error: function (xhr, status, error) {
                    btn.html(oText).prop('disabled', false);
                    var errorMessage = 'Terjadi kesalahan saat mengirim ke Satu Sehat.';
                    if (xhr.responseText) {
                        try {
                            var jsonResponse = JSON.parse(xhr.responseText);
                            if (jsonResponse.message) {
                                errorMessage += '\nDetail: ' + jsonResponse.message;
                            } else {
                                errorMessage += '\nDetail: ' + xhr.responseText;
                            }
                        } catch (e) {
                            errorMessage += '\nDetail: ' + xhr.responseText;
                        }
                    } else if (error) {
                        errorMessage += '\nError: ' + error;
                    }
                    alert(errorMessage);
                }
            });
        }
    });

    $(document).on('click', '.btn-view-pacs, .btn-view-ohif', function (e) {
        e.preventDefault();
        var url = $(this).data('url');
        $('#viewerIframe').attr('src', url);
        $('#viewerModal').modal('show');
    });

    $(document).on('click', '.btn-download-pacs', function (e) {
        e.preventDefault();
        var id = $(this).data('id');
        window.location.href = base_url + '/mini_pacs/download/' + id + '?t=' + mlite.token;
    });

    $(document).on('click', '.btn-danger-delete', function (e) {
        e.preventDefault();
        var id = $(this).data('id');
        if (confirm('Anda yakin ingin menghapus study?')) {
            var form = $('<form>', {
                'action': base_url + '/mini_pacs/delete/' + id + '?t=' + mlite.token,
                'method': 'POST'
            });
            if (window.location.href.indexOf('/main') > -1) {
                form.append($('<input>', {
                    'type': 'hidden',
                    'name': 'main',
                    'value': 'main'
                }));
            }
            $('body').append(form);
            form.submit();
        }
    });

    $(document).on('click', '.btn-forward-pacs', function (e) {
        e.preventDefault();
        var id = $(this).data('id');
        var btn = $(this);
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
        $.post(base_url + '/mini_pacs/forward/' + id + '?t=' + mlite.token, function (res) {
            if (res.status === 'success') {
                alert(res.message);
            } else {
                alert('Gagal: ' + res.message);
            }
            btn.prop('disabled', false).html('<i class="fa fa-paper-plane"></i>');
        });
    });

    $('#viewerModal').on('hidden.bs.modal', function () {
        $('#viewerIframe').attr('src', '');
    });
});
