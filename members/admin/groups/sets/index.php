<?php require_once('../../../../Connections/aquiescedb.php'); ?><?php require_once('../../../../core/includes/adminAccess.inc.php'); ?>
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

$MM_restrictGoTo = "/login/index.php?notloggedin=true&alert=".urlencode("You need to be logged in as an Administrator to access this page.");
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

$currentPage = $_SERVER["PHP_SELF"];

if(isset($_GET['deleteID']) && intval($_GET['deleteID'])>0) {
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$delete = "DELETE FROM usergroupset WHERE ID = ".intval($_GET['deleteID']);
	mysql_query($delete, $aquiescedb) or die(mysql_error());
	$delete = "DELETE FROM usergroupsetgroup WHERE groupsetID = ".intval($_GET['deleteID']);
	mysql_query($delete, $aquiescedb) or die(mysql_error());
	header("location: index.php"); exit;
}

$maxRows_rsGroupSets = 20;
$pageNum_rsGroupSets = 0;
if (isset($_GET['pageNum_rsGroupSets'])) {
  $pageNum_rsGroupSets = $_GET['pageNum_rsGroupSets'];
}
$startRow_rsGroupSets = $pageNum_rsGroupSets * $maxRows_rsGroupSets;

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsGroupSets = "SELECT usergroupset.*, COUNT(usergroupsetgroup.ID) AS groups FROM usergroupset LEFT JOIN usergroupsetgroup ON (usergroupset.ID = usergroupsetgroup.groupsetID) GROUP BY usergroupset.ID ORDER BY usergroupset.createddatetime DESC";
$query_limit_rsGroupSets = sprintf("%s LIMIT %d, %d", $query_rsGroupSets, $startRow_rsGroupSets, $maxRows_rsGroupSets);
$rsGroupSets = mysql_query($query_limit_rsGroupSets, $aquiescedb) or die(mysql_error());
$row_rsGroupSets = mysql_fetch_assoc($rsGroupSets);

if (isset($_GET['totalRows_rsGroupSets'])) {
  $totalRows_rsGroupSets = $_GET['totalRows_rsGroupSets'];
} else {
  $all_rsGroupSets = mysql_query($query_rsGroupSets);
  $totalRows_rsGroupSets = mysql_num_rows($all_rsGroupSets);
}
$totalPages_rsGroupSets = ceil($totalRows_rsGroupSets/$maxRows_rsGroupSets)-1;

$queryString_rsGroupSets = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsGroupSets") == false && 
        stristr($param, "totalRows_rsGroupSets") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsGroupSets = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsGroupSets = sprintf("&totalRows_rsGroupSets=%d%s", $totalRows_rsGroupSets, $queryString_rsGroupSets);
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "User Group Sets"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<link href="../../../css/membersDefault.css" rel="stylesheet"  />
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
        <div class="page users">
    <h1><i class="glyphicon glyphicon-user"></i> User Group Sets</h1>
    <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
      <li><a href="add_group_set.php" ><i class="glyphicon glyphicon-plus-sign"></i> Add Set</a></li>
      <li><a href="../index.php" ><i class="glyphicon glyphicon-arrow-left"></i> Manage Groups</a></li>
    </ul></div></nav>
<p>You can create group sets which can be a combination of groups that can be exported as CSV files or used with the Group Email functionality.</p>
<?php if ($totalRows_rsGroupSets == 0) { // Show if recordset empty ?>
  <p>There are currently no sets in  the system.</p>
  <?php } // Show if recordset empty ?>
  <?php if ($totalRows_rsGroupSets > 0) { // Show if recordset not empty ?>
    <p class="text-muted">Sets <?php echo ($startRow_rsGroupSets + 1) ?> to <?php echo min($startRow_rsGroupSets + $maxRows_rsGroupSets, $totalRows_rsGroupSets) ?> of <?php echo $totalRows_rsGroupSets ?></p>
    <table class="table table-hover">
    <thead>
      <tr>
        <th>&nbsp;</th>
        <th>Created</th>
        <th>Name</th>
        <th>Groups</th>
        <th colspan="3">Actions</th>
        </tr></thead><tbody>
      <?php do { ?>
        <tr>
          <th class="status<?php echo $row_rsGroupSets['statusID']; ?>">&nbsp;</th>
          <th><?php echo date('d M Y',strtotime($row_rsGroupSets['createddatetime'])); ?></th>
          <th><?php echo $row_rsGroupSets['groupsetname']; ?></th>
          <th><?php echo $row_rsGroupSets['groups']; ?></th>
          <th><a href="update_group_set.php?groupsetID=<?php echo $row_rsGroupSets['ID']; ?>" class="link_edit icon_only">Edit</a></th>
          <th><a href="group_set_members.php?groupsetID=<?php echo $row_rsGroupSets['ID']; ?>" class="link_view">View</a></th>
          <th><a href="index.php?deleteID=<?php echo $row_rsGroupSets['ID']; ?>" class="link_delete" onClick="return confirm('Are you sure you want to remove this group set?')"><i class="glyphicon glyphicon-trash"></i> Delete</a></th>
        </tr>
        <?php } while ($row_rsGroupSets = mysql_fetch_assoc($rsGroupSets)); ?></tbody>
    </table>
    <?php } // Show if recordset not empty ?>
  <table class="form-table">
    <tr>
      <td><?php if ($pageNum_rsGroupSets > 0) { // Show if not first page ?>
          <a href="<?php printf("%s?pageNum_rsGroupSets=%d%s", $currentPage, 0, $queryString_rsGroupSets); ?>">First</a>
          <?php } // Show if not first page ?></td>
      <td><?php if ($pageNum_rsGroupSets > 0) { // Show if not first page ?>
          <a href="<?php printf("%s?pageNum_rsGroupSets=%d%s", $currentPage, max(0, $pageNum_rsGroupSets - 1), $queryString_rsGroupSets); ?>">Previous</a>
          <?php } // Show if not first page ?></td>
      <td><?php if ($pageNum_rsGroupSets < $totalPages_rsGroupSets) { // Show if not last page ?>
          <a href="<?php printf("%s?pageNum_rsGroupSets=%d%s", $currentPage, min($totalPages_rsGroupSets, $pageNum_rsGroupSets + 1), $queryString_rsGroupSets); ?>">Next</a>
          <?php } // Show if not last page ?></td>
      <td><?php if ($pageNum_rsGroupSets < $totalPages_rsGroupSets) { // Show if not last page ?>
          <a href="<?php printf("%s?pageNum_rsGroupSets=%d%s", $currentPage, $totalPages_rsGroupSets, $queryString_rsGroupSets); ?>">Last</a>
          <?php } // Show if not last page ?></td>
    </tr>
  </table></div>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsGroupSets);
?>
