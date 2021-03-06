<?php
/*
File: profileEditInfoFunctions.php
Description: Functions for displaying the Info of the currently logged in student or advisor and handles the editting of the user's info.
Author: Daniel Hefner
Certificate of Authenticity:
	I certify that the code in this file is entirely my own.

*/

$div_err;
$act_eng_err;
$act_math_err;
$act_read_err;
$act_sci_err;


/*
Function: displayEditInfoForm($clid, $info, $isAdvisor)
Description: Displays the info for the user in an editable form.
*/
function displayEditInfoForm($clid, $info, $isAdvisor)
{
	global $div_err, $act_eng_err, $act_math_err, $act_read_err, $act_sci_err;

   	include('calculations.php');

    	$row = mysql_fetch_assoc($info);

	echo '<p><b style="font-size:25">Info</b></p>';
	
	// display the form for editting the user info which submits back to the edit info page
	// if the user is editting the advisor profile, make sure the advisor variable stays set to true 
	if ($isAdvisor)
		echo '<form name="editInfo" action="profileEditInfo.php?advisor=1&edit=info" method="post">';
	else
		echo '<form name="editInfo" action="profileEditInfo.php?edit=info" method="post">';

	echo '<table>';
	echo '<tr>';
		echo '<td width="175"><b>CLID: </b></td>';
		echo '<td>'.$clid.'</td>';
	echo '</tr><tr>';
		echo '<td width="175"><b>Name: </b></td>';
		echo '<td><input type="text" name="name" value="'.$row['name'].'" size="12"/></td>';
	echo '</tr>';
	echo '<tr>';
		echo '<td width="175"><b>Password: </b></td>';
		echo '<td><input type="password" name="password" value="'.$row['password'].'" size="12"/></td>';
	echo '</tr>';

	// if the user is editting the student profile display the gpa, division, and act scores
	// if any of the data is null, display N/A in that field
	// if there was an input error in a previous attempt at editting the info, display the error by the appropriate fields
	if (!$isAdvisor){
		echo '<tr>';
			echo '<td width="175"><b>GPA: </b></td>';
			echo '<td>'.round(calculateGpa($clid),2).'</td>';
		echo '</tr><tr>';
			echo '<td width="175"><b>Division: </b></td>';
			if (isset($row['upper_division']))
				echo '<td><input type="text" name="division" value="'.($row['upper_division'] ? 'Upper':'Lower').'" size="4" /></td>';
			else
				echo '<td><input type="text" name="division" value="N/A" size="4" /></td>';
			if ($div_err) echo '<td style="color:red">Enter Upper, Lower, or N/A</td>';
		echo '</tr><tr>';
			echo '<td width="175"><b>ACT Composite: </b></td>';
			echo '<td>'.calculateAct($clid).'</td>';
		echo '</tr><tr>';
			echo '<td width="175"><b>ACT English: </b></td>';
			if (isset($row['act_english']))
				echo '<td><input type="text" name="act_english" value="'.$row['act_english'].'" size="4" /></td>';
			else	
				echo '<td><input type="text" name="act_english" value="N/A" size="4" /></td>';	
			if ($act_eng_err) echo '<td style="color:red">Enter a score between 0 and 36 or N/A</td>';
		echo '</tr><tr>';
			echo '<td width="175"><b>ACT Math: </b></td>';
			if (isset($row['act_math']))
				echo '<td><input type="text" name="act_math" value="'.$row['act_math'].'" size="4" /></td>';
			else
				echo '<td><input type="text" name="act_math" value="N/A" size="4" /></td>';
			if ($act_math_err) echo '<td style="color:red">Enter a score between 0 and 36 or N/A</td>';
		echo '</tr><tr>';
			echo '<td width="175"><b>ACT Reading: </b></td>';
			if (isset($row['act_reading']))
				echo '<td><input type="text" name="act_reading" value="'.$row['act_reading'].'" size="4" /></td>';
			else
				echo '<td><input type="text" name="act_reading" value="N/A" size="4" /></td>';
				
			if ($act_read_err) echo '<td style="color:red">Enter a score between 0 and 36 or N/A</td>';
		echo '</tr><tr>';
			echo '<td width="175"><b>ACT Science: </b></td>';			
			if (isset($row['act_science']))
				echo '<td><input type="text" name="act_science" value="'.$row['act_science'].'" size="4" /></td>';
			else
				echo '<td><input type="text" name="act_science" value="N/A" size="4" /></td>';
				
			if ($act_sci_err) echo '<td style="color:red">Enter a score between 0 and 36 or N/A</td>';
		echo '</tr>';
	}

	echo '<tr>';
		echo '<td/><td align="center"><input type="submit" value="Change" /></td>';
	echo '</tr>';
	echo '</table>';
	echo '</form>';
    
}


/*
Function: displayProfileEditInfo($clid, $isAdvisor)
Description: Gets and displays the user's info in edittable form
*/
function displayProfileEditInfo($clid, $isAdvisor)
{
	echo '<h1 style="text-align:center">'.($isAdvisor?'Advisor ':'Student ').'Profile</h1><h3 style="text-align:center">Edit Info</h3><hr>';

	// if the user is on the advisor profile ensure that the back to profile button points to the advisor profile
	if ($isAdvisor){
		echo '<center><form action="profile.php">';
		echo '<input type="hidden" name="advisor" value="1"><input type="submit" value="Back to profile" />';
		echo '</form></center>';
	}
	else
		echo '<center><form action="profile.php?"><input type="submit" value="Back to profile" /></form></center>';

	$info = getInfo($clid, $isAdvisor);
	displayEditInfoForm($clid, $info, $isAdvisor);
}

