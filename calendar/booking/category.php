<?php require_once('../../Connections/aquiescedb.php'); ?><?php
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

$varCapacityType_rsResources = "capacitystanding";
if (isset($_GET['capacitytype'])) {
  $varCapacityType_rsResources = $_GET['capacitytype'];
}
$varCapacity_rsResources = "0";
if (isset($_GET['capacity'])) {
  $varCapacity_rsResources = $_GET['capacity'];
}
$varLocationCatID_rsResources = "-1";
if (isset($_GET['locationCatID'])) {
  $varLocationCatID_rsResources = $_GET['locationCatID'];
}
$varCategoryID_rsResources = "0";
if (isset($_GET['categoryID'])) {
  $varCategoryID_rsResources = $_GET['categoryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsResources = sprintf("SELECT bookingresource.ID, bookingresource.title, bookingresource.statusID, location.ID AS locationID, location.locationname, bookingcategory.`description` AS category, locationcategory.categoryname FROM bookingresource LEFT JOIN bookingcategory ON (bookingresource.categoryID = bookingcategory.ID) LEFT JOIN location ON (bookingresource.locationID = location.ID) LEFT JOIN locationcategory ON (location.categoryID= locationcategory.ID) WHERE (location.categoryID = %s OR %s IS NULL OR %s < 1 ) AND (bookingresource.categoryID = %s OR %s IS NULL OR %s < 1 OR bookingresource.categoryID = 0) AND (bookingresource.%s >= %s OR %s < 1 OR %s IS NULL) AND bookingresource.statusID = 1 ORDER BY bookingresource.createddatetime DESC", GetSQLValueString($varLocationCatID_rsResources, "int"),GetSQLValueString($varLocationCatID_rsResources, "int"),GetSQLValueString($varLocationCatID_rsResources, "int"),GetSQLValueString($varCategoryID_rsResources, "int"),GetSQLValueString($varCategoryID_rsResources, "int"),GetSQLValueString($varCategoryID_rsResources, "int"),$varCapacityType_rsResources,GetSQLValueString($varCapacity_rsResources, "int"),GetSQLValueString($varCapacity_rsResources, "int"),GetSQLValueString($varCapacity_rsResources, "int"));
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

$colname_rsThisCategory = "-1";
if (isset($_GET['categoryID'])) {
  $colname_rsThisCategory = $_GET['categoryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsThisCategory = sprintf("SELECT `description` FROM bookingcategory WHERE ID = %s", GetSQLValueString($colname_rsThisCategory, "int"));
$rsThisCategory = mysql_query($query_rsThisCategory, $aquiescedb) or die(mysql_error());
$row_rsThisCategory = mysql_fetch_assoc($rsThisCategory);
$totalRows_rsThisCategory = mysql_num_rows($rsThisCategory);

$varCategoryID_rsFeatures = "-1";
if (isset($_GET['categoryID'])) {
  $varCategoryID_rsFeatures = $_GET['categoryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsFeatures = sprintf("SELECT ID, featurename FROM bookingfeature WHERE bookingfeature.categoryID = 0 OR bookingfeature.categoryID = %s ORDER BY featurename ASC", GetSQLValueString($varCategoryID_rsFeatures, "int"));
$rsFeatures = mysql_query($query_rsFeatures, $aquiescedb) or die(mysql_error());
$row_rsFeatures = mysql_fetch_assoc($rsFeatures);
$totalRows_rsFeatures = mysql_num_rows($rsFeatures);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLocationCategories = "SELECT ID, categoryname FROM locationcategory WHERE statusID = 1 ORDER BY categoryname ASC";
$rsLocationCategories = mysql_query($query_rsLocationCategories, $aquiescedb) or die(mysql_error());
$row_rsLocationCategories = mysql_fetch_assoc($rsLocationCategories);
$totalRows_rsLocationCategories = mysql_num_rows($rsLocationCategories);

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
?><?php if ($totalRows_rsResources == 1 && !isset($_GET['search'])) { // only one resource and not a search result so go straight to month page
header("location: month.php?resourceID=".$row_rsResources['ID']); exit; } ?><?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Booking ".$row_rsThisCategory['description']; echo $pageTitle." | ".$site_name; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" --><!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" -->
     <div class="crumbs"><div>You are in: <a href="../index.php">Home</a><span class="separator">&nbsp;&rsaquo;&nbsp;</span><a href="index.php">Booking</a><span class="separator">&nbsp;&rsaquo;&nbsp;</span><?php echo $row_rsThisCategory['description']; ?></div></div>
    <h1>Booking <?php echo $row_rsThisCategory['description']; ?></h1>
 <?php if ($totalRows_rsLocations < 2 ) { // 1 or less locations so don't show that column ?>
  <style> .location { display:none; } </style><?php } ?>
  <?php if ($totalRows_rsResources == 0 && !isset($_GET['search'])) { // no resources in category and not a search result so go straight to month page
  ?><p>There are no bookable resources.</p><?php } else { // bookable resources ?>
  <?php if (isset($bookingsearch) && $bookingsearch == true) { // show if search available ?>
  
  <form action="category.php" method="get" name="form1" id="form1" role="form">
    <fieldset><h2>I require:</h2>
    <p><?php $features = $_GET['feature']; do { ?>

     <label style="display:inline; white-space:nowrap;"><input name="feature[<?php $featureID = $row_rsFeatures['ID']; echo $featureID; ?>]" type="checkbox" id="feature[<?php echo $featureID; ?>]" value="1" <?php if(isset($features[$featureID])) { echo "checked=\"checked\""; } ?> />
    <?php echo $row_rsFeatures['featurename']; ?></label>
    
    
    <?php } while ($row_rsFeatures = mysql_fetch_assoc($rsFeatures)); ?>
</p>
<p>Search
  
  <?php if ($totalRows_rsLocationCategories > 1) {  ?>
  <select name="locationCatID" id="locationCatID">
          <option value="0" <?php if (!(strcmp(0, $_GET['locationCatID']))) {echo "selected=\"selected\"";} ?>>All areas</option>
          <?php
do {  
?>
          <option value="<?php echo $row_rsLocationCategories['ID']?>"<?php if (!(strcmp($row_rsLocationCategories['ID'], $_GET['locationCatID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsLocationCategories['categoryname']?></option>
          <?php
} while ($row_rsLocationCategories = mysql_fetch_assoc($rsLocationCategories));
  $rows = mysql_num_rows($rsLocationCategories);
  if($rows > 0) {
      mysql_data_seek($rsLocationCategories, 0);
	  $row_rsLocationCategories = mysql_fetch_assoc($rsLocationCategories);
  }
?>
        </select> 
    <?php } ?>for 
    <input name="capacity" type="text"  id="capacity" value="<?php echo htmlentities($_GET['capacity'], ENT_COMPAT, "UTF-8"); ?>" size="5" maxlength="5" />
    people 
    <select name="capacitytype" id="capacitytype">
      <option value="capacitystanding" <?php if (!(strcmp("capacitystanding", $_GET['capacitytype']))) {echo "selected=\"selected\"";} ?>>Standing</option>
      <option value="capacitytheatre" <?php if (!(strcmp("capacitytheatre", $_GET['capacitytype']))) {echo "selected=\"selected\"";} ?>>Theatre</option>
      <option value="capacityclassroom" <?php if (!(strcmp("capacityclassroom", $_GET['capacitytype']))) {echo "selected=\"selected\"";} ?>>Classroom</option>
      <option value="capacityboardroom" <?php if (!(strcmp("capacityboardroom", $_GET['capacitytype']))) {echo "selected=\"selected\"";} ?>>Boardroom</option>
      <option value="capacitybanquet" <?php if (!(strcmp("capacitybanquet", $_GET['capacitytype']))) {echo "selected=\"selected\"";} ?>>Banqueting</option>
    </select>
    <input name="categoryID" type="hidden" id="categoryID" value="<?php echo intval($_GET['categoryID']); ?>" />

    <input name="search" type="submit" class="button" id="search" value="Search" />
</p></fieldset>
  </form><?php } // end booking search 
  if (!isset($bookingsearch) || isset($_GET['search'])) { //no booking search or search made ?>
  <?php if ($totalRows_rsResources > 0) { // Show if recordset not empty ?>
    <table border="0" cellpadding="0" cellspacing="0" class="listTable">
      
        <tr>
              <td class="location"><strong>Location</strong></td>
          <td><strong>Resource</strong></td>
                   
                <td><strong>Book</strong></td>
          </tr> 
      <?php $nummatches =0; do { ?>
        <?php $nomatch = "";// calculate if there are matching features
		if (isset($_GET['feature'])) {		
		foreach($_GET['feature'] as $featureID => $value) {
		$query = "SELECT ID FROM bookingresourcefeature WHERE resourceID = ".intval($row_rsResources['ID'])." AND featureID = ".intval($featureID);
		$result = mysql_query($query, $aquiescedb) or die(mysql_error());
		$nomatch .= (mysql_num_rows($result) == 0) ? "1" : ""; // if there is a requirement not matched set 1
		}}
		if ($nomatch == "") { $nummatches ++; //show row if no missmatches - i.e. fulfills search requirements ?>
        
        <tr>
          
          
          <td class="location"><a href="../location/location.php?locationID=<?php echo $row_rsResources['locationID']; ?>"><?php echo $row_rsResources['locationname']; ?></a> <?php echo (isset($row_rsResources['categoryname'])) ? " (".$row_rsResources['categoryname'].")" : ""; ?></td>
          <td><?php echo $row_rsResources['title']; ?></td>
          <td align="center"><a href="month.php?resourceID=<?php echo $row_rsResources['ID']; ?>"><img src="../core/images/icons/date.png" alt="Book this resource" width="16" height="16" class="img" style="vertical-align:
middle;" /></a></td>
        </tr>
        <?php } // end match ?>
          <?php } while ($row_rsResources = mysql_fetch_assoc($rsResources)); ?> 
    </table>
    <?php } // Show if recordset not empty ?>
<?php if ($nummatches == 0 || $totalRows_rsResources == 0) { ?><p>No matches found. Please try another search.</p>
      <?php } ?>
     
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
     <?php } // end results ?>
     <?php } // end bookable resources ?>
<!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsResources);

mysql_free_result($rsLocations);

mysql_free_result($rsThisCategory);

mysql_free_result($rsFeatures);

mysql_free_result($rsLocationCategories);
?>
