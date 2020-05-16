<?php require_once('../Connections/aquiescedb.php'); ?>
<?php require_once('../core/includes/generate_tokens.inc.php'); ?>
<?php require_once('includes/productHeader.inc.php'); ?><?php require_once('includes/productFunctions.inc.php'); ?><?php require_once('../core/includes/framework.inc.php'); ?><?php require_once('includes/products.inc.php'); ?>
<?php $regionID = isset($regionID) ? $regionID : 1; ?>
<?php

if(isset($CSRFtoken)) { // if using basic token security
	if(isset($_REQUEST['productsearch'])) {
		if(!isset($_REQUEST['CSRFtoken']) || $_REQUEST['CSRFtoken'] != getToken()) {
			
			//die("Security token mismatch. If this problem persists please contact the web adminsitrator"); 
			
		}
	}
}


if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{
  if (PHP_VERSION < 6) {
    $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
  }

  $theValue = function_exists("mysql_real_escape_string") ? mysql_real_escape_string($theValue) : mysql_escape_string($theValue);

  switch ($theType) {
    case "text":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;    
    case "long":
    case "int":
      $theValue = ($theValue != "") ? intval($theValue) : "NULL";
      break;
    case "double":
      $theValue = ($theValue != "") ? doubleval($theValue) : "NULL";
      break;
    case "date":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;
    case "defined":
      $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
      break;
  }
  return $theValue;
}
}

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

$search = trim(strtolower($_REQUEST['productsearch']));
//print_r($_SESSION);die("**");
if(isset($_SESSION['fb_tracker']) && strlen($search)>0) { 
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$insert = "INSERT INTO productsearch (`searchterm`,`sessionID`,`regionID`,`createddatetime`) VALUES (".GetSQLValueString($search,"text").",".GetSQLValueString($_SESSION['fb_tracker'],"text").",".GetSQLValueString($regionID, "int").",'".date('Y-m-d H:i:s')."')";
	mysql_query($insert, $aquiescedb) or die(mysql_error());
}



$currentPage = $_SERVER["PHP_SELF"];

if(isset($_GET['pageNum_rsProduct'])) $_GET['pageNum_rsProduct'] = intval($_GET['pageNum_rsProduct']);
if(isset($_GET['totalRows_rsProduct'])) $_GET['totalRows_rsProduct'] = intval($_GET['totalRows_rsProduct']);


$maxRows_rsProduct = 100;
$pageNum_rsProduct = 0;
if (isset($_REQUEST['pageNum_rsProduct'])) {
  $pageNum_rsProduct = $_REQUEST['pageNum_rsProduct'];
}
$startRow_rsProduct = $pageNum_rsProduct * $maxRows_rsProduct;



$sql = "";
$groupby = " GROUP BY pID ";
$orderby = isset($_REQUEST['showoffers']) ? " ORDER BY product.saleitem DESC, score DESC" :  " ORDER BY score DESC"; // " ORDER BY o1,  o2";
$userID = isset($row_rsLoggedIn['ID']) ? $row_rsLoggedIn['ID']: 0;
$usertypeID = isset($_SESSION['MM_UserGroup']) ? intval($usertypeID) : 0;

$manufacturerID = isset($_REQUEST['manufacturerID']) ? intval($_REQUEST['manufacturerID']) : 0;
$categoryID = isset($_REQUEST['categoryID']) ? intval($_REQUEST['categoryID']) : 0;
$select = "SELECT productcategory.ordernum AS o1,  product.ordernum AS o2, product.ID, product.ID AS pID, product.longID, product.sku, product.title, product.vattype, product.`description`, product.price, product.listprice, product.area, product.pricetype, product.priceper, product.shippingexempt, productcategory.title AS category, productcategory.longID AS categorylongID, product.saleitem, productvatrate.ratepercent, product.imageURL, productmanufacturer.manufacturername, product.productcategoryID, productcategory.vatdefault, productcategory.vatincluded, productcategory.vatprice, usergroupmember.userID, product.h2";

