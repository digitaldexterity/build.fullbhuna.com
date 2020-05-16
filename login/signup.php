<?php require_once('../Connections/aquiescedb.php'); ?>
<?php require_once('../core/includes/sslcheck.inc.php'); ?>
<?php require_once('../directory/includes/directoryfunctions.inc.php'); ?>
<?php require_once('../members/includes/userfunctions.inc.php'); ?><?php require_once('../core/includes/framework.inc.php'); ?>
<?php 


$regionID = isset($regionID) ? $regionID : 1;


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
  return strip_tags($theValue); // added by me - nbo tags needed here!
}
}

// back comapt
 if(isset($_GET['newslettersignup'])) {
	 $_GET['email'] = $_GET['newslettersignup'];
 }
 
  if(isset($_GET['registeremail'])) {
	 $_GET['email'] = $_GET['registeremail'];
 }
 
 if(isset($_REQUEST['fullname'])) {
	 $fullname = trim($_REQUEST['fullname']);
	 $pos = strrpos($fullname, " "); 
	 $_REQUEST['firstname'] = trim(substr($fullname, 0,$pos));
	 $_REQUEST['surname'] = trim(substr($fullname, $pos));
 }

if (!isset($_SESSION)) {
  session_start();
}

if (isset($_REQUEST['accesscheck'])) {
  $_SESSION['PrevUrl'] = $_REQUEST['accesscheck']; }
  
if (isset($_REQUEST['returnURL'])) {
  $_SESSION['PrevUrl'] = $_REQUEST['returnURL']; }

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = "SELECT * FROM preferences WHERE ID = ".intval($regionID);
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);


mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsOptinGroups = "SELECT usergroup.ID, usergroup.groupname, usergrouptype.grouptype FROM usergroup LEFT JOIN usergrouptype ON (usergroup.grouptypeID = usergrouptype.ID) WHERE usergroup.statusID = 1 AND usergroup.optin = 1 GROUP BY usergroup.ID ORDER BY usergroup.grouptypeID, usergroup.groupname";
$rsOptinGroups = mysql_query($query_rsOptinGroups, $aquiescedb) or die(mysql_error());
$row_rsOptinGroups = mysql_fetch_assoc($rsOptinGroups);
$totalRows_rsOptinGroups = mysql_num_rows($rsOptinGroups);

// *** check username exists or security code by me

if ($row_rsPreferences['userscansignup'] != 1) { 
die("Sorry, users are not allowed to sign up to this site at present."); } // security
$error = "";


