<?php 

if(!isset($_SESSION['MM_UserGroup'])) die();

function addTag($tagID=0, $blogentryID=0, $eventgroupID=0, $newsID = 0, $createdbyID=0) {
	global $database_aquiescedb, $aquiescedb;
	mysql_select_db($database_aquiescedb, $aquiescedb);
	if($tagID>0 && ($blogentryID>0||$eventgroupID>0||$newsID>0)) { 
		$select = "SELECT ID FROM tagged WHERE tagID = ".GetSQLValueString($tagID, "int")." AND blogentryID = ".GetSQLValueString($blogentryID, "int")." AND eventgroupID = ".GetSQLValueString($eventgroupID, "int")." AND newsID = ".GetSQLValueString($newsID, "int")."";
		$result = mysql_query($select, $aquiescedb) or die(mysql_error().":".$select);
		if(mysql_num_rows($result)==0) {
			$insert = "INSERT INTO tagged(tagID, blogentryID,eventgroupID, newsID,createdbyID, createddatetime) VALUES 
			(".GetSQLValueString($tagID, "int").",".
			GetSQLValueString($blogentryID, "int").",".
			GetSQLValueString($eventgroupID, "int").",".
			GetSQLValueString($newsID, "int").",".
			GetSQLValueString($createdbyID, "int").",NOW())";
			
			mysql_query($insert, $aquiescedb) or die(mysql_error().":".$insert);
			return mysql_insert_id();
		}
	}
	
	return false;
	
}

?>