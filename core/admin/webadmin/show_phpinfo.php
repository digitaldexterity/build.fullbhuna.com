<?php
if (!isset($_SESSION)) {
  session_start();
}

if(isset($_SESSION['MM_UserGroup']) && $_SESSION['MM_UserGroup'] == 10) {
	phpinfo();
}
die();
?>