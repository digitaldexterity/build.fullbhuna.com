<?php require_once('../../../Connections/aquiescedb.php'); ?>
<?php require_once('../../../core/includes/adminAccess.inc.php'); ?>
<?php require_once('../../../location/includes/locationfunctions.inc.php'); ?>
<?php require_once('../../../core/includes/framework.inc.php'); ?>
<?php require_once('../inc/product_functions.inc.php'); ?>
<?php require_once('../../../core/includes/upload.inc.php'); ?>
<?php 
set_time_limit(600); // 10 mins
ini_set("session.gc_maxlifetime","10800");
ini_set("max_execution_time","600"); // 10 mins
ini_set("max_input_time","600"); // 10 mins
$error="";
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "9,10";
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

if(isset($_GET['deleteall'])) {
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$result = mysql_list_tables($database_aquiescedb); 
	$msg = "";
	// delete linked galleries first
	$delete = "DELETE photos, photocategories FROM photos LEFT JOIN photocategories ON (photos.categoryID = photocategories.ID) LEFT JOIN productgallery ON (productgallery.galleryID = photocategories.ID) WHERE productgallery.productID IS NOT NULL";
	mysql_query($delete, $aquiescedb) or die(mysql_error().": ".$delete);	
	for($x = 0; $x < mysql_num_rows($result); $x++) { 
		$table = mysql_tablename($result, $x);
		if (!empty($table)) { 
			if($table !="productprefs" && strpos($table,"product")!==false) {
				$delete = "DELETE FROM ".$table." WHERE ID IS NOT NULL";
				// without the WHERE affected rows returns 0
				mysql_query($delete, $aquiescedb) or die(mysql_error().": ".$delete);	
				if($table == "product") {
					$deletedproducts = mysql_affected_rows();
				}
			}
		} 
	} 
	
	$msg .= "All ".$deletedproducts." products deleted. Associated galleries deleted.";
}

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID, usertypeID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsGroups = "SELECT ID, groupname FROM usergroup ORDER BY groupname ASC";
$rsGroups = mysql_query($query_rsGroups, $aquiescedb) or die(mysql_error());
$row_rsGroups = mysql_fetch_assoc($rsGroups);
$totalRows_rsGroups = mysql_num_rows($rsGroups);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsRegions = "SELECT ID, title FROM region WHERE statusID = 1 ORDER BY title ASC";
$rsRegions = mysql_query($query_rsRegions, $aquiescedb) or die(mysql_error());
$row_rsRegions = mysql_fetch_assoc($rsRegions);
$totalRows_rsRegions = mysql_num_rows($rsRegions);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsUserTypes = "SELECT * FROM usertype ORDER BY ID ASC";
$rsUserTypes = mysql_query($query_rsUserTypes, $aquiescedb) or die(mysql_error());
$row_rsUserTypes = mysql_fetch_assoc($rsUserTypes);
$totalRows_rsUserTypes = mysql_num_rows($rsUserTypes);

