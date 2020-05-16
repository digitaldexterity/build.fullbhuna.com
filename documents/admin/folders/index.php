<?php require_once('../../../Connections/aquiescedb.php'); ?>
<?php require_once('../../../core/includes/adminAccess.inc.php'); ?>
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

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID, users.usertypeID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

$maxRows_rsFolders = 100;
$pageNum_rsFolders = 0;
if (isset($_GET['pageNum_rsFolders'])) {
  $pageNum_rsFolders = $_GET['pageNum_rsFolders'];
}
$startRow_rsFolders = $pageNum_rsFolders * $maxRows_rsFolders;

$varLoggedIn_rsFolders = "-1";
if (isset($row_rsLoggedIn['ID'])) {
  $varLoggedIn_rsFolders = $row_rsLoggedIn['ID'];
}
$varUserType_rsFolders = "0";
if (isset($row_rsLoggedIn['usertypeID'])) {
  $varUserType_rsFolders = $row_rsLoggedIn['usertypeID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsFolders = sprintf("SELECT documentcategory.*, documentcategory.ID AS catID, usergroup.groupname AS readgroup, usertype.name AS readrank, writegroup.groupname AS writegroupname, writeuser.name AS writerank, region.title AS regionname, parentcat.categoryname AS parentname, (SELECT COUNT(DISTINCT(documents.ID)) FROM documents LEFT JOIN documentincategory ON (documents.ID = documentincategory.documentID) WHERE documents.documentcategoryID = catID OR documentincategory .categoryID = catID) AS numdocs FROM documentcategory LEFT JOIN usergroupmember ON (documentcategory.groupreadID  = usergroupmember.groupID) LEFT JOIN usergroup ON (documentcategory.groupreadID = usergroup.ID) LEFT JOIN usertype ON (documentcategory.accessID = usertype.ID) LEFT JOIN usertype AS writeuser ON (documentcategory.writeaccess = writeuser.ID)  LEFT JOIN region ON (documentcategory.regionID = region.ID) LEFT JOIN documentcategory AS parentcat ON (documentcategory.subcatofID = parentcat.ID) LEFT JOIN usergroup AS writegroup ON (documentcategory.groupwriteID  = writegroup.ID)  WHERE (%s = 10 OR documentcategory.groupreadID = 0 OR usergroupmember.userID = %s) AND (%s = 10 OR documentcategory.accessID = 0 OR documentcategory.accessID  IS NULL  OR documentcategory.accessID<=%s) GROUP BY documentcategory.ID ORDER BY regionID ASC", GetSQLValueString($varUserType_rsFolders, "int"),GetSQLValueString($varLoggedIn_rsFolders, "int"),GetSQLValueString($varUserType_rsFolders, "int"),GetSQLValueString($varUserType_rsFolders, "int"));
$query_limit_rsFolders = sprintf("%s LIMIT %d, %d", $query_rsFolders, $startRow_rsFolders, $maxRows_rsFolders);
$rsFolders = mysql_query($query_limit_rsFolders, $aquiescedb) or die(mysql_error());
$row_rsFolders = mysql_fetch_assoc($rsFolders);

if (isset($_GET['totalRows_rsFolders'])) {
  $totalRows_rsFolders = $_GET['totalRows_rsFolders'];
} else {
  $all_rsFolders = mysql_query($query_rsFolders);
  $totalRows_rsFolders = mysql_num_rows($all_rsFolders);
}
$totalPages_rsFolders = ceil($totalRows_rsFolders/$maxRows_rsFolders)-1;

$queryString_rsFolders = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsFolders") == false && 
        stristr($param, "totalRows_rsFolders") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsFolders = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsFolders = sprintf("&totalRows_rsFolders=%d%s", $totalRows_rsFolders, $queryString_rsFolders);
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Manage Folders"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<style>
<!--
-->
</style>
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
      <div class="page class">
        <h1><i class="glyphicon glyphicon-folder-open"></i> Manage Folders</h1>
        <p class="text-muted">Folders <?php echo ($startRow_rsFolders + 1) ?> to <?php echo min($startRow_rsFolders + $maxRows_rsFolders, $totalRows_rsFolders) ?> of <?php echo $totalRows_rsFolders ?> (Note - only folders that you can access are visible)</p>
        <table class="table table-hover">
        <thead>
          <tr>
            <th>&nbsp;</th>
            <th>Folder name</th>
            <th>Parent folder</th>
            <th>View Rank</th>
            <th>Edit Rank</th>
            <th>View Group</th>
            <th>Edit Group</th>
            <th>Site</th>
           
            <th>Edit</th>
             <th colspan="2">View</th>
          </tr></thead><tbody>
          <?php do { ?>
            <tr>
              <td class="status<?php echo $row_rsFolders['active']; ?>">&nbsp;</td>
              <td><?php echo $row_rsFolders['categoryname']; ?></td>
              <td><em><?php echo $row_rsFolders['parentname']; ?></em></td>
              <td><?php echo ($row_rsFolders['accessID']>0) ? $row_rsFolders['readrank'] : "Everyone"; ?></td>
              <td><?php echo ($row_rsFolders['writeaccess']>0) ? $row_rsFolders['writerank'] : "Everyone"; ?></td>
              <td><?php echo $row_rsFolders['readgroup']; ?></td>
              <td><?php echo $row_rsFolders['writegroupname']; ?></td>
              <td><?php echo $row_rsFolders['regionname']; ?></td>
             
              <td><?php if($row_rsFolders['writeaccess']<=$row_rsLoggedIn['usertypeID']) { ?><a href="update_folder.php?categoryID=<?php echo $row_rsFolders['ID']; ?>&token=" class="link_edit icon_only">Edit</a><?php } ?></td> <td><?php echo $row_rsFolders['numdocs']; ?></td>
              <td><a href="/documents/index.php?categoryID=<?php echo $row_rsFolders['ID']; ?>" target="_blank" class="link_view" rel="noopener">View</a></td>
            </tr>
            <?php } while ($row_rsFolders = mysql_fetch_assoc($rsFolders)); ?></tbody>
        </table>
        <table class="form-table">
          <tr>
            <td><?php if ($pageNum_rsFolders > 0) { // Show if not first page ?>
                <a href="<?php printf("%s?pageNum_rsFolders=%d%s", $currentPage, 0, $queryString_rsFolders); ?>">First</a>
                <?php } // Show if not first page ?></td>
            <td><?php if ($pageNum_rsFolders > 0) { // Show if not first page ?>
                <a href="<?php printf("%s?pageNum_rsFolders=%d%s", $currentPage, max(0, $pageNum_rsFolders - 1), $queryString_rsFolders); ?>">Previous</a>
                <?php } // Show if not first page ?></td>
            <td><?php if ($pageNum_rsFolders < $totalPages_rsFolders) { // Show if not last page ?>
                <a href="<?php printf("%s?pageNum_rsFolders=%d%s", $currentPage, min($totalPages_rsFolders, $pageNum_rsFolders + 1), $queryString_rsFolders); ?>">Next</a>
                <?php } // Show if not last page ?></td>
            <td><?php if ($pageNum_rsFolders < $totalPages_rsFolders) { // Show if not last page ?>
                <a href="<?php printf("%s?pageNum_rsFolders=%d%s", $currentPage, $totalPages_rsFolders, $queryString_rsFolders); ?>">Last</a>
                <?php } // Show if not last page ?></td>
          </tr>
        </table>
      </div>
      <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsFolders);
?>
