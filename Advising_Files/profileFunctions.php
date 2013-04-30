<?php
/*
File: profile_functions.php
Description: Displays the profile for the student with clid=$clid.  
         This includes all relevant student info and their current and past classes.
Author: Daniel Hefner
Certificate of Authenticity:

*/


/*
Function: getInfo($clid, $isAdvisor)
Description: Gets the info for the student (for the student profile) or advisor (for the advisor profile) with clid==$clid
*/
function getInfo($clid, $isAdvisor)
{
	if ($isAdvisor)   $query = "SELECT * FROM advisor where clid='$clid'";
	else	  
	{
		if (mysql_fetch_array(mysql_query("SELECT * FROM working_towards WHERE clid='$clid'")))
			$query = "SELECT * FROM student S, working_towards W WHERE S.clid='$clid' and S.clid=W.clid";
		else
			$query = "SELECT * FROM student S WHERE S.clid='$clid'";
	}
	$result = mysql_query($query) or die(mysql_error());

	return $result;
}

/*
Function: displayInfo($clid, $info, $isAdvisor)
Description: Displays the information for the student (for the student profile) or advisor (for the advisor profile) with clid==$clid
*/
function displayInfo($clid, $info, $isAdvisor)
{
	include('calculations.php');
	echo '<table width="100%"><tr><td><b style="font-size:25">Info</b></td>';

	// display the edit info button, which links to the edit info page
	// if the user is on the advisor profile, ensure that the isadvisor value is set for the edit info page
	echo '<td align="right"><form action="profileEditInfo.php">';
	if ($isAdvisor)
		echo '<input type="hidden" name="advisor" value="1" />';
	echo '<input type="submit" value="Edit" />';
	echo '</form></td>';


	echo '</tr></table>';
	$row = mysql_fetch_assoc($info);
   	echo '<table>';
	echo '<tr><td width="175"><b>CLID: </b></td><td>'.$clid.'</td></tr>';
	echo '<tr><td width="175"><b>Name: </b></td><td>'.$row['name'].'</td></tr>';
	
	// if the user is on the student profile display the gpa, division, and act scores 
	if (!$isAdvisor){
		if (isset($row['deg_name'])) 
			echo '<tr><td width="175"><b>Major: </b></td><td>'.$row['deg_name'].'</td></tr>';
		else
			echo '<tr><td width="175"><b>Major: </b></td><td>N/A</td></tr>';
		if (isset($row['plan_name']))
			echo '<tr><td width="175"><b>Concentration: </b></td><td>'.$row['plan_name'].'</td></td>';
		else
			echo '<tr><td width="175"><b>Concentration: </b></td><td>N/A</td></td>';
		if (isset($row['upper_division']))
			echo '<tr><td width="175"><b>Division: </b></td><td>'.($row['upper_division'] ? 'Upper':'Lower').'</td></tr>';
		else
			echo '<tr><td width="175"><b>Division: </b></td><td>N/A</td></tr>';
		echo '<tr><td width="175"><b>GPA: </b></td><td>'.round(calculateGpa($clid),2).'</td></tr>';
		// only display those fields which have non null values
		echo '<tr><td width="175"><b>ACT Composite: </b></td><td>'.calculateAct($clid).'</td></tr>';
		if (isset($row['act_english']))
			echo '<tr><td width="175"><b>ACT English: </b></td><td>'.$row['act_english'].'</td></tr>';
		else
			echo '<tr><td width="175"><b>ACT English: </b></td><td>N/A</td></tr>';
		if (isset($row['act_math']))
			echo '<tr><td width="175"><b>ACT Math: </b></td><td>'.$row['act_math'].'</td></tr>';
		else
			echo '<tr><td width="175"><b>ACT Math: </b></td><td>N/A</td></tr>';
		if (isset($row['act_reading']))
			echo '<tr><td width="175"><b>ACT Reading: </b></td><td>'.$row['act_reading'].'</td></tr>';
		else
			echo '<tr><td width="175"><b>ACT Reading: </b></td><td>N/A</td></tr>';
		if (isset($row['act_science']))
			echo '<tr><td width="175"><b>ACT Science: </b></td><td>'.$row['act_science'].'</td></tr>';
		else
			echo '<tr><td width="175"><b>ACT Science: </b></td><td>N/A</td></tr>';
	}
	echo '</table>';
    
}

