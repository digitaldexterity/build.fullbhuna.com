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

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if(isset($_GET['deleteID']) && intval($_GET['deleteID'])>0) {
	$delete = "DELETE FROM contactsubject WHERE ID = ".intval($_GET['deleteID']);
	mysql_query($delete, $aquiescedb) or die(mysql_error());
	header("location: subject.php"); exit;
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
  $insertSQL = sprintf("INSERT INTO contactsubject (`description`, statusID) VALUES (%s, %s)",
                       GetSQLValueString($_POST['description'], "text"),
                       GetSQLValueString($_POST['statusID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($insertSQL, $aquiescedb) or die(mysql_error());
}

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsSubjectList = "SELECT * FROM contactsubject ORDER BY ordernum ASC";
$rsSubjectList = mysql_query($query_rsSubjectList, $aquiescedb) or die(mysql_error());
$row_rsSubjectList = mysql_fetch_assoc($rsSubjectList);
$totalRows_rsSubjectList = mysql_num_rows($rsSubjectList);
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = ""; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script src="../../../SpryAssets/SpryValidationTextField.js"></script>
<link href="../../../SpryAssets/SpryValidationTextField.css" rel="stylesheet" type="text/css" />
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
    <!-- InstanceBeginEditable name="Body" -->
        <div class="page mail">
    <h1><i class="glyphicon glyphicon-envelope"></i> Manage Contact Subject List</h1>
    <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
      <li class="nav-item"><a href="contact.php" class="nav-link"><i class="glyphicon glyphicon-arrow-left"></i> Back</a></li>
    </ul></div></nav>
    <?php if ($totalRows_rsSubjectList == 0) { // Show if recordset empty ?>
      <p>There are no items in te subject drop down so far</p>
      <?php } // Show if recordset empty ?>
<form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1">
      <fieldset class="form-inline">
        <legend>New item</legend>
        <span id="sprytextfield1">
        <input name="description" type="text" id="description" size="50" maxlength="50"  class="form-control"/>
        <span class="textfieldRequiredMsg">A value is required.</span></span>
        <button type="submit" name="addbutton" id="addbutton" class="btn btn-default btn-secondary">Add</button>
        <input name="statusID" type="hidden" id="statusID" value="1" />
      </fieldset>
      <input type="hidden" name="MM_insert" value="form1" />
    </form>
<?php if ($totalRows_rsSubjectList > 0) { // Show if recordset not empty ?>
  <table class="table table-hover">
  <thead>
    <tr><th>&nbsp;</th>
      <th>Subject</th>
      
      <th>Delete</th>
    </tr></table><tbody>
    <?php do { ?>
      <tr><td class="status<?php echo $row_rsSubjectList['statusID']; ?>">&nbsp;</td>
        <td><?php echo $row_rsSubjectList['description']; ?></td>
        
        <td><a href="subject.php?deleteID=<?php echo $row_rsSubjectList['ID']; ?>" onclick="return confirm('Are you sure you want to delete this item?');" class="link_delete"><i class="glyphicon glyphicon-trash"></i> Delete</a></td>
      </tr>
      <?php } while ($row_rsSubjectList = mysql_fetch_assoc($rsSubjectList)); ?></tbody>
  </table>
  <?php } // Show if recordset not empty ?>
<script>
<!--
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1");
//-->
    </script></div>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsSubjectList);
?>
