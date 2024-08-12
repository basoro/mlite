var optionsPatients = {
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
    fill: {
      type: 'solid',
      opacity: [0.1, 1],
    },
    stroke: {
      curve: "smooth",
      width: [0, 4]
    },
    series: [{
      name: 'New Patients',
      type: 'area',
      data: [400, 550, 350, 450, 300, 350, 270, 320, 330, 410, 300, 490]
    }, {
      name: 'Return Patients',
      type: 'line',
      data: [200, 400, 250, 350, 200, 350, 370, 520, 440, 610, 600, 380]
    }],
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
    legend: {
      position: 'bottom',
      horizontalAlign: 'center',
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
  
  var optionsTreatments = {
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
    fill: {
      type: 'solid',
      opacity: [.1, 1, .5],
    },
    stroke: {
      curve: "smooth",
      width: [0, 4, 0]
    },
    series: [{
      name: 'General',
      type: 'bar',
      data: [400, 550, 350, 450, 300, 350, 270, 320, 330, 410, 300, 490]
    }, {
      name: 'Surgery',
      type: 'line',
      data: [200, 400, 250, 350, 200, 350, 370, 520, 440, 610, 600, 380]
    }, {
      name: 'ICU',
      type: 'bar',
      data: [140, 250, 200, 220, 80, 50, 30, 50, 40, 60, 30, 80]
    }],
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
    colors: ["#116AEF", "#327FF2", "#5394F5", "#75AAF9", "#96BFFC", "#B7D4FF"],
    markers: {
      size: 0,
      opacity: 0.3,
      colors: ["#116AEF", "#327FF2", "#5394F5", "#75AAF9", "#96BFFC", "#B7D4FF"],
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
  
  var optionsCaraBayar = {
    chart: {
      width: 240,
      type: "donut",
    },
    labels: ["Umum", "BPJS", "Lain-Lain"],
    series: [20, 65, 35],
    legend: {
      position: "bottom",
    },
    dataLabels: {
      enabled: false,
    },
    stroke: {
      width: 0,
    },
    colors: ["#ff5a39", "#3e3e42", "#75C2F6"],
  };
  
  var optionsGenderAge = {
    chart: {
      width: 240,
      type: "donut",
    },
    labels: ["Male", "Female"],
    series: [20, 65],
    legend: {
      position: "bottom",
    },
    dataLabels: {
      enabled: false,
    },
    stroke: {
      width: 0,
    },
    colors: ["#116AEF", "#0ebb13"],
  };
    
  var optionsClaims = {
    chart: {
      height: 160,
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
        name: "Claims",
        data: [200, 400, 250, 350, 200, 350, 370, 520, 440, 610, 600, 380]
      },
    ],
    legend: {
      show: false,
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
      axisBorder: {
        show: false,
      },
      yaxis: {
        show: false,
      },
      tooltip: {
        enabled: true,
      },
      labels: {
        show: true,
      },
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
      "#116AEF", "#327FF2", "#5394F5", "#75AAF9", "#96BFFC", "#B7D4FF"
    ],
  };

  var chartPatients = new ApexCharts(document.querySelector("#patients"), optionsPatients);  
  chartPatients.render();

  var chartTreatments = new ApexCharts(document.querySelector("#treatment"), optionsTreatments);
  chartTreatments.render();

  var chartCaraBayar = new ApexCharts(document.querySelector("#caraBayar"), optionsCaraBayar);
  chartCaraBayar.render();
  
  var chartGenderAge = new ApexCharts(document.querySelector("#genderAge"), optionsGenderAge);
  chartGenderAge.render();

  var chartClaims = new ApexCharts(document.querySelector("#claims"), optionsClaims);
  chartClaims.render();

