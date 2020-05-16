<?php require_once('../../../Connections/aquiescedb.php'); ?><?php require_once('../../../core/includes/adminAccess.inc.php'); ?>
<?php require_once('../../../members/includes/userfunctions.inc.php'); ?>
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

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "formupdatesection")) {
$_POST['longID'] = preg_replace("/[^a-zA-Z0-9_\-]/", "", $_POST['longID']); // clean
$_POST['regionID'] = (isset($_POST['regionID']) && $_POST['regionID']!="")  ? $_POST['regionID'] : 1;

}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "formupdatesection")) {
  $updateSQL = sprintf("UPDATE articlesection SET longID=%s, showlink=%s, metakeywords=%s, metadescription=%s, subsectionofID=%s, `description`=%s, accesslevel=%s, groupreadID=%s, writerankID=%s, groupwriteID=%s, regionID=%s, linkaction=%s, newWindow=%s, `class`=%s, modifiedbyID=%s, modifieddatetime=%s, approverankID=%s WHERE ID=%s",
                       GetSQLValueString($_POST['longID'], "text"),
                       GetSQLValueString($_POST['showlink'], "int"),
                       GetSQLValueString($_POST['metakeywords'], "text"),
                       GetSQLValueString($_POST['metadescription'], "text"),
                       GetSQLValueString($_POST['subsectionofID'], "int"),
                       GetSQLValueString($_POST['description'], "text"),
                       GetSQLValueString($_POST['acesslevel'], "int"),
                       GetSQLValueString($_POST['groupreadID'], "int"),
                       GetSQLValueString($_POST['writerankID'], "int"),
                       GetSQLValueString($_POST['groupwriteID'], "int"),
                       GetSQLValueString($_POST['regionID'], "int"),
                       GetSQLValueString($_POST['linkaction'], "int"),
                       GetSQLValueString($_POST['newWindow'], "int"),
                       GetSQLValueString($_POST['class'], "text"),
                       GetSQLValueString($_POST['modifiedbyID'], "int"),
                       GetSQLValueString($_POST['modifieddatetime'], "date"),
                       GetSQLValueString($_POST['approverankID'], "int"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "formupdatesection")) {
	
  $updateGoTo = "index.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo)); exit;
}

$colname_rsSection = "-1";
if (isset($_GET['sectionID'])) {
  $colname_rsSection = $_GET['sectionID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsSection = sprintf("SELECT articlesection.* FROM articlesection  WHERE articlesection.ID = %s", GetSQLValueString($colname_rsSection, "int"));
$rsSection = mysql_query($query_rsSection, $aquiescedb) or die(mysql_error());
$row_rsSection = mysql_fetch_assoc($rsSection);
$totalRows_rsSection = mysql_num_rows($rsSection);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsAccessLevel = "SELECT * FROM usertype WHERE usertype.ID >0";
$rsAccessLevel = mysql_query($query_rsAccessLevel, $aquiescedb) or die(mysql_error());
$row_rsAccessLevel = mysql_fetch_assoc($rsAccessLevel);
$totalRows_rsAccessLevel = mysql_num_rows($rsAccessLevel);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = "SELECT * FROM preferences";
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID, usertypeID,  regionID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsRegions = "SELECT ID, title FROM region WHERE statusID = 1 ORDER BY title ASC";
$rsRegions = mysql_query($query_rsRegions, $aquiescedb) or die(mysql_error());
$row_rsRegions = mysql_fetch_assoc($rsRegions);
$totalRows_rsRegions = mysql_num_rows($rsRegions);

$varRegionID_rsRootSections = "1";
if (isset($regionID)) {
  $varRegionID_rsRootSections = $regionID;
}
$varThisSection_rsRootSections = "-1";
if (isset($_GET['sectionID'])) {
  $varThisSection_rsRootSections = $_GET['sectionID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsRootSections = sprintf("SELECT articlesection.ID, articlesection.`description` FROM articlesection WHERE subsectionofID = 0 AND (articlesection.regionID = %s  OR articlesection.regionID = 0)  AND ID !=%s", GetSQLValueString($varRegionID_rsRootSections, "int"),GetSQLValueString($varThisSection_rsRootSections, "int"));
$rsRootSections = mysql_query($query_rsRootSections, $aquiescedb) or die(mysql_error());
$row_rsRootSections = mysql_fetch_assoc($rsRootSections);
$totalRows_rsRootSections = mysql_num_rows($rsRootSections);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsGroups = "SELECT ID, groupname FROM usergroup ORDER BY groupname ASC";
$rsGroups = mysql_query($query_rsGroups, $aquiescedb) or die(mysql_error());
$row_rsGroups = mysql_fetch_assoc($rsGroups);
$totalRows_rsGroups = mysql_num_rows($rsGroups);
?><!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" --><title><?php $pageTitle = "Update  Section"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title><!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<style><!--
<?php if ($row_rsPreferences['useregions'] != 1) { 
echo ".region {display: none;}";
 } ?>
--></style>
<script src="../../../SpryAssets/SpryValidationTextField.js"></script>
<script src="../../../SpryAssets/SpryTabbedPanels.js"></script>
<link href="../../../SpryAssets/SpryValidationTextField.css" rel="stylesheet"  />
<link href="../../css/defaultArticles.css" rel="stylesheet" >
<link href="../../../SpryAssets/SpryTabbedPanels.css" rel="stylesheet" >
<script>
$(document).ready(function(e) {
    seoPopulate(document.getElementById('description').value,document.getElementById('description').value);
});
</script>
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" --> <div class="page articles">
   <?php  if ($row_rsPreferences['useregions'] == 1 && $row_rsLoggedIn['usertypeID'] < 9 && $row_rsSection['regionID'] != $row_rsLoggedIn['regionID'] ) { //not authorised to edit ?>
   <p class="alert warning alert-warning" role="alert">You are not authorised to update these section details in this site.
   <p>
   <?php } else { ?>
   <h1><i class="glyphicon glyphicon-file"></i> Update  Section</h1>
   <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
     <li><a href="../index.php" class="link_undo"><i class="glyphicon glyphicon-arrow-left"></i> Manage Pages</a></li>
     <li><a href="index.php" class="link_undo"><i class="glyphicon glyphicon-arrow-left"></i> Manage Sections</a></li>
   </ul></div></nav>
   
   <form action="<?php echo $editFormAction; ?>" method="POST" name="formupdatesection" class="section" id="formupdatesection">
     <div id="TabbedPanels1" class="TabbedPanels">
       <ul class="TabbedPanelsTabGroup">
         <li class="TabbedPanelsTab" tabindex="0">Details</li>
         <li class="TabbedPanelsTab" tabindex="0">Advanced</li>
       </ul>
       <div class="TabbedPanelsContentGroup">
         <div class="TabbedPanelsContent">
     <table class="form-table">
       <tr>
         <td class="text-nowrap text-right">Section title:</td>
         <td><span id="sprytextfield1">
           <input name="description" id="description" type="text" value="<?php echo htmlentities($row_rsSection['description'], ENT_COMPAT, 'UTF-8'); ?>" size="50" maxlength="255"  onblur="seoPopulate(this.value, this.value);" class="form-control"/>
<span class="textfieldRequiredMsg"><br />
          A value is required.</span></span></td>
       </tr>
       <tr  class="region">
         <td class="text-nowrap text-right">Site:</td>
         <td>
           <select name="regionID" id="regionID" class="form-control">
             <option value="1" <?php if (!(strcmp(1, $row_rsSection['regionID']))) {echo "selected=\"selected\"";} ?>><?php echo isset($region['text_choose']) ? htmlentities($region['text_choose'], ENT_COMPAT, "UTF-8") : "Choose..." ?></option>
             <option value="0" <?php if (!(strcmp(0, $row_rsSection['regionID']))) {echo "selected=\"selected\"";} ?>>All sites</option>
             <?php
do {  
?><option value="<?php echo $row_rsRegions['ID']?>"<?php if (!(strcmp($row_rsRegions['ID'], $row_rsSection['regionID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsRegions['title']?></option>
             <?php
} while ($row_rsRegions = mysql_fetch_assoc($rsRegions));
  $rows = mysql_num_rows($rsRegions);
  if($rows > 0) {
      mysql_data_seek($rsRegions, 0);
	  $row_rsRegions = mysql_fetch_assoc($rsRegions);
  }
?>
             </select>
           </td>
       </tr>
       <tr>
         <td class="text-nowrap text-right">Parent:</td>
         <td><select name="subsectionofID" id="subsectionofID" class="form-control">
           <option value="0" <?php if (!(strcmp(0, $row_rsSection['subsectionofID']))) {echo "selected=\"selected\"";} ?>>None</option>
           <?php
do {  
?>
  <option value="<?php echo $row_rsRootSections['ID']?>"<?php if (!(strcmp($row_rsRootSections['ID'], $row_rsSection['subsectionofID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsRootSections['description']?></option>
           <?php
} while ($row_rsRootSections = mysql_fetch_assoc($rsRootSections));
  $rows = mysql_num_rows($rsRootSections);
  if($rows > 0) {
      mysql_data_seek($rsRootSections, 0);
	  $row_rsRootSections = mysql_fetch_assoc($rsRootSections);
  }
?>
           </select></td>
       </tr>
       <tr>
         <td class="text-nowrap text-right">Show in menus:</td>
         <td class="text-nowrap"><label data-toggle="tooltip" title="Show links to this section in any navigation menus.">
           <input <?php if (!(strcmp($row_rsSection['showlink'],"1"))) {echo "checked=\"checked\"";} ?> name="showlink" type="radio" id="showlink" value="1"  />
           Yes</label>
           &nbsp;&nbsp;&nbsp;
           <label data-toggle="tooltip" title="Do not show links to this section in any navigation menus">
             <input <?php if (!(strcmp($row_rsSection['showlink'],"-1"))) {echo "checked=\"checked\"";} ?> name="showlink" type="radio" id="showlink" value="-1"  />
             No</label>
           &nbsp;&nbsp;&nbsp;
           <label data-toggle="tooltip" title="Do not show links to this section in any navigation menus, however do show link to this  section in site map/index page">
             <input <?php if (!(strcmp($row_rsSection['showlink'],"0"))) {echo "checked=\"checked\"";} ?> name="showlink" type="radio" id="showlink" value="0"  />
             Site map only</label></td>
       </tr>
       <tr>
         <td class="text-nowrap text-right">Link goes to:</td>
         <td><label>
           <input <?php if (!(strcmp($row_rsSection['linkaction'],"1"))) {echo "checked=\"checked\"";} ?> name="linkaction" type="radio" id="linkaction_0" value="1" >
           First page in section</label>
           &nbsp;&nbsp;&nbsp;
           <label>
             <input <?php if (!(strcmp($row_rsSection['linkaction'],"2"))) {echo "checked=\"checked\"";} ?> type="radio" name="linkaction" value="2" id="linkaction_1">
             Section index</label>
           &nbsp;&nbsp;&nbsp;
           <input <?php if (!(strcmp($row_rsSection['linkaction'],"3"))) {echo "checked=\"checked\"";} ?> type="radio" name="linkaction" value="3" id="linkaction_2">
           No link
           </label></td>
       </tr>
       <tr>
         <td class="text-nowrap text-right">Opens in:</td>
         <td><label>
           <input <?php if (!(strcmp($row_rsSection['newWindow'],"0"))) {echo "checked=\"checked\"";} ?> name="newWindow" type="radio" id="newWindow_0" value="0" >
           Same window</label>
           &nbsp;&nbsp;&nbsp;
           <label>
             <input <?php if (!(strcmp($row_rsSection['newWindow'],"1"))) {echo "checked=\"checked\"";} ?> type="radio" name="newWindow" value="1" id="newWindow_1">
             New window</label></td>
       </tr>
     </table>
         </div>
<div class="TabbedPanelsContent">
  <table class="form-table">
    <tr>
      <th scope="row"><label for="class">Menu/Page Class:</label></th>
      <td><input name="class" type="text" id="class" data-toggle="tooltip" title="Add CSS class for this menu item or page" value="<?php echo $row_rsSection['class']; ?>" size="50" maxlength="50" class="form-control"></td>
    </tr>
    <tr  class="longID">
      <td class="text-nowrap text-right">URL Name:</td>
      <td><input name="longID" type="text" id="longID" value="<?php echo $row_rsSection['longID']; ?>" size="50" maxlength="100" class="form-control"/></td>
    </tr>
    <tr>
      <td class="text-nowrap text-right">Can view:</td>
      <td class="form-inline"><select name="acesslevel" id="acesslevel" class="form-control">
        <option value="0" <?php if (!(strcmp(0, $row_rsSection['accesslevel']))) {echo "selected=\"selected\"";} ?>>Everyone</option>
        <?php
do {  
?>
        <option value="<?php echo $row_rsAccessLevel['ID']?>"<?php if (!(strcmp($row_rsAccessLevel['ID'], $row_rsSection['accesslevel']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsAccessLevel['name']?></option>
        <?php
} while ($row_rsAccessLevel = mysql_fetch_assoc($rsAccessLevel));
  $rows = mysql_num_rows($rsAccessLevel);
  if($rows > 0) {
      mysql_data_seek($rsAccessLevel, 0);
	  $row_rsAccessLevel = mysql_fetch_assoc($rsAccessLevel);
  }
?>
        </select>
        <select name="groupreadID" id="groupreadID" class="group form-control">
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
          </select></td>
    </tr>
    <tr>
      <td class="text-nowrap text-right">Can create/edit:</td>
      <td class="form-inline"><select name="writerankID" id="writerankID" class="form-control">
        <option value="0" <?php if (!(strcmp(0, $row_rsSection['writerankID']))) {echo "selected=\"selected\"";} ?>>Everyone</option>
        <?php
do {  
?>
        <option value="<?php echo $row_rsAccessLevel['ID']?>"<?php if (!(strcmp($row_rsAccessLevel['ID'], $row_rsSection['writerankID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsAccessLevel['name']?></option>
        <?php
} while ($row_rsAccessLevel = mysql_fetch_assoc($rsAccessLevel));
  $rows = mysql_num_rows($rsAccessLevel);
  if($rows > 0) {
      mysql_data_seek($rsAccessLevel, 0);
	  $row_rsAccessLevel = mysql_fetch_assoc($rsAccessLevel);
  }
?>
      </select>
        <select name="groupwriteID" id="groupwriteID" class="group form-control">
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
        </select></td>
    </tr>
    <tr>
      <td class="text-nowrap text-right">Requires approval by:</td>
      <td class="form-inline"><select name="approverankID" id="approverankID" class="form-control">
        <option value="0" <?php if (!(strcmp(0, $row_rsSection['approverankID']))) {echo "selected=\"selected\"";} ?>>No one</option>
        <?php
do {  
?>
        <option value="<?php echo $row_rsAccessLevel['ID']?>"<?php if (!(strcmp($row_rsAccessLevel['ID'], $row_rsSection['approverankID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsAccessLevel['name']?></option>
        <?php
} while ($row_rsAccessLevel = mysql_fetch_assoc($rsAccessLevel));
  $rows = mysql_num_rows($rsAccessLevel);
  if($rows > 0) {
      mysql_data_seek($rsAccessLevel, 0);
	  $row_rsAccessLevel = mysql_fetch_assoc($rsAccessLevel);
  }
?>
      </select> <span class="help-icon" data-toggle="tooltip" title="By default pages can be posted live by anyone who can create and edit them. If you choose an approval rank, the page can only be saved as draft by anyone below that rank."></span></td>
    </tr>
    </table>
  <p>&nbsp;</p>
</div>
       </div>
     </div>
   <span> <button type="submit" class="btn btn-primary" >Save Changes</button></span>
     <input type="hidden" name="ID" value="<?php echo $row_rsSection['ID']; ?>" />
     <input type="hidden" name="MM_update" value="formupdatesection" />
     <input name="metakeywords" type="hidden" id="metakeywords" value="<?php echo $row_rsSection['metakeywords']; ?>" />
     <input name="metadescription" type="hidden" id="metadescription" value="<?php echo $row_rsSection['metadescription']; ?>" />
     <input type="hidden" name="modifieddatetime" id="modifieddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>">
        <input name="modifiedbyID" type="hidden" id="modifiedbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>">
   </form>
 <?php } // authorised?>
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
mysql_free_result($rsSection);

mysql_free_result($rsAccessLevel);

mysql_free_result($rsPreferences);

mysql_free_result($rsLoggedIn);

mysql_free_result($rsRegions);

mysql_free_result($rsRootSections);

mysql_free_result($rsGroups);
?>
