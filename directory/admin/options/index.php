<?php require_once('../../../Connections/aquiescedb.php'); ?><?php require_once('../../../core/includes/framework.inc.php'); ?><?php require_once('../../../core/includes/adminAccess.inc.php'); ?>
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

$MM_restrictGoTo = "../../../login/index.php?notloggedin=true";
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
?><?php
$currentPage = $_SERVER["PHP_SELF"];

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


if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form2")) {
  $updateSQL = sprintf("UPDATE directoryprefs SET allowsuggestions=%s, approveupdates=%s, directoryname=%s, contactform=%s, managedirectoryURL=%s, accesslevel=%s, showcontacts=%s WHERE ID=%s",
                       GetSQLValueString(isset($_POST['directorysuggest']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['directoryapprove']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['directoryname'], "text"),
                       GetSQLValueString(isset($_POST['contactform']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['managedirectoryURL'], "text"),
                       GetSQLValueString($_POST['accesslevel'], "int"),
                       GetSQLValueString(isset($_POST['showcontacts']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
}


$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID, regionID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

// set region parameters depending on access level
if ($_SESSION['MM_UserGroup']<9) { $regionID = isset($row_rsLoggedIn['regionID']) ? $row_rsLoggedIn['regionID'] : 1; } else { $regionID = isset($_GET['regionID']) ? $_GET['regionID'] : 0; } 

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = "SELECT  useregions FROM preferences";
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsRegions = "SELECT ID, title FROM region WHERE statusID = 1 ORDER BY title ASC";
$rsRegions = mysql_query($query_rsRegions, $aquiescedb) or die(mysql_error());
$row_rsRegions = mysql_fetch_assoc($rsRegions);
$totalRows_rsRegions = mysql_num_rows($rsRegions);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsDirectoryPrefs = "SELECT * FROM directoryprefs WHERE ID = ".$regionID;
$rsDirectoryPrefs = mysql_query($query_rsDirectoryPrefs, $aquiescedb) or die(mysql_error());
$row_rsDirectoryPrefs = mysql_fetch_assoc($rsDirectoryPrefs);
$totalRows_rsDirectoryPrefs = mysql_num_rows($rsDirectoryPrefs);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsUserTypes = "SELECT * FROM usertype WHERE ID > 0 ORDER BY ID ASC";
$rsUserTypes = mysql_query($query_rsUserTypes, $aquiescedb) or die(mysql_error());
$row_rsUserTypes = mysql_fetch_assoc($rsUserTypes);
$totalRows_rsUserTypes = mysql_num_rows($rsUserTypes);

if($totalRows_rsDirectoryPrefs==0) {
	mysql_query("INSERT INTO directoryprefs (ID) VALUES (".$regionID.")", $aquiescedb) or die(mysql_error());
	header("location: index.php"); exit;
}

?><!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Directory Options"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->

<?php if ($row_rsPreferences['useregions'] !=1 || $_SESSION['MM_UserGroup'] <9) { // region menu hidden if less than manager of turned off ?>
<style>.region { display:none; } </style>
<?php } ?>

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
<div class="page directory"><?php require_once('../../../core/region/includes/chooseregion.inc.php'); ?>
      <h1><i class="glyphicon glyphicon-book"></i> Directory Options</h1>
      <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
        <li><a href="../index.php" class="link_undo"><i class="glyphicon glyphicon-arrow-left"></i> Back to Directory Manager</a></li>
        <li><a href="import.php" class="link_undo"><i class="glyphicon glyphicon-arrow-left"></i> Import</a></li>
      </ul></div></nav>
      <form action="<?php echo $editFormAction; ?>" method="post" name="form2" id="form2" class="form-inline">
        <p>
          <label>
            Directory name: 
            <input name="directoryname" type="text"  id="directoryname" value="<?php echo $row_rsDirectoryPrefs['directoryname']; ?>" size="50" maxlength="50"  class="form-control" /></label>
            <br /><label>
<input <?php if (!(strcmp($row_rsDirectoryPrefs['contactform'],1))) {echo "checked=\"checked\"";} ?> name="contactform" type="checkbox" id="contactform" value="1" />
            Online contact form</label><br />
            
            <label>
<input <?php if (!(strcmp($row_rsDirectoryPrefs['showcontacts'],1))) {echo "checked=\"checked\"";} ?> name="showcontacts" type="checkbox" id="showcontacts" value="1" />Show contacts (if the users allow)</label><br />
            
            
 <label><input <?php if (!(strcmp($row_rsDirectoryPrefs['allowsuggestions'],1))) {echo "checked=\"checked\"";} ?> name="directorysuggest" type="checkbox" id="directorysuggest"  value="1"/>
            Allow suggestions for the directory</label><br />
         
          <label id="directoryapprovetext"><input <?php if (!(strcmp($row_rsDirectoryPrefs['approveupdates'],1))) {echo "checked=\"checked\"";} ?> name="directoryapprove" type="checkbox" id="directoryapprove"  value="1"  />
            Approve directory submittals before they show on site</label>
          <input name="ID" type="hidden" id="ID" value="1" />
          <input type="hidden" name="MM_update" value="form2" />
        </p>
        <p><label>Access level: 
          
            <select name="accesslevel" id="accesslevel" class="form-control" >
              <option value="0" <?php if (!(strcmp(0, $row_rsDirectoryPrefs['accesslevel']))) {echo "selected=\"selected\"";} ?>>Everyone</option>
              <?php
do {  
?>
              <option value="<?php echo $row_rsUserTypes['ID']?>"<?php if (!(strcmp($row_rsUserTypes['ID'], $row_rsDirectoryPrefs['accesslevel']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsUserTypes['name']?></option>
              <?php
} while ($row_rsUserTypes = mysql_fetch_assoc($rsUserTypes));
  $rows = mysql_num_rows($rsUserTypes);
  if($rows > 0) {
      mysql_data_seek($rsUserTypes, 0);
	  $row_rsUserTypes = mysql_fetch_assoc($rsUserTypes);
  }
?>
            </select>
          </label>
        </p>
        <p>
          <label>Custom Manage Directory URL:
            <input name="managedirectoryURL" type="text" id="managedirectoryURL" value="<?php echo $row_rsDirectoryPrefs['managedirectoryURL']; ?>" size="50" maxlength="50"  class="form-control" />
          </label>
        </p>
        <p>
         
            <button type="submit" name="save" id="save" class="btn btn-primary" >Save changes</button>
         
        </p>
      </form>
     
<form action="csv.php" method="get">

<fieldset class="form-inline"><legend>Export</legend>
  <label>
    <select name="regionID" id="regionID" class="form-control" >
      <option value="0">All sites</option>
      <?php
do {  
?>
      <option value="<?php echo $row_rsRegions['ID']?>"><?php echo $row_rsRegions['title']?></option>
      <?php
} while ($row_rsRegions = mysql_fetch_assoc($rsRegions));
  $rows = mysql_num_rows($rsRegions);
  if($rows > 0) {
      mysql_data_seek($rsRegions, 0);
	  $row_rsRegions = mysql_fetch_assoc($rsRegions);
  }
?>
    </select>
  </label>
  <button  type="submit" class="btn btn-default btn-secondary" >Export as CSV</button></fieldset>

</form>
<p><a href="/location/admin/options/location_to_map.php?directory=true">Add non-mapped locations to map</a></p>
<?php if ($_SESSION['MM_UserGroup'] == 10) { ?>
    <p><a href="../updatedirectoryusertables.php">Update directory tables to latest version</a></p>
    <?php } ?></div>
   <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsPreferences);

mysql_free_result($rsRegions);

mysql_free_result($rsDirectoryPrefs);

mysql_free_result($rsUserTypes);
?>
