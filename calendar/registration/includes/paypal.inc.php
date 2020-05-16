<form action="https://www.paypal.com/cgi-bin/webscr" method="post" role="form">
        <input type="submit" name="makepayment" id="makepayment" value="Make payment..." /><br />
      <img src="/products/payments/paypal/paymenticons.png" alt="All major credit/debit cards accepted" width="353" height="46" />
      <input name="email" type="hidden" id="email" value="<?php echo $row_rsRegistrant['email']; ?>" />
      <input name="first_name" type="hidden"  id="first_name" value="<?php echo $row_rsRegistrant['firstname']; ?>" />
      <input name="last_name" type="hidden" id="last_name" value="<?php echo $row_rsRegistrant['surname']; ?>" />
      <input name="address1" type="hidden"  id="address1" value="<?php echo $row_rsRegistrant['address1']; ?>" />
      <input name="address2" type="hidden"id="address2" value="<?php echo $row_rsRegistrant['address2']; ?>" />
      <input name="city" type="hidden" id="city" value="<?php echo $row_rsRegistrant['address3']; ?>" />
      <input name="zip" type="hidden" id="zip" value="<?php echo $row_rsRegistrant['postcode']; ?>" />
      <input name="countryID" type="hidden" id="countryID" value="" />
      <input type="hidden" name="currency_code" value="GBP" />
      <?php 
?>
      <input type="hidden" name="return" value="<?php echo $returnURL; ?>" />
     
     
      <input type="hidden" name="cancel_return" value="<?php echo $row_rsProductPrefs['failURL']; ?>" />
      
      
      <input type="hidden" name="on0" value="Registration numbers" />
      <input type="hidden" name="os0" value="<?php echo $registrationnumbers; ?>" />
       <input type="hidden" name="on1" value="Transaction ID" />
      <input type="hidden" name="os1" value="<?php echo $strVendorTxCode ?>" />
      <input type="hidden" name="cmd" value="_xclick" />
      <input type="hidden" name="business" value="<?php echo  $row_rsProductPrefs['paymentclientID']; ?>" />
      <input type="hidden" name="item_name" value="<?php echo $row_rsEvent['eventtitle']." - ".$registrationnumbers; ?>" />
      <input type="hidden" name="item_number" value="<?php echo $strVendorTxCode ?>" />
      <input type="hidden" name="shipping" value="" />
      <input type="hidden" name="notify_url" value="<?php echo (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == "on") ? "https://" : "http://"; echo $_SERVER['HTTP_HOST']; ?>/products/payments/paypal/ipn.php" />
      <input type="hidden" name="amount" value="<?php echo $total; ?>" />
      <img alt="" src="https://www.paypal.com/en_GB/i/scr/pixel.gif" width="1" height="1" />
      
       <!-- force to gro to credit card rather than log in-->
          <input type="hidden" name="landing_page" value="billing" />
    </form>