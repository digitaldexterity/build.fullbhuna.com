<?php require_once('../../../Connections/aquiescedb.php'); ?><?php require_once('../../../core/includes/adminAccess.inc.php'); ?>
<?php require_once('../../../core/includes/framework.inc.php'); ?>
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

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE forumsection SET sectionname=%s, sectiondescription=%s, imageURL=%s, accesslevel=%s, regionID=%s, statusID=%s, modifiedbyID=%s, modifieddatetime=%s, moderatorID=%s, rankwrite=%s, groupread=%s, groupwrite=%s WHERE ID=%s",
                       GetSQLValueString($_POST['sectionname'], "text"),
                       GetSQLValueString($_POST['sectiondescription'], "text"),
                       GetSQLValueString($_POST['imageURL'], "text"),
                       GetSQLValueString($_POST['accesslevel'], "int"),
                       GetSQLValueString($_POST['regionID'], "int"),
                       GetSQLValueString(isset($_POST['statusID']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['modifiedbyID'], "int"),
                       GetSQLValueString($_POST['modifieddatetime'], "date"),
                       GetSQLValueString($_POST['moderatorID'], "int"),
                       GetSQLValueString($_POST['rankwrite'], "int"),
                       GetSQLValueString($_POST['groupread'], "int"),
                       GetSQLValueString($_POST['groupwrite'], "int"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());

  $updateGoTo = "/forum/admin/sections/index.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo));
}

include('../../../core/includes/upload.inc.php');

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsAdministrators = "SELECT ID, firstname, surname FROM users WHERE usertypeID >= 7";
$rsAdministrators = mysql_query($query_rsAdministrators, $aquiescedb) or die(mysql_error());
$row_rsAdministrators = mysql_fetch_assoc($rsAdministrators);
$totalRows_rsAdministrators = mysql_num_rows($rsAdministrators);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsRegion = "SELECT ID, title FROM region WHERE statusID = 1 ORDER BY title ASC";
$rsRegion = mysql_query($query_rsRegion, $aquiescedb) or die(mysql_error());
$row_rsRegion = mysql_fetch_assoc($rsRegion);
$totalRows_rsRegion = mysql_num_rows($rsRegion);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsUserTypes = "SELECT * FROM usertype WHERE ID > 1 ORDER BY ID ASC";
$rsUserTypes = mysql_query($query_rsUserTypes, $aquiescedb) or die(mysql_error());
$row_rsUserTypes = mysql_fetch_assoc($rsUserTypes);
$totalRows_rsUserTypes = mysql_num_rows($rsUserTypes);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = "SELECT useregions FROM preferences";
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

