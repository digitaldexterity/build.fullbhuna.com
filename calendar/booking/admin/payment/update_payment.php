<?php require_once('../../../../Connections/aquiescedb.php'); ?>
<?php require_once('../../../../Connections/aquiescedb.php'); ?>
<?php // calculate price hours
$_POST['pricehours'] = $_POST['periodamount']*$_POST['periodmultiple']; ?>
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

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE bookingpricing SET resourceID=%s, price=%s, pricehours=%s, deposit=%s, `currency`=%s, `default`=%s, details=%s, datestart=%s, dateend=%s, timestart=%s, timeend=%s, daysofweek=%s, modifeddatetime=%s, modifiedbyID=%s, statusID=%s WHERE ID=%s",
                       GetSQLValueString($_POST['resourceID'], "int"),
                       GetSQLValueString($_POST['price'], "double"),
                       GetSQLValueString($_POST['pricehours'], "int"),
                       GetSQLValueString($_POST['deposit'], "double"),
                       GetSQLValueString($_POST['currency'], "text"),
                       GetSQLValueString($_POST['default'], "int"),
                       GetSQLValueString($_POST['details'], "text"),
                       GetSQLValueString($_POST['datestart'], "date"),
                       GetSQLValueString($_POST['dateend'], "date"),
                       GetSQLValueString($_POST['timestart'], "date"),
                       GetSQLValueString($_POST['timeend'], "date"),
                       GetSQLValueString($_POST['daysofweek'], "text"),
                       GetSQLValueString($_POST['modifieddatetime'], "date"),
                       GetSQLValueString($_POST['modifiedbyID'], "int"),
                       GetSQLValueString($_POST['statusID'], "int"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());

  $updateGoTo = "../update_resource.php?tab=4";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo));
}

