<?php require_once('../../../Connections/aquiescedb.php'); ?>
<?php require_once('../../../core/includes/adminAccess.inc.php'); ?><?php require_once('../../includes/sendmail.inc.php'); ?>
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

createMailList($_GET['groupemailID']); 

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

$colname_rsGroupEmail = "-1";
if (isset($_GET['groupemailID'])) {
  $colname_rsGroupEmail = $_GET['groupemailID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsGroupEmail = sprintf("SELECT groupemail.* , usertype.name, usergroup.groupname FROM groupemail  LEFT JOIN usertype ON (groupemail.usertypeID = usertype.ID)  LEFT JOIN usergroupmember ON (usergroupmember.groupID= groupemail.usergroupID ) LEFT JOIN usergroup ON (groupemail.usergroupID = usergroup.ID)  WHERE groupemail.ID = %s", GetSQLValueString($colname_rsGroupEmail, "int"));
$rsGroupEmail = mysql_query($query_rsGroupEmail, $aquiescedb) or die(mysql_error());
$row_rsGroupEmail = mysql_fetch_assoc($rsGroupEmail);
$totalRows_rsGroupEmail = mysql_num_rows($rsGroupEmail);

$maxRows_rsUser = 500;
$pageNum_rsUser = 0;
if (isset($_GET['pageNum_rsUser'])) {
  $pageNum_rsUser = $_GET['pageNum_rsUser'];
}
$startRow_rsUser = $pageNum_rsUser * $maxRows_rsUser;

$varGroupEmailID_rsUser = "-1";
if (isset($_GET['groupemailID'])) {
  $varGroupEmailID_rsUser = $_GET['groupemailID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsUser = sprintf("SELECT groupemaillist.sent, users.email,  users.ID, users.firstname, users.surname, users.salutation, users.username, users.password, users.plainpassword, directory.name AS company FROM groupemaillist LEFT JOIN users  ON (groupemaillist.userID = users.ID) LEFT JOIN directory ON (users.ID = directory.userID) WHERE  groupemaillist.groupemailID = %s GROUP BY users.ID ORDER BY groupemaillist.ID", GetSQLValueString($varGroupEmailID_rsUser, "int"));
$query_limit_rsUser = sprintf("%s LIMIT %d, %d", $query_rsUser, $startRow_rsUser, $maxRows_rsUser);
$rsUser = mysql_query($query_limit_rsUser, $aquiescedb) or die(mysql_error());
$row_rsUser = mysql_fetch_assoc($rsUser);

if (isset($_GET['totalRows_rsUser'])) {
  $totalRows_rsUser = $_GET['totalRows_rsUser'];
} else {
  $all_rsUser = mysql_query($query_rsUser);
  $totalRows_rsUser = mysql_num_rows($all_rsUser);
}
$totalPages_rsUser = ceil($totalRows_rsUser/$maxRows_rsUser)-1;

$queryString_rsUser = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsUser") == false && 
        stristr($param, "totalRows_rsUser") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsUser = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsUser = sprintf("&totalRows_rsUser=%d%s", $totalRows_rsUser, $queryString_rsUser);


?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "View Recipients and Merge Fields"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
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
    <!-- InstanceBeginEditable name="Body" --><div class="page mail"> <h1><i class="glyphicon glyphicon-envelope"></i> View Email Recipients
            
        </h1>
      <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
        <li><a href="preview.php?emailID=<?php echo $row_rsGroupEmail['ID']; ?>" class="link_undo"><i class="glyphicon glyphicon-arrow-left"></i> Preview Email</a></li>
      </ul></div></nav>
      <h2>This email is sent to: <?php echo isset($row_rsGroupEmail['groupname']) ? $row_rsGroupEmail['groupname']."; " : "";  echo ($row_rsGroupEmail['name']=="Non User") ? "Any rank" : $row_rsGroupEmail['name']."+"; ?></h2>
      <?php if ($totalRows_rsUser == 0) { // Show if recordset empty ?>
  <p>There are no valid recipients in this group.</p>
  <?php } // Show if recordset empty ?>
      <?php if ($totalRows_rsUser > 0) { // Show if recordset not empty ?>
        <p class="text-muted">Recipients <?php echo ($startRow_rsUser + 1) ?> to <?php echo min($startRow_rsUser + $maxRows_rsUser, $totalRows_rsUser) ?> of <?php echo $totalRows_rsUser ?></p>
        <table  class="table table-hover"><thead>
          <tr>
            <th scope="col"> ID</th>
            <th scope="col">Name</th>
            <th scope="col">Email</th>
            <th scope="col">Username</th>
            <th scope="col">Company</th>
            <th scope="col">Sent</th>
          </tr></thead><tbody>
          <?php do { ?>
            <tr>
              <td><?php echo $row_rsUser['ID']; ?></td>
              <td><?php echo $row_rsUser['firstname']; ?> <?php echo $row_rsUser['surname']; ?></td>
              <td><?php echo $row_rsUser['email']; ?></td>
              <td><?php echo $row_rsUser['username']; ?></td>
              <td><?php echo $row_rsUser['company']; ?></td>
              <td><?php echo ($row_rsUser['sent']==1) ? '<span class="glyphicon glyphicon-ok"></span>' : ""; ?></td>
            </tr>
            <?php } while ($row_rsUser = mysql_fetch_assoc($rsUser)); ?></tbody>
        </table>
        <?php } // Show if recordset not empty ?>
<table class="form-table">
  <tr>
    <td><?php if ($pageNum_rsUser > 0) { // Show if not first page ?>
        <a href="<?php printf("%s?pageNum_rsUser=%d%s", $currentPage, 0, $queryString_rsUser); ?>">First</a>
        <?php } // Show if not first page ?></td>
    <td><?php if ($pageNum_rsUser > 0) { // Show if not first page ?>
        <a href="<?php printf("%s?pageNum_rsUser=%d%s", $currentPage, max(0, $pageNum_rsUser - 1), $queryString_rsUser); ?>">Previous</a>
        <?php } // Show if not first page ?></td>
    <td><?php if ($pageNum_rsUser < $totalPages_rsUser) { // Show if not last page ?>
        <a href="<?php printf("%s?pageNum_rsUser=%d%s", $currentPage, min($totalPages_rsUser, $pageNum_rsUser + 1), $queryString_rsUser); ?>">Next</a>
        <?php } // Show if not last page ?></td>
    <td><?php if ($pageNum_rsUser < $totalPages_rsUser) { // Show if not last page ?>
        <a href="<?php printf("%s?pageNum_rsUser=%d%s", $currentPage, $totalPages_rsUser, $queryString_rsUser); ?>">Last</a>
        <?php } // Show if not last page ?></td>
  </tr>
</table>
      </div><!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsGroupEmail);

mysql_free_result($rsUser);
?>
