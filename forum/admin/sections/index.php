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
  $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;

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
      $theValue = ($theValue != "") ? "'" . doubleval($theValue) . "'" : "NULL";
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

$maxRows_rsForumSections = 10;
$pageNum_rsForumSections = 0;
if (isset($_GET['pageNum_rsForumSections'])) {
  $pageNum_rsForumSections = $_GET['pageNum_rsForumSections'];
}
$startRow_rsForumSections = $pageNum_rsForumSections * $maxRows_rsForumSections;

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsForumSections = "SELECT forumsection.ID, forumsection.sectionname, forumsection.accesslevel, forumsection.regionID, forumsection.statusID, usertype.name AS accesslevelname, region.title AS regionname FROM forumsection LEFT JOIN usertype ON (forumsection.accesslevel = usertype.ID)  LEFT JOIN region ON (forumsection.regionID = region.ID) ORDER BY sectionname ASC";
$query_limit_rsForumSections = sprintf("%s LIMIT %d, %d", $query_rsForumSections, $startRow_rsForumSections, $maxRows_rsForumSections);
$rsForumSections = mysql_query($query_limit_rsForumSections, $aquiescedb) or die(mysql_error());
$row_rsForumSections = mysql_fetch_assoc($rsForumSections);

if (isset($_GET['totalRows_rsForumSections'])) {
  $totalRows_rsForumSections = $_GET['totalRows_rsForumSections'];
} else {
  $all_rsForumSections = mysql_query($query_rsForumSections);
  $totalRows_rsForumSections = mysql_num_rows($all_rsForumSections);
}
$totalPages_rsForumSections = ceil($totalRows_rsForumSections/$maxRows_rsForumSections)-1;

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = "SELECT useregions FROM preferences";
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);
?><!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" --><title><?php $pageTitle = "Manage Forum Sections"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title><!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" --><?php if($row_rsPreferences['useregions'] !=1) { ?>
<style>
.region {
display:none;
} </style><?php } ?>
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" --><div class="page forum">
   <h1><i class="glyphicon glyphicon-comment"></i> Manage Forum Sections</h1>
   
   <?php if ($totalRows_rsForumSections == 0) { // Show if recordset empty ?>
     <p>There are no sections available.</p>
     <p>If any topics currently exist they will be added to the first section you add. They can be moved to another section later, if required.</p>
     <?php } // Show if recordset empty ?>
<nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
     <li class="nav-item"><a href="add_section.php" class="nav-link"><i class="glyphicon glyphicon-plus-sign"></i> Add section</a></li>
     <li class="nav-item"><a href="../index.php" class="nav-link"><i class="glyphicon glyphicon-arrow-left"></i> Back to topics</a></li>
   </ul></div></nav>
   
   
<?php if ($totalRows_rsForumSections > 0) { // Show if recordset not empty ?>
  <table class="table table-hover">
  <thead>
        <tr>
          <th>&nbsp;</th>
          <th>Section</th>
          <th>Access</th>
          <th  class="region">Site</th>
          <th>Edit</th>
        </tr></thead><tbody>
        <?php do { ?>
          <tr>
            <td><?php if($row_rsForumSections['statusID'] == 1) { ?>
              <img src="../../../core/images/icons/green-light.png" alt="Active" width="16" height="16" style="vertical-align:
middle;" />
              <?php } else { ?>
            <img src="../../../core/images/icons/red-light.png" alt="Inactive" width="16" height="16" style="vertical-align:
middle;" />              <?php } ?></td>
            <td><?php echo $row_rsForumSections['sectionname']; ?></td>
            <td><?php echo ($row_rsForumSections['accesslevel'] < 1) ? "Everyone" : $row_rsForumSections['accesslevelname']."s"; ?></td>
            <td class="region"><?php echo $row_rsForumSections['regionname']; ?></td>
            <td><a href="update_section.php?sectionID=<?php echo $row_rsForumSections['ID']; ?>" class="link_edit icon_only">Edit</a></td>
          </tr>
          <?php } while ($row_rsForumSections = mysql_fetch_assoc($rsForumSections)); ?></tbody>
     </table>
  <?php } // Show if recordset not empty ?></div>
<!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsForumSections);

mysql_free_result($rsPreferences);
?>


