<?php require_once('../../../Connections/aquiescedb.php'); ?>
<?php require_once('../../../core/includes/adminAccess.inc.php'); ?><?php require_once('../../../core/includes/framework.inc.php'); ?>
<?php require_once('../../includes/functions.inc.php'); ?>
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

$regionID = (isset($regionID) && intval($regionID)>0) ? intval($regionID) : 1;


$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);


$terms = "<h1>Terms and Conditions</h1><p>The information contained on this Web Site is provided by {sitename} in good faith on an \"as is\" basis. The information is believed to be accurate and current at the date the information was placed on this Web Site.</p><p>No one at {sitename}, its related bodies corporate, any of their directors, officers, employees, agents, <a href=\"https://www.digitaldexterity.co.uk/\">web developers</a> and consultants makes any representation or warranty as to the reliability, accuracy or completeness of the information contained on this Web Site (including in relation to any products or services) and none of them accept any responsibility arising in any way (including negligence) for errors in, or omissions from, the information contained on this Web Site. You should make your own enquiries prior to entering into any transaction based on the contents of this Web Site.</p><p>To the fullest extent permitted by applicable law, {sitename} disclaims all representations and warranties, express or implied, including but not limited to, implied warranties of merchantability and fitness for a particular purpose and anon-infringement. {sitename} makes no warranty that the Web Site or any products or services advertised on the Web Site will meet your requirements, or that the Web Site will be uninterrupted, timely, secure or error free. Furthermore, to the fullest extent permitted by any applicable law, {sitename} and its directors, officers, employees, agents and consultants exclude all liability for any loss or damage (including indirect, special or consequential loss or damage) arising from the use of, or reliance on, that information contained on this website, whether or not caused by any negligent act or omission. If any law prohibits the exclusion of such liability, {sitename} limits its liability to the extent permitted by law, to the re-supply of the information. You expressly acknowledge and agree that {sitename} does not control other users of this Web Site or the suppliers of goods and services and {sitename} is therefore not liable for their opinions or their behaviour, including any information or advice provided by them or any defamatory statements made by them or any offensive conduct on their part.</p><p>The {sitename} website has links to other sites. {sitename} is not responsible for the privacy, information or content of those sites as they are not under the control of {sitename}. The links to those sites are provided for your convenience only, and are not any express or implied endorsement by {sitename}, of those sites, or the products and services provided on those sites.</p><p>No advice or information, whether oral or written, obtained by our from or through this Web Site creates any warranty not expressly made in these Terms and Conditions.</p><p>Information on this Web Site has been prepared in accordance with UK law for use in the UK only. This information may not satisfy the laws of any other country. To the extent that it does not satisfy the laws of the country in which you reside or from which you are accessing this Web Site, it is not directed at you and cannot be relied upon by. In any event, if you do rely on the information on this Web Site, then you agree to indemnify {sitename} for any losses, costs (including legal costs on a solicitor-client basis), expenses or damages {sitename} may suffer as a consequence of your reliance on the information or content of this Web Site.</p><p>If you require more information, please contact us.</p>";
$privacy = "<h1>Privacy Policy</h1><p>{sitename} respects your privacy and is committed to the protection of it. This privacy policy explains why and when we collect your personal information, and what use we make of that information. The policy also explains where and why your information may be disclosed to third parties, and the security measures used by {sitename} to help protect your personal information. </p>
<ul>
  <li><a href=\"#why\">Why and when {sitename} collects your information</a></li>
  <li><a href=\"#how\">How {sitename} uses your information</a></li>
  <li><a href=\"#disclosure\">Disclosure of your information to third parties</a></li>
  <li><a href=\"#accuracy\">Accuracy</a></li>
  <li><a href=\"#security\">Security</a></li>
  <li><a href=\"#changes\">Changes</a></li>
  <li><a href=\"#further\">Further information or comments<br />
    </a></li>
