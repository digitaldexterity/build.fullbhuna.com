<?php require_once('../../../Connections/aquiescedb.php'); ?><?php require_once('../../../core/includes/adminAccess.inc.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "8,9,10";
$MM_donotCheckaccess = "false";

// *** Restrict Access To Page: Grant or deny access to this page
function isAuthorized($strUsers, $strGroups, $UserName, $UserGroup) { 
  // For security, start by assuming the visitor is NOT authorized. 
  $isValid = False; 

  // When a visitor has logged into this site, the Session variable MM_Username set equal to their username. 
  // Therefore, we know that a user is NOT logged in if that Session variable is blank. 
  if (!empty($UserName)) { 
    // Besides being logged in, you may restrict access to only certain users based on an ID established when they login. 
    // Parse the strings into arrays. 
    $arrUsers = Explode(",", $strUsers); 
    $arrGroups = Explode(",", $strGroups); 
    if (in_array($UserName, $arrUsers)) { 
      $isValid = true; 
    } 
    // Or, you may restrict access to only certain users based on their username. 
    if (in_array($UserGroup, $arrGroups)) { 
      $isValid = true; 
    } 
    if (($strUsers == "") && false) { 
      $isValid = true; 
    } 
  } 
  return $isValid; 
}

$MM_restrictGoTo = "/login/index.php?notloggedin=true";
if (!((isset($_SESSION['MM_Username'])) && (isAuthorized("",$MM_authorizedUsers, $_SESSION['MM_Username'], $_SESSION['MM_UserGroup'])))) {   
  $MM_qsChar = "?";
  $MM_referrer = $_SERVER['PHP_SELF'];
  if (strpos($MM_restrictGoTo, "?")) $MM_qsChar = "&";
  if (isset($_SERVER['QUERY_STRING']) && strlen($_SERVER['QUERY_STRING']) > 0) 
  $MM_referrer .= "?" . $_SERVER['QUERY_STRING'];
  $MM_restrictGoTo = $MM_restrictGoTo. $MM_qsChar . "accesscheck=" . urlencode($MM_referrer);
  header("Location: ". $MM_restrictGoTo); 
  exit;
}
?>
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

$currentPage = $_SERVER["PHP_SELF"];

$maxRows_rsPromotions = 500;
$pageNum_rsPromotions = 0;
if (isset($_GET['pageNum_rsPromotions'])) {
  $pageNum_rsPromotions = $_GET['pageNum_rsPromotions'];
}
$startRow_rsPromotions = $pageNum_rsPromotions * $maxRows_rsPromotions;

$varRegionID_rsPromotions = "1";
if (isset($regionID)) {
  $varRegionID_rsPromotions = $regionID;
}
$varShowExpired_rsPromotions = "0";
if (isset($_GET['showexpired'])) {
  $varShowExpired_rsPromotions = $_GET['showexpired'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPromotions = sprintf("SELECT productpromo.*, region.title FROM productpromo LEFT JOIN region ON (productpromo.regionID = region.ID) WHERE (%s = 1 OR (productpromo.statusID = 1 AND (productpromo.enddatetime IS NULL OR productpromo.enddatetime > DATE_ADD(curdate(), INTERVAL 1 WEEK)))) AND (productpromo.regionID = 0 OR productpromo.regionID = %s) ORDER BY productpromo.ordernum ASC, productpromo.createddatetime DESC", GetSQLValueString($varShowExpired_rsPromotions, "int"),GetSQLValueString($varRegionID_rsPromotions, "int"));
$query_limit_rsPromotions = sprintf("%s LIMIT %d, %d", $query_rsPromotions, $startRow_rsPromotions, $maxRows_rsPromotions);
$rsPromotions = mysql_query($query_limit_rsPromotions, $aquiescedb) or die(mysql_error());
$row_rsPromotions = mysql_fetch_assoc($rsPromotions);

if (isset($_GET['totalRows_rsPromotions'])) {
  $totalRows_rsPromotions = $_GET['totalRows_rsPromotions'];
} else {
  $all_rsPromotions = mysql_query($query_rsPromotions);
  $totalRows_rsPromotions = mysql_num_rows($all_rsPromotions);
}
$totalPages_rsPromotions = ceil($totalRows_rsPromotions/$maxRows_rsPromotions)-1;

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsExludedCategories = "SELECT ID, title FROM productcategory WHERE (regionID = 0 OR regionID = ".intval($regionID).") AND excludepromotions = 1 ORDER BY title ASC";
$rsExludedCategories = mysql_query($query_rsExludedCategories, $aquiescedb) or die(mysql_error());
$row_rsExludedCategories = mysql_fetch_assoc($rsExludedCategories);
$totalRows_rsExludedCategories = mysql_num_rows($rsExludedCategories);

$queryString_rsPromotions = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsPromotions") == false && 
        stristr($param, "totalRows_rsPromotions") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsPromotions = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsPromotions = sprintf("&totalRows_rsPromotions=%d%s", $totalRows_rsPromotions, $queryString_rsPromotions);
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Manage Promotions"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<link href="../../css/defaultProducts.css" rel="stylesheet"  />
<?php if(isset($body_class)) $body_class .= " products ";  ?>
 <script> 
    // When the document is ready set up our sortable with it's inherant function(s) 
    $(document).ready(function() { 
	
        $(".sortable").sortable({ 
            handle : '.handle', 
            update : function () { 
            var order = $(this).sortable('serialize'); 
                $("#info").load("/core/ajax/sort.ajax.php?table=productpromo&"+order); 
            } 
        }); 
		
    }); 
</script>
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" --><div class="page products"><?php require_once('../../../core/region/includes/chooseregion.inc.php'); ?>
    <h1><i class="glyphicon glyphicon-shopping-cart"></i> Manage Promotions</h1>
   
    <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
      <li class="nav-item"><a href="../index.php" class="nav-link"><i class="glyphicon glyphicon-arrow-left"></i> Manage Shop</a></li>
      <li class="nav-item"><a href="add_promotion.php" class="nav-link"><i class="glyphicon glyphicon-plus-sign"></i> Add Promotion</a></li>
      
    </ul></div></nav><form method="get"><fieldset><label><input name="showexpired" type="checkbox" value="1" <?php if(isset($_GET['showexpired'])) echo "checked"; ?> onClick="this.form.submit()"> 
    Show deactivated promos  or those expired longer than 1 week</label></fieldset></form>
    <?php if ($totalRows_rsPromotions == 0) { // Show if recordset empty ?>
  <p>There are currently no promotions in the system.</p>
      <?php } // Show if recordset empty ?>
    <?php if ($totalRows_rsPromotions > 0) { // Show if recordset not empty ?>
  <p class="text-muted">Promotions <?php echo ($startRow_rsPromotions + 1) ?> to <?php echo min($startRow_rsPromotions + $maxRows_rsPromotions, $totalRows_rsPromotions) ?> of <?php echo $totalRows_rsPromotions ?>. <span id="info">Drag and drop handles to re-order priority</span>. Promos at the top will be processed first, so for discount groups put bigger discounts first.</p>
      <table class="table table-hover">
       <thead><tr><th>&nbsp;</th>
          <th>&nbsp;</th><th>&nbsp;</th>
          <th>ID</th> 
         
          <th>Promotion</th> <th>Group</th> 
          <th>Code</th>
          <th>Starts</th>
          <th>Ends</th>
          <th>Site</th>
          <th>Edit</th></tr></thead><tbody class="sortable">
        
        <?php do { ?>
          <tr id="listItem_<?php echo $row_rsPromotions['ID']; ?>"><td class= "handle" title="Drag and drop order of pages">&nbsp;</td>
            <td class="status<?php if ($row_rsPromotions['statusID']==1 && (!isset($row_rsPromotions['startdatetime']) || $row_rsPromotions['startdatetime'] <= date('Y-m-d H:i:s')) && (!isset($row_rsPromotions['enddatetime']) || $row_rsPromotions['enddatetime'] >= date('Y-m-d H:i:s'))) { echo 1; } else { echo 2; } ?>">&nbsp;</td><td><?php if ($row_rsPromotions['standalone']!=1) { ?><img src="../../../core/images/icons/link.png" style="vertical-align:middle" alt="This promotion can be used in conjunction with others" title="This promotion can be used in conjunction with others">
            <?php } ?></td>
              <td><?php echo $row_rsPromotions['ID']; ?></td>
             
            <td><?php echo $row_rsPromotions['promotitle']; ?></td> <td class="text-right"><?php echo ($row_rsPromotions['progressivediscountgroup']>0) ? $row_rsPromotions['progressivediscountgroup']  : ""; ?></td>
            <td><?php echo isset($row_rsPromotions['promocode']) ? $row_rsPromotions['promocode'] : "&nbsp;"; ?></td>
            <td><?php echo isset($row_rsPromotions['startdatetime']) ? date('d M Y',strtotime($row_rsPromotions['startdatetime'])) : "-"; ?></td>
            <td><?php echo isset($row_rsPromotions['enddatetime']) ? date('d M Y',strtotime($row_rsPromotions['enddatetime'])) : "-"; ?></td>
            <td><?php echo isset($row_rsPromotions['title']) ? $row_rsPromotions['title'] : "All"; ?></td>
            <td><a href="update_promotion.php?promoID=<?php echo $row_rsPromotions['ID']; ?>" class="link_edit icon_only">Edit</a></td>
         </tr>
          <?php } while ($row_rsPromotions = mysql_fetch_assoc($rsPromotions)); ?>
      </tbody></table>
      <?php } // Show if recordset not empty ?>
<br />
<table class="form-table">
  <tr>
    <td><?php if ($pageNum_rsPromotions > 0) { // Show if not first page ?>
        <a href="<?php printf("%s?pageNum_rsPromotions=%d%s", $currentPage, 0, $queryString_rsPromotions); ?>">First</a>
        <?php } // Show if not first page ?></td>
    <td><?php if ($pageNum_rsPromotions > 0) { // Show if not first page ?>
        <a href="<?php printf("%s?pageNum_rsPromotions=%d%s", $currentPage, max(0, $pageNum_rsPromotions - 1), $queryString_rsPromotions); ?>">Previous</a>
        <?php } // Show if not first page ?></td>
    <td><?php if ($pageNum_rsPromotions < $totalPages_rsPromotions) { // Show if not last page ?>
        <a href="<?php printf("%s?pageNum_rsPromotions=%d%s", $currentPage, min($totalPages_rsPromotions, $pageNum_rsPromotions + 1), $queryString_rsPromotions); ?>">Next</a>
        <?php } // Show if not last page ?></td>
    <td><?php if ($pageNum_rsPromotions < $totalPages_rsPromotions) { // Show if not last page ?>
        <a href="<?php printf("%s?pageNum_rsPromotions=%d%s", $currentPage, $totalPages_rsPromotions, $queryString_rsPromotions); ?>">Last</a>
        <?php } // Show if not last page ?></td>
  </tr>
</table>
<p>NOTE: You can exclude specific categories from all promotions in <a href="../products/categories/index.php">Manage Categories</a>.</p>
<?php if ($totalRows_rsExludedCategories > 0) { // Show if recordset not empty ?>
<h3>Excluded Categories:</h3>
  
    <ul>
    <?php do { ?>
      <li><a href="/products/admin/products/categories/update_category.php?categoryID=<?php echo $row_rsExludedCategories['ID']; ?>"><?php echo $row_rsExludedCategories['title']; ?></a></li>
      <?php } while ($row_rsExludedCategories = mysql_fetch_assoc($rsExludedCategories)); ?>
      </ul>
  <?php } // Show if recordset not empty ?></div>
      <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsPromotions);

mysql_free_result($rsExludedCategories);
?>
