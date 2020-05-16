<?php require_once('../../Connections/aquiescedb.php'); ?>
<?php require_once('../../core/includes/upload.inc.php'); ?>
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

$varUsername_rsMyGalleries = "-1";
if (isset($_SESSION['MM_Username'])) {
  $varUsername_rsMyGalleries = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsMyGalleries = sprintf("SELECT photocategories.ID, photocategories.categoryname FROM photocategories LEFT JOIN users ON (photocategories.addedbyID = users.ID) WHERE photocategories.active = 1 AND users.username = %s ORDER BY photocategories.ordernum", GetSQLValueString($varUsername_rsMyGalleries, "text"));
$rsMyGalleries = mysql_query($query_rsMyGalleries, $aquiescedb) or die(mysql_error());
$row_rsMyGalleries = mysql_fetch_assoc($rsMyGalleries);
$totalRows_rsMyGalleries = mysql_num_rows($rsMyGalleries);
?><?php $directory = UPLOAD_ROOT.DIRECTORY_SEPARATOR."temp".DIRECTORY_SEPARATOR.$_SESSION['MM_Username'];
  $directory = realpath($directory).DIRECTORY_SEPARATOR;  // removed dots
  $thumbs_directory = $directory."thumbs".DIRECTORY_SEPARATOR;
  createDirectory($thumbs_directory); ?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Manage Uploaded Photos"; echo $pageTitle." | ".$site_name; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script>
function addToGallery() {
	theForm = document.getElementById('filesform');
	if(anyChecked(theForm)) {
		if(document.getElementById('galleryID').value>0) {
			document.getElementById('formaction').value = "add";
			theForm.submit();
		} else {
			alert("Please choose a gallery to add to.");
		}
	}		
	return false;
}

function deleteItems() {
	theForm = document.getElementById('filesform');
	if(anyChecked(theForm)) {	
		if(confirm('Are you sure you want to delete the selected items?')) {
			document.getElementById('formaction').value = "delete";
			theForm.submit();
		}
	}		
	return false;
}
</script><script src="/core/scripts/checkbox/checkboxes.js"></script>
<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" -->
  <div class="page photos">
    <h1>Manage Uploaded Photos</h1>
    <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
      <li class="nav-item"><a href="javascript:void(0);" onclick="addToGallery()" class="nav-link"><i class="glyphicon glyphicon-plus-sign"></i> Add to gallery</a></li>
      <li class="nav-item"><a href="javascript:void(0);" class="nav-link" onclick="deleteItems()" ><i class="glyphicon glyphicon-cog"></i> Delete</a></li>
    </ul></div></nav>
    <form action="index.php" method="post" name="filesform" id="filesform">
      
      <input name="directory" type="hidden" id="directory" value="<?php echo $directory; ?>"  />
  


    <?php 
	
	if(is_dir($directory)) {
		$files = scandir($directory);
		if(count($files)>2) {  // is files ?>
        
    <table class="form-table"> 
      <tr>
        <td>&nbsp;</td>
        <td colspan="3"><label>
            <select name="galleryID" id="galleryID">
              <option value="" <?php if (!(strcmp("", @$_REQUEST['galleryID']))) {echo "selected=\"selected\"";} ?>>Choose gallery to add to...</option>
              <?php
do {  
?>
              <option value="<?php echo $row_rsMyGalleries['ID']?>"<?php if (!(strcmp($row_rsMyGalleries['ID'], @$_REQUEST['galleryID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsMyGalleries['categoryname']?></option>
              <?php
} while ($row_rsMyGalleries = mysql_fetch_assoc($rsMyGalleries));
  $rows = mysql_num_rows($rsMyGalleries);
  if($rows > 0) {
      mysql_data_seek($rsMyGalleries, 0);
	  $row_rsMyGalleries = mysql_fetch_assoc($rsMyGalleries);
  }
?>
            </select>
            <input name="formaction" type="hidden" id="formaction" value="add" />
        </label></td>
      </tr>
      <tr>
      <td>
              <input name="selectall" type="checkbox" id="selectall" onclick="checkUncheckAll(this)" value="1" checked="checked" />   </td>
      <td colspan="3"><label for="selectall">Select all files</label></td>
      </tr>
    
    <?php 
	foreach($files as $key => $value) { 
	if($value !=".") {
	$file = $directory.$value; 
	$link = is_file($file) ?  str_replace(SITE_ROOT,"/",$file) : "index.php?directory=".$file.DIRECTORY_SEPARATOR;
if(is_file($file) && in_array(mime_content_type($file),$allowed_images)) {	
	 
?><tr>
   
      <td><input name="item[<?php echo $key; ?>]" type="checkbox" id="item[<?php echo $key; ?>]" value="1" checked="checked" /><input name="filename[<?php echo $key; ?>]" type="hidden" value="<?php echo $file; ?>" />   </td>   
      <td><?php 
		  if(!is_file($thumbs_directory.$value)) {
		CreateImageSize($file,$thumbs_directory.$value,50,$quality=90,mime_content_type($file),$resizetype="maxwidth"); } ?>
      <img src="/Uploads/temp/<?php echo $_SESSION['MM_Username']."/thumbs/".$value; ?>"  />
      </td>
      <td><a href="<?php echo $link; ?>"><?php echo $value;  ?></a></td>
      <td><label>
        <input name="description[<?php echo $key; ?>]" type="text" id="description[<?php echo $key; ?>]" size="20" maxlength="100" value="<?php echo isset($_REQUEST['description[$key]']) ? $_REQUEST['description[$key]'] : "Untitled"; ?>" />
      </label></td>
    </tr> <?php 
	}} }// end for ?></table>
    </form>
  <?php } // end is files 
  else { ?>
  <p>
   
    There are no files in uploaded.</p>
  <?php } ?>
  <?php } // end is directory
  else { ?>
  <p>Cannot find upload directory.</p>
  <?php } ?></div><!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsMyGalleries);
?>
