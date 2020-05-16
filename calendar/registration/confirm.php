<?php require_once('../../Connections/aquiescedb.php'); ?>
<?php

$regionID = (isset($regionID)  && intval($regionID)>0) ? intval($regionID) : 1;

if(!isset($_GET['registrationID']) || !isset($_GET['token']) || $_GET['token'] !=md5(PRIVATE_KEY.$_GET['registrationID'])) {
	die("Sorry, the link was invalid.");
}


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

if(isset($_GET['cancel'])) {
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$update = "UPDATE eventregistration SET statusID = 2 WHERE ID = ".intval($_GET['registrationID']);
	mysql_query($update, $aquiescedb) or die(mysql_error().": ".$update);
	$msg = "Your registration has been cancelled.";
	if(isset($_GET['cancelteam'])) {
		$update = "UPDATE eventregistration SET statusID = 2 WHERE withregistrationID = ".intval($_GET['registrationID']);
		mysql_query($update, $aquiescedb) or die(mysql_error().": ".$update);
	}
}


$varRegistrationID_rsRegistation = "-1";
if (isset($_GET['registrationID'])) {
  $varRegistrationID_rsRegistation = $_GET['registrationID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsRegistation = sprintf("SELECT eventregistration.ID, eventregistration.registrationnumber, eventregistration.eventID, COUNT(teamregistration.withregistrationID) AS teamcount, eventregistration.statusID, event.startdatetime, eventgroup.eventtitle FROM eventregistration LEFT JOIN eventregistration AS teamregistration ON (teamregistration.withregistrationID = eventregistration.ID) LEFT JOIN event ON (eventregistration.eventID = event.ID) LEFT JOIN eventgroup ON (eventgroup.ID = event.eventgroupID) WHERE eventregistration.ID = %s GROUP BY eventregistration.ID ", GetSQLValueString($varRegistrationID_rsRegistation, "int"));
$rsRegistation = mysql_query($query_rsRegistation, $aquiescedb) or die(mysql_error());
$row_rsRegistation = mysql_fetch_assoc($rsRegistation);
$totalRows_rsRegistation = mysql_num_rows($rsRegistation);

$colname_rsTeam = "-1";
if (isset($_GET['registrationID'])) {
  $colname_rsTeam = $_GET['registrationID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsTeam = sprintf("SELECT eventregistration.*, users.firstname, users.surname FROM eventregistration LEFT JOIN users ON (eventregistration.userID = users.ID) WHERE withregistrationID = %s", GetSQLValueString($colname_rsTeam, "int"));
$rsTeam = mysql_query($query_rsTeam, $aquiescedb) or die(mysql_error());
$row_rsTeam = mysql_fetch_assoc($rsTeam);
$totalRows_rsTeam = mysql_num_rows($rsTeam);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsEventPrefs = "SELECT * FROM eventprefs WHERE ID = ".$regionID."";
$rsEventPrefs = mysql_query($query_rsEventPrefs, $aquiescedb) or die(mysql_error());
$row_rsEventPrefs = mysql_fetch_assoc($rsEventPrefs);
$totalRows_rsEventPrefs = mysql_num_rows($rsEventPrefs);


 ?><?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>

<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Cancel Reservation"; echo $pageTitle." | ".$site_name; ?>
</title>
<!--[if IE]><![endif]-->
<!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<style>
<!--
-->
</style>
<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" --><div class="container pageBody"><h1>Event Sign Up</h1>
      <p>You are now registered for the following event:</p><h2><?php echo $row_rsRegistation['eventtitle']; ?></h2>
      <h3><?php echo date('d M Y g:ia', strtotime($row_rsRegistation['startdatetime'])); ?></h3>
      <p><a href="../event.php?eventID=<?php echo $row_rsRegistation['eventID']; ?>">View event details</a></p>
      <h2>Registrants</h2>
      <table class="form-table">
        <tr>
         
          <th>No.&nbsp;</th>
          <th>Name</th>
        </tr>
        <?php do { ?>
          <tr>
           
            <td><?php echo $row_rsTeam['registrationnumber']; ?></td>
            <td><?php echo $row_rsTeam['firstname']; ?> <?php echo $row_rsTeam['surname']; if($totalRows_rsTeam>1 && $row_rsTeam['ID']==$row_rsTeam['withregistrationID']) echo" (main registrant)"; if($row_rsTeam['statusID']==2) echo " CANCELLED"; ?></td>
          </tr>
          <?php } while ($row_rsTeam = mysql_fetch_assoc($rsTeam)); ?>
      </table>
      <?php if($row_rsEventPrefs['allowcancelregistration']==1) { ?>
      <div class="cancel_registration">
<h2>Cancelling your registration</h2><?php require_once('../../core/includes/alert.inc.php'); ?>
        <p>You can bookmark this page if you may want to cancel in future</p>
      <form>
        <p>If you wish to cancel your reservation for the above event, please click on the button below:</p>
<button type="submit" onClick="return confirm('Are you sure you want to cancel your reservation?');">Cancel&nbsp;Reservation</button>
<input type="hidden" name="registrationID" value="<?php echo htmlentities($_GET['registrationID'],ENT_COMPAT, "UTF-8"); ?>">
<input type="hidden" name="token" value="<?php echo htmlentities($_GET['token'],ENT_COMPAT, "UTF-8"); ?>">
<input type="hidden" name="cancel" value="true">&nbsp;
&nbsp;
<?php if($row_rsRegistation['teamcount']>1) { ?><label>You have <?php echo $row_rsRegistation['teamcount']-1; ?> additional registrations  to main registrant - check box to cancel these also: 
<input name="cancelteam" type="checkbox" value="true"></label><?php } ?></form></div><?php } ?></div><!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsRegistation);

mysql_free_result($rsTeam);

mysql_free_result($rsEventPrefs);
?>
