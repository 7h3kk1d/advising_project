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
	global $error;

	echo '<p><b style="font-size:25">Upcoming Courses</b></p>';
	
	// if there was an input error on a previous edit attempt output Invalid Input
	if ($error) echo '<center style="color:red">'.$error.'</center>';

	// display the for to add a upcoming course which submits back to the edit upcoming courses page
	echo '<form name="addUpcomingCourse" action="profileEditUpcomingCourses.php?edit=addCourse" method="post">';
	echo '<table align="center" ><tr>';
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
		echo '<td> <input type="submit" value="Add" /></td>';
	echo '</tr></table>';
	echo '</form>';

	
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
			echo '<form name="removeUpcomingCourse" action="profileEditUpcomingCourses.php?edit=removeCourse" method="post">';
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

}

/*
Function: displayProfileEditUpcomingCourses($clid)
Description: Gets and displays the upcoming courses in and edittable form for the student with clid==$clid 
*/
function displayProfileEditUpcomingCourses($clid)
{
	echo '<h1 style="text-align:center">Student Profile</h1><h3 style="text-align:center">Edit Upcoming Courses</h3><hr>';

	echo '<center><form action="profile.php"><input type="submit" value="Back to profile" /></form></center>';
	
	$upcomingCourses = getUpcomingCourses($clid);
	
	displayEditUpcomingCoursesForm($upcomingCourses);
}

/*
Function: profileEditUpcomingCoursesGenerator($clid)
Description: Edits the appropriate data if the edit flag is set and displays the edit upcoming courses page if the
		user is an advisor with an advisee
*/
function profileEditUpcomingCoursesGenerator(){
	include('profileFunctions.php');
	global $error;

	if($_SESSION['advisor']==true){
		if(isset($_SESSION['advisee_CLID']))
		{
			if ($_GET['edit']=='addCourse')
				$error = addCourse($_SESSION['advisee_CLID'], $_POST);
			if ($_GET['edit']=='removeCourse')
				removeCourse($_SESSION['advisee_CLID'], $_POST);
			displayProfileEditUpcomingCourses($_SESSION['advisee_CLID'], false);
		}
		else
			echo "<font color='#FF0000'>You done goofed: Student Not Selected</font>";
	}
	else
		echo "<font color='#FF0000'>You done goofed: Not Logged in as Advisor</font>";
}

?>
