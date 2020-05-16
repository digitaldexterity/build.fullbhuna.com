<?php  require_once(SITE_ROOT.'core/includes/framework.inc.php'); 

$console = isset($console) ? $console : "";


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

$varRegionID_rsDocumentPrefs = "1";
if (isset($regionID)) {
  $varRegionID_rsDocumentPrefs = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsDocumentPrefs = sprintf("SELECT * FROM documentprefs WHERE ID = %s", GetSQLValueString($varRegionID_rsDocumentPrefs, "int"));
$rsDocumentPrefs = mysql_query($query_rsDocumentPrefs, $aquiescedb) or die(mysql_error());
$row_rsDocumentPrefs = mysql_fetch_assoc($rsDocumentPrefs);
$totalRows_rsDocumentPrefs = mysql_num_rows($rsDocumentPrefs);


if($totalRows_rsDocumentPrefs<1) { // no row exists so add one and refresh...
	$insert = "INSERT INTO documentprefs (ID) VALUES (".$regionID.")";
	$result = mysql_query($insert, $aquiescedb) or die(mysql_error());
	header("location: /documents/index.php"); exit;
}




if(!function_exists("addDocument")) {
function addDocument($documentname = "", $folderID = 0, $uploadID = 0, $statusID = 1, $filename = "",  $userID = 0, $type="application/x-download", $lock = 0 ) {
	$documentID = -1;
	global $database_aquiescedb, $aquiescedb, $regionID;
	mysql_select_db($database_aquiescedb, $aquiescedb);
	if($folderID == -1 && intval($userID)>0) { 
	// my documents - so get real folder ID or create one
		$select = "SELECT ID FROM documentcategory WHERE addedbyID = ".intval($userID)." AND accessID = 99 ORDER BY createddatetime ASC LIMIT 1";
		$result = mysql_query($select, $aquiescedb) or die(mysql_error());
		if(mysql_num_rows($result)>0) {
			$folder = mysql_fetch_assoc($result);
			$folderID = $folder['ID'];
		} else {
			$home = getHomeFolder($regionID);
			$folderID = addfolder("My Documents", $description="", $home['ID'],  $userID, 99,99);
		}
	} // end is my docs
	if($folderID == 0) { // is home folder so get actual ID
		$home = getHomeFolder($regionID);
		$folderID = $home['ID'];
	}
	if($filename !="") {
		$documentname = (trim($documentname) !="") ? $documentname  : basename(filename);
	
	$insert = "INSERT INTO documents (documentname, documentcategoryID, active, filename, uploaddatetime, userID, type, `lock`, uploadID, `left`, `top`) VALUES (".
                       GetSQLValueString($documentname, "text").",".
                       GetSQLValueString($folderID, "int").",".
                       GetSQLValueString($statusID, "int").",".
                       GetSQLValueString($filename, "text").",NOW(),".
                       GetSQLValueString($userID, "int").",".
                       GetSQLValueString($type, "text").",".
                       GetSQLValueString($lock,"int").",".
                       GetSQLValueString($uploadID, "int").",100,100)";
					   mysql_query($insert, $aquiescedb) or die(mysql_error());
					   $documentID = mysql_insert_id();
					  
	}
  return $documentID;
}
}



if(!function_exists("addfolder")) {
function addfolder($foldername = "Untitled Folder", $description="", $parentID = 0, $createdbyID = 0, $readaccessID = 1, $writeaccessID = 9, $groupreadID=0, $groupwriteID=0, $region = 0) {
	global $database_aquiescedb, $aquiescedb, $regionID;
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$regionID = $region>0 ? $region : $regionID;
	// $parentID = 0 means within home folder but there can now be several home folders for different sites so find out which one
	if($parentID==0) {
		$home = getHomeFolder($regionID);
		//$parentID = $home['subcatofID'];
	}
	
	// does folder with this name already exist in this location?
	$select = "SELECT ID FROM documentcategory WHERE subcatofID = ".GetSQLValueString($parentID, "int")." AND categoryname != 'My Documents' AND categoryname = ".GetSQLValueString($foldername, "text");
	$result = mysql_query($select, $aquiescedb) or die(mysql_error());
	if(mysql_num_rows($result)>0) {
		$row = mysql_fetch_assoc($result);
		return $row['ID'];
	} else {
	$insert = "INSERT INTO documentcategory (categoryname, `description`, subcatofID, addedbyID, accessID, writeaccess, groupreadID, groupwriteID, regionID,  `left`, `top`, createddatetime) VALUES (".
                       GetSQLValueString($foldername, "text").",".
                       GetSQLValueString($description, "text").",".
                       GetSQLValueString($parentID, "int").",".
                       GetSQLValueString($createdbyID, "int").",".
                       GetSQLValueString($readaccessID, "int").",".
                       GetSQLValueString($writeaccessID, "int").",".
                       GetSQLValueString($groupreadID, "int").",".
					   GetSQLValueString($groupwriteID, "int").",".
                       GetSQLValueString($regionID, "int").",100,100,NOW())";

 
  mysql_query($insert, $aquiescedb) or die(mysql_error());
  $folderID = mysql_insert_id();
  return $folderID;
	}

}
}

if(!function_exists("addDocumentToCategory")) {
function addDocumentToCategory($documentID,$categoryID,$createdbyID=0,$mainfolder=false) {
	$error = "";
	global $database_aquiescedb, $aquiescedb;
	mysql_select_db($database_aquiescedb, $aquiescedb);
	if(intval($documentID)>0 && $categoryID!="" && (intval($categoryID)>=0 || intval($createdbyID)>0)) { // if my docs must have a user
		// get user info
		$select = "SELECT usertypeID FROM users WHERE ID = ".intval($createdbyID)." LIMIT 1";
		$result = mysql_query($select, $aquiescedb) or die(mysql_error().": ".$select);
		if(mysql_num_rows($result)>0) {
			$user = mysql_fetch_assoc($result);
			$rank = $user['usertypeID'];
		} else {
			$rank = 0;
		}
		// get doc info
		$select = "SELECT documentcategoryID,documentcategory.accessID, documentcategory.writeaccess FROM documents LEFT JOIN  documentcategory ON (documents.documentcategoryID = documentcategory.ID) WHERE documents.ID = ".intval($documentID)." LIMIT 1";
		$result = mysql_query($select, $aquiescedb) or die(mysql_error().": ".$select);
		if(mysql_num_rows($result)>0) { // doc exists
			$document = mysql_fetch_assoc($result);
			// security get mew cat info - must be more equal or more secure
			$select = "SELECT accessID, writeaccess FROM  documentcategory  WHERE ID = ".intval($category)." LIMIT 1";
			$result = mysql_query($select, $aquiescedb) or die(mysql_error().": ".$select);
			if(mysql_num_rows($result)>0) { // cat exists
				$category = mysql_fetch_assoc($result);
				if(!isset($document['documentcategoryID']) || ($category['accessID']>= $document['accessID'] && $category['writeaccess']>=$document['writeaccess'] && $rank >= $category['writeaccess'])) { // permissions OK
			
					if(($mainfolder==true && $categoryID>=0) || !isset($document['documentcategoryID'])){ // make main category if stipulated (and not my docs) or no main category set yet
						if(isset($document['documentcategoryID'])) { // copy existing category to supplementary category
							$result .= addDocumentToCategory($documentID,$categoryID,$createdbyID,false);
							$error .= ($result == "") ? "" : $result." ";
						}
						$update = "UPDATE documents SET documentcategoryID = ".intval($categoryID)." WHERE ID = ".intval($documentID);
						mysql_query($update, $aquiescedb) or die(mysql_error().": ".$update);
					} else if($document['documentcategoryID'] != $categoryID) { // add to supplementary category
						if(intval($categoryID)==-1) { // my docs
							$select = "SELECT ID FROM documentincategory WHERE documentID = ".intval($documentID)." AND categoryID = ".intval($categoryID)." AND userID = ".intval($categoryID)." LIMIT 1";
						} else {
							$select = "SELECT ID FROM documentincategory WHERE documentID = ".intval($documentID)." AND categoryID = ".intval($categoryID)." LIMIT 1";
						}
						$result = mysql_query($select, $aquiescedb) or die(mysql_error().": ".$select);
						if(mysql_num_rows($result)==0) { // doesn't already exist
							$userID = (intval($categoryID)==-1) ? intval($createdbyID) : "NULL";
							$insert = "INSERT INTO documentincategory (documentID, categoryID, userID, createdbyID, createddatetime) VALUES (".intval($documentID).",".intval($categoryID).",".$userID.",".intval($createdbyID).",NOW())";
							mysql_query($insert, $aquiescedb) or die(mysql_error().": ".$insert);
						}
					} // end add to supp
				} else { // fail security
					$error .= "Sorry, for security you cannot add the document to folder. The chosen folder has greater access privileges than the document's main folder. ";
				}
			} //cat exists
		} // end doc exists
	} // data OK
	return $error;
}
}

if(!function_exists("deleteDocument")) {
function deleteDocument($documentID) {
	global $database_aquiescedb, $aquiescedb;
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$select = "SELECT uploadID FROM documents WHERE ID = ".intval($documentID);
	$result = mysql_query($select, $aquiescedb) or die(mysql_error());
	if(mysql_num_rows($result)>0) {
		$upload = mysql_fetch_assoc($result);
		$delete = "DELETE FROM documents WHERE ID = ".GetSQLValueString($documentID, "int");
		$result = mysql_query($delete, $aquiescedb) or die(mysql_error());
		$delete = "DELETE FROM uploads WHERE ID = ".$upload['uploadID'];
		$result = mysql_query($delete, $aquiescedb) or die(mysql_error());
		deleteUpload($upload['uploadID']);
		$select = "SELECT uploadID FROM documentversion WHERE documentID = ".GetSQLValueString($documentID, "int");
		$result = mysql_query($select, $aquiescedb) or die(mysql_error());
		if(mysql_num_rows($result)>0) {
			while($version = mysql_fetch_assoc($result)) {
				deleteUpload($version['uploadID']);
			}
		}		
		$delete = "DELETE FROM documentversion WHERE documentID = ".GetSQLValueString($documentID, "int");
		$result = mysql_query($delete, $aquiescedb) or die(mysql_error());
		$delete = "DELETE documentincategory FROM documentincategory LEFT JOIN documents ON (documentincategory.documentID = documents.ID) WHERE documents.ID IS NULL";
		$result = mysql_query($delete, $aquiescedb) or die(mysql_error());
		//delete any unassociated rows in this table for backwards cleaning
		
	}
	
	$result = mysql_query($delete, $aquiescedb) or die(mysql_error());

}
}
if(!function_exists("getHomeFolderID")) {
function getHomeFolder($regionID) { // returns ARRAY

	global $database_aquiescedb, $aquiescedb;
	$select = "SELECT * FROM documentcategory WHERE   documentcategory.regionID = ".intval($regionID)." AND documentcategory.active = 1 AND subcatofID IS NULL";
	$result = mysql_query($select, $aquiescedb) or die(mysql_error());
	
	if(mysql_num_rows($result)>0) {
		$folder =  mysql_fetch_assoc($result);
		return $folder;
		
	} else {
		
		$insert = "INSERT INTO `documentcategory` (categoryname, subcatofID, accessID, writeaccess, regionID, addedbyID, createddatetime) VALUES ('Home',  NULL,  0, 8, ".$regionID.",0,NOW())";
		mysql_query($insert, $aquiescedb) or die(mysql_error());
		$id = mysql_insert_id();
		return array("ID"=>$id,"categoryname"=>"Home","subcatofID"=>NULL,"accessID"=>0,"writeaccess"=>8,"regionID"=>$regionID);
		
	}

}
}

if(!function_exists("getDocuments")) {
function getDocuments($categoryID=0, $search="", $limit = 1000) {
	global $database_aquiescedb, $aquiescedb, $console, $regionID;
	$varCategory_rsDocuments = "0";
	$regionID = (isset($regionID) && intval($regionID)>0) ? intval($regionID) : 1;
if (isset($categoryID)) {
  $varCategory_rsDocuments = $categoryID;
}
$varSearch_rsDocuments = "-1";
if ($search!="") {
  $varSearch_rsDocuments = $search;
}
$varUsername_rsDocuments = "-1";
if (isset($_SESSION['MM_Username'])) {
  $varUsername_rsDocuments = $_SESSION['MM_Username'];
}
$varUserGroup_rsDocuments = "0";
if (isset($_SESSION['MM_UserGroup'])) {
  $varUserGroup_rsDocuments = $_SESSION['MM_UserGroup'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsDocuments = sprintf("SELECT documents.ID,documents.left,documents.top, documents.documentname, documents.filename, documents.type, documents.`lock`, documents.userID, documents.active,documents.uploaddatetime FROM documents LEFT JOIN users ON (documents.userID = users.ID  AND users.username = %s) LEFT JOIN documentcategory ON (documents.documentcategoryID = documentcategory.ID) LEFT JOIN documentincategory ON (documentincategory.documentID = documents.ID) WHERE (documents.active = 1 OR %s >=8 OR users.username = %s)  AND (documentcategory.accessID = 99 OR documentcategory.accessID <= %s ) AND (documents.documentcategoryID = %s OR documentincategory.categoryID =  %s OR %s !='-1') AND (documents.documentcategoryID >=0 OR documents.userID = users.ID) AND (%s = '-1' OR documents.documentname LIKE %s OR documents.`description` LIKE %s) GROUP BY documents.ID ORDER BY documents.ordernum ASC, uploaddatetime DESC LIMIT ".intval($limit), GetSQLValueString($varUsername_rsDocuments, "text"),GetSQLValueString($varUserGroup_rsDocuments, "int"),GetSQLValueString($varUsername_rsDocuments, "text"),GetSQLValueString($varUserGroup_rsDocuments, "int"),GetSQLValueString($varCategory_rsDocuments, "int"),GetSQLValueString($varCategory_rsDocuments, "int"),GetSQLValueString($varSearch_rsDocuments, "text"),GetSQLValueString($varSearch_rsDocuments, "text"),GetSQLValueString("%" . $varSearch_rsDocuments . "%", "text"),GetSQLValueString("%" . $varSearch_rsDocuments . "%", "text"));

$rsDocuments = mysql_query($query_rsDocuments, $aquiescedb) or die(mysql_error());

$console .= $query_rsDocuments;

if(mysql_num_rows($rsDocuments)>0) {
return $rsDocuments;
} 
else { return false;
}
}
}


if(!function_exists("getFolders")) {
function getFolders($categoryID=0) {
	global $database_aquiescedb, $aquiescedb, $regionID, $console;
	$username = isset($_SESSION['MM_Username']) ? $_SESSION['MM_Username'] : "-1";
	

$regionID = (isset($regionID) && intval($regionID)> 0) ?  intval($regionID): 1;
$categoryID = intval($categoryID);
$rankID = isset($_SESSION['MM_UserGroup']) ? $_SESSION['MM_UserGroup']: 0;

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsFolders = "SELECT documentcategory.ID, documentcategory.categoryname,documentcategory.left,documentcategory.top,documentcategory.addedbyID, documentcategory.accessID, creator.ID AS userID, usertype.name AS usertype, documentcategory.`description` AS folderdetails, usergroup.groupname, creator.firstname, creator.surname FROM documentcategory LEFT JOIN usertype ON (accessID = usertype.ID) LEFT JOIN users AS creator ON (documentcategory.addedbyID = creator.ID) LEFT JOIN usergroup ON (documentcategory.groupreadID = usergroup.ID) LEFT JOIN usergroupmember ON (usergroupmember.groupID = documentcategory.groupreadID) LEFT JOIN users AS groupmember ON (groupmember.ID = usergroupmember.userID) WHERE (documentcategory.regionID = 0 OR documentcategory.regionID = ".GetSQLValueString($regionID, "int")." OR ".GetSQLValueString($regionID, "int")." = 0) AND documentcategory.subcatofID = ".GetSQLValueString($categoryID, "int")." AND documentcategory.active = 1 AND (((documentcategory.accessID = 99 AND ".GetSQLValueString($username, "text")." = creator.username) OR documentcategory.accessID <= ".GetSQLValueString($rankID, "int").") OR "
.GetSQLValueString($rankID, "int")." =10 OR documentcategory.accessID =0 OR (documentcategory.ID = -1 AND ".GetSQLValueString($rankID, "int")." > 0)) AND (documentcategory.groupreadID = 0 OR groupmember.username = ".GetSQLValueString($username, "text").") GROUP BY documentcategory.ID ORDER BY documentcategory.ordernum ASC,documentcategory.createddatetime DESC";
$rsFolders = mysql_query($query_rsFolders, $aquiescedb) or die(mysql_error());
 $console .= "FOLDERS SQL: ".$query_rsFolders."\n\n";
//die($query_rsFolders);
if(mysql_num_rows($rsFolders)>0) {
return $rsFolders;
} 
else { return false;
}
}
}

if(!function_exists("getFlipbooks")) {
function getFlipbooks($categoryID=0) {
	
	global $database_aquiescedb, $aquiescedb;
	
	
	$varCategory_rsFlipbooks = "0";
if (isset($categoryID)) {
  $varCategory_rsFlipbooks = $categoryID;
}
$varUsername_rsFlipbooks = "-1";
if (isset($_SESSION['MM_Username'])) {
  $varUsername_rsFlipbooks = $_SESSION['MM_Username'];
}
$varUserGroup_rsFlipbooks = "-1";
if (isset($_SESSION['MM_UserGroup'])) {
  $varUserGroup_rsFlipbooks = $_SESSION['MM_UserGroup'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsFlipbooks = sprintf("SELECT flipbook.ID, flipbook.flipbookname,flipbook.left,flipbook.top,   'flipbook' AS type, flipbook.createdbyID AS userID, flipbook.statusID AS active,  flipbook.createddatetime AS uploaddatetime FROM flipbook LEFT JOIN users ON (flipbook.createdbyID = users.ID  AND users.username = %s) WHERE (flipbook.statusID = 1 OR %s >=8 OR users.username = %s) AND  flipbook.categoryID = %s AND (flipbook.categoryID >=0 OR flipbook.createdbyID = users.ID) ORDER BY flipbook.ordernum ASC,createddatetime DESC", GetSQLValueString($varUsername_rsFlipbooks, "text"),GetSQLValueString($varUserGroup_rsFlipbooks, "int"),GetSQLValueString($varUsername_rsFlipbooks, "text"),GetSQLValueString($varCategory_rsFlipbooks, "int"));
$rsFlipbooks = mysql_query($query_rsFlipbooks, $aquiescedb) or die(mysql_error());
if(mysql_num_rows($rsFlipbooks)>0) {
return $rsFlipbooks;
} 
else { return false;
}
}
}

if(!function_exists("getShortcuts")) {
function getShortcuts($categoryID=0) {
	
	global $database_aquiescedb, $aquiescedb, $row_rsThisCategory;
	
	$varCategoryID_rsShortcuts = "0";
if (isset($row_rsThisCategory['ID'])) {
  $varCategoryID_rsShortcuts = $row_rsThisCategory['ID'];
}
$varUserType_rsShortcuts = "0";
if (isset($_SESSION['MM_UserGroup'])) {
  $varUserType_rsShortcuts = $_SESSION['MM_UserGroup'];
}
$varUsername_rsShortcuts = "-1";
if (isset($_SESSION['MM_Username'])) {
  $varUsername_rsShortcuts = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsShortcuts = sprintf("SELECT documentshortcut.*, shortcuttocat.categoryname, shortcuttocat.addedbyID, shortcuttocat.accessID, users.firstname, users.surname, usertype.name AS usertype, usergroup.groupname FROM documentshortcut LEFT JOIN documentcategory AS shortcuttocat ON (documentshortcut.shortcuttoID = shortcuttocat.ID) LEFT JOIN users ON (shortcuttocat.addedbyID = users.ID) LEFT JOIN usertype ON (shortcuttocat.accessID = usertype.ID) LEFT JOIN usergroup ON (shortcuttocat.groupreadID = usergroup.ID) LEFT JOIN usergroupmember ON (usergroupmember.groupID = shortcuttocat.groupreadID) LEFT JOIN users AS groupmember ON (usergroupmember.userID = users.ID) WHERE (documentshortcut.categoryID = %s AND   (shortcuttocat.ID IS NULL OR (shortcuttocat.active = 1  AND (shortcuttocat.accessID <= %s OR ( shortcuttocat.accessID = 99 AND users.username = %s )) AND (shortcuttocat.groupreadID = 0 OR groupmember.username = %s)))) ORDER BY documentshortcut.ordernum ASC,documentshortcut.createddatetime DESC", GetSQLValueString($varCategoryID_rsShortcuts, "int"),GetSQLValueString($varUserType_rsShortcuts, "int"),GetSQLValueString($varUsername_rsShortcuts, "int"),GetSQLValueString($varUsername_rsShortcuts, "int"));
$rsShortcuts = mysql_query($query_rsShortcuts, $aquiescedb) or die(mysql_error());
if(mysql_num_rows($rsShortcuts)>0) {
return $rsShortcuts;
} 
else { return false;
}
}
}

if(!function_exists("updateFolderRegion")) {
function updateFolderRegion($documentcategoryID, $regionID) {
	global $database_aquiescedb, $aquiescedb;
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$update = "UPDATE documentcategory SET regionID = ".intval($regionID)." WHERE ID = ".intval($documentcategoryID);
	mysql_query($update, $aquiescedb) or die(mysql_error().": ".$update);
	$select = "SELECT ID FROM documentcategory WHERE subcatofID = ".intval($documentcategoryID);
	$result = mysql_query($select, $aquiescedb) or die(mysql_error().": ".$select);
	if(mysql_num_rows($result)>0) {
		while($subcategory = mysql_fetch_assoc($result)) {
			updateFolderRegion($subcategory['ID'], $regionID);
		}
	}	
}
}
?>