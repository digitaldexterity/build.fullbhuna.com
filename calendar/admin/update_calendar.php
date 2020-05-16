<?php require_once('../../Connections/aquiescedb.php'); ?><?php require_once('../../core/includes/adminAccess.inc.php'); ?>
<?php require_once('../../core/includes/upload.inc.php'); ?>
<?php require_once('../../core/includes/framework.inc.php'); ?>
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

if(isset($_GET['deletefirstID']) && intval($_GET['deletefirstID'])>0) {
	$delete = "DELETE FROM event WHERE firsteventID = ".intval($_GET['deletefirstID']);
	mysql_select_db($database_aquiescedb, $aquiescedb);
  	mysql_query($delete, $aquiescedb) or die(mysql_error());
	header("location: update_calendar.php?eventgroupID=".intval($_GET['eventgroupID'])); exit;
}

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
	$uploaded = getUploads();
	if(isset($_POST['noImage'])) {
		$_POST['imageURL'] = "";
	}
	if(isset($_POST['removeattachment'])) {
		$_POST['attachment1'] = "";
	}
	if (isset($uploaded) && is_array($uploaded)) { 
		if(isset($uploaded["filename"][0]["newname"]) && $uploaded["filename"][0]["newname"]!="") { 
			$_POST['imageURL'] = $uploaded["filename"][0]["newname"]; 
		}
		if(isset($uploaded["filename1"][0]["newname"]) && $uploaded["filename1"][0]["newname"]!="") { 
			$_POST['attachment1'] = $uploaded["filename1"][0]["newname"]; 
		}
	}
}
	
	
if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE eventgroup SET eventtitle=%s, eventdetails=%s, categoryID=%s, resourceID=%s, customvalue1=%s, customvalue2=%s, usertypeID=%s, imageURL=%s, attachment1=%s, modifieddatetime=%s, modifiedbyID=%s, statusID=%s, featured=%s, eventfee=%s, venuefee=%s WHERE ID=%s",
                       GetSQLValueString($_POST['eventtitle'], "text"),
                       GetSQLValueString($_POST['eventdetails'], "text"),
                       GetSQLValueString($_POST['categoryID'], "int"),
                       GetSQLValueString($_POST['resourceID'], "int"),
                       GetSQLValueString($_POST['customvalue1'], "text"),
                       GetSQLValueString($_POST['customvalue2'], "text"),
                       GetSQLValueString($_POST['usertypeID'], "int"),
                       GetSQLValueString($_POST['imageURL'], "text"),
                       GetSQLValueString($_POST['attachment1'], "text"),
                       GetSQLValueString($_POST['modifieddatetime'], "date"),
                       GetSQLValueString($_POST['modifiedbyID'], "int"),
                       GetSQLValueString($_POST['statusID'], "int"),
                       GetSQLValueString(isset($_POST['featured']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['eventfee'], "double"),
                       GetSQLValueString($_POST['venuefee'], "double"),
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
  header(sprintf("Location: %s", $updateGoTo));exit;
}

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsEventCategories = "SELECT ID, title FROM eventcategory WHERE active = 1 ORDER BY title ASC";
$rsEventCategories = mysql_query($query_rsEventCategories, $aquiescedb) or die(mysql_error());
$row_rsEventCategories = mysql_fetch_assoc($rsEventCategories);
$totalRows_rsEventCategories = mysql_num_rows($rsEventCategories);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsEventLocations = "SELECT ID, locationname FROM location WHERE  location.active = 1 AND location.public = 1 AND location.locationname !='' GROUP BY locationname ORDER BY locationname ASC";
$rsEventLocations = mysql_query($query_rsEventLocations, $aquiescedb) or die(mysql_error());
$row_rsEventLocations = mysql_fetch_assoc($rsEventLocations);
$totalRows_rsEventLocations = mysql_num_rows($rsEventLocations);

