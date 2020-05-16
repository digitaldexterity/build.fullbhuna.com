<?php require_once('../../../Connections/aquiescedb.php'); ?><?php require_once('../../../core/includes/adminAccess.inc.php'); ?>
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

require_once('includes/mergeDirectory.inc.php');

if(isset($_POST['merge'])) {
	
	$submit_error = mergeDirectory($_POST['merge'], @$_POST['keep']);
} // end post

if(isset($_GET['mergeidentical'])) {
	mergeIdenticalDirectory();
	header("location: index.php"); exit;
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

$maxRows_rsDirectory = (strlen(@$_REQUEST['search']) > 3) ? 1000 : 20;
$pageNum_rsDirectory = 0;
if (isset($_GET['pageNum_rsDirectory'])) {
  $pageNum_rsDirectory = $_GET['pageNum_rsDirectory'];
}
$startRow_rsDirectory = $pageNum_rsDirectory * $maxRows_rsDirectory;

$varSearch_rsDirectory = "-1";
if (isset($_REQUEST['search'])) {
  $varSearch_rsDirectory = $_REQUEST['search'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsDirectory = sprintf("SELECT directory.ID, directory.name, region.title, directorycategory.`description` AS category, directory.statusID FROM directory LEFT JOIN directorycategory ON (directory.categoryID = directorycategory.ID) LEFT JOIN region ON (directorycategory.regionID = region.ID) WHERE directory.name LIKE %s ", GetSQLValueString("%" . $varSearch_rsDirectory . "%", "text"));
$query_limit_rsDirectory = sprintf("%s LIMIT %d, %d", $query_rsDirectory, $startRow_rsDirectory, $maxRows_rsDirectory);
$rsDirectory = mysql_query($query_limit_rsDirectory, $aquiescedb) or die(mysql_error());
$row_rsDirectory = mysql_fetch_assoc($rsDirectory);

if (isset($_GET['totalRows_rsDirectory'])) {
  $totalRows_rsDirectory = $_GET['totalRows_rsDirectory'];
} else {
  $all_rsDirectory = mysql_query($query_rsDirectory);
  $totalRows_rsDirectory = mysql_num_rows($all_rsDirectory);
}
$totalPages_rsDirectory = ceil($totalRows_rsDirectory/$maxRows_rsDirectory)-1;
 
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Merge Directory Entries"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
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
        <div class="page directory">
    <h1><i class="glyphicon glyphicon-book"></i> Merge Directory Entries</h1>
    <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
      <li><a href="../index.php" class="link_undo"><i class="glyphicon glyphicon-arrow-left"></i> Manage Directory</a></li>
      <li><a href="index.php?mergeidentical=true" class="link_manage"><i class="glyphicon glyphicon-cog"></i> Merge Identical</a></li>
      
    </ul></div></nav>
<p>You can merge duplicate directory entries into one.<br />
  <strong>IMPORTANT: It is recommended that you <a href="/core/admin/backup/index.php">back up</a> your data beforehand.</strong></p>

    <h2>Manual Merge</h2>
   <form action="index.php" method="get" id="searchform" class="form-inline">
     <span id="sprytextfield1">
     <input name="search" type="text"  id="search" size="30" maxlength="30" class="form-control"  />
</span>
     <button type="submit" class="btn btn-default btn-secondary" >Go</button>
   </form> 
   <?php if(isset($submit_error)) { ?><p class="alert alert-danger" role="alert"><?php echo $submit_error; ?></p><?php } ?>
   <?php if(isset($_GET['msg'])) { ?><p class="alert alert-danger" role="alert"><?php echo htmlentities($_GET['msg'], ENT_COMPAT, "UTF-8"); ?></p><?php } ?>
 
    <form action="index.php" method="post" name="form1" id="form1"  <?php if ($totalRows_rsDirectory == 0) { echo "style=\"display:none;\""; } ?> >
     <p>Select the entries you wish to merge using the checkboxes left and the entry you wish you keep (i.e. merge to) using the radio buttons right.</p>
  <p class="text-muted">Matching entries <?php echo ($startRow_rsDirectory + 1) ?> to <?php echo min($startRow_rsDirectory + $maxRows_rsDirectory, $totalRows_rsDirectory) ?> of <?php echo $totalRows_rsDirectory ?> </p>
        
        <table  class="table table-hover">
        <thead>
          <tr>
            <th colspan="2">Merge</th>
            <th>Entry</th>
            <th>Category</th>
            <th>Site</th> 
            <th>Keep</th>
          </tr></thead><tbody>
          <?php do { ?>
            <tr>
              <td><input type="checkbox" name="merge[<?php echo $row_rsDirectory['ID']; ?>]" id="merge[<?php echo $row_rsDirectory['ID']; ?>]" <?php if(isset($_POST['merge'][$row_rsDirectory['ID']])) { echo "checked = \"checked\""; } ?> /></td>
              <td class="status<?php echo $row_rsDirectory['statusID']; ?>">&nbsp;</td>
              <td class="text-nowrap"><a href="../update_directory.php?directoryID=<?php echo $row_rsDirectory['ID']; ?>"><?php echo $row_rsDirectory['name']; ?></a></td>
              <td><em><?php echo $row_rsDirectory['category']; ?></em></td>
              <td><?php echo $row_rsDirectory['title']; ?></td><td>
                <input type="radio" name="keep" id="keep[<?php echo $row_rsDirectory['ID']; ?>]" value="<?php echo $row_rsDirectory['ID']; ?>" <?php if(@$_POST['keep'] == $row_rsDirectory['ID']) { echo "checked = \"checked\""; } ?> /></td>
            </tr>
            <?php } while ($row_rsDirectory = mysql_fetch_assoc($rsDirectory)); ?></tbody>
        </table><div><input type="submit" name="submit2" id="submit2" value="Merge entries..." onClick="return confirm('Are you sure you want to merge these entries?\n\nThis action cannot be undone.');" /><div id="uploading2" style="visibility:hidden;"><a href="javascript:void(0);" onClick="stopSubmit(); return false;">Processing. Please wait...</a></div></div>
        
      <input name="search" type="hidden" id="search" value="<?php echo isset($_REQUEST['search']) ? htmlentities($_REQUEST['search'], ENT_COMPAT, "UTF-8") : ""; ?>" />
    </form>
    <script>
<!--
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1", "none", {hint:"Search by name...", isRequired:false});
//-->
    </script></div>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsDirectory);
?>
