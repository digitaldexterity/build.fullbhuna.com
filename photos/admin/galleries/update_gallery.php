<?php require_once('../../../Connections/aquiescedb.php'); ?>
<?php require_once('../../../core/includes/adminAccess.inc.php'); ?><?php
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

if(isset($_POST['formaction']) && $_POST['formaction'] == "delete") {
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$select = "SELECT ID FROM photos WHERE categoryID = ".GetSQLValueString($_POST['ID'], "int");	
	$result = mysql_query($select, $aquiescedb) or die(mysql_error());
	if(mysql_num_rows($result)>0) {
		while($row = mysql_fetch_assoc($result)) {
			deletePhoto($row['ID']);
		}
	}
	
	$delete = "DELETE FROM photocategories WHERE ID = ".GetSQLValueString($_POST['ID'], "int");	
	mysql_query($delete, $aquiescedb) or die(mysql_error());
	
	$delete = "DELETE FROM photoincategory WHERE categoryID = ".GetSQLValueString($_POST['ID'], "int");	
	mysql_query($delete, $aquiescedb) or die(mysql_error());
  	
	$updateGoTo = "/photos/admin/galleries/";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo)); exit;
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE photocategories SET categoryname=%s, categorydate=%s, coverphotoID=%s, `description`=%s, categoryofID=%s, accesslevel=%s, groupID=%s, active=%s, modifiedbyID=%s, modifieddatetime=%s, soundtrackURL=%s, regionID=%s WHERE ID=%s",
                       GetSQLValueString($_POST['categoryname'], "text"),
                       GetSQLValueString($_POST['categorydate'], "date"),
                       GetSQLValueString($_POST['coverphotoID'], "int"),
                       GetSQLValueString($_POST['description'], "text"),
                       GetSQLValueString($_POST['categoryofID'], "int"),
                       GetSQLValueString($_POST['accesslevel'], "int"),
                       GetSQLValueString($_POST['groupID'], "int"),
                       GetSQLValueString($_POST['statusID'], "int"),
                       GetSQLValueString($_POST['modifiedbyID'], "int"),
                       GetSQLValueString($_POST['modifieddatetime'], "date"),
                       GetSQLValueString($_POST['soundtrackURL'], "text"),
                       GetSQLValueString($_POST['regionID'], "int"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateGoTo = "/photos/admin/galleries/";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo)); exit;
}

