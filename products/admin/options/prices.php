<?php require_once('../../../Connections/aquiescedb.php'); ?><?php require_once('../../../core/includes/adminAccess.inc.php'); ?>
<?php require_once('../../../core/includes/upload.inc.php'); ?>
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

$MM_restrictGoTo = "../../../login/index.php?notloggedin=true";
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

$uploaded = getUploads();
if (isset($uploaded) && is_array($uploaded)  
	&& isset($uploaded["filename"][0]["newname"]) 
	&& $uploaded["filename"][0]["newname"]!="") { 
	
	$filename = UPLOAD_ROOT.$uploaded["filename"][0]["newname"];
	if(is_readable($filename) && !isset($submit_error)) { // file OK
		ini_set('auto_detect_line_endings', true);
		$handle = fopen($filename,"r");
		if($handle) { // handle
			$row = 0; $log = ""; $numcolumns=0;
			$submit_error = "";
			// data in array as follows:
			// $data[row][column]
			$data = array();
			$fieldname = array();
			
			
			// get post data structure
			foreach($_POST['column'] as $column => $columnname) {
				if($columnname != "") {	
					$numcolumns++;
					if($columnname != -1) {
						if(!isset($fieldname[$columnname])) {	
						
							$fieldname[$columnname] = $column-1;
						} else {
							$submit_error .= "You can only import each column once."; break;
						}
						
					}
				}
			}
			//print_r($fieldname); die();
			while($fields = fgetcsv($handle,65535)) { // get line
				$row++; 
				if(count($fields) != $numcolumns) {	
					$submit_error .= "<strong>Column count mismatch on line ".$row.".</strong><br /><br />Please check the integrity of your CSV file: each row must have the same number  of items. For example, commas within column items will cause problems."; break; 
				}
				if($submit_error=="" && (!isset($_POST['omitheadings']) || $row !=1)) {
					foreach($fields as $column=> $value) {
						$data[$row][$column] = $value;
						
					}
					
				}
				
			} // end get line
			//print_r($data); die();
			
			if($submit_error=="") { // no errors
				foreach($data as $row => $columns) {
					$update = "UPDATE product SET price = ".GetSQLValueString($data[$row][1],"double")." WHERE sku = ".GetSQLValueString($data[$row][0],"text");
					mysql_query($update, $aquiescedb) or die(mysql_error());

					//echo $update."<br><br>";
					$update = "UPDATE productoptions SET price = ".GetSQLValueString($data[$row][1],"double")." WHERE stockcode = ".GetSQLValueString($data[$row][0],"text");
					mysql_query($update, $aquiescedb) or die(mysql_error());
					//echo $update."<br><br>";
					
				}
				unlink($filename);
				$msg = "All prices updated.";
			} // no errors
			
		} else { // file not OK
			$submit_error = "Could not find the uploaded file: ".$filename;
		}
	} else { // read not OK
		$submit_error = "Could not read the uploaded file: ".$filename;
	}
	


} // end upload



