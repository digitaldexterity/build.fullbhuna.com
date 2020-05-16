<?php require_once('../../../Connections/aquiescedb.php');
?><?php require_once('../../includes/mysqli_connection.inc.php'); ?><?php require_once('../../includes/install.inc.php'); ?>
<?php



function add_tmp_tables($file)
{

 global $fb_mysqli_con;
  $a= file($file); // get new SQL file and replace text to add fb_tmp_
  $a= str_replace ("DROP TABLE IF EXISTS `" , "DROP TABLE IF EXISTS `fb_tmp_" , $a);
  $a= str_replace ("CREATE TABLE IF NOT EXISTS `" , "CREATE TABLE IF NOT EXISTS `fb_tmp_" , $a);
  $a= str_replace ("CREATE TABLE `" , "CREATE TABLE IF NOT EXISTS `fb_tmp_" , $a);
  $a= str_replace ("INSERT INTO `" , "INSERT INTO `fb_tmp_" , $a);
  if(mysqli_get_server_info($fb_mysqli_con)<4.2) {
	    $a= str_replace ("ENGINE=" , "TYPE=" , $a);
  }
 
   
  foreach ($a as $n => $l) {
	  if (substr($l,0,2)=='--') {unset($a[$n]);} // remove comments
  }
  
    $a=preg_split("/;\r\n|;\r|;\n/", implode(PHP_EOL,$a));
	
  foreach ($a as $q) {
	  if (trim($q)!="") {		 
   		if (!mysqli_query($fb_mysqli_con,$q)) {
	  		 echo "Fail on: ".$q." - ".mysqli_error($fb_mysqli_con); mysqli_close($fb_mysqli_con); return 0;
		}
	  }
  }
 
  return 1;
}


$site_root =  site_root();	
$file = (isset($_GET['sqlfile']) && $_GET['sqlfile'] == 2) ? $site_root."local/additions.sql" : $site_root."install/fullbhuna.sql";

if(is_readable($file)) {
	echo "Using ".$file."<br>";
	add_tmp_tables($file) or die("Error in adding new temp files");
	echo "Done.<br>";
} else {
	echo "ERROR: Can't read ".$file."<br>";
}



	
	

?>