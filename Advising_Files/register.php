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
<title>Edit Users</title>
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
include("registerFunctions.php");
	//Area to Edit
if($_GET['estu']==1)
  displayAddStudent();
else if ($_GET['eadv']==1)
  displayAddAdvisor();
else if ($_GET['dstu']==1)
  displayDropStudent();
else if ($_GET['dadv']==1)
  displayDropAdvisor();
else{
  displayDefault();
}
if($_GET['stuaddquery'])
  addStudent();
if($_GET['advaddquery'])
  addAdvisor();
if($_GET['studropquery'] and $_GET['stuclid'] and $_GET['stuname'])
  dropStudent($_GET['stuclid'], $_GET['stuname']);
if($_GET['advdropquery'] and $_GET['advclid'] and $_GET['advname'])
  dropAdvisor($_GET['advclid'], $_GET['advname']);
?>
</TD>
</TR>
</table>

</body>
</html>
