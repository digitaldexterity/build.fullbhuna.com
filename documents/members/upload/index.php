<?php require_once('../../../Connections/aquiescedb.php'); ?>
<?php require_once('../../../core/includes/sslcheck.inc.php'); ?>
<?php require_once('../../../core/includes/framework.inc.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "1,2,3,4,5,6,7,8,9,10";
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

$MM_restrictGoTo = "../../../login/index.php";
if (!((isset($_SESSION['MM_Username'])) && (isAuthorized("",$MM_authorizedUsers, $_SESSION['MM_Username'], $_SESSION['MM_UserGroup'])))) {   
  $MM_qsChar = "?";
  $MM_referrer = $_SERVER['PHP_SELF'];
  if (strpos($MM_restrictGoTo, "?")) $MM_qsChar = "&";
  if (isset($_SERVER['QUERY_STRING']) && strlen($_SERVER['QUERY_STRING']) > 0) 
  $MM_referrer .= "?" . $_SERVER['QUERY_STRING'];
  $MM_restrictGoTo = $MM_restrictGoTo. $MM_qsChar . "accesscheck=" . urlencode($MM_referrer);
  session_write_close();
  header("Location: ". $MM_restrictGoTo); exit;
  exit;
}
?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Upload"; echo $pageTitle." | ".$site_name; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" --><link href="/documents/css/documentsDefault.css" rel="stylesheet"  />
<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../../../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" -->
  <?php if(defined("FTP_HOST") && FTP_HOST !="") {
	  $temp_dir = "temp/".$_SESSION['MM_Username']."/";
	  $ftp_path = isset($_GET['ftp_path']) ? $_GET['ftp_path'] : FTP_PATH.$temp_dir;
	  createDirectory($ftp_path);
	  $handlerURL = isset($_GET['handler_url']) ? $_GET['handler_url'] : ""; ?>
      <h1>Upload</h1>
      <p>Drag and drop the files on to the area below and click upload...</p><applet 
	code="com.elementit.JavaPowUpload.Manager"
	archive="/components/JavaPowUpload/lib/JavaPowUpload.jar, 
	/components/JavaPowUpload/lib/commons-logging-1.1.jar,
	/components/JavaPowUpload/lib/commons-httpclient-3.1-rc1.jar,
	/components/JavaPowUpload/lib/commons-codec-1.3.jar,
	/components/JavaPowUpload/lib/commons-net-ftp.jar"
	width="600"
	height="400"
	name="JavaPowUpload"
	id="JavaPowUpload"
	mayscript="true"
	alt="JavaPowUpload by www.element-it.com">
   
  <!-- Java Plug-In Options -->
  <param name="progressbar" value="true" />
  <param name="boxmessage" value="Loading JavaPowUpload Applet ..." />
  <!-- JavaPowUpload parameters -->
  <param name="Common.UploadMode" value="true" />
  <param name="Common.FinishUrl" value="<?php echo $handlerURL; ?>" />
  <param name="Common.AllowEmptyFolders" value="<?php echo FTP_ALLOW_EMPTY_FOLDERS; ?>" />
  <param name="Common.FtpPassiveTransferMode" value="<?php echo FTP_PASSIVE; ?>" />
  <param name="Upload.UploadUrl" value="ftp://<?php echo FTP_USERNAME; ?>:<?php echo FTP_PASSWORD; ?>@<?php echo FTP_HOST; ?><?php echo $ftp_path; ?>" />
  
    Your browser does not support applets. Or you have disabled applets in your options.
    To use this applet, please install the newest version of Sun's java.
    You can get it from <a href="http://www.java.com/">java.com</a>
      </applet><?php } else { ?><p class="alert alert-danger" role="alert">In order to use the multiple upload feature, you need to set the FTP details in the site preferences file. If unsure, please speak to your system administrator.</p><?php } ?><!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../../../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>