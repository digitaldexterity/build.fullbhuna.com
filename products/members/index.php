<?php require_once('../../Connections/aquiescedb.php'); ?><?php require_once('../../core/includes/framework.inc.php'); ?>
<?php require_once('../includes/productHeader.inc.php'); ?><?php require_once('../includes/productFunctions.inc.php'); ?>
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

$MM_restrictGoTo = "../../login/index.php?msg=You need to be logged in to view your transactions";
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


$maxRows_rsOrders = 50;
$pageNum_rsOrders = 0;
if (isset($_GET['pageNum_rsOrders'])) {
  $pageNum_rsOrders = $_GET['pageNum_rsOrders'];
}
$startRow_rsOrders = $pageNum_rsOrders * $maxRows_rsOrders;

$varUsername_rsOrders = "-1";
if (isset($_SESSION['MM_Username'])) {
  $varUsername_rsOrders = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsOrders = sprintf("SELECT productorders.basket_json, productorders.createddatetime , productorders.VendorTxCode, productorders.Amount, productorders.Status, product.longID, product.title, product.sku, product.price, product.imageURL, product.statusID, productorderproducts.optionID, productorderproducts.optiontext, productorderproducts.Quantity, productcategory.longID AS productcategorylongID  , product.productcategoryID FROM productorders LEFT JOIN users ON (productorders.userID = users.ID) LEFT JOIN productorderproducts ON (productorders.VendorTxCode = productorderproducts.VendorTxCode) LEFT JOIN product ON (productorderproducts.productID = product.ID) LEFT JOIN  productcategory ON (product.productcategoryID = productcategory.ID) WHERE users.username = %s ORDER BY productorders.createddatetime DESC", GetSQLValueString($varUsername_rsOrders, "text"));
$query_limit_rsOrders = sprintf("%s LIMIT %d, %d", $query_rsOrders, $startRow_rsOrders, $maxRows_rsOrders);
$rsOrders = mysql_query($query_limit_rsOrders, $aquiescedb) or die(mysql_error());
$row_rsOrders = mysql_fetch_assoc($rsOrders);

if (isset($_GET['totalRows_rsOrders'])) {
  $totalRows_rsOrders = $_GET['totalRows_rsOrders'];
} else {
  $all_rsOrders = mysql_query($query_rsOrders);
  $totalRows_rsOrders = mysql_num_rows($all_rsOrders);
}
$totalPages_rsOrders = ceil($totalRows_rsOrders/$maxRows_rsOrders)-1;

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID, usertypeID, firstname, surname FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);
?>
<?php
$currentPage = $_SERVER["PHP_SELF"];
?>
<?php
$queryString_rsOrders = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsOrders") == false && 
        stristr($param, "totalRows_rsOrders") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsOrders = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsOrders = sprintf("&totalRows_rsOrders=%d%s", $totalRows_rsOrders, $queryString_rsOrders);
?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!DOCTYPE html>

