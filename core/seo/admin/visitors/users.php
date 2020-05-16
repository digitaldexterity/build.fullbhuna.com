<?php require_once('../../../../Connections/aquiescedb.php'); ?>
<?php require_once('../../../includes/adminAccess.inc.php'); ?>
<?php require_once('../../../includes/framework.inc.php'); ?>
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

$_GET['startdate'] = isset($_GET['startdate']) ? $_GET['startdate'] : date('Y-m-d', strtotime("1 WEEK AGO"));
$_GET['enddate'] = isset($_GET['enddate']) ? $_GET['enddate'] : date('Y-m-d');

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

$_GET['startdate'] =  isset($_GET['startdate']) ? htmlentities($_GET['startdate']) : date('Y-m-d', strtotime("1 WEEK AGO")) ;
$_GET['enddate'] =  isset($_GET['enddate']) ? htmlentities($_GET['enddate']) : date('Y-m-d') ;

$maxRows_rsUsers = 50;
$pageNum_rsUsers = 0;
if (isset($_GET['pageNum_rsUsers'])) {
  $pageNum_rsUsers = $_GET['pageNum_rsUsers'];
}
$startRow_rsUsers = $pageNum_rsUsers * $maxRows_rsUsers;

$varStartDate_rsUsers = "2000-01-01";
if (isset($_GET['startdate'])) {
  $varStartDate_rsUsers = $_GET['startdate'];
}
$varEndDate_rsUsers = "2999-01-01";
if (isset($_GET['enddate'])) {
  $varEndDate_rsUsers = $_GET['enddate'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsUsers = sprintf("SELECT users.ID, users.firstname, users.surname,users.username, (SELECT COUNT(track_page.ID) FROM track_page WHERE track_page.sessionID = track_session.ID) AS viewed FROM track_session LEFT JOIN  users ON (track_session.username = users.username)  WHERE  track_session.username IS NOT NULL AND (DATE(track_session.`datetime`) >= %s  AND DATE(track_session.`datetime`) <= %s) AND  users.usertypeID < 10 GROUP BY users.ID ORDER BY viewed DESC", GetSQLValueString($varStartDate_rsUsers, "date"),GetSQLValueString($varEndDate_rsUsers, "date"));
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


if(isset($_GET['csv'])) {
	$headers= array("ID|hide","FIRST NAME", "SURNAME", "username|hide","VISITS");
	exportCSV($headers, $rsUsers, "User_Sessions-YY-MM-DD");
	die();
}


?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "User Activity"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
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
      <h1><i class="glyphicon glyphicon-globe"></i> User Activity</h1>
      <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav"> <li class="nav-itmem"><a href="index.php" class="nav-link"><i class="glyphicon glyphicon-arrow-left"></i> Back to Visitors</a></li></ul></div></nav>
       <p>Activity from users logged in to the site. Data kept since <?php echo date('d M Y', strtotime("TODAY - ".TRACKER_PERIOD)) ; ?>.</p>
      <form name="form1" method="get" >
        <fieldset class="form-inline">
          <legend>Filter by date</legend>
          From <input type="hidden" name="startdate" id="startdate" value="<?php $setvalue =  isset($_GET['startdate']) ? htmlentities($_GET['startdate'], ENT_COMPAT, "UTF-8") : date('Y-m-d', strtotime("1 MONTH AGO")) ; echo $setvalue; $inputname = "startdate";?>"><?php require('../../../includes/datetimeinput.inc.php'); ?> until <input type="hidden" name="enddate" id="enddate" value="<?php $setvalue =  isset($_GET['enddate']) ? htmlentities($_GET['enddate'], ENT_COMPAT, "UTF-8") : date('Y-m-d') ; echo $setvalue; $inputname = "enddate";?>"><?php require('../../../includes/datetimeinput.inc.php'); ?>
          <label><input type="checkbox" name="csv" value="1" <?php echo isset($_GET['csv']) ? " checked ": ""; ?>> Download as spreadsheet</label> <button type="submit" class="btn btn-default btn-secondary" >Go</button>
        </fieldset>
      </form>
      <?php if ($totalRows_rsUsers == 0) { // Show if recordset empty ?>
<p>No data for period.</p>
<?php } // Show if recordset empty ?>
<?php if ($totalRows_rsUsers > 0) { // Show if recordset not empty ?>
<p>Users <?php echo ($startRow_rsUsers + 1) ?> to <?php echo min($startRow_rsUsers + $maxRows_rsUsers, $totalRows_rsUsers) ?> of <?php echo $totalRows_rsUsers ?> </p>
      <table class="table table-hover">
      <thead>
        <tr>
          <th>Name</th>
          <th colspan="2">Page Views</th>
          <th>Last accessed</th>        
        </tr></thead><tbody>
        <?php do { ?>
          <tr>
            <td><a href="../../../../members/admin/modify_user.php?userID=<?php echo $row_rsUsers['ID']; ?>"><?php echo $row_rsUsers['firstname']; ?> <?php echo $row_rsUsers['surname']; ?></a></td>
            <td><?php echo $row_rsUsers['viewed']; ?></td><td><a href="user_sessions.php?username=<?php echo $row_rsUsers['username']; ?>&startdate=<?php echo htmlentities($_GET['startdate'], ENT_COMPAT, "UTF-8"); ?>&enddate=<?php echo htmlentities($_GET['enddate'], ENT_COMPAT, "UTF-8"); ?>" class="link_view">View</a></td>
             <td><?php $select = "SELECT datetime FROM  track_session  WHERE username = '".$row_rsUsers['username']."' ORDER BY datetime DESC LIMIT 1 "; 
			 $result = mysql_query($select, $aquiescedb) or die(mysql_error());
$row = mysql_fetch_assoc($result);



 echo isset($row['datetime']) ? date('d M Y H:i', strtotime($row['datetime'])) : ""; ?></td>
          </tr>
          <?php } while ($row_rsUsers = mysql_fetch_assoc($rsUsers)); ?></tbody>
      </table>
      <?php } // Show if recordset not empty ?>
    </div>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsUsers);
?>
