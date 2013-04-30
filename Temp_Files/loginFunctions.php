<?php
/*
File: loginFunctions.php
Description: Contains all functions for the login
Author: Alexander Bandukwala
Date: Thursday, April 21st, 2011
I hereby certify that this page is entirely my own work.
*/

/*
Function: displayLogin()
Description: Display the login form if the user is not logged in. Display the appropriate welcome message if the user is logged in.
*/
function displayLogin(){
	if(!isset($_SESSION['CLID']))
		displayLoginForm();
	else
		displayWelcome($_SESSION['CLID']);
}

/*
Function: displayLoginForm()
Description: Display a blank login form
*/
function displayLoginForm(){	
	//Echo table and form data. Space is removed from the bottom of the form via the inline style property
	echo "<form style = \"display:inline;\" name=\"login\" action=\"index.php\" method=\"post\">
	      <table cellspacing = \"0\" cellpadding = \"0\" width = \"200\">
	      <tr>
	      <td>
	      CLID:
	      </td>
	      <td>
	      Password:
	      </td>
	      </tr>
	      <tr valign = \"center\">
	      <td>
	      <input size = \"7\" type=\"text\" name=\"CLID\" />
	      </td>
	      <td>
	      <input size = \"10%\" type=\"password\" name=\"pwd\" />
	      </td>
	      </tr>
	      </table>
      	      <center><input type=\"submit\" value=\"Submit\" /></center>
      	      </form>";
}

/*
Function: displayWelcome($CLID)
Description: Display the appropriate welcome message if the user is logged in.
*/
function displayWelcome($CLID){
	//Is an advisor logged in?
	if($_SESSION['advisor']==true){
		//If an advisor is logged in, display this welcome message.
		//Query to select the advisor's CLID
		$advisor=mysql_fetch_array(mysql_query("SELECT name FROM advisor WHERE clid='$CLID';"));
		//Table to hold the welcome message
		echo "<table cellspacing = \"0\" cellpadding = \"0\" width = \"250\"><tr><td>";
		echo $advisor['name']."<br />";
		echo "</td></tr></table>";
		//Does the advisor have an advisee?
		if(isset($_SESSION['advisee_CLID'])){
			//If the advisor has an advisee, display this data below the welcome message.
			//Query to select the select the advisee's name
			$student=mysql_fetch_array(mysql_query("SELECT name FROM student WHERE clid='".$_SESSION['advisee_CLID']."';"));
			//Display the advisee's name
			echo "<table cellspacing = \"0\" cellpadding = \"0\" width = \"250\"><tr><td>";
			echo "Student: ".$student['name'];
			echo "</td></tr></table>";
			//Display logout option
			echo "<table cellspacing = \"0\" cellpadding = \"0\" width = \"250\"><tr><td><a href=\"index.php?logout=1\">Logout</a></td><td><a href=\"index.php?advisee_logout=1\">Switch Student</a></td></tr></table>";
		}
		elseif(!isset($_SESSION['advisee_CLID'])){
			//If the advisor does not have an advisee, display the advisee login form to let them choose one.
			displayAdviseeLoginForm();
			//Display logout option
			echo "<a href=\"index.php?logout=1\">Logout</a>";
		}
	}
	else{
		//Query to select the student's name
		$student=mysql_fetch_array(mysql_query("SELECT name FROM student WHERE clid='$CLID';"));
		//Display the student's name
		echo $student['name']."<br />";
		echo "<a href=\"index.php?logout=1\">Logout</a>";
	}
}

/*
Function: checkLogin()
Description: Processes logins and logouts. If a login is not valid, determines what error message to show.
*/
function checkLogin(){
	//Does the advisor want a new advisee?
	if($_GET['advisee_logout']==1)
		//If so, clear advisee data
		unset($_SESSION['advisee_CLID']);
	//Does the user want to log out?
	if($_GET['logout']==1){
		//If so, destroy the session and clear all variables.
		session_destroy();
		unset($_SESSION['CLID']);
		unset($_SESSION['advisee_CLID']);
		unset($_SESSION['advisor']);
	}
	//Is a user trying to log in?
	if(isset($_POST['CLID'])){
		//If so, try to process their request.
		//Student login query to get the CLID and password
		if(mysql_fetch_array(mysql_query("SELECT * FROM student WHERE clid='".$_POST['CLID']."' AND password='".$_POST['pwd']."';"))){
			//Try to log the student in.
			studentLogin($_POST['CLID']);
		}
		//Advisor login query to get the CLID and password
		elseif(mysql_fetch_array(mysql_query("SELECT * FROM advisor WHERE clid='".$_POST['CLID']."' AND password='".$_POST['pwd']."';"))){
			//Try to log the advisor in.
			advisorLogin($_POST['CLID']);
		}
		else{
			//Combination does not beling to either a student or an advisor
			echo "<font color='#FF0000'>You done goofed: CLID/Pass Combo Bad";
		}
	}
	//Is the advisor trying to pick an advisee?
	if(isset($_POST['advisee_CLID'])){
		//Query get the advisee's information
		if(mysql_fetch_array(mysql_query("SELECT * FROM advised_by WHERE advisor_clid='".$_SESSION['clid']."'student_clid='".$_POST['advisee_CLID']."';"))){
			//Log the advisee in
			adviseeLogin($_POST['advisee_CLID']);
		}
		//Otherwise, display error message
		else echo"<font color='#FF0000'>You done goofed: Advisee CLID not in advisee list</font><br>";
	}
}

/*
Function: studentLogin($CLID)
Description: Processes student logins
*/
function studentLogin($CLID){
	//Set the session variables
	$_SESSION['CLID']=$CLID;
	$_SESSION['advisor']=false;
}

/*
Function: advisorLogin($CLID)
Description: Processes advisor logins
*/
function advisorLogin($CLID){
	//Set the session variables
	$_SESSION['CLID']=$CLID;
	$_SESSION['advisor']=true;
}

/*
Function: adviseeLogin($CLID)
Description: Process an advisor's request to add an advisee
*/
function adviseeLogin($CLID){
	//Set the session variables
	$_SESSION['advisee_CLID']=$CLID;
}

/*
Function: displayAdviseeLoginForm()
Description: Display the form for an advisor to choose an advisee.
*/
function displayAdviseeLoginForm(){
	//Echo table and form data. Space is removed from the bottom of the form via the inline style property
	echo "<form style = \"display:inline;\" name=\"adviseeLogin\" action=\"index.php\" method=\"post\">
	      <table width = \"250\" cellspacing = \"0\" cellpadding = \"0\">
	      <tr>
	      <td width = \"100\">
	      Advisee CLID:
	      </td>
	      <td>
	      <input size = \"7\" type=\"text\" name=\"advisee_CLID\" />
	      </td>
	      </tr>
	      </table>
      	      <center><input type=\"submit\" value=\"Submit\" /></center>
      	      </form>";
}

?>

