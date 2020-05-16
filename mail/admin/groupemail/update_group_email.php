<?php require_once('../../../Connections/aquiescedb.php'); ?><?php require_once('../../../core/includes/adminAccess.inc.php'); ?>
<?php require_once('../../../members/includes/userfunctions.inc.php'); ?><?php require_once('../../../core/includes/framework.inc.php'); ?>
<?php require_once('../../includes/sendmail.inc.php'); ?>
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

$MM_restrictGoTo = "../../../login/index.php?notloggedin=true";
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



// Process HTML
// build html
if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
	
	
		
	if($_POST['templateID']>0 || (isset($_POST['mailbody'] ) && $_POST['mailbody'] !="")) { // HTML
	
	// replace heading, paragraphs, breaks with line breaks and strip all other tags for plain text
		$_POST['message'] = strip_tags(preg_replace("/(<br>|<\/p>|<\/h1>|<\/h2>|<\/h3>)/i","\n\n",$_POST['mailbody']));
		// fix empty p tags (tinymce set not to check)
		$_POST['html'] = str_replace("<p></p>","<p>&nbsp;</p>", $_POST['mailbody']);
		
		// merge email links from template and group email editor
		$_POST['html'] = str_replace("admin/groupemail/{forward}","forward.php?emailID=".intval($_GET['emailID'])."&token=".md5(PRIVATE_KEY.$_GET['emailID']),$_POST['html']);
		$_POST['html'] = str_replace("admin/templates/{forward}","forward.php?emailID=".intval($_GET['emailID'])."&token=".md5(PRIVATE_KEY.$_GET['emailID']),$_POST['html']);
		
		// this is just an initial transformation - the rest needs to be done in emails
		$_POST['html'] = str_replace("admin/groupemail/{online}","index.php?{online}",$_POST['html']);
		$_POST['html'] = str_replace("admin/templates/{online}","index.php?{online}",$_POST['html']);
		$_POST['html'] = str_replace("admin/groupemail/{unsubscribe}","unsubscribe.php?{unsubscribe}",$_POST['html']);
		$_POST['html'] = str_replace("admin/templates/{unsubscribe}","unsubscribe.php?{unsubscribe}",$_POST['html']);
		
		$_POST['html'] = isset($_POST['trackclicks']) ? addClickTracking($_POST['html'], $_GET['emailID']) : $_POST['html'];
	
	} // end not plain
}// end process HTML

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE groupemail SET regionID=%s, startdatetime=%s, usertypeID=%s, usergroupID=%s, trackclicks=%s, `from`=%s, fromname=%s, subject=%s, message=%s, head=%s, html=%s, ignoreoptout=%s, showunsubscribe=%s, viewonline=%s, modifiedbyID=%s, modifieddatetime=%s, bodytag=%s WHERE ID=%s",
                       GetSQLValueString($_POST['regionID'], "int"),
                       GetSQLValueString($_POST['startdatetime'], "date"),
                       GetSQLValueString($_POST['usertypeID'], "int"),
                       GetSQLValueString($_POST['groupID'], "int"),
                       GetSQLValueString(isset($_POST['trackclicks']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['from'], "text"),
                       GetSQLValueString($_POST['fromname'], "text"),
                       GetSQLValueString($_POST['subject'], "text"),
                       GetSQLValueString($_POST['message'], "text"),
                       GetSQLValueString($_POST['head'], "text"),
                       GetSQLValueString($_POST['html'], "text"),
                       GetSQLValueString(isset($_POST['ignoreoptout']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['showunsubscribe']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['viewonline']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['modifiedbyID'], "int"),
                       GetSQLValueString($_POST['modifieddatetime'], "date"),
                       GetSQLValueString($_POST['bodytag'], "text"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  
  buildGroupSet($_POST['groupsetID']);
  if($_POST['usertypeID'] != $_POST['oldusertypeID'] || $_POST['groupID'] != $_POST['oldgroupID']) { // group settings changed 
  	deleteMailList($_POST['ID']);
  	
  }
  createMailList($_POST['ID']); // will only create if doesn't exist
  $updateGoTo = "preview.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo)); exit;
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) { exit; } // fix hanging on some servers


$colname_rsGroupEmail = "-1";
if (isset($_GET['emailID'])) {
  $colname_rsGroupEmail = $_GET['emailID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsGroupEmail = sprintf("SELECT groupemail.*, groupemailtemplate.templateHTML,  templatehead, usergroup.groupsetID FROM groupemail LEFT JOIN groupemailtemplate ON (groupemailtemplate.ID = groupemail.templateID) LEFT JOIN usergroup ON (groupemail.usergroupID = usergroup.ID) WHERE groupemail.ID = %s", GetSQLValueString($colname_rsGroupEmail, "int"));
$rsGroupEmail = mysql_query($query_rsGroupEmail, $aquiescedb) or die(mysql_error());
$row_rsGroupEmail = mysql_fetch_assoc($rsGroupEmail);
$totalRows_rsGroupEmail = mysql_num_rows($rsGroupEmail);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsRegions = "SELECT ID, title FROM region WHERE statusID = 1 ORDER BY title ASC";
$rsRegions = mysql_query($query_rsRegions, $aquiescedb) or die(mysql_error());
$row_rsRegions = mysql_fetch_assoc($rsRegions);
$totalRows_rsRegions = mysql_num_rows($rsRegions);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsUserType = "SELECT ID, CONCAT(name,'s') AS usertype FROM usertype WHERE ID >= 0";
$rsUserType = mysql_query($query_rsUserType, $aquiescedb) or die(mysql_error());
$row_rsUserType = mysql_fetch_assoc($rsUserType);
$totalRows_rsUserType = mysql_num_rows($rsUserType);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsUserGroups = "SELECT ID, groupname FROM usergroup WHERE statusID = 1 ORDER BY groupname ASC";
$rsUserGroups = mysql_query($query_rsUserGroups, $aquiescedb) or die(mysql_error());
$row_rsUserGroups = mysql_fetch_assoc($rsUserGroups);
$totalRows_rsUserGroups = mysql_num_rows($rsUserGroups);

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID, usertypeID, regionID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = "SELECT * FROM preferences";
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);


// one off replace of news items in template - currently usused - use {news} in preview
function addNews($html) {
	global $database_aquiescedb, $aquiescedb;
	
$varRegionID_rsNews = "1";
if (isset($regionID)) {
  $varRegionID_rsNews = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsNews = sprintf("SELECT ID, title, summary, body, news.imageURL FROM news WHERE (news.displayfrom IS NULL OR DATE(news.displayfrom) <= CURDATE()) AND (news.displayto IS NULL OR DATE(news.displayto) >= CURDATE()) AND news.status = 1 AND (news.regionID =0 OR news.regionID = %s OR  news.regionID = 0) AND news.groupemail = 1 ORDER BY news.headline DESC LIMIT 4 ", GetSQLValueString($varRegionID_rsNews, "int"));
$rsNews = mysql_query($query_rsNews, $aquiescedb) or die(mysql_error());
$row_rsNews = mysql_fetch_assoc($rsNews);
$totalRows_rsNews = mysql_num_rows($rsNews); 
$protocol = getProtocol()."://";
if(isset($row_rsNews['title']) && strpos($html,"{headline}")!==false) { 

$newshtml = "<h1>".$row_rsNews['title']."</h1>";
$newshtml .= isset($row_rsNews['imageURL']) ? "<img src=\"".$protocol.$_SERVER['HTTP_HOST']."/".getImageURL($row_rsNews['imageURL'],"medium")."\" class=\"fltrt\">" : "";
$newshtml .="<p><strong>".$row_rsNews['summary']."</strong></p><p>".nl2br($row_rsNews['body'])."</p>";
$html = str_replace("{headline}",$newshtml,$html);
}
$row_rsNews = mysql_fetch_assoc($rsNews); 
if(isset($row_rsNews['title']) && strpos($html,"{news1}")!==false) { 
$newshtml = "<h2>".$row_rsNews['title']."</h2><p>".$row_rsNews['summary']."</p><p><a href=\"".$protocol.$_SERVER['HTTP_HOST']."/news/story.php?newsID=".$row_rsNews['ID']."\" target = \"_blank\">Read more...</a></p>";
$html = str_replace("{news1}",$newshtml,$html);
}
$row_rsNews = mysql_fetch_assoc($rsNews); 
if(isset($row_rsNews['title']) && strpos($html,"{news2}")!==false) { 
$newshtml = "<h2>".$row_rsNews['title']."</h2><p>".$row_rsNews['summary']."</p><p><a href=\"".$protocol.$_SERVER['HTTP_HOST']."/news/story.php?newsID=".$row_rsNews['ID']."\" target = \"_blank\">Read more...</a></p>";
$html = str_replace("{news2}",$newshtml,$html);
}
$row_rsNews = mysql_fetch_assoc($rsNews); 
if(isset($row_rsNews['title']) && strpos($html,"{news3}")!==false) { 
$newshtml = "<h2>".$row_rsNews['title']."</h2><p>".$row_rsNews['summary']."</p><p><a href=\"".$protocol.$_SERVER['HTTP_HOST']."/news/story.php?newsID=".$row_rsNews['ID']."\" target = \"_blank\">Read more...</a></p>";
$html = str_replace("{news3}",$newshtml,$html);
}
return $html;
} // end func

?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Edit group email"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<link href="/mail/css/mailDefault.css" rel="stylesheet" type="text/css" />
<?php 
$remove_script_host = "false"; // default for tinymce is true
$convert_urls = "false"; // default for tinymce is true
if(!defined("TINYMCE_CONTENT_CSS")) define("TINYMCE_CONTENT_CSS", "/core/css/global.css");
define("TINY_MCE_PLUGINS", "fullpage");
require('../../../core/tinymce/tinymce.inc.php');
 ?>
<script src="../../../SpryAssets/SpryTabbedPanels.js"></script>
<style type="text/css">
<!--
<?php if($totalRows_rsUserGroups==0) { ?>
.groups { display:none; }
<?php } ?>
<?php if ($row_rsLoggedIn['usertypeID'] <9 || strcmp($row_rsPreferences['useregions'],1) || totalRows_rsRegions == 1) { ?>
.region {display:none; } 
<?php } ?>
-->
</style>
<link href="../../../SpryAssets/SpryTabbedPanels.css" rel="stylesheet" type="text/css" />
<script>
$(window).load(function() {
	/*
	$(tinyMCE.activeEditor.getDoc()).children().find('head').append("<!--editorHead-->"+$("#head").val()+"<!--editorHead-->");
	$("#head").blur(function() {
		$(tinyMCE.activeEditor.getDoc()).children().find('head').replace(/<!--editorHead-->.+?<!--editorHead-->/gi, "<!--editorHead-->"+$("#head").val()+"<!--editorHead-->"); 
	});*/
});



</script>
<script src="/core/scripts/formUpload.js"></script>
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" -->
        <div class="page mail">
     <form action="<?php echo $editFormAction; ?>" method="post" enctype="multipart/form-data" name="form1" id="form1" >
    <h1><i class="glyphicon glyphicon-envelope"></i> Edit Group Mail</h1>
    <div id="TabbedPanels1" class="TabbedPanels">
      <ul class="TabbedPanelsTabGroup">
        <li class="TabbedPanelsTab" tabindex="0">Delivery</li>
        <li class="TabbedPanelsTab" tabindex="0">Content</li>
<li class="TabbedPanelsTab" tabindex="0">Tips</li>
</ul>
      <div class="TabbedPanelsContentGroup">
        <div class="TabbedPanelsContent">
          <table class="form-table"> <tr>
              <td class="text-nowrap text-right">Start sending:</td>
              <td><?php if ($row_rsGroupEmail['startdatetime'] < date('Y-m-d H:i:s')) { $row_rsGroupEmail['startdatetime'] = date('Y-m-d H:i:s'); } ?>
                <input type="hidden" name="startdatetime" id="startdatetime" value="<?php echo $row_rsGroupEmail['startdatetime']; ?>"/>
                <?php $inputname = "startdatetime"; $time= true;  $setvalue = $row_rsGroupEmail['startdatetime']; include("../../../core/includes/datetimeinput.inc.php"); 
			   ?></td>
            </tr>
            <tr class="region">
              <td class="text-nowrap text-right">Site:</td>
              <td><select name="regionID"  id="regionID"  class="form-control">
                <option value="0" <?php if (!(strcmp("0", $row_rsGroupEmail['regionID']))) {echo "selected=\"selected\"";} ?>>All Sites</option>
                <?php
do {  
?>
                <option value="<?php echo $row_rsRegions['ID']?>"<?php if (!(strcmp($row_rsRegions['ID'], $row_rsGroupEmail['regionID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsRegions['title']?></option>
                <?php
} while ($row_rsRegions = mysql_fetch_assoc($rsRegions));
  $rows = mysql_num_rows($rsRegions);
  if($rows > 0) {
      mysql_data_seek($rsRegions, 0);
	  $row_rsRegions = mysql_fetch_assoc($rsRegions);
  }
?>
                </select></td>
            </tr> <tr>
              <td class="text-nowrap text-right">To:</strong></td>
              <td class="form-inline"><span class = "groups">
                <select name="groupID" id="groupID"  class="form-control">
                  <option value="0" <?php if (!(strcmp(0, $row_rsGroupEmail['usergroupID']))) {echo "selected=\"selected\"";} ?>>Everyone</option>
                  <?php
do {  
?>
<option value="<?php echo $row_rsUserGroups['ID']?>"<?php if (!(strcmp($row_rsUserGroups['ID'], $row_rsGroupEmail['usergroupID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsUserGroups['groupname']?></option>
                  <?php
} while ($row_rsUserGroups = mysql_fetch_assoc($rsUserGroups));
  $rows = mysql_num_rows($rsUserGroups);
  if($rows > 0) {
      mysql_data_seek($rsUserGroups, 0);
	  $row_rsUserGroups = mysql_fetch_assoc($rsUserGroups);
  }
?>
                </select>
                who are</span>
                <select name="usertypeID"  id="usertypeID" class="form-control">
                  <option value="-1" <?php if (!(strcmp(-1, $row_rsGroupEmail['usertypeID']))) {echo "selected=\"selected\"";} ?>>Any rank</option>
                  <?php
do {  
?>
<option value="<?php echo $row_rsUserType['ID']?>"<?php if (!(strcmp($row_rsUserType['ID'], $row_rsGroupEmail['usertypeID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsUserType['usertype']?></option>
                  <?php
} while ($row_rsUserType = mysql_fetch_assoc($rsUserType));
  $rows = mysql_num_rows($rsUserType);
  if($rows > 0) {
      mysql_data_seek($rsUserType, 0);
	  $row_rsUserType = mysql_fetch_assoc($rsUserType);
  }
?>
                </select>
                <input name="oldgroupID" id="oldgroupID" type="hidden" value="<?php echo $row_rsGroupEmail['usergroupID']; ?>">
                <input name="oldusertypeID" id="oldusertypeID" type="hidden" value="<?php echo $row_rsGroupEmail['usertypeID']; ?>"></td>
            </tr> <tr>
              <td class="text-nowrap text-right">From name:</td>
              <td><input name="fromname" type="text"  value="<?php echo $row_rsGroupEmail['fromname']; ?>" size="50" maxlength="100"  class="form-control"/></td>
            </tr> <tr>
              <td class="text-nowrap text-right">From email:</td>
              <td><input name="from" type="email"  value="<?php echo $row_rsGroupEmail['from']; ?>" size="50" maxlength="100"  class="form-control"/></td>
            </tr> <tr>
              <td class="text-nowrap text-right">Subject:</td>
              <td><input name="subject" type="text"  value="<?php echo $row_rsGroupEmail['subject']; ?>" size="50" maxlength="100"  class="form-control"/></td>
            </tr> <tr>
              <td class="text-nowrap text-right">Privacy:</td>
              <td><label>
                <input <?php if (!(strcmp($row_rsGroupEmail['trackclicks'],1))) {echo "checked=\"checked\"";} ?> name="trackclicks" type="checkbox" id="trackclicks" value="1"  />
              </label>
                track clicks&nbsp;&nbsp;&nbsp;
                <label>
                  <input <?php if (!(strcmp($row_rsGroupEmail['ignoreoptout'],1))) {echo "checked=\"checked\"";} ?> name="ignoreoptout" type="checkbox" id="ignoreoptout" value="1" onclick="if(this.checked) return confirm('Are you sure you want to send to users that have opted out of emails?\n\nThis is not recommended unless you are sending vital communication.'); " />
                </label>
                send to non-subscribers&nbsp;&nbsp;&nbsp;
                <label>
                  <input <?php if (!(strcmp($row_rsGroupEmail['showunsubscribe'],1))) {echo "checked=\"checked\"";} ?> name="showunsubscribe" type="checkbox" id="showunsubscribe" value="1" />
                  show unsubscribe link</label></td>
            </tr>
          </table>
        </div>
        <div class="TabbedPanelsContent">
          <?php if($row_rsGroupEmail['templateID']>0 || (isset($row_rsGroupEmail['html']) && $row_rsGroupEmail['html']!="") ) { 
	  $html = (isset($row_rsGroupEmail['html']) && $row_rsGroupEmail['html']!="") ? $row_rsGroupEmail['html'] : $row_rsGroupEmail['templateHTML']; 
	  // upgrade to new full HTML if required
	  if(strpos($html,"<head")===false) {
		  $html = (isset($row_rsGroupEmail['bodytag'])  && strpos($row_rsGroupEmail['bodytag'],"<body") !==false) ? $row_rsGroupEmail['bodytag'].$html : "<body>".$html;
		  $html = "<html><head>".$row_rsGroupEmail['head']."</head>".$html."</body></html>";
	  }
	  $html = removeClickTracking($html); // remove link identifiers
	  $html = addNews($html); ?>
          <input type="hidden" name="message" id="message" />
          <textarea name="mailbody" id="mailbody" cols="45" rows="5"  class="mailbody tinymce form-control"><?php echo $html; ?></textarea><br /><label><input <?php if (!(strcmp($row_rsGroupEmail['viewonline'],1))) {echo "checked=\"checked\"";} ?> name="viewonline" type="checkbox" value="1" />Automatically show view online link at top</label>
          <?php } else { // plain text ?>
          <textarea name="message" id="message" cols="80" rows="10" class="form-control"><?php echo $row_rsGroupEmail['head'].$row_rsGroupEmail['message']; ?></textarea>
          <br />
          <?php } ?>
        </div>
<div class="TabbedPanelsContent"> <h2>HTML Email tips:</h2>
         <p>HTML emails are viewed across many different devices and programs with varying capabilities, so to ensure emails look consistent follow these rules:</p>
         <ol>
           <li>Use tables to layout - make top level table 600-650pixels with and centre.</li>
           <li>Background images are not supported</li>
           <li>Don't use Margin or floats - Outlook doesn't suppot them</li>
           <li>All styles must be inline - Gmail deos not support HEAD styles</li>
           <li>Colours must be full hex values (i.e. not #fff or rgb(255,255,255)</li>
           <li>Use web safe colours if overlaying images</li>
         </ol>
         <p>&nbsp;</p>
      
<!-- LEGACY FIELDS -->
  <input name="head" id="head" type="hidden" value="<?php echo $row_rsGroupEmail['head']; ?>">

   <input name="bodytag" id="bodytag" type="hidden" value="<?php echo (isset($row_rsGroupEmail['bodytag'])  && strpos($row_rsGroupEmail['bodytag'],"<body") !==false) ? $row_rsGroupEmail['bodytag'] : htmlentities("<body leftmargin=\"0\" topmargin=\"0\" marginwidth=\"0\" marginheight=\"0\" yahoo=\"fix\" style=\"width: 100%; background-color: #ffffff; margin:0; padding:0; -webkit-font-smoothing: antialiased;font-family: Arial, sans-serif;\">", ENT_COMPAT, "UTF-8"); ?>">
   
   
      </div>
</div>
    </div>
   
    
     
     <div> <button type="submit"  class="btn btn-primary"  onChange="if(document.getElementById('groupID').value != document.getElementById('oldågroupID').value || document.getElementById('usertypeID').value != document.getElementById('oldusertypeID').value) { if(confirm('You have changed the recipient groups. This will mean the recipient list will be reset and sending will start again to the new list.\n\nDo you wish to continue?')) { return true; } else { return false;}}" >Save and preview</button></div>
      
      <input type="hidden" name="html" id="html" />
      <input name="ID" type="hidden" id="ID" value="<?php echo intval($_GET['emailID']); ?>" />
      <input type="hidden" name="MM_update" value="form1" />
     
      <input name="templateHTML" type="hidden" id="templateHTML" value="<?php echo htmlentities($row_rsGroupEmail['templateHTML']); ?>" />
      <input name="templateID" type="hidden" id="templateID" value="<?php echo $row_rsGroupEmail['templateID']; ?>" />
      <input name="modifieddatetime" type="hidden" id="modifieddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
      <input name="modifiedbyID" type="hidden" id="modifiedbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" /><input name="groupsetID" type="hidden" value="<?php echo $row_rsGroupEmail['groupsetID']; ?>">
    </form>
   <?php require_once('../includes/merge_help.inc.php'); ?>
    <script>
var TabbedPanels1 = new Spry.Widget.TabbedPanels("TabbedPanels1", {defaultTab:1});
    </script></div>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php

if(false) {
mysql_free_result($rsGroupEmail);

mysql_free_result($rsRegions);

mysql_free_result($rsUserType);

mysql_free_result($rsUserGroups);

mysql_free_result($rsLoggedIn);

mysql_free_result($rsPreferences);

mysql_free_result($rsNews);

} 

?>
