<?php
/* time span will be EITHER months OR seconds

if months = 0 - full period is in seconds */
$regionID = isset($regionID) ? intval($regionID) : 1;

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

// functions
if (!function_exists("addReminder")) {
	function addReminder($reminderdate, $recipientID, $subject = "", $message="", $repeat = 0, $createdbyID = 0 ,$from="",$friendlyfrom="",$htmlmessage="", $htmlhead="", $eventID = "", $viaemail = 1, $viasms = 0, $smsmessage = "", $months=0, $seconds = 0, $cc= "", $merge=array(), $ignoreoptout = 0) {
		global $database_aquiescedb, $aquiescedb, $regionID; 
		$subject = (trim($subject)=="") ? "Reminder" : $subject;
		$message = (trim($message)=="") ? "Reminder" : $message;
		if(is_array($merge) && !empty($merge)) {
			foreach($merge as $key => $value) {		
				$subject = str_ireplace("{".$key."}",$value,$subject);
				$message = str_ireplace("{".$key."}",$value,$message);
				$htmlmessage = str_ireplace("{".$key."}",$value,$htmlmessage);
				$smsmessage = str_ireplace("{".$key."}",$value,$smsmessage);
			}
		}
		mysql_select_db($database_aquiescedb, $aquiescedb);
		$insert = "INSERT INTO reminder (firstsend, subject, message, reminderrepeat, recipientID, `from`, friendlyfrom, htmlmessage,smsmessage, htmlhead, regionID, eventID, viaemail, viasms, months, seconds, ignoreoptout, createdbyID, createddatetime) VALUES (".
		GetSQLValueString($reminderdate,"date").",".
		GetSQLValueString($subject,"text").",".
		GetSQLValueString($message,"text").",".
		GetSQLValueString($repeat,"int").",".
		intval($recipientID).",".
		GetSQLValueString($from,"text").",".
		GetSQLValueString($friendlyfrom,"text").",".
		GetSQLValueString($htmlmessage,"text").",".
		GetSQLValueString($smsmessage,"text").",".
		GetSQLValueString($htmlhead,"text").",".
		intval($regionID).",".
		GetSQLValueString($eventID,"int").",".
		intval($viaemail).",".
		intval($viasms).",".
		GetSQLValueString($months, "int").",".
		GetSQLValueString($seconds, "int").",".
		GetSQLValueString($ignoreoptout, "int").",".
		GetSQLValueString($createdbyID, "int").",
		NOW())";
		$result = mysql_query($insert, $aquiescedb) or die(mysql_error().": ".$insert);
		
	}
}


if (!function_exists("sendReminder")) {
function sendReminder($reminderID = 0) { // if 0 (default) auto send next due
	global $database_aquiescedb, $aquiescedb, $regionID; 
	mysql_select_db($database_aquiescedb, $aquiescedb);

// we have to treat months different from all other time spans which are calculated in seconds
// there is no way to truly contactinate the whole INTERVAL statement

	if($reminderID>0) { // set specific reminder now
		$where = "WHERE reminder.ID = ".intval($reminderID);
	} else { 
		$where = "WHERE reminder.regionID = ".$regionID." AND reminder.statusID = 1 AND 
		(
			(lastsent IS NOT NULL AND months = 0 AND DATE_ADD(lastsent, INTERVAL seconds SECOND) <= NOW()) 
				OR 
			(lastsent IS NOT NULL AND  months > 0 AND DATE_ADD(lastsent, INTERVAL months MONTH) <= NOW()) 
				OR 
			(lastsent IS NULL AND firstsend <= NOW())
		)"; 
		
		
		
		
	}
	
	
	

	$select = "SELECT reminder.*, users.email, users.firstname, users.surname, users.mobile, users.telephone, users.emailoptin, users.usertypeID, NOW() AS thenoo FROM reminder LEFT JOIN users ON (reminder.recipientID = users.ID)  ".$where." ORDER BY firstsend ASC LIMIT 5";
	
	
	$result = mysql_query($select, $aquiescedb) or die(mysql_error().$select);
$unsubscribe = true; $merge = true;
	if(mysql_num_rows($result)>0){
		
		
		while($row = mysql_fetch_assoc($result)) { // now fetch
		$update = "";
		if($row['emailoptin']==1  || $row['ignoreoptout']==1 ) { // not opt out
			if($row['viaemail']==1) {
				require_once(SITE_ROOT."mail/includes/sendmail.inc.php");
				sendMail($row['email'],$row['subject'],$row['message'],$row['from'],$row['friendlyfrom'],$row['htmlmessage'],"","",$row['cc'], "", $row['htmlhead'], 0 , $unsubscribe, $merge);		
				
				
				
				
							
			}
			if($row['viasms']==1) {
				if(is_readable(SITE_ROOT.'sms/includes/smsfunctions.inc.php')) {
					require_once(SITE_ROOT.'sms/includes/smsfunctions.inc.php');
					
					$number = (isset($row['mobile']) && trim($row['mobile'])!="") ? $row['mobile'] : $row['telephone'];
					$message = (isset($row['smsmessage']) && $row['smsmessage']!="") ? $row['smsmessage']: $row['message'];
					
					
					$message = mailMerge($message , "", "", $row);
	
					if(strlen($number)>5) {				
						$response = sendSMS($number, $message);
					}
				} // end is readable
			} // end is sms
			$update = "lastsent = NOW()";
		} // end not opt out
			
			if($row['reminderrepeat'] ==0) {
				$update .= ($update=="") ? "" : ",";
				$update .= "statusID = 0"; // turn off now if no repeat
			}
			
			$update =  "UPDATE reminder SET ".$update." WHERE ID = ".$row['ID'];
			mysql_query($update, $aquiescedb) or die(mysql_error());
		} // end row fetch
	} // is result
	
	
	mysql_free_result($result);	
}
}


?>
