<?php require_once('../../../Connections/aquiescedb.php'); ?><?php require_once('../../../core/includes/adminAccess.inc.php'); ?>
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

$MM_restrictGoTo = "/login/index.php?notloggedin=true&alert=".urlencode("You need to be logged in as a Web Administrator to access this page");
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
require_once('../../../core/includes/framework.inc.php'); 
if(!function_exists("openFile")) die("Requires a newer filesystem file");
if(isset($_REQUEST['openbutton'])) { // open file
	// add path if not included
	$filename = (strpos($_REQUEST['filename'], SITE_ROOT)==0) ? $_REQUEST['filename'] : SITE_ROOT.$_REQUEST['filename'];
	$filecontents = openFile($filename);
	$filecontents = $filecontents ? $filecontents: "ERROR";
}

if(isset($_POST['savebutton'])) { // save file
	$filename = SITE_ROOT.$_REQUEST['filename'];
	$filecontents = stripslashes($_REQUEST['filecontents']);
	if(saveFile($filename,$filecontents)) {
		$msg = "File saved";
	} else {
		$msg = "There was a problem saving the file.";
	}
}

?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "File Editor"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script src="../../../SpryAssets/SpryValidationTextField.js"></script>
<link href="../../../SpryAssets/SpryValidationTextField.css" rel="stylesheet"  /><link href="/documents/css/documentsDefault.css" rel="stylesheet"  />
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
    <h1><i class="glyphicon glyphicon-folder-open"></i> File Editor</h1>
    <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
      <li class="nav-item"><a href="index.php" class="nav-link"><i class="glyphicon glyphicon-arrow-left"></i> File Manager</a></li>
    </ul></div></nav>
   <?php require_once('../../../core/includes/alert.inc.php'); ?>
    <form action="file.php" method="post" name="form1" id="form1">
    <div class="form-inline">
      <span id="sprytextfield1">
      <input name="filename" type="text" id="filename" size="100" maxlength="255" value="<?php echo isset($_REQUEST['filename']) ? htmlentities($_REQUEST['filename'], ENT_COMPAT, "UTF-8") : ""; ?>" class="form-control"/>
      <span class="textfieldRequiredMsg">A file name is required.</span></span>
      <button type="submit" name="openbutton" id="openbutton" class="btn btn-default btn-secondary" >Open</button>
    </div>
      <p>
        <textarea name="filecontents" id="filecontents" cols="80" rows="30" class="form-control"><?php echo isset($filecontents) ? $filecontents : (isset($_REQUEST['filecontents']) ? $_REQUEST['filecontents'] : ""); ?></textarea>
      </p>
      <p>
        <button type="submit" name="savebutton" id="savebutton" class="btn btn-primary" >Save</button>
      </p>
    </form>
    <script>
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1", "none", {hint:"Enter filename..."});
    </script>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>