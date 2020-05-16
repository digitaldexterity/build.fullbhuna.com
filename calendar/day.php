<?php require_once('../Connections/aquiescedb.php'); ?><?php require_once('includes/calendar.inc.php'); ?>
<?php
$tracker = false; // do not track this page as it can send bots into infinate loops!
$_GET['date'] = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

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

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

if(isset($_POST['eventtitle'])) {	
	$eventgroupID = addEventGroup($_POST['eventtitle'], $_POST['eventdetails'], $_POST['categoryID'], "", "",  0,  "",  "", 1, $row_rsLoggedIn['ID']);
	$enddatetime = date('Y-m-d H:i:s', strtotime($_POST['startdatetime']." + ".intval($_POST['hours'])." HOURS  + ".intval($_POST['minutes'])." MINUTES"));
	$eventID = addEvent($eventgroupID, $_POST['startdatetime'], $enddatetime, 0, $row_rsLoggedIn['ID']);
	
}



$currentPage = $_SERVER["PHP_SELF"];

$maxRows_rsEvents = 20;
$pageNum_rsEvents = 0;
if (isset($_GET['pageNum_rsEvents'])) {
  $pageNum_rsEvents = $_GET['pageNum_rsEvents'];
}
$startRow_rsEvents = $pageNum_rsEvents * $maxRows_rsEvents;

