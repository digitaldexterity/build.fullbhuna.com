<?php require_once('../../../../Connections/aquiescedb.php'); ?>
<?php require_once('../../../../core/includes/adminAccess.inc.php'); ?>
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

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE productprefs SET producttitle=%s, showcondition=%s, defaultcondition=%s, relatedproducts=%s, relatedtext=%s, relatedcategoryID=%s, relatedmanufacturerID=%s, featuredproducts=%s, viewedproducts=%s, alsobought=%s, commondetails=%s, commondetailstitle=%s, commondetailstext=%s, maintabtext=%s, optionsdisplay=%s, allowsharing=%s, allowcomments=%s, reviewstab=%s, commentscaptcha=%s, commentsmemberonly=%s, commentslocation=%s, samplemax=%s, sampletext=%s, producth1category=%s, defaultsort=%s, searchtype=%s, searchresults=%s, defaultitemunit=%s, text_custom_isbn_field=%s, productpagetemplateID=%s, featuredtext=%s, viewedtext=%s, alsoboughttext=%s, indexoptionsdisplay=%s, reviewemailtemplateID=%s, commentsemail=%s, text_filterby=%s WHERE ID=%s",
                       GetSQLValueString($_POST['producttitle'], "text"),
                       GetSQLValueString(isset($_POST['showcondition']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['defaultcondition'], "int"),
                       GetSQLValueString($_POST['relatedproducts'], "int"),
                       GetSQLValueString($_POST['relatedtext'], "text"),
                       GetSQLValueString($_POST['relatedcategoryID'], "int"),
                       GetSQLValueString($_POST['relatedmanufacturerID'], "int"),
                       GetSQLValueString($_POST['featuredproducts'], "int"),
                       GetSQLValueString($_POST['viewedproducts'], "int"),
                       GetSQLValueString($_POST['alsobought'], "int"),
                       GetSQLValueString(isset($_POST['commondetails']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['commondetailstitle'], "text"),
                       GetSQLValueString($_POST['commondetailstext'], "text"),
                       GetSQLValueString($_POST['maintabtext'], "text"),
                       GetSQLValueString($_POST['optionsdisplay'], "int"),
                       GetSQLValueString(isset($_POST['allowsharing']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['allowcomments']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['reviewstab']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['commentscaptcha']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['commentsmemberonly']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['commentslocation']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['samplemax'], "int"),
                       GetSQLValueString($_POST['sampletext'], "text"),
                       GetSQLValueString(isset($_POST['producth1category']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['defaultsort'], "text"),
                       GetSQLValueString($_POST['searchtype'], "int"),
                       GetSQLValueString($_POST['searchresults'], "int"),
                       GetSQLValueString($_POST['defaultitemunit'], "text"),
                       GetSQLValueString($_POST['text_custom_isbn_field'], "text"),
                       GetSQLValueString($_POST['productpagetemplateID'], "int"),
                       GetSQLValueString($_POST['featuredtext'], "text"),
                       GetSQLValueString($_POST['viewedtext'], "text"),
                       GetSQLValueString($_POST['alsoboughttext'], "text"),
                       GetSQLValueString($_POST['indexoptionsdisplay'], "int"),
                       GetSQLValueString($_POST['reviewemailtemplateID'], "int"),
                       GetSQLValueString(isset($_POST['commentsemail']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['text_filterby'], "text"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {  
if($_POST['searchtype']==2) {
	// first check whether full text indexes have been added as it will add again!
	$select = "SELECT DISTINCT index_name
	FROM INFORMATION_SCHEMA.STATISTICS
	WHERE (table_schema, table_name) = ('".$database_aquiescedb."',  'product' ) 
	AND index_type =  'FULLTEXT'";
	$result = mysql_query($select, $aquiescedb) or die(mysql_error());
	if(mysql_num_rows($result)==0) {
		$update="ALTER TABLE product ADD FULLTEXT(title,description,metakeywords,sku)";
		mysql_query($update, $aquiescedb) or die(mysql_error());
	}
}

if(isset($_POST['autometadescription']) && $_POST['autometadescription']>0) {
	createProductMetaDescriptions($_POST['autometadescription']);
}
$updateGoTo = "index.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo)); exit;
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

$varRegionID_rsProductPrefs = "-1";
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
$query_rsCategories = sprintf("SELECT * FROM productcategory WHERE regionID = %s ORDER BY title ASC", GetSQLValueString($varRegionID_rsCategories, "int"));
$rsCategories = mysql_query($query_rsCategories, $aquiescedb) or die(mysql_error());
$row_rsCategories = mysql_fetch_assoc($rsCategories);
$totalRows_rsCategories = mysql_num_rows($rsCategories);

$varRegionID_rsManufacturer = "1";
if (isset($regionID)) {
  $varRegionID_rsManufacturer = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsManufacturer = sprintf("SELECT productmanufacturer.ID, productmanufacturer.manufacturername FROM productmanufacturer WHERE productmanufacturer.statusID = 1 AND (productmanufacturer.regionID = 0 OR productmanufacturer.regionID = %s) ORDER BY productmanufacturer.manufacturername", GetSQLValueString($varRegionID_rsManufacturer, "int"));
$rsManufacturer = mysql_query($query_rsManufacturer, $aquiescedb) or die(mysql_error());
$row_rsManufacturer = mysql_fetch_assoc($rsManufacturer);
$totalRows_rsManufacturer = mysql_num_rows($rsManufacturer);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsTemplates = "SELECT ID, title FROM article WHERE sectionID = -1 AND versionofID IS NULL ORDER BY title ASC";
$rsTemplates = mysql_query($query_rsTemplates, $aquiescedb) or die(mysql_error());
$row_rsTemplates = mysql_fetch_assoc($rsTemplates);
$totalRows_rsTemplates = mysql_num_rows($rsTemplates);

$varRegionID_rsEmailTemplates = "1";
if (isset($regionID)) {
  $varRegionID_rsEmailTemplates = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsEmailTemplates = sprintf("SELECT ID, templatename FROM groupemailtemplate WHERE (regionID = 0 OR regionID = %s) AND groupemailtemplate.statusID = 1 ORDER BY groupemailtemplate.templatename", GetSQLValueString($varRegionID_rsEmailTemplates, "int"));
$rsEmailTemplates = mysql_query($query_rsEmailTemplates, $aquiescedb) or die(mysql_error());
$row_rsEmailTemplates = mysql_fetch_assoc($rsEmailTemplates);
$totalRows_rsEmailTemplates = mysql_num_rows($rsEmailTemplates);

function createProductMetaDescriptions($mode=1) {
	global $database_aquiescedb, $aquiescedb, $regionID;
	$select = "SELECT product.ID, title, description, metadescription FROM product LEFT JOIN productinregion ON (product.ID = productinregion.productID) WHERE statusID = 1 AND product.regionID = ".$regionID." GROUP BY product.ID";
	$result = mysql_query($select, $aquiescedb) or die(mysql_error().": ".$select);
	if(mysql_num_rows($result)>0) {
		while($row = mysql_fetch_assoc($result)) {
			if($mode=2 || trim($row['metadescription']) =="") { 
				$metadescription =$row['title']." ".strip_tags($row['description']);
				$update = "UPDATE  product SET metadescription = ".GetSQLValueString($metadescription, "text")." WHERE ID = ".$row['ID'];
				 mysql_query($update, $aquiescedb) or die(mysql_error());
			}			
		}
	}
}
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Product Options"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" --><link href="../../../css/defaultProducts.css" rel="stylesheet"  />
<style>
<!--
-->
</style>
<?php if(isset($body_class)) $body_class .= " products ";  ?><?php require_once('../../../../core/tinymce/tinymce.inc.php'); ?>
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" -->
     <div class="page class">
        <h1><i class="glyphicon glyphicon-shopping-cart"></i> <?php require_once('../../../../core/region/includes/chooseregion.inc.php'); ?>
Product Options</h1>
<nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
<li><a href="../index.php" class="link_undo"><i class="glyphicon glyphicon-arrow-left"></i> Back to products</a></li>
<li><a href="shopfront.php" class="link_manage"><i class="glyphicon glyphicon-cog"></i> Shop Front</a></li>
<li><a href="images.php" class="link_manage"><i class="glyphicon glyphicon-cog"></i> Images</a></li>
<li><a href="redirects.php" class="link_manage"><i class="glyphicon glyphicon-cog"></i> Redirects</a></li>
   

</ul></div></nav>         <form action="<?php echo $editFormAction; ?>" method="POST" name="form1" class="form-inline">
        
           <h2>
             Product Page
           </h2>
           <p><label>Template: 
             
               <select name="productpagetemplateID" id="productpagetemplateID" class="form-control" >
                 <option value="0" <?php if (!(strcmp(0, $row_rsProductPrefs['productpagetemplateID']))) {echo "selected=\"selected\"";} ?>>Classic</option>
                 <?php if(mysql_num_rows($rsTemplates)>0) {
do {  
?>
                 <option value="<?php echo $row_rsTemplates['ID']?>"<?php if (!(strcmp($row_rsTemplates['ID'], $row_rsProductPrefs['productpagetemplateID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsTemplates['title']?></option>
                 <?php
} while ($row_rsTemplates = mysql_fetch_assoc($rsTemplates));
  $rows = mysql_num_rows($rsTemplates);
  if($rows > 0) {
      mysql_data_seek($rsTemplates, 0);
	  $row_rsTemplates = mysql_fetch_assoc($rsTemplates);
  }
				 }
?>
               </select>
             </label>
           <a href="/articles/admin/">Manage Pages</a></p>
           <table  class="form-table">
             <tr>
               <td align="right"><label for="defaultsort">Default product sort:</label></td>
               <td><select name="defaultsort" class="form-control">
                 <option value="ordernum"  <?php if (!(strcmp("ordernum", $row_rsProductPrefs['defaultsort']))) {echo "selected=\"selected\"";} ?>>Category order set in Manage Products</option>
                 <option value="createddatetime_desc"  <?php if (!(strcmp("createddatetime_desc", $row_rsProductPrefs['defaultsort']))) {echo "selected=\"selected\"";} ?>>Newest first</option>
                 <option value="price_asc"  <?php if (!(strcmp("price_asc", $row_rsProductPrefs['defaultsort']))) {echo "selected=\"selected\"";} ?>>Price (low-high)</option>
                 <option value="price_desc" <?php if (!(strcmp("price_desc", $row_rsProductPrefs['defaultsort']))) {echo "selected=\"selected\"";} ?>>Price (high-low)</option>
                 <option value="title_asc"  <?php if (!(strcmp("title_asc", $row_rsProductPrefs['defaultsort']))) {echo "selected=\"selected\"";} ?>>Alphabetically</option>
                 <option value="bestselling_desc" <?php if (!(strcmp("bestselling_desc", $row_rsProductPrefs['defaultsort']))) {echo "selected=\"selected\"";} ?>>Best selling</option>
               </select></td>
             </tr>
           </table>
       
       
           <p>
             <label>
               <input <?php if (!(strcmp($row_rsProductPrefs['producth1category'],1))) {echo "checked=\"checked\"";} ?> name="producth1category" type="checkbox" id="producth1category" value="1">
               Show as category title (product as sub-title)</label>
         </p>
           <p>Display product options on index page: 
             <label>
               <input <?php if (!(strcmp($row_rsProductPrefs['indexoptionsdisplay'],"0"))) {echo "checked=\"checked\"";} ?> type="radio" name="indexoptionsdisplay" value="0" >
               No</label>
           &nbsp;&nbsp;&nbsp;
             <label>
               <input <?php if (!(strcmp($row_rsProductPrefs['indexoptionsdisplay'],"1"))) {echo "checked=\"checked\"";} ?> type="radio" name="indexoptionsdisplay" value="1" >
               Yes </label>
            
           </p>
           
           
           
           
           
        <p>Display product options as: 
             <label>
               <input <?php if (!(strcmp($row_rsProductPrefs['optionsdisplay'],"1"))) {echo "checked=\"checked\"";} ?> type="radio" name="optionsdisplay" value="1" id="optionsdisplay_0">
               Drop down select menu</label>
           &nbsp;&nbsp;&nbsp;
             <label>
               <input <?php if (!(strcmp($row_rsProductPrefs['optionsdisplay'],"2"))) {echo "checked=\"checked\"";} ?> type="radio" name="optionsdisplay" value="2" id="optionsdisplay_1">
               Radio buttons </label>
            
           </p>
           
           
           <p><label>
               <input <?php if (!(strcmp($row_rsProductPrefs['showcondition'],1))) {echo "checked=\"checked\"";} ?> name="showcondition" type="checkbox" id="showcondition" value="1">
               Show condition. </label><label>Default condition: 
                <input <?php if (!(strcmp($row_rsProductPrefs['defaultcondition'],"0"))) {echo "checked=\"checked\"";} ?> type="radio" name="defaultcondition" value="0" id="condition_0">
New</label>
               &nbsp;&nbsp;&nbsp;
                <label>
                  <input <?php if (!(strcmp($row_rsProductPrefs['defaultcondition'],"1"))) {echo "checked=\"checked\"";} ?> type="radio" name="defaultcondition" value="1" id="condition_1">
                  Used</label>
               
              </p>
           <h2>Related Products</h2>
           <table class="form-table">
             <tr>
               <td class="top text-right"><label for="relatedproducts">Show related products:</label></td>
               <td><input name="relatedproducts" type="text"  id="relatedproducts" value="<?php echo $row_rsProductPrefs['relatedproducts']; ?>" size="2" maxlength="2" class="form-control" />
                 <label>Text:
                   <input name="relatedtext" type="text" id="relatedtext" value="<?php echo $row_rsProductPrefs['relatedtext']; ?>" size="50" maxlength="50" class="form-control" > 
                   <select name="relatedcategoryID" id="relatedcategoryID" class="form-control" >
                    <option value="-2" <?php if (!(strcmp(-1, $row_rsProductPrefs['relatedcategoryID']))) {echo "selected=\"selected\"";} ?>>Show only related</option>
                     <option value="0" <?php if (!(strcmp(0, $row_rsProductPrefs['relatedcategoryID']))) {echo "selected=\"selected\"";} ?>>Show items from same category</option>
                     <option value="-1" <?php if (!(strcmp(-1, $row_rsProductPrefs['relatedcategoryID']))) {echo "selected=\"selected\"";} ?>>Show items from any category</option>
                    
                     <?php
do {  
?>
<option value="<?php echo $row_rsCategories['ID']?>"<?php if (!(strcmp($row_rsCategories['ID'], $row_rsProductPrefs['relatedcategoryID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsCategories['title']?></option>
                     <?php
} while ($row_rsCategories = mysql_fetch_assoc($rsCategories));
  $rows = mysql_num_rows($rsCategories);
  if($rows > 0) {
      mysql_data_seek($rsCategories, 0);
	  $row_rsCategories = mysql_fetch_assoc($rsCategories);
  }
?>
                   </select>
                   <select name="relatedmanufacturerID" id="relatedmanufacturerID" class="form-control" >
                     <option value="0" <?php if (!(strcmp(0, $row_rsProductPrefs['relatedmanufacturerID']))) {echo "selected=\"selected\"";} ?>>From any manufacturer</option>
                     <?php
do {  
?>
<option value="<?php echo $row_rsManufacturer['ID']?>"<?php if (!(strcmp($row_rsManufacturer['ID'], $row_rsProductPrefs['relatedmanufacturerID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsManufacturer['manufacturername']?></option>
                     <?php
} while ($row_rsManufacturer = mysql_fetch_assoc($rsManufacturer));
  $rows = mysql_num_rows($rsManufacturer);
  if($rows > 0) {
      mysql_data_seek($rsManufacturer, 0);
	  $row_rsManufacturer = mysql_fetch_assoc($rsManufacturer);
  }
?>
                   </select>
                 </label></td>
             </tr>
             <tr>
               <td  class="top text-right"><label for="viewedproducts">Show viewed products:</label></td>
               <td  class="top"><input name="viewedproducts" type="text"  id="viewedproducts" value="<?php echo $row_rsProductPrefs['viewedproducts']; ?>" size="2" maxlength="2" class="form-control" />
                  Text:
                   <input name="viewedtext" type="text" id="viewedtext" value="<?php echo $row_rsProductPrefs['viewedtext']; ?>" size="50" maxlength="50" class="form-control" > 
                 (Will show recently viewed products by current customer)</label></td>
             </tr>
             <tr>
               <td  class="top text-right"><label for="alsobought">Show also bought:</label></td>
               <td class="top"><input name="alsobought" type="text"  id="alsobought" value="<?php echo $row_rsProductPrefs['alsobought']; ?>" size="2" maxlength="2" class="form-control" /> Text:
                   <input name="alsoboughttext" type="text" id="alsoboughttext" value="<?php echo $row_rsProductPrefs['alsoboughttext']; ?>" size="50" maxlength="50" class="form-control" > 
                 (Will show what customers who bought a product also bought)</label></td>
             </tr>
             <tr>
               <td class="top text-right"><label for="featuredproducts">Show featured:</label></td>
               <td  class="top"><input name="featuredproducts" type="text" id="featuredproducts" value="<?php echo $row_rsProductPrefs['featuredproducts']; ?>" size="2" maxlength="2" class="form-control" > Text:
                   <input name="featuredtext" type="text" id="featuredtext" value="<?php echo $row_rsProductPrefs['featuredtext']; ?>" size="50" maxlength="50" class="form-control" > 
                 (Will show featured products on designated pages)</td>
             </tr>
            </table>
           <h2>Terminology</h2>
           <p><label>Default item unit: 
             
               <input name="defaultitemunit" type="text" id="defaultitemunit" value="<?php echo $row_rsProductPrefs['defaultitemunit']; ?>" size="20" maxlength="20" class="form-control" >
             </label>
           </p>
           <h2>Product Search</h2>
           <p>Search type:
             <label>
               <input <?php if (!(strcmp($row_rsProductPrefs['searchtype'],"1"))) {echo "checked=\"checked\"";} ?> type="radio" name="searchtype" value="1" id="searchtype_0">
               Simple</label>
             &nbsp;&nbsp;&nbsp;
             <label>
               <input <?php if (!(strcmp($row_rsProductPrefs['searchtype'],"2"))) {echo "checked=\"checked\"";} ?> type="radio" name="searchtype" value="2" id="searchtype_1">
               Score based</label>
          </p>
          
          <p>Results type:
             <label>
               <input <?php if (!(strcmp($row_rsProductPrefs['searchresults'],"1"))) {echo "checked=\"checked\"";} ?> type="radio" name="searchresults" value="1" >
               List</label>
             &nbsp;&nbsp;&nbsp;
             <label>
               <input <?php if (!(strcmp($row_rsProductPrefs['searchresults'],"2"))) {echo "checked=\"checked\"";} ?> type="radio" name="searchresults" value="2">
               Grid</label>
          </p>
          
          
           <h2>Meta Descriptions</h2>
           <p>
             <label>
               <input name="autometadescription" type="radio" id="autometadescription_0" value="0" checked="CHECKED">
              Leave as is</label>
            &nbsp;&nbsp;&nbsp;
             <label>
               <input type="radio" name="autometadescription" value="1" id="autometadescription_1">
               Auto create for those empty</label>
             &nbsp;&nbsp;&nbsp;
             <label>
               <input type="radio" name="autometadescription" value="2" id="autometadescription_2">
               Auto create for all</label>
            
           </p>
           <h2>Titles</h2>
           <p>
             <label for="producttitle">Title layout (advanced):</label>
             <input name="producttitle" type="text" id="producttitle" value="<?php echo $row_rsProductPrefs['producttitle']; ?>" size="50" maxlength="100" class="form-control" >
           </p>
        <p><label for="text_custom_isbn_field">Custom field name:</label>
        <input name="text_custom_isbn_field" type="text" id="text_custom_isbn_field" value="<?php echo $row_rsProductPrefs['text_custom_isbn_field']; ?>" size="50" maxlength="100" class="form-control" >
        </p>
         <h2>Index page</h2>
        <p>Filter by text: <input name="text_filterby" type="text" id="text_filterby" value="<?php echo $row_rsProductPrefs['text_filterby']; ?>" size="10" maxlength="10" class="form-control" > </p>
        
         <h2>Sharing</h2>
         
          <p>
            <label>
              <input <?php if (!(strcmp($row_rsProductPrefs['allowsharing'],1))) {echo "checked=\"checked\"";} ?> name="allowsharing" type="checkbox" id="allowsharing" value="1" />
              Show sharing links (e.g. Email, Facebook, Twitter)</label>
          </p>
          <p>
            <label>
              <input <?php if (!(strcmp($row_rsProductPrefs['allowcomments'],1))) {echo "checked=\"checked\"";} ?> name="allowcomments" type="checkbox" id="allowcomments" value="1" />
              Allow customer reviews</label>&nbsp;&nbsp;
            <label><input <?php if (!(strcmp($row_rsProductPrefs['commentsemail'],1))) {echo "checked=\"checked\"";} ?> name="commentsemail" type="checkbox" id="commentsemail" value="1">Ask email</label>&nbsp;&nbsp;
            <label><input <?php if (!(strcmp($row_rsProductPrefs['commentslocation'],1))) {echo "checked=\"checked\"";} ?> name="commentslocation" type="checkbox" id="commentslocation" value="1">Ask location</label>&nbsp;&nbsp;
            <label><input <?php if (!(strcmp($row_rsProductPrefs['commentscaptcha'],1))) {echo "checked=\"checked\"";} ?> name="commentscaptcha" type="checkbox" id="commentscaptcha" value="1">Use CAPTCHA</label>&nbsp;&nbsp;
            <label><input <?php if (!(strcmp($row_rsProductPrefs['commentsmemberonly'],1))) {echo "checked=\"checked\"";} ?> name="commentsmemberonly" type="checkbox" id="commentsmemberonly" value="1">&nbsp;&nbsp;
            Members only</label>
              <label><input <?php if (!(strcmp($row_rsProductPrefs['reviewstab'],1))) {echo "checked=\"checked\"";} ?> name="reviewstab" type="checkbox" id="reviewstab" value="1">
            Display in tab</label>
          </p>
          <p><label>Send email to reviewer: 
            
              <select name="reviewemailtemplateID" id="reviewemailtemplateID" class="form-control" >
              <option value="">Choose...</option>
              
              
                <?php
do {  
?>
                <option value="<?php echo $row_rsEmailTemplates['ID']?>"<?php if (!(strcmp($row_rsEmailTemplates['ID'], $row_rsProductPrefs['reviewemailtemplateID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsEmailTemplates['templatename']?></option>
                <?php
} while ($row_rsEmailTemplates = mysql_fetch_assoc($rsEmailTemplates));
  $rows = mysql_num_rows($rsEmailTemplates);
  if($rows > 0) {
      mysql_data_seek($rsEmailTemplates, 0);
	  $row_rsEmailTemplates = mysql_fetch_assoc($rsEmailTemplates);
  }
?>
              </select>
            </label>
          </p>
          <h2>Samples</h2>
          <p>Free (or paid) samples must be set up as a product, wich can be hodden from listings.</p>
  <p>Free samples can be added to categories.</p>
  <p>
    <label for="sampletext">Free Sample text (will appear in basket next to free sample items): </label>
  </p>
  <textarea name="sampletext" cols="50" rows="5" id="sampletext" class="form-control" ><?php echo $row_rsProductPrefs['sampletext']; ?></textarea>
  <p>
    <label>Maximum free samples per basket: <input name="samplemax" type="text" value="<?php echo $row_rsProductPrefs['samplemax']; ?>" size="5" maxlength="3" class="form-control" ></label> 
    (0= unlimited)</p>
    
    
    
    
    <h2>Common Details Tab</h2>
    
    <p>Enter details below that you wish to appear with every product in shop (unless opted out).</p>
  <p>
    <label>
      <input <?php if (!(strcmp($row_rsProductPrefs['commondetails'],1))) {echo "checked=\"checked\"";} ?> name="commondetails" type="checkbox" id="commondetails" value="1">
      Display common details</label>
  </p>
  <p>
    <label>Title:
      <input name="commondetailstitle" type="text"  id="commondetailstitle" value="<?php echo $row_rsProductPrefs['commondetailstitle']; ?>" size="50" maxlength="50" class="form-control" >
    </label>
  </p>
  <p>
    <textarea name="commondetailstext" id="commondetailstext" cols="45" rows="10" class="tinymce form-control" ><?php echo $row_rsProductPrefs['commondetailstext']; ?></textarea>
  </p>
  <p>
    <label>Main tab name:
      <input name="maintabtext" type="text" id="maintabtext" value="<?php echo $row_rsProductPrefs['maintabtext']; ?>" size="50" maxlength="50"  autocomplete='off' readonly onfocus="this.removeAttribute('readonly');" class="form-control" >
    </label>
  </p>
           <p>
  <button type="submit" name="button" id="button" class="btn btn-primary">Save changes</button>
             
             <input name="ID" type="hidden" id="ID" value="<?php echo $row_rsProductPrefs['ID']; ?>">
             <input name="modifiedbyID" type="hidden" id="modifiedbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>">
             <input name="createddatetime" type="hidden" id="createddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>">
             <input type="hidden" name="MM_update" value="form1">
           </p>
</form>
       </div>
        <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsProductPrefs);

mysql_free_result($rsCategories);

mysql_free_result($rsManufacturer);

mysql_free_result($rsTemplates);

mysql_free_result($rsEmailTemplates);
?>
