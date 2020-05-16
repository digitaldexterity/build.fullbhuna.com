<?php require_once(SITE_ROOT.'/location/includes/locationfunctions.inc.php');  
if(!function_exists("createDirectoryEntry")) {
function createDirectoryEntry($categoryID="", $name, $description="", $address1="", $address2="", $address3="", $address4="", $address5="", $postcode="", $telephone="", $fax="", $mobile="", $imageURL="", $mapurl="", $latitude="", $longitude="", $email="", $url="", $localwebpage="", $localweburl="", $statusID=1, $locationcategoryID=0, $createdbyID=0,$companynumber = "", $locationname = "", $isparent=0, $parentID="", $entrydate="", $directorytype="", $public=1) {
	global $database_aquiescedb, $aquiescedb;
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$insert = "INSERT INTO directory (name, `description`, address1, address2, address3, address4, address5, postcode, telephone, fax, mobile, imageURL, mapurl, latitude, longitude, email, url, localwebpage, localweburl, statusID, locationcategoryID, companynumber, isparent, parentID, directorytype, public, createdbyID, createddatetime) VALUES (".
                       GetSQLValueString($name, "text").",".
                       GetSQLValueString($description, "text").",".
                       GetSQLValueString($address1, "text").",".
                       GetSQLValueString($address2, "text").",".
                       GetSQLValueString($address3, "text").",".
                       GetSQLValueString($address4, "text").",".
                       GetSQLValueString($address5, "text").",".
                       GetSQLValueString($postcode, "text").",".
                       GetSQLValueString($telephone, "text").",".
                       GetSQLValueString($fax, "text").",".
                       GetSQLValueString($mobile, "text").",".
                       GetSQLValueString($imageURL, "text").",".
                       GetSQLValueString($mapurl, "text").",".
                       GetSQLValueString($latitude, "double").",".
                       GetSQLValueString($longitude, "double").",".
                       GetSQLValueString($email, "text").",".
                       GetSQLValueString($url, "text").",".
                       GetSQLValueString(isset($localwebpage) ? "true" : "", "defined","1","0").",".
                       GetSQLValueString($localweburl, "text").",".
                       GetSQLValueString($statusID, "int").",".
                       GetSQLValueString($locationcategoryID, "int").",".
					   GetSQLValueString($companynumber, "text").",".
					   GetSQLValueString(isset($isparent) ? "true" : "", "defined","1","0").",".
					   GetSQLValueString($parentID, "int").",".
					   GetSQLValueString($directorytype, "int").",".
					   GetSQLValueString($public, "int").",".
                       GetSQLValueString($createdbyID, "int").",NOW())";
					   $errorsql = (isset($_SESSION['MM_UserGroup']) && $_SESSION['MM_UserGroup']==10) ? ":<br><br>".$insert : ""; // only have full select statement if webadmin
					   $result=mysql_query($insert, $aquiescedb) or die(mysql_error().$errorsql);
					   $directoryID = mysql_insert_id();
					   
					   $name = ($locationname =="") ? $name : $locationname;
					   
	$locationID = createLocation(true,$locationcategoryID,$name,"",$address1,$address2,$address3,$address4,$address5,$postcode,$telephone,"","",$fax,"","","", $longitude,$latitude,$createdbyID);
		if(intval($locationID)>0) {
			addLocationToDirectory($directoryID, $locationID, $createdbyID, false, $entrydate);		
		}
		if(intval($categoryID)>0) {
			addDirectoryToCategory($directoryID, $categoryID, $createdbyID);		
		}
		return $directoryID;
}
}

