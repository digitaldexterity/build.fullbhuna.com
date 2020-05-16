<?php require_once('../../../Connections/aquiescedb.php'); ?>
<?php require_once('../includes/logtransaction.inc.php'); ?>
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

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsProductPrefs = "SELECT paymentproviderID, shopstatus FROM productprefs";
$rsProductPrefs = mysql_query($query_rsProductPrefs, $aquiescedb) or die(mysql_error());
$row_rsProductPrefs = mysql_fetch_assoc($rsProductPrefs);
$totalRows_rsProductPrefs = mysql_num_rows($rsProductPrefs);

if (defined("DEBUG_EMAIL")) {
	$post = var_export($_POST, true);
	$post .= "IP: ".$_SERVER['REMOTE_ADDR'];
	mail(DEBUG_EMAIL,"PAYPAL POST",$post);
}



// read the post from PayPal system and add 'cmd'
$req = 'cmd=_notify-validate';
foreach ($_POST as $key => $value) {
	$value = urlencode(stripslashes($value));
	$req .= "&$key=$value";
}

$host = ($row_rsProductPrefs['shopstatus']==1) ? "www.paypal.com" : "www.sandbox.paypal.com";
$paypal_url =  "ssl://".$host ;


$header = "POST /cgi-bin/webscr HTTP/1.0\r\n";
$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
$header .="Host: ".$host."\r\n";
$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";



/*/ post back to PayPal system to validate
$header ="POST /cgi-bin/webscr HTTP/1.1\r\n";
$header .="Content-Type: application/x-www-form-urlencoded
$header .="Host: ".$host."\r\n";
$header .="Connection: close\r\n";*/



$struery=""; $strQuery=""; $strQuery2="";

$fp = fsockopen ($paypal_url, 443, $errno, $errstr, 30);



// assign posted variables to local variables
$item_name = @$_POST['item_name'];
$business = @$_POST['business'];
$item_number = @$_POST['item_number'];
$payment_status = @$_POST['payment_status'];
$mc_gross = @$_POST['mc_gross'];
$payment_currency = @$_POST['mc_currency'];
$txn_id = @$_POST['txn_id'];
$receiver_email = @$_POST['receiver_email'];
$receiver_id = @$_POST['receiver_id'];
$quantity = @$_POST['quantity'];
$num_cart_items = @$_POST['num_cart_items'];
$payment_date = date('Y-m-d H:i:s',strtotime(@$_POST['payment_date']));
$first_name = @$_POST['first_name'];
$last_name = @$_POST['last_name'];
$payment_type = @$_POST['payment_type'];
$payment_status = @$_POST['payment_status'];
$payment_gross = @$_POST['payment_gross'];
$payment_fee = @$_POST['payment_fee'];
$settle_amount = @$_POST['settle_amount'];
$memo = @$_POST['memo'];
$payer_email = @$_POST['payer_email'];
$txn_type = @$_POST['txn_type'];
$payer_status = @$_POST['payer_status'];
$address_street = @$_POST['address_street'];
$address_city = @$_POST['address_city'];
$address_state = @$_POST['address_state'];
$address_zip = @$_POST['address_zip'];
$address_country = @$_POST['address_country'];
$address_status = @$_POST['address_status'];
$item_number = @$_POST['item_number'];
$tax = @$_POST['tax'];
$option_name1 = @$_POST['option_name1'];
$option_selection1 = @$_POST['option_selection1'];
$option_name2 = @$_POST['option_name2'];
$option_selection2 = @$_POST['option_selection2'];
$for_auction = @$_POST['for_auction'];
$invoice = @$_POST['invoice'];
$custom = @$_POST['custom'];
$notify_version = @$_POST['notify_version'];
$verify_sign = @$_POST['verify_sign'];
$payer_business_name = @$_POST['payer_business_name'];
$payer_id = @$_POST['payer_id'];
$mc_currency = @$_POST['mc_currency'];
$mc_fee = @$_POST['mc_fee'];
$exchange_rate = @$_POST['exchange_rate'];
$settle_currency  = @$_POST['settle_currency'];
$parent_txn_id  = @$_POST['parent_txn_id'];
$pending_reason = @$_POST['pending_reason'];
$reason_code = @$_POST['reason_code'];


// subscription specific vars

$subscr_id = @$_POST['subscr_id'];
$subscr_date = @$_POST['subscr_date'];
$subscr_effective  = @$_POST['subscr_effective'];
$period1 = @$_POST['period1'];
$period2 = @$_POST['period2'];
$period3 = @$_POST['period3'];
$amount1 = @$_POST['amount1'];
$amount2 = @$_POST['amount2'];
$amount3 = @$_POST['amount3'];
$mc_amount1 = @$_POST['mc_amount1'];
$mc_amount2 = @$_POST['mc_amount2'];
$mc_amount3 = @$_POST['mcamount3'];
$recurring = @$_POST['recurring'];
$reattempt = @$_POST['reattempt'];
$retry_at = @$_POST['retry_at'];
$recur_times = @$_POST['recur_times'];
$username = @$_POST['username'];
$password = @$_POST['password'];

