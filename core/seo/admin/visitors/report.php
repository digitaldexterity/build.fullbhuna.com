<?php ini_set('mysql.connect_timeout', 300);
ini_set('default_socket_timeout', 300);
?>
<?php require_once('../../../../Connections/aquiescedb.php'); ?>
<?php require_once('../../../includes/adminAccess.inc.php'); ?>
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



$javascript = "";

?>
<?php
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

$MM_restrictGoTo = "/login/index.php?notloggedin=true&alert=".urlencode("You need to be logged in as an Administrator to access this page.");
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

$_GET['hidebots'] = (isset($_GET['hidebots']) || !isset($_GET['startdate'])) ? 1 : 0; // default to hiding bots


//$_GET['startdate'] = isset($_GET['startdate']) ? $_GET['startdate'] : date('Y-m-d', strtotime("1 MONTH AGO"));
$tracker_period =  (defined('TRACKER_PERIOD')) ? TRACKER_PERIOD : "4 WEEKS";

$_GET['startdate'] = isset($_GET['startdate']) ? $_GET['startdate'] : date('Y-m-d', strtotime("NOW - ".$tracker_period));
$_GET['enddate'] = isset($_GET['enddate']) ? $_GET['enddate'] : date('Y-m-d');
$_GET['enddate'] = ($_GET['enddate'] < $_GET['startdate']) ? $_GET['startdate'] : $_GET['enddate'];

