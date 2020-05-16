<?php function copyPagesToRegion($newRegion = 0, $fromRegion = 1) {
	global $database_aquiescedb, $aquiescedb;
	mysql_select_db($database_aquiescedb, $aquiescedb);
	// REQUIRES framework.inc
	if($newRegion>0 && $fromRegion>0) { // need region numbers but possibilty to use 0 later
		// copy product prefs
		//duplicateMySQLRecord ("productPrefs", $fromRegion, "ID", $newRegion );
		$oldnewsections = array();
		
		
		// copy article sections
		$select = "SELECT * FROM articlesection WHERE regionID = ".intval($fromRegion);
		$result = mysql_query($select, $aquiescedb) or die(mysql_error());
		if(mysql_num_rows($result)>0) {
			while($row = mysql_fetch_assoc($result)) {
				$newID = duplicateMySQLRecord ("articlesection", $row['ID'], "ID");
				$update = "UPDATE articlesection SET createdbyID= 0, createddatetime = NOW(), regionID = ".$newRegion." WHERE ID = ".$newID;
				mysql_query($update, $aquiescedb) or die(mysql_error());
				$oldnewsections[$row['ID']] = $newID;
				
			}
			// update sub cat of ID
			foreach($oldnewsections as $key=> $value) {				
				$update = "UPDATE articlesection SET subsectionofID = ".$value." WHERE subsectionofID = ".$key;
				mysql_query($update, $aquiescedb) or die(mysql_error());			
			}
		}
		
		
		
		
		// copy pages
		$select = "SELECT * FROM article WHERE versionofID  IS NULL AND regionID = ".intval($fromRegion);
		$result = mysql_query($select, $aquiescedb) or die(mysql_error());
		if(mysql_num_rows($result)>0) {
			while($row = mysql_fetch_assoc($result)) {
				$newID = duplicateMySQLRecord ("article", $row['ID'], "ID");
				$newsectionID = $oldnewsections[$row['sectionID']]>0 ? intval($oldnewsections[$row['sectionID']]) : 0;
				$update = "UPDATE article SET createdbyID= 0, createddatetime = NOW(), regionID = ".$newRegion.", sectionID = ".$newsectionID." WHERE   ID = ".$newID;
				mysql_query($update, $aquiescedb) or die(mysql_error());
				
										
			}
		}
		
		
		
		
		
		
	}
	
}
					
?>