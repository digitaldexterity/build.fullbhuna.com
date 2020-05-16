<?php require_once('../../Connections/aquiescedb.php'); ?>
<?php if(isset($_GET['token']) && $_GET['token'] == md5(PRIVATE_KEY.$_GET['eventID'])) {
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

$colname_rsEvent = "-1";
if (isset($_GET['eventID'])) {
  $colname_rsEvent = $_GET['eventID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsEvent = sprintf("SELECT event.*, eventgroup.eventtitle, eventgroup.resourceID, eventgroup.eventdetails, eventcategory.title AS categoryname, eventresource.resourcename, users.firstname, users.surname FROM event LEFT JOIN eventgroup ON (event.eventgroupID = eventgroup.ID) LEFT JOIN eventcategory ON (eventgroup.categoryID = eventcategory.ID) LEFT JOIN eventresource ON (eventgroup.resourceID = eventresource.ID) LEFT JOIN users ON (event.createdbyID = users.ID) WHERE event.ID = %s", GetSQLValueString($colname_rsEvent, "int"));
$rsEvent = mysql_query($query_rsEvent, $aquiescedb) or die(mysql_error());
$row_rsEvent = mysql_fetch_assoc($rsEvent);
$totalRows_rsEvent = mysql_num_rows($rsEvent);

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

$colname_rsAttendees = "-1";
if (isset($_GET['eventID'])) {
  $colname_rsAttendees = $_GET['eventID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsAttendees = sprintf("SELECT eventattend.ID, eventattend.userID, eventattend.statusID,users.firstname, users.surname, users.defaultaddressID, users.email, users.mobile FROM eventattend LEFT JOIN users ON (eventattend.userID = users.ID) WHERE eventID = %s", GetSQLValueString($colname_rsAttendees, "int"));
$rsAttendees = mysql_query($query_rsAttendees, $aquiescedb) or die(mysql_error());
$row_rsAttendees = mysql_fetch_assoc($rsAttendees);
$totalRows_rsAttendees = mysql_num_rows($rsAttendees);

mysql_data_seek($rsAttendees,0);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Update event</title>
<link href="/core/css/global.css" rel="stylesheet"  />
<link href="/calendar/css/calendarDefault.css" rel="stylesheet"  /><link href="/local/css/styles.css" rel="stylesheet"  />
</head>

<body class="ajaxUpdateEventPage">
<h2> Details</h2><form  method="post" action="/calendar/day.php" target="_top"><table class="form-table">
 <?php if(isset($row_rsEvent['categoryname'])) { ?>
  <tr>
    <td class="text-right"><label for="eventcategoryID">Type:</label></td>
    <td>
      <em><?php echo $row_rsEvent['categoryname']; ?></em></td>
  </tr>
  <?php } ?>
  <tr>
    <td class="text-right"><label for="eventtitle">Title:</label></td>
    <td><?php echo $row_rsEvent['eventtitle']; ?>
      
      
    </td>
  </tr>
  <?php if(isset($row_rsEvent['resourcename'])) { ?>
  <tr>
    <td class="text-right"><label for="resourceID">Resource:</label></td>
    <td><?php echo $row_rsEvent['resourcename']; ?>
      </td>
  </tr>
  <?php } ?>
  <tr>
    <td class="text-right">Time:</td>
    <td><?php echo date('H:i d M Y', strtotime($row_rsEvent['startdatetime'])); ?>
</td>
  </tr>
  <tr>
    <td class="text-right">Duration:</td>
    <td><?php if(isset($row_rsEvent['enddatetime'])) {
		$duration = strtotime($row_rsEvent['enddatetime']) - strtotime($row_rsEvent['startdatetime']);
		
		$hours = floor($duration/(60*60));
		$minutes = ($duration%(60*60))/60;
	} echo $hours>0 ?  $hours." hours "  : ""; 
	echo $minutes>0 ?  intval($minutes)." minutes "  : ""; ?>
         
      </td>
  </tr><?php if($totalRows_rsAttendees>0) { ?><tr>
    <td class="text-right">Attendees(s):</td>
    <td><ol>
  <?php while ($row_rsAttendees = mysql_fetch_assoc($rsAttendees))  { ?><li>
    <?php echo $row_rsAttendees['firstname']; ?> <?php echo $row_rsAttendees['surname']; ?></li>
    
    <?php }  ?></ol>
</td>
  </tr><?php } ?>
  <tr>
    <td class="top text-right">Notes:</td>
    <td><?php echo nl2br($row_rsEvent['eventdetails']); ?></td>
  </tr>
 
  <tr>
    <td class="text-right">Added by:</td>
    <td><?php echo $row_rsEvent['firstname']; ?> <?php echo $row_rsEvent['surname']; ?> at <?php echo date('H:i', strtotime($row_rsEvent['createddatetime'])); ?> on <?php echo date('d M Y', strtotime($row_rsEvent['createddatetime'])); ?></td>
  </tr>
  <tr>
    <td class="text-right">&nbsp;</td>
    <td><?php  if($row_rsLoggedIn['ID']==$row_rsEvent['createdbyID'] && isset($_GET['openerURL'])) { ?><a href="/calendar/members/update_event.php?eventID=<?php echo $row_rsEvent['ID']; ?>&returnURL=<?php echo urlencode($_GET['openerURL']); ?>" target="_top" class="button">Edit</a> <?php $deleteURL = $_GET['openerURL'];
	$deleteURL .= strpos($_GET['openerURL'],"?")>0 ?  "&" : "?";
	$deleteURL .= "deleteeventID=".$row_rsEvent['ID'] ?><a href="<?php echo $deleteURL; ?>" target="_top" class="button" onclick="return confirm('Are you sure you want to delete this event?')">Delete</a><?php } ?></td>
  </tr>
</table>

</body>
</html>
<?php
mysql_free_result($rsEvent);

mysql_free_result($rsLoggedIn);

mysql_free_result($rsAttendees);
} else {
	die("No access");
}
?>
