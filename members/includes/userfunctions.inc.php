<?php /**

REQUIRES directoryfunctions.inc.php  for completeAddUser!

*/ ?><?php require_once(SITE_ROOT.'core/includes/framework.inc.php'); ?>
<?php require_once(SITE_ROOT.'mail/includes/sendmail.inc.php'); ?>
<?php 

$regionID = (isset($regionID) && $regionID>0) ? intval($regionID) : 1;
$console = isset($console) ? $console : "";

mysql_select_db($database_aquiescedb, $aquiescedb);
$select = "SELECT * FROM preferences WHERE ID = ".$regionID;
$result = mysql_query($select, $aquiescedb) or die(mysql_error());
$userPreferences = mysql_fetch_assoc($result);
	

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

/** ONE WAY (HASH) ENCRYPTION **/
if(!function_exists("encryptPassword")) {
function encryptPassword($password, $crypttype=1,$md5salt="") {
	if($crypttype==2) {
		$password = password_hash(md5($password), PASSWORD_BCRYPT); // bcrypt over the original md5 to allow upgrade
	} else {
		 $password = md5($password.$md5salt); // md5
	}
	return $password;
}
}
/** TWO WAY (HASH) ENCRYPTION **/
if(!function_exists("encryptString")) {
function encryptString($string) {
	if(!defined("PRIVATE_KEY")) die("No private key defined");

	$iv = mcrypt_create_iv(
		mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC),
		MCRYPT_DEV_URANDOM
	);
	
	$encrypted = base64_encode(
		$iv .
		mcrypt_encrypt(
			MCRYPT_RIJNDAEL_128,
			hash('sha256', PRIVATE_KEY, true),
			$string,
			MCRYPT_MODE_CBC,
			$iv
		)
	);
	return $encrypted;
}
}


if(!function_exists("decryptString")) {
function decryptString($encrypted) {
	if(!defined("PRIVATE_KEY")) die("No private key defined");
	$data = base64_decode($encrypted);
	$iv = substr($data, 0, mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC));
	
	$decrypted = rtrim(
		mcrypt_decrypt(
			MCRYPT_RIJNDAEL_128,
			hash('sha256', PRIVATE_KEY, true),
			substr($data, mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC)),
			MCRYPT_MODE_CBC,
			$iv
		),
		"\0"
	);
	return $decrypted;
}
}


// add user when user, address, company fields are all persent - also checks uniquieness
// will return userID if successful otherwise error message
if(!function_exists("completeAddUser")) {
function completeAddUser($salutation="",$firstname, $surname, $email, $usertypeID=-1, $termsagree=0,$emailoptin=1, $groupID=0, $createdbyID=0, $unique=true, $login=false, $telephone="", $mobile="", $address1="", $address2="", $address3="", $address4="", $address5="", $postcode="", $companyname="", $companyaddress1="", $companyaddress2="", $companyaddress3="", $companyaddress4="", $companyaddress5="", $companypostcode="",$username="",$password="",$gender="",$dob="",$agerangeID="", $ethnicityID="", $disabilityID="",$regionID=0,$jobtitle="",$discovered="",$latitude="",$longitude="",$companylongitude="",$companylatitude="",$facebookURL="",$twitterID="",$websiteURL="", $partneremailoptin=0, $discoveredother="",$middlename="") {	
	global $database_aquiescedb, $aquiescedb, $userPreferences;
	$error = ""; $userID = 0;
	//clean
	$username = substr(preg_replace("/[^A-Za-z0-9\.@_\,\-]/", "",$username),0,50);
	$email = substr(preg_replace("/[^A-Za-z0-9\.@_\,\-]/", "",$email),0,50);
	$firstname = trim($firstname);
	$surname = trim($surname);
	$username = ($userPreferences['emailasusername'] == 1) ? $email : $username;
	// check for errors
	if($firstname=="") { 
 		$error .= "Please enter a name. ";
 	}
 	if($surname=="") { 
 		$error .= "Please enter a surname. ";
 	}
 	if($email !=""  && !validEmail($email)) { 
 		$error .= "Please enter a valid email address. ";
 	} 
	if($unique && emailTaken($email)>0) {
    	$error .= "The email address ".$email." has already been used to register. Maybe you have already signed up previously? If you don't have your login details <a href=\"/login/forgot_password.php?username=".$email."\">click here</a>. ";	
	}
	if($login && $userPreferences['autousername'] != 1) { // login and not autousername
  		$loginUsername = $username;
  		$LoginRS__query = sprintf("SELECT username FROM users WHERE username=%s", GetSQLValueString($loginUsername, "text"));
  		$result=mysql_query($LoginRS__query, $aquiescedb) or die(mysql_error());

  		//if there is a row in the database, the username was found - can not add the requested username
  		if(mysql_num_rows($result)>0){
    		$error .= "The username ".$username." is already taken. If you have signed up before, you can get your password <a href=\"/login/forgot_password.php?username=".$username."\">here</a> or please try another. ";
  		}
  		if(strlen($username)<6) {
			$error .= "Your chosen username is too short. ";
  		}
  		if(strlen($password)<6) {
			$error .= "Your chosen password at ".strlen($password)." letters is too short. ";
  		}
		if(strpos($password," ")!==false) {
			$error .= "Your password cannot contain spaces. ";
  		}
		
	}// end login and not auto
	// add user
	if($error=="") {
		$userID = createNewUser($firstname,$surname,$email,$usertypeID,$groupID,0,$createdbyID,$salutation,$login,$username,$password,$gender,$dob,$agerangeID, $ethnicityID, $disabilityID,$regionID,$jobtitle,$termsagree,$emailoptin,$discovered,"", 0, $facebookURL,$twitterID,$websiteURL, "", 0, $partneremailoptin, $discoveredother,$middlename);		
		if(strlen($address1) > 1 || strlen($telephone)>1) { // is address or telephone
			$locationname = trim($firstname." ".$surname);
			$locationID = createLocation(0,0,$locationname,"",$address1,$address2,$address3,$address4,$address5,$postcode,$telephone, "", "", "", "", "", "",$latitude, $longitude,$createdbyID);
			if(intval($locationID)>0) {
				addUserToLocation($userID, $locationID, $createdbyID, true);
			}
		} // end is address
		if(strlen($companyname)>1) { // is company
			$directoryID = createDirectoryEntry("",$companyname,"",$companyaddress1,$companyaddress2,$companyaddress3,$companyaddress4,$companyaddress5,$companypostcode,"","","","","",$latitude,$longitude,"","","","",1,0,$createdbyID);
			if($directoryID>0) {
				addUserToDirectory($userID, $directoryID, $createdbyID);
			}
		} // end is company
	}	
	return ($userID>0) ? $userID : $error;
}
}

