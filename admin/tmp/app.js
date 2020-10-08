$(document).ready(function(){
    $('body').on('change','#shift', function() {
        var optionText = $("#shift option:selected").text();
        $.ajax({
            url: 'http://localhost/Khanza-Lite/admin/presensi/ajax?show=jam_masuk&shift='+optionText+'&t=9a07a65eccb6',
            type: 'GET',
            dataType: 'json',
            success: function(data){
                $('#jam_masuk').val(data);
                // alert(data);
            }
        })
    });
})