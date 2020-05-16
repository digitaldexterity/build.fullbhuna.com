<?php require_once('../../Connections/aquiescedb.php'); ?><?php require_once('../includes/directoryfunctions.inc.php'); ?>
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

if(isset($_GET['addToCategory']) && intval($_GET['addToCategory'])>0) {
	addDirectoryToCategory(intval($_GET['directoryID']), intval($_GET['addToCategory']), intval($_GET['userID']));
	
}

if(isset($_GET['deleteFromCategory']) && intval($_GET['deleteFromCategory'])>0) {
	deleteDirectoryFromCategory(intval($_GET['directoryID']), intval($_GET['deleteFromCategory']), intval($_GET['userID']));
	
}

$colname_rsDirectoryCategories = "-1";
if (isset($_GET['directoryID'])) {
  $colname_rsDirectoryCategories = $_GET['directoryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsDirectoryCategories = sprintf("SELECT directoryincategory.ID, directoryincategory.categoryID, directorycategory.`description` FROM directoryincategory LEFT JOIN directorycategory ON (directoryincategory.categoryID = directorycategory.ID)  WHERE directoryID = %s GROUP BY directoryincategory.ID ORDER BY directorycategory.`description` ", GetSQLValueString($colname_rsDirectoryCategories, "int"));
$rsDirectoryCategories = mysql_query($query_rsDirectoryCategories, $aquiescedb) or die(mysql_error());
$row_rsDirectoryCategories = mysql_fetch_assoc($rsDirectoryCategories);
$totalRows_rsDirectoryCategories = mysql_num_rows($rsDirectoryCategories);

$varDirectoryID_rsThisDirectory = "-1";
if (isset($_GET['directoryID'])) {
  $varDirectoryID_rsThisDirectory = $_GET['directoryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsThisDirectory = sprintf("SELECT directory.categoryID FROM directory WHERE ID = %s", GetSQLValueString($varDirectoryID_rsThisDirectory, "int"));
$rsThisDirectory = mysql_query($query_rsThisDirectory, $aquiescedb) or die(mysql_error());
$row_rsThisDirectory = mysql_fetch_assoc($rsThisDirectory);
$totalRows_rsThisDirectory = mysql_num_rows($rsThisDirectory);
?>
<?php if ($totalRows_rsDirectoryCategories > 0) { // Show if recordset not empty ?>
  <table class="table table-hover">
  <tbody>
    <?php do { ?>
      <tr <?php if($row_rsDirectoryCategories['categoryID'] ==  $row_rsThisDirectory['categoryID']) { echo "class=\"mainCategory\""; }?>>
        <td><?php echo htmlentities($row_rsDirectoryCategories['description'], ENT_COMPAT, "UTF-8"); ?></td>
        <td><a href="javascript:void(0);" class="link_delete" onclick="deleteFromCategory(<?php echo $row_rsDirectoryCategories['categoryID']; ?>); return false;"><i class="glyphicon glyphicon-trash"></i> Delete</a></td>
      </tr>
      <?php } while ($row_rsDirectoryCategories = mysql_fetch_assoc($rsDirectoryCategories)); ?></tbody>
  </table>
  <?php } else { ?>
  <p>This entry does not belong to any categories</p>
  <?php } ?>
<?php
mysql_free_result($rsDirectoryCategories);

mysql_free_result($rsThisDirectory);
?>