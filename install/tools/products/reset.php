<?php require_once('../../Connections/aquiescedb.php'); ?><?php
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

$MM_restrictGoTo = "../../upgrade/login.php?notloggedin=true";
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
<!DOCTYPE html>
<!-- Copyright Paul Egan. Any unauthorised copying, reproduction or alteration is strictly prohibited -->
<html lang="en" class="full_bhuna install <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Install.dwt.php" codeOutsideHTMLIsLocked="false" --><!-- Copyright Paul Egan. Any unauthorised copying, reproduction or alteration is strictly prohibited -->
<head>
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Reset Database"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<!-- InstanceEndEditable -->
</head>
<body>
<?php require_once('../../includes/header.inc.php'); ?>
<main>
<div class="container"><!-- InstanceBeginEditable name="Body" --><?php 


function load_db_dump($file,$sqlserver,$user,$pass,$dest_db)
{
  $sql=mysql_connect($sqlserver,$user,$pass);
  mysql_select_db($dest_db);
  $a=file($file);
  foreach ($a as $n => $l) if (substr($l,0,2)=='--') unset($a[$n]);
  $a=explode(";\n",implode("\n",$a));
  unset($a[count($a)-1]);
  foreach ($a as $q) if ($q) 
   if (!mysql_query($q)) {echo "Fail on '$q'"; mysql_close($sql); return 0;}
  mysql_close($sql);
  return 1;
}

if (isset($_POST['reset'])) {
$file = "../fullbhuna.sql";
if (load_db_dump($file,$hostname_aquiescedb,$username_aquiescedb,$password_aquiescedb,$database_aquiescedb)) {
?><h1>Success!</h1>
      <p>The database has been restored to its original state.</p>
     <?php } else { ?>
     <h1>Failed...</h1>
     <p>There was a problem with the operation. Please see your system administrator. </p>
     <?php }} else { ?>
        <h1>Reset Database</h1>
      <p>You can reset the database to its original state by clicking on the button below.</p>
      <p>WARNING! All data will be lost.</p>
      <form action="reset.php" method="post" id="form1">
        <input type="submit" class="button" onclick="javascript:return confirm('Are you sure you want to delete all data and return database to its original state?');" value="Reset Database" />
        <input name="reset" type="hidden" id="reset" value="true" />
      </form>
      <p>&nbsp;  </p>
      <?php }
?><!-- InstanceEndEditable --></div>
</main>
<?php require_once('../../includes/footer.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>