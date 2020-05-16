<?php if(!isset($aquiescedb) && is_readable('../../../Connections/aquiescedb.php')) { ?>
<?php require_once('../../../Connections/aquiescedb.php'); ?>
<?php } ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
if(!isset($_SESSION['MM_UserGroup'])) { die(); }


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
 
$varUsername_rsMyEntries = "admin";
if (isset($_SESSION['MM_Username'])) {
  $varUsername_rsMyEntries = $_SESSION['MM_Username'];
}
$varShowCreated_rsMyEntries = "0";
if (isset($_GET['showcreated'])) {
  $varShowCreated_rsMyEntries = $_GET['showcreated'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsMyEntries = sprintf("SELECT directory.name, directory.ID AS directoryID, directory.statusID, directory.address1, directory.address2, directory.address3, directory.address4, directory.address5, directory.postcode, directory.telephone, directory.fax, directory.mobile, directory.email, COUNT(contacts.ID) AS numcontacts, COUNT(directorylocation.ID) AS numlocations FROM directory LEFT JOIN users AS creator ON (creator.ID = directory.createdbyID) LEFT JOIN directoryuser ON (directoryuser.directoryID = directory.ID) LEFT JOIN users ON (users.ID = directoryuser.userID) LEFT JOIN users AS contacts ON (contacts.ID = directoryuser.userID) LEFT JOIN directorylocation ON (directorylocation.directoryID = directory.ID) WHERE (users.username = %s) OR (%s=1 AND creator.username = %s) GROUP BY directory.ID ORDER BY directory.name", GetSQLValueString($varUsername_rsMyEntries, "text"),GetSQLValueString($varShowCreated_rsMyEntries, "int"),GetSQLValueString($varUsername_rsMyEntries, "text"));
$rsMyEntries = mysql_query($query_rsMyEntries, $aquiescedb) or die(mysql_error());
$row_rsMyEntries = mysql_fetch_assoc($rsMyEntries);
$totalRows_rsMyEntries = mysql_num_rows($rsMyEntries);
?>

    <?php if ($totalRows_rsMyEntries > 0) { // Show if recordset not empty ?>
      <p>You have the following entries in the directory:</p>
      <table border="0" cellpadding="2" cellspacing="0" class="form-table">
        <?php do { ?>
          <tr>
            <td  class="status<?php echo $row_rsMyEntries['statusID']; ?>">&nbsp;</td>
            <td><strong><?php echo $row_rsMyEntries['name']; ?></strong><br />
            <?php echo nl2br(trim($row_rsMyEntries['address1'].",\n".$row_rsMyEntries['address2'].",\n".$row_rsMyEntries['address3'].",\n".$row_rsMyEntries['address4'].",\n".$row_rsMyEntries['address5'],",\n "));
			echo isset($row_rsMyEntries['postcode']) ? "<br />".$row_rsMyEntries['postcode'] : "";
			echo isset($row_rsMyEntries['telephone']) ? "<br />Telephone: ".$row_rsMyEntries['telephone'] : "";
			echo isset($row_rsMyEntries['fax']) ? "<br />Fax: ".$row_rsMyEntries['fax'] : "";
			echo isset($row_rsMyEntries['mobile']) ? "<br />Mobile: ".$row_rsMyEntries['mobile'] : "";
			echo isset($row_rsMyEntries['email']) ? "<br />email: ".$row_rsMyEntries['email'] : ""; ?></td>
              <td><a href="/directory/members/locations/index.php?directoryID=<?php echo $row_rsMyEntries['directoryID']; ?>"><?php echo $row_rsMyEntries['numlocations']; ?> location<span class="plural<?php echo $row_rsMyEntries['numlocations']; ?>">s</span></a></td>
                <td><a href="/directory/members/contacts/index.php?directoryID=<?php echo $row_rsMyEntries['directoryID']; ?>"><?php echo $row_rsMyEntries['numcontacts']; ?> contact<span class="plural<?php echo $row_rsMyEntries['numcontacts']; ?>">s</span></a></td>
            <td><a href="/directory/members/update_directory.php?directoryID=<?php $directoryID = $row_rsMyEntries['directoryID']; echo $directoryID; ?>" class="link_edit icon_only">Edit</a></td>
          </tr>
          <?php } while ($row_rsMyEntries = mysql_fetch_assoc($rsMyEntries)); ?>
      </table>
      <?php } // Show if recordset not empty ?>

	 <?php
mysql_free_result($rsMyEntries);
?>