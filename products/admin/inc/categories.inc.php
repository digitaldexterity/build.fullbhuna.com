<?php // works as an include and AJAX call
if(isset($_GET['addcategoryID']) || isset($_GET['deletecategoryID'])) { // add require if not an include
	require_once('../../../Connections/aquiescedb.php');
}
?>
<?php // security
if (!isset($_SESSION)) {
  session_start();
}
if(!isset($_SESSION['MM_UserGroup']) || $_SESSION['MM_UserGroup'] < 8) { die("unauthorised access to script"); 
}
?><?php
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

// add to category
if(isset($_GET['addcategoryID']) && intval($_GET['addcategoryID'])>0) {
		mysql_select_db($database_aquiescedb, $aquiescedb);
	// first check if already in category
	$select = "SELECT * FROM product LEFT JOIN productincategory ON (product.ID = productincategory.productID) WHERE product.ID = ".intval($_GET['productID'])." AND (product.productcategoryID = ".intval($_GET['addcategoryID'])." OR productincategory.categoryID = ".intval($_GET['addcategoryID']).") LIMIT 1";
	$result = mysql_query($select, $aquiescedb) or die(mysql_error());
	if(mysql_num_rows($result)==0) { // doesn't exist 	
	$insert="INSERT INTO productincategory (categoryID, productID, createdbyID, createddatetime) VALUES (".GetSQLValueString($_GET['addcategoryID'], "int").",".GetSQLValueString($_GET['productID'], "int").",".GetSQLValueString($_GET['createdbyID'], "int").",'".date('Y-m-d H:i:s')."')";
	$result = mysql_query($insert, $aquiescedb) or die(mysql_error());	
	}
}

// remove from category
if(isset($_GET['deletecategoryID']) && intval($_GET['deletecategoryID'])>0) {
	$delete = "DELETE FROM productincategory WHERE ID = ".intval($_GET['deletecategoryID']);
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$result = mysql_query($delete, $aquiescedb) or die(mysql_error());	
}


$varProductID_rsOtherCategories = "-1";
if (isset($_GET['productID'])) {
  $varProductID_rsOtherCategories = $_GET['productID'];
}

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsOtherCategories = sprintf("SELECT productincategory.ID, productcategory.title FROM productincategory LEFT JOIN productcategory ON (productincategory.categoryID = productcategory.ID) WHERE productincategory.productID = %s", GetSQLValueString($varProductID_rsOtherCategories, "int"));
$rsOtherCategories = mysql_query($query_rsOtherCategories, $aquiescedb) or die(mysql_error());
$row_rsOtherCategories = mysql_fetch_assoc($rsOtherCategories);
$totalRows_rsOtherCategories = mysql_num_rows($rsOtherCategories);
?><?php if ($totalRows_rsOtherCategories > 0) { // Show if recordset not empty ?>
  <table class="table table-hover">
  <tbody>
    <?php do { ?>
      <tr><td><?php echo $row_rsOtherCategories['title']; ?></td>
        <td><a href="javascript:void(0);" onclick="getData('/products/admin/inc/categories.inc.php?productID=<?php echo intval($_GET['productID']); ?>&deletecategoryID=<?php echo $row_rsOtherCategories['ID']; ?>','otherCategoryList')" class="link_delete"><i class="glyphicon glyphicon-trash"></i> Delete</a></td>
      </tr>
      <?php } while ($row_rsOtherCategories = mysql_fetch_assoc($rsOtherCategories)); ?></tbody>
  </table>
  <?php } // Show if recordset not empty ?>
<?php
mysql_free_result($rsOtherCategories);
?>
