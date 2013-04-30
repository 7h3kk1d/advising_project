<?php
/*
File: upperdivisionFunctions.php
Description: Constains all functions to show and edit the upper division requirements page.
Author: Jeffery Wichers/Kenneth Cross
Date: Thursday April 21st, 2011
We hereby certify that this code is our own work.
*/

/*
Function: displayUpperDivision()
Description: Display the upper division requirements for the logged in student , advisor's chosen student, 
or advisor's department
*/
function displayUpperDivision()
{
	echo "<center><h1>Upper Division Requirements</h1></center><hr>";

	displayMajors();
}

/*
Function: displayMajors()
Description: Display the majors of the student or that the advisor is in charge of.
*/
function displayMajors()
{
	if(!$_SESSION['advisor'])
	{
		$clid = $_SESSION['CLID'];
		$MajorNameQuery = mysql_query("SELECT distinct DE.full_dept_name FROM working_towards W,degree_plan D,department DE WHERE W.clid = '$clid' and W.deg_name = D.deg_name and D.deg_name = DE.dept_name order by DE.full_dept_name;") or die(mysql_error());
	}
	if(isset($_SESSION['advisee_CLID']))
	{
		$clid = $_SESSION['advisee_CLID'];
		$MajorNameQuery = mysql_query("SELECT distinct DE.full_dept_name FROM working_towards W,degree_plan D,department DE WHERE W.clid = '$clid' and W.deg_name = D.deg_name and D.deg_name = DE.dept_name 				order by DE.full_dept_name;") or die(mysql_error());
	}
	if($_SESSION['advisor'])
	{
		$clid = $_SESSION['CLID'];
		$MajorNameQuery = mysql_query("SELECT distinct D.full_dept_name FROM advisor A,department D WHERE A.clid = '$clid' and A.department = D.dept_name order by D.full_dept_name;") or die(mysql_error());

		$DepartmentQuery = mysql_query("SELECT distinct D.full_dept_name FROM advisor A,department D WHERE A.clid = '$clid' and A.department = D.dept_name order by D.full_dept_name;") or die(mysql_error());
		$department = mysql_fetch_array($DepartmentQuery);
		displayEditRequirements($department['full_dept_name']);
	}
	if(isset($MajorNameQuery))
	{
		$l = 0;
		$MajorNames = array();
		while($tempMajors = mysql_fetch_array($MajorNameQuery))
		{
			$MajorNames[$l] = $tempMajors['full_dept_name'];
			echo "<font size = \"+1\"><b><a href='javascript:show(\"$l\")'>"."[+] "."$MajorNames[$l]</a></b></font><br>";
			displayRequirements($MajorNames[$l], $l);

			$l = $l + 1;
		}
	}
}

