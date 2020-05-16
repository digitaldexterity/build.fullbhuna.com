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
$submit_error = "";
require_once('../../../core/includes/framework.inc.php'); 

require_once('includes/importCSV.inc.php');

// import data

// $_POST['unique'] = comma deliminated field containing fields (if any) to check for duplicates. If two or more then a combination of BOTH columns must be unique

if(isset($_FILES['filename'])) { // uploaded file
	$msg = ""; $submit_error = "";
	
	$uniquefields = (isset($_POST['unique']) && $_POST['unique']!="") ?  explode(",",$_POST['unique']) : array(); 
		
	if(strpos($_FILES['filename']['name'],".csv")<3) {
		$submit_error .= "The uploaded file was not a CSV file. ";
	} else {
	
	require_once('../../../core/includes/upload.inc.php'); 
	$uploaded = getUploads();
	
	if(is_array($uploaded['filename']) && isset($uploaded['filename'][0]['newname']) && is_readable(UPLOAD_ROOT.$uploaded['filename'][0]['newname']) && !isset($uploaded['filename'][0]['error'])) { // read OK
		$filename = UPLOAD_ROOT.$uploaded['filename'][0]['newname'];
		$uploadID = $uploaded['filename'][0]['uploadID'];
		$errorcheck = isset($_POST['errorcheck']) ? $_POST['errorcheck'] : "";
		$errors = insertCSVtoTable($filename, $_POST['tablename'], $_POST['column'], $uniquefields, $_POST['createdbyID'],$uploadID,$errorcheck);
		$submit_error .= implode("<br />",$errors['error']);
		$msg .= implode("<br />",$errors['message']);
		
	} // read OK
	else { $submit_error .= "Could not read the uploaded file: ".$filename; }
	}
} // end upload


if(isset($submit_error) && $submit_error=="" && isset($_POST['returnURL']) && $_POST['returnURL'] !="") { // return
	$returnURL = (isset($_POST['returnURL']) && $_POST['returnURL']!="") ? $_POST['returnURL'] : "index.php";
	$returnURL .= strpos($returnURL,"?") ? "&" : "?";
	$returnURL .= strlen($msg)>0 ? "msg=".urlencode($msg) : "";
	header("location: ".$returnURL); exit;
}
?>
<?php
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
<?php $pageTitle = "Import CSV"; echo $pageTitle." | ".$site_name; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<link href="/documents/css/documentsDefault.css" rel="stylesheet"  />
 <style >
<!--
#tablename {
	display: none;
}
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
    <h1><i class="glyphicon glyphicon-folder-open"></i> Import CSV</h1>
    <?php if(isset($submit_error) && $submit_error!="") { ?><p class="alert warning alert-warning" role="alert"><?php echo $submit_error; ?></p><?php } ?>
    <?php if(isset($msg) && $msg!="") { ?><p class="message alert alert-info" role="alert"><?php echo $msg; ?></p><?php } ?>
    <form action="" method="post" enctype="multipart/form-data" name="form1" id="form1">
      
        <?php mysql_select_db($database_aquiescedb, $aquiescedb);
$q = "SHOW TABLES FROM ".$database_aquiescedb;//." LIKE 'bs_%'";
$rs = mysql_query($q, $aquiescedb) or die(mysql_error()); ?>
        <select name="tablename" id="tablename">
          <option value="">Choose table...</option>
          <?php while($master_table = mysql_fetch_array($rs)) { ?>
          <option value="<?php echo $master_table[0]; ?>" <?php if(isset($_GET['tablename']) && $_GET['tablename'] == $master_table[0]) echo "selected=\"selected\""; ?>><?php echo $master_table[0]; ?></option>
          <?php } ?>
        </select>
     

      <p>
        <input type="file" name="filename" id="filename" />
      </p><p><?php require_once('includes/columns.inc.php'); ?></p>
      <p>
        <button type="submit" class="btn btn-primary">Upload...</button>
        <input name="createdbyID" type="hidden" id="createdbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
        <input name="createddatetime" type="hidden" id="createddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
        <input type="hidden" name="returnURL" id="returnURL" value="<?php echo isset($_GET['returnURL'] ) ? $_GET['returnURL'] : ""; ?>" />
        <input name="unique" type="hidden" id="unique" value="<?php echo htmlentities($_GET['unique']); ?>" />
      </p>
    </form>
    <p>&nbsp;</p>
  <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>