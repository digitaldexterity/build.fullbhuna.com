<?php require_once('../../Connections/aquiescedb.php'); ?><?php require_once('../../core/includes/adminAccess.inc.php'); ?>
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

$MM_restrictGoTo = "../../login/index.php?notloggedin=true";
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

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

// must go after logged in
if (isset($_GET['associate'])) { // plus clicked on, so add to table and return to user
$insert = "INSERT INTO directoryuser (userID, directoryID, createdbyID, createddatetime, relationshiptype) VALUES ('".intval($_GET['userID'])."','".intval($_GET['directoryID'])."','".$row_rsLoggedIn['ID']."',NOW(),1)";
$result = mysql_query($insert, $aquiescedb) or die(mysql_error());
header("location: update_directory.php?directoryID=".intval($_GET['directoryID'])."&defaultTab=4"); exit;
}

$maxRows_rsUsers = 50;
$pageNum_rsUsers = 0;
if (isset($_GET['pageNum_rsUsers'])) {
  $pageNum_rsUsers = $_GET['pageNum_rsUsers'];
}
$startRow_rsUsers = $pageNum_rsUsers * $maxRows_rsUsers;

$varSearch_rsUsers = "%";
if (isset($_GET['search'])) {
  $varSearch_rsUsers = trim($_GET['search']);
}
$varDirectoryID_rsUsers = "-1";
if (isset($_GET['directoryID'])) {
  $varDirectoryID_rsUsers = $_GET['directoryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsUsers = sprintf("SELECT users.ID, firstname, surname FROM users LEFT JOIN directoryuser ON (users.ID = directoryuser.userID) WHERE usertypeID >=1 AND (firstname LIKE %s OR surname LIKE %s OR %s = '') AND (directoryuser.directoryID != %s OR directoryuser.directoryID IS NULL) GROUP BY users.ID ORDER BY surname ASC ", GetSQLValueString("%" . $varSearch_rsUsers . "%", "text"),GetSQLValueString("%" . $varSearch_rsUsers . "%", "text"),GetSQLValueString($varSearch_rsUsers, "text"),GetSQLValueString($varDirectoryID_rsUsers, "int"));
$query_limit_rsUsers = sprintf("%s LIMIT %d, %d", $query_rsUsers, $startRow_rsUsers, $maxRows_rsUsers);
$rsUsers = mysql_query($query_limit_rsUsers, $aquiescedb) or die(mysql_error());
$row_rsUsers = mysql_fetch_assoc($rsUsers);

if (isset($_GET['totalRows_rsUsers'])) {
  $totalRows_rsUsers = $_GET['totalRows_rsUsers'];
} else {
  $all_rsUsers = mysql_query($query_rsUsers);
  $totalRows_rsUsers = mysql_num_rows($all_rsUsers);
}
$totalPages_rsUsers = ceil($totalRows_rsUsers/$maxRows_rsUsers)-1;

$colname_rsOrganisation = "-1";
if (isset($_GET['directoryID'])) {
  $colname_rsOrganisation = $_GET['directoryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsOrganisation = sprintf("SELECT name FROM directory WHERE ID = %s", GetSQLValueString($colname_rsOrganisation, "int"));
$rsOrganisation = mysql_query($query_rsOrganisation, $aquiescedb) or die(mysql_error());
$row_rsOrganisation = mysql_fetch_assoc($rsOrganisation);
$totalRows_rsOrganisation = mysql_num_rows($rsOrganisation);

$queryString_rsUsers = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsUsers") == false && 
        stristr($param, "totalRows_rsUsers") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsUsers = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsUsers = sprintf("&totalRows_rsUsers=%d%s", $totalRows_rsUsers, $queryString_rsUsers);


?><!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" --><title><?php $pageTitle = "Associate User with Organisation"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title><!-- InstanceEndEditable -->
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
        <div class="page directory">
   <h1><i class="glyphicon glyphicon-book"></i> Associate user with <?php echo $row_rsOrganisation['name']; ?></h1>
   
   <?php if ($totalRows_rsUsers == 0) { // Show if recordset empty ?>
     <p>There are no users currently in the database
       <?php if (isset($_GET['search'])) { ?> that match your search criteria<?php } ?>.</p>
     <?php } // Show if recordset empty ?>
     <?php if($totalRows_rsUsers > 0 || isset($_GET['search'])) { ?><form action="" method="get" name="form1" id="form1" class="form-inline">
    Filter users by those names containing:
      <input name="search" type="text"  id="search" value="<?php echo isset($_GET['search']) ? htmlentities(trim($_GET['search'], ENT_COMPAT, "UTF-8")) : ""; ?>" maxlength="20" class="form-control" />
        <input name="userID" type="hidden" id="userID" value="<?php echo intval($_GET['userID']); ?>" />
        <input type="hidden" name="directoryID" id="directoryID"  value="<?php echo intval($_GET['directoryID']); ?>"/>
<button name="go" type="submit" class="btn btn-default btn-secondary" id="go">Search</button>
  </form><?php } ?>
   <?php if ($totalRows_rsUsers > 0) { // Show if recordset not empty ?>
  
  
  <table  class="table table-hover">
<tbody>
        <?php do { ?>
          <tr>
            <td><a href="../../members/admin/modify_user.php?userID=<?php echo $row_rsUsers['ID']; ?>"><?php echo $row_rsUsers['firstname']." ".$row_rsUsers['surname']; ?></a></td>
            <td><a href="associate_user.php?associate=true&amp;directoryID=<?php echo intval($_GET['directoryID']); ?>&amp;userID=<?php echo $row_rsUsers['ID']; ?>" class="btn btn-default btn-secondary"><i class="glyphicon glyphicon-plus-sign"></i> Add User</a></td>
          </tr>
          <?php } while ($row_rsUsers = mysql_fetch_assoc($rsUsers)); ?></tbody>
    </table>
  <?php } // Show if recordset not empty ?>
  <table class="form-table">
    <tr>
      <td><?php if ($pageNum_rsUsers > 0) { // Show if not first page ?>
            <a href="<?php printf("%s?pageNum_rsUsers=%d%s", $currentPage, 0, $queryString_rsUsers); ?>">First</a>
            <?php } // Show if not first page ?>
      </td>
      <td><?php if ($pageNum_rsUsers > 0) { // Show if not first page ?>
            <a href="<?php printf("%s?pageNum_rsUsers=%d%s", $currentPage, max(0, $pageNum_rsUsers - 1), $queryString_rsUsers); ?>" rel="prev">Previous</a>
            <?php } // Show if not first page ?>
      </td>
      <td><?php if ($pageNum_rsUsers < $totalPages_rsUsers) { // Show if not last page ?>
            <a href="<?php printf("%s?pageNum_rsUsers=%d%s", $currentPage, min($totalPages_rsUsers, $pageNum_rsUsers + 1), $queryString_rsUsers); ?>" rel="next">Next</a>
            <?php } // Show if not last page ?>
      </td>
      <td><?php if ($pageNum_rsUsers < $totalPages_rsUsers) { // Show if not last page ?>
            <a href="<?php printf("%s?pageNum_rsUsers=%d%s", $currentPage, $totalPages_rsUsers, $queryString_rsUsers); ?>">Last</a>
            <?php } // Show if not last page ?>
      </td>
    </tr>
  </table></div>
   <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsUsers);

mysql_free_result($rsOrganisation);

mysql_free_result($rsLoggedIn);
?>


