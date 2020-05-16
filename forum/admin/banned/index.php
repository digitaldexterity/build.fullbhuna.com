<?php require_once('../../../Connections/aquiescedb.php'); ?><?php require_once('../../core/includes/adminAccess.inc.php'); ?>
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
}

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
  $insertSQL = sprintf("INSERT INTO bannedwords (word, createdbyID, createddatetime) VALUES (%s, %s, %s)",
                       GetSQLValueString($_POST['bannedword'], "text"),
                       GetSQLValueString($_POST['createdbyID'], "int"),
                       GetSQLValueString($_POST['createddatetime'], "date"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($insertSQL, $aquiescedb) or die(mysql_error());
}

$colname_rsLoggedIn = "1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

$maxRows_rsBannedWords = 10;
$pageNum_rsBannedWords = 0;
if (isset($_GET['pageNum_rsBannedWords'])) {
  $pageNum_rsBannedWords = $_GET['pageNum_rsBannedWords'];
}
$startRow_rsBannedWords = $pageNum_rsBannedWords * $maxRows_rsBannedWords;

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsBannedWords = "SELECT ID, word FROM bannedwords ORDER BY word ASC";
$query_limit_rsBannedWords = sprintf("%s LIMIT %d, %d", $query_rsBannedWords, $startRow_rsBannedWords, $maxRows_rsBannedWords);
$rsBannedWords = mysql_query($query_limit_rsBannedWords, $aquiescedb) or die(mysql_error());
$row_rsBannedWords = mysql_fetch_assoc($rsBannedWords);

if (isset($_GET['totalRows_rsBannedWords'])) {
  $totalRows_rsBannedWords = $_GET['totalRows_rsBannedWords'];
} else {
  $all_rsBannedWords = mysql_query($query_rsBannedWords);
  $totalRows_rsBannedWords = mysql_num_rows($all_rsBannedWords);
}
$totalPages_rsBannedWords = ceil($totalRows_rsBannedWords/$maxRows_rsBannedWords)-1;

if(isset($_GET['deleteID'])) {
	$delete = "DELETE FROM bannedwords WHERE ID = ".GetSQLValueString($_GET['deleteID'], "int");
	$result = mysql_query($delete, $aquiescedb) or die(mysql_error());
	header("location: index.php"); exit;
}
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Manage Banned Words"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
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
      <h1><i class="glyphicon glyphicon-comment"></i> Banned Words</h1>
      <p>You can add words that you wish to ban from user submitted forms. </p>
      <p>The user will be prevented from submitting a form that contains any of the following words:</p>
      <?php if ($totalRows_rsBannedWords == 0) { // Show if recordset empty ?>
        <p>There are currently no banned words in the system.</p>
        <?php } // Show if recordset empty ?><form action="<?php echo $editFormAction; ?>" method="POST" name="form1" id="form1" class="form-inline">
        <label>Add word:
          <input name="bannedword" type="text"  id="bannedword" class="form-inline" />
        </label>
        <button type="submit" name="addbutton" id="addbutton" class="btn btn-default btn-secondary" >Add</button>
        <input name="createdbyID" type="hidden" id="createdbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
        <input name="createddatetime" type="hidden" id="createddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
        <input type="hidden" name="MM_insert" value="form1" />
      </form> <p>&nbsp;</p>
      <?php if ($totalRows_rsBannedWords > 0) { // Show if recordset not empty ?>
  <table class="form-table">
        <?php do { ?>
      <tr>
        <td><?php echo $row_rsBannedWords['word']; ?></td>
        <td><a href="index.php?deleteID=<?php echo $row_rsBannedWords['ID']; ?>"><i class="glyphicon glyphicon-trash"></i> Delete</a></td>
      </tr>
      <?php } while ($row_rsBannedWords = mysql_fetch_assoc($rsBannedWords)); ?>
  </table>
  <?php } // Show if recordset not empty ?>

     
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsBannedWords);
?>
