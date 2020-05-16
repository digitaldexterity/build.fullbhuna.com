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

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
  $insertSQL = sprintf("INSERT INTO usergroup (regionID, groupname, groupdescription, optin, grouptypeID, createdbyID, createddatetime, notificationemail) VALUES (%s, %s, %s, %s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['regionID'], "int"),
                       GetSQLValueString($_POST['groupname'], "text"),
                       GetSQLValueString($_POST['groupdescription'], "text"),
                       GetSQLValueString($_POST['optin'], "int"),
                       GetSQLValueString($_POST['grouptypeID'], "int"),
                       GetSQLValueString($_POST['createdbyID'], "int"),
                       GetSQLValueString($_POST['createddatetime'], "date"),
                       GetSQLValueString($_POST['notificationemail'], "text"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($insertSQL, $aquiescedb) or die(mysql_error());
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
	if($_POST['usertypeID']>=0) { // auto add users to group
	$groupID = mysql_insert_id();
	$select = "SELECT ID FROM users WHERE ((usertypeID = ".GetSQLValueString($_POST['usertypeID'], "int")." OR ".GetSQLValueString($_POST['usertypeID'], "int")." = 0) AND (DATE(dateadded) >= ".GetSQLValueString($_POST['startdate'], "date")." OR ".GetSQLValueString($_POST['startdate'], "date")." IS NULL) AND (DATE(dateadded) <= ".GetSQLValueString($_POST['enddate'], "date")." OR ".GetSQLValueString($_POST['enddate'], "date")." IS NULL) AND (regionID = 0 OR regionID =".$regionID."))";
	
	$result = mysql_query($select, $aquiescedb) or die(mysql_error());
	while($row = mysql_fetch_assoc($result)) {
		$insert = "INSERT INTO usergroupmember (userID, groupID, createdbyID, createddatetime) VALUES (".$row['ID'].",".$groupID.",".GetSQLValueString($_POST['createdbyID'], "int").",NOW())";
			$result2 = mysql_query($insert, $aquiescedb) or die(mysql_error());																																									   
	} // end while
	
	} // auto add
  $insertGoTo = "index.php?grouptypeID=".intval($_POST['grouptypeID']);
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

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsUserLevels = "SELECT usertype.ID, CONCAT(usertype.name,'s') AS name FROM usertype WHERE ID > 0 ORDER BY ID ASC";
$rsUserLevels = mysql_query($query_rsUserLevels, $aquiescedb) or die(mysql_error());
$row_rsUserLevels = mysql_fetch_assoc($rsUserLevels);
$totalRows_rsUserLevels = mysql_num_rows($rsUserLevels);

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
<title><?php $pageTitle = "Add User Group"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
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
    <h1><i class="glyphicon glyphicon-user"></i> Add User Group </h1><form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1">
      <table class="form-table"> <tr>
          <td class="text-nowrap text-right">Group name:</td>
          <td><span id="sprytextfield1">
            <input name="groupname" type="text"  class="form-control"  size="50" maxlength="50" />
          <span class="textfieldRequiredMsg">A name is required.</span></span></td>
        </tr>
        <tr class="grouptype">
          <td class="text-nowrap text-right"><label for="grouptypeID">Group Type:</label></td>
          <td>
            <select name="grouptypeID" id="grouptypeID"  class="form-control" >
              <option value=""><?php echo isset($region['text_choose']) ? htmlentities($region['text_choose'], ENT_COMPAT, "UTF-8") : "Choose..." ?></option>
              <option value="">None</option>
              <?php
do {  
?>
              <option value="<?php echo $row_rsGroupTypes['ID']; ?>" <?php if($row_rsGroupTypes['ID']==@$_GET['grouptypeID']) echo "selected=\"selected\""; ?>><?php echo $row_rsGroupTypes['grouptype']?></option>
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
          <td><textarea name="groupdescription" cols="50" rows="5"  class="form-control" ></textarea></td>
        </tr><tr>
          <td class="text-nowrap text-right">User opt-in/out:</td>
          <td><label>
            <input name="optin" type="radio" id="optin" value="1" /> Yes</label>&nbsp;&nbsp;&nbsp;
           <label> <input name="optin" type="radio" id="optin" value="0"  /> No </label>&nbsp;&nbsp;&nbsp;
           <label> <input name="optin" type="radio" id="optin" value="-1" checked /> Hidden</label></td>
        </tr> 
        <tr>
          <td class="text-nowrap text-right">Notify email when joined:</td>
          <td><input name="notificationemail" type="text"  value="" size="50" maxlength="50" placeholder="(Optional)"  class="form-control"  /></td>
        </tr>
        <tr>
          <td class="text-nowrap text-right">Automatically add:</td>
          <td><select name="usertypeID" id="usertypeID"  class="form-control" >
            <option value="-1">No users</option>
            <option value="0">All users</option>
            <?php
do {  
?>
            <option value="<?php echo $row_rsUserLevels['ID']?>"><?php echo $row_rsUserLevels['name']?></option>
            <?php
} while ($row_rsUserLevels = mysql_fetch_assoc($rsUserLevels));
  $rows = mysql_num_rows($rsUserLevels);
  if($rows > 0) {
      mysql_data_seek($rsUserLevels, 0);
	  $row_rsUserLevels = mysql_fetch_assoc($rsUserLevels);
  }
?>
          </select></td>
        </tr> <tr>
          <td class="text-nowrap text-right">added between:</td>
          <td><input type="hidden" name="startdate" id="startdate" value="<?php $inputname = "startdate"; ?>"/><?php require('../../../core/includes/datetimeinput.inc.php'); ?>&nbsp;and&nbsp;
          <input type="hidden" name="enddate" id="enddate" value="<?php $inputname = "enddate"; ?>" /><?php require('../../../core/includes/datetimeinput.inc.php'); ?></td>
        </tr> <tr>
          <td class="text-nowrap text-right">&nbsp;</td>
          <td><button type="submit" class="btn btn-primary" >Add group</button></td>
        </tr>
      </table>
   <input name="createdbyID" type="hidden" id="createdbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
        <input name="createddatetime" type="hidden" id="createddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
      <input type="hidden" name="MM_insert" value="form1" /> <input type="hidden" name="regionID" value="<?php echo $regionID; ?>" />
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

mysql_free_result($rsUserLevels);

mysql_free_result($rsGroupTypes);
?>
