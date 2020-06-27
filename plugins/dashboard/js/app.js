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