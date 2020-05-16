<?php require_once('../Connections/aquiescedb.php'); ?><?php require_once('../mail/includes/sendmail.inc.php'); ?><?php require_once('../articles/includes/functions.inc.php'); ?>
<?php require_once('../core/includes/framework.inc.php'); ?><?php require_once('../members/includes/userfunctions.inc.php'); ?>
<?php require_once('../core/includes/upload.inc.php'); ?>
<?php  

if(!defined("MYSQL_SALT")) define("MYSQL_SALT", PRIVATE_KEY);

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

$colname_rsForm = "-1";
if (isset($_REQUEST['formID'])) {
  $colname_rsForm = $_REQUEST['formID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsForm = sprintf("SELECT * FROM `form` WHERE ID = %s", GetSQLValueString($colname_rsForm, "int"));
$rsForm = mysql_query($query_rsForm, $aquiescedb) or die(mysql_error());
$row_rsForm = mysql_fetch_assoc($rsForm);
$totalRows_rsForm = mysql_num_rows($rsForm);


if($row_rsForm['accessrankID']>0) { // restricted access
	if(!isset($_SESSION['MM_UserGroup']) || $_SESSION['MM_UserGroup']<$row_rsForm['accessrankID']) {
		
		$returnURL = $_SERVER['REQUEST_URI'];
		if($row_rsForm['loginsignup']==0) {
			$url = "/login/index.php";
			$msg = "You need to be logged in with appropriate credentials to submit this form.";
		} else {
			
			$url =  "/login/signup.php";
		}
		$url = ($row_rsForm['loginsignup']==0) ? "/login/index.php" : "/login/signup.php";
		$url .="?accesscheck=".urlencode($returnURL)."&msg=".urlencode($msg);
		header("location: ".$url); exit;
	}
}

$colname_rsFormItems = "-1";
if (isset($_REQUEST['formID'])) {
  $colname_rsFormItems = $_REQUEST['formID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsFormItems = sprintf("SELECT * FROM formfield WHERE formID = %s ORDER BY ordernum ASC", GetSQLValueString($colname_rsFormItems, "int"));
$rsFormItems = mysql_query($query_rsFormItems, $aquiescedb) or die(mysql_error());
$row_rsFormItems = mysql_fetch_assoc($rsFormItems);
$totalRows_rsFormItems = mysql_num_rows($rsFormItems);

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID, users.firstname, users.surname FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = "SELECT * FROM preferences WHERE ID = ".$regionID."";
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);
//
if(isset($_POST['formID']) && intval($_POST['formID'])>0) {
	if($_SESSION['form_token'] == $_POST['form_token']) {
	
	$errors = array();
	
	if($row_rsForm['captcha']==1 && (!isset($_POST['captcha_answer']) || !isset($_SESSION['captcha']) || md5(strtolower($_POST['captcha_answer'])) != $_SESSION['captcha'])) { 
			array_push($errors,"You have entered the security letters incorrectly. Please try again.\n");
	}
	
	if($row_rsForm['captcha']==2 || $row_rsForm['captcha']==3) {
		if(isset($_POST['g-recaptcha-response'])) {  
		        
			$response=json_decode(curl_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=".$row_rsPreferences['recaptcha_secret_key']."&response=".$_POST['g-recaptcha-response']."&remoteip=".getClientIP()), true);
			
		
			if($response['success'] == false)
			{
				array_push($errors,"Sorry, you have failed the Captcha test. Please try again.\n");
			}
		} else {
			array_push($errors,"Sorry, you have failed the Captcha test. Please try again.\n");
		}
	}

	
		
	
	
	$message = "The following form has been submitted";
	$message .= (isset($_POST['fullname']) && trim($_POST['fullname']) !="") ? " by ".$_POST['fullname'].":\n\n\n" : ":\n\n\n";
	foreach($_POST['formitemID'] as $key => $formitemID) {
		// check for errors		
		if($_POST['required'][$key] == 1 && (($_POST['formfieldtype'][$key]!=3 && $_POST['formfieldtype'][$key]!=0 && $_POST['formfieldtype'][$key]!=6 && trim($_POST['formfieldresponse'][$key])=="") || ($_POST['formfieldtype'][$key]==6 && !isset($uploaded['formfieldresponse'][$key]['newname'])))) {// required but  not checkbox or description			
			array_push($errors,$_POST['formfieldlabel'][$key]." is a required field.\n");
		}
		// verify field check
		if($_POST['addverifyfield'][$key] == 1 && $_POST['formfieldresponse'][$key]!=$_POST['formfieldverify'][$key]) {
			array_push($errors,"The two ".$_POST['formfieldlabel'][$key]." fields do not match.\n");
		}
		// password field strength check
		if($_POST['formfieldspecialtype'][$key] == 3 && strlen($_POST['formfieldresponse'][$key])<8) {
			array_push($errors,$_POST['formfieldlabel'][$key]." must be a minimum of 8 characters.\n");
		}
		
		
		if($row_rsForm['blockwww']==1 && preg_match("/(http|<|www|cc:)/i", $_POST['formfieldresponse'][$key])) {
			array_push($errors,"Sorry, to help prevent spam, web addresses are not allowed in form posts.\n");
		}
	}
	
		
	if(empty($errors)) {
		$uploaded = getUploads();
		// add to database
		
		
		$insert = "INSERT INTO formresponse (formID, createdbyID, createddatetime) VALUES (".GetSQLValueString($_POST['formID'], "int").",".GetSQLValueString($_POST['createdbyID'], "int").",NOW())";
		mysql_query($insert, $aquiescedb) or die(mysql_error());
		$formresponseID = mysql_insert_id();
		
		$header = "<html><head><style>table.box { width:100%; border-collapse:collapse; border-top-width: 1px;	border-top-style: solid;	border-top-color: #000;	border-left-width: 1px;	border-left-style: solid;	border-left-color: #000;} 
		table.box td {border-bottom-width: 1px;	border-bottom-style: solid;	border-bottom-color: #000;	border-right-width: 1px; border-right-style: solid;	border-right-color: #00; vertical-align:top; padding:5px; }
		table.box table, table.box td td { border:none; }</style></head><body><p>The following form has been posted from the web site:</p>";
		$message = "<table class=\"box\"><tr><td><table>";
		
		$protocol = getProtocol()."://";
		$formlink = $protocol.$_SERVER['HTTP_HOST']."/forms/admin/response.php?responseID=".$formresponseID."&formID=".intval($_POST['formID']);
		
		$footer = "<p>You can view this and other forms online using link below:</p><p><a href=\"".$formlink."\">".$formlink."</a></p></body></html>";
		
		
		
		
		foreach($_POST['formitemID'] as $key => $formitemID) {
			$formfieldchoiceID = "";
			$formfieldtextanswer = "";
			if($_POST['formfieldtype'][$key]>0) { // is response
				$message.= "<tr><td><strong>".$_POST['formfieldlabel'][$key]."</strong> </td>";
				
				if($_POST['formfieldtype'][$key]==3) { // checkbox
					$message .= "<td>";
					if(is_array($_POST['formfieldresponse']) && !empty($_POST['formfieldresponse'])) {
						foreach($_POST['formfieldresponse'][$key] as $checkbox => $value) { 
							$formfieldchoiceID = $_POST['choiceID'][$key][$checkbox];
							$message .=  $value."; ";
							$insert = "INSERT INTO formfieldresponse (formresponseID, formfieldID, formfieldchoiceID, formfieldtextanswer) VALUES (".$formresponseID .",".GetSQLValueString($_POST['formitemID'][$key], "int").",".GetSQLValueString($formfieldchoiceID, "int").",'')";
						mysql_query($insert, $aquiescedb) or die(mysql_error());
						}
					}
					$message .= "</td>";					
				} else  { // other than checkbox
					if($_POST['formfieldtype'][$key]==4 || $_POST['formfieldtype'][$key]==5) { // radio or select
						$formfieldchoiceID = $_POST['formfieldresponse'][$key];
						// get choice text
						$select = "SELECT formfieldchoicename FROM formfieldchoice WHERE ID = ".intval($formfieldchoiceID);
						$result = mysql_query($select, $aquiescedb) or die(mysql_error());
						$row = mysql_fetch_assoc($result);
						$formfieldtextanswer = $row['formfieldchoicename'];
						if(stripos($_POST['formfieldlabel'][$key],"subject")!==false) {
							$subject = isset($subject) ? $subject : $formfieldtextanswer;
						}
						
						$message .= "<td>". $formfieldtextanswer."</td>";
					} else if ($_POST['formfieldtype'][$key]==6) {  // file upload		
						if (isset($uploaded) && is_array($uploaded) && isset($uploaded['formfieldresponse'][$key]['newname'])) {
							$message .= "<td>Click to download: ";
							
							$formfieldtextanswer = $protocol;
							$formfieldtextanswer .= $_SERVER['HTTP_HOST']."/Uploads/".$uploaded['formfieldresponse'][$key]['newname'];
							$message .= $formfieldtextanswer."</td>";
						} else {
							$message .=  "<td>No file uploaded</td>";							
						}
					} else if ($_POST['formfieldtype'][$key]==7) {  // date picker	
						$formfieldtextanswer = $_POST['formfieldresponse'][$key];
						$message .= "<td>".preg_replace("/([0-9]{4})-([0-9]{2})-([0-9]{2})/","$3/$2/$1",$_POST['formfieldresponse'][$key])."</td>";					
					} else { // text
						$formfieldtextanswer = $_POST['formfieldresponse'][$key];
						$messageanswer = ($_POST['formfieldspecialtype'][$key]==3 || $_POST['encryptfield'][$key]==1) ? "[ENCRYPTED]" : $formfieldtextanswer;						
						$message .= "<td>";
						$message .= $messageanswer;
						$message .= "</td>";
						if(stripos($_POST['formfieldlabel'][$key],"email")!==false && strpos($_POST['formfieldresponse'][$key],"@")!==false) {
							// make client email first instance of email in any field names
							$clientemail = isset($clientemail) ? $clientemail : $_POST['formfieldresponse'][$key];
						}
						if(stripos($_POST['formfieldlabel'][$key],"first name")!==false) {
							$firstname = isset($firstname) ? $firstname : $_POST['formfieldresponse'][$key];
						}
						if(stripos($_POST['formfieldlabel'][$key],"surname")!==false) {
							$surname = isset($surname) ? $surname : $_POST['formfieldresponse'][$key];
						}
						if(stripos($_POST['formfieldlabel'][$key],"your name")!==false) {
							$fullname = explode(" ",$_POST['formfieldresponse'][$key],2);
							$firstname = isset($firstname) ? $firstname : $fullname[0];
							$surname = isset($surname) ? $surname : $fullname[1];
						}
						if(stripos($_POST['formfieldlabel'][$key],"subject")!==false) {
							$subject = isset($subject) ? $subject : $_POST['formfieldresponse'][$key];
						}
						if(stripos($_POST['formfieldlabel'][$key],"username")!==false) {
							$username = isset($username) ? $username : $_POST['formfieldresponse'][$key];
						}
						if(stripos($_POST['formfieldlabel'][$key],"password")!==false) {
							$password = isset($password) ? $password : $_POST['formfieldresponse'][$key];
						}
					}
					
					$insert = "INSERT INTO formfieldresponse (formresponseID, formfieldID, formfieldchoiceID, formfieldtextanswer) VALUES (".$formresponseID .",".GetSQLValueString($_POST['formitemID'][$key], "int").",".GetSQLValueString($formfieldchoiceID, "int").",";
					if($_POST['encryptfield'][$key]==1) {
						$insert .= " AES_ENCRYPT(".GetSQLValueString($formfieldtextanswer, "text").",'".MYSQL_SALT."')";
					} else {
						$insert .= GetSQLValueString($formfieldtextanswer, "text");
					}
					$insert .=  ")";
					mysql_query($insert, $aquiescedb) or die(mysql_error());
				} //end other than checkbox
			} // response
			$message .= "</tr>";
		} // end for each
		$message .="</table></td></tr></table>";
		
		
		if($row_rsForm['adduser']) {
			$usertypeID = 0;
			$emailoptin = isset($_POST['emailoptin']) && $_POST['emailoptin']==1 ? 1 : 0;
			$firstname = isset($firstname) ? $firstname : "Anonymous";
			$surname = isset($surname) ? $surname : "User";
			$clientemail = isset($clientemail) ? $clientemail : "";
			$password = isset($password) ? $password : "";
			$username = isset($username) ? $username : $clientemail;
			$login = $password=="" ? 0 :1 ;	
			$userID = createNewUser($firstname,$surname,$clientemail,$usertypeID,$row_rsForm['groupID'],0,0,"",$login,$username,$password,"","","", "", "",0,"",0,$emailoptin);
			$update = "UPDATE formresponse SET createdbyID = ".intval($userID)." WHERE ID = ".intval($formresponseID);
			mysql_query($update, $aquiescedb) or die(mysql_error());
		}
		
		// can override form email with programmed one if required
		$to = isset($form_email) ?  $form_email : $row_rsForm['email'];
		$subject = isset($subject) ? $row_rsForm['formname'].": ".$subject : $row_rsForm['formname']." submission";		
		$from = $default_sender ;
		$replyto = $clientemail;
		$freindlyfrom = $site_name;
		$html = $header . $message .$footer;
		$textmessage = strip_tags(str_replace("<br>","\n", $message));
		if(strpos($to, "@")>0) {
			sendMail($to, $subject, $textmessage, $from, $friendlyfrom, $html,"",false,"","","",0,false,false,"", 0, 0, $replyto);
		}
		if($_POST['sendemail']==1 && isset($clientemail)) { // send email to client
			$to = $clientemail;
			$subject = $row_rsForm['emailsubject'];
			$message = $row_rsForm['emailmessage'];
			sendMail($to, $subject, $message);
		}
		header("location: complete.php?formID=".intval($_POST['formID'])); exit;
	}
	} else {
		die("ERROR: Bad token");
	}
} // end post 




$header = articleMerge($row_rsForm['header']);



?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = isset($row_rsForm['pagetitle']) ? $row_rsForm['pagetitle'] : $row_rsForm['formname']." | ".$site_name; echo $pageTitle; ?>
</title>
<!--[if IE]><![endif]-->
<!-- InstanceEndEditable -->
<?php require_once('../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<meta name="description" content="<?php echo  $row_rsForm['metadescription']; ?>" />
<?php if($row_rsForm['noindex']==1) { ?>
<meta name="robots" content="noindex, nofollow">
<?php } ?>
<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" --><div class="container pageBody"><?php require_once('../core/includes/alert.inc.php'); ?><?php require('includes/form.inc.php'); ?>
      </div>
<!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>
<?php



mysql_free_result($rsLoggedIn);

if(is_resource($rsPreferences)) mysql_free_result($rsPreferences);
?>
