<?php require_once('../../Connections/aquiescedb.php'); ?><?php require_once('../../core/includes/adminAccess.inc.php'); ?><?php require_once('../../core/includes/upload.inc.php'); ?>
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

$colname_rsTopic = "-1";
if (isset($_GET['topicID'])) {
  $colname_rsTopic = $_GET['topicID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsTopic = sprintf("SELECT forumtopic.* FROM forumtopic  WHERE forumtopic.ID = %s", GetSQLValueString($colname_rsTopic, "int"));
$rsTopic = mysql_query($query_rsTopic, $aquiescedb) or die(mysql_error());
$row_rsTopic = mysql_fetch_assoc($rsTopic);
$totalRows_rsTopic = mysql_num_rows($rsTopic);

$query = "SELECT * FROM preferences";
$rsPreferences = mysql_query($query, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);

if (isset($_POST["MM_update"]) ) { // posted 
if ($_POST['statusID'] != $_POST['oldstatusID']) { // status changed
if ($_POST['statusID'] == 1) { //accepted
// mail topic originator AND comment poster that approval has happened
$subject = "Your ".$site_name." discussion topic approved";
$message = "Your topic \"".$row_rsTopic['topic']."\" has been approved for display on the site.\n\n";
$message .= "It may have been amended and says:\n".stripslashes($_POST['message'])."\n\n";
} else { //NOT accepted
$subject = "Your ".$site_name." discussion topic NOT approved";
$message = "Your topic \"".$row_rsTopic['topic']."\" has NOT been approved for display on the site.\n\n";
$message .= "It read:\n".stripslashes($_POST['message'])."\n\n";
$message .= "Posts to this site are moderated. Your post has not been allowed to appear on this site as it breaches the website acceptable usage policy. The moderator's decision is final and no correspondence will be entered in to regarding their decision.";
}// not accepted
$to = $row_rsTopic['email'];
require_once('../../mail/includes/sendmail.inc.php');
sendMail($to,$subject,$message);
} // end status changed
} // end posted


$uploaded = getUploads();
if (isset($uploaded) && is_array($uploaded)) {
	if(isset($uploaded["filename"][0]["newname"]) && $uploaded["filename"][0]["newname"]!="") { 
		$_POST['imageURL'] = $uploaded["filename"][0]["newname"]; 
	}
	$_POST['imageURL'] = (isset($_POST["noImage"])) ? "" : $_POST['imageURL'];
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE forumtopic SET topic=%s, statusID=%s, moderatorID=%s, sectionID=%s, editorpick=%s WHERE ID=%s",
                       GetSQLValueString($_POST['topic'], "text"),
                       GetSQLValueString($_POST['statusID'], "int"),
                       GetSQLValueString($_POST['moderatorID'], "int"),
                       GetSQLValueString($_POST['sectionID'], "int"),
                       GetSQLValueString(isset($_POST['editorpick']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['message'], "text"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());

  $updateGoTo = "index.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo));
}

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = (get_magic_quotes_gpc()) ? $_SESSION['MM_Username'] : addslashes($_SESSION['MM_Username']);
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID, usertypeID FROM users WHERE username = '%s'", $colname_rsLoggedIn);
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsStatus = "SELECT * FROM status";
$rsStatus = mysql_query($query_rsStatus, $aquiescedb) or die(mysql_error());
$row_rsStatus = mysql_fetch_assoc($rsStatus);
$totalRows_rsStatus = mysql_num_rows($rsStatus);


$maxRows_rsComments = 50;
$pageNum_rsComments = 0;
if (isset($_GET['pageNum_rsComments'])) {
  $pageNum_rsComments = $_GET['pageNum_rsComments'];
}
$startRow_rsComments = $pageNum_rsComments * $maxRows_rsComments;

$colname_rsComments = "-1";
if (isset($_GET['topicID'])) {
  $colname_rsComments = $_GET['topicID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsComments = sprintf("SELECT forumcomment.ID, forumcomment.posteddatetime, forumcomment.message, users.firstname, users.surname, forumcomment.statusID, forumcomment.rating FROM forumcomment LEFT JOIN users ON (forumcomment.postedbyID = users.ID) WHERE topicID = %s ORDER BY posteddatetime DESC", GetSQLValueString($colname_rsComments, "int"));
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

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsModerators = "SELECT ID, firstname, surname FROM users WHERE usertypeID >= 8 ORDER BY surname ASC";
$rsModerators = mysql_query($query_rsModerators, $aquiescedb) or die(mysql_error());
$row_rsModerators = mysql_fetch_assoc($rsModerators);
$totalRows_rsModerators = mysql_num_rows($rsModerators);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsForumSections = "SELECT forumsection.ID, forumsection.sectionname FROM forumsection WHERE forumsection.statusID = 1 ORDER BY forumsection.sectionname";
$rsForumSections = mysql_query($query_rsForumSections, $aquiescedb) or die(mysql_error());
$row_rsForumSections = mysql_fetch_assoc($rsForumSections);
$totalRows_rsForumSections = mysql_num_rows($rsForumSections);

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
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Manage Forum - Topics"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<?php if($row_rsPreferences['forumsections'] !=1) { ?>
<style>
.section {
	display:none;
}
</style>
<?php } ?>
<script src="../../SpryAssets/SpryValidationTextField.js"></script>
<link href="../../SpryAssets/SpryValidationTextField.css" rel="stylesheet"  />
<script src="/core/scripts/formUpload.js"></script>
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
    <h1><i class="glyphicon glyphicon-comment"></i> Manage Topic - Comments </h1>
    <?php require_once('../../core/includes/alert.inc.php'); ?>
    <form action="<?php echo $editFormAction; ?>" method="post" enctype="multipart/form-data" name="form1" id="form1" >
      <table class="form-table">
        <tr class="section">
          <td class="text-nowrap text-right">Section:</td>
          <td><select name="sectionID" id="sectionID" class="form-control">
              <option value="1" <?php if (!(strcmp(1, $row_rsTopic['sectionID']))) {echo "selected=\"selected\"";} ?>><?php echo isset($region['text_choose']) ? htmlentities($region['text_choose'], ENT_COMPAT, "UTF-8") : "Choose..." ?></option>
              <?php
do {  
?>
              <option value="<?php echo $row_rsForumSections['ID']?>"<?php if (!(strcmp($row_rsForumSections['ID'], $row_rsTopic['sectionID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsForumSections['sectionname']?></option>
              <?php
} while ($row_rsForumSections = mysql_fetch_assoc($rsForumSections));
  $rows = mysql_num_rows($rsForumSections);
  if($rows > 0) {
      mysql_data_seek($rsForumSections, 0);
	  $row_rsForumSections = mysql_fetch_assoc($rsForumSections);
  }
?>
            </select></td>
        </tr> <tr>
          <td class="text-nowrap text-right">Topic:</td>
          <td><span id="sprytextfield1">
            <input type="text"  name="topic" value="<?php echo $row_rsTopic['topic']; ?>" size="32" class="form-control"/>
            <span class="textfieldRequiredMsg">A title is required.</span></span></td>
        </tr> <tr>
          <td class="text-nowrap text-right">Editor pick:</td>
          <td><label>
            <input <?php if (!(strcmp($row_rsTopic['editorpick'],1))) {echo "checked=\"checked\"";} ?> type="checkbox" name="editorpick" id="editorpick" />
            </label></td>
        </tr> <tr>
          <td class="text-nowrap text-right">Moderator:</td>
          <td><select name="moderatorID" id="moderatorID" class="form-control">
            <option value="0" <?php if (!(strcmp(0, $row_rsTopic['moderatorID']))) {echo "selected=\"selected\"";} ?>>Default</option>
            <?php
do {  
?>
            <option value="<?php echo $row_rsModerators['ID']?>"<?php if (!(strcmp($row_rsModerators['ID'], $row_rsTopic['moderatorID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsModerators['firstname']." ".$row_rsModerators['surname']; ?></option>
            <?php
} while ($row_rsModerators = mysql_fetch_assoc($rsModerators));
  $rows = mysql_num_rows($rsModerators);
  if($rows > 0) {
      mysql_data_seek($rsModerators, 0);
	  $row_rsModerators = mysql_fetch_assoc($rsModerators);
  }
?>
            </select></td>
        </tr> <tr>
          <td class="text-nowrap text-right">Status:</td>
          <td><select name="statusID" class="form-control">
              <?php 
do {  
?>
              <option value="<?php echo $row_rsStatus['ID']?>" <?php if (!(strcmp($row_rsStatus['ID'], $row_rsTopic['statusID']))) {echo "SELECTED";} ?>><?php echo $row_rsStatus['description']?></option>
              <?php
} while ($row_rsStatus = mysql_fetch_assoc($rsStatus));
?>
            </select>
            <input name="oldstatusID" type="hidden" id="oldstatusID" value="<?php echo $row_rsTopic['statusID']; ?>" /></td>
        </tr> <tr>
          <td class="text-nowrap text-right">&nbsp;</td>
          <td><div>
              <button type="submit" class="btn btn-primary" >Save changes</button>
            </div></td>
        </tr>
      </table>
      <input type="hidden" name="ID" value="<?php echo $row_rsTopic['ID']; ?>" />
    
      <input type="hidden" name="MM_update" value="form1" />
    </form>
    <?php if ($totalRows_rsComments == 0) { // Show if recordset empty ?>
      <p>There have been no comments added to this topic so far. </p>
      <?php } // Show if recordset empty ?>
    <?php if ($totalRows_rsComments > 0) { // Show if recordset not empty ?>
    <h3>Comments (Date descending) </h3>
    <p class="text-muted">Comments <?php echo ($startRow_rsComments + 1) ?> to <?php echo min($startRow_rsComments + $maxRows_rsComments, $totalRows_rsComments) ?> of <?php echo $totalRows_rsComments ?> </p>
    <table class="table table-hover">
    <tbody>
      <?php do { ?>
        <tr>
          <td class="top"><?php if ($row_rsComments['statusID']==1) { ?>
            <img src="../../core/images/icons/green-light.png" alt="Active" width="16" height="16" style="vertical-align:
middle;" />
            <?php } else if ($row_rsComments['statusID']==0) { ?>
            <img src="../../core/images/icons/amber-light.png" alt="Pending" width="16" height="16" style="vertical-align:
middle;" />
            <?php } else { ?>
            <img src="../../core/images/icons/red-light.png" alt="Not displayed" width="16" height="16" style="vertical-align:
middle;" />
            <?php } ?>
        </td>
        
        <td class="top"><?php echo nl2br($row_rsComments['message']); ?><br />
            <em>Posted on <?php echo date('d/m/Y',strtotime($row_rsComments['posteddatetime'])); ?> by <?php echo $row_rsComments['firstname']; ?>&nbsp;<?php echo $row_rsComments['surname']; ?></em></td>
        <td class="top"><span class="rating starrating rating<?php echo $row_rsComments['rating']; ?>"><?php echo isset($row_rsComments['rating']) ? $row_rsLatestPosts['rating']."/10": ""; ?></span></td>
          <td class="top"><a href="edit_comment.php?commentID=<?php echo $row_rsComments['ID']; ?>" class="link_edit icon_only">Edit</a></td>
        </tr>
        <?php } while ($row_rsComments = mysql_fetch_assoc($rsComments)); ?></tbody>
    </table>
    <table class="form-table">
      <tr>
        <td><?php if ($pageNum_rsComments > 0) { // Show if not first page ?>
            <a href="<?php printf("%s?pageNum_rsComments=%d%s", $currentPage, 0, $queryString_rsComments); ?>">First</a>
            <?php } // Show if not first page ?></td>
       <td><?php if ($pageNum_rsComments > 0) { // Show if not first page ?>
            <a href="<?php printf("%s?pageNum_rsComments=%d%s", $currentPage, max(0, $pageNum_rsComments - 1), $queryString_rsComments); ?>" rel="prev">Previous</a>
            <?php } // Show if not first page ?></td>
        <td><?php if ($pageNum_rsComments < $totalPages_rsComments) { // Show if not last page ?>
            <a href="<?php printf("%s?pageNum_rsComments=%d%s", $currentPage, min($totalPages_rsComments, $pageNum_rsComments + 1), $queryString_rsComments); ?>" rel="next">Next</a>
            <?php } // Show if not last page ?></td>
        <td><?php if ($pageNum_rsComments < $totalPages_rsComments) { // Show if not last page ?>
            <a href="<?php printf("%s?pageNum_rsComments=%d%s", $currentPage, $totalPages_rsComments, $queryString_rsComments); ?>">Last</a>
            <?php } // Show if not last page ?></td>
      </tr>
    </table>
    <?php } // Show if recordset not empty ?>
    </p>
    <script>
<!--
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1");
//-->
    </script></div>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsStatus);

mysql_free_result($rsTopic);

mysql_free_result($rsComments);

mysql_free_result($rsModerators);

mysql_free_result($rsForumSections);

mysql_free_result($rsPreferences);
?>