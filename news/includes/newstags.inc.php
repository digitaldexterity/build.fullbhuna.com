<?php // TAGS

$varNewsID_rsTags = "-1";
if (isset($_GET['newsID'])) {
  $varNewsID_rsTags = intval($_GET['newsID'])-100;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsTags = sprintf("SELECT tag.ID, tagname FROM tag LEFT JOIN tagged ON (tag.ID = tagged.tagID) WHERE tagged.newsID = %s", GetSQLValueString($varNewsID_rsTags, "int"));
$rsTags = mysql_query($query_rsTags, $aquiescedb) or die(mysql_error());
;
$totalRows_rsTags = mysql_num_rows($rsTags); 

if($totalRows_rsTags>0) { 

$newsbackURL .= strpos($newsbackURL,"?") ? "&" : "?"; ?><ul class="news-tags"><?php
	while($row_rsTags = mysql_fetch_assoc($rsTags)) { ?>
    <li><a href="<?php echo $newsbackURL."tagID=".$row_rsTags['ID']; ?>"><?php echo $row_rsTags['tagname']; ?></a></li>
		
<?php 	} //end while ?></ul>
	
	
	
<?php } // end is tags ?>


<?php mysql_free_result($rsTags);

 ?>