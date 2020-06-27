// Datepicker
$( function() {
  $( ".datepicker" ).datepicker({
    dateFormat: "yy-mm-dd",
    changeMonth: true,
    changeYear: true,
    yearRange: "-100:+0",
  });
  $( ".expired" ).datepicker({
    dateFormat: "yy-mm-dd",
    changeMonth: true,
    changeYear: true,
    yearRange: "-10:+20",
  });
} );
$(document).ready(function(){
    $('.display').DataTable({
      "lengthChange": false,
      "scrollX": true
    });
});
