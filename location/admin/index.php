<?php require_once('../../Connections/aquiescedb.php'); ?><?php require_once('../../core/includes/adminAccess.inc.php'); ?>
<?php 
if(is_readable('../../directory/includes/directoryfunctions.inc.php')) {
	require_once('../../directory/includes/directoryfunctions.inc.php'); 
	
}?>
<?php  
$_GET['categoryID'] = isset($_GET['categoryID']) ? intval($_GET['categoryID']) : 0; 

if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "10,9,8";
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

if(isset($_POST['directoryID']) && isset($_POST['location']) && is_array($_POST['location']) && function_exists("addLocationToDirectory")) {
	$count = 0;
	foreach($_POST['location'] as $locationID=>$value) {
		
		if(addLocationToDirectory($_POST['directoryID'], $locationID,$_POST['createdbyID'])) {
			$count ++;
		}
		
	}
	$msg = $count." locations were updated.";
	
}

$currentPage = $_SERVER["PHP_SELF"];

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

$categoryID = isset($_GET['categoryID']) ? $_GET['categoryID'] : 0;
switch($_GET['orderby']) {
	case "dateadded" : $orderby = "location.createddatetime DESC";	break;
	case "category" : $orderby = "locationcategory.categoryname ASC, location.locationname ASC"; break;
	default: $orderby = "location.locationname ASC"; 
}

$maxRows_rsLocations = 200;
$pageNum_rsLocations = 0;
if (isset($_GET['pageNum_rsLocations'])) {
  $pageNum_rsLocations = $_GET['pageNum_rsLocations'];
}
$startRow_rsLocations = $pageNum_rsLocations * $maxRows_rsLocations;

