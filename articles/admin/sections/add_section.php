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

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
	//$_POST['longID'] = preg_replace("/[^a-zA-Z0-9_\-]/", "", $_POST['longID']); // clean
	$_POST['regionID'] = (isset($_POST['regionID']) && $_POST['regionID']!="")  ? $_POST['regionID'] : 1;
	// convert if posted from add article
	if(isset($_POST['title'])) {
	$_POST['description'] =  $_POST['title'];
	$_POST['subsectionofID'] = $_POST['sectionID'];
	$_POST['active'] = isset($_POST['status']) ? 1 : 0;
	}
	
	if($_POST['subsectionofID']>0) { // assume access permissions of parent
		$select = "SELECT accesslevel FROM articlesection WHERE ID = ".intval($_POST['subsectionofID']);
		mysql_select_db($database_aquiescedb, $aquiescedb);
  		$result = mysql_query($select, $aquiescedb) or die(mysql_error());
		$row = mysql_fetch_assoc($result);
		$_POST['accesslevel'] = $row['accesslevel'];
		
	}

}


if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
  $insertSQL = sprintf("INSERT INTO articlesection (articleID, subsectionofID, `description`, accesslevel, regionID, createdbyID, createddatetime) VALUES (%s, %s, %s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['articleID'], "int"),
                       GetSQLValueString($_POST['subsectionofID'], "int"),
                       GetSQLValueString($_POST['description'], "text"),
                       GetSQLValueString($_POST['accesslevel'], "int"),
                       GetSQLValueString($_POST['regionID'], "int"),
                       GetSQLValueString($_POST['createdbyID'], "int"),
                       GetSQLValueString($_POST['createddatetime'], "date"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($insertSQL, $aquiescedb) or die(mysql_error());
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
	$sectionID = mysql_insert_id();
	$update = "UPDATE articlesection SET ordernum = ID WHERE ID = ".$sectionID;
	mysql_query($update, $aquiescedb) or die(mysql_error());
  $insertGoTo = "update_section.php?sectionID=".$sectionID;
  
  header(sprintf("Location: %s", $insertGoTo)); exit;
}

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsAccessLevel = "SELECT * FROM usertype";
$rsAccessLevel = mysql_query($query_rsAccessLevel, $aquiescedb) or die(mysql_error());
$row_rsAccessLevel = mysql_fetch_assoc($rsAccessLevel);
$totalRows_rsAccessLevel = mysql_num_rows($rsAccessLevel);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = "SELECT * FROM preferences";
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID, usertypeID,  regionID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsRegions = "SELECT ID, title FROM region WHERE statusID = 1 ORDER BY title ASC";
$rsRegions = mysql_query($query_rsRegions, $aquiescedb) or die(mysql_error());
$row_rsRegions = mysql_fetch_assoc($rsRegions);
$totalRows_rsRegions = mysql_num_rows($rsRegions);

$varRegionID_rsRootSections = "1";
if (isset($regionID)) {
  $varRegionID_rsRootSections = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsRootSections = sprintf("SELECT articlesection.ID, articlesection.`description` FROM articlesection WHERE subsectionofID = 0 AND (articlesection.regionID = %s  OR articlesection.regionID = 0) ", GetSQLValueString($varRegionID_rsRootSections, "int"));
$rsRootSections = mysql_query($query_rsRootSections, $aquiescedb) or die(mysql_error());
$row_rsRootSections = mysql_fetch_assoc($rsRootSections);
$totalRows_rsRootSections = mysql_num_rows($rsRootSections);
?><!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" --><title><?php $pageTitle = "Add Section"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title><!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<?php if ($row_rsPreferences['useregions'] != 1) { //no departments ?><style >.region {display: none;}</style><?php } ?><?php if (!(defined("MOD_REWRITE") || isset($_SERVER['HTTP_MOD_REWRITE']))) { // no mod re-write so hide URL option ?>
<style>.longID { display:none; }</style>
<?php } ?>
<script src="../../../SpryAssets/SpryValidationTextField.js"></script>

<link href="../../../SpryAssets/SpryValidationTextField.css" rel="stylesheet"  />
<link href="../../css/defaultArticles.css" rel="stylesheet" >

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
         <div class="page articles">
   <h1><i class="glyphicon glyphicon-file"></i> Add Section</h1>
   <?php require_once('../../../core/includes/alert.inc.php'); ?><form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1">
  <table class="form-table"> <tr>
      <td class="text-nowrap text-right">New section name:        </td>
      <td><span id="sprytextfield1">
        <input name="description" type="text" value="" size="50" maxlength="255"  onblur="seoPopulate(this.value, this.value);" class="form-control" />
        <span class="textfieldRequiredMsg"><br />
          A value is required.</span></span></td>
      </tr>
    <tr class="region">
      <td class="text-nowrap text-right">Site:</td>
      <td><label>
        <select name="regionID" id="regionID" class="form-control" >
          <option value="1"><?php echo isset($region['text_choose']) ? htmlentities($region['text_choose'], ENT_COMPAT, "UTF-8") : "Choose..." ?></option>
          <option value="0">All sites</option>
          <?php
do {  
?>
          <option value="<?php echo $row_rsRegions['ID']?>"<?php if (!(strcmp($row_rsRegions['ID'], $regionID))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsRegions['title']?></option>
          <?php
} while ($row_rsRegions = mysql_fetch_assoc($rsRegions));
  $rows = mysql_num_rows($rsRegions);
  if($rows > 0) {
      mysql_data_seek($rsRegions, 0);
	  $row_rsRegions = mysql_fetch_assoc($rsRegions);
  }
?>
          </select>
        </label></td>
    </tr> <tr>
      <td class="text-nowrap text-right">Parent section:</td>
      <td><select name="subsectionofID" id="subsectionofID" class="form-control" >
        <option value="0">None</option>
        <?php
do {  
?>
        <option value="<?php echo $row_rsRootSections['ID']?>"><?php echo $row_rsRootSections['description']?></option>
        <?php
} while ($row_rsRootSections = mysql_fetch_assoc($rsRootSections));
  $rows = mysql_num_rows($rsRootSections);
  if($rows > 0) {
      mysql_data_seek($rsRootSections, 0);
	  $row_rsRootSections = mysql_fetch_assoc($rsRootSections);
  }
?>
        </select></td>
      </tr> <tr>
      <td class="text-nowrap text-right"><input name="showlink" type="hidden" id="showlink" value="<?php echo isset($_POST['showlink']) ? htmlentities($_POST['showlink']): 1; ?>">
                <input type="hidden" name="articleID" id="articleID"></td>
      <td><button type="submit" class="btn btn-primary">Add section</button></td>
      </tr>
    </table>
  
  <input type="hidden" name="MM_insert" value="form1" />
  
  
  <input type="hidden" name="createddatetime" id="createddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>">
  <input name="createdbyID" type="hidden" id="createdbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>">
  
  <input name="accesslevel" type="hidden" id="accesslevel" value="0">
</form>
<script>
<!--
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1");
//-->
   </script></div>
   <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsAccessLevel);

mysql_free_result($rsPreferences);

mysql_free_result($rsLoggedIn);

mysql_free_result($rsRegions);

mysql_free_result($rsRootSections);
?>
