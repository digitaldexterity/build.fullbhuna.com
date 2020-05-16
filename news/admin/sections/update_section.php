<?php require_once('../../../Connections/aquiescedb.php'); ?><?php require_once('../../../core/includes/adminAccess.inc.php'); ?>
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

$MM_restrictGoTo = "../../../login/index.php?notloggedin=true";
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
$_POST['longID'] = preg_replace("/[^a-zA-Z0-9_\-]/", "", $_POST['longID']); // clean
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE newssection SET longID=%s, redirectURL=%s, metadescription=%s, metakeywords=%s, sectioname=%s, sectiontitle=%s, `description`=%s, accesslevel=%s, editaccess=%s, groupreadID=%s, groupwriteID=%s, requiresapproval=%s, allowcomments=%s, reportabuse=%s, allowphoto=%s, allowattachment=%s, showeventdatetime=%s, classes=%s, statusID=%s, articleID=%s, sectionID=%s, regionID=%s, showbody=%s, orderby=%s, wysiwyg=%s, indexstyle=%s, parentsectionID=%s, emailtemplateID=%s, modifiedbyID=%s, modifieddatetime=%s, customNewsURL=%s, defaultsummary=%s, defaultbody=%s, noindex=%s, showpostedby=%s, emailsenddefault=%s, rsssenddefault=%s, wysiwygsummary=%s WHERE ID=%s",
                       GetSQLValueString($_POST['longID'], "text"),
                       GetSQLValueString($_POST['redirectURL'], "text"),
                       GetSQLValueString($_POST['metadescription'], "text"),
                       GetSQLValueString($_POST['metakeywords'], "text"),
                       GetSQLValueString($_POST['sectioname'], "text"),
                       GetSQLValueString($_POST['sectiontitle'], "text"),
                       GetSQLValueString($_POST['description'], "text"),
                       GetSQLValueString($_POST['accesslevel'], "int"),
                       GetSQLValueString($_POST['editaccess'], "int"),
                       GetSQLValueString($_POST['groupreadID'], "int"),
                       GetSQLValueString($_POST['groupwriteID'], "int"),
                       GetSQLValueString(isset($_POST['requiresapproval']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['allowcomments'], "int"),
                       GetSQLValueString(isset($_POST['reportabuse']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['allowphoto']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['allowattachment']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['showeventdatetime']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['classes'], "text"),
                       GetSQLValueString(isset($_POST['statusID']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['articleID'], "int"),
                       GetSQLValueString($_POST['sectionID'], "int"),
                       GetSQLValueString($_POST['regionID'], "int"),
                       GetSQLValueString(isset($_POST['showbody']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['orderby'], "int"),
                       GetSQLValueString(isset($_POST['wysiwyg']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['indexstyle'], "int"),
                       GetSQLValueString($_POST['parentsectionID'], "int"),
                       GetSQLValueString($_POST['emailtemplateID'], "int"),
                       GetSQLValueString($_POST['modifiedbyID'], "int"),
                       GetSQLValueString($_POST['modifieddatetime'], "date"),
                       GetSQLValueString($_POST['customNewsURL'], "text"),
                       GetSQLValueString($_POST['defaultsummary'], "text"),
                       GetSQLValueString($_POST['defaultbody'], "text"),
                       GetSQLValueString(isset($_POST['noindex']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['showpostedby']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['emailsenddefault']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['rsssenddefault']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['wysiwygsummary']) ? "true" : "", "defined","1","0"),
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
  header(sprintf("Location: %s", $updateGoTo)); exit;
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
$query_rsAccessLevels = "SELECT ID, CONCAT(name,'s') AS accesslevel FROM usertype WHERE ID > 0 ORDER BY ID ASC";
$rsAccessLevels = mysql_query($query_rsAccessLevels, $aquiescedb) or die(mysql_error());
$row_rsAccessLevels = mysql_fetch_assoc($rsAccessLevels);
$totalRows_rsAccessLevels = mysql_num_rows($rsAccessLevels);

$colname_rsSection = "-1";
if (isset($_GET['sectionID'])) {
  $colname_rsSection = $_GET['sectionID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsSection = sprintf("SELECT * FROM newssection WHERE ID = %s", GetSQLValueString($colname_rsSection, "int"));
$rsSection = mysql_query($query_rsSection, $aquiescedb) or die(mysql_error());
$row_rsSection = mysql_fetch_assoc($rsSection);
$totalRows_rsSection = mysql_num_rows($rsSection);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsGroups = "SELECT ID, groupname FROM usergroup ORDER BY groupname ASC";
$rsGroups = mysql_query($query_rsGroups, $aquiescedb) or die(mysql_error());
$row_rsGroups = mysql_fetch_assoc($rsGroups);
$totalRows_rsGroups = mysql_num_rows($rsGroups);

$varRegionID_rsArticleSections = "1";
if (isset($regionID)) {
  $varRegionID_rsArticleSections = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsArticleSections = sprintf("SELECT articlesection.ID, articlesection.`description` FROM articlesection WHERE regionID = %s ORDER BY articlesection.`description`", GetSQLValueString($varRegionID_rsArticleSections, "int"));
$rsArticleSections = mysql_query($query_rsArticleSections, $aquiescedb) or die(mysql_error());
$row_rsArticleSections = mysql_fetch_assoc($rsArticleSections);
$totalRows_rsArticleSections = mysql_num_rows($rsArticleSections);

$colname_rsArticles = "1";
if (isset($regionID)) {
  $colname_rsArticles = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsArticles = sprintf("SELECT * FROM article WHERE versionofID IS NULL AND regionID = %s ORDER BY title ASC", GetSQLValueString($colname_rsArticles, "int"));
$rsArticles = mysql_query($query_rsArticles, $aquiescedb) or die(mysql_error());
$row_rsArticles = mysql_fetch_assoc($rsArticles);
$totalRows_rsArticles = mysql_num_rows($rsArticles);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsRegions = "SELECT ID, title FROM region";
$rsRegions = mysql_query($query_rsRegions, $aquiescedb) or die(mysql_error());
$row_rsRegions = mysql_fetch_assoc($rsRegions);
$totalRows_rsRegions = mysql_num_rows($rsRegions);

$varRegionID_rsTemplates = "1";
if (isset($regionID)) {
  $varRegionID_rsTemplates = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsTemplates = sprintf("SELECT ID, templatename FROM groupemailtemplate WHERE (regionID = 0 OR regionID = %s) AND statusID = 1 ORDER BY templatename ASC", GetSQLValueString($varRegionID_rsTemplates, "int"));
$rsTemplates = mysql_query($query_rsTemplates, $aquiescedb) or die(mysql_error());
$row_rsTemplates = mysql_fetch_assoc($rsTemplates);
$totalRows_rsTemplates = mysql_num_rows($rsTemplates);
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" --><title><?php $pageTitle = "Update Post Section"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title><!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" --><?php require_once('../../../core/tinymce/tinymce.inc.php'); ?>
<script src="../../../SpryAssets/SpryValidationTextField.js"></script>
<script src="../../../SpryAssets/SpryTabbedPanels.js"></script>
<link href="../../../SpryAssets/SpryValidationTextField.css" rel="stylesheet"  />
<?php if (!(defined("MOD_REWRITE") || isset($_SERVER['HTTP_MOD_REWRITE']))) { // no mod re-write so hide URL option ?>
<style><!--
.longID { display:none; }
<?php if($totalRows_rsGroups==0) { echo ".groups { display: 'none' }"; } ?>
--></style><?php } ?><script language="JavaScript">
function seoPopulate(title,content) {
var longID = title.replace(/[^a-zA-Z 0-9]+/g,'');
longID = longID.replace(/[ ]+/g,'-');
if (document.getElementById('longID').value == "") document.getElementById('longID').value = longID;
if (document.getElementById('metadescription').value == "") document.getElementById('metadescription').value = content;
if (document.getElementById('metakeywords').value == "") document.getElementById('metakeywords').value = title;
} // end function
</script>
<link href="../../../SpryAssets/SpryTabbedPanels.css" rel="stylesheet"  />
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
<div class="page news"> <form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1">  <h1><i class="glyphicon glyphicon-bullhorn"></i> Update Post Section</h1>
   <div id="TabbedPanels1" class="TabbedPanels">
     <ul class="TabbedPanelsTabGroup">
       <li class="TabbedPanelsTab" tabindex="0">Details</li>
       <li class="TabbedPanelsTab" tabindex="0">Header</li>
       <li class="TabbedPanelsTab" tabindex="0">Template</li>
       <li class="TabbedPanelsTab" tabindex="0">SEO &amp; Advanced</li>
     </ul>
     <div class="TabbedPanelsContentGroup">
       <div class="TabbedPanelsContent">
         <table class="form-table">
           <tr>
             <td class="text-nowrap text-right">Name:</td>
             <td><span id="sprytextfield1">
               <input name="sectioname" type="text"  value="<?php echo $row_rsSection['sectioname']; ?>" size="50" maxlength="50" onBlur="seoPopulate(this.value, this.value);"  class="form-control" />
               <span class="textfieldRequiredMsg">A name is required.</span></span></td>
           </tr>
           <tr class="longID">
             <td class="text-nowrap text-right top">URL name:</td>
             <td><input name="longID" type="text"  id="longID" value="<?php echo $row_rsSection['longID']; ?>" size="50" maxlength="100"  class="form-control"/></td>
           </tr>
           <tr>
             <td class="text-nowrap text-right">Can view:</td>
             <td class="form-inline"><select name="accesslevel"  class="form-control">
               <option value="0"  <?php if (!(strcmp(0, $row_rsSection['accesslevel']))) {echo "selected=\"selected\"";} ?>>Everyone</option>
               <?php
do {  
?>
               <option value="<?php echo $row_rsAccessLevels['ID']?>"<?php if (!(strcmp($row_rsAccessLevels['ID'], $row_rsSection['accesslevel']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsAccessLevels['accesslevel']?></option>
               <?php
} while ($row_rsAccessLevels = mysql_fetch_assoc($rsAccessLevels));
  $rows = mysql_num_rows($rsAccessLevels);
  if($rows > 0) {
      mysql_data_seek($rsAccessLevels, 0);
	  $row_rsAccessLevels = mysql_fetch_assoc($rsAccessLevels);
  }
?>
             </select>
               <select name="groupreadID" id="groupreadID"  class="form-control group">
                 <option value="0" <?php if (!(strcmp(0, $row_rsSection['groupreadID']))) {echo "selected=\"selected\"";} ?>>Any group</option>
                 <?php
do {  
?>
                 <option value="<?php echo $row_rsGroups['ID']?>"<?php if (!(strcmp($row_rsGroups['ID'], $row_rsSection['groupreadID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsGroups['groupname']?></option>
                 <?php
} while ($row_rsGroups = mysql_fetch_assoc($rsGroups));
  $rows = mysql_num_rows($rsGroups);
  if($rows > 0) {
      mysql_data_seek($rsGroups, 0);
	  $row_rsGroups = mysql_fetch_assoc($rsGroups);
  }
?>
               </select>
               <label>
                 <input <?php if (!(strcmp($row_rsSection['statusID'],1))) {echo "checked=\"checked\"";} ?> name="statusID" type="checkbox" id="statusID" />
                 Active</label></td>
           </tr>
           <tr>
             <td class="text-nowrap text-right">Can post:</td>
             <td class="form-inline"><select name="editaccess"  id="editaccess"  class="form-control">
               <?php
do {  
?>
               <option value="<?php echo $row_rsAccessLevels['ID']?>"<?php if (!(strcmp($row_rsAccessLevels['ID'], $row_rsSection['editaccess']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsAccessLevels['accesslevel']?></option>
               <?php
} while ($row_rsAccessLevels = mysql_fetch_assoc($rsAccessLevels));
  $rows = mysql_num_rows($rsAccessLevels);
  if($rows > 0) {
      mysql_data_seek($rsAccessLevels, 0);
	  $row_rsAccessLevels = mysql_fetch_assoc($rsAccessLevels);
  }
?>
             </select>
             
                 <select name="groupwriteID" id="groupwriteID"  class="form-control group">
                   <option value="0" <?php if (!(strcmp(0, $row_rsSection['groupwriteID']))) {echo "selected=\"selected\"";} ?>>Any group</option>
                   <?php
do {  
?>
                   <option value="<?php echo $row_rsGroups['ID']?>"<?php if (!(strcmp($row_rsGroups['ID'], $row_rsSection['groupwriteID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsGroups['groupname']?></option>
                   <?php
} while ($row_rsGroups = mysql_fetch_assoc($rsGroups));
  $rows = mysql_num_rows($rsGroups);
  if($rows > 0) {
      mysql_data_seek($rsGroups, 0);
	  $row_rsGroups = mysql_fetch_assoc($rsGroups);
  }
?>
                 </select>
                 <label>
                 <input <?php if (!(strcmp($row_rsSection['requiresapproval'],1))) {echo "checked=\"checked\"";} ?> name="requiresapproval" type="checkbox" id="requiresapproval" value="1" />
                 Requires admin approval</label>
               &nbsp;&nbsp;&nbsp;
               <label>
                 <input <?php if (!(strcmp($row_rsSection['reportabuse'],1))) {echo "checked=\"checked\"";} ?> name="reportabuse" type="checkbox" id="reportabuse" value="1" />
                 Link to report abuse</label></td>
           </tr>
           <tr>
             <td class="text-nowrap text-right">Member post options:</td>
             <td><label>
               <input <?php if (!(strcmp($row_rsSection['allowphoto'],1))) {echo "checked=\"checked\"";} ?> name="allowphoto" type="checkbox" id="allowphoto" />
               Allow photo</label>
               &nbsp;&nbsp;&nbsp;
               <label>
                 <input <?php if (!(strcmp($row_rsSection['allowattachment'],1))) {echo "checked=\"checked\"";} ?> type="checkbox" name="allowattachment" id="allowattachment" />
                 Allow attachment</label></td>
           </tr>
           <tr>
             <td class="text-nowrap text-right"><label for="allowcomments">Comments allowed from:</label></td>
             <td><select name="allowcomments" id="allowcomments"  class="form-control">
               <option value="0" <?php if (!(strcmp(0, $row_rsSection['allowcomments']))) {echo "selected=\"selected\"";} ?>>Nobody</option>
               <?php
do {  
?>
               <option value="<?php echo $row_rsAccessLevels['ID']?>"<?php if (!(strcmp($row_rsAccessLevels['ID'], $row_rsSection['allowcomments']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsAccessLevels['accesslevel']?></option>
               <?php
} while ($row_rsAccessLevels = mysql_fetch_assoc($rsAccessLevels));
  $rows = mysql_num_rows($rsAccessLevels);
  if($rows > 0) {
      mysql_data_seek($rsAccessLevels, 0);
	  $row_rsAccessLevels = mysql_fetch_assoc($rsAccessLevels);
  }
?>
             </select></td>
           </tr>
           <tr>       
           <tr>
             <td class="text-nowrap text-right"><label for="showpostedby">Show posted by:</label></td>
             <td><input <?php if (!(strcmp($row_rsSection['showpostedby'],1))) {echo "checked=\"checked\"";} ?> type="checkbox" name="showpostedby" id="showpostedby" value="1"> (on news pages and email sender)</td>
           </tr>
           
           
           
           <td class="text-nowrap text-right">Order Posts by:</td>
             <td><label>
               <input <?php if (!isset($row_rsSection['orderby']) || !(strcmp($row_rsSection['orderby'],"1"))) {echo "checked=\"checked\"";} ?> type="radio" name="orderby" value="1" id="orderby_0">
               Date posted/event</label>
               (newest first)&nbsp;&nbsp;&nbsp;
               <label>
                 <input <?php if (!(strcmp($row_rsSection['orderby'],"2"))) {echo "checked=\"checked\"";} ?> type="radio" name="orderby" value="2" id="orderby_1">
                 Date posted/event (oldest first)</label>
               &nbsp;&nbsp;&nbsp;
               <label>
                 <input <?php if (!(strcmp($row_rsSection['orderby'],"3"))) {echo "checked=\"checked\"";} ?> type="radio" name="orderby" value="3" id="orderby_2">
                 Order in editor (drag and drop)</label>
               &nbsp;&nbsp;&nbsp;
               <label>
                 <input <?php if (!(strcmp($row_rsSection['orderby'],"4"))) {echo "checked=\"checked\"";} ?> type="radio" name="orderby" value="4" >
                 Date from today (if event)</label></td>
           </tr>
           
           <tr class="region">
             <td class="text-nowrap text-right"><label for="regionID">Site:</label></td>
             <td><select name="regionID" id="regionID"  class="form-control">
               <option value="0" <?php if (!(strcmp(0, $row_rsSection['regionID']))) {echo "selected=\"selected\"";} ?>>All sites</option>
               <?php
do {  
?>
               <option value="<?php echo $row_rsRegions['ID']?>"<?php if (!(strcmp($row_rsRegions['ID'], $row_rsSection['regionID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsRegions['title']?></option>
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
         </table>
       </div>
       <div class="TabbedPanelsContent"> <table  class="form-table">
       
       <tr>
             <td colspan="2" class="text-nowrap  ">Header text
               (optional):</td>
           </tr>
           <tr>
             <td colspan="2" class="text-nowrap  "><textarea name="description" id="description" cols="45" rows="5" class="tinymce"><?php echo $row_rsSection['description']; ?></textarea></td>
           </tr>
       
       
       
       
       </table></div>
       <div class="TabbedPanelsContent">
        <table  class="form-table">
       <tr>
             <td>Summary Template:</td>
             </tr><tr>
             <td>
               <textarea name="defaultsummary" cols="50" rows="5" id="defaultsummary" class="<?php if($row_rsSection['wysiwygsummary']==1) { 
echo "tinymce";} ?> form-control"> <?php echo $row_rsSection['defaultsummary']; ?></textarea>
             </td>
             </tr>
             <tr>
             <td>Body Template:</td>
              </tr><tr>
             <td>
               <textarea name="defaultbody" cols="50" rows="5" id="defaultbody" class="<?php if($row_rsSection['wysiwyg']==1) { 
echo "tinymce";} ?> form-control"> <?php echo $row_rsSection['defaultbody']; ?></textarea>
             </td>
             </tr>
      </table>
       </div>
       <div class="TabbedPanelsContent">
         
         <table  class="form-table">
           <tr>
             <td scope="row" class="text-right">Page Title:</td>
             <td><input name="sectiontitle" type="text" value="<?php echo $row_rsSection['sectiontitle']; ?>" size="50" maxlength="255" class="seo-length form-control" /></td>
           </tr>
           <tr>
             <td scope="row" class="text-right">Meta description:</td>
             <td><label>
               <textarea name="metadescription" cols="50" rows="5" id="metadescription" class="seo-length form-control"> <?php echo $row_rsSection['metadescription']; ?></textarea>
             </label></td>
           </tr>
           <tr>
             <td scope="row" class="text-right">Custom Index URL:</td>
             <td><input name="redirectURL" type="text" value="<?php echo $row_rsSection['redirectURL']; ?>" size="50" maxlength="255" class="form-control"/></td>
           </tr>
           <tr>
             <td scope="row" class="text-right">Custom Post URL:</td>
             <td><input name="customNewsURL" type="text" value="<?php echo $row_rsSection['customNewsURL']; ?>" size="50" maxlength="255" class="form-control"/></td>
           </tr>
           <tr>
             <td scope="row" class="text-right">CSS classes:</td>
             <td><input name="classes" type="text" value="<?php echo $row_rsSection['classes']; ?>" size="50" maxlength="50" class="form-control" /></td>
           </tr>
           <tr>
             <td scope="row" class="text-right"><label for="parentsectionID">Section:</label></td>
             <td>
               <select name="parentsectionID" id="parentsectionID" class="form-control">
                 <option value="0" <?php if (!(strcmp(0, $row_rsSection['parentsectionID']))) {echo "selected=\"selected\"";} ?>>Home</option>
                 <?php
do {  
?>
  <option value="<?php echo $row_rsArticleSections['ID']?>"<?php if (!(strcmp($row_rsArticleSections['ID'], $row_rsSection['parentsectionID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsArticleSections['description']?></option>
                 <?php
} while ($row_rsArticleSections = mysql_fetch_assoc($rsArticleSections));
  $rows = mysql_num_rows($rsArticleSections);
  if($rows > 0) {
      mysql_data_seek($rsArticleSections, 0);
	  $row_rsArticleSections = mysql_fetch_assoc($rsArticleSections);
  }
?>
                 </select></td>
           </tr>
           <tr>
             <td scope="row" class="text-right"><label for="sectionID">Sub-section:</label></td>
             <td><select name="sectionID" id="sectionID" class="form-control">
               <option value="" <?php if (!(strcmp("", $row_rsSection['sectionID']))) {echo "selected=\"selected\"";} ?>>None</option>
               <?php
do {  
?>
               <option value="<?php echo $row_rsArticleSections['ID']?>"<?php if (!(strcmp($row_rsArticleSections['ID'], $row_rsSection['sectionID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsArticleSections['description']?></option>
               <?php
} while ($row_rsArticleSections = mysql_fetch_assoc($rsArticleSections));
  $rows = mysql_num_rows($rsArticleSections);
  if($rows > 0) {
      mysql_data_seek($rsArticleSections, 0);
	  $row_rsArticleSections = mysql_fetch_assoc($rsArticleSections);
  }
?>
             </select></td>
           </tr>
           <tr>
             <td scope="row" class="text-right"><label for="articleID">Page:</label></td>
             <td>
               <select name="articleID" id="articleID" class="form-control">
                 <option value="" <?php if (!(strcmp("", $row_rsSection['articleID']))) {echo "selected=\"selected\"";} ?>>None</option>
                 <?php
do {  
?>
                 <option value="<?php echo $row_rsArticles['ID']?>"<?php if (!(strcmp($row_rsArticles['ID'], $row_rsSection['articleID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsArticles['title']?></option>
                 <?php
} while ($row_rsArticles = mysql_fetch_assoc($rsArticles));
  $rows = mysql_num_rows($rsArticles);
  if($rows > 0) {
      mysql_data_seek($rsArticles, 0);
	  $row_rsArticles = mysql_fetch_assoc($rsArticles);
  }
?>
               </select></td>
           </tr>
           <tr>
             <td scope="row" class="text-right rank10"> <label for="showbody">Show body:</label></td>
             <td><input <?php if (!(strcmp($row_rsSection['showbody'],1))) {echo "checked=\"checked\"";} ?> name="showbody" type="checkbox" id="showbody" value="1"></td>
              </td>
           </tr>
           <tr>
             <td scope="row" class="text-right rank10"><label for="wysiwyg">Use WYSIWYG editor:</label></td>
             <td><input <?php if (!(strcmp($row_rsSection['wysiwyg'],1))) {echo "checked=\"checked\"";} ?> name="wysiwyg" type="checkbox" id="wysiwyg" value="1"> &nbsp;&nbsp;&nbsp; <label>include summary: 
                 <input <?php if (!(strcmp($row_rsSection['wysiwygsummary'],1))) {echo "checked=\"checked\"";} ?> name="wysiwygsummary" type="checkbox" id="wysiwygsummary" value="1"></label></td>
           </tr>
           <tr>
             <td scope="row" class="text-right"><label for="noindex">Non-searchable:</label></td>
             <td><input <?php if (!(strcmp($row_rsSection['noindex'],1))) {echo "checked=\"checked\"";} ?> name="noindex" type="checkbox" id="noindex" value="1"> (ask search engines not to list posts in this section)</td>
           </tr>
           
           <tr>
             <td scope="row" class="text-right rank10">Index style:</td>
             <td>
               <label>
                 <input <?php if (!(strcmp($row_rsSection['indexstyle'],"0"))) {echo "checked=\"checked\"";} ?> type="radio" name="indexstyle" value="0" id="indexstyle_0">
                 Classic List</label>
               &nbsp;&nbsp;&nbsp;
               <label>
                 <input <?php if (!(strcmp($row_rsSection['indexstyle'],"1"))) {echo "checked=\"checked\"";} ?> type="radio" name="indexstyle" value="1" id="indexstyle_1">
                 Masonry</label>
               </td>
           </tr>
           
           <tr>
             <td class="text-nowrap text-right"><label for="showeventdatetime">Show event date:</label></td>
             <td><input <?php if (!(strcmp($row_rsSection['showeventdatetime'],1))) {echo "checked=\"checked\"";} ?> type="checkbox" name="showeventdatetime" id="showeventdatetime" value="1"></td>
           </tr>
           
            <tr>
             <td class="text-nowrap text-right"><label for="rsssenddefault">RSS:</label></td>
             <td><input <?php if (!(strcmp($row_rsSection['rsssenddefault'],1))) {echo "checked=\"checked\"";} ?> type="checkbox" name="rsssenddefault" id="rsssenddefault" value="1"> add by default</td>
           </tr>
           
           
           <tr>
             <td scope="row" class="text-right">Email template:</td>
             <td class="form-inline"><select name="emailtemplateID" id="emailtemplateID" class="form-control">
           <option value="" <?php if (!(strcmp("", $row_rsSection['emailtemplateID']))) {echo "selected=\"selected\"";} ?>>Choose...</option>
           <?php $rows = mysql_num_rows($rsTemplates);
  if($rows > 0) {
do {  
?>
           <option value="<?php echo $row_rsTemplates['ID']?>"<?php if (!(strcmp($row_rsTemplates['ID'], $row_rsSection['emailtemplateID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsTemplates['templatename']?></option>
           <?php
} while ($row_rsTemplates = mysql_fetch_assoc($rsTemplates));
  
      mysql_data_seek($rsTemplates, 0);
	  $row_rsTemplates = mysql_fetch_assoc($rsTemplates);
  }
?>
         </select> 
             <a href="/mail/admin/templates/index.php">Manage Templates </a>  &nbsp;&nbsp;
             <input type="checkbox" name="emailsenddefault" id="emailsenddefault">
             <label for="usertype"> send email by default</label>
            </td>
           </tr>
         </table>
   </div>
     </div>
   </div>
   

  
   <button type="submit" class="btn btn-primary" >Save changes</button>
   <input type="hidden" name="modifiedbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
  <input type="hidden" name="modifieddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
  <input name="ID" type="hidden" id="ID" value="<?php echo $row_rsSection['ID']; ?>" />
  <input type="hidden" name="MM_update" value="form1" /><input name="metakeywords" type="hidden" id="metakeywords" value="<?php echo $row_rsSection['metakeywords']; ?>" />
 
 
</form>
   <p>&nbsp;</p>
<script>
<!--
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1");
var TabbedPanels1 = new Spry.Widget.TabbedPanels("TabbedPanels1");
//-->
</script></div>
   <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsAccessLevels);

mysql_free_result($rsSection);

mysql_free_result($rsGroups);

mysql_free_result($rsArticleSections);

mysql_free_result($rsArticles);

mysql_free_result($rsRegions);

mysql_free_result($rsTemplates);
?>
