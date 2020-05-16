<?php if(!function_exists("containsBannedWords")) {
function containsBannedWords($teststring,$checkforlinks=false) {
	global $database_aquiescedb, $aquiescedb;
	$found = 0;
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$select = "SELECT word FROM bannedwords";
	$result = mysql_query($select, $aquiescedb) or die(mysql_error());
	while($row = mysql_fetch_assoc($result)) {
		if(stripos($teststring,$row['word'])) {
			$found ++;
		}
	}
	if($checkforlinks && (stripos($teststring,"<") || stripos($teststring,"http://") || stripos($teststring,"www") || stripos($teststring,"cc:"))) {
		$found ++;
	}
	return ($found>0) ? true : false;	
}
}

?>
