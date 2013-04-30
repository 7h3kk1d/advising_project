<?php
/*
File: courseListingFunctions.php
Description: Contains all functions to show and edit the course offerings page
Author: Kenneth Cross/Alexander Bandukwala
Date: Tuesday, April 19th, 2011
I hereby certify that this page is entirely my own work.
*/


/*
Function: displayBody()
Description: Display the Courses
*/
function displayBody()
{
	//Display header
	echo "<center><h1>Courses</h1></center><hr>";

	//Display the college/department/course list
	displayColleges();
	if($_SESSION['advisor']==true)
		//If logged in as an advisor, give the ability to add departments
		displayAddDepartment();
	if($_SESSION['advisor']==true)
		//If logged in as an advisor, give the ability to add courses
		displayAddCourse();
}

/*
Function: displayColleges()
Description: Write college/course information to the body. Give the user the option to show/hide course information for each college. Also contains the filter.
*/
function displayColleges()
{
	//Form to filter the results by department, year, and semester. All 3 are required
	echo "<center><font size = \"+1\"><b>Filter</b></font><br>";
	echo "*all fields must be entered to be valid</center>";
	echo "<form method=\"post\" style = \"display:inline;\" action=\"courseListing.php\">";
	echo "<table align = \"center\"><tr align=\"center\">";
	echo "<td>Department</td>";
	echo "<td>&nbsp;&nbsp;&nbsp;</td>";
	echo "<td>Year</td>";
	echo "<td>&nbsp;&nbsp;&nbsp;</td>";
	echo "<td>Semester</td></tr>";
	echo "<tr align=\"center\"><td><select name = \"department\"><option value=\"\"></option>";
        $departmentQuery = mysql_query("SELECT dept_name FROM department;");
        while($dept=mysql_fetch_array($departmentQuery)){
        	if($dept['dept_name']!='INST'){
			echo "<option ".($_POST['department']==$dept['dept_name'] ? 'selected=\"yes\"' : '')." value=\"".$dept['dept_name']."\" ";
			echo " >".$dept['dept_name']."</option>";
        	}
      	}
	echo "</select></td>";
	echo "<td>&nbsp;&nbsp;&nbsp;</td>";
	echo "<td><input name=\"year\" size=\"4\" value=\"".$_POST['year']."\" /;></td>";
	echo "<td>&nbsp;&nbsp;&nbsp;</td>";
	echo "<td><select name =\"semester\"><option value=\"\"></option>";
	//The semester is stored in the database using the following standard:
	//0 = spring, 1 = summer intercession, 2 = summer, 3 = fall, 4 = winter intercession.
	echo "<option ".($_POST['semester']=='3' ? 'selected=\"yes\"' : '')." value=\"3\">Fall</option>";//form
	echo "<option ".($_POST['semester']=='4' ? 'selected=\"yes\"' : '')." value=\"4\">Winter Intercession</option><option ".($_POST['semester']=='0' ? 'selected=\"yes\"' : '')." value=\"0\">Spring</option>";//DD
	echo "<option ".($_POST['semester']=='2' ? 'selected=\"yes\"' : '')." value=\"2\">Summer</option><option ".($_POST['semester']=='1' ? 'selected=\"yes\"' : '')." value=\"1\">Summer Intercession</option>";//DD
	echo "</td>";
	echo "</tr></table>";
	echo "<center><input type=\"submit\"  value=\"Submit\" /;></center><br>";
	echo "</form>";
	
	//Table populated based on the data sent from the above form
	if($_POST['semester'] and $_POST['department'] and $_POST['year'])
	{
		//Query to get the classes according to the post data
		$classQuery = mysql_query("SELECT distinct course_num, section_num, title, credit_hours FROM class where year='".$_POST['year']."' and semester = '".$_POST['semester']."' and course_dept = '".$_POST['department']."';");
		if($class = mysql_fetch_array($classQuery))
		{
			echo "<table border = \"1\" align = \"center\"><tr>
			      <td>Number</td>
			      <td>Section</td>
			      <td>Hours</td>
			      <td>Title</td></tr>";
			//Use course number and department to get the course title and credit hours
			$courseQuery = mysql_query("SELECT distinct title, credit_hours FROM course where course_num = '".$class['course_num']."' and course_dept = '".$_POST['department']."';");
			$course = mysql_fetch_array($courseQuery);
			echo "<tr><td>".$class['course_num']."</td><td>".$class['section_num']."</td><td>".$course['credit_hours']."</td><td><a href = 'course.php?NUM=".$class['course_num']."&DEPT=".$_POST['department']."'>".$course['title'].($class['title'] ? ':'.$class['title'] : '')."</a></td></tr>";
			while($class = mysql_fetch_array($classQuery))
			{
				$courseQuery = mysql_query("SELECT distinct title, credit_hours FROM course where course_num = '".$class['course_num']."' and course_dept = '".$_POST['department']."';");
			      $course = mysql_fetch_array($courseQuery);
			      echo "<tr><td>".$class['course_num']."</td><td>".$class['section_num']."</td><td>".$course['credit_hours']."</td><td><a href = 'course.php?NUM=".$class['course_num']."&DEPT=".$_POST['department']."'>".$course['title'].($class['title'] ? ':'.$class['title'] : '')."</a></td></tr>";
			}
			echo "</table>";
		}
		echo "<br>";
	}
	
	//Query to find all college names
	$collegeNameQuery = mysql_query("SELECT distinct college_name FROM department order by college_name;") or die(mysql_error());
	$i = 0;
	$collegeNames = array();

	while($tempNames = mysql_fetch_array($collegeNameQuery))
	{
		if($tempNames['college_name']!='College of Instructor'){		
			$collegeNames[$i] = $tempNames['college_name'];
			echo "<font size = \"+2\"><b><a href='javascript:show(\"$i.0\")' onclick=\"this.innerHTML=(this.innerHTML=='[&ndash;]  $collegeNames[$i]' ? '[+]  $collegeNames[$i]':'[&ndash;]  $collegeNames[$i]')\">[+] $collegeNames[$i]</a></b></font><br>";
		
			//Writes the departments for a specific college. A further function call is made within this function to display individual courses within the departments.
			displayDepartments($collegeNames[$i], $i);

			$i = $i + 1;
		}
	}
}

