<?php function findNextAvailable($count = 1, $eventID = 1, $fillgaps = 1) {
	//before using this function:
	
	//mysql_query("LOCK TABLES eventregistration, eventregistration AS x,eventregistration AS r, eventregistration AS m WRITE;");
	
	//mysql_query("UNLOCK TABLES;");
	
	
	global $database_aquiescedb, $aquiescedb;
	mysql_select_db($database_aquiescedb, $aquiescedb);
	
	if($fillgaps==1) {

	$select = "SELECT start, stop FROM 
	(SELECT m.registrationnumber + 1 as start, 
    		(SELECT min(registrationnumber) - 1 FROM eventregistration AS x 
		WHERE x.registrationnumber > m.registrationnumber  AND x.eventID = ".$eventID.") AS stop
	
	FROM eventregistration AS m

    	LEFT OUTER JOIN 
	eventregistration AS r ON (m.registrationnumber = r.registrationnumber - 1 AND r.eventID = ".$eventID.")
  	
	WHERE r.registrationnumber IS NULL  AND m.eventID = ".$eventID."
	) AS x
WHERE stop IS NULL OR stop-start+1 >= ".$count."
";
		$errorsql = (isset($_SESSION['MM_UserGroup']) && $_SESSION['MM_UserGroup']==10) ? ":<br><br>".$select : ""; // only have full select statement if webadmin
		$result = mysql_query($select, $aquiescedb) or die(mysql_error().$errorsql);

		$row = mysql_num_rows($result)>0 ? mysql_fetch_assoc($result) : 1;
		//$row['select'] = $select;
	return $row['start'];
	} else {
		
		$select = "SELECT max(registrationnumber)+1 AS number FROM eventregistration WHERE eventID = ".intval($eventID);
		$result = mysql_query($select, $aquiescedb) or die(mysql_error());
		$row = mysql_fetch_assoc($result);
		return $row['number'];
	}
}

/* adapted from Next available SQL

select start, stop from (
  select m.id + 1 as start,
    (select min(id) - 1 from sequence as x where x.id > m.id) as stop
  from sequence as m
    left outer join sequence as r on m.id = r.id - 1
  where r.id is null
) as x
where stop is not null;

*/

?>