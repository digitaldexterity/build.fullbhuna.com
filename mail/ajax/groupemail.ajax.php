<?php chdir(dirname(__FILE__)); $is_cron = true; // in case by CRON
require_once('../../Connections/aquiescedb.php'); ?><?php require_once('../../core/includes/framework.inc.php'); ?>
<?php require_once('../includes/reminders.inc.php'); ?>
<?php require_once('../includes/sendmail.inc.php'); ?>
<?php require_once('../../members/includes/userfunctions.inc.php'); ?><?php 
// sends next emails in list unless specific test parameters sent: groupemailID and recipient
ignore_user_abort(true);


$sent = 0; $regionID = isset($regionID) && intval($regionID)>0 ? intval($regionID) : 1;

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

$loop = 0; $max_send = defined('GROUP_EMAIL_SEND_COUNT') ? GROUP_EMAIL_SEND_COUNT : 1; // how many?
$dailymax = defined("GROUP_EMAIL_DAILY_MAX") ? GROUP_EMAIL_DAILY_MAX : 400;

do { // send several at one time

	
	if(!isset($_GET['groupemailID']) && $row_rsMailPrefs['sendcountday'] < date('Y-m-d')) {
		$update = "UPDATE mailprefs SET sendcountday = CURDATE(), sendcount = 0 WHERE ID = ".$regionID;
		mysql_query($update, $aquiescedb) or die(mysql_error());
	} 
	if(isset($_GET['groupemailID']) || $row_rsMailPrefs['sendcount'] < $dailymax) {
		
		$query = "SELECT * FROM groupemail WHERE ";
		$query .= isset($_GET['groupemailID']) ? "ID = ".intval($_GET['groupemailID']) : "active = 1 AND startdatetime <= NOW() LIMIT 1";
		$rsGroupEmail = mysql_query($query, $aquiescedb) or die(mysql_error());
		$row_rsGroupEmail = mysql_fetch_assoc($rsGroupEmail);
		if (isset($row_rsGroupEmail['ID'])) { // we have an active email group			
			
			
			$query_rsUser = "SELECT groupemaillist.ID, users.email,  users.ID AS userID, users.firstname, users.surname, users.jobtitle, users.salutation, users.username, users.password, users.plainpassword, directory.name AS company FROM groupemaillist LEFT JOIN users ON (users.ID = groupemaillist.userID) LEFT JOIN directory ON (users.ID = directory.userID) WHERE ";
			$query_rsUser .= isset($_GET['groupemailID']) ? "users.email = ".GetSQLValueString($_REQUEST['recipient'],"text") : " groupemaillist.groupemailID = ".$row_rsGroupEmail['ID']." AND groupemaillist.sent = 0 ORDER BY groupemaillist.ID LIMIT 1";
			$rsUser = mysql_query($query_rsUser, $aquiescedb) or die(mysql_error());
			$row_rsUser = mysql_fetch_assoc($rsUser);

			if (isset($row_rsUser['userID']) || isset($_GET['groupemailID'])) { // we have a user so send message	
				$email = isset($_GET['groupemailID']) ? $_REQUEST['recipient'] : $row_rsUser['email'];
				$row_rsUser['firstname']  = strlen($row_rsUser['firstname'])>1 ?  $row_rsUser['firstname']:$row_rsGroupEmail['defaultfirstname']; 					
			
				if(!isset($_GET['groupemailID'])) {
					$groupemaillist = "UPDATE groupemaillist SET sent = 1 WHERE ID =".$row_rsUser['ID']."";
					mysql_query($groupemaillist, $aquiescedb) or die(mysql_error());
					$update = "UPDATE mailprefs SET sendcount = sendcount+1 WHERE ID =".$regionID;
					mysql_query($update, $aquiescedb) or die(mysql_error());
				}
				// send moved to after the updates to save latency and locks in mysql
				
				sendGroupEmail($email,$row_rsGroupEmail['subject'],$row_rsGroupEmail['message'],$row_rsGroupEmail['from'],$row_rsGroupEmail['fromname'],$row_rsGroupEmail['html'],$row_rsGroupEmail['head'],$row_rsGroupEmail['bodytag'],$params="",$row_rsGroupEmail['ID'],$row_rsGroupEmail['showunsubscribe'],$row_rsGroupEmail['viewonline'],$row_rsUser, $row_rsGroupEmail['trackclicks'], true);

			} else { // all done in current group , reset emails and add close group

				//set current group as completed
				$updategroupemails = "UPDATE groupemail SET enddatetime = NOW(), active = 0 WHERE ID = ".$row_rsGroupEmail['ID'].""; 
				mysql_query($updategroupemails, $aquiescedb) or die(mysql_error());
			}// end reset
			mysql_free_result($rsUser);
			
			
		} // end we have an active email group
	} // daily max

	$loop++;
} while(isset($row_rsGroupEmail['ID']) && $loop<$max_send && !isset($_GET['groupemailID']));

