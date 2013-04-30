<?php
/*
File: profileEditClassesFunctions.php
Description: Functions which display the Upcoming Classes and handle the addition and removal of classes that the student is upcomingly enrolled in.
Author: Daniel Hefner
Certificate of Authenticity:
	I certify that the code in this file is entirely my own.

*/

/*
Function: displayEditUpcomingClassesForm($upcomingClasses)
Description: Displays the upcoming classes in an edittable form
*/
function displayEditUpcomingClassesForm($upcomingClasses)
{
	echo '<p><b style="font-size:25">Upcoming Classes </b>';
	
	// display the link to shrink/expand the upcoming classes (the text alternates between + and - on click)
	echo '<a href="javascript:show(\'editUpcomingClasses\')" onclick="this.innerHTML=(this.innerHTML==\'[+]\' ? \'[&ndash;]\':\'[+]\')">[&ndash;]</a></p>';

	echo '<div id="editUpcomingClasses" style="display:block">';
	
	// display the list of upcoming classes
	echo '<table width="100%"><tr>';
		echo '<th>Department</th>';
		echo '<th>Course</th>';
		echo '<th>Section</th>';
		echo '<th>Time</th>';
		echo '<th>Days</th>';
		echo '<th>Semester</th>';
		echo '<th>Year</th>';
	echo '</tr>';
	
	$sems = array('SP','SI','SU','FA','WI');
	while ($row = mysql_fetch_assoc($upcomingClasses))
	{
		extract($row);
		$meet = mysql_fetch_assoc(getMeetTimesAndDays($course_dept, $course_num, $section_num, $semester, $year));
		extract($meet);
		echo '<tr>';
			// each class has a form for removing the class from the list
			echo '<form name="removeUpcomingClass" action="profileEditClasses.php?edit=removeClass" method="post">';
			echo '<td style="text-align:center">'.$course_dept.'</td>';
			echo '<td style="text-align:center">'.$course_num.'</td>';
			echo '<td style="text-align:center">'.$section_num.'</td>';
			echo '<td style="text-align:center">'.$meet_time.' - '.$end_time.'</td>';
			echo '<td style="text-align:center">'.$days.'</td>';
			echo '<td style="text-align:center">'.$sems[$semester].'</td>';
			echo '<td style="text-align:center">'.$year.'</td>';
			// hidden inputs contain identifying information for the class to be removed
			echo '<input type="hidden" name="course_dept" value='.$course_dept.' />';
			echo '<input type="hidden" name="course_num" value='.$course_num.' />';
			echo '<input type="hidden" name="section_num" value='.$section_num.' />';
			echo '<input type="hidden" name="semester" value='.$semester.' />';
			echo '<input type="hidden" name="year" value='.$year.' />';
			echo '<td align="right"><input type="submit" value="Remove" /></td>';
			echo '</form>';
		echo '</tr>';
	}
	echo '</table>';
	echo '</div>';

}


