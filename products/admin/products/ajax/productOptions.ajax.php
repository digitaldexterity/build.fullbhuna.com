<?php require_once('../../../../Connections/aquiescedb.php'); ?>
<?php require_once('../../../../core/includes/framework.inc.php'); ?>
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


if(isset($_GET['deleteoptionID']) && intval($_GET['deleteoptionID'])>0) {
	 $delete = "DELETE FROM productoptions WHERE ID = ".GetSQLValueString($_GET['deleteoptionID'], "int");
	$result = mysql_query($delete, $aquiescedb) or die(mysql_error()); 
 }  // end remove


if(isset($_GET['optionname']) && $_GET['optionname']!="") { // option aadded
$optionquantity = strlen($_GET['optionquantity'])>0 ? intval($_GET['optionquantity']) : 1;

$insert = "INSERT INTO productoptions (optionname, stockcode, upc, price, weight, size, instock, productID,finishID, versionID,photoID, createdbyID, createddatetime) VALUES (".GetSQLValueString($_GET['optionname'], "text").",".GetSQLValueString($_GET['stockcode'], "text").",".GetSQLValueString($_GET['optionupc'], "text").",".GetSQLValueString($_GET['optionprice'], "double").",".GetSQLValueString($_GET['optionweight'], "double").",".GetSQLValueString($_GET['optionsize'], "text").",".$optionquantity.",".GetSQLValueString($_GET['productID'], "int").",".GetSQLValueString($_GET['finish'], "int").",".GetSQLValueString($_GET['version'], "int").",".GetSQLValueString($_GET['photoID'], "int").",".GetSQLValueString($_GET['modifiedbyID'], "int").",'".date('Y-m-d H:i:s')."')"; 
 
  $result = mysql_query($insert, $aquiescedb) or die(mysql_error());
 } 

