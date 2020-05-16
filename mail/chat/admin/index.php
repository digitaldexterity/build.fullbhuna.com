<?php require_once('../../../Connections/aquiescedb.php'); ?>
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

$MM_restrictGoTo = "/login/index.php?notloggedin=true&alert=".urlencode("You need to be logged in as an Administrator to access this page.");
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

mysql_select_db($database_aquiescedb, $aquiescedb);
$select = "SELECT * FROM chatprefs";
$result = mysql_query($select, $aquiescedb) or die(mysql_error());
	if(mysql_num_rows($result)==0) {
		$insert = "INSERT INTO chatprefs (ID) VALUES (1);";
		mysql_query($insert, $aquiescedb) or die(mysql_error());
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
  $updateSQL = sprintf("UPDATE chatprefs SET statusID=%s WHERE ID=%s",
                       GetSQLValueString($_POST['statusID'], "int"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
}

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsChatPrefs = "SELECT * FROM chatprefs";
$rsChatPrefs = mysql_query($query_rsChatPrefs, $aquiescedb) or die(mysql_error());
$row_rsChatPrefs = mysql_fetch_assoc($rsChatPrefs);
$totalRows_rsChatPrefs = mysql_num_rows($rsChatPrefs);
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Manage Chat"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
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
    <h1>Manage Chat</h1>
    <form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1">
      <h2>Status: </h2>
      <p>
        <label>
          <input <?php if (!(strcmp($row_rsChatPrefs['statusID'],"0"))) {echo "checked=\"checked\"";} ?> type="radio" name="statusID" value="0" id="statusID_0" />
          Off</label>
        <br />
        <label>
          <input <?php if (!(strcmp($row_rsChatPrefs['statusID'],"1"))) {echo "checked=\"checked\"";} ?> type="radio" name="statusID" value="1" id="statusID_1" />
          On - chat to staff only</label>
        <br />
        <label>
          <input <?php if (!(strcmp($row_rsChatPrefs['statusID'],"2"))) {echo "checked=\"checked\"";} ?> type="radio" name="statusID" value="2" id="statusID_2" />
          On - chat to friends only</label>
           <br />
        <label>
          <input <?php if (!(strcmp($row_rsChatPrefs['statusID'],"3"))) {echo "checked=\"checked\"";} ?> type="radio" name="statusID" value="3" id="statusID_3" />
          On - chat to all</label>
      members</p>
      <p>
        <input type="submit" name="button" id="button" value="Save changes" />
        <input name="ID" type="hidden" id="ID" value="1" />
        <br />
      </p>
      <input type="hidden" name="MM_update" value="form1" />
    </form>
    <p>&nbsp;</p>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsChatPrefs);
?>
