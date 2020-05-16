<?php require_once('../../../Connections/aquiescedb.php'); ?>
<?php require_once('../../../core/includes/adminAccess.inc.php'); ?>
<?php require_once('../../payments/includes/logtransaction.inc.php'); ?>
<?php require_once('../../includes/basketFunctions.inc.php'); ?><?php require_once('../../../mail/sms/includes/smsfunctions.inc.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "8,9,10";
$MM_donotCheckaccess = "false";

// *** Restrict Access To Page: Grant or deny access to this page
function isAuthorized($strUsers, $strGroups, $UserName, $UserGroup) { 
  // For security, start by assuming the visitor is NOT authorized. 
  $isValid = False; 

  // When a visitor has logged into this site, the Session variable MM_Username set equal to their username. 
  // Therefore, we know that a user is NOT logged in if that Session variable is blank. 
  if (!empty($UserName)) { 
    // Besides being logged in, you may restrict access to only certain users based on an ID established when they login. 
    // Parse the strings into arrays. 
    $arrUsers = Explode(",", $strUsers); 
    $arrGroups = Explode(",", $strGroups); 
    if (in_array($UserName, $arrUsers)) { 
      $isValid = true; 
    } 
    // Or, you may restrict access to only certain users based on their username. 
    if (in_array($UserGroup, $arrGroups)) { 
      $isValid = true; 
    } 
    if (($strUsers == "") && false) { 
      $isValid = true; 
    } 
  } 
  return $isValid; 
}

$MM_restrictGoTo = "/login/index.php?notloggedin=true";
if (!((isset($_SESSION['MM_Username'])) && (isAuthorized("",$MM_authorizedUsers, $_SESSION['MM_Username'], $_SESSION['MM_UserGroup'])))) {   
  $MM_qsChar = "?";
  $MM_referrer = $_SERVER['PHP_SELF'];
  if (strpos($MM_restrictGoTo, "?")) $MM_qsChar = "&";
  if (isset($_SERVER['QUERY_STRING']) && strlen($_SERVER['QUERY_STRING']) > 0) 
  $MM_referrer .= "?" . $_SERVER['QUERY_STRING'];
  $MM_restrictGoTo = $MM_restrictGoTo. $MM_qsChar . "accesscheck=" . urlencode($MM_referrer);
  header("Location: ". $MM_restrictGoTo); 
  exit;
}
?>
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

$colname_rsOrderDetails = "-1";
if (isset($_GET['VendorTxCode'])) {
  $colname_rsOrderDetails = $_GET['VendorTxCode'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsOrderDetails = sprintf("SELECT productorders.*, productorderproducts.uploadID, productorderproducts.ID AS orderID, productorderproducts.Price, productorderproducts.Quantity, productorderproducts.dispatched, product.title, product.priceper,productoptions.optionname, productoptions.price AS optionprice, productorderproducts.optiontext, productorderproducts.productID, productorderproducts.mindeliverydatetime, productorderproducts.maxdeliverydatetime,productoptions.stockcode AS optionsku, product.sku, product.vattype, productcategory.vatdefault, productcategory.vatincluded,track_session.ID AS sessionID, track_session.referer, billingcountries.fullname AS billingcountryname, deliverycountries.fullname AS deliverycountryname, discovered.`description` AS howdiscovered, modifiedby.firstname, modifiedby.surname, productcategory.title AS categoryname, CONCAT(approvedby.firstname,' ',approvedby.surname ) AS approvedbyname, CONCAT(productforuser.firstname,' ',productforuser.surname ) AS productforusername, productfinish.finishname FROM productorders LEFT JOIN productorderproducts ON (productorders.VendorTxCode = productorderproducts.VendorTxCode) LEFT JOIN product ON (productorderproducts.productID = product.ID) LEFT JOIN productoptions ON (productoptions.ID = productorderproducts.optionID) LEFT JOIN productfinish ON  (productoptions.finishID = productfinish.ID) LEFT JOIN track_session ON (productorders.sessionID = track_session.ID) LEFT JOIN countries AS billingcountries ON (billingcountries.ID = productorders.billingcountryID) LEFT JOIN countries AS deliverycountries ON (deliverycountries.ID = productorders.deliverycountryID) LEFT JOIN discovered ON (productorders.discovered = discovered.ID) LEFT JOIN users AS modifiedby ON (productorders.modifiedbyID = modifiedby.ID)  LEFT JOIN productcategory ON (product.productcategoryID = productcategory.ID) LEFT JOIN users AS approvedby ON (productorders.approvedbyID = approvedby.ID) LEFT JOIN users AS productforuser ON (productorderproducts.productforuserID = productforuser.ID)  WHERE productorders.VendorTxCode = %s GROUP BY productorderproducts.ID", GetSQLValueString($colname_rsOrderDetails, "text"));
$rsOrderDetails = mysql_query($query_rsOrderDetails, $aquiescedb) or die(mysql_error());
$row_rsOrderDetails = mysql_fetch_assoc($rsOrderDetails);
$totalRows_rsOrderDetails = mysql_num_rows($rsOrderDetails);

$colname_rsPromos = "-1";
if (isset($_GET['VendorTxCode'])) {
  $colname_rsPromos = $_GET['VendorTxCode'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPromos = sprintf("SELECT productorderpromos.ID, productorderpromos.promotionID, productorderpromos.amount, productpromo.promotitle FROM productorderpromos LEFT JOIN productpromo ON (productorderpromos.promotionID = productpromo.ID) WHERE VendorTxCode = %s", GetSQLValueString($colname_rsPromos, "text"));
$rsPromos = mysql_query($query_rsPromos, $aquiescedb) or die(mysql_error());
$row_rsPromos = mysql_fetch_assoc($rsPromos);
$totalRows_rsPromos = mysql_num_rows($rsPromos);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsProductPrefs = "SELECT * FROM productprefs WHERE ID = ".$regionID."";
$rsProductPrefs = mysql_query($query_rsProductPrefs, $aquiescedb) or die(mysql_error());
$row_rsProductPrefs = mysql_fetch_assoc($rsProductPrefs);
$totalRows_rsProductPrefs = mysql_num_rows($rsProductPrefs);

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

$colname_rsThisRegion = "1";
if (isset($regionID)) {
  $colname_rsThisRegion = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsThisRegion = sprintf("SELECT region.*, countries.ID, countries.iso2 FROM region LEFT JOIN countries  ON (region.ID = countries.regionID OR countries.regionID=0) WHERE region.ID =  %s", GetSQLValueString($colname_rsThisRegion, "int"));
$rsThisRegion = mysql_query($query_rsThisRegion, $aquiescedb) or die(mysql_error());
$row_rsThisRegion = mysql_fetch_assoc($rsThisRegion);
$totalRows_rsThisRegion = mysql_num_rows($rsThisRegion);

?>
<?php if(isset($_POST['VendorTxCode'])) { // page saved
$_POST['archive'] = isset($_POST['archive']) ? 1 : 0;

logtransaction($_POST['VendorTxCode'],"",$_POST['Status'],$_POST['archive'],"","",$row_rsLoggedIn['ID'],$_POST['AmountPaid']);

if(isset($_POST['approvalrequired'])) {
	$update = "UPDATE productorders SET approvalrequired = 1 WHERE VendorTxCode = ".GetSQLValueString($_POST['VendorTxCode'], "text");
	mysql_query($update, $aquiescedb) or die(mysql_error());
	if( isset($_POST['approved'])) {
		if($_POST['approvedbyID']=="") {
			$update = "UPDATE productorders SET  approvedbyID = ".$row_rsLoggedIn['ID'].", approveddatetime = '".date('Y-m-d H:i:s')."' WHERE VendorTxCode = ".GetSQLValueString($_POST['VendorTxCode'], "text");
			mysql_query($update, $aquiescedb) or die(mysql_error());
		}
	} else {
		$update = "UPDATE productorders SET approvedbyID = NULL, approveddatetime = NULL WHERE VendorTxCode = ".GetSQLValueString($_POST['VendorTxCode'], "text");
		mysql_query($update, $aquiescedb) or die(mysql_error());
	}
	
} else {
	$update = "UPDATE productorders SET approvalrequired = 0, approvedbyID = NULL, approveddatetime = NULL WHERE VendorTxCode = ".GetSQLValueString($_POST['VendorTxCode'], "text");
	mysql_query($update, $aquiescedb) or die(mysql_error());
}

$update2 = "UPDATE productorderproducts SET dispatched = 0 WHERE VendorTxCode = ".GetSQLValueString($_POST['VendorTxCode'], "text");
mysql_query($update2, $aquiescedb) or die(mysql_error());

	$emaildispatched = "";
	if(isset($_POST['dispatched'])) {
		foreach($_POST['dispatched'] as $key => $value) {
		$update3 = "UPDATE productorderproducts SET dispatched = 1 WHERE ID = ".$key;
		mysql_query($update3, $aquiescedb) or die(mysql_error());
		if($_POST['dispatched'][$key] != $_POST['dispatchedold'][$key]) { // newly checked so add to email
			$emaildispatched .= $_POST['dispatcheddetail'][$key]."\n";
			if(trim($_POST['couriertrackerID'][$key])!="") {
				$emaildispatched .= "Courier tracking ID: ".$_POST['couriertrackerID'][$key]."\n\n";
			}
			} // end newly checked
		} // end for each
	} // is post checked
	if(isset($_POST['emaildispatched']) && $emaildispatched !="") {
		require_once('../../../mail/includes/sendmail.inc.php');
		
		if(isset($row_rsProductPrefs['dispatchemailtemplateID']) && $row_rsProductPrefs['dispatchemailtemplateID']>0) { // use template
			$templateID =  $row_rsProductPrefs['dispatchemailtemplateID'];
			$select = "SELECT templatesubject, templatemessage, templatehead, templateHTML, smsmessage FROM groupemailtemplate WHERE ID = ".$templateID;
			$result = mysql_query($select, $aquiescedb) or die(mysql_error().": ".$select);
			$template = mysql_fetch_assoc($result);
			if($template['templateHTML']!="") { //html
				$message = $template['templateHTML']; $html = true;
			} else {
				$message = $template['templatemessage']; 
				
			}
			$subject = $template['templatesubject'];
			$smsmessage = $template['smsmessage'];
		} else {
			$subject = $row_rsProductPrefs['dispatchemailsubject'];
			$message = $row_rsProductPrefs['dispatchemailmessage']; 
			$smsmessage = "";
		}
		$message = orderMailMerge($message, $_POST['VendorTxCode'], $html);
		$smsmessage = orderMailMerge($smsmessage, $_POST['VendorTxCode'], $html);
		$subject = orderMailMerge($subject, $_POST['VendorTxCode']);
		
		
		
		 
		$to = $row_rsOrderDetails['CustomerEMail'];
		$bcc = $row_rsProductPrefs['dispatchemailcc'];
		$from = "";
		$friendlyfrom = $site_name;
		
		if($html) {
			$htmlmessage = $message; $message = "";
			$htmlhead = $template['templatehead'];
		}
						
		//if($_SESSION['MM_UserGroup']==10) die($to.":".$htmlmessage);
		
		
		sendMail($to, $subject, $message, $from, $friendlyfrom,$htmlmessage,"","","",$bcc,$htmlhead);
		if($row_rsProductPrefs['dispatchsms']==1 && trim($smsmessage)!="" && isset($row_rsOrderDetails['BillingMobile'])) { // send sms also
			$response = sendSMS($row_rsOrderDetails['BillingMobile'], $smsmessage);		
		}
		
	}
     header("location: ".$_POST['returnURL']); exit;
} ?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Order Details: ".$row_rsOrderDetails['VendorTxCode']; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<link href="../../css/defaultProducts.css" rel="stylesheet"  />
<?php if(isset($body_class)) $body_class .= " products ";  ?>
<script>

$(document).ready(function(e) {
    toggleApproval()
});

function toggleApproval() {
	if($("#approvalrequired").is(':checked')) {
		$(".approved").show();
	} else {
		$(".approved").hide();
	}
}
</script>
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" -->
       
    <h1><i class="glyphicon glyphicon-shopping-cart"></i> Order Details <small>Order no: <?php echo $row_rsOrderDetails['ID']; ?> Transaction Code: <?php echo $row_rsOrderDetails['VendorTxCode']; ?> </small></h1>  <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
          <li class="nav-item"><a href="../index.php" class="nav-link"><i class="glyphicon glyphicon-arrow-left"></i> Back to orders</a></li>
          
      <li class="nav-item"><a href="../../payments/invoice.php?VendorTxCode=<?php echo $row_rsOrderDetails['VendorTxCode']; ?>&amp;token=<?php echo md5(PRIVATE_KEY.$row_rsOrderDetails['VendorTxCode']) ; ?>" target="_blank" class="nav-link" rel="noopener"><i class="glyphicon glyphicon-list-alt"></i> Show Invoice</a></li>
      
      <li class="nav-item"><a class="nav-link" href="/core/seo/admin/visitors/visitor-session.php?sessionID=<?php echo $row_rsOrderDetails['sessionID']; ?>" ><i class="glyphicon glyphicon-stats"></i> Customer Activity</a></li>     
          
       
          
          </ul></div></nav>
    <form action="<?php echo $_SERVER['REQUEST_URI'] ?>" method="post" name="form1" id="form1" class="form-inline">
    <p><strong>Status:</strong>
<select name="Status" id="Status" class="form-control">
  <option value="<?php echo $row_rsOrderDetails['Status']; ?>" <?php if (!(strcmp("", htmlentities($row_rsOrderDetails['Status'])))) {echo "selected=\"selected\"";} ?>><?php echo isset($row_rsOrderDetails['Status']) ? substr($row_rsOrderDetails['Status'],0,10) : "UNKNOWN"; ?></option>
  <option value="PENDING" <?php if (!(strcmp("PENDING", $row_rsOrderDetails['Status']))) {echo "selected=\"selected\"";} ?>>PENDING</option>
  <option value="ACCEPTED" <?php if (!(strcmp("ACCEPTED", $row_rsOrderDetails['Status']))) {echo "selected=\"selected\"";} ?>>ACCEPTED</option>
   <option value="INVOICE" <?php if (!(strcmp("INVOICE", $row_rsOrderDetails['Status']))) {echo "selected=\"selected\"";} ?>>INVOICE</option>
  <option value="COMPLETED" <?php if (!(strcmp("COMPLETED", $row_rsOrderDetails['Status']))) {echo "selected=\"selected\"";} ?>>COMPLETED</option>
  <option value="DECLINED" <?php if (!(strcmp("DECLINED", $row_rsOrderDetails['Status']))) {echo "selected=\"selected\"";} ?>>DECLINED</option>
  <option value="CANCELLED" <?php if (!(strcmp("CANCELLED", $row_rsOrderDetails['Status']))) {echo "selected=\"selected\"";} ?>>CANCELLED</option>
  <option value="REFUNDED" <?php if (!(strcmp("REFUNDED", $row_rsOrderDetails['Status']))) {echo "selected=\"selected\"";} ?>>REFUNDED</option>
  
  <option value="REFUNDED" <?php if (!(strcmp("REFUNDED", $row_rsOrderDetails['Status']))) {echo "selected=\"selected\"";} ?>>REFUNDED</option>
  
  <option value="VOID" <?php if (!(strcmp("VOID", $row_rsOrderDetails['Status']))) {echo "selected=\"selected\"";} ?>>VOID</option>
  
  
</select>&nbsp;<label><input type="checkbox" name="approvalrequired" id="approvalrequired" value="1" onClick="toggleApproval();" <?php if($row_rsOrderDetails['approvalrequired']==1) echo "checked"; ?>>&nbsp;Approval required</label> <input name="approvedbyID" type="hidden" value="<?php echo  $row_rsOrderDetails['approvedbyID']; ?>">
<input name="approveddatetime" type="hidden" value="<?php echo  $row_rsOrderDetails['approveddatetime']; ?>">&nbsp;<label class="approved"><input type="checkbox" name="approved" value="1" <?php if(isset($row_rsOrderDetails['approvedbyID'])) echo "checked"; ?>>&nbsp;Approved <?php echo isset($row_rsOrderDetails['approvedbyname']) ? " by ".$row_rsOrderDetails['approvedbyname'] : ""; echo isset($row_rsOrderDetails['approveddatetime']) ? " on ".date('d M Y H:s', strtotime($row_rsOrderDetails['approveddatetime'])) : ""; ?></label> 

<?php if($row_rsOrderDetails['confemailsent']==1) { ?> &nbsp;&nbsp; | &nbsp;&nbsp; Email confirmation sent  <?php } ?>

    <?php if(isset($row_rsOrderDetails['howdiscovered'])) { ?>
    &nbsp;&nbsp; | &nbsp;&nbsp; Customer stated they found site by: 
	<?php echo $row_rsOrderDetails['howdiscovered']; } ?>
    <?php
	if(isset($row_rsOrderDetails['sessionID'])) {
		 $referer = getHostReferer($row_rsOrderDetails['referer']); 
		 if(strlen($referer)>2) { echo " &nbsp;&nbsp; | &nbsp;&nbsp;  Referred from ".$referer." "; 
		 $keywords = getSearchTerms($row_rsOrderDetails['referer']); 
		 if(strlen($keywords)>0) {
			 echo "using search term: \"".$keywords."\" "; ?>
      
      <?php } } } ?></p>
      <p><strong>Date of sale:</strong> <?php echo date('d M Y H:i', strtotime($row_rsOrderDetails['createddatetime'])); ?> | <strong>Last updated: </strong><?php echo date('d M Y H:i', strtotime($row_rsOrderDetails['LastUpdated'])); ?> <?php echo isset($row_rsOrderDetails['firstname']) ? " by ".$row_rsOrderDetails['firstname']." ".$row_rsOrderDetails['surname'] : ""; ?></p>
     
    <table class="table table-hover">
    <thead>
      <tr>
        <th>Product</th>
        <th class="text-right text-nowrap">ex VAT</th>
        <th class="text-right  text-nowrap">inc VAT</th>
        <th>Qty</th>
         <th>Est Delivery</th>
        <th>Dispatched / Courier ID</th>
      </tr></thead><tbody>
      <?php do { $vatinc = ($row_rsOrderDetails['vatdefault']==0) ? $row_rsOrderDetails['vatincluded'] : $row_rsProductPrefs['vatincluded'];
		$vatrate = ($row_rsOrderDetails['vattype']>0) ? $row_rsThisRegion['vatrate'] : 0; 
		$prices = vatPrices($row_rsOrderDetails['Price'], $vatinc, $vatrate);?>
        <tr>
          <td><a href="../products/modify_product.php?productID=<?php echo $row_rsOrderDetails['productID']; ?>" target="_blank" rel="noopener"><?php echo $row_rsOrderDetails['categoryname']." - ".$row_rsOrderDetails['title']; ?></a> <?php if (isset($row_rsOrderDetails['optionname']) && $row_rsOrderDetails['optionname'] !="") {
			  echo " [".$row_rsOrderDetails['optionname']; 
			   echo isset($row_rsOrderDetails['finishname']) ? " (".$row_rsOrderDetails['finishname'].")" : ""; 
			   echo "]";
	  } ?> <?php echo (isset($row_rsOrderDetails['optiontext']) && $row_rsOrderDetails['optiontext']!="") ? " [".$row_rsOrderDetails['optiontext']."]" : ""; ?> <?php echo (isset($row_rsOrderDetails['optionsku']) && $row_rsOrderDetails['optionsku']!="") ? $row_rsOrderDetails['optionsku'] : $row_rsOrderDetails['sku']; 
		  echo isset($row_rsOrderDetails['productforusername']) ? "; For ".$row_rsOrderDetails['productforusername'] : ""; ?>
          <?php if($row_rsOrderDetails['uploadID']) { $select = "SELECT newfilename FROM uploads WHERE ID = ".intval($row_rsOrderDetails['uploadID']); 
		  $result = mysql_query($select, $aquiescedb);
		  $upload = mysql_fetch_assoc($result);
		  
		  echo "<a href=\"/Uploads/". str_replace(UPLOAD_ROOT,"",$upload['newfilename'])."\" target=\"_blank\">Download</a>"; } ?></td>
          <td class="text-right"><?php  echo number_format($prices['net'],2,".","");  ?></td>
           <td class="text-right"><?php  echo number_format($prices['gross'],2,".","");  ?><?php  echo isset($row_rsOrderDetails['priceper']) ? " ".$row_rsOrderDetails['priceper'] : "";  ?></td>
          <td><?php echo $row_rsOrderDetails['Quantity']; ?></td>
           <td><?php echo isset($row_rsOrderDetails['mindeliverydatetime']) ? date('d M Y', strtotime($row_rsOrderDetails['mindeliverydatetime'])) : "N/A";
		   echo (isset($row_rsOrderDetails['maxdeliverydatetime']) && $row_rsOrderDetails['maxdeliverydatetime']>$row_rsOrderDetails['mindeliverydatetime']) ? " - ".date('d M Y', strtotime($row_rsOrderDetails['maxdeliverydatetime'])) : ""; ?></td>
          <td>
            <input type="checkbox" name="dispatched[<?php echo $row_rsOrderDetails['orderID']; ?>]"  <?php if($row_rsOrderDetails['dispatched']==1) { echo "checked = \"checked\""; } ?> />
          <input type="hidden" name="dispatchedold[<?php echo $row_rsOrderDetails['orderID']; ?>]" value="<?php echo $row_rsOrderDetails['dispatched']; ?>"  />
          <input type="hidden" name="dispatcheddetail[<?php echo $row_rsOrderDetails['orderID']; ?>]" value="<?php echo $row_rsOrderDetails['title']; ?> <?php echo (isset($row_rsOrderDetails['optionname']) && $row_rsOrderDetails['optionname'] !="") ? " [".$row_rsOrderDetails['optionname']."]" : ""; ?> <?php echo (isset($row_rsOrderDetails['optiontext']) && $row_rsOrderDetails['optiontext']!="") ? " [".$row_rsOrderDetails['optiontext']."]" : ""; ?> @ <?php echo isset($row_rsOrderDetails['optionprice']) ? $row_rsOrderDetails['optionprice'] : $row_rsOrderDetails['Price']; ?> x <?php echo $row_rsOrderDetails['Quantity']; ?>" />
          
            <input name="couriertrackerID[<?php echo $row_rsOrderDetails['orderID']; ?>]" type="text"  size="20" maxlength="255" placeholder="Tracker ID (optional)" value="<?php echo $row_rsOrderDetails['couriertrackerID']; ?>" class="form-control">
         </td>
        </tr>
       
        <?php } while ($row_rsOrderDetails = mysql_fetch_assoc($rsOrderDetails)); 
		 mysql_data_seek($rsOrderDetails,0); $row_rsOrderDetails = mysql_fetch_assoc($rsOrderDetails); ?> 
         <?php if($totalRows_rsPromos>0) { 
		 do { 
		 
	$promos = vatPrices($row_rsPromos['amount']*-1, $row_rsProductPrefs['vatincluded'], $row_rsThisRegion['vatrate']);   
		
		?>
         <tr>
           <td><strong><?php echo $row_rsPromos['promotitle']; ?>:</strong></td>
           <td  class="text-right"><?php echo number_format($promos['net'],2,".",","); ?></td>
           <td  class="text-right"><?php echo number_format($promos['gross'],2,".",","); ?></td><td colspan="3">&nbsp;</td></tr>
         <?php } while($row_rsPromos = mysql_fetch_assoc($rsPromos));
		 }
		 $shipping = vatPrices($row_rsOrderDetails['shipping'], $row_rsProductPrefs['vatincluded'], $row_rsThisRegion['vatrate']); ?>
        <tr>
          <td> <strong>Shipping:</strong> 
    </td>
          <td class="text-right"><?php echo number_format($shipping['net'],2,".",","); ?></td>
          <td class="text-right"><?php echo number_format($shipping['gross'],2,".",","); ?></td>
          <td colspan="3">&nbsp;</td>
        </tr>
        <tr>
          <td><strong>TOTAL</strong> inc shipping &amp; promos (<?php echo $row_rsOrderDetails['TxType']; ?>) </td>
          <td class="text-right">&nbsp;</td>
          <td class="text-right"><strong><?php echo $row_rsOrderDetails['Currency']; ?> <?php echo $row_rsOrderDetails['Amount']; ?></strong></td>
         <td colspan="3">&nbsp;</td>
        </tr>
       
        <tr>
          <td><label for="AmountPaid"><strong>PAID</strong></label></td>
          <td class="text-right">&nbsp;</td>
          <td class="text-right"><input name="AmountPaid" id="AmountPaid" type="text" class="form-control text-right" value="<?php echo isset($row_rsOrderDetails['AmountPaid']) ? number_format($row_rsOrderDetails['AmountPaid'],2,".","") : ""; ?>" maxlength="10" size="6" ></td>
         <td colspan="3">&nbsp;</td>
        </tr></tbody>
    </table>

      <p class="form-inline"><label><input name="emaildispatched" type="checkbox" id="emaildispatched" value="1" checked="checked" />&nbsp;Auto-email/SMS customer if items dispatched</label>&nbsp;&nbsp;&nbsp;<label><input <?php if (!(strcmp($row_rsOrderDetails['optin'],1))) {echo "checked=\"checked\"";} ?> name="optin" type="checkbox" id="optin" value="1" disabled>&nbsp;Customer opts in to receive promotions</label></p>
      <p  class="form-inline"><input name="VendorTxCode" type="hidden" id="VendorTxCode" value="<?php echo htmlentities($_GET['VendorTxCode']); ?>" />
        <input name="modifiedbyID" type="hidden" id="modifiedbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>">
        <input name="returnURL" type="hidden" id="returnURL" value="<?php echo htmlentities($_GET['returnURL']); ?>" />
        <button type="submit" class="btn btn-primary" >Save changes</button>&nbsp;<label>
          <input <?php if (!(strcmp($row_rsOrderDetails['archive'],1))) {echo "checked=\"checked\"";} ?> name="archive" type="checkbox" id="archive" value="1" />&nbsp;Archive</label>
     </p>
      <table class="table">
       <tr><td class="top">
      <p><strong>Delivery Address:</strong></p><?php if($row_rsOrderDetails['deliverysame']==2) { ?><p><strong>WILL COLLECT</strong></p>
      <?php } else { ?> <?php if($row_rsOrderDetails['deliverytime']!="") { ?><p><strong>Preferred delivery time: <?php echo htmlentities($row_rsOrderDetails['deliverytime'], ENT_COMPAT, "UTF-8"); ?></strong></p>
      <?php } ?>
      <?php if($row_rsOrderDetails['deliveryinstructions']!="") { ?><p><strong><?php echo htmlentities($row_rsOrderDetails['deliveryinstructions'], ENT_COMPAT, "UTF-8"); ?></strong></p>
      <?php } ?>
      
      <p>
        <?php  echo $row_rsOrderDetails['DeliveryFirstnames']; ?>
        <?php echo $row_rsOrderDetails['DeliverySurname']; ?><br />
        <?php echo $row_rsOrderDetails['DeliveryCompany']; ?><br />
        <?php echo $row_rsOrderDetails['DeliveryAddress1']; ?><br />
        <?php echo $row_rsOrderDetails['DeliveryAddress2']; ?><br />
        <?php echo $row_rsOrderDetails['DeliveryCity']; ?><br />
        <?php echo $row_rsOrderDetails['DeliveryPostCode']; ?><br />
        <?php echo $row_rsOrderDetails['DeliveryState']; ?><br />
        <?php echo isset($row_rsOrderDetails['deliverycountryname']) ? $row_rsOrderDetails['deliverycountryname']: $row_rsOrderDetails['DeliveryCountry']; ?></p>
      <?php } ?>
      <p><strong>Phone:</strong> <?php echo $row_rsOrderDetails['DeliveryPhone']; ?></p>
      <p><strong>Customer email:</strong> <a href="mailto:<?php echo $row_rsOrderDetails['CustomerEMail']; ?>"><?php echo $row_rsOrderDetails['CustomerEMail']; ?></a></p></td><td class="top"> <p><strong>Billing Address:</strong></p>
      <p>
        <?php  echo $row_rsOrderDetails['BillingFirstnames']; ?>
        <?php echo $row_rsOrderDetails['BillingSurname']; ?><br />
        <?php echo $row_rsOrderDetails['BillingCompany']; ?><br />
        <?php echo $row_rsOrderDetails['BillingAddress1']; ?><br />
        <?php echo $row_rsOrderDetails['BillingAddress2']; ?><br />
        <?php echo $row_rsOrderDetails['BillingCity']; ?><br />
        <?php echo $row_rsOrderDetails['BillingPostCode']; ?><br />
        <?php echo $row_rsOrderDetails['BillingState']; ?><br />
        <?php echo isset($row_rsOrderDetails['billingcountryname']) ? $row_rsOrderDetails['billingcountryname']: $row_rsOrderDetails['BillingCountry']; ?></p>
      <p><strong>Phone:</strong> <?php echo $row_rsOrderDetails['BillingPhone']; ?></p>
      <p><strong>Mobile:</strong> <?php echo $row_rsOrderDetails['BillingMobile']; ?></p>
      
       <p><strong>VAT Number:</strong> <?php echo isset($row_rsOrderDetails['VATnumber']) ? $row_rsOrderDetails['VATnumber'] : "N/A"; ?></p>
        <p><strong>PO Number:</strong> <?php echo isset($row_rsOrderDetails['purchaseorder']) ? $row_rsOrderDetails['purchaseorder'] : "N/A"; ?></p>
      
       <?php if($row_rsOrderDetails['checkoutanswer1']!="") { ?><p><strong><?php echo $row_rsProductPrefs['checkoutquestion1']; ?>:</strong><br><?php echo nl2br(htmlentities($row_rsOrderDetails['checkoutanswer1'], ENT_COMPAT, "UTF-8")); ?></p>
      <?php } ?>
      
      
      </td></tr></table>
    </form>
     
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsOrderDetails);

mysql_free_result($rsPromos);

mysql_free_result($rsProductPrefs);

mysql_free_result($rsLoggedIn);

mysql_free_result($rsThisRegion);
?>