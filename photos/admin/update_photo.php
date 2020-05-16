<?php require_once('../../Connections/aquiescedb.php'); ?><?php require_once('../../core/includes/adminAccess.inc.php'); ?><?php require_once('../../core/includes/framework.inc.php'); ?>
<?php require_once('../../core/includes/autolinks.inc.php'); ?><?php require_once('../includes/galleryfunctions.inc.php'); 
require_once('../../core/includes/upload.inc.php'); ?>
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
			$submit_error = $uploaded['filename2'][0]['error'];
			unset($_POST['MM_update']);
		} else if (isset($uploaded['filename2']) && is_array($uploaded['filename2'][0]) && isset($uploaded['filename2'][0]['newname'])) {
			$_POST['videoURL'] = $uploaded['filename2'][0]['newname'];
		} 
	
}// end post


if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE photos SET imageURL=%s, width=%s, height=%s, title=%s, `description`=%s, active=%s, linkURL=%s, categoryID=%s, modifiedbyID=%s, modifieddatetime=%s, latitude=%s, longitude=%s, videoURL=%s WHERE ID=%s",
                       GetSQLValueString($_POST['imageURL'], "text"),
                       GetSQLValueString($_POST['width'], "int"),
                       GetSQLValueString($_POST['height'], "int"),
                       GetSQLValueString($_POST['title'], "text"),
                       GetSQLValueString($_POST['summary'], "text"),
                       GetSQLValueString($_POST['status'], "int"),
                       GetSQLValueString($_POST['linkURL'], "text"),
                       GetSQLValueString($_POST['categoryID'], "int"),
                       GetSQLValueString($_POST['modifiedbyID'], "int"),
                       GetSQLValueString($_POST['createddatetime'], "date"),
                       GetSQLValueString($_POST['latitude'], "double"),
                       GetSQLValueString($_POST['longitude'], "double"),
                       GetSQLValueString($_POST['videoURL'], "text"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
	if(isset($_POST['formaction']) && $_POST['formaction']!="") { // is something to do
		$msg  = "";
		if(preg_match("/(.jpeg$|.jpg$|.gif$|.png$)/i",$_POST['imageURL']) && isset($image_sizes)) { 	
			$image_file = UPLOAD_ROOT.$_POST['imageURL'];	
			
			if($_POST['formaction'] == "rotateclockwise" || $_POST['formaction'] == "rotateanticlockwise") {			
				if (preg_match("/png$/i",$image_file)) {
					$image = imagecreatefrompng($image_file); 
				} else if (preg_match("/gif$/i",$image_file)) {
					$image = imagecreatefromgif($image_file); 
				} else {
					$image = imagecreatefromjpeg($image_file); 
				}
				
				if($_POST['formaction'] == "rotateclockwise") {				
					$image = imagerotate($image, -90,0);
				}
				if($_POST['formaction'] == "rotateanticlockwise") {				
					$image = imagerotate($image, 90,0);
				}
				
				if (preg_match("/png$/i",$image_file)) {
					imagepng($image,$image_file);
				} else if (preg_match("/gif$/i",$image_file)) {
					imagegif($image,$image_file);
				} else {
					imagejpeg($image,$image_file);
				}		
				imagedestroy($image);
			} // end rotate
			
			if($_POST['formaction'] == "crop") {				
				if(isset($_POST['x']) && intval($_POST['x'])>0 && isset($_POST['ratio']) && floatval($_POST['ratio'])>0) { // crop

					$original = UPLOAD_ROOT."o_".$_POST['imageURL'];
					if(!is_readable($original)) { 
					// create original before cropping
						saveFile($original,"");
						copy($image_file, $original);
					}
					$ratio  = floatval($_POST['ratio']);
					$w = $ratio * intval($_POST['w']);
					$h = $ratio * intval($_POST['h']);
					$x = $ratio * intval($_POST['x']);
					$y = $ratio * intval($_POST['y']);
					
					$size = $w."x".$h.":".$x.":".$y;
					$msg .= "Image cropped to ".$size." ";
					Image($image_file, "crop", $size, $image_file);
					
				} // end is crop values
			}// end crop
			if($_POST['formaction'] == "uncrop") {	
				copy(UPLOAD_ROOT."o_".$_POST['imageURL'], UPLOAD_ROOT.$_POST['imageURL']);
				unlink(UPLOAD_ROOT."o_".$_POST['imageURL']);
			} // uncrop
			
			
			// always resample
			createImageSizes($image_file);
			$msg .= "Image has been resampled";					
		} // end is an image
	}// end is action
	
	else {
  		$updateGoTo = isset($_GET['returnURL']) ? $_GET['returnURL'] : "index.php";
  		header("Location:". $updateGoTo); exit;
	} // no action
} // is post

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
$query_rsPhotoCategory = "SELECT * FROM photocategories WHERE active !=2 ORDER BY categoryname ASC";
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
$query_rsOtherGalleries = sprintf("SELECT photoincategory.ID, photocategories.categoryname FROM photos LEFT JOIN photoincategory ON (photos.ID = photoincategory.photoID) LEFT JOIN photocategories ON  (photocategories.ID = photoincategory.categoryID) WHERE photoincategory.categoryID != photos.categoryID AND photos.ID = %s", GetSQLValueString($varPhotoID_rsOtherGalleries, "int"));
$rsOtherGalleries = mysql_query($query_rsOtherGalleries, $aquiescedb) or die(mysql_error());
$row_rsOtherGalleries = mysql_fetch_assoc($rsOtherGalleries);
$totalRows_rsOtherGalleries = mysql_num_rows($rsOtherGalleries);



