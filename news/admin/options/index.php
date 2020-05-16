<?php require_once('../../../Connections/aquiescedb.php'); ?><?php require_once('../../../core/includes/adminAccess.inc.php'); ?><?php require_once('../../../core/includes/upload.inc.php'); ?><?php require_once('../../../core/includes/framework.inc.php'); ?>
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

$uploaded = getUploads();
if (isset($uploaded) && is_array($uploaded)) {
	if(isset($uploaded["filename"][0]["newname"]) && $uploaded["filename"][0]["newname"]!="") { 
		$_POST['imageURL'] = $uploaded["filename"][0]["newname"]; 
	}
	$_POST['imageURL'] = (isset($_POST["noimage"])) ? "" : $_POST['imageURL'];
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE newsprefs SET newstickerfeedtitle=%s, newstickerfeed=%s, newspagefeedtitle=%s, newspagefeed=%s, usedefaultimage=%s, defaultImageURL=%s, uselightwindow=%s, sectionindextype=%s, initialsection=%s, newsshare=%s, imagesize_index=%s, imagesize_story=%s, item_class=%s, image_class=%s, text_class=%s WHERE ID=%s",
                       GetSQLValueString($_POST['newstickerfeedtitle'], "text"),
                       GetSQLValueString($_POST['newstickerfeed'], "text"),
                       GetSQLValueString($_POST['newspagefeedtitle'], "text"),
                       GetSQLValueString($_POST['newspagefeed'], "text"),
                       GetSQLValueString(isset($_POST['usedefaultimage']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['imageURL'], "text"),
                       GetSQLValueString(isset($_POST['uselightwindow']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['sectionindextype'], "int"),
                       GetSQLValueString($_POST['initialsection'], "int"),
                       GetSQLValueString(isset($_POST['newsshare']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['imagesize_index'], "text"),
                       GetSQLValueString($_POST['imagesize_story'], "text"),
                       GetSQLValueString($_POST['item_class'], "text"),
                       GetSQLValueString($_POST['image_class'], "text"),
                       GetSQLValueString($_POST['text_class'], "text"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateGoTo = "../index.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo)); exit;
}

$varRegionID_rsNewsPrefs = "1";
if (isset($regionID)) {
  $varRegionID_rsNewsPrefs = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsNewsPrefs = sprintf("SELECT * FROM newsprefs WHERE ID = %s", GetSQLValueString($varRegionID_rsNewsPrefs, "int"));
$rsNewsPrefs = mysql_query($query_rsNewsPrefs, $aquiescedb) or die(mysql_error());
$row_rsNewsPrefs = mysql_fetch_assoc($rsNewsPrefs);
$totalRows_rsNewsPrefs = mysql_num_rows($rsNewsPrefs);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsRanks = "SELECT * FROM usertype WHERE ID > 0 ORDER BY ID ASC";
$rsRanks = mysql_query($query_rsRanks, $aquiescedb) or die(mysql_error());
$row_rsRanks = mysql_fetch_assoc($rsRanks);
$totalRows_rsRanks = mysql_num_rows($rsRanks);

$varRegionID_rsSections = "1";
if (isset($regionID)) {
  $varRegionID_rsSections = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsSections = sprintf("SELECT ID, sectioname FROM newssection WHERE statusID = 1 AND regionID = 0 OR  regionID = %s ORDER BY newssection.ordernum, newssection.ID", GetSQLValueString($varRegionID_rsSections, "int"));
$rsSections = mysql_query($query_rsSections, $aquiescedb) or die(mysql_error());
$row_rsSections = mysql_fetch_assoc($rsSections);
$totalRows_rsSections = mysql_num_rows($rsSections);

$varRegionID_rsTemplates = "1";
if (isset($regionID)) {
  $varRegionID_rsTemplates = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsTemplates = sprintf("SELECT ID, templatename FROM groupemailtemplate WHERE (regionID = 0 OR regionID = %s) AND statusID = 1 ORDER BY templatename ASC", GetSQLValueString($varRegionID_rsTemplates, "int"));
$rsTemplates = mysql_query($query_rsTemplates, $aquiescedb) or die(mysql_error());
$row_rsTemplates = mysql_fetch_assoc($rsTemplates);
$totalRows_rsTemplates = mysql_num_rows($rsTemplates);
?><?php if($totalRows_rsNewsPrefs==0) { 
mysql_query("INSERT INTO newsprefs (ID) values (".intval($regionID).")", $aquiescedb) or die(mysql_error());
header("location: index.php"); exit;
} ?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Post Options"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script src="../../../SpryAssets/SpryValidationTextField.js"></script>
<script src="../../../SpryAssets/SpryTabbedPanels.js"></script>
<link href="../../../SpryAssets/SpryValidationTextField.css" rel="stylesheet"  />
<link href="../../../SpryAssets/SpryTabbedPanels.css" rel="stylesheet" >
<script>
$(document).ready(function(e) {
	toggleEmailType() ;
    toggleEmail();
});
function toggleEmail() {
	if(document.getElementById('emailtemplateID').value =="") {
		document.getElementById('emailtext').style.display = "table-row";
	} else {
		document.getElementById('emailtext').style.display = "none";
	}
}

function toggleEmailType() {
	emailtype = getRadioValue("emailtype")
	if(emailtype==1) {
		document.getElementById('storylink').style.display = "block";
		document.getElementById('fullstory').style.display = "none";
	} else {
		document.getElementById('storylink').style.display = "none";
		document.getElementById('fullstory').style.display = "block";
	}

}
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
    <!-- InstanceBeginEditable name="Body" -->
        <div class="page news"><?php require_once('../../../core/region/includes/chooseregion.inc.php'); ?>
    <h1><i class="glyphicon glyphicon-bullhorn"></i> Post Options</h1>
    <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
      <li class="nav-item"><a href="../index.php" class="nav-link"><i class="glyphicon glyphicon-arrow-left"></i> Manage Post</a></li>
    </ul></div></nav>
    <form action="<?php echo $editFormAction; ?>" method="post" enctype="multipart/form-data" name="form1" id="form1">
     <div id="TabbedPanels1" class="TabbedPanels">
       <ul class="TabbedPanelsTabGroup">
         <li class="TabbedPanelsTab" tabindex="0">Options</li>
         <li class="TabbedPanelsTab" tabindex="0">Feeds</li>
<li class="TabbedPanelsTab" tabindex="0">Advanced</li>
</ul>
       <div class="TabbedPanelsContentGroup">
         <div class="TabbedPanelsContent">
           <table border="0" cellpadding="2" cellspacing="0" class="form-table">
             <tr class="upload">
               <td align="right" valign="top"><label for="usedefaultimage">Show default image in index:</label></td>
               <td><input <?php if (!(strcmp($row_rsNewsPrefs['usedefaultimage'],1))) {echo "checked=\"checked\"";} ?> name="usedefaultimage" type="checkbox" id="usedefaultimage" value="1"></td>
             </tr>
             <tr class="upload">
               <td align="right" valign="top">Default Post image:</td>
               <td><?php if(isset($row_rsNewsPrefs['defaultImageURL'])) { ?>
                 <img src="<?php echo getImageURL($row_rsNewsPrefs['defaultImageURL'],"thumb"); ?>" alt="Image" /><br />
                 <label>
                   <input type="checkbox" name="noImage" id="noImage" />
                   remove image</label>
                 <?php } else { ?>
                 <img src="../../images/newspaper.png" width="121" height="84" alt="Default image" />
                 <?php } ?>
                 <br />
                 Change image below: <br />
                 <input type="file" name="filename" id="filename" />
                 <input name="imageURL" type="hidden" id="imageURL" value="<?php echo $row_rsNewsPrefs['defaultImageURL']; ?>" /></td>
             </tr>
             <tr>
               <td align="right">Image size index:</td>
               <td><select name="imagesize_index" id="imagesize_index"class="form-control">
                 <option value="" <?php if (!(strcmp("", $row_rsNewsPrefs['imagesize_index']))) {echo "selected=\"selected\"";} ?>>Choose image size...</option>
                 <?php foreach($image_sizes as $size=>$values) {
					$values['width'] = isset($values['width']) ? $values['width'] : "any" ;
					$values['height'] = isset($values['height']) ? $values['height'] : "any" ;?>
                 <option value="<?php echo $size; ?>" <?php if (!(strcmp($size, $row_rsNewsPrefs['imagesize_index']))) {echo "selected=\"selected\"";} ?>><?php echo ucwords(str_replace("_", " ",$size))." "; echo trim("(".$values['width']." x ".$values['height'].")","x "); ?></option>
                 <?php } ?>
                 <option value="" <?php if (!(strcmp("", $row_rsNewsPrefs['imagesize_index']))) {echo "selected=\"selected\"";} ?>>Full size</option>
               </select></td>
             </tr>
             <tr>
               <td align="right">Image size story:</td>
               <td><select name="imagesize_story" id="imagesize_story"class="form-control">
                 <option value="" <?php if (!(strcmp("", $row_rsNewsPrefs['imagesize_story']))) {echo "selected=\"selected\"";} ?>>Choose image size...</option>
                 <?php foreach($image_sizes as $size=>$values) {
					$values['width'] = isset($values['width']) ? $values['width'] : "any" ;
					$values['height'] = isset($values['height']) ? $values['height'] : "any" ;?>
                 <option value="<?php echo $size; ?>" <?php if (!(strcmp($size, $row_rsNewsPrefs['imagesize_story']))) {echo "selected=\"selected\"";} ?>><?php echo ucwords(str_replace("_", " ",$size))." "; echo trim("(".$values['width']." x ".$values['height'].")","x "); ?></option>
                 <?php } ?>
                 <option value="" <?php if (!(strcmp("", $row_rsNewsPrefs['imagesize_story']))) {echo "selected=\"selected\"";} ?>>Full size</option>
               </select></td>
             </tr>
             <tr>
               <td align="right">Use lightwindow:</td>
               <td><label>
                 <input <?php if (!(strcmp($row_rsNewsPrefs['uselightwindow'],1))) {echo "checked=\"checked\"";} ?> name="uselightwindow" type="checkbox" id="uselightwindow" value="1" />
               </label></td>
             </tr>
             <tr>
               <td align="right">Show sharing links:</td>
               <td><label>
                 <input <?php if (!(strcmp($row_rsNewsPrefs['newsshare'],1))) {echo "checked=\"checked\"";} ?> name="newsshare" type="checkbox" id="newsshare" value="1" />
                 (e.g. Faceboook - will only appear on public pages)</label></td>
             </tr>
             <tr>
               <td align="right">Section index:</td>
               <td><label>
                 <input <?php if (!(strcmp($row_rsNewsPrefs['sectionindextype'],"0"))) {echo "checked=\"checked\"";} ?> type="radio" name="sectionindextype" value="0" id="RadioGroup1_0">
                 None</label>
                 &nbsp;&nbsp;
                 <label>
                   <input <?php if (!(strcmp($row_rsNewsPrefs['sectionindextype'],"1"))) {echo "checked=\"checked\"";} ?> type="radio" name="sectionindextype" value="1" id="RadioGroup1_1">
                   List</label>
                 &nbsp;&nbsp;
                 <label>
                   <input <?php if (!(strcmp($row_rsNewsPrefs['sectionindextype'],"2"))) {echo "checked=\"checked\"";} ?> type="radio" name="sectionindextype" value="2" id="RadioGroup1_2">
                   Drop down menu</label>
                 &nbsp;&nbsp;
                 <label>
                   <input <?php if (!(strcmp($row_rsNewsPrefs['sectionindextype'],"3"))) {echo "checked=\"checked\"";} ?> type="radio" name="sectionindextype" value="3" id="RadioGroup1_3">
                   Site section pages</label></td>
             </tr>
             <tr>
               <td align="right">Initial section:</td>
               <td><select name="initialsection"  id="initialsection" class="form-control">
                 <option value="0" <?php if (0==$row_rsNewsPrefs['initialsection']) {echo "selected=\"selected\"";} ?>>All</option>
                 <option value="1" <?php if (1==$row_rsNewsPrefs['initialsection']) {echo "selected=\"selected\"";} ?>>First available</option>
                 <?php if ($totalRows_rsSections > 0) { // Show if recordset not empty ?>
                 <?php
do {  
?>
                 <option value="<?php echo $row_rsSections['ID']?>" <?php if ($row_rsSections['ID']==$row_rsNewsPrefs['initialsection']) {echo "selected=\"selected\"";} ?>><?php echo $row_rsSections['sectioname']?></option>
                 <?php
} while ($row_rsSections = mysql_fetch_assoc($rsSections));
  $rows = mysql_num_rows($rsSections);
  if($rows > 0) {
      mysql_data_seek($rsSections, 0);
	  $row_rsSections = mysql_fetch_assoc($rsSections);
  }
?>
                 <?php } // Show if recordset not empty  } ?>
               </select></td>
             </tr>
           </table>
         </div>
         <div class="TabbedPanelsContent">
           <table border="0" cellpadding="2" cellspacing="0" class="form-table">
             <tr>
               <td align="right">Home page ticker feed title:</td>
               <td><input name="newstickerfeedtitle" type="text"  id="newstickerfeedtitle" value="<?php echo $row_rsNewsPrefs['newstickerfeedtitle']; ?>" size="50" maxlength="50" class="form-control"/></td>
             </tr>
             <tr>
               <td align="right">Home page  ticker feed link:</td>
               <td><span id="sprytextfield1">
                 <input name="newstickerfeed" type="text"  id="newstickerfeed" value="<?php echo $row_rsNewsPrefs['newstickerfeed']; ?>" size="50" maxlength="255" class="form-control" />
               </span></td>
             </tr>
             <tr>
               <td align="right">Post page feed title:</td>
               <td><input name="newspagefeedtitle" type="text"  id="newspagefeedtitle" value="<?php echo $row_rsNewsPrefs['newspagefeedtitle']; ?>" size="50" maxlength="50" class="form-control"/></td>
             </tr>
             <tr>
               <td align="right">Post page feed link:</td>
               <td><span id="sprytextfield2">
                 <input name="newspagefeed" type="text"  id="newspagefeed" value="<?php echo $row_rsNewsPrefs['newspagefeed']; ?>" size="50" maxlength="255" class="form-control"/>
               </span></td>
             </tr>
           </table>
         </div>
<div class="TabbedPanelsContent">
  <h3>Styles (for Classic list style only)</h3>
           <table class="form-table">
             <tr>
               <th scope="row" class="text-right">Post Class:</th>
               <td><input name="item_class" value="<?php echo $row_rsNewsPrefs['item_class']; ?>" size="50" maxlength="50" class="form-control"></td>
             </tr>
             <tr>
               <th scope="row" class="text-right">Image Class:</th>
               <td><input name="image_class" value="<?php echo $row_rsNewsPrefs['image_class']; ?>" size="50" maxlength="50" class="form-control"></td>
             </tr>
             <tr>
               <th scope="row" class="text-right">Text Class:</th>
               <td><input name="text_class" value="<?php echo $row_rsNewsPrefs['text_class']; ?>" size="50" maxlength="50" class="form-control"></td>
             </tr>
           </table>
       </div>
</div>
     </div><button name="save" type="submit" class="btn btn-primary" id="save" >Save changes</button>
          <input name="ID" type="hidden" id="ID" value="<?php echo $row_rsNewsPrefs['ID']; ?>" />
          <input type="hidden" name="MM_update" value="form1" />
    </form>
   
    <script>
<!--
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1", "none", {isRequired:false, hint:"Enter a link to an RSS feed"});
var sprytextfield2 = new Spry.Widget.ValidationTextField("sprytextfield2", "none", {isRequired:false, hint:"Enter a link to an RSS feed"});
var TabbedPanels1 = new Spry.Widget.TabbedPanels("TabbedPanels1");
//-->
    </script></div>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsNewsPrefs);

mysql_free_result($rsRanks);

mysql_free_result($rsSections);

mysql_free_result($rsTemplates);
?>
