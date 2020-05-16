<?php require_once('../../../Connections/aquiescedb.php');?>
<?php require_once('../../../core/includes/adminAccess.inc.php'); ?>
<?php require_once('../../../core/includes/framework.inc.php'); ?>
<?php require_once('../../includes/productHeader.inc.php'); ?>
<?php require_once('../../../core/includes/upload.inc.php'); ?>
<?php require_once('../../../mail/includes/sendmail.inc.php'); ?>
<?php require_once('../../includes/productFunctions.inc.php'); ?>
<?php require_once('../inc/product_functions.inc.php'); ?>
<?php require_once('../../../documents/includes/documentfunctions.inc.php'); ?><?php


if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "8,9,10";
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

$currentPage = $_SERVER["PHP_SELF"];

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

mysql_select_db($database_aquiescedb, $aquiescedb);


// delete any product tags from other sites
$delete = "DELETE producttagged FROM producttagged LEFT JOIN producttag ON (producttag.ID = producttagged.tagID) 
LEFT JOIN producttaggroup ON (producttaggroup.ID = producttag.taggroupID) WHERE producttagged.productID = ".intval($_GET['productID'])." AND producttaggroup.regionID !=0 AND producttaggroup.regionID !=".$regionID;

mysql_query($delete, $aquiescedb) or die(mysql_error());



$varUsername_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $varUsername_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT users.ID, users.usertypeID, users.regionID FROM users WHERE users.username = %s", GetSQLValueString($varUsername_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);



if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {  // if post
	$_POST['longID'] = createURLname($_POST['longID'], $_POST['title'], "-",  "product", $_POST['ID']);
	
	if(isset($_POST['options-increase']) && $_POST['old-price'] != $_POST['price'] && floatval($_POST['price'])>0) {
		$percentchange = (floatval($_POST['price']) - floatval($_POST['old-price'])) / floatval($_POST['old-price']) ;
		$update = "UPDATE productoptions SET price = price + (price * ".$percentchange.") WHERE productID = ".intval($_POST['ID']);
		mysql_query($update, $aquiescedb) or die(mysql_error());
	}
	
	if(isset($_POST['x']) && intval($_POST['x'])>0 && isset($_POST['ratio']) && floatval($_POST['ratio'])>0) { // crop
		$filename = UPLOAD_ROOT.$_POST['imageURL'];
		$original = UPLOAD_ROOT."o_".$_POST['imageURL'];
		if(!is_readable($original)) { 
			copy($filename, $original);
		}
		$ratio  = floatval($_POST['ratio']);
		$w = $ratio * intval($_POST['w']);
		$h = $ratio * intval($_POST['h']);
		$x = $ratio * intval($_POST['x']);
		$y = $ratio * intval($_POST['y']);
		
		$size = $w."x".$h.":".$x.":".$y;
		
		$result = Image($filename, "crop", $size, $filename);
		createImageSizes($filename);
	} else { // no crop
		$_POST['imageURL'] = (isset($_POST["noImage"])) ? "" : $_POST['imageURL'];
		
		$uploaded = getUploads();
		if (isset($uploaded) && is_array($uploaded)) {
			if(isset($uploaded["filename"][0]["newname"]) 
				&& $uploaded["filename"][0]["newname"]!="") { 
				$_POST['imageURL'] = $uploaded["filename"][0]["newname"]; 
			}			
		}
	} // no crop
	
	
	if(isset($uploaded['galleryfilename'][0]['newname']) 
		&& $uploaded['galleryfilename'][0]['newname'] !="") { // if gallery file posted			
			addProductGalleryPhoto($_POST['ID'], $uploaded['galleryfilename'][0]['newname'], $row_rsLoggedIn['ID']);
	} else if(isset($_POST['mediaURL']) && trim($_POST['mediaURL'])!="") { 
		addProductGalleryPhoto($_POST['ID'], $_POST['mediaURL'], $row_rsLoggedIn['ID']);
	}
	
	
	// check main category is not in other categories - if so remove
	$delete = "DELETE FROM productincategory WHERE categoryID = ".GetSQLValueString($_POST['productcategoryID'], "int")." AND productID = ". GetSQLValueString($_POST['ID'], "int");
	mysql_query($delete, $aquiescedb) or die(mysql_error());
	
	// check if out of stock changed to in stock
	if($_POST["oldinstock"]<1 && $_POST["instock"]>0) {
		createNotificationGroupEmail(intval($_POST['ID']), $_POST['title']);
	}
	
	if(isset($_POST['oldtag'])) {
		foreach($_POST['oldtag'] as $key => $value) {
			if($value=="" && isset($_POST['tag'][$key])) {
				$insert = "INSERT into producttagged (productID, tagID, createdbyID, createddatetime) VALUES (".GetSQLValueString($_POST['ID'], "int").",".intval($key).",". GetSQLValueString($_POST['modifiedbyID'], "int").",'".date('Y-m-d H:i:s')."')";
				mysql_query($insert, $aquiescedb) or die(mysql_error());
			} else if($value !="" && !isset($_POST['tag'][$key])) {
				$delete = "DELETE FROM producttagged WHERE productID= ".GetSQLValueString($_POST['ID'], "int")." AND tagID=".intval($key);
				mysql_query($delete, $aquiescedb) or die(mysql_error());
			}
			if(isset($_POST['tagstokeywords']) && isset($_POST['tag'][$key]) && strpos($_POST['metakeywords'], $_POST['tag'][$key]) === false) { // if not in meta ketwords add
				$_POST['metakeywords'] .= (strlen($_POST['metakeywords']) >0)  ? ", ".$_POST['tag'][$key] : $_POST['tag'][$key];
			}
		}
		
	}
	
	foreach($_POST['wasinregion'] as $regionID=>$value) {
		if($value!="" && !isset($_POST['productinregion'][$regionID])) {
			$delete = "DELETE FROM productinregion WHERE ID = ".$value;
			mysql_query($delete, $aquiescedb) or die(mysql_error());
		} else if($value == "" && isset($_POST['productinregion'][$regionID])) {
			$insert = "INSERT INTO productinregion (productID, regionID, createdbyID, createddatetime) VALUES (".GetSQLValueString($_POST['ID'], "int").",".$regionID.",".GetSQLValueString($_POST['modifiedbyID'], "int").",'".date('Y-m-d H:i:s')."')";
			mysql_query($insert, $aquiescedb) or die(mysql_error());
		}
	}
	
	$_POST['auctionenddatetime'] = isset($_POST['auction'] ) ? $_POST['auctionenddatetime'] : ""; // only insert or update if auction set

 
}// end post


