<?php require_once('../../Connections/aquiescedb.php'); ?><?php require_once('../../core/includes/upload.inc.php'); ?><?php require_once('../../core/includes/framework.inc.php'); ?><?php require_once('../includes/calendar.inc.php'); ?>
<?php require_once('../../location/includes/locationfunctions.inc.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "";
$MM_donotCheckaccess = "true";

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
    if (($strUsers == "") && true) { 
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
  if (isset($_SESSION['QUERY_STRING']) && strlen($_SESSION['QUERY_STRING']) > 0) 
  $MM_referrer .= "?" . $_SESSION['QUERY_STRING'];
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

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
	if($_POST['token'] != md5($_POST['ID'].$_POST['eventlocationID'].PRIVATE_KEY.$_SERVER['PHP_SELF'])) {
		die(); // security prevent from chamging event or location id
	}
	
	$uploaded = getUploads();
	if(isset($_POST['noImage'])) {
		$_POST['imageURL'] = "";
	}
	
	if (isset($uploaded) && is_array($uploaded)) { 
		if(isset($uploaded["filename"][0]["newname"]) && $uploaded["filename"][0]["newname"]!="") { 
			$_POST['imageURL'] = $uploaded["filename"][0]["newname"]; 
		}
		
	}
}



if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1") && $_POST['formaction'] == "delete") {
	 deleteEvent($_POST['ID']) ;
	
	$updateGoTo = isset($_GET['returnURL']) ? $_GET['returnURL'] : "index.php";
	if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo)); exit;
}

if(isset($_POST['latitude']) && trim($_POST['latitude'])!="") {
	if(isset($_POST['eventlocationID']) && intval($_POST['eventlocationID']) >0) { // location already exists
		$update = "UPDATE location SET latitude = ".GetSQLValueString($_POST['latitude'], "double").", longitude = ".GetSQLValueString($_POST['longitude'], "double")." WHERE ID = ".GetSQLValueString($_POST['eventlocationID'], "int");
		mysql_query($update, $aquiescedb) or die(mysql_error().": ".$update);
	} else { // new location
	
	  $_POST['eventlocationID'] = createLocation(1,0,$_POST['address1'],$description="",$_POST['address1'],$_POST['address2'],$_POST['address3'],$_POST['address4'],$_POST['address5'],$_POST['postcode'],"", "", "", "", "", "", "",$_POST['latitude'], $_POST['longitude'],$_POST['modifiedbyID']);
	
	}
	
}
	

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE event SET eventgroupID=%s, eventlocationID=%s, imageURL=%s, startdatetime=%s, enddatetime=%s, modifiedbyID=%s, modifieddatetime=%s WHERE ID=%s",
                       GetSQLValueString($_POST['eventgroupID'], "int"),
                       GetSQLValueString($_POST['eventlocationID'], "int"),
                       GetSQLValueString($_POST['imageURL'], "text"),
                       GetSQLValueString($_POST['startdatetime'], "date"),
                       GetSQLValueString($_POST['enddatetime'], "date"),
                       GetSQLValueString($_POST['modifiedbyID'], "int"),
                       GetSQLValueString($_POST['modifieddatetime'], "date"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
	$update = "UPDATE eventgroup SET eventtitle = ".GetSQLValueString($_POST['eventtitle'], "text").", 
	eventdetails = ".GetSQLValueString($_POST['eventdetails'], "text").",
	categoryID = ".GetSQLValueString($_POST['categoryID'], "int").",
	customvalue1 = ".GetSQLValueString($_POST['customvalue1'], "text").",
	customvalue2 = ".GetSQLValueString($_POST['customvalue2'], "text").",
	modifiedbyID=". GetSQLValueString($_POST['modifiedbyID'], "int").",
	modifieddatetime = NOW() WHERE ID = ".GetSQLValueString($_POST['eventgroupID'], "int");
	mysql_query($update, $aquiescedb) or die(mysql_error());
	
	
	$updateGoTo = isset($_GET['returnURL']) ? $_GET['returnURL'] : "index.php";
 
  
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo)); exit;
}
?>
<?php
$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

