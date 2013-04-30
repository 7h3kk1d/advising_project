<?php	

/*
Function: degreeChecklist()
Description: Decides on which entity is logged in, allowing for different levels of editablility on the degree checklist. Calls the function to display the checklist.
*/
function degreeChecklist(){

	echo "<center><h1>Degree Checklist</h1></center><hr></hr>";
	
	// Checks to see which entity is logged in: advisor, advisor with advisee, or student
	if(!$_SESSION['advisor']) $CLID = $_SESSION['CLID'];
	elseif(isset($_SESSION['advisee_CLID'])) $CLID = $_SESSION['advisee_CLID'];
	else{
		echo "<center>A student hasn't been selected yet.</center>";
		
		return;
	}

	// Call to start process of displaying checklist
	displayConcentrations($CLID);
}

/*
Function: displayConcentrations($CLID)
Description: Shows the concentrations that the student is working towards, allowing for multiple degree checklists to be available. 
*/
function displayConcentrations($CLID){

	// Gets the degree plans being worked on by the student/advisee
	$concentrationQuery = mysql_query("select distinct plan_name from working_towards W where W.clid = '$CLID' order by plan_name;") or die(mysql_error());

	// If a degree plan was returned
	if(isset($concentrationQuery))
	{
		$i = 0;
		
		// Grab each degree plan and create a javascript function for showing the checklist for that plan
		while ($tempCon = mysql_fetch_array($concentrationQuery)){

			$concentration = $tempCon['plan_name'];
			echo "<font size = \"+1\"><b><a href='javascript:show(\"$i\")'>[+] "."$concentration</a></b></font></br>";

			// Main function for controlling the output of the checklist to the screen
			displayInformation($CLID, $concentration, $i);
			$i++;
		}
	}

	return;
}

