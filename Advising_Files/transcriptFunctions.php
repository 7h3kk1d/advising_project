<?php
/*
File: transcriptFunctions.php
Description: 
Author: Ross Caubarreaux
Date: Wednesday April 24st, 2011
We hereby certify that this code on my own work.
*/

/*
Function: displayTranscript()
Description: Main function to display all of the transcript information
*/
function displayTranscript()
{
	include('calculations.php');
	databaseConnect();
	echo "<center><h1>Unofficial<br>Transcript</h1></center>";
	if($_SESSION['advisor']) $clid = $_SESSION['advisee_CLID'];
	else $clid = $_SESSION['CLID'];
	
	$info = getInfo($clid);
	$row = mysql_fetch_assoc($info);

	echo "<center><b>Name: </b>".$row['name']." (".$clid.")</center><hr><br>";
	echo "<center style = 'text-decoration:underline'><b>Undergraduate Totals</b></center><br>";

//Undergraduate totals
	echo "<table width = '100%'>";
	echo "<tr><td width = '38%'><b>Registered Hours</b></td>";
	echo "<td width = '12%'>".calculateRegisteredHours($clid)."</td></td>";
	echo "<td width = '30%'><b>Completed Hours</b></td>";
	echo "<td width = '10%'>".calculateCompletedHours($clid)."</td></tr>";
	echo "<tr><td><b>Cumulative Hours Earned</b></td><td>".calculateHours($clid)."</td></td>";
	echo "<td><b>Cumulative GPA Hours</b></td><td>".calculateGPAHours($clid)."</td></tr>";
	echo "<tr><td><b>Cumulative Quality Points</b></td><td>".calculateQualityPoints($clid)."</td>";
	echo "<td><b>Cumulative GPA</b></td><td>".calculateGpa($clid)."</td></tr>";
	echo "</table><hr><br>";
	$result_year = mysql_query("SELECT DISTINCT year,semester FROM take WHERE clid = '$clid' ORDER BY year,semester;");
	while($result = mysql_fetch_array($result_year)){
		displayTerm($clid,$result);
	}// End While
}
/*
Function: displayTerm()
Description: Displays all courses taken and repeated by the corresponding clid.
*/ 
function displayTerm($clid,$result){
//Semester listings
	echo "<table>";
	echo "<tr><td style='text-align:right;padding-right:10px' width='250'><b>Term:</b></td>";
	echo "<td style='padding-left:40px'>".transSeme($result)." ".$result['year']."</td></tr>";	
	echo "<tr><td style='text-align:right;padding-right:10px' width='250'><b>Academic Level:</b></td>";
	echo "<td style='padding-left:40px'>UN</td></tr>";
	echo "</table>";

	echo "<table width ='100%'>";
	echo "<tr width='200px'><td style='text-decoration:underline'><b>Course</b></td>";
	echo "<td width='55%' style='text-decoration:underline;text-align:center'><b>Title</b></td>";
	echo "<td width='70px' style='text-align:center'><b>Credit <font style='text-decoration:underline'>Hours</font></b></td>";
	echo "<td style='text-decoration:underline;text-align:center'><b>Grade</b></td>";
	echo "<td style='text-decoration:underline'><b>Repeat</b></td></tr>";
	displayClassesTaken($clid,$result);	// Display courses taken
	// Display Term Total info
	echo "</table>";
	echo "<table width = '100%'>";
	echo "<tr><td width='35%'><b>Term Hours Earned</b></td>";
	echo "<td width='10%'>".calculateTermHours($clid,$result['semester'],$result['year'])."</td>"; 
	echo "<td width='30%'><b>Term GPA Hours</b></td>";  
	echo "<td>".calculateTermGPAHours($clid,$result['semester'],$result['year'])."</td></tr>"; 
	echo "<tr><td><b>Term Quality Points</b></td>";
	echo "<td width='15%'>".calculateTermQualityPoints($clid,$result['semester'],$result['year'])."</td>"; 
	echo "<td><b>Term GPA</b></td>";  
	echo "<td>".calculateTermGPA($clid,$result['semester'],$result['year'])."</td>"; 
	echo "</tr>";
	echo "</table><br><hr>";
}
/*
Function: checkRepeat()
Description: Checks all multiple instances of a course and mark it as repeated with an 'R'. 
*/ 
function checkRepeat($clid,$num,$class){
	$count = mysql_query("SELECT COUNT(*) FROM take T WHERE T.clid = '$clid' AND T.course_num = '$num' AND T.course_dept = '$class'");
	$count = mysql_fetch_array($count);
	if($count[0] > 1)
		return "R"; // Return "R" meaning class was repeated.
}
/*
Function: transSeme()
Description: Converts semester code(0-4) to corresponding english.
	0 - Spring. 1 - Summer Intercession, 2 - Summer, 3 - Fall, 4 - Winter Intercession
*/ 
function transSeme($result){
	switch($result['semester']){
		case 0:
			return 'Spring';
		break;
		case 1:
			return 'Summer Intercession';
		break;
		case 2;
			return 'Summer';
		break;
		case 3:
			return 'Fall';
		break;
		case 4:
			return 'Winter Intercession';
		break;
	}// End switch case

}
/*
Function: getInfo()
Description: Gather all info for a student by the corresponding clid and return as $result.
*/ 
function getInfo($clid)
{
	$query = "SELECT * FROM student where clid='$clid'";
    	$result = mysql_query($query) or die(mysql_error());

    	return $result; 
}
/*
Function: displayClassesTaken()
Description: Finds all classes taken by the corresponding clid, year, semester and displays them out.
*/ 
function displayClassesTaken($clid,$result){
	$result = mysql_query("SELECT T.grade,C.credit_hours,C.title,T.course_num,T.course_dept FROM course C, take T WHERE C.course_num = 	T.course_num AND C.course_dept = T.course_dept AND T.clid= '$clid' AND T.year = ".$result['year']." AND T.semester = ".$result['semester']."");

	while ($row = mysql_fetch_array($result)) {
		echo "<tr><td>".$row['course_dept']." ";
		echo $row['course_num']."</td>";
		echo "<td>".$row['title']."</td>";
		echo "<td style='text-align:center'>".$row['credit_hours']."</td>";
		echo "<td style='text-align:center'>".$row['grade']."</td>";
		echo "<td style='text-align:center'>".checkRepeat($clid,$row['course_num'],$row['course_dept'])."</td>";	
		echo "</tr>";
	}// End while
}

?>

