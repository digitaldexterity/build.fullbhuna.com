<?php require_once('../../Connections/aquiescedb.php'); ?><?php require_once('../../core/includes/adminAccess.inc.php'); ?>
<?php require_once('../../core/includes/framework.inc.php'); ?>
<?php require_once('../includes/userfunctions.inc.php'); ?>
<?php require_once('../../core/includes/upload.inc.php'); ?>
<?php if(is_readable(SITE_ROOT.'documents/includes/documentfunctions.inc.php')) {
	require_once('../../documents/includes/documentfunctions.inc.php'); 
}?><?php require_once('../../location/includes/locationfunctions.inc.php'); ?>
<?php require_once('../../mail/includes/sendmail.inc.php'); ?>
<?php $submit_error = "";
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

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

$varUserID_rsUser = "-1";
if (isset($_GET['userID'])) {
  $varUserID_rsUser = $_GET['userID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsUser = sprintf("SELECT users.*, usertype.name AS usertype, CONCAT(createdby.firstname, ' ', createdby.surname ) AS createdbyname, CONCAT(modifiedby.firstname, ' ', modifiedby.surname ) AS modifiedbyname FROM users LEFT JOIN usertype ON (users.usertypeID = usertype.ID) LEFT JOIN users AS createdby  ON (users.addedbyID = createdby.ID) LEFT JOIN users AS modifiedby ON (users.modifiedbyID = modifiedby.ID) WHERE users.ID = %s", GetSQLValueString($varUserID_rsUser, "int"));
$rsUser = mysql_query($query_rsUser, $aquiescedb) or die(mysql_error());
$row_rsUser = mysql_fetch_assoc($rsUser);
$totalRows_rsUser = mysql_num_rows($rsUser);

//JSON to associative array
$usersettings = json_decode($row_rsUser['usersettings'], true);

if(isset($_GET["password_reset"])) {
		if(sendPasswordResetEmail($row_rsUser['ID'])) {
			$msg = "Password reset email was sent to ".$row_rsUser['email'];
		}
				
	}
	

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) { //if is post
	mysql_select_db($database_aquiescedb, $aquiescedb);
	
	// convert JSON settings back
	if(!is_array($usersettings)) $usersettings = array(); // if initially didn't exist
	// update any values
	//$usersettings['test1'] = $_POST['test1'];
	//$usersettings['test2'] = $_POST['test2'];
	$_POST['usersettings'] = json_encode($usersettings);

// do security checks

	//do have authority?
	$select = "SELECT usertypeID FROM users WHERE usertypeID <= ".$_SESSION['MM_UserGroup']." AND  ID = ".GetSQLValueString($_POST['ID'],"int");
	$result = mysql_query($select, $aquiescedb) or die(mysql_error());
	if(mysql_num_rows($result)<1) { // no authority
		unset($_POST); $userID= $_GET['userID']; unset($_GET); $_GET['userID'] = 		$userID;
		$submit_error .= "You do not have the rights to modify this user. ";

	}
	
	
	if(isset($_POST["gdpr_delete"]) && $_POST["gdpr_delete"]==1) {
		
			GDPRremoveUser($_POST['ID']);
			$msg = "User data has been removed for ".$_POST['firstname']." ".$_POST['surname'];
			header("location: index.php?msg= ".urlencode($msg)); exit;
	}


 // check if current email is used as username so if email is changed then we need to update username also...
 	if($_POST['email'] != $_POST['oldemail'] && $userPreferences['emailasusername']==1){
		$select = "SELECT ID FROM users WHERE users.username = users.email AND ID = ".GetSQLValueString($_POST['ID'],"int");
		$result = mysql_query($select, $aquiescedb) or die(mysql_error());
		if(mysql_num_rows($result)>0) { // email is username
			$_POST['username'] = $_POST['email'];
		}
	}

	// check username
	$select = "SELECT ID FROM users WHERE username = ".GetSQLValueString($_POST['username'], "text")." AND ID != ".GetSQLValueString($_POST['ID'], "int")." LIMIT 1";

	$result = mysql_query($select, $aquiescedb) or die(mysql_error());
	if(mysql_num_rows($result)>0) { // username taken
		unset($_POST["MM_update"]);
		$submit_error .="The username '".GetSQLValueString($_POST['username'],"text")."' is already taken. Please try another. ";
	} else { // check email
		if(emailTaken($_POST['email'], $_POST['ID'])>0) {// email taken
			unset($_POST["MM_update"]);
			$submit_error .= "The email '".GetSQLValueString($_POST['email'],"text")."' is already used by a user in the system. For security, an email address can only be used once. ";
		} // end duplicate email
	} // end check email
	
	// terms agree
	if(isset($_POST['termsagree']) && $_POST['oldtermsagree']==0) {
		// if just agreed to terms make date today
		$_POST['termsagreedate'] = date('Y-m-d H:i:s');
	}
	
	
} // end is post


if(isset($_POST["MM_update"]) && isset($_POST['plainpassword']) && $_POST['plainpassword']!="") {
	// add plain password to database only if none already for security
	$md5salt = md5(generateSalt());
	$password = encryptPassword($_POST['plainpassword'],$userPreferences['defaultcrypttype'],$md5salt);
	$plainpassword = $userPreferences['passwordencrypted'] != 1 ? $_POST['plainpassword'] : "";
	$update = "UPDATE users SET crypttype = ".$userPreferences['defaultcrypttype'].", password = ".GetSQLValueString($password,"text").", password_salt = ".GetSQLValueString($md5salt,"text").", plainpassword = ".GetSQLValueString($plainpassword,"text")." WHERE password IS NULL AND ID = ".GetSQLValueString($_POST['ID'], "int");
	mysql_select_db($database_aquiescedb, $aquiescedb);
 	mysql_query($update, $aquiescedb) or die(mysql_error());
}


if(isset($_POST["MM_update"]) && isset($_POST['groupID']) && $_POST['groupID'] >0) { // add user to group
 	addUsertoGroup($_POST['ID'], $_POST['groupID'],$_POST['modifiedbyID'],$_POST['expirydatetime']);
	if(isset($_POST['groupemailtemplateID']) && $_POST['groupemailtemplateID']!="") {
		$select = "SELECT groupname FROM usergroup WHERE ID = ".intval($_POST['groupID']);
		mysql_select_db($database_aquiescedb, $aquiescedb);
		$result = mysql_query($select, $aquiescedb) or die(mysql_error());
		$row = mysql_fetch_assoc($result );
		$subject = "You have been added to ".$row['groupname'];
		$message = "This is an automated message to inform you that you have been added to the group \"".$row['groupname']."\" on the ".$site_name." site.";
		
		sendMail($_POST['email'],$subject,$message,"","","","",false,"","","",$_POST['groupemailtemplateID'],false,true) ;
	}
}

if(isset($_POST['removegroupID']) && $_POST['removegroupID']>0) { // remove user from group
	$delete = "DELETE FROM usergroupmember WHERE ID = ".GetSQLValueString($_POST['removegroupID'], "int");
	$result = mysql_query($delete, $aquiescedb) or die(mysql_error());
}

if(isset($_POST['relatedtouserID']) && intval($_POST['relatedtouserID'])>0) {
	userRelationship($_POST['ID'], $_POST['relatedtouserID'], $_POST['relationshiptypeID'], $_POST['modifiedbyID']);
}


if(isset($_GET['deleteCommentID']) && $_SESSION['MM_UserGroup'] >= 9){
	$update = "UPDATE usercomments SET statusID = 0 WHERE ID = ".GetSQLValueString($_GET['deleteCommentID'], "int");
	mysql_select_db($database_aquiescedb, $aquiescedb);
  	$result = mysql_query($update, $aquiescedb) or die(mysql_error());
}

if(isset($_POST['emailoptinold']) && $_POST['emailoptinold']==1 && !isset($_POST['emailoptin'])) {
	
	$insert = "INSERT INTO groupemailoptoutlog (email, createdbyID, createddatetime) VALUES (".GetSQLValueString($_POST['email'],"text").",".GetSQLValueString($_POST['modifiedbyID'],"int").",NOW())";
	mysql_query($insert, $aquiescedb) or die(mysql_error());
}


if(isset($_POST["MM_update"]) && $_POST['comment']!="") {
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$insert = "INSERT INTO usercomments (userID, comments, createdbyID, createddatetime, statusID) VALUES (".GetSQLValueString($_POST['ID'], "int").",".GetSQLValueString($_POST['comment'], "text").",".GetSQLValueString($_POST['modifiedbyID'], "int").",NOW(),1)";
		$result = mysql_query($insert, $aquiescedb) or die(mysql_error());
} // comment posted

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
	
	
	if($_POST["usertypeID"] != $_POST["oldusertypeID"]) {
		if(!regradeUser($_POST["ID"], $_POST["usertypeID"], $_POST["modifiedbyID"], true)) {
			
			$submit_error .= "Changing user rank failed. This may be because you are not authorised to alter this user's rank. ";
		}
	}
	
	$uploaded = getUploads();
	if (isset($uploaded) && is_array($uploaded)){ 
		if(isset($uploaded["filename"][0]["error"]) && $uploaded["filename"][0]["error"]!="") {
			$submit_error .= $uploaded["filename"][0]["error"]." ";
		} else {
			if(isset($uploaded["filename"][0]["newname"]) && $uploaded["filename"][0]["newname"]!="") { 		
			$_POST['imageURL'] = $uploaded["filename"][0]["newname"]; 
			}
			if(isset($uploaded["docname"][0]["newname"]) && $uploaded["docname"][0]["newname"]!="") { 		
			
			  addDocument($_POST['documentname'],  -1, $uploaded["docname"][0]["uploadID"], 1, $uploaded["docname"][0]["newname"],  $_POST['ID'], $uploaded["docname"][0]["type"]);
			}
		}
		
	}
}

