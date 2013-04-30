<?php
/*
File: profileEditPastCoursesFunctions.php
Description: Functions for displaying the courses a student has taken in the past with the grades they've received and handling the addition or removal of past courses.
Author: Daniel Hefner
Certificate of Authenticity:
	I certify that the code in this file is entirely my own.

*/

/*
Function: displayEditPastCoursesForm($pastCourses)
Description: Displays the past courses of the student with clid==$clid in a form which
		allows the removal of listed past courses and addition of new ones, as well
		as changing the grades in the listed past courses.
*/
function displayEditPastCoursesForm($pastCourses)
{
	global $error;

	echo '<p><b style="font-size:25">Past Courses</b></p>';

	// displays "Invalid Input" if the user entered invalid input in a previous attempt to add courses
	if ($error) echo '<center style="color:red">'.$error.'</center>';

	// displays the form for adding past courses to the list which submits back to the edit past courses page
	echo '<form name="addPastCourse" action="profileEditPastCourses.php?edit=addCourse" method="post">';
	echo '<table align="center"><tr>';
		echo '<th>Department</th>';
		echo '<th>Course</th>';
		echo '<th>Section</th>';
		echo '<th>Semester</th>';
		echo '<th>Year</th>';
		echo '<th>Grade</th>';
	echo '</tr><tr>';
		echo '<td align="center"><input type="text" name="course_dept" size="8" /></td>';
		echo '<td align="center"><input type="text" name="course_num" size="6" /></td>';
		echo '<td align="center"><input type="text" name="section_num" size="6" /></td>';
		echo '<td align="center"><input type="text" name="semester" size="6" /></td>';
		echo '<td align="center"><input type="text" name="year" size="6" /></td>';
		echo '<td align="center"><input type="text" name="grade" size="6" /></td>';
		echo '<td> <input type="submit" value="Add" /></td>';
	echo '</tr></table></form>';



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
				echo '<form name="changeGrade" action="profileEditPastCourses.php?edit=changeGrade" method="post">';
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
				echo '<form name="removePastCourse" action="profileEditPastCourses.php?edit=removeCourse" method="post">';
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
}

/*
Function: displayProfileEditPastCourses($clid)
Description: Displays the page heading and calls the functions to get and the displays the past courses in an editable form.
*/
function displayProfileEditPastCourses($clid)
{
	echo '<h1 style="text-align:center">Student Profile</h1><h3 style="text-align:center">Edit Past Courses</h3><hr>';

	echo '<center><form action="profile.php"><input type="submit" value="Back to profile" /></form></center>';

	$pastCourses = getPastCourses($clid);
	displayEditPastCoursesForm($pastCourses);

}


/*
Function: displayPastCoursesForm($clid)
Description: Displays the past courses of the student with clid==$clid in a form which
		allows the removal of listed past courses and addition of new ones, as well
		as changing the grades in the listed past courses.
*/
function changeGrade($clid)
{
	extract($_POST);
	mysql_query("UPDATE take SET grade=".($grade!='-'?"'$grade'":"\N")." WHERE clid='$clid' and course_dept='$course_dept'
			and course_num='$course_num' and section_num='$section_num'
			and semester='$semester' and year='$year';") or die(mysql_errno().': '.mysql_error());
}


/*
Function: profileEditPastCoursesGenerator()
Description: Edits the appropriate data if the edit flag is set and displays the edit past courses page if the
		user is an advisor with an advisee
*/
function profileEditPastCoursesGenerator(){
	include ('profileFunctions.php');

	global $error;
	if($_SESSION['advisor']==true){ // the user is an advisor
		if(isset($_SESSION['advisee_CLID'])) // the user has selected an advisee 
		{
			if ($_GET['edit']=='addCourse')
				$error = addCourse($_SESSION['advisee_CLID'], $_POST);
			if ($_GET['edit']=='removeCourse')
				removeCourse($_SESSION['advisee_CLID'], $_POST);
			if ($_GET['edit']=='changeGrade')
				changeGrade($_SESSION['advisee_CLID']);
			displayProfileEditPastCourses($_SESSION['advisee_CLID'], false);
		}
		else
			echo "<font color='#FF0000'>You done goofed: Student Not Selected</font>";
	}
	else	// the user is a student
		echo "<font color='#FF0000'>You done goofed: Not Logged in as Advisor</font>";
}


?>
