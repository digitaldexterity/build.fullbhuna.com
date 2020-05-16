<?php require_once('../../../Connections/aquiescedb.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "10";
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
  $updateSQL = sprintf("UPDATE changerequest SET requesttypeID=%s, requestdetails=%s, developernotes=%s, modifeddatetime=%s, modifiedbyID=%s, statusID=%s WHERE ID=%s",
                       GetSQLValueString($_POST['requesttypeID'], "int"),
                       GetSQLValueString($_POST['requestdetails'], "text"),
                       GetSQLValueString($_POST['developernotes'], "text"),
                       GetSQLValueString($_POST['modifeddatetime'], "date"),
                       GetSQLValueString($_POST['modifiedbyID'], "int"),
                       GetSQLValueString($_POST['statusID'], "int"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
	if(isset($_POST['emailposter'])) { 
		$to = $_POST['email'];
		$from = $_POST['developeremail'];
		$friendlyfrom = $_POST['developername'];
		$subject = "Change request actioned";		
		$message = "Automated message to let you know your change request has been actioned.";
		$message .="\n\nYour change request:\n\n";
		$message .= $_POST['requestdetails'];
		$message .= $_POST['developernotes'] != "" ? "\n\n".$_POST['developername']." has written the following notes:\n\n".$_POST['developernotes'] : "" ;
		require_once('../../mail/includes/sendmail.inc.php');
		sendMail($to,$subject,$message,$from,$friendlyfrom);
	}
	$updateGoTo = "index.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo));exit;
}

$colname_rsChangeRequest = "-1";
if (isset($_GET['requestID'])) {
  $colname_rsChangeRequest = $_GET['requestID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsChangeRequest = sprintf("SELECT changerequest.*, CONCAT(users.firstname, ' ',users.surname) AS postedby, users.email FROM changerequest LEFT JOIN users ON (changerequest.createdbyID = users.ID) WHERE changerequest.ID = %s", GetSQLValueString($colname_rsChangeRequest, "int"));
$rsChangeRequest = mysql_query($query_rsChangeRequest, $aquiescedb) or die(mysql_error());
$row_rsChangeRequest = mysql_fetch_assoc($rsChangeRequest);
$totalRows_rsChangeRequest = mysql_num_rows($rsChangeRequest);

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID, CONCAT(users.firstname, ' ',users.surname) AS developername, users.email FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php echo $site_name; ?> <?php echo $admin_name; ?> - Update Change Request</title>
<!-- InstanceEndEditable -->
<?php require_once('../../seo/includes/seo.inc.php'); ?>
<?php require_once('../../includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" -->
        <div class="page forum">
  <h1>Update Change Request  </h1>
  <form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1">
    <table class="form-table"> <tr>
        <td class="text-nowrap text-right">Request type:</td>
        <td><label><input type="radio" name="requesttypeID" value="1" <?php if (!(strcmp(htmlentities($row_rsChangeRequest['requesttypeID'], ENT_COMPAT, 'UTF-8'),1))) {echo "checked=\"checked\"";} ?> />
              Bug</label><label><input type="radio" name="requesttypeID" value="2" <?php if (!(strcmp(htmlentities($row_rsChangeRequest['requesttypeID'], ENT_COMPAT, 'UTF-8'),2))) {echo "checked=\"checked\"";} ?> />
              Change</label><label><input type="radio" name="requesttypeID" value="3" <?php if (!(strcmp(htmlentities($row_rsChangeRequest['requesttypeID'], ENT_COMPAT, 'UTF-8'),3))) {echo "checked=\"checked\"";} ?> />
              New feature</label></td>
      </tr> <tr>
        <td class="text-nowrap text-right">Page:</td>
        <td><?php echo $row_rsChangeRequest['pagetitle']; ?></td>
      </tr> <tr>
          <td class="text-nowrap text-right">URL:</td>
          <td><a href="<?php echo $row_rsChangeRequest['../../../requests/admin/URL']; ?>" target="_blank" rel="noopener"><?php echo $row_rsChangeRequest['URL']; ?></a></td>
        </tr> <tr>
        <td class="text-nowrap text-right top">Request details:</td>
        <td><textarea name="requestdetails" cols="50" rows="5"><?php echo htmlentities($row_rsChangeRequest['requestdetails'], ENT_COMPAT, 'UTF-8'); ?></textarea></td>
      </tr> <tr>
        <td class="text-nowrap text-right">Posted by:</td>
        <td><?php echo $row_rsChangeRequest['postedby']; ?>, <?php echo $row_rsChangeRequest['createddatetime']; ?></td>
      </tr> <tr>
        <td class="text-nowrap text-right">Client:</td>
        <td><?php echo $row_rsChangeRequest['hostsystem']; ?> <?php echo $row_rsChangeRequest['ip4address']; ?></td>
      </tr><tr>
        <td class="text-nowrap text-right">Status:</td>
        <td><select name="statusID">
          <option value="0" <?php if (!(strcmp(0, htmlentities($row_rsChangeRequest['statusID'], ENT_COMPAT, 'UTF-8')))) {echo "SELECTED";} ?>>Pending</option>
          <option value="1" <?php if (!(strcmp(1, htmlentities($row_rsChangeRequest['statusID'], ENT_COMPAT, 'UTF-8')))) {echo "SELECTED";} ?>>Implemented</option>
          <option value="2" <?php if (!(strcmp(2, htmlentities($row_rsChangeRequest['statusID'], ENT_COMPAT, 'UTF-8')))) {echo "SELECTED";} ?>>Not implemented</option>
          </select></td>
        </tr> <tr>
        <td class="text-nowrap text-right top">Developer notes:</td>
        <td><label>
          <textarea name="developernotes" id="developernotes" cols="50" rows="5"><?php echo $row_rsChangeRequest['developernotes']; ?></textarea>
        </label></td>
      </tr>
      
      <?php if($row_rsChangeRequest['email']!="") { ?> <tr>
        <td class="text-nowrap text-right">Email poster:</td>
        <td><input type="checkbox" name="emailposter" id="emailposter" value="1" /><input name="email" type="email" multiple id="email" value="<?php echo $row_rsChangeRequest['email']; ?>" size="50" maxlength="100" />
          <input name="developername" type="hidden" id="developername" value="<?php echo $row_rsLoggedIn['developername']; ?>" />
          <input name="developeremail" type="hidden" id="developeremail" value="<?php echo $row_rsLoggedIn['email']; ?>" /></td>
      </tr><?php } ?> <tr>
        <td class="text-nowrap text-right">&nbsp;</td>
        <td><button type="submit" class="btn btn-primary">Save changes</button></td>
      </tr>
    </table>
    <input type="hidden" name="ID" value="<?php echo $row_rsChangeRequest['ID']; ?>" />
    <input type="hidden" name="modifeddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
    <input type="hidden" name="modifiedbyID" value="<?php echo htmlentities($row_rsLoggedIn['ID']); ?>" />
    <input type="hidden" name="MM_update" value="form1" />
    <input type="hidden" name="ID" value="<?php echo $row_rsChangeRequest['ID']; ?>" />
  </form>
 </div>
  <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsChangeRequest);

mysql_free_result($rsLoggedIn);
?>
