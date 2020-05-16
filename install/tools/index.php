<?php /* TO BE USED ION VERY OLD SITES
if (!isset($_SESSION)) {
  session_start();
}
$_SESSION['MM_Username'] = "admin";
$_SESSION['MM_UserGroup'] = "10"; */
?><?php
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

$MM_restrictGoTo = "../login.php";
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
?><?php require_once('../../Connections/aquiescedb.php'); ?>
<!DOCTYPE html>
<html lang="en" class="full_bhuna install <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Install.dwt.php" codeOutsideHTMLIsLocked="false" --><!-- Copyright Paul Egan. Any unauthorised copying, reproduction or alteration is strictly prohibited -->
<head>
<!-- InstanceBeginEditable name="doctitle" --><title>
<?php $pageTitle = "Tools"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title><!-- InstanceEndEditable -->
<?php require_once('../includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" --><!-- InstanceEndEditable -->
</head>
<body>
<?php require_once('../includes/header.inc.php'); ?>
<main>
<div class="container"><!-- InstanceBeginEditable name="Body" -->
   <h1>Tools</h1>
     
       <p><a href="../update/">Update Full Bhuna</a>   </p>
      <p><a href="import.php">Import new tables and data</a></p>
      <p><a href="update_directory_addresses.php">Upgrade directory data</a>   </p>
    <p><a href="merge_location_address.php">Merge location and address tables</a></p>
   
   <p><a href="survey_sessions.php">Update survey session IDs</a></p>
  
   <p><a href="calendar/events_to_calendar.php">Calendar</a></p>
   <p><a href="products/gtins.php">Products (import GTINs)</a></p>
<h2>Server Information</h2>
<p><a href="server/phpinfo.php">PHP Info</a></p>
<h2>Moving from one domain to another</h2>
   <p>Remember to update your <a href="../../location/admin/googlemaps.php">API keys</a></p>
   <p><a href="data/domain_links.php">Update absolute links within site</a></p>
   <p><a href="data/url_friendly_links.php">Change to URL-friendly inks</a></p>
   <h2>Housekeeping</h2>
   <p><a href="housekeeping/find_and_replace.php">Find and replace...</a></p>
   <p><a href="housekeeping/obfuscate.php">Obfuscate...</a></p>
   <p><a href="housekeeping/fix-duplicate-product-urls.php">Fix duplicate product URLs (longID)</a></p>
   <h2>Files</h2>
   <p><a href="files/images.php">Image audit</a> (Find images that are not being used and delete to save server space.)</p>
   <p><a href="files/rename.php">Rename files</a></p>
   <h2>WordPress</h2>
   <p><a href="wordpress/index.php">Import WordPress</a></p>
  <!-- InstanceEndEditable --></div>
</main>
<?php require_once('../includes/footer.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>


