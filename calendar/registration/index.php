<?php require_once('../../Connections/aquiescedb.php'); ?><?php require_once('../../core/includes/framework.inc.php'); ?>
<?php require_once('includes/registration.inc.php'); ?>
<?php require_once('../../members/includes/userfunctions.inc.php'); ?>
<?php require_once('../../core/includes/sslcheck.inc.php'); 
require_once('../../location/includes/locationfunctions.inc.php'); 
require_once('../../mail/includes/sendmail.inc.php'); 
//writeLog("End includes");
ignore_user_abort(1); // run script in background if user aborts
set_time_limit(1200); // 20 mins

if (!isset($_SESSION)) {
  session_start();
}

$regionID = (isset($regionID) && intval($regionID)>0) ? intval($regionID) : 1;

// must go first so we can identify user on insert


mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsEventPrefs = "SELECT * FROM eventprefs WHERE ID = ".$regionID."";
$rsEventPrefs = mysql_query($query_rsEventPrefs, $aquiescedb) or die(mysql_error());
$row_rsEventPrefs = mysql_fetch_assoc($rsEventPrefs);
$totalRows_rsEventPrefs = mysql_num_rows($rsEventPrefs);


if($row_rsEventPrefs['registrationaccesslevel']>0) {
	$MM_authorizedUsers = "";
	$MM_donotCheckaccess = "true";
	
	// *** Restrict Access To Page: Grant or deny access to this page
	function isAuthorized($strUsers, $strGroups, $UserName, $UserGroup) { 
	  // For security, start by assuming the visitor is NOT authorized. 
	  $isValid = False; 
	
	  // When a visitor has logged into this site, the Session variable MM_Username set equal to their username. 
	  // Therefore, we know that a user is NOT logged in if that Session variable is blank. 
	  if (!empty($UserName)) { 
		// Besides being logged in, you may restrict access to only certain users based on an ID established when they login. 
		// Parse the strings into arrays. 
		$arrUsers = Explode(",", $strUsers); 
		$arrGroups = Explode(",", $strGroups); 
		if (in_array($UserName, $arrUsers)) { 
		  $isValid = true; 
		} 
		// Or, you may restrict access to only certain users based on their username. 
		if (in_array($UserGroup, $arrGroups)) { 
		  $isValid = true; 
		} 
		if (($strUsers == "") && true) { 
		  $isValid = true; 
		} 
	  } 
	  return $isValid; 
	}
	
	$MM_restrictGoTo = isset($_GET['username']) ? "/login/index.php?username=".$_GET['username'] : "/login/signup.php?eventregistration=true";
	if (!((isset($_SESSION['MM_Username'])) && (isAuthorized("",$MM_authorizedUsers, $_SESSION['MM_Username'], $_SESSION['MM_UserGroup'])))) {   
	  $MM_qsChar = "?";
	  $MM_referrer = $_SERVER['PHP_SELF'];
	  if (strpos($MM_restrictGoTo, "?")) $MM_qsChar = "&";
	  if (isset($_SERVER['QUERY_STRING']) && strlen($_SERVER['QUERY_STRING']) > 0) 
	  $MM_referrer .= "?" . $_SERVER['QUERY_STRING'];
	  $MM_restrictGoTo = $MM_restrictGoTo. $MM_qsChar . "accesscheck=" . urlencode($MM_referrer);
	  header("Location: ". $MM_restrictGoTo); 
	  exit;
	}

}


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



