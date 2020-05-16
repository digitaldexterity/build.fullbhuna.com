<?php require_once('../Connections/aquiescedb.php'); ?>
<?php  require_once('../core/includes/framework.inc.php'); ?><?php require_once('includes/productHeader.inc.php'); ?>

<?php

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

if(isset($_GET['pageNum_rsReviews'])) $_GET['pageNum_rsReviews'] = intval($_GET['pageNum_rsReviews']);
if(isset($_GET['totalRows_rsReviews'])) $_GET['totalRows_rsReviews'] = intval($_GET['totalRows_rsReviews']);


$maxRows_rsReviews = 12;
$pageNum_rsReviews = 0;
if (isset($_GET['pageNum_rsReviews'])) {
  $pageNum_rsReviews = $_GET['pageNum_rsReviews'];
}
$startRow_rsReviews = $pageNum_rsReviews * $maxRows_rsReviews;

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsReviews = "SELECT  comments.ID, comments.message, comments.firstname, comments.surname, comments.posteddatetime, comments.rating, comments.productID, comments.productlongID, comments.sectionID, comments.imageURL, comments.sectionlongID,comments.title  FROM (SELECT forumtopic.ID, forumtopic.message, firstname, surname, posteddatetime,  forumtopic.rating, product.ID AS productID, product.longID AS  productlongID,product.productcategoryID AS sectionID,product.imageURL, productcategory.longID AS sectionlongID, product.title FROM forumtopic LEFT JOIN users ON (forumtopic.postedbyID = users.ID) LEFT JOIN product ON (forumtopic.productID = product.ID) LEFT JOIN productcategory ON (product.productcategoryID = productcategory.ID) WHERE forumtopic .statusID = 1  AND product.ID IS NOT NULL UNION SELECT forumcomment.ID, forumcomment.message, firstname, surname, forumcomment.posteddatetime, forumcomment.rating,  product.ID AS productID, product.longID AS productlongID,product.productcategoryID AS sectionID,product.imageURL,productcategory.longID AS sectionlongID,product.title  FROM forumcomment LEFT JOIN users ON (forumcomment.postedbyID = users.ID) LEFT JOIN forumtopic ON (forumcomment.topicID = forumtopic.ID) LEFT JOIN product ON (forumtopic.productID = product.ID) LEFT JOIN productcategory ON (product.productcategoryID = productcategory.ID) WHERE forumcomment.statusID = 1  AND product.ID IS NOT NULL) AS comments ORDER BY posteddatetime DESC";
$query_limit_rsReviews = sprintf("%s LIMIT %d, %d", $query_rsReviews, $startRow_rsReviews, $maxRows_rsReviews);
$rsReviews = mysql_query($query_limit_rsReviews, $aquiescedb) or die(mysql_error());
$row_rsReviews = mysql_fetch_assoc($rsReviews);

if (isset($_GET['totalRows_rsReviews'])) {
  $totalRows_rsReviews = $_GET['totalRows_rsReviews'];
} else {
  $all_rsReviews = mysql_query($query_rsReviews);
  $totalRows_rsReviews = mysql_num_rows($all_rsReviews);
}
$totalPages_rsReviews = ceil($totalRows_rsReviews/$maxRows_rsReviews)-1;

$currentPage = $_SERVER["PHP_SELF"];

$queryString_rsReviews = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsReviews") == false && 
        stristr($param, "totalRows_rsReviews") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsReviews = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsReviews = sprintf("&totalRows_rsReviews=%d%s", $totalRows_rsReviews, $queryString_rsReviews);

$canonicalURL = htmlentities($_SERVER["REQUEST_URI"], ENT_COMPAT, "UTF-8");

