<?php require_once('../Connections/aquiescedb.php'); ?><?php require_once('../core/includes/framework.inc.php'); ?>
<?php $_GET['forumsectionID'] = isset($_GET['forumsectionID']) ? $_GET['forumsectionID'] : 1; ?>
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

if (!isset($_SESSION)) {
  session_start();
}
$currentPage = $_SERVER["PHP_SELF"];
if(isset($_GET['pageNum_rsTopics'])) $_GET['pageNum_rsTopics'] = intval($_GET['pageNum_rsTopics']);
if(isset($_GET['totalRows_rsTopics'])) $_GET['totalRows_rsTopics'] = intval($_GET['totalRows_rsTopics']);

$maxRows_rsTopics = 10;
$pageNum_rsTopics = 0;
if (isset($_GET['pageNum_rsTopics'])) {
  $pageNum_rsTopics = $_GET['pageNum_rsTopics'];
}
$startRow_rsTopics = $pageNum_rsTopics * $maxRows_rsTopics;

$varSectionID_rsTopics = "1";
if (isset($_GET['forumsectionID'])) {
  $varSectionID_rsTopics = $_GET['forumsectionID'];
}
$varUserGroup_rsTopics = "0";
if (isset($_SESSION['MM_UserGroup'])) {
  $varUserGroup_rsTopics = $_SESSION['MM_UserGroup'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsTopics = sprintf("SELECT forumtopic.ID, forumtopic.topic, forumtopic.viewcount, users.firstname, users.surname, COUNT(forumcomment.ID) AS numComments, forumcomment.message AS shortmessage, users.imageURL AS icon, users.ID AS userID, forumtopic.accesslevel, forumcomment.posteddatetime  FROM forumtopic LEFT JOIN forumcomment ON forumcomment.topicID = forumtopic.ID LEFT JOIN users ON (forumcomment.postedbyID = users.ID)  LEFT JOIN forumsection ON (forumtopic.sectionID = forumsection.ID), preferences WHERE forumtopic.statusID = 1 AND forumcomment.statusID = 1 AND forumtopic.sectionID = %s AND (forumsection.accesslevel <=  %s OR forumsection.accesslevel IS NULL) GROUP BY forumtopic.sectionID, forumtopic.ID  ORDER BY forumcomment.posteddatetime DESC", GetSQLValueString($varSectionID_rsTopics, "int"),GetSQLValueString($varUserGroup_rsTopics, "int"));
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
$query_rsPreferences = "SELECT approveforumposts, allowforumjpeg, preferences.forumpublic, preferences.forumsections, preferences.forummoderatorID,  users.firstname, users.surname, forumintrotext, preferences.communityguidelines FROM preferences LEFT JOIN users ON (preferences.forummoderatoriD = users.ID)";
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);

$varUserGroup_rsSections = "0";
if (isset($_SESSION['MM_UserGroup'])) {
  $varUserGroup_rsSections = $_SESSION['MM_UserGroup'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsSections = sprintf("SELECT forumsection.ID, forumsection.sectionname FROM forumsection WHERE forumsection.statusID = 1 AND (forumsection.accesslevel =0 OR forumsection.accesslevel <= %s OR forumsection.ID = 1)", GetSQLValueString($varUserGroup_rsSections, "int"));
$rsSections = mysql_query($query_rsSections, $aquiescedb) or die(mysql_error());
$row_rsSections = mysql_fetch_assoc($rsSections);
$totalRows_rsSections = mysql_num_rows($rsSections);

$varSectionID_rsThisSection = "1";
if (isset($_GET['forumsectionID'])) {
  $varSectionID_rsThisSection = $_GET['forumsectionID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsThisSection = sprintf("SELECT forumsection.sectionname, forumsection.accesslevel, forumsection.statusID, forumsection.moderatorID, users.firstname, users.surname, forumsection.sectiondescription FROM forumsection LEFT JOIN users ON (forumsection.moderatorID = users.ID) WHERE forumsection.ID = %s", GetSQLValueString($varSectionID_rsThisSection, "int"));
$rsThisSection = mysql_query($query_rsThisSection, $aquiescedb) or die(mysql_error());
$row_rsThisSection = mysql_fetch_assoc($rsThisSection);
$totalRows_rsThisSection = mysql_num_rows($rsThisSection);

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
?>
<!doctype html>
<!-- Web design by Paul Egan, Jim Campbell -->
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Forum Topics"; echo $pageTitle." | ".$site_name; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" --><style><!--
<?php if ($row_rsPreferences['forumsections']<1 || $totalRows_rsSections<1) { ?>

.forumsections { display:none; } 

<?php } else { ?>
#forumsectionindex li.forumsection<?php echo intval($_GET['forumsectionID']); ?> a:link, 
#forumsectionindex li.forumsection<?php echo intval($_GET['forumsectionID']); ?> a:visited
{
border-bottom-color:#FFFFFF;
background-color:#FFFFFF;
}
<?php } ?>
--></style>

<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" -->
          <div class="container pageBody">
            <div class="crumbs"><div><span class="you_are_in">You are in: </span><a href="..//index.php">Home</a><span class="separator">&nbsp;&rsaquo;&nbsp;</span><a href="index.php">Discussions</a><span class="forumsections"><span class="separator">&nbsp;&rsaquo;&nbsp;</span><?php echo $row_rsThisSection['sectionname']; ?></span></div></div>
          <?php if (isset($row_rsPreferences['forumintrotext']) && $row_rsPreferences['forumintrotext']!="") {  echo $row_rsPreferences['forumintrotext'];  } ?>
          
<span class="forumsections"><ul id = "forumsectionindex" class="tabs">

            <?php do { ?>
             <li class="forumsection<?php echo $row_rsSections['ID']; ?>"><?php if ($row_rsSections['ID'] == $_GET['forumsectionID']) { ?><a href="javascript:void(0);" ><?php echo $row_rsSections['sectionname']; ?></a><?php  } else {  ?><a href="index.php?forumsectionID=<?php echo $row_rsSections['ID']; ?>"><?php echo $row_rsSections['sectionname']; ?></a><?php } ?></li>
              
              <?php } while ($row_rsSections = mysql_fetch_assoc($rsSections)); ?>
          </ul>
          <h2 class="sectiontitle"><?php echo $row_rsThisSection['sectionname']; ?></h2><?php echo isset($row_rsThisSection['sectiondescription']) ? "<p>".nl2br($row_rsThisSection['sectiondescription'])."</p>": ""; ?></span>

<?php if ((((isset($_SESSION['MM_UserGroup']) && $_SESSION['MM_UserGroup'] >= $row_rsThisSection['accesslevel']) || $row_rsThisSection['accesslevel'] == 0) && $row_rsThisSection['statusID']==1) || $totalRows_rsSections <1) { // allow to view if someone is logged in or forum is public ?>
          <?php if ($totalRows_rsTopics == 0) { // Show if recordset empty ?>
            <p>There are no topics in this <span class="forumsections">section of the </span>forum so far.</p>
            <?php } // Show if recordset empty ?>
			<?php if (isset($_GET['topicadded'])) { ?>
	      <h2><em>Your new topic has been submitted!</em></h2>
		  <?php if ($row_rsPreferences['approveforumposts'] == 1 && $row_rsLoggedIn['usertypeID'] < 8) { //forum posts must be approved ?>
	      <p>It will appear on the forum once it has been approved. </p>
		  <?php }} // end approved ?>
		  <?php if (isset($_SESSION['MM_Username'])) { // Must be logged in to post ?>
		  <p><img src="/core/images/icons/comments_add.png" alt="Add a topic" width="16" height="16" style="vertical-align:
middle;" /> <a href="add_topic.php?forumsectionID=<?php echo (isset($_GET['forumsectionID']) && $_GET['forumsectionID'] >0) ? $_GET['forumsectionID'] : 1; ?>">Suggest a new topic</a> </p>
		  <?php } else { ?>
		 <fieldset><img src="/core/images/icons/comments_add.png" alt="Add a topic" width="16" height="16" style="vertical-align:
middle;" /> <img src="/core/images/icons/lock_go.png" alt="Log in" width="16" height="16" style="vertical-align:
middle;" /> <a href="/login/index.php?accesscheck=<?php echo urlencode("/forum/add_topic.php?".$_SERVER['QUERY_STRING']); ?>" >Log in</a> to start a new topic or, if you're not yet a member, <img src="/core/images/icons/pencil_go.png" alt="Sign up" width="16" height="16" style="vertical-align:
middle;" /> <a href="/login/signup.php?accesscheck=<?php echo urlencode("/forum/add_topic.php?".$_SERVER['QUERY_STRING']); ?>" >sign up now...</a></fieldset>
		  
		  <?php } ?>
<?php if ($totalRows_rsTopics > 0) { // Show if recordset not empty ?>
          <p class="text-muted">Latest topics <?php echo ($startRow_rsTopics + 1) ?> to <?php echo min($startRow_rsTopics + $maxRows_rsTopics, $totalRows_rsTopics) ?> of <?php echo $totalRows_rsTopics ?>
          (most recent first)</p>
          <table  class="table table-hover">
<tbody>
              <?php do { ?>
                  <tr>
                    <td class="top"><a href="/members/profile/profile.php?userID=<?php echo $row_rsTopics['userID']; ?>" class="img" title="<?php echo anonymiser($row_rsTopics['firstname'],$row_rsTopics['surname']); ?> - click for profile">
                <?php if (isset($row_rsTopics['icon']) && $row_rsTopics['icon']!="") { ?>
                
                <div class="fb_avatar" style="background-image:url(<?php echo getImageURL($row_rsTopics['icon'],"thumb"); ?>);"><?php echo $row_rsCatOrganisations['name']; ?></div>
                <?php } else { ?>
                <img src="/members/images/user-anonymous.png" width="48" height="48" />
        <?php } ?></a> </td>
                    <td class="top"><a href="update_topic.php?topicID=<?php echo $row_rsTopics['ID']; ?>"><strong><?php echo htmlentities($row_rsTopics['topic']); ?></strong></a> <?php if ($row_rsTopics['accesslevel'] > 0) { ?><img src="/core/images/icons/lock_delete.png" alt="This topic has restricted access" width="16" height="16" style="vertical-align:
middle;" /><?php } ?><br />
                      <?php echo nl2br(htmlentities(substr($row_rsTopics['shortmessage'],0,strrpos($row_rsTopics['shortmessage']," ")))); ?>...<br /><img src="/core/images/icons/comment_add.png" alt="Read more or add reply" style="vertical-align:
middle;" />&nbsp;<a href="update_topic.php?topicID=<?php echo $row_rsTopics['ID']; ?>"><?php if ($row_rsTopics['numComments'] < 2) { ?>Be the first to comment on this...<?php } else { ?>Read <?php echo $row_rsTopics['numComments']-1; echo $row_rsTopics['numComments'] == 2 ? " response" : " responses"; ?> or add you own comment...<?php } ?></a><br />
                      <span class="text-muted"><?php echo $row_rsTopics['viewcount']; ?> views. Posted <?php echo date ('d M Y',strtotime($row_rsTopics['posteddatetime'])); ?> by <?php echo anonymiser($row_rsTopics['firstname'],$row_rsTopics['surname']); ?></span><br />
                    </td>
                </tr>
                  <?php } while ($row_rsTopics = mysql_fetch_assoc($rsTopics)); ?></tbody>
          </table><p><a href="featured.php">Featured topics</a></p>
            <?php } // Show if recordset not empty ?>
            <table class="form-table">
            <tr>
              <td><?php if ($pageNum_rsTopics > 0) { // Show if not first page ?>
                    <a href="<?php printf("%s?pageNum_rsTopics=%d%s", $currentPage, 0, $queryString_rsTopics); ?>">First</a>
                <?php } // Show if not first page ?>              </td>
             <td><?php if ($pageNum_rsTopics > 0) { // Show if not first page ?>
                    <a href="<?php printf("%s?pageNum_rsTopics=%d%s", $currentPage, max(0, $pageNum_rsTopics - 1), $queryString_rsTopics); ?>" rel="prev">Previous</a>
                <?php } // Show if not first page ?>              </td>
              <td><?php if ($pageNum_rsTopics < $totalPages_rsTopics) { // Show if not last page ?>
                    <a href="<?php printf("%s?pageNum_rsTopics=%d%s", $currentPage, min($totalPages_rsTopics, $pageNum_rsTopics + 1), $queryString_rsTopics); ?>" rel="next">Next</a>
                <?php } // Show if not last page ?>              </td>
              <td><?php if ($pageNum_rsTopics < $totalPages_rsTopics) { // Show if not last page ?>
                    <a href="<?php printf("%s?pageNum_rsTopics=%d%s", $currentPage, $totalPages_rsTopics, $queryString_rsTopics); ?>">Last</a>
              <?php } // Show if not last page ?>              </td>
            </tr>
          </table><?php if($row_rsThisSection['moderatorID']>0) { ?><p>This section is moderated by <?php echo $row_rsThisSection['firstname']; ?> <?php echo $row_rsThisSection['surname']; ?>.</p><?php } else if ($row_rsPreferences['forummoderatorID']>0) { ?><p>This forum is moderated by <?php echo $row_rsPreferences['firstname']; ?> <?php echo $row_rsPreferences['surname']; ?>.</p><?php } else { ?>
		<p>This forum is generally unmoderated.</p>
		<?php } ?>
<p>Individual topics may be moderated separately.</p>
        <?php } else { //no access to forum ?>
		<p><img src="/core/images/warning.gif" alt="Attention!" width="16" height="16" style="vertical-align:
middle;" /> Sorry, you must <a href="/login/index.php" >log in</a> to have access to read and post to the forum.</p>
		<?php } // end no access ?>
       </div>
    <!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsTopics);

mysql_free_result($rsPreferences);

mysql_free_result($rsSections);

mysql_free_result($rsThisSection);
?>

