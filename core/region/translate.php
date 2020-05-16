<?php require_once('../../Connections/aquiescedb.php'); 
$url = $_SERVER['HTTP_REFERER']."?".$_SERVER['QUERY_STRING']; // back to original if no translation
if(isset($_GET['translate']) && $_GET['translate']!="") {
	$values = explode("|",$_GET['translate']);
	$pageTitle = "Translate page to ".$values[0];
	require_once('../seo/includes/seo.inc.php');
	$url = "https://translate.google.com/translate?hl=en&u=".urldecode($values[1])."&langpair=en%7C".$values[2]."&hl=en";
}
header("location: ".$url); exit; ?>
