<?php require_once('../../../../Connections/aquiescedb.php'); ?><?php require_once('../../../includes/adminAccess.inc.php'); ?>
<?php
require_once('../../includes/visitorfunctions.inc.php'); 
use GeoIp2\Database\Reader; // needs to go at root doc so can go before if / include below...
if(is_readable('../../../../3rdparty/geoip/geoip2.phar')) {
	require('../../../../3rdparty/geoip/geoip2.phar');	
	$gi2 = new Reader(SITE_ROOT.'3rdparty/geoip/GeoLite2-City.mmdb' );
} else if(is_readable('../../../../3rdparty/geoip/geoip.inc')) {
	require_once('../../../../3rdparty/geoip/geoip.inc');
	include("../../../../3rdparty/geoip/geoipcity.inc");
	include("../../../../3rdparty/geoip/geoipregionvars.php");
	$gi = geoip_open(SITE_ROOT."3rdparty/geoip/GeoLiteCity.dat",GEOIP_STANDARD);
}

if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "8,9,10";
$MM_donotCheckaccess = "false";

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
    if (($strUsers == "") && false) { 
      $isValid = true; 
    } 
  } 
  return $isValid; 
}

$MM_restrictGoTo = "../../../../login/index.php?notloggedin=true";
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

$colname_rsSession = "-1";
if (isset($_GET['sessionID'])) {
  $colname_rsSession = $_GET['sessionID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsSession = sprintf("SELECT ID,  `datetime`, page, pageTitle FROM track_page WHERE sessionID = %s ORDER BY track_page.`datetime` ASC", GetSQLValueString($colname_rsSession, "text"));
$rsSession = mysql_query($query_rsSession, $aquiescedb) or die(mysql_error());
$row_rsSession = mysql_fetch_assoc($rsSession);
$totalRows_rsSession = mysql_num_rows($rsSession);

$colname_rsSessionDetails = "-1";
if (isset($_GET['sessionID'])) {
  $colname_rsSessionDetails = $_GET['sessionID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsSessionDetails = sprintf("SELECT * FROM track_session WHERE ID = %s", GetSQLValueString($colname_rsSessionDetails, "text"));
$rsSessionDetails = mysql_query($query_rsSessionDetails, $aquiescedb) or die(mysql_error());
$row_rsSessionDetails = mysql_fetch_assoc($rsSessionDetails);
$totalRows_rsSessionDetails = mysql_num_rows($rsSessionDetails);

$colname_rsOrders = "-1";
if (isset($_GET['sessionID'])) {
  $colname_rsOrders = $_GET['sessionID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsOrders = sprintf("SELECT VendorTxCode, Amount, BillingFirstnames, BillingSurname, Status, LastUpdated FROM productorders WHERE sessionID = %s ORDER BY createddatetime ASC", GetSQLValueString($colname_rsOrders, "text"));
$rsOrders = mysql_query($query_rsOrders, $aquiescedb) or die(mysql_error());
$row_rsOrders = mysql_fetch_assoc($rsOrders);
$totalRows_rsOrders = mysql_num_rows($rsOrders);

$colname_rsCorrespondence = "-1";
if (isset($_GET['sessionID'])) {
  $colname_rsCorrespondence = $_GET['sessionID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsCorrespondence = sprintf("SELECT * FROM correspondence WHERE sessionID = %s", GetSQLValueString($colname_rsCorrespondence, "text"));
$rsCorrespondence = mysql_query($query_rsCorrespondence, $aquiescedb) or die(mysql_error());
$row_rsCorrespondence = mysql_fetch_assoc($rsCorrespondence);
$totalRows_rsCorrespondence = mysql_num_rows($rsCorrespondence);

$varThisIP_rsOtherVisits = "-1";
if (isset($row_rsSessionDetails['remote_address'])) {
  $varThisIP_rsOtherVisits = $row_rsSessionDetails['remote_address'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsOtherVisits = sprintf("SELECT track_session.ID FROM track_session WHERE STRCMP(%s, track_session.remote_address) =0", GetSQLValueString($varThisIP_rsOtherVisits, "text"));
$rsOtherVisits = mysql_query($query_rsOtherVisits, $aquiescedb) or die(mysql_error());
$row_rsOtherVisits = mysql_fetch_assoc($rsOtherVisits);
$totalRows_rsOtherVisits = mysql_num_rows($rsOtherVisits);

$host = (isset($thisRegion['hostdomain'])  && strlen($thisRegion['hostdomain'])>3) ? $thisRegion['hostdomain'] : $_SERVER['HTTP_HOST'];
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" --><title><?php $pageTitle = "Visitor session"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title><!-- InstanceEndEditable -->
<?php require_once('../../includes/seo.inc.php'); ?>
<?php require_once('../../../includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script>
addListener("load", init);
function init() {
	getData("includes/gethostbyaddress.inc.php?remote_address=<?php echo urlencode($row_rsSessionDetails['remote_address']); ?>","hostaddress");
}
</script><!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../../includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" -->
        <div class="page seo">
   <h1><i class="glyphicon glyphicon-globe"></i> Visitor Session</h1>
   <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav"><li><a href="javascript:history.go(-1);" class="link_undo"><i class="glyphicon glyphicon-arrow-left"></i> Back to visitors</a></li></ul></div></nav>
   <table class="table">
   <tr>
   <th class="text-right">Location:</th><td><span title="IP address: <?php echo $row_rsSessionDetails['remote_address']; ?>" data-toggle="tooltip">
  <?php
   if(ip2long($row_rsSessionDetails['remote_address'])) {
			// is valid IPv4 address
			 if(isset($gi2)) {
			
			
			try {
        	$record = $gi2->city($row_rsSessionDetails['remote_address']);
    } catch (GeoIp2\Exception\AddressNotFoundException $e) {
        // Your handling of the not-found case here.
		$record = false;
    }
			if($record) {
				$location = $record->country->name." - ";
				$location .= isset($record->city->name) ? $record->city->name : "unknown locale";
		  
		 
			}
  } else if(isset($gi)) {
			$record = geoip_record_by_addr($gi,$row_rsSessionDetails['remote_address']);
			if($record) {
				$location = $record->country_name." - ";
				$location .= isset($record->city) ? $record->city : "unknown locale";
		  
		 
			}
		   }
   }
		  if(isset( $location)) { ?>
         <?php echo utf8_encode($location); ?>
	<?php   }  else  { ?>Not known<?php } ?> <i class="glyphicon glyphicon-info-sign"></i></span>
    
    
 <span id="hostaddress" class="text-muted"></span><?php if($totalRows_rsOtherVisits>1) { ?> - this visitor has <a href="index.php?pages=1&startdate=2000-01-01&searchIP=<?php echo $row_rsSessionDetails['remote_address']; ?>"><?php echo $totalRows_rsOtherVisits-1; ?> other recent visit(s)</a><?php } ?></td></tr>
    
    
  <tr><th class="text-right">Origin:</th><td> <?php if (isset($row_rsSessionDetails['referer']) && $row_rsSessionDetails['referer']!="") {
	   $url = parse_url($row_rsSessionDetails['referer']); ?>
   <a href="<?php echo  $row_rsSessionDetails['referer']; ?>" title="<?php echo  $row_rsSessionDetails['referer']; ?>" target="_blank" rel="noopener" data-toggle="tooltip"><?php echo  $url['host'];?> <i class="glyphicon glyphicon-info-sign"></i></a>
   
   
  
   
   
   <?php 
   if(isset($url['query'])) {
	   parse_str($url['query']);
  
   if ((isset($q) && trim($q)!="") || (isset($p) && trim($p)!="")) { ?> 
   &nbsp;&nbsp;<strong>Search query  used:</strong> <?php 
   echo isset($q) ? $q : ""; echo isset($p) ? $p : "";   }} } else { ?>Direct visit<?php } ?>
   </td></tr><tr><th class="text-right">System:</th><td><span title="<?php echo $row_rsSessionDetails['user_agent']; ?>" data-toggle="tooltip"><?php $browser = getBrowser($row_rsSessionDetails['user_agent']); echo $browser['name']." ";
   echo $browser['version']>0 ? $browser['version']: "";
   echo  " on ".$browser['platform']; ?><?php if(isset($row_rsSessionDetails['screenwidth'])) { echo " (".$row_rsSessionDetails['screenwidth'] ." x ".$row_rsSessionDetails['screenheight'].")" ;  } ?> <i class="glyphicon glyphicon-info-sign"></i></span></td></tr></table>
  
   
   
  
  
  
  
  <table class="table table-hover">
  <thead>
     <tr>
       <th>Date:</th>
       <th>Time:</th>
       <th>Page</th>
     </tr></thead><tbody>
     <?php if ($totalRows_rsSession > 0) { // Show if recordset not empty ?><?php do { ?>
       <tr>
         
           <td><?php echo date('M d',strtotime($row_rsSession['datetime'])); ?></td>
           <td><?php echo date('H:i:s',strtotime($row_rsSession['datetime'])); ?></td>
           <td><a href="<?php echo "http://".$host.$row_rsSession['page']; ?>" title="<?php echo $row_rsSession['page']; ?>" target="_blank" rel="noopener"><?php  echo ($row_rsSession['pageTitle']!="") ? $row_rsSession['pageTitle'] :  preg_replace('/\?.*/', '', $row_rsSession['page']);  ?></a></td>
</tr>
       <?php } while ($row_rsSession = mysql_fetch_assoc($rsSession)); ?><?php } // Show if recordset not empty ?></tbody>
   </table>
   
  
   <?php if ($totalRows_rsOrders > 0) { // Show if recordset not empty ?>
  <h2>Orders</h2>
     <table class="table table-hover"><thead>
       <tr> <th>Date/time</th>
         <th>Tx Code</th>
         <th>Amount</th>
         <th>Name </th>
         <th>Status</th>
         <th>View</th>
       </tr></thead><tbody>
       <?php do { ?>
         <tr> <td><?php echo date('d M Y H:i', strtotime($row_rsOrders['LastUpdated'])); ?></td>
           <td><?php echo $row_rsOrders['VendorTxCode']; ?></td>
           <td><?php echo number_format($row_rsOrders['Amount'],2,".",","); ?></td>
           <td><?php echo $row_rsOrders['BillingFirstnames']." ".$row_rsOrders['BillingSurname']; ?></td>
           <td><?php echo $row_rsOrders['Status']; ?></td>
           <td><a href="../../../../products/admin/orders/orderDetails.php?VendorTxCode=<?php echo $row_rsOrders['VendorTxCode']; ?>" class="link_view">View</a></td>
         </tr>
         <?php } while ($row_rsOrders = mysql_fetch_assoc($rsOrders)); ?></tbody>
     </table>
     <?php } // Show if recordset not empty ?>
     <?php   if ($totalRows_rsCorrespondence > 0) { // Show if recordset not empty ?> <h2>Correspondence</h2>
   <table class="table table-hover">
    
    <?php do { ?>
      <tr>
        <td><?php echo ($row_rsCorrespondence['sentdatetime']>'2001') ? date('d M',strtotime($row_rsCorrespondence['sentdatetime'])) : date('d M',strtotime($row_rsCorrespondence['createddatetime'])); ?></td>
        
        <td><?php echo ($row_rsCorrespondence['sentdatetime']>'2001') ? date('H:i',strtotime($row_rsCorrespondence['sentdatetime'])) :date('H:i',strtotime($row_rsCorrespondence['createddatetime'])); ?></td>
        <td><a href="mailto:<?php echo $row_rsCorrespondence['sender']; ?>" title="Click on this link to send a reply using your mail client (e.g. Outlook)"><?php $sender = isset($row_rsCorrespondence['sendername']) ? $row_rsCorrespondence['sendername'] : $row_rsCorrespondence['sender']; echo (strlen($sender)>30) ? substr($sender,0,27)."&hellip;" : $sender; ?>
           </a></td>
        <td><?php $row_rsCorrespondence['recipient'] = isset($row_rsCorrespondence['company']) ? $row_rsCorrespondence['company'] : $row_rsCorrespondence['recipient']; echo (strlen($row_rsCorrespondence['recipient'])>130) ? substr($row_rsCorrespondence['recipient'],0,27)."&hellip;" : $row_rsCorrespondence['recipient']; ?></td>
        <td><?php echo (strlen($row_rsCorrespondence['subject'])>115) ? substr($row_rsCorrespondence['subject'],0,13)."&hellip;" : $row_rsCorrespondence['subject']; ?></td>
        
        <td><a href="/mail/admin/email/view.php?correspondenceID=<?php echo $row_rsCorrespondence['ID']; ?>" class="link_view">View</a></td>
        
      </tr>
      <?php } while ($row_rsCorrespondence = mysql_fetch_assoc($rsCorrespondence)); ?>
  </table>
  <?php } // Show if recordset not empty ?>
<p><a href="javascript:history.go(-1);" class="link_back"><i class="glyphicon glyphicon-arrow-left"></i> Back</a></p>
   
 </div>
   <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsSession);

mysql_free_result($rsSessionDetails);

mysql_free_result($rsOrders);

mysql_free_result($rsCorrespondence);

mysql_free_result($rsOtherVisits);
?>