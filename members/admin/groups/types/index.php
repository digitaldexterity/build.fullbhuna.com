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

$MM_restrictGoTo = "/login/index.php?notloggedin=true";
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

$maxRows_rsGroupTypes = 10;
$pageNum_rsGroupTypes = 0;
if (isset($_GET['pageNum_rsGroupTypes'])) {
  $pageNum_rsGroupTypes = $_GET['pageNum_rsGroupTypes'];
}
$startRow_rsGroupTypes = $pageNum_rsGroupTypes * $maxRows_rsGroupTypes;

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsGroupTypes = "SELECT * FROM usergrouptype ORDER BY grouptype ASC";
$query_limit_rsGroupTypes = sprintf("%s LIMIT %d, %d", $query_rsGroupTypes, $startRow_rsGroupTypes, $maxRows_rsGroupTypes);
$rsGroupTypes = mysql_query($query_limit_rsGroupTypes, $aquiescedb) or die(mysql_error());
$row_rsGroupTypes = mysql_fetch_assoc($rsGroupTypes);

if (isset($_GET['totalRows_rsGroupTypes'])) {
  $totalRows_rsGroupTypes = $_GET['totalRows_rsGroupTypes'];
} else {
  $all_rsGroupTypes = mysql_query($query_rsGroupTypes);
  $totalRows_rsGroupTypes = mysql_num_rows($all_rsGroupTypes);
}
$totalPages_rsGroupTypes = ceil($totalRows_rsGroupTypes/$maxRows_rsGroupTypes)-1;


?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Manage Group Types"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
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
    <h1><i class="glyphicon glyphicon-user"></i> Manage  Group Categories</h1>
    <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
      <li class="nav-item"><a href="add_group_type.php" class="nav-link"><i class="glyphicon glyphicon-plus-sign"></i> Add Group Categories</a></li>
      <li class="nav-item"><a href="../index.php?groupID=0" class="nav-link"><i class="glyphicon glyphicon-arrow-left"></i> Back to Groups</a></li>
     
    </ul></div></nav>
    <?php if ($totalRows_rsGroupTypes == 0) { // Show if recordset empty ?>
  <p>There are currently no group categories. You can split groups into different types to help identify their purpose.</p>
  <?php } // Show if recordset empty ?>
<?php if ($totalRows_rsGroupTypes > 0) { // Show if recordset not empty ?>
  <p class="text-muted">Group categories <?php echo ($startRow_rsGroupTypes + 1) ?> to <?php echo min($startRow_rsGroupTypes + $maxRows_rsGroupTypes, $totalRows_rsGroupTypes) ?> of <?php echo $totalRows_rsGroupTypes ?> </p>
      <table  class="table table-hover">
        
        <?php do { ?>
          <tr>
            <td class="status<?php echo $row_rsGroupTypes['statusID']; ?>">&nbsp;</td>
            
            <td><?php echo $row_rsGroupTypes['grouptype']; ?></td>
            <td><a href="update_group_type.php?grouptypeID=<?php echo $row_rsGroupTypes['ID']; ?>" class="link_edit icon_only">Edit</a></td>
            
            
            
          </tr>
          <?php } while ($row_rsGroupTypes = mysql_fetch_assoc($rsGroupTypes)); ?>
      </table>
      <?php } // Show if recordset not empty ?></div>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsGroupTypes);
?>