if(!function_exists("addUsertoGroup")) {
function addUsertoGroup($userID, $groupID,$createdbyID=0, $expiry = "", $log = false, $notify = true) { // check if already a member
	if(intval($userID>0) && intval($groupID)>0) {
		$createdbyID = ($createdbyID==0) ? $userID : $createdbyID;
		global $database_aquiescedb, $aquiescedb;
		mysql_select_db($database_aquiescedb, $aquiescedb);
		if (!userinGroup($groupID, $userID)) { // add to group
			$insert = "INSERT INTO usergroupmember (userID, groupID, expirydatetime, createdbyID, createddatetime) VALUES (".intval($userID).",".intval($groupID).",".GetSQLValueString($expiry, "date").",".$createdbyID.",NOW())";
 			$result = mysql_query($insert, $aquiescedb) or die(mysql_error());
			if($log && function_exists("writeLog")) { writeLog($insert); }
			if($notify) {
				sendGroupNotification($userID, $groupID);
			}
		}// end add to group
	}
}
}


if(!function_exists("sendGroupNotification")) {
	function sendGroupNotification($userID=0, $groupID=0) {
		if($userID>0 && $groupID>0) {
			global $database_aquiescedb, $aquiescedb, $site_name;
			$select = "SELECT * FROM usergroup WHERE ID = ".intval($groupID);
			$result = mysql_query($select, $aquiescedb) or die(mysql_error());
			if(mysql_num_rows($result)>0) {
				$group = mysql_fetch_assoc($result);
				if(strpos($group['notificationemail'],"@")>1) {
					$select = "SELECT ID, firstname, surname, email FROM users WHERE ID = ".intval($userID);
					$result = mysql_query($select, $aquiescedb) or die(mysql_error());
					$user= mysql_fetch_assoc($result);
					$to = $group['notificationemail'];
					$subject = $user['firstname']." ".$user['surname']." joined ".$group['groupname'];
					$message = "This is an automated email to inform you that the following user has now joined the web site group \"".$group['groupname']."\":\n\n";
					$message .= isset($user['surname']) ? $user['firstname']." ".$user['surname']."\n\n" : "";
					$message .=  isset($user['email']) ? "Email: ".$user['email']."\n\n" : "";
					$message .= "Log in to view their profile:\n\n";
					$message .= getProtocol()."://". $_SERVER['HTTP_HOST']."/members/admin/modify_user.php?userID=".$user['ID'];
					sendMail($to,  $subject,  $message);					
				}
			}			
		}
	}
}

if(!function_exists("setUsernamePassword")) {
function setUsernamePassword($userID,$username="",$password="", $sendemail = false, $replace = false) {
	// replace is false - only generate if none exists
	global $database_aquiescedb, $aquiescedb, $userPreferences, $site_name, $row_rsChosenGroup;
	$select = "SELECT firstname,surname,email, username FROM users WHERE ID =".GetSQLValueString($userID, "int");
	$user = mysql_query($select, $aquiescedb) or die(mysql_error());
	$row_user =  mysql_fetch_assoc($user);
	if(!isset($row_user['username']) || $replace) { // credentials already?
		if($userPreferences['emailasusername'] == 1 && isset($row_user['email'])) { // if set in prefs use email as username
			$username = $row_user['email'];
		} else if($username =="") {// generate username or use one provided
		
			$username = (trim($row_user['username'])!="") ? $row_user['username'] : strtolower(preg_replace("/[^A-Za-z]/", "", substr($row_user['firstname'],0,1).$row_user['surname']));	
			
		} // end generate username
		// does username exist?
		 mysql_select_db($database_aquiescedb, $aquiescedb);
		 $count = 0;
		 do {
			$append = $count>0 ? $count : "";
			$select = "SELECT username FROM users WHERE ID != ".intval($userID)." AND username = ".GetSQLValueString($username.$append,"text");
			$result = mysql_query($select, $aquiescedb) or die(mysql_error());
			$count ++;
		 } while (mysql_num_rows($result) >0);
		$username = $username.$append;
		// generate password or use one provided
		$password = ($password=="") ? generatePassword(8,1,$userPreferences['passwordspecialchar'],$userPreferences['passwordmulticase']) : $password;
		
		$md5salt = md5(generateSalt());
		$enctryptedpassword = encryptPassword($password,$userPreferences['defaultcrypttype'], $md5salt);
		$insertplainpassword = ($userPreferences['passwordencrypted'] != 1) ? ", plainpassword = ".GetSQLValueString($password,"text") : "";
		$update = "UPDATE users SET username ='".$username."', password='".$enctryptedpassword."', password_salt = '".$md5salt."'".$insertplainpassword." WHERE ID = ".GetSQLValueString($userID, "int");
		$result = mysql_query($update, $aquiescedb) or die(mysql_error());
		if($sendemail && function_exists("sendMail") && strpos($row_user['email'],"@")>0) {
			$to = $row_user['email'];
			$subject = "Your ".$site_name." login details";
			$message = "Dear ".$row_user['firstname'].",\n\n";
			$message .= "You have been added as a user at ".$site_name.".\n\n";
			if(isset($row_rsChosenGroup['groupname'])) {
				$message .= "You have also been added to the group: ".$row_rsChosenGroup['groupname']."\n\n";
			}
			$message .= "You login details are as follows:\n\n";
			$message .= "USERNAME: ".$username."\n";
			$message .= "PASSWORD: ".$password."\n\n";
			$message .= "Log in here:\n\n";
			$message .= getProtocol()."://". $_SERVER['HTTP_HOST']."/members/";
			sendMail($to,$subject,$message);
			
		} // end send email
		return $username."|".$password; // user explode to get these values
	} // end has credentials
} // end function
}
	
	
	// password generation
if(!function_exists("generatePassword")) {	
function generatePassword($letters = 8,$numbers = 0, $symbol = false, $mixedcase = false) {	
	// will provide a "readable" password of letters with optional numbers and/or symbol at end for extra strength
	$symbols=array("!","@","$","%","&","*",",",".",":",";");
	$vowels=array("a","e","i","o","u"); 
    $consonants=array("b","c","d","f","g","h","j","k","l","m","n","p","r","s","t","v","w","x","y","z");     
    $password=""; 
    srand ((double)microtime()*1000000); 
	
    $letters = $letters/2; // two letter sadded at a time
    for($i=1; $i<=$letters; $i++) 
    { 
    	$password.= ($mixedcase==1 && rand(0,1)) ? strtoupper($consonants[rand(0,19)]) : $consonants[rand(0,19)]; 
    	$password.=$vowels[rand(0,4)]; 
	}	
		$password .= ($numbers) ? (string)rand(0,pow(10,$numbers)-1) : "";
		$password .= ($symbol) ? $symbols[rand(0,9)] : "";
    	
	return $password;
}
}

if(!function_exists("generateSalt")) {	
function generateSalt()
	{	
	
	$dynamicSalt = "";	   
      for ($i = 0; $i < 50; $i++) { 
          $dynamicSalt .= chr(rand(33, 126));
      }
      return $dynamicSalt;
	} 	
	}
	

