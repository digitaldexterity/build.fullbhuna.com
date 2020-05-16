<?php require_once('../../Connections/aquiescedb.php'); ?>
<?php require_once('../../core/includes/adminAccess.inc.php'); ?><?php require_once('../includes/sendmail.inc.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "7,8,9,10";
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



$regionID = (isset($regionID) && intval($regionID)>0) ? intval($regionID) : 1;
$deletedate = isset($_GET['deleteall']) ? date('Y-m-d') : date('Y-m-d', strtotime("1 YEAR AGO"));

$delete = "DELETE FROM correspondence WHERE regionID = ".$regionID." AND DATE(createddatetime) <= ".GetSQLValueString($deletedate, "date");
mysql_query($delete, $aquiescedb) or die(mysql_error());
$numrows = mysql_affected_rows();
//die($delete."=".$numrows);

if(isset($_POST['action']) && $_POST['action'] == "delete") {
	// delete messages
	mysql_select_db($database_aquiescedb, $aquiescedb);
	if(isset($_SESSION['checkbox'])) {
		foreach($_SESSION['checkbox'] as $key => $value) {
			$update = "UPDATE correspondence SET mailfolderID = 2 WHERE ID = ".intval($value);
			mysql_query($update, $aquiescedb) or die(mysql_error());
		}
	unset($_SESSION['checkbox']);
	}
}
if(isset($_POST['action']) && $_POST['action'] == "resend") {
	// resend messages
	$count=0;
	mysql_select_db($database_aquiescedb, $aquiescedb);
	if(isset($_SESSION['checkbox'])) {
		foreach($_SESSION['checkbox'] as $key => $value) {
			$select = "SELECT * FROM correspondence WHERE  ID = ".intval($value);
			$result  = mysql_query($select, $aquiescedb) or die(mysql_error().$select);
			if(mysql_num_rows($result)>0) {
				$correspondence = mysql_fetch_assoc($result);
				$subject = $correspondence['subject']." [Resend]";
				$message = "RESEND OF ORIGINAL MESSAGE FROM ".date('d M Y', strtotime($correspondence['sentdatetime'])).":\n\n";
				$message .= $correspondence['message'];
				$friendlyfrom = $correspondence['sendername'];
				$from = "";
				$replyto = $correspondence['sender'];
				$to = $correspondence['recipient'];
				//$to = "paul@preproductions.com";
				$cc = "";
				$bcc = "";
				sendMail($to,$subject, $message ,$from,$friendlyfrom,"","",false,$cc,$bcc,"",0,false,false,"", 0, 0, $replyto);
				//sendMail($to,$subject, $message ,$from,$friendlyfrom);
		$count++;
			}
		}
	unset($_SESSION['checkbox']);
	}
	$msg = $count." messages resent.";
}

if(isset($_POST['deleteID']) && intval($_POST['deleteID'])>0) {
	// delete from view email page
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$update = "UPDATE correspondence SET mailfolderID = 2 WHERE ID = ".intval($_POST['deleteID']);
	mysql_query($update, $aquiescedb) or die(mysql_error());
}

$_GET['mailfolderID'] = isset($_GET['mailfolderID']) ? $_GET['mailfolderID'] : (isset($_COOKIE['mailfolderID']) ? $_COOKIE['mailfolderID'] : 1);


setcookie('mailfolderID', $_GET['mailfolderID'], time() + (86400 * 365), "/"); // 86400 = 1 day

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
?>
<?php
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsRecipients = "SELECT ID, recipient FROM mailrecipient WHERE statusID = 1 ORDER BY recipient ASC";
$rsRecipients = mysql_query($query_rsRecipients, $aquiescedb) or die(mysql_error());
$row_rsRecipients = mysql_fetch_assoc($rsRecipients);
$totalRows_rsRecipients = mysql_num_rows($rsRecipients);

$_GET['recipientID'] = isset($_GET['recipientID']) ? htmlentities($_GET['recipientID']) : 0;


?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Mail Manager"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<link href="../css/mailDefault.css" rel="stylesheet" type="text/css" />
<script src="/core/scripts/checkbox/checkboxes.js"></script>
<script>
var checkboxForm = 'mailform';
var useCheckboxSession = true;

$(document).ready(function(){
	getStoredMail();
	setTimeout("getPOP()" ,1000); // wait for 1 second before getting POP
	setInterval("getPOP()" ,150000); // repeat every 5 mins
	toggleAdvanced();
	$("#advanced").click(toggleAdvanced);
	
});

function getStoredMail() {
	
	$("#viewmail").load("includes/view_mail.inc.php?mailfolderID="+document.getElementById('mailfolderID').value+"&mailsearch="+document.getElementById('mailsearch').value+"&startdate="+document.getElementById('startdate').value+"&enddate="+document.getElementById('enddate').value+"&recipientID="+document.getElementById('recipientID').value+"&pageNum_rsCorrespondence=<?php echo isset($_GET['pageNum_rsCorrespondence']) ? $_GET['pageNum_rsCorrespondence'] : 0; ?>&ajaxcallURL=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>", checkboxInit);
	
}

function getPOP() {
	gets = getUrlVars();
	if(!(gets['pageNum_rsCorrespondence'])) { 
		$("#results").html('<img src="../../core/images/loading_16x16.gif" alt="Loading" width="16" height="16" border="0" style="vertical-align:middle;">Checking for new mail...');
		$("#results").load("includes/get_pop.inc.php","",getStoredMail);
	}
}

function toggleAdvanced(){
	if(document.getElementById('advanced').checked) {
		$("#advancedSearch").show();
	} else {
		$("#advancedSearch").hide();
	}
}


</script>
<style>
<!--
<?php if(!isset($_GET['mailfolderID']) || $_GET['mailfolderID']==1) {
 echo" .recipient {
 display:none;
}
";
}
?>
-->
</style>
<?php require_once(SITE_ROOT.'core/scripts/checkbox/checkboxsession.inc.php'); ?>
<link href="../../SpryAssets/SpryValidationTextField.css" rel="stylesheet" type="text/css" />
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
      <div class="page mail">
        <?php require_once('../../core/region/includes/chooseregion.inc.php'); ?>
        <h1><i class="glyphicon glyphicon-envelope"></i> Mail Manager </h1>
     <?php require_once('../../core/includes/alert.inc.php'); ?>
        <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
          <?php if($row_rsMailPrefs['enableGroupEmail']==1) { ?>
          <li class="rank8 nav-item"><a href="groupemail/index.php" class="nav-link"><i class="glyphicon glyphicon-user"></i> Group Email</a></li>
          <li class="rank8 nav-item"><a href="letters/index.php" class="nav-link"><i class="glyphicon glyphicon-exclamation-sign"></i> Notifications</a></li>
          <li class="rank8 nav-item"><a href="reminders/index.php" class="nav-link"><i class="glyphicon glyphicon-time"></i> Sheduled Messages</a></li>
          <?php } ?>
          <?php if(is_readable(SITE_ROOT."forms/admin/index.php")) { ?>
          <li class="rank8 nav-item"><a href="/forms/admin/index.php" class="nav-link"><i class="glyphicon glyphicon-th-list"></i> Forms</a></li>
          <?php } ?>
          <li class="rank8 nav-item"><a href="templates/index.php" class="nav-link"><i class="glyphicon glyphicon-file"></i> Templates</a></li>
          <li class="rank8"><a href="options/contact.php" class="nav-link"><i class="glyphicon glyphicon-envelope"></i> Contact Form</a></li>
          
          <li class="rank8 nav-item"><a href="/mail/sms/admin/index.php" class="nav-link"><i class="glyphicon glyphicon-phone"></i> SMS</a></li>
          <li class="rank8 nav-item"><a href="options/index.php" class="nav-link"><i class="glyphicon glyphicon-cog"></i> Advanced</a></li>
        </ul></div></nav>
        <form>
          <fieldset class="form-inline">
            <button type="button" name="checkmail" id="checkmail" onclick="document.location.href='index.php';" class="btn btn-default btn-secondary" >Check mail</button>
            <button type="button" name="checkmail" id="checkmail"  onclick="document.location.href='email/send.php';" class="btn btn-default btn-secondary" >New email</button>
            <label>Search
              <input name="mailsearch" type="text" id="mailsearch" size="20" maxlength="100" value="<?php echo isset($_GET['mailsearch']) ? htmlentities($_GET['mailsearch']): ""; ?>" class="form-control"/>
            </label>
            <label>
              <select name="mailfolderID" id="mailfolderID" onchange="this.form.submit()" class="form-control">
                <option value="0" <?php if (!(strcmp(0,  @$_GET['mailfolderID']))) {echo "selected=\"selected\"";} ?>>Sent Items</option>
                <option value="1" <?php if (!(strcmp(1,  @$_GET['mailfolderID']))) {echo "selected=\"selected\"";} ?>>Inbox</option>
                <option value="2" <?php if (!(strcmp(2,  @$_GET['mailfolderID']))) {echo "selected=\"selected\"";} ?>>Deleted Items</option>
              </select>
            </label>
            <button type="submit" class="btn btn-primary">Go</button>
            <label>
              <input type="checkbox" name="advanced" id="advanced" <?php if(isset($_GET['advanced'])) {echo "checked=\"checked\"";  }; ?> />
              Advanced</label>
            <div id="advancedSearch">
              <label>From:
                <input name="startdate" id="startdate" type="hidden" value="<?php $setvalue = isset($_GET['startdate']) ? htmlentities($_GET['startdate']) : ""; echo $setvalue; $inputname = "startdate"; ?>" />
              </label>
              <?php require('../../core/includes/datetimeinput.inc.php'); ?>
              <label>Until:
                <input name="enddate" id="enddate" type="hidden"  value="<?php $setvalue = isset($_GET['enddate']) ? htmlentities($_GET['enddate']) : ""; echo $setvalue; $inputname = "enddate"; ?>" />
              </label>
              <?php require('../../core/includes/datetimeinput.inc.php'); ?>
              <select name="recipientID" id="recipientID" class="form-control">
                <option value="0" <?php if (!(strcmp(0, isset($_GET['recipientID']) ? htmlentities($_GET['recipientID']) : ""))) {echo "selected=\"selected\"";} ?>>To recipient...</option>
                <?php
