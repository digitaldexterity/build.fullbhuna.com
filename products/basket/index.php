<?php /*

This page can be called by ajax

Basket items are held in session variable as follows:

$_SESSION['basket'][basketitem=productID][productoption]['quantity']

*/ ?>
<?php require_once('../../Connections/aquiescedb.php'); ?>
<?php require_once('../../core/includes/framework.inc.php'); ?>
<?php require_once('../includes/productHeader.inc.php'); ?>
<?php require_once('../includes/basketFunctions.inc.php');?>
<?php require_once('../../core/includes/upload.inc.php'); ?>
<?php require_once('../includes/productFunctions.inc.php'); ?>
<?php require_once('../includes/products.inc.php'); ?>
<?php require_once('../../core/seo/includes/seo.inc.php'); 
if(!empty($_POST)) trackPage("Add items to basket"); ?>
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



if(isset($_GET['VendorTxCode'])) {
	// assume if tx code is passed then basket is being restored from a previous transaction
	mysql_select_db($database_aquiescedb, $aquiescedb);
	
	$select = "SELECT basket_json FROM productorders WHERE VendorTxCode = ".GetSQLValueString($_GET['VendorTxCode'], "text");
	$result = mysql_query($select, $aquiescedb) or die(mysql_error());
	if(mysql_num_rows($result)>0) {
		$basket = mysql_fetch_assoc($result);
		if(trim($basket['basket_json'])!="") {
			$_SESSION['basket'] = json_decode($basket['basket_json'], true);
		} else {
			$error = "Sorry, could not retrieve saved basket.";
		}
	} else {
		$error = "Sorry, could not retrieve specified order.";
	}
	
}

