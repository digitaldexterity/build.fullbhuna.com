<?php require_once(SITE_ROOT."core/includes/framework.inc.php"); ?>
<?php if (!function_exists("getBasket")) {
	require_once(SITE_ROOT."products/includes/basketFunctions.inc.php");
}


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
mysql_select_db($database_aquiescedb, $aquiescedb);
 
$defaultImage = isset($row_rsProductPrefs['defaultImageURL']) ? "/Uploads/".$row_rsProductPrefs['imagesize_basket'].$row_rsProductPrefs['defaultImageURL'] : "/products/images/".$row_rsProductPrefs['imagesize_basket']."no_image.gif";
$vatinc = $row_rsProductPrefs['vatincluded'];
$nettotal =0;
$totalweight = 0;
$totalitems = 0;
$totalshippingitems = 0;
$totalprice = 0;
$totalvat = 0;
$shippingtotal = 0;
$shippingnettotal = 0;
$noshipinternational = 0;
$promototal = 0;
$promonettotal = 0;
$grandtotal = 0;
$totalarea = 0;
$basketDescription = "";
$cookieDescription = ""; 
$sagedescriptionlines = 0;
$sagepaydescription = "";
$freeshipping = 0;
$excludenetprice = 0;
$excludearea = 0;
$ga_script = "";
$oneoff = 0; // totals of all IDs - will be still zero if one-off only



if(!isset($hidebasket)) { ?>

<div id="basketcontents">
  <?php  } // end dont hide basket 