/*
Function: getCurrentClasses($clid)
Description: Gets the current for the student with clid==$clid.  The current month is used for a rough estimate of the current semester.
*/
function getCurrentClasses($clid)
{

    $curYear = date('Y');
    
    $curMonth = date('m');
    if (1<=$curMonth && $curMonth<5) $curSem = 0;
    elseif ($curMonth==5) $curSem = 1;
    elseif (6<=$curMonth && $curMonth<8) $curSem = 2;
    elseif (8<=$curMonth && $curMonth<12) $curSem = 3;
    elseif ($curMonth==12) $curSem = 4;
    $query = "SELECT T.course_dept, T.course_num, T.section_num, T.semester, T.year
        FROM take T
        WHERE T.clid='$clid' and T.year='$curYear' and T.semester='$curSem';";
    $result = mysql_query($query) or die(mysql_errno().': '.mysql_error());
    
    return $result;
}

/*
Function: displayCurrentClasses($currentClasses)
Description: Displays the current classes for the student with clid==$clid
*/
function displayCurrentClasses($currentClasses)
{

	echo '<table width="100%"><tr><td><b style="font-size:25">Current Classes </b>';

	// display the link to shrink/expand the current classes (the text alternates between + and - on click)
	echo '<a href="javascript:show(\'currentClasses\')" onclick="this.innerHTML=(this.innerHTML==\'[+]\' ? \'[&ndash;]\':\'[+]\')">[&ndash;]</a></td>';


	echo '</tr></table>';
	echo '<div id="currentClasses" style="display:block">';
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
		echo '<tr>';
			echo '<td style="text-align:center">'.$row['course_dept'].'</td>';
			echo '<td style="text-align:center">'.$row['course_num'].'</td>';
			echo '<td style="text-align:center">'.$row['section_num'].'</td>';
			echo '<td style="text-align:center">'.$meet['meet_time'].' - '.$meet['end_time'].'</td>';
			echo '<td style="text-align:center">'.$meet['days'].'</td>';
		echo '</tr>';
	}
    echo '</table></div>';
}

/*
Function: getPastClasses($clid)
Description: Gets the past classes for the student with clid==$clid.  The current month is used for a rough estimate of the current semester.
*/
function getPastClasses($clid)
{
	$curYear = date('Y');
    
	$curMonth = date('m');
	if (1<=$curMonth && $curMonth<5) $curSem = 0;
	elseif ($curMonth==5) $curSem = 1;
	elseif (6<=$curMonth && $curMonth<8) $curSem = 2;
	elseif (8<=$curMonth && $curMonth<12) $curSem = 3;
	elseif ($curMonth==12) $curSem = 4;
	$query = "SELECT T.course_dept, T.course_num, T.section_num, T.semester, T.year, T.grade
		FROM take T
		WHERE T.clid='$clid' and (T.year<'$curYear' or (T.year='$curYear' and T.semester<'$curSem'))
		ORDER BY T.year desc, T.semester desc;";
	$result = mysql_query($query) or die(mysql_errno().': '.mysql_error());

	return $result;

}

/*
Function: displayPastClasses($pastClasses)
Description: Displays the past classes for the student with clid==$clid
*/
function displayPastClasses($pastClasses)
{
	echo '<table width="100%"><tr><td><b style="font-size:25">Past Classes </b>';

	
	// display the link to shrink/expand the past classes (the text alternates between + and - on click)
	echo '<a href="javascript:show(\'pastClasses\')" onclick="this.innerHTML=(this.innerHTML==\'[+]\' ? \'[&ndash;]\':\'[+]\')">[&ndash;]</a></td>';
	
	echo '</tr></table>';
	echo '<div id="pastClasses" style="display:block">';
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

	while ($row = mysql_fetch_assoc($pastClasses))
	{
		extract($row);
		$meet = mysql_fetch_assoc(getMeetTimesAndDays($course_dept, $course_num, $section_num, $semester, $year));
		echo '<tr>';
			echo '<td style="text-align:center">'.$row['course_dept'].'</td>';
			echo '<td style="text-align:center">'.$row['course_num'].'</td>';
			echo '<td style="text-align:center">'.$row['section_num'].'</td>';
			echo '<td style="text-align:center">'.$meet['meet_time'].' - '.$meet['end_time'].'</td>';
			echo '<td style="text-align:center">'.$meet['days'].'</td>';
			echo '<td style="text-align:center">'.$sems[$row['semester']].'</td>';
			echo '<td style="text-align:center">'.$row['year'].'</td>';
			echo '<td style="text-align:center">'.($row['grade'] ? $row['grade']:'-').'</td>';
		echo '</tr>';
	}
	echo '</table></div>';

}

