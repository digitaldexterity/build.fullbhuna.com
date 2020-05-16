<?php // Copyright 2013 Paul Egan

/********** REQUIRED ********/
// to use just add hidden field "sendmail" = true in form

// ***********OPTIONAL**********
// logmail = true ( default ) add to database
// recipient = recipient email - REQUIRES:
// key = md5($_POST['recipient'].PRIVATE_KEY)
//"returnURL" - returnURL URL
//"mailfolderID" 
//autoreply = 1:0 - with optional:
//responsesubject
//responsemessage ?>
<?php if(!defined("SITE_ROOT")) die(); // can only be called from FB ?><?php require_once(SITE_ROOT.'core/includes/framework.inc.php'); ?>
<?php require_once('honeypot.inc.php'); ?>
<?php if (!isset($_SESSION)) {  session_start(); } // start session if not already 
$protocol = getProtocol()."://";
if(!defined("PROTOCOL")) define("PROTOCOL", $protocol);
$regionID = (isset($regionID) && $regionID>0) ? intval($regionID) : 1;

$defaultTemplateHTML = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional //EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><!--[if IE]><html xmlns="http://www.w3.org/1999/xhtml" class="ie"><![endif]--><!--[if !IE]><!--><html style="margin: 0;padding: 0;" xmlns="http://www.w3.org/1999/xhtml"><!--<![endif]--><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"><title></title><!--[if !mso]><!--><meta http-equiv="X-UA-Compatible" content="IE=edge"><!--<![endif]--><meta name="viewport" content="width=device-width"><style type=="text/css"></style></head><!--[if mso]><body class="mso"><![endif]--><!--[if !mso]><!--><body class="no-padding" style="margin: 0;padding: 0;-webkit-text-size-adjust: 100%;"><!--<![endif]--><table style="width:500px; margin:15px auto;"><tr><td>HEADER</td></tr><tr><td>BODY</td></tr><tr><td>FOOTER</td></tr></table></body></html>';





if ('sendmail.inc.php' == basename($_SERVER['SCRIPT_FILENAME']))
     die ('<h2>Direct File Access Prohibited</h2>');
$sendMailVersion = 3.0;

require_once(SITE_ROOT.'core/includes/upload.inc.php');
require_once(SITE_ROOT.'core/includes/framework.inc.php'); require_once(SITE_ROOT.'members/includes/userfunctions.inc.php'); 
if(is_readable(SITE_ROOT.'location/includes/locationfunctions.inc.php')) {
require_once(SITE_ROOT.'location/includes/locationfunctions.inc.php');
}?>
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


mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsMailPrefs = "SELECT * FROM mailprefs WHERE ID = ".intval($regionID);
$rsMailPrefs = mysql_query($query_rsMailPrefs, $aquiescedb) or die(mysql_error());
$row_rsMailPrefs = mysql_fetch_assoc($rsMailPrefs);
$totalRows_rsMailPrefs = mysql_num_rows($rsMailPrefs);

if ($totalRows_rsMailPrefs<1) { // no MailPrefs yet
	mysql_query("INSERT INTO mailprefs (ID) VALUES (".intval($regionID).")", $aquiescedb)
	or die(mysql_error());
	header("location: ".$_SERVER['REQUEST_URI']); exit; // refresh page
} // end no mail prefs

if(!isset($row_rsPreferences)) {
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = "SELECT * FROM preferences WHERE ID = ".intval($regionID);
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);

if ($totalRows_rsPreferences<1) { // no MailPrefs yet
	mysql_query("INSERT INTO preferences (ID) VALUES (".intval($regionID).")", $aquiescedb)
	or die(mysql_error());
	header("location: ".$_SERVER['REQUEST_URI']); exit; // refresh page
} // end no  prefs
}

$default_sender = (isset($row_rsMailPrefs['noreplyemail']) && $row_rsMailPrefs['noreplyemail'] !="") ? $row_rsMailPrefs['noreplyemail'] : "website@".str_replace("www.","",$_SERVER['HTTP_HOST']);

if(!function_exists("validEmail")) {
	function validEmail($email)
	{
	   $isValid = true; 
	   $emails = explode(",",$email);
	   foreach($emails as $key=> $email) {
		   $email = trim($email);
		   $atIndex = strrpos($email, "@");
		   if (is_bool($atIndex) && !$atIndex)
		   {
			  $isValid = false;
		   }
		   else
		   {
			  $domain = substr($email, $atIndex+1);
			  $local = substr($email, 0, $atIndex);
			  $localLen = strlen($local);
			  $domainLen = strlen($domain);
			  if ($localLen < 1 || $localLen > 64)
			  {
				 // local part length exceeded
				 $isValid = false;
			  }
			  else if ($domainLen < 1 || $domainLen > 255)
			  {
				 // domain part length exceeded
				 $isValid = false;
			  }
			  else if ($local[0] == '.' || $local[$localLen-1] == '.')
			  {
				 // local part starts or ends with '.'
				 $isValid = false;
			  }
			  else if (preg_match('/\\.\\./', $local))
			  {
				 // local part has two consecutive dots
				 $isValid = false;
			  }
			  else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain))
			  {
				 // character not valid in domain part
				 $isValid = false;
			  }
			  else if (preg_match('/\\.\\./', $domain))
			  {
				 // domain part has two consecutive dots
				 $isValid = false;
			  }
			  else if
		(!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/',
						 str_replace("\\\\","",$local)))
			  {
				 // character not valid in local part unless 
				 // local part is quoted
				 if (!preg_match('/^"(\\\\"|[^"])+"$/',
					 str_replace("\\\\","",$local)))
				 {
					$isValid = false;
				 }
			  }
			  if(function_exists('checkdnsrr')) { // for compat with windows
				  if ($isValid && checkdnsrr("google.com","A") && !(checkdnsrr($domain,"MX") || checkdnsrr($domain,"A")))
			  {
				 // domain not found in DNS
				 $isValid = false;
			  }
			  } // end function exists
		   }
		  
	   } // emd for each
	   return $isValid;
	}
}


