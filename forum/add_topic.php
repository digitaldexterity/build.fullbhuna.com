<?php require_once('../Connections/aquiescedb.php'); ?><?php require_once('includes/forumfunctions.inc.php'); ?><?php require_once('../core/includes/framework.inc.php'); ?>
<?php require_once('../core/includes/upload.inc.php'); ?>
<?php $_GET['forumsectionID'] = isset($_GET['forumsectionID']) ? $_GET['forumsectionID'] : 1; ?><?php
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

// my code********  
if (!isset($_SERVER['QUERY_STRING'])) $_SERVER['QUERY_STRING'] = $_SERVER['QUERY_STRING']; 
// ***************

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
?><?php
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

mysql_select_db($database_aquiescedb, $aquiescedb);
if(isset($_GET['productID']) && intval($_GET['productID'])>0) { // topic on product review
	$select = "SELECT ID FROM forumtopic WHERE productID = ".intval($_GET['productID']);
	$result = mysql_query($select, $aquiescedb) or die(mysql_error());
	if(mysql_num_rows($result)>0) { // topic already started, so go to add comment
	$row = mysql_fetch_assoc($result);
	header("location: update_topic.php?productID=".intval($_GET['productID'])."&topicID=".$row['ID']); exit;
	}
}

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = "SELECT preferences.*, users.email AS forumemail FROM preferences LEFT JOIN users ON (preferences.forummoderatorID = users.ID)";
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);


$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID, usertypeID, users.firstname, users.surname FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

