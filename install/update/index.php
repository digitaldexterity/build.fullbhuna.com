<?php 
require_once('../../Connections/aquiescedb.php');
?><?php require_once('../includes/install.inc.php'); ?>
<?php



if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{
	global $fb_mysqli_con;
  if (PHP_VERSION < 6) {
    $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
  }

  $theValue = function_exists("mysqli_real_escape_string") ? mysqli_real_escape_string($fb_mysqli_con, $theValue) : mysqli_escape_string($fb_mysqli_con, $theValue);

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

$MM_restrictGoTo = "../login.php";
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
<!DOCTYPE html>
<html lang="en" class="full_bhuna install <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Install.dwt.php" codeOutsideHTMLIsLocked="false" --><!-- Copyright Paul Egan. Any unauthorised copying, reproduction or alteration is strictly prohibited -->
<head>
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php echo $site_name; ?> - Update Full Bhuna</title>
<!-- InstanceEndEditable -->
<?php require_once('../includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script>
function doUpdate() {
	document.getElementById('update_button').disabled=true;
	$("#update_results").append("Starting...<br>");
	$.get("ajax/prepareTables.ajax.php",function(data){
		$("#update_results").append(data);
		addTempTables();
	
	});
}

function addTempTables() {
	$("#update_results").append("Adding temporary tables...<br>");
	$.get("ajax/addTempTables.ajax.php",$("#form1").serialize(),function(data, status){
		$("#update_results").append(data);
		updateTables();	
	});
}

function updateTables() {
	$("#update_results").append("Updating tables...<br>");
	$.get("ajax/updateTables.ajax.php",$("#form1").serialize(),function(data){
		$("#update_results").append(data);	
		convertToUTF();
	});
}


function convertToUTF() {
	if(document.getElementById("utf8").checked) {
		$("#update_results").append("Converting tables to UTF...<br>");
		$.get("ajax/convertToUTF.ajax.php",function(data){
			$("#update_results").append(data);
			cleanTables();	
		});
	} else {
		cleanTables();
	}	
}

function cleanTables() {
	if(document.getElementById("clean").checked) {
		$("#update_results").append("Cleaning tables...<br>");
		$.get("ajax/cleanTables.ajax.php",function(data){
			$("#update_results").append(data);
			optimizeTables();	
		});
	} else {
		optimizeTables();
	}	
}

function optimizeTables() {
	if(document.getElementById("optimise").checked) {
		$("#update_results").append("Optimising tables...<br>");
		$.ajax({ 
		timeout: 60000,
		url: "ajax/optimizeTables.ajax.php",
		error: function(data){ 
			$("#update_results").append("ERROR: Timeout<br>");
		},
		complete: function(data){ 
			// complete means on error OR success
			$("#update_results").append(data);
			cleanUpFiles();	
		}});
	} else {
		cleanUpFiles();
	}	
}

function cleanUpFiles() {
	$("#update_results").append("Creating default directories with correct permissions and deleting surplus files...<br>");
	$.get("ajax/cleanUpFiles.ajax.php",function(data){
		$("#update_results").append(data);	
		finish();
	});
}

function finish() {
	$("#update_results").append("FINISHED!<br>");
	document.getElementById('update_button').disabled=false;
}



</script><!-- InstanceEndEditable -->
</head>
<body>
<?php require_once('../includes/header.inc.php'); ?>
<main>
<div class="container"><!-- InstanceBeginEditable name="Body" -->
<h1>Update Full Bhuna</h1>
<p>This script updates all the tables in any current database to the most recent versions to allow you to add any new features to an existing database.</p>
<p>This is not guaranteed to work with all installations but will help being tables as much up-to-date as possible.</p>
<p>MAKE SURE THE fullbhuna.sql FILE IN THE /install/ FOLDER IS THE MOST RECENT VERSION AND WE RECOMMEND YOU <a href="../../core/admin/backup/index.php">BACK UP YOUR DATABASE</a> FIRST!</p>

<form  method="get" id="form1"  class="form-inline">
  <label>
    <input type="radio" name="sqlfile" value="1" id="sqlfile_0" checked  />&nbsp;Standard tables</label>
  &nbsp;&nbsp;&nbsp;
  <label>
    <input type="radio" name="sqlfile" value="2" id="sqlfile_1"  />&nbsp;Additional tables (optional /local/additions.sql)</label><br />
  &nbsp;&nbsp;&nbsp;
<label>
    <input type="checkbox" name="optimise" id="optimise" value="1" checked="checked" />&nbsp;Optimise tables after update</label>
  
 &nbsp;&nbsp;&nbsp;
  <label>
    <input name="utf8" type="checkbox" id="utf8" value="1" />&nbsp;Convert to UTF-8</label>
 &nbsp;&nbsp;&nbsp;
  <label>
    <input type="checkbox" name="clean" id="clean" onclick="if(this.checked) { alert('Any redundant data within databse will be deleted. Only use this option if you know what you are doing!'); }" />&nbsp;Clean linking tables</label>

    
    <br><button type="button" id="update_button" class="btn btn-primary" onClick="doUpdate()" >Upgrade tables</button><input name="update" type="hidden" id="update" value="true" />  
    
    
</form>
<div id="update_results"></div>
<!-- InstanceEndEditable --></div>
</main>
<?php require_once('../includes/footer.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
