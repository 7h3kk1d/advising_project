<?php
/*
File: courseFunctions.php
Description: Contains all functions for the course page
Author: Alexander Bandukwala/Kenneth Cross
Date: Wednesday April 20th, 2011
I hereby certify that this page is entirely my own work.
*/

/*
Function: displayCourse($COURSE_DEPT, $COURSE_NUM)
Description: Display the course data - calls other functions which handle the output
*/
function displayCourse($COURSE_DEPT, $COURSE_NUM){

	$course = mysql_fetch_array(mysql_query("SELECT * FROM course where course_num = '$COURSE_NUM' AND course_dept = '$COURSE_DEPT';"));
	if($course==null)
		noCourseGoof($COURSE_DEPT, $COURSE_NUM);
	else{
		displayCourseInfo($course);
		displayAdvisingLink($course);
		displayCoursePrerequisites($course);
		displayCourseFulfills($course);
		if($_SESSION['advisor']==true)
			displayAddClass();
		displayClassesInfo($COURSE_DEPT, $COURSE_NUM);	
	}
}

/*
Function: noCourseGoof($COURSE_DEPT, $COURSE_NUM)
Description: Error message to show if the course is not found
*/
function noCourseGoof($COURSE_DEPT, $COURSE_NUM){
	echo "<h2><font color='#FF0000'>You done goofed: ".$COURSE_DEPT." ".$COURSE_NUM." Does Not Exist</font></h2>";
}

/*
Function: displayCourseInfo($course)
Description: Display the basic properties of a course
*/
function displayCourseInfo($course){
	echo "<table align = 'right'><tr><td>";
	if($_SESSION['advisor']==true)
		//Form to delete course or edit course
		echo "<form style = \"display:inline;\" method=\"post\" action=\"courseListing.php\">
		      <input type=\"hidden\" name=\"delete_course_dept\" value=\"".$course['course_dept']."\" />
		      <input type=\"hidden\" name=\"delete_course_num\" value=\"".$course['course_num']."\" />
		      <button type='submit' name='delete_course' value='true'>Delete Course</button>
		      </form>
		      <form style = \"display:inline;\" method=\"post\" action=\"course.php?edit=1&DEPT=".$course['course_dept']."&NUM=".$course['course_num']."\">
		      <button type='submit' name='edit'>Edit Course Information</button></form>";
	echo "</td></tr></table><br>";

	//Course title
	echo "<center><h1>".$course['course_dept']." ".$course['course_num']." - ".$course['title']."</h1></center><hr>";
	
	//Course information
	echo "<table align = \"center\" width = \"60%\" border = \"1\">
	      <tr align = \"center\">
	      <td width = \"30%\"><font size = \"-2\">Course</font></td>";
	if(isset($course['title']))
		echo "<td width = \"30%\"><font size = \"-2\">Title</font></td>";
	if(isset($course['credit_hours']))
		echo "<td width = \"20%\"><font size = \"-2\">Credit Hours</font></td>";
	if(isset($course['remedial']))
	     	echo"<td width = \"20%\"><font size = \"-2\">Remedial</font></td>";
 	echo  "</tr>
	      <tr align = \"center\">
	      <td width = \"30%\">".$course['course_dept']." ".$course['course_num']."</td>";
	if(isset($course['title']))
		echo "<td width = \"30%\">".$course['title']."</td>";
	if(isset($course['credit_hours']))
		echo "<td width = \"20%\">".$course['credit_hours']."</td>";
	if(isset($course['remedial']))
     		echo "<td width = \"20%\">".(($course['remedial'])?'Yes':'No')."</td>";
	  echo "</tr>
	        </table><br>";
	if(isset($course['description']))
		echo "<b>Description:</b> ".$course['description']."<br>";
	if(isset($course['notes']))
		echo "<b>Notes:</b> ".$course['notes']."<br>";

	//ACT scores
	if(isset($course['act_composite']) or 
           isset($course['act_english']) or 
           isset($course['act_math']) or 
           isset($course['act_reading']) or 
           isset($course['act_science']))
	{
		echo "<center><b>ACT Requirements</b></center>";
		echo "<table align = \"center\" border = \"1\">
		      <tr align = \"center\">";
		if(isset($course['act_composite']))
			echo "<td width = \"20%\"><font size = \"-2\">Composite</font></td>";
		if(isset($course['act_math']))
			echo "<td width = \"20%\"><font size = \"-2\">Math</font></td>";
		if(isset($course['act_english']))
			echo "<td width = \"20%\"><font size = \"-2\">English</font></td>";
		if(isset($course['act_reading']))
			echo "<td width = \"20%\"><font size = \"-2\">Reading</font></td>";
		if(isset($course['act_science']))
			echo "<td width = \"20%\"><font size = \"-2\">Science</font></td>";
		
		echo"</tr>
		     <tr align = \"center\">";
		
		if(isset($course['act_composite']))
			echo "<td width = \"20%\">".$course['act_composite']."</td>";
		if(isset($course['act_math']))
			echo "<td width = \"20%\">".$course['act_math']."</td>";
		if(isset($course['act_english']))
			echo "<td width = \"20%\">".$course['act_english']."</td>";
		if(isset($course['act_reading']))
			echo "<td width = \"20%\">".$course['act_reading']."</td>";
		if(isset($course['act_science']))
			echo "<td width = \"20%\">".$course['act_science']."</td>";
		echo "</table>";
	}
}

