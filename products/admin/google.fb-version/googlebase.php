<?php require_once('../../../Connections/aquiescedb.php'); ?><?php require_once('../../../core/includes/adminAccess.inc.php'); ?>
<?php require_once('../../includes/productHeader.inc.php'); ?><?php require_once('../../includes/productFunctions.inc.php'); ?><?php require_once('../../includes/basketFunctions.inc.php'); ?><?php require_once('../../../core/includes/framework.inc.php'); ?><?php require_once('inc/googlebase.inc.php'); ?>
<?php set_time_limit(600); // 10 mins
ini_set("session.gc_maxlifetime","10800");
ini_set("max_execution_time","600"); // 10 mins
ini_set("max_input_time","600"); // 10 mins
?>
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

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form")) {
  $updateSQL = sprintf("UPDATE productprefs SET gbaseVAT=%s, googlemerchantID=%s, googlemerchantoptions=%s, googlemerchantoutofstock=%s, googlemerchantpreorder=%s WHERE ID=%s",
                       GetSQLValueString($_POST['gbaseVAT'], "int"),
                       GetSQLValueString($_POST['googlemerchantID'], "int"),
                       GetSQLValueString(isset($_POST['googlemerchantoptions']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['googlemerchantoutofstock']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['googlemerchantpreorder']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
}

$varRegionID_rsCategory = "1";
if (isset($regionID)) {
  $varRegionID_rsCategory = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsCategory = sprintf("SELECT ID, title FROM productcategory WHERE statusID = 1 AND (regionID = 0 OR regionID = %s) ORDER BY title ASC", GetSQLValueString($varRegionID_rsCategory, "int"));
$rsCategory = mysql_query($query_rsCategory, $aquiescedb) or die(mysql_error());
$row_rsCategory = mysql_fetch_assoc($rsCategory);
$totalRows_rsCategory = mysql_num_rows($rsCategory);

$varRegionID_rsManufacturers = "1";
if (isset($regionID)) {
  $varRegionID_rsManufacturers = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsManufacturers = sprintf("SELECT ID, manufacturername FROM productmanufacturer WHERE statusID = 1 AND (regionID = 0 OR regionID = %s) ORDER BY manufacturername ASC", GetSQLValueString($varRegionID_rsManufacturers, "int"));
$rsManufacturers = mysql_query($query_rsManufacturers, $aquiescedb) or die(mysql_error());
$row_rsManufacturers = mysql_fetch_assoc($rsManufacturers);
$totalRows_rsManufacturers = mysql_num_rows($rsManufacturers);

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form")) {
	// reload to get updated product prefs 
	header("location: googlebase.php"); exit;	
}


$regionID = (isset($regionID) && $regionID>0) ? intval($regionID) : 1;




$currentPage = $_SERVER["PHP_SELF"];





if(isset($_GET['tdf'])) {
	require_once('../../../core/includes/framework.inc.php');
	csvHeaders("google-base-products","\t");
	$text = createGoogleProductFeed($_GET['categoryID'], $_GET['manufacturerID'], false, true);
	print($text);
	exit;
}

?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Manage Google Merchant Centre Products"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script>
function copyToPasteboard() {
  /* Get the text field */
  var copyText = document.getElementById("feedurl");

  /* Select the text field */
  copyText.select();

  /* Copy the text inside the text field */
  document.execCommand("copy");

  /* Alert the copied text */
  alert("Copied to clipboard: " + copyText.value);
}
</script>
<link href="../../css/defaultProducts.css" rel="stylesheet"  />
<style><!--

--></style>
<?php if(isset($body_class)) $body_class .= " products ";  ?>
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" --><div class="page products"><?php require_once('../../../core/region/includes/chooseregion.inc.php'); ?>
 
    <h1><i class="glyphicon glyphicon-shopping-cart"></i> Google Merchant Center</h1>
    <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
      <li><a href="../products/index.php" class="link_undo"><i class="glyphicon glyphicon-arrow-left"></i> Manage Products</a></li>
      
      <li><a href="import_gtins.php"><i class="glyphicon glyphicon-cloud-upload"></i> Import GTINs</a></li>
   
    
      <li><a href="https://merchants.google.com/" target="_blank" rel="noopener" ><img src="/core/images/icons/google_favicon.png" width="16" height="16" alt="Google"> Merchant Center Dashboard</a></li>
   
    </ul></div></nav><form method="POST" action="<?php echo $editFormAction; ?>" name="form" class="form-inline"><fieldset><legend>Settings</legend>
    <p>
      <label>Merchant ID: <input name="googlemerchantID" type="text" size="20" maxlength="20" value="<?php echo isset($row_rsProductPrefs['googlemerchantID']) ? htmlentities($row_rsProductPrefs['googlemerchantID'], ENT_COMPAT,"UTF-8") : ""; ?>" class="form-control"></label>
      
      <p>
        <label><input name="googlemerchantoptions" type="checkbox" id="googlemerchantoptions" value="1" <?php if($row_rsProductPrefs['googlemerchantoptions'] == 1) echo "checked"; ?>> Show product options as separate items in feed (if they each have unique identifiers)</label></p>
        
         <p>
        <label><input name="googlemerchantoutofstock" type="checkbox" id="googlemerchantoutofstock" value="1" <?php if($row_rsProductPrefs['googlemerchantoutofstock'] == 1) echo "checked"; ?>> Show out of stock items in feed</label> &nbsp;&nbsp;&nbsp;   <label><input name="googlemerchantpreorder" type="checkbox" id="googlemerchantpreorder" value="1" <?php if($row_rsProductPrefs['googlemerchantpreorder'] == 1) echo "checked"; ?>> Show pre-order items in feed</label></p>
        <p>Include VAT in feed: 
        
        
         <label>
            <input type="radio" name="gbaseVAT" value="0" <?php if($row_rsProductPrefs['gbaseVAT'] == 0) echo "checked"; ?>  onchange="this.form.submit()">
            Same as site </label> &nbsp;&nbsp;&nbsp;
            
            
          <label>
            <input type="radio" name="gbaseVAT" value="1"  <?php if($row_rsProductPrefs['gbaseVAT'] == 1) echo "checked"; ?> onchange="this.form.submit()">
            Yes </label> &nbsp;&nbsp;&nbsp;
       
          <label>
            <input type="radio" name="gbaseVAT" value="2" <?php if($row_rsProductPrefs['gbaseVAT'] == 2) echo "checked"; ?>  onchange="this.form.submit()">
            No</label>
          <input type="hidden" name="ID" id="ID" value="<?php echo $row_rsProductPrefs['ID']; ?>">
          <br>
        </p><button type="submit" class="btn btn-default btn-secondary">Save changes</button>
    </fieldset>
      <input type="hidden" name="MM_update" value="form">
    </form><h2>Scheduled Feed:</h2>
    <p>You can set Google Merchant Center to automatically fetch your full feed at regular intervals. Your feed will be available as a "Tab Delimited Text File" at:</p><pre><?php 
	if(isset($thisRegion['hostdomain'])) {
		$host = ($thisRegion['www']==1) ? "www.".$thisRegion['hostdomain'] : $thisRegion['hostdomain'];
	} else {
		$host =  $_SERVER['HTTP_HOST']; 
	}
	
	$protocol = getProtocol()."://";

		
	
	
	 $url =  $protocol.$host."/Uploads/feeds/merchantcenter".$regionID.".txt"; ?><a href="<?php echo $url; ?>" target="_blank" rel="noopener"><?php echo $url; 
	 
	 writeGoogleFeedFile();
	 
	 ?></a></pre><input type="hidden" value="<?php echo $url; ?>" id="feedurl">
    <p><button onclick="copyToPasteboard()" class="btn btn-default"><i class="glyphicon glyphicon-copy"></i> Copy link</button> Frequency of updates: every time you update products</p>
    <h2>Manual Feed:</h2>
    <p>You can downoad a tab delimited text file to manually upload to Google Merchant Center:
    <form method="get" class="form-inline"><input name="tdf" type="hidden" value="true" >
    <p>Include: <select name="categoryID" class="form-control">
      <option value="0">All categories</option>
      <?php
do {  
?>
      <option value="<?php echo $row_rsCategory['ID']?>"><?php echo $row_rsCategory['title']?></option>
      <?php
} while ($row_rsCategory = mysql_fetch_assoc($rsCategory));
  $rows = mysql_num_rows($rsCategory);
  if($rows > 0) {
      mysql_data_seek($rsCategory, 0);
	  $row_rsCategory = mysql_fetch_assoc($rsCategory);
  }
?>
    </select>
      <select name="manufacturerID" id="manufacturerID"  class="form-control">
        <option value="0">All manufacturers</option>
        <?php
do {  
?>
        <option value="<?php echo $row_rsManufacturers['ID']?>"><?php echo $row_rsManufacturers['manufacturername']?></option>
        <?php
} while ($row_rsManufacturers = mysql_fetch_assoc($rsManufacturers));
  $rows = mysql_num_rows($rsManufacturers);
  if($rows > 0) {
      mysql_data_seek($rsManufacturers, 0);
	  $row_rsManufacturers = mysql_fetch_assoc($rsManufacturers);
  }
?>
      </select> <button type="submit" class="btn btn-default btn-secondary" >Export Tab Delimited File...</button>
    </p>
  
    </form>
    
    
    <h2>Important: </h2>
    <p>Only products that meet Google's listing criteria will be included in the above feeds, i.e:</p>
   
      <ol>
        <li>
       
            Their category must have an equivalent <a href="http://www.google.com/basepages/producttype/taxonomy.en-GB.txt" target="_blank" rel="noopener">Google Category</a> set (Manage Categories)</li>
        <li>They must that have a Manufacturer/Brand
        
        (Manage Products)</li>
        <li>
          
          They must have either a MPN or GTIN/UPC
          
          (Manage Products)</li>
        </ol>
      <p>Google also requires a unique ID. If set, SKU is used,  otherwise an automatically generated ID is used.</p>
      <p>Compatible products/categories will be listed with a <img src="../../../core/images/icons/google_favicon.png" width="16" height="16" style="vertical-align:middle;"> icon.</p>
    </form>
 </div>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsCategory);

mysql_free_result($rsManufacturers);

mysql_free_result($rsProducts);

mysql_free_result($rsProductPrefs);
?>
