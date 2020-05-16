<?php require_once('../../../Connections/aquiescedb.php');
?><?php require_once('../../includes/mysqli_connection.inc.php'); ?><?php require_once('../../includes/install.inc.php'); ?>
<?php




$site_root =  site_root();	
$file = (isset($_GET['sqlfile']) && $_GET['sqlfile'] == 2) ? $site_root."local/additions.sql" : $site_root."install/fullbhuna.sql";

if(is_readable($file)) {
	
	// now get structure of temp tables into array
	$q = "SHOW TABLES FROM `".$database_aquiescedb."` LIKE 'fb_tmp_%'";
	$rs = mysqli_query($fb_mysqli_con,$q) or die(mysqli_error($fb_mysqli_con).": ".$q);
	$master_table = mysqli_fetch_array($rs);	
	$master_tables=array();	
	do {       
		$fields=array();
		$q="SHOW COLUMNS FROM `".$master_table[0]."`";
		$rsFields = mysqli_query($fb_mysqli_con,$q) or die(mysqli_error($fb_mysqli_con).": ".$q);		  
		while ($field = mysqli_fetch_assoc($rsFields)) { 
			array_push($fields,$field);	
		}
		array_push($master_tables,array("name"=>substr($master_table[0],7),"fields"=>$fields)); // takes off fb_tmp_
		   //after adding to array drop table to clean
		$dropq = "DROP TABLE `".$master_table[0]."`";
		$droprs = mysqli_query($fb_mysqli_con,$dropq) or die(mysqli_error($fb_mysqli_con).": ".$dropq);
	} while ($master_table = mysqli_fetch_array($rs));
	
	echo "Created array from Master<br>";
	
	
	
	$q = "SHOW TABLES FROM `".$database_aquiescedb."`";
	$rs = mysqli_query($fb_mysqli_con,$q) or die(mysqli_error($fb_mysqli_con).": ".$q);
	
	$current_tables=array();	
	if(mysqli_num_rows($rs)>0) {	
		while ($current_table = mysqli_fetch_array($rs))		
		{  // count through tables    
		
		
		//echo "<pre>*"; print_r($current_table); die("*");
		
			$fields=array();
			$q="SHOW FIELDS FROM `".$current_table[0]."`";
			$rsFields = mysqli_query($fb_mysqli_con,$q) or die(mysqli_error($fb_mysqli_con).": ".$q);
			if(mysqli_num_rows($rsFields)>0) {				
				while ($field = mysqli_fetch_assoc($rsFields)) 
				{	  // count through fields
					array_push($fields,$field);	
				} // end count through fields
			}
			array_push($current_tables,array("name"=>$current_table[0],"fields"=>$fields));
		}  // end count through tables
		
		
		
		echo "Created array from current tables<br>";
	}
	//echo "<pre>"; print_r($master_tables);
	//echo "<pre>"; print_r($current_tables); die();
	
	// compare tables and create SQL
	$sql = "";
	
	foreach($master_tables as $master_table) { // browse thru master tables
		$found_table=false; 
		foreach($current_tables as $current_table) { 
			if($current_table["name"]==$master_table["name"]) { 
				$found_table = $current_table; 
			}
		}
		
		if(is_array($found_table)) {  // table exists, check fields
			foreach($master_table["fields"] as $master_field) { // count through fields in master table
				$found_field=false;
				foreach($found_table["fields"] as $current_field) {
					if($master_field['Field']==$current_field['Field']) { 
						$found_field=true; // check if any difference in values
						if($master_field['Type'] != $current_field['Type'] || $master_field['Null'] != $current_field['Null'] || $master_field['Default'] != $current_field['Default'] || $master_field['Extra'] != $current_field['Extra'] || ($master_field['Key'] !="" && $current_field['Key']=="")) { // field does exist but some values are different, so update
		
							$q = "ALTER TABLE `".$master_table['name']."` MODIFY `".$master_field['Field']."` ";
							$q.= $master_field['Type'];
							$q.= ($master_field["Null"]=="YES") ? "" : " NOT NULL ";
							if (is_null($master_field['Default']) && $master_field['Extra'] != "auto_increment") {
								 // $q.= " DEFAULT NULL "; *** replaced by below
								$q.= ($master_field["Null"]=="YES") ? " DEFAULT NULL " : "";
							} else if ($master_field['Default']!='') {
								$q.= " DEFAULT ";
								$q .= $master_field['Default'] == "CURRENT_TIMESTAMP " ? "CURRENT_TIMESTAMP" :"'".$master_field['Default']."' ";
							}
							$q .= $master_field['Extra'];
							
							if($master_field['Key'] != $current_field['Key']) {
								if($master_field['Key']=='UNI' || $master_field['Key']=='MUL') {
									$q .= ";\nALTER TABLE `".$master_table['name']."` ADD KEY `".$master_field['Field']."` (`".$master_field['Field']."`)";	
								} else if($master_field['Key']=='PRI') {
									$q .= ";\nALTER TABLE `".$master_table['name']."` ADD PRIMARY KEY `".$master_field['Field']."` (`".$master_field['Field']."`)";	
								}
							}
							$sql .= $q.";\n";
						}
					}
				}
				if(!$found_field) { // field not found so add it...         	  
		 
					$q = "ALTER TABLE `".$master_table['name']."` ADD `".$master_field['Field']."` ";
					$q.= $master_field['Type'];
					$q.= ($master_field["Null"]=="YES") ? "" : " NOT NULL ";
					if (is_null($master_field['Default']) && $master_field['Extra'] != "auto_increment") {
						$q.= ($master_field["Null"]=="YES") ? " DEFAULT NULL " : " DEFAULT '' ";
					} else if ($master_field['Default']!='') {
						$q.= " DEFAULT ";
						$q .= $master_field['Default'] == "CURRENT_TIMESTAMP " ? "CURRENT_TIMESTAMP" :"'".$master_field['Default']."' ";
					}
					$q .= $master_field['Extra'];
					$q .= ($master_field['Key']=='PRI') ? " PRIMARY KEY " : "";							
					if($master_field['Key']=='MUL' || $master_field['Key']=='UNI') {
						$keytype = ($master_field['Key']) == "UNI" ? "UNIQUE " : "";
						$q .= ";\nALTER TABLE `".$master_table['name']."` ADD ".$keytype."KEY `".$master_field['Field']."` (`".$master_field['Field']."`)";
					}
					$sql .= $q.";\n";
				} 
			} 
		}  else {    //table does not exists, create it
			 $q="CREATE TABLE `".$master_table["name"]."` (";
			 $keys = "";
			 foreach($master_table["fields"] as $field_count=>$master_field) {
				$primary= ($master_field["Key"]=='PRI') ? " PRIMARY KEY " : "";
				$keys .= ($master_field["Key"]=='MUL') ? ", KEY `".$master_field['Field']."` (`".$master_field['Field']."`)" : "";
				$q.="`".$master_field["Field"]."` ".$master_field['Type'];
				$q.= ($master_field["Null"]=="YES") ? "" : " NOT NULL ";
				if (is_null($master_field['Default']) && $master_field['Extra'] != "auto_increment") {
					$q.= ($master_field["Null"]=="YES") ? " DEFAULT NULL " : "";
				} else if ($master_field['Default']!='') {
					$q.= " DEFAULT ";
					$q .= $master_field['Default'] == "CURRENT_TIMESTAMP" ? "CURRENT_TIMESTAMP " :"'".$master_field['Default']."' ";
				}
				$q.= $master_field['Extra']." ".$primary;
				if($field_count<(sizeof($master_table['fields'])-1)) { 
					$q.=", \n"; 
				}
			}
			$q.= $keys.") ENGINE=MyISAM AUTO_INCREMENT=1; \n\n";
			$sql .= $q;
		} // end create table
			
	}
	
	echo "Finished creating SQL<br>"; 
	
	if($sql !="") {// SQL exists
		echo "<strong>Changes made:</strong><br />";
	
		$a=explode(";",$sql);
		unset($a[count($a)-1]);
		foreach ($a as $q) {
			if($q) {
				if (!mysqli_query($fb_mysqli_con,$q)) {
					echo "*** FAILED: ".$q." (".mysqli_error($fb_mysqli_con).")"; 
				} else {
					echo "SUCCESS: ".$q;
				}
				echo "<br>";
			} 
		}
	} else {
		echo "No changes required.<br><br>";
	}
	
	
	// OTHER HOUSEKEEPING QUERIES
	echo "HOUSEKEEPING...<br>";
	// make sure documents write access is not lower than read access
	$update = "UPDATE documents SET writeaccess = accessID WHERE writeaccess < accessID";
	mysqli_query($fb_mysqli_con,$update);
	// add linktitles to articles without them
	$update = "UPDATE article SET linktitle = title WHERE linktitle IS NULL AND versionofID IS NULL";
	mysqli_query($fb_mysqli_con,$update);
	// set up organisation name in prefs
	$update = "UPDATE preferences SET updateddatetime= '".date('Y-m-d H:i:s')."',modifiedbyID = ".$row_rsLoggedIn['ID'].", modifieddatetime= '".date('Y-m-d H:i:s')."'";
	mysqli_query($fb_mysqli_con,$update) or die(mysqli_error($fb_mysqli_con));
} else {
	//echo "ERROR: Can't read ".$file."<br>";
}


?>