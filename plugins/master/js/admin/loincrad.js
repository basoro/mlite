$(document).ready(function() {
    var baseURL = mlite.url + '/' + mlite.admin;

    $("#notif").hide();

    // Toggle Form
    $("#index").on('click', '#bukaform', function() {
        $("#form").show().load(baseURL + '/master/loincradform?t=' + mlite.token);
        $(this).val("Tutup Form").attr("id", "tutupform");
    });

    $("#index").on('click', '#tutupform', function() {
        $("#form").hide();
        $(this).val("Buka Form").attr("id", "bukaform");
    });

    // Cancel Button
    $("#form").on("click", "#tutupform_btn", function() {
        $("#form").hide();
        $("#tutupform").val("Buka Form").attr("id", "bukaform");
    });

    // Save
    $("#form").on("submit", "#loincrad_form", function(e) {
        e.preventDefault();
        $.post(baseURL + '/master/loincradsave?t=' + mlite.token, $(this).serialize(), function() {
            $(".display").load(baseURL + '/master/loincraddisplay?t=' + mlite.token);
            $("#form").hide();
            $("#tutupform").val("Buka Form").attr("id", "bukaform");
            $('#notif').html('<div class="alert alert-success alert-dismissible fade in" role="alert" style="border-radius:0px;margin-top:-15px;">Data telah disimpan!<button type="button" class="close" data-dismiss="alert" aria-label="Close">&times;</button></div>').show();
        });
    });

    // Edit
    $(".display").on("click", ".edit", function(e) {
        e.preventDefault();
        var code = $(this).data("code");
        $("#form").load(baseURL + '/master/loincradform?t=' + mlite.token, {Code: code}, function() {
            $(this).show();
            $("#bukaform").val("Tutup Form").attr("id", "tutupform");
        });
    });

    // Delete
    $(".display").on("click", ".hapus", function(e) {
        e.preventDefault();
        var code = $(this).data("code");
        bootbox.confirm("Apakah Anda yakin ingin menghapus data ini?", function(result) {
            if (result) {
                $.post(baseURL + '/master/loincradhapus?t=' + mlite.token, {Code: code}, function(data) {
                    // server mengirim HTML alert ketika penghapusan gagal (status tetap 200)
                    if (typeof data === 'string' && data.indexOf('alert-danger') !== -1) {
                      $('#notif').html(data).show();
                      return;
                    }
                    $(".display").load(baseURL + '/master/loincraddisplay?t=' + mlite.token);
                    $('#notif').html('<div class="alert alert-danger alert-dismissible fade in" role="alert" style="border-radius:0px;margin-top:-15px;">Data LOINC Radiologi telah dihapus!<button type="button" class="close" data-dismiss="alert" aria-label="Close">&times;</button></div>').show();
                }).fail(function(xhr) {
                  // permintaan ditolak server (mis. CSRF 403) atau error jaringan
                  var pesan = 'Data LOINC Radiologi gagal dihapus!';
                  try {
                    var res = JSON.parse(xhr.responseText);
                    if (res && res.message) { pesan = res.message; }
                  } catch (e) {}
                  $('#notif').html("<div class=\"alert alert-danger alert-dismissible fade in\" role=\"alert\" style=\"border-radius:0px;margin-top:-15px;\">"+
                  "<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">&times;</button>"+
                  $("<div>").text(pesan).html()+
                  "</div>").show();
                });
            }
        });
    });

    // Pagination
    $(".display").on("click", ".halaman", function(e) {
        e.preventDefault();
        var halaman = $(this).data("halaman");
        var cari = $('input:text[name=cari]').val();
        $(".display").load(baseURL + '/master/loincraddisplay?t=' + mlite.token, {halaman: halaman, cari: cari});
    });

    // Search
    $('input:text[name=cari]').on('input', function() {
        var cari = $(this).val();
        if (cari.length >= 3 || cari.length === 0) {
            $(".display").load(baseURL + '/master/loincraddisplay?t=' + mlite.token, {cari: cari});
        }
    });
});
