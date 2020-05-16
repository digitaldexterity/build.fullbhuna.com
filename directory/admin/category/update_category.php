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

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE directorycategory SET subcatofID=%s, `description`=%s, modifiedbyID=%s, modifieddatetime=%s, statusID=%s, regionID=%s WHERE ID=%s",
                       GetSQLValueString($_POST['subCatOf'], "int"),
                       GetSQLValueString($_POST['description'], "text"),
                       GetSQLValueString($_POST['modifiedbyID'], "int"),
                       GetSQLValueString($_POST['modifieddatetime'], "date"),
                       GetSQLValueString($_POST['statusID'], "int"),
                       GetSQLValueString($_POST['regionID'], "int"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
}
if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateGoTo = "index.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo));exit;
}

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsStatus = "SELECT * FROM status ORDER BY ID ASC";
$rsStatus = mysql_query($query_rsStatus, $aquiescedb) or die(mysql_error());
$row_rsStatus = mysql_fetch_assoc($rsStatus);
$totalRows_rsStatus = mysql_num_rows($rsStatus);

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = (get_magic_quotes_gpc()) ? $_SESSION['MM_Username'] : addslashes($_SESSION['MM_Username']);
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID FROM users WHERE username = '%s'", $colname_rsLoggedIn);
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

