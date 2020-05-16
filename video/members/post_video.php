<?php require_once('../../Connections/aquiescedb.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "";
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

require_once('../includes/video_upload.php'); 


if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
  $insertSQL = sprintf("INSERT INTO video (videotitle, videodescription, imageURL, method, videoURL, mimetype, createdbyID, createddatetime, statusID, categoryID) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['videotitle'], "text"),
                       GetSQLValueString($_POST['videodescription'], "text"),
                       GetSQLValueString($_POST['imageURL'], "text"),
                       GetSQLValueString($_POST['method'], "int"),
                       GetSQLValueString($_POST['videoURL'], "text"),
                       GetSQLValueString($_POST['mimetype'], "text"),
                       GetSQLValueString($_POST['createdbyID'], "int"),
                       GetSQLValueString($_POST['createddatetime'], "date"),
                       GetSQLValueString($_POST['statusID'], "int"),
                       GetSQLValueString($_POST['categoryID'], "int"));

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
$query_rsLoggedIn = sprintf("SELECT ID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

$varUserGroup_rsVideoCategories = "-99";
if (isset($_SESSION['MM_UserGroup'])) {
  $varUserGroup_rsVideoCategories = $_SESSION['MM_UserGroup'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsVideoCategories = sprintf("SELECT ID, categoryname FROM videocategory WHERE statusID = 1 AND accesslevel <= %s ORDER BY categoryname ASC", GetSQLValueString($varUserGroup_rsVideoCategories, "int"));
$rsVideoCategories = mysql_query($query_rsVideoCategories, $aquiescedb) or die(mysql_error());
$row_rsVideoCategories = mysql_fetch_assoc($rsVideoCategories);
$totalRows_rsVideoCategories = mysql_num_rows($rsVideoCategories);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = "SELECT * FROM preferences";
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);
?><?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" --><title><?php $pageTitle = "Post Video"; echo $pageTitle." | ".$site_name; ?></title>
<link href="../../SpryAssets/SpryValidationTextField.css" rel="stylesheet" >
<!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script src="../../SpryAssets/SpryValidationTextField.js"></script><script src="../../core/scripts/formUpload.js"></script>
<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" -->
  <div class="page video"><?php if ($row_rsPreferences['videoupload']==1) { ?>
   <h1>Add Your Video</h1><?php require_once('../../core/includes/alert.inc.php'); ?>   
   <form action="<?php echo $editFormAction; ?>" method="post" enctype="multipart/form-data" name="form1" id="form1">
     <table class="form-table"> <tr>
         <td class="text-nowrap text-right">Category:</td>
         <td><label>
           <select name="categoryID"  id="categoryID">
             <option value="1"><?php echo isset($region['text_choose']) ? htmlentities($region['text_choose'], ENT_COMPAT, "UTF-8") : "Choose..." ?></option>
             <?php
do {  
?>
             <option value="<?php echo $row_rsVideoCategories['ID']?>"><?php echo $row_rsVideoCategories['categoryname']?></option>
             <?php
} while ($row_rsVideoCategories = mysql_fetch_assoc($rsVideoCategories));
  $rows = mysql_num_rows($rsVideoCategories);
  if($rows > 0) {
      mysql_data_seek($rsVideoCategories, 0);
	  $row_rsVideoCategories = mysql_fetch_assoc($rsVideoCategories);
  }
?>
           </select>
         </label></td>
       </tr> <tr>
         <td class="text-nowrap text-right">Video title:</td>
         <td><span id="sprytextfield1">
           <input name="videotitle" type="text"  value="<?php echo isset($_REQUEST['videotitle']) ? htmlentities($_REQUEST['videotitle']) : ""; ?>" size="50" maxlength="50" />
         <span class="textfieldRequiredMsg">A value is required.</span></span></td>
       </tr> <tr>
         <td class="text-nowrap text-right top">Description:</td>
         <td><textarea name="videodescription" cols="50" rows="5"><?php echo isset($_REQUEST['videodescription']) ? htmlentities($_REQUEST['videodescription']) : ""; ?></textarea>         </td>
       </tr> <tr>
         <td class="text-nowrap text-right">Video file:</td>
         <td><label>
           <input type="file" class="fileinput" name="videofile" id="videofile" />
           <input type="hidden" name="imageURL" id="imageURL" />
           <input name="videoURL" type="hidden" value="" size="50" maxlength="255" />
           <input name="method" type="hidden" id="method" value="2" />
         </label></td>
       </tr> <tr>
         <td class="text-nowrap text-right">&nbsp;</td>
         <td><input type="submit" class="button" value="Upload Video" /></td>
       </tr>
     </table>
     <input name="createdbyID" type="hidden" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
     <input type="hidden" name="createddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
     <input type="hidden" name="statusID" value="1" />
     <input type="hidden" name="MM_insert" value="form1" />
     <input name="autothumbnail" type="hidden" id="autothumbnail" value="1" />
     <input type="hidden" name="mimetype" id="mimetype" />
   </form>
  <?php } else { ?><p>Video uploading is unavailble at present</p><?php } ?></div>
   <script>
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1");
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

mysql_free_result($rsVideoCategories);

mysql_free_result($rsPreferences);
?>


