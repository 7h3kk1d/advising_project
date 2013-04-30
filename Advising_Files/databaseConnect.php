<?php
/*
File: databaseConnect.php
Description: Contains the function to connect the database for the site
Author: Alexander Bandukwala
Date: Tuesday, April 19th, 2011
I hereby certify that this page is entirely my own work.
*/

/*
Function: databaseConnect()
Description: Connects the database using a specific address, user, and password. Selects the proper database.
*/
function databaseConnect(){
	//The SQL statements will need to be altered for the final product
	$link = mysql_connect('localhost', 'root', 'godEvac1');
	if(!$link)
		echo "Database Connect done Goofed";
	mysql_select_db('Advisor',$link);
}
?>