/*
Function: displayInformation($CLID, $concentration, $i)
Description: Display the readable version of the degree checklist for a student, calling other functions to handle the display and queries.
*/
function displayInformation($CLID, $concentration, $i){
	
	echo "<div id='$i' style='display:none;'><ul>";

	$curYear = date('y');
	$curMonth = date('m');
	if ($curMonth == 1) $curSemester = 0;
	elseif($curMonth >= 2 and $curMonth <= 5) $curSemester = 1;
	elseif($curMonth == 6) $curSemester = 2;
	elseif($curMonth == 7 or $curMonth == 8) $curSemester = 3;
	elseif($curMonth >= 9 and $curMonth <= 11) $curSemester = 4;
	else $curSemester = 5; 

	// Get the name via the CLID
	$nameQuery = mysql_query("select name from student S where S.clid = '$CLID';") or die(mysql_error());
	$name = mysql_result($nameQuery, 0);
	
	// Small display of information about the student/advisee
	if ($concentration == 'General' or $concentration == 'Scientific Computing' or $concentration == 'Cognitive Science' or $concentration == 'Video Game Design & Development' 
	   	or $concentration == 'Computer Engineering' or $concentration == 'Information Technology')
	echo "<font size = \"5\"><center>Computer Science</center></font>";

	else echo "<font size = \"5\"><center>" . $concentration . "</center></font>";

	echo "<center><u>Student Name:</u> " . $name . "</center>";
	echo "<center><u>Student CLID:</u> " . $CLID . "</center>";
	echo "<center><u>Concentration:</u> " . $concentration . "</center>";
	echo "<center><u>Minimum hours:</u> 124 Hours</center></br>";

	// Get the courses that are required by the degree plan
	$courses = getCourses($concentration);

	// Get the electives that the student/advisee is taking/taken that fit into the degree plan
	$electives = getElectives($CLID, $concentration);

	// Get the labels for the general groups of electives for the degree plan
	$electiveLabels = getElectiveLabels($concentration);

	$courseInformation = array();
	$courseCounter = 0;

	// Creates an array that stores the required courses for the degree plan, along with the grade/credit for the student/advisee, if applicable
	while($tempCourses = mysql_fetch_array($courses))
	{
		$courseInformation[$courseCounter][0] = $tempCourses['course_dept'];
		$courseInformation[$courseCounter][1] = $tempCourses['course_num'];

		$course_dept = $courseInformation[$courseCounter][0];
		$course_num = $courseInformation[$courseCounter][1];

		$hasCreditFor = hasCreditFor($CLID, $course_dept, $course_num);

		if ($hasCreditFor == NULL) $courseInformation[$courseCounter][2] = '-';
		elseif ($hasCreditFor == 1) $courseInformation[$courseCounter][2] = 'CR';
		else $courseInformation[$courseCounter][2] = $hasCreditFor;

		$courseSemYearQuery = mysql_query("select C.semester, C.year from take T, class C where T.clid = '$CLID' and C.course_dept = '$course_dept' and C.course_num = $course_num and (C. year < $curYear or 
			(C.year = $curYear and C.semester <= $curSemester)) and T.course_dept = C.course_dept and T.course_num = C.course_num and T.section_num = C.section_num and T.semester = C.semester and T.year = C.year order 				by T.grade limit 1;") or die(mysql_error());

		$courseSemYear = mysql_fetch_array($courseSemYearQuery);

		echo $courseSemYear['semester'] . $courseSemYear['year'];

		$courseInformation[$courseCounter][3] = $courseSemYear['semester'];
		$courseInformation[$courseCounter][4] = $courseSemYear['year'];

		$courseCounter++;
	}

	$electiveInformation = array();
	$elecCounter = 0;

	// Creates an array that stores the electives the student/advisee has taken for the degree plan, with labels, along with the grade/credit for the student/advisee, if applicable
	while($tempElectives = mysql_fetch_array($electives))
	{
		$electiveInformation[$elecCounter][0] = $tempElectives['course_dept'];
		$electiveInformation[$elecCounter][1] = $tempElectives['course_num'];

		$course_dept = $electiveInformation[$elecCounter][0];
		$course_num = $electiveInformation[$elecCounter][1];
		
		$hasCreditFor = hasCreditFor($CLID, $course_dept, $course_num);

		if ($hasCreditFor == NULL) $electiveInformation[$elecCounter][2] = '-';
		elseif ($hasCreditFor == 1) $electiveInformation[$elecCounter][2] = 'CR';
		else $electiveInformation[$elecCounter][2] = $hasCreditFor;

		$electiveInformation[$elecCounter][3] = $tempElectives['elec_label'];
		$electiveInformation[$elecCounter][4] = $tempElectives['semester'];
		$electiveInformation[$elecCounter][5] = $tempElectives['year'];

		$elecCounter++;
	}

	// Displays the required courses tables
	populateRequiresTables($courseInformation, $courseCounter, $concentration, $curSemester, $curYear);

	// Displays the electives tables
	populateElectiveTables($electiveInformation, $electiveLabels, $elecCounter, $concentration, $curSemester, $curYear);

	echo "</ul></div>";

	return;
}

/*
Function: getCourses($concentration)
Description: Calls a query to get all courses that are required by a particular degree plan, ordering the courses by course department.
*/
function getCourses($concentration){
	
	// Query to get the courses that are required by the degree plan
	$returnCourses = mysql_query("select distinct course_num, course_dept from requires R where R.plan_name = '$concentration' order by course_dept;") or die(mysql_error());
	
	return $returnCourses;
}

/*
Function: getElectives($CLID, $concentration)
Description: Calls a query to get all electives and their labels that are required by a particular degree plan and taken by the student.
*/
function getElectives($CLID, $concentration){

	// Query to get the electives that the student/advisee is taking/taken that fit into the degree plan	
	$returnElectives = mysql_query("select U.course_dept, U.course_num, U.elec_label, U.semester, U.year from ((select T.course_dept, T.course_num, E.elec_label, T.semester, T.year from take T, elective E, fulfills F where 			E.plan_name = '$concentration' and T.clid = '$CLID' and T.course_num = F.course_num and T.course_dept = F.course_dept and E.prim_key = F.prim_key and E.plan_name = F.plan_name) union (select SP.course_dept, 			SP.course_num, E1.elec_label, NULL, NULL from spec_cred SP, elective E1, fulfills F1 where E1.plan_name = '$concentration' and SP.clid = '$CLID' and SP.course_num = F1.course_num and SP.course_dept = F1.course_dept 			and E1.prim_key = F1.prim_key and E1.plan_name = F1.plan_name)) as U order by U.course_dept, U.course_num;") or die(mysql_error());	

	return $returnElectives;
}

