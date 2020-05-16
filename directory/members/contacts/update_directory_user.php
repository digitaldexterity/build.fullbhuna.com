<?php require_once('../../../Connections/aquiescedb.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "2,3,4,5,6,7,8,9,10";
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
    if (($strUsers == "") && true) { 
      $isValid = true; 
    } 
  } 
  return $isValid; 
}

$MM_restrictGoTo = "../../login/index.php?notloggedin=true";
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
  $updateSQL = sprintf("UPDATE users SET firstname=%s, surname=%s, email=%s, jobtitle=%s, modifieddatetime=%s, modifiedbyID=%s, telephone=%s WHERE ID=%s",
                       GetSQLValueString($_POST['firstname'], "text"),
                       GetSQLValueString($_POST['surname'], "text"),
                       GetSQLValueString($_POST['email'], "text"),
                       GetSQLValueString($_POST['jobtitle'], "text"),
                       GetSQLValueString($_POST['modifieddatetime'], "date"),
                       GetSQLValueString($_POST['modifiedbyID'], "int"),
                       GetSQLValueString($_POST['telephone'], "text"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
	
	if(isset($_POST['maincontact']) && isset($_POST['token'])) {
	if($_POST['token']==md5(PRIVATE_KEY.$_POST['ID'])) { // security
		$update = "UPDATE directory SET userID = ".GetSQLValueString($_POST['ID'], "int")." WHERE ID = ".GetSQLValueString($_POST['directoryID'], "int");
		mysql_query($update, $aquiescedb) or die(mysql_error());
													}
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
$query_rsLoggedIn = sprintf("SELECT ID, usertypeID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

$varDirectoryID_rsDirectory = "-1";
if (isset($_GET['directoryID'])) {
  $varDirectoryID_rsDirectory = $_GET['directoryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsDirectory = sprintf("SELECT directory.ID, directory.userID, directory.name FROM directory WHERE directory.ID = %s", GetSQLValueString($varDirectoryID_rsDirectory, "int"));
$rsDirectory = mysql_query($query_rsDirectory, $aquiescedb) or die(mysql_error());
$row_rsDirectory = mysql_fetch_assoc($rsDirectory);
$totalRows_rsDirectory = mysql_num_rows($rsDirectory);

$varDirectoryID_rsDirectoryUser = "-1";
if (isset($_GET['directoryID'])) {
  $varDirectoryID_rsDirectoryUser = $_GET['directoryID'];
}
$varUsername_rsDirectoryUser = "-1";
if (isset($_SESSION['MM_Username'])) {
  $varUsername_rsDirectoryUser = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsDirectoryUser = sprintf("SELECT directoryuser.ID FROM directoryuser LEFT JOIN users ON (directoryuser.userID = users.ID) WHERE users.username = %s AND directoryuser.directoryID = %s", GetSQLValueString($varUsername_rsDirectoryUser, "text"),GetSQLValueString($varDirectoryID_rsDirectoryUser, "int"));
$rsDirectoryUser = mysql_query($query_rsDirectoryUser, $aquiescedb) or die(mysql_error());
$row_rsDirectoryUser = mysql_fetch_assoc($rsDirectoryUser);
$totalRows_rsDirectoryUser = mysql_num_rows($rsDirectoryUser);

$varUsername_rsIsAuthorised = "-1";
if (isset($_SESSION['MM_Username'])) {
  $varUsername_rsIsAuthorised = $_SESSION['MM_Username'];
}
$varDirectoryID_rsIsAuthorised = "-1";
if (isset($_GET['directoryID'])) {
  $varDirectoryID_rsIsAuthorised = $_GET['directoryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsIsAuthorised = sprintf("SELECT DISTINCT(directory.ID), name FROM directory LEFT JOIN users AS creator ON (directory.createdbyID = creator.ID) LEFT JOIN directoryuser ON (directory.ID = directoryuser.directoryID) LEFT JOIN users ON (directoryuser.userID = users.ID) WHERE (creator.username = %s OR users.username = %s) AND directory.ID= %s", GetSQLValueString($varUsername_rsIsAuthorised, "text"),GetSQLValueString($varUsername_rsIsAuthorised, "text"),GetSQLValueString($varDirectoryID_rsIsAuthorised, "int"));
$rsIsAuthorised = mysql_query($query_rsIsAuthorised, $aquiescedb) or die(mysql_error());
$row_rsIsAuthorised = mysql_fetch_assoc($rsIsAuthorised);
$totalRows_rsIsAuthorised = mysql_num_rows($rsIsAuthorised);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = "SELECT * FROM preferences";
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);

$colname_rsContact = "-1";
if (isset($_GET['directoryID'])) {
  $colname_rsContact = $_GET['directoryID'];
}
$varUserID_rsContact = "-1";
if (isset($_GET['userID'])) {
  $varUserID_rsContact = $_GET['userID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsContact = sprintf("SELECT userID, users.firstname, users.surname, users.jobtitle, users.addedbyID, users.email, users.telephone, usertypeID FROM directoryuser LEFT JOIN users ON (directoryuser.userID = users.ID) WHERE directoryID = %s AND userID = %s ", GetSQLValueString($colname_rsContact, "int"),GetSQLValueString($varUserID_rsContact, "int"));
$rsContact = mysql_query($query_rsContact, $aquiescedb) or die(mysql_error());
$row_rsContact = mysql_fetch_assoc($rsContact);
$totalRows_rsContact = mysql_num_rows($rsContact);
?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php echo $site_name; $pageTitle = "Update Contact"; echo " - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script src="../../../SpryAssets/SpryValidationTextField.js"></script>
<link href="../../../SpryAssets/SpryValidationTextField.css" rel="stylesheet"  />
<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../../../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" -->
    <h1 class="directoryheader">Update Contact</h1>
    <?php if ((($row_rsLoggedIn['usertypeID']>$row_rsContact['usertypeID'] && $row_rsLoggedIn['usertypeID'] == $row_rsContact['addedbyID']) && $totalRows_rsIsAuthorised >0) || $row_rsLoggedIn['usertypeID'] >=8) { //authorsied to access ?>
    <form action="<?php echo $editFormAction; ?>" method="POST" name="form1" id="form1">
      <table border="0" cellpadding="2" cellspacing="2" class="form-table">
        <tr>
          <td align="right"><label for="firstname">First name:</label></td>
          <td><span id="sprytextfield1">
            <input name="firstname" type="text" id="firstname" value="<?php echo isset($_REQUEST['firstname']) ? htmlentities($_REQUEST['firstname'], ENT_COMPAT, "UTF-8") : $row_rsContact['firstname']; ?>" size="50" maxlength="50" class="form-control"/>
          <span class="textfieldRequiredMsg">A value is required.</span></span></td>
        </tr>
        <tr>
          <td align="right"><label for="surname">Surname:</label></td>
          <td><span id="sprytextfield2">
            <input name="surname" type="text" id="surname" value="<?php echo isset($_REQUEST['surname']) ? htmlentities($_REQUEST['surname'], ENT_COMPAT, "UTF-8") : $row_rsContact['surname']; ?>" size="50" maxlength="50"  class="form-control"/>
          <span class="textfieldRequiredMsg">A value is required.</span></span></td>
        </tr>
        <tr>
          <td align="right"><label for="jobtitle"><?php echo isset($row_rsPreferences['text_role']) ? $row_rsPreferences['text_role'] : "Job Title"; ?>:</label></td>
          <td><input name="jobtitle" type="text" id="jobtitle" value="<?php echo isset($_REQUEST['jobtitle']) ? htmlentities($_REQUEST['jobtitle'], ENT_COMPAT, "UTF-8") : $row_rsContact['jobtitle']; ?>" size="50" maxlength="50" class="form-control" /></td>
        </tr>
        <tr>
          <td align="right"><label for="telephone">Telephone:</label></td>
          <td><input name="telephone" type="text" id="telephone" value="<?php echo isset($_REQUEST['telephone']) ? htmlentities($_REQUEST['telephone'], ENT_COMPAT, "UTF-8") : $row_rsContact['telephone']; ?>" size="50" maxlength="50"  class="form-control"/></td>
        </tr>
        <tr>
          <td align="right"><label for="email">Email:</label></td>
          <td><input name="email" type="email" id="email" multiple value="<?php echo isset($_REQUEST['email']) ? htmlentities($_REQUEST['email'], ENT_COMPAT, "UTF-8") : $row_rsContact['email']; ?>" size="50" maxlength="50" class="form-control" /></td>
        </tr>
        <?php if($row_rsDirectory['userID']==$row_rsLoggedIn['ID']) { ?>
        <tr>
          <td align="right">&nbsp;</td>
          <td><label>
            <input type="checkbox" name="maincontact" id="maincontact" <?php if($row_rsDirectory['userID']==$row_rsContact['userID']) { echo "checked=\"checked\""; } ?>>
            Main contact
            <input type="hidden" name="token" id="token" value="<?php echo md5(PRIVATE_KEY.$row_rsContact['userID']); ?>">
          </label></td>
        </tr><?php } ?>
        <tr>
          <td align="right">&nbsp;</td>
          <td><button type="submit" class="btn btn-primary">Save changes</button>
          <input name="ID" type="hidden" id="ID" value="<?php echo $row_rsContact['userID']; ?>" />
          <input name="modifiedbyID" type="hidden" id="modifiedbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
          <input name="modifieddatetime" type="hidden" id="modifieddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
          <input name="directoryID" type="hidden" id="directoryID" value="<?php echo $row_rsDirectory['ID']; ?>"></td>
        </tr>
      </table>
      <input type="hidden" name="MM_update" value="form1" />
    </form>
    <?php } //end authorised
	  else { ?>
    <p class="alert alert-danger" role="alert">You are not authorised to edit this entry.</p>
    <?php } ?>
    <script>
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1");
var sprytextfield2 = new Spry.Widget.ValidationTextField("sprytextfield2");
    </script>
    <!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../../../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsDirectory);

mysql_free_result($rsDirectoryUser);

mysql_free_result($rsIsAuthorised);

mysql_free_result($rsPreferences);

mysql_free_result($rsContact);
?>
