<?php require_once('../../../../Connections/aquiescedb.php'); ?>
<?php require_once('../../../../core/includes/adminAccess.inc.php'); ?><?php require_once('../../../../core/includes/framework.inc.php'); ?>
<?php


$_GET['startdate'] = isset($_GET['startdate']) ? $_GET['startdate'] : date('Y-m-d', strtotime("1 MONTH AGO"));
$_GET['enddate'] = isset($_GET['enddate']) ? $_GET['enddate'] : date('Y-m-d');
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

$maxrows = isset($_GET['csv']) ? 5000 : 100;
$currentPage = $_SERVER["PHP_SELF"];

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

$maxRows_rsProductSearch = $maxrows;
$pageNum_rsProductSearch = 0;
if (isset($_GET['pageNum_rsProductSearch'])) {
  $pageNum_rsProductSearch = $_GET['pageNum_rsProductSearch'];
}
$startRow_rsProductSearch = $pageNum_rsProductSearch * $maxRows_rsProductSearch;

$varStartDate_rsProductSearch = "2000-01-01";
if (isset($_GET['startdate'])) {
  $varStartDate_rsProductSearch = $_GET['startdate'];
}
$varEndDate_rsProductSearch = "2999-01-01";
if (isset($_GET['enddate'])) {
  $varEndDate_rsProductSearch = $_GET['enddate'];
}
$varRegionID_rsProductSearch = "1";
if (isset($regionID)) {
  $varRegionID_rsProductSearch = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsProductSearch = sprintf("SELECT productsearch.searchterm, COUNT(productsearch.ID) AS countsearch FROM productsearch WHERE productsearch.regionID = %s AND DATE(productsearch.createddatetime) >= %s AND DATE(productsearch.createddatetime) <= %s  GROUP BY productsearch.searchterm ORDER BY countsearch DESC ", GetSQLValueString($varRegionID_rsProductSearch, "int"),GetSQLValueString($varStartDate_rsProductSearch, "date"),GetSQLValueString($varEndDate_rsProductSearch, "date"));
$query_limit_rsProductSearch = sprintf("%s LIMIT %d, %d", $query_rsProductSearch, $startRow_rsProductSearch, $maxRows_rsProductSearch);
$rsProductSearch = mysql_query($query_limit_rsProductSearch, $aquiescedb) or die(mysql_error());
$row_rsProductSearch = mysql_fetch_assoc($rsProductSearch);

if (isset($_GET['totalRows_rsProductSearch'])) {
  $totalRows_rsProductSearch = $_GET['totalRows_rsProductSearch'];
} else {
  $all_rsProductSearch = mysql_query($query_rsProductSearch);
  $totalRows_rsProductSearch = mysql_num_rows($all_rsProductSearch);
}
$totalPages_rsProductSearch = ceil($totalRows_rsProductSearch/$maxRows_rsProductSearch)-1;

$queryString_rsProductSearch = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsProductSearch") == false && 
        stristr($param, "totalRows_rsProductSearch") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsProductSearch = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsProductSearch = sprintf("&totalRows_rsProductSearch=%d%s", $totalRows_rsProductSearch, $queryString_rsProductSearch);

if(isset($_GET['csv'])) {
	
	exportCSV("", $rsProductSearch, $filename="Product-Search-YY-MM-DD");
	exit;
}



?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Product Search"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
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
    <?php require_once('../../../../core/region/includes/chooseregion.inc.php'); ?>
<div class="page class">
      <h1><i class="glyphicon glyphicon-shopping-cart"></i> Product Search</h1>
      <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
        <li class="nav-link"><a href="../index.php" class="nav-link"><i class="glyphicon glyphicon-arrow-left"></i> Products</a></li>
      <li class="nav-link"><a href="chronological.php" class="nav-link"><i class="glyphicon glyphicon-sort-by-attributes"></i> Chronological</a></li>   </ul></div></nav>
      <form  method="get" >
      <fieldset>
      <legend>Filter</legend>From <input name="startdate" id="startdate" type="hidden" value="<?php $setvalue = $_GET['startdate']; echo $setvalue; $inputname = "startdate"; ?>"><?php require('../../../../core/includes/datetimeinput.inc.php'); ?> until 
      <input name="enddate" id="enddate" type="hidden" value="<?php $setvalue = $_GET['enddate']; echo $setvalue; $inputname = "enddate"; ?>"><?php require('../../../../core/includes/datetimeinput.inc.php'); ?> <button  type="submit" class="btn btn-default btn-secondary" >Go</button>
      </fieldset>
      </form>
      <?php if ($totalRows_rsProductSearch == 0) { // Show if recordset empty ?>
        <p>There are no search terms matching your criteria</p>
        <?php } // Show if recordset empty ?>
<?php if ($totalRows_rsProductSearch > 0) { // Show if recordset not empty ?>
  <p class="text-muted">Search terms<?php echo ($startRow_rsProductSearch + 1) ?> to <?php echo min($startRow_rsProductSearch + $maxRows_rsProductSearch, $totalRows_rsProductSearch) ?> of <?php echo $totalRows_rsProductSearch ?> <a href="<?php $url =  $_SERVER['REQUEST_URI']; $url .= strpos($url,"?")>0 ? "&" : "?"; $url .= "csv=true"; echo $url; ?>" class="link_csv" >Download as spreadsheet</a></p>
        <table class="table table-hover">
        <thead>
          <tr>
            
            <th>Search term</th> <th>Count</th>
          </tr></thead><tbody>
          <?php do { ?>
            <tr>
              <td><?php echo $row_rsProductSearch['searchterm']; ?></td><td><?php echo $row_rsProductSearch['countsearch']; ?></td>
              
            </tr>
            <?php } while ($row_rsProductSearch = mysql_fetch_assoc($rsProductSearch)); ?></tbody>
        </table>
        <?php } // Show if recordset not empty ?>
<table border="0" class="form-table">
      <tr>
        <td><?php if ($pageNum_rsProductSearch > 0) { // Show if not first page ?>
            <a href="<?php printf("%s?pageNum_rsProductSearch=%d%s", $currentPage, 0, $queryString_rsProductSearch); ?>">First</a>
            <?php } // Show if not first page ?></td>
        <td><?php if ($pageNum_rsProductSearch > 0) { // Show if not first page ?>
            <a href="<?php printf("%s?pageNum_rsProductSearch=%d%s", $currentPage, max(0, $pageNum_rsProductSearch - 1), $queryString_rsProductSearch); ?>">Previous</a>
            <?php } // Show if not first page ?></td>
        <td><?php if ($pageNum_rsProductSearch < $totalPages_rsProductSearch) { // Show if not last page ?>
            <a href="<?php printf("%s?pageNum_rsProductSearch=%d%s", $currentPage, min($totalPages_rsProductSearch, $pageNum_rsProductSearch + 1), $queryString_rsProductSearch); ?>">Next</a>
            <?php } // Show if not last page ?></td>
        <td><?php if ($pageNum_rsProductSearch < $totalPages_rsProductSearch) { // Show if not last page ?>
            <a href="<?php printf("%s?pageNum_rsProductSearch=%d%s", $currentPage, $totalPages_rsProductSearch, $queryString_rsProductSearch); ?>">Last</a>
            <?php } // Show if not last page ?></td>
      </tr>
    </table>
</div>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsProductSearch);
?>
