<?php require_once('../../Connections/aquiescedb.php'); ?>
<?php require_once('../includes/userfunctions.inc.php'); ?><?php require_once('../../core/includes/framework.inc.php'); ?>
<?php require_once('../../core/includes/upload.inc.php'); ?><?php require_once('../../mail/includes/sendmail.inc.php'); ?>
<?php
$MM_authorizedUsers = "";
$MM_donotCheckaccess = "true";

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
$regionID = (isset($regionID) && $regionID > 0 )? $regionID : 1;


$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

$varUsername_rsOptinGroups = "-1";
if (isset($_SESSION['MM_Username'])) {
  $varUsername_rsOptinGroups = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsOptinGroups = sprintf("SELECT usergroup.ID, usergroup.groupname, users.ID AS member, usergrouptype.grouptype FROM usergroup LEFT JOIN usergrouptype ON (usergroup.grouptypeID = usergrouptype.ID) LEFT JOIN usergroupmember ON (usergroup.ID = usergroupmember.groupID) LEFT OUTER JOIN users ON (usergroupmember.userID = users.ID AND users.username = %s) WHERE usergroup.statusID = 1 AND usergroup.optin = 1 GROUP BY usergroup.ID ORDER BY usergroup.grouptypeID, usergroup.groupname", GetSQLValueString($varUsername_rsOptinGroups, "text"));
$rsOptinGroups = mysql_query($query_rsOptinGroups, $aquiescedb) or die(mysql_error());
$row_rsOptinGroups = mysql_fetch_assoc($rsOptinGroups);
$totalRows_rsOptinGroups = mysql_num_rows($rsOptinGroups);

$colname_rsMe = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsMe = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsMe = sprintf("SELECT users.* FROM users WHERE username = %s ", GetSQLValueString($colname_rsMe, "text"));
$rsMe = mysql_query($query_rsMe, $aquiescedb) or die(mysql_error());
$row_rsMe = mysql_fetch_assoc($rsMe);
$totalRows_rsMe = mysql_num_rows($rsMe);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = "SELECT * FROM preferences WHERE ID=".$regionID."";
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) { 
	// do security checks
	if($_POST['ID']!=$row_rsMe['ID']) die();
	if(checkPassword($_SESSION['MM_Username'], $_POST['password'])) {
		$select = "SELECT email FROM users WHERE username = ".GetSQLValueString($_SESSION['MM_Username'],"text")." LIMIT 1";
		$result = mysql_query($select, $aquiescedb) or die(mysql_error());
		$row = mysql_fetch_assoc($result);
		if($row['email'] == $_SESSION['MM_Username'] && $row['email'] != $_POST['email']) { // current username is email and email has changed
			$select2= "SELECT username FROM users WHERE username != ".GetSQLValueString($_SESSION['MM_Username'],"text")." AND email = ".GetSQLValueString($_POST['email'], "text");
			$result2 = mysql_query($select2, $aquiescedb) or die(mysql_error());
			if(mysql_num_rows($result2)) { // email as username already exists
				$error = "The email address you have entered is already in use as a username under another user account.";
				unset($_POST["MM_update"]);
			} // end email already exists
								 
		} // username is email
		if(!validEmail($_POST['email'])) {
			$error = "We require a valid email address.";
			unset($_POST["MM_update"]);
		}
	} else { // wrong password
		$error = "You have entered the wrong password. Please try again.";
		unset($_POST["MM_update"]);
	}
} // end security checks

 
if(isset($_POST['updateoptingroups'])) {
	 do {  
	 if(isset($_POST['optingroup'][$row_rsOptinGroups['ID']])) { // checked 
			addUsertoGroup($row_rsMe['ID'], $row_rsOptinGroups['ID'],$row_rsMe['ID']);
	 	} else {
			$delete = "DELETE FROM usergroupmember WHERE groupID = ".$row_rsOptinGroups['ID']." AND userID = ".$row_rsMe['ID'];
			mysql_query($delete, $aquiescedb) or die(mysql_error());			
	 	}
	 } while ($row_rsOptinGroups = mysql_fetch_assoc($rsOptinGroups));
	 mysql_data_seek($rsOptinGroups,0);
	 $row_rsOptinGroups = mysql_fetch_assoc($rsOptinGroups);
}

if(isset($_POST['locationID']) && $_POST['locationID'] != $_POST['defaultaddressID']) { // new location!
	addUserToLocation($_POST['ID'], $_POST['locationID'], $_POST['ID']);
	$_POST['defaultaddressID'] = (isset($_POST['locationID']) && $_POST['locationID'] !="") ? $_POST['locationID'] : $_POST['defaultaddressID'];
}

