<?php require_once('../../Connections/aquiescedb.php'); ?>
<?php 
set_time_limit(600); // 10 mins
ini_set("session.gc_maxlifetime","10800");
ini_set("max_execution_time","600"); // 10 mins
ini_set("max_input_time","600"); // 10 mins

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
?><?php

if(isset($_GET['removeDuplicates'])) { 
		header("location: remove_duplicates.php?returnURL=".$_SERVER['PHP_SELF']); exit;
} // end remove duplicates

if(isset($_POST['merge'])) {
	require_once('includes/mergeUsers.inc.php');
	$submit_error = mergeUsers($_POST['merge'], @$_POST['keep']);
} // end post


?><?php
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

$maxRows_rsUsers = (strlen(@$_REQUEST['search']) > 3) ? 1000 : 50;
$pageNum_rsUsers = 0;
if (isset($_GET['pageNum_rsUsers'])) {
  $pageNum_rsUsers = $_GET['pageNum_rsUsers'];
}
$startRow_rsUsers = $pageNum_rsUsers * $maxRows_rsUsers;

$varSearch_rsUsers = "-1";
if (isset($_REQUEST['search'])) {
  $varSearch_rsUsers = $_REQUEST['search'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsUsers = sprintf("SELECT users.firstname, users.surname, users.ID, users.usertypeID, users.email, users.username, usertype.name, region.title FROM users LEFT JOIN usertype ON (users.usertypeID = usertype.ID) LEFT JOIN region ON (users.regionID = region.ID) WHERE users.surname LIKE %s OR users.email LIKE %s  ORDER BY surname, firstname", GetSQLValueString($varSearch_rsUsers . "%", "text"),GetSQLValueString($varSearch_rsUsers . "%", "text"));
$query_limit_rsUsers = sprintf("%s LIMIT %d, %d", $query_rsUsers, $startRow_rsUsers, $maxRows_rsUsers);
$rsUsers = mysql_query($query_limit_rsUsers, $aquiescedb) or die(mysql_error());
$row_rsUsers = mysql_fetch_assoc($rsUsers);

if (isset($_GET['totalRows_rsUsers'])) {
  $totalRows_rsUsers = $_GET['totalRows_rsUsers'];
} else {
  $all_rsUsers = mysql_query($query_rsUsers);
  $totalRows_rsUsers = mysql_num_rows($all_rsUsers);
}
$totalPages_rsUsers = ceil($totalRows_rsUsers/$maxRows_rsUsers)-1;
 
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Merge Users"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script src="/core/scripts/formUpload.js"></script>
<script src="/SpryAssets/SpryValidationTextField.js"></script>
<link href="/SpryAssets/SpryValidationTextField.css" rel="stylesheet"  />
<script>
var fb_keepAlive = true;
</script>
<link href="../css/membersDefault.css" rel="stylesheet"  />
<style><!--

.listTable {
	width:100%;
}


td.max-width
{
	
    max-width: 40%;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
--></style>
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" -->
        <div class="page users">
    <h1><i class="glyphicon glyphicon-user"></i> Merge Users</h1>
    <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
      <li><a href="index.php" class="link_undo"><i class="glyphicon glyphicon-arrow-left"></i> Manage users</a></li>
      
    </ul></div></nav>
<p>You can multiple merge users into one, for example if you have more than one system user belonging to the same person.<br />
  <strong>IMPORTANT: It is recommended that you <a href="/core/admin/backup/index.php">back up</a> your data beforehand.</strong></p>
<h2> Remove duplicates</h2>
    <p>You can automatically merge users on the system with the same primary email address. Click on the button below. Processing may take a few minutes depending on number of users.</p>
    <form action="merge_users.php" method="get" name="form3" id="form3"   ><input name="removeDuplicates" type="hidden" value="true" />
      <button type="submit" name="mergeduplicatesbutton" id="mergeduplicatesbutton"onClick="return confirm('Are you sure you want to remove duplicates?\n\nAll users with the same email address will be merged with the highest rank member being kept.\n\nNOTE: This could take some time to process depending on number of users. It is recommended you back up your data first.');" >Merge Duplicates...</button>
    </form>
    <h2>Manual Merge</h2>
   <form action="merge_users.php" method="get" name="searchform" id="searchform">
     <span id="sprytextfield1">
     <input name="search" type="text"  id="search" size="30" maxlength="30" value="<?php echo isset($_GET['search']) ? htmlentities($_GET['search']) : "";?>" />
</span>
     <span><button type="submit" >Go</button></span>
   </form> 
   <?php if(isset($submit_error)) { ?><p class="alert alert-danger" role="alert"><?php echo $submit_error; ?></p><?php } ?>
   <?php if(isset($_GET['msg'])) { ?><p class="alert alert-danger" role="alert"><?php echo htmlentities($_GET['msg']); ?></p><?php } ?>
 
    <form action="merge_users.php" method="post" name="form1" id="form1"  <?php if ($totalRows_rsUsers == 0) { echo "style=\"display:none;\""; } ?> >
     <p>Select the users you wish to merge using the checkboxes left and the user you wish you keep (i.e. merge to) using the radio buttons right.</p>
  <p>Matching users <?php echo ($startRow_rsUsers + 1) ?> to <?php echo min($startRow_rsUsers + $maxRows_rsUsers, $totalRows_rsUsers) ?> of <?php echo $totalRows_rsUsers ?> </p>
        
        <table class="listTable">
          <tr>
            <th>Merge</th>
            <th>User</th>
            <th>email</th>
            <th>Username</th>
            <th>Rank</th>
            <th>Site</th> 
            <th>Keep</th>
          </tr>
          <?php do { ?>
            <tr>
              <td><input type="checkbox" name="merge[<?php echo $row_rsUsers['ID']; ?>]" id="merge[<?php echo $row_rsUsers['ID']; ?>]" <?php if(isset($_POST['merge'][$row_rsUsers['ID']])) { echo "checked = \"checked\""; } ?> /></td>
               <td><a href="modify_user.php?userID=<?php echo $row_rsUsers['ID']; ?>"> <?php echo $row_rsUsers['ID']; ?> <?php echo $row_rsUsers['firstname']; ?> <?php echo $row_rsUsers['surname']; ?></a></td>
              <td class="max-width"><?php echo $row_rsUsers['email']; ?></td>
              <td class="max-width"><?php echo $row_rsUsers['username']; ?></td>
              <td><?php echo $row_rsUsers['name']; ?></td>
              <td><?php echo $row_rsUsers['title']; ?></td><td>
                <input type="hidden" name="username[<?php echo $row_rsUsers['ID']; ?>]" id="username[<?php echo $row_rsUsers['ID']; ?>]" value="<?php echo $row_rsUsers['username']; ?>" />
                <input type="radio" name="keep" id="keep[<?php echo $row_rsUsers['ID']; ?>]" value="<?php echo $row_rsUsers['ID']; ?>" <?php if(@$_POST['keep'] == $row_rsUsers['ID']) { echo "checked = \"checked\""; } ?> /></td>
            </tr>
            <?php } while ($row_rsUsers = mysql_fetch_assoc($rsUsers)); ?>
        </table><div><button type="submit" name="submit2" id="submit2"onClick="return confirm('Are you sure you want to merge these users?\n\nThis action cannot be undone.');" >Merge users...</button><div id="uploading2" style="visibility:hidden;"><a href="javascript:void(0);" onClick="stopSubmit(); return false;">Processing. Please wait...</a></div></div>
        
      <input name="search" type="hidden" id="search" value="<?php echo htmlentities($_REQUEST['search']); ?>" />
    </form>
    <script>
<!--
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1", "none", {hint:"Search by surname or email...", isRequired:false});
//-->
    </script></div>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsUsers);
?>
