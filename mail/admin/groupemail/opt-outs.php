<?php require_once('../../../Connections/aquiescedb.php'); ?><?php require_once('../../../core/includes/adminAccess.inc.php'); ?>
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

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE mailprefs SET text_unsubscribe=%s WHERE ID=%s",
                       GetSQLValueString($_POST['text_unsubscribe'], "text"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
}

$maxRows_rsOptOuts = 50;
$pageNum_rsOptOuts = 0;
if (isset($_GET['pageNum_rsOptOuts'])) {
  $pageNum_rsOptOuts = $_GET['pageNum_rsOptOuts'];
}
$startRow_rsOptOuts = $pageNum_rsOptOuts * $maxRows_rsOptOuts;

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsOptOuts = "SELECT groupemailoptoutlog.ID, groupemailoptoutlog.email, groupemailoptoutlog.createdbyID, groupemailoptoutlog.createddatetime, users.firstname, users.surname, removedby.firstname AS fn, removedby.surname AS sn FROM groupemailoptoutlog LEFT JOIN users ON (groupemailoptoutlog.email = users.email) LEFT JOIN users AS removedby ON (groupemailoptoutlog.createdbyID = removedby.ID) ORDER BY groupemailoptoutlog.createddatetime DESC";
$query_limit_rsOptOuts = sprintf("%s LIMIT %d, %d", $query_rsOptOuts, $startRow_rsOptOuts, $maxRows_rsOptOuts);
$rsOptOuts = mysql_query($query_limit_rsOptOuts, $aquiescedb) or die(mysql_error());
$row_rsOptOuts = mysql_fetch_assoc($rsOptOuts);

if (isset($_GET['totalRows_rsOptOuts'])) {
  $totalRows_rsOptOuts = $_GET['totalRows_rsOptOuts'];
} else {
  $all_rsOptOuts = mysql_query($query_rsOptOuts);
  $totalRows_rsOptOuts = mysql_num_rows($all_rsOptOuts);
}
$totalPages_rsOptOuts = ceil($totalRows_rsOptOuts/$maxRows_rsOptOuts)-1;

$varRegionID_rsMailPrefs = "1";
if (isset($regionID)) {
  $varRegionID_rsMailPrefs = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsMailPrefs = sprintf("SELECT * FROM mailprefs WHERE ID = %s", GetSQLValueString($varRegionID_rsMailPrefs, "int"));
$rsMailPrefs = mysql_query($query_rsMailPrefs, $aquiescedb) or die(mysql_error());
$row_rsMailPrefs = mysql_fetch_assoc($rsMailPrefs);
$totalRows_rsMailPrefs = mysql_num_rows($rsMailPrefs);

$queryString_rsOptOuts = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsOptOuts") == false && 
        stristr($param, "totalRows_rsOptOuts") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsOptOuts = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsOptOuts = sprintf("&totalRows_rsOptOuts=%d%s", $totalRows_rsOptOuts, $queryString_rsOptOuts);
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Opt-out Report"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<link href="../../css/mailDefault.css" rel="stylesheet" type="text/css" />
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
        <div class="page mail">
    <h1><i class="glyphicon glyphicon-envelope"></i> Opt Out Report</h1>
    
    <form name="form1" method="POST" action="<?php echo $editFormAction; ?>">
    <div class="form-group">
      <p>Unsubscribe text:<label for="text_unsubscribe"></label></p>
      <textarea name="text_unsubscribe" id="text_unsubscribe" class="form-control"><?php echo $row_rsMailPrefs['text_unsubscribe']; ?></textarea></div><button  type="submit" class="btn btn-primary">Save</button><input name="ID" type="hidden" value="<?php echo $row_rsMailPrefs['ID']; ?>">
      <input type="hidden" name="MM_update" value="form1">
    </form>
    <p>&nbsp;</p>
    <p>The following users have chosen to opt-out of receiving emails (after previously being opted in).</p>
    <?php if ($totalRows_rsOptOuts == 0) { // Show if recordset empty ?>
  <p>No users have opted out so far.</p>
  <?php } // Show if recordset empty ?>
    <?php if ($totalRows_rsOptOuts > 0) { // Show if recordset not empty ?>
      <p>Opt outs <?php echo ($startRow_rsOptOuts + 1) ?> to <?php echo min($startRow_rsOptOuts + $maxRows_rsOptOuts, $totalRows_rsOptOuts) ?> of <?php echo $totalRows_rsOptOuts ?></p>
      <table border="0" cellpadding="0" cellspacing="0" class="listTable">
        <tr>
          <th>Date</th>
          <th>User</th>
          <th>email</th>
          <th>Removed by</th>
        </tr>
        <?php do { ?>
          <tr>
            <td><?php echo date('d M Y H:i', strtotime($row_rsOptOuts['createddatetime'])); ?></td>
            <td><?php echo $row_rsOptOuts['firstname']; ?> <?php echo $row_rsOptOuts['surname']; ?></td>
            <td><?php echo $row_rsOptOuts['email']; ?></td>
            <td><?php echo $row_rsOptOuts['fn']; ?> <?php echo $row_rsOptOuts['sn']; ?></td>
          </tr>
          <?php } while ($row_rsOptOuts = mysql_fetch_assoc($rsOptOuts)); ?>
      </table>
      <?php } // Show if recordset not empty ?>
<table class="form-table">
      <tr>
        <td><?php if ($pageNum_rsOptOuts > 0) { // Show if not first page ?>
            <a href="<?php printf("%s?pageNum_rsOptOuts=%d%s", $currentPage, 0, $queryString_rsOptOuts); ?>">First</a>
            <?php } // Show if not first page ?></td>
        <td><?php if ($pageNum_rsOptOuts > 0) { // Show if not first page ?>
            <a href="<?php printf("%s?pageNum_rsOptOuts=%d%s", $currentPage, max(0, $pageNum_rsOptOuts - 1), $queryString_rsOptOuts); ?>">Previous</a>
            <?php } // Show if not first page ?></td>
        <td><?php if ($pageNum_rsOptOuts < $totalPages_rsOptOuts) { // Show if not last page ?>
            <a href="<?php printf("%s?pageNum_rsOptOuts=%d%s", $currentPage, min($totalPages_rsOptOuts, $pageNum_rsOptOuts + 1), $queryString_rsOptOuts); ?>">Next</a>
            <?php } // Show if not last page ?></td>
        <td><?php if ($pageNum_rsOptOuts < $totalPages_rsOptOuts) { // Show if not last page ?>
            <a href="<?php printf("%s?pageNum_rsOptOuts=%d%s", $currentPage, $totalPages_rsOptOuts, $queryString_rsOptOuts); ?>">Last</a>
            <?php } // Show if not last page ?></td>
      </tr>
    </table></div>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsOptOuts);

mysql_free_result($rsMailPrefs);
?>