/*
Function: displayClassesInfo($COURSE_DEPT, $COURSE_NUM)
Description: Displays the information for each class instance of a course
*/
function displayClassesInfo($COURSE_DEPT, $COURSE_NUM){
	//Find the years that classes were available
	$year_query = mysql_query("SELECT year FROM class where course_num = '$COURSE_NUM' AND course_dept = '$COURSE_DEPT' GROUP BY year ORDER BY year;");
	//Get them
	while($year = mysql_fetch_array($year_query)){
		//Output properties
		echo "<br><a href=\"javascript:show('".$year['year']."')\">[+] ".$year['year']."</a>";
		echo "<div id='".$year['year']."' style='display:none;'>";
		$class_query = mysql_query("SELECT section_num, semester, year, title FROM class where course_num = '$COURSE_NUM' AND course_dept = '$COURSE_DEPT' AND year='".$year['year']."' ORDER BY semester;");
		echo "<table align = \"center\" width = \"65%\" border = \"1\">";
		while($class = mysql_fetch_array($class_query)){
			echo "<tr><td>".$COURSE_DEPT." ".$COURSE_NUM."</td><td>";
			switch($class['semester']){
				case '3':
					echo "Fall";
					break;
				case '0':
					echo "Spring";
					break;
				case '2':
					echo "Summer";
					break;
				case '1':
					echo "Summer Intercession";
					break;
				case '4':
					echo "Winter Intercession";			
					break;
			}
			echo " ".$year['year'];
			echo "</td><td><a href='class.php?DEPT=".$COURSE_DEPT."&NUM=".$COURSE_NUM."&SEMESTER=".$class['semester']."&YEAR=".$class['year']."&SECTION=".$class['section_num']."'>Section ".sprintf("%03d",$class['section_num'])."</a></td></tr>";
		}
		echo "</table>";
		echo "</div>";
	
	}
}

/*
Function: displayCoursePrerequisites($course)
Description: Display the prerequisites for a course
*/
function displayCoursePrerequisites($course)
{
	//Get the prerequisite groupings
	$keys = mysql_query("SELECT distinct prim_key FROM requisite where course_num = \"".$course['course_num']."\" and course_dept = \"".$course['course_dept']."\" order by prim_key;");
	
	//If there are prerequisites in those groupings, output them
	if($keyArray = mysql_fetch_array($keys)){
		echo "<table align = \"center\"><tr align = \"center\"><td><b>Prerequisites</b></td></tr><tr><td>";
		echo "<div style = \"border: 1px solid #000; vertical-align: middle; display: inline-table;\" width = \"20%\" border = \"1\">";

			$requisites = mysql_query("SELECT req_course_num, req_course_dept, grade, type FROM requisite where course_num = \"".$course['course_num']."\" and course_dept = \"".$course['course_dept']."\" and prim_key=\"".$keyArray['prim_key']."\" order by course_dept;");


		while($requisiteArray=mysql_fetch_array($requisites)){
			if($requisiteArray['req_course_dept'] == "INST" and $requisiteArray['req_course_num'] == "100")
				echo "Instructor Permission Needed<br>";
			else{
				if($requisiteArray['type']=='C')
						echo '*';
				echo $requisiteArray['req_course_dept']." ".$requisiteArray['req_course_num'];
				if(isset($requisiteArray['grade']))
					echo " with '".$requisiteArray['grade']."' or higher";
				echo "<br>";
			}
		}
		echo "</div>";

		while($keyArray = mysql_fetch_array($keys)){
			echo " <b>OR </b><div style = \"border: 1px solid #000; vertical-align: middle; display: inline-table;\" width = \"20%\" border = \"1\">";

			$requisites = mysql_query("SELECT req_course_num, req_course_dept, grade, type FROM requisite where course_num = \"".$course['course_num']."\" and course_dept = \"".$course['course_dept']."\" and prim_key=\"".$keyArray['prim_key']."\" order by course_dept;");

			while($requisiteArray=mysql_fetch_array($requisites)){
				if($requisiteArray['req_course_dept'] == "INST" and $requisiteArray['req_course_num'] == "100")
					echo "Instructor Permission Needed<br>";
				else{
					if($requisiteArray['type']=='C')
						echo '*';
					echo $requisiteArray['req_course_dept']." ".$requisiteArray['req_course_num'];
					if(isset($requisiteArray['grade']))
						echo " with '".$requisiteArray['grade']."' or higher";
					echo "<br>";
				}
			}
			echo "</div>";
		}
		echo "</td></tr></table>";
		echo "<center><font size=\"-2\">(*) indicates course can be taken concurrently</font></center>";
	}
}

