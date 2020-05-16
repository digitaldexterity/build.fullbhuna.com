<?php require_once('../Connections/aquiescedb.php'); ?>
<?php
$tracker = false; // do not track this page as it can send bots into infinate loops!
if (isset($_GET['startDate'])) { $setvalue = $_GET['startDate']; } else { $setvalue = date('Y-m-d H:i:s');} 

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

$currentPage = $_SERVER["PHP_SELF"];

$maxRows_rsEvents = 20;
$pageNum_rsEvents = 0;
if (isset($_GET['pageNum_rsEvents'])) {
  $pageNum_rsEvents = $_GET['pageNum_rsEvents'];
}
$startRow_rsEvents = $pageNum_rsEvents * $maxRows_rsEvents;

$varCategory_rsEvents = "-1";
if (isset($_GET['categoryID'])) {
  $varCategory_rsEvents = $_GET['categoryID'];
}
$varUserGroup_rsEvents = "0";
if (isset($_SESSION['MM_UserGroup'])) {
  $varUserGroup_rsEvents = $_SESSION['MM_UserGroup'];
}
$varUsername_rsEvents = "-1";
if (isset($_SESSION['MM_Username'])) {
  $varUsername_rsEvents = $_SESSION['MM_Username'];
}
$varStartDate_rsEvents = "".date('Y-m-d H:i:s')."";
if (isset($_GET['startDate'])) {
  $varStartDate_rsEvents = $_GET['startDate'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsEvents = sprintf("SELECT event.ID, eventgroup.eventtitle, event.startdatetime, event.enddatetime,  event.recurringweekly, (DAYOFWEEK(event.startdatetime) + 7*(DAYOFWEEK(%s)>DAYOFWEEK(event.startdatetime))) AS dow, eventcategory.title AS category, eventcategory.colour FROM event  LEFT JOIN eventgroup ON (event.eventgroupID = eventgroupID) LEFT JOIN eventcategory ON (eventgroup.categoryID = eventcategory.ID), users WHERE event.statusID = 1 AND  eventgroup.usertypeID <= %s AND   (eventgroup.categoryID = %s OR %s < 1) AND  ( (event.startdatetime >= %s AND event.startdatetime <= DATE_ADD(%s, INTERVAL 7 DAY))  OR  (event.enddatetime IS NOT NULL AND event.enddatetime >= %s AND event.enddatetime <= DATE_ADD(%s, INTERVAL 7 DAY)) OR (event.enddatetime IS NOT NULL AND event.startdatetime <= %s AND event.enddatetime >= DATE_ADD(%s, INTERVAL 7 DAY))  OR (event.recurringweekly = 1 AND (event.recurringend >= DATE_ADD(%s, INTERVAL 7 DAY) OR event.recurringend IS NULL) AND event.startdatetime <= DATE_ADD(%s, INTERVAL 7 DAY))) GROUP BY event.ID ORDER BY (DAYOFWEEK(event.startdatetime) + 7*(DAYOFWEEK(%s)>DAYOFWEEK(event.startdatetime))), EXTRACT(HOUR_MINUTE FROM event.startdatetime)", GetSQLValueString($varStartDate_rsEvents, "date"),GetSQLValueString($varUserGroup_rsEvents, "int"),GetSQLValueString($varCategory_rsEvents, "int"),GetSQLValueString($varCategory_rsEvents, "int"),GetSQLValueString($varStartDate_rsEvents, "date"),GetSQLValueString($varStartDate_rsEvents, "date"),GetSQLValueString($varStartDate_rsEvents, "date"),GetSQLValueString($varStartDate_rsEvents, "date"),GetSQLValueString($varStartDate_rsEvents, "date"),GetSQLValueString($varStartDate_rsEvents, "date"),GetSQLValueString($varStartDate_rsEvents, "date"),GetSQLValueString($varStartDate_rsEvents, "date"),GetSQLValueString($varUsername_rsEvents, "int"));
$query_limit_rsEvents = sprintf("%s LIMIT %d, %d", $query_rsEvents, $startRow_rsEvents, $maxRows_rsEvents);
$rsEvents = mysql_query($query_limit_rsEvents, $aquiescedb) or die(mysql_error());
$row_rsEvents = mysql_fetch_assoc($rsEvents);

if (isset($_GET['totalRows_rsEvents'])) {
  $totalRows_rsEvents = $_GET['totalRows_rsEvents'];
} else {
  $all_rsEvents = mysql_query($query_rsEvents);
  $totalRows_rsEvents = mysql_num_rows($all_rsEvents);
}
$totalPages_rsEvents = ceil($totalRows_rsEvents/$maxRows_rsEvents)-1;

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsCategories = "SELECT ID, title FROM eventcategory WHERE active = 1 ORDER BY title ASC";
$rsCategories = mysql_query($query_rsCategories, $aquiescedb) or die(mysql_error());
$row_rsCategories = mysql_fetch_assoc($rsCategories);
$totalRows_rsCategories = mysql_num_rows($rsCategories);

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
$query_rsEventPrefs = "SELECT * FROM eventprefs";
$rsEventPrefs = mysql_query($query_rsEventPrefs, $aquiescedb) or die(mysql_error());
$row_rsEventPrefs = mysql_fetch_assoc($rsEventPrefs);
$totalRows_rsEventPrefs = mysql_num_rows($rsEventPrefs);

$queryString_rsEvents = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsEvents") == false && 
        stristr($param, "totalRows_rsEvents") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsEvents = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsEvents = sprintf("&totalRows_rsEvents=%d%s", $totalRows_rsEvents, $queryString_rsEvents);

 $accesslevel = $row_rsEventPrefs['accesslevel'];
			   if(is_readable("../members/includes/restrictaccess.inc.php")) require_once('../members/includes/restrictaccess.inc.php');
?><?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Week's Events ".date('d M Y',strtotime($setvalue)); $pageTitle .= (isset($row_rsThisCategory['title'])) ? " - ".$row_rsThisCategory['title'] : ""; echo $pageTitle." | ".$site_name; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<!-- would follow links forever unless line below -->
<meta name="robots" content="index,nofollow" />
<link href="/core/scripts/date-picker/css/datepicker.css" rel="stylesheet"  />
<script src="/core/scripts/date-picker/js/datepicker.js"></script>
<link href="css/calendarDefault.css" rel="stylesheet"  />
<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" --> <div class="container pageBody events">
    <div class="crumbs"><div><span class="you_are_in">You are in: </span><a href="/">Home</a><span class="separator">&nbsp;&rsaquo;&nbsp;</span><a href="/calendar/">Calendar</a><span class="separator">&nbsp;&rsaquo;&nbsp;</span>Week
    <?php if(isset($row_rsThisCategory['title'])) { echo ": ".$row_rsThisCategory['title'];  } ?></div></div>
    <h1>Week's Events<?php
		   if (isset($row_rsThisCategory['title'])) {  echo ": ".$row_rsThisCategory['title'];  } ?></h1><?php
		    
		 $next7 = date('Y-m-d',(strtotime($setvalue)+7*24*60*60));
		 $prev7 = date('Y-m-d',(strtotime($setvalue)-7*24*60*60));
		 $cat = isset($_GET['categoryID']) ? "&categoryID=".intval($_GET['categoryID']) : "";
		 ?><!-- ISEARCH_END_FOLLOW -->
	       <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav"><li><a href="/calendar/" class="link_undo"><i class="glyphicon glyphicon-arrow-left"></i> Coming up...</a></li>
	         <li><a href="month.php?month=<?php echo date('m',strtotime($setvalue)); ?>&amp;year=<?php echo date('Y',strtotime($setvalue)); echo isset($_GET['categoryID']) ? "&amp;categoryID=".intval($_GET['categoryID']) : ""; ?>" class="link_undo"><i class="glyphicon glyphicon-arrow-left"></i> Month view</a></li><li><a href="week.php?startDate=<?php echo $prev7.$cat; ?>" class="link_left" rel="nofollow">Previous 7 days</a></li><li><a href="week.php?startDate=<?php echo $next7.$cat; ?>" class="link_right" rel="nofollow">Next 7 days</a></li></ul></div></nav><!-- ISEARCH_BEGIN_FOLLOW -->
	     <?php if (isset($row_rsThisCategory['description'])) { echo "<p>".nl2br($row_rsThisCategory['description'])."</p>"; } ?>
	     <h2>
	       
	       Week commencing <?php echo date('l jS F Y', strtotime($setvalue)); ?>	       </h2>
		    
	     <?php if ($totalRows_rsEvents == 0) { // Show if recordset empty ?>
	       <p>There are no events for this 7 day period. </p>
	       <?php } // Show if recordset empty ?>
       <?php if ($totalRows_rsEvents > 0) { // Show if recordset not empty ?>
	       <?php $dayName = array(1=>"Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"); ?>
	        <table class="listTable">
                <thead>
                  <tr>
                    <th>&nbsp;</th>
                    <th>Day</th>
                   <th>Time</th>
                   <th>Category</th>
                   <th>Event</th>
                   <th>&nbsp;</th>
                  </tr></thead>
                  <?php do { ?>
                    <tr>
                      <td><?php if($row_rsEvents['recurringweekly'] != 1) { ?><img src="../core/images/icons/date.png" alt="Single event" width="16" height="16" style="vertical-align:
middle;" /><?php } else { ?><img src="../core/images/icons/date_link.png" alt="Recurring event" width="16" height="16" style="vertical-align:
middle;" /><?php } ?>&nbsp;&nbsp;</td>
                      <td><time itemprop="startDate" datetime="<?php echo date('Y-m-dTH:i:s',strtotime($row_rsEvents['startdatetime'])); ?>"><?php if (date('Y-m-d',strtotime($row_rsEvents['startdatetime'])) == date('Y-m-d',strtotime($row_rsEvents['enddatetime'])) || !isset($row_rsEvents['enddatetime'])) { $dow = ($row_rsEvents['dow'] >7) ? $row_rsEvents['dow']-7 : $row_rsEvents['dow']; echo $dayName[$dow]; } ?></time>&nbsp;&nbsp;</td>
                      <td><?php echo date('H:i',strtotime($row_rsEvents['startdatetime'])); ?>&nbsp;&nbsp;</td>
                      <td><?php echo $row_rsEvents['category']; ?></td>
                      <td><?php echo $row_rsEvents['eventtitle']; ?>&nbsp;&nbsp;</td>
                      <td><a href="event.php?eventID=<?php echo $row_rsEvents['ID']; ?>" class="link_view">View</a></td>
                  </tr>
                    <?php } while ($row_rsEvents = mysql_fetch_assoc($rsEvents)); ?>
	        </table>
	       <?php } // Show if recordset not empty ?><p>
            </p><table width="50%" border="0" align="center" class="form-table">
              <tr>
                <td><?php if ($pageNum_rsEvents > 0) { // Show if not first page ?>
                    <a href="<?php printf("%s?pageNum_rsEvents=%d%s", $currentPage, 0, $queryString_rsEvents); ?>">First</a>
                    <?php } // Show if not first page ?>                </td>
               <td><?php if ($pageNum_rsEvents > 0) { // Show if not first page ?>
                    <a href="<?php printf("%s?pageNum_rsEvents=%d%s", $currentPage, max(0, $pageNum_rsEvents - 1), $queryString_rsEvents); ?>" rel="prev">Previous</a>
                    <?php } // Show if not first page ?>                </td>
                <td><?php if ($pageNum_rsEvents < $totalPages_rsEvents) { // Show if not last page ?>
                    <a href="<?php printf("%s?pageNum_rsEvents=%d%s", $currentPage, min($totalPages_rsEvents, $pageNum_rsEvents + 1), $queryString_rsEvents); ?>" rel="next">Next</a>
                    <?php } // Show if not last page ?>                </td>
                <td><?php if ($pageNum_rsEvents < $totalPages_rsEvents) { // Show if not last page ?>
                    <a href="<?php printf("%s?pageNum_rsEvents=%d%s", $currentPage, $totalPages_rsEvents, $queryString_rsEvents); ?>">Last</a>
                    <?php } // Show if not last page ?>                </td>
              </tr>
            </table>
            </p>
            <form action="index.php" method="get" name="form1" id="form1" role="form">
              
              Search
                <?php if ($totalRows_rsCategories > 0) { // Show if recordset not empty ?>
                  <select name="categoryID" id="categoryID">
                    <option value="-1" <?php if (!(strcmp(-1, @$_GET['categoryID']))) {echo "selected=\"selected\"";} ?>>Any category</option>
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
                  <?php } // Show if recordset not empty ?> 
                starting 
               <input name="startDate" id="startDate" type="hidden" value="<?php echo $setvalue; ?>" class='highlight-days-67 split-date format-y-m-d divider-dash' />
            <?php // set value declared earlier - before this input as it clears after include
  $inputname = "startDate"; include('../core/includes/datetimeinput.inc.php'); ?>
 
              for
  <label>7</label>
              days
  <label>
  <input name="Submit2" type="submit" class="button" value="Go" />
  </label>
            </form>
       </div>
    <!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsEvents);

mysql_free_result($rsCategories);

mysql_free_result($rsThisCategory);

mysql_free_result($rsEventPrefs);
?>