$(function () {
    sortable('.modules', {handle:'i'})[0].addEventListener('sortupdate', function(e) {
        var baseURL = mlite.url + '/' + mlite.admin;
        var items   = {};

        $(e.detail.endparent).children('li').each(function(index, element) {
            var module = $(element).data('module');
            items[module] = index;
        });

        $.ajax({
            url: baseURL + '/dashboard/changeOrderOfNavItem?t=' + mlite.token,
            type: 'POST',
            cache: false,
            data: items,
            success: function(respond) {
                console.log(respond);
            }
        });
    });
});
