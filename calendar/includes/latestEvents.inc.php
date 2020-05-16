<?php if(false) {
	require_once('../../Connections/aquiescedb.php'); 
} ?>
<?php 

if(!function_exists("getImageURL")) {
	require_once(SITE_ROOT.'core/includes/framework.inc.php'); 
}

// ***PARAMS *******
// $numEvents = max number to show - default 20
// $latestDate = latest date to show - default forever

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
$eventcategoryID = isset($eventcategoryID) ? $eventcategoryID : 0;
$latestDateTime = isset($latestDateTime) ? $latestDateTime : "2999-01-01 00:00:00";
$maxRows_rsComingUp = isset($numEvents) ? $numEvents : 20;
$pageNum_rsComingUp = 0;
if (isset($_GET['pageNum_rsComingUp'])) {
  $pageNum_rsComingUp = $_GET['pageNum_rsComingUp'];
}
$startRow_rsComingUp = $pageNum_rsComingUp * $maxRows_rsComingUp;

$usertypeID = isset($_SESSION['MM_UserGroup']) ? $_SESSION['MM_UserGroup'] : 0;
$varCategoryID_rsComingUp = $eventcategoryID;
if (isset($_GET['categoryID'])) {
  $varCategoryID_rsComingUp = $_GET['categoryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsComingUp = sprintf("SELECT event.ID, eventgroup.eventtitle,eventgroup.imageURL, eventcategory.title AS eventcategory, location.locationname, event.startdatetime FROM event LEFT JOIN eventgroup ON (event.eventgroupID = eventgroup.ID) LEFT JOIN eventcategory ON (eventgroup.categoryID = eventcategory.ID) LEFT JOIN location ON (event.eventlocationID = location.ID) WHERE eventgroup.usertypeID <= ".GetSQLValueString($usertypeID,"int")." AND																																																																																																				   DATE(event.startdatetime )>= CURDATE() AND 																																																																																																		   DATE(event.startdatetime )<=  ".GetSQLValueString($latestDateTime, "date")." AND event.statusID = 1 AND (eventgroup.categoryID IS NULL OR eventgroup.categoryID = %s OR %s = 0) ORDER BY event.startdatetime ", GetSQLValueString($varCategoryID_rsComingUp, "int"),GetSQLValueString($varCategoryID_rsComingUp, "int"));
$query_limit_rsComingUp = sprintf("%s LIMIT %d, %d", $query_rsComingUp, $startRow_rsComingUp, $maxRows_rsComingUp);
$rsComingUp = mysql_query($query_limit_rsComingUp, $aquiescedb) or die(mysql_error());
$row_rsComingUp = mysql_fetch_assoc($rsComingUp);

if (isset($_GET['totalRows_rsComingUp'])) {
  $totalRows_rsComingUp = $_GET['totalRows_rsComingUp'];
} else {
  $all_rsComingUp = mysql_query($query_rsComingUp);
  $totalRows_rsComingUp = mysql_num_rows($all_rsComingUp);
}
$totalPages_rsComingUp = ceil($totalRows_rsComingUp/$maxRows_rsComingUp)-1;
?>
<div class="eventList"><?php if ($totalRows_rsComingUp == 0) { // Show if recordset empty ?>
    <p>There are currently no upcoming events.</p>
    <?php } // Show if recordset empty ?>
<?php if ($totalRows_rsComingUp > 0) { // Show if recordset not empty ?>
  <ul>
    <?php do { ?>
      <li>
      <div  class="image"><a href="/calendar/event.php?eventID=<?php echo $row_rsComingUp['ID']; ?>"><?php if(isset($row_rsComingUp['imageURL'])) { ?><img src="<?php echo getImageURL($row_rsComingUp['imageURL'],"thumb"); ?>" alt="<?php echo $row_rsComingUp['eventtitle']; ?>" border="0" class="thumb" /><?php } ?></a></div>
        <div  class="datetime"><span class="eventdate"><time itemprop="startDate" datetime="<?php echo date('Y-m-dTH:i:s',strtotime($row_rsComingUp['startdatetime'])); ?>"><?php if(date('Y-m-d',strtotime($row_rsComingUp['startdatetime']))==date('Y-m-d')) { echo "Today"; } else if(date('Y-m-d',strtotime($row_rsComingUp['startdatetime']." - 1 DAY"))==date('Y-m-d')) { echo "Tomorrow"; } else { echo date('D d M Y',strtotime($row_rsComingUp['startdatetime'])); } ?></time></span> <span class="eventtime"><?php echo date('g:ia',strtotime($row_rsComingUp['startdatetime'])); ?></span>  <span class="eventduration"><?php echo isset($row_rsEvents['enddatetime']) && function_exists("showDuration") ?  showDuration($row_rsEvents['startdatetime'], $row_rsEvents['enddatetime']) : ""; ?></span></div>
        <div><span class="eventtitle"><a href="/calendar/event.php?eventID=<?php echo $row_rsComingUp['ID']; ?>"><?php echo $row_rsComingUp['eventtitle']; ?></a></span> <span class="eventlocation"><?php echo isset($row_rsComingUp['locationname']) ? $row_rsComingUp['locationname'] : ""; ?></span> <span class="eventcategory"><?php echo $row_rsComingUp['eventcategory']; ?></span></div>
        
      </li>
      <?php } while ($row_rsComingUp = mysql_fetch_assoc($rsComingUp)); ?>
  </ul>
  <?php } // Show if recordset not empty ?></div>
<?php
mysql_free_result($rsComingUp);
?>
