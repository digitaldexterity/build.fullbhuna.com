<?php require_once('../../Connections/aquiescedb.php'); ?>
<?php 
$theValue =  $_GET['qu'];
 if (PHP_VERSION < 6) {
	$theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
 }
$theValue = function_exists("mysql_real_escape_string") ? mysql_real_escape_string($theValue) : mysql_escape_string($theValue);
mysql_select_db($database_aquiescedb, $aquiescedb);
$query = "SELECT username FROM users WHERE username = '".$theValue."' LIMIT 1";
$result = mysql_query($query, $aquiescedb) or die(mysql_error());
$row = mysql_fetch_assoc($result);
if(mysql_num_rows($result)>0) { ?>
<img src="/core/images/icons/cross.png" alt="Red cross" style="vertical-align:middle;width:16px; height:16px;"> <?php echo $row['username']; ?> is already used. <a href="/login/forgot_password.php">Is this you?</a>
<?php
} else { ?><img src="/core/images/icons/tick-green.png" alt="Tick" style="vertical-align:middle;width:16px; height:16px;">
<?php }
exit;
?>

