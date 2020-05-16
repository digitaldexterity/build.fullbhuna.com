<?php require_once('../../../../Connections/aquiescedb.php'); ?><?php
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

$MM_restrictGoTo = "../../../../login/index.php?notloggedin=true";
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

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
	// check for duplication 
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$select = "SELECT linkkeywords FROM keywordlinks";
	$result = mysql_query($select, $aquiescedb) or die(mysql_error());
	if(mysql_num_rows($result)>0) {
		while($row = mysql_fetch_assoc($result)) {
			if(stristr($row['linkkeywords'], $_POST['linkkeywords']) || stristr($_POST['linkkeywords'], $row['linkkeywords'])){
				$error = $_POST['linkkeywords']." conflicts with existing ".$row['linkkeywords'].". Keyphrases than contain other keyphrases can cause unpredicatable results.";
				unset($_POST["MM_insert"]);
			}
		}
	}
}


if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
  $insertSQL = sprintf("INSERT INTO keywordlinks (linkkeywords, linkURL, linktitle, createdbyID, createddatetime, statusID) VALUES (%s, %s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['linkkeywords'], "text"),
                       GetSQLValueString($_POST['linkURL'], "text"),
                       GetSQLValueString($_POST['linktitle'], "text"),
                       GetSQLValueString($_POST['createdbyID'], "int"),
                       GetSQLValueString($_POST['createddatetime'], "date"),
                       GetSQLValueString($_POST['statusID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($insertSQL, $aquiescedb) or die(mysql_error());

  $insertGoTo = "index.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
    $insertGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $insertGoTo));
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

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLinks = "SELECT 2 AS type, CONCAT('2|',product.ID,'|', productcategory.longID,'/',product.longID) AS ID, product.title, productcategory.title AS sectionname, product.ordernum, productcategory.ordernum AS sectionordernum FROM product LEFT JOIN productcategory ON (product.productcategoryID = productcategory.ID) WHERE product.statusID = 1 UNION SELECT  1 AS type, CONCAT('1|',article.ID,'|',articlesection.longID,'/',article.longID) AS ID, article.title, articlesection.description AS sectionname, article.ordernum, articlesection.ordernum AS sectionordernum  FROM article LEFT JOIN articlesection ON (article.sectionID = articlesection.ID) WHERE article.statusID = 1  AND article.versionofID IS NULL ORDER BY type, sectionordernum,  ordernum";
$rsLinks = mysql_query($query_rsLinks, $aquiescedb) or die(mysql_error());
$row_rsLinks = mysql_fetch_assoc($rsLinks);
$totalRows_rsLinks = mysql_num_rows($rsLinks);
?><!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" --><title><?php $pageTitle = "Update Auto Link"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title><!-- InstanceEndEditable -->
<?php require_once('../../includes/seo.inc.php'); ?>
<?php require_once('../../../includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script src="../../../../SpryAssets/SpryValidationTextField.js"></script>
<link href="../../../../SpryAssets/SpryValidationTextField.css" rel="stylesheet"  />
<script>
function prefill() {
if (document.getElementById('linktitle').value =='') document.getElementById('linktitle').value = document.getElementById('linkkeywords').value;
}

function insertLink(linkID, linkTitle) {
var reWrite = <?php echo (defined("MOD_REWRITE") || isset($_SERVER['HTTP_MOD_REWRITE'])) ? "true" : "false"; ?>;
var separatorPos1 = linkID.indexOf('|');
if (separatorPos1) { // separators exist
var type = linkID.substr(0,separatorPos1);
var separatorPos2 = linkID.indexOf('|',separatorPos1+1); // find next occurance
var ID = linkID.substr(separatorPos1+1,separatorPos2-separatorPos1);
var longID = linkID.substr(separatorPos2+1);
if (type == 1) { //article
if (reWrite === true) {
	linkURL = '/'+longID; }
	else {
		linkURL = "/articles/article.php?articleID="+ID;
	}
} // end article
	else if(type==2 ) {// products
	if (reWrite === true) {
	linkURL = "/shop/"+longID; }
	else {
		linkURL = "/products/product.php?productID="+ID;
	}
	} // end products
document.getElementById('linkURL').value = linkURL;
}// end separators exist
if(document.getElementById('linkkeywords').value == "") { // auto fill keywords for ease if not done
document.getElementById('linkkeywords').value = linkTitle;
document.getElementById('linktitle').value = linkTitle;
}
} // end function

</script>
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../../includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" -->
        <div class="page seo">
   <h1><i class="glyphicon glyphicon-globe"></i> Add Auto Link   
        </h1><?php require_once('../../../includes/alert.inc.php'); ?>

   <p>Note, this link will only appear in new texts. You will need to update existing texts to include new keyword links.</p>
   <p><strong>TIP:</strong> For quickness, you can choose a value from the drop down menu which will fill in all fields.</p>
   <form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1">
     <table class="form-table"> <tr>
         <td class="text-nowrap text-right">Keyword or phrase:</td>
         <td><span id="sprytextfield1">
           <input name="linkkeywords" id="linkkeywords" type="text"   size="60" maxlength="255" onBlur="prefill()"  value="<?php echo htmlentities($_POST['linkkeywords'], ENT_COMPAT, "UTF-8"); ?>"  class="form-control"/>
          <span class="textfieldRequiredMsg">A value is required.</span></span></td>
       </tr> <tr>
         <td class="text-nowrap text-right">Links to:</td>
         <td><label>
           <select name="pageID"  id="pageID" onChange="insertLink(this.value,this.options[this.selectedIndex].text)" class="form-control">
          
             <option value="/">Choose a page or enter a link manually below...</option>
             <?php
do {  
?>
             <option value="<?php echo $row_rsLinks['ID']?>"><?php echo isset($row_rsLinks['sectionname']) ? $row_rsLinks['sectionname']." : " : "";  echo $row_rsLinks['title']?></option>
             <?php
} while ($row_rsLinks = mysql_fetch_assoc($rsLinks));
  $rows = mysql_num_rows($rsLinks);
  if($rows > 0) {
      mysql_data_seek($rsLinks, 0);
	  $row_rsLinks = mysql_fetch_assoc($rsLinks);
  }
?>
           </select>
         </label></td>
       </tr> <tr>
         <td class="text-nowrap text-right">URL:</td>
         <td><span id="sprytextfield2">
           <input name="linkURL" id="linkURL" type="text"   size="60" maxlength="255"   value="<?php echo htmlentities($_POST['linkURL'], ENT_COMPAT, "UTF-8"); ?>" class="form-control"/>
          <span class="textfieldRequiredMsg">A value is required.</span></span></td>
       </tr> <tr>
         <td class="text-nowrap text-right">Optional Tooltip:</td>
         <td><input name="linktitle" id="linktitle" type="text"   size="60" maxlength="255" value="<?php echo htmlentities($_POST['linktitle'], ENT_COMPAT, "UTF-8"); ?>"  class="form-control"/></td>
       </tr> <tr>
         <td class="text-nowrap text-right">&nbsp;</td>
         <td><button type="submit" class="btn btn-primary" >Add Auto Link</button></td>
       </tr>
     </table>
     <input type="hidden" name="createdbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
     <input type="hidden" name="createddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
     <input type="hidden" name="statusID" value="1" />
     <input type="hidden" name="MM_insert" value="form1" />
   </form>
  
   <script>
<!--
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1");
var sprytextfield2 = new Spry.Widget.ValidationTextField("sprytextfield2");
//-->
</script></div>
   <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsLinks);
?>


