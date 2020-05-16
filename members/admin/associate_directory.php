<?php require_once('../../Connections/aquiescedb.php'); ?><?php require_once('../includes/userfunctions.inc.php'); ?>
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
	addUserToDirectory($_GET['userID'], $_GET['directoryID'], $row_rsLoggedIn['ID']);
	header("location: modify_user.php?userID=".intval($_GET['userID'])."&defaultTab=3"); exit;
}

$maxRows_rsDirectory = 50;
$pageNum_rsDirectory = 0;
if (isset($_GET['pageNum_rsDirectory'])) {
  $pageNum_rsDirectory = $_GET['pageNum_rsDirectory'];
}
$startRow_rsDirectory = $pageNum_rsDirectory * $maxRows_rsDirectory;

$varSearch_rsDirectory = "%";
if (isset($_GET['search'])) {
  $varSearch_rsDirectory = trim($_GET['search']);
}
$varUserID_rsDirectory = "-1";
if (isset($_GET['userID'])) {
  $varUserID_rsDirectory = $_GET['userID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsDirectory = sprintf("SELECT directory.ID, name FROM directory LEFT JOIN directoryuser ON (directory.ID = directoryuser.directoryID) WHERE statusID = 1 AND (name LIKE %s OR %s = '') AND (directoryuser.userID != %s OR directoryuser.userID IS NULL) GROUP BY directory.ID ORDER BY name ASC", GetSQLValueString("%" . $varSearch_rsDirectory . "%", "text"),GetSQLValueString($varSearch_rsDirectory, "text"),GetSQLValueString($varUserID_rsDirectory, "int"));
$query_limit_rsDirectory = sprintf("%s LIMIT %d, %d", $query_rsDirectory, $startRow_rsDirectory, $maxRows_rsDirectory);
$rsDirectory = mysql_query($query_limit_rsDirectory, $aquiescedb) or die(mysql_error());
$row_rsDirectory = mysql_fetch_assoc($rsDirectory);

if (isset($_GET['totalRows_rsDirectory'])) {
  $totalRows_rsDirectory = $_GET['totalRows_rsDirectory'];
} else {
  $all_rsDirectory = mysql_query($query_rsDirectory);
  $totalRows_rsDirectory = mysql_num_rows($all_rsDirectory);
}
$totalPages_rsDirectory = ceil($totalRows_rsDirectory/$maxRows_rsDirectory)-1;

$colname_rsUser = "-1";
if (isset($_GET['userID'])) {
  $colname_rsUser = $_GET['userID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsUser = sprintf("SELECT firstname, surname FROM users WHERE ID = %s", GetSQLValueString($colname_rsUser, "int"));
$rsUser = mysql_query($query_rsUser, $aquiescedb) or die(mysql_error());
$row_rsUser = mysql_fetch_assoc($rsUser);
$totalRows_rsUser = mysql_num_rows($rsUser);

$queryString_rsDirectory = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsDirectory") == false && 
        stristr($param, "totalRows_rsDirectory") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsDirectory = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsDirectory = sprintf("&totalRows_rsDirectory=%d%s", $totalRows_rsDirectory, $queryString_rsDirectory);


?><!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" --><title><?php $pageTitle = "Associate User with Organisation"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title><!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<link href="../css/membersDefault.css" rel="stylesheet"  />
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
        <div class="page users">
   <h1><i class="glyphicon glyphicon-user"></i> Associate <?php echo $row_rsUser['firstname']." ".$row_rsUser['surname']; ?> with Organisation </h1>
   
   <?php if ($totalRows_rsDirectory == 0) { // Show if recordset empty ?>
     <p>There are no organisation currently in the database<?php if (isset($_GET['search'])) { ?> that match your search criteria<?php } ?>.</p>
     <?php } // Show if recordset empty ?>
     <?php if($totalRows_rsDirectory > 0 || isset($_GET['search'])) { ?><form action="associate_directory.php" method="get" name="form1" id="form1" class="form-inline">
    Filter results by those containing:
    <input name="search" type="text"  id="search" value="<?php echo isset($_GET['search']) ? htmlentities(trim($_GET['search'])) : ""; ?>" maxlength="20" class="form-control" />
        <input name="userID" type="hidden" id="userID" value="<?php echo intval($_GET['userID']); ?>" />
    <button name="go" type="submit"  id="go" class="form-default" >Search</button>
  </form><?php } ?>
   <?php if ($totalRows_rsDirectory > 0) { // Show if recordset not empty ?>
  <p>To associate this user with an organisation click on Add next to the organisation concerned.</p>
  
  <table class="table table-hover">

        <?php do { ?>
          <tr>
            <td><a href="../../directory/admin/update_directory.php?directoryID=<?php echo $row_rsDirectory['ID']; ?>"><?php echo $row_rsDirectory['name']; ?></a></td>
            <td><a href="associate_directory.php?associate=true&amp;directoryID=<?php echo $row_rsDirectory['ID']; ?>&amp;userID=<?php echo intval($_GET['userID']); ?>" class="btn btn-default btn-secondary">Add</a></td>
          </tr>
          <?php } while ($row_rsDirectory = mysql_fetch_assoc($rsDirectory)); ?>
     </table>
  <?php } // Show if recordset not empty ?>
  <table class="form-table">
    <tr>
      <td><?php if ($pageNum_rsDirectory > 0) { // Show if not first page ?>
            <a href="<?php printf("%s?pageNum_rsDirectory=%d%s", $currentPage, 0, $queryString_rsDirectory); ?>">First</a>
            <?php } // Show if not first page ?>
      </td>
      <td><?php if ($pageNum_rsDirectory > 0) { // Show if not first page ?>
            <a href="<?php printf("%s?pageNum_rsDirectory=%d%s", $currentPage, max(0, $pageNum_rsDirectory - 1), $queryString_rsDirectory); ?>" rel="prev">Previous</a>
            <?php } // Show if not first page ?>
      </td>
      <td><?php if ($pageNum_rsDirectory < $totalPages_rsDirectory) { // Show if not last page ?>
            <a href="<?php printf("%s?pageNum_rsDirectory=%d%s", $currentPage, min($totalPages_rsDirectory, $pageNum_rsDirectory + 1), $queryString_rsDirectory); ?>" rel="next">Next</a>
            <?php } // Show if not last page ?>
      </td>
      <td><?php if ($pageNum_rsDirectory < $totalPages_rsDirectory) { // Show if not last page ?>
            <a href="<?php printf("%s?pageNum_rsDirectory=%d%s", $currentPage, $totalPages_rsDirectory, $queryString_rsDirectory); ?>">Last</a>
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
mysql_free_result($rsDirectory);

mysql_free_result($rsUser);

mysql_free_result($rsLoggedIn);
?>


