<?php require_once('../../../Connections/aquiescedb.php'); ?>
<?php require_once('../../../core/includes/adminAccess.inc.php'); ?>
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

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

if(isset($_GET['check'])) {
	//$mail_box = '{imap.hosts.co.uk:143/novalidate-cert}'; //imap example
	$mail_box = '{pop3.hosts.co.uk:110/pop3/novalidate-cert}'; //pop example	
	$mail_user = 'msol.org.uk'; //mail username
	$mail_pass = 'ae0lu5Itobi7'; //mail password
	
	
	# connect to mailbox
	$conn = imap_open ($mail_box, $mail_user, $mail_pass) or die("ERROR: ".imap_last_error());
	
	
	$total_msgs = imap_num_msg($conn);
	
	$num_msgs = ($total_msgs>1000) ? 1000 : $total_msgs;
	
	# start bounce class
	require_once('../includes/bouncehandler/bounce_driver.class.php'); 
	$bouncehandler = new Bouncehandler();
	
	# get the failures
	$email_addresses = array();
	$delete_addresses = array();
	$bounced = array();
	  for ($n=1;$n<=$num_msgs;$n++) {
	  $bounce = imap_fetchheader($conn, $n).imap_body($conn, $n); //entire message
	  $multiArray = $bouncehandler->get_the_facts($bounce);
	 
		if (!empty($multiArray[0]['action']) && !empty($multiArray[0]['status']) && !empty($multiArray[0]['recipient']) ) {
			
		  if ($multiArray[0]['action']=='failed') {
			  $bounced[$n]['email'] = $multiArray[0]['recipient'];
			  $bounced[$n]['status'] = $multiArray[0]['status'];
		  $email_addresses[$multiArray[0]['recipient']]++; //increment number of failures
		  $delete_addresses[$multiArray[0]['recipient']][] = $n; //add message to delete array
		  } //if delivery failed
		} //if passed parsing as bounce
		if(isset($_GET['delete']) && $_GET['delete']==2) {
			imap_delete($conn, $n);
		}
	  } //for loop
	
	# process the failures
	if(isset($_GET['remove'])) {
		$processed =0;
	  foreach ($bounced as $key => $bounce) { 
	 
			if((isset($_GET['error50']) && ($bounce['status'] < "5.2" ||  $bounce['status'] == "5.4.4")) || (isset($_GET['error52']) && $bounce['status'] >= "5.2" && $bounce['status'] < "5.7") || (isset($_GET['error57']) && $bounce['status'] >= "5.7") && trim($bounce['email']) !="") {
				$set = "SET ";
				$set .= isset($_GET['markbounced']) ? "emailbounced = 1, " : "";
				$set .= isset($_GET['optout']) ? "emailoptin = 0, " : "";
				$set .= isset($_GET['email']) ? "email = NULL, " : "";
				$set = trim($set, ", ");
				if(strlen($set)>5) {
					mysql_select_db($database_aquiescedb, $aquiescedb);
					$update = "UPDATE users ".$set." WHERE email = ".GetSQLValueString($bounce['email'], "text");
					mysql_query($update, $aquiescedb) or die(mysql_error());
					$processed ++;
					//echo $update."<br>";
				}
			}
			if(isset($_GET['delete']) && $_GET['delete']==1) {
		imap_delete($conn, $key);
			}
		
	  } //foreach
	
	//delete messages
	
	imap_expunge($conn);
	
	
	
	} // end remove
	$total_msgs = imap_num_msg($conn);
	imap_close($conn);// close

} // end check

