<?php require_once('../../../../Connections/aquiescedb.php'); ?><?php require_once('../../../includes/adminAccess.inc.php'); ?><?php require_once('../../../includes/framework.inc.php'); ?>
<?php require_once('../../includes/visitorfunctions.inc.php'); 
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



$_GET['hidebots'] = (isset($_GET['hidebots']) || !isset($_GET['startdate'])) ? 1 : 0; // default to hiding bots
$_GET['pages'] = isset($_GET['pages']) ? $_GET['pages'] : 1; // default to showing 1 pages or more initially
$_GET['excludeIP'] = (isset($_GET['pages'])) ? (isset($_GET['excludeIP']) ? 1 : 0) : 1; // exclude by default if no submit
$_GET['excludestaff'] = (isset($_GET['pages'])) ? (isset($_GET['excludestaff']) ? 1 : 0) : 1; // exclude by default if no submit
$_GET['IPaddress'] = (isset($_GET['IPaddress'])) ? $_GET['IPaddress'] : getClientIP();
$tracker_period =  (defined('TRACKER_PERIOD')) ? TRACKER_PERIOD : "4 WEEKS";

$_GET['startdate'] = isset($_GET['startdate']) ? $_GET['startdate'] : date('Y-m-d', strtotime("NOW - ".$tracker_period));
$_GET['enddate'] = isset($_GET['enddate']) ? $_GET['enddate'] : date('Y-m-d');

if($_GET['startdate'] < date('Y-m-d', strtotime("NOW - ".$tracker_period))) {
	$_GET['startdate'] = date('Y-m-d', strtotime("NOW - ".$tracker_period));
}

