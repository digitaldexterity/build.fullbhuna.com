<?php require_once('../Connections/aquiescedb.php'); ?>
<?php
if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{
  $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;

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
      $theValue = ($theValue != "") ? "'" . doubleval($theValue) . "'" : "NULL";
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

$colname_rsCommentPoster = "-1";
if (isset($_GET['forumcommentID'])) {
  $colname_rsCommentPoster = $_GET['forumcommentID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsCommentPoster = sprintf("SELECT postedbyID FROM forumcomment WHERE ID = %s", GetSQLValueString($colname_rsCommentPoster, "int"));
$rsCommentPoster = mysql_query($query_rsCommentPoster, $aquiescedb) or die(mysql_error());
$row_rsCommentPoster = mysql_fetch_assoc($rsCommentPoster);
$totalRows_rsCommentPoster = mysql_num_rows($rsCommentPoster);

$colname_rsTopicPoster = "-1";
if (isset($_GET['forumtopicID'])) {
  $colname_rsTopicPoster = $_GET['forumtopicID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsTopicPoster = sprintf("SELECT postedbyID FROM forumtopic WHERE ID = %s", GetSQLValueString($colname_rsTopicPoster, "int"));
$rsTopicPoster = mysql_query($query_rsTopicPoster, $aquiescedb) or die(mysql_error());
$row_rsTopicPoster = mysql_fetch_assoc($rsTopicPoster);
$totalRows_rsTopicPoster = mysql_num_rows($rsTopicPoster);
?>
<?php 

if (isset($_GET['forumcommentID'])) { // is a comment to delete
// check if it poster that is logged in
if ($row_rsCommentPoster['postedbyID'] == $row_rsLoggedIn['ID']) { // it is, so delete post and return to topic
$update = "UPDATE forumcomment SET statusID = 3 WHERE ID = ".GetSQLValueString($_GET['forumcommentID'],"int");
$result = mysql_query($update, $aquiescedb) or die(mysql_error());
header("Location: update_topic.php?topicID=".intval($_GET['forumtopicID'])); exit;
} // end is poster logged in
else { header("Location: /login/index.php?notloggedin=true"); exit; }
} // end is comment
else { // must be topic if not comment
// check if it poster that is logged in
if ($row_rsTopicPoster['postedbyID'] == $row_rsLoggedIn['ID']) { // it is, so delete post and return to topics page
$update = "UPDATE forumtopic SET statusID = 3 WHERE ID = ".GetSQLValueString($_GET['forumtopicID'],"int");
$result = mysql_query($update, $aquiescedb) or die(mysql_error());
header("Location: index.php"); exit;
} // end is poster
else { header("Location: /login/index.php?notloggedin=true"); exit; }
} // end topic
?>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsCommentPoster);

mysql_free_result($rsTopicPoster);
?>