$varDate_rsEvents = "2009-10-01";
if (isset($_GET['date'])) {
  $varDate_rsEvents = $_GET['date'];
}
$varUserGroup_rsEvents = "0";
if (isset($_GET['MM_UserGroup'])) {
  $varUserGroup_rsEvents = $_GET['MM_UserGroup'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsEvents = sprintf("SELECT eventcategory.title AS category, eventgroup.eventtitle, event.ID, event.startdatetime, event.enddatetime,  location.locationname FROM eventgroup LEFT JOIN event ON (eventgroup.ID = event.eventgroupID)  LEFT JOIN eventcategory ON (eventgroup.categoryID = eventcategory.ID) LEFT JOIN location ON (event.eventlocationID = location.ID) WHERE ((startdatetime >= CONCAT(%s,' 00:00:00') AND startdatetime <= CONCAT(%s,' 23:59:59')) OR (enddatetime >= CONCAT(%s,' 00:00:00') AND enddatetime <= CONCAT(%s,' 23:59:59')) OR (startdatetime < CONCAT(%s,' 00:00:00') AND enddatetime > CONCAT(%s,' 23:59:59'))) AND event.statusID = 1 AND eventgroup.statusID = 1 AND eventgroup.usertypeID <= %s ORDER BY event.startdatetime", GetSQLValueString($varDate_rsEvents, "date"),GetSQLValueString($varDate_rsEvents, "date"),GetSQLValueString($varDate_rsEvents, "date"),GetSQLValueString($varDate_rsEvents, "date"),GetSQLValueString($varDate_rsEvents, "date"),GetSQLValueString($varDate_rsEvents, "date"),GetSQLValueString($varUserGroup_rsEvents, "int"));
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

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsCategories = "SELECT * FROM eventcategory WHERE active = 1 ORDER BY title ASC";
$rsCategories = mysql_query($query_rsCategories, $aquiescedb) or die(mysql_error());
$row_rsCategories = mysql_fetch_assoc($rsCategories);
$totalRows_rsCategories = mysql_num_rows($rsCategories);



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
?>
<?php 
 $accesslevel = $row_rsEventPrefs['accesslevel'];
			   if(is_readable("../members/includes/restrictaccess.inc.php")) require_once('../members/includes/restrictaccess.inc.php');
			   ?><?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Day Events"; $pageTitle .= (isset($row_rsEvents['category'])) ? " - ".$row_rsEvents['category'] : ""; echo $pageTitle." | ".$site_name; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<!-- would follow links forever unless line below -->
<meta name="robots" content="index,nofollow" />
<script src="/3rdparty/jquery/jquery.fancybox-2/jquery.fancybox.js"></script><link href="/3rdparty/jquery/jquery.fancybox-2/jquery.fancybox.css" rel="stylesheet"  />
<link href="/core/scripts/date-picker/css/datepicker.css" rel="stylesheet"  />
<script src="/core/scripts/date-picker/js/datepicker.js"></script>
<link href="css/calendarDefault.css" rel="stylesheet"  />
<script>
$(document).ready(function(e) {
	
	$("a#previous").click(function() {
		$("#date").val($(this).attr("data-date"));
		$("#datenav").submit();
		
	});
	$("a#next").click(function() {
		$("#date").val($(this).attr("data-date"));
		$("#datenav").submit();
		
	});
	
	$("a.event.popup").fancybox({
   		type: "iframe",
		autoSize: false,
		height: 400,
		width: 500
	});
	
	$("#addevent").hide();
<?php if(isset($_SESSION['MM_UserGroup']) && $_SESSION['MM_UserGroup']>=$eventPrefs['writeaccess']) { ?>
	$("div.period").dblclick(function(e) {
		var resourceID = $(this).attr('data-resourceID');
		var hours = $(this).attr('data-hours');
		var mins = $(this).attr('data-minutes');
		
		$.fancybox({
			href: "#addevent",
        	beforeLoad: function() {			
            	$("#resourceID").val(resourceID);
				$('#hh-startdatetime').val(hours);		
				$('#mi-startdatetime').val(mins);		
				updateDate_startdatetime();
        	}
		});        
    });
	<?php } ?>
});
</script>
<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" -->
         <div class="container pageBody day events">
    <div class="crumbs"><div><span class="you_are_in">You are in: </span><a href="/">Home</a><span class="separator">&nbsp;&rsaquo;&nbsp;</span><a href="/calendar/">Calendar</a><span class="separator">&nbsp;&rsaquo;&nbsp;</span>Day's Events<?php echo isset($row_rsThisCategory['title']) ? ": ".$row_rsThisCategory['title'] : ""; ?></div></div>
    <h1>Day's Events 
      <?php if (isset($row_rsThisCategory['title'])) {  echo ": ".$row_rsThisCategory['title'];  } ?></h1><?php if (isset($_GET['date'])) { $date = $_GET['date']; } else { $date = date('Y-m-d H:i:s');}
		
		 $categoryID = isset($_GET['categoryID']) ? "&categoryID=".intval($_GET['categoryID']) : 0;
		 $resourceID = isset($_GET['resourceID']) ? "&resourceID=".intval($_GET['resourceID']) : 0;
		 ?>
     <!-- ISEARCH_END_FOLLOW --><nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav"><li><a href="/calendar/" class="link_undo"><i class="glyphicon glyphicon-arrow-left"></i> Coming up...</a></li>
     <li><a href="month.php?month=<?php echo date('m',strtotime($_GET['date'])); ?>&amp;year=<?php echo date('Y',strtotime($_GET['date'])); ?>" class="link_undo" rel="nofollow"><i class="glyphicon glyphicon-arrow-left"></i> Month View</a></li>
     </ul></div></nav><!-- ISEARCH_BEGIN_FOLLOW -->
	     <?php if (isset($row_rsThisCategory['description'])) { echo "<p>".nl2br($row_rsThisCategory['description'])."</p>"; } ?>
	     <h2>
	       <?php  echo date('l jS F Y', strtotime($date)); ?>	       </h2>
		 <?php require_once('../core/includes/alert.inc.php'); ?>
          <form id="datenav" method="get">
            <fieldset><a href="day.php"><span class="glyphicon glyphicon-step-backward"></span> Today</a>&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;
   <a href="javascript:void(0);" id="previous" data-date="<?php echo date("Y-m-d", strtotime($date." - 1 DAY")); ?>"><span class="glyphicon  glyphicon-backward"></span></a>&nbsp;&nbsp;<input name="date"  id="date" type="hidden" value="<?php $inputname = "date"; $setvalue = $date; echo $setvalue; $submitonchange = true; ?>"><?php require('../core/includes/datetimeinput.inc.php'); ?>
&nbsp;&nbsp;<a href="javascript:void(0);" id="next"  data-date="<?php echo date("Y-m-d", strtotime($date." + 1 DAY")); ?>"><span class="glyphicon  glyphicon-forward"></span></a><?php if(isset($_SESSION['MM_UserGroup']) && $_SESSION['MM_UserGroup']>=$eventPrefs['writeaccess']) { ?>&nbsp;&nbsp;&nbsp;&nbsp;Double-click a time to add an event<?php } ?>
            </fieldset></form>   
	    <?php 
		 echo getDiaryDay($date, 900, $eventPrefs['daystarttime'],  $eventPrefs['dayendtime'], $categoryID , $resourceID); ?>
     </div>
     
     
     <div id="addevent">
        <h2><span class="glyphicon glyphicon-plus-sign"></span> Add Event</h2>
        <form method="post" >
        
        <table class="form-table">
  <tr>
    <td class="text-right"><label for="eventcategoryID">Category:</label></td>
    <td>
      <select name="eventcategoryID" id="eventcategoryID">
        <option value="">Choose...</option>
        <?php
do {  
?>
        <option value="<?php echo $row_rsCategories['ID']?>" <?php if((isset($_POST['eventcategoryID']) && $row_rsCategories['ID'] == $_POST['eventcategoryID'])) echo "selected"; ?>  class="category<?php echo $row_rsCategories['ID']?>"><?php echo $row_rsCategories['title']?></option>
        <?php
} while ($row_rsCategories = mysql_fetch_assoc($rsCategories));
  $rows = mysql_num_rows($rsCategories);
  if($rows > 0) {
      mysql_data_seek($rsCategories, 0);
	  $row_rsCategories = mysql_fetch_assoc($rsCategories);
  }
?>
      </select>  </td>
  </tr>
  <tr>
    <td class="text-right"><label for="eventtitle">Event name:</label></td>
    <td>
      <input name="eventtitle" type="text" class="form-control" id="eventtitle"></td>
  </tr>
  <tr>
    <td class="text-right">Time:</td>
    <td><input type="hidden" name="startdatetime" id="startdatetime" value="<?php $setdate = ($followonfromID>0 && isset($_POST['startdatetime'])) ? date('Y-m-d H:i:s', strtotime("+6 MONTH", strtotime($_POST['startdatetime']))) : $date; $inputname = "startdatetime"; $time = true; $starthour = substr($eventPrefs['daystarttime'],0,2); $endhour = substr($eventPrefs['dayendtime'],0,2); $setvalue = (isset($_POST['addbutton']) && isset($_POST['startdatetime'])) ? htmlentities($_POST['startdatetime']) : $setdate." 08:00"; echo $setvalue; ?>" class='highlight-days-67 split-date format-y-m-d divider-dash'>
      <?php require('../core/includes/datetimeinput.inc.php'); ?>
      </td>
  </tr>
  <tr>
    <td class="text-right">Duration:</td>
    <td>  <label><select name="hours" id="hours">
      <option value="0" <?php if(isset($_POST['hours']) && $_POST['hours']==0) echo "selected"; ?>>0</option>
      <option value="1" <?php if(!isset($_POST['hours']) || $_POST['hours']==1) echo "selected"; ?>>1</option>
      <option value="2" <?php if(isset($_POST['hours']) && $_POST['hours']==2) echo "selected"; ?>>2</option>
      <option value="3" <?php if(isset($_POST['hours']) && $_POST['hours']==3) echo "selected"; ?>>3</option>
       <option value="4" <?php if(isset($_POST['hours']) && $_POST['hours']==4) echo "selected"; ?>>4</option>
        <option value="5" <?php if(isset($_POST['hours']) && $_POST['hours']==5) echo "selected"; ?>>5</option>
         <option value="6" <?php if(isset($_POST['hours']) && $_POST['hours']==6) echo "selected"; ?>>6</option>
          <option value="7" <?php if(isset($_POST['hours']) && $_POST['hours']==7) echo "selected"; ?>>7</option>
           <option value="8" <?php if(isset($_POST['hours']) && $_POST['hours']==8) echo "selected"; ?>>8</option>
            <option value="24" <?php if(isset($_POST['hours']) && $_POST['hours']==24) echo "selected"; ?>>24</option>
             <option value="48" <?php if(isset($_POST['hours']) && $_POST['hours']==48) echo "selected"; ?>>48</option>
    </select> hours </label>
      <label>
        <select name="minutes" id="minutes">
          <option value="0" <?php if(isset($_POST['minutes']) && $_POST['minutes']==0) echo "selected"; ?>>0</option>
          <option value="15" <?php if(isset($_POST['minutes'])  && $_POST['minutes']==15) echo "selected"; ?>>15</option>
          <option value="30" <?php if(isset($_POST['minutes']) && $_POST['minutes']==30) echo "selected"; ?>>30</option>
          <option value="45" <?php if(isset($_POST['minutes']) && $_POST['minutes']==45) echo "selected"; ?>>45</option>
        </select> 
        minutes
      </label></td>
  </tr>
  <tr>
    <td class="top text-right">Notes:</td>
    <td><label for="eventdetails"></label>
      <textarea name="eventdetails" id="eventdetails"><?php echo isset($_POST['eventdetails']) ? htmlentities($_POST['eventdetails'], ENT_COMPAT, "UTF-8") : ""; ?></textarea></td>
  </tr>
  <tr>
    <td class="text-right">&nbsp;</td>
    <td><input type="submit" name="addbutton" id="addbutton" value="Add">
      </td>
  </tr>
</table></form>
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

mysql_free_result($rsThisCategory);

mysql_free_result($rsEventPrefs);

mysql_free_result($rsCategories);

mysql_free_result($rsLoggedIn);
?>