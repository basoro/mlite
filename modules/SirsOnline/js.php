<script type="text/javascript">

var table = $('#datatable').DataTable();
new $.fn.dataTable.Buttons(table, {
    buttons: [
        { extend: 'copy', className: 'btn glyphicon glyphicon-duplicate' },
        { extend: 'csv', className: 'btn glyphicon glyphicon-save-file' },
        { extend: 'excel', className: 'btn glyphicon glyphicon-list-alt' },
        { extend: 'pdf', className: 'btn glyphicon glyphicon-file' },
        { extend: 'print', className: 'btn glyphicon glyphicon-print' }
    ]
}).container().appendTo($('#buttons'));

</script>