$from = " FROM product LEFT JOIN productcategory ON (product.productcategoryID = productcategory.ID)  LEFT JOIN productinregion ON (productinregion.productID = product.ID) LEFT JOIN productmanufacturer ON (product.manufacturerID = productmanufacturer.ID) LEFT JOIN productincategory ON (product.ID = productincategory.productID) LEFT JOIN productcategory AS parentcategory ON (productcategory.subcatofID = parentcategory.ID)  LEFT JOIN productcategory AS altproductcategory ON (altproductcategory.ID = productincategory.categoryID) LEFT JOIN productvatrate ON (product.vattype = productvatrate.ID) LEFT JOIN usergroupmember ON productcategory.groupID = usergroupmember.groupID";

$where = " WHERE product.statusID = 1  AND ((productinregion.regionID IS NULL AND ".intval($regionID)." = 1) OR (productinregion.regionID = ".intval($regionID).")) AND (productcategory.statusID =1 OR productcategory.statusID IS NULL) AND (parentcategory.statusID IS NULL OR parentcategory.statusID=1) 
AND productcategory.accesslevel <= ".$usertypeID."
AND (productcategory.groupID =0 OR usergroupmember.userID =".$userID.")";

$where .= $manufacturerID>0 ? " AND product.manufacturerID = ".$manufacturerID : "";
$where .= $categoryID>0 ? " AND (product.productcategoryID = ".$categoryID." OR productincategory.categoryID = ".$categoryID.")"  : "";


if($row_rsProductPrefs['searchtype']==1 || (isset($_GET['searchtype']) && $_GET['searchtype']==1)) {	// old union style search
	
	$searches = explode(" ", $_REQUEST['productsearch']);
	if(empty($searches)) $searches[0] = "";
	
	foreach($searches as $key => $value) {
		
		$thiswhere = strlen(trim($value))>0  ? "  AND product.title LIKE ".GetSQLValueString("%".$value."%","text") : "";	
		
		$sql .= $sql == "" ? "(" : ") UNION ALL (";
		$sql .= $select.", 2 AS weight".$from.$where.$thiswhere.$groupby;
		
		$thiswhere = strlen(trim($value))>0  ? "  AND (product.description LIKE ".GetSQLValueString("%".$value."%","text").
		" OR productcategory.title LIKE ".GetSQLValueString("%".$value."%","text").
		"  OR altproductcategory.title LIKE ".GetSQLValueString("%".$value."%","text").
		" OR productmanufacturer.manufacturername LIKE ".GetSQLValueString($value,"text").
		" OR REPLACE(sku, ' ' ,'') LIKE ".GetSQLValueString("%".$value."%","text").
		" OR upc LIKE ".GetSQLValueString("%".$value."%","text").
		" OR mpn LIKE ".GetSQLValueString("%".$value."%","text").
		" OR isbn LIKE ".GetSQLValueString("%".$value."%","text").
		" OR product.metakeywords LIKE ".GetSQLValueString("%".$value."%","text").
		" OR product.metadescription LIKE ".GetSQLValueString("%".$value."%","text").")" : "";	
		
		$sql .= $sql == "" ? "(" : ") UNION ALL (";
		$sql .= $select.", 1 AS weight".$from.$where.$thiswhere.$groupby;
	}
	
	$sql = "SELECT p.o1,  p.o2, p.ID, p.pID, p.longID, p.sku, p.title, p.vattype, p.`description`, p.price, p.listprice,  p.area, p.pricetype, p.priceper, p.shippingexempt, p.category, p.categorylongID, p.saleitem, p.imageURL, p.manufacturername, p.productcategoryID, p.vatdefault, p.vatincluded, p.vatprice, SUM(p.weight) AS score, p.ratepercent, p.h2 FROM (".$sql."))  AS p GROUP BY pID ".$orderby;
} else { // new meta description score search
	$match = trim($_REQUEST['productsearch']) !="" ? " MATCH (product.title,product.description,product.metakeywords,product.sku) AGAINST (".GetSQLValueString($_REQUEST['productsearch'],"text")." IN NATURAL LANGUAGE MODE)" : "1";
	$sql = $select ." ,product.metadescription,  ".$match." AS `score` ". $from.$where." AND ".$match.$groupby.$orderby;	
	
}

$console = $sql;

$query_limit_rsProduct = sprintf("%s LIMIT %d, %d", $sql, $startRow_rsProduct, $maxRows_rsProduct);
$rsProduct = mysql_query($query_limit_rsProduct, $aquiescedb) or die(mysql_error().": ".$sql);
$row_rsProduct = mysql_fetch_assoc($rsProduct);

