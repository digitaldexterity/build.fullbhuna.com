<?php require_once('../Connections/aquiescedb.php'); ?><?php require_once('includes/sendmail.inc.php');
require_once('../core/seo/includes/seo.inc.php');  ?><?php require_once('../core/includes/framework.inc.php'); ?>
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
$varEmailID_rsEmail = "-1";
if (isset($_GET['emailID'])) {
  $varEmailID_rsEmail = $_GET['emailID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsEmail = sprintf("SELECT groupemail.usertypeID, subject, message, templateID, groupemail.templateID, groupemail.html, groupemail.showunsubscribe, groupemail.head, groupemail.bodytag FROM groupemail WHERE groupemail.ID = %s", GetSQLValueString($varEmailID_rsEmail, "int"));
$rsEmail = mysql_query($query_rsEmail, $aquiescedb) or die(mysql_error());
$row_rsEmail = mysql_fetch_assoc($rsEmail);
$totalRows_rsEmail = mysql_num_rows($rsEmail);

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT users.ID, users.usertypeID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

$varEmail_rsThisUser = "-1";
if (isset($_GET['email'])) {
  $varEmail_rsThisUser = $_GET['email'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsThisUser = sprintf("SELECT users.ID, users.salutation, users.firstname, users.surname, users.email FROM users WHERE users.email = %s", GetSQLValueString($varEmail_rsThisUser, "text"));
$rsThisUser = mysql_query($query_rsThisUser, $aquiescedb) or die(mysql_error());
$row_rsThisUser = mysql_fetch_assoc($rsThisUser);
$totalRows_rsThisUser = mysql_num_rows($rsThisUser);

$colname_rsTemplate = "-1";
if (isset($_GET['templateID'])) {
  $colname_rsTemplate = $_GET['templateID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsTemplate = sprintf("SELECT * FROM groupemailtemplate WHERE ID = %s", GetSQLValueString($colname_rsTemplate, "int"));
$rsTemplate = mysql_query($query_rsTemplate, $aquiescedb) or die(mysql_error());
$row_rsTemplate = mysql_fetch_assoc($rsTemplate);
$totalRows_rsTemplate = mysql_num_rows($rsTemplate);

?>
<!doctype html>
<html>
<head>
<title><?php $pageTitle = "View Email - ".$row_rsEmail['subject']; echo $pageTitle." | ".$site_name; ?></title>
<meta charset="utf-8" /><meta name="robots" content="noindex,nofollow" />
<?php echo isset($row_rsEmail['head']) ? $row_rsEmail['head'] : ""; ?>
</head>
<?php if(strpos($row_rsEmail['bodytag'],"<body")!==false) {
	echo $row_rsEmail['bodytag'];
} else { ?>
<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" yahoo="fix" style="width: 100%; background-color: #ffffff; margin:0; padding:0; -webkit-font-smoothing: antialiased;font-family: Arial, sans-serif;">
<?php  }
if($totalRows_rsEmail>0) { // is group email
	if ($row_rsLoggedIn['usertypeID'] >= $row_rsEmail['usertypeID'] || (isset($_GET['email']) && $_GET['email'] !="" &&  md5($_GET['email'].@PRIVATE_KEY) == $_GET['token'])) { // query result exists and access level OK , so email available to user
 		if (isset($row_rsEmail['html']) && $row_rsEmail['html']!="") { // HTML email 
			$message =  $row_rsEmail['html']; 
		 } else { //plain text email
 			$message = nl2br($row_rsEmail['message']);
		}
	} else { // not available
 		$message = "<p align=\"center\">Sorry, this email is not available to you.";
		if(!isset($_SESSION['MM_Username'])) { 
			echo "You may need to <a href=\"/login/index.php?accesscheck=".
			urlencode(htmlentities($_SERVER['REQUEST_URI'], ENT_COMPAT, "UTF-8"))."\">log in</a>."; 
		} 
		echo "</p>"; 		
	} // end not available
} else { // is template
	$message = isset($row_rsTemplate['templateHTML']) ? $row_rsTemplate['templateHTML'] : nl2br($row_rsTemplate['templatemessage']);
}
// for security only merge if good token
if(isset($_GET['token']) && $_GET['token'] == md5($_GET['email'].@PRIVATE_KEY)) {
	$message = mergeEmail($message, $_GET['email']);
}
				
echo $message;

if($row_rsEmail['showunsubscribe']==1) { ?>
<p align="center" class="small">If you wish to stop receiving our emails, please <a href="<?php echo getProtocol()."://". $_SERVER['HTTP_HOST']."/"."mail/unsubscribe.php?email=".urlencode(htmlentities($_GET['email'], ENT_COMPAT, "UTF-8")); ?>">unsubscribe here</a>.</p>
<?php } ?>
<?php trackPage($pageTitle); ?>
</body>
</html>
<?php
mysql_free_result($rsEmail);

mysql_free_result($rsLoggedIn);

mysql_free_result($rsThisUser);

mysql_free_result($rsTemplate);
?>
