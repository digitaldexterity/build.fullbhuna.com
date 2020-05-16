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
$query_rsMediaPrefs = "SELECT * FROM mediaprefs";
$rsMediaPrefs = mysql_query($query_rsMediaPrefs, $aquiescedb) or die(mysql_error());
$row_rsMediaPrefs = mysql_fetch_assoc($rsMediaPrefs);
$totalRows_rsMediaPrefs = mysql_num_rows($rsMediaPrefs);

if(isset($_GET['deleteID']) && $_GET['token'] == md5(PRIVATE_KEY.$_GET['deleteID'])) {
	$delete = "DELETE FROM photoincategory WHERE ID = ".intval($_GET['deleteID']);
	mysql_select_db($database_aquiescedb, $aquiescedb);
   mysql_query($delete, $aquiescedb) or die(mysql_error());
}

if(isset($_POST['formaction']) && $_POST['formaction'] == "delete" && isset($_SESSION['MM_UserGroup']) && $_SESSION['MM_UserGroup']>=9) { 
$delete = "DELETE FROM photos WHERE ID = ".GetSQLValueString($_POST['ID'], "int");
mysql_select_db($database_aquiescedb, $aquiescedb);
   mysql_query($delete, $aquiescedb) or die(mysql_error());
   
	deleteUpload($_POST['uploadID']);
	$updateGoTo = isset($_GET['returnURL']) ? $_GET['returnURL'] : "../index.php";
  header(sprintf("Location: %s", $updateGoTo)); exit;
}


if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
	if(isset($_POST["altcategoryID"])) {
		addPhotoToGallery($_POST['ID'],$_POST["altcategoryID"],$_POST['modifiedbyID']);
	}
	require_once('../../core/includes/upload.inc.php'); 
	$_POST['summary'] = addLinks($_POST['summary']);
	if(isset($_POST['x']) && intval($_POST['x'])>0 && isset($_POST['ratio']) && floatval($_POST['ratio'])>0) { // crop
	$filename = UPLOAD_ROOT.$_POST['imageURL'];
		$original = UPLOAD_ROOT."o_".$_POST['imageURL'];
		if(!is_readable($original)) { 
			copy($filename, $original);
		}
		$ratio  = floatval($_POST['ratio']);
		$w = $ratio * intval($_POST['w']);
		$h = $ratio * intval($_POST['h']);
		$x = $ratio * intval($_POST['x']);
		$y = $ratio * intval($_POST['y']);
		
		$size = $w."x".$h.":".$x.":".$y;
		//die($filename.":".$crop.":".$size.":".$filename);
		$result = Image($filename, "crop", $size, $filename);
		createImageSizes($filename);
	} else { // no crop
		$uploaded = getUploads(UPLOAD_ROOT,$image_sizes,"",0,0,"",array("gif","png","jpeg","jpg"),"longest");
		if(isset($uploaded['filename']) && is_array($uploaded['filename'][0]) && isset($uploaded['filename'][0]['error'])) {
			$submit_error = $uploaded['filename'][0]['error'];
			unset($_POST['MM_update']);
		} else if (isset($uploaded['filename']) && is_array($uploaded['filename'][0]) && isset($uploaded['filename'][0]['newname'])) {
			$_POST['imageURL'] = $uploaded['filename'][0]['newname'];
			$_POST['width'] = @$uploaded['filename'][0]['width'];
			$_POST['height'] = @$uploaded['filename'][0]['height'];
		} 
	} // no crop 
}// end post


