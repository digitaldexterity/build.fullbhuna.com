<?php require_once('../../Connections/aquiescedb.php'); ?><?php require_once('../../core/includes/adminAccess.inc.php'); ?>
<?php require_once('../includes/calendar.inc.php'); ?><?php require_once('../../mail/includes/sendmail.inc.php'); ?>
<?php

$_GET['startdate'] = isset($_GET['startdate']) ? $_GET['startdate'] : date('Y-m-d');
$_GET['enddate'] = isset($_GET['enddate']) ? $_GET['enddate'] : date('Y-m-d', strtotime("+ 1 YEAR"));


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

$MM_restrictGoTo = "../../login/index.php?notloggedin=true";
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


if(isset($_GET['deleteeventgroupID'])) {
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$delete = "DELETE FROM eventgroup WHERE ID = ".intval($_GET['deleteeventgroupID']);
	mysql_query($delete, $aquiescedb) or die(mysql_error());
	header("location: index.php"); exit;
}

if($_SESSION['MM_UserGroup']==10 && isset($_GET['deleteall'])) {
	deleteAllEvents();
}

if(isset($_POST['checkboxaction'])) {
	$count = 0;
	// key - eventID
	// value - eventgroupID
	mysql_select_db($database_aquiescedb, $aquiescedb);
	if($_POST['checkboxaction']=="updatecategory" && $_POST['categoryID']!="") {
		$categoryID = $_POST['categoryID']>0 ?  $_POST['categoryID'] : "";
		foreach($_POST['event'] as $eventID=>$eventgroupID) {
			$update = "UPDATE eventgroup SET categoryID = ".GetSQLValueString($categoryID, "int")." WHERE ID = ".$eventgroupID;
			mysql_query($update, $aquiescedb) or die(mysql_error());
			$count ++;				
		}
		$msg = $count." categories(s)  were updated.";
	}
	if($_POST['checkboxaction']=="sendemails") {
		foreach($_POST['event'] as $eventID=>$eventgroupID) {
			sendEventEmails($eventID);
			$count ++;	
		
		}
		$msg = "Emails for ".$count." event(s)  were sent.";
		
	}
	
}


$currentPage = $_SERVER["PHP_SELF"];






$maxRows_rsEvents = 500;
$pageNum_rsEvents = 0;
if (isset($_GET['pageNum_rsEvents'])) {
  $pageNum_rsEvents = $_GET['pageNum_rsEvents'];
}
$startRow_rsEvents = $pageNum_rsEvents * $maxRows_rsEvents;

