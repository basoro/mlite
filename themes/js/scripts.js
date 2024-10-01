//toggle sidebar
$(".toggle-sidebar").on("click", function () {
  $(".page-wrapper").toggleClass("toggled");
});

// Loading
$(function () {
  $("#loading-wrapper").fadeOut(500);
});

// Tooltip
var tooltipTriggerList = [].slice.call(
  document.querySelectorAll('[data-bs-toggle="tooltip"]')
);
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
  return new bootstrap.Tooltip(tooltipTriggerEl);
});

// Popover
var popoverTriggerList = [].slice.call(
  document.querySelectorAll('[data-bs-toggle="popover"]')
);
var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
  return new bootstrap.Popover(popoverTriggerEl);
});

if(window.localStorage.getItem('pinned-sidebar') === 'active') {
  $(".page-wrapper").addClass("pinned");
}

if(window.localStorage.getItem('pinned-sidebar') === 'not_active') {
  $(".page-wrapper").removeClass("pinned");
}

// Pin sidebar on click
$(".pin-sidebar").on("click", function () {

  var updated = '';
  if (window.localStorage.getItem('pinned-sidebar') === 'active') {
      updated = 'not_active';
    } else {
      updated = 'active';
    }
  window.localStorage.setItem('pinned-sidebar', updated);  

  if ($(".page-wrapper").hasClass("pinned")) {
    // unpin sidebar when hovered
    $(".page-wrapper").removeClass("pinned");
    $("#sidebar").unbind("hover");
  } else {
    $(".page-wrapper").addClass("pinned");
    $("#sidebar").hover(
      function () {
        // console.log("mouseenter");
        $(".page-wrapper").addClass("sidebar-hovered");
      },
      function () {
        // console.log("mouseout");
        $(".page-wrapper").removeClass("sidebar-hovered");
      }
    );
  }
});

// Pinned sidebar
$(function () {
  $(".page-wrapper").hasClass("pinned");
  $("#sidebar").hover(
    function () {
      // console.log("mouseenter");
      $(".page-wrapper").addClass("sidebar-hovered");
    },
    function () {
      // console.log("mouseout");
      $(".page-wrapper").removeClass("sidebar-hovered");
    }
  );
});

