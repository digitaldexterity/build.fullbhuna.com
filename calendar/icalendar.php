<?php require_once('../Connections/aquiescedb.php'); ?>
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

$varEventID_rsEvents = "0";
if (isset($_GET['eventID'])) {
  $varEventID_rsEvents = $_GET['eventID'];
}
$varEventGroupID_rsEvents = "0";
if (isset($_GET['eventgroupID'])) {
  $varEventGroupID_rsEvents = $_GET['eventgroupID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsEvents = sprintf("SELECT event.startdatetime, event.enddatetime, eventgroup.eventtitle, eventgroup.eventdetails FROM event LEFT JOIN eventgroup ON (event.eventgroupID = eventgroup.ID) WHERE (event.ID = %s OR event.eventgroupID = %s)", GetSQLValueString($varEventID_rsEvents, "int"),GetSQLValueString($varEventGroupID_rsEvents, "int"));
$rsEvents = mysql_query($query_rsEvents, $aquiescedb) or die(mysql_error());
$row_rsEvents = mysql_fetch_assoc($rsEvents);
$totalRows_rsEvents = mysql_num_rows($rsEvents);
 

// Define the file as an iCalendar file
header("Content-Type: text/calendar");
// Give the file a name and force download
header("Content-Disposition: inline; filename=calendar.ics");
// Header of ics file
echo "BEGIN:VCALENDAR\n";
echo "VERSION:2.0\n";
echo "PRODID:PHP\n";
//echo "METHOD:REQUEST\n";
echo "METHOD:PUBLISH\n";


// can either lopp through all events in group (if $_GET['eventgroupID'] is set) or just single event
$count = 0;
do { //loop
    echo "BEGIN:VEVENT\n";
    // The end date of an event is non-inclusive, so if the event is an all day event or one with no specific start and stop
    // times, the end date would be the next day.  This script is used with a calendar that does not deal with times, just dates,
    // so the time for all events is set to 000000.
    echo "DTSTART:".date('Ymd',strtotime($row_rsEvents['startdatetime']))."T".date('His',strtotime($row_rsEvents['startdatetime']))."\n";
    echo "DTEND:".date('Ymd',strtotime($row_rsEvents['enddatetime']))."T".date('His',strtotime($row_rsEvents['enddatetime']))."\n";
    // Only create Description field if there is a description
    if(isset($row_rsEvents['eventdetails']) && $row_rsEvents['eventdetails'] != '')
    {
            echo "DESCRIPTION:";
			// Clean up and stip tags
            // Remove all linebreaks from description stored in database
			$row_rsEvents['eventdetails'] = strip_tags($row_rsEvents['eventdetails']);
            $description = str_replace(chr(13).chr(10),"  ", $row_rsEvents['eventdetails']);
            echo $description."\n";
    }
    echo "LOCATION:\n";
	echo "SUMMARY:".$row_rsEvents['eventtitle']."\n";
    echo "UID:".$_SERVER['HTTP_HOST']."?".$_SERVER['QUERY_STRING']."-".$count."\n";
    echo "SEQUENCE:0\n";
    echo "DTSTAMP:".date('Ymd').'T'.date('His')."\n";
    echo "END:VEVENT\n";
	$count ++;
} while($row_rsEvents = mysql_fetch_assoc($rsEvents)); // end  loop
echo "END:VCALENDAR\n";

mysql_free_result($rsEvents);exit;
?>
