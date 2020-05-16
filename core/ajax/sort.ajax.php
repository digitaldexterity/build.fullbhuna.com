<?php require_once('../../Connections/aquiescedb.php'); ?>
<?php

if(isset($_REQUEST['listItem']) && isset($_REQUEST['table'])) {
	// security
	$table = (preg_replace("/[^A-Za-z0-9\_\-]/", "", $_REQUEST['table']));
	$field = isset($_REQUEST['field']) && ($_REQUEST['field']=="ordernum" || $_REQUEST['field'] =="questionorder")  ?  $_REQUEST['field'] : "ordernum";
	$primary_key = isset($_REQUEST['primary_key']) && ($_REQUEST['primary_key']=="ID")  ?  $_REQUEST['primary_key'] : "ID";
	mysql_select_db($database_aquiescedb, $aquiescedb);

		foreach ($_REQUEST['listItem'] as $position => $item)
	{ // optional - for multiple lists and to avoid duplicate IDs then we can add 1., 2. etc before ID
		// adding 1 to position as javascript counts from 0 - helps with page numbering, etc.
		$item = (strpos($item,".")===false) ? $item : substr($item, strpos($item,".")+1);
		$query = "UPDATE `".$table."` SET `".$field."` = ".intval($position+1)." WHERE `".$primary_key."` = ".intval($item); 
		mysql_query($query, $aquiescedb) or die(mysql_error());
		//echo $query."<br>";
	}
	
	echo "Order updated.";
	
} else {

	echo "Not updated.";
}
?>