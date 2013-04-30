<?php
/*
File: profileEditCoursesFunctions.php
Description: Contains functions for displaying Profile Edit Courses page.
Author: Daniel Hefner
Certificate of Authenticity:

*/

$error_msg = 'Invalid input';

function getcurrentCourses($clid)
{
	$curYear = date('Y');
    
	$curMonth = date('m');
	if (1<=$curMonth && $curMonth<5) $curSem = 'SP';
	elseif ($curMonth==5) $curSem = 'SI';
	elseif (6<=$curMonth && $curMonth<8) $curSem = 'SU';
	elseif (8<=$curMonth && $curMonth<12) $curSem = 'FA';
	elseif ($curMonth==12) $curSem = 'WI';
	$query = "SELECT T.course_dept, T.course_num, T.section_num, C.days, C.meet_time, C.end_time
		FROM take T,class_meet_times C
		WHERE T.clid='$clid' and T.year='$curYear' and T.semester='$curSem'
		    and T.year=C.year and T.semester=C.semester and T.course_dept=C.course_dept
		    and T.course_num=C.course_num and T.section_num=C.section_num;";
	$result = mysql_query($query) or die(mysql_error());

	return $result;
}


function displayCurrentCoursesForm($currentCourses)
{
	global $current_err, $error_msg;
	$curMonth = date('m');
	if (1<=$curMonth && $curMonth<5) $curSem = 'SP';
	elseif ($curMonth==5) $curSem = 'SI';
	elseif (6<=$curMonth && $curMonth<8) $curSem = 'SU';
	elseif (8<=$curMonth && $curMonth<12) $curSem = 'FA';
	elseif ($curMonth==12) $curSem = 'WI';

	echo '<p><b style="font-size:25">Current Courses </b>';
	echo '<a href="javascript:show(\'current_courses\')" onclick="this.text=(this.text==\'[+]\' ? \'[&ndash;]\':\'[+]\')">[&ndash;]</a></p>';
	
	
	echo '<div id="current_courses" style="display:block">';

	if ($current_err) echo '<center style="color:red">'.$error_msg.'</center>';

	echo '<form name="addCurrentCourse" action="profileEditCourses.php?edit=addCourse&addtype=current" method="post">';
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
			echo '<form name="removeCurrentCourse" action="profileEditCourses.php?edit=removeCourse" method="post">';
			echo '<td style="text-align:center">'.$course_dept.'</td>';
			echo '<td style="text-align:center">'.$course_num.'</td>';
			echo '<td style="text-align:center">'.$section_num.'</td>';
			echo '<td style="text-align:center">'.$meet_time.' - '.$end_time.'</td>';
			echo '<td style="text-align:center">'.$days.'</td>';
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

function getOtherCourses($clid)
{
	$curYear = date('Y');
    
    $curMonth = date('m');
    if (1<=$curMonth && $curMonth<5) $curSem = 'SP';
    elseif ($curMonth==5) $curSem = 'SI';
    elseif (6<=$curMonth && $curMonth<8) $curSem = 'SU';
    elseif (8<=$curMonth && $curMonth<12) $curSem = 'FA';
    elseif ($curMonth==12) $curSem = 'WI';

	$query = "SELECT T.course_dept, T.course_num, T.section_num, T.semester, T.year, T.grade, C.days, C.meet_time, C.end_time
		  FROM take T, class_meet_times C
	  	  WHERE T.clid='$clid' and T.year=C.year and T.semester=C.semester and T.course_dept=C.course_dept
			and T.course_num=C.course_num and T.section_num=C.section_num and (T.year!='$curYear' or T.semester != '$curSem')
		  ORDER BY T.year;";
	$result = mysql_query($query) or die(mysql_error());
	
	return $result;	
}


function displayOtherCoursesForm($otherCourses)
{
	global $other_err, $error_msg;

	echo '<p><b style="font-size:25">Other Courses </b>';
	echo '<a href="javascript:show(\'other_courses\')" onclick="this.text=(this.text==\'[+]\' ? \'[&ndash;]\':\'[+]\')">[&ndash;]</a></p>';

	echo '<div id="other_courses" style="display:block">';

	if ($other_err) echo '<center style="color:red">'.$error_msg.'</center>';
	echo '<form name="addOtherCourse" action="profileEditCourses.php?edit=addCourse&addtype=other" method="post">';
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

	while ($row = mysql_fetch_assoc($otherCourses))
	{
		extract($row);
		echo '<tr>';
			echo '<td style="text-align:center">'.$course_dept.'</td>';
			echo '<td style="text-align:center">'.$course_num.'</td>';
			echo '<td style="text-align:center">'.$section_num.'</td>';
			echo '<td style="text-align:center">'.$meet_time.' - '.$end_time.'</td>';
			echo '<td style="text-align:center">'.$days.'</td>';
			echo '<td style="text-align:center">'.$semester.'</td>';
			echo '<td style="text-align:center">'.$year.'</td>';
			echo '<td align="center">';
				echo '<form name="changeGrade" action="profileEditCourses.php?edit=changeGrade" method="post">';
				echo '<input type="hidden" name="course_dept" value='.$course_dept.' />';
				echo '<input type="hidden" name="course_num" value='.$course_num.' />';
				echo '<input type="hidden" name="section_num" value='.$section_num.' />';
				echo '<input type="hidden" name="semester" value='.$semester.' />';
				echo '<input type="hidden" name="year" value='.$year.' />';
	
				$possibleGrades = array('A','B','C','D','F','CR','NC','W','I','N/A');
				echo '<select name="grade" onchange="this.form.submit();">';
				foreach ($possibleGrades as $g)
					echo '<option'.($grade==$g || (!$grade && $g=='N/A')?' selected="selected"':'').'>'.$g.'</option>';

				echo '</select>';
				echo '</form>';
			echo '</td>';

			echo '<td align="right">';
				echo '<form name="removeOtherCourse" action="profileEditCourses.php?edit=removeCourse" method="post">';
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

function displayProfileEditCourses($clid)
{
	echo '<h1 style="text-align:center">Profile</h1><h3 style="text-align:center">Edit Courses</h3><hr>';

	echo '<center><form action="profile.php"><input type="submit" value="Back to profile" /></form></center>';
	$currentCourses = getCurrentCourses($clid);
	displayCurrentCoursesForm($currentCourses);

	echo '<br>';
	$otherCourses = getOtherCourses($clid);
	displayOtherCoursesForm($otherCourses);

}


function addCourse($clid)
{
	global $current_err, $other_err, $error_msg;
	extract($_POST);
	if ($grade)
		mysql_query("INSERT INTO take VALUES ('$section_num','$semester','$year','$course_num','$course_dept','$clid','$grade');");// or die(mysql_errno().': '.mysql_error());
	else
		mysql_query("INSERT INTO take VALUES ('$section_num','$semester','$year','$course_num','$course_dept','$clid',\N);");// or die(mysql_errno().': '.mysql_error());

	if (mysql_errno()==1452)
		if ($_GET['addtype']=='current')
			$current_err = true;
		elseif ($_GET['addtype']=='other')
			$other_err = true;
	else
		$current_err = $other_err = false;
}

function removeCourse($clid)
{
	extract($_POST);
	mysql_query("DELETE FROM take WHERE clid='$clid' and course_dept='$course_dept' 
			and course_num='$course_num' and section_num='$section_num' 
			and semester='$semester' and year='$year';") or die(mysql_errno().': '.mysql_error());
}

function changeGrade($clid)
{
	extract($_POST);
	mysql_query("UPDATE take SET grade=".($grade!='N/A'?"'$grade'":"\N")." WHERE clid='$clid' and course_dept='$course_dept'
			and course_num='$course_num' and section_num='$section_num'
			and semester='$semester' and year='$year';") or die(mysql_errno().': '.mysql_error());
}

function profileGenerator(){
	if($_GET['advisor']==1)
		displayProfileEditCourses($_SESSION['CLID'], true);
	else{
		if($_SESSION['advisor']==true){
			if(isset($_SESSION['advisee_CLID']))
			{
				if ($_GET['edit']=='addCourse')
					addCourse($_SESSION['advisee_CLID']);
				if ($_GET['edit']=='removeCourse')
					removeCourse($_SESSION['advisee_CLID']);
				if ($_GET['edit']=='changeGrade')
					changeGrade($_SESSION['advisee_CLID']);
				displayProfileEditCourses($_SESSION['advisee_CLID'], false);
			}
			else
				echo "<font color='#FF0000'>You done goofed: Student Not Selected</font>";
		}
		else
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
} else {
document.getElementById(layer_ref).style.display = 'block';
document.all[layer_ref].style.display = 'block'; // for IE 
}
}  
</script>
