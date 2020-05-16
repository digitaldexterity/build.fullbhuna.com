<?php 


function site_root() {
	//$site_root =  addslashes(substr($_SERVER['SCRIPT_FILENAME'], 0, 0-strlen($_SERVER['PHP_SELF']) ).DIRECTORY_SEPARATOR ); // safe calculation of DOCUMENT_ROOT for all unix and windows cnmfigs
	$site_root = addslashes(substr(dirname(__FILE__), 0, -16)); // remove "/install from directory path 
	return $site_root;
}

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


function load_db_dump($file,$sqlserver,$user,$pass,$dest_db)
{
  
  $a=file($file);
  if($a) {
  foreach ($a as $n => $l) {
	  	
	   if (substr($l,0,2)=='--') {
		   unset($a[$n]);
	   } else {
		   $a[$n] = str_replace("\r\n","\n",$l);
	   }
  }
  $a=explode(";\n",implode("\n",$a));
  unset($a[count($a)-1]);
  $sql=mysql_connect($sqlserver,$user,$pass);
  mysql_select_db($dest_db);
  foreach ($a as $q) { 
	
  	if ($q) { 
   		if (!mysql_query($q)) { echo "Fail on '".$q."'<br>[".mysql_error()."]"; mysql_close($sql); return 0;}
	}
  }
  mysql_close($sql);
  return 1;
  } else { //coun't read file
  echo "Couldn't read file: ".htmlentities($file); return 0;
  }
}


function createDirectories($site_root="") {

	if(!is_dir($site_root."Connections".DIRECTORY_SEPARATOR)) {
				mkdir($site_root."Connections".DIRECTORY_SEPARATOR, 0755, true);
	}
	if(!is_dir($site_root."Uploads".DIRECTORY_SEPARATOR)) {
				mkdir($site_root."Uploads".DIRECTORY_SEPARATOR, 0755, true);
			}
			
			if(!is_dir($site_root."Uploads".DIRECTORY_SEPARATOR."filemanager".DIRECTORY_SEPARATOR)) {
				mkdir($site_root."Uploads".DIRECTORY_SEPARATOR."filemanager".DIRECTORY_SEPARATOR, 0755, true);
			}
			if(!is_dir($site_root."Uploads".DIRECTORY_SEPARATOR."filemanager_thumbs".DIRECTORY_SEPARATOR)) {
				mkdir($site_root."Uploads".DIRECTORY_SEPARATOR."filemanager_thumbs".DIRECTORY_SEPARATOR, 0755, true);
			}
			
			if(!is_readable($site_root."Uploads".DIRECTORY_SEPARATOR.".htaccess")) {
				
				$f = fopen($site_root."Uploads".DIRECTORY_SEPARATOR.".htaccess", "a+");
					fwrite($f, '#Options All  -ExecCGI -Includes -Indexes
#php_flag engine off
<FilesMatch "\.(php|phps|html|htm|jsp|asp)$">
    Order allow,deny
    Deny from all
</Files>');
					fclose($f);
			}
			
			
			if(!is_dir($site_root."Uploads".DIRECTORY_SEPARATOR."filemanager".DIRECTORY_SEPARATOR)) {
				return false;
			} else {
			return true;
}
}





if(!function_exists("cleanUpFiles")) {
function cleanUpFiles($dirPath=SITE_ROOT){
    if (! is_dir($dirPath)) {
        throw new InvalidArgumentException("$dirPath must be a directory");
    }
	
    if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
        $dirPath .= '/';
    }
   // all files including hidden
	$files = glob($dirPath.'{,.}*', GLOB_BRACE);
    foreach ($files as $file) {
		if(!strpos($file,"./")) {
        if (is_dir($file)) {			
            	cleanUpFiles($file);			
        } else {    
		// get rid of potential security issue files        
			if(preg_match("#DS_Store|uploadify|[.]sql|dwsync[.]xml|/temp[.]|/test[.]|/mockup[.]/phpinfo[.]|/sandbox/|/_notes/|/__MACOSX/#i", $file)) {
				//echo "DELETEING ".$file."<br>";
				unlink($file);
			} else {
				//echo "Not deleting ".$file."<br>";
			}
        }
		}
    }    
}
}

if(isset($aquiescedb) && isset($_SESSION['MM_Username'])) {
mysql_select_db($database_aquiescedb, $aquiescedb);
$select = "SELECT ID FROM users WHERE username = ".GetSQLValueString($_SESSION['MM_Username'], "text");
$result = mysql_query($select, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($result);
}

?>
