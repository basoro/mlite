$(document).ready(function(){

  $('#manage').on('click', '#submit_periode_rawat_jalan', function(event){
    var baseURL = mlite.url + '/' + mlite.admin;
    event.preventDefault();
    var url    = baseURL + '/presensi/rekap_presensi?t=' + mlite.token;
    var periode_rawat_jalan  = $('input:text[name=periode_rawat_jalan]').val();
    var periode_rawat_jalan_akhir  = $('input:text[name=periode_rawat_jalan_akhir]').val();
    var s  = $('input:text[name=s]').val();
  
    if(periode_rawat_jalan == '') {
      alert('Tanggal awal masih kosong!')
    }
    if(periode_rawat_jalan_akhir == '') {
      alert('Tanggal akhir masih kosong!')
    }

    var ss = decodeURI(s);

    var optionText = document.getElementById("bidang").value;
    var option = optionText.toLowerCase();
    var opt = decodeURI(option);

    window.location.href = baseURL+'/presensi/rekap_presensi?awal='+periode_rawat_jalan+'&akhir='+periode_rawat_jalan_akhir+'&ruang='+opt+'&s='+ss+'&t=' + mlite.token;
    
    event.stopPropagation();
  
  });
  $('#manage').on('click', '#cari', function(event){
    var baseURL = mlite.url + '/' + mlite.admin;
    event.preventDefault();
    var url    = baseURL + '/presensi/rekap_presensi?t=' + mlite.token;
    var s  = $('input:text[name=s]').val();
  
    
    var optionText = document.getElementById("bidang").value;
    var option = optionText.toLowerCase();
    var opt = decodeURI(option);

    window.location.href = baseURL+'/presensi/rekap_presensi?s='+s+'&ruang='+opt+'&t=' + mlite.token;
    
  
    event.stopPropagation();
  
  });
  $('#presensi_masuk').on('click', '#cari', function(event){
    var baseURL = mlite.url + '/' + mlite.admin;
    event.preventDefault();
    var url    = baseURL + '/presensi/presensi?t=' + mlite.token;
    var s  = $('input:text[name=s]').val();
  
    
    var optionText = document.getElementById("bidang").value;
    var option = optionText.toLowerCase();
    var opt = decodeURI(option);

    var depop = document.getElementById("dep").value;
    var depo = depop.toLowerCase();
    var dep = decodeURI(depo);

    window.location.href = baseURL+'/presensi/presensi?s='+s+'&ruang='+opt+'&dep='+dep+'&t=' + mlite.token;
    
  
    event.stopPropagation();
  
  });
})

   $(function () {
       $('.periode_rawat_jalan').datetimepicker({
         defaultDate: new Date(),
         format: 'YYYY-MM-DD',
         locale: 'id'
       });
   });