if (isset($_REQUEST['totalRows_rsProduct'])) {
  $totalRows_rsProduct = $_REQUEST['totalRows_rsProduct'];
} else {
  $all_rsProduct = mysql_query($sql);
  $totalRows_rsProduct = mysql_num_rows($all_rsProduct);
}
$totalPages_rsProduct = ceil($totalRows_rsProduct/$maxRows_rsProduct)-1;

if($totalRows_rsProduct==0 && ($row_rsProductPrefs['searchtype']!=1 && !isset($_GET['searchtype']))) {
	// if advanced search doesn't work, try simple
	$url = "search.php?searchtype=1&".$_SERVER['QUERY_STRING'];
	header("location: ".$url); exit;
}

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsSearchCategories = "SELECT ID, title FROM productcategory WHERE statusID = 1 AND (regionID = 0 OR regionID = ".$regionID.") ORDER BY title ASC";
$rsSearchCategories = mysql_query($query_rsSearchCategories, $aquiescedb) or die(mysql_error());
$row_rsSearchCategories = mysql_fetch_assoc($rsSearchCategories);
$totalRows_rsSearchCategories = mysql_num_rows($rsSearchCategories);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsSearchManufacturers = "SELECT ID, manufacturername FROM productmanufacturer WHERE statusID = 1 AND (regionID = 0 OR regionID = ".$regionID.")  ORDER BY manufacturername ASC";
$rsSearchManufacturers = mysql_query($query_rsSearchManufacturers, $aquiescedb) or die(mysql_error());
$row_rsSearchManufacturers = mysql_fetch_assoc($rsSearchManufacturers);
$totalRows_rsSearchManufacturers = mysql_num_rows($rsSearchManufacturers);





$queryString_rsProduct = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsProduct") == false && 
        stristr($param, "totalRows_rsProduct") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsProduct = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsProduct = sprintf("&totalRows_rsProduct=%d%s", $totalRows_rsProduct, $queryString_rsProduct);

