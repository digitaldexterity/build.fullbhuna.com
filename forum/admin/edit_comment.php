<?php require_once('../../Connections/aquiescedb.php'); ?><?php require_once('../../core/includes/adminAccess.inc.php'); ?><?php require_once('../../core/includes/framework.inc.php'); ?>
<?php require_once('../../core/includes/upload.inc.php'); ?>
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

$colname_rsComment = "-1";
if (isset($_GET['commentID'])) {
  $colname_rsComment = $_GET['commentID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsComment = sprintf("SELECT forumcomment.*, users.email, users.firstname, users.surname, forumtopic.topic, forumcomment.IPaddress FROM forumcomment LEFT JOIN users ON (forumcomment.postedbyID = users.ID) LEFT JOIN forumtopic ON (forumcomment.topicID = forumtopic.ID) WHERE forumcomment .ID = %s", GetSQLValueString($colname_rsComment, "int"));
$rsComment = mysql_query($query_rsComment, $aquiescedb) or die(mysql_error());
$row_rsComment = mysql_fetch_assoc($rsComment);
$totalRows_rsComment = mysql_num_rows($rsComment);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsStatus = "SELECT * FROM status";
$rsStatus = mysql_query($query_rsStatus, $aquiescedb) or die(mysql_error());
$row_rsStatus = mysql_fetch_assoc($rsStatus);
$totalRows_rsStatus = mysql_num_rows($rsStatus);

$topicID = $rsComment['topicID'];

$varTopicID_rsComments = "-1";
if (isset($topicID)) {
  $varTopicID_rsComments = $topicID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsComments = sprintf("SELECT forumcomment.ID, forumcomment.emailme, users.email FROM forumcomment LEFT JOIN users ON (forumcomment.postedbyID = users.ID) WHERE forumcomment.topicID = %s AND forumcomment.statusID = 1", GetSQLValueString($varTopicID_rsComments, "int"));
$rsComments = mysql_query($query_rsComments, $aquiescedb) or die(mysql_error());
$row_rsComments = mysql_fetch_assoc($rsComments);
$totalRows_rsComments = mysql_num_rows($rsComments);

$query = "SELECT * FROM preferences";
$rsPreferences = mysql_query($query, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
// mail topic originator AND comment poster that approval has happened
require('../../mail/includes/sendmail.inc.php');
if ($_POST['statusID'] != $_POST['oldstatusID']) { // status changed
if ($_POST['statusID'] == 1) { // approved
$subject = "Your ".$site_name." discussion comment approved";
$message = "The following comment has been approved:\n";
$message .= $_POST['message']."\n\n";

// email everyone 
 $groupto = "";


//get other emails
do {
	if($row_rsComments['emailme']==1 && $row_rsComments['email'] != $row_rsLoggedIn['email'] && !stristr($groupto,$row_rsComments['email'])) { //mail me, not admin, not the person who has just posted and not already included then add email
	$groupto .= ", ".$row_rsComments['email'];
	} // add email
	
} while ($row_rsComments = mysql_fetch_assoc($rsComments));// end count through posts

$groupto = trim($groupto,",");

if(stristr($groupto,"@")) { // is an address

$subject = "Comment on ".$site_name." discussion topic";
$message = "The following comment has been added to the topic: \"".$row_rsComment['topic']."\"\n";
$message .= $row_rsComment['firstname']." ".substr($row_rsComment['surname'],0,1)." says:\n".$row_rsComment['message']."\n\n";
$message .= "Click on the link below to view this discussion or respond:\n\n";
$message .=  getProtocol()."://". $_SERVER['HTTP_HOST']."/forum/update_topic.php?topicID=".$row_rsComment['topicID']."\n\n";
sendMail("undisclosed-recipients@".$_SERVER['HTTP_HOST'],$subject,$message,"","","","","","",$groupto);
} // is address

} else { //not approved

$subject = "Your ".$site_name." discussion comment NOT approved";
$message = "The following comment has NOT been approved:\n";
$message .= stripslashes($_POST['message'])."\n\n";
$message .= "Posts to this site are moderated. Your post has not been allowed to appear on this site as it breaches the website acceptable usage policy. The moderator's decision is final and no correspondence will be entered in to regarding their decision.";
} // not approved

$to = $row_rsComment['email'];
sendMail($to,$subject,$message);
} // status changed
} // is post

$uploaded = getUploads();
if (isset($uploaded) && is_array($uploaded)) {
	if(isset($uploaded["filename"][0]["newname"]) && $uploaded["filename"][0]["newname"]!="") { 
		$_POST['imageURL'] = $uploaded["filename"][0]["newname"]; 
	}
	$_POST['imageURL'] = (isset($_POST["noImage"])) ? "" : $_POST['imageURL'];
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE forumcomment SET topicID=%s, imageURL=%s, statusID=%s, message=%s, rating=%s, modifiedbyID=%s, modifieddatetime=%s WHERE ID=%s",
                       GetSQLValueString($_POST['topicID'], "int"),
                       GetSQLValueString($_POST['imageURL'], "text"),
                       GetSQLValueString($_POST['statusID'], "int"),
                       GetSQLValueString($_POST['message'], "text"),
                       GetSQLValueString($_POST['rating'], "int"),
                       GetSQLValueString($_POST['postedbyID'], "int"),
                       GetSQLValueString($_POST['posteddatetime'], "date"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());

  $updateGoTo = "edit_topic.php?topicID=" . $row_rsComment['topicID'] . "";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo));
}


?><!doctype html>

<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Forum - Edit Comment"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script src="../../SpryAssets/SpryValidationTextarea.js"></script>
<link href="../../SpryAssets/SpryValidationTextarea.css" rel="stylesheet"  /><script src="/core/scripts/formUpload.js"></script>
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
<h1><i class="glyphicon glyphicon-comment"></i> Edit Comment</h1>
<?php if(isset($submit_error)) { ?><p class="alert alert-danger" role="alert"><?php echo $submit_error; ?></p><?php } ?>
<form action="<?php echo $editFormAction; ?>" method="post" enctype="multipart/form-data" name="form1" id="form1" >      
  
      <table class="form-table">
        
        <tr>        </tr> <tr>
          <td class="text-nowrap text-right top">Topic:</td>
          <td><?php echo $row_rsComment['topic']; ?></td>
        </tr> <tr>
          <td class="text-nowrap text-right top">Message:</td>
          <td><span id="sprytextarea1">
            <textarea name="message" cols="50" rows="5" class="form-control" ><?php echo $row_rsComment['message']; ?></textarea>
            <span class="textareaRequiredMsg">A message is required.</span></span></td>
        </tr>
        <tr>
          <td class="text-nowrap text-right top">Rating:</td>
          <td><label>
            <input  <?php if (!(strcmp($row_rsComment['rating'],"0"))) {echo "checked=\"checked\"";} ?> type="radio" name="rating" value="0" id="rating_0">
            0</label>
            &nbsp;&nbsp;&nbsp;
            <label>
              <input  <?php if (!(strcmp($row_rsComment['rating'],"1"))) {echo "checked=\"checked\"";} ?> type="radio" name="rating" value="1" id="rating_1">
              1</label>
            &nbsp;&nbsp;&nbsp;
            <label>
              <input  <?php if (!(strcmp($row_rsComment['rating'],"2"))) {echo "checked=\"checked\"";} ?> type="radio" name="rating" value="2" id="rating_2">
              2</label>
            &nbsp;&nbsp;&nbsp;
            <label>
              <input  <?php if (!(strcmp($row_rsComment['rating'],"3"))) {echo "checked=\"checked\"";} ?> type="radio" name="rating" value="3" id="rating_3">
              3</label>
            &nbsp;&nbsp;&nbsp;
            <label>
              <input  <?php if (!(strcmp($row_rsComment['rating'],"4"))) {echo "checked=\"checked\"";} ?> type="radio" name="rating" value="4" id="rating_4">
              4</label>
            &nbsp;&nbsp;&nbsp;
            <label>
              <input  <?php if (!(strcmp($row_rsComment['rating'],"5"))) {echo "checked=\"checked\"";} ?> type="radio" name="rating" value="5" id="rating_5">
              5</label>
            &nbsp;&nbsp;&nbsp;
            <label>
              <input  <?php if (!(strcmp($row_rsComment['rating'],"6"))) {echo "checked=\"checked\"";} ?> type="radio" name="rating" value="6" id="rating_6">
              6</label>
            &nbsp;&nbsp;&nbsp;
            <label>
              <input  <?php if (!(strcmp($row_rsComment['rating'],"7"))) {echo "checked=\"checked\"";} ?> type="radio" name="rating" value="7" id="rating_7">
              7</label>
            &nbsp;&nbsp;&nbsp;
            <label>
              <input  <?php if (!(strcmp($row_rsComment['rating'],"8"))) {echo "checked=\"checked\"";} ?> type="radio" name="rating" value="8" id="rating_8">
              8</label>
            &nbsp;&nbsp;&nbsp;
            <label>
              <input  <?php if (!(strcmp($row_rsComment['rating'],"9"))) {echo "checked=\"checked\"";} ?> type="radio" name="rating" value="9" id="rating_9">
              9</label>
            &nbsp;&nbsp;&nbsp;
            <label>
              <input  <?php if (!(strcmp($row_rsComment['rating'],"10"))) {echo "checked=\"checked\"";} ?> type="radio" name="rating" value="10" id="rating_10">
              10</label></td>
        </tr> 
        <tr>
          <td class="text-nowrap text-right">Image:</td>
          <td><?php if (isset($row_rsComment['imageURL'])) { ?>
            <img src="<?php echo getImageURL($row_rsComment['imageURL'],"medium"); ?>" /><br />
            <input name="noImage" type="checkbox" value="1" />
            Remove Image
            <?php } else { ?>
            No image associated with this article story.
            <?php } ?>
            <span class="upload"><br />
              Add/change image below:<br />
              <input name="filename" type="file" id="filename" size="20" /></span>
            <input type="hidden" name="imageURL" value="<?php echo $row_rsComment['imageURL']; ?>" size="32" /></td>
        </tr> <tr>
          <td class="text-nowrap text-right">Posted by:</td>
          <td><?php echo $row_rsComment['firstname']; ?> <?php echo $row_rsComment['surname']; ?> at <?php echo date('g:ia',strtotime($row_rsComment['posteddatetime'])); ?> on <?php echo date('l jS F Y',strtotime($row_rsComment['posteddatetime'])); ?></td>
        </tr> <tr>
          <td class="text-nowrap text-right">Computer ID:</td>
          <td><?php echo isset($row_rsComment['IPaddress']) ? gethostbyaddr($row_rsComment['IPaddress']) : "Not available"; ?></td>
        </tr><tr>
          <td class="text-nowrap text-right">Status:</td>
          <td><select name="statusID" class="form-control">
              <?php 
do {  
?>
              <option value="<?php echo $row_rsStatus['ID']?>" <?php if (!(strcmp($row_rsStatus['ID'], $row_rsComment['statusID']))) {echo "SELECTED";} ?>><?php echo $row_rsStatus['description']?></option>
              <?php
} while ($row_rsStatus = mysql_fetch_assoc($rsStatus));
?>
            </select>
            <input name="oldstatusID" type="hidden" id="oldstatusID" value="<?php echo $row_rsComment['statusID']; ?>" /></td>
        </tr> <tr>
          <td class="text-nowrap text-right">&nbsp;</td>
          <td><div><button type="submit" class="btn btn-primary" >Save changes</button></div></td>
        </tr>
      </table>
      <input type="hidden" name="ID" value="<?php echo $row_rsComment['ID']; ?>" />
      <input type="hidden" name="topicID" value="<?php echo $row_rsComment['topicID']; ?>" />
      <input type="hidden" name="postedbyID" value="<?php echo $row_rsComment['postedbyID']; ?>" />
      <input type="hidden" name="posteddatetime" value="<?php echo $row_rsComment['posteddatetime']; ?>" />
      <input type="hidden" name="MM_update" value="form1" />
      <input type="hidden" name="ID" value="<?php echo $row_rsComment['ID']; ?>" />
      <input name="referer" type="hidden" id="referer" value="<?php echo $_SERVER['HTTP_REFERER']; ?>" />
      
    </form>
    <script>
<!--
var sprytextarea1 = new Spry.Widget.ValidationTextarea("sprytextarea1");
//-->
    </script></div>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsComment);

mysql_free_result($rsStatus);

mysql_free_result($rsComments);
?>