<?php require_once('../../Connections/aquiescedb.php'); ?><?php require_once('../includes/productHeader.inc.php'); ?>
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

$MM_restrictGoTo = "../../login/index.php?msg=You need to be logged in to view your bids.";
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
?>
<?php
$currentPage = $_SERVER["PHP_SELF"];

$maxRows_rsBids = 50;
$pageNum_rsBids = 0;
if (isset($_GET['pageNum_rsBids'])) {
  $pageNum_rsBids = $_GET['pageNum_rsBids'];
}
$startRow_rsBids = $pageNum_rsBids * $maxRows_rsBids;

$varUsername_rsBids = "-1";
if (isset($_SESSION['MM_Username'])) {
  $varUsername_rsBids = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsBids = sprintf("SELECT productbid.ID, productbid.productID, productbid.amount, product.title, productbid.winning FROM productbid LEFT JOIN users ON (productbid.createdbyID = users.ID) LEFT JOIN product ON (productbid.productID = product.ID) WHERE users.username = %s", GetSQLValueString($varUsername_rsBids, "text"));
$query_limit_rsBids = sprintf("%s LIMIT %d, %d", $query_rsBids, $startRow_rsBids, $maxRows_rsBids);
$rsBids = mysql_query($query_limit_rsBids, $aquiescedb) or die(mysql_error());
$row_rsBids = mysql_fetch_assoc($rsBids);

if (isset($_GET['totalRows_rsBids'])) {
  $totalRows_rsBids = $_GET['totalRows_rsBids'];
} else {
  $all_rsBids = mysql_query($query_rsBids);
  $totalRows_rsBids = mysql_num_rows($all_rsBids);
}
$totalPages_rsBids = ceil($totalRows_rsBids/$maxRows_rsBids)-1;

$queryString_rsBids = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsBids") == false && 
        stristr($param, "totalRows_rsBids") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsBids = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsBids = sprintf("&totalRows_rsBids=%d%s", $totalRows_rsBids, $queryString_rsBids);
?><?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!DOCTYPE html>

<html class="" lang="en"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "My Bids"; echo $pageTitle." | ".$site_name; ?>
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
<main id="content"><!-- InstanceBeginEditable name="Body" --> <div class="container pageBody">
          <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
            <li>
             <a href="index.php" class="link_undo"><i class="glyphicon glyphicon-arrow-left"></i> Transactions</a>
            </li>
          </ul></div></nav>
          <h1>My Bids</h1>
          <?php if ($totalRows_rsBids == 0) { // Show if recordset empty ?>
            <p>You have no bids under your current login.</p>
            <?php } // Show if recordset empty ?>
          <?php if ($totalRows_rsBids > 0) { // Show if recordset not empty ?>
            <p class="text-muted">Bids <?php echo ($startRow_rsBids + 1) ?> to <?php echo min($startRow_rsBids + $maxRows_rsBids, $totalRows_rsBids) ?> of <?php echo $totalRows_rsBids ?></p>
            <table class="table table-hover">
            <tbody>
              <?php do { ?>
                <tr>
                  <td><?php echo date('d M Y H:s', strtotime($row_rsBids['createddatetime'])); ?></td>
                  <td><?php echo $row_rsBids['title']; ?></td>
                  <td><?php echo $currency.number_format($row_rsBids['amount'], 2, ".", ","); ?></td>
                  <td><?php if( $row_rsBids['winning']==1) { ?>Winning Bid! <a href="../basket/index.php?addtobasket=true&productID=<?php echo $row_rsBids['productID']; ?>">Pay</a>                    <?php } ?></td>
                </tr>
                <?php } while ($row_rsBids = mysql_fetch_assoc($rsBids)); ?>
           </tbody> </table>
            <?php } // Show if recordset not empty ?>
          <table class="form-table">
            <tr>
              <td><?php if ($pageNum_rsBids > 0) { // Show if not first page ?>
                  <a href="<?php printf("%s?pageNum_rsBids=%d%s", $currentPage, 0, $queryString_rsBids); ?>">First</a>
              <?php } // Show if not first page ?></td>
              <td><?php if ($pageNum_rsBids > 0) { // Show if not first page ?>
                  <a href="<?php printf("%s?pageNum_rsBids=%d%s", $currentPage, max(0, $pageNum_rsBids - 1), $queryString_rsBids); ?>">Previous</a>
              <?php } // Show if not first page ?></td>
              <td><?php if ($pageNum_rsBids < $totalPages_rsBids) { // Show if not last page ?>
                  <a href="<?php printf("%s?pageNum_rsBids=%d%s", $currentPage, min($totalPages_rsBids, $pageNum_rsBids + 1), $queryString_rsBids); ?>">Next</a>
              <?php } // Show if not last page ?></td>
              <td><?php if ($pageNum_rsBids < $totalPages_rsBids) { // Show if not last page ?>
                  <a href="<?php printf("%s?pageNum_rsBids=%d%s", $currentPage, $totalPages_rsBids, $queryString_rsBids); ?>">Last</a>
              <?php } // Show if not last page ?></td>
            </tr>
          </table></div> 
        <!-- InstanceEndEditable --></main>
<?php require_once('../../local/includes/footer.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsBids);
?>
