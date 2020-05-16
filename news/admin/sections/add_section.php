<?php require_once('../../../Connections/aquiescedb.php'); ?><?php require_once('../../../core/includes/adminAccess.inc.php'); ?>
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

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
$_POST['longID'] = preg_replace("/[^a-zA-Z0-9_\-]/", "", $_POST['longID']); // clean
}


if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
  $insertSQL = sprintf("INSERT INTO newssection (longID, metadescription, metakeywords, sectioname, `description`, accesslevel, editaccess, groupreadID, groupwriteID, requiresapproval, allowcomments, reportabuse, allowbody, allowphoto, allowattachment, statusID, createdbyID, createddatetime, orderby, showeventdatetime) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['longID'], "text"),
                       GetSQLValueString($_POST['metadescription'], "text"),
                       GetSQLValueString($_POST['metakeywords'], "text"),
                       GetSQLValueString($_POST['sectioname'], "text"),
                       GetSQLValueString($_POST['description'], "text"),
                       GetSQLValueString($_POST['accesslevel'], "int"),
                       GetSQLValueString($_POST['editaccess'], "int"),
                       GetSQLValueString($_POST['groupreadID'], "int"),
                       GetSQLValueString($_POST['groupwriteID'], "int"),
                       GetSQLValueString(isset($_POST['requiresapproval']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['allowcomments'], "int"),
                       GetSQLValueString(isset($_POST['reportabuse']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['showbody']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['allowphoto']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['allowattachment']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['statusID'], "int"),
                       GetSQLValueString($_POST['createdbyID'], "int"),
                       GetSQLValueString($_POST['createddatetime'], "date"),
                       GetSQLValueString($_POST['orderby'], "int"),
                       GetSQLValueString(isset($_POST['showeventdatetime']) ? "true" : "", "defined","1","0"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($insertSQL, $aquiescedb) or die(mysql_error());
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
  $insertGoTo = "index.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
    $insertGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $insertGoTo)); exit;
}

$colname_rsLoggedIn = "1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID FROM users WHERE username = '%s'", $colname_rsLoggedIn);
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsAccessLevels = "SELECT ID, CONCAT(name,'s') AS accesslevel FROM usertype WHERE ID > 0 ORDER BY ID ASC";
$rsAccessLevels = mysql_query($query_rsAccessLevels, $aquiescedb) or die(mysql_error());
$row_rsAccessLevels = mysql_fetch_assoc($rsAccessLevels);
$totalRows_rsAccessLevels = mysql_num_rows($rsAccessLevels);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsGroups = "SELECT ID, groupname FROM usergroup ORDER BY groupname ASC";
$rsGroups = mysql_query($query_rsGroups, $aquiescedb) or die(mysql_error());
$row_rsGroups = mysql_fetch_assoc($rsGroups);
$totalRows_rsGroups = mysql_num_rows($rsGroups);
?><!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" --><title><?php $pageTitle = "Add Post Section"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title><!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" --><?php require_once('../../../core/tinymce/tinymce.inc.php'); ?>
<script src="../../../SpryAssets/SpryValidationTextField.js"></script>
<link href="../../../SpryAssets/SpryValidationTextField.css" rel="stylesheet"  /><?php if (!(defined("MOD_REWRITE") || isset($_SERVER['HTTP_MOD_REWRITE']))) { // no mod re-write so hide URL option ?>
<style><!--
.longID { display:none; }
<?php if($totalRows_rsGroups==0) { echo ".groups { display: 'none' }"; } ?>
--></style>
<?php } ?><script language="JavaScript">
function seoPopulate(title,content) {
var longID = title.replace(/[^a-zA-Z 0-9]+/g,'');
longID = longID.replace(/[ ]+/g,'-');
if (document.getElementById('longID').value == "") document.getElementById('longID').value = longID;
if (document.getElementById('metadescription').value == "") document.getElementById('metadescription').value = content;
if (document.getElementById('metakeywords').value == "") document.getElementById('metakeywords').value = title;
} // end function
</script>
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
    
<div class="page news">
   <h1><i class="glyphicon glyphicon-bullhorn"></i> Add Post Section</h1>
   
<form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1">
  <table class="form-table"> <tr>
         <td class="text-nowrap text-right">Name:</td>
         <td><span id="sprytextfield1">
           <input name="sectioname" type="text"  value="" size="50" maxlength="50" onBlur="seoPopulate(this.value, this.value);" class="form-control" />
<span class="textfieldRequiredMsg">A name is required.</span></span></td>
       </tr><tr class="longID">
            <td class="text-nowrap text-right top">URL name:</td>
            <td>
              <input name="longID" type="text"  id="longID" size="50" maxlength="100" class="form-control"/>
            </td>
          </tr> <tr>
         <td class="text-nowrap text-right">Can view:</td>
         <td class="form-inline"><select name="accesslevel" class="form-control">
           <option value="0" >Everyone</option>
           <?php
do {  
?>
           <option value="<?php echo $row_rsAccessLevels['ID']?>"><?php echo $row_rsAccessLevels['accesslevel']?></option>
           <?php
} while ($row_rsAccessLevels = mysql_fetch_assoc($rsAccessLevels));
  $rows = mysql_num_rows($rsAccessLevels);
  if($rows > 0) {
      mysql_data_seek($rsAccessLevels, 0);
	  $row_rsAccessLevels = mysql_fetch_assoc($rsAccessLevels);
  }
?>
           </select>
           <select name="groupreadID" id="groupreadID" class="form-control group">
             <option value="0">Any group</option>
             <?php
