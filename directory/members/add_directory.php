<?php require_once('../../Connections/aquiescedb.php'); ?>
<?php require_once('../includes/directoryfunctions.inc.php'); ?>
<?php require_once('../../location/includes/mapit.inc.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "1,2,3,4,5,6,7,8,9,10";
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


$MM_restrictGoTo = "../../login/signup.php";
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

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = "SELECT * FROM preferences";
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);


$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if (isset($_POST["MM_insert"]) && $_POST["MM_insert"] == "form1") {
	$coords = getDataFromPostCode($_POST['postcode']);
	$submit_error = "";
	$_POST['url'] = (trim($_POST['url']) == "http://") ? "" : trim($_POST['url']);
	if($_POST['url']!="" && substr($_POST['url'],0,7) !="http://")  { 
					  unset($_POST["MM_insert"]);
					  $submit_error .= "Invalid web site URL. Must start with http://<br />";					  
					  }
}



if(isset($_POST['subcategoryID']) && $_POST['subcategoryID'] > 0) { $_POST['categoryID'] = $_POST['subcategoryID']; }

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
	$public = isset($_POST['public']) ? 1 : 0;
  $directoryID = createDirectoryEntry($_POST['categoryID'], $_POST['name'], $_POST['description'],$_POST['address1'], $_POST['address2'], $_POST['address3'], $_POST['address4'], $_POST['address5'], $_POST['postcode'], $_POST['telephone1'], "", "", "", "", @$coords['latitude'], @$coords['longitude'], $_POST['email'], $_POST['url'], "", "", $_POST['statusID'], 0, $_POST['createdbyID'], "","", $isparent=0, "", "", @$_POST['directorytype'], $public);
  
  	if(isset($_POST['directoryarea'])) {
foreach($_POST['directoryarea'] as $value) {
	$insert = "INSERT INTO directoryinarea (directoryID, directoryareaID, createdbyID, createddatetime) VALUES (". $directoryID.",".$value.",".GetSQLValueString($_POST['createdbyID'], "int").",NOW())";
	$result = mysql_query($insert, $aquiescedb) or die(mysql_error());
	
}
	}

	$insert = "INSERT INTO directoryuser (directoryID, userID, createdbyID, createddatetime) VALUES (".$directoryID.",".GetSQLValueString($_POST['createdbyID'], "int").",".GetSQLValueString($_POST['createdbyID'], "int").",NOW())";
	$result = mysql_query($insert, $aquiescedb) or die(mysql_error());
	if(isset($_GET['returnURL'])) {
		$insertGoTo = $_GET['returnURL']; 
		 $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
	$insertGoTo .= "directoryID=".$directoryID; 
	} else {
  $insertGoTo = "/directory/members/index.php?addentry=true&directoryID=".$directoryID;
	}
	
	 if(isset($row_rsPreferences['googlemapsAPI'])) { // gooogle maps exist
  $insertGoTo = "map.php?&directoryID=".$directoryID."&returnURL=".urlencode($insertGoTo);
  
  }
  
  
  header(sprintf("Location: %s", $insertGoTo));exit;
  }


$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID, email FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

