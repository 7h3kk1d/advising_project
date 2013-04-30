<?php
/*
File: profile_functions.php
Description: Displays the profile for the student with clid=$clid.  
         This includes all relevant student info and their current and past courses.
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
	else	  $query = "SELECT * FROM student where clid='$clid'";
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
		echo '<tr><td width="175"><b>GPA: </b></td><td>'.round(calculateGpa($clid),2).'</td></tr>';
		// only display those fields which have non null values
		if (isset($row['upper_division']))
			echo '<tr><td width="175"><b>Division: </b></td><td>'.($row['upper_division'] ? 'Upper':'Lower').'</td></tr>';
		else
			echo '<tr><td width="175"><b>Division: </b></td><td>N/A</td></tr>';
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
Function: getCurrentCourses($clid)
Description: Gets the current for the student with clid==$clid.  The current month is used for a rough estimate of the current semester.
*/
function getCurrentCourses($clid)
{

    $curYear = date('Y');
    
    $curMonth = date('m');
    if (1<=$curMonth && $curMonth<5) $curSem = 0;
    elseif ($curMonth==5) $curSem = 1;
    elseif (6<=$curMonth && $curMonth<8) $curSem = 2;
    elseif (8<=$curMonth && $curMonth<12) $curSem = 3;
    elseif ($curMonth==12) $curSem = 4;
    $query = "SELECT T.course_dept, T.course_num, T.section_num, C.days, C.meet_time, C.end_time
        FROM take T,class_meet_times C
        WHERE T.clid='$clid' and T.year='$curYear' and T.semester='$curSem'
            and T.year=C.year and T.semester=C.semester and T.course_dept=C.course_dept
            and T.course_num=C.course_num and T.section_num=C.section_num;";
    $result = mysql_query($query) or die(mysql_errno().': '.mysql_error());
    
    return $result;
}

/*
Function: displayCurrentCourses($currentCourses)
Description: Displays the current courses for the student with clid==$clid
*/
function displayCurrentCourses($currentCourses)
{

	echo '<table width="100%"><tr><td><b style="font-size:25">Current Courses </b>';

	// display the link to shrink/expand the current courses (the text alternates between + and - on click)
	echo '<a href="javascript:show(\'currentCourses\')" onclick="this.innerHTML=(this.innerHTML==\'[+]\' ? \'[&ndash;]\':\'[+]\')">[&ndash;]</a></td>';
	
	// display the edit current courses button if the user is an advisor
	//if ($_SESSION['advisor']==true)
		//echo '<td align="right"><form action="profileEditCurrentCourses.php"><input type="submit" value="Edit" /></form></td>';

	echo '</tr></table>';
	echo '<div id="currentCourses" style="display:block">';
	echo '<table width="100%"><tr>';
		echo '<th>Department</th>';
		echo '<th>Course</th>';
		echo '<th>Section</th>';
		echo '<th>Time</th>';
		echo '<th>Days</th>';
	echo '</tr>';
	while ($row = mysql_fetch_assoc($currentCourses))
	{
		echo '<tr>';
			echo '<td style="text-align:center">'.$row['course_dept'].'</td>';
			echo '<td style="text-align:center">'.$row['course_num'].'</td>';
			echo '<td style="text-align:center">'.$row['section_num'].'</td>';
			echo '<td style="text-align:center">'.$row['meet_time'].' - '.$row['end_time'].'</td>';
			echo '<td style="text-align:center">'.$row['days'].'</td>';
		echo '</tr>';
	}
    echo '</table></div>';
}

