<script>
var table = $('#data_antrian').DataTable({
   dom: 'lr<"table-filter-container">tip',
   initComplete: function(settings){
      var api = new $.fn.dataTable.Api( settings );
      $('.table-filter-container', api.table().container()).append(
         $('#table-filter').detach().show()
      );

      $('#table-filter select').on('change', function(){
         table.search(this.value).draw();
      });
   }
});
</script>
