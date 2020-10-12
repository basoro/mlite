$(document).ready(function(){
    $('body').on('change','#shift', function() {
        var optionText = $("#shift option:selected").text();
        $.ajax({
            url: 'http://localhost/Khanza-Lite/admin/presensi/ajax?show=jam_masuk&shift='+optionText+'&t=db3fff9e0c77',
            type: 'GET',
            dataType: 'json',
            success: function(data){
                $('#jam_masuk').val(data);
                // alert(data);
            }
        })
    });
})