/*
Function: getElectiveLabels($concentration)
Description: Calls a query to get all electives that are required by a particular degree plan, ordering the electives by the elective label.
*/
function getElectiveLabels($concentration){

	// Query to get the labels for the general groups of electives for the degree plan
	$returnLabels = mysql_query("select elec_label from elective where plan_name = '$concentration' order by case elec_label when elec_label = 'HIST' then 1 end, elec_label = 'AHBS', elec_label;") or die(mysql_error());

	return $returnLabels;
}

/*
Function: populateRequiresTables($courseInformation, $c)
Description: Displays the required courses for a student and which ones the student has taken/satisfied, separating them by groups based on course department.
*/
function populateRequiresTables($courseInformation, $c, $concentration, $curSemester, $curYear){
	
	$k = 0;

	while ($k < $c){

		// Decides which department the course belongs to
		if ($courseInformation[$k][0] == 'CMPS'){
			echo "</br>Computer Science";
			$whichDept = 'CMPS';
		}
		elseif ($courseInformation[$k][0] == 'EECE'){
			echo "</br>Electrical Engineering";
			$whichDept = 'EECE';
		}
		elseif ($courseInformation[$k][0] == 'ENGL'){
			echo "</br>English";
			$whichDept = 'ENGL';
		}
		elseif ($courseInformation[$k][0] == 'MATH'){
			echo "</br>Mathematics";
			$whichDept = 'MATH';
		}
		elseif ($courseInformation[$k][0] == 'STAT'){ 
			echo "</br>Statistics";
			$whichDept = 'STAT';
		}
		elseif ($courseInformation[$k][0] == 'ACCT'){ 
			echo "</br>Accounting";
			$whichDept = 'ACCT';
		}
		elseif ($courseInformation[$k][0] == 'VIAR'){ 
			echo "</br>Visual Arts";
			$whichDept = 'VIAR';
		}
		elseif ($courseInformation[$k][0] == 'PHYS'){ 
			echo "</br>Physics";
			$whichDept = 'PHYS';
		}

		// Creates table
		echo "<table width = \"50%\" border = \"2\">
		      <tr>
		      <td align = \"center\" width = \"50%\"><font size = \"2\">Course</font></td>
		      <td align = \"center\" width = \"30%\"><font size = \"2\">Completed</font></td>
	      	      <td align = \"center\" width = \"20%\"><font size = \"2\">Grade/Credit</font></td>
		      </tr>";

		// Courses are ordered, so the courses are placed in the corresponding table in an ascending order, with grades and credit reflected
		while ($courseInformation[$k][0] == $whichDept) {

			//if ($courseInformation[$k][3] < $curYear or ($courseInformation[$k][3] = $curYear and $courseInformation[$k][4] <= $curSemester)){

				if ($courseInformation[$k][2] == '-' or $courseInformation[$k][2] == 'F' or $courseInformation[$k][2] == 'D' or $courseInformation[$k][2] == 'I' or $courseInformation[$k][2] == 'W'){ 
		
					echo "<tr><td width = \"50%\"><a href='course.php?NUM=" . $courseInformation[$k][1] . "&DEPT=" . $courseInformation[$k][0] . "'>" . $courseInformation[$k][0] . ' ' 
						. $courseInformation[$k][1] . "</a></td> <td width = \"30%\">No</td> <td width = \"20%\">" . $courseInformation[$k][2] . "</td></tr>";
				}

				else{
					echo "<tr><td width = \"50%\"><a href='course.php?NUM=" . $courseInformation[$k][1] . "&DEPT=" . $courseInformation[$k][0] . "'>" . $courseInformation[$k][0] . ' '
						. $courseInformation[$k][1] . "</a></td> <td width = \"30%\">Yes</td> <td width = \"20%\">" . $courseInformation[$k][2] . "</td></tr>";
				}

				$k++;
			//}
	  	}
		
		// Close the table
		echo "</table>";
	}

	return;
}

