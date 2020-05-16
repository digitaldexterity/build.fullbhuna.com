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

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE flipbook SET flipbookname=%s, galleryID=%s, modifiedbyID=%s, modifieddatetime=%s, statusID=%s, downloadURL=%s, categoryID=%s WHERE ID=%s",
                       GetSQLValueString($_POST['flipbookname'], "text"),
                       GetSQLValueString($_POST['galleryID'], "int"),
                       GetSQLValueString($_POST['modifiedbyID'], "int"),
                       GetSQLValueString($_POST['modifieddatetime'], "date"),
                       GetSQLValueString(isset($_POST['statusID']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['downloadURL'], "text"),
                       GetSQLValueString($_POST['categoryID'], "int"),
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
  header(sprintf("Location: %s", $updateGoTo)); exit;
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

$colname_rsFlipbook = "-1";
if (isset($_GET['flipbookID'])) {
  $colname_rsFlipbook = $_GET['flipbookID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsFlipbook = sprintf("SELECT * FROM flipbook WHERE ID = %s", GetSQLValueString($colname_rsFlipbook, "int"));
$rsFlipbook = mysql_query($query_rsFlipbook, $aquiescedb) or die(mysql_error());
$row_rsFlipbook = mysql_fetch_assoc($rsFlipbook);
$totalRows_rsFlipbook = mysql_num_rows($rsFlipbook);

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
<?php $pageTitle = "Update Flipbook"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
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
    <h1><i class="glyphicon glyphicon-folder-open"></i> Update Flipbook</h1>
   
    <form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1">
      <table class="form-table"> <tr>
          <td class="text-nowrap text-right"><label for="categoryID">Folder:</label></td>
          <td><select name="categoryID" id="categoryID" class="form-control">
            <option value="" <?php if (!(strcmp("", $row_rsFlipbook['categoryID']))) {echo "selected=\"selected\"";} ?>>Add to document folder...</option>
            <?php
do {  
?>
            <option value="<?php echo $row_rsDocumentCategories['ID']; ?>"  <?php if (!(strcmp($row_rsDocumentCategories['ID'], $row_rsFlipbook['categoryID']))) {echo "selected=\"selected\"";} ?>>
            <?php echo isset($row_rsDocumentCategories['parent']) ? $row_rsDocumentCategories['parent']." > " : ""; echo $row_rsDocumentCategories['categoryname']; ?>
            </option>
            <?php
} while ($row_rsDocumentCategories = mysql_fetch_assoc($rsDocumentCategories));
  $rows = mysql_num_rows($rsDocumentCategories);
  if($rows > 0) {
      mysql_data_seek($rsDocumentCategories, 0);
	  $row_rsDocumentCategories = mysql_fetch_assoc($rsDocumentCategories);
  }
?>
            <?php
do {  
?>
            <option value="<?php echo $row_rsDocumentCategories['ID']?>"<?php if (!(strcmp($row_rsDocumentCategories['ID'], $row_rsFlipbook['categoryID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsDocumentCategories['categoryname']?></option>
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
            <input name="flipbookname" type="text" value="<?php echo $row_rsFlipbook['flipbookname']; ?>" size="50" maxlength="50" class="form-control" />
          <span class="textfieldRequiredMsg">A value is required.</span></span></td>
        </tr> <tr>
          <td class="text-nowrap text-right">Pages from gallery:</td>
          <td><span id="spryselect1">
            <select name="galleryID"  class="form-control">
              <option value="" <?php if (!(strcmp("", $row_rsFlipbook['galleryID']))) {echo "selected=\"selected\"";} ?>><?php echo isset($region['text_choose']) ? htmlentities($region['text_choose'], ENT_COMPAT, "UTF-8") : "Choose..." ?></option>
              <?php
do {  
?>
<option value="<?php echo $row_rsGalleries['ID']?>"<?php if (!(strcmp($row_rsGalleries['ID'], $row_rsFlipbook['galleryID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsGalleries['categoryname']?></option>
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
            <input name="downloadURL" type="text" id="downloadURL" value="<?php echo $row_rsFlipbook['downloadURL']; ?>" size="50" maxlength="255" class="form-control" />
          </span></td>
        </tr>
        <tr>        </tr> <tr>
          <td class="text-nowrap text-right">Active:</td>
          <td><input <?php if (!(strcmp($row_rsFlipbook['statusID'],1))) {echo "checked=\"checked\"";} ?> type="checkbox" name="statusID" value="1" /></td>
        </tr> <tr>
          <td class="text-nowrap text-right">&nbsp;</td>
          <td><button type="submit" class="btn btn-primary">Save changes</button></td>
        </tr>
      </table>
      <input type="hidden" name="modifiedbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
      <input type="hidden" name="modifieddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
      <input name="ID" type="hidden" id="ID" value="<?php echo $row_rsFlipbook['ID']; ?>" />
      <input type="hidden" name="MM_update" value="form1" />
    </form>
    <p>&nbsp;</p>
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

mysql_free_result($rsFlipbook);

mysql_free_result($rsDocumentCategories);
?>
