<?php
session_start();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!--
Design by Free CSS Templates
http://www.freecsstemplates.org
Released for free under a Creative Commons Attribution 2.5 License

Name       : Chocolate Brown 
Description: A two-column, fixed-width design with dark color scheme.
Version    : 1.0
Released   : 20090413

-->
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta name="keywords" content="" />
<meta name="description" content="" />
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<title>PHPSMS V3.0</title>
<link href="style.css" rel="stylesheet" type="text/css" media="screen" />

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
  
  xmlhttp.open("GET","run.php");
  xmlhttp.send();
  xmlhttp2.open("GET","service.php");
  xmlhttp2.send();
  setTimeout("ajax()", 8000);
  }  
  </script>

</head>
<body onload="ajax()">
<div id="wrapper">
	<div id="header">
		<div id="logo">
			<h1><a href="index.php?op=main">PHPSMS V3.0</a></h1>
		</div>
	</div>
	<!-- end #header -->
	<div id="menu">
		<ul>
		    <?php
			if (isset($_SESSION['login']))
			{
			?>
			<li class="current_page_item"><a href="inbox.php?page=1">INBOX</a></li>
			<li><a href="group.php?op=show">GROUP</a></li>
			<li><a href="listphone.php?op=show">PHONEBOOK</a></li>
			<li><a href="sendsms.php?op=single">INSTANT SMS</a></li>
			<li><a href="listmsg.php?op=show">ON SCHEDULED SMS</a></li>
			<li><a href="auto.php?op=show">AUTORESPONDER</a></li>
			<li><a href="report.php?op=show">OUTBOX</a></li>
			<?php
			}
			else
			{
			?>
			<li class="current_page_item">INBOX</li>
			<li>INSTANT SMS</li>
			<li>ON SCHEDULED SMS</li>
			<li>GROUP</li>
			<li>PHONEBOOK</li>
			<li>AUTORESPONDER</li>
			<li>OUTBOX</li>
			<?php
			}
			?>
		</ul>
	</div>
	<!-- end #menu -->
	<div id="page">
	<div id="page-bgtop">
	<div id="page-bgbtm">
		<div id="content">

			<div class="post">
			<div class="post-bgtop">
			<div class="post-bgbtm">
			
<!-------------------------------->
			