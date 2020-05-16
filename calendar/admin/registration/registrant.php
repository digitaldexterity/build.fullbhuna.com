<?php require_once('../../../Connections/aquiescedb.php'); ?>
<?php require_once('../../../members/includes/userfunctions.inc.php'); ?>
<?php require_once('../../registration/includes/registration.inc.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "8,9,10";
$MM_donotCheckaccess = "false";

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
    if (($strUsers == "") && false) { 
      $isValid = true; 
    } 
  } 
  return $isValid; 
}

$MM_restrictGoTo = "/login/index.php?notloggedin=true";
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
?>
<?php
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



if(isset($_GET['deleteID']) && intval($_GET['deleteID'])>0) {
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$delete = "DELETE FROM eventregistration WHERE ID = ".intval($_GET['deleteID'])." OR withregistrationID = ".intval($_GET['deleteID']);
	mysql_query($delete, $aquiescedb) or die(mysql_error());
	
}


$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}


if(isset($_POST['teamsurname']) && trim($_POST['teamsurname']) !="") {
	$userID = completeAddUser("",$_POST['teamfirstname'], $_POST['teamsurname'], $_POST['teamemail'], 0, 1,1, $_POST['registrationgroupID'], $row_rsLoggedIn['ID'], true, false, "", "", "", "", "", "", "", $_POST['teampostcode'], "", "", "", "", "", "", "","","","",$_POST['teamdob']);
	if(intval($userID)>0) {
		//mysql_query("LOCK TABLES eventregistration, eventregistration AS x,eventregistration AS r, eventregistration AS m WRITE;");
		$number = 	findNextAvailable(1, $_GET['eventID'], $_POST['registrationsequential']);
		
		$insert = "INSERT INTO eventregistration (eventID, userID, registrationtshirt, registrationmedical, withregistrationID, registrationteamname, registrationterms, registrationnumber, statusID, createdbyID, createddatetime)  VALUES (".
																																																											  GetSQLValueString($_GET['eventID'], "int").",".
																																																											  intval($userID).",".GetSQLValueString($_POST['teamtshirt'], "int").
																																																											  ",'',".GetSQLValueString($_POST['mainregistrantID'], "int").",".GetSQLValueString($_POST['registrationteamname'], "text").",1,".GetSQLValueString($number, "int").",".$_POST['statusID'].",".$row_rsLoggedIn['ID'].",NOW())";
		$result = mysql_query($insert, $aquiescedb) or die(mysql_error());
		//mysql_query("UNLOCK TABLES;");
				
	}
	//reload page
	header("location: /calendar/admin/registration/registrant.php?eventID=".$_GET['eventID']); exit;
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {	
$_POST['registrationtshirt'] = isset($_POST['registrationtshirt']) ? $_POST['registrationtshirt'] : "";
$_POST['registrationwheelchair'] = isset($_POST['registrationwheelchair']) ? $_POST['registrationtshirt'] : "";
	// if users name has changed
	if($_POST['firstname'].$_POST['surname'] != $_POST['oldfirstname'].$_POST['oldsurname']) {
	// insert new user
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$insert = "INSERT INTO users (firstname, surname, email, dob, usertypeID, addedbyID, dateadded) VALUES (".GetSQLValueString($_POST['firstname'], "text").",".GetSQLValueString($_POST['surname'], "text").",".GetSQLValueString($_POST['email'], "text").",".GetSQLValueString($_POST['dob'], "date").",0,".$row_rsLoggedIn['ID'].",NOW())";
	$result = mysql_query($insert, $aquiescedb) or die(mysql_error());
	$_POST['userID'] = mysql_insert_id();
	// update registration to match
	}
} // end update

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE eventregistration SET userID=%s, modifiedbyID=%s, modifieddatetime=%s, statusID=%s, takenpartbefore=%s, registrationteamname=%s, registrationmedical=%s, registrationinfo=%s, registrationtshirt=%s, registrationtime=%s, registrationwheelchair=%s, registrationdiscovered=%s, registrationmarketing=%s, registrationterms=%s, registrationstarttime=%s, registrationdietryreq=%s, registrationspecialreq=%s, registrationinfo2=%s, registrationinfo3=%s, registrationjobtitle=%s, registrationcompany=%s WHERE ID=%s",
                       GetSQLValueString($_POST['userID'], "int"),
                       GetSQLValueString($_POST['modifiedbyID'], "int"),
                       GetSQLValueString($_POST['modifieddatetime'], "date"),
                       GetSQLValueString($_POST['statusID'], "int"),
                       GetSQLValueString(isset($_POST['takenpartbefore']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['registrationteamname'], "text"),
                       GetSQLValueString($_POST['registrationmedical'], "text"),
                       GetSQLValueString($_POST['registrationinfo'], "text"),
                       GetSQLValueString($_POST['registrationtshirt'], "int"),
                       GetSQLValueString($_POST['registrationtime'], "text"),
                       GetSQLValueString($_POST['registrationwheelchair'], "int"),
                       GetSQLValueString($_POST['registrationdiscovered'], "int"),
                       GetSQLValueString(isset($_POST['registrationmarketing']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['registrationterms']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['registrationstarttime'], "int"),
                       GetSQLValueString($_POST['registrationdietryreq'], "text"),
                       GetSQLValueString($_POST['registrationspecialreq'], "text"),
                       GetSQLValueString($_POST['registrationinfo2'], "text"),
                       GetSQLValueString($_POST['registrationinfo3'], "text"),
                       GetSQLValueString($_POST['registrationjobtitle'], "text"),
                       GetSQLValueString($_POST['registrationcompany'], "text"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
	
  $updateGoTo = "index.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo)); exit;
}

$varRegistrationID_rsRegistrant = "-1";
if (isset($_GET['registrationID'])) {
  $varRegistrationID_rsRegistrant = $_GET['registrationID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsRegistrant = sprintf("SELECT eventregistration.*, users.firstname, users.surname, users.email, users.dob, location.address1, location.ID AS locationID, location.address2, location.address3, location.address4, location.postcode, location.telephone1, location.telephone2 FROM eventregistration LEFT JOIN users ON (eventregistration.userID = users.ID) LEFT JOIN location ON (users.defaultaddressID = location.ID) WHERE eventregistration.ID = %s", GetSQLValueString($varRegistrationID_rsRegistrant, "int"));
$rsRegistrant = mysql_query($query_rsRegistrant, $aquiescedb) or die(mysql_error());
$row_rsRegistrant = mysql_fetch_assoc($rsRegistrant);
$totalRows_rsRegistrant = mysql_num_rows($rsRegistrant);



$varRegistrationID_rsMainRegistrant = "-1";
if (isset($_GET['registrationID'])) {
  $varRegistrationID_rsMainRegistrant = $_GET['registrationID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsMainRegistrant = sprintf("SELECT eventregistration.withregistrationID, users.ID, users.firstname, users.surname FROM eventregistration LEFT JOIN eventregistration AS er ON(eventregistration.withregistrationID = er.ID) LEFT JOIN users ON (users.ID = er.userID) WHERE eventregistration.ID = %s AND eventregistration.withregistrationID != eventregistration.ID", GetSQLValueString($varRegistrationID_rsMainRegistrant, "int"));
$rsMainRegistrant = mysql_query($query_rsMainRegistrant, $aquiescedb) or die(mysql_error());
$row_rsMainRegistrant = mysql_fetch_assoc($rsMainRegistrant);
$totalRows_rsMainRegistrant = mysql_num_rows($rsMainRegistrant);

$varRegistrationID_rsGroupMembers = "-1";
if (isset($_GET['registrationID'])) {
  $varRegistrationID_rsGroupMembers = $_GET['registrationID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsGroupMembers = sprintf("SELECT eventregistration.ID, eventregistration.registrationnumber, eventregistration.withregistrationID, users.firstname, users.surname FROM eventregistration LEFT JOIN users ON (eventregistration.userID = users.ID) WHERE eventregistration.withregistrationID = (SELECT eventregistration.withregistrationID FROM eventregistration WHERE eventregistration.ID = %s) OR  eventregistration.ID = %s", GetSQLValueString($varRegistrationID_rsGroupMembers, "int"),GetSQLValueString($varRegistrationID_rsGroupMembers, "int"));
$rsGroupMembers = mysql_query($query_rsGroupMembers, $aquiescedb) or die(mysql_error());
$row_rsGroupMembers = mysql_fetch_assoc($rsGroupMembers);
$totalRows_rsGroupMembers = mysql_num_rows($rsGroupMembers);

$varRegionID_rsDiscovered = "1";
if (isset($regionID)) {
  $varRegionID_rsDiscovered = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsDiscovered = sprintf("SELECT * FROM discovered WHERE regionID = %s ORDER BY ordernum", GetSQLValueString($varRegionID_rsDiscovered, "int"));
$rsDiscovered = mysql_query($query_rsDiscovered, $aquiescedb) or die(mysql_error());
$row_rsDiscovered = mysql_fetch_assoc($rsDiscovered);
$totalRows_rsDiscovered = mysql_num_rows($rsDiscovered);

$colname_rsStartTimes = "-1";
if (isset($_GET['eventID'])) {
  $colname_rsStartTimes = $_GET['eventID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsStartTimes = sprintf("SELECT ID, starttime FROM eventregstarttime WHERE eventID = %s", GetSQLValueString($colname_rsStartTimes, "int"));
$rsStartTimes = mysql_query($query_rsStartTimes, $aquiescedb) or die(mysql_error());
$row_rsStartTimes = mysql_fetch_assoc($rsStartTimes);
$totalRows_rsStartTimes = mysql_num_rows($rsStartTimes);

$colname_rsEvent = "-1";
if (isset($_GET['eventID'])) {
  $colname_rsEvent = $_GET['eventID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsEvent = sprintf("SELECT event.*, eventgroup.eventtitle AS eventgrouptitle FROM event LEFT JOIN eventgroup ON (event.eventgroupID = eventgroup.ID) WHERE event.ID = %s", GetSQLValueString($colname_rsEvent, "int"));
$rsEvent = mysql_query($query_rsEvent, $aquiescedb) or die(mysql_error());
$row_rsEvent = mysql_fetch_assoc($rsEvent);
$totalRows_rsEvent = mysql_num_rows($rsEvent);






// if a full delete has been done then go back to index
if($totalRows_rsRegistrant==0) {
header("location: /calendar/admin/registration/event.php?eventID=".$_GET['eventID']); exit;
}

?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "View Registrant ".$row_rsRegistrant['registrationnumber'].": ". $row_rsRegistrant['firstname']." ".$row_rsRegistrant['surname'];  echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<style >
<!--
<?php if($row_rsEvent['registrationdob']!=1) { echo "#rowDOB { display: none; }"; } ?>
<?php if($row_rsEvent['registrationteamname']!=1) { echo "#rowTeamname { display: none; }"; } ?>
<?php if($row_rsEvent['registrationtshirt']!=1) { echo "#rowTshirt { display: none; }"; } ?>
<?php if($row_rsEvent['registrationtime']!=1) { echo "#rowPredictedtime { display: none; }"; } ?>
<?php if($row_rsEvent['registrationwheelchair']!=1) { echo "#rowWheelchair { display: none; }"; } ?>
<?php if($row_rsEvent['registrationinfo']!=1) { echo "#rowStory { display: none; }"; } ?>
<?php if($row_rsEvent['registrationmedical']!=1) { echo "#rowMedical { display: none; }"; } ?>
<?php if($row_rsEvent['registrationaskjobtitle']!=1) { echo ".jobtitle { display: none; }"; } ?>
<?php if($row_rsEvent['registrationaskcompany']!=1) { echo ".company { display: none; }"; } ?>
<?php if($row_rsEvent['registrationdietryreq']!=1) { echo ".displayDietry { display: none; }"; } ?>
<?php if($row_rsEvent['registrationspecialreq']!=1) { echo "#displaySpecial { display: none; }"; } ?>
<?php if($row_rsEvent['takenpartbefore']!=1) { echo "#rowTakenpartbefore { display: none; }"; } ?>
<?php if($row_rsEvent['registrationdiscovered']!=1) { echo "#rowDiscovered { display: none; }"; } ?>
<?php if($row_rsEvent['registrationpayment']!=1) { echo "#rowPayment { display: none; }"; } ?>
<?php if(trim($row_rsEvent['registrationextraquestion2'])=="") { echo "#extraquestion2 { display: none; }"; } ?>
<?php if(trim($row_rsEvent['registrationextraquestion3'])=="") { echo "#extraquestion3 { display: none; }"; } ?>
<?php if($totalRows_rsStartTimes<2) { echo "#rowStarttime { display: none; }"; } ?>

-->
</style>
<link href="../../css/calendarDefault.css" rel="stylesheet"  />
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" -->
        <div class="page calendar registrant">
    <h1><i class="glyphicon glyphicon-calendar"></i> Sign up <?php echo $row_rsRegistrant['registrationnumber']; ?>: <?php echo $row_rsRegistrant['firstname']; ?> <?php echo $row_rsRegistrant['surname']; ?></h1>
    <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
      <li><a href="index.php?eventID=<?php echo $row_rsRegistrant['eventID']; ?>" class="link_undo" onclick="history.go(-1); return false;"><i class="glyphicon glyphicon-arrow-left"></i> Back</a></li>
    </ul></div></nav>
    <form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1" role="form">
      <table class="form-table">
      
      <tr>
          <td class="text-nowrap text-right" style="width:40%;">Event:</td>
          <td><?php echo $row_rsEvent['eventgrouptitle']; ?>, <?php echo date('d M Y', strtotime($row_rsEvent['startdatetime']));
		  echo $row_rsEvent['allday'] !=1 ? date(', H:i', strtotime($row_rsEvent['startdatetime'])) : ""; ?>
          
          </td></tr><tr>
          <td class="text-nowrap text-right">Signed up:</td>
          <td><?php echo date('H:i, d M Y',strtotime($row_rsRegistrant['createddatetime'])); ?></td>
        </tr> <tr>
          <td class="text-nowrap text-right" >Name:</td>
          <td><input name="firstname" type="text"  id="firstname" value="<?php echo $row_rsRegistrant['firstname']; ?>" size="25" maxlength="50" />
          <input name="surname" type="text"  id="surname" value="<?php echo $row_rsRegistrant['surname']; ?>" size="25" maxlength="50" />
          <input name="userID" type="hidden" id="userID" value="<?php echo $row_rsRegistrant['userID']; ?>" />
          <input name="oldfirstname" type="hidden" id="oldfirstname" value="<?php echo $row_rsRegistrant['firstname']; ?>" />
          <input name="oldsurname" type="hidden" id="oldsurname" value="<?php echo $row_rsRegistrant['surname']; ?>" /></td>
        </tr>  <tr class="status">
          <td class="text-nowrap text-right">Status:</td>
          <td><select name="statusID">
            <option value="0" <?php if (!(strcmp(0, htmlentities($row_rsRegistrant['statusID'], ENT_COMPAT, 'UTF-8')))) {echo "selected=\"selected\"";} ?>>Pending</option>
            <option value="1" <?php if (!(strcmp(1, htmlentities($row_rsRegistrant['statusID'], ENT_COMPAT, 'UTF-8')))) {echo "selected=\"selected\"";} ?>>Accepted</option>
            <option value="2" <?php if (!(strcmp(2, htmlentities($row_rsRegistrant['statusID'], ENT_COMPAT, 'UTF-8')))) {echo "selected=\"selected\"";} ?>>Rejected</option>
          </select></td>
        </tr> <tr class="address">
          <td class="text-nowrap top text-right">Contact details:</td>
          <td><?php if(isset($row_rsMainRegistrant['ID'])) { ?>Signed up by: <a href="registrant.php?registrationID=<?php echo $row_rsMainRegistrant['withregistrationID']; ?>"><?php echo $row_rsMainRegistrant['firstname']; ?> <?php echo $row_rsMainRegistrant['surname']; ?></a><br>
		  <?php } ?><?php if(isset($row_rsRegistrant['locationID'])) { ?><span class="update_location"><a href="../../../location/admin/modify_location.php?locationID=<?php echo $row_rsRegistrant['locationID']; ?>&returnURL=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="link_edit icon_with_text">Edit</a><br></span><?php } else { ?><span class="add_location"><a href="../../../location/admin/add_location.php?userID=<?php echo $row_rsRegistrant['userID']; ?>&address=true&returnURL=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="link_add icon_with_text">Add</a><br></span><?php } ?>
		  <?php echo isset($row_rsRegistrant['address1']) ? $row_rsRegistrant['address1']."<br>" : ""; ?> 
          <?php echo isset($row_rsRegistrant['address2']) ? $row_rsRegistrant['address2']."<br>" : ""; ?>
          <?php echo isset($row_rsRegistrant['address3']) ? $row_rsRegistrant['address3']."<br>" : ""; ?>
          <?php echo isset($row_rsRegistrant['address4']) ? $row_rsRegistrant['address4']."<br>" : ""; ?>
          <?php echo isset($row_rsRegistrant['postcode']) ? $row_rsRegistrant['postcode']."<br>" : ""; ?>
          <?php echo isset($row_rsRegistrant['telephone1']) ? $row_rsRegistrant['telephone1']."<br>" : ""; ?>
          <?php echo isset($row_rsRegistrant['telephone2']) ? $row_rsRegistrant['telephone2']."<br>" : ""; ?>
          <?php if(isset($row_rsRegistrant['email'])) { ?>
<a href="mailto:<?php echo $row_rsRegistrant['email']; ?>"><?php echo $row_rsRegistrant['email']; ?></a><br><?php } ?></td>
        </tr> <tr class="dob">
          <td class="text-nowrap text-right">Date of birth:</td>
          <td><?php if(isset($row_rsRegistrant['dob'])) {
			  $date= explode("-",$row_rsRegistrant['dob']);
			  echo $date[2]."/".$date[1]."/".$date[0]; // get pre-1970 year
		  } else { 
		  echo "N/A";
		  }
		  ?></td>
        </tr>
        <tr class="jobtitle">
          <td align="right" valign="top"><label for="registrationjobtitle">Job title:</label></td>
          <td class="top"><input name="registrationjobtitle" type="text" id="registrationjobtitle" size="50" maxlength="50"  value="<?php echo isset($_REQUEST['registrationjobtitle']) ? htmlentities($_REQUEST['registrationjobtitle'], ENT_COMPAT, "UTF-8") : htmlentities($row_rsRegistrant['registrationjobtitle'], ENT_COMPAT, "UTF-8"); ?>" /></td>
        </tr>
        <tr class="company">
          <td align="right" valign="top"><label for="registrationcompany">Company name:</label></td>
          <td class="top"><input name="registrationcompany" type="text" id="registrationcompany" size="50" maxlength="50"  value="<?php echo isset($_REQUEST['registrationcompany']) ? htmlentities($_REQUEST['registrationcompany'], ENT_COMPAT, "UTF-8") : htmlentities($row_rsRegistrant['registrationcompany'], ENT_COMPAT, "UTF-8"); ?>" /></td>
        </tr>
        <tr id="rowTeamname">
          <td class="text-nowrap text-right">Registration team name:</td>
          <td><input name="registrationteamname" type="text" value="<?php echo htmlentities($row_rsRegistrant['registrationteamname'], ENT_COMPAT, 'UTF-8'); ?>" size="50" maxlength="50" /></td>
        </tr>
        <tr id="rowTakenpartbefore">
          <td class="text-nowrap text-right">Taken part before:</td>
          <td><input type="checkbox" name="takenpartbefore" value=""  <?php if (!(strcmp(htmlentities($row_rsRegistrant['takenpartbefore'], ENT_COMPAT, 'UTF-8'),"1"))) {echo "checked=\"checked\"";} ?> /></td>
        </tr>
        <tr id="rowTshirt">
          <td align="right" valign="top">T-shirt size:</td>
          <td class="top"><label>
            <input <?php if (!(strcmp($row_rsRegistrant['registrationtshirt'],"1"))) {echo "checked=\"checked\"";} ?>  type="radio" name="registrationtshirt" id="registrationtshirt1" value="1"   />
            Extra small (under 12s) <br />
          </label>
            <label>
              <input <?php if (!(strcmp($row_rsRegistrant['registrationtshirt'],"2"))) {echo "checked=\"checked\"";} ?>  type="radio" name="registrationtshirt" id="registrationtshirt2" value="2"  />
              Small <br />
            </label>
            <label>
              <input <?php if (!(strcmp($row_rsRegistrant['registrationtshirt'],"3"))) {echo "checked=\"checked\"";} ?>  type="radio" name="registrationtshirt" id="registrationtshirt3" value="3"   />
              Medium <br />
            </label>
            <label>
              <input <?php if (!(strcmp($row_rsRegistrant['registrationtshirt'],"4"))) {echo "checked=\"checked\"";} ?>  type="radio" name="registrationtshirt" id="registrationtshirt4" value="4"  />
              Large <br />
            </label>
            <label>
              <input <?php if (!(strcmp($row_rsRegistrant['registrationtshirt'],"5"))) {echo "checked=\"checked\"";} ?>  type="radio" name="registrationtshirt" id="registrationtshirt5" value="5"  />
              Extra large</label></td>
        </tr>
        <tr id="rowStarttime">
          <td align="right" valign="top">Prefrerred start time:</td>
          <td class="top"><select name="registrationstarttime" id="registrationstarttime">
            <option value="" <?php if (!(strcmp("", $row_rsRegistrant['registrationstarttime']))) {echo "selected=\"selected\"";} ?>><?php echo isset($region['text_choose']) ? htmlentities($region['text_choose'], ENT_COMPAT, "UTF-8") : "Choose..." ?></option>
            <?php
do {  
?>
<option value="<?php echo $row_rsStartTimes['ID']?>"<?php if (!(strcmp($row_rsStartTimes['ID'], $row_rsRegistrant['registrationstarttime']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsStartTimes['starttime']?></option>
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
          <td align="right" valign="top">Predicted time:</td>
          <td class="top"><input name="registrationtime" type="text"  id="registrationtime" value="<?php echo $row_rsRegistrant['registrationtime']; ?>" size="20" maxlength="20" /></td>
        </tr>
        <tr id="rowWheelchair">
          <td align="right" valign="top">Wheelchair:</td>
          <td class="top"><label>
            <input <?php if (!(strcmp($row_rsRegistrant['registrationwheelchair'],"1"))) {echo "checked=\"checked\"";} ?>  type="radio" name="registrationwheelchair" id="registrationwheelchair1" value="1"  />
            Specially adapted racing chair</label>
            <br />
            <label>
              <input <?php if (!(strcmp($row_rsRegistrant['registrationwheelchair'],"2"))) {echo "checked=\"checked\"";} ?>  type="radio" name="registrationwheelchair" id="registrationwheelchair2" value="2"  />
              Self propelled chair</label>
            <br />
            <label>
              <input <?php if (!(strcmp($row_rsRegistrant['registrationwheelchair'],"3"))) {echo "checked=\"checked\"";} ?>  type="radio" name="registrationwheelchair" id="registrationwheelchair3" value="3" />
              Pushed / Escorted in wheelchair</label><br /><label><input  <?php if (!(strcmp($row_rsRegistrant['registrationwheelchair'],"0"))) {echo "checked=\"checked\"";} ?> type="radio" name="registrationwheelchair" id="registrationwheelchair0" value="0"  /> None of these</label></td>
        </tr>
       
        <tr  id="rowMedical">
          <td class="text-nowrap text-right top">Medical:</td>
          <td><textarea name="registrationmedical" cols="50" rows="5"><?php echo htmlentities($row_rsRegistrant['registrationmedical'], ENT_COMPAT, 'UTF-8'); ?></textarea></td>
        </tr>
        <tr class="displayDietry">
          <td align="right" valign="top"><label for="registrationdietryreq">Do you have any dietry requirements? If so, list them here:</label></td>
          <td class="top"><textarea name="registrationdietryreq" id="registrationdietryreq" cols="45" rows="5"><?php echo htmlentities($row_rsRegistrant['registrationdietryreq'], ENT_COMPAT, 'UTF-8'); ?></textarea></td>
        </tr>
        <tr class="displaySpecial">
          <td align="right" valign="top"><label for="registrationspecialreq">Do you have any other special requirements? If so, list them here:</label></td>
          <td class="top"><textarea name="registrationspecialreq" id="registrationspecialreq" cols="45" rows="5"><?php echo htmlentities($row_rsRegistrant['registrationspecialreq'], ENT_COMPAT, 'UTF-8'); ?></textarea></td>
        </tr>
        <tr  id ="rowStory">
          <td class=" text-right top"><?php echo $row_rsEvent['registrationextraquestion']; ?></td>
          <td><textarea name="registrationinfo" cols="45" rows="5"><?php echo htmlentities($row_rsRegistrant['registrationinfo'], ENT_COMPAT, 'UTF-8'); ?></textarea></td>
        </tr>
        <trid="extraquestion2">
          <td class=" text-right top"><?php echo $row_rsEvent['registrationextraquestion2']; ?></td>
          <td><textarea name="registrationinfo2" cols="45" rows="5"><?php echo htmlentities($row_rsRegistrant['registrationinfo2'], ENT_COMPAT, 'UTF-8'); ?></textarea></td>
        </tr>
        <tr id="extraquestion3">
          <td class=" text-right top"><?php echo $row_rsEvent['registrationextraquestion3']; ?></td>
          <td><textarea name="registrationinfo3" cols="45" rows="5"><?php echo htmlentities($row_rsRegistrant['registrationinfo3'], ENT_COMPAT, 'UTF-8'); ?></textarea></td>
        </tr>
        <tr id="rowDiscovered">
          <td align="right" valign="top">Discovered:</td>
          <td class="top"><select name="registrationdiscovered" id="registrationdiscovered">
            <option value="0" <?php if (!(strcmp(0, $row_rsRegistrant['registrationdiscovered']))) {echo "selected=\"selected\"";} ?>><?php echo isset($region['text_choose']) ? htmlentities($region['text_choose'], ENT_COMPAT, "UTF-8") : "Choose..." ?></option>
            <?php
do {  
?>
            <option value="<?php echo $row_rsDiscovered['ID']?>"<?php if (!(strcmp($row_rsDiscovered['ID'], $row_rsRegistrant['registrationdiscovered']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsDiscovered['description']?></option>
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
        <tr id="rowPayment">
          <td align="right" valign="top">Payment:</td>
          <td class="top"><?php echo isset($row_rsRegistrant['paymentamount']) ? "&pound;".number_format($row_rsRegistrant['paymentamount'],2,".",",") : "None"; ?></td>
        </tr>
        <tr  class="optin">
          <td align="right" valign="top">Marketing opt-in:</td>
          <td class="top"><input <?php if (!(strcmp($row_rsRegistrant['registrationmarketing'],1))) {echo "checked=\"checked\"";} ?> name="registrationmarketing" type="checkbox" id="registrationmarketing" value="1" /></td>
        </tr>
        <tr class="terms">
          <td align="right" valign="top">Terms agreed:</td>
          <td class="top"><input <?php if (!(strcmp($row_rsRegistrant['registrationterms'],1))) {echo "checked=\"checked\"";} ?> name="registrationterms" type="checkbox" id="registrationterms" value="1" /></td>
        </tr>
        <?php if ($totalRows_rsGroupMembers > 0) { // Show if recordset not empty ?> <tr class="group_members">
            <td align="right" valign="middle" class="text-nowrap">Group members:</td>
            <td>
              <table border="0" cellpadding="0" cellspacing="2" class="listTable">
                <?php do { ?>
                  <tr>
                    <td><?php echo $row_rsGroupMembers['registrationnumber']; ?></td>
                    <td><a href="registrant.php?registrationID=<?php echo $row_rsGroupMembers['ID']; ?>"><?php echo $row_rsGroupMembers['firstname']; ?> <?php echo $row_rsGroupMembers['surname']; ?></a></td>
                    <td><a href="registrant.php?deleteID=<?php echo $row_rsGroupMembers['ID']; ?>&amp;registrationID=<?php echo $row_rsGroupMembers['withregistrationID']; ?>&amp;eventID=<?php echo $row_rsEvent['ID']; ?>" class="link_delete" onclick="return confirm('Are you sure you want to delete this group member? <?php if($row_rsGroupMembers['ID'] == $row_rsGroupMembers['withregistrationID']) { ?>\n\nIMPORTANT: This is the main registrant, so if you delete this registrant, all team members will also be deleted.<?php } ?>');"><i class="glyphicon glyphicon-trash"></i> Delete</a></td>
                    <td><?php if($row_rsGroupMembers['ID'] == $row_rsGroupMembers['withregistrationID']) { ?>
                      <strong>Main Registrant</strong>              <?php } ?>&nbsp;</td>
                  </tr>
                  <?php } while ($row_rsGroupMembers = mysql_fetch_assoc($rsGroupMembers)); ?>
              </table></td>
          </tr>
          <?php } // Show if recordset not empty ?> 
          <tbody class="add_registrant"><tr>
    <td class="text-nowrap text-right">Add new registrant:</td>
    <td>&nbsp;</td>
  </tr> <tr>
    <td class="text-nowrap"  colspan="2"><table border="0" cellpadding="2" cellspacing="0" class="form-table">
      <tr>
        <td><strong>First name:</strong></td>
        <td><strong>Surname:</strong></td>
        <td  class="columnDOB"><strong>Date of birth:</strong></td>
        <td  class="columnEmail"><strong>Email<br />
          (if known):</strong></td>
        <td  class="columnPostcode"><strong>Postcode<br />
          (if different):</strong></td>
        <td  class="columnTshirt"><strong>T-shirt<br />
          size:</strong></td>
      </tr>
     
     </tbody> <tr>
        <td><input name="teamfirstname" type="text"  id="teamfirstname" size="15" maxlength="50" value="<?php echo isset($_REQUEST['teamfirstname']) ? htmlentities($_REQUEST['teamfirstname'], ENT_COMPAT, "UTF-8") : ""; ?>" autocomplete="off" readonly onfocus="this.removeAttribute('readonly');" /></td>
        <td><input name="teamsurname" type="text"  id="teamsurname" size="15" maxlength="50" value="<?php echo isset($_REQUEST['teamsurname']) ? htmlentities($_REQUEST['teamsurname'], ENT_COMPAT, "UTF-8") : ""; ?>" autocomplete="off" readonly onfocus="this.removeAttribute('readonly');"/></td>
        <td class="columnDOB text-nowrap"><input type="hidden" name="teamdob" id="teamdob" value="<?php $setvalue = isset($_REQUEST['teamdob']) ? $_REQUEST['teamdob'] : ""; $inputname = "teamdob"; $startyear = 1900; $endyear = date('Y'); $shortmonth = true; echo $setvalue; ?>" />
          <?php require('../../../core/includes/datetimeinput.inc.php'); ?></td>
        <td><input name="teamemail" type="text"  id="teamemail" size="20" maxlength="50" value="<?php echo isset($_REQUEST['teamemail']) ? htmlentities($_REQUEST['teamemail'], ENT_COMPAT, "UTF-8") : ""; ?>" autocomplete="off" readonly onfocus="this.removeAttribute('readonly');"/></td>
        <td class="columnPostCode"><input name="teampostcode[<?php echo $i; ?>]" type="text"  id="teampostcode" size="10" maxlength="50" value="<?php echo isset($_REQUEST['teampostcode']) ? htmlentities($_REQUEST['teampostcode'], ENT_COMPAT, "UTF-8") : ""; ?>" autocomplete="off" readonly onfocus="this.removeAttribute('readonly');"/></td>
        <td class="columnTshirt"><label>
          <select name="teamtshirt" id="teamtshirt">
            <option>-</option>
            <option value="1" <?php if(isset($_REQUEST['teamtshirt']) && $_REQUEST['teamtshirt'] ==1) echo "selected=\"selected\""; ?>>XS</option>
            <option value="2" <?php if(isset($_REQUEST['teamtshirt']) && $_REQUEST['teamtshirt'] ==2) echo "selected=\"selected\""; ?>>S</option>
            <option value="3" <?php if(isset($_REQUEST['teamtshirt']) && $_REQUEST['teamtshirt'] ==3) echo "selected=\"selected\""; ?>>M</option>
            <option value="4" <?php if(isset($_REQUEST['teamtshirt']) && $_REQUEST['teamtshirt'] ==4) echo "selected=\"selected\""; ?>>L</option>
            <option value="5" <?php if(isset($_REQUEST['teamtshirt']) && $_REQUEST['teamtshirt'] ==5) echo "selected=\"selected\""; ?>>XL</option>
          </select>
          <input name="registrationgroupID" type="hidden" id="registrationgroupID" value="<?php echo $row_rsEvent['registrationgroupID']; ?>" />
          <input name="registrationsequential" type="hidden" id="registrationsequential" value="<?php echo $row_rsEvent['registrationsequential']; ?>" />
          <input name="mainregistrantID" type="hidden" id="mainregistrantID" value="<?php echo $row_rsRegistrant['withregistrationID']; ?>" />
        </label></td>
      </tr>
      
     
    </table></td>
  </tr> <tr>
          <td class="text-nowrap text-right">&nbsp;</td>
          <td><button type="submit" class="btn btn-primary">Save changes</button></td>
        </tr>
      </table>
      <input type="hidden" name="ID" value="<?php echo $row_rsRegistrant['ID']; ?>" />
      <input type="hidden" name="modifiedbyID" value="<?php echo htmlentities($row_rsLoggedIn['ID']); ?>" />
      <input type="hidden" name="modifieddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
      <input type="hidden" name="MM_update" value="form1" />
    </form>
   </div>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsRegistrant);

mysql_free_result($rsLoggedIn);

mysql_free_result($rsMainRegistrant);

mysql_free_result($rsGroupMembers);

mysql_free_result($rsDiscovered);

mysql_free_result($rsStartTimes);

mysql_free_result($rsEvent);
?>