if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form2")) {
  $updateSQL = sprintf("UPDATE productprefs SET vatincluded=%s, vatprice=%s, vattext=%s, askvatnumber=%s, showareaprice=%s, useareaquantity=%s, nopricebuy=%s, nopricetext=%s, text_vatnumber=%s WHERE ID=%s",
                       GetSQLValueString(isset($_POST['vatincluded']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['vatprice']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['vattext']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['askvatnumber']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['showareaprice']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['useareaquantity']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['nopricebuy']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['nopricetext'], "text"),
                       GetSQLValueString($_POST['text_vatnumber'], "text"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form2")) {
	$update = "UPDATE region SET vatrate = ".GetSQLValueString($_POST['vatrate'], "int")." WHERE ID = ".$regionID;
	mysql_query($update, $aquiescedb) or die(mysql_error());
	foreach($_POST['ratepercent'] as $key => $value) {
		$update = "UPDATE productvatrate SET  ratepercent = ".GetSQLValueString($value, "float").", modifiedbyID = ".GetSQLValueString($_POST['modifiedbyID'], "int").", modifieddatetime = '".date('Y-m-d H:i:s')."' WHERE ID = ".$key;
		mysql_query($update, $aquiescedb) or die(mysql_error().": ".$update);
	}
	if(isset($_POST['ratepc']) && floatval($_POST['ratepc'])>0) { // add vat rate
		$insert = "INSERT INTO productvatrate (ratename, ratepercent, regionID, createdbyID, createddatetime) VALUES (".GetSQLValueString($_POST['ratename'], "text").",".GetSQLValueString($_POST['ratepc'], "float").",".$regionID.",".GetSQLValueString($_POST['modifiedbyID'], "int").",'".date('Y-m-d H:i:s')."')";
		mysql_query($insert, $aquiescedb) or die(mysql_error().": ".$update);
	} else {
  $updateGoTo = "index.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo)); exit;
	}
}


$select = "SELECT ID FROM productvatrate WHERE ID = 1";// 1 is reserverd so we need to add this if not used
$result = mysql_query($select, $aquiescedb) or die(mysql_error());
if(mysql_num_rows($result)==0) {
}



$varRegionID_rsProductPrefs = "1";
if (isset($regionID)) {
  $varRegionID_rsProductPrefs = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsProductPrefs = sprintf("SELECT * FROM productprefs WHERE ID = %s", GetSQLValueString($varRegionID_rsProductPrefs, "int"));
$rsProductPrefs = mysql_query($query_rsProductPrefs, $aquiescedb) or die(mysql_error());
$row_rsProductPrefs = mysql_fetch_assoc($rsProductPrefs);
$totalRows_rsProductPrefs = mysql_num_rows($rsProductPrefs);

$varRegionID_rsCategories = "1";
if (isset($regionID)) {
  $varRegionID_rsCategories = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsCategories = sprintf("SELECT productcategory.ID, productcategory.title, parent.title AS parent FROM productcategory LEFT JOIN productcategory AS parent ON (productcategory.subcatofID  = parent.ID) WHERE productcategory.statusID = 1 AND productcategory.regionID = %s ORDER BY parent.title, productcategory.title ASC", GetSQLValueString($varRegionID_rsCategories, "int"));
$rsCategories = mysql_query($query_rsCategories, $aquiescedb) or die(mysql_error());
$row_rsCategories = mysql_fetch_assoc($rsCategories);
$totalRows_rsCategories = mysql_num_rows($rsCategories);

$varRegionID_rsVatRates = "1";
if (isset($regionID)) {
  $varRegionID_rsVatRates = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsVatRates = sprintf("SELECT productvatrate.ID, productvatrate.ratename, productvatrate.ratepercent FROM productvatrate WHERE ID >2 AND  productvatrate.regionID = %s ORDER BY productvatrate.ratepercent", GetSQLValueString($varRegionID_rsVatRates, "int"));
$rsVatRates = mysql_query($query_rsVatRates, $aquiescedb) or die(mysql_error());
$row_rsVatRates = mysql_fetch_assoc($rsVatRates);
$totalRows_rsVatRates = mysql_num_rows($rsVatRates);

$varUsername_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $varUsername_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT users.ID, users.usertypeID, users.regionID FROM users WHERE users.username = %s", GetSQLValueString($varUsername_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);
 
if(isset($_POST['increase']) && floatval($_POST['increase'])>0 && floatval($_POST['increase'])>0) {
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$amount = abs($_POST['increase']) * floatval($_POST['sign']);
	$categoryID = isset($_POST['categoryID']) ? intval($_POST['categoryID']) : 0;
	$saleSQL = isset($_POST['sale']) ? "saleitem = 1, " : "";
	$price = "price";
	$updated = 0;
	switch ($_POST['rrp'])	{
		case 1 : $saleSQL .= "listprice = price, price = "; break;
		case 2 : $saleSQL .= "listprice = "; break;
		case 3 : $saleSQL .= "listprice = "; $price = "listprice"; break;
		default : $saleSQL .= "price = "; break;
	}

	$update = "UPDATE product LEFT JOIN productcategory ON (product.productcategoryID = productcategory.ID) LEFT JOIN productinregion ON (productinregion.productID = product.ID) SET ".$saleSQL." ROUND((".$price."+(".$price."*".$amount."/100))/".floatval($_POST['roundto']).") * ".floatval($_POST['roundto'])." WHERE ((productinregion.regionID IS NULL) OR (productinregion.regionID = ".intval($regionID).")) AND (".$categoryID." = 0 OR  product.productcategoryID = ".$categoryID." OR productcategory.subcatofID = ".$categoryID.")"; 
	$result = mysql_query($update, $aquiescedb) or die(mysql_error().": ".$update);
	$updated += mysql_affected_rows();
	
	if($_POST['rrp']!=2) {
		$update = "UPDATE productoptions LEFT JOIN product ON (productoptions.productID = product.ID) LEFT JOIN productcategory ON (product.productcategoryID = productcategory.ID) LEFT JOIN productinregion ON (productinregion.productID = product.ID)  SET productoptions.price = ROUND((productoptions.price+(productoptions.price*".$amount."/100))/".floatval($_POST['roundto']).") * ".floatval($_POST['roundto'])." WHERE ((productinregion.regionID IS NULL) OR (productinregion.regionID = ".intval($regionID).")) AND  (".$categoryID." = 0 OR  product.productcategoryID = ".$categoryID." OR productcategory.subcatofID = ".$categoryID.")";  
	$result = mysql_query($update, $aquiescedb) or die(mysql_error().": ".$update);
	$updated += mysql_affected_rows();
	}
	

	

}





?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Manage Prices"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script src="../../../SpryAssets/SpryValidationTextField.js"></script>
<link href="../../../SpryAssets/SpryValidationTextField.css" rel="stylesheet"  />
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
<h1><i class="glyphicon glyphicon-shopping-cart"></i> Manage Prices</h1>
    <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
      <li><a href="index.php" class="link_undo"><i class="glyphicon glyphicon-arrow-left"></i> Shop options</a></li>
    </ul></div></nav>
    <?php if(isset($updated)) { ?><p class="alert warning alert-warning" role="alert"><?php echo $updated; ?> product prices (including options) have been changed by the specfied amount. <a href="../products/index.php?categoryID=<?php echo isset($categoryID) ? $categoryID : 0; ?>&showsub=1">View products</a>.</p><?php } ?>
     <?php if(isset($submit_error) && $submit_error!="") { ?><p class="alert alert-danger" role="alert"><?php echo $submit_error; ?></p><?php } ?>
     <?php if(isset($msg) && $msg!="") { ?><p class="message alert alert-info" role="alert"><?php echo $msg; ?></p><?php } ?>
    <form action="" method="post" name="form1" id="form1" class="form-inline">
      <h2>Bulk Category Price Update</h2>
      
      <p>
        <select name="sign" id="sign" class="form-control">
          <option value="1" >Increase</option>
          <option value="-1" <?php if (@$_POST['sign']=="-1") {echo "selected=\"selected\"";} ?>>Decrease</option>
        </select>
<select name="categoryID" id="categoryID" class="form-control">
          <option value="0" <?php if (!(strcmp(0, @$_POST['categoryID']))) {echo "selected=\"selected\"";} ?>>all</option>
          <?php
do {  
?>
          <option value="<?php echo $row_rsCategories['ID']?>"<?php if (!(strcmp($row_rsCategories['ID'], @$_POST['categoryID']))) {echo "selected=\"selected\"";} ?>><?php echo isset($row_rsCategories['parent']) ? $row_rsCategories['parent']." > " : ""; echo $row_rsCategories['title']?></option>
          <?php
} while ($row_rsCategories = mysql_fetch_assoc($rsCategories));
  $rows = mysql_num_rows($rsCategories);
  if($rows > 0) {
      mysql_data_seek($rsCategories, 0);
	  $row_rsCategories = mysql_fetch_assoc($rsCategories);
  }
?>
        </select>
        prices by <span id="sprytextfield2">
        <label>
          <input name="increase" type="text" id="increase" value="<?php echo isset($_REQUEST['increase']) ? $_REQUEST['increase'] : "0" ; ?>" size="5" maxlength="5"class="form-control" />
        </label>
        <span class="textfieldRequiredMsg">A value is required.</span><span class="textfieldInvalidFormatMsg">Invalid format.</span></span> % to the nearest
        <span id="sprytextfield3">
        <label>
          <input name="roundto" type="text" id="roundto" value="<?php echo isset($_REQUEST['roundto']) ? $_REQUEST['roundto'] : "0.01"; ?>" size="5" maxlength="5" class="form-control"/>
        </label>
        <span class="textfieldRequiredMsg">A value is required.</span><span class="textfieldInvalidFormatMsg">Invalid format.</span></span>
<label>
  <button type="button" name="Button" class="btn btn-default btn-secondary"  onclick="if(confirm('Are you sure you want to change all the selected product prices on your site by '+document.getElementById('increase').value+'%?\n\nWARNING: These changes will be implemented immediately and cannot be undone.')) { this.form.submit() }">Update</button>
</label>

  
  

<br>Update: 
      
     
         <label>
        <input type="radio" name="rrp" value="0" <?php if(!isset($_POST['rrp']) || $_POST['rrp']==0) echo "checked"; ?>  >
        Main price only</label>&nbsp;&nbsp;&nbsp;
        
        <label>
        
        <label>
        <input type="radio" name="rrp" value="3" <?php if(isset($_POST['rrp']) && $_POST['rrp']==3) echo "checked"; ?>  >
        RRP only</label>
        &nbsp;&nbsp;&nbsp;
        
        <label>
        
        
        <input type="radio" name="rrp" value="1" <?php if(isset($_POST['rrp']) && $_POST['rrp']==1) echo "checked"; ?> >
        Main price,  making RRP = old price</label>
        &nbsp;&nbsp;&nbsp;
        
        <label>
        <input type="radio" name="rrp" value="2" <?php if(isset($_POST['rrp']) && $_POST['rrp']==2) echo "checked"; ?> >
        RRP only, based on % of main price</label> &nbsp;&nbsp;|&nbsp;&nbsp; <label>
  <input type="checkbox" name="sale" id="sale">
  Set &quot;on sale&quot;</label> 
        
      </p>
      <p><em>Note: If choosing a category all subcategories will also be updated. Only the main category for each produts will be updated to avoid duplicate price increases</em></p>
    </form>
    <form action="prices.php" method="post" enctype="multipart/form-data" name="form3" id="form3">
      <h2>Specific Price Changes</h2>
      <p>Upload a 2-column CSV file (<strong>IMPORTANT</strong>: column 1 must denote stock code [SKU] and column 2 the new price):</p>
      <p>
        <label>
          <input type="file" name="filename" id="filename" /><input name="column[1]" type="hidden" value="sku" />
          <input name="column[2]" type="hidden" value="price" />
        </label>
        <button type="submit" name="upload" id="upload" class="btn btn-default btn-secondary"  onclick="return confirm('NOTE: These prices will be implemented immediately and can not be undone. Proceed?');">Upload</button>
      </p>
     </form><form action="<?php echo $editFormAction; ?>" method="post" enctype="multipart/form-data" name="form2" id="form2">
      <h2>VAT</h2>
      <p>The options below can be overridden on a per-category basis if required:</p>
      <table border="0" cellpadding="2" cellspacing="2" class="form-table">
        <tr>
          <td align="right">VAT included in price:</td>
          <td><input <?php if (!(strcmp($row_rsProductPrefs['vatincluded'],1))) {echo "checked=\"checked\"";} ?> name="vatincluded" type="checkbox" id="vatincluded" value="1" />
            (the price shown includes VAT.)</td>
        </tr>
        <tr>
          <td align="right">Show VAT text</td>
          <td><label>
            <input <?php if (!(strcmp($row_rsProductPrefs['vattext'],1))) {echo "checked=\"checked\"";} ?> name="vattext" type="checkbox" id="vattext" value="1">
            (will show "inc or ex-VAT". Does not apply with option below.)</label></td>
        </tr>
        <tr>
          <td align="right">Show other price:</td>
          <td><input <?php if (!(strcmp($row_rsProductPrefs['vatprice'],1))) {echo "checked=\"checked\"";} ?> name="vatprice" type="checkbox" id="vatprice" value="1" /> 
            (Also show re-calculated price - with or without VAT depending on main price)</td>
        </tr>
        <tr>
          <td align="right"><label for="askvatnumber">Ask for VAT number at checkout:</label></td>
          <td><input <?php if (!(strcmp($row_rsProductPrefs['askvatnumber'],1))) {echo "checked=\"checked\"";} ?> name="askvatnumber" type="checkbox" id="askvatnumber" value="1">
            <input name="text_vatnumber" type="text" id="text_vatnumber" value="<?php echo $row_rsProductPrefs['text_vatnumber']; ?>" size="50" maxlength="50">
            </td>
        </tr>
        <tr>
          <td align="right">Zero rates VAT:</td>
          <td>0%</td>
        </tr>
        <tr>
          <td align="right"><label for="vatrate">Standard VAT:</label></td>
          <td class="form-inline">
            <input name="vatrate" type="text" id="vatrate" size="5" maxlength="5" value="<?php echo $thisRegion['vatrate']; ?>" class="form-control">
            %</td>
        </tr>
        
         <?php if($totalRows_rsVatRates>1) { 
		 
		 	$row_rsVatRates=mysql_fetch_assoc($rsVatRates);  // 1 is reserved, so skip
		  do { ?>
           <tr>
            
             <td class="text-right"><?php echo $row_rsVatRates['ratename']; ?>:</td>
             <td class="form-inline"><input name="ratepercent[<?php echo $row_rsVatRates['ID']; ?>]" type="text" size="5" maxlength="5" value="<?php echo $row_rsVatRates['ratepercent']; ?>" class="form-control"> %</td>
           </tr>
           <?php } while ($row_rsVatRates = mysql_fetch_assoc($rsVatRates)); } ?>
           
           
        <tr>
          <td align="right"><label for="ratename">Add VAT rate:</label></td>
          <td class="form-inline">
            <input type="text" name="ratename" id="ratename" placeholder="Name" size="50" maxlength="100" class="form-control">
            <span id="sprytextfield1">
            <input name="ratepc" type="text" id="ratepc" size="5" maxlength="5" placeholder="Rate" class="form-control">
<span class="textfieldInvalidFormatMsg">Invalid format.</span></span>%
            <button type="submit" class="btn btn-default btn-secondary" >Add</button></td>
        </tr>
      </table>
  
<h2>Quantities and Prices</h2>

 <p>   <label>
      <input <?php if (!(strcmp($row_rsProductPrefs['nopricebuy'],1))) {echo "checked=\"checked\"";} ?> name="nopricebuy" type="checkbox" id="nopricebuy" value="1" />
      Allow purchase if no price set (can be overridden at category level)</label></p>
       <p>
         <label>
           <input <?php if (!(strcmp($row_rsProductPrefs['showareaprice'],1))) {echo "checked=\"checked\"";} ?> name="showareaprice" type="checkbox" id="showareaprice" value="1">
           Show price based on area (where applicable)</label>
       </p>
       <p>
         <label>
           <input <?php if (!(strcmp($row_rsProductPrefs['useareaquantity'],1))) {echo "checked=\"checked\"";} ?> name="useareaquantity" type="checkbox" id="useareaquantity" value="1">
           Buy quantity by area (shown by item/pack in basket)</label>
       </p>
      
       <p class="form-inline"><label>No price text: 
         
           <input name="nopricetext" type="text" id="nopricetext" value="<?php echo $row_rsProductPrefs['nopricetext']; ?>" size="100" maxlength="100" class="form-control"></label>
(can be overridden at category level)<br>
       </p>
       
      <p>
        <button type="submit" name="save" id="save" class="btn btn-primary">Save changes...</button>
        <input name="ID" type="hidden" id="ID" value="<?php echo $row_rsProductPrefs['ID']; ?>" />      <input name="modifiedbyID" type="hidden" id="modifiedbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
      </p>
      
      <input type="hidden" name="MM_update" value="form2" />
    </form>

    <script>
<!--
var sprytextfield2 = new Spry.Widget.ValidationTextField("sprytextfield2", "real");
var sprytextfield3 = new Spry.Widget.ValidationTextField("sprytextfield3", "real");
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1", "real", {isRequired:false});
//-->
    </script>
  <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsProductPrefs);

mysql_free_result($rsCategories);

mysql_free_result($rsVatRates);

mysql_free_result($rsLoggedIn);
?>