$colname_rsPhotoCategory = "-1";
if (isset($_GET['galleryID'])) {
  $colname_rsPhotoCategory = $_GET['galleryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPhotoCategory = sprintf("SELECT photocategories.* , users.username FROM photocategories LEFT JOIN users ON (photocategories.addedbyID = users.ID) WHERE photocategories.ID = %s", GetSQLValueString($colname_rsPhotoCategory, "int"));
$rsPhotoCategory = mysql_query($query_rsPhotoCategory, $aquiescedb) or die(mysql_error());
$row_rsPhotoCategory = mysql_fetch_assoc($rsPhotoCategory);
$totalRows_rsPhotoCategory = mysql_num_rows($rsPhotoCategory);

$varMinAccessLevel_rsAccessLevel = "1";
if (isset($accesslevel)) {
  $varMinAccessLevel_rsAccessLevel = $accesslevel;
}
$varMaxAccessLevel_rsAccessLevel = "0";
if (isset($_SESSION['MM_UserGroup'])) {
  $varMaxAccessLevel_rsAccessLevel = $_SESSION['MM_UserGroup'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsAccessLevel = sprintf("SELECT usertype.ID, CONCAT(usertype.name, 's') AS name FROM usertype WHERE usertype.ID >= 1 AND usertype.ID >= %s AND usertype.ID <= %s ORDER BY usertype.ID ASC", GetSQLValueString($varMinAccessLevel_rsAccessLevel, "int"),GetSQLValueString($varMaxAccessLevel_rsAccessLevel, "int"));
$rsAccessLevel = mysql_query($query_rsAccessLevel, $aquiescedb) or die(mysql_error());
$row_rsAccessLevel = mysql_fetch_assoc($rsAccessLevel);
$totalRows_rsAccessLevel = mysql_num_rows($rsAccessLevel);

$varCategoryID_rsAlbumPhotos = "0";
if (isset($_GET['galleryID'])) {
  $varCategoryID_rsAlbumPhotos = $_GET['galleryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsAlbumPhotos = sprintf("SELECT photos.ID, photos.title FROM photos WHERE photos.active = 1 AND photos.categoryID = %s", GetSQLValueString($varCategoryID_rsAlbumPhotos, "int"));
$rsAlbumPhotos = mysql_query($query_rsAlbumPhotos, $aquiescedb) or die(mysql_error());
$row_rsAlbumPhotos = mysql_fetch_assoc($rsAlbumPhotos);
$totalRows_rsAlbumPhotos = mysql_num_rows($rsAlbumPhotos);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsGroups = "SELECT ID, groupname FROM usergroup WHERE statusID = 1 ORDER BY groupname ASC";
$rsGroups = mysql_query($query_rsGroups, $aquiescedb) or die(mysql_error());
$row_rsGroups = mysql_fetch_assoc($rsGroups);
$totalRows_rsGroups = mysql_num_rows($rsGroups);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsRegions = "SELECT ID, title FROM region WHERE statusID = 1 ORDER BY title ASC";
$rsRegions = mysql_query($query_rsRegions, $aquiescedb) or die(mysql_error());
$row_rsRegions = mysql_fetch_assoc($rsRegions);
$totalRows_rsRegions = mysql_num_rows($rsRegions);

$varRegionID_rsGalleries = "1";
if (isset($regionID)) {
  $varRegionID_rsGalleries = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsGalleries = sprintf("SELECT ID, categoryname FROM photocategories WHERE active = 1 AND regionID = %s ORDER BY ordernum ASC", GetSQLValueString($varRegionID_rsGalleries, "int"));
$rsGalleries = mysql_query($query_rsGalleries, $aquiescedb) or die(mysql_error());
$row_rsGalleries = mysql_fetch_assoc($rsGalleries);
$totalRows_rsGalleries = mysql_num_rows($rsGalleries);

$varUsername_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $varUsername_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID FROM users WHERE username = %s", GetSQLValueString($varUsername_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

$regionID = isset($regionID) ? $regionID : 1;
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Update Picture Gallery"; echo $pageTitle." | ".$site_name;?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script src="../../../SpryAssets/SpryValidationTextField.js"></script>
<link href="../../../SpryAssets/SpryValidationTextField.css" rel="stylesheet"  />
<style>
<!--
<?php if(mysql_num_rows($rsGroups)<1) {
 echo ".groups { display: none; } ";
}
?> <?php if((isset($_SESSION['MM_UserGroup']) && $_SESSION['MM_UserGroup']<9 )|| $totalRows_rsRegions<2) {
 echo ".region { display: none; } ";
}
?>
-->
</style>
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
      <div class="container pageBody updatephotogallery">
        <h1><i class="glyphicon glyphicon-picture"></i> Update Picture Gallery</h1>
        <?php if ((isset($_SESSION['MM_UserGroup']) && $_SESSION['MM_UserGroup'] >= $row_rsPhotoCategory['accesslevel']) || (isset($_SESSION['MM_Username']) && $row_rsPhotoCategory['username'] == $_SESSION['MM_Username'])) { // OK to access ?>
        <?php if(isset($submit_error)) { ?>
        <p class="alert alert-danger" role="alert"><?php echo $submit_error; ?></p>
        <?php } ?>
        <form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1">
          <table class="form-table">
            <tr>
              <td align="right" valign="top">Gallery Name: </td>
              <td><span id="sprytextfield1">
                <input name="categoryname" type="text"  id="categoryname" value="<?php echo htmlentities($row_rsPhotoCategory['categoryname']); ?>" size="50" maxlength="50" class="form-control" />
                <span class="textfieldRequiredMsg">A name is required.</span></span></td>
            </tr>
            <tr>
              <td align="right" valign="top">Gallery Date:</td>
              <td><input name="categorydate" type="hidden" id="categorydate" value="<?php $setvalue = isset($row_rsPhotoCategory['categorydate']) ? $row_rsPhotoCategory['categorydate'] :  $row_rsPhotoCategory['createddatetime']; echo $setvalue; $inputname = "categorydate"; $startyear = "1950"; ?>" />
                <?php require_once('../../../core/includes/datetimeinput.inc.php'); ?></td>
            </tr>
            <tr class="region">
              <td align="right" valign="top"><label for="regionID">Site:</label></td>
              <td><select name="regionID" id="regionID" class="form-control">
                  <option value="1" <?php if (!(strcmp(1, $row_rsPhotoCategory['regionID']))) {echo "selected=\"selected\"";} ?>><?php echo isset($region['text_choose']) ? htmlentities($region['text_choose'], ENT_COMPAT, "UTF-8") : "Choose..." ?></option>
                  <option value="0" <?php if (!(strcmp(0, $row_rsPhotoCategory['regionID']))) {echo "selected=\"selected\"";} ?>>All sites</option>
                  <?php
do {  
?>
                  <option value="<?php echo $row_rsRegions['ID']?>"<?php if (!(strcmp($row_rsRegions['ID'], $row_rsPhotoCategory['regionID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsRegions['title']?></option>
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
              <td align="right" valign="top"><label for="categoryofID">Within gallery:</label></td>
              <td><select name="categoryofID" id="categoryofID" class="form-control">
                  <option value="0">None</option>
                  <?php if($rows > 0) {
      mysql_data_seek($rsGalleries, 0);
do {  
?>
                  <option value="<?php echo $row_rsGalleries['ID']; ?>" <?php if($row_rsGalleries['ID']==$row_rsPhotoCategory['categoryofID']) echo "selected"; ?>><?php echo $row_rsGalleries['categoryname']?></option>
                  <?php
} while ($row_rsGalleries = mysql_fetch_assoc($rsGalleries));
  $rows = mysql_num_rows($rsGalleries);
  
	  $row_rsGalleries = mysql_fetch_assoc($rsGalleries);
  }
?>
                </select></td>
            </tr>
            <tr>
              <td align="right" valign="top">Can be viewed by:</td>
              <td class="form-inline"><select name="accesslevel"  id="accesslevel" class="form-control">
                  <option value="0" <?php if (!(strcmp(0, $row_rsPhotoCategory['accesslevel']))) {echo "selected=\"selected\"";} ?>>Everyone</option>
                  <?php
do {  
?>
                  <option value="<?php echo $row_rsAccessLevel['ID']?>"<?php if (!(strcmp($row_rsAccessLevel['ID'], $row_rsPhotoCategory['accesslevel']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsAccessLevel['name']?></option>
                  <?php
} while ($row_rsAccessLevel = mysql_fetch_assoc($rsAccessLevel));
  $rows = mysql_num_rows($rsAccessLevel);
  if($rows > 0) {
      mysql_data_seek($rsAccessLevel, 0);
	  $row_rsAccessLevel = mysql_fetch_assoc($rsAccessLevel);
  }
?>
                </select>
                <span class="groups"> in
                <select name="groupID" id="groupID" class="form-control">
                  <option value="0" <?php if (!(strcmp(0, $row_rsPhotoCategory['groupID']))) {echo "selected=\"selected\"";} ?>>Any group</option>
                  <?php  $rows = mysql_num_rows($rsGroups);
  if($rows > 0) {
do {  
?>
                  <option value="<?php echo $row_rsGroups['ID']?>"<?php if (!(strcmp($row_rsGroups['ID'], $row_rsPhotoCategory['groupID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsGroups['groupname']?></option>
                  <?php
} while ($row_rsGroups = mysql_fetch_assoc($rsGroups));

      mysql_data_seek($rsGroups, 0);
	  $row_rsGroups = mysql_fetch_assoc($rsGroups);
  }
?>
                </select>
                </span></td>
            </tr>
            <tr>
              <td align="right" valign="top">Cover image:</td>
              <td><select name="coverphotoID" id="coverphotoID" class="form-control">
                  <option value="" <?php if (!(strcmp("", $row_rsPhotoCategory['coverphotoID']))) {echo "selected=\"selected\"";} ?>>First photo in gallery</option>
                  <?php
do {  
?>
                  <option value="<?php echo $row_rsAlbumPhotos['ID']?>"<?php if (!(strcmp($row_rsAlbumPhotos['ID'], $row_rsPhotoCategory['coverphotoID']))) {echo "selected=\"selected\"";} ?>><?php echo ($row_rsAlbumPhotos['title']=="Untitled") ? "Photo ".$row_rsAlbumPhotos['ID'] : $row_rsAlbumPhotos['title']; ?></option>
                  <?php
} while ($row_rsAlbumPhotos = mysql_fetch_assoc($rsAlbumPhotos));
  $rows = mysql_num_rows($rsAlbumPhotos);
  if($rows > 0) {
      mysql_data_seek($rsAlbumPhotos, 0);
	  $row_rsAlbumPhotos = mysql_fetch_assoc($rsAlbumPhotos);
  }
?>
                </select></td>
            </tr>
            <tr>
              <td align="right" valign="top">Description:</td>
              <td><textarea name="description" cols="50" rows="5" id="description" class="form-control"><?php echo htmlentities($row_rsPhotoCategory['description']); ?></textarea></td>
            </tr>
            <tr>
              <td align="right" valign="top"><label for="soundtrackURL">Soundtrack URL:</label></td>
              <td><input name="soundtrackURL" type="text" id="soundtrackURL" value="<?php echo $row_rsPhotoCategory['soundtrackURL']; ?>" size="50" maxlength="255" class="form-control"></td>
            </tr>
            <tr>
              <td align="right" valign="top">Status:</td>
              <td><label>
                  <select name="statusID" id="statusID" class="form-control">
                    <option value="2" <?php if (!(strcmp(2, $row_rsPhotoCategory['active']))) {echo "selected=\"selected\"";} ?>>Off</option>
                    <option value="1"  <?php if (!(strcmp(1, $row_rsPhotoCategory['active']))) {echo "selected=\"selected\"";} ?>>On (listed in galleries)</option>
                    <option value="0" <?php if (!(strcmp(0, $row_rsPhotoCategory['active']))) {echo "selected=\"selected\"";} ?>>On (but not listed in galleries)</option>
                  </select>
                </label></td>
            </tr>
            <tr>
              <td align="right" valign="top">&nbsp;</td>
              <td><button name="add" type="submit" class="btn btn-primary" id="add" >Save changes</button>
                <button name="deletebutton" type="button" class="btn btn-default btn-secondary" onclick="if(confirm('Are you sure you want to delete this gallery? All photos within will be lost.')) { document.getElementById('formaction').value='delete'; this.form.submit(); }" >Delete</button>
                <input name="formaction" type="hidden" id="formaction" />
                <input type="hidden" name="MM_update" value="form1" />
                <input name="ID" type="hidden" id="ID" value="<?php echo $row_rsPhotoCategory['ID']; ?>" />
                <input name="modifiedbyID" type="hidden" id="modifiedbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>">
                <input name="modifieddatetime" type="hidden" id="modifieddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>"></td>
            </tr>
          </table>
        </form>
        <?php } // end OK to access
		 else { ?>
        <p>You cannot edit this gallery.
          <?php if (!isset($_SESSION['MM_Username'])) { ?>
            Try <a href="/login/index.php?accesscheck=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>">logging in</a>.
            <?php } ?>
        </p>
        <?php } ?>
      </div>
      <script>
<!--
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1");
//-->
               </script> 
      <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsPhotoCategory);

mysql_free_result($rsAccessLevel);

mysql_free_result($rsAlbumPhotos);

mysql_free_result($rsGroups);

mysql_free_result($rsRegions);

mysql_free_result($rsGalleries);

mysql_free_result($rsLoggedIn);
?>
