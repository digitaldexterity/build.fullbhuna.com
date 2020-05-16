<?php require_once('../../../Connections/aquiescedb.php'); ?><?php require_once('../../includes/adminAccess.inc.php'); ?>
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
  $updateSQL = sprintf("UPDATE preferences SET googlemeta=%s, bingmeta=%s, alexameta=%s, googleanalytics=%s, googleconversions=%s, addthiscode=%s, facebookconversions=%s, googleanalyticsecommerce=%s, googleconversionsall=%s, googletagmanager=%s WHERE ID=%s",
                       GetSQLValueString($_POST['googlemeta'], "text"),
                       GetSQLValueString($_POST['bingmeta'], "text"),
                       GetSQLValueString($_POST['alexameta'], "text"),
                       GetSQLValueString($_POST['googleanalytics'], "text"),
                       GetSQLValueString($_POST['googleconversions'], "text"),
                       GetSQLValueString($_POST['addthiscode'], "text"),
                       GetSQLValueString($_POST['facebookconversions'], "text"),
                       GetSQLValueString($_POST['googleanalyticsecommerce'], "int"),
                       GetSQLValueString($_POST['googleconversionsall'], "int"),
                       GetSQLValueString($_POST['googletagmanager'], "text"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
}

$regionID = (isset($regionID ) && intval($regionID) >0) ? intval($regionID ) : 1;

$varRegionID_rsPreferences = "1";
if (isset($regionID)) {
  $varRegionID_rsPreferences = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = sprintf("SELECT * FROM preferences WHERE ID = %s", GetSQLValueString($varRegionID_rsPreferences, "int"));
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);

if($totalRows_rsPreferences==0) {
	$insert = "INSERT INTO preferences  (ID) VALUES (".GetSQLValueString($varRegionID_rsPreferences, "int").")";
	mysql_query($insert, $aquiescedb) or die(mysql_error());
	header("location: index.php"); exit;
}

?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Search engine optimisation"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../includes/seo.inc.php'); ?>
<?php require_once('../../includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script src="../../../SpryAssets/SpryValidationTextarea.js"></script>
<script src="../../../SpryAssets/SpryValidationTextField.js"></script>
<link href="../../../SpryAssets/SpryValidationTextarea.css" rel="stylesheet"  />
<link href="../../../SpryAssets/SpryValidationTextField.css" rel="stylesheet"  />
<style><!--
.callout {
	width:300px;
	border: 1px solid #999;
	padding:10px;
}
--></style>
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
        <div class="page seo">
  <?php require_once('../../region/includes/chooseregion.inc.php'); ?>
<h1><i class="glyphicon glyphicon-globe"></i> Search Engine Optimisation</h1>
  <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
    <li><a href="visitors/index.php" class="link_undo"><i class="glyphicon glyphicon-arrow-left"></i> Manage Visitors</a></li>
  </ul></div></nav>
<div class="callout fltrt">
  <h3>SEO Checklist</h3>
  <ol>
    <li>Write plenty of original, quality, keyword-rich content - but do not try to manipulate Google - you <em>will</em> be penalised!</li>
    <li>Get plenty of good, <em>relevant</em> and <em>natural</em> backlinks (i.e. other related quality sites to link to your site)</li>
    <li>Register your site with  <a href="http://www.dmoz.org" target="_blank" rel="noopener">DMOZ</a> (free)</li>
    <li>Register your domain for 10 years or more, and adding a <a href="https://www.globalsign.com/en-au/ssl-information-center/what-is-an-ssl-certificate/" target="_blank" rel="noopener">secure certificate</a> is  recommended - especially for ecommerce sites.</li>
    <li>Sign up to Google services such as <a href="http://www.google.com/business/placesforbusiness/" target="_blank" rel="noopener"> Places,</a> <a href="http://www.google.co.uk/merchants" target="_blank" rel="noopener">Merchant Center</a> and <a href="https://www.google.co.uk/certifiedshops/for-businesses/">Trusted Shops</a>.</li>
    <li>Join <a href="http://plus.google.com/" target="_blank" rel="noopener">Google+</a> and other social networks</li>
    <li>Write a <a href="http://www.blogger.com/" target="_blank" rel="noopener">blog</a> and link to your site</li>
    <li>Add descriptive titles to all your pages</li>
    <li>Add META tags to your pages</li>
    <li>Add ALT and TITLE tags to all your images and links</li>
  </ol>
</div><p>Help get your site higher in search engine search results by following all the tips on this page.<h2>Site Maps</h2>
  <p>Site maps help search engines crawl your site.</p>
  <p><a href="sitemap_make.php">Create/Update site maps</a></p>
  <h2>AutoLinks</h2>
  <p>AutoLinks make it easy to create links between important pages on your site. Just enter key words or phrases and the pages they should link to and AutoLinks will do the rest. </p>
  <p><a href="autolinks/index.php">AutoLinks</a></p>
  <h2>Meta Tags</h2>
  <p>Descriptive and relevant &quot;Meta Tags&quot; - hidden items on your page that search engines can read - then this will help your SEO. Click on the Options tab on the page editors to edit these. Often these will be autmatically filled in with text which you can update.</p>
  <h2>Descriptive URLs</h2>
  <p>If your server supports &quot;Mod rewrite&quot;, then you can use this to make your page addresses more &quot;search engine friendly&quot;. In the &quot;Options&quot; tab of the page editor change the Address Name to one of your choice. This is usually automatically filled in for you, but you can change it. Get your web server aministrator to set this up using the example .htaccess file and changing the preferences file.</p>
  <h2>Webmaster Tools and Analytics</h2>
  <p>Sign up for the following services and enter any provided code snippets into your web site below:</p>
<form action="<?php echo $editFormAction; ?>" method="POST" name="form1" id="form1">
  <table border="0" cellpadding="2" cellspacing="0" class="form-table">
    <tr>
      <td class="top text-right"><a href="http://www.google.com/analytics/" target="_blank" rel="noopener">
        <label for="googleanalytics">Google Analytics</label>
        </a></td>
      <td><span id="sprytextarea1">
        <textarea name="googleanalytics" id="googleanalytics" cols="45" rows="5" class="form-control"><?php echo htmlentities($row_rsPreferences['googleanalytics']); ?></textarea>
</span></td>
      </tr>
    
    <tr>
      <td class="top text-right"><label for="googleanalytics"><a href="http://www.google.com/analytics/" target="_blank" rel="noopener">Google Adwords Conversions:</a></label></td>
      <td><p>
        <textarea name="googleconversions" id="googleconversions" cols="45" rows="5" class="form-control"><?php echo htmlentities($row_rsPreferences['googleconversions']); ?></textarea>
      </p>
        <p>
         
            
            
            
            <label>
          <input <?php if (!(strcmp($row_rsPreferences['googleconversionsall'],"0"))) {echo "checked=\"checked\"";} ?> type="radio" name="googleconversionsall" value="0" >
          All pages</label>
        &nbsp;&nbsp;&nbsp;
        
        <label>
          <input <?php if (!(strcmp($row_rsPreferences['googleconversionsall'],"1"))) {echo "checked=\"checked\"";} ?> type="radio" name="googleconversionsall" value="1" >
          Order Confirmation</label>
        &nbsp;&nbsp;&nbsp;
        
        <label>
          <input <?php if (!(strcmp($row_rsPreferences['googleconversionsall'],"2"))) {echo "checked=\"checked\"";} ?> type="radio" name="googleconversionsall" value="2" >
          Payment Success</label>
        &nbsp;&nbsp;&nbsp;
        
        
        </p></td>
    </tr>
    <tr>
      <td class="top text-right"><label for="facebookconversions"><a href="http://www.facebook.com/help/435189689870514/" target="_blank" rel="noopener">Facebook Conversions:</a></label></td>
      <td><textarea name="facebookconversions" id="facebookconversions" cols="45" rows="5" class="form-control"><?php echo htmlentities($row_rsPreferences['facebookconversions']); ?></textarea></td>
    </tr><tr>
      <td class=" text-right"><label for="googleanalyticsecommerce"><a href="https://developers.google.com/analytics/devguides/collection/gajs/gaTrackingEcommerce?csw=1" target="_blank" rel="noopener">Ecommerce tracking:</a></label></td>
      <td>
        <label>
          <input <?php if (!(strcmp($row_rsPreferences['googleanalyticsecommerce'],"0"))) {echo "checked=\"checked\"";} ?> type="radio" name="googleanalyticsecommerce" value="0" id="googleanalyticsecommerce_0">
          Off</label>
        &nbsp;&nbsp;&nbsp;
        <label>
          <input <?php if (!(strcmp($row_rsPreferences['googleanalyticsecommerce'],"1"))) {echo "checked=\"checked\"";} ?> type="radio" name="googleanalyticsecommerce" value="1" id="googleanalyticsecommerce_1">
          On (order confirmation)</label>
          &nbsp;&nbsp;&nbsp;
        <label>
          <input <?php if (!(strcmp($row_rsPreferences['googleanalyticsecommerce'],"2"))) {echo "checked=\"checked\"";} ?> type="radio" name="googleanalyticsecommerce" value="2" id="googleanalyticsecommerce_2">
          On (payment success)</label>
     </td>
    </tr>
    <tr>
      <td class=" text-right"><label for="googletagmanager"><a href="https://tagmanager.google.com/" target="_blank" rel="noopener">Google Tag Manager Container ID</a>:</label></td>
      <td><input name="googletagmanager" type="text" id="googletagmanager" value="<?php echo $row_rsPreferences['googletagmanager']; ?>" size="50" maxlength="50" placeholder="e.g. GTM-XXXX" class="form-control"></td>
    </tr>
    <tr>
      <td class="top text-right"><a href="http://www.google.com/webmasters/tools/"><label for="googlemeta">Google Webmaster Tools</label></a></td>
      <td><span id="sprytextfield1">
        <input name="googlemeta" type="text" id="googlemeta" value="<?php echo htmlentities($row_rsPreferences['googlemeta']); ?>" size="50" maxlength="255" class="form-control"/>
  </span></td>
    </tr>
    <tr>
      <td class="top text-right"><label for="bingmeta"><a href="http://www.bing.com/toolbox/webmasters/" target="_blank" rel="noopener">Bing Webmaster tools</a></label></td>
      <td><span id="sprytextfield2">
        <input name="bingmeta" type="text" id="bingmeta" value="<?php echo htmlentities($row_rsPreferences['bingmeta']); ?>"  size="50" maxlength="255" class="form-control"/>
</span></td>
      </tr>
    <tr>
      <td class="top text-right"><label for="alexameta"><a href="http://www.alexa.com/" target="_blank" rel="noopener">Alexa Tools</a></label></td>
      <td><span id="sprytextfield3">
        <input name="alexameta" type="text" id="alexameta" value="<?php echo htmlentities($row_rsPreferences['alexameta']); ?>"  size="50" maxlength="255" class="form-control"/>
</span></td>
      </tr>
    <tr>
      <td class="top text-right"><label for="addthiscode"><a href="http://www.addthis.com/" target="_blank" rel="noopener">Add This</a></label></td>
      <td><span id="sprytextarea2">
        <textarea name="addthiscode" id="addthiscode" cols="45" rows="5" class="form-control"><?php echo $row_rsPreferences['addthiscode']; ?></textarea>
</span></td>
    </tr>
    <tr>
      <td class="top text-right"><input name="ID" type="hidden" id="ID" value="<?php echo $row_rsPreferences['ID']; ?>" /></td>
      <td>
        <button type="submit" name="savebutton" id="savebutton" class="btn btn-primary" >Save changes...</button>
        </td>
      </tr>
    <tr>
      <td class="top text-right">&nbsp;</td>
      <td>Other &lt;HEAD&gt; or &lt;BODY&gt; code <a href="/core/admin/preferences.php">can be added in Site Preferences</a></td>
    </tr>
    </table>
  <input type="hidden" name="MM_update" value="form1" />
</form>


    <script>
<!--
var sprytextarea1 = new Spry.Widget.ValidationTextarea("sprytextarea1", {isRequired:false, hint:"Paste Google Analytics code here"});
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1", "none", {isRequired:false, hint:"Paste Google meta tag here"});
var sprytextfield2 = new Spry.Widget.ValidationTextField("sprytextfield2", "none", {isRequired:false, hint:"Paste Bing meta tag here"});
var sprytextfield3 = new Spry.Widget.ValidationTextField("sprytextfield3", "none", {isRequired:false, hint:"Paste Alexa meta tag here"});
var sprytextarea2 = new Spry.Widget.ValidationTextarea("sprytextarea2", {isRequired:false});
//-->
    </script></div>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsPreferences);
?>