$varRegionID_rsPromotionsWithCodes = "0";
if (isset($regionID)) {
  $varRegionID_rsPromotionsWithCodes = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPromotionsWithCodes = sprintf("SELECT ID FROM productpromo WHERE productpromo.statusID = 1 AND (productpromo.regionID = 0 OR productpromo.regionID = %s OR %s = 0)  AND (productpromo.startdatetime IS NULL OR productpromo.startdatetime <= '".date('Y-m-d H:i:s')."') AND (productpromo.enddatetime IS NULL OR productpromo.enddatetime >= '".date('Y-m-d H:i:s')."') AND (productpromo.promocodetype = 1 OR productpromo.promocodetype = 2)", GetSQLValueString($varRegionID_rsPromotionsWithCodes, "int"),GetSQLValueString($varRegionID_rsPromotionsWithCodes, "int"));
$rsPromotionsWithCodes = mysql_query($query_rsPromotionsWithCodes, $aquiescedb) or die(mysql_error());
$row_rsPromotionsWithCodes = mysql_fetch_assoc($rsPromotionsWithCodes);
$totalRows_rsPromotionsWithCodes = mysql_num_rows($rsPromotionsWithCodes);


?>
<?php 

$regionID = isset($regionID) ? $regionID : 1;

if(isset($_GET['oneoffamount'])) { 
	if($_GET['oneoffamount']>0 && isset($_GET['token']) && $_GET['token'] == md5($_GET['oneoffamount'].PRIVATE_KEY)) { //ONE OFF PAYMENT
	$title - isset($_GET['title']) ? $_GET['title']: "One-off payment";
	addProductsTobasket(0, 1, 0, "", $title, $_GET['oneoffamount']);
		header("location: /products/payments/"); exit;
	} else {
		$warning = "Sorry, there was a problem adding the one-off item to your basket. Please check that the link you were given is correct.";
	}

}


if(isset($row_rsProductPrefs['basketpageURL'])) { // custom basket page
	$url = $row_rsProductPrefs['basketpageURL'];
	$url .= isset($_SERVER['QUERY_STRING']) ? "?".$_SERVER['QUERY_STRING'] : "";
	header("location: ".$url); exit;
}
if(isset($_REQUEST['restoreBasket'])) {
	// delete current basket 
	unset($_SESSION['basket']);
	$items = explode("^",trim($_COOKIE['basket'],"^"));
	foreach($items as $key => $value) {
		$item = explode("|", $value);
		$_SESSION['basket'][$item[0]][$item[1]."@@".$item[2]]['quantity'] = $item[3];
			}	
	// cookie now deleted locally 
	$secure = (getProtocol()=="https") ? true : false;
	if (isset($_COOKIE['basket'])) {
    	setcookie('basket', '', time()-42000, '/',"",$secure, true);
	}
	header("location: index.php"); exit;
	
}

if(isset($_REQUEST['promocode'])) { // add promo code
	if(isset($_SESSION['promocode'])) {
		$promoerror = "You already have a promotion code added. You can only use one per shopping basket. <a href=\"index.php?clearpromo=true\">Remove current promo code</a>.";
	} else {
		$promocode = isset($_REQUEST['promocode']) ? str_replace(" ", "", strtoupper($_REQUEST['promocode'])) : "";

		// check to see if the promo is valid
		$select = "SELECT productpromo.*, productpromocode.ID AS uniqueID FROM productpromo LEFT JOIN productpromocode ON (productpromo.ID = productpromocode.promoID) WHERE productpromo.statusID = 1 AND (productpromo.promocode LIKE ".GetSQLValueString($promocode, "text")." OR (productpromocode.promocode LIKE ".GetSQLValueString($promocode, "text")." AND productpromocode.statusID =1))";
		$result = mysql_query($select, $aquiescedb) or die(mysql_error());
		if(mysql_num_rows($result)<1) { // no match
			$promoerror =  isset($row_rsProductPrefs['text_promonotexists']) ? htmlentities($row_rsProductPrefs['text_promonotexists'], ENT_COMPAT, "UTF-8") : "We do not have a active promotion code that matches the one you entered.";
		} else { // match
			$array_promo = mysql_fetch_assoc($result);
			if(isset($array_promo['enddate']) && $array_promo['enddate'] < date('Y-m-d H:i:s')) { // outdated
				$promoerror = "This promotion expired on ".date('jS F Y H:i',strtotime($array_promo['startdate'])).".";
			} else if(isset($array_promo['startdate']) && $array_promo['startdate'] > date('Y-m-d H:i:s')) { // outdated
				$promoerror = "This promotion does not begin until ".date('jS F Y H:i',strtotime($array_promo['startdate'])).". Please try again later.";
			} else { // all OK
			
			 	$_SESSION['promocode'] = $promocode;
				if(isset($array_promo['uniqueID']) ) {
					$update = "UPDATE productpromocode SET statusID = 0, modifieddatetime = '".date('Y-m-d H:i:s')."' WHERE ID = ".intval($array_promo['uniqueID']);
					$result = mysql_query($update, $aquiescedb) or die(mysql_error());
				}
			} // end all OK
		} // end match	
	}
} // add promo

if(isset($_REQUEST['clearpromo'])) { // remove promo code
// revert unique promos back
	$update = "UPDATE productpromocode SET statusID = 1 WHERE  promocode = ".GetSQLValueString($_SESSION['promocode'], "text");
	$result = mysql_query($update, $aquiescedb) or die(mysql_error());
	unset($_SESSION['promocode']);
}
if(isset($_REQUEST['emptybasket'])) { // remove all items
	emptyBasket();
}




// ADD TO BASKET

if((isset($_REQUEST['addtobasket']) || isset($_REQUEST['sample'])) && isset($_REQUEST['productID'])) { // add item add to basket holds id of product
	
	$explanation = "";
	
	$quantity = isset($_REQUEST['quantity']) ?  intval($_REQUEST['quantity']) : 1; // get quantity - if auction can only be 1
	$productID = intval($_REQUEST['productID']);
	$select = "SELECT product.sku, product.title FROM product WHERE ID = ".$productID;
	$result = mysql_query($select, $aquiescedb) or die(mysql_error());
	$product = mysql_fetch_assoc($result);
	
	if(!isset($_REQUEST['sample'])  && isset($_REQUEST['areaquantity'])  && isset($_REQUEST['area']) && $_REQUEST['area']>0) { // convert quantity
		$quantity = ceil($quantity/floatval($_REQUEST['area']));	
		$explanation = "NOTE: Your requested area of ".htmlentities($_REQUEST['quantity'], ENT_COMPAT, "UTF-8")." sq m requires ".$quantity." ".$row_rsProductPrefs['defaultitemunit']."s of ".htmlentities($_REQUEST['area'], ENT_COMPAT, "UTF-8")." sq m  as shown";
		
	}
	if(strpos($_REQUEST['optiontext'],"http")!==false) {
		header("Status: 403");		
		die("Sorry, web addresses not allowed in submissions. Please press back button and try again.");
	}
	$_REQUEST['optiontext'] = isset($_REQUEST['optiontext']) ? htmlentities($_REQUEST['optiontext'], ENT_COMPAT, "UTF-8") : "";
	$_REQUEST['optiontext'] .= (isset($_REQUEST['optiontext2']) && trim($_REQUEST['optiontext2']) !="") ? " | ".htmlentities($_REQUEST['optiontext2'], ENT_COMPAT, "UTF-8") : "";
	$_REQUEST['optiontext'] .= (isset($_REQUEST['optiontext3']) && trim($_REQUEST['optiontext3']) !="") ? " | ".htmlentities($_REQUEST['optiontext3'], ENT_COMPAT, "UTF-8") : "";
	if(isset($_REQUEST['sample'])) { // sample get product ID
		// get original product details
		
		// get sample details
		$select = "SELECT product.ID, product.title, product.sku FROM product LEFT JOIN productinregion ON (product.ID = productinregion.productID) WHERE sku LIKE ".GetSQLValueString($_REQUEST['freesamplesku'], "text")." AND (productinregion.regionID = ".$regionID." OR productinregion.regionID IS NULL) LIMIT 1";
		
		$result = mysql_query($select, $aquiescedb) or die(mysql_error());
		$sample = mysql_fetch_assoc($result);
		
		$productID = isset($sample['ID']) ? $sample['ID'] : $productID;
		$sampletext = isset($product['title']) ? htmlentities($product['sku']." ".$product['title'], ENT_COMPAT, "UTF-8")." ".$row_rsProductPrefs['sampletext'] : htmlentities($_REQUEST['productsku']." ".$_REQUEST['producttitle'], ENT_COMPAT, "UTF-8")." ".$row_rsProductPrefs['sampletext'];		
		$_REQUEST['optiontext'] .= $sampletext;
	}

	addProductsTobasket($productID, $quantity, $_REQUEST, false, $explanation);
	/* AJAX check  */
	if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
		/* special ajax here */
		$itemname = isset($sampletext) ? strip_tags($sampletext) : strip_tags($product['title']);
		$price = "0.00";
		$json = '{ "itemname":'.json_encode($itemname).', "quantity":'.json_encode($quantity).', "price":'.json_encode($price).'}';
		die($json);
	}
}

