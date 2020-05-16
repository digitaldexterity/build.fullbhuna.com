<?php require_once('../../../Connections/aquiescedb.php'); ?>
<?php require_once('../../../core/includes/adminAccess.inc.php'); ?><?php require_once('../../../core/includes/framework.inc.php'); ?>
<?php

if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "7,8,9,10";
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


$defaultstartdate = (isset($_GET['startdate']) && $_GET['startdate'] !="") ?  $_GET['startdate'] : date('Y-m-d') ;
$defaultenddate = (isset($_GET['enddate']) && $_GET['enddate']!="") ?  $_GET['enddate'] : date('Y-m-d', strtotime("+1 YEAR"));


$currentPage = $_SERVER["PHP_SELF"];


/* CLEAN UP!!!
inactive older than a year old
OR
single send over a year old
*/
$delete = "DELETE FROM reminder WHERE 
(statusID = 0 AND createddatetime < DATE_SUB(NOW() , INTERVAL 1 YEAR )) 
OR 
(reminderrepeat = 0 AND firstsend < DATE_SUB(NOW() , INTERVAL 1 YEAR ))";
mysql_select_db($database_aquiescedb, $aquiescedb);
mysql_query($delete, $aquiescedb) or die(mysql_error());

if(isset($_POST['checkboxaction']) && isset($_POST['reminder'])) {
	foreach($_POST['reminder'] as $key=> $value) {
		mysql_select_db($database_aquiescedb, $aquiescedb);
	$update = "UPDATE reminder SET statusID = 0 WHERE ID = ".intval($key);
	//echo $update."<br>";
	mysql_query($update, $aquiescedb) or die(mysql_error());
		
	}
	//die();
}


if(isset($_GET['cancelreminderID'])) {
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$update = "UPDATE reminder SET statusID = 0 WHERE ID = ".intval($_GET['cancelreminderID']);
	mysql_query($update, $aquiescedb) or die(mysql_error());	
}

if(isset($_GET['canceluserID'])) {
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$update = "UPDATE reminder SET statusID = 0 WHERE recipientID = ".intval($_GET['canceluserID']);
	mysql_query($update, $aquiescedb) or die(mysql_error());	
}

$maxRows_rsReminders = 50;
$pageNum_rsReminders = 0;
if (isset($_GET['pageNum_rsReminders'])) {
  $pageNum_rsReminders = $_GET['pageNum_rsReminders'];
}
$startRow_rsReminders = $pageNum_rsReminders * $maxRows_rsReminders;