/*
Function: displayCourseFulfills($course)
Description: Displays courses that the current course can count for
*/
function displayCourseFulfills($course)
{
	//Find courses that the current course can count for
	$countsForQuery = mysql_query("SELECT sub_course_dept, sub_course_num FROM counts_for where course_dept = '".$course['course_dept']."' and course_num = '".$course['course_num']."';");
	//If they exist, output them
	if($countsFor = mysql_fetch_array($countsForQuery))
	{
		$countsFlag = true;
		echo "<table align = \"center\"><tr align = \"center\"><td><b>Counts For</b></td></tr></table>";
		echo "<table align=\"center\"><tr><td>";
		echo "<div style = \"border: 1px solid #000; vertical-align: middle; display: inline-table;\" width = \"20%\" border = \"1\">";

		echo $countsFor['sub_course_dept']." ".$countsFor['sub_course_num'];	
		echo "</div>";
	}
	$i = 1;
	while($countsFor = mysql_fetch_array($countsForQuery))
	{
		if($i % 4 != 0)
			echo "<b> AND </b>";
		
		echo "<div style = \"border: 1px solid #000; vertical-align: middle; display: inline-table;\" width = \"20%\" border = \"1\">";

		echo $countsFor['sub_course_dept']." ".$countsFor['sub_course_num'];	
		echo "</div>";
		$i = $i + 1;
		if($i % 4 == 0)
			echo "</td></tr><tr><td>";
	}
	if($countsFlag)
		echo "</td></tr></table>";
}

/*
Function: displayAdvisingLink($course)
Description: Links the advising page to the course page
*/
function displayAdvisingLink($course){
	include("calculations.php");
	//Is an advisor logged in with an advisee?
	if($_SESSION['advisor'] and isset($_SESSION['advisee_CLID']))
		//If so, set CLID to the advisee CLID
		$CLID = $_SESSION['advisee_CLID'];
	else
		//Otherwise, set the CLID to the session CLID
		$CLID = $_SESSION['CLID'];
	if(isset($_SESSION['advising']['year']) and isset($_SESSION['advising']['semester']) and meetsPrerequisite($CLID, $course['course_dept'], $course['course_num']) and mysql_fetch_array(mysql_query("SELECT * FROM class WHERE course_num='".$course['course_num']."' AND course_dept='".$course['course_dept']."' AND semester='".$_SESSION['advising']['semester']."' and year='".$_SESSION['advising']['year']."';"))){
		echo "<form action=\"advisingForm.php\" method=\"POST\">
		      <input type=\"hidden\" name=\"DEPT\" value=\"".$course['course_dept']."\" />
		      <input type=\"hidden\" name=\"NUM\" value=\"".$course['course_num']."\" />
		      <button name=\"add_advising\" value=\"true\">
		      Add to Advising Form
		      </button>
		      </form>";
	}
}

/*
Function: displayCourseEdit($COURSE_DEPT, $COURSE_NUM)
Description: Displays an editable version of the course.php page
*/
function displayCourseEdit($COURSE_DEPT, $COURSE_NUM){
	//Find the proper course
	$course = mysql_fetch_array(mysql_query("SELECT * FROM course where course_num = '$COURSE_NUM' AND course_dept = '$COURSE_DEPT';"));
	if($course==null)
		//If it doesn't exist, create it
		createCourse($COURSE_DEPT, $COURSE_NUM);
	else{
		//Display data
		displayCourseInfoEdit($course);
		displayCoursePrerequisitesEdit($course);
		displayCourseFulfillsEdit($course);
		echo "<center><font size=\"-2\">(*) indicates course can be taken concurrently</font></center>";
	}
}

/*
Function: displayCourseInfoEdit($course)
Description: Display the fields to be edited for the course table, showing the data that's already in the table.
*/
function displayCourseInfoEdit($course){
	//Header
	echo "<center><h1>".$course['course_dept']." ".$course['course_num']." - ".$course['title']."</h1></center><hr>";
	//Display titles and input fields
	echo "<form action=\"course.php?submit=1&DEPT=".$course['course_dept']."&NUM=".$course['course_num']."\" method=\"POST\">";
	echo "<table align = \"center\" width = \"70%\" border = \"1\">
	      <tr align = \"center\">
	      <td width = \"60%\"><font size = \"-2\">Title</font></td>
              <td width = \"20%\"><font size = \"-2\">Credit Hours</font></td>
	      <td width = \"20%\"><font size = \"-2\">Remedial</font></td></tr>
	      <tr align = \"center\">
   	      <td width = \"60%\">
	      <input type=\"text\" name=\"title\"  value=\"".$course['title']."\" size=30 /></td>
	      <td width = \"20%\">
	      <input type=\"text\" name=\"credit_hours\"  value=\"".$course['credit_hours']."\" size=1 /></td>
	      <td width = \"20%\">
	      <input type=\"checkbox\" name=\"remedial\" value=\"1\" ".(($course['remedial'])?'CHECKED':'')." />
	      </td></tr>
	      </table><br>";
	echo "<b>Description:</b> <textarea name=\"description\" cols=50>".$course['description']."</textarea><br>";
	echo "<b>Notes:</b> <textarea name=\"notes\" cols=50>".$course['notes']."</textarea><br>";
	echo "<center><b>ACT Requirements</b></center>
	      <table align = \"center\" border = \"1\">
	      <tr align = \"center\">
	      <td width = \"20%\"><font size = \"-2\">Composite</font></td>
	      <td width = \"20%\"><font size = \"-2\">Math</font></td>
	      <td width = \"20%\"><font size = \"-2\">English</font></td>
	      <td width = \"20%\"><font size = \"-2\">Reading</font></td>
	      <td width = \"20%\"><font size = \"-2\">Science</font></td>
	      </tr>
	      <tr align = \"center\">
	      <td width = \"20%\"><input type=\"text\" name=\"act_composite\"  value=\"".$course['act_composite']."\" size=2 /></td>
	      <td width = \"20%\"><input type=\"text\" name=\"act_math\"  value=\"".$course['act_math']."\" size=2 /></td>
	      <td width = \"20%\"><input type=\"text\" name=\"act_english\"  value=\"".$course['act_english']."\" size=2 /></td>
	      <td width = \"20%\"><input type=\"text\" name=\"act_reading\"  value=\"".$course['act_reading']."\" size=2 /></td>
	      <td width = \"20%\"><input type=\"text\" name=\"act_science\"  value=\"".$course['act_science']."\" size=2 /></td>
              </table>";
	echo "<br>";
	echo "<center><input type=\"submit\" value=\"Save\"/></center>";
	echo "</form>";
}

