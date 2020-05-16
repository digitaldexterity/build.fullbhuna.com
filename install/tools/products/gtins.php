<?php require_once('../../../Connections/aquiescedb.php'); ?>
<?php require_once('../../../core/includes/framework.inc.php'); ?>
<?php require_once('../../../core/includes/upload.inc.php'); ?><?php
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

$MM_restrictGoTo = "../../login.php?notloggedin=true";
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

if(isset($_POST['upload'])) { // post
	$uploaded = getUploads();
	if (isset($uploaded) && is_array($uploaded)  
		&& isset($uploaded["filename"][0]["newname"]) 
		&& $uploaded["filename"][0]["newname"]!="") { 	
		$filename = UPLOAD_ROOT.$uploaded["filename"][0]["newname"];
		if(is_readable($filename) && $error=="") { // file OK
			ini_set('auto_detect_line_endings', true);
			$handle = fopen($filename,"r");
			if($handle) { // handle
				$row = 	1;
				while($error =="" && $fields = fgetcsv($handle,65535)) { // get line
					
					
					if(count($fields) != 2) {	
						$error .= "There are not 2 columns on row ".$row.". "; break; 
					} else if (isset($_POST['headings']) && $row ==1) { // get headers
						
					} else {
						foreach($fields as $column=> $value) {
							$data[$row][$column] = trim($value);									
						}				
					}	
					$row++; 			
				} // end get line
				
				if($error=="") { // no errors
					
					mysql_select_db($database_aquiescedb, $aquiescedb);
					$html = "";
					$html .= "<table>";
					foreach($data as $row => $columns) {
						$html .= "<tr>";
						$mpn = trim($columns[0]);
						$gtin = trim($columns[1]);
						
						
						$select = "SELECT product.title FROM product  WHERE TRIM(sku) LIKE ".GetSQLValueString($mpn, "text")." OR TRIM(mpn) LIKE ".GetSQLValueString($mpn, "text")."";
						
						$result = mysql_query($select, $aquiescedb) or die(mysql_error());
						$html .= "<th>SKU/MPN: ".$mpn."</th>";
						$html .= "<td>GTIN: ".$gtin."</td>";
						if(mysql_num_rows($result)>0) {
							$html .= "<td>Matches: ";
							while($row = mysql_fetch_assoc($result)) {
								$html .= $row['title']."; ";
							}
							$html .= "</td>";
						} else {
							$html .= "<td>No matches.</td>";
						}
						$update = "UPDATE product SET upc = ".GetSQLValueString($gtin, "text")." WHERE TRIM(sku) LIKE ".GetSQLValueString($mpn, "text")." OR TRIM(mpn) LIKE ".GetSQLValueString($mpn, "text")."";	
						mysql_query($update, $aquiescedb) or die(mysql_error());
						$html .= "</tr>";
					}	
					$html .= "</table>";						
				} // no errors
				// delete CSV
				unlink($filename);
			} else { // file not OK
				$error .= "Could not find the uploaded file: ".$filename." ";
			}
		} else { // read not OK
			$error .= "Could not read the uploaded file: ".$filename." ";
		}
	} // end upload
	else {
		$error .= "No file uploaded. ";
	}
	if($error=="") {
			$msg = "Products successfully inserted. ";
	}
}

?><!DOCTYPE html>
<html lang="en" class="full_bhuna install <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Install.dwt.php" codeOutsideHTMLIsLocked="false" --><!-- Copyright Paul Egan. Any unauthorised copying, reproduction or alteration is strictly prohibited -->
<head>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Upgrade Products V1 to V2</title>
<!-- InstanceEndEditable -->
<?php require_once('../../includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<!-- InstanceEndEditable -->
</head>
<body>
<?php require_once('../../includes/header.inc.php'); ?>
<main>
<div class="container"><!-- InstanceBeginEditable name="Body" -->
<h1>Import GTINs</h1>
 <?php if (isset($error)) { ?><p class="alert alert-danger" role="alert"><?php echo $error; ?></p>
  <?php } ?>
  <?php if (isset($msg)) { ?><p class="notice alert message alert-info" role="alert"><?php echo $msg; ?></p>
  <?php } ?>
  <?php echo isset($html) ? $html : ""; ?>
<p>Import spreadsheet with two columns: MPN or Stock Code, and GTIN</p>
<form method="post" id="form1" enctype="multipart/form-data">
<input name="filename" type="file" />
  <input type="submit" name="update" id="update" value="Submit" />
<input name="upload" type="hidden" value="1" />

  <label>
    <input type="checkbox" name="headings" id="headings" />
    Ignore first row as headings</label>
</form>
 
<!-- InstanceEndEditable --></div>
</main>
<?php require_once('../../includes/footer.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsAllProducts);
?>
