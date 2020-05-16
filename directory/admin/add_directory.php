<?php require_once('../../Connections/aquiescedb.php'); ?>
<?php require_once('../../core/includes/adminAccess.inc.php'); ?>
<?php require_once('../includes/directoryfunctions.inc.php'); ?><?php require_once('../../mail/includes/sendmail.inc.php'); ?>
<?php require_once('../../core/includes/framework.inc.php'); ?><?php require_once('../../members/includes/userfunctions.inc.php'); ?>
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

$MM_restrictGoTo = "../../login/index.php?notloggedin=true";
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
$currentPage = $_SERVER["PHP_SELF"];

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


if(isset($_POST['subcategoryID']) && $_POST['subcategoryID'] > 0) { $_POST['categoryID'] = $_POST['subcategoryID']; }

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
  $directoryID = createDirectoryEntry($_POST['categoryID'], $_POST['name'], $_POST['description'],$_POST['address1'], $_POST['address2'], $_POST['address3'], $_POST['address4'], $_POST['address5'], $_POST['postcode'], $_POST['telephone'], $_POST['fax'],"","", "", "", "", "", "", "", "", $_POST['statusID'], $_POST['locationcategoryID'], $_POST['createdbyID'], $_POST['companynumber'],"",@$_POST['isparent'], @$_POST['parentID'], "", @$_POST['directorytype']);
  
  // add contact
  mysql_select_db($database_aquiescedb, $aquiescedb);
  $regionID = isset($regionID) ? $regionID : 1;
  $select = "SELECT ID, firstname, surname FROM users WHERE usertypeID >=0 AND email = ".GetSQLValueString($_POST['email'], "text")." AND firstname LIKE ".GetSQLValueString($_POST['firstname'], "text")." AND surname LIKE ".GetSQLValueString($_POST['surname'], "text")." AND (regionID = 0 OR regionID = ".$regionID.")";
  $result = mysql_query($select, $aquiescedb) or die(mysql_error());
  if(mysql_num_rows($result)>1) { // user exists
	$row = mysql_fetch_assoc($result);
	$userID = $row['ID'];
	addUserToDirectory($userID, $directoryID, $_POST['createdbyID']);
  } else { // create person as non-user
  	$userID = createNewUser($_POST['firstname'],$_POST['surname'],$_POST['email'],-1,0,$directoryID,$_POST['createdbyID'],"",false,"","","","","", "", "",0,$_POST['jobtitle'],1,1,"",$_POST['telephone1']);
  }
  
  if(isset($_POST['createlogin']) && isset($userID) && $userID>0) {
	  setUsernamePassword($userID, "", "", true);
  }
  
  if(isset($_POST['directorfirstname']) && $_POST['directorfirstname'] !="") {
	  createNewUser($_POST['directorfirstname'],$_POST['directorsurname'],"",-1,0,$directoryID,$_POST['createdbyID'],"",false,"","","","","", "", "",0,"Director");
  }
  
  if(isset($_POST['directoryarea'])) {
foreach($_POST['directoryarea'] as $value) {
	$insert = "INSERT INTO directoryinarea (directoryID, directoryareaID, createdbyID, createddatetime) VALUES (". $directoryID.",".$value.",".GetSQLValueString($_POST['createdbyID'], "int").",NOW())";
	$result = mysql_query($insert, $aquiescedb) or die(mysql_error());
	
}
	}

	$insertGoTo = isset($_GET['returnURL']) ? $_GET['returnURL'] : "update_directory.php?defaultTab=3";
	 $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
	$insertGoTo .= "directoryID=".$directoryID; 
   if (isset($_SERVER['QUERY_STRING'])) {
   
    $insertGoTo .= "&".$_SERVER['QUERY_STRING'];
	removeQueryVarFromURL($insertGoTo,"returnURL");
  }
  header(sprintf("Location: %s", $insertGoTo)); exit;
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

