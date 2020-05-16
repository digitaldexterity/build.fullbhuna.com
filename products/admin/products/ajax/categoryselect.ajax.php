<?php require_once('../../../../Connections/aquiescedb.php'); ?>
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

$varRegionID_rsCategories = "-1";
if (isset($_GET['regionID'])) {
  $varRegionID_rsCategories = $_GET['regionID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsCategories = sprintf("SELECT productcategory.ID, productcategory.title, parentcategory.title AS parenttitle FROM productcategory LEFT JOIN productcategory AS parentcategory ON (productcategory.subcatofID = parentcategory.ID) WHERE productcategory.regionID = %s ORDER BY parentcategory.title ASC, productcategory.title ASC ", GetSQLValueString($varRegionID_rsCategories, "int"));
$rsCategories = mysql_query($query_rsCategories, $aquiescedb) or die(mysql_error());
$row_rsCategories = mysql_fetch_assoc($rsCategories);
$totalRows_rsCategories = mysql_num_rows($rsCategories);
if($totalRows_rsCategories>0) {
?>
<select name="regioncategoryID"><option value="0">Choose category...</option>
  <?php do { ?>
   <option value="<?php echo $row_rsCategories['ID']; ?>"><?php echo isset($row_rsCategories['parenttitle']) ? $row_rsCategories['parenttitle']." > " : ""; ?><?php echo $row_rsCategories['title']; ?></option>
    <?php } while ($row_rsCategories = mysql_fetch_assoc($rsCategories)); ?></select>
<?php
} mysql_free_result($rsCategories);
?>