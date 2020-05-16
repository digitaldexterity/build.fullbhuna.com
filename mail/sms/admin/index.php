<?php require_once('../../../Connections/aquiescedb.php'); ?>
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

$MM_restrictGoTo = "/login/index.php?notloggedin=true";
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

$maxRows_rsAccounts = 10;
$pageNum_rsAccounts = 0;
if (isset($_GET['pageNum_rsAccounts'])) {
  $pageNum_rsAccounts = $_GET['pageNum_rsAccounts'];
}
$startRow_rsAccounts = $pageNum_rsAccounts * $maxRows_rsAccounts;

$varRegionID_rsAccounts = "1";
if (isset($regionID)) {
  $varRegionID_rsAccounts = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsAccounts = sprintf("SELECT * FROM smsaccount WHERE regionID = 0 OR regionID = %s ORDER BY accountname ASC", GetSQLValueString($varRegionID_rsAccounts, "int"));
$query_limit_rsAccounts = sprintf("%s LIMIT %d, %d", $query_rsAccounts, $startRow_rsAccounts, $maxRows_rsAccounts);
$rsAccounts = mysql_query($query_limit_rsAccounts, $aquiescedb) or die(mysql_error());
$row_rsAccounts = mysql_fetch_assoc($rsAccounts);

if (isset($_GET['totalRows_rsAccounts'])) {
  $totalRows_rsAccounts = $_GET['totalRows_rsAccounts'];
} else {
  $all_rsAccounts = mysql_query($query_rsAccounts);
  $totalRows_rsAccounts = mysql_num_rows($all_rsAccounts);
}
$totalPages_rsAccounts = ceil($totalRows_rsAccounts/$maxRows_rsAccounts)-1;
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "SMS"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script>
function sendSMS(user,password,api_id,to,text) {
	alert(text);
	
}
</script>
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
 
    <h1>Manage SMS Accounts</h1>
    <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
      <li><a href="add_account.php" ><i class="glyphicon glyphicon-plus-sign"></i> Add Account</a></li>
      <li><a href="providers/index.php.php" class="link_manage"><i class="glyphicon glyphicon-cog"></i> Manage Providers</a></li>
    </ul></div></nav>
    <?php if ($totalRows_rsAccounts == 0) { // Show if recordset empty ?>
  <p>There are no accounts added</p>
  <?php } // Show if recordset empty ?>
<?php if ($totalRows_rsAccounts > 0) { // Show if recordset not empty ?>
      <p class="text-muted">Accounts <?php echo ($startRow_rsAccounts + 1) ?> to <?php echo min($startRow_rsAccounts + $maxRows_rsAccounts, $totalRows_rsAccounts) ?> of <?php echo $totalRows_rsAccounts ?></p>
  <table  class="table table-hover">
  <thead>
    <tr>
      <th>&nbsp;</th>
      <th>Account name</th>
      <th>Edit/Send</th>
      </tr></thead><tbody>
    <?php do { ?>
      <tr>
        <td class="status<?php echo $row_rsAccounts['statusID']; ?>">&nbsp;</td>
        <td><?php echo $row_rsAccounts['accountname']; ?></td>
        <td><a href="update_account.php?accountID=<?php echo $row_rsAccounts['ID']; ?>" class="link_edit icon_only">Edit</a></td>
        </tr>
      <?php } while ($row_rsAccounts = mysql_fetch_assoc($rsAccounts)); ?></tbody>
  </table>
  <?php } // Show if recordset not empty ?>
      <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsAccounts);
?>
