<?php require_once('../../Connections/aquiescedb.php'); ?>
<?php require_once('../../core/includes/adminAccess.inc.php'); ?><?php require_once('../../core/includes/framework.inc.php'); ?><?php require_once('../includes/functions.inc.php'); ?>
<?php require_once('../../members/includes/userfunctions.inc.php'); ?>
<?php
$_GET['sectionID'] = isset($_GET['sectionID']) ? $_GET['sectionID'] : 1;

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
$regionID = (isset($regionID) && intval($regionID)>0) ? intval($regionID) : 1;


$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}




if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
	$submit_error = "";
	$articletype = $_POST['articletype']; /* to reset radios on error */
	$_POST['articletype'] = ($_POST['articletype'] ==4) ? 1 : $_POST['articletype'];
	// articletype = 2 (section) not used yet
	
	
	$_POST['regionID'] = (isset($_POST['regionID']) && $_POST['regionID']!="")  ? $_POST['regionID'] : 1;
	
	if($_POST['sectionID']==0) { // is home page so check one already doesn't exist
		$select = "SELECT ID FROM article WHERE sectionID = 0 AND regionID = ".GetSQLValueString($_POST['regionID'], "int");
		$result = mysql_query($select, $aquiescedb) or die(mysql_error());
		if( mysql_num_rows($result)>0) { // already exists
			$submit_error .="A home page already exists for this site. Please choose another section.<br />";
	
		} // end already exists
	} // end is home page
	else if ($_POST['sectionID']==-2) { // create new section
		$_POST['sectionID'] = createArticleSection($_POST['title'],$_POST['regionID'],$_POST['createdbyID']);
	}

	// get selected section prefs
	$select = "SELECT * FROM articlesection WHERE ID = ".GetSQLValueString($_POST['sectionID'], "int");
	$result = mysql_query($select, $aquiescedb) or die(mysql_error());
	$section = mysql_fetch_assoc($result);
	
	// check to see if user has rights to write to this section
	
	
	if(!($section['writerankID'] <= $_SESSION['MM_UserGroup'] 
	&& ($section['groupwriteID'] == 0 || userinGroup($section['groupwriteID'], $userID)))) {
		$submit_error .= "You do not have access priviledges to create a page in this section.<br />";
	}
	
	
	
	if($section['approverankID'] > $_SESSION['MM_UserGroup']) {
		// requires approval so add as pending
		unset($_POST['statusID']);
	}
	

	// check to see if article has different region to section....
	if($row['regionID'] !=0 && $_POST['regionID'] != 0 && $section['regionID'] != $_POST['regionID']) {
		$submit_error .= "You have set a site for the article which differs from its parent section's site. Please correct and resubmit.<br />";
	}
	
	
	

	
	if($submit_error!="") {
		unset($_POST["MM_insert"]);
	}
	
} // insert


if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
	
$articleID = createArticle($_POST['articletype'],  $regionID, $_POST['title'],  $_POST['body'], $_POST['metakeywords'], $_POST['metadescription'], $_POST['showlink'], $_POST['statusID'], $_POST['redirectURL'], $_POST['sectionID'], $_POST['headHTML'], $_POST['createdbyID']); 
	
	
	if(isset($_POST['templateID']) && $_POST['templateID']>0) {
	$select = "SELECT headHTML, body, notes, class FROM article WHERE ID = ".intval($_POST['templateID'])." LIMIT 1";
	 	$result = mysql_query($select, $aquiescedb) or die(mysql_error());
	 	$row = mysql_fetch_assoc($result);
		$update = "UPDATE article SET headHTML = ".GetSQLValueString($row['headHTML'],"text").", body = ".GetSQLValueString($row['body'],"text").", notes = ".GetSQLValueString($row['notes'],"text").", class = ".GetSQLValueString($row['class'],"text")." WHERE  ID = ".$articleID;
		mysql_query($update, $aquiescedb) or die(mysql_error());
	}
		
		
	
  $insertGoTo = "update_article.php?articleID=".$articleID;
  if (isset($_SERVER['QUERY_STRING'])) {
    $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
    $insertGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $insertGoTo)); exit;
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
$query_rsPreferences = "SELECT preferences.useregions, preferences.usesections FROM preferences";
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);