/*
Function: getUpcomingClasses($clid)
Description: Gets the upcoming classes for the student with clid==$clid.  The current month is used for a rough estimate of the current semester.
*/
function getUpcomingClasses($clid)
{
	$curYear = date('Y');
    
	$curMonth = date('m');
	if (1<=$curMonth && $curMonth<5) $curSem = 0;
	elseif ($curMonth==5) $curSem = 1;
	elseif (6<=$curMonth && $curMonth<8) $curSem = 2;
	elseif (8<=$curMonth && $curMonth<12) $curSem = 3;
	elseif ($curMonth==12) $curSem = 4;
	$query = "SELECT T.course_dept, T.course_num, T.section_num, T.semester, T.year
		FROM take T
		WHERE T.clid='$clid' and (T.year>'$curYear' or (T.year='$curYear' and T.semester>'$curSem'))
		ORDER BY T.year desc, T.semester desc;";
	$result = mysql_query($query) or die(mysql_errno().': '.mysql_error());

	return $result;

}

/*
Function: getMeetTimesAndDays($course_dept, $course_num, $section_num, $semester, $year)
Description: Gets the meet time and days for the specified class
*/
function getMeetTimesAndDays($course_dept, $course_num, $section_num, $semester, $year)
{
	$query="SELECT meet_time, end_time, days FROM class_meet_times WHERE course_dept='$course_dept' and course_num='$course_num' and section_num='$section_num' and semester='$semester' and year='$year'";
	$result = mysql_query($query) or die(mysql_errno().': '.mysql_error());
	return $result;

}
/*
Function: displayUpcomingClasses($upcomingClasses)
Description: Displays the upcoming classes for the student with clid==$clid
*/
function displayUpcomingClasses($upcomingClasses)
{
	echo '<table width="100%"><tr><td><b style="font-size:25">Upcoming Classes </b>';

	// display the link to shrink/expand the upcoming classes (the text alternates between + and - on click)
	echo '<a href="javascript:show(\'upcomingClasses\')" onclick="this.innerHTML=(this.innerHTML==\'[+]\' ? \'[&ndash;]\':\'[+]\')">[&ndash;]</a></td>';
	
	echo '</tr></table>';
	echo '<div id="upcomingClasses" style="display:block">';
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
		echo '<tr>';
			echo '<td style="text-align:center">'.$course_dept.'</td>';
			echo '<td style="text-align:center">'.$course_num.'</td>';
			echo '<td style="text-align:center">'.$section_num.'</td>';
			$meet = mysql_fetch_assoc(getMeetTimesAndDays($course_dept, $course_num, $section_num, $semester, $year));
			echo '<td style="text-align:center">'.$meet['meet_time'].' - '.$meet['end_time'].'</td>';
			echo '<td style="text-align:center">'.$meet['days'].'</td>';
			echo '<td style="text-align:center">'.$sems[$semester].'</td>';
			echo '<td style="text-align:center">'.$year.'</td>';
		echo '</tr>';
	}
	echo '</table></div>';

}

/*
Function: getSpecialCredit($clid)
Description: Gets the special credit courses for the advisor with clid==$clid
*/
function getSpecialCredit($clid)
{
	$query="SELECT * FROM spec_cred where clid='$clid';";
	$result = mysql_query($query) or die(mysql_error());
	return $result;
}

/*
Function: displaySpecialCredit($specialCred)
Description: Displays the special course credits received by student with clid=$clid
*/
function displaySpecialCredit($specialCred)
{

	echo '<table width="100%"><tr><td><b style="font-size:25">Special Credits </b>';

	// display the link to shrink/expand the upcoming classes (the text alternates between + and - on click)
	echo '<a href="javascript:show(\'specialCredit\')" onclick="this.innerHTML=(this.innerHTML==\'[+]\' ? \'[&ndash;]\':\'[+]\')">[&ndash;]</a></td>';
	
	echo '</tr></table>';
	echo '<div id="specialCredit" style="display:block">';
	echo '<table width="100%"><tr>';
		echo '<th>Department</th>';
		echo '<th>Course</th>';
		echo '<th>Grade</th>';
		echo '<th>Transfer</th>';
	echo '</tr>';

	while ($row = mysql_fetch_assoc($specialCred))
	{
		echo '<tr>';
			echo '<td style="text-align:center">'.$row['course_dept'].'</td>';
			echo '<td style="text-align:center">'.$row['course_num'].'</td>';
			echo '<td style="text-align:center">'.$row['grade'].'</td>';
			if ($row['transfer']) echo '<td style="text-align:center">Yes</td>';
			elseif (!$row['transfer'] && $row['transfer']!='0') echo '<td style="text-align:center">-</td>';
			elseif ($row['transfer']=='0') echo '<td style="text-align:center">No</td>';
	}
	echo '</table></div>';

}