if(!function_exists("createNewUser")) {	
function createNewUser($firstname,$surname="",$email="",$usertypeID=-1,$groupID=0,$directoryID=0,$createdbyID=0,$salutation="",$login=true,$username="",$password="",$gender="",$dob="",$agerangeID="", $ethnicityID="", $disabilityID="",$regionID=0,$jobtitle="",$termsagree=0,$emailoptin=1,$discovered="",$mobile="",$locationID=0,$facebookURL="",$twitterID="",$websiteURL="", $telephone="", $identityverified = 0,  $partneremailoptin=0, $discoveredother="",$middlename="") {
	global $database_aquiescedb, $aquiescedb, $regionID, $console;
	$regionID = isset($regionID) ? $regionID : 1;
	if(trim($firstname.$surname.$email)!="") { // some data
		mysql_select_db($database_aquiescedb, $aquiescedb);
		$select = "SELECT username FROM users WHERE username !='' AND username LIKE ".GetSQLValueString($username, "text");
		$console .= $select."\n";
		$result = mysql_query($select, $aquiescedb) or die(mysql_error());
		if(mysql_num_rows($result)==0) { // user doesn't exist
			$firstname = capitaliseName($firstname);
			$surname = capitaliseName($surname);
			// security - reduce usertypeID to equal or less than current user's
			$_SESSION['MM_UserGroup'] = isset($_SESSION['MM_UserGroup']) ? $_SESSION['MM_UserGroup'] : 1;
			$usertypeID = ($usertypeID>$_SESSION['MM_UserGroup']) ? $_SESSION['MM_UserGroup'] : $usertypeID;
			
			$insert = "INSERT INTO users (usertypeID, salutation, firstname, middlename, surname, email, gender, dob, agerangeID, regionID, ethnicityID, disabilityID, mobile, jobtitle, discovered, emailoptin, partneremailoptin, facebookURL,twitterID,websiteURL,telephone,identityverified,termsagree, termsagreedate,discoveredother, dateadded, addedbyID) VALUES (".GetSQLValueString($usertypeID, "int").",".GetSQLValueString($salutation, "text").",".
			GetSQLValueString($firstname, "text").",".
			GetSQLValueString($middlename, "text").",".
			GetSQLValueString($surname, "text").",".
			GetSQLValueString($email, "text").",".
			GetSQLValueString($gender, "int").",".
			GetSQLValueString($dob, "date").",".
			GetSQLValueString($agerangeID, "int").",".
			GetSQLValueString($regionID, "int").",".
			GetSQLValueString($ethnicityID, "int").",".			GetSQLValueString($disabilityID, "int").",".
			GetSQLValueString($mobile, "text").",".
			GetSQLValueString($jobtitle, "text").",".
			GetSQLValueString($discovered, "int").",".
			GetSQLValueString($emailoptin, "int").",".
			GetSQLValueString($partneremailoptin, "int").",".
			GetSQLValueString($facebookURL, "text").",".
			GetSQLValueString($twitterID, "text").",".
			GetSQLValueString($websiteURL, "text").",".
			GetSQLValueString($telephone, "text")."," .			
			GetSQLValueString($identityverified, "int").",".
			GetSQLValueString($termsagree, "int").",NOW(),".
			GetSQLValueString($discoveredother, "text").",NOW(),".GetSQLValueString($createdbyID, "int").")";	
		  
			$result = mysql_query($insert, $aquiescedb) or die(mysql_error().": ".$insert );
			$userID = mysql_insert_id();
			$console .= $insert."\n";
			
			addUsertoGroup($userID, $groupID, $createdbyID);
			addUserToDirectory($userID, $directoryID, $createdbyID);
			addUserToLocation($userID, $locationID, $createdbyID);
			
			if($login===true) { // generate login details
				$console .= " ** GENERATE LOGIN DETAILS **\n";
				unset($_SESSION['newpassword']);
				$logincredentials = explode("|",setUsernamePassword($userID,$username,$password));
				$_SESSION['newpassword'] = $logincredentials[1];	
			}
			if($emailoptin==1) {	// mail chimp	
				$select = "SELECT mailchimpapi, mailchimplistid FROM mailprefs WHERE ID = ".intval($regionID);
				$result = mysql_query($select, $aquiescedb) or die(mysql_error());
				$mailchimp = mysql_fetch_assoc($result);
				if(isset($mailchimp['mailchimpapi']) && isset($mailchimp['mailchimplistid'])) {
					$result = sendtoMailChimp($mailchimp['mailchimpapi'], $mailchimp['mailchimplistid'], $email, $firstname, $surname);
				}
			}
			return $userID;
		} // new user
	} // is data
	return false;
}
}


if(!function_exists("addUserToDirectory")) {	
function addUserToDirectory($userID, $directoryID, $createdbyID=0, $default=false, $log=false, $relationshiptype = 1) {
global $database_aquiescedb, $aquiescedb;
	mysql_select_db($database_aquiescedb, $aquiescedb);
// add to directory if required
	if(intval($userID)>0 && intval($directoryID)>0) {
		// check if directory already has user
		$select = "SELECT userID FROM directory WHERE ID = ".intval($directoryID);
		$result = mysql_query($select, $aquiescedb) or die(mysql_error());
		$row = mysql_fetch_assoc($result);
		$selectexists = "SELECT ID FROM directoryuser WHERE userID = ".$userID." AND directoryID = ".intval($directoryID);
		$resultexists = mysql_query($selectexists, $aquiescedb) or die(mysql_error());
		if(mysql_num_rows($resultexists) <1) { // doesn't exist already
			$insert = "INSERT INTO directoryuser (userID, directoryID, relationshiptype, createdbyID, createddatetime) VALUES (".$userID.",".intval($directoryID).",".intval($relationshiptype).",".GetSQLValueString($createdbyID, "int").",NOW())";
			$result = mysql_query($insert, $aquiescedb) or die(mysql_error());
			if($log && function_exists("writeLog")) { writeLog($insert); }
			if(!isset($row['userID']) || $default === true) { // no default user
				$update = "UPDATE directory SET userID = ".$userID." WHERE ID = ".intval($directoryID);
				$result = mysql_query($update, $aquiescedb) or die(mysql_error());
				if($log && function_exists("writeLog")) { writeLog($update); }
			} // end add default user
		} // end add relationship	
	} // end add to directory /	
}
}


if(!function_exists("removeUserFromDirectory")) {	
function removeUserFromDirectory($userID, $directoryID) {
global $database_aquiescedb, $aquiescedb;
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$delete = "DELETE FROM  directoryuser WHERE userID = ".intval($userID)." AND directoryID = ".intval($directoryID);
	$result = mysql_query($delete, $aquiescedb) or die(mysql_error());
	$count = mysql_affected_rows();
	
	$update = "UPDATE directory SET userID = NULL WHERE userID = ".intval($userID)." AND ID = ".intval($directoryID);
	$result = mysql_query($update, $aquiescedb) or die(mysql_error());
	return $count;
}
}



