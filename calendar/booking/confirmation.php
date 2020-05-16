<?php require_once('../../Connections/aquiescedb.php'); ?>
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

$colname_rsBookingDetails = "-1";
if (isset($_GET['bookingID'])) {
  $colname_rsBookingDetails = $_GET['bookingID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsBookingDetails = sprintf("SELECT bookinginstance.ID, bookinginstance.bookedfor, bookinginstance.confirmed, bookinginstance.paymentrequired, bookinginstance.`currency`, bookinginstance.price, bookinginstance.pricepaid, bookinginstance.deposit, bookinginstance.depositpaid, bookinginstance.startdatetime, bookinginstance.enddatetime, bookinginstance.createdbyID, bookinginstance.createddatetime, bookinginstance.statusID, bookingresource.title, bookingresource.ID AS resourceID, users.firstname, users.surname, location.mapURL, bookinginstance.recurring FROM bookinginstance LEFT JOIN bookingresource ON (bookinginstance.resourceID = bookingresource.ID) LEFT JOIN users ON (bookinginstance.createdbyID = users.ID) LEFT JOIN location ON (bookingresource.locationID = location.ID) WHERE bookinginstance.ID = %s", GetSQLValueString($colname_rsBookingDetails, "int"));
$rsBookingDetails = mysql_query($query_rsBookingDetails, $aquiescedb) or die(mysql_error());
$row_rsBookingDetails = mysql_fetch_assoc($rsBookingDetails);
$totalRows_rsBookingDetails = mysql_num_rows($rsBookingDetails);
?><?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Booking Confirmation"; echo $pageTitle." | ".$site_name; ?></title>
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
  <div class="crumbs"><div>You are in: <a href="../index.php">Home</a><span class="separator">&nbsp;&rsaquo;&nbsp;</span><a href="index.php">Booking</a><span class="separator">&nbsp;&rsaquo;&nbsp;</span> <a href="month.php?resourceID=<?php echo $row_rsBookingDetails['resourceID']; ?>"><?php echo $row_rsBookingDetails['title']; ?></a><span class="separator">&nbsp;&rsaquo;&nbsp;</span>Confirmation</div></div>
  <h1>Thank you!</h1>
  <p>Your booking request has been received for <?php echo $row_rsBookingDetails['title']; ?>. Details below:</p>
  <p>Booked for: <?php echo $row_rsBookingDetails['bookedfor']; ?> by <?php echo $row_rsBookingDetails['firstname']; ?> <?php echo $row_rsBookingDetails['surname']; ?></p>
  <p>From: <?php echo date('D d M Y g.i a',strtotime($row_rsBookingDetails['startdatetime'])); ?> until: <?php echo date('D d M Y g.i a',strtotime($row_rsBookingDetails['enddatetime'])); ?></p>
  <?php if($row_rsBookingDetails['recurring'] ==1) { ?><p>YOU HAVE REQUESTED A RECURRING BOOKING - THIS WILL REQUIRE EXTRA AUTHORISATION</p><?php } ?>
  <p>We will contact you  with a confirmation once verified.</p>
  <h2>Payment</h2>
  <?php if ($row_rsBookingDetails['paymentrequired'] != 1) { ?>
  <p>No  payment required now. We will contact you with details of any fees.</p>
  <?php } else { ?>
  <p>Your booking will be valid once payment is made. You will be invoiced in the next few days. </p>
  <p>Online payment is not available at present.</p>
  <?php } ?><?php if (isset($row_rsBookingDetails['mapURL']) && $row_rsBookingDetails['mapURL']!="") { ?><h2>Map</h2>
      <p><?php echo $row_rsBookingDetails['mapURL']; ?></p><?php } ?>
  <!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsBookingDetails);
?>
