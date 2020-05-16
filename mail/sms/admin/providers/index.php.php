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

$maxRows_rsProviders = 10;
$pageNum_rsProviders = 0;
if (isset($_GET['pageNum_rsProviders'])) {
  $pageNum_rsProviders = $_GET['pageNum_rsProviders'];
}
$startRow_rsProviders = $pageNum_rsProviders * $maxRows_rsProviders;

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsProviders = "SELECT * FROM smsprovider ORDER BY providername ASC";
$query_limit_rsProviders = sprintf("%s LIMIT %d, %d", $query_rsProviders, $startRow_rsProviders, $maxRows_rsProviders);
$rsProviders = mysql_query($query_limit_rsProviders, $aquiescedb) or die(mysql_error());
$row_rsProviders = mysql_fetch_assoc($rsProviders);

if (isset($_GET['totalRows_rsProviders'])) {
  $totalRows_rsProviders = $_GET['totalRows_rsProviders'];
} else {
  $all_rsProviders = mysql_query($query_rsProviders);
  $totalRows_rsProviders = mysql_num_rows($all_rsProviders);
}
$totalPages_rsProviders = ceil($totalRows_rsProviders/$maxRows_rsProviders)-1;
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
<!-- InstanceBeginEditable name="head" --><style><!--
--></style>
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
      <h1>Manage SMS Providers</h1>
      <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
        <li><a href="add_provider.php" ><i class="glyphicon glyphicon-plus-sign"></i> Add Provider</a></li>
        <li><a href="../index.php" class="link_undo"><i class="glyphicon glyphicon-arrow-left"></i> Accounts</a></li>
      </ul></div></nav>
<?php if ($totalRows_rsProviders == 0) { // Show if recordset empty ?>
  <p>There are no providers added</p>
  <?php } // Show if recordset empty ?>
      
      <?php if ($totalRows_rsProviders > 0) { // Show if recordset not empty ?>
        <p class="text-muted">Providers <?php echo ($startRow_rsProviders + 1) ?> to <?php echo min($startRow_rsProviders + $maxRows_rsProviders, $totalRows_rsProviders) ?> of <?php echo $totalRows_rsProviders ?></p>
  <table class="table table-hover">
  <thead>
    <tr>
      
      <th>Provider name</th><th>Edit</th>
    </tr></thead><tbody>
    <?php do { ?>
      <tr>
        
        <td><?php echo $row_rsProviders['providername']; ?></td><td><a href="update_provider.php?providerID=<?php echo $row_rsProviders['ID']; ?>" class="link_edit icon_only">Edit</a></td>
      </tr>
      <?php } while ($row_rsProviders = mysql_fetch_assoc($rsProviders)); ?></tbody>
  </table>
  <?php } // Show if recordset not empty ?>
      <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsProviders);
?>
