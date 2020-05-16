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

$regionID = (isset($regionID) && intval($regionID)>0) ? intval($regionID) : 1;

$varEventID_rsEvent = "-1";
if (isset($_GET['eventID'])) {
  $varEventID_rsEvent = $_GET['eventID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsEvent = sprintf("SELECT event.*, eventgroup.eventtitle, eventgroup.eventdetails,eventgroup.imageURL, eventgroup.customvalue1,eventgroup.customvalue2, eventgroup.usertypeID,  users.firstname, users.surname, eventcategory.title, location.locationname, location.`description`, location.address1, location.address2, location.address3, location.address4, location.address5, location.postcode, location.telephone1, eventcategory.ID AS categoryID, location.latitude, location.longitude FROM event LEFT JOIN users ON (event.createdbyID = users.ID) LEFT JOIN  eventgroup ON (event.eventgroupID = eventgroup.ID) LEFT JOIN eventcategory ON (eventgroup.categoryID = eventcategory.ID) LEFT JOIN location ON (event.eventlocationID = location.ID) WHERE event.statusID =1 AND  event.ID = %s", GetSQLValueString($varEventID_rsEvent, "int"));
$rsEvent = mysql_query($query_rsEvent, $aquiescedb) or die(mysql_error());
$row_rsEvent = mysql_fetch_assoc($rsEvent);
$totalRows_rsEvent = mysql_num_rows($rsEvent);

$colname_rsAlreadyRegistered = "-1";
if (isset($_GET['eventID'])) {
  $colname_rsAlreadyRegistered = $_GET['eventID'];
}
$varUsername_rsAlreadyRegistered = "-1";
if (isset($_SESSION['MM_Username'])) {
  $varUsername_rsAlreadyRegistered = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsAlreadyRegistered = sprintf("SELECT eventregistration.ID FROM eventregistration , users WHERE eventID = %s AND userID = users.ID AND users.username = %s", GetSQLValueString($colname_rsAlreadyRegistered, "int"),GetSQLValueString($varUsername_rsAlreadyRegistered, "text"));
$rsAlreadyRegistered = mysql_query($query_rsAlreadyRegistered, $aquiescedb) or die(mysql_error());
$row_rsAlreadyRegistered = mysql_fetch_assoc($rsAlreadyRegistered);
$totalRows_rsAlreadyRegistered = mysql_num_rows($rsAlreadyRegistered);

$varEventID_rsTotalResgistrants = "-1";
if (isset($_GET['eventID'])) {
  $varEventID_rsTotalResgistrants = $_GET['eventID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsTotalResgistrants = sprintf("SELECT COUNT(eventregistration.ID) AS numRegistered FROM eventregistration WHERE eventregistration.eventID = %s", GetSQLValueString($varEventID_rsTotalResgistrants, "int"));
$rsTotalResgistrants = mysql_query($query_rsTotalResgistrants, $aquiescedb) or die(mysql_error());
$row_rsTotalResgistrants = mysql_fetch_assoc($rsTotalResgistrants);
$totalRows_rsTotalResgistrants = mysql_num_rows($rsTotalResgistrants);

$varUsername_rsUser = "-1";
if (isset($_SESSION['MM_Username'])) {
  $varUsername_rsUser = $_SESSION['MM_Username'];
}
$varEmail_rsUser = "-1";
if (isset($_GET['email'])) {
  $varEmail_rsUser = $_GET['email'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsUser = sprintf("SELECT ID, email FROM users WHERE username = %s OR email = %s", GetSQLValueString($varUsername_rsUser, "text"),GetSQLValueString($varEmail_rsUser, "text"));
$rsUser = mysql_query($query_rsUser, $aquiescedb) or die(mysql_error());
$row_rsUser = mysql_fetch_assoc($rsUser);
$totalRows_rsUser = mysql_num_rows($rsUser);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsEventPrefs = "SELECT * FROM eventprefs WHERE ID = ".$regionID."";
$rsEventPrefs = mysql_query($query_rsEventPrefs, $aquiescedb) or die(mysql_error());
$row_rsEventPrefs = mysql_fetch_assoc($rsEventPrefs);
$totalRows_rsEventPrefs = mysql_num_rows($rsEventPrefs);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = "SELECT googlemapsAPI FROM preferences";
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);

$varRegionID_rsMerge = "1";
if (isset($regionID)) {
  $varRegionID_rsMerge = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsMerge = sprintf("SELECT * FROM merge WHERE statusID = 1 AND (%s = regionID OR regionID = 0)", GetSQLValueString($varRegionID_rsMerge, "int"));
$rsMerge = mysql_query($query_rsMerge, $aquiescedb) or die(mysql_error());
$row_rsMerge = mysql_fetch_assoc($rsMerge);
$totalRows_rsMerge = mysql_num_rows($rsMerge);

if(isset($row_rsEvent['ID']) && isset($row_rsUser['ID'])) { // get current RSVP status (if any)
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$select = "SELECT statusID FROM eventattend WHERE eventID = ".$row_rsEvent['ID']." AND userID = ".$row_rsUser['ID'];
	$result = mysql_query($select, $aquiescedb) or die(mysql_error());
	$rowStatus = mysql_fetch_assoc($result);

}

 $accesslevel = $row_rsEventPrefs['accesslevel'];
			   if(is_readable("../members/includes/restrictaccess.inc.php")) require_once('../members/includes/restrictaccess.inc.php');
			   
$datedescription = date('jS F Y',strtotime($row_rsEvent['startdatetime']));
if($row_rsEvent['allday']!=1) {
   $datedescription .= date(' - g.ia',strtotime($row_rsEvent['startdatetime']));
}
if(isset($row_rsEvent['enddatetime']) && $row_rsEvent['enddatetime'] != $row_rsEvent['startdatetime']) {
	$enddate = "";
	
	if(date('Y-m-d', strtotime($row_rsEvent['startdatetime'])) != date('Y-m-d', strtotime($row_rsEvent['enddatetime']))) { 
		$enddate .=  date('jS F Y',strtotime($row_rsEvent['enddatetime'])); 
	} 	
	if($row_rsEvent['allday']!=1) {
		$enddate .=  date(' - g.ia',strtotime($row_rsEvent['enddatetime']));
	}
	
	$datedescription .= ($enddate=="") ? "" : " to ".$enddate;
}



if($totalRows_rsMerge>0) {
	do{
		if(trim($row_rsMerge['mergeincludeURL'])!="") {
			$url = SITE_ROOT.$row_rsMerge['mergeincludeURL'];
			if(is_readable($url)) {				
				ob_start();
				include( $url);
				$row_rsMerge['mergetext'] = ob_get_clean(); // gets content, discards buffer			
			} else {
				if(defined("DEBUG")) die("Can not read include (".htmlentities($url).")");
			}
		}		
		$row_rsEvent['eventdetails'] = str_replace($row_rsMerge['mergename'], $row_rsMerge['mergetext'],$row_rsEvent['eventdetails']);
	} while($row_rsMerge = mysql_fetch_assoc($rsMerge));
}



			   
?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = $row_rsEvent['eventtitle']." -  ".$datedescription; echo $pageTitle." | ".$site_name; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<meta name="Description" content="<?php echo strip_tags($row_rsEvent['eventdetails']); ?>" />
<meta name="Keywords" content="<?php echo $row_rsEvent['eventtitle']; ?>, events, diary, calendar, what's on, schedule, timetable" />
<link href="css/calendarDefault.css" rel="stylesheet"  />
<script src="http://maps.google.com/?file=api&amp;v=2.x&amp;key=<?php echo isset($googlemapsAPI) ? $googlemapsAPI : $row_rsPreferences['googlemapsAPI']; ?>"></script>
<script src="/core/scripts/googlemaps/googlemap.js" ></script>
<script src="/core/scripts/googlemaps/fb_maps.js" ></script>
<script>
//<![CDATA[

var initLatitude = <?php echo $row_rsEvent['latitude']; ?>;
var initLongitude = <?php echo $row_rsEvent['longitude']; ?>;
var initZoom = <?php echo 13; ?>;
var initMapType = G_NORMAL_MAP;
var showMapType = true;
var markerLatitude = <?php echo $row_rsEvent['latitude']; ?>;
var markerLongitude = <?php echo $row_rsEvent['longitude']; ?>;
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
<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" -->
    <div class="container pageBody events">
      <div class="crumbs"><div><span class="you_are_in">You are in: </span><a href="/">Home</a><span class="separator">&nbsp;&rsaquo;&nbsp;</span><a href="/news/">News &amp; Events</a><span class="separator">&nbsp;&rsaquo;&nbsp;</span><a href="/calendar/">Diary</a><span class="separator">&nbsp;&rsaquo;&nbsp;</span><?php if(isset($row_rsEvent['title'])) {  ?><a href="/calendar/index.php?categoryID=<?php echo $row_rsEvent['categoryID']; ?>"><?php echo $row_rsEvent['title']; ?></a><span class="separator">&nbsp;&rsaquo;&nbsp;</span><?php } ?><?php echo $row_rsEvent['eventtitle']; ?></div></div>
      <h1><?php if(isset($row_rsEvent['imageURL'])) { ?>
        <img src="<?php echo getImageURL($row_rsEvent['imageURL'], "medium"); ?>" alt="<?php echo $row_rsEvent['eventtitle']; ?>" id = "eventImage" class="fltrt medium" />
        <?php } ?><?php echo $row_rsEvent['eventtitle']; ?></h1>
     <?php require_once(SITE_ROOT.'core/includes/alert.inc.php'); ?>
     
       <h2>When?</h2>
      <p><?php echo $datedescription; ?>
       
    </p> <p><a href="/calendar/icalendar.php?eventID=<?php echo $row_rsEvent['ID']; ?>" rel="nofollow">Add to Outlook or iCal calendar</a></p>
        <?php if (isset($row_rsEvent['locationname'])) { ?>
        <h2> Where?</h2>
      <p><?php echo isset($row_rsEvent['locationname']) ? $row_rsEvent['locationname']."<br>" : "";  ?>
      <?php echo isset($row_rsEvent['address1']) ? $row_rsEvent['address1'].", " : "";  ?>
      <?php echo isset($row_rsEvent['address2']) ? $row_rsEvent['address2'].", " : "";  ?>
      <?php echo isset($row_rsEvent['address3']) ? $row_rsEvent['address3'].", " : "";  ?>
      <?php echo isset($row_rsEvent['address4']) ? $row_rsEvent['address4'].", " : "";  ?>
      <?php echo isset($row_rsEvent['address5']) ? $row_rsEvent['address5'].", " : "";  ?>
      <?php echo isset($row_rsEvent['postcode']) ? $row_rsEvent['postcode'] : "";  ?></p>
      <?php } ?>
      <?php if(isset($row_rsEvent['latitude'])) { // map exists ?>
     <div class="googlemap" id="googlemap"></div>
	  <?php } ?>
 <h2>Details:</h2>
 
      <div id="eventDetails" class="clearfix"><?php echo (trim($row_rsEventPrefs['customfield1'].$row_rsEvent['customvalue1'])!="") ? "<p><strong>".$row_rsEventPrefs['customfield1'].":</strong> ".$row_rsEvent['customvalue1']."</p>" : ""; ?><?php echo (trim($row_rsEventPrefs['customfield2'].$row_rsEvent['customvalue2'])!="") ? "<p><strong>".$row_rsEventPrefs['customfield2'].":</strong> ".$row_rsEvent['customvalue2']."</p>" : ""; ?><?php // contains html or not...
	  echo (strpos($row_rsEvent['eventdetails'],"</")!==false) ? $row_rsEvent['eventdetails'] : nl2br($row_rsEvent['eventdetails']); ?></div>
      <?php if($totalRows_rsAlreadyRegistered>1) { ?>
    <p>You are registered for this event.</p>
    <?php }  if($row_rsEvent['registration']==1 && ((isset($_SESSION['MM_UserGroup']) && $_SESSION['MM_UserGroup']>7) || (isset($row_rsEvent['startdatetime']) && $row_rsEvent['startdatetime'] > date('Y-m-d H:i:s') && (!isset($row_rsEvent['registrationstart']) || $row_rsEvent['registrationstart'] <= date('Y-m-d H:i:s')) && (!isset($row_rsEvent['registrationend']) || $row_rsEvent['registrationend'] >= date('Y-m-d H:i:s')) && ($row_rsEvent['registrationmax'] == 0 || $row_rsTotalResgistrants['numRegistered'] <= $row_rsEvent['registrationmax'])))) { ?>
     <p class="register"><a href="registration/index.php?eventID=<?php echo $row_rsEvent['ID']; ?>"  rel="nofollow"><?php echo $row_rsEventPrefs['text_register']; ?></a></p>
     <?php } ?>
     <?php if(isset($row_rsEvent['rsvp']) && $row_rsEvent['rsvp']==1 && (!isset($row_rsEvent['rsvpdatetime']) || $row_rsEvent['rsvpdatetime'] < date('Y-m-d H:i:s'))) { ?><h2>RSVP</h2>
     <?php if(isset($row_rsUser['ID'])) { ?>
    <p>Will you be attending this event?</p>
    <form action="attend.php" method="post" name="form1" id="form1" role="form">
      
     <label>
        <input name="statusID" type="radio" id="attend_0" value="1" <?php if($rowStatus['statusID'] ==1 || !isset($rowStatus['statusID'])) { echo "checked=\"checked\"";} ?> />
        Yes</label>
      <br />
      <label>
        <input type="radio" name="statusID" value="2" id="attend_1" <?php if($rowStatus['statusID'] ==2) { echo "checked=\"checked\""; }  ?> />
        No</label>
      <br />
      <label>
        <input type="radio" name="statusID" value="0" id="attend_2" <?php if($rowStatus['statusID'] ==0) { echo "checked=\"checked\""; }  ?> />
        Maybe</label>
      <br />  <br />
      <input type="submit" value="Send..." />
      <input name="userID" type="hidden" id="userID" value="<?php echo $row_rsUser['ID']; ?>" />
      <input name="eventID" type="hidden" id="eventID" value="<?php echo $row_rsEvent['ID']; ?>" />
      <input name="email" type="hidden" id="email" value="<?php echo $row_rsUser['email']; ?>" />
     
    
    </form>  
   
    <?php } else { ?>
    <p>You can only RSVP if you are <a href="/login/index.php?accesscheck=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>">logged in</a> or by clicking on the link in  your invitation email.</p>
    <?php } } ?>
     
<p class="back"><a href="<?php echo (isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER']!="" ) ? $_SERVER['HTTP_REFERER'] : "index.php"; ?>" class="link_back"><i class="glyphicon glyphicon-arrow-left"></i> Back</a></p>
  </div>
      <!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsEvent);

mysql_free_result($rsAlreadyRegistered);

mysql_free_result($rsTotalResgistrants);

if(is_resource($rsUser)) mysql_free_result($rsUser);

mysql_free_result($rsEventPrefs);

mysql_free_result($rsMerge);
?>
