<?php require_once('../Connections/aquiescedb.php'); ?><?php require_once('../core/includes/framework.inc.php'); ?>
<?php require_once('includes/forumfunctions.inc.php'); ?>
<?php require_once('../core/includes/upload.inc.php'); ?>
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


$colname_rsThisTopic = "-1";
if (isset($_GET['topicID'])) {
  $colname_rsThisTopic = $_GET['topicID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsThisTopic = sprintf("SELECT forumtopic.ID, forumtopic.topic, moderators.ID as moderatorID, moderators.email AS modemail, moderators.firstname AS modfirstname, moderators.surname AS modsurname, forumtopic.statusID FROM forumtopic LEFT JOIN users AS moderators ON (forumtopic.moderatorID = moderators.ID) WHERE forumtopic.statusID = 1 AND forumtopic.ID = %s ", GetSQLValueString($colname_rsThisTopic, "int"));
$rsThisTopic = mysql_query($query_rsThisTopic, $aquiescedb) or die(mysql_error());
$row_rsThisTopic = mysql_fetch_assoc($rsThisTopic);
$totalRows_rsThisTopic = mysql_num_rows($rsThisTopic);


$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID, usertypeID, users.firstname, users.surname, users.email FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);


mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = "SELECT preferences.*, users.firstname, users.surname, users.email FROM preferences LEFT JOIN users ON (preferences.forummoderatorID = users.ID)";
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);

if(isset($_GET['pageNum_rsComments'])) $_GET['pageNum_rsComments'] = intval($_GET['pageNum_rsComments']);
if(isset($_GET['totalRows_rsComments'])) $_GET['totalRows_rsComments'] = intval($_GET['totalRows_rsComments']);


$maxRows_rsComments = 20;
$pageNum_rsComments = 0;
if (isset($_GET['pageNum_rsComments'])) {
  $pageNum_rsComments = $_GET['pageNum_rsComments'];
}
$startRow_rsComments = $pageNum_rsComments * $maxRows_rsComments;

