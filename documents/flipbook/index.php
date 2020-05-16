<?php require_once('../../Connections/aquiescedb.php'); ?>
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

$maxRows_rsFlipbooks = 50;
$pageNum_rsFlipbooks = 0;
if (isset($_GET['pageNum_rsFlipbooks'])) {
  $pageNum_rsFlipbooks = $_GET['pageNum_rsFlipbooks'];
}
$startRow_rsFlipbooks = $pageNum_rsFlipbooks * $maxRows_rsFlipbooks;

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsFlipbooks = "SELECT flipbook.* FROM flipbook WHERE flipbook.statusID = 1 ORDER BY flipbookname ASC";
$query_limit_rsFlipbooks = sprintf("%s LIMIT %d, %d", $query_rsFlipbooks, $startRow_rsFlipbooks, $maxRows_rsFlipbooks);
$rsFlipbooks = mysql_query($query_limit_rsFlipbooks, $aquiescedb) or die(mysql_error());
$row_rsFlipbooks = mysql_fetch_assoc($rsFlipbooks);

if (isset($_GET['totalRows_rsFlipbooks'])) {
  $totalRows_rsFlipbooks = $_GET['totalRows_rsFlipbooks'];
} else {
  $all_rsFlipbooks = mysql_query($query_rsFlipbooks);
  $totalRows_rsFlipbooks = mysql_num_rows($all_rsFlipbooks);
}
$totalPages_rsFlipbooks = ceil($totalRows_rsFlipbooks/$maxRows_rsFlipbooks)-1;

$queryString_rsFlipbooks = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsFlipbooks") == false && 
        stristr($param, "totalRows_rsFlipbooks") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsFlipbooks = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsFlipbooks = sprintf("&totalRows_rsFlipbooks=%d%s", $totalRows_rsFlipbooks, $queryString_rsFlipbooks);
?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Flipbooks"; echo $pageTitle." | ".$site_name; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" -->
    <h1><i class="glyphicon glyphicon-folder-open"></i> Flipbooks</h1>
    <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
      <li class="nav-item"><a href="../index.php" class="nav-link"><i class="glyphicon glyphicon-arrow-left"></i>  Documents</a></li>
    </ul></div></nav>
    <?php if ($totalRows_rsFlipbooks == 0) { // Show if recordset empty ?>
      <p>There are currently no flipbooks available.</p>
      <?php } // Show if recordset empty ?>
    <?php if ($totalRows_rsFlipbooks > 0) { // Show if recordset not empty ?>
  <p class="text-muted">Flipbooks <?php echo ($startRow_rsFlipbooks + 1) ?> to <?php echo min($startRow_rsFlipbooks + $maxRows_rsFlipbooks, $totalRows_rsFlipbooks) ?> of <?php echo $totalRows_rsFlipbooks ?> </p>
  <table  class="table table-hover">
  <thead>
    <tr><th>&nbsp;</th>
      
      <th>Name</th>
      <th>View</th>
      
    </tr></thead><tbody>
    <?php do { ?>
      <tr><td class="status<?php echo $row_rsFlipbooks['statusID']; ?>">&nbsp;</td>
        
        <td><?php echo $row_rsFlipbooks['flipbookname']; ?></td>
        <td><a href="flipbook.php?flipbookID=<?php echo $row_rsFlipbooks['ID']; ?>" class="link_edit icon_only">Edit</a></td>
        
      </tr>
      <?php } while ($row_rsFlipbooks = mysql_fetch_assoc($rsFlipbooks)); ?></tbody>
  </table>
  <?php } // Show if recordset not empty ?>
<table class="form-table">
  <tr>
    <td><?php if ($pageNum_rsFlipbooks > 0) { // Show if not first page ?>
        <a href="<?php printf("%s?pageNum_rsFlipbooks=%d%s", $currentPage, 0, $queryString_rsFlipbooks); ?>">First</a>
        <?php } // Show if not first page ?></td>
    <td><?php if ($pageNum_rsFlipbooks > 0) { // Show if not first page ?>
        <a href="<?php printf("%s?pageNum_rsFlipbooks=%d%s", $currentPage, max(0, $pageNum_rsFlipbooks - 1), $queryString_rsFlipbooks); ?>">Previous</a>
        <?php } // Show if not first page ?></td>
    <td><?php if ($pageNum_rsFlipbooks < $totalPages_rsFlipbooks) { // Show if not last page ?>
        <a href="<?php printf("%s?pageNum_rsFlipbooks=%d%s", $currentPage, min($totalPages_rsFlipbooks, $pageNum_rsFlipbooks + 1), $queryString_rsFlipbooks); ?>">Next</a>
        <?php } // Show if not last page ?></td>
    <td><?php if ($pageNum_rsFlipbooks < $totalPages_rsFlipbooks) { // Show if not last page ?>
        <a href="<?php printf("%s?pageNum_rsFlipbooks=%d%s", $currentPage, $totalPages_rsFlipbooks, $queryString_rsFlipbooks); ?>">Last</a>
        <?php } // Show if not last page ?></td>
  </tr>
</table>
    <!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsFlipbooks);
?>
