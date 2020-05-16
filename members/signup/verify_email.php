<?php require_once('../../Connections/aquiescedb.php'); 

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

if (!isset($_SESSION)) {
  session_start();
}

$colname_rsUser = "-1";
if (isset($_GET['username'])) {
  $colname_rsUser = (get_magic_quotes_gpc()) ? $_GET['username'] : addslashes($_GET['username']);
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsUser = sprintf("SELECT ID, usertypeID, firstname, username, password FROM users WHERE username = '%s'", $colname_rsUser);
$rsUser = mysql_query($query_rsUser, $aquiescedb) or die(mysql_error());
$row_rsUser = mysql_fetch_assoc($rsUser);
$totalRows_rsUser = mysql_num_rows($rsUser);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = "SELECT license_key, preferences.addressrequired FROM preferences";
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);
?>
<?php 
if (isset($_GET['key']) && md5($row_rsUser['username'].$row_rsPreferences['license_key']) == $_GET['key']) { //email verified
	$rank = ($row_rsUser['usertypeID']==0) ? 1 : $row_rsUser['usertypeID']; // upgrade to 1 if 0 otherwise leave as is
	$updateSQL = "UPDATE users SET usertypeID=".$rank.", emailverified = 1 WHERE username='".$colname_rsUser."'"; //update user from pending
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());

	if (isset($_GET['PrevUrl']) && $_GET['PrevUrl'] != "") {
		$_SESSION['PrevUrl'] = $_GET['PrevUrl']; 
	}

	// log user in - this code must be the same as confirmation page code....
	$_SESSION['MM_Username'] = $row_rsUser['username'];
	$_SESSION['MM_UserGroup'] = 1;

	//redirect to either previous URL or members page
	$redirectURL = isset($_SESSION['PrevUrl']) ? $_SESSION['PrevUrl'] : "/members/index.php?newuser=true";

	if(isset($_SESSION['PrevUrl'])) { 
		unset($_SESSION['PrevUrl']); 
	}
	header("location: ".$redirectURL); exit;
}

?>

<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Verify Email Address"; echo $pageTitle." | ".$site_name; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" -->
	     <h1>Email Verification</h1>
	  
		 <h2>Oops!</h2>
	     <p>Sorry, we can not verify your identity at this time. Please <a href="/contact/index.php">contact our admin team</a>. </p>
		 
    <!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsUser);

mysql_free_result($rsPreferences);
?>