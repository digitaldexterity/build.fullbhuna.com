<?php require_once('../../Connections/aquiescedb.php'); ?>
<?php if (!function_exists("GetSQLValueString")) {
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

if(isset($_REQUEST['registrationID']) && intval($_REQUEST['registrationID']) > 0 ) { // registration success
mysql_select_db($database_aquiescedb, $aquiescedb);
$update = "UPDATE eventregistration SET paymentamount = ".GetSQLValueString($_REQUEST['amount'],"double")." WHERE ID = ".intval($_REQUEST['registrationID']);
mysql_query($update, $aquiescedb) or die(mysql_error());

// update all with to accepted
$update = "UPDATE eventregistration SET statusID = 1 WHERE withregistrationID  = ".intval($_REQUEST['registrationID']);
mysql_query($update, $aquiescedb) or die(mysql_error());
if(isset($_REQUEST['custom'])) {
	$url = parse_url($_REQUEST['custom']);
	parse_str($url['query']);
// redirect
if(isset($returnURL) && $returnURL!="") {
	header("location: ".$returnURL); exit;
} else {
	$msg = "You have successfully registered and paid for this event.";
	header("location: /calendar/event.php?eventID=".intval(@$eventID)."&msg=".urlencode($msg)); exit;
}
} // is custome url
} // end reg success
?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Payment Confirmation"; echo $pageTitle." | ".$site_name; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<link href="../css/calendarDefault.css" rel="stylesheet" type="text/css" />
<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" -->
    <h1 class="calheader">Payment Confirmation</h1>
    <p>There may have been a problem with your payment. Please contact us to find out more.</p>
  <!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>