<?php require_once('../../../Connections/aquiescedb.php'); ?><?php require_once('../../includes/adminAccess.inc.php'); ?>
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

$MM_restrictGoTo = "../../../login/index.php?notloggedin=true";
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
?><?php
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

$userregionID = ($_SESSION['MM_UserGroup'] >=9) ? 0 : $regionID;

$varRegionID_rsRegions = "0";
if (isset($userregionID)) {
  $varRegionID_rsRegions = $userregionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsRegions = sprintf("SELECT region.ID, region.title, region.currencycode,languagecode, COUNT(countries.ID) AS numCountries, region.statusID, region.hostdomain FROM region LEFT JOIN countries ON (region.ID = countries.regionID) WHERE region.ID = %s OR %s = 0 GROUP BY region.ID", GetSQLValueString($varRegionID_rsRegions, "int"),GetSQLValueString($varRegionID_rsRegions, "int"));
$rsRegions = mysql_query($query_rsRegions, $aquiescedb) or die(mysql_error());
$row_rsRegions = mysql_fetch_assoc($rsRegions);
$totalRows_rsRegions = mysql_num_rows($rsRegions);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = "SELECT ID, usertypeID, regionID FROM users";
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);
?>
<?php if ($totalRows_rsRegions == 1) { // only one site - so for simplicity for single-site folks go to that
header("location: update_region.php?regionID=".$row_rsRegions['ID']);
	   exit;
} ?><!doctype html>

<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Manage Sites"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../seo/includes/seo.inc.php'); ?>
<?php require_once('../../includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<style><!--
<?php if($_SESSION['MM_UserGroup'] <9) {
	echo ".rank9 { display: none; }"; 
} ?>
--></style>
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" -->   <div class="page regions">
      <h1><i class="glyphicon glyphicon-globe"></i> Manage Multiple Sites</h1>
      <p>You can add and manage multiple sites, for example if you sell products in several regions of the world.</p>
    <?php if ($totalRows_rsRegions == 0) { // Show if recordset empty ?>
        <p>There are no site versions in the database</p>
        <?php } // Show if recordset empty ?>
        <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
          <li class="nav-item rank9"><a href="add_region.php" class="nav-link"><i class="glyphicon glyphicon-plus-sign"></i> Add a site</a></li>
          <li class="nav-item countries rank9"><a href="countries/index.php" class="nav-link"><i class="glyphicon glyphicon-cog"></i> Manage Countries</a></li>
        </ul></div></nav>
     
        </p>
<?php if ($totalRows_rsRegions > 0) { // Show if recordset not empty ?>
        <table  class="table table-hover">
        <thead>
          <tr>
            <th>&nbsp;</th><th>ID</th>
            <th>Site</th>
           <th>Domain</th>
            <th class="countries">Currency</th>
             <th class="countries">Language</th>
           
            <th>Edit</th>
          </tr></thead><tbody>
          <?php do { ?>
            <tr>
              <td class="status<?php echo $row_rsRegions['statusID']; ?>">&nbsp;</td>
               <td><?php echo $row_rsRegions['ID']; ?></td>
              <td><?php echo $row_rsRegions['title']; ?></td>
            <td><?php echo $row_rsRegions['hostdomain']; ?></td>
              <td class="countries"><?php echo $row_rsRegions['currencycode']; ?></td>
               <td class="countries"><?php echo $row_rsRegions['languagecode']; ?></td>
             
              <td><a href="update_region.php?regionID=<?php echo $row_rsRegions['ID']; ?>" class="link_edit icon_only">Edit</a></td>
          </tr>
            <?php } while ($row_rsRegions = mysql_fetch_assoc($rsRegions)); ?></tbody>
      </table>
        <span class="countries"><?php echo $row_rsRegions['numCountries']; ?></span>
<?php } // Show if recordset not empty ?>
       </div>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsRegions);

mysql_free_result($rsLoggedIn);
?>