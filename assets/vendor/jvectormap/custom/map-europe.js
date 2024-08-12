// Europe
$(function () {
  $("#mapEurope").vectorMap({
    map: "europe_mill",
    zoomOnScroll: false,
    series: {
      regions: [
        {
          values: gdpData,
          scale: ["#116aef"],
          normalizeFunction: "polynomial",
        },
      ],
    },
    backgroundColor: "transparent",
  });
});