/*
Function: displayRequirements()
Description: Display the hours and courses needed to become upperdivision for the major, and hilight those that have been completed by the student.
*/
function displayRequirements($Major,$l)
{
	echo "<div id='$l' style='display:none;'><ul>";
	$HoursQuery = mysql_query("SELECT D.up_div_hours FROM department D WHERE D.full_dept_name = '$Major';") or die(mysql_error());
	$hours = mysql_fetch_array($HoursQuery);
	echo "Required Hours: $hours[0]<br><br>";
	$CoursesQuery = mysql_query("SELECT C.course_dept,C.course_num,C.title,U.grade FROM up_div_req U,department D, course C WHERE D.full_dept_name = '$Major' and U.deg_name = D.dept_name and U.course_num = C.course_num and 			U.course_dept = C.course_dept ORDER BY C.course_dept,C.course_num,C.title;") or die(mysql_error());
	$m = 0;
	echo "<table width = \"60%\" border = \"1\">
	      <tr align = \"center\">";
	if($_SESSION['advisor'])
		echo "<td width = \"7%\"><font size = \"-2\">Drop Requirement</font></td>";
	echo "<td width = \"30%\"><font size = \"-2\">Course</font></td>
	      <td width = \"55%\"><font size = \"-2\">Course Name</font></td>
	      <td width = \"8%\"><font size = \"-2\"> Required Grade</font></td>
	      </tr>";
	$courseInformation = array();
	while($tempCourses = mysql_fetch_array($CoursesQuery))
	{
		$courseInformation[$m][0] = $tempCourses['course_dept'];
		$courseInformation[$m][1] = $tempCourses['course_num'];
		$courseInformation[$m][2] = $tempCourses['title'];
		if(isset($tempCourses['grade'])) $courseInformation[$m][3] = $tempCourses['grade'];
		else $coursesInformation[$m][3] = 'NA';
		if($_SESSION['advisor'] and !isset($_SESSION['advisee_CLID']))
		{
			echo "<form action=\"upperDivision.php?drop_req_dept=".$courseInformation[$m][0]."&drop_req_num=".$courseInformation[$m][1]."\" method=\"post\" style = \"display:inline;\">";
			echo "<tr>";
			if($_SESSION['advisor']==true)
				echo "<td align = \"center\" width = \"7%\"><input type=\"submit\"  value=\"x\" /;></td>";
			echo "<td width = \"30%\"><a href='course.php?NUM=".$courseInformation[$m][1]."&DEPT=".$courseInformation[$m][0]."'>".$courseInformation[$m][0].' '.$courseInformation[$m][1]."</a></td>
				<td width = \"55%\">".$courseInformation[$m][2]."</td>
		     		<td width = \"8%\">".$courseInformation[$m][3]."</td>
				</tr>";
			echo "</form>";
		}
		else
		{
			if(isset($_SESSION['advisee_CLID'])) $clid = $_SESSION['advisee_CLID'];
			else $clid = $_SESSION['CLID'];
			$course_num = $courseInformation[$m][1];
			$course_dept = $courseInformation[$m][0];
			$CompletedQuery = mysql_query("(SELECT distinct grade FROM take T WHERE T.clid = '$clid'  and T.course_num = '$course_num' and T.course_dept = '$course_dept') UNION (SELECT distinct grade FROM spec_cred S WHERE S.clid = '$clid' and S.course_num = '$course_num' and S.course_dept = '$course_dept') ORDER BY grade limit 1;") or die(mysql_error());
			$CreditQuery = mysql_query("(SELECT distinct grade FROM take T WHERE T.clid = '$clid' and T.course_dept = '$course_dept' and T.course_num = '$course_num' and (grade = 'CR' or grade = 'NA') and grade != 'I') UNION (SELECT distinct grade FROM spec_cred S WHERE S.clid = '$clid' and S.course_num = '$course_num' and S.course_dept = '$course_dept' and grade = 'CR') ORDER BY grade limit 1;") or die(mysql_error());
			$tempGrade = mysql_fetch_array($CompletedQuery);
			if((($tempGrade) and (strcmp($tempGrade,$courseInformation[$m][3]) <= 0)) or (mysql_fetch_array($CreditQuery)) or (strcmp($courseInformation[$m][3],"NA") == 0))
			{
				echo "<form action=\"upperDivision.php?drop_req_dept=".$courseInformation[$m][0]."&drop_req_num=".$courseInformation[$m][1]."\" method=\"post\" style = \"display:inline;\">";
				echo "<tr>";
				if($_SESSION['advisor']==true)
					echo "<td align = \"center\" width = \"7%\"><input type=\"submit\"  value=\"x\" /;></td>";
				echo "<td width = \"30%\"><a href='course.php?NUM=".$courseInformation[$m][1]."&DEPT=".$courseInformation[$m][0]."'><font color=\"green\">".$courseInformation[$m][0].' '.$courseInformation[$m][1]."</font></a></td>
					<td width = \"55%\"><font color=\"green\">".$courseInformation[$m][2]."</font></td>
					<td width = \"15%\"><font color=\"green\">".$courseInformation[$m][3]."</font></td>
					</tr>";
				echo "</form>";
			}
			else
			{
				echo "<form action=\"upperDivision.php?drop_req_dept=".$courseInformation[$m][0]."&drop_req_num=".$courseInformation[$m][1]."\" method=\"post\" style = \"display:inline;\">";
				echo "<tr>";
				if($_SESSION['advisor']==true)
					echo "<td align = \"center\" width = \"7%\"><input type=\"submit\"  value=\"x\" /;></td>";
				echo "<td width = \"30%\"><a href='course.php?NUM=".$courseInformation[$m][1]."&DEPT=".$courseInformation[$m][0]."'>".$courseInformation[$m][0].' '.$courseInformation[$m][1]."</a></td>
					<td width = \"55%\">".$courseInformation[$m][2]."</td>
			     		<td width = \"15%\">".$courseInformation[$m][3]."</td>
					</tr>";
				echo "</form>";
			}
		}
		$m = $m + 1;
	}
	echo "</table></ul>";
	if((!$_SESSION['advisor']) or isset($_SESSION['advisee_CLID'])) echo "<font color=\"green\">Green text indicates that the requirements of the course have been met.</font><br></div>";
	else echo "</div>";
}

