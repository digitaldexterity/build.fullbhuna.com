<?php require_once('../Connections/aquiescedb.php'); ?>
<?php 
$tracker = false; // do not track this page as it can send bots into infinate loops!
$_GET['month'] = isset($_GET['month']) ? $_GET['month'] : date('m');  $_GET['year'] = isset($_GET['year']) ? $_GET['year']: date('Y');

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

$colname_rsThisCategory = "-1";
if (isset($_GET['categoryID'])) {
  $colname_rsThisCategory = $_GET['categoryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsThisCategory = sprintf("SELECT title, description, eventcategory.ID FROM eventcategory WHERE ID = %s", GetSQLValueString($colname_rsThisCategory, "int"));
$rsThisCategory = mysql_query($query_rsThisCategory, $aquiescedb) or die(mysql_error());
$row_rsThisCategory = mysql_fetch_assoc($rsThisCategory);
$totalRows_rsThisCategory = mysql_num_rows($rsThisCategory);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsCategories = "SELECT * FROM eventcategory WHERE active = 1 ORDER BY title ASC";
$rsCategories = mysql_query($query_rsCategories, $aquiescedb) or die(mysql_error());
$row_rsCategories = mysql_fetch_assoc($rsCategories);
$totalRows_rsCategories = mysql_num_rows($rsCategories);

if(isset($_GET['pageNum_rsComingUp'])) $_GET['pageNum_rsComingUp'] = intval($_GET['pageNum_rsComingUp']);
if(isset($_GET['totalRows_rsComingUp'])) $_GET['totalRows_rsComingUp'] = intval($_GET['totalRows_rsComingUp']);


$maxRows_rsComingUp = 20;
$pageNum_rsComingUp = 0;
if (isset($_GET['pageNum_rsComingUp'])) {
  $pageNum_rsComingUp = $_GET['pageNum_rsComingUp'];
}
$startRow_rsComingUp = $pageNum_rsComingUp * $maxRows_rsComingUp;

$varCategoryID_rsComingUp = "0";
if (isset($_GET['categoryID'])) {
  $varCategoryID_rsComingUp = $_GET['categoryID'];
}
$varUserGroup_rsComingUp = "0";
if (isset($_SESSION['MM_UserGroup'])) {
  $varUserGroup_rsComingUp = $_SESSION['MM_UserGroup'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsComingUp = sprintf("SELECT event.ID, eventgroup.eventtitle, eventcategory.title AS eventcategory, location.locationname, event.startdatetime FROM event LEFT JOIN eventgroup ON (event.eventgroupID = eventgroup.ID) LEFT JOIN eventcategory ON (eventgroup.categoryID = eventcategory.ID) LEFT JOIN location ON (event.eventlocationID = location.ID) WHERE (eventgroup.categoryID = %s OR %s = 0) AND DATE(event.startdatetime )>= CURDATE() AND event.statusID = 1 AND eventgroup.usertypeID <= %s GROUP BY eventgroupID ORDER BY eventgroup.featured DESC, event.startdatetime ", GetSQLValueString($varCategoryID_rsComingUp, "int"),GetSQLValueString($varCategoryID_rsComingUp, "int"),GetSQLValueString($varUserGroup_rsComingUp, "int"));
$query_limit_rsComingUp = sprintf("%s LIMIT %d, %d", $query_rsComingUp, $startRow_rsComingUp, $maxRows_rsComingUp);
$rsComingUp = mysql_query($query_limit_rsComingUp, $aquiescedb) or die(mysql_error());
$row_rsComingUp = mysql_fetch_assoc($rsComingUp);

if (isset($_GET['totalRows_rsComingUp'])) {
  $totalRows_rsComingUp = $_GET['totalRows_rsComingUp'];
} else {
  $all_rsComingUp = mysql_query($query_rsComingUp);
  $totalRows_rsComingUp = mysql_num_rows($all_rsComingUp);
}
$totalPages_rsComingUp = ceil($totalRows_rsComingUp/$maxRows_rsComingUp)-1;

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsEventPrefs = "SELECT * FROM eventprefs";
$rsEventPrefs = mysql_query($query_rsEventPrefs, $aquiescedb) or die(mysql_error());
$row_rsEventPrefs = mysql_fetch_assoc($rsEventPrefs);
$totalRows_rsEventPrefs = mysql_num_rows($rsEventPrefs);

 $accesslevel = $row_rsEventPrefs['accesslevel'];
			   if(is_readable("../members/includes/restrictaccess.inc.php")) require_once('../members/includes/restrictaccess.inc.php');
?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Diary - Coming up..."; echo $pageTitle." | ".$site_name; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->

<link href="/calendar/css/calendarDefault.css" rel="stylesheet"  />


<script>

$(document).ready(function() {
   $(document).tooltip();
});
</script>
<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" --> <div class="container pageBody events">
  <?php require_once('includes/calendar.inc.php'); 
class DynCalendar extends Calendar 
{	
    function getCalendarLink($month, $year)
    {
        // Redisplay the current page, but with some parameters
        // to set the new month and year
        $s = "month.php";
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
		$eventsDescription = "";
		$date = $year."-".$month."-".$day;
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
		$daylink = "<a href=\"/calendar/day.php?date=".$year."-".$month."-".$day."&categoryID=".$category."\" class=\"".$class."\" data-toggle=\"tooltip\" data-html=\"true\" title=\"".date('d M Y', strtotime($date))."<br>Click for day view<br>".strip_tags(addslashes($eventsDescription),"<br>")."\"><span class = \"calendarDate\">".$day."</span><span class=\"calendarEvents\">".$eventsDescription."</span></a>";
        return $daylink;
    } // end function
} // end class

// Construct a calendar to show the current month
$cal = new DynCalendar;
// If no month/year set, use current month/year
 
$d = getdate(time()); 

$month = (isset($_GET['month']) && $_GET['month']!="") ? $_GET['month'] : $d["mon"]; 
$year = (isset($_GET['year']) && $_GET['year'] !="") ? $_GET['year'] : $d["year"];

?> 
<div class="crumbs"><div>You are in: <a href="/">Home</a><span class="separator">&nbsp;&rsaquo;&nbsp;</span>Calendar Coming Up<?php echo (isset($row_rsThisCategory['title'])) ? ": ".$row_rsThisCategory['title'] : "..."; ?></div></div>
  <div id="monthCalendarContainer"><?php echo $cal->getMonthView($month, $year);
?>
    
  </div>
  <h1><?php echo (isset($row_rsThisCategory['title'])) ? $row_rsThisCategory['title']."&nbsp;" : ""; ?>Coming up...</h1>
 
  <?php if ($totalRows_rsCategories > 0) { // Show if recordset not empty ?>
  <form action="search.php" method="get" id="searchform" role="form">
  <fieldset class="form-inline">
  <legend>Search</legend>
  
  <input name="event" type="text"  id="event" size="30" maxlength="50" value="<?php echo isset($_GET['event']) ? htmlentities($_GET['event'],ENT_COMPAT,"UTF-8") : ""; ?>" placeholder="All events" />
<br /><select name="categoryID" id="categoryID" onChange="this.form.submit()">
        <option value="0" <?php if (!(strcmp(0, @$_GET['categoryID']))) {echo "selected=\"selected\"";} ?>>Filter by category...</option>
        <option value="0" <?php if (!(strcmp(0, @$_GET['categoryID']))) {echo "selected=\"selected\"";} ?>>All categories</option>
        <?php
do {  
?>
        <option value="<?php echo $row_rsCategories['ID']?>"<?php if (!(strcmp($row_rsCategories['ID'], @$_GET['categoryID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsCategories['title']?></option>
        <?php
} while ($row_rsCategories = mysql_fetch_assoc($rsCategories));
  $rows = mysql_num_rows($rsCategories);
  if($rows > 0) {
      mysql_data_seek($rsCategories, 0);
	  $row_rsCategories = mysql_fetch_assoc($rsCategories);
  }
?>
        </select>
    <input name="searchgo" type="submit" class="button" id="searchgo" value="Go" /> <div><a href="month.php<?php echo isset($_GET['categoryID']) ? "?categoryID=".intval($_GET['categoryID']) : ""; ?>" >This month</a> <a href="week.php<?php echo isset($_GET['categoryID']) ? "?categoryID=".intval($_GET['categoryID']) : ""; ?>" >This week</a> <a href="day.php<?php echo isset($_GET['categoryID']) ? "?categoryID=".intval($_GET['categoryID']) : ""; ?>">Today</a></div></fieldset>
  </form>
  
  <?php } // Show if recordset not empty ?>
<?php if (isset($row_rsThisCategory['description']) && $row_rsThisCategory['description'] !="") { ?><p><?php echo nl2br($row_rsThisCategory['description']); ?></p>
  <?php } ?>
  <?php require_once('includes/latestEvents.inc.php'); ?>
  </div>
  
  <!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsThisCategory);

mysql_free_result($rsCategories);

if(is_resource($rsComingUp)) {
mysql_free_result($rsComingUp);
}

mysql_free_result($rsEventPrefs);
?>
