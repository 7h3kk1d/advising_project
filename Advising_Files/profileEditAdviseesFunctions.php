<?php
/*
File: profileEditAdviseesFunctions.php
Description: Functions which display the Edit Advisees page of the Advisor Profile and process adding advisees for the logged in advisor.
Author: Daniel Hefner
Certificate of Authenticity:
	I certify that the code in this file is entirely my own.

*/

/*
Function: displayEditAdviseesCoursesForm($advisorCLID, $advisees)
Description: Displays the advisees of advisor with clid==$advisorCLID in a form which
		allows the removal of listed advisees and addition of new ones
*/
function displayEditAdviseesForm($advisorCLID, $advisees)
{		
	global $error;
	echo '<p><b style="font-size:25">Advisees </b>';

	echo '<form name="addAdvisee" action="profileEditAdvisees.php?edit=addAdvisee" method="post"><table>';
		echo '<td align="center"><input type="text" name="student_clid" size="7"/></td>';
		echo '<td/><td align="right"><input type="submit" value="Add CLID" /></td>';
		if ($error) echo '<td style="color:red">'.$error.'</td>';
	echo '</table></form>';

	echo '<div id="advisees" style="display:block"><table><tr>';
	echo '<th width="100">CLID</th><th style="padding-right:10px">Name</th></tr>';
	while ($row = mysql_fetch_assoc($advisees))
	{
		echo '<tr><td style="text-align:center">'.$row['clid'].'</td><td style="text-align:left;padding-right:10px">'.$row['name'].'</td>';
		echo '<td align="right">';
		echo '<form name="removeAdvisee" action="profileEditAdvisees.php?edit=removeAdvisee" method="post">';
			echo '<input type="hidden" name="student_clid" value='.$row['clid'].' />';
			echo '<input type="submit" value="Remove" />';
		echo '</form></td></tr>';
	}
	
	echo '</table></div>';

}

/*
Function: displayProfileEditAdvisees($clid)
Description: Displays the advisees of advisor with clid==$advisorCLID in a form which
		allows the removal of listed advisees and addition of new ones
*/
function displayProfileEditAdvisees($clid)
{
	echo '<h1 style="text-align:center">Advisor Profile</h1><h3 style="text-align:center">Edit Advisees</h3><hr>';

	echo '<center><form action="profile.php">';
	echo '<input type="hidden" name="advisor" value="1"><input type="submit" value="Back to profile" />';
	echo '</form></center>';
	$advisees = getAdvisees($clid);
	displayEditAdviseesForm($clid, $advisees);
}

/*
Function: addAdvisee($advisorCLID)
Description: Adds a new advisee to the advisor with clid==$advisorCLID
*/
function addAdvisee($advisorCLID)
{
	global $error;
	$studentCLID = $_POST['student_clid'];
	mysql_query("INSERT INTO advised_by VALUES ('$advisorCLID','$studentCLID');");// or die(mysql_errno().': '.mysql_error());
	switch (mysql_errno())
	{
		// set appropriate error output for mysql errors
		case 1452: $error='Student CLID does not exist'; break;
		case 1062: $error='Duplicate student CLID'; break;
		default: $error=''; break;
	}
}

/*
Function: removeAdvisee($advisorCLID)
Description: Removes a current advisee from the list of advisees for advisor with clid==$advisorCLID
*/
function removeAdvisee($advisorCLID)
{
	$studentCLID = $_POST['student_clid'];
	mysql_query("DELETE FROM advised_by WHERE advisor_clid='$advisorCLID' and student_clid='$studentCLID';") or die(mysql_error());
}


/*
Function: profileEditAdviseesGenerator()
Description: Edits the appropriate data if the edit flag is set and displays the edit advisees page
*/
function profileEditAdviseesGenerator(){
	include('profileFunctions.php');
	if ($_GET['edit']=='addAdvisee')
		addAdvisee($_SESSION['CLID']);
	if ($_GET['edit']=='removeAdvisee')
		removeAdvisee($_SESSION['CLID']);
	
	if($_SESSION['advisor']==true) // the user is an advisor
		displayProfileEditAdvisees($_SESSION['CLID']);
	else // the user is a student
		echo "<font color='#FF0000'>You done goofed: Not Logged in as Advisor</font>";
}
?>