if(isset($_POST['emailoptinold']) && $_POST['emailoptinold']==1 && !isset($_POST['emailoptin'])) {
	
	$insert = "INSERT INTO groupemailoptoutlog (email, createdbyID, createddatetime) VALUES (".GetSQLValueString($row_rsMe['email'],"text").",".GetSQLValueString($row_rsMe['ID'],"int").",NOW())";
	mysql_query($insert, $aquiescedb) or die(mysql_error());
}

if(isset($_GET['deletelocationuserID'])) {
	$delete = "DELETE FROM locationuser WHERE userID = ".GetSQLValueString($row_rsMe['ID'],"int")." AND ID = ".intval($_GET['deletelocationuserID']);
	// added userID from security
	mysql_query($delete, $aquiescedb) or die(mysql_error());
	if(mysql_affected_rows()>0) {
		$msg = "Location successfully deleted.";
	}
}


if(isset($_POST['x']) && intval($_POST['x'])>0) { // crop
		list($width) = getimagesize(UPLOAD_ROOT.$_POST['imageURL']);
		list($width2) = getimagesize(UPLOAD_ROOT."m_".$_POST['imageURL']);
		$filename = UPLOAD_ROOT.$_POST['imageURL'];
		$original = UPLOAD_ROOT."o_".$_POST['imageURL'];
		if(!is_readable($original)) { 
			copy($filename, $original);
		}
		$ratio  = floatval($width/$width2);
		$w = $ratio * intval($_POST['w']);
		$h = $ratio * intval($_POST['h']);
		$x = $ratio * intval($_POST['x']);
		$y = $ratio * intval($_POST['y']);
		
		$size = $w."x".$h.":".$x.":".$y;;
		$result = Image($filename, "crop", $size, $filename);
		createImageSizes($filename);
} else { // no crop

	$uploaded = getUploads(UPLOAD_ROOT,$image_sizes,"",0,0,"",array("jpg","jpeg","png","gif"));
	
	if (isset($uploaded) && is_array($uploaded) && isset($uploaded["filename"][0]["error"])) { 
		$error = $uploaded["filename"][0]["error"]; 
		unset($_POST["MM_update"]);
	} else if (isset($uploaded) && is_array($uploaded) && isset($uploaded["filename"]) && $uploaded["filename"][0]["newname"]!="") { 
		$_POST['imageURL'] = $uploaded["filename"][0]["newname"]; 
	}
}


$_POST['emailoptin'] = isset($_POST['emailoptin']) ? intval($_POST['emailoptin']) : 0;
$_POST['partneremailoptin'] = isset($_POST['partneremailoptin']) ? 1 : 0;
	