/*
Function: displayEditCurrentClassesForm($currentClasses)
Description: Displays the current classes in an edittable form
*/
function displayEditCurrentClassesForm($currentClasses)
{
	$curMonth = date('m');
	if (1<=$curMonth && $curMonth<5) $curSem = 'SP';
	elseif ($curMonth==5) $curSem = 'SI';
	elseif (6<=$curMonth && $curMonth<8) $curSem = 'SU';
	elseif (8<=$curMonth && $curMonth<12) $curSem = 'FA';
	elseif ($curMonth==12) $curSem = 'WI';

	echo '<p><b style="font-size:25">Current Classes </b>';
	
	// display the link to shrink/expand the upcoming classes (the text alternates between + and - on click)
	echo '<a href="javascript:show(\'editCurrentClasses\')" onclick="this.innerHTML=(this.innerHTML==\'[+]\' ? \'[&ndash;]\':\'[+]\')">[&ndash;]</a></p>';

	echo '<div id="editCurrentClasses" style="display:block">';
	// display the list of current classes
	echo '<table width="100%"><tr>';
		echo '<th>Department</th>';
		echo '<th>Course</th>';
		echo '<th>Section</th>';
		echo '<th>Time</th>';
		echo '<th>Days</th>';
	echo '</tr>';

	while ($row = mysql_fetch_assoc($currentClasses))
	{
		extract($row);
		$meet = mysql_fetch_assoc(getMeetTimesAndDays($course_dept, $course_num, $section_num, $semester, $year));
		extract($meet);
		echo '<tr>';
			// each class has a form for removing the class from the list
			echo '<form name="removeCurrentClass" action="profileEditClasses.php?edit=removeClass" method="post">';
			echo '<td style="text-align:center">'.$course_dept.'</td>';
			echo '<td style="text-align:center">'.$course_num.'</td>';
			echo '<td style="text-align:center">'.$section_num.'</td>';
			echo '<td style="text-align:center">'.$meet_time.' - '.$end_time.'</td>';
			echo '<td style="text-align:center">'.$days.'</td>';
			// hidden inputs contain identifying information for the class to be removed
			echo '<input type="hidden" name="course_dept" value='.$course_dept.' />';
			echo '<input type="hidden" name="course_num" value='.$course_num.' />';
			echo '<input type="hidden" name="section_num" value='.$section_num.' />';
			echo '<input type="hidden" name="semester" value='.$curSem.' />';
			echo '<input type="hidden" name="year" value='.date('Y').' />';
			echo '<td align="right"><input type="submit" value="Remove" /></td>';
			echo '</form>';
		echo '</tr>';
	}
	echo '</table>';
	echo '</div>';

}

/*
Function: displayEditPastClassesForm($pastClasses)
Description: Displays the past classes of the student with clid==$clid in a form which
		allows the removal of listed past classes and addition of new ones, as well
		as changing the grades in the listed past classes.
*/
function displayEditPastClassesForm($pastClasses)
{
	echo '<p><b style="font-size:25">Past Classes </b>';
	
	// display the link to shrink/expand the past classes (the text alternates between + and - on click)
	echo '<a href="javascript:show(\'editPastClasses\')" onclick="this.innerHTML=(this.innerHTML==\'[+]\' ? \'[&ndash;]\':\'[+]\')">[&ndash;]</a></p>';

	echo '<div id="editPastClasses" style="display:block">';

	echo '<table width="100%"><tr>';
		echo '<th>Department</th>';
		echo '<th>Course</th>';
		echo '<th>Section</th>';
		echo '<th>Time</th>';
		echo '<th>Days</th>';
		echo '<th>Semester</th>';
		echo '<th>Year</th>';	
		echo '<th>Grade</th>';
	echo '</tr>';

	
	$sems = array('SP','SI','SU','FA','WI');	
	$possibleGrades = array('A','B','C','D','F','CR','NC','W','I','-');

	// displays each past class taken as a row in the table with the class dept, class num, section num, meet and end times,
	// meeting days, semester, year, and grade which is in a drop down (it is changeable)
	// also displays a remove button for each class
	while ($row = mysql_fetch_assoc($pastClasses))
	{
		extract($row);
		$meet = mysql_fetch_assoc(getMeetTimesAndDays($course_dept, $course_num, $section_num, $semester, $year));
		extract($meet);
		echo '<tr>';
			echo '<td style="text-align:center">'.$course_dept.'</td>';
			echo '<td style="text-align:center">'.$course_num.'</td>';
			echo '<td style="text-align:center">'.$section_num.'</td>';
			echo '<td style="text-align:center">'.$meet_time.' - '.$end_time.'</td>';
			echo '<td style="text-align:center">'.$days.'</td>';
			echo '<td style="text-align:center">'.$sems[$semester].'</td>';
			echo '<td style="text-align:center">'.$year.'</td>';
			echo '<td align="center">';
				// class grade cell
				echo '<form name="changeGrade" action="profileEditClasses.php?edit=changeGrade" method="post">';
				// hidden inputs contain the required information to the identify the class when a grade being changed
				echo '<input type="hidden" name="course_dept" value='.$course_dept.' />';
				echo '<input type="hidden" name="course_num" value='.$course_num.' />';
				echo '<input type="hidden" name="section_num" value='.$section_num.' />';
				echo '<input type="hidden" name="semester" value='.$semester.' />';
				echo '<input type="hidden" name="year" value='.$year.' />';
					
				// grade drop down menu
				echo '<select name="grade" onchange="this.form.submit();">';

				// display each possible grades as an option in the dropdown and selects the one currently recorded as the default
				foreach ($possibleGrades as $g) 
					echo '<option'.($grade==$g || (!$grade && $g=='-')?' selected="selected"':'').'>'.$g.'</option>';

				echo '</select>';
				echo '</form>';
			echo '</td>';

			// remove button
			echo '<td align="right">';
				echo '<form name="removePastClass" action="profileEditClasses.php?edit=removeClass" method="post">';
				// hidden inputs contain the identifying information for the class being removed
				echo '<input type="hidden" name="course_dept" value='.$course_dept.' />';
				echo '<input type="hidden" name="course_num" value='.$course_num.' />';
				echo '<input type="hidden" name="section_num" value='.$section_num.' />';
				echo '<input type="hidden" name="semester" value='.$semester.' />';
				echo '<input type="hidden" name="year" value='.$year.' />';
				echo '<input type="submit" value="Remove" />';
				echo '</form>';
			echo '</td>';
		echo '</tr>';
	}
	echo '</table>';
	echo '</div>';
}

