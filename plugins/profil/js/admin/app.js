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
