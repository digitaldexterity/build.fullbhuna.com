<?php require_once('../../../Connections/aquiescedb.php'); ?>
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

$varSurname_rsUsers = "%";
if (isset($_GET['surname'])) {
  $varSurname_rsUsers = trim($_GET['surname']);
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsUsers = sprintf("SELECT users.ID, users.firstname, users.surname, users.email FROM users WHERE users.surname LIKE %s ORDER BY users.surname", GetSQLValueString($varSurname_rsUsers . "%", "text"));
$rsUsers = mysql_query($query_rsUsers, $aquiescedb) or die(mysql_error());
$row_rsUsers = mysql_fetch_assoc($rsUsers);
$totalRows_rsUsers = mysql_num_rows($rsUsers);

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);
?>
<?php if(isset($_SESSION['MM_UserGroup']) && $_SESSION['MM_UserGroup'] >=7 ) { 
if ($totalRows_rsUsers > 0) { // Show if recordset not empty ?>
  <table class="table table-hover">
  <thead>
    <tr>
      <th>Name</th>
      <th>email</th>
      <th>Add</th>
    </tr></thead><tbody>
    <?php do { ?>
      <tr>
        <td><?php echo $row_rsUsers['firstname']; ?> <?php echo $row_rsUsers['surname']; ?></td>
        <td><?php echo $row_rsUsers['email']; ?></td>
        <td><a href="/location/admin/modify_location.php?locationID=<?php echo intval($_GET['locationID']); ?>&defaultTab=2&adduserID=<?php echo $row_rsUsers['ID']; ?>&createdbyID=<?php echo $row_rsLoggedIn['ID']; ?><?php if(isset($_GET['returnURL']) && $_GET['returnURL']!="") { echo "&returnURL=".urlencode($_GET['returnURL']); } ?>" ><i class="glyphicon glyphicon-plus-sign"></i> Add</a></td>
      </tr>
      <?php } while ($row_rsUsers = mysql_fetch_assoc($rsUsers)); ?>
  </tbody></table>
  <?php } // Show if recordset not empty 
}
mysql_free_result($rsUsers);
?>
<?php
mysql_free_result($rsLoggedIn);
?>
