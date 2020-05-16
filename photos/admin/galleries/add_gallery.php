<?php require_once('../../../Connections/aquiescedb.php'); ?><?php require_once('../../../core/includes/adminAccess.inc.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "8,9,10";
$MM_donotCheckaccess = "true";

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
    if (($strUsers == "") && true) { 
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
  $insertSQL = sprintf("INSERT INTO photocategories (categoryname, regionID, `description`, categoryofID, accesslevel, groupID, addedbyID, active, createddatetime) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['categoryname'], "text"),
                       GetSQLValueString($_POST['regionID'], "int"),
                       GetSQLValueString($_POST['description'], "text"),
                       GetSQLValueString($_POST['categoryofID'], "int"),
                       GetSQLValueString($_POST['acesslevel'], "int"),
                       GetSQLValueString($_POST['groupID'], "int"),
                       GetSQLValueString($_POST['addedbyID'], "int"),
                       GetSQLValueString($_POST['statusID'], "int"),
                       GetSQLValueString($_POST['createddatetime'], "date"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($insertSQL, $aquiescedb) or die(mysql_error());
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
	$galleryID = mysql_insert_id();
  if(isset($_POST['directoryID']) && intval($_POST['directoryID'])>0) {
	  $insert = "INSERT INTO directorygallery (directoryID, galleryID, createdbyID, createddatetime) VALUES (".GetSQLValueString($_POST['directoryID'], "int").",".$galleryID.",".GetSQLValueString($_POST['addedbyID'], "int").",NOW())";
	  $result = mysql_query($insert, $aquiescedb) or die(mysql_error());
}
$insertGoTo = "index.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
    $insertGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $insertGoTo)); exit;
}

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID, usertypeID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

$varMinAccessLevel_rsAccessLevel = "1";
if (isset($accesslevel)) {
  $varMinAccessLevel_rsAccessLevel = $accesslevel;
}
$varMaxAccessLevel_rsAccessLevel = "0";
if (isset($_SESSION['MM_UserGroup'])) {
  $varMaxAccessLevel_rsAccessLevel = $_SESSION['MM_UserGroup'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsAccessLevel = sprintf("SELECT usertype.ID, CONCAT(usertype.name, 's') AS name FROM usertype WHERE usertype.ID >= 1 AND usertype.ID >= %s AND usertype.ID <= %s ORDER BY usertype.ID ASC", GetSQLValueString($varMinAccessLevel_rsAccessLevel, "int"),GetSQLValueString($varMaxAccessLevel_rsAccessLevel, "int"));
$rsAccessLevel = mysql_query($query_rsAccessLevel, $aquiescedb) or die(mysql_error());
$row_rsAccessLevel = mysql_fetch_assoc($rsAccessLevel);
$totalRows_rsAccessLevel = mysql_num_rows($rsAccessLevel);

$colname_rsThisDirectory = "-1";
if (isset($_GET['directoryID'])) {
  $colname_rsThisDirectory = $_GET['directoryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsThisDirectory = sprintf("SELECT name FROM directory WHERE ID = %s", GetSQLValueString($colname_rsThisDirectory, "int"));
$rsThisDirectory = mysql_query($query_rsThisDirectory, $aquiescedb) or die(mysql_error());
$row_rsThisDirectory = mysql_fetch_assoc($rsThisDirectory);
$totalRows_rsThisDirectory = mysql_num_rows($rsThisDirectory);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsGroups = "SELECT ID, groupname FROM usergroup WHERE statusID = 1 ORDER BY groupname ASC";
$rsGroups = mysql_query($query_rsGroups, $aquiescedb) or die(mysql_error());
$row_rsGroups = mysql_fetch_assoc($rsGroups);
$totalRows_rsGroups = mysql_num_rows($rsGroups);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsRegions = "SELECT ID, title FROM region WHERE statusID = 1 ORDER BY title ASC";
$rsRegions = mysql_query($query_rsRegions, $aquiescedb) or die(mysql_error());
$row_rsRegions = mysql_fetch_assoc($rsRegions);
$totalRows_rsRegions = mysql_num_rows($rsRegions);

$varRegionID_rsGalleries = "1";
if (isset($regionID)) {
  $varRegionID_rsGalleries = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsGalleries = sprintf("SELECT ID, categoryname FROM photocategories WHERE active = 1 AND regionID = %s ORDER BY ordernum ASC", GetSQLValueString($varRegionID_rsGalleries, "int"));
$rsGalleries = mysql_query($query_rsGalleries, $aquiescedb) or die(mysql_error());
$row_rsGalleries = mysql_fetch_assoc($rsGalleries);
$totalRows_rsGalleries = mysql_num_rows($rsGalleries);

$regionID = isset($regionID) ? $regionID : 1;
?>
<!doctype html>
<!-- Web design by Paul Egan, Jim Campbell -->
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Add Picture Album"; echo $pageTitle." | ".$site_name; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script src="/SpryAssets/SpryValidationTextField.js"></script>
<link href="/SpryAssets/SpryValidationTextField.css" rel="stylesheet"  />
<style><!--
<?php if(mysql_num_rows($rsGroups)<1) {
	echo ".groups { display: none; } ";
} ?>
<?php if($row_rsLoggedIn['usertypeID']<9 || $totalRows_rsRegions<2) {
	echo ".region { display: none; } ";
} ?>
--></style>
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
    <div class="container pageBody addphotogallery">
          <h1><i class="glyphicon glyphicon-picture"></i> Add Gallery<?php echo isset($row_rsThisDirectory['name']) ? " for ".$row_rsThisDirectory['name'] : ""; ?></h1>
    <?php if(isset($submit_error)) { ?>
          
    <p class="alert alert-danger" role="alert"><?php echo $submit_error; ?></p><?php } ?>
<form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1">
           
            <table  class="form-table">
              <tr>
                <td align="right" valign="top">Gallery Name: </td>
                <td><span id="sprytextfield1">
                  <input name="categoryname" type="text"  id="categoryname" size="50" maxlength="50" value="<?php echo isset($_REQUEST['categoryname']) ? htmlentities($_REQUEST['categoryname']) : $row_rsThisDirectory['name']; ?>" class="form-control" />
                <span class="textfieldRequiredMsg">A name is required.</span></span></td>
              </tr>
              <tr class="region">
                <td align="right" valign="top"><label for="regionID">Site:</label></td>
                <td>
                  <select name="regionID" id="regionID" class="form-control" >
                    <option value="1" <?php if (!(strcmp(1, "$regionID"))) {echo "selected=\"selected\"";} ?>><?php echo isset($region['text_choose']) ? htmlentities($region['text_choose'], ENT_COMPAT, "UTF-8") : "Choose..." ?></option>
                    <option value="0" <?php if ($regionID==0) {echo "selected=\"selected\"";} ?>>All sites</option>
                    <?php
do {  
?>
                    <option value="<?php echo $row_rsRegions['ID']?>"<?php if ($row_rsRegions['ID']==$regionID) {echo "selected=\"selected\"";} ?>><?php echo $row_rsRegions['title']?></option>
                    <?php
} while ($row_rsRegions = mysql_fetch_assoc($rsRegions));
  $rows = mysql_num_rows($rsRegions);
  if($rows > 0) {
      mysql_data_seek($rsRegions, 0);
	  $row_rsRegions = mysql_fetch_assoc($rsRegions);
  }
?>
                  </select></td>
              </tr>
              <tr>
                <td align="right" valign="top"><label for="categoryofID">Within gallery:</label></td>
                <td><select name="categoryofID" id="categoryofID" class="form-control" >
                  <option value="0">None</option>
                  <?php if($rows > 0) {
      mysql_data_seek($rsGalleries, 0);
do {  
?>
                  <option value="<?php echo $row_rsGalleries['ID']; ?>"><?php echo $row_rsGalleries['categoryname']?></option>
                  <?php
} while ($row_rsGalleries = mysql_fetch_assoc($rsGalleries));
  $rows = mysql_num_rows($rsGalleries);
  
	  $row_rsGalleries = mysql_fetch_assoc($rsGalleries);
  }
?>
                </select></td>
              </tr>
              <tr>
                <td align="right" valign="top">Can be viewed by:</td>
                <td class="form-inline"><select name="acesslevel"  id="acesslevel"  class="form-control" >
                  <option value="0">Everyone</option>
                    <?php
do {  
?>
                    <option value="<?php echo $row_rsAccessLevel['ID']?>"><?php echo $row_rsAccessLevel['name']?></option>
                    <?php
} while ($row_rsAccessLevel = mysql_fetch_assoc($rsAccessLevel));
  $rows = mysql_num_rows($rsAccessLevel);
  if($rows > 0) {
      mysql_data_seek($rsAccessLevel, 0);
	  $row_rsAccessLevel = mysql_fetch_assoc($rsAccessLevel);
  }
?>
                </select>
                <span class="groups"> in
                <select name="groupID" id="groupID" class="form-control" >
                  <option value="0">Any group</option>
                  <?php $rows = mysql_num_rows($rsGroups);if($rows > 0) {
do {  
?>
                  <option value="<?php echo $row_rsGroups['ID']?>"><?php echo $row_rsGroups['groupname']?></option>
                  <?php
} while ($row_rsGroups = mysql_fetch_assoc($rsGroups));
  
  
      mysql_data_seek($rsGroups, 0);
	  $row_rsGroups = mysql_fetch_assoc($rsGroups);
  }
?>
                </select></span></td>
              </tr>
              <tr>
                <td align="right" valign="top">Description:</td>
                <td><textarea name="description" cols="50" rows="5" id="description" class="form-control" ><?php echo isset($_REQUEST['description']) ? htmlentities($_REQUEST['description']) : ""; ?></textarea></td>
              </tr>
              <tr>
                <td align="right" valign="top">Status:</td>
                <td><label>
                  <select name="statusID" id="statusID" class="form-control" >
                    <option value="2">Off</option>
                    <option value="1" selected>On (listed in galleries)</option>
                    <option value="0">On (but not listed in galleries)</option>
                  </select>
                </label></td>
              </tr>
              <tr>
                <td align="right" valign="top"><input type="hidden" name="directoryID" id="directoryID" value="<?php if(isset($_GET['directoryID']) && intval($_GET['directoryID'])>0) { echo intval($_GET['directoryID']); } ?>" />                  <input name="addedbyID" type="hidden" id="addedbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" /></td>
                <td><button name="add" type="submit" class="btn btn-primary" id="add" >Add Gallery</button></td>
              </tr>
      </table>
            
      <input type="hidden" name="MM_insert" value="form1" />
      <input name="referrer" type="hidden" id="referrer" value="<?php echo $_SERVER['HTTP_REFERER']; ?>" />
      <input type="hidden" name="createddatetime" id="createddatetime" value="<?php echo date("Y-m-d H:i:s"); ?>" />
</form></div>
          <script>
<!--
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1");
//-->
          </script>
  <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsAccessLevel);

mysql_free_result($rsThisDirectory);

mysql_free_result($rsGroups);

mysql_free_result($rsRegions);

mysql_free_result($rsGalleries);
?>

