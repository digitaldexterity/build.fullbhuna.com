<?php require_once('../../Connections/aquiescedb.php'); ?><?php require_once('../includes/newsfunctions.inc.php'); ?>
<?php require_once('../../core/includes/upload.inc.php'); ?><?php require_once('../../mail/includes/sendmail.inc.php'); ?>
<?php $_GET['sectionID'] = isset($_GET['sectionID']) ? $_GET['sectionID'] : 1; $regionID = isset($regionID) ? $regionID : 1; ?>
<?php 
if (!isset($_SESSION)) {
  session_start();
}$MM_authorizedUsers = "1,2,3,4,5,6,7,8,9,10";
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

$varSectionID_rsThisSection = "-1";
if (isset($_GET['sectionID'])) {
  $varSectionID_rsThisSection = $_GET['sectionID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsThisSection = sprintf("SELECT * FROM newssection WHERE ID = %s", GetSQLValueString($varSectionID_rsThisSection, "int"));
$rsThisSection = mysql_query($query_rsThisSection, $aquiescedb) or die(mysql_error());
$row_rsThisSection = mysql_fetch_assoc($rsThisSection);
$totalRows_rsThisSection = mysql_num_rows($rsThisSection);



$colname_rsLoggedIn = "1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID,  firstname, surname, usertypeID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

$accesslevel = $row_rsThisSection['accesslevel'];
$groupID = $row_rsThisSection['groupwriteID'];
require_once('../../members/includes/restrictaccess.inc.php');


if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
	
	$_POST['status'] = (($row_rsThisSection['requiresapproval']==0 && $row_rsLoggedIn['usertypeID'] >= $row_rsThisSection['editaccess']) || $row_rsLoggedIn['usertypeID'] >=7) ? 1 : 0;
	
	
	$_POST['longID'] = preg_replace("/[^a-zA-Z0-9_\-]/", "", $_POST['longID']); // clean
	if($row_rsThisSection['statusID']!=1 || $_SESSION['MM_UserGroup'] <$row_rsThisSection['editaccess']) {
		unset($_POST["MM_insert"]);
 	} else {
		$_POST['status'] = ($row_rsThisSection['requiresapproval']==1) ? 0 : 1;
		$uploaded = getUploads(UPLOAD_ROOT,$image_sizes,"",0,0,"",array("jpg","jpeg","png","gif","pdf","doc","docx","xls","xlsx","ppt","pptx","rtf","txt"));
		
		if (isset($uploaded) && is_array($uploaded) && isset($uploaded["filename"][0]["error"])) { 
			$submit_error = $uploaded["filename"][0]["error"]; 
			unset($_POST["MM_insert"]);
		} else if (isset($uploaded) && is_array($uploaded) && $uploaded["filename"][0]["newname"]!="") { 
			$_POST['imageURL'] = $uploaded["filename"][0]["newname"]; 
		}
		
		if (isset($uploaded) && is_array($uploaded) && isset($uploaded["filename1"][0]["error"])) { 
			$submit_error = $uploaded["filename1"][0]["error"]; 
			unset($_POST["MM_insert"]);
		} else if (isset($uploaded) && is_array($uploaded) && $uploaded["filename1"][0]["newname"]!="") { 
			$_POST['attachment1'] = $uploaded["filename1"][0]["newname"]; 
		}
		
 	}
}


if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
  $insertSQL = sprintf("INSERT INTO news (title, summary, body, status, postedbyID, imageURL, posteddatetime, regionID, sectionID, longID, metadescription, metakeywords, modifiedbyID, modifieddatetime, attachment1) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['title'], "text"),
                       GetSQLValueString($_POST['summary'], "text"),
                       GetSQLValueString($_POST['body'], "text"),
                       GetSQLValueString($_POST['status'], "int"),
                       GetSQLValueString($_POST['postedbyID'], "int"),
                       GetSQLValueString($_POST['imageURL'], "text"),
                       GetSQLValueString($_POST['posteddatetime'], "date"),
                       GetSQLValueString($_POST['regionID'], "int"),
                       GetSQLValueString($_POST['sectionID'], "int"),
                       GetSQLValueString($_POST['longID'], "text"),
                       GetSQLValueString($_POST['metadescription'], "text"),
                       GetSQLValueString($_POST['metakeywords'], "text"),
                       GetSQLValueString($_POST['postedbyID'], "int"),
                       GetSQLValueString($_POST['posteddatetime'], "date"),
                       GetSQLValueString($_POST['attachment1'], "text"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($insertSQL, $aquiescedb) or die(mysql_error());
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
	
	
	
$insertGoTo = isset($_GET['returnURL']) ? $_GET['returnURL'] : "/news/";
  header(sprintf("Location: %s", $insertGoTo));exit;
}



mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsStatus = "SELECT * FROM status";
$rsStatus = mysql_query($query_rsStatus, $aquiescedb) or die(mysql_error());
$row_rsStatus = mysql_fetch_assoc($rsStatus);
$totalRows_rsStatus = mysql_num_rows($rsStatus);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = "SELECT * FROM preferences";
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsRegions = "SELECT ID, title FROM region WHERE statusID = 1 ORDER BY title ASC";
$rsRegions = mysql_query($query_rsRegions, $aquiescedb) or die(mysql_error());
$row_rsRegions = mysql_fetch_assoc($rsRegions);
$totalRows_rsRegions = mysql_num_rows($rsRegions);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPhotoGalleries = "SELECT ID, categoryname FROM photocategories WHERE active = 1 ORDER BY categoryname ASC";
$rsPhotoGalleries = mysql_query($query_rsPhotoGalleries, $aquiescedb) or die(mysql_error());
$row_rsPhotoGalleries = mysql_fetch_assoc($rsPhotoGalleries);
$totalRows_rsPhotoGalleries = mysql_num_rows($rsPhotoGalleries);




?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Submit a Post"; echo $pageTitle." | ".$site_name; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<meta name="robots" content="noindex,nofollow" /><script src="/core/scripts/date-picker/js/datepicker.js"></script>
<script src="/core/scripts/formUpload.js"></script>
<script src="../../SpryAssets/SpryValidationTextarea.js"></script>
<script src="../../SpryAssets/SpryValidationTextField.js"></script>

<link href="../../SpryAssets/SpryValidationTextarea.css" rel="stylesheet"  />
<link href="../../SpryAssets/SpryValidationTextField.css" rel="stylesheet"  />
<style><!--
<?php if ($row_rsThisSection['allowbody']!=1) { echo "#row_body { display:none; } "; } ?>
<?php if ($row_rsThisSection['allowphoto']!=1) { echo "#row_image { display:none; } "; } ?>
<?php if ($row_rsThisSection['allowattachment']!=1) { echo "#row_attachment { display:none; } "; } ?>
--></style>
<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" -->
   <div class="page news">
    <h1>Post an item</h1>
    <?php if($row_rsThisSection['statusID']==1) { // can post ?>
    <?php if($row_rsThisSection['requiresapproval']==1 && $row_rsLoggedIn['usertypeID']<7) { ?>
    <p>All Posts will be moderated by an editor before they go live.</p><?php } ?>
    <?php if(isset($submit_error)) { ?>
    <p class="alert alert-danger" role="alert"><?php echo $submit_error; ?></p>
    <?php } ?>
    
    <form action="<?php echo $editFormAction; ?>" method="post" enctype="multipart/form-data" name="form1" id="form1"  >
  
            <table class="form-table"> <tr>
                <td class="text-nowrap text-right">Title:</td>
                <td><span id="sprytextfield1">
                  <input name="title" id="title" type="text"  value="<?php echo isset($_POST['title']) ?  htmlentities($_POST['title'], ENT_COMPAT, "UTF-8") : ""; ?>" size="50" maxlength="100" onBlur="seoPopulate(this.value, document.getElementById('summary').value);"  class="form-control" />
                <span class="textfieldRequiredMsg">A title is required.</span></span></td>
              </tr> <tr>
                <td class="text-nowrap text-right top">Summary:<br /></td>
                <td><span id="sprytextarea1">
                  <textarea name="summary" id="summary" cols="70" rows="5"  onblur="seoPopulate(document.getElementById('title').value, this.value);" class="form-control" ><?php echo isset($_POST['summary']) ? htmlentities($_POST['summary'], ENT_COMPAT, "UTF-8") : ""; ?></textarea><br /><span class="textareaRequiredMsg">A summary is required.</span></span></td>
              </tr>
              <tr id="row_body">
                <td class="text-nowrap text-right top">Body:</td>
                <td><span id="sprytextarea2"><textarea name="body" cols="70" rows="20" class="form-control"><?php echo isset($_POST['body']) ?  htmlentities($_POST['body'], ENT_COMPAT, "UTF-8") : ""; ?></textarea>
</span></td>
              </tr>
             
              <tr class="upload"  id="row_image">
                <td class="text-nowrap text-right"> Optional Image:</td>
                <td><input name="filename" type="file" class="fileinput" id="filename" size="20" /> 
                (JPEG, GIF or PNG format)</td>
              </tr>
            
              <tr id="row_attachment" class="upload" >
                <td class="text-nowrap text-right"><label for="attachment">Optional attachment:</label></td>
                <td><input name="filename1" type="file" id="filename1" size="20"  />
                (e.g. MP4 movie, PDF, Word, Excel or Powerpoint)
                  <input type="hidden" name="attachment1" id="attachment1" /></td>
              </tr> <tr>
                <td class="text-nowrap text-right">Important:</td>
                <td>Please ensure that your post does not contain anything inappropriate or liable to offend anyone.</td>
              </tr> <tr>
                <td class="text-nowrap text-right">Posted by <?php echo htmlentities($row_rsLoggedIn['firstname']." ".$row_rsLoggedIn['surname'], ENT_COMPAT, "UTF-8"); ?></td>
                <td><div><button type="submit" class="btn btn-primary"  onClick="msg = 'Are you ready to post this item?\n\nPlease ensure that your post does not contain anything inappropriate or liable to offend anyone.';if(document.getElementById('sendemail').checked) { msg += '\n\nNOTE: This is being sent via email and therefore cannot be retracted'; } return confirm(msg);">Submit</button></div></td>
              </tr>
            </table>
         
      <input type="hidden" name="postedbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
      <input type="hidden" name="email" value="<?php echo htmlentities($row_rsLoggedIn['email'], ENT_COMPAT, "UTF-8"); ?>" />
      <input type="hidden" name="fullname" value="<?php echo htmlentities($row_rsLoggedIn['firstname']." ".$row_rsLoggedIn['surname'], ENT_COMPAT, "UTF-8"); ?>" />
      <input name="posteddatetime" type="hidden" id="posteddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
      <input name="imageURL" type="hidden" id="imageURL" />
      <input type="hidden" name="MM_insert" value="form1" />
      <input type="hidden" name="metakeywords" id="metakeywords" />
      <input type="hidden" name="metadescription" id="metadescription"/>
      <input name="longID" type="hidden" id="longID" size="50"  />
      
      <input name="regionID" type="hidden" id="regionID" value="<?php echo isset($regionID) ? $regionID : 0; ?>" />
      <input name="sectionID" type="hidden" id="sectionID" value="<?php echo intval($_GET['sectionID']); ?>" /><input name="status" type="hidden"  />
<div></div>
    </form>
    
    <?php 
	} else { // cannot post ?>
    <p class="alert warning alert-warning" role="alert">Sorry, you can not post  to this section right now.</p>
    <?php } ?>

    <script>
var sprytextarea1 = new Spry.Widget.ValidationTextarea("sprytextarea1", {hint:"<?php echo ($row_rsThisSection['allowbody']==1) ? "Enter first paragraph"  : ""; ?>"});
var sprytextarea2 = new Spry.Widget.ValidationTextarea("sprytextarea2", {hint:"Enter remainder of text", isRequired:false});
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1");
    </script></div>
  <!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsStatus);

mysql_free_result($rsPreferences);

mysql_free_result($rsRegions);

mysql_free_result($rsPhotoGalleries);

mysql_free_result($rsThisSection);

mysql_free_result($rsNewsPrefs);
?>
