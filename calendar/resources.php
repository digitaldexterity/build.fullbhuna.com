<?php require_once('../Connections/aquiescedb.php'); ?><?php require_once('includes/calendar.inc.php'); ?>
<?php 

$date = isset($_GET['date']) ? date('Y-m-d', strtotime($_GET['date'])) : date('Y-m-d');


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
$query_rsLoggedIn = sprintf("SELECT ID, firstname, surname FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);


if(isset($_GET['deleteeventID'])) {
	if(deleteEvent($_GET['deleteeventID'])) {
		
		$msg = "Booking deleted.";
		
	} else {
		$msg = "Could not find booking to delete.";
	}
}



/*********

UPDATE APPOINTMENT

***********/

if(isset($_POST['updatebutton']) || isset($_POST['followonbutton'])) {
	$eventID = intval($_POST['ID']);
	$enddatetime = date('Y-m-d H:i:s', strtotime($_POST['startdatetime']." + ".intval($_POST['hours'])." HOURS  + ".intval($_POST['minutes'])." MINUTES"));
	$update = "UPDATE event SET startdatetime = ".GetSQLValueString($_POST['startdatetime'], "date").", enddatetime = ".GetSQLValueString($enddatetime, "date").", modifiedbyID = ".$row_rsLoggedIn['ID'].", modifieddatetime = NOW() WHERE ID = ".$eventID ;
	mysql_query($update, $aquiescedb) or die(mysql_error().": ".$update);
	$update = "UPDATE eventgroup SET eventgroup.eventtitle = ".GetSQLValueString($_POST['eventtitle'], "text").", eventgroup.categoryID = ".GetSQLValueString($_POST['eventcategoryID'], "int").", eventgroup.resourceID = ".GetSQLValueString($_POST['resourceID'], "int").", eventgroup.eventdetails = ".GetSQLValueString($_POST['eventdetails'], "text")." , modifiedbyID = ".$row_rsLoggedIn['ID'].", modifieddatetime = NOW() WHERE ID = ".intval($_POST['eventgroupID']);
	mysql_query($update, $aquiescedb) or die(mysql_error().": ".$update);
	
	
	
	foreach($_POST['eventattend'] as $ID=> $userID) {	
		if(isset($_POST['checkedout'])) {
			$update = "UPDATE eventattend SET checkedoutdatetime = ".GetSQLValueString($_POST['checkedoutdatetime'], "date")." WHERE ID = ".$ID;
			mysql_query($update, $aquiescedb) or die(mysql_error().": ".$update);
		}
		if(isset($_POST['checkedin'])) {
			$update = "UPDATE eventattend SET checkedindatetime = ".GetSQLValueString($_POST['checkedindatetime'], "date")." WHERE ID = ".$ID;
			mysql_query($update, $aquiescedb) or die(mysql_error().": ".$update);
		}
		
	}
}

/*********

ADD APPOINTMENT

***********/

if(isset($_POST['resourceID'])) {	
	$bookingname= (isset($_POST['eventtitle']) && trim($_POST['eventtitle'])!="") ? $_POST['eventtitle'] : "Booking";
	$eventgroupID = addEventGroup($bookingname, $_POST['eventdetails'], $_POST['categoryID'], "", $_POST['resourceID'],  0,  "",  "", 1, $row_rsLoggedIn['ID']);
	if($eventgroupID>0) {
	$enddatetime = date('Y-m-d H:i:s', strtotime($_POST['startdatetime']." + ".intval($_POST['hours'])." HOURS  + ".intval($_POST['minutes'])." MINUTES"));
	
	$recurringend = (isset($_POST['recurs']) && $_POST['recurringend']!="") ? date('Y-m-d 23:59:59',strtotime($_POST['recurringend'])) : $_POST['startdatetime'];
	$recurringinterval = strtoupper($_POST['recurringinterval']);
	$recurringmultiple = intval($_POST['recurringmultiple']);
	
	$eventID = addEvent($eventgroupID, $_POST['startdatetime'], $enddatetime, 0, $row_rsLoggedIn['ID'], 0, $recurringend, $recurringinterval, $recurringmultiple);
	}
}


$varCategoryID_rsResources = "0";
if (isset($_GET['categoryID'])) {
  $varCategoryID_rsResources = $_GET['categoryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsResources = sprintf("SELECT ID, resourcename FROM eventresource WHERE statusID = 1 AND (%s = 0 OR %s = eventresource.categoryID) ORDER BY resourcename ASC", GetSQLValueString($varCategoryID_rsResources, "int"),GetSQLValueString($varCategoryID_rsResources, "int"));
$rsResources = mysql_query($query_rsResources, $aquiescedb) or die(mysql_error());
$row_rsResources = mysql_fetch_assoc($rsResources);
$totalRows_rsResources = mysql_num_rows($rsResources);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsEventPrefs = "SELECT * FROM eventprefs";
$rsEventPrefs = mysql_query($query_rsEventPrefs, $aquiescedb) or die(mysql_error());
$row_rsEventPrefs = mysql_fetch_assoc($rsEventPrefs);
$totalRows_rsEventPrefs = mysql_num_rows($rsEventPrefs);

$colname_rsThisCategory = "-1";
if (isset($_GET['categoryID'])) {
  $colname_rsThisCategory = $_GET['categoryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsThisCategory = sprintf("SELECT title, description, eventcategory.ID FROM eventcategory WHERE ID = %s", GetSQLValueString($colname_rsThisCategory, "int"));
$rsThisCategory = mysql_query($query_rsThisCategory, $aquiescedb) or die(mysql_error());
$row_rsThisCategory = mysql_fetch_assoc($rsThisCategory);
$totalRows_rsThisCategory = mysql_num_rows($rsThisCategory);
?><?php 
 $accesslevel = $row_rsEventPrefs['accesslevel'];
			   if(is_readable("../members/includes/restrictaccess.inc.php")) require_once('../members/includes/restrictaccess.inc.php');
			   ?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>

<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Daily Resource Diary"; echo $pageTitle." | ".$site_name; ?>
</title>
<!--[if IE]><![endif]-->
<!-- InstanceEndEditable -->
<?php require_once('../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" --><meta name="robots" content="index,nofollow" />
<script src="/3rdparty/jquery/jquery.fancybox-2/jquery.fancybox.js"></script><link href="/3rdparty/jquery/jquery.fancybox-2/jquery.fancybox.css" rel="stylesheet"  />
<link href="/core/scripts/date-picker/css/datepicker.css" rel="stylesheet"  />
<script src="/core/scripts/date-picker/js/datepicker.js"></script>
<link href="css/calendarDefault.css?v=2" rel="stylesheet"  />
<script>
$(document).ready(function(e) {
	
	toggleNthDow();
	
	
	$('.recurs').hide();


	
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
		var categoryID = $(this).attr('data-categoryID');
		var hours = $(this).attr('data-hours');
		var mins = $(this).attr('data-minutes');
		
		$.fancybox({
			href: "#addevent",
        	beforeLoad: function() {	
            	$("#resourceID").val(resourceID);
				$("#categoryID").val(categoryID);
				$('#hh-startdatetime').val(hours);		
				$('#mi-startdatetime').val(mins);		
				updateDate_startdatetime();
        	}
		});        
    });
	<?php } ?>
	$(document).tooltip();
});

function toggleNthDow() {
	
	if($('#recurringinterval').val()=="nthdow") {
		$(".nthdow").slideDown();
	} else {
		$(".nthdow").slideUp();
	}
}
</script>

<style>
<!--
-->
</style>
<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" --><div class="container pageBody events">
    <div class="crumbs"><div><span class="you_are_in">You are in: </span><a href="/">Home</a><span class="separator">&nbsp;&rsaquo;&nbsp;</span><a href="/calendar/">Calendar</a><span class="separator">&nbsp;&rsaquo;&nbsp;</span>Dairy
    <?php if(isset($row_rsThisCategory['title'])) { echo ": ".$row_rsThisCategory['title'];  } ?></div></div>
	
	<h2><?php echo $row_rsThisCategory['title']; ?>
	       <?php  echo date('l jS F Y', strtotime($date)); ?>	       </h2>
		 <?php require_once('../core/includes/alert.inc.php'); ?>
          <form id="datenav" method="get">
            <fieldset><a href="resources.php?categoryID=<?php echo isset($_GET['categoryID']) ? intval($_GET['categoryID']) : 0; ?>"><span class="glyphicon glyphicon-step-backward"></span> Today</a>&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;
   <a href="javascript:void(0);" id="previous" data-date="<?php echo date("Y-m-d", strtotime($date." - 1 DAY")); ?>"><span class="glyphicon  glyphicon-backward"></span></a>&nbsp;&nbsp;<input name="date"  id="date" type="hidden" value="<?php $inputname = "date"; $setvalue = $date; echo $setvalue; $submitonchange = true; ?>" class='highlight-days-67 split-date format-y-m-d divider-dash' onChange="this.form.submit();"><?php require('../core/includes/datetimeinput.inc.php'); ?>
&nbsp;&nbsp;<a href="javascript:void(0);" id="next"  data-date="<?php echo date("Y-m-d", strtotime($date." + 1 DAY")); ?>"><span class="glyphicon  glyphicon-forward"></span></a><?php if(isset($_SESSION['MM_UserGroup']) && $_SESSION['MM_UserGroup']>=$eventPrefs['writeaccess']) { ?>&nbsp;&nbsp;&nbsp;&nbsp;Double-click a time to add a booking<?php } ?><input type="hidden" name="categoryID" value="<?php echo isset($_GET['categoryID']) ? intval($_GET['categoryID']) : 0; ?>">
            </fieldset></form> 
		<?php require_once('includes/eventResourceGrid.inc.php'); ?></div><div id="addevent">
        <h2><span class="glyphicon glyphicon-plus-sign"></span> Add Booking</h2>
        <form method="post" >
        
        <table class="form-table">
  
  <tr class="resource">
    <td class="text-right"><label for="resourceID">Resource:</label></td>
    <td>
      <select name="resourceID" id="resourceID">
        <option value="">Choose...</option>
        <?php
do {  
?>
        <option value="<?php echo $row_rsResources['ID']?>" <?php if((isset($_POST['resourceID']) && $row_rsResources['ID'] == $_POST['resourceID'])) echo "selected"; ?>  class="category<?php echo $row_rsResources['ID']?>"><?php echo $row_rsResources['resourcename']?></option>
        <?php
} while ($row_rsResources = mysql_fetch_assoc($rsResources));
  $rows = mysql_num_rows($rsResources);
  if($rows > 0) {
      mysql_data_seek($rsResources, 0);
	  $row_rsResources = mysql_fetch_assoc($rsResources);
  }
?>
      </select> <input type="hidden" name="categoryID" id="categoryID" value="<?php echo isset($_GET['categoryID']) ? intval($_GET['categoryID']) : 0 ; ?>"> </td>
  </tr>
  <tr>
    <td class="text-right"><label for="eventtitle">Name:</label></td>
    <td>
      <input name="eventtitle" type="text" class="form-control" id="eventtitle" value="<?php echo $row_rsLoggedIn['firstname']." ".$row_rsLoggedIn['surname']; ?>"></td>
  </tr>
  <tr>
    <td class="text-right">Time:</td>
    <td class="text-nowrap"><input type="hidden" name="startdatetime" id="startdatetime" value="<?php $setdate = ($followonfromID>0 && isset($_POST['startdatetime'])) ? date('Y-m-d H:i:s', strtotime("+6 MONTH", strtotime($_POST['startdatetime']))) : $date; $inputname = "startdatetime"; $time = true; $starthour = substr($eventPrefs['daystarttime'],0,2); $endhour = substr($eventPrefs['dayendtime'],0,2); $setvalue = (isset($_POST['addbutton']) && isset($_POST['startdatetime'])) ? htmlentities($_POST['startdatetime']) : $setdate." 08:00"; echo $setvalue; ?>" class='highlight-days-67 split-date format-y-m-d divider-dash'>
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
      </label> <label><input type="checkbox" id="recurs" name="recurs" onClick="$('.recurs').slideToggle();"> Recurs</label></td>
  </tr>
  
   <tr class="recurs" >
          <td class="text-nowrap text-right">Recurs:</td>
          <td class="form-inline">Every <input name="recurringmultiple" type="text" id="recurringmultiple" value="1" size="2" maxlength="2" />
                    <select name="recurringinterval" id="recurringinterval" onChange="toggleNthDow();">
                      <option value="days" <?php if(isset($row_rsEventPrefs['defaultrepeatperiod']) && $row_rsEventPrefs['defaultrepeatperiod'] =="days") echo "selected"; ?>>day(s)</option>
                     <option value="weekdays" <?php if(isset($row_rsEventPrefs['defaultrepeatperiod']) && $row_rsEventPrefs['defaultrepeatperiod'] =="weekdays") echo "selected"; ?>>weekday(s)</option>
                      <option value="weeks"  <?php if(!isset($row_rsEventPrefs['defaultrepeatperiod']) || $row_rsEventPrefs['defaultrepeatperiod'] =="weeks") echo "selected"; ?>>week(s)</option>
                      <option value="months" <?php if(isset($row_rsEventPrefs['defaultrepeatperiod']) && $row_rsEventPrefs['defaultrepeatperiod'] =="months") echo "selected"; ?>>month(s) by date</option>
                      <option value="nthdow" <?php if(isset($row_rsEventPrefs['defaultrepeatperiod']) && $row_rsEventPrefs['defaultrepeatperiod'] =="nthdow") echo "selected"; ?>>month(s) by day</option>
                      <option value="years" <?php if(isset($row_rsEventPrefs['defaultrepeatperiod']) && $row_rsEventPrefs['defaultrepeatperiod'] =="years") echo "selected"; ?>>year(s)</option>
                    </select>
                    
                    <span class="nthdow"><br> on the <select name="nth" >
                   
                    <option value="First">First</option>
                    <option value="Second">Second</option>
                    <option value="Third">Third</option>
                     <option value="Fourth">Fourth</option>
                    <option value="Last">Last</option>
                    
                    </select>
                    <select name="dow" >
                    <option value="Monday">Monday</option>
                    <option value="Tuesday">Tuesday</option>
                    <option value="Wednesday">Wednesday</option>
                    <option value="Thursday">Thursday</option>
                    <option value="Friday">Friday</option>
                    <option value="Saturday">Saturday</option>
                    <option value="Sunday">Sunday</option>
                    
                    </select></span>
<br>until
<input type="hidden" name="recurringend" id="recurringend"  value=""  class='highlight-days-67 split-date format-y-m-d divider-dash' />
                  <?php $inputname = "recurringend"; require("../core/includes/datetimeinput.inc.php"); ?></td>
        </tr>
        
        
  <tr>
    <td class="top text-right"><label for="eventdetails">Notes:</label></td>
    <td><textarea name="eventdetails" id="eventdetails"><?php echo isset($_POST['eventdetails']) ? htmlentities($_POST['eventdetails'], ENT_COMPAT, "UTF-8") : ""; ?></textarea></td>
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
mysql_free_result($rsLoggedIn);

mysql_free_result($rsResources);

mysql_free_result($rsEventPrefs);

mysql_free_result($rsThisCategory);
?>
