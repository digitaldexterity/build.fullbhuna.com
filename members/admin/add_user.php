<?php require_once('../../Connections/aquiescedb.php'); ?><?php require_once('../../core/includes/adminAccess.inc.php'); ?><?php require_once('../../mail/includes/sendmail.inc.php'); ?>
<?php require_once('../includes/userfunctions.inc.php'); 
// flexible add user system
/* VARIABLE PASSING :
$_GET['returnURL'] - where to go after adding
$_GET['moredetails'] == true - useed with above - go to update user first
$_GET['directoryID'] - associate user with a directory entry

*/


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

$colname_rsChosenGroup = "-1";
if (isset($_REQUEST['groupID'])) {
  $colname_rsChosenGroup = $_REQUEST['groupID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsChosenGroup = sprintf("SELECT groupname FROM usergroup WHERE ID = %s", GetSQLValueString($colname_rsChosenGroup, "int"));
$rsChosenGroup = mysql_query($query_rsChosenGroup, $aquiescedb) or die(mysql_error());
$row_rsChosenGroup = mysql_fetch_assoc($rsChosenGroup);
$totalRows_rsChosenGroup = mysql_num_rows($rsChosenGroup);


// check to see if email already exists

$email = isset($_POST['email']) ? $_POST['email'] : "";
$userID = emailTaken($email);
if ($userID>0) {
	$select = "SELECT firstname, surname, username, usertypeID FROM users WHERE ID = ".$userID;
	$result = mysql_query($select, $aquiescedb) or die(mysql_error());
	$row = mysql_fetch_assoc($result);
	
    $error = "The email address ".$_POST['email']." has already been used by <a href=\"modify_user.php?userID=".$userID."\">".$row['firstname']." ".$row['surname']." (".$row['username'].")</a>.";
	
	// give option to upgrade
	if((isset($_REQUEST['groupID']) && intval($_REQUEST['groupID'])>0) || (isset($_GET['usertypeID']) && $_GET['usertypeID'] > $row['usertypeID'] )) { 
	$error.= "<br /><a href=\"add_user.php?upgrade=true&userID=".$userID; 
	$error.= isset($_REQUEST['groupID']) ? "&groupID=".intval($_REQUEST['groupID']) : "";  
	$error.= isset($_GET['usertypeID']) ? "&usertypeID=".intval($_GET['usertypeID']) : ""; 
	$error.= isset($_GET['returnURL']) ? "&returnURL=".urlencode($_GET['returnURL']) : "";
	$error.= "\">Add this user to group: ";
	$error.= isset($row_rsThisUserType['name']) ? $row_rsThisUserType['name'] : "Member"; 
	$error.= isset($row_rsChosenGroup['groupname']) ? "/".$row_rsChosenGroup['groupname'] : ""; 
    $error.="</a>";
	} // option to upgrade user
	unset($_POST['MM_insert']);
} // found user
  
// check to see if similar users








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
$query_rsGroups = "SELECT * FROM usergroup WHERE groupsetID IS NULL AND statusID = 1 ORDER BY groupname ASC";
$rsGroups = mysql_query($query_rsGroups, $aquiescedb) or die(mysql_error());
$row_rsGroups = mysql_fetch_assoc($rsGroups);
$totalRows_rsGroups = mysql_num_rows($rsGroups);


$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}


if(isset($_POST['MM_insert']) && $_POST['MM_insert']=='form1') {
	$rsSimilarNames = findSimilarUsers($_POST['firstname'], $_POST['middlename'], $_POST['surname'], $_POST['email']);
	$row_rsSimilarNames = mysql_fetch_assoc($rsSimilarNames);
	if (mysql_num_rows($rsSimilarNames) > 0 && !isset($_POST['notSimilar'])) { 
	unset($_POST["MM_insert"]);
	 }

}




if(isset($_GET['upgrade'])) { // just upgrade
	mysql_select_db($database_aquiescedb, $aquiescedb);
	if(isset($_GET['usertypeID'])) { // upgrade user
		regradeUser($_GET['userID'], $_GET['usertypeID'], $row_rsLoggedIn['ID']);
	} // upgrade user
	if(isset($_GET['groupID'])) { // add to group
		addUsertoGroup($_GET['userID'], $_GET['groupID'],$row_rsLoggedIn['ID']);
	} // add to group
	$redirectURL = (isset($_GET['redirectURL']) && $_GET['redirectURL'] !="") ? $_GET['redirectURL'] : "modify_user.php?userID=".intval($_GET['userID']);
	header("location: ".$redirectURL); exit;			   
} // end  just upgrade







if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
	$directoryID = isset($_POST['directoryID']) ? $_POST['directoryID'] : 0;
	$locationID = isset($_POST['locationID']) ? $_POST['locationID'] : 0;
	$userID = createNewUser($_POST['firstname'],$_POST['surname'],$_POST['email'],$_POST['usertypeID'],$_POST['groupID'],$directoryID,$_POST['createdbyID'],$_POST['salutation'],false,"","","","","", "", "",1,$_POST['jobtitle'],0,1,"","",$locationID, "", "", "", "", 1);
	
	
	if (isset($_POST['autopassword'])) { // auto generate username and password
		setUsernamePassword($userID, "", "", true);
		
	} // end auto generate


  $insertGoTo = (isset($_POST['returnURL']) && $_POST['returnURL'] !="" && $_POST['moredetails']!="") ? $_POST['returnURL'] : "modify_user.php?userID=".$userID;
  $insertGoTo .= isset($userlogin) ? "&username=".$userlogin[0]."&password=".$userlogin[1] : "";
  if (isset($_SERVER['QUERY_STRING'])) {
    $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
    $insertGoTo .= $_SERVER['QUERY_STRING'];
  }
  header("Location: ".$insertGoTo); exit;
}