// REMOVE FROM BASKET

if(isset($_REQUEST['removefrombasket']) && isset($_REQUEST['productID'])) { // remove item from basket
	
	$quantity = isset($_REQUEST['quantity']) ? $_REQUEST['quantity'] : 1;
	

	removeProductsFromBasket($_REQUEST['productID'], $_REQUEST['optionID'], $quantity);
}
















// UPDATE BASKET

if(isset($_POST['updatebasket']) && isset($_POST['quantity'])) {
	$productID = array();
	  foreach($_POST['quantity'] as $productID => $value) {
		  foreach($_POST['quantity'][$productID] as $optionID => $quantity) {
			 $_SESSION['basket'][$productID][base64_decode($optionID)]['quantity'] = intval($quantity);	
			 $_SESSION['basket'][$productID][base64_decode($optionID)]['explanation'] = "";
			 			  
		  }
	  }
}

// reload page without GET values so as not to have basket anomolies

if(isset($_REQUEST['addtobasket']) || isset($_REQUEST['removefrombasket']) || isset($_REQUEST['updatebasket'])) {
	if(isset($_REQUEST['nextproductID']) && intval($_REQUEST['nextproductID'])>0) {
		// usually only with 'single' add to produt so no array here
		$url = "/products/product.php?productID=".intval($_REQUEST['nextproductID']);
		$url .= (isset($_REQUEST['quantity']) && intval($_REQUEST['quantity'])>0) ? "&quantity=".intval($_REQUEST['quantity']) : "";
	} else {
		$url = "index.php?";
		$url .= isset($_REQUEST['returnURL']) ? "returnURL=".urlencode($_REQUEST['returnURL'])."&" : "";
		// keep productID to use with related products
		$url .= isset($_REQUEST['productID']) ? "productID=".intval($_REQUEST['productID']) : "";
	}
	header("location: ".$url); exit;
}
/*
	$_SESSION["strBillingCountry"] = isset($_SESSION["strBillingCountry"] ) ? $_SESSION["strBillingCountry"] : 240;
	$_SESSION["strDeliveryCountry"] = isset($_SESSION["strDeliveryCountry"] ) ? $_SESSION["strDeliveryCountry"] : 240;*/