if(!function_exists("addUserToLocation")) {
	function addUserToLocation($userID, $locationID, $createdbyID=0, $default=false, $log=false, $relationshipID = "") {
global $database_aquiescedb, $aquiescedb;
	mysql_select_db($database_aquiescedb, $aquiescedb);
// add to location if required
	if(intval($userID)>0 && intval($locationID)>0) {
		// check if location already has user
		$select = "SELECT userID FROM location WHERE ID = ".intval($locationID);
		$result = mysql_query($select, $aquiescedb) or die(mysql_error());
		$row = mysql_fetch_assoc($result);
		$selectexists = "SELECT ID FROM locationuser WHERE userID = ".$userID." AND locationID = ".intval($locationID);
		$resultexists = mysql_query($selectexists, $aquiescedb) or die(mysql_error());
		if(mysql_num_rows($resultexists) <1) { // doesn't exist already
			$insert = "INSERT INTO locationuser (userID, locationID, relationshipID,  createdbyID, createddatetime) VALUES (".$userID.",".intval($locationID).",".GetSQLValueString($relationshipID, "int").",".GetSQLValueString($createdbyID, "int").",NOW())";
			$result = mysql_query($insert, $aquiescedb) or die(mysql_error());
			if($log && function_exists("writeLog")) { writeLog($insert); }
			if(!isset($row['userID']) || $default === true) { // no default user
				$update = "UPDATE location SET userID = ".$userID." WHERE ID = ".intval($locationID);
				$result = mysql_query($update, $aquiescedb) or die(mysql_error());
				if($log && function_exists("writeLog")) { writeLog($update); }
			} // end add default user
		} // end add relationship
		else { // exists, just update relationship
			$rowexists = mysql_fetch_assoc($resultexists);
			$update = "UPDATE locationuser SET relationshipID = ".GetSQLValueString($relationshipID, "int").", modifiedbyID= ".GetSQLValueString($createdbyID, "int").", modifieddatetime = NOW() WHERE ID = ".$rowexists['ID'];
			mysql_query($update, $aquiescedb) or die(mysql_error());
		}
		// does user have default address?
		$select = "SELECT defaultaddressID FROM users WHERE ID = ".intval($userID);
		$result = mysql_query($select, $aquiescedb) or die(mysql_error());
		$row = mysql_fetch_assoc($result);
		if($default === true || !isset($row['defaultaddressID'])) {
			$update ="UPDATE users SET defaultaddressID= ".intval($locationID)." WHERE ID = ".intval($userID);
			$result = mysql_query($update, $aquiescedb) or die(mysql_error());	
			if($log && function_exists("writeLog")) { writeLog($update); }
		}
	} // end add to location /	
}
}



if(!function_exists("regradeUser")) {
function regradeUser($userID, $usertypeID, $createdbyID=0,$allowdowngrade = false) {
	global $database_aquiescedb, $aquiescedb;
	if(intval($userID)>0) {
		// correct data
		mysql_select_db($database_aquiescedb, $aquiescedb);
		$_SESSION['MM_UserGroup'] = isset($_SESSION['MM_UserGroup']) ? $_SESSION['MM_UserGroup'] : -1;
		if($usertypeID <7 || $usertypeID<=$_SESSION['MM_UserGroup'] || ($_SESSION['MM_UserGroup']==9 && $usertypeID<=9)  || $_SESSION['MM_UserGroup']==10) { 
		// authorised to upgrade - <7 can be done by system - e.g. payments otherwise need to be logged in
		// check to see if can be upgraded from non-user
			$select = "SELECT usertypeID, username, email FROM users WHERE ID = ".$userID. " LIMIT 1";
			$result = mysql_query($select, $aquiescedb) or die(mysql_error());
			$row = mysql_fetch_assoc($result);
			if($allowdowngrade === true || $usertypeID > $row['usertypeID']) {  // downgrade possible or is upgrade
				if($row['usertypeID']!= -1 || $_SESSION['MM_UserGroup']>8) { 
				// authorised to upgrade banned user
					if($row['usertypeID']!= -1 || emailTaken($row['email'])==0) {
						// is unique user if non-user before					
						$update = "UPDATE users SET 
						usertypeID = ".GetSQLValueString($usertypeID, "int").",
						modifiedbyID= ".intval($createdbyID)." 
						WHERE users.ID = ".GetSQLValueString($userID, "int")." 
						AND ".GetSQLValueString($usertypeID, "int")." <= ".
						GetSQLValueString($_SESSION['MM_UserGroup'], "int");
						$result1 = mysql_query($update, $aquiescedb) or die(mysql_error());
						if($_SESSION['MM_Username']==$row['username']) {
							$_SESSION['MM_UserGroup']=$usertypeID;
						}
						return true;
					}
				}
			}
		}
	}
	return false;
}
}

if(!function_exists("emailTaken")) {
	function emailTaken($email,$userID=0) {
	// $userID is not required for new users, but excludes user in the check if for already existing user
	// Return 0 for unique user or userID otherwise
	$where = "";
	if(defined("CAN_SHARE_EMAIL")) {
		// in exceptional circumstances set variable CAN_SHARE_EMAIL but this will allow users without username to share email
		$where .= " AND username IS NOT NULL ";
	}
	global $database_aquiescedb, $aquiescedb, $regionID;
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$select = "SELECT multisitesignup FROM preferences LIMIT 1";
	$result = mysql_query($select, $aquiescedb) or die(mysql_error());
	$row = mysql_fetch_assoc($result);
	 $where .= (isset($regionID) && intval($regionID) >0) ? " AND (".$row['multisitesignup']." = 1 OR regionID = ".intval($regionID)." OR regionID = 0 OR regionID IS NULL)" : ""; 
	 // same email can co-exist in multiple distinct sites or non user (but not banned users)
  $select = "SELECT ID, username FROM users WHERE ID != ".intval($userID)." AND  usertypeID != -1 AND email= ".GetSQLValueString($email, "text").$where;
	$result=mysql_query($select, $aquiescedb) or die(mysql_error());
	$row=mysql_fetch_assoc($result);
	return (mysql_num_rows($result)>0) ? $row['ID'] : 0;	
}
}




if(!function_exists("userRelationship")) {
	function userRelationship($userID, $relatedtouserID, $relationshiptypeID = 0, $createdbyID=0) {
	if(intval($userID)>0 && intval($relatedtouserID)>0 && $userID!=$relatedtouserID) { //data OK
	
		global $database_aquiescedb, $aquiescedb;
		mysql_select_db($database_aquiescedb, $aquiescedb);
		$select = "SELECT ID FROM userrelationship WHERE 
		(relatedtouserID = ".intval($relatedtouserID)." AND userID = ".intval($userID)." AND relationshiptypeID=".intval($relationshiptypeID).")";
		$result = mysql_query($select, $aquiescedb) or die(mysql_error());
		$row = mysql_fetch_assoc($result);
		if (mysql_num_rows($result)==0) { // new relationship
  			$insert = "INSERT INTO userrelationship (userID, relatedtouserID, createdbyID, createddatetime, relationshiptypeID) VALUES (".intval($userID).",".intval($relatedtouserID).",".intval($createdbyID).", NOW(),".intval($relationshiptypeID).")";
			mysql_query($insert, $aquiescedb) or die(mysql_error());
			return mysql_insert_id();
		} // end new relationship
	} // data OK
	return false;
}
}

if(!function_exists("removeUserRelationship")) {
	function removeUserRelationship($userID, $relatedtouserID) {		
		global $database_aquiescedb, $aquiescedb;
		mysql_select_db($database_aquiescedb, $aquiescedb);
		$delete = "DELETE FROM userrelationship WHERE userID = ".intval($userID)." AND relatedtouserID = ".intval($relatedtouserID);
		mysql_query($delete, $aquiescedb) or die(mysql_error());

	}
}

