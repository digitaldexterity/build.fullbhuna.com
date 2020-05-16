<?php require_once('../../../Connections/aquiescedb.php'); ?><?php require_once('../../../core/includes/adminAccess.inc.php'); ?>
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

$maxRows_rsGroupEmails = 100;
$pageNum_rsGroupEmails = 0;
if (isset($_GET['pageNum_rsGroupEmails'])) {
  $pageNum_rsGroupEmails = $_GET['pageNum_rsGroupEmails'];
}
$startRow_rsGroupEmails = $pageNum_rsGroupEmails * $maxRows_rsGroupEmails;

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsGroupEmails = "SELECT ID, subject FROM groupemail";
$query_limit_rsGroupEmails = sprintf("%s LIMIT %d, %d", $query_rsGroupEmails, $startRow_rsGroupEmails, $maxRows_rsGroupEmails);
$rsGroupEmails = mysql_query($query_limit_rsGroupEmails, $aquiescedb) or die(mysql_error());
$row_rsGroupEmails = mysql_fetch_assoc($rsGroupEmails);

if (isset($_GET['totalRows_rsGroupEmails'])) {
  $totalRows_rsGroupEmails = $_GET['totalRows_rsGroupEmails'];
} else {
  $all_rsGroupEmails = mysql_query($query_rsGroupEmails);
  $totalRows_rsGroupEmails = mysql_num_rows($all_rsGroupEmails);
}
$totalPages_rsGroupEmails = ceil($totalRows_rsGroupEmails/$maxRows_rsGroupEmails)-1;

$groupby = "";
$select = "";

if((!isset($_GET['search']) || isset($_GET['unique']))) {
$groupby =   " GROUP BY users.ID ";
$select = ", COUNT(users.ID) AS views ";
}

$maxRows_rsClicks = 100;
$pageNum_rsClicks = 0;
if (isset($_GET['pageNum_rsClicks'])) {
  $pageNum_rsClicks = $_GET['pageNum_rsClicks'];
}
$startRow_rsClicks = $pageNum_rsClicks * $maxRows_rsClicks;

