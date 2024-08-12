// Morris Donut
Morris.Donut({
  element: "donutColors",
  data: [
    { value: 30, label: "foo" },
    { value: 15, label: "bar" },
    { value: 10, label: "baz" },
    { value: 5, label: "A really really long label" },
  ],
  backgroundColor: "#17181c",
  labelColor: "#17181c",
  colors: [
    "#207a5a",
    "#248a65",
    "#116aef",
    "#3ea37e",
    "#53ad8d",
    "#69b89b",
    "#7ec2a9",
    "#94ccb8",
    "#a9d6c6",
  ],
  resize: true,
  hideHover: "auto",
  gridLineColor: "#dfd6ff",
  formatter: function (x) {
    return x + "%";
  },
});
