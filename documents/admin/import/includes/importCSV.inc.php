<?php 
function insertCSVtoTable($filename, $inserttable, $insertcolumns=array(), $uniquefields=array(),$userID=0,$uploadID=0,$errorcheck="") { 
// unique firls will not allow duplicates for these fields - can be several
// errorcheck = check to see if certan fired are expected values, e.g. age:int
	$insertuploadID = false;
	$inserted = 0;
	$messages = array("message"=>array(), "error"=>array());
	// unique fields is an array of columns that is used to check for duplicates
	global $database_aquiescedb, $aquiescedb,  $log;
	// we might want to combine $allfields into $columns here 
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$fields=array();
	$q="SHOW FIELDS FROM ".$inserttable;
	$rsFields = mysql_query($q, $aquiescedb) or die(mysql_error());
	while ($field = mysql_fetch_assoc($rsFields)) 
   	{  // count through fields
   		$fields[$field["Field"]] = $field;	
		if($field["Field"] == "uploadID") { $insertuploadID = true; }
    } // end count through fields
	ini_set('auto_detect_line_endings', true);
		$handle = fopen($filename,"r");
		if($handle) { // file OK
			$linecount = 0; $log = "";
			$columns = array();
			// do integrity check first
			foreach($insertcolumns as $key => $column) {
				if($column != "") {	array_push($columns,$column); }
				
			}
			while($csvfields = fgetcsv($handle,65535)) { // get line
				$linecount++; 
				if(count($csvfields) != count($columns)) {	
					array_push($messages["error"],"<strong>Column count mismatch on line ".$linecount.".</strong><br /><br />Please check your CSV file - each row must have the same number of items. Check that there are no missing or extraneous commas or line breaks. ");break;
				}
			} // end get line
			if(!is_array($messages["error"]) || count($messages["error"])<1) { // no errors
				$linecount = 0;
				
				$handle = fopen($filename,"r");
				
				while($csvfields = fgetcsv($handle,65535)) { // get line
					$linecount ++;
						// build SQL
					if($linecount > 1 || !isset($_POST['ignorerow1'])) { //if not first line
						$lastuploadID = ($uploadID>0 && $insertuploadID == true) ? $uploadID : 0; 
						$sql_error = insertRow($inserttable,$fields,$columns,$csvfields,$lastuploadID,$uniquefields,$userID);
						if($sql_error != "") {
							$sql_error = "Line ".$linecount." not added - ".$sql_error;
						array_push($messages["error"],$sql_error);
						} else {
							$inserted++;
						}
						
						/**********************************/
						/****** PLUG IN *******************/
						/**********************************/
						if($inserttable == "bs_pick") {
						// check to see if there is new NLP number
							$select = "SELECT ID FROM bs_titles WHERE nlp = ".GetSQLValueString($csvfields[1],"text");
							$result = mysql_query($select, $aquiescedb) or die(mysql_error().$select);
							if(mysql_num_rows($result)==0) {
								$insert = "INSERT INTO bs_titles (nlp, title, statusID, createdbyID, createddatetime) VALUES (".GetSQLValueString($csvfields[1],"text").",".GetSQLValueString($csvfields[0],"text").",1,".GetSQLValueString($_POST['createdbyID'],"int").",NOW())";
								mysql_query($insert, $aquiescedb) or array_push($messages["error"],mysql_error().": ".$insert);
							}							
						}
						/********END PLUG IN **************/
						
					} // end not first line
				} // end get line
				if($inserted>0) {
					array_push($messages["message"],$inserted." rows successfully added.");
				}
		
			} // no errors
			//die("TESTING:<br>".$log);
		} // file OK
		else { array_push($messages["error"],"Could not find the uploaded file: ".$filename);}
		return $messages;
}

function convertdata($csvfielddata,$fieldtype,$dateformat="") {
	if(!(strpos($fieldtype,"date")===false)) { 
		
		//$date= explode("/",$csvfielddata);
		//$csvfielddata = $date[1]."/".$date[0]."/".$date[2]; // switch to US format
		
		$csvfielddata = date('Y-m-d H:i:s', strtotime($csvfielddata)); // convert to SQL time
		
	}
	// add slashes as the are stripped by function below
	if (PHP_VERSION < 6) {
    $csvfielddata = get_magic_quotes_gpc() ? addslashes($csvfielddata) : $csvfielddata;
  }
	return GetSQLValueString($csvfielddata,"text"); 
}

function insertRow($table,$allfields,$columns,$columndata,$uploadID=0,$uniquefields=array(),$userID=0) {
	// unique fields is an array of columns that is used to check for duplicates
	global $database_aquiescedb, $aquiescedb,  $log;
	// we might want to combine $allfields into $columns here 
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$dupSQL = array();
	$sql_error = ""; 
						$sql = "INSERT INTO ".$table." ("; // first part of INSERT statement
						foreach($columns as $key1 => $column) {
							if($column !="none") { // do import
	 							$sql .= "`".$column."`,";
							} // end do import
						} // end for each
						$sql .= $uploadID > 0 ? "uploadID," : "";
						$sql .= "createdbyID, createddatetime) VALUES (";
						foreach($columndata as $key2 => $data) { // count through fields
							if($columns[$key2] != "none") { // do import
								$fieldtype = $allfields[$columns[$key2]]["Type"];
								// if there's an uploadID column populate it other covert data to appropraie SQL type...
								$data = convertdata($data,$fieldtype);
								$sql .= $data.",";
								if(in_array($columns[$key2], $uniquefields)) { // duplicate check columns
									array_push($dupSQL,$columns[$key2] ." = ". $data);
								}
							} // import
							
						} // end count through fields
						$sql .= $uploadID > 0 ? intval($uploadID)."," : "";
						$sql .= $userID;
						$sql .= ", NOW())";
						$duplicates = 0;
						if (count($dupSQL)>0) { 
							$duplicateSQL = "SELECT * FROM ".$table." WHERE ".implode(" AND ",$dupSQL);
							$result = mysql_query($duplicateSQL, $aquiescedb) or die(mysql_error().$duplicateSQL."<br />");
							$duplicates = mysql_num_rows($result);
							}
						// execute SQL
						if($duplicates == 0) { // not a duplicate
							$result = mysql_query($sql, $aquiescedb) or $sql_error = mysql_error().$sql."<br />";
							writeLog($sql); $log .= $sql."<br>";
							$log .= isset($duplicateSQL) ? $duplicateSQL."<br>" : "";
						} // end not a duplicate
						else {
							$error = implode(" AND ",$dupSQL);
							$sql_error = "Duplicate found: ".$error."; ";
						}
						return $sql_error;
}

?>
