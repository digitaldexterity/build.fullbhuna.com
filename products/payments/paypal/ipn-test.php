<?php require_once('../../../Connections/aquiescedb.php'); ?>
<?php if(isset($_POST['business'])) {
	$to = "paul@digdex.co.uk";
	$subject = "TEST IPN";
	$message = "Busines: ".$_POST['business'];
	mail($to, $subject, $message);
	die();
}
?>


<form id="form1" name="form1" method="post" action="http://www.costley-hotels.com/products/payments/paypal/ipn.php">
  <p>
  <input type="submit" name="button" id="button" value="TEST IPN" />
    
  <!-- assign posted variables to local variables-->
  Post to: http://www.costley-hotels.com/products/payments/paypal/ipn.php</p>
  <p>  Post back to:<input type="text" name='test_tertuen_post_url' value = "http://build.fullbhuna.com/products/payments/paypal/ipn-test.php" /></p><p>
    <input type="text" name='item_name' value = "Shopping basket" />
    <br>
    <input type="text" name='business' value = "Test Business" /><br>
    <input type="text" name='item_number' value = "" /><br>
    <input type="text" name='payment_status' value = "" /><br>
    <input type="text" name='mc_gross' value = "" /><br>
    <input type="text" name='mc_currency' value = "" /><br>
    <input type="text" name='txn_id' value = "" /><br>
    <input type="text" name='receiver_email' value = "" /><br>
    <input type="text" name='receiver_id' value = "" /><br>
    <input type="text" name='quantity' value = "" /><br>
    <input type="text" name='num_cart_items' value = "" /><br>
    <input type="text" name='payment_date' value = "" /><br>
    <input type="text" name='first_name' value = "" /><br>
    <input type="text" name='last_name' value = "" /><br>
    <input type="text" name='payment_type' value = "" /><br>
    <input type="text" name='payment_status' value = "" /><br>
    <input type="text" name='payment_gross' value = "" /><br>
    <input type="text" name='payment_fee' value = "" /><br>
    <input type="text" name='settle_amount' value = "" /><br>
    <input type="text" name='memo' value = "" /><br>
    <input type="text" name='payer_email' value = "" /><br>
    <input type="text" name='txn_type' value = "" /><br>
    <input type="text" name='payer_status' value = "" /><br>
    <input type="text" name='address_street' value = "" /><br>
    <input type="text" name='address_city' value = "" /><br>
    <input type="text" name='address_state' value = "" /><br>
    <input type="text" name='address_zip' value = "" /><br>
    <input type="text" name='address_country' value = "" /><br>
    <input type="text" name='address_status' value = "" /><br>
    <input type="text" name='item_number' value = "" /><br>
    <input type="text" name='tax' value = "" /><br>
    <input type="text" name='option_name1' value = "" /><br>
    <input type="text" name='option_selection1' value = "" /><br>
    <input type="text" name='option_name2' value = "" /><br>
    <input type="text" name='option_selection2' value = "" /><br>
    <input type="text" name='for_auction' value = "" /><br>
    <input type="text" name='invoice' value = "" /><br>
    <input type="text" name='custom' value = "" /><br>
    <input type="text" name='notify_version' value = "" /><br>
    <input type="text" name='verify_sign' value = "" /><br>
    <input type="text" name='payer_business_name' value = "" /><br>
    <input type="text" name='payer_id' value = "" /><br>
    <input type="text" name='mc_currency' value = "" /><br>
    <input type="text" name='mc_fee' value = "" /><br>
    <input type="text" name='exchange_rate' value = "" /><br>
    <input type="text" name='settle_currency' value = "" /><br>
    <input type="text" name='parent_txn_id' value = "" /><br>
    <input type="text" name='pending_reason' value = "" /><br>
    <input type="text" name='reason_code' value = "" /><br>
    
    
    <!-- subscription specific vars-->
    
    <input type="text" name='subscr_id' value = "" /><br>
    <input type="text" name='subscr_date' value = "" /><br>
    <input type="text" name='subscr_effective' value = "" /><br>
    <input type="text" name='period1' value = "" /><br>
    <input type="text" name='period2' value = "" /><br>
    <input type="text" name='period3' value = "" /><br>
    <input type="text" name='amount1' value = "" /><br>
    <input type="text" name='amount2' value = "" /><br>
    <input type="text" name='amount3' value = "" /><br>
    <input type="text" name='mc_amount1' value = "" /><br>
    <input type="text" name='mc_amount2' value = "" /><br>
    <input type="text" name='mcamount3' value = "" /><br>
    <input type="text" name='recurring' value = "" /><br>
    <input type="text" name='reattempt' value = "" /><br>
    <input type="text" name='retry_at' value = "" /><br>
    <input type="text" name='recur_times' value = "" /><br>
    <input type="text" name='username' value = "" /><br>
    <input type="text" name='password' value = "" /><br>
    
    <!-- auction specific varsn-->
    
    <input type="text" name=['for_auction' value = "" /><br>
    <input type="text" name=['auction_closing_date' value = "" /><br>
    <input type="text" name='auction_multi_item' value = "" /><br>
    <input type="text" name='auction_buyer_id' value = "" /><br>
  </p>

</form>


