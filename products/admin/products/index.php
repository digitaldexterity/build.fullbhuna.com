<?php require_once('../../../Connections/aquiescedb.php'); ?>
<?php require_once('../../../core/includes/adminAccess.inc.php'); ?>
<?php require_once('../inc/product_functions.inc.php'); ?>
<?php require_once('../../../core/includes/framework.inc.php'); ?>
<?php require_once('../../includes/productFunctions.inc.php'); ?>
<?php require_once('../../includes/basketFunctions.inc.php'); ?>
<?php require_once('../google/inc/googlebase.inc.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "6,7,8,9,10";
$MM_donotCheckaccess = "false";

// *** Restrict Access To Page: Grant or deny access to this page
function isAuthorized($strUsers, $strGroups, $UserName, $UserGroup) { 
  // For security, start by assuming the visitor is NOT authorized. 
  $isValid = False; 

  // When a visitor has logged into this site, the Session variable MM_Username set equal to their username. 
  // Therefore, we know that a user is NOT logged in if that Session variable is blank. 
  if (!empty($UserName)) { 
    // Besides being logged in, you may restrict access to only certain users based on an ID established when they login. 
    // Parse the strings into arrays. 
    $arrUsers = Explode(",", $strUsers); 
    $arrGroups = Explode(",", $strGroups); 
    if (in_array($UserName, $arrUsers)) { 
      $isValid = true; 
    } 
    // Or, you may restrict access to only certain users based on their username. 
    if (in_array($UserGroup, $arrGroups)) { 
      $isValid = true; 
    } 
    if (($strUsers == "") && false) { 
      $isValid = true; 
    } 
  } 
  return $isValid; 
}

$MM_restrictGoTo = "../../../login/index.php?notloggedin=true";
if (!((isset($_SESSION['MM_Username'])) && (isAuthorized("",$MM_authorizedUsers, $_SESSION['MM_Username'], $_SESSION['MM_UserGroup'])))) {   
  $MM_qsChar = "?";
  $MM_referrer = $_SERVER['PHP_SELF'];
  if (strpos($MM_restrictGoTo, "?")) $MM_qsChar = "&";
  if (isset($_SERVER['QUERY_STRING']) && strlen($_SERVER['QUERY_STRING']) > 0) 
  $MM_referrer .= "?" . $_SERVER['QUERY_STRING'];
  $MM_restrictGoTo = $MM_restrictGoTo. $MM_qsChar . "accesscheck=" . urlencode($MM_referrer);
  header("Location: ". $MM_restrictGoTo); 
  exit;
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


$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);



if((isset($_POST['addtocategoryID']) && $_POST['addtocategoryID'] !="") || (isset($_POST['movetocategoryID']) && $_POST['movetocategoryID'] !="") ) { 
	if(isset($_POST['checkbox']) && !empty($_POST['checkbox'])) {	
	
		foreach($_POST['checkbox'] as $key=>$productID) {		
			$select = "SELECT productcategoryID FROM product WHERE ID = ".	$productID." LIMIT 1";	 // get current category	
			$result = mysql_query($select, $aquiescedb) or die(mysql_error());	
			$row = mysql_fetch_assoc($result);
			if(isset($_POST['movetocategoryID']) && $_POST['movetocategoryID'] !="") {
				$default = 1;	
				$categoryID = $_POST['movetocategoryID'];
				$delete = "DELETE FROM productincategory WHERE productID = ".$productID." AND categoryID = ".intval($row['productcategoryID']);
				mysql_query($delete, $aquiescedb) or die(mysql_error());
			} else {
				$default = 0;
				$categoryID = $_POST['addtocategoryID'];
			}
			
			
			addProductToCategory($productID, $categoryID, $default, $_POST['createdbyID']);
			
		}
		//unset($_SESSION['checkbox']);
		header("location: index.php?msg=".urlencode("Selected items have updated category.")); 
		exit;
		
	} else {
		$submit_error = "You must choose one or more products.";
	}
	
}

if(isset($_POST['setsupplierID']) && $_POST['setsupplierID'] !="") { 
	if(isset($_POST['checkbox']) && !empty($_POST['checkbox'])) {	
	
		foreach($_POST['checkbox'] as $key=>$productID) {		
			$update = "UPDATE product SET supplierdirectoryID = ".GetSQLValueString($_POST['setsupplierID'], "int")." WHERE ID = ".intval($productID);
			mysql_query($update, $aquiescedb) or die(mysql_error());
			
		}
		
		//unset($_SESSION['checkbox']);
		header("location: index.php?msg=".urlencode("Selected items have updated supplier.")); 
		exit;
		
	} else {
		$submit_error = "You must choose one or more products.";
	}
	
}




if(isset($_POST['copytoregionID']) && intval($_POST['copytoregionID'])>0 && isset($_POST['regioncategoryID']) && intval($_POST['regioncategoryID'])>0) {
	if(isset($_POST['checkbox']) && !empty($_POST['checkbox'])) {	
	
	
		foreach($_POST['checkbox'] as $key=>$productID) {	
			
			copyProductToRegion($productID, $_POST['regioncategoryID'],$_POST['copytoregionID']) ;
			$msg = "Selected products have been copied.";
		}
	} else {
		$submit_error = "You must choose one or more products.";
	}
	
}

if(isset($_POST['formAction'])) {
	if(isset($_POST['checkbox']) && count($_POST['checkbox'])>0) {
		if($_POST['formAction'] == "mergeOptions") {
			header("location: merge.php"); exit;
		} else if ($_POST['formAction'] == "pending") { // delete
			
			foreach($_POST['checkbox'] as $key=>$value) {							
 				$update = "UPDATE product SET statusID = 0 WHERE ID = ".$value;
 				mysql_query($update, $aquiescedb) or die(mysql_error());
			}
			//unset($_SESSION['checkbox']);
			header("location: index.php?msg=".urlencode("Selected items have been made inactive.")); 
			exit;
		
	} else if ($_POST['formAction'] == "delete") { // delete
			
			foreach($_POST['checkbox'] as $key=>$value) {							
 				$update = "UPDATE product SET statusID = 2 WHERE ID = ".$value;
 				mysql_query($update, $aquiescedb) or die(mysql_error());
			}
			//unset($_SESSION['checkbox']);
			header("location: index.php?msg=".urlencode("Selected items have been deleted.")); 
			exit;
		} else if($_POST['formAction'] == "makeRelated") {
			$count = 0;
			foreach($_POST['checkbox'] as $key=>$productID) {
				foreach($_POST['checkbox'] as $key=>$relatedproductID) {
					addRelatedProduct($productID, $relatedproductID,$_POST['createdbyID']);
					
					
				}
				$count ++;
			}
			$msg = $count ." products were set as related to each other";
		} else if($_POST['formAction'] == "deleteRelated") {
			$count = 0;
			foreach($_POST['checkbox'] as $key=>$productID) {
				removeRelatedProducts($productID);$count ++;
			}
			$msg = $count ." items had their replated products removed.";
		}
		
		
	} else {
		$submit_error = "You must choose one or more products.";
	}
}
mysql_select_db($database_aquiescedb, $aquiescedb);
if(isset($_GET['deleteID']) && intval($_GET['deleteID'])>0) { // delete
 $update = "UPDATE product SET statusID = 2 WHERE ID = ".intval($_GET['deleteID']);
 mysql_query($update, $aquiescedb) or die(mysql_error());
} // end delete



if(isset($_GET['duplicate_productID']) && intval($_GET['duplicate_productID'])>0) {
	// duplicate, then add 'duplicate' to name, clear stock number
	$newproductID = duplicateMySQLRecord ("product", intval($_GET['duplicate_productID']));
	$update = "UPDATE product SET sku = NULL, title = CONCAT(title, ' (DUPLICATE)'), createdbyID = ".$row_rsLoggedIn['ID'].", datetimecreated = '".date('Y-m-d H:i:s')."' WHERE ID=".$newproductID;
	$result = mysql_query($update, $aquiescedb) or die(mysql_error());
	// update linking tables - just category, options now, but maybe add versions tags etc later
	$select = "SELECT categoryID FROM productincategory  WHERE productID = ".intval($_GET['duplicate_productID']);
	$result = mysql_query($select, $aquiescedb) or die(mysql_error());
	if(mysql_num_rows($result)>0) {
		while($row = mysql_fetch_assoc($result)) {
			$insert = "INSERT INTO productincategory (productID, categoryID, createdbyID, createddatetime) VALUES (".$newproductID.",".$row['categoryID'].", ".$row_rsLoggedIn['ID'].", '".date('Y-m-d H:i:s')."')";
			mysql_query($insert, $aquiescedb) or die(mysql_error());
		}
	}
	
	$select = "SELECT regionID FROM productinregion  WHERE productID = ".intval($_GET['duplicate_productID']);
	$result = mysql_query($select, $aquiescedb) or die(mysql_error());
	if(mysql_num_rows($result)>0) {
		while($row = mysql_fetch_assoc($result)) {
			$insert = "INSERT INTO productinregion (productID, regionID, createdbyID, createddatetime) VALUES (".$newproductID.",".$row['regionID'].", ".$row_rsLoggedIn['ID'].", '".date('Y-m-d H:i:s')."')";
			mysql_query($insert, $aquiescedb) or die(mysql_error());
		}
	}
	
	$select = "SELECT ID FROM productoptions  WHERE productID = ".intval($_GET['duplicate_productID']);
	$result = mysql_query($select, $aquiescedb) or die(mysql_error());
	if(mysql_num_rows($result)>0) {
		while($row = mysql_fetch_assoc($result)) {
			$newoptionID = duplicateMySQLRecord ("productoptions", $row['ID']);
			$update = "UPDATE productoptions SET productID = ".$newproductID.",  createdbyID = ".$row_rsLoggedIn['ID'].", createddatetime = '".date('Y-m-d H:i:s')."'  WHERE ID = ".$newoptionID;
			mysql_query($update, $aquiescedb) or die(mysql_error());
		}
	}
	
	// add any product tagged
	  $select2 = "SELECT * FROM producttagged WHERE productID = ".intval($_GET['duplicate_productID']);
	  $result2 = mysql_query($select2, $aquiescedb) or die(mysql_error());
	  if(mysql_num_rows($result2)>0) {
		  while($row2 = mysql_fetch_assoc($result2)) {
			  $insert = "INSERT INTO producttagged (productID, tagID, createdbyID, createddatetime) VALUES(".$newproductID.",".$row2['tagID'].",0, '".date('Y-m-d H:i:s')."')";
			  mysql_query($insert, $aquiescedb) or die(mysql_error().": ".$insert);
		  }
	  }	
	header("location: modify_product.php?productID=".intval($newproductID)); exit;
}


//clean
$_GET['sortby'] = (isset($_GET['sortby']) && $_GET['sortby'] !="") ? str_replace("'", "",$_GET['sortby']): "product.datetimecreated DESC";
$maxproducts = (isset($_GET['categoryID']) && intval($_GET['categoryID'])>0) ? 5000 : 100;
// set userID to filter products by if Agent
$userID = ($_SESSION['MM_UserGroup'] ==6) ?  $row_rsLoggedIn['ID'] : 0;
$search = isset($_GET['search']) ? str_replace(" ","%",trim($_GET['search'])) : "%";

$currentPage = $_SERVER["PHP_SELF"];


$maxRows_rsProducts = $maxproducts;
$pageNum_rsProducts = 0;
if (isset($_GET['pageNum_rsProducts'])) {
  $pageNum_rsProducts = $_GET['pageNum_rsProducts'];
}
$startRow_rsProducts = $pageNum_rsProducts * $maxRows_rsProducts;

$varCategoryID_rsProducts = "0";
if (isset($_GET['categoryID'])) {
  $varCategoryID_rsProducts = $_GET['categoryID'];
}
$varShowSub_rsProducts = "0";
if (isset($_GET['showsub'])) {
  $varShowSub_rsProducts = $_GET['showsub'];
}
$varSupplierID_rsProducts = "0";
if (isset($_GET['supplierID'])) {
  $varSupplierID_rsProducts = $_GET['supplierID'];
}
$varUserID_rsProducts = "0";
if (isset($userID)) {
  $varUserID_rsProducts = $userID;
}
$varManufacturerID_rsProducts = "0";
if (isset($_GET['manufacturerID'])) {
  $varManufacturerID_rsProducts = $_GET['manufacturerID'];
}
$varShowInactive_rsProducts = "0";
if (isset($_GET['inactive'])) {
  $varShowInactive_rsProducts = $_GET['inactive'];
}
$varRegionID_rsProducts = "1";
if (isset($regionID)) {
  $varRegionID_rsProducts = $regionID;
}
$varSearch_rsProducts = "%";
if (isset($search)) {
  $varSearch_rsProducts = $search;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsProducts = sprintf("SELECT product.ID, product.longID, productcategory.longID AS categorylongID, product.title, product.area, product.imageURL, product.statusID, productcategory.title AS category, product.ordernum, product.instock, product.productcategoryID, product.price, product.pricetype, product.relatedall, product.featured, product.saleitem, product.hazardous, product.sku, product.mpn, product.upc, product.manufacturerID, productcategory.gbasecat, productcategory.longID AS categorylongID, COUNT(DISTINCT(productoptions.ID)) AS numoptions,  SUM(productoptions.instock) AS optionsinstock, product.auction, COUNT(productbid.ID) AS bids, directory.name AS supplier FROM product LEFT JOIN productcategory ON (product.productcategoryID = productcategory.ID) LEFT JOIN productinregion ON (productinregion.productID = product.ID) LEFT JOIN productincategory ON (productincategory.productID = product.ID) LEFT JOIN productoptions ON (productoptions.productID = product.ID) LEFT JOIN productbid ON (product.ID = productbid.productID)  LEFT JOIN directoryuser ON (directoryuser.directoryID = productcategory.directoryID) LEFT JOIN directory ON supplierdirectoryID = directory.ID WHERE (product.statusID <2  OR %s =1) AND (%s = 0 OR product.productcategoryID = %s OR productincategory.categoryID = %s OR (%s=1 AND product.productcategoryID = productcategory.subCatOfID)) AND (product.title LIKE REPLACE(%s, ' ' ,'') OR product.sku LIKE REPLACE(%s, ' ' ,'') OR product.mpn LIKE REPLACE(%s, ' ' ,'') OR product.upc LIKE REPLACE(%s, ' ' ,''))  AND ((productinregion.regionID IS NULL AND %s = 1) OR (productinregion.regionID = ".intval($regionID).")) AND (%s = 0 OR product.manufacturerID = %s) AND (%s = 0 OR directoryuser.userID = %s) AND (%s = 0 OR product.supplierdirectoryID = %s) GROUP BY product.ID ORDER BY " . $_GET['sortby'] . "", GetSQLValueString($varShowInactive_rsProducts, "int"),GetSQLValueString($varCategoryID_rsProducts, "int"),GetSQLValueString($varCategoryID_rsProducts, "int"),GetSQLValueString($varCategoryID_rsProducts, "int"),GetSQLValueString($varShowSub_rsProducts, "int"),GetSQLValueString("%" . $varSearch_rsProducts . "%", "text"),GetSQLValueString("%" . $varSearch_rsProducts . "%", "text"),GetSQLValueString("%" . $varSearch_rsProducts . "%", "text"),GetSQLValueString("%" . $varSearch_rsProducts . "%", "text"),GetSQLValueString($varRegionID_rsProducts, "int"),GetSQLValueString($varManufacturerID_rsProducts, "int"),GetSQLValueString($varManufacturerID_rsProducts, "int"),GetSQLValueString($varUserID_rsProducts, "int"),GetSQLValueString($varUserID_rsProducts, "int"),GetSQLValueString($varSupplierID_rsProducts, "int"),GetSQLValueString($varSupplierID_rsProducts, "int"));
$query_limit_rsProducts = sprintf("%s LIMIT %d, %d", $query_rsProducts, $startRow_rsProducts, $maxRows_rsProducts);
$rsProducts = mysql_query($query_limit_rsProducts, $aquiescedb) or die(mysql_error());
$row_rsProducts = mysql_fetch_assoc($rsProducts);

if (isset($_GET['totalRows_rsProducts'])) {
  $totalRows_rsProducts = $_GET['totalRows_rsProducts'];
} else {
  $all_rsProducts = mysql_query($query_rsProducts);
  $totalRows_rsProducts = mysql_num_rows($all_rsProducts);
}
$totalPages_rsProducts = ceil($totalRows_rsProducts/$maxRows_rsProducts)-1;

$varRegionID_rsProductCategories = "1";
if (isset($regionID)) {
  $varRegionID_rsProductCategories = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsProductCategories = sprintf("SELECT productcategory.ID, productcategory.title, parent.title AS parent FROM productcategory LEFT JOIN productcategory AS parent ON (productcategory.subcatofID  = parent.ID) WHERE productcategory.statusID = 1 AND productcategory.regionID = %s ORDER BY parent.title, productcategory.title ASC", GetSQLValueString($varRegionID_rsProductCategories, "int"));
$rsProductCategories = mysql_query($query_rsProductCategories, $aquiescedb) or die(mysql_error());
$row_rsProductCategories = mysql_fetch_assoc($rsProductCategories);
$totalRows_rsProductCategories = mysql_num_rows($rsProductCategories);


mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsProductPrefs = "SELECT * FROM productprefs";
$rsProductPrefs = mysql_query($query_rsProductPrefs, $aquiescedb) or die(mysql_error());
$row_rsProductPrefs = mysql_fetch_assoc($rsProductPrefs);
$totalRows_rsProductPrefs = mysql_num_rows($rsProductPrefs);


mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsManufacturers = "SELECT * FROM productmanufacturer WHERE statusID = 1 ORDER BY manufacturername ASC";
$rsManufacturers = mysql_query($query_rsManufacturers, $aquiescedb) or die(mysql_error());
$row_rsManufacturers = mysql_fetch_assoc($rsManufacturers);
$totalRows_rsManufacturers = mysql_num_rows($rsManufacturers);

$varRegionID_rsSuppliers = "1";
if (isset($regionID)) {
  $varRegionID_rsSuppliers = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsSuppliers = sprintf("SELECT directory.ID, directory .name FROM directory LEFT JOIN directorycategory ON (directory.categoryID= directorycategory.ID) WHERE directory.statusID = 1 AND directorycategory.statusID = 1 AND (directorycategory.regionID = 0 OR directorycategory.regionID  = %s) ORDER BY name ASC", GetSQLValueString($varRegionID_rsSuppliers, "int"));
$rsSuppliers = mysql_query($query_rsSuppliers, $aquiescedb) or die(mysql_error());
$row_rsSuppliers = mysql_fetch_assoc($rsSuppliers);
$totalRows_rsSuppliers = mysql_num_rows($rsSuppliers);

$queryString_rsProducts = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsProducts") == false && 
        stristr($param, "totalRows_rsProducts") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsProducts = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsProducts = sprintf("&totalRows_rsProducts=%d%s", $totalRows_rsProducts, $queryString_rsProducts);


?>
<!doctype html>

<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Manage Products"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<link href="../../css/defaultProducts.css" rel="stylesheet"  />
<script src="/core/scripts/checkbox/checkboxes.js"></script>
<?php //require_once('../../../core/scripts/checkbox/checkboxsession.inc.php'); ?>
<script> 
    // When the document is ready set up our sortable with it's inherant function(s) 
    $(document).ready(function() { 
	<?php if(isset($_GET['sortby']) && isset($_GET['categoryID']) && $_GET['sortby'] == "product.ordernum" && $_GET['categoryID']>0) { $draganddrop = true;?>
        $(".sortable").sortable({ 
            handle : '.handle', 
            update : function () { 
            var order = $(this).sortable('serialize'); 
                $("#info").load("/core/ajax/sort.ajax.php?table=product&"+order); 
            } 
        }); 
		<?php } ?>
		
		
    }); 
	
function getRegionCategories(regionID) {
	url = "ajax/categoryselect.ajax.php?regionID="+regionID;
	getData(url,"categoryselect");
}
</script>
<style>
<!--
.table .auction span {
	background-color: rgb(204,0,0);
	color: rgb(255,255,255);
	border-radius: 50%;
	display: inline-block;
	text-align: center;
	min-width: 20px;
	padding: 2px 0;
}
<?php if(!isset($draganddrop)) {
echo ".draganddrop { display:none !important; }";
}
?> <?php if($totalRegions<2) {
echo ".region { display:none; } ";
}
 if($totalRows_rsManufacturers<1) {
 echo ".manufacturer { display:none !important; } ";
}
if($_SESSION['MM_UserGroup']<8) {
 echo ".rank8 { display:none; } ";
}
 if($row_rsProductPrefs['auctions']!=1) {
 echo ".auction { display: none; }";
}
 if(!isset($_GET['showsupplier'])) {
 echo ".supplier { display: none !important; }";
}
 ?>
-->
</style>
<?php if(isset($body_class)) $body_class .= " products ";  ?>
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" -->
    <div id="pageAdminManageProducts">
      <?php require_once('../../../core/region/includes/chooseregion.inc.php'); ?>
      <h1><i class="glyphicon glyphicon-shopping-cart"></i> Manage Products</h1>
      <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
          <ul class="nav navbar-nav">
            <li class="nav-item rank8"><a href="/products/admin/"  class="nav-link"><i class="glyphicon glyphicon-arrow-left"></i> Manage Shop</a></li>
            <li class="nav-item"><a href="add_product.php<?php echo isset($_GET['categoryID']) ? "?categoryID=".intval($_GET['categoryID']) : ""; ?>"  class="nav-link"><i class="glyphicon glyphicon-plus-sign"></i> Add product</a></li>
            <li  class="nav-item rank8"><a href="categories/index.php" class="nav-link"><i class="glyphicon glyphicon-tags"></i> Categories &amp; Tags</a></li>
            <li class="nav-item"><a href="../google/googlebase.php"  class="nav-link"><img src="/core/images/icons/google_favicon.png" width="16" height="16" alt="Google"> Merchant Center</a></li>
            <li  class="nav-item rank8"><a href="import_products.php" class="nav-link"><i class="glyphicon glyphicon-cloud-upload"></i> Import</a></li>
            <li  class="nav-item rank8"><a href="allproducts.php" class="nav-link"><i class="glyphicon glyphicon-cloud-download"></i> Export</a></li>
            <li  class="nav-item rank8"><a href="search/index.php" class="nav-link"><i class="glyphicon glyphicon-search"></i> Search</a></li>
            <li  class="nav-item rank8"><a href="options/index.php" class="nav-link"><i class="glyphicon glyphicon-cog"></i> Options</a></li>
            <li class="nav-item"><a href="/products/" target="_blank" class="nav-link" rel="noopener"  onClick="openMainWindow('/products/'); return false;"><i class="glyphicon glyphicon-new-window"></i> Go to Shop</a></li>
          </ul>
        </div>
      </nav>
      <?php require_once('../../../core/includes/alert.inc.php'); ?>
      <form action="index.php" id="searchform" class="form-inline">
        <fieldset>
          <legend>Filter</legend>
          <label>Search for products:
            <input name="search" type="text"  id="search" value="<?php echo isset($_GET['search']) ? htmlentities(trim($_GET['search']), ENT_COMPAT, "UTF-8") : ""; ?>" size="50" maxlength="50" class="form-control" />
          </label>
          <button type="submit" class="btn btn-default btn-secondary" >Go</button>
          <label>
            <input <?php if (!(strcmp(@$_GET['inactive'],1))) {echo "checked=\"checked\"";} ?> name="inactive" type="checkbox" id="inactive" value="1" onClick="this.form.submit();" />
            show deleted</label>
          &nbsp;&nbsp;&nbsp;
          
          <label>
            <input <?php if (!(strcmp(@$_GET['showsupplier'],1))) {echo "checked=\"checked\"";} ?> name="showsupplier" type="checkbox" id="showsupplier" value="1" onClick="this.form.submit();"  />
            show supplier</label>
          <br />
          <select name="categoryID" id="categoryID" onChange="this.form.submit()" class="form-control">
            <option value="0" <?php if (!(strcmp(0, @$_GET['categoryID']))) {echo "selected=\"selected\"";} ?>>Any category</option>
            <?php
do {  
?>
            <option value="<?php echo $row_rsProductCategories['ID']?>"<?php if (!(strcmp($row_rsProductCategories['ID'], @$_GET['categoryID']))) {echo "selected=\"selected\"";} ?>><?php echo isset($row_rsProductCategories['parent']) ? $row_rsProductCategories['parent']." > " : ""; echo $row_rsProductCategories['title']?></option>
            <?php
} while ($row_rsProductCategories = mysql_fetch_assoc($rsProductCategories));
  $rows = mysql_num_rows($rsProductCategories);
  if($rows > 0) {
      mysql_data_seek($rsProductCategories, 0);
	  $row_rsProductCategories = mysql_fetch_assoc($rsProductCategories);
  }
?>
          </select>
          <label>
            <input type="checkbox" name="showsub" id="showsub" <?php echo isset($_GET['showsub']) ? " checked ": ""; ?> value="1" onclick="this.form.submit()">
            Include sub categories</label>
          <select name="manufacturerID" class="manufacturer form-control"  onChange="this.form.submit()"  >
            <option value="0" <?php if (!(strcmp(0, @$_GET['manufacturerID']))) {echo "selected=\"selected\"";} ?>>All manufacturers</option>
            <?php $rows = mysql_num_rows($rsManufacturers);
  if($rows > 0) {
do {  
?>
            <option value="<?php echo $row_rsManufacturers['ID']?>"<?php if (!(strcmp($row_rsManufacturers['ID'], @$_GET['manufacturerID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsManufacturers['manufacturername']?></option>
            <?php
} while ($row_rsManufacturers = mysql_fetch_assoc($rsManufacturers));
 
      mysql_data_seek($rsManufacturers, 0);
	  $row_rsManufacturers = mysql_fetch_assoc($rsManufacturers);
  }
?>
          </select>
          <label>Sort by:
            <select name="sortby" id="sortby" onChange="this.form.submit()"  class="form-control">
              <option value="" <?php if (!(strcmp("", @$_GET['sortby']))) {echo "selected=\"selected\"";} ?>>Recently added</option>
              <option value="product.title" <?php if (!(strcmp("product.title",@$_GET['sortby']))) {echo "selected=\"selected\"";} ?>>Product name</option>
              <option value="product.ordernum" <?php if (!(strcmp("product.ordernum",@$_GET['sortby']))) {echo "selected=\"selected\"";} ?>>Page order</option>
              <option value="product.sku" <?php if (!(strcmp("product.sku", @$_GET['sortby']))) {echo "selected=\"selected\"";} ?>>Stock code</option>
              <option value="product.price" <?php if (!(strcmp("product.price", @$_GET['sortby']))) {echo "selected=\"selected\"";} ?>>Price</option>
              <option value="product.relatedall DESC" <?php if (!(strcmp("product.relatedall DESC", @$_GET['sortby']))) {echo "selected=\"selected\"";} ?>>Related products</option>
            </select>
          </label>
        </fieldset>
      </form>
      <?php if ($totalRows_rsProducts == 0) { // Show if recordset empty ?>
        <p>There are no products matching your criteria</p>
        <?php } // Show if recordset empty ?>
      <?php if ($totalRows_rsProducts > 0) { // Show if recordset not empty ?>
      <p class="text-muted">Products <?php echo ($startRow_rsProducts + 1) ?> to <?php echo min($startRow_rsProducts + $maxRows_rsProducts, $totalRows_rsProducts) ?> of <?php echo $totalRows_rsProducts ?> (<span id="checkedCount"></span> selected) <span id="info">Drag and drop items to sort when filtered by category and page order</span></p>
      <form action="index.php" method="post" name="form1" id="form1"class="form-inline">
        <table class="table table-hover">
          <thead>
            <tr>
              <th class="draganddrop">&nbsp;</th>
              <th><input type="checkbox" name="checkAll" id="checkAll" onclick="checkUncheckAll(this);" /></th>
              <th colspan="10"><label for="checkAll"><em>Select all</em></label></th>
              <th>&nbsp;</th>
              <th>Name</th>
              <th>Category</th>
              <th class="text-right">Price</th>
              <th class="supplier">Supplier</th>
              <th class="auction">Bids</th>
              <th >Tools</th>
              
            </tr>
             
            
          <tbody class="sortable">
            <?php do { ?>
              <tr  id="listItem_<?php echo $row_rsProducts['ID']; ?>" >
                <td class= "handle draganddrop">&nbsp;</td>
                <td><input type="checkbox" name="checkbox[<?php echo $row_rsProducts['ID']; ?>]" value="<?php echo $row_rsProducts['ID']; ?>" id="checkbox<?php echo $row_rsProducts['ID']; ?>" /></td>
                <td class="status<?php echo $row_rsProducts['statusID']; ?>" data-toggle="tooltip" title="This is the product status. Green = live on site. Amber = Draft (not live on site). Red = Do not display">&nbsp;</td>
                <td class="text-right"><?php if($row_rsProducts['instock']==0) { ?>
                  <img src="/core/images/icons/cross.png" alt="Out of stock" width="16" height="16" style="vertical-align:
middle;" data-toggle="tooltip" title="This product is displayed as out of stock" />
                  <?php }; ?></td>
                <td class="text-right"><?php if($row_rsProducts['featured']==1) { ?>
                  <img src="/core/images/icons/star.png" alt="Featured Product" width="16" height="16" style="vertical-align:
middle;" data-toggle="tooltip" title="This product is flagged as featured and will show in any areas of the shop that display featured products" />
                  <?php } ?></td>
                <td class="text-right"><?php if($row_rsProducts['saleitem']==1) { ?>
                  <img src="/core/images/icons/flag_red.png" alt="Sale item" width="16" height="16" style="vertical-align:
middle;" data-toggle="tooltip" title="This product is displayed as on sale" />
                  <?php } ?></td>
                <td class="text-right"><?php if($row_rsProducts['relatedall']==1) { ?>
                  <img src="/core/images/icons/emblem-favorite.png" alt="Related item" width="16" height="16" style="vertical-align:
middle;" data-toggle="tooltip" title="This product will show as related to any other product in the shop" />
                  <?php } ?></td>
                <td class="text-right"><?php if($row_rsProducts['hazardous']==1) { ?>
                  <img src="../../images/icons/hazardous.png" alt="Hazardous" width="16" height="16" style="vertical-align:
middle;" data-toggle="tooltip" title="This product is flagged as hazardous. Special shipping options can be set for these products" />
                  <?php } ?></td>
                <td class="text-right"><?php if(isset($row_rsProducts['gbasecat']) && isset($row_rsProducts['manufacturerID']) && isset($row_rsProducts['upc'])) { ?>
                  <img src="../../../core/images/icons/google_favicon.png" alt="Google Shopping compatible" width="16" height="16" style="vertical-align:
middle;" data-toggle="tooltip" title="This product has all the correct criteria ready to be uploaded to Google Merchant Center" />
                  <?php } ?></td>
                <td class="text-right" data-toggle="tooltip" title="This is the number of this product in stock">(<?php echo $row_rsProducts['optionsinstock']>0 ? $row_rsProducts['optionsinstock'] : $row_rsProducts['instock']; ?>)</td>
                <td><span class="fb_avatar" style="background-image:url(<?php echo getImageURL($row_rsProducts['imageURL'],"thumb"); ?>); width:32px; height:32px; vertical-align:
middle;">&nbsp;</span></td>
                <td><a href="modify_product.php?productID=<?php echo $row_rsProducts['ID']; ?>" data-toggle="tooltip" title="This is the product SKU (stock number)"><?php echo $row_rsProducts['sku']; ?></a></td>
                <td data-toggle="tooltip" title="This is the number buying options for this prooduct"><?php echo ($row_rsProducts['numoptions']>0) ? "(".$row_rsProducts['numoptions'].")" : ""; ?></td>
                <td><a href="modify_product.php?productID=<?php echo $row_rsProducts['ID']; ?>"><?php echo $row_rsProducts['title']; ?></a></td>
                <td data-toggle="tooltip" title="This is the product category (also showing parent category if any)"><em><?php echo $row_rsProducts['category']; ?></em></td>
                <td class="text-right"><?php echo number_format($row_rsProducts['price'],2,".",","); echo (isset($row_rsProducts['area']) && floatval($row_rsProducts['area'])>0) ? " (".number_format(($row_rsProducts['price']/$row_rsProducts['area']),2,".",",")."m2)" : ""; ?>
                  <?php switch($row_rsProducts['pricetype']) {
					case 1 : break;
					case 2 : echo " per kg"; break;
					case 3 : echo " per hour"; break;
					case 4 : echo " per day"; break;
				} ?></td>
                <td class="supplier" data-toggle="tooltip" title="This is the product supplier"><?php echo $row_rsProducts['supplier']; ?></td>
                <td class="auction"><?php if( $row_rsProducts['auction']>0) {  ?>
                  <span title="<?php echo $row_rsProducts['bids']; ?> bids" ><?php echo $row_rsProducts['bids']; ?></span>
                  <?php } ?></td>
                <td nowrap>
                
                
                
                 <div class="btn-group" role="group" >
                  
                  <a href="modify_product.php?productID=<?php echo $row_rsProducts['ID']; ?>&returnURL=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="btn btn-sm btn-default btn-secondary" data-toggle="tooltip" title="Click to edit product"><i class="glyphicon glyphicon-pencil"></i></a>
                  
                  
                  
                  
                  <!-- Single button -->
<div class="btn-group">
  <button type="button" class="btn btn-sm btn-default btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
    <i class="glyphicon glyphicon-cog"></i> Actions <span class="caret"></span>
  </button>
  <ul class="dropdown-menu dropdown-menu-right">
 <li><a href="index.php?duplicate_productID=<?php echo $row_rsProducts['ID']; ?>"  onClick="return confirm('Do you want to duplicate this product?');" data-toggle="tooltip" title="Click here to create a clone of this product"><i class="glyphicon glyphicon-duplicate"></i> Duplicate</a></li>
 <li><a href="<?php echo productLink($row_rsProducts['ID'], $row_rsProducts['longID'], $row_rsProducts['productcategoryID'],$row_rsProducts['categorylongID'],0,"",0,"preview=true"); ?>" title="View in shop"  target="_blank" rel="noopener" data-toggle="tooltip" ><i class="glyphicon glyphicon-new-window"></i> View</a></li>
  </ul>
</div><!-- end button group-->
 </div><!-- end btn-group-->
                  
                  </td>
                
               
              </tr>
              <?php } while ($row_rsProducts = mysql_fetch_assoc($rsProducts)); ?>
          </tbody>
        </table>
        <?php } // Show if recordset not empty ?>
        <?php echo createPagination($pageNum_rsProducts,$totalPages_rsProducts,"rsProducts");?>
        <fieldset>
          <legend> With selected:</legend>
         <a href="javascript:void(0);" onClick="if(confirm('All checked items will be removed from shop display. Continue?')) { document.getElementById('formAction').value='pending'; document.getElementById('form1').submit(); }">Make Inactive</a> | <a href="javascript:void(0);" onClick="if(confirm('All checked items will be removed from shop display. Continue?')) { document.getElementById('formAction').value='delete'; document.getElementById('form1').submit(); }">Delete</a> | <a href="javascript:void(0);" onClick="if(confirm('All checked items will be merged into one product with options')) { document.getElementById('formAction').value='mergeOptions'; document.getElementById('form1').submit(); }">Merge into options</a> | <a href="javascript:void(0);" onClick="if(confirm('All checked items will be appear in each product’s related items')) { document.getElementById('formAction').value='makeRelated'; document.getElementById('form1').submit(); }">Make related</a> | <a href="javascript:void(0);" onClick="if(confirm('Are you sure you want to remove all related products from the selected items? NOTE: The selected items will also be removed from their respective related products.')) { document.getElementById('formAction').value='deleteRelated'; document.getElementById('form1').submit(); }">Delete related</a>
          
          
          
          
          
          <br>
          <select name="addtocategoryID" id="addtocategoryID" class="form-control">
            <option value="">Add to category...</option>
            <?php
do {  
?>
            <option value="<?php echo $row_rsProductCategories['ID']?>"><?php echo $row_rsProductCategories['title']?></option>
            <?php
} while ($row_rsProductCategories = mysql_fetch_assoc($rsProductCategories));
  $rows = mysql_num_rows($rsProductCategories);
  if($rows > 0) {
      mysql_data_seek($rsProductCategories, 0);
	  $row_rsProductCategories = mysql_fetch_assoc($rsProductCategories);
  }
?>
          </select>
          OR
          <select name="movetocategoryID" id="movetocategoryID"  class="form-control">
            <option value="">Move to category...</option>
            <?php
do {  
?>
            <option value="<?php echo $row_rsProductCategories['ID']?>"><?php echo $row_rsProductCategories['title']?></option>
            <?php
} while ($row_rsProductCategories = mysql_fetch_assoc($rsProductCategories));
  $rows = mysql_num_rows($rsProductCategories);
  if($rows > 0) {
      mysql_data_seek($rsProductCategories, 0);
	  $row_rsProductCategories = mysql_fetch_assoc($rsProductCategories);
  }
?>
          </select>
          <?php   if( mysql_num_rows($rsSuppliers) > 0) { ?>
          OR
          <select name="setsupplierID"  class="form-control" >
            <option value="" >Set supplier...</option>
            <?php do {  ?>
            <option value="<?php echo $row_rsSuppliers['ID']?>"><?php echo $row_rsSuppliers['name']?></option>
            <?php
} while ($row_rsSuppliers = mysql_fetch_assoc($rsSuppliers));
 
      mysql_data_seek($rsSuppliers, 0);
	  $row_rsSuppliers = mysql_fetch_assoc($rsSuppliers);
 
?>
          </select>
          <?php } ?>
          <span class="region">OR
          <select name="copytoregionID" onChange="getRegionCategories(this.value)"  class="form-control">
            <option value="0">Copy to site...</option>
            <?php mysql_data_seek($rsAdminRegions,0); while($copyregion = mysql_fetch_assoc($rsAdminRegions)) { if($copyregion['ID']!=$regionID) { ?>
            <option value="<?php echo $copyregion['ID']; ?>" ><?php echo $copyregion['title']; ?></option>
            <?php }} ?>
          </select>
          <span id="categoryselect"></span></span>
          <button type="submit" name="gobutton" id="gobutton" class="btn btn-default btn-secondary" >Go</button>
          <input name="createdbyID" type="hidden" value="<?php echo $row_rsLoggedIn['ID']; ?>">
          <input name="formAction" id="formAction" type="hidden" />
        </fieldset>
      </form>
    </div>
    <?php writeGoogleFeedFile(); ?>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsProducts);

mysql_free_result($rsProductCategories);

mysql_free_result($rsProductPrefs);

mysql_free_result($rsLoggedIn);

mysql_free_result($rsManufacturers);

mysql_free_result($rsSuppliers);


?>