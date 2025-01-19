jQuery().ready(function () {
    
    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_dokter").click(function () {
        var search = $('#search').val();
        var tgl_awal = $('#tgl_awal').val();
        var tgl_akhir = $('#tgl_akhir').val();


        $.ajax({
            url: "{?=url([ADMIN,'jasa_medis_dokter','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat', 
                search: search, 
                tgl_awal: tgl_awal, 
                tgl_akhir: tgl_akhir
            },
            dataType: 'json',
            success: function (res) {
                console.log(res);
                var grandtotal = res['grandtotal'];
                var res = res['dokter'];
                var eTable = "<div class='table-responsive'><table id='tbl_lihat_dokter' class='table' style='width:100%'><thead><th>Nama Dokter</th><th>Detail</th></thead>";
                for (var i = 0; i < res.length; i++) {
                    eTable += "<tr>";
                    eTable += '<td>' + res[i]['nm_dokter'] + '</td>';
                    eTable += '<td></td>';
                    eTable += '</tr>';
                    for (var ii = 0; ii < res[i]['rawat_jl_dr'].length; ii++) {
                        eTable += "<tr>";
                        eTable += '<td></td>';
                        eTable += '<td>Tanggal : ' + res[i]['rawat_jl_dr'][ii]['tgl_perawatan'] + '</td>';
                        eTable += '</tr>';
                        for (var iii = 0; iii < res[i]['rawat_jl_dr'][ii]['detail'].length; iii++) {
                            eTable += "<tr>";
                            eTable += '<td></td>';
                            eTable += '<td>- ' + res[i]['rawat_jl_dr'][ii]['detail'][iii]['nm_perawatan'] + ' : Rp. ' + (res[i]['rawat_jl_dr'][ii]['detail'][iii]['tarif_tindakandr']/1000).toFixed(3) + '</td>';
                            eTable += "</tr>";
                        }    
                        eTable += '<tr>';
                        eTable += '<td></td>';
                        eTable += '<td><i>Sub Total : Rp. ' + (res[i]['rawat_jl_dr'][ii]['subtotal']/1000).toFixed(3) + '</i></td>'; 
                        eTable += "</tr>";
                    }
                    eTable += '<tr>';
                    eTable += '<td></td>';
                    eTable += '<td><b>Total : Rp. ' + (res[i]['total']/1000).toFixed(3) + '</b></td>'; 
                    eTable += '</tr>';
                }
                eTable += "<tr>";
                eTable += '<td><b>Grand Total</b></td>';
                eTable += '<td><b>Rp. ' + (grandtotal/1000).toFixed(3) + '</b></td>';
                eTable += "</tr>";
                eTable += "</tbody></table></div>";
                $('#forTable_dokter').html(eTable);
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_dokter").modal();
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
        doc.text("Tabel Jasa Medis Dokter", 20, 95, null, null, null);
        const totalPagesExp = "{total_pages_count_string}";        
        doc.autoTable({
            html: '#tbl_lihat_dokter',
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
        // doc.save('table_data_dokter.pdf')
        window.open(doc.output('bloburl'), '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
              
    })

    // ===========================================
    // Ketika tombol export xlsx di tekan
    // ===========================================
    $("#export_xlsx").click(function () {
        let tbl1 = document.getElementById("tbl_lihat_dokter");
        let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
        let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
        let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
        const new_workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data dokter");
        XLSX.writeFile(new_workbook, 'tmp_file.xls');
    })
});