/*
Function: populateElectiveTables($electiveInformation, $labels, $e)
Description: Sets up the tables for electives required by the course degree plan, calling on a function to display the electives in the correct tables.
*/
function populateElectiveTables($electiveInformation, $labels, $e, $curSemester, $curYear){

	$whichLabel = ' ';
	$tableOff = 0;
	
	// While there are elective labels to consider
	while($tempLabels = mysql_fetch_array($labels)){
		
		// Controls the table marker if the degree plan has no electives
		$tableOff = 1;
		
		// Displays electives in the corresponsing table if the elective label didn't change from the last iteration
		if ($tempLabels[0] == $whichLabel){
			
			// Fills the tables with the corresponding electives
			displayElectives(&$electiveInformation, $whichLabel, $e, $curSemester, $curYear, 0);

			continue;
		}		
		
		// If a new label is encountered, create a new table (close the old one) and start filling the table with the electives, if applicable
		elseif ($tempLabels[0] != $whichLabel){
			
			if ($whichLabel != ' ') echo "</table>";


			echo "<table width = \"50%\" border = \"2\">
			      <tr>
			      <td align = \"center\" width = \"50%\"><font size = \"2\">Course</font></td>
			      <td align = \"center\" width = \"30%\"><font size = \"2\">Completed</font></td>
		      	      <td align = \"center\" width = \"20%\"><font size = \"2\">Grade/Credit</font></td>
			      </tr>";
		
			// Decides which label the elective belongs to
			if ($tempLabels[0] == 'ARTS'){
				echo "</br>Arts";
				$whichLabel = 'ARTS';
			}
			elseif ($tempLabels[0] == 'BHSC'){
				echo "</br>Behavioral Science";
				$whichLabel = 'BHSC';
			}
			elseif ($tempLabels[0] == 'HIST'){
				echo "</br>History";
				$whichLabel = 'HIST';
			}
			elseif ($tempLabels[0] == 'CMCN'){
				echo "</br>Communication";
				$whichLabel = 'CMCN';
			}
			elseif ($tempLabels[0] == 'SCI'){
				echo "</br>Science";
				$whichLabel = 'SCI';
			}
			elseif ($tempLabels[0] == 'AHBS'){
				echo "</br>Arts/Humanities/Behavior Science";
				$whichLabel = 'AHBS';
			}
			elseif ($tempLabels[0] == 'LIT'){
				echo "</br>Literature";
				$whichLabel = 'LIT';
			}
			elseif ($tempLabels[0] == 'CMPS'){
				echo "</br>Computer Science Electives";
				$whichLabel = 'CMPS';
			}
			elseif ($tempLabels[0] == 'MATH'){
				echo "</br>Mathematics";
				$whichLabel = 'MATH';
			}
			elseif ($tempLabels[0] == 'ANY'){
				echo "</br>Electives";
				$whichLabel = 'ANY';
			}
			elseif ($tempLabels[0] == 'SCIB'){
				echo "</br>Science (Biology)";
				$whichLabel = 'SCIB';
			}
			elseif ($tempLabels[0] == 'ELECT'){
				echo "</br>Linguistic Humanities";
				$whichLabel = 'ELECT';
			}
			elseif ($tempLabels[0] == 'EECE'){
				echo "</br>Electrical Engineering";
				$whichLabel = 'EECE';
			}
			elseif ($tempLabels[0] == 'BUSI'){
				echo "</br>Business";
				$whichLabel = 'BUSI';
			}
			
			// Fills the tables with the corresponding electives
			displayElectives(&$electiveInformation, $whichLabel, $e, $curSemester, $curYear, 0);
		}
	}

	// Closes the last table
	if($tableOff) echo "</table>";

	$hasExtra = 0;

	// Checks for extra electives that weren't placed
	for ($f = 0; $f < $e; $f++){

		if($electiveInformation[$f][0] != NULL) $hasExtra = 1;
	}
	
	// If extra classes exist
	if ($hasExtra){
	
		echo "</br>Extra Courses";
	
		echo "<table width = \"50%\" border = \"2\">
		      <tr>
		      <td align = \"center\" width = \"50%\"><font size = \"2\">Course</font></td>
		      <td align = \"center\" width = \"30%\"><font size = \"2\">Completed</font></td>
	      	      <td align = \"center\" width = \"20%\"><font size = \"2\">Grade/Credit</font></td>
		      </tr>";

		// Places the reamining electives in the 'extra courses' table
		for ($o = 0; $o < $e; $o++){

			if($electiveInformation[$o][0] != NULL){

				$whichLabel = $electiveInformation[$o][3];

				displayElectives(&$electiveInformation, $whichLabel, $e, $curSemester, $curYear, 1);
			}
		}
	
	echo "</table>";

	}

	return;
}

	/* Copies the elective information
	for ($row = 0; $row < $e, $row++){
		for($across = 0; $across < 4; $across++){
			echo $electiveInformation[$row][$across] . " ";
		}
		echo "</br>";
	}*/

