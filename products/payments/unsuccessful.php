<?php require_once('../../Connections/aquiescedb.php'); ?>
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



if(isset($_GET["VendorTxCode"])) {
	
	$strVendorTxCode=$_GET["VendorTxCode"];
	$strSQL = "SELECT * FROM productorders where VendorTxCode=" . GetSQLValueString($strVendorTxCode, "text");
	$rsPrimary = mysql_query($strSQL)
		or die ("Query '$strSQL' failed with error message: \"" . mysql_error () . '"');

	$row = mysql_fetch_array($rsPrimary);
	$strStatus=$row["Status"];
	
	//Work out what to tell the customer
	if (substr($strStatus,0,8)=="DECLINED")
		$strReason="You payment was declined by the bank.  This could be due to insufficient funds, or incorrect card details.";
	elseif (substr($strStatus,0,9)=="MALFORMED" || substr($strStatus,0,7)=="INVALID")
		$strReason="The Sage Pay Payment Gateway rejected some of the information provided without forwarding it to the bank.
		Please let us know about this error so we can determine the reason it was rejected. Please call ".$row_rsPreferences['orgphone']."";
	elseif (substr($strStatus,0,8)=="REJECTED")
		$strReason="Your order did not meet our minimum fraud screening requirements.
		If you have questions about our fraud screening rules, or wish to contact us to discuss this, please call ".$row_rsPreferences['orgphone']."";
	elseif (substr($strStatus,5)=="ERROR")
		$strReason="We could not process your order because our Payment Gateway service was experiencing difficulties. You can place the order over the telephone instead by calling ".$row_rsPreferences['orgphone']."";
	else
		$strReason="The transaction process failed.  We please contact us with the date and time of your order and we will investigate.";
        
}

?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!DOCTYPE html>
<html class="" lang="en"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php  $pageTitle = "Payment Failed"; echo $pageTitle." | ".$site_name;?>
</title>
<!-- InstanceEndEditable -->
<!--[if IE]><![endif]-->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script>
$(document).ready(function(e) {
    
if(!bookmarkURL) { // if not in other scripts 
$('.bookmark').click(function(e) {
    var bookmarkURL = window.location.href;
    var bookmarkTitle = document.title;
	$.get("/seo/ajax/trackpage.ajax.php?pageTitle=Bookmark");

    if ('addToHomescreen' in window && addToHomescreen.isCompatible) {
      // Mobile browsers
      addToHomescreen({ autostart: false, startDelay: 0 }).show(true);
    } else if (window.sidebar && window.sidebar.addPanel) {
      // Firefox version < 23
      window.sidebar.addPanel(bookmarkTitle, bookmarkURL, '');
    } else if ((window.sidebar && /Firefox/i.test(navigator.userAgent)) || (window.opera && window.print)) {
      // Firefox 23+ and Opera version < 15
      $(this).attr({
        href: bookmarkURL,
        title: bookmarkTitle,
        rel: 'sidebar'
      }).off(e);
      return true;
    } else if (window.external && ('AddFavorite' in window.external)) {
      // IE Favorites
      window.external.AddFavorite(bookmarkURL, bookmarkTitle);
    } else {
      // Other browsers (mainly WebKit & Blink - Safari, Chrome, Opera 15+)
      alert('Press ' + (/Mac/i.test(navigator.userAgent) ? 'Cmd' : 'Ctrl') + '+D to bookmark this page.');
    }

    return false;
  });
}
  });
</script>
<!-- InstanceEndEditable -->
</head>
<body id="OffTemplate" class="bootstrap <?php echo $body_class;  ?>">
<?php require_once('../../local/includes/header.inc.php'); ?>
<main id="content"><!-- InstanceBeginEditable name="Body" --> <section>
      <div  class="container">
      <h1>Payment cancelled</h1>
      <p>The payment has not been processed by the payment provider.</p>
      <p>Your card has not been charged and the goods will not be dispatched.</p>
      <p><strong><?php echo isset($strReason) ? $strReason : ""; ?></strong></p>
      <ul>
      <li><a href="/basket/">Try again</a></li>
      <li><a href="javascript:void(0);" class="bookmark">Bookmark us</a> if you'd like to come back later</li>
    <li><a href="/contact/">Contact us</a></li></ul><?php require_once('../../core/share/includes/share.inc.php'); ?>
</div></section>
    <!-- InstanceEndEditable --></main>
<?php require_once('../../local/includes/footer.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>