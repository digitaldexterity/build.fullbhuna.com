<?php require_once('../../core/includes/sslcheck.inc.php'); ?>
<?php require_once('../../Connections/aquiescedb.php'); ?><?php require_once('../includes/userfunctions.inc.php'); ?>
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

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT firstname, surname, email FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

$colname_rsThisUser = "-1";
if (isset($_GET['userID'])) {
  $colname_rsThisUser = $_GET['userID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsThisUser = sprintf("SELECT firstname, surname, email FROM users WHERE ID = %s", GetSQLValueString($colname_rsThisUser, "int"));
$rsThisUser = mysql_query($query_rsThisUser, $aquiescedb) or die(mysql_error());
$row_rsThisUser = mysql_fetch_assoc($rsThisUser);
$totalRows_rsThisUser = mysql_num_rows($rsThisUser);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = "SELECT * FROM preferences";
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);
?>
<?php // Security is provided here by an MD5 ky made up of the userID and the site root sent from the sending page
$alert = "";
$users = explode(",",$_GET['userID']); // allow multiple users
$to = "";
mysql_select_db($database_aquiescedb, $aquiescedb);
foreach($users as $userID) {
	$select = "SELECT email FROM users WHERE ID = ".intval($userID);			
	$result = mysql_query($select, $aquiescedb) or die(mysql_error());			$row = mysql_fetch_assoc($result);
	if(isset($row['email'])) { // is an email
		$to .= $row['email'].", ";
	} // end is an email
} // end for each
$to = trim($to,", ");
if(isset($_POST['message'])) { // mail sent
	
	if(md5($_GET['userID'].PRIVATE_KEY) == $_POST['key']) { // valid key		
		if(strpos($to,"@")>1) { // to valid
			if(validEmail($_POST['email'])) {	// from valid
				require_once('../../mail/includes/sendmail.inc.php');
				$subject = "Contact from web site";
				$from = $_POST['email'];
				$message = $_POST['message'];
				sendMail($to,$subject,$message,$from);
				$msg = "Your message has been sent.";
			} else { // no valid email
				$msg = "Your email does not appear to be valid.";
			}
		} else {
	$msg = "This user does not appear to have an email address.";
		} // end no email
		
	} // end key validated
	else { 
	die("There was a security problem sending this message. Please contact site administrator.");
	} // end key invalid
	
} // end posted message
?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php  $pageTitle = "Send message to ".$row_rsThisUser['firstname']." ".$row_rsThisUser['surname']; echo $pageTitle." | ".$site_name;?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<meta name="robots" content="noindex,nofollow"/>
<script src="../../SpryAssets/SpryValidationTextField.js"></script>
<script src="../../SpryAssets/SpryValidationTextarea.js"></script>
<link href="../../SpryAssets/SpryValidationTextField.css" rel="stylesheet"  />
<link href="../../SpryAssets/SpryValidationTextarea.css" rel="stylesheet"  />
<link href="../css/membersDefault.css" rel="stylesheet"  />
<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" -->
<?php if((isset($_GET['key']) && $_GET['key'] == md5($_GET['userID'].PRIVATE_KEY))) { ?>
    <h1 class="memberheader">Send message to <?php $recipientname = isset($_REQUEST['recipientname']) ? $_REQUEST['recipientname'] : $row_rsThisUser['firstname']." ".$row_rsThisUser['surname']; 
	echo htmlentities($recipientname); ?></h1>
  <?php require_once('../../core/includes/alert.inc.php'); ?>
    
	<?php if($row_rsPreferences['usercontactform']==1) { // contact forms available 
	if(strpos($to,"@")>1) { // has email ?>
   
   
    <form action="index.php?userID=<?php echo intval($_GET['userID']); ?>&amp;key=<?php echo htmlentities($_GET['key']); ?>" method="post" name="form1" id="form1">
      <table border="0" cellpadding="2" cellspacing="2" class="form-table">
        <tr>
          <td align="right" valign="top">Your name:</td>
          <td><span id="sprytextfield1">
            <label>
              <input name="fullname" type="text"  id="fullname" value="<?php echo isset($_POST['fullname']) ? htmlentities($_POST['fullname']) :  $row_rsLoggedIn['firstname']." ".$row_rsLoggedIn['surname']; ?>" size="50" maxlength="50" />
            </label>
          <span class="textfieldRequiredMsg">A name is required.</span></span></td>
        </tr>
        <tr>
          <td align="right" valign="top">Your email:</td>
          <td><span id="sprytextfield2">
            <label>
              <input name="email" type="text"  id="email" value="<?php echo isset($_POST['email']) ? htmlentities($_POST['email']) : $row_rsLoggedIn['email']; ?>" size="50" maxlength="50" />
            </label>
          <span class="textfieldRequiredMsg">An email is required.</span></span></td>
        </tr>
        <tr>
          <td align="right" valign="top">Message:</td>
          <td><span id="sprytextarea1">
            <label>
              <textarea name="message" id="message" cols="80" rows="10"><?php echo isset($_POST['message']) ? htmlentities($_POST['message']) : ""; ?></textarea>
            </label>
          <span class="textareaRequiredMsg">A message is required.</span></span></td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td><label>
            <button name="send" type="submit" id="send" >Send message</button>
            <input name="key" type="hidden" id="key" value="<?php echo md5($_GET['userID'].PRIVATE_KEY); ?>" />
            
          </label></td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
        </tr>
      </table>
    </form><?php } else { // no email ?>
     <p class="alert alert-info">Sorry, this user does not have an email address so cannot be sent a message.</p><?php } ?>
    <?php } // end contact form available
	else { ?>
    <p class="alert alert-info">User messages are currently unavailable.</p>
    
    <?php }
	} // end key correct ?>
    <script>
<!--
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1");
var sprytextfield2 = new Spry.Widget.ValidationTextField("sprytextfield2");
var sprytextarea1 = new Spry.Widget.ValidationTextarea("sprytextarea1");
//-->
    </script>
  <!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsThisUser);

mysql_free_result($rsPreferences);
?>
