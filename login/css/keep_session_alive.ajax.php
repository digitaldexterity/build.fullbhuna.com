<?php

session_start();
foreach($_SESSION as $key=>$value) {
	$_SESSION[$key] = $_SESSION[$key];
}
?>