if(isset($submit_error) && $submit_error!="") unset($_POST["MM_update"]);

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE users SET salutation=%s, firstname=%s, middlename=%s, surname=%s, dob=%s, email=%s, username=%s, regionID=%s, jobtitle=%s, telephone=%s, mobile=%s, aboutme=%s, imageURL=%s, termsagree=%s, termsagreedate=%s, emailoptin=%s, partneremailoptin=%s, emailbounced=%s, identityverified=%s, contactbyphone=%s, contactbypost=%s, deceased=%s, showemail=%s, changepassword=%s, canchangepassword=%s, updateprofile=%s, discovered=%s, discoveredother=%s, twoauth=%s, failedlogin=%s, warning=%s, modifieddatetime=%s, modifiedbyID=%s, nationalityID=%s, donotautodelete=%s, youtubeURL=%s, usersettings=%s WHERE ID=%s",
                       GetSQLValueString($_POST['salutation'], "text"),
                       GetSQLValueString($_POST['firstname'], "text"),
                       GetSQLValueString($_POST['middlename'], "text"),
                       GetSQLValueString($_POST['surname'], "text"),
                       GetSQLValueString($_POST['dob'], "date"),
                       GetSQLValueString($_POST['email'], "text"),
                       GetSQLValueString($_POST['username'], "text"),
                       GetSQLValueString($_POST['regionID'], "int"),
                       GetSQLValueString($_POST['jobtitle'], "text"),
                       GetSQLValueString($_POST['telephone'], "text"),
                       GetSQLValueString($_POST['mobile'], "text"),
                       GetSQLValueString($_POST['aboutme'], "text"),
                       GetSQLValueString($_POST['imageURL'], "text"),
                       GetSQLValueString(isset($_POST['termsagree']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['termsagreedate'], "date"),
                       GetSQLValueString(isset($_POST['emailoptin']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['partneremailoptin']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['emailbounced']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['identityverified']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['contactbyphone']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['contactbypost']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['deceased'], "date"),
                       GetSQLValueString(isset($_POST['showemail']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['changepassword']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['canchangepassword']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['updateprofile']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['discovered'], "int"),
                       GetSQLValueString($_POST['discoveredother'], "text"),
                       GetSQLValueString(isset($_POST['twoauth']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['failedlogin'], "int"),
                       GetSQLValueString(isset($_POST['warning']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['modifieddatetime'], "date"),
                       GetSQLValueString($_POST['modifiedbyID'], "int"),
                       GetSQLValueString($_POST['nationalityID'], "int"),
                       GetSQLValueString(isset($_POST['donotautodelete']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['youtubeURL'], "text"),
                       GetSQLValueString($_POST['usersettings'], "text"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
	if(trim($_POST["address1"])!="" || trim($_POST["postcode"])!="") {
		$locationID = createLocation(0,0,"","",$_POST['address1'],$_POST['address2'],$_POST['address3'],$_POST['address4'],$_POST['address5'],$_POST['postcode'],"","", "", "", "", "", "","", "",$_POST['modifiedbyID']);
		addUserToLocation(intval($_POST['ID']), $locationID, $_POST['modifiedbyID']);
	}
	if(defined("EXTRA_USER_TAB")) { 
		$update = "UPDATE users SET ID = ID"; // in case no fields below
	 	foreach($extraUserFields as $key=> $value) {  $name =$extraUserFields[$key]['name']; 
	 		switch($extraUserFields[$key]["type"]) {
				case "text" : $update .= ", ".$name ." = ".GetSQLValueString($_POST[$name], "text"); break;
		  	} // end switch
		 } // end for each
		 $update .=" WHERE ID = ".GetSQLValueString($_POST['ID'], "int");
		 $Result = mysql_query($update, $aquiescedb) or die(mysql_error());
	} // end extra tab 
	if (isset($_POST['notifyemail']) && $_POST['notifyemail']==1 && isset($_POST['email']) && $_POST['email'] !="") { // notify by email
		$to = $_POST['email'];
		$subject = "Your ".$site_name." details";
		$message = "Dear ".$_POST['firstname'].",\n\n";
		$message .= "This is an automated email to inform you of your new access details for ".$site_name.".\n\n";
		if ($_POST['usertypeID'] < 1) { // no longer a member
			$message .= "You will no longer be able to log in to the site with your current username.\n\n"; }
		else { // is member
			$message .= "You can log in to the site using the following log in details:\n\n"; 
			$message .= "Username: ".$_POST['username']."\n\n";
			if(isset($_POST['plainpassword']) && $_POST['plainpassword'] !="") { // password not encrypted if posted
				$message .= "Password: ".$_POST['plainpassword']."\n\n";
			} else {
				$message .= "If you do not know or have forgotten your password click on the link below:\n\n";
				$message .= getProtocol()."://";
				$message .= $_SERVER['HTTP_HOST']."/login/forgot_password.php?email=".urlencode($_POST['email'])."\n\n";
			}
			$message .= "You can view and update your profile and change your password at any time using the link below:\n\n";
			$message .= getProtocol()."://";
			$message .= $_SERVER['HTTP_HOST']."/members/profile/";
		} // is member
		$message .= "\nRegards,\n\n";
		$message .= $site_name." Team";
		sendMail($to,$subject,$message);
	} // end notify by email
	if($_POST['email'] != $_POST['oldemail'] && (isset($_POST['notifyemail']) || $_POST['usertypeID']>=9) && isset($_POST['oldemail']) && $_POST['oldemail'] !="") { // email changed
		$to = $_POST['oldemail'];
		$subject = $site_name." email address update";
		$message = "Dear ".$_POST['firstname'].",\n\n";
		$message .= "This is an automated email to inform you that your main contact email address for ".$site_name." has been updated to: ".$_POST['email']."\n"; 
	$message .= "\nRegards,\n\n";
		$message .= $site_name." Team";
		sendMail($to,$subject,$message);	
	}
	
 	$updateGoTo = (isset($_REQUEST['returnURL']) && $_REQUEST['returnURL'] !="") ? $_REQUEST['returnURL'] : "index.php";
 	if (isset($_SERVER['QUERY_STRING'])) {
    	$updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    	$updateGoTo .= $_SERVER['QUERY_STRING'];
  	}
  	$updateGoTo = removeQueryVarFromURL($updateGoTo,"returnURL");
  	header(sprintf("Location: %s", $updateGoTo)); exit;
}

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID, usertypeID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);



$varUserGroup_rsUserTypes = "-1";
if (isset($_SESSION['MM_UserGroup'])) {
  $varUserGroup_rsUserTypes = $_SESSION['MM_UserGroup'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsUserTypes = sprintf("SELECT usertype.ID, usertype.name FROM usertype WHERE usertype.ID <= %s ORDER BY usertype.ID ASC", GetSQLValueString($varUserGroup_rsUserTypes, "int"));
$rsUserTypes = mysql_query($query_rsUserTypes, $aquiescedb) or die(mysql_error());
$row_rsUserTypes = mysql_fetch_assoc($rsUserTypes);
$totalRows_rsUserTypes = mysql_num_rows($rsUserTypes);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsRegions = "SELECT region.ID, region.title FROM region WHERE region.statusID = 1";
$rsRegions = mysql_query($query_rsRegions, $aquiescedb) or die(mysql_error());
$row_rsRegions = mysql_fetch_assoc($rsRegions);
$totalRows_rsRegions = mysql_num_rows($rsRegions);

$varUserID_rsOrganisations = "-1";
if (isset($_GET['userID'])) {
  $varUserID_rsOrganisations = $_GET['userID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsOrganisations = sprintf("SELECT directory.ID, name FROM directory LEFT JOIN users ON (directory.createdbyID = users.ID) WHERE users.ID = %s", GetSQLValueString($varUserID_rsOrganisations, "int"));
$rsOrganisations = mysql_query($query_rsOrganisations, $aquiescedb) or die(mysql_error());
$row_rsOrganisations = mysql_fetch_assoc($rsOrganisations);
$totalRows_rsOrganisations = mysql_num_rows($rsOrganisations);

$varUserID_rsDirectoryUser = "-1";
if (isset($_GET['userID'])) {
  $varUserID_rsDirectoryUser = $_GET['userID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsDirectoryUser = sprintf("SELECT directory.ID, directory.name, usertype.name AS relationshiptype, directoryuser.ID AS directoryuserID FROM directory, directoryuser, usertype WHERE directory.ID = directoryuser.directoryID AND directoryuser.relationshiptype = usertype.ID AND directoryuser.userID = %s AND (directoryuser.enddate IS NULL OR directoryuser.enddate > CURDATE()) AND directoryuser.relationshiptype >=0", GetSQLValueString($varUserID_rsDirectoryUser, "int"));
$rsDirectoryUser = mysql_query($query_rsDirectoryUser, $aquiescedb) or die(mysql_error());
$row_rsDirectoryUser = mysql_fetch_assoc($rsDirectoryUser);
$totalRows_rsDirectoryUser = mysql_num_rows($rsDirectoryUser);

$varUserID_rsAddresses = "-1";
if (isset($_GET['userID'])) {
  $varUserID_rsAddresses = $_GET['userID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsAddresses = sprintf("SELECT location.locationname, location.address1, location.address2, location.address3, location.address4, location.address5, location.postcode, location.telephone1, location.telephone2, location.telephone3, location.active AS statusID, countries.fullname AS country, location.ID, locationuserrelationship.relationship FROM locationuser LEFT JOIN location ON (locationuser.locationID = location.ID) LEFT JOIN countries ON (location.countryID = countries.ID) LEFT JOIN locationuserrelationship ON (locationuser.relationshipID = locationuserrelationship.ID) WHERE locationuser.userID = %s", GetSQLValueString($varUserID_rsAddresses, "int"));
$rsAddresses = mysql_query($query_rsAddresses, $aquiescedb) or die(mysql_error());
$row_rsAddresses = mysql_fetch_assoc($rsAddresses);
$totalRows_rsAddresses = mysql_num_rows($rsAddresses);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLocations = "SELECT location.ID, location.locationname FROM location WHERE location.active = 1 AND public = 1 ORDER BY location.locationname";
$rsLocations = mysql_query($query_rsLocations, $aquiescedb) or die(mysql_error());
$row_rsLocations = mysql_fetch_assoc($rsLocations);
$totalRows_rsLocations = mysql_num_rows($rsLocations);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsGroups = "SELECT usergroup.*, grouptype FROM usergroup LEFT JOIN usergrouptype ON (usergroup.grouptypeID = usergrouptype.ID ) WHERE usergroup.statusID = 1 AND (usergroup.regionID = 0 OR usergroup.regionID = ".intval($regionID).") AND usergroup.groupsetID IS NULL ORDER BY grouptype ASC, groupname ASC  ";
$rsGroups = mysql_query($query_rsGroups, $aquiescedb) or die(mysql_error());
$row_rsGroups = mysql_fetch_assoc($rsGroups);
$totalRows_rsGroups = mysql_num_rows($rsGroups);

$varUserID_rsUserGroups = "-1";
if (isset($_GET['userID'])) {
  $varUserID_rsUserGroups = $_GET['userID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsUserGroups = sprintf("SELECT usergroupmember.*, usergroup.groupname FROM usergroupmember LEFT JOIN usergroup ON ( usergroupmember.groupID = usergroup.ID) WHERE usergroupmember.userID = %s ORDER BY usergroup.ordernum", GetSQLValueString($varUserID_rsUserGroups, "int"));
$rsUserGroups = mysql_query($query_rsUserGroups, $aquiescedb) or die(mysql_error());
$row_rsUserGroups = mysql_fetch_assoc($rsUserGroups);
$totalRows_rsUserGroups = mysql_num_rows($rsUserGroups);

$varUserID_rsComments = "-1";
if (isset($_GET['userID'])) {
  $varUserID_rsComments = $_GET['userID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsComments = sprintf("SELECT usercomments.*, users.firstname, users.surname FROM usercomments LEFT JOIN users ON usercomments.createdbyID = users.ID WHERE usercomments.userID = %s AND statusID = 1 ORDER BY createddatetime DESC", GetSQLValueString($varUserID_rsComments, "int"));
$rsComments = mysql_query($query_rsComments, $aquiescedb) or die(mysql_error());
$row_rsComments = mysql_fetch_assoc($rsComments);
$totalRows_rsComments = mysql_num_rows($rsComments);

$colname_rsThisDirectory = "-1";
if (isset($_GET['directoryID'])) {
  $colname_rsThisDirectory = $_GET['directoryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsThisDirectory = sprintf("SELECT name FROM directory WHERE ID = %s", GetSQLValueString($colname_rsThisDirectory, "int"));
$rsThisDirectory = mysql_query($query_rsThisDirectory, $aquiescedb) or die(mysql_error());
$row_rsThisDirectory = mysql_fetch_assoc($rsThisDirectory);
$totalRows_rsThisDirectory = mysql_num_rows($rsThisDirectory);

$colname_rsThisLocation = "-1";
if (isset($_GET['locationID'])) {
  $colname_rsThisLocation = $_GET['locationID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsThisLocation = sprintf("SELECT locationname FROM location WHERE ID = %s", GetSQLValueString($colname_rsThisLocation, "int"));
$rsThisLocation = mysql_query($query_rsThisLocation, $aquiescedb) or die(mysql_error());
$row_rsThisLocation = mysql_fetch_assoc($rsThisLocation);
$totalRows_rsThisLocation = mysql_num_rows($rsThisLocation);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsMailTemplates = "SELECT ID, templatename FROM groupemailtemplate WHERE statusID = 1";
$rsMailTemplates = mysql_query($query_rsMailTemplates, $aquiescedb) or die(mysql_error());
$row_rsMailTemplates = mysql_fetch_assoc($rsMailTemplates);
$totalRows_rsMailTemplates = mysql_num_rows($rsMailTemplates);

$varUserID_rsRelationships = "-1";
if (isset($_GET['userID'])) {
  $varUserID_rsRelationships = $_GET['userID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsRelationships = sprintf("SELECT userrelationship.ID, userrelationshiptype.relationshiptype, userrelationship.userID, users.firstname, users.surname FROM userrelationship LEFT JOIN users ON (users.ID = userrelationship.relatedtouserID) LEFT JOIN userrelationshiptype ON (userrelationship.relationshiptypeID = userrelationshiptype.ID) WHERE userrelationship.userID = %s", GetSQLValueString($varUserID_rsRelationships, "int"));
$rsRelationships = mysql_query($query_rsRelationships, $aquiescedb) or die(mysql_error());
$row_rsRelationships = mysql_fetch_assoc($rsRelationships);
$totalRows_rsRelationships = mysql_num_rows($rsRelationships);

$varRegionID_reRelationshipTypes = "1";
if (isset($regionID)) {
  $varRegionID_reRelationshipTypes = $regionID;
}
$varUserGroup_reRelationshipTypes = "-1";
if (isset($_SESSION['MM_UserGroup'])) {
  $varUserGroup_reRelationshipTypes = $_SESSION['MM_UserGroup'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_reRelationshipTypes = sprintf("SELECT userrelationshiptype.ID, userrelationshiptype.relationshiptype FROM userrelationshiptype WHERE userrelationshiptype.statusID = 1  AND userrelationshiptype.accessID <= %s AND userrelationshiptype.regionID = %s ORDER BY userrelationshiptype.relationshiptype", GetSQLValueString($varUserGroup_reRelationshipTypes, "int"),GetSQLValueString($varRegionID_reRelationshipTypes, "int"));
$reRelationshipTypes = mysql_query($query_reRelationshipTypes, $aquiescedb) or die(mysql_error());
$row_reRelationshipTypes = mysql_fetch_assoc($reRelationshipTypes);
$totalRows_reRelationshipTypes = mysql_num_rows($reRelationshipTypes);

$varRegionID_rsDiscovered = "1";
if (isset($regionID)) {
  $varRegionID_rsDiscovered = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsDiscovered = sprintf("SELECT * FROM discovered WHERE statusID = 1 AND regionID = %s ORDER BY discovered.ordernum", GetSQLValueString($varRegionID_rsDiscovered, "int"));
$rsDiscovered = mysql_query($query_rsDiscovered, $aquiescedb) or die(mysql_error());
$row_rsDiscovered = mysql_fetch_assoc($rsDiscovered);
$totalRows_rsDiscovered = mysql_num_rows($rsDiscovered);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsNationality = "SELECT ID, fullname, nationality FROM countries ORDER BY ordernum,  ID";
$rsNationality = mysql_query($query_rsNationality, $aquiescedb) or die(mysql_error());
$row_rsNationality = mysql_fetch_assoc($rsNationality);
$totalRows_rsNationality = mysql_num_rows($rsNationality);

$maxRows_rsDocuments = 100;
$pageNum_rsDocuments = 0;
if (isset($_GET['pageNum_rsDocuments'])) {
  $pageNum_rsDocuments = $_GET['pageNum_rsDocuments'];
}
$startRow_rsDocuments = $pageNum_rsDocuments * $maxRows_rsDocuments;

$colname_rsDocuments = "-1";
if (isset($_GET['userID'])) {
  $colname_rsDocuments = $_GET['userID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsDocuments = sprintf("SELECT documents.uploaddatetime, documents.documentname, documents.ID FROM documents LEFT JOIN documentcategory ON (documents.documentcategoryID = documentcategory.ID) WHERE documentcategory.accessID = 99  AND documentcategory.addedbyID = %s AND documents.active = 1  ORDER BY documents.uploaddatetime DESC", GetSQLValueString($colname_rsDocuments, "int"));
$query_limit_rsDocuments = sprintf("%s LIMIT %d, %d", $query_rsDocuments, $startRow_rsDocuments, $maxRows_rsDocuments);
$rsDocuments = mysql_query($query_limit_rsDocuments, $aquiescedb) or die(mysql_error());
$row_rsDocuments = mysql_fetch_assoc($rsDocuments);

if (isset($_GET['totalRows_rsDocuments'])) {
  $totalRows_rsDocuments = $_GET['totalRows_rsDocuments'];
} else {
  $all_rsDocuments = mysql_query($query_rsDocuments);
  $totalRows_rsDocuments = mysql_num_rows($all_rsDocuments);
}
$totalPages_rsDocuments = ceil($totalRows_rsDocuments/$maxRows_rsDocuments)-1; ?>
<?php 




if(isset($_GET['loginas'])) { // log in as
	if(trim($_GET['loginas'])!="") {
		$select = "SELECT username, usertypeID FROM users WHERE username = ".GetSQLValueString($_GET['loginas'], "text");
		$result = mysql_query($select, $aquiescedb) or die(mysql_error());
		$row = mysql_fetch_assoc($result);
		if(isset($row['username']) && ($row_rsLoggedIn['usertypeID']>$row['usertypeID'] || ($_SESSION['MM_UserGroup']==10 && $_SESSION['MM_Username'] == 'admin'))) { 
			$_SESSION['MM_Username'] = $row['username'];
			$_SESSION['MM_UserGroup'] = $row['usertypeID'];
			header("location: /members/"); exit;
		} else { 
			$submit_error .= "You cannot log in as this user. "; 
		}
	} // not blank
	else { 
		$submit_error .= "This user does not have login details set. "; 
	}
} // end log in as ?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Manage User: ".$row_rsUser['firstname']." ".$row_rsUser['surname']; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<meta http-equiv="Expires" content="Fri, Jun 12 1981 00:00:00 GMT" />
<meta http-equiv="Pragma" content="no-cache" />
<meta http-equiv="Cache-Control" content="no-store" />
<meta http-equiv="Cache-Control" content="no-cache" />

<script> function resetLogin() {
	document.getElementById('failedlogin').value=0;
	document.getElementById('failedloginspan').style.display='none';
}



	</script>
 <?php if( $userPreferences['user_page_tabs']==1) { ?>
 <script src="../../SpryAssets/SpryTabbedPanels.js"></script>
<link href="../../SpryAssets/SpryTabbedPanels.css" rel="stylesheet"  />
<?php  } else {
	echo "<style><!--\n
	.TabbedPanelsTabGroup {	display:none;}\n	
	--></style>";
}
 ?>
<style><!--
<?php if ($row_rsUser['failedlogin']==0) {
// no failed logins so hide 
echo "#failedloginspan { display:none; }\n";


}
 if($userPreferences['usesalutation'] != 1) {

echo ".salutation { display:none; } \n";

}
if($totalRows_rsGroups <1) {
 echo "#userTabGroups { display:none; } \n";

}
if ($row_rsLoggedIn['usertypeID']<9 && $userPreferences['useregions'] !=1) {// cannot modify regions 
echo ".region { display: none; } \n";
} 


 if($row_rsPreferences['askmiddlename'] != 1) { echo ".middlename { display:none; } "; } ?>

--></style>
<link href="../css/membersDefault.css" rel="stylesheet"  /><script src="../../core/scripts/chooseuser.js"></script>
<script>userselectorfield = "#relatedtouserID"; </script>
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" -->
    <div class="page users">
    <h1><i class="glyphicon glyphicon-user"></i> <?php echo $row_rsUser['firstname']." ".$row_rsUser['surname']; ?> <?php if($row_rsUser['warning']==1) { ?>
      <img src="../../core/images/icons-large/dialog-warning.png" style="vertical-align:middle;" alt="This user has an alert flag"/>
      <?php } ?></h1>
    
    <?php if(isset($row_rsThisDirectory['name']) || isset($row_rsThisLocation['locationname'])) { ?>
    <h2><?php echo isset($row_rsThisDirectory['name']) ? $row_rsThisDirectory['name'] : ""; ?> <?php echo isset($row_rsThisLocation['locationname']) ? $row_rsThisLocation['locationname'] : ""; ?></h2>
    <?php } ?>
    <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav"><?php if(isset($_GET['returnURL']) && strlen($_GET['returnURL'])>1) { ?>
      <li class="nav-item"><a  class="nav-link" href="<?php echo isset($_GET['returnURL']) ? htmlentities($_GET['returnURL']) : "index.php"; ?>" ><i class="glyphicon glyphicon-arrow-left"></i> Back</a></li> <?php } ?>
      <?php if(isset($row_rsUser['username']) && ($_SESSION['MM_UserGroup']==10 || $row_rsUser['usertypeID'] <10)) { ?>
      <li class="nav-item"><a class="nav-link" href="/core/seo/admin/visitors/user_sessions.php?username=<?php echo $row_rsUser['username']; ?>"><i class="glyphicon glyphicon-stats"></i> Activity</a></li>
      <?php } ?>
      
      <?php if(isset($row_rsUser['email'])) { ?>
      <li class="nav-item"><a class="nav-link" href="<?php echo "/mail/admin/email/send.php?recipient=".$row_rsUser['email']; ?>" title="email this user"><i class="glyphicon glyphicon-envelope"></i> Send email</a></li>
      <?php } ?>
      
      
      <?php if(isset($row_rsUser['username']) && $row_rsLoggedIn['ID'] != $row_rsUser['ID'] && ($row_rsLoggedIn['usertypeID']>$row_rsUser['usertypeID'] || ($_SESSION['MM_UserGroup']==10 && $_SESSION['MM_Username'] == 'wadmin'))) { ?>
            <li class="nav-item"><a class="nav-link" href="modify_user.php?userID=<?php echo intval($_GET['userID']); ?>&amp;loginas=<?php echo $row_rsUser['username']; ?>" onClick="<?php if(isset($row_rsUser['username'])) { ?>return confirm('Are you sure you want to log in as this user?\n\nYou will be logged out as yourself and returned the the Member Home Page.');<?php } else { ?>alert('This user does not have any log in details yet.'); return false;<?php } ?>"><i class="glyphicon glyphicon-log-in"></i> Log in as <?php echo $row_rsUser['firstname']; ?> <?php echo $row_rsUser['surname']; ?></a></li>
            <?php } ?>
    </ul></div></nav>
   
    <?php require_once('../../core/includes/alert.inc.php'); ?>
    <form action="<?php echo $editFormAction; ?>" method="post" enctype="multipart/form-data" name="form1" id="form1">
      <div id="TabbedPanels1" class="TabbedPanels">
        <ul class="TabbedPanelsTabGroup">
          <li class="TabbedPanelsTab" tabindex="0">User Details</li>
          <li class="TabbedPanelsTab" tabindex="0">Profile</li>
          <li class="TabbedPanelsTab" tabindex="0" id="userTabAddresses">Contact Details</li>
          <li class="TabbedPanelsTab" tabindex="0" id="userTabGroups">Groups</li>
          <li class="TabbedPanelsTab" tabindex="0">Relationships</li>
          <li class="TabbedPanelsTab" tabindex="0" id="userTabOrganisations">Organisations</li>
          <li class="TabbedPanelsTab" tabindex="0" id="userTabComments">Comments</li>
          <li class="TabbedPanelsTab" tabindex="0">Documents</li>
<?php if(defined("EXTRA_USER_TAB")) { ?>
          <li class="TabbedPanelsTab" tabindex="0" id="userTabExtra"><?php echo EXTRA_USER_TAB; ?></li>
          <?php } ?>
        </ul>
        <div class="TabbedPanelsContentGroup">
          <div class="TabbedPanelsContent">
            <table class="form-table">
              <tr class="salutation">
                <td class="text-right">Title:</td>
                <td class="form-inline"><input name="salutation" type="text"  id="salutation" size="6" maxlength="6" value="<?php echo $row_rsUser['salutation']; ?>" class="form-control"/></td>
              </tr>
              <tr>
                <td class="text-right">First Name:</td>
                <td><input name="firstname" type="text"  id="firstname" value="<?php echo $row_rsUser['firstname']; ?>" size="50" maxlength="50" class="form-control" /></td>
              </tr>
              <tr class="middlename">
                <td class="text-right">Middle names:</td>
                <td><input name="middlename" type="text"  id="middlename" value="<?php echo $row_rsUser['middlename']; ?>" size="50" maxlength="50"  class="form-control"/></td>
              </tr>
              
              
              <tr>
                <td class="text-right">Surname:</td>
                <td><input name="surname" type="text"  id="surname" value="<?php echo $row_rsUser['surname']; ?>" size="50" maxlength="50"  class="form-control"/></td>
              </tr>
              
              <tr>
                <td class="text-right"><label for="jobtitle"><?php echo isset($row_rsPreferences['text_role']) ? $row_rsPreferences['text_role'] : "Role"; ?></label>:</td>
                <td><input name="jobtitle" type="text"  id="jobtitle" value="<?php echo $row_rsUser['jobtitle']; ?>" size="50" maxlength="100"  class="form-control"/></td>
              </tr>
              <tr>
                <td class="text-right"><input name="oldusertypeID" type="hidden" id="oldusertypeID" value="<?php echo $row_rsUser['usertypeID']; ?>" />
                  <input name="referer" type="hidden" id="referer" value="<?php echo $_SERVER['HTTP_REFERER']; ?>" />
                  <input name="ID" type="hidden" id="ID" value="<?php echo $row_rsUser['ID']; ?>" />
                  User rank: </td>
                <td class="form-inline"><?php if ($row_rsLoggedIn['usertypeID'] >= $row_rsUser['usertypeID']) { //authoised to update usertype ?>
                  <select name="usertypeID"  id="usertypeID" onChange="resetLogin()"  class="form-control">
                    <?php
do {  
?>
                    <option value="<?php echo $row_rsUserTypes['ID']?>"<?php if (!(strcmp($row_rsUserTypes['ID'], $row_rsUser['usertypeID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsUserTypes['name']?></option>
                    <?php
} while ($row_rsUserTypes = mysql_fetch_assoc($rsUserTypes));
?>
                  </select>
                  <?php } else { ?>
                  <?php echo $row_rsUser['usertype']; ?>
                  <input type="hidden" name="usertypeID" id="usertypeID" value="<?php echo $row_rsUser['usertypeID']; ?>" />
                  <?php } ?>
                  <label>
                    <input <?php if (!(strcmp($row_rsUser['termsagree'],1))) {echo "checked=\"checked\"";} ?> name="termsagree" type="checkbox" id="termsagree" value="1"  />
                    User has  agreed to terms of usage <?php if($row_rsUser['termsagree']==1 && isset($row_rsUser['termsagreedate'])) echo " on ".date('d M Y', strtotime($row_rsUser['termsagreedate'])); ?></label>
                    <input  name="oldtermsagree" type="hidden" id="oldtermsagree" value="<?php echo $row_rsUser['termsagree']; ?>"  /><input name="termsagreedate" type="hidden"  id="termsagreedate" value="<?php echo isset($row_rsUser['termsagreedate']) ? $row_rsUser['termsagreedate'] : date('Y-m-d H:i:s'); ?>"   />
                    
                    </td>
              </tr>
              <tr class="region">
                <td class="text-right">Site:</td>
                <td class="form-inline">
                  <select name="regionID"  id="regionID"  class="form-control">
                    <option value="0" <?php if (!(strcmp(0, $row_rsUser['regionID']))) {echo "selected=\"selected\"";} ?>>All sites</option>
                    <?php
do {  
?>
                    <option value="<?php echo $row_rsRegions['ID']?>"<?php if (!(strcmp($row_rsRegions['ID'], $row_rsUser['regionID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsRegions['title']?></option>
                    <?php
} while ($row_rsRegions = mysql_fetch_assoc($rsRegions));
  $rows = mysql_num_rows($rsRegions);
  if($rows > 0) {
      mysql_data_seek($rsRegions, 0);
	  $row_rsRegions = mysql_fetch_assoc($rsRegions);
  }
?>
                  </select>
                </td>
              </tr>
              <tr>
                <td class="text-right">Username:</td>
                <td><input name="username" type="text"  id="username" autocomplete="off"  value="<?php echo $row_rsUser['username']; ?>" size="50" maxlength="50" readonly onfocus="this.removeAttribute('readonly');"  class="form-control" /></td>
              </tr>
              <tr>
                <td class="text-right" >                  Password:</td>
                <td><?php if ($row_rsUser['password'] =="") { // no password yet ?>
                  <input name="plainpassword" type="text"  id="plainpassword" size="50" maxlength="50"  autocomplete="off" readonly onfocus="this.removeAttribute('readonly');"  class="form-control"/>
                  <?php } else { 
				  if(isset($_GET['password'])) {
					  echo htmlentities($_GET['password'])."<input name=\"plainpassword\" type=\"hidden\" id=\"plainpassword\" value = \"".htmlentities($_GET['password'])."\" />"; }
			 else { echo (isset($row_rsUser['plainpassword']) && trim($row_rsUser['plainpassword']) !="") ? $row_rsUser['plainpassword'] : "(Encrypted)"; }?>
                  <a href="updatepassword.php?userID=<?php echo $row_rsUser['ID']; ?>" class="btn btn-default btn-secondary">Change password</a> <?php if(isset($row_rsUser['email'])) { ?><a href="modify_user.php?userID=<?php echo $row_rsUser['ID']; ?>&password_reset=true" class="btn btn-default btn-secondary">Send password reset email</a><?php } ?>
                  <?php } ?>
                 
                 </td>
              </tr>
              <tr>
                <td class="text-right">&nbsp;</td>
                <td> <label>
                    <input <?php if (!(strcmp($row_rsUser['canchangepassword'],1))) {echo "checked=\"checked\"";} ?> name="canchangepassword" type="checkbox" id="canchangepassword" value="1" onClick="if(!this.checked){ document.getElementById('changepassword').checked = false; }" />
                    User can change their own password</label>
                  &nbsp;&nbsp;&nbsp;
                  <label>
                    <input <?php if (!(strcmp($row_rsUser['changepassword'],1))) {echo "checked=\"checked\"";} ?> name="changepassword" type="checkbox" id="changepassword" value="1" onClick="if(this.checked){ document.getElementById('canchangepassword').checked = true; }" />
                    Must change password next log-in </label>  &nbsp;&nbsp;&nbsp;
                  <label>
                    <input <?php if (!(strcmp($row_rsUser['twoauth'],1))) {echo "checked=\"checked\"";} ?> name="twoauth" type="checkbox" id="twoauth" value="1" onClick="if(this.checked) { if(document.getElementById('mobile').value =='') { alert('Two stage authentication requires a mobile phone number registered against the account.'); return false; } }"   />
                    Two-stage authentication </label></td>
              </tr>
              <tr>
                <td class="text-right">Last logged in:</td>
                <td><?php echo isset($row_rsUser['lastlogin']) ? date('d M Y H:i:s',strtotime($row_rsUser['lastlogin'])) : "N/A"; ?>
                  <input name="failedlogin" type="hidden" id="failedlogin" value="<?php echo $row_rsUser['failedlogin']; ?>" />
                  <span id="failedloginspan">&nbsp;&nbsp;&nbsp;Number of failed login attempts: <?php echo $row_rsUser['failedlogin']; ?> <a href="javascript:void(0);" onClick="resetLogin()">Reset</a></span></td>
              </tr>
            </table>
          </div>
          <div class="TabbedPanelsContent">
           <p><label> <input <?php if (!(strcmp($row_rsUser['identityverified'],1))) {echo "checked=\"checked\"";} ?> name="identityverified" type="checkbox" value="1">
           This user's identity is verified as genuine</label></p>
              <textarea name="aboutme" cols="60" rows="10" class="form-control"><?php echo $row_rsUser['aboutme']; ?></textarea>
              <br />
              <?php if (isset($row_rsUser['imageURL'])) { ?>
              <img src="<?php echo getImageURL($row_rsUser['imageURL'], "medium"); ?>" alt="User Photo" /><br />
              <input name="noImage" type="checkbox" value="1" />
              Remove image
              <?php } else { ?>
              No photo associated with this user.
              <?php } ?>
              <span class="upload"><br />
                Add/change photo below:<br />
                <input name="filename" type="file" class="fileinput" id="filename" size="20" />
                </span>
              <input name="imageURL" type="hidden" id="imageURL" value="<?php echo $row_rsUser['imageURL']; ?>" />
              <br />
              <label>
                <input <?php if (!(strcmp($row_rsUser['showemail'],1))) {echo "checked=\"checked\"";} ?> name="showemail" type="checkbox" id="showemail" value="1" />
                Show profile and email to other members</label>
              &nbsp;&nbsp;&nbsp;
              <label>
                <input <?php if (!(strcmp($row_rsUser['updateprofile'],1))) {echo "checked=\"checked\"";} ?> name="updateprofile" type="checkbox" id="updateprofile" value="1" />
                User must review profile at next log in</label>
         
            <p>Date of birth:
              <input type="hidden" name="dob" id="dob" value="<?php $inputname = "dob"; $setvalue = $row_rsUser['dob'];echo $setvalue; $startyear = 1900;?>" />
              <?php require('../../core/includes/datetimeinput.inc.php'); ?>
              <span class="deceased">&nbsp;&nbsp;&nbsp;Date deceased:
              <input type="hidden" name="deceased" id="deceased" value="<?php $startyear = 2000; $inputname = "deceased"; $setvalue = $row_rsUser['deceased'];echo $setvalue; ?>" />
              <?php require('../../core/includes/datetimeinput.inc.php'); ?></span>
            </p>
            <p class="form-inline"><label for="nationalityID">Nationality:</label> <select name="nationalityID"  id="nationalityID" class="form-control">
              <option value="" <?php if (!(strcmp("", $row_rsUser['nationalityID']))) {echo "selected=\"selected\"";} ?>>Not specified</option>
              <?php
do {  
?>
<option value="<?php echo $row_rsNationality['ID']?>"<?php if (!(strcmp($row_rsNationality['ID'], $row_rsUser['nationalityID']))) {echo "selected=\"selected\"";} ?>><?php echo isset($row_rsNationality['nationality']) ? $row_rsNationality['nationality'] : $row_rsNationality['fullname']; ?></option>
              <?php
} while ($row_rsNationality = mysql_fetch_assoc($rsNationality));
  $rows = mysql_num_rows($rsNationality);
  if($rows > 0) {
      mysql_data_seek($rsNationality, 0);
	  $row_rsNationality = mysql_fetch_assoc($rsNationality);
  }
?>
            </select></p>
            <p class="form-inline"><label for="discovered">Discovered:</label> <select name="discovered"  id="discovered" class="form-control">
              <option value="0" >Choose...</option>
              <?php
do {  
?>
              <option value="<?php echo $row_rsDiscovered['ID']?>"<?php if ($row_rsDiscovered['ID']==$row_rsUser['discovered']) {echo "selected=\"selected\"";} ?>><?php echo $row_rsDiscovered['description']?></option>
              <?php
} while ($row_rsDiscovered = mysql_fetch_assoc($rsDiscovered));
  $rows = mysql_num_rows($rsDiscovered);
  if($rows > 0) {
      mysql_data_seek($rsDiscovered, 0);
	  $row_rsDiscovered = mysql_fetch_assoc($rsDiscovered);
  }
?>
              </select>
              <label>
                <input name="discoveredother" type="text" id="discoveredother" value="<?php echo $row_rsUser['discoveredother']; ?>" size="50" maxlength="50" placeholder="Specifiy if other" class="form-control">
              </label>
            </p>
            
            <table class="form-table">
            <tr>
            <td>YouTube:
            </td>
            <td><input name="youtubeURL" type="text" id="youtubeURL" value="<?php echo $row_rsUser['youtubeURL']; ?>" size="100" maxlength="100" placeholder="YouTube channel or video page" class="form-control">
            </td>
            </tr></table>
            <p><button type="button" class="btn btn-default btn-secondary" onClick="if(confirm('Are you sure you want to delete all data from this user?\n\n- User identifiable information such as name, age, email will be removed\n- Any links to addresseswill be removed\n- The user will be removed from any groups')) { this.form.gdpr_delete.value = 1; this.form.submit(); }">GDPR - Delete User Data</button><input name="gdpr_delete" type="hidden" value="0">
            
         &nbsp;&nbsp; <label><input <?php if (!(strcmp($row_rsUser['donotautodelete'],1))) {echo "checked=\"checked\"";} ?> type="checkbox" name="donotautodelete" value="1"> Do not auto-delete this user's data</label>   
          </div>
          <div class="TabbedPanelsContent">
            <table  class="form-table">
              <tr>
                <td><label for="email">Email address:</label></td>
                <td>
                  <input name="email" type="email"  id="email" value="<?php echo $row_rsUser['email']; ?>" size="50" maxlength="50" class="form-control" />
                  <input name="oldemail" type="hidden" id="oldemail" value="<?php echo $row_rsUser['email']; ?>" />
                
                  </td>
                <td><label><input <?php if (!(strcmp($row_rsUser['emailbounced'],1))) {echo "checked=\"checked\"";} ?> name="emailbounced" type="checkbox" value="1"> Flagged as bounced</label></td>
              </tr>
              <tr>
                <td>Mobile phone:</td>
                <td><input name="mobile" type="text"  id="mobile" value="<?php echo isset($row_rsUser['mobile']) ? $row_rsUser['mobile'] : ""; ?>" size="50" maxlength="50"  class="form-control"/></td>
                <td>&nbsp;</td>
              </tr>
              <tr>
                <td>Work phone:</td>
                <td><input name="telephone" type="text"  id="telephone" value="<?php echo isset($row_rsUser['telephone']) ? $row_rsUser['telephone'] : ""; ?>" size="50" maxlength="50"  class="form-control"/></td>
                <td>&nbsp;</td>
              </tr>
            </table>
            <p>
              <label>
                <input <?php if (!(strcmp($row_rsUser['emailoptin'],1))) {echo "checked=\"checked\"";} ?> name="emailoptin" type="checkbox" id="emailoptin" value="1" />
                User opt in site emails</label>
              <input name="emailoptinold" type="hidden" id="emailoptinold" value="<?php echo $row_rsUser['emailoptin']; ?>" />
              &nbsp;&nbsp;&nbsp;
              
              
              <label>
                <input <?php if (!(strcmp($row_rsUser['partneremailoptin'],1))) {echo "checked=\"checked\"";} ?> name="partneremailoptin" type="checkbox" id="partneremailoptin" value="1" />
                User opt in partner emails</label>
             
              &nbsp;&nbsp;&nbsp;
              
              
              <label>
                <input <?php if (!(strcmp($row_rsUser['contactbypost'],1))) {echo "checked=\"checked\"";} ?> name="contactbypost" type="checkbox" id="contactbypost" value="1" />
                User can be contacted by post</label>
              &nbsp;&nbsp;&nbsp;
              <label>
                <input <?php if (!(strcmp($row_rsUser['contactbyphone'],1))) {echo "checked=\"checked\"";} ?> name="contactbyphone" type="checkbox" id="contactbyphone" value="1" />
                User can be contacted by phone</label>
            </p>
            <div id="userPostalAddress">
              <h2>Postal Addresses</h2>
              <?php if ($totalRows_rsAddresses == 0) { // Show if recordset empty ?>
              <p>There are no postal addresses stored for this user.</p>
              <?php } // Show if recordset empty ?>
              
                
                
                
              <?php if ($totalRows_rsAddresses > 0) { // Show if recordset not empty ?>
              <table  class="form-table">
                <tr>
                  <?php $col = 1; $maxcols = 2; do { ?>
                  <td class="top"><p  <?php if ($row_rsAddresses['ID'] == $row_rsUser['defaultaddressID']) { echo 'style="font-weight:bold"'; } ?>>
                    <?php 
			echo ($row_rsAddresses['locationname']!="") ? $row_rsAddresses['locationname']."<br />" : "" ;
			echo isset($row_rsAddresses['relationship']) ? "(".$row_rsAddresses['relationship'].")<br />" : "" ;
			echo ($row_rsAddresses['address1']!="") ? $row_rsAddresses['address1']."<br />" : "" ;
			echo ($row_rsAddresses['address2']!="") ? $row_rsAddresses['address2']."<br />" : "" ;
			echo ($row_rsAddresses['address3']!="") ? $row_rsAddresses['address3']."<br />" : "" ;
			echo ($row_rsAddresses['address4']!="") ? $row_rsAddresses['address4']."<br />" : "" ;
			echo ($row_rsAddresses['address5']!="") ? $row_rsAddresses['address5']."<br />" : "" ;
			echo ($row_rsAddresses['country']!="") ? $row_rsAddresses['country']."<br />" : "" ;
			echo ($row_rsAddresses['postcode']!="") ? $row_rsAddresses['postcode']."<br />" : "" ;
			echo ($row_rsAddresses['telephone1']!="") ? "<br />Telephone: ".$row_rsAddresses['telephone1']."<br />" : "" ;
			echo ($row_rsAddresses['telephone2']!="") ? "<br />Alt Phone: ".$row_rsAddresses['telephone2']."<br />" : "" ;
			echo ($row_rsAddresses['telephone3']!="") ? "<br />Alt Phone: ".$row_rsAddresses['telephone3']."<br />" : "" ; ?>
                  </p>
                    <p><a href="/location/admin/modify_location.php?locationID=<?php echo $row_rsAddresses['ID']; ?>&amp;userID=<?php echo intval($_GET['userID']); ?>">Edit</a></p></td>
                  <?php  $col = $col + 1 ; if ($col > $maxcols){  $col = 1; echo "</tr><tr>";}  } while ($row_rsAddresses = mysql_fetch_assoc($rsAddresses)); ?>
                </tr>
              </table>
              <?php } // Show if recordset not empty ?>
              
              <h3>Add address:</h3>


<table class="form-table">
                  
                  <tr>
                    <td align="right"><input name="locationname" type="hidden" value=""/>
                      Address:</td>
                    <td><input name="address1" type="text"   value="<?php echo isset($_REQUEST['address1']) ? htmlentities($_REQUEST['address1']) : ""; ?>" size="50" maxlength="50"  class="form-control"/></td>
                  </tr>
                  <tr>
                    <td align="right">&nbsp;</td>
                    <td><input name="address2" type="text"  value="<?php echo isset($_REQUEST['address2']) ? htmlentities($_REQUEST['address2']) : ""; ?>" size="50" maxlength="50" class="form-control" /></td>
                  </tr>
                  <tr>
                    <td align="right">&nbsp;</td>
                    <td><input name="address3" type="text"  value="<?php echo isset($_REQUEST['address3']) ? htmlentities($_REQUEST['address3']) : ""; ?>" size="50" maxlength="50"  class="form-control"/></td>
                  </tr>
                  <tr>
                    <td align="right">&nbsp;</td>
                    <td><input name="address4" type="text"   value="<?php echo isset($_REQUEST['address4']) ? htmlentities($_REQUEST['address4']) : ""; ?>" size="50" maxlength="50"  class="form-control"/></td>
                  </tr>
                  <tr>
                    <td align="right">&nbsp;</td>
                    <td><input name="address5" type="text"  value="<?php echo isset($_REQUEST['address5']) ? htmlentities($_REQUEST['address5']) : ""; ?>" size="50" maxlength="50" class="form-control" /></td>
                  </tr>
                  <tr>
                    <td align="right">Postcode: </td>
                    <td><input name="postcode" type="text"  size="20" maxlength="10"  value="<?php echo isset($_REQUEST['postcode']) ? htmlentities($_REQUEST['postcode']) : ""; ?>" class="form-control" /></td>
                  </tr>
                </table>
                
            </div>
            <h2>Alternative Email Addresses</h2>
            <div id="emailaddresses">
              <?php require_once('ajax/emailaddresses.inc.php'); ?>
            </div>
            <p class="form-inline"><span id="sprytextfield1">
              <input name="useremail" type="text"  id="useremail" size="50" maxlength="50"  class="form-control"/>
              <input name="returnURL" type="hidden" id="returnURL" value="<?php echo @$_REQUEST['returnURL']; ?>" />
            </span> <a href="javascript:void(0);" onClick="getData('/members/admin/ajax/emailaddresses.inc.php?ajax=true&amp;userID=<?php echo intval($_GET['userID']); ?>&amp;useremail='+document.getElementById('useremail').value+'&amp;modifiedbyID=<?php echo $row_rsLoggedIn['ID']; ?>','emailaddresses');return false;" class="btn btn-default btn-secondary" >Add</a></p>
          </div>
          <div class="TabbedPanelsContent">
            <fieldset class="form-inline">
              <legend>Add to group</legend>
              <label>
                <select name="groupID" id="groupID" class="form-control">
                  <option value="0"><?php echo isset($region['text_choose']) ? htmlentities($region['text_choose'], ENT_COMPAT, "UTF-8") : "Choose..." ?></option>
                  <?php
do {  
?>
                  <option value="<?php echo $row_rsGroups['ID']?>"><?php echo isset($row_rsGroups['grouptype']) ? $row_rsGroups['grouptype']." - " : ""; echo $row_rsGroups['groupname']?></option>
                  <?php
} while ($row_rsGroups = mysql_fetch_assoc($rsGroups));
  $rows = mysql_num_rows($rsGroups);
  if($rows > 0) {
      mysql_data_seek($rsGroups, 0);
	  $row_rsGroups = mysql_fetch_assoc($rsGroups);
  }
?>
                </select>
              </label>
              <select name="groupemailtemplateID" id="groupemailtemplateID" class="form-control">
                <option value="">with no notification to user</option>
                <option value="0">default email message to user</option>
                <?php
do {  
?>
                <option value="<?php echo $row_rsMailTemplates['ID']?>"><?php echo $row_rsMailTemplates['templatename']?></option>
                <?php
} while ($row_rsMailTemplates = mysql_fetch_assoc($rsMailTemplates));
  $rows = mysql_num_rows($rsMailTemplates);
  if($rows > 0) {
      mysql_data_seek($rsMailTemplates, 0);
	  $row_rsMailTemplates = mysql_fetch_assoc($rsMailTemplates);
  }
?>
              </select>
              
              <button name="addbutton" type="button" class="btn btn-default btn-secondary"  onClick="document.getElementById('returnURL').value='<?php echo addslashes($_SERVER['REQUEST_URI']); ?>&amp;defaultTab=3'; this.form.submit(); return false; " ><i class="glyphicon glyphicon-plus-sign"></i> Add</button> <label>Expires?
              
                <input type="checkbox" name="expires" id="expires" onClick="if(this.checked) { document.getElementById('expiresselector').style.display ='block'; } else { document.getElementById('expiresselector').style.display ='none'; }">
              </label>
              <div id="expiresselector" style="display:none;">
                <label>Valid until:
                  <input name="expirydatetime" id="expirydatetime" type="hidden" value="<?php $inputname = "expirydatetime";$setvalue =  ""; echo $setvalue;?>">
                </label>
                <?php require('../../core/includes/datetimeinput.inc.php'); ?>
              </div>
            </fieldset>
            <?php if ($totalRows_rsUserGroups == 0) { // Show if recordset empty ?>
            <p>Not a member of any groups.</p>
            <?php } // Show if recordset empty ?>
            <?php if ($totalRows_rsUserGroups > 0) { // Show if recordset not empty ?>
            <p>This user is a member of the following groups:</p>
            <table class="table table-hover">
            <tbody>
              <?php do { ?>
              <tr>
                <td class="status<?php echo ($row_rsUserGroups['statusID']==1 && (!isset($row_rsUserGroups['expirydatetime']) || $row_rsUserGroups['expirydatetime'] >= date('Y-m-d H:i:s'))) ? 1 : 2; ?>">&nbsp;</td>
                <td><?php echo $row_rsUserGroups['groupname']; ?></td>
                
                <td><em><?php echo isset($row_rsUserGroups['createddatetime']) ? "Added ".date('d M Y', strtotime($row_rsUserGroups['createddatetime'])) : ""; ?></em></td>
                <td><em><?php echo isset($row_rsUserGroups['expirydatetime']) ? "Expires ".date('d M Y', strtotime($row_rsUserGroups['expirydatetime'])) : ""; ?></em></td>
                
                
                <td><button name="deletebutton" type="button" class="btn btn-sm btn-default btn-secondary" onClick="if(confirm('Are you sure you want to remove this user from this group?')) { document.getElementById('removegroupID').value = '<?php echo $row_rsUserGroups['ID']; ?>'; document.getElementById('returnURL').value='<?php echo addslashes($_SERVER['REQUEST_URI']); ?>&amp;defaultTab=3'; this.form.submit(); } return false;" ><i class="glyphicon glyphicon-trash"></i> Remove</button></td>
              </tr>
              <?php } while ($row_rsUserGroups = mysql_fetch_assoc($rsUserGroups)); ?></tbody>
            </table>
            <?php } // Show if recordset not empty ?>
            <p>
              <input type="hidden" name="removegroupID" id="removegroupID" />
            </p>
          </div>
          <div class="TabbedPanelsContent">
            <?php if ($totalRows_rsRelationships == 0) { // Show if recordset empty ?>
            <p>This user has no relationships with any other users.</p>
            <?php } // Show if recordset empty ?>
            <?php if ($totalRows_reRelationshipTypes > 0) { // Show if recordset not empty ?>
            <p class="form-inline">
              <select name="relationshiptypeID" id="relationshiptypeID" class="form-control">
                <option value="">Add relationship...</option>
                <?php
do {  
?>
                <option value="<?php echo $row_reRelationshipTypes['ID']?>"><?php echo $row_reRelationshipTypes['relationshiptype']?></option>
                <?php
} while ($row_reRelationshipTypes = mysql_fetch_assoc($reRelationshipTypes));
  $rows = mysql_num_rows($reRelationshipTypes);
  if($rows > 0) {
      mysql_data_seek($reRelationshipTypes, 0);
	  $row_reRelationshipTypes = mysql_fetch_assoc($reRelationshipTypes);
  }
?>
              </select>
              <input name="relatedtouserID" type="text" id="relatedtouserID" size="5" maxlength="10" class="form-control">
              <input name="addbutton" type="image" onClick="document.getElementById('returnURL').value='<?php echo addslashes($_SERVER['REQUEST_URI']); ?>&amp;defaultTab=4'; this.form.submit(); return false; " src="/core/images/icons/add.png"  />
            </p>
            <?php } // Show if recordset not empty ?>
            <?php if ($totalRows_rsRelationships > 0) { // Show if recordset not empty ?>
            <table class="table table-hover">
            <tbody>
              <?php do { ?>
              <tr>
                <td><?php echo $row_rsRelationships['relationshiptype']; ?></td>
                <td>&nbsp;&raquo;&nbsp;</td>
                <td><?php echo $row_rsRelationships['firstname']; ?> <?php echo $row_rsRelationships['surname']; ?></td>
              </tr>
              <?php } while ($row_rsRelationships = mysql_fetch_assoc($rsRelationships)); ?></tbody>
            </table>
            <?php } // Show if recordset not empty ?>
          </div>
          <div class="TabbedPanelsContent">
            <?php if ($totalRows_rsDirectoryUser+$totalRows_rsOrganisations == 0) { ?>
            <p>This user is not associated with any organisations.</p>
            <?php } ?>
            <p><a href="associate_directory.php?userID=<?php echo $row_rsUser['ID']; ?>">Associate this user with an existing organisation</a></p>
            <?php if ($totalRows_rsDirectoryUser+$totalRows_rsOrganisations > 0) { ?>
            <h3>Organisations this user is associated with:</h3>
            <?php } ?>
            <?php if ($totalRows_rsDirectoryUser > 0) { // Show if recordset not empty ?>
            <table  class="table table-hover">
            <tbody>
              <?php do { ?>
              <tr>
                <td><a href="../../directory/admin/update_directory.php?directoryID=<?php echo $row_rsDirectoryUser['ID']; ?>"><?php echo $row_rsDirectoryUser['name']; ?></a></td>
                <td><em><?php echo $row_rsDirectoryUser['relationshiptype']; ?></em></td>
                <td><a href="delete_directory_user.php?userID=<?php echo $row_rsUser['ID']; ?>&amp;directoryuserID=<?php echo $row_rsDirectoryUser['directoryuserID']; ?>" onClick="document.returnValue = confirm('Are you sure you want to delete this user’s association with this organisation?'); return document.returnValue;" class="link_delete"><i class="glyphicon glyphicon-trash"></i> Delete</a></td>
              </tr>
              <?php } while ($row_rsDirectoryUser = mysql_fetch_assoc($rsDirectoryUser)); ?></tbody>
            </table>
            <?php } // Show if recordset not empty ?>
            <?php if ($totalRows_rsOrganisations > 0) { // Show if recordset not empty ?>
            <table class="form-table">
              <?php do { ?>
              <tr>
                <td><a href="../../directory/admin/update_directory.php?directoryID=<?php echo $row_rsOrganisations['ID']; ?>"><?php echo $row_rsOrganisations['name']; ?></a></td>
              </tr>
              <?php } while ($row_rsOrganisations = mysql_fetch_assoc($rsOrganisations)); ?>
            </table>
            <?php if ($_SESSION['MM_UserGroup'] ==10) { ?>
            <p>These organisations are listed in the old system. Click here to update to the new system.</p>
            <?php } ?>
            <?php } // Show if recordset not empty ?>
          </div>
          <div class="TabbedPanelsContent">
            <h2>Comments</h2>
            <p>
              <input <?php if (!(strcmp($row_rsUser['warning'],1))) {echo "checked=\"checked\"";} ?> name="warning" type="checkbox" id="warning" value="1">
              <label for="warning">Alert flag  this user </label>
            (visible to Administrators only)</p>
            <?php if ($totalRows_rsComments == 0) { // Show if recordset empty ?>
            <p>There are currently no comments for this user.</p>
            <?php } // Show if recordset empty ?>
            <?php if ($totalRows_rsComments > 0) { // Show if recordset not empty ?>
            <table border="0" cellpadding="0" cellspacing="0" class="form-table">
              <?php do { ?>
              <tr>
                <td><em>On <?php echo date('d M Y',strtotime($row_rsComments['createddatetime'])); ?> at <?php echo date('H:i a',strtotime($row_rsComments['createddatetime'])); ?>, <?php echo $row_rsComments['firstname']; ?> <?php echo $row_rsComments['surname']; ?> wrote:</em><br />
                  <?php echo nl2br($row_rsComments['comments']); ?>
                  <?php if($_SESSION['MM_UserGroup']>=9) { ?>
                  <a href="modify_user.php?deleteCommentID=<?php echo $row_rsComments['ID']; ?>&amp;defaultTab=5&amp;<?php echo $_SERVER['QUERY_STRING']; ?>" onClick="return confirm('Are you sure you want to delete this comment?');"><img src="../../core/images/icons/trash.png" alt="Delete" width="16" height="16" style="vertical-align:
middle;" /></a>
                  <?php } ?>
                <tr>
                  <td></td>
                </tr>
              <tr>
                <td>                  
              </tr>
              <?php } while ($row_rsComments = mysql_fetch_assoc($rsComments)); ?>
            </table>
            <?php } // Show if recordset not empty ?>
            <p>Add a comment below and click save changes:</p>
            <p>
              <label for="comment"></label>
              <textarea name="comment" id="comment" cols="80" rows="5"></textarea>
            </p>
          </div>
          <div class="TabbedPanelsContent"><fieldset class="form-inline"><label>Add a document: <input name="docname" type="file" class="fileinput" size="20"></label>
            <label>Title:
                <input name="documentname" type="text" id="documentname" placeholder="Enter a title (optional)" size="50" maxlength="100" class="form-control"></label>
          </fieldset>
            <?php if ($totalRows_rsDocuments == 0) { // Show if recordset empty ?>
              <p>There are no documents for this user.</p>
              <?php } // Show if recordset empty ?>
            <?php if ($totalRows_rsDocuments > 0) { // Show if recordset not empty ?>
           
            <ul>
                <?php do { ?>
                  <li><?php echo date('d M Y', strtotime($row_rsDocuments['uploaddatetime'])); ?> <a href="../../documents/view.php?documentID=<?php echo $row_rsDocuments['ID']; ?>" target="_blank" rel="noopener"><?php echo $row_rsDocuments['documentname']; ?></a> <a href="../../documents/modify_document.php?documentID=<?php echo $row_rsDocuments['ID']; ?>" target="_blank" class="link_edit icon_with_text" rel="noopener">Edit</a></li>
                  <?php } while ($row_rsDocuments = mysql_fetch_assoc($rsDocuments)); ?>
              </ul>
              <?php } // Show if recordset not empty ?>
          </div>
<?php if(defined("EXTRA_USER_TAB")) { ?>
          <div class="TabbedPanelsContent">
          <table border="0" cellpadding="5" cellspacing="0" class="form-table">
          <?php foreach($extraUserFields as $key=> $value) {  $name =$extraUserFields[$key]['name']; ?>
          <tr>
          <td align="right"><?php echo $extraUserFields[$key]["title"]; ?></td>
          <td><?php switch($extraUserFields[$key]["type"]) {
			  case "text" : echo "<input type =\"text\" name=\"".$name."\" value=\"".$row_rsUser[$name]."\" size=\"50\" maxlength=\"50\"></td>"; break;
		  } ?></td>
          </tr>
		  <?php } // end for each?>
          </table>
          </div>
          <?php } ?>
        </div>
      </div>
      <p>
        <button type="submit" class="btn btn-primary" >Save changes</button>
        <label><input name="notifyemail" type="checkbox" id="notifyemail" value="1" />
Notify user of update by email</label> 
<input name="modifiedbyID" type="hidden" id="modifiedbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
        <input name="modifieddatetime" type="hidden" id="modifieddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
        <input type="hidden" name="MM_update" value="form1" />
        <input type="hidden" name="usersettings" value="<?php echo htmlentities($row_rsUser['usersettings'], ENT_COMPAT, "UTF-8"); ?>" /><!--
        
        <input type="text" name="test1" value="<?php echo is_array($usersettings) && isset($usersettings['test1']) ? htmlentities($usersettings['test1'], ENT_COMPAT, "UTF-8") : ""; ?>" />
        <input type="text" name="test2" value="<?php echo  is_array($usersettings) && isset($usersettings['test2']) ? htmlentities($usersettings['test2'], ENT_COMPAT, "UTF-8") : ""; ?>" />-->
      </p>
      <p class="text-muted"><em>
        <?php if ($row_rsUser['addedbyID']==0) { ?>
        Web sign-up
        <?php }  else { ?>
        Created by <?php echo $row_rsUser['createdbyname']; } ?> on <?php echo date('d M Y',strtotime($row_rsUser['dateadded'])); ?> at <?php echo date('H:i',strtotime($row_rsUser['dateadded'])); if (isset($row_rsUser['modifieddatetime'])) { ?><br />
          Last modified by <?php echo $row_rsUser['modifiedbyname']; ?> on <?php echo date('d M Y',strtotime($row_rsUser['modifieddatetime'])); ?> at <?php echo date('H:i',strtotime($row_rsUser['modifieddatetime'])); ?>
          <?php } ?>
      </em></p>
    </form> <?php if( $userPreferences['user_page_tabs']==1) { 
	if (isset($_GET['defaultTab'])) { echo '<script>
<!--
var TabbedPanels1 = new Spry.Widget.TabbedPanels("TabbedPanels1", {defaultTab:'.intval($_GET['defaultTab']).'});
//-->
    </script>'; } else { ?>
    <script>
<!--
var TabbedPanels1 = new Spry.Widget.TabbedPanels("TabbedPanels1");
//-->
    </script>
    <?php } } ?>
</div>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsLocations);

mysql_free_result($rsThisDirectory);

mysql_free_result($rsThisLocation);

mysql_free_result($rsMailTemplates);

mysql_free_result($rsRelationships);

mysql_free_result($reRelationshipTypes);

mysql_free_result($rsDiscovered);

mysql_free_result($rsNationality);

mysql_free_result($rsUserGroups);

mysql_free_result($rsDocuments);

mysql_free_result($rsGroups);
?>
