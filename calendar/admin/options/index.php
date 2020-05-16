<?php require_once('../../../Connections/aquiescedb.php'); ?>
<?php require_once('../../../core/includes/adminAccess.inc.php'); ?>
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

$MM_restrictGoTo = "/login/index.php?notloggedin=true&alert=".urlencode("You need to be logged in as an Administrator to access this page.");
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

$regionID = (isset($regionID) && intval($regionID) > 0) ?  intval($regionID) : 1;

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE eventprefs SET accesslevel=%s, registrationaccesslevel=%s, defaultrepeatperiod=%s, daystarttime=%s, dayendtime=%s, customfield1=%s, customfield2=%s, registrationalertemail=%s, registrationalertincludelink=%s, text_register=%s, text_received=%s, writeaccess=%s, eventname=%s, allowcancelregistration=%s, defaultregistrationmax=%s, defaultregistrationteam=%s, userlistgroupID=%s, addeventuseremailtemplateID=%s, addeventlocationemailtemplateID=%s, remindereventuseremailtemplateID=%s, remindereventlocationemailtemplateID=%s, remindereventuseremailhours=%s, remindereventlocationemailhours=%s, canceleventuseremailtemplateID=%s, canceleventlocationemailtemplateID=%s, remindereventlocationemail2templateID=%s, remindereventlocationemail2hours=%s WHERE ID=%s",
                       GetSQLValueString($_POST['accesslevel'], "int"),
                       GetSQLValueString(isset($_POST['registrationaccesslevel']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['recurringinterval'], "text"),
                       GetSQLValueString($_POST['daystarttime'], "date"),
                       GetSQLValueString($_POST['dayendtime'], "date"),
                       GetSQLValueString($_POST['customfield1'], "text"),
                       GetSQLValueString($_POST['customfield2'], "text"),
                       GetSQLValueString($_POST['registrationalertemail'], "text"),
                       GetSQLValueString(isset($_POST['registrationalertincludelink']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['text_register'], "text"),
                       GetSQLValueString($_POST['text_received'], "text"),
                       GetSQLValueString($_POST['writeaccess'], "int"),
                       GetSQLValueString($_POST['eventname'], "text"),
                       GetSQLValueString(isset($_POST['allowcancelregistration']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['defaultregistrationmax'], "int"),
                       GetSQLValueString($_POST['defaultregistrationteam'], "int"),
                       GetSQLValueString($_POST['userlistgroupID'], "int"),
                       GetSQLValueString($_POST['addeventuseremailtemplateID'], "int"),
                       GetSQLValueString($_POST['addeventlocationemailtemplateID'], "int"),
                       GetSQLValueString($_POST['remindereventuseremailtemplateID'], "int"),
                       GetSQLValueString($_POST['remindereventlocationemailtemplateID'], "int"),
                       GetSQLValueString($_POST['remindereventuseremailhours'], "int"),
                       GetSQLValueString($_POST['remindereventlocationemailhours'], "int"),
                       GetSQLValueString($_POST['canceleventuseremailtemplateID'], "int"),
                       GetSQLValueString($_POST['canceleventlocationemailtemplateID'], "int"),
                       GetSQLValueString($_POST['remindereventlocationemail2templateID'], "int"),
                       GetSQLValueString($_POST['remindereventlocationemail2hours'], "int"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());

  $updateGoTo = "../index.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo));
}

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsUserTypes = "SELECT * FROM usertype WHERE ID > 0 ORDER BY ID ASC";
$rsUserTypes = mysql_query($query_rsUserTypes, $aquiescedb) or die(mysql_error());
$row_rsUserTypes = mysql_fetch_assoc($rsUserTypes);
$totalRows_rsUserTypes = mysql_num_rows($rsUserTypes);

$varRegionID_rsEventPrefs = "1";
if (isset($regionID)) {
  $varRegionID_rsEventPrefs = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsEventPrefs = sprintf("SELECT * FROM eventprefs WHERE ID = %s", GetSQLValueString($varRegionID_rsEventPrefs, "int"));
$rsEventPrefs = mysql_query($query_rsEventPrefs, $aquiescedb) or die(mysql_error());
$row_rsEventPrefs = mysql_fetch_assoc($rsEventPrefs);
$totalRows_rsEventPrefs = mysql_num_rows($rsEventPrefs);

$varRegionID_rsUserGroups = "1";
if (isset($regionID)) {
  $varRegionID_rsUserGroups = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsUserGroups = sprintf("SELECT * FROM usergroup WHERE regionID = %s OR regionID = 0", GetSQLValueString($varRegionID_rsUserGroups, "int"));
$rsUserGroups = mysql_query($query_rsUserGroups, $aquiescedb) or die(mysql_error());
$row_rsUserGroups = mysql_fetch_assoc($rsUserGroups);
$totalRows_rsUserGroups = mysql_num_rows($rsUserGroups);

$varRegionID_rsEmailTemplates = "1";
if (isset($regionID)) {
  $varRegionID_rsEmailTemplates = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsEmailTemplates = sprintf("SELECT ID, templatename FROM groupemailtemplate WHERE (regionID = %s OR regionID = 0) AND statusID = 1 ORDER BY templatename ASC", GetSQLValueString($varRegionID_rsEmailTemplates, "int"));
$rsEmailTemplates = mysql_query($query_rsEmailTemplates, $aquiescedb) or die(mysql_error());
$row_rsEmailTemplates = mysql_fetch_assoc($rsEmailTemplates);
$totalRows_rsEmailTemplates = mysql_num_rows($rsEmailTemplates);

if($totalRows_rsEventPrefs==0) {
	mysql_query("INSERT INTO eventprefs (`accesslevel`) VALUES (0)");
	header("location: index.php"); exit;
}
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Calendar Options"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<link href="../../css/calendarDefault.css" rel="stylesheet"  />
<link href="../../../SpryAssets/SpryTabbedPanels.css" rel="stylesheet" >
<script src="../../../SpryAssets/SpryTabbedPanels.js"></script>
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
   <div class="page calendar"> <h1><i class="glyphicon glyphicon-calendar"></i> Calendar Options</h1>
   <form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1" role="form">
     <div id="TabbedPanels1" class="TabbedPanels">
       <ul class="TabbedPanelsTabGroup">
         <li class="TabbedPanelsTab" tabindex="0">Calendar</li>
         <li class="TabbedPanelsTab" tabindex="0">Registration</li>
         <li class="TabbedPanelsTab" tabindex="0">Terminology</li>
</ul>
       <div class="TabbedPanelsContentGroup">
         <div class="TabbedPanelsContent">
           <table class="form-table">
             <tr>
               <td align="right"><label for="accesslevel">Calendar available to read:</label></td>
               <td><select name="accesslevel" id="accesslevel" class="form-control">
                 <option value="0" <?php if (!(strcmp(0, $row_rsEventPrefs['accesslevel']))) {echo "selected=\"selected\"";} ?>>Everyone</option>
                 <?php
do {  
?>
                 <option value="<?php echo $row_rsUserTypes['ID']?>"<?php if (!(strcmp($row_rsUserTypes['ID'], $row_rsEventPrefs['accesslevel']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsUserTypes['name']?></option>
                 <?php
} while ($row_rsUserTypes = mysql_fetch_assoc($rsUserTypes));
  $rows = mysql_num_rows($rsUserTypes);
  if($rows > 0) {
      mysql_data_seek($rsUserTypes, 0);
	  $row_rsUserTypes = mysql_fetch_assoc($rsUserTypes);
  }
?>
               </select></td>
             </tr>
             <tr>
               <td align="right"><label for="writeaccess">Calendar available to write:</label></td>
               <td><select name="writeaccess" id="writeaccess" class="form-control">
                 <?php
do {  
?>
                 <option value="<?php echo $row_rsUserTypes['ID']?>"<?php if (!(strcmp($row_rsUserTypes['ID'], $row_rsEventPrefs['writeaccess']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsUserTypes['name']?></option>
                 <?php
} while ($row_rsUserTypes = mysql_fetch_assoc($rsUserTypes));
  $rows = mysql_num_rows($rsUserTypes);
  if($rows > 0) {
      mysql_data_seek($rsUserTypes, 0);
	  $row_rsUserTypes = mysql_fetch_assoc($rsUserTypes);
  }
?>
               </select></td>
             </tr>
             <tr>
               <td align="right">Default repeat period:</td>
               <td><select name="recurringinterval" id="recurringinterval"  class="form-control">
                 <option value="days" <?php if(isset($row_rsEventPrefs['defaultrepeatperiod']) && $row_rsEventPrefs['defaultrepeatperiod'] =="days") echo "selected"; ?>>day(s)</option>
                 <option value="weekdays" <?php if(isset($row_rsEventPrefs['defaultrepeatperiod']) && $row_rsEventPrefs['defaultrepeatperiod'] =="weekdays") echo "selected"; ?>>weekday(s)</option>
                 <option value="weeks"  <?php if(!isset($row_rsEventPrefs['defaultrepeatperiod']) || $row_rsEventPrefs['defaultrepeatperiod'] =="weeks") echo "selected"; ?>>week(s)</option>
                 <option value="months" <?php if(isset($row_rsEventPrefs['defaultrepeatperiod']) && $row_rsEventPrefs['defaultrepeatperiod'] =="months") echo "selected"; ?>>month(s) by date</option>
                 <option value="nthdow" <?php if(isset($row_rsEventPrefs['defaultrepeatperiod']) && $row_rsEventPrefs['defaultrepeatperiod'] =="nthdow") echo "selected"; ?>>month(s) by day</option>
                 <option value="years" <?php if(isset($row_rsEventPrefs['defaultrepeatperiod']) && $row_rsEventPrefs['defaultrepeatperiod'] =="years") echo "selected"; ?>>year(s)</option>
               </select></td>
             </tr>
             <tr>
               <td align="right"><label for="daystarttime">Day start:</label></td>
               <td class="form-inline"><input name="daystarttime" type="text" class="form-control" id="daystarttime" value="<?php echo $row_rsEventPrefs['daystarttime']; ?>">
                 HH:MM:SS</td>
             </tr>
             <tr>
               <td align="right"><label for="dayendtime">Day end:</label></td>
               <td class="form-inline"><input name="dayendtime" type="text" class="form-control" id="dayendtime" value="<?php echo $row_rsEventPrefs['dayendtime']; ?>">
                 HH:MM:SS</td>
             </tr>
             <tr>
               <td align="right"><label for="customfield1">Optional field 1:</label></td>
               <td><input name="customfield1" type="text" class="form-control" id="customfield1" value="<?php echo $row_rsEventPrefs['customfield1']; ?>" size="50" maxlength="50"></td>
             </tr>
             <tr>
               <td align="right"><label for="customfield2">Optional field 2:</label></td>
               <td><input name="customfield2" type="text" class="form-control" id="customfield2" value="<?php echo $row_rsEventPrefs['customfield2']; ?>" size="50" maxlength="50"></td>
             </tr>
            
             <tr>
               <td align="right"><label for="userlistgroupID">User list group: </label></td>
               <td>
                 <select name="userlistgroupID" id="userlistgroupID" class="form-control">
                   <option value="0" <?php if (!(strcmp(0, $row_rsEventPrefs['userlistgroupID']))) {echo "selected=\"selected\"";} ?>>None</option>
                   <?php
do {  
?>
                   <option value="<?php echo $row_rsUserGroups['ID']?>"<?php if (!(strcmp($row_rsUserGroups['ID'], $row_rsEventPrefs['userlistgroupID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsUserGroups['groupname']?></option>
                   <?php
} while ($row_rsUserGroups = mysql_fetch_assoc($rsUserGroups));
  $rows = mysql_num_rows($rsUserGroups);
  if($rows > 0) {
      mysql_data_seek($rsUserGroups, 0);
	  $row_rsUserGroups = mysql_fetch_assoc($rsUserGroups);
  }
?>
                 </select></td>
             </tr>
             
             
              <tr>
               <td align="right"><label for="addeventuseremailtemplateID">Send add event email to user:</label></td>
               <td class="form-inline"><select name="addeventuseremailtemplateID" id="addeventuseremailtemplateID" class="form-control">
                 <option value="0" <?php if (!(strcmp(0, $row_rsEventPrefs['addeventuseremailtemplateID']))) {echo "selected=\"selected\"";} ?>>None</option>
                 <?php
do {  
?>
                 <option value="<?php echo $row_rsEmailTemplates['ID']?>"<?php if (!(strcmp($row_rsEmailTemplates['ID'], $row_rsEventPrefs['addeventuseremailtemplateID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsEmailTemplates['templatename']?></option>
                 <?php
} while ($row_rsEmailTemplates = mysql_fetch_assoc($rsEmailTemplates));
  $rows = mysql_num_rows($rsEmailTemplates);
  if($rows > 0) {
      mysql_data_seek($rsEmailTemplates, 0);
	  $row_rsEmailTemplates = mysql_fetch_assoc($rsEmailTemplates);
  }
?>
               </select> and reminder <select name="remindereventuseremailtemplateID" class="form-control">
                 <option value="0" <?php if (!(strcmp(0, $row_rsEventPrefs['remindereventuseremailtemplateID']))) {echo "selected=\"selected\"";} ?>>None</option>
                 <?php
do {  
?>
                 <option value="<?php echo $row_rsEmailTemplates['ID']?>"<?php if (!(strcmp($row_rsEmailTemplates['ID'], $row_rsEventPrefs['remindereventuseremailtemplateID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsEmailTemplates['templatename']?></option>
                 <?php
} while ($row_rsEmailTemplates = mysql_fetch_assoc($rsEmailTemplates));
  $rows = mysql_num_rows($rsEmailTemplates);
  if($rows > 0) {
      mysql_data_seek($rsEmailTemplates, 0);
	  $row_rsEmailTemplates = mysql_fetch_assoc($rsEmailTemplates);
  }
?>
               </select> 
               <input name="remindereventuseremailhours" type="text" class="form-control" value="<?php echo $row_rsEventPrefs['remindereventuseremailhours']; ?>" size="3" maxlength="5"> hours prior</td>
             </tr>
             <tr>
               <td align="right"><label for="addeventuseremailtemplateID">Send add event email to venue:</label></td>
               <td class="form-inline">
                 <select name="addeventlocationemailtemplateID" id="addeventlocationemailtemplateID" class="form-control">
                   <option value="0" <?php if (!(strcmp(0, $row_rsEventPrefs['addeventlocationemailtemplateID']))) {echo "selected=\"selected\"";} ?>>None</option>
                   <?php
do {  
?>
                   <option value="<?php echo $row_rsEmailTemplates['ID']?>"<?php if (!(strcmp($row_rsEmailTemplates['ID'], $row_rsEventPrefs['addeventlocationemailtemplateID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsEmailTemplates['templatename']?></option>
                   <?php
} while ($row_rsEmailTemplates = mysql_fetch_assoc($rsEmailTemplates));
  $rows = mysql_num_rows($rsEmailTemplates);
  if($rows > 0) {
      mysql_data_seek($rsEmailTemplates, 0);
	  $row_rsEmailTemplates = mysql_fetch_assoc($rsEmailTemplates);
  }
?>
                 </select><br>and reminders <select name="remindereventlocationemailtemplateID" class="form-control">
                   <option value="0" <?php if (!(strcmp(0, $row_rsEventPrefs['remindereventlocationemailtemplateID']))) {echo "selected=\"selected\"";} ?>>None</option>
                   <?php
do {  
?>
                   <option value="<?php echo $row_rsEmailTemplates['ID']?>"<?php if (!(strcmp($row_rsEmailTemplates['ID'], $row_rsEventPrefs['remindereventlocationemailtemplateID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsEmailTemplates['templatename']?></option>
                   <?php
} while ($row_rsEmailTemplates = mysql_fetch_assoc($rsEmailTemplates));
  $rows = mysql_num_rows($rsEmailTemplates);
  if($rows > 0) {
      mysql_data_seek($rsEmailTemplates, 0);
	  $row_rsEmailTemplates = mysql_fetch_assoc($rsEmailTemplates);
  }
?>
               </select> 
               <input name="remindereventlocationemailhours" type="text" class="form-control" value="<?php echo $row_rsEventPrefs['remindereventlocationemailhours']; ?>" size="3" maxlength="5"> hours prior
               <select name="remindereventlocationemail2templateID" class="form-control">
                   <option value="0" <?php if (!(strcmp(0, $row_rsEventPrefs['remindereventlocationemail2templateID']))) {echo "selected=\"selected\"";} ?>>None</option>
                   <?php
do {  
?>
                   <option value="<?php echo $row_rsEmailTemplates['ID']?>"<?php if (!(strcmp($row_rsEmailTemplates['ID'], $row_rsEventPrefs['remindereventlocationemail2templateID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsEmailTemplates['templatename']?></option>
                   <?php
} while ($row_rsEmailTemplates = mysql_fetch_assoc($rsEmailTemplates));
  $rows = mysql_num_rows($rsEmailTemplates);
  if($rows > 0) {
      mysql_data_seek($rsEmailTemplates, 0);
	  $row_rsEmailTemplates = mysql_fetch_assoc($rsEmailTemplates);
  }
?>
               </select> 
               <input name="remindereventlocationemail2hours" type="text" class="form-control" value="<?php echo $row_rsEventPrefs['remindereventlocationemail2hours']; ?>" size="3" maxlength="5"> hours prior</td>
             </tr>
             <tr>
               <td align="right"><label for="canceleventuseremailtemplateID">Send cancelled event email to user:</label></td>
               <td class="form-inline"><select name="canceleventuseremailtemplateID" class="form-control">
                   <option value="0" <?php if (!(strcmp(0, $row_rsEventPrefs['canceleventuseremailtemplateID']))) {echo "selected=\"selected\"";} ?>>None</option>
                   <?php
do {  
?>
                   <option value="<?php echo $row_rsEmailTemplates['ID']?>"<?php if (!(strcmp($row_rsEmailTemplates['ID'], $row_rsEventPrefs['canceleventuseremailtemplateID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsEmailTemplates['templatename']?></option>
                   <?php
} while ($row_rsEmailTemplates = mysql_fetch_assoc($rsEmailTemplates));
  $rows = mysql_num_rows($rsEmailTemplates);
  if($rows > 0) {
      mysql_data_seek($rsEmailTemplates, 0);
	  $row_rsEmailTemplates = mysql_fetch_assoc($rsEmailTemplates);
  }
?>
               </select> </td>
             </tr>
             <tr>
               <td align="right"><label for="canceleventlocationemailtemplateID">Send cancelled event email to venue:</label></td>
               <td class="form-inline"><select name="canceleventlocationemailtemplateID" class="form-control">
                   <option value="0" <?php if (!(strcmp(0, $row_rsEventPrefs['canceleventlocationemailtemplateID']))) {echo "selected=\"selected\"";} ?>>None</option>
                   <?php
do {  
?>
                   <option value="<?php echo $row_rsEmailTemplates['ID']?>"<?php if (!(strcmp($row_rsEmailTemplates['ID'], $row_rsEventPrefs['canceleventlocationemailtemplateID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsEmailTemplates['templatename']?></option>
                   <?php
} while ($row_rsEmailTemplates = mysql_fetch_assoc($rsEmailTemplates));
  $rows = mysql_num_rows($rsEmailTemplates);
  if($rows > 0) {
      mysql_data_seek($rsEmailTemplates, 0);
	  $row_rsEmailTemplates = mysql_fetch_assoc($rsEmailTemplates);
  }
?>
               </select> </td>
             </tr>
           </table>
         </div>
         <div class="TabbedPanelsContent">
           <p>Send email on all registrations to:</p>
           <p>
             <input name="registrationalertemail" type="text" id="registrationalertemail" value="<?php echo $row_rsEventPrefs['registrationalertemail']; ?>" size="100" maxlength="255"  class="form-control">
           </p>
           <p>
             <label>
               <input <?php if (!(strcmp($row_rsEventPrefs['registrationalertincludelink'],1))) {echo "checked=\"checked\"";} ?> name="registrationalertincludelink" type="checkbox" id="registrationalertincludelink" value="1">
               Include link back to control panel (adds convenience but can increase spam score)</label>
           </p>
           <p>(additional email settings on each individual registration)</p>
           <p>
             <label>
               <input <?php if (!(strcmp($row_rsEventPrefs['registrationaccesslevel'],1))) {echo "checked=\"checked\"";} ?> name="registrationaccesslevel" type="checkbox" id="registrationaccesslevel" value="1">
               Site membership required for registration</label>
           </p>
           <p>
             <label>
               <input <?php if (!(strcmp($row_rsEventPrefs['allowcancelregistration'],1))) {echo "checked=\"checked\"";} ?> name="allowcancelregistration" type="checkbox" id="allowcancelregistration" value="1">
               Allow registration cancellation</label>
           </p>
           <table class="form-table">
             <tr>
               <td class="text-right"><label for="text_register">Register:</label></td>
               <td><input name="text_register" type="text" id="text_register" value="<?php echo $row_rsEventPrefs['text_register']; ?>" size="50" maxlength="255" class="form-control"></td>
             </tr>
             <tr>
               <td class="text-right"><label for="text_received">Registration received:</label></td>
               <td><input name="text_received" type="text" id="text_received" value="<?php echo $row_rsEventPrefs['text_received']; ?>" size="50" maxlength="255" class="form-control" ></td>
             </tr>
             <tr>
               <td>Defaults:</td>
               <td>&nbsp;</td>
             </tr>
             <tr>
               <td><span class="text-nowrap text-right">Maximum numbers:</span></td>
               <td class="form-inline"><input name="defaultregistrationmax" type="text" value="<?php echo htmlentities($row_rsEventPrefs['defaultregistrationmax'], ENT_COMPAT, 'UTF-8'); ?>" size="5" maxlength="5"  class="form-control"/>
(enter zero if no maximum)</td>
             </tr>
             <tr>
               <td><span class="text-nowrap text-right">Group registration:</span></td>
               <td class="form-inline"><input name="defaultregistrationteam" id="defaultregistrationteam" type="text" value="<?php echo isset($row_rsEventPrefs['defaultregistrationteam']) ? htmlentities($row_rsEventPrefs['defaultregistrationteam'], ENT_COMPAT, 'UTF-8') : "0"; ?>" size="5" maxlength="2"  class="form-control"/>
(number of extra persons allowed on one registration, if any)</td>
             </tr>
           </table>
         </div>
         <div class="TabbedPanelsContent">
           <table class="form-table">
             <tr>
               <th scope="row">Event name:</th>
               <td><input name="eventname" type="text" id="eventname" value="<?php echo $row_rsEventPrefs['eventname']; ?>" size="50" maxlength="50"></td>
             </tr>
             <tr>
               <th scope="row">&nbsp;</th>
               <td>&nbsp;</td>
             </tr>
             <tr>
               <th scope="row">&nbsp;</th>
               <td>&nbsp;</td>
             </tr>
           </table>
         </div>
</div>
     </div>
   <input name="ID" type="hidden" id="ID" value="<?php echo $regionID; ?>" />
     <button type="submit" class="btn btn-primary">Save changes...</button>
       
      
       <input type="hidden" name="MM_update" value="form1" />
     </form></div>
    <script>
var TabbedPanels1 = new Spry.Widget.TabbedPanels("TabbedPanels1");
    </script>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsUserTypes);

mysql_free_result($rsEventPrefs);

mysql_free_result($rsUserGroups);

mysql_free_result($rsEmailTemplates);
?>
