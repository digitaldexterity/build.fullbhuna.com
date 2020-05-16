<?php if(!isset($aquiescedb)) die();  require_once(SITE_ROOT.'mail/includes/sendmail.inc.php'); ?>
<?php require_once(SITE_ROOT.'members/includes/userfunctions.inc.php'); ?>
<?php require_once(SITE_ROOT.'location/includes/locationfunctions.inc.php'); ?>
<?php require_once(SITE_ROOT.'mail/includes/reminders.inc.php'); ?>
<?php require_once(SITE_ROOT.'products/includes/productFunctions.inc.php'); ?>
<?php require_once(SITE_ROOT.'core/includes/framework.inc.php'); ?>
<?php 
 // logtransaction() REQUIRES BASKET CONTENTS PREVIOUSLY iF NEW TRANSACTION
 $console = isset($console) ? $console : "";
error_reporting(32767); // 0 = display no errors, 32767 display all
	@ini_set("display_errors", 1); // 0 = don't display none, 1 = display/
 
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

 
 function random_string($len=8, $str='')
{
  for($i=1; $i<=$len; $i++)
   {
    //generates a random number that will be the ASCII code of the character. We only want  upper case letters. 
    $ord=rand(65, 90);
    $str.=chr($ord);
    
  }
  return $str;
}


