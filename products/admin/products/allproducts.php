<?php require_once('../../../Connections/aquiescedb.php'); ?><?php require_once('../../includes/productFunctions.inc.php'); ?>
<?php require_once('../../../core/includes/framework.inc.php'); ?><?php require_once('../../../core/includes/adminAccess.inc.php'); ?><?php 
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

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsMaxOptions = "SELECT COUNT(productoptions.ID) AS maxoptions FROM productoptions GROUP BY productoptions.productID ORDER BY maxoptions DESC LIMIT 1";
$rsMaxOptions = mysql_query($query_rsMaxOptions, $aquiescedb) or die(mysql_error());
$row_rsMaxOptions = mysql_fetch_assoc($rsMaxOptions);
$totalRows_rsMaxOptions = mysql_num_rows($rsMaxOptions);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsAllProducts= "SELECT product.ID,product.price, product.imageURL,product.sku,product.title,productcategory.title AS categoryname, product.seotitle,product.metakeywords, product.metadescription, productmanufacturer.manufacturername, product.longID, productcategory.longID AS categorylongID, product.productcategoryID FROM product LEFT JOIN productcategory ON product.productcategoryID = productcategory.ID LEFT JOIN productmanufacturer ON product.manufacturerID = productmanufacturer.ID LEFT JOIN productinregion ON (productinregion.productID = product.ID) WHERE (productinregion.regionID = ".$regionID." OR productinregion.regionID IS NULL) AND product.statusID = 1 GROUP BY product.ID";
$rsAllProducts= mysql_query($query_rsAllProducts, $aquiescedb) or die(mysql_error());
$row_rsAllProducts= mysql_fetch_assoc($rsAllProducts);
$totalRows_rsAllProducts= mysql_num_rows($rsAllProducts);

$maxoptions = $row_rsMaxOptions['maxoptions'];

