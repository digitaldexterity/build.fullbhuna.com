<?php require_once('../../../../Connections/aquiescedb.php'); ?>
<?php require_once('../../../includes/adminAccess.inc.php'); ?><?php require_once('../../../includes/framework.inc.php'); ?>
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

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

$_GET['startdate'] =  isset($_GET['startdate']) ? htmlentities($_GET['startdate']) : date('Y-m-d', strtotime("1 MONTH AGO")) ;
$_GET['enddate'] =  isset($_GET['enddate']) ? htmlentities($_GET['enddate']) : date('Y-m-d') ;

$maxRows_rsUserSessions = 100;
$pageNum_rsUserSessions = 0;
if (isset($_GET['pageNum_rsUserSessions'])) {
  $pageNum_rsUserSessions = $_GET['pageNum_rsUserSessions'];
}
$startRow_rsUserSessions = $pageNum_rsUserSessions * $maxRows_rsUserSessions;

$varStartDate_rsUserSessions = "2000-01-01";
if (isset($_GET['startdate'])) {
  $varStartDate_rsUserSessions = $_GET['startdate'];
}
$varUsername_rsUserSessions = "-1";
if (isset($_GET['username'])) {
  $varUsername_rsUserSessions = $_GET['username'];
}
$varEndDate_rsUserSessions = "2999-01-01";
if (isset($_GET['enddate'])) {
  $varEndDate_rsUserSessions = $_GET['enddate'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsUserSessions = sprintf("SELECT track_page.`datetime`, track_page.page, track_page.pageTitle, track_session.ID AS sessionID FROM track_session LEFT JOIN  track_page ON (track_page.sessionID = track_session.ID) WHERE track_session.username = %s AND DATE(track_session.`datetime`) >= %s  AND DATE(track_session.`datetime`) <= %s ORDER BY track_page.`datetime` DESC", GetSQLValueString($varUsername_rsUserSessions, "text"),GetSQLValueString($varStartDate_rsUserSessions, "date"),GetSQLValueString($varEndDate_rsUserSessions, "date"));
$query_limit_rsUserSessions = sprintf("%s LIMIT %d, %d", $query_rsUserSessions, $startRow_rsUserSessions, $maxRows_rsUserSessions);
$rsUserSessions = mysql_query($query_limit_rsUserSessions, $aquiescedb) or die(mysql_error());
$row_rsUserSessions = mysql_fetch_assoc($rsUserSessions);

if (isset($_GET['totalRows_rsUserSessions'])) {
  $totalRows_rsUserSessions = $_GET['totalRows_rsUserSessions'];
} else {
  $all_rsUserSessions = mysql_query($query_rsUserSessions);
  $totalRows_rsUserSessions = mysql_num_rows($all_rsUserSessions);
}
$totalPages_rsUserSessions = ceil($totalRows_rsUserSessions/$maxRows_rsUserSessions)-1;

$colname_rsThisUser = "-1";
if (isset($_GET['username'])) {
  $colname_rsThisUser = $_GET['username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsThisUser = sprintf("SELECT firstname, surname FROM users WHERE username = %s", GetSQLValueString($colname_rsThisUser, "text"));
$rsThisUser = mysql_query($query_rsThisUser, $aquiescedb) or die(mysql_error());
$row_rsThisUser = mysql_fetch_assoc($rsThisUser);
$totalRows_rsThisUser = mysql_num_rows($rsThisUser);
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "User Activity for ";
$pageTitle .= isset($row_rsThisUser['surname']) ? htmlentities($row_rsThisUser['firstname']." ". $row_rsThisUser['surname'], ENT_COMPAT, "UTF-8") : htmlentities($_GET['username'], ENT_COMPAT, "UTF-8");
 echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../includes/seo.inc.php'); ?>
<?php require_once('../../../includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<style>
<!--
-->
</style>
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../../includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" -->
    <div class="page class">
      <h1><i class="glyphicon glyphicon-globe"></i> <?php echo $pageTitle; ?></h1>
      <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav"> <li class="nav-item"><a href="javascript:history.go(-1);" class="nav-link"><i class="glyphicon glyphicon-arrow-left"></i> Back </a></li></ul></div></nav>
     
      <form name="form1" method="get" >
        <fieldset class="form-inline">
          <legend>Filter by date</legend>
          From <input type="hidden" name="startdate" id="startdate" value="<?php $setvalue =  isset($_GET['startdate']) ? htmlentities($_GET['startdate']) : date('Y-m-d', strtotime("1 MONTH AGO")) ; echo $setvalue; $inputname = "startdate";?>"><?php require('../../../includes/datetimeinput.inc.php'); ?> until <input type="hidden" name="enddate" id="enddate" value="<?php $setvalue =  isset($_GET['enddate']) ? htmlentities($_GET['enddate']) : date('Y-m-d') ; echo $setvalue; $inputname = "enddate";?>"><?php require('../../../includes/datetimeinput.inc.php'); ?>
        <button type="submit" class="btn btn-default btn-secondary" >Go</button> <em>Data kept since <?php echo date('d M Y', strtotime("TODAY - ".TRACKER_PERIOD)) ; ?></em>
        </fieldset>
      </form>
      <?php if ($totalRows_rsUserSessions == 0) { // Show if recordset empty ?>
<p>This user has not had any activity for the set dates</p>
<?php } // Show if recordset empty ?>
<?php if ($totalRows_rsUserSessions > 0) { // Show if recordset not empty ?>
<p class="text-muted">Pages visited <?php echo ($startRow_rsUserSessions + 1) ?> to <?php echo min($startRow_rsUserSessions + $maxRows_rsUserSessions, $totalRows_rsUserSessions) ?> of <?php echo $totalRows_rsUserSessions ?> (most recent frst)</p>
      <table class="table table-hover">
      <thead>
        <tr>
          <th>Date/Time</th>
          <th>Page </th>
          <th>&nbsp; </th>
        
        </tr></thead><tbody>
        <?php do { ?>
          <tr>
            <td><?php echo  date('d M Y H:i', strtotime($row_rsUserSessions['datetime'])); ?></td>
            <td><a href="<?php echo $row_rsUserSessions['page']; ?>" title="<?php echo $row_rsUserSessions['page']; ?>" target="_blank" rel="noopener" data-toggle="tooltip"><?php echo $row_rsUserSessions['pageTitle']; ?></a></td>
            <td><a href="visitor-session.php?sessionID=<?php echo sprintf('%f', $row_rsUserSessions['sessionID']); ?>" class="btn btn-default btn-secondary">Session</a></td>
          </tr>
          <?php } while ($row_rsUserSessions = mysql_fetch_assoc($rsUserSessions)); ?></tbody>
      </table>
      <?php } // Show if recordset not empty ?>
      <?php echo createPagination($pageNum_rsUserSessions,$totalPages_rsUserSessions,"rsUserSessions");?>
    </div>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsUserSessions);

mysql_free_result($rsThisUser);
?>
