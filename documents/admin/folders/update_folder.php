<?php require_once('../../../Connections/aquiescedb.php'); ?>
<?php require_once('../../../core/includes/adminAccess.inc.php'); ?>
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
  $updateSQL = sprintf("UPDATE documentcategory SET categoryname=%s, `description`=%s, subcatofID=%s, accessID=%s, writeaccess=%s, groupreadID=%s, groupwriteID=%s, active=%s, modifiedbyID=%s, modifieddatetime=%s WHERE ID=%s",
                       GetSQLValueString($_POST['categoryname'], "text"),
                       GetSQLValueString($_POST['description'], "text"),
                       GetSQLValueString($_POST['subcatofID'], "int"),
                       GetSQLValueString($_POST['accessID'], "int"),
                       GetSQLValueString($_POST['writeaccess'], "int"),
                       GetSQLValueString($_POST['groupreadID'], "int"),
                       GetSQLValueString($_POST['groupwriteID'], "int"),
                       GetSQLValueString(isset($_POST['active']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['modifiedbyID'], "int"),
                       GetSQLValueString($_POST['modifieddatetime'], "date"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());

  $updateGoTo = "index.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo));
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

$colname_rsCategory = "-1";
if (isset($_GET['categoryID'])) {
  $colname_rsCategory = $_GET['categoryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsCategory = sprintf("SELECT * FROM documentcategory WHERE ID = %s", GetSQLValueString($colname_rsCategory, "int"));
$rsCategory = mysql_query($query_rsCategory, $aquiescedb) or die(mysql_error());
$row_rsCategory = mysql_fetch_assoc($rsCategory);
$totalRows_rsCategory = mysql_num_rows($rsCategory);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsAccessLevels = "SELECT usertype.ID, usertype.name AS accesslevel FROM usertype WHERE usertype.ID > 0 ORDER BY usertype.ID ASC";
$rsAccessLevels = mysql_query($query_rsAccessLevels, $aquiescedb) or die(mysql_error());
$row_rsAccessLevels = mysql_fetch_assoc($rsAccessLevels);
$totalRows_rsAccessLevels = mysql_num_rows($rsAccessLevels);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsGroups = "SELECT ID, groupname FROM usergroup ORDER BY groupname ASC";
$rsGroups = mysql_query($query_rsGroups, $aquiescedb) or die(mysql_error());
$row_rsGroups = mysql_fetch_assoc($rsGroups);
$totalRows_rsGroups = mysql_num_rows($rsGroups);

$varCategoryID_rsInFolder = "0";
if (isset($_GET['categoryID'])) {
  $varCategoryID_rsInFolder = $_GET['categoryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsInFolder = sprintf("SELECT parentcategory.* FROM documentcategory LEFT JOIN documentcategory AS parentcategory ON (documentcategory.subCatOfID = parentcategory.ID) WHERE documentcategory.ID = %s", GetSQLValueString($varCategoryID_rsInFolder, "int"));
$rsInFolder = mysql_query($query_rsInFolder, $aquiescedb) or die(mysql_error());
$row_rsInFolder = mysql_fetch_assoc($rsInFolder);
$totalRows_rsInFolder = mysql_num_rows($rsInFolder);
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Update Folder"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
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
      <h1><i class="glyphicon glyphicon-folder-open"></i> Manage Folder</h1>
     
      <form method="post" name="form1" action="<?php echo $editFormAction; ?>">
        <table class="form-table">
          <tr>
            <td class="text-nowrap text-right top">Folder name:</td>
            <td><input type="text" name="categoryname" value="<?php echo htmlentities($row_rsCategory['categoryname'], ENT_COMPAT, 'utf-8'); ?>" size="32" class="form-control" ></td>
          </tr>
          <tr>
            <td class="text-nowrap text-right top">Description:</td>
            <td><input type="text" name="description" value="<?php echo htmlentities($row_rsCategory['description'], ENT_COMPAT, 'utf-8'); ?>" size="32" class="form-control"></td>
          </tr>
          <tr>
            <td class="text-nowrap text-right top">Parent category:</td>
            <td><select name="subcatofID" class="form-control">
              <option value="" <?php if (!(strcmp("", $row_rsCategory['subcatofID']))) {echo "selected=\"selected\"";} ?>>Home</option>
              <?php
do {  
?>
              <option value="<?php echo $row_rsInFolder['ID']?>"<?php if (!(strcmp($row_rsInFolder['ID'], $row_rsCategory['subcatofID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsInFolder['categoryname']?></option>
              <?php
} while ($row_rsInFolder = mysql_fetch_assoc($rsInFolder));
  $rows = mysql_num_rows($rsInFolder);
  if($rows > 0) {
      mysql_data_seek($rsInFolder, 0);
	  $row_rsInFolder = mysql_fetch_assoc($rsInFolder);
  }
?>
            </select></td>
          </tr> <tr>
            <td class="text-nowrap text-right top">Read access:</td>
            <td class="form-inline"><select name="accessID" class="form-control">
             <option value="99" <?php if (!(strcmp(99, $row_rsCategory['accessID']))) {echo "selected=\"selected\"";} ?>>Creator only</option>
                  <option value="0" <?php if (!(strcmp(0, $row_rsCategory['accessID']))) {echo "selected=\"selected\"";} ?>>Everyone</option>
                  
              <?php
do {  
?>
              <option value="<?php echo $row_rsAccessLevels['ID']?>"<?php if (!(strcmp($row_rsAccessLevels['ID'], $row_rsCategory['accessID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsAccessLevels['accesslevel']?></option>
              <?php
} while ($row_rsAccessLevels = mysql_fetch_assoc($rsAccessLevels));
  $rows = mysql_num_rows($rsAccessLevels);
  if($rows > 0) {
      mysql_data_seek($rsAccessLevels, 0);
	  $row_rsAccessLevels = mysql_fetch_assoc($rsAccessLevels);
  }
?>
             
              </select>
            <select name="groupreadID" id="groupreadID" class="form-control group" onchange="if(this.value!=0) alert('Remember, you will need to be in the chosen group if you wish to view or edit this folder in future');">
                    <option value="0" <?php if (!(strcmp(0, $row_rsCategory['groupreadID']))) {echo "selected=\"selected\"";} ?>>Any group</option>
                <?php
do {  
?>
                <option value="<?php echo $row_rsGroups['ID']?>"<?php if (!(strcmp($row_rsGroups['ID'], $row_rsCategory['groupreadID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsGroups['groupname']?></option>
                <?php
} while ($row_rsGroups = mysql_fetch_assoc($rsGroups));
  $rows = mysql_num_rows($rsGroups);
  if($rows > 0) {
      mysql_data_seek($rsGroups, 0);
	  $row_rsGroups = mysql_fetch_assoc($rsGroups);
  }
?>
              </select></td>
          </tr>
          <tr>
            <td class="text-nowrap text-right top">Update access:</td>
            <td class="form-inline"><select name="writeaccess" class="form-control"><option value="99"  <?php if (!(strcmp(99, $row_rsCategory['writeaccess']))) {echo "selected=\"selected\"";} ?>>Just me</option>
                    <option value="100"  <?php if (!(strcmp(100, $row_rsCategory['writeaccess']))) {echo "selected=\"selected\"";} ?>>Nobody</option>
                    <option value="0"  <?php if (!(strcmp(0, $row_rsCategory['writeaccess']))) {echo "selected=\"selected\"";} ?>>Everyone</option>
              <?php
do {  
?>
              <option value="<?php echo $row_rsAccessLevels['ID']?>"<?php if (!(strcmp($row_rsAccessLevels['ID'], $row_rsCategory['writeaccess']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsAccessLevels['accesslevel']?></option>
              <?php
} while ($row_rsAccessLevels = mysql_fetch_assoc($rsAccessLevels));
  $rows = mysql_num_rows($rsAccessLevels);
  if($rows > 0) {
      mysql_data_seek($rsAccessLevels, 0);
	  $row_rsAccessLevels = mysql_fetch_assoc($rsAccessLevels);
  }
?>
           
            </select>
              <select name="groupwriteID" class="group form-control"> <option value="0" <?php if (!(strcmp(0, $row_rsCategory['groupwriteID']))) {echo "selected=\"selected\"";} ?>>Any group</option>
                <?php
do {  
?>
                <option value="<?php echo $row_rsGroups['ID']?>"<?php if (!(strcmp($row_rsGroups['ID'], $row_rsCategory['groupwriteID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsGroups['groupname']?></option>
                <?php
} while ($row_rsGroups = mysql_fetch_assoc($rsGroups));
  $rows = mysql_num_rows($rsGroups);
  if($rows > 0) {
      mysql_data_seek($rsGroups, 0);
	  $row_rsGroups = mysql_fetch_assoc($rsGroups);
  }
?>
              </select></td>
          </tr>
         
          <tr>
            <td class="text-nowrap text-right top">Active:</td>
            <td><input type="checkbox" name="active" value="1"  <?php if ($row_rsCategory['active']==1) {echo "checked=\"checked\"";} ?>></td>
          </tr>
          <tr>
            <td class="text-nowrap text-right top">&nbsp;</td>
            <td><button type="submit" class="btn btn-primary" >Save changes</button></td>
          </tr>
        </table>
        <input type="hidden" name="ID" value="<?php echo $row_rsCategory['ID']; ?>">
        <input type="hidden" name="modifiedbyID" value="<?php echo htmlentities($row_rsLoggedIn['ID']); ?>">
        <input type="hidden" name="modifieddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>">
        <input type="hidden" name="MM_update" value="form1">
        <input type="hidden" name="ID" value="<?php echo $row_rsCategory['ID']; ?>">
      </form>
      <p>&nbsp;</p>
    </div>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsCategory);

mysql_free_result($rsAccessLevels);

mysql_free_result($rsGroups);

mysql_free_result($rsInFolder);
?>