/*
Function: displayDepartments($college, $i)
Description: Takes a college and a counter number (for the element id) and writes links for all the departments in that college.
*/
function displayDepartments($college, $i)
{
	echo "<div id='$i.0' style='display:none;'>";
	
	echo "<ul>";
	$j = 0;
	$departmentNameQuery = mysql_query("SELECT dept_name FROM department where college_name = '".mysql_real_escape_string($college)."';") or die(mysql_error());
	while($tempNames = mysql_fetch_array($departmentNameQuery))
	{
		if($_SESSION['advisor']==true)
			echo "<form action=\"courseListing.php\" method=\"post\" style = \"display:inline;\">";
		$departmentNames[$j] = $tempNames['dept_name'];
		//$k is a temporary variable for output purposes only.
		$k = $j + 1;
		
		echo "<li style = 'list-style-type: none;'><font size = \"+1\">";
		if($_SESSION['advisor']==true)
			echo "<input type=\"hidden\" name=\"drop_dept_name\" value=\"".$departmentNames[$j]."\" />
		              <input type=\"submit\"  value=\"x\" />";
		echo  "<a href='javascript:show(\"$i.$k\")'  onclick=\"this.innerHTML=(this.innerHTML=='[&ndash;]  $departmentNames[$j]' ? '[+]  $departmentNames[$j]':'[&ndash;]  $departmentNames[$j]')\">[+] $departmentNames[$j]</a></font><br>";
		echo  "</li>";
			//Writes the courses for a specific department as links to a dynamic page with their information.
		displayCourses($departmentNames[$j], $i, $k);
			$j = $j + 1;
		if($_SESSION['advisor']==true)
			echo "</form>";
	}
        echo "</ul>";
	
	echo "</div>";
}

