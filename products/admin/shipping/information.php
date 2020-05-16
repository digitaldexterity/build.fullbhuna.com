<?php require_once('../../../Connections/aquiescedb.php'); ?><?php require_once('../../../core/includes/adminAccess.inc.php'); ?>
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

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE productprefs SET shippingnotes=%s, shippinginfoURL=%s, shippinginfonewwindow=%s, returnspolicyURL=%s, deliverytimes1=%s, deliverytimes2=%s, deliverytimes3=%s, text_delivery_time=%s, shippingendofday=%s WHERE ID=%s",
                       GetSQLValueString($_POST['shippingnotes'], "text"),
                       GetSQLValueString($_POST['shippinginfoURL'], "text"),
                       GetSQLValueString(isset($_POST['shippinginfonewwindow']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['returnspolicyURL'], "text"),
                       GetSQLValueString($_POST['deliverytimes1'], "text"),
                       GetSQLValueString($_POST['deliverytimes2'], "text"),
                       GetSQLValueString($_POST['deliverytimes3'], "text"),
                       GetSQLValueString($_POST['text_delivery_time'], "text"),
                       GetSQLValueString($_POST['shippingendofday'], "date"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());

  $updateGoTo = "index.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo));
}

$varRegionID_rsShippingInfo = "1";
if (isset($regionID)) {
  $varRegionID_rsShippingInfo = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsShippingInfo = sprintf("SELECT * FROM productprefs WHERE ID = %s", GetSQLValueString($varRegionID_rsShippingInfo, "int"));
$rsShippingInfo = mysql_query($query_rsShippingInfo, $aquiescedb) or die(mysql_error());
$row_rsShippingInfo = mysql_fetch_assoc($rsShippingInfo);
$totalRows_rsShippingInfo = mysql_num_rows($rsShippingInfo);
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php echo $site_name; ?><?php echo $admin_name; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<?php require_once('../../../core/tinymce/tinymce.inc.php'); ?>
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
    <?php require_once('../../../core/region/includes/chooseregion.inc.php'); ?>
<h1><i class="glyphicon glyphicon-shopping-cart"></i> Shipping Information</h1>
    <form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1">
  <table class="form-table"><tr>
      <td><label for="shippingnotes">Shipping notes (at foot of Customer Details page):</label></td>
    </tr>
    <tr>
      <td>
        <input name="shippingnotes" type="text" id="shippingnotes" value="<?php echo $row_rsShippingInfo['shippingnotes']; ?>" size="100"class="form-control" ></td>
    </tr>
   
    
    <tr>
      <td>Alternative shipping information page URL:</td>
    </tr>
    <tr>
      <td><input name="shippinginfoURL" type="text" id="shippinginfoURL" value="<?php echo $row_rsShippingInfo['shippinginfoURL']; ?>" size="100" maxlength="255" class="form-control"> <label><input <?php if (!(strcmp($row_rsShippingInfo['shippinginfonewwindow'],1))) {echo "checked=\"checked\"";} ?> name="shippinginfonewwindow" type="checkbox" id="shippinginfonewwindow" value="1"> Open in new window</label></td>
    </tr>
    <tr>
      <td><label for="returnspolicyURL">Returns Policy URL:</label></td>
    </tr> <tr>
      <td> 
          <input name="returnspolicyURL" id="returnspolicyURL" value="<?php echo htmlentities($row_rsShippingInfo['returnspolicyURL'], ENT_COMPAT, 'UTF-8');  ?>"  size="100" maxlength="255" class="form-control"></td>
    </tr> <tr>
      <td><h2>Shipping times</h2>
        <p class="form-inline">End of shipping day: 
          <input name="shippingendofday" type="text" id="shippingendofday" value="<?php echo $row_rsShippingInfo['shippingendofday']; ?>" size="10" maxlength="8" class="form-control">
        </p>
      <p>Shipping information at brand level (how long shipping will take) can be set in <a href="../products/categories/manufacturer/index.php">Manage Manufacturers</a></td>
    </tr>
    <tr>
      <td><h2>Delivery Times</h2>
        <p>You can give the customer options of up to 3 delivery times during the day. Enter below:</p>
        <table class="form-table"><tr>
         <td> <label for="text_delivery_time">Descriptor:</label></td><td>
          <input name="text_delivery_time" type="text" id="text_delivery_time" value="<?php echo $row_rsShippingInfo['text_delivery_time']; ?>" size="50" maxlength="255" class="form-control"></td></tr><tr><td>
          <label for="deliverytimes1">Option 1:</label></td><td>
          <input name="deliverytimes1" type="text" id="deliverytimes1" value="<?php echo $row_rsShippingInfo['deliverytimes1']; ?>" size="50" maxlength="50" placeholder="(optional)" class="form-control"></td></tr><tr><td>
          <label for="deliverytimes2">Option 2:</label></td><td>
          <input name="deliverytimes2" type="text" id="deliverytimes2" value="<?php echo $row_rsShippingInfo['deliverytimes2']; ?>" size="50" maxlength="50" placeholder="(optional)" class="form-control"></td></tr><tr><td>
          <label for="deliverytimes3">Option 3:</label></td><td>
          <input name="deliverytimes3" type="text" id="deliverytimes3" value="<?php echo $row_rsShippingInfo['deliverytimes3']; ?>" size="50" maxlength="50" placeholder="(optional)" class="form-control"></td></tr></table>
        </p></td>
    </tr>
    <tr>
      <td><button type="submit" class="btn btn-primary">Save changes</button></td>
    </tr>
  </table>
  <input type="hidden" name="MM_update" value="form1" />
  <input type="hidden" name="ID" value="<?php echo $row_rsShippingInfo['ID']; ?>" />
</form>

  <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsShippingInfo);
?>
