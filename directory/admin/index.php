<?php require_once('../../Connections/aquiescedb.php'); ?><?php require_once('../../core/includes/adminAccess.inc.php'); ?>
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

$MM_restrictGoTo = "../../login/index.php?notloggedin=true";
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

// set region parameters depending on access level
if ($_SESSION['MM_UserGroup']<9) { $regionID = (isset($row_rsLoggedIn['regionID']) && $row_rsLoggedIn['regionID']>=0) ? $row_rsLoggedIn['regionID'] : 1; } else { $regionID = isset($_GET['regionID']) ? $_GET['regionID'] : 0; } 



$varRegionID_rsCategories = "0";
if (isset($regionID)) {
  $varRegionID_rsCategories = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsCategories = sprintf("SELECT ID, `description` FROM directorycategory WHERE statusID = 1 AND (regionID=0 OR regionID = %s OR %s = 0) ORDER BY `description` ASC", GetSQLValueString($varRegionID_rsCategories, "int"),GetSQLValueString($varRegionID_rsCategories, "int"));
$rsCategories = mysql_query($query_rsCategories, $aquiescedb) or die(mysql_error());
$row_rsCategories = mysql_fetch_assoc($rsCategories);
$totalRows_rsCategories = mysql_num_rows($rsCategories);


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
$query_rsDirectoryPrefs = "SELECT * FROM directoryprefs";
$rsDirectoryPrefs = mysql_query($query_rsDirectoryPrefs, $aquiescedb) or die(mysql_error());
$row_rsDirectoryPrefs = mysql_fetch_assoc($rsDirectoryPrefs);
$totalRows_rsDirectoryPrefs = mysql_num_rows($rsDirectoryPrefs);

?>
<!doctype html>

<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Directory Category"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<style><!--
<?php if ($row_rsPreferences['useregions'] !=1 || $_SESSION['MM_UserGroup'] <9) { echo ".region { display:none; }"; } ?>
--></style>
<script src="/core/scripts/liveSearch.js"></script>
<script>
var liveSearchURL = "/directory/admin/ajax/directory.inc.php";


    // When the document is ready set up our sortable with it's inherant function(s) 
    $(document).ready(function() { 
	<?php if(isset($_GET['categoryID']) && $_GET['categoryID']>0) { $draganddrop = true;?>
        $(".sortable").sortable({ 
            handle : '.handle', 
            update : function () { 
            var order = $(this).sortable('serialize'); 
                $("#info").load("/core/ajax/sort.ajax.php?table=directory&"+order); 
            } 
        }); 
		<?php } ?>
    }); 
</script>
<style><!--
<?php if(!$draganddrop) echo ".draganddrop { display: none !important; } "; ?>
--></style>
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" -->
<div class="page directory">      <h1><i class="glyphicon glyphicon-book"></i>  <?php echo $row_rsDirectoryPrefs['directoryname']; ?> Manager</h1>

       
          <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
            <li class="nav-item"><a href="add_directory.php<?php echo isset($_GET['categoryID']) ? "?categoryID=".intval($_GET['categoryID']) : ""; ?>" class="nav-link"><i class="glyphicon glyphicon-plus-sign"></i> Add an entry</a></li>
            <li  class="nav-item"id="linkDirectoryCategories"><a href="category/index.php" class="nav-link"><i class="glyphicon glyphicon-tags"></i> Manage Categories</a></li>
            <li class="nav-item"><a href="areas/index.php" class="nav-link"><i class="glyphicon glyphicon-screenshot"></i> Directory Areas</a></li>
             <li  class="nav-item"><a href="/location/admin/" class="nav-link"><i class="glyphicon glyphicon-flag"></i> Locations</a></li>
            <li class="nav-item"><a href="relationship/index.php" class="nav-link"><i class="glyphicon glyphicon-user"></i> Relationships</a></li>
            <li  class="nav-item"id="linkDirectoryMerge"><a href="merge/index.php" class="nav-link"> Merge Entries</a></li>
            <li  class="nav-item"id="linkDirectoryMerge"><a href="options/index.php" class="nav-link"><i class="glyphicon glyphicon-cog"></i> Options</a></li>
          </ul></div></nav>
          <form action="index.php" method="get"><fieldset class="form-inline"><legend>Filter</legend>

Name: 
    <input name="search" type="text"  id="search" value="<?php echo isset($_GET['search']) ? htmlentities($_GET['search'], ENT_COMPAT, "UTF-8") : "";  ?>" size="30" maxlength="30"  class="form-control"/> <select name="regionID" id="regionID"  class="form-control region" onChange="this.form.submit()">
  <option value="0" <?php if (!(strcmp(0, @$_GET['regionID']))) {echo "selected=\"selected\"";} ?>>All sites</option>
  <?php
do {  
?><option value="<?php echo $row_rsRegions['ID']?>"<?php if (!(strcmp($row_rsRegions['ID'], @$_GET['regionID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsRegions['title']?></option>
  <?php
} while ($row_rsRegions = mysql_fetch_assoc($rsRegions));
  $rows = mysql_num_rows($rsRegions);
  if($rows > 0) {
      mysql_data_seek($rsRegions, 0);
	  $row_rsRegions = mysql_fetch_assoc($rsRegions);
  }
?>
            </select> 
          <select name="categoryID" id="categoryID"  class="form-control" onChange="this.form.submit()">
            <option value="0" <?php if (!(strcmp(0, @$_GET['categoryID']))) {echo "selected=\"selected\"";} ?>>All categories</option>
            <?php
do {  
?>
            <option value="<?php echo $row_rsCategories['ID']?>"<?php if (!(strcmp($row_rsCategories['ID'], @$_GET['categoryID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsCategories['description']?></option>
            <?php
} while ($row_rsCategories = mysql_fetch_assoc($rsCategories));
  $rows = mysql_num_rows($rsCategories);
  if($rows > 0) {
      mysql_data_seek($rsCategories, 0);
	  $row_rsCategories = mysql_fetch_assoc($rsCategories);
  }
?>
          </select> 
          <button name="go" type="submit" class="btn btn-default btn-secondary" >Go</button></fieldset>
</form> <div id="liveSearchDIV">
<?php require_once('ajax/directory.inc.php'); ?></div></div>
    
  <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsCategories);

mysql_free_result($rsPreferences);

mysql_free_result($rsRegions);

?>
