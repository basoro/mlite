$('body').on('change','#kd_dokter', function() {
     var optionText = $("#kd_dokter option:selected").text();
     $('#nm_dokter').val(optionText);
});
$('body').on('change','#nip', function() {
     var optionText = $("#nip option:selected").text();
     $('#nama').val(optionText);
});
