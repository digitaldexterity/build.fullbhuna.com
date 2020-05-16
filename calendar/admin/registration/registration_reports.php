<?php require_once('../../../Connections/aquiescedb.php'); ?>
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

$varEventID_rsThisEvent = "-1";
if (isset($_GET['eventID'])) {
  $varEventID_rsThisEvent = $_GET['eventID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsThisEvent = sprintf("SELECT event.ID, eventgroup.eventtitle FROM event LEFT JOIN eventgroup ON (event.eventgroupID = eventgroup.ID) WHERE event.ID = %s", GetSQLValueString($varEventID_rsThisEvent, "int"));
$rsThisEvent = mysql_query($query_rsThisEvent, $aquiescedb) or die(mysql_error());
$row_rsThisEvent = mysql_fetch_assoc($rsThisEvent);
$totalRows_rsThisEvent = mysql_num_rows($rsThisEvent);

$varEventID_rsTotalRegistrants = "-1";
if (isset($_GET['eventID'])) {
  $varEventID_rsTotalRegistrants = $_GET['eventID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsTotalRegistrants = sprintf("SELECT COUNT(eventregistration.ID) AS numberRegistrants FROM eventregistration WHERE eventregistration.eventID = %s ", GetSQLValueString($varEventID_rsTotalRegistrants, "int"));
$rsTotalRegistrants = mysql_query($query_rsTotalRegistrants, $aquiescedb) or die(mysql_error());
$row_rsTotalRegistrants = mysql_fetch_assoc($rsTotalRegistrants);
$totalRows_rsTotalRegistrants = mysql_num_rows($rsTotalRegistrants);

$varEventID_rsTshirtXL = "-1";
if (isset($_GET['eventID'])) {
  $varEventID_rsTshirtXL = $_GET['eventID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsTshirtXL = sprintf("SELECT COUNT(eventregistration.ID) AS numberRegistrants FROM eventregistration WHERE eventregistration.eventID = %s AND  eventregistration.statusID = 1 AND eventregistration.registrationtshirt = 5", GetSQLValueString($varEventID_rsTshirtXL, "int"));
$rsTshirtXL = mysql_query($query_rsTshirtXL, $aquiescedb) or die(mysql_error());
$row_rsTshirtXL = mysql_fetch_assoc($rsTshirtXL);
$totalRows_rsTshirtXL = mysql_num_rows($rsTshirtXL);

$varEventID_rsTshirtL = "-1";
if (isset($_GET['eventID'])) {
  $varEventID_rsTshirtL = $_GET['eventID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsTshirtL = sprintf("SELECT COUNT(eventregistration.ID) AS numberRegistrants FROM eventregistration WHERE eventregistration.eventID = %s AND  eventregistration.statusID = 1 AND eventregistration.registrationtshirt = 4", GetSQLValueString($varEventID_rsTshirtL, "int"));
$rsTshirtL = mysql_query($query_rsTshirtL, $aquiescedb) or die(mysql_error());
$row_rsTshirtL = mysql_fetch_assoc($rsTshirtL);
$totalRows_rsTshirtL = mysql_num_rows($rsTshirtL);

$varEventID_rsTshirtM = "-1";
if (isset($_GET['eventID'])) {
  $varEventID_rsTshirtM = $_GET['eventID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsTshirtM = sprintf("SELECT COUNT(eventregistration.ID) AS numberRegistrants FROM eventregistration WHERE eventregistration.eventID = %s AND  eventregistration.statusID = 1 AND eventregistration.registrationtshirt = 3", GetSQLValueString($varEventID_rsTshirtM, "int"));
$rsTshirtM = mysql_query($query_rsTshirtM, $aquiescedb) or die(mysql_error());
$row_rsTshirtM = mysql_fetch_assoc($rsTshirtM);
$totalRows_rsTshirtM = mysql_num_rows($rsTshirtM);

$varEventID_rsTshirtS = "-1";
if (isset($_GET['eventID'])) {
  $varEventID_rsTshirtS = $_GET['eventID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsTshirtS = sprintf("SELECT COUNT(eventregistration.ID) AS numberRegistrants FROM eventregistration WHERE eventregistration.eventID = %s AND  eventregistration.statusID = 1 AND eventregistration.registrationtshirt = 2", GetSQLValueString($varEventID_rsTshirtS, "int"));
$rsTshirtS = mysql_query($query_rsTshirtS, $aquiescedb) or die(mysql_error());
$row_rsTshirtS = mysql_fetch_assoc($rsTshirtS);
$totalRows_rsTshirtS = mysql_num_rows($rsTshirtS);

$varEventID_rsTshortXS = "-1";
if (isset($_GET['eventID'])) {
  $varEventID_rsTshortXS = $_GET['eventID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsTshortXS = sprintf("SELECT COUNT(eventregistration.ID) AS numberRegistrants FROM eventregistration WHERE eventregistration.eventID = %s AND  eventregistration.statusID = 1 AND eventregistration.registrationtshirt = 1", GetSQLValueString($varEventID_rsTshortXS, "int"));
$rsTshortXS = mysql_query($query_rsTshortXS, $aquiescedb) or die(mysql_error());
$row_rsTshortXS = mysql_fetch_assoc($rsTshortXS);
$totalRows_rsTshortXS = mysql_num_rows($rsTshortXS);

$varEventID_rsDiscovery = "-1";
if (isset($_GET['eventID'])) {
  $varEventID_rsDiscovery = $_GET['eventID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsDiscovery = sprintf("SELECT COUNT(eventregistration.registrationdiscovered) AS number, discovered.`description` FROM eventregistration LEFT JOIN discovered ON eventregistration.registrationdiscovered = discovered.ID WHERE eventregistration.eventID = %s  AND eventregistration.withregistrationID = eventregistration.ID GROUP BY discovered.ID ORDER BY number DESC", GetSQLValueString($varEventID_rsDiscovery, "int"));
$rsDiscovery = mysql_query($query_rsDiscovery, $aquiescedb) or die(mysql_error());
$row_rsDiscovery = mysql_fetch_assoc($rsDiscovery);
$totalRows_rsDiscovery = mysql_num_rows($rsDiscovery);

$varEventID_rsTotalMainRegistrants = "-1";
if (isset($_GET['eventID'])) {
  $varEventID_rsTotalMainRegistrants = $_GET['eventID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsTotalMainRegistrants = sprintf("SELECT COUNT(eventregistration.ID) AS numberRegistrants FROM eventregistration WHERE eventregistration.eventID = %s AND  eventregistration.statusID = 1 AND eventregistration.withregistrationID = eventregistration.ID", GetSQLValueString($varEventID_rsTotalMainRegistrants, "int"));
$rsTotalMainRegistrants = mysql_query($query_rsTotalMainRegistrants, $aquiescedb) or die(mysql_error());
$row_rsTotalMainRegistrants = mysql_fetch_assoc($rsTotalMainRegistrants);
$totalRows_rsTotalMainRegistrants = mysql_num_rows($rsTotalMainRegistrants);

$varEventID_rsAge15under = "-1";
if (isset($_GET['eventID'])) {
  $varEventID_rsAge15under = $_GET['eventID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsAge15under = sprintf("SELECT COUNT(users.ID) AS count FROM users INNER JOIN eventregistration ON (users.ID = eventregistration.ID) WHERE eventregistration.eventID = %s AND DATE_FORMAT(NOW(), '%%Y') - DATE_FORMAT(dob, '%%Y') - (DATE_FORMAT(NOW(), '00-%%m-%%d') < DATE_FORMAT(dob, '00-%%m-%%d')) >= 0 AND DATE_FORMAT(NOW(), '%%Y') - DATE_FORMAT(dob, '%%Y') - (DATE_FORMAT(NOW(), '00-%%m-%%d') < DATE_FORMAT(dob, '00-%%m-%%d')) <=15 ", GetSQLValueString($varEventID_rsAge15under, "int"));
$rsAge15under = mysql_query($query_rsAge15under, $aquiescedb) or die(mysql_error());
$row_rsAge15under = mysql_fetch_assoc($rsAge15under);
$totalRows_rsAge15under = mysql_num_rows($rsAge15under);

$varEventID_rsAge1625 = "-1";
if (isset($_GET['eventID'])) {
  $varEventID_rsAge1625 = $_GET['eventID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsAge1625 = sprintf("SELECT COUNT(users.ID) AS count FROM users INNER JOIN eventregistration ON (users.ID = eventregistration.ID) WHERE eventregistration.eventID = %s AND  DATE_FORMAT(NOW(), '%%Y') - DATE_FORMAT(dob, '%%Y') - (DATE_FORMAT(NOW(), '00-%%m-%%d') < DATE_FORMAT(dob, '00-%%m-%%d')) >=16 AND DATE_FORMAT(NOW(), '%%Y') - DATE_FORMAT(dob, '%%Y') - (DATE_FORMAT(NOW(), '00-%%m-%%d') < DATE_FORMAT(dob, '00-%%m-%%d')) <=25", GetSQLValueString($varEventID_rsAge1625, "int"));
$rsAge1625 = mysql_query($query_rsAge1625, $aquiescedb) or die(mysql_error());
$row_rsAge1625 = mysql_fetch_assoc($rsAge1625);
$totalRows_rsAge1625 = mysql_num_rows($rsAge1625);

$varEventID_rsAge2635 = "-1";
if (isset($_GET['eventID'])) {
  $varEventID_rsAge2635 = $_GET['eventID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsAge2635 = sprintf("SELECT COUNT(users.ID) AS count FROM users INNER JOIN eventregistration ON (users.ID = eventregistration.ID) WHERE eventregistration.eventID = %s AND DATE_FORMAT(NOW(), '%%Y') - DATE_FORMAT(dob, '%%Y') - (DATE_FORMAT(NOW(), '00-%%m-%%d') < DATE_FORMAT(dob, '00-%%m-%%d')) >=26 AND DATE_FORMAT(NOW(), '%%Y') - DATE_FORMAT(dob, '%%Y') - (DATE_FORMAT(NOW(), '00-%%m-%%d') < DATE_FORMAT(dob, '00-%%m-%%d')) <=35", GetSQLValueString($varEventID_rsAge2635, "int"));
$rsAge2635 = mysql_query($query_rsAge2635, $aquiescedb) or die(mysql_error());
$row_rsAge2635 = mysql_fetch_assoc($rsAge2635);
$totalRows_rsAge2635 = mysql_num_rows($rsAge2635);

$varEventID_rsAge3645 = "-1";
if (isset($_GET['eventID'])) {
  $varEventID_rsAge3645 = $_GET['eventID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsAge3645 = sprintf("SELECT COUNT(users.ID) AS count FROM users INNER JOIN eventregistration ON (users.ID = eventregistration.ID) WHERE eventregistration.eventID = %s AND DATE_FORMAT(NOW(), '%%Y') - DATE_FORMAT(dob, '%%Y') - (DATE_FORMAT(NOW(), '00-%%m-%%d') < DATE_FORMAT(dob, '00-%%m-%%d')) >=36 AND DATE_FORMAT(NOW(), '%%Y') - DATE_FORMAT(dob, '%%Y') - (DATE_FORMAT(NOW(), '00-%%m-%%d') < DATE_FORMAT(dob, '00-%%m-%%d')) <=45", GetSQLValueString($varEventID_rsAge3645, "int"));
$rsAge3645 = mysql_query($query_rsAge3645, $aquiescedb) or die(mysql_error());
$row_rsAge3645 = mysql_fetch_assoc($rsAge3645);
$totalRows_rsAge3645 = mysql_num_rows($rsAge3645);

$varEventID_rsAge4655 = "-1";
if (isset($_GET['eventID'])) {
  $varEventID_rsAge4655 = $_GET['eventID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsAge4655 = sprintf("SELECT COUNT(users.ID) AS count FROM users INNER JOIN eventregistration ON (users.ID = eventregistration.ID) WHERE eventregistration.eventID = %s AND DATE_FORMAT(NOW(), '%%Y') - DATE_FORMAT(dob, '%%Y') - (DATE_FORMAT(NOW(), '00-%%m-%%d') < DATE_FORMAT(dob, '00-%%m-%%d')) >=46 AND DATE_FORMAT(NOW(), '%%Y') - DATE_FORMAT(dob, '%%Y') - (DATE_FORMAT(NOW(), '00-%%m-%%d') < DATE_FORMAT(dob, '00-%%m-%%d')) <=55", GetSQLValueString($varEventID_rsAge4655, "int"));
$rsAge4655 = mysql_query($query_rsAge4655, $aquiescedb) or die(mysql_error());
$row_rsAge4655 = mysql_fetch_assoc($rsAge4655);
$totalRows_rsAge4655 = mysql_num_rows($rsAge4655);

$varEventID_rsAge5665 = "-1";
if (isset($_GET['eventID'])) {
  $varEventID_rsAge5665 = $_GET['eventID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsAge5665 = sprintf("SELECT COUNT(users.ID) AS count FROM users INNER JOIN eventregistration ON (users.ID = eventregistration.ID) WHERE eventregistration.eventID = %s AND DATE_FORMAT(NOW(), '%%Y') - DATE_FORMAT(dob, '%%Y') - (DATE_FORMAT(NOW(), '00-%%m-%%d') < DATE_FORMAT(dob, '00-%%m-%%d')) >=56 AND DATE_FORMAT(NOW(), '%%Y') - DATE_FORMAT(dob, '%%Y') - (DATE_FORMAT(NOW(), '00-%%m-%%d') < DATE_FORMAT(dob, '00-%%m-%%d')) <=65", GetSQLValueString($varEventID_rsAge5665, "int"));
$rsAge5665 = mysql_query($query_rsAge5665, $aquiescedb) or die(mysql_error());
$row_rsAge5665 = mysql_fetch_assoc($rsAge5665);
$totalRows_rsAge5665 = mysql_num_rows($rsAge5665);

$varEventID_rsAge66plus = "-1";
if (isset($_GET['eventID'])) {
  $varEventID_rsAge66plus = $_GET['eventID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsAge66plus = sprintf("SELECT COUNT(users.ID) AS count FROM users INNER JOIN eventregistration ON (users.ID = eventregistration.ID) WHERE eventregistration.eventID = %s AND DATE_FORMAT(NOW(), '%%Y') - DATE_FORMAT(dob, '%%Y') - (DATE_FORMAT(NOW(), '00-%%m-%%d') < DATE_FORMAT(dob, '00-%%m-%%d')) >=66 AND DATE_FORMAT(NOW(), '%%Y') - DATE_FORMAT(dob, '%%Y') - (DATE_FORMAT(NOW(), '00-%%m-%%d') < DATE_FORMAT(dob, '00-%%m-%%d')) <=150", GetSQLValueString($varEventID_rsAge66plus, "int"));
$rsAge66plus = mysql_query($query_rsAge66plus, $aquiescedb) or die(mysql_error());
$row_rsAge66plus = mysql_fetch_assoc($rsAge66plus);
$totalRows_rsAge66plus = mysql_num_rows($rsAge66plus);

$varEventID_rsTotalAcceptedRegistrants = "-1";
if (isset($_GET['eventID'])) {
  $varEventID_rsTotalAcceptedRegistrants = $_GET['eventID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsTotalAcceptedRegistrants = sprintf("SELECT COUNT(eventregistration.ID) AS numberRegistrants FROM eventregistration WHERE eventregistration.eventID = %s AND  eventregistration.statusID = 1", GetSQLValueString($varEventID_rsTotalAcceptedRegistrants, "int"));
$rsTotalAcceptedRegistrants = mysql_query($query_rsTotalAcceptedRegistrants, $aquiescedb) or die(mysql_error());
$row_rsTotalAcceptedRegistrants = mysql_fetch_assoc($rsTotalAcceptedRegistrants);
$totalRows_rsTotalAcceptedRegistrants = mysql_num_rows($rsTotalAcceptedRegistrants);

$varEventID_rsAgeSpecified = "31";
if (isset($_GET['eventID'])) {
  $varEventID_rsAgeSpecified = $_GET['eventID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsAgeSpecified = sprintf("SELECT COUNT(users.ID) AS count FROM users INNER JOIN eventregistration ON (users.ID = eventregistration.ID) WHERE eventregistration.eventID = %s AND DATE_FORMAT(NOW(), '%%Y') - DATE_FORMAT(dob, '%%Y') - (DATE_FORMAT(NOW(), '00-%%m-%%d') < DATE_FORMAT(dob, '00-%%m-%%d')) >0 AND DATE_FORMAT(NOW(), '%%Y') - DATE_FORMAT(dob, '%%Y') - (DATE_FORMAT(NOW(), '00-%%m-%%d') < DATE_FORMAT(dob, '00-%%m-%%d')) <=150", GetSQLValueString($varEventID_rsAgeSpecified, "int"));
$rsAgeSpecified = mysql_query($query_rsAgeSpecified, $aquiescedb) or die(mysql_error());
$row_rsAgeSpecified = mysql_fetch_assoc($rsAgeSpecified);
$totalRows_rsAgeSpecified = mysql_num_rows($rsAgeSpecified);

$varEventID_rsPostcodes = "31";
if (isset($_GET['eventID'])) {
  $varEventID_rsPostcodes = $_GET['eventID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPostcodes = sprintf("SELECT eventregistration.ID, location.postcode FROM eventregistration LEFT JOIN users ON (eventregistration.userID = users.ID) LEFT JOIN location ON (users.defaultaddressID = location.ID) WHERE eventregistration.statusID = 1 AND eventregistration.eventID = %s AND postcode IS NOT NULL ORDER BY postcode", GetSQLValueString($varEventID_rsPostcodes, "int"));
$rsPostcodes = mysql_query($query_rsPostcodes, $aquiescedb) or die(mysql_error());
$row_rsPostcodes = mysql_fetch_assoc($rsPostcodes);
$totalRows_rsPostcodes = mysql_num_rows($rsPostcodes);
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Registration Reports"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
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
    <div class="page calendar"><h1><i class="glyphicon glyphicon-calendar"></i> Registration Reports</h1>
    <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
      <li><a href="index.php?eventID=<?php echo intval($_GET['eventID']); ?>" class="link_undo"><i class="glyphicon glyphicon-arrow-left"></i> Back</a></li>
    </ul></div></nav>
    <h2><?php echo $row_rsThisEvent['eventtitle']; ?></h2>
    <table border="0" cellpadding="2" cellspacing="2" class="form-table">
      <tr>
        <td align="right"><strong>Total registrants:</strong></td>
        <td><?php echo $row_rsTotalRegistrants['numberRegistrants']; ?></td>
      </tr>
      <tr>
        <td align="right"><strong>Total accepted registrants:</strong></td>
        <td><?php echo $row_rsTotalAcceptedRegistrants['numberRegistrants']; ?></td>
      </tr>
      <tr>
        <td align="right"><strong>Total main registrants:</strong></td>
        <td><?php echo $row_rsTotalMainRegistrants['numberRegistrants']; ?></td>
      </tr>
    </table>
  <h2>T-shirts</h2>
      <table border="0" cellpadding="2" cellspacing="2" class="form-table">
      <tr>
        <td align="right"><strong>T-shirts XL:</strong></td>
        <td><?php echo $row_rsTshirtXL['numberRegistrants']; ?></td>
      </tr>
      <tr>
        <td align="right"><strong>T-shirts L:</strong></td>
        <td><?php echo $row_rsTshirtL['numberRegistrants']; ?></td>
      </tr>
      <tr>
        <td align="right"><strong>T-shirts M:</strong></td>
        <td><?php echo $row_rsTshirtM['numberRegistrants']; ?></td>
      </tr>
      <tr>
        <td align="right"><strong>T-shirts S:</strong></td>
        <td><?php echo $row_rsTshirtS['numberRegistrants']; ?></td>
      </tr>
      <tr>
        <td align="right"><strong>T-shirts XS:</strong></td>
        <td><?php echo $row_rsTshortXS['numberRegistrants']; ?></td>
      </tr>
      <tr>
        <td align="right"><strong>T-shirts Unknown:</strong></td>
        <td><?php echo ($row_rsTotalRegistrants['numberRegistrants']-$row_rsTshirtXL['numberRegistrants']-$row_rsTshirtL['numberRegistrants']-$row_rsTshirtM['numberRegistrants']-$row_rsTshirtS['numberRegistrants']-$row_rsTshortXS['numberRegistrants']); ?></td>
      </tr>
      
    </table>
    <h2>Age breakdown</h2>
    <p>Ages (where specified)</p>
    <table border="0" cellpadding="2" cellspacing="0" class="form-table">
      <tr>
        <td align="right"><strong>0-15:</strong></td>
        <td><?php echo $row_rsAge15under['count']; ?>&nbsp;</td>
        <td>(<?php echo isset($row_rsAge15under['count']) ? number_format(($row_rsAge15under['count']/$row_rsAgeSpecified['count']*100),0,"","") : "0"; ?>%)&nbsp;</td>
      </tr>
      <tr>
        <td align="right"><strong>16-25:</strong></td>
        <td><?php echo $row_rsAge1625['count']; ?>&nbsp;</td>
        <td>(<?php echo isset($row_rsAge1625['count']) ? number_format(($row_rsAge1625['count']/$row_rsAgeSpecified['count']*100),0,"","") : "0"; ?>%)&nbsp;</td>
      </tr>
      <tr>
        <td align="right"><strong>26-35:</strong></td>
        <td><?php echo $row_rsAge2635['count']; ?>&nbsp;</td>
        <td>(<?php echo isset($row_rsAge2635['count']) ? number_format(($row_rsAge2635['count']/$row_rsAgeSpecified['count']*100),0,"","") : "0"; ?>%)&nbsp;</td>
      </tr>
      <tr>
        <td align="right"><strong>36-45:</strong></td>
        <td><?php echo $row_rsAge3645['count']; ?>&nbsp;</td>
        <td>(<?php echo isset($row_rsAge3645['count']) ? number_format(($row_rsAge3645['count']/$row_rsAgeSpecified['count']*100),0,"","") : "0"; ?>%)&nbsp;</td>
      </tr>
      <tr>
        <td align="right"><strong>46-55:</strong></td>
        <td><?php echo $row_rsAge4655['count']; ?>&nbsp;</td>
        <td>(<?php echo isset($row_rsAge4655['count']) ? number_format(($row_rsAge4655['count']/$row_rsAgeSpecified['count']*100),0,"","") : "0"; ?>%)&nbsp;</td>
      </tr>
      <tr>
        <td align="right"><strong>56-65:</strong></td>
        <td><?php echo $row_rsAge5665['count']; ?>&nbsp;</td>
        <td>(<?php echo isset($row_rsAge5665['count']) ? number_format(($row_rsAge5665['count']/$row_rsAgeSpecified['count']*100),0,"","") : "0"; ?>%)&nbsp;</td>
      </tr>
      <tr>
        <td align="right"><strong>66+:</strong></td>
        <td><?php echo $row_rsAge66plus['count']; ?>&nbsp;</td>
        <td>(<?php echo isset($row_rsAge66plus['count']) ? number_format(($row_rsAge66plus['count']/$row_rsAgeSpecified['count']*100),0,"","") : "0"; ?>%)&nbsp;</td>
      </tr>
    </table>
    <h2>Postcode Breakdown</h2>
    
    
    
      <?php $postcodearea = array(); 
	  do {  
	  $postcode =  getPostCodeDistrict($row_rsPostcodes['postcode']);
	  if(!isset($postcodearea[$postcode])) {
		  $postcodearea[$postcode] = 1;
	  } else {
		  $postcodearea[$postcode] ++;
	  }
	   } while ($row_rsPostcodes = mysql_fetch_assoc($rsPostcodes)); ?>
	   <table class="form-table">
	   <?php foreach($postcodearea as $key => $value) { if($key!="") { ?>
       <tr><td><?php echo $key; ?></td><td><?php echo $value; ?></td></tr>
	   <?php }} ?>
       </table>
   
<h2>Discovered event</h2>
    <table  class="listTable">
      
      <?php do { ?>
        <tr>
          
          <td><?php echo isset($row_rsDiscovery['description']) ? $row_rsDiscovery['description'] : "Not specified"; ?></td><td><?php echo isset($row_rsDiscovery['number']) ? number_format(($row_rsDiscovery['number']/$row_rsTotalMainRegistrants['numberRegistrants']*100),0,"","") : "0"; ?>%</td>
        </tr>
        <?php } while ($row_rsDiscovery = mysql_fetch_assoc($rsDiscovery)); ?>
    </table>
    <?php 
	function getPostCodeDistrict($postcode) {
	$postcode = preg_replace("/[^0-9A-Z]/","",strtoupper($postcode));
	// last part of (if any) Postcode always 3 digits so remove them
	$length = strlen($postcode);
	$postcode = $length > 4 ? substr($postcode,0,strlen($postcode)-3) : $postcode;
	return $postcode;	
}

function getPostCodeArea($postcode) {
	$postcode = getPostCodeDistrict($postcode);
	$postcode = preg_replace("/[0-9]/","",$postcode);
	return $postcode;
}
?></div>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsThisEvent);

mysql_free_result($rsTotalRegistrants);

mysql_free_result($rsTshirtXL);

mysql_free_result($rsTshirtL);

mysql_free_result($rsTshirtM);

mysql_free_result($rsTshirtS);

mysql_free_result($rsTshortXS);

mysql_free_result($rsDiscovery);

mysql_free_result($rsTotalMainRegistrants);

mysql_free_result($rsAge15under);

mysql_free_result($rsAge1625);

mysql_free_result($rsAge2635);

mysql_free_result($rsAge3645);

mysql_free_result($rsAge4655);

mysql_free_result($rsAge5665);

mysql_free_result($rsAge66plus);

mysql_free_result($rsTotalAcceptedRegistrants);

mysql_free_result($rsAgeSpecified);

mysql_free_result($rsPostcodes);
?>
