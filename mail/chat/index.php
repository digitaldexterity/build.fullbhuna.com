<?php require_once('../../Connections/aquiescedb.php'); ?><?php require_once('../../core/includes/framework.inc.php'); ?>
<?php if(isset($_POST['firstname']) && $_POST['firstname'] !="") {
	require_once('../../members/includes/userfunctions.inc.php'); 
	$userID = createNewUser($_POST['firstname']);	
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$select = "SELECT username FROM users WHERE ID = ".$userID;
	$result = mysql_query($select, $aquiescedb) or die(mysql_error());
	$row = mysql_fetch_assoc($result);
	$_SESSION['MM_Username'] = $row['username'];
	$_SESSION['MM_UserGroup'] = 0;	
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
$query_rsLoggedIn = sprintf("SELECT ID, firstname, surname, chatstatus FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPrefs = "SELECT contactemail FROM preferences";
$rsPrefs = mysql_query($query_rsPrefs, $aquiescedb) or die(mysql_error());
$row_rsPrefs = mysql_fetch_assoc($rsPrefs);
$totalRows_rsPrefs = mysql_num_rows($rsPrefs);


	// close old chats after 10 mins no activity
$select = "SELECT chat.ID, MAX(chatitem.createddatetime) AS lastpost FROM chat LEFT JOIN chatitem ON (chat.ID = chatitem.chatID) WHERE chat.statusID = 1 GROUP BY chat.ID HAVING lastpost < '".date('Y-m-d H:i:s',strtotime("10 MINUTES AGO"))."'";
$result = mysql_query($select, $aquiescedb) or die(mysql_error());
if(mysql_num_rows($result)>0) {
	while($row = mysql_fetch_assoc($result)) {
		$update = "UPDATE chat SET statusID = 0 WHERE ID = ".$row['ID'];
		mysql_query($update, $aquiescedb) or die(mysql_error());
	}
}
	if(isset($_GET['requestchat'])) {
		require_once('../includes/sendmail.inc.php'); 
		$to = $row_rsPrefs['contactemail'];
		$subject = "WEB SITE CHAT REQUESTED FROM ".strtoupper($row_rsLoggedIn['firstname']." ".$row_rsLoggedIn['surname']);
		$message = "This is an automated message.\n\n";
		$message .= "Chat requested by ".$row_rsLoggedIn['firstname']." ".$row_rsLoggedIn['surname']." on ".date('l jS F')." at ".date('g.ia').".\n\n";
		$message .= "Please log on to the chat system at:\n\n";
		$message .= getProtocol()."://";
		$message .= $_SERVER['HTTP_HOST']."/mail/chat/";
		sendMail($to,$subject,$message);
	}
 
 ?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Chat"; echo $pageTitle." | ".$site_name; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" --><script src="scripts/chat.js"></script>
<script>
addListener("load",init);

function init() {
	usersOnline();
	setInterval("usersOnline()",5000);
	updateMyConversations();
	setInterval("updateMyConversations()",5000);
}
</script>
<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" -->
    <h1>Live Chat</h1>
    <?php if(isset($row_rsLoggedIn['firstname'])) { // someone is logged in ?>
    <h2>Welcome <?php echo $row_rsLoggedIn['firstname']; ?>!</h2>
    <p>Your chat status: 
      <select name="chatstatus" id="chatstatus" onchange="updateStatus(<?php echo $row_rsLoggedIn['ID']; ?>, this.value)">
        <option value="0" <?php if (!(strcmp(0, $row_rsLoggedIn['chatstatus']))) {echo "selected=\"selected\"";} ?>>Appear offline</option>
        <option value="1" <?php if (!(strcmp(1, $row_rsLoggedIn['chatstatus']))) {echo "selected=\"selected\"";} ?>>Online</option>
        <option value="2" <?php if (!(strcmp(2, $row_rsLoggedIn['chatstatus']))) {echo "selected=\"selected\"";} ?>>Back soon</option>
      </select>
    </p>
    <?php } else { ?>
    <form action="index.php" method="post" name="form1" id="form1"> <p><label>Enter your first name:
   
        <input type="text" name="firstname" id="firstname" /></label><input name="" type="submit" value="Chat" />
   
     or <a href="../../login/index.php?accesscheck=/mail/chat/">log in</a> if you're  a member. 
    </p>
    </form>
  <?php } ?>    
    <div id="chatStatusReturnValue"><!-- any errors from ajax will go here--></div>
   
      <div id="selectuser">
  </div>
      <div id="chatlist"></div>
      
       <div id="loading"></div>

  
  <!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsPrefs);
?>