$colname_rsEventGroup = "-1";
if (isset($_GET['eventgroupID'])) {
  $colname_rsEventGroup = $_GET['eventgroupID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsEventGroup = sprintf("SELECT eventgroup.*, creator.firstname AS creatorfirstname, creator.surname AS creatorsurname, users.firstname, users.surname FROM eventgroup LEFT JOIN users AS creator ON (eventgroup.createdbyID = creator.ID) LEFT JOIN users ON (eventgroup.modifiedbyID = users.ID) WHERE eventgroup.ID = %s", GetSQLValueString($colname_rsEventGroup, "int"));
$rsEventGroup = mysql_query($query_rsEventGroup, $aquiescedb) or die(mysql_error());
$row_rsEventGroup = mysql_fetch_assoc($rsEventGroup);
$totalRows_rsEventGroup = mysql_num_rows($rsEventGroup);

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
$query_rsEventPrefs = "SELECT * FROM eventprefs WHERE ID =".$regionID."";
$rsEventPrefs = mysql_query($query_rsEventPrefs, $aquiescedb) or die(mysql_error());
$row_rsEventPrefs = mysql_fetch_assoc($rsEventPrefs);
$totalRows_rsEventPrefs = mysql_num_rows($rsEventPrefs);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsResources = "SELECT ID, resourcename FROM eventresource WHERE statusID = 1 ORDER BY resourcename ASC";
$rsResources = mysql_query($query_rsResources, $aquiescedb) or die(mysql_error());
$row_rsResources = mysql_fetch_assoc($rsResources);
$totalRows_rsResources = mysql_num_rows($rsResources);

$varEventGroup_rsAllTags = "-1";
if (isset($_GET['eventgroupID'])) {
  $varEventGroup_rsAllTags = $_GET['eventgroupID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsAllTags = sprintf("SELECT tag.ID, tagname, taggroup.taggroupname, tagged.tagID AS tagged FROM tag LEFT JOIN taggroup ON (tag. taggroupID = taggroup.ID) LEFT JOIN tagged ON (tagged.tagID =tag.ID AND tagged.eventgroupID = %s) ORDER BY taggroupID, tagname ASC", GetSQLValueString($varEventGroup_rsAllTags, "int"));
$rsAllTags = mysql_query($query_rsAllTags, $aquiescedb) or die(mysql_error());
$row_rsAllTags = mysql_fetch_assoc($rsAllTags);
$totalRows_rsAllTags = mysql_num_rows($rsAllTags);

?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Update Calendar"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script>window.jQuery || document.write('<script src="/3rdparty/jquery/jquery-1.12.1.min.js"><\/script>'); // if not already loaded</script>
<?php $editor_width = 650; $editor_height = 200; $WYSIWYGstyle = "simpletext";
require_once('../../core/tinymce/tinymce.inc.php'); ?>
<script src="../../SpryAssets/SpryValidationTextField.js"></script>
<link href="../../SpryAssets/SpryValidationTextField.css" rel="stylesheet"  />
<script src="/core/scripts/date-picker/js/datepicker.js"></script>
<script src="../../SpryAssets/SpryTabbedPanels.js"></script>
<script>
$(document).ready(function(e) {
	$('#fieldset_addtimings').hide();
	toggleAllDay();
	toggleRepeats();
	toggleNthDow();
    addListener("change" , updateEndDate , document.getElementById('yy-startdatetime'));
	addListener("change" , updateEndDate , document.getElementById('mm-startdatetime'));
	addListener("change" , updateEndDate , document.getElementById('dd-startdatetime'));
	addListener("change" , updateEndDate , document.getElementById('hh-startdatetime'));
	addListener("change" , updateEndDate , document.getElementById('mi-startdatetime'));
	addListener("change" , updateEndDate , document.getElementById('startdatetime'));
});



function updateEndDate() {
	updateDate_startdatetime();
	if(document.getElementById('startdatetime').value !="" && document.getElementById('enddatetime').value !="" && document.getElementById('startdatetime').value > document.getElementById('enddatetime').value) {
	copyDate();
	}
	
}
function checkData()
{
var error = '';
	if (document.getElementById('startdatetime').value == "") { 
		error += "You must have a start date\n";
	} else {
		if (document.getElementById('enddatetime').value == "") copyDate();
	}
	
	if (document.getElementById('enddatetime').value > 0 && document.getElementById('startdatetime').value > document.getElementById('enddatetime').value ) error += "The start date must come before the end date\n";
	if (document.getElementById('recurringend').value > 0 && document.getElementById('startdatetime').value > document.getElementById('recurringend').value ) error += "The recurring end date must come after the start date\n";
	if (document.getElementById('repeats').checked && document.getElementById('recurringend').value =="") error += "You have set the event to repeat but have not entered a finish date.\n";
	
	if (error) // form not complete
	{
		alert("You cannot submit the event times because of the following errors:\n\n"+error+"\nPlease rectify and try again.");
		return false;
	}
	else // form OK
	{
		return true;
	}
} // end func

function copyDate()
{
	document.getElementById('yy-enddatetime').options[document.getElementById('yy-startdatetime').selectedIndex].selected = true;
	document.getElementById('mm-enddatetime').options[document.getElementById('mm-startdatetime').selectedIndex].selected = true;
	document.getElementById('dd-enddatetime').options[document.getElementById('dd-startdatetime').selectedIndex].selected = true;
	// only  update time if it's later
	if(document.getElementById('hh-startdatetime').selectedIndex>=document.getElementById('hh-enddatetime').selectedIndex) {
	// minutes
		document.getElementById('mi-enddatetime').options[document.getElementById('mi-startdatetime').selectedIndex].selected = true;
		// for hour - always add one unless null or 24 when set back to 00
		hourSelected = document.getElementById('hh-startdatetime').selectedIndex; 
		hourSelected = (hourSelected>0) ? hourSelected+1 : 0;
		hourSelected = (hourSelected>=24) ? 1 : hourSelected;
		document.getElementById('hh-enddatetime').options[hourSelected].selected = true;
	}
	updateDate_enddatetime();
}

function checkForm() {
	
	if(document.getElementById('eventcount').value == 0) {
		return confirm('No date or times added.\n\nAre you sure you want to save this event without any times?\n\nIf not, click Cancel, set times and click Add button.');
	}
	return true;
}

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

function addTimes() {
	if(checkData()) {
		querystring = 'addevent=true&eventgroupID=<?php echo intval($_GET['eventgroupID']); ?>&startdatetime='+document.getElementById('startdatetime').value+'&enddatetime='+document.getElementById('enddatetime').value+'&recurringmultiple='+document.getElementById('recurringmultiple').value+'&recurringinterval='+document.getElementById('recurringinterval').value+'&recurringend='+document.getElementById('recurringend').value+'&eventlocationID='+document.getElementById('eventlocationID').value+'&nthdow='+document.getElementById('nth').value+" "+document.getElementById('dow').value+'&createdbyID=<?php echo $row_rsLoggedIn['ID'];  ?>';
		if(document.getElementById('repeats').checked) querystring +="&repeats=true";
		$.get('ajax/timings.php?'+querystring,'timings', function(data, status){
   		$("#timings").html(data);
  	}); 
	}
}

function toggleHistory(checked) {
	querystring = 'eventgroupID=<?php echo intval($_GET['eventgroupID']); ?>';
	querystring += checked ? "&showhistory=1" : "";
	$.get('ajax/timings.php?'+querystring,'timings', function(data, status){
   		$("#timings").html(data);
  	});
}

              </script>
<style >
<!--
<?php echo "#recurring {
 display: none;
}
";
?><?php if($totalRows_rsResources==0) {
 echo ".resource { display: none; }";
}
?> <?php if(trim($row_rsEventPrefs['customfield1'])=="") {
 echo ".custom1 { display: none; }";
}
 if(trim($row_rsEventPrefs['customfield2'])=="") {
 echo ".custom2 { display: none; }";
}

if($totalRows_rsEventCategories==0) {
	echo ".category { display: none; }";
}
 ?>
-->
</style>
<link href="../../SpryAssets/SpryTabbedPanels.css" rel="stylesheet"  />
<link href="/core/tags/css/tags.css" rel="stylesheet" >
<script>

function updateTag(isChecked, tagID) {
	if(isChecked) { // add tag
	$.ajax({url: "/core/tags/ajax/addtag.ajax.php?tagID="+tagID+"&eventgroupID="+<?php echo $row_rsEventGroup['ID']; ?>+"&createdbyID="+<?php echo $row_rsLoggedIn['ID']; ?>, success: function(result){
				$("#info").html(result);
				}
			});
	} else { // remove tag
	$.ajax({url: "/core/tags/ajax/removetag.ajax.php?tagID="+tagID+"&eventgroupID="+<?php echo $row_rsEventGroup['ID']; ?>+"&createdbyID="+<?php echo $row_rsLoggedIn['ID']; ?>, success: function(result){
				$("#info").html(result);
				}
			});
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
    <h1><i class="glyphicon glyphicon-calendar"></i> <?php echo $row_rsEventGroup['eventtitle']; ?></h1>
    <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
      <li><a href="index.php" class="link_undo"><i class="glyphicon glyphicon-arrow-left"></i> Back to calendar</a></li>
      <li><a href="add_calendar.php"  onClick="return confirm('Do you want to add a completely new event?\n\nNOTE: To add more occurances to THIS event, hit cancel then click on \'Add more occurances\' below.')"><i class="glyphicon glyphicon-plus-sign"></i> Add New <?php echo ucwords($row_rsEventPrefs['eventname']); ?></a></li>
      <li><a href="../../core/tags/admin/index.php" class="link_manage"><i class="glyphicon glyphicon-cog"></i> Manage Tags</a></li>
    </ul></div></nav>
    <form action="<?php echo $editFormAction; ?>" method="post" enctype="multipart/form-data" name="form1" id="form1" role="form">
      <div id="TabbedPanels1" class="TabbedPanels">
        <ul class="TabbedPanelsTabGroup">
          <li class="TabbedPanelsTab" tabindex="0">Times</li>
          <li class="TabbedPanelsTab" tabindex="0"><?php echo ucwords($row_rsEventPrefs['eventname']); ?> details</li>
          <li class="TabbedPanelsTab" tabindex="0">Tags</li>
        </ul>
        <div class="TabbedPanelsContentGroup">
          <div class="TabbedPanelsContent">
            <p id="link_addtimings"><a href="javascript:void(0);" class="link_add icon_with_text" onClick="$('#link_addtimings').hide();$('#fieldset_addtimings').show();">Add more occurances...</a>
              <label>
                <input type="checkbox" name="showhistory" <?php if(isset($_GET['showhistory'])) echo "checked"; ?> onClick="toggleHistory(this.checked);">
                Show historical</label>
            </p>
            <fieldset id="fieldset_addtimings">
              <legend>Add times</legend>
              <table border="0" cellpadding="2" cellspacing="0" class="form-table">
                <tr>
                 <td class="text-nowrap text-right">When?</td>
                 <td><label>
     
      <input name="allday" type="checkbox" id="allday" onClick="toggleAllDay()" checked="CHECKED">
      All day <?php echo ucwords($row_rsEventPrefs['eventname']); ?></label>&nbsp;&nbsp;&nbsp;
      <label><input type="checkbox" name="repeats" id="repeats" onClick="toggleRepeats()">
      Repeats</label></td></tr> <tr>
                  <td class="text-nowrap text-right">on:</td>
                  <td><input name="startdatetime" id="startdatetime" type="hidden" value="<?php $setvalue = date('Y-m-d '. $row_rsEventPrefs['daystarttime']); echo $setvalue; ?>" class='highlight-days-67 split-date format-y-m-d divider-dash'/>
                    <?php $inputname = "startdatetime"; $time=true;  include("../../core/includes/datetimeinput.inc.php"); ?>
                    </td>
                </tr>
                <tr class="enddatetime">
                  <td class="text-nowrap text-right">until:</td>
                  <td><input type="hidden" name="enddatetime" id="enddatetime"  value="<?php $setvalue  = date('Y-m-d '. $row_rsEventPrefs['dayendtime']); echo $setvalue;  ?>"  class='highlight-days-67 split-date format-y-m-d divider-dash' />
                    <?php $inputname = "enddatetime"; $time=true; include("../../core/includes/datetimeinput.inc.php"); ?></td>
                </tr>
                <tr class="repeats">
                  <td class="text-nowrap text-right">occurs every:</td>
                  <td class="form-inline"><input name="recurringmultiple" type="text" id="recurringmultiple" value="1" size="4" maxlength="4" class="form-control" />
                    <select name="recurringinterval" id="recurringinterval" onChange="toggleNthDow();"  class="form-control">
                      <option value="days" <?php if(isset($row_rsEventPrefs['defaultrepeatperiod']) && $row_rsEventPrefs['defaultrepeatperiod'] =="days") echo "selected"; ?>>day(s)</option>
                      <option value="weekdays" <?php if(isset($row_rsEventPrefs['defaultrepeatperiod']) && $row_rsEventPrefs['defaultrepeatperiod'] =="weekdays") echo "selected"; ?>>weekday(s)</option>
                      <option value="weeks"  <?php if(!isset($row_rsEventPrefs['defaultrepeatperiod']) || $row_rsEventPrefs['defaultrepeatperiod'] =="weeks") echo "selected"; ?>>week(s)</option>
                      <option value="months" <?php if(isset($row_rsEventPrefs['defaultrepeatperiod']) && $row_rsEventPrefs['defaultrepeatperiod'] =="months") echo "selected"; ?>>month(s) by date</option>
                      <option value="nthdow" <?php if(isset($row_rsEventPrefs['defaultrepeatperiod']) && $row_rsEventPrefs['defaultrepeatperiod'] =="nthdow") echo "selected"; ?>>month(s) by day</option>
                      <option value="years" <?php if(isset($row_rsEventPrefs['defaultrepeatperiod']) && $row_rsEventPrefs['defaultrepeatperiod'] =="years") echo "selected"; ?>>year(s)</option>
                    </select>
                    <span class="nthdow"> on the
                    <select name="nth" id="nth"  class="form-control">
                      <option value="First">First</option>
                      <option value="Second">Second</option>
                      <option value="Third">Third</option>
                      <option value="Fourth">Fourth</option>
                      <option value="Last">Last</option>
                    </select>
                    <select name="dow" id="dow"  class="form-control" >
                      <option value="Monday">Monday</option>
                      <option value="Tuesday">Tuesday</option>
                      <option value="Wednesday">Wednesday</option>
                      <option value="Thursday">Thursday</option>
                      <option value="Friday">Friday</option>
                      <option value="Saturday">Saturday</option>
                      <option value="Sunday">Sunday</option>
                    </select>
                    </span> until
                    <input type="hidden" name="recurringend" id="recurringend"  value=""  class='highlight-days-67 split-date format-y-m-d divider-dash'/>
                    <?php $inputname = "recurringend"; require("../../core/includes/datetimeinput.inc.php"); ?></td>
                </tr>
                <tr class="location">
                  <td class="text-nowrap text-right"><label for="eventlocationID">Where? </label></td>
                  <td><select name="eventlocationID" id="eventlocationID" class="form-control">
                      <option value="0">Choose (or state in event details)...</option>
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
                
                <tr>
                  <td colspan="2"><button name="addbutton" type="button" class="btn btn-default btn-secondary" id="addbutton" onClick="addTimes(); return false;" >Add times</button>
                    <button name="save" type="submit" class="btn btn-default btn-secondary" id="save2"  onClick="return checkForm();">Save changes</button></td>
                </tr>
              </table>
            </fieldset>
            <div id="timings"></div>
          </div>
          <div class="TabbedPanelsContent">
            <table class="form-table">
              <tr class="category">
                <td class="text-nowrap text-right">Category:</td>
                <td><select name="categoryID">
                    <option value="">None</option>
                    <option value="0" <?php if ( $row_rsEventGroup['categoryID']==0) {echo "selected=\"selected\"";} ?>>Unavailable/Closed</option>
                    <?php
do {  
?>
                    <option value="<?php echo $row_rsEventCategories['ID']?>"<?php if (!(strcmp($row_rsEventCategories['ID'], $row_rsEventGroup['categoryID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsEventCategories['title']?></option>
                    <?php
} while ($row_rsEventCategories = mysql_fetch_assoc($rsEventCategories));
  $rows = mysql_num_rows($rsEventCategories);
  if($rows > 0) {
      mysql_data_seek($rsEventCategories, 0);
	  $row_rsEventCategories = mysql_fetch_assoc($rsEventCategories);
  }
?>
                  </select>
                  <a href="eventcategories/index.php">Manage categories</a>  </td>
              </tr>
              <tr>
                <td class="text-nowrap text-right">Event:</td>
                <td><span id="sprytextfield1">
                  <input name="eventtitle" type="text"  value="<?php echo $row_rsEventGroup['eventtitle']; ?>" size="50" maxlength="255" class="form-control" />
                  <span class="textfieldRequiredMsg">A title is required.</span></span> </td>
              </tr>
              <tr class="custom1">
                <td align="right" valign="top" scope="row"><label for="customvalue1"><?php echo $row_rsEventPrefs['customfield1']; ?>:</label></td>
                <td><input name="customvalue1" type="text" id="customvalue1" value="<?php echo $row_rsEventGroup['customvalue1']; ?>" size="50" maxlength="100" class="form-control"></td>
              </tr>
              <tr class="custom2">
                <td align="right" valign="top" scope="row"><label for="customvalue2"><?php echo $row_rsEventPrefs['customfield2']; ?>:</label></td>
                <td><input name="customvalue2" type="text" id="customvalue2" value="<?php echo $row_rsEventGroup['customvalue2']; ?>" size="50" maxlength="100" class="form-control"></td>
              </tr>
              <tr class="resource">
                <td class="text-nowrap text-right"><label for="resourceID">Using:</label></td>
                <td class="form-inline"><select name="resourceID" id="resourceID" class="form-control">
                    <option value="" <?php if (!(strcmp("", $row_rsEventGroup['resourceID']))) {echo "selected=\"selected\"";} ?>>Not applicable</option>
                    <?php
do {  
?>
                    <option value="<?php echo $row_rsResources['ID']?>"<?php if (!(strcmp($row_rsResources['ID'], $row_rsEventGroup['resourceID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsResources['resourcename']?></option>
                    <?php
} while ($row_rsResources = mysql_fetch_assoc($rsResources));
  $rows = mysql_num_rows($rsResources);
  if($rows > 0) {
      mysql_data_seek($rsResources, 0);
	  $row_rsResources = mysql_fetch_assoc($rsResources);
  }
?>
                    <option value="0" <?php if ($row_rsEventGroup['resourceID']==0) {echo "selected=\"selected\"";} ?>>All above</option>
                  </select>
                  <a href="resources/index.php">Manage Resources</a></td>
              </tr>
              <tr>
                <td class="text-nowrap text-right top">Details:</td>
                <td><textarea name="eventdetails" cols="50" rows="5" class="tinymce"><?php echo $row_rsEventGroup['eventdetails']; ?></textarea></td>
              </tr>
              <tr>
                <td class="text-nowrap text-right top"><label for="filename">Image:</label></td>
                <td><?php if (isset($row_rsEventGroup['imageURL'])) { ?>
                    <img src="<?php echo getImageURL($row_rsEventGroup['imageURL'], "medium"); ?>" alt="Current image" />
                    <label>
                      <input name="noImage" type="checkbox" value="1" />
                      Remove image</label>
                    <br />
                    <?php } ?>
                  <span class="upload">
                  <input name="filename" type="file" class="fileinput" id="filename" maxlength="50" />
                  <input name="imageURL" type="hidden" id="imageURL" value="<?php echo $row_rsEventGroup['imageURL']; ?>" />
                  </span></td>
              </tr>
              <tr>
                <td class="text-nowrap text-right"><label for="attachment">Attachment:</label></td>
                <td><?php if(isset($row_rsEventGroup['attachment1']) && $row_rsEventGroup['attachment1']!="") { 
		  echo "<a href = \"/Uploads/".$row_rsEventGroup['attachment1']."\" target=\"_blank\">".$row_rsEventGroup['attachment1']."</a>"; ?>
                  <label>
                    <input type="checkbox" name="removeattachment" id="removeattachment" />
                    Remove </label>
                  <br />
                  <?php } ?>
                  <span class="upload">
                  <input type="file" name="filename1" id="filename1" class="fileinput" />
                  </span>
                  <input name="attachment1" type="hidden" id="attachment1" value="<?php echo $row_rsNews['attachment1']; ?>" /></td>
              </tr><tr class="form-group eventfee">
	          <td class="text-nowrap text-right"><label for="eventfee">Event fee:</label></td>
	          <td class="form-inline">
              <input name="eventfee" type="text" class="form-control" id="eventfee" value="<?php echo number_format($row_rsEventGroup['eventfee'],2,".",""); ?>" size="10">  &nbsp;&nbsp;&nbsp; <label for="venuefee">Venue fee:</label> <input type="text" name="venuefee" id="venuefee" class="form-control" size="10" value="<?php echo number_format($row_rsEventGroup['venuefee'],2,".",""); ?>"></td>
          </tr>
              <tr>
                <td class="text-nowrap text-right"><label for="usertypeID">Visible to:</label></td>
                <td class="form-inline"><select name="usertypeID" id="usertypeID" class="form-control">
                    <option value="0" <?php if (!(strcmp(0, $row_rsEventGroup['usertypeID']))) {echo "selected=\"selected\"";} ?>>Everyone</option>
                    <?php
do {  
?>
                    <option value="<?php echo $row_rsUserType['ID']?>"<?php if (!(strcmp($row_rsUserType['ID'], $row_rsEventGroup['usertypeID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsUserType['usertype']?></option>
                    <?php
} while ($row_rsUserType = mysql_fetch_assoc($rsUserType));
  $rows = mysql_num_rows($rsUserType);
  if($rows > 0) {
      mysql_data_seek($rsUserType, 0);
	  $row_rsUserType = mysql_fetch_assoc($rsUserType);
  }
?>
                  </select> &nbsp;&nbsp;&nbsp; <label><input <?php if (!(strcmp($row_rsEventGroup['featured'],1))) {echo "checked=\"checked\"";} ?> name="featured" type="checkbox" value="1"> Featured <span data-toggle="tooltip" class="help" title="Checking this will show this event at the top of any chronological listings"></span></label></td>
              </tr>
            </table>
          </div>
          <div class="TabbedPanelsContent">
            <div id="info"></div>
            <?php if ($totalRows_rsAllTags > 0) { // Show if recordset not empty ?>
            <?php   $groupname = ""; do { 
		  if($row_rsAllTags['taggroupname'] != $groupname) {
			  $groupname = $row_rsAllTags['taggroupname'];
			  echo "<h3>".$groupname."</h3>";
		  }?>
            <label>
              <input type="checkbox" value="<?php echo $row_rsAllTags['ID']; ?>" onClick="updateTag(this.checked, this.value)" <?php if(isset($row_rsAllTags['tagged'])) echo "checked"; ?>>
              &nbsp;<?php echo $row_rsAllTags['tagname']; ?></label>
            &nbsp;&nbsp;
            <?php } while ($row_rsAllTags = mysql_fetch_assoc($rsAllTags)); ?>
            <?php } else { ?>
            <p>There are currently no tags set up.</p>
            <?php } ?>
          </div>
        </div>
      </div>
      </div>
      <div>
        <p>
          <button name="save" type="submit" class="btn btn-primary" id="save" onClick="return checkForm();">Save changes</button>
          <span class="TabbedPanelsContent">
          <input type="hidden" name="modifiedbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
          <input type="hidden" name="modifieddatetime" value="<?php echo date("Y-m-d H:i:s"); ?>" />
          <input type="hidden" name="statusID" value="1" />
          <input name="ID" type="hidden" id="ID" value="<?php echo $row_rsEventGroup['ID']; ?>" />
          <input type="hidden" name="MM_update" value="form1" />
          </span></p>
        <p><em>Created by: <?php echo $row_rsEventGroup['creatorfirstname']; ?> <?php echo $row_rsEventGroup['creatorsurname']; ?> on <?php echo date('l jS F',strtotime($row_rsEventGroup['createddatetime'])); ?> at <?php echo date('g:ia',strtotime($row_rsEventGroup['createddatetime'])); ?>
          <?php if(isset($row_rsEventGroup['modifieddatetime'])) { ?>
          <br />
          Last updated by: <?php echo $row_rsEventGroup['firstname']; ?> <?php echo $row_rsEventGroup['surname']; ?> on <?php echo date('l jS F',strtotime($row_rsEventGroup['modifieddatetime'])); ?> at <?php echo date('g:ia',strtotime($row_rsEventGroup['modifieddatetime'])); ?>
          <?php } ?>
        </em></p>
      </div>
    </form>
    <?php if (isset($_GET['defaultTab'])) { echo '<script type="text/javascript">
<!--
var TabbedPanels1 = new Spry.Widget.TabbedPanels("TabbedPanels1", {defaultTab:'.intval($_GET['defaultTab']).'});
//-->
    </script>'; } else { ?>
    <script>
<!--
var TabbedPanels1 = new Spry.Widget.TabbedPanels("TabbedPanels1", {defaultTab:0});
//-->
    </script>
    <?php } ?>
    <script>
<!--
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1");
//-->

$.get('ajax/timings.php?eventgroupID=<?php echo intval($_GET['eventgroupID']); ?>', function(data, status){
   		$("#timings").html(data);
  	});
    </script>
    </div>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsEventCategories);

mysql_free_result($rsEventLocations);

mysql_free_result($rsEventGroup);

mysql_free_result($rsLoggedIn);

mysql_free_result($rsUserType);

mysql_free_result($rsEventPrefs);

mysql_free_result($rsResources);

mysql_free_result($rsAllTags);
?>