/*
Function: submitData()
Description: Update the database with the information for the course.
*/
function submitData(){
	//Update the course with the form information
	mysql_query("UPDATE course SET title=".((trim($_POST['title'])=="")?'null':"'".mysql_real_escape_string(trim($_POST['title']))."'").", credit_hours=".((trim($_POST['credit_hours'])=="")?'null':"'".mysql_real_escape_string(trim($_POST['credit_hours']))."'").", remedial='".(($_POST['remedial']=='1')?'1':'0')."', description=".((trim($_POST['description'])=="")?'null':"'".mysql_real_escape_string(trim($_POST['description']))."'").", notes=".((trim($_POST['notes'])=="")?'null':"'".mysql_real_escape_string(trim($_POST['notes']))."'").", act_composite=".((trim($_POST['act_composite'])=="")?'null':"'".mysql_real_escape_string(trim($_POST['act_composite']))."'").", act_math=".((trim($_POST['act_math'])=="")?'null':"'".mysql_real_escape_string(trim($_POST['act_math']))."'").", act_english=".((trim($_POST['act_english'])=="")?'null':"'".mysql_real_escape_string(trim($_POST['act_english']))."'").", act_reading=".((trim($_POST['act_reading'])=="")?'null':"'".mysql_real_escape_string(trim($_POST['act_reading']))."'").", act_science=".((trim($_POST['act_science'])=="")?'null':"'".mysql_real_escape_string(trim($_POST['act_science']))."'")." WHERE course_dept='".$_GET['DEPT']."' AND course_num='".$_GET['NUM']."';");
}

/*
Function: displayAddClass()
Description: Display the fileds for adding an instance of a class to that course.
*/
function displayAddClass(){
	//Header
	echo "<center><h2>Add Instance of Course</h2></center>";
	//Forms to add an instance of the course
	echo "<form action=\"course.php?NUM=".$_GET['NUM']."&DEPT=".$_GET['DEPT']."\" method=\"post\">
	      <input type=\"hidden\" name=\"add_class\" value=\"true\" />
	      <table align=\"center\">
	      <tr>";
	echo "<th>Department</th>";
	echo "<th>Number</th>";
	echo "<th>Section Number</th>";
	echo "<th>Semester</th>";
	echo "<th>Year</th>";
	echo "</tr><tr>";
	echo "<td align=\"center\">";
	echo $_GET['DEPT'];
	echo "</td>";
	echo "<td align=\"center\">".$_GET['NUM']."</td>";
	echo "<td align=\"center\"><input type=\"text\" name=\"new_class_section_num\" size=\"3\" value=\"".$_POST['new_class_section_num']."\" />
	      </td>";
	echo "<td align=\"center\">
	      <select name=\"new_class_semester\">
	      <option value=\"0\" ".(($_POST['new_class_semester']=="0") ? "selected":null).">Spring
	      </option>
	      <option value=\"1\" ".(($_POST['new_class_semester']=="1") ? "selected":null).">Summer Intercession
	      </option>
	      <option value=\"2\" ".(($_POST['new_class_semester']=="2") ? "selected":null).">Summer
	      </option>
	      <option value=\"3\" ".(($_POST['new_class_semester']=="3") ? "selected":null).">Fall
	      </option>
	      <option value=\"4\" ".(($_POST['new_class_semester']=="4") ? "selected":null).">Winter Intercession
	      </option>
	      </select>
	      </td>";
	echo "<td align=\"center\"><input type=\"text\" name=\"new_class_year\" size=\"4\" value=\"".$_POST['new_class_year']."\" /></td>";
	echo "<td align=\"center\"><input type=\"submit\" value=\"Add Class\" /></td>";
	echo "</tr></table></form>";
}

