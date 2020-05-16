<?php require_once('../../../Connections/aquiescedb.php'); ?>
<?php require_once('../../includes/adminAccess.inc.php'); ?>
<?php

$regionID = isset($regionID) ? intval($regionID ): 1;


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



$varUsername_rsFavourites = "-1";
if (isset($_SESSION['MM_Username'])) {
  $varUsername_rsFavourites = $_SESSION['MM_Username'];
}
$varRegionID_rsFavourites = "0";
if (isset($regionID)) {
  $varRegionID_rsFavourites = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsFavourites = sprintf("SELECT favourites.*, createdby.firstname, createdby.surname, region.title FROM favourites LEFT JOIN users ON (favourites.userID = users.ID) LEFT JOIN users AS createdby ON (favourites.createdbyID = createdby.ID) LEFT JOIN region ON (favourites.regionID= region.ID) WHERE (favourites.userID = 0 OR users.username = %s) AND (favourites.regionID=0 OR favourites.regionID=%s)", GetSQLValueString($varUsername_rsFavourites, "text"),GetSQLValueString($varRegionID_rsFavourites, "int"));
$rsFavourites = mysql_query($query_rsFavourites, $aquiescedb) or die(mysql_error());
$row_rsFavourites = mysql_fetch_assoc($rsFavourites);
$totalRows_rsFavourites = mysql_num_rows($rsFavourites);

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID, users.usertypeID, users.firstname, users.surname, users.changepassword, users.lastlogin FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Manage Favourites"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../seo/includes/seo.inc.php'); ?>
<?php require_once('../../includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
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
            <div class="page favourites"><?php require_once('../../region/includes/chooseregion.inc.php'); ?>
    <h1><i class="glyphicon glyphicon-heart"></i> Manage Favourites</h1>
    <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
      <li><a href="add_favourite.php" ><i class="glyphicon glyphicon-plus-sign"></i> Add Favourite</a></li>
    </ul></div></nav>
    <?php if ($totalRows_rsFavourites == 0) { // Show if recordset empty ?>
  <p>No favourites currently added. Add one above.</p>
  <?php } // Show if recordset empty ?>
<?php if ($totalRows_rsFavourites > 0) { // Show if recordset not empty ?>
  <table class="table table-hover">
  <thead>
    <tr>
      <th>&nbsp;</th>
      <th>Page name</th>
      <th>Added by</th>
      <th>All Users</th>
      <th>New Window</th>
      <th class="region">Site</th>
      <th>Edit</th>
    </tr></thead><tbody>
    <?php do { ?>
      <tr>
        <td class="status<?php echo $row_rsFavourites['statusID']; ?>">&nbsp;</td>
        <td><a href="<?php echo $row_rsFavourites['../../../admin/favourites/url']; ?>" target="_blank" rel="noopener"><?php echo $row_rsFavourites['pagetitle']; ?></a></td>
        <td><?php echo $row_rsFavourites['firstname']; ?> <?php echo $row_rsFavourites['surname']; ?></td>
        <td class="tick<?php echo ($row_rsFavourites['userID']==0) ? 1 : 0; ?>">&nbsp;</td>
        <td class="tick<?php echo $row_rsFavourites['newwindow']; ?>">&nbsp;</td>
        <td  class="region"><em><?php echo $row_rsFavourites['regionID']==0 ? "All sites" : $row_rsFavourites['title']; ?></em></td>
        <td><?php if($row_rsFavourites['userID']!=0 || $row_rsFavourites['createdbyID']==$row_rsLoggedIn['ID']) { ?><a href="update_favourite.php?favouriteID=<?php echo $row_rsFavourites['ID']; ?>" class="link_edit icon_only">Edit</a><?php } ?></td>
      </tr>
      <?php } while ($row_rsFavourites = mysql_fetch_assoc($rsFavourites)); ?></tbody>
  </table>
  <?php } // Show if recordset not empty ?></div>
      <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsFavourites);

mysql_free_result($rsLoggedIn);
?>