?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Add "; $pageTitle .= isset($row_rsThisUserType['name']) ? $row_rsThisUserType['name'] : "User"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script src="/SpryAssets/SpryTabbedPanels.js"></script>
<script src="../../SpryAssets/SpryValidationTextField.js"></script>
<link href="/SpryAssets/SpryTabbedPanels.css" rel="stylesheet"  />
<style><!--
<?php if($row_rsPreferences['usesalutation'] != 1) { ?>.salutation { display:none; } <?php } ?>
<?php if($row_rsPreferences['askmiddlename'] != 1) { ?>.middlename { display:none; } <?php } ?>
<?php if($totalRows_rsGroups <1 || isset($_GET['groupID'])) { // no groups or group pre-set ?>.usergroups { display:none; }<?php } ?>
--></style>

<link href="../../SpryAssets/SpryValidationTextField.css" rel="stylesheet"  />
<link href="../css/membersDefault.css" rel="stylesheet"  />
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
            <h1><i class="glyphicon glyphicon-user"></i> Add <?php echo isset($row_rsThisUserType['name']) ? $row_rsThisUserType['name'] : "User"; ?></h1>
<?php require_once('../../core/includes/alert.inc.php'); ?>
            <form action="<?php echo $editFormAction; ?>" method="POST" name="form1" id="form1" class="form-inline"><span id="sprytextfield4"><input name="salutation" type="text"  id="salutation" size="6" maxlength="6"  value="<?php echo isset($_REQUEST['salutation']) ? htmlentities($_REQUEST['salutation']) : ""; ?>" placeholder="Title" class="form-control" />
</span> <span id="sprytextfield1"><input name="firstname" type="text"  id="firstname" value="<?php echo isset($_REQUEST['firstname']) ? htmlentities($_REQUEST['firstname']) : ""; ?>" size="20" maxlength="20" placeholder="First name" class="form-control"/>
                      <span class="textfieldRequiredMsg"><br />A first name is required.</span></span>
                      
                      
                      <span class="top middlename">
                        <input name="middlename" type="text"  id="middlename" size="20" maxlength="20"  value="<?php echo isset($_REQUEST['middlename']) ? htmlentities($_REQUEST['middlename']) : ""; ?>" placeholder="Middle name(s)" class="form-control" />
</span> 

<span id="sprytextfield2">
                        <input name="surname" type="text"  id="surname" size="20" maxlength="20"  value="<?php echo isset($_REQUEST['surname']) ? htmlentities($_REQUEST['surname']) : ""; ?>" placeholder="Surname" class="form-control" />
<span class="textfieldRequiredMsg"><br />
            A surname is required.</span></span> <span id="sprytextfield3">
                        <input name="email" type="email"  id="email" size="20" maxlength="50"  value="<?php echo isset($_REQUEST['email']) ? htmlentities($_REQUEST['email']) : ""; ?>" placeholder="email  (optional)"  class="form-control"/>
                      <span class="textfieldInvalidFormatMsg"><br />
                      Invalid email.</span></span>
                      <span class="jobtitle"><span id="sprytextfield5">
                        <input name="jobtitle" type="text"  id="jobtitle" size="20" maxlength="50"  value="<?php echo isset($_REQUEST['jobtitle']) ? htmlentities($_REQUEST['jobtitle']) : ""; ?>" placeholder="<?php echo isset($row_rsPreferences['text_role']) ? $row_rsPreferences['text_role'] : "Job Title"; ?> (optional)"  class="form-control"/>
</span></span>
                    
                    <div class="usergroups">
                      <select name="groupID" id="groupID" class="form-control">
                        <option value="0" <?php if (!(strcmp(0, @$_REQUEST['groupID']))) {echo "selected=\"selected\"";} ?>>Add to group (optional)...</option>
                        <?php
do {  
?>
                        <option value="<?php echo $row_rsGroups['ID']?>"<?php if (!(strcmp($row_rsGroups['ID'], @$_REQUEST['groupID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsGroups['groupname']?></option>
                        <?php
} while ($row_rsGroups = mysql_fetch_assoc($rsGroups));
  $rows = mysql_num_rows($rsGroups);
  if($rows > 0) {
      mysql_data_seek($rsGroups, 0);
	  $row_rsGroups = mysql_fetch_assoc($rsGroups);
  }
?>
                      </select></div>
               
                  
                  <?php if (isset($rsSimilarNames) && (is_object($rsSimilarNames) ||is_resource($rsSimilarNames)) && mysql_num_rows($rsSimilarNames)>0) { // Show if recordset not empty ?>
        <p class="alert warning alert-warning" role="alert">We have found the following similar users already in the system:</p>
                    <table  class="listTable">
                      <tr>
                        <th>Added</th>
                        <th>Name</td>
                        <th>Type</th>
                        <th>Action</th>
                      </tr>
                      <?php do { ?>
                        <tr>
                          <td><?php echo date('d M y',strtotime($row_rsSimilarNames['dateadded'])); ?></td>
                          <td class="warning<?php echo $row_rsSimilarNames['warning']; ?>"><a href="modify_user.php?userID=<?php echo $row_rsSimilarNames['ID']; ?>"><?php echo $row_rsSimilarNames['firstname']." ".$row_rsSimilarNames['middlename']." ". $row_rsSimilarNames['surname']; ?></a></td>
                          <td><?php echo $row_rsSimilarNames['usertype']; ?></td>
                          <td><?php if(isset($_REQUEST['groupID']) || (isset($_GET['usertypeID']) && $_GET['usertypeID'] > $row_rsSimilarNames['usertypeID'] )) { ?><a href="add_user.php?upgrade=true&userID=<?php echo $row_rsSimilarNames['ID']; echo isset($_REQUEST['groupID']) ? "&groupID=".intval($_REQUEST['groupID']) : "";  echo isset($_GET['usertypeID']) ? "&usertypeID=".intval($_GET['usertypeID']) : ""; echo isset($_GET['returnURL']) ? "&returnURL=".urlencode($_GET['returnURL']) : ""; ?>">Add to group: <?php echo isset($row_rsThisUserType['name']) ? $row_rsThisUserType['name'] : "Member"; ?><?php echo isset($row_rsChosenGroup['groupname']) ? "/".$row_rsChosenGroup['groupname'] : ""; ?></a><?php } ?>&nbsp;</td>
                        </tr>
<?php } while ($row_rsSimilarNames = mysql_fetch_assoc($rsSimilarNames)); ?>
                  </table>
        <p><input name="notSimilar" type="hidden" id="notSimilar" value="1" />
        If it's NOT one of the above, click to add new user below:</p>
                     
                    
                    <?php } // Show if recordset not empty ?>

                    <button type="submit" class="btn btn-primary"  onclick="checkForm(this.form);return document.returnValue;">Add New User</button>&nbsp;&nbsp;
                        <label><input name="autopassword" type="checkbox" id="autopassword" value="1" <?php if(isset($_REQUEST['autopassword']) && $_REQUEST['autopassword'] == 1) { ?>checked="checked"<?php } ?> />&nbsp;Auto generate and email username/password to user
                        <input name="usertypeID" type="hidden" id="usertypeID" value="<?php echo isset($_REQUEST['usertypeID']) ? $_REQUEST['usertypeID'] : 1; ?>" />
                    </label>
        <input type="hidden" name="MM_insert" value="form1" />
                    <input name="createdbyID" type="hidden" id="createdbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
                    <input name="createddatetime" type="hidden" id="createddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
<input name="returnURL" type="hidden" id="returnURL" value="<?php echo isset($_REQUEST['returnURL']) ?  $_REQUEST['returnURL'] : ""; ?>" />
   
    <input name="directoryID" type="hidden" id="directoryID" value="<?php echo isset($_REQUEST['directoryID']) ? $_REQUEST['directoryID'] : ""; ?>" />
     <input name="locationID" type="hidden" id="locationID" value="<?php echo isset($_REQUEST['locationID']) ? $_REQUEST['locationID'] : ""; ?>" />
    <input name="moredetails" type="hidden" id="moredetails" value="<?php echo isset($_REQUEST['moredetails']) ? $_REQUEST['moredetails'] : ""; ?>" />
    </form>
          
         
    <script>
<!--
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1", "none");
var sprytextfield2 = new Spry.Widget.ValidationTextField("sprytextfield2", "none");
var sprytextfield3 = new Spry.Widget.ValidationTextField("sprytextfield3", "email", {isRequired:false});
var sprytextfield4 = new Spry.Widget.ValidationTextField("sprytextfield4", "none", {isRequired:false});
var sprytextfield5 = new Spry.Widget.ValidationTextField("sprytextfield5", "none", {isRequired:false});
//-->
    </script></div>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php

mysql_free_result($rsLoggedIn);

mysql_free_result($rsPreferences);

mysql_free_result($rsThisUserType);

mysql_free_result($rsGroups);

mysql_free_result($rsChosenGroup);
?>