/*
Function: submit_new_class()
Description: Update the class table with the new class instance.
*/
function submit_new_class(){
	$fail=false;
	//Error message
	if(trim($_POST['new_class_section_num'])==""){
		echo "<font color='#FF0000'>You done goofed: Section Number Not Listed</font><br>";
		$fail=true;
	}
	//Error message
	if(trim($_POST['new_class_year'])==""){
		echo "<font color='#FF0000'>You done goofed: Year Not Listed</font><br>";
		$fail=true;
	}
	//Error message
	if(ereg('[^0-9]',trim($_POST['new_class_section_num']))){
		echo "<font color='#FF0000'>You done goofed: Section Number Can Only Contain Numbers</font><br>";
	}
	//Error message
	if(ereg('[^0-9]',trim($_POST['new_class_year']))){
		echo "<font color='#FF0000'>You done goofed: Year Can Only Contain Numbers</font><br>";
	}
	//Error message
	if(mysql_fetch_array(mysql_query("SELECT * FROM class WHERE course_dept='".trim($_GET['DEPT'])."' AND course_num='".$_GET['NUM']."' AND section_num='".trim($_POST['new_class_section_num'])."' AND semester='".trim($_POST['new_class_semester'])."' AND year='".trim($_POST['new_class_year'])."';"))){
		echo "<font color='#FF0000'>You done goofed: Section already exists</font><br>";
		$fail=true;
	}
	//Success - insert values
	if(!$fail){
		mysql_query("INSERT INTO class VALUES ('".trim($_POST['new_class_section_num'])."','".$_POST['new_class_semester']."','".$_POST['new_class_year']."','".$_GET['NUM']."','".$_GET['DEPT']."',null,null,null,null,0,null,null);");
		unset($_POST['new_class_section_num']);
		unset($_POST['new_class_semester']);
		unset($_POST['new_class_year']);
		unset($_POST['new_course_credit_hours']);
		unset($_POST['add_class']);
	}
}

/*
Function: dropClass()
Description: Drop the selected class from the database.
*/
function dropClass(){
	$fail=false;
	//Error message
	if(mysql_fetch_array(mysql_query("SELECT * FROM take WHERE course_dept='".$_POST['delete_class_dept']."' AND course_num='".$_POST['delete_class_num']."' AND section_num='".$_POST['delete_class_section_num']."' AND year='".$_POST['delete_class_year']."' AND semester='".$_POST['delete_class_semester']."';"))){
		$fail=true;
		echo "<font color='#FF0000'>You done goofed: Can't Delete Class Which Has Students</font><br>";
	}
	//Delete the class
	if(!$fail){
		mysql_query("DELETE FROM class WHERE course_dept='".$_POST['delete_class_dept']."' AND course_num='".$_POST['delete_class_num']."' AND section_num='".$_POST['delete_class_section_num']."' AND year='".$_POST['delete_class_year']."' AND semester='".$_POST['delete_class_semester']."';");
	}
}

