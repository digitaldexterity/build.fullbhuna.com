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

$MM_restrictGoTo = "file:///Macintosh HD/Users/pegan/Sites/login/index.php?notloggedin=true";
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
  $insertSQL = sprintf("INSERT INTO article (ordernum, title, body, statusID, sectionID, regionID, createdbyID, createddatetime) VALUES (%s, %s, %s, %s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['ordernum'], "int"),
                       GetSQLValueString($_POST['title'], "text"),
                       GetSQLValueString($_POST['body'], "text"),
                       GetSQLValueString($_POST['status'], "int"),
                       GetSQLValueString($_POST['sectionID'], "int"),
                       GetSQLValueString($_POST['regionID'], "int"),
                       GetSQLValueString($_POST['createdbyID'], "int"),
                       GetSQLValueString($_POST['createddatetime'], "date"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($insertSQL, $aquiescedb) or die(mysql_error());
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
  $insertGoTo = "suggest_article_conf.php?articleID=".mysql_insert_id();
  if (isset($_SERVER['QUERY_STRING'])) {
    $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
    $insertGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $insertGoTo));
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
$query_rsStatus = "SELECT * FROM status";
$rsStatus = mysql_query($query_rsStatus, $aquiescedb) or die(mysql_error());
$row_rsStatus = mysql_fetch_assoc($rsStatus);
$totalRows_rsStatus = mysql_num_rows($rsStatus);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = "SELECT preferences.useregions, preferences.usesections FROM preferences";
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);

$varUserGroup_rsSections = "0";
if (isset($_SESSION['MM_UserGroup'])) {
  $varUserGroup_rsSections = $_SESSION['MM_UserGroup'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsSections = sprintf("SELECT ID, `description` FROM articlesection WHERE  articlesection.accesslevel <= %s", GetSQLValueString($varUserGroup_rsSections, "int"));
$rsSections = mysql_query($query_rsSections, $aquiescedb) or die(mysql_error());
$row_rsSections = mysql_fetch_assoc($rsSections);
$totalRows_rsSections = mysql_num_rows($rsSections);

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

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsArticleNextID = "SELECT MAX(ID)+1 AS maxID FROM article";
$rsArticleNextID = mysql_query($query_rsArticleNextID, $aquiescedb) or die(mysql_error());
$row_rsArticleNextID = mysql_fetch_assoc($rsArticleNextID);
$totalRows_rsArticleNextID = mysql_num_rows($rsArticleNextID);
?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Suggest an article"; echo $pageTitle." | ".$site_name; ?>
</title>
<link href="../../SpryAssets/SpryValidationTextField.css" rel="stylesheet" >
<!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<?php $WYSIWYGstyle = "compact";
require_once('../../core/tinymce/tinymce.inc.php'); ?>
<script src="../../SpryAssets/SpryValidationTextField.js"></script>
<script>
var fb_keepAlive = true;
window.setInterval(function() {
				 $.get('/login/ajax/keep_session_alive.ajax.php');
			 }, 60000);
</script>
<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" -->
    <?php if (strcmp($row_rsPreferences['usesections'],1)) { ?>
    <style>
   .section {display:none; } 
     </style>
    <?php } 
   if (strcmp($row_rsPreferences['useregions'],1)) { ?>
    <style>
   .region {display:none; } 
     </style>
    <?php }?>
    <div class="crumbs"><div><span class="you_are_in">You are in: </span><a href="../../index.php">Home</a><span class="separator">&nbsp;&rsaquo;&nbsp;</span><a href="/members/" >Members</a><span class="separator">&nbsp;&rsaquo;&nbsp;</span>Suggest an article</div></div>
    <h1>Suggest an Article</h1>
   
    <?php if(isset($submit_error)) { ?>
    <p class="alert alert-danger" role="alert"><?php echo $submit_error; ?></p>
    <?php } ?>
    <form action="<?php echo $editFormAction; ?>" method="post" enctype="multipart/form-data" name="form1" id="form1">
      <table class="form-table">
        <tr class="section">
          <td class="text-nowrap text-right">Section:</td>
          <td class="form-inline"><?php if ($totalRows_rsSections > 0) { // Show if recordset not empty ?>
              <select name="sectionID" id="sectionID" class="form-control">
                <option value="1" <?php if (!(strcmp(1, @$_GET['sectionID']))) {echo "selected=\"selected\"";} ?>><?php echo isset($region['text_choose']) ? htmlentities($region['text_choose'], ENT_COMPAT, "UTF-8") : "Choose..." ?></option>
                <?php
do {  
?>
                <option value="<?php echo $row_rsSections['ID']?>"<?php if (!(strcmp($row_rsSections['ID'], @$_GET['sectionID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsSections['description']?></option>
                <?php
} while ($row_rsSections = mysql_fetch_assoc($rsSections));
  $rows = mysql_num_rows($rsSections);
  if($rows > 0) {
      mysql_data_seek($rsSections, 0);
	  $row_rsSections = mysql_fetch_assoc($rsSections);
  }
?>
              </select>
              <?php } // Show if recordset not empty ?>
            <?php if ($totalRows_rsSections == 0) { // Show if recordset empty ?>
              <input name="sectionID" type="hidden" id="sectionID" value="1" />
              No sections created
              <?php } // Show if recordset empty ?></td>
        </tr> <tr>
          <td class="text-nowrap text-right">Title:</td>
          <td><span id="sprytextfield1">
            <input name="title" type="text" value="" size="50" maxlength="50" class="form-control" />
          <span class="textfieldRequiredMsg">A value is required.</span></span></td>
        </tr>
        <tr class="region">
          <td class="text-nowrap text-right">Site:</td>
          <td><select name="regionID" id="regionID"  class="form-control" >
              <option value="1"><?php echo isset($region['text_choose']) ? htmlentities($region['text_choose'], ENT_COMPAT, "UTF-8") : "Choose..." ?></option>
              <?php
do {  
?>
              <option value="<?php echo $row_rsRegions['ID']?>"><?php echo $row_rsRegions['title']?></option>
              <?php
} while ($row_rsRegions = mysql_fetch_assoc($rsRegions));
  $rows = mysql_num_rows($rsRegions);
  if($rows > 0) {
      mysql_data_seek($rsRegions, 0);
	  $row_rsRegions = mysql_fetch_assoc($rsRegions);
  }
?>
            </select>
            <input name="ordernum" type="hidden" id="ordernum" value="<?php echo $row_rsArticleNextID['maxID']; ?>" /></td>
        </tr> <tr>
          <td colspan="2" ><textarea  class="tinymce form-control"  name="body" id="body" cols="60" rows="20"></textarea></td>
        </tr> <tr>
          <td colspan="2" class="text-nowrap"><button type="submit" class="btn btn-primary" >Submit article</button></td>
        </tr>
      </table>
      <input name="createdbyID" type="hidden" id="createdbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
      <input type="hidden" name="MM_insert" value="form1" />
      <input name="createddatetime" type="hidden" id="createddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
      <input name="status" type="hidden" value="0" />
    </form>
   
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

mysql_free_result($rsStatus);

mysql_free_result($rsPreferences);

mysql_free_result($rsSections);

mysql_free_result($rsRegions);

mysql_free_result($rsPhotoGalleries);

mysql_free_result($rsArticleNextID);
?>
