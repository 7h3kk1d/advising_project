<?php
/*
File: classFunctions.php
Description: Contains all functions for the class page
Author: Alexander Bandukwala/Kenneth Cross
Date: Wednesday April 22nd, 2011
I hereby certify that this page is entirely my own work.
*/

/*
Function: displayClass($COURSE_DEPT, $COURSE_NUM, $COURSE_SECTION,$COURSE_SEMESTER,$COURSE_YEAR)
Description: Display a class page containing its information
*/
function displayClass($COURSE_DEPT, $COURSE_NUM, $COURSE_SECTION,$COURSE_SEMESTER,$COURSE_YEAR){
	//Query to determine if the class exists
	$class = mysql_fetch_array(mysql_query("SELECT * FROM class where course_num = '$COURSE_NUM' AND course_dept = '$COURSE_DEPT' AND section_num='$COURSE_SECTION' AND semester='$COURSE_SEMESTER' AND year='$COURSE_YEAR';"));

	//Was the class found?
	if($class==null)
		//If not, return an error message
		noClassGoof();
	else{
		//If the class was found, display the page
		displayClassInfo($class);
		displayClassMeetTimes($class);
		displayClassPrerequisites($class);
	}
}

/*
Function: noClassGoof()
Description: Error message if a class isn't found
*/
function noClassGoof(){
	//Error for a class not existing
	echo "<h2><font color='#FF0000'>You Done Goofed: Class Does Not Exist</font></h2>";
}

/*
Function: displayClassInfo($class)
Description: Displays a class's information
*/
function displayClassInfo($class){
	//Used for calculation of student GPA
	include("calculations.php");

	//Calculate the enrollment for the class to be displayed
	$class['enrollment']=calculateNroll($class['section_num'], $class['semester'], $class['year'], $class['course_num'], $class['course_dept']);
	
	//Is an advisor logged in
	if($_SESSION['advisor']==true)
		echo "";
	//Start table for the delete button
	echo "<table align = 'right'><tr><td>";
	//Is an advisor logged in?
	if($_SESSION['advisor']==true)
		//Output the delete button for the class instance
		echo "<form style = \"display:inline;\" method=\"post\" action=\"course.php?DEPT=".$class['course_dept']."&NUM=".$class['course_num']."\">
		      <input type=\"hidden\" name=\"delete_class_dept\" value=\"".$class['course_dept']."\" />
		      <input type=\"hidden\" name=\"delete_class_num\" value=\"".$class['course_num']."\" />
		      <input type=\"hidden\" name=\"delete_class_section_num\" value=\"".$class['section_num']."\" />
		      <input type=\"hidden\" name=\"delete_class_year\" value=\"".$class['year']."\" />
		      <input type=\"hidden\" name=\"delete_class_semester\" value=\"".$class['semester']."\" />
		      <button type='submit' name='delete_class' value='true'>Delete Class</button>
		      </form>
		      <form style = \"display:inline;\" method=\"post\" action=\"class.php?edit=1&DEPT=".$class['course_dept']."&NUM=".$class['course_num']."&SEMESTER=".$class['semester']."&YEAR=".$class['year']."&SECTION=".$class['section_num']."\">";
		 //Output the delete button for editing the class instance
		 echo "<button type='submit'>Edit Class Information</button>
		       </form>";
	echo "</td></tr></table><br>";
	//Output the class title
	echo "<center><h1>".$class['course_dept']." ".$class['course_num']." ".sprintf("%03d",$class['section_num'])." ";
	//Output the class semester
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
	//Output the class year
	echo " ".$class['year'];
	
	//If the class has a title, print it
	if($class['title'])
		echo "<br>".$class['title'];
	echo "</h1></center><hr>";
	
	//Does the class have credit hours or enrollment?
	if(isset($class['credit_hours']) or isset($class['enrollment'])){
		//If it does, output the credit hours and/or enrollment in a table
		echo "<table align = \"center\" width = \"60%\" border = \"1\">
	              <tr align = \"center\">";
		
		if(isset($class['title']))
			echo "<td width = \"20%\"><font size = \"-2\">Title</font></td>";
		if(isset($class['credit_hours']))
			echo "<td width = \"20%\"><font size = \"-2\">Credit Hours</font></td>";
		if(isset($class['enrollment']))
			echo "<td width = \"20%\"><font size = \"-2\">Enrollment</font></td>";
		if(isset($class['max_enrollment']))
			echo "<td width = \"20%\"><font size = \"-2\">Max Enrollment</font></td>";
     		echo "</tr>
	              <tr align = \"center\">";
		if(isset($class['title']))
			echo"<td width = \"20%\">".$class['title']."</td>";
  	 	if(isset($class['credit_hours']))
			echo"<td width = \"20%\">".$class['credit_hours']."</td>";
		if(isset($class['enrollment']))
			echo "<td width = \"20%\">".$class['enrollment']."</td>";
		if(isset($class['max_enrollment']))
			echo "<td width = \"20%\">".$class['max_enrollment']."</td>";
		echo "</tr>
		      </table><br>";
	}
	      
	      
	//If the description, notes, or teacher are set, output them	      
	if(isset($class['description']))
		echo "<b>Description:</b> ".$class['description']."<br>";
	if(isset($class['notes']))
		echo "<b>Notes:</b> ".$class['notes']."<br>";
	if(isset($class['teacher_clid']))
		echo "<b>Teacher:</b> ".$class['teacher_clid']." - ".$class['teacher_name']."<br>";
}

