<?php require_once('../../../Connections/aquiescedb.php'); ?><?php require_once('../../../core/includes/adminAccess.inc.php'); ?><?php require_once('../../../members/includes/userfunctions.inc.php'); ?><?php require_once('../../includes/sendmail.inc.php'); ?><?php require_once('../../../core/includes/framework.inc.php'); ?>
<?php

createMailList($_GET['emailID']); 


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

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}
// start sending
if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "makeactive")) {
	buildGroupSet($_POST['groupsetID']);
	$startdatetime = ($_POST['startdatetime']<= date('Y-m-d H:i:s')) ? date('Y-m-d H:i:s') : $_POST['startdatetime'];
  $updateSQL = "UPDATE groupemail SET active = 1, startdatetime = ".GetSQLValueString($startdatetime, "date")." WHERE ID=".GetSQLValueString($_POST['ID'], "int")."";
  mysql_select_db($database_aquiescedb, $aquiescedb);
  mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
  

  $updateGoTo = "index.php";
  if($_POST['startdatetime']<= date('Y-m-d H:i:s')) {
	  $updateGoTo .= "?refresh=true"; // refershes index page to send emails
  }
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo)); exit;
}

// reset or stop
if (isset($_POST["pause"]) || isset($_POST["stop"]) || isset($_POST["reset"])) {
	buildGroupSet($_POST['groupsetID']);
	if(isset($_POST["pause"])) {
		pauseGroupEmail($_POST['emailID']);
	} else {
  		deleteMailList($_POST['emailID']);
		if(isset($_POST["reset"])) {
			
			mysql_select_db($database_aquiescedb, $aquiescedb);
			$update = "UPDATE groupemail SET enddatetime = NULL WHERE ID = ".intval($_POST['emailID']);
			mysql_query($update, $aquiescedb) or die(mysql_error());
		}
	}
  $updateGoTo = "preview.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo)); exit;
}