if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE product SET longID=%s, productcategoryID=%s, metadescription=%s, metakeywords=%s, title=%s, sku=%s, isbn=%s, mpn=%s, upc=%s, `description`=%s, price=%s, pricetype=%s, listprice=%s, shippingexempt=%s, weight=%s, box_length=%s, box_height=%s, box_width=%s, hazardous=%s, manufacturerID=%s, imageURL=%s, inputfield=%s, instock=%s, featured=%s, saleitem=%s, vattype=%s, statusID=%s, modifiedbyID=%s, modifieddatetime=%s, addfrom=%s, int_length=%s, int_height=%s, int_width=%s, capacity=%s, fileupload=%s, nocommondetails=%s, relatedall=%s, priceper=%s, shippingrateID=%s, area=%s, noshipinternational=%s, altimage=%s, custompageURL=%s, seotitle=%s, videoembed=%s, showareaprice=%s, redirect301=%s, h2=%s, inputfield2=%s, auction=%s, auctionenddatetime=%s, inputfield3=%s, startingbid=%s, showrrp=%s, auctionsellafter=%s, `class`=%s, `condition`=%s, costprice=%s, supplierdirectoryID=%s, googleID=%s, `availabledate`=%s, deliveryperiod=%s, mindeliverytime=%s, maxdeliverytime=%s WHERE ID=%s",
                       GetSQLValueString($_POST['longID'], "text"),
                       GetSQLValueString($_POST['productcategoryID'], "int"),
                       GetSQLValueString($_POST['metadescription'], "text"),
                       GetSQLValueString($_POST['metakeywords'], "text"),
                       GetSQLValueString($_POST['title'], "text"),
                       GetSQLValueString($_POST['sku'], "text"),
                       GetSQLValueString($_POST['isbn'], "text"),
                       GetSQLValueString($_POST['mpn'], "text"),
                       GetSQLValueString($_POST['upc'], "text"),
                       GetSQLValueString($_POST['description'], "text"),
                       GetSQLValueString($_POST['price'], "double"),
                       GetSQLValueString($_POST['pricetype'], "int"),
                       GetSQLValueString($_POST['listprice'], "double"),
                       GetSQLValueString(isset($_POST['shippingexempt']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['weight'], "double"),
                       GetSQLValueString($_POST['box_length'], "double"),
                       GetSQLValueString($_POST['box_height'], "double"),
                       GetSQLValueString($_POST['box_width'], "double"),
                       GetSQLValueString(isset($_POST['hazardous']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['manufacturerID'], "int"),
                       GetSQLValueString($_POST['imageURL'], "text"),
                       GetSQLValueString($_POST['inputfield'], "text"),
                       GetSQLValueString($_POST['instock'], "int"),
                       GetSQLValueString(isset($_POST['featured']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['saleitem']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['vattype'], "int"),
                       GetSQLValueString($_POST['statusID'], "int"),
                       GetSQLValueString($_POST['modifiedbyID'], "int"),
                       GetSQLValueString($_POST['modifieddatetime'], "date"),
                       GetSQLValueString(isset($_POST['addfrom']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['int_length'], "double"),
                       GetSQLValueString($_POST['int_height'], "double"),
                       GetSQLValueString($_POST['int_width'], "double"),
                       GetSQLValueString($_POST['capacity'], "double"),
                       GetSQLValueString($_POST['fileupload'], "int"),
                       GetSQLValueString(isset($_POST['nocommondetails']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['relatedall']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['priceper'], "text"),
                       GetSQLValueString($_POST['shippingrateID'], "int"),
                       GetSQLValueString($_POST['area'], "double"),
                       GetSQLValueString(isset($_POST['noshipinternational']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['altimage']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['custompageURL'], "text"),
                       GetSQLValueString($_POST['seotitle'], "text"),
                       GetSQLValueString($_POST['videoembed'], "text"),
                       GetSQLValueString(isset($_POST['showareaprice']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['redirect301']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['h2'], "text"),
                       GetSQLValueString($_POST['inputfield2'], "text"),
                       GetSQLValueString($_POST['auction'], "int"),
                       GetSQLValueString($_POST['auctionenddatetime'], "date"),
                       GetSQLValueString($_POST['inputfield3'], "text"),
                       GetSQLValueString($_POST['startingbid'], "double"),
                       GetSQLValueString(isset($_POST['showrrp']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['auctionsellafter']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['class'], "text"),
                       GetSQLValueString($_POST['condition'], "int"),
                       GetSQLValueString($_POST['costprice'], "double"),
                       GetSQLValueString($_POST['supplierdirectoryID'], "int"),
                       GetSQLValueString($_POST['googleID'], "text"),
                       GetSQLValueString($_POST['availabledate'], "date"),
                       GetSQLValueString($_POST['deliveryperiod'], "int"),
                       GetSQLValueString($_POST['mindeliverytime'], "int"),
                       GetSQLValueString($_POST['maxdeliverytime'], "int"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
}


if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) { 	
  $updateGoTo = (isset($_POST['returnURL']) && $_POST['returnURL']!="") ? $_POST['returnURL'] : "index.php";
  if (isset($_SERVER['QUERY_STRING']) && !isset($_POST['returnURL'])) { // don't add query string if redirect set
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo));exit;
}

$colname_rsThisProduct = "-1";
if (isset($_GET['productID'])) {
  $colname_rsThisProduct = $_GET['productID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsThisProduct = sprintf("SELECT product.*, CONCAT(creator.firstname, ' ',creator.surname) AS createdbyname,CONCAT(modifier.firstname, ' ',modifier.surname) AS modifiedbyname,productcategory.regionID, productcategory.longID AS categorylongID, productgallery.galleryID, SUM(productoptions.instock) AS optionsinstock FROM product LEFT JOIN productcategory ON (product.productcategoryID = productcategory.ID) LEFT JOIN productgallery ON (product.ID=productgallery.productID) LEFT JOIN productoptions ON (productoptions.productID = product.ID) LEFT JOIN users AS creator ON (product.createdbyID = creator.ID) LEFT JOIN users AS modifier ON (product.modifiedbyID = modifier.ID) WHERE product.ID = %s GROUP BY (product.ID)", GetSQLValueString($colname_rsThisProduct, "int"));
$rsThisProduct = mysql_query($query_rsThisProduct, $aquiescedb) or die(mysql_error());
$row_rsThisProduct = mysql_fetch_assoc($rsThisProduct);
$totalRows_rsThisProduct = mysql_num_rows($rsThisProduct);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsStatus = "SELECT * FROM status";
$rsStatus = mysql_query($query_rsStatus, $aquiescedb) or die(mysql_error());
$row_rsStatus = mysql_fetch_assoc($rsStatus);
$totalRows_rsStatus = mysql_num_rows($rsStatus);

// set userID to filter categories by if Agent
$userID = ($_SESSION['MM_UserGroup'] <=6) ?  $row_rsLoggedIn['ID'] : 0;

$varRegionID_rsAllSiteCategories = "1";
if (isset($regionID)) {
  $varRegionID_rsAllSiteCategories = $regionID;
}
$varUserID_rsAllSiteCategories = "0";
if (isset($userID)) {
  $varUserID_rsAllSiteCategories = $userID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsAllSiteCategories = sprintf("SELECT productcategory.ID, productcategory.title, parentcategory.title AS parent FROM productcategory LEFT JOIN productcategory AS parentcategory ON (productcategory.subcatofID  = parentcategory.ID) LEFT JOIN directoryuser ON (productcategory.directoryID = directoryuser.directoryID) WHERE productcategory.statusID = 1 AND (productcategory.regionID = %s   OR productcategory.regionID =0) AND (%s = 0 OR %s = directoryuser.userID) GROUP BY productcategory.ID ORDER BY parentcategory.ordernum, productcategory.ordernum  ", GetSQLValueString($varRegionID_rsAllSiteCategories, "int"),GetSQLValueString($varUserID_rsAllSiteCategories, "int"),GetSQLValueString($varUserID_rsAllSiteCategories, "int"));
$rsAllSiteCategories = mysql_query($query_rsAllSiteCategories, $aquiescedb) or die(mysql_error());
$row_rsAllSiteCategories = mysql_fetch_assoc($rsAllSiteCategories);
$totalRows_rsAllSiteCategories = mysql_num_rows($rsAllSiteCategories);


$varProductID_rsProductDetails = "-1";
if (isset($_GET['productID'])) {
  $varProductID_rsProductDetails = $_GET['productID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsProductDetails = sprintf("SELECT productdetails.ID, productdetails.tabtitle, productdetails.statusID, region.title AS region, productdetails.defaulttab FROM productdetails LEFT JOIN region ON (productdetails.regionID = region.ID) WHERE productdetails.productID = %s ORDER BY productdetails.ordernum", GetSQLValueString($varProductID_rsProductDetails, "int"));
$rsProductDetails = mysql_query($query_rsProductDetails, $aquiescedb) or die(mysql_error());
$row_rsProductDetails = mysql_fetch_assoc($rsProductDetails);
$totalRows_rsProductDetails = mysql_num_rows($rsProductDetails);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsProductPrefs = "SELECT * FROM productprefs WHERE ID=".$regionID."";
$rsProductPrefs = mysql_query($query_rsProductPrefs, $aquiescedb) or die(mysql_error());
$row_rsProductPrefs = mysql_fetch_assoc($rsProductPrefs);
$totalRows_rsProductPrefs = mysql_num_rows($rsProductPrefs);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = "SELECT useregions FROM preferences WHERE ID=".$regionID."";
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);

$varThisRegion_rsAllThisSiteOtherProducts = "1";
if (isset($row_rsThisProduct['regionID'])) {
  $varThisRegion_rsAllThisSiteOtherProducts = $row_rsThisProduct['regionID'];
}
$varThisProduct_rsAllThisSiteOtherProducts = "-1";
if (isset($_GET['productID'])) {
  $varThisProduct_rsAllThisSiteOtherProducts = $_GET['productID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsAllThisSiteOtherProducts = sprintf("SELECT product.ID, product.title, productcategory.regionID FROM product LEFT JOIN productcategory ON (product.productcategoryID = productcategory.ID) WHERE product.statusID = 1 AND productcategory.statusID = 1 AND (productcategory.regionID  = %s OR productcategory.regionID =0) AND product.ID != %s ORDER BY product.title", GetSQLValueString($varThisRegion_rsAllThisSiteOtherProducts, "int"),GetSQLValueString($varThisProduct_rsAllThisSiteOtherProducts, "int"));
$rsAllThisSiteOtherProducts = mysql_query($query_rsAllThisSiteOtherProducts, $aquiescedb) or die(mysql_error());
$row_rsAllThisSiteOtherProducts = mysql_fetch_assoc($rsAllThisSiteOtherProducts);
$totalRows_rsAllThisSiteOtherProducts = mysql_num_rows($rsAllThisSiteOtherProducts);

$varThisManufacturerID_rsManufacturers = "-1";
if (isset($row_rsThisProduct['manufacturerID'])) {
  $varThisManufacturerID_rsManufacturers = $row_rsThisProduct['manufacturerID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsManufacturers = sprintf("SELECT * FROM productmanufacturer WHERE statusID = 1 OR ID = %s ORDER BY manufacturername ASC", GetSQLValueString($varThisManufacturerID_rsManufacturers, "int"));
$rsManufacturers = mysql_query($query_rsManufacturers, $aquiescedb) or die(mysql_error());
$row_rsManufacturers = mysql_fetch_assoc($rsManufacturers);
$totalRows_rsManufacturers = mysql_num_rows($rsManufacturers);

$varProductID_rsTags = "-1";
if (isset($_GET['productID'])) {
  $varProductID_rsTags = $_GET['productID'];
}
$varRegionID_rsTags = "1";
if (isset($regionID)) {
  $varRegionID_rsTags = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsTags = sprintf("SELECT producttag.ID, producttag.tagname, producttaggroup.taggroupname, producttagged.ID AS tagged FROM producttag LEFT JOIN producttaggroup ON (producttag.taggroupID = producttaggroup.ID) LEFT JOIN producttagged ON (producttag.ID = producttagged.tagID AND producttagged.productID = %s) WHERE producttaggroup.regionID = %s ORDER BY producttaggroup.ordernum, producttag.taggroupID , producttag.ordernum, producttag.tagname", GetSQLValueString($varProductID_rsTags, "int"),GetSQLValueString($varRegionID_rsTags, "int"));
$rsTags = mysql_query($query_rsTags, $aquiescedb) or die(mysql_error());
$row_rsTags = mysql_fetch_assoc($rsTags);
$totalRows_rsTags = mysql_num_rows($rsTags);

$varProductID_rsRegionProduct = "-1";
if (isset($_GET['productID'])) {
  $varProductID_rsRegionProduct = $_GET['productID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsRegionProduct = sprintf("SELECT region.ID, region.title, productinregion.ID AS isin FROM region LEFT JOIN productinregion ON (productinregion.regionID = region.ID AND productinregion.productID = %s) ", GetSQLValueString($varProductID_rsRegionProduct, "int"));
$rsRegionProduct = mysql_query($query_rsRegionProduct, $aquiescedb) or die(mysql_error());
$row_rsRegionProduct = mysql_fetch_assoc($rsRegionProduct);
$totalRows_rsRegionProduct = mysql_num_rows($rsRegionProduct);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsShippingRates = "SELECT ID, shippingname, shippingrate FROM productshipping WHERE statusID = 1 ORDER BY shippingname ASC";
$rsShippingRates = mysql_query($query_rsShippingRates, $aquiescedb) or die(mysql_error());
$row_rsShippingRates = mysql_fetch_assoc($rsShippingRates);
$totalRows_rsShippingRates = mysql_num_rows($rsShippingRates);

$varRegionID_rsVatRates = "1";
if (isset($regionID)) {
  $varRegionID_rsVatRates = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsVatRates = sprintf("SELECT productvatrate.ID, productvatrate.ratename, productvatrate.ratepercent FROM productvatrate WHERE ID >1 AND productvatrate.regionID = %s ORDER BY productvatrate.ratepercent", GetSQLValueString($varRegionID_rsVatRates, "int"));
$rsVatRates = mysql_query($query_rsVatRates, $aquiescedb) or die(mysql_error());
$row_rsVatRates = mysql_fetch_assoc($rsVatRates);
$totalRows_rsVatRates = mysql_num_rows($rsVatRates);

$colname_rsBids = "-1";
if (isset($_GET['productID'])) {
  $colname_rsBids = $_GET['productID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsBids = sprintf("SELECT productbid .amount, productbid.createddatetime, users.ID AS userID, users.firstname, users.surname FROM productbid LEFT JOIN users ON (productbid.createdbyID = users.ID) WHERE productbid .productID = %s ORDER BY productbid .createddatetime ASC", GetSQLValueString($colname_rsBids, "int"));
$rsBids = mysql_query($query_rsBids, $aquiescedb) or die(mysql_error());
$row_rsBids = mysql_fetch_assoc($rsBids);
$totalRows_rsBids = mysql_num_rows($rsBids);

$varRegionID_rsSuppliers = "1";
if (isset($regionID)) {
  $varRegionID_rsSuppliers = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsSuppliers = sprintf("SELECT directory.ID, directory .name FROM directory LEFT JOIN directorycategory ON (directory.categoryID= directorycategory.ID) WHERE directory.statusID = 1 AND directorycategory.statusID = 1 AND (directorycategory.regionID = 0 OR directorycategory.regionID  = %s) ORDER BY name ASC", GetSQLValueString($varRegionID_rsSuppliers, "int"));
$rsSuppliers = mysql_query($query_rsSuppliers, $aquiescedb) or die(mysql_error());
$row_rsSuppliers = mysql_fetch_assoc($rsSuppliers);
$totalRows_rsSuppliers = mysql_num_rows($rsSuppliers);

$varProductID_rsVersions = "-1";
if (isset($_GET['productID'])) {
  $varProductID_rsVersions = $_GET['productID'];
}



function createNotificationGroupEmail($productID,$productname) {
	global $aquiescedb, $site_name;
	$select = "SELECT users.email FROM productnotify LEFT JOIN users ON (productnotify.userID = users.ID) WHERE productID = ".$productID;
	$result = mysql_query($select, $aquiescedb) or die(mysql_error());
	if(mysql_num_rows($result)>0) {
		$bcc = "";
		while($row = mysql_fetch_assoc($result)) { // concat emails
			$bcc .= isset($row['email']) ? $row['email']."," : "";
		} // end concat emails
		if($bcc!="") {
			$select = "SELECT contactemail, orgname FROM preferences";
			$result = mysql_query($select, $aquiescedb) or die(mysql_error());
			$row = mysql_fetch_assoc($result);
			$to = $row['contactemail']; // for bcc
			$from = $row['contactemail'];
			$friendlyfrom = $row['orgname'];			
			$subject = $productname." now available";
			$message = "Dear customer,\n\nThis is an automated email to let you that the the following product is now available at ".$site_name.":\n\n";
			$message .= $productname."\n\n";
			$message .= "Visit the shop at ";
			$message .= getProtocol()."://".$_SERVER['HTTP_HOST'].productLink($row_rsThisProduct['ID'], "", $row_rsThisProduct['productcategoryID'])."\n\n";
			sendMail($to,$subject,$message,$from,$friendlyfrom,"","",false,"",$bcc);
		}
	} // is result
	
	// create group email
	// update notify 
	$update = "UPDATE productnotify SET notified = 1, notifieddatetime = '".date('Y-m-d H:i:s')."' WHERE productID = ".$productID;
	$result = mysql_query($update, $aquiescedb) or die(mysql_error());	
}

if(isset($_GET['uncrop'])) {
	copy(UPLOAD_ROOT."o_".$row_rsThisProduct['imageURL'], UPLOAD_ROOT.$row_rsThisProduct['imageURL']);
	
	createImageSizes(UPLOAD_ROOT.$row_rsThisProduct['imageURL']);
	unlink(UPLOAD_ROOT."o_".$row_rsThisProduct['imageURL']);
}

$width = 0; $height = 0;
if(isset($row_rsThisProduct['imageURL']) && is_readable(UPLOAD_ROOT.$row_rsThisProduct['imageURL'])) {
	list($width, $height, $type, $attr)= @getimagesize(UPLOAD_ROOT.$row_rsThisProduct['imageURL']);
}



?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Update ".$row_rsThisProduct['sku']." ".$row_rsThisProduct['title']; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<?php  $editor_height = 100; require_once('../../../core/tinymce/tinymce.inc.php'); ?>
<style>
<!--
 <?php  if ($totalRows_rsManufacturers==0) {
?>.manufacturer {
display:none;
}
<?php
}
if ($row_rsPreferences['useregions']!=1) {
?> .region {
display:none;
}
<?php
}
 if($row_rsProductPrefs['shippingcalctype'] !=2 && $row_rsProductPrefs['shippingcalctype'] !=4) {
 echo "#shippingrates { display:none; } ";
}
 if ($row_rsProductPrefs['auctions']!=1) {
 echo ".auction { display:none; } ";
}

echo ".options-increase{ display:none; } "; // overridden by ajax
 ?>
-->
</style>
<script src="/SpryAssets/SpryTabbedPanels.js"></script>
<script src="/SpryAssets/SpryValidationTextField.js"></script>
<script src="/core/scripts/formUpload.js"></script>
<script src="/3rdparty/jquery/jquery.jcrop/js/jquery.Jcrop.js"></script>
<link href="/3rdparty/jquery/jquery.jcrop/css/jquery.Jcrop.css" rel="stylesheet" >
<link href="/SpryAssets/SpryTabbedPanels.css" rel="stylesheet"  />
<script>

var fb_keepAlive = true;

// When the document is ready set up our sortable with it's inherant function(s) 
$(document).ready(function() { 

	$(".sortable").sortable({ 
		handle : '.handle', 
		update : function () { 
		var order = $(this).sortable('serialize'); 
			$("#info").load("/core/ajax/sort.ajax.php?table=productdetails&"+order); 
		} 
	}); 
	$('.croppable').Jcrop({onSelect: showCoords});
	getData("/products/admin/products/ajax/productOptions.ajax.php?productID="+document.getElementById('ID').value,"productOptionList");
	getData("/products/admin/inc/product_checkboxes.ajax.php?set=version&productID="+document.getElementById('ID').value,"productversions");
	getData("/products/admin/inc/product_checkboxes.ajax.php?set=finish&productID="+document.getElementById('ID').value,"productfinishes");
	updatePricem2();
	togglePricePer();
	
	url = "/products/admin/inc/orders.inc.php?productID="+$("#ID").val();
	$.get(url, function(data) {
		$("#orders").html(data);
	});
}); 



 
 
 
 function showCoords(c)
{
	$('#x').val(c.x);
	$('#y').val(c.y);
	$('#x2').val(c.x2);
	$('#y2').val(c.y2);
	$('#w').val(c.w);
	$('#h').val(c.h);
};

submitID= "submitbutton";


function updateVersion(versionname, isChecked, type) {
	
	//alert(versionname+":"+type+":"+isChecked);
	getData("/products/admin/inc/update_checkbox.ajax.php?type="+type+"&productID="+document.getElementById('ID').value+"&versionname="+escape(versionname)+"&checked="+isChecked+"&userID="+document.getElementById('modifiedbyID').value);
	
}


function updatePrice() {
	if(parseFloat(document.getElementById('area').value)>0 && parseFloat(document.getElementById('pricem2').value) >0) {
		price =  parseFloat(document.getElementById('area').value) * parseFloat(document.getElementById('pricem2').value);
		document.getElementById('price').value = price.toFixed(2);
	}
}

function updatePricem2() {
	if(parseFloat(document.getElementById('area').value)>0 && parseFloat(document.getElementById('price').value) >0) {
		pricem2 = parseFloat(document.getElementById('price').value)/parseFloat(document.getElementById('area').value) ;
		document.getElementById('pricem2').value = pricem2.toFixed(2);
	}
}


function togglePricePer() {
	if(document.getElementById('pricetype').value == 0) {
		document.getElementById('priceper').style.display = "inline";
	} else {
		document.getElementById('priceper').style.display = "none";
	}
}


</script>
<link href="/SpryAssets/SpryValidationTextField.css" rel="stylesheet"  />
<link href="/photos/css/defaultGallery.css" rel="stylesheet"  />
<link href="../../css/defaultProducts.css" rel="stylesheet"  />
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
        <h1><i class="glyphicon glyphicon-shopping-cart"></i> <?php echo $row_rsThisProduct['sku']; ?> <?php echo $row_rsThisProduct['title']; ?></h1>
        <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
          <li><a href="<?php echo (isset($_GET['returnURL'])) ? htmlentities($_GET['returnURL']) : "index.php"; ?>" class="link_undo"><i class="glyphicon glyphicon-arrow-left"></i> Back</a></li>
          <li><a href="<?php echo productLink($row_rsThisProduct['ID'], $row_rsThisProduct['longID'], $row_rsThisProduct['productcategoryID'],$row_rsThisProduct['categorylongID'],0,"",0,"preview=true"); ?>" target="_blank" rel="noopener" ><i class="glyphicon glyphicon-new-window"></i> View in shop</a></li>
        </ul></div></nav>
        <?php if ($row_rsLoggedIn['usertypeID'] >=8) { //admin only ?>
        <?php  if(isset($submit_error)) { ?>
        <p class="alert alert-danger" role="alert"><?php echo $submit_error; ?></p>
        <?php } ?>
        <form action="<?php echo $editFormAction; ?>" method="post" enctype="multipart/form-data" name="form1" id="form1"    >
          <div id="TabbedPanels1" class="TabbedPanels">
            <ul class="TabbedPanelsTabGroup">
              <li class="TabbedPanelsTab" tabindex="0" id="tabMainDetails">Summary</li>
              <li class="TabbedPanelsTab" tabindex="0" id="tabPhotos">Photos &amp; Video</li>
              <li class="TabbedPanelsTab" tabindex="0" id="tabMoreDetails">Details</li>
              <li class="TabbedPanelsTab" tabindex="0" id="tabCategories">Categories &amp; Tags</li>
              <li class="TabbedPanelsTab" tabindex="0" id="tabProductOptions">Buy Options</li>
              <li class="TabbedPanelsTab auction" tabindex="0">Auction</li>
              <li class="TabbedPanelsTab" tabindex="0" id="tabShipping">Shipping</li>
              <li class="TabbedPanelsTab" tabindex="0" id="tabRelated">Related Products</li>
              <li class="TabbedPanelsTab" tabindex="0">Sales</li>
<li class="TabbedPanelsTab" tabindex="0" id="tabSEO">SEO</li>
              
              <li class="TabbedPanelsTab" tabindex="0">Advanced</li>
            </ul>
            <div class="TabbedPanelsContentGroup"> 
              
              <!--
          
          
          
          SUMMARY 
          
          
          
          
          --->
              
              <div class="TabbedPanelsContent">
                <span id="sprytextfield2">
                      <input name="title" type="text"  value="<?php echo htmlentities($row_rsThisProduct['title'], ENT_COMPAT, "UTF-8"); ?>" size="50" maxlength="100" class="form-control" />
                      <span class="textfieldRequiredMsg">A value is required.</span></span><textarea name="description" id="description" cols="45" rows="5" class="tinymce"><?php echo htmlentities($row_rsThisProduct['description'],ENT_COMPAT,"UTF-8"); ?></textarea><table class="form-table">
                 
                  <tr>
                    <td class="text-nowrap text-right top">Price:</td>
                    <td  class="form-inline"><span id="sprytextfield3">
                      <input name="price" type="text"  id="price" value="<?php echo number_format($row_rsThisProduct['price'],2,".",""); ?>" size="8" maxlength="8" onKeyUp="updatePricem2()"  class="form-control" />
                      <input name="old-price" type="hidden"  value="<?php echo number_format($row_rsThisProduct['price'],2,".",""); ?>"   class="form-control" />
                      <select name="pricetype" id="pricetype"  onChange="togglePricePer()"  class="form-control">
                        <option value="1" <?php if (!(strcmp(1, $row_rsThisProduct['pricetype']))) {echo "selected=\"selected\"";} ?>>per item</option>
                        <option value="2" <?php if (!(strcmp(2, $row_rsThisProduct['pricetype']))) {echo "selected=\"selected\"";} ?>>per kg</option>
                        <option value="3" <?php if (!(strcmp(3, $row_rsThisProduct['pricetype']))) {echo "selected=\"selected\"";} ?>>per hour</option>
                        <option value="4" <?php if (!(strcmp(4, $row_rsThisProduct['pricetype']))) {echo "selected=\"selected\"";} ?>>per day</option>
                        <option value="0" <?php if (!(strcmp(0, $row_rsThisProduct['pricetype']))) {echo "selected=\"selected\"";} ?>>other...</option>
                      </select>
                      <input name="priceper" id="priceper" type="text" value="<?php echo $row_rsThisProduct['priceper']; ?>" size="20" maxlength="20"  class="form-control">
                      <span class="textfieldRequiredMsg">A price is required. However, you can enter zero.</span></span> &nbsp;&nbsp;
                      <label>Tax:
                        <select name="vattype" id="vattype"  class="form-control">
                          <option value="0" <?php if (!(strcmp(0, $row_rsThisProduct['vattype']))) {echo "selected=\"selected\"";} ?>>Zero rated VAT (0%)</option>
                          <option value="1" <?php if (!(strcmp(1, $row_rsThisProduct['vattype']))) {echo "selected=\"selected\"";} ?>>Standard VAT<?php echo isset($thisRegion['vatrate']) ? " (".$thisRegion['vatrate']."%)" : ""; ?></option>
                          <?php if($totalRows_rsVatRates>0) {
do {  
?>
                          <option value="<?php echo $row_rsVatRates['ID']; ?>" <?php if ($row_rsVatRates['ID'] == $row_rsThisProduct['vattype']){echo "selected=\"selected\"";} ?>><?php echo $row_rsVatRates['ratename']." (".$row_rsVatRates['ratepercent']."%)"; ?></option>
                          <?php
} while ($row_rsVatRates = mysql_fetch_assoc($rsVatRates));
  $rows = mysql_num_rows($rsVatRates);
  if($rows > 0) {
      mysql_data_seek($rsVatRates, 0);
	  $row_rsVatRates = mysql_fetch_assoc($rsVatRates);
  }
					}
?>
                        </select>
                      </label>
                      <label class="options-increase">
                        <input type="checkbox" name="options-increase">
                        change options prices by same percentage</label></td>
                  </tr>
                  <tr>
                    <td class="text-nowrap text-right top">RRP:</td>
                    <td class="form-inline"><label id="rrp-price">
                      <input name="listprice" type="text"  id="listprice" value="<?php echo isset($row_rsThisProduct['listprice']) ? number_format($row_rsThisProduct['listprice'],2,".","") : ""; ?>" size="8" maxlength="8"   class="form-control"/>
                    </label>
                      <label>
                        <input <?php if (!(strcmp($row_rsThisProduct['showrrp'],1))) {echo "checked=\"checked\"";} ?> name="showrrp" type="checkbox" id="showrrp" value="1" />
                        Show</label>
                      &nbsp;&nbsp;&nbsp;
                      <label>Cost price:
                        <input name="costprice" type="text"  id="costprice" value="<?php echo  isset($row_rsThisProduct['costprice']) ? number_format($row_rsThisProduct['costprice'],2,".",""): ""; ?>" size="8" maxlength="8"  class="form-control"/>
                      </label></td>
                  </tr>
                  <tr>
                    <td class="text-nowrap text-right"><label for="instock">Number in stock:</label></td>
                    <td class="form-inline"><span id="sprytextfield8">
                      <input name="instock" type="text"  id="instock" value="<?php echo ($row_rsProductPrefs['stockcontrol']==1 && isset($row_rsThisProduct['optionsinstock'])) ? $row_rsThisProduct['optionsinstock'] : $row_rsThisProduct['instock']; ?>" size="8" maxlength="8"  class="form-control"/>
                      <span class="textfieldRequiredMsg">A value is required.</span><span class="textfieldInvalidFormatMsg">Invalid format.</span></span>
                      <input name="oldinstock" type="hidden" id="oldinstock" value="<?php echo $row_rsThisProduct['instock']; ?>" />
                      <?php if($row_rsProductPrefs['stockcontrol']==1 && isset($row_rsThisProduct['optionsinstock']) && $row_rsThisProduct['optionsinstock'] != $row_rsThisProduct['instock']) echo "Updated to match sum of options in stock"; ?>
                      <label>Available from:
                        <input name="availabledate" id="availabledate" type="hidden" value="<?php $setvalue = $row_rsThisProduct['availabledate']; echo $setvalue; $inputname = "availabledate"; ?>">
                      </label>
                      <?php require('../../../core/includes/datetimeinput.inc.php'); ?></td>
                  </tr>
                  <tr>
                    <td class="text-nowrap text-right">Status:</td>
                    <td class="form-inline"><select name="statusID"  class="form-control">
                      <?php
do {  
?>
                      <option value="<?php echo $row_rsStatus['ID']?>"<?php if (!(strcmp($row_rsStatus['ID'], $row_rsThisProduct['statusID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsStatus['description']?></option>
                      <?php
} while ($row_rsStatus = mysql_fetch_assoc($rsStatus));
  $rows = mysql_num_rows($rsStatus);
  if($rows > 0) {
      mysql_data_seek($rsStatus, 0);
	  $row_rsStatus = mysql_fetch_assoc($rsStatus);
  }
?>
                    </select>
                      <label>
                        <input <?php if (!(strcmp($row_rsThisProduct['featured'],1))) {echo "checked=\"checked\"";} ?> name="featured" type="checkbox" id="featured" value="1" />
                        Featured product</label>
                      &nbsp;&nbsp;&nbsp;
                      <label>
                        <input <?php if (!(strcmp($row_rsThisProduct['saleitem'],1))) {echo "checked=\"checked\"";} ?> name="saleitem" type="checkbox" id="saleitem" value="1" />
                        Sale item</label>
                      &nbsp;&nbsp;&nbsp; </td>
                  </tr>
                </table>
              </div>
              <div class="TabbedPanelsContent">
                <h2>Main image</h2>
                <?php if (isset($row_rsThisProduct['imageURL'])) { ?>
                <img src="<?php echo getImageURL($row_rsThisProduct['imageURL'],"large","", true); ?>" class="croppable large" /><br />
                <input name="noImage" type="checkbox" value="1" />
                Remove image<br>
  <?php if(is_readable(UPLOAD_ROOT."o_".$row_rsThisProduct['imageURL'])) { ?>
                This image has been cropped. <a href="modify_product.php?productID=<?php echo $row_rsThisProduct['ID']; ?>&uncrop=true">Uncrop</a>.
  <?php } ?>
                To crop, drag your mouse over the image and click save changes below.
  <?php } else { ?>
                No image associated with this product.
  <?php } ?>
  <input type="hidden" name="x" id="x">
  <input type="hidden" name="x2" id="x2">
  <input type="hidden" name="y" id="y">
  <input type="hidden" name="y2" id="y2">
  <input type="hidden" name="w" id="w">
  <input type="hidden" name="h" id="h">
  <input type="hidden" name="ratio" id="ratio" value="<?php echo floatval($width/$image_sizes['large']['width']); ?>">
  <span class="upload"><br />
    Add/change image below:<br />
  <input name="filename" type="file" id="filename" size="20" class="fileinput " accept=".jpg,.jpeg,.gif,.png"  />
  </span>
  <h2>Photo &amp; Video Gallery</h2>
  <div class="clearfix">
    <?php if(isset($row_rsThisProduct['galleryID'])) { 
			  $_GET['galleryID'] = $row_rsThisProduct['galleryID'];
			  require_once('../../../photos/includes/gallery.inc.php');   } ?>
  </div>
  <fieldset class="upload">
    <label for="galleryfilename">Add to gallery:</label>
    <input name="galleryfilename" type="file" id="galleryfilename" size="20" class="fileinput " accept=".jpg,.jpeg,.gif,.png,.mp4"  />
    <input name="galleryID" type="hidden" id="galleryID" value="<?php echo $row_rsThisProduct['galleryID']; ?>" />
    <label>or enter URL:<input name="mediaURL" type="text" class="form-control" placeholder="e.g. YouTube link"></label>
    <button name="addphoto" type="button" id="addphoto" onMouseDown="document.getElementById('returnURL').value='modify_product.php?defaultTab=1&amp;productID=<?php echo $row_rsThisProduct['ID']; ?>'; this.form.submit();" class="btn btn-default btn-secondary"><i class="glyphicon glyphicon-plus-sign"></i> Add photo/video</button>
  </fieldset>
  <label>
    <input <?php if (!(strcmp($row_rsThisProduct['altimage'],1))) {echo "checked=\"checked\"";} ?> name="altimage" type="checkbox" value="1">
    Show alternative image from gallery as main image on product detail page (if available).</label>
  
  
              </div>
              <div class="TabbedPanelsContent">
                <p>
                  <label>Condition:
                    <input <?php if ((!isset($row_rsThisProduct['condition']) && $row_rsProductPrefs['defaultcondition']!=1) || $row_rsThisProduct['condition']==0) {echo "checked=\"checked\"";} ?> type="radio" name="condition" value="0" id="condition_0">
                    New</label>
                  &nbsp;&nbsp;&nbsp;
                  <label>
                    <input <?php if ((!isset($row_rsThisProduct['condition']) && $row_rsProductPrefs['defaultcondition']==1) || $row_rsThisProduct['condition']==1) {echo "checked=\"checked\"";} ?> type="radio" name="condition" value="1" id="condition_1">
                    Used</label>
                </p>
                <table class="form-table form-inline">
                  <tr>
                    <th colspan="2" >Manufacture/codes</th>
                    <th colspan="2">External Dimensions</th>
                    <th colspan="2">Internal (if applicable)</th>
                  </tr>
                  <tr>
                    <td align="right">Manufacturer:</td>
                    <td><select name="manufacturerID" id="manufacturerID" class="form-control">
                      <option value="" <?php if (!(strcmp("", $row_rsThisProduct['manufacturerID']))) {echo "selected=\"selected\"";} ?>>Choose manufacturer...</option>
                      <?php
do {  
?>
                      <option value="<?php echo $row_rsManufacturers['ID']?>"<?php if (!(strcmp($row_rsManufacturers['ID'], $row_rsThisProduct['manufacturerID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsManufacturers['manufacturername']?></option>
                      <?php
} while ($row_rsManufacturers = mysql_fetch_assoc($rsManufacturers));
  $rows = mysql_num_rows($rsManufacturers);
  if($rows > 0) {
      mysql_data_seek($rsManufacturers, 0);
	  $row_rsManufacturers = mysql_fetch_assoc($rsManufacturers);
  }
?>
                    </select></td>
                    <td align="right">Width:</td>
                    <td><input name="box_width" type="text"  id="box_width" value="<?php echo $row_rsThisProduct['box_width']; ?>" size="10" maxlength="10" class="form-control"/>
                      cm</td>
                    <td>Internal </td>
                    <td><input name="int_width" type="text"  id="int_width" value="<?php echo $row_rsThisProduct['int_width']; ?>" size="10" maxlength="10"  class="form-control"/>
                      cm</td>
                  </tr>
                  <tr>
                    <td align="right">Stock code (SKU):</td>
                    <td><input name="sku" type="text" id="sku" value="<?php echo $row_rsThisProduct['sku']; ?>" size="30" maxlength="50"class="form-control" /></td>
                    <td align="right">Height:</td>
                    <td><input name="box_height" type="text"  id="box_height" value="<?php echo $row_rsThisProduct['box_height']; ?>" size="10" maxlength="10"  onKeyUp="document.getElementById('area').value= parseFloat(document.getElementById('box_length').value * document.getElementById('box_height').value/10000); updatePricem2()" class="form-control"  />
                      cm</td>
                    <td>Internal</td>
                    <td><input name="int_height" type="text"  id="int_height" value="<?php echo $row_rsThisProduct['int_height']; ?>" size="10" maxlength="10" class="form-control" />
                      cm</td>
                  </tr>
                  <tr>
                    <td align="right"><?php echo $row_rsProductPrefs['text_custom_isbn_field']; ?>:</td>
                    <td><input name="isbn" type="text"  id="isbn" value="<?php echo $row_rsThisProduct['isbn']; ?>" size="30" maxlength="50"class="form-control" /></td>
                    <td align="right">Length:</td>
                    <td><input name="box_length" type="text"  id="box_length" value="<?php echo $row_rsThisProduct['box_length']; ?>" size="10" maxlength="10" onKeyUp="document.getElementById('area').value= parseFloat(document.getElementById('box_length').value * document.getElementById('box_height').value/10000); updatePricem2()" class="form-control" />
                      cm</td>
                    <td>Internal</td>
                    <td><input name="int_length" type="text"  id="int_length" value="<?php echo $row_rsThisProduct['int_length']; ?>" size="10" maxlength="10" class="form-control"/>
                      cm</td>
                  </tr>
                  <tr>
                    <td align="right">GTIN or UPC:</td>
                    <td><input name="upc" type="text" id="upc" value="<?php echo $row_rsThisProduct['upc']; ?>" size="30" maxlength="50" class="form-control" /></td>
                    <td align="right">Area (HxL):</td>
                    <td><input name="area" type="text"  id="area" value="<?php echo $row_rsThisProduct['area']; ?>" size="10" maxlength="10" onKeyUp="updatePricem2()" class="form-control" >
                      m&sup2;</td>
                    <td align="right"><label for="capacity">Capacity:</label></td>
                    <td><input name="capacity" type="text"  id="capacity" value="<?php echo $row_rsThisProduct['capacity']; ?>" size="10" maxlength="10" class="form-control">
                      litres</td>
                  </tr>
                  <tr>
                    <td align="right">MPN:</td>
                    <td><input name="mpn" type="text" id="mpn" value="<?php echo $row_rsThisProduct['mpn']; ?>" size="30" maxlength="50"class="form-control" /></td>
                    <td align="right">&nbsp;</td>
                    <td>&nbsp;</td>
                    <td align="right">&nbsp;</td>
                    <td>&nbsp;</td>
                  </tr>
                  <tr>
                    <td align="right">&nbsp;</td>
                    <td>&nbsp;</td>
                    <td align="right"><label>
                      <input <?php if (!(strcmp($row_rsThisProduct['showareaprice'],1))) {echo "checked=\"checked\"";} ?> name="showareaprice" type="checkbox" id="showareaprice" value="1">
                      Calculate and show </label>
                      <label for="pricem2">area Price:</label></td>
                    <td><input type="text" name="pricem2" id="pricem2" size="10" maxlength="10" onKeyUp="updatePrice();" class="form-control">
                      £/m&sup2;</td>
                    <td align="right">Weight:</td>
                    <td><input name="weight" type="text"  id="weight" value="<?php echo $row_rsThisProduct['weight']; ?>" size="10" maxlength="10" />
                      kgs</td>
                  </tr>
                </table>
                <h2>Details Tabs</h2>
                <button name="tabbutton" type="button" class="btn btn-default btn-secondary" id="tabbutton"  onmousedown="if(Spry.Widget.Form.validate(this.form)) { document.getElementById('returnURL').value='/products/admin/tabs/add_tab.php?defaultTab=2&amp;productID=<?php echo $row_rsThisProduct['ID']; ?>'; this.form.submit(); } else { alert('Please correct errors on page before adding more details.'); }" >Add more details</button>
                <br />
                <?php if ($totalRows_rsProductDetails > 0) { // Show if recordset not empty ?>
              <table class="table table-hover">
                 <thead><tr><th>&nbsp;</th> <th>&nbsp;</th> <th>Tab Title</th> <th class="region">Site</th> <th>Default</th> <th>Edit</th></tr></thead><tbody class="sortable">
                  <?php do { ?>
                  <tr  id="listItem_<?php echo $row_rsProductDetails['ID']; ?>" > <td class= "handle" title="Drag and drop order of pages">&nbsp;</td> <td class="status<?php echo $row_rsProductDetails['statusID']; ?>">&nbsp;</td> <td><?php echo $row_rsProductDetails['tabtitle']; ?></td> <td class="region"><em><?php echo $row_rsProductDetails['region']; ?></em></td> <td>
                    <?php if ($row_rsProductDetails['defaulttab']==1) { ?>
                    <img src="/core/images/icons/tick-green.png" alt="Default tab" width="16" height="16" style="vertical-align:
middle;" />
                    <?php } ?>
                    &nbsp; </td> <td>
                      <input type="image" name="imageField" id="imageField" src="/core/images/icons/edit-find-replace.png" onMouseDown="document.getElementById('returnURL').value='/products/admin/tabs/update_tab.php?defaultTab=2&amp;tabID=<?php echo $row_rsProductDetails['ID']; ?>&amp;productID=<?php echo $row_rsThisProduct['ID']; ?>';" />
                    </td></tr>
                  <?php } while ($row_rsProductDetails = mysql_fetch_assoc($rsProductDetails)); ?>
               </tbody></table>
               <span id="info">Drag and drop re-order</span>
                  <?php } // Show if recordset not empty ?>
                  <?php if ($totalRows_rsProductDetails == 0) { // Show if recordset empty ?>
                  <p>There are currently no description tabs for this product. You can add extra description tabs by clicking on New Tab above</p>
                  <?php } // Show if recordset empty ?>
                  <p>
                    <label>
                      <input <?php if (!(strcmp($row_rsThisProduct['nocommondetails'],1))) {echo "checked=\"checked\"";} ?> name="nocommondetails" type="checkbox" id="nocommondetails" value="1">
                      Exclude common details (if any set)</label>
                  </p>
               </div>
              <div class="TabbedPanelsContent">
                <h2>Main Category:</h2>
                <p>
                  <select name="productcategoryID" class="form-control">
                    <option value="0" <?php if (!(strcmp(0, $row_rsThisProduct['productcategoryID']))) {echo "selected=\"selected\"";} ?>>Choose category...</option>
                    <option value="0" <?php if (!(strcmp(0, $row_rsThisProduct['productcategoryID']))) {echo "selected=\"selected\"";} ?>>None</option>
                    <?php
do {  
?>
                    <option value="<?php echo $row_rsAllSiteCategories['ID']?>"<?php if (!(strcmp($row_rsAllSiteCategories['ID'], $row_rsThisProduct['productcategoryID']))) {echo "selected=\"selected\"";} ?>><?php echo isset($row_rsAllSiteCategories['parent']) ? $row_rsAllSiteCategories['parent']." &rsaquo; " : ""; echo $row_rsAllSiteCategories['title']?></option>
                    <?php
} while ($row_rsAllSiteCategories = mysql_fetch_assoc($rsAllSiteCategories));
  $rows = mysql_num_rows($rsAllSiteCategories);
  if($rows > 0) {
      mysql_data_seek($rsAllSiteCategories, 0);
	  $row_rsAllSiteCategories = mysql_fetch_assoc($rsAllSiteCategories);
  }
?>
                  </select>
                  <a href="categories/index.php">Manage Categories</a></p>
                <h2>Other categories:</h2>
                <p>You can add other categories for this product below:</p>
                <p>
                  <select name="productcategoryID2" id="productcategoryID2" class="form-control">
                    <option value="0">Choose category...</option>
                    <?php
do {  
?>
                    <option value="<?php echo $row_rsAllSiteCategories['ID']?>"><?php echo isset($row_rsAllSiteCategories['parent']) ? $row_rsAllSiteCategories['parent']." &rsaquo; " : ""; echo $row_rsAllSiteCategories['title']?></option>
                    <?php
} while ($row_rsAllSiteCategories = mysql_fetch_assoc($rsAllSiteCategories));
  $rows = mysql_num_rows($rsAllSiteCategories);
  if($rows > 0) {
      mysql_data_seek($rsAllSiteCategories, 0);
	  $row_rsAllSiteCategories = mysql_fetch_assoc($rsAllSiteCategories);
  }
?>
                  </select>
                  &nbsp;<a href="javascript:void(0);" onClick="getData('/products/admin/inc/categories.inc.php?productID=<?php echo intval($_GET['productID']); ?>&amp;addcategoryID='+document.getElementById('productcategoryID2').value+'&amp;createdbyID=<?php echo $row_rsLoggedIn['ID']; ?>','otherCategoryList')"><img src="/core/images/icons/add.png" alt="Add extra category" width="16" height="16" style="vertical-align:
middle;" /></a></p>
                <div id="otherCategoryList">
                  <?php require_once('../inc/categories.inc.php'); ?>
                </div>
                <h2>Search/Filter Tags</h2>
                <?php if ($totalRows_rsTags == 0) { // Show if recordset empty ?>
                <p>There are currently no Search Tags set. <a href="tags/index.php">Add some now</a>.</p>
                <?php } // Show if recordset empty ?>
                <?php if ($totalRows_rsTags > 0) { // Show if recordset not empty ?>
                <table border="0" cellpadding="0" cellspacing="0" class="form-table">
                  <?php do { ?>
                  <tr>
                    <td><input name="tag[<?php echo $row_rsTags['ID']; ?>]" type="checkbox" value="<?php echo htmlentities($row_rsTags['tagname'], ENT_COMPAT, "UTF-8"); ?>" <?php if(isset($row_rsTags['tagged'])) { echo "checked=\"checked\""; } ?>>
                      <input name="oldtag[<?php echo $row_rsTags['ID']; ?>]" type="hidden" value="<?php if(isset($row_rsTags['tagged'])) { echo "checked"; } ?>" ></td>
                    <td><?php echo $row_rsTags['tagname']; ?></td>
                    <td><em><?php echo $row_rsTags['taggroupname']; ?></em></td>
                  </tr>
                  <?php } while ($row_rsTags = mysql_fetch_assoc($rsTags)); ?>
                </table>
                <p>
                  <label>Add tags to META keywords:
                    <input type="checkbox" name="tagstokeywords">
                  </label>
                </p>
                <?php } // Show if recordset not empty ?>
                <hr>
                <h2>Versions/Size Options</h2>
                <div id="productversions">Loading... </div>
                <h2>Colours/Finish Options</h2>
                <div id="productfinishes">Loading... </div>
                <p><strong>NOTE:</strong> Versions and Colour Options  work in search and filtering but, in addition, when more than one option is available and adding a product to basket, the customer is required to choose a single option from the range (e.g. colour).</p>
                <div class="region">
                  <h2>Site</h2>
                  <?php do { ?>
                  <label>
                    <input name="productinregion[<?php echo $row_rsRegionProduct['ID']; ?>]" type="checkbox" value="<?php echo $row_rsRegionProduct['ID']; ?>" <?php if(isset($row_rsRegionProduct['isin'])) { echo "checked=\"checked\""; } ?>>
                    <?php echo $row_rsRegionProduct['title']; ?>
                    <input name="wasinregion[<?php echo $row_rsRegionProduct['ID']; ?>]" type="hidden" value="<?php echo $row_rsRegionProduct['isin'];  ?>">
                  </label>
                  <?php } while ($row_rsRegionProduct = mysql_fetch_assoc($rsRegionProduct)); ?>
                </div>
              </div>
              <div class="TabbedPanelsContent">
                <h2>1. Set Product Options</h2>
                <p>Like versions but with different prices/GTIN for each option. The customer will be required to choose one option before adding to basket.</p>
                <div id="productOptionList">&nbsp;</div>
                <p>
                  <label>
                    <input <?php if (!(strcmp($row_rsThisProduct['addfrom'],1))) {echo "checked=\"checked\"";} ?> name="addfrom" type="checkbox" id="addfrom" value="1" />
                    Prefix price with 'From'</label>
                </p>
                <h2>2. Customer Specific Option(s)</h2>
                <p>You can also optionally ask for more general ordering information, e.g. an engravement message, that the customer will be asked to enter in a text box when they purchase. Enter question below:</p>
                <ol>
                  <li>
                    <input name="inputfield" type="text"  id="inputfield" value="<?php echo $row_rsThisProduct['inputfield']; ?>" size="100" maxlength="255" placeholder="Optional" class="form-control" />
                  </li>
                  <li>
                    <input name="inputfield2" type="text"  id="inputfield2" value="<?php echo $row_rsThisProduct['inputfield2']; ?>" size="100" maxlength="255" placeholder="Optional" class="form-control"/>
                  </li>
                  <li>
                    <input name="inputfield3" type="text"  id="inputfield3" value="<?php echo $row_rsThisProduct['inputfield3']; ?>" size="100" maxlength="255" placeholder="Optional" class="form-control"/>
                  </li>
                </ol>
                <h2>3. File upload</h2>
                <p class="form-inline">
                  <label>Upload file with order:
                    <select name="fileupload" id="fileupload" class="form-control">
                      <option value="0" <?php if (!(strcmp(0, $row_rsThisProduct['fileupload']))) {echo "selected=\"selected\"";} ?>>Not Required</option>
                      <option value="1" <?php if (!(strcmp(1, $row_rsThisProduct['fileupload']))) {echo "selected=\"selected\"";} ?>>Optional</option>
                      <option value="2" <?php if (!(strcmp(2, $row_rsThisProduct['fileupload']))) {echo "selected=\"selected\"";} ?>>Compulsary</option>
                    </select>
                  </label>
                </p>
              </div>
              <div class="TabbedPanelsContent">
                <h2>Auction</h2>
                <p> Bidding for this product:
                  <label>
                    <input <?php if (!(strcmp($row_rsThisProduct['auction'],"0"))) {echo "checked=\"checked\"";} ?> type="radio" name="auction" value="0" >
                    Off</label>
                  &nbsp;&nbsp;&nbsp;
                  <label>
                    <input <?php if (!(strcmp($row_rsThisProduct['auction'],"1"))) {echo "checked=\"checked\"";} ?> type="radio" name="auction" value="1"  onClick="if(this.checked && document.getElementById('instock').value>1) { alert('You can only auction a product that has just one in stock.'); return false; }">
                    Bid or buy</label>
                  &nbsp;&nbsp;&nbsp;
                  <label>
                    <input <?php if (!(strcmp($row_rsThisProduct['auction'],"2"))) {echo "checked=\"checked\"";} ?> type="radio" name="auction" value="2"  onClick="if(this.checked && document.getElementById('instock').value>1) { alert('You can only auction a product that has just one in stock.'); return false; }">
                    Bid only</label>
                </p>
                <p> Auction end date and time:
                  <input name="auctionenddatetime" type="hidden" id="auctionenddatetime" value="<?php $period = "+".intval($row_rsProductPrefs['auctiondays'])." DAYS ".intval($row_rsProductPrefs['auctionhours'])." HOURS"; $setvalue = isset($row_rsThisProduct['auctionenddatetime']) ? $row_rsThisProduct['auctionenddatetime'] : date('Y-m-d H:i:s', strtotime($period)); echo $setvalue; $inputname="auctionenddatetime"; $time = true; ?>">
                  <?php require_once('../../../core/includes/datetimeinput.inc.php');  ?>
                  <label>
                    <input <?php if (!(strcmp($row_rsThisProduct['auctionsellafter'],1))) {echo "checked=\"checked\"";} ?> name="auctionsellafter" type="checkbox" id="auctionsellafter" value="1">
                    Continue to offer for sale after auction (if no bids and price set).</label>
                </p>
                <p class="form-inline">
                  <label>Starting bid:
                    <input name="startingbid" type="text" id="startingbid" size="10" maxlength="10" value="<?php echo isset($row_rsThisProduct['startingbid']) ? number_format($row_rsThisProduct['startingbid'],2,".", ""): "0.99"; ?>" class="form-control">
                  </label>
                </p>
                <h3>Bids</h3>
                <?php if ($totalRows_rsBids == 0) { // Show if recordset empty ?>
                <p>No bids so far.</p>
                <?php } // Show if recordset empty ?>
                <?php if ($totalRows_rsBids > 0) { // Show if recordset not empty ?>
                <table class="table table-hover">
                <tbody>
                  <?php do { ?>
                  <tr>
                    <td><?php echo date('d M Y H:s', strtotime($row_rsBids['createddatetime'])); ?></td>
                    <td><a href="../../../members/admin/modify_user.php?userID=<?php echo $row_rsBids['userID']; ?>" target="_blank" rel="noopener"><?php echo $row_rsBids['firstname']." ".$row_rsBids['surname']; ?></a></td>
                    <td><?php echo number_format($row_rsBids['amount'],2,".",","); ?></td>
                  </tr>
                  <?php } while ($row_rsBids = mysql_fetch_assoc($rsBids)); ?></tbody>
                </table>
                <?php } // Show if recordset not empty ?>
              </div>
              <div class="TabbedPanelsContent">
                <p>
                  <label>
                    <input <?php if (!(strcmp($row_rsThisProduct['shippingexempt'],1))) {echo "checked=\"checked\"";} ?> name="shippingexempt" type="checkbox" id="shippingexempt" value="1" />
                    This product is exempt from shipping</label>
                </p>
                <p>
                  <label>
                    <input <?php if (!(strcmp($row_rsThisProduct['noshipinternational'],1))) {echo "checked=\"checked\"";} ?> name="noshipinternational" type="checkbox" id="noshipinternational" value="1" />
                    This product cannot be shipped internationally</label>
                </p>
                <p>
                  <label>
                    <input <?php if (!(strcmp($row_rsThisProduct['hazardous'],1))) {echo "checked=\"checked\"";} ?> name="hazardous" type="checkbox" id="hazardous" value="1" />
                    This product is hazardous and needs to be shipped by specialist</label>
                  <img src="../../images/icons/hazardous.png" alt="Hazardous" width="16" height="16" style="vertical-align:
middle;" /></p>
                <fieldset id="shippingrates">
                  <legend>Specific Rate</legend>
                  <p class="form-inline">
                    <select name="shippingrateID" id="shippingrateID"class="form-control">
                      <option value="0" <?php if (!(strcmp(0, $row_rsThisProduct['shippingrateID']))) {echo "selected=\"selected\"";} ?>><?php echo isset($region['text_choose']) ? htmlentities($region['text_choose'], ENT_COMPAT, "UTF-8") : "Choose..." ?></option>
                      <?php
do {  
?>
                      <option value="<?php echo $row_rsShippingRates['ID']?>"<?php if (!(strcmp($row_rsShippingRates['ID'], $row_rsThisProduct['shippingrateID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsShippingRates['shippingname']." (".$row_rsShippingRates['shippingrate'].")"; ?></option>
                      <?php
} while ($row_rsShippingRates = mysql_fetch_assoc($rsShippingRates));
  $rows = mysql_num_rows($rsShippingRates);
  if($rows > 0) {
      mysql_data_seek($rsShippingRates, 0);
	  $row_rsShippingRates = mysql_fetch_assoc($rsShippingRates);
  }
?>
                    </select>
                  </p>
                </fieldset>
                <h3>Drop Ship</h3>
                <p class="form-inline">
                  <label>This item will be shipped directly from supplier:
                    <select name="supplierdirectoryID" id="supplierdirectoryID" class="form-control">
                      <option value="" <?php if (!(strcmp("", $row_rsThisProduct['supplierdirectoryID']))) {echo "selected=\"selected\"";} ?>>None</option>
                      <?php if(mysql_num_rows($rsSuppliers)>0) {
do {  
?>
                      <option value="<?php echo $row_rsSuppliers['ID']?>"<?php if (!(strcmp($row_rsSuppliers['ID'], $row_rsThisProduct['supplierdirectoryID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsSuppliers['name']?></option>
                      <?php
} while ($row_rsSuppliers = mysql_fetch_assoc($rsSuppliers)); 
  $rows = mysql_num_rows($rsSuppliers);
  if($rows > 0) {
      mysql_data_seek($rsSuppliers, 0);
	  $row_rsSuppliers = mysql_fetch_assoc($rsSuppliers);
  }
					  }
?>
                    </select>
                  </label>
                  <a href="../../../directory/admin/index.php">Manage suppliers</a></p>
                <h3>Delivery Lead Times</h3>
                <p class="foem-inline">
                  <label for="mindeliverytime">Between</label>
                  <input name="mindeliverytime" id="mindeliverytime" value="<?php echo $row_rsThisProduct['mindeliverytime']; ?>" size="5" maxlength="5" class="form-control">
                  and
                  <input name="maxdeliverytime" id="maxdeliverytime" value="<?php echo $row_rsThisProduct['maxdeliverytime']; ?>" size="5" maxlength="5" class="form-control">
                  <select name="deliveryperiod" id="deliveryperiod" class="form-control">
                    <option value="1" <?php if (!(strcmp(1, $row_rsThisProduct['deliveryperiod']))) {echo "selected=\"selected\"";} ?>>hours</option>
                    <option value="24" <?php if (!(strcmp(24, $row_rsThisProduct['deliveryperiod']))) {echo "selected=\"selected\"";} ?>>working days</option>
                    <option value="168" <?php if (!(strcmp(168, $row_rsThisProduct['deliveryperiod']))) {echo "selected=\"selected\"";} ?>>weeks</option>
                  </select>
                </p>
              </div>
              <div class="TabbedPanelsContent">
                <p>Add any related products that you wish to show along with this product:</p>
                <p class="form-inline">
                  <select name="relatedtoID" id="relatedtoID" class="form-control">
                    <option value="0">Choose related product...</option>
                    <?php
do {  
?>
                    <option value="<?php echo $row_rsAllThisSiteOtherProducts['ID']?>"><?php echo $row_rsAllThisSiteOtherProducts['title']?></option>
                    <?php
} while ($row_rsAllThisSiteOtherProducts = mysql_fetch_assoc($rsAllThisSiteOtherProducts));
  $rows = mysql_num_rows($rsAllThisSiteOtherProducts);
  if($rows > 0) {
      mysql_data_seek($rsAllThisSiteOtherProducts, 0);
	  $row_rsAllThisSiteOtherProducts = mysql_fetch_assoc($rsAllThisSiteOtherProducts);
  }
?>
                  </select>
                  <a href="javascript:void(0);" onClick="getData('/products/admin/inc/relatedproducts.inc.php?productID=<?php echo intval($_GET['productID']); ?>&amp;relatedtoID='+document.getElementById('relatedtoID').value+'&amp;createdbyID=<?php echo $row_rsLoggedIn['ID']; ?>','relatedProductList')"><img src="/core/images/icons/add.png" alt="Add as related product" width="16" height="16" style="vertical-align:
middle;" /></a></p>
                <div id="relatedProductList">
                  <?php require_once('../inc/relatedproducts.inc.php'); ?>
                </div>
                <label>
                  <input <?php if (!(strcmp($row_rsThisProduct['relatedall'],1))) {echo "checked=\"checked\"";} ?> name="relatedall" type="checkbox" value="1">
                  Show this product as related to all other products</label>
              </div>
              <div class="TabbedPanelsContent"><div id="orders"></div></div>
             
              
              <div class="TabbedPanelsContent">
                <h2>Seach Engine Optimisation (SEO)</h2>
                <p>Recommended for advanced users only: you can change the details below to help improve your search engine rankings. </p>
                <table border="0" cellpadding="2" cellspacing="0" class="form-table">
                  <tr>
                    <td align="right"><label for="seotitle">Title tag:</label></td>
                    <td><input name="seotitle" type="text" id="seotitle" value="<?php echo $row_rsThisProduct['seotitle']; ?>" size="66" maxlength="66" class="seo-length  form-control"></td>
                  </tr>
                  <tr>
                    <td align="right">URL name:</td>
                    <td><input name="longID" type="text" id="longID" value="<?php echo $row_rsThisProduct['longID']; ?>" size="32" maxlength="100" class="form-control" /></td>
                  </tr>
                  <tr>
                    <td align="right" valign="top">Meta Keywords:</td>
                    <td><textarea name="metakeywords" id="metakeywords" cols="45" rows="5" class="form-control"><?php echo $row_rsThisProduct['metakeywords']; ?></textarea></td>
                  </tr>
                  <tr>
                    <td align="right" valign="top">Meta Description:</td>
                    <td><textarea name="metadescription" id="metadescription" cols="45" rows="5" class="seo-length form-control"><?php echo $row_rsThisProduct['metadescription']; ?></textarea></td>
                  </tr>
                  <tr>
                    <td align="right" valign="top">H2 tag</td>
                    <td><textarea name="h2" id="h2" cols="45" rows="5" class="form-control"><?php echo $row_rsThisProduct['h2']; ?></textarea></td>
                  </tr>
                  <tr>
                    <td align="right"><label data-toggle="tooltip" title="This is the identifier number Google uses within Merchant Center. It is NOT REQUIRED but can be used to help compare competitors' prices. NOTE: This is NOT the same as Google Category ID or MPN or UPC.">Google Product Number:</label></td>
                    <td><input name="googleID" type="text" id="googleID" value="<?php echo $row_rsThisProduct['googleID']; ?>" size="30" maxlength="50" placeholder="(optional)" class="form-control" /></td>
                    
                  </tr>
                </table>
              </div>
              
           
              <!--
          
          
          
          ADVANCED 
          
          
          
          
          --->
              <div class="TabbedPanelsContent">
                <p>Redirect to another page:</p>
                <p>
                  <label for="custompageURL">URL: </label>
                  <input name="custompageURL" type="text" id="custompageURL" value="<?php echo $row_rsThisProduct['custompageURL']; ?>" size="100" maxlength="255">
                  <label>
                    <input <?php if (!(strcmp($row_rsThisProduct['redirect301'],1))) {echo "checked=\"checked\"";} ?> name="redirect301" type="checkbox" id="redirect301" value="1">
                    Permanent redirect (301)</label>
                </p>
                <p class="form-inline"><label for="class">Class:   </label>
                
                  <input name="class" type="text" id="class" value="<?php echo $row_rsThisProduct['class']; ?>" size="20" maxlength="20" class="form-control">
                </p>
              </div>
            </div>
          </div>
          <input type="hidden" name="MM_update" value="form1" />
          <input name="modifiedbyID" type="hidden" id="modifiedbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
          <input name="modifieddatetime" type="hidden" id="modifieddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
          <input type="hidden" id="ID" name="ID" value="<?php echo $row_rsThisProduct['ID']; ?>" />
          <input name="imageURL" type="hidden" id="imageURL" value="<?php echo $row_rsThisProduct['imageURL']; ?>" />
          <input type="hidden" name="returnURL" id="returnURL" value="<?php echo isset($_GET['returnURL']) ? htmlentities($_GET['returnURL'], ENT_COMPAT, "UTF-8") : ""; ?>" />
          <div>
            <button type="submit" class="btn btn-primary" id="submitbutton" >Save changes</button>
          </div>
           <p><em>This product was originally created by <?php echo isset($row_rsThisProduct['createdbyname']) ? $row_rsThisProduct['createdbyname'] : "the system"; ?>  at <?php echo date('g:ia',strtotime( $row_rsThisProduct['datetimecreated'])); ?> on <?php echo date('l jS F Y',strtotime($row_rsThisProduct['datetimecreated'])); ?>
            <?php if(isset($row_rsThisProduct['modifieddatetime'])) { ?>
          <br>It was updated by <?php echo isset($row_rsThisProduct['modifiedbyname']) ? $row_rsThisProduct['modifiedbyname'] : "the system"; ?> at <?php echo date('g:ia',strtotime($row_rsThisProduct['modifieddatetime'])); ?> on <?php echo date('l jS F Y',strtotime($row_rsThisProduct['modifieddatetime']));   } ?></em></p>
        </form>
        <?php } // end admin only
			else { ?>
        <p class="alert warning alert-warning" role="alert">Only site administrators can update the main product details, power users can update their own regional descriptions above.</p>
        <?php } ?>
<?php if (isset($_GET['defaultTab'])) { echo '<script>
<!--
var TabbedPanels1 = new Spry.Widget.TabbedPanels("TabbedPanels1", {defaultTab:'.intval($_GET['defaultTab']).'});
//-->
    </script>'; } else { ?>
        <script>
<!--
var TabbedPanels1 = new Spry.Widget.TabbedPanels("TabbedPanels1", {defaultTab:0});
//-->
    </script>
        <?php } ?>
<script>
<!--
var sprytextfield2 = new Spry.Widget.ValidationTextField("sprytextfield2");
var sprytextfield3 = new Spry.Widget.ValidationTextField("sprytextfield3");

var sprytextfield8 = new Spry.Widget.ValidationTextField("sprytextfield8", "integer");
//-->
    </script> 
        <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsThisProduct);

mysql_free_result($rsStatus);

mysql_free_result($rsAllSiteCategories);

mysql_free_result($rsLoggedIn);

mysql_free_result($rsProductDetails);

mysql_free_result($rsProductPrefs);

mysql_free_result($rsPreferences);

mysql_free_result($rsAllThisSiteOtherProducts);

mysql_free_result($rsManufacturers);

mysql_free_result($rsTags);

mysql_free_result($rsRegionProduct);

mysql_free_result($rsShippingRates);

mysql_free_result($rsVatRates);

mysql_free_result($rsBids);

mysql_free_result($rsSuppliers);


?>