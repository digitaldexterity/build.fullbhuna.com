<?php require_once('../../../Connections/aquiescedb.php'); ?>
<?php if(!isset($_SESSION['MM_UserGroup']) || $_SESSION['MM_UserGroup']<7) die(); ?>
<?php if(!isset($regionID)) {
	$regionID = (isset($_SESSION['regionID']) && $_SESSION['regionID']>0) ? $_SESSION['regionID']: 1;
}
if(defined("DASHBOARD_TRACKER_PERIOD")) {
	$trackerperiod =strtolower(DASHBOARD_TRACKER_PERIOD)."s";
} else {

$trackerperiod = defined("TRACKER_PERIOD") ? strtolower(TRACKER_PERIOD)."s" : "4 weeks";
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

$varRegionID_rsHits = "1";
if (isset($regionID)) {
  $varRegionID_rsHits = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsHits = sprintf("SELECT COUNT(track_session.ID ) as hits FROM track_session WHERE DATE(track_session.datetime) >= '".date('Y-m-d', strtotime('TODAY - '.$trackerperiod))."' AND track_session.screenwidth IS NOT NULL AND track_session.regionID = %s", GetSQLValueString($varRegionID_rsHits, "int"));
$rsHits = mysql_query($query_rsHits, $aquiescedb) or die(mysql_error());
$row_rsHits = mysql_fetch_assoc($rsHits);
$totalRows_rsHits = mysql_num_rows($rsHits);

if(mysql_get_server_info()>"4.1.1") {
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsOnline = "SELECT COUNT(DISTINCT(track_session.ID)) AS sessions FROM track_session LEFT JOIN track_page ON (track_page.sessionID = track_session.ID) WHERE track_session.regionID = ".intval($regionID)." AND track_page.`datetime` > date_sub(now(), interval 5 minute)";
$rsOnline = mysql_query($query_rsOnline, $aquiescedb) or die(mysql_error().$query_rsOnline);
$row_rsOnline = mysql_fetch_assoc($rsOnline);
$totalRows_rsOnline = mysql_num_rows($rsOnline);
}


?>
<?php if(isset($row_rsHits['hits']) && $row_rsHits['hits']>0) { ?>
              <?php if(isset($row_rsOnline['sessions'])) { ?><p>There are <strong><?php echo $row_rsOnline['sessions']; ?> </strong>visitors online now. <?php } ?>In the past <?php echo $trackerperiod; ?> there has been <strong><?php echo $row_rsHits['hits']; ?></strong> site visits.</p><?php } else { ?><p>No vistors to report.</p><?php } ?>
<?php
mysql_free_result($rsHits);

if(mysql_get_server_info()>"4.1.1") {
mysql_free_result($rsOnline);
}


?>