$varCategoryID_rsLocations = "0";
if (isset($_GET['categoryID'])) {
  $varCategoryID_rsLocations = $_GET['categoryID'];
}
$varPrivate_rsLocations = "0";
if (isset($_GET['private'])) {
  $varPrivate_rsLocations = $_GET['private'];
}
$varSearch_rsLocations = "%";
if (isset($_GET['locationsearch'])) {
  $varSearch_rsLocations = $_GET['locationsearch'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLocations = sprintf("SELECT location.ID, locationname, location.active AS statusID, locationcategory.categoryname, location.address1, location.address2,  location.address3, location.address4, location.address5, location.telephone1,  location.postcode, location.latitude, location.longitude, location.locationURL, location.public, directorylocation.directoryID, directory.name AS directoryname, COUNT(directory.ID) AS countdirectory FROM location LEFT JOIN locationcategory ON (location.categoryID = locationcategory.ID) LEFT JOIN directorylocation ON (location.ID = directorylocation.locationID) LEFT JOIN directory ON (directory.ID = directorylocation.directoryID) WHERE (location.public = 1 OR %s = 1) AND  (location.categoryID=0 OR location.categoryID = %s OR %s = 0) AND (location.locationname LIKE %s OR location.address1 LIKE %s OR location.address2 LIKE %s OR location.postcode LIKE %s) GROUP BY location.ID ORDER BY ".$orderby." ", GetSQLValueString($varPrivate_rsLocations, "int"),GetSQLValueString($varCategoryID_rsLocations, "int"),GetSQLValueString($varCategoryID_rsLocations, "int"),GetSQLValueString($varSearch_rsLocations . "%", "text"),GetSQLValueString($varSearch_rsLocations . "%", "text"),GetSQLValueString($varSearch_rsLocations . "%", "text"),GetSQLValueString($varSearch_rsLocations . "%", "text"));
$query_limit_rsLocations = sprintf("%s LIMIT %d, %d", $query_rsLocations, $startRow_rsLocations, $maxRows_rsLocations);
$rsLocations = mysql_query($query_limit_rsLocations, $aquiescedb) or die(mysql_error());
$row_rsLocations = mysql_fetch_assoc($rsLocations);

if (isset($_GET['totalRows_rsLocations'])) {
  $totalRows_rsLocations = $_GET['totalRows_rsLocations'];
} else {
  $all_rsLocations = mysql_query($query_rsLocations);
  $totalRows_rsLocations = mysql_num_rows($all_rsLocations);
}
$totalPages_rsLocations = ceil($totalRows_rsLocations/$maxRows_rsLocations)-1;

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = "SELECT * FROM preferences";
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLocationCategories = "SELECT ID, categoryname FROM locationcategory WHERE statusID = statusID ORDER BY categoryname ASC";
$rsLocationCategories = mysql_query($query_rsLocationCategories, $aquiescedb) or die(mysql_error());
$row_rsLocationCategories = mysql_fetch_assoc($rsLocationCategories);
$totalRows_rsLocationCategories = mysql_num_rows($rsLocationCategories);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLocationPrefs = "SELECT * FROM locationprefs";
$rsLocationPrefs = mysql_query($query_rsLocationPrefs, $aquiescedb) or die(mysql_error());
$row_rsLocationPrefs = mysql_fetch_assoc($rsLocationPrefs);
$totalRows_rsLocationPrefs = mysql_num_rows($rsLocationPrefs);

$latitude = isset($row_rsLocations['latitude']) ? $row_rsLocations['latitude'] : @$_GET['latitude'];
$longitude = isset($row_rsLocations['longitude']) ? $row_rsLocations['longitude'] : @$_GET['longitude'];

$varLat_rsOtherLocations = "0";
if (isset($latitude)) {
  $varLat_rsOtherLocations = $latitude;
}
$varLong_rsOtherLocations = "0";
if (isset($longitude)) {
  $varLong_rsOtherLocations = $longitude;
}
$varRadius_rsOtherLocations = "0";
if (isset($_GET['radius'])) {
  $varRadius_rsOtherLocations = $_GET['radius'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsOtherLocations = sprintf("SELECT location.ID, 3956*2*ASIN(SQRT(POWER(SIN((location.latitude-%s)*pi()/180/2),2)+COS(location.latitude*pi()/180)*COS(%s*pi()/180)*POWER(SIN((location.longitude-%s)*pi()/180/2),2))) as distance FROM location  WHERE %s > 0 HAVING distance <= %s", GetSQLValueString($varLat_rsOtherLocations, "int"),GetSQLValueString($varLat_rsOtherLocations, "int"),GetSQLValueString($varLong_rsOtherLocations, "int"),GetSQLValueString($varRadius_rsOtherLocations, "int"),GetSQLValueString($varRadius_rsOtherLocations, "int"));
$rsOtherLocations = mysql_query($query_rsOtherLocations, $aquiescedb) or die(mysql_error());
$row_rsOtherLocations = mysql_fetch_assoc($rsOtherLocations);
$totalRows_rsOtherLocations = mysql_num_rows($rsOtherLocations);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsDirectory = "SELECT ID, name FROM directory ORDER BY name ASC";
$rsDirectory = mysql_query($query_rsDirectory, $aquiescedb) or die(mysql_error());
$row_rsDirectory = mysql_fetch_assoc($rsDirectory);
$totalRows_rsDirectory = mysql_num_rows($rsDirectory);

$queryString_rsLocations = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsLocations") == false && 
        stristr($param, "totalRows_rsLocations") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsLocations = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsLocations = sprintf("&totalRows_rsLocations=%d%s", $totalRows_rsLocations, $queryString_rsLocations);
?>
<!doctype html>
<!-- Web design by Paul Egan, Jim Campbell -->
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Manage ".ucwords($row_rsLocationPrefs['locationdescriptor'])."s";  echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<?php if(isset($row_rsPreferences['googlemapsAPI'])) {
	$javascript = "";

$latitude = isset($_GET['defaultlatitude']) ? $_GET['defaultlatitude'] : $row_rsPreferences['defaultlatitude'];
$longitude =  isset($_GET['defaultlongitude']) ? $_GET['defaultlongitude'] : $row_rsPreferences['defaultlongitude']; 
$magnification = isset($_GET['defaultzoom']) ? $_GET['defaultzoom'] : $row_rsPreferences['defaultzoom']; ?>
<script src="//maps.google.com/?file=api&v=2&key=<?php echo isset($googlemapsAPI) ? $googlemapsAPI : $row_rsPreferences['googlemapsAPI']; ?>&sensor=false"></script>
<script src="/core/scripts/googlemaps/googlemap.js" ></script>
<script src="/core/scripts/googlemaps/markermanager.js"></script>
<script src="/core/scripts/googlemaps/fb_maps.js"></script><script src="scripts/icon_defs.js"></script>

<script>
//<![CDATA[

function submitForm() {	
	postcode = document.getElementById('locationsearch').value;
	if(document.getElementById('showradius').checked && postcode !="") {
		if(typeof(localSearch) !== 'undefined') {
			usePointFromPostcode(postcode, setLatLong);
		}
	} else {
	
		//document.getElementById('searchloading').style.visibility = 'visible';
	document.getElementById('searchbutton').disabled = true;
	document.getElementById('searchform').submit();
	}
	
}

function setLatLong(setLong, setLat) {
	document.getElementById('latitude').value = setLat;
	document.getElementById('longitude').value = setLong;
	//document.getElementById('searchloading').style.visibility = 'visible';
	document.getElementById('searchbutton').disabled = true;
	document.getElementById('searchform').submit();
}



var map = null;
var geocoder = null;
var latsgn = 1;
var lgsgn = 1;
var zm = 0; 
var marker = null;

function init() {
	addListener("click",submitForm, document.getElementById('searchbutton'));
	if (GBrowserIsCompatible()) {
		map = new GMap2(document.getElementById("googlemap"));
		map.setCenter(new GLatLng(<?php echo $latitude; ?>, <?php echo $longitude; ?>), <?php echo $magnification; ?>);
		map.setMapType(G_NORMAL_MAP);zm=1;
		map.addControl(new GSmallMapControl());
		map.addControl(new MapTypeControl());
		map.addControl(new GScaleControl());
		map.enableScrollWheelZoom();
		map.disableDoubleClickZoom();
		geocoder = new GClientGeocoder();
		addManagerMarkers();
	}
}

<?php if($totalRows_rsLocations>0) { // are locations
	do {
	if(isset($row_rsLocations['latitude'])) { 
    $javascript .= "locationmarker".$row_rsLocations['ID']." = new GMarker(new GLatLng(".$row_rsLocations['latitude'].",".$row_rsLocations['longitude']."), {icon:redIcon});
GEvent.addListener(locationmarker".$row_rsLocations['ID'].", \"click\", function()
 {
 document.location='/location/admin/modify_location.php?locationID=".$row_rsLocations['ID']."';
 });
GEvent.addListener(locationmarker".$row_rsLocations['ID'].", \"mouseover\", function()
 {
	
 locationmarker".$row_rsLocations['ID'].".openInfoWindowHtml(\"".trim($row_rsLocations['locationname']." ".$row_rsLocations['address1']." ".$row_rsLocations['address2']." ".$row_rsLocations['address3']." ".$row_rsLocations['address4']." ".$row_rsLocations['address5'])."<br>".$row_rsLocations['postcode']."<br>".$row_rsLocations['telephone1']."<br>".$row_rsLocations['locationURL']."\");
 });
markers.push(locationmarker".$row_rsLocations['ID'].");";

 } ?>
  <?php } while ($row_rsLocations = mysql_fetch_assoc($rsLocations)); 
  mysql_data_seek($rsLocations,0); 
  $row_rsLocations = mysql_fetch_assoc($rsLocations);  
} // are locations ?>
 
addListener("load",init);

//]]>
</script>
<script>

function addManagerMarkers() { // Create our icons
var redIcon = new GIcon(G_DEFAULT_ICON);
redIcon.image = "/core/scripts/googlemaps/images/red-dot.png";
redIcon.iconSize=new GSize(32,32);
redIcon.shadow = "/core/scripts/googlemaps/images/msmarker.shadow.png";
redIcon.shadowSize = new GSize(59, 32);
redIcon.iconAnchor = new GPoint(16, 32);

<?php
 echo $javascript; ?>
 mgr = new MarkerManager(map);
 mgr.addMarkers(markers,1);
 mgr.refresh();}
 </script><?php } else { // no google maps
echo "<style> #googlemap { display:none; } </style>";
} ?>
<?php if(isset($row_rsPreferences['googlesearchAPI'])) { // load GoogleAjaxSearch ?>
<script src="//www.google.com/uds/api?file=uds.js&v=1.0&key=<?php echo $row_rsPreferences['googlesearchAPI']; ?>"></script>
<script>
var localSearch = new GlocalSearch();
</script>
<?php } ?>
<script src="../../SpryAssets/SpryValidationTextField.js"></script>
<link href="../../SpryAssets/SpryValidationTextField.css" rel="stylesheet"  />
<script src="/core/scripts/checkbox/checkboxes.js"></script><?php require_once(SITE_ROOT.'core/scripts/checkbox/checkboxsession.inc.php'); ?>
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
 <div class="page location">
          <h1><i class="glyphicon glyphicon-flag"></i> <?php echo ucwords($row_rsLocationPrefs['locationdescriptor']); ?>s Manager</h1><?php if ($row_rsPreferences['uselocationcategory']!=1) { ?><style>.categories { display:none !important; }</style><?php } ?>
          <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
            <li class="nav-item"><a href="add_location.php?categoryID=<?php echo $categoryID; ?>" class="nav-link"><i class="glyphicon glyphicon-plus-sign"></i> Add a <?php echo ucwords($row_rsLocationPrefs['locationdescriptor']); ?></a></li>
            <li class="nav-item categories"><a href="/location/admin/category/index.php"  class="nav-link"><i class="glyphicon glyphicon-tags"></i> Manage Categories</a></li>
            
            <li class="nav-item"><a href="merge/index.php" class="nav-link"><i class="glyphicon glyphicon-resize-small"></i> Merge <?php echo ucwords($row_rsLocationPrefs['locationdescriptor']); ?>s</a></li>
            <li class="nav-item"><a href="options/index.php" class="nav-link"><i class="glyphicon glyphicon-cog"></i> Options</a></li>
    </ul></div></nav><?php require_once('../../core/includes/alert.inc.php'); ?>
<form action="index.php" method="get" id="searchform"><fieldset class="form-inline"><legend>Search</legend>

        <input name="locationsearch" type="text" id="locationsearch" value="<?php echo isset($_REQUEST['locationsearch']) ? htmlentities($_REQUEST['locationsearch']) : ""; ?>" size="50" maxlength="50" placeholder="Search by postcode or location" class="form-control" />

        <select  class="form-control categories"  name="categoryID"  id="categoryID" >
  <option value="0" <?php if (!(strcmp(0, $_GET['categoryID']))) {echo "selected=\"selected\"";} ?>>All categories</option>
  <?php
do {  
?>
  <option value="<?php echo $row_rsLocationCategories['ID']?>"<?php if (!(strcmp($row_rsLocationCategories['ID'], $_GET['categoryID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsLocationCategories['categoryname']?></option>
  <?php
} while ($row_rsLocationCategories = mysql_fetch_assoc($rsLocationCategories));
  $rows = mysql_num_rows($rsLocationCategories);
  if($rows > 0) {
      mysql_data_seek($rsLocationCategories, 0);
	  $row_rsLocationCategories = mysql_fetch_assoc($rsLocationCategories);
  }
?>
</select>
  
    <button type="button" name="searchbutton" id="searchbutton" class="btn btn-default btn-secondary" >Find...</button>
 
    <label>
      <input type="checkbox" name="private" id="private" <?php if(isset($_GET['private'])) echo "checked"; ?> value="1">
      Show private addresses</label>
    
    
 <p>Order by : 
 
 <label><input name="orderby" type="radio" value="alphabetical" <?php if(!isset($_GET['orderby']) ||  $_GET['orderby']== "alphabetical") echo "checked"; ?> onClick="this.form.submit()"> Alphabetical</label> &nbsp;&nbsp;&nbsp; 
 
 <label><input name="orderby" type="radio" value="category" <?php if(isset($_GET['orderby']) && $_GET['orderby']== "category") echo "checked"; ?> onClick="this.form.submit()"> Category</label> &nbsp;&nbsp;&nbsp; 
 
 <label><input name="orderby" type="radio" value="dateadded" <?php if(isset($_GET['orderby']) && $_GET['orderby']== "dateadded") echo "checked"; ?> onClick="this.form.submit()"> Date added</label> &nbsp;&nbsp;&nbsp;
      <input type="hidden" name="latitude" id="latitude" />
      <input type="hidden" name="longitude" id="longitude" />
    
   
    <label>
      <input type="checkbox" name="showradius" id="showradius" <?php if(isset($_GET['showradius'])) { echo "checked = \"checked\""; } ?> />
      also show locations within a</label>
    <span id="sprytextfield2">
    <label>
      <input name="miles" type="text" id="miles" size="3" maxlength="3" class="form-control" value="<?php echo isset($_GET['radius']) ? intval($_GET['radius']) : 1; ?>" />
      mile radius</label>
    <span class="textfieldRequiredMsg">A value is required.</span><span class="textfieldInvalidFormatMsg">Invalid format.</span></span>
    </fieldset>
</form><div class="googlemap" id="googlemap"></div>
        <?php if ($totalRows_rsLocations == 0) { // Show if recordset empty ?>
            <p>There are no <?php echo ucwords($row_rsLocationPrefs['locationdescriptor']); ?>s matching your search.</p>
            <?php } // Show if recordset empty ?><?php if ($totalRows_rsLocations > 0) { // Show if recordset not empty ?>
            <p class="text-muted"><?php echo ucwords($row_rsLocationPrefs['locationdescriptor']); ?>s <?php echo ($startRow_rsLocations + 1) ?> to <?php echo min($startRow_rsLocations + $maxRows_rsLocations, $totalRows_rsLocations) ?> of <?php echo $totalRows_rsLocations ?> </p>
	     <form method="post">
		    <table class="table table-hover">
		      <tr><th><input type="checkbox" name="checkAll" id="checkAll" onClick="checkUncheckAll(this);" /></th>
		        <th>&nbsp;</th>
		        <th><?php echo ucwords($row_rsLocationPrefs['locationdescriptor']); ?></th>
		        <th>Public</th>
                <th class="categories">Category</th>
                <th class="directory">Directory</th>
                <th>Edit</th>
              </tr>
		      <?php do { ?>
	          <tr><td><input type="checkbox" name="location[<?php echo $row_rsLocations['ID']; ?>]" id="location<?php echo $row_rsLocations['ID']; ?>" value="<?php echo $row_rsLocations['ID']; ?>" /></td>
	            <td class="status<?php echo $row_rsLocations['statusID']; ?>">&nbsp;</td>
	            <td><?php echo isset($row_rsLocations['locationname']) ? $row_rsLocations['locationname'].", ".$row_rsLocations['address1'] : $row_rsLocations['address1']." ".$row_rsLocations['address2']." ".$row_rsLocations['postcode']; ?><?php echo (isset($row_rsLocations['locationURL'])) ? "<a href=\"".$row_rsLocations['locationURL']."\" target=\"_blank\"> &raquo;www</a>" : ""; ?>&nbsp;&nbsp;</td>
	            <td class="tick<?php echo $row_rsLocations['public']; ?>">&nbsp;</td>
	            <td class="categories"><em><?php echo (isset($row_rsLocations['categoryname']) && $row_rsLocations['categoryname'] != "") ? $row_rsLocations['categoryname'] : "<em>None</em>" ; ?></em></td>
                
                <td><?php if($row_rsLocations['countdirectory']) {
					echo  "<a href=\"/directory/admin/index.php?locationID=".$row_rsLocations['ID']."\">(".$row_rsLocations['countdirectory'].")</a>"; ?><?php echo $row_rsLocations['countdirectory']>1 ? " incl." : " "; echo "<a href=\"/directory/admin/update_directory.php?directoryID=".$row_rsLocations['directoryID']."\">".$row_rsLocations['directoryname']."</a>"; } ?></td>
                
                
	            <td><a href="modify_location.php?locationID=<?php echo $row_rsLocations['ID']; ?>&amp;categoryID=<?php echo $categoryID; ?>" class="btn btn-sm btn-default btn-secondary" ><i class="glyphicon glyphicon-pencil"></i> Edit</a></td>
	          </tr>
	          <?php } while ($row_rsLocations = mysql_fetch_assoc($rsLocations)); ?>
          </table>
            <?php if ($totalRows_rsDirectory > 0) { // Show if recordset not empty ?>
  <fieldset><legend>with selected</legend><label>Associate with: 
    <select name="directoryID" id="directoryID" class="form-control">
      <option value="">Choose...</option>
      <?php
do {  
?>
      <option value="<?php echo $row_rsDirectory['ID']?>"><?php echo $row_rsDirectory['name']?></option>
      <?php
} while ($row_rsDirectory = mysql_fetch_assoc($rsDirectory));
  $rows = mysql_num_rows($rsDirectory);
  if($rows > 0) {
      mysql_data_seek($rsDirectory, 0);
	  $row_rsDirectory = mysql_fetch_assoc($rsDirectory);
  }
?>
      </select></label>
    <button type="submit" class="btn btn-default btn-secondary"  name="button" id="button"  onClick="if(this.form.directoryID.value!='') { return confirm('Are you sure you want to link the selected locations with this directory entry?'); } else { alert('Please select a directory entry.'); return false; }">Go</button>
    <input name="createdbyID" type="hidden" id="createdbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>">
  </fieldset>
  <?php } // Show if recordset not empty ?>
         </form>
		    <?php } // Show if recordset not empty ?>
            <table class="form-table">
              <tr>
                <td><?php if ($pageNum_rsLocations > 0) { // Show if not first page ?>
                    <a href="<?php printf("%s?pageNum_rsLocations=%d%s", $currentPage, 0, $queryString_rsLocations); ?>">First</a>
                    <?php } // Show if not first page ?></td>
                <td><?php if ($pageNum_rsLocations > 0) { // Show if not first page ?>
                    <a href="<?php printf("%s?pageNum_rsLocations=%d%s", $currentPage, max(0, $pageNum_rsLocations - 1), $queryString_rsLocations); ?>">Previous</a>
                    <?php } // Show if not first page ?></td>
                <td><?php if ($pageNum_rsLocations < $totalPages_rsLocations) { // Show if not last page ?>
                    <a href="<?php printf("%s?pageNum_rsLocations=%d%s", $currentPage, min($totalPages_rsLocations, $pageNum_rsLocations + 1), $queryString_rsLocations); ?>">Next</a>
                    <?php } // Show if not last page ?></td>
                <td><?php if ($pageNum_rsLocations < $totalPages_rsLocations) { // Show if not last page ?>
                    <a href="<?php printf("%s?pageNum_rsLocations=%d%s", $currentPage, $totalPages_rsLocations, $queryString_rsLocations); ?>">Last</a>
                    <?php } // Show if not last page ?></td>
              </tr>
            </table>
    </div><script>
<!--

var sprytextfield2 = new Spry.Widget.ValidationTextField("sprytextfield2", "integer");
//-->
    </script>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLocations);

mysql_free_result($rsPreferences);

mysql_free_result($rsLocationCategories);

mysql_free_result($rsLocationPrefs);

mysql_free_result($rsOtherLocations);

mysql_free_result($rsDirectory);
?>
