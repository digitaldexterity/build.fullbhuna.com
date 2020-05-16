<?php require_once(SITE_ROOT."core/includes/framework.inc.php");

set_time_limit(1200); // 20 mins
ini_set("session.gc_maxlifetime","10800");
ini_set("max_execution_time","1200"); // 20 mins
ini_set("max_input_time","1200"); // 20 mins


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

function mergeUsers($users,$keepID="") {
	// it is the KEY not the value that should hold the userID
	global $database_aquiescedb, $aquiescedb;
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$newuser = array();
	$oldusers = array();
	$found = false;
	$count = 0;
	$highestrank = -10;
	$message = "";
	
	$q = "SHOW TABLES";// FROM ".$database_aquiescedb;
	$rs = mysql_query($q, $aquiescedb) or die(mysql_error());
	$i=0;
	// get all table fields that will need updating into an array
	while($tab = mysql_fetch_array($rs)) { // cycle through tables
		$tablename = $tab[0];
		$fields=array();
    	$q="SHOW COLUMNS FROM ".$tablename; 
		$rsFields = mysql_query($q, $aquiescedb) or die(mysql_error());
		while ($field = mysql_fetch_assoc($rsFields)) { // cycle through columns
			$fieldname = $field['Field']; 
			if(preg_match("/(userID|relatedtouserID|byID|operativeID|moderatorID|organiser|recipientID|tutor|contactID|clientID|trainerID|studentID|inspectorID)/i",$fieldname)) { // related to entry
				$updatefields[$i]['table'] = $tablename;
				$updatefields[$i]['field'] = $fieldname;
				$i++;
			} // end related to entry
    	} // end cycle through columns
	} // end cycle through tables
	
	
	if(is_array($users) && !empty($users)) { // gather info
		writeLog(" ****************** START MERGE USER");
		foreach($users as $key => $id) { // count through users	
			$select = "SELECT username, usertypeID FROM users WHERE ID = ".intval($id); 
			$result = mysql_query($select, $aquiescedb) or die(mysql_error());
			$row = mysql_fetch_assoc($result); 
			if(isset($row['usertypeID'])) { // user found
				if($keepID==$id) { // keep user				
					$found = true;
					$newuser['ID'] = $id;
					$newuser['username'] = $row['username'];
					$newuser['usertypeID'] = $row['usertypeID'];				
				} else {
					$oldusers[$id]['ID'] = $id;
					$oldusers[$id]['username'] = $row['username'];
					$highestrank = ($row['usertypeID'] > $highestrank) ? $row['usertypeID'] : $highestrank;
					$count ++;				
				} // end non keep user
			} // user found
		} // end count through posts
	} // end gather info
	
	
	if($found && $count > 0 && $newuser['usertypeID']>=$highestrank) { // keep user among merge

		// go through all tables and fields to update...

    	foreach($updatefields as $key => $value) {
			updateOldUsersSQL($updatefields[$key]['table'], $updatefields[$key]['field'], $newuser, $oldusers,"ID");
		}
  
	// delete old user(s)
	foreach($oldusers as $key => $olduser) {
		$delete = "DELETE FROM users WHERE ID = ".$olduser['ID']; 
		writeLog($delete);
		$result = mysql_query($delete, $aquiescedb);
	}
	// delete dulplicates in linking tables created by merge
	delete2ColumnDuplicates("directoryuser", "userID", "directoryID");
	delete2ColumnDuplicates("locationuser", "userID", "locationID");
	delete2ColumnDuplicates("usergroupmember", "userID", "groupID");
	$message = ($count+1)." users were successfully merged.";
	} // end found
	else { $message = "You must choose AT LEAST TWO users to merge.<br />You must choose a user to keep who must also have the highest rank of users to be merged."; }
	writeLog("END MERGE USERS");

	return $message;

} // end function mergeusers

function updateOldUsersSQL($table,$field,$newuser,$oldusers,$type) {
	global $database_aquiescedb, $aquiescedb;
	mysql_select_db($database_aquiescedb, $aquiescedb);
	foreach($oldusers as $key => $olduser) {
		$update = "UPDATE ".$table." SET ".$field." = '".$newuser[$type]."' WHERE ".$field." = '".$olduser[$type]."'";		
		$result = mysql_query($update, $aquiescedb);
		writeLog($update);
 	} // end foreach
}  // end func





function delete2ColumnDuplicates($table, $column1, $column2) {
	global $database_aquiescedb, $aquiescedb;
	writeLog("Checking for duplicates in ".$table);
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$select = "SELECT ID, ".$column1.", ".$column2." FROM ".$table." AS a
	WHERE 1 < (SELECT COUNT( * ) 
	FROM ".$table." AS b
	WHERE b.".$column1." = a.".$column1."
	AND b.".$column2." = a.".$column2."
	) ORDER BY ".$column1.", ".$column2;
	$result = mysql_query($select, $aquiescedb);
	$a = 0; $b=0;
	while($row = mysql_fetch_assoc($result)) { 
		if($row[$column1] == $a && $row[$column2]== $b) {
			$delete = "DELETE FROM ".$table." WHERE ID = ".$row['ID'];
			mysql_query($delete, $aquiescedb);
			writeLog($delete);
		}
		$a = $row[$column1]; $b = $row[$column2];
	}
}
	



function mergeUsersSameEmail($limit=0) { // this function merges all users with same email
	global $database_aquiescedb, $aquiescedb; 

	mysql_select_db($database_aquiescedb, $aquiescedb);
	writeLog("****START MERGE EMAILS****");
	$duplicates = array();
	$limit = ($limit>0) ? "LIMIT ".$limit : "";
	$select1 = "SELECT count(email) AS numemails, email  FROM users WHERE (email !='' AND email IS NOT NULL)  GROUP BY email 
				HAVING numemails > 1 ".$limit;
	$result1 = mysql_query($select1, $aquiescedb) or die(mysql_error());
	while($duplicate_email = mysql_fetch_assoc($result1)) { // count through email groups 
		$merge = array();
		
		$select2 = "SELECT ID, email FROM users WHERE email LIKE ".GetSQLValueString($duplicate_email['email'], "text")." ORDER BY usertypeID DESC, dateadded ASC"; 
		// we keep highest rank followed by date created (remember user must therefore keep opt-out prefs if added again in email CSV for example)
		$result2 = mysql_query($select2, $aquiescedb) or die(mysql_error());
		while($user = mysql_fetch_assoc($result2)) { //count through individuals in group
			
			$mergeuser[] = $user['ID']; 
		} // end individuals
		
			
		 mergeUsers($mergeuser,$mergeuser[0]);
		//echo $duplicate_email['email']." x ".$duplicate_email['numemails']."<br>"; 
		$duplicates['email'][] =  $duplicate_email['email'];
		$duplicates['count'][] =  $duplicate_email['numemails'];
		
	} // end count through emails;
	//die("****END MERGE EMAILS****");
	
	
	writeLog("****END MERGE EMAILS****");
	return $duplicates;	
} // end function removeDuplicateUsers

?>