<?php require_once('../../Connections/aquiescedb.php'); ?><?php require_once('../../core/includes/autolinks.inc.php'); ?><?php require_once('../includes/galleryfunctions.inc.php'); ?>
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

$varRegionID_rsPhotoCategory = "1";
if (isset($regionID)) {
  $varRegionID_rsPhotoCategory = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPhotoCategory = sprintf("SELECT * FROM photocategories WHERE active = 1 AND regionID = %s ORDER BY categoryname ASC", GetSQLValueString($varRegionID_rsPhotoCategory, "int"));
$rsPhotoCategory = mysql_query($query_rsPhotoCategory, $aquiescedb) or die(mysql_error());
$row_rsPhotoCategory = mysql_fetch_assoc($rsPhotoCategory);
$totalRows_rsPhotoCategory = mysql_num_rows($rsPhotoCategory);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsMediaPrefs = "SELECT * FROM mediaprefs";
$rsMediaPrefs = mysql_query($query_rsMediaPrefs, $aquiescedb) or die(mysql_error());
$row_rsMediaPrefs = mysql_fetch_assoc($rsMediaPrefs);
$totalRows_rsMediaPrefs = mysql_num_rows($rsMediaPrefs);

$colname_rsLoggedIn = "1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID, usertypeID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsStatus = "SELECT * FROM status";
$rsStatus = mysql_query($query_rsStatus, $aquiescedb) or die(mysql_error());
$row_rsStatus = mysql_fetch_assoc($rsStatus);
$totalRows_rsStatus = mysql_num_rows($rsStatus);

if(isset($_POST['createddatetime'])) { // post
	if($_POST['categoryID'] == -1) { // new category
		
		
	 $galleryID = addGallery($_POST['newcategory'], $_POST['postedbyID']);
	}
	if(isset($_POST['photo']) && ($row_rsMediaPrefs['uploadpermissioncheck'] == 0 || $_POST['permission']==1)) {
		mysql_select_db($database_aquiescedb, $aquiescedb);
		$galleryID = isset($galleryID) ? $galleryID : $_POST['categoryID'];
		foreach($_POST['photo'] as $key => $value) {
			$_POST['description'][$key] = addLinks($_POST['description'][$key]);
			$_POST['title'][$key] = (isset($_POST['title'][$key]) && $_POST['title'][$key]!="") ? $_POST['title'][$key]: "Untitled";
			$photoID = addPhoto( $value,  $_POST['title'][$key], $_POST['description'][$key], $_POST['linkURL'][$key], $_POST['postedbyID'], $_POST['statusID'], $_POST['width'][$key], $_POST['height'][$key]);
			addPhotoToGallery($photoID,$galleryID,$_POST['postedbyID']);
		}
		session_regenerate_id(); // so that folder is no longer used
		header("location: /photos/gallery/index.php?galleryID=".$galleryID); exit;
	}
	
}
?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Review your photos"; echo $pageTitle." | ".$site_name; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<link href="../css/defaultGallery.css" rel="stylesheet"  />
<script src="../../SpryAssets/SpryValidationCheckbox.js"></script>
<script src="../../SpryAssets/SpryValidationSelect.js"></script>
<link href="../../SpryAssets/SpryValidationCheckbox.css" rel="stylesheet"  />
<style><!--
<?php if ($row_rsMediaPrefs['uploadpermissioncheck']==1) { ?>
#permission {display:none; }
<?php } ?>
<?php if ($row_rsMediaPrefs['allowlinks']!=1) { ?>
.link {display:none !important; }
<?php } ?>
--></style>
<script>
addListener("load", toggleCategory);
function toggleCategory() {
	if(document.getElementById('categoryID').value == -1) {
		document.getElementById('newcategory').style.display = 'inline';
	} else {
		document.getElementById('newcategory').style.display = 'none';
	}
}</script>
<link href="../../SpryAssets/SpryValidationSelect.css" rel="stylesheet"  />
<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" -->
  <div id="pagePhotoReview" style="position:relative; z-index:5000;" class="container pageBody">
    <h1>Review your pictures</h1>
    <p>Check over your upload, add any titles, descriptions and choose a gallery to add them to.</p>
    <form action="" method="post" name="form1" id="form1">
      <?php $directory = "users/".$_SESSION['MM_Username']."/".session_id()."/";
	  $prefix = date('Y').DIRECTORY_SEPARATOR.date('m').DIRECTORY_SEPARATOR.date('d').DIRECTORY_SEPARATOR; 
  $path = UPLOAD_ROOT.$directory.$prefix;  // removed dots 
  $count = 0;
  if(is_dir($path)) {
	$files = scandir($path);
	if(count($files)>2) { 
		foreach($files as $key => $value) { 
			if($value !=".") {
				$file = $path.$value; 
	 			if(is_file($file) && substr($value,0,2)=="t_") { // is thumbnail image 
					list($width, $height, $type, $attr)= getimagesize($path.substr($value,2));
					$count ++; ?>
      <div class="photoItem">
        <input name="photo[<?php echo $key; ?>]" type="checkbox" id="photo<?php echo $key; ?>" value="<?php echo $directory.$prefix.substr($value,2); ?>" checked="checked" class="photoCheckbox" />
        <input name="width[<?php echo $key; ?>]" type="hidden" value="<?php echo $width; ?>" />
        <input name="height[<?php echo $key; ?>]" type="hidden" value="<?php echo $height; ?>" />
        <div class="photoImage">
          <img src="/Uploads/<?php echo $directory.$prefix.$value; ?>"/></div>
        <div class="photoDetails">
          <label class="photoTitle" for="title[<?php echo $key; ?>]">Title:</label>
          <input name="title[<?php echo $key; ?>]" type="text" id="title<?php echo $key; ?>" value="<?php echo substr($value,11); // remove random prefix ?>" size="50" maxlength="50" />
          
          <label class="photoDescription" for="description[<?php echo $key; ?>]">Description:                </label>
          
          <textarea name="description[<?php echo $key; ?>]" id="description<?php echo $key; ?>" cols="50" rows="5"></textarea>
          
          <label class="link" for="linkURL<?php echo $key; ?>">Link:</label>
          <input name="linkURL[<?php echo $key; ?>]" type="text" id="linkURL<?php echo $key; ?>" value="" size="50" maxlength="255" class="link" />
          </div></div>
      
      
      <?php } // endis thumbnail
			} // is not dot
		} // end for each
	} // is files
  } // is path
  if($count>0) { ?>
  
      
      <p>Add selected photos to: 
        <span id="spryselect1">
        <label>
          <select name="categoryID" id="categoryID" onchange="toggleCategory()">
            <option value="" <?php if (!(strcmp("", @$_GET['galleryID']))) {echo "selected=\"selected\"";} ?>><?php echo isset($region['text_choose']) ? htmlentities($region['text_choose'], ENT_COMPAT, "UTF-8") : "Choose..." ?></option>
            <option value="-1" <?php if (!(strcmp(-1, @$_GET['galleryID']))) {echo "selected=\"selected\"";} ?>>New gallery...</option>
            <?php
do {  
?>
            <option value="<?php echo $row_rsPhotoCategory['ID']?>"<?php if (!(strcmp($row_rsPhotoCategory['ID'], @$_GET['galleryID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsPhotoCategory['categoryname']?></option>
            <?php
} while ($row_rsPhotoCategory = mysql_fetch_assoc($rsPhotoCategory));
  $rows = mysql_num_rows($rsPhotoCategory);
  if($rows > 0) {
      mysql_data_seek($rsPhotoCategory, 0);
	  $row_rsPhotoCategory = mysql_fetch_assoc($rsPhotoCategory);
  }
?>
          </select>
        </label>
        <span class="selectRequiredMsg">Please select an item.</span></span> 
       
          <input name="newcategory" type="text" id="newcategory" size="30" maxlength="50" />
       
      </p>
      <input name="createddatetime" type="hidden" id="createddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
      <input type="hidden" name="postedbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
      
      <?php if ($row_rsMediaPrefs['uploadapprove']==1  && $row_rsLoggedIn['usertypeID'] < 8) { ?>
      <p>Photos will be reviewed by an administrator before they are displayed.</p><input name="statusID" type="hidden" id="statusID" value="0" />
      <?php } else { ?><p>
      <label>Initial status: <select name="statusID" id="statusID">
                    <?php
do {  
?>
                    <option value="<?php echo $row_rsStatus['ID']?>"<?php if (!(strcmp($row_rsStatus['ID'], 1))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsStatus['description']?></option>
                    <?php
} while ($row_rsStatus = mysql_fetch_assoc($rsStatus));
  $rows = mysql_num_rows($rsStatus);
  if($rows > 0) {
      mysql_data_seek($rsStatus, 0);
	  $row_rsStatus = mysql_fetch_assoc($rsStatus);
  }
?>
                  </select> </label>&nbsp;<label><span id="permission"><span id="sprycheckbox2">
            <input name="permission" type="checkbox"  value="1" <?php if ($row_rsMediaPrefs['uploadpermissioncheck']!=1) { echo "checked=\"checked\"";} ?>
 />
            I have permission to use these images.
          <span class="checkboxRequiredMsg">Please ensure you have permission.</span></span></span> </label></p>
	  <?php }?>
      <p>
        <button type="submit" class="btn btn-primary" >Add to gallery...</button>
          
        </p>
        <?php } else { ?>
        <p class="alert warning alert-warning" role="alert">No images are available to add to a gallery. <a href="add_photos.php">Please try again</a>. <?php if($_SESSION['MM_UserGroup']==10) { ?><br><br>WEBADMIN: <?php echo $path; } ?></p>  
        <?php } ?>
      </form>
    
    <script>
var sprycheckbox2 = new Spry.Widget.ValidationCheckbox("sprycheckbox2");
    </script></div>
  <script>
var spryselect1 = new Spry.Widget.ValidationSelect("spryselect1");
  </script>
  <!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsPhotoCategory);

mysql_free_result($rsMediaPrefs);

mysql_free_result($rsLoggedIn);

mysql_free_result($rsStatus);
?>
