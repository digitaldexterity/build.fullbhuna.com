<?php require_once('../../../../Connections/aquiescedb.php'); ?>
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

$maxRows_rsBookingCategories = 20;
$pageNum_rsBookingCategories = 0;
if (isset($_GET['pageNum_rsBookingCategories'])) {
  $pageNum_rsBookingCategories = $_GET['pageNum_rsBookingCategories'];
}
$startRow_rsBookingCategories = $pageNum_rsBookingCategories * $maxRows_rsBookingCategories;

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsBookingCategories = "SELECT ID, `description`, statusID FROM bookingcategory ORDER BY `description` ASC";
$query_limit_rsBookingCategories = sprintf("%s LIMIT %d, %d", $query_rsBookingCategories, $startRow_rsBookingCategories, $maxRows_rsBookingCategories);
$rsBookingCategories = mysql_query($query_limit_rsBookingCategories, $aquiescedb) or die(mysql_error());
$row_rsBookingCategories = mysql_fetch_assoc($rsBookingCategories);

if (isset($_GET['totalRows_rsBookingCategories'])) {
  $totalRows_rsBookingCategories = $_GET['totalRows_rsBookingCategories'];
} else {
  $all_rsBookingCategories = mysql_query($query_rsBookingCategories);
  $totalRows_rsBookingCategories = mysql_num_rows($all_rsBookingCategories);
}
$totalPages_rsBookingCategories = ceil($totalRows_rsBookingCategories/$maxRows_rsBookingCategories)-1;
?><!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" --><title><?php $pageTitle = "Booking Categories"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title><!-- InstanceEndEditable -->
<?php require_once('../../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" --><!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" --><div class="page calendar">
   <h1>Booking Categories</h1>
   <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav"><li><a href="add_category.php" ><i class="glyphicon glyphicon-plus-sign"></i> Add a category</a></li><li><a href="../index.php" class="link_undo"><i class="glyphicon glyphicon-arrow-left"></i> Back to resources</a></li>
     
   </ul></div></nav>
   <?php if ($totalRows_rsBookingCategories == 0) { // Show if recordset empty ?>
     <p>There are not categories entered at present.</p>
     <?php } // Show if recordset empty ?>
   <?php if ($totalRows_rsBookingCategories > 0) { // Show if recordset not empty ?>
  <p>Categories <?php echo ($startRow_rsBookingCategories + 1) ?> to <?php echo min($startRow_rsBookingCategories + $maxRows_rsBookingCategories, $totalRows_rsBookingCategories) ?> of <?php echo $totalRows_rsBookingCategories ?> </p>
  <table border="0" cellpadding="0" cellspacing="0" class="listTable">

        <?php do { ?>
          <tr>
            <td><?php if($row_rsBookingCategories['statusID'] == 1) { ?>
              <img src="../../../../core/images/icons/green-light.png" alt="Active category" style="vertical-align:
middle;" />
              <?php } else { ?>
            <img src="../../../../core/images/icons/red-light.png" alt="Inactive category" style="vertical-align:
middle;" />              <?php } ?></td>
            <td><?php echo $row_rsBookingCategories['description']; ?></td>
            <td><a href="update_category.php?categoryID=<?php echo $row_rsBookingCategories['ID']; ?>" class="link_edit icon_only">Edit</a></td>
          </tr>
          <?php } while ($row_rsBookingCategories = mysql_fetch_assoc($rsBookingCategories)); ?>
     </table>
  <?php } // Show if recordset not empty ?></div>
<!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsBookingCategories);
?>