if(isset($_GET['csv']) && $_GET['csv']==1){
	$headers = array("ID", "Image","SKU", "H1", "H2", "Title Tag", "Meta Keywords", "Meta Description", "Manufacturer", "id|hide", "id|hide", "id|hide");
	csvHeaders("Export");
	print("ID (Edit),Image,Stock No,H1 Product Title,H2 Sub Head,Title tag,Meta Keywords,Meta Description,Manufacturer,Link (view)"); // headers
	if(isset($_GET['showoptions'])) { 
	print(",Price");
	$maxoptions = $row_rsMaxOptions['maxoptions'];
		if($maxoptions>0) {
			for($i=1; $i<=$maxoptions; $i++) {
				print ",Option ".$i.",SKU,Price";
			}
		} 
	}
	print "\n";
	do {
		$link  = getProtocol()."://". $_SERVER['HTTP_HOST'].productLink($row_rsAllProducts['ID'], $row_rsAllProducts['longID'], $row_rsAllProducts['productcategoryID'], $row_rsAllProducts['categorylongID'],  0,  "",0,"", 1);
		print (formatCSV($row_rsAllProducts['ID'],"number")).",";
		print (formatCSV($row_rsAllProducts['imageURL'],"text")).",";
		print (formatCSV($row_rsAllProducts['sku'],"text")).",";
		print (formatCSV($row_rsAllProducts['title'],"text")).",";
		print (formatCSV($row_rsAllProducts['categoryname'],"text")).",";
		print (formatCSV($row_rsAllProducts['seotitle'],"text")).",";
		print (formatCSV($row_rsAllProducts['metakeywords'],"text")).",";
		print (formatCSV($row_rsAllProducts['metadescription'],"text")).",";
		print (formatCSV($row_rsAllProducts['manufacturername'],"text")).",";
		print (formatCSV($link,"text"));
		if(isset($_GET['showoptions'])) { 
			print (",".formatCSV($row_rsAllProducts['price'],"currency"));
			$select = "SELECT optionname, stockcode, price FROM productoptions WHERE productID = ".$row_rsAllProducts['ID'];
	  		$result= mysql_query($select, $aquiescedb) or die(mysql_error());
	  		$numoptions = mysql_num_rows($result);
	  		if($numoptions>0) {
		  		while($option= mysql_fetch_assoc($result)) {
			   		print(",".formatCSV($option['optionname'],"text").",".formatCSV($option['stockcode'],"text").",".formatCSV($option['price'],"currency"));
		  		} // end while		  
	  		}// end results
			if(($maxoptions-$numoptions)>0) {
			  	for($i=$numoptions; $i<$maxoptions; $i++) {
				  	echo ",,,";
			  	}
		  	}
		}
		print "\n";
	} while ($row_rsAllProducts= mysql_fetch_assoc($rsAllProducts)); 
	
   
  die();
	
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="robots" content="noindex,nofollow" />
<title>
<?php $pageTitle = "All Products"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<style><!--
body {
	font-family:Arial, Helvetica, sans-serif;
	padding: 0 10px;
	background:rgb(255,255,255) !important;
}

table {
	border-collapse:collapse;
}

table td {
	border: 1px solid #999;
}
</style>
</head>
<body>

  
      
        <noscript>
        <p class="alert warning alert-warning" role="alert">The Control Panel requires your browser's JavaScript to be turned on. Some functions may not work correctly without it.</p>
        </noscript>
        <h1><i class="glyphicon glyphicon-shopping-cart"></i> All Products</h1> <form  method="get" id="form1" >
        <fieldset><a href="#" class="link_csv icon_with_text" onClick="document.getElementById('csv').value='1'; document.getElementById('form1').submit()">Download results as spreadsheet</a> 
       
          <label>
            <input type="checkbox" name="showoptions" id="showoptions" <?php if(isset($_GET['showoptions'])) echo "checked";  ?> onClick="document.getElementById('form1').submit()" >
            Show options</label><input name="csv" id="csv" type="hidden" value="0"></fieldset>
        </form>
<table class="table table-hover">
<thead>
  <tr>
    <th>ID (Edit)</th>
    <th>Image</th>
    
    <th>Stock No</th>
    <th width="132">H1 Product Title</th>  <th width="207">H2 Sub Head</th>
    
    
    
    
    <th>Title tag</th>
    <th>Meta Keywords</th>
    <th>Meta Description</th><th width="237">Manufacturer</th> <th width="37">Link (view)</th>
    <?php if(isset($_GET['showoptions'])) { ?><th width="237">Price</th><?php
	$maxoptions = $row_rsMaxOptions['maxoptions'];
	if($maxoptions>0) {
	for($i=1; $i<=$maxoptions; $i++) {
		echo "<th>Option ".$i."</th><th>SKU</th><th>Price</th>";
	}
	} 
	}?>
   
    </tr></thead><tbody>
  <?php do { ?>
    <tr> <td><a href="modify_product.php?productID=<?php echo $row_rsAllProducts['ID']; ?>" class="link_edit icon_with_text"><?php echo $row_rsAllProducts['ID']; ?></a></td>
      <td><div class="fb_avatar" style="background-image:url(<?php echo getImageURL($row_rsAllProducts['imageURL'], "thumb"); ?>)" ></div></td>
      
      <td class="text-nowrap"><?php echo htmlentities($row_rsAllProducts['sku'], ENT_COMPAT,"UTF-8"); ?></td>
      <td><?php echo htmlentities($row_rsAllProducts['title'], ENT_COMPAT,"UTF-8"); ?></td> 
      <td><?php echo htmlentities($row_rsAllProducts['categoryname'], ENT_COMPAT,"UTF-8"); ?></td>
     
      <td><?php echo htmlentities($row_rsAllProducts['seotitle'], ENT_COMPAT,"UTF-8"); ?></td>
      <td><?php echo htmlentities($row_rsAllProducts['metakeywords'], ENT_COMPAT,"UTF-8"); ?></td>
      <td><?php echo htmlentities($row_rsAllProducts['metadescription'], ENT_COMPAT,"UTF-8"); ?></td>
      <td><?php echo htmlentities($row_rsAllProducts['manufacturername'], ENT_COMPAT,"UTF-8"); ?></td>
    
      <td><?php $link = getProtocol()."://".$_SERVER['HTTP_HOST'].productLink($row_rsAllProducts['ID'], $row_rsAllProducts['longID'], $row_rsAllProducts['productcategoryID'], $row_rsAllProducts['categorylongID'],  0,  "",0,"", 1); ?><a href="<?php echo $link; ?>" target="_blank" class="link_view icon_with_text" rel="noopener"><?php echo $link; ?></a></td>
	  <?php if(isset($_GET['showoptions'])) { ?> <td class="text-right"><?php echo isset($row_rsAllProducts['price']) ? number_format($row_rsAllProducts['price'],2,".",",") : ""; ?></td>
	  <?php $select = "SELECT optionname, stockcode, price FROM productoptions WHERE productID = ".$row_rsAllProducts['ID'];
	  $result= mysql_query($select, $aquiescedb) or die(mysql_error());
	  $numoptions = mysql_num_rows($result);
	  if($numoptions>0) {
		  while($option= mysql_fetch_assoc($result)) {
			  echo "<td>".htmlentities($option['optionname'], ENT_COMPAT,"UTF-8")."</td>";
			   echo "<td>".htmlentities($option['stockcode'], ENT_COMPAT,"UTF-8")."</td>";
			    echo "<td class=\"right\">".number_format($option['price'],2,".",",")."</td>";
		  } // end while
		  
	  }// end results
if(($maxoptions-$numoptions)>0) {
			  for($i=$numoptions; $i<$maxoptions; $i++) {
				  echo "<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>";
			  }
		  }

  } // is options ?>
      </tr>
    <?php } while ($row_rsAllProducts= mysql_fetch_assoc($rsAllProducts)); ?></tbody>
</table>
   
  
 

<?php trackPage(@$pageTitle);  ?>

</body>
</html>
<?php
mysql_free_result($rsMaxOptions);

mysql_free_result($rsAllProducts);
?>
