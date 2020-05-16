<?php require_once('../../../Connections/aquiescedb.php'); ?><?php require_once('../../../core/includes/adminAccess.inc.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "7,8,9,10";
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

$_GET['startdate'] = isset($_GET['startdate']) ? $_GET['startdate']: date('Y-m-d', strtotime("1 YEAR AGO"));
$_GET['enddate'] = isset($_GET['enddate']) ? $_GET['enddate']: date('Y-m-d', strtotime("TODAY + 1 MONTH"));



$currentPage = $_SERVER["PHP_SELF"];



mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsDirectory = "SELECT ID, name FROM directory WHERE statusID = 1 ORDER BY name ASC";
$rsDirectory = mysql_query($query_rsDirectory, $aquiescedb) or die(mysql_error());
$row_rsDirectory = mysql_fetch_assoc($rsDirectory);
$totalRows_rsDirectory = mysql_num_rows($rsDirectory);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsNoteTypes = "SELECT ID, typename FROM communicationtype WHERE statusID = 1 ORDER BY typename ASC";
$rsNoteTypes = mysql_query($query_rsNoteTypes, $aquiescedb) or die(mysql_error());
$row_rsNoteTypes = mysql_fetch_assoc($rsNoteTypes);
$totalRows_rsNoteTypes = mysql_num_rows($rsNoteTypes);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsCategories = "SELECT ID, categoryname FROM communicationcategory ORDER BY categoryname ASC";
$rsCategories = mysql_query($query_rsCategories, $aquiescedb) or die(mysql_error());
$row_rsCategories = mysql_fetch_assoc($rsCategories);
$totalRows_rsCategories = mysql_num_rows($rsCategories);

?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Manage Notes"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<style><!--
<?php if ($totalRows_rsNoteTypes==0) { 
 echo ".communicationtype { display:none; }";
} 

if ($totalRows_rsCategories==0) { 
 echo ".communicationcategory { display:none; }";
}
?>
--></style>
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
    <div class="page forum"><h1><i class="glyphicon glyphicon-envelope"></i> Manage  Notes</h1>
    <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
    
      <li class="nav-item"><a href="options/index.php" class="nav-link"><i class="glyphicon glyphicon-cog"></i> Options</a></li>
    </ul></div></nav><form action="index.php" method="get" name="searchform" id="searchform"><fieldset class="form-inline"><legend>Filter</legend>
    Search notes for: 
        <input name="search" type="text" size="20" maxlength="20" value="<?php echo isset($_GET['search']) ? htmlentities($_GET['search']) : "";  ?>"  class="form-control" /><?php if($totalRows_rsDirectory>0) { ?>
at 
<select name="directoryID" id="directoryID"  class="form-control">
  <option value="0" <?php if (!(strcmp(0, @$_GET['directoryID']))) {echo "selected=\"selected\"";} ?>>All clients</option>
  <?php
do {  
?>
  <option value="<?php echo $row_rsDirectory['ID']?>"<?php if (!(strcmp($row_rsDirectory['ID'], @$_GET['directoryID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsDirectory['name']?></option>
  <?php
} while ($row_rsDirectory = mysql_fetch_assoc($rsDirectory));
  $rows = mysql_num_rows($rsDirectory);
  if($rows > 0) {
      mysql_data_seek($rsDirectory, 0);
	  $row_rsDirectory = mysql_fetch_assoc($rsDirectory);
  }
?>
</select>
<?php } ?><label class="communicationtype">
<select name="commtypeID" id="commtypeID"  class="form-control">
  <option value="0" <?php if (!(strcmp(0, @$_GET['commtypeID']))) {echo "selected=\"selected\"";} ?>>All types</option>
  <?php if ($totalRows_rsNoteTypes>0) {
do {  
?>
  <option value="<?php echo $row_rsNoteTypes['ID']?>"<?php if (!(strcmp($row_rsNoteTypes['ID'], @$_GET['commtypeID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsNoteTypes['typename']?></option>
  <?php
} while ($row_rsNoteTypes = mysql_fetch_assoc($rsNoteTypes));
  $rows = mysql_num_rows($rsNoteTypes);
  if($rows > 0) {
      mysql_data_seek($rsNoteTypes, 0);
	  $row_rsNoteTypes = mysql_fetch_assoc($rsNoteTypes);
  }
  }
?>
</select></label>
<label class="communicationcategory">
  <select name="commcatID" id="commcatID"  class="form-control">
    <option value="0" <?php if (!(strcmp(0, @$_GET['commcatID']))) {echo "selected=\"selected\"";} ?>>Any category</option>
    <?php if ($totalRows_rsCategories>0) { 
do {  
?>
    <option value="<?php echo $row_rsCategories['ID']?>"<?php if (!(strcmp($row_rsCategories['ID'], @$_GET['commcatID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsCategories['categoryname']?></option>
    <?php
} while ($row_rsCategories = mysql_fetch_assoc($rsCategories));
  $rows = mysql_num_rows($rsCategories);
  if($rows > 0) {
      mysql_data_seek($rsCategories, 0);
	  $row_rsCategories = mysql_fetch_assoc($rsCategories);
  }}
?>
  </select>
</label>
<br />
        between
<input name="startdate" id="startdate" type="hidden" value="<?php $setvalue = isset($_GET['startdate']) ? htmlentities($_GET['startdate']) : ""; echo $setvalue; $inputname = "startdate"; ?>" /><?php require('../../../core/includes/datetimeinput.inc.php'); ?> and <input name="enddate" id="enddate" type="hidden"  value="<?php $setvalue = isset($_GET['enddate']) ? htmlentities($_GET['enddate']) : ""; echo $setvalue; $inputname = "enddate"; ?>" /><?php require('../../../core/includes/datetimeinput.inc.php'); ?> <label><input name="followups" type="checkbox" value="1" onclick="this.form.submit()" <?php if(isset($_GET['followups'])) echo "checked = \"checked\""; ?> />Only follow ups</label>
<button type="submit" name="searchbutton" id="searchbutton" class="btn btn-default btn-secondary">Search...</button>
    </fieldset></form>
    <div id="noteslist">
<?php require_once('includes/notes.inc.php'); ?>
</div></div>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsDirectory);

mysql_free_result($rsNoteTypes);

mysql_free_result($rsCategories);
?>