/*
Function: displayEditPastClassesForm($pastClasses)
Description: Displays the special course credits of the student with clid==$clid in a form which
		allows the removal of listed special credits and addition of new ones, as well
		as changing the grades in the listed special credits.
*/
function displayEditSpecialCreditForm($specialCred)
{
	echo '<p><b style="font-size:25">Special Credits </b>';
	
	// display the link to shrink/expand the past classes (the text alternates between + and - on click)
	echo '<a href="javascript:show(\'editSpecialCredit\')" onclick="this.innerHTML=(this.innerHTML==\'[+]\' ? \'[&ndash;]\':\'[+]\')">[&ndash;]</a></p>';

	echo '<div id="editSpecialCredit" style="display:block">';

	
	displayAddSpecialCreditForm();

	echo '<table width="100%"><tr>';
		echo '<th>Department</th>';
		echo '<th>Course</th>';
		echo '<th>Grade</th>';
		echo '<th>Transfer</th>';
	echo '</tr>';

	// displays each special credit as a row in the table with the class dept, class num, grade which is in a drop down (it is changeable)
	// and whether or not the credit is a transferral in a dropdown to select yes or no
	// also displays a remove button for each credit
	while ($row = mysql_fetch_assoc($specialCred))
	{
		extract($row);
		echo '<tr>';
			echo '<td style="text-align:center">'.$course_dept.'</td>';
			echo '<td style="text-align:center">'.$course_num.'</td>';
			echo '<td align="center">';
				// credit grade cell
				echo '<form name="changeGrade" action="profileEditClasses.php?edit=changeSpecialCreditGrade" method="post">';
				// hidden inputs contain the required information to identify the class when a grade being changed
				echo '<input type="hidden" name="course_dept" value='.$course_dept.' />';
				echo '<input type="hidden" name="course_num" value='.$course_num.' />';
					
				// grade drop down menu
				echo '<select name="grade" onchange="this.form.submit();">';

				// display each possible grades as an option in the dropdown and selects the one currently recorded as the default
				echo '<option'.($grade=='CR'?' selected="selected"':'').'>CR</option>';
				echo '<option'.($grade=='NA'?' selected="selected"':'').'>NA</option>';
				echo '</select>';
				echo '</form>';
			echo '</td>';
			echo '<td align="center">';
				// credit transfer cell
				echo '<form name="changeTransfer" action="profileEditClasses.php?edit=changeSpecialCreditTransfer" method="post">';
				// hidden inputs contain the required information to identify the class when the credit transfer is being changed
				echo '<input type="hidden" name="course_dept" value='.$course_dept.' />';
				echo '<input type="hidden" name="course_num" value='.$course_num.' />';
				echo '<select name="transfer" onchange="this.form.submit();">';
				echo '<option'.($transfer ?' selected="selected"':'').'>Yes</option>';
				echo '<option'.($transfer=='0' ?' selected="selected"':'').'>No</option>';
				echo '<option'.(!$transfer && $transfer!='0' ? ' selected="selected"':'').'>-</option>';
				echo '</select>';
				echo '</form>';	
			echo '</td>';

			// remove button
			echo '<td align="right">';
				echo '<form name="removePastClass" action="profileEditClasses.php?edit=removeSpecialCredit" method="post">';
				// hidden inputs contain the identifying information for the class being removed
				echo '<input type="hidden" name="course_dept" value='.$course_dept.' />';
				echo '<input type="hidden" name="course_num" value='.$course_num.' />';
				echo '<input type="submit" value="Remove" />';
				echo '</form>';
			echo '</td>';
		echo '</tr>';
	}
	echo '</table>';
	echo '</div>';
}
/*
Function: displayAddClassesForm()
Description: Displays the form to add a new class
*/
function displayAddClassesForm()
{
	global $class_error;
	if ($class_error) echo '<center style="color:red">'.$class_error.'</center>';
// displays the form for adding past classes to the list which submits back to the edit classes page
	echo '<form name="addClass" action="profileEditClasses.php?edit=addClass" method="post">';
	echo '<table align="center"><tr>';
		echo '<th>Department</th>';
		echo '<th>Class</th>';
		echo '<th>Section</th>';
		echo '<th>Semester</th>';
		echo '<th>Year</th>';
	echo '</tr><tr>';
		echo '<td align="center"><input type="text" name="course_dept" size="8" /></td>';
		echo '<td align="center"><input type="text" name="course_num" size="6" /></td>';
		echo '<td align="center"><input type="text" name="section_num" size="6" /></td>';
		echo '<td align="center"><input type="text" name="semester" size="6" /></td>';
		echo '<td align="center"><input type="text" name="year" size="6" /></td>';
		echo '<td> <input type="submit" value="Add Class" /></td>';
	echo '</tr></table></form>';

}