if(!function_exists("addLocationToDirectory")) {
function addLocationToDirectory($directoryID, $locationID,$createdbyID=0, $default=false, $entrydate ="") {
global $database_aquiescedb, $aquiescedb;
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$insertID = false;
// add to directory if required
	if(intval($directoryID)>0 && intval($locationID)>0) {
		// check if directory already has user
		$select = "SELECT mainlocationID FROM directory WHERE ID = ".intval($directoryID);
		$result = mysql_query($select, $aquiescedb) or die(mysql_error());
		$row = mysql_fetch_assoc($result);
		$selectexists = "SELECT ID FROM directorylocation WHERE directoryID = ".$directoryID." AND locationID = ".intval($locationID);
		$resultexists = mysql_query($selectexists, $aquiescedb) or die(mysql_error());
		if(mysql_num_rows($resultexists) <1) { // doesn't exist already
			$insert = "INSERT INTO directorylocation (directoryID, locationID, entrydate, createdbyID, createddatetime) VALUES (".$directoryID.",".intval($locationID).",".GetSQLValueString($entrydate, "date").",".GetSQLValueString($createdbyID, "int").",NOW())";
			$result = mysql_query($insert, $aquiescedb) or die(mysql_error());
			$insertID = mysql_insert_id();
			if(!isset($row['mainlocationID']) || $default === true) { // no default user
				$update = "UPDATE directory SET mainlocationID = ".$locationID;
				$result = mysql_query($update, $aquiescedb) or die(mysql_error());
			} // end add default user
		} // end add relationship
		
	} // end add to location 
	return $insertID;
}
}


if(!function_exists("addDirectoryToCategory")) {
function addDirectoryToCategory($directoryID, $categoryID, $userID=0, $maincat = false) {
	// main defaults to false unless it is the first then always true
	global $database_aquiescedb, $aquiescedb;
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$select = "SELECT categoryID FROM directory WHERE ID = ".intval($directoryID);
	$result = mysql_query($select, $aquiescedb) or die(mysql_error());
	$main = mysql_fetch_assoc($result);
	if(!isset($main['categoryID']) || $maincat == true) {
		// add as cmain cat
		$update = "UPDATE directory SET categoryID = ".intval($categoryID)." WHERE ID = ".intval($directoryID);
		$result = mysql_query($update, $aquiescedb) or die(mysql_error());
	}
	
	$select = "SELECT ID FROM directoryincategory WHERE directoryID = ".intval($directoryID)." AND categoryID = ".intval($categoryID);
	$result = mysql_query($select, $aquiescedb) or die(mysql_error());
	if(mysql_num_rows($result)==0) {
		$insert = "INSERT INTO directoryincategory (directoryID, categoryID, createdbyID, createddatetime) VALUES (".intval($directoryID).",".intval($categoryID).",".intval($userID).",NOW())";
		$result = mysql_query($insert, $aquiescedb) or die(mysql_error());
	}
}
}

if(!function_exists("deleteDirectoryFromCategory")) {
function deleteDirectoryFromCategory($directoryID, $categoryID, $userID=0) {
	global $database_aquiescedb, $aquiescedb;
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$delete = "DELETE FROM directoryincategory WHERE directoryID = ".intval($directoryID)." AND categoryID = ".intval($categoryID);
	$result = mysql_query($delete, $aquiescedb) or die(mysql_error());
	$select = "SELECT categoryID FROM directory WHERE ID =".intval($directoryID);
	$result = mysql_query($select, $aquiescedb) or die(mysql_error());
	$row = mysql_fetch_assoc($result);
	if($row['categoryID'] == $categoryID) {
		$select = "SELECT categoryID FROM directoryincategory WHERE directoryID = ".intval($directoryID)." AND categoryID != ".$row['categoryID']."  LIMIT 1";
		$result = mysql_query($select, $aquiescedb) or die(mysql_error());
		$row = mysql_fetch_assoc($result);
		$newmaincategory = mysql_num_rows($result)>0 ? $row['categoryID'] : "NULL";
		$update = "UPDATE directory SET categoryID = ".$newmaincategory.", modifiedbyID=".intval($userID).", modifieddatetime = NOW() WHERE ID=".intval($directoryID);
		$result = mysql_query($update, $aquiescedb) or die(mysql_error().$update);
	}
}
}
 ?>