if(!function_exists("capitaliseName")) {
	function capitaliseName($name) {
	 $names = explode(" ",trim($name));
		  foreach($names as $key=>$value) {
			  $names2 = explode("-",$names[$key]);
			  foreach($names2 as $key2 => $value2) {
				  $names2[$key2] = ucfirst(strtolower($names2[$key2]));
				  $names2[$key2] = strpos($names2[$key2],"Mc")===0 ? "Mc".ucfirst(substr($names2[$key2],2)) : $names2[$key2];
		  $names2[$key2] = strpos($names2[$key2],"Mac")===0 ? "Mac".ucfirst(substr($names2[$key2],3)) : $names2[$key2];
		  $names2[$key2] = strpos($names2[$key2],"O'")===0 ? "O'".ucfirst(substr($names2[$key2],2)) : $names2[$key2];
			  }
			  $names[$key] = implode("-",$names2);			  
		  }
		  $name = implode(" ",$names);
	return $name;
}
}


if(!function_exists("userinGroup")) {
	function userinGroup($groupID=-1, $userID=0) {	
	// omit $userID if using user who is logged in
	$username = isset($_SESSION['MM_Username']) ? $_SESSION['MM_Username'] : "";
	if(intval($groupID)>0) {
		if($userID>0 || ($userID==0 && $username !="")) {
			global $database_aquiescedb, $aquiescedb;
			mysql_select_db($database_aquiescedb, $aquiescedb);
			$select = "SELECT usergroupmember.ID FROM usergroupmember LEFT JOIN users ON (usergroupmember.userID = users.ID) WHERE usergroupmember.statusID =1 AND (usergroupmember.expirydatetime IS NULL OR usergroupmember.expirydatetime >= NOW()) AND usergroupmember.groupID = ".GetSQLValueString($groupID, "int")." AND (users.ID = ".GetSQLValueString($userID, "int")." OR (".GetSQLValueString($userID, "int")." = 0 AND users.username = ".GetSQLValueString($username, "text")."))";
			$result = mysql_query($select, $aquiescedb) or die(mysql_error());		
		 	return (mysql_num_rows($result)>0) ? true : false;
		} else {
			return false;
		}
	} else {
		return (intval($groupID)==0) ? true : false; // group "everyone" or none
	}	
	return false; // no group specified
}
}


if(!function_exists("thisUser")) {
function thisUser() {
	global $database_aquiescedb, $aquiescedb;
		mysql_select_db($database_aquiescedb, $aquiescedb);
		$select = "SELECT ID FROM users WHERE username = ".GetSQLValueString($_SESSION["MM_Username"], "text");
																					   $result = mysql_query($select, $aquiescedb) or die(mysql_error());																					   $row = mysql_fetch_assoc( $result);																					   return $row['ID'];
}
}


if(!function_exists("thisUserHasAccess")) {
function thisUserHasAccess($accesslevel, $groupID=0, $userID = 0) {
	/* DEPRACATED FOR MORE FUNCTIONAL BELOW */
	$userrank = isset($_SESSION['MM_UserGroup']) ? intval($_SESSION['MM_UserGroup']) : 0;
	if(($userrank >= $accesslevel && 
	   userinGroup($groupID))
	   || ($accesslevel == 99 && thisUser()==$userID)
	   || ($userrank == 10)
	   ) { 
// not authorised [group policy not applied to adminisrators]
		return true;
	} else {
		return false;
	}
}
}


if(!function_exists("passwordSecurity")) {
function passwordSecurity($password) {
	global $userPreferences;
	$msg = "";
	if(isset($userPreferences['passwordmulticase'])) {
		if($userPreferences['passwordmulticase']==1 && strtolower($password)==$password) {
			$msg .= " - Your password must contain both upper and lower case characters.\n";
		}
		if($userPreferences['passwordnumber']==1 && preg_replace("/[^0-9]/", '', $password)==$password) {
			$msg .= " - Your password must contain at least one number.\n";
		}
		if($userPreferences['passwordspecialchar']==1 && preg_replace("/[^A-Za-z0-9 ]/", '', $password)==$password) {
			$msg .= " - Your password must contain at least  one special character, e.g. &pound; !, @.\n";
		}
	}
	return $msg;
}
}


if(!function_exists("buildGroupSet")) {
function buildGroupSet($groupsetID=0, $createdbyID = 0) {
	global $database_aquiescedb, $aquiescedb, $regionID;
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$regionID = (isset($regionID) && $regionID>0) ? $regionID : 1;
	if($groupsetID>0) { // group set specified
		$select = "SELECT * FROM usergroupset WHERE ID = ".intval($groupsetID)." LIMIT 1";
		$result = mysql_query($select, $aquiescedb) or die(mysql_error().": ".$select);
		if(mysql_num_rows($result)>0) {	 // is a group set
			$groupset = mysql_fetch_assoc($result);
			
			$select = "SELECT ID FROM usergroup WHERE groupsetID = ".$groupset['ID'];
			$result = mysql_query($select, $aquiescedb) or die(mysql_error().": ".$select);		
			if(mysql_num_rows($result)>0) {
				// group exists - delete all entiries
				$group = mysql_fetch_assoc($result);
				$groupID = $group['ID'];
				$delete = "DELETE FROM usergroupmember WHERE groupID = ".$groupID;
				mysql_query($delete, $aquiescedb) or die(mysql_error().": ".$delete);		
			} else {
				// create group
				$insert = "INSERT INTO usergroup (groupname, groupsetID, regionID, createdbyID, createddatetime) VALUES ('SET: ".$groupset['groupsetname']."',".intval($groupsetID).",".intval($regionID).",".intval($createdbyID).",NOW())"; 
				mysql_query($insert, $aquiescedb) or die(mysql_error().": ".$insert);
				$groupID = mysql_insert_id();			
			}
			$select = "SELECT * FROM usergroupsetgroup WHERE groupsetID = ".$groupset['ID']. " ORDER BY relationship DESC";
			$result = mysql_query($select, $aquiescedb) or die(mysql_error().": ".$select);
			
			if(mysql_num_rows($result)>0) {
				$include = "";
				$exclude = "";
				$i = 1;
				while($groupsetgroup = mysql_fetch_assoc($result)) {
					if($groupsetgroup['relationship'] == 1) {
						$include .= ($include == "") ? "\n" : "\nOR\n ";
						$include .= ($groupsetgroup['groupID'] >0) ? "(users.ID = usergroupmember.userID AND usergroupmember.groupID = ".$groupsetgroup['groupID'].") " : "users.usertypeID >=0 ";
		  
					} else if($groupsetgroup['relationship'] == -1) {
						$exclude .= "\nAND NOT EXISTS (SELECT users".$i.".ID FROM users AS users".$i.", usergroupmember AS usergroupmember".$i." WHERE users.ID = users".$i.".ID AND users".$i.".ID = usergroupmember".$i.".userID AND usergroupmember".$i.".groupID = ".$groupsetgroup['groupID'].") ";
						$i++;
					}			
				}
				if($include != "") { // is include portion so add members
					$userselect = "SELECT DISTINCT users.ID
					FROM users, usergroupmember
					WHERE  (".$include.") ".$exclude;
					
					$allusers = mysql_query($userselect, $aquiescedb) or die(mysql_error()." ". $userselect);
					if(mysql_num_rows($allusers)>0) {
						while($user = mysql_fetch_assoc($allusers)) {
							addUsertoGroup($user['ID'], $groupID,$createdbyID);						
						}
					}
				} 			
			}
		}
	}
}
}



