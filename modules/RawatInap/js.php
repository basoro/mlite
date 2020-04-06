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
        $('table.datatable').DataTable( {
          bStateSave: true,
          responsive: true,
          order: [[ 4, 'asc' ]]
        } );
    });

    $('.dpjp').select2({
      placeholder: 'Pilih Dokter',
      ajax: {
        url: '<?php echo URL; ?>/modules/RawatInap/includes/select-dokter.php',
        dataType: 'json',
        delay: 250,
        processResults: function (data) {
          return {
            results: data
          };
        },
        cache: true
      },
      templateResult: formatData,
      minimumInputLength: 3
    });

    $('.kd_poli').select2({
      placeholder: 'Pilih poli',
      ajax: {
        url: '<?php echo URL; ?>/modules/RawatInap/includes/select-poli.php',
        dataType: 'json',
        delay: 250,
        processResults: function (data) {
          return {
            results: data
          };
        },
        cache: true
      },
      templateResult: formatData,
      minimumInputLength: 3
    });

    setInterval(function() {
      $('#antri').load("<?php echo URL; ?>/modules/RawatInap/includes/ambil.php").fadeIn('slow');
    }, 1000);

    $(".tglprk").on("change", function(e) {
      var kode = $("#tglprk").val();
      var poli = $("#kd_poli").val();
      $.ajax({
        url: '<?php echo URL; ?>/modules/RawatInap/includes/noreg.php',
        data: {kode:kode,poli:poli},
        success: function(data){
          var json = data;
          obj = JSON.parse(json);
          $('#noreg').val(obj.noreg);
        }
      })
    });

    $(document).ready(function() {
      $(".simpan").click(function(){
        var data = $(".data").serialize();
        $.ajax({
          type: 'POST',
          data: data,
          url: '<?php echo URL; ?>/modules/RawatInap/includes/simpan-survei.php',
          success: function(data) {
            alert(data);
          }
        })
      })
    });
    $("#mulai").datepicker({
    minDate: 0,
    maxDate: '+1Y+6M',
    onSelect: function (dateStr) {
        var min = $(this).datepicker('getDate'); // Get selected date
        $("#akhir").datepicker('option', 'minDate', min || '0'); // Set other min, default to today
    }
    });

      $("#akhir").datepicker({
          minDate: '0',
          maxDate: '+1Y+6M',
          onSelect: function (dateStr) {
              var max = $(this).datepicker('getDate'); // Get selected date
              $('#datepicker').datepicker('option', 'maxDate', max || '+1Y+6M'); // Set other max, default to +18 months
              var start = $("#mulai").datepicker("getDate");
              var end = $("#akhir").datepicker("getDate");
              var days = (end - start) / (1000 * 60 * 60 * 24);
              $("#total").val(days);
          }
      });
</script>