$varEndDate_rsVisits = "2999-01-01";
if (isset($_GET['enddate'])) {
  $varEndDate_rsVisits = $_GET['enddate'];
}
$varHideBots_rsVisits = "1";
if (isset($_GET['hidebots'])) {
  $varHideBots_rsVisits = $_GET['hidebots'];
}
$varRegionID_rsVisits = "1";
if (isset($regionID)) {
  $varRegionID_rsVisits = $regionID;
}
$varStartDate_rsVisits = "1970-01-01";
if (isset($_GET['startdate'])) {
  $varStartDate_rsVisits = $_GET['startdate'];
}
$varLocal_rsVisits = "0";
if (isset($_GET['showlocal'])) {
  $varLocal_rsVisits = $_GET['showlocal'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsVisits = sprintf("SELECT track_session.ID AS thissessionID, track_session.`datetime`, track_session.remote_address, track_session.referer, track_session.username, track_session.prepay, track_session.postpay, track_session.user_agent, track_session.screenwidth, track_session.screenheight, '' AS entryPage FROM track_session WHERE (%s = 0 || track_session.screenwidth IS NOT NULL) AND  (%s = 0 OR local =1) AND track_session.regionID = %s AND DATE(track_session.`datetime`) >= %s AND DATE(track_session.`datetime`) <= %s ORDER BY track_session.`datetime` DESC", GetSQLValueString($varHideBots_rsVisits, "int"),GetSQLValueString($varLocal_rsVisits, "int"),GetSQLValueString($varRegionID_rsVisits, "int"),GetSQLValueString($varStartDate_rsVisits, "date"),GetSQLValueString($varEndDate_rsVisits, "date"));
$rsVisits = mysql_query($query_rsVisits, $aquiescedb) or die(mysql_error());
$row_rsVisits = mysql_fetch_assoc($rsVisits);
$totalRows_rsVisits = mysql_num_rows($rsVisits);

$varRegionID_rsEntryPages = "1";
if (isset($regionID)) {
  $varRegionID_rsEntryPages = $regionID;
}
$varLocal_rsEntryPages = "0";
if (isset($_GET['showlocal'])) {
  $varLocal_rsEntryPages = $_GET['showlocal'];
}
$varStartDate_rsEntryPages = "1970-01-01";
if (isset($_GET['startdate'])) {
  $varStartDate_rsEntryPages = $_GET['startdate'];
}
$varEndDate_rsEntryPages = "2999-01-01";
if (isset($_GET['enddate'])) {
  $varEndDate_rsEntryPages = $_GET['enddate'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsEntryPages = sprintf("SELECT track_page.page,  track_page.pageTitle,COUNT(track_page.pageTitle) AS hits FROM track_session LEFT JOIN track_page ON (track_session.entrypageID = track_page.ID) WHERE  (%s = 0 OR local =1) AND track_session.regionID = %s AND DATE(track_session.datetime) >= %s AND DATE(track_session.datetime) <= %s GROUP BY track_page.pageTitle ORDER BY hits DESC", GetSQLValueString($varLocal_rsEntryPages, "int"),GetSQLValueString($varRegionID_rsEntryPages, "int"),GetSQLValueString($varStartDate_rsEntryPages, "date"),GetSQLValueString($varEndDate_rsEntryPages, "date"));
$rsEntryPages = mysql_query($query_rsEntryPages, $aquiescedb) or die(mysql_error());
$row_rsEntryPages = mysql_fetch_assoc($rsEntryPages);
$totalRows_rsEntryPages = mysql_num_rows($rsEntryPages);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = "SELECT googlemapsAPI, defaultzoom, defaultlongitude, defaultlatitude FROM preferences WHERE ID = ".intval($regionID)."";
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error().": ".$query_rsPreferences );
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);

$orderby = isset($_GET['alphabetical']) ? "track_page.pageTitle ASC": "hits DESC";

$varStartDate_rsPages = "1970-01-01";
if (isset($_GET['startdate'])) {
  $varStartDate_rsPages = $_GET['startdate'];
}
$varLocal_rsPages = "0";
if (isset($_GET['showlocal'])) {
  $varLocal_rsPages = $_GET['showlocal'];
}
$varRegionID_rsPages = "1";
if (isset($regionID)) {
  $varRegionID_rsPages = $regionID;
}
$varEndDate_rsPages = "2020-01-01";
if (isset($_GET['enddate'])) {
  $varEndDate_rsPages = $_GET['enddate'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPages = sprintf("SELECT COUNT(track_page.pageTitle) AS hits, track_page.pageTitle, track_page.page FROM track_page LEFT JOIN track_session ON (track_page.sessionID = track_session.ID) WHERE (%s = 0 OR local = 1) AND pageTitle NOT LIKE 'Login%%' AND track_session.regionID = %s AND DATE(track_session.datetime) >= %s AND DATE(track_session.datetime) <%s GROUP BY pageTitle ORDER BY ".$orderby."", GetSQLValueString($varLocal_rsPages, "int"),GetSQLValueString($varRegionID_rsPages, "int"),GetSQLValueString($varStartDate_rsPages, "date"),GetSQLValueString($varEndDate_rsPages, "date"));
$rsPages = mysql_query($query_rsPages, $aquiescedb) or die(mysql_error());
$row_rsPages = mysql_fetch_assoc($rsPages);
$totalRows_rsPages = mysql_num_rows($rsPages);



$browser_count = array();
$os_count = array();
$keywords_count = array();
$entry_pages_count = array();
$referer_count = array();
$search_engine_count = array();
$screen_count = array();
$search_engine_count["Google"] = 0;
$search_engine_count["Duck Duck Go"] = 0;
$search_engine_count["Yahoo"] = 0;
$search_engine_count["Bing"] = 0;
$search_engine_count["Other/Not Search Engine"] = 0;

$geoip = array();
$searchtotal = 0;

function sortmulti ($array, $index, $order, $natsort=FALSE, $case_sensitive=FALSE) {
        if(is_array($array) && count($array)>0) {
            foreach(array_keys($array) as $key) 
            $temp[$key]=$array[$key][$index];
            if(!$natsort) {
                if ($order=='asc')
                    asort($temp);
                else    
                    arsort($temp);
            }
            else 
            {
                if ($case_sensitive===true)
                    natsort($temp);
                else
                    natcasesort($temp);
            if($order!='asc') 
                $temp=array_reverse($temp,TRUE);
            }
            foreach(array_keys($temp) as $key) 
                if (is_numeric($key))
                    $sorted[]=$array[$key];
                else    
                    $sorted[$key]=$array[$key];
            return $sorted;
        }
    return $sorted;
}

function periodHits($startdatetime, $enddatetime) {
	global $database_aquiescedb, $aquiescedb;
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$select = "SELECT COUNT(track_session.ID) AS hits FROM track_session WHERE (".intval($varLocal_rsPages)." = 0 OR local =1) AND datetime > '".$startdatetime."' AND datetime <= '".$enddatetime."'";
	$result = mysql_query($select, $aquiescedb) or die(mysql_error().": ".$select);
	$row = mysql_fetch_assoc($result);
	return $row['hits'];
}
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Visitor Statistics Report"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../includes/seo.inc.php'); ?>
<?php require_once('../../../includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<!--[if IE]><script src="/3rdparty/jquery/jquery.visualise/excanvas.compiled.js"></script><![endif]-->
<?php $googlemapsAPI =isset($googlemapsAPI) ? $googlemapsAPI : $row_rsPreferences['googlemapsAPI'];
if($googlemapsAPI!="") { ?>
<script src="//maps.googleapis.com/maps/api/js?key=<?php echo $googlemapsAPI; ?>&v=3" ></script>
<script src="https://cdn.rawgit.com/googlemaps/js-marker-clusterer/gh-pages/src/markerclusterer.js"></script>
<script>

var map;
var markers = [];
var infowindow = [];
var initLatitude = <?php echo isset($row_rsPreferences['defaultlatitude']) ? $row_rsPreferences['defaultlatitude'] : 0; ?>;
var initLongitude = <?php echo isset($row_rsPreferences['defaultlongitude']) ? $row_rsPreferences['defaultlongitude'] : 0; ?>;


$(document).ready(function(e) {	
   var mapOptions = {
        zoom: 2 ,
        center: new google.maps.LatLng(initLatitude, initLongitude),
        scaleControl: true,
        overviewMapControl: true,
        overviewMapControlOptions:{opened:true},
        mapTypeId: google.maps.MapTypeId.ROADMAP,
		scrollwheel: false
	};
	map = new google.maps.Map(document.getElementById('googlemap'), mapOptions);
	var markerCluster = new MarkerClusterer(map, markers, { 
    imagePath: 'https://cdn.rawgit.com/googlemaps/js-marker-clusterer/gh-pages/images/m' 
});
	
	
	// fix for spry tabs
	$(".TabbedPanelsTab").click(function() {
		google.maps.event.trigger(map, 'resize');	
		var latLng = new google.maps.LatLng(initLatitude, initLongitude)
		map.setCenter(latLng);
	});

	
	
});

//]]>
</script>
<?php } ?><script src="https://code.highcharts.com/highcharts.src.js"></script><script src="/3rdparty/jquery/jquery.highchartTable-min.js"></script>
<script src="/SpryAssets/SpryTabbedPanels.js"></script>
<link href="/SpryAssets/SpryTabbedPanels.css" rel="stylesheet"  />

<script>
 $(document).ready(function() {
	$('.graph-line').hide();
	
	
	$('table.highchart').highchartTable();
	
});
</script>
<style>
<!--

 <?php if(!isset($gi) && !isset($gi2)) {
echo "#tab_geographical { display:none; }";
}
?>
-->
</style>
<!-- InstanceEndEditable -->
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
    <?php require_once('../../../region/includes/chooseregion.inc.php'); ?>
    <h1><i class="glyphicon glyphicon-globe"></i> Site Visitors Analytics Report</h1>
    <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
      <li class="nav-item"><a href="index.php" class="nav-link"><i class="glyphicon glyphicon-arrow-left"></i> Back to Vistors</a></li>
    </ul></div></nav>
    <form action="report.php" method="get" name="form1" id="form1">
      <fieldset class="form-inline">
        <legend>Filter</legend>
        Visitors between
        <input type="hidden" name="startdate" id="startdate" value="<?php $setvalue = isset($_GET['startdate']) ? $_GET['startdate'] : date('Y-m-d',strtotime("1 MONTH AGO")); echo $setvalue; $inputname = "startdate"; ?>" />
        <?php require('../../../includes/datetimeinput.inc.php'); ?>
        and
        <input type="hidden" name="enddate" id="enddate" value="<?php $setvalue = isset($_GET['enddate']) ? $_GET['enddate'] : date('Y-m-d'); echo $setvalue; $inputname = "enddate"; ?>" />
        <?php require('../../../includes/datetimeinput.inc.php'); ?>
        <button type="submit"  class="btn btn-default btn-secondary" >Show</button>
        <br>
        <label><input <?php if (isset($_GET['hidebots']) && $_GET['hidebots']==1) {echo "checked=\"checked\"";} ?> name="hidebots" type="checkbox" id="showlohidebotscal" value="1" onClick="alert('Known bots, e.g. Googlebot, are always excluded. This option excludes other visitors suspected of being bots.'); this.form.submit()" />
  hide suspected bots</label>

  
   &nbsp;&nbsp;&nbsp; 
   
        <label>
          <input type="checkbox" name="detail" id="detail" <?php echo isset($_GET['detail']) ? "checked=\"checked\"" : ""; ?> />
          Show details</label>
        &nbsp;&nbsp;&nbsp;
        <label class="text-nowrap">
          <input type="checkbox" name="alphabetical" id="alphabetical" <?php echo isset($_GET['alphabetical']) ? "checked=\"checked\"" : ""; ?> />
          Alphabetical</label>
        &nbsp;&nbsp;&nbsp;
        <label class="text-nowrap">
          <input <?php if (!(strcmp(@$_GET['showlocal'],1))) {echo "checked=\"checked\"";} ?> name="showlocal" type="checkbox" id="showlocal" value="1" />
          show only local</label>
      </fieldset>
    </form>
    <h2>Total site visits (1 page or more) in period: <?php echo $totalRows_rsVisits; ?></h2>
    <?php if($totalRows_rsVisits>0) {
		 echo (isset($_GET['detail'])) ? "<table border=\"1\" cellpadding=\"0\" cellspacing=\"0\" style=\"table-layout:fixed; width:100%\" >" : ""; ?>
    <?php 
	   do { $browser = getBrowser($row_rsVisits['user_agent']);
	   $browser_version = intval($browser['version']);
	   $browser_name = $browser['name']; 
	   $browser_count[$browser_name][$browser_version] = isset($browser_count[$browser_name][$browser_version]) ? $browser_count[$browser_name][$browser_version]+1 : 1;
	   
	   $p= ""; $q="";
if(isset($row_rsVisits['referer'])) {
	$referer = getHostReferer($row_rsVisits['referer']);

	if(strlen($referer)>1) {
		
		$referer_count[$referer] = isset($referer_count[$referer]) ? $referer_count[$referer]+1 : 1;
		if(strpos($referer, "google")!==false) {
				$search_engine_count["Google"]++;
		} else if(strpos($referer, "bing")!==false) {
			$search_engine_count["Bing"]++;
		} else if(strpos($referer, "yahoo")!==false) {
			$search_engine_count["Yahoo"]++;
		} else if(strpos($referer, "duckduckgo")!==false) { $search_engine_count["Duck Duck Go"]++;
		} else {
			$search_engine_count["Other/Not Search Engine"]++;		
		}
		$searchtotal++;
		$keywords = getSearchTerms($row_rsVisits['referer']);
		if(strlen($keywords)>1) {
			
			$keywords_count[$keywords] = isset($keywords_count[$keywords]) ? $keywords_count[$keywords] + 1 : 1;
		}
	}
}


	  
	   
	   if(isset($gi2)) {
try {
        	$record = $gi2->city($row_rsVisits['remote_address']);
    } catch (GeoIp2\Exception\AddressNotFoundException $e) {
        // Your handling of the not-found case here.
		$record = false;
    }			
				if($record) {
				$location = $record->country->name." - ";
				$location .= isset($record->city->name) ? $record->city->name : "unknown locale";
		  if(isset($geoip[$location]['count'])) {
			   $geoip[$location]['count']++ ;
			  } else {
				  $geoip[$location]['count'] = 1;
				  $geoip[$location]['long'] = $record->location->longitude;
				  $geoip[$location]['lat'] = $record->location->latitude;
				  
				  
				  
				  
    

  $javascript .= "var latLng = new google.maps.LatLng(".$geoip[$location]['lat'].",".$geoip[$location]['long'].");
  var marker = new google.maps.Marker({'position': latLng});
  markers.push(marker);";
				   
		  }
		  			}
		  
	   } else if(isset($gi)) {
			$record = geoip_record_by_addr($gi,$row_rsVisits['remote_address']);
			if($record) {
				$location = $record->country_name." - ";
				$location .= isset($record->city) ? $record->city : "unknown locale";
		  if(isset($geoip[$location]['count'])) {
			   $geoip[$location]['count']++ ;
			  } else {
				  $geoip[$location]['count'] = 1;
				  $geoip[$location]['long'] = $record->longitude;
				  $geoip[$location]['lat'] = $record->latitude;
				  
				  
				  
				  
    

  $javascript .= "var latLng = new google.maps.LatLng(".$geoip[$location]['lat'].",".$geoip[$location]['long'].");
  var marker = new google.maps.Marker({'position': latLng});
  markers.push(marker);";
				   
		  }
		  			}
		  
	   }
	    $os_count[$browser['platform']] = isset($os_count[$browser['platform']]) ? $os_count[$browser['platform']]+1 : 1; 
		
		$screen_count[$row_rsVisits['screenwidth']."x".$row_rsVisits['screenheight']] = isset($screen_count[$row_rsVisits['screenwidth']."x".$row_rsVisits['screenheight']]) ? $screen_count[$row_rsVisits['screenwidth']."x".$row_rsVisits['screenheight']]+1 : 1; 
	if (isset($_GET['detail']))  {  
	   ?>
    <tr>
      <td class="text-nowrap"><?php echo date('d M H:i',strtotime($row_rsVisits['datetime'])); ?></td>
      <td><?php echo $row_rsVisits['entryPage']; ?></td>
      <td><?php echo $row_rsVisits['remote_address']."<br>".$location;?></td>
      <td><?php echo $browser_name." ".$browser_version; ?></td>
      <td><?php  echo $browser['platform']; ?></td>
      <td><?php echo $p.$q; ?></td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td colspan="5" ><div style="overflow:hidden; width:100%"><?php echo $row_rsVisits['user_agent']; ?></div></td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td colspan="5" ><div style="overflow:hidden; width:100%"><?php echo $row_rsVisits['referer']; ?></div></td>
    </tr>
    <?php }
         } while ($row_rsVisits = mysql_fetch_assoc($rsVisits)); 
    echo (isset($_GET['detail'])) ? "</table>" : ""; ?>
    
    
    <div id="TabbedPanels1" class="TabbedPanels">
      <ul class="TabbedPanelsTabGroup">
        <li class="TabbedPanelsTab" tabindex="0">Timeline</li>
        <li class="TabbedPanelsTab" tabindex="0" id="tab_search"  >Searches</li>
        <li class="TabbedPanelsTab" tabindex="0">Pages</li>
        <li class="TabbedPanelsTab" tabindex="0" id="tab_geographical">Geographical</li>
        <li class="TabbedPanelsTab" tabindex="0">System</li>
      </ul>
      <div class="TabbedPanelsContentGroup">
        <div class="TabbedPanelsContent">
        
          <table  class="graph-line highchart" data-graph-container-before="1" data-graph-type="line">
          <thead>
            <tr><th>Date</th><th>Visits</th></tr></thead>
            <tbody>
              <?php $increment = "1 HOUR";
		$period = strtotime($_GET['enddate']) - strtotime($_GET['startdate']);
		if($period>60*60) { $increment = "1 HOUR"; $label = "H";  }
		if($period>60*60*24) { $increment = "1 DAY"; $label = "d";  }
		if($period>60*60*24*31) { $increment = "1 WEEK";  $label = "d";  }
		if($period>60*60*24*31*6) { $increment = "1 MONTH";  $label = "M";  }
		if($period>60*60*24*365*2) { $increment = "1 YEAR"; $label = "Y"; }
		$data = "";
		$periodstart = $_GET['startdate']." 00:00:00";
		$periodend = date('Y-m-d H:i:s', strtotime($periodstart." + ".$increment));
		while($periodend<$_GET['enddate']." 23:59:59") { ?><tr>
              <td><?php echo date('d',strtotime($periodstart)); ?></td>
              <td><?php echo periodHits($periodstart,$periodend); ?></td><?php
					 $periodstart = $periodend;
			$periodend = date('Y-m-d H:i:s', strtotime($periodend." + ".$increment)); ?></tr>
			
			
	<?php	}?>
            </tbody>
          </table>
        </div><!-- end tab -->
        <div class="TabbedPanelsContent">
        
        <div class="row">
        <div class="col-md-4">
         <h2>Referrers</h2>
          <p>The site visitors arrived from:</p>
          <ol>
            <?php  arsort($referer_count);
	 foreach($referer_count as $key => $value) {
		 if($key==$_SERVER['HTTP_HOST']) $key = "Direct (no referrer)";
		echo "<li><a href=\"referers.php?referer=".htmlentities($key, ENT_COMPAT, "UTF-8")."\">".htmlentities($key, ENT_COMPAT, "UTF-8")." (".htmlentities($value, ENT_COMPAT, "UTF-8").")</a></li>";
	}  // end for each ?>
          </ol>
        </div><!--end col -->
         
          <?php if($searchtotal>0) { 
		  arsort($search_engine_count); ?>
          <div class="col-md-4"> <h2>Search Engines</h2>
            <p>How visitors found you:</p>
            
            
            
            
            <table class="graph-pie highchart" data-graph-container-before="1" data-graph-type="pie" data-graph-datalabels-enabled="1">
           
              <thead>
                <tr>
                  <th>Search Engine</th>
                  <th>%</th>
                </tr>
              </thead>
              <tbody>
                <?php 
	 foreach($search_engine_count as $key => $value) { ?>
                <tr>
                  <td><?php echo htmlentities($key, ENT_COMPAT, "UTF-8"); ?></td>
                  <td data-graph-name="<?php echo htmlentities($key, ENT_COMPAT, "UTF-8"); ?>" ><?php echo number_format(($value/$searchtotal*100),1,".",""); ?></td>
                </tr>
                <?php } // end for each ?>
              </tbody>
            </table>
            
            
            
            
            
            
            
            
            
            
            
            
            
            
          </div><!-- end col -->
         
        <div class="col-md-4">
        <h2>Keywords</h2>
        <p>The search terms used to find your site:</p>
        <ol>
          <?php arsort($keywords_count);
	 foreach($keywords_count as $key => $value) {
		echo "<li>".htmlentities($key, ENT_COMPAT, "UTF-8")." - ".number_format(($value/$searchtotal*100),1)."%</li>";
	} ?>
        </ol></div><!--end col-->
        <?php } else { ?>
        <div class="col-md-8">
        <p>There were no visits from search engines.</p>
        </div><!-- end col-->
        <?php } ?>
        </div><!-- end row-->
      </div><!-- end tab -->
      <div class="TabbedPanelsContent">
      <div class="row">
        <div class="col-md-6">
          <h2>Entry Pages</h2>
          <p>The first page visitors arrived at:</p>
          <ol>
            <?php do { ?>
              <li><a href="page.php?page=<?php echo urlencode($row_rsEntryPages['page']); ?>" title="<?php echo htmlentities($row_rsEntryPages['page'], ENT_COMPAT, "UTF-8"); ?>" data-toggle="tooltip"><?php echo ($row_rsEntryPages['pageTitle'] !="") ? htmlentities($row_rsEntryPages['pageTitle'], ENT_COMPAT, "UTF-8") : htmlentities($row_rsEntryPages['page'], ENT_COMPAT, "UTF-8"); ?></a> (<?php echo $row_rsEntryPages['hits']; ?>)</li>
              <?php } while ($row_rsEntryPages = mysql_fetch_assoc($rsEntryPages)); ?>
        </ol>
        </div><!-- end col-->
        <div class="col-md-6">
        <h2>Page Popularity</h2>
        <p>The most frequently visited pages:</p>
        <ol>
          <?php do { ?>
            <li> <a href="page.php?page=<?php echo urlencode($row_rsPages['page']); ?>" title="<?php echo htmlentities($row_rsPages['page'], ENT_COMPAT, "UTF-8"); ?>" data-toggle="tooltip"><?php echo ($row_rsPages['pageTitle'] !="") ? htmlentities($row_rsPages['pageTitle'], ENT_COMPAT, "UTF-8") : htmlentities($row_rsPages['page'], ENT_COMPAT, "UTF-8"); ?></a> (<?php echo $row_rsPages['hits']; ?>)</li>
            <?php } while ($row_rsPages = mysql_fetch_assoc($rsPages)); ?>
        </ol>
       </div><!-- end col-->
       </div><!-- end row -->
      </div><!-- end tab -->
      <div class="TabbedPanelsContent" >
        <h2>Geographic</h2>
        <p>Where visitors came from:</p>
        <?php if(isset($gi)|| isset($gi2)) { ?>
        <div class="googlemap" id="googlemap"></div>
        <ol>
          <?php $geoip = sortmulti($geoip, "count", "desc");
	 foreach($geoip as $key => $value) {
		echo "<li>".htmlentities($key, ENT_COMPAT, "UTF-8")." - ".number_format(($geoip[$key]['count']/$totalRows_rsVisits*100),1)."%</li>";
	} ?>
        </ol>
        <p>Please note that due to the nature of the Internet, this infomation is never 100% accurate, so is for guidance only.</p>
        <?php } else { ?>
        <p>No geographical information available.</p>
        <?php } ?>
      </div>
      <div class="TabbedPanelsContent">
      <div class="row"><div class="col-md-4">
        <h2>Browsers</h2>
        <table class="table">
        <thead>
          <tr>
            <th>Browser</th>
            <th>Share</th>
            <th>Version breakdown</th>
          </tr></thead><tbody>
          <?php  
	foreach($browser_count as $browsername => $value) {
		$total = 0; 
		$breakdown[$browsername] = "";
		ksort($browser_count[$browsername]);
		foreach($browser_count[$browsername] as $version => $count) {
			$percent = number_format(($count/$totalRows_rsVisits*100),1);
			 $breakdown[$browsername] .= ($version > 0 && $percent > 0) ? "&nbsp;&nbsp;&nbsp;".$version."&nbsp;<em>(".$percent."%)</em> " : "";
			 $total += $percent;
		}
		$browsertotal[$browsername] = $total;
		
	}
	arsort($browsertotal);
	foreach($browsertotal as $browsername => $total) {
		echo "<tr><td>".$browsername."</td><td>".$total."%</td><td>";
		echo isset($breakdown[$browsername]) ? $breakdown[$browsername] : "&nbsp;";
		echo "</td></tr>";
	}
	?></tbody>
        </table>
        <p><em>*indicitive results as some browsers deliberately masquerade as others</em></p>
        </div><!-- end col --><div class="col-md-4">
        <h2>Operating systems</h2>
        <table class="table">
        <thead>
        <tr><th>Operating System</th><th>Share</th></tr></thead>
        <tbody>
        
        
          <?php arsort($os_count);
	 foreach($os_count as $key => $value) {
		echo "<tr><td>".htmlentities($key, ENT_COMPAT, "UTF-8")."</td><td >".number_format(($value/$totalRows_rsVisits*100),1)."%</td></tr>";
	} ?>
        </tbody></table>
        </div><!-- end col --><div class="col-md-4">
        <h2>Screen sizes</h2>
        <table class="table">
        <thead>
        <tr><th>Screen Size</th><th>Share</th></tr></thead>
        <tbody>
        
          <?php arsort($screen_count);
	 foreach($screen_count as $key => $value) {
		echo "<tr><td>";
		echo ($key=="x") ? "Unknown" : $key;
		echo "</td><td>".number_format(($value/$totalRows_rsVisits*100),1)."%</td></tr>";
	} ?>
      </tbody></table>
        </div><!--end col -->
       </div><!-- end row -->
      </div><!-- end tab -->
    </div><!-- end group -->
  </div><!-- end tabs -->
  <script>
<?php echo $javascript; ?>

    </script> 
  <script>
<!--
var TabbedPanels1 = new Spry.Widget.TabbedPanels("TabbedPanels1");
//-->
    </script>
  <?php } // visits > 0 ?>
  </div><!-- end page -->
  <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsVisits);

mysql_free_result($rsEntryPages);

mysql_free_result($rsPreferences);

mysql_free_result($rsPages);
?>
