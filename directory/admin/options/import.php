<?php require_once('../../../Connections/aquiescedb.php'); ?><?php require_once('../../../core/includes/framework.inc.php'); ?><?php require_once('../../../core/includes/upload.inc.php'); ?>
<?php require_once('../../../core/includes/adminAccess.inc.php'); ?><?php require_once('../../includes/directoryfunctions.inc.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "8,9,10";
$MM_donotCheckaccess = "false";

// *** Restrict Access To Page: Grant or deny access to this page
function isAuthorized($strUsers, $strGroups, $UserName, $usergroup) { 
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
    if (in_array($usergroup, $arrGroups)) { 
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

$users = array();
$uploaded = getUploads();
if ((isset($uploaded) && is_array($uploaded) 
	&& isset($uploaded["filename"][0]["newname"]) 
	&& $uploaded["filename"][0]["newname"]!="") || (isset($_POST['uselastupload']) && $_POST['lastupload']!=="")) { // upload or last upload	
	$filename = (isset($_POST['uselastupload']) && $_POST['lastupload']!=="") ? $_POST['lastupload'] : UPLOAD_ROOT.$uploaded["filename"][0]["newname"];
	if(is_readable($filename) && !isset($error)) { // file OK
		ini_set('auto_detect_line_endings', true);
		$handle = fopen($filename,"r");
		if($handle) { // handle
			$linecount = 0; $log = "";
			
			
			/*// do integrity check first
			First Name	Surname	Photo ID	Combined Name	Start Date	Location	Title	Team 1	Team 2	Email	Work Mobile	BIOGRAPHY*/
			
			while($fields = fgetcsv($handle,65535)) { // get line
				$linecount++; 
				if(count($fields) != intval($_POST['columns'])) {	
					
					$error = "<strong>Column count mismatch on line ".$linecount." - ".count($fields)." items.</strong><br /><br />Please check the integrity of your CSV file: each row must have the ".intval($_POST['columns'])."  items. For example, commas within column items will cause problems."; break; 
					
				}
				foreach($fields as $key => $field) { 
					// count through fields
						$data[$linecount][$key] = $field;
					}
			} // end get line
			
			
				
			
			
		} else { // file not OK
			$error = "Could not find the uploaded file: ".$filename;
		}
	} else { // read not OK
		$error = "Could not read the uploaded file: ".$filename;
	}
} // end upload

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
<?php $pageTitle = "Import Directory"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
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
      <h1><i class="glyphicon glyphicon-book"></i>  Directory Import</h1>
       <?php require_once('../../../core/includes/alert.inc.php'); ?>
      <form method="post" enctype="multipart/form-data" name="form1" id="form1">
      
     
      <p>
        <label>1. Get your data ready in the form of a CSV file and select:
          <input type="file" name="filename" id="filename" />
          </label> &nbsp;&nbsp; <?php if(isset($filename)) { ?><label><input name="uselastupload">&nbsp;Use last upload</label><input name="lastupload" type="hidden" value="<?php echo htmlentities($filename, ENT_COMPAT, "UTF-8"); ?>"><?php } ?></p>
          
         
      <p>
        <label>Number of Columns:
          <input type="text" name="columns" id="columns" class="form-control" value="<?php echo (isset($_POST['columns']) && intval($_POST['columns'])>0) ? intval($_POST['columns']) : 1; ?>">
        </label>
        </p>
      <button type="submit" class="btn btn-primary">Submit</button></form>
          <?php if(isset($filename)) { ?>
          <pre>
      <?php  
		  echo $filename."<br>".$linecount."<br>";  
		  /* DELETE ALL DATA
		  $delete = "DELETE FROM users WHERE usertypeID = 7";
		  mysql_query($delete, $aquiescedb) or die(mysql_error().$delete);
		  
		  
		  $delete = "DELETE FROM usergroupmember";
		  mysql_query($delete, $aquiescedb) or die(mysql_error().$delete);
		  $delete = "DELETE FROM locationuser";
		  mysql_query($delete, $aquiescedb) or die(mysql_error().$delete);
		  */
		   ?>
      </pre>
      <?php 
	  foreach($data as $row=> $values) {
		  echo $row;
		  print_r($values);
		  $directoryID = createDirectoryEntry(2, $values[0]); echo "=".$directoryID;
		  echo "<hr>";
		  
		  
		  
	  } // for each
	 
	  }// file
	 
	  ?>
    </div>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);
?>