/*
Function: getPastCourses($clid)
Description: Gets the past courses for the student with clid==$clid.  The current month is used for a rough estimate of the current semester.
*/
function getPastCourses($clid)
{
	$curYear = date('Y');
    
	$curMonth = date('m');
	if (1<=$curMonth && $curMonth<5) $curSem = 0;
	elseif ($curMonth==5) $curSem = 1;
	elseif (6<=$curMonth && $curMonth<8) $curSem = 2;
	elseif (8<=$curMonth && $curMonth<12) $curSem = 3;
	elseif ($curMonth==12) $curSem = 4;
	$query = "SELECT T.course_dept, T.course_num, T.section_num, T.semester, T.year, T.grade, C.days, C.meet_time, C.end_time
		FROM take T,class_meet_times C
		WHERE T.clid='$clid' and (T.year<'$curYear' or (T.year='$curYear' and T.semester<'$curSem'))
		and T.year=C.year and T.semester=C.semester and T.course_dept=C.course_dept
		and T.course_num=C.course_num and T.section_num=C.section_num
		ORDER BY T.year desc, T.semester desc;";
	$result = mysql_query($query) or die(mysql_errno().': '.mysql_error());

	return $result;

}

/*
Function: displayPastCourses($pastCourses)
Description: Displays the past courses for the student with clid==$clid
*/
function displayPastCourses($pastCourses)
{
	echo '<table width="100%"><tr><td><b style="font-size:25">Past Courses </b>';

	
	// display the link to shrink/expand the past courses (the text alternates between + and - on click)
	echo '<a href="javascript:show(\'pastCourses\')" onclick="this.innerHTML=(this.innerHTML==\'[+]\' ? \'[&ndash;]\':\'[+]\')">[&ndash;]</a></td>';
	
	// display the edit past courses button if the user is an advisor
	/*if ($_SESSION['advisor']==true)
		echo '<td align="right"><form action="profileEditPastCourses.php"><input type="submit" value="Edit" /></form></td>';*/
	echo '</tr></table>';
	echo '<div id="pastCourses" style="display:block">';
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

	while ($row = mysql_fetch_assoc($pastCourses))
	{
		echo '<tr>';
			echo '<td style="text-align:center">'.$row['course_dept'].'</td>';
			echo '<td style="text-align:center">'.$row['course_num'].'</td>';
			echo '<td style="text-align:center">'.$row['section_num'].'</td>';
			echo '<td style="text-align:center">'.$row['meet_time'].' - '.$row['end_time'].'</td>';
			echo '<td style="text-align:center">'.$row['days'].'</td>';
			echo '<td style="text-align:center">'.$sems[$row['semester']].'</td>';
			echo '<td style="text-align:center">'.$row['year'].'</td>';
			echo '<td style="text-align:center">'.($row['grade'] ? $row['grade']:'-').'</td>';
		echo '</tr>';
	}
	echo '</table></div>';

}

/*
Function: getUpcomingCourses($clid)
Description: Gets the upcoming courses for the student with clid==$clid.  The current month is used for a rough estimate of the current semester.
*/
function getUpcomingCourses($clid)
{
	$curYear = date('Y');
    
	$curMonth = date('m');
	if (1<=$curMonth && $curMonth<5) $curSem = 0;
	elseif ($curMonth==5) $curSem = 1;
	elseif (6<=$curMonth && $curMonth<8) $curSem = 2;
	elseif (8<=$curMonth && $curMonth<12) $curSem = 3;
	elseif ($curMonth==12) $curSem = 4;
	$query = "SELECT T.course_dept, T.course_num, T.section_num, T.semester, T.year, C.days, C.meet_time, C.end_time
		FROM take T,class_meet_times C
		WHERE T.clid='$clid' and (T.year>'$curYear' or (T.year='$curYear' and T.semester>'$curSem'))
		and T.year=C.year and T.semester=C.semester and T.course_dept=C.course_dept
		and T.course_num=C.course_num and T.section_num=C.section_num
		ORDER BY T.year desc, T.semester desc;";
	$result = mysql_query($query) or die(mysql_errno().': '.mysql_error());

	return $result;

}