if (isset($_POST['signup_token'])) {
	if($row_rsPreferences['securityletters']==0 && (!isset($_SESSION["signup_token"]) || $_POST["signup_token"] != $_SESSION["signup_token"])) { 
	// signup_token is only required if there is NO CAPTCHA so we can post from external sites
		unset($_SESSION);
		die("Invalid submit"); // invalid submit
	}
	if(isset($_REQUEST['fullname'])) {
		$names = explode(" ", $_REQUEST['fullname']);
		$firstname = $names[0];
		$surname = isset($names[0]) ? $names[1] : "";
	} else {
		$firstname = isset($_REQUEST['firstname']) ? $_REQUEST['firstname'] : "";
		$surname =  isset($_REQUEST['surname']) ? $_REQUEST['surname'] : "";
	}
	
	if($_POST['usertypeID'] >=0) { 	// security - allows negative
		$_POST['usertypeID'] =  ($row_rsPreferences['manualverify']==1 || $row_rsPreferences['emailverify'] == 1) ? 0 : 1; 
	}
	
	if($row_rsPreferences['securityletters']==1) {
		if($row_rsPreferences['captcha_type']==1) {
			if(md5(strtolower($_POST['captcha_answer'])) != $_SESSION['captcha'])	{ // security image incorrect
				$error .= "You have typed the security letters incorrectly. ";
			} // end letters wrong
		} else if ($row_rsPreferences['captcha_type']==2 || $row_rsPreferences['captcha_type']==3) { // advanced captcha
			if(isset($_POST['g-recaptcha-response'])) {          
				$response=json_decode(curl_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=".$row_rsPreferences['recaptcha_secret_key']."&response=".$_POST['g-recaptcha-response']."&remoteip=".getClientIP()), true);
				//echo "*"; print_r($response); die();
				if($response['success'] == false) {
					$error .= "Sorry, you have failed the Captcha test. Please try again. ";
				} // end fail
			} else { // no captch post
				$error .= "Sorry, you have failed the Captcha test. Please try again. ";
			}
		} // end advanced
	} // end security image incorrect

	// pre-checks
	$_POST['agerangeID'] = isset($_POST['agerangeID']) ? $_POST['agerangeID'] : "";
	$_POST['gender'] = isset($_POST['gender']) ? $_POST['gender'] : "";
	$_POST['groupID'] = (isset($_POST['groupID']) && intval($_POST['groupID'])>0 && md5($_POST['groupID'].PRIVATE_KEY) == $_POST['groupkey']) ? $_POST['groupID'] : "";
	$_POST['termsagree'] = isset($_POST['termsagree']) ? 1 : 0;
	$_POST['emailoptin'] = isset($_POST['emailoptin']) ? intval($_POST['emailoptin']) : 0;
	$_POST['partneremailoptinset'] = isset($_POST['partneremailoptinset']) ? 1 : 0;
	if($row_rsPreferences['emailoptintype'] == 2) { // reverse if opt out is set in prefs		
		$_POST['emailoptin'] = ($_POST['emailoptin']==1) ? 0 : 1;
	}
	if($row_rsPreferences['partneremailoptintype'] == 2) { // reverse if opt out is set in prefs		
		$_POST['partneremailoptinset'] = ($_POST['partneremailoptinset']==1) ? 0 : 1;
	}
	if(!validEmail($_POST['email'])) {
		$error .= "The email address \"".htmlentities($_POST['email'])."\" does not appear to be valid. ";
	}
	
	if($_POST['termsagree']!=1) { 
 		$error .= "You must agree to the terms and conditions to sign up. ";
 	} 
	if($row_rsPreferences['askgender']==1 && $row_rsPreferences['askgendercompulsary']==1 && $_POST['gender']=="") {
		$error .= "Please enter your gender. ";
	}
	if($row_rsPreferences['askfacebook']==1 && $row_rsPreferences['askfacebookcompulsary']==1 && trim($_POST['askfacebookURL'])=="") {
		$error .= "Please enter your Facebook ID. ";
	}
	if($row_rsPreferences['asktwitter']==1 && $row_rsPreferences['asktwittercompulsary']==1 && trim($_POST['twitterID'])=="") {
		$error .= "Please enter your Twitter ID. ";
	}
	if($row_rsPreferences['askwebsiteURL']==1 && $row_rsPreferences['askwebsiteURLcompulsary']==1 && trim($_POST['websiteURL'])=="") {
		$error .= "Please enter your web site address. ";
	}
	if($row_rsPreferences['askjobtitle']==1 && $row_rsPreferences['askjobtitlecompulsary']==1 && trim($_POST['jobtitle'])=="") {
		$error .= "Please enter your job title. ";
	}
	if($row_rsPreferences['askethnicity']==1 && $row_rsPreferences['askethnicitycompulsary']==1 && $_POST['ethnicityID']=="") {
		$error .= "Please enter your ethnicity. ";
	}
	if($row_rsPreferences['askdisability']==1 && $row_rsPreferences['askdisabilitiescompulsary']==1 && $_POST['disabilities']=="") {
		$error .= "Please enter your gender. ";
	}
	if($row_rsPreferences['asktelephone']==1 && $row_rsPreferences['asktelephonecompulsary']==1 && trim($_POST['telephone'])=="") {
		$error .= "Please enter your telephone number. ";
	}
	if($row_rsPreferences['askmobile']==1 && $row_rsPreferences['askmobile']==1 && trim($_POST['mobile'])=="") {
		$error .= "Please enter your mobile number. ";
	}
	if($row_rsPreferences['askhowdiscovered']==1 && $row_rsPreferences['askhowdiscoveredcompulsary']==1 && $_POST['discovered']<1) {
		$error .= "Please let us know how you discovered the site. ";
	}
	if($row_rsPreferences['askdateofbirth']==1 && $row_rsPreferences['minimumage']>0) {
		if(!isset($_POST['dob'])) { // if minimum age is set, date of birth is mandatory
			$error .= "You must enter a date of birth. ";
		}
		else if(time() - strtotime($_POST['dob']) < ($row_rsPreferences['minimumage'] * 365 * 24 * 60 * 60)) {	
	 $error .= "Sorry, you are younger than the minimum age of ".$row_rsPreferences['minimumage']." required to sign up to this site. "; // other errors irrelevant
		} 
 	} // end ask DOB
}

if($error!="") {
	unset($_POST['signup_token']);
}


if (isset($_POST['signup_token'])) { // submit 
		$regionID = $row_rsPreferences['multisitesignup']==1 ? 0 :  $regionID;
		$unique = ($_POST['usertypeID'] >= 0) ? true : false;
		$login = ($row_rsPreferences['userscanlogin']== 1) ? true : false;
	$userID = completeAddUser($_POST['salutation'],$firstname, $surname, $_POST['email'], $_POST['usertypeID'], $_POST['termsagree'],$_POST['emailoptin'], @$_POST['groupID'], 0, $unique, $login, $_POST['telephone'], $_POST['mobile'], $_POST['address1'], $_POST['address2'], $_POST['address3'], $_POST['address4'], "", $_POST['postcode'], "", "", "", "", "", "","",$_POST['username'],$_POST['password'],@$_POST['gender'],$_POST['dob'],@$_POST['agerangeID'], $_POST['ethnicityID'], $_POST['disabilityID'],$regionID,$_POST['jobtitle'],$_POST['discovered'],"","","","",$_POST['facebookURL'],$_POST['twitterID'],$_POST['websiteURL'],$_POST['partneremailoptinset'], $_POST['discoveredother'], $_POST['middlename']);
	
	if(intval($userID)>0) {	
		if((isset($_POST['optingroup']) && count($_POST['optingroup'])>0) || isset($_POST['updateoptingroups'])) {	
					
			 do {  
				if(isset($_POST['optingroup'][$row_rsOptinGroups['ID']])) { // checked 
					addUsertoGroup($userID, $row_rsOptinGroups['ID'],$row_rsMe['ID']);
				} else {
					$delete = "DELETE FROM usergroupmember WHERE groupID = ".$row_rsOptinGroups['ID']." AND userID = ".$userID;
					mysql_query($delete, $aquiescedb) or die(mysql_error());			
				}
			 } while ($row_rsOptinGroups = mysql_fetch_assoc($rsOptinGroups));
			 mysql_data_seek($rsOptinGroups,0);
			 $row_rsOptinGroups = mysql_fetch_assoc($rsOptinGroups);
		}
		
	
		unset($_SESSION['captcha']); // clear so can't be used again
		// log out current user
		require_once('includes/logout.inc.php');
		
		$signup_token = md5($userID.PRIVATE_KEY);
		$insertGoTo = "signupcomplete.php?userID=".$userID."&signup_token=".$signup_token;	
		header("Location:". $insertGoTo); exit; 
		
	} else {
		$error = $userID;
	}
}  // end submit


$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

// me - ssl option
$editFormAction = defined('USE_SSL') ? "https://".$_SERVER['HTTP_HOST'].$editFormAction : $editFormAction;





$varRegionID_rsDiscovered = "1";
if (isset($regionID)) {
  $varRegionID_rsDiscovered = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsDiscovered = sprintf("SELECT * FROM discovered WHERE statusID = 1 AND regionID = %s ORDER BY ordernum", GetSQLValueString($varRegionID_rsDiscovered, "int"));
$rsDiscovered = mysql_query($query_rsDiscovered, $aquiescedb) or die(mysql_error());
$row_rsDiscovered = mysql_fetch_assoc($rsDiscovered);
$totalRows_rsDiscovered = mysql_num_rows($rsDiscovered);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsEthnicity = "SELECT * FROM ethnicity WHERE statusID = 1 ORDER BY ordernum ASC";
$rsEthnicity = mysql_query($query_rsEthnicity, $aquiescedb) or die(mysql_error());
$row_rsEthnicity = mysql_fetch_assoc($rsEthnicity);
$totalRows_rsEthnicity = mysql_num_rows($rsEthnicity);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsDisability = "SELECT * FROM disability WHERE statusID = 1 ORDER BY ordernum ASC";
$rsDisability = mysql_query($query_rsDisability, $aquiescedb) or die(mysql_error());
$row_rsDisability = mysql_fetch_assoc($rsDisability);
$totalRows_rsDisability = mysql_num_rows($rsDisability);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsCountries = "SELECT fullname, ID FROM countries WHERE statusID = 1 ORDER BY ordernum ASC, fullname ASC ";
$rsCountries = mysql_query($query_rsCountries, $aquiescedb) or die(mysql_error());
$row_rsCountries = mysql_fetch_assoc($rsCountries);
$totalRows_rsCountries = mysql_num_rows($rsCountries);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsAgeRanges = "SELECT ID, agerange FROM agerange WHERE statusID = 1 ORDER BY ordernum ASC";
$rsAgeRanges = mysql_query($query_rsAgeRanges, $aquiescedb) or die(mysql_error());
$row_rsAgeRanges = mysql_fetch_assoc($rsAgeRanges);
$totalRows_rsAgeRanges = mysql_num_rows($rsAgeRanges);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsRegion = "SELECT * FROM region WHERE ID = ".$regionID;
$rsRegion = mysql_query($query_rsRegion, $aquiescedb) or die(mysql_error());
$row_rsRegion = mysql_fetch_assoc($rsRegion);
$totalRows_rsRegion = mysql_num_rows($rsRegion);


?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Sign up"; echo $pageTitle." | ".$site_name; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<meta http-equiv="Expires" content="Fri, Jun 12 1981 00:00:00 GMT" />
<meta http-equiv="Pragma" content="no-cache" />
<meta http-equiv="Cache-Control" content="no-store" />
<meta http-equiv="Cache-Control" content="no-cache" />
<meta name="robots" content="noindex,nofollow"/>
<?php if(isset($row_rsPreferences['googlesearchAPI'])) { // load GoogleAjaxSearch used to get lat long from postcode?>
<script src="//www.google.com/uds/api?file=uds.js&amp;v=1.0&amp;key=<?php echo $row_rsPreferences['googlesearchAPI']; ?>"></script>
<script src="../SpryAssets/SpryValidationConfirm.js" type="text/javascript"></script>
<script>
var localSearch = new GlocalSearch();
</script>
<?php } ?>
<style>
<!--




<?php if($row_rsPreferences['addressrequired'] == 0) {
echo ".postcode, .address { display:none; }";
}
else if($row_rsPreferences['addressrequired'] == 2) {
echo ".address { display:none; }";
}
if($row_rsPreferences['usesalutation'] != 1) {
echo ".salutation { display:none; }";
}
if($row_rsPreferences['userscanlogin'] != 1) {
echo ".logInLink { display:none; }";
}
if($row_rsPreferences['askgender'] != 1) {
echo ".gender { display:none; }";
}


if($row_rsPreferences['asktelephone'] != 1) {
echo ".telephone { display:none; }";
}

if($row_rsPreferences['askmobile'] != 1) {
echo ".mobile { display:none; }";
}


if($row_rsPreferences['askethnicity'] != 1) {
echo ".ethnicity { display:none; }";
}

if($row_rsPreferences['askfacebook'] != 1) {
echo ".facebookURL { display:none; }";
}


 if($row_rsPreferences['asktwitter'] != 1) {
echo ".twitterID { display:none; }";

}

if($row_rsPreferences['askwebsiteURL'] != 1) {
echo ".websiteURL { display:none; }";
}

if($row_rsPreferences['askdisability'] != 1) {
echo ".disability { display:none; }";
}



if($row_rsPreferences['askdateofbirth'] != 1) {
echo ".dob { display:none; }";
}
 if($row_rsPreferences['askdateofbirth'] != 2) {
 echo ".agerange { display:none; }";
}
if($row_rsPreferences['askdateofbirth'] < 2 && $row_rsPreferences['askgender'] !=1 && $row_rsPreferences['askethnicity'] !=1) {
echo ".demographics { display:none; }";
}
if ($row_rsPreferences['askhowdiscovered'] != 1) {
echo ".howdiscovered { display:none; }";
}
if ($row_rsPreferences['askhowdiscoveredother'] != 1) {
echo ".discoveredother { display:none; }";

}
 
if ($row_rsPreferences['emailasusername'] == 1) {
 echo ".username { position:absolute; left:-999em; }";

}
 if ($row_rsPreferences['autousername'] == 1 || $row_rsPreferences['userscanlogin'] != 1) {
echo ".usernamepassword { display:none; }";
}
 if ($row_rsPreferences['communityguidelines'] != 1) {
echo "#membership_guidelines_link { display:none; }";
}
if ($row_rsPreferences['emailoptintype'] ==0) {
 echo ".emailoptin { display:none; }";
}
if ($row_rsPreferences['partneremailoptintype'] ==0) {
 echo ".partneremailoptin { display:none; }";
}

if (!isset($row_rsPreferences['askgenderother']) || $row_rsPreferences['askgenderother'] ==0) {
 echo ".genderother { display:none; }";
}

if (!isset($row_rsPreferences['askgenderrathernotsay']) || $row_rsPreferences['askgenderrathernotsay'] ==0) {
 echo ".genderrathernotsay { display:none; }";
}

 if($row_rsPreferences['askmiddlename'] != 1) { echo ".middlename { display:none; } "; } 

?>
-->
</style>
<script src="/core/scripts/validator.min.js"></script>
<script src="/login/ajax/checkChosenLogin.js"></script>
<script src="/login/ajax/signup.js"></script>
<link href="/login/css/defaultSignup.css" rel="stylesheet"  />
<link href="../SpryAssets/SpryValidationConfirm.css" rel="stylesheet" type="text/css">
<?php $termsURL =(isset($row_rsPreferences['termsarticleID']) && $row_rsPreferences['termsarticleID']>0) ? "/articles/article.php?articleID=".intval($row_rsPreferences['termsarticleID']) : "";


?>
<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" -->
      <section>
  <div id="pageSignup" class="container pageBody login">
    <div class="crumbs"><div><span class="you_are_in">You are in: </span><a href="/">Home</a><span class="separator">&nbsp;&rsaquo;&nbsp;</span>Register</div></div>
     <?php require_once('../core/includes/alert.inc.php'); ?>
    <?php if(isset($_GET['eventregistration'])) { ?>
    <h1>Are you a member or have you registered before?</h1>
    <h2>Log in now to register for this event</h2>
    <p>If you've registered before we should already have you details on the system, so please <a href="index.php<?php echo (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING']!="") ? "?".addslashes($_SERVER['QUERY_STRING']) : ""; ?>">log in here with your username and password</a> to register for this event. If you have forgotten or don't have your log in details, <a href="/login/forgot_password.php">retrieve them here</a>.</p>
    <p>If this is your first time here, please fill out the sign up form below:</p>
    <?php } else { ?>
   
  <?php $signuphtml = preg_split("#(<hr>|<hr />)#i",$row_rsPreferences['text_signupinfo']); 
  echo (isset($signuphtml[0]) && $signuphtml[0]!="") ? $signuphtml[0] : "<h1 id=\"signUpHeader\">Your details...</h1>"; ?>
    <?php } ?>
 
  
   <div class="row">
   <div class="col-sm-6 col-sm-push-6"><?php echo $row_rsPreferences['text_signup2']; ?></div>
   
   
   <div class="col-sm-6 col-sm-pull-6">
   <?php echo $row_rsPreferences['text_signup1']; ?>
    <form action="<?php echo $editFormAction; ?>" method="post" name="signup" id="signup" data-toggle="validator">
    
        <div class="form-group salutation">
          <input name="salutation" type="text" class="form-control" id="salutation" maxlength="6" value="<?php echo isset($_REQUEST['salutation']) ? htmlentities($_REQUEST['salutation'], ENT_COMPAT, "UTF-8") : ""; ?>" placeholder="Title" /><div class="help-block with-errors"></div>
        </div>
        <div class="form-group fullname">
       
            <input name="firstname"  required type="text" class="form-control" id="firstname" value="<?php echo isset($_REQUEST['firstname']) ? htmlentities($_REQUEST['firstname'], ENT_COMPAT, "UTF-8") : ""; ?>" maxlength="50"  placeholder="<?php echo isset($row_rsPreferences['text_firstname']) ? htmlentities($row_rsPreferences['text_firstname'], ENT_COMPAT, "UTF-8") : "First name"; ?>" data-required-error="Please enter your first name" /><div class="help-block with-errors"></div>
            
           </div> 
          <div class="form-group fullname">   
            <input name="middlename" type="text" class="form-control  middlename" id="middlename" value="<?php echo isset($_REQUEST['middlename']) ? htmlentities($_REQUEST['middlename'], ENT_COMPAT, "UTF-8") : ""; ?>" maxlength="50" placeholder="<?php echo isset($row_rsPreferences['text_middlename']) ? htmlentities($row_rsPreferences['text_middlename'], ENT_COMPAT, "UTF-8") : "Middle Name"; ?>" /><div class="help-block with-errors"></div>
          </div>  
            
           <div class="form-group fullname">  
            
            <input name="surname" required type="text" class="form-control" id="surname" value="<?php echo isset($_REQUEST['surname']) ? htmlentities($_REQUEST['surname'], ENT_COMPAT, "UTF-8") : ""; ?>" maxlength="50" placeholder="<?php echo isset($row_rsPreferences['text_surname']) ? htmlentities($row_rsPreferences['text_surname'], ENT_COMPAT, "UTF-8") : "Surname"; ?>" data-required-error="Please enter your surname" />
            <div class="help-block with-errors"></div>
        </div>
        <div class="form-group jobtitle">
          <input name="jobtitle" type="text"  id="jobtitle" value="<?php echo isset($_REQUEST['jobtitle']) ? htmlentities($_REQUEST['jobtitle'], ENT_COMPAT, "UTF-8") : ""; ?>" maxlength="50" placeholder="<?php echo isset($row_rsPreferences['text_role']) ? htmlentities($row_rsPreferences['text_role'], ENT_COMPAT, "UTF-8") : "Job Title"; ?>" /><div class="help-block with-errors"></div>
        </div>
        <div class="form-group email">
         
            <input name="email" type="email" multiple  id="fb-signup-email" value="<?php echo isset($_REQUEST['email']) ? htmlentities($_REQUEST['email']) : ""; ?>" maxlength="100" autocomplete="off" class="form-control" placeholder="<?php echo isset($row_rsPreferences['text_email']) ? htmlentities($row_rsPreferences['text_email'], ENT_COMPAT, "UTF-8") : "Email"; ?>" required  data-required-error="A valid email is required" />
            <div class="help-block with-errors"></div><div><div id="emailAlert"></div>&nbsp;</div>
        </div>
        <div class="form-group telephone">
          <input name="telephone" type="tel" class="form-control" id="telephone" value="<?php echo isset($_REQUEST['telephone']) ? htmlentities($_REQUEST['telephone'], ENT_COMPAT, "UTF-8") : ""; ?>" maxlength="20"  placeholder="Telephone" <?php if($row_rsPreferences['asktelephonecompulsary'] == 1) echo " required "; ?> data-required-error="Please provide a telephone number"/>
       <div class="help-block with-errors"></div> </div>
        
        
        <div class="form-group mobile">
          <input name="mobile" type="tel" class="form-control" id="mobile" value="<?php echo isset($_REQUEST['mobile']) ? htmlentities($_REQUEST['mobile'], ENT_COMPAT, "UTF-8") : ""; ?>" maxlength="20"  placeholder="Mobile" <?php if($row_rsPreferences['askmobilecompulsary'] == 1) echo " required "; ?> data-required-error="Please provide a mobile number" /><div class="help-block with-errors"></div>
        </div>
        
        <div class="form-group address">
          
            <input name="address1" type="text" class="form-control" id="address1" value="<?php echo isset($_REQUEST['address1']) ? htmlentities($_REQUEST['address1'], ENT_COMPAT, "UTF-8") : ""; ?>" c lass="form-control" maxlength="50" placeholder="Address" />
           <div class="help-block with-errors"></div>
        </div>
        <div class="form-group address">
          <input name="address2" type="text" class="form-control" id="address2" value="<?php echo isset($_REQUEST['address2']) ? htmlentities($_REQUEST['address2'], ENT_COMPAT, "UTF-8") : ""; ?>" maxlength="50" /><div class="help-block with-errors"></div>
        </div>
        <div class="form-group address">
          <input name="address3" type="text" class="form-control" id="address3" value="<?php echo isset($_REQUEST['address3']) ? htmlentities($_REQUEST['address3'], ENT_COMPAT, "UTF-8") : ""; ?>" maxlength="50" /><div class="help-block with-errors"></div>
        </div>
        <div class="form-group address">
          <input name="address4" type="text" class="form-control" id="address4" value="<?php echo isset($_REQUEST['address4']) ? htmlentities($_REQUEST['address4'], ENT_COMPAT, "UTF-8") : ""; ?>" maxlength="50" /><div class="help-block with-errors"></div>
        </div>
        <div class="form-group postcode">
          <input name="postcode" type="text" class="form-control" id="postcode" value="<?php echo isset($_REQUEST['postcode']) ? htmlentities($_REQUEST['postcode'], ENT_COMPAT, "UTF-8") : ""; ?>" maxlength="10" onblur="usePointFromPostcode(this.value)" placeholder="Postcode"  /><div class="help-block with-errors"></div>
            <input type="hidden" name="latitude" id="latitude" />
            <input type="hidden" name="longitude" id="longitude" />
        </div>
        <div class="form-group address">
          <label for="countryID">Country:</label>
            <select name="countryID" id="countryID" class="form-control">
              <option value="" <?php if (!(strcmp("", @$_REQUEST['countryID']))) {echo "selected=\"selected\"";} ?>><?php echo isset($region['text_choose']) ? htmlentities($region['text_choose'], ENT_COMPAT, "UTF-8") : "Choose..." ?></option>
              <?php
