<?php require_once('../../../Connections/aquiescedb.php'); ?><?php require_once('../../../core/includes/adminAccess.inc.php'); ?><?php require_once('../../../members/includes/userfunctions.inc.php'); ?>
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

if(isset($_POST['clientID']) && $_POST['clientID'] == 0 && trim($_POST['firstname'])!="") {
	$_POST['clientID'] = createNewUser($_POST['firstname'],$_POST['surname']);
	addUserToDirectory($_POST['clientID'], $_POST['directoryID'], $_POST['createdbyID']);
	addUserToLocation($_POST['clientID'], $_POST['locationID'], $_POST['createdbyID']);
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
  $insertSQL = sprintf("INSERT INTO communication (commtypeID, commcatID, incoming, userID, clientID, locationID, directoryID, orderID, notes, thiscommdatetime, nextcommdatetime, createdbyID, createddatetime, modifiedbyID, modifieddatetime, statusID, followupuserID) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['commtypeID'], "int"),
                       GetSQLValueString($_POST['communicationcatID'], "int"),
                       GetSQLValueString($_POST['incoming'], "int"),
                       GetSQLValueString($_POST['userID'], "int"),
                       GetSQLValueString($_POST['clientID'], "int"),
                       GetSQLValueString($_POST['locationID'], "int"),
                       GetSQLValueString($_POST['directoryID'], "int"),
                       GetSQLValueString($_POST['orderID'], "int"),
                       GetSQLValueString($_POST['notes'], "text"),
                       GetSQLValueString($_POST['thiscommdatetime'], "date"),
                       GetSQLValueString($_POST['nextcommdatetime'], "date"),
                       GetSQLValueString($_POST['createdbyID'], "int"),
                       GetSQLValueString($_POST['createddatetime'], "date"),
                       GetSQLValueString($_POST['modifiedbyID'], "int"),
                       GetSQLValueString($_POST['modifieddatetime'], "date"),
                       GetSQLValueString($_POST['statusID'], "int"),
                       GetSQLValueString($_POST['followupuserID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($insertSQL, $aquiescedb) or die(mysql_error());
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
	if(isset($_POST['followupID']) && intval($_POST['followupID']) >0) {
		$update = "UPDATE communication SET nextcommID = ".mysql_insert_id()." WHERE ID = ".intval($_POST['followupID']);
		$result = mysql_query($update, $aquiescedb) or die(mysql_error());
	}
  $insertGoTo = isset($_GET['returnURL']) ? $_GET['returnURL'] : "index.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
    $insertGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $insertGoTo)); exit;
}

$colname_rsFollowup = "-1";
if (isset($_GET['followupID'])) {
  $colname_rsFollowup = $_GET['followupID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsFollowup = sprintf("SELECT * FROM communication WHERE ID = %s", GetSQLValueString($colname_rsFollowup, "int"));
$rsFollowup = mysql_query($query_rsFollowup, $aquiescedb) or die(mysql_error());
$row_rsFollowup = mysql_fetch_assoc($rsFollowup);
$totalRows_rsFollowup = mysql_num_rows($rsFollowup);

$_GET['clientID'] = isset($_GET['clientID']) ? intval($_GET['clientID']) : $row_rsFollowup['clientID'];
$_GET['orderID'] = isset($_GET['orderID']) ? intval($_GET['orderID']) : $row_rsFollowup['orderID'];
$_GET['locationID'] = isset($_GET['locationID']) ? intval($_GET['locationID']) : $row_rsFollowup['locationID'];
$_GET['directoryID'] = isset($_GET['directoryID']) ? intval($_GET['directoryID']) : $row_rsFollowup['directoryID'];


mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsCommType = "SELECT ID, typename FROM communicationtype WHERE statusID = 1  ORDER BY communicationtype.ordernum";
$rsCommType = mysql_query($query_rsCommType, $aquiescedb) or die(mysql_error());
$row_rsCommType = mysql_fetch_assoc($rsCommType);
$totalRows_rsCommType = mysql_num_rows($rsCommType);

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

$varDirectoryID_rsThisDirectory = "-1";
if (isset($_GET['directoryID'])) {
  $varDirectoryID_rsThisDirectory = $_GET['directoryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsThisDirectory = sprintf("SELECT directory.name FROM directory WHERE directory.ID = %s", GetSQLValueString($varDirectoryID_rsThisDirectory, "int"));
$rsThisDirectory = mysql_query($query_rsThisDirectory, $aquiescedb) or die(mysql_error());
$row_rsThisDirectory = mysql_fetch_assoc($rsThisDirectory);
$totalRows_rsThisDirectory = mysql_num_rows($rsThisDirectory);

$colname_rsThisClient = "-1";
if (isset($_GET['clientID'])) {
  $colname_rsThisClient = $_GET['clientID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsThisClient = sprintf("SELECT firstname, surname FROM users WHERE ID = %s", GetSQLValueString($colname_rsThisClient, "int"));
$rsThisClient = mysql_query($query_rsThisClient, $aquiescedb) or die(mysql_error());
$row_rsThisClient = mysql_fetch_assoc($rsThisClient);
$totalRows_rsThisClient = mysql_num_rows($rsThisClient);

$varDirectoryID_rsDirectoryUsers = "-1";
if (isset($_GET['directoryID'])) {
  $varDirectoryID_rsDirectoryUsers = $_GET['directoryID'];
}
$varLocationID_rsDirectoryUsers = "-1";
if (isset($_GET['locationID'])) {
  $varLocationID_rsDirectoryUsers = $_GET['locationID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsDirectoryUsers = sprintf("(SELECT users.ID, users.firstname, users.surname FROM users LEFT JOIN directoryuser ON (directoryuser.userID = users.ID) WHERE directoryuser.directoryId = %s) UNION (SELECT users.ID, users.firstname, users.surname FROM users LEFT JOIN locationuser ON (locationuser.userID = users.ID) WHERE locationuser.locationID = %s) ", GetSQLValueString($varDirectoryID_rsDirectoryUsers, "int"),GetSQLValueString($varLocationID_rsDirectoryUsers, "int"));
$rsDirectoryUsers = mysql_query($query_rsDirectoryUsers, $aquiescedb) or die(mysql_error());
$row_rsDirectoryUsers = mysql_fetch_assoc($rsDirectoryUsers);
$totalRows_rsDirectoryUsers = mysql_num_rows($rsDirectoryUsers);

$colname_rsThisLocation = "-1";
if (isset($_GET['locationID'])) {
  $colname_rsThisLocation = $_GET['locationID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsThisLocation = sprintf("SELECT locationname, address1, address2, address3 FROM location WHERE ID = %s", GetSQLValueString($colname_rsThisLocation, "int"));
$rsThisLocation = mysql_query($query_rsThisLocation, $aquiescedb) or die(mysql_error());
$row_rsThisLocation = mysql_fetch_assoc($rsThisLocation);
$totalRows_rsThisLocation = mysql_num_rows($rsThisLocation);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsStaff = "SELECT ID, firstname, surname FROM users WHERE usertypeID >= 7 ORDER BY surname ASC";
$rsStaff = mysql_query($query_rsStaff, $aquiescedb) or die(mysql_error());
$row_rsStaff = mysql_fetch_assoc($rsStaff);
$totalRows_rsStaff = mysql_num_rows($rsStaff);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsCategories = "SELECT ID, categoryname FROM communicationcategory ORDER BY categoryname ASC";
$rsCategories = mysql_query($query_rsCategories, $aquiescedb) or die(mysql_error());
$row_rsCategories = mysql_fetch_assoc($rsCategories);
$totalRows_rsCategories = mysql_num_rows($rsCategories);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsAccounts = "SELECT COUNT(ID) AS numaccounts FROM mailaccount WHERE statusID = 1";
$rsAccounts = mysql_query($query_rsAccounts, $aquiescedb) or die(mysql_error());
$row_rsAccounts = mysql_fetch_assoc($rsAccounts);
$totalRows_rsAccounts = mysql_num_rows($rsAccounts);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsRecentMail = "SELECT ID, subject, createddatetime, correspondence.recipient, correspondence.sender, correspondence.sendername FROM correspondence  WHERE createddatetime >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH) ORDER BY createddatetime DESC LIMIT 50";
$rsRecentMail = mysql_query($query_rsRecentMail, $aquiescedb) or die(mysql_error());
$row_rsRecentMail = mysql_fetch_assoc($rsRecentMail);
$totalRows_rsRecentMail = mysql_num_rows($rsRecentMail);


?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Add Note"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" --><script src="/core/scripts/date-picker/js/datepicker.js"></script>
<style><!--
<?php
echo "#followupdate { display:none; }";
 if ($totalRows_rsCommType==0) { 
 echo ".communicationtype { display:none; }";
}

if ($totalRows_rsCategories==0) { 
 echo ".communicationcategory { display:none; }";
} 

if ($totalRows_rsRecentMail==0) { 
 echo ".correspondence { display:none; }";
}

?>
--></style>
<script src="../../../SpryAssets/SpryValidationRadio.js"></script>
<script>
addListener("load", init);
function init() { 
	var url = "/mail/admin/communication/includes/notes.inc.php?clientID=<?php echo intval($_GET['clientID']); ?>&directoryID=<?php echo intval($_GET['directoryID']); ?>&locationID=<?php echo intval($_GET['locationID']); ?>&orderID=<?php echo intval($_GET['orderID']); ?>";
	getData(url,"noteslist");
	toggleNewClient();
	addListener("change",toggleNewClient,document.getElementById('clientID'));
}

function toggleFollowUp() {
	if(getRadioValue("followup") == 1) {
		document.getElementById('followupdate').style.display = "inline";
	} else {
		document.getElementById('followupdate').style.display = "none";
		document.getElementById('nextcommdatetime').value = "";
		document.getElementById('dd-nextcommdatetime').value = "--";
		document.getElementById('mm-nextcommdatetime').value = "--";
		document.getElementById('yy-nextcommdatetime').value = "--";
	}
}

function toggleNewClient() { 
	if(document.getElementById('clientID').value=="0") {
		document.getElementById('clientname').style.display = 'inline';
	} else {
		document.getElementById('clientname').style.display = 'none';
	}
}

function insertCorrensponence(correspondenceID) { 
	getData("/mail/admin/communication/includes/getCorrespondence.ajax.php?correspondenceID="+correspondenceID,"clipboard","clipboard","",function(){
		currentNotes = document.getElementById('notes').value;
	document.getElementById('notes').value = currentNotes + document.getElementById('clipboard').innerHTML; });
	
}
</script>
<link href="../../../SpryAssets/SpryValidationRadio.css" rel="stylesheet" type="text/css" />
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
    <!-- InstanceBeginEditable name="Body" -->
   <div class="page forum">
    <form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1"> <h1><i class="glyphicon glyphicon-envelope"></i> Add <?php echo (isset($_GET['followupID'])) ? "Follow-up" : "Note"; ?></h1>
    <?php if($_GET['clientID']!="" || $_GET['locationID']!="" || $_GET['directoryID']!="" ||$_GET['orderID']!="" ) { // have relation ?>
    <h2><?php echo trim($row_rsThisDirectory['name']." - " .$row_rsThisLocation['locationname']." - ".$row_rsThisClient['firstname']. " ".$row_rsThisClient['surname']," -"); ?> <?php echo isset($row_rsThisOrder['or_orderID']) ? " (".$row_rsThisOrder['or_orderID'].")" : ""; ?></h2>
  <?php echo isset($row_rsThisLocation['address1']) ? "<h3>".$row_rsThisLocation['address1']." ".$row_rsThisLocation['address2']." ".$row_rsThisLocation['address3']."</h3>" : ""; ?>
      <table class="form-table"> <tr>
          <td class="text-nowrap text-right">Note made:</td>
          <td><input type="hidden" name="thiscommdatetime" id="thiscommdatetime" value="<?php $setvalue = date('Y-m-d H:i:s'); echo $setvalue; ?>" class='highlight-days-67 split-date format-y-m-d divider-dash' />
          <?php $inputname = "thiscommdatetime"; $time = true; require('../../../core/includes/datetimeinput.inc.php'); ?></td>
        </tr>
        <tr class="communicationcategory">
          <td class="text-nowrap text-right"><label for="communicationcatID">Category:</label></td>
          <td><select name="communicationcatID" id="communicationcatID" class="form-control" >
              <option value=""><?php echo isset($region['text_choose']) ? htmlentities($region['text_choose'], ENT_COMPAT, "UTF-8") : "Choose..." ?></option>
              <?php
do {  
?>
              <option value="<?php echo $row_rsCategories['ID']?>"><?php echo $row_rsCategories['categoryname']?></option>
              <?php
} while ($row_rsCategories = mysql_fetch_assoc($rsCategories));
  $rows = mysql_num_rows($rsCategories);
  if($rows > 0) {
      mysql_data_seek($rsCategories, 0);
	  $row_rsCategories = mysql_fetch_assoc($rsCategories);
  }
?>
          </select></td>
        </tr>
        <tr class="communicationtype">
          <td class="text-nowrap text-right">Type:</td>
          <td><select name="commtypeID"  class="form-control">
            <?php 
do {  
?>
            <option value="<?php echo $row_rsCommType['ID']?>" ><?php echo $row_rsCommType['typename']?></option>
            <?php
} while ($row_rsCommType = mysql_fetch_assoc($rsCommType));
?>
          </select>&nbsp;&nbsp;
            
          </td>
        </tr> <tr>
          <td class="text-nowrap text-right"><label for="clientID">With:</label></td>
          <td class="form-inline"><?php if ($totalRows_rsDirectoryUsers>0) { ?>
            <?php echo $row_rsThisClient['firstname']; ?>
            <select name="clientID" id="clientID"  class="form-control">
              <option value="" <?php if (!(strcmp("", $_GET['clientID']))) {echo "selected=\"selected\"";} ?>><?php echo isset($region['text_choose']) ? htmlentities($region['text_choose'], ENT_COMPAT, "UTF-8") : "Choose..." ?></option>
               <option value="0" <?php if (!(strcmp(0, $_GET['clientID']))) {echo "selected=\"selected\"";} ?>>Not listed</option>
              <?php
do {  
?>
              <option value="<?php echo $row_rsDirectoryUsers['ID']?>"<?php if (!(strcmp($row_rsDirectoryUsers['ID'], @$_GET['clientID']))) { echo "selected=\"selected\"";} ?>><?php echo trim($row_rsDirectoryUsers['firstname']." ".$row_rsDirectoryUsers['surname']); ?></option>
              <?php
} while ($row_rsDirectoryUsers = mysql_fetch_assoc($rsDirectoryUsers));
  $rows = mysql_num_rows($rsDirectoryUsers);
  if($rows > 0) {
      mysql_data_seek($rsDirectoryUsers, 0);
	  $row_rsDirectoryUsers = mysql_fetch_assoc($rsDirectoryUsers);
  }
?>
            </select>
          <?php } else { ?> <input type="hidden" name="clientID" id="clientID" value="<?php echo isset($_GET['clientID']) ? intval($_GET['clientID']) : 0; ?>" />
          <?php echo $row_rsThisClient['surname']; ?><?php } ?><span id="clientname">&nbsp;&nbsp;<label>First name:<input name="firstname" type="text" size="10" maxlength="20"  class="form-control"/></label>&nbsp;<label>Surname:<input name="surname" type="text" size="10" maxlength="50"  class="form-control"/></label></span><label>
              <input type="radio" name="incoming" value="1" id="incoming_0" />
              Incoming</label>&nbsp;&nbsp;
           
            <label>
              <input type="radio" name="incoming" value="0" id="incoming_1" />
              Outgoing</label></td>
        </tr>
        
        <tr> </tr> <tr>
          <td class="text-nowrap text-right top">Notes:</td>
          <td><textarea name="notes" id="notes"  cols="80" rows="8"  class="form-control"></textarea></td>
        </tr>
        <tr class="correspondence">
          <td class="text-nowrap text-right top"><label for="correspondenceID">Insert:</label></td>
          <td>
            <select name="correspondenceID" id="correspondenceID" onchange="insertCorrensponence(this.value)"  class="form-control">
              <option value="">Select recent correspondence...</option>
              <?php
do {  
?>
              <option value="<?php echo $row_rsRecentMail['ID']?>"><?php echo date('d/m/y H:i', strtotime($row_rsRecentMail['createddatetime'])); ?> <?php echo substr($row_rsRecentMail['sendername'],0,20); ?>-&gt;<?php echo substr($row_rsRecentMail['recipient'],0,20); ?> <?php echo substr($row_rsRecentMail['subject'],0,30);?></option>
              <?php
} while ($row_rsRecentMail = mysql_fetch_assoc($rsRecentMail));
  $rows = mysql_num_rows($rsRecentMail);
  if($rows > 0) {
      mysql_data_seek($rsRecentMail, 0);
	  $row_rsRecentMail = mysql_fetch_assoc($rsRecentMail);
  }
?>
          </select>
         <div id="clipboard" style="display:none"></div></td>
        </tr> <tr>
          <td class="text-nowrap text-right">Follow-up:</td>
          <td class="form-inline"><span id="spryradio1">
             <label>
              <input name="followup" type="radio" id="followup_1" onclick="toggleFollowUp()" value="0" checked="checked" />
              No</label>&nbsp;&nbsp;<label>
              <input type="radio" name="followup" value="1" id="followup_0" onclick="toggleFollowUp()"/>
              Yes</label>
            &nbsp;&nbsp;
           
          
            <span class="radioRequiredMsg">Please make a selection.</span></span>
            <span id="followupdate"><label>When?<input type="hidden" name="nextcommdatetime" id="nextcommdatetime" class='highlight-days-67 split-date format-y-m-d divider-dash' value="<?php $setvalue = ""; ?>" /></label>
          <?php $inputname = "nextcommdatetime"; require('../../../core/includes/datetimeinput.inc.php'); ?> <label>by 
          
            <select name="followupuserID" id="followupuserID"  class="form-control">
              <option value="" <?php if (!(strcmp("", $row_rsLoggedIn['ID']))) {echo "selected=\"selected\"";} ?>>Not specified</option>
              <?php do {  ?>

              <option value="<?php echo $row_rsStaff['ID']?>" <?php if (!(strcmp($row_rsStaff['ID'], $row_rsLoggedIn['ID']))) { echo "selected=\"selected\"";} ?>>
              <?php echo $row_rsStaff['firstname']." ".$row_rsStaff['surname']; ?>
              </option>
              <?php
} while ($row_rsStaff = mysql_fetch_assoc($rsStaff));
  $rows = mysql_num_rows($rsStaff);
  if($rows > 0) {
      mysql_data_seek($rsStaff, 0);
	  $row_rsStaff = mysql_fetch_assoc($rsStaff);
  }
?>

            </select>
          </label></span></td>
        </tr> <tr>
          <td class="text-nowrap text-right">&nbsp;</td>
          <td><button type="submit" class="btn btn-primary">Add note...</button></td>
        </tr>
      </table>
     
      <input type="hidden" name="directoryID" value="<?php echo isset($_GET['directoryID']) ? htmlentities($_GET['directoryID']) : ""; ?>" />
      <input type="hidden" name="orderID" value="<?php echo isset($_GET['orderID']) ? htmlentities($_GET['orderID']) : ""; ?>" />
      <input type="hidden" name="userID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
      <input type="hidden" name="createdbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
      <input type="hidden" name="createddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
      <input type="hidden" name="modifiedbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
      <input type="hidden" name="modifieddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
      <input type="hidden" name="statusID" value="1" />
      <input type="hidden" name="MM_insert" value="form1" />
      <input type="hidden" name="followupID" id="followupID" value="<?php  echo isset($_GET['followupID']) ? htmlentities($_GET['followupID']) : ""; ?>" />
      <input name="locationID" type="hidden" id="locationID" value="<?php echo isset($_GET['locationID']) ? htmlentities($_GET['locationID']) : ""; ?>" />
   
   
    <?php } else { // no relation ?>
    <p>The system can not add an activity with nothing to relate it to, e.g. user, location, company or order.</p>
    <?php } ?>
    </form>
    <div id="noteslist">
    </div>
<script>
<!--
var spryradio1 = new Spry.Widget.ValidationRadio("spryradio1");
//-->
    </script></div>
  <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsCommType);

mysql_free_result($rsLoggedIn);

mysql_free_result($rsThisDirectory);

mysql_free_result($rsThisClient);

mysql_free_result($rsDirectoryUsers);

mysql_free_result($rsThisLocation);

mysql_free_result($rsStaff);

mysql_free_result($rsCategories);

mysql_free_result($rsAccounts);

mysql_free_result($rsRecentMail);

mysql_free_result($rsFollowup);
?>
