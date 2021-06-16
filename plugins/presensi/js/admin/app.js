$(document).ready(function(){
    $('body').on('change','#shift', function() {
        var optionText = $("#shift option:selected").text();
        $.ajax({
            url: '{?=url()?}/admin/presensi/ajax?show=jam_masuk&shift='+optionText+'&t={?=$_SESSION[token]?}',
            type: 'GET',
            dataType: 'json',
            success: function(data){
                $('#jam_masuk').val(data);
            }
        })
    });
})

$(document).ready(function(){

  $('.bidang').on('change', function() {
    var baseURL = mlite.url + '/' + mlite.admin;
    var optionText = (this.value);
    // alert(optionText)
    window.location.href = baseURL+'/presensi/rekap_presensi?ruang='+optionText+'&t={?=$_SESSION[token]?}'
      // $.ajax({
      //     url: '{?=url()?}/admin/presensi/ajax?show=jam_masuk&shift='+optionText+'&t={?=$_SESSION[token]?}',
      //     type: 'GET',
      //     dataType: 'json',
      //     success: function(data){
      //         $('#jam_masuk').val(data);
      //     }
      // })
  });
})

$( function() {
  $( ".tanggal" ).datetimepicker({
    format: 'YYYY-MM-DD',
    locale: 'id'
  });
} );
