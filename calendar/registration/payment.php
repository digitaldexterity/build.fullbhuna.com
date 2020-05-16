<?php require_once('../../Connections/aquiescedb.php'); ?><?php require_once('../../members/includes/userfunctions.inc.php'); ?>
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
?>
<?php
$varRegistrationID_rsRegistrant = "-1";
if (isset($_GET['registrationID'])) {
  $varRegistrationID_rsRegistrant = $_GET['registrationID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsRegistrant = sprintf("SELECT eventregistration.*, users.firstname, users.surname, users.dob, location.address1, location.address2, location.address3, location.address4, location.postcode, users.email, users.username FROM eventregistration LEFT JOIN eventregistration AS otherregistration ON (otherregistration.withregistrationID = eventregistration.ID) LEFT JOIN users ON ( eventregistration.userID = users.ID) LEFT JOIN location ON (users.defaultaddressID = location.ID) WHERE eventregistration.withregistrationID = (SELECT eventregistration.withregistrationID FROM eventregistration WHERE eventregistration.ID = %s) OR  eventregistration.ID = %s GROUP BY eventregistration.ID ORDER BY registrationnumber ", GetSQLValueString($varRegistrationID_rsRegistrant, "int"),GetSQLValueString($varRegistrationID_rsRegistrant, "int"));
$rsRegistrant = mysql_query($query_rsRegistrant, $aquiescedb) or die(mysql_error());
$row_rsRegistrant = mysql_fetch_assoc($rsRegistrant);
$totalRows_rsRegistrant = mysql_num_rows($rsRegistrant);

$varEventID_rsEvent = "-1";
if (isset($_GET['eventID'])) {
  $varEventID_rsEvent = $_GET['eventID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsEvent = sprintf("SELECT event.*, eventgroup.eventtitle FROM event LEFT JOIN eventgroup ON (event.eventgroupID = eventgroup.ID) WHERE event.ID = %s", GetSQLValueString($varEventID_rsEvent, "int"));
$rsEvent = mysql_query($query_rsEvent, $aquiescedb) or die(mysql_error());
$row_rsEvent = mysql_fetch_assoc($rsEvent);
$totalRows_rsEvent = mysql_num_rows($rsEvent);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsProductPrefs = "SELECT * FROM productprefs";
$rsProductPrefs = mysql_query($query_rsProductPrefs, $aquiescedb) or die(mysql_error());
$row_rsProductPrefs = mysql_fetch_assoc($rsProductPrefs);
$totalRows_rsProductPrefs = mysql_num_rows($rsProductPrefs);

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);
?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Event Registration Payment"; echo $pageTitle." | ".$site_name; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<link href="../css/calendarDefault.css" rel="stylesheet"  />
<style><!--
<?php if($row_rsEvent['registrationdob']==0) {
	echo ".dob { display: none; } ";
} ?>
--></style>
<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" -->
       <div class="container registration">
    <h1>Action required: Registration Payment</h1>
    <h2><?php echo $row_rsEvent['eventtitle']; ?></h2>
    <p>IMPORTANT: Registration will be complete once payment has been received <?php echo ($row_rsEvent['surveyID']>0) ? " AND you have completed the following questionnaire " : ""; ?> for this event.</p>
   
    <h2>Payment due:</h2>
    <table border="0" cellpadding="2" cellspacing="0" class="listTable">
      <tr>
        <th width="209">No.</th>
        <th width="294">Name</th>
        <th width="86"><span class="dob">Date of birth</span></th>
        <th width="34" align="right">Cost</th>
      </tr>
      <?php  $total = 0; $children = 0; $adults = 0; $registrationnumbers = ""; do { ?>
      <tr>
        <td><?php echo $row_rsRegistrant['registrationnumber']; $registrationnumbers .= $row_rsRegistrant['registrationnumber']."; ";?></td>
        <td><?php echo $row_rsRegistrant['firstname']; ?> <?php echo $row_rsRegistrant['surname']; ?></td>
        <td><span class="dob"><?php $dob = explode("-",$row_rsRegistrant['dob']); echo isset($dob[2]) ? $dob[2]."/".$dob[1]."/".$dob[0] : ""; ?></span></td>
        <td align="right"><?php $price = (isset($row_rsRegistrant['dob']) && $row_rsRegistrant['dob']!="" && (strtotime($row_rsRegistrant['dob']) >= strtotime("16 YEARS AGO") || ($row_rsEvent['over65'] == 1 && strtotime($row_rsRegistrant['dob']) < strtotime("65 YEARS AGO")))) ? $row_rsEvent['registrationconcession'] : $row_rsEvent['registrationcost'];
		if($row_rsEvent['registrationpayment']==1 || $total ==0) {
			// if per person or total is zero
			echo "&pound;".number_format($price,2,".",","); 
			$total += $price; 
		}
		if(isset($row_rsRegistrant['dob']) && $row_rsRegistrant['dob']!="" && strtotime($row_rsRegistrant['dob']) >= strtotime("16 YEARS AGO")) {
			$children ++;
		} else {
			$adults ++;
		};
		 ?></td>
      </tr>
      <?php } while ($row_rsRegistrant = mysql_fetch_assoc($rsRegistrant)); ?>
      <?php 
	  $discount = 0; $rank = isset($_SESSION['MM_UserGroup']) ? $_SESSION['MM_UserGroup'] : 0;
	  if($row_rsEvent['teamdiscountamount']>0 && $totalRows_rsRegistrant>$row_rsEvent['teamdiscountnumber']) { 
	  // QUALIFIES FOR TEAM DISCOUNT
	  $discount = ($row_rsEvent['teamdiscountamounttype']==1) ? $row_rsEvent['teamdiscountamount']/100*$total : $row_rsEvent['teamdiscountamount']; 
	  $total = $total - $discount; ?>
      <tr>
        <td colspan="3" align="right">Group Discount (<?php echo $row_rsEvent['teamdiscountnumber']; ?> members or over):</td>
        <td align="right"><?php echo "&pound;".number_format($discount,2,".",","); ?></td>
      </tr>
      <?php  }   else if(isset($row_rsEvent['familydiscountamount']) && $row_rsEvent['familydiscountamount']>0 && $adults >= $row_rsEvent['familydiscountadults'] && $children >= $row_rsEvent['familydiscountchildren'])  {
		  // QUALIFIES FOR FAMILY DISCOUNT
		  $discount = ($row_rsEvent['familydiscountamounttype']==1) ? ($row_rsEvent['familydiscountamount']/100*$total) : $row_rsEvent['familydiscountamount'] ; 
		  $total = $total - $discount; ?>
           <tr>
        <td colspan="3" align="right">Family Discount (<?php echo $adults; ?> Adults, <?php echo $children; ?> Children):</td>
        <td align="right"><?php echo "&pound;".number_format($discount,2,".",","); ?></td>
      </tr>
		  
	 <?php  }  else if(isset($row_rsEvent['memberdiscountamount']) && $row_rsEvent['memberdiscountamount']>0 && $row_rsEvent['memberdiscountrank']<=$rank && ($row_rsEvent['memberdiscountgroup']==0 || userinGroup($row_rsEvent['memberdiscountgroup'], $row_rsLoggedIn['ID'])))  {
		  // QUALIFIES FOR MEMBER DISCOUNT
		  $discount = ($row_rsEvent['memberdiscounttype']==1) ? ($row_rsEvent['memberdiscountamount']/100*$total) : $row_rsEvent['memberdiscountamount'] ; 
		  $total = $total - $discount; ?>
           <tr>
        <td colspan="3" align="right">Member Discount:</td>
        <td align="right"><?php echo "&pound;".number_format($discount,2,".",","); ?></td>
      </tr>
		  
	 <?php  } ?>
	 
      <tr>
        <td colspan="3" align="right"><strong>Total:</strong></td>
        <td align="right"><?php echo "&pound;".number_format($total,2,".",","); ?></td>
      </tr>
    </table><?php echo $row_rsEvent['paymentinstructions']; $returnURL = isset($_GET['returnURL'])  ? $_GET['returnURL'] : "/calendar/event.php?eventID=".intval($_GET['eventID'])."&msg=".urlencode($msg); ?>
    <p>Please click on the button below to pay now by credit/debit card using our secure payment provider <?php if($row_rsEvent['registrationinvoice']==1) { ?><a href="<?php $msg = "You have submitted your details for this event. NOTE: Registration will be complete once payment has been received.";
	 // for here and PayPal
	echo $returnURL; ?>">or click here if you prefer to be invoiced</a><?php } ?>.</p>
    <?php if ($row_rsProductPrefs['paymentproviderID']==1) { // paypal
	$strVendorTxCode = "registration-".intval($_GET['registrationID']);
	  require_once('../../products/payments/includes/logtransaction.inc.php');
 logtransaction($strVendorTxCode,"PAYPAL");
 require_once('includes/paypal.inc.php'); 
	 } // end paypal
	 
	
	?></div>
    <!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsRegistrant);

mysql_free_result($rsEvent);

mysql_free_result($rsProductPrefs);

mysql_free_result($rsLoggedIn);
?>
