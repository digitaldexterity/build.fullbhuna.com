<?php require_once('../../../Connections/aquiescedb.php'); ?><?php require_once('../../../core/includes/adminAccess.inc.php'); ?>
<?php require_once('../../includes/sendmail.inc.php'); ?>
<?php

$_GET['groupID'] = isset($_GET['groupID']) ? $_GET['groupID'] : 0;
$_POST['groupID'] = isset($_POST['groupID']) ? $_POST['groupID'] : 0;

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
	$emailID = addGroupEmail($_POST['subject'], "", $_POST['usertypeID'], $_POST['groupID'], $_POST['from'], $_POST['fromname'], $_POST['templateID'], "", "", $_POST['regionID'], $_POST['createdbyID'],  1, $_POST['startdatetime'], $_POST['active'], 1,1);


  $insertGoTo = "update_group_email.php?emailID=".$emailID."";
  if (isset($_SERVER['QUERY_STRING'])) {
    $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
    $insertGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $insertGoTo)); exit;
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) { exit; } // added to stop hanging

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID, usertypeID, regionID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsUserType = "SELECT ID, CONCAT(name,'s') AS usertype FROM usertype WHERE ID >= 0 ORDER BY ID";
$rsUserType = mysql_query($query_rsUserType, $aquiescedb) or die(mysql_error());
$row_rsUserType = mysql_fetch_assoc($rsUserType);
$totalRows_rsUserType = mysql_num_rows($rsUserType);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = "SELECT orgname, contactemail, useregions FROM preferences";
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsRegions = "SELECT ID, title FROM region WHERE statusID = 1 ORDER BY title ASC";
$rsRegions = mysql_query($query_rsRegions, $aquiescedb) or die(mysql_error());
$row_rsRegions = mysql_fetch_assoc($rsRegions);
$totalRows_rsRegions = mysql_num_rows($rsRegions);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsTemplates = "SELECT ID, templatename FROM groupemailtemplate WHERE statusID = statusID ORDER BY templatename ASC";
$rsTemplates = mysql_query($query_rsTemplates, $aquiescedb) or die(mysql_error());
$row_rsTemplates = mysql_fetch_assoc($rsTemplates);
$totalRows_rsTemplates = mysql_num_rows($rsTemplates);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsUserGroups = "SELECT ID, groupname FROM usergroup WHERE statusID = 1 ORDER BY groupname ASC";
$rsUserGroups = mysql_query($query_rsUserGroups, $aquiescedb) or die(mysql_error());
$row_rsUserGroups = mysql_fetch_assoc($rsUserGroups);
$totalRows_rsUserGroups = mysql_num_rows($rsUserGroups);


?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" --><title><?php $pageTitle = "Add Group Email"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title><!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script src="../../../SpryAssets/SpryValidationTextField.js"></script>
<script src="../../../SpryAssets/SpryValidationTextarea.js"></script>

<link href="../../../SpryAssets/SpryValidationTextField.css" rel="stylesheet" type="text/css" />
<link href="../../../SpryAssets/SpryValidationTextarea.css" rel="stylesheet" type="text/css" />
<script>
 function validateForm()
 {
  var errors = "";
   if (document.getElementById('subject').value == "") errors = errors + "Please enter a subject.\n";
	    if (document.getElementById('from').value == "") errors = errors + "Please state who email is from.\n";


if (errors) window.alert(errors); else if (document.getElementById('startdatetime').value <= '<?php echo date('Y-m-d H:i:s'); ?>' && document.getElementById('active').value == 1) errors = !confirm('You have set this email to start sending now.\nDo you wish to continue?');   if (!errors) { document.getElementById('submit').disabled = true; }
   document.returnValue = (!errors);
 }
 </script>
<style type="text/css">
<!--
#imageinput {
	display: none;
}
#newsinput {
	display: none;
}
<?php if($totalRows_rsUserGroups==0) { ?>
.groups { display:none; }
<?php } ?>
<?php if ($row_rsLoggedIn['usertypeID'] <9 || strcmp($row_rsPreferences['useregions'],1) || totalRows_rsRegions == 1) { ?>
.region {display:none; } 
<?php } ?>
-->
</style>
<link href="../../css/mailDefault.css" rel="stylesheet" type="text/css" />
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" --><div class="page mail">
    <?php if ($row_rsLoggedIn['usertypeID'] <9 || strcmp($row_rsPreferences['useregions'],1)) { ?><style>.region {display:none; } </style><?php } ?><h1><i class="glyphicon glyphicon-envelope"></i> Add Group Email   </h1>
    <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
      <li><a href="../templates/index.php" class="link_manage"><i class="glyphicon glyphicon-cog"></i> Manage Templates</a></li>
      <li><a href="index.php" class="link_undo"><i class="glyphicon glyphicon-arrow-left"></i> Group Email</a></li>
      
    </ul></div></nav>
    <?php if(isset($submit_error)) { ?><p class="alert alert-danger" role="alert"><?php echo $submit_error; ?></p><?php } ?>
