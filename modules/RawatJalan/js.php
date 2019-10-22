<?php  ?>
<script type="text/javascript">

    function formatData (data) {
        var $data = $(
            '<b>'+ data.id +'</b> - <i>'+ data.text +'</i>'
        );
        return $data;
    };

    function formatInputData (data) {
          var $data = $(
              '<b>('+ data.id +')</b> Rp '+ data.tarif +' - <i>'+ data.text +'</i>'
          );
          return $data;
      };

    function formatDataTEXT (data) {
        var $data = $(
            '<b>'+ data.text +'</b>'
        );
        return $data;
    };


    $('.kd_tdk').select2({
      placeholder: 'Pilih tindakan',
      ajax: {
        url: '<?php echo URL; ?>/modules/RawatJalan/includes/select-tindakan.php',
        dataType: 'json',
        delay: 250,
        processResults: function (data) {
          return {
            results: data
          };
        },
        cache: true
      },
      templateResult: formatInputData,
    minimumInputLength: 3
    });

    $('.kd_tdk').on('change', function () {
     var kode = $("#kd_tdk").val();
     $.ajax({
      url: '<?php echo URL; ?>/modules/RawatJalan/includes/biaya.php',
      data: "kode="+kode,
     }).success(function (data){
       var json = data,
           obj = JSON.parse(json);
          $('#kdtdk').val(obj.tarif);
       });
    });


</script>
<script type="text/javascript">
  function formatInputData (data) {
              var $data = $(
                  '<b>Bed '+ data.id +'</b> '+ data.kelas +' - <i>'+ data.text +'</i>'
              );
              return $data;
          };
  function formatDataTEXT (data) {
            var $data = $(
                '<b>'+ data.id +'</b>'
            );
            return $data;
        };
  $('.kamar').select2({
          placeholder: 'Pilih kamar',
          ajax: {
            url: 'modules/RawatJalan/includes/select-kamar.php',
            dataType: 'json',
            delay: 250,
            processResults: function (data) {
              return {
                results: data
              };
            },
            cache: true
          },
          templateResult: formatInputData,
      	minimumInputLength: 3
        });

  	$('.kamar').on('change', function () {
         var kode = $("#kamar").val();
         $.ajax({
         	url: 'modules/RawatJalan/includes/biayabed.php',
         	data: "kode="+kode,
         }).success(function (data){
           var json = data,
               obj = JSON.parse(json);
           		$('#kmr').val(obj.tarif);
           });
        });
</script>
<script>

    $(document).ready(function() {

        $('#riwayatmedis').dataTable( {
          bStateSave: true,
          responsive: true
        } );

        $('#datatable_ralan').dataTable( {
          bStateSave: true,
          responsive: true,
          order: [[ 2, 'asc' ]]
        } );
        $('#datatable_ranap').dataTable( {
          bStateSave: true,
          responsive: true,
          order: [[ 4, 'asc' ]]
        } );
        $('#databooking').dataTable( {
          bStateSave: true,
          responsive: true,
          order: [[ 1, 'asc' ]]
        } );

    } );

</script>
