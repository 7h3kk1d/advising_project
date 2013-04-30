<?php
/*
File: indexFunctions.php
Description: Contains all functions for the INDEX
Author: Kenneth Cross
Date: Thursday, April 21st, 2011
I hereby certify that this page is entirely my own work.
*/

/*
Function: displayIndex()
Description: Displays the title on the home page.
*/
function displayIndex()
{	
	echo "<center><h1>Welcome to the UL Student Advising Site</h1></center><hr>";
	displayBody();
}

/*
Function: displayBody()
Description: Display the explanation on the home page.
*/
function displayBody()
{
	echo "This is a sample UL student advising site that we created for our CMPS 460 semester project. It contains the ability to support students of all majors, but for this demonstration we will focus on those majoring in computer science. Go ahead and log in to try it out!";
}

?>