</ul>
<h2><a name=\"why\" id=\"why\"></a>Why and when we collect your information</h2>
<h3>Personal information you give us </h3>
<p>{sitename} collects some of the information you give us, either online or by email or facsimile. In most cases, the personal information {sitename} will collect from you is the personal information we are required to provide our services. This information includes your full name, mailing address, phone number, email address and where applicable, your facsimile number. {sitename} does not store your payment details when purchase anything from this site, such as your credit card number or bank account details.<br />
</p>
<h3>Website information (including cookies) </h3>
<p>{sitename} makes limited use of cookies on this website. A cookie is a small message given to your web browser by our web server. </p>
<p>The browser stores the message, and sends the message back to the server each time you request a page from the server.</p>
<p>{sitename} only uses cookies to tell us when you have set up an account with us previously (i.e. when you are a \"registered user\" of our website), and sometimes to let us know how you found our website. Where the cookies tell us that you are a registered user, cookies also allow us to automatically link you to your personalised accounts so that you can easily access any products or services only made available to our registered users, without your having to provide us with your username and password on every web page. The cookies allow us only to identify you in our database if you are a registered user, and to connect you to the relevant products and services on our website, but not to track the pages you visited on our website.</p>
<p>You always have the option to disable cookies by turning them off in your browser. However, you should be aware that many of the services available on the {sitename} website will not work if you have chosen to do this.</p>
<p>In addition to cookies, {sitename} <a href=\"https://www.digitaldexterity.co.uk/\">web hosting servers</a>  register the type of web browser you are using and your IP address when you access our website.</p>
<h2><a name=\"how\" id=\"how\"></a>How we use your information</h2>
<p>{sitename} may use your information to measure your experience of our services and products and to inform you of new product developments and associated services that we believe will interest you. If you do not wish to receive email notices or other communications from {sitename} regarding additional products and services, simply use the  unsubscribe function in the emails  to notify us that you wish to opt out of this service.</p>
<p>We may use your information in statistical form to analyze your purchasing preferences. This information is not used to track your individual preferences but rather to give us a general picture of the products and services our customers prefer. We might also gather information in statistical form such as the number of visitors who visit the {sitename} website on a daily basis. Again this is not used to track any individual customer's browsing habits.</p>
<p>We may use your email communications with {sitename} to analyze customer service issues as a way of identifying potential improvements for {sitename} products and services. In addition, we may occasionally monitor telephone conversations with you in order to facilitate staff training and to maintain our high levels of customer service. We will always inform you prior to any telephone conversation which is monitored in this way to obtain your prior approval.</p>
<p>We may use your IP address to contact your Internet Service Provider to let you know of any problems with payments you have made to us where we believe this is reasonably necessary to protect against fraud and credit card misuse.</p>
<h2><a name=\"disclosure\" id=\"disclosure\"></a>Disclosure of your information to third parties</h2>
<h3>Third party service providers</h3>
<p>We may supply your personal information to our third party billing and payment service providers where we believe this is reasonably necessary to protect against fraud and credit card misuse. Where you have made a query in respect of a payment processed by {sitename} which is made known to us by your bank or credit card company by communicating the query through our bank, we may supply the relevant personal information to assist you with your query. On occasion, we may also engage other companies or individuals to perform services on our behalf such as the distribution of marketing information to you (except where you have chosen to opt out of receiving this information from us).</p>
<p>Our relationships with such third party service providers are governed by our contracts with them. Those service providers are required to hold your personal information strictly confidential.</p>
<h2><a name=\"accuracy\" id=\"accuracy\"></a>Accuracy</h2>
<p>You may access and update the personal information we provide to the registries in respect of your domain name licence at any time. For information on this procedure, feel free to contact {sitename}. If you believe that {sitename} may hold other personal information about you which is inaccurate, please contact us by using this web form and we will take reasonable steps to ensure that it is corrected.</p>
<h2><a name=\"security\" id=\"security\"></a>Security</h2>
<p>The transfer of information across any media may involve a certain degree of risk, and the Internet is no different. However, helping you to keep your information secure is very important to {sitename}. In order to protect the security of personal information transmitted to {sitename} online, our Web servers support the use of the Secure Socket Layer (SSL) Protocol where appropriate. Using this protocol, information transferred between our systems is encrypted.</p>
<p>In addition, in respect of any payment details you send to us, we reveal only the first six and last three digits of your credit card number on the tax invoice receipt which we send to you for billing purposes. This is to help keep your credit card details secure.</p>
<p>You can also use simple precautions to help protect your security, such asprotecting against the unauthorised use of your username or password or other authentication id.</p>
<h2><a name=\"changes\" id=\"changes\"></a>Changes</h2>
<p>{sitename} may make changes to its privacy policy from time to time. You should check our privacy policy from time to time to see if we have made any changes to it.</p>
<h2><a name=\"further\" id=\"further\"></a>Further information or queries</h2>
<p>If you have any queries or comments, or would like more information concerning the {sitename} privacy policy, please contact us by using our contact page.</p>";

$guidelines = "<h1>{sitename} Web Community Guidelines </h1><p>When using this site, you may make contributions such as text, photos or video. Any contributions or communications with other members must be in accordance with the following {rsPreferences.orgname} Community Guidelines.</p><h2>About your posts:</h2>
<ul><li>
Contributions must be civil and tasteful;
</li><li>
No unlawful or objectionable content: unlawful, harassing, defamatory, abusive, threatening, harmful, obscene, profane, sexually oriented, racially offensive overtly political or otherwise objectionable material is not acceptable;
</li><li>
No spamming or off-topic material: we don't allow the submission of the same or very similar contributions many times. Please don't re-submit your contribution to more than one discussion, or contribute off-topic material in subject-specific areas;
</li><li>
No advertising;
</li><li>
No impersonation;
</li><li>
No inappropriate (e.g. vulgar, offensive etc) user names.
</li></ul>


<h2>Safety:</h2>
<ul><li>We advise that you never reveal any personal information about yourself or anyone else (for example: telephone number, home address or email address), and please do not include postal addresses of any kind;</li><li>We advise that you do not upload photos that individuals can be identified in.</li></ul> 



<h2>About the law:</h2>
<ul><li>
You may not submit any defamatory or illegal material of any nature. This includes text, graphics, video, programs or audio;
</li><li>
Contributing material with the intention of committing or promoting an illegal act is strictly prohibited;
</li><li>
You agree to only submit materials which are your own original work.
</li><li> You must not violate, plagiarise, or infringe the rights of third parties including copyright, trade mark, trade secrets, privacy, publicity, personal or proprietary rights.</li></ul>
<p>Material that doesn't meet these guidelines may be deleted or your user account could be withdrawn. The editor's decision is final on any disputes.</p>
<p>These guidelines are in addition to any legal terms and our own terms, conditions and privacy policy.</p>";

if(isset($_POST['createsectionpages'])) {
	$created = 0;
	foreach($_POST['createsectionpages'] as $key=>$sectionname) {
		$sectionID = createArticleSection($sectionname,$_POST['regionID'],$_POST['createdbyID'],0);
		if($sectionname=="Legal") {
			if(isset($_POST['terms'])) {
				$articleID = createArticle(1,   $_POST['regionID'], "Terms and Conditions",  str_replace("{sitename}", $site_name,$terms), "", "", 1, 1, "", $sectionID,"", $_POST['createdbyID']);
				$created ++;
				$update = "UPDATE preferences SET privacyarticleID = ".intval($articleID)." WHERE ID = ".intval($_POST['regionID']);
				mysql_query($update, $aquiescedb) or die(mysql_error());
			}
			if(isset($_POST['privacy'])) {
				$articleID = createArticle(1,   $_POST['regionID'], "Privacy Policy",  str_replace("{sitename}", $site_name,$privacy), "", "", 1, 1, "", $sectionID,"", $_POST['createdbyID']);
				$created ++;
				$update = "UPDATE preferences SET termsarticleID = ".intval($articleID)." WHERE ID = ".intval($_POST['regionID']);
				mysql_query($update, $aquiescedb) or die(mysql_error());
			}
			if(isset($_POST['guidelines'])) {
				$articleID = createArticle(1,   $_POST['regionID'], "Posting Guidelines",  str_replace("{sitename}", $site_name,$guidelines), "", "", 1, 1, "", $sectionID,"", $_POST['createdbyID']);
				$created ++;
				$update = "UPDATE preferences SET termsarticleID = ".intval($articleID)." WHERE ID = ".intval($_POST['regionID']);
				mysql_query($update, $aquiescedb) or die(mysql_error());
			}
		}
	}
	$msg = $created." pages created.";
}
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Auto Create"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<style>
<!--
-->
</style>
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
    <div class="page class">
      <h1><i class="glyphicon glyphicon-file"></i> Auto Create</h1>
      <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
      <li><a href="../index.php" class="link_back"><i class="glyphicon glyphicon-arrow-left"></i> Manage Pages</a></li></ul></div></nav><?php require_once('../../../core/includes/alert.inc.php'); ?>
      <p>Select the pages you want to automatically create below. Your site name and links to the web developers will be auto-inserted where appropriate. Section menu items will appear by default in site map only. You can the edit these sections and pages further shoud you wish.</p>
<form action="" method="post"><h2>Legal</h2>
     <p> <label><input type="checkbox" name="terms"> Terms &amp; Conditions</label></p>
     <p> <label><input type="checkbox" name="privacy"> Privacy Policy</label></p>
     
     <p> <label><input type="checkbox" name="guidelines"> Posting Guidelines</label></p>
     
     <button  type="submit" class="btn btn-primary"  onClick="return('Are you sure you want to create the selected pages?');">Create pages...</button><input name="createsectionpages[1]" type="hidden" value="Legal" >
     <input name="createdbyID" type="hidden" id="createdbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>">
     <input name="regionID" type="hidden" id="regionID" value="<?php echo $regionID; ?>">
      </form>
    </div>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);
?>
