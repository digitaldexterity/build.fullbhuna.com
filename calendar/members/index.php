<?php require_once('../../Connections/aquiescedb.php'); ?><?php
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

$MM_restrictGoTo = "../../login/index.php?notloggedin=true";
if (!((isset($_SESSION['MM_Username'])) && (isAuthorized("",$MM_authorizedUsers, $_SESSION['MM_Username'], $_SESSION['MM_UserGroup'])))) {   
  $MM_qsChar = "?";
  $MM_referrer = $_SERVER['PHP_SELF'];
  if (strpos($MM_restrictGoTo, "?")) $MM_qsChar = "&";
  if (isset($_SESSION['QUERY_STRING']) && strlen($_SESSION['QUERY_STRING']) > 0) 
  $MM_referrer .= "?" . $_SESSION['QUERY_STRING'];
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

$varRegionID_rsEventPrefs = "1";
if (isset($regionID)) {
  $varRegionID_rsEventPrefs = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsEventPrefs = sprintf("SELECT * FROM eventprefs WHERE ID = %s", GetSQLValueString($varRegionID_rsEventPrefs, "int"));
$rsEventPrefs = mysql_query($query_rsEventPrefs, $aquiescedb) or die(mysql_error());
$row_rsEventPrefs = mysql_fetch_assoc($rsEventPrefs);
$totalRows_rsEventPrefs = mysql_num_rows($rsEventPrefs);


$maxRows_rsMyEvents = 50;
$pageNum_rsMyEvents = 0;
if (isset($_GET['pageNum_rsMyEvents'])) {
  $pageNum_rsMyEvents = $_GET['pageNum_rsMyEvents'];
}
$startRow_rsMyEvents = $pageNum_rsMyEvents * $maxRows_rsMyEvents;

$varUsername_rsMyEvents = "-1";
if (isset($_SESSION['MM_Username'])) {
  $varUsername_rsMyEvents = $_SESSION['MM_Username'];
}
$varStartDate_rsMyEvents = "1970-01-01";
if (isset($_GET['startdate'])) {
  $varStartDate_rsMyEvents = $_GET['startdate'];
}
$varEndDate_rsMyEvents = "2099-01-01";
if (isset($_GET['enddate'])) {
  $varEndDate_rsMyEvents = $_GET['enddate'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsMyEvents = sprintf("SELECT event.ID, event.startdatetime, event.enddatetime, eventgroup.eventtitle,event.eventlocationID, eventgroup.eventdetails, location.locationname, eventcategory.title AS category FROM event LEFT JOIN eventgroup ON (event.eventgroupID = eventgroup.ID) LEFT JOIN location ON (event.eventlocationID = location.ID) LEFT JOIN users ON event.createdbyID = users.ID LEFT JOIN eventcategory ON (eventgroup.categoryID = eventcategory.ID) WHERE users.username = %s AND DATE(event.enddatetime) >= %s AND DATE(event.enddatetime) <= %s ORDER BY event.enddatetime", GetSQLValueString($varUsername_rsMyEvents, "text"),GetSQLValueString($varStartDate_rsMyEvents, "date"),GetSQLValueString($varEndDate_rsMyEvents, "date"));
$query_limit_rsMyEvents = sprintf("%s LIMIT %d, %d", $query_rsMyEvents, $startRow_rsMyEvents, $maxRows_rsMyEvents);
$rsMyEvents = mysql_query($query_limit_rsMyEvents, $aquiescedb) or die(mysql_error());
$row_rsMyEvents = mysql_fetch_assoc($rsMyEvents);

if (isset($_GET['totalRows_rsMyEvents'])) {
  $totalRows_rsMyEvents = $_GET['totalRows_rsMyEvents'];
} else {
  $all_rsMyEvents = mysql_query($query_rsMyEvents);
  $totalRows_rsMyEvents = mysql_num_rows($all_rsMyEvents);
}
$totalPages_rsMyEvents = ceil($totalRows_rsMyEvents/$maxRows_rsMyEvents)-1;
?>
<?php require_once('../includes/calendar.inc.php'); 
class DynCalendar extends Calendar 
{	
    function getCalendarLink($month, $year)
    {
        // Redisplay the current page, but with some parameters
        // to set the new month and year
        $s = "index.php";
		$s .= "?month=".$month."&year=".$year;
		$s .= isset($_GET['categoryID']) ? "&categoryID=".intval($_GET['categoryID']): "";
        return $s;
    }
	
	function getDateLink($day, $month, $year)
    {
		global $database_aquiescedb, $aquiescedb, $eventsHTML;
		mysql_select_db($database_aquiescedb, $aquiescedb);
		$day = str_pad($day,2,"0",STR_PAD_LEFT);
		$month = str_pad($month,2,"0",STR_PAD_LEFT);
		$eventsDescription = "No events on this day";
		$class = "";
		$category = isset($_GET['categoryID']) ? intval($_GET['categoryID']) : 0;
		
		$date = $year."-".$month."-".$day;
		$eventsDescription = "";
		$usertypeID = isset($_SESSION['MM_UserGroup']) ? $_SESSION['MM_UserGroup'] : 0;
		$select = "SELECT event.*, eventgroup.eventtitle AS eventgrouptitle FROM eventgroup LEFT JOIN event ON (event.eventgroupID = eventgroup.ID) WHERE
		eventgroup.usertypeID <= ".GetSQLValueString($usertypeID,"int")." AND
		((DATE(startdatetime) >= ".GetSQLValueString($date,"date")." AND DATE(startdatetime) <= ".GetSQLValueString($date,"date").") OR (DATE(enddatetime) >= ".GetSQLValueString($date,"date")." AND DATE(enddatetime) <= ".GetSQLValueString($date,"date").") OR (DATE(startdatetime) < ".GetSQLValueString($date,"date")." AND DATE(enddatetime) > ".GetSQLValueString($date,"date").")) AND event.statusID = 1 AND eventgroup.statusID = 1 AND  (eventgroup.categoryID = ".GetSQLValueString($category,"int")." OR ".GetSQLValueString($category,"int")." < 1)";
		$result = mysql_query($select, $aquiescedb) or die(mysql_error());
		$row = mysql_fetch_assoc($result);
		$totalRows = mysql_num_rows($result);
		if($totalRows>0) { // event(s) on this day
			$class = "events";
			do {
				$eventsDescription .= "<span class=\"calendarEvent\">";
				$eventsDescription .= isset($row['startdatetime']) ? date("H:i", strtotime($row['startdatetime']))." " : "";
				$eventsDescription .= htmlentities($row['eventgrouptitle'],ENT_COMPAT, "UTF-8").
				"</span><br>"; 
			} while ($row = mysql_fetch_assoc($result));		
		} // end event(s) on this day
		else {
				$eventsDescription .= "<span class=\"calendarEvent\">No events";
				$eventsDescription .= $category>0 ? " in this category." : "";
				$eventsDescription .= "</span><br>";
		}
		$daylink = "<a href=\"javascript:void();\" class=\"".$class."\" data-toggle=\"tooltip\" data-html=\"true\" title=\"".date('d M Y', strtotime($date))."<br>Click for day view<br>".strip_tags(addslashes($eventsDescription),"<br>")."\"><span class = \"calendarDate\">".$day."</span><span class=\"calendarEvents\">".$eventsDescription."</span></a>";
        return $daylink;
    } // end function
} // end class

// Construct a calendar to show the current month
$cal = new DynCalendar;
// If no month/year set, use current month/year
 
$d = getdate(time()); 

$month = (isset($_GET['month']) && $_GET['month']!="") ? $_GET['month'] : $d["mon"]; 
$year = (isset($_GET['year']) && $_GET['year'] !="") ? $_GET['year'] : $d["year"];

$canonicalURL = htmlentities($_SERVER["REQUEST_URI"], ENT_COMPAT, "UTF-8");

?> 
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php  $pageTitle = "My Events"; echo $site_name." | ".$pageTitle;?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" --><link href="../css/calendarDefault.css" rel="stylesheet"  />
<script>
$(document).ready(function() {
   $(document).tooltip();
});
</script>
<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" --><div class="calendar members container">
     <div class="crumbs"><div><span class="you_are_in">You are in: </span>
      
      <ol itemscope itemtype="http://schema.org/BreadcrumbList">
            <li itemprop="itemListElement" itemscope
      itemtype="http://schema.org/ListItem"><a itemprop="item" href="/"><span itemprop="name">Home</span></a>
      <meta itemprop="position" content="1" /></li>
      
     <li itemprop="itemListElement" itemscope
      itemtype="http://schema.org/ListItem"> 
      <a itemprop="item" href="/calendar/index.php" rel="index"><span itemprop="name">Events</span></a>
       <meta itemprop="position" content="2" />
      </li> 
      
	  
	  <li itemprop="itemListElement" itemscope
      itemtype="http://schema.org/ListItem">
	  <a itemprop="item" href="<?php echo $canonicalURL; ?>"><span itemprop="name">
	  My Events</span></a> <meta itemprop="position" content="3" /></li></ol>
      
      
      </div></div>
		  <h1 class="calendarHeader">My Events</h1>
		  <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
		    <li><a href="../index.php" class="link_back"><i class="glyphicon glyphicon-arrow-left"></i> Full Diary</a></li>
             <li><a href="add_event.php" ><i class="glyphicon glyphicon-plus-sign"></i> Add Event</a></li>
	      </ul></div></nav>
<div class="row">
<div class="col-md-6"><?php echo $cal->getMonthView($month, $year);
?>
</div><div class="col-md-6">
		  <?php if ($totalRows_rsMyEvents == 0) { // Show if recordset empty ?>
  <p>You have no upcoming events.</p>
  <?php } // Show if recordset empty ?>
          <?php if ($totalRows_rsMyEvents > 0) { // Show if recordset not empty ?>
          <p>Events <?php echo ($startRow_rsMyEvents + 1) ?> to <?php echo min($startRow_rsMyEvents + $maxRows_rsMyEvents, $totalRows_rsMyEvents) ?> of <?php echo $totalRows_rsMyEvents ?></p>
            <table  class="listTable">
              <tr>
               
                <th>Starts</th>
                <th>Ends</th>
                <th>Event</th>
                
                <th>Location</th>
                <th>Category</th> <th>Edit</th>
                <th>View</th>
              </tr>
              <?php do { ?>
                <tr>
                
                  <td><?php echo date('d M Y H:i', strtotime($row_rsMyEvents['startdatetime'])); ?></td>
                  <td><?php echo date('d M Y H:i', strtotime($row_rsMyEvents['enddatetime'])); ?></td>
                  <td><?php echo $row_rsMyEvents['eventtitle']; ?></td>
                  <td><a href="../../location/members/update_location.php?locationID=<?php echo $row_rsMyEvents['eventlocationID']; ?>&amp;returnURL=<?php echo $_SERVER['PHP_SELF']; ?>"><?php echo $row_rsMyEvents['locationname']; ?></a></td>
                  <td><?php echo $row_rsMyEvents['category']; ?></td>  <td><a href="update_event.php?eventID=<?php echo $row_rsMyEvents['ID']; ?>" class="link_edit icon_only">Edit</a></td>
                  <td><a href="../event.php?eventID=<?php echo $row_rsMyEvents['ID']; ?>" class="link_view">View</a></td>
                </tr>
                <?php } while ($row_rsMyEvents = mysql_fetch_assoc($rsMyEvents)); ?>
            </table>
            <?php } // Show if recordset not empty ?></div></div></div><div style="display:none;" id="add_event_form"><?php require_once('includes/add_event_form.inc.php'); ?>
            </div>
          <!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsMyEvents);
?>
