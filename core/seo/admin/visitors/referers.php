<?php require_once('../../../../Connections/aquiescedb.php'); ?>
<?php require_once('../../../includes/adminAccess.inc.php'); ?>
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

$maxRows_rsReferers = 500;
$pageNum_rsReferers = 0;
if (isset($_GET['pageNum_rsReferers'])) {
  $pageNum_rsReferers = $_GET['pageNum_rsReferers'];
}
$startRow_rsReferers = $pageNum_rsReferers * $maxRows_rsReferers;

$varSearch_rsReferers = "%";
if (isset($_GET['referer'])) {
  $varSearch_rsReferers = $_GET['referer'];
}
$varRegionID_rsReferers = "1";
if (isset($regionID)) {
  $varRegionID_rsReferers = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsReferers = sprintf("SELECT track_session.ID,  remote_address, referer, regionID, track_page.pageTitle, track_session.`datetime` FROM track_session LEFT JOIN track_page ON (track_session.entrypageID = track_page.ID) WHERE regionID = %s AND referer LIKE %s ORDER BY track_session.`datetime`  DESC", GetSQLValueString($varRegionID_rsReferers, "int"),GetSQLValueString("%" . $varSearch_rsReferers . "%", "text"));
$query_limit_rsReferers = sprintf("%s LIMIT %d, %d", $query_rsReferers, $startRow_rsReferers, $maxRows_rsReferers);
$rsReferers = mysql_query($query_limit_rsReferers, $aquiescedb) or die(mysql_error());
$row_rsReferers = mysql_fetch_assoc($rsReferers);

if (isset($_GET['totalRows_rsReferers'])) {
  $totalRows_rsReferers = $_GET['totalRows_rsReferers'];
} else {
  $all_rsReferers = mysql_query($query_rsReferers);
  $totalRows_rsReferers = mysql_num_rows($all_rsReferers);
}
$totalPages_rsReferers = ceil($totalRows_rsReferers/$maxRows_rsReferers)-1;
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Referers"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
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
   <div class="page seo">
    <?php require_once('../../../region/includes/chooseregion.inc.php'); ?>
      <h1><i class="glyphicon glyphicon-globe"></i> Referers</h1> <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
      <li class="nav-item"><a href="index.php" class="nav-link"><i class="glyphicon glyphicon-arrow-left"></i> Back to Vistors</a></li>
    </ul></div></nav>
      <table class="table table-hover">
      <thead>
        <tr>
         <th>Date</th>
          <th>Referer Page</th> <th>Entry Page</th>
          <th>IP Address</th>
          <th>View Session</th>
        </tr></thead><tbody>
        <?php do { ?>
          <tr>
            <td class="text-nowrap"><?php echo date('d M Y', strtotime($row_rsReferers['datetime'])); ?></td>
            <td><a href="<?php echo $row_rsReferers['referer']; ?>" target="_blank" rel="noopener"><?php echo $row_rsReferers['referer']; ?></a></td>
             <td><?php echo $row_rsReferers['pageTitle']; ?></td>
            <td><?php echo $row_rsReferers['remote_address']; ?></td>
             <td><a href="visitor-session.php?sessionID=<?php echo urlencode($row_rsReferers['ID']); ?>" class="link_view">View</a></td>
          </tr>
          <?php } while ($row_rsReferers = mysql_fetch_assoc($rsReferers)); ?></tbody>
      </table>
   </div>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsReferers);
?>
