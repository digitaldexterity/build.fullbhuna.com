<?php // REQUIRES productFunctions.js
global $row_rsProduct, $row_rsProductPrefs, $currency, $totalRows_rsPurchaseAccount, $row_rsPurchaseAccount;

$instock =0;

if(@$row_rsProduct['price']>0 || (!isset($row_rsProduct['nopricebuy']) && $row_rsProductPrefs['nopricebuy']==1) ||  $row_rsProduct['nopricebuy']  ==1) {// has price
$select = "SELECT productoptions.*, productfinish.finishname, productfinish.imageURL, productversion.versionname FROM productoptions  LEFT JOIN productfinish ON (productoptions.finishID = productfinish.ID) LEFT JOIN productversion ON (productoptions.versionID = productversion.ID) WHERE productID = ".$row_rsProduct['ID']." AND productoptions.statusID = 1 GROUP BY productoptions.ID ORDER BY ordernum, ID";
$optionresult = mysql_query($select, $aquiescedb) or die(mysql_error());
if(mysql_num_rows($optionresult)>0) {
	while($option = mysql_fetch_assoc($optionresult)) {
		$instock+=$option['instock'];
	}
	mysql_data_seek($optionresult,0);
}
$instock += isset($row_rsProduct['instock']) ? $row_rsProduct['instock'] : 1;



	 if($instock>0 || isset($row_rsProduct['availabledate'])) { // in stock or available at a date in future so add basket button ?>
     
<form action="/products/basket/index.php" method="post" enctype="multipart/form-data" name="addtobasketform" class="addtobasketform" onsubmit="return addToBasket(this);">


   <div class="productinputquestions">
   <div <?php if(isset( $row_rsProduct['inputfield']) && $row_rsProduct['inputfield'] != "0") { $required = 1; } else { $required = 0; echo "style=\"display:none\""; } // backward compat as this used to be just a boolean ?>>
  <div class="productinputquestion" >
  	<?php echo ($row_rsProduct['inputfield']!="1") ? "".$row_rsProduct['inputfield']."" : ""; ?>
  </div>
  <div class="productinputfield">
    <input name="optiontext" type="text"  size="25" maxlength="255"  placeholder="Type here" ><input name="optiontextrequired" type="hidden" value="<?php echo $required; ?>" >
  </div></div>
   <div <?php if(isset( $row_rsProduct['inputfield2']) && $row_rsProduct['inputfield2'] != "") { $required = 1; } else { $required = 0;  echo "style=\"display:none\""; } ?>>

  <div class="productinputquestion">
  	<?php echo $row_rsProduct['inputfield2']; ?>
  </div>
  <div class="productinputfield">
    <input name="optiontext2" type="text"  size="25" maxlength="255"  placeholder="Type here" ><input name="optiontext2required" type="hidden" value="<?php echo $required; ?>" >
  </div></div>
  
  
  
  <div <?php if(isset( $row_rsProduct['inputfield3']) && $row_rsProduct['inputfield3'] != "") { $required = 1; } else { $required = 0;  echo "style=\"display:none\""; } ?>>

  <div class="productinputquestion">
  	<?php echo $row_rsProduct['inputfield3']; ?>
  </div>
  <div class="productinputfield">
    <input name="optiontext3" type="text"  size="25" maxlength="255"  placeholder="Type here" ><input name="optiontext3required" type="hidden" value="<?php echo $required; ?>" >
  </div></div>
  
  
  
 </div>
  
  
  
  
   <?php if (isset($row_rsProduct['fileupload']) && $row_rsProduct['fileupload'] > 0) { // upload file  ?>
  <div class="productfileupload">
  	<label>Upload file: <input name="filename[<?php echo @$row_rsProduct['ID']; ?>]" type="file" /></label>
  </div>
 
  <?php } ?>
  
  <?php $select = "SELECT productversion.ID, productversion.versionname FROM productversion  LEFT JOIN productwithversion ON (productwithversion.versionID = productversion.ID) WHERE productID = ".$row_rsProduct['ID']." ORDER BY ordernum";
  $result = mysql_query($select, $aquiescedb) or die(mysql_error());
  if(mysql_num_rows($result)>1) { // is options ?>
  <div class="productoptions version">
    
    <select name="version" title="Version/Size">
      <option value="none-chosen">Select version/size...</option>
      <?php while($row = mysql_fetch_assoc($result)) {  ?>
      <option><?php echo htmlentities($row['versionname'], ENT_COMPAT, "UTF-8");  ?></option>
      <?php } ?>
    </select>
  </div>
  <?php } // end options ?>
  
  
  <?php $select = "SELECT productfinish.ID, productfinish.finishname, productfinish.imageURL FROM productfinish  LEFT JOIN productwithfinish ON (productwithfinish.finishID = productfinish.ID) WHERE productID = ".$row_rsProduct['ID']."";
  $result = mysql_query($select, $aquiescedb) or die(mysql_error());
  if(mysql_num_rows($result)>1) { // is options ?>
  <div class="productoptions finish">
   <?php if(isset($row_rsProductPrefs['colourchooser']) && $row_rsProductPrefs['colourchooser']==2) {
	    while($row = mysql_fetch_assoc($result)) {  ?>
    
       <label class="text-nowrap">
      <input <?php if (isset($REQUEST['finishname']) && $REQUEST['finishname'] == $row['finishname']) {echo "checked=\"checked\"";} ?> type="radio" name="finish" value="<?php echo htmlentities($row['finishname'], ENT_COMPAT, "UTF-8");  ?>" ><div class="fb_avatar" style="background-image:url(<?php echo getImageURL($row['imageURL'], "thumb"); ?>); width:36px; height:36px;"><?php echo htmlentities($row['finishname'], ENT_COMPAT, "UTF-8");  ?></div>&nbsp;<?php echo htmlentities($row['finishname'], ENT_COMPAT, "UTF-8");  ?></label>
      <?php } 
   } else { ?>
    <select name="finish"  title="Colour/Finish">
      <option value="none-chosen">Select colour/finish...</option>
      <?php while($row = mysql_fetch_assoc($result)) {  ?>
      <option><?php echo htmlentities($row['finishname'], ENT_COMPAT, "UTF-8");  ?></option>
      <?php } ?>
    </select>
    <?php } ?>
  </div>
  <?php } // end options ?>
  
  
  <?php 
  if(mysql_num_rows($optionresult)>0) { // is options 
  ?>
  <div class="productoptions option">
    <input type="hidden" name="on1" value="Option"  />
    <?php if(mysql_num_rows($optionresult)==1) { $option = mysql_fetch_assoc($optionresult);?>
    <input type="hidden" name="optionID" value="<?php echo $option['ID']; ?>"  /><?php echo htmlentities($option['optionname']." ".$option['versionname']." ".$option['finishname'], ENT_COMPAT, "UTF-8"); echo ($option['weight']>0) ? "&nbsp;(".$option['weight']." kgs)" : "";    ?>
    <?php } else { // more than one option ?>
    <?php if(isset($row_rsProductPrefs['optionsdisplay']) && $row_rsProductPrefs['optionsdisplay']==2) { // radio 
    
    while($option = mysql_fetch_assoc($optionresult)) {  ?>
    
       <label class="text-nowrap instock<?php echo ($option['instock']>0) ? 1 : 0; ?> finish<?php echo $option['finishID']; ?> version<?php echo $option['versionID']; ?>">
      <input <?php if (isset($REQUEST['optionID']) && $REQUEST['optionID'] == $option['ID']) {echo "checked=\"checked\"";} ?> type="radio" name="optionID" value="<?php echo $option['ID'];  ?>">
      <div class="fb_avatar" style="background-image:url(<?php echo getImageURL($option['imageURL'], "thumb"); ?>); width:36px; height:36px;"><?php echo htmlentities($option['optionname'], ENT_COMPAT, "UTF-8");  ?></div>&nbsp;<?php echo htmlentities($option['optionname'], ENT_COMPAT, "UTF-8"); if($option['instock'] == 0) { echo " (Out of stock";
	  echo (isset($option['availabledate']) && $option['availabledate'] > date('Y-m-d')) ? date('d M Y') : "";
	  echo ")"; } ?></label>
      <?php } 
	  
	  
 	 } else { // select ?> <select name="optionID" title="Option">
      <option value="none-chosen">Select option...</option>
      <?php while($option = mysql_fetch_assoc($optionresult)) {  ?>
      <option value="<?php echo $option['ID']?>"><?php echo htmlentities($option['optionname'], ENT_IGNORE, "UTF-8")." ".htmlentities($option['versionname'], ENT_IGNORE, "UTF-8")." ".htmlentities($option['finishname'], ENT_IGNORE, "UTF-8"); echo ($option['weight']>0) ? "&nbsp;(".$option['weight']." kgs)" : "";  echo isset($option['price']) ? ($option['price']>0 ? "&nbsp;".$currency.number_format($option['price'],2,".",",") : "Call for price") : ""; 
	  if(isset($option['availabledate']) && $option['availabledate'] > date('Y-m-d')) {
		  echo " (available from ".date('d M Y' , strtotime($option['availabledate'])).")";
	  } else if($option['instock'] == 0) { 
	  	echo " (Out of stock)";
	  
	  } ?></option>
      <?php } ?>
      <!-- prevent text on mobile devices being truncated -->
       <optgroup label=""></optgroup>
    </select><?php } // end select ?><?php } // end more than one ?>
  </div>
  <?php } // end options ?>
  
  
  <?php /** SPECIAL ORDER FOR USER ID FOR ACCOUNT CUSTOMERS  **/
  if($totalRows_rsPurchaseAccount>0 && isset($row_rsPurchaseAccount['orderforlist']) && $row_rsPurchaseAccount['orderforlist'] == 1  && $row_rsPurchaseAccount['groupID']) {
  $select = "SELECT users.ID, firstname, surname FROM users LEFT JOIN usergroupmember ON users.ID = usergroupmember.userID WHERE usergroupmember.groupID = ".$row_rsPurchaseAccount['groupID']." ORDER BY surname"; 
   $result = mysql_query($select, $aquiescedb) or die(mysql_error());
  if(mysql_num_rows($result)>0) { // are options 
  
  ?>
  <select name="productforuserID" title="Option">
      <option value="">Select person who this is for...</option>
      <?php while($row = mysql_fetch_assoc($result)) {  ?>
      <option value="<?php echo $row['ID']?>"><?php echo htmlentities($row['firstname']." ".$row['surname'], ENT_COMPAT, "UTF-8");  ?></option>
      <?php } ?>
    </select> 
  <?php } }  ?>
  
  
  
  <div class="addToBasket"><?php if($row_rsProduct['auction']<2) { ?>
  <input type="hidden" name="productID" id="productID_<?php echo $row_rsProduct['ID']; ?>" value="<?php echo $row_rsProduct['ID']; ?>">
  <input type="hidden" name="producttitle"  value="<?php echo isset($row_rsProduct['title']) ? htmlentities($row_rsProduct['title'], ENT_COMPAT, "UTF-8") : ""; ?>">
  <input type="hidden" name="productsku"  value="<?php echo $row_rsProduct['sku']; ?>">
  
   <input type="hidden" name="area" value="<?php echo $row_rsProduct['area']; ?>">
    <input name="addtobasket" type="hidden" value="true" />
    <input name="returnURL" type="hidden"  value="<?php echo isset($catLink) ? $catLink : $_SERVER['REQUEST_URI'] ?>" />
   
    <input name="stockquantity" type="hidden"  value="<?php echo ($row_rsProductPrefs['stockcontrol']==1  && isset($row_rsProduct['instock'])) ? $row_rsProduct['instock'] : 10000; ?>" />
     
    <label class="productquantity"><span class="text_quantity">Quantity<?php if ($row_rsProduct['area']>0 && ((isset($row_rsProductPrefs['useareaquantity']) && $row_rsProductPrefs['useareaquantity']==1) && (isset($row_rsProduct['showareaprice']) && $row_rsProduct['showareaprice']==1))) { echo  " required in m&sup2;"  ?><input name="areaquantity" type="hidden" value="true" /><?php } ?>:</span>
    <input type="text" class="productquantity" name="quantity" id="quantity<?php echo isset($_GET['productID']) ?  "" : "_".$row_rsProduct['ID']; ?>" value="1" size="3" maxlength="5"></label>
    
    
   
    
    
    <button type="submit" name="submit[<?php echo $row_rsProduct['ID']; ?>]" id="submit_<?php echo $row_rsProduct['ID']; ?>"  class="btn btn-primary shopButton addbasket"><?php echo ($row_rsProduct['instock']===0 && isset($row_rsProduct['availabledate'])) ? "Pre-Order" : $row_rsProductPrefs['addtobasket']; ?></button>
    
    <?php $select = "SELECT productcategory.freesamplesku,productcategory.samplequote, othercategory.freesamplesku AS osku, productmanufacturer.freesamplesku AS msku FROM product LEFT JOIN productcategory ON (product.productcategoryID = productcategory.ID) LEFT JOIN productincategory ON (product.ID = productincategory.productID) LEFT JOIN productcategory AS othercategory ON (othercategory.ID = productincategory.categoryID) LEFT JOIN productmanufacturer ON (productmanufacturer.ID = product.manufacturerID) WHERE  product.ID = ".$row_rsProduct['ID']." AND (productcategory.freesamplesku IS NOT NULL OR othercategory.freesamplesku IS NOT NULL OR productmanufacturer.freesamplesku IS NOT NULL) LIMIT 1";  $result = mysql_query($select, $aquiescedb) or die(mysql_error()); 
	
	if(mysql_num_rows($result)>0) { 
	 $row = mysql_fetch_assoc($result);  ?>
     <input type="hidden" name="freesamplesku"  value="<?php echo isset($row['freesamplesku']) ? $row['freesamplesku'] : (isset($row['osku']) ? $row['osku'] : $row['msku']); ?>">
    <input type="hidden" name="predictedamount"  value="">
     <input type="hidden" name="sampleofID"  value="<?php echo $row_rsProduct['ID']; ?>"><button type="submit" name="sample"  class="btn btn-default btn-secondary shopButton freesample" <?php if($row['samplequote']==1) { ?>onclick="this.form.predictedamount.value = prompt('<?php echo (isset($row_rsProductPrefs['showareaprice']) && $row_rsProductPrefs['showareaprice']==1 && $row_rsProduct['area']>0) ? "Sqm" : "Number"; ?> required.\n(Leave as 0 if not known):','0');" <?php } ?>>Order sample</button>
    <?php } ?>
   
   
    
    
    
    <?php if(isset($row_rsProduct['nextproductID'])) { ?>
    <label><input type="checkbox" name="nextproductID" value="<?php echo $row_rsProduct['nextproductID'];?>" />Add <?php echo $row_rsProduct['nextproducttitle'];?></label>
    <?php } ?>
   
    <?php } // no auction only ?>
  </div>
</form>
<div class="auction">
<?php if($row_rsProductPrefs['auctions']==1 && $row_rsProduct['auction']>=1 && $row_rsProduct['instock']==1) { // auction 
if($row_rsProduct['auctionenddatetime']>date("Y-m-d H:i:s")) { // auction on 
$select = "SELECT amount  FROM productbid WHERE productID = ".$row_rsProduct['ID']." ORDER BY amount DESC";
$result = mysql_query($select, $aquiescedb) or die(mysql_error()); 
$bids = mysql_num_rows($result);
if($bids>0) { 
	 $row = mysql_fetch_assoc($result);
} ?>
<form action="/products/auction.php" method="get" class="bidform"><?php echo isset($row['amount']) ? "<a href=\"/products/auction.php?productID=".$row_rsProduct['ID']."\">Current bid: ".number_format($row['amount'],2)." (after ".$bids." bids)</a>" : "Starting bid: ".number_format($row_rsProduct['auction'],2); ?> Your bid: <input name="bid" type="text" size="6" maxlength="6" value="<?php echo isset($_POST['bid']) ? htmlentities($_POST['bid'], ENT_COMPAT, "UTF-8") : ""; ?>"  /><button type="submit" class="btn btn-primary" >Place Bid</button>
  <input type="hidden" name="productID" id="productID" value="<?php echo $row_rsProduct['ID']; ?>" />
</form>
<?php } else { //auction ended
 ?>
Bidding has ended for this item.
<?php } 

} // allow auction ?>
</div>
<?php }  else { // out of stock  ?>
<form action="/products/notifyme.php" method="get" class="stockstatus outOfStock">
<input name="categoryID" type="hidden" value="<?php echo isset($row_rsProduct['productcategoryID']) ? $row_rsProduct['productcategoryID'] : 0;   ?>" />
<input type="hidden" name="productID" value="<?php echo $row_rsProduct['ID']; ?>">

<p class="outOfStockText">Out of stock</p>
<label for="oosemail">Notify me when available</label>
 <div class="input-group">
 <input name="email" id="oosemail" type="email" class="form-control" value="<?php echo isset($row_rsLoggedIn['email']) ? htmlentities($row_rsLoggedIn['email'], ENT_COMPAT, "UTF-8"): ""; ?>" placeholder="Enter your email" />
 <span class="input-group-btn">
<button type="submit"  class="btn btn-default btn-secondary" ><i class="glyphicon glyphicon-envelope"></i></button></span></div>

  
  
</form>
<?php } // out of stock

} // has price ?><span class="localStorage favProducts"><span class=" favProducts add"  data-productID = "<?php echo $row_rsProduct['ID'];?>">Add to favourites</span></span>
