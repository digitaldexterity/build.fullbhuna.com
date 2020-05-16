<?php require_once('../../Connections/aquiescedb.php'); ?><?php require_once('../../core/includes/framework.inc.php'); ?>
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

$MM_restrictGoTo = "../../login/signup.php";
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

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}
if (isset($_POST['startdatetime'])) { $date = $_POST['startdatetime']; } else if (isset($_GET['date'])) { $date = $_GET['date']." 00:00:00"; } else { $date = date('Y-m-d 00:00:00'); } // set date
if (isset($_POST['enddatetime'])) { $enddate = $_POST['enddatetime']; } else if (isset($_GET['date'])) { $enddate = $_GET['date']." 23:55:00"; } else { $enddate = date('Y-m-d 23:55:00'); } // set date

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsBookingPrefs = "SELECT * FROM bookingprefs";
$rsBookingPrefs = mysql_query($query_rsBookingPrefs, $aquiescedb) or die(mysql_error());
$row_rsBookingPrefs = mysql_fetch_assoc($rsBookingPrefs);
$totalRows_rsBookingPrefs = mysql_num_rows($rsBookingPrefs);


$varResourceID_rsBooked = "-1";
if (isset($_GET['resourceID'])) {
  $varResourceID_rsBooked = $_GET['resourceID'];
}
$varAllowOverlap_rsBooked = "0";
if (isset($row_rsBookingPrefs['tentativeoverlap'])) {
  $varAllowOverlap_rsBooked = $row_rsBookingPrefs['tentativeoverlap'];
}
$varStartDate_rsBooked = "2008-02-06 09:00:00";
if (isset($date)) {
  $varStartDate_rsBooked = $date;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsBooked = sprintf("SELECT bookinginstance.bookedfor, bookinginstance.confirmed, bookinginstance.startdatetime, bookinginstance.enddatetime, bookinginstance.ID FROM bookinginstance WHERE (bookinginstance.statusID = 1 OR (bookinginstance.statusID = 0 AND %s = 0)) AND bookinginstance.resourceID = %s AND ((bookinginstance.startdatetime >= %s AND bookinginstance.startdatetime <= DATE_ADD(%s, INTERVAL 1 DAY)) OR (bookinginstance.enddatetime >= %s AND bookinginstance.enddatetime <= DATE_ADD(%s, INTERVAL 1 DAY)) OR (bookinginstance.startdatetime < %s AND bookinginstance.enddatetime > DATE_ADD(%s, INTERVAL 1 DAY)))", GetSQLValueString($varAllowOverlap_rsBooked, "int"),GetSQLValueString($varResourceID_rsBooked, "int"),GetSQLValueString($varStartDate_rsBooked, "date"),GetSQLValueString($varStartDate_rsBooked, "date"),GetSQLValueString($varStartDate_rsBooked, "date"),GetSQLValueString($varStartDate_rsBooked, "date"),GetSQLValueString($varStartDate_rsBooked, "date"),GetSQLValueString($varStartDate_rsBooked, "date"));
$rsBooked = mysql_query($query_rsBooked, $aquiescedb) or die(mysql_error());
$row_rsBooked = mysql_fetch_assoc($rsBooked);
$totalRows_rsBooked = mysql_num_rows($rsBooked);

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
if (isset($_POST["MM_insert"]) && $_POST["MM_insert"] == "booking") {
// CHECH FOR  ERRORS
$error = ""; $loopstartdate = $date; $loopenddate = $enddate; $dow = $row_rsThisResource['availabledow'];
for($week=1; $week<=$_POST['recurring']; $week++) {
// repeat for number of weeks of recurring booking
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
$query_rsBookingOverlap = sprintf("SELECT bookinginstance.bookedfor, bookinginstance.confirmed, bookinginstance.startdatetime, bookinginstance.enddatetime, bookinginstance.ID FROM bookinginstance WHERE bookinginstance.resourceID = %s AND (bookinginstance.statusID = 1 OR (bookinginstance.statusID = 0 AND varAllowOverlap = 0))  AND ((bookinginstance.startdatetime >= %s AND bookinginstance.startdatetime <= %s) OR (bookinginstance.enddatetime >= %s AND bookinginstance.enddatetime <= %s) OR (bookinginstance.startdatetime < %s AND bookinginstance.enddatetime > %s))", GetSQLValueString($varResourceID_rsBookingOverlap, "int"),GetSQLValueString($varStartDate_rsBookingOverlap, "date"),GetSQLValueString($varEndDate_rsBookingOverlap, "date"),GetSQLValueString($varStartDate_rsBookingOverlap, "date"),GetSQLValueString($varEndDate_rsBookingOverlap, "date"),GetSQLValueString($varStartDate_rsBookingOverlap, "date"),GetSQLValueString($varEndDate_rsBookingOverlap, "date"));
$rsBookingOverlap = mysql_query($query_rsBookingOverlap, $aquiescedb) or die(mysql_error());
$row_rsBookingOverlap = mysql_fetch_assoc($rsBookingOverlap);
$totalRows_rsBookingOverlap = mysql_num_rows($rsBookingOverlap);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = "SELECT * FROM preferences";
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);



if (strtotime($date) - strtotime(date('Y-m-d 00:00:00')) < ($row_rsThisResource['minNotice']*60*60) && $_SESSION['MM_UserGroup'] < 8) { // not enough in advance
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

 $daynum= date('w',strtotime($loopstartdate)) == "0" ? "7" : date('w',strtotime($loopstartdate)); // convert PHP ver 3 to ISO-8601 


if (strpos($dow,$daynum)===false && $week == 1) {// not bookable on this dow - although only checks first week of period so far
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

if ($error !="") unset($_POST["MM_insert"]); // kill post if errors

// NOTIFY ADMIN
if (isset($_POST["MM_insert"]) && $_POST["MM_insert"] == "booking" && isset($row_rsThisResource['notifyemail'])) {
$to = $row_rsThisResource['notifyemail'];
$subject = isset($row_rsThisResource['locationname']) ? $row_rsThisResource['locationname']." " : "";
$subject .= $row_rsThisResource['title']." web booking";
$message = "This is an automated message to alert you of the following web booking:\n\n";
$message .= "For: ".$_POST['bookingfor']."\n";
$message .= "From: ".date('d/m/Y H:i',strtotime($_POST['startdatetime']))."\n";
$message .= "To: ".date('d/m/Y H:i',strtotime($_POST['enddatetime']))."\n\n";
$message .= ($_POST['recurring'] > 1) ? "THIS IS A RECURRING BOOKING FOR ".$_POST['recurring']." WEEKS.\n\n" :"";
$message .= "Log on to the web site to view details using link below:\n\n";
$message .= (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == "on") ? "https://" : "http://";
$message .= $_SERVER['HTTP_HOST']."/booking/admin/update_resource.php?startDate=".date('Y-m-d',strtotime($_POST['startdatetime']))."&resourceID=".$row_rsThisResource['ID']."";
require_once('../mail/includes/sendmail.inc.php');
sendMail($to,$subject,$message);
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "booking")) {
$loopstartdate = $date; $loopenddate = $enddate;$firstInsertID=0;
for($week=1; $week<=$_POST['recurring']; $week++) {
  $insertSQL = sprintf("INSERT INTO bookinginstance (resourceID, bookedfor, paymentrequired, startdatetime, enddatetime, recurring, recurringID, createdbyID, createddatetime, statusID) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['resourceID'], "int"),
                       GetSQLValueString($_POST['bookingfor'], "text"),
                       GetSQLValueString($_POST['paymentrequired'], "int"),
                       GetSQLValueString($loopstartdate, "date"),
                       GetSQLValueString($loopenddate, "date"),
					   GetSQLValueString($_POST['recurring'], "int"),
					   GetSQLValueString($firstInsertID, "int"),
                       GetSQLValueString($_POST['createdbyID'], "int"),
                       GetSQLValueString($_POST['createddatetime'], "date"),
                       GetSQLValueString($_POST['statusID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($insertSQL, $aquiescedb) or die(mysql_error());
  $loopstartdate = date('Y-m-d H:i:s',strtotime($loopstartdate . " +1 week"));   $loopenddate = date('Y-m-d H:i:s',strtotime($loopenddate . " +1 week"));
  if ($firstInsertID==0) {$firstInsertID = mysql_insert_id(); }
  } // end loop
  
  // update $firstInsertID into first record which will be by default 0
  $update = "UPDATE bookinginstance SET recurringID = ".intval($firstInsertID)." WHERE ID = ".intval($firstInsertID);
  $result = mysql_query($update, $aquiescedb) or die(mysql_error());

  $insertGoTo = "payment.php?bookingID=".$firstInsertID;
  if (isset($_SERVER['QUERY_STRING'])) {
    $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
    $insertGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $insertGoTo));exit;
} 


}// end is POST
?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Booking Times - ".$row_rsThisResource['title']; echo $pageTitle." | ".$site_name; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script src="/core/scripts/date-picker/js/datepicker.js"></script>
<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" --><?php if ($row_rsThisResource['recurringallow']!=1) { ?><style> #recurring { display:none; } </style><?php } 
	if($_POST['recurring'] <= 1) { ?><style> #bookweeks { display:none; } </style><?php }  ?>
    <script>
<!--
function PadDigits(n, totalDigits) 
    { 
        n = n.toString(); 
        var pd = ''; 
        if (totalDigits > n.length) 
        { 
            for (i=0; i < (totalDigits-n.length); i++) 
            { 
                pd += '0'; 
            } 
        } 
        return pd + n.toString(); 
    } 
	
function validateForm() { 
 var errors = "";
 var startdate = new Date; // Generic JS date object
 startdate.setFullYear(document.getElementById('yy-startdatetime').value,document.getElementById('mm-startdatetime').value,document.getElementById('dd-startdatetime').value);
 startdate.setHours(document.getElementById('hh-startdatetime').value,document.getElementById('mi-startdatetime').value);
 starttime = parseInt(document.getElementById('hh-startdatetime').value+document.getElementById('mi-startdatetime').value);
 availablestarttime = parseInt('<?php echo date('Hi',strtotime($row_rsThisResource['availablestart'])); ?>',10); 
 
  var enddate = new Date; // Generic JS date object
 enddate.setFullYear(document.getElementById('yy-enddatetime').value,document.getElementById('mm-enddatetime').value,document.getElementById('dd-enddatetime').value);
 enddate.setHours(document.getElementById('hh-enddatetime').value,document.getElementById('mi-enddatetime').value);
  endtime = parseInt(document.getElementById('hh-enddatetime').value+document.getElementById('mi-enddatetime').value);
   availableendtime =  parseInt('<?php echo date('Hi',strtotime($row_rsThisResource['availableend'])); ?>',10); 

 
 var nowdate = new Date;
  nowdate.setFullYear(<?php echo (int)date('Y').",".(int)date('m').",".(int)date('d'); ?>);
 nowdate.setHours(<?php echo (int)date('H').",".(int)date('i'); ?>);

if (starttime < availablestarttime || starttime > availableendtime || endtime < availablestarttime || endtime > availableendtime) errors += "- your booking is outwith the available hours.\n";
if (document.booking.isrecurring.checked == true && document.booking.recurring.value < 2) errors += "- a recurring booking must last for more than 1 week.\n";
if (enddate<startdate) errors += "- the end of your booking period is before the start.\n";
if (document.booking.recurring.value > 1 && (enddate.getTime()-startdate.getTime()>24*60*60*1000)) errors += "- recurring booking length must be 24 hours or less.\n";
<?php if (!isset($_SESSION['MM_UserGroup']) || $_SESSION['MM_UserGroup'] < 8) { // only check following if not admin ?>
if (startdate<nowdate) errors += "- you cannot book in the past.\n"; else if (startdate.getTime()-nowdate.getTime()<<?php echo $row_rsThisResource['minNotice']; ?>*60*60*1000) errors += "- you must book at least <?php echo $row_rsThisResource['minNotice']; ?> hours in advance.\n";
if (<?php echo $row_rsThisResource['maxHours']; ?>>0 && (enddate.getTime()-startdate.getTime()><?php echo $row_rsThisResource['maxHours']; ?>*60*60*1000)) errors += "- your booking exceeds the maximum length allowed.\n";
<?php } ?>

resource = "<?php echo $row_rsThisResource['title']; ?>";
fromdatetime = startdate.getDate()+"/"+startdate.getMonth()+"/"+startdate.getFullYear()<?php if( $row_rsThisResource['interval']==1) { ?>+" at "+PadDigits(startdate.getHours(),2)+":"+PadDigits(startdate.getMinutes(),2)<?php } ?>;
todatetime = enddate.getDate()+"/"+enddate.getMonth()+"/"+enddate.getFullYear()<?php if( $row_rsThisResource['interval']==1) { ?>+" at "+PadDigits(enddate.getHours(),2)+":"+PadDigits(enddate.getMinutes(),2)<?php } ?>;
confirmmessage = 'Please confirm you wish to book '+resource+'\n\nfrom '+fromdatetime+'\n\nuntil '+todatetime+'\n\n';
confirmmessage += (document.booking.isrecurring.checked == true) ? 'This will be a RECURRING booking for '+document.booking.recurring.value+' weeks.' : '';
   if (errors) window.alert('You cannot make this booking because:\n\n'+errors+'\nPlease check your entry and try again.'); else errors = !confirm(confirmmessage);
   if (!errors) document.getElementByID('submit').disabled = true;
   document.returnValue = (!errors);
 
}
//-->
</script>
    <div class="crumbs"><div>You are in: <a href="/">Home</a><span class="separator">&nbsp;&rsaquo;&nbsp;</span><a href="index.php">Booking</a><span class="separator">&nbsp;&rsaquo;&nbsp;</span>
      <?php if(isset($row_rsThisResource['category'])) { ?>
      <a href="category.php?categoryID=<?php echo $row_rsThisResource['categoryID']; ?>"><?php echo $row_rsThisResource['category']; ?></a><span class="separator">&nbsp;&rsaquo;&nbsp;</span>
      <?php } ?>
      <?php if(isset($row_rsThisResource['locationname'])) { ?>
      <a href="../location/location.php?locationID=<?php echo $row_rsThisResource['locationID']; ?>"><?php echo $row_rsThisResource['locationname']; ?></a><span class="separator">&nbsp;&rsaquo;&nbsp;</span>
      <?php } ?>
      <a href="month.php?resourceID=<?php echo $row_rsThisResource['ID']; ?>&amp;month=<?php echo date('m',strtotime($date)); ?>&amp;year=<?php echo date('Y',strtotime($date)); ?>"><?php echo $row_rsThisResource['title']; ?></a><span class="separator">&nbsp;&rsaquo;&nbsp;</span>Book</div></div>
    <h1>
      <?php if (isset($row_rsThisResource['imageURL']) && $row_rsThisResource['imageURL'] !="") { ?>
      <a href="<?php echo getImageURL($row_rsThisResource['imageURL'],"large"); ?>" title="<?php echo $row_rsThisResource['title']; ?>" class="img"><img src="<?php echo getImageURL($row_rsThisResource['imageURL'], "medium"); ?>" alt="<?php echo $row_rsThisResource['title']; ?>" class="fltrt" /></a>
      <?php } ?>
      <img src="/core/images/icons-large/office-calendar.png" alt="Bookings" width="32" height="32" style="vertical-align:
middle;" /> Book <?php echo $row_rsThisResource['title']; ?></h1>
    <p>Day: <?php echo date('l jS F Y', strtotime($date)); ?>
      <?php
		 $next = date('Y-m-d',(strtotime($date)+24*60*60));
		 $prev = date('Y-m-d',(strtotime($date)-24*60*60));
		 $resourceID = isset($_GET['resourceID']) ? "&resourceID=".intval($_GET['resourceID']) : "";
		 ?>
      &laquo;&nbsp;<a href="day.php?date=<?php echo $prev.$resourceID; ?>" rel="nofollow">Previous day</a> | <a href="day.php?date=<?php echo date('Y-m-d').$resourceID; ?>">Today</a> | <a href="day.php?date=<?php echo $next.$resourceID; ?>" rel="nofollow">Next day</a>&nbsp;&raquo;</p>
    <?php if (isset($error) && $error !="") { ?>
    <p class="alert alert-danger" role="alert"><?php echo $error; ?></p>
    <?php } ?>Availability:&nbsp;
    <?php if ($row_rsThisResource['availabledow'] != "1234567") { ?>
    
    <?php if (stristr($row_rsThisResource['availabledow'],"1")) {echo "Mon&nbsp;";} if (stristr($row_rsThisResource['availabledow'],"2")) {echo "Tue&nbsp;";} if (stristr($row_rsThisResource['availabledow'],"3")) {echo "Wed&nbsp;";} if (stristr($row_rsThisResource['availabledow'],"4")) {echo "Thu&nbsp;";} if (stristr($row_rsThisResource['availabledow'],"5")) {echo "Fri&nbsp;";} if (stristr($row_rsThisResource['availabledow'],"6")) {echo "Sat&nbsp;";} if (stristr($row_rsThisResource['availabledow'],"7")) {echo "Sun&nbsp;";} } ?>
    <?php if ((strtotime($row_rsThisResource['availableend'])-strtotime($row_rsThisResource['availableend'])) > 23*60*60) { ?>
    &nbsp;between&nbsp;<?php echo date('d M y',strtotime($row_rsThisResource['availablestart'])); ?>&nbsp;and&nbsp;<?php echo date('d M y',strtotime($row_rsThisResource['availableend'])); } else { ?>&nbsp;between&nbsp;<?php echo date('H:i',strtotime($row_rsThisResource['availablestart'])); ?>&nbsp;and&nbsp;<?php echo date('H:i',strtotime($row_rsThisResource['availableend'])); } ?>
    <h2>Booked:</h2>
    <?php if ($totalRows_rsBooked == 0) { // Show if recordset empty ?>
      <p><?php echo $row_rsThisResource['title']; ?> has not been booked on this day.</p>
      <?php } // Show if recordset empty ?>
    <?php if ($totalRows_rsBooked > 0) { // Show if recordset not empty ?>
      <table border="0" cellpadding="0" cellspacing="0" class="listTable">
        <tr>
          <td>&nbsp;</td>
          <td><strong>From:</strong></td>
          <td>&nbsp;</td>
          <td><strong>Until:</strong></td>
          <td>&nbsp;</td>
        </tr>
        <?php do { ?>
          <tr>
            <td><?php if (($row_rsBooked['statusID'] == 1)) { ?>
              <img src="/core/images/icons/green-light.png" alt="This booking is confirmed" style="vertical-align:
middle;" />
              <?php } else if (($row_rsBooked['statusID'] == 0)) { ?>
              <img src="/core/images/icons/amber-light.png" alt="This booking has not been confirmed" width="16" height="16" style="vertical-align:
middle;" />
              <?php } else { ?>
              <img src="/core/images/icons/red-light.png" alt="This booking has been refused or cancelled" width="16" height="16" style="vertical-align:
middle;" />
              <?php } ?></td>
            <td><?php echo date('D d M Y g.i a',strtotime($row_rsBooked['startdatetime'])); ?></td>
            <td>&raquo;&raquo;</td>
            <td><?php echo date('D d M Y g.i a',strtotime($row_rsBooked['enddatetime'])); ?></td>
            <td><?php if ($row_rsLoggedIn['usertypeID'] >=8) { // admin only ?>
              <a href="admin/update_booking.php?bookingID=<?php echo $row_rsBooked['ID']; ?>"><?php echo $row_rsBooked['bookedfor']; ?></a>
              <?php } ?>
              &nbsp;</td>
          </tr>
          <?php } while ($row_rsBooked = mysql_fetch_assoc($rsBooked)); ?>
      </table>
      <br />
      <?php } // Show if recordset not empty ?>
    <fieldset id="book">
    <h2>Book:</h2>
    <?php if (isset($error) && $error!="") { ?>
      <p class="alert warning alert-warning" role="alert">You cannot make the requested booking. <a href="month.php?resourceID=<?php echo $row_rsThisResource['ID']; ?>&amp;month=<?php echo date('m',strtotime($date)); ?>&amp;year=<?php echo date('Y',strtotime($date)); ?>">Back to month view</a>.</p>
      <?php } else { ?>
      <form action="<?php echo $editFormAction; ?>" method="POST" name="booking" id="booking" onSubmit="validateForm();return document.returnValue;" role="form">
        <table border="0" cellpadding="0" cellspacing="0" class="form-table">
          <tr>
            <td>For: </td>
            <td><input name="bookingfor" type="text"  id="bookingfor" value="<?php echo isset($_POST['bookingfor']) ? $_POST['bookingfor'] : $row_rsLoggedIn['firstname']." ".$row_rsLoggedIn['surname']; ?>" size="30" maxlength="30" /></td>
          </tr>
          <tr>
            <td>From: </td>
            <td><input type="hidden" name="startdatetime" id="startdatetime" value="<?php $setvalue = $date; echo $setvalue; ?>"  size="32"   class='highlight-days-67 split-date format-y-m-d divider-dash'  />
              <?php $time = ($row_rsThisResource['interval'] == 1) ? true : false; $inputname = "startdatetime"; $formName = "booking"; include("../core/includes/datetimeinput.inc.php"); ?></td>
          </tr>
          <tr>
            <td>Until: </td>
            <td><input type="hidden" name="enddatetime" id="enddatetime"  value="<?php $setvalue = isset($_POST['enddatetime']) ? $_POST['enddatetime'] : $enddate; echo $setvalue; ?>" size="32"  class='highlight-days-67 split-date format-y-m-d divider-dash'  />
              <?php $time = ($row_rsThisResource['interval'] == 1) ? true : false; $inputname = "enddatetime"; include("../core/includes/datetimeinput.inc.php"); ?>
              <input name="createdbyID" type="hidden" id="createdbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
              <input name="createddatetime" type="hidden" id="createddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
              <input name="resourceID" type="hidden" id="resourceID" value="<?php echo intval($_GET['resourceID']); ?>" />
              <input name="statusID" type="hidden" id="statusID" value="<?php echo ($row_rsLoggedIn['usertypeID'] >=8 ) ? 1 : 0; ?>" />
              <input type="hidden" name="MM_insert" value="booking" />
              <input name="maxHours" type="hidden" id="maxHours" value="<?php echo $row_rsThisResource['maxHours']; ?>" />
              <input name="paymentrequired" type="hidden" id="paymentrequired" value="<?php echo $row_rsThisResource['paymentrequired']; ?>" />
              <input type="hidden" name="hiddenField" id="hiddenField" /></td>
          </tr>
          <tr id="recurring">
            <td>&nbsp;</td>
            <td><input <?php if (!(strcmp($_POST['isrecurring'],1))) {echo "checked=\"checked\"";} ?> name="isrecurring" type="checkbox" id="isrecurring" value="1" onClick="if(this.checked!=1) { document.getElementById('bookweeks').style.display = 'none'; document.getElementById('recurring').value = 1; } else  { document.getElementById('bookweeks').style.display = 'block'; }"/>
              I wish to make a weekly recurring booking.<br />
              <div id = "bookweeks">Book for <?php echo "<SELECT NAME='recurring'  id='recurring'>\n";           
for($week = 1; $week <= 52; $week += 1)  {  
echo "<OPTION VALUE='".$week."'";  
if(isset($_POST['recurring']) && $week== $_POST['recurring']) {  
echo " SELECTED";  
}
echo ">".$week."\n";  
}  
echo "</SELECT>"; ?> weeks. </div></td>
          </tr>
          <tr>
            <td>&nbsp;</td>
            <td><input type="submit" class="button" value="Make Booking" /></td>
          </tr>
        </table>
      </form>
      <?php } ?>
    </fieldset>
    <!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsBooked);

mysql_free_result($rsThisResource);

mysql_free_result($rsLoggedIn);

if (false) { // added because of the no exists error

mysql_free_result($rsBookingOverlap);

mysql_free_result($rsPreferences);

mysql_free_result($rsBookingPrefs);

}
?>
