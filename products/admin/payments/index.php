<?php require_once('../../../Connections/aquiescedb.php'); ?>
<?php require_once('../../../core/includes/adminAccess.inc.php'); ?><?php require_once('../../../mail/includes/sendmail.inc.php'); ?><?php require_once('../../../core/includes/framework.inc.php'); ?>
<?php
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

$MM_restrictGoTo = "/login/index.php?notloggedin=true&alert=".urlencode("You need to be logged in as an Administrator to access this page.");
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

if(isset($_POST['amount']) && floatval($_POST['amount'])>0) {
	$oneoffamount = floatval($_POST['amount']);
	$token = md5($oneoffamount .PRIVATE_KEY);
	$url = getProtocol()."://". $_SERVER['HTTP_HOST']."/products/basket/index.php?oneoffamount=".$oneoffamount."&title=".urlencode($_POST['title'])."&token=".$token;
	if(function_exists(get_bitly_short_url)) {
		// use bit.ly if available
		$url = get_bitly_short_url($url);
	}

	if(isset($_POST['emaillink'], $_POST['email']) && strpos($_POST['email'],"@")>0) {
		$to = $_POST['email'];
		$subject = $thisRegion['title'] . " payment link";
		$message = $_POST['paynowemail'];
		
		$merge = array("item"=>$_POST['title'], "amount"=>$thisRegion['currencycode']." ".number_format($oneoffamount,2,".", ","), "link"=>$url);
		$htmlmessage = "";
		if(strpos($message,"<html")!==false) {
			$htmlmessage = $message;
			$message = "";
		} 
		
		sendMail($to, $subject, $message,"","",$htmlmessage,"","","","","","",false,$merge);
		
		$update = "UPDATE productprefs SET paynowemail = ".GetSQLValueString($_POST['paynowemail'], "text").", paynowtemplateID = ".intval($_POST['emailtemplateID'])." WHERE ID = ".$regionID;
		mysql_query($update, $aquiescedb) or die(mysql_error());
		$msg = "Email has been sent to ".$to;
	}
}

$colname_rsLoggedIn = "1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsProductPrefs = "SELECT paynowemail, paynowtemplateID FROM productprefs WHERE ID = ".$regionID."";
$rsProductPrefs = mysql_query($query_rsProductPrefs, $aquiescedb) or die(mysql_error());
$row_rsProductPrefs = mysql_fetch_assoc($rsProductPrefs);
$totalRows_rsProductPrefs = mysql_num_rows($rsProductPrefs);

