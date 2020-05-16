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

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}


if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {

if(isset($_POST['allsites']))  $_POST['regionID'] = 0;
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE usergroup SET regionID=%s, groupname=%s, groupdescription=%s, optin=%s, notificationemail=%s, renewalcost=%s, grouptypeID=%s, modifiedbyID=%s, modifieddatetime=%s, statusID=%s WHERE ID=%s",
                       GetSQLValueString($_POST['regionID'], "int"),
                       GetSQLValueString($_POST['groupname'], "text"),
                       GetSQLValueString($_POST['groupdescription'], "text"),
                       GetSQLValueString($_POST['optin'], "int"),
                       GetSQLValueString($_POST['notificationemail'], "text"),
                       GetSQLValueString($_POST['renewalcost'], "double"),
                       GetSQLValueString($_POST['grouptypeID'], "int"),
                       GetSQLValueString($_POST['modifiedbyID'], "int"),
                       GetSQLValueString($_POST['modifieddatetime'], "date"),
                       GetSQLValueString(isset($_POST['statusID']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateGoTo = "index.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo)); exit;
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
  $insertGoTo = "index.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
    $insertGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $insertGoTo)); exit;
}

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

$colname_rsUserGroup = "-1";
if (isset($_GET['groupID'])) {
  $colname_rsUserGroup = $_GET['groupID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsUserGroup = sprintf("SELECT * FROM usergroup WHERE ID = %s", GetSQLValueString($colname_rsUserGroup, "int"));
$rsUserGroup = mysql_query($query_rsUserGroup, $aquiescedb) or die(mysql_error());
$row_rsUserGroup = mysql_fetch_assoc($rsUserGroup);
$totalRows_rsUserGroup = mysql_num_rows($rsUserGroup);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsGroupTypes = "SELECT ID, grouptype FROM usergrouptype ORDER BY grouptype ASC";
$rsGroupTypes = mysql_query($query_rsGroupTypes, $aquiescedb) or die(mysql_error());
$row_rsGroupTypes = mysql_fetch_assoc($rsGroupTypes);
$totalRows_rsGroupTypes = mysql_num_rows($rsGroupTypes);
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Update User Group"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script src="../../../SpryAssets/SpryValidationTextField.js"></script>
<link href="../../../SpryAssets/SpryValidationTextField.css" rel="stylesheet"  />
<link href="../../css/membersDefault.css" rel="stylesheet"  />
<style><!--
<?php if($totalRows_rsGroupTypes==0) { echo ".grouptype { display:none; }"; } ?>
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
        <div class="page users">
    <h1><i class="glyphicon glyphicon-user"></i> Update User Group
      </h1><form action="<?php echo $editFormAction; ?>" method="POST" name="form1" id="form1">
      <table class="form-table"> <tr>
          <td class="text-nowrap text-right">Group name:</td>
          <td><span id="sprytextfield1">
            <input name="groupname" type="text"  value="<?php echo $row_rsUserGroup['groupname']; ?>" size="50" maxlength="50" class="form-control" />
          <span class="textfieldRequiredMsg">A name is required.</span></span></td>
        </tr>
        <tr class="grouptype">
          <td class="text-nowrap text-right"><label for="grouptypeID">Group Type:</label></td>
          <td>
            <select name="grouptypeID" id="grouptypeID"  class="form-control" >
              <option value="" <?php if (!(strcmp("", $row_rsUserGroup['grouptypeID']))) {echo "selected=\"selected\"";} ?>><?php echo isset($region['text_choose']) ? htmlentities($region['text_choose'], ENT_COMPAT, "UTF-8") : "Choose..." ?></option>
              <option value="" <?php if (!(strcmp("", $row_rsUserGroup['grouptypeID']))) {echo "selected=\"selected\"";} ?>>None</option>
              <?php
do {  
?>
              <option value="<?php echo $row_rsGroupTypes['ID']?>"<?php if (!(strcmp($row_rsGroupTypes['ID'], $row_rsUserGroup['grouptypeID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsGroupTypes['grouptype']?></option>
              <?php
} while ($row_rsGroupTypes = mysql_fetch_assoc($rsGroupTypes));
  $rows = mysql_num_rows($rsGroupTypes);
  if($rows > 0) {
      mysql_data_seek($rsGroupTypes, 0);
	  $row_rsGroupTypes = mysql_fetch_assoc($rsGroupTypes);
  }
?>
            </select>
          </td>
        </tr> <tr>
          <td class="text-nowrap text-right top">Group description:</td>
          <td><textarea name="groupdescription" cols="50" rows="5"  class="form-control" ><?php echo $row_rsUserGroup['groupdescription']; ?></textarea></td>
        </tr> 
                <tr>
          <td class="text-nowrap text-right">User opt-in/out:</td>
          <td><label>
            <input <?php if (!(strcmp($row_rsUserGroup['optin'],"1"))) {echo "checked=\"checked\"";} ?> name="optin" type="radio" id="optin" value="1" />
            Yes</label>
            &nbsp;&nbsp;&nbsp;
            <label>
              <input <?php if (!(strcmp($row_rsUserGroup['optin'],"0"))) {echo "checked=\"checked\"";} ?> name="optin" type="radio" id="optin" value="0"  />
              No </label>
            &nbsp;&nbsp;&nbsp;
            <label>
              <input <?php if (!(strcmp($row_rsUserGroup['optin'],"-1"))) {echo "checked=\"checked\"";} ?> name="optin" type="radio" id="optin" value="-1"  />
              Hidden</label></td>
        </tr> <tr>
          <td class="text-nowrap text-right">Notify email when joined:</td>
          <td><input name="notificationemail" type="text"  value="<?php echo $row_rsUserGroup['notificationemail']; ?>" size="50" maxlength="50" placeholder="(Optional)"  class="form-control" /></td>
        </tr>

        <tr>
          <td class="text-nowrap text-right"><label for="renewalcost">Cost to join/renew:</label></td>
          <td class="form-inline">
            <input name="renewalcost" type="text" id="renewalcost" value="<?php echo number_format($row_rsUserGroup['renewalcost'],2,".",""); ?>" size="10" maxlength="10"  class="form-control" ></td>
        </tr>
        <tr>
          <td class="text-nowrap text-right rank9">All sites:</td>
          <td>            <input <?php if ($row_rsUserGroup['regionID']==0) {echo "checked=\"checked\"";} ?> type="checkbox" name="allsites"/>
           </td>
        </tr>
        <tr>
          <td class="text-nowrap text-right">Active:</td>
          <td><label>
            <input <?php if (!(strcmp($row_rsUserGroup['statusID'],1))) {echo "checked=\"checked\"";} ?> type="checkbox" name="statusID" id="statusID" />
            </label></td>
        </tr> <tr>
          <td class="text-nowrap text-right">&nbsp;</td>
          <td><button type="submit" class="btn btn-primary" >Save changes</button></td>
        </tr>
      </table>
       <input name="modifiedbyID" type="hidden" id="createdbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
        <input name="modifieddatetime" type="hidden" id="createddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
        <input name="ID" type="hidden" id="ID" value="<?php echo $row_rsUserGroup['ID']; ?>" />
        <input type="hidden" name="MM_update" value="form1" />
        <input type="hidden" name="regionID" id="regionID" value="<?php echo $row_rsUserGroup['regionID']; ?>">
      </form>
   </div>
    <script>
<!--
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1");
//-->
    </script>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsUserGroup);

mysql_free_result($rsGroupTypes);
?>
