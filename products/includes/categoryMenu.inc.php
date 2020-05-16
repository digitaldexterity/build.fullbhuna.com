<!-- ISEARCH_END_INDEX --><?php 
	if(is_readable(SITE_ROOT."local/includes/categoryMenu.inc.php")) {
	require(SITE_ROOT."local/includes/categoryMenu.inc.php");
} else { ?><div id = "categoryMenu">
<?php if(!isset($categoryID)) {
	$categoryID = isset($row_rsThisCategory['ID']) ? $row_rsThisCategory['ID'] : (isset($_GET['categoryID']) ? $_GET['categoryID'] : -1);
mysql_select_db($database_aquiescedb, $aquiescedb);
}



$regionID = (isset($regionID) && intval($regionID)>0) ? intval($regionID) : 1;

// get this category details

$select = "SELECT ID, subcatofID FROM productcategory WHERE productcategory.statusID = 1 AND ID = " .intval($categoryID)." OR longID = ".GetSQLValueString($categoryID, "text")." LIMIT 1";//die($select);
$categories = mysql_query($select, $aquiescedb) or die(mysql_error().$select);
if(mysql_num_rows($categories)>0) {
	$thisCategory = mysql_fetch_assoc($categories);
} else {
	$thisCategory['ID'] = 0;
	$thisCategory['subcatofID'] = 0;
}



$thismanufacturerID = isset($row_rsThisManufacturer['ID']) ? $row_rsThisManufacturer['ID'] : (isset($_REQUEST['manufacturerID']) ? intval($_REQUEST['manufacturerID']) : -1);
	$where = (isset($row_rsProductPrefs['manufacturershowsubs'])  && $row_rsProductPrefs['manufacturershowsubs']) ? "" : " AND productmanufacturer.subsidiaryofID IS NULL ";
	$select = "SELECT productmanufacturer.ID, productmanufacturer.manufacturername, productmanufacturer.ordernum FROM productmanufacturer
	LEFT JOIN  product ON (productmanufacturer.ID = product.manufacturerID) 
LEFT JOIN productinregion ON (productinregion.productID = product.ID) 
LEFT JOIN productcategory ON (product.productcategoryID = productcategory.ID)
LEFT JOIN productcategory AS parentcategory ON (productcategory.subcatofID = parentcategory.ID  )  
WHERE (productinregion.regionID = 0 OR productinregion.regionID = ".$regionID.") AND (productmanufacturer.ID = ".intval($thismanufacturerID)." OR ".$thisCategory['ID']." = parentcategory.ID OR productcategory.ID = ".$thisCategory['ID'].") AND product.statusID =1 GROUP BY productmanufacturer.ID ORDER BY productmanufacturer.ordernum, productmanufacturer.manufacturername ASC";

	$manufacturers = mysql_query($select, $aquiescedb) or die(mysql_error().$select);
	
	
	
	


$select = "SELECT producttag.ID, producttag.tagname, producttaggroup.taggroupname, producttag.taggroupID FROM producttag LEFT JOIN producttaggroup ON (producttag.taggroupID = producttaggroup.ID) LEFT JOIN producttagged ON (producttag.ID = producttagged.tagID)  LEFT JOIN product ON (producttagged.productID = product.ID) LEFT JOIN productcategory ON (product.productcategoryID = productcategory.ID) LEFT JOIN productinregion ON (productinregion.productID = product.ID)  LEFT JOIN productcategory AS parent ON (productcategory.subcatofID = parent.ID) LEFT JOIN productincategory ON (product.ID = productincategory.productID) LEFT JOIN productcategory AS  suppcat ON (productincategory.categoryID = suppcat.ID)  WHERE (".$thisCategory['ID']." < 1 OR productincategory.categoryID = ".$thisCategory['ID']." OR product.productcategoryID = ".$thisCategory['ID']." OR parent.ID = ".$thisCategory['ID']." OR suppcat.subcatofID = ".$thisCategory['ID'].")  AND product.statusID = 1  AND  productinregion.regionID = ".intval($regionID)."  AND producttaggroup.regionID = ".intval($regionID)." GROUP BY producttag.ID ORDER BY producttaggroup.ordernum, producttag.ordernum, producttaggroup.ID "; 
$tags = mysql_query($select, $aquiescedb) or die(mysql_error().$select);





$select = "SELECT productfinish.ID, productfinish.finishname FROM productfinish LEFT JOIN productwithfinish ON (productfinish.ID = productwithfinish.finishID)  LEFT JOIN product ON (productwithfinish.productID = product.ID) LEFT JOIN productcategory ON (product.productcategoryID = productcategory.ID) LEFT JOIN productcategory AS parent ON (productcategory.subcatofID = parent.ID) LEFT JOIN productincategory ON (product.productcategoryID = productincategory.categoryID) LEFT JOIN productinregion ON (productinregion.productID = product.ID) WHERE (".$thisCategory['ID']." < 1 OR productincategory.categoryID = ".$thisCategory['ID']." OR product.productcategoryID = ".$thisCategory['ID']." OR parent.ID = ".$thisCategory['ID'].")  AND product.statusID = 1 AND  ((productinregion.regionID IS NULL AND ".intval($regionID)." = 1) OR productinregion.regionID = ".intval($regionID).") GROUP BY productfinish.ID ORDER BY productfinish.ordernum"; 
$finishes = mysql_query($select, $aquiescedb) or die(mysql_error().$select);


$select = "SELECT productversion.ID, productversion.versionname FROM productversion 
LEFT JOIN productwithversion ON (productversion.ID = productwithversion.versionID)  
LEFT JOIN product ON (productwithversion.productID = product.ID) 
LEFT JOIN productcategory ON (product.productcategoryID = productcategory.ID) 
LEFT JOIN productcategory AS parent ON (productcategory.subcatofID = parent.ID) 
LEFT JOIN productincategory ON (product.productcategoryID = productincategory.categoryID) 
WHERE (".$thisCategory['ID']." < 1 OR productincategory.categoryID = ".$thisCategory['ID']." OR productcategory.ID = ".$thisCategory['ID']." OR parent.ID = ".$thisCategory['ID'].")  AND product.statusID = 1 GROUP BY productversion.ID ORDER BY productversion.ordernum"; 
$versions = mysql_query($select, $aquiescedb) or die(mysql_error().$select);



// get related categories

$select = "SELECT productcategory.* FROM productcategory  WHERE productcategory.statusID = 1 AND productcategory.regionID = ".$regionID." AND (ID = ".$thisCategory['ID']." OR (".$thisCategory['subcatofID']." >0 AND subcatofID = ".$thisCategory['subcatofID'].") OR subcatofID = ".$thisCategory['ID'].") ORDER BY subcatofID";
$catresult = mysql_query($select, $aquiescedb) or die(mysql_error().$select);



?><style><!--
/* don't display when there's only one choice - except category */

.rows-1 {
	display:none; 
	
	
}

.group-cat.rows-1 {
	display:block;
}
--></style>

  <form id="form1" name="form1" method="get" action="/products/">
  
   <?php $rows = mysql_num_rows($catresult); if($rows>0) { 
   $closed = (isset($_COOKIE['tag-groupgroup-cat']) && $_COOKIE['tag-groupgroup-cat'] =='true') ? "closed" : "";
    echo "<div class=\"tag-group group-cat rows-".$rows."\"><h3 class=\"cat-toggle ". $closed."\">Category</h3><ul>"; 
   while($cat = mysql_fetch_assoc($catresult)) { ?><li>
   <input type="radio" name="categoryID" id="radio-cat-<?php echo $cat['ID']; ?>" value="<?php echo $cat['ID']; ?>" <?php if(isset($thisCategory['ID']) && $cat['ID'] == $thisCategory['ID']) { echo "checked=\"checked\""; } ?>  onchange="this.form.submit()" />
		  
		  <label class="checkbox" for="radio-cat-<?php echo $cat['ID']; ?>"><?php echo $cat['title']; ?></label></li>
	 
   <?php }
   echo "</ul></div>";
   } ?>
   
   
   <?php $rows =  mysql_num_rows($versions); if($rows>0) { 
	 $closed = (isset($_COOKIE['tag-groupgroup-version']) && $_COOKIE['tag-groupgroup-version']=='true') ? "closed" : "";
  echo "<div class=\"tag-group group-version rows-".$rows."\"><h3  class=\"cat-toggle ".$closed."\">Thickness</h3><ul>"; 
	
		while($version = mysql_fetch_assoc($versions)) { ?><li>
          <input type="checkbox" name="version[<?php echo $version['ID']; ?>]" id="checkbox-ver-<?php echo $version['ID']; ?>" value="<?php echo $version['ID']; ?>" <?php if(isset($_REQUEST['version']) && ((is_array( $_REQUEST['version']) && in_array($version['ID'], $_REQUEST['version'])) ||  $_REQUEST['version']==$version['ID'])) { echo "checked=\"checked\""; } ?>  onclick="this.form.submit()" />
		  
		  <label class="checkbox" for="checkbox-ver-<?php echo $version['ID']; ?>"><?php echo $version['versionname']; ?></label></li>
        <?php 
			
		} 
		echo "</ul></div>";
	}  ?>
    
    
 
 
    <?php if(mysql_num_rows($tags)>0) { 
	$taggroup = "0"; while($tag = mysql_fetch_assoc($tags)) { 
	if($tag['taggroupname'] != $taggroup) {
		echo $taggroup == "0" ? "" : "</ul></div>\n";$taggroup = $tag['taggroupname'];
		$closed = (isset($_COOKIE['tag-groupgroup'.$tag['taggroupID']]) && $_COOKIE['tag-groupgroup'.$tag['taggroupID']]=='true') ? "closed" : "";
		echo "<div class=\"tag-group group".$tag['taggroupID']."\"><h3 class=\"cat-toggle ".$closed ."\">".$tag['taggroupname']."</h3>\n<ul>\n";
	} ?>
	 <li>
      
        <input type="checkbox" name="tagID[<?php echo $tag['ID']; ?>]" id="checkbox-tag-<?php echo $tag['ID']; ?>" value="<?php echo $tag['ID']; ?>" <?php if(isset($_REQUEST['tagID']) && ((is_array( $_REQUEST['tagID']) && in_array($tag['ID'], $_REQUEST['tagID'])) || $_REQUEST['tagID']== $tag['ID'])) { echo "checked=\"checked\""; } ?> onclick="this.form.submit()" />
        <label class="checkbox" for="checkbox-tag-<?php echo $tag['ID']; ?>"><?php echo $tag['tagname']; ?></label>
      </li>     
      <?php }   
	  
	  echo "</ul></div>";
	   } 
	   
	   ?>
       
       
       
       <?php $rows =  mysql_num_rows($finishes); if($rows>0) { 
	$closed = (isset($_COOKIE['tag-groupgroup-finish']) && $_COOKIE['tag-groupgroup-finish']=='true') ? "closed" : "";
  echo "<div class=\"tag-group group-finish rows-".$rows."\"><h3  class=\"cat-toggle ".$closed ."\">Colour</h3><ul>"; 
	
		while($finish = mysql_fetch_assoc($finishes)) { ?><li>
          <input type="checkbox" name="finish[<?php echo $finish['ID']; ?>]" id="checkbox-fin-<?php echo $finish['ID']; ?>" value="<?php echo $finish['ID']; ?>" <?php if(isset($_REQUEST['finish']) && ((is_array( $_REQUEST['finish']) && in_array($finish['ID'], $_REQUEST['finish'])) || $_REQUEST['finish']==$finish['ID']) ) { echo "checked=\"checked\""; } ?>  onclick="this.form.submit()" />
		  
		  <label class="checkbox" for="checkbox-fin-<?php echo $finish['ID']; ?>"><?php echo $finish['finishname']; ?></label></li>
        <?php 
			
		} 
		echo "</ul></div>";
	}  ?>
    
    
 
 <?php  
	
	
	$rows = mysql_num_rows($manufacturers); if($rows>1) { 
 $closed = (isset($_COOKIE['tag-groupgroup-brand']) && $_COOKIE['tag-groupgroup-brand']=='true') ? "closed" : "";
  echo "<div class=\"tag-group  group-brand  rows-".$rows."\"><h3  class=\"cat-toggle ".$closed."\">Brand</h3><ul>"; 
	
		while($manufacturer = mysql_fetch_assoc($manufacturers)) { ?><li>
          <input type="checkbox" name="manufacturerID[<?php echo $manufacturer['ID']; ?>]" id="checkbox-man-<?php echo $manufacturer['ID']; ?>" value="<?php echo $manufacturer['ID']; ?>" <?php if(isset($_REQUEST['manufacturerID']) && ((is_array( $_REQUEST['manufacturerID']) && in_array($manufacturer['ID'], $_REQUEST['manufacturerID'])) || $_REQUEST['manufacturerID']==$manufacturer['ID'])) { echo "checked=\"checked\""; } ?>  onclick="this.form.submit()" />
		  
		  <label class="checkbox" for="checkbox-man-<?php echo $manufacturer['ID']; ?>"><?php echo $manufacturer['manufacturername']; ?></label></li>
        <?php 
			
		} 
		echo "</ul></div>";
	} ?>
    
    
    
    
     
 <!-- prevent Google from indexing results page -->
    <input type="hidden" name="noindex">
 
     
 
 
    
 
  </form>

</div>
<?php 
if(isset($tags) && is_resource($tags)) {

mysql_free_result($tags);
}

}  ?>
<!-- ISEARCH_BEGIN_INDEX -->

