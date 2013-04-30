<?php
/*
File: registerFormFunctions.php
Description: Contains all functions for the registration page
Author: John Tortorich/Kenneth Cross
Date: Thursday April 28th, 2011
I hereby certify that this page is entirely my own work.
*/

/*
Function: displayDefault()
Description: Display unusable buttons on the page.
*/
function displayDefault(){
	if($_SESSION['advisor'])
	{
		echo "<center><h1>Edit Users</h1><hr>";
		echo "<table><tr align=\"center\">";
		echo "<td><form method=\"post\" style = \"display:inline;\" action=\"register.php?estu=1\"><button type='submit'>Add Student</button></form></td>";
		echo "<td><form method=\"post\" style = \"display:inline;\" action=\"register.php?eadv=1\"><button type='submit'>Add Advisor</button></form></td></tr>";
		echo "<tr align=\"center\"><td><form method=\"post\" style = \"display:inline;\" action=\"register.php?dstu=1\"><button type='submit'>Remove Student</button></form></td>";
		echo "<td><form method=\"post\" style = \"display:inline;\" action=\"register.php?dadv=1\"><button type='submit'>Remove Advisor</button></form></td>";
		echo "</tr></table></center>";
	}
	else
		echo "<font color='#FF0000'>You done goofed: Must Be Logged In As An Advisor</font><br>";
}

/*
Function: displayAddStudent()
Description: Display the upper division requirements for the logged in student , advisor's chosen student, 
or advisor's department
*/
function displayAddStudent(){
	if($_SESSION['advisor'])
	{
		echo "<center><h1>Add A Student</h1></center><hr>";
		echo "<form method=\"post\" action=\"register.php?stuaddquery=1\">";
		echo "<table><tr>";
		echo "<tr><td>Student CLID</td><td><input type=\"text\" name=\"nclid\" size=7></td></tr>";
		echo "<tr><td>Student Name</td><td><input type=\"text\" name=\"nname\"></td></tr>";
		echo "<tr><td>Password</td><td><input type=\"text\" name=\"npass\"></td></tr>";
		echo "<tr><td>Division<br>(0 for lower, 1 for upper)</td><td><input type=\"text\" name=\"nupper\" size=1></td></tr>";
		echo "<tr><td>ACT English Score</td><td><input type=\"text\" name=\"nenglish\" size=1></td></tr>";
		echo "<tr><td>ACT Math Score  </td><td> <input type=\"text\" name=\"nmath\" size=1></td></tr>";
		echo "<tr><td>ACT Reading Score</td><td><input type=\"text\" name=\"nreading\" size=1></td></tr>";
		echo "<tr><td>ACT Science Score</td><td><input type=\"text\" name=\"nscience\" size=1></td></tr>";
		echo "<tr><td><button type='submit'>Add to Database</button></form></td>";
		echo "<form method=\"post\" action=\"register.php\">
		<td><button type='submit'>Back</button></form></td></tr></table></form>";
	}
	else
		echo "<font color='#FF0000'>You done goofed: Must Be Logged In As An Advisor</font><br>";
}

function displayAddAdvisor(){
	if($_SESSION['advisor'])
	{
		echo "<center><h1>Add An Advisor</h1></center><hr>";
		echo "<form method=\"post\" action=\"register.php?advaddquery=1\">";
		echo "<table><tr>";
		echo "<tr><td>Advisor CLID</td><td><input type=\"text\" name=\"aclid\" size=7></td></tr>";
		echo "<tr><td>Advisor Name</td><td><input type=\"text\" name=\"aname\"></td></tr>";
		echo "<tr><td>Password</td><td><input type=\"text\" name=\"apass\"></td></tr>";
		echo "<tr><td><button type='submit'>Add to Database</button></form></td>";
		echo "<form method=\"post\" action=\"register.php\">
		<td><button type='submit'>Back</button></form></td></tr></table></form>";
	}
	else
		echo "<font color='#FF0000'>You done goofed: Must Be Logged In As An Advisor</font><br>";
}

function displayDropStudent(){
	if($_SESSION['advisor'])
	{
		echo "<center><h1>Remove A Student</h1></center><hr>";
		echo "*Students having classes/courses on an advising form, being advised, and/or working toward a degree cannot be dropped";
		//List Students except the (possible) advisee
		$studentQuery = mysql_query("SELECT clid, name from student order by clid;");
		echo "<table border = \"1\" align = \"center\"><tr align = \"center\"><td><b>DROP</b></td><td><b>CLID</b></td><td><b>NAME</b></td></tr>";
		while($student = mysql_fetch_array($studentQuery))
		{
			if($student['clid'] != $_SESSION['advisee_CLID'])
			{
				echo "<form method=\"post\" style = \"display:inline;\" action=\"register.php?studropquery=1&stuclid=".$student['clid']."&stuname=".$student['name']."\">";
				echo "<tr><td align = \"center\"><input type=\"submit\"  value=\"x\" /;></td><td>".$student['clid']."</td><td>".$student['name']."</td></tr>";
				echo "</form>";
			}
		}
		echo "</table>";
		echo "<table align = \"center\"><tr align = \"center\"><td><form style = \"display:inline;\" method=\"post\" action=\"register.php\">
		<button type='submit'>Back</button></form></td></tr></table></form>";
	}
	else
		echo "<font color='#FF0000'>You done goofed: Must Be Logged In As An Advisor</font><br>";
}

