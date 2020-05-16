<?php require_once('../../../Connections/aquiescedb.php'); ?>
<?php require_once('../../../core/includes/adminAccess.inc.php'); ?><?php require_once('../../includes/functions.inc.php'); ?>
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



if(isset($_POST["allsites"])) { $_POST["regionID"] = 0; }
mysql_select_db($database_aquiescedb, $aquiescedb);

$error = "";

// check for error
if (isset($_POST["mergename"])) {
	$_POST["mergename"] = trim($_POST["mergename"]);
	if(strpos($_POST["mergename"], "{")===false || strpos($_POST["mergename"], "}")!=strlen($_POST["mergename"])-1) {
		$error .= "Your merge name must be wrapped in braces {}. \n";
	}
	
	
	
	
  	$result = mysql_query($select, $aquiescedb) or die(mysql_error());
  	if(mergeExists($_POST["mergename"], $_POST['regionID'])) {
	  	$error .= "There is already a merge field with this name. \n";
  	}
	

}



if($error!="") unset($_POST["MM_insert"]);

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
  $insertSQL = sprintf("INSERT INTO merge (mergename, mergetext, createdbyID, createddatetime, modifiedbyID, modifieddatetime, statusID, regionID, mergeincludeURL) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['mergename'], "text"),
                       GetSQLValueString($_POST['mergetext'], "text"),
                       GetSQLValueString($_POST['createdbyID'], "int"),
                       GetSQLValueString($_POST['createddatetime'], "date"),
                       GetSQLValueString($_POST['modifiedbyID'], "int"),
                       GetSQLValueString($_POST['modifieddatetime'], "date"),
                       GetSQLValueString($_POST['statusID'], "int"),
                       GetSQLValueString($_POST['regionID'], "int"),
                       GetSQLValueString($_POST['mergeincludeURL'], "text"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($insertSQL, $aquiescedb) or die(mysql_error());

  $insertGoTo = "index.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
    $insertGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $insertGoTo));
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
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Create Add-in"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" --><?php require_once('../../../core/tinymce/tinymce.inc.php'); ?>
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
    <div class="page articles">
      <h1><i class="glyphicon glyphicon-file"></i> Create Add-in</h1>
    <?php require_once('../../../core/includes/alert.inc.php'); ?>
      <form method="post" name="form1" action="<?php echo $editFormAction; ?>">
        <table class="form-table">
          <tr>
            <td class="text-nowrap text-right" >Merge name:</td>
            <td><input name="mergename" type="text" size="50" maxlength="50" placeholder="{example}" value="<?php echo isset($_POST['mergename']) ? htmlentities($_POST['mergename'], ENT_COMPAT, "UTF-8") : ""; ?>"  class="form-control"></td>
          </tr>
          <tr>
            <td class="text-nowrap text-right top">Add-in text:</td>
            <td><textarea name="mergetext" cols="50" rows="5"  class="form-control tinymce"><?php echo isset($_POST['mergetext']) ? htmlentities($_POST['mergetext'], ENT_COMPAT, "UTF-8") : ""; ?></textarea></td>
          </tr>
          <tr> 
            <td class="text-nowrap text-right">or include path:</td>
            <td><input name="mergeincludeURL"  class="form-control" type="text" size="50" maxlength="255" placeholder="(optional)" value="<?php echo isset($_POST['mergeincludeURL']) ? htmlentities($_POST['mergeincludeURL'], ENT_COMPAT, "UTF-8") : ""; ?>"></td>
          </tr> <tr class="region">
            <td class="text-right text-nowrap">All sites:</td>
            <td><label>
              <input type="checkbox" name="allsites" id="allsites">
              <input name="regionID" type="hidden" id="regionID" value="<?php echo $regionID; ?>">
            </label></td>
          </tr>
          <tr>
            <td class="text-nowrap text-right">&nbsp;</td>
            <td><button type="submit" class="btn btn-primary">Add merge field</button></td>
          </tr>
       
        </table>
        <input type="hidden" name="createdbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>">
        <input type="hidden" name="createddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>">
        <input type="hidden" name="modifiedbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>">
        <input type="hidden" name="modifieddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>">
        <input type="hidden" name="statusID" value="1">
        <input type="hidden" name="MM_insert" value="form1">
      </form>
    
    </div>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);
?>