$varStartDate_rsEvents = "-1";
if (isset($_GET['startdate'])) {
  $varStartDate_rsEvents = $_GET['startdate'];
}
$varEndDate_rsEvents = "2020-01-01";
if (isset($_GET['enddate'])) {
  $varEndDate_rsEvents = $_GET['enddate'];
}
$varResourceID_rsEvents = "0";
if (isset($_GET['resourceID'])) {
  $varResourceID_rsEvents = $_GET['resourceID'];
}
$varEvent_rsEvents = "%";
if (isset($_GET['entry'])) {
  $varEvent_rsEvents = $_GET['entry'];
}
$varLongEvents_rsEvents = "0";
if (isset($_GET['longevents'])) {
  $varLongEvents_rsEvents = $_GET['longevents'];
}
$varCategoryID_rsEvents = "-1";
if (isset($_GET['categoryID'])) {
  $varCategoryID_rsEvents = $_GET['categoryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsEvents = sprintf("SELECT eventgroup.featured, eventgroup.eventtitle, event.startdatetime, event.enddatetime, event.recurringweekly, eventcategory.title AS category, eventcategory.colour, event.statusID, eventgroup.ID AS eventgroupID, event.ID, location.locationname, eventresource.resourcename FROM eventgroup LEFT JOIN event ON (event.eventgroupID = eventgroup.ID) LEFT JOIN eventcategory ON (eventgroup.categoryID = eventcategory.ID) LEFT JOIN location ON (event.eventlocationID = location.ID) LEFT JOIN eventresource ON (eventgroup.resourceID = eventresource.ID) WHERE DATE(event.startdatetime) >= %s AND DATE(event.startdatetime) <= %s AND eventgroup.eventtitle LIKE %s AND (eventgroup.categoryID = %s OR %s = -1) AND (%s = 0 OR eventgroup.resourceID = %s) AND (%s=0 OR DATEDIFF(event.enddatetime, event.startdatetime)>0) ORDER BY event.startdatetime ASC", GetSQLValueString($varStartDate_rsEvents, "date"),GetSQLValueString($varEndDate_rsEvents, "date"),GetSQLValueString($varEvent_rsEvents . "%", "text"),GetSQLValueString($varCategoryID_rsEvents, "int"),GetSQLValueString($varCategoryID_rsEvents, "int"),GetSQLValueString($varResourceID_rsEvents, "int"),GetSQLValueString($varResourceID_rsEvents, "int"),GetSQLValueString($varLongEvents_rsEvents, "int"));
$query_limit_rsEvents = sprintf("%s LIMIT %d, %d", $query_rsEvents, $startRow_rsEvents, $maxRows_rsEvents);
$rsEvents = mysql_query($query_limit_rsEvents, $aquiescedb) or die(mysql_error());
$row_rsEvents = mysql_fetch_assoc($rsEvents);

if (isset($_GET['totalRows_rsEvents'])) {
  $totalRows_rsEvents = $_GET['totalRows_rsEvents'];
} else {
  $all_rsEvents = mysql_query($query_rsEvents);
  $totalRows_rsEvents = mysql_num_rows($all_rsEvents);
}
$totalPages_rsEvents = ceil($totalRows_rsEvents/$maxRows_rsEvents)-1;




$queryString_rsEvents = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsEvents") == false && 
        stristr($param, "totalRows_rsEvents") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsEvents = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsEvents = sprintf("&totalRows_rsEvents=%d%s", $totalRows_rsEvents, $queryString_rsEvents);


mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsCategories = "SELECT ID, title FROM eventcategory WHERE active = 1 ORDER BY title ASC";
$rsCategories = mysql_query($query_rsCategories, $aquiescedb) or die(mysql_error());
$row_rsCategories = mysql_fetch_assoc($rsCategories);
$totalRows_rsCategories = mysql_num_rows($rsCategories);

$maxRows_rsEmptyEvents = 10;
$pageNum_rsEmptyEvents = 0;
if (isset($_GET['pageNum_rsEmptyEvents'])) {
  $pageNum_rsEmptyEvents = $_GET['pageNum_rsEmptyEvents'];
}
$startRow_rsEmptyEvents = $pageNum_rsEmptyEvents * $maxRows_rsEmptyEvents;

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsEmptyEvents = "SELECT eventgroup.ID, eventgroup.eventtitle FROM eventgroup LEFT JOIN event ON (eventgroup.ID = event.eventgroupID) WHERE event.ID IS NULL";
$query_limit_rsEmptyEvents = sprintf("%s LIMIT %d, %d", $query_rsEmptyEvents, $startRow_rsEmptyEvents, $maxRows_rsEmptyEvents);
$rsEmptyEvents = mysql_query($query_limit_rsEmptyEvents, $aquiescedb) or die(mysql_error());
$row_rsEmptyEvents = mysql_fetch_assoc($rsEmptyEvents);

if (isset($_GET['totalRows_rsEmptyEvents'])) {
  $totalRows_rsEmptyEvents = $_GET['totalRows_rsEmptyEvents'];
} else {
  $all_rsEmptyEvents = mysql_query($query_rsEmptyEvents);
  $totalRows_rsEmptyEvents = mysql_num_rows($all_rsEmptyEvents);
}
$totalPages_rsEmptyEvents = ceil($totalRows_rsEmptyEvents/$maxRows_rsEmptyEvents)-1;

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsResource = "SELECT * FROM eventresource ORDER BY resourcename ASC";
$rsResource = mysql_query($query_rsResource, $aquiescedb) or die(mysql_error());
$row_rsResource = mysql_fetch_assoc($rsResource);
$totalRows_rsResource = mysql_num_rows($rsResource);

$colname_rsEventPrefs = "-1";
if (isset($regionID)) {
  $colname_rsEventPrefs = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsEventPrefs = sprintf("SELECT * FROM eventprefs WHERE ID = %s", GetSQLValueString($colname_rsEventPrefs, "int"));
$rsEventPrefs = mysql_query($query_rsEventPrefs, $aquiescedb) or die(mysql_error());
$row_rsEventPrefs = mysql_fetch_assoc($rsEventPrefs);
$totalRows_rsEventPrefs = mysql_num_rows($rsEventPrefs);

?>
<!doctype html>

<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Manage Calendar"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<style><!--
<?php 

if($totalRows_rsEventCategories==0) {
	echo ".calendarCategory { display: none; }";
}

?>
--></style>
<script src="../../SpryAssets/SpryTabbedPanels.js"></script>
<link href="../css/calendarDefault.css" rel="stylesheet"  />
<link href="../../SpryAssets/SpryTabbedPanels.css" rel="stylesheet"  />
<script src="/core/scripts/checkbox/checkboxes.js"></script><?php require_once('../../core/scripts/checkbox/checkboxsession.inc.php'); ?>
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
      <?php require_once('../includes/calendar.inc.php'); 
  $eventsHTML = "";
class DynCalendar extends Calendar 
{
	
	
    function getCalendarLink($month, $year)
    {
        // Redisplay the current page, but with some parameters
        // to set the new month and year
        $s = "index.php";
		$s .= "?month=".$month."&year=".$year;
		$s .= isset($_GET['categoryID']) ? "&categoryID=".intval($_GET['categoryID']): "";
        return $s;
    }
	
	function getDateLink($day, $month, $year)
    {
		global $database_aquiescedb, $aquiescedb, $eventsHTML;
		$day = str_pad($day,2,"0",STR_PAD_LEFT);
		$month = str_pad($month,2,"0",STR_PAD_LEFT);
		$eventsDescription ="";
		$category = isset($_GET['categoryID']) ? $_GET['categoryID'] : 0;
		$daylink = "<div class=\"dateContents\"><div class=\"calendarDate\">".$day."</div></div>";//default 
		$date = $year."-".$month."-".$day;
		$select = "SELECT event.*, eventgroup.eventtitle AS eventgrouptitle FROM eventgroup LEFT JOIN event ON (event.eventgroupID = eventgroup.ID) WHERE ((DATE(startdatetime) >= ".GetSQLValueString($date,"date")." AND DATE(startdatetime) <= ".GetSQLValueString($date,"date").") OR (DATE(enddatetime) >= ".GetSQLValueString($date,"date")." AND DATE(enddatetime) <= ".GetSQLValueString($date,"date").") OR (DATE(startdatetime) < ".GetSQLValueString($date,"date")." AND DATE(enddatetime) > ".GetSQLValueString($date,"date").")) AND event.statusID = 1 AND eventgroup.statusID = 1 AND  (eventgroup.categoryID = ".GetSQLValueString($category,"int")." OR ".GetSQLValueString($category,"int")." < 1)";
		mysql_select_db($database_aquiescedb, $aquiescedb);
		$result = mysql_query($select, $aquiescedb) or die(mysql_error());
		$row = mysql_fetch_assoc($result);
		$totalRows = mysql_num_rows($result);
		if($totalRows>0) { // event(s) on this day
		do {
			$eventsHTML .= "<div class = \"event\"><strong>"
			.date('d M',strtotime($date))."</strong>&nbsp;<a href=\"/calendar/event.php?eventID=".$row['ID']."\">".$row['eventgrouptitle']."</a>
			</div>";
			$eventsDescription .= $row['eventgrouptitle']."\n"; // for pop-up
		} while ($row = mysql_fetch_assoc($result));
		$daylink = "<a href=\"../day.php?date=".$year."-".$month."-".$day."\" class=\"booked\" title=\"".$eventsDescription."\">".$day."</a>";
		} // end event(s) on this day


        return $daylink;
    } // end function
} // end class

// Construct a calendar to show the current month
$cal = new DynCalendar;
// If no month/year set, use current month/year
 
$d = isset($_GET['startdate']) ? getdate(strtotime($_GET['startdate'])) : getdate(time()); 

$month = (isset($_GET['month']) && $_GET['month']!="") ? $_GET['month'] : $d["mon"]; 
$year = (isset($_GET['year']) && $_GET['year'] !="") ? $_GET['year'] : $d["year"];

?> 
      <h1><i class="glyphicon glyphicon-calendar"></i> Calendar Manager</h1>
      
      <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
        <li><a href="add_calendar.php" ><i class="glyphicon glyphicon-plus-sign"></i> Add <?php echo ucwords($row_rsEventPrefs['eventname']); ?></a></li><li class="calendarCategory"><a href="eventcategories/index.php" ><i class="glyphicon glyphicon-tags"></i> Manage Categories</a></li><li class="calendarLocation"><a href="../../location/admin/index.php" ><i class="glyphicon glyphicon-flag"></i> Manage Locations</a></li>
        <li class="calendarLocation"><a href="resources/index.php"  ><i class="glyphicon glyphicon-th-large"></i> Manage Resources</a></li>
          <li class="calendarLocation"><a href="registration/index.php"><i class="glyphicon glyphicon-user"></i> Manage Sign Ups</a></li>
          <li ><a href="registration/non-attends.php"><i class="glyphicon glyphicon-ban-circle"></i> Non-Attendees</a></li>
        <li><a href="options/index.php"><i class="glyphicon glyphicon-cog"></i> Options</a></li>
      </ul></div></nav> 
      
      <form action="index.php" method="get" name="form1" id="form1" role="form"> <fieldset class="form-group form-inline">
        <legend>Filter</legend>
        Search for
       
          <label>
            <input name="entry" type="text"  id="entry" value="<?php echo isset($_GET['entry']) ? htmlentities($_GET['entry'], ENT_COMPAT, "UTF-8") : ""; ?>" size="20" maxlength="50" placeholder="Any <?php echo ucwords($row_rsEventPrefs['eventname']); ?>" class="form-control"/>
          </label>
  <span  class="calendarCategory"><label> in
  
    <select name="categoryID"  class="form-control">
      <option value="-1" <?php if (!isset($_GET['categoryID']) || $_GET['categoryID']==-1) {echo "selected=\"selected\"";} ?>>All categories</option>
       <option value="0" <?php if (isset($_GET['categoryID']) && $_GET['categoryID']==0) {echo "selected=\"selected\"";} ?>>Unavailable/Closed</option>
      <?php $rows = mysql_num_rows($rsCategories);
  if($rows > 0) {
do {  
?>
      <option value="<?php echo $row_rsCategories['ID']?>"<?php if (!(strcmp($row_rsCategories['ID'], @$_GET['categoryID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsCategories['title']?></option>
      <?php
} while ($row_rsCategories = mysql_fetch_assoc($rsCategories));
  
      mysql_data_seek($rsCategories, 0);
	  $row_rsCategories = mysql_fetch_assoc($rsCategories);
  }
?>
      </select>
    </label></span><?php if($totalRows_rsResource>0) { ?>
       <label for="resourceID" class="resource">
          with 
        
        <select name="resourceID" id="resourceID" class="form-control">
          <option value="0" <?php if (!(strcmp(0, @$_GET['resourceID']))) {echo "selected=\"selected\"";} ?>>Any resource</option>
          <?php
do {  
?>
          <option value="<?php echo $row_rsResource['ID']?>"<?php if (!(strcmp($row_rsResource['ID'], @$_GET['resourceID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsResource['resourcename']?></option>
          <?php
} while ($row_rsResource = mysql_fetch_assoc($rsResource));
  $rows = mysql_num_rows($rsResource);
  if($rows > 0) {
      mysql_data_seek($rsResource, 0);
	  $row_rsResource = mysql_fetch_assoc($rsResource);
  }
?>
        </select></label><?php } ?>
        <label>from
  <input name="startdate" type="hidden" id="startdate" value="<?php $setvalue =  isset($_GET['startdate']) ? htmlentities($_GET['startdate']) : date('Y-m-d'); echo $setvalue; $inputname = "startdate"; ?>" />    </label>
          <?php require('../../core/includes/datetimeinput.inc.php'); ?> 
          
          
          <label>until
          <input name="enddate" type="hidden" id="date" value="<?php $setvalue =  isset($_GET['date']) ? htmlentities($_GET['date']) : date('Y-m-d', strtotime("+ 1 YEAR")); echo $setvalue; $inputname = "date"; ?>" />    </label>
          <?php require('../../core/includes/datetimeinput.inc.php'); ?><label> &nbsp;&nbsp; <input type="checkbox" name="longevents" <?php if(isset($_GET['longevents'])) echo "checked"; ?> value="1">  Longer than a day</label>   
          <button name="searchbutton" type="submit" class="btn btn-default btn-secondary" id="searchbutton" >Go</button>
    
          
      </fieldset></form>
      <?php require_once('../../core/includes/alert.inc.php'); ?>
      <div id="TabbedPanels1" class="TabbedPanels">
        <ul class="TabbedPanelsTabGroup">
          <li class="TabbedPanelsTab" tabindex="0"><?php echo ucwords($row_rsEventPrefs['eventname']); ?></li>
          <li class="TabbedPanelsTab" tabindex="0">Calendar</li>
</ul>
        <div class="TabbedPanelsContentGroup">
          <div class="TabbedPanelsContent">
            <?php if ($totalRows_rsEvents == 0) { // Show if recordset empty ?>
            <p>There are no matches to your search criteria.</p>
          
            <?php } // Show if recordset empty ?>
            <?php if ($totalRows_rsEvents > 0) { // Show if recordset not empty ?>
            <form name="checkboxform" id="checkboxform" method="post">
            <p class="text-muted"><?php echo ucwords($row_rsEventPrefs['eventname']); ?>s <?php echo ($startRow_rsEvents + 1) ?> to <?php echo min($startRow_rsEvents + $maxRows_rsEvents, $totalRows_rsEvents) ?> of <?php echo $totalRows_rsEvents ?></p>
            <table  class="table table-hover">
            <thead>
              <tr>
                <th><input type="checkbox" name="checkAll" id="checkAll" onClick="checkUncheckAll(this);" /></th>  <th>&nbsp;</th>
                <th>&nbsp;</th>
                 <th class="rank10">Admin ID</th>
                <th colspan="2">Date</th>
                <th>Start</th>
                <th>&nbsp;</th>
                <th>End</th>
                <th>Event</th>
                <th class="calendarLocation">Location</th>
                <th class="calendarCategory">Category</th>
                 <th class="resource">Resource</th>
                <th colspan="3">Actions</th>
              </tr></thead><tbody>
              <?php do { ?>
              <tr bgcolor="<?php echo isset($row_rsEvents['colour']) ? $row_rsEvents['colour'] : ''; ?>">
                <td><input type="checkbox" name="event[<?php echo $row_rsEvents['ID']; ?>]" id="event<?php echo $row_rsEvents['ID']; ?>" value="<?php echo $row_rsEvents['eventgroupID']; ?>" /></td>
                <td class="status<?php echo $row_rsEvents['statusID']; ?>">&nbsp;</td>
                <td><?php if($row_rsEvents['recurringweekly'] ==0 ) { ?>
                  <img src="../../core/images/icons/date.png" alt="Single event" width="16" height="16" style="vertical-align:
middle;" />
                  <?php } else if($row_rsEvents['recurringweekly'] == 1) { ?>
                  <img src="../../core/images/icons/date_link.png" alt="Recurring event" width="16" height="16" style="vertical-align:
middle;" />
                  <?php } ?></td>
                   <td class="rank10"><?php echo $row_rsEvents['eventgroupID']; ?>/<?php echo $row_rsEvents['ID']; ?></td>
                <td class="text-nowrap"><?php echo  isset($row_rsEvents['startdatetime']) ? date('D',strtotime($row_rsEvents['startdatetime'])) : "None"; ?></td>
                <td class="text-nowrap"><?php  echo isset($row_rsEvents['startdatetime']) ? date('d M Y',strtotime($row_rsEvents['startdatetime'])) : ""; $startdate =  isset($row_rsEvents['startdatetime']) ? date('D d M Y',strtotime($row_rsEvents['startdatetime'])) : ""; ?></td>
                 <td class="text-nowrap"><?php echo isset($row_rsEvents['startdatetime']) ? date('H:i',strtotime($row_rsEvents['startdatetime'])) : "&nbsp;"; ?></td>
                 <td class="text-nowrap"><?php $enddate =  isset($row_rsEvents['enddatetime']) ? date('D d M Y',strtotime($row_rsEvents['enddatetime'])) : "None"; echo ($enddate != $startdate) ? $enddate : ""; ?></td>
                   <td class="text-nowrap"><?php echo isset($row_rsEvents['enddatetime']) ? date('H:i',strtotime($row_rsEvents['enddatetime'])) : "&nbsp;"; ?></td>
                <td><?php echo ($row_rsEvents['featured']==1) ? "<span class='glyphicon glyphicon-star' data-toggle='tooltip' title='This event is featured so will appear at the top of chronological listings'></span>" : ""; ?><a href="update_calendar.php?eventgroupID=<?php echo $row_rsEvents['eventgroupID']; ?>"><?php echo $row_rsEvents['eventtitle']; ?></a></td>
                <td class="calendarLocation"><?php echo isset($row_rsEvents['locationname']) ? $row_rsEvents['locationname'] : "(See details)"; ?></td>
                <td class="calendarCategory"><em><?php echo $row_rsEvents['category']; ?></em></td>
                <td class="resource"><em><?php echo $row_rsEvents['resourcename']; ?></em></td>
                <td><a href="update_calendar.php?eventgroupID=<?php echo $row_rsEvents['eventgroupID']; ?>" class="link_edit icon_only" title="Edit this event">Edit</a></td>
                <td><?php if(isset($row_rsEvents['ID'])) { ?>
                  <a href="registration/event.php?eventID=<?php echo $row_rsEvents['ID']; ?>" class="link_users calendarRegistration" title="Manage sign ups for this event">Sign ups</a>
                  <?php } ?></td>
                <td><a href="../event.php?eventID=<?php echo $row_rsEvents['ID']; ?>" title="View event page" target="_blank" class="link_view" rel="noopener">View</a></td>
              </tr>
              <?php } while ($row_rsEvents = mysql_fetch_assoc($rsEvents)); ?></tbody>
            </table>
            
             <?php do { ?>
               <?php echo createPagination($pageNum_rsEvents,$totalPages_rsEvents,"rsEvents");?>
               <?php } while ($row_rsEvents = mysql_fetch_assoc($rsEvents)); ?>
            
            <fieldset class="form-group form-inline ">
            <p>With selected: <input name="checkboxaction"  id="checkboxaction" type="hidden" value=""><a href="javascript:void(0)" onClick="if(confirm('Are you sure you want to send event emails for all the selected events (these will get sent to key event users as set in Options)\nNOTE: Recommended to send to maximum 10 recipients at a time')) { document.getElementById('checkboxaction').value='sendemails';document.getElementById('checkboxform').submit(); }">Send event emails</a> 
            <label class="calendarCategory"> | <a href="javascript:void(0);" onClick="if(confirm('Are you sure you want to update the category for the selected events? Note that event category will be updated for all linked events')) { document.getElementById('checkboxaction').value='updatecategory';document.getElementById('checkboxform').submit(); }">Update category to:</a> 
    <select name="categoryID"  class="form-control">
      <option value="" >Choose...</option>
       <option value="0" <?php if (isset($_GET['categoryID']) && $_GET['categoryID']==0) {echo "selected=\"selected\"";} ?>>No category</option>
      <?php
do {  
?>
      <option value="<?php echo $row_rsCategories['ID']?>"<?php if (!(strcmp($row_rsCategories['ID'], @$_GET['categoryID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsCategories['title']?></option>
      <?php
} while ($row_rsCategories = mysql_fetch_assoc($rsCategories));
  $rows = mysql_num_rows($rsCategories);
  if($rows > 0) {
      mysql_data_seek($rsCategories, 0);
	  $row_rsCategories = mysql_fetch_assoc($rsCategories);
  }
?>
      </select>
    </label></p></fieldset></form>
            <?php } // Show if recordset not empty ?>
           
           
           
          
           
           
            <?php if ($totalRows_rsEmptyEvents > 0) { // Show if recordset not empty ?>
            <h3>Events without times</h3>
            <table class="listTable">
              <?php do { ?>
              <tr>
                <td><?php echo $row_rsEmptyEvents['eventtitle']; ?></td>
                <td><a href="update_calendar.php?eventgroupID=<?php echo $row_rsEmptyEvents['ID']; ?>" class="link_edit icon_only">View</a></td>
                <td><a href="index.php?deleteeventgroupID=<?php echo $row_rsEmptyEvents['ID']; ?>" class="link_delete"><i class="glyphicon glyphicon-trash"></i> Delete</a></td>
              </tr>
              <?php } while ($row_rsEmptyEvents = mysql_fetch_assoc($rsEmptyEvents)); ?>
            </table>
            <?php } // Show if recordset not empty ?>
            
            <?php if($_SESSION['MM_UserGroup']==10) { ?>
            <p>ADMIN: <a href="index.php?deleteall=true" onClick="return confirm('Are you sure you want to delete all events?');">Delete all events</a></p>
            <?php } ?>
          </div>
          <div class="TabbedPanelsContent"><?php echo $cal->getMonthView($month, $year);
?> </div>
</div>
      </div>

   </div>
   
    <?php if (isset($_GET['defaultTab'])) { echo '<script type="text/javascript">
<!--
var TabbedPanels1 = new Spry.Widget.TabbedPanels("TabbedPanels1", {defaultTab:'.intval($_GET['defaultTab']).'});
//-->
    </script>'; } else { ?>
    <script>
<!--
var TabbedPanels1 = new Spry.Widget.TabbedPanels("TabbedPanels1");
//-->
    </script>
    <?php } ?>
    
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsEvents);

mysql_free_result($rsCategories);

mysql_free_result($rsEmptyEvents);

mysql_free_result($rsResource);

mysql_free_result($rsEventPrefs);
?>
