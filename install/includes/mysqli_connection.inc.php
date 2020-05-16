<?php  

if(!isset($_SESSION['MM_UserGroup']) || $_SESSION['MM_UserGroup']!=10) die("Not authorised");


$fb_mysqli_con=mysqli_connect($hostname_aquiescedb,$username_aquiescedb,$password_aquiescedb,$database_aquiescedb);
  if (mysqli_connect_errno())
  {
  die( "Failed to connect to MySQL: " . mysqli_connect_error());
  }

if (!mysqli_query($fb_mysqli_con,"SET sql_mode = ''") ) {
	// allow for old style dates to upldate tables
	die("Error: ".mysqli_error($fb_mysqli_con));
}

error_reporting(32767); // 0 = display no errors, 32767 display all
	@ini_set("display_errors", 1); // 0 = don't display none, 1 = display/
?>