if(!function_exists("sendtoMailChimp")) {
function sendtoMailChimp($apiID, $listID, $email="", $firstname="", $surname=""){

    require_once(SITE_ROOT.'mail/includes/MCAPI.class.php');  // same directory as store-address.php

    // grab an API Key from http://admin.mailchimp.com/account/api/
    $api = new MCAPI($apiID);

    $merge_vars = Array( 
        'EMAIL' => $email,
        'FNAME' => $firstname, 
        'LNAME' => $surname
    );

    // grab your List's Unique Id by going to http://admin.mailchimp.com/lists/
    // Click the "settings" link for the list - the Unique Id is at the bottom of that page. 
    $list_id = $listID;

    if($api->listSubscribe($list_id, $email, $merge_vars , "HTML") === true) {
        // It worked!   
        return 'Success!&nbsp; Check your inbox or spam folder for a message containing a confirmation link.';
    }else{
        // An error ocurred, return error message   
        return '<b>Error:</b>&nbsp; ' . $api->errorMessage;
    }
}
}

if(!function_exists("resetUserTermsAgree")) {
function resetUserTermsAgree() {
	  global $database_aquiescedb, $aquiescedb, $regionID;
	  $regionID = (isset($regionID) && $regionID !="") ? intval($regionID ) : 1;
	$update = "UPDATE users SET termsagree= 0 WHERE regionID = 0 OR regionID IS NULL OR regionID = ".$regionID;

  mysql_select_db($database_aquiescedb, $aquiescedb);
  mysql_query($update, $aquiescedb) or die(mysql_error());
 }
}
 
 
if(!function_exists("findSimilarUsers")) {
 function findSimilarUsers($firstname="", $middlename="", $surname="", $email="", $dob="") {
	  global $database_aquiescedb, $aquiescedb, $regionID;
	 mysql_select_db($database_aquiescedb, $aquiescedb);
	$select = "SELECT users.*, usertype.name AS usertype FROM users LEFT JOIN usertype ON (users.usertypeID = usertype.ID)  WHERE usertypeID >= -1 AND (users.regionID = 0 OR users.regionID IS NULL OR users.regionID = ".intval($regionID).") AND  users.firstname LIKE ".GetSQLValueString("%" . $firstname . "%", "text")." AND users.surname LIKE ".GetSQLValueString("%" . $surname . "%", "text")."";
	
	/*$fullname = "<".trim($firstname)."* <".trim($middlename)."* ".trim($surname)."*";	
	$select = "SELECT users.*, usertype.name AS usertype FROM users LEFT JOIN usertype ON (users.usertypeID = usertype.ID) WHERE (users.regionID = 0 OR users.regionID = ".intval($regionID).") 
	AND usertypeID >= -1 
	AND  MATCH(firstname, middlename, surname) AGAINST (".GetSQLValueString( $fullname , "text")." IN BOOLEAN MODE) ORDER BY surname, firstname";*/	
		$result =  mysql_query($select, $aquiescedb) or die(mysql_error()." ". $select);
		return $result;
 }
}


if(!function_exists("activation")) {
function activation($key) {global $multidomain;if(!isset($multidomain) && !preg_match("/(preferences|login)/", $_SERVER["PHP_SELF"])  && substr(md5(preg_replace("/(www.|http:\/\/|https:\/\/)/i","",$_SERVER["HTTP_HOST"].".php")),3,12) !=$key) {header("location: http://www.fullbhuna.com/activate/index.php?host=".urlencode($_SERVER["HTTP_HOST"]));exit;}}
}

if(!function_exists("checkPassword")) {
function checkPassword($username="", $password="", $preencrypted = false, $regionID=0) {
	// light check - no region , crypt type etc - hope to merge with login.inc
	global $database_aquiescedb, $aquiescedb, $userPreferences, $geoIP;
	mysql_select_db($database_aquiescedb, $aquiescedb);
	if($username!="" && $password!="") {
		$username = trim($username);
		$password = trim($password);
		$select= "SELECT crypttype, password_salt, failedlogin, plainpassword FROM users WHERE (username = ".GetSQLValueString($username, "text")." OR email = ".GetSQLValueString($username, "text").")";
			$result = mysql_query($select, $aquiescedb);
			$row = mysql_fetch_assoc($result);			
			$crypttype = isset($row["crypttype"]) ? $row["crypttype"] : 1;
			
		if(!$preencrypted) {
			
			$enc_password=encryptPassword($password,$crypttype, $row["password_salt"]);
			
		} else {
			$enc_password=$password;
		}
		
		
		$regionSQL = $regionID>0 ? " AND (".GetSQLValueString($userPreferences["multisitesignup"],"int")." = 1 OR ".GetSQLValueString($regionID, "text")." = ".GetSQLValueString(0, "text")." OR regionID = ".GetSQLValueString($regionID, "text")." OR regionID = 0 OR regionID IS NULL)" : ""; // add region 	
		$loginattempts = (intval($userPreferences["loginattempts"])>0) ? " AND failedlogin < ".intval($userPreferences["loginattempts"]) : "";	
		$select="SELECT users.ID, username, password, email,usertypeID, failedlogin, lastlogin, password_salt, changepassword,updateprofile,termsagree, userip.ipv4 FROM users LEFT JOIN userip ON (users.ID = userip.userID) WHERE (username=".GetSQLValueString($username, "text")." OR email=".GetSQLValueString($username, "text").")  AND (password=".GetSQLValueString($enc_password, "text")." OR plainpassword = ".GetSQLValueString($password, "text").")".$loginattempts." AND usertypeID > 0 ".$regionSQL." LIMIT 1"; 
		// ipv4 just to check if one has been logged yet
			
  		$result = mysql_query($select, $aquiescedb) or die(mysql_error());		
  		if(mysql_num_rows($result)>0) { // user found
			$user = mysql_fetch_assoc($result);
	//die($user['password'].$user['plainpassword'].":".$enc_password);
			if(intval($user["ID"])>0) { // user exists
				if($user["usertypeID"]>=9 && isset($user["ipv4"])) { // main admin only - for now, plus NOT first IP logged 
					// security IP check
					$select = "SELECT ID FROM userip WHERE userID = ".intval($user["ID"])." AND ipv4 = ".GetSQLValueString(getClientIP(), "text");
					$ipresult = mysql_query($select, $aquiescedb) or die(mysql_error());
					if(mysql_num_rows($ipresult)==0) { // not found
						$insert = "INSERT INTO userip (userID, ipv4, createddatetime) VALUES  (".intval($user["ID"]).",".GetSQLValueString(getClientIP(), "text").",NOW())";
						mysql_query($insert, $aquiescedb) or die(mysql_error());
					
						$to = $user['email'];
						$subject = "Your account has been logged into";
						$message = "Hopefully it was you!\n\n";
						$message .="If it wasn’t, your security is very important to us so here’s what to do:\n\n";
						$message .="1. Change your password. Don’t delay. If you’re using the same password in other places, you’re going to want to change them all.\n";
						$message .="2. Contact your web developers and let them know.\n\nIf it was you, no need to worry. For security all web site administrators get these emails whenever there’s new login or password activity on your account, so that’s why we’re telling you all this.\n\n";
						$message .="Details:\n\n";
						
						if(isset($geoIP)) {
							$record = geoip_record_by_addr($geoIP,getClientIP());
							if($record) {
								$message .=	 "Location: ".$record->country_name;
								$message .= isset($record->city) ? " - ".$record->city : " - unknown locale";
								$message .= "\n";
								
							}
		
						}
						$message .="Time: ".date('g:ia, l jS M Y')."\n";
						$message .="IP: ".getClientIP()."\n";
						
						$message .="User Agent: ".$_SERVER['HTTP_USER_AGENT']."\n";
						
						sendMail($to, $subject, $message);				
					}
				} // end wadmin only for now
				
				
				//reset number attempts back to zero and  last login date
				$update = "UPDATE users SET failedlogin = 0, lastlogin = NOW() WHERE ID = ".intval($user["ID"]);
				mysql_query($update, $aquiescedb) or die(mysql_error());
				
				if($user["password_salt"]=="") {
					
					// update to salt encryption if required
					$md5salt = md5(generateSalt());
					$enc_password=encryptPassword($password,$crypttype, $md5salt);
					$update = "UPDATE users SET  password = '".$enc_password."', password_salt='".$md5salt."' WHERE username = ".GetSQLValueString($username, "text")." LIMIT 1";
					mysql_query($update, $aquiescedb) or die(mysql_error());
					
					$cookenameprepend  = (getProtocol()=="https") ? "__Secure-" : "";
					// update cookie password too
					if(isset($_COOKIE[$cookenameprepend."cookiepassword"])) $_COOKIE[$cookenameprepend."cookiepassword"] = $enc_password;
				} else if($userPreferences["defaultcrypttype"]==2 && $row["crypttype"] ==1) { //upgrade crypt
					$enc_password=encryptPassword($password,2,$row["password_salt"]);
					$update = "UPDATE users SET  crypttype = 2, password = '".$enc_password."' WHERE username = ".GetSQLValueString($username, "text")." LIMIT 1";
					mysql_query($update, $aquiescedb) or die(mysql_error());
					// update cookie password too
					if(isset($_COOKIE["cookiepassword"])) $_COOKIE["cookiepassword"] = $enc_password;
				}  
				
				//check activation ONCE LOGGED IN
				//activation($userPreferences["license_key"]);
				return $user;
			}// end user exisits
		} // end user found
	} // end credentials
	return false;
} // end func
}