do {  
?>
             <option value="<?php echo $row_rsGroups['ID']?>"><?php echo $row_rsGroups['groupname']?></option>
             <?php
} while ($row_rsGroups = mysql_fetch_assoc($rsGroups));
  $rows = mysql_num_rows($rsGroups);
  if($rows > 0) {
      mysql_data_seek($rsGroups, 0);
	  $row_rsGroups = mysql_fetch_assoc($rsGroups);
  }
?>
          </select></td>
       </tr> <tr>
         <td class="text-nowrap text-right">Can post:</td>
         <td class="form-inline"><select name="editaccess"  id="editaccess" class="form-control">
           <?php
do {  
?>
           <option value="<?php echo $row_rsAccessLevels['ID']?>"<?php if (!(strcmp($row_rsAccessLevels['ID'], 8))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsAccessLevels['accesslevel']?></option>
           <?php
} while ($row_rsAccessLevels = mysql_fetch_assoc($rsAccessLevels));
  $rows = mysql_num_rows($rsAccessLevels);
  if($rows > 0) {
      mysql_data_seek($rsAccessLevels, 0);
	  $row_rsAccessLevels = mysql_fetch_assoc($rsAccessLevels);
  }
?>
           </select>
           <select name="groupwriteID" id="groupwriteID" class="form-control group">
             <option value="0">Any group</option>
             <?php
do {  
?>
             <option value="<?php echo $row_rsGroups['ID']?>"><?php echo $row_rsGroups['groupname']?></option>
             <?php
} while ($row_rsGroups = mysql_fetch_assoc($rsGroups));
  $rows = mysql_num_rows($rsGroups);
  if($rows > 0) {
      mysql_data_seek($rsGroups, 0);
	  $row_rsGroups = mysql_fetch_assoc($rsGroups);
  }
?>
           </select> 
           <label>
             <input type="checkbox" name="requiresapproval" id="requiresapproval" />
             Requires admin approval</label>&nbsp;&nbsp;&nbsp;<label><input type="checkbox" name="reportabuse" id="reportabuse" />
          Link to report abuse</label></td>
       </tr><tr>
         <td class="text-nowrap text-right">Member post options:</td>
         <td><label>
           <input name="showbody" type="checkbox" id="showbody" checked="checked" />
           Allow body</label> &nbsp;&nbsp;&nbsp; <label>
           <input name="allowphoto" type="checkbox" id="allowphoto" checked="checked" />
           Allow photo</label> &nbsp;&nbsp;&nbsp; <label>
           <input type="checkbox" name="allowattachment" id="allowattachment" />
           Allow attachment</label></td>
       </tr> <tr>
         <td class="text-nowrap text-right"><label for="allowcomments">Comments allowed from:</label></td>
         <td><select name="allowcomments" id="allowcomments" class="form-control">
           <option value="0" >Nobody</option>
           <?php
do {  
?>
           <option value="<?php echo $row_rsAccessLevels['ID']?>"<?php if (!(strcmp($row_rsAccessLevels['ID'], $row_rsSection['allowcomments']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsAccessLevels['accesslevel']?></option>
           <?php
} while ($row_rsAccessLevels = mysql_fetch_assoc($rsAccessLevels));
  $rows = mysql_num_rows($rsAccessLevels);
  if($rows > 0) {
      mysql_data_seek($rsAccessLevels, 0);
	  $row_rsAccessLevels = mysql_fetch_assoc($rsAccessLevels);
  }
?>
           </select></td>
       </tr> <tr>
         <td class="text-nowrap text-right"><label for="showeventdatetime">Show event date:</label></td>
         <td><input type="checkbox" name="showeventdatetime" id="showeventdatetime"></td>
       </tr>
       <tr>
         <td class="text-nowrap text-right">Order Posts by:</td>
         <td><label>
           <input name="orderby"  type="radio" id="orderby_0" value="1" checked="CHECKED">
           Date posted</label>
           (newest first)&nbsp;&nbsp;&nbsp;
           <label>
             <input  type="radio" name="orderby" value="2" >
             Date posted (oldest first)</label>
           &nbsp;&nbsp;&nbsp;
           <label>
             <input type="radio" name="orderby" value="3" >
             Order in editor (drag and drop)</label>
             
              &nbsp;&nbsp;&nbsp;
           <label>
             <input type="radio" name="orderby" value="4" >
             Date from today (if event)</label>
          </td>
       </tr>  
      
       <tr>
         <td colspan="2" class="text-nowrap  ">Header text
           (optional):</td>
       </tr> <tr>
         <td colspan="2" class="text-nowrap  "><textarea name="description" id="description" cols="45" rows="5" class="tinymce form-control"></textarea></td>
        </tr> <tr>
         <td colspan="2" class="text-nowrap  "><button type="submit" class="btn btn-primary" >Add Section</button></td>
        </tr>
     </table>
     <input type="hidden" name="statusID" value="1" />
     <input type="hidden" name="createdbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
     <input type="hidden" name="createddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
     <input type="hidden" name="MM_insert" value="form1" /><input type="hidden" name="metakeywords" id="metakeywords" />
  <input type="hidden" name="metadescription" id="metadescription"/>
   <input type="hidden" name="regionID" id="regionID" value="<?php echo isset($regionID) ? $regionID : 1; ?>">
</form>
   <p>&nbsp;</p>
<script>
<!--
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1");
//-->
   </script></div>
   <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsAccessLevels);

mysql_free_result($rsGroups);
?>
