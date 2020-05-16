<?php require_once('../Connections/aquiescedb.php'); ?>
<?php

$_GET['startdate'] = (isset($_GET['startdate']) && $_GET['startdate']!="") ? $_GET['startdate']: date('Y-m-d');


// THIS PAGE IS ALSO VERY IMPORTANT FOR SEARCH ENGINE SPIDERING ALL EVENTS AS DATE BASED VIEWS USE NO FOLLOW
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

$varEvent_rsEvents = "%";
if (isset($_GET['event'])) {
  $varEvent_rsEvents = $_GET['event'];
}
$varUserGroup_rsEvents = "0";
if (isset($_SESSION['MM_UserGroup'])) {
  $varUserGroup_rsEvents = $_SESSION['MM_UserGroup'];
}
$varStartDate_rsEvents = "2000-01-01";
if (isset($_GET['startdate'])) {
  $varStartDate_rsEvents = $_GET['startdate'];
}
$varCategoryID_rsEvents = "0";
if (isset($_GET['categoryID'])) {
  $varCategoryID_rsEvents = $_GET['categoryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsEvents = sprintf("SELECT eventgroup.eventtitle, event.startdatetime, event.recurringweekly, eventcategory.title AS category, eventcategory.colour, event.statusID, eventgroup.ID AS eventgroupID, event.ID FROM eventgroup LEFT JOIN event ON (event.eventgroupID = eventgroup.ID) LEFT JOIN eventcategory ON (eventgroup.categoryID = eventcategory.ID) WHERE event.statusID = 1 AND eventgroup.usertypeID <= %s AND eventgroup.eventtitle LIKE %s AND (eventgroup.categoryID = %s OR %s = 0) AND (event.startdatetime >= %s)ORDER BY event.startdatetime ASC", GetSQLValueString($varUserGroup_rsEvents, "int"),GetSQLValueString("%" . $varEvent_rsEvents . "%", "text"),GetSQLValueString($varCategoryID_rsEvents, "int"),GetSQLValueString($varCategoryID_rsEvents, "int"),GetSQLValueString($varStartDate_rsEvents, "date"));
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
<title>
<?php  $pageTitle = "Search Events"; echo $pageTitle." | ".$site_name;?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script src="../SpryAssets/SpryValidationTextField.js"></script>
<link href="../SpryAssets/SpryValidationTextField.css" rel="stylesheet"  />
<link href="css/calendarDefault.css" rel="stylesheet"  />
<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" -->
        <div class="container pageBody events">
    <div class="crumbs"><div><span class="you_are_in">You are in: </span><a href="/">Home</a><span class="separator">&nbsp;&rsaquo;&nbsp;</span><a href="/calendar/">Calendar</a><span class="separator">&nbsp;&rsaquo;&nbsp;</span>Search</div></div>
    <h1 class="calheader">Calendar Search </h1>
    <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
      <li><a href="/calendar/" class="link_undo"><i class="glyphicon glyphicon-arrow-left"></i> Coming up...</a></li>
    </ul></div></nav>
    <fieldset class="form-inline">
      <legend>Filter</legend>
      <form action="search.php" method="get" name="form1" id="form1" role="form">
        Search for <span id="sprytextfield1">
        <input name="event" type="text"  id="event" value="<?php echo isset($_GET['entry']) ? htmlentities($_GET['entry'], ENT_COMPAT, "UTF-8") : ""; ?>" size="20" maxlength="50"/>
        </span> after
        <input name="startdate" type="hidden" id="startdate" value="<?php $inputname = "startdate"; $setvalue = $_GET['startdate']; echo $setvalue; ?>" />
        <?php require_once('../core/includes/datetimeinput.inc.php'); ?>
        &nbsp;in
        <select name="categoryID" id="categoryID">
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
        <input name="searchbutton" type="submit" class="button" id="searchbutton" value="Go" />
        
      </form>
    </fieldset>
    <?php if ($totalRows_rsEvents == 0) { // Show if recordset empty ?>
      <p>There are no events in the database that meet your search criteria.</p>
      <?php } // Show if recordset empty ?>
    <?php if ($totalRows_rsEvents > 0) { // Show if recordset not empty ?>
      <p>Events <?php echo ($startRow_rsEvents + 1) ?> to <?php echo min($startRow_rsEvents + $maxRows_rsEvents, $totalRows_rsEvents) ?> of <?php echo $totalRows_rsEvents ?> </p>
      <table border="0" cellpadding="2" cellspacing="0" class="listTable">
        <tr>
          <td><strong>Date/Time</strong></td>
          <td><strong>Entry</strong></td>
          <td><strong>Category</strong></td>
          <td>&nbsp;</td>
        </tr>
        <?php do { ?>
          <tr bgcolor="<?php echo isset($row_rsEvents['colour']) ? $row_rsEvents['colour'] : ''; ?>">
            <td class="text-nowrap"><?php echo isset($row_rsEvents['startdatetime']) ? date('d M Y, g:ia',strtotime($row_rsEvents['startdatetime'])) : "N/A"; ?></td>
            <td><a href="event.php?eventID=<?php echo $row_rsEvents['ID']; ?>"><?php echo $row_rsEvents['eventtitle']; ?></a></td>
            <td><em><?php echo $row_rsEvents['category']; ?></em></td>
            <td><a href="event.php?eventID=<?php echo $row_rsEvents['ID']; ?>" class="link_view">View</a></td>
          </tr>
          <?php } while ($row_rsEvents = mysql_fetch_assoc($rsEvents)); ?>
      </table>
      <?php } // Show if recordset not empty ?>
   
    <table width="50%" border="0" class="form-table">
      <tr>
        <td><?php if ($pageNum_rsEvents > 0) { // Show if not first page ?>
            <a href="<?php printf("%s?pageNum_rsEvents=%d%s", $currentPage, 0, $queryString_rsEvents); ?>">First</a>
            <?php } // Show if not first page ?></td>
       <td><?php if ($pageNum_rsEvents > 0) { // Show if not first page ?>
            <a href="<?php printf("%s?pageNum_rsEvents=%d%s", $currentPage, max(0, $pageNum_rsEvents - 1), $queryString_rsEvents); ?>" rel="prev">Previous</a>
            <?php } // Show if not first page ?></td>
        <td><?php if ($pageNum_rsEvents < $totalPages_rsEvents) { // Show if not last page ?>
            <a href="<?php printf("%s?pageNum_rsEvents=%d%s", $currentPage, min($totalPages_rsEvents, $pageNum_rsEvents + 1), $queryString_rsEvents); ?>" rel="next">Next</a>
            <?php } // Show if not last page ?></td>
        <td><?php if ($pageNum_rsEvents < $totalPages_rsEvents) { // Show if not last page ?>
            <a href="<?php printf("%s?pageNum_rsEvents=%d%s", $currentPage, $totalPages_rsEvents, $queryString_rsEvents); ?>">Last</a>
            <?php } // Show if not last page ?></td>
      </tr>
    </table>
    </p>
    <script>
<!--
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1", "none", {isRequired:false, hint:"All events"});
//-->
      </script></div>
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

mysql_free_result($rsEventPrefs);
?>