$varRegionID_rsProductPrefs = "1";
if (isset($regionID)) {
  $varRegionID_rsProductPrefs = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsProductPrefs = sprintf("SELECT importsettings FROM productprefs WHERE ID = %s", GetSQLValueString($varRegionID_rsProductPrefs, "int"));
$rsProductPrefs = mysql_query($query_rsProductPrefs, $aquiescedb) or die(mysql_error());
$row_rsProductPrefs = mysql_fetch_assoc($rsProductPrefs);
$totalRows_rsProductPrefs = mysql_num_rows($rsProductPrefs);

$setting = explode(":",$row_rsProductPrefs['importsettings']);

if(isset($_POST['createdbyID'])) { // post
	$uploaded = getUploads(); $msg = ""; $count = 0;
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
				$fieldname = array();	
				$headers = array();	
				$options = array();
				$settings = "";	
				// get post data structure
				foreach($_POST['column'] as $column => $columnname) {				
					if($columnname != "0") { //is column	
						$numcolumns++; 
						$settings .= $columnname.":";
						if($columnname != -1) {
							if(substr($columnname,0,5)=="multi") {
								// for multiple allowed columns add suffix
								$columnname .= "-".$column-1;
							}
							if(!isset($fieldname[$columnname])) {							
								$fieldname[$columnname] = $column-1;
							} else {
								$error .= "You can only import columns without asterisk (*) once. "; break;
							}						
						}
					}
				} // end for each
				if(isset($_REQUEST['savesettings'])) {
					$update = "UPDATE productprefs SET importsettings = '".trim($settings,":")."' WHERE ID = ".$regionID;
					mysql_query($update, $aquiescedb) or die(mysql_error());	
				}
				$row = 1;
				while($error =="" && $fields = fgetcsv($handle,65535)) { // get line
					$length = 0;
					
					if(count($fields) != $numcolumns) {	
						$error .= "<strong>Column count mismatch on line ".$row.".</strong><br /><br />(".count($fields)." columns in file,  ".$numcolumns." columns chosen)<br>Please check the integrity of your CSV file: each row must have the same number  of items. For example, commas within column items will cause problems. "; break; 
					} else if (isset($_POST['headings']) && $row ==1) { // get headers
						foreach($fields as $column=> $value) {
							$headers[$column] = $value;	
							$length += strlen(trim($value));			
						}	
						//print_r($headers);die();	
					} else {
						foreach($fields as $column=> $value) {
							$data[$row][$column] = trim($value);	
							$length += strlen(trim($value));									
						}				
					}	
					if($length>0) $row++; 			
				} // end get line
				
				if($error=="") { // no errors
					$matchtitle = isset($_POST['updatematches']) ? 1 : 0;
					
					foreach($data as $row => $columns) {
						$options = array();	
						$minprice = 0;	// to show min option price			
						//format description
						$productdescription = isset($data[$row][$fieldname['description1']]) ? $data[$row][$fieldname['description1']] : "";
						$producttitle = isset($data[$row][$fieldname['title']]) ? $data[$row][$fieldname['title']] : "";
						foreach($fieldname as $key=>$value) { 
							if(substr($key,0,18)=="multi|description4") {
								$productdescription .= "<div><span>".$columns[$key]."</span>:<span>".$data[$row][$fieldname[$key]]."</span></div>";
							}
							if(substr($key,0,12)=="multi|title2") {
								$producttitle .= " ".$data[$row][$fieldname[$key]]; 
							}
							// get all options first to collate later
							if(substr($key,0,13)=="productoption") {
								$options[substr($key,20,1)][substr($key,21)] = $data[$row][@$fieldname[$key]];
								if(substr($key,21)=="price" && $data[$row][$fieldname[$key]] >0 && ($data[$row][$fieldname[$key]] < $minprice || $minprice==0)) {
									$minprice=$data[$row][$fieldname[$key]];									 
								}
							}						
						} // end for each
						$price = (!isset($data[$row][$fieldname['price']]) || ($minprice>0 && $data[$row][$fieldname['price']]>$minprice)) ? $minprice : $data[$row][$fieldname['price']];
						$productdescription = utf8_encode(nl2br($productdescription));
						$length = isset($data[$row][$fieldname['int_length']]) ? $data[$row][$fieldname['int_length']]: "";
						$height = isset($data[$row][$fieldname['int_height']]) ? $data[$row][$fieldname['int_height']]: "";
						$width = isset($data[$row][$fieldname['int_width']]) ? $data[$row][$fieldname['int_width']]: "";
						if(isset($data[$row][$fieldname['dimensions']])) {
								$dimensions = explode("x",strtolower($data[$row][$fieldname['dimensions']]),3);
								$length = isset($dimensions[0]) ? trim($dimensions[0]) : $length;
								$height = isset($dimensions[1]) ? trim($dimensions[1]) : $height;
								$width = isset($dimensions[2]) ? trim($dimensions[2]) : $width;
						}
						$id = isset($data[$row][@$fieldname['ID']]) ? $data[$row][@$fieldname['ID']] : 0;
						$testmode = isset($_POST['testmode']) ? 1 : 0;
						$returnvalue = addUpdateProduct($id , $_POST['import_mode'], $row_rsLoggedIn['ID'],$matchtitle,
							$producttitle, 
							$productdescription, 
							$price, 
							@$data[$row][@$fieldname['rrp']], 
							@$data[$row][@$fieldname['sku']], 
							@$data[$row][@$fieldname['imageURL']], 
							@$data[$row][@$fieldname['box_length']],
							@$data[$row][@$fieldname['box_width']], 
							@$data[$row][@$fieldname['box_height']], 
							@$data[$row][@$fieldname['weight']],
							@$data[$row][@$fieldname['volume']], 
							$length,
							$width, 
							$height,
							@$data[$row][@$fieldname['area']],
							$regionID,
							@$data[$row][@$fieldname['mpn']],
							@$data[$row][@$fieldname['upc']],
							@$data[$row][@$fieldname['condition']],
							@$data[$row][@$fieldname['longID']],
							@$data[$row][@$fieldname['instock']],
							@$data[$row][@$fieldname['availabledate']],
							$testmode);	
						if($testmode==0 && is_array($returnvalue)>0) { // product or option inserted or updated	
							$count ++;	
							if(isset($returnvalue['productID']) && $returnvalue['productID']>0) { // product added or updated		
								$categoryID = addCategory(@$data[$row][@$fieldname['category']], 0,$row_rsLoggedIn['ID']);
								$subcatID = addCategory(@$data[$row][@$fieldname['subcat']], $categoryID ,$row_rsLoggedIn['ID']);					
								if($subcatID >0) {
									addProductToCategory($productID, $subcatID, 1, $row_rsLoggedIn['ID']); 
								} else {
									addProductToCategory($productID, $categoryID, 1, $row_rsLoggedIn['ID']);
								}					
								
								$manufacturerID=0; $manufacturerrangeID=0;
								if(array_key_exists("manufacturername", $fieldname)) {
									$manufacturerID = addManufacturer(@$data[$row][$fieldname["manufacturername"]],0, $row_rsLoggedIn['ID']);
								} 
								if(array_key_exists("manufacturername2", $fieldname)) {
									$manufacturerrangeID = addManufacturer(@$data[$row][$fieldname["manufacturername2"]],$manufacturerID, $row_rsLoggedIn['ID']);
								}
								if($manufacturerID+$manufacturerrangeID>0) {
									$manufacturerID = $manufacturerrangeID>0 ? $manufacturerrangeID : $manufacturerID;
									setProductToManufacturer($productID, $manufacturerID, $row_rsLoggedIn['ID']);
								}				
								foreach($fieldname as $key=>$value) { // get all columns that are not standard product cols							
									if(substr($key,0,14)=="multi|category") {
										$secondcatID = addCategory(@$data[$row][@$fieldname[$key]], 0 ,$row_rsLoggedIn['ID']);
										addProductToCategory($productID, $secondcatID, 0, $row_rsLoggedIn['ID']);
									}											
									if(substr($key,0,13)=="multi|version") {
										addVersionToProduct($productID, @$data[$row][@$fieldname[$key]], $row_rsLoggedIn['ID']);
									}
									if(substr($key,0,12)=="multi|finish") {
										addFinishToProduct($productID, @$data[$row][@$fieldname[$key]], $row_rsLoggedIn['ID']);
									}
									if(substr($key,0,9)=="multi|tag") {
										$taggroupname = isset($headers[@$fieldname[$key]]) ? $headers[@$fieldname[$key]] : "";
										$tagID = addTag(@$data[$row][@$fieldname[$key]],0,$row_rsLoggedIn['ID'], $taggroupname);
										tagProduct($productID , $tagID, $row_rsLoggedIn['ID']);
									}
									if(substr($key,0,14)=="multi|imageURL") {						
										addProductGalleryPhoto($productID, @$data[$row][@$fieldname[$key]], $row_rsLoggedIn['ID']);
									}							
								} // end for each
								if(!empty($options)) {
									foreach($options as $optionnumber => $option) {								
										if(trim(@$option['description'].@$option['sku'].@$option['price'].@$option['weight'].@$option['size'])!="") {
											$description = isset($option['description']) ? @$option['description'] : @$option['sku']." ".@$option['weight']." ".@$option['size']." ".@$option['quantity'];					
											addProductOption($productID, $description, @$option['sku'], @$option['price'], @$option['weight'], @$option['size'],@$option['quantity'],0);	
										}
									} // end for each
								} // end is options
							} // end actual product unserted or updated (not option)
						}// not test mode product added or updated
						else {// no product added so add result to msg
							$msg .= isset($returnvalue['result']) ? $returnvalue['result']."\n" : "";
						}
					}							
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
			$msg .= $count." products successfully inserted/updated. ";
	}
}// end post

function selectMenu($n) {
	global $setting;
	$values = array("--§0",
	 "Omit Column§-1",
	 "Product Name§title",
	 "Description§description1",
	 "Price§price",
	 "ID§ID",
	 "Stock code§sku",
	 "GTIN§upc",
	 "MPN§mpn",
	 "ISBN§isbn",
	 "URL Name§longID",
	 "In Stock§instock",
	 "Available Date§availabledate",
	 "Main Image§imageURL",
	 "Extra Image*§multi|imageURL",
	 "Tag (Grouped by column name)*§multi|tag",
	 "Area (m2)§area",
	 "Dimensions (LxHxW)§dimensions",
	 "Box length§box_length",
	 "Box width§box_width",
	 "Box height§box_height",	 
	 "Length§int_length",
	 "Height§int_height",
	 "Width§int_width",
	 "Main Category§category",
	 "Sub-category§subcat",
	 "Additional category*§multi|category",
	 "Manufacturer§manufacturername",
	 "Manufacturer Range§manufacturername2",	 
	 "RRP§listprice",
	 "Weight§weight",
	 "Capacity§volume",
	 "Version/Size*§multi|version",
	 "Colour/Finish*§multi|finish",
	 "Description Tab 2§description2",
	 "Description Tab 3§description3",
	 "Append description (Column heading: cell)*§multi|description4",
	 "Append to title*§multi|title2",
	 "Option 1 Name§productoption|option1description",
	 "Option 2 Name§productoption|option2description",
	 "Option 3 Name§productoption|option3description",
	 "Option 4 Name§productoption|option4description",
	 "Option 5 Name§productoption|option5description",
	 "Option 1 Price§productoption|option1price",
	 "Option 2 Price§productoption|option2price",
	 "Option 3 Price§productoption|option3price",
	 "Option 4 Price§productoption|option4price",
	 "Option 5 Price§productoption|option5price",
	 "Option 1 SKU§productoption|option1sku",
	 "Option 2 SKU§productoption|option2sku",
	 "Option 3 SKU§productoption|option3sku",
	 "Option 4 SKU§productoption|option4sku",
	 "Option 5 SKU§productoption|option5sku",
	 "Option 1 Size§productoption|option1size",
	 "Option 2 Size§productoption|option2size",
	 "Option 3 Size§productoption|option3size",
	 "Option 4 Size§productoption|option4size",
	 "Option 5 Size§productoption|option5size",
	 "Option 1 In stock§productoption|option1quantity",
	 "Option 2 In stock§productoption|option2quantity",
	 "Option 3 In stock§productoption|option3quantity",
	 "Option 4 In stock§productoption|option4quantity",
	 "Option 5 In stock§productoption|option5quantity");
	
	$html = "<select name=\"column[".$n."]\" class=\"form-control\" >\n";
	foreach($values as $key=>$value) {
		$option = explode("§",$value);
		$html .= "<option value=\"".$option[1]."\"";
		if (isset($_POST['column'][$n]) &&  $_POST['column'][$n] == $option[1]) { 
			$html .= " selected=\"selected\" ";
		} else if ($setting[$n-1] == $option[1]) { 
			$html .= " selected=\"selected\" ";
		}
		$html .= ">".$option[0]."</option>\n";
	}
	$html .="</select>\n";
	return $html;
}


      
            
       

?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Import Products"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script>

 var fb_keepAlive = true;
 </script>
<script src="/core/scripts/formUpload.js"></script>
<style >
<!--
<?php if($totalRows_rsRegions==0) {
?> #regionID {
 display: none;
}
<?php
}
if($totalRows_rsGroups==0) {
?> #groupID {
 display: none;
}
<?php
}
?>
-->
</style>
<link href="../../css/defaultProducts.css" rel="stylesheet"  />
<?php if(isset($body_class)) $body_class .= " products ";  ?>
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
 
        <?php require_once('../../../core/region/includes/chooseregion.inc.php'); ?>
  <h1><i class="glyphicon glyphicon-shopping-cart"></i> Import: Add or Update Products</h1>
      <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
        <li><a href="index.php" class="link_undo"><i class="glyphicon glyphicon-arrow-left"></i> Products</a></li>
      </ul></div></nav>
     <?php  require_once('../../../core/includes/alert.inc.php'); ?>
      <form action="import_products.php" method="post" enctype="multipart/form-data" name="form1" id="form1">
      <p><label data-toggle="tooltip" title="Import new products. If product already exists, update existing product"><input type="radio" name="import_mode" value="0" <?php if(!isset($_POST['import_mode']) || $_POST['import_mode']==0) echo " checked "; ?>> 
      Add or Update</label>&nbsp;&nbsp;&nbsp;
      <label data-toggle="tooltip" title="Import new products. If product already exists, ignore."><input type="radio" name="import_mode" value="1" <?php if(isset($_POST['import_mode']) && $_POST['import_mode']==1) echo " checked "; ?>>
         Add only</label>&nbsp;&nbsp;&nbsp;<label data-toggle="tooltip" title="Update existing products. Ignore unknown products."><input type="radio" name="import_mode" value="2" <?php if(isset($_POST['import_mode']) && $_POST['import_mode']==2) echo " checked "; ?>> Update only</label>&nbsp;&nbsp;&nbsp;</p>
