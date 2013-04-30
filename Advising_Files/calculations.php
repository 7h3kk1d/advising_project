<?php
/*
File: advisingFormFunctions.php
Description: Contains all functions for the advising form page
Author: Daniel Hefner/John Tortorich/Jeffery Wichers
Date: Saturday April 23rd, 2011
I hereby certify that this page is entirely my own work.
*/

/*
Function: calculateAct($clid)
Description: Calculate and return a student's composite ACT score
*/
function calculateAct($clid){
  //Query to get the student's individual ACT scores
  $result = mysql_query("Select S.act_english, S.act_reading, S.act_math, S.act_science
             FROM student S
             WHERE S.clid = '$clid';") or die(mysql_error());
  $row = mysql_fetch_array($result);
  //Add the results of the query and divide by 4
	if (!isset($row['act_english']) || !isset($row['act_reading']) || !isset($row['act_math']) || !isset($row['act_science']))
		return 'N/A';
  $tempcomp = ($row['act_english'] + $row['act_reading'] + $row['act_math'] + $row['act_science'])/4.0;
  //Round to the nearest integer
  $tempcomp = round($tempcomp);
  return $tempcomp;
 
}

/*
Function: calculateNroll($section_num, $semester, $year, $course_num, $course_dept)
Description: Returns the number of students that were enrolled in a particular section of a course on a particular semester of a particular year
*/
function calculateNroll($section_num, $semester, $year, $course_num, $course_dept){
    //Query to get the number of students
    $result = mysql_query("Select count(*) FROM take T WHERE '$section_num' = T.section_num and '$semester'=T.semester and '$year'=T.year and '$course_num'=T.course_num and '$course_dept'=T.course_dept;") or die(mysql_error());
      $row = mysql_fetch_array($result);
      return $row['count(*)'];
}

/*
Function: calculateGpa($clid)
Description: Function to calculate and return a student's GPA
*/
function calculateGpa($clid){
  //Initialize the temporary variables
  $tempgpa=0;
  $chours=0;
  //Query to return the student's grades and credit hours
  $result = mysql_query("Select grade, sum(credit_hours) FROM(( Select grade, credit_hours FROM take T, class Cl WHERE T.grade IS NOT NULL and T.grade<>'I' and T.grade <> 'CR' and T.grade <> 'NC' and T.grade <> 'W' and T.clid = '$clid' and Cl.section_num = T.section_num and Cl.semester = T.semester and Cl.year = T.year and Cl.course_num = T.course_num and Cl.course_dept = T.course_dept and Cl.credit_hours IS NOT NULL) UNION ALL (Select S.grade, Co.credit_hours FROM spec_cred S, course Co WHERE S.grade IS NOT NULL and S.grade <> 'I' and S.grade <> 'CR' and S.grade <> 'W' and S.grade <> 'NC'  and !S.transfer and S.clid = '$clid' and S.course_dept = Co.course_dept and S.course_num = Co.course_num) UNION ALL (Select T.grade,Co.credit_hours FROM take T,course Co WHERE T.grade IS NOT NULL and T.grade<>'I' and T.grade <> 'NC' and T.grade <> 'W' and T.grade <> 'CR' and T.clid = '$clid' and T.course_dept = Co.course_dept and T.course_num = Co.course_num and NOT EXISTS(select * from class Cl WHERE Cl.course_dept = Co.course_dept and Cl.course_num = Co.course_num and Cl.credit_hours IS NOT NULL))) AS result GROUP BY grade;") or die(mysql_error());
  //Calculate the GPA
  while($row = mysql_fetch_array($result)){
    //Is the grade an A?
    if($row['grade'] == 'A')
      //If so, increment tempgpa by four times the sum of the credit hours
      $tempgpa+=4*$row['sum(credit_hours)'];
    //Is the grade a B?
    else if ($row['grade']=='B')
      //If so, increment tempgpa by 3 times the sum of the credit hours
      $tempgpa+=3*$row['sum(credit_hours)'];
    //Is the grade a C?
    else if ($row['grade']=='C')
      //If so, increment tempgpa by 2 times the sum of the credit hours
      $tempgpa+=2*$row['sum(credit_hours)'];
    //Is the grade a D?
    else if ($row['grade']=='D')
      //If so, increment tempgpa by the sum of the credit hours
      $tempgpa+=1*$row['sum(credit_hours)'];
    //Increment chours by the sum of the credit hours
    $chours+=$row['sum(credit_hours)'];
  }
  //Divide tempgpa by chours to get the student's GPA
	if($chours == 0) return 0;
  	else return round($tempgpa/$chours,2);
}

/*
Function: calculateHours($clid)
Description: Function to calculate and return a student's earned credit hours
*/
function calculateHours($clid){
	$result = mysql_query("Select sum(credit_hours) FROM(( Select credit_hours FROM take T, class Cl WHERE T.grade IS NOT NULL and T.grade<>'I' and T.grade <> 'NC' and T.grade<>'F' and T.grade <> 'W' and T.clid = '$clid' and Cl.section_num = T.section_num and Cl.semester = T.semester and Cl.year = T.year and Cl.course_num = T.course_num and Cl.course_dept = T.course_dept and Cl.credit_hours IS NOT NULL) UNION ALL (Select Co.credit_hours FROM spec_cred S, course Co WHERE S.grade IS NOT NULL and S.grade <> 'I' and S.grade <> 'NC' and S.grade <> 'F' and S.grade <> 'W' and S.clid = '$clid' and S.course_dept = Co.course_dept and S.course_num = Co.course_num) UNION ALL (Select Co.credit_hours FROM take T,course Co WHERE T.grade IS NOT NULL and T.grade<>'I' and T.grade <> 'NC' and T.grade<>'F' and T.grade <> 'W' and T.clid = '$clid' and T.course_dept = Co.course_dept and T.course_num = Co.course_num and NOT EXISTS(select * from class Cl WHERE Cl.course_dept = Co.course_dept and Cl.course_num = Co.course_num and Cl.credit_hours IS NOT NULL))) AS result;") or die(mysql_error());
	$hours = mysql_result($result,0);
	if($hours == NULL) return 0;
	else return $hours;
}

/*
Function: calculateGPAHours($clid)
Description: Function to calculate and return a student's credit hours that affect the GPA
*/
function calculateGPAHours($clid){
	$result = mysql_query("Select sum(credit_hours) FROM(( Select credit_hours FROM take T, class Cl WHERE T.grade IS NOT NULL and T.grade<>'I' and T.grade <> 'NC' and T.grade <> 'CR' and T.grade <> 'W' and T.clid = '$clid' and Cl.section_num = T.section_num and Cl.semester = T.semester and Cl.year = T.year and Cl.course_num = T.course_num and Cl.course_dept = T.course_dept and Cl.credit_hours IS NOT NULL) UNION ALL (Select Co.credit_hours FROM spec_cred S, course Co WHERE S.grade IS NOT NULL and S.grade <> 'I' and S.grade <> 'NC' and S.grade <> 'CR' and S.grade <> 'W' and !S.transfer and S.clid = '$clid' and S.course_dept = Co.course_dept and S.course_num = Co.course_num) UNION ALL (Select Co.credit_hours FROM take T,course Co WHERE T.grade IS NOT NULL and T.grade<>'I' and T.grade <> 'NC' and T.grade <> 'CR' and T.grade <> 'W' and T.clid = '$clid' and T.course_dept = Co.course_dept and T.course_num = Co.course_num and NOT EXISTS(select * from class Cl WHERE Cl.course_dept = Co.course_dept and Cl.course_num = Co.course_num and Cl.credit_hours IS NOT NULL))) AS result;") or die(mysql_error());
	$hours = mysql_result($result,0);
	if($hours == NULL) return 0;
	else return $hours;
}

/*
Function: calculateQualityPoints($clid)
Description: Function to calculate and return a student's total quality points
*/
function calculateQualityPoints($clid){
  //Initialize the temporary variables
  $totalhours=0;
  //Query to return the student's grades and credit hours
  $result = mysql_query("Select grade, sum(credit_hours) FROM(( Select grade, credit_hours FROM take T, class Cl WHERE T.grade IS NOT NULL and T.grade<>'I' and T.grade <> 'CR' and T.grade <> 'NC' and T.grade <> 'W' and T.clid = '$clid' and Cl.section_num = T.section_num and Cl.semester = T.semester and Cl.year = T.year and Cl.course_num = T.course_num and Cl.course_dept = T.course_dept and Cl.credit_hours IS NOT NULL) UNION ALL (Select S.grade, Co.credit_hours FROM spec_cred S, course Co WHERE S.grade IS NOT NULL and S.grade <> 'I' and S.grade <> 'CR' and S.grade <> 'NC' and S.grade <> 'W' and !S.transfer and S.clid = '$clid' and S.course_dept = Co.course_dept and S.course_num = Co.course_num) UNION ALL (Select T.grade,Co.credit_hours FROM take T,course Co WHERE T.grade IS NOT NULL and T.grade<>'I' and T.grade <> 'NC' and T.grade <> 'W' and T.grade <> 'CR' and T.clid = '$clid' and T.course_dept = Co.course_dept and T.course_num = Co.course_num and NOT EXISTS(select * from class Cl WHERE Cl.course_dept = Co.course_dept and Cl.course_num = Co.course_num and Cl.credit_hours IS NOT NULL))) AS result GROUP BY grade;") or die(mysql_error());
  while($row = mysql_fetch_array($result)){
    //Is the grade an A?
    if($row['grade'] == 'A')
      //If so, increment totalhours by four times the sum of the credit hours
      $totalhours+=4*$row['sum(credit_hours)'];
    //Is the grade a B?
    else if ($row['grade']=='B')
      //If so, increment totalhours by 3 times the sum of the credit hours
      $totalhours+=3*$row['sum(credit_hours)'];
    //Is the grade a C?
    else if ($row['grade']=='C')
      //If so, increment totalhours by 2 times the sum of the credit hours
      $totalhours+=2*$row['sum(credit_hours)'];
    //Is the grade a D?
    else if ($row['grade']=='D')
      //If so, increment totalhours by the sum of the credit hours
      $totalhours+=1*$row['sum(credit_hours)'];
  }
  return $totalhours;
}

/*
Function: calculateTermGpa($clid,$semester,$year)
Description: Function to calculate and return a student's GPA
*/
function calculateTermGpa($clid,$semester,$year){
  //Initialize the temporary variables
  $tempgpa=0;
  $chours=0;
  //Query to return the student's grades and credit hours for a semester
  $result = mysql_query("Select grade, sum(credit_hours) FROM(( Select grade, credit_hours FROM take T, class Cl WHERE T.grade IS NOT NULL and T.grade<>'I' and T.grade <> 'CR' and T.grade <> 'NC' and T.grade <> 'W' and T.clid = '$clid' and Cl.section_num = T.section_num and Cl.semester = T.semester and Cl.year = T.year and Cl.course_num = T.course_num and Cl.course_dept = T.course_dept and Cl.credit_hours IS NOT NULL and T.semester='$semester' and T.year='$year') UNION ALL (Select T.grade,Co.credit_hours FROM take T,course Co WHERE T.grade IS NOT NULL and T.grade<>'I' and T.grade <> 'NC' and T.grade <> 'W' and T.grade <> 'CR' and T.clid = '$clid' and T.course_dept = Co.course_dept and T.course_num = Co.course_num and T.semester='$semester' and T.year='$year' and NOT EXISTS(select * from class Cl WHERE Cl.course_dept = Co.course_dept and Cl.course_num = Co.course_num and Cl.credit_hours IS NOT NULL))) AS result GROUP BY grade;") or die(mysql_error());
  //Calculate the GPA
  while($row = mysql_fetch_array($result)){
    //Is the grade an A?
    if($row['grade'] == 'A')
      //If so, increment tempgpa by four times the sum of the credit hours
      $tempgpa+=4*$row['sum(credit_hours)'];
    //Is the grade a B?
    else if ($row['grade']=='B')
      //If so, increment tempgpa by 3 times the sum of the credit hours
      $tempgpa+=3*$row['sum(credit_hours)'];
    //Is the grade a C?
    else if ($row['grade']=='C')
      //If so, increment tempgpa by 2 times the sum of the credit hours
      $tempgpa+=2*$row['sum(credit_hours)'];
    //Is the grade a D?
    else if ($row['grade']=='D')
      //If so, increment tempgpa by the sum of the credit hours
      $tempgpa+=1*$row['sum(credit_hours)'];
    //Increment chours by the sum of the credit hours
    $chours+=$row['sum(credit_hours)'];
  }
  //Divide tempgpa by chours to get the student's GPA
	if($chours == 0) return 0;
  	else return round($tempgpa/$chours,2);
}

/*
Function: calculateTermHours($clid,$semester,$year)
Description: Function to calculate and return a student's earned credit hours for a semester
*/
function calculateTermHours($clid,$semester,$year){
	$result = mysql_query("Select sum(credit_hours) FROM(( Select credit_hours FROM take T, class Cl WHERE T.grade IS NOT NULL and T.grade<>'I' and T.grade<>'F' and T.grade <> 'NC' and T.grade <> 'W' and T.clid = '$clid' and Cl.section_num = T.section_num and Cl.semester = T.semester and Cl.year = T.year and Cl.course_num = T.course_num and Cl.course_dept = T.course_dept and Cl.credit_hours IS NOT NULL and T.semester='$semester' and T.year='$year') UNION ALL (Select Co.credit_hours FROM take T,course Co WHERE T.grade IS NOT NULL and T.grade<>'I' and T.grade <> 'NC' and T.grade<>'F' and T.grade <> 'W' and T.clid = '$clid' and T.course_dept = Co.course_dept and T.course_num = Co.course_num and T.semester='$semester' and T.year='$year' and NOT EXISTS(select * from class Cl WHERE Cl.course_dept = Co.course_dept and Cl.course_num = Co.course_num and Cl.credit_hours IS NOT NULL))) AS result;") or die(mysql_error());
	$hours = mysql_result($result,0);
	if($hours == NULL) return 0;
	else return $hours;
}

/*
Function: calculateTermGPAHours($clid,$semester,$year)
Description: Function to calculate and return a student's credit hours that affect the GPA for a semester
*/
function calculateTermGPAHours($clid,$semester,$year){
	$result = mysql_query("Select sum(credit_hours) FROM(( Select credit_hours FROM take T, class Cl WHERE T.grade IS NOT NULL and T.grade<>'I' and T.grade <> 'NC' and T.grade <> 'CR' and T.grade <> 'W' and T.clid = '$clid' and Cl.section_num = T.section_num and Cl.semester = T.semester and Cl.year = T.year and Cl.course_num = T.course_num and Cl.course_dept = T.course_dept and Cl.credit_hours IS NOT NULL  and T.semester='$semester' and T.year='$year') UNION ALL (Select Co.credit_hours FROM take T,course Co WHERE T.grade IS NOT NULL and T.grade<>'I' and T.grade <> 'NC' and T.grade <> 'CR' and T.grade <> 'W' and T.clid = '$clid' and T.course_dept = Co.course_dept and T.course_num = Co.course_num and T.semester='$semester' and T.year='$year' and NOT EXISTS(select * from class Cl WHERE Cl.course_dept = Co.course_dept and Cl.course_num = Co.course_num and Cl.credit_hours IS NOT NULL))) AS result;") or die(mysql_error());
	$hours = mysql_result($result,0);
	if($hours == NULL) return 0;
	else return $hours;
}

/*
Function: calculateTermQualityPoints($clid,$semester,$year)
Description: Function to calculate and return a student's total quality points for a semester
*/
function calculateTermQualityPoints($clid,$semester,$year){
  //Initialize the temporary variables
  $totalhours=0;
  //Query to return the student's grades and credit hours
  $result = mysql_query("Select grade, sum(credit_hours) FROM(( Select grade, credit_hours FROM take T, class Cl WHERE T.grade IS NOT NULL and T.grade<>'I' and T.grade <> 'CR' and T.grade <> 'NC' and T.grade <> 'W' and T.clid = '$clid' and Cl.section_num = T.section_num and Cl.semester = T.semester and Cl.year = T.year and Cl.course_num = T.course_num and Cl.course_dept = T.course_dept and Cl.credit_hours IS NOT NULL and T.semester='$semester' and T.year='$year') UNION ALL (Select T.grade,Co.credit_hours FROM take T,course Co WHERE T.grade IS NOT NULL and T.grade<>'I' and T.grade <> 'NC' and T.grade <> 'W' and T.grade <> 'CR' and T.clid = '$clid' and T.course_dept = Co.course_dept and T.course_num = Co.course_num and T.semester='$semester' and T.year='$year' and NOT EXISTS(select * from class Cl WHERE Cl.course_dept = Co.course_dept and Cl.course_num = Co.course_num and Cl.credit_hours IS NOT NULL))) AS result GROUP BY grade;") or die(mysql_error());
  //Find the quality points per class
  while($row = mysql_fetch_array($result)){
    //Is the grade an A?
    if($row['grade'] == 'A')
      //If so, increment totalhours by four times the sum of the credit hours
      $totalhours+=4*$row['sum(credit_hours)'];
    //Is the grade a B?
    else if ($row['grade']=='B')
      //If so, increment totalhours by 3 times the sum of the credit hours
      $totalhours+=3*$row['sum(credit_hours)'];
    //Is the grade a C?
    else if ($row['grade']=='C')
      //If so, increment totalhours by 2 times the sum of the credit hours
      $totalhours+=2*$row['sum(credit_hours)'];
    //Is the grade a D?
    else if ($row['grade']=='D')
      //If so, increment totalhours by the sum of the credit hours
      $totalhours+=1*$row['sum(credit_hours)'];
  }
  return $totalhours;
}

/*
Function: calculateRegisteredHours($CLID)
Description: Function to calculate how many hours of classes the student has registered for and has gotten a grade.
*/
function calculateRegisteredHours($clid){
	//Query to return the number of hours the student is regustered for
	$result = mysql_query("Select sum(credit_hours) FROM(( Select credit_hours FROM take T, class Cl WHERE T.grade is not NULL and T.clid = '$clid' and Cl.section_num = T.section_num and Cl.semester = T.semester and Cl.year = T.year and Cl.course_num = T.course_num and Cl.course_dept = T.course_dept and Cl.credit_hours IS NOT NULL) UNION ALL (Select Co.credit_hours FROM take T,course Co WHERE T.grade is not NULL and T.clid = '$clid' and T.course_dept = Co.course_dept and T.course_num = Co.course_num and NOT EXISTS(select * from class Cl WHERE Cl.course_dept = Co.course_dept and Cl.course_num = Co.course_num and Cl.credit_hours IS NOT NULL))) AS result;") or die(mysql_error());
	$hours = mysql_result($result,0);
	if($hours == NULL) return 0;
	else return $hours;
}

/*
Function: calculateCompletedHours($CLID)
Description: Function to calculate how many of the registered hours the student has credit for.
*/
function calculateCompletedHours($clid){
	//Query to return the sum of the student's hours
	$result = mysql_query("Select sum(credit_hours) FROM(( Select credit_hours FROM take T, class Cl WHERE T.grade is not NULL and T.grade <> 'I' and T.grade <> 'NC' and T.grade <> 'W' and T.clid = '$clid' and Cl.section_num = T.section_num and Cl.semester = T.semester and Cl.year = T.year and Cl.course_num = T.course_num and Cl.course_dept = T.course_dept and Cl.credit_hours IS NOT NULL) UNION ALL (Select Co.credit_hours FROM take T,course Co WHERE T.grade is not NULL and T.grade <> 'I' and T.grade <> 'NC' and T.grade <> 'W' and T.clid = '$clid' and T.course_dept = Co.course_dept and T.course_num = Co.course_num and NOT EXISTS(select * from class Cl WHERE Cl.course_dept = Co.course_dept and Cl.course_num = Co.course_num and Cl.credit_hours IS NOT NULL))) AS result;") or die(mysql_error());
	$hours = mysql_result($result,0);
	if($hours == NULL) return 0;
	else return $hours;
}

/*
Function: meetsPrerequisite($CLID, $course)
Description: Function to calculate whether a student meets a certain course's prerequisites
*/
function meetsPrerequisite($clid, $course_dept,$course_num){
	//Query to determine if the student meets a course's prerequisites
	$meetsPrereqSet = mysql_query("select * from ((select prim_key from requisite R,take T where R.course_num='$course_num' and R.course_dept='$course_dept' and R.course_dept <> 'INST' and R.type='P' and T.clid='$clid' and T.course_num=R.req_course_num and T.course_dept=R.req_course_dept and (T.grade <= R.grade or T.grade is null) and T.grade <> 'W' and T.grade <> 'I' and T.grade <> 'F') union all (select prim_key from requisite R,spec_cred S where R.course_num='$course_num' and R.course_dept='$course_dept' and R.course_dept <> 'INST' and R.type='P' and S.clid='$clid' and S.course_num=R.req_course_num and S.course_dept=R.req_course_dept and (S.grade <= R.grade or S.grade is null) and S.grade <> 'W' and S.grade <> 'I' and S.grade <> 'F') union all (select prim_key from requisite R,take T,counts_for C where R.course_num='$course_num' and R.course_dept='$course_dept' and R.course_dept <> 'INST' and R.type='P' and T.clid='$clid' and T.course_num=C.course_num and T.course_dept=C.course_dept and C.sub_course_num=R.course_num and C.sub_course_dept=R.course_dept and (T.grade <= R.grade or T.grade is null) and T.grade <> 'W' and T.grade <> 'I' and T.grade <> 'F') union all (select prim_key from requisite R,spec_cred S,counts_for C where R.course_num='$course_num' and R.course_dept='$course_dept' and R.course_dept <> 'INST' and R.type='P' and S.clid='$clid' and S.course_num=C.course_num and S.course_dept=C.course_dept and C.sub_course_num=R.course_num and C.sub_course_dept=R.course_dept and (S.grade <= R.grade or S.grade is null) and S.grade <> 'W' and S.grade <> 'I' and S.grade <> 'F')) as result group by prim_key having count(*)=(select count(*) from requisite R where R.course_num='$course_num' and R.course_dept='$course_dept' and R.course_dept <> 'INST' and R.type='P' and R.prim_key=result.prim_key);") or die(mysql_error());
	$act_composite=calculateAct($clid);
	$ACTquery=mysql_query("select * from course C,student S where S.clid='$clid' and C.course_num='$course_num' and C.course_dept='$course_dept' and (C.act_composite='$act_composite' or C.act_math=S.act_math or C.act_english=S.act_english or C.act_reading=S.act_reading or C.act_science=S.act_science);") or die(mysql_error());
	$numprereqs=mysql_query("select count(*) from requisite R where R.course_num='$course_num' and R.course_dept='$course_dept' and R.type='P' and R.req_course_dept <> 'INST';") or die(mysql_error());
	$permissionquery=mysql_query("select * from requisite R where R.course_dept='$course_dept' and R.course_num='$course_num' and R.req_course_dept='INST' and not exists (select * from requisite R2 where R2.course_dept='$course_dept' and R2.course_num='$course_num' and R2.req_course_dept <> 'INST' and R.prim_key=R2.prim_key);") or die(mysql_error());
	if((mysql_result($numprereqs,0)==0) or (mysql_fetch_array($permissionquery))) return true;
	if((mysql_fetch_array($meetsPrereqSet)) or (mysql_fetch_array($ACTquery))) return true;
	else return false;

}

/*
Function: hasCreditFor($CLID, $course)
Description: Determines whether a student has credit for a course.  Returns grade, and any class that was used as
a substitute for the given course.
*/
function hasCreditFor($clid,$course_dept,$course_num)
{
	// Check take and spec_cred for the highest grade for the course.
	$CreditQuery = mysql_query("SELECT result.grade FROM ((SELECT T.grade FROM take T WHERE T.clid = '$clid' and T.course_dept = '$course_dept' and T.course_num = '$course_num' and T.grade is not NULL and T.grade != 'I') UNION 		(SELECT S.grade FROM spec_cred S WHERE S.clid = '$clid' and S.course_dept = '$course_dept' and S.course_num = '$course_num')) AS result ORDER BY grade LIMIT 1;") or die(mysql_error());

	// Check spec_cred to see if the student has tested out of a class but doesn't have credit
	$NoCreditQuery = mysql_query("SELECT grade FROM spec_cred WHERE clid = '$clid' and course_dept = '$course_dept' and course_num = '$course_num' and grade = 'NA';") or die(mysql_error());

	// Check counts_for to see if the student has taken a course that substitutes for the course.
	$CountsForQuery = mysql_query("SELECT * FROM ((SELECT C.course_dept,C.course_num,T.grade FROM take T,counts_for C WHERE T.clid = '$clid' and C.sub_course_dept = '$course_dept' and C.sub_course_num = '$course_num' and T.grade 		is not NULL and T.grade != 'I' and T.course_dept = C.course_dept and T.course_num = C.course_num) UNION (SELECT C.course_dept,C.course_num,S.grade FROM spec_cred S,counts_for C WHERE S.clid = '$clid' and C.sub_course_dept = 
	'$course_dept' and C.sub_course_num = '$course_num' and S.grade is not NULL and S.grade != 'I' and S.course_dept = C.course_dept and S.course_num = C.course_num)) as result order by course_dept,course_num limit 1;") or 		die(mysql_error());

	$grade = mysql_result($CreditQuery, 0);

	if(mysql_fetch_array($NoCreditQuery) or $grade == "CR") return true;
	else
	{
		if(isset($grade)) return $grade;
		else
		{	
			$substitute_course = mysql_fetch_array($CountsForQuery);
			if(isset($substitute_course)) return $substitute_course;
			else return false;
		}
	}
}

?>
