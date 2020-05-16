<?php require_once('../../Connections/aquiescedb.php'); ?><?php require_once('../../core/includes/adminAccess.inc.php'); ?>
<?php require_once('../../core/includes/upload.inc.php'); ?><?php require_once('../../articles/includes/functions.inc.php'); ?><?php require_once('../../core/includes/framework.inc.php'); ?>
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



if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
	$uploaded = getUploads();
	if (isset($uploaded) && is_array($uploaded)) {
		if(isset($uploaded["filename"][0]["newname"]) && $uploaded["filename"][0]["newname"]!="") { 
			$_POST['imageURL'] = $uploaded["filename"][0]["newname"]; 
		}
		$_POST['imageURL'] = (isset($_POST["noImage"])) ? "" : $_POST['imageURL'];
	}
	
	$_POST['width_px'] = ($_POST['width_px'] =="") ? @$uploaded["filename"][0]['width'] : $_POST['width_px'];
	$_POST['height_px'] = ($_POST['height_px'] =="") ? @$uploaded["filename"][0]['height'] : $_POST['height_px'];
	if(strlen($_POST['furniturelink'])>1) {
		// remove site host if added by accident
		$_POST['furniturelink'] = str_replace("http://".$_SERVER['HTTP_HOST'],"",$_POST['furniturelink']);
		$_POST['furniturelink'] = str_replace("https://".$_SERVER['HTTP_HOST'],"",$_POST['furniturelink']);
	}

}


