<!DOCTYPE html>
<html lang="en">
<head>
	<title>Menampilkan Grafik NAMA_TABLE</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {font-family: sans-serif;}
        ul {
            list-style-type: none;
            margin: 0;
            padding: 0;
            overflow: hidden;
            background-color: #333;
        }
        li {
            float: left;
        }
        li a {
            display: block;
            color: white;
            text-align: center;
            padding: 14px 16px;
            text-decoration: none;
        }
        li a:hover {
            background-color: #111;
        }
        .active {
            background-color: #d50707;
        }
        .dropdown-content {
            display: none;
            position: absolute;
            background-color: #f9f9f9;
            min-width: 160px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 1;
        }
        .dropdown-content a {
            float: none;
            color: black;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
            text-align: left;
        }
        .dropdown-content a:hover {
            background-color: #ddd;
        }
        .dropdown:hover .dropdown-content {
            display: block;
        }  
    </style>
</head>
<body>
	<div class="container pt-5">	
        <div class="nav">
            <ul>
                <li class="item {if: parseUrl()[2] == '' || parseUrl()[2] == 'pie'}active{/if}">
                    {if: isset_or(parseUrl()[3])} 
                        <a href="{?=url(['MODULE_NAME','chart','pie', parseUrl()[3]])?}">Pie Chart</a>
                    {else}
                        <a href="{?=url(['MODULE_NAME','chart','pie'])?}">Pie Chart</a>
                    {/if}
                </li>
                <li class="item {if: parseUrl()[2] == 'line'}active{/if}">
                    {if: isset_or(parseUrl()[3])} 
                        <a href="{?=url(['MODULE_NAME','chart','line', parseUrl()[3]])?}">Line Chart</a>
                    {else}
                        <a href="{?=url(['MODULE_NAME','chart','line'])?}">Line Chart</a>
                    {/if}
                </li>
                <li class="item {if: parseUrl()[2] == 'bar'}active{/if}">
                    {if: isset_or(parseUrl()[3])} 
                        <a href="{?=url(['MODULE_NAME','chart','bar', parseUrl()[3]])?}">Bar Chart</a>
                    {else}
                        <a href="{?=url(['MODULE_NAME','chart','bar'])?}">Bar Chart</a>
                    {/if}
                </li>
                <li class="dropdown">
                    <a href="#">Datasets</a>
                    <div class="dropdown-content">
                        {loop: $column}
                            {if: isset_or(parseUrl()[3])} 
                                <a href="{?=url(['MODULE_NAME','chart',parseUrl()[2], $value.COLUMN_NAME])?}">{$value.COLUMN_NAME}</a>
                            {else}
                                <a href="{?=url(['MODULE_NAME','chart','pie', $value.COLUMN_NAME])?}">{$value.COLUMN_NAME}</a>
                            {/if}
                        {/loop}
                    </div>                    
                </li>
            </ul>
        </div>
		<h1>Menampilkan Grafik NAMA_TABLE</h1>
		<div class="chart-container" style="position: relative; height:70vh;">
			<canvas id="MODULE_NAME_chart"></canvas>
		</div>
	</div>
</body>
<script src="{?=url()?}/assets/js/chart.js"></script>
<script>
	const ctx = document.getElementById('MODULE_NAME_chart');
	new Chart(ctx, {
		type: '{$type}',
		data: {
			labels: {$labels},
			datasets: [{
				label: '# of {if: isset_or(parseUrl()[3])}{?=parseUrl()[3]?}{else}CHART{/if}',
				data: {$datasets}
			}]
		},
		options: {
			scales: {
				y: {
					beginAtZero: true
				}
			}
        }
	});
</script>
</html>