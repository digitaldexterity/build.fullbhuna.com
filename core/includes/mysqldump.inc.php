<?php // Copyright 2013 Paul Egan ?>
<?php if(!defined("SITE_ROOT")) die(); // can only be called from FB ?>
<?php 
function dumpDatabase($database, $file) { 
	// Connect to database 
    $db = @mysql_select_db($database); 
	$result = false; $bytes = 0;

	if (!empty($db)) { 
		$handler = fopen($file, 'w+');


		// Get all table names from database 
		$c = 0; 
		$result = mysql_list_tables($database); 
		for($x = 0; $x < mysql_num_rows($result); $x++) { 
			$table = mysql_tablename($result, $x); 
			if (!empty($table)) { 
				$arr_tables[$c] = mysql_tablename($result, $x); 
				$c++; 
			} 
		} 

		// List tables 
		foreach ($arr_tables as $table) { // count thru tables
			
			// Structure Header 
			$structure = "-- \n"; 
			$structure .= "-- Table structure for table `".$table."` \n"; 
			$structure .= "-- \n\n"; 

			// Dump Structure 
			
			$structure .= "DROP TABLE IF EXISTS `".$table."`; \n"; 
			$structure .= "CREATE TABLE `".$table."` (\n"; 
			$result = mysql_db_query($database, "SHOW FIELDS FROM `".$table."`"); 
			while($row = mysql_fetch_object($result)) { 

				$structure .= "  `".$row->Field."` ".$row->Type.""; 
				$structure .= (!empty($row->Default)) ? " DEFAULT '".$row->Default."'" : false; 
				$structure .= ($row->Null != "YES") ? " NOT NULL" : false; 
				$structure .= (!empty($row->Extra)) ? " ".$row->Extra."" : false; 
				$structure .= ",\n"; 

			} 

			$structure = preg_replace("/,\n$/", "", $structure); 

			// Save all Column Indexes in array 
			unset($index); 
			$result = mysql_db_query($database, "SHOW KEYS FROM `".$table."`"); 
			while($row = mysql_fetch_object($result)) { 

				if (($row->Key_name == 'PRIMARY') AND ($row->Index_type == 'BTREE')) { 
					$index['PRIMARY'][$row->Key_name] = $row->Column_name; 
				} 

				if (($row->Key_name != 'PRIMARY') AND ($row->Non_unique == '0') AND ($row->Index_type == 'BTREE')) { 
					$index['UNIQUE'][$row->Key_name] = $row->Column_name; 
				} 

				if (($row->Key_name != 'PRIMARY') AND ($row->Non_unique == '1') AND ($row->Index_type == 'BTREE')) { 
					$index['INDEX'][$row->Key_name] = $row->Column_name; 
				} 

				if (($row->Key_name != 'PRIMARY') AND ($row->Non_unique == '1') AND ($row->Index_type == 'FULLTEXT')) { 
					$index['FULLTEXT'][$row->Key_name] = $row->Column_name; 
				} 

			} 

			// Return all Column Indexes of array 
			if (isset($index) && is_array($index)) { 
				foreach ($index as $xy => $columns) { 
					$structure .= ",\n"; 
					$c = 0; 
					foreach ($columns as $column_key => $column_name) { 
						$c++; 
						$structure .= ($xy == "PRIMARY") ? "  PRIMARY KEY  (`{$column_name}`)" : false; 
						$structure .= ($xy == "UNIQUE") ? "  UNIQUE KEY `{$column_key}` (`{$column_name}`)" : false; 
						$structure .= ($xy == "INDEX") ? "  KEY `{$column_key}` (`{$column_name}`)" : false; 
						$structure .= ($xy == "FULLTEXT") ? "  FULLTEXT `{$column_key}` (`{$column_name}`)" : false; 

						$structure .= ($c < (count($index[$xy]))) ? ",\n" : false;
					} 
				} 
			} 
			$structure .= "\n);\n\n"; 

			// Header 
			$structure .= "-- \n"; 
			$structure .= "-- Dumping data for table `$table` \n"; 
			$structure .= "-- \n\n"; 
			
			$writeresult = fwrite($handler, $structure ); 
			if($writeresult) {
				$bytes += $writeresult;
			} else {
				return false;
			}
			
			
			// do not include tracker or search tables
			if (!(preg_match("/(track_|search_)/i",$table))) { // not tracker or search
				// Dump data 
				$sql = "SELECT * FROM `".$table."`";
				$result     = mysql_query($sql) or die(mysql_error().": ".$sql);
				$num_rows   = mysql_num_rows($result); 
				$num_fields = mysql_num_fields($result); 
				if($num_rows>0){ // is rows
					for ($i = 0; $i < $num_rows; $i++) { 
						$data = "";
						$row = mysql_fetch_object($result); 
						if($i==0) { // first row only...
							$insert = "INSERT INTO `$table` ("; 
	
							// Field names 
							for ($x = 0; $x < $num_fields; $x++) { 
	
								$field_name = mysql_field_name($result, $x); 
	
								$insert .= "`{$field_name}`"; 
								$insert .= ($x < ($num_fields - 1)) ? ", " : false; 
	
							} 
	
							$data .= $insert.") VALUES (";
						} else if($i%100==0) { // start again every 100 rows
							$data .= ");\n".$insert.") VALUES (";
						} else {
							$data .= "),("; 
						}
	
						// Values 
						for ($x = 0; $x < $num_fields; $x++) { 
							$field_name = mysql_field_name($result, $x); 
	
							$data .= $row->$field_name === NULL ? "NULL" : "'" . str_replace('\"', '"', mysql_escape_string($row->$field_name)) . "'"  ;
							$data .= ($x < ($num_fields - 1)) ? ", " : false; 
	
						} 
						// write data for table row to file
						$writeresult = fwrite($handler, $data ); 
						if($writeresult) {
							$bytes += $writeresult;
						} else {
							return false;
						}
	
						
					} // each row
					$writeresult = fwrite($handler, ");\n\n"); 
				} // is rows
				
				
				  if($writeresult) {
					  $bytes += $writeresult;
				  } else {
					  return false;
				  }
				
			} // not tracker
			
			$write = "-- --------------------------------------------------------\n\n"; 

			$writeresult = fwrite($handler, $write ); 
			if($writeresult) {
				$bytes += $writeresult;
			} else {
				return false;
			}
		
		} // end  tables loop 

	  $writeresult = fclose($handler);

	} 
	if($writeresult) {
		return $bytes;
	} else {
		return false;
	}

} 


?>