// error codes
$error['5.0.0']="Address does not exist";
$error['5.1.0']="Other address status";
$error['5.1.1']="Bad destination mailbox address";
$error['5.1.2']="Bad destination system address";
$error['5.1.3']="Bad destination mailbox address syntax";
$error['5.1.4']="Destination mailbox address ambiguous";
$error['5.1.5']="Destination mailbox address valid";
$error['5.1.6']="Mailbox has moved";
$error['5.1.7']="Bad sender’s mailbox address syntax";
$error['5.1.8']="Bad sender’s system address";
$error['5.2.0']="Other or undefined mailbox status";
$error['5.2.1']="Mailbox disabled, not accepting messages";
$error['5.2.2']="Mailbox full";
$error['5.2.3']="Message length exceeds administrative limit.";
$error['5.2.4']="Mailing list expansion problem";
$error['5.3.0']="Other or undefined mail system status";
$error['5.3.1']="Mail system full";
$error['5.3.2']="System not accepting network messages";
$error['5.3.3']="System not capable of selected features";
$error['5.3.4']="Message too big for system";
$error['5.4.0']="Other or undefined network or routing status";
$error['5.4.1']="No answer from host";
$error['5.4.2']="Bad connection";
$error['5.4.3']="Routing server failure";
$error['5.4.4']="Unable to route";
$error['5.4.5']="Network congestion";
$error['5.4.6']="Routing loop detected";
$error['5.4.7']="Delivery time expired";
$error['5.5.0']="Other or undefined protocol status";
$error['5.5.1']="Invalid command";
$error['5.5.2']="Syntax error";
$error['5.5.3']="Too many recipients";
$error['5.5.4']="Invalid command arguments";
$error['5.5.5']="Wrong protocol version";
$error['5.6.0']="Other or undefined media error";
$error['5.6.1']="Media not supported";
$error['5.6.2']="Conversion required and prohibited";
$error['5.6.3']="Conversion required but not supported";
$error['5.6.4']="Conversion with loss performed";
$error['5.6.5']="Conversion failed";
$error['5.7.0']="Other or undefined security status";
$error['5.7.1']="Delivery not authorized, message refused";
$error['5.7.2']="Mailing list expansion prohibited";
$error['5.7.3']="Security conversion required but not possible";
$error['5.7.4']="Security features not supported";
$error['5.7.5']="Cryptographic failure";
$error['5.7.6']="Cryptographic algorithm not supported";
$error['5.7.7']="Message integrity failure";

?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Manage Bounces"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" --><style><!--
--></style>
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" --><div class="page mail">
      <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
  
     
     <li class="nav-item" ><a href="index.php" class="nav-link"><i class="glyphicon glyphicon-arrow-left"></i> Back to Group Email</a></li>
   </ul></div></nav>
      <h1><i class="glyphicon glyphicon-envelope"></i> Manage Bounces </h1>
      <p><?php echo isset($total_msgs) ? "Total messages on server: ".$total_msgs.". " : ""; ?>Maximum 1000 messages processed each check. Repeat if required.</p>
        <?php require_once('../../../core/includes/alert.inc.php'); ?>
     <?php if(isset($processed) || !isset($bounced)) { 
	 if($processed>0) {?>
     <p><?php echo $processed; ?> messages processed</p>
     <?php } ?>
      <form  method="get">
      <fieldset><legend>Bounce Mailbox</legend>
      <input type="hidden" name="check"  value="true">
       <button type="submit" class="btn btn-default btn-secondary" >Check for bounces...</button></fieldset>
      </form>
 
    
      <?php } else { if(isset($bounced) && is_array($bounced)) {
		  echo "<table>";
	  foreach($bounced as $bounce) {
		  echo "<tr><td>".$bounce['email']."</td><td>".$bounce['status'];
		  echo isset($error[$bounce['status']]) ? " ".$error[$bounce['status']] : "";
		  echo "</td></tr>";
	  }
		echo "</table>";  ?>
      <form  method="get">
      <fieldset><legend>Process bounces</legend>
       
        <p><strong>With email addresses that were:</strong>
          <label>
  <input name="error50" type="checkbox" value="1" checked>
  undeliverable</label>
(e.g. don't exist)         &nbsp;&nbsp;&nbsp;
          <label>
            <input name="error52" type="checkbox" value="1">
            were  unreachable (possibly temporary)</label>
          &nbsp;&nbsp;&nbsp;
          <label>
            <input name="error57" type="checkbox" value="1">
            spam blocked</label>
          <input type="hidden" name="check"  value="true">
          <input type="hidden" name="remove" id="remove">
           <p><strong>do the following:</strong>
             <label>
  <input name="markbounced" type="checkbox" value="1" checked>
  flag as bounced</label>
          &nbsp;&nbsp;&nbsp;
          
          
          <label>
    <input name="optout" type="checkbox" value="1" >
    set to opt out</label>
          &nbsp;&nbsp;&nbsp;
          
          <label>
    <input name="delete" type="checkbox" value="1" >
    remove email address from user</label>
         </p>
        </p>
         <p><button type="submit" class="btn btn-default btn-secondary" >Go</button>
           
            Delete messages after processing:
              <label>
                <input type="radio" name="delete" value="0" checked>
                None</label>
               &nbsp;&nbsp;&nbsp;
              <label>
                <input type="radio" name="delete" value="1">
                Processed</label>
             &nbsp;&nbsp;&nbsp;
              <label>
                <input type="radio" name="delete" value="2">
                All</label>
             
         </p>
      </fieldset>
      </form>
	<?php  }
	  }
	  ?></div>
      <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);
?>
