<?php //print_r($_POST); die("*"); 
ini_set('memory_limit', '800M' ); 
set_time_limit(1200); // 20 mins
ini_set("max_execution_time",1200);
$max_upload = ini_get('post_max_size') > ini_get('upload_max_filesize') ? ini_get('upload_max_filesize') : ini_get('post_max_size');
//$max_upload = str_replace("M","000000",$max_upload);





require_once('../../Connections/aquiescedb.php'); ?>
<?php require_once('../../core/includes/upload.inc.php'); ?>
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

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {


	if($_POST['method']==2) { 
		require_once('../includes/video_upload.php'); 
	} 


	$uploaded = getUploads();
	if (isset($uploaded) && is_array($uploaded)) {
		if(isset($uploaded["filename"][0]["newname"]) && $uploaded["filename"][0]["newname"]!="") { 
			$_POST['previewURL'] = $uploaded["filename"][0]["newname"]; 
		}
		if(isset($uploaded["filename1"][0]["newname"]) && $uploaded["filename1"][0]["newname"]!="") { 
			$_POST['imageURL'] = $uploaded["thumb"][0]["newname"]; 
		}
	}


}
if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
  $insertSQL = sprintf("INSERT INTO video (videotitle, videodescription, previewURL, method, videoURL, mimetype, createdbyID, createddatetime, statusID, categoryID, videoheight, videowidth) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['videotitle'], "text"),
                       GetSQLValueString($_POST['videodescription'], "text"),
                       GetSQLValueString($_POST['previewURL'], "text"),
                       GetSQLValueString($_POST['method'], "int"),
                       GetSQLValueString($_POST['videoURL'], "text"),
                       GetSQLValueString($_POST['mimetype'], "text"),
                       GetSQLValueString($_POST['createdbyID'], "int"),
                       GetSQLValueString($_POST['createddatetime'], "date"),
                       GetSQLValueString($_POST['statusID'], "int"),
                       GetSQLValueString($_POST['categoryID'], "int"),
                       GetSQLValueString($_POST['videoheight'], "int"),
                       GetSQLValueString($_POST['videowidth'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($insertSQL, $aquiescedb) or die(mysql_error());
}
if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) { 
  $insertGoTo = "index.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
    $insertGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $insertGoTo));exit;
}

$colname_rsLoggedIn = "1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID FROM users WHERE username = '%s'", $colname_rsLoggedIn);
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsVideoCategories = "SELECT ID, categoryname FROM videocategory WHERE statusID = 1 ORDER BY ordernum, categoryname ASC";
$rsVideoCategories = mysql_query($query_rsVideoCategories, $aquiescedb) or die(mysql_error());
$row_rsVideoCategories = mysql_fetch_assoc($rsVideoCategories);
$totalRows_rsVideoCategories = mysql_num_rows($rsVideoCategories);


			
			