/*
Function: displayCourses($department, $i, $j)
Description: Takes a department and two counter numbers (college/department) and writes links for all courses in the department.
*/
function displayCourses($department, $i, $j)
{
		echo "<div id='$i.$j' style='display:none;'><ul>";
		$k = 0;
		
		//Query to return the department titles and course numbers
		$departmentNameQuery = mysql_query("SELECT distinct title, course_num FROM course where course_dept = '".$department."';") or die(mysql_error());
		//Output the course numbers and titles if they exist
		while($tempNames = mysql_fetch_array($departmentNameQuery))
		{
			$courseNames[$k][0] = $tempNames['course_num'];
			$courseNames[$k][1] = $tempNames['title'];
			//$l is a temporary variable for output purposes only.
			$l = $k + 1;			
			echo "<li style = 'list-style-type: none;'><a href='course.php?NUM=".$courseNames[$k][0]."&DEPT=".$department."'>".$courseNames[$k][0].", ".$courseNames[$k][1]."</a><br></li>";
			
			$k = $k + 1;
		}
	        echo "</ul></div>";
}

/*
Function: displayAddDepartment()
Description: Shows the forms to add a department
*/
function displayAddDepartment(){
	//Needs department name, full department name, and college name
	echo "<center><h2>Add Department</h2></center>";
	echo "<form action=\"courseListing.php\" method=\"post\">";
	echo "<table align=\"center\"><tr>";
	echo "<th>DEPT</th>";
	echo "<th>Full Department</th>";
	echo "<th>College Name</th>";
	echo "</tr><tr>";
	echo "<td align=\"center\"><input type=\"text\" name=\"new_dept\" size=\"6\" value=\"".$_POST['new_dept']."\" /></td>";
	echo "<td align=\"center\"><input type=\"text\" name=\"new_full_dept\" size=\"20\" value=\"".$_POST['new_full_dept']."\" /></td>";
	echo "<td align=\"center\"><input type=\"text\" name=\"new_college_name\" size=\"20\" value=\"".$_POST['new_college_name']."\" /></td>";
	echo "<td align=\"center\"><input type=\"submit\" /></td>";
	echo "</tr></table></form>";
}

/*
Function: displayAddCourse()
Description: Shows the forms to add a course
*/
function displayAddCourse(){
	//Course must belong to a department
	$departmentQuery = mysql_query("SELECT dept_name FROM department;");
	echo "<center><h2>Add Course</h2></center>";
	echo "<form action=\"courseListing.php\" method=\"post\">";
	echo "<table align=\"center\"><tr>";
	echo "<th>Department</th>";
	echo "<th>Number</th>";
	echo "<th>Title</th>";
	echo "<th>Credit Hours</th>";
	echo "</tr><tr>";
	echo "<td align=\"center\">
	      <select name=\"new_course_dept\">";
	while($dept=mysql_fetch_array($departmentQuery)){
		if($dept['dept_name']!='INST'){
			echo "<option value=\"".$dept['dept_name']."\" ";
			echo " >".$dept['dept_name']."</option>";
		}
	}
	echo "</select></td>";
	echo "<td align=\"center\"><input type=\"text\" name=\"new_course_num\" size=\"3\" value=\"".$_POST['new_course_num']."\" /></td>";
	echo "<td align=\"center\"><input type=\"text\" name=\"new_course_title\" size=\"20\" value=\"".$_POST['new_course_title']."\" /></td>";
	echo "<td align=\"center\"><input type=\"text\" name=\"new_course_credit_hours\" size=\"3\" value=\"".$_POST['new_course_credit_hours']."\" /></td>";
	echo "<td align=\"center\"><input type=\"submit\" /></td>";
	echo "</tr></table></form>";
}