if(isset($_SESSION['basket']) && count($_SESSION['basket'])>0) { 
	switch($row_rsThisRegion['currencycode']) {
		case "GBP" : $currency = "&pound;"; break;
		case "EUR" : $currency = "&euro;"; break;
		case "USD" : $currency = "$"; break;
		default : $currency = $row_rsThisRegion['currencycode']." "; }
	if(!isset($hidebasket)) { ?>
  <form action="/products/basket/index.php<?php echo isset($_GET['returnURL']) ? "?returnURL=".urlencode($_GET['returnURL']) : ""; ?>" method="post" >
    <input name="updatebasket" type="hidden" value="true" />
    <table  class="table table-hover table-basket" >
      <thead>
        <tr>
          <th class="basket-column-image">&nbsp;</th>
          <th class="basket-column-description"><?php echo $row_rsProductPrefs['itemtext']; ?></th>
          <th class="basket-column-price-m2">Price/m&sup2;</th>
          <th class="basket-column-price"><?php echo $row_rsProductPrefs['pricetext']; ?></th>
          <th class="basket-column-vat"><?php echo $row_rsProductPrefs['taxname']; ?></th>
          <th class="basket-column-qty"><?php echo $row_rsProductPrefs['quantitytext']; ?></th>
          <th class="basket-column-update"><?php echo isset($row_rsProductPrefs['text_update']) ? htmlentities($row_rsProductPrefs['text_update'], ENT_COMPAT, "UTF-8") : "Update."; ?></th>
          <th class="basket-column-weight">Weight</th>
          <th class="basket-column-total"><?php echo $row_rsProductPrefs['subtotaltext']; ?></th>
          <th class="basket-column-remove">&nbsp;</th>
        </tr>
      </thead>
      <tbody>
        <?php }
	$items = array();
	$items = getBasket(); 
	foreach($items as $key => $item) {
			$vatinc = ($item['vatdefault']==0) ? $item['vatincluded'] : $row_rsProductPrefs['vatincluded'];
			$vatrate =  $item['ratepercent'] ;
			$price = vatPrices($item['price'], $vatinc, $vatrate);
			$itemtotal = $item['quantity']*$price['net'];
			$totalvat +=  $item['quantity']*$price['vat'];			
			$nettotal += $itemtotal;
			$excludepromo = false;
			
			$oneoff +=$item['productID'];
			
			if(function_exists("productLink")) {
				$productlink = productLink($item['productID'], $item['productlongID'], $item['productcategoryID'], $item['categorylongID']) ;
			} else {
				$productlink = addslashes($_SERVER['REQUEST_URI']);
			}
		if(!isset($hidebasket)) {  ?>
        <tr class="basket-row-product">
          <td  class="basket-column-image"><span  class="basketbasket-column-image">
            <?php if(isset($item['imageURL'])) { ?>
            <a href="<?php echo $productlink; ?>"><img src="<?php echo getImageURL($item['imageURL'],$row_rsProductPrefs['imagesize_basket']); ?>" class="<?php echo $row_rsProductPrefs['imagesize_basket']; ?>" /></a>
            <?php } ?>
            </span></td>
          <td class="basket-column-description"><a href="<?php echo $productlink; ?>"><span class="sku"><?php echo $item['sku']; ?> </span>
            <?php   echo strip_tags($item['title'], '<strong><em><a>')." ".strip_tags($item['explanation'], '<strong><em><a>');?>
            <?php echo ($item['hazardous']==1) ? "<span class=\"hazardous\"> (Hazardous)</span>" : ""; ?>
            <?php if ($item['excludepromotions']==1) { echo  "<span class=\"excludepromotions\">*</span>"; $excludepromo = true; } 
			if($item['noshipinternational']==1) { 
			echo  "<span class=\"noshipinternational\"> (Cannot be shipped internationally)</span>"; $noshipinternational++ ;
			} ; ?>
            </a>
            <div class="shippinginfo">
              <div class="manufacturershipping"> <?php echo $item['manufacturershipping']; ?><?php echo isset($item['availabledate']) && $item['availabledate']>date('Y-m-d') ? " available from ".date('d M Y', strtotime($item['availabledate'])).". " : ""; ?>
                <?php if(function_exists("showDeliveryTime")) echo showDeliveryTime($item['mindeliverytime'],$item['maxdeliverytime'],$item['deliveryperiod'], $item['availabledate'], $row_rsProductPrefs['shippingendofday']).".";  ?>
              </div>
            </div></td>
          <td class="basket-column-price-m2"><?php if($item['area']>0) { 
			echo $currency.number_format(($item['price']/$item['area']),2,".",""); } ?></td>
          <td class="basket-column-price"><?php echo (isset($item['listprice']) && $item['listprice']>0) ? "<s>".$currency.number_format($item['listprice'],2,".",",")."</s>" : "" ; ?> <?php echo $currency.number_format($item['price'],2,".",",") ; ?></td>
          <td class="basket-column-vat"><?php echo $item['ratepercent']; ?>%</td>
          <td class="basket-column-qty"><span class="updateablequantity form-inline">
            <input  name="<?php $option = $item['optionID']; $option .= isset($item['optiontext']) ? " @@ ".$item['optiontext'] : "";  
			echo "quantity[".$item['productID']."][".base64_encode($option)."]"; ?>" type="text"  value="<?php echo intval($item['quantity']); ?>" size="3" maxlength="6" onfocus="document.getElementById('updatequantitybutton').style.display='inline';"  class="form-control text-right"/>
            </span><span class="fixedquantity">
            <?php  echo intval($item['quantity']);  ?>
            </span></td>
          <td class="basket-column-update" ><a href="/products/basket/index.php?addtobasket=true&productID=<?php echo $item['productID']; ?>&optionID=<?php echo urlencode($item['option']); ?>" class="basketadd"><img src="/core/images/icons/list-add.png" alt="Add icon" width="16" height="16" style="vertical-align:
middle;" /></a>&nbsp;<a href="/products/basket/index.php?removefrombasket=true&quantity=1&productID=<?php echo $item['productID']; ?>&optionID=<?php echo urlencode($item['option']); ?>"  class="basketremove"><img src="/core/images/icons/list-remove.png" alt="Remove icon" width="16" height="16" style="vertical-align:
middle;" /></a></td>
          <td class="basket-column-weight"><?php echo (($item['quantity']*$item['weight'])>0) ? $item['quantity']*$item['weight']." kgs" : "N/A"; ?></td>
          <td  class="basket-column-total"><?php  echo $currency.number_format($item['quantity']*$item['price'],2,".",",");  ?></td>
          <td class="basket-column-remove"><a href="/products/basket/index.php?removefrombasket=true&amp;quantity=0&amp;productID=<?php echo $item['productID']; ?>&amp;optionID=<?php echo urlencode($item['option']); ?>" onclick="return confirm('<?php echo isset($row_rsProductPrefs['text_confirmremovebasket']) ? htmlentities($row_rsProductPrefs['text_confirmremovebasket'], ENT_COMPAT, "UTF-8") : "Are you sure you want to remove this item from your basket?."; ?>');" class="btn btn-sm btn-default btn-secondary"><i class="glyphicon glyphicon-trash"></i> <?php echo isset($row_rsProductPrefs['text_remove']) ? htmlentities($row_rsProductPrefs['text_remove'], ENT_COMPAT, "UTF-8") : "Remove."; ?></a></td>
        </tr>
        <?php  }  
		  
		 if($item['area']>0 && $item['excludepromotions']!=1) { 
			 $totalarea += $item['area']*$item['quantity']; 
			 $excludearea =  $excludepromo ? $item['area']*$item['quantity'] : 0;} 
			$excludenetprice += $excludepromo ? $itemtotal : 0;
		$totalitems += $item['quantity'];
		$totalshippingitems += ($item['shippingexempt']==0) ? $item['quantity'] : 0;
  		
		
		$basketDescription .= $item['title'] ." x ".$item['quantity'].";   ";
 		$sagepaydescription .= appendSagePay($item['title'],$item['price'],$item['quantity'], $item['vattype']);
		$cookieDescription .= $item['productID']."|".$item['optionID']."|".$item['optiontext']."|".$item['quantity']."^";
		
		
   
  	} // end  for each item
 
  if($totalitems >0) { // if items in cart do promo check, shipping then final total...

  		$nettotal = ($nettotal > 0) ? $nettotal : 0; 
		$vatadded = 0; $promo = 0; $shipping = 0;
		
		$discounts = getPromotions(); 
		
	foreach($discounts as $key => $discount) {
			   if(isset($discount['amount']) ) { 
			   if(!isset($hidebasket) &&    ($discount['addbasket']== 1  || $discount['amount']>0)) {
			   ?>
        <tr class = "basket-row-promos">
          <td class="basket-column-image"><!--image column--></td>
          <td class="basket-column-description"><?php echo $discount['name'].":"; ?></td>
          <td class="basket-column-price-m2">&nbsp;</td>
          <td class="basket-column-price">&nbsp;</td>
          <td class="basket-column-vat">&nbsp;</td>
          <td class="basket-column-qty">&nbsp;</td>
          <td class="basket-column-update">&nbsp;</td>
          <td class="basket-column-weight">&nbsp;</td>
          <td class="basket-column-total"><?php  echo ($discount['amount'] > 0) ? "-".$currency.number_format($discount['amount'],2,".",",") : "Included"; ?></td>
          <td class="basket-column-remove">&nbsp;</td>
        </tr>
        <?php } // end no hide basket
				
				
				
				$basketDescription .= $discount['name'].";   ";
				
				$promoamount = vatPrices($discount['amount'],$row_rsProductPrefs['vatincluded'],$row_rsThisRegion['vatrate']);
				
				
				
				$promototal += number_format($promoamount['gross'],2,".",",");
					$promonettotal += number_format($promoamount['net'],2,".",",");
					$sagepaydescription .= appendSagePay($discount['name'],$promoamount['net'],1,1);
			   }
   
	} // end each discount	
	
	
	if($totalvat>0 && $promototal>0) {
	
	
	
	
		$totalvat -= ($promototal - $promonettotal);

	}
	
			
				
	// shipping if not collection
		if($oneoff>0) { // not one-off only where we don't show/add shipping or VAT
		$freeshipping = ($freeshipping>=count($_SESSION['basket'])) ? $freeshipping : 0; // only add free shipping if all items qualify for promotion
		$shipping = getShipping($items);
		if(is_array($shipping)) { 
			 foreach($shipping as $key => $shippingitem) { 
		if(!isset($hidebasket)) { ?>
        <tr class="basket-row-shipping <?php echo ($shippingitem['amount'] ==0) ? $row_rsProductPrefs['text_free'] : ""; ?>">
          <td class="basket-column-image"><!--image column--></td>
          <td class="basket-column-description"><?php echo isset($shippingitem['name']) ? $shippingitem['name'].":" : $row_rsProductPrefs['text_shipping'].":"; ?></td>
          <td class="basket-column-price-m2">&nbsp;</td>
          <td class="basket-column-price">&nbsp;</td>
          <td class="basket-column-vat">&nbsp;</td>
          <td class="basket-column-qty">&nbsp;</td>
          <td class="basket-column-update">&nbsp;</td>
          <td class="basket-column-weight">&nbsp;</td>
          <td class="basket-column-total"><?php echo ($shippingitem['amount'] ==0) ? $row_rsProductPrefs['text_free'] : $currency.number_format($shippingitem['amount'],2,".",","); ?></td>
          <td class="basket-column-remove">&nbsp;</td>
        </tr>
        <?php }// end no hide basket
	$shippingamount = vatPrices($shippingitem['amount'],$row_rsProductPrefs['vatincluded'],$row_rsThisRegion['vatrate']);
		$shippingtotal += $shippingamount['gross'];
		$shippingnettotal += $shippingamount['net'];
		$totalvat += $shippingamount['vat'];
		
	$sagepaydescription .= appendSagePay($shippingitem['name'],$shippingamount['net'],1,1);
	}
		}
	
		
		
   	
  		
		if($vatinc!=1) { // price does not include VAT  
	
		
		if(!isset($hidebasket)) { ?>
        <tr class="basket-row-vat vat">
          <td class="basket-column-image"><!--image column--></td>
          <td class="basket-column-description"><?php echo isset($row_rsProductPrefs['subtotaltext']) ? htmlentities($row_rsProductPrefs['subtotaltext'], ENT_COMPAT, "UTF-8") : "Sub total"; ?> (before VAT): </td>
          <td class="basket-column-price-m2">&nbsp;</td>
          <td class="basket-column-price">&nbsp;</td>
          <td class="basket-column-vat">&nbsp;</td>
          <td class="basket-column-qty">&nbsp;</td>
          <td class="basket-column-update">&nbsp;</td>
          <td class="basket-column-weight">&nbsp;</td>
          <td  class="basket-column-total"><?php echo $currency.number_format($nettotal+$shippingnettotal-$promonettotal,2,".",","); ?></td>
          <td class="basket-column-remove">&nbsp;</td>
        </tr>
        <tr class="basket-row-vat vat">
          <td class="basket-column-image"><!--image column--></td>
          <td class="basket-column-description"><?php echo $row_rsProductPrefs['taxname']; ?>:</td>
          <td class="basket-column-price-m2">&nbsp;</td>
          <td class="basket-column-price">&nbsp;</td>
          <td class="basket-column-vat">&nbsp;</td>
          <td class="basket-column-qty">&nbsp;</td>
          <td class="basket-column-update">&nbsp;</td>
          <td class="basket-column-weight">&nbsp;</td>
          <td class="basket-column-total"><?php  echo $currency.number_format($totalvat,2,".",","); ?></td>
          <td class="basket-column-remove">&nbsp;</td>
        </tr>
        <?php } // end no hide basket
		  } else { // price does  include VAT  
	
		
		if(!isset($hidebasket)) { ?>
        <tr class="basket-row-vat vat">
          <td class="basket-column-image"><!--image column--></td>
          <td class="basket-column-description">(<?php echo $row_rsProductPrefs['taxname']; ?>):</td>
          <td class="basket-column-price-m2">&nbsp;</td>
          <td class="basket-column-price">&nbsp;</td>
          <td class="basket-column-vat">&nbsp;</td>
          <td class="basket-column-qty">&nbsp;</td>
          <td class="basket-column-update">&nbsp;</td>
          <td class="basket-column-weight">&nbsp;</td>
          <td  class="basket-column-total text-nowrap">(
            <?php  echo $currency.number_format($totalvat,2,".",",");  ?>
            )</td>
          <td class="basket-column-remove">&nbsp;</td>
        </tr>
        <?php } // end no hide basket
		  } // end does not include VAT
		  
		  	} // end not one-off
		
		  $nettotal = $nettotal-$promonettotal+$shippingnettotal; 
		  $nettotal = ($nettotal>0) ? $nettotal : 0; // make zero if less than
		  
		  $grandtotal = $nettotal + $totalvat; 
		   $grandtotal = ($grandtotal>0) ? $grandtotal : 0; // make zero if less than
		
			
			 if(!isset($hidebasket)) { ?>
        <tr class="basket-row-grand-total">
          <td class="basket-column-image"><!--image column--></td>
          <td class="basket-column-description"><?php echo isset($row_rsProductPrefs['grandtotaltext']) ? htmlentities($row_rsProductPrefs['grandtotaltext'], ENT_COMPAT, "UTF-8") : "Total"; ?>:<span class="updateablequantity">
            <button name="update" type="submit"  style="display:none;"  id="updatequantitybutton" class="btn btn-default btn-secondary"><?php echo $row_rsProductPrefs['updatebaskettext']; ?></button>
            </span></td>
          <td class="basket-column-price-m2">&nbsp;</td>
          <td class="basket-column-price">&nbsp;</td>
          <td class="basket-column-vat">&nbsp;</td>
          <td class="basket-column-qty">&nbsp;</td>
          <td class="basket-column-update">&nbsp;</td>
          <td class="basket-column-weight">&nbsp;</td>
          <td class="basket-column-total"><?php echo $currency.number_format($grandtotal,2,".",","); ?></td>
          <td class="basket-column-remove">&nbsp;</td>
        </tr>
        <?php 
			  }// end no hide basket
			  } if(!isset($hidebasket)) {?>
      </tbody>
    </table>
  </form>
  <?php 	}// end no hide basket
		  } // if session variable
	 
