<?php require_once('../../../Connections/aquiescedb.php'); ?><?php require_once('../../../core/includes/adminAccess.inc.php'); ?><?php
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
?><?php
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

$currentPage = $_SERVER["PHP_SELF"];

$maxRows_rsCategories = 50;
$pageNum_rsCategories = 0;
if (isset($_GET['pageNum_rsCategories'])) {
  $pageNum_rsCategories = $_GET['pageNum_rsCategories'];
}
$startRow_rsCategories = $pageNum_rsCategories * $maxRows_rsCategories;

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsCategories = "SELECT eventcategory.* FROM eventcategory ORDER BY title ASC";
$query_limit_rsCategories = sprintf("%s LIMIT %d, %d", $query_rsCategories, $startRow_rsCategories, $maxRows_rsCategories);
$rsCategories = mysql_query($query_limit_rsCategories, $aquiescedb) or die(mysql_error());
$row_rsCategories = mysql_fetch_assoc($rsCategories);

if (isset($_GET['totalRows_rsCategories'])) {
  $totalRows_rsCategories = $_GET['totalRows_rsCategories'];
} else {
  $all_rsCategories = mysql_query($query_rsCategories);
  $totalRows_rsCategories = mysql_num_rows($all_rsCategories);
}
$totalPages_rsCategories = ceil($totalRows_rsCategories/$maxRows_rsCategories)-1;

$queryString_rsCategories = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsCategories") == false && 
        stristr($param, "totalRows_rsCategories") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsCategories = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsCategories = sprintf("&totalRows_rsCategories=%d%s", $totalRows_rsCategories, $queryString_rsCategories);
?><!doctype html>

<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Diary Categories"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->

<link href="../../css/calendarDefault.css" rel="stylesheet"  />
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
      <div class="page calendar"><h1><i class="glyphicon glyphicon-calendar"></i> Manage Diary Categories</h1>
      
        <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav"><li><a href="add_event_category.php" ><i class="glyphicon glyphicon-plus-sign"></i> Add Event Category</a></li>
          <li><a href="categorygroups/index.php" class="link_manage"><i class="glyphicon glyphicon-cog"></i> Manage Category Groups</a></li>
          <li><a href="../index.php" class="link_undo"><i class="glyphicon glyphicon-arrow-left"></i> Diary</a></li>
    </ul></div></nav>
     
  <?php if ($totalRows_rsCategories == 0) { // Show if recordset empty ?>
        <p>There are no categories in the database</p>
        <?php } // Show if recordset empty ?>

        <?php if ($totalRows_rsCategories > 0) { // Show if recordset not empty ?>
        <p class="text-muted">Categories <?php echo ($startRow_rsCategories + 1) ?> to <?php echo min($startRow_rsCategories + $maxRows_rsCategories, $totalRows_rsCategories) ?> of <?php echo $totalRows_rsCategories ?></p>
        <table class="table table-hover">
        <thead>
<tr>
                <th>&nbsp;</th>
                <th>Name</th>
                <th>Priority</th>
                <th>Edit</th>
              </tr></thead><tbody>
          <?php do { ?>
              
              <tr>
              <td><?php if($row_rsCategories['active'] ==1) { ?>
                <img src="../../../core/images/icons/green-light.png" alt="Active" width="16" height="16" />
                <?php } else { ?>
              <img src="../../../core/images/icons/red-light.png" alt="Inactive" width="16" height="16" />                <?php } ?></td>
              <td bgcolor="<?php echo isset($row_rsCategories['colour']) ? $row_rsCategories['colour'] : ''; ?>"><?php echo $row_rsCategories['title']; ?></td>
              <td><?php echo $row_rsCategories['priority']; ?></td>
              <td><a href="update_event_category.php?categoryID=<?php echo $row_rsCategories['ID']; ?>" class="link_edit icon_only">Edit</a></td>
            </tr>
            <?php } while ($row_rsCategories = mysql_fetch_assoc($rsCategories)); ?></tbody>
        </table>
    <?php } // Show if recordset not empty ?>
    <table class="form-table">
      <tr>
        <td><?php if ($pageNum_rsCategories > 0) { // Show if not first page ?>
            <a href="<?php printf("%s?pageNum_rsCategories=%d%s", $currentPage, 0, $queryString_rsCategories); ?>">First</a>
            <?php } // Show if not first page ?></td>
        <td><?php if ($pageNum_rsCategories > 0) { // Show if not first page ?>
            <a href="<?php printf("%s?pageNum_rsCategories=%d%s", $currentPage, max(0, $pageNum_rsCategories - 1), $queryString_rsCategories); ?>">Previous</a>
            <?php } // Show if not first page ?></td>
        <td><?php if ($pageNum_rsCategories < $totalPages_rsCategories) { // Show if not last page ?>
            <a href="<?php printf("%s?pageNum_rsCategories=%d%s", $currentPage, min($totalPages_rsCategories, $pageNum_rsCategories + 1), $queryString_rsCategories); ?>">Next</a>
            <?php } // Show if not last page ?></td>
        <td><?php if ($pageNum_rsCategories < $totalPages_rsCategories) { // Show if not last page ?>
            <a href="<?php printf("%s?pageNum_rsCategories=%d%s", $currentPage, $totalPages_rsCategories, $queryString_rsCategories); ?>">Last</a>
            <?php } // Show if not last page ?></td>
      </tr>
    </table></div>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsCategories);
?>