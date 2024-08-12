// Africa
$(function () {
  $("#mapAfrica").vectorMap({
    map: "africa_mill",
    backgroundColor: "transparent",
    scaleColors: ["#116aef"],
    zoomOnScroll: false,
    zoomMin: 1,
    hoverColor: true,
    series: {
      regions: [
        {
          values: gdpData,
          scale: ["#116aef"],
          normalizeFunction: "polynomial",
        },
      ],
    },
  });
});
