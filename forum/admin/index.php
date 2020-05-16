<?php require_once('../../Connections/aquiescedb.php'); ?>
<?php require_once('../../core/includes/adminAccess.inc.php'); ?><?php require_once('../../core/includes/framework.inc.php'); ?>
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

$MM_restrictGoTo = "../../login/index.php?notloggedin=true";
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
$currentPage = $_SERVER["PHP_SELF"];

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




$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
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


if(isset($_POST['checkbox'])) {
	 mysql_select_db($database_aquiescedb, $aquiescedb);
	foreach($_POST['checkbox'] as $key => $value) {
		$update = "UPDATE forumcomment SET statusID = 2, modifiedbyID = ".intval($row_rsLoggedIn['ID']).", modifieddatetime = NOW() WHERE ID = ".intval($value);
		
		mysql_query($update, $aquiescedb) or die(mysql_error());
	}
	$msg = count($_POST['checkbox'])." posts have been deleted.";
	
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE preferences SET forumsections=%s, forummoderatorID=%s, approveforumposts=%s, allowforumjpeg=%s, allowforumHTML=%s, allowforumflagreview=%s, forumpublic=%s, userpostalias=%s WHERE ID=%s",
                       GetSQLValueString(isset($_POST['forumsections']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['moderatorID'], "int"),
                       GetSQLValueString(isset($_POST['approveforumposts']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['allowforumjpeg']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['allowforumHTML']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['allowforumflagreview']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['publicforum']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['userpostalias'], "int"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());

  $updateGoTo = "index.php?defaultTab=1";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo));
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form2")) {
  $updateSQL = sprintf("UPDATE preferences SET forumintrotext=%s WHERE ID=%s",
                       GetSQLValueString($_POST['forumintrotext'], "text"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());

  $updateGoTo = "index.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo));
}

$maxRows_rsTopics = 50;
$pageNum_rsTopics = 0;
if (isset($_GET['pageNum_rsTopics'])) {
  $pageNum_rsTopics = $_GET['pageNum_rsTopics'];
}
$startRow_rsTopics = $pageNum_rsTopics * $maxRows_rsTopics;

$varRegionID_rsTopics = "1";
if (isset($regionID)) {
  $varRegionID_rsTopics = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsTopics = sprintf("SELECT forumtopic.ID, forumtopic.topic, forumcomment.posteddatetime, forumtopic.editorpick, forumtopic.sectionID, users.firstname, users.surname, forumtopic.statusID, forumsection.sectionname, forumcomment.postedbyID, forumtopic.editorpick FROM forumtopic LEFT JOIN forumcomment ON (forumcomment.topicID = forumtopic.ID) LEFT JOIN users ON (forumcomment.postedbyID = users.ID) LEFT JOIN forumsection ON (forumtopic.sectionID = forumsection.ID) WHERE forumtopic.regionID = 0 OR forumtopic.regionID = %s GROUP BY forumtopic.ID ORDER BY forumcomment.posteddatetime DESC", GetSQLValueString($varRegionID_rsTopics, "int"));
$query_limit_rsTopics = sprintf("%s LIMIT %d, %d", $query_rsTopics, $startRow_rsTopics, $maxRows_rsTopics);
$rsTopics = mysql_query($query_limit_rsTopics, $aquiescedb) or die(mysql_error());
$row_rsTopics = mysql_fetch_assoc($rsTopics);

if (isset($_GET['totalRows_rsTopics'])) {
  $totalRows_rsTopics = $_GET['totalRows_rsTopics'];
} else {
  $all_rsTopics = mysql_query($query_rsTopics);
  $totalRows_rsTopics = mysql_num_rows($all_rsTopics);
}
$totalPages_rsTopics = ceil($totalRows_rsTopics/$maxRows_rsTopics)-1;

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = "SELECT approveforumposts, allowforumjpeg, allowforumHTML, preferences.forumpublic, preferences.forumsections, preferences.forummoderatorID, preferences.allowforumflagreview, preferences.forumintrotext, preferences.userpostalias FROM preferences";
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsAdministrators = "SELECT ID, firstname, surname FROM users WHERE usertypeID >= 7";
$rsAdministrators = mysql_query($query_rsAdministrators, $aquiescedb) or die(mysql_error());
$row_rsAdministrators = mysql_fetch_assoc($rsAdministrators);
$totalRows_rsAdministrators = mysql_num_rows($rsAdministrators);

$limit = (isset($_GET['csv']) && $_GET['csv']==1)  ? 1000 : 20;

$varSearch_rsLatestPosts = "%";
if (isset($_GET['search'])) {
  $varSearch_rsLatestPosts = $_GET['search'];
}
$varRegionID_rsLatestPosts = "1";
if (isset($regionID)) {
  $varRegionID_rsLatestPosts = $regionID;
}
$varShowAll_rsLatestPosts = "0";
if (isset($_GET['showall'])) {
  $varShowAll_rsLatestPosts = $_GET['showall'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLatestPosts = sprintf("SELECT forumcomment.ID, forumcomment.posteddatetime,  forumtopic.topic, forumcomment.message, forumcomment.statusID, forumcomment.rating FROM forumcomment LEFT JOIN forumtopic ON (forumcomment.topicID = forumtopic.ID) WHERE (forumtopic.regionID = 0 OR forumtopic.regionID =%s) AND (forumcomment.statusID < 2 OR  %s = 1) AND (forumcomment.message LIKE %s) ORDER BY posteddatetime DESC LIMIT ".$limit."", GetSQLValueString($varRegionID_rsLatestPosts, "int"),GetSQLValueString($varShowAll_rsLatestPosts, "int"),GetSQLValueString("%" . $varSearch_rsLatestPosts . "%", "text"));
$rsLatestPosts = mysql_query($query_rsLatestPosts, $aquiescedb) or die(mysql_error());
$row_rsLatestPosts = mysql_fetch_assoc($rsLatestPosts);
$totalRows_rsLatestPosts = mysql_num_rows($rsLatestPosts);

$queryString_rsTopics = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsTopics") == false && 
        stristr($param, "totalRows_rsTopics") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsTopics = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsTopics = sprintf("&totalRows_rsTopics=%d%s", $totalRows_rsTopics, $queryString_rsTopics);

if(isset($_GET['csv']) && $_GET['csv']==1) {
	
	$headers = array("ID|hide","Date/time|date","Topic","Message","Status", "Rating");
	
	exportCSV($headers, $rsLatestPosts, $filename="Posts-YY-MM-DD");
	die();
}
?><!doctype html>

<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Comments Manager"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<?php if($row_rsPreferences['forumsections'] !=1) { ?>
<style>
.section {
display:none;
} 
</style><?php } ?>
<script src="/SpryAssets/SpryTabbedPanels.js"></script>
<link href="/SpryAssets/SpryTabbedPanels.css" rel="stylesheet"  />
<?php $WYSIWYGstyle = "compact";
require_once('../../core/tinymce/tinymce.inc.php'); ?>

<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" -->
            <div class="page forum">
  <?php require_once('../../core/region/includes/chooseregion.inc.php'); ?>
<h1><i class="glyphicon glyphicon-comment"></i> Comments Manager</h1>
  <?php if(isset($submit_error)) { ?><p class="alert alert-danger" role="alert"><?php echo $submit_error; ?></p><?php } ?>
<nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
        <li  class="nav-item section">          <a href="sections/index.php" class="nav-link"><i class="glyphicon glyphicon-cog"></i> Manage Sections</a></li>
        <li class="nav-item viewforum">          <a href="/forum/" target="_blank" class="nav-link" rel="noopener" onClick="if(top.opener && !top.opener.closed) { top.opener.location = '/forum/'; top.opener.focus(); } else { window.open('/forum/','_blank'); } return false;"><i class="glyphicon glyphicon-cog"></i> View Forums</a></li>
        <li class="nav-item"><a href="banned/index.php" class="nav-link"><i class="glyphicon glyphicon-cog"></i> Banned Words</a></li>
      </ul></div></nav><?php require_once('../../core/includes/alert.inc.php'); ?>
      <div id="TabbedPanels1" class="TabbedPanels">
        <ul class="TabbedPanelsTabGroup">
          <li class="TabbedPanelsTab" tabindex="0">Latest</li>
          <li class="TabbedPanelsTab" tabindex="0">Topics</li>
<li class="TabbedPanelsTab" tabindex="0">Options</li>
          <li class="TabbedPanelsTab" tabindex="0">Intro</li>
        </ul>
        <div class="TabbedPanelsContentGroup">
          <div class="TabbedPanelsContent"><form method="get" name="forumsearchform"><fieldset class="form-inline"><legend>Search</legend><input name="search" type="text" value="<?php echo isset($_GET['search']) ? htmlentities($_GET['search'], ENT_COMPAT, "UTF-8"): ""; ?>" size="50" maxlength="50" class="form-control"> <button type="submit"  onClick="this.form.csv.value=0" class="btn btn-default btn-secondary">Search</button> <label><input name="showall" type="checkbox" value="1" <?php if(isset($_GET['showall'])) echo "checked"; ?> onClick="this.form.csv.value=0;this.form.submit()">&nbsp;Show deleted</label><input name="csv" type="hidden" value="0"> <a href="javascript:vois(0)" onClick="document.forumsearchform.csv.value=1;document.forumsearchform.submit();" class="link_csv">Download all as spreadsheet</a></fieldset> </form>
            <?php if ($totalRows_rsLatestPosts > 0) { // Show if recordset not empty ?>
            <form method="post" name="checkboxform" id="checkboxform">
  <table class="table table-hover">
  <thead>
    <tr>
      <th>&nbsp;</th>
      <th><input type="checkbox"  onClick="$('#checkboxform input:checkbox').prop('checked', $(this).prop('checked'));"></th>
      <th>Posted</th>
      <th>Message</th>
      <th>Rating</th>
  
      
      <th>Edit</th>
      
    </tr></thead><tbody>
    <?php do { ?>
      <tr>
        
        <td class="status<?php echo $row_rsLatestPosts['statusID']; ?>">&nbsp;</td><td><input name="checkbox[<?php echo $row_rsLatestPosts['ID']; ?>]" type="checkbox" value="<?php echo $row_rsLatestPosts['ID']; ?>"></td><td class="top text-nowrap"><?php echo date('d M Y',strtotime($row_rsLatestPosts['posteddatetime']))."<br /><em>".date('g.ia',strtotime($row_rsLatestPosts['posteddatetime']))."</em>"; ?></td>
        <td class="top"><em><?php echo $row_rsLatestPosts['topic']; ?></em><br>
          <?php echo $row_rsLatestPosts['message']; ?></td>
          <td><span class="rating starrating rating<?php echo $row_rsLatestPosts['rating']; ?>"><?php echo isset($row_rsLatestPosts['rating']) ? $row_rsLatestPosts['rating']."/10": ""; ?></span></td>
       
        <td class="top"><a href="<?php if($row_rsLatestPosts['type']=="Response:") { ?>edit_comment.php?commentID=<?php echo $row_rsLatestPosts['ID']; } else { ?>edit_comment.php?commentID=<?php echo $row_rsLatestPosts['ID']; } ?>" class="link_edit icon_only">Edit</a></td>
        
        
      </tr>
      
      <?php } while ($row_rsLatestPosts = mysql_fetch_assoc($rsLatestPosts)); ?></tbody>
  </table><fieldset>With selected: <a href="javascript:void(0);" onClick="if(confirm('Are you sure you want to delete these posts?')) { document.checkboxform.submit(); } return false;">Delete</a></fieldset></form>
  <?php } // Show if recordset not empty ?>
  <?php if ($totalRows_rsLatestPosts == 0) { // Show if recordset empty ?>
  <p>There are currently no posts.</p>
  <?php } // Show if recordset empty ?>
          </div>
          <div class="TabbedPanelsContent">
            <?php if ($totalRows_rsTopics == 0) { // Show if recordset empty ?>
            <p>There are no forum topics in the database</p>
            <?php } // Show if recordset empty ?>
            <?php if ($totalRows_rsTopics > 0) { // Show if recordset not empty ?>
            <p class="text-muted">Topics <?php echo ($startRow_rsTopics + 1) ?> to <?php echo min($startRow_rsTopics + $maxRows_rsTopics, $totalRows_rsTopics) ?> of <?php echo $totalRows_rsTopics ?></p>
            <table  class="table table-hover">
            <thead>
              <tr>
                <th>&nbsp;</th>
                <th>Posted</th>
                <th>Topic</th>
                <th  class="section">Section</th>
                <th>Posted by </th>
                <th>Pick</th>
                <th>Edit</th>
              </tr></thead><tbody>
              <?php do { ?>
              <tr>
                <td><?php if ($row_rsTopics['statusID']==1) { ?>
                  <img src="../../core/images/icons/green-light.png" alt="Approved - displays on site" width="16" height="16" style="vertical-align:
middle;" />
                  <?php } else if ($row_rsTopics['statusID']==0) { ?>
                  <img src="../../core/images/icons/amber-light.png" alt="Pending apporval - does not display on site" width="16" height="16" style="vertical-align:
middle;" />
                  <?php } else { ?>
                  <img src="../../core/images/icons/red-light.png" alt="Not displayed" width="16" height="16" style="vertical-align:
middle;" />
                  <?php } ?></td>
                <td><?php echo date('d M Y',strtotime($row_rsTopics['posteddatetime'])); ?>&nbsp;</td>
                <td><a href="edit_topic.php?topicID=<?php echo $row_rsTopics['ID']; ?>"><?php echo $row_rsTopics['topic']; ?></a>&nbsp;</td>
                <td class="section" ><em><a href="sections/update_section.php?sectionID=<?php echo $row_rsTopics['sectionID']; ?>"><?php echo $row_rsTopics['sectionname']; ?></a></em></td>
                <td><a href="../../admin/users/modify_user.php?userID=<?php echo $row_rsTopics['postedbyID']; ?>"><?php echo $row_rsTopics['firstname']; ?>&nbsp;<?php echo $row_rsTopics['surname']; ?>&nbsp;</a></td>
                 <td class="top"><?php if($row_rsTopics['editorpick']==1) { ?><img src="../../core/images/icons/thumb_up.png" width="16" height="16" alt="Editor's pick" /><?php } else { ?>&nbsp;<?php } ?></td>
                <td><a href="edit_topic.php?topicID=<?php echo $row_rsTopics['ID']; ?>" class="link_edit icon_only">View/Edit</a></td>
              </tr>
              <?php } while ($row_rsTopics = mysql_fetch_assoc($rsTopics)); ?></tbody>
            </table>
            <table  class="form-table">
              <tr>
                <td><?php if ($pageNum_rsTopics > 0) { // Show if not first page ?>
                  <a href="<?php printf("%s?pageNum_rsTopics=%d%s", $currentPage, 0, $queryString_rsTopics); ?>">First</a>
                  <?php } // Show if not first page ?></td>
               <td><?php if ($pageNum_rsTopics > 0) { // Show if not first page ?>
                  <a href="<?php printf("%s?pageNum_rsTopics=%d%s", $currentPage, max(0, $pageNum_rsTopics - 1), $queryString_rsTopics); ?>" rel="prev">Previous</a>
                  <?php } // Show if not first page ?></td>
                <td><?php if ($pageNum_rsTopics < $totalPages_rsTopics) { // Show if not last page ?>
                  <a href="<?php printf("%s?pageNum_rsTopics=%d%s", $currentPage, min($totalPages_rsTopics, $pageNum_rsTopics + 1), $queryString_rsTopics); ?>" rel="next">Next</a>
                  <?php } // Show if not last page ?></td>
                <td><?php if ($pageNum_rsTopics < $totalPages_rsTopics) { // Show if not last page ?>
                  <a href="<?php printf("%s?pageNum_rsTopics=%d%s", $currentPage, $totalPages_rsTopics, $queryString_rsTopics); ?>">Last</a>
                  <?php } // Show if not last page ?></td>
              </tr>
            </table>
            <?php } // Show if recordset not empty ?>
          </div>
<div class="TabbedPanelsContent">
        <form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1">
              <label>
              <input <?php if (!(strcmp($row_rsPreferences['approveforumposts'],1))) {echo "checked=\"checked\"";} ?> name="approveforumposts" type="checkbox" id="approveforumposts" value="1" onChange="this.form.submit()" />
                Forum posts must be approved before displaying on site </label>
              <input name="ID" type="hidden" id="ID" value="1" />
              <input type="hidden" name="MM_update" value="form1" />
              <br /><label><input <?php if (!(strcmp($row_rsPreferences['allowforumflagreview'],1))) {echo "checked=\"checked\"";} ?> name="allowforumflagreview" type="checkbox" id="allowforumflagreview" value="1" onChange="this.form.submit()" />
                Allow members to flag posts for review (flagged posts will be automatically removed until review)</label>
              <br />
<label><input <?php if (!(strcmp($row_rsPreferences['allowforumjpeg'],1))) {echo "checked=\"checked\"";} ?> name="allowforumjpeg" type="checkbox" id="allowforumjpeg" value="1" onChange="this.form.submit()" />
                Allow images to be uploaded</label>
              <br />
              <label>
              <input <?php if (!(strcmp($row_rsPreferences['allowforumHTML'],1))) {echo "checked=\"checked\"";} ?> name="allowforumHTML" type="checkbox" id="allowforumHTML" value="1" onChange="this.form.submit()" />
                Allow basic HTML </label>
              <br />
              <label>
              <input <?php if (!(strcmp($row_rsPreferences['forumpublic'],1))) {echo "checked=\"checked\"";} ?> name="publicforum" type="checkbox" id="publicforum" onChange="this.form.submit()" value="1" />
                Basic forum readable by public </label>
              <br />
              <label>
              <input <?php if (!(strcmp($row_rsPreferences['forumsections'],1))) {echo "checked=\"checked\"";} ?> name="forumsections" type="checkbox" id="forumsections" onChange="this.form.submit()" value="1" />
                Divide forum into sections <br>
                
              </label><p>Users post as: 
                <label>
                  <input <?php if (!(strcmp($row_rsPreferences['userpostalias'],"0"))) {echo "checked=\"checked\"";} ?> type="radio" name="userpostalias" value="0" id="userpostalias_0">
                  Full name</label>
               &nbsp;&nbsp;
                <label>
                  <input <?php if (!(strcmp($row_rsPreferences['userpostalias'],"1"))) {echo "checked=\"checked\"";} ?> type="radio" name="userpostalias" value="1" id="userpostalias_1">
                  Firstname, Surname initial</label>
                &nbsp;&nbsp;
                <label>
                  <input <?php if (!(strcmp($row_rsPreferences['userpostalias'],"2"))) {echo "checked=\"checked\"";} ?> type="radio" name="userpostalias" value="2" id="userpostalias_2">
                  Initials</label>
                 &nbsp;&nbsp;
                <label>
                  <input <?php if (!(strcmp($row_rsPreferences['userpostalias'],"3"))) {echo "checked=\"checked\"";} ?> type="radio" name="userpostalias" value="3" id="userpostalias_3">
  "Visitor"</label>
               
              </p>
              <p class="form-inline">Forum moderator:
                <select name="moderatorID" onChange="this.form.submit()" class="form-control">
                    <option value="0">None</option>
                    <?php
do {  
?>
                    <option value="<?php echo $row_rsAdministrators['ID']?>" <?php if (!(strcmp($row_rsAdministrators['ID'], $row_rsPreferences['forummoderatorID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsAdministrators['firstname']." ".$row_rsAdministrators['surname']; ?></option>
                    <?php
} while ($row_rsAdministrators = mysql_fetch_assoc($rsAdministrators));
  $rows = mysql_num_rows($rsAdministrators);
  if($rows > 0) {
      mysql_data_seek($rsAdministrators, 0);
	  $row_rsAdministrators = mysql_fetch_assoc($rsAdministrators);
  }
?>
                  </select>
              (Individual sections and topics can have their own moderator)</p>
              <p>
                <button name="submit2" type="submit" class="btn btn-primary" id="submit2" >Save changes</button>
              </p>
            </form>
          </div>
<div class="TabbedPanelsContent"><p>Add an optional introduction that will be displayed at the top of the forum home page:</p><form action="<?php echo $editFormAction; ?>" method="POST" name="form2" id="form2">
           
              <textarea name="forumintrotext" id="forumintrotext" cols="50" rows="6" class="form-control"><?php echo $row_rsPreferences['forumintrotext']; ?></textarea>
              <br />
              <button type="submit" class="btn btn-primary" >Save changes</button>

              <input name="ID" type="hidden" id="ID" value="1" />
              <input type="hidden" name="MM_update" value="form2" />
          </form></div>
        </div>
    </div>
      

    <?php if (isset($_GET['defaultTab'])) { echo '<script>
<!--
var TabbedPanels1 = new Spry.Widget.TabbedPanels("TabbedPanels1", {defaultTab:'.intval($_GET['defaultTab']).'});
//-->
    </script>'; } else { ?>
         
    <script>
<!--
var TabbedPanels1 = new Spry.Widget.TabbedPanels("TabbedPanels1");
//-->
    </script>
<?php } ?>
</div>
   <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsTopics);

mysql_free_result($rsPreferences);

mysql_free_result($rsAdministrators);

mysql_free_result($rsLoggedIn);

mysql_free_result($rsLatestPosts);
?>
