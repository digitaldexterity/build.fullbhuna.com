<?php require_once('../../../../Connections/aquiescedb.php'); ?>
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

$maxRows_rsProductSearches = 1000;
$pageNum_rsProductSearches = 0;
if (isset($_GET['pageNum_rsProductSearches'])) {
  $pageNum_rsProductSearches = $_GET['pageNum_rsProductSearches'];
}
$startRow_rsProductSearches = $pageNum_rsProductSearches * $maxRows_rsProductSearches;

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsProductSearches = "SELECT datetime, page, sessionID FROM track_page WHERE page  LIKE '/products/search.php?productsearch=%' ORDER BY datetime DESC";
$query_limit_rsProductSearches = sprintf("%s LIMIT %d, %d", $query_rsProductSearches, $startRow_rsProductSearches, $maxRows_rsProductSearches);
$rsProductSearches = mysql_query($query_limit_rsProductSearches, $aquiescedb) or die(mysql_error());
$row_rsProductSearches = mysql_fetch_assoc($rsProductSearches);

if (isset($_GET['totalRows_rsProductSearches'])) {
  $totalRows_rsProductSearches = $_GET['totalRows_rsProductSearches'];
} else {
  $all_rsProductSearches = mysql_query($query_rsProductSearches);
  $totalRows_rsProductSearches = mysql_num_rows($all_rsProductSearches);
}
$totalPages_rsProductSearches = ceil($totalRows_rsProductSearches/$maxRows_rsProductSearches)-1;

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPageSearch = "SELECT * FROM isearch_search_log";
$rsPageSearch = mysql_query($query_rsPageSearch, $aquiescedb) or die(mysql_error());
$row_rsPageSearch = mysql_fetch_assoc($rsPageSearch);
$totalRows_rsPageSearch = mysql_num_rows($rsPageSearch);

$currentPage = $_SERVER["PHP_SELF"];

$queryString_rsProductSearches = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsProductSearches") == false && 
        stristr($param, "totalRows_rsProductSearches") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsProductSearches = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsProductSearches = sprintf("&totalRows_rsProductSearches=%d%s", $totalRows_rsProductSearches, $queryString_rsProductSearches);
?><!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Product Searches"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../includes/seo.inc.php'); ?>
<?php require_once('../../../includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<style>
<!--
-->
</style>
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../../includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" -->
    
    
    <h1><i class="glyphicon glyphicon-globe"></i> Page Searches</h1>
    
    
    <h1><i class="glyphicon glyphicon-globe"></i> Product Searches</h1>
    <?php if ($totalRows_rsProductSearches == 0) { // Show if recordset empty ?>
  <p>Sorry there are no searches stored at present.</p>
  <?php } // Show if recordset empty ?>
    <?php if ($totalRows_rsProductSearches > 0) { // Show if recordset not empty ?>
      <p class="text-muted">All searches (<?php echo $totalRows_rsProductSearches ?>) most recent first.</p>
     <table class="table table-hover"><thead>
        <tr>
          <th>Date/time</th>
          <th>Search terms</th>
          <th>View</th>
        </tr></thead><tbody>
        <?php do { ?>
          <tr>
            <td><?php echo date('d M Y H:i', strtotime($row_rsProductSearches['datetime'])); ?></td>
            <td><?php $urlparts = parse_url($row_rsProductSearches['page']); 
	
	 parse_str($urlparts['query'], $get_array); 
	 echo $get_array['productsearch'];
	?></td>
            <td><a href="visitor-session.php?sessionID=<?php echo $row_rsProductSearches['sessionID']; ?>" class="link_view">View session</a></td>
          </tr>
          <?php } while ($row_rsProductSearches = mysql_fetch_assoc($rsProductSearches)); ?></tbody>
      </table>
      <br>
      <table border="0" class="form-table">
        <tr>
          <td><?php if ($pageNum_rsProductSearches > 0) { // Show if not first page ?>
              <a href="<?php printf("%s?pageNum_rsProductSearches=%d%s", $currentPage, 0, $queryString_rsProductSearches); ?>">First</a>
              <?php } // Show if not first page ?></td>
          <td><?php if ($pageNum_rsProductSearches > 0) { // Show if not first page ?>
              <a href="<?php printf("%s?pageNum_rsProductSearches=%d%s", $currentPage, max(0, $pageNum_rsProductSearches - 1), $queryString_rsProductSearches); ?>">Previous</a>
              <?php } // Show if not first page ?></td>
          <td><?php if ($pageNum_rsProductSearches < $totalPages_rsProductSearches) { // Show if not last page ?>
              <a href="<?php printf("%s?pageNum_rsProductSearches=%d%s", $currentPage, min($totalPages_rsProductSearches, $pageNum_rsProductSearches + 1), $queryString_rsProductSearches); ?>">Next</a>
              <?php } // Show if not last page ?></td>
          <td><?php if ($pageNum_rsProductSearches < $totalPages_rsProductSearches) { // Show if not last page ?>
              <a href="<?php printf("%s?pageNum_rsProductSearches=%d%s", $currentPage, $totalPages_rsProductSearches, $queryString_rsProductSearches); ?>">Last</a>
              <?php } // Show if not last page ?></td>
        </tr>
      </table>
<?php } // Show if recordset not empty ?>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsProductSearches);

mysql_free_result($rsPageSearch);
?>