?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!DOCTYPE html>
<html class="" lang="en"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Customer Product Reviews"; echo $pageTitle." | ".$site_name; ?>
</title>
<!--[if IE]><![endif]-->
<!-- InstanceEndEditable -->
<!--[if IE]><![endif]-->
<?php require_once('../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<style>
<!--

.padding {
	padding:10px;
}

.productimage {
	float:left;
	padding: 0 10px 30px 0;
}
-->
</style>
<!-- InstanceEndEditable -->
</head>
<body id="OffTemplate" class="bootstrap <?php echo $body_class;  ?>">
<?php require_once('../local/includes/header.inc.php'); ?>
<main id="content"><!-- InstanceBeginEditable name="Body" --><section>
      <div class="container pageBody reviews">
      
      
      
      
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
	  Product Reviews</span></a> <meta itemprop="position" content="3" /></li>
      
        </ol>
      </div>
    </div>
    
    
    
          <h1>Product Reviews</h1>
          <p>A selection of our customer product reviews. Want to write a review? Add your message at the foot of the product page.</p>
        
        <?php do { $link = productLink($row_rsReviews['productID'], $row_rsReviews['productlongID'], $row_rsReviews['sectionID'], $row_rsReviews['sectionlongID']); ?><div class="review clearfix"><p><strong><?php echo htmlentities(anonymiser($row_rsReviews['firstname'], $row_rsReviews['surname']), ENT_COMPAT, "UTF-8"); 
	echo (isset($row_rsReviews['locationname']) && trim($row_rsReviews['locationname'])!="") ? ", ".htmlentities($row_rsReviews['locationname'], ENT_COMPAT, "UTF-8") : ""; ?></strong> reviewed:</p><a href="<?php echo $link; ?>"><img src="<?php echo getImageURL($row_rsReviews['imageURL'],$row_rsProductPrefs['imagesize_productthumbs']); ?>" alt="<?php echo $row_rsReviews['title']; ?>" class="productimage <?php echo $row_rsProductPrefs['imagesize_productthumbs']; ?>" /><?php echo $row_rsReviews['title']; ?>:<br />
    <img src="/core/images/ratings/star_ratings_<?php $rating = (isset($row_rsReviews['rating']) && $row_rsReviews['rating']!="") ? $row_rsReviews['rating'] : 0; echo $rating; ?>.png" width="80" height="16" alt="Rating: <?php echo $row_rsReviews['rating']; ?> stars" /><br /><em>&#8220;<?php echo substr($row_rsReviews['message'],0,150); ?>...&#8221;</em><br />
    View product and reviews...</a></div>
            
            <?php } while ($row_rsReviews = mysql_fetch_assoc($rsReviews)); ?>
     
            
        
        <table class="form-table">
          <tr>
            <td><?php if ($pageNum_rsReviews > 0) { // Show if not first page ?>
                <a href="<?php printf("%s?pageNum_rsReviews=%d%s", $currentPage, 0, $queryString_rsReviews); ?>">First</a>
                <?php } // Show if not first page ?></td>
            <td><?php if ($pageNum_rsReviews > 0) { // Show if not first page ?>
                <a href="<?php printf("%s?pageNum_rsReviews=%d%s", $currentPage, max(0, $pageNum_rsReviews - 1), $queryString_rsReviews); ?>">Previous</a>
                <?php } // Show if not first page ?></td>
            <td><?php if ($pageNum_rsReviews < $totalPages_rsReviews) { // Show if not last page ?>
                <a href="<?php printf("%s?pageNum_rsReviews=%d%s", $currentPage, min($totalPages_rsReviews, $pageNum_rsReviews + 1), $queryString_rsReviews); ?>">Next</a>
                <?php } // Show if not last page ?></td>
            <td><?php if ($pageNum_rsReviews < $totalPages_rsReviews) { // Show if not last page ?>
                <a href="<?php printf("%s?pageNum_rsReviews=%d%s", $currentPage, $totalPages_rsReviews, $queryString_rsReviews); ?>">Last</a>
                <?php } // Show if not last page ?></td>
          </tr>
        </table></div></section>
    <!-- InstanceEndEditable --></main>
<?php require_once('../local/includes/footer.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php

mysql_free_result($rsReviews);

mysql_free_result($rsSaleEnds);
?>