$varCreatedByID_rsThisResource = "-1";
if (isset($_GET['createdbyID'])) {
  $varCreatedByID_rsThisResource = $_GET['createdbyID'];
}
$varResourceID_rsThisResource = "-1";
if (isset($_GET['resourceID'])) {
  $varResourceID_rsThisResource = $_GET['resourceID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsThisResource = sprintf("SELECT bookingresource.ID, bookingresource.title, bookingpricing.ID AS isAlreadyPrice FROM bookingresource LEFT JOIN bookingpricing ON (bookingresource.ID = bookingpricing.resourceID) WHERE bookingresource.ID = %s OR bookingresource.createdbyID = %s ORDER BY bookingresource.createddatetime DESC", GetSQLValueString($varResourceID_rsThisResource, "int"),GetSQLValueString($varCreatedByID_rsThisResource, "int"));
$rsThisResource = mysql_query($query_rsThisResource, $aquiescedb) or die(mysql_error());
$row_rsThisResource = mysql_fetch_assoc($rsThisResource);
$totalRows_rsThisResource = mysql_num_rows($rsThisResource);

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

$varPaymentRateID_rsPaymentRate = "-1";
if (isset($_GET['paymentID'])) {
  $varPaymentRateID_rsPaymentRate = $_GET['paymentID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPaymentRate = sprintf("SELECT bookingpricing.ID, bookingpricing.resourceID, bookingpricing.price, bookingpricing.pricehours, bookingpricing.deposit, bookingpricing.`currency`, bookingpricing.`default`, bookingpricing.details, bookingpricing.datestart, bookingpricing.dateend, bookingpricing.timestart, bookingpricing.timeend, bookingpricing.daysofweek, bookingpricing.createddatetime, bookingpricing.createdbyID, bookingpricing.modifeddatetime, bookingpricing.modifiedbyID, bookingpricing.statusID FROM bookingpricing WHERE bookingpricing.ID = %s", GetSQLValueString($varPaymentRateID_rsPaymentRate, "int"));
$rsPaymentRate = mysql_query($query_rsPaymentRate, $aquiescedb) or die(mysql_error());
$row_rsPaymentRate = mysql_fetch_assoc($rsPaymentRate);
$totalRows_rsPaymentRate = mysql_num_rows($rsPaymentRate);
?><!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" --><title><?php $pageTitle = "Update rate"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title><!-- InstanceEndEditable -->
<?php require_once('../../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<style >
<!--
.datetimes {
}
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
   <h1>Update Rate for <?php echo $row_rsThisResource['title']; ?> </h1>
   <form action="<?php echo $editFormAction; ?>" method="POST" name="form1" id="form1" role="form">
   <?php if ($row_rsPaymentRate['default'] ==1) { ?>
   <p>This is the DEFAULT rate for <?php echo $row_rsThisResource['title']; ?>. You cannot add any date or time specifics for this rate.</p>
   <style> .datetimes { display:none; } </style><?php } ?> 
   
   
  
     <table class="form-table"> <tr>
         <td class="text-nowrap text-right">Price:</td>
         <td><?php $hours = $row_rsPaymentRate['pricehours'];
		 if (($hours/168) == number_format(($hours/168),0)) { // is weeks
		 $periodamount = $hours/168; $periodmultiple = 168;
		 } else if (($hours/24) == number_format(($hours/24),0)) { // is days
		 	 $periodamount = $hours/24; $periodmultiple = 24;
			 } else { // is hours
			 $periodamount = $hours; $periodmultiple = 1;
			 }
		 ?>
         <input name="price" type="text"  value="<?php echo number_format($row_rsPaymentRate['price'],2); ?>" size="8" maxlength="8" />
          
          
           per
           <input name="periodamount" type="text"  id="periodamount" value="<?php echo $periodamount; ?>" size="3" maxlength="3" />
           <select name="periodmultiple" id="periodmultiple">
             <option value="1" <?php if (!(strcmp(1, "$periodmultiple"))) {echo "selected=\"selected\"";} ?>>hour(s)</option>
             <option value="24" selected="selected" <?php if (!(strcmp(24, "$periodmultiple"))) {echo "selected=\"selected\"";} ?>>day(s)</option>
<option value="168" <?php if (!(strcmp(168, "$periodmultiple"))) {echo "selected=\"selected\"";} ?>>week(s)</option>
           </select>
           <input name="pricehours" type="hidden" id="pricehours" value="<?php echo $row_rsPaymentRate['pricehours']; ?>"  />
          
           Currency:
          <input name="currency" type="text"  value="<?php echo $row_rsPaymentRate['currency']; ?>" size="4" maxlength="3" /></td>
       </tr> <tr>
         <td class="text-nowrap text-right">Deposit:</td>
         <td><input name="deposit" type="text"  value="<?php echo number_format($row_rsPaymentRate['deposit'],2); ?>" size="8" maxlength="8" /></td>
       </tr> <tr>
         <td class="text-nowrap text-right top">Details:</td>
         <td><textarea name="details" cols="50" rows="5"><?php echo $row_rsPaymentRate['details']; ?></textarea>         </td>
       </tr> <tr>
         <td  class="text-nowrap datetimes text-right">Datestart:</td>
         <td class="datetimes"><input name="datestart" type="text"  value="<?php echo $row_rsPaymentRate['datestart']; ?>" size="32" /></td>
       </tr> <tr>
         <td  class="text-nowrap datetimes text-right">Dateend:</td>
         <td class="datetimes"><input name="dateend" type="text"  value="<?php echo $row_rsPaymentRate['dateend']; ?>" size="32" /></td>
       </tr> <tr>
         <td  class="text-nowrap datetimes text-right">Timestart:</td>
         <td class="datetimes"><input name="timestart" type="text"  value="<?php echo $row_rsPaymentRate['timestart']; ?>" size="32" /></td>
       </tr> <tr>
         <td  class="text-nowrap datetimes text-right">Timeend:</td>
         <td class="datetimes"><input name="timeend" type="text"  value="<?php echo $row_rsPaymentRate['timeend']; ?>" size="32" /></td>
       </tr> <tr>
         <td  class="text-nowrap datetimes text-right">Daysofweek:</td>
         <td class="datetimes"><input name="daysofweek" type="text"  value="<?php echo $row_rsPaymentRate['daysofweek']; ?>" size="7" maxlength="7" /></td>
       </tr> <tr>
         <td  class="text-nowrap datetimes text-right">&nbsp;</td>
         <td><button type="submit" class="btn btn-primary" >Save changes</button></td>
       </tr>
     </table><input type="hidden" name="default" value="<?php echo $row_rsPaymentRate['default']; ?>" />
     <input type="hidden" name="resourceID" value="<?php echo $row_rsThisResource['ID']; ?>" />
     <input name="modifieddatetime" type="hidden" value="<?php echo date('Y-m-d H:i:s'); ?>" />
     <input type="hidden" name="modifiedbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
     <input type="hidden" name="statusID" value="1" />
<input name="ID" type="hidden" id="ID" value="<?php echo intval($_GET['paymentID']); ?>" />
<input type="hidden" name="MM_update" value="form1" />
   </form>
   <p>&nbsp;</p>
   <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsThisResource);

mysql_free_result($rsLoggedIn);

mysql_free_result($rsPaymentRate);
?>


