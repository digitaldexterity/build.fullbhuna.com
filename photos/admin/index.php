<?php require_once('../../Connections/aquiescedb.php'); ?><?php require_once('../../core/includes/adminAccess.inc.php'); ?><?php require_once('../../core/includes/framework.inc.php'); ?><?php require_once('../includes/galleryfunctions.inc.php'); ?>
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
?><?php

$currentPage = $_SERVER["PHP_SELF"];

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

if ((isset($_GET['photoID'])) && ($_GET['photoID'] != "")) {
  $deleteSQL = sprintf("DELETE FROM photos WHERE ID=%s",
                       GetSQLValueString($_GET['photoID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($deleteSQL, $aquiescedb) or die(mysql_error());
}

if(isset($_POST['formaction'])) { // is action and selected items
mysql_select_db($database_aquiescedb, $aquiescedb);
	if($_POST['formaction']==1) { // remove
		foreach($_POST['photocheckbox'] as $key=> $value) {
			$update = "UPDATE photos SET active = 3, modifiedbyID = ".intval($_POST['modifiedbyID']).", modifieddatetime = NOW() WHERE ID = ".intval($value); 
			mysql_query($update, $aquiescedb) or die(mysql_error()); 		
		}		
		$msg = count($_POST['photocheckbox'])." photos successfully removed from display.";		
 	} // end action
	if($_POST['formaction']==2) { // delete
		foreach($_POST['photocheckbox'] as $key=> $value) {
			deletePhoto( $value) ;
		}		
		$msg = count($_POST['photocheckbox'])." photos successfully deleted.";		
 	} // end action
	if($_POST['formaction']=='changeGallery') { // update gallery
		foreach($_POST['photocheckbox'] as $key=> $value) {
			$update = "UPDATE photos SET categoryID = ".intval($_POST['galleryID']).", modifiedbyID = ".intval($_POST['modifiedbyID']).", modifieddatetime = NOW() WHERE ID = ".intval($value); 
			mysql_query($update, $aquiescedb) or die(mysql_error()); 		
		}
		$msg = count($_POST['photocheckbox'])." photos successfully moved.";
	}// end action
	
	unset($_SESSION['checkbox']);
}// end post

if(isset($_GET['delete']) && $_SESSION['MM_UserGroup']==10) {
	$delete = "DELETE FROM photos,photocategories,photoincategory,photocomments,photoviews";
}



$orderby = (isset($_GET['galleryID']) && $_GET['galleryID']>0) ? " ordernum ASC, ID ASC " : " createddatetime DESC ";
$maxrows =  (isset($_GET['galleryID']) && $_GET['galleryID']>0) ? 1000 : 50;

$maxRows_rsPhotos = $maxrows;
$pageNum_rsPhotos = 0;
if (isset($_GET['pageNum_rsPhotos'])) {
  $pageNum_rsPhotos = $_GET['pageNum_rsPhotos'];
}
$startRow_rsPhotos = $pageNum_rsPhotos * $maxRows_rsPhotos;

$varRegionID_rsPhotos = "1";
if (isset($regionID)) {
  $varRegionID_rsPhotos = $regionID;
}
$varGalleryID_rsPhotos = "0";
if (isset($_GET['galleryID'])) {
  $varGalleryID_rsPhotos = $_GET['galleryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPhotos = sprintf("SELECT photos.*, photocategories.categoryname,photocategories.active AS categorystatusID, users.firstname, users.surname FROM photos LEFT JOIN photocategories ON photos.categoryID = photocategories.ID LEFT JOIN users ON (photos.userID = users.ID) WHERE (photocategories.regionID IS NULL OR photocategories.regionID = %s) AND (%s = 0 OR photos.categoryID = %s) ORDER BY ".$orderby."", GetSQLValueString($varRegionID_rsPhotos, "int"),GetSQLValueString($varGalleryID_rsPhotos, "int"),GetSQLValueString($varGalleryID_rsPhotos, "int"));
$query_limit_rsPhotos = sprintf("%s LIMIT %d, %d", $query_rsPhotos, $startRow_rsPhotos, $maxRows_rsPhotos);
$rsPhotos = mysql_query($query_limit_rsPhotos, $aquiescedb) or die(mysql_error());
$row_rsPhotos = mysql_fetch_assoc($rsPhotos);

if (isset($_GET['totalRows_rsPhotos'])) {
  $totalRows_rsPhotos = $_GET['totalRows_rsPhotos'];
} else {
  $all_rsPhotos = mysql_query($query_rsPhotos);
  $totalRows_rsPhotos = mysql_num_rows($all_rsPhotos);
}
$totalPages_rsPhotos = ceil($totalRows_rsPhotos/$maxRows_rsPhotos)-1;

$varRegionID_rsCategories = "1";
if (isset($regionID)) {
  $varRegionID_rsCategories = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsCategories = sprintf("SELECT ID, categoryname FROM photocategories WHERE regionID = %s ORDER BY categoryname ASC", GetSQLValueString($varRegionID_rsCategories, "int"));
$rsCategories = mysql_query($query_rsCategories, $aquiescedb) or die(mysql_error());
$row_rsCategories = mysql_fetch_assoc($rsCategories);
$totalRows_rsCategories = mysql_num_rows($rsCategories);

$queryString_rsPhotos = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsPhotos") == false && 
        stristr($param, "totalRows_rsPhotos") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsPhotos = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsPhotos = sprintf("&totalRows_rsPhotos=%d%s", $totalRows_rsPhotos, $queryString_rsPhotos);
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Manage Photos"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script src="/core/scripts/checkbox/checkboxes.js"></script><?php require_once('../../core/scripts/checkbox/checkboxsession.inc.php'); ?>
<link href="../css/defaultGallery.css" rel="stylesheet"  />
<script src="/3rdparty/dropzone/dropzone.js"></script>
<link rel="stylesheet" href="/3rdparty/dropzone/dropzone.css">
<script>
$(document).ready(function(e) {
  /* Dropzone requires no configuration whatsoever - just the js file - but added below to redirect when finished */  

Dropzone.options.dropzone1 = {
	addRemoveLinks: true,
  init: function() {
    this.on("queuecomplete", function(file) { 
	document.location.href = "review.php?galleryID=<?php echo isset($_GET['galleryID']) ? intval($_GET['galleryID']) : 0; ?>";
	 });
  }
};

    // When the document is ready set up our sortable with it's inherant function(s) 
	<?php if(isset($_GET['galleryID']) && $_GET['galleryID']>0 ) { $draganddrop = true;?>
        $(".sortable").sortable({ 
            handle : '.handle', 
            update : function () { 
            var order = $(this).sortable('serialize'); 
                $("#info").load("/core/ajax/sort.ajax.php?table=photos&"+order); 
            } 
        }); 
		<?php } ?>
    }); 
</script>
<style><!--
<?php if(!isset($draganddrop)) { 
echo ".handle { display:none !important; }\n";
} ?>-->
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
    <!-- InstanceBeginEditable name="Body" -->
 <div class="page"><?php require_once('../../core/region/includes/chooseregion.inc.php'); ?>
 <h1><i class="glyphicon glyphicon-picture"></i> Manage Photos
  </h1>
  <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
  
    <li class="nav-item"><a href="galleries/index.php" class="nav-link"><i class="glyphicon glyphicon-picture"></i> Manage Galleries</a> </li>
    <li class="nav-item"><a href="add_video.php" class="nav-link"><i class="glyphicon glyphicon-film"></i> Add Video</a> </li>
    
    <li class="nav-item"><a href="options/index.php" class="nav-link"><i class="glyphicon glyphicon-cog"></i> Options</a> </li><li class="nav-item"><a href="/photos/" target="_blank" class="nav-link" rel="noopener" onclick="openMainWindow('/photos/'); return false;" ><i class="glyphicon glyphicon-arrow-left"></i> Go to public Galleries</a></li>
  </ul></div></nav><?php require_once('../../core/includes/alert.inc.php'); ?>
  <form action="add_photos.php" class="dropzone" name = "dropzone1" id="dropzone1" enctype="multipart/form-data" method="post">
<div class="fallback">
    <input name="file" type="file" multiple ><button type="submit" class="btn btn-primary">Upload</button>
    <input type="hidden" name="nodropzone" value="true">
  </div><input name="directory" type="hidden" value="users/<?php echo $_SESSION['MM_Username']; ?>/<?php echo session_id(); ?>/"></form>
  
  <form method="get" class="form-inline">
  <?php if ($totalRows_rsPhotos == 0) { // Show if recordset empty ?>
  <p>There are currently no photos added  in 
    
      <select name="galleryID" id="galleryID" onChange="this.form.submit()" class="form-control">
        <option value="0" <?php if (!isset($_GET['galleryID']) || $_GET['galleryID']==0) {echo "selected=\"selected\"";} ?>>All galleries</option>
        <?php $rows = mysql_num_rows($rsCategories);
  if($rows > 0) {
		
do {  
?>
        <option value="<?php echo $row_rsCategories['ID']; ?>"<?php if ($row_rsCategories['ID']==$_GET['galleryID']) {echo "selected=\"selected\"";} ?>><?php echo $row_rsCategories['categoryname']?></option>
        <?php
} while ($row_rsCategories = mysql_fetch_assoc($rsCategories));
 
      mysql_data_seek($rsCategories, 0);
	  $row_rsCategories = mysql_fetch_assoc($rsCategories);
  }
?>
      </select></p>
  <?php } // Show if recordset empty ?>
  <?php if ($totalRows_rsPhotos > 0) { // Show if recordset not empty ?>
    <p class="text-muted">Photos <?php echo ($startRow_rsPhotos + 1) ?> to <?php echo min($startRow_rsPhotos + $maxRows_rsPhotos, $totalRows_rsPhotos) ?> of <?php echo $totalRows_rsPhotos ?> in 
    
      <select name="galleryID" id="galleryID" onChange="this.form.submit()" class="form-control">
        <option value="0" <?php if (!isset($_GET['galleryID']) || $_GET['galleryID']==0) {echo "selected=\"selected\"";} ?>>All galleries</option>
        <?php $rows = mysql_num_rows($rsCategories);
  if($rows > 0) {
do {  
?>
        <option value="<?php echo $row_rsCategories['ID']; ?>"<?php if ($row_rsCategories['ID']==$_GET['galleryID']) {echo "selected=\"selected\"";} ?>><?php echo $row_rsCategories['categoryname']?></option>
        <?php
} while ($row_rsCategories = mysql_fetch_assoc($rsCategories));
  
      mysql_data_seek($rsCategories, 0);
	  $row_rsCategories = mysql_fetch_assoc($rsCategories);
  }
?>
      </select> <span id="info">Select a gallery and drag and drop to re-order</span></p>
    </form>
    <form action="index.php" method="post"  id="checkboxform">
   <table class="table table-hover">
     <thead><tr>
        <th class="handle">&nbsp;</th><th><input type="checkbox" name="checkAll" id="checkAll" onclick="checkUncheckAll(this);" /></th>
        <th>&nbsp;</th>  <th>&nbsp;</th> 
        <th>Uploaded</th>
        <th>Photo</th>
        <th>Video</th>
       
        <th>Category</th>
        <th>Actions</th>
      </tr></thead><tbody class="sortable">
      <?php do { ?>
      
       <tr id="listItem_<?php echo $row_rsPhotos['ID']; ?>" >
       
       <td class="handle">&nbsp;</td>
        <td class="text-nowrap">
              <input type="checkbox" name="photocheckbox[<?php echo $row_rsPhotos['ID']; ?>]" id="photocheckbox<?php echo $row_rsPhotos['ID']; ?>" value="<?php echo $row_rsPhotos['ID']; ?>" />
            </td>
         <td class="status<?php echo $row_rsPhotos['active']; ?>">&nbsp;</td>
      
          <td><a href="update_photo.php?photoID=<?php echo $row_rsPhotos['ID']; ?>" title="<?php echo $row_rsPhotos['imageURL']; ?>" class="fb_avatar" style="background-image:url(<?php echo getImageURL($row_rsPhotos['imageURL'],"thumb"); ?>)"></a></td>
          <td><?php echo date('d M y',strtotime($row_rsPhotos['createddatetime'])); ?><br><em><?php echo date('H:i',strtotime($row_rsPhotos['createddatetime'])); ?></em></td>
          <td><a href="update_photo.php?photoID=<?php echo $row_rsPhotos['ID']; ?>" title="<?php echo $row_rsPhotos['imageURL']; ?>"><?php echo $row_rsPhotos['title']; ?></a><br><em><?php echo $row_rsPhotos['description']; ?></em></td>
           <td><?php if(isset($row_rsPhotos['videoURL'])) { ?><a href="update_photo.php?photoID=<?php echo $row_rsPhotos['ID']; ?>" title="<?php echo $row_rsPhotos['videoURL']; ?>"><i class="glyphicon glyphicon-film"></i></a><?php } ?>&nbsp;</td>
          <td><em><?php echo $row_rsPhotos['categoryname']; ?></em></td>
          <td nowrap><div class="btn-group" role="group" ><a href="update_photo.php?photoID=<?php echo $row_rsPhotos['ID']; ?>" title="<?php echo $row_rsPhotos['imageURL']; ?>" class="btn btn-sm btn-default btn-secondary" ><i class="glyphicon glyphicon-pencil"></i> Edit</a> <a href="index.php?photoID=<?php echo $row_rsPhotos['ID']; ?>" onClick="return confirm('Are you sure you want to delete this photo permanently?');" class="btn btn-sm btn-default btn-secondary"><i class="glyphicon glyphicon-trash"></i> Delete</a></div></td>
         </tr>
        <?php } while ($row_rsPhotos = mysql_fetch_assoc($rsPhotos)); ?>
    </tbody></table>
    <fieldset class="form-inline">
     <p>With selected:  <?php $rows = mysql_num_rows($rsCategories);
  if($rows > 0) { ?><select name="galleryID" id="galleryID" onChange="if(confirm('Are you sure you want to move selected photos to '+$('option:selected', this).text()+'?')) { document.getElementById('formaction').value='changeGallery'; document.getElementById('checkboxform').submit(); } else { $(this).val('0'); return false;} " class="form-control input-sm">
        <option value="0" >Move to gallery....</option>
        <?php
do {  
?>
        <option value="<?php echo $row_rsCategories['ID']; ?>"><?php echo $row_rsCategories['categoryname']?></option>
        <?php
} while ($row_rsCategories = mysql_fetch_assoc($rsCategories));
  $rows = mysql_num_rows($rsCategories);
 
      mysql_data_seek($rsCategories, 0);
	  $row_rsCategories = mysql_fetch_assoc($rsCategories);
  
?>
      </select><?php } ?> <a href="javascript:void(0);" class="btn btn-sm btn-default btn-secondary" onClick="if(confirm('Are you sure you want to remove these photos from display? They can be reactivated if required later.')) { document.getElementById('formaction').value=1; document.getElementById('checkboxform').submit(); } return false;"><i class="glyphicon glyphicon-trash"></i> Remove photos</a> <a href="javascript:void(0);" class="rank10 btn btn-sm btn-danger " onClick="if(confirm('Are you sure you want to delete these photos from completely?')) { document.getElementById('formaction').value=2; document.getElementById('checkboxform').submit(); } return false;">Delete photos</a></p></fieldset>
      <input name="modifiedbyID" type="hidden" id="modifiedbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
       <input name="formaction" id="formaction" type="hidden" /></form>
    <?php echo createPagination($pageNum_rsPhotos,$totalPages_rsPhotos,"rsPhotos"); ?>
    <?php } // Show if recordset not empty ?></div>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsPhotos);

mysql_free_result($rsCategories);
?>
