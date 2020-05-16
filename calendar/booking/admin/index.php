<?php require_once('../../../Connections/aquiescedb.php'); ?>
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

$maxRows_rsResources = 20;
$pageNum_rsResources = 0;
if (isset($_GET['pageNum_rsResources'])) {
  $pageNum_rsResources = $_GET['pageNum_rsResources'];
}
$startRow_rsResources = $pageNum_rsResources * $maxRows_rsResources;

$varLocationID_rsResources = "0";
if (isset($_GET['locationID'])) {
  $varLocationID_rsResources = $_GET['locationID'];
}
$varCategoryID_rsResources = "0";
if (isset($_GET['categoryID'])) {
  $varCategoryID_rsResources = $_GET['categoryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsResources = sprintf("SELECT bookingresource.ID, bookingresource.title, bookingresource.statusID, location.locationname, bookingcategory.`description` AS category FROM bookingresource LEFT JOIN location ON (bookingresource.locationID = location.ID) LEFT JOIN bookingcategory ON (bookingresource.categoryID = bookingcategory.ID) WHERE (bookingresource.locationID = %s OR %s IS NULL OR %s < 1 ) AND (bookingresource.categoryID = %s OR %s IS NULL OR %s < 1) AND bookingresource.statusID = 1 ORDER BY bookingresource.createddatetime DESC", GetSQLValueString($varLocationID_rsResources, "int"),GetSQLValueString($varLocationID_rsResources, "int"),GetSQLValueString($varLocationID_rsResources, "int"),GetSQLValueString($varCategoryID_rsResources, "int"),GetSQLValueString($varCategoryID_rsResources, "int"),GetSQLValueString($varCategoryID_rsResources, "int"));
$query_limit_rsResources = sprintf("%s LIMIT %d, %d", $query_rsResources, $startRow_rsResources, $maxRows_rsResources);
$rsResources = mysql_query($query_limit_rsResources, $aquiescedb) or die(mysql_error());
$row_rsResources = mysql_fetch_assoc($rsResources);

if (isset($_GET['totalRows_rsResources'])) {
  $totalRows_rsResources = $_GET['totalRows_rsResources'];
} else {
  $all_rsResources = mysql_query($query_rsResources);
  $totalRows_rsResources = mysql_num_rows($all_rsResources);
}
$totalPages_rsResources = ceil($totalRows_rsResources/$maxRows_rsResources)-1;

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLocations = "SELECT * FROM location WHERE active = 1 ORDER BY locationname ASC";
$rsLocations = mysql_query($query_rsLocations, $aquiescedb) or die(mysql_error());
$row_rsLocations = mysql_fetch_assoc($rsLocations);
$totalRows_rsLocations = mysql_num_rows($rsLocations);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsCategories = "SELECT * FROM bookingcategory WHERE statusID = 1 ORDER BY `description` ASC";
$rsCategories = mysql_query($query_rsCategories, $aquiescedb) or die(mysql_error());
$row_rsCategories = mysql_fetch_assoc($rsCategories);
$totalRows_rsCategories = mysql_num_rows($rsCategories);

$queryString_rsResources = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsResources") == false && 
        stristr($param, "totalRows_rsResources") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsResources = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsResources = sprintf("&totalRows_rsResources=%d%s", $totalRows_rsResources, $queryString_rsResources);
?><!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Bookings Manager"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" --><!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" --><div class="page calendar">  <?php if ($totalRows_rsCategories < 2 ) { // 1 or less categories so don't show that column ?>
  <style> .category { display:none; } </style><?php } ?> 
   <?php if ($totalRows_rsLocations < 2 ) { // 1 or less locations so don't show that column ?>
  <style> .location { display:none; } </style><?php } ?> 
      <h1>Bookings Manager</h1>
      <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
        <li><a href="add_resource.php" ><i class="glyphicon glyphicon-plus-sign"></i> Add Booking Resource</a></li>
        <li><a href="categories/index.php" class="link_manage"><i class="glyphicon glyphicon-cog"></i> Manage Categories</a></li><li><a href="../../../location/admin/index.php" class="link_manage"><i class="glyphicon glyphicon-cog"></i> Manage Locations</a></li>
        <li><a href="features/index.php" class="link_manage"><i class="glyphicon glyphicon-cog"></i> Manage Features</a></li>
        <li><a href="options/index.php" class="link_manage"><i class="glyphicon glyphicon-cog"></i> Options</a></li>
        
    </ul></div></nav>
<?php if ($totalRows_rsResources == 0) { // Show if recordset empty ?>
        <p>There are no resources in the system so far.</p>
        <?php } // Show if recordset empty ?>
<?php if ($totalRows_rsResources > 0) { // Show if recordset not empty ?>
        <form action="index.php" method="get" name="form1" id="form1" role="form">
          <p>
              <?php if ($totalRows_rsCategories > 0 || $totalRows_rsLocations > 0) { // Show if recordset not empty ?>
            Filter by:
            <?php if ($totalRows_rsCategories > 0) { // Show if recordset not empty ?>
              <select name="categoryID" id="categoryID">
                <option value="0" <?php if (!(strcmp(0, $_GET['categoryID']))) {echo "selected=\"selected\"";} ?>>All categories</option>
                <?php
do {  
?>
                <option value="<?php echo $row_rsCategories['ID']?>"<?php if (!(strcmp($row_rsCategories['ID'], $_GET['categoryID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsCategories['description']?></option>
                <?php
} while ($row_rsCategories = mysql_fetch_assoc($rsCategories));
  $rows = mysql_num_rows($rsCategories);
  if($rows > 0) {
      mysql_data_seek($rsCategories, 0);
	  $row_rsCategories = mysql_fetch_assoc($rsCategories);
  }
?>
              </select>
              <?php } // Show if recordset not empty ?>
              <?php if ($totalRows_rsLocations > 0) { // Show if recordset not empty ?>
            at
            <select name="locationID" id="locationID">
              <option value="0" <?php if (!(strcmp(0, $_GET['locationID']))) {echo "selected=\"selected\"";} ?>>all locations</option>
              <?php
do {  
?>
              <option value="<?php echo $row_rsLocations['ID']?>"<?php if (!(strcmp($row_rsLocations['ID'], $_GET['locationID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsLocations['locationname']?></option>
              <?php
} while ($row_rsLocations = mysql_fetch_assoc($rsLocations));
  $rows = mysql_num_rows($rsLocations);
  if($rows > 0) {
      mysql_data_seek($rsLocations, 0);
	  $row_rsLocations = mysql_fetch_assoc($rsLocations);
  }
?>
            </select>
            <?php } // Show if recordset not empty ?>
            <input name="go" type="submit" class="button" id="go" value="Go" />
            <?php } // Show if recordset not empty ?>
          </p>
        </form><p>Resources <?php echo ($startRow_rsResources + 1) ?> to <?php echo min($startRow_rsResources + $maxRows_rsResources, $totalRows_rsResources) ?> of <?php echo $totalRows_rsResources ?></p>
      <table border="0" cellpadding="0" cellspacing="0" class="listTable">
         
              <tr>
                <td>&nbsp;</td>
                <td><strong>Resource</strong></td>
                <td class="category"><strong>Category</strong></td>
                <td class="location"><strong>Location</strong></td>
                <td><strong>View</strong></td>
              </tr> <?php do { ?>
          <tr>
        <td><?php if($row_rsResources['statusID']==1) { ?>
          <img src="../../../core/images/icons/green-light.png" alt="Active Resource" width="16" height="16" style="vertical-align:
middle;" />
          <?php } else { ?>
          <img src="../../../core/images/icons/red-light.png" alt="Inactive Resource" width="16" height="16" style="vertical-align:
middle;" />          <?php } ?></td>
        <td><a href="update_resource.php?resourceID=<?php echo $row_rsResources['ID']; ?>"><?php echo $row_rsResources['title']; ?></a></td>
        <td class="category"><em><?php echo $row_rsResources['category']; ?></em></td>
        <td class="location"><em><?php echo $row_rsResources['locationname']; ?></em></td>
        <td><a href="update_resource.php?resourceID=<?php echo $row_rsResources['ID']; ?>" class="link_edit icon_only">Edit</a></td>
        </tr>
      <?php } while ($row_rsResources = mysql_fetch_assoc($rsResources)); ?>
      </table>
  <?php } // Show if recordset not empty ?>
<table class="form-table">
        <tr>
          <td><?php if ($pageNum_rsResources > 0) { // Show if not first page ?>
              <a href="<?php printf("%s?pageNum_rsResources=%d%s", $currentPage, 0, $queryString_rsResources); ?>">First</a>
              <?php } // Show if not first page ?>          </td>
          <td><?php if ($pageNum_rsResources > 0) { // Show if not first page ?>
              <a href="<?php printf("%s?pageNum_rsResources=%d%s", $currentPage, max(0, $pageNum_rsResources - 1), $queryString_rsResources); ?>" rel="prev">Previous</a>
              <?php } // Show if not first page ?>          </td>
          <td><?php if ($pageNum_rsResources < $totalPages_rsResources) { // Show if not last page ?>
              <a href="<?php printf("%s?pageNum_rsResources=%d%s", $currentPage, min($totalPages_rsResources, $pageNum_rsResources + 1), $queryString_rsResources); ?>" rel="next">Next</a>
              <?php } // Show if not last page ?>          </td>
          <td><?php if ($pageNum_rsResources < $totalPages_rsResources) { // Show if not last page ?>
              <a href="<?php printf("%s?pageNum_rsResources=%d%s", $currentPage, $totalPages_rsResources, $queryString_rsResources); ?>">Last</a>
              <?php } // Show if not last page ?>          </td>
        </tr>
      </table>
      </p></div>
<!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsResources);

mysql_free_result($rsLocations);

mysql_free_result($rsCategories);
?>