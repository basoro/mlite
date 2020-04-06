<script type="text/javascript">
$(document).ready(function() {
  $(".cek").on('click', function(){
    var data = $("#norm").val();
    $.ajax({
      type: 'POST',
      url: '<?php echo URL; ?>/modules/Inhealth/includes/cek_ep.php',
      data: "data="+data,
      success: function(data){
        //alert(data)
        if(data == '00'){
          $("#coba").load("<?php echo URL; ?>/?module=Sisrute&page=index&action=sep&no_rawat=<?php echo $row['6']; ?>");
        };
      }
    })
  })
})

</script>
