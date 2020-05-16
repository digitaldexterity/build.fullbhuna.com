<?php require_once('../Connections/aquiescedb.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
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

if(isset($_GET['pageNum_rsForms'])) $_GET['pageNum_rsForms'] = intval($_GET['pageNum_rsForms']);
if(isset($_GET['totalRows_rsForms'])) $_GET['totalRows_rsForms'] = intval($_GET['totalRows_rsForms']);


$maxRows_rsForms = 20;
$pageNum_rsForms = 0;
if (isset($_GET['pageNum_rsForms'])) {
  $pageNum_rsForms = $_GET['pageNum_rsForms'];
}
$startRow_rsForms = $pageNum_rsForms * $maxRows_rsForms;

$varRegionID_rsForms = "1";
if (isset($regionID)) {
  $varRegionID_rsForms = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsForms = sprintf("SELECT * FROM `form` WHERE regionID = %s AND statusID =1", GetSQLValueString($varRegionID_rsForms, "int"));
$query_limit_rsForms = sprintf("%s LIMIT %d, %d", $query_rsForms, $startRow_rsForms, $maxRows_rsForms);
$rsForms = mysql_query($query_limit_rsForms, $aquiescedb) or die(mysql_error());
$row_rsForms = mysql_fetch_assoc($rsForms);

if (isset($_GET['totalRows_rsForms'])) {
  $totalRows_rsForms = $_GET['totalRows_rsForms'];
} else {
  $all_rsForms = mysql_query($query_rsForms);
  $totalRows_rsForms = mysql_num_rows($all_rsForms);
}
$totalPages_rsForms = ceil($totalRows_rsForms/$maxRows_rsForms)-1;

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = "SELECT userscanlogin FROM preferences WHERE ID = $regionID";
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);

$queryString_rsForms = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsForms") == false && 
        stristr($param, "totalRows_rsForms") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsForms = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsForms = sprintf("&totalRows_rsForms=%d%s", $totalRows_rsForms, $queryString_rsForms);
?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Available Forms"; $pageTitle." | ".$site_name; echo $pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<style>
<!--
-->
</style>
<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" -->
<div class="container pageBody formbuilder">
  <h1>Available Forms</h1>
  <?php if ($totalRows_rsForms == 0) { // Show if recordset empty ?>
  <p>There are currently no forms available to you. <?php if(!isset($_SESSION['MM_Username']) && $row_rsPreferences['userscanlogin']==1) { ?>You may need to <a href="/login/index.php?accesscheck=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>">log in</a>.<?php } ?></p>
  <?php } // Show if recordset empty ?>
  <?php if ($totalRows_rsForms > 0) { // Show if recordset not empty ?>
  <p>Forms <?php echo ($startRow_rsForms + 1) ?> to <?php echo min($startRow_rsForms + $maxRows_rsForms, $totalRows_rsForms) ?> of <?php echo $totalRows_rsForms ?> </p>
          <ul>
            <?php do { ?><li>
             <a href="form.php?formID=<?php echo $row_rsForms['ID']; ?>" ><?php echo $row_rsForms['formname']; ?></a></li>
              
              <?php } while ($row_rsForms = mysql_fetch_assoc($rsForms)); ?>
         </ul>
          <?php } // Show if recordset not empty ?>
<table class="form-table">
          <tr>
            <td><?php if ($pageNum_rsForms > 0) { // Show if not first page ?>
                <a href="<?php printf("%s?pageNum_rsForms=%d%s", $currentPage, 0, $queryString_rsForms); ?>">First</a>
                <?php } // Show if not first page ?></td>
            <td><?php if ($pageNum_rsForms > 0) { // Show if not first page ?>
                <a href="<?php printf("%s?pageNum_rsForms=%d%s", $currentPage, max(0, $pageNum_rsForms - 1), $queryString_rsForms); ?>">Previous</a>
                <?php } // Show if not first page ?></td>
            <td><?php if ($pageNum_rsForms < $totalPages_rsForms) { // Show if not last page ?>
                <a href="<?php printf("%s?pageNum_rsForms=%d%s", $currentPage, min($totalPages_rsForms, $pageNum_rsForms + 1), $queryString_rsForms); ?>">Next</a>
                <?php } // Show if not last page ?></td>
            <td><?php if ($pageNum_rsForms < $totalPages_rsForms) { // Show if not last page ?>
                <a href="<?php printf("%s?pageNum_rsForms=%d%s", $currentPage, $totalPages_rsForms, $queryString_rsForms); ?>">Last</a>
                <?php } // Show if not last page ?></td>
          </tr>
        </table></div>
        <!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsForms);

mysql_free_result($rsPreferences);
?>
