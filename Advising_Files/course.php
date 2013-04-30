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
<title>Course</title>
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
<TD width = "65%" bgcolor = "#efe0c0" style="vertical-align:top">
<?php
include("courseFunctions.php");

if($_POST['add_perm']=="true")
	addInstPerm();
if($_POST['delete_prereq']=="true")
	dropPrereq();
if($_POST['delete_counts_for']=="true")
	dropCountsFor();
if($_POST['delete_class']=="true")
	dropClass();
if($_POST['inst_perm']&&$_POST['teacher_permission']!="true")
	dropTeacherPermission();
if($_POST['teacher_permission']=="true")
	addTeacherPermission();
if($_POST['add_prereq']=="true")
	addPrereq();
if($_POST['add_counts_for']=="true")
	addCountsFor();
if($_POST['add_class']=="true")
	submit_new_class();
if($_GET['submit']=="1")
	submitData();
if($_GET['edit']==1)
	displayCourseEdit($_GET['DEPT'], $_GET['NUM']);
else
	displayCourse($_GET['DEPT'], $_GET['NUM']);
?>
</TD>
</TR>
</table>

</body>
</html>
