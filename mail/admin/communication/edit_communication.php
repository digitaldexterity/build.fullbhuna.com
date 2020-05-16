<?php require_once('../../../Connections/aquiescedb.php'); ?><?php require_once('../../../core/includes/adminAccess.inc.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "7,8,9,10";
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

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE communication SET commtypeID=%s, commcatID=%s, incoming=%s, userID=%s, directoryID=%s, orderID=%s, notes=%s, thiscommdatetime=%s, nextcommdatetime=%s, modifiedbyID=%s, modifieddatetime=%s, statusID=%s, followupuserID=%s WHERE ID=%s",
                       GetSQLValueString($_POST['commtypeID'], "int"),
                       GetSQLValueString($_POST['communicationcatID'], "int"),
                       GetSQLValueString($_POST['incoming'], "int"),
                       GetSQLValueString($_POST['userID'], "int"),
                       GetSQLValueString($_POST['directoryID'], "int"),
                       GetSQLValueString($_POST['orderID'], "int"),
                       GetSQLValueString($_POST['notes'], "text"),
                       GetSQLValueString($_POST['thiscommdatetime'], "date"),
                       GetSQLValueString($_POST['nextcommdatetime'], "date"),
                       GetSQLValueString($_POST['modifiedbyID'], "int"),
                       GetSQLValueString($_POST['modifieddatetime'], "date"),
                       GetSQLValueString(isset($_POST['statusID']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['followupuserID'], "int"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateGoTo = isset($_GET['returnURL']) ? $_GET['returnURL'] : "index.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo)); exit;
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
	if(isset($_POST['followupID']) && intval($_POST['followupID']) >0) {
		$update = "UPDATE communication SET nextcommID = ".mysql_insert_id()." WHERE ID = ".intval($_POST['followupID']);
		$result = mysql_query($update, $aquiescedb) or die(mysql_error());
	}
  $insertGoTo = isset($_GET['returnURL']) ? $_GET['returnURL'] : "index.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
    $insertGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $insertGoTo)); exit;
}

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsCommType = "SELECT ID, typename FROM communicationtype WHERE statusID = 1  ORDER BY communicationtype.ordernum";
$rsCommType = mysql_query($query_rsCommType, $aquiescedb) or die(mysql_error());
$row_rsCommType = mysql_fetch_assoc($rsCommType);
$totalRows_rsCommType = mysql_num_rows($rsCommType);

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

