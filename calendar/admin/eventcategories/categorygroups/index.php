<?php require_once('../../../../Connections/aquiescedb.php'); ?>
<?php require_once('../../../../core/includes/adminAccess.inc.php'); ?>
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

$maxRows_rsEventCategoryGroups = 10;
$pageNum_rsEventCategoryGroups = 0;
if (isset($_GET['pageNum_rsEventCategoryGroups'])) {
  $pageNum_rsEventCategoryGroups = $_GET['pageNum_rsEventCategoryGroups'];
}
$startRow_rsEventCategoryGroups = $pageNum_rsEventCategoryGroups * $maxRows_rsEventCategoryGroups;

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsEventCategoryGroups = "SELECT ID, groupname, statusID FROM eventcategorygroup ORDER BY groupname ASC";
$query_limit_rsEventCategoryGroups = sprintf("%s LIMIT %d, %d", $query_rsEventCategoryGroups, $startRow_rsEventCategoryGroups, $maxRows_rsEventCategoryGroups);
$rsEventCategoryGroups = mysql_query($query_limit_rsEventCategoryGroups, $aquiescedb) or die(mysql_error());
$row_rsEventCategoryGroups = mysql_fetch_assoc($rsEventCategoryGroups);

if (isset($_GET['totalRows_rsEventCategoryGroups'])) {
  $totalRows_rsEventCategoryGroups = $_GET['totalRows_rsEventCategoryGroups'];
} else {
  $all_rsEventCategoryGroups = mysql_query($query_rsEventCategoryGroups);
  $totalRows_rsEventCategoryGroups = mysql_num_rows($all_rsEventCategoryGroups);
}
$totalPages_rsEventCategoryGroups = ceil($totalRows_rsEventCategoryGroups/$maxRows_rsEventCategoryGroups)-1;
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Event Category Groups"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<link href="../../../css/calendarDefault.css" rel="stylesheet"  />
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
        <div class="page calendar">
  <h1><i class="glyphicon glyphicon-calendar"></i> Event Category Groups</h1>
  <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
    <li><a href="add-group.php" ><i class="glyphicon glyphicon-plus-sign"></i> Add Category Group</a></li>
    <li><a href="../index.php" class="link_undo"><i class="glyphicon glyphicon-arrow-left"></i> Back to Categories</a></li>
  </ul></div></nav>
  <?php if ($totalRows_rsEventCategoryGroups == 0) { // Show if recordset empty ?>
  <p>There are no category groups at present.</p>
    <p>You can create groups of categories, e.g. Age Group, Geographical Area, Activity Type, which will then be broken down into categories: 0-5,6-10,11-16, and so on.</p>
    <p>Any existing categories will be added to your first group. However, you may need to move these to new groups as you create them.</p>
    <?php } // Show if recordset empty ?>
  <?php if ($totalRows_rsEventCategoryGroups > 0) { // Show if recordset not empty ?>
    <p class="text-muted">Category groups <?php echo ($startRow_rsEventCategoryGroups + 1) ?> to <?php echo min($startRow_rsEventCategoryGroups + $maxRows_rsEventCategoryGroups, $totalRows_rsEventCategoryGroups) ?> of <?php echo $totalRows_rsEventCategoryGroups ?></p>
    <table class="table table-hover">
    <thead>
      <tr>
        <th>&nbsp;</th>
        <th>Group name</th>
        <th>Edit</th>
      </tr></thead><tbody>
      <?php do { ?>
        <tr>
          <td><?php if($row_rsEventCategoryGroups['statusID']==1) { ?>
            <img src="../../../../core/images/icons/green-light.png" alt="Active" width="16" height="16" style="vertical-align:
middle;" />
            <?php } else { ?>
            <img src="../../../../core/images/icons/red-light.png" alt="Inactive" width="16" height="16" style="vertical-align:
middle;" />
            <?php } ?></td>
          <td><?php echo $row_rsEventCategoryGroups['groupname']; ?></td>
          <td><a href="modify_group.php?groupID=<?php echo $row_rsEventCategoryGroups['ID']; ?>" class="link_edit icon_only">Edit</a></td>
        </tr>
        <?php } while ($row_rsEventCategoryGroups = mysql_fetch_assoc($rsEventCategoryGroups)); ?></tbody>
    </table>
    <?php } // Show if recordset not empty ?></div>
  <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsEventCategoryGroups);
?>
