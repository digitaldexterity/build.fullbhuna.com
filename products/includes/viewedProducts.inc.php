 <div id="productsviewed-container"><?php // replaces viewed.inc.php
if(!isset($aquiescedb)) { // ajax
	 require_once('../../Connections/aquiescedb.php'); 
}
if(!function_exists("getImageURL")) {
	require_once(SITE_ROOT.'core/includes/framework.inc.php');
}

$log_view_product = true; 

if((isset($row_rsProductPrefs['viewedproducts']) && $row_rsProductPrefs['viewedproducts']>0) || (isset($_GET['viewcount']) && intval($_GET['viewcount'])>0)) {
	$viewcount = isset($row_rsProductPrefs['viewedproducts']) ? $row_rsProductPrefs['viewedproducts'] : intval($_GET['viewcount']);																		
	if(!isset($_SESSION['productsviewed'])) {
		$_SESSION['productsviewed']['productID'] = array();
		$_SESSION['productsviewed']['imageURL'] = array();
		$_SESSION['productsviewed']['title'] = array();
		$_SESSION['productsviewed']['datetime'] = array();
		$_SESSION['productsviewed']['price'] = array();
		$_SESSION['productsviewed']['url'] = array();
	}
	if(isset($_GET['removeviewedID']) && intval($_GET['removeviewedID'])>0) {
		$key = $_GET['removeviewedID']; 
		if(isset($_SESSION['productsviewed']['productID'][$key])) {
		   unset($_SESSION['productsviewed']['productID'][$key]);
		   unset($_SESSION['productsviewed']['imageURL'][$key]);
		   unset($_SESSION['productsviewed']['title'][$key]);
		   unset($_SESSION['productsviewed']['datetime'][$key]);
		   unset($_SESSION['productsviewed']['url'][$key]);
		}
	}

	// list most recent viewed
	if(count($_SESSION['productsviewed']['productID'])>0) {
		
?>

<div class="productsviewed">
  <h2><?php echo  $row_rsProductPrefs['viewedtext'];  ?></h2>
   <?php $count = 0;  
	$start = isset($_GET['viewedstart']) ? intval($_GET['viewedstart']) : 0;
	$size = isset($row_rsProductPrefs['imagesize_viewed']) ? $row_rsProductPrefs['imagesize_viewed'] :  substr(@$_GET['size'],0,20); ?>
  <div class="viewed_nav"><?php if($start>0) { ?>&lt;&nbsp;<a class="productsviewedremove" href="javascript:void(0);" onclick="getData('/products/includes/viewedProducts.inc.php?viewcount=<?php echo $viewcount; ?>&size=<?php echo $size; ?>&viewedstart=<?php echo $start-$viewcount; ?>','productsviewed-container');">Previous</a>&nbsp;&nbsp;&nbsp;<?php } if (($start+$viewcount) < count($_SESSION['productsviewed']['title'])) { ?><a class="productsviewedremove" href="javascript:void(0);" onclick="getData('/products/includes/viewedProducts.inc.php?viewcount=<?php echo $viewcount; ?>&size=<?php echo $size; ?>&viewedstart=<?php echo $start+$viewcount; ?>','productsviewed-container');">Next</a>&nbsp;&gt;<?php } ?></div>
  <ul>
   
    <?php
	foreach($_SESSION['productsviewed']['productID'] as $key => $value) { 
	$count ++; if($count>$start) { // in range ?>
    <li><a href = "<?php echo $_SESSION['productsviewed']['url'][$key]; ?>"> <span class="productsviewedimg"> <img src="<?php echo getImageURL($_SESSION['productsviewed']['imageURL'][$key],$size); ?>" alt="<?php echo $_SESSION['productsviewed']['title'][$key]; ?>" class="<?php echo $size; ?>"></span> <span class="productsviewedtext"><span class="productsviewedname"><?php echo $_SESSION['productsviewed']['title'][$key]; ?></span> <span class="productsviewedprice"><?php echo $_SESSION['productsviewed']['price'][$key]; ?></span></span></a> <a class="productsviewedremove" href="javascript:void(0);" onclick="getData('/products/includes/viewedProducts.inc.php?viewcount=<?php echo $viewcount; ?>&size=<?php echo $size; ?>&removeviewedID=<?php echo $key; ?>','productsviewed-container');">Remove</a> </li>
    <?php } // end in range
	if(($count+$start)==$viewcount) { break; }
	} // end for each  ?>
  </ul>
</div>
<?php } // end count >0

	// add just viewed
	if(isset($log_view_product) && isset($row_rsProduct['ID'])) { 
		if(!in_array($row_rsProduct['ID'], $_SESSION['productsviewed']['productID'])) {
			array_push($_SESSION['productsviewed']['productID'],$row_rsProduct['ID']);
			array_push($_SESSION['productsviewed']['imageURL'],$row_rsProduct['imageURL']);
			array_push($_SESSION['productsviewed']['title'],$row_rsProduct['title']);
			array_push($_SESSION['productsviewed']['price'],$currency.$row_rsProduct['price']);
			array_push($_SESSION['productsviewed']['datetime'],date('Y-m-d H:i:s'));
			array_push($_SESSION['productsviewed']['url'],$_SERVER['REQUEST_URI']);
			krsort($_SESSION['productsviewed']['datetime']);
		}
	} // log product on
} // end turned on
?></div>
