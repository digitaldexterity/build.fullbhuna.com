<?php 
  mysql_select_db($database_aquiescedb, $aquiescedb);
  $regionID = isset($regionID ) ? intval($regionID ) : 1;

$select = "SELECT * FROM furniture WHERE statusID = 1 AND regionID = ".$regionID;
$result = mysql_query($select, $aquiescedb) or die(mysql_error());
$furniture = array();
$furniture[0] = ""; // use up 0 so rest match with mySQL ID.
while($rowFurniture = mysql_fetch_assoc($result)) {
	array_push($furniture,$rowFurniture);
}



function placeFurniture($startID = 1, $endID = 100) {
	
	global $furniture;
	for($ID=$startID; $ID<=$endID; $ID++) { 
	
	if(is_array($furniture[$ID]) && $furniture[$ID]['imageURL'] !="") { // exits 
	 ?>

<div id="furniture<?php echo $furniture[$ID]['ID']; ?>" class="furniture" > <a href="<?php echo $furniture[$ID]['furniturelink']; ?>" style="display:block; background-image: url(/Uploads/<?php echo $furniture[$ID]['imageURL']; ?>);  background-repeat:no-repeat; width:<?php echo $furniture[$ID]['width_px']; ?>px; height:<?php echo $furniture[$ID]['height_px']; ?>px; overflow:hidden; text-decoration:none; text-indent:-999em;" <?php if($furniture[$ID]['newwindow']==1) { echo "target=\"_blank\""; } ?>><?php echo $furniture[$ID]['furnituretext']; ?></a> </div>
<?php } // end exists
	
	} // end for
} // end func

?>
