<?php require_once('../../Connections/aquiescedb.php'); ?><?php require_once('../includes/basketFunctions.inc.php'); ?>
<?php require_once('../includes/productHeader.inc.php'); ?>
<?php 
$regionID = (isset($regionID) && intval($regionID)>0)  ? intval($regionID) : (isset($_SESSION['regionID'])  && $_SESSION['regionID']>0? $_SESSION['regionID'] : 1 ); ?>
<?php
 


// can take invoice EITHER from invoiceID OR vendorTXCode
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



if(isset($row_rsProductPrefs['invoiceURL']) && trim($row_rsProductPrefs['invoiceURL'])!="") {
	$url = $row_rsProductPrefs['invoiceURL'];
	$url .= strpos($row_rsProductPrefs['invoiceURL'],"?")===false ? "?" : "&";
	$url .= isset($_GET['VendorTxCode']) ? "VendorTxCode=".urlencode($_GET['VendorTxCode']) : "";
	$url .= isset($_GET['token']) ? "&token=".urlencode($_GET['token']) : "";
	header("location: ".$url); exit;
}


$colname_rsOrderDetails = "-1";
if (isset($_GET['VendorTxCode'])) {
  $colname_rsOrderDetails = $_GET['VendorTxCode'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsOrderDetails = sprintf("SELECT productorders.*, productorderproducts.ID AS orderID, productorderproducts.Price, productorderproducts.Quantity, productorderproducts.dispatched, product.title, productoptions.optionname, productoptions.price AS optionprice, productorderproducts.optiontext, productoptions.stockcode AS optionsku, product.sku,product.area, billingcountries.fullname AS billingcountryname, deliverycountries.fullname AS deliverycountryname,product.vattype, productcategory.vatdefault, productcategory.vatincluded FROM productorders LEFT JOIN productorderproducts ON (productorders.VendorTxCode = productorderproducts.VendorTxCode) LEFT JOIN product ON (productorderproducts.productID = product.ID) LEFT JOIN productcategory ON (product.productcategoryID = productcategory.ID) LEFT JOIN productoptions ON (productoptions.ID = productorderproducts.optionID) LEFT JOIN countries AS billingcountries ON (billingcountries.ID = productorders.billingcountryID) LEFT JOIN countries AS deliverycountries ON (deliverycountries.ID = productorders.deliverycountryID) WHERE productorders.VendorTxCode = %s", GetSQLValueString($colname_rsOrderDetails, "text"));
$rsOrderDetails = mysql_query($query_rsOrderDetails, $aquiescedb) or die(mysql_error());
$row_rsOrderDetails = mysql_fetch_assoc($rsOrderDetails);
$totalRows_rsOrderDetails = mysql_num_rows($rsOrderDetails);

$regionID = isset($row_rsOrderDetails['regionID']) ? $row_rsOrderDetails['regionID'] : $regionID; // user order region not site

$colname_rsPromos = "-1";
if (isset($_GET['VendorTxCode'])) {
  $colname_rsPromos = $_GET['VendorTxCode'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPromos = sprintf("SELECT promotionID, amount, productpromo.promotitle, productpromo.promodetails FROM productorderpromos LEFT JOIN productpromo ON (productorderpromos.promotionID = productpromo.ID) WHERE VendorTxCode = %s GROUP BY promotionID", GetSQLValueString($colname_rsPromos, "text"));
$rsPromos = mysql_query($query_rsPromos, $aquiescedb) or die(mysql_error());
$row_rsPromos = mysql_fetch_assoc($rsPromos);
$totalRows_rsPromos = mysql_num_rows($rsPromos);



$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsRegion = "SELECT vatrate, imageURL, vatnumber, region.address, region.postcode, region.telephone, region.fax FROM region WHERE ID = $regionID";
$rsRegion = mysql_query($query_rsRegion, $aquiescedb) or die(mysql_error());
$row_rsRegion = mysql_fetch_assoc($rsRegion);
$totalRows_rsRegion = mysql_num_rows($rsRegion);

$promodetails = "";

ob_start();

 ?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!DOCTYPE html>
<html class="" lang="en"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = isset($row_rsProductPrefs['text_invoice']) ? htmlentities($row_rsProductPrefs['text_invoice'], ENT_COMPAT, "UTF-8") : "Invoice"; $pageTitle .= ": ".$row_rsOrderDetails['VendorTxCode']; echo $pageTitle," | ".$site_name; ?>
</title>
<!-- InstanceEndEditable -->
<!--[if IE]><![endif]-->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<link href="../css/defaultProducts.css" rel="stylesheet"  />
<style><!--
@media print {
  .container {
    width:100% !important;
  }
}
--></style>
<!-- InstanceEndEditable -->
</head>
<body id="OffTemplate" class="bootstrap <?php echo $body_class;  ?>">
<?php require_once('../../local/includes/header.inc.php'); ?>
<main id="content"><!-- InstanceBeginEditable name="Body" -->
  <section>
    <div class="container">
      <?php // authenticate - has token, is admin, or is user
	if((isset($_GET['token']) && $_GET['token'] == md5(PRIVATE_KEY.$row_rsOrderDetails['VendorTxCode'])) 
	|| (isset($_SESSION['MM_UserGroup']) && $_SESSION['MM_UserGroup']>7)
	|| ($row_rsOrderDetails['userID']==$row_rsLoggedIn['ID'])) { // auth ?>
      <h1 ><?php echo isset($row_rsProductPrefs['text_invoice']) ? htmlentities($row_rsProductPrefs['text_invoice'], ENT_COMPAT, "UTF-8") : "Invoice"; ?>: <?php echo $row_rsOrderDetails['ID']; ?> TX Code: <?php echo $row_rsOrderDetails['VendorTxCode']; ?>
        <button type="button" class="hidden-print btn btn-default btn-secondary" onclick="window.print();" ><i class="glyphicon glyphicon-print"></i> Print</button>
        <?php if(is_readable("../../local/includes/dompdf-master/dompdf_config.inc.php")) { ?>
        <button type="button" class="hidden-print  btn btn-default btn-secondary" onclick="document.location='<?php echo $_SERVER['REQUEST_URI']."&pdf=true"; ?>';" >PDF</button>
        <?php } ?>
      </h1>
      <table class="form-table">
      <tbody>
        <tr>
          <td><?php if(isset($row_rsRegion['imageURL']) && $row_rsRegion['imageURL']!="") { ?>
            <img src="/Uploads/<?php echo $row_rsRegion['imageURL']; ?>" />
            <?php } ?></td>
          <td><p><?php echo $row_rsRegion['title']; ?><br><?php echo nl2br($row_rsRegion['address']); ?> <?php echo isset($row_rsRegion['postcode']) ? "<br>".$row_rsRegion['postcode'] : ""; ?> <?php echo isset($row_rsRegion['telephone']) ? "<br>Telephone: ".$row_rsRegion['telephone'] : ""; ?> <?php echo isset($row_rsRegion['fax']) ? "<br>Fax: ".$row_rsRegion['fax'] : ""; ?>  </p></td>
        </tr>
        <tr>
          <td class="top"><p><strong><?php echo isset($row_rsProductPrefs['text_invoice']) ? htmlentities($row_rsProductPrefs['text_invoice'], ENT_COMPAT, "UTF-8") : "Invoice"; ?> to:</strong><br>
              <?php  echo htmlentities($row_rsOrderDetails['BillingFirstnames'], ENT_COMPAT, "UTF-8"); ?>
              <?php echo htmlentities($row_rsOrderDetails['BillingSurname'], ENT_COMPAT, "UTF-8"); ?><br />
               <?php echo isset($row_rsOrderDetails['BillingCompany']) ?  htmlentities($row_rsOrderDetails['BillingCompany'], ENT_COMPAT, "UTF-8")."<br>" : ""; ?>
                <?php echo isset($row_rsOrderDetails['BillingAddress1']) ?  htmlentities($row_rsOrderDetails['BillingAddress1'], ENT_COMPAT, "UTF-8")."<br>" : ""; ?>
                 <?php echo isset($row_rsOrderDetails['BillingAddress2']) ?  htmlentities($row_rsOrderDetails['BillingAddress2'], ENT_COMPAT, "UTF-8")."<br>" : ""; ?>
                  <?php echo isset($row_rsOrderDetails['BillingCity']) ?  htmlentities($row_rsOrderDetails['BillingCity'], ENT_COMPAT, "UTF-8")." " : ""; ?>
                   <?php echo isset($row_rsOrderDetails['BillingPostCode']) ?  htmlentities($row_rsOrderDetails['BillingPostCode'], ENT_COMPAT, "UTF-8")."<br>" : ""; ?>
                    <?php echo isset($row_rsOrderDetails['BillingState']) ?  htmlentities($row_rsOrderDetails['BillingState'], ENT_COMPAT, "UTF-8")."<br>" : ""; ?>
                     <?php echo isset($row_rsOrderDetails['billingcountryname']) ?  htmlentities($row_rsOrderDetails['billingcountryname'], ENT_COMPAT, "UTF-8")."<br>" : ""; ?>
                     
                      
                      
                      
            </p> </td>
          <td class="top"><p><strong>Date:</strong> <?php echo isset($row_rsOrderDetails['createddatetime']) ?  date('d M Y', strtotime($row_rsOrderDetails['createddatetime'])) : date('d M Y', strtotime($row_rsOrderDetails['LastUpdated'])); ?><?php echo isset($row_rsRegion['vatnumber']) ? "<br><strong>VAT Number:</strong> ".$row_rsRegion['vatnumber'] : ""; ?></p>
            <p>&nbsp;</p></td>
        </tr></tbody>
      </table>
     
      <?php if ($totalRows_rsOrderDetails > 0) { // Show if recordset not empty ?>
        <table class="table table-hover" style="max-width:100%" >
        <thead>
          <tr>
            <th>Product</th>
            <th class="text-right">Quantity</th>
            <th class="text-right">Net</th>
            <th class="text-right">VAT</th>
              <th class="text-right">Gross</th>
            <?php if(isset($_SESSION['MM_UserGroup']) && $_SESSION['MM_UserGroup']>=8) { ?>
            <?php } ?>
          </tr></thead>
          <?php   
		  $grosstotal = 0;
		    $netotal = 0;
		$vattotal = 0; 
		
		
		
		
		do { 
		
		$vatinc = ($row_rsOrderDetails['vatdefault']==0) ? $row_rsOrderDetails['vatincluded'] : $row_rsProductPrefs['vatincluded'];
		$vatrate = ($row_rsOrderDetails['vattype']>0) ? $row_rsThisRegion['vatrate'] : 0;
		$prices = vatPrices($row_rsOrderDetails['Price'], $vatinc, $vatrate); 
		
		?><tbody>
          <tr>
            <td><?php echo htmlentities(strip_tags($row_rsOrderDetails['title']), ENT_COMPAT, "UTF-8"); ?> <?php echo (isset($row_rsOrderDetails['optionname']) && $row_rsOrderDetails['optionname'] !="") ? " [".htmlentities($row_rsOrderDetails['optionname'], ENT_COMPAT, "UTF-8")."]" : ""; ?> <?php echo (isset($row_rsOrderDetails['optiontext']) && $row_rsOrderDetails['optiontext']!="") ? " [".htmlentities($row_rsOrderDetails['optiontext'], ENT_COMPAT, "UTF-8")."]" : ""; ?> <?php echo (isset($row_rsOrderDetails['optionsku']) && $row_rsOrderDetails['optionsku']!="") ? htmlentities($row_rsOrderDetails['optionsku'], ENT_COMPAT, "UTF-8") : htmlentities($row_rsOrderDetails['sku'], ENT_COMPAT, "UTF-8"); ?></td>
            <td class="text-right"><?php echo $row_rsOrderDetails['Quantity']; ?></td>
            <td class="text-right"><?php  $net =  number_format(($row_rsOrderDetails['Quantity']*$prices['net']),2,".",""); echo $currency.$net; $nettotal +=$net;?></td>
            <td class="text-right"><em>
            <?php  $vat =  number_format(($row_rsOrderDetails['Quantity']*$prices['vat']),2,".",""); $vattotal += $vat; echo $currency.$vat; ?>
            </em></td>
             <td class="text-right"><?php  $gross =  number_format(($row_rsOrderDetails['Quantity']*$prices['gross']),2,".",""); $grosstotal += $gross; echo $currency.$gross; ?></td>
            <?php if(isset($_SESSION['MM_UserGroup']) && $_SESSION['MM_UserGroup']>=8) { ?>
            <?php } ?>
          </tr>
          <?php } while ($row_rsOrderDetails = mysql_fetch_assoc($rsOrderDetails)); 
		 mysql_data_seek($rsOrderDetails,0); $row_rsOrderDetails = mysql_fetch_assoc($rsOrderDetails); ?>
          <?php if($totalRows_rsPromos>0) { 
		 do { if($row_rsPromos['amount']>0) {  ?>
          <tr>
            <td><strong><?php echo $row_rsPromos['promotionID']." - ".htmlentities($row_rsPromos['promotitle'], ENT_COMPAT, "UTF-8"); $promodetails .= "<h3>".$row_rsPromos['promotitle']."</h3><p>".$row_rsPromos['promodetails']."</p>"; ?></strong></td>
            <td>&nbsp;</td>
            <td  class="text-right"><?php $promos = vatPrices($row_rsPromos['amount']*-1, $row_rsProductPrefs['vatincluded'], $row_rsThisRegion['vatrate']); $net =  $promos['net']; echo $currency.number_format($net,2,".",","); $nettotal +=$net; ?>
             </td>
           <td  class="text-right"><em>
            <?php $vat =  $promos['vat']; echo $currency.number_format($vat,2,".",","); $vattotal +=$vat; ?>
            </em></td> <td  class="text-right"><?php $gross =  $promos['gross']; echo $currency.number_format($gross,2,".",","); $grosstotal +=$gross; ?></td>
            
          </tr>
          <?php }
		 } while($row_rsPromos = mysql_fetch_assoc($rsPromos));
		 mysql_data_seek($rsPromos,0);
		 $row_rsPromos = mysql_fetch_assoc($rsPromos); }?>
          <tr>
            <td><strong><?php echo $row_rsProductPrefs['text_shipping']; ?></strong></td>
            <td>&nbsp;</td>
            <td class="text-right"><?php  $shipping = vatPrices($row_rsOrderDetails['shipping'], $row_rsProductPrefs['vatincluded'], $row_rsThisRegion['vatrate']); 
			
			$net =  $shipping['net']; echo $currency.number_format($net,2,".",","); $nettotal +=$net; ?></td>
            <td class="text-right"><em>
            <?php $vat =  $shipping['vat']; echo $currency.number_format($vat,2,".",","); $vattotal +=$vat; ?>
            </em></td> <td class="text-right"><?php $gross =  $shipping['gross']; echo $currency.number_format($gross,2,".",","); $grosstotal +=$gross; ?></td>
            
           </tr>
          <tr>
            <td><strong><?php echo $row_rsProductPrefs['grandtotaltext']; ?></strong> (<?php echo $row_rsOrderDetails['TxType']; ?>) </td>
            <td><!-- QTY --></td>
            <td class="text-right"><strong><?php echo $currency.number_format($nettotal,2,".",","); //echo $currency.number_format($row_rsOrderDetails['Amount'],2,".",","); ?></strong></td>
            <td  class="text-right"><em><?php echo $currency.number_format($vattotal,2,".",","); ?></em></td>
             <td  class="text-right"><strong><?php echo $currency.number_format($grosstotal,2,".",","); ?></strong></td>
            <?php if(isset($_SESSION['MM_UserGroup']) && $_SESSION['MM_UserGroup']>=8) { ?>
            <?php } ?>
          </tr></tbody>
        </table>
        <?php } // Show if recordset not empty ?>
     
      <hr />
      <?php echo (isset($row_rsOrderDetails['deliveryinstructions']) && trim($row_rsOrderDetails['deliveryinstructions'])!="") ? "<p class=\"notice\">".$row_rsOrderDetails['deliveryinstructions']."</p>" : ""; ?>
      <table class="form-table">
        <tr>
          <td><p><strong>Delivery Address:</strong><br>
            <?php if($row_rsOrderDetails['deliverysame']==2) { ?>
            <strong>WILL COLLECT</strong>
            <?php } else { ?>
     
              <?php  echo htmlentities($row_rsOrderDetails['DeliveryFirstnames']." ".$row_rsOrderDetails['DeliverySurname'], ENT_COMPAT, "UTF-8"); ?>
              <br />
             
              
              
               <?php echo isset($row_rsOrderDetails['BillingCompany']) ?  htmlentities($row_rsOrderDetails['BillingCompany'], ENT_COMPAT, "UTF-8")."<br>" : ""; ?>
               <?php echo isset($row_rsOrderDetails['DeliveryAddress1']) ?  htmlentities($row_rsOrderDetails['DeliveryAddress1'], ENT_COMPAT, "UTF-8")."<br>" : ""; ?>
               
                 <?php echo isset($row_rsOrderDetails['DeliveryAddress2']) ?  htmlentities($row_rsOrderDetails['DeliveryAddress2'], ENT_COMPAT, "UTF-8")."<br>" : ""; ?>
                  <?php echo isset($row_rsOrderDetails['DeliveryCity']) ?  htmlentities($row_rsOrderDetails['DeliveryCity'], ENT_COMPAT, "UTF-8")." " : ""; ?>
                   <?php echo isset($row_rsOrderDetails['DeliveryPostCode']) ?  htmlentities($row_rsOrderDetails['DeliveryPostCode'], ENT_COMPAT, "UTF-8")."<br>" : ""; ?>
                    <?php echo isset($row_rsOrderDetails['DeliveryState']) ?  htmlentities($row_rsOrderDetails['DeliveryState'], ENT_COMPAT, "UTF-8")."<br>" : ""; ?>
                    <?php echo isset($row_rsOrderDetails['deliverycountryname']) ?  htmlentities($row_rsOrderDetails['deliverycountryname'], ENT_COMPAT, "UTF-8")."<br>" : ""; ?>
            <?php } ?></p>
            <p><strong>Phone:</strong> <?php echo htmlentities($row_rsOrderDetails['DeliveryPhone'], ENT_COMPAT, "UTF-8"); ?></p></td>
          <td class="top"><p><strong>Payment status: <?php echo (preg_match("/(ACCEPTED|COMPLETED)/i",$row_rsOrderDetails['Status'])) ? "PAID IN FULL" : $row_rsOrderDetails['Status']; ?></strong></p>
           <p><strong>PO Number:</strong>  <?php echo htmlentities($row_rsOrderDetails['purchaseorder'], ENT_COMPAT, "UTF-8"); ?></p>
            <p><strong>VAT Rate:</strong> <?php echo $row_rsRegion['vatrate']; ?>%</p>
            <?php if(isset($row_rsProductPrefs['bacsdetails'])) { ?>
            <p><strong>BACS Details:</strong><br />
              <?php echo nl2br($row_rsProductPrefs['bacsdetails']); ?></p>
            <?php } ?></td>
        </tr>
      </table>
      <?php if(trim($promodetails) !="") { ?>
      <h2>Promotion details</h2>
      <?php echo $promodetails; }
		   } else { ?>
      <p class="alert warning alert-warning" role="alert">Sorry, this <?php echo isset($row_rsProductPrefs['text_invoice']) ? htmlentities($row_rsProductPrefs['text_invoice'], ENT_COMPAT, "UTF-8") : "Invoice"; ?> is unavailable to you. You may need to <a href="../../login/index.php?accesscheck=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>">log in</a>.</p>
      <?php } ?>
    </div>
  </section>
  <!-- InstanceEndEditable --></main>
<?php require_once('../../local/includes/footer.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php if(isset($_GET['pdf'])) {
	require_once("../../local/includes/dompdf-master/dompdf_config.inc.php");

	$html = ob_get_contents();
	ob_end_clean(); 

	$html = preg_replace('/<head>.+?<\/head>/is', '<head></head>', $html); // remove contents of head tag
	
	 // absolute server path for images
	$html = preg_replace('#/local/#is', SITE_ROOT.'local/', $html);
	$html = preg_replace('#/core/#is', SITE_ROOT.'core/', $html);
	$html = preg_replace('#/Uploads/#is', SITE_ROOT.'Uploads/', $html);
	
	$pdfhead = "<style  type=\"text/css\">  header, nav, footer, .noprint { display:none; } </style>";
	$html = str_replace("<head>", "<head>".$pdfhead, $html); // add PDF custom head instead
	
	
$dompdf = new DOMPDF();
$dompdf->load_html($html);
$dompdf->render();
$dompdf->stream("invoice-".$row_rsOrderDetails['VendorTxCode'].".pdf");
	
} else {
	ob_end_flush();
}

?>
<?php
mysql_free_result($rsOrderDetails);

mysql_free_result($rsPromos);

mysql_free_result($rsLoggedIn);

mysql_free_result($rsRegion);

mysql_free_result($rsProductPrefs);
?>