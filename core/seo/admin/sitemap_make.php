<?php require_once('../../../Connections/aquiescedb.php'); ?><?php require_once('../../includes/framework.inc.php'); ?>
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

$MM_restrictGoTo = "../../../login/index.php?notloggedin=true";
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
?><!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" --><title><?php $pageTitle = "Site Maps"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title><!-- InstanceEndEditable -->
<?php require_once('../includes/seo.inc.php'); ?>
<?php require_once('../../includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" --><!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" -->
        <div class="page seo">
   <h1><i class="glyphicon glyphicon-globe"></i> Site Maps</h1>
   
    <h2>Automated Site Maps</h2>
   <p>For CMS generated content, you can link Google to the following automated site maps, which are updated as you update content:</p>
  <?php  $host = getProtocol()."://". $_SERVER['HTTP_HOST'];?>
   <p><a href="<?php echo $host; ?>/articles/sitemap.xml.php" target="_blank" rel="noopener"><?php echo $host; ?>/articles/sitemap.xml.php</a> - for standard pages</p>
   <p><a href="<?php echo $host; ?>/news/sitemap.xml.php" target="_blank" rel="noopener"><?php echo $host; ?>/news/sitemap.xml.php</a> - for news pages</p><p><a href="<?php echo $host; ?>/products/sitemap.xml.php" target="_blank" rel="noopener"><?php echo $host; ?>/products/sitemap.xml.php</a> - for shop pages</p>
   
   
   <h2>This is an expert configuration section for 3rd party software</h2>
   <p>1. Copy the following files to root from the phpSitemapNG folder:<br />
     <br />
     sitemap.xml (- or sitemap.xml.gz for compressed sitemap)<br />
    sitemap.txt (if you would like to write txt sitemaps files)</p>
   <p>2. Make the following files WRITEABLE (666)<br />
     <br />
     /sitemap.xml (- or /sitemap.xml.gz for compressed sitemap)<br />
     /sitemap.txt (if you would like to write txt sitemaps files)<br />
     settings/settings.inc.php (to store your settings)<br />
    settings/files.inc.php</p>
   <p>3. Recommend settingsonce launched:</p>
   <p>Scan local files off</p>
   <p>Remember to save file (bizarrely above list of files)</p>
   <p><a href="phpSitemapNG/index.php">Then click here to run</a></p></div>
   <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>


