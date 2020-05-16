<?php 

//start a session 
if (!isset($_SESSION)) {
  session_start();
}

$spam1 = rand(1,9); $spam2= rand(1,9); 

$_SESSION['captcha'] = $spam1+$spam2;
?>
