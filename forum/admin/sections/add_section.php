<?php require_once('../../../Connections/aquiescedb.php'); ?><?php require_once('../../../core/includes/adminAccess.inc.php'); ?><?php require_once('../../../core/includes/upload.inc.php'); ?>
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
if (isset($uploaded) && is_array($uploaded)) {
	if(isset($uploaded["filename"][0]["newname"]) && $uploaded["filename"][0]["newname"]!="") { 
		$_POST['imageURL'] = $uploaded["filename"][0]["newname"]; 
	}
	$_POST['imageURL'] = (isset($_POST["noimage"])) ? "" : $_POST['imageURL'];
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
  $insertSQL = sprintf("INSERT INTO forumsection (sectionname, sectiondescription, imageURL, accesslevel, regionID, statusID, createdbyID, createddatetime, moderatorID, rankwrite, groupread, groupwrite) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['sectionname'], "text"),
                       GetSQLValueString($_POST['sectiondescription'], "text"),
                       GetSQLValueString($_POST['imageURL'], "text"),
                       GetSQLValueString($_POST['accesslevel'], "int"),
                       GetSQLValueString($_POST['regionID'], "int"),
                       GetSQLValueString(isset($_POST['statusID']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['createdbyID'], "int"),
                       GetSQLValueString($_POST['createddatetime'], "date"),
                       GetSQLValueString($_POST['moderatorID'], "int"),
                       GetSQLValueString($_POST['rankwrite'], "int"),
                       GetSQLValueString($_POST['groupread'], "int"),
                       GetSQLValueString($_POST['groupwrite'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($insertSQL, $aquiescedb) or die(mysql_error());

  $insertGoTo = "index.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
    $insertGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $insertGoTo));
}

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
$query_rsUserTypes = "SELECT * FROM usertype WHERE ID > 0 ORDER BY ID ASC";
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

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsGroups = "SELECT ID, groupname FROM usergroup ORDER BY groupname ASC";
$rsGroups = mysql_query($query_rsGroups, $aquiescedb) or die(mysql_error());
$row_rsGroups = mysql_fetch_assoc($rsGroups);
$totalRows_rsGroups = mysql_num_rows($rsGroups);
?><!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" --><title><?php $pageTitle = "Add Forum Section"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title><!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" --><?php if($row_rsPreferences['useregions'] !=1) { ?>
<style>
.region {
display:none;
} </style><?php } ?><!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" --><div class="page forum">
   <h1><i class="glyphicon glyphicon-comment"></i> Add Forum Section </h1>
   <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
     <li><a href="index.php" class="link_undo"><i class="glyphicon glyphicon-arrow-left"></i> Sections</a></li>
     <li><a href="../index.php" class="link_undo"><i class="glyphicon glyphicon-arrow-left"></i> Topics</a></li>
   </ul></div></nav>
   <form action="<?php echo $editFormAction; ?>" method="post" enctype="multipart/form-data" name="form1" id="form1">
     <table class="form-table"> <tr>
         <td class="text-nowrap text-right">Name:</td>
         <td><input type="text"  name="sectionname" class="form-control"  /></td>
       </tr> <tr>
         <td class="text-nowrap text-right top">Description:</td>
         <td><textarea name="sectiondescription" cols="50" rows="5" class="form-control"></textarea>
         </td>
       </tr>
       <tr class="upload">
          <td class="text-nowrap text-right"> Image:</td>
          <td><input name="filename" type="file" id="filename" size="20" />
            <input type="hidden" name="imageURL" id="imageURL" />            </td>
        </tr> <tr>
         <td class="text-nowrap text-right">Can view:</td>
         <td class="form-inline"><select name="accesslevel" class="form-control">
           <option value="0" >Everyone</option>
           <?php
do {  
?>
           <option value="<?php echo $row_rsUserTypes['ID']?>"><?php echo $row_rsUserTypes['name']?>s</option>
           <?php
} while ($row_rsUserTypes = mysql_fetch_assoc($rsUserTypes));
  $rows = mysql_num_rows($rsUserTypes);
  if($rows > 0) {
      mysql_data_seek($rsUserTypes, 0);
	  $row_rsUserTypes = mysql_fetch_assoc($rsUserTypes);
  }
?>
                    </select> 
           who are in group:<select name="groupread" id="groupread" class="group form-control">
            <option value="0" >Any group</option>
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
         <td class="text-nowrap text-right">Can post</td>
         <td class="form-inline"><select name="rankwrite" class="form-control">
           <option value="0" >Everyone</option>
           <?php
do {  
?>
           <option value="<?php echo $row_rsUserTypes['ID']?>"><?php echo $row_rsUserTypes['name']?>s</option>
           <?php
} while ($row_rsUserTypes = mysql_fetch_assoc($rsUserTypes));
  $rows = mysql_num_rows($rsUserTypes);
  if($rows > 0) {
      mysql_data_seek($rsUserTypes, 0);
	  $row_rsUserTypes = mysql_fetch_assoc($rsUserTypes);
  }
?>
         </select> 
           who are in group:<select name="groupwrite" id="groupwrite" class="group form-control">
            <option value="0" >Any group</option>
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
       </tr>
       <tr class="region">
         <td class="text-nowrap text-right">Site:</td>
         <td><select name="regionID" class="form-control">
           <option value="1"><?php echo isset($region['text_choose']) ? htmlentities($region['text_choose'], ENT_COMPAT, "UTF-8") : "Choose..." ?></option>
           <?php
do {  
?>
           <option value="<?php echo $row_rsRegion['ID']?>"><?php echo $row_rsRegion['title']?></option>
           <?php
} while ($row_rsRegion = mysql_fetch_assoc($rsRegion));
  $rows = mysql_num_rows($rsRegion);
  if($rows > 0) {
      mysql_data_seek($rsRegion, 0);
	  $row_rsRegion = mysql_fetch_assoc($rsRegion);
  }
?>
                    </select>
         </td>
       </tr>
       <tr> </tr> <tr>
         <td class="text-nowrap text-right">Moderator:</td>
         <td><select name="moderatorID" class="form-control">
           <option value="0">Default</option>
           <?php
do {  
?>
           <option value="<?php echo $row_rsAdministrators['ID']?>"><?php echo $row_rsAdministrators['firstname']." ".$row_rsAdministrators['surname']; ?></option>
<?php
} while ($row_rsAdministrators = mysql_fetch_assoc($rsAdministrators));
  $rows = mysql_num_rows($rsAdministrators);
  if($rows > 0) {
      mysql_data_seek($rsAdministrators, 0);
	  $row_rsAdministrators = mysql_fetch_assoc($rsAdministrators);
  }
?>
                    </select>
         </td>
       </tr><tr>
         <td class="text-nowrap text-right">Active:</td>
         <td><input type="checkbox" name="statusID" value="" checked="checked" /></td>
       </tr>
       <tr> </tr> <tr>
         <td class="text-nowrap text-right">&nbsp;</td>
         <td><button type="submit" class="btn btn-primary" >Add Section</button></td>
       </tr>
     </table>
     <input type="hidden" name="createdbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
     <input type="hidden" name="createddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
     <input type="hidden" name="MM_insert" value="form1" />
   </form>
 </div>
   <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsAdministrators);

mysql_free_result($rsRegion);

mysql_free_result($rsUserTypes);

mysql_free_result($rsPreferences);

mysql_free_result($rsLoggedIn);

mysql_free_result($rsGroups);
?>


