<?php if (!isset($_SESSION)) {
  session_start();
}
if(isset($_SESSION['MM_Username'])) {
	$host = gethostbyaddr($_GET['remote_address']);
	
	echo ($host != $_GET['remote_address']) ? " (".$host.")" : "";
} ?>