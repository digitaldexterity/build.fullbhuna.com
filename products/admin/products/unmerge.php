<?php require_once('../../../Connections/aquiescedb.php'); ?>
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

$MM_restrictGoTo = "/login/index.php?notloggedin=true&alert=".urlencode("You need to be logged in as an Administrator to access this page.");
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

$colname_rsProduct = "-1";
if (isset($_GET['productID'])) {
  $colname_rsProduct = $_GET['productID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsProduct = sprintf("SELECT * FROM product WHERE ID = %s", GetSQLValueString($colname_rsProduct, "int"));
$rsProduct = mysql_query($query_rsProduct, $aquiescedb) or die(mysql_error());
$row_rsProduct = mysql_fetch_assoc($rsProduct);
$totalRows_rsProduct = mysql_num_rows($rsProduct);

$colname_rsOptions = "-1";
if (isset($_GET['productID'])) {
  $colname_rsOptions = $_GET['productID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsOptions = sprintf("SELECT * FROM productoptions WHERE productID = %s", GetSQLValueString($colname_rsOptions, "int"));
$rsOptions = mysql_query($query_rsOptions, $aquiescedb) or die(mysql_error());
$row_rsOptions = mysql_fetch_assoc($rsOptions);
$totalRows_rsOptions = mysql_num_rows($rsOptions);
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Separate Options to Products"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<link href="../../css/defaultProducts.css" rel="stylesheet"  />
<?php if(isset($body_class)) $body_class .= " products ";  ?>
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
    <h1><i class="glyphicon glyphicon-shopping-cart"></i> Separate Options to Products</h1>
    
    <table  class="table table-hover">
     <tbody>
      <?php do { ?>
      <?php $insert = "INSERT INTO product (title, sku, versionID, finishID, weight, price, regionID, description, box_height, box_width, box_length, hazardous, imageURL, inputfield, isbn, manufacturerID, max_per_box, metadescription, pricetype, productcategoryID, shippingexempt, vattype) VALUES ("
	  .GetSQLValueString($row_rsOptions['optionname'], "text").","
	  .GetSQLValueString($row_rsOptions['stockcode'], "text").","
	  .GetSQLValueString($row_rsOptions['versionID'], "int").","
	  .GetSQLValueString($row_rsOptions['finishID'], "int").","
	  .GetSQLValueString($row_rsOptions['weight'], "double").","
	  .GetSQLValueString($row_rsOptions['price'], "double").","
	  .GetSQLValueString($row_rsOptions['regionID'], "int").","
	  .GetSQLValueString($row_rsProduct['box_height'], "text").","
	  .GetSQLValueString($row_rsProduct['box_width'], "text").","
	  .GetSQLValueString($row_rsProduct['box_length'], "text").","
	  .GetSQLValueString($row_rsProduct['hazardous'], "text").","
	  .GetSQLValueString($row_rsProduct['imageURL'], "text").","
	  .GetSQLValueString($row_rsProduct['inputfield'], "text").","
	  .GetSQLValueString($row_rsProduct['isbn'], "text").","
	  .GetSQLValueString($row_rsProduct['manufacturerID'], "text").","
	  .GetSQLValueString($row_rsProduct['max_per_box'], "text").","
	  .GetSQLValueString($row_rsProduct['metadescription'], "text").","
	  .GetSQLValueString($row_rsProduct['pricetype'], "text").","
	  .GetSQLValueString($row_rsProduct['productcategoryID'], "text").","
	  .GetSQLValueString($row_rsProduct['shippingexempt'], "text").","
	  .GetSQLValueString($row_rsProduct['vattype'], "text").")";
	  mysql_query($insert, $aquiescedb) or die(mysql_error());
	  $productID = mysql_insert_id();

	  
	   ?>;
        <tr>
        <td><?php echo $row_rsOptions['stockcode']; ?></td>
         
          <td><?php echo $row_rsOptions['optionname']; ?></td>
         
          <td><?php echo $row_rsOptions['price']; ?></td>
         
          <td><a href="modify_product.php?productID=<?php echo $productID;?>" class="link_edit icon_only">Edit</a></td>
        </tr>
        <?php } while ($row_rsOptions = mysql_fetch_assoc($rsOptions)); ?></tbody>
    </table>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsProduct);

mysql_free_result($rsOptions);
?>