/*
Function: getAdvisees($clid)
Description: Gets the advisees for the advisor with clid==$clid
*/
function getAdvisees($clid)
{
	$query="SELECT S.clid, S.name FROM advised_by A, student S WHERE A.advisor_clid='$clid' and A.student_clid=S.clid;";
	$result = mysql_query($query) or die(mysql_error());
	return $result;
}

/*
Function: displayAdvisees($advisees)
Description: Displays the advisees for the current advisor
*/
function displayAdvisees($advisees)
{
	echo '<table width="100%"><tr><td><b style="font-size:25">Advisees </b>';
	
	// display the link to shrink/expand the advisees (the text alternates between + and - on click)
	echo '<a href="javascript:show(\'advisees\')" onclick="this.innerHTML=(this.innerHTML==\'[+]\' ? \'[&ndash;]\':\'[+]\')">[&ndash;]</a></td>';

	echo '<td align="right"><form action="profileEditAdvisees.php"><input type="submit" value="Edit" /></form></td>';
	echo '</tr></table>';
	echo '<div id="advisees" style="display:block"><table><tr>';
	echo '<th width="100">CLID</th><th >Name</th>';
	while ($row = mysql_fetch_assoc($advisees))
		echo '<tr><td style="text-align:center">'.$row['clid'].'</td><td style="text-align:left">'.$row['name'].'</td></tr>';
	echo '</table>';

}

/*
Function: displayProfile($clid, $isAdvisor)
Description: Calls the functions to get and display the upcoming, current and past classes
*/
function displayAllClasses($clid)
{
	echo '<br>';
	echo '<center><form action="profileEditClasses.php"><input type="submit" value="Edit Classes" /></form></center>';
	$upcomingClasses = getUpcomingClasses($clid);
	displayUpcomingClasses($upcomingClasses);
	echo '<br>';
	$currentClasses = getCurrentClasses($clid);
	displayCurrentClasses($currentClasses);
	echo '<br>';
	$pastClasses = getPastClasses($clid);
	displayPastClasses($pastClasses);
	echo '<br>';
	$specialCred = getSpecialCredit($clid);
	displaySpecialCredit($specialCred);

}

/*
Function: displayProfile($clid, $isAdvisor)
Description: Calls the functions to get and display the info, upcoming, current and past classes (for the student profile), and advisees (for the advisor profile)
*/
function displayProfile($clid, $isAdvisor)
{
	
	echo '<h1 style="text-align:center">'.($isAdvisor ? 'Advisor ':'Student ').'Profile</h1><hr>';

	$info = getInfo($clid, $isAdvisor);

   	displayInfo($clid, $info, $isAdvisor);
    
	// display the advisees on the advisor profile and the current and past classes on the student profile
	if ($isAdvisor){
		$advisees = getAdvisees($clid);
		displayAdvisees($advisees);
	}
	else {
		displayAllClasses($clid);
	}
}


/*
Function: profileGenerator()
Description: Decides whether to display advisor profile or the student profile and whether to use the user's clid or the user's advisee's clid for the student profile.
*/
function profileGenerator(){
	if($_GET['advisor']==1){ // looking at the advisor profile
		displayProfile($_SESSION['CLID'], true);
	}
	else{ // looking at the student profile
		if($_SESSION['advisor']==true){ // the user is an advisor
			if(isset($_SESSION['advisee_CLID'])) // the user has an advisee
				displayProfile($_SESSION['advisee_CLID'], false);
			else
				echo "<font color='#FF0000'>You done goofed: Student Not Selected</font>";
		}
		else // the user is a student
			displayProfile($_SESSION['CLID'], false);
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

