<?php require_once('../../core/includes/sslcheck.inc.php'); ?>
<?php require_once('../../Connections/aquiescedb.php'); ?><?php require_once('../../core/includes/framework.inc.php'); ?><?php require_once('../includes/userfunctions.inc.php'); ?>
<?php
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

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}



$currentPage = $_SERVER["PHP_SELF"];

$colname_rsUser = "-1";
if (isset($_GET['userID'])) {
  $colname_rsUser = $_GET['userID'];
}
$varusername_rsUser = "-1";
if (isset($_SESSION['MM_Username'])) {
  $varusername_rsUser = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsUser = sprintf("SELECT users.ID, users.firstname, users.surname, users.email, users.mobile, users.aboutme, users.identityverified, location.locationname, users.jobtitle, users.imageURL, users.showemail, usertype.name, users.usertypeID, users.websiteURL, users.twitterID, users.facebookURL, location.active, location.`public`, users.defaultaddressID, users.telephone, usertype.name AS rank FROM users LEFT JOIN location ON (users.defaultaddressID = location.ID) LEFT JOIN usertype ON (users.usertypeID = usertype.ID) WHERE (users.ID = %s) OR ( %s = -1 AND users.username = %s)", GetSQLValueString($colname_rsUser, "int"),GetSQLValueString($colname_rsUser, "int"),GetSQLValueString($varusername_rsUser, "text"));
$rsUser = mysql_query($query_rsUser, $aquiescedb) or die(mysql_error());
$row_rsUser = mysql_fetch_assoc($rsUser);
$totalRows_rsUser = mysql_num_rows($rsUser);

$colname_rsLoggedIn = "1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = (get_magic_quotes_gpc()) ? $_SESSION['MM_Username'] : addslashes($_SESSION['MM_Username']);
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID, usertypeID FROM users WHERE username = '%s'", $colname_rsLoggedIn);
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

if(isset($_POST['userID'])) { // must go after logged in and before relationships
	userRelationship($row_rsLoggedIn['ID'], $_POST['userID'], 1);
}

$_GET['userID'] = isset($_GET['userID']) ? $_GET['userID'] : $row_rsLoggedIn['ID'];

$varUserID_rsForumTopicPosts = "-1";
if (isset($_GET['userID'])) {
  $varUserID_rsForumTopicPosts = $_GET['userID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsForumTopicPosts = sprintf("SELECT COUNT(forumcomment.ID) AS count FROM forumcomment WHERE forumcomment.postedbyID = %s", GetSQLValueString($varUserID_rsForumTopicPosts, "int"));
$rsForumTopicPosts = mysql_query($query_rsForumTopicPosts, $aquiescedb) or die(mysql_error());
$row_rsForumTopicPosts = mysql_fetch_assoc($rsForumTopicPosts);
$totalRows_rsForumTopicPosts = mysql_num_rows($rsForumTopicPosts);

$varUserID_rsForumCommentPosts = "-1";
if (isset($_GET['userID'])) {
  $varUserID_rsForumCommentPosts = $_GET['userID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsForumCommentPosts = sprintf("SELECT COUNT(forumcomment.ID) AS count FROM forumcomment WHERE forumcomment.postedbyID = %s", GetSQLValueString($varUserID_rsForumCommentPosts, "int"));
$rsForumCommentPosts = mysql_query($query_rsForumCommentPosts, $aquiescedb) or die(mysql_error());
$row_rsForumCommentPosts = mysql_fetch_assoc($rsForumCommentPosts);
$totalRows_rsForumCommentPosts = mysql_num_rows($rsForumCommentPosts);

$colname_rsMicroSite = "-1";
if (isset($_GET['userID'])) {
  $colname_rsMicroSite = $_GET['userID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsMicroSite = sprintf("SELECT ID, pageTitle FROM microsite WHERE userID = %s", GetSQLValueString($colname_rsMicroSite, "int"));
$rsMicroSite = mysql_query($query_rsMicroSite, $aquiescedb) or die(mysql_error());
$row_rsMicroSite = mysql_fetch_assoc($rsMicroSite);
$totalRows_rsMicroSite = mysql_num_rows($rsMicroSite);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = "SELECT * FROM preferences";
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);

if(isset($_GET['unlink']) && intval($_GET['userID'])>0) {
	// AFTER logged in BEFORE relationship
	$update = "UPDATE userrelationship SET relationshiptypeID=0 WHERE createdbyID = ".$row_rsLoggedIn['ID']." AND userID = ".intval($_GET['userID']);
	mysql_query($update, $aquiescedb) or die(mysql_error());
	header("location: index.php?userID=".intval($_GET['userID'])); exit;
}


$varUser_rsRelationship = "-1";
if (isset($_GET['userID'])) {
  $varUser_rsRelationship = $_GET['userID'];
}
$varLoggedIn_rsRelationship = "-1";
if (isset($row_rsLoggedIn['ID'])) {
  $varLoggedIn_rsRelationship = $row_rsLoggedIn['ID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsRelationship = sprintf("SELECT userrelationship.relationshiptypeID, createdbyID FROM userrelationship WHERE ((userID = %s AND createdbyID = %s) OR (userID = %s AND createdbyID = %s)) AND userrelationship.relationshiptypeID = 1", GetSQLValueString($varUser_rsRelationship, "int"),GetSQLValueString($varLoggedIn_rsRelationship, "int"),GetSQLValueString($varLoggedIn_rsRelationship, "int"),GetSQLValueString($varUser_rsRelationship, "int"));
$rsRelationship = mysql_query($query_rsRelationship, $aquiescedb) or die(mysql_error());
$row_rsRelationship = mysql_fetch_assoc($rsRelationship);
$totalRows_rsRelationship = mysql_num_rows($rsRelationship);

$maxRows_rsConnections = 50;
$pageNum_rsConnections = 0;
if (isset($_GET['pageNum_rsConnections'])) {
  $pageNum_rsConnections = $_GET['pageNum_rsConnections'];
}
$startRow_rsConnections = $pageNum_rsConnections * $maxRows_rsConnections;

$varUserID_rsConnections = "-1";
if (isset($_GET['userID'])) {
  $varUserID_rsConnections = $_GET['userID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsConnections = sprintf("SELECT userrelationship.userID, users.firstname, users.surname, users.imageURL FROM userrelationship LEFT JOIN users ON (userrelationship.userID = users.ID) WHERE createdbyID = %s AND relationshiptypeID = 1", GetSQLValueString($varUserID_rsConnections, "int"));
$query_limit_rsConnections = sprintf("%s LIMIT %d, %d", $query_rsConnections, $startRow_rsConnections, $maxRows_rsConnections);
$rsConnections = mysql_query($query_limit_rsConnections, $aquiescedb) or die(mysql_error());
$row_rsConnections = mysql_fetch_assoc($rsConnections);

if (isset($_GET['totalRows_rsConnections'])) {
  $totalRows_rsConnections = $_GET['totalRows_rsConnections'];
} else {
  $all_rsConnections = mysql_query($query_rsConnections);
  $totalRows_rsConnections = mysql_num_rows($all_rsConnections);
}
$totalPages_rsConnections = ceil($totalRows_rsConnections/$maxRows_rsConnections)-1;

$varUserGroup_rsNewsCategories = "0";
if (isset($_SESSION['MM_UserGroup'])) {
  $varUserGroup_rsNewsCategories = $_SESSION['MM_UserGroup'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsNewsCategories = sprintf("SELECT newssection.ID FROM newssection WHERE newssection.accesslevel <= %s  AND newssection.statusID = 1", GetSQLValueString($varUserGroup_rsNewsCategories, "int"));
$rsNewsCategories = mysql_query($query_rsNewsCategories, $aquiescedb) or die(mysql_error());
$row_rsNewsCategories = mysql_fetch_assoc($rsNewsCategories);
$totalRows_rsNewsCategories = mysql_num_rows($rsNewsCategories);

$varUserID_rsOtherLocations = "-1";
if (isset($_GET['userID'])) {
  $varUserID_rsOtherLocations = $_GET['userID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsOtherLocations = sprintf("SELECT locationuser.ID, location.locationname, locationuser.daysofweek FROM locationuser LEFT JOIN location ON (locationuser.locationID = location.ID) WHERE location.`public` = 1 AND location.active = 1 AND locationuser.userID = %s", GetSQLValueString($varUserID_rsOtherLocations, "int"));
$rsOtherLocations = mysql_query($query_rsOtherLocations, $aquiescedb) or die(mysql_error());
$row_rsOtherLocations = mysql_fetch_assoc($rsOtherLocations);
$totalRows_rsOtherLocations = mysql_num_rows($rsOtherLocations);

$colname_rsGroups = "-1";
if (isset($_GET['userID'])) {
  $colname_rsGroups = $_GET['userID'];
}
$varRegionID_rsGroups = "1";
if (isset($regionID)) {
  $varRegionID_rsGroups = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsGroups = sprintf("SELECT usergroup.groupname, usergroup.optin FROM usergroupmember LEFT JOIN usergroup ON (usergroupmember.groupID = usergroup.ID) WHERE userID = %s AND usergroup.optin >=0 AND usergroup.statusID = 1 AND (usergroup.regionID = 0 OR usergroup.regionID = %s) GROUP BY usergroup.ID ORDER BY usergroup.ordernum ASC ", GetSQLValueString($colname_rsGroups, "int"),GetSQLValueString($varRegionID_rsGroups, "int"));
$rsGroups = mysql_query($query_rsGroups, $aquiescedb) or die(mysql_error());
$row_rsGroups = mysql_fetch_assoc($rsGroups);
$totalRows_rsGroups = mysql_num_rows($rsGroups);



?>
<!doctype html>
<!-- Web design by Paul Egan, Jim Campbell -->
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Profile for ". $row_rsUser['firstname']." ".$row_rsUser['surname']; echo $pageTitle." | ".$site_name; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<link href="../css/membersDefault.css" rel="stylesheet"  />
<?php if(is_readable(SITE_ROOT."news/css/newsDefault.css")) { ?>
<link href="/news/css/newsDefault.css" rel="stylesheet"  />
<?php } ?><script src="/3rdparty/jquery/jquery.timeago.js"></script>
<script >
jQuery(document).ready(function() {
  jQuery("abbr.timeago").timeago();
 
  });
</script>
<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" -->
  <div id="pageProfile" class="container pageBody members">
    <div id="profileheader">
      <p class="fltrt text-right"><a href="/members/" class="link_undo icon_with_text">My Home</a> 
      <?php if ($row_rsLoggedIn['ID'] == $row_rsUser['ID']) { // authorised to edit ?>
      <br /><a href="update_profile.php" class="link_edit icon_with_text">Update My Profile </a> 
      <?php } ?></p><h1>
        
        Profile for <?php echo $row_rsUser['firstname']; ?> <?php echo $row_rsUser['surname']; ?><span class="verified<?php echo $row_rsUser['identityverified']; ?>" title="<?php echo $row_rsUser['identityverified']==1 ? "This user's identity is verified as genuine" : ""; ?>">&nbsp;</span></h1>
		
          <div id="profileimage"><?php if (isset($row_rsUser['imageURL'])) { ?><a href="/core/images/view_large_image.php?imageURL=<?php echo $row_rsUser['imageURL']; ?>"><img src="<?php echo getImageURL($row_rsUser['imageURL'],"thumb"); ?>" alt="<?php echo htmlentities($row_rsUser['firstname']." ".$row_rsUser['surname'], ENT_COMPAT, "UTF-8"); ?>" /></a> <?php } else { ?>
            <img src="../images/user-anonymous-150.png" width="150" height="150" alt="This user has not posted a photo of themselves yet" />
    <?php } ?></div>
         
          <div id="profiletext">
       <p><?php  echo isset($row_rsUser['jobtitle']) ? "<span data-toggle=\"tooltip\" title=\"".$row_rsUser['rank']."\">".htmlentities($row_rsUser['jobtitle'], ENT_COMPAT, "UTF-8")."</span>" : $row_rsUser['rank']; ?></p>
     
<?php if($row_rsUser['ID']!=$row_rsLoggedIn['ID'] && $row_rsPreferences['membernetwork']==1) { // can connect and not you ?>
        <div><form action="<?php echo $editFormAction; ?>" method="post" name="form" id="form">
<?php if($totalRows_rsRelationship>0) { // is relationship
	if($totalRows_rsRelationship>1) { // both connected
		echo "You and ".htmlentities($row_rsUser['firstname'], ENT_COMPAT, "UTF-8")." are linked with each other. <a href=\"index.php?userID=".$row_rsUser['ID']."&amp;unlink=true\">Unlink with them</a>";
	} else if($row_rsRelationship['createdbyID']==$row_rsLoggedIn['ID']) { 
		// pending from you
		echo "You are linked with ".htmlentities($row_rsUser['firstname'], ENT_COMPAT, "UTF-8").". <a href=\"index.php?userID=".$row_rsUser['ID']."&amp;unlink=true\">Unlink</a>";
	} else { // pending for them
		echo htmlentities($row_rsUser['firstname'], ENT_COMPAT, "UTF-8")." is linked with you."; ?>
        <button name="submitbutton" type="submit" >Link with <?php echo  htmlentities($row_rsUser['firstname'], ENT_COMPAT, "UTF-8"); ?>...</button>
<?php } // end pending for them
} // end is relationship
else { ?><button name="submitbutton" type="submit" >Link with <?php echo htmlentities($row_rsUser['firstname'], ENT_COMPAT, "UTF-8")." ".htmlentities($row_rsUser['surname'], ENT_COMPAT, "UTF-8"); ?></button><?php } ?>
        <input name="createdbyID" type="hidden" id="createdbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
        <input name="createddatetime" type="hidden" id="createddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
        <input name="userID" type="hidden" id="userID" value="<?php echo $row_rsUser['ID']; ?>" />
        <input type="hidden" name="MM_insert" value="form" />    </form>    
</div>
<?php } // end can connect } ?>
    
    
    <?php if($row_rsUser['showemail']==1) { ?>
    <p><span class="glyphicon glyphicon-envelope"></span> <strong>Email:</strong> <a href="mailto:<?php echo htmlentities($row_rsUser['email'], ENT_COMPAT, "UTF-8"); ?>" > <?php echo htmlentities($row_rsUser['email'], ENT_COMPAT, "UTF-8"); ?></a></p>
    <?php } ?>
    <div class="row">
   <?php if (isset($row_rsUser['telephone'])) { ?>
   <div class="col-md-6">
       <p><span class="glyphicon glyphicon-phone-alt"></span> <strong>Direct:</strong> <?php echo htmlentities($row_rsUser['telephone'], ENT_COMPAT, "UTF-8"); ?></p></div>
      <?php }?>
      <?php if (isset($row_rsUser['mobile'])) { ?>
   <div class="col-md-6">
       <p><span class="glyphicon glyphicon-phone"></span> <strong>Mobile:</strong> <?php echo htmlentities($row_rsUser['mobile'], ENT_COMPAT, "UTF-8"); ?></p></div>
      <?php }?>
 </div>     
       <?php if (isset($row_rsUser['websiteURL'])) { ?>
       <p><strong>Web site:</strong> <a href="<?php echo htmlentities($row_rsUser['websiteURL'], ENT_COMPAT, "UTF-8"); ?>" target="_blank" rel="noopener"><?php echo htmlentities($row_rsUser['websiteURL'], ENT_COMPAT, "UTF-8"); ?></a></p>
      <?php }?>
      
       <?php if (isset($row_rsUser['facebookURL'])) { ?>
       <p><strong>Facebook page:</strong> <a href="<?php echo htmlentities($row_rsUser['facebookURL'], ENT_COMPAT, "UTF-8"); ?>" target="_blank" rel="noopener"><?php echo htmlentities($row_rsUser['facebookURL'], ENT_COMPAT, "UTF-8"); ?></a></p>
      <?php }?>
      
       <?php if (isset($row_rsUser['twitterID'])) { ?>
       <p><strong>Twitter:</strong> <a href="http://twitter.com/<?php echo htmlentities(str_replace("@","",$row_rsUser['twitterID']), ENT_COMPAT, "UTF-8"); ?>" target="_blank" rel="noopener"><?php echo htmlentities($row_rsUser['twitterID'], ENT_COMPAT, "UTF-8"); ?></a></p>
      <?php }?>
      
      
      <?php if (isset($row_rsUser['locationname']) && $row_rsUser['public'] == 1 && $row_rsUser['active'] == 1) { ?>
      <p><strong>Location:</strong> <a href="../../location/location.php?locationID=<?php echo $row_rsUser['defaultaddressID']; ?>"><?php echo htmlentities($row_rsUser['locationname'], ENT_COMPAT, "UTF-8"); ?></a></p>
      <?php } ?>
    <?php if(!isset($row_rsUser['imageURL']) && $row_rsUser['ID']==$row_rsLoggedIn['ID']) { ?>You haven't posted a photo of yourself. <a href="update_profile.php">Add one now</a>.
            <?php  } ?>
    <h2>About Me:</h2>
    <p>
      <?php if (isset ($row_rsUser['aboutme'])) { echo nl2br(htmlentities($row_rsUser['aboutme'], ENT_COMPAT, "UTF-8")); } else if ($row_rsUser['ID'] == $row_rsLoggedIn['ID']) { ?>
      So far, you haven't added anything about yourself. <a href="update_profile.php">Add some interesting facts now!</a>
      <?php } else { ?>
      <?php echo htmlentities($row_rsUser['firstname'], ENT_COMPAT, "UTF-8"); ?> has not added anything yet.
      <?php } ?>
    </p></div>
    </div>
    <h2>Groups:</h2>
    <?php if ($totalRows_rsGroups == 0) { // Show if recordset empty ?>
  <p>Not a member of any groups</p>
  <?php } // Show if recordset empty ?>
<?php if ($totalRows_rsGroups > 0) { // Show if recordset not empty ?>
  <ul>
    <?php do { ?>
      <li class="optin<?php echo $row_rsGroups['optin']; ?>"><?php echo htmlentities($row_rsGroups['groupname'], ENT_COMPAT, "UTF-8"); ?></li>
      
      <?php } while ($row_rsGroups = mysql_fetch_assoc($rsGroups)); ?>
  </ul>
  <?php } // Show if recordset not empty ?>
<?php if($row_rsPreferences['membernetwork']==1) { // can connect and not you ?>
    <h2>User links:</h2>
    <?php if ($totalRows_rsConnections == 0) { // Show if recordset empty ?>
  <p><?php echo ($row_rsUser['ID']==$row_rsLoggedIn['ID']) ? "You don't " : htmlentities($row_rsUser['firstname'], ENT_COMPAT, "UTF-8")." doesn't ";?>have any links with other users at present.</p>
  <?php } // Show if recordset empty ?>
  <?php if ($totalRows_rsConnections > 0) { // Show if recordset not empty ?>
    <ul id="userconnections">
      <?php do { ?>
        <li><a href="/members/profile/index.php?userID=<?php echo $row_rsConnections['userID']; ?>">
        
        <div class="avatar" style="background-image:url(<?php echo isset($row_rsConnections['imageURL']) ? getImageURL($row_rsConnections['imageURL'],"thumb") : "/members/images/user-anonymous.png"; ?>);"></div>
        
        <br />
        <?php echo htmlentities($row_rsConnections['firstname'], ENT_COMPAT, "UTF-8"); ?> <?php echo htmlentities($row_rsConnections['surname'], ENT_COMPAT, "UTF-8"); ?></a></li>
        <?php } while ($row_rsConnections = mysql_fetch_assoc($rsConnections)); ?>
    </ul>
    <?php } // Show if recordset not empty ?>
<?php } // end can network ?>
<h2>Activity:</h2><?php $userID = $row_rsUser['ID'];
$newscategories = array();
do {
	array_push($newscategories,$row_rsNewsCategories['ID']);
} while($row_rsNewsCategories = mysql_fetch_assoc($rsNewsCategories));

require_once('../../news/members/newsfeed.inc.php');  ?>
    </div>
    <!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>
<?php if(false) { // avoid free result bug

mysql_free_result($rsUser);

mysql_free_result($rsLoggedIn);

mysql_free_result($rsForumTopicPosts);

mysql_free_result($rsForumCommentPosts);

mysql_free_result($rsMicroSite);

mysql_free_result($rsPreferences);

mysql_free_result($rsRelationship);

mysql_free_result($rsConnections);

mysql_free_result($rsNewsCategories);

mysql_free_result($rsOtherLocations);

mysql_free_result($rsGroups);

}
?>
