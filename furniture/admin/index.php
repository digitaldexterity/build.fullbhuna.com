<?php require_once('../../Connections/aquiescedb.php'); ?><?php require_once('../../core/includes/adminAccess.inc.php'); ?>
<?php require_once('../../core/includes/framework.inc.php'); ?>
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

$varRegionID_rsFurniture = "1";
if (isset($regionID)) {
  $varRegionID_rsFurniture = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsFurniture = sprintf("SELECT * FROM furniture WHERE regionID = %s", GetSQLValueString($varRegionID_rsFurniture, "int"));
$rsFurniture = mysql_query($query_rsFurniture, $aquiescedb) or die(mysql_error());
$row_rsFurniture = mysql_fetch_assoc($rsFurniture);
$totalRows_rsFurniture = mysql_num_rows($rsFurniture);
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Manage Furniture"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" -->
            <div class="page furniture"><?php require_once('../../core/region/includes/chooseregion.inc.php'); ?>
  <h1>Manage  Furniture</h1>
  <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
    <li class="nav-item"><a href="add_furniture.php" class="nav-link"><i class="glyphicon glyphicon-plus-sign"></i> Add furniture</a></li>
    <li class="nav-item"><a href="/articles/admin/merge/index.php" class="nav-link"><i class="glyphicon glyphicon-cog"></i> Add-ins</a></li>
  </ul></div></nav>
  <?php if ($totalRows_rsFurniture == 0) { // Show if recordset empty ?>
  <p>There is currently no page furniture.</p>
  <?php } // Show if recordset empty ?>
  <?php if ($totalRows_rsFurniture > 0) { // Show if recordset not empty ?>
    <table class="table table-hover">
    <tbody>
      <?php do { ?>
        <tr>
          <td valign="top" class="status<?php echo $row_rsFurniture['statusID']; ?>">&nbsp;</td>
          <td class="top"><?php if(isset($row_rsFurniture['imageURL'])) { ?>
            <img src="<?php echo getImageURL($row_rsFurniture['imageURL'], "thumb"); ?>" alt="Image" class="thumb" />
            <?php } ?>
            &nbsp;</td>
          <td class="top"><strong><?php echo $row_rsFurniture['furniturename']; ?></strong> <?php echo isset($row_rsFurniture['width_px']) ? " (".$row_rsFurniture['width_px']."px x " : "";  echo isset($row_rsFurniture['height_px']) ? $row_rsFurniture['height_px']."px)" : ""; ?><br />
            <?php echo $row_rsFurniture['furnituretext']; ?> <?php if(isset($row_rsFurniture['furniturelink'])) { ?><br>Link: <a href="<?php echo $row_rsFurniture['furniturelink']; ?>" target="_blank" rel="noopener"><?php echo $row_rsFurniture['furniturelink']; ?><?php echo ($row_rsFurniture['newwindow']==1) ? " (New window)" : ""; ?></a>
            <?php } ?>
            <?php if(isset($row_rsFurniture['appearsonURL'])) { ?><br>Appears on: <a href="<?php echo $row_rsFurniture['appearsonURL']; ?>" target="_blank" rel="noopener"><?php echo $row_rsFurniture['appearsonURL']; ?></a>
            <?php } ?></td>
          <td class="top"><a href="update_furniture.php?furnitureID=<?php echo $row_rsFurniture['ID']; ?>" class="link_edit icon_only">Edit</a></td>
        </tr>
        <?php } while ($row_rsFurniture = mysql_fetch_assoc($rsFurniture)); ?></tbody>
    </table>
    <?php } // Show if recordset not empty ?></div>
  <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsFurniture);
?>
