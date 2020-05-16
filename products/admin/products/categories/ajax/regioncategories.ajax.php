<?php require_once('../../../../../Connections/aquiescedb.php'); ?>
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

$colname_rsRegionCategories = "-1";
if (isset($_GET['regionID'])) {
  $colname_rsRegionCategories = $_GET['regionID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsRegionCategories = sprintf("SELECT ID, title FROM productcategory WHERE regionID = %s AND statusID = 1 ORDER BY title ASC", GetSQLValueString($colname_rsRegionCategories, "int"));
$rsRegionCategories = mysql_query($query_rsRegionCategories, $aquiescedb) or die(mysql_error());
$row_rsRegionCategories = mysql_fetch_assoc($rsRegionCategories);
$totalRows_rsRegionCategories = mysql_num_rows($rsRegionCategories);

?><select name="regioncategoryID">
  <option value="0"><?php echo isset($region['text_choose']) ? htmlentities($region['text_choose'], ENT_COMPAT, "UTF-8") : "Choose..." ?></option>
   <option value="0">&nbsp;</option>
  <option value="0">New Category</option>
  <option value="0">&nbsp;</option>
  <?php
do {  
?>
  <option value="<?php echo $row_rsRegionCategories['ID']?>"><?php echo $row_rsRegionCategories['title']?></option>
  <?php
} while ($row_rsRegionCategories = mysql_fetch_assoc($rsRegionCategories));
  $rows = mysql_num_rows($rsRegionCategories);
  if($rows > 0) {
      mysql_data_seek($rsRegionCategories, 0);
	  $row_rsRegionCategories = mysql_fetch_assoc($rsRegionCategories);
  }
?>
</select>
<?php 
mysql_free_result($rsRegionCategories);
?>