$colname_rsClicks = "0";
if (isset($_GET['groupemailID'])) {
  $colname_rsClicks = $_GET['groupemailID'];
}
$varSearch_rsClicks = "%";
if (isset($_GET['search'])) {
  $varSearch_rsClicks = $_GET['search'];
}
$varClickOnly_rsClicks = "0";
if (isset($_GET['clicksonly'])) {
  $varClickOnly_rsClicks = $_GET['clicksonly'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsClicks = sprintf("SELECT groupemailclick.*, users.firstname, users.surname, users.email ".$select." FROM groupemailclick LEFT JOIN users ON (groupemailclick.userID = users.ID) WHERE (groupemailID = %s OR %s = 0) AND (%s = 0 OR url IS NOT NULL) AND (users.email LIKE %s OR users.surname LIKE %s) ".$groupby." ORDER BY createddatetime DESC", GetSQLValueString($colname_rsClicks, "int"),GetSQLValueString($colname_rsClicks, "int"),GetSQLValueString($varClickOnly_rsClicks, "int"),GetSQLValueString($varSearch_rsClicks . "%", "text"),GetSQLValueString($varSearch_rsClicks . "%", "text"));
$query_limit_rsClicks = sprintf("%s LIMIT %d, %d", $query_rsClicks, $startRow_rsClicks, $maxRows_rsClicks);
$rsClicks = mysql_query($query_limit_rsClicks, $aquiescedb) or die(mysql_error());
$row_rsClicks = mysql_fetch_assoc($rsClicks);

if (isset($_GET['totalRows_rsClicks'])) {
  $totalRows_rsClicks = $_GET['totalRows_rsClicks'];
} else {
  $all_rsClicks = mysql_query($query_rsClicks);
  $totalRows_rsClicks = mysql_num_rows($all_rsClicks);
}
$totalPages_rsClicks = ceil($totalRows_rsClicks/$maxRows_rsClicks)-1;

$queryString_rsClicks = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsClicks") == false && 
        stristr($param, "totalRows_rsClicks") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsClicks = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsClicks = sprintf("&totalRows_rsClicks=%d%s", $totalRows_rsClicks, $queryString_rsClicks);

$queryString_rsGroupEmails = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsGroupEmails") == false && 
        stristr($param, "totalRows_rsGroupEmails") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsGroupEmails = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsGroupEmails = sprintf("&totalRows_rsGroupEmails=%d%s", $totalRows_rsGroupEmails, $queryString_rsGroupEmails);


?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Click tracking"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->

<link href="../../css/mailDefault.css" rel="stylesheet" type="text/css" />
<style><!--
<?php
if($groupby=="") {
	echo ".views { display: none !important; }";
}
?>
--></style>
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
        <div class="page mail">
    <h1><i class="glyphicon glyphicon-envelope"></i> Email known views* and click tracking</h1>
    <p>*NOTE - many email reader programs try to prevent detection of email viewing, so this is figure is only a guide. Click tracking is always accurate.</p>
    <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
      <li class="nav-item"><a href="index.php" class="nav-link"><i class="glyphicon glyphicon-arrow-left"></i> Group email</a></li>
    </ul></div></nav>
    <form method="get" name="form1" id="form1">
     <fieldset class="form-inline">
      <input name="search" type="text" value="<?php echo isset($_GET['search']) ? htmlentities($_GET['search']) : ""; ?>" size="30" maxlength="30" placeholder="Search by surname or email..."   class="form-control"/>
   
      <select name="groupemailID" id="groupemailID" class="form-control">
        <option value="0" <?php if (!(strcmp(0, @$_GET['groupemailID']))) {echo "selected=\"selected\"";} ?>>All emails</option>
        <?php
do {  
?>
        <option value="<?php echo $row_rsGroupEmails['ID']?>"<?php if (!(strcmp($row_rsGroupEmails['ID'], @$_GET['groupemailID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsGroupEmails['subject']?></option>
        <?php
} while ($row_rsGroupEmails = mysql_fetch_assoc($rsGroupEmails));
  $rows = mysql_num_rows($rsGroupEmails);
  if($rows > 0) {
      mysql_data_seek($rsGroupEmails, 0);
	  $row_rsGroupEmails = mysql_fetch_assoc($rsGroupEmails);
  }
?>
      </select>
      <button type="submit" name="filterbutton" id="filterbutton" class="btn btn-default btn-secondary" >Submit</button>
      &nbsp;&nbsp;&nbsp;
      <label>
        <input <?php if (!(strcmp(@$_GET['clicksonly'],1))) {echo "checked=\"checked\"";} ?> name="clicksonly" type="checkbox" id="clicksonly" value="1" onClick="this.form.submit();" />
        Show only clicks</label>
        
        &nbsp;&nbsp;&nbsp;
        <label>
        <input <?php if (!isset($_GET['search']) || $_GET['unique']==1) {echo "checked=\"checked\"";} ?> name="unique" type="checkbox" id="unique" value="1" onClick="this.form.submit();" />
        Group by unique visitor</label>
        
        </fieldset>
    </form>
    <?php if ($totalRows_rsClicks == 0) { // Show if recordset empty ?>
      <p>No one has clicked on any links in this email.</p>
      <?php } // Show if recordset empty ?>
    <?php if ($totalRows_rsClicks > 0) { // Show if recordset not empty ?>
  <p class="text-muted">Results <?php echo ($startRow_rsClicks + 1) ?> to <?php echo min($startRow_rsClicks + $maxRows_rsClicks, $totalRows_rsClicks) ?> of <?php echo $totalRows_rsClicks ?> </p>
  <table  class="table tabke-hover">
  <thead>
    <tr>
      <th>Logged</th>
      <th>User</th>
      <th>Email</th>
      <th class="views">Views
        </th>
      <th>Clicked link</th>
      
    </tr></thead><tbody>
    <?php do { ?>
      <tr>
        <td class="text-nowrap"><?php echo date('d M Y H:i',strtotime($row_rsClicks['createddatetime'])); ?></td>   
        <td><?php echo $row_rsClicks['firstname']; ?> <?php echo $row_rsClicks['surname']; ?></td> <td><?php echo $row_rsClicks['email']; ?></td>
        <td class="views"><?php echo $row_rsClicks['views']; ?></td>
        
        <td><?php echo $row_rsClicks['url']; ?></td>
      </tr>
      <?php } while ($row_rsClicks = mysql_fetch_assoc($rsClicks)); ?>
 </tbody> </table>
      
  <table class="form-table">
    <tr>
      <td><?php if ($pageNum_rsClicks > 0) { // Show if not first page ?>
        <a href="<?php printf("%s?pageNum_rsClicks=%d%s", $currentPage, 0, $queryString_rsClicks); ?>">First</a>
        <?php } // Show if not first page ?></td>
      <td><?php if ($pageNum_rsClicks > 0) { // Show if not first page ?>
        <a href="<?php printf("%s?pageNum_rsClicks=%d%s", $currentPage, max(0, $pageNum_rsClicks - 1), $queryString_rsClicks); ?>">Previous</a>
        <?php } // Show if not first page ?></td>
      <td><?php if ($pageNum_rsClicks < $totalPages_rsClicks) { // Show if not last page ?>
        <a href="<?php printf("%s?pageNum_rsClicks=%d%s", $currentPage, min($totalPages_rsClicks, $pageNum_rsClicks + 1), $queryString_rsClicks); ?>">Next</a>
        <?php } // Show if not last page ?></td>
      <td><?php if ($pageNum_rsClicks < $totalPages_rsClicks) { // Show if not last page ?>
        <a href="<?php printf("%s?pageNum_rsClicks=%d%s", $currentPage, $totalPages_rsClicks, $queryString_rsClicks); ?>">Last</a>
        <?php } // Show if not last page ?></td>
    </tr>
  </table>
  <?php } // Show if recordset not empty ?>
   </div>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsGroupEmails);

mysql_free_result($rsClicks);
?>
