<?php require_once('../../../Connections/aquiescedb.php'); ?><?php require_once('../../../mail/includes/sendmail.inc.php'); ?>
<?php require_once('../../../members/includes/userfunctions.inc.php'); 
// flexible add user system
/* VARIABLE PASSING :
$_GET['directoryID'] - associate user with a directory entry

*/
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

$varDirectoryID_rsIsAuthorised = "-1";
if (isset($_GET['directoryID'])) {
  $varDirectoryID_rsIsAuthorised = $_GET['directoryID'];
}
$varUsername_rsIsAuthorised = "-1";
if (isset($_SESSION['MM_Username'])) {
  $varUsername_rsIsAuthorised = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsIsAuthorised = sprintf("SELECT directoryuser.ID FROM directoryuser LEFT JOIN users ON (directoryuser.userID = users.ID) WHERE directoryID = %s AND users.username = %s", GetSQLValueString($varDirectoryID_rsIsAuthorised, "int"),GetSQLValueString($varUsername_rsIsAuthorised, "text"));
$rsIsAuthorised = mysql_query($query_rsIsAuthorised, $aquiescedb) or die(mysql_error());
$row_rsIsAuthorised = mysql_fetch_assoc($rsIsAuthorised);
$totalRows_rsIsAuthorised = mysql_num_rows($rsIsAuthorised);

if($totalRows_rsIsAuthorised==0 && $_SESSION['MM_UserGroup']<8) {
	die("You are not authorised to add contacts.");
}

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = "SELECT * FROM preferences";
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);

