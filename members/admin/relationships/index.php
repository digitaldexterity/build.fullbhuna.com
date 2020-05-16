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

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

$maxRows_rsUserRelationships = 10;
$pageNum_rsUserRelationships = 0;
if (isset($_GET['pageNum_rsUserRelationships'])) {
  $pageNum_rsUserRelationships = $_GET['pageNum_rsUserRelationships'];
}
$startRow_rsUserRelationships = $pageNum_rsUserRelationships * $maxRows_rsUserRelationships;

$varRegionID_rsUserRelationships = "1";
if (isset($regionID)) {
  $varRegionID_rsUserRelationships = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsUserRelationships = sprintf("SELECT userrelationshiptype.ID, userrelationshiptype.relationshiptype, userrelationshiptype.statusID, usertype.name, userrelationshiptype.accessID FROM userrelationshiptype LEFT JOIN usertype ON (userrelationshiptype.accessID = usertype.ID) WHERE regionID = %s ORDER BY relationshiptype ASC", GetSQLValueString($varRegionID_rsUserRelationships, "int"));
$query_limit_rsUserRelationships = sprintf("%s LIMIT %d, %d", $query_rsUserRelationships, $startRow_rsUserRelationships, $maxRows_rsUserRelationships);
$rsUserRelationships = mysql_query($query_limit_rsUserRelationships, $aquiescedb) or die(mysql_error());
$row_rsUserRelationships = mysql_fetch_assoc($rsUserRelationships);

if (isset($_GET['totalRows_rsUserRelationships'])) {
  $totalRows_rsUserRelationships = $_GET['totalRows_rsUserRelationships'];
} else {
  $all_rsUserRelationships = mysql_query($query_rsUserRelationships);
  $totalRows_rsUserRelationships = mysql_num_rows($all_rsUserRelationships);
}
$totalPages_rsUserRelationships = ceil($totalRows_rsUserRelationships/$maxRows_rsUserRelationships)-1;
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Manage User Relationships"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
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
        <?php require_once('../../../core/region/includes/chooseregion.inc.php'); ?>
<h1><i class="glyphicon glyphicon-user"></i> Manage User Relationships</h1>
<nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
  <li class="nav-item"><a href="add_relationship_type.php" class="nav-link"><i class="glyphicon glyphicon-plus-sign"></i> Add</a></li>
  <li class="nav-item"><a href="../index.php" class="nav-link"><i class="glyphicon glyphicon-arrow-left"></i> Back to users</a></li>
</ul></div></nav>
        <p class="text-muted">Relationships 1 to <?php echo min($startRow_rsUserRelationships + $maxRows_rsUserRelationships, $totalRows_rsUserRelationships)+1 ?> of <?php echo $totalRows_rsUserRelationships+1 ?> </p>
        <table class="table table-hover">
        <thead>
          <tr>
            <th>&nbsp;</th>
          
            <th>Relationship type</th>
            <th>Can create</th>  
            <th>Edit</th>
          </tr></thead><tbody> <tr>
            <td class="status1">&nbsp;</td>
            <td>Linked</td>
            <td><em>Everyone</em></td>
            <td>(Default)</td></tr>
          <?php if ($totalRows_rsUserRelationships > 0) { // Show if recordset not empty ?> <?php do { ?>
            <tr>
             
  <td class="status<?php echo $row_rsUserRelationships['statusID']; ?>">&nbsp;</td>
                
                <td><?php echo $row_rsUserRelationships['relationshiptype']; ?></td>
                <td><em><?php echo ($row_rsUserRelationships['accessID']==0) ? "Everyone" : $row_rsUserRelationships['name']; ?></em></td>
                <td><a href="update_relationship_type.php?relationshiptypeID=<?php echo $row_rsUserRelationships['ID']; ?>" class="link_edit icon_only">Edit</a></td>
                
            </tr>
            <?php } while ($row_rsUserRelationships = mysql_fetch_assoc($rsUserRelationships)); ?><?php } // Show if recordset not empty ?></tbody>
        </table>
        <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsUserRelationships);
?>