if($_GET['enddate'] >  date('Y-m-d')) {
	$_GET['enddate'] = date('Y-m-d');
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

if(isset($_GET['showlocal'])) { // show only local results so add ocal identifier to sessions
	mysql_select_db($database_aquiescedb, $aquiescedb);

	$select = "SELECT ID, remote_address FROM track_session";
	$result = mysql_query($select, $aquiescedb) or die(mysql_error());
	if(mysql_num_rows($result)>0) {
		while($row = mysql_fetch_assoc($result)) {
			if(isset($gi2)) {	
				
				$record = $gi2->city($row['remote_address']);
				if($record->country->name == "United Kingdom") {
					$update = "UPDATE track_session SET local = 1 WHERE ID = '".$row['ID']."'";
					mysql_query($update, $aquiescedb) or die(mysql_error());
				}
			}
			if(isset($gi)) {
				$record = geoip_record_by_addr($gi,$row['remote_address']);
				if($record->country_name == "United Kingdom") {
					$update = "UPDATE track_session SET local = 1 WHERE ID = '".$row['ID']."'";
					mysql_query($update, $aquiescedb) or die(mysql_error());
				}
			}
		}
	}
	

}

if(isset($_GET['cleandata']) && (isset($gi2) || isset($gi))) {
	$select = "SELECT track_session.ID AS thissessionID,  track_session.remote_address FROM track_session WHERE track_session.regionID = ".$regionID;
	$result = mysql_query($select, $aquiescedb) or die(mysql_error());
	if(mysql_num_rows($result)>0) {
	 	while($row = mysql_fetch_assoc($result)) {	
			if(isset($gi2)) {
			$record = $gi2->city($row['remote_address']);
			if($record) {
				$location = $record->country->name;				
				if($location=="Russian Federation" || $location == "China" || $location=="Ukraine"  || $location=="Kazakhstan") {
					$deleteSQL = "DELETE track_session, track_page FROM track_session LEFT JOIN track_page ON (track_session.ID = track_page.sessionID) WHERE  track_session.ID = ".GetSQLValueString($row['thissessionID'],"text");
					mysql_query($deleteSQL, $aquiescedb) or die(mysql_error());					
				} // end dodgy		
			}	// end record	
			} else  {
			
			$record = geoip_record_by_addr($gi,$row['remote_address']);
			if($record) {
				$location = $record->country_name;				
				if($location=="Russian Federation" || $location == "China" || $location=="Ukraine"  || $location=="Kazakhstan") {
					$deleteSQL = "DELETE track_session, track_page FROM track_session LEFT JOIN track_page ON (track_session.ID = track_page.sessionID) WHERE  track_session.ID = ".GetSQLValueString($row['thissessionID'],"text");
					mysql_query($deleteSQL, $aquiescedb) or die(mysql_error());					
				} // end dodgy		
			}	// end record 
			}
	 	} // end while
	} // end rsults
	header("location: index.php"); exit;
}

$maxRows = isset($_GET['csv']) ? 50000 : 100;

$currentPage = $_SERVER["PHP_SELF"];

$maxRows_rsVisits = $maxRows;
$pageNum_rsVisits = 0;
if (isset($_GET['pageNum_rsVisits'])) {
  $pageNum_rsVisits = $_GET['pageNum_rsVisits'];
}
$startRow_rsVisits = $pageNum_rsVisits * $maxRows_rsVisits;

$varPages_rsVisits = "2";
if (isset($_GET['pages'])) {
  $varPages_rsVisits = $_GET['pages'];
}
$varHideBots_rsVisits = "0";
if (isset($_GET['hidebots'])) {
  $varHideBots_rsVisits = $_GET['hidebots'];
}
$varLocal_rsVisits = "0";
if (isset($_GET['showlocal'])) {
  $varLocal_rsVisits = $_GET['showlocal'];
}
$varRegionID_rsVisits = "1";
if (isset($regionID)) {
  $varRegionID_rsVisits = $regionID;
}
$varStartDate_rsVisits = "1970-01-01";
if (isset($_GET['startdate'])) {
  $varStartDate_rsVisits = $_GET['startdate'];
}
$varEndDate_rsVisits = "2999-01-01";
if (isset($_GET['enddate'])) {
  $varEndDate_rsVisits = $_GET['enddate'];
}
$varExcludeIP_rsVisits = "1";
if (isset($_GET['excludeIP'])) {
  $varExcludeIP_rsVisits = $_GET['excludeIP'];
}
$varSearchIP_rsVisits = "-1";
if (isset($_GET['searchIP'])) {
  $varSearchIP_rsVisits = $_GET['searchIP'];
}
$varIPaddress_rsVisits = "0.0.0.0";
if (isset($_GET['IPaddress'])) {
  $varIPaddress_rsVisits = $_GET['IPaddress'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsVisits = sprintf("SELECT track_session.ID AS thissessionID, track_session.`datetime`, track_session.remote_address, track_session.referer, track_session.username, track_session.adwords, track_session.prepay, track_session.postpay, track_session.screenwidth, track_session.screenheight, (SELECT COUNT(track_page.ID) FROM track_page WHERE track_page.sessionID = track_session.ID) AS hits FROM track_session WHERE (%s = 0 || track_session.screenwidth IS NOT NULL) AND (%s = 0 OR local = 1) AND track_session.regionID = %s AND DATE(track_session.`datetime`) >= %s AND DATE(track_session.`datetime`) <=%s AND (%s = 0 OR STRCMP(%s, track_session.remote_address) !=0) AND (%s = - 1 OR STRCMP(%s, track_session.remote_address) =0) HAVING hits >= %s ORDER BY track_session.`datetime` DESC", GetSQLValueString($varHideBots_rsVisits, "int"),GetSQLValueString($varLocal_rsVisits, "int"),GetSQLValueString($varRegionID_rsVisits, "int"),GetSQLValueString($varStartDate_rsVisits, "date"),GetSQLValueString($varEndDate_rsVisits, "date"),GetSQLValueString($varExcludeIP_rsVisits, "int"),GetSQLValueString($varIPaddress_rsVisits, "text"),GetSQLValueString($varSearchIP_rsVisits, "text"),GetSQLValueString($varSearchIP_rsVisits, "text"),GetSQLValueString($varPages_rsVisits, "int"));
$query_limit_rsVisits = sprintf("%s LIMIT %d, %d", $query_rsVisits, $startRow_rsVisits, $maxRows_rsVisits);
$rsVisits = mysql_query($query_limit_rsVisits, $aquiescedb) or die(mysql_error());
$row_rsVisits = mysql_fetch_assoc($rsVisits);

if (isset($_GET['totalRows_rsVisits'])) {
  $totalRows_rsVisits = $_GET['totalRows_rsVisits'];
} else {
  $all_rsVisits = mysql_query($query_rsVisits);
  $totalRows_rsVisits = mysql_num_rows($all_rsVisits);
}
$totalPages_rsVisits = ceil($totalRows_rsVisits/$maxRows_rsVisits)-1;

$varRegionID_rsProductPrefs = "1";
if (isset($regionID)) {
  $varRegionID_rsProductPrefs = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsProductPrefs = sprintf("SELECT paymentproviderID FROM productprefs WHERE ID = %s", GetSQLValueString($varRegionID_rsProductPrefs, "int"));
$rsProductPrefs = mysql_query($query_rsProductPrefs, $aquiescedb) or die(mysql_error());
$row_rsProductPrefs = mysql_fetch_assoc($rsProductPrefs);
$totalRows_rsProductPrefs = mysql_num_rows($rsProductPrefs);

$queryString_rsVisits = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsVisits") == false && 
        stristr($param, "totalRows_rsVisits") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsVisits = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsVisits = sprintf("&totalRows_rsVisits=%d%s", $totalRows_rsVisits, $queryString_rsVisits);

if(isset($_GET['csv'])) {
	$headers = array("ID", "Date", "IP", "referer", "User", "Adwords",  "prepay|hide", "postpay|hide", "Pages");
	exportCSV($headers, $rsVisits, "Visitors-YY-MM-DD");
	die();
	
}


?><!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" --><title>
<?php $pageTitle = "Recent Visits"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title><!-- InstanceEndEditable -->
<?php require_once('../../includes/seo.inc.php'); ?>
<?php require_once('../../../includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<link href="css/visitors.css" rel="stylesheet"  />
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../../includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" --><div class="page seo"><?php require_once('../../../region/includes/chooseregion.inc.php'); ?>
   <h1><i class="glyphicon glyphicon-globe"></i> Recent Visits</h1>
   <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav"> <li class="nav-item"><a href="report.php" class="nav-link" ><i class="glyphicon glyphicon-stats"></i> Site Analytics Report</a></li>
   <li class="nav-item"><a href="users.php" class="nav-link"><i class="glyphicon glyphicon-user"></i> User Activity</a></li>
   <li class="nav-item"><a href="index.php?cleandata=true"  onClick="return confirm('This option permanently removes any visit data from major known hacking areas such as Russia, China and Ukraine who would normally be unlikely genuine vistors to UK sites.\n\nContinue?')" class="nav-link"><i class="glyphicon glyphicon-erase"></i> Clean Data</a></li>
    
    <li><a href="historical.php"><i class="glyphicon glyphicon-calendar"></i> Historical</a></li>
     
     <li class="nav-item"><a href="/core/seo/admin/index.php" class="nav-link"><i class="glyphicon glyphicon-thumbs-up"></i> Manage SEO</a></li> 
   </ul></div></nav> 
   <form action="index.php" method="get" name="form1" id="form1"><fieldset class="form-group form-inline"><legend>Filter</legend>
       Visiting
       <select class="form-control" name="pages"  id="pages" onChange="this.form.submit();">
         <option value="1" <?php if (!(strcmp(1, $_GET['pages']))) {echo "selected=\"selected\"";} ?>>1</option>
         <option value="2" <?php if (!(strcmp(2, $_GET['pages']))) {echo "selected=\"selected\"";} ?>>2</option>
         <option value="5" <?php if (!(strcmp(5, $_GET['pages']))) {echo "selected=\"selected\"";} ?>>5</option>
         <option value="10" <?php if (!(strcmp(10, $_GET['pages']))) {echo "selected=\"selected\"";} ?>>10</option>
         <option value="20" <?php if (!(strcmp(20, $_GET['pages']))) {echo "selected=\"selected\"";} ?>>20</option>
       </select> between<input name="startdate" id="startdate" type="hidden" value="<?php  $inputname = "startdate"; $setvalue =  htmlentities($_GET['startdate']); echo $setvalue; ?>" /><?php require('../../../includes/datetimeinput.inc.php'); ?> and <input name="enddate" id="enddate" type="hidden" value="<?php $inputname = "enddate"; $setvalue =  htmlentities($_GET['enddate']); echo $setvalue; ?>" /><?php require('../../../includes/datetimeinput.inc.php'); ?>
 <button name="Go" type="submit" class="btn btn-default btn-secondary" id="Go" >Go</button><br />
 <label><input <?php if (isset($_GET['hidebots']) && $_GET['hidebots']==1) {echo "checked=\"checked\"";} ?> name="hidebots" type="checkbox" id="showlohidebotscal" value="1" onClick="alert('Known bots, e.g. Googlebot, are always excluded. This option excludes other visitors suspected of being bots.'); this.form.submit()" />
  hide suspected bots</label>

  
   &nbsp;&nbsp;&nbsp; 

 <label><input <?php if (!(strcmp(@$_GET['showlocal'],1))) {echo "checked=\"checked\"";} ?> name="showlocal" type="checkbox" id="showlocal" value="1"  onClick="this.form.submit()"/>
  show only internal</label>
  
   &nbsp;&nbsp;&nbsp;
 <label><input <?php if (!(strcmp(@$_GET['csv'],1))) {echo "checked=\"checked\"";} ?> name="csv" type="checkbox" id="showlocal" value="1" onClick="this.form.submit()" />
  download as spreadseet</label>
  &nbsp;&nbsp;&nbsp;

<label>
  <input name="excludeIP" type="checkbox" id="excludeIP" value="1" <?php if (!(strcmp(@$_GET['excludeIP'],1))) {echo "checked=\"checked\"";} ?> />
exclude this IP address:</label>


  <input name="IPaddress" type="text"  id="IPaddress" value="<?php echo isset($_GET['IPaddress']) ? htmlentities($_GET['IPaddress']) : getClientIP(); ?>" size="15" class="form-control" /> 

  
   </fieldset> </form>
   <?php if ($totalRows_rsVisits == 0) { // Show if recordset empty ?>
    
   <p>There is no data stored at present.</p>
     <?php } // Show if recordset empty ?>
 <?php if ($totalRows_rsVisits > 0) { // Show if recordset not empty ?>
     
        <p><strong>Total visits: <?php echo $totalRows_rsVisits ?></strong> (Showing <?php echo ($startRow_rsVisits + 1) ?> to <?php echo min($startRow_rsVisits + $maxRows_rsVisits, $totalRows_rsVisits) ?>) &nbsp;&nbsp;&nbsp;  <strong>Key:</strong> <img src="/core/images/icons/google_favicon.png" alt="Google Adwords" width="16" height="16" style="vertical-align:
middle;"> = AdWords referral<?php if($row_rsProductPrefs['paymentproviderID']>0) { ?> &nbsp;&nbsp;&nbsp; <img src="/core/images/icons/money_pound_0.png" width="16" height="16" style="vertical-align:
middle;">= Started shopping&nbsp;&nbsp;&nbsp; <img src="/core/images/icons/money_pound_1.png" width="16" height="16" style="vertical-align:
middle;">= Purchase made<?php } ?></p> 
     <div class="table-responsive">
     <table  class="table table-hover">
     <thead>
       <tr>
         <th>Date</th>
          <th>Time</th>
             <th>User</th>
       <th>From</th>
          
          <th>Referrer </th>
          <th class="keywords">Keywords</th>
          <th colspan="3">Pages</th>
        </tr></thead> <tbody>
       <?php do { 
	   $location = "";
	    if(ip2long($row_rsVisits['remote_address'])) {
			// is valid IPv4 address
	   if(isset($gi2)) {			
			
					
			try {
        	$record = $gi2->city($row_rsVisits['remote_address']);
    } catch (GeoIp2\Exception\AddressNotFoundException $e) {
        // Your handling of the not-found case here.
		$record = false;
    }
			if($record) {
				$location = $record->country->name;				
				$location .= isset($record->city->name) ? " - ".$record->city->name : " - unknown locale"; 
			}		  
		}  else if(isset($gi)) {
			$record = geoip_record_by_addr($gi,$row_rsVisits['remote_address']);
			if($record) {
				$location = $record->country_name;				
				$location .= isset($record->city) ? " - ".$record->city : " - unknown locale"; 
			}		  
		}  
		}
		
			
			?>
           
         <tr>
           <td class="text-nowrap"><?php echo date('M d',strtotime($row_rsVisits['datetime'])); ?></td>
           <td class="text-nowrap"><?php echo date('H:i',strtotime($row_rsVisits['datetime'])); ?></td>
           <td class="text-nowrap"><?php if(isset($row_rsVisits['username'])) { ?><a href="user_sessions.php?username=<?php echo $row_rsVisits['username']; ?>" > <?php echo $row_rsVisits['username']; ?></a><?php } else { ?><em>Public</em><?php }  ?></td>
           
           <td><?php 
		   
				
			if ( $location == "")  {
				echo  $row_rsVisits['remote_address'];
	   } else {
		   // GEOIP1 responds in ISO-8859-1
		    echo isset($gi) ? utf8_encode($location) :$location ; 
	   } 
			
	   ?></td>
           
           <td class="text-nowrap"><?php echo getHostReferer($row_rsVisits['referer']); echo ($row_rsVisits['adwords']==1) ?   "&nbsp;<img src=\"/core/images/icons/google_favicon.png\" width=\"16\" height=\"16\" alt=\"Google Adwords\"  style=\"vertical-align:
middle;\" >" : ""; ?></td>
           <td  class="keywords"><?php $keywords =  getSearchTerms($row_rsVisits['referer']); echo "<span title=\"".$keywords."\">".$keywords."</span>"; ?></td>
           <td><?php echo $row_rsVisits['hits']; ?></td>
           <td class="payment<?php echo $row_rsVisits['prepay']; ?><?php echo $row_rsVisits['postpay']; ?>">&nbsp;&nbsp;</td>
           <td><a href="visitor-session.php?sessionID=<?php echo urlencode($row_rsVisits['thissessionID']); ?>" class="btn btn-default btn-secondary"><i class="glyphicon glyphicon-search"></i> View</a></td>
         </tr>
         <?php 
		 } while ($row_rsVisits = mysql_fetch_assoc($rsVisits)); ?>
</tbody></table></div>
     <?php } // Show if recordset not empty ?>
<?php echo createPagination($pageNum_rsVisits,$totalPages_rsVisits,"rsVisits") ; ?>

  <?php 
  
   // work out average  
   $result = mysql_query($query_rsVisits, $aquiescedb) or die(mysql_error());
   $num_rows = mysql_num_rows($result);
   $total_hits = 0;
   if($num_rows>0) {
	   while($row=mysql_fetch_assoc($result)) {
		   $total_hits += $row['hits'];
	   }
	   echo "<p><strong>AVERAGE HITS: ".number_format(($total_hits/$num_rows),2)."</strong></p>";
   }
   
?></div>
<!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsVisits);

mysql_free_result($rsProductPrefs);
?>