do {  
?>
                <option value="<?php echo $row_rsRecipients['ID']?>"<?php if (!(strcmp($row_rsRecipients['ID'], isset($_GET['recipientID']) ? htmlentities($_GET['recipientID']) : ""))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsRecipients['recipient']?></option>
                <?php
} while ($row_rsRecipients = mysql_fetch_assoc($rsRecipients));
  $rows = mysql_num_rows($rsRecipients);
  if($rows > 0) {
      mysql_data_seek($rsRecipients, 0);
	  $row_rsRecipients = mysql_fetch_assoc($rsRecipients);
  }
?>
              </select>
              <a href="email/export.php">Export</a></div>
          </fieldset>
        </form>
        <form action="index.php?pageNum_rsCorrespondence=0" method="post" name="mailform" id="mailform">
          <div id="viewmail"> </div>
          <div id="status">Status: <span id="results">Idle</span> </div>
          <?php if(isset($row_rsMailPrefs['lastViewed']) && $_SESSION['MM_UserGroup'] <10) { ?>
          <p>Last viewed: <?php echo date('d M Y, H:i',strtotime($row_rsMailPrefs['lastViewed'])); ?> </p>
          <?php }
$update = "UPDATE mailprefs SET lastViewed = '".date('Y-m-d H:i:s')."'";
mysql_query($update, $aquiescedb) or die(mysql_error());
?>
          <p>With selected:
            <button type="submit"  onclick="if(confirm('Are you sure you want to delete these messages?')) { document.getElementById('action').value='delete'; } else { return false; }" class="btn btn-default btn-secondary">Delete</button>
            
             <button type="submit"  onclick="if(confirm('Are you sure you want to send these messages again?')) { document.getElementById('action').value='resend'; } else { return false; }" class="btn btn-default btn-secondary">Resend</button>
             
         
          <input name="action" id="action" type="hidden" /> All messages are automatically deleted after 1 year  <a href="index.php?deleteall=true" class="btn btn-danger rank10" onClick="return confirm('Are you sure you want to delete all messages?\n\nThis cannot be undone.');">Delete All</a></p>
        </form>
      </div>
      <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsRecipients);
?>
