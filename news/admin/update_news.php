<?php require_once('../../Connections/aquiescedb.php'); ?><?php require_once('../../core/includes/adminAccess.inc.php'); ?>
<?php require_once('../includes/newsfunctions.inc.php'); ?>
<?php require_once('../../core/includes/upload.inc.php'); ?><?php require_once('../../mail/includes/sendmail.inc.php'); ?><?php require_once('../../core/includes/framework.inc.php'); ?><?php require_once('../../photos/includes/galleryfunctions.inc.php'); ?>
<?php $regionID = (isset($regionID) && intval($regionID)>0) ? intval($regionID) : 1;
if (!isset($_SESSION)) {
  session_start();
}$MM_authorizedUsers = "8,9,10";
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


if(isset($_FILES) && !empty($_FILES) && !isset($_POST["MM_update"])) { // dropzone upload
	$uploaded = getUploads();
	$createdbyID=0;
	$newsID = intval($_GET['newsID']);
	if (isset($uploaded) && is_array($uploaded)) { 
		if(isset($uploaded["filename"][0]["newname"]) && $uploaded["filename"][0]["newname"]!="") { 
			$imageURL = $uploaded["filename"][0]["newname"]; 
			$select = "SELECT imageURL, photogalleryID, title FROM news WHERE ID = ".$newsID;			
			mysql_select_db($database_aquiescedb, $aquiescedb);
  			$result = mysql_query($select, $aquiescedb) or die(mysql_error());
			if(mysql_num_rows($result)>0) {
				$news=mysql_fetch_assoc($result);
				$photoID = 0;
				$galleryID = 0;
				$mainimageURL = "";
				$log = "imageURL=".$news['imageURL']." photogalleryID=".$news['photogalleryID'];
				if(!isset($news['imageURL']) || $news['imageURL']=="") { // no image exists so add as main
					$update = "UPDATE news SET imageURL = ".GetSQLValueString($imageURL, "text")." WHERE ID = ".$newsID;
					mysql_query($update, $aquiescedb) or die(mysql_error());
					$mainimageURL = $imageURL;
					$log .= $update."\n";
				} else {
					$photoID = addPhoto( $imageURL,  $news['title'], "", "", $createdbyID);
					if(!isset($news['photogalleryID']) || $news['photogalleryID']=="") { // no gallery exists so add one
					
						$galleryID = addGallery($news['title']." photos");
						$update = "UPDATE news SET photogalleryID = ".GetSQLValueString($galleryID, "int")." WHERE ID = ".$newsID;
						mysql_query($update, $aquiescedb) or die(mysql_error());
						$log .= $update."\n";
					} else {
						$galleryID = intval($news['photogalleryID']);
						$log .="Existing gallery=".$galleryID."\n";
					}
				
					addPhotoToGallery($photoID,$galleryID,$createdbyID);
				}
				$response = array();
				$response['log'] = $log;
				$response['mainimageURL'] = $mainimageURL;
				$response['photoID'] = $photoID;
				$response['galleryID'] = $galleryID;
				echo json_encode($response);
			} // end select result
			
		}
	}
	die(); // kill after dropzone upload
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
	$_POST['longID'] = createURLname($_POST['longID'], $_POST['title'], "-",  "news", $_POST['ID']);

	$uploaded = getUploads();
	if(isset($_POST['removeattachment'])) {
		$_POST['attachment1'] = "";
	}
	if(isset($_POST['noImage'])) {
		$_POST['imageURL'] = "";
	}
	if(isset($_POST['noImage2'])) {
		$_POST['imageURL2'] = "";
	}
	if (isset($uploaded) && is_array($uploaded)) { 
		if(isset($uploaded["filename"][0]["newname"]) && $uploaded["filename"][0]["newname"]!="") { 
			$_POST['imageURL'] = $uploaded["filename"][0]["newname"]; 
		}
		if(isset($uploaded["filename1"][0]["newname"]) && $uploaded["filename1"][0]["newname"]!="") { 
			$_POST['attachment1'] = $uploaded["filename1"][0]["newname"]; 
		}
		if(isset($uploaded["filename2"][0]["newname"]) && $uploaded["filename2"][0]["newname"]!="") { 
			$_POST['imageURL2'] = $uploaded["filename2"][0]["newname"]; 
		}
	}
	
	
}