?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!DOCTYPE html>
<html class="" lang="en"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php  $pageTitle = $row_rsProductPrefs['baskettext']; echo $pageTitle." | ".$site_name;?>
</title>
<!-- InstanceEndEditable -->
<!--[if IE]><![endif]-->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<meta name="robots" content="noindex,nofollow" />
<link href="../css/defaultProducts.css" rel="stylesheet"  />
<script src="../scripts/productFunctions.js"></script>
<script>
window.jQuery || document.write('<script src="/3rdparty/jquery/jquery-1.12.1.min.js"><\/script>'); // if not already loaded
$(document).ready(function(e) {
	// write new values to header display if available
	$(".basket_total_items").html($("#basket_total_items").val());
	$(".basket_grand_total").html($("#basket_grand_total").val());
    
});

</script>
<style>
<!--
 <?php if($row_rsProductPrefs['basketshowweight']!=1) {
 echo ".basket-column-weight  { display: none !important; }\n";
}
if($row_rsProductPrefs['basketshowupdatequantity']!=1) {
 echo ".basket-column-qty .updateablequantity { display: none; }\n";
}
else {
 echo ".basket-column-qty .fixedquantity { display: none; }\n";
}
if($row_rsProductPrefs['basketshowadjustablequantity']!=1) {
 echo ".basket-column-update  { display: none !important; }\n";
}
if($row_rsProductPrefs['basketshowremove']!=1) {
 echo ".basket-column-remove  { display: none !important; }\n";
}
if($row_rsProductPrefs['basketshowpricem2']!=1) {
 echo ".basket-column-price-m2  { display: none !important; }\n";
}

 ?>
