<?php require_once('../../../Connections/aquiescedb.php'); ?><?php require_once('../../../core/includes/adminAccess.inc.php'); ?>
<?php require_once('../inc/product_functions.inc.php'); ?>
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

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

if(isset($_POST['productID']) && intval($_POST['productID'])>0 ) {
	$regionID = isset($regionID) ? $regionID : 1;
	// insert options from products
	foreach($_POST['ID'] as $key =>$value) {
		$insert = "INSERT INTO productoptions(productID, optionname, price, stockcode, weight, finishID, versionID, regionID, createdbyID, createddatetime) VALUES (".intval($_POST['productID']).",".GetSQLValueString($_POST['title'][$key],"text").",".GetSQLValueString($_POST['price'][$key],"double").",".GetSQLValueString($_POST['sku'][$key],"text").",".GetSQLValueString($_POST['weight'][$key],"double").",".GetSQLValueString($_POST['finish'][$key],"text").",".GetSQLValueString($_POST['version'][$key],"double").",".GetSQLValueString($regionID,"int").",".GetSQLValueString($row_rsLoggedIn['ID'],"int").",'".date('Y-m-d H:i:s')."')";
		mysql_query($insert, $aquiescedb) or die(mysql_error()." ".$insert);;
		if($_POST['ID'][$key] != $_POST['productID']) {
			deleteProduct($key, $_POST['productID']);
			//echo "Delete ".$key." <br>";
		}
		
	}
	
	unset($_SESSION['checkbox']);
	header("location: index.php"); exit;
}
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Merge Products"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
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
    <h1><i class="glyphicon glyphicon-shopping-cart"></i> Merge Products</h1>
    <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
      <li><a href="index.php" class="link_undo"><i class="glyphicon glyphicon-arrow-left"></i> Products</a></li>
    </ul></div></nav>
<p>You can merge the following products into one main product with optios for the others. The main product will contain the description and photograph, and the options will contain the different titles and pioces, etc.</p>
    <p>Choose the Main Product below, the click merge:</p>
  <?php  mysql_select_db($database_aquiescedb, $aquiescedb);
 if(isset($_SESSION['checkbox']) && count($_SESSION['checkbox'])>1) {?>
 <form action="merge.php" method="post">
 <table class="table table-hover">
 <thead>
 <tr><th>Main</th><th>Product</th><th>Weight</th>
   <th>Finsish</th>
   <th>Version</th>
   <th>Price</th></tr></thead><tbody>
 <?php  foreach($_SESSION['checkbox'] as $key => $value) {
	  $select = "SELECT product.ID, title, sku, weight, finishID, finishname, versionID, versionname  price FROM product LEFT JOIN productfinish ON (product.finishID = productfinish.ID) LEFT JOIN productversion ON (product.versionID = productversion.ID) WHERE product.ID = ".intval($value). " GROUP BY product.ID";
 		$result = mysql_query($select, $aquiescedb) or die(mysql_error());
		$row = mysql_fetch_assoc($result); ?>
       <tr>
       <td>        <input name="productID" type="radio" value="<?php echo $row['ID']; ?>" /><input type="hidden" name="ID[<?php echo $row['ID']; ?>]" value="<?php echo $row['ID']; ?>"  /></td><td><?php echo $row['title']; ?> <?php echo $row['sku']; ?>
         <input type="hidden" name="title[<?php echo $row['ID']; ?>]"  value="<?php echo $row['title']; ?>"  />
         <input type="hidden" name="sku[<?php echo $row['ID']; ?>]"  value="<?php echo $row['sku']; ?>" />
         
         </td>
       <td><?php echo (isset($row['weight']) && $row['weight']>0) ? $row['weight'] : "N/A"; ?><input type="hidden" name="weight[<?php echo $row['ID']; ?>]" value="<?php echo $row['weight']; ?>"  /></td>
       <td><?php echo (isset($row['finishname']) && $row['finishname']>0) ? $row['finishname'] : "N/A"; ?><input type="hidden" name="finish[<?php echo $row['finishID']; ?>]" value="<?php echo $row['finishID']; ?>"  />&nbsp;</td>
       <td><?php echo (isset($row['versionname']) && $row['versionname']>0) ? $row['versionname'] : "N/A"; ?><input type="hidden" name="version[<?php echo $row['versionID']; ?>]" value="<?php echo $row['versionID']; ?>"  />&nbsp;</td>
       <td><?php echo number_format($row['price'],2,".",","); ?> <input type="hidden" name="price[<?php echo $row['ID']; ?>]" value="<?php echo $row['price']; ?>" /></td>
       </tr> 
		
 <?php  }  // end for each ?></tbody></table><button name="mergebutton" type="submit" class="btn btn-default btn-secondary" >Merge</button></form>
<?php  } // end OK
 else { ?>No products selected.<?php } ?>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);
?>
