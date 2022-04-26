var reader  = new FileReader();
reader.addEventListener("load", function() {
  $("#photoPreview").attr('src', reader.result);
}, false);
$("input[name=photo]").change(function() {
  reader.readAsDataURL(this.files[0]);
});
$( function() {
    $( ".tanggal" ).datetimepicker({
      format: 'YYYY-MM-DD',
      locale: 'id'
    });
} );

$(document).ready(function(){
 jQuery('.timeline').timeline({
  //mode: 'horizontal',
  //visibleItems: 4
  //Remove this comment for see Timeline in Horizontal Format otherwise it will display in Vertical Direction Timeline
 });
});