$canonicalURL = htmlentities($_SERVER["REQUEST_URI"], ENT_COMPAT, "UTF-8");
?><!DOCTYPE html>
<html class="" lang="en"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Product Search"; $pageTitle .= (isset($_REQUEST['productsearch']) && $_REQUEST['productsearch']!="") ? " - ".htmlentities($_REQUEST['productsearch'], ENT_COMPAT, "UTF-8") : ""; echo $pageTitle." | ".$site_name; ?>
</title>
<!-- InstanceEndEditable -->
<!--[if IE]><![endif]-->
<?php require_once('../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<link href="/products/css/defaultProducts.css" rel="stylesheet"  />
<script src="/products/scripts/productFunctions.js"></script>
<script src="/SpryAssets/SpryValidationTextField.js"></script>
<link href="/SpryAssets/SpryValidationTextField.css" rel="stylesheet"  />
<meta name="robots" content="noindex">
<!-- InstanceEndEditable -->
</head>
<body id="OffTemplate" class="bootstrap <?php echo $body_class;  ?>">
<?php require_once('../local/includes/header.inc.php'); ?>
<main id="content"><!-- InstanceBeginEditable name="Body" -->
       <section>
    <div id="pageProductSearch" class="container">
      
      
      
      
      
       <div class="crumbs">
      <div><span class="you_are_in">You are in: </span>
      
      
      <ol itemscope itemtype="http://schema.org/BreadcrumbList">
            <li itemprop="itemListElement" itemscope
      itemtype="http://schema.org/ListItem" class="home"><a itemprop="item" href="/"><span itemprop="name">Home</span></a>
              <meta itemprop="position" content="1" />
            </li>
            
            <li itemprop="itemListElement" itemscope
      itemtype="http://schema.org/ListItem" class="productshome" ><a itemprop="item" href="/products/"><span itemprop="name"><?php echo isset($row_rsProductPrefs['shopTitle']) ? $row_rsProductPrefs['shopTitle'] : "Shop";  ?></span></a>
      <meta itemprop="position" content="2" />
      </li>
      
      
	  <li itemprop="itemListElement" itemscope
      itemtype="http://schema.org/ListItem">
	  <a itemprop="item" href="<?php echo $canonicalURL; ?>"><span itemprop="name">
	  Product Search</span></a> <meta itemprop="position" content="3" /></li>
      
        </ol>
      </div>
    </div>
    
    <form action="search.php" method="get" class="form-inline"><fieldset>
        <legend>Search Filter</legend>
        <span id="sprytextfield1">
        <label>
          <input name="productsearch" type="text" id="productsearch" value="<?php echo isset($_REQUEST['productsearch']) ? htmlentities($_REQUEST['productsearch'], ENT_COMPAT, "UTF-8") : ""; ?>"  maxlength="255"  class="form-control" />
        </label>
</span>
        <?php if ($totalRows_rsSearchCategories > 0) { // Show if recordset not empty ?>
          <select name="categoryID" id="categoryID" class="form-control">
            <option value="0" <?php if (!(strcmp(0, @$_REQUEST['categoryID']))) {echo "selected=\"selected\"";} ?>>All categories</option>
            <?php
do {  
?>
            <option value="<?php echo $row_rsSearchCategories['ID']; ?>"<?php if (!(strcmp($row_rsSearchCategories['ID'], @$_REQUEST['categoryID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsSearchCategories['title']; ?></option>
            <?php
} while ($row_rsSearchCategories = mysql_fetch_assoc($rsSearchCategories));
  $rows = mysql_num_rows($rsSearchCategories);
  if($rows > 0) {
      mysql_data_seek($rsSearchCategories, 0);
	  $row_rsSearchCategories = mysql_fetch_assoc($rsSearchCategories);
  }
?>
          </select>
          <?php } // Show if recordset not empty ?>
        <?php if ($totalRows_rsSearchManufacturers > 0) { // Show if recordset not empty ?>
          <select name="manufacturerID" id="manufacturerID"  class="form-control">
            <option value="0" <?php if (!(strcmp(0, @$_REQUEST['manufacturerID']))) {echo "selected=\"selected\"";} ?>>All manufacturers</option>
            <?php
do {  
?>
            <option value="<?php echo $row_rsSearchManufacturers['ID']?>"<?php if (!(strcmp($row_rsSearchManufacturers['ID'], @$_REQUEST['manufacturerID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsSearchManufacturers['manufacturername']?></option>
            <?php
} while ($row_rsSearchManufacturers = mysql_fetch_assoc($rsSearchManufacturers));
  $rows = mysql_num_rows($rsSearchManufacturers);
  if($rows > 0) {
      mysql_data_seek($rsSearchManufacturers, 0);
	  $row_rsSearchManufacturers = mysql_fetch_assoc($rsSearchManufacturers);
  }
?>
          </select>
          <?php } // Show if recordset not empty ?>
          <input name="CSRFtoken" type="hidden" value="<?php echo $CSRFtoken; ?>">
<button type="submit" name="go2" id="go" class="btn btn-default btn-secondary">Go</button>
<label>
  <input <?php if (!(strcmp(@$_REQUEST['showoffers'],1))) {echo "checked=\"checked\"";} ?> name="showoffers" type="checkbox" id="showoffers" value="1" />
   show offers first</label>
      </fieldset></form>
      <?php if ($totalRows_rsProduct == 0) { // Show if recordset empty ?>
        <p>No products match your search criteria.</p>
        <?php } // Show if recordset empty ?>
  <?php if ($totalRows_rsProduct > 0) { // Show if recordset not empty ?><p>Products <?php echo ($startRow_rsProduct + 1) ?> to <?php echo min($startRow_rsProduct + $maxRows_rsProduct, $totalRows_rsProduct) ?> of <?php echo $totalRows_rsProduct ?> matching your search</p>
  
  <?php if((!isset($row_rsProductPrefs['searchresults']) || $row_rsProductPrefs['searchresults']==1)) {  // list based?>
    <table  class="table table-hover">
    <tbody>
      <?php do { ?>
        <tr>
          <td class="top"><?php  if(isset($row_rsProduct['imageURL'])) { ?>
            <a href="<?php $link =  productLink($row_rsProduct['ID'], $row_rsProduct['longID'], $row_rsProduct['productcategoryID'], $row_rsProduct['categorylongID']); echo $link;  ?>"><img src="<?php echo getImageURL($row_rsProduct['imageURL'],"thumb"); ?>" alt="<?php echo $row_rsProduct['title']; ?>" class="thumb" /></a>
            <?php } ?>
           </td>
          <td class="top"><a href="<?php echo $link; ?>"><strong><?php echo $row_rsProduct['title']; ?></strong> <span class="sku"><?php echo $row_rsProduct['sku']; ?></span></a>
            <div class="description"><?php echo nl2br(strip_tags(truncate($row_rsProduct['description'], 800))); ?></div></td>
          <td class="top"><a href="<?php echo $link; ?>"><?php echo $row_rsProduct['category']; ?></a></td>
          <td class="text-nowrap  top">       <?php require('includes/price.inc.php'); ?>
  </td>
          <td class="top"><a href="<?php echo productLink($row_rsProduct['ID'], $row_rsProduct['longID'], $row_rsProduct['productcategoryID'], $row_rsProduct['categorylongID']); ?>"><?php echo $row_rsProductPrefs['moreinfotext']; ?></a></td>
          </tr>
        <?php } while ($row_rsProduct = mysql_fetch_assoc($rsProduct)); ?></tbody>
      </table>
       <?php   } else { 
	   
	   $html = ""; $query = "";
	  $productclass = ($productclass != "") ? $productclass : (defined("PRODUCT_CLASS") ? PRODUCT_CLASS : "col-md-3 col-sm-4");?>
        <div id="products-container">
        <?php 
	
	  
	
	  $html .= "<div class=\"row products\">";
		// REPLICATE ON PRODUCTS FOR NOW
		$item = 0; do { $item ++; 
			$thiscategoryID = (is_numeric($categoryID) && $categoryID>0) ? $categoryID : $row_rsProduct['productcategoryID'];
			$categorylongID = (is_string($categoryID)) ? $categoryID : $row_rsProduct['categorylongID'];
			$productLink = productLink($row_rsProduct['ID'], $row_rsProduct['longID'], $thiscategoryID,$categorylongID,"","",0,$query);
			$size = ($item == 1) ? $row_rsProductPrefs['imagesize_featured'] : $row_rsProductPrefs['imagesize_index'];
			$html .= "<!--  shopItem --><div class=\"".$productclass." shopItem item". $item;
			$html .=  ($row_rsProduct['saleitem']==1) ? " sale productsale" : ""; 
			$html .=  (isset($row_rsProduct['catinsale']) || $row_rsProduct['parentsale']==1 || $row_rsProduct['categorysale']==1) ? " sale categorysale" : ""; 
			$html .=  ($row_rsProduct['manufacturersale']==1) ? " sale manufacturersale" : ""; 
			$html .= "\" ><div itemscope itemtype=\"http://schema.org/Product\" >"; // extra div for styling wrapper and schema
			$html .= "<a class=\"productimage\" href=\"".$productLink."\"  title=\"". str_replace("&lt;br&gt;","<br>",htmlentities(productTitle($row_rsProduct), ENT_COMPAT, "UTF-8"))."\"><img src=\"";
			$html .= isset($row_rsProduct['imageURL']) ? getImageURL($row_rsProduct['imageURL'],$size) : getImageURL($row_rsProductPrefs['defaultImageURL'],$size);
		  
			$html .= "\" alt=\"". htmlentities(strip_tags($row_rsProduct['title']),ENT_COMPAT, "UTF-8")."\" class=\"".$size."\" /><span class=\"productImageOverlay\">";
			$overlayimageURL = isset($row_rsProduct['imageURL3']) ? $row_rsProduct['imageURL3'] : (isset($row_rsProductPrefs['imageOverlayURL']) ? $row_rsProductPrefs['imageOverlayURL'] : "");
			if(trim($overlayimageURL)!="") { 
				$html .= "<img src = \"/Uploads/". $overlayimageURL."\" alt=\"Overlay image\"  />";
			} 
			$html .= "</span></a>";
			$html .= "<div class=\"producttext\">";
			$html .= "<h3 itemprop=\"name\"><a href=\"".$productLink."\"  title=\"". str_replace("&lt;br&gt;","<br>",htmlentities(productTitle($row_rsProduct), ENT_COMPAT, "UTF-8"))."\">".str_replace("&lt;br&gt;","<br>",htmlentities($row_rsProduct['title'], ENT_COMPAT, "UTF-8"))."</a></h3>";
			$html .= "<div class=\"product-h2\">". $row_rsProduct['h2']."</div>";
			$html .= "<!-- product rating --><div  itemprop=\"aggregateRating\" itemscope itemtype=\"http://schema.org/AggregateRating\"  class=\"rating starrating rating".intval($row_rsProduct['avgrating'])."\">Rating: <span itemprop=\"ratingValue\">".intval($row_rsProduct['avgrating'])."</span>
			<meta itemprop=\"worstRating\" content=\"0\" />
			<meta itemprop=\"bestRating\" content=\"10\" />
			<meta itemprop=\"ratingCount\" content=\"".intval($row_rsProduct['ratingCount'])."\" /></div>";
			$html .= "<!-- product buy --><div class = \"buyproduct\"> ";
			$html .= showProductPrice($row_rsProduct['price'], $row_rsProduct); 
			$html .= "<form class=\"formMoreInfo\" method = \"post\" action=\"".$productLink."\">";
			$html .= "<button type=\"submit\"  class=\"moreinfo btn btn-default btn-secondary\">".$row_rsProductPrefs['moreinfotext']."</button>
			<input type=\"hidden\" name =\"addtobasket\" value=\"true\" />
			<input type=\"hidden\" name =\"productID\" value=\"".$row_rsProduct['ID']."\" />";
			 if(($row_rsProduct['price']>0 || $row_rsProductPrefs['nopricebuy']==1) && $row_rsProduct['instock']>0) { 
				$html .= "<button type=\"submit\" class=\"addtobasket button\" onclick=\"this.form.action='/products/basket/'; this.form.submit(); return false;\"  >".$row_rsProductPrefs['addtobasket']."</button>";
			 }
			 $html .= "</form>";
			 $html .= "</div><!-- end product buy --></div> <!-- end product text --> ";
			 $html .= "</div></div><!-- end shopItem -->";
		  } while (($row_rsProduct=mysql_fetch_assoc($rsProduct))); 
		$html .= "</div><!-- end products -->";	?>
		 <?php echo $html; ?></div>
<?php 		 } ?>
    <?php } // Show if recordset not empty ?>
      <table class="form-table">
     
        
        <tr>
          <td><?php if ($pageNum_rsProduct > 0) { // Show if not first page ?>
            <a href="<?php printf("%s?pageNum_rsProduct=%d%s", $currentPage, 0, $queryString_rsProduct); ?>">First</a>
            <?php } // Show if not first page ?></td>
          <td><?php if ($pageNum_rsProduct > 0) { // Show if not first page ?>
            <a href="<?php printf("%s?pageNum_rsProduct=%d%s", $currentPage, max(0, $pageNum_rsProduct - 1), $queryString_rsProduct); ?>">Previous</a>
            <?php } // Show if not first page ?></td>
          <td><?php if ($pageNum_rsProduct < $totalPages_rsProduct) { // Show if not last page ?>
            <a href="<?php printf("%s?pageNum_rsProduct=%d%s", $currentPage, min($totalPages_rsProduct, $pageNum_rsProduct + 1), $queryString_rsProduct); ?>">Next</a>
            <?php } // Show if not last page ?></td>
          <td><?php if ($pageNum_rsProduct < $totalPages_rsProduct) { // Show if not last page ?>
            <a href="<?php printf("%s?pageNum_rsProduct=%d%s", $currentPage, $totalPages_rsProduct, $queryString_rsProduct); ?>">Last</a>
            <?php } // Show if not last page ?></td>
        </tr>
    </table>
    <?php if(isset($_SESSION['MM_UserGroup']) && $_SESSION['MM_UserGroup']==10) { 
	echo $sql;
	} ?></div></section>
    <script>
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1", "none", {hint:"All products", isRequired:false});
    </script>
    <!-- InstanceEndEditable --></main>
<?php require_once('../local/includes/footer.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsProduct);

mysql_free_result($rsSearchCategories);

mysql_free_result($rsSearchManufacturers);

mysql_free_result($rsLoggedIn);

mysql_free_result($rsProductPrefs);

mysql_free_result($rsThisRegion);
?>
