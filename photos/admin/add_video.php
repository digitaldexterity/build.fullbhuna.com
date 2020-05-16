<?php require_once('../../core/includes/adminAccess.inc.php'); ?><?php require_once('../../core/includes/adminAccess.inc.php'); ?>
<?php require_once('../../Connections/aquiescedb.php'); ?><?php require_once('../../core/includes/framework.inc.php'); ?>
<?php require_once('../../core/includes/autolinks.inc.php'); ?><?php require_once('../includes/galleryfunctions.inc.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}$MM_authorizedUsers = "8,9,10";
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



mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = "SELECT * FROM preferences WHERE ID = ".$regionID;
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsMediaPrefs = "SELECT * FROM mediaprefs";
$rsMediaPrefs = mysql_query($query_rsMediaPrefs, $aquiescedb) or die(mysql_error());
$row_rsMediaPrefs = mysql_fetch_assoc($rsMediaPrefs);
$totalRows_rsMediaPrefs = mysql_num_rows($rsMediaPrefs);





if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
	
	require_once('../../core/includes/upload.inc.php'); 
	$_POST['summary'] = addLinks($_POST['summary']);
	
		$uploaded = getUploads(UPLOAD_ROOT,$image_sizes,"",0,0,"",array("gif","png","jpeg","jpg","mp4"),"longest");
		if(isset($uploaded['filename']) && is_array($uploaded['filename'][0]) && isset($uploaded['filename'][0]['error'])) {
			$submit_error = $uploaded['filename'][0]['error'];
			unset($_POST['MM_update']);
		} else if (isset($uploaded['filename']) && is_array($uploaded['filename'][0]) && isset($uploaded['filename'][0]['newname'])) {
			$_POST['imageURL'] = $uploaded['filename'][0]['newname'];
			$_POST['width'] = @$uploaded['filename'][0]['width'];
			$_POST['height'] = @$uploaded['filename'][0]['height'];
		} 
		
		if(isset($uploaded['filename2']) && is_array($uploaded['filename2'][0]) && isset($uploaded['filename2'][0]['error'])) {
			$submit_error .= $uploaded['filename2'][0]['error'];
			unset($_POST['MM_update']);
		} else if (isset($uploaded['filename2']) && is_array($uploaded['filename2'][0]) && isset($uploaded['filename2'][0]['newname'])) {
			$_POST['videoURL'] = $uploaded['filename2'][0]['newname'];
		} 
	
}// end post

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
  $insertSQL = sprintf("INSERT INTO photos (imageURL, title, `description`, active, linkURL, categoryID, createddatetime, videoURL) VALUES (%s, %s, %s, %s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['imageURL'], "text"),
                       GetSQLValueString($_POST['title'], "text"),
                       GetSQLValueString($_POST['summary'], "text"),
                       GetSQLValueString($_POST['status'], "int"),
                       GetSQLValueString($_POST['linkURL'], "text"),
                       GetSQLValueString($_POST['categoryID'], "int"),
                       GetSQLValueString($_POST['createddatetime'], "date"),
                       GetSQLValueString($_POST['videoURL'], "text"));

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

$colname_rsLoggedIn = "1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = (get_magic_quotes_gpc()) ? $_SESSION['MM_Username'] : addslashes($_SESSION['MM_Username']);
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID, users.usertypeID FROM users WHERE username = '%s'", $colname_rsLoggedIn);
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPhotoCategory = "SELECT * FROM photocategories WHERE active = 1 ORDER BY categoryname ASC";
$rsPhotoCategory = mysql_query($query_rsPhotoCategory, $aquiescedb) or die(mysql_error());
$row_rsPhotoCategory = mysql_fetch_assoc($rsPhotoCategory);
$totalRows_rsPhotoCategory = mysql_num_rows($rsPhotoCategory);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsStatus = "SELECT * FROM status";
$rsStatus = mysql_query($query_rsStatus, $aquiescedb) or die(mysql_error());
$row_rsStatus = mysql_fetch_assoc($rsStatus);
$totalRows_rsStatus = mysql_num_rows($rsStatus);
?><!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Add Video"; echo $pageTitle." | ".$site_name; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->


<script src="../../SpryAssets/SpryValidationTextField.js"></script>
<script src="../../SpryAssets/SpryValidationSelect.js"></script>
<link href="../../SpryAssets/SpryValidationTextField.css" rel="stylesheet"  />
<link href="../../SpryAssets/SpryValidationSelect.css" rel="stylesheet"  />
<script src="/core/scripts/formUpload.js"></script>
<style>
<?php if ($row_rsMediaPrefs['allowlinks']!=1) { ?>
.link {display:none; }
<?php } ?>
</style>
<link href="../css/defaultGallery.css" rel="stylesheet"  />