$colname_rsForumSection = "-1";
if (isset($_GET['sectionID'])) {
  $colname_rsForumSection = $_GET['sectionID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsForumSection = sprintf("SELECT * FROM forumsection WHERE ID = %s", GetSQLValueString($colname_rsForumSection, "int"));
$rsForumSection = mysql_query($query_rsForumSection, $aquiescedb) or die(mysql_error());
$row_rsForumSection = mysql_fetch_assoc($rsForumSection);
$totalRows_rsForumSection = mysql_num_rows($rsForumSection);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsGroups = "SELECT ID, groupname FROM usergroup ORDER BY groupname ASC";
$rsGroups = mysql_query($query_rsGroups, $aquiescedb) or die(mysql_error());
$row_rsGroups = mysql_fetch_assoc($rsGroups);
$totalRows_rsGroups = mysql_num_rows($rsGroups);
?><?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" --><title><?php echo $site_name; ?> <?php echo $admin_name; ?> - Update Forum Section</title><!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" --><?php if($row_rsPreferences['useregions'] !=1) { ?>
<style>
.region {
display:none;
} </style><?php } ?><!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../../../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" -->
   <h1><i class="glyphicon glyphicon-comment"></i> <img src="/core/images/icons-large/internet-group-chat.png" alt="Forum" width="32" height="32" align="absmiddle" /> Update Forum Section </h1>
   <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
     <li class="nav-item"><a href="/forum/admin/sections/index.php" class="nav-link"><i class="glyphicon glyphicon-arrow-left"></i> Sections</a></li>
     <li class="nav-item"><a href="/forum/admin/index.php" class="nav-link"><i class="glyphicon glyphicon-arrow-left"></i> Topics</a></li>
   </ul></div></nav>
   <form action="<?php echo $editFormAction; ?>" method="POST" enctype="multipart/form-data" name="form1" id="form1">
     <table class="form-table"> <tr>
         <td class="text-nowrap text-right">Name:</td>
         <td><input type="text"  name="sectionname" value="<?php echo $row_rsForumSection['sectionname']; ?>"  class="form-control" /></td>
       </tr> <tr>
         <td class="text-nowrap text-right top">Description:</td>
         <td><textarea name="sectiondescription" cols="50" rows="5"  class="form-control"><?php echo $row_rsForumSection['sectiondescription']; ?></textarea>         </td>
       </tr> <tr>
         <td class="text-nowrap text-right"> Image:</td>
         <td>
           <input name="filename" type="file" id="filename" size="20" />
           <input name="imageURL" type="hidden" id="imageURL" value="<?php echo $row_rsForumSection['imageURL']; ?>" />
           <?php if(isset($row_rsForumSection['imageURL']) && $row_rsForumSection['imageURL']!="") { ?><br />Current image:<br /><img src="<?php echo getImageURL($row_rsForumSection['imageURL'],"thumb"); ?>" alt="Current image" /><?php } ?></td>
       </tr> <tr>
         <td class="text-nowrap text-right">Can view:</td>
         <td class="form-inline"><select name="accesslevel" class="form-control">
           <option value="0"  <?php if (!(strcmp(0, $row_rsForumSection['accesslevel']))) {echo "selected=\"selected\"";} ?>>Everyone</option>
           <?php
do {  
?>
           <option value="<?php echo $row_rsUserTypes['ID']?>"<?php if (!(strcmp($row_rsUserTypes['ID'], $row_rsForumSection['accesslevel']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsUserTypes['name']?></option>
           <?php
} while ($row_rsUserTypes = mysql_fetch_assoc($rsUserTypes));
  $rows = mysql_num_rows($rsUserTypes);
  if($rows > 0) {
      mysql_data_seek($rsUserTypes, 0);
	  $row_rsUserTypes = mysql_fetch_assoc($rsUserTypes);
  }
?>
         </select>
           who are in group:
           <select name="groupread" id="groupread" class="group form-control">
             <option value="0"  <?php if (!(strcmp(0, $row_rsForumSection['groupread']))) {echo "selected=\"selected\"";} ?>>Any group</option>
             <?php
do {  
?>
             <option value="<?php echo $row_rsGroups['ID']?>"<?php if (!(strcmp($row_rsGroups['ID'], $row_rsForumSection['groupread']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsGroups['groupname']?></option>
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
         <td class="form-inline"><select name="rankwrite" class="form-control">
           <option value="0"  <?php if (!(strcmp(0, $row_rsForumSection['rankwrite']))) {echo "selected=\"selected\"";} ?>>Everyone</option>
           <?php
do {  
?>
           <option value="<?php echo $row_rsUserTypes['ID']?>"<?php if (!(strcmp($row_rsUserTypes['ID'], $row_rsForumSection['rankwrite']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsUserTypes['name']?></option>
           <?php
} while ($row_rsUserTypes = mysql_fetch_assoc($rsUserTypes));
  $rows = mysql_num_rows($rsUserTypes);
  if($rows > 0) {
      mysql_data_seek($rsUserTypes, 0);
	  $row_rsUserTypes = mysql_fetch_assoc($rsUserTypes);
  }
?>
         </select>
           who are in group:
           <select name="groupwrite" id="groupwrite" class="group form-control">
             <option value="0"  <?php if (!(strcmp(0, $row_rsForumSection['groupwrite']))) {echo "selected=\"selected\"";} ?>>Any group</option>
             <?php
do {  
?>
             <option value="<?php echo $row_rsGroups['ID']?>"<?php if (!(strcmp($row_rsGroups['ID'], $row_rsForumSection['groupwrite']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsGroups['groupname']?></option>
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
       <tr class="region">
         <td class="text-nowrap text-right">Region:</td>
         <td><select name="regionID" class="form-control">
           <option value="1" <?php if (!(strcmp(1, $row_rsForumSection['regionID']))) {echo "selected=\"selected\"";} ?>><?php echo isset($region['text_choose']) ? htmlentities($region['text_choose'], ENT_COMPAT, "UTF-8") : "Choose..." ?></option>
           <?php
do {  
?><option value="<?php echo $row_rsRegion['ID']?>"<?php if (!(strcmp($row_rsRegion['ID'], $row_rsForumSection['regionID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsRegion['title']?></option>
           <?php
} while ($row_rsRegion = mysql_fetch_assoc($rsRegion));
  $rows = mysql_num_rows($rsRegion);
  if($rows > 0) {
      mysql_data_seek($rsRegion, 0);
	  $row_rsRegion = mysql_fetch_assoc($rsRegion);
  }
?>
           </select>         </td>
       </tr>
       <tr> </tr> <tr>
         <td class="text-nowrap text-right">Moderator:</td>
         <td><select name="moderatorID" class="form-control">
           <option value="0" <?php if (!(strcmp($row_rsAdministrators['ID'], $row_rsForumSection['moderatorID']))) {echo "selected=\"selected\"";} ?>>Default</option>
           <?php
do {  
?>
           <option value="<?php echo $row_rsAdministrators['ID']?>" <?php if (!(strcmp($row_rsAdministrators['ID'], $row_rsForumSection['moderatorID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsAdministrators['firstname']." ".$row_rsAdministrators['surname']; ?></option>
<?php
} while ($row_rsAdministrators = mysql_fetch_assoc($rsAdministrators));
  $rows = mysql_num_rows($rsAdministrators);
  if($rows > 0) {
      mysql_data_seek($rsAdministrators, 0);
	  $row_rsAdministrators = mysql_fetch_assoc($rsAdministrators);
  }
?>
                    </select>         </td>
       </tr><tr>
         <td class="text-nowrap text-right">Active:</td>
         <td><input <?php if (!(strcmp($row_rsForumSection['statusID'],1))) {echo "checked=\"checked\"";} ?> type="checkbox" name="statusID" value="" checked="checked" /></td>
       </tr>
       <tr> </tr> <tr>
         <td class="text-nowrap text-right">&nbsp;</td>
         <td><button type="submit" class="btn btn-primary" >Save changes</button></td>
       </tr>
     </table>
     <input type="hidden" name="modifiedbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
     <input type="hidden" name="modifieddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
      <input name="ID" type="hidden" id="ID" value="<?php echo $row_rsForumSection['ID']; ?>" />
      <input type="hidden" name="MM_update" value="form1" />
      </form>
   <p>&nbsp;</p>
   <!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../../../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsAdministrators);

mysql_free_result($rsRegion);

mysql_free_result($rsUserTypes);

mysql_free_result($rsPreferences);

mysql_free_result($rsLoggedIn);

mysql_free_result($rsForumSection);

mysql_free_result($rsGroups);
?>


