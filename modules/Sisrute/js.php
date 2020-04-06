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


    $('.faskes').select2({
      placeholder: 'Pilih faskes tujuan',
      ajax: {
        url: '<?php echo URL; ?>/modules/Sisrute/includes/select-faskes.php',
        dataType: 'json',
        delay: 250,
        processResults: function (data) {
          return {
            results: $.map(data, function(obj){
              return { id: obj.KODE , text: obj.NAMA };
            })
          };
        },
        cache: true
      },
      templateResult: formatData,
    minimumInputLength: 3
    });

    $('.faskes').on('select2:select', function (e) {
     var kode = e.params.data.text;
     document.getElementById("kdfaskes").value = kode;
    });

    $('.alasan').select2({
      placeholder: 'Pilih alasan rujukan',
      ajax: {
        url: '<?php echo URL; ?>/modules/Sisrute/includes/select-alasan.php',
        dataType: 'json',
        delay: 250,
        processResults: function (data) {
          return {
            results: $.map(data, function(obj){
              return { id: obj.KODE , text: obj.NAMA };
            })
          };
        },
        cache: true
      },
      templateResult: formatData,
    });

    $('.alasan').on('select2:select', function (e) {
     var kode = e.params.data.text;
     document.getElementById("kdalasan").value = kode;
    });

    $('.diagnosa').select2({
      placeholder: 'Pilih diagnosa',
      ajax: {
        url: '<?php echo URL; ?>/modules/Sisrute/includes/select-diagnosa.php',
        dataType: 'json',
        delay: 250,
        processResults: function (data) {
          return {
            results: $.map(data, function(obj){
              return { id: obj.KODE , text: obj.NAMA };
            })
          };
        },
        cache: true
      },
      templateResult: formatData,
    minimumInputLength: 3
    });

    $('.diagnosa').on('select2:select', function (e) {
     var kode = e.params.data.text;
     document.getElementById("kddx").value = kode;
    });

    $('.dr').select2({
      placeholder: 'Pilih dokter perujuk',
      ajax: {
        url: '<?php echo URL; ?>/modules/Sisrute/includes/select-dokter.php',
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

    $('.dr').on('select2:select', function (e) {
     var kode = e.params.data.text;
     document.getElementById("kddr").value = kode;
    });

    $('.kdpel').select2({
      placeholder: 'Pilih pelayanan',
      ajax: {
        url: '<?php echo URL; ?>/modules/Sisrute/includes/select-pel.php',
        dataType: 'json',
        delay: 250,
        processResults: function (data) {
          return {
            results: $.map(data, function(obj){
              return { id: obj.kode , text: obj.pelayanan };
            })
          };
        },
        cache: true
      },
      templateResult: formatData,
    // minimumInputLength: 3
    });

    $(document).ready(function() {
      $(".kirim").click(function(){
        var data = $(".form").serialize();
        $.ajax({
          type: 'POST',
          data: data,
          url: '<?php echo URL; ?>/modules/Sisrute/includes/buildsisrute.php',
          success: function(data) {
            alert(data);
          }
        })
      })
    });

    $(function () {
       $('#datetimepicker1').bootstrapMaterialDatePicker({
           format: 'YYYY-MM-DD HH:mm:ss'
       });
     });
</script>
