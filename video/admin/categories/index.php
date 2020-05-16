<?php require_once('../../../Connections/aquiescedb.php'); ?>
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

$MM_restrictGoTo = "../../../login/index.php?notloggedin=true";
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

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsVideoCategories = "SELECT videocategory.ID, videocategory.categoryname, videocategory.statusID, region.title AS regionname FROM videocategory LEFT JOIN region ON videocategory.regionID = region.ID ORDER BY videocategory.ordernum, videocategory.categoryname";
$rsVideoCategories = mysql_query($query_rsVideoCategories, $aquiescedb) or die(mysql_error());
$row_rsVideoCategories = mysql_fetch_assoc($rsVideoCategories);
$totalRows_rsVideoCategories = mysql_num_rows($rsVideoCategories);$varRegionID_rsVideoCategories = "0";
if (isset($regionID)) {
  $varRegionID_rsVideoCategories = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsVideoCategories = sprintf("SELECT videocategory.ID, videocategory.categoryname, videocategory.statusID, region.title AS regionname FROM videocategory LEFT JOIN region ON videocategory.regionID = region.ID WHERE %s = videocategory.regionID OR videocategory.regionID = 0 ORDER BY videocategory.ordernum, videocategory.categoryname", GetSQLValueString($varRegionID_rsVideoCategories, "int"));
$rsVideoCategories = mysql_query($query_rsVideoCategories, $aquiescedb) or die(mysql_error());
$row_rsVideoCategories = mysql_fetch_assoc($rsVideoCategories);
$totalRows_rsVideoCategories = mysql_num_rows($rsVideoCategories);
?><!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" --><title><?php $pageTitle = " Manage Video Categories"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title><!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script> 
    // When the document is ready set up our sortable with it's inherant function(s) 
    $(document).ready(function() { 	
        $(".sortable").sortable({ 
            handle : '.handle', 
            update : function () { 
            var order = $(this).sortable('serialize'); 
                $("#info").load("/core/ajax/sort.ajax.php?table=videocategory&"+order); 
            } 
        }); 
		
    }); 
</script>
<style><!--

--></style>
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" -->
        <div class="page video">
   <h1><i class="glyphicon glyphicon-film"></i> Manage Video Categories</h1>
 
   <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
     <li><a href="add_video_category.php" ><i class="glyphicon glyphicon-plus-sign"></i> Add Category</a></li>
     <li><a href="../index.php" class="link_undo"><i class="glyphicon glyphicon-arrow-left"></i> Back to videos</a></li>
   </ul></div></nav>
   <?php if ($totalRows_rsVideoCategories == 0) { // Show if recordset empty ?>
     <p>There are no categories</p>
     <?php } // Show if recordset empty ?>
  
   <?php if ($totalRows_rsVideoCategories > 0) { // Show if recordset not empty ?>
     <p id="info">Drag and drop to re-order</p>
     <ul  class="listTable sortable">

        <?php do { ?>
          <li id="listItem_<?php echo $row_rsVideoCategories['ID']; ?>" ><span class="handle">&nbsp;</span>
            <span class="status<?php echo $row_rsVideoCategories['statusID']; ?>">&nbsp;</span>
            <span><?php echo $row_rsVideoCategories['categoryname']; ?></span>
            <span class="region"><em><?php echo isset($row_rsVideoCategories['regionname']) ? $row_rsVideoCategories['regionname'] : "All sites"; ?></em></span>
            <span><a href="update_video_category.php?categoryID=<?php echo $row_rsVideoCategories['ID']; ?>" class="link_edit icon_only">Edit</a></span>
          </li>
          <?php } while ($row_rsVideoCategories = mysql_fetch_assoc($rsVideoCategories)); ?>
        </ul>
     <?php } // Show if recordset not empty ?></div>
<!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsVideoCategories);
?>