$colname_rsEmail = "-1";
if (isset($_GET['emailID'])) {
  $colname_rsEmail = $_GET['emailID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsEmail = sprintf("SELECT groupemail.*, usergroup.groupname, usertype.name, usergroup.groupsetID FROM groupemail LEFT JOIN usergroup ON (groupemail.usergroupID = usergroup.ID) LEFT JOIN usertype ON (groupemail.usertypeID = usertype.ID) WHERE groupemail.ID = %s", GetSQLValueString($colname_rsEmail, "int"));
$rsEmail = mysql_query($query_rsEmail, $aquiescedb) or die(mysql_error());
$row_rsEmail = mysql_fetch_assoc($rsEmail);
$totalRows_rsEmail = mysql_num_rows($rsEmail);

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT email FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);


$html = $row_rsEmail['html'];

//****** MERGE

$html = str_replace("{currentmonth}",date('F'), $html);
$html = str_replace("{currentyear}",date('Y'), $html);


// **** MERGE NEWS ********

$varRegionID_rsNews = "0";
if (isset($regionID)) {
  $varRegionID_rsNews = $regionID;
}
$varAccessLevel_rsNews = "0";
if (isset($row_rsEmail['usertypeID'])) {
  $varAccessLevel_rsNews = $row_rsEmail['usertypeID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsNews = sprintf("SELECT news.ID, news.title, summary FROM news LEFT JOIN newssection ON (news.sectionID = newssection.ID) WHERE status = 1 AND (news.displayfrom <= NOW() OR news.displayfrom IS NULL) AND (news.displayto >= NOW() OR news.displayto IS NULL)  AND (news.regionID = %s OR news.regionID IS NULL OR %s=0) AND news.groupemail = 1 AND (newssection.accesslevel <= %s OR  newssection.accesslevel  IS NULL) ORDER BY headline DESC, news.displayfrom ASC", GetSQLValueString($varRegionID_rsNews, "int"),GetSQLValueString($varRegionID_rsNews, "int"),GetSQLValueString($varAccessLevel_rsNews, "int"));
$rsNews = mysql_query($query_rsNews, $aquiescedb) or die(mysql_error());
$row_rsNews = mysql_fetch_assoc($rsNews);
$totalRows_rsNews = mysql_num_rows($rsNews);

$varEmailID_rsProgress = "-1";
if (isset($_GET['emailID'])) {
  $varEmailID_rsProgress = $_GET['emailID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsProgress = sprintf("SELECT COUNT(groupemaillist.sent) AS emailsent FROM groupemaillist WHERE groupemailID = %s AND groupemaillist.sent = 1 ", GetSQLValueString($varEmailID_rsProgress, "int"));
$rsProgress = mysql_query($query_rsProgress, $aquiescedb) or die(mysql_error());
$row_rsProgress = mysql_fetch_assoc($rsProgress);
$totalRows_rsProgress = mysql_num_rows($rsProgress);

$varEmailID_rsTotal = "-1";
if (isset($_GET['emailID'])) {
  $varEmailID_rsTotal = $_GET['emailID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsTotal = sprintf("SELECT COUNT(groupemaillist.sent) AS recipients FROM groupemaillist WHERE groupemailID = %s ", GetSQLValueString($varEmailID_rsTotal, "int"));
$rsTotal = mysql_query($query_rsTotal, $aquiescedb) or die(mysql_error());
$row_rsTotal = mysql_fetch_assoc($rsTotal);
$totalRows_rsTotal = mysql_num_rows($rsTotal);
$news = "";
if ($totalRows_rsNews > 0) { // Show if recordset not empty 
	do { 
 		$news .=" <h2>".$row_rsNews['title']."</h2>";
		$news .= "<p>".$row_rsNews['summary']."<br />&raquo;&nbsp;<a href=\""; echo getProtocol()."://". $_SERVER['HTTP_HOST']."/news/story.php?newsID=".(100+$row_rsNews['ID'])."\">Read more...</a></p>";
     	$update = "UPDATE news SET groupemail = 0 WHERE ID = ".$row_rsNews['ID'];
		mysql_query($update, $aquiescedb) or die(mysql_error());
     } while ($row_rsNews = mysql_fetch_assoc($rsNews)); 
} // Show if recordset not empty 
	 
$html = str_replace("{news}",$news,$html);	 
$update = "UPDATE groupemail SET html = ".GetSQLValueString($html, "text")." WHERE ID = ". $_GET['emailID'];
mysql_query($update, $aquiescedb) or die(mysql_error());

/// ******** END MERGE NEWS **********
	 ?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Email Preview"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script>
<!--

function sendTest(recipient) {
	recipient = prompt("Do you want to send a test email to yourself?\n\nThe email will be sent to the address below:\n(Change if required)",recipient);
	if(recipient) {
		url = "/mail/ajax/groupemail.ajax.php?groupemailID=<?php echo $_GET['emailID']; ?>&recipient="+recipient;
		
		$.get(url, function(data, status){
        	alert("Test email has been sent. Please check your Inbox. \nStatus: " + status);
    	});
		
	}
}


	
    //--></script>
<style type="text/css">
<!--
#emailBackground {
	background-color: #FFF;
	padding: 10px;
	margin-top: 10px;
	margin-bottom: 10px;
}
-->
</style>
<link href="../../css/mailDefault.css" rel="stylesheet" type="text/css" />
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
    <h1><i class="glyphicon glyphicon-envelope"></i> Preview Email</h1>
    <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
    <li> <a href="index.php" class="link_back"><i class="glyphicon glyphicon-arrow-left"></i> Manage Group Emails</a>
    </li>
    <li> <a href="recipients.php?groupemailID=<?php echo $row_rsEmail['ID']; ?>" class="link_view">View recipients</a>
    </li></ul></div></nav>
 
   
  
      <?php if(!isset($row_rsEmail['enddatetime']) && $row_rsEmail['active']==0) { // ready to send ?>

    <form action="<?php echo $editFormAction; ?>" method="post" name="makeactive" id="makeactive">
      <fieldset>
        <button name="Edit" type="button" id="Edit" onclick="window.location.href='update_group_email.php?emailID=<?php echo intval($_GET['emailID']); ?>';" class="btn btn-default btn-secondary" >Edit</button>
        <button name="save" type="button" class="btn btn-default btn-secondary" id="save"  onclick="window.location.href='index.php';" >Save for later</button>
        <button name="sendtest" type="button" class="btn btn-default btn-secondary" id="sendtest" onclick="javascript:sendTest('<?php echo $row_rsLoggedIn['email']; ?>'); return document.returnValue;" >Send test to myself</button>
        <button name="start" type="submit" class="btn btn-primary"  id="start" onclick="return confirm('This email will be sent to:\n<?php echo $row_rsEmail['usergroupID'] == 0 ? "Everyone, " : $row_rsEmail['groupname'].", "; echo $row_rsEmail['usertypeID'] == 0 ? "all ranks" : "with rank: ".$row_rsEmail['name']; ?>\n\nDo you wish to start sending this email <?php echo (date('Y-m-d',strtotime($row_rsEmail['startdatetime'])) <= date('Y-m-d')) ? "now" : "on ".date('jS M y',strtotime($row_rsEmail['startdatetime'])); ?>?');" ><?php echo ($row_rsProgress['emailsent']>0 && $row_rsProgress['emailsent']<$row_rsTotal['recipients']) ? "Resume" :  "Start"; ?> sending...</button> <?php if($totalRows_rsProgress>0) echo $row_rsProgress['emailsent']." of ".$row_rsTotal['recipients']." sent."; ?>
        <input name="active" type="hidden" id="active" value="1" />
        <input name="startdatetime" type="hidden" id="startdatetime" value="<?php echo $row_rsEmail['startdatetime']; ?>" />
        <input name="ID" type="hidden" id="ID" value="<?php echo $row_rsEmail['ID']; ?>" />
        <input type="hidden" name="MM_update" value="makeactive" />
        <input name="groupsetID" type="hidden" id="groupsetID" value="<?php echo $row_rsEmail['groupsetID']; ?>">
      </fieldset>
    </form>
    
    <?php } else if(!isset($row_rsEmail['enddatetime']) && $row_rsEmail['active']==1 && $row_rsEmail['startdatetime']> date('Y-m-d H:i:s')) { // queued to send  ?>
    <form action="<?php echo $_SERVER['REQUEST_URI'] ?>" method="post" name="stop" id="stop">
            <fieldset>
<p>This email is queued to send at <?php echo date('H:i, d M Y', strtotime($row_rsEmail['startdatetime'])); ?>. 
        <button type="submit" name="stop"  onclick="return confirm('Are you sure you want to remove this email from the queue?');" class="btn btn-default btn-secondary" >Stop</button>
      </p>
      <input type="hidden" name="submittype" value="reset" />
      <input name="emailID" type="hidden" id="emailID" value="<?php echo $row_rsEmail['ID']; ?>" /></fieldset>
    </form>
    <?php } else if(!isset($row_rsEmail['enddatetime']) && $row_rsEmail['active']==1) { // sending  ?> 
    <form action="<?php echo $_SERVER['REQUEST_URI'] ?>" method="post" name="stop" id="stop">
            <fieldset>
<p>This email is currently sending.
        <button name="pause" type="submit" id="pause" onclick="return confirm('Are you sure you want to pause sending this email?  Remaining recipients will receive this once restarted.');" class="btn btn-default btn-secondary" >Pause</button> <button type="submit" name="stop" class="btn btn-default btn-secondary" onclick="return confirm('Are you sure you want to stop sending this email? Remaining recipients will no longer receive this.');" >Stop</button>
      </p>
      <input type="hidden" name="submittype" value="reset" />
      <input name="emailID" type="hidden" id="emailID" value="<?php echo $row_rsEmail['ID']; ?>" /></fieldset>
    </form>
    
    <?php } else { // completed ?>
    <form action="<?php echo $_SERVER['REQUEST_URI'] ?>" method="post" name="reset" id="reset">
           <fieldset>
 <p>This email has already completed sending.
        <button name="reset" type="submit" class="btn btn-default btn-secondary"   onclick="return confirm('Are you sure you want to reset this email to start sending again?');" >Reset</button>
      </p>
      <input type="hidden" name="submittype" value="reset" />
      <input name="emailID" type="hidden" id="emailID" value="<?php echo $row_rsEmail['ID']; ?>" /></fieldset>
    </form>
    <?php } ?>
    <iframe width="750px" height="400px" src="email.php?emailID=<?php echo intval($_GET['emailID']); ?>" frameborder="0"></iframe>
    </div>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsEmail);

mysql_free_result($rsLoggedIn);

mysql_free_result($rsNews);

mysql_free_result($rsProgress);

mysql_free_result($rsTotal);
?>
