<?php require_once('../../../Connections/aquiescedb.php'); ?>
<?php require_once('../../../core/includes/adminAccess.inc.php'); ?>
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

$MM_restrictGoTo = "/login/index.php?notloggedin=true&alert=".urlencode("You need to be logged in as an Administrator to access this page.");
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

$maxRows_rsSubscriptions = 50;
$pageNum_rsSubscriptions = 0;
if (isset($_GET['pageNum_rsSubscriptions'])) {
  $pageNum_rsSubscriptions = $_GET['pageNum_rsSubscriptions'];
}
$startRow_rsSubscriptions = $pageNum_rsSubscriptions * $maxRows_rsSubscriptions;

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsSubscriptions = "SELECT * FROM productsubscription ORDER BY createddatetime DESC";
$query_limit_rsSubscriptions = sprintf("%s LIMIT %d, %d", $query_rsSubscriptions, $startRow_rsSubscriptions, $maxRows_rsSubscriptions);
$rsSubscriptions = mysql_query($query_limit_rsSubscriptions, $aquiescedb) or die(mysql_error());
$row_rsSubscriptions = mysql_fetch_assoc($rsSubscriptions);

if (isset($_GET['totalRows_rsSubscriptions'])) {
  $totalRows_rsSubscriptions = $_GET['totalRows_rsSubscriptions'];
} else {
  $all_rsSubscriptions = mysql_query($query_rsSubscriptions);
  $totalRows_rsSubscriptions = mysql_num_rows($all_rsSubscriptions);
}
$totalPages_rsSubscriptions = ceil($totalRows_rsSubscriptions/$maxRows_rsSubscriptions)-1;

$queryString_rsSubscriptions = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsSubscriptions") == false && 
        stristr($param, "totalRows_rsSubscriptions") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsSubscriptions = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsSubscriptions = sprintf("&totalRows_rsSubscriptions=%d%s", $totalRows_rsSubscriptions, $queryString_rsSubscriptions);

?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Manage Subscriptions"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
<link href="../../css/defaultProducts.css" rel="stylesheet" >
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<?php if(isset($body_class)) $body_class .= " products ";  ?>
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
      <h1><i class="glyphicon glyphicon-shopping-cart"></i> Manage Subscriptions</h1>
      <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav"><li><a href="../index.php" class="link_undo"><i class="glyphicon glyphicon-arrow-left"></i> Back to Shop</a></li>
        <li><a href="add_subscription.php" ><i class="glyphicon glyphicon-plus-sign"></i> Add Subscription</a></li>
        
      </ul></div></nav>
      <?php if ($totalRows_rsSubscriptions == 0) { // Show if recordset empty ?>
        <p>There are no subscriptions matching your search criteria.</p>
        <?php } // Show if recordset empty ?>
      <?php if ($totalRows_rsSubscriptions > 0) { // Show if recordset not empty ?>
        <p class="text-muted">Subscriptions <?php echo ($startRow_rsSubscriptions + 1) ?> to <?php echo min($startRow_rsSubscriptions + $maxRows_rsSubscriptions, $totalRows_rsSubscriptions) ?> of <?php echo $totalRows_rsSubscriptions ?></p>
        <table class="table table-hover">
        <thead>
          <tr>
            <th>&nbsp;</th>
            <th>followonID</th>
            <th>User</th>
            <th>Company</th>
            <th>Product</th>
            <th>Starts</th>
            <th>Ends</th>
            <th>Edit</th>
          </tr></thead><tbody>
          <?php do { ?>
            <tr>
              <td class="status<?php echo $row_rsSubscriptions['statusID']; ?>">&nbsp;</td>
              <td><?php echo $row_rsSubscriptions['followonID']; ?></td>
              <td><?php echo $row_rsSubscriptions['userID']; ?></td>
              <td><?php echo $row_rsSubscriptions['directoryID']; ?></td>
              <td><?php echo $row_rsSubscriptions['productID']; ?></td>
              <td><?php echo  date('d M Y', strtotime($row_rsSubscriptions['startdatetime'])); ?></td>
              <td><?php echo date('d M Y', strtotime($row_rsSubscriptions['enddatetime'])); ?></td>
              <td><a href="update_subscription.php?subscriptionID=<?php echo $row_rsSubscriptions['ID']; ?>" class="link_edit icon_only">Edit</a></td>
            </tr>
            <?php } while ($row_rsSubscriptions = mysql_fetch_assoc($rsSubscriptions)); ?></tbody>
        </table>
        <?php } // Show if recordset not empty ?>
      <table class="form-table">
        <tr>
          <td><?php if ($pageNum_rsSubscriptions > 0) { // Show if not first page ?>
              <a href="<?php printf("%s?pageNum_rsSubscriptions=%d%s", $currentPage, 0, $queryString_rsSubscriptions); ?>">First</a>
              <?php } // Show if not first page ?></td>
          <td><?php if ($pageNum_rsSubscriptions > 0) { // Show if not first page ?>
              <a href="<?php printf("%s?pageNum_rsSubscriptions=%d%s", $currentPage, max(0, $pageNum_rsSubscriptions - 1), $queryString_rsSubscriptions); ?>">Previous</a>
              <?php } // Show if not first page ?></td>
          <td><?php if ($pageNum_rsSubscriptions < $totalPages_rsSubscriptions) { // Show if not last page ?>
              <a href="<?php printf("%s?pageNum_rsSubscriptions=%d%s", $currentPage, min($totalPages_rsSubscriptions, $pageNum_rsSubscriptions + 1), $queryString_rsSubscriptions); ?>">Next</a>
              <?php } // Show if not last page ?></td>
          <td><?php if ($pageNum_rsSubscriptions < $totalPages_rsSubscriptions) { // Show if not last page ?>
              <a href="<?php printf("%s?pageNum_rsSubscriptions=%d%s", $currentPage, $totalPages_rsSubscriptions, $queryString_rsSubscriptions); ?>">Last</a>
              <?php } // Show if not last page ?></td>
        </tr>
      </table>
      <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsSubscriptions);
?>
