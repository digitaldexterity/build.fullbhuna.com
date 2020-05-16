<?php require_once('../../../../Connections/aquiescedb.php'); ?><?php require_once('../../../../core/includes/adminAccess.inc.php'); ?>
<?php require_once('../../inc/product_functions.inc.php'); ?><?php require_once('../../../includes/productFunctions.inc.php'); ?><?php require_once('../../../../core/includes/framework.inc.php'); ?>
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

$MM_restrictGoTo = "../../../../login/index.php?notloggedin=true";
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

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form2")) {
  $updateSQL = sprintf("UPDATE productprefs SET sitemap=%s WHERE ID=%s",
                       GetSQLValueString(isset($_POST['sitemap']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
}

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsRegions = "SELECT region.ID, region.title FROM region WHERE region.statusID = 1 ORDER BY region.title";
$rsRegions = mysql_query($query_rsRegions, $aquiescedb) or die(mysql_error());
$row_rsRegions = mysql_fetch_assoc($rsRegions);
$totalRows_rsRegions = mysql_num_rows($rsRegions);

$varRegionID_rsProductPrefs = "1";
if (isset($regionID)) {
  $varRegionID_rsProductPrefs = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsProductPrefs = sprintf("SELECT * FROM productprefs WHERE ID = %s", GetSQLValueString($varRegionID_rsProductPrefs, "int"));
$rsProductPrefs = mysql_query($query_rsProductPrefs, $aquiescedb) or die(mysql_error());
$row_rsProductPrefs = mysql_fetch_assoc($rsProductPrefs);
$totalRows_rsProductPrefs = mysql_num_rows($rsProductPrefs);

// delete category

if(isset($_GET['deleteID']) && $_GET['deleteID']>0) {
	$delete = "DELETE FROM productcategory WHERE ID = ".GetSQLValueString($_GET['deleteID'], "int");
	$result = mysql_query($delete, $aquiescedb) or die(mysql_error());
}




$currentPage = $_SERVER["PHP_SELF"];

if(function_exists("saveProductMenu")) { saveProductMenu($regionID); }






?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Manage Product Categories"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script src="/core/scripts/checkbox/checkboxes.js"></script>
<?php require_once('../../../../core/scripts/checkbox/checkboxsession.inc.php'); ?>
<link href="../../../css/defaultProducts.css" rel="stylesheet"  />
<script> 
    // When the document is ready set up our sortable with it's inherant function(s) 
    $(document).ready(function() { 
        $(".sortable").sortable({ 
            handle : '.handle', 
            update : function () { 
            var order = $(this).sortable('serialize'); 
                $("#info").load("/core/ajax/sort.ajax.php?table=productcategory&"+order); 
            } 
        }); 
    }); 
</script>
<style><!--
<?php if($totalRows_rsRegions<2) { echo ".region { display:none; } "; } ?>
--></style>
<link href="../../../css/categoryList.css" rel="stylesheet"  />
<link href="../../../css/defaultProducts.css" rel="stylesheet"  />
<?php if(isset($body_class)) $body_class .= " products ";  ?>
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" --><?php require_once('../../../../core/region/includes/chooseregion.inc.php'); ?>
<h1><i class="glyphicon glyphicon-shopping-cart"></i> Manage Product Categories</h1>
   <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav"> <li><a href="/products/admin/products/index.php" ><i class="glyphicon glyphicon-arrow-left"></i> Back to products</a></li>
    <li><a href="add_category.php" ><i class="glyphicon glyphicon-plus-sign"></i> Add Category</a></li>
    <li><a href="../tags/index.php" ><i class="glyphicon glyphicon-tags"></i> Tags</a></li>
        <li><a href="manufacturer/index.php" ><i class="glyphicon glyphicon-wrench"></i> Manufacturers</a></li>
             <li><a href="../finish/index.php" ><i class="glyphicon glyphicon-eye-open"></i> Colours/Finishes</a></li>
          <li><a href="../version/index.php" ><i class="glyphicon glyphicon-resize-horizontal"></i> Sizes/Versions</a></li>
           <li><a href="options.php" ><i class="glyphicon glyphicon-cog"></i> Options</a></li>
       
    </ul></div></nav><?php require_once('../../../../core/includes/alert.inc.php'); ?>
    <p>Products can be categorised in many ways, which allow the custimer to find the product the want easily:    
    <ul>
      <li>By default they will display sorted by main category (below).    </li>
      <li> Tags and Manufacturers allow the customer to filter catgories by a more defined set of rules (e.g. in category TVs, by TVs with certain features).
      </li>
      <li>Colours and Sizes allow customer to choose from avaiilable options when adding to basket (if same price)</li>
    </ul>
    <div id="categoryList">
      
      
	   <?php $html = getChildren(0);
	   if($html=="") { ?>
       <p>There are no categories at present</p>
       <?php } else { ?>
       <p id="info">Drag and drop to sort.</p><div style="width:100%; overflow:hidden">
       <?php echo $html;?></div>
       <?php  } ?>
       <form action="<?php echo $editFormAction; ?>" method="POST" name="form2">
         <p><label>
           <input <?php if (!(strcmp($row_rsProductPrefs['sitemap'],1))) {echo "checked=\"checked\"";} ?> name="sitemap" type="checkbox" id="sitemap" onClick="this.form.submit()" value="1">
           Include product categories in site index</label>
         <input name="ID" type="hidden" id="ID" value="<?php echo $row_rsProductPrefs['ID']; ?>">
         <input type="hidden" name="MM_update" value="form2"></p>
       </form>
   </div>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsRegions);

mysql_free_result($rsProductPrefs);
?>
