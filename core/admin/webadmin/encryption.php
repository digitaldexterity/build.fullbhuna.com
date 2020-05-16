<?php require_once('../../../Connections/aquiescedb.php'); ?>
<?php require_once('../../includes/adminAccess.inc.php'); ?><?php require_once('../../includes/framework.inc.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "10";
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

if(!function_exists("encryptTableField")) {
function encryptTableField($table, $field) {
	global $database_aquiescedb, $aquiescedb;
	mysql_select_db($database_aquiescedb, $aquiescedb);
	if(defined("MYSQL_SALT")) {
		$select = "SELECT encrypted_fields FROM preferences WHERE ID = 1";
		$result = mysql_query($select, $aquiescedb) or die(mysql_error());
		if(mysql_num_rows($result)>0) {
			$row = mysql_fetch_assoc($result);
			$encrypted_fields = json_decode($row['encrypted_fields'], true);
			if(!is_array($encrypted_fields[$table])) {
				$encrypted_fields[$table] = array();
			}
			if(!in_array($field, $encrypted_fields[$table])) {
				array_push($encrypted_fields[$table],$field);
			}
			// update tables here!
			
			$update = "UPDATE ".$table." SET ".$field." = AES_ENCRYPT(".$field.",'".MYSQL_SALT."')";
			mysql_query($update, $aquiescedb) or die(mysql_error());
			$update = "UPDATE preferences SET encrypted_fields = '".json_encode($encrypted_fields)."' WHERE ID = 1";
			mysql_query($update, $aquiescedb) or die(mysql_error());
		} else {
			die("Could not read perferences.");
		}
	} else {
		die("No salt");
	}
}
}

if(!function_exists("decryptTableField")) {
function decryptTableField($table, $field) {
	global $database_aquiescedb, $aquiescedb;
	mysql_select_db($database_aquiescedb, $aquiescedb);
	
	$encrypted_fields = getEncryptedFields();
	if(!is_array($encrypted_fields[$table])) {
		die("Table not encrypted");
	}
	$key = array_search($field, $encrypted_fields[$table]);
	if(isset($encrypted_fields[$table][$key])) {
		unset($encrypted_fields[$table][$key]);
	} else {
		die("Field not encrypted");
	}
	$update = "UPDATE ".$table." SET ".$field." = AES_DECRYPT(".$field.",'".MYSQL_SALT."')";
	mysql_query($update, $aquiescedb) or die(mysql_error());
	$update = "UPDATE preferences SET encrypted_fields = '".json_encode($encrypted_fields)."' WHERE ID = 1";
	mysql_query($update, $aquiescedb) or die(mysql_error());
		
}
}

if(isset($_POST['crypt'])) {
	if($_POST['crypt']==1) {
		encryptTableField($_POST['table'],$_POST['field']);
	} else {	
		decryptTableField($_POST['table'],$_POST['field']);
	}
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
$query_rsPreferences = "SELECT encrypted_fields FROM preferences WHERE ID = 1";
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Encryption"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../seo/includes/seo.inc.php'); ?>
<?php require_once('../../includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<style>
<!--
-->
</style>
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" -->
    <div class="page class">
      <h1>Encryption</h1>
      <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light">
      <!-- BS4 navbar-default depracated and container-fluid can be removed -->
        <div class="container-fluid">
          <ul class="nav navbar-nav">
           
            <li class="nav-item"><a class="nav-link" href="index.php" ><i class="glyphicon glyphicon-arrow-left"></i> Back</a></li>
          </ul>
        </div>
      </nav>
      <p>Encryption settings cover all sites on this installation.</p>
      
        <textarea name="encrypted_fields" id="encrypted_fields" class="form-control"><?php echo $row_rsPreferences['encrypted_fields']; ?></textarea>
        
        <form name="form1" method="post" action="" class="form-inline"><label><input type="radio" name="crypt" value="1" required> Encrypt</label> &nbsp;&nbsp;  <label><input type="radio" name="crypt" value="-1" required> decrypt</label>
        <input type="text" class="form-control" name="table"> 
        <input type="text" class="form-control" name="field">             
        <button type="submit" class="btn btn-primary">Save changes</button>
      </form>
      <p>&nbsp;</p>
      
    </div>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsPreferences);
?>
