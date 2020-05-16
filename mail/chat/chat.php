<?php require_once('../../Connections/aquiescedb.php');
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "";
$MM_donotCheckaccess = "true";

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
    if (($strUsers == "") && true) { 
      $isValid = true; 
    } 
  } 
  return $isValid; 
}

$MM_restrictGoTo = "../../login/index.php?notloggedin=true";
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
if (isset($_GET['MM_Username'])) {
  $colname_rsLoggedIn = $_GET['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

// as page loads, get chatID or start new chat
if(isset($_GET['chatID']) && intval($_GET['chatID'])>0) {
	$chatID = intval($_GET['chatID']);
} else {
	$insert = "INSERT INTO chat (createdbyID, createddatetime, statusID) VALUES (".$row_rsLoggedIn['ID'].",NOW(),1)";
	mysql_query($insert, $aquiescedb) or die(mysql_error());
	$chatID = mysql_insert_id();
} 

 
 ?>
<!doctype html>

<html class="" lang="en">
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<title><?php $pageTitle = "Conversation"; echo $pageTitle." | ".$site_name; ?></title>
<script src="/core/scripts/common.js"></script>
<script src="scripts/chat.js"></script>
<script>
addListener("load",init);

function init() {
 	//usersOnline();
	//setInterval("usersOnline()",5000);
	document.getElementById('chattext').focus();
	addUserToChat(document.getElementById('chatID').value, document.getElementById('userID').value, 1); // add logged in user to this conversation
	<?php if(isset($_GET['inviteuserID']) && intval($_GET['inviteuserID'])>0) { // user to add to conversation ?>
	addUserToChat(document.getElementById('chatID').value, <?php echo intval($_GET['inviteuserID']); ?>, 0); // add logged in user to this conversation
	<?php } ?>
	refreshChat(document.getElementById('chatID').value,'');
	setInterval("refreshChat(document.getElementById('chatID').value,'')",5000);
	
}
</script>
<style><!--
#chatpopup {
	padding:5px;
}
#chatwindow {
	height: 300px;
	border: 1px solid #999;
	overflow: scroll;
}
#chatbutton {
	margin:5px;
}
.chatitem {
	margin:5px;
}
--></style>
<link href="/local/css/styles.css" rel="stylesheet" type="text/css" />
</head>
<body id="chatpopup">


<noscript>Sorry, the chat system requires that you have Javascript switched on in your browser to function.</noscript>
  <div id="chatusers"></div>
      <textarea name="chattext" id="chattext" rows="5" style="width:100%" onkeyup="keyCheck(event);"></textarea>
      <input type="submit" name="chatbutton" id="chatbutton" value="Add" onclick="addResponse();" />
      <input name="userID" type="hidden" id="userID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
      <input name="chatID" type="hidden" id="chatID" value="<?php echo $chatID; ?>" />
    
    <div id="chatwindow"></div>
    <div id="loading"></div>
     

</body>
</html>
<?php
mysql_free_result($rsLoggedIn);
?>
