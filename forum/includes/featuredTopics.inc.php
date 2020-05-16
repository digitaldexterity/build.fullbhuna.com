<?php 
// select latest
// select most read
// then an editors pick (that's not one of the above if possible)
// then output as latest, editors pick, most read, recent
if(is_readable('../../Connections/aquiescedb.php')) {
	require_once('../../Connections/aquiescedb.php'); 
	 }?>
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
 
$varUserGroup_rsTopics = "0";
if (isset($_SESSION['MM_UserGroup'])) {
  $varUserGroup_rsTopics = $_SESSION['MM_UserGroup'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsTopics = sprintf("SELECT forumtopic.ID, forumtopic.topic, forumtopic.viewcount ,forumtopic.posteddatetime, users.firstname, users.surname, COUNT(forumcomment.ID) AS numComments,  forumtopic.message, users.imageURL AS icon, users.ID AS userID FROM forumtopic LEFT JOIN forumsection ON (forumtopic.sectionID = forumsection.ID) LEFT JOIN users ON (forumtopic.postedbyID = users.ID) LEFT JOIN forumcomment ON (forumcomment.topicID = forumtopic.ID), preferences WHERE forumtopic.statusID = 1 AND forumsection.statusID = 1 AND (forumsection.accesslevel = 0 OR forumsection.accesslevel <= %s) GROUP BY forumtopic.sectionID, forumtopic.ID ORDER BY posteddatetime DESC LIMIT 6", GetSQLValueString($varUserGroup_rsTopics, "int"));
$rsTopics = mysql_query($query_rsTopics, $aquiescedb) or die(mysql_error());
$row_rsTopics = mysql_fetch_assoc($rsTopics);
$totalRows_rsTopics = mysql_num_rows($rsTopics);

$varUserGroup_rsMostRead = "0";
if (isset($_SESSION['MM_UserGroup'])) {
  $varUserGroup_rsMostRead = $_SESSION['MM_UserGroup'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsMostRead = sprintf("SELECT forumtopic.ID, forumtopic.topic, forumtopic.viewcount ,forumtopic.posteddatetime, users.firstname, users.surname, COUNT(forumcomment.ID) AS numComments,  forumtopic.message, users.imageURL AS icon, users.ID AS userID FROM forumtopic LEFT JOIN forumsection ON (forumtopic.sectionID = forumsection.ID) LEFT JOIN users ON (forumtopic.postedbyID = users.ID) LEFT JOIN forumcomment ON (forumcomment.topicID = forumtopic.ID), preferences WHERE forumtopic.statusID = 1 AND forumsection.statusID = 1 AND (forumsection.accesslevel = 0 OR forumsection.accesslevel <= %s) GROUP BY forumtopic.sectionID, forumtopic.ID ORDER BY viewcount DESC LIMIT 1", GetSQLValueString($varUserGroup_rsMostRead, "int"));
$rsMostRead = mysql_query($query_rsMostRead, $aquiescedb) or die(mysql_error());
$row_rsMostRead = mysql_fetch_assoc($rsMostRead);
$totalRows_rsMostRead = mysql_num_rows($rsMostRead);


$varUserGroup_rsEditorsPick = "0";
if (isset($_SESSION['MM_UserGroup'])) {
  $varUserGroup_rsEditorsPick = $_SESSION['MM_UserGroup'];
}
$varMostRead_rsEditorsPick = "-1";
if (isset($mostread)) {
  $varMostRead_rsEditorsPick = $rsMostRead['ID'];
}
$varLatest_rsEditorsPick = "-1";
if (isset($latest)) {
  $varLatest_rsEditorsPick = $row_rsTopics['ID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsEditorsPick = sprintf("SELECT forumtopic.ID, forumtopic.topic, forumtopic.viewcount ,forumtopic.posteddatetime, users.firstname, users.surname, COUNT(forumcomment.ID) AS numComments,  forumtopic.message, users.imageURL AS icon, users.ID AS userID FROM forumtopic LEFT JOIN forumsection ON (forumtopic.sectionID = forumsection.ID) LEFT JOIN users ON (forumtopic.postedbyID = users.ID) LEFT JOIN forumcomment ON (forumcomment.topicID = forumtopic.ID), preferences WHERE forumtopic.editorpick = 1 AND forumtopic.ID != %s AND forumtopic.ID != %s AND forumtopic.statusID = 1 AND forumsection.statusID = 1 AND (forumsection.accesslevel = 0 OR forumsection.accesslevel <= %s) GROUP BY forumtopic.sectionID, forumtopic.ID ORDER BY posteddatetime DESC LIMIT 1", GetSQLValueString($varMostRead_rsEditorsPick, "int"),GetSQLValueString($varLatest_rsEditorsPick, "int"),GetSQLValueString($varUserGroup_rsEditorsPick, "int"));
$rsEditorsPick = mysql_query($query_rsEditorsPick, $aquiescedb) or die(mysql_error());
$row_rsEditorsPick = mysql_fetch_assoc($rsEditorsPick);
$totalRows_rsEditorsPick = mysql_num_rows($rsEditorsPick);

if($totalRows_rsTopics>0) {

?>
<div class="forumtopic">
         <div class="forumtopictitle"><a href="/forum/update_topic.php?topicID=<?php echo $row_rsTopics['ID']; ?>" class="link_comment"><span id="forumlatest">Latest:</span> <?php echo htmlentities($row_rsTopics['topic']); ?></a></div>
          <?php echo htmlentities(nl2br($row_rsTopics['message'])); ?>...<br />
          <span class="text-muted">Posted <?php echo date('d M Y',strtotime($row_rsTopics['posteddatetime'])); ?>, <?php echo $row_rsTopics['viewcount']; ?> views</span> <a href="/forum/update_topic.php?topicID=<?php echo $row_rsTopics['ID']; ?>" title="<?php echo $row_rsTopics['topic']; ?>">Read or discuss...</a></div>
           <?php
		  if(isset($row_rsEditorsPick['ID']) && $row_rsEditorsPick['ID'] != $row_rsTopics['ID']) { // if editors pick is not latest ?>
          <div class="forumtopic">
         <div class="forumtopictitle"><a href="/forum/update_topic.php?topicID=<?php echo $row_rsEditorsPick['ID']; ?>" class="link_comment"><span id="forumeditorpick">Editor&#8217;s choice:</span> <?php echo htmlentities($row_rsEditorsPick['topic']); ?></a></div>
          <?php echo htmlentities(nl2br($row_rsEditorsPick['message'])); ?>...<br />
          <span class="text-muted">Posted <?php echo date('d M Y',strtotime($row_rsEditorsPick['posteddatetime'])); ?>, <?php echo $row_rsEditorsPick['viewcount']; ?> views</span> <a href="/forum/update_topic.php?topicID=<?php echo $row_rsEditorsPick['ID']; ?>" title="<?php echo $row_rsEditorsPick['topic']; ?>">Read or discuss...</a></div>
          <?php }
          
		  if($row_rsMostRead['ID'] != $row_rsTopics['ID']) { // if most viewed is not latest ?>
          <div class="forumtopic">
         <div class="forumtopictitle"><a href="/forum/update_topic.php?topicID=<?php echo $row_rsMostRead['ID']; ?>" class="link_comment"><span id="forummostread">Most viewed:</span> <?php echo htmlentities($row_rsMostRead['topic']); ?></a></div>
          <?php echo htmlentities(nl2br($row_rsMostRead['message'])); ?>...<br />
          <span class="text-muted">Posted <?php echo date('d M Y',strtotime($row_rsTopics['posteddatetime'])); ?>, <?php echo $row_rsMostRead['viewcount']; ?> views</span> <a href="/forum/update_topic.php?topicID=<?php echo $row_rsMostRead['ID']; ?>" title="<?php echo $row_rsMostRead['topic']; ?>">Read or discuss...</a></div>
          <?php } 

if($row_rsTopics = mysql_fetch_assoc($rsTopics)) { // any more?

 do {  if($row_rsTopics['ID'] != $row_rsMostRead['ID'] && $row_rsTopics['ID'] != $row_rsEditorsPick['ID']) { //not above  ?>

        <div class="forumtopic">
         <div class="forumtopictitle"><a href="/forum/update_topic.php?topicID=<?php echo $row_rsTopics['ID']; ?>" class="link_comment"><?php echo htmlentities($row_rsTopics['topic']); ?></a></div>
          <?php echo htmlentities(nl2br($row_rsTopics['message'])); ?>...<br />
          <span class="text-muted">Posted <?php echo date('d M Y',strtotime($row_rsTopics['posteddatetime'])); ?>, <?php echo $row_rsTopics['viewcount']; ?> views</span> <a href="/forum/update_topic.php?topicID=<?php echo $row_rsTopics['ID']; ?>" title="<?php echo $row_rsTopics['topic']; ?>">Read or discuss...</a></div>
        <?php } // not above
		} while ($row_rsTopics = mysql_fetch_assoc($rsTopics));
} // end any more
} // end is topics
		
		mysql_free_result($rsTopics);

mysql_free_result($rsMostRead);

mysql_free_result($rsEditorsPick);
		
		?>