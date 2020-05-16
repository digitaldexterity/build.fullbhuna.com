<?php require_once('../../../Connections/aquiescedb.php'); ?>
<?php require_once('../../../core/includes/adminAccess.inc.php'); ?>
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

$currentPage = $_SERVER["PHP_SELF"];

$maxRows_rsFlipbooks = 50;
$pageNum_rsFlipbooks = 0;
if (isset($_GET['pageNum_rsFlipbooks'])) {
  $pageNum_rsFlipbooks = $_GET['pageNum_rsFlipbooks'];
}
$startRow_rsFlipbooks = $pageNum_rsFlipbooks * $maxRows_rsFlipbooks;

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsFlipbooks = "SELECT flipbook.*, photocategories.categoryname  FROM flipbook LEFT JOIN photocategories ON (flipbook.galleryID = photocategories.ID) ORDER BY flipbookname ASC";
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
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Manage Flipbooks"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
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
    <h1><i class="glyphicon glyphicon-folder-open"></i> Manage Flipbooks</h1>
    <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
      <li class="nav-item"><a href="add_flipbook.php" class="nav-link"><i class="glyphicon glyphicon-plus-sign"></i> Add Flipbook</a></li>
      <li class="nav-item"><a href="../index.php" class="nav-link"><i class="glyphicon glyphicon-arrow-left"></i> Manage Documents</a></li>
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
      <th>From Gallery</th>
      <th>Edit</th>
      
    </tr></thead><tbody>
    <?php do { ?>
      <tr><td class="status<?php echo $row_rsFlipbooks['statusID']; ?>">&nbsp;</td>
        
        <td><?php echo $row_rsFlipbooks['flipbookname']; ?></td>
        <td><?php echo $row_rsFlipbooks['categoryname']; ?></td>
        <td><a href="update_flipbook.php?flipbookID=<?php echo $row_rsFlipbooks['ID']; ?>" class="link_edit icon_only">Edit</a></td>
        
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
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsFlipbooks);
?>
