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

if (isset($_POST['startdatetime'])) { $date = $_POST['startdatetime']; } else if (isset($_GET['date'])) { $date = $_GET['date']." 00:00:00"; } else { $date = date('Y-m-d 00:00:00'); } // set date
if (isset($_POST['enddatetime'])) { $enddate = $_POST['enddatetime']; } else if (isset($_GET['date'])) { $enddate = $_GET['date']." 23:55:00"; } else { $enddate = date('Y-m-d 23:55:00'); } // set date

$colname_rsThisResource = "-1";
if (isset($_GET['resourceID'])) {
  $colname_rsThisResource = $_GET['resourceID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsThisResource = sprintf("SELECT bookingresource.*, bookingcategory.`description` AS category, bookingresource.locationID, location.locationname FROM bookingresource LEFT JOIN bookingcategory ON (bookingresource.categoryID = bookingcategory.ID) LEFT JOIN location ON (bookingresource.locationID = location.ID) WHERE bookingresource.ID = %s", GetSQLValueString($colname_rsThisResource, "int"));
$rsThisResource = mysql_query($query_rsThisResource, $aquiescedb) or die(mysql_error());
$row_rsThisResource = mysql_fetch_assoc($rsThisResource);
$totalRows_rsThisResource = mysql_num_rows($rsThisResource);

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID, firstname, surname, users.usertypeID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

// MOVED UP TO CATCH OVERLAPS
// INSERT MOVED BELOW AS CHECKS USE DATABASE VALUES....
if (isset($_POST["MM_update"]) && $_POST["MM_update"] == "booking") {
// CHECH FOR  ERRORS
$error = ""; $loopstartdate = $date; $loopenddate = $enddate; $dow = $row_rsThisResource['availabledow'];
for($week=1; $week<=$_POST['recurring']; $week++) {
// repeat for number of weeks of recurring booking
$varRecurringID_rsBookingOverlap = "-1";
if (isset($_POST['recurringID'])) {
  $varRecurringID_rsBookingOverlap = $_POST['recurringID'];
}
$varResourceID_rsBookingOverlap = "-1";
if (isset($_GET['resourceID'])) {
  $varResourceID_rsBookingOverlap = $_GET['resourceID'];
}
$varStartDate_rsBookingOverlap = "-1";
if (isset($loopstartdate)) {
  $varStartDate_rsBookingOverlap = $loopstartdate;
}
$varEndDate_rsBookingOverlap = "-1";
if (isset($loopenddate)) {
  $varEndDate_rsBookingOverlap = $loopenddate;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsBookingOverlap = sprintf("SELECT bookinginstance.bookedfor, bookinginstance.confirmed, bookinginstance.startdatetime, bookinginstance.enddatetime, bookinginstance.ID FROM bookinginstance WHERE bookinginstance.statusID < 2 AND bookinginstance.resourceID = %s AND ((bookinginstance.startdatetime >= %s AND bookinginstance.startdatetime <= %s) OR (bookinginstance.enddatetime >= %s AND bookinginstance.enddatetime <= %s) OR (bookinginstance.startdatetime < %s AND bookinginstance.enddatetime > %s)) AND bookinginstance.recurringID != %s", GetSQLValueString($varResourceID_rsBookingOverlap, "int"),GetSQLValueString($varStartDate_rsBookingOverlap, "date"),GetSQLValueString($varEndDate_rsBookingOverlap, "date"),GetSQLValueString($varStartDate_rsBookingOverlap, "date"),GetSQLValueString($varEndDate_rsBookingOverlap, "date"),GetSQLValueString($varStartDate_rsBookingOverlap, "date"),GetSQLValueString($varEndDate_rsBookingOverlap, "date"),GetSQLValueString($varRecurringID_rsBookingOverlap, "int"));
$rsBookingOverlap = mysql_query($query_rsBookingOverlap, $aquiescedb) or die(mysql_error());
$row_rsBookingOverlap = mysql_fetch_assoc($rsBookingOverlap);
$totalRows_rsBookingOverlap = mysql_num_rows($rsBookingOverlap);


if (strtotime($date) - strtotime(date('Y-m-d 00:00:00')) < ($row_rsThisResource['minNotice']*60*60)) { // not enough in advance
$hours = $row_rsThisResource['minNotice'];
		 if (($hours/168) == number_format(($hours/168),0)) { // is weeks
		 $periodamount = $hours/168; $periodmultiple = ($periodamount == 1) ? "week" : $periodamount." weeks";
		 } else if (($hours/24) == number_format(($hours/24),0)) { // is days
		 	 $periodamount = $hours/24; $periodmultiple = ($periodamount == 1) ? "day" : $periodamount." days";
			 } else { // is hours
			 $periodamount = $hours; $periodmultiple = ($periodamount == 1) ? "hour" : $periodamount." hours";
			 }
		 
 $error .= "You need to book at least ".$periodmultiple." in advance. "; break;
 }//end in advance

 $daynum= date('w',strtotime($loopstartdate)) == 0 ? 7 : date('w',strtotime($loopstartdate)); // convert PHP ver 3 to ISO-8601 

if (stristr($dow,$daynum)===false) {// not bookable on this dow - although only checks first day of period so far
$error .= "This resource cannot be booked on ".date('l',strtotime($_POST['startdatetime']))."s. "; break;
}
 
if ($totalRows_rsBookingOverlap >0) { // overlap error 
$error .= "Your booking overlaps with another:<br />"; // overlap
do {  $error .= date('d/m/y H:i',strtotime($row_rsBookingOverlap['startdatetime']))." - ".date('d/m/y H:i',strtotime($row_rsBookingOverlap['enddatetime']))."<br />"; } while ($row_rsBookingOverlap = mysql_fetch_assoc($rsBookingOverlap));
}

if (isset($_POST['maxHours']) && $_POST['maxHours'] > 0 && $timeDiff > $_POST['maxHours']) { // longer than max time
$error .= "Your booking is ".$timeDiff." hours. This is greater than the maximum allowed for this resource of ".$_POST['maxHours']." hours. "; break;
} 

// end errors
  $loopstartdate = date('Y-m-d H:i:s',strtotime($loopstartdate . " +1 week"));   $loopenddate = date('Y-m-d H:i:s',strtotime($loopenddate . " +1 week"));
} // end for repeat recurring booking
} // end update error check

if ($error !="") unset($_POST["MM_update"]); // kill post if errors

if (isset($_POST['applyall']) && isset($_POST["MM_update"]) && $_POST["MM_update"] == "booking") { // do special all update
$update= "UPDATE bookinginstance SET bookedfor = ".GetSQLValueString($_POST['bookedfor'],"text").", modifiedbyID = ".GetSQLValueString($_POST['modifiedbyID'],"int").", modifieddatetime = ".GetSQLValueString($_POST['modifieddatetime'],"date").", statusID = ".GetSQLValueString($_POST['statusID'],"int").", confirmed = ";
$update .= isset($_POST['confirmed']) ? 1 : 0;
$update .=" WHERE recurringID = ".GetSQLValueString($_POST['ID'],"int");
$result = mysql_query($update, $aquiescedb) or die(mysql_error());
 $updateGoTo = "update_resource.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo));exit;
} // end do all update

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "booking")) {
  $updateSQL = sprintf("UPDATE bookinginstance SET bookedfor=%s, confirmed=%s, startdatetime=%s, enddatetime=%s, modifiedbyID=%s, modifieddatetime=%s, statusID=%s WHERE ID=%s",
                       GetSQLValueString($_POST['bookedfor'], "text"),
                       GetSQLValueString(isset($_POST['confirmed']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['startdatetime'], "date"),
                       GetSQLValueString($_POST['enddatetime'], "date"),
                       GetSQLValueString($_POST['modifiedbyID'], "int"),
                       GetSQLValueString($_POST['modifieddatetime'], "date"),
                       GetSQLValueString($_POST['statusID'], "int"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());

  $updateGoTo = "update_resource.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo));
}

$colname_rsBooking = "-1";
if (isset($_GET['bookingID'])) {
  $colname_rsBooking = $_GET['bookingID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsBooking = sprintf("SELECT bookinginstance.ID, bookinginstance.bookedfor, bookinginstance.confirmed, bookinginstance.startdatetime, bookinginstance.enddatetime, bookinginstance.modifiedbyID, bookinginstance.modifieddatetime, bookinginstance.statusID, bookingresource.title, users.firstname, users.surname, bookinginstance.recurring, bookinginstance.recurringID, users.ID AS userID FROM bookinginstance LEFT JOIN bookingresource ON (bookinginstance.resourceID = bookingresource.ID) LEFT JOIN users ON (bookinginstance.createdbyID = users.ID) WHERE bookinginstance.ID = %s", GetSQLValueString($colname_rsBooking, "int"));
$rsBooking = mysql_query($query_rsBooking, $aquiescedb) or die(mysql_error());
$row_rsBooking = mysql_fetch_assoc($rsBooking);
$totalRows_rsBooking = mysql_num_rows($rsBooking);

?><!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" --><title><?php $pageTitle = "Update Booking for ".$row_rsBooking['title']; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title><!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" --><?php if($row_rsBooking['recurring'] <2) { ?>
<style> .recurring { display:none; } </style>
<?php } ?><!-- InstanceEndEditable -->
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
   <h1>Update Booking for <?php echo $row_rsBooking['title']; ?></h1>
   
    <?php if (isset($error)) { ?><p class="alert alert-danger" role="alert"><?php echo $error; ?></p><?php } ?>
      <form action="<?php echo $editFormAction; ?>" method="post" name="booking" id="booking" role="form">
     <table class="form-table"> <tr>
         <td class="text-nowrap text-right">Booked for:</td>
         <td><input type="text"  name="bookedfor" value="<?php echo htmlentities($row_rsBooking['bookedfor'], ENT_COMPAT, 'UTF-8'); ?>" size="32" />
          <br />
          by <a href="../../../members/admin/update_user.php?userID=<?php echo $row_rsBooking['userID']; ?>"><?php echo $row_rsBooking['firstname']; ?> <?php echo $row_rsBooking['surname']; ?></a></td>
       </tr> <tr>
         <td class="text-nowrap text-right">Status:</td>
         <td><select name="statusID">
             <option value="0" <?php if (!(strcmp(0, htmlentities($row_rsBooking['statusID'], ENT_COMPAT, 'UTF-8')))) {echo "SELECTED";} ?>>Pending approval</option>
             <option value="1" <?php if (!(strcmp(1, htmlentities($row_rsBooking['statusID'], ENT_COMPAT, 'UTF-8')))) {echo "SELECTED";} ?>>Booked</option>
             <option value="2" <?php if (!(strcmp(2, htmlentities($row_rsBooking['statusID'], ENT_COMPAT, 'UTF-8')))) {echo "SELECTED";} ?>>Cancelled</option>
             <option value="3" <?php if (!(strcmp(3, htmlentities($row_rsBooking['statusID'], ENT_COMPAT, 'UTF-8')))) {echo "SELECTED";} ?>>Rejected</option>
           </select>         </td>
       </tr> <tr>
         <td class="text-nowrap text-right">Confirmed:</td>
         <td><input type="checkbox" name="confirmed" value=""  <?php if (!(strcmp(htmlentities($row_rsBooking['confirmed'], ENT_COMPAT, 'UTF-8'),""))) {echo "checked=\"checked\"";} ?> /></td>
       </tr>
       <tr class="recurring">
         <td colspan="2" align="left"><strong>This is part of a recurring booking for <?php echo $row_rsBooking['recurring']; ?> weeks. </strong><br />
          Check  box below if you wish to apply changes to all bookings in this recurring booking.</td>
        </tr>
       <tr class="recurring">
         <td class="text-nowrap text-right">Apply to all:</td>
         <td>
           <input type="checkbox" name="applyall" id="applyall"  onclick="if(this.checked==1) { document.getElementById('bookfrom').style.display = 'none';  document.getElementById('bookuntil').style.display = 'none'; } else  { document.getElementById('bookfrom').style.display = '';  document.getElementById('bookuntil').style.display = ''; }"/>
         
           <input name="recurring" type="hidden" id="recurring" value="<?php echo $row_rsBooking['recurring']; ?>" />
           <input name="recurringID" type="hidden" id="recurringID" value="<?php echo $row_rsBooking['recurringID']; ?>" />           </td>
       </tr>  <tr id="bookfrom">
         <td class="text-nowrap text-right">From:</td>
         <td><input type="hidden" name="startdatetime" value="<?php $setvalue = $row_rsBooking['startdatetime']; echo $setvalue; ?>" size="32" />
          <?php $time = true; $inputname = "startdatetime"; include("../../../core/includes/datetimeinput.inc.php"); ?></td>
       </tr>
       <tr  id="bookuntil">
         <td class="text-nowrap text-right">Until:</td>
         <td><input type="hidden" name="enddatetime" value="<?php $setvalue = $row_rsBooking['enddatetime']; echo $setvalue; ?>" size="32" />
          <?php $time = true; $inputname = "enddatetime"; include("../../../core/includes/datetimeinput.inc.php"); ?></td>
       </tr> <tr>
         <td class="text-nowrap text-right">&nbsp;</td>
         <td><input type="submit" class="button" value="Update Booking" /></td>
       </tr>
     </table>
     <input type="hidden" name="modifiedbyID" value="<?php echo htmlentities($row_rsLoggedin['ID']); ?>" />
     <input type="hidden" name="modifieddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
     <input type="hidden" name="MM_update" value="booking" />
     <input type="hidden" name="ID" value="<?php echo $row_rsBooking['ID']; ?>" />
   </form></div>
   <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsBooking);

mysql_free_result($rsLoggedin);

mysql_free_result($rsThisResource);

mysql_free_result($rsBookingOverlap);

?>


