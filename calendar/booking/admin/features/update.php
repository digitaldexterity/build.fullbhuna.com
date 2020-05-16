<?php require_once('../../../../Connections/aquiescedb.php'); ?>
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

$MM_restrictGoTo = "../../../../login/index.php?notloggedin=true";
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
  $updateSQL = sprintf("UPDATE bookingfeature SET featurename=%s, featuredetails=%s, categoryID=%s WHERE ID=%s",
                       GetSQLValueString($_POST['featurename'], "text"),
                       GetSQLValueString($_POST['featuredetails'], "text"),
                       GetSQLValueString($_POST['categoryID'], "int"),
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

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsCategories = "SELECT ID, `description` FROM bookingcategory ORDER BY `description` ASC";
$rsCategories = mysql_query($query_rsCategories, $aquiescedb) or die(mysql_error());
$row_rsCategories = mysql_fetch_assoc($rsCategories);
$totalRows_rsCategories = mysql_num_rows($rsCategories);

$colname_rsBookingFeature = "-1";
if (isset($_GET['featureID'])) {
  $colname_rsBookingFeature = $_GET['featureID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsBookingFeature = sprintf("SELECT * FROM bookingfeature WHERE ID = %s", GetSQLValueString($colname_rsBookingFeature, "int"));
$rsBookingFeature = mysql_query($query_rsBookingFeature, $aquiescedb) or die(mysql_error());
$row_rsBookingFeature = mysql_fetch_assoc($rsBookingFeature);
$totalRows_rsBookingFeature = mysql_num_rows($rsBookingFeature);
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" --><title><?php $pageTitle = "Update booking feature"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title><!-- InstanceEndEditable -->
<?php require_once('../../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script src="../../../../SpryAssets/SpryValidationTextField.js"></script>
<link href="../../../../SpryAssets/SpryValidationTextField.css" rel="stylesheet"  />
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" --><div class="page calendar">
   <h1>Update Booking Feature</h1>
   <form action="<?php echo $editFormAction; ?>" method="POST" name="form1" id="form1" role="form">
     <table class="form-table"> <tr>
         <td class="text-nowrap text-right">Displays in:</td>
         <td><select name="categoryID">
             <option value="0" <?php if (!(strcmp(0, $row_rsBookingFeature['categoryID']))) {echo "selected=\"selected\"";} ?>>All categories</option>
             <?php
do {  
?><option value="<?php echo $row_rsCategories['ID']?>"<?php if (!(strcmp($row_rsCategories['ID'], $row_rsBookingFeature['categoryID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsCategories['description']?></option>
             <?php
} while ($row_rsCategories = mysql_fetch_assoc($rsCategories));
  $rows = mysql_num_rows($rsCategories);
  if($rows > 0) {
      mysql_data_seek($rsCategories, 0);
	  $row_rsCategories = mysql_fetch_assoc($rsCategories);
  }
?>
                    </select>
           <a href="../categories/index.php">Manage categories</a> </td>
       </tr>
       <tr> </tr> <tr>
         <td class="text-nowrap text-right">Feature:</td>
         <td><span id="sprytextfield1">
           <input name="featurename" type="text"  value="<?php echo $row_rsBookingFeature['featurename']; ?>" size="50" maxlength="50" />
          <span class="textfieldRequiredMsg"><br />
          A value is required.</span></span></td>
       </tr> <tr>
         <td class="text-nowrap text-right top">Details (optional):</td>
         <td><textarea name="featuredetails" cols="50" rows="5"><?php echo $row_rsBookingFeature['featuredetails']; ?></textarea>
         </td>
       </tr> <tr>
         <td class="text-nowrap text-right"><input name="ID" type="hidden" id="ID" value="<?php echo $row_rsBookingFeature['ID']; ?>" /></td>
         <td><button type="submit" class="btn btn-primary" >Save changes</button></td>
       </tr>
     </table>
     
     <input type="hidden" name="MM_update" value="form1" />
</form>
   <p>&nbsp;</p>
   <p>&nbsp;</p>
   <script>
<!--
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1");
//-->
   </script></div>
<!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsCategories);

mysql_free_result($rsBookingFeature);
?>