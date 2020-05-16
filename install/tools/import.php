<?php require("../../Connections/aquiescedb.php"); ?>
<?php require("../includes/install.inc.php"); ?>
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

if(isset($_GET['install'])) { 			
				$file = SITE_ROOT."local/additions.sql";
				if (load_db_dump($file,$hostname_aquiescedb,$username_aquiescedb,$password_aquiescedb,$database_aquiescedb)) { // data entered
				
				
				
				} else { // data not entered
				$submit_error = "Install failed.";
				} // end data not entered
			
} // end post


?><!DOCTYPE html>
<!-- Copyright Paul Egan. Any unauthorised copying, reproduction or alteration is strictly prohibited -->
<html lang="en" class="full_bhuna install <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="../../Templates/Install.dwt.php" codeOutsideHTMLIsLocked="false" --><!-- Copyright Paul Egan. Any unauthorised copying, reproduction or alteration is strictly prohibited -->
<head>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Install tables</title>
<!-- InstanceEndEditable -->
<?php require_once('../includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" --><meta name="robots" content="noindex,nofollow" /><!-- InstanceEndEditable -->
</head>
<body>
<?php require_once('../includes/header.inc.php'); ?>
<main>
<div class="container"><!-- InstanceBeginEditable name="Body" -->
      <h1>Install Tables and Data</h1>
      <?php if (isset($submit_error)) { ?><p class="alert alert-danger" role="alert"><?php echo $submit_error; ?></p>
      <?php } else if(isset($_GET['install'])) { ?>
      <h2>Done</h2>
      <?php } ?><p>This will install tables and data of any file at /local/additions.sql</p><form method="get"><input type="hidden"  name="install"value="true" /><input type="submit" value="Install" /></form>
      
  <!-- InstanceEndEditable --></div>
</main>
<?php require_once('../includes/footer.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>