/*
Function: displayUpcomingCourses($upcomingCourses)
Description: Displays the upcoming courses for the student with clid==$clid
*/
function displayUpcomingCourses($upcomingCourses)
{
	echo '<table width="100%"><tr><td><b style="font-size:25">Upcoming Courses </b>';

	
	// display the link to shrink/expand the upcoming courses (the text alternates between + and - on click)
	echo '<a href="javascript:show(\'upcomingCourses\')" onclick="this.innerHTML=(this.innerHTML==\'[+]\' ? \'[&ndash;]\':\'[+]\')">[&ndash;]</a></td>';
	
	// display the edit upcoming courses button if the user is an advisor
	//if ($_SESSION['advisor']==true)
		//echo '<td align="right"><form action="profileEditUpcomingCourses.php"><input type="submit" value="Edit" /></form></td>';
	echo '</tr></table>';
	echo '<div id="upcomingCourses" style="display:block">';
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

	while ($row = mysql_fetch_assoc($upcomingCourses))
	{
		echo '<tr>';
			echo '<td style="text-align:center">'.$row['course_dept'].'</td>';
			echo '<td style="text-align:center">'.$row['course_num'].'</td>';
			echo '<td style="text-align:center">'.$row['section_num'].'</td>';
			echo '<td style="text-align:center">'.$row['meet_time'].' - '.$row['end_time'].'</td>';
			echo '<td style="text-align:center">'.$row['days'].'</td>';
			echo '<td style="text-align:center">'.$sems[$row['semester']].'</td>';
			echo '<td style="text-align:center">'.$row['year'].'</td>';
		echo '</tr>';
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
Function: removeCourse($clid)
Description: Removes a course from the take table in the database for the student with clid==$clid
*/
function removeCourse($clid, $coursedata)
{
	extract($coursedata);
	
	mysql_query("DELETE FROM take WHERE clid='$clid' and course_dept='$course_dept' 
			and course_num='$course_num' and section_num='$section_num' 
			and semester='$semester' and year='$year';") or die(mysql_errno().': '.mysql_error());
}


/*
Function: addCourse($clid)
Description: Adds a past course to the take table in the database for the student with clid==$clid
*/
function addCourse($clid, $coursedata)
{
	extract($coursedata);
	

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
		return 'Student Cannot Add Current or Past Courses';

	$course_dept = strtoupper($course_dept);

	mysql_query("INSERT INTO take VALUES ('$section_num','$semNum','$year','$course_num','$course_dept','$clid',\N);");// or die(mysql_errno().': '.mysql_error());

	if (mysql_errno()==1452)  // set error message to be displayed when primary key data is not found in the data base
		return 'Invalid Input -- Try Again!';
	else
		return '';
}

/*
Function: displayProfile($clid, $isAdvisor)
Description: Calls the functions to get and display the upcoming, current and past courses
*/
function displayAllCourses($clid)
{
	echo '<br>';
	echo '<center><form action="profileEditCourses.php"><input type="submit" value="Edit Courses" /></form></center>';
	$upcomingCourses = getUpcomingCourses($clid);
	displayUpcomingCourses($upcomingCourses);
	echo '<br>';
	$currentCourses = getCurrentCourses($clid);
	displayCurrentCourses($currentCourses);
	echo '<br>';
	$pastCourses = getPastCourses($clid);
	displayPastCourses($pastCourses);
}

/*
Function: displayProfile($clid, $isAdvisor)
Description: Calls the functions to get and display the info, upcoming, current and past courses (for the student profile), and advisees (for the advisor profile)
*/
function displayProfile($clid, $isAdvisor)
{
	
	echo '<h1 style="text-align:center">'.($isAdvisor ? 'Advisor ':'Student ').'Profile</h1><hr>';

	$info = getInfo($clid, $isAdvisor);

   	displayInfo($clid, $info, $isAdvisor);
    
	// display the advisees on the advisor profile and the current and past courses on the student profile
	if ($isAdvisor){
		$advisees = getAdvisees($clid);
		displayAdvisees($advisees);
	}
	else {
		displayAllCourses($clid);
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