$varRegionID_rsTemplate = "1";
if (isset($regionID)) {
  $varRegionID_rsTemplate = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsTemplate = sprintf("SELECT groupemailtemplate.ID, groupemailtemplate.templatename FROM groupemailtemplate WHERE groupemailtemplate.regionID = %s ORDER BY groupemailtemplate.templatename", GetSQLValueString($varRegionID_rsTemplate, "int"));
$rsTemplate = mysql_query($query_rsTemplate, $aquiescedb) or die(mysql_error());
$row_rsTemplate = mysql_fetch_assoc($rsTemplate);
$totalRows_rsTemplate = mysql_num_rows($rsTemplate);


?><?php if(isset($body_class)) $body_class .= " products ";  ?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Pay now"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<?php $remove_script_host = "false"; // default for tinymce is true
if(!defined("TINYMCE_CONTENT_CSS")) define("TINYMCE_CONTENT_CSS", "/core/css/global.css");
define("TINY_MCE_PLUGINS", "fullpage");
require_once('../../../core/tinymce/tinymce.inc.php'); ?>
<style>
<!--
-->
</style>
<script>
$(document).ready(function(e) {
    toggleEmail();
	toggleTemplate();
});

function toggleEmail() {
	
	if(document.getElementById('emaillink').checked) { 
	$('tbody.email').slideDown();
	$('.btn-get').hide();
	 $('.btn-email').show();
	 } else { 
	 $('tbody.email').hide();
	 $('.btn-email').hide();
	 $('.btn-get').show();
	  }
}

function toggleTemplate(id) {
	if(id > 0) { 
	tinymce.EditorManager.execCommand('mceAddEditor',true, 'paynowemail');
		// get any data posted sent on to ajax script for merge
		data = "templateID="+id;
		url = "/mail/admin/email/ajax/getTemplate.ajax.php?templateID="+id;
		
		
		$.post(url, data, function(response) {
    	
			content = response.split('<!--template break-->');
			$("#subject").val(content[0]);
        	tinyMCE.get('paynowemail').setContent(content[1]);
			
			
		});
	}
}

	function copyToPasteboard() {
  /* Get the text field */
  var copyText = document.getElementById("payurl");

  /* Select the text field */
  copyText.select();

  /* Copy the text inside the text field */
  document.execCommand("copy");

  /* Alert the copied text */
  alert("Copied to clipboard: " + copyText.value);
}
</script>
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
    <div class="page class"><?php require_once('../../../core/region/includes/chooseregion.inc.php'); ?>
      <h1><i class="glyphicon glyphicon-shopping-cart"></i> Pay now...</h1>
      <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
      <li class="nav-item"><a class="nav-link" href="../index.php" ><i class="glyphicon glyphicon-arrow-left"></i> Orders</a>
      </ul></div></nav>
      <?php require_once('../../../core/includes/alert.inc.php'); ?>
      <p>You can allow a customer to use your online checkout now for the amount you choose. Get the link here:</p><form name="form1" method="post" ><table class="form-table">
  
  <tr>
    <th class="top text-right" scope="row"><label for="amount">Amount: </label> </th>
    <td class="form-inline"><input name="amount" type="text" id="amount" size="10" maxlength="10" value="<?php echo isset($_POST['amount']) ? htmlentities($_POST['amount']): ""; ?>" class="form-control">&nbsp;<?php echo $thisRegion['currencycode']; ?></td>
  </tr><tr>
    <th class="top text-right" scope="row"><label for="title">Item:</label></th>
    <td><input name="title" type="text" id="title" size="50" maxlength="100" value="<?php echo isset($_POST['title']) ? htmlentities($_POST['title'], ENT_COMPAT, "UTF-8"): "One-off payment"; ?>" class="form-control"></td>
  </tr>
  <tr>
    <th class="text-right" scope="row"><label>Send link:</label></th>
    <td><label for="emaillink"><input type="checkbox" name="emaillink" id="emaillink" onClick="toggleEmail();" <?php if(isset($_POST['emaillink'])) echo " checked "; ?>> email link to customer</label>
      </td>
  </tr>
  <tbody class="email">
  <tr>
    <th class="top text-right" scope="row"><label for="email">Recipient:</label></th>
    <td><input name="email" type="email" multiple id="email" size="50" maxlength="100" value="<?php echo isset($_POST['email']) ? htmlentities($_POST['email']): ""; ?>" placeholder="Email address" class="form-control"></td>
  </tr>
  <tr>
    <th class="top text-right" scope="row"><label for="subject">Subject:</label></th>
    <td><input name="subject" type="text"  id="subject" size="50" maxlength="100" value="<?php echo isset($_POST['subject']) ? htmlentities($_POST['subject']): "Your Payment Link"; ?>" placeholder="Email subject" class="form-control"></td>
  </tr>
  <tr>
    <th class="top text-right" scope="row"><label for="emailtemplateID">Email:</label></th>
    <td>
    <select name="emailtemplateID" id="emailtemplateID" class="form-control" onChange="toggleTemplate(this.value)">
                  <option value="0" >As below</option>
                  <?php
do {  
?>
                  <option value="<?php echo $row_rsTemplate['ID']?>"><?php echo $row_rsTemplate['templatename']; ?></option>
                  <?php
} while ($row_rsTemplate = mysql_fetch_assoc($rsTemplate));
  $rows = mysql_num_rows($rsTemplate);
  if($rows > 0) {
      mysql_data_seek($rsTemplate, 0);
	  $row_rsTemplate = mysql_fetch_assoc($rsTemplate);
  }
?>
                </select>
                <?php $email = (isset($row_rsProductPrefs['paynowemail']) && $row_rsProductPrefs['paynowemail']!="") ? $row_rsProductPrefs['paynowemail'] : "Here is the payment link for {item}\n\nAmount: {amount}\n\n{link}\n\nPlease click on this link to make your payment now.\n\n(If link does not work, not please copy and paste the link into your browser.)\n\nThank you."; ?><input name="templateHEAD" type="hidden" value="">
      <textarea name="paynowemail" cols="80" rows="10" id="paynowemail" class="form-control <?php if(strpos($email,"<html")!==false) echo " tinymce "; ?>" ><?php echo $email; ?></textarea></td>
  </tr>
  <tr>
    <th align="right" scope="row"><label>Merges:</label></th>
    <td><br>
{amount} = payment (including currency), {item} = payment for, {link} = generated link</td>
  </tr></tbody>
  <tr>
    <th align="right" scope="row">&nbsp;</th>
    <td>  <button  type="submit" class="btn btn-primary btn-get" >Get link</button><button  type="submit" class="btn btn-primary btn-email" >Send link</button>
      <input name="regionID" type="hidden" id="regionID" value="<?php echo $regionID; ?>"></td>
  </tr>
</table>
      
       
          
        
          
       
      
      </form><?php if(isset($url)) { ?>
      <h3>Payment link:</h3>
      <p>The link for immediate payment is:</p><p><a href="<?php echo $url; ?>" target="_blank" rel="noopener" ><?php echo $url; ?></a></p> <input type="hidden" value="<?php echo $url; ?>" id="payurl"><button onclick="copyToPasteboard()" class="btn btn-default"><i class="glyphicon glyphicon-copy"></i> Copy link</button>
      <?php } ?>
     
    </div>
    
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsProductPrefs);

mysql_free_result($rsTemplate);
?>
