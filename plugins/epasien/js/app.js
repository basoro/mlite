
/* 1. TOGGLE SIDEBAR
--------------------------------------------------------- */
$("#sidebar-toggle").click(function(e) {
    e.preventDefault();
    $("#wrapper").toggleClass("toggled");
});

/* 2. COLLAPSE LINKS IN SIDEBAR
--------------------------------------------------------- */
$('.sidebar-nav li a').click(function(e)
{
    if($('li:hidden', $(this).next()).length)
    {
        e.preventDefault();
        $('.sidebar-nav li ul.in').collapse('hide');
        $(this).next('ul').collapse('show');
    }
    else if($('li:visible', $(this).next()).length)
    {
        e.preventDefault();
    	$(this).next('ul').collapse('hide');
    }
});
$('.sidebar-nav li.active ul').addClass('in');

/* 3. CONFIRM BOX
--------------------------------------------------------- */
$(document).on('click touchstart', '[data-confirm]:not(.disabled):not([disabled])', function(evt)
{
    evt.preventDefault();
    var text = $(this).attr('data-confirm');
    var source = $(this);

    bootbox.confirm({
        message: text,
        callback: function(result) {
            if(result)
            {
                if(source.is('[type="submit"]'))
                {
                    $(document).off('click touchstart', '[data-confirm]:not(.disabled):not([disabled])');
                    source.click();
                }
                else if(source.is('a'))
                {
                    $(location).attr('href', source.attr('href'));
                }
            }
        }
    });
});

/* 4. TOOLTIP ACTIVATION
--------------------------------------------------------- */
$(function () {
    $("[data-toggle='tooltip']").tooltip();
    $("[data-toggle='popover']").popover();
});

/* 5. NOTIFICATION
--------------------------------------------------------- */
$(function () {
	if($('#notify').length)
    {
		$('#notify').slideDown(500);
        if($( window ).width() < 768)
            $('#content-wrapper').animate({'top' : '+=46'}, 500);

		setTimeout(function() {
			$('#notify').slideUp(500);
            if($( window ).width() < 768)
                $('#content-wrapper').animate({'top' : '-=46'}, 500);
		}, 8000);
	}
});

/* 6. SORTABLE SIDEBAR
--------------------------------------------------------- */
$(function () {
    sortable('.sidebar-nav', {handle:'i'})[0].addEventListener('sortupdate', function(e) {
        var baseURL = opensimrs.url + '/' + opensimrs.admin;
        var items   = {};

        $(e.detail.endparent).children('li').each(function(index, element) {
            var module = $(element).data('module');
            items[module] = index;
        });

        $.ajax({
            url: baseURL + '/settings/changeOrderOfNavItem?t=' + opensimrs.token,
            type: 'POST',
            cache: false,
            data: items,
            success: function(respond) {
                console.log(respond);
            }
        });
    });
});

/* 7. TINYNAV
--------------------------------------------------------- */
$(function () {
    $('.panel-heading .nav-tabs').tinyNav({
        active: 'active'
    });
});

/* 8. CUSTOM CHECKBOXES & RADIO BUTTONS
--------------------------------------------------------- */
$(':checkbox').kalypto();
$(':radio').kalypto({toggleClass: 'toggleR'});

/* 9. REMOTE MODAL
--------------------------------------------------------- */
$('a[data-toggle="modal"]').on('click', function(e) {
    var target_modal = $(e.currentTarget).data('target');
    var remote_content = $(e.currentTarget).attr('href');

    if(remote_content.indexOf('#') === 0) return;

    var modal = $(target_modal);
    var modalContent = $(target_modal + ' .modal-content');

    modal.off('show.bs.modal');
    modal.on('show.bs.modal', function () {
        modalContent.load(remote_content);
    }).modal();

    return false;
});

/* 10. CUSTOM SELECT
--------------------------------------------------------- */
$('select').each(function () {
    var options = {
        useDimmer: true,
        useSearch: false,
        labels: {
            search: '...'
        }
    };
    $.each($(this).data(), function (key, value) {
        options[key] = value;
    });
    $(this).selectator(options);
});

$(document).ready(function() {
    var defaultColors = [
        "rgba(131, 58, 163, 0.5)", "rgba(201, 216, 88, 0.5)", "rgba(5, 183, 196, 0.5)", "rgba(139, 20, 229, 0.5)", "rgba(85, 150, 219, 0.5)", "rgba(46, 151, 155, 0.5)", "rgba(169, 99, 226, 0.5)", "rgba(90, 27, 209, 0.5)", "rgba(123, 160, 3, 0.5)", "rgba(161, 95, 226, 0.5)", "rgba(201, 59, 214, 0.5)", "rgba(9, 102, 104, 0.5)", "rgba(81, 118, 186, 0.5)", "rgba(220, 63, 252, 0.5)", "rgba(252, 63, 82, 0.5)", "rgba(97, 249, 176, 0.5)", "rgba(232, 30, 154, 0.5)", "rgba(239, 7, 231, 0.5)", "rgba(107, 239, 211, 0.5)", "rgba(168, 10, 23, 0.5)", "rgba(221, 90, 99, 0.5)", "rgba(35, 102, 237, 0.5)", "rgba(15, 226, 216, 0.5)", "rgba(63, 122, 211, 0.5)", "rgba(226, 88, 86, 0.5)", "rgba(232, 98, 85, 0.5)", "rgba(168, 6, 226, 0.5)"
    ];
    var charts = [];
    $('[data-chart]').each(function() {
        var name = $(this).attr('id') || false;

        if(name === false)
            return;

        var type = $(this).data('chart');
        var labels = $(this).data('labels');
        var data = $(this).data('datasets');

        var options = {};
        if(type == 'bar')
        {
            options = Object.assign(options, {scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero:true
                        }
                    }]
                }});
        }

        var backgroundColor = function() {
            if(type == 'pie')
                return defaultColors;
            else
                return 'rgba(248, 190, 18, 0.2)';
        }
        var datasets = [];
        data = eval(data);
        data.forEach(function(e) {
            datasets.push(Object.assign({
                label: '',
                data: [],
                borderWidth: 1,
                backgroundColor: backgroundColor()
            }, e))
        });

        var ctx = document.getElementById(name);
        var myChart = new Chart(ctx, {
            type: type,
            data: {
                labels: labels,
                datasets: datasets
            },
            options: options
        });
    });
});

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
