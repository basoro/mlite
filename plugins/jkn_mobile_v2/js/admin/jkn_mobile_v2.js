$( function() {
  $(".datepicker").datepicker({
    dateFormat: "yy-mm-dd",
    changeMonth: true,
    changeYear: true,
    yearRange: "-100:+0",
  });
});

$('.display').DataTable({
  "lengthChange": false,
  "scrollX": true,
  "language": { "search": "" },
  dom: "<<'data-table-title'><'datatable-search'f>><'row'<'col-sm-12'tr>><<'pmd-datatable-pagination' l i p>>"
});
var t = $(".display").DataTable().rows().count();
$(".data-table-title").html('<h3 style="display:inline;float:left;margin-top:0;" class="hidden-xs">Total: ' + t + '</h3>');

$('#poli_nama').keyup(function(e){
  if(e.which == 32) {
    $.ajax({
      url: '{?=url([ADMIN, 'jkn_mobile_v2', 'refpoli'])?}',
      method:"GET",
      data:{},
           success:function(data)
    {
      var data = JSON.parse(data);
      console.log(data);
      var json_obj = [data];
      var output='';
      if(json_obj[0].metaData.code == 1) {
        var response = json_obj[0].response;
        for (var i in response) {
          output+='<li class=\"list-group-item link-class\">' + response[i].kdsubspesialis + ': ' + response[i].nmsubspesialis + '</li>';
        }
      } else {
        output+='<li class=\"list-group-item link-class\">' + json_obj[0].metaData.message + '</li>';
      }
      output+='';
      //console.log(output);
      $('#poliList').fadeIn();
      $('#poliList').html(output).show();
    }
  });
}
                              });
$('#poliList').on('click', 'li', function(){
  $('#poli_nama').val($(this).text().split(': ')[1]);
  $('#poli_kode').val($(this).text().split(': ')[0]);
  var poli_kode = $('#poli_kode').val();
  $('#poliList').fadeOut();
});

$('#dokter_nama').keyup(function(e){
  if(e.which == 32) {
    $.ajax({
      url: '{?=url([ADMIN, 'jkn_mobile_v2', 'refdokter'])?}',
      method:"GET",
      data:{},
           success:function(data)
    {
      var data = JSON.parse(data);
      console.log(data);
      var json_obj = [data];
      var output='';
      if(json_obj[0].metaData.code == 1) {
        var response = json_obj[0].response;
        for (var i in response) {
          output+='<li class=\"list-group-item link-class\">' + response[i].kodedokter + ': ' + response[i].namadokter + '</li>';
        }
      } else {
        output+='<li class=\"list-group-item link-class\">' + json_obj[0].metaData.message + '</li>';
      }
      output+='';
      //console.log(output);
      $('#dokterList').fadeIn();
      $('#dokterList').html(output).show();
    }
  });
}
                              });
$('#dokterList').on('click', 'li', function(){
  $('#dokter_nama').val($(this).text().split(': ')[1]);
  $('#dokter_kode').val($(this).text().split(': ')[0]);
  var dokter_kode = $('#dokter_kode').val();
  $('#dokterList').fadeOut();
});