?><!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" --><title><?php $pageTitle = "Add video"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title><!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" --><script src="../../core/scripts/formUpload.js"></script>
<?php if(!(defined("FFMPEG_PATH") && is_readable(FFMPEG_PATH))) { echo "<style> .advanced {display:none;} </style>"; } ?>
<script src="../../SpryAssets/SpryTabbedPanels.js"></script>
<link href="../../SpryAssets/SpryTabbedPanels.css" rel="stylesheet"  /> <script>
 function validateForm(form)
 {
  

  var errors = "";
 if (document.form1.videotitle.value == "") errors = errors + "Please enter a title.\n";
  if (document.form1.method.value==1 && (document.form1.videoURL.value.indexOf('<iframe') < 0 || document.form1.videoURL.value.indexOf('iframe>') < 0)) errors = errors + "Please enter the full embed code. For YouTube, this will begin with '<iframe' and end with 'iframe>'\n";
  if (document.form1.videofile.value == "" && document.form1.method.value==2) errors = errors + "Please choose a file.\n";
 
 
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
 
   <h1><i class="glyphicon glyphicon-film"></i> <img src="/core/images/icons-large/applications-multimedia.png" alt="Video" width="32" height="32" style="vertical-align:
middle;" /> Add Video</h1><?php require_once('../../core/includes/alert.inc.php'); ?>
  
     
   <form action="<?php echo $editFormAction; ?>" method="post" enctype="multipart/form-data" name="form1" id="form1"  >
   <div id="TabbedPanels1" class="TabbedPanels">
     <ul class="TabbedPanelsTabGroup">
       <li class="TabbedPanelsTab" tabindex="0">Video</li>
       <li class="TabbedPanelsTab advanced" tabindex="0">Advanced</li>
     </ul>
     <div class="TabbedPanelsContentGroup">
       <div class="TabbedPanelsContent">
     <table class="form-table"> <tr>
         <td class="text-nowrap text-right">Category:</td>
         <td><label>
           <select name="categoryID"  id="categoryID">
             <option value="1" <?php if (!(strcmp(1, @$_POST['categoryID']))) {echo "selected=\"selected\"";} ?>><?php echo isset($region['text_choose']) ? htmlentities($region['text_choose'], ENT_COMPAT, "UTF-8") : "Choose..." ?></option>
             <?php
do {  
?>
             <option value="<?php echo $row_rsVideoCategories['ID']?>"<?php if ($row_rsVideoCategories['ID']==$_POST['categoryID']) {echo "selected=\"selected\"";} ?>><?php echo $row_rsVideoCategories['categoryname']?></option>
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
         <td><input name="videotitle" type="text"  value="<?php echo isset($_POST['videotitle']) ? htmlentities($_POST['videotitle'], ENT_COMPAT, "UTF-8") : ""; ?>" size="32" /></td>
       </tr> <tr>
         <td class="text-nowrap text-right top">Description:</td>
         <td><textarea name="videodescription" cols="50" rows="5"><?php echo isset($_POST['videodescription']) ? htmlentities($_POST['videodescription'], ENT_COMPAT, "UTF-8") : ""; ?></textarea>         </td>
       </tr>
       <tr class="upload">
         <td class="text-nowrap text-right">Method:</td>
         <td><select name="method"  id="method" onChange="setState(this.value);">
           <option value="1" <?php if (!(strcmp(1, @$_POST['method']))) {echo "selected=\"selected\"";} ?>>Embed (e.g. YouTube)</option>
         <option value="2" <?php if (!(strcmp(2, @$_POST['method']))) {echo "selected=\"selected\"";} ?>>Upload</option>
          <option value="3" <?php if (!(strcmp(3, @$_POST['method']))) {echo "selected=\"selected\"";} ?>>URL</option>
         </select></td>
       </tr> <tr>
         <td class="text-nowrap text-right"><span id="text_embed">Embed Code:</span><span id="text_file">Video file:</span></td>
         <td><input name="videoURL" type="text"  id="videoURL" value="<?php echo isset($_POST['videoURL']) ? htmlentities($_POST['videoURL'], ENT_COMPAT, "UTF-8") : ""; ?>" size="50" /><input type="file" class="fileinput upload" name="videofile" id="videofile"  /> (<?php echo $max_upload ; ?>  max)
           <input type="hidden" name="previewURL" id="previewURL" /></td>
       </tr>
       
       <tr class="upload">
         <td class="text-nowrap text-right">Preview:</td>
         <td><span id="autothumb" class="advanced"><input type="checkbox" name="autothumbnail" id="autothumbnail" <?php if(is_readable(FFMPEG_PATH)) { ?>checked="checked"<?php } ?> onClick="if(this.checked === true) { document.getElementById('thumbnailinput').style.display = 'none'; document.getElementById('filename').value=''; } else { document.getElementById('thumbnailinput').style.display = 'inline'; }" />
           Automatically generate preview image<br /></span>
  <span id="thumbnailinput"><input type="file" class="fileinput upload" name="filename" id="filename" />
           (optional)</span></td>
       </tr>
       <tr class="upload">
         <td class="text-nowrap text-right">Thumb:</td>
         <td><input type="file" class="fileinput upload" name="thumb" id="thumb" />
           (optional - will use preview above if available)
             <input type="hidden" name="imageURL" id="imageURL"></td>
       </tr>

      
     </table>
     <input name="createdbyID" type="hidden" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
     <input type="hidden" name="createddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
     <input type="hidden" name="statusID" value="1" />
     <input type="hidden" name="MM_insert" value="form1" /><input type="hidden" name="mimetype" id="mimetype" />
   </div>
       <div class="TabbedPanelsContent">
       <p>Do not change these default values unless you know what you're doing!</p>
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
         <p>Multiple or very large uploads? <a href="/ftp/index.php">Click here</a>.</p>
       </div>
     </div>
   </div>
  
<p><input type="submit" class="button" value="Add Video" /></p></form>
   
<script>
<!--
 setState(<?php echo isset($_POST['method']) ? $_POST['method'] : 1; ?>);

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

mysql_free_result($rsVideoCategories);
?>


