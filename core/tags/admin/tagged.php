<?php require_once('../../../Connections/aquiescedb.php'); ?>
<?php require_once('../../includes/adminAccess.inc.php'); ?>
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

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

$maxRows_rsTagged = 50;
$pageNum_rsTagged = 0;
if (isset($_GET['pageNum_rsTagged'])) {
  $pageNum_rsTagged = $_GET['pageNum_rsTagged'];
}
$startRow_rsTagged = $pageNum_rsTagged * $maxRows_rsTagged;

$colname_rsTagged = "-1";
if (isset($_GET['tagID'])) {
  $colname_rsTagged = $_GET['tagID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsTagged = sprintf("SELECT tagged.*, blogentry.blogentrytitle, eventgroup.eventtitle, users.firstname, users.surname FROM tagged LEFT JOIN blogentry ON (tagged.blogentryID = blogentry.ID) LEFT JOIN eventgroup ON (tagged.eventgroupID = eventgroup.ID) LEFT JOIN users ON (tagged .createdbyID=users.ID) WHERE tagID = %s", GetSQLValueString($colname_rsTagged, "int"));
$query_limit_rsTagged = sprintf("%s LIMIT %d, %d", $query_rsTagged, $startRow_rsTagged, $maxRows_rsTagged);
$rsTagged = mysql_query($query_limit_rsTagged, $aquiescedb) or die(mysql_error());
$row_rsTagged = mysql_fetch_assoc($rsTagged);

if (isset($_GET['totalRows_rsTagged'])) {
  $totalRows_rsTagged = $_GET['totalRows_rsTagged'];
} else {
  $all_rsTagged = mysql_query($query_rsTagged);
  $totalRows_rsTagged = mysql_num_rows($all_rsTagged);
}
$totalPages_rsTagged = ceil($totalRows_rsTagged/$maxRows_rsTagged)-1;

$queryString_rsTagged = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsTagged") == false && 
        stristr($param, "totalRows_rsTagged") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsTagged = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsTagged = sprintf("&totalRows_rsTagged=%d%s", $totalRows_rsTagged, $queryString_rsTagged);
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Tagged"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../seo/includes/seo.inc.php'); ?>
<?php require_once('../../includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<style>
<!--
-->
</style>
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" -->
    <div class="page class">
      <h1>Tagged</h1>
      <?php if ($totalRows_rsTagged == 0) { // Show if recordset empty ?>
        <p>There are no items tagged.</p>
        <?php } // Show if recordset empty ?>
      <?php if ($totalRows_rsTagged > 0) { // Show if recordset not empty ?>
        <p class="text-muted">Items <?php echo ($startRow_rsTagged + 1) ?> to <?php echo min($startRow_rsTagged + $maxRows_rsTagged, $totalRows_rsTagged) ?> of <?php echo $totalRows_rsTagged ?></p>
        <table class="table table-hover">
        <thead>
          <tr>
            <th>ID</th><th>Added</th>
          <th>Blog</th>
            <th>Event</th>
            <th>Added by</th>
            
          </tr></thead><tbody>
          <?php do { ?>
            <tr>
              <td><?php echo $row_rsTagged['ID']; ?></td><td><?php echo date('d M Y H:i', strtotime($row_rsTagged['createddatetime'])); ?></td>
              <td><?php echo $row_rsTagged['blogentrytitle']; ?></td>
              <td><?php echo $row_rsTagged['eventtitle']; ?></td>
              <td><?php echo $row_rsTagged['firstname']; ?> <?php echo $row_rsTagged['surname']; ?></td>
              
            </tr>
            <?php } while ($row_rsTagged = mysql_fetch_assoc($rsTagged)); ?></tbody>
        </table>
        <table class="form-table">
          <tr>
            <td><?php if ($pageNum_rsTagged > 0) { // Show if not first page ?>
                <a href="<?php printf("%s?pageNum_rsTagged=%d%s", $currentPage, 0, $queryString_rsTagged); ?>">First</a>
                <?php } // Show if not first page ?></td>
            <td><?php if ($pageNum_rsTagged > 0) { // Show if not first page ?>
                <a href="<?php printf("%s?pageNum_rsTagged=%d%s", $currentPage, max(0, $pageNum_rsTagged - 1), $queryString_rsTagged); ?>">Previous</a>
                <?php } // Show if not first page ?></td>
            <td><?php if ($pageNum_rsTagged < $totalPages_rsTagged) { // Show if not last page ?>
                <a href="<?php printf("%s?pageNum_rsTagged=%d%s", $currentPage, min($totalPages_rsTagged, $pageNum_rsTagged + 1), $queryString_rsTagged); ?>">Next</a>
                <?php } // Show if not last page ?></td>
            <td><?php if ($pageNum_rsTagged < $totalPages_rsTagged) { // Show if not last page ?>
                <a href="<?php printf("%s?pageNum_rsTagged=%d%s", $currentPage, $totalPages_rsTagged, $queryString_rsTagged); ?>">Last</a>
                <?php } // Show if not last page ?></td>
          </tr>
        </table>
        <?php } // Show if recordset not empty ?>
    </div>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsTagged);
?>