if(!function_exists("buildEmailHTML")) {
function buildEmailHTML($template = array(), $to="", $groupemailID=0) {
	global $row_rsMailPrefs;
	$html ="";
	$viewonlinehtml = "";
	$unsubscribehtml = "";
	$querystring ="";
	
	if($template['templateHTML']!="") {  // is HTML
	
		if(strpos($to,"@")!==false) {
			if(isset($groupemailID) && $groupemailID>0) {
				$querystring = "emailID=".$groupemailID."&email=".$to."&token=".md5($to.@PRIVATE_KEY);
			} else if ($template['ID']>0) {
				$querystring = "templateID=".$template['ID']."&email=".$to."&token=".md5($to.@PRIVATE_KEY);
			}					
		} // end is to
		
	
		if(strpos($template['templateHTML'],"<html>") === false) { 
		// not full html so build below
		
			
			$html = "<html><head>".$template['templatehead']."</head>";
			$html .= strpos($template['templatebodytag'],"<body")!==false ? $template['templatebodytag'] : "<body leftmargin=\"0\" topmargin=\"0\" marginwidth=\"0\" marginheight=\"0\" yahoo=\"fix\" style=\"width: 100%; background-color: #ffffff; margin:0; padding:0; -webkit-font-smoothing: antialiased;font-family: Arial, sans-serif;\">";
			$html .= $template['templateHTML']."</body></html>";
		} // not full html
		else {
			$html = $template['templateHTML'];
		}
		
		 // inject extras into html
		$viewonlinehtml = ($querystring !="" && isset($template['viewonline']) && $template['viewonline']==1) ?  "<p align='center' style='font-size:9px;'>If you cannot see this email, <a href='".PROTOCOL.$_SERVER['HTTP_HOST']."/"."mail/index.php?".$querystring."'>click here to view online</a>.</p>" : "";
		$unsubscribehtml ="";	
		if($querystring !="" && isset($template['showunsubscribe']) && $template['showunsubscribe']==1) {
			$unsubscribehtml .=  "<p align='center' class='unsubscribe'><a href='".PROTOCOL.$_SERVER['HTTP_HOST']."/"."mail/unsubscribe.php?".$querystring."' style='text-decoration:none;'>";
			$unsubscribehtml .=  isset($row_rsMailPrefs['text_unsubscribe']) ? $row_rsMailPrefs['text_unsubscribe'] : "If you wish to stop receiving our emails, unsubscribe here.";
			$unsubscribehtml .= "</a></p>";
		}
		$trackhtml = (isset($template['trackclicks'] ) && $template['trackclicks'] == 1 && $groupemailID>0) ? "<img src=\"".PROTOCOL.$_SERVER['HTTP_HOST']."/mail/includes/mail_track_img.php?clicktrackemailID=".$groupemailID."&clicktrackuseremail=".$to."\" width=\"1\" height=\"1\" alt=\"\" />" : "";		
		//$html = preg_replace('/<body[^>]+\>/i', '$0'.$unsubscribehtml, $html);
		$html = str_replace("</body>",$unsubscribehtml.$trackhtml."</html>",$html);
	} // is html
	
	//merges
	$html = str_replace("{unsubscribe}",$unsubscribehtml,$html);
	$html = str_replace("{online}",$viewonlinehtml,$html);
	// add carriage return before every tag to avoid common problem of  extra line breaks being added within tag
	$html = str_replace("<","\r\n<", $html);
	return $html;
} // end func
} 

if(!function_exists("sendMailgun")) {
function sendMailgun($mg_api, $mg_domain, $mg_from_email, $friendlyfrom="", $to, $subject, $message, $html="", $attachments = array(),  $mg_reply_to_email="", $mg_cc="", $mg_bcc="") {
	
	global $row_rsMailPrefs;

//$mg_api = 'key-1a851364de450b2b21105af33c48fd61';
//$mg_domain = "digidex.co.uk";
$mg_version =  $row_rsMailPrefs['mailgunregion'] == 2 ? 'api.eu.mailgun.net/v3/': 'api.mailgun.net/v3/';


$mg_reply_to_email = ($mg_reply_to_email == "") ? $mg_from_email : $mg_reply_to_email;

$mg_message_url = "https://".$mg_version.$mg_domain."/messages";
$send_as = strlen($html) > 20 ?  "html" : "text"; // taking account of line breaks between head and body
$message = strlen($html) > 20 ? $html : $message;

$postfields = array(  'from'      => 	$friendlyfrom.' <' . $mg_from_email . '>',
                'to'        => $to,
                'h:Reply-To'=>  ' <' . $mg_reply_to_email . '>',
                'subject'   => $subject,
                $send_as      => $message);
				
	if($mg_cc!="") { $postfields['cc']	 = $mg_cc;	}
	if($mg_bcc!="") { $postfields['bcc']	 = $mg_bcc;	}
	
				foreach($attachments as $key=>$attachment) {
					$postfields['attachment['.$key.']'] = curl_file_create(SITE_ROOT.$attachment['name'], $attachment['type'], basename($attachment['name']));
}

$ch = curl_init();
curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

curl_setopt ($ch, CURLOPT_MAXREDIRS, 3);
curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, false);
curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt ($ch, CURLOPT_VERBOSE, 0);
curl_setopt ($ch, CURLOPT_HEADER, 1);
curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 10);
curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);

curl_setopt($ch, CURLOPT_USERPWD, 'api:' . $mg_api);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

curl_setopt($ch, CURLOPT_POST, true); 
//curl_setopt($curl, CURLOPT_POSTFIELDS, $params); 
curl_setopt($ch, CURLOPT_HEADER, false); 

//curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_URL, $mg_message_url);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
//print_r($postfields);
//die();
$result = curl_exec($ch);
curl_close($ch);
$res = json_decode($result,TRUE);
//print_r($res);
//die();
}
}