$varNewsID_rsNewsTitle = "-1";
if (isset($_GET['newsID'])) {
  $varNewsID_rsNewsTitle = $_GET['newsID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsNewsTitle = sprintf("SELECT news.title FROM news WHERE news.ID = %s", GetSQLValueString($varNewsID_rsNewsTitle, "int"));
$rsNewsTitle = mysql_query($query_rsNewsTitle, $aquiescedb) or die(mysql_error());
$row_rsNewsTitle = mysql_fetch_assoc($rsNewsTitle);
$totalRows_rsNewsTitle = mysql_num_rows($rsNewsTitle);

$colname_rsArticleTitle = "-1";
if (isset($_GET['articleID'])) {
  $colname_rsArticleTitle = $_GET['articleID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsArticleTitle = sprintf("SELECT title FROM article WHERE ID = %s", GetSQLValueString($colname_rsArticleTitle, "int"));
$rsArticleTitle = mysql_query($query_rsArticleTitle, $aquiescedb) or die(mysql_error());
$row_rsArticleTitle = mysql_fetch_assoc($rsArticleTitle);
$totalRows_rsArticleTitle = mysql_num_rows($rsArticleTitle);

$colname_rsThisSection = "1";
if (isset($_GET['forumsectionID'])) {
  $colname_rsThisSection = $_GET['forumsectionID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsThisSection = sprintf("SELECT sectionname, users.email FROM forumsection LEFT JOIN users ON (forumsection.moderatorID = users.ID) WHERE forumsection.ID = %s", GetSQLValueString($colname_rsThisSection, "int"));
$rsThisSection = mysql_query($query_rsThisSection, $aquiescedb) or die(mysql_error());
$row_rsThisSection = mysql_fetch_assoc($rsThisSection);
$totalRows_rsThisSection = mysql_num_rows($rsThisSection);

$varUserGroup_rsSections = "0";
if (isset($_SESSION['MM_UserGroup'])) {
  $varUserGroup_rsSections = $_SESSION['MM_UserGroup'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsSections = sprintf("SELECT ID, sectionname FROM forumsection WHERE statusID = 1 AND (forumsection.accesslevel <= %s OR forumsection.accesslevel = 0)", GetSQLValueString($varUserGroup_rsSections, "int"));
$rsSections = mysql_query($query_rsSections, $aquiescedb) or die(mysql_error());
$row_rsSections = mysql_fetch_assoc($rsSections);
$totalRows_rsSections = mysql_num_rows($rsSections);

$colname_rsProductTitle = "-1";
if (isset($_GET['productID'])) {
  $colname_rsProductTitle = $_GET['productID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsProductTitle = sprintf("SELECT title FROM product WHERE ID = %s", GetSQLValueString($colname_rsProductTitle, "int"));
$rsProductTitle = mysql_query($query_rsProductTitle, $aquiescedb) or die(mysql_error());
$row_rsProductTitle = mysql_fetch_assoc($rsProductTitle);
$totalRows_rsProductTitle = mysql_num_rows($rsProductTitle);
$approveforumposts =  ($row_rsPreferences['approveforumposts'] == 1 && $row_rsLoggedIn['usertypeID'] < 8)  ? 1 : 0;
$statusID = 1- $approveforumposts;

if (isset($_POST["MM_insert"])) {
	
	if(containsBannedWords($_POST['topic']) || containsBannedWords($_POST['message'])) {
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
  $insertSQL = "INSERT INTO forumtopic (topic, sectionID, statusID) VALUES (".GetSQLValueString($_POST['topic'], "text").", ". GetSQLValueString($_POST['sectionID'], "int").", 1)";

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($insertSQL, $aquiescedb) or die(mysql_error());
  $topicID = mysql_insert_id();
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
	$insert = "INSERT INTO forumcomment (topicID,  postedbyID, posteddatetime, statusID, message, IPaddress, emailme, rating) VALUES (".$topicID.",".GetSQLValueString($_POST['postedbyID'], "int").",NOW(), ".$statusID.", ".GetSQLValueString($_POST['message'], "text").",".GetSQLValueString($_POST['IPaddress'], "text").", ". GetSQLValueString(isset($_POST['mailme']) ? "true" : "", "defined","1","0").", ".GetSQLValueString($_POST['rating'], "int").")";
		mysql_query($insert, $aquiescedb) or die(mysql_error());
// Jiggery pokery
// insert and mail must go after preferences and topic to pick up email addresses
	
	$to = isset($row_rsThisSection['email']) ? $row_rsThisSection['email'] : isset($row_rsPreferences['forumemail']) ? $row_rsPreferences['forumemail'] : $row_rsPreferences['contactemail'];
$subject = "New topic started on web site forum";
$message = "A new topic has been started on the web site forum:\n\n";
$message .= $_POST['topic']."\n\n";
$message .= ($row_rsPreferences['approveforumposts'] == 1) ? "This topic will need to be approved by you before it is displayed on the site.\n\n" : "This topic now appears live on the discussion forum.\n\n";
$message .= $row_rsLoggedIn['firstname']." ".$row_rsLoggedIn['surname']." says:\n".$_POST['message']."\n\n";
$message .= "View this topic here: ";
$message .= getProtocol()."://". $_SERVER['HTTP_HOST']."/forum/update_topic.php?topicID=".$topicID;
require_once('../mail/includes/sendmail.inc.php');
sendMail($to,$subject,$message);



  $insertGoTo = "index.php?topicadded=true";
  if (isset($_SERVER['QUERY_STRING'])) {
    $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
    $insertGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $insertGoTo));
}


?>
<?php $_GET['forumsectionID'] = isset($_GET['forumsectionID']) ? $_GET['forumsectionID'] : 1; ?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php echo $site_name; $pageTitle = "Forum - New Topic"; ?> - Forum - New Topic</title>
<!-- InstanceEndEditable -->
<?php require_once('../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<style><!--
<?php if ($row_rsPreferences['forumsections']!=1 || $totalRows_rsSections < 2) { ?>

.forumsections {
display:none;
}<?php } ?>
<?php if(!isset($_GET['productID'])) { echo ".rating { display:none; }"; } ?>
--></style>

<script src="/SpryAssets/SpryValidationTextField.js"></script>
<script src="/SpryAssets/SpryValidationTextarea.js"></script>
<link href="/SpryAssets/SpryValidationTextField.css" rel="stylesheet"  />
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
        <div class="crumbs"><div><span class="you_are_in">You are in: </span><a href="/">Home</a><span class="separator">&nbsp;&rsaquo;&nbsp;</span><a href="index.php">Discussions</a><span class="forumsections"><span class="separator">&nbsp;&rsaquo;&nbsp;</span><a href="index.php?forumsectionID=<?php echo intval($_GET['forumsectionID']); ?>"><?php echo $row_rsThisSection['sectionname']; ?></a></span><span class="separator">&nbsp;&rsaquo;&nbsp;</span>New Topic</div></div>
    <h1>Start New Topic </h1>

<?php if(isset($submit_error)) { ?><p class="alert alert-danger" role="alert"><?php echo $submit_error; ?></p><?php } ?>
<form action="<?php echo $editFormAction; ?>" method="post" enctype="multipart/form-data" name="form1" id="form1" >         
  <table class="form-table">
    <tr class="forumsections">
      <td class="text-nowrap text-right">Section:</td>
      <td><select name="sectionID" id="sectionID">
        <option value="1" <?php if (!(strcmp(1, $_GET['forumsectionID']))) {echo "selected=\"selected\"";} ?>><?php echo isset($region['text_choose']) ? htmlentities($region['text_choose'], ENT_COMPAT, "UTF-8") : "Choose..." ?></option>
        <?php
do {  
?>
        <option value="<?php echo $row_rsSections['ID']?>"<?php if (!(strcmp($row_rsSections['ID'], $_GET['forumsectionID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsSections['sectionname']?></option>
        <?php
} while ($row_rsSections = mysql_fetch_assoc($rsSections));
  $rows = mysql_num_rows($rsSections);
  if($rows > 0) {
      mysql_data_seek($rsSections, 0);
	  $row_rsSections = mysql_fetch_assoc($rsSections);
  }
?>
        </select>            </td>
      </tr> <tr>
      <td class="text-nowrap text-right">Topic title:</td>
      <td><span id="sprytextfield1">
        <input name="topic" type="text"  value="<?php echo isset($row_rsNewsTitle['title']) ? $row_rsNewsTitle['title'] : ""; echo isset($row_rsArticleTitle['title']) ? $row_rsArticleTitle['title'] : ""; echo isset($row_rsProductTitle['title']) ? $row_rsProductTitle['title'] : ""; ?>" size="50" maxlength="100" />
        <span class="textfieldRequiredMsg">A title is required.</span></span>
        <input name="newsID" type="hidden" id="newsID" value="<?php echo intval($_GET['newsID']); ?>" />
        <input name="articleID" type="hidden" id="articleID" value="<?php echo intval($_GET['articleID']); ?>" /></td>
      </tr>
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
      <td class="text-nowrap text-right top">Message:</td>
      <td><span id="sprytextarea1">
        <textarea name="message" cols="50" rows="5"></textarea>
        <span class="textareaRequiredMsg">A message is required.</span></span></td>
      </tr>
    <?php if ($row_rsPreferences['allowforumjpeg'] == 1) { //allow images ?>
    <tr class="upload">
      <td class="text-nowrap text-right">Optional image:</td>
      <td><input name="filename" type="file" id="filename" size="20" />                </td>
      </tr> <?php } //end allow images ?> <tr>
      <td class="text-nowrap text-right">Email me:</td>
      <td><input type="checkbox" name="mailme" id="mailme" />
        <label for="mailme">(when someone comments on my topic)</label></td>
      </tr>
    <?php if(isset($_SESSION['MM_UserGroup']) && $_SESSION['MM_UserGroup'] >=8) { // is admin?> <tr>
      <td class="text-nowrap text-right">Email everyone:</td>
      <td><input name="emailmembers" type="checkbox" id="emailmembers" value="1" />
        <label for="emailmembers">(notification of new topic - available only to administrator's posts)</label></td>
      </tr><?php } // end is admin ?> <tr>
      <td class="text-nowrap text-right">&nbsp;</td>
      <td><div><input type="submit" class="button" value="Add New Topic" /></div></td>
      </tr>
    </table>
  <input name="imageURL" type="hidden" id="imageURL" />
  <input type="hidden" name="postedbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
  <input type="hidden" name="MM_insert" value="form1" />
  <input name="regionID" type="hidden" value="<?php echo isset($regionID) ? $regionID : 1; ?>" />
  <input name="productID" type="hidden" id="productID" value="<?php echo isset($_REQUEST['productID']) ? htmlentities($_REQUEST['productID']) : ""; ?>" />
  <input name="posteddatetime" type="hidden" id="posteddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
  <input name="IPaddress" type="hidden" id="IPaddress" value="<?php echo @getClientIP(); ?>" />
  <?php if ($row_rsPreferences['approveforumposts'] == 1 && $row_rsLoggedIn['usertypeID'] < 8) { //forum posts must be approved ?>
  
  <p>NOTE: All forum posts will be reviewed first before they are displayed on the site.</p><input type="hidden" name="statusID" value="0" />
  <?php } else { ?><input type="hidden" name="statusID" value="1" />
  <?php } ?>
</form>
<script>
<!--
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1");
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

mysql_free_result($rsNewsTitle);

mysql_free_result($rsArticleTitle);

mysql_free_result($rsThisSection);

mysql_free_result($rsSections);

mysql_free_result($rsProductTitle);

mysql_free_result($rsPreferences);
?>