function logtransaction($strVendorTxCode="",$strTransactionType="UNKNOWN",$status="PENDING",$archive = 0, $strVPSTxId="", $strSecurityKey="", $modifiedbyID = 0, $amountpaid = "") {	 // final two field sagepay security
	if(isset($_SESSION['debug'])) writeLog("function logtransaction(strVendorTxCode=".$strVendorTxCode.",strTransactionType=".$strTransactionType.",status=".$status.",archive=".$archive.",strVPSTxId=".$strVPSTxId.",modifiedbyID=".$modifiedbyID.",strSecurityKey=".$strSecurityKey.",amountpaid=".$amountpaid.")");
	global $database_aquiescedb, $aquiescedb, $grandtotal, $shippingtotal, $totalvat, $discounts, $regionID, $console, $row_rsThisRegion; 
	$basket_json = json_encode($_SESSION['basket']);
	$console .= " ** LOG TRANSACTION **\n";
	$amountpaid = $amountpaid!="" ? floatval(str_replace(',', '', $amountpaid)) : ""; // get rid ofcommas in thousands retunrd by SagePay
	$status = strtoupper($status);
	$status = ($status=="ACCEPTED" || $status=="COMPLETED" || $status=="PROCESSED"  || $status=="SUCCESS" || strpos($status, "AUTHORISED") !==false) ? "COMPLETED" : $status; // standardise various statuses
	mysql_select_db($database_aquiescedb, $aquiescedb);	
	$regionID = isset($regionID) ? $regionID : 1;
	$select = "SELECT * FROM productprefs WHERE ID = ".intval($regionID);
	$result = mysql_query($select, $aquiescedb) or die(mysql_error().": ".$select);
	$productPrefs = mysql_fetch_assoc($result);	
	$select = "SELECT * FROM preferences WHERE ID = ".intval($regionID);
	$rsPreferences = mysql_query($select, $aquiescedb) or die(mysql_error().": ".$select);	
	$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
	
	if($strVendorTxCode=="" || $strVPSTxId!="") { // no transaction yet or new SagePay transaction
		$shippingtotal = (isset($shippingtotal) && $shippingtotal>0) ? str_replace(",","",$shippingtotal) : 0;
		$grandtotal = (isset($grandtotal) && $grandtotal>0) ? str_replace(",","",$grandtotal) : 0;
		$totalvat = (isset($totalvat) && $totalvat>0) ? str_replace(",","",$totalvat) : 0;
		$strCurrency= isset($row_rsThisRegion['currencycode']) ? $row_rsThisRegion['currencycode'] : "GBP";
		$strTimeStamp = date("y/m/d : H:i:s", time());
		$intRandNum = rand(0,32000)*rand(0,32000);
		$strVendorTxCode = ($strVendorTxCode == "") ? random_string() . "-" . date("ymdHis") : $strVendorTxCode;						
	
		// Gather customer details from the session
		$strCustomerEMail      = @$_SESSION["strCustomerEMail"];
		$strBillingFirstnames  = @$_SESSION["strBillingFirstnames"];
		$strBillingSurname     = @$_SESSION["strBillingSurname"];
		$strBillingCompany    = @$_SESSION["strBillingCompany"];
		$strBillingAddress1    = @$_SESSION["strBillingAddress1"];
		$strBillingAddress2    = @$_SESSION["strBillingAddress2"];
		$strBillingCity        = @$_SESSION["strBillingCity"];
		$strBillingPostCode    = @$_SESSION["strBillingPostCode"];
		$strBillingCountry     = @$_SESSION["strBillingCountry"];
		$strBillingState       = @$_SESSION["strBillingState"];
		$strBillingPhone       = @$_SESSION["strBillingPhone"];
		$strBillingMobile       = @$_SESSION["strBillingMobile"];
		$bIsDeliverySame       = isset($_SESSION["bIsDeliverySame"]) ? $_SESSION["bIsDeliverySame"] : 1;
		$strDeliveryFirstnames = (@$_SESSION["bIsDeliverySame"] ==0) ? @$_SESSION["strDeliveryFirstnames"] : $strBillingFirstnames;
		$strDeliverySurname    = (@$_SESSION["bIsDeliverySame"] ==0) ? @$_SESSION["strDeliverySurname"] : $strBillingSurname;
		$strDeliveryCompany   = (@$_SESSION["bIsDeliverySame"] ==0) ? @$_SESSION["strDeliveryCompany"] : $strBillingCompany;
		$strDeliveryAddress1   = (@$_SESSION["bIsDeliverySame"] ==0) ? @$_SESSION["strDeliveryAddress1"] : $strBillingAddress1;
		$strDeliveryAddress2   = (@$_SESSION["bIsDeliverySame"] ==0) ? @$_SESSION["strDeliveryAddress2"] : $strBillingAddress2;
		$strDeliveryCity       = (@$_SESSION["bIsDeliverySame"] ==0) ? @$_SESSION["strDeliveryCity"] : $strBillingCity;
		$strDeliveryPostCode   = (@$_SESSION["bIsDeliverySame"] ==0) ? @$_SESSION["strDeliveryPostCode"] : $strBillingPostCode;
		$strDeliveryState      = (@$_SESSION["bIsDeliverySame"] ==0) ? @$_SESSION["strDeliveryState"] : $strBillingState;
		$strDeliveryPhone      = (@$_SESSION["bIsDeliverySame"] ==0) ? @$_SESSION["strDeliveryPhone"] : $strBillingPhone;
		$deliveryinstructions       = isset($_SESSION["deliveryinstructions"] ) ? $_SESSION["deliveryinstructions"] : "";
		$deliverytime       = isset($_SESSION["deliverytime"] ) ? $_SESSION["deliverytime"] : "";
		$checkoutanswer1       = isset($_SESSION["checkoutanswer1"] ) ? $_SESSION["checkoutanswer1"] : "";
		
		$optin       = isset($_SESSION["optin"]) ? intval($_SESSION["optin"]) : 0;
		$partneroptin       = (isset($_SESSION["partneroptin"] ) && $_SESSION["partneroptin"] == 1) ? 1 : 0;
		$discovered = isset($_SESSION['discovered']) ? intval($_SESSION['discovered']) : "NULL";
		$vatnumber = isset($_SESSION['vatnumber']) ? $_SESSION['vatnumber'] : "NULL";
		$purchaseorder = isset($_SESSION['purchaseorder']) ? $_SESSION['purchaseorder'] : "NULL";
			
		// PE - get country code
		$select = "SELECT iso2 FROM countries WHERE ID = ".intval($_SESSION["strBillingCountry"]);
		$result = mysql_query($select, $aquiescedb) or die(mysql_error());
		$rowBillingCountry = mysql_fetch_assoc($result);
		
		$strBillingCountry   = isset($rowBillingCountry['iso2']) ? $rowBillingCountry['iso2'] : "GB";
		$billingCountryID = isset($_SESSION["strBillingCountry"]) ? intval($_SESSION["strBillingCountry"]) : 0;
		
		$select = "SELECT iso2 FROM countries WHERE ID = ".intval($_SESSION["strDeliveryCountry"]);
		$result = mysql_query($select, $aquiescedb) or die(mysql_error());
		$rowDeliveryCountry = mysql_fetch_assoc($result);
		
		$strDeliveryCountry   = isset($rowDeliveryCountry['iso2']) ? $rowDeliveryCountry['iso2'] : "GB";
		$deliveryCountryID = isset($_SESSION["strDeliveryCountry"]) ? intval($_SESSION["strDeliveryCountry"]) : 0;
		
		if($row_rsPreferences['emailoptintype'] == 2) { // reverse optin flag if opt out is set in preferences
			$optin = ($optin==1) ? 0 : 1;
		}
		
		if($row_rsPreferences['partneremailoptintype'] == 2) { // reverse optin flag if opt out is set in preferences
			$partneroptin = ($partneroptin==1) ? 0 : 1;
		}
		$userID = 0;
		$rank  = 0;
		$approvalrequired = 0;
		if(isset($_SESSION["MM_Username"])) { // logged in user	
		 	$console .= " ** LOGGED IN AS ".$_SESSION["MM_Username"]." **\n";
			$select = "SELECT ID FROM users WHERE username = ".GetSQLValueString($_SESSION["MM_Username"], "text")." AND (users.regionID = 0 OR users.regionID IS NULL OR users.regionID = ".intval($regionID).")";
			$userresult = mysql_query($select, $aquiescedb) or die(mysql_error());
			if(mysql_num_rows($userresult)>0) {
				$row = mysql_fetch_assoc($userresult);
				$userID = $row['ID'];
			}			
		} 
		if($userID==0) { // not logged in, so new user? (either matching email, or surname and postcode)	
			$console .= " ** NOT LOGGED IN **\n";
			$select = "SELECT users.ID, users.username FROM users LEFT JOIN location ON (users.defaultaddressID = location.ID) WHERE (users.regionID = 0 OR users.regionID IS NULL OR users.regionID = ".intval($regionID).") AND ((email IS NOT NULL AND email = ".GetSQLValueString($strCustomerEMail, "text").") OR (surname = ".GetSQLValueString($strBillingSurname, "text")." AND postcode = ".GetSQLValueString($strBillingPostCode, "text").")) LIMIT 1";
			$console .= $select."\n";
			$userresult = mysql_query($select, $aquiescedb) or die(mysql_error());
			if(mysql_num_rows($userresult)>0) {  //existing user in this region				
				$row = mysql_fetch_assoc($userresult);
				$console .= " ** EXISTING USER ".$row['ID']." - ".$row['username']." **\n";
				$userID = $row['ID'];
			} else { // NEW USER - create a user profile at this point				
				$login = ($row_rsPreferences['userscanlogin']==1) ? true : false;	
				$usertypeID = $login ? (($row_rsPreferences['manualverify']==1 || $row_rsPreferences['emailverify'] == 1) ? 0 : 1) : -1;
				$console .= " ** NEW USER (Usertype= ".$usertypeID.", Can log in = ".$login.") **\n";													
				$userID = createNewUser($strBillingFirstnames,$strBillingSurname,$strCustomerEMail,$usertypeID,0,0,0,"",$login,"","","","","", "", "",$regionID,"",1,$optin,$discovered,"",0,"","","", $strBillingPhone, 0,  $partneroptin);			
				$locationname = trim($strBillingFirstnames." ".$strBillingSurname);
				$locationID = createLocation(0,0,$locationname,"",$strBillingAddress1,$strBillingAddress2,$strBillingCity,$strBillingState,"",$strBillingPostCode,$strBillingPhone);
				if(intval($locationID)>0) {
					addUserToLocation($userID, $locationID, $userID, true);
				}		
		
				if(!$bIsDeliverySame) { // is delivery address 
					$locationname = trim($strDeliveryFirstnames." ".$strDeliverySurname);
					$locationID = createLocation(0,0,$locationname,"",$strDeliveryAddress1,$strDeliveryAddress2,$strDeliveryCity,$strDeliveryState,"",$strDeliveryPostCode,$strDeliveryPhone);
					if(intval($locationID)>0) {
						addUserToLocation($userID, $locationID, $userID);
					}
				} // end is delivery address 
			} // end new user
		} // end possible new user
		
		
		$strSQL="INSERT INTO productorders(VendorTxCode, 
			sessionID,
			regionID,
			userID,
			TxType, 
			Amount,
			AmountTax,
			AmountPaid,
			shipping,
			Currency, 
			BillingFirstnames, 
			BillingSurname, 
			BillingCompany,
			BillingAddress1,
			BillingAddress2, 
			BillingCity, 
			BillingPostCode, 
			BillingCountry,
			billingcountryID, 
			BillingState, 
			BillingPhone,
			BillingMobile, 
			deliverysame,
			deliveryinstructions,
			deliverytime,
			checkoutanswer1,
			optin,
			discovered,
			DeliveryFirstnames,
			DeliverySurname,
			DeliveryCompany,
			DeliveryAddress1,
			DeliveryAddress2,
			DeliveryCity,
			DeliveryPostCode,
			DeliveryCountry,
			deliverycountryID,
			DeliveryState,
			DeliveryPhone, 
			CustomerEMail,
			Status, 
			VPSTxId, 
			SecurityKey,
			VATnumber,
			purchaseorder,
			approvalrequired,
			basket_json,
			LastUpdated,
			createdbyID, createddatetime) VALUES (";

		$strSQL=$strSQL . "'" . mysql_real_escape_string($strVendorTxCode) . "',"; //Add the VendorTxCode generated above
		$strSQL .= isset($_SESSION['fb_tracker']) ? GetSQLValueString($_SESSION['fb_tracker'], "text")."," : "NULL,"; //session
		$strSQL=$strSQL . GetSQLValueString($regionID, "int").","; 
		$strSQL=$strSQL . GetSQLValueString($userID, "int").","; //Add the userID if logged in
		$strSQL=$strSQL . "'" . mysql_real_escape_string($strTransactionType) . "',"; //Add the TxType from the includes file
		$strSQL=$strSQL . "'" . number_format($grandtotal,2,".","") . "',"; //Add the formatted total amount
		$strSQL=$strSQL . "'" . number_format($totalvat,2,".","") . "',"; //Add the formatted total amount
		$strSQL=$strSQL . GetSQLValueString($amountpaid, "double").",";
		$strSQL=$strSQL . "'" . number_format($shippingtotal,2,".","") . "',"; //Add the formatted shipping amount
		$strSQL=$strSQL . "'" . mysql_real_escape_string($strCurrency) . "',"; //Add the Currency

		// Add the Billing details 
		$strSQL=$strSQL . "'" . mysql_real_escape_string($strBillingFirstnames) . "',";   
		$strSQL=$strSQL . "'" . mysql_real_escape_string($strBillingSurname) . "',";  
		$strSQL=$strSQL . GetSQLValueString($strBillingCompany, "text").",";  
		$strSQL=$strSQL . "'" . mysql_real_escape_string($strBillingAddress1) . "',";  
		
		if (strlen($strBillingAddress2)>0) 
			$strSQL=$strSQL . "'" . mysql_real_escape_string($strBillingAddress2) . "',"; 
		else 
			$strSQL=$strSQL . "null,";
		
		$strSQL=$strSQL . "'" . mysql_real_escape_string($strBillingCity) . "',";  
		$strSQL=$strSQL . "'" . mysql_real_escape_string($strBillingPostCode) . "',"; 
		$strSQL=$strSQL . "'" . mysql_real_escape_string($strBillingCountry) . "',";  
		$strSQL .= mysql_real_escape_string($billingCountryID) . ",";  
		
		if (strlen($strBillingState)>0)  
			$strSQL=$strSQL . "'" . mysql_real_escape_string($strBillingState) . "',"; 
		else 
			$strSQL=$strSQL . "null,"; 
		
		if (strlen($strBillingPhone)>0)  
			$strSQL=$strSQL . "'" . mysql_real_escape_string($strBillingPhone) . "',";  
		else 
			$strSQL=$strSQL . "null,";
			
			
		if (strlen($strBillingMobile)>0)  
			$strSQL=$strSQL . "'" . mysql_real_escape_string($strBillingMobile) . "',";  
		else 
			$strSQL=$strSQL . "null,";
			
			
		$strSQL=$strSQL .  mysql_real_escape_string($bIsDeliverySame) . ",";
		$strSQL=$strSQL .  "'" . mysql_real_escape_string($deliveryinstructions) . "',";
		$strSQL=$strSQL .  "'" . mysql_real_escape_string($deliverytime) . "',";
		$strSQL=$strSQL .  "'" . mysql_real_escape_string($checkoutanswer1) . "',";
		
		$strSQL .= $optin . ",";
		$strSQL .= $discovered . ",";
			 
		// Add the Delivery details 
		$strSQL=$strSQL . "'" . mysql_real_escape_string($strDeliveryFirstnames) . "',"; 
		$strSQL=$strSQL . "'" . mysql_real_escape_string($strDeliverySurname) . "',"; 
		$strSQL=$strSQL . GetSQLValueString($strDeliveryCompany, "text").",";   
		$strSQL=$strSQL . "'" . mysql_real_escape_string($strDeliveryAddress1) . "',"; 

		if (strlen($strDeliveryAddress2)>0) 
			$strSQL=$strSQL . "'" . mysql_real_escape_string($strDeliveryAddress2) . "',";  
		else 
			$strSQL=$strSQL . "null,";
		
		$strSQL=$strSQL . "'" . mysql_real_escape_string($strDeliveryCity) . "',";  
		$strSQL=$strSQL . "'" . mysql_real_escape_string($strDeliveryPostCode) . "',"; 
		$strSQL=$strSQL . "'" . mysql_real_escape_string($strDeliveryCountry) . "',";  
		$strSQL .= mysql_real_escape_string($deliveryCountryID) . ",";
		
		if (strlen($strDeliveryState)>0) 
			$strSQL=$strSQL . "'" . mysql_real_escape_string($strDeliveryState) . "',"; 
		else 
			$strSQL=$strSQL . "null,";   
		
		if (strlen($strDeliveryPhone)>0) 
			$strSQL=$strSQL . "'" . mysql_real_escape_string($strDeliveryPhone) . "',"; 
		else 
			$strSQL=$strSQL . "null,"; 
		 
		// Customer email 
		if (strlen($strCustomerEMail)>0)
			$strSQL=$strSQL . "'" . mysql_real_escape_string($strCustomerEMail) . "',"; 
		else 
			$strSQL=$strSQL . "null,"; 
			
			 $strSQL=$strSQL . "'".$status."',"; 
			 
			 /** Now save the fields returned from the Sage Pay System and extracted above **/
		$strSQL=$strSQL . "'" . mysql_real_escape_string($strVPSTxId) . "',"; //Save the Sage Pay System's unique transaction reference
		$strSQL=$strSQL . "'" . mysql_real_escape_string($strSecurityKey) . "',";
		$strSQL=$strSQL . "'" . mysql_real_escape_string($vatnumber) . "',";
		$strSQL=$strSQL . "'" . mysql_real_escape_string($purchaseorder) . "',";
		$strSQL=$strSQL . intval($approvalrequired). ",";
		$strSQL=$strSQL . GetSQLValueString($basket_json, "text"). ",";
		
		$strSQL=$strSQL . "'".date('Y-m-d H:i:s')."', ".intval($modifiedbyID).", '".date('Y-m-d H:i:s')."')";


		/** Execute the SQL command to insert this data to the productorders table **/

		$rsPrimary = mysql_query($strSQL)
			or die ("Query ".$strSQL." failed with error message: \"" . mysql_error () . '"');
		$rsPrimary="";
		$strSQL="";
		
		/** Now add the basket contents to the productorderproducts table, one line at a time **/
		
		$usergroups = array();
		foreach($_SESSION['basket'] as $productID => $product) {
			$quantity = 0;
			$select = "SELECT product.price, product.instock, productcategory.usergroupID, parentcategory.usergroupID AS parentusergroupID,product.mindeliverytime,product.maxdeliverytime,product.deliveryperiod,product.availabledate FROM product LEFT JOIN productcategory ON (product.productcategoryID = productcategory.ID) LEFT JOIN productcategory AS parentcategory ON (productcategory.subcatofID = parentcategory.ID) WHERE product.ID = ".$productID;
			$result = mysql_query($select, $aquiescedb) or die(mysql_error());			
			$rowProduct = mysql_fetch_assoc($result);			
			$dates = getDeliveryTimes($rowProduct['mindeliverytime'],$rowProduct['maxdeliverytime'],$rowProduct['deliveryperiod'], $rowProduct['availabledate']);			
			$mindeliverydatetime = isset($dates['fromdatetime']) ? $dates['fromdatetime'] : "";
			$maxdeliverydatetime = isset($dates['todatetime']) ? $dates['todatetime'] : "";
			if(isset($rowProduct['usergroupID'])) array_push($usergroups, $rowProduct['usergroupID']);
			if(isset($rowProduct['parentusergroupID'])) array_push($usergroups, $rowProduct['parentusergroupID']);
			foreach($product as $option => $details) { // options
				if($details['quantity']>0 && $productID >0) { // valid
					$quantity = $details['quantity']; 
					$predictedamount = isset($details['predictedamount']) ? $details['predictedamount'] : 0;
					$sampleofID = isset($details['sampleofID']) ? $details['sampleofID'] : 0;
					$options = explode(" @@ ",$option);
					$optionID = @$options[0];
					$optiontext = @$options[1];
					$uploadID = isset($details['uploadID']) ? intval($details['uploadID']) : "NULL";
					$select2 = "SELECT price FROM productoptions WHERE ID = ".intval(@$options[0])." LIMIT 1";
					$result2 = mysql_query($select2, $aquiescedb) or die(mysql_error());
					$rowOption = mysql_fetch_assoc($result2);
					$price = isset($rowOption['price']) ? floatval($rowOption['price']) : floatval($rowProduct['price']);
					$strSQL="INSERT INTO productorderproducts(VendorTxCode,ProductId,Price,Quantity,optiontext,optionID,uploadID, predictedamount, sampleofID,productforuserID,mindeliverydatetime,maxdeliverydatetime)
				VALUES(" . GetSQLValueString($strVendorTxCode,"text") . "," . GetSQLValueString($productID,"int") . ","
				. number_format($price,2,".","") . "," . GetSQLValueString($quantity,"int") . ",".GetSQLValueString($optiontext,"text").",".GetSQLValueString($optionID,"int").",".$uploadID.", ".intval($predictedamount).",".intval($sampleofID).",".GetSQLValueString($details['productforuserID'],"int").",".GetSQLValueString($mindeliverydatetime, "date").",".GetSQLValueString($maxdeliverydatetime, "date").")";				
					$rsPrimary = mysql_query($strSQL)
						or die ("Query ".$strSQL." failed with error message: \"" . mysql_error () . '"');					
					$rsPrimary="";
					$strSQL="";
					
				} // end valid
			} // end options
			

			
		}// end for each basket item
		if(isset($userID) && $userID>0) {
			foreach($usergroups as $key=>$groupID) {
				addUsertoGroup($userID, $groupID);
			}
		}
		// NOW INSERT INTO PROMOS TABLE
		
		
		if(is_array($discounts) && !empty($discounts)) {
			foreach($discounts as $key => $discount) {
				if(isset($discount['amount'])) {
				$insert = "INSERT INTO productorderpromos (VendorTxCode, promotionID, amount) VALUES (" . GetSQLValueString($strVendorTxCode,"text"). ",".intval($discount['ID']).",".number_format($discount['amount'],2,".","").")";
				$result = mysql_query($insert)
					or die ("Query ".$insert." failed with error message: \"" . mysql_error () . '"');
				}
			}
		}
		
		
		
	// end new transaction
	} else { // existing transaction		
		if($productPrefs['stockcontrol']==1 && ($status=="COMPLETED" || $status=="INVOICE")) {		
			$select = "SELECT productorderproducts.ProductId, productorderproducts.optionID, productorderproducts.Quantity, productorderproducts.stockdecremented , product.instock, product.title, productoptions.instock AS optioninstock  FROM productorderproducts LEFT JOIN product ON (productorderproducts.ProductId = product.ID) LEFT JOIN productoptions ON (productorderproducts.optionID = productoptions.ID)  WHERE VendorTxCode = ".GetSQLValueString($strVendorTxCode,"text");
			$result = mysql_query($select, $aquiescedb) or die(mysql_error().": ".$select);
			if(mysql_num_rows($result)>0) {
				while($orderitem = mysql_fetch_assoc($result)) {
					if($orderitem['stockdecremented']==0) {							
						$update = "UPDATE productorderproducts SET stockdecremented = ".intval($orderitem['Quantity'])." WHERE VendorTxCode = ".GetSQLValueString($strVendorTxCode,"text");
						mysql_query($update, $aquiescedb) or die(mysql_error().": ".$update);
						$update = "UPDATE product SET instock = instock - ".intval($orderitem['Quantity'])." WHERE ID = ".intval($orderitem['ProductId']);
						
						mysql_query($update, $aquiescedb) or die(mysql_error().": ".$update);
						$remaining = $orderitem['instock'] - $orderitem['Quantity'];
						if($orderitem['optionID']>0) { // is a product option
							$update = "UPDATE productoptions SET instock = instock - ".intval($orderitem['Quantity'])." WHERE ID = ".intval($orderitem['optionID']);	
							mysql_query($update, $aquiescedb) or die(mysql_error().": ".$update);
							$remaining = $orderitem['optioninstock'] - $orderitem['Quantity'];
						}
						
						if($productPrefs['stocklowamount']>0 && $remaining <= $productPrefs['stocklowamount']) {
							$to = $row_rsPreferences['contactemail'];
							$subject = "Stock low alert: ".$orderitem['title'];
							$message = "This is an automated message to inform you that stock is low on:\n\n";
							$message .= $orderitem['title'];
							$message .= isset($orderitem['optionname']) ? " [".$orderitem['optionname']."]\n\n" : "\n\n";
							$message .= "View/edit product info below:\n\n";
							$message .= getProtocol()."://".$_SERVER['HTTP_HOST']."/products/admin/products/modify_product.php?productID=".$orderitem['ProductId']."\n\n";
							$message .= "Time: ".date('H:i:s d M Y')." IP: ".getClientIP()." Transaction: ".$strVendorTxCode." Ordered: ".$orderitem['Quantity']." Remaining: ".$remaining." Option: ".$orderitem['optionID'];
							sendMail($to,$subject,$message);
						}
					} // stock decremented already?
				} // loop products
			} // products in order
		} // stock control on
		if(defined("DEBUG_EMAIL")) {
     		mail(DEBUG_EMAIL, "COMPLETED", $strVendorTxCode);
		}
		
		$update = "UPDATE productorders SET ";
		$update .= $amountpaid >0 ? "AmountPaid = ".GetSQLValueString($amountpaid, "double").", ": "";
		$update .= "archive = ".intval($archive).", Status = ".GetSQLValueString($status,"text").", modifiedbyID = ".intval($modifiedbyID).", LastUpdated = '".date('Y-m-d H:i:s')."' WHERE VendorTxCode = ".GetSQLValueString($strVendorTxCode, "text"); 
		$result = mysql_query($update, $aquiescedb) or die(mysql_error().": ".$update);
		
		
		//update session
		mysql_select_db($database_aquiescedb, $aquiescedb);
		$update = "UPDATE track_session LEFT JOIN productorders ON (track_session.ID = productorders.sessionID) SET postpay = 1 WHERE  productorders.VendorTxCode = ".GetSQLValueString($strVendorTxCode,"text");
		mysql_query($update, $aquiescedb) or die(mysql_error().": ".$update);
		
		
		
			
		
	} // end existing transaction
	confirmationEmail($strVendorTxCode,$status);
	$purchasemade = $amountpaid>0 ? 2 : 1;
	sendFollowUps($strVendorTxCode, $purchasemade);
	cleanSales();
	return $strVendorTxCode;	
} // end func

function confirmationEmail($strVendorTxCode="",$status="PENDING") {
	global $database_aquiescedb, $aquiescedb, $regionID;
	$approvalrequired = 0;
	if($strVendorTxCode!="" && ($status=="COMPLETED" || $status=="INVOICE")) {
		$select = "SELECT * FROM productorders WHERE VendorTxCode = ".GetSQLValueString($strVendorTxCode,"text")." LIMIT 1";
		
		$orderresult = mysql_query($select, $aquiescedb) or die(mysql_error());
		if(mysql_num_rows($orderresult)>0) { // order found
			$ordernumber = $rowOrder['confemaIDilsent'];
			$rowOrder=mysql_fetch_assoc($orderresult);
			if($rowOrder['confemailsent']!=1) { // not already sent 			
			
				// approval required?			
				$select = "SELECT productaccount.groupID, productaccount.approverrankID FROM productaccount LEFT JOIN usergroupmember ON (productaccount.groupID = usergroupmember.groupID) LEFT JOIN users ON (users.ID = usergroupmember.userID) WHERE productaccount.statusID =1 AND (productaccount.regionID = 0 OR productaccount.regionID = ".$regionID.") AND usergroupmember.userID = ".intval($rowOrder['userID'])." AND approverrankID > users.usertypeID LIMIT 1";
				$approvalresult = mysql_query($select, $aquiescedb) or die(mysql_error());
				if(mysql_num_rows($approvalresult)>0) {  //user in group that needs approval					
					$approvalrequired = 1;
					$approvalrow = mysql_fetch_assoc($approvalresult);
					// get users who can approve and email them 
					$select = "SELECT users.ID, users.email FROM users LEFT JOIN usergroupmember ON (users.ID = usergroupmember.userID) WHERE usergroupmember.groupID = ".$approvalrow['groupID']." AND usertypeID >=".$approvalrow['approverrankID'];
					$approversresult = mysql_query($select, $aquiescedb) or die(mysql_error());
					$approversemails = "";
					if(mysql_num_rows($approversresult)>0) {  //user in group that needs approval
						$update = "UPDATE productorders SET approvalrequired = 1 WHERE VendorTxCode = ".GetSQLValueString($strVendorTxCode,"text");
						mysql_query($update, $aquiescedb) or die(mysql_error());
						while($approver = mysql_fetch_assoc($approversresult)) {
							if(trim($approver['email']) !="") {
								$approversemails .= ($approversemails=="") ? "" : ", ";
								$approversemails .= $approver['email'];
							}
						} // loop
						$subject = "Approval required for ".$site_name." order ". $ordernumber;
						$message = "To view and approve or reject this order, please click on the link below:\n\n";
						$message .= getProtocol()."://".$_SERVER['HTTP_HOST']."/products/members/order.php?VendorTxCode=".$strVendorTxCode."&token=".md5($strVendorTxCode.PRIVATE_KEY)."&login=true";		
						sendMail($approversemails,$subject, $message);
					} // end we have approvers				
				} // end approval
			
			
			
				$invoicelink = getProtocol()."://".$_SERVER['HTTP_HOST']."/products/payments/invoice.php?VendorTxCode=". $strVendorTxCode."&token=".md5(PRIVATE_KEY.$strVendorTxCode);
				if(function_exists("get_bitly_short_url")) {
					// use bit.ly if available
					$invoicelink = get_bitly_short_url($invoicelink);
				}
				$basketDescription = orderDetails($strVendorTxCode);
				$regionID = (isset($rowOrder['regionID']) && $rowOrder['regionID'] >0) ? $rowOrder['regionID'] : 1;
				$select = "SELECT * FROM productprefs WHERE ID = ".intval($regionID);
				$result = mysql_query($select, $aquiescedb) or die(mysql_error().": ".$select);
				$row_rsProductPrefs = mysql_fetch_assoc($result);
				$select = "SELECT * FROM region WHERE ID = ".intval($regionID);
				$result = mysql_query($select, $aquiescedb) or die(mysql_error().": ".$select);
				$row_rsThisRegion = mysql_fetch_assoc($result);
				require_once(SITE_ROOT.'mail/includes/sendmail.inc.php'); 
				if($row_rsProductPrefs['saleemail']==1 && $row_rsThisRegion['email']!="") { // email org
					$to =  $row_rsThisRegion['email'];
					$subject = $row_rsThisRegion['title']." Transaction Completed Order Number:".$ordernumber;
					$message = "A customer has just completed a transaction on your site.\n\n";
					if($approvalrequired==1) {
						
						$message .= "NOTE THIS ORDER REQUIRES APPROVAL BEFORE PROCESSING\nA FURTHER EMAIL WILL BE SENT ONCE APPROVED\n\n";
					}
					$message .= $basketDescription."\n\n";
					$message .= "Click on the link view and update  in your Control Panel:\n\n";
					$url = getProtocol()."://".$_SERVER['HTTP_HOST']."/products/admin/orders/orderDetails.php?VendorTxCode=".$strVendorTxCode."\n\n";
					if(function_exists("get_bitly_short_url")) {
					// use bit.ly if available
						$url = get_bitly_short_url($url);
					}
					$message .= $url."\n\n";					
					$message .= "You can also view the ";
					$message .= isset($row_rsProductPrefs['text_invoice']) ? htmlentities($row_rsProductPrefs['text_invoice'], ENT_COMPAT, "UTF-8") : "Invoice"; 
					$message .= " here:\n\n";
					$message .= $invoicelink;				
					sendMail($to,$subject,$message);
				} // end email org
				
				if($rowOrder["CustomerEMail"]!="") { // customer has email
					if($row_rsProductPrefs['confirmationemail']==1) { // conf email customer
						$to = $rowOrder["CustomerEMail"];
						$bcc = $row_rsProductPrefs['confemailcc'];
						$from = $row_rsThisRegion['email'];
						$friendlyfrom = $row_rsThisRegion['title'];
						$subject = $row_rsProductPrefs['confemailsubject'];
						$html = false;
						if((isset($row_rsProductPrefs['confemailtemplateID']) && $row_rsProductPrefs['confemailtemplateID']>0) || ($rowOrder['Amount'] == 0 && isset($row_rsProductPrefs['conffreeemailtemplateID']) && $row_rsProductPrefs['conffreeemailtemplateID']>0)) { // use template
							$templateID = ($rowOrder['Amount'] == 0 && isset($row_rsProductPrefs['conffreeemailtemplateID']) && $row_rsProductPrefs['conffreeemailtemplateID']>0) ? $row_rsProductPrefs['conffreeemailtemplateID'] : $row_rsProductPrefs['confemailtemplateID'];
							$select = "SELECT templatesubject, templatemessage, templatehead, templateHTML FROM groupemailtemplate WHERE ID = ".$templateID;
							$result = mysql_query($select, $aquiescedb) or die(mysql_error().": ".$select);
							$template = mysql_fetch_assoc($result);
							if($template['templateHTML']!="") { //html
								$message = $template['templateHTML']; $html = true;
							} else {
								$message = $template['templatemessage']; 
							}				
						} else {	// not template				
							$message = $row_rsProductPrefs['confemailmessage'];
						}
						$message = orderMailMerge($message, $strVendorTxCode, $html);
						$subject = orderMailMerge($subject, $strVendorTxCode);
						
						if($html) {
							$htmlmessage = $message; $message = "";
							$htmlhead = $template['templatehead'];
						} 
						
						if(sendMail($to,$subject,$message,$from,$friendlyfrom, $htmlmessage,"","","",$bcc,$htmlhead)=="OK") {
							$update = "UPDATE productorders SET confemailsent = 1 WHERE VendorTxCode = ".GetSQLValueString($strVendorTxCode,"text");
							$result = mysql_query($update, $aquiescedb) or die(mysql_error());
						}
					} // end conf email customer 					
				} // end customer has email
			} // not already sent
		} // order found
	} // approved		
} // end func confirmation email


function sendFollowUps($strVendorTxCode, $purchasemade = 1) {	

	if(isset($_SESSION['debug'])) writeLog("function sendFollowUps(".$strVendorTxCode.",".$purchasemade.")");
	global $database_aquiescedb, $aquiescedb, $regionID;
	if($strVendorTxCode!="") {
		$select = "SELECT * FROM productorders WHERE VendorTxCode = ".GetSQLValueString($strVendorTxCode,"text")." LIMIT 1";		
		$orderresult = mysql_query($select, $aquiescedb) or die(mysql_error());
		if(mysql_num_rows($orderresult)>0) { // order found
			$rowOrder=mysql_fetch_assoc($orderresult);
			if($rowOrder['userID']>0) { // is user			
				$select = "SELECT * FROM productemail WHERE statusID = 1  AND regionID = ".$regionID." AND (purchasemade = ".intval($purchasemade)." OR (".intval($purchasemade)." = 2 AND purchasemade>1))";
				$followupresult = mysql_query($select, $aquiescedb) or die(mysql_error());
				if(mysql_num_rows($followupresult)>0) { //  is follow ups
					while($followup = mysql_fetch_assoc($followupresult)) {
						if(!is_array($_SESSION['followup_done'])) { 
							$_SESSION['followup_done'] = array(); 
						}
						if(!in_array($_SESSION['followup_done'],$followup['templateID'])) { // prevent duplicate sends
							array_push($_SESSION['followup_done'],$followup['tenplateID']);
							// check category
							$select = "SELECT productorderproducts.ID FROM productorderproducts	LEFT JOIN productincategory ON (productincategory.productID = productorderproducts.ProductId) LEFT JOIN product ON (product.ID = productorderproducts.ProductId) WHERE	(".$followup['categoryID']." = 0 OR productincategory.categoryID = ".$followup['categoryID']." OR product.productcategoryID = ".$followup['categoryID'].") AND productorderproducts.VendorTxCode = ".	GetSQLValueString($strVendorTxCode,"text")." GROUP BY productorderproducts.ID";	
							$catresult = mysql_query($select, $aquiescedb) or die(mysql_error());
							if(mysql_num_rows($catresult)>0) { // applicable products
								$select = "SELECT templatesubject, templatemessage, templatehead, templateHTML, smsmessage FROM groupemailtemplate WHERE ID = ".intval($followup['templateID']);
								$result = mysql_query($select, $aquiescedb) or die(mysql_error().": ".$select);
								$template = mysql_fetch_assoc($result);
								$reminderdate = date('Y-m-d H:i:s', (time()+$followup['period']));
								$html = (trim($template['templateHTML'])=="") ? false : true;
								$message = orderMailMerge($template['message'], $strVendorTxCode, false);
								$smsmessage = orderMailMerge($template['smsmessage'], $strVendorTxCode, false);
								if($html) {
									$htmlmessage = orderMailMerge($template['templateHTML'], $strVendorTxCode, true);
									$htmlhead = $template['templatehead'];
								}						
								addReminder($reminderdate,$rowOrder['userID'],$template['templatesubject'], $message,  0, $rowOrder['userID'] ,"","",$htmlmessage,$template['templatehead'],"",$followup['viaemail'],$followup['viasms'], $smsmessage, 0, 0, "", "", $followup['ignoreoptout']);		
							} // applicatble products
						} // end duplicate
					} // end while follow up
				} // end is follow ups
			} // is user			
		} // order found 
	} //is order
}

function orderDetails($strVendorTxCode="") {
	global $database_aquiescedb, $aquiescedb;
	$html = "";
	
	if($strVendorTxCode!="") {
		mysql_select_db($database_aquiescedb, $aquiescedb);
		$query_rsOrderDetails = sprintf("SELECT productorders.*, productorderproducts.ID AS orderID, productorderproducts.Price, productorderproducts.Quantity, productorderproducts.dispatched, product.title, productoptions.optionname, productoptions.price AS optionprice, productorderproducts.optiontext, productoptions.stockcode AS optionsku, product.imageURL,  product.sku,productcategory.title AS categoryname FROM productorders LEFT JOIN productorderproducts ON (productorders.VendorTxCode = productorderproducts.VendorTxCode) LEFT JOIN product ON (productorderproducts.productID = product.ID) LEFT JOIN productoptions ON (productoptions.ID = productorderproducts.optionID) LEFT JOIN productcategory ON (product.productcategoryID = productcategory.ID) WHERE productorders.VendorTxCode = %s", GetSQLValueString($strVendorTxCode, "text"));
		$rsOrderDetails = mysql_query($query_rsOrderDetails, $aquiescedb) or die(mysql_error().":".$query_rsOrderDetails);
		$row_rsOrderDetails = mysql_fetch_assoc($rsOrderDetails);
		$totalRows_rsOrderDetails = mysql_num_rows($rsOrderDetails);
		
		$currency = ($row_rsOrderDetails['Currency']== "GBP") ? "&pound;" : $row_rsOrderDetails['Currency'];
		
		$query_rsPromos = sprintf("SELECT promotionID, amount, productpromo.promotitle FROM productorderpromos LEFT JOIN productpromo ON (productorderpromos.promotionID = productpromo.ID) WHERE VendorTxCode = %s", GetSQLValueString($strVendorTxCode, "text"));
		$rsPromos = mysql_query($query_rsPromos, $aquiescedb) or die(mysql_error());
		$row_rsPromos = mysql_fetch_assoc($rsPromos);
		$totalRows_rsPromos = mysql_num_rows($rsPromos);
	
		$html .="<table class='product-order-table' cellpadding='5'>";
		do {
			
			$imageURL = getProtocol()."://".$_SERVER['HTTP_HOST'].getImageURL($row_rsOrderDetails['imageURL'], "thumb");		
			$html .= "<tr><td><img src='".$imageURL."' style='max-width:150px; height:auto;'></td><td>".$row_rsOrderDetails['categoryname']." - ".$row_rsOrderDetails['title']."</td>";
			$html .= (isset($row_rsOrderDetails['optionname']) && $row_rsOrderDetails['optionname'] !="") ? "<td> [".$row_rsOrderDetails['optionname']."]</td>" : "<td>&nbsp;</td>"; 
			$html .= (isset($row_rsOrderDetails['optiontext']) && $row_rsOrderDetails['optiontext']!="") ? "<td> [".$row_rsOrderDetails['optiontext']."]</td>" : "<td>&nbsp;</td>"; 
			$html .=  (isset($row_rsOrderDetails['optionsku']) && $row_rsOrderDetails['optionsku']!="") ? "<td>".$row_rsOrderDetails['optionsku']."</td>" : "<td>".$row_rsOrderDetails['sku']."</td>";
			$html .= "<td nowrap align='right'>  ".$currency.$row_rsOrderDetails['Price']."</td>";
			$html .= "<td nowrap> x ". $row_rsOrderDetails['Quantity']."</td></tr>\n";
		   
		} while ($row_rsOrderDetails = mysql_fetch_assoc($rsOrderDetails)); 
		
		mysql_data_seek($rsOrderDetails,0); $row_rsOrderDetails = mysql_fetch_assoc($rsOrderDetails); 
		if($totalRows_rsPromos>0) { 
			 do { 
				if($row_rsPromos['amount']>0) {
					$html .= "<tr><td colspan='5'>".$row_rsPromos['promotitle']."</td><td align='right'>". $currency.number_format($row_rsPromos['amount'],2,".",",")."</td></tr>"; 
				 }		 
			 } while($row_rsPromos = mysql_fetch_assoc($rsPromos));
			 mysql_data_seek($rsOrderDetails,0);
			 $row_rsOrderDetails = mysql_fetch_assoc($rsOrderDetails);
		 }		 
		 $html .= "<tr><td colspan='5'>Shipping:</td><td align='right'>". $currency.number_format($row_rsOrderDetails['shipping'],2,".",",")."</td></tr>"; 
		 $html .= "<tr><td colspan='5'>TOTAL (".$row_rsOrderDetails['TxType'].")</td><td align='right'>".$currency.$row_rsOrderDetails['Amount']."</td></tr></table>";
	}
	return $html;
}

function orderMailMerge($message="", $strVendorTxCode="", $html = false) {
	global $database_aquiescedb, $aquiescedb, $regionID, $currency;
	
	$select = "SELECT productorders.*, productorderproducts.ProductId, product.title FROM productorders LEFT JOIN productorderproducts ON (productorders.VendorTxCode =  productorderproducts.VendorTxCode) LEFT JOIN product ON (productorderproducts.ProductId = product.ID) WHERE productorders.VendorTxCode = ".GetSQLValueString($strVendorTxCode,"text")." LIMIT 1";
	// just get first product in order for review link
	$result = mysql_query($select, $aquiescedb) or die(mysql_error());
	if(mysql_num_rows($result)>0) { // order found
		$rowOrder=mysql_fetch_assoc($result);
		$ordernumber = $rowOrder['ID'];
		$regionID = (isset($rowOrder['regionID']) && $rowOrder['regionID'] >0) ? $rowOrder['regionID'] :1;
		$select = "SELECT * FROM region WHERE ID = ".intval($regionID);
		$result = mysql_query($select, $aquiescedb) or die(mysql_error().": ".$select);
		$row_rsThisRegion = mysql_fetch_assoc($result);
		$currency = $row_rsThisRegion['currencycode']." "; 
		$invoicelink = getProtocol()."://";
		$reviewlink = $invoicelink;
		$basketlink = $invoicelink;
		$invoicelink .= $_SERVER['HTTP_HOST']."/products/payments/invoice.php?VendorTxCode=". $strVendorTxCode."&token=".md5(PRIVATE_KEY.$strVendorTxCode);
		$reviewlink .= $_SERVER['HTTP_HOST']."/products/product.php?productID=". $rowOrder["ProductId"]."#productreviews";
		$basketlink .= $_SERVER['HTTP_HOST']."/products/basket/index.php?VendorTxCode=". $strVendorTxCode."&token=".md5(PRIVATE_KEY.$strVendorTxCode);
		if(function_exists("get_bitly_short_url")) {
					// use bit.ly if available
				$invoicelink = get_bitly_short_url($invoicelink);
				$reviewlink = get_bitly_short_url($reviewlink);
				$basketlink = get_bitly_short_url($basketlink);
		}
					
		$deliveryaddress = $rowOrder["DeliveryFirstnames"]."\n".$rowOrder["DeliverySurname"]."\n".$rowOrder["DeliveryAddress1"]."\n".$rowOrder["DeliveryAddress2"]."\n".$rowOrder["DeliveryCity"]."\n".$rowOrder["DeliveryPostcode"];
		$basketDescription = orderDetails($strVendorTxCode);
		if(!$html) {
			$basketDescription = strip_tags(str_replace("</tr>","\n",$basketDescription));	
			//remove any HTML chars
			$basketDescription = str_replace("&pound;","£", $basketDescription);
			$basketDescription = str_replace("&nbsp;"," ", $basketDescription);					
		}
		$message = preg_replace ("/(\[firstname\]|\{firstname\})/i",$rowOrder["BillingFirstnames"],$message);
		$message = preg_replace ("/(\[surname\]|\{surname\})/i",$rowOrder["BillingSurname"],$message);
		$message = preg_replace ("/(\[customer\]|\{customer\})/i",$rowOrder["BillingFirstnames"]." ".$rowOrder["BillingSurname"],$message);							
		$message = preg_replace ("/(\[order\]|\{order\})/i",$basketDescription,$message);
		$message = preg_replace ("/(\[code\]|\{code\})/i",$ordernumber."/".$strVendorTxCode,$message);			
		$message = preg_replace ("/(\[invoicelink\]|\{invoicelink\})/i","<a href=\"".$invoicelink."\">".$invoicelink."</a>",$message);
		$message = preg_replace ("/(\[vatnumber\]|\{vatnumber\})/i",$row_rsThisRegion['vatnumber'],$message);
		$message = preg_replace ("/(\[purchaseorder\]|\{purchaseorder\})/i",$rowOrder["purchaseorder"],$message);
		$message = preg_replace ("/(\{reviewlink\})/i",$reviewlink,$message);
		$message = preg_replace ("/(\{basketlink\})/i",$basketlink,$message);
		$message = preg_replace ("/(\{productID\})/i",$rowOrder["ProductId"],$message);
		$message = preg_replace ("/(\{productname\})/i",$rowOrder["title"],$message);
		$message = preg_replace ("/(\{delivery\})/i",$deliveryaddress,$message);
		$message = preg_replace ("/(\{date\})/i",date('d M Y', strtotime($rowOrder["createddatetime"])),$message);
	}
	return $message;
}

function cleanSales($clean_period="30 MINUTE") {
	global $database_aquiescedb, $aquiescedb, $regionID;
	if(isset($_SESSION['debug'])) writeLog("Function cleanSales()");
	// update any transactions shown as pending to cancelled if older than 1 hour
	
	// 1. ARCHIVE AND CANCEL THOSE older than hour that have more recent transaction by same user
	// 2. CANCEL AND FOLLOW UP THOSE without later sale
 	mysql_select_db($database_aquiescedb, $aquiescedb);	
	
	$select = "SELECT pendingorders.VendorTxCode, 
	(SELECT COUNT(completedorders.VendorTxCode) FROM productorders AS completedorders WHERE completedorders.archive = 0 AND completedorders.regionID = ".intval($regionID)." AND  completedorders.userID = pendingorders.userID AND completedorders.LastUpdated > pendingorders.LastUpdated AND completedorders.AmountPaid IS NOT NULL) AS latersales 
	FROM productorders AS pendingorders 
	WHERE pendingorders.archive = 0 AND pendingorders.regionID = ".intval($regionID)." AND (pendingorders.Status = 'PENDING' OR pendingorders.Status LIKE 'ABORTED%') AND (pendingorders.TxType='SAGEPAY' OR pendingorders.TxType='CREDITCARD' OR  pendingorders.TxType='PAYPAL' OR pendingorders.TxType='PAYMENT') AND (pendingorders.LastUpdated + INTERVAL ".$clean_period.") < '".date('Y-m-d H:i:s')."'"; 
	
	$result = mysql_query($select, $aquiescedb) or die(mysql_error());
	
	if(mysql_num_rows($result)>0) {
		while($order = mysql_fetch_assoc($result)) {
			if($order['latersales']>0) { // has associated completed order
				$update = "UPDATE productorders SET archive = 1, Status = 'CANCELLED' WHERE VendorTxCode = ".GetSQLValueString($order['VendorTxCode'], "text");
				mysql_query($update, $aquiescedb) or die(mysql_error());
				
			} else {
				$update = "UPDATE productorders SET  Status = 'CANCELLED' WHERE VendorTxCode = ".GetSQLValueString($order['VendorTxCode'], "text");
				mysql_query($update, $aquiescedb) or die(mysql_error());
				// add any follow up abandoned cart
				sendFollowUps($order['VendorTxCode'], 0); 
			}
			if(isset($_SESSION['debug'])) writeLog($update);
		}
	}	
}

?>