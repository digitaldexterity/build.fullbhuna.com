<?php 
global $row_rsProductPrefs, $row_rsProduct, $productTitle;

if ($row_rsProductPrefs['producth1category']==1) { ?>
    <h2 class="productcategorytitle productsummary"><?php echo isset($row_rsProduct['h2']) ? $row_rsProduct['h2']  :   $row_rsThisCategory['title']; ?></h2>    
    <?php }  ?>
    <h1 class="producttitle product<?php echo $row_rsProduct['ID']; ?>"><span itemprop="sku" class="sku" ><?php echo $row_rsProduct['sku']; ?> </span><span itemprop="name" ><?php echo str_replace("&lt;br&gt;","<br>",htmlentities($productTitle , ENT_COMPAT, "UTF-8")); ?></span></h1>