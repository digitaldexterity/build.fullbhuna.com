<?php require_once('../../../../Connections/aquiescedb.php'); ?>
<?php require_once('../../../includes/adminAccess.inc.php'); ?>
<?php require_once('../../../includes/framework.inc.php'); ?>
<?php require_once('../../includes/visitorfunctions.inc.php'); ?>
			<?php

use GeoIp2\Database\Reader; // needs to go at root doc so can go before if / include below...
if(is_readable('../../../../3rdparty/geoip/geoip2.phar')) {
	require('../../../../3rdparty/geoip/geoip2.phar');	
	$gi2 = new Reader(SITE_ROOT.'3rdparty/geoip/GeoLite2-City.mmdb' );
} else if(is_readable('../../../../3rdparty/geoip/geoip.inc')) {
	require_once('../../../../3rdparty/geoip/geoip.inc');
	include("../../../../3rdparty/geoip/geoipcity.inc");
	include("../../../../3rdparty/geoip/geoipregionvars.php");
	$gi = geoip_open(SITE_ROOT."3rdparty/geoip/GeoLiteCity.dat",GEOIP_STANDARD);
}
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

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

$maxRows_rsPageVisitors = 100;
$pageNum_rsPageVisitors = 0;
if (isset($_GET['pageNum_rsPageVisitors'])) {
  $pageNum_rsPageVisitors = $_GET['pageNum_rsPageVisitors'];
}
$startRow_rsPageVisitors = $pageNum_rsPageVisitors * $maxRows_rsPageVisitors;

$colname_rsPageVisitors = "-1";
if (isset($_GET['page'])) {
  $colname_rsPageVisitors = $_GET['page'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPageVisitors = sprintf("SELECT track_page.*, track_session.*, COUNT(sessionpage.ID) AS pages FROM track_page LEFT JOIN track_session ON track_page.sessionID = track_session.ID LEFT JOIN track_page AS sessionpage ON (sessionpage.sessionID = track_session.ID) WHERE track_page.page LIKE %s GROUP BY  track_session.ID ORDER BY track_page.`datetime` DESC", GetSQLValueString($colname_rsPageVisitors . "%", "text"));
$query_limit_rsPageVisitors = sprintf("%s LIMIT %d, %d", $query_rsPageVisitors, $startRow_rsPageVisitors, $maxRows_rsPageVisitors);
$rsPageVisitors = mysql_query($query_limit_rsPageVisitors, $aquiescedb) or die(mysql_error());
$row_rsPageVisitors = mysql_fetch_assoc($rsPageVisitors);

if (isset($_GET['totalRows_rsPageVisitors'])) {
  $totalRows_rsPageVisitors = $_GET['totalRows_rsPageVisitors'];
} else {
  $all_rsPageVisitors = mysql_query($query_rsPageVisitors);
  $totalRows_rsPageVisitors = mysql_num_rows($all_rsPageVisitors);
}
$totalPages_rsPageVisitors = ceil($totalRows_rsPageVisitors/$maxRows_rsPageVisitors)-1;
?>
			<!doctype html>
			<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
            <title>
            <?php $pageTitle = "Page visits: ".htmlentities($row_rsPageVisitors['pageTitle'], ENT_COMPAT, "UTF-8"); echo $site_name." ".$admin_name." - ".$pageTitle; ?>
            </title>
            <!-- InstanceEndEditable -->
<?php require_once('../../includes/seo.inc.php'); ?>
<?php require_once('../../../includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
            <style>
<!--
-->
</style>
            <!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../../includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" -->
                <div class="page seo">
                  <h1><i class="glyphicon glyphicon-globe"></i> Who’s Visited This Page?</h1>
                  <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light">
                    <div class="container-fluid">
                      <ul class="nav navbar-nav">
                        <li class="nav-item"><a href="index.php" onClick="window.history.back(); return false;" class="nav-link"><i class="glyphicon glyphicon-arrow-left"></i> Back</a></li>
                        <li class="nav-item"><a href="index.php"  class="nav-link"><i class="glyphicon glyphicon-user"></i> Manage Visitors</a></li>
                        
                         <li class="nav-item"><a href="<?php echo htmlentities($_GET['page'], ENT_COMPAT, "UTF-8"); ?>"  class="nav-link" target="_blank" rel="noopener"><i class="glyphicon glyphicon-new-window"></i> Open Page</a></li>
                      </ul>
                    </div>
                  </nav>
                  <h2><?php echo htmlentities($row_rsPageVisitors['pageTitle'], ENT_COMPAT, "UTF-8"); ?></h2>
                  <p>Visitors <?php echo ($startRow_rsPageVisitors + 1) ?> to <?php echo min($startRow_rsPageVisitors + $maxRows_rsPageVisitors, $totalRows_rsPageVisitors) ?> of <?php echo $totalRows_rsPageVisitors;  $tracker_period =  (defined('TRACKER_PERIOD')) ? TRACKER_PERIOD : "4 WEEKS"; echo " since ". date('d M Y', strtotime("NOW - ".$tracker_period)); ?> </p>
                  <table class="table table-hover">
                    <thead>
                      <tr>
                        <th>Date/time</th>
                        <th>Vistor IP</th>
                        <th>Country</th>
                        <th>Referer</th>
                        <th>Keywords</th>
                        <th>User</th>
                        <th colspan="2">Session</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php do { ?>
                        <tr>
                        <td><?php echo date('d M Y H:i', strtotime( $row_rsPageVisitors['datetime'])); ?></td>
                        <td><?php echo $row_rsPageVisitors['remote_address']; 
			$location = "";
	   if(isset($gi2)) {			
			try {
        	$record = $gi2->city($row_rsPageVisitors['remote_address']);
    } catch (GeoIp2\Exception\AddressNotFoundException $e) {
        // Your handling of the not-found case here.
		$record = false;
    }
			if($record) {
				$location .= $record->country->name;
				
				
				$location .= isset($record->city->name) ? " - ".$record->city->name : " - unknown locale"; 
			}		  
		}  else if(isset($gi)) {
			$record = geoip_record_by_addr($gi,$row_rsPageVisitors['remote_address']);
			if($record) {
				$location = $record->country_name;
				
				
				$location .= isset($record->city) ? " - ".$record->city : " - unknown locale"; 
			}		  
		}
		?></td>
                        <td><?php echo utf8_encode($location); ?></td>
                        <td class="text-nowrap"><?php echo getHostReferer($row_rsVisits['referer']);  ?></td>
                        <td><?php $referer =  getSearchTerms($row_rsPageVisitors['referer']); echo "<span title=\"".$referer."\">".$referer."</span>"; ?></td>
                        <td><?php echo $row_rsPageVisitors['username']; ?></td>
                        <td class="text-right">(<?php echo $row_rsPageVisitors['pages']; ?>)</td>
                        <td><a href="visitor-session.php?sessionID=<?php echo $row_rsPageVisitors['sessionID']; ?>" class="link_view">Session</a></td>
                      </tr>
                        <?php } while ($row_rsPageVisitors = mysql_fetch_assoc($rsPageVisitors)); ?>
                    </tbody>
                  </table>
                  <?php echo createPagination($pageNum_rsPageVisitors,$totalPages_rsPageVisitors,"rsPageVisitors") ; ?> </div>
                <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
			<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsPageVisitors);
?>
			