/*
Function: displayAddCourse()
Description: Submits the data in the new department forms
*/
function submit_new_dept(){
	$fail=false;
	if(trim($_POST['new_dept'])==""){
		echo "<font color='#FF0000'>You done goofed: Department Not Listed</font><br>";
		$fail=true;
	}
	if(ereg('[^A-Za-z]',trim($_POST['new_dept']))){
		echo "<font color='#FF0000'>You done goofed: DEPT can only contain characters</font><br>";
	}
	if(trim($_POST['new_college_name'])==""){
		echo "<font color='#FF0000'>You done goofed: College Name Not Listed</font><br>";
		$fail=true;
	}
	if(strlen(trim($_POST['new_dept']))>6){
		echo "<font color='#FF0000'>You done goofed: DEPT Can't be longer than 6 characters</font><br>";
		$fail=true;
	}
	if(mysql_fetch_array(mysql_query("SELECT * FROM department WHERE dept_name='".trim($_POST['new_dept'])."'"))){
		echo "<font color='#FF0000'>You done goofed: DEPT already exists</font><br>";
		$fail=true;
	}
	if(!$fail){
		mysql_query("INSERT INTO department VALUES ('".trim(strtoupper($_POST['new_dept']))."', '".mysql_real_escape_string(trim($_POST['new_full_dept']))."', '".mysql_real_escape_string(trim($_POST['new_college_name']))."', null);") or die(mysql_error());
		unset($_POST['new_dept']);
		unset($_POST['new_college_name']);
		unset($_POST['new_full_dept']);
	}
}

/*
Function: submit_new_course()
Description: Submits the data in the new course forms
*/
function submit_new_course(){
	$fail=false;
	if(trim($_POST['new_course_num'])==""){
		echo "<font color='#FF0000'>You done goofed: Course Number Not Listed</font><br>";
		$fail=true;
	}
	if(ereg('[^0-9]',trim($_POST['new_course_num']))){
		echo "<font color='#FF0000'>You done goofed: Course Number Can Only Contain Numbers</font><br>";
	}
	if(ereg('[^0-9]',trim($_POST['new_course_credit_hours']))){
		echo "<font color='#FF0000'>You done goofed: Credit Hours Can Only Contain Numbers</font><br>";
		$fail=true;
	}
	if($_POST['new_course_credit_hours']<0){
		echo "<font color='#FF0000'>You done goofed: Credit Hours Must Be Positive</font><br>";
		$fail=true;
	}
	if(mysql_fetch_array(mysql_query("SELECT * FROM department WHERE dept_name='".trim($_POST['new_dept'])."'"))){
		echo "<font color='#FF0000'>You done goofed: DEPT already exists</font><br>";
		$fail=true;
	}
	if(!$fail){
		mysql_query("INSERT INTO course VALUES ('".trim($_POST['new_course_num'])."', '".$_POST['new_course_dept']."', '".$_POST['new_course_title']."', null, '".$_POST['new_course_credit_hours']."', null, null, null, null, null, null, null);") or die(mysql_error());
		unset($_POST['new_course_dept']);
		unset($_POST['new_course_num']);
		unset($_POST['new_course_title']);
		unset($_POST['new_course_credit_hours']);
	}
}

/*
Function: dropDept()
Description: Removes a department from the database
*/
function dropDept(){
	$fail=false;
	//Can't delete a department which has courses (preserves database integrity)
	if(mysql_fetch_array(mysql_query("SELECT * FROM course WHERE course_dept='".$_POST['drop_dept_name']."';"))){
		$fail=true;
		echo "<font color='#FF0000'>You done goofed: Can't Delete Department Which Has Courses</font><br>";
	}
	if(!$fail){
		mysql_query("DELETE FROM department WHERE dept_name='".$_POST['drop_dept_name']."';");
	}
}