/*
Function: editInfo($clid, $isAdvisor)
Description: Modifies the user's information in the database.
*/
function editInfo($clid, $isAdvisor)
{
	global $div_err, $act_eng_err, $act_math_err, $act_read_err, $act_sci_err;

	extract($_POST);

	// Update the values of name, division, act_english, act_math, act_reading, and act_science
	if ($name){
		// make sure all special characters in the string are properly escaped
		$name = mysql_real_escape_string($name);
		if ($isAdvisor) mysql_query("UPDATE advisor SET name='$name' WHERE clid='$clid';");
		else mysql_query("UPDATE student SET name='$name' WHERE clid='$clid';");
	}
		
	if ($password){
		// make sure all special characters are properly escaped
		$name = mysql_real_escape_string($password);
		if ($isAdvisor) mysql_query("UPDATE advisor SET password='$password' WHERE clid='$clid';");
		else mysql_query("UPDATE student SET password='$password' WHERE clid='$clid';");
	}


	if ($division){
		$division=strtolower($division);
		if ($division=='upper' || $division=='lower'){
			$division = ($division=='upper' ? 1:0);'<td style="color:red">Enter a value between 0 and 36</td>';
			mysql_query("UPDATE student SET upper_division='$division' WHERE clid='$clid';");
			$div_err = false;
		}
		else 
			// if the division is non null and not some form of 'upper' or 'lower' set div_err to true
			$div_err = true;
	}
	else{
		// if division is null set the field in the database to null
		mysql_query("UPDATE student SET upper_division=\N WHERE clid='$clid';");
		$div_err = false;
	}
	if ($act_english){
		if (0<=$act_english && $act_english<=36){
			mysql_query("UPDATE student SET act_english='$act_english' WHERE clid='$clid';");
			$act_eng_err = false;
		}
		else
			// if the english score is non null and not in 0-36 set act_eng_err to true
			$act_eng_err = true;
	}
	else{
		// if act_english is null set the field in the database to null
		mysql_query("UPDATE student SET act_english=\N WHERE clid='$clid';");
		$act_eng_err = false;
	}
	if ($act_math){
		if (0<=$act_math && $act_math<=36){
			mysql_query("UPDATE student SET act_math='$act_math' WHERE clid='$clid';");
			$act_math_err = false;
		}
		else
			// if the math score is non null and not in 0-36 set act_math_err to true
			$act_math_err = true;
	}
	else{
		// if act_math is null set the field in the database to null
		mysql_query("UPDATE student SET act_math=\N WHERE clid='$clid';");
		$act_math_err = false;
	}
	if ($act_reading){
		if (0<=$act_reading && $act_reading<=36){
			mysql_query("UPDATE student SET act_reading='$act_reading' WHERE clid='$clid';");
			$act_read_err = false;
		}
		else
			// if the reading score is non null and not in 0-36 set act_read_err to true
			$act_read_err = true;
	}
	else{
		// if act_reading is null set the field in the database to null
		mysql_query("UPDATE student SET act_reading=\N WHERE clid='$clid';");
		$act_read_err = false;
	}
	if ($act_science){
		if (0<=$act_science && $act_science<=36){
			mysql_query("UPDATE student SET act_science='$act_science' WHERE clid='$clid';");
			$act_sci_err = false;
		}
		else
			// if the science score is non null and not in 0-36 set act_sci_err to true
			$act_sci_err = true;
	}
	else{
		// if act_science is null set the field in the database to null
		mysql_query("UPDATE student SET act_science=\N WHERE clid='$clid';");
		$act_sci_err = false;
	}

	// if there are no errors in the input, redirect to the profile page
	if (!$div_err && !$act_eng_err && !$act_math_err && !$act_read_err && !$act_sci_err){
		header('Location:profile.php'.($isAdvisor?'?advisor=1':''));
		exit;
	}
	
}

/*
Function: profileGenerator()
Description: Edits the appropriate data if the edit flag is set and displays either the student or advisor profile edit info page
*/
function profileEditInfoGenerator(){
	include('profileFunctions.php');
	if($_GET['advisor']==1){ // the user is on the advisor profile
		if ($_GET['edit']=='info')
			editInfo($_SESSION['CLID'], true);
		displayProfileEditInfo($_SESSION['CLID'], true);
	}
	else{ // the user is on the student profile
		if($_SESSION['advisor']==true){ // the user is an advisor
			if(isset($_SESSION['advisee_CLID'])){ // the user has an advisee selected
				if ($_GET['edit']=='info')
					editInfo($_SESSION['advisee_CLID'], false);
				displayProfileEditInfo($_SESSION['advisee_CLID'], false);
			}
			else
				echo "<font color='#FF0000'>You done goofed: Student Not Selected</font>";
		}
		else // the user is a student
			echo "<font color='#FF0000'>You done goofed: Not Logged in as Advisor</font>";
	}
}
?>