<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" --><div class="pageBody">
<h1><i class="glyphicon glyphicon-picture"></i> Add Video </h1>
<?php require_once('../../core/includes/alert.inc.php'); ?>
<form action="<?php echo $editFormAction; ?>" method="POST" enctype="multipart/form-data" name="form1" id="form1">

     
        <table class="form-table">
          <tr>
            <td class="text-right text-nowrap">Title:</td>
            <td><span id="sprytextfield1">
              <input name="title" type="text"  value="<?php echo isset($_POST['title']) ?  htmlentities($_POST['title'], ENT_COMPAT, "UTF-8") : ""; ?>" size="50" maxlength="50" class="form-control" />
              <span class="textfieldRequiredMsg">A title is required.</span></span></td>
          </tr>
          <tr>
            <td class="text-right text-nowrap">Gallery:</td>
            <td><span id="spryselect1">
              <select name="categoryID"  id="categoryID"  class="form-control">
                <option value="" <?php if (!(strcmp("", $row_rsPhoto['categoryID']))) {echo "SELECTED";} ?>>General</option>
                <?php
do {  
?>
                <option value="<?php echo $row_rsPhotoCategory['ID']?>"<?php if (!(strcmp($row_rsPhotoCategory['ID'], $row_rsPhoto['categoryID']))) {echo "SELECTED";} ?>><?php echo $row_rsPhotoCategory['categoryname']?></option>
                <?php
} while ($row_rsPhotoCategory = mysql_fetch_assoc($rsPhotoCategory));
  $rows = mysql_num_rows($rsPhotoCategory);
  if($rows > 0) {
      mysql_data_seek($rsPhotoCategory, 0);
	  $row_rsPhotoCategory = mysql_fetch_assoc($rsPhotoCategory);
  }
?>
              </select>
              <span class="selectRequiredMsg">Please select an item.</span></span><a href="galleries/add_gallery.php">Add a gallery </a></td>
          </tr>
          <tr>
            <td class="text-right top text-nowrap">Description:</td>
            <td><textarea name="summary" cols="50" rows="5"  class="form-control"><?php echo isset($_POST['summary']) ?  htmlentities($_POST['summary'], ENT_COMPAT, "UTF-8") : ""; ?></textarea></td>
          </tr>
          <tr  class="link">
            <td class="text-right top text-nowrap"><label for="linkURL">Link:</label></td>
            <td><input name="linkURL" type="text" id="linkURL" size="50" maxlength="255" value="<?php echo isset($_POST['linkURL']) ?  htmlentities($_POST['linkURL'], ENT_COMPAT, "UTF-8") : ""; ?>"  class="form-control"/></td>
          </tr>
          <tr>
            <td class="text-right text-nowrap">Status:</td>
            <td><select name="status"  id="status"  class="form-control">
              <?php
do {  
?>
              <option value="<?php echo $row_rsStatus['ID']?>"><?php echo $row_rsStatus['description']?></option>
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
          <tr>
            <td class="text-right text-nowrap">Thumbnail file:</td>
            <td><input type="hidden" name="MAX_FILE_SIZE" value="<?php echo defined("MAX_UPLOAD") ? MAX_UPLOAD : "2000000"; ?>" />
              <input name="filename" type="file" class="fileinput" id="filename" size="20" />
              <input type="hidden" name="imageURL" id="imageURL"></td>
          </tr>
          <tr>
            <td class="text-right text-nowrap">Video file:</td>
            <td class="form-inline" >
              <input name="filename2" type="file" class="fileinput" id="filename" size="20" /> 
              (mp4 ONLY)
              <input type="hidden" name="videoURL" id="videoURL"></td>
          </tr>
          
          
        </table>
        <input name="createdbyID" type="hidden" id="createdbyID" value="<?php echo $row_rsPhoto['userID']; ?>" />
        
        <input name="createddatetime" type="hidden" id="createddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
       
    <p>Maximum upload size on this server is: <?php  echo ini_get('upload_max_filesize')>ini_get('post_max_size') ? ini_get('post_max_size') : ini_get('upload_max_filesize'); ?></p>
  
          <button type="submit" class="btn btn-primary" >Add video</button>
          <input type="hidden" name="MM_insert" value="form1">
              
        </form>

</div>
<script>
<!--
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1");
var spryselect1 = new Spry.Widget.ValidationSelect("spryselect1");
//-->
</script>
  <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsPhotoCategory);

mysql_free_result($rsStatus);

mysql_free_result($rsMediaPrefs);
?>