/*
Function: displayCoursePrerequisitesEdit($course)
Description: Display the fields showing requisites and the fields for editing and adding correquisites.
*/
function displayCoursePrerequisitesEdit($course)
{
	//Get the groupings of prereuisites
	$keys = mysql_query("SELECT distinct prim_key FROM requisite where course_num = \"".$course['course_num']."\" and course_dept = \"".$course['course_dept']."\" order by prim_key;");
	//Header
	echo "<table align = \"center\"><tr align = \"center\"><td><b>Prerequisites</b></td></tr><tr><td>";
	
	//If there are prerequisites in the groupings, display them
	if($keyArray = mysql_fetch_array($keys)){
		$requisites = mysql_query("SELECT req_course_num, req_course_dept, grade, type FROM requisite where course_num = \"".$course['course_num']."\" and course_dept = \"".$course['course_dept']."\" and prim_key=\"".$keyArray['prim_key']."\" order by course_dept;");
		echo "<div style = \"border: 1px solid #000; vertical-align: middle; display: inline-table;\" width = \"20%\" border = \"1\">";
		echo "<form action=\"course.php?edit=1&DEPT=".$course['course_dept']."&NUM=".$course['course_num']."\" method=\"post\" style = \"display:inline;\">
		      <input type=\"hidden\" name=\"inst_perm\" value=\"true\" />
		      <input type=\"hidden\" name=\"prim_key\" value=\"".$keyArray['prim_key']."\" />
		      <input type=\"checkbox\" name=\"teacher_permission\" value=\"true\" onclick=\"submit();\"";
		if(mysql_fetch_array(mysql_query("SELECT req_course_num, req_course_dept, grade, type FROM requisite where course_num = \"".$course['course_num']."\" and course_dept = \"".$course['course_dept']."\" and req_course_dept='INST' and req_course_num='100' and prim_key=\"".$keyArray['prim_key']."\" order by course_dept;")))
			echo "checked=\"yes\"";
		echo "> Instructor Permission<br>
		      </form>";
		while($requisiteArray=mysql_fetch_array($requisites)){
			$INST=false;
			//Instructor permission
			if($requisiteArray['req_course_dept'] == "INST" and $requisiteArray['req_course_num'] == "100")
				$INST=true;
			else{
				echo"<form style = \"display:inline;\" action=\"course.php?edit=1&DEPT=".$course['course_dept']."&NUM=".$course['course_num']."\" method=\"post\">
				     <input type=\"hidden\" name=\"prereq_dept\" value=\"".$course['course_dept']."\" />
			 	     <input type=\"hidden\" name=\"prereq_num\" value=\"".$course['course_num']."\" />
				     <input type=\"hidden\" name=\"req_dept\" value=\"".$requisiteArray['req_course_dept']."\" />
				     <input type=\"hidden\" name=\"req_num\" value=\"".$requisiteArray['req_course_num']."\" />
				     <input type=\"hidden\" name=\"prim_key\" value=\"".$keyArray['prim_key']."\" />
				     <button type='submit' name='delete_prereq' value='true'>X</button>
				     </form>";
				if($requisiteArray['type']=='C')
						echo '*';
				echo $requisiteArray['req_course_dept']." ".$requisiteArray['req_course_num'];
				if(isset($requisiteArray['grade']))
					echo " with '".$requisiteArray['grade']."' or higher";
				echo "<br>";
			}
		}
		//Titles
		echo "<form style = \"display:inline;\" method=\"post\" action=\"course.php?edit=1&DEPT=".$course['course_dept']."&NUM=".$course['course_num']."\">
		      <table>
		      <tr>
		      <th><font size=\"-1\">DEPT</font></th>
		      <th><font size=\"-1\">NUM</font></th>
		      <th><font size=\"-1\">GRADE</font></th>
		      <th><font size=\"-1\">CONCURRENT</font></th>
		      </tr>
		      <tr>
		      <td>
		      <select name=\"prereq_dept\">";
		$departmentQuery = mysql_query("SELECT dept_name FROM department;");
		while($dept=mysql_fetch_array($departmentQuery)){
			if($dept['dept_name']!='INST')
				echo "<option value=\"".$dept['dept_name']."\" >".$dept['dept_name']."</option>";
		}
		echo "</select>
		      <td align=\"center\">
		      <input type=\"text\" name=\"prereq_num\" size=\"3\" value=\"".$_POST['new_course_num']."\" />
	              </td>
		      <td align=\"center\">";
		$possibleGrades = array('A','B','C','D','N/A');
			echo '<select name="grade">';
			foreach ($possibleGrades as $g)// echo $grade.' '.$g;
				echo "<option value=\"$g\">".$g."</option>";
		echo "</select>
		      </td>
		      <td align=\"center\">
	              <input type=\"checkbox\" name=\"concurrent\" value=\"true\" />
		      </td>
		      </tr>
		      </table>
		      <input type=\"hidden\" name=\"prim_key\" value=\"".$keyArray['prim_key']."\" />
		      <center>
		      <button type='submit' name='add_prereq' value='true'>Add Prereq</button>
		      </center>
		      </form>
		      </div>";

		//If there are more prerequisites, show them
		//FROM HERE, THE LOGIC FOLLOWS THE UPPER PART OF THE FUNCTION
		while($keyArray = mysql_fetch_array($keys)){
			echo " <b>OR </b><div style = \"border: 1px solid #000; vertical-align: middle; display: inline-table;\" width = \"20%\" border = \"1\">";

			$requisites = mysql_query("SELECT req_course_num, req_course_dept, grade, type FROM requisite where course_num = \"".$course['course_num']."\" and course_dept = \"".$course['course_dept']."\" and prim_key=\"".$keyArray['prim_key']."\" order by course_dept;");
			echo "<form action=\"course.php?edit=1&DEPT=".$course['course_dept']."&NUM=".$course['course_num']."\" method=\"post\" style = \"display:inline;\">
		      <input type=\"hidden\" name=\"inst_perm\" value=\"true\" />
		      <input type=\"hidden\" name=\"prim_key\" value=\"".$keyArray['prim_key']."\" />
		      <input type=\"checkbox\" name=\"teacher_permission\" value=\"true\" onclick=\"submit();\"";
		if(mysql_fetch_array(mysql_query("SELECT req_course_num, req_course_dept, grade, type FROM requisite where course_num = \"".$course['course_num']."\" and course_dept = \"".$course['course_dept']."\" and req_course_dept='INST' and req_course_num='100' and prim_key=\"".$keyArray['prim_key']."\" order by course_dept;")))
			echo "checked=\"yes\"";
		echo "> Instructor Permission<br>
		      </form>";
			$INST=false;
			while($requisiteArray=mysql_fetch_array($requisites)){
				if($requisiteArray['req_course_dept'] == "INST" and $requisiteArray['req_course_num'] == "100")
					$INST=true;
				else{
					echo"<form method=\"post\" style = \"display:inline;\" action=\"course.php?edit=1&DEPT=".$course['course_dept']."&NUM=".$course['course_num']."\">
				     <input type=\"hidden\" name=\"prereq_dept\" value=\"".$course['course_dept']."\" />
			 	     <input type=\"hidden\" name=\"prereq_num\" value=\"".$course['course_num']."\" />
				     <input type=\"hidden\" name=\"req_dept\" value=\"".$requisiteArray['req_course_dept']."\" />
				     <input type=\"hidden\" name=\"req_num\" value=\"".$requisiteArray['req_course_num']."\" />
				     <input type=\"hidden\" name=\"prim_key\" value=\"".$keyArray['prim_key']."\" />
				     <button type='submit' name='delete_prereq' value='true'>X</button>
				     </form>";
					if($requisiteArray['type']=='C')
						echo '*';
					echo $requisiteArray['req_course_dept']." ".$requisiteArray['req_course_num'];
					if(isset($requisiteArray['grade']))
						echo " with '".$requisiteArray['grade']."' or higher";
					echo "<br>";
				}
			}
			echo "<form style = \"display:inline;\" method=\"post\" action=\"course.php?edit=1&DEPT=".$course['course_dept']."&NUM=".$course['course_num']."\">
			      <table>
			      <tr>
			      <th><font size=\"-1\">DEPT</font></th>
			      <th><font size=\"-1\">NUM</font></th>
			      <th><font size=\"-1\">GRADE</font></th>
			      <th><font size=\"-1\">CONCURRENT</font></th>
			      </tr>
			      <tr>
			      <td>
			      <select name=\"prereq_dept\">";
			$departmentQuery = mysql_query("SELECT dept_name FROM department;");
			while($dept=mysql_fetch_array($departmentQuery)){
				if($dept['dept_name']!='INST')
					echo "<option value=\"".$dept['dept_name']."\" >".$dept['dept_name']."</option>";
			}
				echo "</select>
				      <td align=\"center\">
				      <input type=\"text\" name=\"prereq_num\" size=\"3\" value=\"".$_POST['new_course_num']."\" />
	      		  	      </td>
				      <td align=\"center\">";
			$possibleGrades = array('A','B','C','D','N/A');
				echo '<select name="grade">';
				foreach ($possibleGrades as $g)// echo $grade.' '.$g;
					echo "<option value=\"$g\">".$g."</option>";
				echo "</select>
		     		      </td>
				      <td align=\"center\">
			              <input type=\"checkbox\" name=\"concurrent\" value=\"true\" />
				      </td>
				      </tr>
				      </table>
				      <input type=\"hidden\" name=\"prim_key\" value=\"".$keyArray['prim_key']."\" />
				      <center>
				      <button type='submit' name='add_prereq' value='true'>Add Prereq</button>
				      </center>
				      </form>
				      </div>";
		}
		echo "</td></tr></table>";
	}
	$prim_key=mysql_fetch_array(mysql_query("SELECT max(prim_key) FROM requisite WHERE course_num='".$_GET['NUM']."' AND course_dept='".$_GET['DEPT']."';"));
	$prim_key=$prim_key[0]+1;
	echo "<form style = \"display:inline;\" method=\"post\" action=\"course.php?edit=1&DEPT=".$course['course_dept']."&NUM=".$course['course_num']."\">
			      <table align=\"center\">
			      <tr>
			      <th><font size=\"-1\">DEPT</font></th>
			      <th><font size=\"-1\">NUM</font></th>
			      <th><font size=\"-1\">GRADE</font></th>
			      <th><font size=\"-1\">CONCURRENT</font></th>
			      </tr>
			      <tr>
			      <td>
			      <select name=\"prereq_dept\">";
			$departmentQuery = mysql_query("SELECT dept_name FROM department;");
			while($dept=mysql_fetch_array($departmentQuery)){
				if($dept['dept_name']!='INST')
					echo "<option value=\"".$dept['dept_name']."\" >".$dept['dept_name']."</option>";
			}
				echo "</select>
				      <td align=\"center\">
				      <input type=\"text\" name=\"prereq_num\" size=\"3\" value=\"".$_POST['new_course_num']."\" />
	      		  	     </td>
				      <td align=\"center\">";
			$possibleGrades = array('A','B','C','D','N/A');
				echo '<select name="grade">';
				foreach ($possibleGrades as $g)// echo $grade.' '.$g;
					echo "<option value=\"$g\">".$g."</option>";
				echo "</select>
		     		      </td>
				      <td align=\"center\">
			              <input type=\"checkbox\" name=\"concurrent\" value=\"true\" />
				      </td>
				      </tr>
				      </table>
				      <input type=\"hidden\" name=\"prim_key\" value=\"".$prim_key."\" />
				      <center>
				      <button type='submit' name='add_prereq' value='true'>Add Prereq</button>
				      </center>
				      </form>
				      <form action=\"course.php?edit=1&DEPT=".$course['course_dept']."&NUM=".$course['course_num']."\" method=\"post\">
				      <center>
				      <button type='submit' name='add_perm' value='true'>Add Instructor Permission
				      </button>
				      </center>
				      </form>";
}

