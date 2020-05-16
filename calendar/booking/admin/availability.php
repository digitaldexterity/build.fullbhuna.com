<?php require_once('../../../Connections/aquiescedb.php'); ?>
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

// MOVED UP TO CATCH OVERLAPS
$varResourceID_rsBookingOverlap = "-1";
if (isset($_GET['resourceID'])) {
  $varResourceID_rsBookingOverlap = $_GET['resourceID'];
}
$varEndDate_rsBookingOverlap = "2010-10-01";
if (isset($_POST['enddatetime'])) {
  $varEndDate_rsBookingOverlap = $_POST['enddatetime'];
}
$varStartDate_rsBookingOverlap = "1970-01-01";
if (isset($_POST['startdatetime'])) {
  $varStartDate_rsBookingOverlap = $_POST['startdatetime'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsBookingOverlap = sprintf("SELECT bookinginstance.bookedfor, bookinginstance.confirmed, bookinginstance.startdatetime, bookinginstance.enddatetime, bookinginstance.ID FROM bookinginstance WHERE bookinginstance.resourceID = %s AND bookinginstance.statusID < 2 AND ((bookinginstance.startdatetime >= %s AND bookinginstance.startdatetime <= %s) OR (bookinginstance.enddatetime >= %s AND bookinginstance.enddatetime <= %s) OR (bookinginstance.startdatetime < %s AND bookinginstance.enddatetime > %s))", GetSQLValueString($varResourceID_rsBookingOverlap, "int"),GetSQLValueString($varStartDate_rsBookingOverlap, "date"),GetSQLValueString($varEndDate_rsBookingOverlap, "date"),GetSQLValueString($varStartDate_rsBookingOverlap, "date"),GetSQLValueString($varEndDate_rsBookingOverlap, "date"),GetSQLValueString($varStartDate_rsBookingOverlap, "date"),GetSQLValueString($varEndDate_rsBookingOverlap, "date"));
$rsBookingOverlap = mysql_query($query_rsBookingOverlap, $aquiescedb) or die(mysql_error());
$row_rsBookingOverlap = mysql_fetch_assoc($rsBookingOverlap);
$totalRows_rsBookingOverlap = mysql_num_rows($rsBookingOverlap);

// MOVED UP TO CATCH OVERLAPS
if (isset($_POST["MM_insert"]) && $_POST["MM_insert"] == "booking") { // check for post errors
if ($totalRows_rsBookingOverlap > 0  ) $error = "Your booking overlaps with another - please try again";
$timeDiff = (strtotime($_POST['enddatetime']) - strtotime($_POST['startdatetime']))/60/60;
if ($timeDiff < 0) $error = "Your finish time is before your start time";
if (isset($_POST['maxHours']) && $_POST['maxHours'] > 0 && $timeDiff > $_POST['maxHours']) {
$error = "Your booking is ".$timeDiff." hours. This is greater than the maximum allowed for this resource of ".$_POST['maxHours']." hours.";
} 
if ($error) unset($_POST["MM_insert"]);
}
// end errors

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "booking")) {
  $insertSQL = sprintf("INSERT INTO bookinginstance (resourceID, bookedfor, startdatetime, enddatetime, createdbyID, createddatetime, statusID) VALUES (%s, %s, %s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['resourceID'], "int"),
                       GetSQLValueString($_POST['bookingfor'], "text"),
                       GetSQLValueString($_POST['startdatetime'], "date"),
                       GetSQLValueString($_POST['enddatetime'], "date"),
                       GetSQLValueString($_POST['createdbyID'], "int"),
                       GetSQLValueString($_POST['createddatetime'], "date"),
                       GetSQLValueString($_POST['statusID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($insertSQL, $aquiescedb) or die(mysql_error());
}

if (isset($_POST['startdatetime'])) { $startDate = $_POST['startdatetime']; } else if (isset($_GET['startDate'])) { $startDate = $_GET['startDate']." 09:00:00"; } else { $startDate = date('Y-m-d H:i:s'); } // set date


$varResourceID_rsBooked = "-1";
if (isset($_GET['resourceID'])) {
  $varResourceID_rsBooked = $_GET['resourceID'];
}
$varStartDate_rsBooked = "2008-02-06 09:00:00";
if (isset($startDate)) {
  $varStartDate_rsBooked = $startDate;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsBooked = sprintf("SELECT bookinginstance.bookedfor, bookinginstance.confirmed, bookinginstance.startdatetime, bookinginstance.enddatetime, bookinginstance.ID FROM bookinginstance WHERE bookinginstance.resourceID = %s AND ((bookinginstance.startdatetime >= %s AND bookinginstance.startdatetime <= DATE_ADD(%s, INTERVAL 7 DAY)) OR (bookinginstance.enddatetime >= %s AND bookinginstance.enddatetime <= DATE_ADD(%s, INTERVAL 7 DAY)) OR (bookinginstance.startdatetime < %s AND bookinginstance.enddatetime > DATE_ADD(%s, INTERVAL 7 DAY)))", GetSQLValueString($varResourceID_rsBooked, "int"),GetSQLValueString($varStartDate_rsBooked, "date"),GetSQLValueString($varStartDate_rsBooked, "date"),GetSQLValueString($varStartDate_rsBooked, "date"),GetSQLValueString($varStartDate_rsBooked, "date"),GetSQLValueString($varStartDate_rsBooked, "date"),GetSQLValueString($varStartDate_rsBooked, "date"));
$rsBooked = mysql_query($query_rsBooked, $aquiescedb) or die(mysql_error());
$row_rsBooked = mysql_fetch_assoc($rsBooked);
$totalRows_rsBooked = mysql_num_rows($rsBooked);

$colname_rsResource = "-1";
if (isset($_GET['resourceID'])) {
  $colname_rsResource = $_GET['resourceID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsResource = sprintf("SELECT * FROM bookingresource WHERE ID = %s", GetSQLValueString($colname_rsResource, "int"));
$rsResource = mysql_query($query_rsResource, $aquiescedb) or die(mysql_error());
$row_rsResource = mysql_fetch_assoc($rsResource);
$totalRows_rsResource = mysql_num_rows($rsResource);

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID, firstname, surname FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" --><title><?php $pageTitle = "Bookings: ".$row_rsResource['title']; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title><!-- InstanceEndEditable -->
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
    <!-- InstanceBeginEditable name="Body" -->
   <div class="page calendar">
   <h1>Bookings: <?php echo $row_rsResource['title']; ?></h1>
   <?php if (isset($error)) { ?><p class="alert alert-danger" role="alert"><?php echo $error; ?></p><?php } ?>

	      This resource is generally available:&nbsp;
          <?php if (stristr($row_rsResource['availabledow'],"1")) {echo "Mon&nbsp;";} if (stristr($row_rsResource['availabledow'],"2")) {echo "Tue&nbsp;";} if (stristr($row_rsResource['availabledow'],"3")) {echo "Wed&nbsp;";} if (stristr($row_rsResource['availabledow'],"4")) {echo "Thu&nbsp;";} if (stristr($row_rsResource['availabledow'],"5")) {echo "Fri&nbsp;";} if (stristr($row_rsResource['availabledow'],"6")) {echo "Sat&nbsp;";} if (stristr($row_rsResource['availabledow'],"7")) {echo "Sun&nbsp;";} ?>
          &nbsp;between&nbsp;<?php echo date('H:i',strtotime($row_rsResource['availablestart'])); ?>&nbsp;and&nbsp;<?php echo date('H:i',strtotime($row_rsResource['availableend'])); ?>
          <?php if((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "booking") && !isset($error)) { ?> <p><strong>Your booking has been  made! (See below)</strong>.<br />
            Remember it will only be available in the generally available times above.</p>
	      <?php } ?>
	        <h2>Booked:</h2><p>Week commencing: <?php echo date('l jS F Y', strtotime($startDate)); ?>	      
		     <?php
		 $next7 = date('Y-m-d',(strtotime($startDate)+7*24*60*60));
		 $prev7 = date('Y-m-d',(strtotime($startDate)-7*24*60*60));
		 $resourceID = isset($_GET['resourceID']) ? "&resourceID=".intval($_GET['resourceID']) : "";
		 ?>
&laquo;&nbsp;<a href="availability.php?startDate=<?php echo $prev7.$resourceID; ?>" rel="prev">Previous 7 days</a> | <a href="availability.php?startDate=<?php echo $next7.$resourceID; ?>" rel="next">Next 7 days</a>&nbsp;&raquo;</p>
   
  
   
   <?php if ($totalRows_rsBooked == 0) { // Show if recordset empty ?>
     <p>This resource is not booked during this period.</p>
     <?php } // Show if recordset empty ?>
   <?php if ($totalRows_rsBooked > 0) { // Show if recordset not empty ?>
  <table border="0" cellpadding="0" cellspacing="0" class="listTable">
    <tr>
      <td>&nbsp;</td>
      <td><strong>Booked for:</strong></td>
          <td><strong>From:</strong></td>
          <td>&nbsp;</td>
          <td><strong>Until:</strong></td><td>&nbsp;</td>
        </tr>
    <?php do { ?>
      <tr>
        <td><?php if (($row_rsBooked['stuatusID'] >= 1)) { ?><img src="../../../core/images/icons/green-light.png" alt="This booking is confirmed" style="vertical-align:
middle;" /><?php } else if (($row_rsBooked['stuatusID'] == 0)) { ?><img src="../../../core/images/icons/amber-light.png" alt="This booking has not been confirmed" width="16" height="16" style="vertical-align:
middle;" /><?php } else { ?><img src="../../../core/images/icons/red-light.png" alt="This booking has been refused or cancelled" width="16" height="16" style="vertical-align:
middle;" />
          <?php } ?></td>
        <td><?php echo $row_rsBooked['bookedfor']; ?></td>
        <td><?php echo date('D d M Y g.i a',strtotime($row_rsBooked['startdatetime'])); ?></td><td>&raquo;&raquo;</td>
        <td><?php echo date('D d M Y g.i a',strtotime($row_rsBooked['enddatetime'])); ?></td>
        <td><strong><a href="update_booking.php?resourceID=<?php echo $row_rsResource['ID']; ?>&amp;bookingID=<?php echo $row_rsBooked['ID']; ?>" class="link_edit icon_only">Edit</a></strong></td>
        </tr>
      <?php } while ($row_rsBooked = mysql_fetch_assoc($rsBooked)); ?>
  </table>
  <?php } // Show if recordset not empty ?>
<h2>Book:</h2>
   <form action="<?php echo $editFormAction; ?>" method="POST" name="booking" id="booking" role="form">
    
     <table border="0" cellpadding="0" cellspacing="0" class="form-table">
       <tr>
         <td>For: </td>
         <td><input name="bookingfor" type="text"  id="bookingfor" value="<?php echo isset($_POST['bookingfor']) ? $_POST['bookingfor'] : $row_rsLoggedIn['firstname']." ".$row_rsLoggedIn['surname']; ?>" size="30" maxlength="30" /></td>
       </tr>
       <tr>
         <td>From: </td>
         <td><input type="hidden" name="startdatetime" value="<?php $setvalue = $startDate; echo $setvalue; ?>"  size="32" />
           <?php $time = true; $showPicker = true; $inputname = "startdatetime"; $formName = "booking"; include("../../../core/includes/datetimeinput.inc.php"); ?></td>
       </tr>
       <tr>
         <td>Until: </td>
         <td><input type="hidden" name="enddatetime" value="<?php $setvalue = isset($_POST['enddatetime']) ? $_POST['enddatetime'] : $startDate; echo $setvalue; ?>" size="32" />
           <?php $time = true; $inputname = "enddatetime"; include("../../../core/includes/datetimeinput.inc.php"); ?>
           <input type="submit" class="button" value="Book" />
           <input name="createdbyID" type="hidden" id="createdbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
           <input name="createddatetime" type="hidden" id="createddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
           <input name="resourceID" type="hidden" id="resourceID" value="<?php echo intval($_GET['resourceID']); ?>" />
           <input name="statusID" type="hidden" id="statusID" value="1" />
          <input type="hidden" name="MM_insert" value="booking" />
          <input name="maxHours" type="hidden" id="maxHours" value="<?php echo $row_rsResource['maxHours']; ?>" /></td>
       </tr>
     </table>
     </form></div>
   <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsBooked);

mysql_free_result($rsResource);

mysql_free_result($rsLoggedIn);

mysql_free_result($rsBookingOverlap);
?>


