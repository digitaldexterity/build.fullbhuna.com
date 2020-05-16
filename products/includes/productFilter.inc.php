<?php 

mysql_select_db($database_aquiescedb, $aquiescedb);
$select = "SELECT producttag.ID, producttag.tagname, producttaggroup.taggroupname FROM producttag LEFT JOIN producttaggroup ON (producttag.taggroupID = producttaggroup.ID) ORDER BY producttaggroup.ordernum, producttag.ordernum ";
$result = mysql_query($select, $aquiescedb) or die(mysql_error());
$row_rsTags = mysql_fetch_assoc($result);

if(mysql_num_rows($result)>0) {
?>
<div id="productfilter" ><div  class="productfiltertype">
  <form action="" method="post" name="form1" id="form1">
 
 
    <?php $taggroup = "-1"; do { 
	if($row_rsTags['taggroupname'] != $taggroup) {
		$taggroup = $row_rsTags['taggroupname'];
		echo $taggroup == "-1" ? "" : "</ul>\n";
		echo "<h3>".$row_rsTags['taggroupname']."</h3>\n<ul>\n";
	} ?>
	 <li>
      <label>
        <input type="checkbox" name="tag[<?php echo $row_rsTags['ID']; ?>]" id="checkbox<?php echo $row_rsTags['ID']; ?>" value="<?php echo $row_rsTags['ID']; ?>" <?php if(isset($_REQUEST['tag']) && in_array($row_rsTags['ID'], $_REQUEST['tag'])) { echo "checked=\"checked\""; } ?> />
        <?php echo $row_rsTags['tagname']; ?></label>
      </li>     
      <?php } while ($row_rsTags = mysql_fetch_assoc($result)); ?>
  <ul>
    
    <li>
      <button name="filterbutton" type="button"  class="btn btn-default btn-secondary"
 >Filter</button>
      </li>
  </ul><input name="categoryID" type="hidden" value="<?php echo isset($_GET['categoryID']) ? intval($_GET['categoryID']) : -1; ?>" />
   
 <!-- prevent Google from indexing results page -->
    <input type="hidden" name="noindex">
 
  </form></div></div>
<?php }
mysql_free_result($rsTags);
?>