<p>
        <label>1. Get your data ready in the form of a CSV file and select:
          <input type="file" name="filename" id="filename" value="<?php echo isset($_POST['filename']) ? htmlentities($_POST['filename'], ENT_COMPAT, "UTF-8") : ""; ?>" />
        </label>
      </p>
      <p>2. Choose the columns to import below:</p>
      <table class="form-table">
        <tr>
          <td>Column 1 (A):</td>
          <td>Column 2 (B):</td>
          <td>Column 3 (C):</td>
          <td>Column 4 (D):</td>
        </tr>
        <tr>
          <td><?php echo selectMenu(1); ?></td>
          <td><?php echo selectMenu(2); ?></td>
          <td><?php echo selectMenu(3); ?></td>
          <td><?php echo selectMenu(4); ?></td>
        </tr>
        <tr>
          <td>Column 5 (E):</td>
          <td>Column 6 (F):</td>
          <td>Column 7 (G):</td>
          <td>Column 8 (H):</td>
        </tr>
        <tr>
          <td><?php echo selectMenu(5); ?></td>
          <td><?php echo selectMenu(6); ?></td>
          <td><?php echo selectMenu(7); ?></td>
          <td><?php echo selectMenu(8); ?></td>
        </tr>
        <tr>
          <td>Column 9 (I):</td>
          <td>Column 10 (J):</td>
          <td>Column 11 (K):</td>
          <td>Column 12 (L):</td>
        </tr>
        <tr>
          <td><?php echo selectMenu(9); ?></td>
          <td><?php echo selectMenu(10); ?></td>
          <td><?php echo selectMenu(11); ?></td>
          <td><?php echo selectMenu(12); ?></td>
        </tr>
        <tr>
          <td>Column 13 (M):</td>
          <td>Column 14 (N):</td>
          <td>Column 15 (O):</td>
          <td>Column 16 (P):</td>
        </tr>
        <tr>
          <td><?php echo selectMenu(13); ?></td>
          <td><?php echo selectMenu(14); ?></td>
          <td><?php echo selectMenu(15); ?></td>
          <td><?php echo selectMenu(16); ?></td>
        </tr>
        <tr>
          <td>Column 17 (Q):</td>
          <td>Column 18 (R):</td>
          <td>Column 19 (S):</td>
          <td>Column 20 (T):</td>
        </tr>
        <tr>
          <td><?php echo selectMenu(17); ?></td>
          <td><?php echo selectMenu(18); ?></td>
          <td><?php echo selectMenu(19); ?></td>
          <td><?php echo selectMenu(20); ?></td>
        </tr>
        <tr>
          <td>Column 21 (U):</td>
          <td>Column 22 (V):</td>
          <td>Column 23 (W):</td>
          <td>Column 24 (X):</td>
        </tr>
         <tr>
          <td><?php echo selectMenu(21); ?></td>
          <td><?php echo selectMenu(22); ?></td>
          <td><?php echo selectMenu(23); ?></td>
          <td><?php echo selectMenu(24); ?></td>
        </tr>
        <tr>
          <td>Column 25 (Y):</td>
          <td>Column 26 (Z):</td>
          <td>Column 27 (AA):</td>
          <td>Column 28 (AB):</td>
        </tr>
         <tr>
          <td><?php echo selectMenu(25); ?></td>
          <td><?php echo selectMenu(26); ?></td>
          <td><?php echo selectMenu(27); ?></td>
          <td><?php echo selectMenu(28); ?></td>
        </tr>
        <tr>
          <td>Column 29 (AC):</td>
          <td>Column 30 (AD):</td>
          <td>Column 31 (AE):</td>
          <td>Column 32 (AF):</td>
        </tr>
         <tr>
          <td><?php echo selectMenu(29); ?></td>
          <td><?php echo selectMenu(30); ?></td>
          <td><?php echo selectMenu(31); ?></td>
          <td><?php echo selectMenu(32); ?></td>
        </tr>
      </table>
     
      <p>3. Choose your options and Submit:</p>
      <fieldset>
        <p>
          <label>
            <input type="checkbox" name="headings" id="headings" <?php if(isset($_POST['headings'])) { echo "checked = \"checked\""; } ?> />
            First row is headings (do not import)</label> &nbsp;&nbsp;
          <label>
            <input <?php if (!(strcmp($_REQUEST['updatematches'],1))) {echo "checked=\"checked\"";} ?> name="updatematches" type="checkbox" id="updatematches" value="1">
            Match products by title (all products must have unique title)</label>
          &nbsp;&nbsp;
          <label>
            <input type="checkbox" name="savesettings" id="savesettings" <?php if (isset($_REQUEST['savesettings'])) {echo "checked=\"checked\"";} ?>>
            Save import settings</label>&nbsp;&nbsp;
            
             <label data-toggle="tooltip" title="Do not make any actual changes, but return a report of changes that would have been made.">
            <input type="checkbox" name="testmode" <?php if (isset($_REQUEST['testmode'])) {echo "checked=\"checked\"";} ?>>
            Test mode</label>
          <br><button type="submit"  class="btn btn-primary">Submit</button>
          <input name="createdbyID" type="hidden" id="createdbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
        </p>
        <p>Note: Tags will be grouped into the column name they're in (if first row is 'headings').</p>
       
      </fieldset><fieldset><legend>Web Admin</legend><p><a href="import_products.php?deleteall=true" onClick="return confirm('Are you sure you want to delete all products and photos.\n\nWARNING: This affects ALL sites using this CMS and cannot be undone.');">Delete all products</a></p></fieldset>
    </form>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsGroups);

mysql_free_result($rsRegions);

mysql_free_result($rsUserTypes);

mysql_free_result($rsProductPrefs);
?>