if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE photos SET imageURL=%s, width=%s, height=%s, title=%s, `description`=%s, active=%s, categoryID=%s, modifiedbyID=%s, modifieddatetime=%s, linkURL=%s WHERE ID=%s",
                       GetSQLValueString($_POST['imageURL'], "text"),
                       GetSQLValueString($_POST['width'], "int"),
                       GetSQLValueString($_POST['height'], "int"),
                       GetSQLValueString($_POST['title'], "text"),
                       GetSQLValueString($_POST['summary'], "text"),
                       GetSQLValueString($_POST['status'], "int"),
                       GetSQLValueString($_POST['categoryID'], "int"),
                       GetSQLValueString($_POST['modifiedbyID'], "int"),
                       GetSQLValueString($_POST['createddatetime'], "date"),
                       GetSQLValueString($_POST['linkURL'], "text"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
	if(isset($_POST['formaction']) && $_POST['formaction'] == "resample") { 
	if(preg_match("/(.jpeg$|.jpg$|.gif$|.png$)/i",$_POST['imageURL']) && isset($image_sizes)) { 	
				
					createImageSizes(UPLOAD_ROOT.$_POST['imageURL']);
										
									} // end is an image
	}// end resample
	
  $updateGoTo = isset($_GET['returnURL']) ? $_GET['returnURL'] : "../index.php";
  header("Location:". $updateGoTo); exit;
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

$colname_rsPhoto = "1";
if (isset($_GET['photoID'])) {
  $colname_rsPhoto = $_GET['photoID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPhoto = sprintf("SELECT * FROM photos WHERE ID = %s", GetSQLValueString($colname_rsPhoto, "int"));
$rsPhoto = mysql_query($query_rsPhoto, $aquiescedb) or die(mysql_error());
$row_rsPhoto = mysql_fetch_assoc($rsPhoto);
$totalRows_rsPhoto = mysql_num_rows($rsPhoto);

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

$varPhotoID_rsOtherGalleries = "-1";
if (isset($_GET['photoID'])) {
  $varPhotoID_rsOtherGalleries = $_GET['photoID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsOtherGalleries = sprintf("SELECT photoincategory.ID, photocategories.categoryname FROM photocategories LEFT JOIN photoincategory ON (photocategories.ID = photoincategory.categoryID) WHERE photoincategory.photoID = %s", GetSQLValueString($varPhotoID_rsOtherGalleries, "int"));
$rsOtherGalleries = mysql_query($query_rsOtherGalleries, $aquiescedb) or die(mysql_error());
$row_rsOtherGalleries = mysql_fetch_assoc($rsOtherGalleries);
$totalRows_rsOtherGalleries = mysql_num_rows($rsOtherGalleries);

if(isset($_GET['uncrop'])) {
	copy(UPLOAD_ROOT."o_".$row_rsPhoto['imageURL'], UPLOAD_ROOT.$row_rsPhoto['imageURL']);
	
	createImageSizes(UPLOAD_ROOT.$row_rsPhoto['imageURL']);
	unlink(UPLOAD_ROOT."o_".$row_rsPhoto['imageURL']);
}

// get using PHP rather than database to correct any old values
list($width, $height, $type, $attr)= getimagesize(UPLOAD_ROOT.$row_rsPhoto['imageURL']);
?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Update Picture"; echo $pageTitle." | ".$site_name; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" --><script src="../../3rdparty/jquery/jquery.jcrop/js/jquery.Jcrop.js"></script>
<script>

 $(function(){ $('.croppable').Jcrop({onSelect: showCoords}); });
 function showCoords(c)
{
	$('#x').val(c.x);
	$('#y').val(c.y);
	$('#x2').val(c.x2);
	$('#y2').val(c.y2);
	$('#w').val(c.w);
	$('#h').val(c.h);
};
 </script><link href="../../3rdparty/jquery/jquery.jcrop/css/jquery.Jcrop.css" rel="stylesheet"  /><script src="../../SpryAssets/SpryValidationTextField.js"></script>
<script src="../../SpryAssets/SpryValidationSelect.js"></script>
<link href="../../SpryAssets/SpryValidationTextField.css" rel="stylesheet"  />
<link href="../../SpryAssets/SpryValidationSelect.css" rel="stylesheet"  />
<script src="/core/scripts/formUpload.js"></script>
<script src="../../SpryAssets/SpryValidationCheckbox.js"></script>
<style>
<?php if ($row_rsMediaPrefs['uploadpermissioncheck']!=1) { ?>
#rowPermission {display:none; }
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
      <!-- InstanceBeginEditable name="Body" --><div class="container pageBody updatephoto">
<h1>Update Picture </h1>
<?php $uploadlevel = isset($uploadlevel) ? $uploadlevel : 1;
if ($row_rsPhoto['userID'] == $row_rsLoggedIn['ID'] && isset($_SESSION['MM_UserGroup']) && $_SESSION['MM_UserGroup'] >= $uploadlevel || $row_rsLoggedIn['usertypeID'] >= 8) { // authorised ?>
<?php if(isset($submit_error)) { ?><p class="alert alert-danger" role="alert"><?php echo $submit_error; ?></p><?php } ?>
<form action="<?php echo $editFormAction; ?>" method="post" enctype="multipart/form-data" name="form1" id="form1">
  <table class="form-table"> <tr>
	      <td class="text-right text-nowrap">&nbsp;</td>

      <td>
        <?php if (isset($row_rsPhoto['imageURL'])) { ?><img src="<?php echo getImageURL($row_rsPhoto['imageURL'],"large"); ?>" class="croppable large"  /><br />
            <?php if(is_readable(UPLOAD_ROOT."o_".$row_rsPhoto['imageURL'])) { ?>This image has been cropped. <a href="update_photo.php?photoID=<?php echo $row_rsPhoto['ID']; ?>&amp;uncrop=true">Uncrop</a>. <?php } ?>To crop, drag your mouse over the image and click save changes below.
            <?php } ?>
        <input type="hidden" name="x" id="x" />
        <input type="hidden" name="x2" id="x2" />
        <input type="hidden" name="y" id="y" />
        <input type="hidden" name="y2" id="y2" />
        <input type="hidden" name="w" id="w" />
        <input type="hidden" name="h" id="h" />
        <input type="hidden" name="ratio" id="ratio" value="<?php echo floatval($width/$image_sizes['large']['width']); ?>" />
        
        </td>
      </tr><tr>
      <td class="text-right text-nowrap">Title:</td>
      <td><span id="sprytextfield1">
        <input name="title" type="text"  value="<?php echo htmlentities($row_rsPhoto['title'], ENT_COMPAT, "UTF-8"); ?>" size="50" maxlength="50" />
        <span class="textfieldRequiredMsg">A title is required.</span></span></td>
    </tr><tr>
      <td class="text-right text-nowrap">Main Gallery:</td>
      <td><span id="spryselect1">
        <select name="categoryID"  id="categoryID">
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
        <span class="selectRequiredMsg">Please select an item.</span></span><a href="galleries/add_gallery.php">Add a gallery </a> </td>
    </tr>
    <tr class="othergalleries">
      <td class="text-right top text-nowrap">Additional galleries:</td>
      <td><select name="altcategoryID"  id="altcategoryID">
          <option value="" ><?php echo isset($region['text_choose']) ? htmlentities($region['text_choose'], ENT_COMPAT, "UTF-8") : "Choose..." ?></option>
          <?php
do {  
?>
          <option value="<?php echo $row_rsPhotoCategory['ID']?>"><?php echo $row_rsPhotoCategory['categoryname']?></option>
          <?php
} while ($row_rsPhotoCategory = mysql_fetch_assoc($rsPhotoCategory));
  $rows = mysql_num_rows($rsPhotoCategory);
  if($rows > 0) {
      mysql_data_seek($rsPhotoCategory, 0);
	  $row_rsPhotoCategory = mysql_fetch_assoc($rsPhotoCategory);
  }
?>
        </select>
        <?php if ($totalRows_rsOtherGalleries > 0) { // Show if recordset not empty ?>
 <ul>
    <?php do { ?>
      <li><?php echo $row_rsOtherGalleries['categoryname']; ?> <a href="update_photo.php?photoID=<?php echo $row_rsPhoto['ID']; ?>&deleteID=<?php echo $row_rsOtherGalleries['ID']; ?>&token=<?php echo md5(PRIVATE_KEY.$row_rsOtherGalleries['ID']); ?>">Delete</a></li>
      <?php } while ($row_rsOtherGalleries = mysql_fetch_assoc($rsOtherGalleries)); ?>
</ul>
  <?php } // Show if recordset not empty ?>      </td>
    </tr> 
    <tr>
      <td class="text-right top text-nowrap">Description:</td>
      <td><textarea name="summary" cols="50" rows="5"><?php echo htmlentities($row_rsPhoto['description'], ENT_COMPAT, "UTF-8"); ?></textarea>
      </td>
    </tr>
    <tr  class="link">
      <td class="text-right top text-nowrap"><label for="linkURL">Link:</label></td>
      <td>
        <input name="linkURL" type="text" id="linkURL" size="50" maxlength="255" value="<?php echo htmlentities($row_rsPhoto['linkURL']); ?>" /></td>
    </tr>
   <tr  <?php echo ($row_rsMediaPrefs['uploadapprove']==1 && $row_rsPhoto['active'] != 1 && $_SESSION['MM_UserGroup'] <8 ) ? "style = 'display:none;'" : "";  ?>>
      <td class="text-right text-nowrap">Status:</td>
      <td><select name="status"  id="status"
    
>          <?php
do {  
?><option value="<?php echo $row_rsStatus['ID']?>"<?php if (!(strcmp($row_rsStatus['ID'], $row_rsPhoto['active']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsStatus['description']?></option>
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
   <tr   <?php echo ($row_rsMediaPrefs['uploadapprove']==1 && $row_rsPhoto['active'] != 1 && $_SESSION['MM_UserGroup'] <8 ) ? "style = 'display:none;'" : "";  ?>>
     <td class="text-right text-nowrap">New file:</td>
     <td> <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo defined("MAX_UPLOAD") ? MAX_UPLOAD : "2000000"; ?>" />
        <input name="filename" type="file" class="fileinput" id="filename" size="20" /></td>
   </tr>
    <tr  id="rowPermission">
      <td>&nbsp;</td>
      <td><span id="sprycheckbox1">
        <label>
          <input name="permission" type="checkbox" id="permission" value="1" <?php if ($row_rsMediaPrefs['uploadpermissioncheck']!=1) { echo "checked=\"checked\"";} ?>/>
          I have permission to use this image. </label>
<span class="checkboxRequiredMsg">Please ensure you have permission.</span></span></td>
    </tr> <tr>
      <td><input name="referrer" type="hidden" id="referrer" value="<?php echo $_SERVER['HTTP_REFERER']; ?>" />
        <input type="hidden" name="imageURL" value="<?php echo $row_rsPhoto['imageURL']; ?>" size="32" /></td>
      <td><button type="submit" class="btn btn-primary" >Save changes</button>
        <?php if(isset($_SESSION['MM_UserGroup']) && $_SESSION['MM_UserGroup'] >=9) { ?>
        <button type="submit" name="deletebutton" id="deletebutton"  onclick="if(confirm('Are you sure you want to permanently delete this photo?')) { document.getElementById('formaction').value='delete'; return true; } else { return false;}" class="btn btn-default btn-secondary" >Delete</button> <button type="submit" name="deletebutton" id="deletebutton"  onclick="document.getElementById('formaction').value='resample';" class="btn btn-default btn-secondary">Resample</button><?php } ?>
        <input type="hidden" name="formaction" id="formaction" />
        <input name="uploadID" type="hidden" id="uploadID" value="<?php echo $row_rsPhoto['uploadID']; ?>" /></td>
    </tr>
  </table>
  <input name="modifiedbyID" type="hidden" id="modifiedbyID" value="<?php echo $row_rsPhoto['userID']; ?>" />
  <input type="hidden" name="MM_update" value="form1" />
  <input type="hidden" name="ID" value="<?php echo $row_rsPhoto['ID']; ?>" />
      <input name="createddatetime" type="hidden" id="createddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
  
 
        <input name="height" type="hidden" id="height" value="<?php echo $height; ?>" />
        <input name="width" type="hidden" id="width" value="<?php echo $width; ?>" />
    
</form> <?php } else { //unauthorised ?>
<p class="alert warning alert-warning" role="alert">Sorry, you are not able to alter this photograph. Please try <a href="../../login/index.php?accesscheck=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>">logging in</a>.</p> 
<?php } ?>
<p><a href="javascript:history.go(-1);" class="link_back"><i class="glyphicon glyphicon-arrow-left"></i> Go back</a></p></div>
<script>
<!--
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1");
var spryselect1 = new Spry.Widget.ValidationSelect("spryselect1");
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

mysql_free_result($rsPhoto);

mysql_free_result($rsPhotoCategory);

mysql_free_result($rsStatus);

mysql_free_result($rsOtherGalleries);

mysql_free_result($rsMediaPrefs);
?>
