<?php require_once('categoryMenu.inc.php'); ?>

<!-- product main -->
<div class="product-main" itemscope itemtype="http://schema.org/Product">
<?php if(is_readable(SITE_ROOT.'local/includes/productHeader.inc.php')) { 
	require_once(SITE_ROOT.'local/includes/productHeader.inc.php'); } ?>
<?php require_once('producttitle.inc.php'); ?>
<!-- product detail-->
<div id="productDetails" >
<?php if($row_rsProductPrefs['buyposition']==1) { ?>
<!-- product buy-->
<div class = "buyproduct">
  <?php require('price.inc.php'); 
	   
		   require('addtobasket.inc.php'); 
		    ?>
 
</div>
<!-- end product buy -->
<?php } ?>
<?php require('productphotos.inc.php'); ?>
<!--  product text -->
<div id="productText" >
  <?php require_once('producttabs.inc.php'); ?>
    
    <!--finishes -->
    
    <?php if ($totalRows_rsFinishes > 0) { // Show if recordset not empty ?>
      <div id="productFinishes">
        <ul>
          <?php do { ?>
            <li><img src="<?php echo getImageURL($row_rsFinishes['imageURL']); ?>" alt="Finish" /> <?php echo $row_rsFinishes['finishname']; ?> </li>
            <?php } while ($row_rsFinishes = mysql_fetch_assoc($rsFinishes)); ?>
        </ul>
      </div>
      <?php } // Show if recordset not empty ?>
    
    <!-- end finishes--> 
    
    <!-- append HTML --> 
    <?php echo isset($row_rsThisCategory['appendProductDescription'] ) ? $row_rsThisCategory['appendProductDescription'] : ""; ?> 
    
    <!-- optional include -->
    <?php if(is_readable(SITE_ROOT."local/includes/product.inc.php")) {
		require_once(SITE_ROOT."local/includes/product.inc.php");
	} ?>
    
    
    <?php if($row_rsProductPrefs['buyposition']==2) { ?>
    <!-- product buy-->
    <div class = "buyproduct">
      <?php require_once('price.inc.php'); 
	   
		   require_once('addtobasket.inc.php'); 
		   ?>
    </div>
    <!-- end product buy -->
    <?php } ?>
    
    
    <?php if($row_rsProductPrefs['allowsharing']==1) { require_once(SITE_ROOT.'core/share/includes/share.inc.php'); }  ?>
  </div><!-- end product text -->
</div><!-- end product detail -->
<?php if($row_rsProductPrefs['reviewstab']==0) { ?>
<?php require('review.inc.php'); ?>
<?php } ?>
</div>
<!-- end product main --> 

<!-- ISEARCH_END_INDEX --> 
<!-- promo-->
<?php if(isset($row_rsPromo['imageURL'])) { 
 $promoLink = isset($row_rsPromo['linkURL']) ? $row_rsPromo['linkURL'] : "javascript:void(0);"; ?>
<div id="productPromo"><a href="<?php echo $promoLink; ?>" title="<?php echo $row_rsPromo['promotitle']; ?>"><img src="/Uploads/<?php echo $row_rsPromo['imageURL']; ?>" alt="<?php echo $row_rsPromo['promotitle']; ?>"  /></a> </div>
<?php } ?>
<!-- end promo --> 

<!-- related products -->
<?php require_once('alsoBought.inc.php'); ?>
<?php require_once('relatedProducts.inc.php'); ?>
<?php require_once('viewedProducts.inc.php'); ?>
<!-- ISEARCH_BEGIN_INDEX --> 
<!-- end related products -->


