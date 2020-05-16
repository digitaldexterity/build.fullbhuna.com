<div class="PayPalShowCartButton">
<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="paypal">
<input type="hidden" name="cmd" value="_cart">
<input type="hidden" name="business" value="<?php echo isset($row_rsThisRegion['paymentsystemID']) ? $row_rsThisRegion['paymentsystemID'] : $row_rsPreferences['paymentsystemID']; ?>">
 <input name="cartbutton" type="submit" class="button" id="cartbutton" value="<?php echo isset($row_rsThisRegion['link_basket']) ? $row_rsThisRegion['link_basket'] : "View Basket"; ?>" />
<input type="hidden" name="display" value="1">
</form>
</div>