// get using PHP rather than database to correct any old values
list($width, $height, $type, $attr)= getimagesize(UPLOAD_ROOT.$row_rsPhoto['imageURL']);

if(function_exists("exif_read_data")) {

$exif =  @exif_read_data(UPLOAD_ROOT.$row_rsPhoto['imageURL']); 


$latitude = isset($row_rsPhoto['latitude']) ? $row_rsPhoto['latitude'] : (isset($exif['GPSLatitudeRef']) ? decimalLatLong($exif['GPSLatitude'][0],$exif['GPSLatitude'][1],$exif['GPSLatitude'][2],$exif['GPSLatitudeRef']) : "");
}

$longitude = isset($row_rsPhoto['longitude']) ? $row_rsPhoto['longitude'] : (isset($exif['GPSLongitudeRef']) ? decimalLatLong($exif['GPSLongitude'][0],$exif['GPSLongitude'][1],$exif['GPSLongitude'][2],$exif['GPSLongitudeRef']) : "");


function decimalLatLong($deg, $min, $sec, $hem) 
{
    $d = $deg + ((($min/60) + ($sec/3600))/100);
    return ($hem=='S' || $hem=='W') ? $d*=-1 : $d;
}

 ?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Update Media"; echo $pageTitle." | ".$site_name; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" --><script src="../../3rdparty/jquery/jquery.jcrop/js/jquery.Jcrop.js"></script>
<script src="../../SpryAssets/SpryTabbedPanels.js"></script>
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
 </script>
<link href="../../3rdparty/jquery/jquery.jcrop/css/jquery.Jcrop.css" rel="stylesheet"  /><script src="../../SpryAssets/SpryValidationTextField.js"></script>
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
<link href="../../SpryAssets/SpryTabbedPanels.css" rel="stylesheet" >


