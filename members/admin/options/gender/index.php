<?php require_once('../../../../Connections/aquiescedb.php'); ?>
<?php require_once('../../../../core/includes/adminAccess.inc.php'); ?>
<?php $regionID = (isset($regionID ) && $regionID>0)   ? intval($regionID ) : 1;
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

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form")) {
  $updateSQL = sprintf("UPDATE preferences SET askgendertext=%s, askgenderother=%s, askgenderrathernotsay=%s WHERE ID=%s",
                       GetSQLValueString($_POST['askgendertext'], "text"),
                       GetSQLValueString(isset($_POST['askgenderother']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['askgenderrathernotsay']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form")) {
  $updateGoTo = "../index.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo)); exit;
}



mysql_select_db($database_aquiescedb, $aquiescedb);
$update = "UPDATE users SET gender = NULL WHERE gender < 1";
mysql_query($update, $aquiescedb) or die(mysql_error());


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
$query_rsPreferences = "SELECT * FROM preferences WHERE ID = $regionID";
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsGenderStats = "SELECT users.gender , COUNT(*) as count FROM users GROUP BY users.gender ORDER BY count DESC";
$rsGenderStats = mysql_query($query_rsGenderStats, $aquiescedb) or die(mysql_error());
$row_rsGenderStats = mysql_fetch_assoc($rsGenderStats);
$totalRows_rsGenderStats = mysql_num_rows($rsGenderStats);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsHasGender = "SELECT COUNT( users.gender) AS counted FROM users";
$rsHasGender = mysql_query($query_rsHasGender, $aquiescedb) or die(mysql_error());
$row_rsHasGender = mysql_fetch_assoc($rsHasGender);
$totalRows_rsHasGender = mysql_num_rows($rsHasGender);
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Manage Gender Questions"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<style>
<!--
-->
</style>
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" --><div class="page class">
          <h1><i class="glyphicon glyphicon-user"></i> Manage Gender</h1> <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
            <li class="nav-item"><a href="../index.php" class="nav-link"><i class="glyphicon glyphicon-arrow-left"></i> User Options</a></li>
          </ul></div></nav><form name="form" action="<?php echo $editFormAction; ?>" method="POST"><fieldset><legend>Prompt</legend><input name="askgendertext" type="text" value="<?php echo $row_rsPreferences['askgendertext']; ?>" size="50" maxlength="100" class="form-control">
              
              <input name="ID" type="hidden" id="ID" value="<?php echo $regionID; ?>">
          </fieldset>
            <input type="hidden" name="MM_update" value="form">
            <fieldset>
              <legend>Options</legend>
              In addition to Male/Female, add choices for: 
              <label>
                <input <?php if (!(strcmp($row_rsPreferences['askgenderother'],1))) {echo "checked=\"checked\"";} ?> name="askgenderother" type="checkbox" id="askgenderother" value="1">
                Other</label>&nbsp;&nbsp;&nbsp;
                
                <label>
                <input <?php if (!(strcmp($row_rsPreferences['askgenderrathernotsay'],1))) {echo "checked=\"checked\"";} ?> name="askgenderrathernotsay" type="checkbox" id="askgenderrathernotsay" value="1">
                Prefer not to say</label>
            </fieldset><button type="submit" name="button" id="button"  class="btn btn-primary">Save changes</button>
          </form>
          <h2>Statistics</h2>
          
          <table class="form-table">
          <thead>
            <tr>
              <th>Gender</th>
              <th>Count</th>
            </tr></thead><tbody>
            <?php do { ?>
              <tr>
                <td><?php switch( $row_rsGenderStats['gender']) {
					case 1 : echo "Male"; break;
					case 2 : echo "Female"; break;
					case 3 : echo "Other"; break;
					case 4 : echo "Prefer not to say"; break;
					default : echo "Not stated";
				}; ?></td>
                <td><?php echo $row_rsGenderStats['count']; if($row_rsGenderStats['gender']>0 && $row_rsHasGender['counted']>0) {  echo " (".number_format(($row_rsGenderStats['count']/$row_rsHasGender['counted']*100),1,".",",")."%)"; } ?></td>
              </tr>
              <?php } while ($row_rsGenderStats = mysql_fetch_assoc($rsGenderStats)); ?></tbody>
          </table>
        </div><!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsPreferences);

mysql_free_result($rsGenderStats);

mysql_free_result($rsHasGender);
?>
