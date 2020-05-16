<?php require_once('../../../../Connections/aquiescedb.php'); ?>
<?php require_once('../../../../core/includes/adminAccess.inc.php'); ?>
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

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

$maxRows_rsProductRedirects = 100;
$pageNum_rsProductRedirects = 0;
if (isset($_GET['pageNum_rsProductRedirects'])) {
  $pageNum_rsProductRedirects = $_GET['pageNum_rsProductRedirects'];
}
$startRow_rsProductRedirects = $pageNum_rsProductRedirects * $maxRows_rsProductRedirects;

$varRegionID_rsProductRedirects = "0";
if (isset($regionID)) {
  $varRegionID_rsProductRedirects = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsProductRedirects = sprintf("SELECT product .ID, product .title, product .custompageURL, product .redirect301 FROM product LEFT JOIN productinregion ON (product.ID = productinregion.productID) WHERE custompageURL != '' AND (%s = 0 OR productinregion.regionID = %s) ORDER BY custompageURL DESC", GetSQLValueString($varRegionID_rsProductRedirects, "int"),GetSQLValueString($varRegionID_rsProductRedirects, "int"));
$query_limit_rsProductRedirects = sprintf("%s LIMIT %d, %d", $query_rsProductRedirects, $startRow_rsProductRedirects, $maxRows_rsProductRedirects);
$rsProductRedirects = mysql_query($query_limit_rsProductRedirects, $aquiescedb) or die(mysql_error());
$row_rsProductRedirects = mysql_fetch_assoc($rsProductRedirects);

if (isset($_GET['totalRows_rsProductRedirects'])) {
  $totalRows_rsProductRedirects = $_GET['totalRows_rsProductRedirects'];
} else {
  $all_rsProductRedirects = mysql_query($query_rsProductRedirects);
  $totalRows_rsProductRedirects = mysql_num_rows($all_rsProductRedirects);
}
$totalPages_rsProductRedirects = ceil($totalRows_rsProductRedirects/$maxRows_rsProductRedirects)-1;
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = ""; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<style>
<!--
-->
</style>
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" -->
    <div class="page class">
      <h1>Product Redirects</h1>
      <?php if ($totalRows_rsProductRedirects == 0) { // Show if recordset empty ?>
  <p>There are currently no redirects in the system</p>
  <?php } // Show if recordset empty ?>
<?php if ($totalRows_rsProductRedirects > 0) { // Show if recordset not empty ?>
  <p>
    Redirects <?php echo ($startRow_rsProductRedirects + 1) ?> to <?php echo min($startRow_rsProductRedirects + $maxRows_rsProductRedirects, $totalRows_rsProductRedirects) ?> of <?php echo $totalRows_rsProductRedirects ?></p>
  <table class="table table-hover">
    <thead>
      <tr>
        <th>Product</th>
        <th>Redirect URL</th>
        <th>301</th>
      </tr></thead><tbody>
        <?php do { ?>
          <tr>
            <td><a href="../modify_product.php?productID=<?php echo $row_rsProductRedirects['ID']; ?>"><?php echo $row_rsProductRedirects['title']; ?></a></td>
            <td><?php echo $row_rsProductRedirects['custompageURL']; ?></td>
            <td class="tick<?php echo $row_rsProductRedirects['redirect301']; ?>">&nbsp;</td>
          </tr>
          <?php } while ($row_rsProductRedirects = mysql_fetch_assoc($rsProductRedirects)); ?></tbody>
  </table>
  <?php } // Show if recordset not empty ?>
    </div>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsProductRedirects);
?>
