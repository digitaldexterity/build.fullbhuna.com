<?php require_once('../../Connections/aquiescedb.php'); ?>
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

$varRegionID_rsParentCats = "0";
if (isset($_GET['regionID'])) {
  $varRegionID_rsParentCats = $_GET['regionID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsParentCats = sprintf("SELECT ID, `description` FROM directorycategory WHERE subcatofID = 0 AND statusID = 1 AND (regionID=%s OR regionID =0 OR regionID IS NULL OR %s = 0) ORDER BY `description` ASC", GetSQLValueString($varRegionID_rsParentCats, "int"),GetSQLValueString($varRegionID_rsParentCats, "int"));
$rsParentCats = mysql_query($query_rsParentCats, $aquiescedb) or die(mysql_error());
$row_rsParentCats = mysql_fetch_assoc($rsParentCats);
$totalRows_rsParentCats = mysql_num_rows($rsParentCats);
?><select name="categoryID"  id="categoryID" class="form-control">
  <option value="0">None</option>
  <?php
do {  
?>
  <option value="<?php echo $row_rsParentCats['ID']?>"><?php echo htmlentities($row_rsParentCats['description'], ENT_COMPAT, "UTF-8"); ?></option>
  <?php
} while ($row_rsParentCats = mysql_fetch_assoc($rsParentCats));
  $rows = mysql_num_rows($rsParentCats);
  if($rows > 0) {
      mysql_data_seek($rsParentCats, 0);
	  $row_rsParentCats = mysql_fetch_assoc($rsParentCats);
  }
?>
</select>
<?php
mysql_free_result($rsParentCats);
?>
