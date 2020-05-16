<?php global $row_rsProductPrefs, $row_rsProduct, $row_rsThisCategory, $rsProductTags, $row_rsProductTags, $catLink;

$varProductID_rsOtherCategories = "-1";
if (isset($row_rsProduct['ID'])) {
  $varProductID_rsOtherCategories = $row_rsProduct['ID'];
}
$varMainCategoryID_rsOtherCategories = "-1";
if (isset($row_rsThisCategory['ID'])) {
  $varMainCategoryID_rsOtherCategories = $row_rsThisCategory['ID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsOtherCategories = sprintf("SELECT productcategory.ID, productcategory.longID, productcategory.title, categorysale FROM productcategory LEFT JOIN productincategory ON (productcategory.ID = productincategory.categoryID) WHERE productincategory.productID = %s AND productcategory.ID != %s", GetSQLValueString($varProductID_rsOtherCategories, "int"),GetSQLValueString($varMainCategoryID_rsOtherCategories, "int"));
$rsOtherCategories = mysql_query($query_rsOtherCategories, $aquiescedb) or die(mysql_error());
$row_rsOtherCategories = mysql_fetch_assoc($rsOtherCategories);
$totalRows_rsOtherCategories = mysql_num_rows($rsOtherCategories);


 ?>
<div class="productID"><span class="label">Product ID: </span><?php echo $row_rsProduct['ID']; ?></div>
<?php if(isset($row_rsProduct['sku'])) { ?>
<div class="sku"><span class="label">Stock code: </span><?php echo $row_rsProduct['sku']; ?></div>
<?php } if(isset($row_rsProduct['upc'])) {?>
<div class="upc"><span class="label">Part code: </span><span itemprop="gtin<?php echo strlen($row_rsProduct['upc']); ?>"><?php echo $row_rsProduct['upc']; ?></span></div>
<?php } if(isset($row_rsProduct['mpn'])) {?>
<div class="mpn"><span class="label">Part number: </span><span itemprop="mpn"><?php echo $row_rsProduct['mpn']; ?></span></div>
<?php }  if(isset($row_rsProduct['isbn'])) {?>
<div class="isbn"><span class="label"><?php echo $row_rsProductPrefs['text_custom_isbn_field']; ?>: </span><?php echo $row_rsProduct['isbn']; ?></div>
<?php } ?>
<div class="itemCondition"><span class="label">Condition: </span><span itemprop="itemCondition" ><?php echo ($row_rsProduct['condition'])==0 ? "New" : "Used"; ?></span></div>
<?php if(isset($row_rsProduct['manufacturername'])) { ?>
<div class="manufacturername"><span class="label" itemprop="brand" >Brand: </span><?php echo  isset($row_rsProduct['parentmanufacturername']) ? htmlentities($row_rsProduct['parentmanufacturername'] ." - ".$row_rsProduct['manufacturername'], ENT_COMPAT, "UTF-8") : htmlentities($row_rsProduct['manufacturername'], ENT_COMPAT, "UTF-8"); ?></div>
<?php } ?>
<?php if (isset($row_rsThisCategory['title'])) { ?>
  <div class="data-categories"><span class="label">Categories: </span>
    <?php if (isset($row_rsThisCategory['parenttitle'])) { ?>
      <a href="<?php echo productLink(0, "", $row_rsThisCategory['parentID'], $row_rsThisCategory['parentlongID']); ?>"><?php echo $row_rsThisCategory['parenttitle']; ?></a>&nbsp;&nbsp;
      <?php } ?>
    <a href="<?php echo $catLink; ?>" title="<?php echo $row_rsThisCategory['title']; ?>"><?php echo $row_rsThisCategory['title']; ?></a>&nbsp;&nbsp;
    <?php if($totalRows_rsOtherCategories>0) {  while($row_rsOtherCategories = mysql_fetch_assoc($rsOtherCategories)) {
	echo  "<a href = \"".productLink(0,"",$row_rsOtherCategories['ID'],$row_rsOtherCategories['longID'])."\" title =\"".htmlentities($row_rsOtherCategories['title'], ENT_COMPAT, "UTF-8")."\">".htmlentities($row_rsOtherCategories['title'], ENT_COMPAT, "UTF-8")."</a>&nbsp;&nbsp; "; } }?>
  </div>
  <?php } ?>
<?php if($row_rsProduct['area']>0) { ?>
<div class="area"><span class="label">Area covered: </span><span class="value"><?php echo $row_rsProduct['area']; ?></span><span class="units">m<sup>2</sup></span></div>
<?php } ?>
<?php if($row_rsProduct['weight']>0) { ?>
<div class="dimensions"><span class="label">Weight: </span><?php echo $row_rsProduct['weight']; ?><span class="units">kgs</span></div>
<?php } ?>
<?php if(isset($row_rsProduct['box_height']) && $row_rsProduct['box_height']>0 ) { ?>
<div class="dimensions"> <span class="label">Dimensions: </span><?php echo $row_rsProduct['box_height']; ?><span class="units">cm</span> x <?php echo $row_rsProduct['box_length']; ?><span class="units">cm</span><?php echo (isset($row_rsProduct['box_width']) && $row_rsProduct['box_width']>0) ? " x ".$row_rsProduct['box_width']."<span class=\"units\">cm</span>" : ""; ?></div>
<?php } ?>
<?php $taggroupname = "-1"; if(mysql_num_rows($rsProductTags)>0) {  do { 
	 if($row_rsProductTags['taggroupname']!=$taggroupname) { echo $taggroupname != "-1" ? "</div>" : ""; ?>
<div class="taggroup taggroup<?php echo $row_rsProductTags['taggroupID']; ?>"><span class="label"><?php echo htmlentities($row_rsProductTags['taggroupname'], ENT_COMPAT, "UTF-8"); ?>: </span>
  <?php } // end if new group
	  echo "<a href = \"/products/index.php?categoryID=-1&tagID=".$row_rsProductTags['ID']."\">".htmlentities($row_rsProductTags['tagname'], ENT_COMPAT, "UTF-8")."</a>&nbsp;&nbsp; ";  
	  if($row_rsProductTags['taggroupname']!=$taggroupname) {  
	  $taggroupname = $row_rsProductTags['taggroupname']; ?>
  <?php } // end if ?>
  <?php } while ($row_rsProductTags = mysql_fetch_assoc($rsProductTags)); ?>
</div>
<?php  } ?>
<?php mysql_free_result($rsOtherCategories); ?>
