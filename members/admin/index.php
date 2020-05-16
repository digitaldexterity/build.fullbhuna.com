<?php require_once('../../Connections/aquiescedb.php'); ?><?php require_once('../../core/includes/adminAccess.inc.php'); ?>
<?php require_once('../includes/userfunctions.inc.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "7,8,9,10";
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
}//print_r($_SESSION['checkbox']); die();
?>
<?php 

$currentPage = $_SERVER["PHP_SELF"];


$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID, usertypeID, regionID FROM users WHERE username = %s LIMIT 1", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

// added by me to restrict region on power users
if ($row_rsLoggedIn['usertypeID'] <9) { $_GET['regionID'] = $row_rsLoggedIn['regionID']; } 
 $_GET['usertypeID'] = isset( $_GET['usertypeID']) ?  $_GET['usertypeID'] : -4;
// end added by me



if(isset($_POST['formaction'])) {
	if($_POST['formaction']=="generateLogins") {
		if(isset($_SESSION['checkbox']) && count($_SESSION['checkbox'])>0) { 
			$send_emails = (count($_SESSION['checkbox'])<=20) ? true : false;// for small numbers we can email
			$count = 0;
			foreach($_SESSION['checkbox'] as $key=>$userID) {
				if($userID!=$row_rsLoggedIn['ID'] ) {
					// not your own login! 
					setUsernamePassword($userID,"","",$send_emails, true);
						$count++;
				}			
			}// end foreach
			
		$msg = $count." selected users have been given new usernames and passwords.";
		$msg .= $send_emails ? "\n\nThe users with email addresses have been sent their login details." : "";
		}
	} else if($_POST['formaction']=="sendEmails") {
		if(isset($_SESSION['checkbox']) && count($_SESSION['checkbox'])>0) { 
			$users = array();
			$email_string = "";
			foreach($_SESSION['checkbox'] as $key=>$userID) {
				array_push($users, intval($userID));
			}
			if(count($users)>0) {
				$usersql = implode(" OR ID = ",$users);
				mysql_select_db($database_aquiescedb, $aquiescedb);	
				$select = "SELECT email FROM users WHERE email IS NOT NULL AND (ID = ".$usersql.")";
				$result = mysql_query($select, $aquiescedb) or die(mysql_error());
				if(mysql_num_rows($result)>0) {
					while($row = mysql_fetch_assoc($result)) {
						$email_string .= ($email_string=="") ? "" : "; ";
						$email_string .= $row['email'];
					}
				}
			}			
		}	
	} else if($_POST['formaction']=="changeRank") {
		if(isset($_SESSION['checkbox']) && count($_SESSION['checkbox'])>0) { 
			foreach($_SESSION['checkbox'] as $key=>$userID) {
				regradeUser($userID, $_POST['usertypeID'], $row_rsLoggedIn['ID'],true);				
			} // end foreach
			$msg = count($_SESSION['checkbox'])." selected users have been updated.";
		}
	}
}

if(isset($_GET['upgradepending'])) {
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$update = "UPDATE users SET usertypeID = 1 WHERE usertypeID = 0 AND (regionID IS NULL OR regionID = 0 OR regionID = ".intval($regionID).")";
	mysql_query($update, $aquiescedb) or die(mysql_error());
	$count = mysql_affected_rows();
	$msg = $count." users have been upgraded to Member.";
}




mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsUserType = "SELECT * FROM usertype ORDER BY ID ASC";
$rsUserType = mysql_query($query_rsUserType, $aquiescedb) or die(mysql_error());
$row_rsUserType = mysql_fetch_assoc($rsUserType);
$totalRows_rsUserType = mysql_num_rows($rsUserType);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = "SELECT * FROM preferences WHERE ID=".$regionID;
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsRegions = "SELECT ID, title FROM region WHERE statusID = 1 ORDER BY title ASC";
$rsRegions = mysql_query($query_rsRegions, $aquiescedb) or die(mysql_error());
$row_rsRegions = mysql_fetch_assoc($rsRegions);
$totalRows_rsRegions = mysql_num_rows($rsRegions);

