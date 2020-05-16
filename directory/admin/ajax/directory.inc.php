<?php if(!$aquiescedb) {
	require_once('../../../Connections/aquiescedb.php'); 
}?><?php require_once(SITE_ROOT.'core/includes/framework.inc.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
if(isset($_SESSION['MM_UserGroup']) && intval($_SESSION['MM_UserGroup'])>7) {
?>
<?php
$currentPage = "/directory/admin/index.php";

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

$regionID = isset($regionID) ? intval($regionID)  : 1;

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsDirectoryPrefs = "SELECT * FROM directoryprefs WHERE ID =".$regionID;
$rsDirectoryPrefs = mysql_query($query_rsDirectoryPrefs, $aquiescedb) or die(mysql_error());
$row_rsDirectoryPrefs = mysql_fetch_assoc($rsDirectoryPrefs);
$totalRows_rsDirectoryPrefs = mysql_num_rows($rsDirectoryPrefs);

$maxRows_rsDirectory = 50;
$pageNum_rsDirectory = 0;
if (isset($_GET['pageNum_rsDirectory'])) {
  $pageNum_rsDirectory = $_GET['pageNum_rsDirectory'];
}
$startRow_rsDirectory = $pageNum_rsDirectory * $maxRows_rsDirectory;

$varRegionID_rsDirectory = "0";
if (isset($_GET['regionID'])) {
  $varRegionID_rsDirectory = $_GET['regionID'];
}
$varName_rsDirectory = "%";
if (isset($_GET['search'])) {
  $varName_rsDirectory = $_GET['search'];
}
$varCategoryID_rsDirectory = "0";
if (isset($_GET['categoryID'])) {
  $varCategoryID_rsDirectory = $_GET['categoryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsDirectory = sprintf("SELECT directory.ID, directory.name, directory.statusID, directory.latitude,directory.longitude,directorycategory.description, directory.categoryID, region.title, parentcategory.description AS parentcategoryname , COUNT(DISTINCT(directorylocation.ID)) AS numlocations, COUNT(DISTINCT(directoryuser.ID)) AS numcontacts FROM directory  LEFT JOIN directorycategory ON (directory.categoryID = directorycategory.ID)  LEFT JOIN directorycategory AS parentcategory ON (directorycategory.subcatofID = parentcategory.ID)  LEFT JOIN region ON (directorycategory.regionID = region.ID)  LEFT JOIN directoryincategory ON (directory.ID = directoryincategory.directoryID)  LEFT JOIN directoryuser ON (directory.ID = directoryuser.directoryID)  LEFT JOIN directorylocation ON (directorylocation.directoryID = directory.ID) LEFT JOIN location ON (directorylocation.locationID = location.ID) WHERE (directorycategory.regionID = 0 OR directorycategory.regionID = %s OR %s =0) AND (%s = 0 OR directory.categoryID = %s OR directoryincategory.categoryID= %s) AND (directory.name LIKE %s OR locationname LIKE %s) GROUP BY directory.ID ORDER BY  directory.ordernum, directory.name ASC", GetSQLValueString($varRegionID_rsDirectory, "int"),GetSQLValueString($varRegionID_rsDirectory, "int"),GetSQLValueString($varCategoryID_rsDirectory, "int"),GetSQLValueString($varCategoryID_rsDirectory, "int"),GetSQLValueString($varCategoryID_rsDirectory, "int"),GetSQLValueString($varName_rsDirectory . "%", "text"),GetSQLValueString($varName_rsDirectory . "%", "text"));
$query_limit_rsDirectory = sprintf("%s LIMIT %d, %d", $query_rsDirectory, $startRow_rsDirectory, $maxRows_rsDirectory);
$rsDirectory = mysql_query($query_limit_rsDirectory, $aquiescedb) or die(mysql_error());
$row_rsDirectory = mysql_fetch_assoc($rsDirectory);

if (isset($_GET['totalRows_rsDirectory'])) {
  $totalRows_rsDirectory = $_GET['totalRows_rsDirectory'];
} else {
  $all_rsDirectory = mysql_query($query_rsDirectory);
  $totalRows_rsDirectory = mysql_num_rows($all_rsDirectory);
}
$totalPages_rsDirectory = ceil($totalRows_rsDirectory/$maxRows_rsDirectory)-1;

$queryString_rsDirectory = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsDirectory") == false && 
        stristr($param, "totalRows_rsDirectory") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsDirectory = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsDirectory = sprintf("&totalRows_rsDirectory=%d%s", $totalRows_rsDirectory, $queryString_rsDirectory);

$link = (isset($row_rsDirectoryPrefs['managedirectoryURL']) && $row_rsDirectoryPrefs['managedirectoryURL']!="") ? $row_rsDirectoryPrefs['managedirectoryURL'] : "update_directory.php";
$link .= "?directoryID=";

} 
?>
<?php if ($totalRows_rsDirectory == 0) { // Show if recordset empty ?>
          <p>There are no results. <?php //echo $query_rsDirectory ; ?></p>
<?php } // Show if recordset empty ?>
          <?php if ($totalRows_rsDirectory > 0) { // Show if recordset not empty ?>
      <p class="text-muted">Entries <?php echo ($startRow_rsDirectory + 1) ?> to <?php echo min($startRow_rsDirectory + $maxRows_rsDirectory, $totalRows_rsDirectory) ?> of <?php echo $totalRows_rsDirectory ?>.  <span id="info">Choose category and drag and drop to set order</span></p>
      </table>
        <table class="table table-hover">
        <thead>
          <tr>
            <th class="draganddrop">&nbsp;</th><th>&nbsp;</th> <th>ID</th>
            <th>Entry&nbsp;&nbsp;</td>
            <th colspan="3" >Category, Addresses, Contacts</th>
            <th class="region" >Site</th>
            <th>&nbsp;</th>
          </tr></th><tbody class="sortable">
          <?php do { ?>
            <tr id="listItem_<?php echo $row_rsDirectory['ID']; ?>" ><td class= "draganddrop handle" data-toggle="tooltip" data-placement="right" title="Drag and drop order of items">&nbsp;</td>
              <td class="status<?php echo $row_rsDirectory['statusID']; ?>">&nbsp;</td>  <td><?php echo $row_rsDirectory['ID']; ?></td>
              <td><a href="<?php echo $link.$row_rsDirectory['ID']; ?>"><?php echo $row_rsDirectory['name']; ?></a>&nbsp;&nbsp;</td>
              <td><a href="category/update_category.php?categoryID=<?php echo $row_rsDirectory['categoryID']; ?>"><?php echo isset($row_rsDirectory['parentcategoryname']) ? $row_rsDirectory['parentcategoryname']." > " : ""; echo $row_rsDirectory['description']; ?></a></td>
              <td><?php echo $row_rsDirectory['numlocations']+1; if(isset($row_rsDirectory['latitude'])) { ?> <i class="glyphicon glyphicon-flag" title="Location mapped"></i><?php } ?></td>
              <td><?php echo $row_rsDirectory['numcontacts']; ?></td>
              <td valign="middle" class="region" ><?php echo isset($row_rsDirectory['title']) ? $row_rsDirectory['title'] : "All"; ?></td>
              <td valign="middle" ><a href="<?php echo $link.$row_rsDirectory['ID']; ?>" class="btn btn-default btn-secondary"><i class="glyphicon glyphicon-pencil"></i> Edit</a></td>
          </tr>
            <?php } while ($row_rsDirectory = mysql_fetch_assoc($rsDirectory)); ?></tbody>
        </table>
<?php } // Show if recordset not empty ?>
     <?php echo createPagination($pageNum_rsDirectory,$totalPages_rsDirectory,"rsDirectory",20, "index.php");?>
<?php

mysql_free_result($rsDirectory);

mysql_free_result($rsDirectoryPrefs);
?>