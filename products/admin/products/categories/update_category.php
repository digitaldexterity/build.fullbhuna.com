<?php require_once('../../../../Connections/aquiescedb.php'); ?>
<?php require_once('../../../../core/includes/adminAccess.inc.php'); ?>
<?php require_once('../../../../core/includes/upload.inc.php'); ?>
<?php require_once('../../../../core/includes/framework.inc.php'); ?>
<?php require_once('../../inc/product_functions.inc.php'); ?>
<?php $msg = "";
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

$MM_restrictGoTo = "../../../../login/index.php?notloggedin=true";
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

if(isset($_POST['copytoregionID']) && intval($_POST['copytoregionID'])>0 && isset($_POST['regioncategoryID'])) {
	copyProductsToRegion($_POST['copytoregionID'], $regionID , $_GET['categoryID'], $_POST['regioncategoryID']) ;
	$msg .= "Products in the category have been copied. ";
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
	$uploaded = getUploads();
	if (isset($uploaded) && is_array($uploaded)) {
		if(isset($uploaded["filename"][0]["newname"]) && $uploaded["filename"][0]["newname"]!="") { 
			$_POST['imageURL'] = $uploaded["filename"][0]["newname"]; 
		}
		$_POST['imageURL'] = (isset($_POST["noImage"])) ? "" : $_POST['imageURL'];
		
		if(isset($uploaded["filename2"][0]["newname"]) && $uploaded["filename2"][0]["newname"]!="") { 
			$_POST['imageURL2'] = $uploaded["filename2"][0]["newname"]; 
		}
		$_POST['imageURL2'] = (isset($_POST["noImage2"])) ? "" : $_POST['imageURL2'];
		
		if(isset($uploaded["filename3"][0]["newname"]) && $uploaded["filename3"][0]["newname"]!="") { 
			$_POST['imageURL3'] = $uploaded["filename3"][0]["newname"]; 
		}
		$_POST['imageURL3'] = (isset($_POST["noImage3"])) ? "" : $_POST['imageURL3'];
	
	}
	$_POST['longID'] =  createURLname($_POST['longID'], $_POST['description'], "-", "productcategory", $_POST['ID']);
	$_POST['usergroupID'] =  isset($_POST['usergroupID']) ? $_POST['usergroupID'] : "";
	


}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE productcategory SET longID=%s, accesslevel=%s, groupID=%s, metadescription=%s, metakeywords=%s, forcemaincategory=%s, gbasecat=%s, subcatofID=%s, usergroupID=%s, title=%s, summary=%s, `description`=%s, appendProductDescription=%s, freesamplesku=%s, samplequote=%s, nextproductsku=%s, excludepromotions=%s, featured=%s, imageURL=%s, imageURL2=%s, imageURL3=%s, statusID=%s, showinmenu=%s, vatdefault=%s, vatincluded=%s, vatprice=%s, vattext=%s, noindex=%s, categorysale=%s, seotitle=%s, directoryID=%s, directoryadmin=%s, directorynotify=%s, modifiedbyID=%s, modifieddatetime=%s, redirectURL=%s WHERE ID=%s",
                       GetSQLValueString($_POST['longID'], "text"),
                       GetSQLValueString($_POST['accesslevel'], "int"),
                       GetSQLValueString($_POST['groupID'], "int"),
                       GetSQLValueString($_POST['metadescription'], "text"),
                       GetSQLValueString($_POST['metakeywords'], "text"),
                       GetSQLValueString(isset($_POST['forcemaincategory']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['gbasecat'], "text"),
                       GetSQLValueString($_POST['subcatofID'], "int"),
                       GetSQLValueString($_POST['usergroupID'], "int"),
                       GetSQLValueString($_POST['title'], "text"),
                       GetSQLValueString($_POST['summary'], "text"),
                       GetSQLValueString($_POST['description'], "text"),
                       GetSQLValueString($_POST['appendProductDescription'], "text"),
                       GetSQLValueString($_POST['freesamplesku'], "text"),
                       GetSQLValueString(isset($_POST['samplequote']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['nextproductsku'], "text"),
                       GetSQLValueString(isset($_POST['excludepromotions']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['featured']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['imageURL'], "text"),
                       GetSQLValueString($_POST['imageURL2'], "text"),
                       GetSQLValueString($_POST['imageURL3'], "text"),
                       GetSQLValueString(isset($_POST['statusID']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['showinmenu']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['vatdefault']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['vatincluded']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['vatprice']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['vattext']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['noindex']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['categorysale']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['seotitle'], "text"),
                       GetSQLValueString($_POST['directoryID'], "int"),
                       GetSQLValueString(isset($_POST['directoryadmin']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['directorynotify']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['modifiedbyID'], "int"),
                       GetSQLValueString($_POST['modifieddatetime'], "date"),
                       GetSQLValueString($_POST['redirectURL'], "text"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
	if(isset($_POST['forcemaincategory'])) {
		makeMainCategory($_POST['ID'],0);
	}
	
	if(isset($_POST['resetsale'])) {
		$update = "UPDATE product SET price = listprice WHERE product.productcategoryID = ".GetSQLValueString($_POST['ID'], "int")." AND listprice > 0";
		$result = mysql_query($update, $aquiescedb) or die(mysql_error());	
		$update = "UPDATE product SET saleitem = 0 WHERE product.productcategoryID = ".GetSQLValueString($_POST['ID'], "int");
		$result = mysql_query($update, $aquiescedb) or die(mysql_error());
	}
  $updateGoTo = "index.php?msg=".$msg;
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo));exit;
}

$colname_rsCategory = "-1";
if (isset($_GET['categoryID'])) {
  $colname_rsCategory = $_GET['categoryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsCategory = sprintf("SELECT * FROM productcategory WHERE ID = %s", GetSQLValueString($colname_rsCategory, "int"));
$rsCategory = mysql_query($query_rsCategory, $aquiescedb) or die(mysql_error());
$row_rsCategory = mysql_fetch_assoc($rsCategory);
$totalRows_rsCategory = mysql_num_rows($rsCategory);

$varThisCategoryID_rsCategories = "-1";
if (isset($_GET['categoryID'])) {
  $varThisCategoryID_rsCategories = $_GET['categoryID'];
}
$varRegionID_rsCategories = "1";
if (isset($regionID)) {
  $varRegionID_rsCategories = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsCategories = sprintf("SELECT productcategory.ID, productcategory.title, productcategory.statusID, parentcategory.title AS parent FROM productcategory LEFT JOIN productcategory AS parentcategory ON (productcategory.subcatofID  = parentcategory.ID) WHERE productcategory.statusID = 1 AND productcategory.regionID = %s  AND productcategory.ID != %s ORDER BY parentcategory.ordernum, productcategory.ordernum ", GetSQLValueString($varRegionID_rsCategories, "int"),GetSQLValueString($varThisCategoryID_rsCategories, "int"));
$rsCategories = mysql_query($query_rsCategories, $aquiescedb) or die(mysql_error());
$row_rsCategories = mysql_fetch_assoc($rsCategories);
$totalRows_rsCategories = mysql_num_rows($rsCategories);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = "SELECT useregions FROM preferences";
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsRegions = "SELECT ID, title FROM region ORDER BY title ASC";
$rsRegions = mysql_query($query_rsRegions, $aquiescedb) or die(mysql_error());
$row_rsRegions = mysql_fetch_assoc($rsRegions);
$totalRows_rsRegions = mysql_num_rows($rsRegions);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsRanks = "SELECT * FROM usertype WHERE ID > 0 ORDER BY ID ASC";
$rsRanks = mysql_query($query_rsRanks, $aquiescedb) or die(mysql_error());
$row_rsRanks = mysql_fetch_assoc($rsRanks);
$totalRows_rsRanks = mysql_num_rows($rsRanks);

$varRegionID_rsGroups = "1";
if (isset($regionID)) {
  $varRegionID_rsGroups = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsGroups = sprintf("SELECT ID, groupname FROM usergroup WHERE groupsetID IS NULL and statusID =1 AND regionID = %s ORDER BY groupname ASC", GetSQLValueString($varRegionID_rsGroups, "int"));
$rsGroups = mysql_query($query_rsGroups, $aquiescedb) or die(mysql_error());
$row_rsGroups = mysql_fetch_assoc($rsGroups);
$totalRows_rsGroups = mysql_num_rows($rsGroups);

$varRegionID_rsDirectory = "1";
if (isset($regionID)) {
  $varRegionID_rsDirectory = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsDirectory = sprintf("SELECT directory .ID, name FROM directory LEFT JOIN directoryincategory ON (directoryincategory.directoryID = directory.ID) LEFT JOIN directorycategory ON (directoryincategory.categoryID = directorycategory.ID) WHERE directorycategory.regionID = %s", GetSQLValueString($varRegionID_rsDirectory, "int"));
$rsDirectory = mysql_query($query_rsDirectory, $aquiescedb) or die(mysql_error());
$row_rsDirectory = mysql_fetch_assoc($rsDirectory);
$totalRows_rsDirectory = mysql_num_rows($rsDirectory);

$varRegionID_rsUserGroups = "1";
if (isset($regionID)) {
  $varRegionID_rsUserGroups = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsUserGroups = sprintf("SELECT usergroup.ID, usergroup.groupname FROM usergroup WHERE usergroup.regionID = %s AND usergroup.statusID = 1 ORDER BY usergroup.groupname", GetSQLValueString($varRegionID_rsUserGroups, "int"));
$rsUserGroups = mysql_query($query_rsUserGroups, $aquiescedb) or die(mysql_error());
$row_rsUserGroups = mysql_fetch_assoc($rsUserGroups);
$totalRows_rsUserGroups = mysql_num_rows($rsUserGroups);
?>
<!doctype html>

<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Update Category: ".$row_rsCategory['title']; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<?php require_once('../../../../core/tinymce/tinymce.inc.php'); ?>
<script src="/SpryAssets/SpryTabbedPanels.js"></script>
<script src="../../../../SpryAssets/SpryValidationTextField.js"></script><script src="../../../../core/scripts/formUpload.js"></script><script>
addListener("load", init);

function init() {
	toggleVAT();
}

function toggleVAT() {
	
	if(document.getElementById('vatdefault').checked) {
		document.getElementById('categoryvat').style.display = 'none';
	} else { 
		document.getElementById('categoryvat').style.display = 'block';
	}
}

function updateRegionCategories(regionID) {
	url ="ajax/regioncategories.ajax.php?regionID="+parseInt(regionID);
	getData(url,"copytocategory");
}
</script>
<link href="/SpryAssets/SpryTabbedPanels.css" rel="stylesheet"  />
<style>
<?php if($totalRows_rsRegions<=1) {
?> .region {
display:none;
}
<?php
}
?>
</style>
<link href="../../../../SpryAssets/SpryValidationTextField.css" rel="stylesheet"  />
<?php if(isset($body_class)) $body_class .= " products ";  ?>
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" -->
      <div class="page products">
        <h1><i class="glyphicon glyphicon-shopping-cart"></i> Update Category: <?php echo $row_rsCategory['title']; ?></h1>
        <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
          <li><a href="index.php" class="link_undo"><i class="glyphicon glyphicon-arrow-left"></i> Back</a> </li>
        </ul></div></nav>
        <?php if(isset($submit_error)) { ?>
        <p class="alert alert-danger" role="alert"><?php echo $submit_error; ?></p>
        <?php } ?>
        <form action="<?php echo $editFormAction; ?>" method="post" enctype="multipart/form-data" name="form1" id="form1" >
          <div id="TabbedPanels1" class="TabbedPanels">
            <ul class="TabbedPanelsTabGroup">
              <li class="TabbedPanelsTab" tabindex="0">Details</li>
              <li class="TabbedPanelsTab" tabindex="1">User Groups</li>
              <li class="TabbedPanelsTab" tabindex="2">VAT</li>
              <li class="TabbedPanelsTab" tabindex="3">Free sample</li>
              <li class="TabbedPanelsTab" tabindex="4">Options</li>
              <li class="TabbedPanelsTab region" tabindex="5">Copy to site</li>
              <li class="TabbedPanelsTab" tabindex="6">Dropship</li>
              <li class="TabbedPanelsTab" tabindex="0">SEO</li>
            </ul>
            <div class="TabbedPanelsContentGroup">
              <div class="TabbedPanelsContent">
                <table class="form-table">
                  <tr>
                    <td class="text-nowrap text-right">Title:</td>
                    <td><span id="sprytextfield1">
                      <input name="title" type="text"  value="<?php echo $row_rsCategory['title']; ?>" size="50" maxlength="50" class="form-control" />
                      <span class="textfieldRequiredMsg">A value is required.</span></span></td>
                  </tr>
                  <tr class="region">
                    <td class="text-nowrap text-right">Site:</td>
                    <td><select name="regionID" id="regionID"  class="form-control" >
                        <option value="1" <?php if (!(strcmp(1, $row_rsCategory['regionID']))) {echo "selected=\"selected\"";} ?>><?php echo isset($region['text_choose']) ? htmlentities($region['text_choose'], ENT_COMPAT, "UTF-8") : "Choose..." ?></option>
                        <?php
do {  
?>
                        <option value="<?php echo $row_rsRegions['ID']?>"<?php if (!(strcmp($row_rsRegions['ID'], $row_rsCategory['regionID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsRegions['title']?></option>
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
                    <td class="text-nowrap text-right">Sub category of: </td>
                    <td><select name="subcatofID" id="subcatofID"  class="form-control" >
                        <option value="0" <?php if (!(strcmp(0, $row_rsCategory['subcatofID']))) {echo "selected=\"selected\"";} ?>>None</option>
                        <?php
do {  
?>
                        <option value="<?php echo $row_rsCategories['ID']?>"<?php if (!(strcmp($row_rsCategories['ID'], $row_rsCategory['subcatofID']))) {echo "selected=\"selected\"";} ?>>
                        <?php  echo isset($row_rsCategories['parent']) ? $row_rsCategories['parent']." &rsaquo; " : ""; echo $row_rsCategories['title']?>
                        </option>
                        <?php
} while ($row_rsCategories = mysql_fetch_assoc($rsCategories));
  $rows = mysql_num_rows($rsCategories);
  if($rows > 0) {
      mysql_data_seek($rsCategories, 0);
	  $row_rsCategories = mysql_fetch_assoc($rsCategories);
  }
?>
                      </select></td>
                  </tr>
                  <tr>
                    <td class="text-nowrap text-right top">Summary:<br>
                      (appears in category listing)</td>
                    <td class="top">
                        <textarea name="summary" cols="50" rows="3" id="summary" class="form-control"  ><?php echo $row_rsCategory['summary']; ?></textarea></td>
                  </tr>
                  <tr>
                    <td class="text-nowrap text-right top">Description:<br>
                      (appears on category page)</td>
                    <td><textarea name="description" cols="50" rows="5" class="tinymce"><?php echo $row_rsCategory['description']; ?></textarea></td>
                  </tr>
                  <tr>
                    <td class="text-nowrap text-right top">Image:</td>
                    <td><?php if (isset($row_rsCategory['imageURL'])) { ?>
                        <img src="<?php echo getImageURL($row_rsCategory['imageURL'], "medium"); ?>" alt="" /><br />
                        <input name="noImage" type="checkbox" value="1" />
                        Remove image
                        <?php } else { ?>
                        No image associated with this category.
                        <?php } ?>
                      <span class="upload"><br />
                      Add/change image below:<br />
                      <input name="filename" type="file" id="filename" size="20" class="fileinput " accept=".jpg,.jpeg,.gif,.png"  />
                      </span>
                      <input name="imageURL" type="hidden" id="imageURL" value="<?php echo $row_rsCategory['imageURL']; ?>" /></td>
                  </tr>
                  <tr>
                    <td class="text-nowrap text-right top">Image:</td>
                    <td><?php if (isset($row_rsCategory['imageURL2'])) { ?>
                        <img src="<?php echo getImageURL($row_rsCategory['imageURL2'], "medium"); ?>" alt="" /><br />
                        <input name="noImage2" type="checkbox" value="1" />
                        Remove image
                        <?php } else { ?>
                        No image associated with this category.
                        <?php } ?>
                      <span class="upload"><br />
                      Add/change image below:<br />
                      <input name="filename2" type="file" id="filename2" size="20" class="fileinput " accept=".jpg,.jpeg,.gif,.png"  />
                      </span>
                      <input name="imageURL2" type="hidden" id="imageURL2" value="<?php echo $row_rsCategory['imageURL2']; ?>" /></td>
                  </tr>
                  <tr>
                    <td class="text-nowrap text-right top"> Overlay Image:</td>
                    <td><?php if (isset($row_rsCategory['imageURL3'])) { ?>
                        <img src="<?php echo getImageURL($row_rsCategory['imageURL3'],"medium"); ?>" alt="" /><br />
                        <input name="noImage3" type="checkbox" value="1" />
                        Remove image
                        <?php } else { ?>
                        No image associated with this category.
                        <?php } ?>
                      <span class="upload"><br />
                      Add/change image below:<br />
                      <input name="filename3" type="file" id="filename3" size="20" class="fileinput " accept=".jpg,.jpeg,.gif,.png"  />
                      </span>
                      <input name="imageURL3" type="hidden" id="imageURL3" value="<?php echo $row_rsCategory['imageURL3']; ?>" /></td>
                  </tr>
                  <tr>
                    <td class="text-nowrap text-right"><label for="categorysale">On sale:</label></td>
                    <td><input <?php if (!(strcmp($row_rsCategory['categorysale'],1))) {echo "checked=\"checked\"";} ?> name="categorysale" type="checkbox" id="categorysale" value="1">
                      &nbsp;&nbsp;&nbsp;
                      <label>Featured:
                        <input <?php if (!(strcmp($row_rsCategory['featured'],1))) {echo "checked=\"checked\"";} ?> name="featured" type="checkbox" id="featured" value="1">
                      </label>
                      &nbsp;&nbsp;&nbsp;
                      <label>Reset products on sale:
                        <input type="checkbox" name="resetsale" id="resetsale">
                      </label></td>
                  </tr>
                  <tr>
                    <td class="text-nowrap text-right"><label for="statusID" data-toggle="tooltip" title="Check to make live. Uncheck to prevent categories and products uniquely within from showing.">Active:</label></td>
                    <td><input type="checkbox" name="statusID" id="statusID" value="1" <?php if (!(strcmp($row_rsCategory['statusID'],1))) {echo "checked=\"checked\"";} ?> />
                      &nbsp;&nbsp;&nbsp;
                      <label data-toggle="tooltip" title="If checked, will show in menus and index pages. Otherwise hidden, and can be used for promotions.">Show on  site:
                        <input <?php if (!(strcmp($row_rsCategory['showinmenu'],1))) {echo "checked=\"checked\"";} ?> name="showinmenu" type="checkbox" id="showinmenu" value="1">
                      </label></td>
                  </tr>
                </table>
              </div>
              <div class="TabbedPanelsContent">
                <h3>Permissions:</h3>
                <p>Only users with the following credentials can view and buy from this category:</p>
                <table class="form-table form-inline">
                  <tr>
                    <td align="right" valign="top">Access:</td>
                    <td class="form-inline"><select name="accesslevel" id="accesslevel"  class="form-control">
                        <option value="0" <?php if (!(strcmp(0, $row_rsCategory['accesslevel']))) {echo "selected=\"selected\"";} ?>>Everyone</option>
                        <?php
do {  
?>
                        <option value="<?php echo $row_rsRanks['ID']?>"<?php if (!(strcmp($row_rsRanks['ID'], $row_rsCategory['accesslevel']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsRanks['name']?></option>
                        <?php
} while ($row_rsRanks = mysql_fetch_assoc($rsRanks));
  $rows = mysql_num_rows($rsRanks);
  if($rows > 0) {
      mysql_data_seek($rsRanks, 0);
	  $row_rsRanks = mysql_fetch_assoc($rsRanks);
  }
?>
                      </select>
                      in group
                      <select name="groupID" id="groupID"  class="form-control">
                        <option value="0" <?php if (!(strcmp(0, $row_rsCategory['groupID']))) {echo "selected=\"selected\"";} ?>>Any group</option>
                        <?php $rows = mysql_num_rows($rsGroups);
  if($rows > 0) {
do {  
?>
                        <option value="<?php echo $row_rsGroups['ID']?>"<?php if (!(strcmp($row_rsGroups['ID'], $row_rsCategory['groupID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsGroups['groupname']?></option>
                        <?php
} while ($row_rsGroups = mysql_fetch_assoc($rsGroups));
  
      mysql_data_seek($rsGroups, 0);
	  $row_rsGroups = mysql_fetch_assoc($rsGroups);
  }
?>
                      </select></td>
                  </tr>
                </table>
                <h3>Purchase group:</h3>
                <p>
                  <label for="usergroupID"> Automatically add anyone who buys a product from this category to the following user group:</label>
                </p>
                <p>
                  <?php if ($totalRows_rsUserGroups > 0) { // Show if recordset not empty ?>
                    <select name="usergroupID" id="usergroupID"  class="form-control">
                      <option value="" <?php if (!(strcmp("", $row_rsCategory['usergroupID']))) {echo "selected=\"selected\"";} ?>><?php echo isset($region['text_choose']) ? htmlentities($region['text_choose'], ENT_COMPAT, "UTF-8") : "Choose..." ?></option>
                      <?php
do {  
?>
                      <option value="<?php echo $row_rsUserGroups['ID']?>"<?php if (!(strcmp($row_rsUserGroups['ID'], $row_rsCategory['usergroupID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsUserGroups['groupname']?></option>
                      <?php
} while ($row_rsUserGroups = mysql_fetch_assoc($rsUserGroups));
  $rows = mysql_num_rows($rsUserGroups);
  if($rows > 0) {
      mysql_data_seek($rsUserGroups, 0);
	  $row_rsUserGroups = mysql_fetch_assoc($rsUserGroups);
  }
?>
                    </select>
                    <?php } // Show if recordset not empty ?>
                </p>
                <?php if ($totalRows_rsUserGroups == 0) { // Show if recordset empty ?>
                  <p>There are currently no users groups in the system. <a href="../../../../members/admin/groups/index.php">Add  here</a>.
                    <?php } // Show if recordset empty ?>
              </div>
              <div class="TabbedPanelsContent">
                <p>
                  <label>Use VAT preferences set site-wide:
                    <input <?php if (!(strcmp($row_rsCategory['vatdefault'],1))) {echo "checked=\"checked\"";} ?> name="vatdefault" id="vatdefault" type="checkbox" value="1" onClick="toggleVAT()">
                  </label>
                </p>
                <div id="categoryvat">
                  <p>The following settings will override the site-wide preferences for this category only:</p>
                  <table border="0" cellpadding="2" cellspacing="2" class="form-table">
                    <tr>
                      <td align="right">VAT included in price:</td>
                      <td><input <?php if (!(strcmp($row_rsCategory['vatincluded'],1))) {echo "checked=\"checked\"";} ?> name="vatincluded" type="checkbox" id="vatincluded" value="1" />
                        (the price shown includes VAT.)</td>
                    </tr>
                    <tr>
                      <td align="right">Show VAT text</td>
                      <td><label>
                          <input <?php if (!(strcmp($row_rsCategory['vattext'],1))) {echo "checked=\"checked\"";} ?> name="vattext" type="checkbox" id="vattext" value="1">
                          (will show "inc or ex-VAT". Does not apply with option below.)</label></td>
                    </tr>
                    <tr>
                      <td align="right">Show other price:</td>
                      <td><input <?php if (!(strcmp($row_rsCategory['vatprice'],1))) {echo "checked=\"checked\"";} ?> name="vatprice" type="checkbox" id="vatprice" value="1" />
                        (Also show re-calculated price - with or without VAT depending on main price)</td>
                    </tr>
                  </table>
                </div>
              </div>
              <div class="TabbedPanelsContent">
                <p>To offer a free sample of products in this category:</p>
                <p>1. add a product to the system called(for example) &quot;Free Sample&quot;</p>
                <p>2. We recommend to set to not display</p>
                <p>3. Give it an SKU and add below:</p>
                <p class="form-inline">
                  <label>Free sample SKU:
                    <input name="freesamplesku" type="text" id="freesamplesku" value="<?php echo $row_rsCategory['freesamplesku']; ?>" size="50" maxlength="150"  class="form-control" >
                  </label>
                  <label>
                    <input <?php if (!(strcmp($row_rsCategory['samplequote'],1))) {echo "checked=\"checked\"";} ?> name="samplequote" type="checkbox" id="samplequote" value="1">
                    Ask for expected purchase quantitty</label>
                </p>
                <p>&nbsp;</p>
              </div>
              <div class="TabbedPanelsContent">
                <h2>Also Add</h2>
                <p>Offer the ability to add a specified additional product when adding any product to the basket within this category. </p>
                <p>This option will add a checkbox below the add to basket button giving the customer this option. If checked, the customer will be immediately taken to the additional product page so they can add that product also.</p>
                <p class="form-inline">
                  <label for="nextproductsku">Product stock code:</label>
                  <input name="nextproductsku" type="text" id="nextproductsku" value="<?php echo $row_rsCategory['nextproductsku']; ?>" size="50" maxlength="50"  class="form-control" >
                </p>
                <h2>Promotions</h2>
                <p>
                  <label>
                    <input <?php if (!(strcmp($row_rsCategory['excludepromotions'],1))) {echo "checked=\"checked\"";} ?> name="excludepromotions" type="checkbox" id="excludepromotions" value="1">
                    Exclude items in this category from promotions </label>
                </p>
                <h2>Main Category</h2>
                <p>
                  <label>
                    <input <?php if (!(strcmp($row_rsCategory['forcemaincategory'],1))) {echo "checked=\"checked\"";} ?> name="forcemaincategory" type="checkbox" id="forcemaincategory" value="1">
                    Make this the main category of any product added to it (any existing product category will be made a supplementary category)</label>
                </p>
                <h2>Redirect</h2>
                <p>You can redirect this category to another internal or extrenal URL:</p>
                <p>
                  <input name="redirectURL" type="text" id="redirectURL" placeholder="http://" value="<?php echo $row_rsCategory['redirectURL']; ?>" size="100" maxlength="255"  class="form-control" >
                </p>
                <p>&nbsp;</p>
              </div>
              <div class="TabbedPanelsContent region">
                <p>You can copy all active products within this category to another site, either to an exitisng category or a new one.</p>
                <table class="form-table">
                  <tr>
                    <td><label for="copytoregionID">Copy to site:</label></td>
                    <td><select name="copytoregionID" id="copytoregionID" onChange="updateRegionCategories(this.value);"  class="form-control" >
                        <option value=""><?php echo isset($region['text_choose']) ? htmlentities($region['text_choose'], ENT_COMPAT, "UTF-8") : "Choose..." ?></option>
                        <?php
do { if(isset($regionID) && $row_rsRegions['ID'] != $regionID) { 
?>
                        <option value="<?php echo $row_rsRegions['ID']; ?>"><?php echo $row_rsRegions['title']?></option>
                        <?php
} } while ($row_rsRegions = mysql_fetch_assoc($rsRegions));
  $rows = mysql_num_rows($rsRegions);
  if($rows > 0) {
      mysql_data_seek($rsRegions, 0);
	  $row_rsRegions = mysql_fetch_assoc($rsRegions);
  }
?>
                      </select></td>
                  </tr>
                  <tr>
                    <td>Copy to category:</td>
                    <td id="copytocategory">&nbsp;</td>
                  </tr>
                </table>
              </div>
              <div class="TabbedPanelsContent">
                <p>
                  <label for="directoryID">Dropship company:</label>
                </p>
                <p class="form-inline">
                  <select name="directoryID" id="directoryID"  class="form-control" >
                    <option value="" <?php if (!(strcmp("", $row_rsCategory['directoryID']))) {echo "selected=\"selected\"";} ?>><?php echo isset($region['text_choose']) ? htmlentities($region['text_choose'], ENT_COMPAT, "UTF-8") : "Choose..." ?></option>
                    <?php
do {  
?>
                    <option value="<?php echo $row_rsDirectory['ID']?>"<?php if (!(strcmp($row_rsDirectory['ID'], $row_rsCategory['directoryID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsDirectory['name']?></option>
                    <?php
} while ($row_rsDirectory = mysql_fetch_assoc($rsDirectory));
  $rows = mysql_num_rows($rsDirectory);
  if($rows > 0) {
      mysql_data_seek($rsDirectory, 0);
	  $row_rsDirectory = mysql_fetch_assoc($rsDirectory);
  }
?>
                  </select>
                  <a href="../../../../directory/admin/add_directory.php?returnURL=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>">Add company</a></p>
                <p>
                  <label>
                    <input <?php if (!(strcmp($row_rsCategory['directorynotify'],1))) {echo "checked=\"checked\"";} ?> name="directorynotify" type="checkbox" id="directorynotify" value="1">
                    Auto-notify company on sale</label>
                  &nbsp;&nbsp;&nbsp;
                  <label>
                    <input <?php if (!(strcmp($row_rsCategory['directoryadmin'],1))) {echo "checked=\"checked\"";} ?> name="directoryadmin" type="checkbox" id="directoryadmin" value="1">
                    Allow company "Agents" to adminsiter products in ths category</label>
                </p>
              </div>
              <div class="TabbedPanelsContent">
                <table border="0" cellpadding="2" cellspacing="0" class="form-table">
                  
                    <td align="right"><label for="seotitle">Title tag:</label></td>
                    <td>
                      <input name="seotitle" type="text" id="seotitle" value="<?php echo $row_rsCategory['seotitle']; ?>" size="50" maxlength="66"  class="seo-length form-control"></td>
                  </tr><tr class="longID">
                    <td align="right">URL name:</td>
                    <td><input name="longID" type="text"  id="longID" value="<?php echo $row_rsCategory['longID']; ?>" size="50" maxlength="100" class="form-control" /></td>
                  </tr>
                  <tr>
                  <tr>
                    <td align="right" valign="top">Meta Keywords:</td>
                    <td><textarea name="metakeywords" id="metakeywords" cols="45" rows="5" class="form-control"><?php echo $row_rsCategory['metakeywords']; ?></textarea></td>
                  </tr>
                  <tr>
                    <td align="right" valign="top">Meta Description:</td>
                    <td><textarea name="metadescription" id="metadescription" cols="45" rows="5" class="seo-length form-control"><?php echo $row_rsCategory['metadescription']; ?></textarea></td>
                  </tr>
                  <tr>
                  <tr>
                    <td align="right"><label for="gbasecat" data-toggle="tooltip" title="This is the category identifier number or title that Google uses for Merchant Center. This must match the Google ID or category title exactly">Google Category ID:</label></td>
                    <td><input name="gbasecat" type="text" id="gbasecat" value="<?php echo $row_rsCategory['gbasecat']; ?>" size="50" maxlength="255" placeholder="(optional)"  class="form-control" /></td>
                  </tr>
                  
                    <td align="right" valign="top"><label for="appendProductDescription">Product Description Append HTML:</label></td>
                    <td><textarea name="appendProductDescription" cols="45" rows="5" id="appendProductDescription" class="form-control"><?php echo $row_rsCategory['appendProductDescription']; ?></textarea></td>
                  </tr>
                  <tr>
                    <td align="right" valign="top"><label for="noindex">Hide from search engines:</label></td>
                    <td><input <?php if (!(strcmp($row_rsCategory['noindex'],1))) {echo "checked=\"checked\"";} ?> name="noindex" type="checkbox" id="noindex" onClick="if(this.checked) alert('A \'No Index\' request will be added to all products in this category.\n\nNote: This applies to category index and product pages, but there is no guarantee that all search engines will comply.');" value="1"></td>
                  </tr>
                </table>
              </div>
            </div>
          </div>
          <div>
            <input name="modifiedbyID" type="hidden" id="modifiedbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
            <input name="modifieddatetime" type="hidden" id="modifieddatetime" value="<?php echo date("Y-m-d H:i:s"); ?>" />
            <input type="hidden" name="MM_update" value="form1" />
            <input type="hidden" name="ID" value="<?php echo $row_rsCategory['ID']; ?>" />
            <button type="submit" class="btn btn-primary" >Save changes</button>
          </div>
        </form>
        <script>
<!--
var TabbedPanels1 = new Spry.Widget.TabbedPanels("TabbedPanels1");
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1");
//-->
</script></div>
      <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsCategory);

mysql_free_result($rsCategories);

mysql_free_result($rsPreferences);

mysql_free_result($rsRegions);

mysql_free_result($rsRanks);

mysql_free_result($rsGroups);

mysql_free_result($rsDirectory);

mysql_free_result($rsUserGroups);
?>