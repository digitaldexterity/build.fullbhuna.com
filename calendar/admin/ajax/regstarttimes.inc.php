<?php if(is_readable('../../../Connections/aquiescedb.php')) { ?>
<?php require_once('../../../Connections/aquiescedb.php'); ?>
<?php } 
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

if(isset($_GET['add'])) { // add
mysql_select_db($database_aquiescedb, $aquiescedb);

$select = "SELECT ID FROM users WHERE username = ".GetSQLValueString($_SESSION['MM_Username'],"text");
$result = mysql_query($select, $aquiescedb) or die(mysql_error());
$row = mysql_fetch_assoc($result);
$insert = "INSERT INTO eventregstarttime (starttime, eventID, createdbyID, createddatetime) VALUES (".GetSQLValueString($_GET['starttime'], "text").",".GetSQLValueString($_GET['eventID'], "int").",".$row['ID'].",NOW())";
$result = mysql_query($insert, $aquiescedb) or die(mysql_error());
}


if(isset($_GET['delete'])) { // delete
mysql_select_db($database_aquiescedb, $aquiescedb);
$delete = "DELETE FROM eventregstarttime WHERE ID = ".GetSQLValueString($_GET['startID'], "int");
$result = mysql_query($delete, $aquiescedb) or die(mysql_error());
}

$colname_rsStartTimes = "-1";
if (isset($_GET['eventID'])) {
  $colname_rsStartTimes = $_GET['eventID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsStartTimes = sprintf("SELECT ID, starttime FROM eventregstarttime WHERE eventID = %s", GetSQLValueString($colname_rsStartTimes, "int"));
$rsStartTimes = mysql_query($query_rsStartTimes, $aquiescedb) or die(mysql_error());
$row_rsStartTimes = mysql_fetch_assoc($rsStartTimes);
$totalRows_rsStartTimes = mysql_num_rows($rsStartTimes);
?>
<div id="regstarttimes">
<p class="form-inline">
  <label>Add start time: 
    <input type="text" name="regstarttime" id="regstarttime"  />
  </label>
<a href="javascript:void(0);" onclick="getData('/calendar/admin/ajax/regstarttimes.inc.php?eventID=<?php echo intval($_GET['eventID']); ?>&starttime='+escape(document.getElementById('regstarttime').value)+'&add=true','regstarttimes');"><img src="/core/images/icons/add.png" alt="Add" width="16" height="16" style="vertical-align:
middle;" /></a></p>

  <?php if ($totalRows_rsStartTimes > 0) { // Show if recordset not empty ?>
  <table border="0" cellpadding="2" cellspacing="0" class="form-table">
    <?php do { ?>
      <tr>
        <td><?php echo $row_rsStartTimes['starttime']; ?></td>
        <td><a href="javascript:void(0);"  onclick="if(confirm('Are you sure you want to delete this start time?\n\nWARNING: Any data associated with current registrants for this event and this time will also be deleted.')) { getData('/calendar/admin/ajax/regstarttimes.inc.php?eventID=<?php echo intval($_GET['eventID']); ?>&startID=<?php echo $row_rsStartTimes['ID']; ?>&delete=true','regstarttimes'); }" class="link_delete"><i class="glyphicon glyphicon-trash"></i> Delete</a></td>
      </tr>
      <?php } while ($row_rsStartTimes = mysql_fetch_assoc($rsStartTimes)); ?>
  </table>
  <?php } // Show if recordset not empty ?>
</div>
<?php
mysql_free_result($rsStartTimes);
?>
