<?php require_once('../../../Connections/aquiescedb.php'); ?>
<?php require_once('../../../core/includes/framework.inc.php'); ?><?php require_once('../../../core/includes/adminAccess.inc.php'); ?>
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
  $updateSQL = sprintf("UPDATE preferences SET streetview=%s, uselocationcategory=%s WHERE ID=%s",
                       GetSQLValueString(isset($_POST['streetview']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['uselocationcategory']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
	$updateSQL = "UPDATE locationprefs SET  locationdescriptor = ".GetSQLValueString($_POST['locationdescriptor'], "text").", postcodecheckerkey = ".GetSQLValueString($_POST['postcodecheckerkey'], "text").", publicaccess = ".GetSQLValueString(isset($_POST['publicaccess']) ? "true" : "", "defined","1","0");

  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
  $updateGoTo = "../index.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo)); exit;
}

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = "SELECT streetview, uselocationcategory FROM preferences";
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLocationPrefs = "SELECT * FROM locationprefs WHERE ID = ".$regionID;
$rsLocationPrefs = mysql_query($query_rsLocationPrefs, $aquiescedb) or die(mysql_error());
$row_rsLocationPrefs = mysql_fetch_assoc($rsLocationPrefs);
$totalRows_rsLocationPrefs = mysql_num_rows($rsLocationPrefs);

if($totalRows_rsLocationPrefs==0) {
	$insert = "INSERT INTO locationprefs (ID, publicaccess) VALUES (".$regionID.",1)";
	mysql_query($insert, $aquiescedb) or die(mysql_error());
	header("location: index.php"); exit;
}


?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = ucwords($row_rsLocationPrefs['locationdescriptor'])." Options"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script src="../../../SpryAssets/SpryValidationTextField.js"></script>
<link href="../../../SpryAssets/SpryValidationTextField.css" rel="stylesheet"  />
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
        <div class="page location"><?php require_once('../../../core/region/includes/chooseregion.inc.php'); ?>
    <h1><i class="glyphicon glyphicon-flag"></i> <?php echo ucwords($row_rsLocationPrefs['locationdescriptor']); ?> Options</h1>
    <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
    <li><a href="../googlemaps.php" class="link_manage"><i class="glyphicon glyphicon-cog"></i> Google Maps</a></li>
      <li><a href="../location_to_map.php" class="link_manage"><i class="glyphicon glyphicon-cog"></i> Map Locations</a></li>
      <li><a href="../relationships/index.php" class="link_manage"><i class="glyphicon glyphicon-cog"></i> User Relationships</a></li>
      <li><a href="../index.php" class="link_undo"><i class="glyphicon glyphicon-arrow-left"></i> Manage Locations</a></li>
    </ul></div></nav>
    <form action="<?php echo $editFormAction; ?>" method="POST" name="form1" id="form1">
      <p><label><input <?php if (!(strcmp($row_rsPreferences['uselocationcategory'],1))) {echo "checked=\"checked\"";} ?> name="uselocationcategory" type="checkbox" id="uselocationcategory" value="1" />
          Divide multiple <?php echo ucwords($row_rsLocationPrefs['locationdescriptor']); ?>s into categories</label></p>
      <p>
        <label>
          <input <?php if (!(strcmp($row_rsPreferences['streetview'],1))) {echo "checked=\"checked\"";} ?> name="streetview" type="checkbox" id="streetview" value="1" />
          Enable Google Street View (if maps are enabled)</label>
      </p>
      <p>
        <label>
          <input <?php if (!(strcmp($row_rsLocationPrefs['publicaccess'],1))) {echo "checked=\"checked\"";} ?> name="publicaccess" type="checkbox" id="publicaccess" value="1" />
          Allow public access to view <?php echo ucwords($row_rsLocationPrefs['locationdescriptor']); ?>s marked as 'public'.
        </label>
      </p>
      <p> <label><a href="http://www.postcodeanywhere.co.uk/">Postcode Anywhere</a> key:
<input name="postcodecheckerkey" type="text" id="postcodecheckerkey" value="<?php echo $row_rsLocationPrefs['postcodecheckerkey']; ?>"  size="20" maxlength="20" />
        </label>
      </p>
      <p><span id="sprytextfield1">
        <label>Location descriptor:
          <input name="locationdescriptor" type="text" id="locationdescriptor" value="<?php echo $row_rsLocationPrefs['locationdescriptor']; ?>" size="20" maxlength="20" />
        </label>
      <span class="textfieldRequiredMsg">A value is required.</span></span></p>
      <p>
        <label>
          <input type="submit" name="save" id="save" value="Save" />
        </label>
        <input name="ID" type="hidden" id="ID" value="1" />
      </p>
      <input type="hidden" name="MM_update" value="form1" />
    </form>
   </div>
    <script>
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1");
    </script>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsPreferences);

mysql_free_result($rsLocationPrefs);
?>