-->
</style>
<!-- InstanceEndEditable -->
</head>
<body id="OffTemplate" class="bootstrap <?php echo $body_class;  ?>">
<?php require_once('../../local/includes/header.inc.php'); ?>
<main id="content"><!-- InstanceBeginEditable name="Body" -->
  <section>
    <div id="basketpage" class="container">
      <div class="crumbs">
        <div><span class="you_are_in">You are in: </span><a href="/">Home</a><span class="separator">&nbsp;&rsaquo;&nbsp;</span><a href="/products/">Shop</a><span class="separator">&nbsp;&rsaquo;&nbsp;</span><?php echo $row_rsProductPrefs['text_yourorder']; ?></div>
      </div>
      <ol class="checkoutprogress">
        <li class="selected"><a href="#"><?php echo isset($row_rsProductPrefs['text_yourorder']) ? htmlentities($row_rsProductPrefs['text_yourorder'], ENT_COMPAT, "UTF-8") : "Your Order" ?></a></li>
        <li><a href="/products/payments/index.php"><?php echo isset($row_rsProductPrefs['text_yourdetails']) ? htmlentities($row_rsProductPrefs['text_yourdetails'], ENT_COMPAT, "UTF-8") : "Your details" ; ?></a></li>
        <li><a href="#"><?php echo isset($row_rsProductPrefs['paymenttext']) ? htmlentities($row_rsProductPrefs['paymenttext'], ENT_COMPAT, "UTF-8" ): "Payment"; ?></a></li>
      </ol>
      <h1><?php echo isset($row_rsProductPrefs['text_yourorder']) ? htmlentities($row_rsProductPrefs['text_yourorder'], ENT_COMPAT, "UTF-8") : "Your Order" ?></h1>     
     
      <?php if(isset($_SESSION['basket_total_items'])) { ?>
      <div class="basketnavigation top">
        <a href="<?php echo  isset($_REQUEST['returnURL']) ? $_REQUEST['returnURL'] : "/products/"; ?>" class="btn btn-default btn-secondary button_continueshopping"><?php echo $row_rsProductPrefs['continueshoppingtext']; ?></a>
        <form action="/products/payments/index.php"  method="get" class="form-place-order">
          <button type="submit"   class="btn btn-primary button_checkout" ><?php echo $row_rsProductPrefs['checkouttext']; ?></button>
        </form>
      </div>
      <?php } ?>
      <?php require_once('../../core/includes/alert.inc.php'); ?>
      <?php $updateable = true; require_once('../includes/basketcontents.inc.php'); ?>
      <div class="promoform">
        <?php if(isset($_SESSION['promocode'])) { ?>
        <p><?php echo isset($row_rsProductPrefs['text_usingpromo']) ? htmlentities($row_rsProductPrefs['text_usingpromo'], ENT_COMPAT, "UTF-8") : "You are using promotion code:"; ?> <?php echo htmlentities(strtoupper($_SESSION['promocode']),ENT_COMPAT, "UTF-8"); ?> <a href="index.php?clearpromo=true"><?php echo isset($row_rsProductPrefs['text_remove']) ? htmlentities($row_rsProductPrefs['text_remove'], ENT_COMPAT, "UTF-8") : "Remove."; ?></a></p>
        <?php } else if($totalRows_rsPromotionsWithCodes>0) { ?>
        <form action="index.php" method="get">
          <p>
            <label><?php echo $row_rsProductPrefs['promocodetext']; ?>
              <input name="promocode" type="text"  id="promocode" value="<?php echo isset($_REQUEST['promocode']) ? htmlentities($_REQUEST['promocode'], ENT_COMPAT, "UTF-8") : "" ; ?>" size="20" maxlength="20" />
            </label>
            <button type="submit" name="add"  class="btn btn-default btn-secondary" id="button_entercode" ><?php echo $row_rsProductPrefs['promobuttontext']; ?></button>
          </p>
          <?php if(isset($promoerror)) { echo "<p class=\"warning_light\">".$promoerror."</p>"; } ?>
        </form>
        <?php } ?>
      </div>
      <div class="basketnavigation bottom">
      <a href="<?php echo  isset($_REQUEST['returnURL']) ? $_REQUEST['returnURL'] : "/products/"; ?>" class="btn btn-default btn-secondary button_continueshopping"><?php echo $row_rsProductPrefs['continueshoppingtext']; ?></a>
        
        <?php if($totalitems>0) { ?>
        <form action="/products/payments/index.php"  method="get" class="form-place-order">
          <button type="submit"  class="btn btn-primary button_checkout" ><?php echo $row_rsProductPrefs['checkouttext']; ?></button><?php if($row_rsProductPrefs['minimumorder']>0 && $grandtotal<$row_rsProductPrefs['minimumorder']) echo " (Minimum order ".$currency.number_format($row_rsProductPrefs['minimumorder'],2,".",",").")"; ?>
        </form>
        <?php } ?>
      </div>
      <?php if ($row_rsProductPrefs['basketrelatedcategoryID']>=0) { 
			if($row_rsProductPrefs['basketrelatedcategoryID']== 0) {
				
				$productID = isset($item['productID']) ? $item['productID']: 0;
				$categoryID = isset($item['productcategoryID']) ?  $item['productcategoryID'] : 0;
			} else {
				$productID  = 0;
				$categoryID = $row_rsProductPrefs['basketrelatedcategoryID'];
			}
		require_once('../includes/relatedProducts.inc.php');
		} ?>
    </div>
  </section>
  <?php  if($grandtotal<$row_rsProductPrefs['minimumorder']) { 
  echo "<style>.button_checkout { display: none; }</style>";
  } ?>
  <!-- InstanceEndEditable --></main>
<?php require_once('../../local/includes/footer.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsPromotionsWithCodes);

mysql_free_result($rsThisRegion);

mysql_free_result($rsProductPrefs);
?>