if(!isset($hidebasket)) { ?>
  <div id="basketOperations">
    <?php if($totalitems ==0) { // basket empty ?>
    <p><?php echo isset($row_rsProductPrefs['text_noitems']) ? htmlentities($row_rsProductPrefs['text_noitems'], ENT_COMPAT, "UTF-8") : "You have no items in your basket."; ?></p>
    <?php } else {  ?>
    <p id="saveBasket" class="javascriptOnly"><a href="javascript:void(0);" onclick="saveBasket(); return false;" >Save for later</a> | <a href="/products/basket/index.php?emptybasket=true" onclick="return confirm('Are you sure you wish to remove all items from your basket?');" >Empty basket</a> </p>
    <?php } ?>
    <p id="restoreBasket" class="javascriptOnly">You have a saved basket. <a href="javascript:void(0);" class="btn btn-default btn-secondary" onclick="restoreBasket(); return false;" role="button">Restore</a>. <a href="javascript:void(0);" class="btn btn-default btn-secondary" onclick="deleteBasket(false); return false;" role="button">Delete</a>.</p>
  </div>
  <input type="hidden" name="basketcontentsstring" id="basketcontentsstring" value="<?php echo htmlentities(strip_tags($cookieDescription), ENT_COMPAT, "UTF-8"); ?>" />
</div>
<input id="basket_grand_total" type="hidden" value="<?php echo number_format($grandtotal,2,".",","); ?>" />
<input id="basket_total_items" type="hidden" value="<?php echo $totalitems; ?>" />
<?php } // end hide basket 
  $_SESSION['basket_total_items'] = $totalitems ;
$_SESSION['basket_grand_total'] = $grandtotal; ?>
