<?php require_once('../Connections/aquiescedb.php'); ?>
<?php require_once('../Connections/aquiescedb.php'); ?>
<?php require_once('../Connections/aquiescedb.php'); 
// detect user either by email sent in URL - possibley from group email or logged in



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
?>
<?php
$colname_rsEvent = "-1";
if (isset($_GET['eventID'])) {
  $colname_rsEvent = $_GET['eventID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsEvent = sprintf("SELECT event.ID, eventgroup.eventtitle, event.startdatetime, location.locationname, location.address1, location.address2, location.address3, location.address4, location.address5, location.postcode, location.telephone1, location.latitude FROM event LEFT JOIN eventgroup ON (event.eventgroupID = eventgroup.ID) LEFT JOIN location ON (event.eventlocationID = location.ID) WHERE event.ID = %s", GetSQLValueString($colname_rsEvent, "int"));
$rsEvent = mysql_query($query_rsEvent, $aquiescedb) or die(mysql_error());
$row_rsEvent = mysql_fetch_assoc($rsEvent);
$totalRows_rsEvent = mysql_num_rows($rsEvent);

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
$query_rsEventPrefs = "SELECT * FROM eventprefs";
$rsEventPrefs = mysql_query($query_rsEventPrefs, $aquiescedb) or die(mysql_error());
$row_rsEventPrefs = mysql_fetch_assoc($rsEventPrefs);
$totalRows_rsEventPrefs = mysql_num_rows($rsEventPrefs);


if(isset($row_rsEvent['ID']) && isset($row_rsUser['ID'])) { // get current RSVP status (if any)
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$select = "SELECT ID, statusID FROM eventattend WHERE eventID = ".$row_rsEvent['ID']." AND userID = ".$row_rsUser['ID'];
	$result = mysql_query($select, $aquiescedb) or die(mysql_error());
	$rowStatus = mysql_fetch_assoc($result);

}

if(isset($_REQUEST['statusID']) && isset($row_rsEvent['ID']) && isset($row_rsUser['ID'])) { 
	mysql_select_db($database_aquiescedb, $aquiescedb);
	if(!isset($rowStatus['statusID'])) { // new addition
	$insert = "INSERT INTO eventattend (eventID, userID, statusID, createdbyID, createddatetime) VALUES (".$row_rsEvent['ID'].",".$row_rsUser['ID'].",".intval($_REQUEST['statusID']).",".$row_rsUser['ID'].",NOW())";
	$result = mysql_query($insert, $aquiescedb) or die(mysql_error());
	} else { // updated addition
	$update = "UPDATE eventattend SET statusID = ".intval($_REQUEST['statusID'])." WHERE ID = ".$rowStatus['ID'];
	$result = mysql_query($update, $aquiescedb) or die(mysql_error());
	}
	switch($_REQUEST['statusID']) {
		case 1 : $status = "WILL"; break;
		case 2 : $status = "WILL NOT"; break;
		default : $status = "MIGHT";
	}
	
	$to = $_REQUEST['email'];
	$subject = "Your attendance ".$row_rsEvent['eventtitle'];
	$message = "Thank you,\n\nWe can confirm that we have received your attendance choice that you ".$status." attend the ".$row_rsEvent['eventtitle']." event on ".date('l jS F Y', strtotime($row_rsEvent['startdatetime']))." at ".date('g:ia', strtotime($row_rsEvent['startdatetime']))."."; 
	$message2 = "";
	if($_REQUEST['statusID']!=2) {
	$message2 .= "\n\nLocation:\n";
	$message2 .= $row_rsEvent['locationname']."\n";
	$message2 .= isset($row_rsEvent['address1']) ? $row_rsEvent['address1']."\n" : "";
	$message2 .= isset($row_rsEvent['address2']) ? $row_rsEvent['address2']."\n" : "";
	$message2 .= isset($row_rsEvent['address3']) ? $row_rsEvent['address3']."\n" : "";
	$message2 .= isset($row_rsEvent['address4']) ? $row_rsEvent['address4']."\n" : "";
	$message2 .= isset($row_rsEvent['postcode']) ? $row_rsEvent['postcode']."\n" : "";
	$message2 .= isset($row_rsEvent['telephone1']) ? "Telephone: ".$row_rsEvent['telephone1']."\n" : "";
	$message2 .="\n\nTo add to your Outlook or iCal calendar, click here:\n";
	$message2 .= (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == "on") ? "https://" : "http://";
	$message2 .= $_SERVER['HTTP_HOST']."/calendar/icalendar.php?eventID=".$row_rsEvent['ID']."\n\n";
	$message2 .="For more information on this event click here:\n";
	$message2 .= (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == "on") ? "https://" : "http://";
	$message2 .= $_SERVER['HTTP_HOST']."/calendar/event.php?eventID=".$row_rsEvent['ID']; }
	$message2 .="\n\nRegards,\n\n".$site_name;
	require_once('../mail/includes/sendmail.inc.php');
	$friendlyfrom = $site_name;
	sendMail($to, $subject, $message.$message2, "", $friendlyfrom);
	
	
}
if(isset($_GET['returnURL']) && $_GET['returnURL'] !="" ) {
			   header("location: ".$_GET['returnURL']); exit;
			   }
			   
			   $accesslevel = $row_rsEventPrefs['accesslevel'];
			   if(is_readable("../members/includes/restrictaccess.inc.php")) require_once('../members/includes/restrictaccess.inc.php');
?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Attend Event - ".$row_rsEvent['eventtitle']; echo $pageTitle." | ".$site_name; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script src="../SpryAssets/SpryValidationRadio.js"></script>
<link href="../SpryAssets/SpryValidationRadio.css" rel="stylesheet"  />
<link href="css/calendarDefault.css" rel="stylesheet"  />
<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" -->
    <h1 class="calheader">Attendance Confirmation</h1>
    <h2><?php echo $row_rsEvent['eventtitle']; ?> on <?php echo date('l jS F Y', strtotime($row_rsEvent['startdatetime'])); ?> at <?php echo date('g:ia', strtotime($row_rsEvent['startdatetime'])); ?></h2>
    <?php if(isset($message)) { ?>
    <p><?php echo nl2br($message); ?></p>
    <?php if($_REQUEST['statusID']!=2) { ?>
    <p>You should receive a confirmation email with event details shortly.</p>
    <p><a href="event.php?eventID=<?php echo $row_rsEvent['ID']; ?>" >Click here for more details</a></p>
    <?php } ?>
    <?php } else if(isset($row_rsUser['ID'])) { ?>
    <h2>RSVP</h2><p>Will you be attending this event?</p>
    <form action="attend.php" method="post" name="form1" id="form1" role="form">
      <span id="spryradio1">
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
      <br />
      <span class="radioRequiredMsg">Please make a selection.</span></span>
    </form>
    <p>&nbsp;</p>
    <script>
var spryradio1 = new Spry.Widget.ValidationRadio("spryradio1");
    </script>
    <?php } else { ?>
    <p>You can only RSVP if you are <a href="/login/index.php?accesscheck=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>">logged in</a> or by clicking on the link in  your invitation email.</p>
    <?php } ?>
  <!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsEvent);

mysql_free_result($rsUser);

mysql_free_result($rsEventPrefs);
?>
