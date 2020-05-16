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

$varBookingID_rsBooking = "-1";
if (isset($_GET['bookingID'])) {
  $varBookingID_rsBooking = $_GET['bookingID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsBooking = sprintf("SELECT bookinginstance.ID, bookinginstance.resourceID, bookinginstance.bookedfor, bookinginstance.paymentrequired, bookinginstance.`currency`, bookinginstance.price, bookinginstance.deposit, bookingresource.title, bookinginstance.startdatetime, bookingresource.paymentnotes FROM bookinginstance LEFT JOIN bookingresource ON (bookinginstance.resourceID = bookingresource.ID) WHERE bookinginstance.ID = %s ORDER BY bookinginstance.createddatetime DESC", GetSQLValueString($varBookingID_rsBooking, "int"));
$rsBooking = mysql_query($query_rsBooking, $aquiescedb) or die(mysql_error());
$row_rsBooking = mysql_fetch_assoc($rsBooking);
$totalRows_rsBooking = mysql_num_rows($rsBooking);
?><?php if ($row_rsBooking['paymentrequired'] != 1) { // no payment required so go to confirmation
header("location: confirmation.php?bookingID=".$row_rsBooking['ID']); exit;
} ?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Booking Payment"; echo $pageTitle." | ".$site_name; ?></title>
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
  <div class="crumbs"><div>You are in: <a href="../../index.php">Home</a><span class="separator">&nbsp;&rsaquo;&nbsp;</span><a href="index.php">Booking</a><span class="separator">&nbsp;&rsaquo;&nbsp;</span><?php echo $row_rsBooking['title']; ?><span class="separator">&nbsp;&rsaquo;&nbsp;</span>Payment</div></div>
  <h1><?php echo $row_rsBooking['title']; ?></h1>
  <h2>Payment</h2>
  <p><?php echo $row_rsBooking['paymentnotes']; ?></p>
  <p>You will be invoiced for payment soon.</p>
  <p><a href="confirmation.php?bookingID=<?php echo $row_rsBooking['ID']; ?>" >Click to continue</a></p>
  <!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsBooking);
?>
