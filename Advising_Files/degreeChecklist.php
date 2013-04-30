<?php 
	include("databaseConnect.php");
	databaseConnect();
	include("loginFunctions.php");
	session_start();
?>

<html>
<head>
<!-- #eBeginEditable "title" -->
<title>Degree Checklist</title>
<!-- #EndEditable -->
</head>

<body vlink = "#871F78" link = "#8B0000" bgcolor = "#FFFFFF" text = "#000000">
<table cellpadding = "0" cellspacing = "0" align = "right" width = "100%">
<TR>
<TD bgcolor="#FFFFFF" width = "70%">
</TD>
<TD width = "10%" bgcolor = "#eff3d2" align="center" style="vertical-align:top">

<?php
	checkLogin();
	displayLogin();
?>

</TD>
</TR>
</table><br><br>

<table cellpadding = "5" cellspacing = "5" align = "center" width = "70%">
<TR>
<TD width="15%" bgcolor = "#eff3d2" style="vertical-align:top">

<u></u>

<?php

	include("sidebar.php");
	display_sidebar();
?>

<u></u>
</TD>
<TD width = "60%" bgcolor = "#efe0c0">
<u></u>

<?php
	echo "<u></u>";
	include("meetFunctions.php");
	echo "<u></u>";
	include("degreeChecklistFunctions.php");
	echo "<u></u>";
	degreeChecklist();
?>

</TD>
</TR>
</table>
</body>
</html>