<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" --><div class="pageBody updatephoto">
<h1><i class="glyphicon glyphicon-picture"></i>  Update Media </h1>
<?php require_once('../../core/includes/alert.inc.php'); ?>
<?php $uploadlevel = isset($uploadlevel) ? $uploadlevel : 1;
if ($row_rsPhoto['userID'] == $row_rsLoggedIn['ID'] && isset($_SESSION['MM_UserGroup']) && $_SESSION['MM_UserGroup'] >= $uploadlevel || $row_rsLoggedIn['usertypeID'] >= 9) { // authorised ?>
<form action="<?php echo $editFormAction; ?>" method="post" enctype="multipart/form-data" name="form1" id="form1">
<div id="TabbedPanels1" class="TabbedPanels">
  <ul class="TabbedPanelsTabGroup">
    <li class="TabbedPanelsTab" tabindex="0">General</li>
    <li class="TabbedPanelsTab" tabindex="0">Map</li>
    <li class="TabbedPanelsTab" id="tabMaps" tabindex="0">Sizes</li>
    <li class="TabbedPanelsTab" tabindex="0">Exif (Admin)</li>
  </ul>
  <div class="TabbedPanelsContentGroup">
    <div class="TabbedPanelsContent">
     
        <table class="form-table">
          <tr>
            <td class="text-right text-nowrap">&nbsp;</td>
            <td><?php $preview_size= "medium"; if (isset($row_rsPhoto['imageURL'])) { ?>
              <img src="<?php echo getImageURL($row_rsPhoto['imageURL'],$preview_size,"",true); ?>" class="croppable large"  /><br />
              
              <?php if(isset($_SESSION['MM_UserGroup']) && $_SESSION['MM_UserGroup'] >=9) { ?>
              <button  name="deletebutton" id="deletebutton" type="submit" class="btn btn-default btn-secondary"  onclick="if(confirm('Are you sure you want to permanently delete this photo?')) { document.getElementById('formaction').value='delete'; return true; } else { return false;}"  ><span class="glyphicon glyphicon-trash"></span> Delete</button>
             <button type="submit"  class="btn btn-default btn-secondary" onclick="document.getElementById('formaction').value='resample';"><span class="glyphicon glyphicon-refresh"></span> Resample</button>
              <?php } ?>
              <button type="submit" class="btn btn-default btn-secondary" name="rotateclockwise" id="rotateclockwise"  onclick="document.getElementById('formaction').value='rotateclockwise';" ><span class="glyphicon glyphicon-repeat" ></span> Rotate clockwise</button>
              
              <button type="submit" class="btn btn-default btn-secondary"  name="rotateanticlockwise" id="rotateanticlockwise" onclick="document.getElementById('formaction').value='rotateanticlockwise';"  ><span class="glyphicon glyphicon-repeat" style="-moz-transform: scaleX(-1);    -webkit-transform: scaleX(-1);    -o-transform: scaleX(-1);    transform: scaleX(-1);    -ms-filter: fliph; filter: fliph;"></span> Rotate antoclockwise</button>
              
              <button type="submit" data-toggle = "tooltip" title="To crop, drag your mouse over the image to size then click this button" class="btn btn-default btn-secondary" onclick="document.getElementById('formaction').value='crop';" name="crop" id="crop"   ><i class="glyphicon glyphicon-scissors"></i> Crop</button>
              
              
              <?php if(is_readable(UPLOAD_ROOT."o_".$row_rsPhoto['imageURL'])) { ?>
              
              <button type="submit" data-toggle = "tooltip"  class="btn btn-default btn-secondary" onclick="document.getElementById('formaction').value='uncrop';" name="crop" id="crop"   ><i class="glyphicon glyphicon-fullscreen"></i> Unrop</button>
              
              
             
              <?php } ?>
              
              <?php } ?>
              <input type="hidden" name="x" id="x" />
              <input type="hidden" name="x2" id="x2" />
              <input type="hidden" name="y" id="y" />
              <input type="hidden" name="y2" id="y2" />
              <input type="hidden" name="w" id="w" />
              <input type="hidden" name="h" id="h" />
              <input type="hidden" name="ratio" id="ratio" value="<?php echo floatval($width/$image_sizes[$preview_size]['width']); ?>" /></td>
          </tr><tr>
            <td class="text-right text-nowrap">Main Gallery:</td>
            <td><span id="spryselect1">
              <select name="categoryID"  id="categoryID" class="form-control" onChange="if(this.value=='-1') { if(confirm('Add new gallery?'))  { document.location.href='galleries/add_gallery.php';}}">
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
?><option value="" disabled></option><option value="-1">Add new gallery...</option>
              </select>
              <span class="selectRequiredMsg">Please select an item.</span></span>
          </tr>
          <tr class="othergalleries">
            <td class="text-right top text-nowrap">Additional galleries:</td>
            <td><select name="altcategoryID"  id="altcategoryID" class="form-control">
              <option value="" ><?php echo isset($region['text_choose']) ? htmlentities($region['text_choose'], ENT_COMPAT, "UTF-8") : "Choose (optional)..." ?></option>
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
                <li><?php echo $row_rsOtherGalleries['categoryname']; ?> <a href="update_photo.php?photoID=<?php echo $row_rsPhoto['ID']; ?>&deleteID=<?php echo $row_rsOtherGalleries['ID']; ?>&token=<?php echo md5(PRIVATE_KEY.$row_rsOtherGalleries['ID']); ?>" class="btn btn-sm btn-default btn-secondary" >Delete</a></li>
                <?php } while ($row_rsOtherGalleries = mysql_fetch_assoc($rsOtherGalleries)); ?>
              </ul>
              <?php } // Show if recordset not empty ?></td>
          </tr>
          <tr>
            <td class="text-right text-nowrap">Title:</td>
            <td><span id="sprytextfield1">
              <input name="title" type="text" class="form-control" value="<?php echo htmlentities($row_rsPhoto['title'], ENT_COMPAT, "UTF-8"); ?>" size="50" maxlength="50" />
              <span class="textfieldRequiredMsg">A title is required.</span></span></td>
          </tr>
          
          <tr>
            <td class="text-right top text-nowrap">Description:</td>
            <td><textarea name="summary" class="form-control" cols="50" rows="5"><?php echo htmlentities($row_rsPhoto['description'], ENT_COMPAT, "UTF-8"); ?></textarea></td>
          </tr>
          <tr  class="link">
            <td class="text-right top text-nowrap"><label for="linkURL">Link:</label></td>
            <td><input name="linkURL" type="text" id="linkURL" size="50" maxlength="255" class="form-control" value="<?php echo htmlentities($row_rsPhoto['linkURL']); ?>" /></td>
          </tr>
          <tr  <?php echo ($row_rsMediaPrefs['uploadapprove']==1 && $row_rsPhoto['active'] != 1 && $_SESSION['MM_UserGroup'] <8 ) ? "style = 'display:none;'" : "";  ?>>
            <td class="text-right text-nowrap">Status:</td>
            <td><select name="status"  id="status"
    class="form-control"
