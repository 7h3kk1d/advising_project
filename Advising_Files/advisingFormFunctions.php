<?php
/*
File: advisingFormFunctions.php
Description: Contains all functions for the advising form page
Author: John Tortorich/Kenneth Cross
Date: Wednesday April 27th, 2011
I hereby certify that this page is entirely my own work.
*/

//Used for calculation of student GPA
include("calculations.php");

/*
Function: displayAdvisingForm()
Description: Display the advising form for the logged in student or advisor's chosen student
*/
function displayAdvisingForm(){
	//Header
	echo "<center><h1>Advising Form</h1></center><hr>";

	//Is an advisor logged in with a chosen student?
	if($_SESSION['advisee_CLID'])
		//If so, display that student's advising form
		displayFinalForm($_SESSION['advisee_CLID']);
	else
		//If not, display the advising form for the student logged in
		displayFinalForm($_SESSION['CLID']);
	echo "<center>
	      <form action=\"advisingForm.php?clear=1\" method=\"post\">
	      <button type=\"submit\" value=\"true\">Clear Advising Form</button>
	      </form></center>";
}

/*
Function: displayAdvisingStudentInfoEdit($CLID)
Description: Display the editable version of the advising form with all the fields showing.
*/
function displayAdvisingStudentInfoEdit($CLID){
	//Header
	echo "<center><h1>Advising Form</h1></center><hr>";
	
	//Query to return the student's name and division based on the CLID
	$result = mysql_query("SELECT distinct S.name, S.upper_division FROM student S WHERE S.clid = '$CLID';");
	//Get the student's information
	$row = mysql_fetch_array($result);

	//Initialize the form. Once the data is submitted, the "submit" flag is raised to 1, which triggers an if statement on the advisingForm.php page.
	echo "<form action=\"advisingForm.php?submit=1\" method=\"POST\">";
	//Output of main table containing student name and CLID.
	echo "<center><table width = \"60%\" border = \"1\"><tr><td width = \"40%\" align = \"center\">";
	echo $row['name'] ;
	echo "</td><td width = \"20%\" align = \"center\">";
	echo "$CLID";
	echo "</td></tr>";

	//Table titles residing on the next row
	echo "<tr><td width = \"40%\"><font size = \"-2\"><center>Name";
	echo "</center></font></td><td width = \"20%\"><font size = \"-2\"><center>UL Lafayette CLID";
	echo "</center></font></td></tr></table><br><table width = \"60%\" border = \"1\">";

	//Academic field bulletin for the user to enter. Upon loading, it is filled with whatever value the session variable stores.
	echo "<tr><td width = \"25%\">";
	echo "Effective Bulletin:<input type=\"text\" name=\"bulletin_year\" value=\"".trim($_SESSION['advising']['bulletin_year'])."\" size=9/>"; //Bulletin Form
	echo "</td><td width = \"25%\" align = \"center\">";

	//Output of the main table containing student division, major(s), and GPA
	if($row['upper_division'])
	  echo "Upper Division";	//Upper Division status display
	else echo "Junior Division";
	echo "</td><td width = \"25%\" align = \"center\">";

	//Query to return the student's major
	$result = mysql_query("SELECT D.full_dept_name FROM working_towards W, department D WHERE '$CLID' = W.clid and W.deg_name = D.dept_name;");
	$row=mysql_fetch_array($result);
	echo $row['full_dept_name'];		//display a list of Majors, if necessary
	while($row = mysql_fetch_array($result))
	  echo ", ".$row['full_dept_name'];

	echo "</td><td width = \"25%\" align = \"center\">";
	//Call the calculateGpa function from the calculations.php file
	echo calculateGpa($CLID);		//output GPA here

	//Table titles residing on the next row
	echo "</td></tr><tr><td width = \"25%\"><font size = \"-2\"><center>Bulletin in Effect";
	echo "</center></font></td><td width = \"25%\"><font size = \"-2\"><center>";
	echo "Upper Division/Junior Division";
	echo "</center></font></td><td width = \"25%\"><font size = \"-2\"><center>";
	echo "Major</center></font><td width = \"25%\"><font size = \"-2\"><center>";
	echo "Cumulative GPA</center></font></td></tr></table>";

	//Output of the main table containing student home phone number, cellphone number, and weekly work hours
	echo "<br><table width = \"60%\" border = \"1\"><tr><td width = \"33%\">";
	echo " Home Phone:<input type=\"text\" name=\"homePhone\" value=\"".trim($_SESSION['advising']['homePhone'])."\" size=12/>"; //form for homephone. Upon loading, it is filled with whatever value the session variable stores.
	echo "</td><td width = \"33%\">";
	echo " Cell Phone:<input type=\"text\" name=\"cellPhone\" value=\"".trim($_SESSION['advising']['cellPhone'])."\" size=12/>"; //form for cellphone. Upon loading, it is filled with whatever value the session variable stores.
	echo "</td><td width = \"33%\">";
	echo " Weekly work hours:<input type=\"text\" name=\"wrkHrs\" value=\"".trim($_SESSION['advising']['wrkHrs'])."\" size=3/>";//form Work Hours. Upon loading, it is filled with whatever value the session variable stores.

	//Table titles residing on the next row
	echo "</td></tr><tr><td width = \"33%\"><font size = \"-2\"><center>Home Phone Number";
	echo "</center></font</td><td width = \"33%\"><font size = \"-2\"><center>Cell Phone Number";
	echo "</center></font></td><td width = \"33%\"><font size = \"-2\"><center>";
	echo "Hours Working Weekly</center></font></tr></table><hr><b>";

	//Output of the main table containing the student's chosen semester and year.
	//The semester is stored in the database using the following standard:
	//0 = spring, 1 = summer intercession, 2 = summer, 3 = fall, 4 = winter intercession.
	//The semesters are stored in an array and accessed via this standard.
	$semesters = array("Spring","Summer Intercession","Summer","Fall","Winter Intercession");
	echo "<select name=\"semester\">
	<option ".($_SESSION['advising']['semester']=='3' ? 'selected=\"yes\"' : '')." value=\"3\">Fall</option>
	<option ".($_SESSION['advising']['semester']=='4' ? 'selected=\"yes\"' : '')." value=\"4\">Winter Intercession</option>
	<option ".($_SESSION['advising']['semester']=='0' ? 'selected=\"yes\"' : '')." value=\"0\">Spring</option>
	<option ".($_SESSION['advising']['semester']=='2' ? 'selected=\"yes\"' : '')." value=\"2\">Summer</option>
	<option ".($_SESSION['advising']['semester']=='1' ? 'selected=\"yes\"' : '')." value=\"1\">Summer Intercession</option>";//DD
	//The user must have a value for the year field for the form to be valid.
	echo "</select>Semester, <input type=\"text\" name=\"year\" value=\"".trim($_SESSION['advising']['year'])."\" size=4/>";//form

	//If the user has courses, display them.
	displayCourses();

	//Submit
	echo "<center><input type=\"submit\" value = \"Submit\" /></center>";
	echo "</form>";
}

