$(document).ready(function () {
  // Create an array of random colors
  var colors = ["#116aef", "#ce313e", "#436ccf", "#dcad10", "#02b86f", "#151516"];

  // Iterate over all td elements in the table
  $(".randomTableColors.table td").each(function () {
    // Get a random color from the array
    var color = colors[Math.floor(Math.random() * colors.length)];

    // Set the background color of the td element to the random color
    $(this).css("color", color);
  });
});