//auction specific vars

$for_auction = @$_POST['for_auction'];
$auction_closing_date  = @$_POST['auction_closing_date'];
$auction_multi_item  = @$_POST['auction_multi_item'];
$auction_buyer_id  = @$_POST['auction_buyer_id'];

$VendorTxCode = ($row_rsProductPrefs['paymentproviderID']==6) ? $invoice : $option_selection2;


//DB connect creds and email 
$DB_Server = $hostname_aquiescedb; //your MySQL Server
$DB_Username = $username_aquiescedb; //your MySQL User Name
$DB_Password = $password_aquiescedb; //your MySQL Password
$DB_DBName = $database_aquiescedb; //your MySQL Database Name


if (!$fp && defined("DEBUG_EMAIL")) {
 	mail(DEBUG_EMAIL, "Could not reply to PayPal ".$site_name."", "$res\n $req\n $strQuery\n $struery\n  $strQuery2");
} else { // http
	fputs ($fp, $header . $req);
	while (!feof($fp)) {
		$res = fgets ($fp, 1024);
		if (strcmp ($res, "VERIFIED") == 0) {

			//create MySQL connection
			$Connect = @mysql_connect($DB_Server, $DB_Username, $DB_Password)
			or die("Couldn't connect to MySQL:<br>" . mysql_error() . "<br>" . mysql_errno());
			//select database
			$Db = @mysql_select_db($DB_DBName, $Connect)
			or die("Couldn't select database:<br>" . mysql_error(). "<br>" . mysql_errno());

			$fecha = date("m")."/".date("d")."/".date("Y");
			$fecha = date("Y").date("m").date("d");
	
			//check if transaction ID has been processed before
			$checkquery = "select txnid from paypal_payment_info where txnid='".$txn_id."'";
			$sihay = mysql_query($checkquery) or die("Duplicate txn id check query failed:<br>" . mysql_error() . "<br>" . mysql_errno());
			$nm = mysql_num_rows($sihay);
			if ($nm == 0) { // new transaction

				//execute query
    			if ($txn_type == "cart"){ // is cart
    				$strQuery = "insert into paypal_payment_info(paymentstatus,buyer_email,firstname,lastname,street,city,state,zipcode,country,mc_gross,mc_fee,memo,paymenttype,paymentdate,txnid,pendingreason,reasoncode,tax,datecreation) values ('".$payment_status."','".$payer_email."','".$first_name."','".$last_name."','".$address_street."','".$address_city."','".$address_state."','".$address_zip."','".$address_country."','".$mc_gross."','".$mc_fee."','".$memo."','".$payment_type."','".$payment_date."','".$txn_id."','".$pending_reason."','".$reason_code."','".$tax."','".$fecha."')";

     				$result = mysql_query($strQuery) or die("Cart - paypal_payment_info, Query failed:<br>" . mysql_error() . "<br>" . mysql_errno());
     				for ($i = 1; $i <= $num_cart_items; $i++) {
         				$itemname = "item_name".$i;
         				$itemnumber = "item_number".$i;
         				$on0 = "option_name1_".$i;
         				$os0 = "option_selection1_".$i;
         				$on1 = "option_name2_".$i;
         				$os1 = "option_selection2_".$i;
         				$quantity = "quantity".$i;

         				$struery = "insert into paypal_cart_info(txnid,itemnumber,itemname,os0,on0,os1,on1,quantity,invoice,custom) values ('".$txn_id."','".$_POST[$itemnumber]."','".$_POST[$itemname]."','".$_POST[$on0]."','".$_POST[$os0]."','".$_POST[$on1]."','".$_POST[$os1]."','".$_POST[$quantity]."','".$invoice."','".$custom."')";
         				$result = mysql_query($struery) or die("Cart - paypal_cart_info, Query failed:<br>" . mysql_error() . "<br>" . mysql_errno());
	
     				} // end dloop
    			} else { // not cart
     				$strQuery = "insert into paypal_payment_info(paymentstatus,buyer_email,firstname,lastname,street,city,state,zipcode,country,mc_gross,mc_fee,itemnumber,itemname,os0,on0,os1,on1,quantity,memo,paymenttype,paymentdate,txnid,pendingreason,reasoncode,tax,datecreation) values ('".$payment_status."','".$payer_email."','".$first_name."','".$last_name."','".$address_street."','".$address_city."','".$address_state."','".$address_zip."','".$address_country."','".$mc_gross."','".$mc_fee."','".$item_number."','".$item_name."','".$option_name1."','".$option_selection1."','".$option_name2."','".$option_selection2."','".$quantity."','".$memo."','".$payment_type."','".$payment_date."','".$txn_id."','".$pending_reason."','".$reason_code."','".$tax."','".$fecha."')";
     				$result = mysql_query("insert into paypal_payment_info(paymentstatus,buyer_email,firstname,lastname,street,city,state,zipcode,country,mc_gross,mc_fee,itemnumber,itemname,os0,on0,os1,on1,quantity,memo,paymenttype,paymentdate,txnid,pendingreason,reasoncode,tax,datecreation) values ('".$payment_status."','".$payer_email."','".$first_name."','".$last_name."','".$address_street."','".$address_city."','".$address_state."','".$address_zip."','".$address_country."','".$mc_gross."','".$mc_fee."','".$item_number."','".$item_name."','".$option_name1."','".$option_selection1."','".$option_name2."','".$option_selection2."','".$quantity."','".$memo."','".$payment_type."','".$payment_date."','".$txn_id."','".$pending_reason."','".$reason_code."','".$tax."','".$fecha."')") or die("Default - paypal_payment_info, Query failed:<br>" . mysql_error() . "<br>" . mysql_errno());
    			} // end not cart


    			// send an email for verified, new transaction cart and not cart
 				echo "Verified"; 
				if(defined("DEBUG_EMAIL")) {
     				mail(DEBUG_EMAIL, "VERIFIED IPN (".strtoupper($payment_status).") ".$site_name."", "$res\n $req\n $strQuery\n $struery\n  $strQuery2");
				}
	  
			} else { // is duplicate transaction
				// send an email
				if(defined("DEBUG_EMAIL")) {
					mail(DEBUG_EMAIL, "VERIFIED DUPLICATED TRANSACTION", "$res\n $req \n $strQuery\n $struery\n  $strQuery2");
				}
			} // end duplicate

    		//subscription handling branch
    		if ( $txn_type == "subscr_signup"  ||  $txn_type == "subscr_payment"  ) {

    			// insert subscriber payment info into paypal_payment_info table
     			$strQuery = "insert into paypal_payment_info(paymentstatus,buyer_email,firstname,lastname,street,city,state,zipcode,country,mc_gross,mc_fee,memo,paymenttype,paymentdate,txnid,pendingreason,reasoncode,tax,datecreation) values ('".$payment_status."','".$payer_email."','".$first_name."','".$last_name."','".$address_street."','".$address_city."','".$address_state."','".$address_zip."','".$address_country."','".$mc_gross."','".$mc_fee."','".$memo."','".$payment_type."','".$payment_date."','".$txn_id."','".$pending_reason."','".$reason_code."','".$tax."','".$fecha."')";
      			$result = mysql_query($strQuery) or die("Subscription - paypal_payment_info, Query failed:<br>" . mysql_error() . "<br>" . mysql_errno());
         		// insert subscriber info into paypal_subscription_info table
     			$strQuery2 = "insert into paypal_subscription_info(subscr_id , sub_event, subscr_date ,subscr_effective,period1,period2, period3, amount1 ,amount2 ,amount3,  mc_amount1,  mc_amount2,  mc_amount3, recurring, reattempt,retry_at, recur_times, username ,password, payment_txn_id, subscriber_emailaddress, datecreation) values ('".$subscr_id."', '".$txn_type."','".$subscr_date."','".$subscr_effective."','".$period1."','".$period2."','".$period3."','".$amount1."','".$amount2."','".$amount3."','".$mc_amount1."','".$mc_amount2."','".$mc_amount3."','".$recurring."','".$reattempt."','".$retry_at."','".$recur_times."','".$username."','".$password."', '".$txn_id."','".$payer_email."','".$fecha."')";
        		$result = mysql_query($strQuery2) or die("Subscription - paypal_subscription_info, Query failed:<br>" . mysql_error() . "<br>" . mysql_errno());
	
		 		if(defined("DEBUG_EMAIL")) {
					mail(DEBUG_EMAIL, "VERIFIED IPN 2", "$res\n $req\n $strQuery\n $struery\n  $strQuery2");
				}

    		} // end subscribe
			
			if(is_readable(SITE_ROOT."local/includes/paypal_ipn.inc.php")) {
				include(SITE_ROOT."local/includes/paypal_ipn.inc.php");
			} else {
				
				// update Full Bhuna database
				logtransaction($VendorTxCode,"",strtoupper($payment_status), 0, "", "", 0, $payment_gross);
			}
		} // end verified

		// if the IPN POST was 'INVALID'...do this


		else if (strcmp ($res, "INVALID") == 0) {
			// log for manual investigation
			if(defined("DEBUG_EMAIL")) {
				mail(DEBUG_EMAIL, "INVALID IPN", "$res\n $req");
			}
		} // end invalid
		// otherwise other line in post
		
	} // end while not eof
	
	fclose ($fp);

} // end http

mysql_free_result($rsProductPrefs);
?>