/*
Function: submitInfo()
Description: Submit the form data from the editable advising form. The user must enter a year/semester combination in order to start adding classes to the form. If the user has not chosen this combination upon submitting the data, an error message is shown and the editable form is re-displayed.
*/
function submitInfo(){
	//Did the submission have a year value?
	if($_POST['year'] == "")
	{
		//If the year field has no value, the form is not valid. Re-display with an error message.
		echo "<font color='#FF0000'><center>You Done Goofed: Must Enter A Year/Semester Combination</center></font>";
		//Is an advisor logged in with a chosen student?
		if($_SESSION['advisee_CLID'])
			//If so, display that student's editable advising form
			displayAdvisingStudentInfoEdit($_SESSION['advisee_CLID']);
		else
			//If not, display the editable advising form for the student logged in
			displayAdvisingStudentInfoEdit($_SESSION['CLID']);
	}
	else
	{
		//If the submission did have a year value, the data is valid. Store the POST values in the session.
		$_SESSION['advising']['semester']=$_POST['semester'];
		$_SESSION['advising']['homePhone']=$_POST['homePhone'];
		$_SESSION['advising']['cellPhone']=$_POST['cellPhone'];
		$_SESSION['advising']['wrkHrs']=$_POST['wrkHrs'];
		$_SESSION['advising']['bulletin_year']=$_POST['bulletin_year'];
		$_SESSION['advising']['year']=$_POST['year'];
		//Display the non-editable advising form.
		displayAdvisingForm();
	}
}

