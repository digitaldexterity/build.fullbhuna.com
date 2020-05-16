<?php require_once('../../Connections/aquiescedb.php'); ?><?php require_once('../../core/includes/framework.inc.php'); ?>
<?php require_once('../../core/includes/upload.inc.php'); ?>
<?php 
ini_set('memory_limit', '800M' ); 
set_time_limit(1200); // 20 mins
ini_set("max_execution_time",1200);
$max_upload = ini_get('post_max_size') > ini_get('upload_max_filesize') ? ini_get('upload_max_filesize') : ini_get('post_max_size');

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


if($_POST['method']==2) { require_once('../includes/video_upload.php'); } 

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {

$uploaded = getUploads();
	if (is_array($uploaded)) {
		if(isset($uploaded["filename"][0]["newname"]) && $uploaded["filename"][0]["newname"]!="") { 
			$_POST['previewURL'] = $uploaded["filename"][0]["newname"]; 
		}
		if(isset($uploaded["thumb"][0]["newname"]) && $uploaded["thumb"][0]["newname"]!="") { 
			$_POST['imageURL'] = $uploaded["thumb"][0]["newname"]; 
		}
	}
	}


if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE video SET videotitle=%s, videodescription=%s, imageURL=%s, previewURL=%s, method=%s, videoURL=%s, originalFile=%s, mimetype=%s, modifiedbyID=%s, modifieddatetime=%s, statusID=%s, categoryID=%s, videoheight=%s, videowidth=%s WHERE ID=%s",
                       GetSQLValueString($_POST['videotitle'], "text"),
                       GetSQLValueString($_POST['videodescription'], "text"),
                       GetSQLValueString($_POST['imageURL'], "text"),
                       GetSQLValueString($_POST['previewURL'], "text"),
                       GetSQLValueString($_POST['method'], "int"),
                       GetSQLValueString($_POST['videoURL'], "text"),
                       GetSQLValueString($_POST['filename'], "text"),
                       GetSQLValueString($_POST['mimetype'], "text"),
                       GetSQLValueString($_POST['modifiedbyID'], "int"),
                       GetSQLValueString($_POST['modifieddatetime'], "date"),
                       GetSQLValueString($_POST['statusID'], "int"),
                       GetSQLValueString($_POST['categoryID'], "int"),
                       GetSQLValueString($_POST['videoheight'], "int"),
                       GetSQLValueString($_POST['videowidth'], "int"),
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
  header(sprintf("Location: %s", $updateGoTo));exit;
}


$colname_rsLoggedIn = "1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsVideoCategories = "SELECT ID, categoryname FROM videocategory WHERE statusID = 1 ORDER BY ordernum, categoryname ASC";
$rsVideoCategories = mysql_query($query_rsVideoCategories, $aquiescedb) or die(mysql_error());
$row_rsVideoCategories = mysql_fetch_assoc($rsVideoCategories);
$totalRows_rsVideoCategories = mysql_num_rows($rsVideoCategories);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsStatus = "SELECT * FROM status";
$rsStatus = mysql_query($query_rsStatus, $aquiescedb) or die(mysql_error());
$row_rsStatus = mysql_fetch_assoc($rsStatus);
$totalRows_rsStatus = mysql_num_rows($rsStatus);

