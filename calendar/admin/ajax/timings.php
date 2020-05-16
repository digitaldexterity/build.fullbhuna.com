<?php require_once('../../../Connections/aquiescedb.php'); ?><?php require_once('../../includes/calendar.inc.php'); ?>
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

// add event
//die($_GET['eventgroupID']."*".$_SERVER['REQUEST_URI']); 

if(isset($_GET['addevent'])) { // event to add
	$startdatetime = isset($_GET['startdatetime']) ? $_GET['startdatetime'] : date('Y-m-d H:i:s');
	$enddatetime = (isset($_GET['enddatetime']) && $_GET['enddatetime'] > $_GET['startdatetime'])  ? $_GET['enddatetime'] : $startdatetime;
	$allday = isset($_GET['allday']) ? true : false;
	$recurringend = (isset($_GET['repeats']) && $_GET['recurringend']!="") ? date('Y-m-d 23:59:59',strtotime($_GET['recurringend'])) : $_GET['startdatetime'];
	$recurringinterval = strtoupper($_GET['recurringinterval']);
	$recurringmultiple = intval($_GET['recurringmultiple']);

	$eventID = addEvent($_GET['eventgroupID'],$startdatetime,$enddatetime,$_GET['eventlocationID'],$_GET['createdbyID'], $allday, $recurringend, $recurringinterval, $recurringmultiple, "", $_GET['nthdow']);
	
		
} // end add event


if(isset($_GET['deleteeventID'])) { // delete event
	if($_GET['deleteeventID']==0) { // delete all
		$delete = "DELETE FROM event WHERE eventgroupID = ".GetSQLValueString($_GET['eventgroupID'],"int");
	} else {
 		$delete = "DELETE FROM event WHERE ID = ".GetSQLValueString($_GET['deleteeventID'],"int");
	}
	$result = mysql_query($delete, $aquiescedb) or die(mysql_error());
}// end delete event


$varShowHistory_rsEventTimings = "0";
if (isset($_GET['showhistory'])) {
  $varShowHistory_rsEventTimings = $_GET['showhistory'];
}
$varEventGroup_rsEventTimings = "-1";
if (isset($_GET['eventgroupID'])) {
  $varEventGroup_rsEventTimings = $_GET['eventgroupID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsEventTimings = sprintf("SELECT event.*, locationname, users.firstname, users.surname FROM event LEFT JOIN location ON (event.eventlocationID = location.ID) LEFT JOIN users ON (event.userID = users.ID) WHERE eventgroupID = %s AND (%s=1 OR DATE(startdatetime) >= CURDATE()) ORDER BY startdatetime ASC", GetSQLValueString($varEventGroup_rsEventTimings, "int"),GetSQLValueString($varShowHistory_rsEventTimings, "int"));
$rsEventTimings = mysql_query($query_rsEventTimings, $aquiescedb) or die(mysql_error());
$row_rsEventTimings = mysql_fetch_assoc($rsEventTimings);
$totalRows_rsEventTimings = mysql_num_rows($rsEventTimings);
$count = 0;
?>
<?php  if ($totalRows_rsEventTimings > 0) { // Show if recordset not empty ?>
  <table  class="table table-hover">
    <tr>
      <th>&nbsp;</th>
      <th>Start</th> <th>&nbsp;</th> <th>&nbsp;</th><th>&nbsp;</th>
      <th>End</th> <th>&nbsp;</th> <th>&nbsp;</th>
      <th>&nbsp;</th>
      <th class="calendarLocation">Location</th>
       <th class="rank10">First ID (Admin)</th>
      <th colspan="4">Actions</th>
    </tr>
    <?php do { ?>
      <tr>
        <td class="status<?php echo $row_rsEventTimings['statusID']; ?>">&nbsp;</td>
        <td class="text-nowrap"><?php echo date('l',strtotime($row_rsEventTimings['startdatetime'])); ?></td>
        
        <td class="text-nowrap"><?php echo date('jS F Y',strtotime($row_rsEventTimings['startdatetime'])); ?></td>
        <td class="text-nowrap"><?php echo date('g.ia',strtotime($row_rsEventTimings['startdatetime'])); ?></td>
        <td>&raquo;</td>
        <td class="text-nowrap"><?php echo date('l',strtotime($row_rsEventTimings['enddatetime'])); ?></td>
         <td class="text-nowrap"><?php echo date('jS F Y',strtotime($row_rsEventTimings['enddatetime'])); ?></td>
          <td class="text-nowrap"><?php echo date('g.ia',strtotime($row_rsEventTimings['enddatetime'])); ?></td>
          <td class="userlist"><?php echo $row_rsEventTimings['firstname']; ?>&nbsp;<?php echo $row_rsEventTimings['surname']; ?></td>
        <td class="calendarLocation"><?php echo $row_rsEventTimings['locationname']; ?>&nbsp;</td>
        <td class="rank10"><?php echo $row_rsEventTimings['firsteventID']; ?></td>
        <td><a href="/calendar/admin/registration/event.php?eventID=<?php echo $row_rsEventTimings['ID']; ?>" class="link_users calendarRegistration" title="Manage registration for this event">Registrants</a></td>
        <td><a href="/calendar/admin/update_times.php?eventID=<?php echo $row_rsEventTimings['ID']; ?>&amp;eventgroupID=<?php echo $row_rsEventTimings['eventgroupID']; ?>"  class="link_edit icon_only" title="Update times for this event">Edit</a></td>
        <td><a href="/calendar/event.php?eventID=<?php echo $row_rsEventTimings['ID']; ?>" target="_blank" class="link_view" rel="noopener">View</a></td>
        <td><a href="javascript:void(0);" onclick="if(confirm('Are you sure you want to delete this event?')) { getData('ajax/timings.php?eventgroupID=<?php echo intval($_GET['eventgroupID']); ?>&deleteeventID=<?php echo $row_rsEventTimings['ID']; ?>','timings'); } return false;" class="link_delete" title="Delete this event"><i class="glyphicon glyphicon-trash"></i> Delete</a></td>
      </tr>
      <?php $count++; } while ($row_rsEventTimings = mysql_fetch_assoc($rsEventTimings)); ?>
  </table>
  <?php if ($totalRows_rsEventTimings > 5) { // only really need to show delete all if 5 or more ?>
<p><a href="javascript:void(0);" onclick="if(confirm('Are you sure you want to delete all timings for this event?')) { getData('ajax/timings.php?eventgroupID=<?php echo intval($_GET['eventgroupID']); ?>&deleteeventID=0','timings'); } return false;" class="link_delete icon_with_text" title="Delete this event">Delete all times</a></p>
  <?php } ?>
  <?php } else { ?>
<p class="alert warning alert-warning" role="alert">There are currently no specific dates or times set for this event. You can enter one or more occurances and locations for this event above.</p>
  <?php } ?>
<input type="hidden" id="eventcount" value="<?php echo $count; ?>" />
<?php
mysql_free_result($rsEventTimings);
?>
