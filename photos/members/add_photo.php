<?php require_once('../../Connections/aquiescedb.php'); ?><?php require_once('../../core/includes/autolinks.inc.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "1,2,3,4,5,6,7,8,9,10";
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
$query_rsMediaPrefs = "SELECT * FROM mediaprefs";
$rsMediaPrefs = mysql_query($query_rsMediaPrefs, $aquiescedb) or die(mysql_error());
$row_rsMediaPrefs = mysql_fetch_assoc($rsMediaPrefs);
$totalRows_rsMediaPrefs = mysql_num_rows($rsMediaPrefs);

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
	require_once('../../core/includes/upload.inc.php'); 

	$_POST['summary'] = addLinks($_POST['summary']);
	$uploaded = getUploads(UPLOAD_ROOT,$image_sizes,"",0,0,"",array("gif","png","jpeg","jpg"),"longest");
	if(is_array($uploaded['filename'][0]) && isset($uploaded['filename'][0]['error'])) {
		$submit_error = $uploaded['filename'][0]['error'];
		unset($_POST['MM_insert']);
	} else if(is_array($uploaded['filename'][0]) && isset($uploaded['filename'][0]['newname'])) {
		$_POST['imageURL'] = $uploaded['filename'][0]['newname'];
		$_POST['width'] = $uploaded['filename'][0]['width'];
		$_POST['height'] = $uploaded['filename'][0]['height'];
	} else {
		$submit_error = "No file uploaded";
		unset($_POST['MM_insert']);
	}
}// end post