$colname_rsVideo = "-1";
if (isset($_GET['videoID'])) {
  $colname_rsVideo = $_GET['videoID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsVideo = sprintf("SELECT * FROM video WHERE ID = %s", GetSQLValueString($colname_rsVideo, "int"));
$rsVideo = mysql_query($query_rsVideo, $aquiescedb) or die(mysql_error());
$row_rsVideo = mysql_fetch_assoc($rsVideo);
$totalRows_rsVideo = mysql_num_rows($rsVideo);
?><!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" --><title><?php $pageTitle = "Update video"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title><!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<?php if(!defined("FFMPEG_PATH") || !is_readable(FFMPEG_PATH)) { echo "<style> .advanced {display:none;} </style>"; } ?>
<script src="../../core/scripts/formUpload.js"></script>
<script src="../../SpryAssets/SpryTabbedPanels.js"></script>
<link href="../../SpryAssets/SpryTabbedPanels.css" rel="stylesheet"  />
  <script>
 function validateForm(form)
 {
  

  var errors = "";
 if (document.form1.videotitle.value == "") errors = errors + "Please enter a title.\n";
  if (document.form1.method.value==1 && (document.form1.videoURL.value.indexOf('<iframe') < 0 || document.form1.videoURL.value.indexOf('iframe>') < 0)) errors = errors + "Please enter the full embed code. For YouTube, this will begin with '<iframe' and end with 'iframe>'\n";
   
   return errors;
 }
 
 function setState(methodID) {
	 if(methodID==1) { // EMBED OR NOT
	 document.getElementById('text_embed').style.display='inline';
	 document.getElementById('text_file').style.display='none'; 
	 } else { 
	 document.getElementById('text_embed').style.display='none';
	 document.getElementById('text_file').style.display='inline'; 
	 } 
	 if(methodID==2) { // UPLOAD OR NOT
	 document.getElementById('videofile').style.display='inline';
	 document.getElementById('videoURL').style.display='none'; 
	 document.getElementById('autothumb').style.display='inline';
	  document.getElementById('autothumbnail').checked=true;
	  document.getElementById('thumbnailinput').style.display='none';
	 } else {
		 document.getElementById('videofile').style.display='none';
		 document.getElementById('videoURL').style.display='inline';
		  document.getElementById('autothumb').style.display='none';
		  document.getElementById('thumbnailinput').style.display='inline';
		 }
 }
   </script>
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
        <div class="page video">
   <h1><i class="glyphicon glyphicon-film"></i> Update Video   </h1><?php require_once('../../core/includes/alert.inc.php'); ?>
    
   
     <form action="<?php echo $editFormAction; ?>" method="post" enctype="multipart/form-data" name="form1" id="form1" >
    <div id="TabbedPanels1" class="TabbedPanels">
       <ul class="TabbedPanelsTabGroup">
         <li class="TabbedPanelsTab" tabindex="0">Video</li>
         <li class="TabbedPanelsTab advanced" tabindex="0">Advanced</li>
       </ul>
       <div class="TabbedPanelsContentGroup">
         <div class="TabbedPanelsContent"><table class="form-table"> <tr>
      <td class="text-nowrap text-right">Category:</td>
      <td><label>
        <select name="categoryID"  id="categoryID">
          <option value="1" <?php if (!(strcmp(1, $row_rsVideo['categoryID']))) {echo "selected=\"selected\"";} ?>><?php echo isset($region['text_choose']) ? htmlentities($region['text_choose'], ENT_COMPAT, "UTF-8") : "Choose..." ?></option>
          <?php
do {  
?><option value="<?php echo $row_rsVideoCategories['ID']?>"<?php if (!(strcmp($row_rsVideoCategories['ID'], $row_rsVideo['categoryID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsVideoCategories['categoryname']?></option>
          <?php
} while ($row_rsVideoCategories = mysql_fetch_assoc($rsVideoCategories));
  $rows = mysql_num_rows($rsVideoCategories);
  if($rows > 0) {
      mysql_data_seek($rsVideoCategories, 0);
	  $row_rsVideoCategories = mysql_fetch_assoc($rsVideoCategories);
  }
?>
          </select>
        <a href="categories/index.php">Manage Categories</a></label></td>
      </tr> <tr>
      <td class="text-nowrap text-right">Video title:</td>
      <td><input name="videotitle" type="text"  value="<?php echo $row_rsVideo['videotitle']; ?>" size="32" /></td>
      </tr> <tr>
      <td class="text-nowrap text-right top">Description:</td>
      <td><textarea name="videodescription" cols="50" rows="5"><?php echo $row_rsVideo['videodescription']; ?></textarea>         </td>
      </tr>
        
    <tr class="upload">
      <td class="text-nowrap text-right">Method:</td>
      <td><select name="method"  id="method"  onchange="setState(this.value);">
        <option value="1" <?php if (!(strcmp(1, $row_rsVideo['method']))) {echo "selected=\"selected\"";} ?>>Embed (e.g. YouTube)</option>
       <option value="2" <?php if (!(strcmp(2, $row_rsVideo['method']))) {echo "selected=\"selected\"";} ?>>Upload</option>
       <option value="3" <?php if (!(strcmp(3, $row_rsVideo['method']))) {echo "selected=\"selected\"";} ?>>URL</option>
        </select></td>
    </tr> <tr>
      <td class="text-nowrap text-right"><span id="text_embed">Embed Code:</span><span id="text_file">Video file:</span></td>
      <td><input name="videoURL" type="text"  id="videoURL" value="<?php echo isset($_POST['videoURL']) ? $_POST['videoURL'] : htmlentities($row_rsVideo['videoURL']); ?>" size="50" />
        <input type="file" class="fileinput upload" name="videofile" id="videofile"  />  (<?php echo $max_upload ; ?>  max)</td>
    </tr><tr>
          <td class="text-nowrap text-right top">Current preview:</td>
          <td class="top">
            <?php if (is_readable(SITE_ROOT.getImageURL($row_rsVideo['previewURL'],"thumb"))) { ?><img src="<?php echo getImageURL($row_rsVideo['previewURL'],"thumb"); ?>" alt="Current image" /><?php } else { ?>
              <img src="/video/images/video_icon.png" width="110" height="90" alt="Default thumbnail" />
              <?php } ?> <input name="previewURL" type="hidden" id="previewURL" value="<?php echo $row_rsVideo['previewURL']; ?>" /></td>
          </tr> <tr>
      <td class="text-nowrap text-right">Preview:</td>
      <td><span id="autothumb" class="advanced">
        <input type="checkbox" name="autothumbnail" id="autothumbnail" <?php if(is_readable(FFMPEG_PATH)) { ?>checked="checked"<?php } ?> onClick="if(this.checked === true) { document.getElementById('thumbnailinput').style.display = 'none'; document.getElementById('filename').value=''; } else { document.getElementById('thumbnailinput').style.display = 'inline'; }" />
        Automatically generate preview image<br />
        </span> <span id="thumbnailinput">
          <input type="file" class="fileinput upload" name="filename" id="filename"  />
          (optional)</span></td>
    </tr> <tr>
      <td class="text-nowrap text-right top">Current thumb:</td>
      <td><?php if (is_readable(SITE_ROOT.getImageURL($row_rsVideo['imageURL'],"thumb"))) { ?><img src="<?php echo getImageURL($row_rsVideo['imageURL']."thumb"); ?>" alt="Current image" /><?php } else  if (is_readable(SITE_ROOT.getImageURL($row_rsVideo['previewURL'],"thumb"))) { ?><img src="<?php echo getImageURL($row_rsVideo['previewURL'],"thumb"); ?>" alt="Current image" /><?php }  ?> <input name="imageURL" type="hidden" id="imageURL" value="<?php echo $row_rsVideo['imageURL']; ?>"></td>
    </tr>
    <tr class="upload">
      <td class="text-nowrap text-right">Thumb:</td>
      <td><input type="file" class="fileinput upload" name="thumb" id="thumb"   />
        (optional)        </td>
    </tr> <tr>
      <td class="text-nowrap text-right">Status:</td>
      <td><label>
        <select name="statusID"  id="statusID">
          <?php
do {  
?><option value="<?php echo $row_rsStatus['ID']?>"<?php if (!(strcmp($row_rsStatus['ID'], $row_rsVideo['statusID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsStatus['description']?></option>
          <?php
} while ($row_rsStatus = mysql_fetch_assoc($rsStatus));
  $rows = mysql_num_rows($rsStatus);
  if($rows > 0) {
      mysql_data_seek($rsStatus, 0);
	  $row_rsStatus = mysql_fetch_assoc($rsStatus);
  }
?>
          </select>
        </label></td>
    </tr>
   
    </table></div>
         <div class="TabbedPanelsContent">
           <p>Any relacement video you upload will inherit the following properties:
</p>
           
           <table border="0" cellpadding="0" cellspacing="0" class="form-table">
             <tr>
               <td align="right">Video Height:</td>
               <td><input name="videoheight" type="text"  id="videoheight" value="<?php echo VIDEO_DEFAULT_HEIGHT; ?> " size="6" maxlength="4" />
                 pixels</td>
             </tr>
             <tr>
               <td align="right">Video Width:</td>
               <td><input name="videowidth" type="text"  id="videowidth" value="<?php echo VIDEO_DEFAULT_WIDTH; ?> " size="6" maxlength="4" />
                 pixels</td>
             </tr>
             <tr>
               <td align="right">Video Bitrate:</td>
               <td><input name="videobitrate" type="text"  id="videobitrate" value="<?php echo VIDEO_DEFAULT_BITRATE; ?> " size="6" maxlength="4" />
                 kilobits per second </td>
             </tr>
             <tr>
               <td align="right">Audio Bitrate:</td>
               <td><input name="audiobitrate" type="text"  id="audiobitrate" value="<?php echo AUDIO_DEFAULT_BITRATE; ?> " size="6" maxlength="4" />
                 kilobits per second </td>
             </tr>
           </table>
         </div>
       </div>
    </div>

<p><button type="submit" class="btn btn-primary" >Save changes</button></p>
  <input name="modifiedbyID" type="hidden" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
  <input name="modifieddatetime" type="hidden" id="modifieddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
  <input name="ID" type="hidden" id="ID" value="<?php echo $row_rsVideo['ID']; ?>" />
  <input type="hidden" name="MM_update" value="form1" />
  <input type="hidden" name="mimetype" id="mimetype" />
    </form>
 
<script>
<!--
 setState(<?php echo isset($_POST['method']) ? $_POST['method'] : $row_rsVideo['method']; ?>);
var TabbedPanels1 = new Spry.Widget.TabbedPanels("TabbedPanels1");
//-->
   </script></div>
  <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsVideoCategories);

mysql_free_result($rsStatus);

mysql_free_result($rsVideo);
?>


