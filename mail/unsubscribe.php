<?php require_once('../Connections/aquiescedb.php'); ?>
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

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

if(isset($_GET['confirmed']) || isset($_GET['resubscribe'])) {
	 if(isset($_GET['token']) && (md5($_GET['email'].PRIVATE_KEY) == $_GET['token'] ||  $_GET['token'] == $_SESSION['token'])) { 
	$optin = isset($_GET['resubscribe']) ? 1 : 0;
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$update = "UPDATE users SET emailoptin = ".$optin." WHERE email = ".GetSQLValueString($_GET['email'],"text");
	mysql_query($update, $aquiescedb) or die(mysql_error());
	if(mysql_affected_rows()) {
	$userID = isset($row_rsLoggedIn['ID']) ? $row_rsLoggedIn['ID'] : 0;
	$insert = "INSERT INTO groupemailoptoutlog (email, createdbyID, createddatetime) VALUES (".GetSQLValueString($_GET['email'],"text").",".GetSQLValueString($userID,"int").",NOW())";
	mysql_query($insert, $aquiescedb) or die(mysql_error());
	$updated = true;
	} else {
		$error = "Sorry, we couldn't find the email address ".htmlentities($_GET['email'], ENT_COMPAT,"UTF-8")." on our mailing list. You may have unsubscribed already.";
		unset($_GET['email']);
	}
	 } else {
		 $error = "Sorry, there was a technical issue removing  ".htmlentities($_GET['email'], ENT_COMPAT,"UTF-8")." from our mailing list. Please contact us.";
		 unset($_GET['email']);
	 }
}
?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php  $pageTitle = "Email Unsubscribe"; $pageTitle .= isset($confirmed) ? " - Success" : ""; echo $pageTitle." | ".$site_name;?></title>
<!-- InstanceEndEditable -->
<?php require_once('../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<meta name="robots" content="noindex,nofollow" />
<script>
$(document).ready(function(e) {
    // run an automatic unsubscribe with javascript only as this could be triggered by mail servers otherwise
	<?php if(!isset($updated) && isset($_GET['email']) && isset($_GET['token']) && (md5($_GET['email'].PRIVATE_KEY) == $_GET['token'] ||  $_GET['token'] == $_SESSION['token'])) { ?>
	if(confirm('Are you sure you want to unsubscribe from our mailing list?')) {
	document.location.href='unsubscribe.php?email=<?php echo urlencode($_GET['email']); ?>&token=<?php echo urlencode($_GET['token']); ?>&confirmed=true';
	}
	<?php }	?>
	
});
	</script>
<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" -->
        <div class="container pageBody"><?php require_once('../core/includes/alert.inc.php'); ?>
        <?php if(isset($updated)) { 
		if(isset($_GET['resubscribe'])) { ?>
        <h1>You're back!</h1>
        <p>We've resubscribed you to our mailing list.</p>
        <?php } else  { ?>
        <h1>Unsubscribed</h1>
    <h2>You have successfully been removed from our mailing list.</h2>
    <p>We will no longer send emails to: <?php echo htmlentities($_GET['email']); ?></p>
    <p>If you did this in error, please <a href="unsubscribe.php?email=<?php echo urlencode($_GET['email']); ?>&token=<?php echo urlencode($_GET['token']); ?>&resubscribe=true">click here to immediately resubscribe.</a></p>
    <p>If you are a member of our site, you can manage your mailing list preferences by <a href="/members/profile/update_profile.php">updating your profile.</a></p>
    <p>Please note that on rare occasions, settings may take a few days to update.</p>
    <?php } // end unsubscribe
	} else { // not confirmed yet ?>
    
    
    <h1>Unsubscribe?</h1>
    <h2>We'd be sorry to see you go...</h2>
    <?php if(isset($_GET['email'])) { // we know email
    if(isset($_GET['token']) && (md5($_GET['email'].PRIVATE_KEY) == $_GET['token'] ||  $_GET['token'] == $_SESSION['token'])) { 
	// verify token from external source or this site ?>
    
    <p>To <em>stop</em> receiving our emails, <a href="unsubscribe.php?email=<?php echo urlencode($_GET['email']); ?>&token=<?php echo urlencode($_GET['token']); ?>&confirmed=true">click here to confirm you want to unsubscribe</a>.</p>
    <p>To <em>continue</em> getting news updates and offers from us, you don't need to do anything - just sit back and wait and there'll be another instalment along soon!</p>
    <?php } else { // token issue ?>
    <p>We're sorry, there was a technical problem  automatically unsubscribing you. Please contact us.</p>
    <?php } // end token issue
	} else { // we don't know email ?><form  method="get"><input name="token" type="hidden" value="<?php $token = md5(time().PRIVATE_KEY); $_SESSION['token'] = $token; echo $token; ?>"><p><label for="email">Please enter the email address you wish to unsubscribe from our lists:</label></p><input name="email" type="email"  value="" placeholder="Your email"> <input  type="submit" value="Unsubscribe me">
      <input name="confirmed" type="hidden" id="confirmed" value="true">
    </form>
    <?php } // end we  don't know email
	} // end unsubscibe confirmation ?></div>
  <!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html><?php
mysql_free_result($rsLoggedIn);
?>

