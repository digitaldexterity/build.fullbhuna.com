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

$varRegionID_rslocationcategorys = "1";
if (isset($regionID)) {
  $varRegionID_rslocationcategorys = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rslocationcategorys = sprintf("SELECT locationcategory.*, parentcategory.categoryname AS parentcategoryname FROM locationcategory LEFT JOIN locationcategory AS parentcategory ON (locationcategory.subcatofID = parentcategory.ID) WHERE locationcategory.regionID = 0 OR locationcategory.regionID = %s ORDER BY parentcategory.categoryname, locationcategory.categoryname ASC", GetSQLValueString($varRegionID_rslocationcategorys, "int"));
$rslocationcategorys = mysql_query($query_rslocationcategorys, $aquiescedb) or die(mysql_error());
$row_rslocationcategorys = mysql_fetch_assoc($rslocationcategorys);
$totalRows_rslocationcategorys = mysql_num_rows($rslocationcategorys);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLocationPrefs = "SELECT * FROM locationprefs";
$rsLocationPrefs = mysql_query($query_rsLocationPrefs, $aquiescedb) or die(mysql_error());
$row_rsLocationPrefs = mysql_fetch_assoc($rsLocationPrefs);
$totalRows_rsLocationPrefs = mysql_num_rows($rsLocationPrefs);
?><!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" --><title><?php $pageTitle = "Manage Location Categories"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title><!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" --><!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" -->
        <div class="page location"><?php require_once('../../../core/region/includes/chooseregion.inc.php'); ?>
   <h1><i class="glyphicon glyphicon-flag"></i> Manage Categories</h1>
   <p>You can optionally divide your locations in to areas to allow for customised searches, for example.</p>
   <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
     <li><a href="add.php" ><i class="glyphicon glyphicon-plus-sign"></i> Add a category</a></li>
     <li><a href="../index.php" class="link_undo"><i class="glyphicon glyphicon-arrow-left"></i> Back to <?php echo ucwords($row_rsLocationPrefs['locationdescriptor']); ?>s</a></li>
   </ul></div></nav>
   
   <?php if ($totalRows_rslocationcategorys == 0) { // Show if recordset empty ?>
     <p>There are no areas added so far.</p>
     <?php } // Show if recordset empty ?>
   <?php if ($totalRows_rslocationcategorys > 0) { // Show if recordset not empty ?>
  <table class="table table-hover">
    <tbody>
    <?php do { ?>
      <tr>
        <td><?php if ($row_rslocationcategorys['statusID'] == 1) { ?><img src="../../../core/images/icons/green-light.png" alt="Active" width="16" height="16" style="vertical-align:
middle;" />
          <?php } else { ?><img src="../../../core/images/icons/red-light.png" alt="Inactive" width="16" height="16" style="vertical-align:
middle;" />          <?php } ?></td>
        <td><?php echo isset($row_rslocationcategorys['parentcategoryname']) ? $row_rslocationcategorys['parentcategoryname']." > " : ""; ?><?php echo $row_rslocationcategorys['categoryname']; ?><?php echo ($row_rslocationcategorys['regionID ']==0 )?  "<span class=\"region\">*</span>" : ""; ?></td>
        <td><a href="update.php?categoryID=<?php echo $row_rslocationcategorys['ID']; ?>" class="link_edit icon_only">Edit</a></td>
      </tr>
      <?php } while ($row_rslocationcategorys = mysql_fetch_assoc($rslocationcategorys)); ?></tbody>
  </table>
  <?php } // Show if recordset not empty ?></div>
  <p class="region">* All sites</p>
<!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rslocationcategorys);

mysql_free_result($rsLocationPrefs);
?>


