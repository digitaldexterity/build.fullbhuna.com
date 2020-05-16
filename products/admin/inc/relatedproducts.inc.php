<?php // works as an include and AJAX call
if(isset($_GET['relatedtoID']) || isset($_GET['deleteID'])) { // add require if not an include
	require_once('../../../Connections/aquiescedb.php');
}
?><?php require_once(SITE_ROOT.'/products/admin/inc/product_functions.inc.php'); ?>
<?php // security
if (!isset($_SESSION)) {
  session_start();
}
if(!isset($_SESSION['MM_UserGroup']) || $_SESSION['MM_UserGroup'] < 8) { die("unauthorised access to script"); 
}
?>
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

// add related
if(isset($_GET['relatedtoID']) && isset($_GET['productID']) && isset($_GET['createdbyID'])) {
	addRelatedProduct($_GET['productID'], $_GET['relatedtoID'],$_GET['createdbyID']);
} // end add related

// delete

if(isset($_GET['deleteID'])) {
	$delete = "DELETE FROM productrelated WHERE ID = ".GetSQLValueString($_GET['deleteID'],"int");
			$result = mysql_query($delete, $aquiescedb) or die(mysql_error());			  

} // end delete

$varProductID_rsRelatedProducts = "-1";
if (isset($_GET['productID'])) {
  $varProductID_rsRelatedProducts = $_GET['productID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsRelatedProducts = sprintf("SELECT productrelated.ID, product.ID AS productID, product.title FROM product, productrelated WHERE (productrelated.productID = %s AND productrelated.relatedtoID = product.ID) OR (productrelated.productID = product.ID AND productrelated.relatedtoID = %s) ", GetSQLValueString($varProductID_rsRelatedProducts, "int"),GetSQLValueString($varProductID_rsRelatedProducts, "int"));
$rsRelatedProducts = mysql_query($query_rsRelatedProducts, $aquiescedb) or die(mysql_error());
$row_rsRelatedProducts = mysql_fetch_assoc($rsRelatedProducts);
$totalRows_rsRelatedProducts = mysql_num_rows($rsRelatedProducts);
?>
<?php if ($totalRows_rsRelatedProducts > 0) { // Show if recordset not empty ?>
  <table class="table table-hover">
    <tbody>
    <?php do { ?>
      <tr>
        <td><?php echo $row_rsRelatedProducts['title']; ?></td>
        <td><a href="javascript:void(0);" onClick="if(confirm('Are you sure you want to delete this realtionship?')) { getData('/products/admin/inc/relatedproducts.inc.php?productID=<?php echo intval($_GET['productID']); ?>&amp;deleteID=<?php echo $row_rsRelatedProducts['ID']; ?>','relatedProductList') }" class="link_delete" ><i class="glyphicon glyphicon-trash"></i> Delete</a></td>
      </tr>
      <?php } while ($row_rsRelatedProducts = mysql_fetch_assoc($rsRelatedProducts)); ?></tbody>
  </table>
  <?php } // Show if recordset not empty ?>
<?php
mysql_free_result($rsRelatedProducts); 
?>