/*
Function: dropPrereq()
Description: Drop the selected requisite from the database.
*/
function dropPrereq(){
	//Delete the prerequisite from the table
	mysql_query("DELETE FROM requisite WHERE course_dept='".$_POST['prereq_dept']."' AND course_num='".$_POST['prereq_num']."' AND prim_key='".$_POST['prim_key']."' AND req_course_num='".$_POST['req_num']."' AND req_course_dept='".$_POST['req_dept']."';");
}

/*
Function: addPrereq()
Description: Add the selected requisite for the selected course.
*/
function addPrereq(){
	$fail=false;
	//Error message
	if(!mysql_fetch_array(mysql_query("SELECT * FROM course WHERE course_num='".$_POST['prereq_num']."' AND course_dept='".$_POST['prereq_dept']."';"))){
		$fail=true;
		echo "<h2><font color='#FF0000'>You done goofed: Course Does Not Exist</font></h2>";
	}
	//Insert the prerequisite
	if(!$fail)
		mysql_query("INSERT INTO requisite VALUES ('".$_POST['prim_key']."','".$_GET['NUM']."','".$_GET['DEPT']."','".$_POST['prereq_num']."','".$_POST['prereq_dept']."',".(($_POST['grade']=="N/A")?"null":"'".$_POST['grade']."'").", '".(($_POST['concurrent']=="true")?"C":"P")."');");
}