if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE furniture SET furniturename=%s, furnituretext=%s, furniturelink=%s, appearsonURL=%s, newwindow=%s, imageURL=%s, width_px=%s, height_px=%s, modifiedbyID=%s, modifieddatetime=%s, statusID=%s WHERE ID=%s",
                       GetSQLValueString($_POST['furniturename'], "text"),
                       GetSQLValueString($_POST['furnituretext'], "text"),
                       GetSQLValueString($_POST['furniturelink'], "text"),
                       GetSQLValueString($_POST['appearsonURL'], "text"),
                       GetSQLValueString(isset($_POST['newwindow']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['imageURL'], "text"),
                       GetSQLValueString($_POST['width_px'], "int"),
                       GetSQLValueString($_POST['height_px'], "int"),
                       GetSQLValueString($_POST['modifiedbyID'], "int"),
                       GetSQLValueString($_POST['modifieddatetime'], "date"),
                       GetSQLValueString(isset($_POST['statusID']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  header(sprintf("Location: %s", $_POST['returnURL'])); exit;
}

$colname_rsLoggedIn = "1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

$colname_rsFurniture = "-1";
if (isset($_GET['furnitureID'])) {
  $colname_rsFurniture = $_GET['furnitureID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsFurniture = sprintf("SELECT * FROM furniture WHERE ID = %s", GetSQLValueString($colname_rsFurniture, "int"));
$rsFurniture = mysql_query($query_rsFurniture, $aquiescedb) or die(mysql_error());
$row_rsFurniture = mysql_fetch_assoc($rsFurniture);
$totalRows_rsFurniture = mysql_num_rows($rsFurniture);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLinks = "(SELECT 0 AS type, article.sectionID,article.ID AS articleID ,article.longID, articlesection.longID AS sectionlongID, articlesection.subsectionofID AS parentsectionID, CONCAT('Page: ',articlesection.description,' > ', article.title) AS name FROM article LEFT JOIN articlesection ON (articlesection.ID = article.sectionID) WHERE article.statusID = 1 AND article.versionofID IS NULL  AND articlesection.regionID = ".$regionID.") UNION (SELECT 1 AS type, articlesection.ID AS sectionID, NULL AS articleID, NULL AS longID, articlesection.longID AS sectionlongID, articlesection.subsectionofID AS parentsectionID, CONCAT('Section: ',articlesection.description) AS name FROM articlesection WHERE articlesection.regionID = ".$regionID.") ORDER BY type, name";
$rsLinks = mysql_query($query_rsLinks, $aquiescedb) or die(mysql_error());
$row_rsLinks = mysql_fetch_assoc($rsLinks);
$totalRows_rsLinks = mysql_num_rows($rsLinks);
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Update Furniture"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" --><script src="/core/scripts/formUpload.js"></script>
<script src="../../SpryAssets/SpryValidationTextField.js"></script>
<script src="../../SpryAssets/SpryValidationTextarea.js"></script>
<link href="../../SpryAssets/SpryValidationTextField.css" rel="stylesheet"  />
<link href="../../SpryAssets/SpryValidationTextarea.css" rel="stylesheet"  />
<style >
#imagesize {
	display: none;
}
</style>
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" --><div class="page furniture">
  <h1>Update Furniture</h1>
  
  <form action="<?php echo $editFormAction; ?>" method="POST" enctype="multipart/form-data" name="form1" id="form1">
    <table class="form-table"> <tr>
        <td class="text-nowrap text-right">Name:</td>
        <td><span id="sprytextfield1">
          <input name="furniturename" type="text"  value="<?php echo $row_rsFurniture['furniturename']; ?>" size="50" maxlength="50" class="form-control" />
          <span class="textfieldRequiredMsg">A value is required.</span></span></td>
      </tr> <tr>
        <td class="text-nowrap text-right top">Text:</td>
        <td><span id="sprytextarea1">
          <textarea name="furnituretext" cols="50" rows="5" class="form-control"><?php echo $row_rsFurniture['furnituretext']; ?></textarea>
  </span></td>
      </tr> <tr>
        <td class="text-nowrap text-right">Link:</td>
        <td><select name="linkmenu" id="linkmenu" onChange="document.getElementById('furniturelink').value = this.value" class="form-control">
          <option value="">Select page or type url below...</option>
          <?php
do {  
?>
          <option value="<?php echo articleLink($row_rsLinks['articleID'], $row_rsLinks['longID'], $row_rsLinks['sectionID'], $row_rsLinks['sectionlongID'], $row_rsLinks['parentsectionID']); ?>"><?php echo $row_rsLinks['name']?></option>
          <?php
} while ($row_rsLinks = mysql_fetch_assoc($rsLinks));
  $rows = mysql_num_rows($rsLinks);
  if($rows > 0) {
      mysql_data_seek($rsLinks, 0);
	  $row_rsLinks = mysql_fetch_assoc($rsLinks);
  }
?>
        </select>
          <br />
            <input name="furniturelink" id="furniturelink" type="text"  value="<?php echo $row_rsFurniture['furniturelink']; ?>" size="50" maxlength="100" class="form-control" />
</td>
      </tr> <tr>
        <td class="text-nowrap text-right">&nbsp;</td>
        <td><label>
          <input <?php if (!(strcmp($row_rsFurniture['newwindow'],1))) {echo "checked=\"checked\"";} ?> name="newwindow" type="checkbox" id="newwindow" value="1" />
          Open link in new window</label></td>
      </tr> <tr>
        <td class="text-nowrap text-right">Appears on:</td>
        <td><select name="appearsonURL" id="appearsonURL" class="form-control" >
          <option value="" <?php if (!(strcmp("", $row_rsFurniture['appearsonURL']))) {echo "selected=\"selected\"";} ?>>All pages</option> <option value="/" <?php if (!(strcmp("/", $row_rsFurniture['appearsonURL']))) {echo "selected=\"selected\"";} ?>>Home</option>
          <?php
do {  
?>
          <option value="<?php echo articleLink($row_rsLinks['articleID'], $row_rsLinks['longID'], $row_rsLinks['sectionID'], $row_rsLinks['sectionlongID'], $row_rsLinks['parentsectionID']); ?>"<?php if (!(strcmp(articleLink($row_rsLinks['articleID'], $row_rsLinks['longID'], $row_rsLinks['sectionID'], $row_rsLinks['sectionlongID'], $row_rsLinks['parentsectionID']), $row_rsFurniture['appearsonURL']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsLinks['name']?></option>
<?php
} while ($row_rsLinks = mysql_fetch_assoc($rsLinks));
  $rows = mysql_num_rows($rsLinks);
  if($rows > 0) {
      mysql_data_seek($rsLinks, 0);
	  $row_rsLinks = mysql_fetch_assoc($rsLinks);
  }
?>
        </select></td>
      </tr>
      <tr valign="top">
        <td class="text-nowrap text-right">Image:</td>
        <td>
          <?php if (isset($row_rsFurniture['imageURL'])) { ?>
            <img src="<?php echo getImageURL($row_rsFurniture['imageURL'], "medium"); ?>" alt="Current image" class="medium" /><br />Size: <?php echo $row_rsFurniture['width_px']; ?> x <?php echo $row_rsFurniture['height_px']; ?><input name="noImage" type="checkbox" value="1" />
            Remove image
            <?php } else { ?>
            No image at present.
            <?php } ?>
          <br />
          Add/change image below:<br />
          <input name="filename" type="file" class="fileinput" id="filename" size="20" />
          <input type="hidden" name="imageURL" value="<?php echo $row_rsFurniture['imageURL']; ?>" />
          </td>
      </tr> <tr>
        <td class="text-nowrap text-right">Size:</td>
        <td><label>
          <input name="autodetect" type="checkbox" id="autodetect" onClick="if(this.checked) { document.getElementById('imagesize').style.display = 'none'; document.getElementById('width_px').value = ''; document.getElementById('height_px').value = ''; } else { document.getElementById('imagesize').style.display = 'inline' }" checked="checked"/>
          Auto detect</label><br /><span id="imagesize" class="form-inline">Width:<input name="width_px" type="text"  value="<?php echo $row_rsFurniture['width_px']; ?>" size="4" maxlength="4" class="form-control" />
          pixels Height:
            <input name="height_px" type="text"  value="<?php echo $row_rsFurniture['height_px']; ?>" size="4" maxlength="4" class="form-control"  /> 
            pixels</span></td>
      </tr> <tr>
        <td class="text-nowrap text-right">Active:</td>
        <td><label>
          <input <?php if (!(strcmp($row_rsFurniture['statusID'],1))) {echo "checked=\"checked\"";} ?> name="statusID" type="checkbox" id="statusID" value="1" />
        </label></td>
      </tr> <tr>
        <td class="text-nowrap text-right">&nbsp;</td>
        <td><button type="submit" class="btn btn-primary" >Save changes</button></td>
      </tr>
    </table>
    <input type="hidden" name="modifiedbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
    <input type="hidden" name="modifieddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
    <input name="ID" type="hidden" id="ID" value="<?php echo $row_rsFurniture['ID']; ?>" />
    <input type="hidden" name="MM_update" value="form1" /><input name="returnURL" type="hidden" value="<?php echo isset($_GET['returnURL']) ? $_GET['returnURL'] : $_SERVER['HTTP_REFERER']; ?>">
  </form>
  <script>
<!--
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1");
var sprytextarea1 = new Spry.Widget.ValidationTextarea("sprytextarea1", {isRequired:false, hint:"Add any supplemental text - this usually will not be visible but will help with accessibility"});
//-->
  </script></div>
  <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsFurniture);

mysql_free_result($rsLinks);
?>
