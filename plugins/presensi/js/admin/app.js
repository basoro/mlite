$(document).ready(function(){
    $('body').on('change','#shift', function() {
        var optionText = $("#shift option:selected").text();
        $.ajax({
            url: '{?=url()?}/admin/presensi/ajax?show=jam_masuk&shift='+optionText+'&t={?=$_SESSION['token']?}',
            type: 'GET',
            dataType: 'json',
            success: function(data){
                $('#jam_masuk').val(data);
            }
        })
    });
})

$( function() {
  $( ".tanggal" ).datetimepicker({
    format: 'YYYY-MM-DD',
    locale: 'id'
  });
} );