do {  
?>
              <option value="<?php echo $row_rsCountries['ID']?>"<?php if (!(strcmp($row_rsCountries['ID'], @$_REQUEST['countryID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsCountries['fullname']?></option>
              <?php
} while ($row_rsCountries = mysql_fetch_assoc($rsCountries));
  $rows = mysql_num_rows($rsCountries);
  if($rows > 0) {
      mysql_data_seek($rsCountries, 0);
	  $row_rsCountries = mysql_fetch_assoc($rsCountries);
  }
?>
              </select>
           <div class="help-block with-errors"></div>
        </div>
        
        <div class="form-group facebookURL">
          <input name="facebookURL" type="text" class="form-control" id="facebookURL" value="<?php echo isset($_REQUEST['facebookURL']) ? htmlentities($_REQUEST['facebookURL'], ENT_COMPAT, "UTF-8") : ""; ?>" maxlength="50" placeholder="Facebook ID" <?php if($row_rsPreferences['askfacebookcompulsary'] == 1) echo " required " ; ?> data-required-error="Please provide a Facebook address" />
       <div class="help-block with-errors"></div> </div>
        <div class="form-group twitterID">
          <input name="twitterID" type="text" class="form-control" id="twitterID" value="<?php echo isset($_REQUEST['twitterID']) ? htmlentities($_REQUEST['twitterID'], ENT_COMPAT, "UTF-8") : ""; ?>" maxlength="50" placeholder="Twitter handle" <?php if($row_rsPreferences['asktwittercompulsary'] == 1) echo " required " ; ?> data-required-error="Please provide a Twitter handle"/>
     <div class="help-block with-errors"></div>   </div>
        <div class="form-group websiteURL">
          <input name="websiteURL" type="url" class="form-control" id="websiteURL" value="<?php echo isset($_REQUEST['websiteURL']) ? htmlentities($_REQUEST['websiteURL'], ENT_COMPAT, "UTF-8") : ""; ?>" maxlength="50" placeholder="Your web site (http://)" <?php if($row_rsPreferences['askwebsiteURLcompulsary'] == 1) echo " required "; ?> data-required-error="Please provide a web site" />
     <div class="help-block with-errors"></div>   </div>
		<?php if($row_rsPreferences['minimumage']>0) { ?>
        <div class="form-group dob">
          <label for="dob">Your date of birth:</label><input type="hidden" name="dob" id="dob" value="<?php $setvalue = isset($_REQUEST['dob']) ? $_REQUEST['dob'] : ""; echo $setvalue; ?>" />
            <?php $inputname = "dob"; $startyear = date('Y') - 100; include("../core/includes/datetimeinput.inc.php"); ?>
   <div class="help-block with-errors"></div>     </div>
        <?php } ?>
        <div class="form-group usernamepassword">
          <?php echo $row_rsPreferences['text_choosepassword']; ?>
       <div class="help-block with-errors"></div> </div>
        <div class="form-group usernamepassword username">
        
        
        
        
          <input name="username" type="text" class="form-control" id="username" value="<?php echo isset($_REQUEST['username']) ? htmlentities($_REQUEST['username']) : ""; ?>" maxlength="20" onkeyup="checkLiveInput(event,'usernameAlert','username')" autocomplete="off" placeholder="<?php echo isset($row_rsPreferences['text_username']) ? htmlentities($row_rsPreferences['text_username'], ENT_COMPAT, "UTF-8") : "Username"; ?>" <?php if ($row_rsPreferences['autousername'] != 1 && $row_rsPreferences['emailasusername'] == 0 ) echo " required "; ?> data-required-error="A username is required" />
            <span id="usernameAlert"></span>
       <div class="help-block with-errors"></div> </div>
        <div class="form-group usernamepassword password">
         <div class="input-group"><span id="spryconfirm1">
           <input name="password" type="password" class="form-control" id="fb-password-field" value="" maxlength="20" onFocus = "document.getElementById('passwordAlert').innerHTML='';document.getElementById('password2Alert').innerHTML='';"  onKeyUp="checkLiveInput(event,'passwordAlert','password')"    autocomplete="off" placeholder="<?php echo isset($row_rsPreferences['text_password']) ? htmlentities($row_rsPreferences['text_password'], ENT_COMPAT, "UTF-8") : "Password"; ?>"  <?php if ($row_rsPreferences['autousername'] != 1 ) echo " required "; ?> data-required-error="A password is required"/>
           <span class="confirmRequiredMsg">A value is required.</span><span class="confirmInvalidMsg">The values don't match.</span></span><span class="input-group-btn">
        <button class="btn btn-default btn-secondary  toggle-password" type="button" toggle="#fb-password-field"><i class="glyphicon glyphicon-eye-open  "></i></button>
      </span> </div>
            <span><span id="passwordAlert"></span>&nbsp;</span>
        <div class="help-block with-errors"></div></div>
        <div class="form-group usernamepassword password">
         <div class="input-group">
          <input name="password2" type="password" id="fb-password-field2" class="form-control" value="" maxlength="20" onkeyup="checkLiveInput(event,'password2Alert','password2')"    autocomplete="off" placeholder="<?php echo isset($row_rsPreferences['text_retypepassword']) ? htmlentities($row_rsPreferences['text_retypepassword'], ENT_COMPAT, "UTF-8") : "Re-type Password"; ?>"  <?php if ($row_rsPreferences['autousername'] != 1 ) echo " required "; ?> data-required-error="A password is required" /><span class="input-group-btn">
        <button class="btn btn-default btn-secondary  toggle-password" type="button" toggle="#fb-password-field2"><i class="glyphicon glyphicon-eye-open  "></i></button>
      </span> </div>
            <span><span id="password2Alert"></span>&nbsp;</span>
            <input type="hidden" name="plainpassword" id="plainpassword" /><div class="help-block with-errors"></div>
        </div>
    
          <div class="form-group demographics">
            <p>We ask the following <strong>optional</strong> questions in order to get a better idea of who is using our services and improve them:</p>
          
          <?php if($row_rsPreferences['minimumage']==0) { ?>
          <div class="form-group dob">
            <label for="dob">Your date of birth:</label><input type="hidden" name="dob" id="dob" value="<?php $setvalue = isset($_REQUEST['dob']) ? htmlentities($_REQUEST['dob'], ENT_COMPAT, "UTF-8") : ""; echo $setvalue; ?>" />
              <?php $inputname = "dob"; $startyear = date('Y') - 100; include("../core/includes/datetimeinput.inc.php"); ?>
         <div class="help-block with-errors"></div> </div>
          <?php } ?>
         
          <div class="form-group agerange">
             <?php  if($totalRows_rsAgeRanges>0) { ?>
              
              <?php
				  echo isset($row_rsPreferences['askagerangetext']) ? htmlentities($row_rsPreferences['askagerangetext'] , ENT_COMPAT, "UTF-8") : "Your age group"; ?>:
              <?php do { ?>
                
                
                <input <?php if (isset($_REQUEST['agerangeID']) && $_REQUEST['agerangeID']==$row_rsAgeRanges['ID']) {echo "checked=\"checked\"";} ?> type="radio" name="agerangeID" id="agerange_<?php echo $row_rsAgeRanges['ID']; ?>" value="<?php echo $row_rsAgeRanges['ID']; ?>" />
                <label for="agerange_<?php echo $row_rsAgeRanges['ID']; ?>"><?php echo htmlentities($row_rsAgeRanges['agerange'], ENT_COMPAT, "UTF-8"); ?></label>
                
                
                
                
                <?php } while ($row_rsAgeRanges = mysql_fetch_assoc($rsAgeRanges)); ?>
              <?php } ?>
<div class="help-block with-errors"></div>
          </div>
          <div class="form-group gender">
            <label for="gender"><?php echo isset($row_rsPreferences['askgendertext']) ? htmlentities($row_rsPreferences['askgendertext'] , ENT_COMPAT, "UTF-8") : "Your gender"; ?>:</label><input <?php if (!(strcmp(@$_REQUEST['gender'],"1"))) {echo "checked=\"checked\"";} ?> type="radio" name="gender" id="male" value="1" <?php if($row_rsPreferences['askgendercompulsary'] == 1) echo "required "; ?> data-required-error="Please state your gender" />
               <label for="male">Male</label>
              <input <?php if (!(strcmp(@$_REQUEST['gender'],"2"))) {echo "checked=\"checked\"";} ?> type="radio" name="gender" id="female" value="2"  <?php if($row_rsPreferences['askgendercompulsary'] == 1) echo "required "; ?> />
               <label for="female">Female</label>
              
              
             
              <input <?php if (!(strcmp(@$_REQUEST['gender'],"3"))) {echo "checked=\"checked\"";} ?> type="radio" name="gender" id="genderother" value="3"   class="genderother"  <?php if($row_rsPreferences['askgendercompulsary'] == 1) echo "required "; ?> />
              <label for="genderother" class="genderother">Other</label>
              
              
             
              <input <?php if (!(strcmp(@$_REQUEST['gender'],"4"))) {echo "checked=\"checked\"";} ?> type="radio" name="gender" id="genderrathernotsay" value="4" class="genderrathernotsay"  <?php if($row_rsPreferences['askgendercompulsary'] == 1) echo "required "; ?> />
              <label for="genderrathernotsay" class="genderrathernotsay">Prefer not to say</label>
        <div class="help-block with-errors"></div>  </div>
          <div class="form-group ethnicity">
            <label for="ethnicityID"><?php echo isset($row_rsPreferences['askethnicitytext']) ? htmlentities($row_rsPreferences['askethnicitytext'] , ENT_COMPAT, "UTF-8") : "Your ethnicity"; ?>:</label><select name="ethnicityID"  id="ethnicityID" class="form-control" <?php if($row_rsPreferences['askethnicitycompulsary'] == 1) echo " required " ; ?> data-required-error="Please select your ethnicity">
              <option value="" <?php if (!(strcmp("", @$_REQUEST['ethnicityID']))) {echo "selected=\"selected\"";} ?>><?php echo isset($region['text_choose']) ? htmlentities($region['text_choose'], ENT_COMPAT, "UTF-8") : "Choose..." ?></option>
              <?php
do {  
?>
              <option value="<?php echo $row_rsEthnicity['ID']?>"<?php if (!(strcmp($row_rsEthnicity['ID'], @$_REQUEST['ethnicityID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsEthnicity['ethnicityname']?></option>
              <?php
} while ($row_rsEthnicity = mysql_fetch_assoc($rsEthnicity));
  $rows = mysql_num_rows($rsEthnicity);
  if($rows > 0) {
      mysql_data_seek($rsEthnicity, 0);
	  $row_rsEthnicity = mysql_fetch_assoc($rsEthnicity);
  }
?><option value="0" <?php if (!(strcmp(0, @$_REQUEST['ethnicityID']))) {echo "selected=\"selected\"";} ?>>Prefer not to say</option>
            </select><div class="help-block with-errors"></div>
          </div>
          <div class="form-group disability">
            <label for="disabilityID"><?php echo isset($row_rsPreferences['askdisabilitytext']) ? htmlentities($row_rsPreferences['askdisabilitytext'] , ENT_COMPAT, "UTF-8") : "Do you have any disabilities?"; ?></label><select name="disabilityID"  id="disabilityID" class="form-control" <?php if($row_rsPreferences['askdisabilitycompulsary'] == 1) echo " required "; ?> data-required-error="Please make a selection">
              <option value="" <?php if (!(strcmp("", @$_REQUEST['disabilityID']))) {echo "selected=\"selected\"";} ?>><?php echo isset($region['text_choose']) ? htmlentities($region['text_choose'], ENT_COMPAT, "UTF-8") : "Choose..." ?></option>
              
              <?php 
do {  
?>
              <option value="<?php echo $row_rsDisability['ID']?>"<?php if (!(strcmp($row_rsDisability['ID'], @$_REQUEST['disabilityID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsDisability['disabilityname']?></option>
              <?php
} while ($row_rsDisability = mysql_fetch_assoc($rsDisability));
  $rows = mysql_num_rows($rsDisability);
  if($rows > 0) {
      mysql_data_seek($rsDisability, 0);
	  $row_rsDisability = mysql_fetch_assoc($rsDisability);
  }
?>
            </select><div class="help-block with-errors"></div>
          </div></div>
          <div class="form-group howdiscovered">
            <label for="discovered">How did you hear about us?</label><select name="discovered"  id="discovered" class="form-control" <?php if ($row_rsPreferences['askhowdiscoveredcompulsary'] == 1) echo " required "; ?> data-required-error="Please state how discovered"
>
              <option value="0" <?php if (!(strcmp("", @$_REQUEST['discovered']))) {echo "selected=\"selected\"";} ?>><?php echo isset($region['text_choose']) ? htmlentities($region['text_choose'], ENT_COMPAT, "UTF-8") : "Choose..." ?></option>
              <?php
do {  
?>
              <option value="<?php echo $row_rsDiscovered['ID']?>"<?php if (!(strcmp($row_rsDiscovered['ID'], @$_REQUEST['discovered']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsDiscovered['description']?></option>
              <?php
} while ($row_rsDiscovered = mysql_fetch_assoc($rsDiscovered));
  $rows = mysql_num_rows($rsDiscovered);
  if($rows > 0) {
      mysql_data_seek($rsDiscovered, 0);
	  $row_rsDiscovered = mysql_fetch_assoc($rsDiscovered);
  }
?>
              </select>
              
              <div class="help-block with-errors"></div>
           
          </div>
           <div class="form-group discoveredother">
           <input type="text" name="discoveredother" id="discoveredother" value="<?php echo isset($_REQUEST['discoveredother']) ? htmlentities($_REQUEST['discoveredother'], ENT_COMPAT, "UTF-8") : ""; ?>" placeholder="If other, please specify" class="form-control" >
           </div>
        <?php if ($row_rsPreferences['securityletters']==1) { 
		if($row_rsPreferences['captcha_type']==1) {?>
        <div>
          <img src="/core/includes/random_image.php" alt="Security CAPTCHA image" style="width:150px !important; height:50px !important;" />
        </div>
        <div>
          <input name="captcha_answer" type="text" class="form-control" id="captcha_answer" maxlength="40" placeholder="Please enter letters above"  />
            
        </div>
        <?php } else if(($row_rsPreferences['captcha_type']==2 || $row_rsPreferences['captcha_type']==3) && trim($row_rsPreferences['recaptcha_site_key'])!="") {
		if($row_rsPreferences['captcha_type']==2) {  ?>
       <div class="g-recaptcha" data-sitekey="<?php echo $row_rsPreferences['recaptcha_site_key']; ?>"></div><?php }//  reCaptcha 2
	  } // reCaptcha
	   } // security letters ?>
      <div class="form-group termsagree">
          <label>
            <input <?php if (isset($_REQUEST['termsagree'])) {echo "checked=\"checked\"";} ?> name="termsagree" type="checkbox" id="termsagree" value="1" data-error="You must agree to site terms and conditions" required /> <?php if(isset($row_rsPreferences['termsagreetext']) && trim($row_rsPreferences['termsagreetext'])!="") { echo $row_rsPreferences['termsagreetext']; } else { ?>
            I agree to this site's <?php if($termsURL!="") { ?><a href="<?php echo $termsURL; ?>" title="Click to view our web site use terms and conditions." target="_blank" id="terms" rel="noopener" onclick="javascript:MM_openBrWindow('<?php echo $termsURL; ?>','terms','scrollbars=yes,width=400,height=400'); return false;">terms and conditions</a></label><?php } else { ?>terms and conditions<?php } } ?><br />
            <div class="help-block with-errors"></div>
        </div>
        <div  class="form-group emailoptin">
        <?php if($row_rsPreferences['emailoptintype'] == 3) { ?>
   <?php echo $row_rsPreferences['emailoptintext']; ?>: 
    <label><input type="radio" name="emailoptin" id="emailoptin_1" value="1" <?php if(isset($_REQUEST['emailoptin']) && $_REQUEST['emailoptin']==1) echo "checked" ?> required> Yes</label> &nbsp; 
  <label><input type="radio" name="emailoptin" value="0" <?php if(isset($_REQUEST['emailoptin']) && $_REQUEST['emailoptin']==0) echo "checked" ?> required> No</label>
  <div class="help-block with-errors"></div><?php } else { ?>
  
          <label>
            <input <?php if (isset($_REQUEST['emailoptin']) || ($row_rsPreferences['emailoptinset']==1 && !isset($_REQUEST['signup_token']))) {echo "checked=\"checked\"";} ?> name="emailoptin" type="checkbox" id="emailoptin" value="1"  />
            <?php echo $row_rsPreferences['emailoptintext']; ?></label>
            
    <?php } ?>        <br>
            <label class="partneremailoptin">
              <input <?php if (isset($_REQUEST['partneremailoptinset']) || ($row_rsPreferences['partneremailoptinset']==1 && !isset($_REQUEST['signup_token']))) {echo "checked=\"checked\"";} ?> name="partneremailoptinset" type="checkbox" id="partneremailoptinset" value="1"  />
            <?php echo $row_rsPreferences['partneremailoptintext']; ?></label>
            
            
            
            <div id="form-group optingroups"><?php if ($totalRows_rsOptinGroups > 0) { // Show if recordset not empty ?>
              
              <p> <?php echo $row_rsPreferences['groupoptintext']; ?></p>
              
              <?php $lasttype = ""; do { 
	  if(isset($row_rsOptinGroups['grouptype']) && $row_rsOptinGroups['grouptype']!=$lasttype) { 
	  $lasttype = $row_rsOptinGroups['grouptype'];
	  echo "<h3>".$lasttype."</h3>"; } ?>
              <input name="updateoptingroups" type="hidden" value="1" />
              <label class="text-nowrap">
                <input type="checkbox" name="optingroup[<?php echo $row_rsOptinGroups['ID']; ?>]" <?php if(isset($_POST['optingroup'][$row_rsOptinGroups['ID']]) || (isset($_REQUEST['groupID']) && $_REQUEST['groupID'] == $row_rsOptinGroups['ID'])) { echo "checked=\"checked\""; } ?> onclick="if(this.checked &amp;&amp; !document.getElementById('emailoptin').checked) { alert('In order to receive any of these emails you must also check the Allow news updates box below.'); }" />&nbsp;<?php echo $row_rsOptinGroups['groupname']; ?></label>&nbsp;&nbsp;&#8200; <!-- Breaking space for IE glitch -->
              
              <?php } while ($row_rsOptinGroups = mysql_fetch_assoc($rsOptinGroups)); ?>
          <?php } // Show if recordset not empty ?></div>
            
          </div>
        <div>
          <button type="submit" class="btn btn-primary <?php if($row_rsPreferences['captcha_type']==3) {  ?> g-recaptcha" data-sitekey="<?php echo $row_rsPreferences['recaptcha_site_key']; ?>" data-callback="onSignupSubmit<?php } ?>" ><?php echo isset($row_rsRegion['text_continue']) ? htmlentities($row_rsRegion['text_continue'], ENT_COMPAT, "UTF-8"): "Continue..."; ?></button>
            <input name="usertypeID" type="hidden" id="usertypeID" value="<?php echo isset($_REQUEST['usertypeID']) ? urlencode($_REQUEST['usertypeID']) : 0; ?>" />
            <input type="hidden" name="signup_token" id="signup_token" value="<?php $signup_token = md5(uniqid(rand(), true));
$_SESSION['signup_token'] = $signup_token; echo $signup_token; ?>" />
            <input name="groupID" type="hidden" id="groupID" value="<?php echo isset($_REQUEST['groupID']) ? intval($_REQUEST['groupID']) : 0; ?>" />
          <input name="groupkey" type="hidden" id="groupkey" value="<?php echo isset($_GET['groupkey']) ? htmlentities($_GET['groupkey']) : ""; ?>" />
           <input name="autousername" type="hidden" id="autousername" value="<?php echo $row_rsPreferences['autousername']; ?>" />
          <input name="emailasusername" type="hidden"  id="emailasusername" value="<?php echo $row_rsPreferences['emailasusername']; ?>">
          <input name="userscanlogin" type="hidden"  id="userscanlogin" value="<?php echo $row_rsPreferences['userscanlogin']; ?>">
          
        </div>
      <?php  echo (isset($signuphtml[1]) && $signuphtml[1]!="") ? $signuphtml[1] : "<div class=\"privacypolicy\">
          <h3 class=\"small\"><br />
            YOUR PRIVACY:</h3>
            <p class=\"small\"> We will <strong> NEVER</strong> divulge any of your details to anyone else unless you explicitly ask us to. </p>
            <p class=\"small\">We will only send you important information and updates you ask for. See our privacy policy. </p>
        </div>  "; ?>
    
      </form></div></div>
    </div></section>
    <script>
$(".toggle-password").click(function() {

  $(this).find("i").toggleClass("glyphicon-eye-open glyphicon-eye-close");
  var input = $($(this).attr("toggle"));
  if (input.attr("type") == "password") {
    input.attr("type", "text");
  } else {
    input.attr("type", "password");
  }
});
</script>
   <?php if($row_rsPreferences['securityletters'] == 1 && ($row_rsPreferences['captcha_type']==2 || $row_rsPreferences['captcha_type']==3)  && trim($row_rsPreferences['recaptcha_site_key'])!="") { ?><script src='https://www.google.com/recaptcha/api.js' async defer></script>
   <script>
$(document).ready(function(){
    $('#signup').validator().on('submit', function (e) {
      if (e.isDefaultPrevented()) {
        // handle the invalid form...
       console.log("validation failed");
      } else {
        // everything looks good!
        e.preventDefault();
        console.log("validation success");
        grecaptcha.execute();
      }
    });
}); 



// function below only used for invisbile reCaptcha [3]
       function onSignupSubmit(token) {
         document.getElementById("signup").submit();
       }
var spryconfirm1 = new Spry.Widget.ValidationConfirm("spryconfirm1", "username");
   </script>
<?php } ?>
    <!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsPreferences);

mysql_free_result($rsDiscovered);

mysql_free_result($rsEthnicity);

mysql_free_result($rsDisability);

mysql_free_result($rsCountries);

mysql_free_result($rsAgeRanges);

mysql_free_result($rsRegion);

mysql_free_result($rsOptinGroups);
?>
