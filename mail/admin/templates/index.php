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

if(isset($_GET['deleteID']) && intval($_GET['deleteID'])>0) {
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$delete = "DELETE FROM groupemailtemplate WHERE ID = ".intval($_GET['deleteID']);
	mysql_query($delete, $aquiescedb) or die(mysql_error());

}

$varRegionID_rsTemplates = "1";
if (isset($regionID)) {
  $varRegionID_rsTemplates = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsTemplates = sprintf("SELECT * FROM groupemailtemplate WHERE groupemailtemplate.regionID = %s ORDER BY createddatetime DESC", GetSQLValueString($varRegionID_rsTemplates, "int"));
$rsTemplates = mysql_query($query_rsTemplates, $aquiescedb) or die(mysql_error());
$row_rsTemplates = mysql_fetch_assoc($rsTemplates);
$totalRows_rsTemplates = mysql_num_rows($rsTemplates);

?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Manage Mail Templates"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<link href="../../css/mailDefault.css" rel="stylesheet" type="text/css" />
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" --><div class="page mail"><?php require_once('../../../core/region/includes/chooseregion.inc.php'); ?>
  <h1><i class="glyphicon glyphicon-envelope"></i> Manage Mail Templates</h1>
  <p>NOTE: This section is intended only for advanced users who have a knowledge of HTML.  </p>
  <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
    <li class="nav-item"><a href="add_template.php" class="nav-link"><i class="glyphicon glyphicon-plus-sign"></i> Add new template</a></li>
    <li  class="nav-item"><a href="../index.php" class="nav-link"><i class="glyphicon glyphicon-arrow-left"></i> Manage Mail</a></li>
  </ul></div></nav>
  <?php if ($totalRows_rsTemplates == 0) { // Show if recordset empty ?>
  <p>There are currently no templates in the system.</p>
  <?php } // Show if recordset empty ?>
<?php if ($totalRows_rsTemplates > 0) { // Show if recordset not empty ?>
  <table  class="table table-hover">
  <thead>
    <tr>
      <th>&nbsp;</th>
      <th>Created</th>
      <th>Name</th>
      <th>Default Subject</th>
      <th colspan="3">Actions</th></tr></thead><tbody>
    <?php do { ?>
      <tr><td class="status<?php echo $row_rsTemplates['statusID']; ?>"></td><td><?php echo date('d M y',strtotime($row_rsTemplates['createddatetime'])); ?></td>
        <td><a href="update_template.php?templateID=<?php echo $row_rsTemplates['ID']; ?>"><?php echo $row_rsTemplates['templatename']; ?></a></td>
        <td><a href="update_template.php?templateID=<?php echo $row_rsTemplates['ID']; ?>"><?php echo $row_rsTemplates['templatesubject']; ?></a></td>
        <td class="text-nowrap"><a href="update_template.php?templateID=<?php echo $row_rsTemplates['ID']; ?>" class="btn btn-sm btn-default btn-secondary"><i class="glyphicon glyphicon-pencil"></i> Edit</a> <a href="index.php?deleteID=<?php echo $row_rsTemplates['ID']; ?>"  onclick="return confirm('Are you sure you wish to delete this template?\n\nThis cannot be undone.');" class="btn btn-sm btn-default btn-secondary"><i class="glyphicon glyphicon-trash"></i> Delete</a> <a href="add_template.php?basedontemplateID=<?php echo $row_rsTemplates['ID']; ?>" class="btn btn-sm btn-default btn-secondary" title="Add new based on this template"><i class="glyphicon glyphicon-plus-sign"></i> Add Copy</a></td>
        
        
      </tr>
      <?php } while ($row_rsTemplates = mysql_fetch_assoc($rsTemplates)); ?></tbody>
  </table>
  <?php } // Show if recordset not empty ?></div>
  <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsTemplates);
?>