if(!function_exists("stayLoggedIn")) {
function stayLoggedIn($username,$enc_password) {
	$path = "/";
	$domain = "";
	$httponly = true;
	$secure =  (getProtocol()=="https") ? true : false;
	$cookenameprepend  = ($secure) ? "__Secure-" : "";
	setcookie($cookenameprepend."cookieusername", $username,time()+60*60*24*30,$path,$domain, $secure, $httponly );
	setcookie($cookenameprepend."cookiepassword", $enc_password,time()+60*60*24*30,$path,$domain, $secure, $httponly ); // set encrypted password on computer
	setcookie("cookiestayloggedin", true,time()+60*60*24*30,$path,$domain, $secure, false ); // set encrypted password on computer
}
}

if(!function_exists("loginWithKey")) {
function loginWithKey($key="",$username="",$expires="") {
	global $database_aquiescedb, $aquiescedb, $userPreferences;
	mysql_select_db($database_aquiescedb, $aquiescedb);
	if(function_exists("session_regenerate_id")) session_regenerate_id(); // for security
	if($key!="") {
		if($key== md5(PRIVATE_KEY.$username.$expires) && $expires >= date('Y-m-d H:i:s')) {
			$select = "SELECT ID, username, email, lastlogin, usertypeID FROM users WHERE (username = ".GetSQLValueString($username,"text")." OR email = ".GetSQLValueString($username,"text").") LIMIT 1";
			$users= mysql_query($select, $aquiescedb) or die(mysql_error());
			$user = mysql_fetch_assoc($users);
			$username = isset($user["username"]) ? $user["username"] : $user["email"];
			if(trim($username)!="" && ($userPreferences['userscanlogin']==1 || $user['usertypeID']>=7)) {
				
				$_SESSION["lastlogin"] =  $user["lastlogin"]; 
				$_SESSION["MM_Username"] = $username;
				$_SESSION["MM_UserGroup"] = $user["usertypeID"];
				// set username if there isn't one already (i.e. only email set above)
				$update = "UPDATE users SET username = ".GetSQLValueString($username, "text").", failedlogin = 0, lastlogin = NOW() WHERE ID = ".GetSQLValueString($user["ID"], "int");
				mysql_query($update, $aquiescedb) or die(mysql_error());
				if(isset($_SESSION['fb_tracker'])) {
					$update = "UPDATE track_session SET username = ".GetSQLValueString($user["username"],"text")." WHERE ID = ".GetSQLValueString($_SESSION['fb_tracker'],"text");
					mysql_query($update, $aquiescedb) or die(mysql_error());
				}	
				return true;
			}	
		}		
	}
	return false;
}
}

if(!function_exists("GDPRremoveUser")) {
	function GDPRremoveUser($userID) {
		global $database_aquiescedb, $aquiescedb;
		mysql_select_db($database_aquiescedb, $aquiescedb);
		$select = "SELECT surname FROM users WHERE ID =".intval($userID)." LIMIT 1";
		$users = mysql_query($select, $aquiescedb) or die(mysql_error().": ".$select);
		if(mysql_num_rows($users)>0) {			
			$user = mysql_fetch_assoc($users);
			// delete user fields
			$update = "UPDATE users SET usertypeID = -1, firstname = 'Anonymous', middlename = '', surname = 'User', dob = NULL,deceased= NULL, imageURL= NULL, emailoptin = 0,  jobtitle = 'GDPR removed', aboutme = 'Removed from system for GDPR reasons', defaultaddressID = NULL,  email = NULL, username = NULL, password = NULL, plainpassword = NULL, mobile = NULL,  telephone = NULL, NI_number = NULL, twitterID = NULL, websiteURL = NULL, facebookURL = NULL WHERE ID = ".intval($userID);
			mysql_query($update, $aquiescedb) or die(mysql_error().": ".$update);
			// delete any location associations
			$select = "SELECT ID, locationuser.ID AS locationuserID FROM location LEFT JOIN locationuser ON (location.ID = locationuser.locationID) WHERE locationuser.userID = ".intval($userID);
			$locations = mysql_query($update, $aquiescedb) or die(mysql_error().": ".$select);
			if(mysql_num_rows($locations>0)) {
				while($location = mysql_fetch_assoc($result)) {
					if(strlen($user['surname'])>1 && strpos($location['locationname'],$user['surname'])!==false) { // remove name from location name if in there
						$update = "UPDATE location SET locationname = NULL WHERE ID = ".$location['ID'];
						mysql_query($update, $aquiescedb) or die(mysql_error().": ".$update);					
					} // user name in location
					$delete = "DELETE FROM locationuser WHERE ID = ".intval($location['locationuserID']);
					mysql_query($delete, $aquiescedb) or die(mysql_error().": ".$delete);
				} // end while
			} // locations linked to user
			// remove from all user groups
			$delete = "DELETE FROM usergroupmember WHERE userID = ".intval($userID);
			mysql_query($delete, $aquiescedb) or die(mysql_error().": ".$delete);
		} // user found
	} // end function
} // function exists


