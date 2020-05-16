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

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
	$_POST['period'] = $_POST['multiple']*$_POST['length'];
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE productemail SET templateID=%s, categoryID=%s, period=%s, purchasemade=%s, modifiedbyID=%s, modifieddatetime=%s, statusID=%s, viaemail=%s, viasms=%s, ignoreoptout=%s WHERE ID=%s",
                       GetSQLValueString($_POST['templateID'], "int"),
                       GetSQLValueString($_POST['categoryID'], "int"),
                       GetSQLValueString($_POST['period'], "int"),
                       GetSQLValueString($_POST['purchasemade'], "int"),
                       GetSQLValueString($_POST['modifiedbyID'], "int"),
                       GetSQLValueString($_POST['modifieddatetime'], "date"),
                       GetSQLValueString(isset($_POST['statusID']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['viaemail']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['viasms']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['ignoreoptout']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {

  $updateGoTo = "index.php?defaultTab=2";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo)); exit;
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

$colname_rsFollowUp = "-1";
if (isset($_GET['followupID'])) {
  $colname_rsFollowUp = $_GET['followupID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsFollowUp = sprintf("SELECT * FROM productemail WHERE ID = %s", GetSQLValueString($colname_rsFollowUp, "int"));
$rsFollowUp = mysql_query($query_rsFollowUp, $aquiescedb) or die(mysql_error());
$row_rsFollowUp = mysql_fetch_assoc($rsFollowUp);
$totalRows_rsFollowUp = mysql_num_rows($rsFollowUp);

$varRegionID_rsTemplate = "1";
if (isset($regionID)) {
  $varRegionID_rsTemplate = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsTemplate = sprintf("SELECT groupemailtemplate.ID, groupemailtemplate.templatename FROM groupemailtemplate WHERE groupemailtemplate.regionID = %s ORDER BY groupemailtemplate.templatename", GetSQLValueString($varRegionID_rsTemplate, "int"));
$rsTemplate = mysql_query($query_rsTemplate, $aquiescedb) or die(mysql_error());
$row_rsTemplate = mysql_fetch_assoc($rsTemplate);
$totalRows_rsTemplate = mysql_num_rows($rsTemplate);

$varRegionID_rsCategories = "1";
if (isset($regionID)) {
  $varRegionID_rsCategories = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsCategories = sprintf("SELECT productcategory.ID, productcategory.title FROM productcategory WHERE productcategory.statusID = 1 AND (productcategory.regionID = %s   OR productcategory.regionID =0) GROUP BY productcategory.ID ORDER BY productcategory.title ASC", GetSQLValueString($varRegionID_rsCategories, "int"));
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
<?php $pageTitle = "Update Follow Up"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<style>
<!--
-->
</style>
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
    <div class="page class">
      <h1>Update Follow Up</h1>
     
      <form method="post" name="form1" action="<?php echo $editFormAction; ?>">
        <table class="form-table">
          
          <tr><?php if($row_rsFollowUp['period']>=604800 &&  $row_rsFollowUp['period']/604800 == intval($row_rsFollowUp['period']/604800)) {
			  $multiple = $row_rsFollowUp['period']/604800;
			  $length = 604800;
		  } else  if($row_rsFollowUp['period']>=86400 &&  $row_rsFollowUp['period']/86400 == intval($row_rsFollowUp['period']/86400)) {
			  $multiple = $row_rsFollowUp['period']/86400;
			  $length = 86400;
		  }
		  else  if($row_rsFollowUp['period']>=3600 &&  $row_rsFollowUp['period']/3600 == intval($row_rsFollowUp['period']/3600)) {
			  $multiple = $row_rsFollowUp['period']/3600;
			  $length = 3600;
		  }
		  else {
			  $multiple = $row_rsFollowUp['period']/60;
			  $length = 60;
		  } ?>
            <td nowrap align="right">Period:</td>
            <td class="form-inline"><input type="hidden" name="period" value="<?php echo htmlentities($row_rsFollowUp['period'], ENT_COMPAT, 'utf-8'); ?>" ><input name="multiple"  type="text" size="5" maxlength="5"  class="form-control" value="<?php echo intval($multiple); ?>"> 
                <select name="length"  class="form-control">
                    <option value="60" <?php if($length == 60) echo " selected "; ?> >minutes</option>
                  <option value="3600" <?php if($length == 3600) echo " selected "; ?> >hours</option>
                  <option value="86400"  <?php if($length == 86400) echo " selected "; ?>>days</option>
                  <option value="604800"  <?php if($length == 604800) echo " selected "; ?>>weeks</option>
                </select> after
                <select name="purchasemade"  class="form-control">
                <option value="2" <?php if($row_rsFollowUp['purchasemade']==2) echo " selected ";?> >paid transactions</option>
                  <option value="1" <?php if($row_rsFollowUp['purchasemade']==1) echo " selected ";?> >all transactions</option>
                  <option value="0" <?php if($row_rsFollowUp['purchasemade']==0) echo " selected ";?> >checkout abandoned</option>
              </select></td>
          </tr>
          <tr>
            <td nowrap align="right">Category:</td>
            <td><select name="categoryID" class="form-control">
            <option value="0" <?php if ($row_rsCategories['ID']==0) {echo "SELECTED";} ?> >Any category</option>
              <?php 
do {  
?>
              <option value="<?php echo $row_rsCategories['ID']?>" <?php if (!(strcmp($row_rsCategories['ID'], htmlentities($row_rsFollowUp['categoryID'], ENT_COMPAT, 'utf-8')))) {echo "SELECTED";} ?>><?php echo $row_rsCategories['title']?></option>
              <?php
} while ($row_rsCategories = mysql_fetch_assoc($rsCategories));
?>
            </select></td></tr>
            <tr>
            <td nowrap align="right">Template:</td>
            <td><select name="templateID" class="form-control">
              <?php 
do {  
?>
              <option value="<?php echo $row_rsTemplate['ID']?>" <?php if (!(strcmp($row_rsTemplate['ID'], htmlentities($row_rsFollowUp['templateID'], ENT_COMPAT, 'utf-8')))) {echo "SELECTED";} ?>><?php echo $row_rsTemplate['templatename']?></option>
              <?php
} while ($row_rsTemplate = mysql_fetch_assoc($rsTemplate));
?>
            </select></td>
          </tr>
          
          <tr>
            <td nowrap align="right">Via email:</td>
            <td><input type="checkbox" name="viaemail" value="1"  <?php if ($row_rsFollowUp['viaemail']==1) {echo "checked=\"checked\"";} ?>></td>
          </tr>
          <tr>
            <td nowrap align="right">Via sms:</td>
            <td><input type="checkbox" name="viasms" value="1"  <?php if ($row_rsFollowUp['viasms']==1) {echo "checked=\"checked\"";} ?>></td>
          </tr><tr>
            <td nowrap align="right">Active:</td>
            <td><input type="checkbox" name="statusID" value="1"  <?php if ($row_rsFollowUp['statusID']==1) {echo "checked=\"checked\"";} ?>></td>
          </tr>
          <tr>
            <td nowrap align="right">Override opt-out:</td>
            <td><input type="checkbox" name="ignoreoptout" value="1"  <?php if ($row_rsFollowUp['ignoreoptout']==1) {echo "checked=\"checked\"";} ?> onClick="if(this.checked) alert('This will send to users who have opted out of receiving communications. Please enure this complies with GDPR or similar legislation');" ></td>
          </tr>
          <tr>
            <td nowrap align="right">&nbsp;</td>
            <td><button type="submit" class="btn btn-primary" >Save changes</button></td>
          </tr>
        </table>
        <input type="hidden" name="ID" value="<?php echo $row_rsFollowUp['ID']; ?>">
        <input type="hidden" name="modifiedbyID" value="<?php echo htmlentities($row_rsLoggedIn['ID']); ?>">
        <input type="hidden" name="modifieddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>">
        <input type="hidden" name="MM_update" value="form1">
        <input type="hidden" name="ID" value="<?php echo $row_rsFollowUp['ID']; ?>">
      </form>
      
    </div>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsFollowUp);

mysql_free_result($rsTemplate);

mysql_free_result($rsCategories);
?>
