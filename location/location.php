<?php require_once('../Connections/aquiescedb.php'); ?><?php require_once('../core/includes/framework.inc.php'); ?>
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

$colname_rsLocation = "-1";
if (isset($_GET['locationID'])) {
  $colname_rsLocation = $_GET['locationID'];
}

$regionID = (isset($regionID ) && intval($regionID )>0) ? intval($regionID): 1;

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLocation = sprintf("SELECT * FROM location WHERE ID = %s", GetSQLValueString($colname_rsLocation, "int"));
$rsLocation = mysql_query($query_rsLocation, $aquiescedb) or die(mysql_error());
$row_rsLocation = mysql_fetch_assoc($rsLocation);
$totalRows_rsLocation = mysql_num_rows($rsLocation);



mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = "SELECT googlemapsAPI FROM preferences";
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLocationPrefs = "SELECT * FROM locationprefs";
$rsLocationPrefs = mysql_query($query_rsLocationPrefs, $aquiescedb) or die(mysql_error());
$row_rsLocationPrefs = mysql_fetch_assoc($rsLocationPrefs);
$totalRows_rsLocationPrefs = mysql_num_rows($rsLocationPrefs);

$varLocationID_rsContacts = "-1";
if (isset($_GET['locationID'])) {
  $varLocationID_rsContacts = $_GET['locationID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsContacts = sprintf("SELECT locationuser.userID, users.firstname, users.surname, users.email, users.telephone, users.jobtitle FROM locationuser LEFT JOIN users ON (locationuser.userID = users.ID) WHERE locationuser.locationID = %s AND users.usertypeID > 1", GetSQLValueString($varLocationID_rsContacts, "int"));
$rsContacts = mysql_query($query_rsContacts, $aquiescedb) or die(mysql_error());
$row_rsContacts = mysql_fetch_assoc($rsContacts);
$totalRows_rsContacts = mysql_num_rows($rsContacts);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_Recordset1 = "SELECT * FROM locationprefs WHERE ID =".$regionID;
$Recordset1 = mysql_query($query_Recordset1, $aquiescedb) or die(mysql_error());
$row_Recordset1 = mysql_fetch_assoc($Recordset1);
$totalRows_Recordset1 = mysql_num_rows($Recordset1);

$sql="SHOW TABLES LIKE 'bookingresource'";
$result = mysql_query($sql, $aquiescedb) or die(mysql_error());
if(mysql_num_rows($result)>0) {

$varLocationID_rsBookableResources = "-1";
if (isset($_GET['locationID'])) {
  $varLocationID_rsBookableResources = $_GET['locationID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsBookableResources = sprintf("SELECT bookingresource.ID, bookingresource.title, bookingresource.`description`, bookingresource.imageURL FROM bookingresource WHERE bookingresource.locationID = %s AND bookingresource.statusID = 1", GetSQLValueString($varLocationID_rsBookableResources, "int"));
$rsBookableResources = mysql_query($query_rsBookableResources, $aquiescedb) or die(mysql_error());
$row_rsBookableResources = mysql_fetch_assoc($rsBookableResources);
$totalRows_rsBookableResources = mysql_num_rows($rsBookableResources);
}
?><?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = htmlentities($row_rsLocation['locationname'], ENT_COMPAT, "UTF-8"); echo $pageTitle." | ".$site_name; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->

<script src="../SpryAssets/SpryTabbedPanels.js"></script>

<script src="//maps.google.com/?file=api&amp;v=2.x&amp;key=<?php echo isset($googlemapsAPI) ? $googlemapsAPI : $row_rsPreferences['googlemapsAPI']; ?>"></script>
<script src="/core/scripts/googlemaps/googlemap.js" ></script>
<script src="/core/scripts/googlemaps/fb_maps.js" ></script>
<script>
//<![CDATA[

var initLatitude = <?php echo $row_rsLocation['latitude']; ?>;
var initLongitude = <?php echo $row_rsLocation['longitude']; ?>;
var initZoom = <?php echo 13; ?>;
var initMapType = G_NORMAL_MAP;
var showMapType = true;
var markerLatitude = <?php echo $row_rsLocation['latitude']; ?>;
var markerLongitude = <?php echo $row_rsLocation['longitude']; ?>;
var defaultIcon = new GIcon(G_DEFAULT_ICON);
var isEditable = false;
function init() {	
setupMap();
createMarker(markerLatitude,markerLongitude,defaultIcon,false);
map.addControl(new streetViewLink());
}

addListener("load",init);

//]]>
</script>
<link href="../SpryAssets/SpryTabbedPanels.css" rel="stylesheet" type="text/css" />
<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" --><div class="container pageBody">
    <div class="crumbs"><div>You are in: <a href="/">Home</a><span class="separator">&nbsp;&rsaquo;&nbsp;</span><a href="index.php"><?php echo ucwords($row_rsLocationPrefs['locationdescriptor']); ?></a><span class="separator">&nbsp;&rsaquo;&nbsp;</span><?php echo htmlentities($row_rsLocation['locationname'], ENT_COMPAT, "UTF-8"); ?></div></div> <?php if($row_rsLocationPrefs['publicaccess']==1 || (isset($_SESSION['MM_UserGroup']) && $_SESSION['MM_UserGroup'] >0)) { // allowed access ?>
    <h1 class="locationheader"><?php echo htmlentities($row_rsLocation['locationname'], ENT_COMPAT, "UTF-8"); ?></h1>
   <div id="TabbedPanels1" class="TabbedPanels">
     <ul class="TabbedPanelsTabGroup">
       <li class="TabbedPanelsTab" tabindex="0">Overview</li>
       <li class="TabbedPanelsTab" tabindex="0" id="locationmap">Map</li>
       <li class="TabbedPanelsTab" tabindex="0">Contacts</li>
</ul>
     <div class="TabbedPanelsContentGroup">
       <div class="TabbedPanelsContent">
         <?php if (isset($row_rsLocation['imageURL'])) { ?>
         <a href="<?php echo getImageURL($row_rsLocation['imageURL'],"large"); ?>"  title="<?php echo htmlentities($row_rsLocation['locationname'], ENT_COMPAT, "UTF-8"); ?>" class="img"><img src="<?php echo getImageURL($row_rsLocation['imageURL'],"medium"); ?>" alt="<?php echo htmlentities($row_rsLocation['locationname'], ENT_COMPAT, "UTF-8"); ?>" /></a><br />
         <?php } ?>
         <?php echo isset($row_rsLocation['address']) ? nl2br(htmlentities($row_rsLocation['address'], ENT_COMPAT, "UTF-8")).
		"<br />" : ""; ?> <?php echo isset($row_rsLocation['address1']) ? htmlentities($row_rsLocation['address1'], ENT_COMPAT, "UTF-8")."<br />" : ""; ?> <?php echo isset($row_rsLocation['address2']) ? htmlentities($row_rsLocation['address2'], ENT_COMPAT, "UTF-8")."<br />" : ""; ?> <?php echo isset($row_rsLocation['address3']) ? htmlentities($row_rsLocation['address3'], ENT_COMPAT, "UTF-8")."<br />" : ""; ?> <?php echo isset($row_rsLocation['address4']) ? htmlentities($row_rsLocation['address4'], ENT_COMPAT, "UTF-8")."<br />" : ""; ?> <?php echo isset($row_rsLocation['address5']) ? htmlentities($row_rsLocation['address5'], ENT_COMPAT, "UTF-8")."<br />" : ""; ?> 
		<?php echo isset($row_rsLocation['postcode']) ? htmlentities($row_rsLocation['postcode'], ENT_COMPAT, "UTF-8")."<br />" : ""; ?> 
		<?php echo isset($row_rsLocation['telephone1']) ? "Tel: ".htmlentities($row_rsLocation['telephone1'], ENT_COMPAT, "UTF-8")."<br />" : ""; ?> 
		<?php echo isset($row_rsLocation['telephone2']) ? "Tel: ".htmlentities($row_rsLocation['telephone2'], ENT_COMPAT, "UTF-8")."<br />" : ""; ?>  <?php echo isset($row_rsLocation['telephone3']) ? "Tel: ".htmlentities($row_rsLocation['telephone3'], ENT_COMPAT, "UTF-8")."<br />" : ""; ?>  <?php echo isset($row_rsLocation['fax']) ? "Fax: ".htmlentities($row_rsLocation['fax'], ENT_COMPAT, "UTF-8")."<br />" : ""; ?>
         <p><?php echo nl2br(htmlentities($row_rsLocation['description'], ENT_COMPAT, "UTF-8")); ?></p>
         <?php if(isset($row_rsLocation['locationURL'])) { ?>
         <p>Web site: <a href="<?php echo htmlentities($row_rsLocation['locationURL'], ENT_COMPAT, "UTF-8"); ?>" target="_blank" rel="noopener"><?php echo htmlentities($row_rsLocation['locationURL'], ENT_COMPAT, "UTF-8"); ?></a></p>
         <?php } ?>
         <?php if ($totalRows_rsBookableResources > 0) { // Show if recordset not empty ?>
         <h2>Bookable Resources</h2>
         <table class="table table-hover">
         <tbody>
           <?php do { ?>
           <tr>
             <td><p>
               <?php if (isset($row_rsBookableResources['imageURL'])) { ?>
               <a href="<?php echo getImageURL($row_rsBookableResources['imageURL'],"large"); ?>"  title="<?php echo $row_rsBookableResources['title']; ?>" class="img"><img src="<?php echo getImageURL($row_rsBookableResources['imageURL'],"thumb"); ?>" alt="<?php echo $row_rsBookableResources['title']; ?>" class="fltlft" /></a>
               <?php } ?>
               <strong><?php echo $row_rsBookableResources['title']; ?></strong><br />
               <?php echo $row_rsBookableResources['description']; ?><br />
               <a href="../booking/month.php?resourceID=<?php echo $row_rsBookableResources['ID']; ?>">More...</a></p></td>
           </tr>
           <?php } while ($row_rsBookableResources = mysql_fetch_assoc($rsBookableResources)); ?></tbody>
         </table>
         <?php } // Show if recordset not empty ?>
       </div>
       <div class="TabbedPanelsContent">
         <?php if ((isset($row_rsLocation['latitude']) && $row_rsLocation['latitude']!="")) { ?>
         <div class="googlemap" id="googlemap"></div>
         <?php } ?>
         <?php if (isset($row_rsLocation['mapURL']) && $row_rsLocation['mapURL']!="") { ?>
         <p><a href="<?php echo $row_rsLocation['mapURL']; ?>" target="_blank" rel="noopener">Click here to view map</a></p>
         <?php } else { ?>
         <p><a href="http://maps.google.com/?q=<?php echo isset($row_rsLocation['latitude']) ? $row_rsLocation['latitude'].",".$row_rsLocation['longitude'] : $row_rsLocation['postcode'] ; ?>&z=16" target="_blank" rel="noopener">Open in Google Maps</a></p>
         <?php } ?>
       </div>
<div class="TabbedPanelsContent">
         <?php if ($totalRows_rsContacts == 0) { // Show if recordset empty ?>
  <p>There are currently no contacts associated with this location.</p>
  <?php } // Show if recordset empty ?>
<?php if ($totalRows_rsContacts > 0) { // Show if recordset not empty ?>
  <table class="table table-hover">
    <tbody>
    <?php do { ?>
      <tr>
        <td><?php echo htmlentities($row_rsContacts['firstname'], ENT_COMPAT, "UTF-8"); ?> <?php echo htmlentities($row_rsContacts['surname'], ENT_COMPAT, "UTF-8"); ?></td><td><?php echo htmlentities($row_rsContacts['jobtitle'], ENT_COMPAT, "UTF-8"); ?></td>
        <td><script>writeEmail(<?php  $email = explode("@",$row_rsContacts['email']); echo "\"".$email[0]."\",\"".$email[1]."\""; ?>);</script>&nbsp;</td>
        
        <td><?php echo htmlentities($row_rsContacts['telephone'], ENT_COMPAT, "UTF-8"); ?>&nbsp;</td>
      </tr>
      <?php } while ($row_rsContacts = mysql_fetch_assoc($rsContacts)); ?></tbody>
  </table>
  <?php } // Show if recordset not empty ?>
       </div>
</div>
   </div>
  
    
     
    <?php if (isset($_GET['defaultTab'])) { echo '<script>
<!--
var TabbedPanels1 = new Spry.Widget.TabbedPanels("TabbedPanels1", {defaultTab:'.intval($_GET['defaultTab']).'});
//-->
    </script>'; } else { ?>
    <script>
<!--
var TabbedPanels1 = new Spry.Widget.TabbedPanels("TabbedPanels1");
//-->
    </script>
    <?php } ?>
    <p><a href="index.php" class="link_back" onclick="history.go(-1); return false;"><i class="glyphicon glyphicon-arrow-left"></i> Back</a></p> <?php } else { // no access ?>
            <p class="alert warning alert-warning" role="alert">You do not have access to view locations. You may need to <a href="../login/index.php?accesscheck=/location/">log in</a>.</p>
            <?php } ?></div>
  <!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLocation);

mysql_free_result($rsBookableResources);

mysql_free_result($rsPreferences);

mysql_free_result($rsLocationPrefs);

mysql_free_result($rsContacts);

mysql_free_result($Recordset1);
?>
