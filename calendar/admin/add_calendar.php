<?php require_once('../../Connections/aquiescedb.php'); ?><?php require_once('../../core/includes/adminAccess.inc.php'); ?><?php require_once('../../core/includes/upload.inc.php'); ?><?php require_once('../includes/calendar.inc.php'); ?><?php require_once('../../mail/includes/sendmail.inc.php'); ?>
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

$regionID = (isset($regionID) && intval($regionID) > 0) ?  intval($regionID) : 1;

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
	$uploaded = getUploads();
	
	if (isset($uploaded) && is_array($uploaded)) { 
		if(isset($uploaded["filename"][0]["newname"]) && $uploaded["filename"][0]["newname"]!="") { 
			$_POST['imageURL'] = $uploaded["filename"][0]["newname"]; 
		}
		if(isset($uploaded["filename1"][0]["newname"]) && $uploaded["filename1"][0]["newname"]!="") { 
			$_POST['attachment1'] = $uploaded["filename1"][0]["newname"]; 
		}
	}
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
  $insertSQL = sprintf("INSERT INTO eventgroup (eventtitle, eventdetails, categoryID, resourceID, customvalue1, customvalue2, usertypeID, imageURL, attachment1, createdbyID, createddatetime, statusID, featured, eventfee, venuefee) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['eventtitle'], "text"),
                       GetSQLValueString($_POST['eventdetails'], "text"),
                       GetSQLValueString($_POST['categoryID'], "int"),
                       GetSQLValueString($_POST['resourceID'], "int"),
                       GetSQLValueString($_POST['customvalue1'], "text"),
                       GetSQLValueString($_POST['customvalue2'], "text"),
                       GetSQLValueString($_POST['usertypeID'], "int"),
                       GetSQLValueString($_POST['imageURL'], "text"),
                       GetSQLValueString($_POST['attachment1'], "text"),
                       GetSQLValueString($_POST['createdbyID'], "int"),
                       GetSQLValueString($_POST['createddatetime'], "date"),
                       GetSQLValueString($_POST['statusID'], "int"),
                       GetSQLValueString(isset($_POST['featured']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['eventfee'], "double"),
                       GetSQLValueString($_POST['venuefee'], "double"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($insertSQL, $aquiescedb) or die(mysql_error());
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
	$eventgroupID = mysql_insert_id();
	$incalendar = isset($_POST['incalendar']) ? true : false;
	$allday = isset($_POST['allday']) ? true : false;
	$recurringend = (isset($_POST['repeats']) && $_POST['recurringend']!="") ? date('Y-m-d 23:59:59',strtotime($_POST['recurringend'])) : $_POST['startdatetime'];
	$recurringinterval = strtoupper($_POST['recurringinterval']);
	$recurringmultiple = intval($_POST['recurringmultiple']);
	if($incalendar) {
		if(isset($_SESSION['debug'])) $_SESSION['log'] .= "Adding times...<br>";
		$eventID = addEvent($eventgroupID,$_POST['startdatetime'],$_POST['enddatetime'],$_POST['eventlocationID'],$_POST['createdbyID'], $allday, $recurringend, $recurringinterval, $recurringmultiple, "", $_POST['nth']." ".$_POST['dow'],$_POST['registration'], $_POST['registrationURL'], $_POST['registrationtext'],$_POST['registrationmax'],$_POST['registrationteam'],$_POST['userID']);
	}
	
	if(isset($_POST['sendemails'])) {
		sendEventEmails($eventID);
	}


  $insertGoTo = "update_calendar.php?eventgroupID=" . $eventgroupID;
  if (isset($_SERVER['QUERY_STRING'])) {
    $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
    $insertGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $insertGoTo));
}

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsEventCategories = "SELECT ID, title FROM eventcategory WHERE active = 1 ORDER BY title ASC";
$rsEventCategories = mysql_query($query_rsEventCategories, $aquiescedb) or die(mysql_error());
$row_rsEventCategories = mysql_fetch_assoc($rsEventCategories);
$totalRows_rsEventCategories = mysql_num_rows($rsEventCategories);

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsUserType = "SELECT usertype.ID, CONCAT(usertype.name,' and above') AS usertype FROM usertype WHERE ID > 0";
$rsUserType = mysql_query($query_rsUserType, $aquiescedb) or die(mysql_error());
$row_rsUserType = mysql_fetch_assoc($rsUserType);
$totalRows_rsUserType = mysql_num_rows($rsUserType);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsEventLocations = "SELECT ID, locationname FROM location WHERE  location.active = 1 AND location.public = 1 AND location.locationname !='' ORDER BY locationname ASC";
$rsEventLocations = mysql_query($query_rsEventLocations, $aquiescedb) or die(mysql_error());
$row_rsEventLocations = mysql_fetch_assoc($rsEventLocations);
$totalRows_rsEventLocations = mysql_num_rows($rsEventLocations);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsResources = "SELECT ID, resourcename FROM eventresource WHERE statusID = 1 ORDER BY resourcename ASC";
$rsResources = mysql_query($query_rsResources, $aquiescedb) or die(mysql_error());
$row_rsResources = mysql_fetch_assoc($rsResources);
$totalRows_rsResources = mysql_num_rows($rsResources);

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
<?php $pageTitle = "Add ". ucwords($row_rsEventPrefs['eventname']);echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script>window.jQuery || document.write('<script src="/3rdparty/jquery/jquery-1.12.1.min.js"><\/script>'); // if not already loaded</script>
<script src="/core/scripts/date-picker/js/datepicker.js"></script>
<script src="../../SpryAssets/SpryValidationTextField.js"></script>
<link href="../../SpryAssets/SpryValidationTextField.css" rel="stylesheet"  />
<?php  $editor_width = 650; $editor_height = 200; $WYSIWYGstyle = "simpletext"; require_once('../../core/tinymce/tinymce.inc.php'); ?>
<link href="../css/calendarDefault.css" rel="stylesheet"  />
<style><!--
<?php if(trim($row_rsEventPrefs['customfield1'])=="") {
	echo ".custom1 { display: none; }";
}

if(trim($row_rsEventPrefs['customfield2'])=="") {
	echo ".custom2 { display: none; }";
}


if($totalRows_rsEventCategories==0) {
	echo ".category { display: none; }";
}


if($totalRows_rsResources==0) {
	echo ".resource { display: none; }";
}

if($totalRows_rsUsers==0) {
	echo ".userlist { display: none; }";
}

?>
--></style>
<script>

$(document).ready(function(e) {
    toggleAllDay();
	toggleRepeats();
	toggleNthDow();
	toggleRegistration();
	
	var endupdated = false;
	$("#datepicker_enddatetime select").change(function() {
		endupdated = true;
	});
	
	$("#datepicker_startdatetime select").change(function() {
		if(endupdated == false) {
			$("#mm-enddatetime").val($("#mm-startdatetime").val()); 
			$("#yy-enddatetime").val($("#yy-startdatetime").val()); 
			$("#dd-enddatetime").val($("#dd-startdatetime").val()); 
			if($("#hh-enddatetime").val() == "" || $("#hh-startdatetime").val() > $("#hh-enddatetime").val()) {
				$("#hh-enddatetime").val($("#hh-startdatetime").val()); 
				$("#mi-enddatetime").val($("#mi-startdatetime").val());
			}
		}
	});
});

$(window).load(function() {
	toggleMidnight(); /* after calendar pop up icon */
});

function toggleAllDay() {
	if(document.getElementById('allday').checked) {
		$(".enddatetime").hide();
		$(".time").css("visibility", "hidden");
	} else {
		$(".enddatetime").show();
		$(".time").css("visibility", "visible");
	}
}

function toggleNthDow() {
	
	if($('#recurringinterval').val()=="nthdow") {
		$(".nthdow").show();
	} else {
		$(".nthdow").hide();
	}
}

function toggleRepeats() {
	if(document.getElementById('repeats').checked) {
		$(".repeats").show();
		
	} else {
		$(".repeats").hide();
	}
}


function toggleRegistration() {
	if($('input[name=registration]:checked').val()==1) { 
		$("#registrationURL").hide();
		$(".registration-options").show();
	} else if($('input[name=registration]:checked').val()==2) {
		$("#registrationURL").show();
		$(".registration-options").show();
	} else {
		$("#registrationURL").hide();
		$(".registration-options").hide();
	}
	
	
	
}

function toggleMidnight() {
	if($('input[name=midnight]').is(':checked')) { 
		$(".enddatetime a, .enddatetime select.dom, .enddatetime select.month,.enddatetime select.year,.enddatetime .timeat").css("visibility", "visible");
	} else {
		$(".enddatetime a, .enddatetime select.dom, .enddatetime select.month,.enddatetime select.year, .enddatetime .timeat").css("visibility", "hidden");
	}
}

function  toggleInCalendar() {
	if($('input[name=incalendar]').is(':checked')) { 
		$("tr.calendar").show();
	} else {
		$("tr.calendar").hide();
	}
}

</script>
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
        <div class="page calendar">
    <h1><i class="glyphicon glyphicon-calendar"></i> Add <?php echo ucwords($row_rsEventPrefs['eventname']); ?></h1>
    <form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1" role="form" enctype="multipart/form-data" >
      <table class="form-table"><tr  class="category form-group">
          <td class="text-nowrap text-right"><label for="categoryID" title="A category is optional but if you have many events listed it may help you or the site vostors find an event they're looking for by filtering the results bu category, e.g. Classes, Meeetings, Sports Events">Category:</label></td>
          <td class="form-inline"><select name="categoryID" class="form-control">
          <option value="">None</option>
          <option value="0">Unavailable/Closed</option>
            <?php
do {  
?>
            <option value="<?php echo $row_rsEventCategories['ID']?>"><?php echo $row_rsEventCategories['title']?></option>
            <?php
} while ($row_rsEventCategories = mysql_fetch_assoc($rsEventCategories));
  $rows = mysql_num_rows($rsEventCategories);
  if($rows > 0) {
      mysql_data_seek($rsEventCategories, 0);
	  $row_rsEventCategories = mysql_fetch_assoc($rsEventCategories);
  }
?>
          </select> 
            <a href="eventcategories/index.php">Manage categories</a>
            </td>
        </tr> 
        
         
        <tr class="form-group">
          <td class="text-nowrap text-right"><label for="eventtitle" title="Enter a descriptive name for your event. Remember if your event occurs more than once it must cover all instances."><?php echo ucwords($row_rsEventPrefs['eventname']); ?> name:</label></td>
          <td><span id="sprytextfield1">
            <input name="eventtitle" type="text"  value="" size="50" maxlength="255"  class="form-control"/>
          <span class="textfieldRequiredMsg">A title is required.</span></span> </td>
        </tr>
        <tr  class="location form-group">
          <td class="text-nowrap text-right"><label for="eventlocationID">Where? </label></td>
          <td class="form-inline">
                    <select name="eventlocationID" id="eventlocationID" class="form-control">
                      <option value="0">Choose (optional or state in event details)...</option>
                      <?php
do {  
?>
                      <option value="<?php echo $row_rsEventLocations['ID']?>"><?php echo $row_rsEventLocations['locationname']?></option>
                      <?php
} while ($row_rsEventLocations = mysql_fetch_assoc($rsEventLocations));
  $rows = mysql_num_rows($rsEventLocations);
  if($rows > 0) {
      mysql_data_seek($rsEventLocations, 0);
	  $row_rsEventLocations = mysql_fetch_assoc($rsEventLocations);
  }
?>
                    </select>
                  <a href="/location/admin/add_location.php?returnURL=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="link_edit icon_with_text">Add Location</a></td>
        </tr> 
        <tr class="form-group">
          <td class="text-nowrap text-right">When?</td>
          <td><label>
      <input name="incalendar" type="checkbox" id="incalendar" onClick="toggleInCalendar()" checked="CHECKED"> Add to calendar
      <label> &nbsp;&nbsp;&nbsp;
      <input name="allday" type="checkbox" id="allday" onClick="toggleAllDay()" checked="CHECKED">
      All day <?php echo ucwords($row_rsEventPrefs['eventname']); ?></label>&nbsp;&nbsp;&nbsp;
      <label><input type="checkbox" name="repeats" id="repeats" onClick="toggleRepeats()">
      Repeats</label></td>
        </tr>
        <tr class="calendar form-group">
          <td class="text-nowrap text-right">on:</td>
          <td><input name="startdatetime" id="startdatetime" type="hidden" value="<?php $setvalue = date('Y-m-d '. $row_rsEventPrefs['daystarttime']); echo $setvalue; ?>" class='highlight-days-67 split-date format-y-m-d divider-dash'/>
    <?php $inputname = "startdatetime"; $time=true;  include("../../core/includes/datetimeinput.inc.php"); ?>&nbsp;&nbsp;&nbsp;
   
    </td>
        </tr>
        <tr  class="calendar form-group">
          <td class="text-nowrap text-right enddatetime">until:</td>
          <td class="enddatetime"><input type="hidden" name="enddatetime" id="enddatetime"  value="<?php $setvalue  = date('Y-m-d '. $row_rsEventPrefs['dayendtime']); echo $setvalue; ?>"  class='highlight-days-67 split-date format-y-m-d divider-dash' />
                  <?php $inputname = "enddatetime"; $time=true; include("../../core/includes/datetimeinput.inc.php"); ?>&nbsp;&nbsp;&nbsp; <label><input type="checkbox" name="midnight" id="midnight" onClick="toggleMidnight()" value="1"> Extends midnight</label></td>
        </tr>
        <tr class="repeats form-group form-inline">
          <td class="text-nowrap text-right">occurs every:</td>
          <td><input name="recurringmultiple" type="text" id="recurringmultiple" value="1" size="4" maxlength="4" class="form-control"/>
                    <select name="recurringinterval" id="recurringinterval" onChange="toggleNthDow();" class="form-control">
                      <option value="days" <?php if(isset($row_rsEventPrefs['defaultrepeatperiod']) && $row_rsEventPrefs['defaultrepeatperiod'] =="days") echo "selected"; ?>>day(s)</option>
                     <option value="weekdays" <?php if(isset($row_rsEventPrefs['defaultrepeatperiod']) && $row_rsEventPrefs['defaultrepeatperiod'] =="weekdays") echo "selected"; ?>>weekday(s)</option>
                      <option value="weeks"  <?php if(!isset($row_rsEventPrefs['defaultrepeatperiod']) || $row_rsEventPrefs['defaultrepeatperiod'] =="weeks") echo "selected"; ?>>week(s)</option>
                      <option value="months" <?php if(isset($row_rsEventPrefs['defaultrepeatperiod']) && $row_rsEventPrefs['defaultrepeatperiod'] =="months") echo "selected"; ?>>month(s) by date</option>
                      <option value="nthdow" <?php if(isset($row_rsEventPrefs['defaultrepeatperiod']) && $row_rsEventPrefs['defaultrepeatperiod'] =="nthdow") echo "selected"; ?>>month(s) by day</option>
                      <option value="years" <?php if(isset($row_rsEventPrefs['defaultrepeatperiod']) && $row_rsEventPrefs['defaultrepeatperiod'] =="years") echo "selected"; ?>>year(s)</option>
                    </select>
                    
                    <span class="nthdow"> on the <select name="nth" class="form-control">
                   
                    <option value="First">First</option>
                    <option value="Second">Second</option>
                    <option value="Third">Third</option>
                     <option value="Fourth">Fourth</option>
                    <option value="Last">Last</option>
                    
                    </select>
                    <select name="dow" class="form-control">
                    <option value="Monday">Monday</option>
                    <option value="Tuesday">Tuesday</option>
                    <option value="Wednesday">Wednesday</option>
                    <option value="Thursday">Thursday</option>
                    <option value="Friday">Friday</option>
                    <option value="Saturday">Saturday</option>
                    <option value="Sunday">Sunday</option>
                    
                    </select></span>
until
<input type="hidden" name="recurringend" id="recurringend"  value=""  class='highlight-days-67 split-date format-y-m-d divider-dash' />
                  <?php $inputname = "recurringend"; include("../../core/includes/datetimeinput.inc.php"); ?></td>
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
            <option value="<?php echo $row_rsUsers['ID']?>"><?php echo $row_rsUsers['firstname']." ".$row_rsUsers['surname']; ?></option>
            <?php
} while ($row_rsUsers = mysql_fetch_assoc($rsUsers));
  $rows = mysql_num_rows($rsUsers);
  if($rows > 0) {
      mysql_data_seek($rsUsers, 0);
	  $row_rsUsers = mysql_fetch_assoc($rsUsers);
  }