$colname_rsCommunication = "-1";
if (isset($_GET['communicationID'])) {
  $colname_rsCommunication = $_GET['communicationID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsCommunication = sprintf("SELECT communication.*, directory.name, users.firstname, users.surname, productorders.VendorTxCode, location.address1, location.address2, location.address3 FROM communication LEFT JOIN directory ON (communication.directoryID = directory.ID) LEFT JOIN users ON (communication.userID = users.ID) LEFT JOIN productorders ON (communication.orderID = productorders.VendorTxCode) LEFT JOIN location ON (communication.locationID = location.ID) WHERE communication.ID = %s", GetSQLValueString($colname_rsCommunication, "int"));
$rsCommunication = mysql_query($query_rsCommunication, $aquiescedb) or die(mysql_error());
$row_rsCommunication = mysql_fetch_assoc($rsCommunication);
$totalRows_rsCommunication = mysql_num_rows($rsCommunication);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsStaff = "SELECT ID, firstname, surname FROM users WHERE usertypeID >= 7 ORDER BY surname ASC";
$rsStaff = mysql_query($query_rsStaff, $aquiescedb) or die(mysql_error());
$row_rsStaff = mysql_fetch_assoc($rsStaff);
$totalRows_rsStaff = mysql_num_rows($rsStaff);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsCategories = "SELECT ID, categoryname FROM communicationcategory ORDER BY categoryname ASC";
$rsCategories = mysql_query($query_rsCategories, $aquiescedb) or die(mysql_error());
$row_rsCategories = mysql_fetch_assoc($rsCategories);
$totalRows_rsCategories = mysql_num_rows($rsCategories);
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Edit Note"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->


<script src="/core/scripts/date-picker/js/datepicker.js"></script>



<style><!--
<?php if ($totalRows_rsCommType==0) { 
 echo ".communicationtype { display:none; }";
} 

if ($totalRows_rsCategories==0) { 
 echo ".communicationcategory { display:none; }";
}
?>
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
<div class="page forum">

<h1><i class="glyphicon glyphicon-envelope"></i> Update Note</h1>
    <h2><?php echo isset($row_rsCommunication['name']) ? $row_rsCommunication['name']." - " : ""; ?><?php echo isset($row_rsCommunication['locationname']) ? $row_rsCommunication['locationname']." " : ""; ?><?php echo $row_rsCommunication['firstname']; ?> <?php echo $row_rsCommunication['surname']; ?> <?php echo isset($row_rsCommunication['or_orderID']) ? " - ".$row_rsCommunication['VendorTxCode'] : ""; ?></h2>
    <?php echo isset($row_rsCommunication['address1']) ? "<h3>".$row_rsCommunication['address1']." ".$row_rsCommunication['address2']." ".$row_rsCommunication['address3']."</h3>" : ""; ?>
  
<form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1">
      <table class="form-table"> <tr>
          <td class="text-nowrap text-right">Completed:</td>
          <td><input type="hidden" name="thiscommdatetime" id="thiscommdatetime" value="<?php $setvalue = $row_rsCommunication['thiscommdatetime'];  echo $setvalue; ?>" class='highlight-days-67 split-date format-y-m-d divider-dash' />
          <?php $inputname = "thiscommdatetime"; $time = true; require('../../../core/includes/datetimeinput.inc.php'); ?></td>
        </tr>
        <tr class="communicationcategory">
          <td class="text-nowrap text-right"><label for="communicationcatID">Category:</label></td>
          <td>
            <select name="communicationcatID" id="communicationcatID"  class="form-control">
              <option value="" <?php if (!(strcmp("", $row_rsCommunication['commcatID']))) {echo "selected=\"selected\"";} ?>><?php echo isset($region['text_choose']) ? htmlentities($region['text_choose'], ENT_COMPAT, "UTF-8") : "Choose..." ?></option>
              <?php
do {  
?>
<option value="<?php echo $row_rsCategories['ID']?>"<?php if (!(strcmp($row_rsCategories['ID'], $row_rsCommunication['commcatID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsCategories['categoryname']?></option>
              <?php
} while ($row_rsCategories = mysql_fetch_assoc($rsCategories));
  $rows = mysql_num_rows($rsCategories);
  if($rows > 0) {
      mysql_data_seek($rsCategories, 0);
	  $row_rsCategories = mysql_fetch_assoc($rsCategories);
  }
?>
          </select></td>
        </tr>
        <tr class="communicationtype">
          <td class="text-nowrap text-right">Type:</td>
          <td><select name="commtypeID"  class="form-control">
            <?php
do {  
?>
            <option value="<?php echo $row_rsCommType['ID']?>"<?php if (!(strcmp($row_rsCommType['ID'], $row_rsCommunication['commtypeID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsCommType['typename']?></option>
            <?php
} while ($row_rsCommType = mysql_fetch_assoc($rsCommType));
  $rows = mysql_num_rows($rsCommType);
  if($rows > 0) {
      mysql_data_seek($rsCommType, 0);
	  $row_rsCommType = mysql_fetch_assoc($rsCommType);
  }
?>
          </select></td>
        </tr> <tr>
          <td class="text-nowrap text-right"> with:</td>
          <td><?php echo (isset($row_rsCommunication['firstname']) || isset($row_rsCommunication['surname'])) ? trim($row_rsCommunication['firstname']." ".$row_rsCommunication['surname']) : "N/A"; ?>&nbsp;&nbsp;
            <label>
              <input <?php if (!(strcmp($row_rsCommunication['incoming'],"1"))) {echo "checked=\"checked\"";} ?> type="radio" name="incoming" value="1" id="incoming_0" />
              Incoming</label>&nbsp;&nbsp;
           
            <label>
              <input <?php if (!(strcmp($row_rsCommunication['incoming'],"0"))) {echo "checked=\"checked\"";} ?> type="radio" name="incoming" value="0" id="incoming_1" />
              Outgoing</label></td>
        </tr>
        <tr> </tr> <tr>
          <td class="text-nowrap text-right top">Notes:</td>
          <td><textarea name="notes" cols="80" rows="5"  class="form-control"><?php echo $row_rsCommunication['notes']; ?></textarea></td>
        </tr> <tr>
          <td class="text-nowrap text-right">Follow-up:</td>
          <td class="form-inline"><input type="hidden" name="nextcommdatetime" id="nextcommdatetime" class='highlight-days-67 split-date format-y-m-d divider-dash' value="<?php $setvalue = $row_rsCommunication['nextcommdatetime']; echo $setvalue; ?>" />
          <?php $inputname = "nextcommdatetime"; require('../../../core/includes/datetimeinput.inc.php'); ?> <label>by 
          
            <select name="followupuserID" id="followupuserID"  class="form-control">
              <option value="" <?php if (!(strcmp("", $row_rsCommunication['followupuserID']))) {echo "selected=\"selected\"";} ?>>Not specified</option>
              <?php
do {  
?>
<option value="<?php echo $row_rsStaff['ID']?>"<?php if (!(strcmp($row_rsStaff['ID'], $row_rsCommunication['followupuserID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsStaff['firstname']." ".$row_rsStaff['surname']; ?></option>
              <?php
} while ($row_rsStaff = mysql_fetch_assoc($rsStaff));
  $rows = mysql_num_rows($rsStaff);
  if($rows > 0) {
      mysql_data_seek($rsStaff, 0);
	  $row_rsStaff = mysql_fetch_assoc($rsStaff);
  }
?>
            </select></label>
          <?php if(isset($row_rsCommunication['nextcommID'])) { ?>
          <span class="glyphicon glyphicon-ok"></span> Completed<?php } ?></td>
        </tr> <tr>
          <td class="text-nowrap text-right">Active:</td>
          <td><input <?php if (!(strcmp($row_rsCommunication['statusID'],1))) {echo "checked=\"checked\"";} ?> name="statusID" type="checkbox" id="statusID" value="1" /></td>
        </tr> <tr>
          <td class="text-nowrap text-right">&nbsp;</td>
          <td><button type="submit" class="btn btn-primary">Save changes</button></td>
        </tr>
      </table>
     
      <input type="hidden" name="directoryID" value="<?php echo $row_rsCommunication['directoryID']; ?>" />
      <input type="hidden" name="orderID" value="<?php echo $row_rsCommunication['orderID']; ?>" />
      <input type="hidden" name="userID" value="<?php echo $row_rsCommunication['userID']; ?>" />
      
      <input type="hidden" name="modifiedbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
      <input type="hidden" name="modifieddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
     
     
      <input name="ID" type="hidden" id="ID" value="<?php echo $row_rsCommunication['ID']; ?>" />
      <input type="hidden" name="MM_update" value="form1" />
</form>
    </div>
<!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsCommType);

mysql_free_result($rsLoggedIn);

mysql_free_result($rsCommunication);

mysql_free_result($rsStaff);

mysql_free_result($rsCategories);
?>
