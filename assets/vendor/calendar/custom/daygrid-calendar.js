document.addEventListener("DOMContentLoaded", function () {
  var calendarEl = document.getElementById("dayGrid");
  var calendar = new FullCalendar.Calendar(calendarEl, {
    headerToolbar: {
      left: "prevYear,prev,next,nextYear today",
      center: "title",
      right: "dayGridMonth,dayGridWeek,dayGridDay",
    },
    initialDate: "2024-05-10",
    navLinks: true, // can click day/week names to navigate views
    editable: true,
    dayMaxEvents: true, // allow "more" link when too many events
    events: [
      {
        title: "Annual Meeting",
        start: "2024-05-01",
        color: "#116aef",
      },
      {
        title: "Clinical Research Conference",
        start: "2024-05-07",
        end: "2024-05-10",
        color: "#1D93DF",
      },
      {
        groupId: 999,
        title: "Gynecological Ultrasound",
        start: "2024-05-09T16:00:00",
        color: "#3AA2E5",
      },
      {
        groupId: 999,
        title: "Ultrafest Conference",
        start: "2024-05-16T16:00:00",
        color: "#57B0EB",
      },
      {
        title: "Conference",
        start: "2024-05-11",
        end: "2024-05-13",
        color: "#74BFF0",
      },
      {
        title: "Meeting",
        start: "2024-05-14T10:30:00",
        end: "2024-05-14T12:30:00",
        color: "#91CEF6",
      },
      {
        title: "Lunch",
        start: "2024-05-16T12:00:00",
        color: "#9FD5F9",
      },
      {
        title: "Ultrafest",
        start: "2024-05-18T14:30:00",
        color: "#BCE4FF",
      },
      {
        title: "Interview",
        start: "2024-05-21T17:30:00",
        color: "#48A9E8",
      },
      {
        title: "Meeting",
        start: "2024-05-22T20:00:00",
        color: "#74BFF0",
      },
      {
        title: "Conference",
        start: "2024-05-13T07:00:00",
        color: "#AEDDFC",
      },
      {
        title: "Click for Google",
        url: "http://bootstrap.gallery/",
        start: "2024-05-28",
        color: "#1D93DF",
      },
      {
        title: "Interview",
        start: "2024-05-20",
        color: "#82C6F3",
      },
      {
        title: "Surgery Meet",
        start: "2024-05-29",
        color: "#0E8BDC",
      },
      {
        title: "Management Meet",
        start: "2024-05-25",
        color: "#91CEF6",
      },
    ],
  });

  calendar.render();
});
