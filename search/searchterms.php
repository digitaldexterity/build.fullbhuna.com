<?php require_once('../Connections/aquiescedb.php');

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

$_GET['totalRows_rsSearch'] = intval($_GET['totalRows_rsSearch']);
$_GET['pageNum_rsSearch'] = intval($_GET['pageNum_rsSearch']);

$maxRows_rsSearch = 50;
$pageNum_rsSearch = 0;
if (isset($_GET['pageNum_rsSearch'])) {
  $pageNum_rsSearch = $_GET['pageNum_rsSearch'];
}
$startRow_rsSearch = $pageNum_rsSearch * $maxRows_rsSearch;

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsSearch = "SELECT id, search_term, `time`, sum(matches) AS matches FROM isearch_search_log WHERE  regionID = ".intval($regionID) ." GROUP BY  search_term ORDER BY `time` DESC";
$query_limit_rsSearch = sprintf("%s LIMIT %d, %d", $query_rsSearch, $startRow_rsSearch, $maxRows_rsSearch);
$rsSearch = mysql_query($query_limit_rsSearch, $aquiescedb) or die();
$row_rsSearch = mysql_fetch_assoc($rsSearch);

if (isset($_GET['totalRows_rsSearch'])) {
  $totalRows_rsSearch = $_GET['totalRows_rsSearch'];
} else {
  $all_rsSearch = mysql_query($query_rsSearch);
  $totalRows_rsSearch = mysql_num_rows($all_rsSearch);
}
$totalPages_rsSearch = ceil($totalRows_rsSearch/$maxRows_rsSearch)-1;

$currentPage = $_SERVER["PHP_SELF"];

$queryString_rsSearch = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsSearch") == false && 
        stristr($param, "totalRows_rsSearch") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsSearch = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsSearch = sprintf("&totalRows_rsSearch=%d%s", $totalRows_rsSearch, $queryString_rsSearch);
?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html><html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Search Terms"; echo $pageTitle." | ".$site_name; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" -->
    <h1>Search Terms</h1>
    <p>The most recent search terms used on this site:</p>
    <?php if ($totalRows_rsSearch == 0) { // Show if recordset empty ?>
      <p>There are currently no serach terms in the system.</p>
      <?php } // Show if recordset empty ?>
    <?php if ($totalRows_rsSearch > 0) { // Show if recordset not empty ?>
  <p>Searches <?php echo ($startRow_rsSearch + 1) ?> to <?php echo min($startRow_rsSearch + $maxRows_rsSearch, $totalRows_rsSearch) ?> of 
    <?php echo $totalRows_rsSearch ?></p>
  <?php } // Show if recordset not empty ?><table class="listTable">
<?php do { $link = (defined("MOD_REWRITE") || isset($_SERVER['HTTP_MOD_REWRITE'])) ? "/terms/".str_replace(" ","_",preg_replace("/[^a-zA-Z0-9_\-]/", "", $row_rsSearch['search_term']))."/" : "/search/index.php?s=".urlencode($row_rsSearch['search_term']); ?><tr><td>
<?php
echo date('d M Y H:i',$row_rsSearch['time']); ?></td><td><a href="<?php echo $link; ?>"><?php echo $row_rsSearch['search_term']; ?></a></td><td> (<?php echo $row_rsSearch['matches']; ?>)</td></tr>
        <?php } while ($row_rsSearch = mysql_fetch_assoc($rsSearch)); ?></table>
        <table class="form-table">
          <tr>
            <td><?php if ($pageNum_rsSearch > 0) { // Show if not first page ?>
                <a href="<?php printf("%s?pageNum_rsSearch=%d%s", $currentPage, 0, $queryString_rsSearch); ?>">First</a>
                <?php } // Show if not first page ?></td>
            <td><?php if ($pageNum_rsSearch > 0) { // Show if not first page ?>
                <a href="<?php printf("%s?pageNum_rsSearch=%d%s", $currentPage, max(0, $pageNum_rsSearch - 1), $queryString_rsSearch); ?>">Previous</a>
                <?php } // Show if not first page ?></td>
            <td><?php if ($pageNum_rsSearch < $totalPages_rsSearch) { // Show if not last page ?>
                <a href="<?php printf("%s?pageNum_rsSearch=%d%s", $currentPage, min($totalPages_rsSearch, $pageNum_rsSearch + 1), $queryString_rsSearch); ?>">Next</a>
                <?php } // Show if not last page ?></td>
            <td><?php if ($pageNum_rsSearch < $totalPages_rsSearch) { // Show if not last page ?>
                <a href="<?php printf("%s?pageNum_rsSearch=%d%s", $currentPage, $totalPages_rsSearch, $queryString_rsSearch); ?>">Last</a>
                <?php } // Show if not last page ?></td>
          </tr>
        </table>
  <!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsSearch);
?>