<form action="<?php echo $editFormAction; ?>" method="post"  name="form1" id="form1" >
  <table class="form-table"> <tr>
      <td class="text-nowrap text-right">Start date:</td>
      <td><input type="hidden" name="startdatetime" id="startdatetime" value="<?php $inputname = "startdatetime"; $time= true; $setvalue = date('Y-m-d H:i:s'); echo $setvalue; ?>"/> 
        <?php  include("../../../core/includes/datetimeinput.inc.php"); ?>
        <input name="active" type="hidden" id="active" value="0" /></td>
      </tr>
    <tr class="region">
      <td class="text-nowrap text-right">Site:</td>
      <td><select name="regionID"  id="regionID" class="form-control">
        <option value="0" <?php if (!(strcmp(0, $row_rsLoggedIn['regionID']))) {echo "selected=\"selected\"";} ?>>All sites</option>
        <?php if($totalRows_rsRegions>0) {
do {  
?><option value="<?php echo $row_rsRegions['ID']?>"<?php if (!(strcmp($row_rsRegions['ID'], $row_rsLoggedIn['regionID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsRegions['title']?></option>
        <?php
} while ($row_rsRegions = mysql_fetch_assoc($rsRegions));
  $rows = mysql_num_rows($rsRegions);
  if($rows > 0) {
      mysql_data_seek($rsRegions, 0);
	  $row_rsRegions = mysql_fetch_assoc($rsRegions);
  }}
?>
        </select></td>
      </tr> <tr>
      <td class="text-nowrap text-right form-inline">To:</strong></td>
      <td><span class = "groups"><select name="groupID" id="groupID"  class="form-control">
        <option value="0" <?php if (!(strcmp(0, $_POST['groupID']))) {echo "selected=\"selected\"";} ?>>Everyone</option>
        <?php
do {  
?>
        <option value="<?php echo $row_rsUserGroups['ID']?>"<?php if (!(strcmp($row_rsUserGroups['ID'], $_POST['groupID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsUserGroups['groupname']?></option>
        <?php
} while ($row_rsUserGroups = mysql_fetch_assoc($rsUserGroups));
  $rows = mysql_num_rows($rsUserGroups);
  if($rows > 0) {
      mysql_data_seek($rsUserGroups, 0);
	  $row_rsUserGroups = mysql_fetch_assoc($rsUserGroups);
  }
?>
      </select> 
        who are</span>
        <select name="usertypeID"  id="usertypeID"  class="form-control">
          <option value="-1" <?php if(!isset($_REQUEST['usertypeID'])) { echo "selected=\"selected\""; } ?>>Any rank</option>
          <?php
do {  
?>
          <option value="<?php echo $row_rsUserType['ID']?>"<?php if (!(strcmp($row_rsUserType['ID'], @$_REQUEST['usertypeID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsUserType['usertype']?> or higher rank</option>
          <?php
} while ($row_rsUserType = mysql_fetch_assoc($rsUserType));
  $rows = mysql_num_rows($rsUserType);
  if($rows > 0) {
      mysql_data_seek($rsUserType, 0);
	  $row_rsUserType = mysql_fetch_assoc($rsUserType);
  }
?>
        </select></td>
      </tr> <tr>
      <td class="text-nowrap text-right">Sender name:</td>
      <td><span id="sprytextfield1">
        <input name="fromname"  id="fromname" type="text"  value="<?php echo $row_rsPreferences['orgname']; ?>" size="50" maxlength="100"  class="form-control"/>
        <span class="textfieldRequiredMsg">A name is required.</span></span></td>
    </tr> <tr>
      <td class="text-nowrap text-right">Sender email:</td>
      <td><span id="sprytextfield2">
      <input name="from"  id="from" type="email"  value="<?php echo isset($row_rsPreferences['contactemail']) ? singleEmail($row_rsPreferences['contactemail']) : ""; ?>" size="50" maxlength="100"  class="form-control"/>
      <span class="textfieldRequiredMsg">An email address is required.</span><span class="textfieldInvalidFormatMsg">Invalid format.</span></span></td>
      </tr> <tr>
      <td class="text-nowrap text-right">Subject:</td>
      <td><span id="sprytextfield3">
        <input name="subject" id="subject" type="text"  value="<?php echo isset($_POST['subject']) ? $_POST['subject'] : ""; ?>" size="50" maxlength="100" class="form-control" />
<span class="textfieldRequiredMsg">A subject is required.</span></span></td>
      </tr> <tr>
      <td class="text-nowrap text-right">Template:</td>
      <td><select name="templateID" id="templateID"  onchange="if(this.value&gt;=1) {document.getElementById('messagerow').style.display = 'none';} else {document.getElementById('messagerow').style.display = '';}"  class="form-control">
        <option value="0" <?php if (!(strcmp(0, 1))) {echo "selected=\"selected\"";} ?>>Plain Text</option>
        
        <?php if($totalRows_rsTemplates>0) {
do {  
?>
        <option value="<?php echo $row_rsTemplates['ID']; ?>">Graphic Template: <?php echo $row_rsTemplates['templatename']?></option>
        <?php
} while ($row_rsTemplates = mysql_fetch_assoc($rsTemplates));
  $rows = mysql_num_rows($rsTemplates);
  if($rows > 0) {
      mysql_data_seek($rsTemplates, 0);
	  $row_rsTemplates = mysql_fetch_assoc($rsTemplates);
  } }
		  ?>
      
       
        </select></td>
      </tr> <tr>
      <td>&nbsp;</td>
      <td class="top"><button type="submit" class="btn btn-primary" >Create Group Email</button>
        <input type="hidden" name="MM_insert" value="form1" />
        <input type="hidden" name="createddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
        <input type="hidden" name="createdbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" /></td>
    </tr>
    </table>
  
  
</form>
      <p>
<script>
<!--
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1");
var sprytextfield2 = new Spry.Widget.ValidationTextField("sprytextfield2", "email");
var sprytextfield3 = new Spry.Widget.ValidationTextField("sprytextfield3");
//-->
      </script>
      *Note: emails will only be sent to  those who have opted in to receive emails </p></div>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsUserType);

mysql_free_result($rsPreferences);

mysql_free_result($rsRegions);

mysql_free_result($rsTemplates);

mysql_free_result($rsUserGroups);


?>