if($row_rsPreferences['emailoptintype'] == 2) { // reverse if opt out is set in prefs		
	$_POST['emailoptin'] = ($_POST['emailoptin']==1) ? 0 : 1;
}
if($row_rsPreferences['partneremailoptintype'] == 2) { // reverse if opt out is set in prefs		
	$_POST['partneremailoptin'] = ($_POST['partneremailoptin']==1) ? 0 : 1;
}

 

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE users SET firstname=%s, surname=%s, dob=%s, email=%s, jobtitle=%s, telephone=%s, aboutme=%s, defaultaddressID=%s, imageURL=%s, twitterID=%s, facebookURL=%s, websiteURL=%s, emailoptin=%s, contactbyphone=%s, contactbypost=%s, showemail=%s, modifieddatetime=%s, modifiedbyID=%s, updateprofile=%s, mobile=%s, emailbounced=%s, partneremailoptin=%s WHERE ID=%s",
                       GetSQLValueString($_POST['firstname'], "text"),
                       GetSQLValueString($_POST['surname'], "text"),
                       GetSQLValueString($_POST['dob'], "date"),
                       GetSQLValueString($_POST['email'], "text"),
                       GetSQLValueString($_POST['jobtitle'], "text"),
                       GetSQLValueString($_POST['telephone'], "text"),
                       GetSQLValueString($_POST['aboutme'], "text"),
                       GetSQLValueString($_POST['defaultaddressID'], "int"),
                       GetSQLValueString($_POST['imageURL'], "text"),
                       GetSQLValueString($_POST['twitterID'], "text"),
                       GetSQLValueString($_POST['facebookURL'], "text"),
                       GetSQLValueString($_POST['websiteURL'], "text"),
                       GetSQLValueString($_POST['emailoptin'], "int"),
                       GetSQLValueString(isset($_POST['contactbyphone']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['contactbypost']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['showemail']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['modifieddatetime'], "date"),
                       GetSQLValueString($_POST['modifiedbyID'], "int"),
                       GetSQLValueString($_POST['updateprofile'], "int"),
                       GetSQLValueString($_POST['mobile'], "text"),
                       GetSQLValueString($_POST['emailbounced'], "int"),
                       GetSQLValueString($_POST['partneremailoptin'], "int"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
}
if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
	
	// check if current email is used as username so if email is changed then we need to update username also...
	if($row_rsPreferences['emailasusername']==1){
		$update = "UPDATE users SET username = ".GetSQLValueString($_POST['email'], "text")." WHERE ID = ".GetSQLValueString($_POST['ID'], "int");
		mysql_query($update, $aquiescedb) or die(mysql_error());
	}

	
	if(isset($_POST['locationuserID'])) { 
		foreach($_POST['locationuserID'] as $locationuserID) {
			$days = ""; 
			if(isset($_POST['daysofweek'][$locationuserID])) {				
				foreach($_POST['daysofweek'][$locationuserID] as $day => $value) {
					$days .= $value;
				}	
				
			}
			$update  = "UPDATE locationuser SET daysofweek = ".GetSQLValueString($days, "text")." WHERE ID = ".intval($locationuserID); 
			mysql_query($update, $aquiescedb) or die(mysql_error());
		}		
	}
	
	if(intval($_POST['addlocationID'])>0) {
		addUserToLocation($_POST['ID'], $_POST['addlocationID'], $_POST['ID']);		
	} else { // not add location
		
	
		if($_POST['email'] != $_POST['oldemail'] && isset($_POST['oldemail']) && $_POST['oldemail'] !="") { // email changed
			$to = $_POST['oldemail'];
			$subject = $site_name." email address update";
			$message = "Hello ".$_POST['firstname'].",\n\n";
			$message .= "This is an automated email to inform you that your main contact email address for ".$site_name." has been updated to: ".$_POST['email']."\n"; 
		$message .= "\nRegards,\n\n";
			$message .= $site_name." Team";
			sendMail($to,$subject,$message);
		
		}
		
		if($row_rsPreferences['userupdatealert']==1) {
			$to = $row_rsPreferences['contactemail'];
			$subject = $site_name." user profile update";
			$message = $_POST['firstname']." ".$_POST['surname']." has updated their profile\n\n";
			$message .= "View their user profile here:\n\n";
			$message .= getProtocol()."://". $_SERVER['HTTP_HOST']."/members/admin/modify_user.php?userID=".intval($_POST['ID']);			
			sendMail($to,$subject,$message);
		}
		
		
		$updateGoTo = isset($_GET['returnURL']) ? addslashes($_GET['returnURL']) : "/members/profile/";
		if (isset($_SERVER['QUERY_STRING']) && !isset($_GET['returnURL'])) {
			$updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
			$updateGoTo .= $_SERVER['QUERY_STRING'];
  		}
  		header(sprintf("Location: %s", $updateGoTo));exit;
	}
}





mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPublicLocations = "SELECT ID, locationname FROM location WHERE `public` = 1 AND location.active = 1 ORDER BY locationname ASC";
$rsPublicLocations = mysql_query($query_rsPublicLocations, $aquiescedb) or die(mysql_error());
$row_rsPublicLocations = mysql_fetch_assoc($rsPublicLocations);
$totalRows_rsPublicLocations = mysql_num_rows($rsPublicLocations);

