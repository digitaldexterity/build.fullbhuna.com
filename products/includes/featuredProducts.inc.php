 <?php 
 
 global $row_rsProductPrefs, $regionID;
 
 if($row_rsProductPrefs['featuredproducts']>0) {   
 // requires productHeader.inc.php and framework.inc.php
 
 
?>


  <div class="featuredproducts">
    <h2><?php echo  $row_rsProductPrefs['featuredtext'];  ?></h2>
   <?php  echo getProducts($regionID,"","","",$row_rsProductPrefs['featuredproducts']); ?>
  </div>
  
<?php  }?>