if(!function_exists("autoDeleteUserData")) {
	function autoDeleteUserData($delete=false) {
		$deletecount = 0;
		global $database_aquiescedb, $aquiescedb, $userPreferences;
		if(isset($userPreferences['deletedatausertypeID']) && $userPreferences['deletedataperiod']>0) {
			mysql_select_db($database_aquiescedb, $aquiescedb);
			$outofdate = date('Y-m-d H:i:s', time() - $userPreferences['deletedataperiod']);
			$select = "SELECT ID, firstname, surname FROM users WHERE usertypeID <= ".intval($userPreferences['deletedatausertypeID'])." AND donotautodelete = 0 AND ((lastlogin IS NULL AND dateadded < '".$outofdate."') OR (lastlogin IS NOT NULL AND lastlogin < '".$outofdate."')) LIMIT 20";
			
			$result = mysql_query($select, $aquiescedb) or die(mysql_error().": ".$select);
			$deletecount = mysql_num_rows($result);
			if($deletecount>0) {
				while($user=mysql_fetch_assoc($result)) {
					//GDPRremoveUser($user['ID']) ;
					if(isset($_SESSION['MM_UserGroup']) && $_SESSION['MM_UserGroup']==10) {
					echo $user['ID']." ".$user['firstname']." ".$user['surname']."<br>";
					}
				}
			}
			
	
		}
		return $deletecount;
}
}

if(!function_exists("getPasswordLink")) {
	function getPasswordLink($username, $returnURL="") {
		$login_link_expiry = defined("LOGIN_LINK_EXPIRY") ? LOGIN_LINK_EXPIRY : "24 HOURS";
		$expires = date('Y-m-d H:i:s', strtotime("NOW + ".$login_link_expiry));
		$key= md5(PRIVATE_KEY.$username.$expires);
				
				
		$url = getProtocol() ."://". $_SERVER['HTTP_HOST']."/members/profile/change_password.php?username=".urlencode($username)."&key=".urlencode($key)."&expires=".urlencode($expires);
		if(($returnURL!="")) {
			$url .= "&returnURL=".urlencode($returnURL);
		}
		return $url;
	}
}

if(!function_exists("logUserIn")) {
function logUserIn($user, $bycookie = false) {
	global $database_aquiescedb, $aquiescedb, $stayloggedin;
	mysql_select_db($database_aquiescedb, $aquiescedb);
	if(isset($_SESSION["MM_UserGroup"]) && $_SESSION["MM_UserGroup"]>$user["usertypeID"]) {
		unset($_SESSION["PrevUrl"]);
	}
	$_SESSION["lastlogin"] =  $user["lastlogin"]; 
	$_SESSION["MM_Username"] = $user["username"];
	$_SESSION["MM_UserGroup"] = $user["usertypeID"];	
	if(isset($_SESSION['fb_tracker'])) {
		$update = "UPDATE track_session SET username = ".GetSQLValueString($user["username"],"text")." WHERE ID = ".GetSQLValueString($_SESSION['fb_tracker'],"text");
		mysql_query($update, $aquiescedb) or die(mysql_error());
	}
	if(function_exists("session_regenerate_id")) session_regenerate_id(); // for security

	if(defined("DEBUG_EMAIL")) {
		$to = DEBUG_EMAIL;
		$subject= "LOG IN SUCCESS: ".@$site_name;
		$message = $user["username"]."/".substr($user["password"],0,1)."******\nIP: ".$_SERVER["REMOTE_ADDR"];
		mail($to,$subject,$message);
	}	

	if ($stayloggedin) {//set cookies for year
		stayLoggedIn($user["username"],$user["password"]);
	} 
	
	if(isset($_SESSION["PrevUrl"])) {
		$successURL = addslashes($_SESSION["PrevUrl"]);
	} else if($bycookie) {
		$successURL = addslashes($_SERVER["REQUEST_URI"]);
	} else {
		$successURL =  defined("DEFAULT_LOGIN_HOME") ? DEFAULT_LOGIN_HOME : "/members/";
	}

	 
	unset($_SESSION["PrevUrl"]); // get rid of this as sometimes can be a pain
	$pageTitle = ($bycookie) ? "Cookie " : "";
	$pageTitle .= "Login Success (".$user["username"]." - Rank ".$user["usertypeID"].")";

	trackPage($pageTitle, $user["username"]);

	$msg = passwordSecurity($user["password"]); // check if password secure enough
	if($msg !="" || $user["changepassword"] == 1) {
		$secure = (getProtocol()=="https") ? true : false;	
		setcookie("cookiepassword", "",time()-3600,"/", "", $secure, true);
		$msg = ($msg=="") ? "Now choose a new personal password of your own to continue...\n\n(If you have just been emailed a new temporary password, enter this in Your Current Password box.)" : "Site security requires you to update your password to a more secure one:\n\n".$msg;
		$url = "/members/profile/change_password.php?msg=".urlencode($msg)."&returnURL=".urlencode($successURL);
		header("location: ".$url); exit;
	}
	if ($user["updateprofile"] == 1) { 
		header("Location: /members/profile/update_profile.php?msg=".urlencode("Please review your profile and ensure it is up to date before continuing.")."&returnURL=".urlencode($successURL));exit;} 
	if ($user["termsagree"] != 1) { 
		header("Location: /members/updated_terms.php?returnURL=".urlencode(htmlentities($successURL, ENT_COMPAT, "UTF-8")));exit;
	}
	header("Location: ".htmlentities($successURL, ENT_COMPAT, "UTF-8"));exit;
}
}


if(!function_exists("sendPasswordResetEmail")) {
function sendPasswordResetEmail($userID, $returnURL="") {
	global $database_aquiescedb, $aquiescedb, $userPreferences;
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$select = "SELECT firstname, email, username, usertypeID FROM users WHERE ID = ".intval($userID);
	$result = mysql_query($select, $aquiescedb) or die(mysql_error());
		if(mysql_num_rows($result)>0) {
			$user = mysql_fetch_assoc($result );
			if($user['usertypeID']>=0) {
	$message = "Dear ".$user['firstname'].",\n\n";
				$message .= "A password reset was requested for your email address. You can reset your password by clicking on the link below:\n\n";
				
				
				$message .= getPasswordLink($user['username'], $returnURL);
				
				
				$message .= "\n\nRegards,\n".$userPreferences['orgname']." Team\n\n";			  
			  	$message .= "Note: This link is only valid for 24 hours. This is an automated email, please do not respond. If you are suspicious about this email, please contact us. The IP address of requestor is: ".getClientIP();	
				  
			  	$subject = "Your ".$userPreferences['orgname']." account";	
				$to = $user['email'];				
				sendMail($to,$subject,$message,$userPreferences['contactemail'],$userPreferences['orgname']); 
				return true;
		} 
		}
			return false;
		
}
}

?>