/*
Function: displayAddSpecialCreditForm()
Description: Displays the form to add a new special course credit
*/
function displayAddSpecialCreditForm()
{
	global $spec_cred_error;
	if ($spec_cred_error) echo '<center style="color:red">'.$spec_cred_error.'</center>';
// displays the form for adding special credit to the list which submits back to the edit classes page
	echo '<form name="addClass" action="profileEditClasses.php?edit=addSpecialCredit" method="post">';
	echo '<table align="center"><tr>';
		echo '<th>Department</th>';
		echo '<th>Class</th>';
		echo '<th>Grade</th>';
	echo '</tr><tr>';
		echo '<td align="center"><input type="text" name="course_dept" size="8" /></td>';
		echo '<td align="center"><input type="text" name="course_num" size="6" /></td>';
		echo '<td align="center"><input type="text" name="grade" size="6" /></td>';
		echo '<td> <input type="submit" value="Add Special Credit" /></td>';
	echo '</tr></table></form>';

}

/*
Function: displayProfileEditClasses($clid)
Description: Gets and displays the upcoming classes in and edittable form for the student with clid==$clid 
*/
function displayProfileEditClasses($clid)
{
	echo '<h1 style="text-align:center">Student Profile</h1><h3 style="text-align:center">Edit Classes</h3><hr>';

	echo '<center><form action="profile.php"><input type="submit" value="Back to profile" /></form></center>';
	
	displayAddClassesForm();
	echo '<br>';
	$upcomingClasses = getUpcomingClasses($clid);
	displayEditUpcomingClassesForm($upcomingClasses);
	echo '<br>';
	$currentClasses = getCurrentClasses($clid);
	if ($_SESSION['advisor']==true) // user is an advisor
		displayEditCurrentClassesForm($currentClasses);
	else
		displayCurrentClasses($currentClasses);
	echo '<br>';
	$pastClasses = getPastClasses($clid);
	if ($_SESSION['advisor']==true) // user is an advisor
		displayEditPastClassesForm($pastClasses);
	else
		displayPastClasses($pastClasses);
	echo '<br>';
	$specialCred = getSpecialCredit($clid);
	if ($_SESSION['advisor']==true) // user is an advisor
		displayEditSpecialCreditForm($specialCred);
	else
		displaySpecialCredit($specialCred);
		
	
}