if (isset($photos_approval) && $photos_approval == true && $_SESSION['MM_UserGroup'] < 8 && (isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) { //notify admin if doc needs approval
mysql_select_db($database_aquiescedb, $aquiescedb);
$query = "SELECT * FROM preferences";
$rsPreferences = mysql_query($query, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$to = $row_rsPreferences['contactemail'];
$subject = $site_name." uploaded photo for approval";
$message = "A photo has been uploaded to the ".$site_name." web site that required administrator approval to be displayed.\n\n";
$message .= "The photo is: ".$_POST['title']."\n\n";
$message .= "Uploaded by: ".$_POST['firstname']." ".$_POST['surname']."\n\n";
require_once('../../mail/includes/sendmail.inc.php');
sendMail($to,$subject,$message);
}



if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
  $insertSQL = sprintf("INSERT INTO photos (userID, imageURL, width, height, title, `description`, active, categoryID, createddatetime, linkURL) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['postedbyID'], "int"),
                       GetSQLValueString($_POST['imageURL'], "text"),
                       GetSQLValueString($_POST['width'], "int"),
                       GetSQLValueString($_POST['height'], "int"),
                       GetSQLValueString($_POST['title'], "text"),
                       GetSQLValueString($_POST['summary'], "text"),
                       GetSQLValueString($_POST['statusID'], "int"),
                       GetSQLValueString($_POST['categoryID'], "int"),
                       GetSQLValueString($_POST['createddatetime'], "date"),
                       GetSQLValueString($_POST['linkURL'], "text"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($insertSQL, $aquiescedb) or die(mysql_error());
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
	$update = "UPDATE photos SET ordernum = ".mysql_insert_id()." WHERE ID = ".mysql_insert_id();
			 mysql_query($update, $aquiescedb) or die(mysql_error());
  $insertGoTo = "/photos/gallery/index.php?galleryID=".intval($_POST['categoryID']);
  
  
  header(sprintf("Location: %s", $insertGoTo)); exit;
}




$colname_rsLoggedIn = "1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID, usertypeID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

$varRegionID_rsPhotoCategory = "1";
if (isset($regionID)) {
  $varRegionID_rsPhotoCategory = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPhotoCategory = sprintf("SELECT * FROM photocategories WHERE active = 1 AND regionID = %s ORDER BY categoryname ASC", GetSQLValueString($varRegionID_rsPhotoCategory, "int"));
$rsPhotoCategory = mysql_query($query_rsPhotoCategory, $aquiescedb) or die(mysql_error());
$row_rsPhotoCategory = mysql_fetch_assoc($rsPhotoCategory);
$totalRows_rsPhotoCategory = mysql_num_rows($rsPhotoCategory);


?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Add Picture"; echo $pageTitle." | ".$site_name; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script src="../../SpryAssets/SpryValidationTextField.js"></script>
<link href="../../SpryAssets/SpryValidationTextField.css" rel="stylesheet"  />
<script src="../../SpryAssets/SpryValidationSelect.js"></script>
<link href="../../SpryAssets/SpryValidationSelect.css" rel="stylesheet"  />
<script src="/core/scripts/formUpload.js"></script>
<script src="../../SpryAssets/SpryValidationCheckbox.js"></script>
<style>
<?php if ($row_rsMediaPrefs['uploadpermissioncheck']==1) { ?>
#permission {display:none; }
<?php } ?>
<?php if ($row_rsMediaPrefs['allowlinks']!=1) { ?>
.link {display:none; }
<?php } ?>
</style>
<link href="../../SpryAssets/SpryValidationCheckbox.css" rel="stylesheet"  />
<link href="../css/defaultGallery.css" rel="stylesheet"  />
<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" -->
    <h1>Add your pictures...</h1>
    <?php $uploadlevel = isset($uploadlevel) ? $uploadlevel : 1;
	if (isset($_SESSION['MM_UserGroup']) && $_SESSION['MM_UserGroup'] >= $uploadlevel) { // OK to upload?>
    <h2>Adding a picture is easy - just follow the next 3 steps:</h2>
    <?php if(isset($submit_error)) { ?>
    <p class="alert alert-danger" role="alert"><?php echo $submit_error; ?></p>
    <?php } ?>
    <form action="<?php echo $editFormAction; ?>" method="post" enctype="multipart/form-data" name="form1" id="form1">
      <ol>
        <li>Have a  picture in JPEG, GIF or PNG file format on
          your computer. <?php if(defined("FTP_HOST") && FTP_HOST !="") { 				$handler = "/photos/members/upload_handler.php";
		  $handler .= (isset($_GET['galleryID'])) ? "?galleryID=".intval($_GET['galleryID']) : ""; ?><a href="/documents/members/upload/index.php?handler_url=<?php echo urlencode($handler); ?>">Multiple images?</a><?php } ?></li>
        <li>Enter the details below. Use the <strong>Browse</strong> button to locate the picture on your computer.</li>
        <table class="form-table"> <tr>
            <td class="text-nowrap text-right">Gallery:</td>
            <td><span id="spryselect1">
              <select name="categoryID"  id="categoryID">
                <option value=""><?php echo isset($region['text_choose']) ? htmlentities($region['text_choose'], ENT_COMPAT, "UTF-8") : "Choose..." ?></option>
                <option value="0" <?php if (!(strcmp(0, @$_REQUEST['galleryID']))) {echo "selected=\"selected\"";} ?>>None</option>
                <?php
do {  
?>
                <option value="<?php echo $row_rsPhotoCategory['ID']?>"<?php if (!(strcmp($row_rsPhotoCategory['ID'], @$_REQUEST['galleryID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsPhotoCategory['categoryname']?></option>
                <?php
} while ($row_rsPhotoCategory = mysql_fetch_assoc($rsPhotoCategory));
  $rows = mysql_num_rows($rsPhotoCategory);
  if($rows > 0) {
      mysql_data_seek($rsPhotoCategory, 0);
	  $row_rsPhotoCategory = mysql_fetch_assoc($rsPhotoCategory);
  }
?>
              </select>
            <span class="selectRequiredMsg">Please select an item.</span></span><a href="galleries/add_gallery.php" title="Start a new gallery"><img src="../../core/images/icons/add.png" alt="Start a new gallery" width="16" height="16" style="vertical-align:
middle;" /></a></td>
          </tr> <tr>
            <td class="text-nowrap text-right">Picture:</td>
            <td><input type="hidden" name="MAX_FILE_SIZE" value="<?php echo defined("MAX_UPLOAD") ? MAX_UPLOAD : "2000000"; ?>" />
              <span id="sprytextfield2"><input name="filename" type="file" class="fileinput" id="filename" size="20" /><span class="textfieldRequiredMsg">A picture is required.</span></span></td>
          </tr> <tr>
            <td class="text-nowrap text-right">Title:</td>
            <td><span id="sprytextfield1">
              <input name="title" type="text"  value="<?php echo isset($_POST['title']) ? htmlentities($_POST['title']) : "Untitled"; ?>" size="50" maxlength="50" />
            <span class="textfieldRequiredMsg">A title is required.</span></span></td>
          </tr>
          <tr style="display:none;">
            <td class="text-nowrap text-right top" >Description:</td>
            <td><textarea name="summary" cols="50" rows="5"><?php echo isset($_POST['description']) ? htmlentities($_POST['description']) : ""; ?> </textarea></td>
          </tr><tr class="link">
      <td class="text-nowrap text-right top"><label for="linkURL">Link:</label></td>
      <td>
        <input name="linkURL" type="text" id="linkURL" size="50" maxlength="255" value="<?php echo isset($_POST['linkURL']) ? htmlentities($_POST['linkURL']) : ""; ?>" /></td>
    </tr>
        </table>
        <input type="hidden" name="height" id="height" />
        <input type="hidden" name="width" id="width" />
        <input name="imageURL" type="hidden" id="imageURL" />
        <input name="createddatetime" type="hidden" id="createddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
        <input type="hidden" name="postedbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
        <input name="referrer" type="hidden" id="referrer" value="<?php echo $_SERVER['HTTP_REFERER']; ?>" />
        <input type="hidden" name="MM_insert" value="form1" />
        <input name="statusID" type="hidden" id="statusID" value="<?php echo ($row_rsMediaPrefs['uploadapprove']==1 && $row_rsLoggedIn['usertypeID'] < 8) ? "0" : "1"; ?>" />
        <?php if ($row_rsMediaPrefs['uploadapprove']==1  && $row_rsLoggedIn['usertypeID'] < 8) { ?>
        <p>Photos will be reviewed by an administrator before they are displayed.</p>
        <?php } ?>
        <li>Click on the &quot;Add Picture&quot; button below  and WAIT.<br />
          Once the photo has uploaded, you will be taken back to the photos page. <br />
          (It may take a while for your photo to upload to the site)</li>
      </ol>
      
      <p id="permission"><span id="sprycheckbox1">
        <label>
          <input name="permission" type="checkbox" id="permission2" value="1" <?php if ($row_rsMediaPrefs['uploadpermissioncheck']!=1) { echo "checked=\"checked\"";} ?>
 />
          I have permission to use this image. </label>
      <span class="checkboxRequiredMsg">Please ensure you have permission.</span></span></p>
     
      <div><input type="submit" class="button" value="Add Picture" /></div>
    </form>
    <?php } else { // Not OK to upload?>
    <p class="alert warning alert-warning" role="alert">You do not have access to upload. Try logging in.</p>
    <?php } ?>
    
    <script>
<!--
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1");
var spryselect1 = new Spry.Widget.ValidationSelect("spryselect1");
var sprytextfield2 = new Spry.Widget.ValidationTextField("sprytextfield2");
var sprycheckbox1 = new Spry.Widget.ValidationCheckbox("sprycheckbox1");
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

mysql_free_result($rsPhotoCategory);

mysql_free_result($rsMediaPrefs);
?>
