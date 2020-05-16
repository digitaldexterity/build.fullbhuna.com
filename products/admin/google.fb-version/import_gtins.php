<?php require_once('../../../Connections/aquiescedb.php'); ?>
<?php require_once('../../../core/includes/adminAccess.inc.php'); ?><?php require_once('../../../core/includes/framework.inc.php'); ?><?php require_once('../../../core/includes/upload.inc.php'); ?>
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

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

if(isset($_POST['fileupload'])) { // post
	$error = "";
	$uploaded = getUploads();
	if (isset($uploaded) && is_array($uploaded)  
		&& isset($uploaded["filename"][0]["newname"]) 
		&& $uploaded["filename"][0]["newname"]!="") { 	
		$filename = UPLOAD_ROOT.$uploaded["filename"][0]["newname"];
		if(is_readable($filename) && $error=="") { // file OK
			ini_set('auto_detect_line_endings', true);
			$handle = fopen($filename,"r");
			if($handle) { // handle
				$log = ""; $numcolumns=0;			
				// data in array as follows:
				// $data[row][column]
				$data = array();
				$row = 1;
				while($error =="" && $fields = fgetcsv($handle,65535)) { // get line
					$length = 0;
					
					if(count($fields) != 2) {	
						$error .= "<strong>Column count mismatch on line ".$row.".</strong><br /><br />(".count($fields)." columns in file,  2 columns chosen)<br>Please check the integrity of your CSV file: each row must have the same number  of items. For example, commas within column items will cause problems. "; break; 
					}  else {
						foreach($fields as $column=> $value) {
							$data[$row][$column] = trim($value);	
							$length += strlen(trim($value));									
						}				
					}	
					if($length>0) $row++; 			
				} // end get line
				
				if($error=="") { // no errors
					$html .= "<table>";					
					foreach($data as $row => $columns) {
						$html .="<tr><td>".$columns[0]."</td><td>".$columns[1]."</td>";
						$select = "SELECT productoptions.ID, optionname, product.title, productoptions.upc FROM productoptions LEFT JOIN product ON (productoptions.productID = product.ID) WHERE stockcode = ".GetSQLValueString(trim($columns[0]), "text")."";
						$result = mysql_query($select, $aquiescedb) or die(mysql_error());
						if(mysql_num_rows($result)>0) { // found option
							$row = mysql_fetch_assoc($result);
								$html .="<td>";
								if(isset($row['upc'])) {
									$html .= $row['upc'] ;
								}
								$html .="</td><td>OPTION MATCH ".$row['ID']."</td><td>".$row['title']." [".$row['optionname']."]</td><td>";
								if(mysql_num_rows($result)>1) {
									$html .="ERROR! more than one match";
								}
								$html .="</td>";
								
						} else { // no option
							$select = "SELECT ID, title, upc FROM product WHERE sku = ".GetSQLValueString(trim($columns[0]), "text")."";
							$result = mysql_query($select, $aquiescedb) or die(mysql_error());
							if(mysql_num_rows($result)) {
								$row = mysql_fetch_assoc($result);
								$html .="<td>";
								if(isset($row['gtin'])) {
									$html .= $row['gtin'] ;
								}
								$html .="</td><td>PRODUCT MATCH ".$row['ID']."</td><td>".$row['title']."</td>";
								$html .="<td>";
								if(mysql_num_rows($result)>1) {
									$html .="ERROR! more than one match";
								}
								$html .="</td>";
							} else {
								$html .="<td></td><td></td><td></td><td>ERROR! NO MATCH</td>";
							}
						} // end no option
						$html .="</tr>";					
					}	 // end for each		
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
}// end post
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Import GTINs"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
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
      <h1><i class="glyphicon glyphicon-shopping-cart"></i> Import GTINs</h1>
  
        <?php require_once('../../../core/includes/alert.inc.php'); ?>

      <form action="" method="post" enctype="multipart/form-data" name="form1">
        <label for="filename">Upload a 2 column CSV file. Column 1 = SKU, Column 2 = GTIN</label>
        <input type="file" name="filename" id="filename"><button type="submit">Submit</button>
        <input name="fileupload" type="hidden" id="fileupload" value="true">
      </form>
  
      <?php if(isset($html)) echo $html; ?>
    </div>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);
?>