/*
Function: displayFinalForm($CLID)
Description: Display the non-editable version of the advising form with all the fields that have data showing.
*/
function displayFinalForm($CLID){
	//Query to return the student's name and division based on the CLID
	$result = mysql_query("SELECT distinct S.name, S.upper_division FROM student S WHERE S.clid = '$CLID';");
	$row = mysql_fetch_array($result);

	//Initialize the form. Once the data is submitted, the "edit" flag is raised to 1, which triggers an if statement on the advisingForm.php page.
	echo "<form method=\"post\" action=\"advisingForm.php?edit=1\">";
	//Button to edit the student's information
	echo "<table  width = \"100%\"><tr><td align = \"right\"><button type='submit'>Edit Information</button>";
	echo "</td></tr></table>";

	//Output of main table containing student name and CLID.
	echo "<center><table width = \"60%\" border = \"1\"><tr><td width = \"40%\" align = \"center\">";
	echo $row['name'] ;
	echo "</td><td width = \"20%\" align = \"center\">";
	echo "$CLID";
	echo "</td></tr>";

	//Table titles residing on the next row
	echo "<tr><td width = \"40%\"><font size = \"-2\"><center>Name";
	echo "</center></font></td><td width = \"20%\"><font size = \"-2\"><center>UL Lafayette CLID";
	echo "</center></font></td></tr></table><br><table width = \"60%\" border = \"1\"><tr>";

	//Output of the main table containing the (optional) bulletin year
	if($_SESSION['advising']['bulletin_year']!="")
		echo "<td width = \"25%\" align = \"center\">".$_SESSION['advising']['bulletin_year']."</td>";

	//Output of main table containing division, major(s), and GPA.
	echo "<td width = \"25%\" align = \"center\">";
	if($row['upper_division'])
	  echo "Upper Division";	//Upper Division status display
	else echo "Junior Division";
	
	echo "</td><td width = \"25%\" align = \"center\">";
	$result = mysql_query("SELECT D.full_dept_name FROM working_towards W, department D WHERE '$CLID' = W.clid and W.deg_name = D.dept_name;");
	$row=mysql_fetch_array($result);
	echo $row['full_dept_name'];		//display a list of Majors, if necessary
	while($row = mysql_fetch_array($result))
	  echo ", ".$row['full_dept_name'];

	echo "</td><td width = \"25%\" align = \"center\">";
	//Call the calculateGpa function from the calculations.php file
	echo calculateGpa($CLID)."</td></tr><tr>";		//output GPA here
	
	//(Optional) Table title residing on the next row	
	if($_SESSION['advising']['bulletin_year']!="")
		echo "<td width = \"25%\"><font size = \"-2\"><center>Bulletin in Effect</center></font></td>";
	echo "<td width = \"25%\"><font size = \"-2\"><center>";
	//Table titles residing on the next row
	echo "Upper Division/Junior Division";
	echo "</center></font></td><td width = \"25%\"><font size = \"-2\"><center>";
	echo "Major</center></font><td width = \"25%\"><font size = \"-2\"><center>";
	echo "Cumulative GPA</center></font></td></tr></table>";
	echo "<br>";

	//Output of the main table containing student home phone number, cellphone number, and weekly work hours
	//If the following fields are not blank, display them:
	if($_SESSION['advising']['homePhone']!="" or $_SESSION['advising']['cellPhone']!=""or $_SESSION['advising']['wrkHrs']!="")
		echo "<table width = \"60%\" border = \"1\"><tr>";

	if($_SESSION['advising']['homePhone']!="")
		echo "<td width = \"33%\" align = \"center\">".$_SESSION['advising']['homePhone']."</td>";
	if($_SESSION['advising']['cellPhone']!="")
		echo "<td width = \"33%\" align = \"center\">".$_SESSION['advising']['cellPhone']."</td>";
	if($_SESSION['advising']['wrkHrs']!="")
		echo "<td width = \"33%\" align = \"center\">".$_SESSION['advising']['wrkHrs']."</td>";

	if($_SESSION['advising']['homePhone']!="" or $_SESSION['advising']['cellPhone']!="" or $_SESSION['advising']['wrkHrs']!="")
		echo "</tr><tr>";

	if($_SESSION['advising']['homePhone']!="")
		echo "<td width = \"33%\" align = \"center\"><font size = \"-2\">Home Phone Number</font></td>";
	if($_SESSION['advising']['cellPhone']!="")
		echo "<td width = \"33%\" align = \"center\"><font size = \"-2\">Cell Phone Number</font></td>";
	if($_SESSION['advising']['wrkHrs']!="")
		echo "<td width = \"33%\" align = \"center\"><font size = \"-2\">Hours Working Weekly</font></td>";

	if($_SESSION['advising']['homePhone']!="" or $_SESSION['advising']['cellPhone']!="" or $_SESSION['advising']['wrkHrs']!="")
		echo "</tr></table>";

	//Dividing line to deparate student information from term and courses
	echo "<hr><b>";
	if($_SESSION['advising']['semester']!="" and $_SESSION['advising']['year']!="")
	{
		//Output of the main table containing the student's chosen semester and year.
		//The semester is stored in the database using the following standard:
		//0 = spring, 1 = summer intercession, 2 = summer, 3 = fall, 4 = winter intercession.
		//The semesters are stored in an array and accessed via this standard.
		$semesters = array("Spring","Summer Intercession","Summer","Fall","Winter Intercession");
		echo $semesters[$_SESSION['advising']['semester']]." Semester, ".$_SESSION['advising']['year'];
	}
	echo "</form>";
	//If the user has courses, display them.
	displayCourses();
}