>
              <?php
do {  
?>
              <option value="<?php echo $row_rsStatus['ID']?>"<?php if (!(strcmp($row_rsStatus['ID'], $row_rsPhoto['active']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsStatus['description']?></option>
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
            <td class="text-right text-nowrap">Replace photo file:</td>
            <td>
            <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo defined("MAX_UPLOAD") ? MAX_UPLOAD : "2000000"; ?>" />
              <input name="filename" type="file" class="fileinput" id="filename" size="20" /></td>
          </tr>
          
          <tr <?php echo !isset($row_rsPhoto['videoURL']) ? "style = 'display:none;'" : "";  ?>>
            <td class="text-right text-nowrap">Replace video file:</td>
            <td><input type="hidden" name="videoURL" value="<?php echo $row_rsPhoto['videoURL']; ?>" />
              <input name="filename2" type="file" class="fileinput" id="filename2" size="20" /></td>
          </tr>
          
          
          <tr  id="rowPermission">
            <td>&nbsp;</td>
            <td><span id="sprycheckbox1">
              <label>
                <input name="permission" type="checkbox" id="permission" value="1" <?php if ($row_rsMediaPrefs['uploadpermissioncheck']!=1) { echo "checked=\"checked\"";} ?>/>
                I have permission to use this image. </label>
              <span class="checkboxRequiredMsg">Please ensure you have permission.</span></span></td>
          </tr>
          
          
        </table>
       
        <input name="modifiedbyID" type="hidden" id="modifiedbyID" value="<?php echo $row_rsPhoto['userID']; ?>" />
        <input type="hidden" name="MM_update" value="form1" />
        <input type="hidden" name="ID" value="<?php echo $row_rsPhoto['ID']; ?>" />
        <input name="createddatetime" type="hidden" id="createddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
        <input name="height" type="hidden" id="height" value="<?php echo $height; ?>" />
        <input name="width" type="hidden" id="width" value="<?php echo $width; ?>" />
    
    </div>
    <div class="TabbedPanelsContent"> <?php if(is_readable(SITE_ROOT.'location/admin/includes/googlemap.inc.php')) { require_once('../../location/admin/includes/googlemap.inc.php');
	}?>
              </div>
    <div class="TabbedPanelsContent">
      <?php foreach($image_sizes as $image_size => $value) { ?>
      <h3><?php echo ucwords($image_size).":"; ?></h3>
      <img src="<?php echo getImageURL($row_rsPhoto['imageURL'], $image_size,"",true); ?>" class="<?php echo $image_size; ?>"><br>
      <?php  } ?>
    </div>
    <div class="TabbedPanelsContent"><pre><?php print_r($exif); ?></pre></div>
  </div>
</div> <p>Maximum upload size on this server is: <?php  echo ini_get('upload_max_filesize')>ini_get('post_max_size') ? ini_get('post_max_size') : ini_get('upload_max_filesize'); ?></p><input name="referrer" type="hidden" id="referrer" value="<?php echo $_SERVER['HTTP_REFERER']; ?>" />
              <input type="hidden" name="imageURL" value="<?php echo $row_rsPhoto['imageURL']; ?>" /><button type="submit" class="btn btn-primary"><span class="glyphicon glyphicon-save"></span> Save changes</button>
              
              
              <input type="hidden" name="formaction" id="formaction" />
              <input name="uploadID" type="hidden" id="uploadID" value="<?php echo $row_rsPhoto['uploadID']; ?>" /> </form>
 <?php } else { //unauthorised ?>
<p class="alert warning alert-warning" role="alert">Sorry, you are not able to alter this photograph. Please try <a href="../../login/index.php?accesscheck=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>">logging in</a>.</p> 
<?php } ?>
<p><a href="javascript:history.go(-1);" class="link_back"><i class="glyphicon glyphicon-arrow-left"></i> Go back</a></p></div>
<script>
<!--
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1");
var spryselect1 = new Spry.Widget.ValidationSelect("spryselect1");
var sprycheckbox1 = new Spry.Widget.ValidationCheckbox("sprycheckbox1");
var TabbedPanels1 = new Spry.Widget.TabbedPanels("TabbedPanels1");
//-->
</script>
  <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../core/includes/adminFooter.inc.php'); ?>
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
