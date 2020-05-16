<?php require_once('../../../../Connections/aquiescedb.php'); ?>
<?php

if(!isset($_SESSION['MM_UserGroup']) || $_SESSION['MM_UserGroup'] <10) die();
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

$table = preg_replace("/[^A-Za-z0-9\.@_\-]/", "", $_GET['table']);
if($table!="") {
$q="SHOW FIELDS FROM ".$table;

	$rsFields = mysql_query($q, $aquiescedb) or die(mysql_error());
	echo "<select name = \"field\" class=\"form-control\">";
      	
 	while ($field = mysql_fetch_assoc($rsFields)) 
   	{  // count through fields
   		echo "<option"; echo ($field['Field']==@$_GET['selectedfield']) ? " selected=\"selected\" " : ""; echo ">".$field['Field']."</option>";
    } // end count through fields
	
	echo "</select>";
} 
?>