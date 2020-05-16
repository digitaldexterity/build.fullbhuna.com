<?php if(is_readable('../../../Connections/aquiescedb.php')) { ?>
<?php require_once('../../../Connections/aquiescedb.php'); ?>
<?php } ?>
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

$varUsername_rsMyRegistrations = "-1";
if (isset($_SESSION['MM_Username'])) {
  $varUsername_rsMyRegistrations = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsMyRegistrations = sprintf("SELECT eventregistration.ID, eventgroup.eventtitle, eventregistration.registrationnumber, eventregistration.createddatetime FROM eventregistration INNER JOIN users ON (eventregistration.userID = users.ID) LEFT JOIN event ON (eventregistration.eventID = event.ID) LEFT JOIN eventgroup ON (event.eventgroupID = eventgroup.ID)  WHERE eventregistration.userID AND users.username = %s", GetSQLValueString($varUsername_rsMyRegistrations, "text"));
$rsMyRegistrations = mysql_query($query_rsMyRegistrations, $aquiescedb) or die(mysql_error());
$row_rsMyRegistrations = mysql_fetch_assoc($rsMyRegistrations);
$totalRows_rsMyRegistrations = mysql_num_rows($rsMyRegistrations);
?>
<?php if ($totalRows_rsMyRegistrations > 0) { // Show if recordset not empty ?>
<h2>My event registrations</h2>
  <table border="0" cellpadding="2" cellspacing="0" class="listTable">
    <tr>
      <td><strong>Date</strong></td>
      <td><strong>Event</strong></td>
      <td><strong>Reg. No.</strong></td>
      <td>&nbsp;</td>
    </tr>
    <?php do { ?>
      <tr>
        <td><?php echo date('d M Y',strtotime($row_rsMyRegistrations['createddatetime'])); ?></td>
        <td><?php echo $row_rsMyRegistrations['eventtitle']; ?></td>
        <td><?php echo $row_rsMyRegistrations['registrationnumber']; ?></td>
        <td>&nbsp;</td>
      </tr>
      <?php } while ($row_rsMyRegistrations = mysql_fetch_assoc($rsMyRegistrations)); ?>
  </table>
  <?php } // Show if recordset not empty ?>
<?php
mysql_free_result($rsMyRegistrations);
?>