$colname_rsCategory = "-1";
if (isset($_GET['categoryID'])) {
  $colname_rsCategory = $_GET['categoryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsCategory = sprintf("SELECT directorycategory.ID, directorycategory.description, directorycategory.createdbyID, directorycategory.createddatetime, users.firstname, users.surname, directorycategory.statusID, directorycategory.regionID, directorycategory.subcatofID FROM directorycategory LEFT JOIN users ON (directorycategory.createdbyID = users.ID) WHERE directorycategory.ID = %s", GetSQLValueString($colname_rsCategory, "int"));
$rsCategory = mysql_query($query_rsCategory, $aquiescedb) or die(mysql_error());
$row_rsCategory = mysql_fetch_assoc($rsCategory);
$totalRows_rsCategory = mysql_num_rows($rsCategory);

$colname_rsLastModified = "-1";
if (isset($_GET['categoryID'])) {
  $colname_rsLastModified = (get_magic_quotes_gpc()) ? $_GET['categoryID'] : addslashes($_GET['categoryID']);
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLastModified = sprintf("SELECT directorycategory.modifieddatetime, users.firstname, users.surname FROM directorycategory LEFT JOIN users ON (directorycategory.modifiedbyID = users.ID) WHERE directorycategory.ID = '%s'", $colname_rsLastModified);
$rsLastModified = mysql_query($query_rsLastModified, $aquiescedb) or die(mysql_error());
$row_rsLastModified = mysql_fetch_assoc($rsLastModified);
$totalRows_rsLastModified = mysql_num_rows($rsLastModified);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsRegions = "SELECT ID, title FROM region WHERE statusID = 1 ORDER BY title ASC";
$rsRegions = mysql_query($query_rsRegions, $aquiescedb) or die(mysql_error());
$row_rsRegions = mysql_fetch_assoc($rsRegions);
$totalRows_rsRegions = mysql_num_rows($rsRegions);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = "SELECT useregions FROM preferences";
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);

$varThisCat_rsActiveParentCategories = "-1";
if (isset($_GET['categoryID'])) {
  $varThisCat_rsActiveParentCategories = $_GET['categoryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsActiveParentCategories = sprintf("SELECT directorycategory.ID, directorycategory.description, directorycategory.statusID FROM directorycategory WHERE directorycategory.statusID = 1 AND directorycategory.subCatOfID=0 AND directorycategory.ID != %s ORDER BY directorycategory.description", GetSQLValueString($varThisCat_rsActiveParentCategories, "int"));
$rsActiveParentCategories = mysql_query($query_rsActiveParentCategories, $aquiescedb) or die(mysql_error());
$row_rsActiveParentCategories = mysql_fetch_assoc($rsActiveParentCategories);
$totalRows_rsActiveParentCategories = mysql_num_rows($rsActiveParentCategories);
?><!doctype html>

<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Update Directory Category: ".$row_rsCategory['description']; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<?php if ($row_rsPreferences['useregions'] !=1) { ?>
<style>.region { display:none; } </style>
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
      <h1><i class="glyphicon glyphicon-book"></i> Update Directory Category </h1>
      <?php if(isset($submit_error)) { ?><p class="alert alert-danger" role="alert"><?php echo $submit_error; ?></p><?php } ?>
<form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1">
  <table class="form-table"> <tr>
      <td class="text-nowrap text-right">Category name:</td>
      <td><input name="description" type="text"  value="<?php echo $row_rsCategory['description']; ?>"  class="form-control"  /></td>
    </tr>
    <tr>
      <td class="text-nowrap text-right">Parent category:</td>
      <td><label for="subCatOf"></label>
          <select name="subCatOf" id="subCatOf" class="form-control" >
            <option value="0" <?php if (!(strcmp(0, $row_rsCategory['subcatofID']))) {echo "selected=\"selected\"";} ?>>None</option>
            <?php
do {  
?>
<option value="<?php echo $row_rsActiveParentCategories['ID']?>"<?php if (!(strcmp($row_rsActiveParentCategories['ID'], $row_rsCategory['subcatofID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsActiveParentCategories['description']?></option>
            <?php
} while ($row_rsActiveParentCategories = mysql_fetch_assoc($rsActiveParentCategories));
  $rows = mysql_num_rows($rsActiveParentCategories);
  if($rows > 0) {
      mysql_data_seek($rsActiveParentCategories, 0);
	  $row_rsActiveParentCategories = mysql_fetch_assoc($rsActiveParentCategories);
  }
?>
          </select></td>
    </tr> 
    <tr class="region">
              <td class="text-nowrap text-right"><label for="regionID">Show in site:</label></td>
              <td>
                <select name="regionID" id="regionID" class="form-control" >
                  <option value="0" <?php if (!(strcmp(0, $row_rsCategory['regionID']))) {echo "selected=\"selected\"";} ?>>All sites</option>
                  <?php
do {  
?><option value="<?php echo $row_rsRegions['ID']?>"<?php if (!(strcmp($row_rsRegions['ID'], $row_rsCategory['regionID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsRegions['title']?></option>
                  <?php
} while ($row_rsRegions = mysql_fetch_assoc($rsRegions));
  $rows = mysql_num_rows($rsRegions);
  if($rows > 0) {
      mysql_data_seek($rsRegions, 0);
	  $row_rsRegions = mysql_fetch_assoc($rsRegions);
  }
?>
                </select></td>
        </tr> <tr>
      <td class="text-nowrap text-right">Status:</td>
      <td><select name="statusID" class="form-control" >
        <?php
do {  
?><option value="<?php echo $row_rsStatus['ID']?>"<?php if (!(strcmp($row_rsStatus['ID'], $row_rsCategory['statusID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsStatus['description']?></option>
        <?php
} while ($row_rsStatus = mysql_fetch_assoc($rsStatus));
  $rows = mysql_num_rows($rsStatus);
  if($rows > 0) {
      mysql_data_seek($rsStatus, 0);
	  $row_rsStatus = mysql_fetch_assoc($rsStatus);
  }
?>
      </select></td>
    </tr>
    <tr>    </tr> <tr>
      <td class="text-nowrap text-right">&nbsp;</td>
      <td><button type="submit" class="btn btn-primary" >Save changes</button></td>
    </tr>
  </table>
  <input type="hidden" name="ID" value="<?php echo $row_rsCategory['ID']; ?>" />
  <input type="hidden" name="modifiedbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
  <input type="hidden" name="modifieddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
  <input type="hidden" name="MM_update" value="form1" />
  <input type="hidden" name="ID" value="<?php echo $row_rsCategory['ID']; ?>" />
</form>
<p><em>Originally created by <?php echo $row_rsCategory['firstname']; ?> <?php echo $row_rsCategory['surname']; ?> at <?php echo date('g:ia',strtotime( $row_rsCategory['createddatetime'])); ?> on <?php echo date('l jS F Y',strtotime($row_rsCategory['createddatetime'])); ?></em></p>
    <em>
    <?php if(isset($row_rsLastModified['modifieddatetime'])) { ?>
    </em>
    <p><em>Last updated by <?php echo $row_rsLastModified['firstname']; ?> <?php echo $row_rsLastModified['surname']; ?> at <?php echo date('g:ia',strtotime($row_rsLastModified['modifieddatetime'])); ?> on <?php echo date('l jS F Y',strtotime($row_rsLastModified['modifieddatetime'])); ?></em></p>
    <?php } ?></div>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsStatus);

mysql_free_result($rsLoggedIn);

mysql_free_result($rsCategory);

mysql_free_result($rsLastModified);

mysql_free_result($rsRegions);

mysql_free_result($rsPreferences);

mysql_free_result($rsActiveParentCategories);
?>
