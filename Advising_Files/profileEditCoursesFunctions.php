<?php
/*
File: profileEditUpcomingCoursesFunctions.php
Description: Functions which display the Upcoming Courses and handle the addition and removal of courses that the student is upcomingly enrolled in.
Author: Daniel Hefner
Certificate of Authenticity:
	I certify that the code in this file is entirely my own.

*/

/*
Function: displayEditUpcomingCoursesForm($upcomingCourses)
Description: Displays the upcoming courses in an edittable form
*/
function displayEditUpcomingCoursesForm($upcomingCourses)
{
	echo '<p><b style="font-size:25">Upcoming Courses </b>';
	
	// display the link to shrink/expand the upcoming courses (the text alternates between + and - on click)
	echo '<a href="javascript:show(\'editUpcomingCourses\')" onclick="this.innerHTML=(this.innerHTML==\'[+]\' ? \'[&ndash;]\':\'[+]\')">[&ndash;]</a></p>';

	echo '<div id="editUpcomingCourses" style="display:block">';
	
	// display the list of upcoming courses
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
		extract($row);
		echo '<tr>';
			// each course has a form for removing the course from the list
			echo '<form name="removeUpcomingCourse" action="profileEditCourses.php?edit=removeCourse" method="post">';
			echo '<td style="text-align:center">'.$course_dept.'</td>';
			echo '<td style="text-align:center">'.$course_num.'</td>';
			echo '<td style="text-align:center">'.$section_num.'</td>';
			echo '<td style="text-align:center">'.$meet_time.' - '.$end_time.'</td>';
			echo '<td style="text-align:center">'.$days.'</td>';
			echo '<td style="text-align:center">'.$sems[$semester].'</td>';
			echo '<td style="text-align:center">'.$year.'</td>';
			// hidden inputs contain identifying information for the course to be removed
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
Function: displayEditCurrentCoursesForm($currentCourses)
Description: Displays the current courses in an edittable form
*/
function displayEditCurrentCoursesForm($currentCourses)
{
	$curMonth = date('m');
	if (1<=$curMonth && $curMonth<5) $curSem = 'SP';
	elseif ($curMonth==5) $curSem = 'SI';
	elseif (6<=$curMonth && $curMonth<8) $curSem = 'SU';
	elseif (8<=$curMonth && $curMonth<12) $curSem = 'FA';
	elseif ($curMonth==12) $curSem = 'WI';

	echo '<p><b style="font-size:25">Current Courses </b>';
	
	// display the link to shrink/expand the upcoming courses (the text alternates between + and - on click)
	echo '<a href="javascript:show(\'editCurrentCourses\')" onclick="this.innerHTML=(this.innerHTML==\'[+]\' ? \'[&ndash;]\':\'[+]\')">[&ndash;]</a></p>';

	echo '<div id="editCurrentCourses" style="display:block">';
	// display the list of current courses
	echo '<table width="100%"><tr>';
		echo '<th>Department</th>';
		echo '<th>Course</th>';
		echo '<th>Section</th>';
		echo '<th>Time</th>';
		echo '<th>Days</th>';
	echo '</tr>';

	while ($row = mysql_fetch_assoc($currentCourses))
	{
		extract($row);
		echo '<tr>';
			// each course has a form for removing the course from the list
			echo '<form name="removeCurrentCourse" action="profileEditCourses.php?edit=removeCourse" method="post">';
			echo '<td style="text-align:center">'.$course_dept.'</td>';
			echo '<td style="text-align:center">'.$course_num.'</td>';
			echo '<td style="text-align:center">'.$section_num.'</td>';
			echo '<td style="text-align:center">'.$meet_time.' - '.$end_time.'</td>';
			echo '<td style="text-align:center">'.$days.'</td>';
			// hidden inputs contain identifying information for the course to be removed
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
Function: displayEditPastCoursesForm($pastCourses)
Description: Displays the past courses of the student with clid==$clid in a form which
		allows the removal of listed past courses and addition of new ones, as well
		as changing the grades in the listed past courses.
*/
function displayEditPastCoursesForm($pastCourses)
{
	echo '<p><b style="font-size:25">Past Courses </b>';
	
	// display the link to shrink/expand the past courses (the text alternates between + and - on click)
	echo '<a href="javascript:show(\'editPastCourses\')" onclick="this.innerHTML=(this.innerHTML==\'[+]\' ? \'[&ndash;]\':\'[+]\')">[&ndash;]</a></p>';

	echo '<div id="editPastCourses" style="display:block">';

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

	// displays each past course taken as a row in the table with the course dept, course num, section num, meet and end times,
	// meeting days, semester, year, and grade which is in a drop down (it is changeable)
	// also displays a remove button for each course
	while ($row = mysql_fetch_assoc($pastCourses))
	{
		extract($row);
		echo '<tr>';
			echo '<td style="text-align:center">'.$course_dept.'</td>';
			echo '<td style="text-align:center">'.$course_num.'</td>';
			echo '<td style="text-align:center">'.$section_num.'</td>';
			echo '<td style="text-align:center">'.$meet_time.' - '.$end_time.'</td>';
			echo '<td style="text-align:center">'.$days.'</td>';
			echo '<td style="text-align:center">'.$sems[$semester].'</td>';
			echo '<td style="text-align:center">'.$year.'</td>';
			echo '<td align="center">';
				// course grade cell
				echo '<form name="changeGrade" action="profileEditCourses.php?edit=changeGrade" method="post">';
				// hidden inputs contain the required information to the identify the course when a grade being changed
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
				echo '<form name="removePastCourse" action="profileEditCourses.php?edit=removeCourse" method="post">';
				// hidden inputs contain the identifying information for the course being removed
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
Function: displayAddCoursesForm()
Description: Displays the form to add a new course
*/
function displayAddCoursesForm()
{
	global $error;
	if ($error) echo '<center style="color:red">'.$error.'</center>';
// displays the form for adding past courses to the list which submits back to the edit past courses page
	echo '<form name="addCourse" action="profileEditCourses.php?edit=addCourse" method="post">';
	echo '<table align="center"><tr>';
		echo '<th>Department</th>';
		echo '<th>Course</th>';
		echo '<th>Section</th>';
		echo '<th>Semester</th>';
		echo '<th>Year</th>';
	echo '</tr><tr>';
		echo '<td align="center"><input type="text" name="course_dept" size="8" /></td>';
		echo '<td align="center"><input type="text" name="course_num" size="6" /></td>';
		echo '<td align="center"><input type="text" name="section_num" size="6" /></td>';
		echo '<td align="center"><input type="text" name="semester" size="6" /></td>';
		echo '<td align="center"><input type="text" name="year" size="6" /></td>';
		echo '<td> <input type="submit" value="Add Course" /></td>';
	echo '</tr></table></form>';

}
/*
Function: displayProfileEditUpcomingCourses($clid)
Description: Gets and displays the upcoming courses in and edittable form for the student with clid==$clid 
*/
function displayProfileEditCourses($clid)
{
	echo '<h1 style="text-align:center">Student Profile</h1><h3 style="text-align:center">Edit Courses</h3><hr>';

	echo '<center><form action="profile.php"><input type="submit" value="Back to profile" /></form></center>';
	
	displayAddCoursesForm();
	echo '<br>';
	$upcomingCourses = getUpcomingCourses($clid);
	displayEditUpcomingCoursesForm($upcomingCourses);
	echo '<br>';
	$currentCourses = getCurrentCourses($clid);
	if ($_SESSION['advisor']==true) // user is an advisor
		displayEditCurrentCoursesForm($currentCourses);
	else
		displayCurrentCourses($currentCourses);
	echo '<br>';
	$pastCourses = getPastCourses($clid);
	if ($_SESSION['advisor']==true) // user is an advisor
		displayEditPastCoursesForm($pastCourses);
	else
		displayPastCourses($pastCourses);
	
}


/*
Function: changeGrade($clid)
Description: Changes the grade for the specified course for the student with clid=$clid
*/
function changeGrade($clid)
{
	extract($_POST);
	mysql_query("UPDATE take SET grade=".($grade!='-'?"'$grade'":"\N")." WHERE clid='$clid' and course_dept='$course_dept'
			and course_num='$course_num' and section_num='$section_num'
			and semester='$semester' and year='$year';") or die(mysql_errno().': '.mysql_error());
}

/*
Function: profileEditUpcomingCoursesGenerator($clid)
Description: Edits the appropriate data if the edit flag is set and displays the edit upcoming courses page if the
		user is an advisor with an advisee
*/
function profileEditCoursesGenerator(){
	include('profileFunctions.php');
	global $error;

	if($_SESSION['advisor']==true){
		if(isset($_SESSION['advisee_CLID']))
		{
			if ($_GET['edit']=='addCourse')
				$error = addCourse($_SESSION['advisee_CLID'], $_POST);
			if ($_GET['edit']=='removeCourse')
				removeCourse($_SESSION['advisee_CLID'], $_POST);
			if ($_GET['edit']=='changeGrade')
				changeGrade($_SESSION['advisee_CLID']);
			displayProfileEditCourses($_SESSION['advisee_CLID'], false);
		}
		else
			echo "<font color='#FF0000'>You done goofed: Student Not Selected</font>";
	}
	else{ // the user is a student
		//echo "<font color='#FF0000'>You done goofed: Not Logged in as Advisor</font>";
		if ($_GET['edit']=='addCourse')
			$error = addCourse($_SESSION['CLID'], $_POST);
		if ($_GET['edit']=='removeCourse')
			removeCourse($_SESSION['CLID'], $_POST);
		displayProfileEditCourses($_SESSION['CLID'], false);
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

