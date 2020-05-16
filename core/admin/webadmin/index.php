<?php require_once('../../../Connections/aquiescedb.php'); ?><?php require_once('../../includes/adminAccess.inc.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "10";
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
  $updateSQL = sprintf("UPDATE preferences SET license_key=%s, googlemapsAPI=%s, openspaceAPI=%s, uselocations=%s, useregions=%s, usesections=%s, userdirectory=%s, controlpanelURL=%s, enablewidgets=%s WHERE ID=%s",
                       GetSQLValueString($_POST['license_key'], "text"),
                       GetSQLValueString($_POST['gmapapi'], "text"),
                       GetSQLValueString($_POST['openspaceAPI'], "text"),
                       GetSQLValueString(isset($_POST['uselocations']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['useregions']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['usesections']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['userdirectory']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['controlpanelURL'], "text"),
                       GetSQLValueString(isset($_POST['enablewidgets']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
	
	$update = "UPDATE mailprefs SET  enableGroupEmail =";
	$update .= isset($_POST['enableGroupEmail']) ? 1 : 0;
	$update .= ", enableletters =";
	$update .= isset($_POST['enableletters']) ? 1 : 0;
	$Result1 = mysql_query($update, $aquiescedb) or die(mysql_error());
	
  $updateGoTo = "/core/admin/";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo)); exit;
}

$varRegionID_rsPreferences = "1";
if (isset($regionID)) {
  $varRegionID_rsPreferences = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = sprintf("SELECT * FROM preferences WHERE ID = %s", GetSQLValueString($varRegionID_rsPreferences, "int"));
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT usertypeID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsMailPrefs = "SELECT * FROM mailprefs";
$rsMailPrefs = mysql_query($query_rsMailPrefs, $aquiescedb) or die(mysql_error());
$row_rsMailPrefs = mysql_fetch_assoc($rsMailPrefs);
$totalRows_rsMailPrefs = mysql_num_rows($rsMailPrefs);
?><!doctype html>
<!-- Copyright Paul Egan. Any unauthorised copying, reproduction or alteration is strictly prohibited -->
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Advanced Preferences"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../seo/includes/seo.inc.php'); ?>
<?php require_once('../../includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script src="../../../SpryAssets/SpryTabbedPanels.js"></script>
<link href="../../../SpryAssets/SpryTabbedPanels.css" rel="stylesheet"  />
 <?php if ($row_rsLoggedIn['usertypeID'] < 10) { echo "<style>
 .webadmin {
	 display:none;
 }
 </style>"; } ?>
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" -->
     <div  class="page preferences">
    <h1>Web Administrator Settings</h1>
    <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav"><li><a href="../preferences.php" class="link_undo"><i class="glyphicon glyphicon-arrow-left"></i> Back</a></li>
    <li><a href="show_phpinfo.php" target="_blank" class="link_manage" rel="noopener"><i class="glyphicon glyphicon-cog"></i> Server Information</a></li>
    <li><a href="encryption.php" target="_blank" class="link_manage" rel="noopener"><i class="glyphicon glyphicon-cog"></i> Encryption</a></li>
    <li><a href="tls_test.php" target="_blank" class="link_manage" rel="noopener"><i class="glyphicon glyphicon-cog"></i> TLS Test</a></li></ul></div></nav>
   
     
      <p>These options are only available to Web  Administrator.</p>
     
      <?php if(isset($submit_error)) { ?><p class="alert alert-danger" role="alert"><?php echo $submit_error; ?></p><?php } ?>
<form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1">
      <div id="TabbedPanels1" class="TabbedPanels">
        <ul class="TabbedPanelsTabGroup">
          <li class="TabbedPanelsTab webadmin" tabindex="0">Features</li>
          <li class="TabbedPanelsTab" tabindex="0"> Keys</li>
         
           <li class="TabbedPanelsTab" tabindex="0">Preferences File<br />
          </li>
        </ul>
        <div class="TabbedPanelsContentGroup">
          <div class="TabbedPanelsContent webadmin">
            <p> <label for="controlpanelURL">Control Panel home page:</label></p>
            <p>
             
              <input name="controlpanelURL" type="text" id="controlpanelURL" value="<?php echo $row_rsPreferences['controlpanelURL']; ?>" class="form-control">
            </p>
            <p>These options set out the feautures available on the site. We recommened only activating features that are required as more  features make the CMS more complex for users to use. </p>
            <table border="0" cellpadding="5" cellspacing="0" class="form-table">
              <tr>
                <td align="right">Use locations:</td>
                <td><input <?php if (!(strcmp($row_rsPreferences['uselocations'],1))) {echo "checked=\"checked\"";} ?> name="uselocations" type="checkbox" id="uselocations" value="1" /></td>
              </tr>
              <tr>
                <td align="right">Use sections:</td>
                <td><input <?php if (!(strcmp($row_rsPreferences['usesections'],1))) {echo "checked=\"checked\"";} ?> type="checkbox" name="usesections" id="usesections" /></td>
              </tr>
              <tr>
                <td align="right">Enable multi sites: </td>
                <td><input <?php if (!(strcmp($row_rsPreferences['useregions'],1))) {echo "checked=\"checked\"";} ?> name="useregions" type="checkbox" id="useregions" value="1" /></td>
              </tr>
              <tr>
                <td align="right">User directory: </td>
                <td><input <?php if (!(strcmp($row_rsPreferences['userdirectory'],1))) {echo "checked=\"checked\"";} ?> name="userdirectory" type="checkbox" id="userdirectory" value="1" /></td>
              </tr>
              <tr>
                <td align="right">Enable widgets:</td>
                <td><input name="enablewidgets" type="checkbox" id="enablewidgets" value="1" <?php if (!(strcmp($row_rsPreferences['enablewidgets'],1))) {echo "checked=\"checked\"";} ?> /></td>
              </tr>
              <tr>
                <td align="right">Enable Group Mail:</td>
                <td><input <?php if (!(strcmp($row_rsMailPrefs['enableGroupEmail'],1))) {echo "checked=\"checked\"";} ?> name="enableGroupEmail" type="checkbox" id="enableGroupEmail" value="1" /></td>
              </tr>
              <tr>
                <td align="right"><label for="enableletters">Enable letters:</label></td>
                <td><input <?php if (!(strcmp($row_rsMailPrefs['enableletters'],1))) {echo "checked=\"checked\"";} ?> name="enableletters" type="checkbox" id="enableletters" value="1">
                </td>
              </tr>
            </table>
            <p><a href="../../../search/admin/index.php" >Site Search </a></p>
            <p><a href="../../../install/tools/index.php" >Upgrade from earlier version</a></p>
            <p><a href="../../../admin/setup.php" >Set up site</a></p>
            <p><a href="../../../admin/reset.php" >Reset Site Database </a></p>
            <p><a href="../../../documents/admin/files/index.php" >File Manager</a></p>
           
          </div>
          <div class="TabbedPanelsContent">
            <h3 class="form-inline">Site Licence Key: 
              <input name="license_key" type="text" id="license_key" value="<?php echo $row_rsPreferences['license_key']; ?>" size="20" maxlength="20" class="form-control" />
            </h3>
            <h3>To use the advanced features such as the Google Maps functionality you need to enter a unique API key. More information at:</h3>
            <p><a href="https://console.developers.google.com/cloud-resource-manager" target="_blank" rel="noopener">https://console.developers.google.com/cloud-resource-manager</a></p>
          
            <table border="0" cellpadding="2" cellspacing="0" class="form-table">
              <tr>
                <td align="right"><label>Google  API key: </label></td>
                <td><input name="gmapapi" type="text"  id="gmapapi" value="<?php echo $row_rsPreferences['googlemapsAPI']; ?>" size="50" maxlength="255" class="form-control"/></td>
              </tr>
              <tr>
                <td align="right"><label>OpenSpace Maps API key: </label></td>
                <td><input name="openspaceAPI" type="text"  id="openspaceAPI" value="<?php echo $row_rsPreferences['openspaceAPI']; ?>" size="50" maxlength="255" class="form-control" /></td>
              </tr>
            </table>
            <label> </label>
            <p></p>
            <p></p>
          </div>
          
            <div class="TabbedPanelsContent">
            <p>
      <?php $constants = get_defined_constants(true); 
	foreach ($constants['user'] as $key => $value ) {
		echo $key." : <input name =\"".$key."\" type=\"text\" value = \"". $value ."\"><br />";
	}
	?>
    </p>
    <p><a href="/documents/admin/files/file.php?filename=/Connections/preferences.php&openbutton=true">Edit file directly</a></p></div>
        </div>
      </div>
      <ul>
        <li><br class="clearfloat" />
          <button type="submit" class="btn btn-primary" >Save changes</button>
          <input name="ID" type="hidden" id="ID" value="<?php echo $row_rsPreferences['ID']; ?>" />
          
          
          <input type="hidden" name="MM_update" value="form1" />
        </li>
      </ul>
</form>
<script>
<!--
var TabbedPanels1 = new Spry.Widget.TabbedPanels("TabbedPanels1", {defaultTab:1});
//-->
      </script> </div>
   <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsPreferences);

mysql_free_result($rsLoggedIn);

mysql_free_result($rsMailPrefs);
?>