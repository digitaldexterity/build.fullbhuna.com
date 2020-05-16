<?php require_once('../../Connections/aquiescedb.php'); ?><?php
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

$currentPage = $_SERVER["PHP_SELF"];

$maxRows_rsResources = 20;
$pageNum_rsResources = 0;
if (isset($_GET['pageNum_rsResources'])) {
  $pageNum_rsResources = $_GET['pageNum_rsResources'];
}
$startRow_rsResources = $pageNum_rsResources * $maxRows_rsResources;

$varLocationID_rsResources = "0";
if (isset($_GET['locationID'])) {
  $varLocationID_rsResources = $_GET['locationID'];
}
$varCategoryID_rsResources = "0";
if (isset($_GET['categoryID'])) {
  $varCategoryID_rsResources = $_GET['categoryID'];
}

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsCategories = "SELECT ID, `description` FROM bookingcategory WHERE statusID = 1 ORDER BY `description` ASC";
$rsCategories = mysql_query($query_rsCategories, $aquiescedb) or die(mysql_error());
$row_rsCategories = mysql_fetch_assoc($rsCategories);
$totalRows_rsCategories = mysql_num_rows($rsCategories);

$queryString_rsResources = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsResources") == false && 
        stristr($param, "totalRows_rsResources") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsResources = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsResources = sprintf("&totalRows_rsResources=%d%s", $totalRows_rsResources, $queryString_rsResources);
?>
<?php
if ($totalRows_rsCategories <= 1) { // 1 or no categories so go straight to category search page
header("location: category.php?categoryID=".$row_rsCategories['ID']); 
exit; }
?><?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Bookings"; echo $pageTitle." | ".$site_name; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" --><meta name="description" content="Booking">
<meta name="keywords" content="booking"><!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" -->
     <div class="crumbs"><div>You are in: <a href="../../index.php">Home</a><span class="separator">&nbsp;&rsaquo;&nbsp;</span>Booking</div></div>
 <h1>Booking - What do you want to book?</h1>
 <?php if ($totalRows_rsCategories > 0) { // Show if recordset not empty ?>
   <table border="0" cellpadding="0" cellspacing="0" class="form-table">

       <?php do { ?>
          <tr>
            <td><a href="category.php?categoryID=<?php echo $row_rsCategories['ID']; ?>"><?php echo $row_rsCategories['description']; ?></a></td>
          </tr>
          <?php } while ($row_rsCategories = mysql_fetch_assoc($rsCategories)); ?>
        </table>
   <?php } // Show if recordset not empty ?><p>&nbsp;</p>
 </p>
    <!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsCategories);
?>
