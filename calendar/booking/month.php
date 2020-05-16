<?php require_once('../../Connections/aquiescedb.php'); ?><?php require_once('../../core/includes/framework.inc.php'); ?>
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

mysql_select_db($database_aquiescedb, $aquiescedb);
?><?php


mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsBookingPrefs = "SELECT * FROM bookingprefs";
$rsBookingPrefs = mysql_query($query_rsBookingPrefs, $aquiescedb) or die(mysql_error());
$row_rsBookingPrefs = mysql_fetch_assoc($rsBookingPrefs);
$totalRows_rsBookingPrefs = mysql_num_rows($rsBookingPrefs);


$colname_rsThisResource = "-1";
if (isset($_GET['resourceID'])) {
  $colname_rsThisResource = $_GET['resourceID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsThisResource = sprintf("SELECT bookingresource.title, bookingresource.`description`, bookingresource.imageURL, bookingresource.paymentrequired, bookingresource.depositrequired, bookingcategory.`description` AS category, bookingresource.categoryID, bookingresource.capacitystanding, bookingresource.capacitytheatre, bookingresource.capacityclassroom, bookingresource.capacityboardroom, bookingresource.capacitybanquet, bookingresource.`interval`, bookingresource.availabledow, bookingresource.availablestart, bookingresource.availableend, bookingresource.locationID, location.locationname, bookingresource.featurenotes, bookingresource.minNotice FROM bookingresource LEFT JOIN bookingcategory ON (bookingresource.categoryID = bookingcategory.ID) LEFT JOIN location ON (bookingresource.locationID = location.ID) WHERE bookingresource.ID = %s", GetSQLValueString($colname_rsThisResource, "int"));
$rsThisResource = mysql_query($query_rsThisResource, $aquiescedb) or die(mysql_error());
$row_rsThisResource = mysql_fetch_assoc($rsThisResource);
$totalRows_rsThisResource = mysql_num_rows($rsThisResource);
?> <?php
$colname_rsPricing = "-1";
if (isset($_GET['resourceID'])) {
  $colname_rsPricing = $_GET['resourceID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPricing = sprintf("SELECT * FROM bookingpricing WHERE resourceID = %s ORDER BY bookingpricing.`default` DESC, bookingpricing.datestart ASC, bookingpricing.timestart ASC", GetSQLValueString($colname_rsPricing, "int"));
$rsPricing = mysql_query($query_rsPricing, $aquiescedb) or die(mysql_error());
$row_rsPricing = mysql_fetch_assoc($rsPricing);
$totalRows_rsPricing = mysql_num_rows($rsPricing);
?><?php
$varResourceID_rsFeatures = "-1";
if (isset($_GET['resourceID'])) {
  $varResourceID_rsFeatures = $_GET['resourceID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsFeatures = sprintf("SELECT bookingfeature.featurename, bookingfeature.featuredetails FROM bookingfeature, bookingresourcefeature WHERE bookingresourcefeature.resourceID = %s AND bookingresourcefeature.featureID = bookingfeature.ID", GetSQLValueString($varResourceID_rsFeatures, "int"));
$rsFeatures = mysql_query($query_rsFeatures, $aquiescedb) or die(mysql_error());
$row_rsFeatures = mysql_fetch_assoc($rsFeatures);
$totalRows_rsFeatures = mysql_num_rows($rsFeatures);
?><?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Booking - ".$row_rsThisResource['title']; echo $pageTitle." | ".$site_name; ?> - Bookings</title>
<!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<link href="css/month.css" rel="stylesheet"  />
<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" -->
  <?php  require_once('../../calendar/includes/calendar.inc.php'); 
 
class DynCalendar extends Calendar 
{
    function getCalendarLink($month, $year)
    {
        // Redisplay the current page, but with some parameters
        // to set the new month and year
        $s = getenv('SCRIPT_NAME');
        return "$s?month=".$month."&year=".$year."&resourceID=".intval($_GET['resourceID']);
    }
	
	function getDateLink($day, $month, $year)
    {
		global $database_aquiescedb, $aquiescedb, $availablestart, $availableend, $interval, $minnotice, $dow;
        $varDateTime_rsBookingDay = $year."-".$month."-".$day." 00:00:00";
		if (isset($varDateTime_rsBookingDay)) {
  			$varDateTime_rsBookingDay = $varDateTime_rsBookingDay;
		}
$varResourceID_rsBookingDay = "-1";
if (isset($_GET['resourceID'])) {
  $varResourceID_rsBookingDay = $_GET['resourceID'];
}
$varAllowOverlap_rsBookingDay = "0";
if (isset($row_rsBookingPrefs['tentativeoverlap'])) {
  $varAllowOverlap_rsBookingDay = $row_rsBookingPrefs['tentativeoverlap'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsBookingDay = sprintf("SELECT SUM(UNIX_TIMESTAMP(bookinginstance.enddatetime)-UNIX_TIMESTAMP(bookinginstance.startdatetime)) AS sumdiff FROM bookinginstance WHERE bookinginstance.resourceID = %s AND (bookinginstance.statusID = 1 OR (bookinginstance.statusID = 0 AND %s = 0))  AND ((bookinginstance.startdatetime>= %s AND  bookinginstance.startdatetime < DATE_ADD(%s, INTERVAL 1 DAY)) OR (bookinginstance.enddatetime>= %s AND  bookinginstance.enddatetime < DATE_ADD(%s, INTERVAL 1 DAY)) OR (bookinginstance.startdatetime< %s AND  bookinginstance.enddatetime > %s)) GROUP BY bookinginstance.ID", GetSQLValueString($varResourceID_rsBookingDay, "int"),GetSQLValueString($varAllowOverlap_rsBookingDay, "int"),GetSQLValueString($varDateTime_rsBookingDay, "date"),GetSQLValueString($varDateTime_rsBookingDay, "date"),GetSQLValueString($varDateTime_rsBookingDay, "date"),GetSQLValueString($varDateTime_rsBookingDay, "date"),GetSQLValueString($varDateTime_rsBookingDay, "date"),GetSQLValueString($varDateTime_rsBookingDay, "date"));
$rsBookingDay = mysql_query($query_rsBookingDay, $aquiescedb) or die(mysql_error());
$row_rsBookingDay = mysql_fetch_assoc($rsBookingDay);
$totalRows_rsBookingDay = mysql_num_rows($rsBookingDay);

$fulltime =  strtotime($availableend)-strtotime($availablestart);
		$unbookedtime =  $fulltime-$row_rsBookingDay['sumdiff'];

		$link = ""; $daynum= date('w',strtotime($varDateTime_rsBookingDay)) == "0" ? "7" : date('w',strtotime($varDateTime_rsBookingDay)); // convert PHP ver 3 to ISO-8601
 		if ((strtotime($varDateTime_rsBookingDay) - strtotime(date('Y-m-d')) < ($row_rsThisResource['minNotice']*60*60)) || strpos($dow,$daynum)===false) {// not bookable - === as 0 implies false too!
		} else { // bookable
        if ($totalRows_rsBookingDay > 0) // is booked
        {
		if($interval==0 || $unbookedtime < 60*30) {// interval day or less than half an hour left
            $class = "booked";
			} else {
			$class = "partbooked";
			}
        } else {  $class = "notbooked"; 
		} 
		$link = "<a href='day.php?date=".$year."-".$month."-".$day."&resourceID=".intval($_GET['resourceID'])."' class='".$class."' title='Click to book on this day' rel='nofollow'>";
		}// end bookable
        return $link;
    }
}

// Construct a calendar to show the current month
$cal = new DynCalendar;
// If no month/year set, use current month/year
 
$d = getdate(time()); $month = $_GET['month']; $year = $_GET['year'];

if ($month == "")
{
    $month = $d["mon"];
}

if ($year == "")
{
    $year = $d["year"];
} 

$dow = $row_rsThisResource['availabledow']; 
$availablestart = $row_rsThisResource['availablestart'];
$availableend = $row_rsThisResource['availableend'];
$interval = $row_rsThisResource['interval'];
$minnotice = $row_rsThisResource['minNotice'];
?>
<div class="crumbs"><div>You are in: <a href="../index.php">Home</a><span class="separator">&nbsp;&rsaquo;&nbsp;</span><a href="index.php">Booking</a><span class="separator">&nbsp;&rsaquo;&nbsp;</span><?php if(isset($row_rsThisResource['category'])) { ?>
  <a href="category.php?categoryID=<?php echo $row_rsThisResource['categoryID']; ?>"><?php echo $row_rsThisResource['category']; ?></a><span class="separator">&nbsp;&rsaquo;&nbsp;</span><?php } ?><?php if(isset($row_rsThisResource['locationname'])) { ?><a href="../location/location.php?locationID=<?php echo $row_rsThisResource['locationID']; ?>"><?php echo $row_rsThisResource['locationname']; ?></a><span class="separator">&nbsp;&rsaquo;&nbsp;</span><?php } ?><?php echo $row_rsThisResource['title']; ?></div></div>
  <div style="float:right;margin-left:5px;width:320px;"><?php echo $cal->getMonthView($month, $year, $database_aquiescedb, $aquiescedb, $row_rsThisResource['availablestart'],$row_rsThisResource['availableend'],$row_rsThisResource['interval'],$row_rsThisResource['minNotice'],$row_rsThisResource['availabledow']);
?>
  <table border="0" cellpadding="2" cellspacing="0" class="form-table">
    <tr>
      <td colspan="4"><strong>Key</strong>: </td>
      </tr>
    <tr>
      <td colspan="2">Available:</td>
      <td colspan="2">Not available:</td>
      </tr>
    <tr>
      <td><img src="images/notbooked.gif" alt="Not booked" width="16" height="16" style="vertical-align:
middle;" /></td>
      <td> Available all day<br /></td>
      <td><img src="images/booked.gif" alt="Booked" width="16" height="16" style="vertical-align:
middle;" /></td>
      <td> Booked all day</td>
    </tr>
    <tr>
      <td><img src="images/partbooked.gif" alt="Part booked" width="16" height="16" style="vertical-align:
middle;" /></td>
      <td> Booked part of this day</td>
      <td><img src="images/unbookable.gif" alt="Unbookable" width="16" height="16" style="vertical-align:
middle;" /></td>
      <td> Unavailable</td>
    </tr>
  </table>
 
  </div><h1><?php echo $row_rsThisResource['title']; ?></h1><?php if(isset($row_rsThisResource['locationname'])) { ?><p>
  <a href="../../location/location.php?locationID=<?php echo $row_rsThisResource['locationID']; ?>" ><?php echo $row_rsThisResource['locationname']; ?></a></p><?php } ?>
  <?php if (isset($row_rsThisResource['imageURL']) && $row_rsThisResource['imageURL'] !="") { ?>
  <p><a href="<?php echo getImageURL($row_rsThisResource['imageURL'],"large"); ?>"  title="<?php echo $row_rsThisResource['title']; ?>" class="img"><img src="<?php echo getImageURL($row_rsThisResource['imageURL'],"medium"); ?>" alt="<?php echo $row_rsThisResource['title']; ?>" /></a> </p>
  <?php } ?>
 
  <p><?php echo nl2br($row_rsThisResource['description']); ?></p>
  <p><?php echo isset($row_rsThisResource['capacitystanding']) ? "Standing capacity: ".$row_rsThisResource['capacitystanding']."<br />" : ""; ?> <?php echo isset($row_rsThisResource['capacitytheatre']) ? "Theatre capacity: ".$row_rsThisResource['capacitytheatre']."<br />" : ""; ?> <?php echo isset($row_rsThisResource['capacityclassroom']) ? "Classroom capacity: ".$row_rsThisResource['capacityclassroom']."<br />" : ""; ?> <?php echo isset($row_rsThisResource['capacitybanquet']) ? "Banquet capacity: ".$row_rsThisResource['capacitybanquet']."<br />" : ""; ?>  </p>
 
    
<?php if ($totalRows_rsFeatures > 0) { // Show if recordset not empty ?>
    <strong>Features:</strong> 
  <?php do { ?>
    <?php echo $row_rsFeatures['featurename']; ?>;&nbsp;<?php } while ($row_rsFeatures = mysql_fetch_assoc($rsFeatures)); ?>
  <?php } // Show if recordset not empty ?>

    <?php echo isset($row_rsThisResource['featurenotes']) ? "<p><strong>Notes: </strong>".nl2br($row_rsThisResource['featurenotes'])."</p>" : ""; ?>
    <p>Click on the  day on the calendar you wish to start booking...</p>
    <?php if ($row_rsThisResource['paymentrequired']==1 && $totalRows_rsPricing > 0) { ?>
  <table border="0" cellpadding="0" cellspacing="0" class="listTable">
    <tr>
      <td><strong>Period</strong></td>
      <td><strong>Rate</strong></td>
      </tr>
    <?php do { ?>
      <tr>
        <td><?php if($row_rsPricing['default'] ==1) { if ($totalRows_rsPricing ==1) { ?>Standard rate: <?php } else { ?>All other times: <?php }
		} else { 
		echo date('jS M',strtotime($row_rsPricing['datestart']));
		echo ($row_rsPricing['everyyear']==1) ? "" : date('Y',strtotime($row_rsPricing['datestart']));
		echo isset($row_rsPricing['timestart']) ? date('H:i',strtotime($row_rsPricing['timestart'])) : ""; 
		echo " to ".date('jS M',strtotime($row_rsPricing['dateend']));
		echo ($row_rsPricing['everyyear']==1) ? "" : date('Y',strtotime($row_rsPricing['dateend']));
		echo isset($row_rsPricing['timeend']) ? date('H:i',strtotime($row_rsPricing['timeend'])) : ""; } ?></td>
        <td><?php $currency = ($row_rsPricing['currency'] = "GBP") ? "&pound;" : $row_rsPricing['currency']; echo $currency.number_format($row_rsPricing['price'],2); ?><?php $hours =$row_rsPricing['pricehours'];
		 if (($hours/168) == number_format(($hours/168),0)) { // is weeks
		 $periodamount = $hours/168; $periodmultiple = ($periodamount == 1) ? "week" : $periodamount." weeks";
		 } else if (($hours/24) == number_format(($hours/24),0)) { // is days
		 	 $periodamount = $hours/24; $periodmultiple = ($periodamount == 1) ? "day" : $periodamount." days";
			 } else { // is hours
			 $periodamount = $hours; $periodmultiple = ($periodamount == 1) ? "hour" : $periodamount." hours";
			 }
			 echo " per ".$periodmultiple; if ($row_rsPricing['deposit']>0) { echo "<br />Deposit: ".$currency.number_format($row_rsPricing['deposit'],2); } ?></td>
        </tr><?php if ($row_rsPricing['details'] !="") { ?>
     <?php } ?>
      <?php } while ($row_rsPricing = mysql_fetch_assoc($rsPricing)); ?>
  </table>
  <?php  } ?>
  <!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsThisResource);

mysql_free_result($rsPricing);

mysql_free_result($rsFeatures);

mysql_free_result($rsBookingPrefs);

mysql_free_result($rsBookingDay);
?>

