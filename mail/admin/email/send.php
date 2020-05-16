<?php require_once('../../../Connections/aquiescedb.php'); ?>
<?php 
 require_once('../../includes/sendmail.inc.php');  
require_once('../../../core/includes/upload.inc.php'); 

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

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "emailform")) {
	$to = trim(preg_replace("/([\w\s]+)<([\S@._-]*)>/", " $2", $_POST['recipient'])); // some servers don't seem to  like the full name address
	$subject = trim($_POST['subject']) !="" ? $_POST['subject'] : "No subject";
	$message = (isset($_POST['message']) && strlen($_POST['message'])>0) ? stripslashes($_POST['message']) : "";
	$from = $_POST['sender'];
	$friendlyfrom = (isset($_POST['friendlyfrom']) && trim($_POST['friendlyfrom']) !=="") ? $_POST['friendlyfrom'] : $site_name;
	$attachments = array();			
	$uploaded = getUploads();	
	if (isset($uploaded) && is_array($uploaded)) {
		if(isset($uploaded["filename"]) && is_array($uploaded["filename"])) {
			foreach($uploaded["filename"] as $key => $value) {
				if(isset($uploaded["filename"][$key]["newname"]) && 
				$uploaded["filename"][$key]["newname"] !="") {
					$attachments[$key]["name"]="/Uploads/"
					.$uploaded["filename"][$key]["newname"];
					$attachments[$key]["type"]="/Uploads/"
					.$uploaded["filename"][$key]["type"];
				}
			}
		}
	}
	//print_r($attachments); die();
	if(isset($_POST['templateID']) && $_POST['templateID']>0) {
		$select = "SELECT * FROM groupemailtemplate WHERE ID = ".intval($_POST['templateID']);
		$result = mysql_query($select, $aquiescedb) or die(mysql_error());
		$row = mysql_fetch_assoc($result);
		sendMail($to,$subject,"",$from,$friendlyfrom,$message,$attachments,false,"","",$row['templatehead'],0,false,true) ;
	} else {
		sendMail($to,$subject,$message,$from,$friendlyfrom,"",$attachments);
	}
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "emailform")) {
  $insertSQL = sprintf("INSERT INTO correspondence (recipient, subject, message, createddatetime, mailfolderID, autoreply, sender, directoryID) VALUES (%s, %s, %s, %s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['recipient'], "text"),
                       GetSQLValueString($_POST['subject'], "text"),
                       GetSQLValueString($message, "text"),
                       GetSQLValueString($_POST['createddatetime'], "date"),
                       GetSQLValueString($_POST['mailfolderID'], "int"),
                       GetSQLValueString($_POST['autoreply'], "int"),
                       GetSQLValueString($_POST['sender'], "text"),
                       GetSQLValueString($_POST['directoryID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($insertSQL, $aquiescedb) or die(mysql_error());
}

$varRegionID_rsMailTemplates = "1";
if (isset($regionID)) {
  $varRegionID_rsMailTemplates = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsMailTemplates = sprintf("SELECT ID, templatename FROM groupemailtemplate WHERE statusID = 1 AND (regionID = 0 OR regionID = %s) ORDER BY templatename ASC", GetSQLValueString($varRegionID_rsMailTemplates, "int"));
$rsMailTemplates = mysql_query($query_rsMailTemplates, $aquiescedb) or die(mysql_error());
$row_rsMailTemplates = mysql_fetch_assoc($rsMailTemplates);
$totalRows_rsMailTemplates = mysql_num_rows($rsMailTemplates);

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID, email, firstname, surname FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

$colname_rsRecipient = "-1";
if (isset($_GET['recipient'])) {
  $colname_rsRecipient = $_GET['recipient'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsRecipient = sprintf("SELECT ID FROM users WHERE email = %s", GetSQLValueString($colname_rsRecipient, "text"));
$rsRecipient = mysql_query($query_rsRecipient, $aquiescedb) or die(mysql_error());
$row_rsRecipient = mysql_fetch_assoc($rsRecipient);
$totalRows_rsRecipient = mysql_num_rows($rsRecipient);

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "emailform")) {
  $insertGoTo = (isset($_POST['returnURL']) && $_POST['returnURL']!="") ? urldecode($_POST['returnURL']) : "../index.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
    $insertGoTo .= $_SERVER['QUERY_STRING'];
  }
 // die("*".$insertGoTo."*");
  header("Location: ". $insertGoTo); exit;
}
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Email reply"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" --><?php $remove_script_host = "false"; // default for tinymce is true
if(!defined("TINYMCE_CONTENT_CSS")) define("TINYMCE_CONTENT_CSS", "/core/css/global.css");
define("TINY_MCE_PLUGINS", "fullpage");
?><?php require_once('../../../core/tinymce/tinymce.inc.php'); ?>
<script src="../../../core/scripts/formUpload.js"></script>
<script src="../../../SpryAssets/SpryValidationTextField.js"></script>
<script>


$(window).load(function() {
	
	toggleTemplate(<?php echo isset($_GET['templateID']) ? intval($_GET['templateID']) : 0; ?>);
	$("#subject").focus(toggleSubject);	
});

function toggleTemplate(id) {
	if(id==0) { // no template - convert to text-only edit
		tinyMCE.triggerSave();		
		if (tinyMCE.activeEditor) {
			tinyMCE.execCommand('mceFocus', true, 'message');
			tinymce.EditorManager.execCommand('mceRemoveEditor',true, 'message'); 
		}
		document.getElementById('subject').value=document.getElementById('oldsubject').value;
	} else {  // wysiwyg editor and insert template
	
		tinymce.EditorManager.execCommand('mceAddEditor',true, 'message');
		// get any data posted sent on to ajax script for merge
		data = "templateID="+id+"<?php echo isset($_POST) ? "&merge=".base64_encode(http_build_query($_POST)) : ""; echo ($totalRows_rsRecipient>0) ? "&userID=".$row_rsRecipient['ID'] : ""; ?>";
		url = "/mail/admin/email/ajax/getTemplate.ajax.php?templateID="+id;
		
		
		$.post(url, data, function(response) {
    		
			content = response.split('<!--template break-->');
			$("#subject").val(content[0]);
        	tinyMCE.get('message').setContent(content[1]);
			
			
		});
		
	}
}

function toggleSubject() {
	if(document.getElementById('subject').value=="No subject") {
		document.getElementById('subject').value==""
	} else {
		// do nothing
	}
}


function addHeadTags(headHTML) {	
	var oldHeadHTML = $(tinyMCE.activeEditor.getDoc()).children().find('head').html(); 
	oldHeadHTML = oldHeadHTML.replace(/<!--editorHead-->.+?<!--editorHead-->/gi, ""); 
	$(tinyMCE.activeEditor.getDoc()).children().find('head').html(oldHeadHTML+"<!--editorHead-->"+headHTML+"<!--editorHead-->");
	

}
</script>
<link href="../../css/mailDefault.css" rel="stylesheet" type="text/css" />
<link href="../../../SpryAssets/SpryValidationTextField.css" rel="stylesheet" type="text/css" />
<style><!--

form#emailform input[type='text'], form#emailform textarea {
	width:100%;
}

--></style>
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
    <h1><i class="glyphicon glyphicon-envelope"></i> Send Email</h1>
    <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
      <li class="nav-item"><a href="../index.php" class="nav-link"><i class="glyphicon glyphicon-arrow-left"></i> Back to Mail</a></li>
      <li class="nav-item"><a href="../templates/index.php" class="nav-link"><i class="glyphicon glyphicon-cog"></i> Manage Templates</a></li>
    </ul></div></nav>
    <p><a href="mailto:<?php echo (isset($_REQUEST['recipient'])) ? htmlentities($_REQUEST['recipient']) : ""; ?>?subject=<?php echo "RE: ".htmlentities($_REQUEST['subject']); echo isset($_REQUEST['message']) ?  "&amp;body=".str_replace("\n","%0A",htmlentities($_REQUEST['message'])) : ""; ?>">Use Outlook or Email Client</a></p>
    <form action="<?php echo $editFormAction; ?>" method="post" enctype="multipart/form-data" name="emailform" id="emailform">
      <table class="form-table" style="100%;">
        <tr>
          <td style="33.333%;"><label for="recipient"><strong>To:</strong></label></td>
          <td colspan="2"><span id="sprytextfield1">
            <input name="recipient" type="email" multiple id="recipient" value="<?php echo (isset($_REQUEST['recipient'])) ? htmlentities($_REQUEST['recipient']) : ""; ?>" size="50" maxlength="100" class="form-control" />
            <span class="textfieldRequiredMsg">A value is required.</span></span></td>
          </tr>
        <tr>
          <td  style="33.333%;"><label for="sender"><strong>From:</strong></label></td>
          <td  style="33.333%;"><input name="friendlyfrom" type="text" id="friendlyfrom" value="<?php echo isset($_REQUEST['friendlyfrom']) ? htmlentities($_REQUEST['friendlyfrom']) : $row_rsLoggedIn['firstname']." ".$row_rsLoggedIn['surname']; ?>" placeholder="Name"  class="form-control"  /></td>
          <td><input name="sender" type="text"  id="sender"  value="<?php echo isset($_REQUEST['sender']) ? htmlentities($_REQUEST['sender']) :  $row_rsLoggedIn['email']; ?>" size="25" maxlength="100" placeholder="Email"  class="form-control" />
            </td>
        </tr>
        <tr>
          <td><label for="subject"><strong>Subject:</strong></label></td>
          <td colspan="2"><span id="sprytextfield2">
            <input name="subject" type="text"  id="subject" value="<?php echo isset($_REQUEST['subject']) ? htmlspecialchars($_REQUEST['subject']) : "No subject"; ?>" size="50" maxlength="100" class="form-control"  />
            <span class="textfieldRequiredMsg">A value is required.</span></span>            <input type="hidden" name="oldsubject" id="oldsubject" value="<?php echo isset($_REQUEST['subject']) ? htmlspecialchars($_REQUEST['subject']) : "No subject"; ?>" /></td>
          </tr>
        <tr>
          <td><label for="message"><strong>Message:</strong></label></td>
          <td colspan="2"><input name="mailfolderID" type="hidden" id="mailfolderID" value="0" />
            <input type="hidden" name="directoryID" id="directoryID" />
            <input name="autoreply" type="hidden" id="autoreply" value="0" />
            <input name="createddatetime" type="hidden" id="createddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
            
            
            <input name="returnURL" type="hidden" id="returnURL" value="<?php echo isset($_REQUEST['returnURL']) ? urlencode($_REQUEST['returnURL']) : ""; ?>" />
            <select name="templateID" id="templateID" onchange="toggleTemplate(this.value);" class="form-control"  >
              <option value="0" <?php if (!(strcmp(0, @$_REQUEST['templateID']))) {echo "selected=\"selected\"";} ?>>Send message below...</option>
              <?php if($totalRows_rsMailTemplates>0) {
do {  
?>
              <option value="<?php echo $row_rsMailTemplates['ID']; ?>"<?php if(isset($_GET['templateID']) && $row_rsMailTemplates['ID']== $_GET['templateID']) {echo "selected=\"selected\"";} ?>><?php echo $row_rsMailTemplates['templatename']; ?></option>
              <?php
} while ($row_rsMailTemplates = mysql_fetch_assoc($rsMailTemplates));
  $rows = mysql_num_rows($rsMailTemplates);
  if($rows > 0) {
      mysql_data_seek($rsMailTemplates, 0);
	  $row_rsMailTemplates = mysql_fetch_assoc($rsMailTemplates);
  }
			  }
?>
            </select></td>
          </tr>
        <tr>
          <td colspan="3"><textarea name="message" id="message" rows="20"  class="form-control" ><?php
		  
		  $message = isset($_REQUEST['message']) ? stripslashes($_REQUEST['message']) : "";
		  $message = isset($_REQUEST['reply']) ?  "\n\n\n".$_REQUEST['recipient']." wrote:\n\n> ".str_replace("\n","\n> ",$message) : $message; echo $message; ?></textarea><div id="preview" style="padding:10px; background-color:#FFF;"></div></td>
        </tr>
        <tr>
          <td colspan="3"><label>Attachment:
            
            <input type="file" name="filename[0]" id="filename0" />
            </label></td>
        </tr>
        <tr>
          <td colspan="3"><button type="submit"  class="btn btn-primary">Send</button></td>
        </tr>
      </table>
      <input type="hidden" name="MM_insert" value="emailform" />
    </form>
    <script>
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1");
var sprytextfield2 = new Spry.Widget.ValidationTextField("sprytextfield2");
    </script></div>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsMailTemplates);

mysql_free_result($rsLoggedIn);

mysql_free_result($rsRecipient);
?>