?>
          </select></td></tr>
        <tr  class="resource form-group">
          <td class="text-nowrap text-right"><label for="resourceID">Using:</label></td>
          <td class="form-inline">
            <select name="resourceID" id="resourceID" class="form-control">
              <option value="">Not applicable</option>
              <?php
do {  
?>
              <option value="<?php echo $row_rsResources['ID']?>"><?php echo $row_rsResources['resourcename']?></option>
              <?php
} while ($row_rsResources = mysql_fetch_assoc($rsResources));
  $rows = mysql_num_rows($rsResources);
  if($rows > 0) {
      mysql_data_seek($rsResources, 0);
	  $row_rsResources = mysql_fetch_assoc($rsResources);
  }
?><option value="0" >All above</option>
            </select> 
            <a href="resources/index.php">Manage Resources</a></td>
        </tr>
       
        <tr class="custom1 form-group">
		      <td align="right" valign="top" scope="row"><label for="customvalue1"><?php echo $row_rsEventPrefs['customfield1']; ?>:</label></td>
		      <td>
		        <input name="customvalue1" type="text" class="form-control" id="customvalue1" size="50" maxlength="100"></td>
		      </tr>
		    <tr class="custom2 form-group">
		      <td align="right" valign="top" scope="row"><label for="customvalue2"><?php echo $row_rsEventPrefs['customfield2']; ?>:</label></td>
		      <td><input name="customvalue2" type="text" class="form-control" id="customvalue2" size="50" maxlength="100"></td>
		      </tr> <tr class="form-group">
          <td class="text-nowrap text-right top">Details:</td>
          <td><textarea name="eventdetails" cols="50" rows="5" class="tinymce"></textarea></td>
        </tr> 
	       <tr class="form-group">
              <td class="text-nowrap text-right top"><label for="filename">Image:</label></td>
              <td>
                <span class="upload">
                  <input name="filename" type="file" class="fileinput" id="filename" maxlength="50" />
                  <input name="imageURL" type="hidden" id="imageURL"  />
                </span></td>
            </tr><tr class="form-group">
                <td class="text-nowrap text-right"><label for="attachment">Attachment:</label></td>
                <td>
                <span class="upload"><input type="file" name="filename1" id="filename1" class="fileinput"  /></span>
                <input type="hidden" name="attachment1" id="attachment1" /></td>
              </tr> 
	       <tr class="form-group">
          <td class="text-nowrap text-right"><label for="usertypeID">Visible to:</label></td>
          <td class="form-inline">
            <select name="usertypeID" id="usertypeID" class="form-control">
              <option value="0">Everyone</option>
              <?php