$varRegionID_rsSections = "0";
if (isset($regionID)) {
  $varRegionID_rsSections = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsSections = sprintf("SELECT articlesection.ID, articlesection.`description`, parentsection.`description`AS parent FROM articlesection LEFT JOIN articlesection AS parentsection ON (articlesection.subsectionofID = parentsection.ID) WHERE  %s = 0 OR articlesection.regionID = %s OR articlesection.regionID= 0", GetSQLValueString($varRegionID_rsSections, "int"),GetSQLValueString($varRegionID_rsSections, "int"));
$rsSections = mysql_query($query_rsSections, $aquiescedb) or die(mysql_error());
$row_rsSections = mysql_fetch_assoc($rsSections);
$totalRows_rsSections = mysql_num_rows($rsSections);

$varRegionID_rsTemplates = "1";
if (isset($regionID)) {
  $varRegionID_rsTemplates = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsTemplates = sprintf("SELECT ID, title FROM article WHERE versionofID IS NULL AND regionID = %s AND article.sectionID = -1 ORDER BY title ASC", GetSQLValueString($varRegionID_rsTemplates, "int"));
$rsTemplates = mysql_query($query_rsTemplates, $aquiescedb) or die(mysql_error());
$row_rsTemplates = mysql_fetch_assoc($rsTemplates);
$totalRows_rsTemplates = mysql_num_rows($rsTemplates);
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Add Page"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script>
$(document).ready(function() {
						   toggleArticleType();
						   });
function toggleArticleType() {
	if(getRadioValue("articletype") == 1) { // page
		$(".redirect").hide();
		$(".templates").hide();
	} else if(getRadioValue("articletype") == 4) {//template
		$(".redirect").hide();
		$(".templates").show();
	} else if(getRadioValue("articletype") == 2) {//section (not used yet)
		$(".redirect").hide();
		$(".templates").hide();
	} else { // redirect
		$(".redirect").show();
		$(".templates").hide();
	}
}
</script>
<script src="/SpryAssets/SpryValidationTextField.js"></script>
<link href="/SpryAssets/SpryValidationTextField.css" rel="stylesheet"  />

<style >
<?php if (strcmp($row_rsPreferences['usesections'], 1)) {
?>  .section {
display:none;
}
<?php
}
 if (strcmp($row_rsPreferences['useregions'], 1)) {
?>  .region {
display:none;
}
<?php
}
?>
</style>
<link href="../css/defaultArticles.css" rel="stylesheet"  />
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
    <div class="page articles">
       <?php require_once('../../core/region/includes/chooseregion.inc.php'); ?>
<h1><i class="glyphicon glyphicon-file"></i> Add Page</h1>

    <?php require_once('../../core/includes/alert.inc.php'); ?>
    <form action="<?php echo $editFormAction; ?>" method="post" enctype="multipart/form-data" name="form1" id="form1"   onsubmit="if(Spry.Widget.Form.validate(form1)) { seoPopulate(document.getElementById('title').value);  } else { alert('There are highlighted errors on the page. Please correct before submitting.'); return false; } ">
   
     
      
            
          
            <table class="form-table">
              
              <tr class="form-group"  >
                <td class="text-nowrap text-right">Type:</td>
                <td>
                  <label>
                    <input name="articletype" type="radio" id="articletype_0" value="1" <?php if(!isset($articletype) || $articletype ==1) echo " checked "; ?> onClick="toggleArticleType()">
                    Blank Page</label>
&nbsp;&nbsp;&nbsp; <label>
 <?php if($totalRows_rsTemplates>0) { ?>
                <input name="articletype" type="radio" id="articletype_4" value="4" <?php if(isset($articletype) && $articletype ==4) echo " checked "; ?> onClick="toggleArticleType()">
                    Page from template &nbsp;&nbsp;&nbsp;   </label>
              <?php } ?><label style="display:none">
                    <input type="radio" name="articletype" value="2" id="articletype_1" <?php if(isset($articletype) && $articletype ==2) echo " checked "; ?> onClick="toggleArticleType()">
                    Section &nbsp;&nbsp;&nbsp; </label>
                 
                  <label>
                    <input type="radio" name="articletype" value="3" id="articletype_2" <?php if(isset($articletype) && $articletype ==3) echo " checked "; ?> onClick="toggleArticleType()">
                    Redirect</label>
                 
                </td>
              </tr>
              <tr class="form-group"  >
                <td class="text-nowrap text-right">In section:</td>
                <td><div><span id="sprytextfield1"><span class="textfieldRequiredMsg">A title is required.</span></span><span class="section"><?php if ($totalRows_rsSections > 0) { // Show if recordset not empty ?>
                      <select name="sectionID" id="sectionID" onChange="if(this.value==-2) alert('This page will be added to a new section with the same title.\n\nYou can update this later in Manage Sections.');" class="form-control">
                        <option value="" <?php if (!isset($_GET['sectionID']) || $_GET['sectionID']=="") {echo "selected=\"selected\"";} ?>><?php echo isset($region['text_choose']) ? htmlentities($region['text_choose'], ENT_COMPAT, "UTF-8") : "Choose..." ?></option>
                        <option value="0" <?php if (!(strcmp(0, $_GET['sectionID']))) {echo "selected=\"selected\"";} ?>>Home Page</option><option value="-1" <?php if (!(strcmp(-1, $_GET['sectionID']))) {echo "selected=\"selected\"";} ?>>Templates</option>
                        
                        
                        <option value="-2" <?php if (!(strcmp(-2, $_GET['sectionID']))) {echo "selected=\"selected\"";} ?>>New section...</option>
                        <option value="" disabled ></option>
                        <?php if($totalRows_rsSections>0) {
do {  
?>
                        <option value="<?php echo $row_rsSections['ID']?>"<?php if (!(strcmp($row_rsSections['ID'], $_GET['sectionID']))) {echo "selected=\"selected\"";} ?>><?php echo isset($row_rsSections['parent']) ? $row_rsSections['parent']." &rsaquo; " : ""; echo  $row_rsSections['description'];  ?></option>
                        <?php
} while ($row_rsSections = mysql_fetch_assoc($rsSections));
  $rows = mysql_num_rows($rsSections);
  if($rows > 0) {
      mysql_data_seek($rsSections, 0);
	  $row_rsSections = mysql_fetch_assoc($rsSections);
  }}
?>
                      </select> 
                      <?php } // Show if recordset not empty ?>

                     </span></div></td>
              </tr>
             
        <tr  class="templates form-group" >
          <td class="text-nowrap text-right"><label for="templateID article">Apply template</label>:</td>
          <td>
            <select name="templateID" id="templateID" class="form-control">
              <option value="">Choose…</option>
              <?php
do {  
?>
              <option value="<?php echo $row_rsTemplates['ID']?>"><?php echo $row_rsTemplates['title']?></option>
              <?php
} while ($row_rsTemplates = mysql_fetch_assoc($rsTemplates));
  $rows = mysql_num_rows($rsTemplates);
  if($rows > 0) {
      mysql_data_seek($rsTemplates, 0);
	  $row_rsTemplates = mysql_fetch_assoc($rsTemplates);
  }
?>
            </select>
            </td>
        </tr>
              <tr class="form-group" >
                <td class="text-nowrap text-right">Page Title:</td>
                <td><span id="sprytextfield2">
                  <input name="title" id = "title" type="text" value="<?php echo isset($_POST['title']) ? $_POST['title'] : ""; ?>" size="50" maxlength="100" class="form-control" />
                <span class="textfieldRequiredMsg">A value is required.</span></span></td>
              </tr>
              <tr class="redirect form-group" >
                <td class="text-nowrap text-right"><label for="redirectURL">Redirect to URL:</label></td>
                <td>
                <input name="redirectURL" type="text" id="redirectURL" size="50" maxlength="255" value="<?php echo isset($_POST['redirectURL']) ? $_POST['redirectURL'] : ""; ?>" class="form-control"></td>
              </tr>
              <tr class="form-group" >
                <td class="text-nowrap text-right">Show in menus:</td>
                <td class="text-nowrap"><label data-toggle="tooltip" title="Show links to this page in any navigation menus. You can also add manual links to this page within any page content"><input name="showlink" type="radio" id="showlink" value="1" checked="checked" />
                    Yes</label>
                     &nbsp;&nbsp;&nbsp;
                     <label data-toggle="tooltip" title="Do not show links to this page in any navigation menus, however you can add manual links within page content"><input name="showlink" type="radio" id="showlink" value="-1"  />
                    No</label>  &nbsp;&nbsp;&nbsp;
                    <label data-toggle="tooltip" title="Do not show links to this page in any navigation menus, however do show link to this  page in site map/index page"><input name="showlink" type="radio" id="showlink" value="0"  />
                    Site map only</label>
                     </td>
              </tr>
              <tr class="form-group" >
                <td class="text-nowrap text-right">&nbsp;</td>
                <td class="text-nowrap"><button type="submit" class="btn btn-primary" >Add page...</button></td>
              </tr>
            </table>
       
      <input name="createdbyID" type="hidden" id="createdbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
      <input type="hidden" name="MM_insert" value="form1" />
      <input name="createddatetime" type="hidden" id="createddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
      <input name="metakeywords" type="hidden" id="metakeywords" size="50" maxlength="255" value="<?php echo isset($_POST['metakeywords']) ? $_POST['metakeywords'] : ""; ?>" />
      <input name="metadescription" type="hidden" id="metadescription" size="50" maxlength="255" value="<?php echo isset($_POST['metadescription']) ? $_POST['metadescription'] : ""; ?>" />
      
<input name="longID" type="hidden"  id="longID" value="" />
<input type="hidden" name="seotitle" id="seotitle" />
<input name="headHTML" type="hidden" id="headHTML" value="&lt;style&gt;&lt;!-- --&gt;&lt;/style&gt;">
<input type="hidden" name="regionID" id="regionID" value="<?php echo isset($regionID) ? $regionID : 1; ?>" />
<input type="hidden" name="statusID" id="statusID" value="0" />


    </form>
<script>
<!--
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1", "none", {hint:"Enter title here"});
//-->

var sprytextfield2 = new Spry.Widget.ValidationTextField("sprytextfield2");
</script></div>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsPreferences);

mysql_free_result($rsSections);

mysql_free_result($rsTemplates);
?>
