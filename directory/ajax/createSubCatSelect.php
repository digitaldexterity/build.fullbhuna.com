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

$varParentCatID_rsSubCats = "-1";
if (isset($_GET['parentCatID'])) {
  $varParentCatID_rsSubCats = $_GET['parentCatID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsSubCats = sprintf("SELECT ID, `description` FROM directorycategory WHERE subcatofID = %s AND statusID = 1 AND %s > 0 ORDER BY `description` ASC", GetSQLValueString($varParentCatID_rsSubCats, "int"),GetSQLValueString($varParentCatID_rsSubCats, "int"));
$rsSubCats = mysql_query($query_rsSubCats, $aquiescedb) or die(mysql_error());
$row_rsSubCats = mysql_fetch_assoc($rsSubCats);
$totalRows_rsSubCats = mysql_num_rows($rsSubCats);
?><select name="subcategoryID"  id="subcategoryID" class="form-control">
  <option value="0" <?php if (!(strcmp(0, @$_GET['categoryID']))) {echo "selected=\"selected\"";} ?>>None</option>
  <?php
do {  
?>
  <option value="<?php echo $row_rsSubCats['ID']?>"<?php if (!(strcmp($row_rsSubCats['ID'], @$_GET['categoryID']))) {echo "selected=\"selected\"";} ?>><?php echo htmlentities($row_rsSubCats['description'], ENT_COMPAT, "UTF-8"); ?></option>
  <?php
} while ($row_rsSubCats = mysql_fetch_assoc($rsSubCats));
  $rows = mysql_num_rows($rsSubCats);
  if($rows > 0) {
      mysql_data_seek($rsSubCats, 0);
	  $row_rsSubCats = mysql_fetch_assoc($rsSubCats);
  }
?>
</select>
<?php
mysql_free_result($rsSubCats);
?>
