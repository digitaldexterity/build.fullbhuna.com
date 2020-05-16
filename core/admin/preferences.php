<?php require_once('../../Connections/aquiescedb.php'); ?><?php require_once('../includes/adminAccess.inc.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}$MM_authorizedUsers = "7,8,9,10";
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

// 7 - can view but cannot make changes

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1") && $_SESSION['MM_UserGroup'] < 8) {
	$submit_error = "You do not have privileges to update this page.";
	unset($_POST["MM_update"]);
	
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE preferences SET license_key=%s, orgname=%s, orgaddress=%s, orgphone=%s, orgfax=%s, orgskype=%s, contactemail=%s, header=%s, footer=%s, openinghours=%s, head=%s WHERE ID=%s",
                       GetSQLValueString($_POST['license_key'], "text"),
                       GetSQLValueString($_POST['orgname'], "text"),
                       GetSQLValueString($_POST['orgaddress'], "text"),
                       GetSQLValueString($_POST['orgphone'], "text"),
                       GetSQLValueString($_POST['orgfax'], "text"),
                       GetSQLValueString($_POST['skype'], "text"),
                       GetSQLValueString($_POST['contactemail'], "text"),
                       GetSQLValueString($_POST['header'], "text"),
                       GetSQLValueString($_POST['footer'], "text"),
                       GetSQLValueString($_POST['openinghours'], "text"),
                       GetSQLValueString($_POST['head'], "text"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateGoTo = "/core/admin/index.php?msg=".urlencode("Your details have been saved.")."";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo));exit;
}
?>
<?php
$regionID = (isset($regionID ) && intval($regionID) >0) ? intval($regionID ) : 1;

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}


mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID, users.usertypeID, users.firstname, users.surname, users.changepassword, users.lastlogin FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = "SELECT * FROM preferences WHERE ID =".$regionID ."";
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);

if($totalRows_rsPreferences==0) {
	$insert = "INSERT INTO preferences (ID, createdbyID, createddatetime) VALUES (".$regionID .", ".$row_rsLoggedIn['ID'].",'".date('Y-m-d H:i:s')."')";
	mysql_query($insert, $aquiescedb) or die(mysql_error());
	header("location: ".$_SERVER['PHP_SELF']); exit;
}