if(isset($_POST['headline'])) {
	// clear current
	$update = "UPDATE news SET headline = 0";
	  mysql_select_db($database_aquiescedb, $aquiescedb);
  $result = mysql_query($update, $aquiescedb) or die(mysql_error());
  // new updated in update/ insert
} 

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE news SET longID=%s, metadescription=%s, metakeywords=%s, title=%s, summary=%s, body=%s, displayfrom=%s, displayto=%s, status=%s, headline=%s, featured=%s, alert=%s, rss=%s, imageURL=%s, imagealt=%s, imageURL2=%s, attachment1=%s, redirectURL=%s, youtube=%s, regionID=%s, photogalleryID=%s, groupemail=%s, modifiedbyID=%s, modifieddatetime=%s, groupemailID=%s, pagetitle=%s, eventdatetime=%s, slideshow=%s WHERE ID=%s",
                       GetSQLValueString($_POST['longID'], "text"),
                       GetSQLValueString($_POST['metadescription'], "text"),
                       GetSQLValueString($_POST['metakeywords'], "text"),
                       GetSQLValueString($_POST['title'], "text"),
                       GetSQLValueString($_POST['summary'], "text"),
                       GetSQLValueString($_POST['body'], "text"),
                       GetSQLValueString($_POST['displayfrom'], "date"),
                       GetSQLValueString($_POST['displayto'], "date"),
                       GetSQLValueString($_POST['status'], "int"),
                       GetSQLValueString(isset($_POST['headline']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['featured']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['isalert']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['rss']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['imageURL'], "text"),
                       GetSQLValueString($_POST['imagealt'], "text"),
                       GetSQLValueString($_POST['imageURL2'], "text"),
                       GetSQLValueString($_POST['attachment1'], "text"),
                       GetSQLValueString($_POST['redirectURL'], "text"),
                       GetSQLValueString($_POST['youtube'], "text"),
                       GetSQLValueString($_POST['regionID'], "int"),
                       GetSQLValueString($_POST['photogalleryID'], "int"),
                       GetSQLValueString(isset($_POST['groupemail']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['modifiedbyID'], "int"),
                       GetSQLValueString($_POST['modifieddatetime'], "date"),
                       GetSQLValueString($_POST['groupemailID'], "int"),
                       GetSQLValueString($_POST['pagetitle'], "text"),
                       GetSQLValueString($_POST['eventdatetime'], "date"),
                       GetSQLValueString(isset($_POST['slideshow']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
	
	if($_POST['status'] == 1 && isset($_POST["sendemail"]) && $_POST["groupemailID"] =="") {
		$active = $_POST['groupemaildraft']==1 ? 0 : 1;
		
		$groupemailID = sendNewsEmail($_POST['ID'], $active);
		$update = "UPDATE news SET groupemailID = ".GetSQLValueString($groupemailID, "int")." WHERE ID = ".GetSQLValueString($_POST['ID'], "int");
		mysql_query($update, $aquiescedb) or die(mysql_error());
		$msg = "The post has been added to the email queue.";
		
		
		
	}
	

  $updateGoTo = "index.php?sectionID=".intval($_POST['sectionID']);
  if(isset($msg)) {
	  $updateGoTo .= "&msg=".urlencode($msg);
  }
  
  header(sprintf("Location: %s", $updateGoTo));exit;
}

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID, usertypeID, firstname, surname, regionID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

$colname_rsNews = "-1";
if (isset($_GET['newsID'])) {
  $colname_rsNews = $_GET['newsID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsNews = sprintf("SELECT news.*, CONCAT(created.firstname,' ', created.surname) AS createdby, CONCAT(modified.firstname,' ', modified.surname) AS modifiedby FROM news LEFT JOIN users AS created ON (news.postedbyID = created.ID) LEFT JOIN users AS modified ON (news.modifiedbyID = modified.ID) WHERE news.ID = %s", GetSQLValueString($colname_rsNews, "int"));
$rsNews = mysql_query($query_rsNews, $aquiescedb) or die(mysql_error());
$row_rsNews = mysql_fetch_assoc($rsNews);
$totalRows_rsNews = mysql_num_rows($rsNews);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsStatus = "SELECT * FROM status";
$rsStatus = mysql_query($query_rsStatus, $aquiescedb) or die(mysql_error());
$row_rsStatus = mysql_fetch_assoc($rsStatus);
$totalRows_rsStatus = mysql_num_rows($rsStatus);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = "SELECT * FROM preferences WHERE ID = ".$regionID;
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsRegions = "SELECT ID, title FROM region WHERE statusID = 1 ORDER BY title ASC";
$rsRegions = mysql_query($query_rsRegions, $aquiescedb) or die(mysql_error());
$row_rsRegions = mysql_fetch_assoc($rsRegions);
$totalRows_rsRegions = mysql_num_rows($rsRegions);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPhotoGalleries = "SELECT ID, categoryname FROM photocategories WHERE active !=2 ORDER BY categoryname ASC";
$rsPhotoGalleries = mysql_query($query_rsPhotoGalleries, $aquiescedb) or die(mysql_error());
$row_rsPhotoGalleries = mysql_fetch_assoc($rsPhotoGalleries);
$totalRows_rsPhotoGalleries = mysql_num_rows($rsPhotoGalleries);

$varRegionID_rsSections = "1";
if (isset($regionID)) {
  $varRegionID_rsSections = $regionID;
}
$varUserGroup_rsSections = "-1";
if (isset($_SESSION['MM_UserGroup'])) {
  $varUserGroup_rsSections = $_SESSION['MM_UserGroup'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsSections = sprintf("SELECT * FROM newssection WHERE statusID = 1 AND (%s >=9 OR newssection.regionID = 0 OR newssection.regionID = %s) ORDER BY newssection.ordernum, newssection.ID", GetSQLValueString($varUserGroup_rsSections, "int"),GetSQLValueString($varRegionID_rsSections, "int"));
$rsSections = mysql_query($query_rsSections, $aquiescedb) or die(mysql_error());
$row_rsSections = mysql_fetch_assoc($rsSections);
$totalRows_rsSections = mysql_num_rows($rsSections);

$colname_rsThisSection = "1";
if (isset($row_rsNews['sectionID'])) {
  $colname_rsThisSection = $row_rsNews['sectionID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsThisSection = sprintf("SELECT newssection.*, usergroup.groupname, usertype.name AS rankname FROM newssection LEFT JOIN usergroup ON (newssection.groupreadID = usergroup.ID) LEFT JOIN usertype ON (newssection.accesslevel = usertype.ID) WHERE newssection.ID = %s", GetSQLValueString($colname_rsThisSection, "int"));
$rsThisSection = mysql_query($query_rsThisSection, $aquiescedb) or die(mysql_error());
$row_rsThisSection = mysql_fetch_assoc($rsThisSection);
$totalRows_rsThisSection = mysql_num_rows($rsThisSection);

$varNewsID_rsAllTags = "-1";
if (isset($_GET['newsID'])) {
  $varNewsID_rsAllTags = $_GET['newsID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsAllTags = sprintf("SELECT tag.ID, tagname, taggroup.taggroupname, tagged.tagID AS tagged FROM tag LEFT JOIN taggroup ON (tag. taggroupID = taggroup.ID) LEFT JOIN tagged ON (tagged.tagID =tag.ID AND newsID = %s) ORDER BY taggroupID, tag.ordernum, tagname ASC", GetSQLValueString($varNewsID_rsAllTags, "int"));
$rsAllTags = mysql_query($query_rsAllTags, $aquiescedb) or die(mysql_error());
$row_rsAllTags = mysql_fetch_assoc($rsAllTags);
$totalRows_rsAllTags = mysql_num_rows($rsAllTags);


?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Update Post"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script>
var fb_keepAlive  = true;
$(document).ready(function(e) {
	  
	   showGallery(<?php echo intval($row_rsNews['photogalleryID']); ?>);
	   
	   $("#removeMainImage").click(function() {
		   $.get("ajax/news_functions.ajax.php?cmd=remove_image&newsID=<?php echo $row_rsNews['ID']; ?>", function(data) {
			  
		   	$("#main_image").fadeOut();
		   	$("#main_image").attr("src","");
		   	$("#mainimageURL").val("");
		   });
	   });
	   
	    $("#swapMainImage").click(function() {
			$.get("ajax/news_functions.ajax.php?cmd=swap_image&newsID=<?php echo $row_rsNews['ID']; ?>", function(data) {
			
				if(data.indexOf("ERROR:")<0) {
					if($("#main_image").length) {
						$("#main_image").attr("src",data);
					} else {
						$("#mainimageURL").after("<img src='"+data+"'>");
					}
					showGallery(<?php echo intval($row_rsNews['photogalleryID']); ?>);
				}
			});
	   });
	   
});




function updateTag(isChecked, tagID) {
	if(isChecked) { // add tag
	$.ajax({url: "/core/tags/ajax/addtag.ajax.php?tagID="+tagID+"&newsID="+<?php echo $row_rsNews['ID']; ?>+"&createdbyID="+<?php echo $row_rsLoggedIn['ID']; ?>, success: function(result){
				$("#info").html(result);
				}
			});
	} else { // remove tag
	$.ajax({url: "/core/tags/ajax/removetag.ajax.php?tagID="+tagID+"&newsID="+<?php echo $row_rsNews['ID']; ?>, success: function(result){
				$("#info").html(result);
				}
			});
	}
}

function showGallery(galleryID) {
	if(galleryID>0) {
		$.get("/photos/includes/gallery.inc.php", {galleryID: galleryID}, function(data) {
		$(".photoGallery").html(data);
	});
	}
}

</script>
<script src="/core/scripts/date-picker/js/datepicker.js"></script>
<script src="../../core/scripts/liveUrlScrape.js"></script>
<script src="/core/scripts/formUpload.js"></script>
<script src="../../SpryAssets/SpryTabbedPanels.js"></script>
<script src="../../SpryAssets/SpryValidationTextField.js"></script>
<link href="../../SpryAssets/SpryTabbedPanels.css" rel="stylesheet"  /><link href="/core/tags/css/tags.css" rel="stylesheet" >
<style><!--
.liveURLscrapeImgPreview img {
	max-width:300px;
}
<?php if (!(defined("MOD_REWRITE") || isset($_SERVER['HTTP_MOD_REWRITE']))) { // no mod re-write so hide URL option ?>
.longID { display:none; }
<?php } ?>
<?php if ($row_rsPreferences['useregions']!=1) { // use regions ?>
 .region {
	display:none;
}
<?php } 
if($totalRows_rsSections < 1) {?>
.section {
display:none;
}

<?php } else {

if($row_rsThisSection['showbody']==0) {
	echo ".newsbody { display: none; }";
}

}
if($totalRows_rsSections < 1 || $row_rsThisSection['showeventdatetime']==0) {
	echo ".eventdatetime { display: none; }";
}
?>--></style>
<link href="../../SpryAssets/SpryValidationTextField.css" rel="stylesheet" >
<link href="/core/scripts/dropzone/dropzone.css" rel="stylesheet" type="text/css">
<script src="/core/scripts/dropzone/dropzone.js"></script>
<link href="/core/scripts/dropzone/dropzone.css" rel="stylesheet" >
<link href="../../photos/css/defaultGallery.css" rel="stylesheet" type="text/css">
<script>

// Prevent Dropzone from auto discovering this element:
Dropzone.options.photosDropzone = false;
// This is useful when you want to create the
// Dropzone programmatically later


$(document).ready(function(e) {
    var photosDropzone = new Dropzone("#photosDropzone", { 
		addRemoveLinks: true,
		url: "<?php echo $_SERVER['REQUEST_URI']; ?>",
		acceptedFiles : ".jpg,.jpeg,.png,.gif", 
		parallelUploads : 1, /* specifically to ensure first image is main image */
		paramName: "filename", /*otherwise Dropzone changes to 'file' */
	 	init: function() {	
			 
		 	this.on('error', function(file, response) {
    			alert(response);
			});
			this.on('success', function(file, response) {
    			//alert(response);
				var dropzone_response = JSON.parse(response);
				if(dropzone_response.mainimageURL!="") {
					document.getElementById("mainimageURL").value=dropzone_response.mainimageURL;
				}	
				if(dropzone_response.galleryID>0) {	
					document.getElementById("photogalleryID").value=dropzone_response.galleryID;
				}
			});
			this.on("queuecomplete", function(file, response) { 
				//alert(response);
	 		});	
			
			
	 	}
		
 	});	 
});

</script>
<?php 
require_once('../../core/tinymce/tinymce.inc.php'); 
?>
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
<div class="page news">
<h1><i class="glyphicon glyphicon-bullhorn"></i> Update Post</h1>
<nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
  <li class="nav-item"><a href="index.php<?php echo isset($row_rsThisSection['ID']) ? "?sectionID=".intval($row_rsThisSection['ID']) : ""; ?>" class="nav-link" onClick="return confirm('Any changes have not been saved. Continue back?'); "><i class="glyphicon glyphicon-arrow-left"></i> Back to Posts</a></li>
</ul></div></nav>
<?php if($row_rsLoggedIn['usertypeID']>=8 || $row_rsLoggedIn['regionID'] == $regionID || $row_rsLoggedIn['ID'] = $row_rsNews['postedbyID']) { //authorised to edit ?>
<?php if(isset($submit_error)) { ?><p class="alert alert-danger" role="alert"><?php echo $submit_error; ?></p><?php } ?><form action="<?php echo $editFormAction; ?>" method="post" enctype="multipart/form-data" name="form1" id="form1"  onsubmit="validateForm(document.form1); return document.returnValue;">

<div id="TabbedPanels1" class="TabbedPanels">
  <ul class="TabbedPanelsTabGroup">
    <li class="TabbedPanelsTab" tabindex="0">Content</li>
    <li class="TabbedPanelsTab" tabindex="0">Tags</li>
    <li class="TabbedPanelsTab" tabindex="0">Options</li>
    <li class="TabbedPanelsTab" tabindex="0">SEO</li>
  </ul>
  <div class="TabbedPanelsContentGroup">
    <div class="TabbedPanelsContent">
      <table class="form-table" style="width:100%">
        <tr class="eventdatetime">
          <td class="text-nowrap text-right top">Event date:</td>
          <td><input name="eventdatetime" type="hidden" class='highlight-days-67 split-date format-y-m-d divider-dash' id="eventdatetime" value="<?php $setvalue = $row_rsNews['eventdatetime']; echo $setvalue;?>"/>
            <?php $inputname = "eventdatetime"; include("../../core/includes/datetimeinput.inc.php"); ?></td>
        </tr>
        <tr>
          <td class="text-nowrap text-right top">Title:</td>
          <td><input name="title" id="title" type="text"  value="<?php echo htmlentities($row_rsNews['title'], ENT_COMPAT, "UTF-8"); ?>" size="50" maxlength="100" onBlur="seoPopulate(this.value, document.getElementById('summary').value);"  class="form-control"/></td>
        </tr>
        
        <tr>
          <td class="text-nowrap text-right top">Summary/<br>
            lead paragraph:<br><label><input type="checkbox" checked id="liveScrapeOn">&nbsp;Look for web links</label></td>
          <td><textarea name="summary" id="summary" cols="50" rows="5" onBlur="seoPopulate(document.getElementById('title').value, this.value);"   class="form-control  liveURLscrape liveURLscrapeSummary <?php echo ($row_rsThisSection['wysiwygsummary']==1) ? " tinymce " : ""; ?>" ><?php echo htmlentities($row_rsNews['summary'], ENT_COMPAT, "UTF-8"); ?></textarea></td>
        </tr>
        
        <tr>
          <td class="text-nowrap text-right top">Main image/<br>
            Gallery:</td>
          <td><input type="hidden" name="imageURL" id="mainimageURL" value="<?php echo $row_rsNews['imageURL']; ?>"  /><?php if (isset($row_rsNews['imageURL'])) { ?>
            <img src="<?php echo getImageURL($row_rsNews['imageURL'], "thumb"); ?>" id="main_image" alt="Current image" class="medium" />
            
             <?php } ?>
           
            <div class="form-group">
             <?php if(isset($row_rsNews['imageURL'])) { ?>
            <button type="button" class="btn btn-default btn-secondary" id="removeMainImage"><i class="glyphicon glyphicon-trash"></i> Remove main image</button>
            <?php  } ?>
             <?php if(isset($row_rsNews['photogalleryID'])) { ?>
            <button type="button" class="btn btn-default btn-secondary" id="swapMainImage"><i class="glyphicon glyphicon-sort"></i> Swap with first in Gallery</button> <?php  } ?></div>
            
           
           
            <div class="photoGallery"></div>
            <div id="photosDropzone" class="dropzone" >
         <div class="fallback">
              <input name="filename" type="file" class="fileinput" id="filename" accept=".jpg,.jpeg,.png,.gif" />
              <input type="hidden" name="nodropzone" value="true">
            </div>
            </div></td>
        </tr>
        
        <tr  class="newsbody">
          <td class="text-nowrap text-right top">Body:</td>
          <td><textarea name="body" cols="50" rows="10"  class="form-control <?php echo ($row_rsThisSection['wysiwyg']==1) ? " tinymce " : ""; ?>"><?php echo htmlentities($row_rsNews['body'], ENT_COMPAT, "UTF-8"); ?></textarea></td>
        </tr>
        
        <tr <?php if($row_rsThisSection['wysiwyg']==1) { 
echo "class=\"hide-form-item\""; 
} ?>>
          <td class="text-nowrap text-right"><label for="attachment">Attachment:</label></td>
          <td><?php if(isset($row_rsNews['attachment1']) && $row_rsNews['attachment1']!="") { 
		  echo "<a href = \"/Uploads/".$row_rsNews['attachment1']."\" target=\"_blank\">".$row_rsNews['attachment1']."</a>"; ?>
            <label>
              <input type="checkbox" name="removeattachment" id="removeattachment" />
              Remove </label>
            <br />
            <?php } ?>
           
              <input type="file" name="filename1" id="filename1" class="fileinput" />
            
            <input name="attachment1" type="hidden" id="attachment1" value="<?php echo $row_rsNews['attachment1']; ?>" /></td>
        </tr>
        
        <tr>
          <td class="text-nowrap text-right">Sharing:</td>
          <td><input <?php if(isset($_GET['newpost'])) { echo $row_rsThisSection['rsssenddefault']==1 ? " checked " : ""; } else if (!(strcmp($row_rsNews['rss'],1))) {echo "checked=\"checked\"";} ?> name="rss" type="checkbox" id="rss" value="1">
            <label for="rss">(add to <a href="../news.rss.php" target="_blank" rel="noopener">RSS feed</a> and  selected social neworks using a feed account, e.g. <a href="https://dlvr.it" target="_blank" rel="noopener">dlvr.it</a>)</label></td>
        </tr>
        <?php if($row_rsThisSection['emailtemplateID']>0) { ?>
        <tr>
          <td class="text-nowrap text-right">email:</td>
          <td><?php if($row_rsNews['groupemailID']>0) { ?>
            <p>This post has been added to a group email. <a href="../../mail/admin/groupemail/preview.php?emailID=<?php echo $row_rsNews['groupemailID']; ?>" target="_blank" rel="noopener">View here</a>.</p>
            <?php } ?>
            
            <input <?php if(isset($_GET['newpost'])) { echo $row_rsThisSection['emailsenddefault']==1 ? " checked " : ""; } ?> name="sendemail" type="checkbox" id="sendemail" value="1" onClick="if(this.checked && this.form.status.value!=1) { alert('Email will only be added if status is set to Approved'); } if(this.checked && document.getElementById('groupemailID').value !='') { if(confirm('This news story has already been sent as an email. Are you sure you want to send again?')) { document.getElementById('groupemailID').value = ''; return true;} else { return false;} }" />
            <label for="sendemail">Send post <?php echo $row_rsNews['groupemailID']>0 ? "again" : ""; ?> 
              <input name="groupemailID" type="hidden" id="groupemailID" value="<?php echo $row_rsNews['groupemailID']; ?>">
            </label>
            
            <p>Send to <?php echo 
			($row_rsThisSection['accesslevel']>0) ?   $row_rsThisSection['rankname']." rank" : "everyone"; echo isset($row_rsThisSection['groupname']) ? " in " .$row_rsThisSection['groupname'] : ""; ?>: <label><input type="radio" name="groupemaildraft" value="0" > now</label>, or <input type="radio" name="groupemaildraft" value="1" checked> add to drafts</label></p></td>
        </tr>
       <?php } ?> 
        <tr>
          <td class="text-nowrap text-right top">Status:</td>
          <td><select name="status"  id="poststatus"  class="form-control">
            <?php
			// if new post update status to  ready to post
			if(isset($_GET['newpost'])) $row_rsNews['status']=1;
do {  
?>
            <option value="<?php echo $row_rsStatus['ID']?>"<?php if (!(strcmp($row_rsStatus['ID'], $row_rsNews['status']))) {echo "SELECTED";} ?>><?php echo $row_rsStatus['description']?></option>
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
          <td class="text-nowrap text-right top">&nbsp;</td>
          <td><button type="submit" class="btn btn-primary" onClick="if(document.getElementById('poststatus').value==1) return confirm('This post is set to approved and will be live from date set.');" >Save Changes</button></td>
        </tr>
        
      </table>
    </div>
    <div class="TabbedPanelsContent"><div id="info"></div>
            <?php if ($totalRows_rsAllTags > 0) { // Show if recordset not empty ?>
            <?php   $groupname = ""; do { 
		  if($row_rsAllTags['taggroupname'] != $groupname) {
			  $groupname = $row_rsAllTags['taggroupname'];
			  echo "<h3>".$groupname."</h3>";
		  }?>
            <label>
              <input type="checkbox" value="<?php echo $row_rsAllTags['ID']; ?>" onClick="updateTag(this.checked, this.value)" <?php if(isset($row_rsAllTags['tagged'])) echo "checked"; ?>>
              &nbsp;<?php echo $row_rsAllTags['tagname']; ?></label>
            &nbsp;&nbsp;
            <?php } while ($row_rsAllTags = mysql_fetch_assoc($rsAllTags)); ?>
            <?php } else { ?>
            <p>There are currently no tags set up. <a href="../../core/tags/admin/index.php">Manage Tags</a>.</p>
            <?php } ?>
            <button type="submit" class="btn btn-primary" >Save Changes</button>
        </div>
    <div class="TabbedPanelsContent">
      <table class="form-table">
        <tr>
          <td class="text-nowrap text-right">Priority:</td>
          <td><input <?php if (!(strcmp($row_rsNews['headline'],1))) {echo "checked=\"checked\"";} ?> type="checkbox" name="headline" id="headline" />&nbsp;Headline (always top)&nbsp;&nbsp;
            <label>
              <input <?php if (!(strcmp($row_rsNews['featured'],1))) {echo "checked=\"checked\"";} ?> name="featured" type="checkbox" id="featured" value="1"> Featured
            </label>
            &nbsp;&nbsp;&nbsp;
            <label>
              <input <?php if (!(strcmp($row_rsNews['alert'],1))) {echo "checked=\"checked\"";} ?> name="isalert" type="checkbox" id="isalert" value="1"> Alert
            </label></td>
        </tr>
        <tr>
          <td class="text-nowrap text-right">Display from:</td>
          <td><input name="displayfrom" type="hidden" class='highlight-days-67 split-date format-y-m-d divider-dash' id="displayfrom" value="<?php $setvalue = $row_rsNews['displayfrom']; echo $setvalue;?>"/>
            <?php $inputname = "displayfrom"; include("../../core/includes/datetimeinput.inc.php"); ?></td>
        </tr>
        <tr>
          <td class="text-nowrap text-right">Display to:</td>
          <td><input name="displayto" type="hidden"  class='highlight-days-67 split-date format-y-m-d divider-dash' id="displayto" value="<?php $setvalue = $row_rsNews['displayto']; echo $setvalue; ?>" />
            <?php $inputname = "displayto"; include("../../core/includes/datetimeinput.inc.php"); ?></td>
        </tr>
        
        
        <tr>
          <td class="text-nowrap text-right">Add photo gallery:</td>
          <td class="form-inline">
		  <input type="hidden" name="photogalleryID" id="photogalleryID" value="<?php echo $row_rsNews['photogalleryID']; ?>" />
		  <?php if ($totalRows_rsPhotoGalleries == 0) { // Show if recordset empty ?>
            No photo galleries created
            
            <?php } // Show if recordset empty ?>
            <?php if ($totalRows_rsPhotoGalleries > 0) { // Show if recordset not empty ?>
            <select name="photoGallerySelect"  id="photoGallerySelect" class="form-control" onChange="document.getElementById('photogalleryID').value=this.value">
              <option value="" <?php if (!(strcmp("", $row_rsNews['photogalleryID']))) {echo "selected=\"selected\"";} ?>>None</option>
              <?php
do {  
?>
              <option value="<?php echo $row_rsPhotoGalleries['ID']?>"<?php if (!(strcmp($row_rsPhotoGalleries['ID'], $row_rsNews['photogalleryID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsPhotoGalleries['categoryname']?></option>
              <?php
} while ($row_rsPhotoGalleries = mysql_fetch_assoc($rsPhotoGalleries));
  $rows = mysql_num_rows($rsPhotoGalleries);
  if($rows > 0) {
      mysql_data_seek($rsPhotoGalleries, 0);
	  $row_rsPhotoGalleries = mysql_fetch_assoc($rsPhotoGalleries);
  }
?>
            </select> <label><input <?php if (!(strcmp($row_rsNews['slideshow'],1))) {echo "checked=\"checked\"";} ?> name="slideshow" type="checkbox" value="1"> as slideshow</label>
            <?php } // Show if recordset not empty ?></td>
        </tr>
        <tr>
          <td class="text-nowrap text-right top">Alternative index image:</td>
          <td><?php if (isset($row_rsNews['imageURL2'])) { ?>
            <img src="<?php echo getImageURL($row_rsNews['imageURL2'],"thumb"); ?>" alt="Current image" />
            <label>
              <input name="noImage2" type="checkbox" value="1" />
              Remove image</label>
            <br />
            <?php } ?>
            <span class="upload">
              <input type="file" name="filename2" id="filename2"  />
              </span>
            <input type="hidden" name="imageURL2" value="<?php echo $row_rsNews['imageURL2']; ?>"  /></td>
        </tr>
        <tr class="longID">
          <td class="text-nowrap text-right top"><label for="imagealt">Image alt text:</label></td>
          <td><span id="sprytextfield2">
            <input name="imagealt" type="text" id="imagealt" size="50" maxlength="255" value="<?php echo htmlentities($row_rsNews['imagealt']); ?>"  class="form-control">
          </span></td>
        </tr>
        <tr>
          <td class="text-nowrap text-right top">Embed media:</td>
          <td><input name="youtube" type="text"  id="youtube" value="<?php echo htmlentities($row_rsNews['youtube']); ?>" size="50" placeholder="e.g. YouTube, Soundcloud"  class="form-control"/></td>
        </tr>
        <tr>
          <td class="text-nowrap text-right top">Redirect to URL</td>
          <td><input name="redirectURL" type="text" id="redirectURL" value="<?php echo $row_rsNews['redirectURL']; ?>" size="50" maxlength="255"  class="form-control"/></td>
        </tr>
        <tr class="region">
          <td class="text-nowrap text-right">Site:</td>
          <td><select name="regionID"  id="regionID"  class="form-control">
            <option value="" <?php if (!(strcmp("", $row_rsNews['regionID']))) {echo "selected=\"selected\"";} ?>><?php echo isset($region['text_choose']) ? htmlentities($region['text_choose'], ENT_COMPAT, "UTF-8") : "Choose..." ?></option>
            <option value="0" <?php if (!(strcmp(0, $row_rsNews['regionID']))) {echo "selected=\"selected\"";} ?>>All Sites</option>
            <?php
do {  
?>
            <option value="<?php echo $row_rsRegions['ID']?>"<?php if (!(strcmp($row_rsRegions['ID'], $row_rsNews['regionID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsRegions['title']?></option>
            <?php
} while ($row_rsRegions = mysql_fetch_assoc($rsRegions));
  $rows = mysql_num_rows($rsRegions);
  if($rows > 0) {
      mysql_data_seek($rsRegions, 0);
	  $row_rsRegions = mysql_fetch_assoc($rsRegions);
  }
?>
          </select></td>
        </tr><tr>
          <td class="text-nowrap text-right top">&nbsp;</td>
          <td><button type="submit" class="btn btn-primary" >Save Changes</button></td>
        </tr>
      </table>
  </div>
    <div class="TabbedPanelsContent">
      <table class="form-table">
        <tr>
          <th scope="row" class="top text-right">Page Title:</th>
          <td> <input name="pagetitle" type="text" id="pagetitle" value="<?php echo $row_rsNews['pagetitle']; ?>" size="50" maxlength="255"  class="seo-length form-control"/></td>
        </tr>
        <tr class="longID">
          <td class="text-nowrap text-right top">URL name:</td>
          <td><input name="longID" type="text"  id="longID" value="<?php echo $row_rsNews['longID']; ?>" size="50" maxlength="100"  class="form-control"/></td>
        </tr>
        <tr>
          <th scope="row" class="top text-right">Meta Description:</th>
          <td> <textarea name="metadescription" cols="50" rows="5"  class="seo-length form-control" id="metadescription" ><?php echo $row_rsNews['metadescription']; ?></textarea></td>
        </tr>
        <tr>
          <th scope="row" class="top text-right">Meta Keywords:</th>
          <td><textarea name="metakeywords" cols="50" rows="5" class="form-control"  id="metakeywords" ><?php echo $row_rsNews['metakeywords']; ?></textarea></td>
        </tr><tr>
          <td class="text-nowrap text-right top">&nbsp;</td>
          <td><button type="submit" class="btn btn-primary" >Save Changes</button></td>
        </tr>
      </table>
    </div>
  </div>
</div>

      <input type="hidden" name="email" value="<?php echo $row_rsLoggedIn['email']; ?>" />
      <input type="hidden" name="fullname" value="<?php echo $row_rsLoggedIn['firstname']." ".$row_rsLoggedIn['surname']; ?>" />
      
      
   <input type="hidden" name="modifiedbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
  <input name="modifieddatetime" type="hidden" id="modifieddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
  <input type="hidden" name="MM_update" value="form1">
  <input type="hidden" name="ID" id="newsID" value="<?php echo $row_rsNews['ID']; ?>" />
  <input type="hidden" name="sectionID" value="<?php echo $row_rsNews['sectionID']; ?>" />
 
  
</form>
<p><em class="text-muted">Created at <?php echo date('g.ia', strtotime($row_rsNews['posteddatetime'])); ?> on <?php echo date('l jS F Y', strtotime($row_rsNews['posteddatetime'])); echo isset($row_rsNews['createdby']) ? " by ".$row_rsNews['createdby'] : ""; ?></em></p>
<p><em class="text-muted"><?php echo isset($row_rsNews['modifiedby']) ? "Last modified at ".date('g.ia', strtotime($row_rsNews['modifieddatetime']))." on ".date('l jS F Y', strtotime($row_rsNews['modifieddatetime']))." by ".$row_rsNews['modifiedby'] : ""; ?></em></p>
<script>
<!--
var sprytextfield2 = new Spry.Widget.ValidationTextField("sprytextfield2", "none", {hint:"(by default title of Post)", isRequired:false});
//-->
</script>
<?php 
	if (isset($_GET['defaultTab'])) { echo '<script>
<!--
var TabbedPanels1 = new Spry.Widget.TabbedPanels("TabbedPanels1", {defaultTab:'.intval($_GET['defaultTab']).'});
//-->
    </script>'; } else { ?>
    <script>
<!--
var TabbedPanels1 = new Spry.Widget.TabbedPanels("TabbedPanels1");
//-->
    </script>
    <?php  } ?>
    
<?php } else { // not authorised to edit ?>
<p class="message alert alert-info" role="alert">Sorry, you are not able to edit this page. You need to be a manager, editor for the same site or the originator of the story.</p>
<?php } ?></div>
   <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsNews);

mysql_free_result($rsStatus);

mysql_free_result($rsPreferences);

mysql_free_result($rsRegions);

mysql_free_result($rsPhotoGalleries);

mysql_free_result($rsSections);

mysql_free_result($rsThisSection);

mysql_free_result($rsAllTags);

mysql_free_result($rsNewsPrefs);
?>