do {  
?>
              <option value="<?php echo $row_rsUserType['ID']?>"><?php echo $row_rsUserType['usertype']?></option>
              <?php
} while ($row_rsUserType = mysql_fetch_assoc($rsUserType));
  $rows = mysql_num_rows($rsUserType);
  if($rows > 0) {
      mysql_data_seek($rsUserType, 0);
	  $row_rsUserType = mysql_fetch_assoc($rsUserType);
  }
?>
            </select> &nbsp;&nbsp;&nbsp; <label><input name="featured" type="checkbox"> Featured <span data-toggle="tooltip" class="help" title="Checking this will show this event at the top of any chronological listings"></span></label></td>
        </tr> 
	        <tr class="form-group">
	          <td class="text-nowrap top text-right">Registration:</td>
	          <td class="form-inline">
        <label><input type="radio" name="registration"  value="0"  checked  onClick="toggleRegistration()"  /> Off</label>&nbsp;&nbsp;&nbsp;        
        
        <label><input type="radio" name="registration"  value="1"  <?php if ($row_rsThisEvent['registration']==1) {echo "checked=\"checked\"";} ?> onClick="toggleRegistration()"    /> On (on-site)</label>&nbsp;&nbsp;&nbsp;        
        
        <label><input type="radio" name="registration"  value="2"  <?php if ($row_rsThisEvent['registration']==2) {echo "checked=\"checked\"";} ?>  onClick="toggleRegistration()"  /> On (off-site)</label><label id="registrationURL">URL: <input name="registrationURL" type="text" value="" size="50" maxlength="255" placeholder="Booking link"></label>  <br>     
        
       <div class="registration-options">
       <table class="form-table">
       <tr class="form-group">
         <th>
           <label for="registrationtext">Registration/booking/tickets link text:</label>  </th><td>
             <input name="registrationtext" type="text" id="registrationtext" value="Register" size="50" maxlength="255" placeholder="Enter text for registration link"></td></tr>
       <tr class="form-group">
         <td class="text-nowrap text-right">Maximum numbers:</td>
         <td class="form-inline"><input name="registrationmax" type="text" value="<?php echo $row_rsEventPrefs['defaultregistrationmax']; ?>" size="5" maxlength="5" />
           (enter zero if no maximum)</td>
       </tr>
       <tr class="form-group">
         <td class="text-nowrap text-right">Group registration:</td>
         <td class="form-inline"><input name="registrationteam" id="registrationteam" type="text" value="<?php echo $row_rsEventPrefs['defaultregistrationteam']; ?>" size="5" maxlength="2" />
           (number of extra persons allowed on one registration, if any)</td>
       </tr>
       </table>
              
         </div> 
           
           </td>
            </tr>
	        <tr class="form-group eventfee">
	          <td class="text-nowrap text-right"><label for="eventfee">Event fee:</label></td>
	          <td class="form-inline">
              <input type="text" name="eventfee" id="eventfee" class="form-control" size="10"> &nbsp;&nbsp;&nbsp; <label for="venuefee">Venue fee:</label> <input type="text" name="venuefee" id="venuefee" class="form-control" size="10"></td>
          </tr>
	        <tr class="form-group">
	          <td class="text-nowrap text-right">&nbsp;</td>
	          <td><button type="submit" class="btn btn-primary" >Add event</button> <label><input type="checkbox" name="sendemails">
	          Send  event emails to venue and user</label></td>
            </tr>
      </table>
      <input type="hidden" name="imageURL" value="" />
      <input type="hidden" name="createdbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
      <input type="hidden" name="createddatetime" value="<?php echo date("Y-m-d H:i:s"); ?>" />
      <input type="hidden" name="statusID" value="1" />
      <input type="hidden" name="MM_insert" value="form1" />
    </form>
   </div>
    <script>
<!--
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1");
//-->
    </script>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsEventCategories);

mysql_free_result($rsLoggedIn);

mysql_free_result($rsUserType);

mysql_free_result($rsEventLocations);

mysql_free_result($rsResources);

mysql_free_result($rsEventPrefs);

mysql_free_result($rsUsers);
?>