if(isset($rsGroupEmail) && (is_object($rsGroupEmail) || is_resource($rsGroupEmail))) mysql_free_result($rsGroupEmail);

sendReminder();


function sendGroupEmail($to,$subject,$message="",$from,$friendlyfrom="",$html="",$htmlhead="",$bodytag = "", $params="",$groupemailID=0,$showunsubscribe=1,$viewonline=1,$merge=array(),$trackclicks=0,$log=false) { 
 global $site_name, $row_rsMailPrefs;
 $template = array("templateHTML"=>$html,"templatehead"=>$htmlhead,"templatebodytag"=>$bodytag ,"showunsubscribe"=>$showunsubscribe,"viewonline" =>$viewonline, "trackclicks"=> $trackclicks);
	$protocol = getProtocol()."://";
	$params = ($params == "") ?  "-f ".$from : $params; 
	ini_set("sendmail_from", $from);
	$headersfrom = ($friendlyfrom !="") ? $friendlyfrom." <".$from.">" : $from;
	if(strpos($to,"@")!==false) {
		
		if ($html!="") { //send HTML
					
			$message = buildEmailHTML($template, $to, $groupemailID);
			
			$headers = "From: ".$headersfrom."\n" . 
						"MIME-Version: 1.0\n" . 
						"Content-type: text/html; charset=UTF-8"; 
		} else { //plain text
			$message = strip_tags($message);
			$message .= ($showunsubscribe==1) ? "\n\n\nIf you wish to unsubscribe from ".$site_name." emails please reply using UNSUBSCRIBE as the subject line." : "";
			$headers = "From: ".$headersfrom;
		} // end plain text
		
		if(!empty($merge)) {
			foreach($merge as $key => $value) {
			  if($key=="password") {
				  $username  = isset($merge['username']) ? $merge['username'] : $merge['email'];
				   $value = isset($merge['plainpassword']) ? $merge['plainpassword'] : "<a href=\"".getPasswordLink($username)."\">Click here to set your password.</a>";
			  }					   
			  $message = str_replace("{".$key."}",$value,$message);
			  $subject = str_replace("{".$key."}",$value,$subject);
		  }
		  $message = str_replace("{usertoken}",md5(PRIVATE_KEY.$merge['userID']),$message);	
		  $message = str_replace("{date}",date('jS F Y'),$message);
		}
		if(isset($row_rsMailPrefs['mailgunapi']) && strlen($row_rsMailPrefs['mailgunapi'])>10) {
			$message = $html =="" ? nl2br($message) : $message;
			
			sendMailgun($row_rsMailPrefs['mailgunapi'], $row_rsMailPrefs['mailgundomain'], $from,$friendlyfrom, $to, $subject, $message, $message);
		
		} else { // send via server		
			mail($to,$subject,$message,$headers,$params);
		}
		if($log) { writeLog("Group mail '".$subject."' sent to: ".$to); }
	}
	
}


?>