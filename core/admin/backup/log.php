<?php require_once('../../../Connections/aquiescedb.php'); ?><?php require_once('../../includes/adminAccess.inc.php'); ?>
<?php require_once('../../includes/framework.inc.php'); ?>
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

if(isset($_GET['clearlog'])) {
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$delete = "DELETE  FROM backup";
	mysql_query($delete, $aquiescedb) or die(mysql_error());	
	header("location: log.php");
}
$currentPage = $_SERVER["PHP_SELF"];

$maxRows_rsBackupLog = 100;
$pageNum_rsBackupLog = 0;
if (isset($_GET['pageNum_rsBackupLog'])) {
  $pageNum_rsBackupLog = $_GET['pageNum_rsBackupLog'];
}
$startRow_rsBackupLog = $pageNum_rsBackupLog * $maxRows_rsBackupLog;

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsBackupLog = "SELECT backup.*, users.firstname, users.surname FROM backup LEFT JOIN users ON (backup.createdbyID = users.ID) ORDER BY createddatetime DESC";
$query_limit_rsBackupLog = sprintf("%s LIMIT %d, %d", $query_rsBackupLog, $startRow_rsBackupLog, $maxRows_rsBackupLog);
$rsBackupLog = mysql_query($query_limit_rsBackupLog, $aquiescedb) or die(mysql_error());
$row_rsBackupLog = mysql_fetch_assoc($rsBackupLog);

if (isset($_GET['totalRows_rsBackupLog'])) {
  $totalRows_rsBackupLog = $_GET['totalRows_rsBackupLog'];
} else {
  $all_rsBackupLog = mysql_query($query_rsBackupLog);
  $totalRows_rsBackupLog = mysql_num_rows($all_rsBackupLog);
}
$totalPages_rsBackupLog = ceil($totalRows_rsBackupLog/$maxRows_rsBackupLog)-1;

$queryString_rsBackupLog = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsBackupLog") == false && 
        stristr($param, "totalRows_rsBackupLog") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsBackupLog = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsBackupLog = sprintf("&totalRows_rsBackupLog=%d%s", $totalRows_rsBackupLog, $queryString_rsBackupLog);
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Back Up Log"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../seo/includes/seo.inc.php'); ?>
<?php require_once('../../includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" --><div class="page backup">
    <h1><i class="glyphicon glyphicon-saved"></i> Back-up log</h1>
    <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
      <li><a href="index.php" class="link_undo"><i class="glyphicon glyphicon-arrow-left"></i> Backup Manager</a></li>
      <li><a href="log.php?clearlog=true" class="link_manage" onClick="return confirm('Are you sure you want to erase this log?\nWARNING: This cannot be undone')"><i class="glyphicon glyphicon-cog"></i> Clear Log</a></li>
    </ul></div></nav>
    <?php if ($totalRows_rsBackupLog == 0) { // Show if recordset empty ?>
  <p>There are no back-ups currently logged.
  </p>
  <?php } // Show if recordset empty ?>
    <?php if ($totalRows_rsBackupLog > 0) { // Show if recordset not empty ?>
      <p class="text-muted">Logged back-ups <?php echo ($startRow_rsBackupLog + 1) ?> to <?php echo min($startRow_rsBackupLog + $maxRows_rsBackupLog, $totalRows_rsBackupLog) ?> of <?php echo $totalRows_rsBackupLog ?></p>
      <table  class="table table-hover">
      <thead>
        <tr><th>&nbsp;</th>
         <th>Date/time</th>
          <th>Content</th>
           <th>Filename</th>
         
          <th>User</th>
        </tr></thead><tbody>
        <?php do { ?>
          <tr> <td class="status<?php echo $row_rsBackupLog['statusID']; ?>">&nbsp;</td>
                       <td><?php echo date('d M Y H:i',strtotime($row_rsBackupLog['createddatetime'])); ?></td>
 <td><?php echo $row_rsBackupLog['backupcontenttype']==1 ? "Database" : "File" ; ?></td>
            <td><a href="javascript:void(0);" title="<?php echo htmlentities($row_rsBackupLog['remotefilename'], ENT_COMPAT, "UTF-8"); ?>" data-toggle="tooltip"><?php echo $row_rsBackupLog['backupfilename']; if(basename($row_rsBackupLog['backupfilename'])!=basename($row_rsBackupLog['remotefilename'])) echo " [Name modified]"; ?></a></td>
            <td><?php echo $row_rsBackupLog['createdbyID']==0 ? "System (automatic)" : $row_rsBackupLog['firstname']." ".$row_rsBackupLog['surname']; ?></td>            
           

          </tr>
          <?php } while ($row_rsBackupLog = mysql_fetch_assoc($rsBackupLog)); ?></tbody>
      </table>
      <?php } // Show if recordset not empty ?>
       <?php echo createPagination($pageNum_rsBackupLog,$totalPages_rsBackupLog,"rsBackupLog");?>
       <p>[Name modified] - for maximum compatibility with most servers, filenames must only contain alpha-numeric characters (as well as dots, underscores and dashes) so any other characters will be stripped out for backup.</p>
</div>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsBackupLog);
?>