<html class="" lang="en"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "My Account"; echo $pageTitle." | ".$site_name; ?>
</title>
<!--[if IE]><![endif]-->
<!-- InstanceEndEditable -->
<!--[if IE]><![endif]-->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<style>
<!--
-->
</style>
<!-- InstanceEndEditable -->
</head>
<body id="OffTemplate" class="bootstrap <?php echo $body_class;  ?>">
<?php require_once('../../local/includes/header.inc.php'); ?>
<main id="content"><!-- InstanceBeginEditable name="Body" -->
  <div class="container pageBody"><div class="crumbs">
      <div><span class="you_are_in">You are in: </span><a href="/">Home</a><span class="separator">&nbsp;&rsaquo;&nbsp;</span><a href="/products/"><?php echo $row_rsProductPrefs['shopTitle'];  ?></a>
       
        <span class="separator">&nbsp;&rsaquo;&nbsp;</span>My Account
      </div>
    </div>
    <h1>My Account</h1>
    <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
      <?php if($row_rsProductPrefs['auctions']==1) { ?>
      <li><a href="bids.php"><i class="glyphicon glyphicon-thumbs-up"></i> My Bids</a></li>
      <?php } ?>
      <li><a href="../../members/profile/contact_addresses.php" ><i class="glyphicon glyphicon-home"></i> <?php echo $row_rsPreferences['text_address_book']; ?></a></li>
      <li><a href="../../members/profile/update_profile.php" ><i class="glyphicon glyphicon-user"></i> My Profile</a></li>
      <?php if(isset($_SESSION['MM_UserGroup']) && $_SESSION['MM_UserGroup']>=7) { ?>
      <li><a href="/core/admin" target="_blank" rel="noopener"><i class="glyphicon glyphicon-cog"></i> Control Panel</a></li>
      <?php } ?><li><a href="../../login/logout.php" ><i class="glyphicon glyphicon-log-out"></i> <?php echo $row_rsPreferences['logouttext']; ?></a></li>
    </ul></div></nav>
    <h2><?php echo str_replace("{name}",$row_rsLoggedIn['firstname'] , $row_rsProductPrefs['text_welcomeback']); ?></h2>
    <h3><?php echo $row_rsProductPrefs['text_mypurchases']; ?></h3>
    <?php if ($totalRows_rsOrders == 0) { // Show if recordset empty ?>
      <p>You currently have no orders to show under you current log in (<?php echo $_SESSION['MM_Username']; ?>).</p>
      <?php } // Show if recordset empty ?>
    <?php if ($totalRows_rsOrders > 0) { // Show if recordset not empty ?>
      <p>Orders <?php echo ($startRow_rsOrders + 1) ?> to <?php echo min($startRow_rsOrders + $maxRows_rsOrders, $totalRows_rsOrders) ?> of <?php echo $totalRows_rsOrders ?> under your current login (<?php echo $_SESSION['MM_Username']; ?>).</p>
      <table class="table table-hover">
      <tbody>
        
        <?php $currentVendorTxCode = ""; ?>
        <?php do { ?>
        <?php if($row_rsOrders['VendorTxCode']!= $currentVendorTxCode) { $currentVendorTxCode = $row_rsOrders['VendorTxCode']; ?>
        </tbody><thead>
          <tr>
            <th><?php echo isset($row_rsOrders['createddatetime']) ?  date('d M Y', strtotime($row_rsOrders['createddatetime'])) : ""; ?></th>
            <th>Order: <?php echo $row_rsOrders['ID']; ?><br>TX Code: <?php echo $row_rsOrders['VendorTxCode']; ?></th>
            <th><?php echo $currency.number_format($row_rsOrders['Amount'],2,".",","); ?> <?php echo $row_rsOrders['Status']; ?> </th>
            
            <th><a href="order.php?VendorTxCode=<?php echo $row_rsOrders["VendorTxCode"]."&token=".md5($row_rsOrders["VendorTxCode"].PRIVATE_KEY)."&returnURL=".urlencode($_SERVER['REQUEST_URI']); ?>" class="btn btn-default btn-secondary"><i class="glyphicon glyphicon-search"></i> View Order Details</a></th>
            
            <th class="text-right"> <?php if(isset($row_rsOrders["basket_json"])) { ?><a href="/products/basket/index.php?VendorTxCode=<?php echo $row_rsOrders["VendorTxCode"]; ?>" class="btn btn-primary"><i class="glyphicon glyphicon-shopping-cart"></i> Buy Again</a><?php } ?></th>
            
          </tr></thead><tbody><?php } ?>
           <tr>
            <td><img src="<?php echo getImageURL($row_rsOrders['imageURL'], "thumb"); ?>"></td>
            <td><?php echo $row_rsOrders['title']; ?></td>
             <td><?php if(isset($row_rsProductPrefs['returnspolicyURL'])) { ?><a href="<?php echo $row_rsProductPrefs['returnspolicyURL']; ?>" >Return</a><?php } ?></td>
            <td><?php if($row_rsOrders['statusID']==1) { ?><a href="<?php echo productLink($row_rsOrders['productID'], $row_rsOrders['longID'], $row_rsOrders['productcategoryID'], $row_rsOrders['productcategorylongID']); ?>#productreviews" class="btn btn-default btn-secondary"><i class="glyphicon glyphicon-pencil"></i> Write review</a><?php }  ?>&nbsp;&nbsp;&nbsp;</td>
            <td  class="text-right"><?php if($row_rsOrders['statusID']==1) { ?><a href="<?php echo productLink($row_rsOrders['productID'], $row_rsOrders['longID'], $row_rsOrders['productcategoryID'], $row_rsOrders['productcategorylongID']); ?>" class="btn btn-default btn-secondary" role="button"><i class="glyphicon glyphicon-plus-sign"></i> Add to Basket</a><?php } else { ?>No longer available<?php } ?></td>
           
          </tr>
          <?php } while ($row_rsOrders = mysql_fetch_assoc($rsOrders)); ?>
    </tbody>  </table>
      <?php } // Show if recordset not empty ?>
    <table class="form-table">
      <tr>
        <td><?php if ($pageNum_rsOrders > 0) { // Show if not first page ?>
            <a href="<?php printf("%s?pageNum_rsOrders=%d%s", $currentPage, 0, $queryString_rsOrders); ?>">First</a>
            <?php } // Show if not first page ?></td>
        <td><?php if ($pageNum_rsOrders > 0) { // Show if not first page ?>
            <a href="<?php printf("%s?pageNum_rsOrders=%d%s", $currentPage, max(0, $pageNum_rsOrders - 1), $queryString_rsOrders); ?>">Previous</a>
            <?php } // Show if not first page ?></td>
        <td><?php if ($pageNum_rsOrders < $totalPages_rsOrders) { // Show if not last page ?>
            <a href="<?php printf("%s?pageNum_rsOrders=%d%s", $currentPage, min($totalPages_rsOrders, $pageNum_rsOrders + 1), $queryString_rsOrders); ?>">Next</a>
            <?php } // Show if not last page ?></td>
        <td><?php if ($pageNum_rsOrders < $totalPages_rsOrders) { // Show if not last page ?>
            <a href="<?php printf("%s?pageNum_rsOrders=%d%s", $currentPage, $totalPages_rsOrders, $queryString_rsOrders); ?>">Last</a>
            <?php } // Show if not last page ?></td>
      </tr>
    </table>
  </div>
  <!-- InstanceEndEditable --></main>
<?php require_once('../../local/includes/footer.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsOrders);

mysql_free_result($rsLoggedIn);
?>