$varUsername_rsOtherLocations = "-1";
if (isset($_SESSION['MM_Username'])) {
  $varUsername_rsOtherLocations = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsOtherLocations = sprintf("SELECT locationuser.ID, location.locationname, locationuser.daysofweek FROM locationuser LEFT JOIN users ON (users.ID = locationuser.userID) LEFT JOIN location ON (locationuser.locationID = location.ID) WHERE location.`public` = 1 AND location.active = 1 AND users.username = %s ", GetSQLValueString($varUsername_rsOtherLocations, "text"));
$rsOtherLocations = mysql_query($query_rsOtherLocations, $aquiescedb) or die(mysql_error());
$row_rsOtherLocations = mysql_fetch_assoc($rsOtherLocations);
$totalRows_rsOtherLocations = mysql_num_rows($rsOtherLocations);


if(isset($_GET['uncrop'])) {
	copy(UPLOAD_ROOT."o_".$row_rsMe['imageURL'], UPLOAD_ROOT.$row_rsMe['imageURL']);
	
	createImageSizes(UPLOAD_ROOT.$row_rsMe['imageURL']);
	unlink(UPLOAD_ROOT."o_".$row_rsMe['imageURL']);
}

?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Update profile"; echo $pageTitle." | ".$site_name; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" --><script src="/3rdparty/jquery/jquery.jcrop/js/jquery.Jcrop.js"></script>
<script>

 $(function(){ $('.croppable').Jcrop({onSelect: showCoords}); });
 function showCoords(c)
{
	$('#x').val(c.x);
	$('#y').val(c.y);
	$('#x2').val(c.x2);
	$('#y2').val(c.y2);
	$('#w').val(c.w);
	$('#h').val(c.h);
};

function checkOptins() {	
	 z = 0, count = 0; isChecked = 0;
	 theForm = document.getElementById('form1');
	for(z=0; z<theForm.length;z++){
      if(theForm[z].type == 'checkbox' && theForm[z].name.indexOf('optingroup') != -1) {
		  count ++;
		  if(theForm[z].checked) {
		  	isChecked ++;		  
		  }
	  }
    }
	if(false && count>0 && isChecked ==0) { // unused as was for SCIF
		return confirm('You have not checked any newsletter subscriptions. Are you sure you want to continue?');
	} else {
	 
	 return true;
	}
}
 </script><link href="/3rdparty/jquery/jquery.jcrop/css/jquery.Jcrop.css" rel="stylesheet"  />
<script src="/core/scripts/formUpload.js"></script><link href="../css/membersDefault.css" rel="stylesheet"  />
<style>
<!--
.required {
	color:#900;
	display:none;
}
/* the following are always compulsary if shown*/
.fullname .required, .email .required {
display:inline;
}

label {
	display:inline;
}

input {
	width:auto;
}
<?php if($row_rsMe['canchangepassword']==0) {
echo "#linkChangePassword { display: none;";
}
?> <?php if($row_rsPreferences['memberpubliclocation']==0 || $totalRows_rsPublicLocations == 0) {
echo ".publicLocation { display: none; }";
}
?> <?php if($row_rsPreferences['askjobtitleprofile']==0) {
echo ".jobtitle { display: none; }";
}
?><?php if ($row_rsPreferences['askphotoprofile']!=1) {
echo " .profilephoto { display:none; } ";
}if($row_rsPreferences['askfacebookprofile'] != 1) {
?>.facebookURL { display:none; }
<?php
}

if($row_rsPreferences['askfacebookcompulsary'] == 1) {
?>.facebookURL .required { display:inline; }
<?php
}
 if($row_rsPreferences['asktwitterprofile'] != 1) {
?>.twitterID { display:none; }
<?php
}

if($row_rsPreferences['asktwittercompulsary'] == 1) {
?>.twitterID .required { display:inline; }
<?php
}
if($row_rsPreferences['askwebsiteURLprofile'] != 1) {
?>.websiteURL { display:none; }
<?php
}
if($row_rsPreferences['askwebsiteURLcompulsary'] != 1) {
?>.websiteURL .required{ display:inline; }
<?php
}
if($row_rsPreferences['askaboutmeprofile'] != 1) {
?>.aboutme { display:none; }
<?php
}
if($row_rsPreferences['askaboutmecompulsary'] != 1) {
?>.aboutme .required{ display:inline; }
<?php
}
if($row_rsPreferences['askphotoprofile'] != 1) {
?>.profilephoto { display:none; }
<?php
}
if($row_rsPreferences['askphotocompulsary'] != 1) {
?>.profilephoto .required{ display:inline; }
<?php
}
if ($row_rsPreferences['partneremailoptintype']==0) {
	echo ".partneremailoptin { display: none; }";
}
?>
-->
</style>

<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" -->
  <div id="pageUpdateProfile" class="container pageBody members">
    <h1 class="memberheader">Update my profile</h1>
<?php require_once('../../core/includes/alert.inc.php'); ?>
    <form action="<?php echo $editFormAction; ?>" method="post" enctype="multipart/form-data" name="form1" id="form1" class="form-horizontal" >
     <div class="profilephoto form-group">
       <div  class="col-md-3">Picture<span class="required">*</span>:</div>
       <div class="col-md-9"><?php if (isset($row_rsMe['imageURL'])) { ?>
            <img src="<?php echo getImageURL($row_rsMe['imageURL'],"medium"); ?>" class="croppable actualsize" /><br />
           <?php if(is_readable(UPLOAD_ROOT."o_".$row_rsMe['imageURL'])) { ?>This image has been cropped. <a href="update_profile.php?uncrop=true">Uncrop</a>. <?php } ?>Crop image by dragging mouse across and clicking Save.<br /><label><input name="noImage" type="checkbox" value="1" />
            Remove photo</label><input type="hidden" name="x" id="x" />
        <input type="hidden" name="x2" id="x2" />
        <input type="hidden" name="y" id="y" />
        <input type="hidden" name="y2" id="y2" />
        <input type="hidden" name="w" id="w" />
        <input type="hidden" name="h" id="h" />
        <input type="hidden" name="ratio" id="ratio" value="<?php echo floatval($row_rsMe['width']/$image_sizes['medium']['width']); ?>" />
            <?php } else { ?>
            You have not added a picture of yourself yet. Upload one below.
            <?php } ?></div></div><!-- end form-group-->
          <div  class="profilephoto upload form-group">
          <label for="filename" class="col-md-3">Add/update picture:</label>
          <div class="col-md-9"><input name="filename" type="file" class="fileinput" id="filename" size="20" />
            <input name="imageURL" type="hidden" id="imageURL" value="<?php echo $row_rsMe['imageURL']; ?>" /></div></div><!-- end form-group-->
            
        <div class="fullname form-group">
          <label for="firstname" class="col-md-3">First name<span class="required">*</span>:</label>
          <div class="col-md-3">
            <input name="firstname" type="text" id="firstname" value="<?php echo htmlentities($row_rsMe['firstname'], ENT_COMPAT, "UTF-8"); ?>" size="20" maxlength="50" class="form-control" /></div>
          <label for="surname" class="col-md-3">Surname<span class="required">*</span>:</label>
          <div class="col-md-3"><input name="surname" type="text" id="surname" value="<?php echo htmlentities($row_rsMe['surname'], ENT_COMPAT, "UTF-8"); ?>" size="20" maxlength="50" class="form-control"  /></div></div><!-- end form-group -->
        <div class="form-group">
          <label class="col-md-3">Username:</label>
        <div class="col-md-9"><span class="form-control-static"><?php echo htmlentities($row_rsMe['username'], ENT_COMPAT, "UTF-8"); ?></span> <span class="linkChangePassword"><a href="change_password.php" class="btn btn-default btn-secondary">Change password</a></span>&nbsp;&nbsp;&nbsp;<span id="profileUpdateContacts"><a href="contact_addresses.php?userID=<?php echo $row_rsMe['ID']; ?>" class="btn btn-default btn-secondary">Update Contact Details</a></span>
            </div></div><!-- end form-group-->
            
          
         
         <div class="publicLocation form-group">
         <label for="locationID" class="col-md-3">Main location<span class="required">*</span>:</label>
          <div class="col-md-9"><select name="locationID" id="locationID" class="form-control">
            <option value="" <?php if (!(strcmp("", $row_rsMe['defaultaddressID']))) {echo "selected=\"selected\"";} ?>><?php echo isset($region['text_choose']) ? htmlentities($region['text_choose'], ENT_COMPAT, "UTF-8") : "Choose..." ?></option>
            <option value="0" <?php if (!(strcmp("0", $row_rsMe['defaultaddressID']))) {echo "selected=\"selected\"";} ?>>Various locations</option>
            <?php
do {  
?>
            <option value="<?php echo $row_rsPublicLocations['ID']?>"<?php if (!(strcmp($row_rsPublicLocations['ID'], $row_rsMe['defaultaddressID']))) {echo "selected=\"selected\"";} ?>><?php echo htmlentities($row_rsPublicLocations['locationname'], ENT_COMPAT, "UTF-8"); ?></option>
            <?php
} while ($row_rsPublicLocations = mysql_fetch_assoc($rsPublicLocations));
  $rows = mysql_num_rows($rsPublicLocations);
  if($rows > 0) {
      mysql_data_seek($rsPublicLocations, 0);
	  $row_rsPublicLocations = mysql_fetch_assoc($rsPublicLocations);
  }
?>
            </select><input name="defaultaddressID" type="hidden" id="defaultaddressID" value="<?php echo $row_rsMe['defaultaddressID']; ?>" />
            <?php if ($totalRows_rsOtherLocations > 1) { // Show if recordset not empty ?>
  <table  class="listTable">
    
    <?php do { ?>
      <tr>
      
        <td><input name="locationuserID[<?php echo $row_rsOtherLocations['ID']; ?>]" type="hidden" value="<?php echo $row_rsOtherLocations['ID']; ?>" /><?php echo htmlentities($row_rsOtherLocations['locationname'], ENT_COMPAT, "UTF-8"); ?></td>
        <td><label>
          <input type="checkbox" name="daysofweek[<?php echo $row_rsOtherLocations['ID']; ?>][1]" value="1" id="daysofweek_0" <?php  echo strpos($row_rsOtherLocations['daysofweek'],"1") !==false  ? "checked=\"checked\"" : ""; ?> />
          Mo</label>
          &nbsp;&nbsp;
          <label>
            <input type="checkbox" name="daysofweek[<?php echo $row_rsOtherLocations['ID']; ?>][2]" value="2" id="daysofweek_1" <?php  echo strpos($row_rsOtherLocations['daysofweek'],"2") !==false  ? "checked=\"checked\"" : ""; ?> />
            Tu</label>
          &nbsp;&nbsp;
          <label>
            <input type="checkbox" name="daysofweek[<?php echo $row_rsOtherLocations['ID']; ?>][3]" value="3" id="daysofweek_2" <?php  echo strpos($row_rsOtherLocations['daysofweek'],"3") !==false  ? "checked=\"checked\"" : ""; ?> />
            We</label>
          &nbsp;&nbsp;
          <label>
            <input type="checkbox" name="daysofweek[<?php echo $row_rsOtherLocations['ID']; ?>][4]" value="4" id="daysofweek_3" <?php  echo strpos($row_rsOtherLocations['daysofweek'],"4") !==false  ? "checked=\"checked\"" : ""; ?> />
            Th</label>
          &nbsp;&nbsp;
          <label>
            <input type="checkbox" name="daysofweek[<?php echo $row_rsOtherLocations['ID']; ?>][5]" value="5" id="daysofweek_4" <?php  echo strpos($row_rsOtherLocations['daysofweek'],"5") !==false  ? "checked=\"checked\"" : ""; ?> />
            Fr</label>
          &nbsp;&nbsp;
          <label>
            <input type="checkbox" name="daysofweek[<?php echo $row_rsOtherLocations['ID']; ?>][6]" value="6" id="daysofweek_5" <?php  echo strpos($row_rsOtherLocations['daysofweek'],"6") !==false  ? "checked=\"checked\"" : ""; ?> />
            Sa</label>
          &nbsp;&nbsp;
          <label>
            <input type="checkbox" name="daysofweek[<?php echo $row_rsOtherLocations['ID']; ?>][7]" value="7" id="daysofweek_6" <?php  echo strpos($row_rsOtherLocations['daysofweek'],"7") !==false  ? "checked=\"checked\"" : ""; ?> />
            Su</label></td>
        <td><a href="update_profile.php?deletelocationuserID=<?php echo $row_rsOtherLocations['ID']; ?>" class="link_delete" onclick="return confirm('Are you sure you want to delete this location from your profile?');"><i class="glyphicon glyphicon-trash"></i> Delete</a></td>
      </tr>
      <?php } while ($row_rsOtherLocations = mysql_fetch_assoc($rsOtherLocations)); ?>
  </table>
  <?php } // Show if recordset not empty ?>

<div class="addlocation"><select name="addlocationID" id="addlocationID">
            <option value="" >Add another location...</option>
            <?php
do {  
?>
            <option value="<?php echo $row_rsPublicLocations['ID']?>"><?php echo htmlentities($row_rsPublicLocations['locationname'], ENT_COMPAT, "UTF-8"); ?></option>
            <?php
} while ($row_rsPublicLocations = mysql_fetch_assoc($rsPublicLocations));
  $rows = mysql_num_rows($rsPublicLocations);
  if($rows > 0) {
      mysql_data_seek($rsPublicLocations, 0);
	  $row_rsPublicLocations = mysql_fetch_assoc($rsPublicLocations);
  }
?>
            </select><button type="submit" id="addlocationbutton" name="addlocationbutton" >Add</button></div>
            </div></div><!-- end form-group -->
            
            
        <div class="jobtitle form-group">
          <label for="jobtitle" class="col-md-3"><?php echo isset($row_rsPreferences['text_role']) ? htmlentities($row_rsPreferences['text_role'], ENT_COMPAT, "UTF-8") : "Job Title"; ?><span class="required">*</span>:</label>
          <div class="col-md-9"><input name="jobtitle" type="text" id="jobtitle" value="<?php echo htmlentities($row_rsMe['jobtitle'], ENT_COMPAT, "UTF-8"); ?>" size="80" maxlength="75" class="form-control"  /></div>
      </div><!-- end form-group -->
          
          
          
        
        <div class="aboutme form-group">
          <label for="aboutme" class="col-md-3">About me<span class="required">*</span>:</label>
          <div class="col-md-9">
            <textarea name="aboutme"  rows="6" id="aboutme" placeholder="" class="form-control" ><?php echo isset($_REQUEST['aboutme']) ? htmlentities($_REQUEST['aboutme'], ENT_COMPAT, "UTF-8") : htmlentities($row_rsMe['aboutme'], ENT_COMPAT, "UTF-8"); ?></textarea>
            </div></div><!-- end form-control-->
        
        <div  class="dob form-group ">
          <label for="dob" class="col-md-3">Date of birth<span class="required">*</span>:</label>
          <div class="col-md-9"><input name="dob" type="text" id="dob" value="<?php $setvalue = $row_rsMe['dob']; echo $setvalue; $startyear = 1900; $inputname = "dob"; ?>" />
            <?php require('../../core/includes/datetimeinput.inc.php'); ?></div></div><!-- end form-group-->
      
        <h2>Contacting you</h2>
         
         
        <div class="telephone form-group">
          <label for="telephone" class="col-md-3">Direct  work phone<span class="required">*</span>:</label>
          <div class="col-md-9">
            <input name="telephone" type="tel" id="telephone" value="<?php echo isset($_REQUEST['telephone']) ? htmlentities($_REQUEST['telephone'], ENT_COMPAT, "UTF-8") : htmlentities($row_rsMe['telephone'], ENT_COMPAT, "UTF-8"); ?>" size="50" maxlength="50" class="form-control"  /></div></div><!-- end form-group -->
            
        <div class="telephone form-group">
         <label for="mobile" class="col-md-3">Mobile phone:</label>
          <div class="col-md-9"><input name="mobile" type="tel" id="mobile" value="<?php echo isset($_REQUEST['mobile']) ? htmlentities($_REQUEST['mobile'], ENT_COMPAT, "UTF-8") : htmlentities($row_rsMe['mobile'], ENT_COMPAT, "UTF-8"); ?>" size="50" maxlength="50" class="form-control"  /></div></div><!-- end form-group-->
           
        <div class="email form-group">
          <label for="email" class="col-md-3">Email<span class="required">*</span>:</label>
          <div class="col-md-9">
              <input name="email" type="email" multiple id="email" value="<?php echo isset($_REQUEST['email']) ? htmlentities($_REQUEST['email'], ENT_COMPAT, "UTF-8") : htmlentities($row_rsMe['email'], ENT_COMPAT, "UTF-8"); ?>" size="50" maxlength="100" class="form-control"  required />
              <input name="oldemail" type="hidden" value="<?php echo  htmlentities($row_rsMe['email']); ?>"  />
             
             
  <?php if ($totalRows_rsOptinGroups > 0) { // Show if recordset not empty ?>
    <h3> Groups</h3>
   <p><?php echo $row_rsPreferences['groupoptintext']; ?></p>
    <div id="optingroups">
      <?php $lasttype = ""; do { 
	  if(isset($row_rsOptinGroups['grouptype']) && $row_rsOptinGroups['grouptype']!=$lasttype) { 
	  $lasttype = $row_rsOptinGroups['grouptype'];
	  echo "<h3>".$lasttype."</h3>"; } ?>
        <input name="updateoptingroups" type="hidden" value="1" />
        <label>
          <input type="checkbox" name="optingroup[<?php echo $row_rsOptinGroups['ID']; ?>]" <?php if(($row_rsOptinGroups['member'])>0) { echo "checked=\"checked\""; } ?> onclick="if(this.checked &amp;&amp; !document.getElementById('emailoptin').checked) { alert('In order to receive any of these emails you must also check the Allow news updates box below.'); }" />
          &nbsp;<?php echo htmlentities($row_rsOptinGroups['groupname'], ENT_COMPAT, "UTF-8"); ?>&nbsp;&nbsp;&nbsp; </label><?php } while ($row_rsOptinGroups = mysql_fetch_assoc($rsOptinGroups)); ?>
      </div><!-- end optin groups--><?php } // Show if recordset not empty ?>
               <div class="networking form-group">
              <h3>Networking</h3>
              <p><label><input name="showemail" type="checkbox" id="showemail" value="1" <?php if (!(strcmp($row_rsMe['showemail'],1))) {echo "checked=\"checked\"";} ?> />
              Allow other members to contact me though the site</label></p></div>
              <p class="websiteURL facebookURL twitterID">Your social networking/blog/web site links will appear on your profile page if you choose to enter this information:</p></div><!-- end col--></div><!--end form group-->
        
        <div class="websiteURL form-group">
          <label for="websiteURL" class="col-md-3">Web site/blog address<span class="required">*</span>:</label>
          <div class="col-md-9">
            <input name="websiteURL" type="text" id="websiteURL" value="<?php echo htmlentities($row_rsMe['websiteURL'], ENT_COMPAT, "UTF-8"); ?>" size="50" maxlength="50" placeholder="e.g. http://www.mywebsite.com/" class="form-control"  />
</div></div><!-- end form-group -->
        
        <div class="facebookURL form-group">
          <label class="col-md-3" for="facebookURL">Facebook page<span class="required">*</span>:</label>
          <div class="col-md-9">
          <input name="facebookURL" type="text" id="facebookURL" value="<?php echo htmlentities($row_rsMe['facebookURL'], ENT_COMPAT, "UTF-8"); ?>" size="50" maxlength="100" placeholder="e.g. https://www.facebook.com/JoeBloggs" class="form-control"  />
</div></div><!-- end form-control-->

 <div class="twitterID form-group">
      <label class="col-md-3" for="twitterID">Twitter ID<span class="required">*</span>:</label>
      <div class="col-md-9">
              <input name="twitterID" type="text" id="twitterID" value="<?php echo htmlentities($row_rsMe['twitterID'], ENT_COMPAT, "UTF-8"); ?>" size="50" maxlength="50" placeholder="e.g. @JoeBloggs" class="form-control"  /></div></div><!-- end form-group-->
              
              
          <div class="form-group">
          
      <div class="col-md-offset-3 col-md-9"> <h3>Your privacy </h3>
              
  <p class="privacypolicy"> <?php echo $row_rsPreferences['orgname']; ?> takes your privacy seriously and will only contact you   with important notifications. We will <strong>NEVER</strong> disclose your email to anyone else unless you explicitly ask us to.</p>
  <p>
  <?php if($row_rsPreferences['emailoptintype'] == 3) { ?>
   <?php echo $row_rsPreferences['emailoptintext']; ?>: <label><input type="radio" name="emailoptin" value="1" <?php if($row_rsMe['emailoptin']==1) echo "checked" ?>> Yes</label> &nbsp; 
  <label><input type="radio" name="emailoptin" value="0" <?php if($row_rsMe['emailoptin']==0) echo "checked" ?>> No</label> <?php } else { ?>
    <label>
      <input <?php if (($row_rsPreferences['emailoptintype'] == 1 && $row_rsMe['emailoptin']==1) || ($row_rsPreferences['emailoptintype'] == 2 && $row_rsMe['emailoptin']==0)) {echo "checked=\"checked\"";} ?> name="emailoptin" type="checkbox" id="emailoptin" value="1" onclick="if(!this.checked) { return confirm('You will no longer receive any email bulletins you may have subscribed to. However, we might occasionally contact you with essential alerts.'); }" />
     <?php echo $row_rsPreferences['emailoptintext']; ?></label>
     <?php } ?>
    <input name="emailoptinold" type="hidden" id="emailoptinold" value="<?php echo $row_rsMe['emailoptin']; ?>" />
  </p> <p class="partneremailoptin">
    <label>
      <input <?php if (($row_rsPreferences['partneremailoptintype'] == 1 && $row_rsMe['partneremailoptin']==1) || ($row_rsPreferences['partneremailoptintype'] == 2 && $row_rsMe['partneremailoptin']==0)) {echo "checked=\"checked\"";} ?> name="partneremailoptin" type="checkbox" id="partneremailoptin" value="1"  />
     <?php echo $row_rsPreferences['partneremailoptintext']; ?></label>
   
  </p>
  
  
              
              <p> We can also contact you the following ways if you prefer:</p>
              <p>
                <label>
                  <input <?php if (!(strcmp($row_rsMe['contactbyphone'],1))) {echo "checked=\"checked\"";} ?> name="contactbyphone" type="checkbox" id="contactbyphone" value="1" />
                  You can contact me by phone</label>
                <br />
                <label>
                  <input <?php if (!(strcmp($row_rsMe['contactbypost'],1))) {echo "checked=\"checked\"";} ?> name="contactbypost" type="checkbox" id="contactbypost" value="1" />
                  You can contact me by post</label>
               
              </p></div><!--end col--></div><!-- end form-group -->
              
              
        <div class="form-group"><label class="col-md-3">Password for security:</label>
          <div class="col-md-9">
            <input type="password" name="password" class="form-control" size="50" maxlength="50" placeholder="To save changes enter your current password" required   />
         </div></div><!-- end form-group-->
            
            
           <div class="form-group"><div class="col-md-offset-3 col-md-9">
            <button type="submit" onclick="return checkOptins()" class="btn btn-default btn-secondary">Save and <?php echo isset($_GET['returnURL']) ? "continue..." : "view..." ; ?></button> 
            </div></div><!-- end form-group-->
            
            
      <input type="hidden" name="MM_update" value="form1" />
      <input name="ID" type="hidden" id="ID" value="<?php echo $row_rsMe['ID']; ?>" />
      
      <input name="modifiedbyID" type="hidden" id="modifiedbyID" value="<?php echo $row_rsMe['ID']; ?>" />
      <input name="modifieddatetime" type="hidden" id="modifieddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
      <input name="updateprofile" type="hidden" id="updateprofile" value="0" />
      <input name="emailbounced" type="hidden"  value="0" />
    </form>
  </div>

   
    <!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsMe);

mysql_free_result($rsPreferences);

mysql_free_result($rsPublicLocations);

mysql_free_result($rsOtherLocations);

mysql_free_result($rsOptinGroups);
?>
