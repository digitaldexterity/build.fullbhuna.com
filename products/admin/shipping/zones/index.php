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

$maxRows_rsShippingZones = 1000;
$pageNum_rsShippingZones = 0;
if (isset($_GET['pageNum_rsShippingZones'])) {
  $pageNum_rsShippingZones = $_GET['pageNum_rsShippingZones'];
}
$startRow_rsShippingZones = $pageNum_rsShippingZones * $maxRows_rsShippingZones;

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsShippingZones = "SELECT * FROM productshippingzone ORDER BY zonename ASC";
$query_limit_rsShippingZones = sprintf("%s LIMIT %d, %d", $query_rsShippingZones, $startRow_rsShippingZones, $maxRows_rsShippingZones);
$rsShippingZones = mysql_query($query_limit_rsShippingZones, $aquiescedb) or die(mysql_error());
$row_rsShippingZones = mysql_fetch_assoc($rsShippingZones);

if (isset($_GET['totalRows_rsShippingZones'])) {
  $totalRows_rsShippingZones = $_GET['totalRows_rsShippingZones'];
} else {
  $all_rsShippingZones = mysql_query($query_rsShippingZones);
  $totalRows_rsShippingZones = mysql_num_rows($all_rsShippingZones);
}
$totalPages_rsShippingZones = ceil($totalRows_rsShippingZones/$maxRows_rsShippingZones)-1;
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Manage Shipping Zones"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<link href="../../../css/defaultProducts.css" rel="stylesheet"  />
<?php if(isset($body_class)) $body_class .= " products ";  ?>
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" -->
    <h1><i class="glyphicon glyphicon-shopping-cart"></i> Manage Shipping Zones</h1>
    <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
      <li class="nav-item"><a href="add_zone.php" class="nav-link"><i class="glyphicon glyphicon-plus-sign"></i> Add Zone</a></li>
      <li class="nav-item"><a href="../index.php" class="nav-link"><i class="glyphicon glyphicon-arrow-left"></i> Back to Shipping</a></li>
    </ul></div></nav>
    <?php if ($totalRows_rsShippingZones == 0) { // Show if recordset empty ?>
      <p>There are no shipping zones at present</p>
      <?php } // Show if recordset empty ?>
    <?php if ($totalRows_rsShippingZones > 0) { // Show if recordset not empty ?>
  <p class="text-muted">Zones <?php echo ($startRow_rsShippingZones + 1) ?> to <?php echo min($startRow_rsShippingZones + $maxRows_rsShippingZones, $totalRows_rsShippingZones) ?> of <?php echo $totalRows_rsShippingZones ?> </p>
      <table  class="table table-hover">
      <thead>
        <tr>
          <th>&nbsp;</th>
          <th>Zone</th>
          <th>Type</th>
          <th>Postcodes</th>
          <th>Distance</th>
          <th>Edit</th>
        </tr></thead><tbody>
        <?php do { ?>
          <tr>
            <td class="status<?php echo $row_rsShippingZones['statusID']; ?>">&nbsp;</td>
            <td><?php echo $row_rsShippingZones['zonename']; ?></td>
            <td><?php switch($row_rsShippingZones['type']) {
				case 1 : echo "National"; break;
				case 2 : echo "International"; break;
				
			} ?></td>
            <td><?php echo trim(str_replace(":",",",$row_rsShippingZones['bypostcode']),","); ?>&nbsp;</td>
            <td><?php echo isset($row_rsShippingZones['bydistance']) ? $row_rsShippingZones['bydistance']."km" : ""; ?></td>
            <td><a href="update_zone.php?zoneID=<?php echo $row_rsShippingZones['ID']; ?>" class="link_edit icon_only">Edit</a></td>
          </tr>
          <?php } while ($row_rsShippingZones = mysql_fetch_assoc($rsShippingZones)); ?></tbody>
      </table>
      <?php } // Show if recordset not empty ?>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsShippingZones);
?>
