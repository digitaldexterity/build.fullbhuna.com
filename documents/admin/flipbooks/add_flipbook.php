<?php require_once('../../../Connections/aquiescedb.php'); ?>
<?php require_once('../../../core/includes/adminAccess.inc.php'); ?>
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

$MM_restrictGoTo = "/login/index.php?notloggedin=true&alert=".urlencode("You need to be logged in as an Administrator to access this page.");
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
  $insertSQL = sprintf("INSERT INTO flipbook (flipbookname, galleryID, createdbyID, createddatetime, statusID, downloadURL, categoryID) VALUES (%s, %s, %s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['flipbookname'], "text"),
                       GetSQLValueString($_POST['galleryID'], "int"),
                       GetSQLValueString($_POST['createdbyID'], "int"),
                       GetSQLValueString($_POST['createddatetime'], "date"),
                       GetSQLValueString(isset($_POST['statusID']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['downloadURL'], "text"),
                       GetSQLValueString($_POST['categoryID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($insertSQL, $aquiescedb) or die(mysql_error());
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
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
$query_rsLoggedIn = sprintf("SELECT ID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

$colname_rsGalleries = "-1";
if (isset($_SESSION['MM_UserGroup'])) {
  $colname_rsGalleries = $_SESSION['MM_UserGroup'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsGalleries = sprintf("SELECT ID, categoryname FROM photocategories WHERE accesslevel <= %s ORDER BY categoryname ASC", GetSQLValueString($colname_rsGalleries, "int"));
$rsGalleries = mysql_query($query_rsGalleries, $aquiescedb) or die(mysql_error());
$row_rsGalleries = mysql_fetch_assoc($rsGalleries);
$totalRows_rsGalleries = mysql_num_rows($rsGalleries);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsDocumentCategories = "SELECT documentcategory.ID, parentcat.categoryname AS parent, documentcategory.categoryname FROM documentcategory LEFT JOIN documentcategory AS parentcat ON (documentcategory.subcatofID = parentcat.ID) ORDER BY categoryname ASC";
$rsDocumentCategories = mysql_query($query_rsDocumentCategories, $aquiescedb) or die(mysql_error());
$row_rsDocumentCategories = mysql_fetch_assoc($rsDocumentCategories);
$totalRows_rsDocumentCategories = mysql_num_rows($rsDocumentCategories);
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Add Flipbook"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script src="../../../SpryAssets/SpryValidationSelect.js"></script>
<script src="../../../SpryAssets/SpryValidationTextField.js"></script>
<link href="../../../SpryAssets/SpryValidationSelect.css" rel="stylesheet"  />
<link href="../../../SpryAssets/SpryValidationTextField.css" rel="stylesheet"  />
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
    <h1><i class="glyphicon glyphicon-folder-open"></i> Add Flipbook</h1>
    <p>1. Prepare pages as JPEGs. (You can export from a PDF using Acrobat - choose highest quality settings).</p>
    <p>2. <a href="../../../photos/members/add_photos.php?returnURL=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>">Import all pictures into a photo gallery</a>. (Add chapter or bookmark pages in photo description).</p>
    <p>3. Add Flipbook below:</p>
   
    <form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1">
      <table class="form-table"> <tr>
          <td class="text-nowrap text-right"><label for="categoryID">Folder:</label></td>
          <td>
            <select name="categoryID" id="categoryID" class="form-control">
              <option value="" <?php if (!(strcmp("", @$_GET['categoryID']))) {echo "selected=\"selected\"";} ?>>Add to document folder...</option>
              <?php
do {  
?>
              <option value="<?php echo $row_rsDocumentCategories['ID']?>"<?php if (!(strcmp($row_rsDocumentCategories['ID'], @$_GET['categoryID']))) {echo "selected=\"selected\"";} ?>><?php echo isset($row_rsDocumentCategories['parent']) ? $row_rsDocumentCategories['parent']." > " : ""; echo $row_rsDocumentCategories['categoryname']; ?></option>
              <?php
} while ($row_rsDocumentCategories = mysql_fetch_assoc($rsDocumentCategories));
  $rows = mysql_num_rows($rsDocumentCategories);
  if($rows > 0) {
      mysql_data_seek($rsDocumentCategories, 0);
	  $row_rsDocumentCategories = mysql_fetch_assoc($rsDocumentCategories);
  }
?>
          </select></td>
        </tr> <tr>
          <td class="text-nowrap text-right">Flipbook name:</td>
          <td><span id="sprytextfield1">
            <input name="flipbookname" type="text" value="" size="50" maxlength="50" class="form-control" />
          <span class="textfieldRequiredMsg">A value is required.</span></span></td>
        </tr> <tr>
          <td class="text-nowrap text-right">Pages from gallery:</td>
          <td><span id="spryselect1">
            <select name="galleryID"  class="form-control">
              <option value=""><?php echo isset($region['text_choose']) ? htmlentities($region['text_choose'], ENT_COMPAT, "UTF-8") : "Choose..." ?></option>
              <?php
do {  
?>
              <option value="<?php echo $row_rsGalleries['ID']?>"><?php echo $row_rsGalleries['categoryname']?></option>
              <?php
} while ($row_rsGalleries = mysql_fetch_assoc($rsGalleries));
  $rows = mysql_num_rows($rsGalleries);
  if($rows > 0) {
      mysql_data_seek($rsGalleries, 0);
	  $row_rsGalleries = mysql_fetch_assoc($rsGalleries);
  }
?>
            </select>
          <span class="selectRequiredMsg">Please select an item.</span></span></td>
        </tr> <tr>
          <td class="text-nowrap text-right"><label for="downloadURL">Alt download URL</label></td>
          <td><span id="sprytextfield2">
            <input name="downloadURL" type="text" id="downloadURL" size="50" maxlength="255"  class="form-control"/>
</span></td>
        </tr>
        <tr>        </tr> <tr>
          <td class="text-nowrap text-right">Active:</td>
          <td><input type="checkbox" name="statusID" value="" checked="checked" /></td>
        </tr> <tr>
          <td class="text-nowrap text-right">&nbsp;</td>
          <td><button type="submit" class="btn btn-primary" >Add Flipbook</button></td>
        </tr>
      </table>
      <input type="hidden" name="createdbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
      <input type="hidden" name="createddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
      <input type="hidden" name="MM_insert" value="form1" />
    </form>
   
    <script>
var spryselect1 = new Spry.Widget.ValidationSelect("spryselect1");
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1");
var sprytextfield2 = new Spry.Widget.ValidationTextField("sprytextfield2", "none", {isRequired:false, hint:"(optional link to downloadable version)"});
    </script>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsGalleries);

mysql_free_result($rsDocumentCategories);
?>
