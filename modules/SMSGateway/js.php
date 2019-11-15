<script type="text/javascript">

  function ajax()
  {
  if (window.XMLHttpRequest)
  {// code for IE7+, Firefox, Chrome, Opera, Safari
     xmlhttp=new XMLHttpRequest();
	 xmlhttp2=new XMLHttpRequest();
  }
  else
  {// code for IE6, IE5
     xmlhttp =new ActiveXObject("Microsoft.XMLHTTP");
	 xmlhttp2 =new ActiveXObject("Microsoft.XMLHTTP");
  }

  xmlhttp.onreadystatechange=function()
  {
  if (xmlhttp.readyState==4 && xmlhttp.status==200)
    {
	<?php
	if (basename($_SERVER['PHP_SELF']) == "report.php")
	{
     echo 'document.getElementById("sms").innerHTML = xmlhttp.responseText;';
	}
	?>
    }
  }

  xmlhttp2.onreadystatechange=function()
  {
  if (xmlhttp2.readyState==4 && xmlhttp2.status==200)
    {
	document.getElementById("service").innerHTML = xmlhttp2.responseText;
    }
  }

  xmlhttp.open("GET","<?php echo URL; ?>/modules/SMSGateway/run.php");
  xmlhttp.send();
  xmlhttp2.open("GET","<?php echo URL; ?>/modules/SMSGateway/service.php");
  xmlhttp2.send();
  setTimeout("ajax()", 8000);
  }
  </script>