/*
Function: displayClassPrerequisites($class)
Description: Displays a class's prerequisites
*/
function displayClassPrerequisites($class)
{
	//Query to select the prerequisite groupings
	$keys = mysql_query("SELECT distinct prim_key FROM class_requisite where course_num = \"".$class['course_num']."\" and course_dept = \"".$class['course_dept']."\" and semester = \"".$class['semester']."\" and year = \"".$class['year']."\" and section_num = \"".$class['section_num']."\" order by prim_key;");
	//If there is a valid grouping, output the prerequisites
	if($keyArray = mysql_fetch_array($keys)){
		echo "<table align = \"center\"><tr align = \"center\"><td><b>Prerequisites</b></td></tr><tr><td>";
		echo "<div style = \"border: 1px solid #000; vertical-align: middle; display: inline-table;\" width = \"20%\" border = \"1\">";
			//Determine the prerequisites by the grouping
			$requisites = mysql_query("SELECT req_course_num, req_course_dept, grade, type FROM class_requisite where course_num = \"".$class['course_num']."\" and course_dept = \"".$class['course_dept']."\" and semester = \"".$class['semester']."\" and year = \"".$class['year']."\" and section_num = \"".$class['section_num']."\" and prim_key=\"".$keyArray['prim_key']."\" order by course_dept;");

		//Output the prerequisite boxes
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

		//Output the prerequisite boxes
		while($keyArray = mysql_fetch_array($keys)){
			echo " <b>OR </b><div style = \"border: 1px solid #000; vertical-align: middle; display: inline-table;\" width = \"20%\" border = \"1\">";

			$requisites = mysql_query("SELECT req_course_num, req_course_dept, grade, type FROM class_requisite where course_num = \"".$class['course_num']."\" and course_dept = \"".$class['course_dept']."\" and semester = \"".$class['semester']."\" and year = \"".$class['year']."\" and section_num = \"".$class['section_num']."\" and prim_key=\"".$keyArray['prim_key']."\" order by course_dept;");


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
		echo "(*) indicates course can be taken concurrently";
	}
}

/*
Function: displayClassEdit($COURSE_DEPT, $COURSE_NUM, $COURSE_SECTION,$COURSE_SEMESTER,$COURSE_YEAR)
Description: Calls functions to display the editable class page
*/
function displayClassEdit($COURSE_DEPT, $COURSE_NUM, $COURSE_SECTION,$COURSE_SEMESTER,$COURSE_YEAR){
	//Query to determine if the class exists
	$class = mysql_fetch_array(mysql_query("SELECT * FROM class where course_num = '$COURSE_NUM' AND course_dept = '$COURSE_DEPT' AND section_num='$COURSE_SECTION' AND semester='$COURSE_SEMESTER' AND year='$COURSE_YEAR';"));

	//Was the class found?
	if($class==null)
		//If not, return an error message
		noClassGoof();
	else{
		//If the class was found, display the page
		displayClassInfoEdit($class);
		displayClassPrerequisitesEdit($class);
		displayClassMeetTimesEdit($class);
	}
}

/*
Function: displayClassInfoEdit($class)
Description: Displays the actual editable class attributes
*/
function displayClassInfoEdit($class){
	//Form to submit the class data
	echo "<form method=\"post\" action=\"class.php?submit=1&DEPT=".$class['course_dept']."&NUM=".$class['course_num']."&SEMESTER=".$class['semester']."&YEAR=".$class['year']."&SECTION=".$class['section_num']."\">";
	
	//Display semester
	echo "<center><h1>".$class['course_dept']." ".$class['course_num']." ".sprintf("%03d",$class['section_num'])." ";
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
	//Display year
	echo " ".$class['year'];
	
	//If the class has a title, display it
	if($class['title'])
		echo "<br>".$class['title'];
	echo "</h1></center><hr>";
	
	//Table with class title, credit hours, and max enrollment	
	echo "<table align = \"center\" width = \"60%\" border = \"1\">
	      <tr align = \"center\">
	      <td width = \"60%\"><font size = \"-2\">Title</font></td>
              <td width = \"20%\"><font size = \"-2\">Credit Hours</font></td>
              <td width = \"20%\"><font size = \"-2\">Max Enrollment</font></td></tr>
	      <tr align = \"center\">
	      <td width = \"60%\">
              <input type=\"text\" name=\"title\"  value=\"".$class['title']."\" size=30 /></td>
	      <td width = \"20%\">
              <input type=\"text\" name=\"credit_hours\"  value=\"".$class['credit_hours']."\" size=1 /></td>
	      <td width = \"20%\">
	      <input type=\"text\" name=\"max_enrollment\"  value=\"".$class['max_enrollment']."\" size=3 /></td>
	      </tr>
	      </table><br>";
	      
	//Echo the description, notes, teacher CLID, and teacher name
	echo "<b>Description:</b><textarea name=\"description\" cols=50 >".trim($class['description'])."</textarea><br>";
	echo "<b>Notes:</b><textarea name=\"notes\" cols=50 >".trim($class['notes'])."</textarea><br>";
	echo "<b>Teacher CLID:</b> <input type=\"text\" name=\"teacher_clid\" size=7  value=\"".trim($class['teacher_clid'])."\" size=5 /><br>";
	echo "<b>Teacher Name:</b> <input type=\"text\" name=\"teacher_name\"  size=20 value=\"".trim($class['teacher_name'])."\" size=5 /><br>";
	//Save info
	echo "<center><input type=\"submit\" value=\"Save Info\" /></center>";
	echo "</form>";
}

/*
Function: submitData()
Description: Submits the user's data, updates the class table with it
*/
function submitData(){
	//Query to update the class table
	mysql_query("UPDATE class SET title=".((trim($_POST['title'])=="")?'null':"'".mysql_real_escape_string(trim($_POST['title']))."'").", credit_hours=".((trim($_POST['credit_hours'])=="")?'null':"'".mysql_real_escape_string(trim($_POST['credit_hours']))."'").", description=".((trim($_POST['description'])=="")?'null':"'".mysql_real_escape_string(trim($_POST['description']))."'").", notes=".((trim($_POST['notes'])=="")?'null':"'".mysql_real_escape_string(trim($_POST['notes']))."'").", teacher_clid=".((trim($_POST['teacher_clid'])=="")?'null':"'".mysql_real_escape_string(trim($_POST['teacher_clid']))."'").", teacher_name=".((trim($_POST['teacher_name'])=="")?'null':"'".mysql_real_escape_string(trim($_POST['teacher_name']))."'").", max_enrollment='".$_POST['max_enrollment']."' WHERE course_dept='".$_GET['DEPT']."' AND course_num='".$_GET['NUM']."' AND semester='".$_GET['SEMESTER']."' AND section_num='".$_GET['SECTION']."' AND year='".$_GET['YEAR']."';");
}

/*
Function: displayClassMeetTimes($class)
Description: Displays the class's meet times
*/
function displayClassMeetTimes($class){
	//Query to get the class meet times
	$time_query=mysql_query("SELECT * FROM class_meet_times WHERE course_dept='".$class['course_dept']."' AND course_num='".$class['course_num']."' AND section_num='".$class['section_num']."' AND semester='".$class['semester']."' AND year='".$class['year']."';");
	//Does the class have meet times?
	if($time = mysql_fetch_array($time_query)){
		//Output meet times
		echo "<table align = \"center\"><tr align = \"center\"><th colspan=\"2\"><b>Meet Times</b></th></tr>
	      	      <tr align = \"center\">
		      <td>".$time['days']."</td>
		      <td>".$time['meet_time']." - ".$time['end_time']."</td>
		      </tr>";
		while($time = mysql_fetch_array($time_query))
			echo "<tr align = \"center\">
			      <td>".$time['days']."</td>
			      <td>".$time['meet_time']." - ".$time['end_time']."</td>
			      </tr>";		
	      	echo "</table>";
	}
}

/*
Function: displayClassMeetTimesEdit($class)
Description: Displays the editable version of the class's meet times
*/
function displayClassMeetTimesEdit($class){
	//Query to get the class meet times
	$time_query=mysql_query("SELECT * FROM class_meet_times WHERE course_dept='".$class['course_dept']."' AND course_num='".$class['course_num']."' AND section_num='".$class['section_num']."' AND semester='".$class['semester']."' AND year='".$class['year']."';");
	echo "<table align = \"center\"><tr align = \"center\"><th colspan=\"3\"><b>Meet Times</b></th></tr>";
	//Does the class have meet times?
	while($time = mysql_fetch_array($time_query))
			//Output forms to edit meet times
			echo "<tr align = \"center\">
			      <td>
			      <form style = \"display:inline;\" action=\"class.php?edit=1&DEPT=".$class['course_dept']."&NUM=".$class['course_num']."&SEMESTER=".$class['semester']."&YEAR=".$class['year']."&SECTION=".$class['section_num']."\" method=\"post\">
			      <input type=\"hidden\" name=\"delete_time_dept\" value=\"".$class['course_dept']."\" />
			      <input type=\"hidden\" name=\"delete_time_num\" value=\"".$class['course_num']."\" />
			      <input type=\"hidden\" name=\"delete_time_section_num\" value=\"".$class['section_num']."\" />
			      <input type=\"hidden\" name=\"delete_time_year\" value=\"".$class['year']."\" />
			      <input type=\"hidden\" name=\"delete_time_semester\" value=\"".$class['semester']."\" />
			      <input type=\"hidden\" name=\"delete_meet_time\" value=\"".$time['meet_time']."\" />
			      <input type=\"hidden\" name=\"delete_end_time\" value=\"".$time['end_time']."\" />
			      <input type=\"hidden\" name=\"delete_time_days\" value=\"".$time['days']."\" />
			      <button type='submit' name='delete_time' value='true'>X</button>
			      </form>
			      </td>
			      <td>".$time['days']."</td>
			      <td>".$time['meet_time']." - ".$time['end_time']."</td>
			      </tr>";
      	echo "</table>";
      	//Form to edit meet days
	echo "<form style = \"display:inline;\" action=\"class.php?edit=1&DEPT=".$class['course_dept']."&NUM=".$class['course_num']."&SEMESTER=".$class['semester']."&YEAR=".$class['year']."&SECTION=".$class['section_num']."\" method=\"post\">
	      <table align =\"center\">
	      <tr>
	      <th><font size = \"-2\">M</font></th>
	      <th><font size = \"-2\">T</font></th>
	      <th><font size = \"-2\">W</font></th>
	      <th><font size = \"-2\">R</font></th>
	      <th><font size = \"-2\">F</font></th>
	      <th><font size = \"-2\">S</font></th>
	      <th><font size = \"-2\">U</font></th>
	      <th><font size = \"-2\">Start Time</font></th>
	      <th><font size = \"-2\">End Time</font></th>
	      </tr>
	      <tr>
	      <td><input type=\"checkbox\" name=\"day[]\" value=\"M\" /></td>
	      <td><input type=\"checkbox\" name=\"day[]\" value=\"T\" /></td>
	      <td><input type=\"checkbox\" name=\"day[]\" value=\"W\" /></td>
	      <td><input type=\"checkbox\" name=\"day[]\" value=\"R\" /></td>
	      <td><input type=\"checkbox\" name=\"day[]\" value=\"F\" /></td>
	      <td><input type=\"checkbox\" name=\"day[]\" value=\"S\" /></td>
	      <td><input type=\"checkbox\" name=\"day[]\" value=\"U\" /></td>
	      <td>
	      <input type=\"text\" name=\"start_time_hour\" value=\"00\" size=2 />:
	      <input type=\"text\" name=\"start_time_minute\" value=\"00\" size=2 />
	      </td>
	      <td>
	      <input type=\"text\" name=\"end_time_hour\" value=\"00\" size=2 />:
	      <input type=\"text\" name=\"end_time_minute\" value=\"00\" size=2 />
	      </td>
	      <td>
	      <button type='submit' name='add_time' value='true'>Add Time</button>
	      </td>
	      </table>
	      </form>";
	echo"<center><font size = \"-2\">Use 24:00 time</font></center>";
}

/*
Function: dropTime()
Description: Deletes a meeting time for a class
*/
function dropTime(){
	//Delete the class meet times
	mysql_query("DELETE FROM class_meet_times WHERE course_dept='".$_POST['delete_time_dept']."' AND course_num='".$_POST['delete_time_num']."' AND section_num='".$_POST['delete_time_section_num']."' AND year='".$_POST['delete_time_year']."' AND semester='".$_POST['delete_time_semester']."' AND meet_time='".$_POST['delete_meet_time']."' AND end_time='".$_POST['delete_end_time']."' AND days='".$_POST['delete_time_days']."';");
}

/*
Function: addTime()
Description: Adds a meeting time for a class
*/
function addTime(){
	$fail=false;
	//Error message
	if(ereg('[^0-9]',trim($_POST['start_time_hour'])) or ereg('[^0-9]',trim($_POST['start_time_minute']))){
		$fail = true;
		echo "<h2><font color='#FF0000'>You Done Goofed: Start Time Can Only Have Numbers</font></h2>";
	}
	//Error message
	if(ereg('[^0-9]',trim($_POST['end_time_hour'])) or ereg('[^0-9]',trim($_POST['end_time_minute']))){
		$fail = true;
		echo "<h2><font color='#FF0000'>You Done Goofed: End Time Can Only Have Numbers</font></h2>";
	}
	//Error message
	if($_POST['start_time_hour']<0 or $_POST['start_time_hour']>24 or $_POST['start_time_minute']<0 or $_POST['start_time_minute']>59){
		$fail = true;
		echo "<h2><font color='#FF0000'>You Done Goofed: Start Time Not Possible</font></h2>";
	}
	//Success
	if(!$fail){
		$day='';
		foreach($_POST['day'] as $day)
			$days=$days.$day;
		$start_time=trim($_POST['start_time_hour']).":".trim($_POST['start_time_minute']);
		$end_time=trim($_POST['end_time_hour']).":".trim($_POST['end_time_minute']);
		//Insert the time into the table
		mysql_query("INSERT INTO class_meet_times VALUES('".$_GET['SECTION']."', '".$_GET['SEMESTER']."', '".$_GET['YEAR']."', '".$_GET['NUM']."', '".$_GET['DEPT']."', '$days', '$start_time', '$end_time');");
	}
}

/*
Function: displayClassPrerequisitesEdit($class)
Description: Display the editable version of the class prerequisites
*/
function displayClassPrerequisitesEdit($class)
{
	//Query to get the prerequisite groupings
	$keys = mysql_query("SELECT distinct prim_key FROM class_requisite where course_num = \"".$class['course_num']."\" and course_dept = \"".$class['course_dept']."\" and semester = \"".$class['semester']."\" and year = \"".$class['year']."\" and section_num = \"".$class['section_num']."\" order by prim_key;");
	echo "<table align = \"center\"><tr align = \"center\"><td><b>Prerequisites</b></td></tr><tr><td>";

	//Are there prerequisites within the groupings?
	if($keyArray = mysql_fetch_array($keys)){
		//If so, get them
		$requisites = mysql_query("SELECT req_course_num, req_course_dept, grade, type FROM class_requisite where course_num = \"".$class['course_num']."\" and course_dept = \"".$class['course_dept']."\" and semester = \"".$class['semester']."\" and year = \"".$class['year']."\" and section_num = \"".$class['section_num']."\" and prim_key=\"".$keyArray['prim_key']."\" order by course_dept;");
		//Display forms to edit them
		echo "<div style = \"border: 1px solid #000; vertical-align: middle; display: inline-table;\" width = \"20%\" border = \"1\">";
		echo "<form action=\"class.php?edit=1&DEPT=".$class['course_dept']."&NUM=".$class['course_num']."&SEMESTER=".$class['semester']."&YEAR=".$class['year']."&SECTION=".$class['section_num']."\" method=\"post\" style = \"display:inline;\">
		      <input type=\"hidden\" name=\"inst_perm\" value=\"true\" />
		      <input type=\"hidden\" name=\"prim_key\" value=\"".$keyArray['prim_key']."\" />
		      <input type=\"checkbox\" name=\"teacher_permission\" value=\"true\" onclick=\"submit();\"";
		//Get the prerequisite information
		if(mysql_fetch_array(mysql_query("SELECT req_course_num, req_course_dept, grade, type FROM class_requisite where course_num = \"".$class['course_num']."\" and course_dept = \"".$class['course_dept']."\" and req_course_dept='INST' and req_course_num='100' and prim_key=\"".$keyArray['prim_key']."\" order by course_dept and semester = \"".$class['semester']."\" and year = \"".$class['year']."\" and section_num = \"".$class['section_num']."\";")))
			echo "checked=\"yes\"";
		//Output
		echo "> Instructor Permission<br>
		      </form>";
		while($requisiteArray=mysql_fetch_array($requisites)){
			$INST=false;
			if($requisiteArray['req_course_dept'] == "INST" and $requisiteArray['req_course_num'] == "100")
				$INST=true;
			else{
				echo"<form style = \"display:inline;\" action=\"class.php?edit=1&DEPT=".$class['course_dept']."&NUM=".$class['course_num']."&SEMESTER=".$class['semester']."&YEAR=".$class['year']."&SECTION=".$class['section_num']."\" method=\"post\">
				     <input type=\"hidden\" name=\"prereq_dept\" value=\"".$class['course_dept']."\" />
			 	     <input type=\"hidden\" name=\"prereq_num\" value=\"".$class['course_num']."\" />
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
		//Form to add prerequisites
		echo "<form style = \"display:inline;\" method=\"post\" action=\"class.php?edit=1&DEPT=".$class['course_dept']."&NUM=".$class['course_num']."&SEMESTER=".$class['semester']."&YEAR=".$class['year']."&SECTION=".$class['section_num']."\">
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
		//Query to get list of department names
		$departmentQuery = mysql_query("SELECT dept_name FROM department;");
		//Output them for options
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
		//Add prerequisite button
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

		//Output ors
		//FROM HERE ON, LOGIC FOLLOWS AS IN ABOVE PART OF THIS FUNCTION
		while($keyArray = mysql_fetch_array($keys)){
			echo " <b>OR </b><div style = \"border: 1px solid #000; vertical-align: middle; display: inline-table;\" width = \"20%\" border = \"1\">";
			//Are there prerequisites within the groupings?
			$requisites = mysql_query("SELECT req_course_num, req_course_dept, grade, type FROM class_requisite where course_num = \"".$class['course_num']."\" and course_dept = \"".$class['course_dept']."\" and semester = \"".$class['semester']."\" and year = \"".$class['year']."\" and section_num = \"".$class['section_num']."\" and prim_key=\"".$keyArray['prim_key']."\" order by course_dept;");

			echo "<form action=\"class.php?edit=1&DEPT=".$class['course_dept']."&NUM=".$class['course_num']."&SEMESTER=".$class['semester']."&YEAR=".$class['year']."&SECTION=".$class['section_num']."\" method=\"post\" style = \"display:inline;\">
		      <input type=\"hidden\" name=\"inst_perm\" value=\"true\" />
		      <input type=\"hidden\" name=\"prim_key\" value=\"".$keyArray['prim_key']."\" />
		      <input type=\"checkbox\" name=\"teacher_permission\" value=\"true\" onclick=\"submit();\"";
		if(mysql_fetch_array(mysql_query("SELECT req_course_num, req_course_dept, grade, type FROM class_requisite where course_num = \"".$class['course_num']."\" and course_dept = \"".$class['course_dept']."\" and req_course_dept='INST' and req_course_num='100' and prim_key=\"".$keyArray['prim_key']."\" order by course_dept and semester = \"".$class['semester']."\" and year = \"".$class['year']."\" and section_num = \"".$class['section_num']."\";")))
			echo "checked=\"yes\"";
		echo "> Instructor Permission<br>
		      </form>";
			$INST=false;

			while($requisiteArray=mysql_fetch_array($requisites)){
				if($requisiteArray['req_course_dept'] == "INST" and $requisiteArray['req_course_num'] == "100")
					$INST=true;
				else{
					echo"<form style = \"display:inline;\" action=\"class.php?edit=1&DEPT=".$class['course_dept']."&NUM=".$class['course_num']."&SEMESTER=".$class['semester']."&YEAR=".$class['year']."&SECTION=".$class['section_num']."\" method=\"post\">
				     <input type=\"hidden\" name=\"prereq_dept\" value=\"".$class['course_dept']."\" />
			 	     <input type=\"hidden\" name=\"prereq_num\" value=\"".$class['course_num']."\" />
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
			echo "<form style = \"display:inline;\" method=\"post\" action=\"class.php?edit=1&DEPT=".$class['course_dept']."&NUM=".$class['course_num']."&SEMESTER=".$class['semester']."&YEAR=".$class['year']."&SECTION=".$class['section_num']."\">
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
	$prim_key=mysql_fetch_array(mysql_query("SELECT max(prim_key) FROM class_requisite WHERE course_num='".$_GET['NUM']."' AND course_dept='".$_GET['DEPT']."' and semester = \"".$_GET['SEMESTER']."\" and year = \"".$_GET['YEAR']."\" and section_num = '".$_GET['SECTION']."';"));
	$prim_key=$prim_key[0]+1;
	echo "<form style = \"display:inline;\" method=\"post\" action=\"class.php?edit=1&DEPT=".$class['course_dept']."&NUM=".$class['course_num']."&SEMESTER=".$class['semester']."&YEAR=".$class['year']."&SECTION=".$class['section_num']."\">
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
				      <form action=\"class.php?edit=1&DEPT=".$class['course_dept']."&NUM=".$class['course_num']."&SEMESTER=".$class['semester']."&YEAR=".$class['year']."&SECTION=".$class['section_num']."\" method=\"post\">
				      <center>
				      <button type='submit' name='add_perm' value='true'>Add Instructor Permission
				      </button>
				      </center>
				      </form>";
		echo "<center><font size=\"-2\">(*) indicates course can be taken concurrently</font></center>";
		echo "</div>
		      <br>";
}

/*
Function: dropPrereq()
Description: Function to drop prerequisites
*/
function dropPrereq(){
	//Query to drop a prerequisite
	mysql_query("DELETE FROM class_requisite WHERE course_dept='".$_GET['DEPT']."' AND course_num='".$_GET['NUM']."' AND prim_key='".$_POST['prim_key']."' AND req_course_num='".$_POST['req_num']."' AND req_course_dept='".$_POST['req_dept']."' and semester = \"".$_GET['SEMESTER']."\" and year = \"".$_GET['YEAR']."\" and section_num = '".$_GET['SECTION']."';");
}

/*
Function: addPrereq()
Description: Function to add prerequisites
*/
function addPrereq(){
	//If the course exists, insert it. Otherwise, display an error message.
	$fail=false;
	if(!mysql_fetch_array(mysql_query("SELECT * FROM course WHERE course_num='".$_POST['prereq_num']."' AND course_dept='".$_POST['prereq_dept']."';"))){
		$fail=true;
		echo "<h2><font color='#FF0000'>You done goofed: Course Does Not Exist</font></h2>";
	}
	if(!$fail)
		mysql_query("INSERT INTO class_requisite VALUES ('".$_POST['prim_key']."','".$_GET['SECTION']."','".$_GET['SEMESTER']."','".$_GET['YEAR']."','".$_GET['NUM']."','".$_GET['DEPT']."','".$_POST['prereq_num']."','".$_POST['prereq_dept']."',".(($_POST['grade']=="N/A")?"null":"'".$_POST['grade']."'").", '".(($_POST['concurrent']=="true")?"C":"P")."');");
}

/*
Function: addTeacherPermission()
Description: Adds teacher permission as a prerequisite
*/
function addTeacherPermission(){
	//Insert teacher permission
	mysql_query("INSERT INTO class_requisite VALUES ('".$_POST['prim_key']."','".$_GET['SECTION']."','".$_GET['SEMESTER']."','".$_GET['YEAR']."','".$_GET['NUM']."','".$_GET['DEPT']."','100','INST',"."null".", '"."P"."');");
}

/*
Function: dropTeacherPermission()
Description: Removes teacher permission as a prerequisite
*/
function dropTeacherPermission(){
	//Delete teacher premission
	mysql_query("DELETE FROM class_requisite WHERE course_dept='".$_GET['DEPT']."' and semester = \"".$_GET['SEMESTER']."\" and year = \"".$_GET['YEAR']."\" and section_num = '".$_GET['SECTION']."' AND course_num='".$_GET['NUM']."' AND prim_key='".$_POST['prim_key']."' AND req_course_num='100' AND req_course_dept='INST';");
}

/*
Function: addInstPerm()
Description: Adds instructor permission as a prerequisite
*/
function addInstPerm(){
	//Gets the prerequisite groupings
	$prim_key=mysql_fetch_array(mysql_query("SELECT max(prim_key) FROM class_requisite WHERE course_num='".$_GET['NUM']."' AND course_dept='".$_GET['DEPT']."' and semester = \"".$_GET['SEMESTER']."\" and year = \"".$_GET['YEAR']."\" and section_num = '".$_GET['SECTION']."';"));
	$prim_key=$prim_key[0]+1;
	//Insert the prerequisite
	mysql_query("INSERT INTO class_requisite VALUES ('".$prim_key."','".$_GET['SECTION']."','".$_GET['SEMESTER']."','".$_GET['YEAR']."','".$_GET['NUM']."','".$_GET['DEPT']."','100','INST',"."null".", '"."P"."');");
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