$colname_rsAlreadyRegistered = "-1";
if (isset($_GET['eventID'])) {
  $colname_rsAlreadyRegistered = $_GET['eventID'];
}
$varUsername_rsAlreadyRegistered = "-1";
if (isset($_SESSION['MM_Username'])) {
  $varUsername_rsAlreadyRegistered = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsAlreadyRegistered = sprintf("SELECT eventregistration.ID FROM eventregistration , users WHERE eventID = %s AND userID = users.ID AND users.username = %s", GetSQLValueString($colname_rsAlreadyRegistered, "int"),GetSQLValueString($varUsername_rsAlreadyRegistered, "text"));
$rsAlreadyRegistered = mysql_query($query_rsAlreadyRegistered, $aquiescedb) or die(mysql_error());
$row_rsAlreadyRegistered = mysql_fetch_assoc($rsAlreadyRegistered);
$totalRows_rsAlreadyRegistered = mysql_num_rows($rsAlreadyRegistered);


$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT users.ID, firstname, surname, users.email, users.dob, users.jobtitle, users.defaultaddressID, location.address1, location.address2, location.address3, location.address4, location.postcode, location.telephone1, location.telephone2, directory.name FROM users LEFT JOIN location ON (users.defaultaddressID = location.ID) LEFT JOIN directoryuser ON (directoryuser.userID = users.ID) LEFT JOIN directory ON (directoryuser.directoryID = directory.ID) WHERE username = %s LIMIT 1", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

$colname_rsEvent = "-1";
if (isset($_GET['eventID'])) {
  $colname_rsEvent = $_GET['eventID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsEvent = sprintf("SELECT event.*, eventgroup.eventtitle AS eventgrouptitle, eventgroup.categoryID FROM event LEFT JOIN eventgroup ON (event.eventgroupID = eventgroup.ID) WHERE event.ID = %s", GetSQLValueString($colname_rsEvent, "int"));
$rsEvent = mysql_query($query_rsEvent, $aquiescedb) or die(mysql_error());
$row_rsEvent = mysql_fetch_assoc($rsEvent);
$totalRows_rsEvent = mysql_num_rows($rsEvent);

$colname_rsStartTimes = "-1";
if (isset($_GET['eventID'])) {
  $colname_rsStartTimes = $_GET['eventID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsStartTimes = sprintf("SELECT ID, starttime FROM eventregstarttime WHERE eventID = %s", GetSQLValueString($colname_rsStartTimes, "int"));
$rsStartTimes = mysql_query($query_rsStartTimes, $aquiescedb) or die(mysql_error());
$row_rsStartTimes = mysql_fetch_assoc($rsStartTimes);
$totalRows_rsStartTimes = mysql_num_rows($rsStartTimes);


$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}
//writeLog("Registration error checks");
// error checks
$submit_error = isset($submit_error) ? $submit_error : "";

if ($row_rsEvent['registrationmulti'] == 2) {
	// primarily added for EEHLC
	$select = "SELECT users.ID FROM users LEFT JOIN eventregistration ON (eventregistration.userID = users.ID) WHERE  users.dob IS NOT NULL AND firstname LIKE ".GetSQLValueString($_POST['firstname'], "text")." AND surname LIKE ".GetSQLValueString($_POST['surname'], "text")." AND dob LIKE ".GetSQLValueString($_POST['dob'], "date")."  AND eventregistration.eventID=".GetSQLValueString($_GET['eventID'],"int");
	$result = mysql_query($select, $aquiescedb) or die(mysql_error());
	if(mysql_num_rows($result)>0) {
		$submit_error .= "It looks like you have already registered for this event.  If you think this is in error, please contact us.<br />";
	}
	foreach($_POST['teamdob'] as $key=> $value) {
		if(trim($_POST['teamdob'][$key])!="") {
			if($_POST['dob']==$_POST['teamdob'][$key] && $_POST['teamfirstname'][$key] == $_POST['firstname'] && $_POST['teamsurname'][$key] == $_POST['surname'] ) {
				$submit_error .= "You appear to have added yourself twice - once as the main registrant and again as a team member. If you think this is in error, please contact us.<br />";
			} else {
				$select = "SELECT users.ID FROM users LEFT JOIN eventregistration ON (eventregistration.userID = users.ID) WHERE users.dob IS NOT NULL AND firstname LIKE ".GetSQLValueString($_POST['teamfirstname'][$key], "text")." AND surname LIKE ".GetSQLValueString($_POST['teamsurname'][$key], "text")." AND  dob LIKE ".GetSQLValueString($_POST['teamdob'][$key], "date")." AND eventregistration.eventID=".GetSQLValueString($_GET['eventID'],"int");
				$result = mysql_query($select, $aquiescedb) or die(mysql_error());
				if(mysql_num_rows($result)>0) {
					$submit_error .= $_POST['teamfirstname'][$key]." ".$_POST['teamsurname'][$key]." appears to be already registered for this event. If you think this is in error, please contact us.<br />";
				} // end is error
			}
		}// end is dob	
	} // end for each	
} // end multi check


if($row_rsEvent['registrationmulti'] == 1 && $totalRows_rsAlreadyRegistered>0) {
	$submit_error .= "Your registration for this event has already been received.<br />
	";
}
if(isset($_POST["MM_insert"]) && $row_rsEvent['takenpartbefore']==1 && !isset($_POST['takenpartbefore'])) {
	$submit_error .="Please state whether you have taken part before.<br />
	";
}

if(isset($_POST["MM_insert"]) && $row_rsEvent['registrationaskaddress']==1 && $_POST['address1']=="") {
	$submit_error .="Please provide an address.<br />
	";
}

if(isset($_POST["MM_insert"])  && $row_rsEvent['registrationasktelephone']==1 && $_POST['telephone1']=="" && $_POST['telephone2']=="") {
	$submit_error .="Please provide at least one telephone number.<br />
	";
}

if(isset($_POST["MM_insert"]) && $_POST['dob']=="" && $row_rsEvent['registrationdob']==1) {
	$submit_error .="Please provide your date of birth.<br />
	";
}

if(isset($_POST["MM_insert"]) && $_POST['userID']=="" && strpos($_POST['email'],"@")==-false) {
	$submit_error .="Please provide an email address.<br />
	";
}

if(isset($_POST["MM_insert"]) && $_POST['userID']=="" && trim($_POST['surname'])=="") {
	$submit_error .="Please enter your name.<br />
	";
}


if(isset($_POST["MM_insert"]) && $row_rsEvent['registrationtermstextshow']==1 &&  trim($row_rsEvent['registrationtermstext'])!="" &&  !isset($_POST['registrationterms'])) {
	$submit_error .="To register for this event, you must check box to agree with terms.<br />
	";
}

if(isset($_POST["MM_insert"]) && $row_rsEvent['registrationmarketingtextshow']==1 && trim($row_rsEvent['registrationmarketingtext'])!="" && !isset($_POST['registrationmarketing'])) {
	$submit_error .="Please state whether you wish to opt in to our mailing list.<br />
	";
}

if(isset($_POST["MM_insert"]) && $row_rsEvent['registrationextracompulsary']==1 && trim($_POST['registrationinfo'])=="") {  $submit_error .="Please answer: ".$row_rsEvent['registrationextraquestion']."<br />";
}

if(isset($_POST["MM_insert"]) && $row_rsEvent['registrationextracompulsary2']==1 && trim($_POST['registrationinfo2'])=="") {  $submit_error .="Please answer: ".$row_rsEvent['registrationextraquestion2']."<br />";
}

if(isset($_POST["MM_insert"]) && $row_rsEvent['registrationextracompulsary3']==1 && trim($_POST['registrationinfo3'])=="") {  $submit_error .="Please answer: ".$row_rsEvent['registrationextraquestion3']."<br />";
}

if($submit_error !="") {
	unset($_POST["MM_insert"]);
}

$_POST['registrationmarketing'] = isset($_POST['registrationmarketing']) ? $_POST['registrationmarketing'] : 0;
$_POST['statusID'] = ($row_rsEvent['registrationautoaccept'] == 1) ? 1 : 0;
$_POST['registrationtshirt'] = isset($_POST['registrationtshirt']) ? $_POST['registrationtshirt'] : "";
$_POST['registrationtime'] = isset($_POST['registrationtime']) ? $_POST['registrationtime'] : "";
$_POST['registrationwheelchair'] = isset($_POST['registrationwheelchair']) ? $_POST['registrationwheelchair'] : "";
$_POST['registrationdiscovered'] = isset($_POST['registrationdiscovered']) ? $_POST['registrationdiscovered'] : "";
$_POST['takenpartbefore'] = isset($_POST['takenpartbefore']) ? $_POST['takenpartbefore'] : "";
$_POST['registrationstarttime'] = isset($_POST['registrationstarttime']) ? $_POST['registrationstarttime'] : "";
//writeLog("End errors");
if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "event-registration-form")) {
	
	if(isset($_POST['userID']) && intval($_POST['userID'])>0) {
		$userID =  $_POST['userID'];
	} else {
		$userID = createNewUser($_POST['firstname'],$_POST['surname'],$_POST['email'],-1,0,0,0,"",0,"","","",$_POST['dob'],"", "", "",$regionID);	
	}
	$createdbyID = (isset($_POST['createdbyID']) && intval($_POST['createdbyID'])> 0) ? $_POST['createdbyID'] : 0;
  $insertSQL = sprintf("INSERT INTO eventregistration (eventID, userID, createdbyID, createddatetime, statusID, takenpartbefore, registrationteamname, registrationmedical, registrationinfo, registrationtshirt, registrationtime, registrationwheelchair, registrationdiscovered, registrationmarketing, registrationterms, registrationstarttime, registrationdietryreq, registrationspecialreq, registrationinfo2, registrationinfo3, registrationjobtitle, registrationcompany) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['eventID'], "int"),
                       GetSQLValueString($userID, "int"),
                       GetSQLValueString($createdbyID, "int"),
                       GetSQLValueString($_POST['createddatetime'], "date"),
                       GetSQLValueString($_POST['statusID'], "int"),
                       GetSQLValueString($_POST['takenpartbefore'], "int"),
                       GetSQLValueString($_POST['registrationteamname'], "text"),
                       GetSQLValueString($_POST['registrationmedical'], "text"),
                       GetSQLValueString($_POST['registrationinfo'], "text"),
                       GetSQLValueString($_POST['registrationtshirt'], "int"),
                       GetSQLValueString($_POST['registrationtime'], "text"),
                       GetSQLValueString($_POST['registrationwheelchair'], "int"),
                       GetSQLValueString($_POST['registrationdiscovered'], "int"),
                       GetSQLValueString($_POST['registrationmarketing'], "int"),
                       GetSQLValueString(isset($_POST['registrationterms']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['registrationstarttime'], "int"),
                       GetSQLValueString($_POST['registrationdietryreq'], "text"),
                       GetSQLValueString($_POST['registrationspecialreq'], "text"),
                       GetSQLValueString($_POST['registrationinfo2'], "text"),
                       GetSQLValueString($_POST['registrationinfo3'], "text"),
                       GetSQLValueString($_POST['registrationjobtitle'], "text"),
                       GetSQLValueString($_POST['registrationcompany'], "text"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($insertSQL, $aquiescedb) or die(mysql_error().": ".$insertSQL);
}



if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "event-registration-form")) {
	mysql_select_db($database_aquiescedb, $aquiescedb);
//writeLog("Start numbers");
	$registrationID = mysql_insert_id();
	$token = md5(PRIVATE_KEY.$registrationID);
	$emails = array();
	$pendingNumber['ID'] = array(); // array to hold IDs of registrations to get number
	$pendingNumber['number'] = array(); // array to hold IDs of registrations to get number
	$pendingNumber['name'] = array(); // array to hold IDs of registrations to get number
	array_push($pendingNumber['ID'], $registrationID);
	array_push($pendingNumber['name'], $row_rsLoggedIn['firstname']." ".$row_rsLoggedIn['surname']);
	array_push($emails,$row_rsLoggedIn['email']);
		
	 //add to group
	 if(isset($row_rsEvent['registrationgroupID'])) {
		 addUsertoGroup($userID,$row_rsEvent['registrationgroupID'],$createdbyID);
	 }
	if($row_rsEvent['registrationaskaddress']==1||$row_rsEvent['registrationasktelephone']==1) {
		if(isset($row_rsLoggedIn['defaultaddressID'])) {
		// update address
			$update = "UPDATE location SET address1 = ".GetSQLValueString($_POST['address1'], "text").", address2 = ".GetSQLValueString($_POST['address2'], "text").", address3=".GetSQLValueString($_POST['address3'], "text").", address4=".GetSQLValueString($_POST['address4'], "text").", postcode=".GetSQLValueString($_POST['postcode'], "text").", telephone1=".GetSQLValueString($_POST['telephone1'], "text").", telephone2=".GetSQLValueString($_POST['telephone2'], "text").", modifiedbyID= ".$userID.", modifieddatetime = NOW() WHERE ID = ".$row_rsLoggedIn['defaultaddressID'];
			$result = mysql_query($update, $aquiescedb) or die(mysql_error().": ".$update); 
		} else { // new address
			$insert = "INSERT INTO location (userID,address1,address2,address3,address4,postcode,telephone1,telephone2,createdbyID, createddatetime) VALUES (".GetSQLValueString($userID, "int").",".GetSQLValueString($_POST['address1'], "text").",".GetSQLValueString($_POST['address2'], "text").",".GetSQLValueString($_POST['address3'], "text").",".GetSQLValueString($_POST['address4'], "text").",".GetSQLValueString($_POST['postcode'], "text").",".GetSQLValueString($_POST['telephone1'], "text").",".GetSQLValueString($_POST['telephone2'], "text").",".$userID.",NOW())";
			$result = mysql_query($insert, $aquiescedb) or die(mysql_error().": ".$insert); 
			$locationID = mysql_insert_id();
			$update = "UPDATE users SET defaultaddressID = ".$locationID." WHERE ID = ".$userID;
			$result = mysql_query($update, $aquiescedb) or die(mysql_error().": ".$update);
		}
	}
	// update dob
	$update = "UPDATE users SET dob = ".GetSQLValueString($_POST['dob'], "date")." WHERE ID = ".$userID;
	$result = mysql_query($update, $aquiescedb) or die(mysql_error().": ".$update);
	//now for the team members if any
	// add original to own group first...
	$update = "UPDATE eventregistration SET withregistrationID = ".$registrationID." WHERE ID = ".$registrationID;			
	$result = mysql_query($update, $aquiescedb) or die(mysql_error().": ".$update);
	
	
	
	
	
	//now for the team members if any
	if(isset($_POST['teamfirstname']) && trim($_POST['teamfirstname'][1])!="") { //team entered
	
		foreach($_POST['teamfirstname'] as $key => $value) {
			$teamuserID = 0; 
			if(isset($value) && trim($value)!="") { // name found
				// folks have a habit of putting their own email in again, so delete
				$_POST['teamemail'][$key] = (!in_array($_POST['teamemail'][$key],$emails)) ? $_POST['teamemail'][$key] : "";
				array_push($emails,$_POST['teamemail'][$key]);
				/* no longer check to see if team members exist already - just add as non-members
				if(isset($_POST['teamemail'][$key]) && $_POST['teamemail'][$key] !="" ) { // has  email 
					$select = "SELECT ID FROM users WHERE email = ".GetSQLValueString($_POST['teamemail'][$key],"text")." LIMIT 1";
					$result = mysql_query($select, $aquiescedb) or die(mysql_error());					
					if(mysql_num_rows($result)>0) {
						$row = mysql_fetch_assoc($result);
						$teamuserID = $row['ID'];
					}		
				} // has email */
				if($teamuserID==0) { // user not in system so create new non user
					$insert = "INSERT INTO users (firstname, surname, email, telephone, dob, usertypeID, addedbyID, dateadded) VALUES (".GetSQLValueString(ucwords($_POST['teamfirstname'][$key]), "text").",".GetSQLValueString(ucwords($_POST['teamsurname'][$key]), "text").",".GetSQLValueString($_POST['teamemail'][$key], "text").",".
					GetSQLValueString($_POST['teamphone'][$key], "text").",".GetSQLValueString($_POST['teamdob'][$key], "date").",-1,".$userID.",NOW())";
					$result = mysql_query($insert, $aquiescedb) or die(mysql_error().": ".$insert);
					$teamuserID = mysql_insert_id();
				}
				// add postcode if given
				if(isset($_POST['teampostcode'][$key]) && trim($_POST['teampostcode'][$key]) !="") {
					$locID = createLocation(false,0,GetSQLValueString($_POST['teampostcode'][$key], "text"),"","","","","","",GetSQLValueString($_POST['teampostcode'][$key], "text"));
					addUserToLocation($teamuserID, $locID, $teamuserID);
				}
				
				// insert into registration
				$insert = "INSERT INTO eventregistration (eventID, userID, registrationtshirt, registrationmedical, withregistrationID, registrationteamname, registrationterms, statusID, createdbyID, createddatetime)  VALUES (".GetSQLValueString($_POST['eventID'], "int").",".$teamuserID.",".GetSQLValueString($_POST['teamtshirt'][$key], "int").",".GetSQLValueString($_POST['teammedical'][$key],"text").",".$registrationID.",".GetSQLValueString($_POST['registrationteamname'], "text").",1,".$_POST['statusID'].",".$userID.",NOW())";
				$result = mysql_query($insert, $aquiescedb) or die(mysql_error().": ".$insert);
				$subregistrationID = mysql_insert_id();
				array_push($pendingNumber['ID'], $subregistrationID);
				array_push($pendingNumber['name'], $_POST['teamfirstname'][$key]." ".$_POST['teamsurname'][$key]);
				if(isset($row_rsEvent['registrationgroupID'])) { // add to group if required
		 			addUsertoGroup($teamuserID,$row_rsEvent['registrationgroupID'],$createdbyID);
	 			} // end add to group
			}// end name found
		} // end for each
	} // end team entered
	//writeLog("Lock tables");
	// assign numbers
	mysql_select_db($database_aquiescedb, $aquiescedb);
	mysql_query("LOCK TABLES eventregistration, eventregistration AS x,eventregistration AS r, eventregistration AS m WRITE;");
	
	
	$number = findNextAvailable(count($pendingNumber['ID']), intval($_POST['eventID']), $row_rsEvent['registrationsequential']);
	
	
	foreach($pendingNumber['ID'] as $key=>$value) {
		$update = "UPDATE eventregistration SET registrationnumber = ".intval($number)." WHERE ID = ".intval($value);
		$result = mysql_query($update, $aquiescedb) or die(mysql_error().": ".$update);
		array_push($pendingNumber['number'], $number);
		$number++;
	}
	mysql_select_db($database_aquiescedb, $aquiescedb);	
	mysql_query("UNLOCK TABLES;");
	//writeLog("Mail ");
	// mail folks
	
	$eventdate = date('jS F Y',strtotime($row_rsEvent['startdatetime']));
		
	$eventtime = "";
	if($row_rsEvent['allday']!=1) {
	   $eventtime .= date(' - g.ia',strtotime($row_rsEvent['startdatetime']));
	}
		
	$to = "";	
	$bcc = "";
	if(isset($row_rsEventPrefs['registrationalertemail'])) { // mail admin		
		$to = $row_rsEventPrefs['registrationalertemail'];
		
	}
	if($row_rsEvent['registrationalertemail']==1 && isset($row_rsEvent['registrationadminemail'])) {
			$to .= trim($to) =="" ? "" : ", ";
			$to .= $row_rsEvent['registrationadminemail'];
	}
	$protocol = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == "on") ? "https://" : "http://";
	
	if($to!="") {
		$subject = $row_rsEvent['eventgrouptitle']." (".$eventdate.") online sign up";
		$message = "There has been an online sign up to the event:\n\n".$row_rsEvent['eventgrouptitle']." on ".$eventdate." ".$eventtime."\n\n";
		if(isset($_POST['surname'])) {			
			$message.= "NAME: ".$_POST['firstname']." ".$_POST['surname'];
			$message.= isset($_POST['telephone1']) ? " T:".$_POST['telephone1'] : "";
			$message.= isset($_POST['email']) ? " E:".$_POST['email'] : "";
			$message.= "\n";
		}
		if(isset($_POST['teamfirstname']) && trim($_POST['teamfirstname'][1])!="") { //team entered
	
			foreach($_POST['teamfirstname'] as $key => $value) {
				$message.= "NAME: ".$_POST['teamfirstname'][$key]." ".$_POST['teamsurname'][$key];
			$message.= isset($_POST['teamphone'][$key]) ? " T:".$_POST['teamphone'][$key] : "";
			$message.= isset($_POST['teamemail'][$key]) ? " E:".$_POST['teamemail'][$key] : "";
			$message.= "\n";
			}
		}
		$message.= "\n";
		if($row_rsEventPrefs['registrationalertincludelink']==1) {		
			$message .= "You can view all registrants using the link below:\n\n";
			$message .= $protocol.$_SERVER['HTTP_HOST']."/calendar/admin/registration/event.php?eventID=".$row_rsEvent['ID'];
		}
	
		
		sendMail($to,$subject,$message,"","","","",true,"",$bcc);
		
		
	}
	
	
	if($row_rsEvent['registrationemail']==1) { // mail client		
		if(isset($row_rsEvent['enddatetime']) && $row_rsEvent['enddatetime'] != $row_rsEvent['startdatetime']) {
			$enddate = "";		
			if(date('Y-m-d', strtotime($row_rsEvent['startdatetime'])) != date('Y-m-d', strtotime($row_rsEvent['enddatetime']))) { 
				$enddate .=  date('jS F Y',strtotime($row_rsEvent['enddatetime'])); 
			} 	
			if($row_rsEvent['allday']!=1) {
				$enddate .=  date(' - g.ia',strtotime($row_rsEvent['enddatetime']));
			}			
		}
		
		
		$to = $_POST['email'];
		$bcc = ""; 			
		$eventlink = $protocol.$_SERVER['HTTP_HOST']."/calendar/registration/payment.php?eventID=" . $row_rsEvent['ID'] . "&registrationID=".$registrationID."\n\n";
		$merges = array("eventname"=>$row_rsEvent['eventgrouptitle'],
			"eventdate"=>$eventdate,
			"eventtime"=>$eventtime,
			"eventlink"=>$eventlink);
		if($row_rsEvent['registrationemailtemplateID']>0) { // template message 
			$select = "SELECT * FROM groupemailtemplate WHERE ID = ".$row_rsEvent['registrationemailtemplateID'];
			$result = mysql_query($select, $aquiescedb) or die(mysql_error().": ".$select);
			$row = mysql_fetch_assoc($result);		
			$htmlmessage = mailMerge($row['templateHTML'] ,"", "", $merges);		
			sendMail($to,$row['templatesubject'],"","","",$htmlmessage,"", true);
		} else { // simple message
			$datedescription = $eventdate." ".$eventtime;
			$datedescription .= ($enddate=="") ? "" : " to ".$enddate;
			$subject = $row_rsEvent['eventgrouptitle']." ";
			
			if($row_rsEvent['surveyID']>0 || $row_rsEvent['registrationcost']>0) {
				$subject .= $row_rsEvent['eventgrouptitle']." - ACTION REQUIRED";
			}
			
			$message = "Dear ".$_POST['firstname'].",\n\n";
			$message .= "Thank you, we have received your event sign up for:\n\n";
			$message .= $row_rsEvent['eventgrouptitle'].", ".$datedescription."\n\n";
			$message .= "More details here: ";
			$message .= $protocol.$_SERVER['HTTP_HOST']."/calendar/event.php?eventID=".$row_rsEvent['ID']."\n\n";
			if($row_rsEvent['surveyID']>0) {
				$message .= "IMPORTANT: If you haven't done so already you can answer the registration survey using the link below:\n\n";
				
				$message .= $protocol.$_SERVER['HTTP_HOST']."/surveys/survey.php?surveyID=".$row_rsEvent['surveyID']."&registrationID=".$registrationID."\n\n";
			}
			if($row_rsEvent['registrationautoaccept'] == 1) {
				$message .= ($row_rsEvent['registrationcost'] ==0) ? "You are now successfully registered for this event.\n\n" : "IMPORTANT: Once your payment has been processed you will receive an email confirming your registration for this event.\n\n";
			}
			
			if($row_rsEvent['registrationpayment']>0) {
				$message .= "Remember, you are not fully registered for the event until payment is received. If you still need to make payment for this event, please click on the link below:\n\n";
				$message .=  $eventlink."\n\n";
			}
			if($row_rsEvent['registrationemailnumbers']>0) {
				$message .= "Your registration number(s):\n\n";
				foreach($pendingNumber['number'] as $key => $value) {
					$message .= $value.": ".$pendingNumber['name'][$key]."\n ";
				} // end for each
			}
			if($row_rsEventPrefs['allowcancelregistration']==1) { 
				$message .= "If you need to cancel your attendance at any time, please click on the link below:\n\n";
			
				$message .=  $protocol.$_SERVER['HTTP_HOST']."/calendar/registration/confirm.php?registrationID=".$registrationID."&token=".$token."\n\n";
			}
			
			$message .= isset($row_rsEvent['registrationemailmessage']) ? $row_rsEvent['registrationemailmessage'] : "";
			$message = mailMerge($message ,"", "", $merges);
			sendMail($to,$subject,$message,"","","","",true,"",$bcc);
		
		}
		
	}
	
	// is the maximum reached?
	
	if($row_rsEvent['registrationmax']>0 && $number>=$row_rsEvent['registrationmax']) {
		$to = $row_rsEvent['registrationadminemail'];
		$subject = $row_rsEvent['eventgrouptitle']." ALERT - registration maximum reached";
		$message = "This is an automated message to inform you that the maximum number of registrants (".$row_rsEvent['registrationmax'].") has been reached for this event.\n\n";
		$message .= (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == "on") ? "https://" : "http://";
		$message .= $_SERVER['HTTP_HOST']."/calendar/admin/registration/event.php?eventID=".$row_rsEvent['ID'];
		sendMail($to,$subject,$message);
	}
	
	if(isset($row_rsEvent['registrationconfirmationURL']) && $row_rsEvent['registrationconfirmationURL']!="") { 
		$insertGoTo = $row_rsEvent['registrationconfirmationURL'];
	} else {	
		$msg = urlencode($row_rsEventPrefs['text_received']);
  		$insertGoTo = "/calendar/registration/confirm.php?registrationID=".$registrationID."&token=".$token."&msg=".$msg;
  		if (isset($_SERVER['QUERY_STRING'])) {
    		$insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
    		$insertGoTo .= $_SERVER['QUERY_STRING'];
  		}
	}
	
	
	//go to survey?
	if($row_rsEvent['surveyID']>0) {
		$insertGoTo = "/surveys/survey.php?surveyID=" . $row_rsEvent['surveyID'] . "&registrationID=".$registrationID."&returnURL=".urlencode($insertGoTo);
	} 
	
	// payment needed?
	if($row_rsEvent['registrationpayment']>0) {
		$insertGoTo = "payment.php?eventID=" . $row_rsEvent['ID'] . "&registrationID=".$registrationID."&returnURL=".urlencode($insertGoTo);
	} 
	
	
  header(sprintf("Location: %s", $insertGoTo)); exit;
}






$varEventID_rsTotalResgistrants = "-1";
if (isset($_GET['eventID'])) {
  $varEventID_rsTotalResgistrants = $_GET['eventID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsTotalResgistrants = sprintf("SELECT COUNT(eventregistration.ID) AS numRegistered FROM eventregistration WHERE eventregistration.eventID = %s", GetSQLValueString($varEventID_rsTotalResgistrants, "int"));
$rsTotalResgistrants = mysql_query($query_rsTotalResgistrants, $aquiescedb) or die(mysql_error());
$row_rsTotalResgistrants = mysql_fetch_assoc($rsTotalResgistrants);
$totalRows_rsTotalResgistrants = mysql_num_rows($rsTotalResgistrants);

$varRegionID_rsDiscovered = "1";
if (isset($regionID)) {
  $varRegionID_rsDiscovered = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsDiscovered = sprintf("SELECT * FROM discovered WHERE statusID = 1 AND regionID = %s ORDER BY ordernum", GetSQLValueString($varRegionID_rsDiscovered, "int"));
$rsDiscovered = mysql_query($query_rsDiscovered, $aquiescedb) or die(mysql_error());
$row_rsDiscovered = mysql_fetch_assoc($rsDiscovered);
$totalRows_rsDiscovered = mysql_num_rows($rsDiscovered);

$body_class = isset($body_class) ? $body_class : "";
$body_class .= " eventcategory".$row_rsEvent['categoryID'];


?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = $row_rsEvent['eventgrouptitle']." Registration"; echo $pageTitle." | ".$site_name; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" --><link href="/calendar/css/calendarDefault.css" rel="stylesheet"  />
<script> 

document.write("<style> #link_addmore { display: table-row-group; }</style><style> #extrarows { display: none; }</style>");

function addMore() {
	
	if(navigator.appName.indexOf("Microsoft") > -1){
var canSee = 'block'
} else {
var canSee = 'table-row-group';
}
	
	document.getElementById('extrarows').style.display = canSee;
}

function toggleMedical(row) {
	
	if(navigator.appName.indexOf("Microsoft") > -1){
var canSee = 'block'
} else {
var canSee = 'table-cell';
}


	if(document.getElementById('displayMedical'+row).style.display == "none") {
		document.getElementById('displayMedical'+row).style.display = canSee;
	} else {
		document.getElementById('displayMedical'+row).style.display = "none";
	}
}


function validateForm() {
	errors ="";
 if(<?php echo $row_rsEvent['registrationdob']; ?> == 1 && document.getElementById('dob').value == "") {
	 errors +="Please enter your date of birth.\n";
 }
 if(<?php echo $row_rsEvent['registrationasktelephone']; ?> == 1 &&  document.getElementById('telephone1').value == "" && document.getElementById('telephone2').value == "") {
	 errors +="Please enter at least one telephone contact number.\n";
 }
 if(<?php echo $row_rsEvent['takenpartbefore']; ?> == 1 && getRadioValue('takenpartbefore') == null) {
	 errors +="Please enter whether you have taken part before.\n";
 }
 if(<?php echo $row_rsEvent['registrationtshirt']; ?> == 1 && getRadioValue('registrationtshirt') == null) {
	 errors +="Please enter your t-shirt size.\n";
 }
 if(<?php echo $row_rsEvent['registrationmarketing']; ?> == 1 && getRadioValue('registrationmarketing') == null) {
	 errors +="Please state whether you would like to be on our mailing list.\n";
 }
 if(document.getElementById("registrationterms") && !document.getElementById('registrationterms').checked) {
	 errors +="To register for this event, you must check box to agree with terms.\n";
 }
 if(<?php echo $totalRows_rsStartTimes; ?> > 1 && <?php echo $row_rsEvent['registrationchoosestarttime']; ?> == 1 && document.getElementById('registrationstarttime').value == "") {
	 errors +="Please enter your preferred start time.\n";
 }
 if(errors=="") {
	 alert("Your form will now be submitted. It may take a short while to process your registration.");
 }
 
return errors;
} 



</script>
<script src="/core/scripts/formUpload.js"></script>


<style >
<!--
<?php if(isset($row_rsLoggedIn['ID']))  {
	echo ".notregistered { display: none; } ";
} else { 
	echo ".registered { display: none; } ";
} ?>
<?php if($row_rsEvent['registrationdob']!=1) { echo "#rowDOB, .columnDOB { display: none; }"; } ?>
<?php if($row_rsEvent['registrationteamname']!=1) { echo "#rowTeamname { display: none; }"; } ?>
<?php if($row_rsEvent['registrationtshirt']!=1) { echo "#rowTshirt, .columnTshirt { display: none; }"; } ?>
<?php if($row_rsEvent['registrationtime']!=1) { echo "#rowPredictedtime { display: none; }"; } ?>
<?php if($row_rsEvent['registrationwheelchair']!=1) { echo "#rowWheelchair { display: none; }"; } ?>

<?php if($row_rsEvent['registrationinfo']!=1) { echo "#extraquestion { display: none; }"; } ?>
<?php if(trim($row_rsEvent['registrationextraquestion2'])=="") { echo "#extraquestion2 { display: none; }"; } ?>
<?php if(trim($row_rsEvent['registrationextraquestion3'])=="") { echo "#extraquestion3 { display: none; }"; } ?>
<?php if($row_rsEvent['registrationextracompulsary']==0) { echo "#extraquestion .red { display: none; }"; } ?>
<?php if($row_rsEvent['registrationextracompulsary2']==0) { echo "#extraquestion2 .red { display: none; }"; } ?>
<?php if($row_rsEvent['registrationextracompulsary3']==0) { echo "#extraquestion3 .red { display: none; }"; } ?>
<?php if($row_rsEvent['registrationmedical']!=1) { echo ".displayMedical { display: none; }"; } ?>
<?php if($row_rsEvent['registrationaskjobtitle']!=1) { echo ".jobtitle { display: none; }"; } ?>
<?php if($row_rsEvent['registrationaskaddress']!=1) { echo ".address, .columnPostCode { display: none; }"; } ?>
<?php if($row_rsEvent['registrationasktelephone']!=1) { echo ".telephone, .columnTelephone { display: none; }"; } ?>

<?php if($row_rsEvent['registrationaskcompany']!=1) { echo ".company { display: none; }"; } ?>

<?php if($row_rsEvent['registrationdietryreq']!=1) { echo ".displayDietry { display: none; }"; } ?>
<?php if($row_rsEvent['registrationspecialreq']!=1) { echo ".displaySpecial { display: none; }"; } ?>
<?php if($row_rsEvent['takenpartbefore']!=1) { echo "#rowTakenpartbefore { display: none; }"; } ?>
<?php if($row_rsEvent['registrationdiscovered']!=1) { echo "#rowDiscovered { display: none; }"; } ?>
<?php if($totalRows_rsStartTimes<2 || $row_rsEvent['registrationchoosestarttime']==0) { echo "#rowStarttime { display: none; }"; } ?>
#groupmembers .rowMedical {
	display:none;
}


-->
</style>

<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" --> 
  <div class="container registration" id="registrationPage">
    <h1><?php $row_rsEventPrefs['text_register']; ?></h1>
    <h2>Event: <?php echo $row_rsEvent['eventgrouptitle']; ?></h2>
    
    <?php if($row_rsEvent['registrationmulti'] != 1 && $totalRows_rsAlreadyRegistered>=1 && $_SESSION['MM_UserGroup']<8) { ?>
    <p>You have already registered for this event. Please contact us if you have any queries.</p>
    <?php } else if($_SESSION['MM_UserGroup']<7 && ($row_rsEvent['registration']!=1 || (isset($row_rsEvent['startdatetime']) && $row_rsEvent['startdatetime'] < date('Y-m-d H:i:s')) || (isset($row_rsEvent['registrationstart']) && $row_rsEvent['registrationstart'] > date('Y-m-d H:i:s')) || (isset($row_rsEvent['registrationend']) && $row_rsEvent['registrationend'] < date('Y-m-d H:i:s')) || ($row_rsEvent['registrationmax'] >0 && $row_rsTotalResgistrants['numRegistered'] >= $row_rsEvent['registrationmax']))) { ?>
    <p>Unfortunately this event is now full.</p>
    <?php } else { // ok to register
	if($submit_error!="") { ?>
    <p class="alert alert-danger" role="alert"><?php echo $submit_error; ?></p>
    
    <?php } ?>
    <h3>Your details:</h3>
    <form action="<?php echo $editFormAction; ?>" method="post" name="event-registration-form" id="event-registration-form" role="form" class="registration-form">
      <table class="main-registrant">
        <tr>
          <td align="right" >First name:</td>
          <td><input name="userID" type="hidden" id="userID" value="<?php echo isset($row_rsLoggedIn['ID']) ? $row_rsLoggedIn['ID'] : ""; ?>" /><span class="registered"><?php echo isset($row_rsLoggedIn['firstname'])  ? $row_rsLoggedIn['firstname'] : ""; ?> </span><span class="notregistered"><input name="firstname" type="text" value="<?php echo isset($_POST['firstname']) ? htmlentities($_POST['firstname'], ENT_COMPAT, "UTF-8") : (isset($row_rsLoggedIn['firstname'])  ? $row_rsLoggedIn['firstname'] : ""); ?>" /></span> </td>
          </tr>
          
          
           <tr>
          <td align="right" >Surname:</td>
          <td><span class="registered"><?php echo isset($row_rsLoggedIn['surname']) ? $row_rsLoggedIn['surname'] : ""; ?></span><span class="notregistered"><input name="surname" type="text" value="<?php echo isset($_POST['surname']) ? htmlentities($_POST['surname'], ENT_COMPAT, "UTF-8") : (isset($row_rsLoggedIn['surname']) ? $row_rsLoggedIn['surname'] : ""); ?>" /></span></td>
          </tr>
          
          
          
        <tr>
          <td align="right" >email<span class="notregistered red">&#42;</span>:</td>
          <td><span class="registered"><?php echo $row_rsLoggedIn['email']; ?></span><span class="notregistered"><input name="email" type="email" multiple  value="<?php echo isset($_POST['email']) ? htmlentities($_POST['email'], ENT_COMPAT, "UTF-8") : isset($row_rsLoggedIn['email']) ? $row_rsLoggedIn['email'] : ""; ?>" /></span></td>
          </tr><tr class="jobtitle">
          <td align="right" ><label for="registrationjobtitle">Job title:</label></td>
          <td><input name="registrationjobtitle" type="text" id="registrationjobtitle" size="50" maxlength="50"  value="<?php echo isset($_REQUEST['registrationjobtitle']) ? htmlentities($_REQUEST['registrationjobtitle'], ENT_COMPAT, "UTF-8") : (isset($row_rsLoggedIn['jobtitle']) ? htmlentities($row_rsLoggedIn['jobtitle'], ENT_COMPAT, "UTF-8") : ""); ?>" /></td>
        </tr>
        <tr class="company">
          <td align="right" ><label for="registrationcompany">Company name:</label></td>
          <td><input name="registrationcompany" type="text" id="registrationcompany" size="50" maxlength="50"  value="<?php echo isset($_REQUEST['registrationcompany']) ? htmlentities($_REQUEST['registrationcompany'], ENT_COMPAT, "UTF-8") : (isset($row_rsLoggedIn['name']) ? htmlentities($row_rsLoggedIn['name'], ENT_COMPAT, "UTF-8") : ""); ?>" /></td>
        </tr>
        <tr class="address">
          <td align="right" >Address<span class="red">&#42;</span>:</td>
          <td>
            <input name="address1" type="text"  id="address1" value="<?php echo isset($_REQUEST['address1']) ? htmlentities($_REQUEST['address1'], ENT_COMPAT, "UTF-8") : (isset($row_rsLoggedIn['address1']) ? htmlentities($row_rsLoggedIn['address1'], ENT_COMPAT, "UTF-8") : ""); ?>" size="50" maxlength="50" /><br />
           </td>
          </tr>
        <tr class="address">
          <td align="right" >&nbsp;</td>
          <td><input name="address2" type="text"  id="address2" value="<?php echo isset($_REQUEST['address2']) ? htmlentities($_REQUEST['address2'], ENT_COMPAT, "UTF-8") : (isset($row_rsLoggedIn['address2']) ? htmlentities($row_rsLoggedIn['address2'], ENT_COMPAT, "UTF-8") : ""); ?>" size="50" maxlength="50" /></td>
          </tr>
        <tr class="address">
          <td align="right" >&nbsp;</td>
          <td><input name="address3" type="text"  id="address3" value="<?php echo isset($_REQUEST['address3']) ? htmlentities($_REQUEST['address3'], ENT_COMPAT, "UTF-8") : (isset($row_rsLoggedIn['address3']) ? htmlentities($row_rsLoggedIn['address3'], ENT_COMPAT, "UTF-8") : ""); ?>" size="50" maxlength="50" /></td>
          </tr>
        <tr class="address">
          <td align="right" >&nbsp;</td>
          <td><input name="address4" type="text"  id="address4" value="<?php echo isset($_REQUEST['address4']) ? htmlentities($_REQUEST['address4'], ENT_COMPAT, "UTF-8") : (isset($row_rsLoggedIn['address4']) ? htmlentities($row_rsLoggedIn['address4'], ENT_COMPAT, "UTF-8") : "");?>" size="50" maxlength="50" /></td>
          </tr>
        <tr class="address">
          <td align="right" >Postcode<span class="red">&#42;</span>:</td>
          <td><input name="postcode" type="text"  id="postcode" value="<?php echo isset($_REQUEST['postcode']) ? htmlentities($_REQUEST['postcode'], ENT_COMPAT, "UTF-8") : (isset($row_rsLoggedIn['postcode']) ? htmlentities($row_rsLoggedIn['postcode'], ENT_COMPAT, "UTF-8") : ""); ?>" size="10" maxlength="10" /></td>
          </tr>
        <tr class="telephone">
          <td align="right" >Telephone<span class="red">&#42;</span>:</td>
          <td>
            <input name="telephone1" type="text"  id="telephone1" value="<?php echo isset($_REQUEST['telephone1']) ? htmlentities($_REQUEST['telephone1'], ENT_COMPAT, "UTF-8") : (isset($row_rsLoggedIn['telephone1']) ? htmlentities($row_rsLoggedIn['telephone1'], ENT_COMPAT, "UTF-8") : ""); ?>" size="50" maxlength="50" />
          </td>
          </tr>
        <tr class="telephone mobile">
          <td align="right" >Mobile:</td>
          <td><input name="telephone2" type="text"  id="telephone2" value="<?php echo isset($_REQUEST['telephone2']) ? htmlentities($_REQUEST['telephone2'], ENT_COMPAT, "UTF-8") : (isset($row_rsLoggedIn['telephone2']) ? htmlentities($row_rsLoggedIn['telephone2'], ENT_COMPAT, "UTF-8") : ""); ?>" size="50" maxlength="50" /></td>
          </tr>
        <tr id="rowDOB">
          <td align="right" >Date of birth<span class="red">&#42;</span>:</td>
          <td><input name="dob" type="hidden" id="dob" value="<?php $setvalue = isset($row_rsLoggedIn['dob']) ?  $row_rsLoggedIn['dob'] : ""; echo $setvalue; $inputname = "dob"; $startyear = 1900; ?>" />
            <?php require_once('../../core/includes/datetimeinput.inc.php'); ?></td>
          </tr>
        
        <tr id="rowTakenpartbefore">
          <td align="right" >Have you taken part in <?php echo $row_rsEvent['eventgrouptitle']; ?> before?<span class="red">&#42;</span></td>
          <td>
            <label>
              <input type="radio" name="takenpartbefore" value="1" id="takenpartbefore_0" <?php if(isset($_REQUEST['takenpartbefore']) && $_REQUEST['takenpartbefore'] ==1) echo "checked=\"checked\""; ?> />
              Yes</label>
            &nbsp;&nbsp;&nbsp;
            <label>
              <input type="radio" name="takenpartbefore" value="0" id="takenpartbefore_1"  <?php if(isset($_REQUEST['takenpartbefore']) && $_REQUEST['takenpartbefore'] ==0) echo "checked=\"checked\""; ?>/>
              No</label>
            </td>
          </tr>
        <tr id="rowTeamname">
          <td align="right" >What  team or organisation are you taking part with (if any)?:</td>
          <td><input name="registrationteamname" type="text"  id="registrationteamname" size="25" maxlength="50" value="<?php echo isset($_REQUEST['registrationteamname']) ? htmlentities($_REQUEST['registrationteamname'], ENT_COMPAT, "UTF-8") : ""; ?>" /></td>
          </tr>
        <tr id="rowTshirt">
          <td align="right" >T-shirt size<span class="red">&#42;</span>:</td>
          <td><!--<label><input  type="radio" name="registrationtshirt" id="registrationtshirt1" value="1" <?php if(isset($_REQUEST['registrationtshirt']) && $_REQUEST['registrationtshirt'] ==1) echo "checked=\"checked\""; ?>  />
            Extra small (under 12s)
  <br /></label>-->
            <label><input  type="radio" name="registrationtshirt" id="registrationtshirt2" value="2" <?php if(isset($_REQUEST['registrationtshirt']) && $_REQUEST['registrationtshirt'] ==2) echo "checked=\"checked\""; ?>  />
              Small
              
              <br /></label>
            <label><input  type="radio" name="registrationtshirt" id="registrationtshirt3" value="3" <?php if(isset($_REQUEST['registrationtshirt']) && $_REQUEST['registrationtshirt'] ==3) echo "checked=\"checked\""; ?>  />
              Medium
              
              <br /></label>
            <label><input  type="radio" name="registrationtshirt" id="registrationtshirt4" value="4" <?php if(isset($_REQUEST['registrationtshirt']) && $_REQUEST['registrationtshirt'] ==4) echo "checked=\"checked\""; ?> />
              Large
              
              <br /></label>
            <label><input  type="radio" name="registrationtshirt" id="registrationtshirt5" value="5"<?php if(isset($_REQUEST['registrationtshirt']) && $_REQUEST['registrationtshirt'] ==5) echo "checked=\"checked\""; ?>  />
              Extra large</label>
            </td>
          </tr> <tr id="rowStarttime">
          <td align="right" >Prefrerred start time<span class="red">&#42;</span>:</td>
          <td><select name="registrationstarttime" id="registrationstarttime">
            <option value="" <?php if (!(strcmp("", @$_REQUEST['registrationstarttime']))) {echo "selected=\"selected\"";} ?>><?php echo isset($region['text_choose']) ? htmlentities($region['text_choose'], ENT_COMPAT, "UTF-8") : "Choose..." ?></option>
            <?php
do {  
?>
            <option value="<?php echo $row_rsStartTimes['ID']?>"<?php if (!(strcmp($row_rsStartTimes['ID'], @$_REQUEST['registrationstarttime']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsStartTimes['starttime']?></option>
            <?php
} while ($row_rsStartTimes = mysql_fetch_assoc($rsStartTimes));
  $rows = mysql_num_rows($rsStartTimes);
  if($rows > 0) {
      mysql_data_seek($rsStartTimes, 0);
	  $row_rsStartTimes = mysql_fetch_assoc($rsStartTimes);
  }
?>
          </select></td>
        </tr>
        <tr id="rowPredictedtime">
          <td align="right" >Predicted time:</td>
          <td><input name="registrationtime" type="text"  id="registrationtime" value="<?php echo isset($_REQUEST['registrationtime']) ? htmlentities($_REQUEST['registrationtime'], ENT_COMPAT, "UTF-8") : ""; ?>" size="20" maxlength="20" /></td>
          </tr>
       
        <tr id="rowWheelchair">
          <td align="right" >Wheelchair entrants only. Will you participate in a... </td>
          <td><label><input  type="radio" name="registrationwheelchair" id="registrationwheelchair1" value="1" <?php if(isset($_REQUEST['registrationwheelchair']) && $_REQUEST['registrationwheelchair'] ==1) echo "selected=\"selected\""; ?> /> Specially adapted racing chair</label><br />
            <label><input  type="radio" name="registrationwheelchair" id="registrationwheelchair2" value="2" <?php if(isset($_REQUEST['registrationwheelchair']) && $_REQUEST['registrationwheelchair'] ==2) echo "selected=\"selected\""; ?> /> Self propelled chair</label><br />
            <label><input  type="radio" name="registrationwheelchair" id="registrationwheelchair3" value="3" <?php if(isset($_REQUEST['registrationwheelchair']) && $_REQUEST['registrationwheelchair'] ==3) echo "selected=\"selected\""; ?> /> Pushed / Escorted in wheelchair</label><br /><label><input  type="radio" name="registrationwheelchair" id="registrationwheelchair0" value="0" <?php if(isset($_REQUEST['registrationwheelchair']) && $_REQUEST['registrationwheelchair'] ==0) echo "selected=\"selected\""; ?> /> None of these</label>
            </td>
          </tr>
        <tr class="displayMedical">
          <td align="right" ><label for="registrationmedical">Please list any medical conditions that you have that the organiser should know about. Information will be treated in the strictest confidence:</label></td>
          <td><textarea name="registrationmedical" id="registrationmedical" cols="45" rows="5"><?php echo isset($_REQUEST['registrationmedical']) ? htmlentities($_REQUEST['registrationmedical'], ENT_COMPAT, "UTF-8") : ""; ?></textarea></td>
          </tr>
        <tr class="displayDietry">
          <td align="right" ><label for="registrationdietryreq">Do you have any dietry requirements? If so, list them here:</label></td>
          <td><textarea name="registrationdietryreq" id="registrationdietryreq" cols="45" rows="5"><?php echo isset($_REQUEST['registrationdietryreq']) ? htmlentities($_REQUEST['registrationdietryreq'], ENT_COMPAT, "UTF-8") : ""; ?></textarea></td>
        </tr>
        <tr class="displaySpecial">
          <td align="right" ><label for="registrationspecialreq">Do you have any other special requirements? If so, list them here:</label></td>
          <td><textarea name="registrationspecialreq" id="registrationspecialreq" cols="45" rows="5"><?php echo isset($_REQUEST['registrationspecialreq']) ? htmlentities($_REQUEST['registrationspecialreq'], ENT_COMPAT, "UTF-8") : ""; ?></textarea></td>
        </tr>
        <tr id ="extraquestion">
          <td align="right" ><label for="registrationinfo"><?php echo isset($row_rsEvent['registrationextraquestion']) ? nl2br($row_rsEvent['registrationextraquestion']) : "Do you have any other information we should know?<br />For example: what is your reason for taking part? Are you bringing pets, buggies, etc?"; ?>:</label><span class="red">&#42;</span></td>
          <td><textarea name="registrationinfo" id="registrationinfo" cols="45" rows="5"><?php echo isset($_REQUEST['registrationinfo']) ? htmlentities($_REQUEST['registrationinfo'], ENT_COMPAT, "UTF-8") : ""; ?></textarea></td>
          </tr>
        <tr id="extraquestion2">
          <td align="right" ><?php echo $row_rsEvent['registrationextraquestion2']; ?><span class="red">&#42;</span></td>
          <td><textarea name="registrationinfo2" id="registrationinfo2" cols="45" rows="5"><?php echo isset($_REQUEST['registrationinfo2']) ? htmlentities($_REQUEST['registrationinfo2'], ENT_COMPAT, "UTF-8") : ""; ?></textarea></td>
        </tr>
        <tr id="extraquestion3">
          <td align="right" ><?php echo $row_rsEvent['registrationextraquestion3']; ?><span class="red">&#42;</span></td>
          <td><textarea name="registrationinfo3" id="registrationinfo3" cols="45" rows="5"><?php echo isset($_REQUEST['registrationinfo3']) ? htmlentities($_REQUEST['registrationinfo3'], ENT_COMPAT, "UTF-8") : ""; ?></textarea></td>
        </tr>
        <tr id="rowDiscovered">
          <td align="right" >How did you find out about this event?</td>
          <td><select name="registrationdiscovered" id="registrationdiscovered">
            <option value="0" <?php if (!(strcmp("", @$_REQUEST['registrationdiscovered']))) {echo "selected=\"selected\"";} ?>><?php echo isset($region['text_choose']) ? htmlentities($region['text_choose'], ENT_COMPAT, "UTF-8") : "Choose..." ?></option>
            <?php
do {  
?>
            <option value="<?php echo $row_rsDiscovered['ID']?>"<?php if (!(strcmp($row_rsDiscovered['ID'], @$_REQUEST['registrationdiscovered']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsDiscovered['description']?></option>
            <?php
} while ($row_rsDiscovered = mysql_fetch_assoc($rsDiscovered));
  $rows = mysql_num_rows($rsDiscovered);
  if($rows > 0) {
      mysql_data_seek($rsDiscovered, 0);
	  $row_rsDiscovered = mysql_fetch_assoc($rsDiscovered);
  }
?>
            </select></td>
          </tr>
        
        </table>
      <?php if(intval($row_rsEvent['registrationteam'])>0) { ?>
      <div id="groupmembers">
      <h3>Details of any other members of your party/team</h3>
      <p> Enter details  of <em><strong>anyone else</strong></em> you are registering on behalf of (e.g. family, friends or team member -<strong> do not </strong>enter yourself or email again). Enter as much information as you have available.<span  class="displayMedical"> Click on the first aid (<img src="../images/first_aid_icon.gif" alt="Medical details" width="16" height="16" style="vertical-align:
middle;" />) icon to add any medical conditions we should know about.</span></p>
      <table class="team-registration">
      <thead>
        <tr>
          <th>First name:</th>
          <th>Surname:</strong></th>
          <th  class="columnDOB">Date of birth:</strong></th>
            <th  class="columnEmail">Email<br />
            (if known/different):</th>
            <th  class="columnTelephone">Phone:</th>
              <th  class="columnPostCode">Postcode<br />
                (if different):</th>
          <th  class="columnTshirt">T-shirt<br />
            size:</th>
          <th  class="displayMedical"  >&nbsp;</th>
          </tr>
         </thead>
        <tbody>
        <?php for($i=1; $i<=$row_rsEvent['registrationteam']; $i++) { 
		if ($i==5 && $row_rsEvent['registrationteam']>7) { ?>
        </tbody><tbody id="link_addmore">
        <tr>
          <td colspan="8"><a href="javascript:void(0);" onclick="addMore();">Add more</a></td></tr></tbody><tbody id="extrarows">
		<?php } ?>
        <tr>
          <td><input name="teamfirstname[<?php echo $i; ?>]" type="text"  id="teamfirstname[<?php echo $i; ?>]" size="15" maxlength="50" value="<?php echo isset($_REQUEST['teamfirstname'][$i]) ? htmlentities($_REQUEST['teamfirstname'][$i], ENT_COMPAT, "UTF-8") : ""; ?>" autocomplete="off" /></td>
          <td><input name="teamsurname[<?php echo $i; ?>]" type="text"  id="teamsurname[<?php echo $i; ?>]" size="15" maxlength="50" value="<?php echo isset($_REQUEST['teamsurname'][$i]) ? htmlentities($_REQUEST['teamsurname'][$i], ENT_COMPAT, "UTF-8") : ""; ?>" autocomplete="off"/></td>
          <td class="nowrap columnDOB"><input type="hidden" name="teamdob[<?php echo $i; ?>]" id="teamdob<?php echo $i; ?>" value="<?php $setvalue = isset($_REQUEST['teamdob'][$i]) ? $_REQUEST['teamdob'][$i] : ""; $inputname = "teamdob".$i; $startyear = 1900; $endyear = date('Y'); $shortmonth = true; echo $setvalue; ?>" />
            <?php require('../../core/includes/datetimeinput.inc.php'); ?>
            </td>
          <td class="columnEmail"><input name="teamemail[<?php echo $i; ?>]" type="email"  id="teamemail[<?php echo $i; ?>]" size="15" maxlength="50" value="<?php echo isset($_REQUEST['teamemail'][$i]) ? htmlentities($_REQUEST['teamemail'][$i], ENT_COMPAT, "UTF-8") : ""; ?>" autocomplete="off"/></td>
          <td class="columnTelephone"><input name="teamphone[<?php echo $i; ?>]" type="text"  id="teamphone[<?php echo $i; ?>]" size="15" maxlength="50" value="<?php echo isset($_REQUEST['teamphone'][$i]) ? htmlentities($_REQUEST['teamphone'][$i], ENT_COMPAT, "UTF-8") : ""; ?>" autocomplete="off"/></td>
          <td class="columnPostCode"><input name="teampostcode[<?php echo $i; ?>]" type="text"  id="teampostcode[<?php echo $i; ?>]" size="10" maxlength="50" value="<?php echo isset($_REQUEST['teampostcode'][$i]) ? htmlentities($_REQUEST['teampostcode'][$i], ENT_COMPAT, "UTF-8") : ""; ?>" autocomplete="off"/></td>
          <td class="columnTshirt"><label>
            <select name="teamtshirt[<?php echo $i; ?>]" id="teamtshirt[<?php echo $i; ?>]">
              <option>-</option>
              <option value="1" <?php if(isset($_REQUEST['teamtshirt'][$i]) && $_REQUEST['teamtshirt'][$i] ==1) echo "selected=\"selected\""; ?>>XS</option>
              <option value="2" <?php if(isset($_REQUEST['teamtshirt'][$i]) && $_REQUEST['teamtshirt'][$i] ==2) echo "selected=\"selected\""; ?>>S</option>
              <option value="3" <?php if(isset($_REQUEST['teamtshirt'][$i]) && $_REQUEST['teamtshirt'][$i] ==3) echo "selected=\"selected\""; ?>>M</option>
              <option value="4" <?php if(isset($_REQUEST['teamtshirt'][$i]) && $_REQUEST['teamtshirt'][$i] ==4) echo "selected=\"selected\""; ?>>L</option>
              <option value="5" <?php if(isset($_REQUEST['teamtshirt'][$i]) && $_REQUEST['teamtshirt'][$i] ==5) echo "selected=\"selected\""; ?>>XL</option>
              </select>
            </label></td>
          <td class="displayMedical" ><a href="javascript:void(0);" onclick="toggleMedical(<?php echo $i; ?>)"><img src="../images/first_aid_icon.gif" alt="Medical details" width="16" height="16" /></a></td>
          </tr>
        <tr>
          <td colspan="8" class="rowMedical displayMedical" id="displayMedical<?php echo $i; ?>">Medical conditions:
            <input name="teammedical[<?php echo $i; ?>]" type="text" id="teammedical[<?php echo $i; ?>]" size="50" maxlength="100" autocomplete="off"/></td>
          </tr>
        <?php } ?></tbody>
        </table></div><?php } ?>
       <?php if($row_rsEvent['registrationmarketingtextshow']==1 && trim($row_rsEvent['registrationmarketingtext'])!="") { ?>
       <div class="dataprotection">
      <h3>Data Protection - please note the following:</h3>
      <ol><li><?php echo nl2br($row_rsEvent['registrationmarketingtext']); ?><span class="red">&#42; </span>
        
        
          <label>
            <input type="radio" name="registrationmarketing" value="1" id="registrationmarketing_1" <?php if (isset($_REQUEST['registrationmarketing']) && $_REQUEST['registrationmarketing']==1) {echo "checked=\"checked\"";} ?> />
            
            Yes</label>&nbsp;&nbsp;&nbsp;
          
          <label>
            <input type="radio" name="registrationmarketing" value="0" id="registrationmarketing_0"<?php if (isset($_REQUEST['registrationmarketing']) && $_REQUEST['registrationmarketing']==0) {echo "checked=\"checked\"";} ?> />
            No</label>
          
        </p>
      </li><?php } ?>
      <?php if($row_rsEvent['registrationtermstextshow']==1 && trim($row_rsEvent['registrationtermstext'])!="") { ?>
        <li><?php echo nl2br($row_rsEvent['registrationtermstext']); ?>
         <br /> <label>Check box to agree to these terms:<span class="red">&#42; </span>
          
<input type="checkbox" name="registrationterms" id="registrationterms" value="1" <?php if (isset($_REQUEST['registrationterms']) && $_REQUEST['registrationterms']==1) {echo "checked=\"checked\"";} ?> /></label>
        </li>
      </ol></div><?php } ?>
      
      <input name="eventID" type="hidden" id="eventID" value="<?php echo $row_rsEvent['ID']; ?>" />
      <input name="categoryID" type="hidden" id="categoryID" value="<?php echo $row_rsEvent['categoryID']; ?>" />
      <input name="createdbyID" type="hidden" id="createdbyID" value="<?php echo isset($row_rsLoggedIn['ID'] ) ? $row_rsLoggedIn['ID'] : 0; ?>" />
      <input name="createddatetime" type="hidden" id="createddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
      
      <input type="hidden" name="MM_insert" value="event-registration-form" />
      <input type="hidden" name="statusID" id="statusID" />
      <div> <p id="submitpara"><input type="submit" class="button"  value="Submit" />&nbsp;&nbsp;&nbsp;<span class="red">&#42; </span>Mandatory fields</p></div>
      </form>
  
    <?php } // ok to register ?>
  </div>
  
  <!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsEvent);

mysql_free_result($rsAlreadyRegistered);

mysql_free_result($rsTotalResgistrants);

mysql_free_result($rsDiscovered);

mysql_free_result($rsEventPrefs);

mysql_free_result($rsStartTimes);

mysql_free_result($rsLoggedIn);
?>
