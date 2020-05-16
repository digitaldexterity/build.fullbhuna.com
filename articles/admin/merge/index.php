<?php require_once('../../../Connections/aquiescedb.php'); ?>
<?php require_once('../../../core/includes/adminAccess.inc.php'); ?><?php require_once('../../includes/functions.inc.php'); ?>
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

if(isset($_GET['createdefaults'])) { 
createDefaultMerges();
}

if(isset($_GET['deletemergeID'])) {
	mysql_select_db($database_aquiescedb, $aquiescedb);
$delete ="DELETE FROM merge WHERE ID = " .intval($_GET['deletemergeID']);
mysql_query($delete, $aquiescedb) or die(mysql_error());

	
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

$maxRows_rsMerge = 100;
$pageNum_rsMerge = 0;
if (isset($_GET['pageNum_rsMerge'])) {
  $pageNum_rsMerge = $_GET['pageNum_rsMerge'];
}
$startRow_rsMerge = $pageNum_rsMerge * $maxRows_rsMerge;

$varRegionID_rsMerge = "0";
if (isset($regionID)) {
  $varRegionID_rsMerge = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsMerge = sprintf("SELECT * FROM merge WHERE regionID = 0 OR regionID = %s OR %s = 0 ORDER BY mergename ASC", GetSQLValueString($varRegionID_rsMerge, "int"),GetSQLValueString($varRegionID_rsMerge, "int"));
$query_limit_rsMerge = sprintf("%s LIMIT %d, %d", $query_rsMerge, $startRow_rsMerge, $maxRows_rsMerge);
$rsMerge = mysql_query($query_limit_rsMerge, $aquiescedb) or die(mysql_error());
$row_rsMerge = mysql_fetch_assoc($rsMerge);

if (isset($_GET['totalRows_rsMerge'])) {
  $totalRows_rsMerge = $_GET['totalRows_rsMerge'];
} else {
  $all_rsMerge = mysql_query($query_rsMerge);
  $totalRows_rsMerge = mysql_num_rows($all_rsMerge);
}
$totalPages_rsMerge = ceil($totalRows_rsMerge/$maxRows_rsMerge)-1;

$queryString_rsMerge = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsMerge") == false && 
        stristr($param, "totalRows_rsMerge") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsMerge = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsMerge = sprintf("&totalRows_rsMerge=%d%s", $totalRows_rsMerge, $queryString_rsMerge);

?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Manage Add-ins"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<style>
<!--

-->
</style>
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
    <div class="page articles">
      <?php require_once('../../../core/region/includes/chooseregion.inc.php'); ?>
<h1><i class="glyphicon glyphicon-file"></i> Manage Add-ins</h1>
      <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
        <li><a href="add_merge.php" ><i class="glyphicon glyphicon-plus-sign"></i> Create Add-in</a></li>
        <li><a href="index.php?createdefaults=true" ><i class="glyphicon glyphicon-plus-sign"></i> Create defaults</a></li>
         <li><a href="../index.php" class="link_undo"><i class="glyphicon glyphicon-arrow-left"></i> Manage Pages</a></li>
      </ul></div></nav>
      <?php if ($totalRows_rsMerge == 0) { // Show if recordset empty ?>
  <p>There are no merge fields. Add one above.</p>
  <?php } // Show if recordset empty ?>
<?php if ($totalRows_rsMerge > 0) { // Show if recordset not empty ?>
  <p class="text-muted">Add-ins <?php echo ($startRow_rsMerge + 1) ?> to <?php echo min($startRow_rsMerge + $maxRows_rsMerge, $totalRows_rsMerge) ?> of <?php echo $totalRows_rsMerge ?> </p>
        <table class="table table-hover">
        <thead>
          <tr>
            <th></th>
            <th>Add-in merge text</th>
            <th class="region">&nbsp;</th>
            
            <th>Actions</th>
          </tr></thead><tbody>
          <?php do { ?>
            <tr>
              <td class="status<?php echo $row_rsMerge['statusID']>0 ? 1 : 0; ?>">&nbsp;</td>
              <td><?php echo $row_rsMerge['mergename']; ?></td>
              <td class="region"><?php echo $row_rsMerge['regionID']==0 ? "(all sites)" : "&nbsp;"; ?></td>
              
              <td><a href="update_merge.php?mergeID=<?php echo $row_rsMerge['ID']; ?>" class="btn btn-default btn-secondary"><i class="glyphicon glyphicon-pencil"></i> Edit</a> <a href="index.php?deletemergeID=<?php echo $row_rsMerge['ID']; ?>" class="btn btn-default btn-secondary" onClick="return confirm('Are you sure you want to delete this Add-in?')"><i class="glyphicon glyphicon-trash"></i> Delete</a></td>
            </tr>
            <?php } while ($row_rsMerge = mysql_fetch_assoc($rsMerge)); ?></tbody>
        </table>
        <?php } // Show if recordset not empty ?>

<table class="form-table">
  <tr>
    <td><?php if ($pageNum_rsMerge > 0) { // Show if not first page ?>
        <a href="<?php printf("%s?pageNum_rsMerge=%d%s", $currentPage, 0, $queryString_rsMerge); ?>">First</a>
        <?php } // Show if not first page ?></td>
    <td><?php if ($pageNum_rsMerge > 0) { // Show if not first page ?>
        <a href="<?php printf("%s?pageNum_rsMerge=%d%s", $currentPage, max(0, $pageNum_rsMerge - 1), $queryString_rsMerge); ?>">Previous</a>
        <?php } // Show if not first page ?></td>
    <td><?php if ($pageNum_rsMerge < $totalPages_rsMerge) { // Show if not last page ?>
        <a href="<?php printf("%s?pageNum_rsMerge=%d%s", $currentPage, min($totalPages_rsMerge, $pageNum_rsMerge + 1), $queryString_rsMerge); ?>">Next</a>
        <?php } // Show if not last page ?></td>
    <td><?php if ($pageNum_rsMerge < $totalPages_rsMerge) { // Show if not last page ?>
        <a href="<?php printf("%s?pageNum_rsMerge=%d%s", $currentPage, $totalPages_rsMerge, $queryString_rsMerge); ?>">Last</a>
        <?php } // Show if not last page ?></td>
  </tr>
</table>

    </div>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsMerge);
?>
