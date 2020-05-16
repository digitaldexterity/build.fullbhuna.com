<?php
// a merge function will sometimes result in duplicates in linking tables such as directoryuser, so we need to add a generic function to get around this....


require_once(SITE_ROOT."core/includes/framework.inc.php");
require_once(SITE_ROOT."members/admin/includes/mergeUsers.inc.php");

set_time_limit(600); // 10 mins
ini_set("session.gc_maxlifetime","10800");
ini_set("max_execution_time","600"); // 10 mins
ini_set("max_input_time","600"); // 10 mins

if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{
  if (PHP_VERSION < 6) {
    $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
  }

  $theValue = function_exists("mysql_real_escape_string") ? mysql_real_escape_string($theValue) : mysql_escape_string($theValue);

  switch ($theType) {
    case "text":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;    
    case "long":
    case "int":
      $theValue = ($theValue != "") ? intval($theValue) : "NULL";
      break;
    case "double":
      $theValue = ($theValue != "") ? doubleval($theValue) : "NULL";
      break;
    case "date":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;
    case "defined":
      $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
      break;
  }
  return $theValue;
}
}

function mergeLocations($entries,$keepID="") {
	// it is the KEY not the value that should hold the userID
	global $database_aquiescedb, $aquiescedb;
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$newentry = array();
	$oldentries = array();
	$found = false;
	$count = 0;
	$highestrank = -10;
	$message = "";
	if(is_array($entries)) { // any to merge
	foreach($entries as $key => $value) { // count through posts
	
	if($keepID==$key) { // keep user
		$select = "SELECT ID FROM location WHERE ID = ".intval($key); 
		$result = mysql_query($select, $aquiescedb) or die(mysql_error());
		$row = mysql_fetch_assoc($result); 
		if(isset($row['ID'])) {
			$found = true;
			$newentry['ID'] = $key;
		} // record found
	} else {
		$select = "SELECT ID FROM location WHERE ID = ".intval($key); 
		$result = mysql_query($select, $aquiescedb) or die(mysql_error());
		$row = mysql_fetch_assoc($result);
		if(isset($row['ID'])) {
			$oldentries[$key]['ID'] = $key;
			$count ++;
		} // record found
	} // end non keep user
	} // end count through posts
	} // end any merge
	
	
if($found && $count > 0) { // keep user among merge



// go through all tables...
	$q = "SHOW TABLES FROM ".$database_aquiescedb;
	$rs = mysql_query($q, $aquiescedb) or die(mysql_error());


	while($tab = mysql_fetch_array($rs)) { // cycle through tables
		$tablename = $tab[0];
		$fields=array();
    	$q="SHOW COLUMNS FROM ".$tablename; 
		$rsFields = mysql_query($q, $aquiescedb) or die(mysql_error());
		while ($field = mysql_fetch_assoc($rsFields)) { // cycle through columns
			$fieldname = $field['Field']; 
			if($fieldname =="locationID" || $fieldname =="defaultaddressID"  || $fieldname =="registeredaddressID") { // related to entry
				updateOldLocationSQL($tablename, $fieldname, $newentry, $oldentries,"ID");
			} // end related to entry
    	} // end cycle through columns
	} // end cycle through tables

	// delete duplicates in many-to-many group tables


	// delete old entries
	foreach($oldentries as $key => $oldentry) {
		$delete = "DELETE FROM location WHERE ID = ".$oldentry['ID']; writeLog($delete);
		$result = mysql_query($delete, $aquiescedb);
		//echo $delete;
	}
	delete2ColumnDuplicates("locationuser", "userID", "locationID");
$message = ($count+1)." entries were successfully merged.";
} // end found
else { $message = "You must choose AT LEAST TWO entries to merge.<br />You must choose a user to keep who must also have the highest rank of entries to be merged."; }

return $message;

} // end function mergeentries

function updateOldLocationSQL($table,$field,$newentry,$oldentries,$type) {
	global $database_aquiescedb, $aquiescedb;
	mysql_select_db($database_aquiescedb, $aquiescedb);
	foreach($oldentries as $key => $oldentry) {
		$update = "UPDATE ".$table." SET ".$field." = '".$newentry[$type]."' WHERE ".$field." = '".$oldentry[$type]."'"; writeLog($update);
		$result = mysql_query($update, $aquiescedb);
		//echo $update;
 	} // end foreach
}  // end func



?>