$colname_rsProductOptions = "-1";
if (isset($_GET['productID'])) {
  $colname_rsProductOptions = $_GET['productID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsProductOptions = sprintf("SELECT productoptions.*, productfinish.finishname, productversion.versionname, photos.imageURL FROM productoptions LEFT JOIN productfinish ON (productoptions.finishID = productfinish.ID) LEFT JOIN productversion ON (productoptions.versionID = productversion.ID) LEFT JOIN photos ON (productoptions.photoID = photos.ID)  WHERE productID = %s AND productoptions.statusID =1 GROUP BY productoptions.ID  ", GetSQLValueString($colname_rsProductOptions, "int"));
$rsProductOptions = mysql_query($query_rsProductOptions, $aquiescedb) or die(mysql_error());
$row_rsProductOptions = mysql_fetch_assoc($rsProductOptions);
$totalRows_rsProductOptions = mysql_num_rows($rsProductOptions);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsFinishes = "SELECT ID, finishname FROM productfinish WHERE statusID = 1 ORDER BY finishname ASC";
$rsFinishes = mysql_query($query_rsFinishes, $aquiescedb) or die(mysql_error());
$row_rsFinishes = mysql_fetch_assoc($rsFinishes);
$totalRows_rsFinishes = mysql_num_rows($rsFinishes);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsVersions = "SELECT ID, versionname FROM productversion WHERE statusID = 1 ORDER BY versionname ASC";
$rsVersions = mysql_query($query_rsVersions, $aquiescedb) or die(mysql_error());
$row_rsVersions = mysql_fetch_assoc($rsVersions);
$totalRows_rsVersions = mysql_num_rows($rsVersions);

$varProductID_rsPhotos = "-1";
if (isset($_GET['productID'])) {
  $varProductID_rsPhotos = $_GET['productID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPhotos = sprintf("SELECT photos.ID, photos.title, imageURL FROM photos LEFT JOIN productgallery ON (productgallery.galleryID = photos.categoryID) WHERE productgallery.productID = %s AND photos.active = 1 ORDER BY photos.ordernum", GetSQLValueString($varProductID_rsPhotos, "int"));
$rsPhotos = mysql_query($query_rsPhotos, $aquiescedb) or die(mysql_error());
$row_rsPhotos = mysql_fetch_assoc($rsPhotos);
$totalRows_rsPhotos = mysql_num_rows($rsPhotos);
?>
<?php if ($totalRows_rsProductOptions == 0) { // Show if recordset empty ?>
              <p>- There are currently no options for this product.</p>
              <?php } // Show if recordset empty ?>
<?php if ($totalRows_rsProductOptions > 0) { // Show if recordset not empty ?>
<style>.options-increase{ display:inline-block; } </style>
              <table  class="table table-hover">
              <tbody>
                <?php do { ?>
                  <tr>
                    <td><?php if(isset($row_rsProductOptions['imageURL'])) { ?><div class="fb_avatar" style="background-image:url(<?php echo getImageURL($row_rsProductOptions['imageURL'],"thumb"); ?>)" ></div><?php } echo $row_rsProductOptions['stockcode'];
					echo isset($row_rsProductOptions['upc']) ? " [GTIN ".$row_rsProductOptions['upc']."]" : "";
					echo " ".htmlentities($row_rsProductOptions['optionname'],ENT_IGNORE,"UTF-8")." ".$row_rsProductOptions['versionname']." ".$row_rsProductOptions['finishname'];
					echo " (";
					  if($row_rsProductOptions['instock'] == 0) { 
						echo "Out of stock";
	   } else { 
	  echo $row_rsProductOptions['instock'] >1 ? $row_rsProductOptions['instock']. " in stock" : "In stock";
	  }
	  
	  if(isset($row_rsProductOptions['availabledate']) && $row_rsProductOptions['availabledate'] > date('Y-m-d')) {
						echo " available ".date('d M Y', strtotime($row_rsProductOptions['availabledate']));
					}
					echo ")";
					
					
					 echo ($row_rsProductOptions['weight']>0) ? "&nbsp;(".$row_rsProductOptions['weight']."kgs)" : "";  echo isset($row_rsProductOptions['price']) ? "&nbsp;&pound;".number_format($row_rsProductOptions['price'],2,".",",") : "";  ?></td>
                    <td><div class="btn-group"><a href="/products/admin/products/editoption.php?productID=<?php echo $row_rsProductOptions['productID']; ?>&optionID=<?php echo $row_rsProductOptions['ID']; ?>" class="btn btn-sm btn-default btn-secondary"><i class="glyphicon glyphicon-pencil"></i> Edit</a><a href="javascript:void(0);" class="btn btn-sm btn-default btn-secondary" onclick="if(confirm('Are you sure you want to delete this option?')) { getData('/products/admin/products/ajax/productOptions.ajax.php?productID='+document.getElementById('ID').value+'&deleteoptionID=<?php echo $row_rsProductOptions['ID']; ?>','productOptionList');}"><i class="glyphicon glyphicon-trash"></i> Delete</a></div></td>
                  </tr>
                  <?php } while ($row_rsProductOptions = mysql_fetch_assoc($rsProductOptions)); ?></tbody>
  </table><p><a href="/products/admin/products/unmerge.php?productID=<?php echo intval($_GET['productID']); ?>" onclick="return confirm('Are you sure you want to separate these options into individual products?');">Separate into products</a></p>
              <?php } // Show if recordset not empty ?><fieldset class="form-inline"><legend>Add option</legend>
           <table class="form-table">
           <tbody>
  
  <tr>
    <td class="text-nowrap"><input name="optionname" type="text"  id="optionname" size="26" maxlength="30" placeholder="Option name (e.g. Black finish)" class="form-control" /></td>
    <td class="text-nowrap"><input name="stockcode" type="text"  id="stockcode" size="16" maxlength="50" placeholder="Stock code(optional)"  class="form-control"/></td>
    
  
    <td class="text-nowrap"><input name="optionupc" id="optionupc" type="text"   size="16" maxlength="50" placeholder="GTIN/UPC (optional)"  class="form-control"/></td>
    
    <td class="text-nowrap"><input name="optionsize" type="text"  id="optionsize" size="10" maxlength="50" placeholder="Size (optional)"  class="form-control"/></td>
    <td class="text-nowrap"><input name="optionquantity" type="text"  id="optionquantity" size="10" maxlength="10" placeholder="No. in stock"  class="form-control"/></td>
    <td class="text-nowrap">&pound;
    <input name="optionprice" type="text"  id="optionprice" size="10" maxlength="20" placeholder="Price (if diff'nt)"  class="form-control"/></td>
    <td class="text-nowrap"><a href="javascript:void(0);" onclick="getData('/products/admin/products/ajax/productOptions.ajax.php?productID='+document.getElementById('ID').value+'&optionname='+escape(document.getElementById('optionname').value)+'&stockcode='+escape(document.getElementById('stockcode').value)+'&upc='+escape(document.getElementById('optionupc').value)+'&version='+escape(document.getElementById('version').value)+'&finish='+escape(document.getElementById('finish').value)+'&optionweight='+escape(document.getElementById('optionweight').value)+'&optionprice='+escape(document.getElementById('optionprice').value)+'&optionsize='+escape(document.getElementById('optionsize').value)+'&optionquantity='+escape(document.getElementById('optionquantity').value)+'&photoID='+$('input[name=photoID]:checked').val()+'&modifiedbyID='+escape(document.getElementById('modifiedbyID').value)+'&defaultTab=2','productOptionList'); return false;" class="btn btn-default"><i class="glyphicon glyphicon-plus-sign"></i> Add</a></td>
  </tr>
  </tbody><tbody id="extra_product_options" >
  <tr>
    <td colspan="7" class="text-nowrap"><input name="optionweight" type="text"  id="optionweight" size="10" maxlength="20" placeholder="Weight (optl):"  class="form-control" />
kgs &nbsp; <select name="finish" id="finish" <?php if($totalRows_rsFinishes==0) echo "style=\"display: none;\""; ?>  class="form-control">
      <option value="">Choose colour (optional)...</option>
      <?php
do {  
?>
      <option value="<?php echo $row_rsFinishes['ID']?>"><?php echo $row_rsFinishes['finishname']?></option>
      <?php
} while ($row_rsFinishes = mysql_fetch_assoc($rsFinishes));
  $rows = mysql_num_rows($rsFinishes);
  if($rows > 0) {
      mysql_data_seek($rsFinishes, 0);
	  $row_rsFinishes = mysql_fetch_assoc($rsFinishes);
  }
?>
    </select> <select name="version" id="version" <?php if($totalRows_rsVersions==0) echo "style=\"display: none;\""; ?> class="form-control">
      <option value="">Choose version (optional)...</option>
      <?php
do {  
?>
      <option value="<?php echo $row_rsVersions['ID']?>"><?php echo $row_rsVersions['versionname']?></option>
      <?php
} while ($row_rsVersions = mysql_fetch_assoc($rsVersions));
  $rows = mysql_num_rows($rsVersions);
  if($rows > 0) {
      mysql_data_seek($rsVersions, 0);
	  $row_rsVersions = mysql_fetch_assoc($rsVersions);
  }
?>
    </select>
    </td>
  </tr>
  <?php if($totalRows_rsPhotos>0) { ?>
  <tr><td colspan="7">
  
  
  <label><input type="radio" value="" name="photoID" checked>
            None </label>
            &nbsp;&nbsp;&nbsp;
              <?php do { ?> 
                <label><input type="radio" value="<?php echo $row_rsPhotos['ID']; ?>"  name="photoID">&nbsp;<div class="fb_avatar" style="background-image:url(<?php echo getImageURL($row_rsPhotos['imageURL'],"thumb"); ?>)" ></div>
             </label> &nbsp;&nbsp;&nbsp;
                <?php } while ($row_rsPhotos = mysql_fetch_assoc($rsPhotos)); ?>
          
  </td></tr>
  <?php } ?>
  
  </tbody>
</table>
</fieldset>              
          
<?php
mysql_free_result($rsProductOptions);

mysql_free_result($rsFinishes);

mysql_free_result($rsVersions);

mysql_free_result($rsPhotos);
?>