/*
Function: displayCourses()
Description: Display the courses that the student has currently chosen for the advising session.
*/
function displayCourses(){
	//Has the user chosen courses?
	if(!empty($_SESSION['advising']['courses']))
	{
		//If so, display the course table header
		echo "</b><br><br><table width = \"60%\" border = \"1\"><tr><td width = \"20%\">";
		echo "Department Abbreviation</td><td width = \"15%\">";
		echo "Course Number</td><td width = \"10%\">Credit";
		echo "</td><td width = \"55%\">Notes</td></tr>";
	
	
	foreach($_SESSION['advising']['courses'] as $val){
	    $result = mysql_query("SELECT C.notes, C.credit_hours FROM course C WHERE C.course_num = '".$val['NUM']."' and C.course_dept = '".$val['DEPT']."';");
	    $row=mysql_fetch_array($result);
	    echo "<tr><td>".$val['DEPT']."</td><td>".$val['NUM']."</td><td>".$row['credit_hours']."</td><td>".$row['notes']."</td></tr>";
	}
	echo "</table></center>";
	}

}

function addAdvising(){
	if(!isset($_SESSION['advising']['courses']))
		$_SESSION['advising']['courses']=array();
	$course=array();
	$course['NUM']=$_POST['NUM'];
	$course['DEPT']=$_POST['DEPT'];
	$_SESSION['advising']['courses'][]=$course;
}

function clearAdvising(){
	$_SESSION['advising']['courses']=array();
	$_SESSION['advising']=array();
}

?>
