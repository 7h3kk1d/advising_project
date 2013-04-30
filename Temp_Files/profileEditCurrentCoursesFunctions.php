<?php
/*
File: profileEditCurrentCoursesFunctions.php
Description: Functions which display the Current Courses and handle the addition and removal of courses that the student is currently enrolled in.
Author: Daniel Hefner
Certificate of Authenticity:
	I certify that the code in this file is entirely my own.

*/

/*
Function: displayEditCurrentCoursesForm($currentCourses)
Description: Displays the current courses in an edittable form
*/
function displayEditCurrentCoursesForm($currentCourses)
{
	global $error;
	$curMonth = date('m');
	if (1<=$curMonth && $curMonth<5) $curSem = 0;
	elseif ($curMonth==5) $curSem = 1;
	elseif (6<=$curMonth && $curMonth<8) $curSem = 2;
	elseif (8<=$curMonth && $curMonth<12) $curSem = 3;
	elseif ($curMonth==12) $curSem = 4;

	echo '<p><b style="font-size:25">Current Courses</b></p>';
	
	// if there was an input error on a previous edit attempt output Invalid Input
	if ($error) echo '<center style="color:red">'.$error.'</center>';

	// display the for to add a current course which submits back to the edit current courses page
	echo '<form name="addCurrentCourse" action="profileEditCurrentCourses.php?edit=addCourse" method="post">';
	echo '<table align="center" ><tr>';
		echo '<th>Department</th>';
		echo '<th>Course</th>';
		echo '<th>Section</th>';
	echo '</tr><tr>';
		echo '<td align="center"><input type="text" name="course_dept" size="8" /></td>';
		echo '<td align="center"><input type="text" name="course_num" size="6" /></td>';
		echo '<td align="center"><input type="text" name="section_num" size="6" /></td>';
		echo '<input type="hidden" name="semester" value='.$curSem.' />';
		echo '<input type="hidden" name="year" value='.date('Y').' />';
		echo '<td> <input type="submit" value="Add" /></td>';
	echo '</tr></table>';
	echo '</form>';

	
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
			echo '<form name="removeCurrentCourse" action="profileEditCurrentCourses.php?edit=removeCourse" method="post">';
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

}

/*
Function: displayProfileEditCurrentCourses($clid)
Description: Gets and displays the current courses in and edittable form for the student with clid==$clid 
*/
function displayProfileEditCurrentCourses($clid)
{
	echo '<h1 style="text-align:center">Student Profile</h1><h3 style="text-align:center">Edit Current Courses</h3><hr>';

	echo '<center><form action="profile.php"><input type="submit" value="Back to profile" /></form></center>';
	
	$currentCourses = getCurrentCourses($clid);
	
	displayEditCurrentCoursesForm($currentCourses);
}

/*
Function: profileEditCurrentCoursesGenerator()
Description: Edits the appropriate data if the edit flag is set and displays the edit current courses page if the
		user is an advisor with an advisee
*/
function profileEditCurrentCoursesGenerator(){
	include('profileFunctions.php');
	global $error;

	if($_SESSION['advisor']==true){ // the user is an advisor
		if(isset($_SESSION['advisee_CLID'])) // the user has an advisee
		{
			if ($_GET['edit']=='addCourse')
				$error = addCourse($_SESSION['advisee_CLID'], $_POST);
			if ($_GET['edit']=='removeCourse')
				removeCourse($_SESSION['advisee_CLID'], $_POST);
			displayProfileEditCurrentCourses($_SESSION['advisee_CLID'], false);
		}
		else
			echo "<font color='#FF0000'>You done goofed: Student Not Selected</font>";
	}
	else // the user is a student
		echo "<font color='#FF0000'>You done goofed: Not Logged in as Advisor</font>";

}

?>