// Selectator
$('select').each(function () {
    var options = {
        useDimmer: true,
        useSearch: true,
        labels: {
            search: '...'
        }
    };
    $.each($(this).data(), function (key, value) {
        options[key] = value;
    });
    $(this).selectator(options);
  });
  
  // Bootbox
  $(document).on('click touchstart', '[data-confirm]:not(.disabled):not([disabled])', function(evt) {
    evt.preventDefault();
    var text = $(this).attr('data-confirm');
    var source = $(this);
  
    bootbox.confirm({
        message: text,
        callback: function(result) {
            if(result) {
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
  
  // Remote modal
  $('a[data-toggle="modal"]').on('click', function(e) {
    var target_modal = $(e.currentTarget).data('bs-target');
    var remote_content = $(e.currentTarget).attr('href');
    console.log(remote_content);
  
    if(remote_content.indexOf('#') === 0) return;
  
    var modal = $(target_modal);
    var modalContent = $(target_modal + ' .modal-content');
    
    modal.off('show.bs.modal');
    modal.on('show.bs.modal', function () {
        modalContent.load(remote_content);
    }).modal('show');

    return false;
  });

  /* 5. NOTIFICATION
--------------------------------------------------------- */
$(function () {
	if($('#notify').length) {
		$('#notify').slideDown(500);
        if($( window ).width() < 768)
            $('.app-body').animate({'top' : '+=36'}, 500);

		setTimeout(function() {
			$('#notify').slideUp(500);
            if($( window ).width() < 768)
                $('.app-body').animate({'top' : '-=36'}, 500);
		}, 5000);
	}
});

if (typeof $.fn.slimScroll != 'undefined') {
    var height = ($(window).height() - 210);
    var $el = $('.sidebar-menu');

    $el.slimscroll({
        height: height + "px",
        color: 'rgba(0,0,0,0.5)',
        size: '4px',
        alwaysVisible: false,
        borderRadius: '0',
        railBorderRadius: '0'
    });

    //Scroll active menu item when page load, if option set = true
    var item = $('.sidebarMenuScroll .sidebar-menu li.active')[0];
    // console.log(item);
    if (item) {
        var activeItemOffsetTop = item.offsetTop;
        if (activeItemOffsetTop > 50) $el.slimscroll({ scrollTo: activeItemOffsetTop + 'px' });
    }
}

function umurDaftar(dateString) {
    var now = new Date();
    var today = new Date(now.getYear(),now.getMonth(),now.getDate());
  
    var yearNow = now.getYear();
    var monthNow = now.getMonth();
    var dateNow = now.getDate();
  
    var dob = new Date(dateString.substring(6,10),
                       dateString.substring(0,2)-1,                   
                       dateString.substring(3,5)                  
                       );
  
    var yearDob = dob.getYear();
    var monthDob = dob.getMonth();
    var dateDob = dob.getDate();
    var age = {};
    var ageString = "";
    var yearString = "";
    var monthString = "";
    var dayString = "";
  
  
    yearAge = yearNow - yearDob;
  
    if (monthNow >= monthDob)
      var monthAge = monthNow - monthDob;
    else {
      yearAge--;
      var monthAge = 12 + monthNow -monthDob;
    }
  
    if (dateNow >= dateDob)
      var dateAge = dateNow - dateDob;
    else {
      monthAge--;
      var dateAge = 31 + dateNow - dateDob;
  
      if (monthAge < 0) {
        monthAge = 11;
        yearAge--;
      }
    }
  
    age = {
        years: yearAge,
        months: monthAge,
        days: dateAge
        };
  
    if ( age.years > 1 ) yearString = " Th";
    else yearString = " Th";
    if ( age.months> 1 ) monthString = " Bl";
    else monthString = " Bl";
    if ( age.days > 1 ) dayString = " Hr";
    else dayString = " Hr";
  
  
    if ( (age.years > 0) && (age.months > 0) && (age.days > 0) )
      ageString = age.years + yearString + " " + age.months + monthString + " " + age.days + dayString;
    else if ( (age.years == 0) && (age.months == 0) && (age.days > 0) )
      ageString = age.days + dayString;
    else if ( (age.years > 0) && (age.months == 0) && (age.days == 0) )
      ageString = age.years + yearString;
    else if ( (age.years > 0) && (age.months > 0) && (age.days == 0) )
      ageString = age.years + yearString + " " + age.months + monthString;
    else if ( (age.years == 0) && (age.months > 0) && (age.days > 0) )
      ageString = age.months + monthString + " " + age.days + dayString;
    else if ( (age.years > 0) && (age.months == 0) && (age.days > 0) )
      ageString = age.years + yearString + " " + age.days + dayString;
    else if ( (age.years == 0) && (age.months > 0) && (age.days == 0) )
      ageString = age.months + monthString + " old.";
    else ageString = "Oops! Could not calculate age!";
  
    return ageString;
}  
  
function OpenModal(loadURL)
{
    var modal = $('#myModal');
    var modalContent = $('#myModal .modal-content');

    modal.off('show.bs.modal');
    modal.on('show.bs.modal', function () {
        modalContent.load(loadURL);
    }).modal('show');
    return false;
}

function OpenPDF(loadURL)
{
  window.open(loadURL, '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");
  return false;
}

$("#myInput").on("keyup", function() {
  var value = this.value.toLowerCase().trim();
  $("ul.sidebar-menu li").show().filter(function() {
    return $(this).text().toLowerCase().trim().indexOf(value) == -1;
  }).hide();
});

$(".datepicker").daterangepicker({
  singleDatePicker: true,
  showDropdowns: true,
  locale: {
      format: "YYYY-MM-DD",
  },
});

$('.daterange').daterangepicker({
  opens: 'left'
}, function(start, end, label) {
  $('#tanggal_awal').val(start.format('YYYY-MM-DD'));
  $('#tanggal_akhir').val(end.format('YYYY-MM-DD'));
});

$(".datetimepicker").daterangepicker({
  timePicker: true,
  use24hours: true,
  showMeridian: false, 
  singleDatePicker: true,
  showDropdowns: true,
  locale: {
      format: "YYYY-MM-DD hh:mm:ss",
  }
});      

$(".timepicker").daterangepicker({
  timePicker : true,
  singleDatePicker:true,
  timePicker24Hour : true,
  timePickerIncrement : 1,
  timePickerSeconds : true,
  startDate: moment().format('HH:mm:ss'),
  locale : {
      format : 'HH:mm:ss'
  }
}).on('show.daterangepicker', function(ev, picker){
  picker.container.find(".calendar-table").hide()
});
