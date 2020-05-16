<?php require_once('../../../../../Connections/aquiescedb.php'); ?>
<?php require_once('../../../../../core/includes/adminAccess.inc.php'); ?><?php require_once('../../../../../core/includes/framework.inc.php'); ?><?php require_once('../../../../includes/productFunctions.inc.php'); ?>
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

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {

	$_POST['longID'] = createURLname($_POST['longID'], $_POST['manufacturername'], "-",  "productmanufacturer");
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
  $insertSQL = sprintf("INSERT INTO productmanufacturer (manufacturername, createdbyID, createddatetime, subsidiaryofID, `description`, longID, regionID) VALUES (%s, %s, %s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['manufacturername'], "text"),
                       GetSQLValueString($_POST['createdbyID'], "int"),
                       GetSQLValueString($_POST['createddatetime'], "date"),
                       GetSQLValueString($_POST['subsidiaryofID'], "int"),
                       GetSQLValueString($_POST['description'], "text"),
                       GetSQLValueString($_POST['longID'], "text"),
                       GetSQLValueString($_POST['regionID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($insertSQL, $aquiescedb) or die(mysql_error());
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
  $insertGoTo = "update_manufacturer.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
    $insertGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $insertGoTo)); exit;
}

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID, usertypeID, regionID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsManufacturers = "SELECT * FROM productmanufacturer ORDER BY manufacturername ASC";
$rsManufacturers = mysql_query($query_rsManufacturers, $aquiescedb) or die(mysql_error());
$row_rsManufacturers = mysql_fetch_assoc($rsManufacturers);
$totalRows_rsManufacturers = mysql_num_rows($rsManufacturers);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsRegion = "SELECT ID, title FROM region WHERE statusID = 1 ORDER BY title ASC";
$rsRegion = mysql_query($query_rsRegion, $aquiescedb) or die(mysql_error());
$row_rsRegion = mysql_fetch_assoc($rsRegion);
$totalRows_rsRegion = mysql_num_rows($rsRegion);
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Add Manufacturer"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<link href="../../../../css/defaultProducts.css" rel="stylesheet"  />
<style><!--
<?php if($totalRows_rsManufacturers<1) { 
echo ".subsidiary { display: none; } ";
}

if($totalRows_rsRegion==0 || $row_rsLoggedIn['usertypeID'] <9 ) {
	echo ".region { display: none; } ";
}

?>
--></style>
<link href="../../../../../SpryAssets/SpryValidationTextField.css" rel="stylesheet" >
<script src="../../../../../SpryAssets/SpryValidationTextField.js"></script>
<?php if(isset($body_class)) $body_class .= " products ";  ?>
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../../../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" -->
  <h1><i class="glyphicon glyphicon-shopping-cart"></i> Add Manufacturer</h1>
  
  <form action="<?php echo $editFormAction; ?>" method="post" enctype="multipart/form-data" name="form1" id="form1">
    <table class="form-table"> <tr>
        <td class="text-nowrap text-right">Manufacturer:</td>
        <td><span id="sprytextfield1">
          <input name="manufacturername" type="text"  value="" size="50" maxlength="100" class="form-control" />
          <span class="textfieldRequiredMsg">A value is required.</span></span></td>
      </tr>
      <tr class="subsidiary">
        <td class="text-nowrap text-right"><label for="subsidiaryofID">Subsidiary of:</label></td>
        <td>
          <select name="subsidiaryofID" id="subsidiaryofID"  class="form-control" >
            <option value="">None</option>
            <?php $rows = mysql_num_rows($rsManufacturers);
  if($rows > 0) {
do {  
?>
            <option value="<?php echo $row_rsManufacturers['ID']?>"><?php echo $row_rsManufacturers['manufacturername']?></option>
            <?php
} while ($row_rsManufacturers = mysql_fetch_assoc($rsManufacturers));
 
      mysql_data_seek($rsManufacturers, 0);
	  $row_rsManufacturers = mysql_fetch_assoc($rsManufacturers);
  }
?>
          </select></td>
      </tr>
      <tr class="region">
        <td class="text-nowrap text-right"><label for="regionID">Site:</label></td>
        <td>
          <select name="regionID" id="regionID" class="form-control" >
            <option value="0">All sites</option>
            <?php if($totalRows_rsRegion>0) {
do {  
?>
            <option value="<?php echo $row_rsRegion['ID']?>" <?php if(isset($regionID) && $regionID == $row_rsRegion['ID']) echo "selected"; ?>><?php echo $row_rsRegion['title']; ?></option>
            <?php
} while ($row_rsRegion = mysql_fetch_assoc($rsRegion));
  $rows = mysql_num_rows($rsRegion);
  if($rows > 0) {
      mysql_data_seek($rsRegion, 0);
	  $row_rsRegion = mysql_fetch_assoc($rsRegion);
  }
			}
?>
          </select></td>
      </tr> <tr>
        <td class="text-nowrap text-right">&nbsp;</td>
        <td><button type="submit" class="btn btn-primary" >Add Manufacturer</button></td>
      </tr>
    </table>
    <input type="hidden" name="createdbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
    <input type="hidden" name="createddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
    <input type="hidden" name="MM_insert" value="form1" />
    <input type="hidden" name="longID" id="longID">
  </form>

  <script>
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1");
  </script>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsManufacturers);

mysql_free_result($rsRegion);
?>
