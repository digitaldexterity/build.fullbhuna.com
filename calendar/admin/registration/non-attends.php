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
$query_rsLoggedIn = sprintf("SELECT ID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

$maxRows_rsNonAttendees = 200;
$pageNum_rsNonAttendees = 0;
if (isset($_GET['pageNum_rsNonAttendees'])) {
  $pageNum_rsNonAttendees = $_GET['pageNum_rsNonAttendees'];
}
$startRow_rsNonAttendees = $pageNum_rsNonAttendees * $maxRows_rsNonAttendees;

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsNonAttendees = "SELECT event.ID, event.startdatetime, users.firstname, users.surname, eventgroup.eventtitle, eventresource.resourcename FROM event LEFT JOIN eventattend ON (event.ID =  eventattend.eventID) LEFT JOIN users ON (eventattend.userID = users.ID) LEFT JOIN eventgroup ON (event.eventgroupID = eventgroup.ID) LEFT JOIN eventresource ON (eventgroup.resourceID = eventresource.ID) WHERE event.startdatetime < CURDATE() AND eventattend.statusID = 0 ORDER BY event.startdatetime DESC";
$query_limit_rsNonAttendees = sprintf("%s LIMIT %d, %d", $query_rsNonAttendees, $startRow_rsNonAttendees, $maxRows_rsNonAttendees);
$rsNonAttendees = mysql_query($query_limit_rsNonAttendees, $aquiescedb) or die(mysql_error());
$row_rsNonAttendees = mysql_fetch_assoc($rsNonAttendees);

if (isset($_GET['totalRows_rsNonAttendees'])) {
  $totalRows_rsNonAttendees = $_GET['totalRows_rsNonAttendees'];
} else {
  $all_rsNonAttendees = mysql_query($query_rsNonAttendees);
  $totalRows_rsNonAttendees = mysql_num_rows($all_rsNonAttendees);
}
$totalPages_rsNonAttendees = ceil($totalRows_rsNonAttendees/$maxRows_rsNonAttendees)-1;

$queryString_rsNonAttendees = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsNonAttendees") == false && 
        stristr($param, "totalRows_rsNonAttendees") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsNonAttendees = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsNonAttendees = sprintf("&totalRows_rsNonAttendees=%d%s", $totalRows_rsNonAttendees, $queryString_rsNonAttendees);
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Non-Attendees Report"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
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
        <h1><i class="glyphicon glyphicon-calendar"></i> Non-Attendees Report</h1>
        <?php if ($totalRows_rsNonAttendees == 0) { // Show if recordset empty ?>
  <p>There are currently no non-attendees recorded.</p>
  <?php } // Show if recordset empty ?>
        <?php if ($totalRows_rsNonAttendees > 0) { // Show if recordset not empty ?>
          <p>Records <?php echo ($startRow_rsNonAttendees + 1) ?> to <?php echo min($startRow_rsNonAttendees + $maxRows_rsNonAttendees, $totalRows_rsNonAttendees) ?> of <?php echo $totalRows_rsNonAttendees ?></p>
          <form name="form1" method="post" action="">
            <input type="hidden" name="startdatetime" id="enddatetime">
          </form>
          <table class="listTable">
            <tr>
              <th>Time</th>
              <th>Diary</th>
              <th>&nbsp;</th>
              <th>Name</th>
            </tr>
            <?php do { ?>
              <tr>
                <td><?php echo date('d M Y H:i', strtotime($row_rsNonAttendees['startdatetime'])); ?></td>
                <td><?php echo $row_rsNonAttendees['eventtitle']; ?></td>
                <td><?php echo $row_rsNonAttendees['resourcename']; ?></td>
                <td><?php echo $row_rsNonAttendees['firstname']; ?> <?php echo $row_rsNonAttendees['surname']; ?></td>
              </tr>
              <?php } while ($row_rsNonAttendees = mysql_fetch_assoc($rsNonAttendees)); ?>
          </table>
          <?php } // Show if recordset not empty ?>
<table border="0" class="form-table">
          <tr>
            <td><?php if ($pageNum_rsNonAttendees > 0) { // Show if not first page ?>
                <a href="<?php printf("%s?pageNum_rsNonAttendees=%d%s", $currentPage, 0, $queryString_rsNonAttendees); ?>">First</a>
            <?php } // Show if not first page ?></td>
            <td><?php if ($pageNum_rsNonAttendees > 0) { // Show if not first page ?>
                <a href="<?php printf("%s?pageNum_rsNonAttendees=%d%s", $currentPage, max(0, $pageNum_rsNonAttendees - 1), $queryString_rsNonAttendees); ?>">Previous</a>
            <?php } // Show if not first page ?></td>
            <td><?php if ($pageNum_rsNonAttendees < $totalPages_rsNonAttendees) { // Show if not last page ?>
                <a href="<?php printf("%s?pageNum_rsNonAttendees=%d%s", $currentPage, min($totalPages_rsNonAttendees, $pageNum_rsNonAttendees + 1), $queryString_rsNonAttendees); ?>">Next</a>
            <?php } // Show if not last page ?></td>
            <td><?php if ($pageNum_rsNonAttendees < $totalPages_rsNonAttendees) { // Show if not last page ?>
                <a href="<?php printf("%s?pageNum_rsNonAttendees=%d%s", $currentPage, $totalPages_rsNonAttendees, $queryString_rsNonAttendees); ?>">Last</a>
            <?php } // Show if not last page ?></td>
          </tr>
        </table>
<p>&nbsp;</p>
      </div>
      <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsNonAttendees);
?>