?>
<?php if ($row_rsLoggedIn['changepassword'] == 1) { 
$url ="/members/profile/change_password.php?compulsary=true&returnURL=/core/admin/";
$url .= isset($_GET['firstrun']) ? "&currentpassword=admin" : "";
header("Location: ".$url);exit;}?><?php if(isset($row_rsPreferences['controlpanelURL']) && $row_rsPreferences['controlpanelURL'] !="") { header("location: ".$row_rsPreferences['controlpanelURL']); exit; } ?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Preferences"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../seo/includes/seo.inc.php'); ?>
<?php require_once('../includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script src="../../SpryAssets/SpryTabbedPanels.js"></script>
<link href="../../SpryAssets/SpryTabbedPanels.css" rel="stylesheet"  />
<style><!--
<?php if($_SESSION['MM_UserGroup'] <10) {
	echo "#tabLicense { display: none; }";
} ?>
--></style><?php require_once('../../core/tinymce/tinymce.inc.php'); ?>
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" --><div  class="page preferences"><?php require_once('../region/includes/chooseregion.inc.php'); ?>
      <h1>Preferences</h1>
      <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
      <li class="nav-item"><a href="index.php" class="nav-link"><i class="glyphicon glyphicon-arrow-left"></i> Dashboard</a></li>
      <li><a href="../seo/admin/index.php" class="nav-link"><i class="glyphicon glyphicon-ok"></i> SEO</a></li>
  <li class="nav-item"><a href="../../location/admin/googlemaps.php" class="nav-link"><i class="glyphicon glyphicon-globe"></i> Google Maps</a></li>
  <li class="nav-item"><a href="backup/index.php" class="nav-link"><i class="glyphicon glyphicon-repeat"></i> Back-up</a></li>
  <?php if ($_SESSION['MM_UserGroup'] == 10) { // if web admin ?>
  <li class="nav-item"><a href="webadmin/index.php" class="nav-link"><i class="glyphicon glyphicon-cog"></i> Web Administrator </a></li>
  <?php }//end web admin ?>
      
      </ul></div></nav>
    
    <?php if(isset($_GET['msg'])) { ?><p class="message alert alert-info" role="alert"><?php echo htmlentities($_GET['msg'], ENT_COMPAT, "UTF-8"); ?></p><?php } ?>
  <?php if(isset($submit_error)) { ?><p class="alert alert-danger" role="alert"><?php echo $submit_error; ?></p><?php } ?>
<form action="<?php echo $editFormAction; ?>" method="POST" name="form1" id="form1">
        <div id="TabbedPanels1" class="TabbedPanels">
          <ul class="TabbedPanelsTabGroup">
<li class="TabbedPanelsTab" tabindex="0">Organisation Details</li>
<li class="TabbedPanelsTab" tabindex="0" id="openingHoursTab">Opening Hours</li>

<li class="TabbedPanelsTab" tabindex="0">Header</li>
<li class="TabbedPanelsTab" tabindex="0">Footer</li>
<li class="TabbedPanelsTab" tabindex="0">&lt;HEAD&gt;<br>
</li>
<li class="TabbedPanelsTab" tabindex="0" id="tabLicense">License</li>
          </ul>
          <div class="TabbedPanelsContentGroup">
<div class="TabbedPanelsContent">
              <table class="form-table">
                <tr>
                  <td class="text-nowrap text-right top">Name:</td>
                  <td><input name="orgname" type="text"  id="orgname" value="<?php echo htmlentities($row_rsPreferences['orgname'], ENT_COMPAT, "UTF-8"); ?>" size="50" maxlength="100" class="form-control" /></td>
                </tr>
                <tr>
                  <td class="text-nowrap text-right top">Address:</td>
                  <td><textarea name="orgaddress" cols="50" rows="6" id="orgaddress"  class="form-control"><?php echo htmlentities($row_rsPreferences['orgaddress'], ENT_COMPAT, "UTF-8"); ?></textarea></td>
                </tr>
                <tr>
                  <td class="text-nowrap text-right top">Phone:</td>
                  <td><input name="orgphone" type="text"  id="orgphone" value="<?php echo htmlentities($row_rsPreferences['orgphone'], ENT_COMPAT, "UTF-8"); ?>" size="20" maxlength="50"  class="form-control"/></td>
                </tr>
                <tr>
                  <td class="text-nowrap text-right top">Fax:</td>
                  <td><input name="orgfax" type="text"  id="orgfax" value="<?php echo htmlentities($row_rsPreferences['orgfax'], ENT_COMPAT, "UTF-8"); ?>" size="20" maxlength="20"  class="form-control"/></td>
                </tr>
                <tr>
                  <td class="text-nowrap text-right top">Skype:</td>
                  <td><input name="skype" type="text"  id="skype" value="<?php echo htmlentities($row_rsPreferences['orgskype'], ENT_COMPAT, "UTF-8"); ?>" size="20" maxlength="50"  class="form-control"/></td>
                </tr>
                <tr>
                  <td class="text-nowrap text-right top">Email:</td>
                  <td><input name="contactemail" type="email" multiple id="contactemail" value="<?php echo htmlentities($row_rsPreferences['contactemail'], ENT_COMPAT, "UTF-8"); ?>" size="50" maxlength="255" class="form-control" /></td>
                </tr>
              </table>
            </div>
<div class="TabbedPanelsContent">
          <p>Opening Hours:</p>
              <p>
                <textarea name="openinghours" id="openinghours" cols="45" rows="5"  class="form-control"><?php echo htmlentities($row_rsPreferences['openinghours'], ENT_COMPAT, "UTF-8"); ?></textarea>
              </p>
            </div>

<div class="TabbedPanelsContent">
  <p>If the header content is editable, you can do so here:</p>
  <p>
   
      <textarea name="header" class="tinymce"><?php echo  $row_rsPreferences['header']; ?></textarea>
   
  </p>
  <p>This code will appear immediately after the &lt;BODY&gt; tag.</p>
</div>
<div class="TabbedPanelsContent">
  <p>If the footer content is editable, you can do so here:</p>
  <p>
    <textarea name="footer" class="tinymce">
      <?php echo $row_rsPreferences['footer']; ?>
      </textarea>
  </p>
  <p>This code will appear immediately before the close &lt;/BODY&gt; tag.</p>
</div>
<div class="TabbedPanelsContent">
  <p>For advanced users, if the &lt;HEAD&gt; content is editable, you can add code here:</p>
  <p>
    <textarea name="head" cols="100" rows="10"  class="form-control"><?php echo $row_rsPreferences['head']; ?></textarea>
  </p>
</div>
<div class="TabbedPanelsContent">
  <p>The license key for this site is:</p>
  <p>
    <input name="license_key" type="text" id="license_key" value="<?php echo isset($_GET['key']) ? htmlentities($_GET['key']) : $row_rsPreferences['license_key']; ?>" size="32" maxlength="32"  class="form-control" />
  </p>
  <p>If you move this site to another domain, you may need a new license key. <a href="http://activate.fullbhuna.com/">Click here for more information</a>.</p>
</div>
          </div>
      </div>
     
       <p>
<button type="submit" class="btn btn-primary" >Save Changes</button>
          <input name="ID" type="hidden" id="ID" value="<?php echo $row_rsPreferences['ID']; ?>" />
          <input type="hidden" name="MM_update" value="form1" />
                  </form>
   </p>
      <?php if (isset($_GET['defaultTab'])) { echo '<script>
<!--
var TabbedPanels1 = new Spry.Widget.TabbedPanels("TabbedPanels1", {defaultTab:'.intval($_GET['defaultTab']).'});
//-->
    </script>'; } else { ?>
    <script>
<!--
var TabbedPanels1 = new Spry.Widget.TabbedPanels("TabbedPanels1");
//-->
    </script>
    <?php } ?></div>
  
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsPreferences);

mysql_free_result($rsLoggedIn);
?>
