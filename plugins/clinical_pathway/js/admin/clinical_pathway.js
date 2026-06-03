$(function () {
  if ($('.dataTables').length) {
    $('.dataTables').DataTable({
      order: [[0, 'desc']],
      pagingType: 'full',
      lengthChange: false,
      scrollX: true,
      language: {
        paginate: {
          first: '&laquo;',
          last: '&raquo;',
          previous: '‹',
          next: '›'
        },
        search: '',
        searchPlaceholder: 'Cari...'
      },
      dom: "<<'data-table-title'><'datatable-search'f>><'row'<'col-sm-12'tr>><<'pmd-datatable-pagination' l i p>>"
    });
  }
});
