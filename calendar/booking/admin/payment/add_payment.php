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

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
  $insertSQL = sprintf("INSERT INTO bookingpricing (resourceID, price, pricehours, deposit, `currency`, `default`, details, datestart, dateend, timestart, timeend, daysofweek, createddatetime, createdbyID, statusID) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
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
                       GetSQLValueString($_POST['createddatetime'], "date"),
                       GetSQLValueString($_POST['createdbyID'], "int"),
                       GetSQLValueString($_POST['statusID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($insertSQL, $aquiescedb) or die(mysql_error());

  $insertGoTo = "../update_resource.php?resourceID=" . intval($_GET['resourceID']) . "&tab=4";
  if (isset($_SERVER['QUERY_STRING'])) {
    $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
    $insertGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $insertGoTo));
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
?><!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" --><title><?php $pageTitle = "Add rate"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title><!-- InstanceEndEditable -->
<?php require_once('../../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<style type="text/css">
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
   <h1>Add Rate for <?php echo $row_rsThisResource['title']; ?></h1>
   <form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1" role="form">
   <?php if (!isset($row_rsThisResource['isAlreadyPrice'])) { ?>
   <p>This is the DEFAULT rate for <?php echo $row_rsThisResource['title']; ?>. You can add more variations for date or time afterwards. This rate will be charged if none of the criteria for any extra date or time specific rates are met.</p><style> .datetimes { display:none; } </style><?php } ?> <input type="hidden" name="default" value="<?php echo (isset($row_rsThisResource['isAlreadyPrice'])) ? 0 : 1; ?>" />
   
   
  
     <table> <tr>
         <td class="nowrap text-right">Price:</td>
         <td><input name="price" type="text" class="textbox" value="" size="8" maxlength="8" />
          
          
           per
           <input name="periodamount" type="text" class="textbox" id="periodamount" value="1" size="3" maxlength="3" />
           <select name="periodmultiple" class="selectinput" id="periodmultiple">
             <option value="1">hour(s)</option>
             <option value="24" selected="selected">day(s)</option>
             <option value="168">week(s)</option>
           </select>
           <input name="pricehours" type="hidden" id="pricehours" value="24"  />
          
           Currency:
          <input name="currency" type="text" class="textbox" value="GBP" size="4" maxlength="3" /></td>
       </tr> <tr>
         <td class="nowrap text-right">Deposit:</td>
         <td><input name="deposit" type="text" class="textbox" value="0.00" size="8" maxlength="8" /></td>
       </tr> <tr>
         <td class="nowrap text-right top">Details:</td>
         <td><textarea name="details" cols="50" rows="5"></textarea>         </td>
       </tr> <tr>
         <td class="nowrap text-right"class="datetimes">Datestart:</td>
         <td class="datetimes"><input type="text" class="textbox" name="datestart" value="" size="32" /></td>
       </tr> <tr>
         <td class="nowrap text-right"class="datetimes">Dateend:</td>
         <td class="datetimes"><input type="text" class="textbox" name="dateend" value="" size="32" /></td>
       </tr> <tr>
         <td class="nowrap text-right"class="datetimes">Timestart:</td>
         <td class="datetimes"><input type="text" class="textbox" name="timestart" value="" size="32" /></td>
       </tr> <tr>
         <td class="nowrap text-right"class="datetimes">Timeend:</td>
         <td class="datetimes"><input type="text" class="textbox" name="timeend" value="" size="32" /></td>
       </tr> <tr>
         <td class="nowrap text-right"class="datetimes">Daysofweek:</td>
         <td class="datetimes"><input name="daysofweek" type="text" class="textbox" value="1234567" size="7" maxlength="7" /></td>
       </tr> <tr>
         <td class="nowrap text-right">&nbsp;</td>
         <td><input type="submit" class="button" value="Add Rate" /></td>
       </tr>
     </table>
     <input type="hidden" name="resourceID" value="<?php echo $row_rsThisResource['ID']; ?>" />
     <input name="createddatetime" type="hidden" value="<?php echo date('Y-m-d H:i:s'); ?>" />
     <input type="hidden" name="createdbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
     <input type="hidden" name="statusID" value="1" />
     <input type="hidden" name="MM_insert" value="form1" />
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
?>


