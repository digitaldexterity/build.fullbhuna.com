<?php require_once('../../core/includes/sslcheck.inc.php'); ?>
<?php require_once('../../Connections/aquiescedb.php'); ?><?php
if (!isset($_SESSION)) {
  session_start();
}
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

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "addresses")) {
  $updateSQL = sprintf("UPDATE users SET defaultaddressID=%s WHERE ID=%s",
                       GetSQLValueString($_POST['defaultaddress'], "int"),
                       GetSQLValueString($_POST['userID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
}

$maxRows_rsAddresses = 10;
$pageNum_rsAddresses = 0;
if (isset($_GET['pageNum_rsAddresses'])) {
  $pageNum_rsAddresses = $_GET['pageNum_rsAddresses'];
}
$startRow_rsAddresses = $pageNum_rsAddresses * $maxRows_rsAddresses;

$varUsername_rsAddresses = "-1";
if (isset($_SESSION['MM_Username'])) {
  $varUsername_rsAddresses = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsAddresses = sprintf("SELECT location.ID, location.locationname, location.address1, location.postcode FROM location, users WHERE location.active = 1 AND users.username = %s   AND location.userID = users.ID", GetSQLValueString($varUsername_rsAddresses, "text"));
$query_limit_rsAddresses = sprintf("%s LIMIT %d, %d", $query_rsAddresses, $startRow_rsAddresses, $maxRows_rsAddresses);
$rsAddresses = mysql_query($query_limit_rsAddresses, $aquiescedb) or die(mysql_error());
$row_rsAddresses = mysql_fetch_assoc($rsAddresses);

if (isset($_GET['totalRows_rsAddresses'])) {
  $totalRows_rsAddresses = $_GET['totalRows_rsAddresses'];
} else {
  $all_rsAddresses = mysql_query($query_rsAddresses);
  $totalRows_rsAddresses = mysql_num_rows($all_rsAddresses);
}
$totalPages_rsAddresses = ceil($totalRows_rsAddresses/$maxRows_rsAddresses)-1;

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = (get_magic_quotes_gpc()) ? $_SESSION['MM_Username'] : addslashes($_SESSION['MM_Username']);
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID, users.defaultaddressID FROM users WHERE username = '%s'", $colname_rsLoggedIn);
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);
?><?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "My Addresses"; echo $pageTitle." | ".$site_name; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" --><div class="members container pageBody">
           <h1>My Address Book </h1>
              <?php if ($totalRows_rsAddresses == 0) { // Show if recordset empty ?>
                <p>You have no contact details entered so far. Please add an address to help us contact you.</p>
                <?php } // Show if recordset empty ?>
                <p>You can enter as many addresses as you like - quite handy if you have different  addresses you want to store! </p>
           
              <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
                  <li class="nav-item"><a href="../../location/members/add_location.php?useraddress=true" class="nav-link"><i class="glyphicon glyphicon-plus-sign"></i> Add an address</a></li>
      </ul></div></nav>
<?php if ($totalRows_rsAddresses > 0) { // Show if recordset not empty ?>
              <p class="text-muted">Addresses <?php echo ($startRow_rsAddresses + 1) ?> to <?php echo min($startRow_rsAddresses + $maxRows_rsAddresses, $totalRows_rsAddresses) ?> of <?php echo $totalRows_rsAddresses ?> </p>
             <?php if(isset($submit_error)) { ?><p class="alert alert-danger" role="alert"><?php echo $submit_error; ?></p><?php } ?>
<form action="<?php echo $editFormAction; ?>" method="post" name="addresses" id="addresses"> <table  class="table table-hover">
<thead>
                <tr>
                  <th>Name</th>
                  <th>Address</th>
                  <th>Postcode</th>
                  <th>Default</th>
                  <th>&nbsp;</th>
                </tr></thead><tbody>
                <?php do { ?>
                  <tr>
                    <td><?php echo htmlentities($row_rsAddresses['locationname'], ENT_COMPAT, "UTF-8"); ?></td>
                    <td><?php echo isset($row_rsAddresses['address1']) ? htmlentities($row_rsAddresses['address1'], ENT_COMPAT, "UTF-8") : "<em>Not yet entered</em>"; ?></td>
                    <td><?php echo $row_rsAddresses['postcode']; ?></td>
                    <td>
                      <div align="center">
                        <input name="defaultaddress" type="radio" value="<?php echo $row_rsAddresses['ID']?>" <?php if ($row_rsLoggedIn['defaultaddressID'] == $row_rsAddresses['ID']) { echo "checked"; }  ?> onclick = "this.form.submit();" />
                    </div></td>
                    <td><a href="../../location/members/update_location.php?locationID=<?php echo $row_rsAddresses['ID']; ?>&amp;useraddress=true">Update</a></td>
                  </tr>
                  <?php } while ($row_rsAddresses = mysql_fetch_assoc($rsAddresses)); ?></tbody>
              </table>
        <input name="userID" type="hidden" id="userID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
               <input type="hidden" name="MM_update" value="addresses" />
</form>
              
              <?php } // Show if recordset not empty ?><p>Your 'default' address is the address we will normally correspond to if you don't specify otherwise. </p><p><a href="../index.php" class="link_back"><i class="glyphicon glyphicon-arrow-left"></i> Back to Member Page </a></p></div>
    <!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>
<?php if(false) { // avoid free result bug
mysql_free_result($rsAddresses);

mysql_free_result($rsLoggedIn);
}
?>

