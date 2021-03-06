<?php require_once('../../../Connections/aquiescedb.php'); ?>
<?php 
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "9,10";
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
?><?php

if(isset($_POST['merge'])) {
	require_once('includes/mergeLocations.inc.php');
	$submit_error = mergeLocations($_POST['merge'], @$_POST['keep']);
} // end post


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

$maxRows_rsLocation = (strlen(@$_REQUEST['search']) > 3) ? 1000 : 20;
$pageNum_rsLocation = 0;
if (isset($_GET['pageNum_rsLocation'])) {
  $pageNum_rsLocation = $_GET['pageNum_rsLocation'];
}
$startRow_rsLocation = $pageNum_rsLocation * $maxRows_rsLocation;

$varSearch_rsLocation = "-1";
if (isset($_REQUEST['search'])) {
  $varSearch_rsLocation = $_REQUEST['search'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLocation = sprintf("SELECT location.ID, location.locationname, region.title, locationcategory.categoryname AS category, location.address1, location.postcode FROM location LEFT JOIN locationcategory ON (location.categoryID = locationcategory.ID) LEFT JOIN region ON (locationcategory.regionID = region.ID) WHERE location.locationname LIKE %s  OR location.address1 LIKE %s ", GetSQLValueString("%" . $varSearch_rsLocation . "%", "text"),GetSQLValueString($varSearch_rsLocation . "%", "text"));
$query_limit_rsLocation = sprintf("%s LIMIT %d, %d", $query_rsLocation, $startRow_rsLocation, $maxRows_rsLocation);
$rsLocation = mysql_query($query_limit_rsLocation, $aquiescedb) or die(mysql_error());
$row_rsLocation = mysql_fetch_assoc($rsLocation);

if (isset($_GET['totalRows_rsLocation'])) {
  $totalRows_rsLocation = $_GET['totalRows_rsLocation'];
} else {
  $all_rsLocation = mysql_query($query_rsLocation);
  $totalRows_rsLocation = mysql_num_rows($all_rsLocation);
}
$totalPages_rsLocation = ceil($totalRows_rsLocation/$maxRows_rsLocation)-1;
 
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Merge Locations"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script src="/SpryAssets/SpryValidationTextField.js"></script>
<link href="/SpryAssets/SpryValidationTextField.css" rel="stylesheet"  /><script src="/core/scripts/formUpload.js"></script><script>
var fb_keepAlive = true;
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
        <div class="page location">
    <h1><i class="glyphicon glyphicon-flag"></i> Merge Locations</h1>
    <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
      <li><a href="../index.php" class="link_undo"><i class="glyphicon glyphicon-arrow-left"></i> Manage Locations</a></li>
      
    </ul></div></nav>
<p>You can merge duplicate location entries into one.<br />
  <strong>IMPORTANT: It is recommended that you <a href="/admin/backup/index.php">back up</a> your data beforehand.</strong></p>

    <h2>Manual Merge</h2>
   <form action="index.php" method="get" id="searchform">
     <span id="sprytextfield1">
     <input name="search" type="text"  id="search" size="30" maxlength="30" />
</span>
     <input type="submit" value="Go" />
   </form> 
   <?php if(isset($submit_error)) { ?><p class="alert alert-danger" role="alert"><?php echo $submit_error; ?></p><?php } ?>
   <?php if(isset($_GET['msg'])) { ?><p class="alert alert-danger" role="alert"><?php echo htmlentities($_GET['msg']); ?></p><?php } ?>
 
    <form action="index.php" method="post" name="form1" id="form1"  <?php if ($totalRows_rsLocation == 0) { echo "style=\"display:none;\""; } ?> >
     <p>Select the locations you wish to merge using the checkboxes left and the location you wish you keep (i.e. merge to) using the radio buttons right.</p>
  <p class="text-muted">Matching locations <?php echo ($startRow_rsLocation + 1) ?> to <?php echo min($startRow_rsLocation + $maxRows_rsLocation, $totalRows_rsLocation) ?> of <?php echo $totalRows_rsLocation ?> </p>
        
        <table  class="table table-hover">
        <thead>
          <tr>
            <th>Merge</th>
            <th>Location</th><th>Postcode</th>
            <th>Category</th>
            
            <th>Site</th> 
            <th>Keep</th>
          </tr></thead><tbody>
          <?php do { ?>
            <tr>
              <td><input type="checkbox" name="merge[<?php echo $row_rsLocation['ID']; ?>]" id="merge[<?php echo $row_rsLocation['ID']; ?>]" <?php if(isset($_POST['merge'][$row_rsLocation['ID']])) { echo "checked = \"checked\""; } ?> /></td>
              <td class="text-nowrap"><?php echo isset($row_rsLocation['locationname']) ? $row_rsLocation['locationname']." " : ""; ?><?php echo $row_rsLocation['address1']; ?></td><td><?php echo $row_rsLocation['postcode']; ?></td>
              <td><em><?php echo $row_rsLocation['category']; ?></em></td>
              
              <td><?php echo $row_rsLocation['title']; ?></td><td>
               
                <input type="radio" name="keep" id="keep[<?php echo $row_rsLocation['ID']; ?>]" value="<?php echo $row_rsLocation['ID']; ?>" <?php if(@$_POST['keep'] == $row_rsLocation['ID']) { echo "checked = \"checked\""; } ?> /></td>
            </tr>
            <?php } while ($row_rsLocation = mysql_fetch_assoc($rsLocation)); ?></tbody>
        </table><div><button class="btn btn-primary" type="submit" name="submit2" id="submit2" onClick="return confirm('Are you sure you want to merge these locations?\n\nThis action cannot be undone.');">Merge entries...</button><div id="uploading2" style="visibility:hidden;"><a href="javascript:void(0);" onClick="stopSubmit(); return false;">Processing. Please wait...</a></div></div>
        
      <input name="search" type="hidden" id="search" value="<?php echo htmlentities($_REQUEST['search']); ?>" />
    </form>
    <script>
<!--
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1", "none", {hint:"Search by address...", isRequired:false});
//-->
    </script></div>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLocation);
?>