$varTopicID_rsComments = "-1";
if (isset($_GET['topicID'])) {
  $varTopicID_rsComments = $_GET['topicID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsComments = sprintf("SELECT forumcomment.ID, forumcomment.posteddatetime, forumcomment.message, forumcomment.emailme, users.firstname, users.surname, forumcomment.imageURL, users.imageURL AS icon, users.dateadded, users.email, users.ID AS userID, forumcomment.postedbyID FROM forumcomment LEFT JOIN users ON (forumcomment.postedbyID = users.ID) WHERE forumcomment.statusID = 1 AND forumcomment.topicID = %s ORDER BY forumcomment.posteddatetime ASC", GetSQLValueString($varTopicID_rsComments, "int"));
$query_limit_rsComments = sprintf("%s LIMIT %d, %d", $query_rsComments, $startRow_rsComments, $maxRows_rsComments);
$rsComments = mysql_query($query_limit_rsComments, $aquiescedb) or die(mysql_error());
$row_rsComments = mysql_fetch_assoc($rsComments);

if (isset($_GET['totalRows_rsComments'])) {
  $totalRows_rsComments = $_GET['totalRows_rsComments'];
} else {
  $all_rsComments = mysql_query($query_rsComments);
  $totalRows_rsComments = mysql_num_rows($all_rsComments);
}
$totalPages_rsComments = ceil($totalRows_rsComments/$maxRows_rsComments)-1;

$varTopicID_rsThisSection = "-1";
if (isset($_GET['topicID'])) {
  $varTopicID_rsThisSection = $_GET['topicID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsThisSection = sprintf("SELECT forumtopic.ID, forumsection.ID AS sectionID, forumsection.sectionname, forumsection.accesslevel, users.firstname, users.surname, users.email FROM forumtopic LEFT JOIN forumsection ON (forumtopic.sectionID = forumsection.ID) LEFT JOIN users ON (forumsection.moderatorID = users.ID) WHERE forumtopic.ID = %s", GetSQLValueString($varTopicID_rsThisSection, "int"));
$rsThisSection = mysql_query($query_rsThisSection, $aquiescedb) or die(mysql_error());
$row_rsThisSection = mysql_fetch_assoc($rsThisSection);
$totalRows_rsThisSection = mysql_num_rows($rsThisSection);
?>
<?php
$queryString_rsComments = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsComments") == false && 
        stristr($param, "totalRows_rsComments") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsComments = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsComments = sprintf("&totalRows_rsComments=%d%s", $totalRows_rsComments, $queryString_rsComments);

if(isset($_SESSION['MM_UserGroup'])) { // ONLY POST IF LOGGED IN
	
	if (isset($_POST["MM_insert"])) {
	require_once('../core/includes/framework.inc.php'); 
	if(containsBannedWords($_POST['message'])) {
		$submit_error = "Your post contains inappropriate content. If you believe this to be incorrect please contact us.";
		unset($_POST["MM_insert"]);
	}
	$_POST['rating'] = isset($_POST['rating']) ? $_POST['rating'] : "";
}


$uploaded = getUploads();
if (isset($uploaded) && is_array($uploaded)) {
	if(isset($uploaded["filename"][0]["newname"]) && $uploaded["filename"][0]["newname"]!="") { 
		$_POST['imageURL'] = $uploaded["filename"][0]["newname"]; 
	}
	$_POST['imageURL'] = (isset($_POST["noimage"])) ? "" : $_POST['imageURL'];
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
  $insertSQL = sprintf("INSERT INTO forumcomment (topicID, imageURL, postedbyID, posteddatetime, statusID, message, IPaddress, emailme, rating) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['topicID'], "int"),
                       GetSQLValueString($_POST['imageURL'], "text"),
                       GetSQLValueString($_POST['postedbyID'], "int"),
                       GetSQLValueString($_POST['posteddatetime'], "date"),
                       GetSQLValueString($_POST['statusID'], "int"),
                       GetSQLValueString($_POST['message'], "text"),
                       GetSQLValueString($_POST['IPaddress'], "text"),
                       GetSQLValueString(isset($_POST['emailme']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['rating'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($insertSQL, $aquiescedb) or die(mysql_error());
}

if (isset($_POST["MM_insert"]) && $_POST["MM_insert"] == "form1") { 
// Jiggery pokery
// insert and mail must go after preferences and topic to pick up email addresses


require_once('../mail/includes/sendmail.inc.php');
if (isset($row_rsThisTopic['modemail'])) { $to = $row_rsThisTopic['modemail']; }
else if (isset($row_rsThisSection['email'])) { $to = $row_rsThisSection['email']; }
else if (isset($row_rsPreferences['email'])) { $to = $row_rsPreferences['email']; }
else { $to = $row_rsPreferences['contactemail']; }
$subject = "Comment added to web site forum";
$message = "The following comment has been posted to the topic '".$row_rsThisTopic['topic']."'\n";
$message .= ($row_rsPreferences['approveforumposts'] == 1) ? "This post will need to be approved by you before it is displayed on the site.\n\n" : "This post now appears live on the discussion forum.\n\n";
$message .= $row_rsLoggedIn['firstname']." ".$row_rsLoggedIn['surname']." says:\n".$_POST['message']."\n\n";
sendMail($to,$subject,$message);


if ($row_rsPreferences['approveforumposts'] != 1) { 
// mail original poster if asked for AND if approve posts is off AND not admin (avoid duplicate mail) ,otherwise added to admin side
    $groupto = "";
if(isset($row_rsThisTopic['email']) && $row_rsThisTopic['mailme']==1 && $row_rsThisTopic['email'] != $to) {
	$groupto .= ", ". $row_rsThisTopic['email'];
}

//get other emails
do {
	if($row_rsComments['emailme']==1 && $row_rsComments['email'] != $to && $row_rsComments['email'] != $row_rsLoggedIn['email'] && !stristr($groupto,$row_rsComments['email'])) { //mail me, not admin, not the person who has just posted and not already included then add email
	$groupto .= ", ".$row_rsComments['email'];
	} // add email
	
} while ($row_rsComments = mysql_fetch_assoc($rsComments));// end count through posts

$groupto = trim($groupto,",");

if(stristr($groupto,"@")) { // is address

$subject = "Comment on ".$site_name." discussion topic";
$message = "The following comment has been added to the topic: \"".$row_rsThisTopic['topic']."\"\n";
$message .= $row_rsLoggedIn['firstname']." ".substr($row_rsLoggedIn['surname'],0,1)." says:\n".stripslashes($_POST['message'])."\n\n";
$message .= "Click on the link below to view this  discussion or respond:\n\n";
$message .=  getProtocol()."://". $_SERVER['HTTP_HOST']."/forum/update_topic.php?topicID=".$row_rsThisTopic['ID']."\n\n";
sendMail("undisclosed-recipients@".$_SERVER['HTTP_HOST'],$subject,$message,"","","","","","",$groupto);
} // is address
} // end approved posts


$insertGoTo = "update_topic.php?comment=true";
  if (isset($_SERVER['QUERY_STRING'])) {
    $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
    $insertGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $insertGoTo));exit;
}  
} // end logged in

// view count - could be made more secure by adding IP check

$update = "UPDATE forumtopic SET viewcount = viewcount+1 WHERE ID = ".$row_rsThisTopic['ID'];
 $result = mysql_query($update, $aquiescedb) or die(mysql_error());

?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Forum Topic - ".$row_rsThisTopic['topic']; echo $pageTitle." | ".$site_name; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<style><!--
<?php if ($row_rsPreferences['forumsections']!=1) { ?>

.forumsections {
display:none;
}

<?php } ?>
<?php if(!isset($_GET['productID'])) { echo ".rating { display:none; }"; } ?>

--></style>
<script src="/SpryAssets/SpryValidationTextarea.js"></script>
<link href="/SpryAssets/SpryValidationTextarea.css" rel="stylesheet"  />
<script src="/core/scripts/formUpload.js"></script>
<script src="/core/scripts/ratings/script.js"></script>
<link href="/core/scripts/ratings/stars.css" rel="stylesheet"  />
<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" -->
        <div class="container pageBody">
	<?php if ($row_rsThisTopic['statusID'] == 1 && isset($_SESSION['MM_UserGroup']) && $_SESSION['MM_UserGroup']>0 &&
	
	($_SESSION['MM_UserGroup'] >=$row_rsThisSection['accesslevel'] || $row_rsThisSection['accesslevel'] ==0 || !isset($row_rsThisSection['accesslevel']))
	
	
	) { // OK to access ?>
	
	  <div class="crumbs"><div><span class="you_are_in">You are in: </span><a href="../index.php">Home</a><span class="separator">&nbsp;&rsaquo;&nbsp;</span><a href="index.php">Discussions</a><span class="forumsections"><span class="separator">&nbsp;&rsaquo;&nbsp;</span><a href="index.php?forumsectionID=<?php echo $row_rsThisSection['sectionID']; ?>"><?php echo $row_rsThisSection['sectionname']; ?></a></span><span class="separator">&nbsp;&rsaquo;&nbsp;</span><?php echo htmlentities($row_rsThisTopic['topic']); ?></div></div>
    <h2><?php echo htmlentities($row_rsThisTopic['topic']); ?></h2>
    
    <?php if (isset($_GET['comment'])) { ?>
              <p class="message alert alert-info" role="alert">Your comment has been received!
              <?php if ($row_rsPreferences['approveforumposts'] == 1 && $row_rsLoggedIn['usertypeID'] < 8) { //forum posts must be approved ?>
              It will appear on the forum once it has been approved. 
              <?php } // end needs  approved 
			  else { ?>It has been added below.
             
                <?php } // end not need approved ?></p>
				<?php } // end comment post
				?>
              
              <?php if ($totalRows_rsComments == 1) { // Show if recordset empty ?>
              <p>Be the first to reply to this topic...</p>
              <?php } // Show if recordset empty ?>
<?php if (!isset($_GET['comment'])) { ?>
              
              <p><img src="../core/images/icons/comment_add.png" alt="Add comment below" width="16" height="16" style="vertical-align:
middle;" /> <a href="#add_comment">Add reply below</a></p>
              <?php } ?>
<?php if ($totalRows_rsComments > 0) { // Show if recordset not empty ?>
              <p class="text-muted">Comments <?php echo ($startRow_rsComments + 1) ?> to <?php echo min($startRow_rsComments + $maxRows_rsComments, $totalRows_rsComments) ?> of <?php echo $totalRows_rsComments ?> (chronological order) </p>
              <table  class="table table-hover">
              <tbody>
                <?php do { ?>
                <tr>
                  <td width="48" valign="top"><a href="/members/profile/index.php?userID=<?php echo $row_rsComments['userID']; ?>" class="img" >
                    <?php if (isset($row_rsComments['icon']) && $row_rsComments['icon']!="") { ?>
                    <div class="fb_avatar" style="background-image:url(<?php echo getImageURL($row_rsComments['icon'],"thumb"); ?>);"><?php echo $row_rsComments['firstname']." ".$row_rsComments['surname']; ?> - click for profile</div>
                    
                   
                    <?php } else { ?>
                    <img src="/members/images/user-anonymous.png" alt="<?php echo $row_rsComments['firstname']." ".$row_rsComments['surname']; ?> - click for profile" width="48" height="48" class="square" />
                    <?php } ?>
                  </a></td>
                  <td class="top"><?php if (isset($row_rsComments['imageURL'])) { ?>
                 
                      
                      
                      <div class="fb_avatar" style="background-image:url(<?php echo getImageURL($row_rsComments['imageURL'],"thumb"); ?>);"><?php echo anonymiser($row_rsComments['firstname'],$row_rsComments['surname']); ?></div>
                <?php } else { ?>
                <img src="/members/images/user-anonymous.png" width="48" height="48" /><?php } ?>
                    <strong  class="postername"><?php echo anonymiser($row_rsComments['firstname'],$row_rsComments['surname']); ?> says:</strong><br />
<?php echo htmlentities(nl2br($row_rsComments['message'])); ?><br />                      
                  <span class="text-muted">Posted at <?php echo date('g:i a',strtotime($row_rsComments['posteddatetime'])); ?> on <?php echo date('l jS F Y',strtotime($row_rsComments['posteddatetime'])); ?></span> <a href="#add_comment"><img src="../core/images/icons/comment_add.png" alt="Add comment below" width="16" height="16" style="vertical-align:
middle;" /></a><?php if ($row_rsPreferences['approveforumposts']!=1 && isset($_SESSION['MM_Username']) && $row_rsLoggedIn['ID'] != $row_rsComments['postedbyID'] && $row_rsPreferences['allowforumflagreview']==1) { // if posts not approved already and logged in show review icon ?> <a href="review.php?forumcommentID=<?php echo $row_rsComments['ID']; ?>" title="Mark this comment for moderator review (will not show until reviewed)" onClick="return confirm('You have clicked this post for moderator review. This post will not display until it has been reviewed and reinstated or deleted. You must be logged in to mark this post and you must also give a valid reason for review.\n\nDo you wish to continue?')"><img src="../core/images/icons/comment_delete.png" alt="Mark this comment for moderator review (it will not display again until reviewed)" width="16" height="16" style="vertical-align:
middle;" /></a>
                  <?php } else if ($row_rsLoggedIn['ID'] == $row_rsComments['postedbyID']) { // posted by logged in user ?> <a href="delete.php?forumtopicID=<?php echo intval($_GET['topicID']); ?>&forumcommentID=<?php echo $row_rsComments['ID']; ?>" title="Delete your post" onClick="return confirm('This is your post. By clicking here you will delete it.\n\nDo you wish to continue?')"><img src="../core/images/icons/comment_delete.png" alt="Delete post" width="16" height="16" style="vertical-align:
middle;" /></a><?php } ?><br />                        
                  <br />                    </td>
                </tr>
                <?php } while ($row_rsComments = mysql_fetch_assoc($rsComments)); ?></tbody>
              </table>
              <table class="form-table">
                <tr>
      <td><?php if ($pageNum_rsComments > 0) { // Show if not first page ?>
                      <a href="<?php printf("%s?pageNum_rsComments=%d%s", $currentPage, 0, $queryString_rsComments); ?>">First</a>
                      <?php } // Show if not first page ?>                  </td>
     <td><?php if ($pageNum_rsComments > 0) { // Show if not first page ?>
                      <a href="<?php printf("%s?pageNum_rsComments=%d%s", $currentPage, max(0, $pageNum_rsComments - 1), $queryString_rsComments); ?>" rel="prev">Previous</a>
                      <?php } // Show if not first page ?>                  </td>
      <td><?php if ($pageNum_rsComments < $totalPages_rsComments) { // Show if not last page ?>
                      <a href="<?php printf("%s?pageNum_rsComments=%d%s", $currentPage, min($totalPages_rsComments, $pageNum_rsComments + 1), $queryString_rsComments); ?>" rel="next">Next</a>
                      <?php } // Show if not last page ?>                  </td>
      <td><?php if ($pageNum_rsComments < $totalPages_rsComments) { // Show if not last page ?>
                      <a href="<?php printf("%s?pageNum_rsComments=%d%s", $currentPage, $totalPages_rsComments, $queryString_rsComments); ?>">Last</a>
                      <?php } // Show if not last page ?>                  </td>
                </tr>
              </table>
<?php } // Show if recordset not empty ?>
              <h2>
          <a name="add_comment" id="add_comment"></a>Add Reply</h2>

<?php if (isset($_SESSION['MM_Username'])) { // Must be logged in to post ?>
<?php if(isset($submit_error)) { ?><p class="alert alert-danger" role="alert"><?php echo $submit_error; ?></p><?php } ?>
<form action="<?php echo $editFormAction; ?>" method="post" enctype="multipart/form-data" name="form1" id="form1" >           
  <table class="form-table">
           <tr class="rating">
                        <td class="text-nowrap text-right">Rating:</td>
                        <td><div class="stars">
  <label><input id="rating-1" name="rating" type="radio" value="1"/>1</label>
  <label><input id="rating-2" name="rating" type="radio" value="2"/>2</label>
  <label><input id="rating-3" name="rating" type="radio" value="3"/>3</label>
  <label><input id="rating-4" name="rating" type="radio" value="4"/>4</label>
    <label><input id="rating-5" name="rating" type="radio" value="5"/>5</label>
  <label><input id="rating-6" name="rating" type="radio" value="6"/>6</label>
  <label><input id="rating-7" name="rating" type="radio" value="7"/>7</label>
  <label><input id="rating-8" name="rating" type="radio" value="8"/>8</label>
    <label><input id="rating-9" name="rating" type="radio" value="9"/>9</label>
  <label><input id="rating-10" name="rating" type="radio" value="10"/>10</label>
  

</div></td>
        </tr> <tr>
              <td class="text-nowrap text-right top">Comment:</td>
              <td><span id="sprytextarea1">
                <textarea name="message" cols="50" rows="5"></textarea>
              <span class="textareaRequiredMsg">A message is required.</span></span></td>
            </tr>
			          <?php if ($row_rsPreferences['allowforumjpeg'] == 1) { //allow images ?>

            
            <tr class="upload">
              <td class="text-nowrap text-right">Optional image:</td>
              <td><input name="filename" type="file" id="filename" size="20" /></td>
</tr> <tr>
              <td class="text-nowrap text-right">Email me:</td>
              <td><label>
                <input name="emailme" type="checkbox" id="emailme" value="1" />
              (when someone else posts to this topic)</label></td>
            </tr>		  
        <?php } //end allow images ?> <tr>
              <td class="text-nowrap text-right">&nbsp;</td>
              <td><input type="submit" class="button" value="Add comment" /></td>
        </tr>
  </table><input name="imageURL" type="hidden" id="imageURL" /><input name="IPaddress" type="hidden" id="IPaddress" value="<?php echo getClientIP(); ?>" />
          <input type="hidden" name="topicID" value="<?php echo $row_rsThisTopic['ID']; ?>" />
          <input type="hidden" name="postedbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
          <input type="hidden" name="posteddatetime" value="<?php echo date ('Y-m-d H:i:s'); ?>" />
         <?php if ($row_rsPreferences['approveforumposts'] == 1 && $row_rsLoggedIn['usertypeID'] < 8) { //forum posts must be approved ?>
          
          <p>NOTE: All forum posts will be reviewed first before they are displayed on the site.</p><input type="hidden" name="statusID" value="0" />
          <?php } else { ?><input type="hidden" name="statusID" value="1" />
          <?php } ?>
          <input type="hidden" name="MM_insert" value="form1" />
    </form>
        <?php } else { ?>
        <fieldset><img src="/core/images/icons/comment_add.png" alt="Add comment" width="16" height="16" style="vertical-align:
middle;" /> <img src="/core/images/icons/lock_go.png" alt="Log in" width="16" height="16" style="vertical-align:
middle;" /> <a href="/login/index.php?accesscheck=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" >Log in</a> to reply or, if you're not yet a member, <img src="/core/images/icons/pencil_go.png" alt="Sign up" width="16" height="16" style="vertical-align:
middle;" /> <a href="/login/signup.php?accesscheck=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" >sign up and reply...</a></fieldset>
<?php } ?>
       
		<?php } // end OK to access
		else { ?>
	<p><img src="/core/images/warning.gif" alt="Attention!" width="16" height="16" style="vertical-align:
middle;" /> Either this topic has been disabled or you do not have access rights to this topic.  </p>
        
        <?php } ?> <p><a href="index.php" class="link_back"><i class="glyphicon glyphicon-arrow-left"></i> Back to topics</a></p>
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
mysql_free_result($rsThisTopic);

mysql_free_result($rsComments);

mysql_free_result($rsThisSection);

mysql_free_result($rsLoggedIn);

mysql_free_result($rsPreferences);
?>