$varUserType_rsThisUserType = "-1";
if (isset($_GET['usertypeID'])) {
  $varUserType_rsThisUserType = $_GET['usertypeID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsThisUserType = sprintf("SELECT name FROM usertype WHERE ID = %s AND %s > 1 ", GetSQLValueString($varUserType_rsThisUserType, "int"),GetSQLValueString($varUserType_rsThisUserType, "int"));
$rsThisUserType = mysql_query($query_rsThisUserType, $aquiescedb) or die(mysql_error());
$row_rsThisUserType = mysql_fetch_assoc($rsThisUserType);
$totalRows_rsThisUserType = mysql_num_rows($rsThisUserType);

// check to see if email already exists

$email = isset($_POST['email']) ? $_POST['email'] : "";
$userID = emailTaken($email);
if ($userID>0) {
	$select = "SELECT firstname, surname, username, usertypeID FROM users WHERE ID = ".$userID;
	$result = mysql_query($select, $aquiescedb) or die(mysql_error());
	$row = mysql_fetch_assoc($result);
	
    $submit_error = "The email address ".$_POST['email']." has already been used by<a href=\"modifyuser.php?userID=".$userID."\">".$row['firstname']." ".$row['surname']." (".$row['username'].")</a>.";
	
	
	unset($_POST['MM_insert']);
} // found user
  
// check to see if similar users


$maxRows_rsSimilarNames = 100;
$pageNum_rsSimilarNames = 0;
if (isset($_GET['pageNum_rsSimilarNames'])) {
  $pageNum_rsSimilarNames = $_GET['pageNum_rsSimilarNames'];
}
$startRow_rsSimilarNames = $pageNum_rsSimilarNames * $maxRows_rsSimilarNames;

$varFirstname_rsSimilarNames = "%";
if (isset($_POST['firstname'])) {
  $varFirstname_rsSimilarNames = $_POST['firstname'];
}
$varMMInsert_rsSimilarNames = "-1";
if (isset($_POST['MM_insert'])) {
  $varMMInsert_rsSimilarNames = $_POST['MM_insert'];
}
$varSurname_rsSimilarNames = "%";
if (isset($_POST['surname'])) {
  $varSurname_rsSimilarNames = $_POST['surname'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsSimilarNames = sprintf("SELECT users.ID, users.firstname, users.surname, users.dob, users.jobtitle, users.dateadded, users.usertypeID, usertype.name AS usertype FROM users LEFT JOIN usertype ON (users.usertypeID = usertype.ID) WHERE users.firstname LIKE %s AND users.surname LIKE %s AND %s = 'form1'", GetSQLValueString("%" . $varFirstname_rsSimilarNames . "%", "text"),GetSQLValueString("%" . $varSurname_rsSimilarNames . "%", "text"),GetSQLValueString($varMMInsert_rsSimilarNames, "text"));
$query_limit_rsSimilarNames = sprintf("%s LIMIT %d, %d", $query_rsSimilarNames, $startRow_rsSimilarNames, $maxRows_rsSimilarNames);
$rsSimilarNames = mysql_query($query_limit_rsSimilarNames, $aquiescedb) or die(mysql_error());
$row_rsSimilarNames = mysql_fetch_assoc($rsSimilarNames);

if (isset($_GET['totalRows_rsSimilarNames'])) {
  $totalRows_rsSimilarNames = $_GET['totalRows_rsSimilarNames'];
} else {
  $all_rsSimilarNames = mysql_query($query_rsSimilarNames);
  $totalRows_rsSimilarNames = mysql_num_rows($all_rsSimilarNames);
}
$totalPages_rsSimilarNames = ceil($totalRows_rsSimilarNames/$maxRows_rsSimilarNames)-1;

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ($totalRows_rsSimilarNames > 0 && !isset($_POST['notSimilar'])) { unset($_POST["MM_insert"]); }


if(isset($_GET['addcontact']) && isset($_GET['userID']) && isset($_GET['directoryID'])) { // just add contact
	addUserToDirectory($_GET['userID'], $_GET['directoryID'], $row_rsLoggedIn['ID']);
			   
} // end  just add contact







if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
	$directoryID = isset($_POST['directoryID']) ? $_POST['directoryID'] : 0;
	
	$userID = createNewUser($_POST['firstname'],$_POST['surname'],$_POST['email'],$_POST['usertypeID'],$_POST['groupID'],$directoryID,$_POST['createdbyID'],$_POST['salutation'],false,"","","","","", "", "",1,$_POST['jobtitle'],0,1,"",$_POST['telephone']);
	
	if (isset($_POST['autopassword'])) { // auto generate username and password
		setUsernamePassword($userID, "", "", true);
		
	} // end auto generate
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1") || isset($_GET['addcontact'])) {

  $insertGoTo = "/directory/members/update_directory.php?directoryID=".$directoryID;
  if (isset($_SERVER['QUERY_STRING'])) {
    $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
    $insertGoTo .= $_SERVER['QUERY_STRING'];
  }
  header("Location: ".$insertGoTo); exit;
}

?>
<?php
$varDirectoryID_rsDirectory = "-1";
if (isset($_GET['directoryID'])) {
  $varDirectoryID_rsDirectory = $_GET['directoryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsDirectory = sprintf("SELECT directory.name FROM directory WHERE directory.ID = %s", GetSQLValueString($varDirectoryID_rsDirectory, "int"));
$rsDirectory = mysql_query($query_rsDirectory, $aquiescedb) or die(mysql_error());
$row_rsDirectory = mysql_fetch_assoc($rsDirectory);
$totalRows_rsDirectory = mysql_num_rows($rsDirectory);


?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php echo $site_name; $pageTitle = "Add Contact - ".$row_rsDirectory['name']; echo " - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script src="/SpryAssets/SpryTabbedPanels.js"></script>
<script src="../../../SpryAssets/SpryValidationTextField.js"></script>
<link href="/SpryAssets/SpryTabbedPanels.css" rel="stylesheet"  />
<style><!--
<?php if($row_rsPreferences['usesalutation'] != 1) { ?>.salutation { display:none; } <?php } ?>
--></style>

<link href="../../../SpryAssets/SpryValidationTextField.css" rel="stylesheet"  />
<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../../../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" -->
            <h1 class="directoryheader">Add <?php echo $row_rsDirectory['name']; ?> Contact</h1>
             <?php if(isset($submit_error)) { ?><p class="alert alert-danger" role="alert"><?php echo $submit_error; ?></p><?php } ?>
            <form action="<?php echo $editFormAction; ?>" method="POST" name="form1" id="form1" class="form-inline">
                  <table  class="form-table">
                    <tr>
                      <td valign="top" class="salutation"><span id="sprytextfield4">
                        <input name="salutation" type="text"  id="salutation" size="6" maxlength="6"  value="<?php echo isset($_REQUEST['salutation']) ? htmlentities($_REQUEST['salutation'], ENT_COMPAT, "UTF-8") : ""; ?>" class="form-control" />
</span></td>
                      <td class="top"><span id="sprytextfield1">
                        <input name="firstname" type="text"  id="firstname" value="<?php echo isset($_REQUEST['firstname']) ? htmlentities($_REQUEST['firstname'], ENT_COMPAT, "UTF-8") : ""; ?>" size="20" maxlength="20" class="form-control"  />
                      <span class="textfieldRequiredMsg"><br />A first name is required.</span></span></td>
                      <td class="top"><span id="sprytextfield2">
                        <input name="surname" type="text"  id="surname" size="20" maxlength="20"  value="<?php echo isset($_REQUEST['surname']) ? htmlentities($_REQUEST['surname'], ENT_COMPAT, "UTF-8") : ""; ?>"  class="form-control" />
<span class="textfieldRequiredMsg"><br />
            A surname is required.</span></span></td>
                      <td class="top"><span id="sprytextfield5">
                        <input name="jobtitle" type="text"  id="jobtitle" size="20" maxlength="20"  value="<?php echo isset($_REQUEST['jobtitle']) ? htmlentities($_REQUEST['jobtitle'], ENT_COMPAT, "UTF-8") : ""; ?>" class="form-control"  />
</span></td>
                      <td class="top"><span id="sprytextfield3">
                        <input name="email" type="email"  id="email" size="20" maxlength="50" multiple value="<?php echo isset($_REQUEST['email']) ? htmlentities($_REQUEST['email'], ENT_COMPAT, "UTF-8") : ""; ?>" />
                      <span class="textfieldInvalidFormatMsg"><br />
                      Invalid email.</span></span></td>
                      <td class="top"><span id="sprytextfield6">
                        <input name="telephone" type="text"  id="email2" size="20" maxlength="50"  value="<?php echo isset($_REQUEST['telephone']) ? htmlentities($_REQUEST['telephone'], ENT_COMPAT, "UTF-8") : ""; ?>"  class="telephone form-control" />
</span></td>
                      
                    </tr>
                  </table>
               
                  
                  <?php if ($totalRows_rsSimilarNames > 0) { // Show if recordset not empty ?>
        <p class="alert warning alert-warning" role="alert">We have found the following users already in the system:</p>
                    <table  class="table table-hover">
                    <thead>
                      <tr>
                        <th>Name</th>
                        <th>Role</th>
                        <th>Action</th>
                      </tr></thead><tbody>
                      <?php do { ?>
                        <tr>
                          <td><a href="/members/admin/modify_user.php?userID=<?php echo $row_rsSimilarNames['ID']; ?>"><?php echo $row_rsSimilarNames['firstname']." ". $row_rsSimilarNames['surname']; ?></a></td>
                          <td><?php echo $row_rsSimilarNames['jobtitle']; ?></td>
                          <td><a href="add_directory_user.php?addcontact=true&amp;directoryID=<?php echo intval($_GET['directoryID']); ?>&amp;userID=<?php echo $row_rsSimilarNames['ID']; ?>">Add this contact</a></td>
                        </tr>
<?php } while ($row_rsSimilarNames = mysql_fetch_assoc($rsSimilarNames)); ?></tbody>
                  </table>
        <p><input name="notSimilar" type="hidden" id="notSimilar" value="1" />
        If it's NOT one of the above, click to add the new user below:</p>
                     
                    
                    <?php } // Show if recordset not empty ?>

                    <button type="submit" class="btn btn-default btn-secondary"  onclick="checkForm(this.form);return document.returnValue;" >Add new contact</button> <label>
                        <input name="autopassword" type="checkbox" id="autopassword" value="1" <?php if(isset($_REQUEST['autopassword']) && $_REQUEST['autopassword'] == 1) { ?>checked="checked"<?php } ?> />
                        Auto generate and email username/password to user
                        <input name="usertypeID" type="hidden" id="usertypeID" value="<?php echo isset($_REQUEST['usertypeID']) ? $_REQUEST['usertypeID'] : 1; ?>" />
                    </label>
        <input type="hidden" name="MM_insert" value="form1" />
                    <input name="createdbyID" type="hidden" id="createdbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
                    <input name="createddatetime" type="hidden" id="createddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
<input name="returnURL" type="hidden" id="returnURL" value="<?php echo isset($_REQUEST['returnURL']) ?  $_REQUEST['returnURL'] : ""; ?>" />
   
    <input name="directoryID" type="hidden" id="directoryID" value="<?php echo isset($_REQUEST['directoryID']) ? $_REQUEST['directoryID'] : ""; ?>" />
    
    </form>
          
         
    <script>
<!--
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1", "none", {hint:"First name(s)"});
var sprytextfield2 = new Spry.Widget.ValidationTextField("sprytextfield2", "none", {hint:"Surname"});
var sprytextfield3 = new Spry.Widget.ValidationTextField("sprytextfield3", "email", {isRequired:false, hint:"email (optional)"});
var sprytextfield4 = new Spry.Widget.ValidationTextField("sprytextfield4", "none", {isRequired:false, hint:"Title"});
var sprytextfield5 = new Spry.Widget.ValidationTextField("sprytextfield5", "none", {isRequired:false, hint:"Role (optional)"});
var sprytextfield6 = new Spry.Widget.ValidationTextField("sprytextfield6", "none", {isRequired:false, hint:"Telephone (optional)"});
//-->
    </script>
    <!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../../../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsSimilarNames);

mysql_free_result($rsLoggedIn);

mysql_free_result($rsPreferences);

mysql_free_result($rsThisUserType);

mysql_free_result($rsDirectory);

mysql_free_result($rsIsAuthorised);
?>
