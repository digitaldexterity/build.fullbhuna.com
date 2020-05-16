<?php require_once('../../Connections/aquiescedb.php'); ?><?php require_once('../../login/includes/login.inc.php'); ?>
<?php require_once('../includes/productHeader.inc.php'); ?>
<?php require_once('../includes/basketFunctions.inc.php'); ?>
<?php require_once('../../core/includes/framework.inc.php'); ?>
<?php require_once('../payments/includes/logtransaction.inc.php'); // includes sendmail ?>
<?php if(!isset($_GET['token']) || $_GET['token']!=md5($_GET['VendorTxCode'].PRIVATE_KEY)) {
	die("Security token error. Please check that the link was correct or try viewing from your user account.");
}

$regionID = (isset($regionID) && intval($regionID)>0) ? intval($regionID) : 1;

if(isset($_GET['login'])) {
	// some features are only available when logged in such as approval, so add to link if this is required
	if(!isset($_SESSION['MM_UserGroup'])) {
		$url = "/login/index.php?accesscheck=".urlencode($_SERVER['REQUEST_URI'])."&msg=".urlencode("You need to be logged in to view this page.");
		header("location: ".$url); exit;
	}
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
$query_rsLoggedIn = sprintf("SELECT ID, usertypeID, firstname, surname FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);





$colname_rsOrderDetails = "-1";
if (isset($_GET['VendorTxCode'])) {
  $colname_rsOrderDetails = $_GET['VendorTxCode'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsOrderDetails = sprintf("SELECT productorders.*, productorderproducts.uploadID, productorderproducts.ID AS orderID, productorderproducts.Price, productorderproducts.Quantity, productorderproducts.dispatched, product.title, product.imageURL,productoptions.optionname, productoptions.price AS optionprice, productorderproducts.optiontext, productoptions.stockcode AS optionsku, product.sku, track_session.ID, track_session.referer, billingcountries.fullname AS billingcountryname, deliverycountries.fullname AS deliverycountryname, discovered.`description` AS howdiscovered, modifiedby.firstname, modifiedby.surname, productcategory.title AS categoryname, product.vattype, productcategory.vatdefault, productcategory.vatincluded, productmanufacturer.manufacturershipping , CONCAT(productforuser.firstname,' ',productforuser.surname ) AS productforusername   FROM productorders LEFT JOIN productorderproducts ON (productorders.VendorTxCode = productorderproducts.VendorTxCode) LEFT JOIN product ON (productorderproducts.productID = product.ID) LEFT JOIN productoptions ON (productoptions.ID = productorderproducts.optionID) LEFT JOIN track_session ON (productorders.sessionID = track_session.ID) LEFT JOIN countries AS billingcountries ON (billingcountries.ID = productorders.billingcountryID) LEFT JOIN countries AS deliverycountries ON (deliverycountries.ID = productorders.deliverycountryID) LEFT JOIN discovered ON (productorders.discovered = discovered.ID) LEFT JOIN users AS modifiedby ON (productorders.modifiedbyID = modifiedby.ID)  LEFT JOIN productcategory ON (product.productcategoryID = productcategory.ID) LEFT JOIN productmanufacturer ON (product.manufacturerID = productmanufacturer.ID)  LEFT JOIN users AS productforuser ON (productorderproducts.productforuserID = productforuser.ID)  WHERE productorders.VendorTxCode = %s GROUP BY productorderproducts.ID", GetSQLValueString($colname_rsOrderDetails, "text"));
$rsOrderDetails = mysql_query($query_rsOrderDetails, $aquiescedb) or die(mysql_error());
$row_rsOrderDetails = mysql_fetch_assoc($rsOrderDetails);
$totalRows_rsOrderDetails = mysql_num_rows($rsOrderDetails);

$colname_rsPromos = "-1";
if (isset($_GET['VendorTxCode'])) {
  $colname_rsPromos = $_GET['VendorTxCode'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPromos = sprintf("SELECT productorderpromos.ID, productorderpromos.promotionID, productorderpromos.amount, productpromo.promotitle FROM productorderpromos LEFT JOIN productpromo ON (productorderpromos.promotionID = productpromo.ID) WHERE VendorTxCode = %s", GetSQLValueString($colname_rsPromos, "text"));
$rsPromos = mysql_query($query_rsPromos, $aquiescedb) or die(mysql_error());
$row_rsPromos = mysql_fetch_assoc($rsPromos);
$totalRows_rsPromos = mysql_num_rows($rsPromos);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsProductPrefs = "SELECT * FROM productprefs WHERE ID = ".$regionID."";
$rsProductPrefs = mysql_query($query_rsProductPrefs, $aquiescedb) or die(mysql_error());
$row_rsProductPrefs = mysql_fetch_assoc($rsProductPrefs);
$totalRows_rsProductPrefs = mysql_num_rows($rsProductPrefs);

$select = "SELECT * FROM region WHERE ID = ".intval($regionID);
$result = mysql_query($select, $aquiescedb) or die(mysql_error().": ".$select);
$row_rsThisRegion = mysql_fetch_assoc($result);


// approval required and able to give approval?		
if(isset($row_rsLoggedIn['ID'])) {	
	$select = "SELECT productaccount.groupID AS gID, productaccount.approverrankID FROM productaccount LEFT JOIN usergroupmember ON (productaccount.groupID = usergroupmember.groupID)  LEFT JOIN usergroupmember AS usergroupmember2 ON (productaccount.groupID = usergroupmember2.groupID)  WHERE productaccount.statusID =1 AND (productaccount.regionID = 0 OR productaccount.regionID = ".$regionID.") AND usergroupmember2.userID = ".$row_rsOrderDetails['userID']." AND usergroupmember.userID = ".$row_rsLoggedIn['ID']." AND approverrankID <= ".$row_rsLoggedIn['usertypeID']." LIMIT 1";
	$approvalresult = mysql_query($select, $aquiescedb) or die(mysql_error().": ".$select);
	if(mysql_num_rows($approvalresult)>0) {
		$approvalauthorised = true;
		if(isset($_POST['approved'])) {
			if($_POST['approved']==1) {
				$update = "UPDATE productorders SET  approvedbyID = ".$row_rsLoggedIn['ID'].", approveddatetime = '".date('Y-m-d H:i:s')."' WHERE VendorTxCode =  ".GetSQLValueString($_POST['VendorTxCode'], "text");
				mysql_query($update, $aquiescedb) or die(mysql_error());
				// send email to all concerned - just site admin for now as not sure if client should be involved in process
			$to =  $row_rsThisRegion['email'];
			$subject = "Order Approved by ".$row_rsLoggedIn['firstname']." ".$row_rsLoggedIn['surname'].": ".$_POST['VendorTxCode'];
			$message = "The order".$_POST['VendorTxCode']." has now been approved. Click on the link below to view:\n\n";
			$message .= getProtocol()."://".$_SERVER['HTTP_HOST']."/products/admin/orders/orderDetails.php?VendorTxCode=".$strVendorTxCode."\n\n";		
			sendMail($to, $subject, $message);
			} else if($_POST['approved']==-1) { // reject
				$update = "UPDATE productorders SET Status = 'CANCELLED', modifiedbyID = ".$row_rsLoggedIn['ID'].", LastUpdated = '".date('Y-m-d H:i:s')."'  WHERE VendorTxCode =  ".GetSQLValueString($_POST['VendorTxCode'], "text");
				mysql_query($update, $aquiescedb) or die(mysql_error());
				// send email to all concerned - just site admin for now as not sure if client should be involved in process
			$to =  $row_rsThisRegion['email'];
			$subject = "Order REJECTED by ".$row_rsLoggedIn['firstname']." ".$row_rsLoggedIn['surname'].": ".$_POST['VendorTxCode'];
			$message = "The order".$_POST['VendorTxCode']." has been rejected as is effectively cancelled.\n\nFor reference you can view the order here:\n\n";
			$message .= getProtocol()."://".$_SERVER['HTTP_HOST']."/products/admin/orders/orderDetails.php?VendorTxCode=".$strVendorTxCode."\n\n";		
			sendMail($to, $subject, $message);
			}
			
			
			header("location: ".$_SERVER['REQUEST_URI']); exit; // reload to refesh database
		}
	}
}


					
?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!DOCTYPE html>
<html class="" lang="en"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Order Details: ".$row_rsOrderDetails['VendorTxCode'];  echo $pageTitle." | ".$site_name; ?></title>
<!-- InstanceEndEditable -->
<!--[if IE]><![endif]-->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<link href="../css/defaultProducts.css" rel="stylesheet"  />
<?php if(isset($body_class)) $body_class .= " products ";  ?>
<!-- InstanceEndEditable -->
</head>
<body id="OffTemplate" class="bootstrap <?php echo $body_class;  ?>">
<?php require_once('../../local/includes/header.inc.php'); ?>
<main id="content"><!-- InstanceBeginEditable name="Body" -->
       <div class="container"> <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
          <li><a href="index.php" class="link_undo"><i class="glyphicon glyphicon-arrow-left"></i> Back to orders</a></li><li>
 <a href="../payments/invoice.php?VendorTxCode=<?php echo $row_rsOrderDetails['VendorTxCode']; ?>&token=<?php echo md5(PRIVATE_KEY.$row_rsOrderDetails['VendorTxCode']) ; ?>" target="_blank" rel="noopener">Show <?php echo isset($row_rsProductPrefs['text_invoice']) ? htmlentities($row_rsProductPrefs['text_invoice'], ENT_COMPAT, "UTF-8") : "Invoice"; ?></a></li>
</ul></div></nav>
    <h1>Order Details: <?php echo $row_rsOrderDetails['Status']; ?><?php echo $row_rsOrderDetails['ID']; ?>/<?php echo $row_rsOrderDetails['VendorTxCode']; ?></h1> 
<h2>Order Status: </h2> 
<form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>"><p>
<?php if(isset($approvalauthorised) && $row_rsOrderDetails['approvalrequired']==1 
&& !isset($row_rsOrderDetails['approvedbyID'])
&& $row_rsOrderDetails['Status'] !="CANCELLED"
) { ?><div class="notice alert message alert-info" role="alert">
<strong>APPROVAL REQUIRED:</strong> <label><input type="radio" name="approved" value="1" >  Approve</label> &nbsp;&nbsp;&nbsp;
<label><input type="radio" name="approved" value="-1" > Reject </label> &nbsp;&nbsp;&nbsp; <input name="VendorTxCode" type="hidden" value="<?php echo $row_rsOrderDetails['VendorTxCode']; ?>"><button type="submit" class="btn brn-primary">Save</button></div>
<?php } ?>




    </p></form>
   
    <table class="table table-hover">
    <thead>
      <tr>
        <th>Product</th>
        <th class="text-right">Price</th>
        <th class="text-right">Quantity</th>
        <th class="text-right">Dispatched</th>
      </tr></thead><tbody>
      <?php do { 
	  
	  $vatinc = ($row_rsOrderDetails['vatdefault']==0) ? $row_rsOrderDetails['vatincluded'] : $row_rsProductPrefs['vatincluded'];
		$vatrate = ($row_rsOrderDetails['vattype']>0) ? $row_rsThisRegion['vatrate'] : 0;
		$prices = vatPrices($row_rsOrderDetails['Price'], $vatinc, $vatrate); 
		
		
		?>
        <tr>
          <td><img src="<?php echo getImageURL($row_rsOrderDetails['imageURL'],$row_rsProductPrefs['imagesize_basket']); ?>" class="<?php echo $row_rsProductPrefs['imagesize_basket']; ?>" /><?php echo $row_rsOrderDetails['categoryname']." - ".$row_rsOrderDetails['title']; ?> <?php echo (isset($row_rsOrderDetails['optionname']) && $row_rsOrderDetails['optionname'] !="") ? " [".$row_rsOrderDetails['optionname']."]" : ""; ?> <?php echo (isset($row_rsOrderDetails['optiontext']) && $row_rsOrderDetails['optiontext']!="") ? " [".$row_rsOrderDetails['optiontext']."]" : ""; ?> <?php echo (isset($row_rsOrderDetails['optionsku']) && $row_rsOrderDetails['optionsku']!="") ? $row_rsOrderDetails['optionsku'] : $row_rsOrderDetails['sku'];  echo isset($row_rsOrderDetails['productforusername']) ? "; For ".$row_rsOrderDetails['productforusername'] : ""; ?>
          <?php if($row_rsOrderDetails['uploadID']) { $select = "SELECT newfilename FROM uploads WHERE ID = ".intval($row_rsOrderDetails['uploadID']); 
		  $result = mysql_query($select, $aquiescedb);
		  $upload = mysql_fetch_assoc($result);
		  
		  echo "<a href=\"/Uploads/". str_replace(UPLOAD_ROOT,"",$upload['newfilename'])."\" target=\"_blank\">Download</a>"; } ?><div class="shippinginfo">
            <div class="manufacturershipping"><?php echo $row_rsOrderDetails['manufacturershipping']; ?></div></div></td>
          <td class="text-right"><?php  echo $currency.number_format($prices['gross'],2,".",","); ?></td>
          <td class="text-right"><?php echo $row_rsOrderDetails['Quantity']; ?></td>
          <td>
           <?php echo ($row_rsOrderDetails['dispatched']==1) ? "YES" : ""; ?></td>
        </tr>
       
        <?php } while ($row_rsOrderDetails = mysql_fetch_assoc($rsOrderDetails)); 
		 mysql_data_seek($rsOrderDetails,0); $row_rsOrderDetails = mysql_fetch_assoc($rsOrderDetails); ?> 
         <?php if($totalRows_rsPromos>0) { 
		 do { ?>
         <tr>
           <td><strong><?php echo $row_rsPromos['promotitle']; ?>:</strong></td><td  class="text-right"><?php echo $currency.number_format(-1*$row_rsPromos['amount'],2,".",","); ?></td><td>&nbsp;</td><td>&nbsp;</td></tr>
         <?php } while($row_rsPromos = mysql_fetch_assoc($rsPromos));
		 }?>
        <tr>
          <td> <strong>Shipping:</strong> 
    </td>
          <td class="text-right"><?php echo $currency.number_format($row_rsOrderDetails['shipping'],2,".",","); ?></td>
          <td colspan="2">&nbsp;</td>
        </tr>
        <tr>
          <td><strong>TOTAL</strong> inc shipping & promos (<?php echo $row_rsOrderDetails['TxType']; ?>) </td>
          <td class="text-right"><strong><?php echo $currency.$row_rsOrderDetails['Amount']; ?></strong></td>
          <td colspan="2">&nbsp;</td>
        </tr>
       
        <tr>
          <td><strong>PAID</strong></td>
          <td class="text-right"><?php echo isset($row_rsOrderDetails['AmountPaid']) ? number_format($row_rsOrderDetails['AmountPaid'],2,".",",") : ""; ?></td>
          <td colspan="2">&nbsp;</td>
        </tr></tbody>
    </table>
<br><br>

      <table class="form-table">
       <tr><td class="top">
      <p><strong>Delivery Address:</strong></p><?php if($row_rsOrderDetails['deliverysame']==2) { ?><p><strong>WILL COLLECT</strong></p>
      <?php } else { ?>
      <?php if($row_rsOrderDetails['deliverytime']!="") { ?><p><strong>Preferred delivery time: <?php echo htmlentities($row_rsOrderDetails['deliverytime'], ENT_COMPAT, "UTF-8"); ?></strong></p>
      <?php } ?>
      <?php if($row_rsOrderDetails['deliveryinstructions']!="") { ?><p><strong><?php echo htmlentities($row_rsOrderDetails['deliveryinstructions'], ENT_COMPAT, "UTF-8"); ?></strong></p>
      <?php } ?>
      <p>
        <?php  echo $row_rsOrderDetails['DeliveryFirstnames']; ?>
        <?php echo $row_rsOrderDetails['DeliverySurname']; ?><br />
                 <?php echo $row_rsOrderDetails['DeliveryCompany']; ?><br />
        <?php echo $row_rsOrderDetails['DeliveryAddress1']; ?><br />
        <?php echo $row_rsOrderDetails['DeliveryAddress2']; ?><br />
        <?php echo $row_rsOrderDetails['DeliveryCity']; ?><br />
        <?php echo $row_rsOrderDetails['DeliveryPostCode']; ?><br />
        <?php echo $row_rsOrderDetails['DeliveryState']; ?><br />
        <?php echo isset($row_rsOrderDetails['deliverycountryname']) ? $row_rsOrderDetails['deliverycountryname']: $row_rsOrderDetails['DeliveryCountry']; ?></p>
      <?php } ?>
      <p><strong>Phone:</strong> <?php echo $row_rsOrderDetails['DeliveryPhone']; ?></p>
      <p><strong>Customer email:</strong> <a href="mailto:<?php echo $row_rsOrderDetails['CustomerEMail']; ?>"><?php echo $row_rsOrderDetails['CustomerEMail']; ?></a></p></td><td class="top"> <p><strong>Billing Address:</strong></p>
      <p>
        <?php  echo $row_rsOrderDetails['BillingFirstnames']; ?>
        <?php echo $row_rsOrderDetails['BillingSurname']; ?><br />
         <?php echo $row_rsOrderDetails['BillingCompany']; ?><br />
        <?php echo $row_rsOrderDetails['BillingAddress1']; ?><br />
        <?php echo $row_rsOrderDetails['BillingAddress2']; ?><br />
        <?php echo $row_rsOrderDetails['BillingCity']; ?><br />
        <?php echo $row_rsOrderDetails['BillingPostCode']; ?><br />
        <?php echo $row_rsOrderDetails['BillingState']; ?><br />
        <?php echo isset($row_rsOrderDetails['billingcountryname']) ? $row_rsOrderDetails['billingcountryname']: $row_rsOrderDetails['BillingCountry']; ?></p>
      <p><strong>Phone:</strong> <?php echo $row_rsOrderDetails['BillingPhone']; ?></p>
      <p><strong>Mobile:</strong> <?php echo $row_rsOrderDetails['BillingMobile']; ?></p>
      
       <p><strong>VAT Number:</strong> <?php echo isset($row_rsOrderDetails['VATnumber']) ? $row_rsOrderDetails['VATnumber'] : "N/A"; ?></p>
         <p><strong>PO Number:</strong> <?php echo isset($row_rsOrderDetails['purchaseorder']) ? $row_rsOrderDetails['purchaseorder'] : "N/A"; ?></p>
      
       <?php if($row_rsOrderDetails['checkoutanswer1']!="") { ?><p><strong><?php echo $row_rsProductPrefs['checkoutquestion1']; ?>:</strong><br><?php echo nl2br(htmlentities($row_rsOrderDetails['checkoutanswer1'], ENT_COMPAT, "UTF-8")); ?></p>
      <?php } ?>
      
      
      </td></tr></table>
  </div>
    <!-- InstanceEndEditable --></main>
<?php require_once('../../local/includes/footer.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsOrderDetails);

mysql_free_result($rsPromos);

mysql_free_result($rsProductPrefs);

mysql_free_result($rsLoggedIn);
?>