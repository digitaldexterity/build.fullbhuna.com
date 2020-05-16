<?php

session_start();

if(!isset($_SESSION['keepalivestarted'])) {
	$_SESSION['keepalivestarted'] = time();
}

foreach($_SESSION as $key => $value) {
	$_SESSION[$key] = $_SESSION[$key];
}

echo $_SESSION['keepalivestarted'];


?>