<?php
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT" ); 
header("Last-Modified: " . gmdate( "D, d M Y H:i:s" ) . "GMT" ); 
header("Cache-Control: no-cache, must-revalidate" ); 
header("Pragma: no-cache" );
header("Content-Type: text/xml; charset=utf-8");
if (isset($_GET['wordsearch']) && $_GET['wordsearch'] != '') {
require_once("../../Connections/aquiescedb.php");
mysql_select_db($database_aquiescedb, $aquiescedb);
$search = addslashes($_GET['wordsearch']);
$query = "SELECT DISTINCT(name) as suggest FROM directory LEFT JOIN directorycategory ON (directory.categoryID = directorycategory.ID) WHERE name like('" . $search . "%') AND directorycategory.regionID = ".intval($regionID)." UNION SELECT DISTINCT(description) as suggest FROM directorycategory WHERE description like('" . $search . "%') AND directorycategory.regionID = ".intval($regionID)." ORDER BY suggest LIMIT 30";
$result = mysql_query($query, $aquiescedb) or die($query);
while($row = mysql_fetch_assoc($result)) {
echo $row['suggest']."\n";
}
}
?>