$varRegionID_rsGroups = "1";
if (isset($regionID)) {
  $varRegionID_rsGroups = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsGroups = sprintf("SELECT ID, groupname FROM usergroup WHERE usergroup.regionID = 0 OR usergroup.regionID = %s ORDER BY groupname ASC", GetSQLValueString($varRegionID_rsGroups, "int"));
$rsGroups = mysql_query($query_rsGroups, $aquiescedb) or die(mysql_error());
$row_rsGroups = mysql_fetch_assoc($rsGroups);
$totalRows_rsGroups = mysql_num_rows($rsGroups);

$body_class = " users";
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Manage Users"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<style> <!--  
<?php if ($totalRegions<1 || $_SESSION['MM_UserGroup'] <9) {  
	echo ".region {display:none !important; }\n"; 
} 
if ($_SESSION['MM_UserGroup'] <9) { 
	echo ".managerOnly {display:none !important;}\n"; 
} 

if($row_rsPreferences['user_list_email']==0) {
	echo ".col_email { display: none !important; }\n";
}
if($row_rsPreferences['user_list_phone']==0) {
	echo ".col_phone { display: none !important; }\n";
}
if($row_rsPreferences['user_list_mobile']==0) {
	echo ".col_mobile { display: none !important; }\n";
}
?>
--></style>
<script src="/core/scripts/checkbox/checkboxes.js"></script><?php require_once('../../core/scripts/checkbox/checkboxsession.inc.php'); ?>
<script src="/core/scripts/liveSearch.js"></script>
<script>

var liveSearchURL = "/members/admin/ajax/users.inc.php?returnURL=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>";

addListener("load",init);

useCheckboxSession = true;
checkboxForm = 'formUsers';


function init() {
	addListener("click", generateLogins, document.getElementById('generateLoginsLink'));
	addListener("click", upgradePending, document.getElementById('upgradePendingLink'));
	addListener("click", sendEmails, document.getElementById('sendEmails'));
	
}

function generateLogins() {	
	if(confirm('Are you sure you want to generate NEW usernames and passwords for the selected users?\n\n[Your own password cannot be changed this way]')) {	
		if(anyChecked(document.getElementById('formUsers'))) {
			document.getElementById('formaction').value = "generateLogins";
		 	document.getElementById('formUsers').submit();
		}
	}
}

function changeRank() {
	if(document.getElementById('rankID').value!="") {	
		if(anyChecked(document.getElementById('formUsers'))) {
			if(confirm('Are you sure you want to change the rank of all selected users to '+$('#rankID option:selected').text()+'?\n\nOnly users with equal or lower rank to your own will be updated.')) {
			document.getElementById('formaction').value = "changeRank";
			document.formUsers.usertypeID.value = document.getElementById('rankID').value;
			 document.getElementById('formUsers').submit();
			} else {
				$('#rankID').val('');
			}
		}
	} else {
		alert ("Please select a rank");
	}
}

function sendEmails() {
	
	if(anyChecked(document.getElementById('formUsers'))) {
		document.getElementById('formaction').value = "sendEmails";
		 document.getElementById('formUsers').submit();
	} 
}

function upgradePending() {
	if(confirm('Are you sure you want to upgrade all Pending Members to Members?\n\n(If there are some you do not wish to upgrade, update those to Non Users first.)')) {
	document.location = "index.php?upgradepending=true";
	}
}
</script>
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
    <!-- InstanceBeginEditable name="Body" --><div class="page users"><?php require_once('../../core/region/includes/chooseregion.inc.php'); ?>
  
    <h1><i class="glyphicon glyphicon-user"></i> Manage Users</h1>
    <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
      <li class="nav-item"><a class="nav-link" href="add_user.php" ><i class="glyphicon glyphicon-plus-sign"></i> Add User</a></li>
      <li class="nav-item" id="linkUserGroups"><a class="nav-link" href= "groups/index.php"><i class="glyphicon glyphicon-user"></i>  Groups &amp; Relationships</a></li>
      
      
      <li class="nav-item" id="linkUserOptions"><a href="options/index.php" class="nav-link"><i class="glyphicon glyphicon-cog"></i> User Options</a></li>
      
      <li class="nav-item"><a class="nav-link" href="javascript:void(0);" id="upgradePendingLink"><i class="glyphicon glyphicon-hand-up"></i> Upgrade all pending</a></li>
     
    </ul></div></nav>
    <form action="index.php" method="get" name="searchform" id="searchform" class="form-inline">
    <fieldset><legend>Filter</legend>
     <div class="input-group">
            <input name="search" type="text"  id="search" value="<?php echo isset($_GET['search']) ? htmlentities($_GET['search']) : ""; ?>" size="50" maxlength="50" class="form-control" placeholder="Search by name or email" /><span class="input-group-btn">
          <button type="submit" class="btn btn-default btn-secondary"><i class="glyphicon glyphicon-search"></i></button></span></div>
            <select name="usertypeID" class="form-control" onChange="this.form.submit()"  >
              <option value="-4" <?php if (!(strcmp(-4, $_GET['usertypeID']))) {echo "selected=\"selected\"";} ?>>All Active Ranks</option>
              <option value="-3" <?php if (!(strcmp(-3, $_GET['usertypeID']))) {echo "selected=\"selected\"";} ?>>All Users</option>
              <?php
do {  
?>
              <option value="<?php echo $row_rsUserType['ID']?>"<?php if (!(strcmp($row_rsUserType['ID'], $_GET['usertypeID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsUserType['name']?></option>
              <?php
} while ($row_rsUserType = mysql_fetch_assoc($rsUserType));
  $rows = mysql_num_rows($rsUserType);
  if($rows > 0) {
      mysql_data_seek($rsUserType, 0);
	  $row_rsUserType = mysql_fetch_assoc($rsUserType);
  }
?>
            </select>
            <?php if ($totalRows_rsGroups > 0) { // Show if recordset not empty ?>
  <select class="form-control" name="groupID" id="groupID" onChange="this.form.submit();">
    
    <option value="0" <?php if (!(strcmp(0, htmlentities($groupID)))) {echo "selected=\"selected\"";} ?>>In any or no group</option>
    <?php
do {  
?>
    <option value="<?php echo $row_rsGroups['ID']?>" <?php if ($row_rsGroups['ID']==$_GET['groupID']) {echo "selected=\"selected\"";} ?>><?php echo $row_rsGroups['groupname']; ?></option>
    <?php
} while ($row_rsGroups = mysql_fetch_assoc($rsGroups));
  $rows = mysql_num_rows($rsGroups);
  if($rows > 0) {
      mysql_data_seek($rsGroups, 0);
	  $row_rsGroups = mysql_fetch_assoc($rsGroups);
  }
?>
  </select>
  <?php } // Show if recordset not empty ?>
    </fieldset>
    </form>
   <?php require_once('../../core/includes/alert.inc.php'); ?>
    <?php if(isset($email_string) && strlen($email_string)>5) { ?>
    <p class="message alert alert-info" role="alert">To send an email to the specified recipients, click on the link below:<br><a href="mailto:<?php echo $email_string; ?>"><?php echo $email_string; ?></a></p>
	<?php } ?>
   <div id="liveSearchDIV"><?php require_once('ajax/users.inc.php'); ?>
   </div>
   <p class="form-inline">With selected: <a href="javascript:void(0);" id="generateLoginsLink" class="btn btn-sm btn-default btn-secondary"  ><i class="glyphicon glyphicon-lock"></i> Generate Logins</a> <a href="javascript:void(0);" id="sendEmails"class="btn btn-sm btn-default btn-secondary"><i class="glyphicon glyphicon-envelope"></i> Send email</a> <select name="rankID"  id="rankID" onChange="changeRank()" class="form-control input-sm" >
             
              <option value="" >Change rank to...</option>
              <?php
do {  if($row_rsUserType['ID']<= $row_rsLoggedIn['usertypeID']) {
?>
              <option value="<?php echo $row_rsUserType['ID']; ?>"><?php echo $row_rsUserType['name']?></option>
              <?php
} 

} while ($row_rsUserType = mysql_fetch_assoc($rsUserType));
  $rows = mysql_num_rows($rsUserType);
  if($rows > 0) {
      mysql_data_seek($rsUserType, 0);
	  $row_rsUserType = mysql_fetch_assoc($rsUserType);
  }
?>
            </select> </p>
    <h2>Icons</h2>
  
      
    <p><img src="/core/images/icons/world.png" alt="Web sign-up" width="16" height="16" style="vertical-align:
middle;" /> = Web sign up&nbsp;&nbsp;&nbsp;&nbsp;<img src="/core/images/icons/cross.png" alt="No login details" width="16" height="16" style="vertical-align:
middle;" />= No login credentials</p>
    </div>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php

mysql_free_result($rsUserType);

mysql_free_result($rsRegions);

mysql_free_result($rsGroups);

mysql_free_result($rsLoggedIn);
?>
