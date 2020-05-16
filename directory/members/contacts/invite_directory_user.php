<?php require_once('../../../Connections/aquiescedb.php'); ?><?php require_once('../../../mail/includes/sendmail.inc.php'); ?>
<?php require_once('../../../members/includes/userfunctions.inc.php'); 
// flexible add user system
/* VARIABLE PASSING :
$_GET['directoryID'] - associate user with a directory entry

*/
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "1,2,3,4,5,6,7,8,9,10";
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
$varDirectoryID_rsIsAuthorised = "-1";
if (isset($_GET['directoryID'])) {
  $varDirectoryID_rsIsAuthorised = $_GET['directoryID'];
}
$varUsername_rsIsAuthorised = "-1";
if (isset($_SESSION['MM_Username'])) {
  $varUsername_rsIsAuthorised = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsIsAuthorised = sprintf("SELECT directoryuser.ID FROM directoryuser LEFT JOIN users ON (directoryuser.userID = users.ID) WHERE directoryID = %s AND users.username = %s", GetSQLValueString($varDirectoryID_rsIsAuthorised, "int"),GetSQLValueString($varUsername_rsIsAuthorised, "text"));
$rsIsAuthorised = mysql_query($query_rsIsAuthorised, $aquiescedb) or die(mysql_error());
$row_rsIsAuthorised = mysql_fetch_assoc($rsIsAuthorised);
$totalRows_rsIsAuthorised = mysql_num_rows($rsIsAuthorised);

if($totalRows_rsIsAuthorised==0 && $_SESSION['MM_UserGroup']<8) {
	die("You are not authorised to add contacts.");
}

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = "SELECT * FROM preferences";
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID, users.firstname, users.surname, users.email FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

$varDirectoryID_rsDirectory = "-1";
if (isset($_GET['directoryID'])) {
  $varDirectoryID_rsDirectory = $_GET['directoryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsDirectory = sprintf("SELECT ID, directory.name FROM directory WHERE directory.ID = %s", GetSQLValueString($varDirectoryID_rsDirectory, "int"));
$rsDirectory = mysql_query($query_rsDirectory, $aquiescedb) or die(mysql_error());
$row_rsDirectory = mysql_fetch_assoc($rsDirectory);
$totalRows_rsDirectory = mysql_num_rows($rsDirectory);

if(isset($_POST['emails']) && strpos($_POST['emails'],"@")>1) {
	$emails = explode(",",$_POST['emails']);
	$validemails = array();//print_r($validemails); die();
	foreach($emails as $key => $email) {
		if(validEmail($email)) {
			array_push($validemails, $email);
		}
	}
	if(count($validemails)>0) {
		$to = implode(",",$validemails);
		$subject = "Invitation to join ".$row_rsDirectory['name'];
		$token = md5($row_rsDirectory['ID'].PRIVATE_KEY);
		$message = $row_rsLoggedIn['firstname']." has invited you to become a member of ".$site_name." and also be part of the entry for ".$row_rsDirectory['name']."\n\n";
		$message .= "You can only join by clicking on the link below:\n\n";
		$message .= (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == "on") ? "https://" : "http://";
		$message .= $_SERVER['HTTP_HOST']."/directory/members/join_directory.php?directoryID=".$row_rsDirectory['ID']."&token=".$token;

		$from = $row_rsLoggedIn['email'];
		$friendlyfrom = $row_rsLoggedIn['firstname']." " .$row_rsLoggedIn['surname'];
		sendMail($to, $subject, $message, $from, $friendlyfrom);
		$msg = "An email has been sent to the recipients you listed inviting them to join ".$site_name." and also be a contact for ".$row_rsDirectory['name'];
	} else {
		$submit_error = "There were no valid emails to send the email to.";
	}
}


?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Invite contacts - ".$row_rsDirectory['name']; echo $pageTitle." | ".$site_name; ?><</title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script src="/SpryAssets/SpryTabbedPanels.js"></script>
<script src="../../../SpryAssets/SpryValidationTextarea.js"></script>
<link href="/SpryAssets/SpryTabbedPanels.css" rel="stylesheet"  />
<style><!--
<?php if($row_rsPreferences['usesalutation'] != 1) { ?>.salutation { display:none; } <?php } ?>
--></style>
<link href="../../../SpryAssets/SpryValidationTextarea.css" rel="stylesheet" >
<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../../../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" -->
            <h1 class="directoryheader">Invite <?php echo $row_rsDirectory['name']; ?> Contacts</h1>
            <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
              <li><a href="index.php?directoryID=<?php echo intval($_GET['dircetoryID']); ?>" class="link_undo"><i class="glyphicon glyphicon-arrow-left"></i> Contacts</a></li>
            </ul></div></nav>
             <?php if(isset($msg)) { ?><p class="message alert alert-info" role="alert"><?php echo $msg; ?></p><?php } ?>
<?php if(isset($submit_error)) { ?><p class="alert alert-danger" role="alert"><?php echo $submit_error; ?></p><?php } ?>
            <form action="<?php echo $editFormAction; ?>" method="POST" name="form1" id="form1">
              <span id="sprytextarea1">
              <label>Enter the email addresses of each contact separated by commas: <br>
                <textarea name="emails" id="emails" cols="40" rows="10"></textarea>
              </label>
              
              <span class="textareaRequiredMsg">A value is required.</span></span>
              <p><input type="submit" name="sunmitbutton" id="sunmitbutton" value="Submit"></p>
              <p>Click submit and we will send them an email with a special link that allows them to sign up to the site as well as being associated with your organisation.</p>
            </form>
<script>
<!--
var sprytextarea1 = new Spry.Widget.ValidationTextarea("sprytextarea1", {hint:"e.g. admin@google.com, infor@microsoft.com, kim@apple.com"});
//-->
            </script>
  <!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../../../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsPreferences);

mysql_free_result($rsDirectory);

mysql_free_result($rsIsAuthorised);
?>