if(!function_exists("processMailForm")) {
function processMailForm($log=true, $usecaptcha = 0) { 
// returns TRUE or error string
	global $database_aquiescedb, $aquiescedb, $site_name, $row_rsMailPrefs, $regionID, $default_sender, $row_rsPreferences;
	
	$error = "";
	$fullname = "";
	if(isset($_POST['full_name'])) {
		$fullname = $_POST['full_name'];
	} else if(isset($_POST['fullname'])) {
		$fullname = $_POST['fullname'];
	} else if(isset($_POST['realname'])) {
		$fullname = $_POST['realname'];
	} else if(isset($_POST['firstname']) && isset($_POST['surname'])) {
		$fullname = $_POST['firstname']." ".$_POST['surname'];
	}
	if($row_rsMailPrefs['companyrequired']==1 && strlen(trim($_POST['company'])) == 0) { 
		$error .= "Please provide a company name. ";
	} 
	if($row_rsMailPrefs['emailrequired']==1 && !validEmail($_POST['email'])) { 
	  $error .= "Please provide a valid email address. ";
	}
	if($row_rsMailPrefs['dobrequired']==1 && strlen(trim($_POST['date_of_birth'])) == 0) { 
	  $error .= "Please provide a date of birth. ";
	} 
	$custom_field = preg_replace("/[^a-zA-Z0-9_\-]/", "_", $row_rsMailPrefs['text_custom']);
	if($row_rsMailPrefs['customrequired']==1 && strlen(trim($_POST[$custom_field])) == 0) { 
	  $error .= "Please provide ".htmlentities($row_rsMailPrefs['text_custom'], ENT_COMPAT, "UTF-8").". ";
	} 
	if($row_rsMailPrefs['messagerequired']==1 && strlen(trim($_POST['message'])) == 0) { 
	  $error .= "Please provide a message. ";
	} 
	if($row_rsMailPrefs['telephonerequired']==1 && ((isset($_POST['phone']) && strlen(trim($_POST['phone'])) == 0) || (isset($_POST['telephone']) && strlen(trim($_POST['telephone'])) == 0))) { 
	  $error .= "Please provide a contact telphone number. ";
	} 
	if($row_rsMailPrefs['addressrequired']==1 && strlen(trim($_POST['address'])) == 0) { 
	  $error .= "Please provide a contact address. ";
	} 
	if($row_rsMailPrefs['discoveredrequired']==1 && $_POST['discovered']=="") { 
	  $error .= "Please tell us how you discovered us. ";
	} 
	if($row_rsMailPrefs['namerequired']==1 && strlen(trim($fullname)) == 0) { 
	  $error .= "Please provide your name. ";
	} 
	if($row_rsMailPrefs['emailconfirm']==1 && isset($_POST['email']) && isset($_POST['email2']) && $_POST['email'] != $_POST['email2'] ) { 
	  $error .= "The two email addresses do not match. ";
	} 
	 
	
	$spam = spamCheck($usecaptcha, $row_rsMailPrefs['captchatype']);
	if($error=="" && $spam===false) { // passed spam check and other checks above	
		$attachments = array();			
		$uploaded = getUploads();	
		if (isset($uploaded) && is_array($uploaded)) {
			if(isset($uploaded["filename"]) && is_array($uploaded["filename"])) {
				foreach($uploaded["filename"] as $key => $value) {
					if(isset($uploaded["filename"][$key]["newname"]) && 
					$uploaded["filename"][$key]["newname"] !="") {
						$attachments[$key]["name"]="/Uploads/"
						.$uploaded["filename"][$key]["newname"];
						$attachments[$key]["type"]="/Uploads/"
						.$uploaded["filename"][$key]["type"];
					}
				}
			}
		}
		
		
		
		$subject = (isset($_POST['subject']) && $_POST['subject'] != "") ? $_POST['subject'] : "Contact Form";
		$subject .= " (sent from ".$_SERVER['HTTP_HOST'].")";
		$recipientID = isset($_POST['recipientID']) && intval($_POST['recipientID'])>0 ? intval($_POST['recipientID']) : 0;
		if($recipientID>0) {
			$select = "SELECT recipient, email, responsesubject, responsemessage FROM mailrecipient WHERE ID = ".$recipientID;
			$result = mysql_query($select, $aquiescedb) or die(mysql_error());
			$recipient = mysql_fetch_assoc($result);
			$to = $recipient['email'];
			$subject = "[".$recipient['recipient']."] ".$subject;			
		} else if(isset($_POST['recipient']) && $_POST['recipient']!="" && $_POST['key'] == md5($_POST['recipient'].@PRIVATE_KEY)) {
			$to = $_POST['recipient'];						
		}
		
		if(!isset($to) || !validEmail($to)) {
			$to =  (isset($row_rsPreferences['contactemail'])) ? $row_rsPreferences['contactemail'] : "hello@digdex.co.uk";
		}
		
		
		
		$friendlyfrom = (isset($_POST['full_name']) && $_POST['full_name']!="") ? $_POST['full_name'] : ((isset($_POST['firstname']) && $_POST['firstname']!="") ? $_POST['firstname']." ".$_POST['surname'] :  "");
		
		$header = "<html><head><style>table.box { width:100%; border-collapse:collapse; border-top-width: 1px;	border-top-style: solid;	border-top-color: #000;	border-left-width: 1px;	border-left-style: solid;	border-left-color: #000;} 
		table.box td {border-bottom-width: 1px;	border-bottom-style: solid;	border-bottom-color: #000;	border-right-width: 1px; border-right-style: solid;	border-right-color: #00; vertical-align:top; padding:5px; }
		table.box table, table.box td td { border:none; }</style></head><body><p>The following message has been sent from the web site:</p>";
		$message = "<table class=\"box\"><tr><td><table>";
		$footer = "</body></html>";
		
		
		$_POST['reply_using'] = isset($_POST['reply_using']) ? $_POST['reply_using'] : 0;
		if ($_POST['reply_using'] >0) {
			$header.= "<strong>Reply Using:<strong> ";
			switch ($_POST['reply_using']) {
				case 1:
					$header.= "Email<br>";
					break;
				case 2:
					$header.= "Phone<br>";
					break;
				case 3:
					$header.= "Post<br>";
					break;
			}
		}
		
		foreach($_POST as $key => $value) {
			if(!preg_match("/(captcha|messageheader|messagefooter|email2|reply_using|submit|sendmail|subject|logmail|max_file_size|key|responsemessage|directoryID|MM_Insert|type[0]|filename[0]|imageurl0|createddatetime|mailfolderID|autoreply|returnURL|recipient|mm-date|yy-date|dd-date)/i",$key)) {
				// if not one of the proprietory fields...
				
				
				if	(isset($value)) {
				$value = strval($value);
				$message.= (trim($value)!="") ? "<tr><td><strong>".ucwords(str_replace("_"," ",$key))."</strong>: </td><td>". nl2br(stripslashes(preg_replace("/([0-9]{4})-([0-9]{2})-([0-9]{2})/","$3/$2/$1",$value)))."<br></td></tr>" : "";
			}
			}
			
		}
		
		$header .=(isset($_POST['messageheader']) && $_POST['messageheader'] !="") ? "<br>".$_POST['messageheader'] : "";
		$message .="</table></td></tr></table>";
		$message.=(isset($_POST['messagefooter']) && $_POST['messagefooter'] !="") ? "<br>".$_POST['messagefooter'] : "";
		$message .=(!empty($attachments)) ? "There are attachments to this email." : "";
		
		$html = $header . $message.$footer;
		$fullmessage = strip_tags(str_replace("<br>","\n", $message));
		if($row_rsMailPrefs['html']==0) $html = ""; // get rid of html if not needed
		
		$from = $default_sender;
		if(isset($_POST['email']) && strpos($_POST['email'],"@")!==false) {
			$replyto = $_POST['email'];
		} else {
			$replyto = "";
			$friendlyfrom .= " (NO EMAIL SUPPLIED)";
		}
		
		$log =  isset($log) ? $log : (!isset($_POST["logmail"])) || ($_POST["logmail"] == "true") ? true : false;
		// auto response
		$_POST['autoreply'] = (isset($_POST['autoreply']) && $_POST['autoreply']  >0) ? 1 : 0;
		$_POST['mailfolderID'] = (isset($_POST['mailfolderID'] ) && $_POST['mailfolderID']  != "") ? $_POST['mailfolderID'] : 1;	
		
		$status = sendMail($to,$subject,$fullmessage,$from,$friendlyfrom,$html,$attachments, $log, "", "", "", 0, false, false, "", 1, $recipientID,$replyto);
		
			
		if ($status == "OK" && $_POST['autoreply']==1 && isset($_POST['email']) && strpos($_POST['email'],"@")) { // email exists and auto email
			
			
			$responsesubject = "Thank you!";
			if(isset($row_rsMailPrefs['responsesubject'])) {
				$responsesubject = $row_rsMailPrefs['responsesubject'];
			}			
			if(isset($recipient['responsesubject'])) {
				$responsesubject = $recipient['responsesubject'];
			}			
			if(isset($_POST['responsesubject'])) {
				$responsesubject = $_POST['responsesubject'];
			}
			
			
			
			$responsemessage = "We have received your message sent via our website.\n\nMany thanks for your enquiry and we will get back to you as soon as possible.";
			if(isset($row_rsMailPrefs['responsemessage'])) {
				$responsemessage = $row_rsMailPrefs['responsemessage'];
			}			
			if(isset($recipient['responsemessage'])) {
				$responsemessage = $recipient['responsemessage'];
			}			
			if(isset($_POST['responsemessage'])) {
				$responsemessage = $_POST['responsemessage'];
			}
			


		
			$from = $to; // reply from address orginally to
			$to = $_POST['email'];
			$responsemessage = $friendlyfrom.",\n\n".$responsemessage."\n\nYour form submission was:\n\n".strip_tags(str_replace("<br>","\n", $message));
			$friendlyfrom = $site_name;
			$status = sendMail($to,$responsesubject,$responsemessage,$from,$friendlyfrom);
		} // end auto response
		
		
		
		// add to contacts if set
		if ( $row_rsMailPrefs['addtocontacts'] ==1 && (isset($_POST['address']) || isset($_POST['email'])) && $error=="") { // we have opt in groups so add user 
			$names = explode(" ",$fullname,2);
			$names[1]  = isset($names[1]) ? $names[1] : "_";	
			
			$emailoptin = (isset($_POST['emailoptin'])) ? intval($_POST['emailoptin']) : 0;
			if($row_rsPreferences['emailoptintype'] == 2) { // reverse optin flag if opt out is set in preferences
				$emailoptin = ($emailoptin==1) ? 0 : 1;
			}
			if($row_rsPreferences['partneremailoptintype'] == 2) { // reverse optin flag if opt out is set in preferences
				$partneremailoptin = ($partneremailoptin==1) ? 0 : 1;
			}			
			$userID = (isset($_POST['userID']) && intval($_POST['userID'])>0) ? intval($_POST['userID']) : completeAddUser("", $names[0], $names[1], $_POST['email'], 0, 1,$emailoptin, "", 0, true, false, $_POST['telephone'], "", $_POST['address'], "", "", "", "", $_POST['postcode'], "", "", "", "", "", "","","","","","","", "", "","",$_POST['jobtitle']);		
			if(intval($userID>0) && !empty($_POST['optingroups'])) {
				foreach($_POST['optingroups'] as $groupID=> $value) { 					
					addUsertoGroup($userID, $groupID);				
				} 				
			}	
		} // end add to contacts
		return $status == "OK" ? true : $status;
	}  // end passed spam check and no errors	
	return $error.$spam;
} // end  process mail form
}


// mail functionality
if(!function_exists("sendMail")) {
function sendMail($to,$subject,$message,$from="",$friendlyfrom="",$htmlmessage="",$attachments=array(),$log=false,$cc="",$bcc="",$htmlhead="",$templateID=0,$unsubscribelink=false,$merge=false,$params="", $mailfolderID=0, $recipientID=0, $replyto="", $envelopefrom="") 
	{
		// FUNCTION RETURNS "OK" OR ERROR MESSAGE
	global $database_aquiescedb, $aquiescedb,$row_rsMailPrefs, $site_name;
	$senderEmail = ($row_rsMailPrefs['noreplyemail']!="") ? $row_rsMailPrefs['noreplyemail'] : "website@".gethostname();
	$envelopeFrom = $row_rsMailPrefs['envelopefrom'];
	
	$status = "OK";
	
	// if we're using template then take body from that instead
	if($templateID>0) {
		$select = "SELECT * FROM groupemailtemplate WHERE ID = ".intval($templateID);
		mysql_select_db($database_aquiescedb, $aquiescedb);
		$result = mysql_query($select, $aquiescedb) or die(mysql_error());
		if(mysql_num_rows($result)>0) {
			$template = mysql_fetch_assoc($result);
			$message = $template['templatemessage'];
			$htmlmessage  = $template['templateHTML'];
			$htmlhead = $template['templatehead'];
			$subject = $template['templatesubject'];
		}		
	} else { // not template
		$template = array("templatemessage"=>$message, "templateHTML" =>$htmlmessage, "templatehead" =>$htmlhead); 
	}
	
	$subject= (strlen($subject)>0 && strlen($subject)<255) ? $subject : "WWW Submission"; // removed htmlentities here
	$from = singleEmail($from);
	$from = (preg_match("/^.+@.+$/",$from)) ? $from : $senderEmail; 
	
	$replyto = ($replyto!="") ? $replyto : $row_rsMailPrefs['replytoemail'];	
	$replyto = ($replyto!="") ? $replyto : $from; 
	
	/*https://stackoverflow.com/questions/2049502/what-characters-are-allowed-in-an-email-address*/
	
	
	$friendlyfrom = ($friendlyfrom!="") ? substr(preg_replace("/[^A-Za-z0-9\.@_-]/", " ",$friendlyfrom),0,50) : $site_name;
	
	$message = strip_tags($message);
	
	if(is_array($merge)) {
		$message = mailMerge($message , "", "", $merge);
		$htmlmessage  = mailMerge($htmlmessage , "", "", $merge);
		$subject  = mailMerge($subject , "", "", $merge);
		/*if($_SESSION['MM_UserGroup']==10) {
			echo $message."*<br>";
			print_r($merge);
			die();
		}*/
	} else if($merge == true) {
		$message = mergeEmail($message, $to);
		$htmlmessage = mergeEmail($htmlmessage, $to);
		$subject = mergeEmail($subject, $to);
	}
	$message .= ($unsubscribelink==true) ? "\n\n\nIf you wish to unsubscribe from these emails please update your user profile on the web site or drop us an email." : "";
	
	;

	$headers = ($friendlyfrom !="") ? 'From: '.$friendlyfrom.' <'.$from.'>'."\n" : 'From: '.$from."\n";
	$headers .= ($friendlyfrom !="") ? 'Reply-To: '.$friendlyfrom.' <'.$replyto.'>' : 'Reply-To: '.$replyto;
	$headers .= ($cc !="") ? "\n".'Cc: '.$cc : "";
	$headers .= ($bcc !="") ? "\n".'Bcc: '.$bcc : "";
	$template['templateHTML'] = $htmlmessage;
	$htmlmessage = buildEmailHTML($template, $to);
	
	if(isset($row_rsMailPrefs['mailgunapi']) && strlen($row_rsMailPrefs['mailgunapi'])>10) {
		$from = ($from!="") ? $from : "website@".str_replace("www.","",$_SERVER['HTTP_HOST']);
		sendMailgun($row_rsMailPrefs['mailgunapi'], $row_rsMailPrefs['mailgundomain'], $from,$friendlyfrom, $to, $subject, $message, $htmlmessage, $attachments, $replyto,$cc,$bcc );
		
		
	} else { // send via server
	
	
		//multipart?
		if($htmlmessage !="" || !empty($attachments)) { // build message
		$random_hash = md5(date('r', time())); 
		$alt_header = 'Content-Type: multipart/alternative; boundary='.chr(34).'PHP-alt-'.$random_hash.chr(34);
		$alt_boundary = '--PHP-alt-'.$random_hash;
		$multi_header = 'Content-Type: multipart/mixed; boundary='.chr(34).'PHP-mixed-'.$random_hash.chr(34);
		$multi_boundary = '--PHP-mixed-'.$random_hash;
		$plain_header = 'Content-Type: text/plain; charset=UTF-8'."\n".
		'Content-Transfer-Encoding: 7bit';
		$html_header = 'Content-Type: text/html; charset=UTF-8'."\n".
		'Content-Transfer-Encoding: 7bit';
		if($htmlmessage!="") { // html
			
			$header2 = "\n".'MIME-Version: 1.0'."\n".$alt_header."\n";
			$message = "\n".$alt_boundary."\n".$plain_header."\n\r\n".$message."\r\n";
			$message .= "\n".$alt_boundary."\n";
			$message .= $html_header."\r\n\r\n".$htmlmessage."\n";
		} // end html
		
		if(is_array($attachments) && !empty($attachments)) { // are attachments
			$header2 = "\n".'MIME-Version: 1.0'."\n".$multi_header."\n";
			if($htmlmessage!="") {
				$message = "\n".$multi_boundary."\n".$alt_header."\n\n".$message."\n";
				$message .= "\n".$alt_boundary."--\n";
			} else {
				$message = "\n".$multi_boundary."\n".$plain_header."\n\n".$message."\n";	
			}		
			// add attachments
			foreach($attachments as $key => $attachment) { 
			
				$attachmentfile = chunk_split(base64_encode(file_get_contents(SITE_ROOT.$attachment['name'])));
				$message .= "\n".$multi_boundary."\n";
				$message .= 'Content-Type: '.getMimeType($attachment['name']).'; name="'.basename($attachment['name']).'"'."\n".
				'Content-Transfer-Encoding: base64'."\n".
				'Content-Disposition: attachment; filename="'.basename($attachment['name']).'"';
				$message .= "\n\n".$attachmentfile."\n";
			} // for each
			$message .= "\n".$multi_boundary."--\n";
		}// attachments
		$message = "This is a multi-part message in MIME format.\r\n".$message;
		$headers .= $header2;
		
		} // end build message
		
		//die(htmlentities($message));
		if(preg_match("/^.+@.+$/",$to) && !preg_match("/example\.com/",$to) && $message !="" && !defined("SUPRESS_MAIL")) { // we have someone to send to and something to send
			if($envelopeFrom!=1) {
				@ini_set("sendmail_from", $envelopeFrom); 
			}
			// set the envelope address - some hosts prevent this.
			$params = ($params!="") ? $params : (isset($envelopeFrom) && $envelopeFrom != "") ? "-f".$envelopeFrom : ""; // helps when local domain but external mail server
			if($params!="") {
				mail($to,$subject,$message,$headers,$params);
			} else {
				mail($to,$subject,$message,$headers);
			}
		
		} // end we have someone to send to and something to send
		   else { // error 
			   $status = "There was a problem. To: ".$to;
		}
	   
	} // end send via web server
	if($log) { logMail($mailfolderID, $to, $subject, $message, $attachments, $recipientID, $from, $friendlyfrom); 
		}

return $status;
	} // end sendmail
}

// get MIME Type of attachment
if(!function_exists("getMimeType")) {
function getMimeType($attachment){
	$nameArray=explode('.',basename($attachment));
	switch(strtolower($nameArray[count($nameArray)-1])){
		case 'jpg':
		$mimeType='image/jpeg';
		break;
		case 'jpeg':
		$mimeType='image/jpeg';
		break;
		case 'gif':
		$mimeType='image/gif';
		break;
		case 'png':
		$mimeType='image/png';
		break;
		case 'txt':
		$mimeType='text/plain';
		break;
		case 'rtf':
		$mimeType='text/rtf';
		break;
		case 'doc':
		$mimeType='application/msword';
		break;
		case 'docx':
		$mimeType='application/msword';
		break;
		case 'xls':
		$mimeType='application/vnd.ms-excel';
		break;
		case 'ppt':
		$mimeType='application/vnd.ms-powerpoint';
		break;
		case 'pdf':
		$mimeType='application/pdf';
		break;
		case 'csv';
		$mimeType='text/csv';
		break;
		case 'html':
		$mimeType='text/html';
		break;
		case 'htm':
		$mimeType='text/html';
		break;
		case 'xml':
		$mimeType='text/xml';
		break;
		case 'zip':
		$mimeType='application/zip';
		break;
		default : $mimeType='unknown';
	}
	return $mimeType;
}
}

if(!function_exists("logMail")) {
function logMail($mailfolderID=0, $to, $subject, $message, $attachments=array(), $recipientID=0, $from="", $friendlyfrom="") {
	global $_POST, $database_aquiescedb, $aquiescedb, $regionID;
	$_POST['autoreply'] = isset($_POST['autoreply']) ? $_POST['autoreply'] : 0;
	$_POST['reply_using'] = isset($_POST['reply_using']) ? $_POST['autoreply'] : 0;
	$from = ($from=="") ? $_POST['emil'] : $from;
	

// $recipientID = mail department
	
	$sessionID = isset($_SESSION['fb_tracker']) ? $_SESSION['fb_tracker'] : "";
  $insertSQL = sprintf("INSERT INTO correspondence (recipient, recipientID, subject, message, createddatetime, sentdatetime, mailfolderID, autoreply, sender, sendername, reply_using, telephone, address, directoryID, regionID, sessionID) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
                       GetSQLValueString($to, "text"),
					   GetSQLValueString($recipientID, "int"),
                       GetSQLValueString($subject, "text"),
                       GetSQLValueString($message, "text"),
                       "NOW(),NOW()",
                       GetSQLValueString($mailfolderID, "int"),
                       GetSQLValueString(@$_POST['autoreply'], "int"),
                       GetSQLValueString($from, "text"),
                       GetSQLValueString($friendlyfrom, "text"),
                       GetSQLValueString(@$_POST['reply_using'], "int"),
                       GetSQLValueString(@$_POST['phone'], "text"),
                       GetSQLValueString(@$_POST['address'], "text"),
                       GetSQLValueString(@$_POST['directoryID'], "int"),
					   GetSQLValueString($regionID, "int"),
					   GetSQLValueString($sessionID, "text"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($insertSQL, $aquiescedb) or die(mysql_error());
}
// any attachments to record?
if(!empty($attachments)) {
	$correspondenceID = mysql_insert_id();
	foreach($attachments as $key => $attachment) {
		$insert = "INSERT INTO mailattachments (correspondenceID, filename, mimetype) VALUES (".$correspondenceID.",'".$attachment['name']."','".$attachment['type']."')";
		$result = mysql_query($insert, $aquiescedb) or die(mysql_error());
	}

} // end logMail
}

if(!function_exists("singleEmail")) {
function singleEmail($email, $separator = ",") {
	$emails = explode($separator, $email);
	return trim($emails[0]);
}
}


function mergeEmail($message, $email) {
	global $database_aquiescedb, $aquiescedb;
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$select = "SELECT users.*, users.ID AS userID, directory.name AS company  FROM users LEFT JOIN directory ON (users.ID = directory.userID) WHERE users.email = ".GetSQLValueString($email,"text")." LIMIT 1";
	$result = mysql_query($select, $aquiescedb) or die(mysql_error());
	if(mysql_num_rows($result)>0) {
	
		$message = mailMerge($message ,$result);
	}
	
	return $message;
}

if(!function_exists("mailMerge")) {
function mailMerge($message ,$recipients = "", $signature="", $merge=array()) {
	// will find and  replace :
	// 1. Standard fields, e.g. date
	// 2. Any GET variables  (that are not in $merge)
	// 4. Any fields in $recipients mysql resource multiple times // PRINT ONLY
	// 3. Any fields in $merge array , e.g. $merge['invoicenumber'] = "53625623" (same for each recipient)
	
	// 1. do one offs
	$html = "";
	$date = isset($_REQUEST['date']) ? $_REQUEST['date'] : date('jS F Y');
	$message = str_ireplace("{date}",$date,$message);
	$message = str_ireplace("{page break}","</div><div class=\"a4page\">",$message);
	$message = $signature!="" ? str_ireplace("{signature}",$signature,$message) : $message;
	
	// 2. Any GET variables
	foreach($_REQUEST as $key => $value) {
	// PHP replaces spaces in variable names with underscores
		if(!isset($merge[$key])  && is_string($value)) { // if not already in $merge array and value exists
			$key = str_replace("_"," ",urldecode($key));
			$message = str_ireplace("{".$key."}",stripslashes($value),$message);
		}
	}
	
	// 3. Any $merge values
	if(is_array($merge) && count($merge)>0) {
		foreach($merge as $key => $value) {		
			$key = strpos($key,"{")===0 ? $key :	"{".$key."}"; // if curly brackets already in key	
			$message = str_ireplace($key,$value,$message);
			
		}
	}
	
	// 4. Any fields in $recipients mysql resource multiple times
	if((is_resource($recipients) || is_object($recipients)) && mysql_num_rows($recipients)>0) {
		
		mysql_data_seek($recipients,0);
		while($row_rsRecipients = mysql_fetch_assoc($recipients)) {
			$mergebody = $message;	
			
			foreach($row_rsRecipients as $key => $value) {
				if($key=="password") {
					$username  = isset($row_rsRecipients['username']) ? $row_rsRecipients['username'] : $row_rsRecipients['email'];
				   $value = isset($row_rsRecipients['plainpassword']) ? $row_rsRecipients['plainpassword'] : "<a href=\"".getPasswordLink($username)."\">Click here to set your password.</a>";
				}
				if($key=="ID") $key="userID";
				$mergebody = str_ireplace("{".$key."}",$value,$mergebody);
				
			} // end foreach
			// add token for user authentication links (to avoid necessity to log in)
			$mergebody = str_replace("{usertoken}",md5(PRIVATE_KEY.$row_rsRecipients['ID']),$mergebody);
			
					
			$address = (isset($row_rsRecipients['address1'])) ? nl2br(trim($row_rsRecipients['address1']."\n".$row_rsRecipients['address2']."\n".$row_rsRecipients['address3']."\n".$row_rsRecipients['address4']."\n".$row_rsRecipients['address5'])."\n".trim($row_rsRecipients['postcode'])) : "";
			$mergebody = str_ireplace("{address}",$address,$mergebody);			
			
			$html .= $mergebody;
		}		
	} else {
		$html = $message;
	}	
	// remove any remaining unused fields if not just template view
	if(count($_GET)>1 || count($_POST)>1 || $recipients!="") {
		// replace any unfound firstnames with 'Reader'
		$html = str_ireplace("{firstname}","Reader",$html);
		// clear any reamining unused merge fields
		$html = preg_replace('/\{([A-Za-z0-9\&\;\s]+)\}/', '', $html);
	}
	return $html;
}
}

if(!function_exists("spamCheck")) {
function spamCheck($usecaptcha=0, $captchatype=0) { // mail sent do security check point
	global $row_rsPreferences;
	$errors = "";
	if($usecaptcha ==1 || (isset($_SESSION['showCaptcha']) && $_SESSION['showCaptcha'] ==true)) { // captcha check 
		if($captchatype==1) { // letters
			if(!isset($_POST['captcha_answer']) || !isset($_SESSION['captcha']) || md5(strtolower($_POST['captcha_answer'])) != $_SESSION['captcha']) { 
			// secury image incorrect
				$errors .= "You have typed the security letters incorrectly. Please try again. \n";
			} else {
				unset($_SESSION['showCaptcha']); // succesful send so unset for next time
			}
		} else if($captchatype==2 || $captchatype==3){ // reCaptcha
			if(isset($_POST['g-recaptcha-response'])) {          
				$response=json_decode(curl_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=".$row_rsPreferences['recaptcha_secret_key']."&response=".$_POST['g-recaptcha-response']."&remoteip=".getClientIP()), true);
				//echo "https://www.google.com/recaptcha/api/siteverify?secret=".$row_rsPreferences['recaptcha_secret_key']."&response=".$_POST['g-recaptcha-response']."&remoteip=".getClientIP()." * "; print_r($response); die();
				if($response['success'] == false) {
					$errors .= "Sorry, you have failed the Captcha test. Please try again. ";
				} // end fail
			} else { // no captch post
				$errors .= "Sorry, you have failed the Captcha test. Please try again. ";
			}
		}
	} else { // check for spam (only if no captcha)
		foreach($_POST as $key => $value) {
			if(!is_array($value)) { // ignore attachment array
   				$$key = trim($value);
  				if(preg_match("/(http|<|www|cc:)/i",$value)) { // suspect post
  					if($usecaptcha==2) { // prefs set to use captcha if needbe
  						$_SESSION['showCaptcha'] = true; // set session to use captcha in mail resend
  						$errors .= "For extra security and to help reduce spam, please enter the security letters below and resend. \n";
  						unset($_POST["sendmail"]);
						unset($_POST["MM_insert"]);
  					} else { // kill
  						$errors .= "Your comments have NOT been sent as they contain disallowed content, e.g. you cannot supply links to other web sites. Please click back button and try again. \n";
  					}// no captch rule
  				}  // end suspect
			} // end ignore attachment array
		} // end  foreach
	} // check for spam
	if ($errors == "") {			
		return false;
	} else {
		return $errors;
	}
} // end mail sent
}

if(!function_exists("replace_first")) {
function replace_first($search, $replace, $data, $offset) { 
    $res = strpos($data, $search,$offset); 
    if($res === false) { 
        return $data; 
    } else { 
        // There is data to be replaced 
        $left_seg = substr($data, 0, strpos($data, $search ,$offset)); 
        $right_seg = substr($data, (strpos($data, $search ,$offset) + strlen($search))); 
        return $left_seg . $replace . $right_seg; 
    } 
}
}

if(!function_exists("addClickTracking")) {
function addClickTracking($html, $emailID) {
	$html = stripslashes($html);
	$foundpos=0;
	while(strlen($html)>($foundpos+10) && $foundpos = strpos($html,"<a href=",$foundpos+1)) {		
		$endpos = strpos($html,"\"",$foundpos+10);
		$url = substr($html,$foundpos+9,$endpos-$foundpos-9);
		$html = replace_first($url,PROTOCOL.$_SERVER['HTTP_HOST']."/mail/clicktrack.php?clicktrackurl=".urlencode($url)."&clicktrackemailID=".intval($emailID)."&clicktrackuseremail={email}",$html,$foundpos);
		
	}
	$html = str_replace("%7B","{",$html); // revert merge fields back
	$html = str_replace("%7D","}",$html);
	return $html;
}
}

if(!function_exists("removeClickTracking")) {
function removeClickTracking($html) {
	$foundpos=0;
	$search= PROTOCOL.$_SERVER['HTTP_HOST']."/mail/clicktrack.php?url=";
	while($foundpos = strpos($html,"<a href=",$foundpos+1)) {		
		$endpos = strpos($html,"\"",$foundpos+10);
		$url = substr($html,$foundpos+9,$endpos-$foundpos-9);
		$parseurl = parse_url($url);
		$query = isset($parseurl['query']) ? parse_str($parseurl['query']) : "";
		if(isset($clicktrackurl)) {
			$html = replace_first($url,urldecode($clicktrackurl),$html,$foundpos);
		}		
	}	
	return $html;	
}
}

if(!function_exists("addGroupEmail")) {
function addGroupEmail($subject = "", $message = "", $usertypeID = 1, $usergroupID = 0, $from = "", $fromname= "", $templateID = 0, $head="", $html="", $regionID = 1, $createdbyID = 0,  $showunsubscribe = 0, $startdatetime = "NOW()", $active= 1, $trackclicks = 0 , $viewonline = 1) {
	$usergroupID = ($usergroupID>0) ?  $usergroupID: 0;
	global $database_aquiescedb, $aquiescedb, $row_rsMailPrefs;
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$usertypeID = intval($usertypeID);
	$createdbyID = intval($createdbyID);
	
	$senderEmail = ($row_rsMailPrefs['noreplyemail']!="") ? $row_rsMailPrefs['noreplyemail'] : "website@".gethostname();
	$from = (strpos($from,"@")!==false) ? $from : $noReplyAddress;
	$startdatetime  = strlen($startdatetime )>0 ? $startdatetime  : date('Y-m-d H:i:s');
	
	if($templateID>0) {	
		$select = "SELECT templatehead, templateHTML, viewonline, templatedefaultfirstname FROM groupemailtemplate WHERE ID = ".GetSQLValueString($_POST['templateID'],"int");
		$result = mysql_query($select, $aquiescedb) or die(mysql_error());
		$row =  mysql_fetch_assoc($result);
		$head = $row['templatehead']; 
		$html = $row['templateHTML'];
		$bodytag = $row['templatebodytag'];
		$viewonline = $row['viewonline'];
		$defaultfirstname = $row['templatedefaultfirstname'];
		
	}
	 $insert = "INSERT INTO groupemail (startdatetime, usertypeID, usergroupID, `from`, fromname, subject, message, templateID, regionID, head, html,bodytag, showunsubscribe, trackclicks, viewonline, defaultfirstname, createdbyID, createddatetime, active) VALUES (".
                       GetSQLValueString($startdatetime, "date").",".
                       GetSQLValueString($usertypeID, "int").",".
                       GetSQLValueString($usergroupID, "int").",".
                       GetSQLValueString($from, "text").",".
                       GetSQLValueString($fromname, "text").",".
                       GetSQLValueString($subject, "text").",".
					   GetSQLValueString($message, "text").",".
                       GetSQLValueString($templateID, "int").",".
                       GetSQLValueString($regionID, "int").",".
					   GetSQLValueString($head, "text").",".
					   GetSQLValueString($html, "text").",".
					   GetSQLValueString($bodytag, "text").",".
					   GetSQLValueString($showunsubscribe, "int").",".
					   GetSQLValueString($trackclicks, "int").",".
					   GetSQLValueString($viewonline, "int").",".
					   GetSQLValueString($defaultfirstname, "text").",".
                       GetSQLValueString($createdbyID, "int").",NOW(),".
                       GetSQLValueString($active, "int").")";

  mysql_select_db($database_aquiescedb, $aquiescedb);
  mysql_query($insert, $aquiescedb) or die(mysql_error());
  $emailID = mysql_insert_id();
  if($trackclicks == 1 && $html !="") {
	  $html = addClickTracking($html, $emailID);
	  $update = "UPDATE groupemail SET html = ".GetSQLValueString($html, "text")." WHERE ID = ".$emailID;
	
	  mysql_query($update, $aquiescedb) or die(mysql_error());	  
  }
  if(function_exists("buildGroupSet")) buildGroupSet($usergroupID);
  createMailList($emailID); 
  return $emailID;
}
}

if(!function_exists("createMailList")) {
function createMailList($groupemailID) {
	global $database_aquiescedb, $aquiescedb;
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$select = "SELECT * FROM groupemail LEFT JOIN groupemaillist ON (groupemaillist.groupemailID = groupemail.ID) WHERE groupemail.ID = ".intval($groupemailID)." ORDER BY groupemaillist.sent DESC LIMIT 1";
	$result = mysql_query($select, $aquiescedb) or die(mysql_error());
	$row_rsGroupEmail = mysql_fetch_assoc($result);
	if(!isset($row_rsGroupEmail['sent']) || $row_rsGroupEmail['sent']==0) {  // none sent so far	
	// order by rand to ensure people in group recieve in different order each time
		$select = "SELECT users.ID FROM users LEFT JOIN usergroupmember ON (users.ID = usergroupmember.userID) LEFT JOIN directory ON (users.ID = directory.userID) WHERE  (users.emailoptin = 1 OR ".$row_rsGroupEmail['ignoreoptout']." = 1) AND users.emailbounced = 0 AND users.email IS NOT NULL AND users.usertypeID >= ".$row_rsGroupEmail['usertypeID']." AND (".$row_rsGroupEmail['regionID']." = 0  OR users.regionID IS NULL OR users.regionID =0 OR users.regionID = ".$row_rsGroupEmail['regionID'].") AND (".$row_rsGroupEmail['usergroupID']." = 0 OR (usergroupmember.groupID =".$row_rsGroupEmail['usergroupID']." AND usergroupmember.statusID = 1 AND (usergroupmember.expirydatetime IS NULL OR usergroupmember.expirydatetime >= NOW()))) GROUP BY users.email ORDER BY RAND()";
		
		$result = mysql_query($select, $aquiescedb) or die(mysql_error().": ".$select);
		if(mysql_num_rows($result)>0) {
			// remove any existing lists
			
			$delete = "DELETE FROM groupemaillist WHERE groupemailID = ".intval($groupemailID);
			mysql_query($delete, $aquiescedb) or die(mysql_error().": ".$delete);
			while($row = mysql_fetch_assoc($result)) {
				$insert = "INSERT INTO groupemaillist (groupemailID, userID) VALUES (".intval($groupemailID).",".$row['ID'].")";
				mysql_query($insert, $aquiescedb) or die(mysql_error().": ".$insert);
			}
			$optimize = "OPTIMIZE TABLE groupemaillist";
			mysql_query($optimize, $aquiescedb)  or die(mysql_error().": ".$optimize);
			return true;
		} // are users to send to
	} // no list exists
	return false;	
}
}

if(!function_exists("pauseGroupEmail")) {
function pauseGroupEmail($groupemailID) {
	global $database_aquiescedb, $aquiescedb;
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$update = "UPDATE groupemail SET active = 0 WHERE ID = ".intval($groupemailID);
	mysql_query($update, $aquiescedb) or die(mysql_error());
}
}

if(!function_exists("deleteMailList")) {
function deleteMailList($groupemailID) {
	global $database_aquiescedb, $aquiescedb;
	mysql_select_db($database_aquiescedb, $aquiescedb);
	pauseGroupEmail($groupemailID);
	$delete = "DELETE FROM groupemaillist WHERE groupemailID = ".intval($groupemailID);
	mysql_query($delete, $aquiescedb) or die(mysql_error());
}
}

if(!function_exists("repopulateForm")) {
function repopulateForm($formname="contactform") { // repopulate mail form on failed post
	// needs to be placed between HEAD tags to write javascript
	// JQuery is required
	// $formname is required
	if(isset($_POST["sendmail"])) {
		echo "<script>\n";
		echo "\$(document).ready(function() {\n";
		foreach($_POST as $key => $value) {
			echo "inputType = document.".htmlentities($formname, ENT_COMPAT, "UTF-8").".".htmlentities($key, ENT_COMPAT, "UTF-8").".type;\n";
			echo "if(inputType=='text' || inputType=='hidden') {\n";
			echo "  document.".htmlentities($formname, ENT_COMPAT, "UTF-8").".".htmlentities($key, ENT_COMPAT, "UTF-8").".value = '".htmlentities($value, ENT_COMPAT, "UTF-8")."';\n";
			echo "} else if(inputType=='textarea') {\n";
			echo "  document.".htmlentities($formname, ENT_COMPAT, "UTF-8").".".htmlentities($key, ENT_COMPAT, "UTF-8").".innerHTML = '".htmlentities($value, ENT_COMPAT, "UTF-8")."';\n";
			echo "} else if(inputType=='checkbox') {\n";
			echo "  document.".htmlentities($formname, ENT_COMPAT, "UTF-8").".".htmlentities($key, ENT_COMPAT, "UTF-8").".checked;\n";
			echo "}\n";
		}
		echo "});\n";
		echo "</script>\n";
	}	
}
}

if(isset($_POST["sendmail"])) { // backwards compat
	global $row_rsMailPrefs;
	$status = processMailForm(true, $row_rsMailPrefs['useCaptcha']);
	
	if ($status === true || $status == 1) {
		$msg = isset($_POST['responsemessage']) ? $_POST['responsemessage'] : "Thank you. We have received your submission.";
	} else  {
		$error = $status; 
	}	
	
	if(isset($_POST['returnURL']) && $_POST['returnURL'] !="") { 
		$url = $_POST['returnURL'];
		$url .= strpos($_POST['returnURL'],"?") ? "&" : "?";
		$url .= "enquiry=sent";
		if(isset($error)) {			
			$url .= "&alert=".urlencode("Sorry, your message was not sent due to errors:\n".$error);
		}  else {
			$url .= "&msg=".urlencode($msg);
		}
		
		header("location: ".$url); exit;
	} 
}




?>