/*
Function: changeGrade($clid)
Description: Changes the grade for the specified class for the student with clid=$clid
*/
function changeGrade($clid)
{
	extract($_POST);
	mysql_query("UPDATE take SET grade=".($grade!='-'?"'$grade'":"\N")." WHERE clid='$clid' and course_dept='$course_dept'
			and course_num='$course_num' and section_num='$section_num'
			and semester='$semester' and year='$year';") or die(mysql_errno().': '.mysql_error());
}



/*
Function: removeClass($clid)
Description: Removes a class from the take table in the database for the student with clid==$clid
*/
function removeClass($clid)
{
	extract($_POST);
	
	mysql_query("DELETE FROM take WHERE clid='$clid' and course_dept='$course_dept' 
			and course_num='$course_num' and section_num='$section_num' 
			and semester='$semester' and year='$year';") or die(mysql_errno().': '.mysql_error());
}


/*
Function: addClass($clid)
Description: Adds a past class to the take table in the database for the student with clid==$clid
*/
function addClass($clid)
{
	extract($_POST);
	

	$sems = array('SP','SI','SU','FA','WI');
	$semNum = array_search(strtoupper($semester),$sems);

	if (!$section_num || ($semNum!=0 && $semNum==false) || !$year || !$course_num || !$course_dept) 
		return 'Invalid Input -- Try Again!';
	

	$curYear = date('Y');
    
	$curMonth = date('m');
	if (1<=$curMonth && $curMonth<5) $curSem = 0;
	elseif ($curMonth==5) $curSem = 1;
	elseif (6<=$curMonth && $curMonth<8) $curSem = 2;
	elseif (8<=$curMonth && $curMonth<12) $curSem = 3;
	elseif ($curMonth==12) $curSem = 4;
	
	if (!$_SESSION['advisor'] && ($year < $curYear || ($year == $curYear && $semNum <= $curSem)))
		return 'Student Cannot Add Current or Past Classes';

	$course_dept = strtoupper($course_dept);

	mysql_query("INSERT INTO take VALUES ('$section_num','$semNum','$year','$course_num','$course_dept','$clid',\N);");// or die(mysql_errno().': '.mysql_error());

	if (mysql_errno()==1452)  // set error message to be displayed when primary key data is not found in the data base
		return 'Invalid Input -- Try Again!';
	else
		return '';
}

/*
Function: removeSpecialCred($clid)
Description: Removes a special course credit from the spec_cred table in the database for the student with clid==$clid
*/
function removeSpecialCredit($clid)
{
	extract($_POST);
	
	mysql_query("DELETE FROM spec_cred WHERE clid='$clid' and course_dept='$course_dept' 
			and course_num='$course_num';") or die(mysql_errno().': '.mysql_error());
}


/*
Function: addSpecialCredit($clid)
Description: Adds a past class to the take table in the database for the student with clid==$clid
*/
function addSpecialCredit($clid)
{
	extract($_POST);

	if (!$course_num || !$course_dept) 
		return 'Invalid Input -- Try Again!';
	
	if (!$_SESSION['advisor'])
		return 'Student Cannot Add Special Credits';

	$grade = strtoupper($grade);
	if ($grade!='NA' && $grade!='CR')
		return 'Grade must be CR or NA';

	$course_dept = strtoupper($course_dept);
	mysql_query("INSERT INTO spec_cred VALUES ('$clid','$course_num','$course_dept','$grade',\N);");// or die(mysql_errno().': '.mysql_error());

	if (mysql_errno()==1452)  // set error message to be displayed when primary key data is not found in the data base
		return 'Invalid Input -- Try Again!';
	else
		return '';
}

/*
Function: changeSpecialCreditTransfer($clid)
Description: Changes the transfer value for the specified special credit for the student with clid=$clid
*/
function changeSpecialCreditTransfer($clid)
{
	extract($_POST);
	if ($transfer=='Yes')
		mysql_query("UPDATE spec_cred SET transfer='1' WHERE clid='$clid' and course_dept='$course_dept'
			and course_num='$course_num';") or die(mysql_errno().': '.mysql_error());
	elseif ($transfer=='No')
		mysql_query("UPDATE spec_cred SET transfer='0' WHERE clid='$clid' and course_dept='$course_dept'
			and course_num='$course_num';") or die(mysql_errno().': '.mysql_error());
	elseif ($transfer=='-')		
		mysql_query("UPDATE spec_cred SET transfer=\N WHERE clid='$clid' and course_dept='$course_dept'
			and course_num='$course_num';") or die(mysql_errno().': '.mysql_error());
}

/*
Function: changeSpecialCreditGrade($clid)
Description: Changes the grade for the specified special credit for the student with clid=$clid
*/
function changeSpecialCreditGrade($clid)
{
	extract($_POST);
	mysql_query("UPDATE spec_cred SET grade=".($grade!='-'?"'$grade'":"\N")." WHERE clid='$clid' and course_dept='$course_dept'
			and course_num='$course_num';") or die(mysql_errno().': '.mysql_error());
	
}


/*
Function: profileEditUpcomingClassesGenerator($clid)
Description: Edits the appropriate data if the edit flag is set and displays the edit upcoming classes page if the
		user is an advisor with an advisee
*/
function profileEditClassesGenerator(){
	include('profileFunctions.php');
	global $class_error, $spec_cred_error;

	if($_SESSION['advisor']==true){
		if(isset($_SESSION['advisee_CLID']))
		{
			if ($_GET['edit']=='addClass')
				$class_error = addClass($_SESSION['advisee_CLID']);
			if ($_GET['edit']=='removeClass')
				removeClass($_SESSION['advisee_CLID']);
			if ($_GET['edit']=='changeGrade')
				changeGrade($_SESSION['advisee_CLID']);
			if ($_GET['edit']=='addSpecialCredit')
				$spec_cred_error = addSpecialCredit($_SESSION['advisee_CLID']);
			if ($_GET['edit']=='removeSpecialCredit')
				removeSpecialCredit($_SESSION['advisee_CLID']);
			if ($_GET['edit']=='changeSpecialCreditGrade')
				changeSpecialCreditGrade($_SESSION['advisee_CLID']);
			if ($_GET['edit']=='changeSpecialCreditTransfer')
				changeSpecialCreditTransfer($_SESSION['advisee_CLID']);
			displayProfileEditClasses($_SESSION['advisee_CLID'], false);
		}
		else
			echo "<font color='#FF0000'>You done goofed: Student Not Selected</font>";
	}
	else{ // the user is a student
		//echo "<font color='#FF0000'>You done goofed: Not Logged in as Advisor</font>";
		if ($_GET['edit']=='addClass')
			$class_error = addClass($_SESSION['CLID']);
		if ($_GET['edit']=='removeClass')
			removeClass($_SESSION['CLID']);
		displayProfileEditClasses($_SESSION['CLID'], false);
	}
}

?>

<script language type="text/javascript">
/*
Function: show()
Description: Expand or shrink the element referenced by layer_ref (expands if the current state is "none", shrinks if the current state is "block")
*/
function show(layer_ref) {
if (document.all && document.all[layer_ref].style.display=='block') {
document.all[layer_ref].style.display = 'none'; // for IE
} else if(document.getElementById(layer_ref).style.display=='block'){
document.getElementById(layer_ref).style.display = 'none';
document.getElementById(layer_ref).value='[+]';
} else {
document.getElementById(layer_ref).style.display = 'block';
document.all[layer_ref].style.display = 'block'; // for IE
}
}  
</script>

