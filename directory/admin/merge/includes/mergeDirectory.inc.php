<?php
// a merge function will sometimes result in duplicates in linking tables such as directoryuser, so we need to add a generic function to get around this....
set_time_limit(600); // 10 mins
ini_set("session.gc_maxlifetime","10800");
ini_set("max_execution_time","600"); // 10 mins
ini_set("max_input_time","600"); // 10 mins

require_once(SITE_ROOT."core/includes/framework.inc.php");
require_once(SITE_ROOT."members/admin/includes/mergeUsers.inc.php");


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

function mergeDirectory($entries,$keepID="") {
	writeLog("****START MERGE DIRECTORY****");
	// it is the KEY not the value that should hold the userID
	global $database_aquiescedb, $aquiescedb;
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$newentry = array();
	$oldentries = array();
	$found = false;
	$count = 0;
	$highestrank = -10;
	$message = "";
	
	$q = "SHOW TABLES FROM ".$database_aquiescedb;
	$rs = mysql_query($q, $aquiescedb) or die(mysql_error());
	$i=0;

	while($tab = mysql_fetch_array($rs)) { // cycle through tables
		$tablename = $tab[0];
		$fields=array();
    	$q="SHOW COLUMNS FROM ".$tablename; 
		$rsFields = mysql_query($q, $aquiescedb) or die(mysql_error());
		while ($field = mysql_fetch_assoc($rsFields)) { // cycle through columns
			$fieldname = $field['Field']; 
			if($fieldname =="directoryID") { // related to entry
				$updatefields[$i]['table'] = $tablename;
				$updatefields[$i]['field'] = $fieldname;
				$i++;
			} // end related to entry
    	} // end cycle through columns
	} // end cycle through tables
	
	
	
	if(is_array($entries)) { // any to merge
		foreach($entries as $key => $value) { // count through posts
	
			if($keepID==$key) { // keep directory
				$select = "SELECT ID FROM directory WHERE ID = ".intval($key); 
				$result = mysql_query($select, $aquiescedb) or die(mysql_error());
				$row = mysql_fetch_assoc($result); 
				if(isset($row['ID'])) {
					$found = true;
					$newentry['ID'] = $key;
				} // record found
			} else {
				$select = "SELECT ID FROM directory WHERE ID = ".intval($key); 
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


	foreach($updatefields as $key => $value) {
		updateOldDirectorySQL($updatefields[$key]['table'], $updatefields[$key]['field'], $newentry, $oldentries,"ID");
	} // end cycle through fields

	// delete duplicates in many-to-many group tables


	// delete old entries
	foreach($oldentries as $key => $oldentry) {
		$delete = "DELETE FROM directory WHERE ID = ".$oldentry['ID']; writeLog($delete);
		$result = mysql_query($delete, $aquiescedb);
		//echo $delete;
	}
	writeLog("****START DELETE 2 COLUMN DUPLICATES****");
	delete2ColumnDuplicates("directoryuser", "userID", "directoryID");
	delete2ColumnDuplicates("directoryincategory", "categoryID", "directoryID");
	delete2ColumnDuplicates("directorylocation", "locationID", "directoryID");
	delete2ColumnDuplicates("directoryinarea", "directoryareaID", "directoryID");
$message = ($count+1)." entries were successfully merged.";
} // end found
else { $message = "You must choose AT LEAST TWO entries to merge.<br />You must choose a user to keep who must also have the highest rank of entries to be merged."; }
	writeLog("****END MERGE DIRECTORY****");

return $message;

} // end function mergeentries

function updateOldDirectorySQL($table,$field,$newentry,$oldentries,$type) {
	global $database_aquiescedb, $aquiescedb;
	mysql_select_db($database_aquiescedb, $aquiescedb);
	foreach($oldentries as $key => $oldentry) {
		$update = "UPDATE ".$table." SET ".$field." = '".$newentry[$type]."' WHERE ".$field." = '".$oldentry[$type]."'"; writeLog($update);
		$result = mysql_query($update, $aquiescedb);
		//echo $update;
 	} // end foreach
}  // end func


function mergeIdenticalDirectory() { // this function merges all users with same email
	global $database_aquiescedb, $aquiescedb; 
	mysql_select_db($database_aquiescedb, $aquiescedb);
	writeLog("****START MERGE IDENTICAL DIRECTORY****");
	$count = 0; 
	$select1 = "SELECT count(ID) AS numdirectory, name  FROM directory WHERE name IS NOT NULL  GROUP BY name 
				HAVING numdirectory > 1";
	$result1 = mysql_query($select1, $aquiescedb) or die(mysql_error());
	while($row1 = mysql_fetch_assoc($result1)) { // count through email groups 
		$merge = array(); $keepID = 0;
		$select2 = "SELECT ID FROM directory WHERE name = ".GetSQLValueString($row1['name'], "text")." ORDER BY createddatetime ASC"; 
		$result2 = mysql_query($select2, $aquiescedb) or die(mysql_error());
		while($row2 = mysql_fetch_assoc($result2)) { //count through directory in group
			$merge[$row2['ID']] = $row2['ID']; 
			$keepID = ($keepID == 0) ? $row2['ID'] : $keepID; // keep first
		} // end individuals
		 mergeDirectory($merge, $keepID);
		 $count ++; 
	} // end count through groups
	writeLog("****END MERGE IDENTICAL DIRECTORY****");
	return $count+1;
} // end function removeDuplicateUsers
?>