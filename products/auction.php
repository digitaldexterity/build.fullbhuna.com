<?php require_once('../Connections/aquiescedb.php'); ?><?php require_once('includes/productHeader.inc.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "";
$MM_donotCheckaccess = "true";

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
    if (($strUsers == "") && true) { 
      $isValid = true; 
    } 
  } 
  return $isValid; 
}

$MM_restrictGoTo = "../login/index.php?badlogin=true";
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
<?php require_once('includes/productFunctions.inc.php'); ?>
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

$colname_rsProduct = "-1";
if (isset($_GET['productID'])) {
  $colname_rsProduct = $_GET['productID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsProduct = sprintf("SELECT product.*, productbid.amount, productbid.createddatetime AS bidtime, productcategory.longID AS productcategorylongID FROM product LEFT JOIN productcategory ON (product.productcategoryID = productcategory.ID) LEFT JOIN productbid ON (product.ID = productbid.productID) WHERE product.ID = %s ORDER BY productbid.amount DESC LIMIT 1", GetSQLValueString($colname_rsProduct, "int"));
$rsProduct = mysql_query($query_rsProduct, $aquiescedb) or die(mysql_error());
$row_rsProduct = mysql_fetch_assoc($rsProduct);
$totalRows_rsProduct = mysql_num_rows($rsProduct);


$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID, firstname, surname FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);


$maxbid = isset($row_rsProduct['amount']) ? $row_rsProduct['amount'] : $row_rsProduct['startingbid'];
if($row_rsProduct['price']<$maxbid) {
	$update = "UPDATE product SET price = ".floatval($maxbid)." WHERE ID = ".$row_rsProduct['ID'];
	mysql_query($update, $aquiescedb) or die(mysql_error());
}



if(isset($_REQUEST['bid']) && floatval($_REQUEST['bid'])>0) {
	
	if($totalRows_rsProduct <1 || $row_rsProduct['statusID']!=1  ||$row_rsProductPrefs['auctions']!=1 ||  $row_rsProduct['auction']<1 || $row_rsProduct['instock']<1) { // auction ended
	 	$alert = "Sorry, bidding has closed on this item.";
	} else {
		$bid = floatval($_REQUEST['bid']);
		
		if($bid < $row_rsProduct['amount']+$row_rsProductPrefs['auctionminincrement']) {
			$error = "Sorry, you must bid at least ".$currency.number_format($row_rsProductPrefs['auctionminincrement'],2,".","")." higher than the last bid.";
			unset($bid);
		} else {
			if(isset($_POST['token']) && $_POST['token'] = md5(PRIVATE_KEY.$bid.$row_rsLoggedIn['ID'].$row_rsProduct['ID'])) { // has token, so confirmed
			$insert = "INSERT INTO productbid (productID, amount, createdbyID, createddatetime) VALUES (".$row_rsProduct['ID'].",".$bid.",".$row_rsLoggedIn['ID'].",'".date('Y-m-d H:i:s')."')";
			mysql_query($insert, $aquiescedb) or die(mysql_error());
		} // has token
	} // auction not ended
} // end no errors
	
}



// get YOUR current highest bid
$select = "SELECT amount,  createddatetime FROM productbid WHERE productID = ".$row_rsProduct['ID']." AND createdbyID = ".$row_rsLoggedIn['ID']." ORDER BY amount DESC LIMIT 1";
$result = mysql_query($select, $aquiescedb) or die(mysql_error());
if(mysql_num_rows($result)>0) {
	$mylastbid = mysql_fetch_assoc($result);
}

$productLink = productLink($row_rsProduct['ID'], $row_rsProduct['longID'], $row_rsProduct['productcategoryID'], $row_rsProduct['productcategorylongID']);
?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!DOCTYPE html>

<html class="" lang="en"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Bids"; echo $pageTitle." | ".$site_name; ?>
</title>
<!--[if IE]><![endif]-->
<!-- InstanceEndEditable -->
<!--[if IE]><![endif]-->
<?php require_once('../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<style>
<!--
-->
</style>
<!-- InstanceEndEditable -->
</head>
<body id="OffTemplate" class="bootstrap <?php echo $body_class;  ?>">
<?php require_once('../local/includes/header.inc.php'); ?>
<main id="content"><!-- InstanceBeginEditable name="Body" -->
      <section>
      <div class="container">
        <h1>Bidding for: <?php echo $row_rsProduct['title']; ?></h1>
        <h3>Auction closes: <?php echo date('d M Y H:i', strtotime($row_rsProduct['auctionenddatetime'])); ?><br>Current highest bid: <?php  echo (isset($mylastbid['amount']) && $mylastbid['amount']>$maxbid) ?  number_format($mylastbid['amount'],2)." (You)" : number_format($maxbid,2); echo isset($row_rsProduct['bidtime']) ? " at ".date('H:i', strtotime($row_rsProduct['bidtime']))." on ".date('d M Y', strtotime($row_rsProduct['bidtime'])) : "";  ?><?php if(isset($mylastbid['amount'])) { ?><br>Your highest bid:<?php echo number_format($mylastbid['amount'],2); echo " at ".date('H:i', strtotime($mylastbid['createddatetime']))." on ".date('d M Y', strtotime($mylastbid['createddatetime'])); } ?></h3>
        <?php require_once('../core/includes/alert.inc.php'); ?>
        <?php if(isset($bid) && !isset($_POST['token'])) { // bid to be confirmed 
		?>
  <form method="post" name="form1"> <p>Welcome, <?php echo $row_rsLoggedIn['firstname']; ?> <?php echo $row_rsLoggedIn['surname']; ?></p>
          <p>Please confirm your bid of  <?php echo number_format($bid,2); ?><button type="submit" class="btn btn-primary" >Confirm</button></p><input name="bid" type="hidden" value="<?php echo $bid; ?>">
         <input name="token" type="hidden" value="<?php echo md5(PRIVATE_KEY.$bid.$row_rsLoggedIn['ID'].$row_rsProduct['ID']); ?>">
          </form>
          <?php } else { 
         if(isset($bid)) { // bid ?>
          <p>Thank you, <?php echo $row_rsLoggedIn['firstname']; ?> <?php echo $row_rsLoggedIn['surname']; ?></p>
          <p>Your bid of  <?php echo number_format($bid,2); ?> has been approved.</p><?php } ?>
           <form method="get" name="form1">Your bid: <input name="bid" type="text" size="6" maxlength="6" value="<?php echo isset($_POST['bid']) ? htmlentities($_POST['bid'], ENT_COMPAT, "UTF-8") : ""; ?>"  /><button type="submit" class="btn btn-primary">Place Bid</button><input name="productID" type="hidden" value="<?php echo $row_rsProduct['ID']; ?>"></form>
          <?php } ?>
          <p><a href="<?php echo $productLink; ?>">View product</a></p></div></section>
        <!-- InstanceEndEditable --></main>
<?php require_once('../local/includes/footer.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsProduct);

mysql_free_result($rsLoggedIn);
?>