function displayEditRequirements($department)
{
	echo "<center><font size = \"+1\"><b>Add Upper Division Requirement</b></font></center>";
	echo "<form action=\"upperDivision.php?submit=1\" method=\"post\">";
	echo "<table align=\"center\"><tr>";
	echo "<td>Department</td>";
	echo "<td width = \"10%\"></td>";
	echo "<td>Course Number</td>";
	echo "<td width = \"10%\"></td>";
	echo "<td>Required Grade</td>";
	echo "</tr><tr>";
	echo "<td align=\"center\">
	      <select name=\"new_dept\">";
	      $departmentQuery = mysql_query("SELECT dept_name FROM department;");
	      while($dept=mysql_fetch_array($departmentQuery)){
	      	if($dept['dept_name']!='INST'){
			echo "<option value=\"".$dept['dept_name']."\" ";
			echo " >".$dept['dept_name']."</option>";
	        }
	      }
	echo "</select></td>";
	echo "<td width = \"10%\"></td>";
	echo "<td align=\"center\"><input type=\"text\" name=\"new_course_num\" size=\"3\"\" /></td>";
	echo "<td width = \"10%\"></td>";
	echo "<td align=\"center\">
	      <select name=\"new_grade\">";
	echo "<option value = \"A\">A</option>";
	echo "<option value = \"B\">B</option>";
	echo "<option value = \"C\">C</option>";
	echo "<option value = \"D\">D</option>";
	echo "<option value = \"F\">F</option>";
	echo "<option value = \"CR\">CR</option>";
	echo "<option value = \"NA\">NA</option>";
	echo "<option value = \"I\">I</option>";
	echo "</select></td></tr></table>";
	echo "<table align=\"center\"><tr>";
	echo "<td align=\"center\"><input type=\"submit\" /></td>";
	echo "</tr></table></form>";
}

function submitAddition()
{
	if(!$_SESSION['advisor'])
	{
		echo "<center><font color='#FF0000'>You done goofed: Must Be Logged In As An Advisor To Add Upper Division Requirements</font></center>";
	}
	elseif($_SESSION['advisor'] and $_POST['new_course_num']!="" and $_POST['new_dept']!="" and $_POST['new_grade']!="")
	{
		$clid = $_SESSION['CLID'];

		$DepartmentQuery = mysql_query("SELECT distinct A.department FROM advisor A WHERE A.clid = '$clid'order by A.department;") or die(mysql_error());
		$department = mysql_fetch_array($DepartmentQuery);
		
		//Query to find the desired course/department combination
		$departmentCheck = mysql_query("SELECT * from course WHERE course_num = '".$_POST['new_course_num']."' and course_dept = '".$_POST['new_dept']."';");
		//Does the desired course/department combination exist?
		if(mysql_fetch_array($departmentCheck))
		{
			//Query try to find the course/department combination in the upper division requirements
			$upperDivisionCheck = mysql_query("SELECT * from up_div_req WHERE up_div_req.course_num = '".$_POST['new_course_num']."' and up_div_req.course_dept = '".$_POST['new_dept']."';");
			//Is the desired combination already part of the requirements?
			if(!mysql_fetch_array($upperDivisionCheck))
			{
				mysql_query('SET foreign_key_checks = 0');
				mysql_query("INSERT INTO up_div_req VALUES ('".trim(strtoupper($department['department']))."', '".mysql_real_escape_string(trim($_POST['new_course_num']))."', '".mysql_real_escape_string(trim($_POST['new_dept']))."', '".mysql_real_escape_string(trim($_POST['new_grade']))."');")  or die(mysql_error());
				mysql_query('SET foreign_key_checks = 1');
				unset($_POST['new_course_num']);
				unset($_POST['new_dept']);
				unset($_POST['new_grade']);
			}
		}
		else
			echo "<center><font color='#FF0000'>You done goofed: The Desired Department Does Not Have This Course</font></center>";
	}
}

function dropReq($department, $course_num)
{
	//Query to find the desired requirement
	$requirementCheck = mysql_query("SELECT * from up_div_req WHERE course_num = '".$course_num."' and course_dept = '".$department."';");
	//Does the desired course/department combination exist?
	if(mysql_fetch_array($requirementCheck))
		//If it does, delete it
		mysql_query("DELETE FROM up_div_req WHERE course_dept='".$department."' and course_num = '".$course_num."';");
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