$varSearch_rsReminders = "%";
if (isset($_GET['search'])) {
  $varSearch_rsReminders = $_GET['search'];
}
$varRegionID_rsReminders = "1";
if (isset($regionID)) {
  $varRegionID_rsReminders = $regionID;
}
$varStartDate_rsReminders = "1970-01-01";
if (isset($defaultstartdate)) {
  $varStartDate_rsReminders = $defaultstartdate;
}
$varEndDate_rsReminders = "2999-01-01";
if (isset($defaultenddate)) {
  $varEndDate_rsReminders = $defaultenddate;
}
$varShowAll_rsReminders = "-1";
if (isset($_GET['showall'])) {
  $varShowAll_rsReminders = $_GET['showall'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsReminders = sprintf("SELECT reminder.*, CONCAT(users.firstname, ' ',users.surname) AS fullname, users.emailoptin FROM reminder LEFT JOIN users ON(reminder.recipientID = users.ID) WHERE reminder.regionID = %s AND users.surname LIKE %s AND (%s = 1 OR (reminder.statusID = 1 AND (reminder.lastsent IS NULL OR reminder.reminderrepeat = 1))) AND ((reminder.firstsend >= %s AND reminder.firstsend <= %s) OR (reminder.lastsent >= %s AND reminder.lastsent <= %s)) ORDER BY reminder.firstsend", GetSQLValueString($varRegionID_rsReminders, "int"),GetSQLValueString($varSearch_rsReminders . "%", "text"),GetSQLValueString($varShowAll_rsReminders, "int"),GetSQLValueString($varStartDate_rsReminders, "date"),GetSQLValueString($varEndDate_rsReminders, "date"),GetSQLValueString($varStartDate_rsReminders, "date"),GetSQLValueString($varEndDate_rsReminders, "date"));
$query_limit_rsReminders = sprintf("%s LIMIT %d, %d", $query_rsReminders, $startRow_rsReminders, $maxRows_rsReminders);
$rsReminders = mysql_query($query_limit_rsReminders, $aquiescedb) or die(mysql_error());
$row_rsReminders = mysql_fetch_assoc($rsReminders);

if (isset($_GET['totalRows_rsReminders'])) {
  $totalRows_rsReminders = $_GET['totalRows_rsReminders'];
} else {
  $all_rsReminders = mysql_query($query_rsReminders);
  $totalRows_rsReminders = mysql_num_rows($all_rsReminders);
}
$totalPages_rsReminders = ceil($totalRows_rsReminders/$maxRows_rsReminders)-1;

$queryString_rsReminders = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsReminders") == false && 
        stristr($param, "totalRows_rsReminders") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsReminders = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsReminders = sprintf("&totalRows_rsReminders=%d%s", $totalRows_rsReminders, $queryString_rsReminders);


?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Manage Scheduled Messages"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script src="/core/scripts/checkbox/checkboxes.js"></script><?php require_once('../../../core/scripts/checkbox/checkboxsession.inc.php'); ?>
<style><!--

span.link_delete {
	position:relative;
	cursor:pointer;
}
span.link_delete .popup {
	display:none;
	position:absolute;
	top:0;
	right:0;
	background:#FFFFFF;
	border:1px solid rgb(207,207,207);
	padding:10px;
	z-index:10;
	text-indent:0;
	white-space:nowrap;

}

span.link_delete:hover .popup {
	display:block;
}
--></style>
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
    <div class="page reminder"><?php require_once('../../../core/region/includes/chooseregion.inc.php'); ?>
<h1><i class="glyphicon glyphicon-envelope"></i> Scheduled Message Queue</h1>
    <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
      <li class="nav-item"><a href="add_reminder.php" class="nav-link"><i class="glyphicon glyphicon-plus-sign"></i> Add Scheduled Message</a></li>
    </ul></div></nav>
    <form action="index.php" method="get" >
     <fieldset class="form-inline"><legend>Search</legend>
  
        
          <input name="search" type="text"  id="search" value="<?php echo isset($_GET['search']) ? htmlentities(trim($_GET['search'])) : ""; ?>" size="20" maxlength="20" placeholder="Search by surname" class="form-control" />
       

          <input type="hidden" name="startdate" id="startdate" value="<?php $inputname = "startdate";  $setvalue = (isset($_GET['startdate']) && $_GET['startdate'] !="") ? htmlentities($_GET['startdate']) : $defaultstartdate; echo $setvalue; ?>"><?php require('../../../core/includes/datetimeinput.inc.php'); ?>
          <input type="hidden" name="enddate" id="enddate" value="<?php $inputname = "enddate";   $setvalue = (isset($_GET['enddate']) && $_GET['enddate']!="") ? htmlentities($_GET['enddate']) : $defaultenddate; echo $setvalue; ?>"><?php require('../../../core/includes/datetimeinput.inc.php'); ?>
          <label>
            <input <?php if (!(strcmp(@$_GET['showall'],1))) {echo "checked=\"checked\"";} ?> name="showall" type="checkbox" id="showall" value="1"  onClick="this.form.submit();"/> show inactive </label>
   
      <button type="submit" name="searchbutton" id="searchbutton" class="btn btn-default btn-secondary" >Go</button> Server time now: <?php echo date('H:i'); ?>
    </fieldset>
    </form>
 <?php if ($totalRows_rsReminders == 0) { // Show if recordset empty ?> <p>There are no reminders that meet your search criteria</p>
  <?php } // Show if recordset empty ?>
   <?php if ($totalRows_rsReminders > 0) { // Show if recordset not empty ?>
   <form method="post" name="checkboxform"><p class="text-muted">Reminders <?php echo ($startRow_rsReminders + 1) ?> to <?php echo min($startRow_rsReminders + $maxRows_rsReminders, $totalRows_rsReminders) ?> of <?php echo $totalRows_rsReminders ?> </p>
   
      <table  class="table table-hover">
      <thead>
        <tr> <th><input type="checkbox" name="checkAll" id="checkAll" onClick="checkUncheckAll(this);" /></th>
          <th>&nbsp;</th>
          <th>Type</th>
          <th>Recipient</th> <th>Opt out</th> <th>Override</th>
          <th>Subject</th>
          <th>First send</th>
          <th>Last sent</th>
          <th>Repeats</th>
          <th colspan="2">Actions</th>
        </tr></thead><tbody>
        <?php do { ?>
          <tr>
          <td><input type="checkbox" name="reminder[<?php echo $row_rsReminders['ID']; ?>]" id="user<?php echo $row_rsReminders['ID']; ?>" value="<?php echo $row_rsReminders['ID']; ?>" /></td>
            <td class="status<?php echo $row_rsReminders['statusID']; ?>">&nbsp;</td><td><?php if($row_rsReminders['viaemail']==1) { ?>
              <img src="../../../core/images/icons/mail-forward.png" width="16" height="16" alt="By Email" style="vertical-align:middle;">
              <?php } if ($row_rsReminders['viasms']==1) { ?>
              <img src="../../../core/images/icons/telephone.png" width="16" height="16" alt="By Text"  style="vertical-align:middle;">              <?php } ?></td>
            <td><a href="../../../members/admin/modify_user.php?userID=<?php echo $row_rsReminders['recipientID']; ?>"><?php echo $row_rsReminders['fullname']; ?> </a></td><td><?php if($row_rsReminders['emailoptin']==0) { ?><i class="glyphicon glyphicon-remove"></i><?php } ?></td><td><?php if($row_rsReminders['ignoreoptout']==1) { ?><i class="glyphicon glyphicon-ok"></i><?php } ?></td>
            <td><?php echo $row_rsReminders['subject']; ?></td>
            <td><?php echo date('d M Y H:i',strtotime($row_rsReminders['firstsend'])); ?></td>
            <td><?php echo isset($row_rsReminders['lastsent']) ? date('d M Y H:i',strtotime($row_rsReminders['lastsent'])) : ""; ?></td>
            <td><?php if($row_rsReminders['reminderrepeat']==0) { 
			echo "No";
			} else { // repeats
				if($row_rsReminders['months']>0) { // months
		   $multiple = $row_rsReminders['months'];
		  $increment = "month(s)";
		  } else  if(($row_rsReminders['seconds']/604800) >= 1) { // weeks
		  $multiple = floor($row_rsReminders['seconds']/604800);
		  $increment = "week(s)";
		  } else  if(($row_rsReminders['seconds']/86400) >= 1) { // days
		  $multiple = floor($row_rsReminders['seconds']/86400);
		  $increment = "day(s)";
		  } else  if(($row_rsReminders['seconds']/3600) >= 1) { // hours
		  $multiple = floor($row_rsReminders['seconds']/3600);
		  $increment = "hour(s)";
		  } else  if(($row_rsReminders['seconds']/60) >= 1) { // minutes
		  $multiple = floor($row_rsReminders['seconds']/60) ;
		  $increment = "minute(s)";
		  } else {
		  $multiple = $row_rsReminders['seconds'];
		  $increment = "second(s)";
		  }
			 echo "Every ".$multiple." ".$increment; 
			} // repeats
			?>
            </td>
            <td><a href="update_reminder.php?reminderID=<?php echo $row_rsReminders['ID']; ?>" class="link_edit icon_only">Edit</a></td>
             <td><span class="link_delete">Delete<div class="popup"><a href="index.php?cancelreminderID=<?php echo $row_rsReminders['ID']; ?>" onClick="return confirm('Are you sure you want to cancel this reminder?');">Cancel this</a> | <a href="index.php?canceluserID=<?php echo $row_rsReminders['recipientID']; ?>" onClick="return confirm('Are you sure you want to cancel this and ALL OTHER reminders for <?php echo $row_rsReminders['fullname']; ?>?');">Cancel all for <?php echo $row_rsReminders['fullname']; ?></a></div></span></td>
          </tr>
          <?php } while ($row_rsReminders = mysql_fetch_assoc($rsReminders)); ?></tbody>
      </table>
      <p>With selected: <a href="javascript:void(0);" onClick="if(confirm('Are you sure you want to remove the selected messages?')) { document.checkboxform.checkboxaction.value='remove'; document.checkboxform.submit();  }">Remove</a></p>
      <input name="checkboxaction" type="hidden" value="">
      </form>
      <?php } // Show if recordset not empty ?>
    <?php echo createPagination($pageNum_rsReminders,$totalPages_rsReminders,"rsReminders");?>
</div>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsReminders);
?>