$varRegionID_rsParentCategory = "0";
if (isset($regionID)) {
  $varRegionID_rsParentCategory = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsParentCategory = sprintf("SELECT ID, description FROM directorycategory WHERE (directorycategory.regionID = %s OR directorycategory.regionID=0) AND directorycategory.statusID = 1 AND directorycategory.subCatOfID = 0 ORDER BY description ASC", GetSQLValueString($varRegionID_rsParentCategory, "int"));
$rsParentCategory = mysql_query($query_rsParentCategory, $aquiescedb) or die(mysql_error());
$row_rsParentCategory = mysql_fetch_assoc($rsParentCategory);
$totalRows_rsParentCategory = mysql_num_rows($rsParentCategory);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsDirectoryPrefs = "SELECT * FROM directoryprefs";
$rsDirectoryPrefs = mysql_query($query_rsDirectoryPrefs, $aquiescedb) or die(mysql_error());
$row_rsDirectoryPrefs = mysql_fetch_assoc($rsDirectoryPrefs);
$totalRows_rsDirectoryPrefs = mysql_num_rows($rsDirectoryPrefs);

$varCategoryID_rsThisCategory = "0";
if (isset($_GET['categoryID'])) {
  $varCategoryID_rsThisCategory = $_GET['categoryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsThisCategory = sprintf("SELECT directorycategory.ID, directorycategory.`description`, parentcategory.ID AS parentID FROM directorycategory LEFT JOIN  directorycategory AS parentcategory ON (directorycategory.subcatofID = parentcategory.ID) WHERE directorycategory.ID = %s", GetSQLValueString($varCategoryID_rsThisCategory, "int"));
$rsThisCategory = mysql_query($query_rsThisCategory, $aquiescedb) or die(mysql_error());
$row_rsThisCategory = mysql_fetch_assoc($rsThisCategory);
$totalRows_rsThisCategory = mysql_num_rows($rsThisCategory);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsDirectoryAreas = "SELECT directoryarea.ID, directoryarea.areaname FROM directoryarea WHERE directoryarea.statusID ORDER BY directoryarea.areaname";
$rsDirectoryAreas = mysql_query($query_rsDirectoryAreas, $aquiescedb) or die(mysql_error());
$row_rsDirectoryAreas = mysql_fetch_assoc($rsDirectoryAreas);
$totalRows_rsDirectoryAreas = mysql_num_rows($rsDirectoryAreas);




?>
<?php 
// set initial parameters for select menus
$parentID = isset($row_rsThisCategory['parentID']) ? $row_rsThisCategory['parentID'] : $row_rsThisCategory['ID'];
$categoryID = isset($row_rsThisCategory['parentID']) ? $row_rsThisCategory['ID'] : 0;
?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Add organisation";  echo $pageTitle." | ".$site_name; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<meta name="robots" content="noindex,nofollow" /><style>
<!--
<?php if ($totalRows_rsParentCategory < 1) {
echo ".category { display:none; }";
}
?> <?php if ($totalRows_rsDirectoryAreas < 1) {
echo ".directoryareas { display:none; }";
}
?>
-->
</style>
<script src="../../SpryAssets/SpryValidationTextField.js"></script>
<script src="../../SpryAssets/SpryValidationTextarea.js"></script>
<script src="../../SpryAssets/SpryValidationRadio.js"></script>
<link href="../../SpryAssets/SpryValidationRadio.css" rel="stylesheet" >
<link href="../../SpryAssets/SpryValidationTextField.css" rel="stylesheet"  />
<link href="../../SpryAssets/SpryValidationTextarea.css" rel="stylesheet"  />
<script src="../../core/scripts/formUpload.js"></script>
<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" -->
      <div id="pageMemberAddDirectory">
      <div class="crumbs"><div><span class="you_are_in">You are in: </span><a href="/">Home</a><span class="separator">&nbsp;&rsaquo;&nbsp;</span><a href="/directory/"><?php echo $row_rsDirectoryPrefs['directoryname']; ?></a><span class="separator">&nbsp;&rsaquo;&nbsp;</span>Add an entry</div></div>
      <h1 class="directoryheader">Add your organisation... </h1>
      <?php if(isset($submit_error) && $submit_error!="") { ?>
      <p class="alert alert-danger" role="alert"><?php echo $submit_error; ?></p>
      <?php } 
	  else { if(isset($_GET['msg'])) { ?>
      <p class="message alert alert-info" role="alert"><?php echo htmlentities($_GET['msg'], ENT_COMPAT, "UTF-8"); ?></p>
      <?php } } ?>
      <form action="<?php echo $editFormAction; ?>" method="post" enctype="multipart/form-data" name="form1" id="form1" >
        <table class="form-table">
          <tr class="category">
            <td class="text-nowrap text-right">Category:</td>
            <td><select name="categoryID" id="categoryID" onChange="getData('/directory/ajax/createSubCatSelect.php?parentCatID='+this.value+'&amp;categoryID=<?php echo intval($_GET['categoryID']); ?>','subCat')" class="form-control" >
                <option value="0" <?php if (!(strcmp(0, $parentID))) {echo "selected=\"selected\"";} ?>><?php echo isset($region['text_choose']) ? htmlentities($region['text_choose'], ENT_COMPAT, "UTF-8") : "Choose..." ?></option>
                <?php
do {  
?>
                <option value="<?php echo $row_rsParentCategory['ID']?>"<?php if (!(strcmp($row_rsParentCategory['ID'], $parentID))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsParentCategory['description']?></option>
                <?php
} while ($row_rsParentCategory = mysql_fetch_assoc($rsParentCategory));
  $rows = mysql_num_rows($rsParentCategory);
  if($rows > 0) {
      mysql_data_seek($rsParentCategory, 0);
	  $row_rsParentCategory = mysql_fetch_assoc($rsParentCategory);
  }
?>
              </select></td>
          </tr>
          <tr class="category">
            <td class="text-nowrap text-right">Sub category:</td>
            <td id="subCat"><select name="subcategoryID" id="subcategoryID" class="form-control" >
                <option value="0">None</option>
              </select></td>
          </tr> <tr>
            <td class="text-nowrap text-right">Organisation name:</td>
            <td><span id="sprytextfield1">
              <input name="name"  id="name" type="text"  size="50" maxlength="255" autocomplete="off" value="<?php echo isset($_POST['name']) ? htmlentities($_POST['name'], ENT_COMPAT, "UTF-8") : ""; ?>" class="form-control" />
              <span class="textfieldRequiredMsg">A value is required.</span></span></td>
          </tr>
          <tr class="directorytypes">
            <td class="text-nowrap text-right" > Type:</td>
            <td><span id="spryradio1">
              <label>
                <input  type="radio" name="directorytype" value="1" id="directorytype_1" />
                Ltd</label>
              <label>
                <input  type="radio" name="directorytype" value="2" id="directorytype_2" />
                LLP</label>
              <label>
                <input  type="radio" name="directorytype" value="3" id="directorytype_3" />
                Sole trader</label>
              <label>
                <input type="radio" name="directorytype" value="4" id="directorytype_4" />
                Charity</label>
              <label>
                <input  type="radio" name="directorytype" value="5" id="directorytype_5" />
                Private </label>
              <label>
                <input type="radio" name="directorytype" value="6" id="directorytype_6" />
                Social Enterprise</label>
              <label>
                <input type="radio" name="directorytype" value="7" id="directorytype_7" />
                Public Sector</label>
                <label>
                <input type="radio" name="directorytype" value="0" id="directorytype_0" />
                Other</label>
              <span class="radioRequiredMsg">Please make a selection.</span></span></td>
          </tr>
          <tr class="directoryareas">
            <td class="text-nowrap text-right" >Areas covered:</td>
            <td><?php do { ?>
                <label>
                  <input name="directoryarea[<?php echo $row_rsDirectoryAreas['ID']; ?>]" type="checkbox" value="<?php echo $row_rsDirectoryAreas['ID']; ?>">
                  &nbsp;<?php echo $row_rsDirectoryAreas['areaname']; ?></label>
                &nbsp;&nbsp;&nbsp;
                <?php } while ($row_rsDirectoryAreas = mysql_fetch_assoc($rsDirectoryAreas)); ?></td>
          </tr> <tr>
            <td class="text-nowrap text-right top">Description:</td>
            <td class="top"><span id="sprytextarea1">
              <textarea name="description" id="description" cols="50" rows="5" class="form-control" ><?php echo isset($_POST['description']) ? htmlentities($_POST['description'], ENT_COMPAT, "UTF-8") : ""; ?></textarea>
              </span></td>
          </tr> <tr>
            <td class="text-nowrap text-right top">Address:</td>
            <td><span id="sprytextfield2">
              <input name="address1" type="text"  id="address1" value="<?php echo isset($_POST['address1']) ? htmlentities($_POST['address1'], ENT_COMPAT, "UTF-8") : ""; ?>" size="50" maxlength="50"  class="form-control" />
              <span class="textfieldRequiredMsg">A value is required.</span></span></td>
          </tr> <tr>
            <td class="text-nowrap text-right top">&nbsp;</td>
            <td><input name="address2" type="text"  id="address2" value="<?php echo isset($_POST['address2']) ? htmlentities($_POST['address2'], ENT_COMPAT, "UTF-8") : ""; ?>" size="50" maxlength="50" class="form-control"  /></td>
          </tr> <tr>
            <td class="text-nowrap text-right top">&nbsp;</td>
            <td><input name="address3" type="text"  id="address3" value="<?php echo isset($_POST['address3']) ? htmlentities($_POST['address3'], ENT_COMPAT, "UTF-8") : ""; ?>" size="50" maxlength="50"  class="form-control" /></td>
          </tr> <tr>
            <td class="text-nowrap text-right top">&nbsp;</td>
            <td><input name="address4" type="text"  id="address4" value="<?php echo isset($_POST['address4']) ? htmlentities($_POST['address4'], ENT_COMPAT, "UTF-8") : ""; ?>" size="50" maxlength="50" class="form-control"  /></td>
          </tr> <tr>
            <td class="text-nowrap text-right top">&nbsp;</td>
            <td><input name="address5" type="text"  id="address5" value="<?php echo isset($_POST['address5']) ? htmlentities($_POST['address5'], ENT_COMPAT, "UTF-8") : ""; ?>" size="50" maxlength="50" class="form-control"  /></td>
          </tr> <tr>
            <td class="text-nowrap text-right">Postcode:</td>
            <td><input name="postcode" type="text"  value="<?php echo isset($_POST['postcode']) ? htmlentities($_POST['postcode'], ENT_COMPAT, "UTF-8") : ""; ?>" size="50" maxlength="10" autocomplete="off" class="form-control"  /></td>
          </tr> <tr>
            <td class="text-nowrap text-right">Telephone:</td>
            <td><input name="telephone1" type="text"  id="telephone1" value="<?php echo isset($_POST['telephone']) ? htmlentities($_POST['telephone1'], ENT_COMPAT, "UTF-8") : ""; ?>" size="50" maxlength="50"  autocomplete="off" class="form-control"  /></td>
          </tr> <tr>
            <td class="text-nowrap text-right">Organisation email:</td>
            <td><input name="email" type="email" multiple value="" size="50" maxlength="100" autocomplete="off" class="form-control"  /></td>
          </tr> <tr>
            <td class="text-nowrap text-right">Web site:</td>
            <td><span id="sprytextfield3">
              <input name="url" type="text"  value="<?php echo isset($_POST['url']) ? htmlentities($_POST['url']) : ""; ?>" size="50" maxlength="50"  class="form-control" />
              </span></td>
          </tr> <tr>
            <td class="text-nowrap text-right">&nbsp;</td>
            <td><label>
                <input name="public" type="checkbox" id="public" value="1" checked>
                I am happy for this information to be publicly available on the web site</label></td>
          </tr> <tr>
            <td class="text-nowrap text-right">&nbsp;</td>
            <td><div>
                <button type="submit" class="btn btn-primary" >Continue...</button>
              </div></td>
          </tr>
        </table>
        <input type="hidden" name="statusID" value="<?php echo ($row_rsDirectoryPrefs['approveupdates'] == 1) ? 0 : 1; ?>" />
        <input type="hidden" name="createdbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
        <input type="hidden" name="createddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
        <input type="hidden" name="MM_insert" value="form1" />
        <input type="hidden" name="imageURL" />
      </form></div>
      <script>
<!--
var sprytextfield3 = new Spry.Widget.ValidationTextField("sprytextfield3", "none", {hint:"http://", isRequired:false});
var sprytextarea1 = new Spry.Widget.ValidationTextarea("sprytextarea1", {isRequired:false, hint:"Enter some details about your organisation"});
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1");
var spryradio1 = new Spry.Widget.ValidationRadio("spryradio1");
//-->
  </script> 
      <!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsParentCategory);

mysql_free_result($rsDirectoryPrefs);

mysql_free_result($rsThisCategory);

mysql_free_result($rsDirectoryAreas);

mysql_free_result($rsPreferences);
?>
