    <script>

          $('#allsep').dataTable( {
                "bInfo": true,
                "bStateSave": true,
              	"scrollX": true,
                "processing": true,
                "serverSide": true,
                "responsive": false,
                "oLanguage": {
                    "sProcessing":   "Sedang memproses...",
                    "sLengthMenu":   "Tampilkan _MENU_ entri",
                    "sZeroRecords":  "Tidak ditemukan data yang sesuai",
                    "sInfo":         "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                    "sInfoEmpty":    "Menampilkan 0 sampai 0 dari 0 entri",
                    "sInfoFiltered": "(disaring dari _MAX_ entri keseluruhan)",
                    "sInfoPostFix":  "",
                    "sSearch":       "Cari:",
                    "sUrl":          "",
                    "oPaginate": {
                        "sFirst":    "«",
                        "sPrevious": "‹",
                        "sNext":     "›",
                        "sLast":     "»"
                    }
                },
                "order": [[ 0, "asc" ]],
                "ajax": "<?php echo URL; ?>/modules/BridgingBPJS/includes/sep.php"
          } );

    </script>

    <script>
      function myFunction(){
    		$("#dpjp").on("change",function(){
            //Getting Value
            var selValue = $("#dpjp :selected").text();
            //Setting Value
            $("#nmdp").val(selValue);
        });}
    </script>
