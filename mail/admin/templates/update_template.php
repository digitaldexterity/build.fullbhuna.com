<?php require_once('../../../Connections/aquiescedb.php'); ?>
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

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
// fix empty <p> tags (tinymce set not to check)
	$_POST['templateHTML'] = str_replace("<p></p>","<p>&nbsp;</p>", $_POST['templateHTML']);
}


if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE groupemailtemplate SET templatename=%s, templatesubject=%s, templatehead=%s, templatebodytag=%s, templateHTML=%s, smsmessage=%s, viewonline=%s, createdbyID=%s, createddatetime=%s, modifiedbyID=%s, modifieddatetime=%s, statusID=%s, templatedefaultfirstname=%s WHERE ID=%s",
                       GetSQLValueString($_POST['templatename'], "text"),
                       GetSQLValueString($_POST['templatesubject'], "text"),
                       GetSQLValueString($_POST['templatehead'], "text"),
                       GetSQLValueString($_POST['templatebodytag'], "text"),
                       GetSQLValueString($_POST['templateHTML'], "text"),
                       GetSQLValueString($_POST['smsmessage'], "text"),
                       GetSQLValueString(isset($_POST['viewonline']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['createdbyID'], "int"),
                       GetSQLValueString($_POST['createddatetime'], "date"),
                       GetSQLValueString($_POST['modifiedbyID'], "int"),
                       GetSQLValueString($_POST['modifieddatetime'], "date"),
                       GetSQLValueString(isset($_POST['statusID']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['templatedefaultfirstname'], "text"),
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

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

$colname_rsTemplate = "-1";
if (isset($_GET['templateID'])) {
  $colname_rsTemplate = $_GET['templateID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsTemplate = sprintf("SELECT * FROM groupemailtemplate WHERE ID = %s", GetSQLValueString($colname_rsTemplate, "int"));
$rsTemplate = mysql_query($query_rsTemplate, $aquiescedb) or die(mysql_error());
$row_rsTemplate = mysql_fetch_assoc($rsTemplate);
$totalRows_rsTemplate = mysql_num_rows($rsTemplate);
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Update Mail template"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script src="../../../SpryAssets/SpryValidationTextField.js"></script>
<script src="../../../SpryAssets/SpryTabbedPanels.js"></script>
<link href="../../../SpryAssets/SpryValidationTextField.css" rel="stylesheet" type="text/css" />
<?php $remove_script_host = "false"; // default for tinymce is true
$convert_urls = "false"; // default for tinymce is true
if(!defined("TINYMCE_CONTENT_CSS")) define("TINYMCE_CONTENT_CSS", "/core/css/global.css");
define("TINY_MCE_PLUGINS", "fullpage");
require_once('../../../core/tinymce/tinymce.inc.php'); ?>
<link href="../../../SpryAssets/SpryTabbedPanels.css" rel="stylesheet" type="text/css" />
<script>


$(window).load(function() {
	$(tinyMCE.activeEditor.getDoc()).children().find('head').append("<!--editorHead-->"+$("#templatehead").val()+"<!--editorHead-->");
	$("#head").blur(function() {
		$(tinyMCE.activeEditor.getDoc()).children().find('head').replace(/<!--editorHead-->.+?<!--editorHead-->/gi, "<!--editorHead-->"+$("#templatehead").val()+"<!--editorHead-->"); 
	});
});

</script>
<link href="../../css/mailDefault.css" rel="stylesheet" type="text/css" />
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
        <div class="page mail">
  <h1><i class="glyphicon glyphicon-envelope"></i> Update Mail template</h1>
 <form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1"> <p><label>Name:<span id="sprytextfield1">
          <input name="templatename" type="text"  value="<?php echo htmlspecialchars($row_rsTemplate['templatename'], ENT_COMPAT, 'UTF-8'); ?>" size="50" maxlength="50" class="form-control" />
          <span class="textfieldRequiredMsg">A value is required.</span></span></label></p>
  
  <div id="TabbedPanels1" class="TabbedPanels">
    <ul class="TabbedPanelsTabGroup">
      <li class="TabbedPanelsTab" tabindex="0">Email Content</li>
      <li class="TabbedPanelsTab" tabindex="0">SMS Content</li>
      <li class="TabbedPanelsTab" tabindex="0">Tips</li>
</ul>
    <div class="TabbedPanelsContentGroup">
      <div class="TabbedPanelsContent"> <table class="form-table"> <tr>
        <td class="text-nowrap"><label for="templatesubject" class="form-inline">Default Subject:</label></td><td><input name="templatesubject" id="templatesubject" type="text" value="<?php echo $row_rsTemplate['templatesubject']; ?>" size="50" maxlength="50" class="form-control" /></td>
        </tr> 
        
        <tr>
        <td class="text-nowrap"><label for= "templatedefaultfirstname" class="form-inline">Default To Name:</label></td><td nowrap><input name="templatedefaultfirstname" id="templatedefaultfirstname" type="text" value="<?php echo $row_rsTemplate['templatedefaultfirstname']; ?>" size="50" maxlength="50" class="form-control" /> (if merge {firstname} unavailable)</td>
        </tr> 
        
        <tr>
        <td colspan="2" class="text-nowrap  top">
        <?php // upgrade to new full HTML if required
	  if(strpos($html,"<head")===false) {
		  $html = (isset($row_rsTemplate['templatebodytag'])  && strpos($row_rsTemplate['templatebodytag'],"<body") !==false) ? $row_rsGroupEmail['bodytag'].$html : "<body>".$html;
		  $html = "<html><head>".$row_rsTemplate['templatehead']."</head>".$html."</body></html>";
	  }
	  ?>
          <textarea name="templateHTML" cols="80" rows="10" class="tinymce"><?php echo htmlentities($row_rsTemplate['templateHTML'], ENT_COMPAT, 'UTF-8'); ?></textarea></td>
        </tr> <tr>
        <td class="text-nowrap"  colspan="2">Active:
          <input type="checkbox" name="statusID" value=""  <?php if (!(strcmp(htmlentities($row_rsTemplate['statusID'], ENT_COMPAT, 'UTF-8'),1))) {echo "checked=\"checked\"";} ?> /></td>
        </tr>
      
    </table></div>
      <div class="TabbedPanelsContent">
      <label for="">SMS Message:</label><br>
        <textarea name="smsmessage" id="smsmessage" class="form-control"><?php echo $row_rsTemplate['smsmessage']; ?></textarea>
      </div>
      <div class="TabbedPanelsContent">
        <h2>HTML Email tips:</h2>
        <p>HTML emails are viewed across many different devices and programs with varying capabilities, so to ensure emails look consistent follow these rules:</p>
        <ol>
          <li>Use tables to layout - make top level table 600-650pixels with and centre.</li>
          <li>Background images are not supported</li>
          <li>Don't use Margin or floats - Outlook doesn't suppot them</li>
          <li>All styles must be inline - Gmail deos not support HEAD styles</li>
          <li>Colours must be full hex values (i.e. not #fff or rgb(255,255,255)</li>
          <li>Use web safe colours if overlaying images</li>
        </ol>
        <p>&nbsp;</p>
        <!-- LEGACY FIELDS -->
        <input name="templatehead" id="templatehead" type="hidden" value="<?php echo htmlentities($row_rsTemplate['templatehead'], ENT_COMPAT, "UTF-8"); ?>">
        <input name="templatebodytag" id="templatebodytag" type="hidden" value="<?php echo (isset($row_rsTemplate['templatebodytag'])  && strpos($row_rsTemplate['templatebodytag'],"<body") !==false) ? $row_rsTemplate['templatebodytag'] : htmlentities("<body leftmargin=\"0\" topmargin=\"0\" marginwidth=\"0\" marginheight=\"0\" yahoo=\"fix\" style=\"width: 100%; background-color: #ffffff; margin:0; padding:0; -webkit-font-smoothing: antialiased;font-family: Arial, sans-serif;\">", ENT_COMPAT, "UTF-8"); ?>">
        <p>
          <label>
            <input <?php if (!(strcmp($row_rsTemplate['viewonline'],1))) {echo "checked=\"checked\"";} ?> name="viewonline" type="checkbox" value="1" >
            Show view online link</label>
        </p>
      </div>
</div>
  </div>
 <button type="submit" class="btn btn-primary" >Save changes</button>
  
  
   
    <input type="hidden" name="ID" value="<?php echo $row_rsTemplate['ID']; ?>" />
    <input type="hidden" name="createdbyID" value="<?php echo htmlentities($row_rsTemplate['createdbyID'], ENT_COMPAT, 'UTF-8'); ?>" />
    <input type="hidden" name="createddatetime" value="<?php echo htmlentities($row_rsTemplate['createddatetime'], ENT_COMPAT, 'UTF-8'); ?>" />
    <input type="hidden" name="modifiedbyID" value="<?php echo htmlentities($row_rsLoggedIn['ID']); ?>" />
    <input type="hidden" name="modifieddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
    <input type="hidden" name="MM_update" value="form1" />
    <input type="hidden" name="ID" value="<?php echo $row_rsTemplate['ID']; ?>" />
   <?php require_once('../includes/merge_help.inc.php'); ?>
    <script>
var TabbedPanels1 = new Spry.Widget.TabbedPanels("TabbedPanels1", {defaultTab:2});
    </script>
  </form>
  <script>
<!--
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1");
var TabbedPanels1 = new Spry.Widget.TabbedPanels("TabbedPanels1", {defaultTab:0});
//-->
  </script></div>
  <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsTemplate);
?>
