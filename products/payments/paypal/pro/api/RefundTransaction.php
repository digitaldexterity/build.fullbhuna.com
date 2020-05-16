
<!--
RefundTransaction.html

This is the main page for RefundTransaction sample. 
This page allow the user to enter the required 
parameters for RefundTransaction API call and a Submit button 
that calls RefundReceipt.php.

Called by index.html.

Calls RefundReceipt.php.

-->
<?php
   
   $transaction_id = $_REQUEST['transaction_id'];
   if(!isset($transaction_id))
      $transaction_id='';
   $amount = $_REQUEST['amount'];
   if(!isset($amount))
      $amount = '0.00';
   $currency = $_REQUEST['currency'];
   if(!isset($currency))
      $currency = 'USD'; 
?>

<html>
<head>
    <title>PayPal PHP SDK - RefundTransaction API</title>
    <link href="sdk.css" rel="stylesheet"  />
</head>
<body>
		<br>
		<center>
		<font size=2 color=black face=Verdana><b>RefundTransaction</b></font>
		<br><br>
    
    <form action="RefundReceipt.php" method="POST">
        <table class="api">
            <tr>
                <td class="thinfield">
                    Transaction ID:</td>
                <td>
                    <input type="text" name="transactionID" value=<?php echo $transaction_id?>></td>
                    <td><b>(Required)</b></td>
            </tr>
            <tr>
                <td class="thinfield">
                    Refund Type:</td>
                <td>
                    <select name="refundType">
                    <option value="Full">Full</option>
                    <option value="Partial">Partial</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="thinfield">
                    Amount:</td>
                <td>
                    <input type="text" name="amount" value=<?php echo $amount?> />
                    <select name="currency">
 <?php
   $cur_list = array('USD', 'GBP', 'EUR', 'JPY', 'CAD', 'AUD');
   for($s=0; $s < sizeof($cur_list); $s++) {
      $selected = (!strcmp($currency, $cur_list[$s])) ? 'selected' : '';
?>
			<option  <?php echo $selected?>><?php echo $cur_list[$s]?></option>

<?php } ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td />
                <td>
                    <b>(Required if Partial Refund)</b>
                </td>
            </tr>
            <tr>
                <td class="thinfield">
                    Memo:</td>
                <td>
                    <textarea name="memo" cols="30" rows="4"></textarea></td>
            </tr>
            <tr>
                <td class="thinfield">
                </td>
                <td>
                    <button type="submit"  class="btn btn-primary">Submit</button></td>
            </tr>
        </table>
    </form>
    </center>
    <a class="home" id="CallsLink" href="index.html">Home</a>
</body>
</html>
