<?php require_once('../../../Connections/aquiescedb.php'); ?><?php require_once('../../../core/includes/adminAccess.inc.php'); ?><?php
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

$varRegionID_rsCategories = "0";
if (isset($_GET['regionID'])) {
  $varRegionID_rsCategories = $_GET['regionID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsCategories = sprintf("SELECT directorycategory.ID, directorycategory.description, directorycategory.statusID, parentcategory.description AS parent, region.title AS region FROM directorycategory LEFT JOIN directorycategory AS parentcategory ON (directorycategory.subcatofID = parentcategory.ID) LEFT JOIN region ON (directorycategory.regionID = region.ID) WHERE (directorycategory.regionID = %s OR %s = 0 OR directorycategory.regionID = 0) ORDER BY directorycategory.description", GetSQLValueString($varRegionID_rsCategories, "int"),GetSQLValueString($varRegionID_rsCategories, "int"));
$rsCategories = mysql_query($query_rsCategories, $aquiescedb) or die(mysql_error());
$row_rsCategories = mysql_fetch_assoc($rsCategories);
$totalRows_rsCategories = mysql_num_rows($rsCategories);

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = (get_magic_quotes_gpc()) ? $_SESSION['MM_Username'] : addslashes($_SESSION['MM_Username']);
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID FROM users WHERE username = '%s'", $colname_rsLoggedIn);
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = "SELECT useregions FROM preferences";
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsRegions = "SELECT ID, title FROM region WHERE statusID = 1 ORDER BY title ASC";
$rsRegions = mysql_query($query_rsRegions, $aquiescedb) or die(mysql_error());
$row_rsRegions = mysql_fetch_assoc($rsRegions);
$totalRows_rsRegions = mysql_num_rows($rsRegions);
?><!doctype html>

<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Directory"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<?php if ($row_rsPreferences['useregions'] !=1) { ?>
<style>
.region { display:none; } 
</style>
<?php } ?>
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
<div class="page directory">
      <h1><i class="glyphicon glyphicon-book"></i> Directory Management</h1>
  <h2>Categories</h2>
     
        <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
          <li><a href="add_category.php" ><i class="glyphicon glyphicon-plus-sign"></i> Add a category</a></li><li><a href="../index.php" class="link_undo"><i class="glyphicon glyphicon-arrow-left"></i> Back to directory</a></li>
    </ul></div></nav><div class="region">
    <form action="index.php" method="get">
      <label>Filter by:
      <select name="regionID" id="regionID" onChange="this.form.submit();">
        <option value="0" <?php if (!(strcmp(0, @$_GET['regionID']))) {echo "selected=\"selected\"";} ?>>All sites</option>
        <?php
do {  
?>
        <option value="<?php echo $row_rsRegions['ID']?>"<?php if (!(strcmp($row_rsRegions['ID'], @$_GET['regionID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsRegions['title']?></option>
        <?php
} while ($row_rsRegions = mysql_fetch_assoc($rsRegions));
  $rows = mysql_num_rows($rsRegions);
  if($rows > 0) {
      mysql_data_seek($rsRegions, 0);
	  $row_rsRegions = mysql_fetch_assoc($rsRegions);
  }
?>
      </select> 
      </label>
    </form></div>
         <?php if ($totalRows_rsCategories == 0) { // Show if recordset empty ?>
        <p>There are no categories in the database </p>
        <?php } // Show if recordset empty ?><?php if ($totalRows_rsCategories > 0) { // Show if recordset not empty ?>
        <table class="table table-hover">
        <thead>
         <tr>
                <th>&nbsp;</th>
                <th>Category</th>
           <th>Parent</th>
           <th class="region">Site</th>
           <th>&nbsp;</th>
         </tr></thead><tbody>
             <?php do { ?><tr>
              
              <td><?php if ($row_rsCategories['statusID'] == 1) { ?><img src="../../../core/images/icons/green-light.png" alt="Active" width="16" height="16" style="vertical-align:
middle;" /><?php } else if ($row_rsCategories['statusID'] == 0) { ?><img src="../../../core/images/icons/amber-light.png" alt="Pending approval" width="16" height="16" style="vertical-align:
middle;" /><?php } else { ?><img src="../../../core/images/icons/red-light.png" alt="Inactive" width="16" height="16" style="vertical-align:
middle;" /><?php } ?>&nbsp;&nbsp;</td>
              <td><a href="update_category.php?categoryID=<?php echo $row_rsCategories['ID']; ?>"><?php echo $row_rsCategories['description']; ?></a></td>
              <td><?php echo isset($row_rsCategories['parent']) ? $row_rsCategories['parent'] : "&nbsp;"; ?></td>
              <td class="region"><?php echo isset($row_rsCategories['region']) ? $row_rsCategories['region'] : "All"; ?></td>
              <td class="region"><a href="update_category.php?categoryID=<?php echo $row_rsCategories['ID']; ?>" class="link_edit icon_only">Edit</a></td>
             </tr>
            <?php } while ($row_rsCategories = mysql_fetch_assoc($rsCategories)); ?></tbody>
</table>
<?php } // Show if recordset not empty ?>
        </div>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsCategories);

mysql_free_result($rsLoggedIn);

mysql_free_result($rsPreferences);

mysql_free_result($rsRegions);
?>