/*
Function: displayElectives(&$electiveInformation, $whichDept, $e, $extra)
Description: Displays the required electives for a student and which ones the student has taken/satisfied, separating them by groups based on course department.
*/
function displayElectives(&$electiveInformation, $whichLabel, $e, $curSemester, $curYear, $extra){

	for ($l = 0; $l < $e; $l++){
		
		// If the electives being considered are for the 'extra courses' table and aren't the desired department, skip the iteration
		if ($extra and $electiveInformation[$l][3] != $whichLabel) continue;	
	
		// When the elective label matches the desired label, places the elective into the corresponding table
		if ($electiveInformation[$l][3] == $whichLabel){

			//if ($courseInformation[$k][4] < $curYear or ($courseInformation[$k][4] = $curYear and $courseInformation[$k][5] <= $curSemester)){

				if ($electiveInformation[$l][2] == '-' or $electiveInformation[$l][2] == 'F' or $electiveInformation[$l][2] == 'D' or $electiveInformation[$l][2] == 'W' or $electiveInformation[$l][2] == 'I'){ 

					echo "<tr><td width = \"50%\"><a href='course.php?NUM=" . $electiveInformation[$l][1] . "&DEPT=" . $electiveInformation[$l][0] . "'>" . $electiveInformation[$l][0] . ' ' . 
					     $electiveInformation[$l][1] . "</a></td> <td width = \"30%\">No</td> <td width = \"20%\">" . $electiveInformation[$l][2] . "</td></tr>";
				}

				else{
					echo "<tr><td width = \"50%\"><a href='course.php?NUM=" . $electiveInformation[$l][1] . "&DEPT=" . $electiveInformation[$l][0] . "'>" . $electiveInformation[$l][0] . ' ' . 
					     $electiveInformation[$l][1] . "</a></td> <td width = \"30%\">Yes</td> <td width = \"20%\">" . $electiveInformation[$l][2] . "</td></tr>";
				}

				$copy = array();
			
				// Holds copy of the elective entered into the table
				for ($copyIndex = 0; $copyIndex < 6; $copyIndex++) $copy[$copyIndex] = $electiveInformation[$l][$copyIndex];

				// If two electives match (with different labels), remove duplicates with lower grades
				for ($removeDup = 0; $removeDup < $e; $removeDup++){
				
					if ($electiveInformation[$removeDup][0] == $copy[0] and $electiveInformation[$removeDup][1] == $copy[1] and $electiveInformation[$removeDup][2] <= $copy[2]){  
				
						for ($p = 0; $p < 6; $p++) echo $electiveInformation[$removeDup][$p] = NULL;
					}
				}

				return;
			//}
		}
	}
	
	// If no elective is found to match the label, place a blank template into the table
	if ($whichLabel == 'HIST') echo "<tr><td width = \"50%\">HIST ***</a></td> <td width = \"30%\">No</td> <td width = \"20%\">-</td></tr>";
	elseif ($whichLabel == 'CMCN') echo "<tr><td width = \"50%\">CMCN ***</a></td> <td width = \"30%\">No</td> <td width = \"20%\">-</td></tr>";
	else echo "<tr><td width = \"50%\">**** ***</a></td> <td width = \"30%\">No</td> <td width = \"20%\">-</td></tr>";

	return;
}

?>

<script language type="text/javascript">

/*
Function: show()
//Description: Expand or shrink the element referenced by layer_ref (expands if the current state is "none", shrinks if the current state is "block")
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



