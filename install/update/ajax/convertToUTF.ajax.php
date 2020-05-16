<?php 
require_once('../../../Connections/aquiescedb.php');
?><?php require_once('../../includes/mysqli_connection.inc.php'); ?><?php require_once('../../includes/install.inc.php'); ?>
<?php

if(!isset($_SESSION['MM_UserGroup']) || $_SESSION['MM_UserGroup']!=10) die("Not authorised");
if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{
	global $fb_mysqli_con;
  if (PHP_VERSION < 6) {
    $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
  }

  $theValue = function_exists("mysqli_real_escape_string") ? mysqli_real_escape_string($fb_mysqli_con, $theValue) : mysqli_escape_string($fb_mysqli_con, $theValue);

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

$q1 = "ALTER DATABASE ".$database_aquiescedb." CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci";
$result1 = mysqli_query($fb_mysqli_con,$q1);


	
		$q2 = 'SHOW TABLES';
		if ( !($result2 = mysqli_query($fb_mysqli_con,$q2))) {
		   echo '<span style="color: red;">Get SHOW TABLE - SQL Error: <br>' . "</span>";
		}
	
		
		while ( $master_tables = mysqli_fetch_array($result2) ) {
			echo $master_tables[0];
			# Loop through all tables in this database
			$master_table = $master_tables[key($master_tables)];
			$q3 = sprintf("ALTER TABLE %s CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci", $master_table);
			if ( !($result3 = mysqli_query($fb_mysqli_con,$q3) ) ) {
				echo '<span style="color: red;">UTF SET - SQL Error: <br>' . "</span>";
			  
				  break;
		   }
		 
		   
		   print "$master_table changed to UTF-8 successfully.<br>";
		
		   # Now loop through all the fields within this table
		   $q4 = "SHOW COLUMNS FROM `".$master_table."`";
		   if ( !($result4 = mysqli_query($fb_mysqli_con,$q4)) ) {
				  echo '<span style="color: red;">Get Table Columns Query - SQL Error: <br>' . "</span>";
			  
				  break;
		   }
		
		   while ( $column = mysqli_fetch_array( $result4 ) )
		   {
			  $field_name = $column['Field'];
			  $field_type = $column['Type'];
			  
			  # Change text based fields
			  $skipped_field_types = array('char', 'text', 'enum', 'set');
			  
			  foreach ( $skipped_field_types as $type )
			  {         
				 if ( strpos($field_type, $type) !== false )
				 {
					$q5 = "ALTER TABLE $master_table CHANGE `$field_name` `$field_name` $field_type CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
					$result5 = mysqli_query($fb_mysqli_con,$q5);
		
					echo "---- $field_name changed to UTF-8 successfully.<br>";
				 }
			  }
		   }
		  
		}
	
echo "Done.<br>";
?>