$colname_rsThisEvent = "-1";
if (isset($_GET['eventID'])) {
  $colname_rsThisEvent = $_GET['eventID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsThisEvent = sprintf("SELECT event.ID, eventlocationID, startdatetime, enddatetime, eventgroupID,eventgroup.eventtitle, eventgroup.eventdetails, event.createdbyID, eventgroup.customvalue1, eventgroup.customvalue2, event.imageURL,location.latitude, location.longitude, location.address1, location.address2, location.address3, location.address4, location.address5, location.postcode, eventgroup.categoryID FROM event LEFT JOIN eventgroup ON (event.eventgroupID = eventgroup.ID) LEFT JOIN location ON (event.eventlocationID = location.ID) WHERE event.ID = %s", GetSQLValueString($colname_rsThisEvent, "int"));
$rsThisEvent = mysql_query($query_rsThisEvent, $aquiescedb) or die(mysql_error());
$row_rsThisEvent = mysql_fetch_assoc($rsThisEvent);
$totalRows_rsThisEvent = mysql_num_rows($rsThisEvent);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsCategories = "SELECT ID, title FROM eventcategory WHERE active = 1 ORDER BY title ASC";
$rsCategories = mysql_query($query_rsCategories, $aquiescedb) or die(mysql_error());
$row_rsCategories = mysql_fetch_assoc($rsCategories);
$totalRows_rsCategories = mysql_num_rows($rsCategories);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsEventPrefs = "SELECT * FROM eventprefs";
$rsEventPrefs = mysql_query($query_rsEventPrefs, $aquiescedb) or die(mysql_error());
$row_rsEventPrefs = mysql_fetch_assoc($rsEventPrefs);
$totalRows_rsEventPrefs = mysql_num_rows($rsEventPrefs);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = "SELECT * FROM preferences";
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);

$canonicalURL = htmlentities($_SERVER["REQUEST_URI"], ENT_COMPAT, "UTF-8");

?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php  $pageTitle = "Update Event"; echo $site_name." | ".$pageTitle;?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script src="/core/scripts/date-picker/js/datepicker.js"></script>
<?php $googlemapsAPI =isset($googlemapsAPI) ? $googlemapsAPI : $row_rsPreferences['googlemapsAPI'];
if($googlemapsAPI!="") { ?>
<script src="/location/scripts/location.js"></script>
<script src="//maps.google.com/?file=api&amp;v=2.x&amp;key=<?php echo $googlemapsAPI; ?>"></script>
<script src="/core/scripts/googlemaps/googlemap.js" ></script>
<script src="/core/scripts/googlemaps/fb_maps.js" ></script>
<script>

if(typeof(fb_maps_version) === 'undefined' || fb_maps_version < 2) alert("Javascript library fb_maps.js needs updating.");

var initLatitude = <?php echo isset($row_rsThisEvent['latitude']) ? $row_rsThisEvent['latitude']: 0; ?>;
var initLongitude =<?php echo isset($row_rsThisEvent['longitude']) ? $row_rsThisEvent['longitude']: 0; ?>;
var initZoom = <?php echo isset($row_rsThisEvent['latitude']) ? 16 : 2; ?> ;
var initMapType = G_NORMAL_MAP;
var showMapType = false;
var mapControlType = "small"; // delete for  normal 
var markerLatitude = <?php echo isset($row_rsThisEvent['latitude']) ? $row_rsThisEvent['latitude']: "null"; ?>;
var markerLongitude = <?php echo isset($row_rsThisEvent['longitude']) ? $row_rsThisEvent['longitude']: "null"; ?>;
var defaultIcon = new GIcon(G_DEFAULT_ICON);
var isEditable = true;

function init() {
setupMap();
document.getElementById('address').value = document.getElementById('address1').value+" "+document.getElementById('postcode').value;
if(markerLatitude) {
	createMarker(markerLatitude,markerLongitude,defaultIcon,true) 
} else { // try and find a location
	findLocation(document.getElementById('address1').value+" "+document.getElementById('postcode').value);
}
if(gl) { // location services available
		document.getElementById('getLocation').style.display = 'inline'; // change from default 'block' in location.js
	}
	
}

addListener("load",init);

//]]>
</script>
<?php if(isset($row_rsPreferences['googlesearchAPI'])) { // load GoogleAjaxSearch ?>
<script src="//www.google.com/uds/api?file=uds.js&v=1.0&key=<?php echo $row_rsPreferences['googlesearchAPI']; ?>"></script>
<script>
var localSearch = new GlocalSearch();
</script>
<?php } } 
else { echo "<style> #tabMaps { display:none; } </style>" ; } ?>
<script src="../../SpryAssets/SpryValidationTextField.js"></script>
<script src="../../SpryAssets/SpryTabbedPanels.js"></script>
<link href="../../SpryAssets/SpryValidationTextField.css" rel="stylesheet"  />
<link href="../css/calendarDefault.css" rel="stylesheet"  /><style><!--
<?php if($totalRows_rsCategories==0) {
	echo ".category { display: none; }";
} ?><?php if(trim($row_rsEventPrefs['customfield1'])=="") {
	echo ".custom1 { display: none; }";
}

if(trim($row_rsEventPrefs['customfield2'])=="") {
	echo ".custom2 { display: none; }";
}

?>

--></style>
<link href="../../SpryAssets/SpryTabbedPanels.css" rel="stylesheet"  />
<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" -->
      <div class="calendar members container">
		 <div class="crumbs"><div><span class="you_are_in">You are in: </span>
      
      <ol itemscope itemtype="http://schema.org/BreadcrumbList">
            <li itemprop="itemListElement" itemscope
      itemtype="http://schema.org/ListItem"><a itemprop="item" href="/"><span itemprop="name">Home</span></a>
      <meta itemprop="position" content="1" /></li>
      
     <li itemprop="itemListElement" itemscope
      itemtype="http://schema.org/ListItem"> 
      <a itemprop="item" href="/calendar/index.php" rel="index"><span itemprop="name">Events</span></a>
       <meta itemprop="position" content="2" />
      </li> 
      
	  
	  <li itemprop="itemListElement" itemscope
      itemtype="http://schema.org/ListItem">
	  <a itemprop="item" href="<?php echo $canonicalURL; ?>"><span itemprop="name">
	  Update Event</span></a> <meta itemprop="position" content="3" /></li></ol>
      
      
      </div></div>
		  <h1 class="calendarHeader">Update Event</h1>
          <?php if ($_SESSION['MM_UserGroup'] <8 && $row_rsThisEvent['createdbyID'] != $row_rsLoggedIn['ID']) { ?>
		  <p class="alert warning alert-warning" role="alert">You can only update events that you created.</p>
          <?php } else { ?>
		  <form action="<?php echo $editFormAction; ?>" method="post" enctype="multipart/form-data" name="form1" id="form1" role="form">
		    <div id="TabbedPanels1" class="TabbedPanels">
		      <ul class="TabbedPanelsTabGroup">
		        <li class="TabbedPanelsTab" tabindex="0">Event</li>
		        <li class="TabbedPanelsTab" tabindex="0" id="tabMaps">Map</li>
	          </ul>
		      <div class="TabbedPanelsContentGroup">
		        <div class="TabbedPanelsContent">
		  <table border="0" cellpadding="0" cellspacing="0" class="form-table">
		    <tr class="category form-group">
		      <th class="top text-right" scope="row"><label for="categoryID">Category:</label></th>
		      <td><select name="categoryID" id="categoryID" class="form-control">
		        <option value="" <?php if (!(strcmp("", $row_rsThisEvent['categoryID']))) {echo "selected=\"selected\"";} ?>><?php echo isset($region['text_choose']) ? htmlentities($region['text_choose'], ENT_COMPAT, "UTF-8") : "Choose..." ?></option>
		        <?php
do {  
?>
		        <option value="<?php echo $row_rsCategories['ID']?>"<?php if (!(strcmp($row_rsCategories['ID'], $row_rsThisEvent['categoryID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsCategories['title']?></option>
		        <?php
} while ($row_rsCategories = mysql_fetch_assoc($rsCategories));
  $rows = mysql_num_rows($rsCategories);
  if($rows > 0) {
      mysql_data_seek($rsCategories, 0);
	  $row_rsCategories = mysql_fetch_assoc($rsCategories);
  }
?>
		        </select></td>
	        </tr>
		    <tr class="form-group">
		      <th class="top text-right" scope="row"><label for="eventtitle">Event Name:</label></th>
		      <td><span id="sprytextfield1">
		        <input name="eventtitle" type="text" id="eventtitle" value="<?php echo $row_rsThisEvent['eventtitle']; ?>" size="50" maxlength="50" class="form-control" />
		        <span class="textfieldRequiredMsg">A value is required.</span></span></td>
	        </tr>
		    <tr class="form-group">
		      <th class="top text-right" scope="row">Starts:</th>
		      <td><input type="hidden" name="startdatetime" id="startdatetime" value="<?php $setvalue =  $row_rsThisEvent['startdatetime']; echo $setvalue;  $inputname = "startdatetime"; $time = true; ?>" />
	          <?php require('../../core/includes/datetimeinput.inc.php'); ?></td>
	        </tr>
		    <tr class="form-group">
		      <th class="top text-right" scope="row">Ends:</th>
		      <td><input type="hidden" name="enddatetime" id="enddatetime" value="<?php $setvalue = $row_rsThisEvent['enddatetime']; echo $setvalue;  $inputname = "enddatetime"; $time = true; ?>" /><?php require('../../core/includes/datetimeinput.inc.php'); ?></td>
	        </tr> <tr class="custom1 form-group">
		      <th class="top text-right" scope="row"><label for="customvalue1"><?php echo $row_rsEventPrefs['customfield1']; ?>:</label></th>
		      <td>
		        <input name="customvalue1" type="text" id="customvalue1" value="<?php echo $row_rsThisEvent['customvalue1']; ?>" size="50" maxlength="100"  class="form-control"/></td>
		      </tr>
		    <tr class="custom2 form-group">
		      <th class="top text-right" scope="row"><label for="customvalue2"><?php echo $row_rsEventPrefs['customfield2']; ?>:</label></th>
		      <td><input name="customvalue2" type="text" id="customvalue2" value="<?php echo $row_rsThisEvent['customvalue2']; ?>" size="50" maxlength="100"  class="form-control"/></td>
	        </tr>
		    <tr class="form-group">
		      <th class="top text-right" scope="row"><label for="eventdetails">Details:</label></th>
		      <td>
		        
		        <textarea name="eventdetails" id="eventdetails" cols="45" rows="5" class="form-control"><?php echo $row_rsThisEvent['eventdetails']; ?></textarea>
		        </td>
		      </tr> <tr class="form-group">
		      <th class="text-nowrap  top text-right"><input name="imageURL" type="hidden" id="imageURL" value="<?php echo $row_rsThisEvent['imageURL']; ?>">
		        Image:</th>
		      <td><?php if (isset($row_rsThisEvent['imageURL'])) { ?>
		        <img src="<?php echo getImageURL($row_rsThisEvent['imageURL'], "medium"); ?>" alt="Current image" />
		        <label>
		          <input name="noImage" type="checkbox" value="1" />
		          Remove image</label>
		        <br />
		        <?php } ?>
		        <span class="upload">
		          <input name="filename" type="file" class="fileinput" id="filename" maxlength="50" />
		          </span></td>
		      </tr>
		    
	      </table>
		        </div>
		        <div class="TabbedPanelsContent">            <p>Drag marker or find address:
              <input name="address" type="text"  id="address" size="30" maxlength="100" />
              <input name="find" type="button" class="button" id="find" onclick="findLocation(document.getElementById('address').value);" value="Find" />
              <input name="Button" type="button" class="button" onclick="clearMap();" value="Clear" />
              <span id="getLocation" class="locationServices">
              <input type="button" onclick="getGeoLocation(); return false;" value="Get my location" />
              </span>
              <input name="skip" type="submit" class="submit" id="skip" value="Save location"/>
            </p>
            <div class="googlemap" id="googlemap"></div>
            <!-- hidden for now - maybe add editing featires later -->
            <input name="address1" id="address1" type="hidden" value="<?php echo $row_rsThisEvent['address1']; ?>">
            <input name="address2" id="address2" type="hidden" value="<?php echo $row_rsThisEvent['address2']; ?>">
            <input name="address3" id="address3" type="hidden" value="<?php echo $row_rsThisEvent['address3']; ?>">
            <input name="address4" id="address4" type="hidden" value="<?php echo $row_rsThisEvent['address4']; ?>">
            <input name="address5" id="address5" type="hidden" value="<?php echo $row_rsThisEvent['address5']; ?>">
            <input name="postcode" id="postcode" type="hidden" value="<?php echo $row_rsThisEvent['postcode']; ?>">
            <input type="hidden" id="latitude" name="latitude" value="<?php echo $row_rsThisEvent['latitude']; ?>" />
            <input type="hidden" id="longitude" name="longitude" value="<?php echo $row_rsThisEvent['longitude']; ?>" />
            <input name="eventlocationID" type="hidden" id="eventlocationID" value="<?php echo $row_rsThisEvent['eventlocationID']; ?>">
		        </div>
	          </div>
	        </div>
		  <input type="hidden" name="MM_update" value="form1" /><span><button type="submit" >Save changes</button></span> <button type="submit" name="deletebutton" id="deletebutton" onclick="if(confirm('Are you sure you want to delete this event?')) { document.getElementById('formaction').value = 'delete'; } else { return false; }">Delete event</button>
	          <input name="modifieddatetime" type="hidden" id="modifieddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
	          <input name="modifiedbyID" type="hidden" id="modifiedbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
	          <input type="hidden" name="directoryID" id="directoryID" value="<?php echo isset($_GET['directoryID']) ? htmlentities($_GET['directoryID']) : ""; ?>" />
	          <input name="eventgroupID" type="hidden" id="eventgroupID" value="<?php echo $row_rsThisEvent['eventgroupID']; ?>" />
	          <input name="ID" type="hidden" id="ID" value="<?php echo $row_rsThisEvent['ID']; ?>" />
              	          <input name="formaction" type="hidden" id="formaction" value="" />

	          <input name="token" type="hidden" id="token" value="<?php echo md5($row_rsThisEvent['ID'].$row_rsThisEvent['eventlocationID'].PRIVATE_KEY.$_SERVER['PHP_SELF']); ?>" />
          </form></div>
		  <script>
<!--
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1");
var TabbedPanels1 = new Spry.Widget.TabbedPanels("TabbedPanels1");
//-->
          </script><?php } ?>
	    <!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsThisEvent);

mysql_free_result($rsCategories);

mysql_free_result($rsEventPrefs);
?>
