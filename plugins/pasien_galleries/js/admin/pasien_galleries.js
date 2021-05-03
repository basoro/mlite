$(document).ready(function(){
  $('.display').DataTable({
    "language": { "search": "", "searchPlaceholder": "Search..." },
    "lengthChange": false,
    "scrollX": true,
    dom: "<<'data-table-title'><'datatable-search'f>><'row'<'col-sm-12'tr>><<'pmd-datatable-pagination' l i p>>"
  });
  var t = $(".display").DataTable().rows().count();
  $(".data-table-title").html('<h3 style="display:inline;float:left;margin-top:0;" class="hidden-xs">Total: ' + t + '</h3>');
});
$(document).ready(function () {
  var strip_tags = function(str) {
    return (str + '').replace(/<\/?[^>]+(>|$)/g, '')
  };
  var truncate_string = function(str, chars) {
    if ($.trim(str).length <= chars) {
      return str;
    } else {
      return $.trim(str.substr(0, chars)) + '...';
    }
  };
  $('select').selectator('destroy');
  $('.pasien_ajax').selectator({
    labels: {
      search: 'Cari pasien...'
    },
    load: function (search, callback) {
      if (search.length < this.minSearchLength) return callback();
      $.ajax({
        url: '{?=url()?}/admin/pasien_galleries/ajax?show=pasien&s=' + encodeURIComponent(search) + '&t={?=$_SESSION['token']?}',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
          callback(data.slice(0, 100));
          console.log(data);
        },
        error: function() {
          callback();
        }
      });
    },
    delay: 500,
    minSearchLength: 4,
    valueField: 'no_rkm_medis',
    textField: 'nm_pasien'
  });
  $('select').selectator();
});
