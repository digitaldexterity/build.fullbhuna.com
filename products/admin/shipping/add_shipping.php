<?php require_once('../../../Connections/aquiescedb.php'); ?>
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
  $insertSQL = sprintf("INSERT INTO productshipping (shippingname, shippingrate, minweight, maxweight, regionID, createdbyID, createddatetime, ratemultiple, ratemultipleamount, shippingzoneID, hazardous, express, promotion) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['shippingname'], "text"),
                       GetSQLValueString($_POST['shippingrate'], "double"),
                       GetSQLValueString($_POST['minweight'], "double"),
                       GetSQLValueString($_POST['maxweight'], "double"),
                       GetSQLValueString($_POST['regionID'], "int"),
                       GetSQLValueString($_POST['createdbyID'], "int"),
                       GetSQLValueString($_POST['createddatetime'], "date"),
                       GetSQLValueString($_POST['ratemultiple'], "int"),
                       GetSQLValueString($_POST['ratemultipleamount'], "int"),
                       GetSQLValueString($_POST['shippingzoneID'], "int"),
                       GetSQLValueString(isset($_POST['hazardous']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['express']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['promotion']) ? "true" : "", "defined","1","0"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($insertSQL, $aquiescedb) or die(mysql_error());
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
  $insertGoTo = "index.php";
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
$query_rsLoggedIn = sprintf("SELECT ID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsRegions = "SELECT * FROM region WHERE statusID = 1 ORDER BY title ASC";
$rsRegions = mysql_query($query_rsRegions, $aquiescedb) or die(mysql_error());
$row_rsRegions = mysql_fetch_assoc($rsRegions);
$totalRows_rsRegions = mysql_num_rows($rsRegions);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsShippingZones = "SELECT ID, zonename FROM productshippingzone WHERE statusID = 1 ORDER BY zonename ASC";
$rsShippingZones = mysql_query($query_rsShippingZones, $aquiescedb) or die(mysql_error());
$row_rsShippingZones = mysql_fetch_assoc($rsShippingZones);
$totalRows_rsShippingZones = mysql_num_rows($rsShippingZones);

$colname_rsBasedOnShipping = "-1";
if (isset($_GET['basedonshippingID'])) {
  $colname_rsBasedOnShipping = $_GET['basedonshippingID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsBasedOnShipping = sprintf("SELECT * FROM productshipping WHERE ID = %s", GetSQLValueString($colname_rsBasedOnShipping, "int"));
$rsBasedOnShipping = mysql_query($query_rsBasedOnShipping, $aquiescedb) or die(mysql_error());
$row_rsBasedOnShipping = mysql_fetch_assoc($rsBasedOnShipping);
$totalRows_rsBasedOnShipping = mysql_num_rows($rsBasedOnShipping);
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Add Shipping Rate"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script src="../../../SpryAssets/SpryValidationTextField.js"></script>
<link href="../../../SpryAssets/SpryValidationTextField.css" rel="stylesheet"  />
<link href="../../css/defaultProducts.css" rel="stylesheet"  />
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
    <!-- InstanceBeginEditable name="Body" -->
    <h1><i class="glyphicon glyphicon-shopping-cart"></i> Add Shipping Rate</h1>
    <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
      <li><a href="index.php" class="link_undo"><i class="glyphicon glyphicon-arrow-left"></i> Shipping rates</a></li>
    </ul></div></nav>
    <form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1">
      <table class="form-table"> <tr>
          <td class="text-nowrap text-right">Shipping name:</td>
          <td><span id="sprytextfield1">
            <input name="shippingname" type="text"  value="<?php echo $row_rsBasedOnShipping['shippingname']; ?>" size="50" maxlength="50" class="form-control" />
            <span class="textfieldRequiredMsg">A value is required.</span></span></td>
        </tr> <tr>
          <td class="text-nowrap text-right">Shipping rate:</td>
          <td class="form-inline"><span id="sprytextfield2">
          <input name="shippingrate" type="text"  value="<?php echo $row_rsBasedOnShipping['shippingrate']; ?>" size="10" maxlength="10"class="form-control" />
          <span class="textfieldRequiredMsg">A value is required.</span><span class="textfieldInvalidFormatMsg">Invalid format.</span></span>
            
              <label>
                <input <?php if ($row_rsBasedOnShipping['ratemultiple']==0 || $row_rsBasedOnShipping['ratemultiple']==1 || !isset($row_rsBasedOnShipping['ratemultiple'])) {echo "checked=\"checked\"";} ?> name="ratemultiple" type="radio" id="ratemultiple_0" value="0"  />
                Flat rate</label>
             &nbsp;&nbsp;&nbsp;
             
              <label>
                <input <?php if (!(strcmp($row_rsBasedOnShipping['ratemultiple'],"2"))) {echo "checked=\"checked\"";} ?> type="radio" name="ratemultiple" value="2" id="ratemultiple_2" />
                Per</label>
  <span id="sprytextfield5"><input name="ratemultipleamount" type="text"  id="ratemultipleamount" size="4" maxlength="4"  value="<?php echo isset($row_rsBasedOnShipping['ratemultipleamount']) ? htmlentities($row_rsBasedOnShipping['ratemultipleamount'], ENT_COMPAT, "UTF-8") : 1; ?>"/>
                kg
            <span class="textfieldRequiredMsg">A value is required.</span><span class="textfieldInvalidFormatMsg">Invalid format.</span><span class="textfieldMinValueMsg">The entered value is less than the minimum required.</span></span></td>
        </tr> <tr>
          <td class="text-nowrap text-right">Applies to weights</td>
          <td class="form-inline"><span id="sprytextfield3">
            <input name="minweight" type="text"  value="<?php echo $row_rsBasedOnShipping['minweight']; ?>" size="10" maxlength="10" class="form-control"/>
            <span class="textfieldInvalidFormatMsg">Invalid format.</span></span> 
            kg to 
            <span id="sprytextfield4">
            <input name="maxweight" type="text"  value="<?php echo $row_rsBasedOnShipping['maxweight']; ?>" size="10" maxlength="10" class="form-control"/>
<span class="textfieldInvalidFormatMsg">Invalid format.</span></span> kg. (Optional)</td>
        </tr>
        <tr>
          <td class="text-nowrap text-right">Site:</td>
          <td><select name="regionID" class="form-control">
            <?php
do {  
?>
            <option value="<?php echo $row_rsRegions['ID']?>"<?php if (!(strcmp($row_rsRegions['ID'], $row_rsBasedOnShipping['regionID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsRegions['title']?></option>
            <?php
} while ($row_rsRegions = mysql_fetch_assoc($rsRegions));
  $rows = mysql_num_rows($rsRegions);
  if($rows > 0) {
      mysql_data_seek($rsRegions, 0);
	  $row_rsRegions = mysql_fetch_assoc($rsRegions);
  }
?>
          </select></td>
        </tr> <tr>
          <td class="text-nowrap text-right">Zone:</td>
          <td><select name="shippingzoneID" id="shippingzoneID" class="form-control">
            <option value="0">All zones</option>
            <?php
do {  
?>
<option value="<?php echo $row_rsShippingZones['ID']?>"<?php if (isset($regionID) && $row_rsShippingZones['ID']==$regionID) {echo "selected=\"selected\"";} ?>><?php echo $row_rsShippingZones['zonename']?></option>
            <?php
} while ($row_rsShippingZones = mysql_fetch_assoc($rsShippingZones));
  $rows = mysql_num_rows($rsShippingZones);
  if($rows > 0) {
      mysql_data_seek($rsShippingZones, 0);
	  $row_rsShippingZones = mysql_fetch_assoc($rsShippingZones);
  }
?>
          </select></td>
        </tr> <tr>
          <td class="text-nowrap text-right">Promotion included:</td>
          <td><input type="checkbox" name="promotion" id="promotion" /></td>
        </tr> <tr>
          <td class="text-nowrap text-right">Express:</td>
          <td><input <?php if (!(strcmp($row_rsBasedOnShipping['express'],1))) {echo "checked=\"checked\"";} ?> name="express" type="checkbox" id="express" value="1" />
         </td>
        </tr> <tr>
          <td class="text-nowrap text-right">Hazardous:</td>
          <td><input <?php if (!(strcmp($row_rsBasedOnShipping['hazardous'],1))) {echo "checked=\"checked\"";} ?> name="hazardous" type="checkbox" id="hazardous" value="1" />
          </td>
        </tr> <tr>
          <td class="text-nowrap text-right">&nbsp;</td>
          <td><button type="submit" class="btn btn-primary" >Add shipping rate</button></td>
        </tr>
      </table>
     <input name="createdbyID" type="hidden" id="createdbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
      <input name="createddatetime" type="hidden" id="createddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
      <input type="hidden" name="MM_insert" value="form1" />
    *The local rates will only be allowed on deliveries nationally.
    </form>
    <p>&nbsp;</p>
    <script>
<!--
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1");
var sprytextfield2 = new Spry.Widget.ValidationTextField("sprytextfield2", "currency");
var sprytextfield3 = new Spry.Widget.ValidationTextField("sprytextfield3", "real", {isRequired:false});
var sprytextfield4 = new Spry.Widget.ValidationTextField("sprytextfield4", "real", {isRequired:false});
var sprytextfield5 = new Spry.Widget.ValidationTextField("sprytextfield5", "integer", {minValue:1});
//-->
    </script>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsRegions);

mysql_free_result($rsShippingZones);

mysql_free_result($rsBasedOnShipping);
?>