$maxRows_rsDirectory = 20;
$pageNum_rsDirectory = 0;
if (isset($_GET['pageNum_rsDirectory'])) {
  $pageNum_rsDirectory = $_GET['pageNum_rsDirectory'];
}
$startRow_rsDirectory = $pageNum_rsDirectory * $maxRows_rsDirectory;

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsDirectory = "SELECT directory.ID, directory.name, directory.statusID, directorycategory.description, directory.categoryID FROM directory LEFT JOIN directorycategory ON (directory.categoryID = directorycategory.ID) ORDER BY ID DESC";
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

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsParentCategory = "SELECT ID, description FROM directorycategory WHERE directorycategory.statusID = 1 AND directorycategory.subCatOfID = 0 ORDER BY description ASC";
$rsParentCategory = mysql_query($query_rsParentCategory, $aquiescedb) or die(mysql_error());
$row_rsParentCategory = mysql_fetch_assoc($rsParentCategory);
$totalRows_rsParentCategory = mysql_num_rows($rsParentCategory);

$varRegionID_rsLocationCategory = "0";
if (isset($regionID)) {
  $varRegionID_rsLocationCategory = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLocationCategory = sprintf("SELECT locationcategory.ID, locationcategory.categoryname FROM locationcategory WHERE locationcategory.statusID = 1 AND (locationcategory.regionID = %s OR %s = 0)", GetSQLValueString($varRegionID_rsLocationCategory, "int"),GetSQLValueString($varRegionID_rsLocationCategory, "int"));
$rsLocationCategory = mysql_query($query_rsLocationCategory, $aquiescedb) or die(mysql_error());
$row_rsLocationCategory = mysql_fetch_assoc($rsLocationCategory);
$totalRows_rsLocationCategory = mysql_num_rows($rsLocationCategory);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsRegions = "SELECT region.ID, region.title FROM region WHERE region.statusID = 1 ";
$rsRegions = mysql_query($query_rsRegions, $aquiescedb) or die(mysql_error());
$row_rsRegions = mysql_fetch_assoc($rsRegions);
$totalRows_rsRegions = mysql_num_rows($rsRegions);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = "SELECT * FROM preferences";
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsDirectoryPrefs = "SELECT * FROM directoryprefs";
$rsDirectoryPrefs = mysql_query($query_rsDirectoryPrefs, $aquiescedb) or die(mysql_error());
$row_rsDirectoryPrefs = mysql_fetch_assoc($rsDirectoryPrefs);
$totalRows_rsDirectoryPrefs = mysql_num_rows($rsDirectoryPrefs);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsParents = "SELECT ID, name FROM directory WHERE statusID = 1 AND directory.isparent = 1 ORDER BY name ASC";
$rsParents = mysql_query($query_rsParents, $aquiescedb) or die(mysql_error());
$row_rsParents = mysql_fetch_assoc($rsParents);
$totalRows_rsParents = mysql_num_rows($rsParents);

$varDirectoryID_rsDirectoryAreas = "-1";
if (isset($_GET['directoryID'])) {
  $varDirectoryID_rsDirectoryAreas = $_GET['directoryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsDirectoryAreas = sprintf("SELECT directoryarea.ID, directoryarea.areaname, directoryinarea.directoryareaID FROM directoryarea LEFT JOIN directoryinarea ON (directoryarea.ID = directoryinarea.directoryareaID AND directoryinarea.directoryID = %s) WHERE directoryarea.statusID ORDER BY directoryarea.areaname", GetSQLValueString($varDirectoryID_rsDirectoryAreas, "int"));
$rsDirectoryAreas = mysql_query($query_rsDirectoryAreas, $aquiescedb) or die(mysql_error());
$row_rsDirectoryAreas = mysql_fetch_assoc($rsDirectoryAreas);
$totalRows_rsDirectoryAreas = mysql_num_rows($rsDirectoryAreas);


$queryString_rsDirectory = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsDirectory") == false && 
        stristr($param, "totalRows_rsDirectory") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsDirectory = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsDirectory = sprintf("&totalRows_rsDirectory=%d%s", $totalRows_rsDirectory, $queryString_rsDirectory);
?><!doctype html>

<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Add Directory Entry"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<style><!--
<?php if (!isset($allowlocalwebpage) || $allowlocalwebpage == false) {  echo ".localweb { display:none; } ";
 } ?>
 <?php if ($totalRows_rsParents==0) {  echo ".hasparent { display:none; } ";
 } ?>

 -->
 </style>
<?php if ($totalRows_rsLocationCategory < 1 || (isset($_GET['hidecategory']) && $_GET['hidecategory']==1 )) { // not using location categories so hide
echo "<style>.locationCategory {display: none;} </style>" ; }
if ($row_rsPreferences['useregions'] != 1) { // not using regions so hide
echo "<style>.region {display: none;} </style>"; }
?>
<script src="../../core/scripts/formUpload.js"></script>
 <script src="../../SpryAssets/SpryValidationTextField.js"></script>
 <script>
 function init() {
getData('/directory/ajax/createSubCatSelect.php?parentCatID='+document.getElementById('categoryID').value,'subCat');
 }
 
 addListener("load",init);
      </script>

<script src="../../SpryAssets/SpryValidationRadio.js"></script><link href="../../SpryAssets/SpryValidationRadio.css" rel="stylesheet" ><link href="../../SpryAssets/SpryValidationTextField.css" rel="stylesheet"  />
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" -->
<div class="page directory">
  <h1><i class="glyphicon glyphicon-book"></i> Add a <?php echo $row_rsDirectoryPrefs['directoryname']; ?> Entry</h1>
  <?php if ($totalRows_rsParentCategory == 0) { // Show if recordset empty ?>
    <p>Before you can add an organisation, there needs to be at least one category to add it to.</p>
    <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
      <li><a href="category/index.php" class="link_manage"><i class="glyphicon glyphicon-cog"></i> Manage Categories</a>  </li>
      </ul></div></nav>
    <?php } // Show if recordset empty ?>
  
  <?php if ($totalRows_rsParentCategory > 0) { // Show if recordset not empty ?>
    <?php if(isset($submit_error)) { ?><p class="alert alert-danger" role="alert"><?php echo $submit_error; ?></p><?php } ?>
    <br /><form action="<?php echo $editFormAction; ?>" method="post" enctype="multipart/form-data" name="form1" id="form1">
   <fieldset>
     <legend>Organisation</legend>   
      <table class="form-table">
        <tr  class="region">
          <td class="text-nowrap text-right">Site:</td>
          <td><select name="regionID" id="regionID"  onchange="getData('/directory/ajax/createParentCats.php?regionID='+this.value+'','parentCat'); getData('/directory/ajax/createSubCatSelect.php?parentCatID='+this.value+'','subCat')" class="form-control">
            <option value="0"><?php echo isset($region['text_choose']) ? htmlentities($region['text_choose'], ENT_COMPAT, "UTF-8") : "Choose..." ?></option>
            <option value="0">All</option>
            <?php
do {  
?>
            <option value="<?php echo $row_rsRegions['ID']?>"><?php echo $row_rsRegions['title']?></option>
            <?php
} while ($row_rsRegions = mysql_fetch_assoc($rsRegions));
  $rows = mysql_num_rows($rsRegions);
  if($rows > 0) {
      mysql_data_seek($rsRegions, 0);
	  $row_rsRegions = mysql_fetch_assoc($rsRegions);
  }
?>
            </select></td>
          </tr><tr id="rowCategory">
            <td class="text-nowrap text-right">Directory Category:</td>
            <td id="parentCat"><select name="categoryID" id="categoryID" onChange="getData('/directory/ajax/createSubCatSelect.php?parentCatID='+this.value+'','subCat')" class="form-control">
              <option value="" <?php if (!(strcmp("", @$_REQUEST['categoryID']))) {echo "selected=\"selected\"";} ?>><?php echo isset($region['text_choose']) ? htmlentities($region['text_choose'], ENT_COMPAT, "UTF-8") : "Choose..." ?></option>
              <?php
do {  
?>
              <option value="<?php echo $row_rsParentCategory['ID']?>"<?php if (!(strcmp($row_rsParentCategory['ID'], @$_REQUEST['categoryID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsParentCategory['description']?></option>
              <?php
} while ($row_rsParentCategory = mysql_fetch_assoc($rsParentCategory));
  $rows = mysql_num_rows($rsParentCategory);
  if($rows > 0) {
      mysql_data_seek($rsParentCategory, 0);
	  $row_rsParentCategory = mysql_fetch_assoc($rsParentCategory);
  }
?> </select></td>
            </tr>
        <tr id="rowSubCat">
          <td class="text-nowrap text-right">Sub category:</td>
          <td id="subCat"><select name="subcategoryID" id="subcategoryID" class="form-control">
            <option value="0">None</option>
            </select></td>
          </tr>
        <tr class="locationCategory">
          <td class="text-nowrap text-right">Location Category:</td>
          <td><select name="locationcategoryID" id="locationcategoryID" class="form-control">
            <option value="0"><?php echo isset($region['text_choose']) ? htmlentities($region['text_choose'], ENT_COMPAT, "UTF-8") : "Choose..." ?></option>
            <?php
do {  
?>
            <option value="<?php echo $row_rsLocationCategory['ID']?>"><?php echo $row_rsLocationCategory['categoryname']?></option>
            <?php
} while ($row_rsLocationCategory = mysql_fetch_assoc($rsLocationCategory));
  $rows = mysql_num_rows($rsLocationCategory);
  if($rows > 0) {
      mysql_data_seek($rsLocationCategory, 0);
	  $row_rsLocationCategory = mysql_fetch_assoc($rsLocationCategory);
  }
?>
            </select></td>
        </tr>
        <tr class="directorytypes">
		        <td class="text-nowrap text-right top">
		          
	            Type:</td>
		        <td><span id="spryradio1"><label>
		          <input  type="radio" name="directorytype" value="1" id="directorytype_1" />&nbsp;
		          Ltd</label> &nbsp;&nbsp;&nbsp;
		          <label>
		            <input  type="radio" name="directorytype" value="2" id="directorytype_2" />&nbsp;
		            LLP</label> &nbsp;&nbsp;&nbsp;
		          <label>
		            <input  type="radio" name="directorytype" value="3" id="directorytype_3" />&nbsp;
		            Sole trader</label> &nbsp;&nbsp;&nbsp;
		          <label>
		            <input type="radio" name="directorytype" value="4" id="directorytype_4" />&nbsp;
		            Charity</label> &nbsp;&nbsp;&nbsp;
		          <label>
		            <input  type="radio" name="directorytype" value="5" id="directorytype_5" />&nbsp;
		            Private </label> &nbsp;&nbsp;&nbsp;
		          <label>
		            <input type="radio" name="directorytype" value="6" id="directorytype_6" />&nbsp;
		            Social Enterprise</label>
                     &nbsp;&nbsp;&nbsp; <label>
		            <input type="radio" name="directorytype" value="7" id="directorytype_7" />&nbsp;
		            Public Sector</label>  &nbsp;&nbsp;&nbsp; <input type="radio" name="directorytype" value="0" id="directorytype_0" />&nbsp;
		            Other</label><span class="radioRequiredMsg">Please make a selection.</span></span></td>
	          </tr>
        <?php if($totalRows_rsDirectoryAreas >0) { ?>
        <tr class="directoryareas">
          <td class="text-nowrap text-right top">Areas covered:</td>
          <td><?php do { ?>
            <label>
              <input name="directoryarea[<?php echo $row_rsDirectoryAreas['ID']; ?>]" type="checkbox" value="<?php echo $row_rsDirectoryAreas['ID']; ?>" />
              <?php echo $row_rsDirectoryAreas['areaname']; ?></label>
            &nbsp;&nbsp;&nbsp;
            <?php } while ($row_rsDirectoryAreas = mysql_fetch_assoc($rsDirectoryAreas)); ?></td>
        </tr>
        <?php } ?> <tr>
          <td class="text-nowrap text-right">Organisation name:</td>
          <td><span id="sprytextfield1">
            <input name="name" type="text" class="form-control"  value="<?php echo isset($_POST['name']) ? $_POST['name'] : ""; ?>" size="50" maxlength="255" />
            <span class="textfieldRequiredMsg">A value is required.</span></span></td>
        </tr> <tr>
          <td class="text-nowrap text-right top"> Main address:</td>
          <td><input name="address1" type="text" class="form-control"  id="address1" value="<?php echo isset($_POST['address1']) ? $_POST['address1'] : ""; ?>" size="50" maxlength="50" /></td>
          </tr> <tr>
          <td class="text-nowrap text-right top">&nbsp;</td>
          <td><input name="address2" type="text" class="form-control"  id="address2" value="<?php echo isset($_POST['address2']) ? $_POST['address2'] : ""; ?>" size="50" maxlength="50" /></td>
          </tr> <tr>
          <td class="text-nowrap text-right top">&nbsp;</td>
          <td><input name="address3" type="text" class="form-control"  id="address3" value="<?php echo isset($_POST['address3']) ? $_POST['address3'] : ""; ?>" size="50" maxlength="50" /></td>
          </tr> <tr>
          <td class="text-nowrap text-right top">&nbsp;</td>
          <td><input name="address4" type="text" class="form-control"  id="address4" value="<?php echo isset($_POST['address4']) ? $_POST['address4']: ""; ?>" size="50" maxlength="50" /></td>
          </tr> <tr>
          <td class="text-nowrap text-right top">&nbsp;</td>
          <td><input name="address5" type="text" class="form-control"  id="address5" value="<?php echo isset($_POST['address5']) ? $_POST['address5'] : ""; ?>" size="50" maxlength="50" /></td>
          </tr> <tr>
          <td class="text-nowrap text-right">Postcode:</td>
          <td><input name="postcode" type="text" class="form-control"  id="postcode"  value="<?php echo isset($_POST['postcode']) ? $_POST['postcode'] : ""; ?>" size="10" maxlength="10" /></td>
          </tr> <tr>
          <td class="text-nowrap text-right">Telephone:</td>
          <td><input name="telephone" type="text" class="form-control"  id="telephone" value="<?php echo isset($_POST['telephone']) ? $_POST['telephone'] : ""; ?>" size="50" maxlength="50" /></td>
          </tr> <tr>
          <td class="text-nowrap text-right">Fax:</td>
          <td><input name="fax" type="text" class="form-control"  id="fax" value="<?php echo isset($_POST['fax']) ? $_POST['fax'] : ""; ?>" size="50" maxlength="50" /></td>
        </tr> <tr>
          <td class="text-nowrap text-right">Company number:</td>
          <td><input name="companynumber" type="text" class="form-control" id="companynumber" value="<?php echo isset($_POST['companynumber']) ? $_POST['companynumber'] : ""; ?>" size="50" maxlength="50"/></td>
        </tr>
        <tr class="row_parent">
          <td class="text-nowrap text-right">Parent:</td>
          <td><label>
            <input type="checkbox" name="isparent" id="isparent" />
            is parent.</label>&nbsp;&nbsp;&nbsp;<label class="hasparent">Has parent:
            <select name="parentID" id="parentID">
              <option value="">No parent</option>
              <?php
do {  
?>
              <option value="<?php echo $row_rsParents['ID']?>"><?php echo $row_rsParents['name']?></option>
              <?php
} while ($row_rsParents = mysql_fetch_assoc($rsParents));
  $rows = mysql_num_rows($rsParents);
  if($rows > 0) {
      mysql_data_seek($rsParents, 0);
	  $row_rsParents = mysql_fetch_assoc($rsParents);
  }
?>
            </select>
          </label></td>
        </tr> <tr>
          <td class="text-nowrap text-right top">Description:</td>
          <td><textarea name="description" cols="50" rows="5"  class="form-control"><?php echo isset($_POST['description']) ? $_POST['description'] : ""; ?></textarea></td>
          </tr></table></fieldset><br /><fieldset>
            <legend>Contacts (optional)</legend><table class="form-table"> <tr>
          <td class="text-nowrap text-right">Main contact:</td>
          <td class="form-inline">
            <input name="firstname" type="text" class="form-control" id="firstname"value="<?php echo isset($_POST['sprytextfield2']) ? $_POST['sprytextfield2'] : ""; ?>" size="20" maxlength="50" placeholder="First name"  />

<input name="surname" type="text" class="form-control" id="surname" value="<?php echo isset($_POST['surname']) ? $_POST['surname'] : ""; ?>" size="20" maxlength="50" placeholder="Surname" />
</td>
        </tr> <tr>
          <td class="text-nowrap text-right"><?php echo isset($row_rsPreferences['text_role']) ? $row_rsPreferences['text_role'] : "Job Title"; ?>:</td>
          <td><input name="jobtitle" type="text" class="form-control" id="jobtitle" value="<?php echo isset($_POST['jobtitle']) ? $_POST['jobtitle'] : ""; ?>" size="50" maxlength="50" /></td>
        </tr> <tr>
          <td class="text-nowrap text-right">Telephone:</td>
          <td><input name="telephone1" type="text" class="form-control"  id="telephone1" value="<?php echo isset($_POST['telephone1']) ? $_POST['telephone1'] : ""; ?>" size="50" maxlength="50" /></td>
        </tr> <tr>
          <td class="text-nowrap text-right">email:</td>
          <td class="form-inline"><input name="email" type="email" id="email" multiple size="50" maxlength="100" value="<?php echo isset($_POST['email']) ? $_POST['email'] : ""; ?>" class="form-control" /> <label><input name="createlogin" type="checkbox" value="1">Create a log in for this user</label></td>
        </tr> <tr>
          <td class="text-nowrap text-right">&nbsp;</td>
          <td>&nbsp;</td>
        </tr> <tr>
          <td class="text-nowrap text-right">Director:</td>
          <td class="form-inline">
            <input name="directorfirstname" type="text" class="form-control" id="directorfirstname"value="<?php echo isset($_POST['directorfirstname']) ? $_POST['directorfirstname'] : ""; ?>" size="20" maxlength="50"  placeholder="First Name" />

<input name="directorsurname" type="text" id="directorsurname" size="20" maxlength="50"value="<?php echo isset($_POST['directorsurname']) ? $_POST['directorsurname'] : ""; ?>" class="form-control" placeholder="Surname"  />
</td>
        </tr> <tr>
          <td class="text-nowrap text-right">&nbsp;</td>
          <td><button type="submit" class="btn btn-primary" >Submit</button></td>
        </tr>
        </table>
      
      
      <input type="hidden" name="statusID" value="1" />
      <input type="hidden" name="createdbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
      
      <input type="hidden" name="MM_insert" value="form1" />
      </fieldset>
      </form>
    <?php } // Show if recordset not empty ?>
</div>
<script>
<!--
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1");


//-->
    </script><script>
<!--
var spryradio1 = new Spry.Widget.ValidationRadio("spryradio1");
//-->
  </script>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsDirectory);

mysql_free_result($rsParentCategory);

mysql_free_result($rsLocationCategory);

mysql_free_result($rsRegions);

mysql_free_result($rsPreferences);

mysql_free_result($rsDirectoryPrefs);

mysql_free_result($rsParents);
?>
