<script>
$('#elemen').dataTable( {
  "bStateSave": true,
  "processing": true,
  "responsive": true,
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
  "columns": [
    null,
    { "searchable": false },
    { "searchable": false },
    { "searchable": false }
] } );
</script>
