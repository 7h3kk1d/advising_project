<!-- #BeginTemplate template="advising" 
codeOutsideHTMLIsLocked="true" -->

<?php 
include("databaseConnect.php");
databaseConnect();
include("loginFunctions.php");
session_start();
?>
<html>
<head>
<!-- #BeginEditable "title" -->
<title>Advising Form</title>
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
<TD width="10%" bgcolor = "#eff3d2" style="vertical-align:top">
<?php
include("sidebar.php");

display_sidebar();
?>
</TD>
<TD width = "60%" bgcolor = "#efe0c0" style="vertical-align:top">
<?php
include("advisingFormFunctions.php");
if($_GET['clear']=="1")
	clearAdvising();	
if($_POST['add_advising']=="true")
	addAdvising();	
if($_GET['submit']==1)
	submitInfo();	
else if($_GET['edit']==1)
	if($_SESSION['advisee_CLID'])
		displayAdvisingStudentInfoEdit($_SESSION['advisee_CLID']);
	else
		displayAdvisingStudentInfoEdit($_SESSION['CLID']);
else
	displayAdvisingForm();
?>
</TD>
</TR>
</table>

</body>
</html>
