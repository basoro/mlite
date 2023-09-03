$('.dataTables').DataTable({
  "order": [[ 1, "desc" ]],
  "pagingType": "full",
  "language": {
    "paginate": {
      "first": "&laquo;",
      "last": "&raquo;",
      "previous": "‹",
      "next":     "›"
    },
    "search": "",
    "searchPlaceholder": "Search..."
  },
  "lengthChange": false,
  "scrollX": true,
  dom: "<<'data-table-title'><'datatable-search'f>><'row'<'col-sm-12'tr>><<'pmd-datatable-pagination' l i p>>"
});
var t = $(".dataTables").DataTable().rows().count();
$(".data-table-title").html('<h3 style="display:inline;float:left;margin-top:0;" class="hidden-xs">Total: ' + t + '</h3>');
$('.dataTables_filter input').addClass('form-control pencarian');
$('.displayData').DataTable();

$(function () {
    $('.tanggaljam').datetimepicker({
      format: 'YYYY-MM-DD HH:mm:ss',
      locale: 'id'
    });
});

$(function () {
    $('.tanggal').datetimepicker({
      format: 'YYYY-MM-DD',
      locale: 'id'
    });
});