/*
Function: addTeacherPermission()
Description: Add permission of instructor as a prerequisite to the course.
*/
function addTeacherPermission(){
	//Add teacher permission
	mysql_query("INSERT INTO requisite VALUES ('".$_POST['prim_key']."','".$_GET['NUM']."','".$_GET['DEPT']."','100','INST',"."null".", '".(($_POST['concurrent']=="true")?"C":"P")."');");
}

/*
Function: dropTeacherPermission()
Description: Remove permission of instructor as a prerequisite to the course.
*/
function dropTeacherPermission(){
	//Remove teacher permission
	mysql_query("DELETE FROM requisite WHERE course_dept='".$_GET['DEPT']."' AND course_num='".$_GET['NUM']."' AND prim_key='".$_POST['prim_key']."' AND req_course_num='100' AND req_course_dept='INST';");
}

/*
Function: addInstPerm()
Description: Add permission of instructor to a set of requisites for the course.
*/
function addInstPerm(){
	//Maximum grouping
	$prim_key=mysql_fetch_array(mysql_query("SELECT max(prim_key) FROM requisite WHERE course_num='".$_GET['NUM']."' AND course_dept='".$_GET['DEPT']."';"));
	$prim_key=$prim_key[0]+1;
	//Insert into the maximum grouping
	mysql_query("INSERT INTO requisite VALUES ('".$prim_key."','".$_GET['NUM']."','".$_GET['DEPT']."','100','INST',null, '0');");
}

/*
Function: displayCourseFulfillsEdit($course)
Description: Display and edit the courses that the selected course can count for.
*/
function displayCourseFulfillsEdit($course)
{
	//Header
	echo "<table align = \"center\"><tr align = \"center\"><td><b>Counts For</b></td></tr></table>";
	
	//Does this course count for any other course?
	$countsForQuery = mysql_query("SELECT sub_course_dept, sub_course_num FROM counts_for where course_dept = '".$course['course_dept']."' and course_num = '".$course['course_num']."';");
	
	//If it does, display that course
	$i = 0;
	echo "<table align=\"center\"><tr><td>";
	while($countsFor = mysql_fetch_array($countsForQuery))
	{
		if($i != 0 and $i % 4 != 0)
			echo "<b> AND </b>";
		echo "<form style = \"display:inline;\" action=\"course.php?edit=1&DEPT=".$course['course_dept']."&NUM=".$course['course_num']."\" method=\"post\">";
		
		echo "<div style = \"border: 1px solid #000; vertical-align: middle; display: inline-table;\" width = \"20%\" border = \"1\">";
		
		echo "<input type=\"hidden\" name=\"sub_course_dept\" value=\"".$countsFor['sub_course_dept']."\">";
		echo "<input type=\"hidden\" name=\"sub_course_num\" value=\"".$countsFor['sub_course_num']."\">";
		
		echo "<button type='submit' name='delete_counts_for' value='true'>X</button> ".$countsFor['sub_course_dept']." ".$countsFor['sub_course_num'];	
		echo "</div>";	
		echo "</form>";
		$i = $i + 1;
		if($i % 4 == 0)
			echo "</td></tr><tr><td>";
	}
	echo "</td></tr></table>";
	
	//Form to add a counts_for course
	echo "<table align = \"center\"><tr><td>";
	echo "<form action=\"course.php?edit=1&DEPT=".$course['course_dept']."&NUM=".$course['course_num']."\" method=\"post\"><table align = \"center\"><tr align = \"center\">
	      <td><font size=\"-1\"><b>DEPT</b></font></td>
	      <td><font size=\"-1\"><b>NUM</b></font></td></tr><tr>
	      <td>
	      <select name=\"counts_for_dept\">";
	      $departmentQuery = mysql_query("SELECT dept_name FROM department;");
	      while($dept=mysql_fetch_array($departmentQuery)){
	      	if($dept['dept_name']!='INST')
			echo "<option value=\"".$dept['dept_name']."\" >".$dept['dept_name']."</option>";
	      }
	      echo "</select>
	      <td align=\"center\">
	      <input type=\"text\" name=\"counts_for_num\" size=\"3\" value=\"".$_POST['counts_for_num']."\" />
	      </td></tr></table>
	<center><button type='submit' name='add_counts_for' value='true'>Add Counts For</button></center>
	</form>";
}

/*
Function: addContsFor()
Description: Add a selected course as a course the original can count for.
*/
function addCountsFor()
{
	$fail=false;
	//Error message
	if(!mysql_fetch_array(mysql_query("SELECT * FROM course WHERE course_num='".$_POST['counts_for_num']."' AND course_dept='".$_POST['counts_for_dept']."';"))){
		$fail=true;
		echo "<h2><font color='#FF0000'>You done goofed: Course Does Not Exist</font></h2>";
	}
	//Success - add the counts_for course
	if(!$fail)
		mysql_query("INSERT INTO counts_for VALUES ('".$_GET['NUM']."','".$_GET['DEPT']."','".$_POST['counts_for_num']."','".$_POST['counts_for_dept']."');");
}

/*
Function: dropContsFor()
Description: Drops a selected course as a course the original can count for.
*/
function dropCountsFor()
{
	//Delete the counts_for course
	mysql_query("DELETE FROM counts_for WHERE sub_course_dept='".$_POST['sub_course_dept']."' AND sub_course_num='".$_POST['sub_course_num']."' AND course_dept='".$_GET['DEPT']."' AND course_num='".$_GET['NUM']."';");
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
