<?php if(isset($_POST['headline'])) {
	// clear current
	$update = "UPDATE news SET headline = 0";
	  mysql_select_db($database_aquiescedb, $aquiescedb);
  $result = mysql_query($update, $aquiescedb) or die(mysql_error());
  // new updated in update/ insert
} ?>