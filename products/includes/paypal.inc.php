<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="paypal" onsubmit="if(this.os1.value == 'none') { alert('Please select an option from the drop down menu first.'); return false; } ">
  <?php if (isset( $row_rsProduct['inputfield']) && $row_rsProduct['inputfield'] != "0") { // backwoard compat as this used to be just a boolean ?>
  
  <div class="productinputquestion"><?php echo ($row_rsProduct['inputfield']!="1") ? "".$row_rsProduct['inputfield']."" : ""; ?></div><div class="productinputfield">
  <input type="hidden" name="on0" value="Notes" /><input name="os0" type="text"  id="os0" size="25" maxlength="255" >
  </div>
  <?php } ?>
  <?php $select = "SELECT * FROM productoptions WHERE productID = ".$row_rsProduct['ID']." AND statusID = 1";
  $result = mysql_query($select, $aquiescedb) or die(mysql_error());
  $row = mysql_fetch_assoc($result);

  if($row) { ?>
   <div class="productoptions">
 <input type="hidden" name="on1" value="Option" />
  <select name="os1">
    <option value="none">Please select option...</option>
    <?php do {  ?>
    <option value="<?php echo $row['optionname']?>"><?php echo $row['optionname']?></option>
    <?php } while ($row = mysql_fetch_assoc($result)); ?>
  </select>
</div>
  
  <?php } else { ?> <input type="hidden" name="os1" value="" /><?php } ?>
  <div id="productbuttons" >
  <button name="buybutton" type="submit" class="btn btn-primary" id="buybutton" ><?php echo isset($row_rsThisRegion['link_buynow']) ? $row_rsThisRegion['link_buynow'] : "Buy Now"; ?></button>
  <img alt="" src="https://www.paypal.com/en_GB/i/scr/pixel.gif" width="1" height="1" />
  <input type="hidden" name="add" value="1" />
  <input type="hidden" name="cmd" value="_cart" />
  <input type="hidden" name="business" value="<?php echo isset($row_rsThisRegion['paymentsystemID']) ? $row_rsThisRegion['paymentsystemID'] : $row_rsPreferences['paymentsystemID']; ?>" />
  <input type="hidden" name="item_name" value="<?php echo $row_rsProduct['product_title']; ?>" />
  <input type="hidden" name="amount" value="<?php echo number_format($row_rsProduct['price'],2); ?>" />
  <input type="hidden" name="no_shipping" value="0" />
  <input type="hidden" name="no_note" value="1" />
  <input type="hidden" name="currency_code" value="<?php echo isset($row_rsThisRegion['currencycode']) ? $row_rsThisRegion['currencycode'] : "GBP"; ?>" />
  <input type="hidden" name="lc" value="GB" />
  <input type="hidden" name="bn" value="PP-ShopCartBF" />
  </div>
</form>

