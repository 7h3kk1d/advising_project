<?php
/*
File: sidebar.php
Description: Contains all functions for the sidebar on the left of each page
Author: John Tortorich
Date: Thursday, April 21st, 2011
I hereby certify that this page is entirely my own work.
*/

/*
Function: display_sidebar()
Description: Displays the appropriate sidebar for advisors or students - calls the other functions
*/
function display_sidebar() {
  echo "<b>";
  //display default information, regardless of student/advisor status
  echo "<center><b>Default Options</b></center>";
  echo "<ul style = \"padding-left:20;\">";
  echo "<li><a href = \"index.php\">Home</a></li>";
  echo "<li><a href = \"courseListing.php\">Course List</a></li>";
  echo "</ul>";
  //Is an advisor logged in?
  if(isset($_SESSION['advisor']) and $_SESSION['advisor']){//advisor login
    //If so, show the advisor sidebar
    display_advisor();
  }
  //Is a student logged in?
  elseif(isset($_SESSION['CLID'])){//student chosen
    //If so, show the student sidebar
    display_student();
  }
  echo "</b>";
}

/*
Function: display_student()
Description: Displays the sidebar for a student's session
*/
function display_student(){
  //Simple output of the links in list form
  echo "<hr><center><b>Student Options</b></center>";
  echo "<ul style = \"padding-left:20;\">";
  echo "<li><a href = \"profile.php\">Student Profile</a></li>";
    echo "<li><a href = \"degreeChecklist.php\">Degree Checklist</a></li>";
  echo "<li><a href = \"transcript.php\">Transcript</a></li>";
  echo "<li><a href = \"advisingForm.php\">Advising Form</a></li>";
  echo "<li><a href = \"upperDivision.php\">Upper Division </a></li>";
  echo "</ul>";
}

/*
Function: display_advisor()
Description: Displays the sidebar for an advisor's session
*/
function display_advisor(){
  //Simple output of the links in list form
  echo "<hr><center><b>Advisor Options</b></center>";
  echo "<ul style = \"padding-left:20;\">";
  echo "<li><a href = \"profile.php?advisor=1\">Advisor Profile</a></li>";
  echo "<li><a href = \"upperDivision.php\">Upper Division</a></li>";
  echo "<li><a href = \"register.php\">Edit Users</a></li>";
  if(isset($_SESSION['advisee_CLID'])){ //check if a student is chosen
    echo "</ul><hr>";
    echo "<center><b>Advisee Options</b></center>";
    echo "<ul style = \"padding-left:20;\"><li><a href = \"profile.php\">Student Profile</a></li>";
    echo "<li><a href = \"transcript.php\">Transcript</a></li>";
    echo "<li><a href = \"degreeChecklist.php\">Degree Checklist</a></li>";
    echo "<li><a href = \"advisingForm.php\">Advising Form</a></li>";
  }
  echo "</ul>";
}

?>
