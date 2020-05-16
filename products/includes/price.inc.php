<?php 

global $row_rsProduct, $row_rsThisCategory,$row_rsProductPrefs, $row_rsThisRegion, $currency;

$price =0;

if(is_readable(SITE_ROOT."local/includes/price.inc.php")) { // is plug in
include(SITE_ROOT."local/includes/price.inc.php");

} else {
	
				switch($row_rsThisRegion['currencycode']) {
				case "GBP" :  $currency = "&pound;"; break;
				case "EUR" :  $currency = "&euro;"; break;
				case "USD" :  $currency = "$"; break;
				default :  $currency = $row_rsThisRegion['currencycode']." "; }
				
				switch($row_rsProduct['pricetype']) {
					case 1 : $pricetype = ""; break;
					case 2 : $pricetype =  " per kg"; break;
					case 3 : $pricetype =  " per hour"; break;
					case 4 : $pricetype =  " per day"; break;
					case 0 : $pricetype = " ".@$row_rsProduct['priceper']; break;
				}  ?>

<div class="productprice" itemprop="offers" itemscope itemtype="http://schema.org/Offer">
  <?php $vatdefault = isset($row_rsThisCategory['vatdefault']) ? $row_rsThisCategory['vatdefault'] : (isset($row_rsProduct['vatdefault']) ? $row_rsProduct['vatdefault'] : 0);
$vatincluded = ($vatdefault ==0 && isset($row_rsThisCategory['vatincluded'])) ? $row_rsThisCategory['vatincluded'] : ($vatdefault ==0 && isset($row_rsProduct['vatincluded']) ? $row_rsProduct['vatincluded'] : (isset($row_rsProductPrefs['vatincluded']) ? $row_rsProductPrefs['vatincluded'] : 1));
$vatprice = ($vatdefault ==0 && isset($row_rsThisCategory['vatprice'])) ? $row_rsThisCategory['vatprice'] : ($vatdefault ==0 && isset($row_rsProduct['vatprice']) ? $row_rsProduct['vatprice'] : (isset($row_rsProductPrefs['vatprice']) ? $row_rsProductPrefs['vatprice'] : 0));
$vattext = ($vatdefault ==0 && isset($row_rsThisCategory['vattext'])) ? $row_rsThisCategory['vattext'] : (isset($row_rsProductPrefs['vattext']) ? $row_rsProductPrefs['vattext'] : 0); ?>
  <?php if (@$row_rsProduct['saleitem']==1) { ?>
  <div class="saleitem">Offer</div>
  <?php } ?>
  <?php $rrp = 0; if (isset($row_rsProduct['listprice']) && $row_rsProduct['listprice']>0 && $row_rsProduct['listprice']>$row_rsProduct['price']) { ?>
  <span class="rrp"> <span class="pricetext label">Was:&nbsp;</span> <span class="pricecurrency" content="<?php echo $row_rsThisRegion['currencycode']; ?>"><?php echo $currency; ?></span> <span class="priceamount"><?php $rrp = ($row_rsProduct['area']>0 && ((isset($row_rsProductPrefs['useareaquantity']) && $row_rsProductPrefs['useareaquantity']==1) || (isset($row_rsProduct['showareaprice']) && $row_rsProduct['showareaprice']==1))) ? number_format(($row_rsProduct['listprice']/$row_rsProduct['area']),2,".",",") : number_format($row_rsProduct['listprice'],2,".",","); echo $rrp;  ?></span> <?php } ?>
  </span>
 
  <span class="sellingprice" >
  <?php if(isset($row_rsProduct['price']) && $row_rsProduct['price']>0) { ?>
  <span class="pricetext label">Price:&nbsp;</span>
  <?php if(@$row_rsProduct['addfrom'] ==1) { ?>
  <span class="pricefrom">From&nbsp;</span>
  <?php } ?>
  
  <span class="pricecurrency" itemprop="priceCurrency" content="<?php echo $row_rsThisRegion['currencycode']; ?>"><?php echo $currency; ?></span>
  <?php 
  $price =  $row_rsProduct['price']; 
  if($row_rsProduct['area']>0 && ((isset($row_rsProductPrefs['showareaprice']) && $row_rsProductPrefs['showareaprice']==1) || (isset($row_rsProduct['showareaprice']) && $row_rsProduct['showareaprice']==1))) {
	  $price =  floatval($row_rsProduct['price']/$row_rsProduct['area']);
	  $areaprice =  number_format($price,2,".",",")."/m&sup2;";
	 
  }
  $price = isset($areaprice) ? $areaprice : number_format($price,2,".",","); 
  
	

?>
  <span class="priceamount" <?php echo ($vatincluded==1) ? "itemprop=\"price\" content=\"".$price."\"" : ""; ?>><?php echo $price; ?></span></span>&nbsp;<span class="pricetype"> <?php echo $pricetype; echo isset($areaprice) ? "<span class=\"area\">/m&sup2;</span>" : ""; ?> </span>
  <?php if($vattext==1) { ?>
  <span class="vattext">
  <?php  echo ($vatincluded==0) ? "<span class=\"excluding\"> ex. VAT</span>" : "<span class=\"including\"> inc. VAT</span>"; ?>
  </span>
  <?php } ?>
  <?php if($vatprice==1 && @$row_rsProduct['price']>0 && $row_rsProduct['vattype']>0) { 
				$vatrate = ($row_rsProduct['vattype']>1) ? $row_rsProduct['ratepercent'] : $row_rsThisRegion['vatrate'];
				$vatprice = ($vatincluded==1) ? ($row_rsProduct['price']/(1+$vatrate/100)) : ($row_rsProduct['price']*(100+$vatrate)/100);
				$vatprice =  number_format($vatprice,2,".",","); ?>
  <span class="incvatprice" <?php echo ($vatincluded==0) ? "itemprop=\"price\" content=\"".$vatprice."\"" : ""; ?>> <?php echo "(".$currency.$vatprice;
					 echo ($vatincluded==1) ? " ex. VAT)" : " inc. VAT)"; ?> </span>
  <?php } 
  } else {  // price == 0
  
	  ?>
     <span class="priceamount no-price" ><?php echo isset($row_rsProduct['nopricetext']) ? $row_rsProduct['nopricetext'] : "Please call for price"; ?></span>
<?php }  ?>
  
  
  
  
  
  <?php if($row_rsProduct['shippingexempt']==1) { ?>
  <span class="shippingexempt">Includes Delivery</span>
  <?php } ?>
  
  
  
  <?php $saving = $rrp-$price; if ($saving>0 && isset($row_rsProduct['listprice']) && $row_rsProduct['listprice']>0) { ?>
  <span class="savings"> <span class="pricetext label">You save:&nbsp;</span> <span class="pricecurrency"><?php echo $currency; ?></span><span class="priceamount"><?php echo number_format($saving,2,".",","); echo (isset($row_rsProductPrefs['showareaprice']) && $row_rsProductPrefs['showareaprice']==1 && $row_rsProduct['area']>0) ? "<span class=\"area\">/m&sup2;</span>": "";   ?> </span><span class="pricetype"> <?php echo $pricetype; ?> </span></span>
  <?php } ?>
  
  
  
  
  
   <?php if(isset($row_rsProduct['area']) && $row_rsProduct['area']>0 && isset($areaprice) && $areaprice>0) { // show other details ?>
  <span class="quantitypersqm"><span class="pricetext label">Quantity per m&sup2;:&nbsp;</span><span><span class="decimal"><?php $exact = (1/$row_rsProduct['area']); $approx = round($exact,3); echo $approx; ?></span><?php echo $exact!=$approx ? " (approx)" : "";  ?></span></span> <span class="sqmcovered"><span class="pricetext label">Area covered (m&sup2;)</span><span><?php echo $row_rsProduct['area']; ?></span></span> <span class="nonareaprice"> <span class="pricetext label">Price per item:&nbsp;</span> <span class="pricecurrency"><?php echo $currency; ?></span><span class="priceamount"><?php echo number_format($row_rsProduct['price'],2,".",","); ?> </span><span class="pricetype"> <?php echo $pricetype; ?> </span></span>
  <?php } // end show other details 
 
 
 
 /* AVAILABILITY https://schema.org/ItemAvailability */
  
  
 if(isset($row_rsProduct['instock']) && $row_rsProduct['instock']>0) { ?>
 <span class="stockstatus instock" ><span class="stockdescriptor">Availability:</span> <span class="count"><?php echo $row_rsProduct['instock']; ?></span> 
 <?php if(isset($row_rsProduct['availabledate']) && $row_rsProduct['availabledate']>date('Y-m-d')) { ?>
 
 <span itemprop="availability" content="OutOfStock"> from </span> <span class="availabledate"><?php echo date('d M Y', strtotime($row_rsProduct['availabledate'])); ?></span>
 <?php } else { ?>
  <span itemprop="availability" content="InStock">in stock</span> 
<?php } ?> 
  </span>
 <?php } ?>
 
 
 <!-- Shipping info -->
 
 
  <?php if(isset($isProductPage)) { // ONLY on product page as we cannot have link within link ?><div class="manufacturershipping"><?php echo $row_rsProduct['manufacturershipping']; ?><?php echo isset($row_rsProduct['availabledate']) && $row_rsProduct['availabledate']>date('Y-m-d') ? " Available from ".date('d M Y', strtotime($row_rsProduct['availabledate'])).". " : ""; ?><?php echo showDeliveryTime($row_rsProduct['mindeliverytime'],$row_rsProduct['maxdeliverytime'],$row_rsProduct['deliveryperiod'], $row_rsProduct['availabledate'],$row_rsProductPrefs['shippingendofday']);  ?></div>
  <?php if(isset($row_rsProductPrefs['shippinginfoURL'])) { ?>
  <a class="shippinginfolink fancybox  fancybox.iframe" href="<?php echo $row_rsProductPrefs['shippinginfoURL']; ?>" <?php if($row_rsProductPrefs['shippinginfonewwindow']==1) echo "target=\"_blank\""; ?>><span>Delivery information</span></a>
  <?php } } ?>
 
 
 <!-- end Shipping info -->
  
</div><!-- end price -->
<?php } // end no plug in ?>
