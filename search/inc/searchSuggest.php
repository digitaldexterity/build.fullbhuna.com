<?php // Copyright 2009 Paul Egan ?><?php
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT" ); 
header("Last-Modified: " . gmdate( "D, d M Y H:i:s" ) . "GMT" ); 
header("Cache-Control: no-cache, must-revalidate" ); 
header("Pragma: no-cache" );
header("Content-Type: text/xml; charset=utf-8");
if (isset($_GET['wordsearch']) && $_GET['wordsearch'] != '') {
require_once("../../Connections/aquiescedb.php");
mysql_select_db($database_aquiescedb, $aquiescedb);
$search = addslashes($_GET['wordsearch']);
$query = "SELECT DISTINCT(word) as suggest FROM isearch_words WHERE  regionID = ".intval($regionID)." AND word like('" . $search . "%') ORDER BY suggest LIMIT 30";
$result = mysql_query($query, $aquiescedb);
while($row = mysql_fetch_assoc($result)) {
echo $row['suggest']."\n";
}
}
?>