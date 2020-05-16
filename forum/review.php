<?php require_once('../Connections/aquiescedb.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
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

$MM_restrictGoTo = "../login/index.php?notloggedin=true";
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

if (isset($_POST['userID'])) { // something posted
mysql_select_db($database_aquiescedb, $aquiescedb);
// get moedrators email address
$query = "SELECT email FROM users WHERE ID = ".GetSQLValueString($_POST['moderatorID'], "int");
$result = mysql_query($query, $aquiescedb) or die(mysql_error());  
$row = mysql_fetch_assoc($result);

$update = "UPDATE forumcomment SET statusID = 0 WHERE ID = ".GetSQLValueString($_POST['forumcommentID'],"int");
$editaddress = $_SERVER['HTTP_HOST']."/forum/admin/edit_comment.php?commentID=".$_POST['forumcommentID'];
$redirectURL = "update_topic.php?topicID=".$_POST['forumtopicID'];

$result = mysql_query($update, $aquiescedb) or die(mysql_error());  
if (isset($row['email'])) { // moderator email address exists
$query = "SELECT * FROM preferences";
$rsPreferences = mysql_query($query, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$to = $row['email'];
$from = $_POST['email'];
$subject = "Discussion Forum Comment Flagged for Review";
$message = "The following discussion forum comment has been flagged for review by ".$_POST['firstname']." ".$_POST['surname'].":\n\n";
$message .= $_POST['message']."\n\n";
$message .= "The reason given was:\n\n";
$message .= $_POST['reason']."\n\n";
$message .= "This status of this comment has been change to 'pending'. Please click on the link below to either reject, reinstate or edit this forum comment.\n\n";
$message .= $editaddress;
require_once('../mail/includes/sendmail.inc.php');
sendMail($to,$subject,$message);
}
header("Location: ".$redirectURL); exit;
}

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID, firstname, surname, email FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);



$colname_rsComment = "-1";
if (isset($_GET['forumcommentID'])) {
  $colname_rsComment = $_GET['forumcommentID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsComment = sprintf("SELECT forumcomment.ID, forumcomment.imageURL, forumcomment.topicID, forumcomment.message, forumtopic.moderatorID AS topicmoderatorID, forumsection.moderatorID AS sectionmoderatorID FROM forumcomment LEFT JOIN forumtopic ON (forumcomment.topicID = forumtopic.ID) LEFT JOIN forumsection ON (forumtopic.sectionID = forumsection.ID) WHERE forumcomment.ID = %s", GetSQLValueString($colname_rsComment, "int"));
$rsComment = mysql_query($query_rsComment, $aquiescedb) or die(mysql_error());
$row_rsComment = mysql_fetch_assoc($rsComment);
$totalRows_rsComment = mysql_num_rows($rsComment);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsForumModerator = "SELECT forummoderatorID FROM preferences";
$rsForumModerator = mysql_query($query_rsForumModerator, $aquiescedb) or die(mysql_error());
$row_rsForumModerator = mysql_fetch_assoc($rsForumModerator);
$totalRows_rsForumModerator = mysql_num_rows($rsForumModerator);
?><?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Flag for review"; echo $pageTitle." | ".$site_name; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script src="../SpryAssets/SpryValidationTextarea.js"></script>
<link href="../SpryAssets/SpryValidationTextarea.css" rel="stylesheet"  />
<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" -->
        <div class="container pageBody">
    <h1>Flag for Review</h1>
    <p>We take abuse of this discussion forum very seriously and, in addition to reviewing the content on a regular basis, we rely on our users to help us keep inappropriate comments off this site.</p>
    <p>If you flag this comment as inappropriate and it will be automatically removed from the site until it is reviewed by a moderator who will make a final decision on whether to include it or not. This may amount to displaying an edited version of the comment.</p>
    <p>Consistent abusers of the discussion forum will be banned. In addition, users who continually flag for review other users' comments without good reason will also be banned.</p>
    <h2>Flag the following comment for review:</h2>
    <p><?php echo htmlentities(nl2br($row_rsTopic['message'])); ?><br /><?php echo htmlentities(nl2br($row_rsComment['message'])); ?></p>
    <p><strong>Please give a valid reason why you (<?php echo $row_rsLoggedIn['firstname']; ?> <?php echo $row_rsLoggedIn['surname']; ?>) think this post is inappropriate for this forum:</strong></p>
    <form action="review.php" method="post" name="form1" id="form1">
      <label for="reason"></label>
      <p><span id="sprytextarea1">
        <textarea name="reason" id="reason" cols="45" rows="5"></textarea>
      <span class="textareaRequiredMsg">A reason is required.</span></span></p>
      <p>
        <label for="submit"></label>
        <input type="submit" class="button" value="Flag for review" />
        <input name="forumcommentID" type="hidden" id="forumcommentID" value="<?php echo $row_rsComment['ID']; ?>" />
        <input name="userID" type="hidden" id="userID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
        <input name="firstname" type="hidden" id="firstname" value="<?php echo $row_rsLoggedIn['firstname']; ?>" />
        <input name="surname" type="hidden" id="surname" value="<?php echo $row_rsLoggedIn['surname']; ?>" />
        <input name="email" type="hidden" id="email" value="<?php echo $row_rsLoggedIn['email']; ?>" />
        <input type="hidden" name="message" id="message" value = "<?php echo isset($row_rsTopic['message']) ? $row_rsTopic['message'] : $row_rsComment['message'] ; ?>" />
        <input type="hidden" name="referer" id="referer" value="<?php echo $_SERVER['HTTP_REFERER']; ?>" />
        <input type="hidden" name="moderatorID" id="moderatorID" value= "<?php if($row_rsComment['topicmoderatorID'] > 0) { echo $row_rsComment['topicmoderatorID']; } else if ($row_rsTopic['topicmoderatorID']>0 ) { echo $row_rsTopic['topicmoderatorID']; } else if($row_rsComment['sectionmoderatorID'] > 0) { echo $row_rsComment['sectionmoderatorID']; } else if ($row_rsTopic['sectionmoderatorID']>0 ) { echo $row_rsTopic['sectionmoderatorID']; } else echo $row_rsForumModerator['forummoderatorID']; ?>"/>
      </p>
    </form>
   
    <script>
<!--
var sprytextarea1 = new Spry.Widget.ValidationTextarea("sprytextarea1");
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
mysql_free_result($rsLoggedIn);

mysql_free_result($rsTopic);

mysql_free_result($rsComment);

mysql_free_result($rsForumModerator);
?>
