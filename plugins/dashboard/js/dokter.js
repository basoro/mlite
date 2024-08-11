// Rating
$(function () {
	$.fn.raty.defaults.path = "img";
	$("#rate1").raty({ score: 4 });
	$("#rate2").raty({ score: 5 });
	$("#rate3").raty({ score: 5 });
	$("#rate4").raty({ score: 4 });
	$("#rate5").raty({ score: 3 });
	$("#rate6").raty({ score: 2 });

	$(".rate1").raty({ score: 4 });
	$(".rate2").raty({ score: 5 });
	$(".rate3").raty({ score: 5 });
	$(".rate4").raty({ score: 4 });
	$(".rate5").raty({ score: 3 });
	$(".rate6").raty({ score: 2 });

	$(".rateA").raty({ score: 5 });
	$(".rateB").raty({ score: 4 });
	$(".rateC").raty({ score: 3 });
	$(".rateD").raty({ score: 2 });
	$(".rateE").raty({ score: 1 });

	$(".readonly0").raty({ readOnly: true, score: 0 });
	$(".readonly1").raty({ readOnly: true, score: 1 });
	$(".readonly2").raty({ readOnly: true, score: 2 });
	$(".readonly3").raty({ readOnly: true, score: 3 });
	$(".readonly4").raty({ readOnly: true, score: 4 });
	$(".readonly5").raty({ readOnly: true, score: 5 });
});

var optionsActivity = {
	chart: {
	  height: 150,
	  type: "bar",
	  toolbar: {
		show: false,
	  },
	},
	plotOptions: {
	  bar: {
		columnWidth: "70%",
		borderRadius: 2,
		distributed: true,
		dataLabels: {
		  position: "center",
		},
	  },
	},
	series: [
	  {
		name: "Pasien",
		data: [{$activity.reg_periksa}],
	  },
	],
	legend: {
	  show: false,
	},
	xaxis: {
	  categories: ["{$activity.day}"],
	  axisBorder: {
		show: false,
	  },
	  labels: {
		show: true,
	  },
	},
	yaxis: {
	  show: false,
	},
	grid: {
	  borderColor: "#d8dee6",
	  strokeDashArray: 5,
	  xaxis: {
		lines: {
		  show: true,
		},
	  },
	  yaxis: {
		lines: {
		  show: false,
		},
	  },
	  padding: {
		top: 0,
		right: 0,
		bottom: 0,
		left: 0,
	  },
	},
	tooltip: {
	  y: {
		formatter: function (val) {
		  return val;
		},
	  },
	},
	colors: [
	  "rgba(255, 255, 255, 0.7)", "rgba(255, 255, 255, 0.6)", "rgba(255, 255, 255, 0.5)", "rgba(255, 255, 255, 0.4)", "rgba(255, 255, 255, 0.3)", "rgba(255, 255, 255, 0.2)", "rgba(255, 255, 255, 0.2)"
	],
  };
  
  var optionsIncome = {
	chart: {
	  height: 300,
	  type: "line",
	  toolbar: {
		show: false,
	  },
	},
	dataLabels: {
	  enabled: false,
	},
	stroke: {
	  curve: "smooth",
	  width: 3,
	},
	series: [
	  {
		name: "Patients",
		data: [100, 400, 150, 400, 200, 350, 150, 300, 200, 450, 300, 560],
	  },
	  {
		name: "Income",
		data: [80, 240, 200, 550, 300, 450, 280, 390, 290, 500, 490, 650],
	  }
	],
	grid: {
	  borderColor: "#d8dee6",
	  strokeDashArray: 5,
	  xaxis: {
		lines: {
		  show: true,
		},
	  },
	  yaxis: {
		lines: {
		  show: false,
		},
	  },
	  padding: {
		top: 0,
		right: 0,
		bottom: 0,
		left: 0,
	  },
	},
	xaxis: {
	  categories: [
		"Jan",
		"Feb",
		"Mar",
		"Apr",
		"May",
		"Jun",
		"Jul",
		"Aug",
		"Sep",
		"Oct",
		"Nov",
		"Dec",
	  ],
	},
	yaxis: {
	  labels: {
		show: false,
	  },
	},
	colors: ["#116AEF", "#0ebb13", "#5394F5", "#75AAF9", "#96BFFC", "#B7D4FF"],
	markers: {
	  size: 0,
	  opacity: 0.3,
	  colors: ["#116AEF", "#0ebb13", "#5394F5", "#75AAF9", "#96BFFC", "#B7D4FF"],
	  strokeColor: "#ffffff",
	  strokeWidth: 1,
	  hover: {
		size: 7,
	  },
	},
	tooltip: {
	  y: {
		formatter: function (val) {
		  return val;
		},
	  },
	},
  };
  
  var optionsOrders = {
	chart: {
	  height: 300,
	  type: "bar",
	  toolbar: {
		show: false,
	  },
	},
	dataLabels: {
	  enabled: false,
	},
	plotOptions: {
	  bar: {
		horizontal: false,
		columnWidth: '20%',
	  },
	},
	stroke: {
	  show: true,
	  width: 6,
	  colors: ['transparent']
	},
	series: [
	  {
		name: "Orders",
		data: [100, 200, 300, 400, 300, 200, 150, 300, 200, 450, 300, 500],
	  }
	],
	grid: {
	  borderColor: "#d8dee6",
	  strokeDashArray: 5,
	  xaxis: {
		lines: {
		  show: true,
		},
	  },
	  yaxis: {
		lines: {
		  show: false,
		},
	  },
	  padding: {
		top: 0,
		right: 0,
		bottom: 0,
		left: 0,
	  },
	},
	xaxis: {
	  categories: [
		"Jan",
		"Feb",
		"Mar",
		"Apr",
		"May",
		"Jun",
		"Jul",
		"Aug",
		"Sep",
		"Oct",
		"Nov",
		"Dec",
	  ],
	},
	yaxis: {
	  labels: {
		show: false,
	  },
	},
	colors: ["#116aef", "#ff3939", "#436ccf", "#dcad10", "#828382"],
	markers: {
	  size: 0,
	  opacity: 0.3,
	  colors: ["#116aef", "#ff3939", "#436ccf", "#dcad10", "#828382"],
	  strokeColor: "#ffffff",
	  strokeWidth: 1,
	  hover: {
		size: 7,
	  },
	},
	tooltip: {
	  y: {
		formatter: function (val) {
		  return val;
		},
	  },
	},
  };
  
  var chartActivity = new ApexCharts(document.querySelector("#docActivity"), optionsActivity);
  chartActivity.render();

  var chartIncome = new ApexCharts(document.querySelector("#income"), optionsIncome);
  chartIncome.render();

  var chartOrders = new ApexCharts(document.querySelector("#orders"), optionsOrders);
  chartOrders.render();

