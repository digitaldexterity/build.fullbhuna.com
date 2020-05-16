<?php  
ini_set("max_execution_time",1200);
ini_set('memory_limit', '80M' ); 
require_once('../../../Connections/aquiescedb.php'); if(!isset($_SESSION['MM_UserGroup'])) die(); ?>
<?php require(SITE_ROOT."/mail/admin/includes/pop_functions.inc.php"); ?><?php require_once(SITE_ROOT.'/mail/includes/PlancakeEmailParser.php'); 
session_write_close();// unlock PHP session so other functions can work on mail page

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

$regionID = isset($regionID) ? $regionID : 1;

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsAccounts = "SELECT * FROM mailaccount WHERE regionID = ".intval($regionID)." AND statusID = 1 AND bounceaccount = 0";
$rsAccounts = mysql_query($query_rsAccounts, $aquiescedb) or die(mysql_error());

global $timeout, $error, $buffer, $count; 

$error            = ""; //    Error string. 
$timeout          = 480; //    Default timeout before giving up on a network operation. 
$count            =  -1;    //    Mailbox msg count 
$maxcount = 100; // max number to retrieve
$buffer           = 512; //    Socket buffer for socket fgets() calls, max per RFC 1939 the returned line a POP3  server can send is 512 bytes. 


while($row_rsAccounts = mysql_fetch_assoc($rsAccounts)) { 
	$reset = ($row_rsAccounts['deletemail']==1) ? false : true;
	$usessl = ($row_rsAccounts['usessl']==1) ? true : false;
	$status = getMail($row_rsAccounts['mailserver'], 
	$row_rsAccounts['port'], $usessl, $row_rsAccounts['username'], $row_rsAccounts['password'], $row_rsAccounts['ID'], $reset,$maxcount);
	
	echo "> ".$row_rsAccounts['accountname'].": ". $status."<br>";
}

echo " Updated: ".date("H:i");

function getMail($server, $port, $ssl = false, $login, $pass, $accountID=0, $reset = true, $maxcount=500) {	
	// reset true = do NOT delete messages off server afterwards	
	global $timeout, $error, $buffer, $database_aquiescedb, $aquiescedb, $count; 
	$msg = "";	
	$msg_list_array = array(); 	
	set_time_limit($timeout); 
	$fp = connect ($server, $port, $ssl); 
	if(is_resource($fp)) { // connected		
		if(login($login,$pass, $fp)) {  // authenticated		
			$count = last("count", $fp); 
			$msg .= $count . " messages	found. ";		
			if( (!$count) or ($count == -1) ) 
			{ 
				return "No messages"; 				
				exit; 
			} // end if 
		
			// ONLY USE THIS IF YOUR PHP VERSION SUPPORTS IT! 
			register_shutdown_function(quit()); 
		
			if ($count < 1) 
			{ 
				return "No messages"; 
			} else { // is messages
				$msg_list_array = uidl("", $fp); //echo"<pre>"; print_r($msg_list_array); echo"</pre>";die();
				set_time_limit($timeout); 					
				$start = $count - $maxcount;
			// loop thru the array to get each message 
				for ($i=$start; $i <= $count; $i++){ 
					set_time_limit($timeout); 						
					$messageID = $msg_list_array[$i];
					if(isset($messageID) && strlen($messageID)>5) {
					  // has message ID
						mysql_select_db($database_aquiescedb, $aquiescedb);
						$select = "SELECT message FROM correspondence WHERE messageID = ".GetSQLValueString($messageID, "text"). " LIMIT 1";
						$result = mysql_query($select, $aquiescedb) or die(mysql_error());
						if(mysql_num_rows($result)==0) { // get message
							$headerlines = top ($i, 0, $fp);
							if(!is_array($headerlines) || empty($headerlines)) 
							{ 
								$msg.= "Header ".$i." not returned by the server.<BR>\n"; 
						  
							} else {
								$header = implode("",$headerlines)."\r\n";	
							}
							$messagelines = get($i, $fp);
							if(!is_array($messagelines) || empty($messagelines)) 
							{ 
								$msg.= "Message ".$i." not returned by the server.<BR>\n";  
							} else {
								$message = implode("",$messagelines)."\r\n";
								$email =  $header.$message;
								addToDatabase($email, $messageID, $accountID);
							}	
						} // end get message
					} //end has message ID
					$msg .= ($error !="") ? $error."<br>" : ""; $error = "";
				}// end for loop 	
			}// end is messages  
			if($reset) { // do not delete
			  resets($fp); 
			} else { // delete
			  quit($fp); 
			}			
		} // end is authenticated		
	} // end connected
	return $msg.$error;	  
} // end func



function addToDatabase($email, $messageID, $accountID = 0, $createdbyID =0) {
	global $database_aquiescedb, $aquiescedb;
	mysql_select_db($database_aquiescedb, $aquiescedb);	
	$emailParser = new PlancakeEmailParser($email);		
	$from = explode("<",$emailParser->getHeader('From'),2);
	$date = trim(preg_replace('/\s*\([^)]*\)/', '', $emailParser->getHeader('Date'))); // clean comments
	$date = date('Y-m-d H:i:s', strtotime($date));
	$to = implode(",",$emailParser->getTo());
	$insert = "INSERT INTO correspondence (accountID, messageID, mailfolderID, sender, sendername, recipient, subject, message, createdbyID, sentdatetime, createddatetime) VALUES (".		GetSQLValueString($accountID, "int").",".
	GetSQLValueString($messageID, "text").",1,".
	GetSQLValueString(trim(@$from[1]," >"), "text").",".
	GetSQLValueString(trim(@$from[0]," <'\""), "text").",".
	GetSQLValueString($to, "text").",".
	GetSQLValueString($emailParser->getSubject(), "text").",".
	GetSQLValueString($emailParser->getPlainBody(), "text").",".
	GetSQLValueString($createdbyID, "int").",".
	GetSQLValueString($date, "date").",NOW())";
	mysql_query($insert, $aquiescedb) or die(mysql_error().": ".$insert);
	//echo "<pre>".$date.$emailParser->getHeader('To')."</pre>";
}

?>