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
