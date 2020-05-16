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

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsCurrentFeatures = "SELECT bookingfeature.ID, bookingfeature.featurename, bookingcategory.`description` AS category FROM bookingfeature LEFT JOIN bookingcategory ON (bookingfeature.categoryID = bookingcategory.ID) ORDER BY bookingfeature.featurename";
$rsCurrentFeatures = mysql_query($query_rsCurrentFeatures, $aquiescedb) or die(mysql_error());
$row_rsCurrentFeatures = mysql_fetch_assoc($rsCurrentFeatures);
$totalRows_rsCurrentFeatures = mysql_num_rows($rsCurrentFeatures);
?><!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" --><title><?php $pageTitle = "Manage Booking Features"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title><!-- InstanceEndEditable -->
<?php require_once('../../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" --><!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" -->
  <div class="page calendar"><h1>Manage Booking Features   </h1>
   <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
     <li><a href="add.php" ><i class="glyphicon glyphicon-plus-sign"></i> Add a feature</a></li><li><a href="../index.php" class="link_undo"><i class="glyphicon glyphicon-arrow-left"></i> Back to resources</a></li>
     
   </ul></div></nav>
   <p>You can add available featues to optionally add to each booking resource that can be searched by users.</p>
   
   <?php if ($totalRows_rsCurrentFeatures == 0) { // Show if recordset empty ?>
     <p>There are no available features stores so far.</p>
     <?php } // Show if recordset empty ?>
   <?php if ($totalRows_rsCurrentFeatures > 0) { // Show if recordset not empty ?>
  <table border="0" cellpadding="0" cellspacing="0" class="listTable">
    <tr>
      <td><strong>Feature</strong></td>
          <td><strong>Displays in</strong></td>
          <td><strong>Edit</strong></td>
          <td><strong>Delete</strong></td>
    </tr>
    <?php do { ?>
      <tr>
        <td><?php echo $row_rsCurrentFeatures['featurename']; ?></td>
        <td><?php echo (isset($row_rsCurrentFeatures['category']) && $row_rsCurrentFeatures['category']!="") ? $row_rsCurrentFeatures['category'] : "All categories"; ?></td>
        <td><a href="update.php?featureID=<?php echo $row_rsCurrentFeatures['ID']; ?>" class="link_edit icon_only">Edit</a></td>
        <td><a href="delete.php?featureID=<?php echo $row_rsCurrentFeatures['ID']; ?>" title = "Delete this feature" onClick="document.returnValue = confirm('Are you sure you want to delete this feature?\n\nAll references to it in bookable resources will also be deleted.'); return document.returnValue;" class="link_delete" ><i class="glyphicon glyphicon-trash"></i> Delete</a></td>
      </tr>
      <?php } while ($row_rsCurrentFeatures = mysql_fetch_assoc($rsCurrentFeatures)); ?>
  </table>
  <?php } // Show if recordset not empty ?></div>
<!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsCurrentFeatures);
?>


