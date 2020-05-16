<?php require_once('../../Connections/aquiescedb.php'); ?><?php require_once('../../core/includes/adminAccess.inc.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "6,7,8,9,10";
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

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) { // fix errors
$_POST['startdatetime'] = isset($_POST['startdatetime']) ? $_POST['startdatetime'] : date('Y-m-d H:i:s');
$_POST['enddatetime'] = (isset($_POST['enddatetime']) && $_POST['enddatetime'] > $_POST['startdatetime']) ? $_POST['enddatetime'] : $_POST['startdatetime'];
	
}

$where = isset($_POST['applyall']) ? " WHERE firsteventID = ".intval($_POST['firsteventID']):  " WHERE ID = ".intval($_POST['ID']);

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE event SET eventlocationID=%s, startdatetime=%s, enddatetime=%s, modifiedbyID=%s, modifieddatetime=%s, statusID=%s, userID=%s ".$where."",
                       GetSQLValueString($_POST['eventlocationID'], "int"),
                       GetSQLValueString($_POST['startdatetime'], "date"),
                       GetSQLValueString($_POST['enddatetime'], "date"),
                       GetSQLValueString($_POST['modifiedbyID'], "int"),
                       GetSQLValueString($_POST['modifieddatetime'], "date"),
                       GetSQLValueString(isset($_POST['statusID']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['userID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
 $updateGoTo = "update_calendar.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo)); exit;
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

$colname_rsEvent = "-1";
if (isset($_GET['eventID'])) {
  $colname_rsEvent = $_GET['eventID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsEvent = sprintf("SELECT event.*, eventgroup.eventtitle AS eventgrouptitle FROM event LEFT JOIN eventgroup ON (event.eventgroupID = eventgroup.ID) WHERE event.ID = %s", GetSQLValueString($colname_rsEvent, "int"));
$rsEvent = mysql_query($query_rsEvent, $aquiescedb) or die(mysql_error());
$row_rsEvent = mysql_fetch_assoc($rsEvent);
$totalRows_rsEvent = mysql_num_rows($rsEvent);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsEventLocations = "SELECT ID, locationname FROM location WHERE location.active = 1 AND location.public = 1 ORDER BY locationname ASC";
$rsEventLocations = mysql_query($query_rsEventLocations, $aquiescedb) or die(mysql_error());
$row_rsEventLocations = mysql_fetch_assoc($rsEventLocations);
$totalRows_rsEventLocations = mysql_num_rows($rsEventLocations);

$varEventID_rsFirstEvent = "-1";
if (isset($_GET['eventID'])) {
  $varEventID_rsFirstEvent = $_GET['eventID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsFirstEvent = sprintf("SELECT firstevent.ID, firstevent.startdatetime FROM event LEFT JOIN event AS firstevent ON (firstevent.ID = event.firsteventID) WHERE event.ID = %s", GetSQLValueString($varEventID_rsFirstEvent, "int"));
$rsFirstEvent = mysql_query($query_rsFirstEvent, $aquiescedb) or die(mysql_error());
$row_rsFirstEvent = mysql_fetch_assoc($rsFirstEvent);
$totalRows_rsFirstEvent = mysql_num_rows($rsFirstEvent);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsEventPrefs = "SELECT * FROM eventprefs WHERE ID =".$regionID."";
$rsEventPrefs = mysql_query($query_rsEventPrefs, $aquiescedb) or die(mysql_error());
$row_rsEventPrefs = mysql_fetch_assoc($rsEventPrefs);
$totalRows_rsEventPrefs = mysql_num_rows($rsEventPrefs);

$varGroupID_rsUsers = "-1";
if (isset($row_rsEventPrefs['userlistgroupID'])) {
  $varGroupID_rsUsers = $row_rsEventPrefs['userlistgroupID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsUsers = sprintf("SELECT users.ID, firstname, surname FROM users LEFT JOIN usergroupmember ON (users.ID = usergroupmember.userID) WHERE usergroupmember.groupID = %s ORDER BY surname", GetSQLValueString($varGroupID_rsUsers, "int"));
$rsUsers = mysql_query($query_rsUsers, $aquiescedb) or die(mysql_error());
$row_rsUsers = mysql_fetch_assoc($rsUsers);
$totalRows_rsUsers = mysql_num_rows($rsUsers);
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Update times - ".$row_rsEvent['eventgrouptitle'];  echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<style><!--
<?php 
if($totalRows_rsUsers==0) {
	echo ".userlist { display: none; }";
}
?>
--></style>
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" --><div class="page calendar">
    <h1><i class="glyphicon glyphicon-calendar"></i> Update times:</h1><h2><?php echo $row_rsEvent['eventgrouptitle']; ?></h2>
    <form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1" role="form">
      <table class="form-table">
        <tr class="calendarLocation form-group">
          <td class="text-nowrap text-right">Location:</td>
          <td><select name="eventlocationID" class="form-control">
            <option value="" <?php if (!(strcmp("", htmlentities($row_rsEvent['eventlocationID'], ENT_COMPAT, 'UTF-8')))) {echo "selected=\"selected\"";} ?>>See event description</option>
            <?php
do {  
?>
            <option value="<?php echo $row_rsEventLocations['ID']?>"<?php if (!(strcmp($row_rsEventLocations['ID'], htmlentities($row_rsEvent['eventlocationID'], ENT_COMPAT, 'UTF-8')))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsEventLocations['locationname']?></option>
            <?php
} while ($row_rsEventLocations = mysql_fetch_assoc($rsEventLocations));
  $rows = mysql_num_rows($rsEventLocations);
  if($rows > 0) {
      mysql_data_seek($rsEventLocations, 0);
	  $row_rsEventLocations = mysql_fetch_assoc($rsEventLocations);
  }
?>
          </select></td>
        </tr>
        
         <tr  class="userlist form-group">
        <td class="text-nowrap text-right"><label for="userID">For:</label></td>
        
        <td>
          <select name="userID" id="userID" class="form-control">
            <option value="">Choose...</option>
            <option value="">N/A</option>
            <?php
do {  
?>
            <option value="<?php echo $row_rsUsers['ID']; ?>" <?php if($row_rsUsers['ID']==$row_rsEvent['userID']) echo "selected"; ?>><?php echo $row_rsUsers['firstname']." ".$row_rsUsers['surname']; ?></option>
            <?php
} while ($row_rsUsers = mysql_fetch_assoc($rsUsers));
  $rows = mysql_num_rows($rsUsers);
  if($rows > 0) {
      mysql_data_seek($rsUsers, 0);
	  $row_rsUsers = mysql_fetch_assoc($rsUsers);
  }
?>
          </select></td></tr>
          
           <tr>
          <td class="text-nowrap text-right">Starts:</td>
          <td><input type="hidden" name="startdatetime" id="startdatetime" value="<?php $setvalue =  htmlentities($row_rsEvent['startdatetime'], ENT_COMPAT, 'UTF-8'); echo $setvalue; $inputname="startdatetime"; $time = true; ?>"  /><?php require('../../core/includes/datetimeinput.inc.php'); ?></td>
        </tr> <tr>
          <td class="text-nowrap text-right">Ends:</td>
          <td><input type="hidden" name="enddatetime" id="enddatetime" value="<?php $setvalue = htmlentities($row_rsEvent['enddatetime'], ENT_COMPAT, 'UTF-8'); echo $setvalue; $inputname="enddatetime"; $time = true; ?>"/><?php require('../../core/includes/datetimeinput.inc.php'); ?>
</td>
        </tr> <tr>
          <td class="text-nowrap text-right">Active:</td>
          <td><input type="checkbox" name="statusID" value="1" <?php if (!(strcmp(htmlentities($row_rsEvent['statusID']),1))) {echo "checked=\"checked\"";} ?> /></td>
        </tr><?php if(isset($row_rsEvent['firsteventID']) && $row_rsEvent['firsteventID']>0) { ?>
        <tr>
          <td class="text-nowrap text-right">&nbsp;</td>
          <td>(This is part of a repeating event<?php echo isset($row_rsFirstEvent['startdatetime']) ? " beginning ".date('d M Y H:i', strtotime($row_rsFirstEvent['startdatetime'])) : ""; ?> - check this box to apply changes to all). <a href="update_calendar.php?eventgroupID=<?php echo intval($_GET['eventgroupID']); ?>&amp;deletefirstID=<?php echo $row_rsEvent['firsteventID']; ?>">Delete all repeats.</a></td>
        </tr>
        <tr>
        <td class="text-nowrap text-right">Apply to all:</td><td>
      <input type="checkbox" name="applyall" value="1"  /> 
      </td>
        </tr><?php } ?><tr>
          <td class="text-nowrap text-right">&nbsp;</td>
          <td><button type="submit" class="btn btn-primary" >Save changes</button></td>
        </tr>
      </table>
      
     
        <input type="hidden" name="ID" value="<?php echo $row_rsEvent['ID']; ?>" />
         <input type="text" name="firsteventID" value="<?php echo $row_rsEvent['firsteventID']; ?>" />
        <input type="hidden" name="modifiedbyID" value="<?php echo htmlentities($row_rsLoggedIn['ID']); ?>" />
        <input type="hidden" name="modifieddatetime" value="<?php echo htmlentities($row_rsEvent['modifieddatetime'], ENT_COMPAT, 'UTF-8'); ?>" />
        <input type="hidden" name="MM_update" value="form1" />
           
    </form>
   </div>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsEvent);

mysql_free_result($rsEventLocations);

mysql_free_result($rsFirstEvent);

mysql_free_result($rsEventPrefs);

mysql_free_result($rsUsers);
?>
