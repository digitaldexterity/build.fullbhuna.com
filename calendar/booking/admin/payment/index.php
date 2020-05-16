<?php require_once('../../../../Connections/aquiescedb.php'); ?><?php
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


$colname_rsResource = "-1";
if (isset($_GET['resourceID'])) {
  $colname_rsResource = $_GET['resourceID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsResource = sprintf("SELECT * FROM bookingresource WHERE ID = %s", GetSQLValueString($colname_rsResource, "int"));
$rsResource = mysql_query($query_rsResource, $aquiescedb) or die(mysql_error());
$row_rsResource = mysql_fetch_assoc($rsResource);
$totalRows_rsResource = mysql_num_rows($rsResource);

$maxRows_rePricings = 10;
$pageNum_rePricings = 0;
if (isset($_GET['pageNum_rePricings'])) {
  $pageNum_rePricings = $_GET['pageNum_rePricings'];
}
$startRow_rePricings = $pageNum_rePricings * $maxRows_rePricings;

$colname_rePricings = "-1";
if (isset($_GET['resourceID'])) {
  $colname_rePricings = $_GET['resourceID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rePricings = sprintf("SELECT * FROM bookingpricing WHERE resourceID = %s", GetSQLValueString($colname_rePricings, "int"));
$query_limit_rePricings = sprintf("%s LIMIT %d, %d", $query_rePricings, $startRow_rePricings, $maxRows_rePricings);
$rePricings = mysql_query($query_limit_rePricings, $aquiescedb) or die(mysql_error());
$row_rePricings = mysql_fetch_assoc($rePricings);

if (isset($_GET['totalRows_rePricings'])) {
  $totalRows_rePricings = $_GET['totalRows_rePricings'];
} else {
  $all_rePricings = mysql_query($query_rePricings);
  $totalRows_rePricings = mysql_num_rows($all_rePricings);
}
$totalPages_rePricings = ceil($totalRows_rePricings/$maxRows_rePricings)-1;
?><!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = " Booking Pricing"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" --><style >
<!--
<?php if (!(strcmp($row_rsResource['interval'],"0"))) { ?>
#availabletime {
	display: none;
} <?php } ?>
-->
</style><!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" -->
      <script>
    function validateForm(form)
{
form.availabledow.value = (form.mon.checked == true ? "1" : "")+(form.tue.checked == true ? "2" : "")+(form.wed.checked == true ? "3" : "")+(form.thu.checked == true ? "4" : "")+(form.fri.checked == true ? "5" : "")+(form.sat.checked == true ? "6" : "")+(form.sun.checked == true ? "7" : "");
var errors = '';
if (form.availabledow.value == "") errors +="Please enter at least one day\n";
if (form.availablestart.value == "") errors +="Please enter a start time\n";
if (form.availableend.value == "") errors +="Please enter an end time\n";

if (form.title.value == "") errors +="Please enter a resource name\n";


 if (errors) window.alert(errors); 
  
   document.returnValue = (!errors);
 }
              </script><div class="page calendar">
    <h1>Booking Pricing for <?php echo $row_rsResource['title']; ?></h1>
    <p>Under Construction</p>
    <p><a href="add_payment.php?resourceID=<?php echo $row_rsResource['ID']; ?>">Add a pricing structure</a></p>
    
    <?php if ($totalRows_rePricings == 0) { // Show if recordset empty ?>
      <p>There are no pricing structures for <?php echo $row_rsResource['title']; ?>.</p>
      <?php } // Show if recordset empty ?>
<?php if ($totalRows_rePricings > 0) { // Show if recordset not empty ?> 
        <table border="0" cellpadding="0" cellspacing="0" class="listTable">
          <tr>
            <td><strong>Price</strong></td>
            <td><strong>per</strong></td>
            <td><strong>Deposit</strong></td>
            <td>&nbsp;</td>
            <td><strong>Date Start</strong></td>
            <td><strong>End</strong></td>
            <td><strong>Time Start</strong></td>
            <td><strong>End</strong></td>
            <td><strong>Days</strong></td>
           
          </tr>
          <?php do { ?>
          <tr>
            <td><?php echo $row_rePricings['currency']; ?><?php echo $row_rePricings['price']; ?></td>
            <td><?php echo $row_rePricings['pricehours']; ?>hrs</td>
            <td><?php echo $row_rePricings['currency']; ?><?php echo $row_rePricings['deposit']; ?></td>
            <td><?php if ($row_rePricings['default'] == 1) echo "DEFAULT</td><td colspan=5>&nbsp;</td>"; else { ?></td>
            <td><?php echo $row_rePricings['datestart']; ?></td>
            <td><?php echo $row_rePricings['dateend']; ?></td>
            <td><?php echo $row_rePricings['timestart']; ?></td>
            <td><?php echo $row_rePricings['timeend']; ?></td>
            <td><?php echo $row_rePricings['daysofweek']; ?></td>
            <?php } ?>
          </tr>
          <?php } while ($row_rePricings = mysql_fetch_assoc($rePricings)); ?>
            </table>
        <?php } // Show if recordset not empty ?></div>
<!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsResource);

mysql_free_result($rePricings);
?>