function displayDropAdvisor(){
	if($_SESSION['advisor'])
	{
		echo "<center><h1>Remove An Advisor</h1></center><hr>";
		echo "*Advisors mentoring students cannot be dropped";
		//List Advisors except the active one
		$advisorQuery = mysql_query("SELECT clid, name from advisor order by clid;");
		echo "<table border = \"1\" align = \"center\"><tr align = \"center\"><td><b>DROP</b></td><td><b>CLID</b></td><td><b>NAME</b></td></tr>";
		while($advisor = mysql_fetch_array($advisorQuery))
		{
			if($advisor['clid'] != $_SESSION['CLID'])
			{
				echo "<form method=\"post\" style = \"display:inline;\" action=\"register.php?advdropquery=1&advclid=".$advisor['clid']."&advname=".$advisor['name']."\">";
				echo "<tr><td align = \"center\"><input type=\"submit\"  value=\"x\" /;></td><td>".$advisor['clid']."</td><td>".$advisor['name']."</td></tr>";
				echo "</form>";
			}
		}
		echo "</table>";
		echo "<table align = \"center\"><tr align = \"center\"><td><form style = \"display:inline;\" method=\"post\" action=\"register.php\">
		<button type='submit'>Back</button></form></td></tr></table></form>";
	}
	else
		echo "<font color='#FF0000'>You done goofed: Must Be Logged In As An Advisor</font><br>";
}

function dropStudent($clid, $name){
	//Query to check if the advisor is in the database
	$studentQuery = mysql_query("SELECT clid, name from student where student.name = \"".$name."\" and student.clid = \"".$clid."\";");
	//Is the student in the database?
	if($student = mysql_fetch_array($studentQuery))
	{
		//If the student is in the database, delete the entry
		mysql_query("DELETE FROM student WHERE name='".$name."' and clid = '".$clid."';");
	}		
}

function dropAdvisor($clid, $name){
	//Query to check if the advisor is in the database
	$advisorQuery = mysql_query("SELECT clid, name from advisor where advisor.name = \"".$name."\" and advisor.clid = \"".$clid."\";");
	//Is the advisor in the database?
	if($advisor = mysql_fetch_array($advisorQuery))
	{
		//If the advisor is in the database, delete the entry
		mysql_query("DELETE FROM advisor WHERE name='".$name."' and clid = '".$clid."';");
	}		
}

function addStudent(){
	//Is the post data set?
	if($_POST['nclid'] and $_POST['nname'] and $_POST['npass']){
		//If it is, check to see if the student is already in the database
		$studentQuery = mysql_query("SELECT clid, name, password from student where student.name = \"".$_POST['nname']."\" and student.clid = \"".$_POST['nclid']."\" and student.password = \"".$_POST['npass']."\";");
		//Is the student already in the database?
		if(!mysql_fetch_array($studentQuery))
		{
			mysql_query("INSERT INTO student VALUES ('".mysql_real_escape_string(trim($_POST['nclid']))."', '".mysql_real_escape_string(trim($_POST['nname']))."', '".mysql_real_escape_string(trim($_POST['npass']))."', '".mysql_real_escape_string(trim($_POST['nupper']))."', '".mysql_real_escape_string(trim($_POST['nenglish']))."', '".mysql_real_escape_string(trim($_POST['nmath']))."', '".mysql_real_escape_string(trim($_POST['nreading']))."', '".mysql_real_escape_string(trim($_POST['nscience']))."');")  or die(mysql_error());
			unset($_POST['nclid']);
			unset($_POST['nname']);
			unset($_POST['npass']);
			unset($_POST['nupper']);
			unset($_POST['nenglish']);
			unset($_POST['nmath']);
			unset($_POST['nreading']);
			unset($_POST['nscience']);
		}		
	}
	else
		echo "<font color='#FF0000'>You done goofed: Must enter CLID, Name, and Password to add a student</font><br>";		
}

function addAdvisor(){
	//Is the post data set?
	if($_POST['aclid'] and $_POST['aname'] and $_POST['apass']){
		//If it is, check to see if the advisor is already in the database
		$advisorQuery = mysql_query("SELECT clid, name, password from advisor where advisor.name = \"".$_POST['aname']."\" and advisor.clid = \"".$_POST['aclid']."\" and advisor.password = \"".$_POST['apass']."\";");
		//Is the advisor already in the database?
		if(!mysql_fetch_array($advisorQuery))
		{
			//Find the active advisor's department in order to add the new advisor
			$advisorQuery = mysql_query("SELECT distinct department from advisor where advisor.clid = \"".$_SESSION['CLID']."\";");			
			$activeAdvisor = mysql_fetch_array($advisorQuery);
			mysql_query("INSERT INTO advisor VALUES ('".mysql_real_escape_string(trim($_POST['aclid']))."', '".mysql_real_escape_string(trim($_POST['aname']))."', '".mysql_real_escape_string(trim($_POST['apass']))."', '".mysql_real_escape_string(trim($activeAdvisor['department']))."');")  or die(mysql_error());
			unset($_POST['aclid']);
			unset($_POST['aname']);
			unset($_POST['apass']);
			unset($_POST['adept']);
		}		
	}
	else
		echo "<font color='#FF0000'>You done goofed: Must enter CLID, Name, and Password to add an advisor</font><br>";	
}
?>