/*
Function: dropCourse()
Description: Removes a course from the database
*/
function dropCourse(){
	$fail=false;
	//Error statement to preserve database integrity
	if(mysql_fetch_array(mysql_query("SELECT * FROM class WHERE course_dept='".$_POST['delete_course_dept']."' AND course_num='".$_POST['delete_course_num']."';"))){
		$fail=true;
		echo "<font color='#FF0000'>You done goofed: Can't Delete Course Which Has Classes</font><br>";
	}
	//Error statement to preserve database integrity
	if(mysql_fetch_array(mysql_query("SELECT * FROM class_requisite WHERE req_course_dept='".$_POST['delete_course_dept']."' AND req_course_num='".$_POST['delete_course_num']."';"))){
		$fail=true;
		echo "<font color='#FF0000'>You done goofed: Can't Delete Course Which is a Class Prereq</font><br>";
	}
	//Error statement to preserve database integrity
	if(mysql_fetch_array(mysql_query("SELECT * FROM spec_cred WHERE course_dept='".$_POST['delete_course_dept']."' AND course_num='".$_POST['delete_course_num']."';"))){
		$fail=true;
		echo "<font color='#FF0000'>You done goofed: Can't Delete Course Which Has Special Cred</font><br>";
	}
	//Error statement to preserve database integrity
	if(mysql_fetch_array(mysql_query("SELECT * FROM requisite WHERE req_course_dept='".$_POST['delete_course_dept']."' AND req_course_num='".$_POST['delete_course_num']."';"))){
		$fail=true;
		echo "<font color='#FF0000'>You done goofed: Can't Delete Course Which is a Requisite</font><br>";
	}
	//Error statement to preserve database integrity
	if(mysql_fetch_array(mysql_query("SELECT * FROM requisite WHERE course_dept='".$_POST['delete_course_dept']."' AND course_num='".$_POST['delete_course_num']."';"))){
		$fail=true;
		echo "<font color='#FF0000'>You done goofed: Can't Delete Course Which Has Requisites</font><br>";
	}
	//Error statement to preserve database integrity
	if(mysql_fetch_array(mysql_query("SELECT * FROM up_div_req WHERE course_dept='".$_POST['delete_course_dept']."' AND course_num='".$_POST['delete_course_num']."';"))){
		$fail=true;
		echo "<font color='#FF0000'>You done goofed: Can't Delete Course Which is Required for Upper Division</font><br>";
	}
	//Error statement to preserve database integrity
	if(mysql_fetch_array(mysql_query("SELECT * FROM counts_for WHERE course_dept='".$_POST['delete_course_dept']."' AND course_num='".$_POST['delete_course_num']."';"))){
		$fail=true;
		echo "<font color='#FF0000'>You done goofed: Can't Delete Course Which Counts for Another Course</font><br>";
	}
	//Error statement to preserve database integrity
	if(mysql_fetch_array(mysql_query("SELECT * FROM counts_for WHERE sub_course_dept='".$_POST['delete_course_dept']."' AND sub_course_num='".$_POST['delete_course_num']."';"))){
		$fail=true;
		echo "<font color='#FF0000'>You done goofed: Can't Delete Course Which Has Another Course Count for It</font><br>";
	}
	//Error statement to preserve database integrity
	if(mysql_fetch_array(mysql_query("SELECT * FROM requires WHERE course_dept='".$_POST['delete_course_dept']."' AND course_num='".$_POST['delete_course_num']."';"))){
		$fail=true;
		echo "<font color='#FF0000'>You done goofed: Can't Delete Course Which Is in the Degree Checklist</font><br>";
	}
	//Error statement to preserve database integrity
	if(!mysql_fetch_array(mysql_query("SELECT * FROM course WHERE course_dept='".$_POST['delete_course_dept']."' AND course_num='".$_POST['delete_course_num']."';"))){
		$fail=true;
		echo "<font color='#FF0000'>You done goofed: Can't Delete Course Which Has Been Deleted</font><br>";
	}
	//Success statement - delete course.
	if(!$fail){
		mysql_query("DELETE FROM course WHERE course_dept='".$_POST['delete_course_dept']."' AND course_num='".$_POST['delete_course_num']."';");
	}
}
?>

<script language type="text/javascript">
/*
Function: show()
Description: Expand or shrink the element referenced by layer_ref (expands if the current state is "none", shrinks if the current state is "block")
*/	
function show(layer_ref) {
//if(layer_ref == "2.1")document.write(layer_ref);
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
