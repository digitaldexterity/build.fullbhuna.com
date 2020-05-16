<?php require_once('../../Connections/aquiescedb.php'); ?>
<?php require_once('../../core/includes/adminAccess.inc.php'); ?>
<?php require_once('../payments/includes/logtransaction.inc.php'); ?>
<?php require_once('../../core/includes/framework.inc.php'); 

$_GET['startdate'] = (isset($_GET['startdate']) && $_GET['startdate']!="") ? $_GET['startdate'] : date('Y-m-d', strtotime("1 MONTH AGO"));
$_GET['enddate'] = (isset($_GET['enddate']) && $_GET['enddate']!="") ? $_GET['enddate'] : date('Y-m-d');

if (!isset($_SESSION)) {
  session_start();
}

if($_SESSION['MM_UserGroup']>=6 && $_SESSION['MM_UserGroup']<=7) { 
// Agents and Staff members can only access products
header("location: products/"); exit;
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

$currentPage = $_SERVER["PHP_SELF"];

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPromotions = "SELECT ID, promotitle, productpromo.promocode FROM productpromo ORDER BY promotitle ASC";
$rsPromotions = mysql_query($query_rsPromotions, $aquiescedb) or die(mysql_error());
$row_rsPromotions = mysql_fetch_assoc($rsPromotions);
$totalRows_rsPromotions = mysql_num_rows($rsPromotions);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsProductPrefs = "SELECT * FROM productprefs WHERE ID = ".intval($regionID);
$rsProductPrefs = mysql_query($query_rsProductPrefs, $aquiescedb) or die(mysql_error());
$row_rsProductPrefs = mysql_fetch_assoc($rsProductPrefs);
$totalRows_rsProductPrefs = mysql_num_rows($rsProductPrefs);



cleanSales();
	


mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsSupplier = "SELECT ID, name FROM directory ORDER BY name ASC";
$rsSupplier = mysql_query($query_rsSupplier, $aquiescedb) or die(mysql_error());
$row_rsSupplier = mysql_fetch_assoc($rsSupplier);
$totalRows_rsSupplier = mysql_num_rows($rsSupplier);

if(isset($_GET['csv'])) {
	require('inc/orders.inc.php'); 
	die();
}

?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Manage Orders"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script src="../payments/sagepay/scripts/common.js" ></script>
<style>
<!--
.status-CANCELLED {
	color: #900;
}
.status-COMPLETED {
	color: #060;
}

<?php

if(!isset($_GET['groupby']) || $_GET['groupby']==1) { 
	// customer orders
	echo ".product { display: none !important; } \n"; 
	echo ".supplier {display: none !important;} \n"; }
	
	
	 
else if(isset($_GET['groupby']) && $_GET['groupby']==0) { 
// products
echo ".lastupdated, .purchased, .txcode, .txtype, .status { display: none !important; } \n"; 	}
	
	
	
 ?>
-->
</style>
<link href="../css/defaultProducts.css" rel="stylesheet"  />
<?php if(isset($body_class)) $body_class .= " products ";  ?>
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" -->
        <div class="page products"><?php require_once('../../core/region/includes/chooseregion.inc.php'); ?>
       
          <h1><i class="glyphicon glyphicon-shopping-cart"></i> Orders</h1>
          <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
            <li class="nav-item"><a class="nav-link" href="/products/admin/products/index.php"><i class="glyphicon glyphicon-tags"></i> Products &amp; Categories</a></li>
            <li class="nav-item"><a class="nav-link" href="payments/index.php" ><i class="glyphicon glyphicon-gbp"></i> Pay now</a></li>
           
            <li class="nav-item"><a class="nav-link"a href="/products/admin/promotions/index.php"><i class="glyphicon glyphicon-certificate"></i> Promotions</a></li>
         
             <li class="nav-item"><a class="nav-link" href="/products/admin/crm/index.php"><i class="glyphicon glyphicon-heart"></i> Customer Relationship</a></li>
            <li class="nav-item"><a class="nav-link" href="options/auctions.php" ><i class="glyphicon glyphicon-bullhorn"></i> Auctions</a></li>
            <li class="nav-item"><a class="nav-link" href="/products/admin/options/index.php" ><i class="glyphicon glyphicon-gift"></i> Payment &amp; Shipping</a></li>
          <li class="nav-item"><a class="nav-link" href="/products/" target="_blank" rel="noopener"   onClick="openMainWindow('/products/'); return false;"><i class="glyphicon glyphicon-new-window"></i> Go to Shop</a></li>
          </ul></div></nav>
          <form>
            <fieldset class="form-group form-inline">
              <legend>Search</legend>
              <label>
                <input name="search" type="text" id="search" value="<?php echo isset($_REQUEST['search']) ? htmlentities($_REQUEST['search'], ENT_COMPAT, "UTF-8") : ""; ?>" size="40" maxlength="40" placeholder="Customer surname or Order no" class="form-control" />
              </label>
              between
              <input name="startdate" type="hidden" id="startdate" value="<?php $setvalue = isset($_GET['startdate']) ? htmlentities($_GET['startdate'], ENT_COMPAT, "UTF-8") : ""; echo $setvalue; $inputname = "startdate"; ?>" />
              <?php require('../../core/includes/datetimeinput.inc.php'); ?>
              and
              <input name="enddate" type="hidden" id="enddate" value="<?php $setvalue = isset($_GET['enddate']) ? htmlentities($_GET['enddate'], ENT_COMPAT, "UTF-8") : ""; echo $setvalue; $inputname = "enddate"; ?>" />
              <?php require('../../core/includes/datetimeinput.inc.php'); ?> 
              <label>Show archived:&nbsp;<input <?php if (!isset($_GET['showarchived']) || $_GET['showarchived']==1) {echo "checked=\"checked\"";} ?> name="showarchived" type="checkbox" id="showarchived" value="1" onClick="this.form.submit()" />
              </label>
              &nbsp;&nbsp;
              
              <label>Show only sales:&nbsp;<input <?php if (isset($_GET['onlysales']) && $_GET['onlysales']==1) {echo "checked=\"checked\"";} ?> name="onlysales" type="checkbox" id="onlysales" value="1" onClick="this.form.submit()" />
              </label>
              
              <br />
              <select name="orderby" onChange="this.form.submit()" class="form-control">
                <option value="1" >Sort by order date</option>
                <option value="2" <?php if(isset($_REQUEST['orderby']) && $_REQUEST['orderby']==2) echo "selected=\"selected\""; ?> >Sort by last updated</option>
              </select>
              
              <?php if ($totalRows_rsPromotions > 0) { // Show if recordset not empty ?>
                Filter by promotion:
                <select name="promotionID" id="promotionID"  class="form-control">
                  <option value="0" <?php if (!(strcmp(0, @$_GET['promotionID']))) {echo "selected=\"selected\"";} ?>><?php echo isset($region['text_choose']) ? htmlentities($region['text_choose'], ENT_COMPAT, "UTF-8") : "Choose..." ?></option>
                  <?php
do {  
?>
                  <option value="<?php echo $row_rsPromotions['ID']?>"<?php if (!(strcmp($row_rsPromotions['ID'], @$_GET['promotionID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsPromotions['ID']." ".$row_rsPromotions['promotitle']." ".$row_rsPromotions['promocode']; ?></option>
                  <?php
} while ($row_rsPromotions = mysql_fetch_assoc($rsPromotions));
  $rows = mysql_num_rows($rsPromotions);
  if($rows > 0) {
      mysql_data_seek($rsPromotions, 0);
	  $row_rsPromotions = mysql_fetch_assoc($rsPromotions);
  }
?>
                </select>
                <?php } // Show if recordset not empty ?>
              
              &nbsp;&nbsp;&nbsp;
              <label>
                <select name="supplierID" id="supplierID" class="supplier form-control" onChange="this.form.submit();">
                  <option value="0" <?php if (!(strcmp(0, $_GET['supplierID']))) {echo "selected=\"selected\"";} ?>>All suppliers</option>
                  <?php if(mysql_num_rows($rsSupplier) > 0) {
do {  
?>
                  <option value="<?php echo $row_rsSupplier['ID']?>"<?php if (!(strcmp($row_rsSupplier['ID'], $_GET['supplierID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsSupplier['name']?></option>
                  <?php
} while ($row_rsSupplier = mysql_fetch_assoc($rsSupplier));
				  }
  $rows = mysql_num_rows($rsSupplier);
  if($rows > 0) {
      mysql_data_seek($rsSupplier, 0);
	  $row_rsSupplier = mysql_fetch_assoc($rsSupplier);
  }
?>
                </select>
              </label>
              Group by: 
            
              <label>
              <input type="radio" name="groupby" value="1"   onClick="this.form.submit();" <?php if(!isset($_GET['groupby']) || $_GET['groupby'] == 1) echo "checked"; ?>> Customer Orders</label>   &nbsp;&nbsp;&nbsp;<!--<label><input type="radio" name="groupby" value="2" onClick="this.form.submit();"  <?php if(isset($_GET['groupby']) && $_GET['groupby'] == 2) echo "checked"; ?>> Suppliers</label>
                     &nbsp;&nbsp;&nbsp;--><label><input type="radio" name="groupby" value="0"  onClick="this.form.onlysales.checked=true; this.form.submit();"  <?php if(isset($_GET['groupby']) && $_GET['groupby'] == 0) echo "checked"; ?>> Products/Suppliers</label>
             &nbsp;&nbsp;&nbsp;
              <button type="submit" name="findbutton" id="findbutton"  class="btn btn-default btn-secondary">Find</button>
              <label>Export as CSV:
                <input name="csv" type="checkbox" id="csv" value="1" />
              </label>
            </fieldset>
          </form>
          <?php require('inc/orders.inc.php'); ?>
        </div>
     
        <p>NOTE: Credit Card and PayPal PENDING orders are automatically CANCELLED after an hour of inactivity.</p>
        <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsProductPrefs);

mysql_free_result($rsPromotions);

mysql_free_result($rsOrders);

mysql_free_result($rsSupplier);
?>
